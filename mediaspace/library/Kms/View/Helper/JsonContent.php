<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Kms_View_Helper_JsonContent extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    
    public function JsonContent($params)
    {
        $target = $content = $action = '';
        
        if(isset($params['content']) 
                && $params['content'])
        {
            $content = $params['content'];
        }
                    
        if(isset($params['target']) 
                && $params['target'])
        {
            $target = $params['target'];
        }
        
        if(isset($params['action']) 
                && $params['action'])
        {
            $action = $params['action'];
        }
        
        if(!isset($this->view->jsonContent))
        {
            $this->view->jsonContent = array();
        }
        
        $this->view->jsonContent[] = array(
            'target' => $target,
            'action' => $action,
            'content' => $content,
        );
        
        
    }
}