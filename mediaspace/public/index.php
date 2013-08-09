<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

// mark the start time;
$startTime = microtime(true);

// Define unique script execution identifier
defined('UNIQUE_ID')
    || define('UNIQUE_ID', uniqid());

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Define build number (svn revision)
defined('BUILD_NUMBER')
    || define('BUILD_NUMBER', (getenv('BUILD_NUMBER') ? getenv('BUILD_NUMBER') : '0'));


// Define path to themes directory
defined('THEMES_PATH')
    || define('THEMES_PATH', realpath(dirname(__FILE__) . '/../themes'));

// Define path to custom themes directory
defined('CUSTOM_THEMES_PATH')
    || define('CUSTOM_THEMES_PATH', realpath(dirname(__FILE__) . '/../themesCustom'));


// Define path to modules directory
defined('MODULES_PATH')
    || define('MODULES_PATH', realpath(dirname(__FILE__) . '/../modules'));

// Define path to custom modules directory
defined('CUSTOM_MODULES_PATH')
    || define('CUSTOM_MODULES_PATH', realpath(dirname(__FILE__) . '/../modulesCustom'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library/'),
//    get_include_path(),
)));


$matches = array();
if (preg_match('#build.*/([^/]*)/asset/(.*\.(js|css|jpg|jpeg|png|gif|flv|swf))$#',$_SERVER['REQUEST_URI'], $matches) == 1)     
{
    /** assets request - bypass zend */

    // extract the file name and type
    $module = $matches[1];
    $fileName = $matches[2];
    $fileExtention = $matches[3];

    // call the asset function to serve the file
    require_once 'asset.php';
}
else
{
    /** Zend_Application */
    require_once 'Zend/Application.php';

    $configs = array(APPLICATION_PATH . '/configs/application.ini');
    if(file_exists(APPLICATION_PATH . '/../configs/local.ini'))
    {
        $configs[] = APPLICATION_PATH . '/../configs/local.ini';
    }
    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        array('config' => $configs)
    );

    // run the application
    try{
        $application->bootstrap();
        $application->run();
    }
    catch(Zend_Exception $e){
        Kms_Log::log('init: Error in request '.$_SERVER['REQUEST_URI'] . ' code:' .$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
        trigger_error('Zend_Exception: ' . $e->getCode() . ': ' . $e->getMessage(), E_USER_WARNING);
        
        $scheme = Zend_Controller_Front::getInstance()->getRequest()->getScheme();
        $host = Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

        header("Location: " . $scheme . '://' . $host . $baseUrl . "/error/error");
    }

    // calculate total runtime
    $totalRuntime = microtime(true) - $startTime;

    // log the runtime 
    Kms_Log::log('init: Execution time of '.$_SERVER['REQUEST_URI'].': '.$totalRuntime, Kms_Log::DEBUG);
}


