<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Headermenu_Model_Headermenu extends Kms_Module_BaseModel
{
    
    
    const MODULE_NAME = 'headermenu';
    /* view hooks */
    public $viewHooks = array
        (
            'headerMenu' => array
            (
                'action' => 'index', 
                'controller' => 'index',
                'order' => 1
            ),
        );
    /* end view hooks */

    

    public function getAccessRules()
    {
        $accessrules = array(
            array(
                    'controller' => 'headermenu:index',
                    'actions' => array( 'index'),
                    'role' => Kms_Plugin_Access::EMPTY_ROLE,
            ),
            
        );
        
        return $accessrules;
    }
   
    
}

