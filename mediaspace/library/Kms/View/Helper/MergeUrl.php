<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/*
 * View Helper to merge an array of params with the Url view helper
 */

/**
 * Description of MergeUrl
 *
 * @author leon
 */
class Kms_View_Helper_MergeUrl extends Zend_View_Helper_Abstract
{
    //put your code here
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function MergeUrl($params = array(), $route = NULL)
    {
        // get the params from the url (sometimes we do not have a pretty url so we need to add it to the params...)
        $urlParams = array();
        if($this->view->params && is_array($this->view->params))
        {
            $urlParams = $this->view->params;
        }
        elseif($this->view->partial()->view->params && is_array($this->view->partial()->view->params))
        {
            $urlParams = $this->view->partial()->view->params;
        }
        
        // clean up null values
        foreach($urlParams as $key => $value)
        {
            if(is_null($value))
            {
                unset($key);
            }
        }
        return $this->view->url( array_merge($urlParams, $params ), $route);
    }
}