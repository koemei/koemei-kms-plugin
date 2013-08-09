<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

// Define unique script execution identifier
defined('UNIQUE_ID')
    || define('UNIQUE_ID', uniqid());

// Define build number (svn revision)
defined('BUILD_NUMBER')
    || define('BUILD_NUMBER', (getenv('BUILD_NUMBER') ? getenv('BUILD_NUMBER') : '0'));


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'testing');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library/'),
    realpath(APPLICATION_PATH . '/../utils/phpunit/'),
   // get_include_path(),
)));


for ($i = 0; $i < 40000; $i++)
{
    echo ' '; // extra spaces for the flush
}
?>
<pre>
    <?php
    set_time_limit(0);
    ob_implicit_flush();
    ob_end_flush();
    $rootDir = dirname(__FILE__) . '/..';
    require_once('Zend/Config/Ini.php');
    $ini = new Zend_Config_Ini($rootDir . '/configs/config.ini', 'client');
    $salt = $ini->adminSecret;
    $hash = $_GET['h'];
    if (!$hash)
        die('Access Denied');

    $decoded = base64_decode($hash);
    list($signature, $info) = explode('|', $decoded);
    $mysig = md5($salt . $info);
    if ($mysig != $signature)
        die('Access Denied');

    chdir($rootDir . "/tests");

    system("/usr/bin/env php ../utils/phpunit/bin/phpunit -c phpunit-nocc.xml");
    ?>
</pre>


