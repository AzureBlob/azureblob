<?php
declare(strict_types=1);

namespace AzureBlob\Controllers;

use AzureBlob\Services\AzureBlobService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class AclController
{
    private AzureBlobService $blobService;
    private LoggerInterface $logger;

    /**
     * @param AzureBlobService $blobService
     * @param LoggerInterface $logger
     */
    public function __construct(AzureBlobService $blobService, LoggerInterface $logger)
    {
        $this->blobService = $blobService;
        $this->logger = $logger;
    }

    public function getAcl(Request $request, Response $response, string $containerName = ''): Response
    {
        $this->blobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $containerAcl = $this->blobService->getBlobContainerAcl($containerName);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'acl.twig',[
            'acl' => $containerAcl,
            'container' => $containerName,
        ]);
    }

    public function setAcl(Request $request, Response $response, string $containerName = ''): Response
    {
        $this->blobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $postData = (array) $request->getParsedBody();
        $acl = $postData['acl'] ?? '';
        $this->blobService->setBlobContainerAcl($containerName, $acl);
        return $response
            ->withHeader('Location', '/storage')
            ->withStatus(302);
    }
}
