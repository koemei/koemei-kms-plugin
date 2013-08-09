<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * module to add channel settings button.
 * 
 * @author talbone
 *
 */
class Channelsettings_Model_Channelsettings extends Kms_Module_BaseModel implements Kms_Interface_Contextual_Role
{
    const MODULE_NAME = 'Channelsettings';
    /* view hooks */
    public $viewHooks = array
    (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PRE_CHANNEL => array(
                    'action' => 'managers',
                    'controller' => 'index',
                    'order' => 10
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNEL_BUTTONS => array(
                    'action' => 'button',
                    'controller' => 'index',
                    'order' => 10
            )
    );
    
    /* end view hooks */
    
    /**
     * (non-PHPdoc)
     * @see Kms_Module_BaseModel::getAccessRules()
     */
    public function getAccessRules()
    {
        $accessrules = array(
                array(
                        'controller' => 'channelsettings:index',
                        'actions' => array('managers'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelsettings:index',
                        'actions' => array('button'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
        );
    
        return $accessrules;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Contextual_Role::getContextualAccessRuleForAction()
     */
    public function getContextualAccessRuleForAction($actionName)
    {
        $contextualRule = false;

        if ($actionName == 'managers'){
            $contextualRule =  new Kms_Module_Contextual_Access_Channel(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER,
                    array('channelname', 'channelid'),
                    false
            );
        }
        // button control is done at the controller/view level
        
        return $contextualRule;
    }
}