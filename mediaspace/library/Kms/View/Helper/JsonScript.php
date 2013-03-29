<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Kms_View_Helper_JsonScript extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    
    public function JsonScript($js = '')
    {
        if($js)
        {
        
            if(!isset($this->view->jsonScript))
            {
                $this->view->jsonScript = array();
            }

            $this->view->jsonScript[] = $js;
        }        
        
    }
}