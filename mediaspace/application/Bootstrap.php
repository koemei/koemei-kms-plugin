<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /* protected function _initModules()
      {
      $front = Zend_Controller_Front::getInstance();
      $front->addModuleDirectory(APPLICATION_PATH .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules');
      }
     */

    protected function _initDebugConfiguration()
    {
        if(isset($_COOKIE[Kms_Resource_Config::DEBUG_CONFIG_NAME]))
        {
            $options = $this->getOptions();
            $options['resources']['config']['config'] = APPLICATION_PATH . '/../configs/config.'.$_COOKIE[Kms_Resource_Config::DEBUG_CONFIG_NAME].'.ini';
            $options['resources']['config']['modules'] = APPLICATION_PATH . '/../configs/modules.'.$_COOKIE[Kms_Resource_Config::DEBUG_CONFIG_NAME].'.ini';
            $this->setOptions($options);
            
        }
    }
    
    
    protected function _initSession()
    {
        /* handle the kms session */
        $sessionPrefix = 'kms';
        $sessionName = $sessionPrefix . substr(md5(dirname(__FILE__)), 0, 10);
        session_name($sessionName);
    }
    
    
    protected function _initLoaderCache()
    {
        $cacheDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';
        $classFileIncCache = $cacheDir . DIRECTORY_SEPARATOR . 'pluginLoaderCache.php';
        if (file_exists($classFileIncCache))
        {
            include_once $classFileIncCache;
        }
        if ($this->getOption('enablePluginLoaderCache') && is_writable($cacheDir))
        {
            Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
        }
    }

    protected function _initHelpers()
    {
        $moduleHelper = new Kms_Action_Helper_Module();
        Zend_Controller_Action_HelperBroker::addHelper($moduleHelper);
    }

    protected function _initRouter()
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'routes.ini', 'production');
        $router->addConfig($config, 'routes');
    }

    protected function _initDoctype()
    {
        Zend_Layout::startMvc();
/*        $this->bootstrap('view');
        $view = $this->getResource('view');

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);*/
    }

    public function run()
    {
        Kms_Log::log('bootstrap: run()', Kms_Log::DEBUG);
        
        $view = $this->getResource('view');
        $view->addHelperPath('ZendX' . DIRECTORY_SEPARATOR . 'JQuery' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Helper', 'ZendX_JQuery_View_Helper');

        $view->doctype('XHTML1_STRICT');
        $view->headTitle()->setDefaultAttachOrder('PREPEND');
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle(Kms_Resource_Config::getConfiguration('application', 'title'));

        // set the timezone. need to be done here to allow for per (saas) instance configuration.
        date_default_timezone_set(Kms_Resource_Config::getConfiguration('application', 'timezone'));

        parent::run();
    }

    protected function _initAutoloaders()
    {
    }

}

