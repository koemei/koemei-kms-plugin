<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Application_Model_Entry
{

    const MY_MEDIA_PAGE_SIZE = 10;
    const MY_MEDIA_LIST_PAGE_SIZE = 20;
    
    public $id;
    public $entry = NULL;
    public $Entries;
    public $modules = array();
    private $models;
    public $pageSize = false;
    public $readyToPublish = true;
    private $_lastResultsTotal = 0;
    private $liked = NULL;
    
    const MINIMUM_SEARCH_KEYWORD_LENGTH = 1;

    function __construct()
    {
//        $this->entry = new Kaltura_Client_Type_BaseEntry();
    }

    private function createEntryCacheParams($entryId, $userId)
    {
    	return array('id' => $entryId, 'userId' => $userId);
    }
    private function setEntryCache($entry, $userId)
    {
        // save cache
        $cacheTags = array('entry_' . $entry->id, 'user_' . $userId);
        $entryModerated = $entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::AUTO_APPROVED || $entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::APPROVED;
        $entryReady = $entry->status == Kaltura_Client_Enum_EntryStatus::READY;
        // only save cache if entry is ready and approved moderation and all the fields are set
        if ($entryModerated && $entryReady) 
        {
            Kms_Resource_Cache::apiSet('entry', $this->createEntryCacheParams($entry->id, $userId), $entry, $cacheTags);
        }
    }
    /**
     *
     * @param string $id
     * @return Kaltura_Client_Type_BaseEntry
     */
     public function get($id, $getModules = true)
    {        
        $this->entry = NULL;

        $client = Kms_Resource_Client::getUserClient();
        $userId = Kms_Plugin_Access::getId();
        // try to fetch entry from cache
        $this->entry = Kms_Resource_Cache::apiGet('entry', $this->createEntryCacheParams($id, $userId));
        if (!$this->entry)
        {      
            $this->entry = $client->baseEntry->get($id);
            if(is_null($this->entry) || !$this->entry)
            {
                throw new Kaltura_Client_Exception("Cannot get entry from API", "ENTRY_ID_NOT_FOUND"); 
            }
            $this->setEntryCache($this->entry, $userId);
        }
                
        $this->id = $this->entry->id;
        // update the models plugin to include the entry
        Kms_Resource_Models::setEntry($this);
        
        // execute the modules implementing "get" for entry
        $this->models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_Get');

        if ($getModules)
        {
            foreach ($this->models as $model)
            {
                $model->get($this);
            }
        }

        //entry can be accessed if it was created by the current user or it was published to one of the categories user is entitled to 
        if (!Kms_Plugin_Access::isCurrentUser($this->entry->userId) && !$this->isPublished($this->entry))
        {
            throw new Kaltura_Client_Exception("Entry should be created by current user or user is not entitled to categories entry is published to", "ENTRY_ID_NOT_FOUND");
        }
        //if it is not an owner of entry check if it is within its scheduling window
        if (!Kms_Plugin_Access::isCurrentUser($this->entry->userId) && !self::checkScheduling($this->entry->startDate, $this->entry->endDate))
        {
        	throw new Kaltura_Client_Exception("Entry should be within its scheduling window", "ENTRY_ID_NOT_FOUND");
        }
        
        $entryPartnerId = $this->entry->partnerId;
        $currentPartnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        if ($entryPartnerId != $currentPartnerId)
        {
            throw new Kaltura_Client_Exception("Entry should be created in the same partner id", "ENTRY_ID_NOT_FOUND");
        }
        // check that all required fields have been filled
        $this->checkReadyToPublish();
        Kms_Resource_Models::setEntry($this);

        return $this->entry;
    }
    
    public static function checkScheduling($startDate, $endDate) {
    	$now = time();
    	
		//if startDate exists and in future - false
    	if (!empty($startDate) && $startDate > $now)
    	{
    		return false;
    	}
    	//if endDate exists and in past - false
    	if (!empty($endDate) && $endDate < $now)
    	{
    		return false;
    	}
    	//otherwise
    	return true; 
             
    }

    /**
     * Check if entry is ok to publish
     * 
     * @return boolean 
     */
    public function checkReadyToPublish()
    {
        $readyToPublish = true;
        $metadataConfig = Kms_Resource_Config::getSection('metadata');
        if ($metadataConfig)
        {
            // check created by
            if (isset($metadataConfig->createdByRequired) && $metadataConfig->createdByRequired == "1")
            {
                $readyToPublish = isset($this->entry->tags) && Kms_View_Helper_String::getAuthorNameFromTags($this->entry->tags);
            }

            // check description
            if (isset($metadataConfig->descriptionRequired) && $metadataConfig->descriptionRequired == "1")
            {
                $readyToPublish = isset($this->entry->description) && strlen(trim($this->entry->description));
            }

            // check tags
            if (isset($metadataConfig->tagsRequired) && $metadataConfig->tagsRequired == "1")
            {
                if (isset($this->entry->tags))
                {
                    $tags = Kms_View_Helper_String::removeAuthorNameFromTags($this->entry->tags);
                    if (!strlen($tags))
                    {
                        $readyToPublish = false;
                    }
                }
                else
                {
                    $readyToPublish = false;
                }
            }
        }
        
        $this->readyToPublish = $readyToPublish;
        
        // call on modules to modify "ready to publish" via interface
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_ReadyToPublish');
        foreach($models as $model)
        {
            $moduleReady = $model->entryReadyToPublish($this);
            if($readyToPublish && $moduleReady === false)
            {
                $readyToPublish = false;
            }
        }

        // check if this entry handled by a module
        if ($this->handleEntryByModule($this->entry)) {
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
            foreach ($models as $model)
            {
                // get the module handling this particular type
                if ($model->isHandlingEntryType($this->entry)) {
                    // call on the module implementing the entry type to determine "ready to publish"                    
                    $readyToPublish = $model->readyToPublish($this->entry, $readyToPublish);
                }
            }
        }

        $this->readyToPublish = $readyToPublish;
    }

    /**
     * Delete entry (entryId)
     * @param long $entryId 
     * @return boolean
     */
    public function delete($entryId)
    {
        $userId = Kms_Plugin_Access::getId();
        $client = Kms_Resource_Client::getUserClient();

        try
        {
            $client->baseEntry->delete($entryId);
            // invalidate the cache
            $cacheTags = array(
                'entry_' . $entryId,
                'user_' . $userId,
            );
            Kms_Resource_Cache::apiClean('entry', array('id' => $entryId), $cacheTags);
            Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags);
            Kms_Resource_Cache::apiClean('like', array('id' => $entryId, 'userId' => $userId));

            return true;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('entry: Entry delete failed. ' . $e->getCode() . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple entries by entryId, using multirequest
     * @param array $entries 
     * @return int
     */
    public function deleteMulti($entries)
    {
        // use user client because if user has no permission to delete, then we skip
        $userId = Kms_Plugin_Access::getId();
        $client = Kms_Resource_Client::getUserClient();

        // increase the client timeout to 60 sec
        $config = $client->getConfig();
        $config->curlTimeout = 60;
        $client->setConfig($config);


        if (is_array($entries) && count($entries))
        {
            if (count($entries) == 1) // only one entry, pass it on to the usual delete function
            {
                list($entryId) = $entries;
                return $this->delete($entryId);
            }
            else
            {
                $cacheTags = array(
                    'user_' . $userId,
                );
                Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags);
                try
                {
                    $client->startMultiRequest();
                    foreach ($entries as $entryId)
                    {
                        $client->baseEntry->delete($entryId);
                        $cacheTags[] = 'entry_' . $entryId;
                        Kms_Resource_Cache::apiClean('like', array('id' => $entryId, 'userId' => $userId));
					}
					// invalidate the cache
                    Kms_Resource_Cache::apiClean('entry', array('id' => $entryId), $cacheTags);
                    					
                    $res = $client->doMultiRequest();
                }
                catch (Exception $e)
                {
                    Kms_Log::log('entry: Entry delete failed. ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::ERR);
                }
                return count($entries);
            }
        }
    }

    /**
     * set the entry manually
     * @param Kaltura_Client_Type_BaseEntry $entry 
     */
    public function setEntry($entry)
    {
        if (isset($entry->id))
        {
            // running get here, in order for the modules to run as well
            $this->get($entry->id);
        }
    }

    /**
     * get the last results total number
     * @return int
     */
    public function getLastResultCount()
    {
        return $this->_lastResultsTotal;
    }

    /**
     * Return the standard entry filter
     * @return Kaltura_Client_Type_BaseEntryFilter
     */
    public static function getStandardEntryFilter($params = array())
    {

        // check validity of params
        if (!isset($params['type']))
        {
            $params['type'] = null;
        }

        if (!isset($params['sort']))
        {
            $params['sort'] = null;
        }

        if (!isset($params['keyword']))
        {
            $params['keyword'] = null;
        }

        if (!isset($params['tag']))
        {
            $params['tag'] = null;
        }

        // instantiate filter
        $filter = self::createEntryFilter($params['type']);
        
        // filter entries which are not scheduled or within their scheduling window
        $filter = self::applySchedulingOnFilter($filter);

        // apply sort filter
        $filter = self::applySortFilter($filter, $params['sort']);

        // apply keyword filter
        $filter = self::applyKeywordFilter($filter, $params['keyword']);

        $filter = self::applyTagFilter($filter, $params['tag']);

        // filter by ready status
        $filter->statusEqual = Kaltura_Client_Enum_EntryStatus::READY ;


        // filter by approved/auto approved moderation status
        $filter->moderationStatusIn = join(',', array(
                Kaltura_Client_Enum_EntryModerationStatus::APPROVED,
                Kaltura_Client_Enum_EntryModerationStatus::AUTO_APPROVED,
                Kaltura_Client_Enum_EntryModerationStatus::FLAGGED_FOR_REVIEW,
            )
        );

        return $filter;
    }

    
    public function entryAdded()
    {
        // execute the modules implementing "Kms_Interface_Model_Entry_EntryAdded" for entry
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_EntryAdded');

        foreach ($models as $model)
        {
            $model->entryAdded($this);
        }
        
    }
    
    
    /**
     * Return the standard entry pager
     * @return Kaltura_Client_Type_FilterPager
     */
    public static function getStandardEntryPager($params = array())
    {
        // instantiate pager
        $pager = new Kaltura_Client_Type_FilterPager();
        // set the page size
        if (isset($params['pageSize']) && $params['pageSize'] !== false)
        {
            $pager->pageSize = $params['pageSize'];
        }
        else
        {
            $pager->pageSize = Kms_Resource_Config::getConfiguration('gallery', 'pageSize');
        }
        return $pager;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }
    
    /**
     * Apply the scheduling param on the filter
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * 
     * @return Kaltura_Client_Type_BaseEntryFilter 
     */
    public static function applySchedulingOnFilter($filter)
    {
    	$filter->startDateLessThanOrEqualOrNull = time(); 
    	$filter->endDateGreaterThanOrEqualOrNull = time();
    	return $filter;
    }

    /**
     * Apply the sort param on the filter, based on the sort parameter
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * @param string $sort
     * @return Kaltura_Client_Type_BaseEntryFilter 
     */
    public static function applySortFilter($filter, $sort)
    {
        switch ($sort)
        {
            case 'views':
                // if video presentation then the order by cannot be by views
                if ($filter instanceof Kaltura_Client_Type_MediaEntryFilter)
                {
                    // order by plays descending
                    $filter->orderBy = Kaltura_Client_Enum_MediaEntryOrderBy::VIEWS_DESC;
                }
                else
                {
                    // order video presentations by recent
                    $filter->orderBy = Kaltura_Client_Enum_BaseEntryOrderBy::CREATED_AT_DESC;
                }
                break;
            case 'name':
                // order by name ascending (alphabetical)
                $filter->orderBy = Kaltura_Client_Enum_BaseEntryOrderBy::NAME_ASC;
                break;
            case 'like':
                // order by likes descenting
                $filter->orderBy = Kaltura_Client_Enum_BaseEntryOrderBy::TOTAL_RANK_DESC;
                break;
            case 'recent':
            default:
                // order by recent
                $filter->orderBy = Kaltura_Client_Enum_BaseEntryOrderBy::CREATED_AT_DESC;
                break;
        }
        
        // execute the modules implementing "Kms_Interface_Model_Entry_FilterSort" for entry
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_FilterSort');

        foreach ($models as $model)
        {
            $filter = $model->editSortFilter($filter, $sort);
        }
        
        return $filter;
    }

    /**
     * instantiate the kaltura entry filter ( base entry or media entry, depending on the type )
     * @param string $type
     * @return Kaltura_Client_Type_MediaEntryFilter 
     */
    public static function createEntryFilter($type)
    {
		// what is a type of media
		if ($type) 
		{
			switch ($type)
			{
				case 'video' :
					$media_type = Kaltura_Client_Enum_MediaType::VIDEO;
					break;
				case 'audio' :
					$media_type = Kaltura_Client_Enum_MediaType::AUDIO;
					break;
				case 'image' :
					$media_type = Kaltura_Client_Enum_MediaType::IMAGE;
					break;
				default :
					break;
			}
		}
		if (!empty($media_type)) 
		{
			// instantiate a media filter
			$filter = new Kaltura_Client_Type_MediaEntryFilter();
			// filter by media type
			$filter->mediaTypeEqual = $media_type;
		} 
		else 
		{
			$filter = new Kaltura_Client_Type_BaseEntryFilter();
		}
		
		$filter->typeIn = array();
		//add presentation entries if enabled
		if ((empty($type) || $type == "all" || $type == "presentation") && 
			Kms_Resource_Config::getConfiguration('application', 'enablePresentations'))
		{
			array_push($filter->typeIn, Kaltura_Client_Enum_EntryType::DATA);
		}
				
		//add media clip if needed
		if (empty($type) || $type == "all" || !empty($media_type))
		{
			array_push($filter->typeIn, Kaltura_Client_Enum_EntryType::MEDIA_CLIP);
		}
		
		$filter->typeIn = implode(',', $filter->typeIn);
		$filter = self::hookModifyFilterByType($filter, $type);
        return $filter;
    }

    /**
     * Apply the freeText on the filter, based on the keyword parameter
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * @param string $keyword
     * @return Kaltura_Client_Type_BaseEntryFilter 
     */
    public static function applyKeywordFilter($filter, $keyword)
    {
        if ($keyword)
        {
            if(strlen($keyword) >= self::MINIMUM_SEARCH_KEYWORD_LENGTH)
            {
                $filter->freeText = $keyword;
            }
        }
        return $filter;
    }

    /**
     * Apply the tagsMultiLikeAnd on the filter, based on the $tag parameter
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * @param string $tag
     * @return Kaltura_Client_Type_BaseEntryFilter 
     */
    public static function applyTagFilter($filter, $tag)
    {
        if ($tag)
        {
            $filter->tagsMultiLikeAnd = '"' . addslashes($tag) . '"';
        }
        return $filter;
    }

    public function getEntriesByTag($tag, $params = array())
    {
        // init the standard entry filter
        $filter = self::getStandardEntryFilter($params);

        // init the standard entry pager
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));

        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;

        // filter by $tag tag
        $filter->tagsMultiLikeAnd = '"' . addslashes($tag) . '"';

        // set root category
        $filter->categoriesMatchOr = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));

        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_TAG_SEARCH);
        // get the entries
        return $this->listAction($filter, $pager);
    }

    public function getEntriesByUserId($userId, $params = array())
    {
        // init the standard entry filter
        $filter = self::getStandardEntryFilter($params);

        // init the standard entry pager
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));

        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;

        // set root category
        $filter->categoriesMatchOr = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));
        
        // filter by $userId
        $filter->userIdEqual = $userId;
        
        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_USER_SEARCH);

        // get the entries
        return $this->listAction($filter, $pager);
    }

    public function getEntriesByKeyword($searchKeyword, $params = array())
    {
        // init the standard entry filter
        $filter = self::getStandardEntryFilter($params);

        // init the standard entry pager
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));

        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;

        // filter by search keyword
        // set root category
        $filter->categoriesMatchOr = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));
        
        if ($params['keyword'])
        {// add search within keyword to free text if needed
            $filter->freeText = $searchKeyword . ' ' . $params['keyword'];
        }
        else
        {
            $filter->freeText = $searchKeyword;
        }

        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_KEYWORD_SEARCH);
        // get the entries
        return $this->listAction($filter, $pager);
    }

    public function getEntriesByChannel($channelId, $params = array())
    {
        // init the standard entry filter
        $filter = self::getStandardEntryFilter($params);
        
        // init the standard entry pager
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));
        
        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;
        
        // filter by $channel channel
        $filter->categoriesIdsMatchOr = $channelId;
        
        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_CATEGORY_SEARCH);
        
        // get the entries via standard list action
        return $this->listAction($filter, $pager);
        
    }
    
    public function getEntriesByCategory($categoryId, $params = array())
    {
        // init the standard entry filter
        $filter = self::getStandardEntryFilter($params);

        // init the standard entry pager
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));

        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;

        // filter by $category category
        // we use categoryAncestorIdIn and not categoriesIdsMatchOr to receive 
        // entries of sub categories
        $filter->categoryAncestorIdIn = $categoryId;   

        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_CATEGORY_SEARCH);

        // get the entries via standard list action
        return $this->listAction($filter, $pager);
    }

    /**
     *
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * @param Kaltura_Client_Type_FilterPager $pager 
     * @param Boolean $nocache
     * @param Boolean $getModules
     * @return array
     */
    public function listAction(Kaltura_Client_Type_BaseEntryFilter $filter, Kaltura_Client_Type_FilterPager $pager, $nocache = false, $getModules = true)
    {
        $cacheParams = array();
        // build the caching params
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter, $pager);
        $userId = Kms_Plugin_Access::getId();
        $cacheParams['userId'] = $userId;
        $entryCacheTags = array();
        $entryCacheTags[] = 'user_' . $userId;
        
        if(Kms_Resource_Cache::isEnabled() && !$nocache)
        {
        	$isIds = false;	// use category ids/names
            $categories = '';
            if ($filter->categoriesMatchOr) {
                $categories = $filter->categoriesMatchOr;
            }
            else if (isset($filter->advancedSearch->categoriesMatchOr)){
                $categories = $filter->advancedSearch->categoriesMatchOr;
            }
            else if ($filter->categoriesIdsMatchOr) {
            	$isIds = true;
            	$categories = $filter->categoriesIdsMatchOr;
            }
            else if (isset($filter->advancedSearch->categoriesIdsMatchOr)){
            	$isIds = true;
                $categories = $filter->advancedSearch->categoriesIdsMatchOr;
            }
            else if (isset($filter->categoryAncestorIdIn)){
            	$isIds = true;
                $categories = $filter->categoryAncestorIdIn;
            }
            else if (isset($filter->advancedSearch->categoryAncestorIdIn)){
            	$isIds = true;
                $categories = $filter->advancedSearch->categoryAncestorIdIn;
            }

            if (!empty($categories))
            {
                // add cache tags by category
                $entryCacheTags[] = 'category_' . $categories;
                $categoryNamesArray = explode(',', $categories);
                $categoryModel = Kms_Resource_Models::getCategory();
                
                // go over the categories and fetch their ids to add to the cache tags
                foreach($categoryNamesArray as $catName)
                {
                	$catId = $catName;
                	if (!$isIds) {
	                    $categoryObj = $categoryModel->get($catName, true);
                        $catId = $categoryObj->id; 
                	}
                	$entryCacheTags[] = 'categoryId_' . $catId;
                }
            }

            if ($filter->userIdEqual)
            {
                // add cache tags by user id
                $entryCacheTags[] = 'user_' . $filter->userIdEqual;
            }
        }
        
        // try to fetch entry from cache ( in case nocache = false
        $entries = $nocache ? false : Kms_Resource_Cache::apiGet('entries', $cacheParams);
        if (!$entries)
        {
            // instantiate client
            $client = Kms_Resource_Client::getAdminClient();
            try
            {
                $entries = $client->baseEntry->listAction($filter, $pager);
                if (isset($entries->objects))
                {
                    if (!$nocache && count($entries->objects))
                    {
                        foreach ($entries->objects as $entry)
                        {
                            $entryCacheTags[] = 'entry_' . $entry->id;
                            //save each entry to cache as well
                            $this->setEntryCache($entry, $userId);
                        }

                        $cacheConfig = Kms_Resource_Config::getCacheConfig();
                        $expiry = null;
                        if (isset($cacheConfig->global) && isset($cacheConfig->global->entryListLifetime))
                        {
                            $expiry = $cacheConfig->global->entryListLifetime;
                        }
                        
                        Kms_Resource_Cache::apiSet('entries', $cacheParams, $entries, $entryCacheTags, $expiry);
                    }
                }
            }
            catch (Kaltura_Client_Exception $ex)
            {
                Kms_Log::log('entry: Error in baseEntry->listAction: ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
            }
        }
        
        if (isset($entries->objects))
        {
            $this->_lastResultsTotal = $entries->totalCount;
            
            foreach($entries->objects as $entryObj)
            {
                $newEntry = new Application_Model_Entry();
                $newEntry->entry = $entryObj;
                $newEntry->id = $entryObj->id;
                        
                $this->Entries[$newEntry->id] = $newEntry;
            }
            
            // execute the modules implementing "listAction" for entry
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_List');

            if ($getModules)
            {
                foreach ($models as $model)
                {
                    $model->listAction($this);
                }
            }
            
            
            // run check ready to publish on all entries models
            if(is_array($this->Entries) && count($this->Entries))
            {
                foreach($this->Entries as $entryModelObj)
                {
                    //Kms_Resource_Models::setEntry($entryModelObj);
                    $readyToPublish = $entryModelObj->checkReadyToPublish($this);
                }
            }
            Kms_Resource_Models::setEntry($this);
            
            return $entries->objects;
        }
        else
        {
            return array();
        }
    }

    public function clearMyMediaCache()
    {
        $userId = Kms_Plugin_Access::getId();
        $result = Kms_Resource_Cache::apiClean('entries', array(''), array('user_' . $userId));
    }

    public function getMyMedia($params = array())
    {
        $userId = Kms_Plugin_Access::getId();
        $pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));
        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;
        $filter = self::getStandardEntryFilter($params);

        $filter->userIdEqual = $userId;

        $filter->moderationStatusIn = join(',', array(
            Kaltura_Client_Enum_EntryModerationStatus::APPROVED,
            Kaltura_Client_Enum_EntryModerationStatus::AUTO_APPROVED,
            Kaltura_Client_Enum_EntryModerationStatus::FLAGGED_FOR_REVIEW,
            Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION,
            Kaltura_Client_Enum_EntryModerationStatus::REJECTED,
        ));
        $filter->statusEqual = NULL;
        $filter->statusIn = join(',', array(
            Kaltura_Client_Enum_EntryStatus::READY,
            Kaltura_Client_Enum_EntryStatus::PRECONVERT,
            Kaltura_Client_Enum_EntryStatus::PENDING,
            Kaltura_Client_Enum_EntryStatus::IMPORT,
            Kaltura_Client_Enum_EntryStatus::ERROR_CONVERTING,
            Kaltura_Client_Enum_EntryStatus::ERROR_IMPORTING,
            Kaltura_Client_Enum_EntryStatus::INFECTED,
                )
        );
        
        //don't filter by scheduling window
        $filter->startDateGreaterThanOrEqualOrNull = null;
        $filter->endDateGreaterThanOrEqualOrNull = null;

        $categoryEntryAdvancedFilter = new Kaltura_Client_Type_CategoryEntryAdvancedFilter();
        $categoryEntryAdvancedFilter->categoryEntryStatusIn = join(',', array(
            Kaltura_Client_Enum_CategoryEntryStatus::ACTIVE,
            Kaltura_Client_Enum_CategoryEntryStatus::PENDING,
            Kaltura_Client_Enum_CategoryEntryStatus::REJECTED,
        ));
        // add categories to filter
   //     $categoryEntryAdvancedFilter->categoriesMatchOr = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $filter->advancedSearch = $categoryEntryAdvancedFilter;
        
        //call hookModifyFilter to allow modules to change filter
        $filter = self::hookModifyFilter($filter, Kms_Interface_Model_Entry_ListFilter::CONTEXT_LIST_MYMEDIA);

        $entries = $this->listAction($filter, $pager);

        // if cache is enabled
        $cacheConfig = Kms_Resource_Config::getCacheConfig();

        if ($cacheConfig && $cacheConfig->global && $cacheConfig->global->cacheEnabled)
        {

            $refreshEntries = array();
            // check for changes in entries that do not have status READY
            foreach ($entries as $key => $entry)
            {
                if ($entry->status != Kaltura_Client_Enum_EntryStatus::READY)
                {
                    // remove if deleted
                    if ($entry->status == Kaltura_Client_Enum_EntryStatus::DELETED)
                    {
                        unset($entries[$key]);
                    }
                    else
                    {
                        // these are the entries that must be refreshed
                        $refreshEntries[$entry->id] = array('key' => $key, 'status' => $entry->status, 'id' => $entry->id);
                    }
                }
            }

            if (count($refreshEntries))
            {
                // get the entries to be refreshed
                $client = Kms_Resource_Client::getUserClient();
                $client->startMultiRequest();
                foreach ($refreshEntries as $entryId => $refreshEntry)
                {
                    $client->baseEntry->get($entryId);
                }

                // execute the multirequest
                try
                {
                    $result = $client->doMultiRequest();
                }
                catch (Kaltura_Client_Exception $ex)
                {
                    Kms_Log::log('entry: Unable to get entry id ' . $refreshEntry['id'] . '. ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
                    //throw new Kaltura_Client_Exception($ex->getMessage(), $ex->getCode());
                }

                // results came back
                if (isset($result) && count($result))
                {
                    $clean = false;
                    foreach ($result as $entry)
                    {
                        // check each entry if the status has changed
                        if (isset($refreshEntries[$entry->id]))
                        {
                            $entries{ $refreshEntries[$entry->id]['key'] } = $entry;
                            $clean = true;
                        }
                    }

                    if ($clean)
                    {
                        Kms_Resource_Cache::apiClean('entries', array(''), array("user_" . $userId));
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * allow modules using model hooks to keep and access data on the model
     * @param string $moduleName
     */
    public function getModuleData($moduleName)
    {
        return isset($this->modules[$moduleName]) ? $this->modules[$moduleName] : NULL;
    }

    /**
     * allow modules using model hooks to keep and access data on the model
     * @param unknown_type $data
     * @param string $moduleName
     */
    public function setModuleData($data, $moduleName)
    {
        $this->modules[$moduleName] = $data;
    }

    public function save(array $data, $admin = false)
    {
        // get the entry
        $this->get($data['id'], false);
        // check if we have permission to update (user id matches the entry user id)
        if (Kms_Plugin_Access::isCurrentUser($this->entry->userId) || $admin)
        {

            // update entry
            $entry = new Kaltura_Client_Type_BaseEntry();

            // assign the properties
            foreach ($data as $key => $value)
            {
                if (property_exists($entry, $key))
                {
                    $entry->$key = $value;
                }
            }

            $cacheTags = array();

            // @todo - no more entry->categories - cache will not be erased
            $entryOldCategories = explode(',', $this->entry->categories);

            // use admin client to save admin tags
            if (isset($data['adminTags']) || $admin)
            {
                $client = Kms_Resource_Client::getAdminClient();
            }
            else
            {
                $client = Kms_Resource_Client::getUserClient();
            }

            try
            {
                $client->startMultiRequest();
                $client->baseEntry->update($entry->id, $entry);
                $client->baseEntry->get($entry->id);
                $res = $client->doMultiRequest();
                $this->entry = isset($res[1]) ? $res[1] : NULL;
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('entry: Error saving entry ' . $this->entry->id . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
            
            // invoke modules save hooks
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_Save');
            foreach ($models as $model)
            {
                $model->save($this);
            }

            // invalidate the cache for the entry
            $cacheTags[] = 'entry_' . $this->entry->id;
            $entryCategories = explode(',', $this->entry->categories);
            // $entry = $this->get($entry->id);
            // create cache tags for entry categories
            if (Kms_Resource_Cache::isEnabled())
            {
                $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
                // iterate over all old entry and new entry cats
                $entryCategories = array_unique(array_merge($entryOldCategories, $entryCategories));
                foreach ($entryCategories as $entryCategory)
                {
                    $catNameArr = explode('>', $entryCategory);
                    if (count($catNameArr))
                    {
                        $catTagName = '';
                        foreach ($catNameArr as $value)
                        {
                            if ($value == $rootCategory)
                            {
                                // do not clean root category cache
                                if($catTagName == '')
                                {
                                    $catTagName .= $value;
                                }
                                else
                                {
                                    $catTagName .= '>' . $value;
                                }
                            }
                            else
                            {
                                if($catTagName == '')
                                {
                                    $catTagName .= $value;
                                }
                                else
                                {
                                    $catTagName .= '>' . $value;
                                }
                                $cacheTags[] = 'category_' . $catTagName;
                            }
                        }
                    }
                }
                $cacheTags = array_unique($cacheTags);
            }

            $cacheTags[] = 'user_' . $this->entry->userId;
            Kms_Resource_Cache::apiClean('entry', array('id' => $entry->id), $cacheTags);

            $entryModerated = $this->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::AUTO_APPROVED || $this->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::APPROVED;
            $entryReady = $this->entry->status == Kaltura_Client_Enum_EntryStatus::READY;

            // only save cache if entry is ready and approved moderation
            if ($entryModerated && $entryReady)
            {
                // save back into cache
                Kms_Resource_Cache::apiSet('entry', array('id' => $this->entry->id), $this->entry);
            }

            // set the model entry var to the new entry

            return $this->entry;
        }
        else
        {
            Kms_Log::log('Access denied, tried to update entry that does not belong to me', Kms_Log::WARN);
            throw new Kaltura_Client_Exception('Access denied, tried to update entry that does not belong to me', Kaltura_Client_Exception::ERROR_GENERIC);
        }
    }

    
    /**
     * toggle category for entry on/off
     * @param string $entryId
     * @param string $category
     * @return boolean
     */
    public function toggleCategory($entryId, $category)
    {
        // retrieve entry
        $this->get($entryId, false);
        // get entry categories , as array
        $entryCategories = $this->getEntryCategories($entryId);
        $cacheTags = array();
        // create a hash of categories as keys

        $categoryKeysArray = array_flip(array_keys($entryCategories));
        
        if (isset($categoryKeysArray[$category]))
        {
            // remove the category
//            Kms_Log::log('removing '.$category);
            unset($categoryKeysArray[$category]);
            $published = false;
        }
        else
        {
            // add the category
            $categoryKeysArray[$category] = '1';
            $published = $category;
        }

        
        // reverse the array keys to values
        $this->updateCategories($entryId, array_keys($categoryKeysArray));

        return $published;
    }

    /**
     * remove category from an entry
     * @param string $entryId
     * @param string $category
     * @return boolean
     */
    public function removeCategory($entryId, $category)
    {
        // retrieve entry
        $this->get($entryId, false);
        // get entry categories , as array
        $entryCategories = $this->getEntryCategories($entryId);
        $cacheTags = array();
        // create a hash of categories as keys

        $categoryKeysArray = array_flip(array_keys($entryCategories));
        
        if (isset($categoryKeysArray[$category]))
        {
            // remove the category
//            Kms_Log::log('removing '.$category);
            unset($categoryKeysArray[$category]);
            
            // reverse the array keys to values
            $result = $this->updateCategories($entryId, array_keys($categoryKeysArray));
        }
        else
        {
            Kms_Log::log('entry: skipping unpublish of '.$entryId.' because it is not published in '.$category, Kms_Log::DEBUG);
            $result = false;
        }

        

        return $result;
    }

    
    /**
     * add category for an entry
     * @param string $entryId
     * @param string|array $categoryArray
     * @return boolean
     */
    public function addCategory($entryId, $categoryIdArray)
    {
        // retrieve entry
        $this->get($entryId, false);
        // get entry categories , as array
        $entryCategories = $this->getEntryCategories($entryId);
        $cacheTags = array();
        // create a hash of categories as keys

        $categoryKeysArray = array_flip(array_keys($entryCategories));
        if(!is_array($categoryIdArray))
        {
            $categoryIdArray = array($categoryIdArray);
        }
        
        $resultcount = 0;
        
        foreach($categoryIdArray as $catId)
        {
            if (isset($categoryKeysArray[$catId]))
            {
                // remove the category
                Kms_Log::log('entry: skipping publish of '.$entryId.' because already published in '.$catId, Kms_Log::DEBUG);
                //$result = false;
            }
            else
            {
                // add the category
                $categoryKeysArray[$catId] = '1';
            }
        }
        
        if(count($categoryKeysArray))
        {
            $result = $this->updateCategories($entryId, array_keys($categoryKeysArray));
            if($result)
            {
                $resultcount ++;
            }
        }
        

        return $resultcount;
    }
    
    
    public function approve($entry)
    {
        $auth = Zend_Auth::getInstance();
        if (is_object($auth->getIdentity()))
        {
            $role = $auth->getIdentity()->getRole();
        }

        if ($role == Kms_Plugin_Access::getRole(Kms_Plugin_Access::UNMOD_ROLE) && $entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION)
        {
            /* get entry from video presentation */
            if (!isset($entry->mediaType))
            {
                $syncXML = new SimpleXMLElement($entry->dataContent);
                if ($syncXML->video && $syncXML->video->entryId && count($syncXML->video->entryId))
                {
                    $entryId = $syncXML->video->entryId->__toString();
                    $entry = $this->get($entryId);
                }
            }

            $client = Kms_Resource_Client::getAdminClient();
            try
            {
                $client->baseEntry->approve($entry->id);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('upload: Entry auto-approve failed. ' . $e->getCode() . ': ' . $e->getMessage());
                return false;
            }
        }
    }

    
    public function getPublishedInCategory($entryId, $categoryId)
    {
        if($entryId && !is_array($entryId))
        {
            $entryId = array($entryId);
        }
        
        $client = Kms_Resource_Client::getUserClient();
        $filter = new Kaltura_Client_Type_CategoryEntryFilter();
        $filter->entryIdIn = join(',', $entryId);
        $filter->categoryIdEqual = $categoryId;
        $filter->statusIn = join(',', array(Kaltura_Client_Enum_CategoryEntryStatus::ACTIVE, Kaltura_Client_Enum_CategoryEntryStatus::PENDING));
        $res = $client->categoryEntry->listAction($filter);
        if($res && isset($res->objects) && count($res->objects))
        {
            return $res->objects;
        }
        else
        {
            return array();
        }
    }
    
    
    /**
     * get channels and galleries that contain the given entry
     * @param string $entryId
     */
    public function getEntryCategories($entryId)
    {
        static $rootResult; // root categories (galleries / channels)

        $categoryModel = Kms_Resource_Models::getCategory();
        
        $client = Kms_Resource_Client::getUserClient();
        $filter = new Kaltura_Client_Type_CategoryEntryFilter();
        $filter->entryIdEqual = $entryId;
        $filter->statusIn = join(',', array(Kaltura_Client_Enum_CategoryEntryStatus::ACTIVE, Kaltura_Client_Enum_CategoryEntryStatus::PENDING));
        $categoriesForFilter = array(
                    Kms_Resource_Config::getRootGalleriesCategory(), 
                    Kms_Resource_Config::getRootChannelsCategory(),
        );
       
        $categories = Kms_Resource_Cache::apiGet('categoryEntry', array('id' => $entryId));
        if(!$categories) {
            // first fetch the root categories' full ids 
            $privateCategoryId = null;
            $rootFilter = new Kaltura_Client_Type_CategoryFilter();
            $rootFilter->fullNameIn = join(',', $categoriesForFilter);
            $rootFullIds = array();
            try {
                if(!isset($rootResult)) {
                    $rootResult = $categoryModel->getListByFilter($rootFilter);
                }
                if(isset($rootResult) && count($rootResult)) {
                    foreach($rootResult as $rootCat) {
                        if(isset($rootCat->fullIds)) {
                            $rootFullIds[] = $rootCat->fullIds;
                        }
                    }
                }
            }
            catch(Kaltura_Client_Exception $e) {
                Kms_Log::log('entry: Error listing root and private categories in EntryModel::getEntryCategories(): ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                
            }
            
            // initialize the return array (will hold categories ids)
            $categoryArray = array();
            
            // now start a multi request fetching only categories where fullid matches the $rootFullIds
            // launch each category in a separate request
            $client->startMultiRequest();
            foreach($rootFullIds as $rootFullId) {
                $filter->categoryFullIdsStartsWith = $rootFullId.'>';
                $client->categoryEntry->listAction($filter);
            }
            
            try {
                $entryCategoriesMultiResult = $client->doMultiRequest();
                
                if($entryCategoriesMultiResult && count($entryCategoriesMultiResult)) {
                    // merge the multirequest
                    foreach($entryCategoriesMultiResult as $singleMultiResult) 
                    {
                        if(isset($singleMultiResult->objects) && count($singleMultiResult->objects))
                        {
                            foreach($singleMultiResult->objects as $singleCategoryResult)
                            {
                                // double check here, also if the entry id is the right one
                                if(isset($singleCategoryResult->categoryId) && isset($singleCategoryResult->entryId) && $singleCategoryResult->entryId == $entryId)
                                {
                                    $categoryArray[] = $singleCategoryResult->categoryId;
                                }
                            }
                        }
                    }
                }
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('entry: Error getting categoryEntry list for entry: ' . (isset($this->entry->id) ? $this->entry->id : '') . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }


            $cacheTags = array(
                    'entry_'.$entryId
            );
            
            if(count($categoryArray))
            {
                foreach($categoryArray as $catId)
                {
                    $cacheTags[] = 'category_'.$catId;
                }
                
                try {
                    $filter = new Kaltura_Client_Type_CategoryFilter();
                    $filter->idIn = join(',', $categoryArray);
                    $categories = $categoryModel->getListByFilter($filter);
                }
                catch(Kaltura_Client_Exception $e) {
                    Kms_Log::log('entry: Error getting categories for entry ' . $this->entry->id . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
            else {
                $categories = array();
            }
            Kms_Resource_Cache::apiSet('categoryEntry', array('id' => $entryId), $categories, $cacheTags);
        }
        
        return $categories;
    }
    
    
    public function updateCategories($entryId, $entryCategories)
    {
        $entryOldCategories = $this->getEntryCategories($entryId);
        $client = Kms_Resource_Client::getUserClient();
        // increase the client timeout to 120 sec
        $config = $client->getConfig();
        $config->curlTimeout = 120;
        $client->setConfig($config);
        // get root category
        $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
       

        $deleteArray = array_diff(array_keys($entryOldCategories), $entryCategories);
        $addArray = array_diff($entryCategories, array_keys($entryOldCategories));
        
        $client->startMultiRequest();
        $cacheTags = array();

        // remove entry from categories
        foreach($deleteArray as $categoryId)
        {
            if($categoryId)
            {
                //check if the category belongs to our root category
                if(isset($entryOldCategories[$categoryId]) && preg_match('#^'.preg_quote($rootCategory, '#').'>.*#', $entryOldCategories[$categoryId]->fullName))
                {
                    $client->categoryEntry->delete($entryId, $categoryId);
                    $cacheTags[] = 'categoryId_'.$categoryId;
                }
            }
        }

        
        // add entry to categories
        foreach($addArray as $categoryId)
        {
            $categoryEntry = new Kaltura_Client_Type_CategoryEntry();
            $categoryEntry->entryId = $entryId;
            $categoryEntry->categoryId = $categoryId;
            $cacheTags[] = 'categoryId_'.$categoryId;
            $client->categoryEntry->add($categoryEntry);
        }
        
        $res = null;
        
        try 
        {
            $res = $client->doMultiRequest();
            $success = true;
        }
        catch(Kaltura_Client_Exception $e)
        {
            $success = false;
            Kms_Log::log('entry: Error updating categories for entry ' . $entryId . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
            if($e->getCode() == 'MAX_CATEGORIES_FOR_ENTRY_REACHED')
            {
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        $cacheTags[] = 'entry_' . $entryId;
        Kms_Resource_Cache::apiClean('entry', array('id' => $entryId), $cacheTags);
        Kms_Resource_Cache::apiClean('categoryEntry', array('id' => $entryId));
        return $success;
    }

    public function setNickname($nickname)
    {
        if ($nickname)
        {
            $this->get($this->id);
            $tags = trim($this->entry->tags);
            $entryData = array(
                'id' => $this->entry->id,
                'tags' => 'displayname_' . $nickname . ($tags ? ', ' . $tags : ''),
            );
            $this->save($entryData);
        }
    }

    public function addAdminTag($tag)
    {
        $adminTags = array();
        $this->get($this->id);
        if (isset($this->entry->adminTags) && $this->entry->adminTags)
        {
            $adminTags = array_reverse(explode(',', $this->entry->adminTags));
        }

        $adminTags[$tag] = '';
        $entryData = array(
            'id' => $this->entry->id,
            'adminTags' => join(',', array_keys($adminTags))
        );

        $this->save($entryData);
    }

    /**
     * update the like cache, invalidate entry caches after like/unlike
     * @param bool $id
     * @param bool $liked
     */
    private function refreshEntryCache($id,$liked)
    {
        $userId = Kms_Plugin_Access::getId();
        
        // change the cached entry to reflect the new `liked` status by this user
        Kms_Resource_Cache::apiSet('like', array('id' => $id, 'userId' => $userId), array($liked));

        // invalidate the entry cache
        $cacheTags = array('entry_' . $id,);
        Kms_Resource_Cache::apiClean('entry', array('id' => $id), $cacheTags);
        Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags);
        
        // reload the entry
        $this->get($id);
    }
    
    /**
     * like an entry
     * @param string $id
     * @throws Kaltura_Client_Exception
     */
    public function like($id)
    {
        $result = false;
        if (Kms_Resource_Config::getConfiguration('application', 'enableLike'))
        {
            try
            {
                $client = Kms_Resource_Client::getUserClient();    
                $likePlugin = Kaltura_Client_Like_Plugin::get($client);
                $result = $likePlugin->like->like($id);
                if ($result)
                {
                    // change the cached entry to reflect the new `liked` status by this user
                    $this->liked = true;                    
                    $this->refreshEntryCache($id,$this->liked);
                }
                Kms_Log::log('entry: entry id ' . $id . ' liked ' . $this->liked, Kms_Log::DEBUG);
            }
            catch (Kaltura_Client_Exception $ex)
            {
                // could be double like   
                Kms_Log::log('entry: Unable to like entry id ' . $id . '. ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
                $result = false;
            }
        }
        else
        {
            Kms_Log::log('entry: like feature not active', Kms_Log::NOTICE);
        }
        return $result;
    }

    /**
     * unlike an entry
     * @param string $id
     * @throws Kaltura_Client_Exception
     */
    public function unlike($id)
    {
        $result = false;
        if (Kms_Resource_Config::getConfiguration('application', 'enableLike'))
        {
            try
            {
                $client = Kms_Resource_Client::getUserClient();
                $likePlugin = Kaltura_Client_Like_Plugin::get($client);
                $result = $likePlugin->like->unlike($id);
                if ($result)
                {
                    // change the cached entry to reflect the new `unliked` status by this user
                    $this->liked = false;                    
                    $this->refreshEntryCache($id,$this->liked);
                }
                Kms_Log::log('entry: entry id ' . $id . ' unliked ' . $this->liked, Kms_Log::DEBUG);
            }
            catch (Kaltura_Client_Exception $ex)
            {
                // could be double unlike
                Kms_Log::log('entry: Unable to unlike entry id ' . $id . '. ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
                $result = false;
            }
        }
        else
        {
            Kms_Log::log('entry: like feature not active', Kms_Log::NOTICE);
        }
        
        return $result;
    }

    /**
     * check on an entry liked status
     * @param string $id
     * @throws Kaltura_Client_Exception
     * @return boolean
     */
    public function isLiked($id)
    {
        if (Kms_Resource_Config::getConfiguration('application', 'enableLike') && Kms_Plugin_Access::isLoggedIn())
        {
            // check if this entry was queried before by this user
            if (!isset($this->liked))
            {
                // check in the cache
                $userId = Kms_Plugin_Access::getId();
                $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $id, 'userId' => $userId));
                if (!$isLiked)
                {
                    // no data member - no cache - go to the api
                    try
                    {
                        $client = Kms_Resource_Client::getUserClient();
                        $likePlugin = Kaltura_Client_Like_Plugin::get($client);
                        $this->liked = $likePlugin->like->checkLikeExists($id, $userId);

                        // change the cached entry to reflect the `like` status by this user
                        Kms_Resource_Cache::apiSet('like', array('id' => $id, 'userId' => $userId), array($this->liked));
                    }
                    catch (Kaltura_Client_Exception $ex)
                    {
                        Kms_Log::log('entry: Unable to check if entry id ' . $id . '. is liked ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
                    }
                }
                else
                {
                    // cached data
                    $this->liked = $isLiked[0];
                }
            } // if (!isset($this->liked))
            Kms_Log::log('entry: entry id ' . $id . ' is liked value "' . $this->liked . '"', Kms_Log::DEBUG);
        }
        
        return $this->liked;
    }

    private static function hookModifyFilter($filter, $context)
    {
        // execute the modules implementing "Kms_Interface_Model_Entry_ListFilter" for entry
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Entry_ListFilter');


        foreach ($models as $model)
        {
            $filter = $model->modifyFilter($filter, $context);
        }


        return $filter;
    }
    
    private static function hookModifyFilterByType($filter, $type)
    {
    	// execute the modules implementing "Kms_Interface_Model_Entry_ListFilter" for entry
    	$models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
    	foreach ($models as $model)
    	{
    		$filter = $model->modifyFilter($filter, $type);
    	}
    	return $filter;
    }
    
    /**
     * check if entry is published in gallery or channels
     */
    public function isPublished(Kaltura_Client_Type_BaseEntry $entry)
    {
        $published = false;
        $entryCats = array();
        try
        {
            $entryCats = $this->getEntryCategories($entry->id);
        }
        catch(Kaltura_Client_Exception $e)
        {
            Kms_Log::log('entry: failed to get entry categories in "isPublished()" - '.$e->getCode().': '. $e->getMessage(), Kms_Log::WARN);
        }
        if(count($entryCats))
        {
            $galleriesRoot = Kms_Resource_Config::getRootGalleriesCategory();
            $channelsRoot = Kms_Resource_Config::getRootChannelsCategory();
            
            foreach($entryCats as $entryCat)
            {
                if(preg_match("#^(".preg_quote($galleriesRoot, '#').'|'.preg_quote($channelsRoot, '#').')>'.'#', $entryCat->fullName))
                {
                    $published = true;
                    break;
                }
            }
        }
        return $published;
    }
    

     /**
     * check if core KMS can handle this entry type, or should a module 
     * handle it.
     * @param Kaltura_Client_Type_BaseEntry $entry
     * @return boolean is this entry to be handled by a module
     */
    public function handleEntryByModule(Kaltura_Client_Type_BaseEntry $entry)
    {
        $renderByModule = true;
        $type = $entry->type;
        // allow entry of type livestream to be handled as regular entry by core
        if ($type == Kaltura_Client_Enum_EntryType::MEDIA_CLIP || $type == Kaltura_Client_Enum_EntryType::LIVE_STREAM)
        {
            $renderByModule = false;
        }
        elseif ($type == Kaltura_Client_Enum_EntryType::DATA && strpos("presentation", $entry->adminTags) !== false ) 
        {
            $renderByModule = false;
        }

        if ($renderByModule) {
            Kms_Log::log('entry: rendering entry by module.', Kms_Log::DEBUG);
        }

        return $renderByModule;
    }

    /**
     * get the current loaded entry, or load it if not loaded.
     * @param string $entryId
     * @throws Zend_Controller_Action_Exception
     * @return Kaltura_Client_Type_BaseEntry entry
     */
    public function getCurrent($entryId = null)
    {
       $entry = null;
    
        if (isset($this->entry))
        {
            // we have an entry
            $entry = $this->entry;
    
            // is it the right entry?
            if ($entryId != null && $this->entry->id != $entryId)
            {
                // but we want a different one
                $entry = $this->get($entryId);
            }
        }
        else
        {
            // we have no entry loaded - lets get it.
            $entry = $this->get($entryId);
        }
    
        if (empty($entry) || !isset($entry->id))
        {
            // error - we dont have an entry
            Kms_Log::log('entry: no entry ' . ($entryId ? $entryId : 'current'), Kms_Log::ERR);
            throw new Zend_Controller_Action_Exception('no entry id specified', 500);
        }
    
        return $entry;
    }
    
    public function getEntriesByIds($ids)
    {
    	$userId = Kms_Plugin_Access::getId();
    	$missed_ids = array();
    	$entries = array();
    	foreach ($ids as $id)
    	{
    		$entry = Kms_Resource_Cache::apiGet('entry', $this->createEntryCacheParams($id, $userId));
    		$entry ? array_push($entries, $entry) : array_push($missed_ids, $id);
    	}
    	
    	if (count($missed_ids) > 0)
    	{
    		//get all missed entries
    		$pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));
        
	        $filter = new Kaltura_Client_Type_BaseEntryFilter();
    	    $filter->userIdEqual = $userId;
        	$filter->idIn = implode(',', $missed_ids);
	        $client = Kms_Resource_Client::getAdminClient();
        	try
	        {
    	    	$result = $client->baseEntry->listAction($filter, $pager);
        		if (isset($result->objects))
            	{
					$entries = array_merge($entries, $result->objects);
            	}
        	}
	        catch (Kaltura_Client_Exception $ex)
			{
        		Kms_Log::log('entry: Error in baseEntry->listAction: ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
			}
    	}
        return $entries;
    }
    
    
    /**
     * retreive the id of a media flavor playable by the HTML5 player
     * @param string $entryId id of the entry for which we want a mobile flavor
     */
    public function getMobileFlavorId($entryId) {
    	$flavorId = null;
    	
   		$pager = self::getStandardEntryPager(array('pageSize' => $this->pageSize));
       
        $filter = new Kaltura_Client_Type_FlavorAssetFilter();
   	    $filter->entryIdEqual = $entryId;
       	$filter->tagsLike = 'iphone';
        $client = Kms_Resource_Client::getAdminClient();
        
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter, $pager);
        
        $flavorId = Kms_Resource_Cache::apiGet('entry', $cacheParams);
        
        if (empty($flavorId)) {
	       	try {
	  	    	$result = $client->flavorAsset->listAction($filter, $pager);
	       		if (!empty($result->objects)) {
					$flavorId = $result->objects[0]->id;
	           	}
	           	Kms_Resource_Cache::apiSet('entry', $cacheParams, $flavorId, array('entry_' . $entryId));
	       	}
	        catch (Kaltura_Client_Exception $ex) {
	       		Kms_Log::log('entry: Error in flavorAsset->listAction: ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
			}
        }
		
		return $flavorId;
    } 
	
	 /**
     * gets a uiconf object by its id
     * @param unknown_type $uiconfid
     */
    public function getUiconfById($uiconfid) {
    	$client = Kms_Resource_Client::getAdminClient();
    	try {
    		$result = $client->uiConf->get($uiconfid);
    	}
    	catch (Kaltura_Client_Exception $ex) {
       		Kms_Log::log('entry: Error in uiConf->get: ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
		}
		return $result;
    }
}

