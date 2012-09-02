<?php

class Blob_IndexController extends Zend_Controller_Action
{

    protected $_session = null;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace(Application_Service_AzureBlob::WASA_SERVICE);
    }

    public function indexAction()
    {
        return $this->_helper->redirector('container', 'index', 'blob');
    }

    public function browseAction()
    {
        $container = $this->getRequest()->getParam('container', null);
        $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
        $containers = $azureBlob->listContainers();
        if (null === $container && !empty ($containers)) {
            $container = $containers[0]->getName();
        }
        $this->_session->lastContainer = $container;
        $blobs = $azureBlob->listBlobs($container);
        
        $this->view->assign(array (
            'containers' => $containers,
            'blobs' => $blobs,
            'lastContainer' => $this->_session->lastContainer,
        ));
    }

    public function addContainerAction()
    {
        // action body
    }

    public function removeContainerAction()
    {
        $container = $this->getRequest()->getParam('container', null);
        if (null !== $container) {
            $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
            $azureBlob->removeContainer($container);
        }
        return $this->_helper->redirector('container', 'index', 'blob');
    }

    public function addBlobAction()
    {
        $container = $this->getRequest()->getParam('container', null);
        $this->view->container = $container;
    }

    public function removeBlobAction()
    {
        $blobName = $this->getRequest()->getParam('file', null);
        if (null !== $blobName) {
            $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
            $azureBlob->removeBlob($this->_session->lastContainer, $blobName);
        }
        return $this->_helper->redirector('browse', 'index', 'blob', array ('container' => $this->_session->lastContainer));
    }

    public function containerAction()
    {
        $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
        $containers = $azureBlob->listContainers();
        $this->view->assign(array (
            'containers' => $containers,
        ));
    }

    public function uploadBlobAction()
    {
        $results = array ();
        if (!empty ($_FILES)){
            foreach ($_FILES['file']['error'] as $key => $error) {
                $file = array (
                    'name' => $_FILES['file']['name'][$key],
                    'type' => $_FILES['file']['type'][$key],
                    'size' => $_FILES['file']['size'][$key],
                    'tmp_name' => $_FILES['file']['tmp_name'][$key]);

                if (UPLOAD_ERR_OK === $error && is_uploaded_file($file['tmp_name'])) {
                    //move_uploaded_file($file['tmp_name'], sprintf('./uploads/%s', $file['name']));
                    $file['name'] = str_replace(' ', '_', $file['name']);
                    $contents = file_get_contents($file['tmp_name']);
                    $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
                    $azureBlob->addBlob(
                        $this->_session->lastContainer, 
                        $file['name'], 
                        $contents, 
                        array ('content-type' => $file['type'])
                    );
                    $results[] = sprintf('You have uploaded %s of type %s and size %d bytes',
                        $file['name'],
                        $file['type'],
                        $file['size']);
                }
            }
        }
        $this->view->results = $results;
    }

    public function createContainerAction()
    {
        $container = $this->getRequest()->getParam('containerName', null);
        if (null !== $container) {
            $azureBlob = new Application_Service_AzureBlob($this->_session->creds['account_name'], $this->_session->creds['account_key']);
            $azureBlob->createContainer($container);
        }
        return $this->_helper->redirector('container', 'index', 'blob');
    }


}

















