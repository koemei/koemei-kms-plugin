<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Advanced Channel Settings Module Model
 * 
 */
class Channelsettingsadvanced_Model_Channelsettingsadvanced extends Kms_Module_BaseModel implements Kms_Interface_Contextual_Role 
{
    const MODULE_NAME = 'Channelsettingsadvanced';
    /* view hooks */

    public $viewHooks = array
	(
	Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABLINKS => array(
	    'action' => 'tab',
	    'controller' => 'index',
	    'order' => 20
	),
	Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABS => array(
	    'action' => 'index',
	    'controller' => 'index',
	    'order' => 20
	)
    );

    /* end view hooks */

    /**
     * (non-PHPdoc)
     * @see Kms_Module_BaseModel::getAccessRules()
     */
    public function getAccessRules() {
	$accessrules = array(
	    array(
		'controller' => 'channelsettingsadvanced:index',
		'actions' => array('tab'),
		'role' => Kms_Plugin_Access::VIEWER_ROLE,
	    ),
	    array(
		'controller' => 'channelsettingsadvanced:index',
		'actions' => array('index'),
		'role' => Kms_Plugin_Access::VIEWER_ROLE,
	    ),
	    array(
	    'controller' => 'channelsettingsadvanced:index',
	    'actions' => array('update'),
	    'role' => Kms_Plugin_Access::VIEWER_ROLE,
	    )
	);

	return $accessrules;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Contextual_Role::getContextualAccessRuleForAction()
     */
    public function getContextualAccessRuleForAction($actionName) {

	$contextualRule = false;

	if ($actionName == 'index' || $actionName == 'tab') {
	    $contextualRule = new Kms_Module_Contextual_Access_Channel(
			Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER,
			array('channelname', 'channelid'),
			false
	    );
	}
	return $contextualRule;
    }

}