<?php
declare(strict_types=1);

namespace AzureBlob\Controllers;

use AzureBlob\Services\AzureBlobService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final class StorageController
{
    private AzureBlobService $azureBlobService;

    /**
     * @param AzureBlobService $azureBlobService
     */
    public function __construct(AzureBlobService $azureBlobService)
    {
        $this->azureBlobService = $azureBlobService;
    }

    public function getContainerListing(Request $request, Response $response, array $args = []): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $containers = $this->azureBlobService->listBlobContainers();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'containers.twig',[
            'containers' => $containers,
        ]);
    }

    public function addContainer(Request $request, Response $response, array $args = []): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'container.twig');
    }

    public function createContainer(Request $request, Response $response, array $args = []): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        $postData = (array) $request->getParsedBody();
        $containerName = $postData['container'] ?? '';
        if ('' !== $containerName) {
            $this->azureBlobService->addBlobContainer($containerName);
        }
        return $response
            ->withHeader('Location', sprintf('/storage/container/%s', strtolower($containerName)))
            ->withStatus(302);
    }

    public function removeContainer(Request $request, Response $response, string $containerName = ''): Response
    {
        $this->azureBlobService->createBlobClient(
            base64_decode($_SESSION['az_account_name']),
            base64_decode($_SESSION['az_account_key'])
        );
        if ('' !== $containerName) {
            $this->azureBlobService->removeBlobContainer($containerName);
        }
        return $response
            ->withHeader('Location', '/storage')
            ->withStatus(302);
    }
}