<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * plugin for initializing install process if configuration does not exist
 * This plugin is ONLY registered (in Kms_Resource_Config::initConfig, when config file does not exist)
 * @author Gonen
 */



class Kms_Plugin_Install extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if(!defined('KMS_INSTALL_CONTROLLER_ALLOWED'))
        {
            define("KMS_INSTALL_CONTROLLER_ALLOWED", true);
        }
        if($request->getControllerName() != 'install' && $request->getControllerName() != 'error')
        {
            $request->setControllerName('install');
            $request->setActionName('index');
        }
    }
}
?>
