<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Addtoplaylists_Model_Addtoplaylists extends Kms_Module_BaseModel
{
    
    
    const MODULE_NAME = 'Addtoplaylists';
    /* view hooks */
    public $viewHooks = array
        (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array
            (
                'action' => 'index', 
                'controller' => 'index',
                'order' => 20
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array
            (
                'action' => 'tab', 
                'controller' => 'index',
                'order' => 20
            ),
            Kms_Resource_ViewHook::CORE_VIEW_HOOK_MYMEDIABULK => array(
                'action' => 'bulk-button', 
                'controller' => 'index',
                'order' => '30',

            )
        );
    /* end view hooks */

    
    public function getAccessRules()
    {
        $accessrules = array(
            array(
                    'controller' => 'addtoplaylists:index',
                    'actions' => array('bulk-button','bulk', 'index', 'tab', 'add-entry', 'add-new-playlist'),
                    'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
            
        );
        
        return $accessrules;
    }
}

