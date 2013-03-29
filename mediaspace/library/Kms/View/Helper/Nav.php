<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * View Helper to display navigation from Zend_Navigation object
 */

/**
 * Description of UserLink
 *
 * @author leon
 */
class Kms_View_Helper_Nav extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function Nav()
    {
        
    }
}