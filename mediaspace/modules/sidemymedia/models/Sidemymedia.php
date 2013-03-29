<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Sidemymedia_Model_Sidemymedia extends Kms_Module_BaseModel
{
    const MODULE_NAME = 'sidemymedia';
    const DEFAULT_LIMIT = 20;
    /* view hooks */
    public $viewHooks = array
        (
            'PlayerSideTabs' => array
            (
                'action' => 'index', 
                'controller' => 'index',
                'order' => 10
            ),
            'PlayerSideTabLinks' => array
            (
                'action' => 'tab', 
                'controller' => 'index',
                'order' => 10
            )
        );
    /* end view hooks */

    
    public function getSideMyMedia()
    {
        $limit = Kms_Resource_Config::getModuleConfig('sidemymedia' ,'limit');
        if($limit === '' || is_null($limit))
        {
            $limit = self::DEFAULT_LIMIT;
        }
        $ret = array();
        $entryModel = Kms_Resource_Models::getEntry();
        $entryModel->setPageSize($limit);
        $params = array(
            'page' => 1,
            'sort' => 'recent',
            'type' => null,
        );
        
        $items = $entryModel->getMyMedia($params);
        return $items;
    }
    

    public function getAccessRules()
    {
        $accessrules = array(
            array(
                    'controller' => 'sidemymedia:index',
                    'actions' => array('index', 'tab'),
                    'role' => Kms_Plugin_Access::PRIVATE_ROLE,
            ),
            
        );
        
        return $accessrules;
    }
   
    
}

