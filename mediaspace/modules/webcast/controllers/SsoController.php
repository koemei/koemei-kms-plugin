<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Webcast module sso controller.
 * 
 * @author talbone
 *
 */
class Webcast_SsoController extends Kms_Module_Controller_Abstract
{
    protected $_translate = null;

    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
    
        $contextSwitch->setSuffix('ajax', 'ajax');
    
        $contextSwitch->addActionContext('redirect', 'ajax')->initContext();

        $this->_helper->contextSwitch()->initContext();    
    }

    /**
     *	redirect the user to webcast after generating the ks
     */
    public function redirectAction()
    {
    	$entryId = $this->getRequest()->getParam('entryId');

    	$model = new Webcast_Model_Webcast();

        // get the redirect url
        $customdata = $model->getEntryCustomdata($entryId);        
    	$url = $model->getUrl($customdata);
    	
        // generate the kaltura info
        $kalturaInfo = $model->getKalturaInfo($url);

        // compose the redirect url with the kaltura info
    	$this->view->url = $url . '&kparam=' . $kalturaInfo;

    	if ($this->getRequest()->getParam('format') != 'ajax') {
    		$this->_redirect($this->view->url);
    	}
    }
}