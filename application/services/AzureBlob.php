<?php
/**
 * AzureBlob
 * 
 * This is general Windows Azure Storage browser. It provides the basic
 * functinality to list, add or update and remove storage objects from a single
 * Windows Azure Storage account.
 * 
 * @package AzureBlob
 * @category Browser
 * @copyright Creative Commons Attribution-ShareAlike 3.0 Unported License
 * @link http://creativecommons.org/licenses/by-sa/3.0/
 */
/**
 * @see WindowsAzure
 * @link https://github.com/WindowsAzure/azure-sdk-for-php
 */
require_once 'WindowsAzure/WindowsAzure.php';

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\CreateBlobOptions;
use WindowsAzure\Table\Models\Entity;
use WindowsAzure\Table\Models\EdmType;

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
class Application_Service_AzureBlob
{
    const WASA_SERVICE     = 'AzureBlob';
    const WASA_COOKIE_NAME = 'azure_account';
    const WASA_BLOB_URI    = 'blob.core.windows.net';
    const WASA_QUEUE_URI   = 'queue.core.windows.net';
    const WASA_TABLE_URI   = 'table.core.windows.net';
    
    /**
     * @var string The name of WASA
     */
    protected $_accountName;
    /**
     * @var string The primary key of WASA
     */
    protected $_accountKey;
    /**
     * Create a new Windows Azure Blob Service
     * 
     * @param null|string $accountName
     * @param null|string $accountKey 
     */
    public function __construct($accountName = null, $accountKey = null)
    {
        if (null !== $accountName) {
            $this->setAccountName($accountName);
        }
        if (null !== $accountKey) {
            $this->setAccountKey($accountKey);
        }
    }
    /**
     * Sets the account name for this Windows Azure storage Service
     * 
     * @param string $accountName
     * @return Application_Service_AzureBlob 
     */
    public function setAccountName($accountName)
    {
        $this->_accountName = (string) $accountName;
        return $this;
    }
    /**
     * Retrieves the account name from this Windows Azure Storage Service
     * 
     * @return string
     */
    public function getAccountName()
    {
        return $this->_accountName;
    }
    /**
     * Sets the primary account key for this Windows Azure Storage Service
     * 
     * @param string $accountKey
     * @return Application_Service_AzureBlob 
     */
    public function setAccountKey($accountKey)
    {
        $this->_accountKey = (string) $accountKey;
        return $this;
    }
    /**
     * Retrieves the primary account key from this Windows Azure Storage Service
     * 
     * @return string
     */
    public function getAccountKey()
    {
        return $this->_accountKey;
    }
    /**
     * Creates a connection string for this Widnows Azure Storage Service
     * 
     * @return string
     * @access protected
     */
    protected function _getConfig()
    {
        $config = sprintf(
            'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s',
            'http',
            $this->getAccountName(),
            $this->getAccountKey()
        );
        return $config;
    }
    /**
     * List the containers of a Windows Azure Blob Storage account
     * 
     * @return array
     * @throws ServiceException
     */
    public function listContainers()
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        $containers = array ();
        try {
            $containerList = $blobRestProxy->listContainers();
            $containers = $containerList->getContainers();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $containers;
    }
    /**
     * Creates a new container in a Windows Azure Blob Storage account
     * 
     * @param string $label
     * @param string $accessType
     * @param null|array $metaData
     * @return boolean
     * @throws ServiceException
     */
    public function createContainer($label, $accessType = PublicAccessType::CONTAINER_AND_BLOBS, $metaData = null)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        
        $containerOptions = new CreateContainerOptions();
        $containerOptions->setPublicAccess($accessType);
        
        if (null !== $metaData && is_array($metaData)) {
            foreach ($metaData as $key => $value) {
                $containerOptions->addMetadata($key, $value);
            }
        }
        $success = false;
        try {
            $blobRestProxy->createContainer($label, $containerOptions);
            $success = true;
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    /**
     * Removes a container from a Windows Azure Blob Storage account
     * 
     * @param string $label
     * @return boolean
     * @throws ServiceException 
     */
    public function removeContainer($label)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        $success = false;
        try {
            $blobRestProxy->deleteContainer($label);
            $success = true;
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    /**
     * Lists all blobs in a container in a Windows Azure Blob Storage account
     * 
     * @param string $container
     * @return array
     * @throws ServiceException
     */
    public function listBlobs($container)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        $blobs = array ();
        try {
            $blobList = $blobRestProxy->listBlobs($container);
            $blobs = $blobList->getBlobs();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $blobs;
    }
    /**
     * Adds a blob in a given container of a Windows Azure Blob Storage account
     * 
     * @param string $container
     * @param string $blobName
     * @param mixed $data
     * @param null|array $options
     * @return boolean
     * @throws ServiceException
     */
    public function addBlob($container, $blobName, $data, $options = null)
    {
        $blobName = str_replace(' ', '_', $blobName);
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        $success = false;
        
        if (null !== $options) {
            $blobOptions = new CreateBlobOptions();
            if (isset ($options['content-type'])) {
                $blobOptions->setContentType($options['content-type']);
                $blobOptions->setBlobContentType($options['content-type']);
            }
        }
        
        try {
            $blobRestProxy->createBlockBlob($container, $blobName, $data, $blobOptions);
            $success = true;
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    /**
     * Removes a blob form a container in a Windows Azure Blob Storage account
     * 
     * @param string $container
     * @param string $blobName
     * @return boolean
     * @throws ServiceException
     */
    public function removeBlob($container, $blobName)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($this->_getConfig());
        $success = false;
        try {
            $blobRestProxy->deleteBlob($container, $blobName);
            $success = true;
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    /**
     * List the tables in a Windows Azure Table Storage account
     * 
     * @return array
     * @throws ServiceException
     */
    public function listTables()
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $tables = array ();
        try {
            $tables = $tableRestProxy->queryTables();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $tables;
    }
    /**
     * Lists all entities in a given table
     * 
     * @param string $table
     * @return array
     */
    public function listEntities($table)
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $entities = array ();
        try {
            $entityList = $tableRestProxy->queryEntities($table);
            $entities = $entityList->getEntities();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $entities;
    }
    /**
     * Retrieves a single entity from a partition
     * 
     * @param string $table
     * @param string $partitionKey
     * @param string $rowKey
     * @return WindowsAzure\Table\Model\Entity
     */
    public function getEntity($table, $partitionKey, $rowKey)
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $entity = null;
        try {
            $entityResult = $tableRestProxy->getEntity($table, $partitionKey, $rowKey);
            $entity = $entityResult->getEntity();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $entity;
    }
    /**
     * Creates an entity to store data values in
     * 
     * @param string $table
     * @param string $partitionKey
     * @param string $rowKey
     * @return boolean 
     */
    public function createEntity($table, $partitionKey, $rowKey)
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $success = false;
        
        $entity = new Entity;
        $entity->setPartitionKey($partitionKey);
        $entity->setRowKey($rowKey);
        
        try {
            $tableRestProxy->insertEntity($table, $entity);
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    public function removeEntity($table, $partitionKey, $rowKey)
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $success = false;
        
        try {
            $tableRestProxy->deleteEntity($table, $partitionKey, $rowKey);
        } catch (ServiceException $e) {
            throw $e;
        }
        return $success;
    }
    /**
     * Adds a property to an existing entity
     * 
     * @param string $table
     * @param string $partionKey
     * @param string $rowKey
     * @param string $name
     * @param string $value
     * @param null|string $emdType
     * @return boolean 
     */
    public function addProperty($table, $partitionKey, $rowKey, $name, $value, $emdType = null)
    {
        $tableRestProxy = ServicesBuilder::getInstance()->createTableService($this->_getConfig());
        $success = false;
        $entity = null;
        
        try {
            $entity = $tableRestProxy->getEntity($table, $partitionKey, $rowKey);
        } catch (ServiceException $e) {
            throw $e;
        }
        
        if ($entity instanceof Entity) {
            $entity->addProperty($name, $edmType, $value);
            try {
                $tableRestProxy->insertOrMergeEntity($table, $entity);
                $success = true;
            } catch (ServiceException $e) {
                throw $e;
            }
        }
        return $success;
    }
    /**
     * Lists the queues in a Windows Azure Queue Storage account
     * 
     * @return array
     * @throws ServiceException
     */
    public function listQueues()
    {
        $queueRestProxy = QueueService::create($this->_getConfig('queue'));
        $queues = array ();
        try {
            $queueList = $queueRestProxy->listQueues();
            $queues = $queueList->getQueues();
        } catch (ServiceException $e) {
            
        }
        return $queues;
    }
    /**
     * Lists the messages in a queue from a Windows Azure Queue Storage account
     * 
     * @param string $queue
     * @return array
     * @throws ServiceException
     */
    public function listMessages($queue)
    {
        $queueRestProxy = QueueService::create($this->_getConfig('queue'));
        $messages = array ();
        try {
            $messageList = $queueRestProxy->listMessages($queue);
            $messages = $messageList->getQueueMessages();
        } catch (ServiceException $e) {
            throw $e;
        }
        return $messages;
    }
}