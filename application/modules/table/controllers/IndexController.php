<?php

class Table_IndexController extends Zend_Controller_Action
{
    protected $_session;

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


}



