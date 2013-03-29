<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Model_Playlist
{
    /**
     * @return Kaltura_Client_Type_Playlist
     * 
     */
    
    public $id;
    public $playlist;
    public $totalCount;
    
    function __construct()
    {
        $this->playlist = new Kaltura_Client_Type_Playlist();
    }
    
    function get($id)
    {
        $cacheParams = array('id' => $id);
        
        if(!$playlist = Kms_Resource_Cache::apiGet('playlist', $cacheParams))
        {
            $client = Kms_Resource_Client::getUserClient();
            try 
            {
                $playlist = $client->playlist->get($id);
                Kms_Resource_Cache::apiSet('playlist', $cacheParams, $playlist, array('playlist_'.$id));
                $this->id = $id;
                $this->playlist = $playlist;
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('playlist: Failed to get playlist '.$id.'. '.$e->getCode().': '.$e->getMessage());
                //throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        Kms_Resource_Models::setPlaylist($this);
        
        return $playlist;
    }
    
    public function setId($playlistId)
    {
        $this->id = $playlistId;
        Kms_Resource_Models::setPlaylist($this);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function delete($playlistId)
    {
        $userId = Kms_Plugin_Access::getId();
        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            $client->playlist->delete($playlistId);
            // invalidate the cache
            $cacheTags = array(
                'playlist_'.$playlistId,
            );
            Kms_Resource_Cache::apiClean('playlist', array('id' => $playlistId), $cacheTags);
            Kms_Resource_Cache::apiClean('playlists', array('userId' => $userId), $cacheTags);
            Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags);
            return true;
        }
        catch(Kaltura_Client_Exception $e)
        {
            Kms_Log::log('playlist: Playlist delete failed. '.$e->getCode().': '.$e->getMessage());
            return false;
        }
    }
    
    
    
    function getEntries($id, $limit = null, $page = null)
    {
        $userId = Kms_Plugin_Access::getId();
        $cacheParams = array('id' => $id, 'limit' => $limit, 'userId' => $userId, 'page' => $page);
        
        if(!$entries = Kms_Resource_Cache::apiGet('playlistentries', $cacheParams))
        {
            $client = Kms_Resource_Client::getAdminClient();
            try 
            {
                $entries = $client->playlist->execute($id);
                $cacheTags = array('playlist_'.$id);
                foreach($entries as $entry)
                {
                    if(isset($entry->id))
                    {
                        $cacheTags[] = 'entry_'.$entry->id;
                    }
                }
                Kms_Resource_Cache::apiSet('playlistentries', $cacheParams, $entries, $cacheTags);
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('playlist: Failed to get playlist '.$id.'. '.$e->getCode().': '.$e->getMessage());
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        $this->totalCount = count($entries);
        if($entries && $limit)
        {
            
            if(is_null($page))
            {
                $page = 1;
            }
            $start = ($page - 1) * $limit;
            $entries = array_slice($entries, $start , $limit);
        }
        
        return $entries;
    }
    
    
    public function entryExistsInPlaylist($playlistId, $entryId)
    {
        $playlist = $this->get($playlistId);
        if($playlist && Kms_Plugin_Access::isCurrentUser($playlist->userId))
        {
            $currentEntries = array_unique(explode(',', $playlist->playlistContent));
            if(in_array($entryId, $currentEntries))
            {
                return true;
            }
        }
        
        return false;
        
    }
    
    
    
    public function removeEntryFromPlaylist($playlistId, $entryId)
    {
        $userId = Kms_Plugin_Access::getId();
        $cacheParams = array('userId'=>$userId);
        
        $playlist = $this->get($playlistId);
        // check if we have the playlist, and that it belongs to our user
        if($playlist && Kms_Plugin_Access::isCurrentUser($playlist->userId ))
        {

            $currentEntries = array_unique(explode(',', $playlist->playlistContent));
            $key = array_search($entryId, $currentEntries);
            if(false !== $key)
            {
                unset($currentEntries[$key]);
                $newPlaylist = new Kaltura_Client_Type_Playlist();
                if(count($currentEntries))
                {
                    $newPlaylist->playlistContent = join(',',$currentEntries);
                }
                else
                {
                    $newPlaylist->playlistContent = ' ';
                }
                try
                {
                    $client = Kms_Resource_Client::getAdminClient();
                    $newPlaylist = $client->playlist->update($playlistId, $newPlaylist);
                    Kms_Resource_Cache::apiClean('playlists', $cacheParams, array('playlist_'.$newPlaylist->id));
                    return $newPlaylist;
                }
                catch(Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('playlist: Failed to update playlist '.$playlist->name.', entries: '.Kms_Log::printData($entriesArray) . ' - '. $e->getCode().': '.$e->getMessage());
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
                
            }
        }
    }
    
    /**
     *  add entries to a playlist
     *
     *  @param string $playlistId
     *  @param array $entries - array of Kaltura_Client_Type_BaseEntry to be published
     */
    function addEntriesToPlaylist($playlistId, array $entries)
    {
        $userId = Kms_Plugin_Access::getId();
        $cacheParams = array('userId'=>$userId);
        
        $entryModel = Kms_Resource_Models::getEntry();
        $entriesArray = array();
        $playlist = $this->get($playlistId);

        // determine if the entries can be published to playlists
        foreach ($entries as $entry) {
            $canPublish = true;
            // check if this entry handled by a module
            if ($entryModel->handleEntryByModule($entry)) {
                $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
                foreach ($models as $model){
                    // get the module handling this particular type
                    if ($model->isHandlingEntryType($entry)) {
                        // call on the module implementing the entry type to determine if it can be published to a playlist                   
                        $canPublish = $model->canPublishToPlaylist($entry);
                    }
                }
            }
            if ($canPublish) {
                $entriesArray[] = $entry->id;
            }
        }

        // check if we have the playlist, and that it belongs to our user
        if($playlist && $playlist->userId == $userId && !empty($entriesArray))
        {
            $currentEntries = explode(',', $playlist->playlistContent);
            // merge and unique the current and the new entries
            $newEntriesArray = array_unique(array_merge($currentEntries, $entriesArray));
            $newPlaylist = new Kaltura_Client_Type_Playlist();
            $newPlaylist->playlistContent = join(',',$newEntriesArray);
            
            try
            {
                $client = Kms_Resource_Client::getAdminClient();
                $newPlaylist = $client->playlist->update($playlistId, $newPlaylist);
                Kms_Resource_Cache::apiClean('playlists', $cacheParams, array('playlist_'.$newPlaylist->id));
                return $newPlaylist;
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('playlist: Failed to update playlist '.$playlist->name.', entries: '.Kms_Log::printData($entriesArray) . ' - '. $e->getCode().': '.$e->getMessage());
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
            //$entries = join(',', $entriesArray);
        }
    }
    
    function updatePlaylist($playlistId, $entriesArray)
    {
        $userId = Kms_Plugin_Access::getId();
        $cacheParams = array('userId'=>$userId);
        $playlist = $this->get($playlistId);
        
        if(!is_array($entriesArray))
        {
            $entriesArray = array($entriesArray);
        }
        // check if we have the playlist, and that it belongs to our user, and that it  is a static playlist
        if($playlist && $playlist->userId == $userId && $playlist->playlistType == Kaltura_Client_Enum_PlaylistType::STATIC_LIST)
        {
            $newPlaylist = new Kaltura_Client_Type_Playlist();
            // merge and unique the current and the new entries
            $newPlaylist->playlistContent = join(',',array_unique($entriesArray));
            
            try
            {
                $client = Kms_Resource_Client::getAdminClient();
                $newPlaylist = $client->playlist->update($playlistId, $newPlaylist);
                Kms_Resource_Cache::apiClean('playlists', $cacheParams, array('playlist_'.$newPlaylist->id));
                return $newPlaylist;
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('playlist: Failed to update playlist '.$playlist->name.', entries: '.Kms_Log::printData($entriesArray) . ' - '. $e->getCode().': '.$e->getMessage());
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
            //$entries = join(',', $entriesArray);
        }
        
    }
    
    
    function createPlaylist($name, $entryId)
    {
        $userId = Kms_Plugin_Access::getId();
        try
        {
            $client = Kms_Resource_Client::getAdminClient();
            $playlist = new Kaltura_Client_Type_Playlist();
            $playlist->playlistType = Kaltura_Client_Enum_PlaylistType::STATIC_LIST;
            $playlist->name = $name;
            $playlist->userId = $userId;
            $playlist->playlistContent = $entryId;
            $newPlaylist = $client->playlist->add($playlist);
            $cacheParams = array('userId'=>$userId);
            Kms_Resource_Cache::apiClean('playlists', $cacheParams);
            return $newPlaylist;
            
        }
        catch(Kaltura_Client_Exception $e)
        {
            Kms_Log::log('playlist: Failed to create playlist name '.$name.', entryId: '.$entryId . ' - '. $e->getCode().': '.$e->getMessage());
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
    }
    
    function getUserPlaylists($userId, $limit = null)
    {
        $cacheParams = array('userId'=>$userId);
        $playlists = array();
        if(!$playlists = Kms_Resource_Cache::apiGet('playlists', $cacheParams))
        {
            try 
            {
                $client = Kms_Resource_Client::getUserClient();
                $pager = new Kaltura_Client_Type_FilterPager();
                $pager->pageSize = 5000;
                
                $filter = new Kaltura_Client_Type_PlaylistFilter();
                $filter->userIdEqual = $userId;
                $filter->orderBy = Kaltura_Client_Enum_PlaylistOrderBy::CREATED_AT_DESC;
                $playlists = $client->playlist->listAction($filter, $pager);
                if(isset($playlists->objects) && count($playlists->objects))
                {
                    /* hack for filtering out deleted playlists that come back from Kaltura due to a bug in syncronization */
                    foreach($playlists->objects as $key => $pl)
                    {
                        // here we filter out anything that does not have a "ready" status and is not a "static list" 
                        if($pl->status != Kaltura_Client_Enum_EntryStatus::READY || $pl->playlistType != Kaltura_Client_Enum_PlaylistType::STATIC_LIST)
                        {
                            unset($playlists->objects[$key]);
                        }
                    }
                    
                    if($limit)
                    {
                        $playlists->objects = array_slice($playlists->objects, 0, $limit);
                    }
                    
/*                    foreach($playlists as $key => $playlist)
                    {
                        if($playlist->status == Kaltura_Client_Enum_Playlist)
                    }*/
                    
                    Kms_Resource_Cache::apiSet('playlists', $cacheParams, $playlists);
                }
                else
                {
                    $playlists = array();
                }
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('playlist: Failed to get playlists. '.$e->getCode().': '.$e->getMessage());
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        return $playlists;
    }
    
    
    public function makePublic($playlists)
    {
        $ret = array();
        if(is_array($playlists) && count($playlists))
        {
            $client = Kms_Resource_Client::getAdminClientNoEntitlement();
            $rootCategory = Kms_Resource_Config::getRootCategory();
            $cat = Kms_Resource_Models::getCategory()->get($rootCategory.'>playlists', true);
            if($cat)
            {
                foreach($playlists as $playlistId)
                {
                /*    $categoryEntryFilter = new Kaltura_Client_Type_CategoryEntryFilter();
                    $categoryEntryFilter->entryIdEqual = $playlistId;
                    $p = $client->categoryEntry->listAction($categoryEntryFilter);
                    Zend_Debug::dump($p);
                    continue;*/
                    try
                    {
                        $categoryEntry = new Kaltura_Client_Type_CategoryEntry();
                        $categoryEntry->entryId = $playlistId;
                        $categoryEntry->categoryId = $cat->id;
                        $res = $client->categoryEntry->add($categoryEntry);
                        if($res && isset($res->playlistId))
                        {
                            $ret[] = $res->playlistId;
                        }
                    }
                    catch(Kaltura_Client_Exception $e)
                    {
                        Kms_Log::log('playlist: Failed to add playlists to category. '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
                    }
                }

            }
        }
        return $ret;
        
    }
    

}

