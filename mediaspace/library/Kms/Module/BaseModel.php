<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of BaseModel
 * Base class for all Models of Modules
 *
 * @author leon
 */
class Kms_Module_BaseModel implements Kms_Interface_Model_ViewHook, Kms_Interface_Access
{
    
    public function getAccessRules()
    {
        return array();
    }
    
    public function getViewHook($viewHook)
    {
        return isset($this->viewHooks) && isset($this->viewHooks[$viewHook]) ? $this->viewHooks[$viewHook] : array();
    }

    public function addViewHooks()
    {
        return array();
    }

    public function getImplementedViewHooks()
    {
        $hooks = array();
        if(isset($this->viewHooks) && count($this->viewHooks))
        {
            foreach($this->viewHooks as $hook => $settings)
            {
                $hooks[] = $hook;
            }
        }
        return $hooks;
    }
    
/*
 * deprecated - not called anymore...
 *     function viewHookExists($viewHook)
 *
    {
        return isset($this->viewHooks[$viewHook]);
    }
  */

}

?>
