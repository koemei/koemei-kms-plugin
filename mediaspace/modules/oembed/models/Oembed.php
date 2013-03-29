<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Oembed_Model_Oembed extends Kms_Module_BaseModel implements Kms_Interface_Model_Dependency
{
    const MODULE_NAME = 'oembed';
    
    private $Oembed;

    /* view hooks */
    public $viewHooks = array 
    (
        'preEmbedTab' => array
        (
            'action' => 'index', 
            'controller' => 'index',
        )
    );
    /* end view hooks */
    
    
    public function getAccessRules()
    {
        $embedAllowed = Kms_Resource_Config::getModuleConfig('embed', 'embedAllowed');
        $accessrules = array();
        if($embedAllowed)
        {
            $embedAllowed = $embedAllowed->toArray();
            foreach($embedAllowed as $roleKey => $role)
            {
                $accessrules[] = array(
                    'controller' => 'oembed:index',
                    'actions' => array('index','oembed'),
                    'role' => $roleKey
                );
            }
        }
        else
        {
            // defaults to allow for anonymous
            $accessrules[] = array(
                'controller' => 'oembed:index',
                'actions' => array('index','oembed'),
                'role' => Kms_Plugin_Access::ANON_ROLE,
            );
        }
        $accessrules[] = array(
            'controller' => 'oembed:index',
            'actions' => array('grab'),
            'role' => Kms_Plugin_Access::ANON_ROLE
        );
        
        return $accessrules;
    }
    
    public static function getModuleDependency()
    {
        return array('embed');
    }
}

