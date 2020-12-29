<?php
namespace AzureBlob;

use MicrosoftAzure\Storage\Blob\Internal\IBlob;
use MicrosoftAzure\Storage\Blob\Models\BlobBlockType;
use MicrosoftAzure\Storage\Blob\Models\Block;
use MicrosoftAzure\Storage\Blob\Models\CommitBlobBlocksOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsResult;
use MicrosoftAzure\Storage\Blob\Models\ListContainersResult;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;

class AzureBlob
{
    const AZBLOB_PROTO_HTTP = 'http';
    const AZBLOB_PROTO_HTTPS = 'https';
    const AZBLOB_CHUNK_SIZE = 1048576;
    const AZBLOB_DEF_TYPE = 'application/octet-stream';
    /**
     * @var string The HTTP protocol used for connecting with Microsoft Azure
     * endpoints: 'http' or 'https'
     */
    protected $endpointProtocol;
    /**
     * @var string The name of your storage account
     */
    protected $accountName;
    /**
     * @var string The key associated with your storage account
     */
    protected $accountKey;
    /**
     * @var IBlob The proxy interface to interact with
     * Microsoft Azure Blob Storage
     */
    protected $blobProxy;

    public function __construct($accountName = null, $accountKey = null, $protocol = self::AZBLOB_PROTO_HTTP)
    {
        if (null !== $accountName && null !== $accountKey) {
            $this->setAccountName($accountName)
                ->setAccountKey($accountKey)
                ->setEndpointProtocol($protocol);
            $this->init();
        }
    }

    protected function init()
    {
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s',
            $this->getEndpointProtocol(),
            $this->getAccountName(),
            $this->getAccountKey()
        );
        $this->blobProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);
    }

    /**
     * @return string
     */
    public function getEndpointProtocol()
    {
        if (null === $this->endpointProtocol) {
            $this->setEndpointProtocol(self::AZBLOB_PROTO_HTTPS);
        }
        return $this->endpointProtocol;
    }

    /**
     * @param string $endpointProtocol
     * @return AzureBlob
     */
    public function setEndpointProtocol($endpointProtocol)
    {
        $this->endpointProtocol = $endpointProtocol;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        if (null === $this->accountName) {
            throw new \RuntimeException(
                'Please provide your Microsoft Azure Storage account name'
            );
        }
        return $this->accountName;
    }

    /**
     * @param string $accountName
     * @return AzureBlob
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountKey()
    {
        if (null === $this->accountKey) {
            throw new \RuntimeException(
                'Please provide your Microsoft Azure Storage account key'
            );
        }
        return $this->accountKey;
    }

    /**
     * @param string $accountKey
     * @return AzureBlob
     */
    public function setAccountKey($accountKey)
    {
        $this->accountKey = $accountKey;
        return $this;
    }

    /**
     * @return IBlob
     */
    public function getBlobProxy()
    {
        if (null === $this->blobProxy) {
            $this->init();
        }
        return $this->blobProxy;
    }

    /**
     * @param IBlob $blobProxy
     */
    public function setBlobProxy($blobProxy)
    {
        $this->blobProxy = $blobProxy;
    }

    /**
     * List all containers for this storage account
     *
     * @return ListContainersResult
     */
    public function listContainers()
    {
        $containerList = new ListContainersResult();
        try {
            $containerList = $this->getBlobProxy()->listContainers();
        } catch (ServiceException $serviceException) {
            throw $serviceException;
        }
        return $containerList;
    }

    /**
     * Creates a new container with given name in the storage account
     *
     * @param string $containerName
     * @param null $options
     * @return bool
     */
    public function createContainer($containerName, $options = null)
    {
        try {
            $this->getBlobProxy()->createContainer($containerName, $options);
            return true;
        } catch (ServiceException $serviceException) {
            throw $serviceException;
        }
        return false;
    }

    public function removeContainer($containerName, $options = null)
    {
        try {
            $this->getBlobProxy()->deleteContainer($containerName, $options);
            return true;
        } catch (ServiceException $serviceException) {
            throw $serviceException;
        }
        return false;
    }

    /**
     * @param $container
     * @return ListBlobsResult
     */
    public function listBlobs($container)
    {
        $blobList = new ListBlobsResult();
        try {
            $blobList = $this->getBlobProxy()->listBlobs($container);
        } catch (ServiceException $serviceException) {
            throw $serviceException;
        }
        return $blobList;
    }

    /**
     * Allows you to upload large binary files onto the Microsoft Azure
     * blob storage, which will be executed in chunks of 1MB.
     *
     * @param string $container The name of the Azure Blob Storage Container
     * @param string $file The location of the uploaded file
     * @param string $fileType The content-type (MIME-Type) of file that's being uploaded
     * @param string $fileName The filename you want to give the blob
     * @param string $prefix The prefix of the file for segmentation within the container
     * @example $azureBlob->uploadFile('demo', '/path/to/file.png', 'image/png', 'demo.png', 'mydemo/files')
     */
    public function uploadFile($container, $file, $fileType, $fileName, $prefix = null)
    {
        $counter = 1;
        $blockIds = [];

        $start = microtime(1);
        if (!is_readable($file)) {
            throw new \RuntimeException('File ' . $file . ' is not accessible for reading');
        }
        if (false === ($fh = fopen($file, 'r'))) {
            throw new \RuntimeException('Cannot open ' . $file);
        }

        if (null !== $prefix) {
            $prefix = rtrim($prefix, '/\\');
            $fileName = $prefix . '/' . $fileName;
        }

        while (!feof($fh)) {
            $blockId = str_pad($counter, 6, "0", STR_PAD_LEFT);
            $block = new Block();
            $block->setBlockId(base64_encode($blockId));
            $block->setType(BlobBlockType::UNCOMMITTED_TYPE);
            $blockIds[] = $block;
            $data = fread($fh, self::AZBLOB_CHUNK_SIZE);
            try {
                $this->getBlobProxy()->createBlobBlock($container, $fileName, base64_encode($blockId), $data);
                $this->log('Uploading block ' . $blockId . ' in ' . $container);
            } catch (ServiceException $serviceException) {
                $this->log('Committing blob ' . $fileName . ' with total of ' . count($blockIds) . ' blocks');
                throw $serviceException;
            }
            $counter++;
        }
        fclose($fh);

        $stop = microtime(1);
        $this->log('Completed upload of ' . $fileName . ' in ' . ($stop - $start) . ' seconds');

        $options = new CommitBlobBlocksOptions();
        $options->setContentType($fileType);
        try {
            $this->getBlobProxy()->commitBlobBlocks($container, $fileName, $blockIds, $options);
            $this->log('Committing blob ' . $fileName . ' with total of ' . count($blockIds) . ' blocks');
        } catch (ServiceException $serviceException) {
            $this->log('Unable to commit ' . $fileName . ' with total of ' . count($blockIds) . ' blocks');
            throw $serviceException;
        }
    }

    public function removeBlob($container, $blobName)
    {
        try {
            $this->getBlobProxy()->deleteBlob($container, $blobName);
        } catch (ServiceException $serviceException) {
            throw $serviceException;
        }
    }

    protected function log($message)
    {
        echo $message . '<br>' . PHP_EOL;
    }

    public static function shortenString($string, $maxLength = 24, $stringSplitter = '...')
    {
        if ($maxLength >= strlen($string)) {
            return $string;
        }
        $middle = floor($maxLength / 2) - 1;
        return substr($string, 0, $middle) . $stringSplitter . substr($string, 0 - $middle);
    }
}
