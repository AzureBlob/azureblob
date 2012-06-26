<?php
/**
 * AzureBlob
 * 
 * This is general Windows Azure Blob Storage browser. It provides the basic
 * functinality to list, add or update and remove blob objects from a single
 * Windows Azure Blob Storage account.
 * 
 * @package AzureBlob
 * @category Browser
 * @copyright Creative Commons Attribution-ShareAlike 3.0 Unported License
 * @link http://creativecommons.org/licenses/by-sa/3.0/
 */
/**
 * @see WindowsAzure
 */
require_once 'WindowsAzure/WindowsAzure.php';

use WindowsAzure\Common\Configuration;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Blob\BlobSettings;
use WindowsAzure\Blob\BlobService;
use WindowsAzure\Common\ServiceException;
/**
 * AzureBlob
 * 
 * This is general Windows Azure Blob Storage browser. It provides the basic
 * functinality to list, add or update and remove blob objects from a single
 * Windows Azure Blob Storage account.
 * 
 * @package AzureBlob
 * @category Browser
 * @copyright Creative Commons Attribution-ShareAlike 3.0 Unported License
 * @link http://creativecommons.org/licenses/by-sa/3.0/
 */
class AzureBlob
{
    /**
     * @var string The name of a Windows Azure Blob Storage account
     */
    protected $_accountName;
    /**
     * @var string The primary key of a Windows Azure Blob Storage account
     */
    protected $_accountKey;
    /**
     * @var string The url of a Windows Azure Blob Storage account
     */
    protected $_accountUrl;
    /**
     * @var WindowsAzure\Common\Configuration
     */
    protected $_config;
    /**
     * Constructor that sets the Windows Azure Blob Storage account details
     * 
     * @param string $accountName
     * @param string $accountKey
     * @param string $accountUrl 
     */
    public function __construct($accountName, $accountKey, $accountUrl)
    {
        $this->setAccountName($accountName)
             ->setAccountKey($accountKey)
             ->setAccountUrl($accountUrl);
        $this->initAzureConfig();
    }
    /**
     * Sets the name of this Windows Azure Blob Storage account
     * 
     * @param string $accountName
     * @return AzureBlob 
     */
    public function setAccountName($accountName)
    {
        $this->_accountName = (string) $accountName;
        return $this;
    }
    /**
     * Retrieves the name from this Windows Azure Blob Storage account
     * 
     * @return string
     */
    public function getAccountName()
    {
        return $this->_accountName;
    }
    /**
     * Sets the primary key for this Windows Azure Blob Storage account
     * 
     * @param string $accountKey
     * @return AzureBlob 
     */
    public function setAccountKey($accountKey)
    {
        $this->_accountKey = (string) $accountKey;
        return $this;
    }
    /**
     * Retrieves the key from this Windows Azure Blob Storage account
     * 
     * @return string
     */
    public function getAccountKey()
    {
        return $this->_accountKey;
    }
    /**
     * Sets the URL for this Windows Azure Blob Storage account
     * 
     * @param string $accountUrl
     * @return AzureBlob
     */
    public function setAccountUrl($accountUrl)
    {
        $this->_accountUrl = (string) $accountUrl;
        return $this;
    }
    /**
     * Retrieves the URL from this Windows Azure Blob Storage account
     * 
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_accountUrl;
    }
    protected function initAzureConfig()
    {
        $config = new Configuration();
        $config->setProperty(BlobSettings::ACCOUNT_NAME, $this->getAccountName());
        $config->setProperty(BlobSettings::ACCOUNT_KEY, $this->getAccountKey());
        $config->setProperty(BlobSettings::URI, $this->getAccountUrl());
        $this->_config = $config;
    }
    /**
     * Returns a collection of Blob objects
     * 
     * @param string $container
     * @return array 
     */
    public function listBlobs($container)
    {
        // Create blob REST proxy.
        $blobRestProxy = BlobService::create($this->_config);
        $blobs = array ();
        try {
            // List blobs.
            $blob_list = $blobRestProxy->listBlobs($container);
            $blobs = $blob_list->getBlobs();

        } catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $blobs;
    }
    /**
     * Adds a blob object into the Windows Azure Blob storage
     * 
     * @param string $container
     * @param string $blobName
     * @param stream $blobContents
     * @return boolean 
     */
    public function addBlob($container, $blobName, $blobContents)
    {
        // Create blob REST proxy.
        $blobRestProxy = BlobService::create($this->_config);
        
        // Replace spaces by underscores
        $blobName = str_replace(' ', '_', $blobName);
        
        // Let's fail first until we succeed in uploading the blob
        $success = false;
        try {
            //Upload blob
            $blobRestProxy->createBlockBlob($container, $blobName, $blobContents);
            $success = true;
        } catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $success;
    }
    /**
     * Removes a blob object from this Windows Azure Blob Storage account
     * 
     * @param string $container
     * @param string $blobName
     * @return boolean 
     */
    public function removeBlob($container, $blobName)
    {
        // Create blob REST proxy.
        $blobRestProxy = BlobService::create($this->_config);
        
        $success = false;
        try {
            // Delete container.
            $blobRestProxy->deleteBlob($container, $blobName);
            $success = true;
        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $success;
    }
    /**
     * Retrieves a list of containers
     * 
     * @return WindowsAzure\Blob\Models\ListContainersResult
     */
    public function listContainers()
    {
        // Create blob REST proxy
        $blobRestProxy = BlobService::create($this->_config);
        $containers = array ();
        
        try {
            $containers = $blobRestProxy->listContainers();
        } catch (ServiceException $e) {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $containers;
    }
    /**
     * Creates a new Windows Azure Blob Storage Container
     * 
     * @param string $label The label you want to give this container
     * @param string $accessType Access public or private for blob or conainer
     * @param null|array $metaData Extra meta data you want to provide
     * @return boolean 
     */
    public function createContainer($label, $accessType = PublicAccessType::NONE, $metaData = null)
    {
        // Create blob REST proxy.
        $blobRestProxy = BlobService::create($this->_config);

        // OPTIONAL: Set public access policy and metadata.
        // Create container options object.
        $createContainerOptions = new CreateContainerOptions(); 

        // Set public access policy. Possible values are 
        // PublicAccessType::CONTAINER_AND_BLOBS and PublicAccessType::BLOBS_ONLY.
        // CONTAINER_AND_BLOBS:     
        // Specifies full public read access for container and blob data.
        // proxys can enumerate blobs within the container via anonymous 
        // request, but cannot enumerate containers within the storage account.
        //
        // BLOBS_ONLY:
        // Specifies public read access for blobs. Blob data within this 
        // container can be read via anonymous request, but container data is not 
        // available. proxys cannot enumerate blobs within the container via 
        // anonymous request.
        // If this value is not specified in the request, container data is 
        // private to the account owner.
        $createContainerOptions->setPublicAccess($accessType);

        // Set container metadata
        if (null !== $metaData && is_array($metaData)) {
            foreach ($metaData as $key => $value) {
                $createContainerOptions->addMetaData($key, $value);
            }
        }
        
        $success = false;
        try {
            // Create container.
            $blobRestProxy->createContainer($label, $createContainerOptions);
            $success = true;
        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $success;
    }
    /**
     * Removes a container and everything in it
     * 
     * @param string $container
     * @return boolean 
     */
    public function removeContainer($container)
    {
        // Create blob REST proxy.
        $blobRestProxy = BlobService::create($this->_config);
        
        $success = false;
        try {
            $blobRestProxy->deleteContainer($container);
            $success = true;
        } catch (ServiceException $e) {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/en-us/library/windowsazure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        return $success;
    }
}
