<?php
// Define unique script execution identifier
defined('UNIQUE_ID')
    || define('UNIQUE_ID', uniqid());

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Define build number (svn revision)
defined('BUILD_NUMBER')
    || define('BUILD_NUMBER', (getenv('BUILD_NUMBER') ? getenv('BUILD_NUMBER') : '0'));



// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../library/phpunit/lib/phpunit'),
    get_include_path(),
)));
require_once('Zend/Application.php');
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

$application->bootstrap();

Zend_Session::$_unitTestEnabled = true;
$application->bootstrap('view');

/*require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
*/
#require_once 'ControllerTestCase.php';

