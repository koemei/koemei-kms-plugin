<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Screencapture_Model_Screencapture extends Kms_Module_BaseModel 
{

    const MODULE_NAME = 'screencapture';
    /* view hooks */

    public $viewHooks = array
        (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_MYMEDIASIDEBARPOST => array
            (
            'action' => 'add-link',
            'controller' => 'index',
            'order' => 45,
        ),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_POST_HEADERUPLOAD => array
            (
            'action' => 'add-li',
            'controller' => 'index',
            'order' => 45,
        ),
    );
    /* end view hooks */


    public function getAccessRules()
    {
        $accessrules = array(
            array(
                'controller' => 'screencapture:index',
                'actions' => array('add', 'add-video'),
                'role' => Kms_Plugin_Access::PRIVATE_ROLE,
            ),
            array(
                'controller' => 'screencapture:index',
                'actions' => array('add-link', 'add-li'),
                'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
            array(
                'controller' => 'screencapture:index',
                'actions' => array('process'),
                'role' => Kms_Plugin_Access::EMPTY_ROLE,
            ),
        );

        return $accessrules;
    }

}

