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
    public const REMEMBER_COOKIE_NAME = 'azblob_account';

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

    public function setRememberMeCookie(string $accountName, string $accountKey): bool
    {
        $accountHash = base64_encode(sprintf('%s:%s', $accountName, $accountKey));
        return setcookie(self::REMEMBER_COOKIE_NAME, $accountHash, [
            'expires' => time() + 60 * 60 * 24 * 356,
            'path' => '/',
            'domain' => $_SERVER['SERVER_NAME'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function startSession(string $accountName, string $accountKey): void
    {
        $_SESSION['az_account_name'] = base64_encode($accountName);
        $_SESSION['az_account_key'] = base64_encode($accountKey);
    }
}