<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Kms_View_Helper_InfoFields extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    
    }
    
    public function InfoFields($fields = false)
    {
        $ret = '';
        
        if(!empty($fields)){
            $ret = '<div class="info tabItem"><fieldset class="itemCollection"><legend>Module Info</legend>';
        }
        
        // iterate over the fields
        foreach($fields as $name => $field)
        {
            // we only support text fields 
            if (is_string($field))
            {
                $ret .= '<div class="tabItem"><dt id="spMetadata-name-label">';
                $ret .= $name;
                $ret .= '</dt>';
                $ret .= "<dd id='spMetadata-$name-element'>";
                $ret .= $field;
                $ret .= '</dd></div>';
            }
        }
        if(!empty($fields)){
            $ret .= '</fieldset></div>';
        }
        return $ret;
    }
    
}