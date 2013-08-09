<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/*
 * View Helper to create a link to an entry
 */

/**
 * Description of EntryLink
 *
 * @author leon
 */
class Kms_View_Helper_EntryLink extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function EntryLink($id, $name = null, $redirect = false)
    {
    	$enableEntryTitle = Kms_Resource_Config::getConfiguration('application', 'enableEntryTitles');
        if($name && $enableEntryTitle)
        {
            $name = str_replace('%2F','', urlencode(str_replace('/',' ', $name)));
            $link = 'media/'.$name.'/'.$id;
        }
        else
        {
            $link = 'media/'.$id;
        }
        
        if($redirect)
        {
            return $link;
        }
        else
        {
            return $this->view->baseUrl( $link );
        }
    }
}