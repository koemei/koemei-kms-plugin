<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Sideplaylists_Model_Sideplaylists extends Kms_Module_BaseModel
{
    
    private static $_sidePlaylists = false;
    
    const MODULE_NAME = 'sideplaylists';
    /* view hooks */
    public $viewHooks = array
        (
            'PlayerSideTabs' => array
            (
                'action' => 'index', 
                'controller' => 'index',
                'order' => 30
            ),
            'PlayerSideTabLinks' => array
            (
                'action' => 'tab', 
                'controller' => 'index',
                'order' => 30
            )
        );
    /* end view hooks */

    
/*    public static function getAllPartnerPlaylists()
    {
        $client = Kms_Resource_Client::getAdminClient();
        $filter = new Kaltura_Client_Type_PlaylistFilter();
        
        $pager = new Kaltura_Client_Type_FilterPager();
        $pager->pageSize = 1000;
        
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter, $pager);
        $playlists = Kms_Resource_Cache::apiGet('partnerplaylists', $cacheParams);
        if(!$playlists)
        {
            $playlists = $client->playlist->listAction($filter, $pager);
            Kms_Resource_Cache::apiSet('partnerplaylists', $cacheParams, $playlists);
        }
        $ret = array();
        foreach($playlists->objects as $playlist)
        {
            $ret[] = $playlist->id;
        }
        return $ret;
        
    }
  */  
    
    public function getSidePlaylists()
    {
        if(self::$_sidePlaylists)
        {
            return self::$_sidePlaylists;
        }
        
        $playlists = Kms_Resource_Config::getModuleConfig('sideplaylists', 'items');
        $limit = Kms_Resource_Config::getModuleConfig('sideplaylists' ,'limit');
        $ret = array();
        $playlistModel = Kms_Resource_Models::getPlaylist();
        if(count($playlists))
        {
            foreach($playlists as $playlist)
            {
                if(isset($playlist->id) && trim($playlist->id))
                {
                    $items = array();
                    try
                    {
                        $items = $playlistModel->getEntries($playlist->id, $limit);
                    }
                    catch(Kaltura_Client_Exception $e)
                    {
                        Kms_Log::log('sideplaylists: '.$e->getCode().': '.$e->getMessage());
                    }
                    
                    if(count($items))
                    {
                        $ret[] = array(
                            'id' => $playlist->id,
                            'entries' => $items,
                            'label' => isset($playlist->label) && $playlist->label ? $playlist->label : $playlist->id,
                        );
                    }
                }

            }
        }
        self::$_sidePlaylists = $ret;
        return $ret;
    }
    

    public function getAccessRules()
    {
        $accessrules = array(
            array(
                    'controller' => 'sideplaylists:index',
                    'actions' => array('index', 'tab'),
                    'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
            
        );
        
        return $accessrules;
    }
   
    static public function adminPostSave($params)
    {
        $playlists = array();
        
        // get the playlists from the navigation items (post and pre)
        foreach($params as $navItem)
        {
            if(isset($navItem['label']) && isset($navItem['id']) && !empty($navItem['id']))
            {
                $playlists[] = $navItem['id'];
            }
        }
       
        $res = array();
        if(count($playlists))
        {
            $playlists = array_unique($playlists);
            $res = Kms_Resource_Models::getPlaylist()->makePublic($playlists);
        }
        
        if(count($res))
        {
            return 'The following playlists were added to the "playlists" category: '.join($res, ', ');
        }
        else
        {
            return '';
        }
    }
    
    
}

