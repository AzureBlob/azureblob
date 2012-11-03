<?php

class Table_IndexController extends Zend_Controller_Action
{

    protected $_session = null;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace(Application_Service_AzureBlob::WASA_SERVICE);
    }

    public function indexAction()
    {
        return $this->_helper->redirector('list', 'index', 'table');
    }

    public function listAction()
    {
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        $tableList = $azureBlob->listTables();
        $tables = $tableList->getTables();
        
        $this->view->tables = $tables;
    }

    public function browseAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        $entities = $azureBlob->listEntities($table);
        $this->view->assign(array (
            'table' => $table,
            'entities' => $entities,
        ));
    }

    public function addEntityAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $this->view->table = $table;
    }

    public function createEntityAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $partitionKey = $this->getRequest()->getParam('partition', null);
        $rowKey = $this->getRequest()->getParam('row', null);
        
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        $azureBlob->createEntity($table, $partitionKey, $rowKey);
        return $this->_helper->redirector('browse', 'index', 'table', array ('table' => $table));
    }

    public function removeEntityAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $partitionKey = $this->getRequest()->getParam('partition', null);
        $rowKey = $this->getRequest()->getParam('row', null);
        
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        $azureBlob->removeEntity($table, $partitionKey, $rowKey);
        return $this->_helper->redirector('browse', 'index', 'table', array ('table' => $table));
    }

    public function addPropertyAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $partitionKey = $this->getRequest()->getParam('partition', null);
        $rowKey = $this->getRequest()->getParam('row', null);
        
        $types = Application_Service_AzureBlob::getPropertyTypes();
        
        $this->view->assign(array (
            'table' => $table,
            'partition' => $partitionKey,
            'row' => $rowKey,
            'types' => $types,
        ));
    }

    public function listPropertiesAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $partitionKey = $this->getRequest()->getParam('partition', null);
        $rowKey = $this->getRequest()->getParam('row', null);
        
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        
        $types = Application_Service_AzureBlob::getPropertyTypes();
        
        $entity = $azureBlob->getEntity($table, $partitionKey, $rowKey);
        $this->view->assign(array (
            'table'     => $table,
            'partition' => $partitionKey,
            'row' => $rowKey,
            'entity' => $entity,
            'propertyTypes' => $types,
        ));
    }

    public function createPropertyAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        $partitionKey = $this->getRequest()->getParam('partition', null);
        $rowKey = $this->getRequest()->getParam('row', null);
        $name = $this->getRequest()->getParam('name', null);
        $value = $this->getRequest()->getParam('value', null);
        $type = $this->getRequest()->getParam('type', null);
        
        $azureBlob = new Application_Service_AzureBlob(
            $this->_session->creds['account_name'], 
            $this->_session->creds['account_key']
        );
        $azureBlob->addProperty($table, $partitionKey, $rowKey, $name, $value, $type);
        return $this->_helper->redirector('browse', 'index', 'table', array ('table' => $table));
    }

    public function removeTableAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        
        $azureBlob = new Application_Service_AzureBlob(
                $this->_session->creds['account_name'],
                $this->_session->creds['account_key']
        );
        $azureBlob->dropTable($table);
        return $this->_helper->redirector('list', 'index', 'table');
    }

    public function createTableAction()
    {
        $table = $this->getRequest()->getParam('table', null);
        
        $azureBlob = new Application_Service_AzureBlob(
                $this->_session->creds['account_name'],
                $this->_session->creds['account_key']
        );
        $azureBlob->createTable($table);
    }


}





















