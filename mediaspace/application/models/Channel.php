<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Channels Model - extends the Category Model for channel context.
 * 
 * @author talbone
 *
 */
class Application_Model_Channel extends Application_Model_Category
{       
    private static $valid_tree = array(
            'site' => array(
                    'galleries' => array(),
                    'channels' => array()),
            'archive' => array(),
            'playlists' => array());
    
    private static $private_categories = array('galleries','channels');
    
    /** see deployment/metadataProfiles/channelThumbnails.xml */
    const METADATA_URL_XPATH = '/metadata/ChannelThumbnails';
    const METADATA_URL_FIELD = 'ChannelThumbnails';
    
    /** see deployment/metadataProfiles/channelDetails.xml */
    const METADATA_MODERATE_XPATH = '/metadata/Moderate';
    const METADATA_MODERATE_FIELD = 'Moderate';
    
    const CHANNEL_THUMBNAILS = 7;
    const CHANNEL_THUMBNAILS_CACHE_LIFETIME = 43200; // 12 hours 
    
    const CHANNEL_USER_TYPE_ALL = 'all';
    const CHANNEL_USER_TYPE_MANAGER = 'manager';
    
    const CHANNEL_PAGE_SIZE = 1000;
    
    /**
     * get a single channel by its name or id.
     * @param string $name - the name of the category. NOT id - name.
     * @param boolean
     * @param string category id
     * @throws Kaltura_Client_Exception
     * @return Ambigous <boolean, unknown>
     */
    public function get($name = null, $absolutePath = false, $id = null)
    {        
        $fullPath = $name;
        if (!$absolutePath){
            $fullPath = Kms_Resource_Config::getRootChannelsCategory().'>'.$name;
        }
        
        $this->Category = parent::get($fullPath, true, $id);
            
        // call Kms_Interface_Model_Channel_Get
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Channel_Get');
        foreach ($models as $model)
        {
            $model->get($this);
        }
        
        return $this->Category;
    }

    
    
    /**
     * use getbyfilter to get channel by it's ID
     * @param string $id 
     */
    public function getById($id)
    {
        $channel = parent::getById($id);
        if(!empty($channel))
        {
            $this->Category = $channel;
            
            // call Kms_Interface_Model_Channel_Get
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Channel_Get');
            foreach ($models as $model)
            {
                $model->get($this);
            }
        
            return $this->Category;
        }
        else
        {
            return null;
        }
    }
    
    
    /**
     * get a list of all the channels
     * @param array $params the filter params
     * @param Kaltura_Client_Type_CategoryFilter $filter a filter to modify
     * @param Kaltura_Client_Type_FilterPager $pager the pager to use
     * @return Ambigous <boolean, unknown, multitype:unknown >
     */
    public function getChannelList(array $params = array(), Kaltura_Client_Type_CategoryFilter $filter = null, Kaltura_Client_Type_FilterPager $pager = null)
    {        
        // construct the category filter
        $rootCategory = Kms_Resource_Config::getRootChannelsCategory();
        if (is_null($pager)){    
            $pager = new Kaltura_Client_Type_FilterPager();
            $pager->pageIndex = 1;
            $pager->pageSize = Kms_Resource_Config::getConfiguration('channels', 'pageSize');
        }
        
        if (is_null($filter)){
            $filter = new Kaltura_Client_Type_CategoryFilter();
        }
        $filter->fullNameStartsWith = $rootCategory . '>';

        if (!empty($params['page'])){
            $pager->pageIndex = $params['page'];
        }
        if (!empty($params['sort'])){
            switch ($params['sort'])
            {
                case 'date':
                    $filter->orderBy = Kaltura_Client_Enum_CategoryOrderBy::CREATED_AT_DESC;
                    break;
                case 'name':
                    $filter->orderBy = Kaltura_Client_Enum_CategoryOrderBy::NAME_ASC;
                    break;
                case 'members':
                    $filter->orderBy = Kaltura_Client_Enum_CategoryOrderBy::MEMBERS_COUNT_DESC;
                    break;
            }
        }
        if (!empty($params['keyword'])){
            $filter->freeText = $params['keyword'];
        }

        // execute the modules implementing "Kms_Interface_Model_Channel_ListFilter" for catgeory
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Channel_ListFilter');
        foreach ($models as $model)
        {
            $filter = $model->modifyFilter($filter);
        }

        // get the channels
        $channels =  parent::getListByFilter($filter, $pager);
        
        return $channels;
    }
    
    
 	/**
     * @param string $channelName
     * @return Kaltura_Client_Type_Category, NULL
     */
    public function getCurrent($channelName = '', $channelId = null)
    {
    	$channel = null;
        $model = Kms_Resource_Models::getChannel();
        
        if (!empty($model->Category)) {
	        if (!empty($channelId) && $model->Category->id == $channelId) {
	            $channel = $model->Category;
	        }
	        elseif ($model->Category->name == $channelName) {
	        	$channel = $model->Category;
	        }
        }
        
        if (is_null($channel)) {
            $channel = $model->get($channelName, false, $channelId);
        }
        
        return $channel;
    }
    
    
    /**
     * get the category user objects
     * @param Kaltura_Client_Type_CategoryUserFilter $filter
     * @param array $params
     * @throws Kaltura_Client_Exception
     */
    private function getCategoryUserList(Kaltura_Client_Type_CategoryUserFilter $filter, array $params = array())
    {
        $pager = new Kaltura_Client_Type_FilterPager();
        //$pager->pageSize = 3;
        if (!empty($params['page'])){
            $pager->pageIndex = $params['page'];
        }
        if (!empty($params['pagesize'])){
            $pager->pageSize = $params['pagesize'];
        }
        $cacheTags = array();
        $channelUsers = array();
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter,$pager);
        
        if(!$results = Kms_Resource_Cache::apiGet('categoryUser', $cacheParams))
        {
            $client = Kms_Resource_Client::getUserClient();
            try
            {
                $results = $client->categoryUser->listAction($filter,$pager);
        
                if (!empty($results->objects) && count($results->objects)){
                    foreach ($results->objects as $categoryUser){
                        $cacheTags[] = 'userId_' . $categoryUser->userId;
                        $cacheTags[] = 'categoryId_' . $categoryUser->categoryId;
                    }
                }
                
                // add the filter params to the tags, in case the results are empty
                if (!empty($filter->categoryIdEqual)) {
                    $cacheTags[] = 'categoryId_' . $filter->categoryIdEqual;
                }
                 if (!empty($filter->userIdEqual)) {
                    $cacheTags[] = 'userId_' . $filter->userIdEqual;
                }

                Kms_Resource_Cache::apiSet('categoryUser', $cacheParams, $results, $cacheTags);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('channel: Failed to get categoryUser ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        if($results){
            $channelUsers = $results->objects;            
            $this->setTotalCount(isset($results->totalCount) ? $results->totalCount : 0);
        }
        
        return $channelUsers;
    }
    
    /**
     * get all the channels the user can publish to
     */
    public function getChannelsForPublish()
    {
        $channels = array();
        $rootCategoryObj = parent::get(Kms_Resource_Config::getRootChannelsCategory(), true);
        $currentUser = Kms_Plugin_Access::getId(); // we use this var only to determine the existance of user id

        if(!empty($rootCategoryObj) && !empty($currentUser))
        {
            // get all the open channels
            $filter = new Kaltura_Client_Type_CategoryFilter();
            $filter->contributionPolicyEqual = Kaltura_Client_Enum_ContributionPolicyType::ALL;
            $filter->fullIdsStartsWith = $rootCategoryObj->fullIds .'>';
            
            $pager = new Kaltura_Client_Type_FilterPager();
            $pager->pageSize = self::CHANNEL_PAGE_SIZE; 
            $channels = parent::getListByFilter($filter, $pager);
            
            // get the channels the user is manager/moderator/contributor of
            $filter = new Kaltura_Client_Type_CategoryUserFilter();
            $filter->userIdEqual = Kms_Plugin_Access::getId();
            $filter->categoryFullIdsStartsWith = $rootCategoryObj->fullIds .'>';
                        
            $permissions = array(
                        Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER,
                        Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR,
                        Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR);
            $filter->permissionLevelIn = implode(',',$permissions);
                        
            // get the category user objects
            $channelIds = array();            
            $myChannels = $this->getCategoryUserList($filter , array('pagesize' => self::CHANNEL_PAGE_SIZE));
            foreach ($myChannels as $channel){
                $channelIds[] = $channel->categoryId;
            }
            
            if (count($channelIds)){
                $filter = new Kaltura_Client_Type_CategoryFilter();
                $filter->idIn = implode(',', $channelIds);
                
                // get the actual channels
                $myChannels =  parent::getListByFilter($filter, $pager);    
            }
            
            // merge the channel lists
            $channels += array_diff_key($myChannels,$channels);   
        }
        return $channels;
    }
    
    /**
     * get a list of the user's channels
     * @param array $params
     * @param Kaltura_Client_Type_FilterPager $pager 
     */
    public function getMyChannelsList(array $params = array())
    {                      
        // create filter for my channels
        $filter = new Kaltura_Client_Type_CategoryFilter();
        
        if (isset($params['type']) && $params['type'] == 'manager')
        {
            // only manager level
            $filter->managerEqual = Kms_Plugin_Access::getId();
        }
        else {
            // member level
            $filter->memberEqual = Kms_Plugin_Access::getId();
        }
        
        // get the channels
        $channels = $this->getChannelList($params,$filter);
        $translate = Zend_Registry::get('Zend_Translate');
        
        foreach ($channels as $channel){
            // set the channel type
            $membership = parent::getMembership($channel);
            switch ($membership){
                case parent::MEMBERSHIP_OPEN:
                    $channel->type = $translate->translate('Open');
                    break;
                case parent::MEMBERSHIP_RESTRICTED:
                    $channel->type = $translate->translate('Restricted');
                    break;
                case parent::MEMBERSHIP_PRIVATE:
                    $channel->type = $translate->translate('Private');
                    break;
            }
        }
        
        return $channels;
    }
    
    /**
     * get the channel count for member and admin
     * 
     * we use the getChannelList() instead of the slightly lighter getCategoryUserList() 
     * to prevent discreptencies between the channel count and the actual channels shown.
     */
    public function getMyChannelsCount()
    {
        $channelCount = array('member' => 0, 'manager' => 0);
        
        // we dont want the actual results, just the total count
        $pager = new Kaltura_Client_Type_FilterPager();
        $pager->pageIndex = 1;
        $pager->pageSize = 1;
        
        // get the 'as member' channel count
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->memberEqual = Kms_Plugin_Access::getId();
        $myChannels = $this->getChannelList(array(),$filter,$pager);
        $channelCount['member'] = parent::getTotalCount();
        
        // get the 'as manager' channel count
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->managerEqual = Kms_Plugin_Access::getId();
        $myChannels = $this->getChannelList(array(),$filter,$pager);
        $channelCount['manager'] = parent::getTotalCount();
        
        return $channelCount;
    }
    
 
    /**
     * return the channel users of a specific type
     * @param Kaltura_Client_Type_Category $channel
     * @param string $userType
     * @param array $params
     * @throws Kaltura_Client_Exception
     */
    public function getChannelUsers(Kaltura_Client_Type_Category $channel, $userType = '', array $params = array())
    {
        $channelUsers = array();
        
        // filter
        $filter = new Kaltura_Client_Type_CategoryUserFilter();
        $filter->categoryIdEqual = $channel->id;
        $filter->orderBy = Kaltura_Client_Enum_CategoryUserOrderBy::CREATED_AT_ASC;
                
        if ($userType == self::CHANNEL_USER_TYPE_ALL){
             // all levels 
            $permissions = array(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER,
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR,
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR,
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER);
            $filter->permissionLevelIn = implode(',',$permissions);
        }
        else if ($userType == self::CHANNEL_USER_TYPE_MANAGER)
        {
            // only manager level
            $filter->permissionLevelEqual = Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER;
        }
        else {
            // all levels but manager
            $permissions = array(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER,
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR,
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR);
            $filter->permissionLevelIn = implode(',',$permissions);
        }
        
        // get the category user objects
        $channelUsers = $this->getCategoryUserList($filter,$params);
        
        return $channelUsers;
    }
    
    /**
     * saves a channel category
     * @param array $data
     */
    public function saveChannel(array $data = array())
    {   
        // get the channels root category
        $channel = parent::save($data, false, Kms_Resource_Config::getRootChannelsCategory());
        
        // save the channel details metadata
        //$this->setChannelDetails($channel, $data);
        
        if (!isset($data['id']) || empty($data['id'])){
            // done by BE now
            //$this->setCategoryUserRole($channel->id, Kms_Plugin_Access::getId(), Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER);
        
            // clean the cache by this user
            Kms_Resource_Cache::apiClean('categoryUser', array(), array('userId_' . Kms_Plugin_Access::getId()));
        }
            
        // invoke modules save hooks
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Channel_Save');
        foreach ($models as $model)
        {
            $model->save($this, $data);
        }

        return $channel;
    }
    
    
    /**
     * deletes a channel
     * @param string $channelName
     * @param int $channelId
     */
    public function delete($channelName = null, $channelId = '')
    {        
        // get the channel for the view hooks
        $channel = $this->get($channelName, false, $channelId);

        if (!empty($channel)){
        
            // invoke modules save hooks
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Channel_Delete');
            foreach ($models as $model)
            {
                $model->delete($this);
            }
                
            // delete the channel
            parent::deleteCategory($channel);
                
            // clean the cache
            $profileId = Kms_Resource_Config::getConfiguration('channels', 'channelDetailsProfileId');
            $caheTags = array('channelId_' . $channel->id);
            $cacheParams = array('profileId' => $profileId, 'channelId' => $channel->id);
            
            Kms_Resource_Cache::appClean('channelTns', array(), $caheTags);
            Kms_Resource_Cache::appClean('channelDetails', $cacheParams);
            Kms_Resource_Cache::apiClean('categoryUser', array(), array('userId_' . Kms_Plugin_Access::getId()));
        }
        else{
            Kms_Log::log('channel: channel was already deleted. name = ' . $channelName, Kms_Log::WARN);
        }
    }
    
    
    /**
     * validates that the root category fits the channel structure
     * @param string $rootCategory - the new root category to validate
     * @return array of newly created categories
     */
    public function validateRootCategoryStructure($rootCategory)
    {
        Kms_Log::log('channel: validating category tree structure ', Kms_Log::INFO);
    
        // the root category structure
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->fullNameEqual = $rootCategory;
        $category = $client->category->listAction($filter);
        $created_categories = array();
    
        // validate the category structure
        if (!empty($category->objects)){
            $category = $category->objects[0];
            $created_categories = $this->validateCategoryStructure(self::$valid_tree,$category);
    
            if (count($created_categories)){
                Kms_Log::log('channel: the following categories were created ' . implode($created_categories,' '), Kms_Log::INFO);
            }
        }
        else{
            Kms_Log::log('channel: non existing root category given ' . $rootCategory, Kms_Log::ERR);
        }
    
        return $created_categories;
    }
    
    /**
     * recursive validate a category tree by a predefined category tree
     * @param array $valid_tree
     * @param Kaltura_Client_Type_Category $rootCategory
     */
    private function validateCategoryStructure(array $valid_tree, Kaltura_Client_Type_Category $rootCategory)
    {
        Kms_Log::log('channel: validating category tree structure for category ' . $rootCategory->fullName, Kms_Log::INFO);
        
        $privacyContext = Kms_Resource_Config::getCategoryContext();
        $created_categories = array();
        // dont use entitlement - if the cat tree was there, it takes time for the new context to filter down.
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $filter = new Kaltura_Client_Type_CategoryFilter();
    
        foreach ($valid_tree as $name => $sub_tree)
        {
            // use the api and not the model to avoid cache and model interface calls
            $filter->fullNameEqual = $rootCategory->fullName . '>' .$name;
            $category = $client->category->listAction($filter);
            $category = $category->objects;
            if (!empty($category)){
                $category = $category[0];
            }
    
            // test and create the missing categories
            if (empty($category))
            {
                Kms_Log::log('channel: category ' . $filter->fullNameEqual . ' does not exist. creating it.',Kms_Log::INFO);
                
                $created_categories[$filter->fullNameEqual] = $filter->fullNameEqual;
    
                $category = new Kaltura_Client_Type_Category();
                $category->name = $name;
                $category->parentId = $rootCategory->id;
    
                $category = $client->category->add($category);
            }
            
            // make the kms root categories private
            if (in_array($category->name, self::$private_categories))
            {
                Kms_Log::log('channel: checking privacy context on ' . $category->name ,Kms_Log::INFO);
     
                // check for privacy context on categories, so privacy settings can be set
                if(empty($category->privacyContext) && empty($category->privacyContexts) && !empty($privacyContext))
                {
                    Kms_Log::log('channel: privacy context is missing on ' . $category->name ,Kms_Log::INFO);
                    
                    $this->addCategoryContext($category->fullName, $privacyContext);
                }
                
                Kms_Log::log('channel: making ' . $category->name . ' category private.',Kms_Log::INFO);
                
                $privateCategory = new Kaltura_Client_Type_Category();
                $privateCategory->privacy = Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY;
                
                $category = $client->category->update($category->id,$privateCategory);
            }
            
            // recursion
            $created_categories += $this->validateCategoryStructure($sub_tree, $category);
        }
        return $created_categories;
    }
    
    /**
     * validate that the root category has a privacy context -
     * add context to the new category, remove context from the old category.
     * @param string $rootCategory - the new root category
     */
    public function validateRootCategoryContext($rootCategory)
    {
        // the new root category
        $this->addCategoryContext($rootCategory, Kms_Resource_Config::getCategoryContext());
    
        // the old root category
        $oldRootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        if ($oldRootCategory && strcmp($rootCategory, $oldRootCategory)!=0 )
        {
            // remove the context from the category tree
            $client = Kms_Resource_Client::getAdminClientNoEntitlement();    
            $filter = new Kaltura_Client_Type_CategoryFilter();
            $filter->fullNameEqual = $oldRootCategory;
            $category = $client->category->listAction($filter);
            
            if (!empty($category->objects) && !empty($category->objects[0]))
            {
                $category = $category->objects[0];
                $this->delCategoryContext(self::$valid_tree, $category, Kms_Resource_Config::getCategoryContext());
            }
        }
    }
    
    /**
     * add to a context to a category.
     * @param unknown_type $category_name
     * @param unknown_type $context
     */
    private function addCategoryContext($category_name, $context)
    {
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->fullNameEqual = $category_name;
        $category = $client->category->listAction($filter);
    
        if (!empty($category->objects))
        {
            Kms_Log::log('channel: adding context "' . $context . '" to catgeory "' . $category_name . '".',Kms_Log::INFO);
    
            // add the context to the category current contexts
            $category = $category->objects[0];
            $contexts = explode(',',$category->privacyContext);
            if (!in_array($context, $contexts)){
                $contexts[] = trim($context);
            }
            // update the category
            $newCategory = new Kaltura_Client_Type_Category();
            $newCategory->name = $category->name;
            $newCategory->parentId = $category->parentId;
            $newCategory->privacyContext = trim(implode(',', $contexts),',');
            $client->category->update($category->id, $newCategory);
        }
    }
    
    
    /**
     * removes a context from a category tree
     * @param array $valid_tree
     * @param Kaltura_Client_Type_Category $rootCategory
     * @param unknown_type $context
     */
    private function delCategoryContext(array $valid_tree, Kaltura_Client_Type_Category $rootCategory, $context)
    {
        // dont use entitlement - if the cat tree was there, it takes time for the new context to filter down.
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $filter = new Kaltura_Client_Type_CategoryFilter();

        foreach ($valid_tree as $name => $sub_tree)
        {
            // use the api and not the model to avoid cache and model interface calls
            $filter->fullNameEqual = $rootCategory->fullName . '>' .$name;
            $category = $client->category->listAction($filter);
            $category = $category->objects;
            if (!empty($category)){
                $category = $category[0];

                // recursion
                $this->delCategoryContext($sub_tree, $category, $context); 
            }
        }
        
        Kms_Log::log('channel: checking ' . $rootCategory->name . ' for privacy context ' . $context,Kms_Log::INFO);
                 
        if(!empty($rootCategory->privacyContext))
        {
            Kms_Log::log('channel: removing privacy context from ' . $rootCategory->name ,Kms_Log::INFO);
        
            $contexts = explode(',',$rootCategory->privacyContext);
            if (($key = array_search($context, $contexts)) !== null){
                unset($contexts[$key]);
            }
            $contexts = trim(implode(',', $contexts),',');
            if (empty($contexts)){
                // set the empty value if the context is empty
                $contexts = Kaltura_Client_ClientBase::getKalturaNullValue();
            }
                        
            // update the category
            $newCategory = new Kaltura_Client_Type_Category();
            $newCategory->privacyContext = $contexts;
            $client->category->update($rootCategory->id, $newCategory);
        }
    }
    
    /**
     * 
     * @param string $categoryId
     * @param string $userId
     * @param unknown_type $permissionLevel
     * @throws Kaltura_Client_Exception
     */
    public function setCategoryUserRole($categoryId , $userId, $permissionLevel)
    {
        $client = Kms_Resource_Client::getUserClient();
        
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $categoryId;
        $categoryUser->userId = $userId;
        $categoryUser->permissionLevel = $permissionLevel;
        
        try {
            $client->categoryUser->add($categoryUser);
        }
        catch (Kaltura_Client_Exception $e){
            Kms_Log::log('channel: Failed setting user role for category ' . $categoryId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::DEBUG);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * get the user role associated with a channel. 
     * @param string $channelName
     * @param string $userId
     * @param string $channelId
     * @return integer the role
     */
    public function getUserRoleInChannel($channelName , $userId, $channelId = '')
    {
        return $this->getUserRoleInCategory($channelName, $userId, $channelId);
    }
    
    /**
     * get the channel/s metadata from the api
     * @param unknown_type $profileId
     * @param unknown_type $channelIds
     * @throws Kaltura_Client_Exception
     */
    private function getChannelMetadata($profileId, $channelIds)
    {
        $metadata = array();
         
        if (!empty($profileId))
        {
            // get the metadata from the api
            $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
            $filter->objectIdIn = $channelIds;
            $filter->metadataProfileIdEqual = $profileId;
            $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;

            $client = Kms_Resource_Client::getAdminClient();
            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

            try {
                $metadata = $metadataPlugin->metadata->listAction($filter);

                // test that we got the object from the correct profile
                if (!empty($metadata->objects) && count($metadata->objects)){
                    if ($metadata->objects[0]->metadataProfileId != $profileId){
                        Kms_Log::log('channel: Got wrong customdata for channel Ids ' . $channelIds . ' profileId ' . $profileId, Kms_Log::ERR);
                        $metadata = array();
                    }
                }
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('channel: Failed getting customdata for channel Ids ' . $channelIds . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        else
        {
            Kms_Log::log('channel: Empty profileId ' . $profileId, Kms_Log::ERR);
        }

        return $metadata;
    }
    
    /**
     * set the channel metadata in the api
     * @param string $profileId
     * @param string $channelId
     * @param SimpleXMLElement $customDataXML
     * @throws Kaltura_Client_Exception
     */
    private function setChannelMetadata($profileId, $channelId, SimpleXMLElement $customDataXML)
    {
        if (!empty($profileId))
        {
            $client = Kms_Resource_Client::getAdminClient();
            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

            // get the current metadata from the matadata api
            $metadata = $this->getChannelMetadata($profileId, $channelId);

            if (!empty($metadata->objects) && count($metadata->objects))
            {
                // existing metadata - update metadata
                try {
                    $customdataId = $metadata->objects[0]->id;
                    $metadataPlugin->metadata->update($customdataId, $customDataXML->asXML());
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('channel: Failed updating customdata for channel Id ' . $channelId . ', profileId ' . $profileId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::ERR);
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
            else{
                // no metadata - add new metadata
                try {
                    $metadataPlugin->metadata->add($profileId,Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, $channelId, $customDataXML->asXML());
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('channel: Failed adding thumbnail for channel Id ' . $channelId . ', profileId ' . $profileId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::ERR);
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
        }
        else
        {
            Kms_Log::log('channel: Empty profileId ' . $profileId, Kms_Log::ERR);
        }
    }
    
    /**
     * get the channel thumbnail urls - formatted and cached
     * @param array $channels - the channels to get the thumbnails of
     * @return array $thumbnails - the thumbnails
     * @throws Exception
     */
    public function getChannelThumbnails(array $channels)
    {
        if (!empty($channels))
        {
            $profileId = Kms_Resource_Config::getConfiguration('channels', 'channelThumbnailProfileId');

            // gererate the channel ids for the filter and cache params
            $channelIds = array();
            $caheTags = array();
            foreach ($channels as $channel){
                // create an array first, so the channels will always be in the same order - for the same cache param
                $channelIds[$channel->id] = $channel->id;
                $caheTags[] = 'channelId_' . $channel->id;
            }
            $channelIds = implode(',', $channelIds);
            $cacheParams = array('profileId' => $profileId, 'channelId' => $channelIds);

            $thumbnails = Kms_Resource_Cache::appGet('channelTns', $cacheParams);
            if ($thumbnails === false)
            {
                // we always return an array
                $thumbnails = array();

                // get the metadata from the api
                $metadata = $this->getChannelMetadata($profileId, $channelIds);

                // parse the metadata
                if (!empty($metadata) && !empty($metadata->objects))
                {
                    try {
                        foreach ($metadata->objects as $metadataObject){
                            $xmlObj = new SimpleXMLElement($metadataObject->xml);
                            $urls = $xmlObj->xpath(self::METADATA_URL_XPATH);
                            if(is_array($urls)){
                                foreach ($urls as $url){
                                    $thumbnails[$metadataObject->objectId][] = (string)$url;
                                }
                            }
                        }
                    }
                    catch (Exception $e){
                        Kms_Log::log('channel: Failed parsing thumbnail customdata for channel Ids ' . $channelIds . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                        throw new Exception($e->getMessage(), $e->getCode());
                    }
                }

                // update the cache
                Kms_Resource_Cache::appSet('channelTns', $cacheParams, $thumbnails, $caheTags,self::CHANNEL_THUMBNAILS_CACHE_LIFETIME);
            }

            foreach ($channels as &$channel){
                if (!empty($thumbnails[$channel->id])){
                    $channel->thumbnails = $thumbnails[$channel->id];
                }
            }
        
        }
        
        return $channels;
    }
    
    /**
     * create the channel thumbnail urls in the metadata
     * @param Kaltura_Client_Type_Category $channel
     */
    public function createChannelThumbnails(Kaltura_Client_Type_Category $channel)
    {
        $profileId = Kms_Resource_Config::getConfiguration('channels', 'channelThumbnailProfileId');
 
        // get the channel thumbnails urls
        $urls = $this->getChannelEntriesUrls($channel);
        
        // generate the thumbnails metadata
        $customDataXML = new SimpleXMLElement('<metadata/>');
        foreach ($urls as $url){
            $customDataXML->addChild(self::METADATA_URL_FIELD, $url);
        }
        
        $this->setChannelMetadata($profileId, $channel->id, $customDataXML);
        
        // clean the cache
        $caheTags = array('channelId_' . $channel->id);
        Kms_Resource_Cache::appClean('channelTns', array(), $caheTags);
    }
    
    /**
     * get the channel thumbnail urls from the channel entries
     * @param Kaltura_Client_Type_Category $channel
     * @return array $urls
     */
    private function getChannelEntriesUrls(Kaltura_Client_Type_Category $channel)
    {
        $urls = array();
        // use a new Entry Model - we are going to change its page size
        $entryModel = new Application_Model_Entry();
        $entryModel->setPageSize(self::CHANNEL_THUMBNAILS);
        
        // get the channel entries
        $entries = $entryModel->getEntriesByChannel($channel->id);
                
        // get the urls for the entries
        if(!empty($entries)){
            foreach ($entries as $entry){
                $urls[] = $entry->thumbnailUrl;
            }
        }
                
        return $urls;
    }
    
    /**
     * get the channel details metadata
     * This method is not in use. kept for use in near future
     *
     * @param Kaltura_Client_Type_Category $channel
     * @throws Exception
     */
    public function getChannelDetails(Kaltura_Client_Type_Category $channel)
    {
        // we always return an array
        $details = array();
        $profileId = Kms_Resource_Config::getConfiguration('channels', 'channelDetailsProfileId');
        
        if (!empty($channel) && !empty($profileId))
        {
            $cacheParams = array('profileId' => $profileId, 'channelId' => $channel->id);
        
            $details = Kms_Resource_Cache::appGet('channelDetails', $cacheParams);
            if ($details === false)
            {
                // get the metadata from the api
                $metadata = $this->getChannelMetadata($profileId, $channel->id);
        
                // parse the metadata
                if (!empty($metadata) && !empty($metadata->objects))
                {
                    try {
                        $metadataObject = $metadata->objects[0];
                        $xmlObj = new SimpleXMLElement($metadataObject->xml);                        
                        $moderate = $xmlObj->xpath(self::METADATA_MODERATE_XPATH);
                        $details['moderate'] = (string)$moderate[0] == 'yes' ? true : false;
                    }
                    catch (Exception $ex){
                        Kms_Log::log('channel: Failed parsing thumbnail customdata for channel Ids ' . $channelIds . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                        throw new Exception($e->getMessage(), $e->getCode());
                    }
                }
        
                // update the cache
                Kms_Resource_Cache::appSet('channelDetails', $cacheParams, $details);
            }
        }
        else
        {
            if (empty($profileId)){
                Kms_Log::log('channel: channelDetailsProfileId not configured' , Kms_Log::ERR);
            }
            elseif (empty($channel)){
                Kms_Log::log('channel: empty channel given' , Kms_Log::ERR);
            }
        }
        
        return $details;
    }
    
    /**
     * set the channel details metadata
     * This method is not in use. kept for use in near future
     *
     * @param Kaltura_Client_Type_Category $channel
     * @param array $data
     */
    public function setChannelDetails(Kaltura_Client_Type_Category $channel, array $data = array())
    {
        $profileId = Kms_Resource_Config::getConfiguration('channels', 'channelDetailsProfileId');

        if (!empty($channel) && !empty($profileId))
        {
            $cacheParams = array('profileId' => $profileId, 'channelId' => $channel->id);

            if (isset($data['moderate'])){
                // generate the details metadata
                $customDataXML = new SimpleXMLElement('<metadata/>');
                $customDataXML->addChild(self::METADATA_MODERATE_FIELD, $data['moderate'] ? 'yes' : 'no');

                // set the metadata in the api
                $this->setChannelMetadata($profileId, $channel->id, $customDataXML);
            }

            // clean the cache
            Kms_Resource_Cache::appClean('channelDetails', $cacheParams);
        }
        else
        {
            if (empty($profileId)){
                Kms_Log::log('channel: channelDetailsProfileId not configured' , Kms_Log::ERR);
            }
            elseif (empty($channel)){
                Kms_Log::log('channel: empty channel given' , Kms_Log::ERR);
            }
        }
    }
  
    /**
     * approve entries pending moderation
     * @param Kaltura_Client_Type_Category $channel
     * @param array $entries
     */
    public function approveChannelEntries(Kaltura_Client_Type_Category $channel, array $entries = array())
    {
        Kms_Log::log('channel: approving entries ' . implode(',', $entries),Kms_Log::DEBUG);
        
        $client = Kms_Resource_Client::getUserClient();
        
        $client->startMultiRequest();
        
        foreach ($entries as $entry)
        {
            $client->categoryEntry->activate($entry, $channel->id);
            $cacheTags[] = 'entry_' . $entry;
        }
        try
        {
            $client->doMultiRequest();
        }
        catch(Kaltura_Client_Exception $e)
        {
            Kms_Log::log('channel: Error approving entries for channel ' . $channel->name . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::WARN);
            return false;
        }
        
        // clean the entries cache
        $cacheTags[] = 'categoryId_'.$channel->id;
        Kms_Resource_Cache::apiClean('entry', array(), $cacheTags);
        Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags);
    }
    
    /**
     * reject the entries pending moderation
     * @param Kaltura_Client_Type_Category $channel
     * @param array $entries
     * @return boolean
     */
    public function rejectChannelEntries(Kaltura_Client_Type_Category $channel, array $entries = array())
    {
        Kms_Log::log('channel: rejecting entries ' . implode(',', $entries),Kms_Log::DEBUG);
        $result = true;
        
        $client = Kms_Resource_Client::getUserClient();
        
        // reject the entries from the channel
        $client->startMultiRequest();
        foreach ($entries as $entry)
        {
            $client->categoryEntry->reject($entry, $channel->id);
            $cacheTags[] = 'entry_' . $entry;
        }
        try
        {
            $client->doMultiRequest();
        }
        catch(Kaltura_Client_Exception $e)
        {
            // test if an entry is not pending 
            if ($e->getCode() == 'CANNOT_REJECT_CATEGORY_ENTRY_SINCE_IT_IS_NOT_PENDING' && count($entries) > 1)
            {
                Kms_Log::log('channel: some entries ' . implode(',',$entries) .' are not pending for channel ' . $channel->id . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::INFO);
                // this is fine by us - go on.
            }
            else{
                Kms_Log::log('channel: Error rejecting entries ' . implode(',',$entries) .' for channel ' . $channel->id . ': ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::WARN);
                return false;
            }
        }        
                
        // clean the entries cache
        $cacheTags[] = 'categoryId_'.$channel->id;
        Kms_Resource_Cache::apiClean('entry', array(), $cacheTags);
        Kms_Resource_Cache::apiClean('entries', array(''), $cacheTags); 
        return $result;
    }
}
