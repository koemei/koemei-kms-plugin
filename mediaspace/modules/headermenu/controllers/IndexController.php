<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Headermenu_IndexController extends Kms_Module_Controller_Abstract
{
    public function init()
    {
    }
    
    public function indexAction()
    {
        $this->view->menuConfig = Kms_Resource_Config::getModuleConfig('headermenu', 'menu');
    }
    
}



