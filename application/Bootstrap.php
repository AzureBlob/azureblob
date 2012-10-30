<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initSetupView()
    {
        // Initialize view
        $view = $this->getPluginResource('view')->getView();
        $view->headTitle('Windows Azure Storage Browser');
        $view->headTitle()->setSeparator(': ');
        $view->headLink()->appendStylesheet($view->baseUrl('/style/style.css'));
        $view->headMeta()->setHttpEquiv('Content-type', 'text/html; Charset=UTF-8');
        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);
        // Return it, so that it can be stored by the bootstrap
        return $view;
    }
    
    protected function _initNavigation()
    {
        $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml');
        $navigation = new Zend_Navigation();
        $navigation->setPages($config->navigation->toArray());
        $view = $this->getPluginResource('view')->getView();
        $view->navigation($navigation);
        return $view;
    }
    
    protected function _initTranslations()
    {
        if ('development' === APPLICATION_ENV) { 
            $resourceLog = $this->getPluginResource('log');
            $log = $resourceLog->getLog();
            
            $resource = $this->getPluginResource('translate');
            $translate = $resource->getTranslate();
            $options = array (
                'log' => $log,
                'logPriority' => 6,
            );
            $translate->setOptions($options);
        }
    }
}

