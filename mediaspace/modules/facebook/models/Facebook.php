<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Facebook module Model
 *
 * @author atars
 *
 */
 class Facebook_Model_Facebook extends Kms_Module_BaseModel implements Kms_Interface_Deployable_PreDeployment
 {
    const MODULE_NAME = 'facebook';
    

    /* view hooks */
    public $viewHooks = array
    (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array( 'action' => 'header','controller' => 'tag', 'order' => 20),
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
                        'controller' => 'facebook:tag',
                        'actions' => array('header'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
        );
        return $accessrules;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canInstall()
     */
    public function canInstall()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canEnable()
     */
    public function canEnable()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::getPreDeploymentFailReason()
     */
    public function getPreDeploymentFailReason()
    {
        return 'Deployment should be allowed. You shouldn\'t ever see this message';
    }

}