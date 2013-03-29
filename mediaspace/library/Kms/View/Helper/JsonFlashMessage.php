<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Kms_View_Helper_JsonFlashMessage extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    
    public function JsonFlashMessage($message = null)
    {
        if(null !== $message)
        {
            if(!isset($this->view->jsonFlashMessages))
            {
                $this->view->jsonFlashMessages = array();
            }

            $this->view->jsonFlashMessages[] = $message;
        }        
        
    }
}