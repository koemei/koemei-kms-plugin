<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Module
 *
 * @author leon
 */
class Kms_Action_Helper_Module extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $front = Zend_Controller_Front::getInstance();
        if($this->_actionController->getRequest()->getModuleName() != $front->getDefaultModule())
        {
            if(!is_subclass_of($this->_actionController, 'Kms_Module_Controller_Abstract'))
            {
                throw new Zend_Controller_Action_Exception("Module controller must extend Kms_Module_Controller_Abstract");
            }
        }
    }
    //put your code here
}

