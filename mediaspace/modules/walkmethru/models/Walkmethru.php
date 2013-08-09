<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Walk me thru module Model
 *
 * @author talbone
 *
 */
class Walkmethru_Model_Walkmethru extends Kms_Module_BaseModel                                                                 
{
    const MODULE_NAME = 'walkmethru';

	/* view hooks */
    public $viewHooks = array
    (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_ADMIN_FOOTER => array( 'action' => 'footer','controller' => 'index', 'order' => 20),  
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_FOOTER => array( 'action' => 'footer','controller' => 'index', 'order' => 20),     
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
                        'controller' => 'walkmethru:index',
                        'actions' => array('footer'),
                        'role' => Kms_Plugin_Access::EMPTY_ROLE,
                ),                
        );
        return $accessrules;
    }
}
    