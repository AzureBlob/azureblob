<?php

class IndexController extends Zend_Controller_Action
{
    protected $_session;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace(Application_Service_AzureBlob::WASA_SERVICE);
        $this->_helper->layout()->setLayout('layout_splash');
    }

    public function indexAction()
    {
        $creds = array ('account_name' => '', 'account_key' => '');
        if (isset ($_COOKIE[Application_Service_AzureBlob::WASA_COOKIE_NAME])) {
            $creds = unserialize($_COOKIE[Application_Service_AzureBlob::WASA_COOKIE_NAME]);
            $creds['remember_me'] = 1;
        } elseif (isset ($this->_session->creds)) {
            $creds = $this->_session->creds;
        }
        $this->view->assign($creds);
    }

    public function browseAction()
    {
        $container = $this->getRequest()->getParam('container', null);
        $azureBlob = new Application_Service_AzureBlob(self::ACCOUNT_NAME, self::ACCOUNT_KEY);
        $containers = $azureBlob->listContainers();
        if (null === $container && !empty ($containers)) {
            $container = $containers[0]->getName();
        }
        $blobs = $azureBlob->listBlobs($container);
        
        $queues = $azureBlob->listQueues();
        
        $this->view->assign(array (
            'containers' => $containers,
            'blobs' => $blobs,
            'queues' => $queues,
        ));
    }

    public function authAction()
    {
        $accountName = $this->getRequest()->getParam('account_name', null);
        $accountKey = $this->getRequest()->getParam('account_key', null);
        $remember = $this->getRequest()->getParam('remember_me', 0);
        
        $creds = array (
            'account_name' => $accountName, 
            'account_key' => $accountKey
        );
        $this->_session->creds = $creds;
        
        if (1 === (int) $remember) {
            $status = setcookie(
                Application_Service_AzureBlob::WASA_COOKIE_NAME, 
                serialize($creds),
                time() + 60 * 60 * 24 * 30, // expire in 30 days
                '/',
                $_SERVER['HTTP_HOST']
            );
        }
        return $this->_helper->redirector('index', 'index', 'blob');
    }


}





