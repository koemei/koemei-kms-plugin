<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Walk me thru module controller.
 * 
 * @author talbone
 *
 */
class Walkmethru_IndexController extends Kms_Module_Controller_Abstract
{
	/**
     *  add the wlkmethru to the page footer
     */
    public function footerAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();            
        $role = Kms_Plugin_Access::getRoleKey($identity->getRole());

        if ($role == Kms_Plugin_Access::PARTNER_ROLE) {
            // KMS admin always get the script
            $param = true;
        }
        else{
            // KMS gets the script only if the paramater is present
    	   $param = $this->getRequest()->getParam(Kms_Resource_Config::getModuleConfig(Walkmethru_Model_Walkmethru::MODULE_NAME, 'activationParam'));
        }

       	if (!empty($param) && ($param == true)) {
            // get the script url
            $this->view->script = Kms_Resource_Config::getModuleConfig(Walkmethru_Model_Walkmethru::MODULE_NAME, 'scriptUrl');

            // strip http/https
            $this->view->script = str_replace('https://', '', $this->view->script);
            $this->view->script = str_replace('http://', '', $this->view->script);

        	// determine http/https
            if( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ){
                $this->view->script = 'https://' . $this->view->script;
            }
            else{
                $this->view->script = 'http://' . $this->view->script;
            }
        }
        else {
        	$this->_helper->viewRenderer->setNoRender(TRUE);        	
        }
    }
}