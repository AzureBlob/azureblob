<?php
declare(strict_types=1);

namespace AzureBlob\Services;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobBlockOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsResult;
use MicrosoftAzure\Storage\Blob\Models\ListContainersOptions;
use MicrosoftAzure\Storage\Blob\Models\ListContainersResult;
use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;
use Slim\Psr7\UploadedFile;

/**
 * AzureBlobService
 *
 * This service provides functionality to retrieve, upload and change
 * binary objects on Azure Blob Storage.
 *
 * @see https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php?tabs=linux
 */
final class AzureBlobService
{
    public const ENDPOINT_PROTO_HTTP = 'http';
    public const ENDPOINT_PROTO_HTTPS = 'https';

    /**
     * @var BlobRestProxy The client used to access the blob storage
     */
    private BlobRestProxy $blobClient;

    public function createBlobClient(
        string $accountName,
        string $accountKey,
        string $endpointProtocol = self::ENDPOINT_PROTO_HTTPS
    ): void {
        if(! in_array($endpointProtocol, [self::ENDPOINT_PROTO_HTTP, self::ENDPOINT_PROTO_HTTPS])) {
            $endpointProtocol = self::ENDPOINT_PROTO_HTTPS;
        }
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s',
            $endpointProtocol,
            $accountName,
            $accountKey
        );
        $this->blobClient = BlobRestProxy::createBlobService($connectionString);
    }

    public function addBlobContainer(string $containerName): bool
    {
        $this->blobClient->createContainer(strtolower($containerName));
        return true;
    }

    public function removeBlobContainer(string $containerName): bool
    {
        $this->blobClient->deleteContainer($containerName);
        return true;
    }

    public function listBlobContainers(): ListContainersResult
    {
        return $this->blobClient->listContainers();
    }

    public function listBlobs(string $containerName): ListBlobsResult
    {
        return $this->blobClient->listBlobs($containerName);
    }

    public function uploadBlob(string $containerName, UploadedFile $uploadedFile, string $prefix = '')
    {
        $contents = file_get_contents($uploadedFile->getFilePath());
        $blobName = $uploadedFile->getClientFilename();
        if ('' !== $prefix) {
            $blobName = sprintf(
                '%s/%s',
                rtrim($prefix, '/'),
                $uploadedFile->getClientFilename()
            );
        }
        $this->blobClient->createBlockBlob($containerName, $blobName, $contents);
        $blobOptions = new SetBlobPropertiesOptions();
        $blobOptions->setContentType($uploadedFile->getClientMediaType());
        $this->blobClient->setBlobProperties(
            $containerName,
            $blobName,
            $blobOptions
        );
    }

    public function removeBlob(string $containerName, string $blobName): bool
    {
        $this->blobClient->deleteBlob($containerName, $blobName);
        return true;
    }
}