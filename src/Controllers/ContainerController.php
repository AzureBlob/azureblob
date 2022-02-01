<?php
declare(strict_types=1);

namespace AzureBlob\Controllers;

use AzureBlob\Services\AzureBlobService;
use AzureBlob\Utilities\CacheUtilityInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\UploadedFile;
use Slim\Views\Twig;

final class ContainerController
{
    private AzureBlobService $azureBlobService;
    private LoggerInterface $logger;
    private CacheUtilityInterface $cache;

    /**
     * @param AzureBlobService $azureBlobService
     */
    public function __construct(
        AzureBlobService $azureBlobService,
        LoggerInterface $logger,
        CacheUtilityInterface $cacheUtility
    ) {
        $this->azureBlobService = $azureBlobService;
        $this->logger = $logger;
        $this->cache = $cacheUtility;
    }

    public function getBlobListing(Request $request, Response $response, string $containerName = ''): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $cacheKey = sprintf(
            '%s-%s',
            base64_decode($_SESSION['az_account_name']),
            $containerName
        );
        if (false === ($blobs = $this->cache->load($cacheKey))) {
            $this->logger->info(sprintf('Retrieving %s from backend', $cacheKey));
            $blobs = $this->azureBlobService->listBlobs($containerName);
            $this->cache->save($cacheKey, $blobs);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'blobs.twig',[
            'blobs' => $blobs,
            'container' => $containerName,
        ]);
    }

    public function uploadBlob(Request $request, Response $response, string $containerName): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );

        $postData = (array) $request->getParsedBody();
        $prefix = $postData['prefix'];

        $uploadedFiles = $request->getUploadedFiles();
        foreach($uploadedFiles as $uploadedFile) {
            $this->azureBlobService->uploadBlob($containerName, $uploadedFile, $prefix);
        }
        $cacheKey = sprintf(
            '%s-%s',
            base64_decode($_SESSION['az_account_name']),
            $containerName
        );
        $this->cache->purge($cacheKey);
        return $response->withHeader('Location', sprintf('/storage/container/%s', $containerName));
    }

    public function removeBlob(Request $request, Response $response, $containerName): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $queryParams = $request->getQueryParams();
        $blobName = $queryParams['blob'] ?? '';

        if ('' !== $blobName) {
            $this->azureBlobService->removeBlob($containerName, $blobName);
        }
        $cacheKey = sprintf(
            '%s-%s',
            base64_decode($_SESSION['az_account_name']),
            $containerName
        );
        $this->cache->purge($cacheKey);
        return $response->withHeader('Location', sprintf('/storage/container/%s', $containerName));
    }
}