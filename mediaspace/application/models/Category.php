<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 *  Category Model
 */
class Application_Model_Category
{
    const MEMBERSHIP_OPEN = 1;
    const MEMBERSHIP_RESTRICTED = 2;
    const MEMBERSHIP_PRIVATE = 3;
        
    const CATEGORY_USER_NO_ROLE = 99;
    
    public $id;
    public $name;
    public $Category;
    private $totalcount = 0;
    private $modules = array();
    
    
 	/**
     * retreive a category either by its id or by its name.
     * @param string $name			category full name
     * @param boolean $absolutePath is this the whole category name (true) or only from local root (false)
     * @param int $id				category id
     */
    public function get($name = null, $absolutePath = false, $id = null) {
    	if (!empty($id)) {
    		return $this->getById($id);
    	}
    	else if (!empty($name)) {
    		return $this->getByName($name, $absolutePath);
    	}
    	return null;
    }
    
    
    /**
     * get a single category by its name.
     * @param string $name - the name of the category. NOT id - name.
     * @throws Kaltura_Client_Exception
     * @return Ambigous <boolean, unknown>
     */
    public function getByName($name, $absolutePath = false)
    {
        // gallery root
        $rootGalleryCategory = Kms_Resource_Config::getRootGalleriesCategory();
        $client = Kms_Resource_Client::getUserClient();
        $filter = new Kaltura_Client_Type_CategoryFilter();
        if($absolutePath || preg_match('/^' . preg_quote($rootGalleryCategory, '/') . '>/', $name)) {
            // get from absolute mediaspace root
            $filter->fullNameEqual = $name;
        }
        else {
            $filter->fullNameEqual = $rootGalleryCategory.'>'.$name;
        }
        
		$userId = Kms_Plugin_Access::getId();
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);
		$cacheParams['userId'] = $userId;
        
        
        if(!$this->Category = Kms_Resource_Cache::apiGet('category', $cacheParams)) {            
            try  {
                $category = $client->category->listAction($filter);
                if(isset($category->objects) && count($category->objects)) {
                    $this->Category = $category->objects[0];

                    // set the membership data
                    $this->Category->membership = $this->getMembership($this->Category);
                    // cache with list params
			        $cacheTags = array( 'categoryUser_' . $userId, 'categoryId_' . $this->Category->id, 'categories');
                    Kms_Resource_Cache::apiSet('category', $cacheParams, $this->Category, $cacheTags);
                    // cache with get params
                    $cacheParams = array( "categoryId" => $this->Category->id, "userId" => $userId);
                    Kms_Resource_Cache::apiSet('category', $cacheParams, $this->Category, $cacheTags);
                }
            }
            catch(Kaltura_Client_Exception $e) {
                Kms_Log::log('category: Failed to get category '.$name.'. '.$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        $this->name = isset($this->Category) && $this->Category && isset($this->Category->name) ? $this->Category->name : null;
        $this->id = isset($this->Category) && $this->Category && isset($this->Category->fullName) ? $this->Category->fullName : null;
        
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Category_Get');
        foreach ($models as $model) {
            $model->get($this);
        }
        return $this->Category;
    }
    
    
    
    public function getById($id)
    {
    	$category = null;
        $userId = Kms_Plugin_Access::getId();
        
    	$cacheParams = array( "categoryId" => $id, "userId" => $userId);
        $cacheTags = array( 'categoryUser_' . $userId, 'categoryId_' . $id, 'categories');
        
        if(!$category = Kms_Resource_Cache::apiGet('category', $cacheParams)) {
            $client = Kms_Resource_Client::getUserClient();
            try {
                $category = $client->category->get($id);
                
            	// set the membership data
	            if ($category){
	                $category->membership = $this->getMembership($category);
	            }
	            
            	// cache with get params
            	Kms_Resource_Cache::apiSet('category', $cacheParams, $category, $cacheTags);
            	// cache with list params
            	$cacheParams = $this->createMockCacheParams($category->fullName);
	            $cacheParams['userId'] = $userId;
            	Kms_Resource_Cache::apiSet('category', $cacheParams, $category, $cacheTags);
	    
	            // call Kms_Interface_Model_Category_Get
	            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Category_Get');
	            foreach ($models as $model) {
	                $model->get($this);
	            }
            }
            catch(Kaltura_Client_Exception $e) {
                Kms_Log::log('category: Error fetching category from API: '.$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
            }
        }
        $this->Category = $category;
        return $category;
    }
    
    
    /**
     * create cache params as if request was made using "getByName"
     * @param  $categoryName	category's full name
     */
    private function createMockCacheParams($categoryName) 
    {
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->fullNameEqual = $categoryName;
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);
        return $cacheParams;
    }
    
    
    /**
     * get a list of categories by the given filter
     * @param Kaltura_Client_Type_CategoryFilter $filter
     * @param Kaltura_Client_Type_FilterPager $pager
     * @return array with categories matching the given data
     */
    public function getListByFilter(Kaltura_Client_Type_CategoryFilter $filter, Kaltura_Client_Type_FilterPager $pager = null)
    {
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter, $pager);
        $identity = Zend_Auth::getInstance()->getIdentity();
        $userRole = null;
                
        if($identity)
        {
            $userRole = $identity->getRole();
            $userId = Kms_Plugin_Access::getId();
            $cacheParams['userId'] = $userId;
            $cacheTags = array( 'categoryUser_' . $userId, 'categories');
        }
        
        if(!$results = Kms_Resource_Cache::apiGet('categories', $cacheParams))
        {
            $cats = array();
        
            $client = Kms_Resource_Client::getUserClient();
            try
            {
                $results = $client->category->listAction($filter, $pager);
                if (isset($results->objects) && count($results->objects))
                {
                    foreach($results->objects as $obj)
                    {
                        // only keep cache tags if the category will be included in the result set
                        if($this->isCategoryAllowed($obj, $userRole))
                        {
                            $cacheTags[] = 'categoryId_' . $obj->id;
                            $cacheTags[$obj->parentId] = 'categoryId_' .$obj->parentId;
                        }
                    }
                }
                elseif (!empty($filter->fullNameStartsWith) && Kms_Resource_Cache::isEnabled())
                {
                    // generate the tags so the cache will be cleaned when the first category is created
                    // called only if the cache is enabled because it involves an api call
                    $cacheTags[] = 'categoryId_' . $this->getParentCategoryId(chop($filter->fullNameStartsWith,'>'));
                }
                                
                Kms_Resource_Cache::apiSet('categories', $cacheParams, $results, $cacheTags);
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('category: Error fetching category list from API: '.$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                //throw new Zend_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        // set the total result count for this request
        $this->totalcount = isset($results->totalCount) ? $results->totalCount : 0;
              
        // check if categories allowed
        $cats = array();
        if (isset($results->objects) && count($results->objects))
        {
            foreach($results->objects as $obj)
            {
                if($this->isCategoryAllowed($obj, $userRole))
                {
                    $cats[$obj->id] = $obj;
                }
            }
        }
        
        return $cats;
    }
    
    
    /**
     * get a list of categories in the branch starting from the given category.
     * @param string $categoryFullName	full name of the root category
     * @param int $maxDepth
     * @return array with matching categories
     */
    public function getList($categoryFullName = null, $maxDepth = null)
    {
        $filter = new Kaltura_Client_Type_CategoryFilter();
        if (is_null($categoryFullName)) {
            $categoryFullName = Kms_Resource_Config::getRootGalleriesCategory() . '>';
            // filtering for categories (i.e. navigation) - order by partner_sort_value
            $filter->orderBy = Kaltura_Client_Enum_CategoryOrderBy::PARTNER_SORT_VALUE_ASC;
        }
        
        $filter->fullNameStartsWith = $categoryFullName;
        $filter->depthEqual = $maxDepth;
        
        // execute the modules implementing "Kms_Interface_Model_Category_ListFilter" for catgeory
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Category_ListFilter');
        foreach ($models as $model)
        {
            $filter = $model->modifyFilter($filter);
        }
        
        return $this->getListByFilter($filter);
    }
    

    /**
     *
     */ 
    public function isCategoryAllowed($catObj, $userRole)
    {
         // retreive the root category
        $rootCategory = Kms_Resource_Config::getRootGalleriesCategory();
        // get list of restricted categories and the roles, from the configuration
        $restrictedCategories = Kms_Resource_Config::getConfiguration('categories', 'restricted');
        // the category's "full name" starting from mediaspace galleries root
        $categoryLocalName = str_replace($rootCategory.'>', '', $catObj->fullName);
        // iterate over the restricted categories
        if(count($restrictedCategories))
        {
            foreach($restrictedCategories as $restrictedCat)
            {
                if($restrictedCat->category == $categoryLocalName || preg_match('/^'.preg_quote($restrictedCat->category, '/').'>.*/', $categoryLocalName))
                {
                    // the role can use this category
                    if($userRole == Kms_Plugin_Access::PARTNER_ROLE || !isset($restrictedCat->roles) || !count($restrictedCat->roles) || in_array($userRole, $restrictedCat->roles->toArray()))
                    {        
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * 
     */ 
    function getCategoriesForForm()
    {
        // get list of categories
        $categories = $this->getList(null, null);
        
        // retreive the root category
        $rootCategory = Kms_Resource_Config::getRootGalleriesCategory();

        // get list of restricted categories and the roles, from the configuration
        $restrictedCategories = Kms_Resource_Config::getConfiguration('categories', 'restricted');
        
        // get the user role
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $userRole = $auth->getIdentity()->getRole();
        }
        else
        {
            $userRole = false;
        }
       
        // create an array of category names ordered by name
        $categoriesByName = array();
        if(is_array($categories))
        {
            foreach($categories as $category)
            {
                // remove the public category name from the beginning of the category
                $categoriesByName[ $category->id ] = str_replace($rootCategory.'>', '', $category->fullName);
            }
            // sort alphabetically, while maintaining the keys
            asort($categoriesByName);

            // run on all the categories
            foreach($categoriesByName as $catId => $category)
            {
                // check if category is restricted
                if(count($restrictedCategories))
                {
                    foreach($restrictedCategories as $restrictedCat)
                    {
                        if($restrictedCat->category == $category || preg_match('/^'.preg_quote($restrictedCat->category, '/').'>.*/', $category))
                        {
                            // the role can use this category
                            if($userRole != Kms_Plugin_Access::PARTNER_ROLE)
                            {
                                if(!isset($restrictedCat->roles) || count($restrictedCat->roles) || in_array($userRole, (array) $restrictedCat->roles))
                                {
                                    // role cannot use this category, we remove it from the list
                                    unset($categoriesByName[$catId]);
                                }
                            }
                        }
                        else
                        {
                            continue;
                        }
                    }
                }
            }
        }
        return $categoriesByName;
    }
    
    /**
     * save the category
     * @param array $data
     * @param unknown_type $admin
     * @param string $parentCategory - the parent category name
     * @return Kaltura_Client_Type_Category the saved category
     */
    public function save(array $data, $admin = false, $parentCategory = null)
    {                
        $client = Kms_Resource_Client::getAdminClient();
        
        // set the data
        $category = new Kaltura_Client_Type_Category();
        $category->name = $data['name'];
        $category->description = $data['description'];
        $category->tags = $data['tags'];
        $category->moderation = $data['moderation'];
        $category = $this->setMembership($category, $data['membership']);
        
        // save the category
        if (isset($data['id']) && $data['id'] != 0)
        {
            // existing category - update
            $category->id = $data['id'];
            try {
                $category = $client->category->update($data['id'], $category);
                
                // invalidate the caches
                Kms_Resource_Cache::apiClean('category', array('id' => $data['id']));
                Kms_Resource_Cache::apiClean('categories', array(''), array('categoryId_'.$data['id'], 'categoryId_'.$category->parentId));
            }
            catch (Kaltura_Client_Exception $e){
                Kms_Log::log('category: Error updating category id: ' . $data['id'] . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        else{
            // new category - add           
            $category->parentId = $this->getParentCategoryId($parentCategory);
            $category->owner = Kms_Plugin_Access::getId();                        
            try {
                $category = $client->category->add($category);
                // invalidate the categories cache by the parent id
                Kms_Resource_Cache::apiClean('categories', array(''), array('categoryId_'.$category->parentId));
            }
            catch (Kaltura_Client_Exception $e){
                Kms_Log::log('category: Error adding category to: ' . $category->parentId . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        $this->Category = $category;
        
        // invoke modules save hooks
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_Category_Save');
        foreach ($models as $model)
        {
            $model->save($this, $data);
        }
        
        return $this->Category;
    }
    
    
    /**
     * deletes a category
     * @param string $id - the category id. Not Name.
     */
    public function delete($categoryName = null, $categoryId = '')
    {
        $category = $this->getById($categoryId);
        if (!empty($category))
        {
            $this->deleteCategory($category);
        }
        else
        {
            Kms_Log::log('category: category was already deleted. id = ' . $id, Kms_Log::WARN);
        }
    }
    
    /**
     * deletes a given category and cleans its cache.
     * we need the entire category object to clean the cache of itself and its parent.
     * @param Kaltura_Client_Type_Category $category 
     * @throws Kaltura_Client_Exception
     */
    protected function deleteCategory(Kaltura_Client_Type_Category $category)
    {        
        $client = Kms_Resource_Client::getAdminClient();
    
        try
        {
            // delete the category with the flag set NOT to move its entries up the tree.
            $client->category->delete($category->id, Kaltura_Client_Enum_NullableBoolean::FALSE_VALUE);
        }
        catch (Kaltura_Client_Exception $e)
        {
            if ($e->getCode() == 'CATEGORY_NOT_FOUND'){
                // this is fine. just log.
                Kms_Log::log('category: category was already deleted. ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
            }
            else{
                Kms_Log::log('category: Failed to delete category ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }        
        
        // clean the cache
        $caheTags = array('categoryId_' . $category->id, 'categoryUser_' . Kms_Plugin_Access::getId());
        if (!empty($category) && !empty($category->parentId)){
            // clean the cache of the parent category - this will clean all the pages of list actions
            $caheTags[] = 'categoryId_' . $category->parentId;
        }
        Kms_Resource_Cache::apiClean('category', array(), $caheTags);
    }
    
    
    /**
     * get a category's id
     * @param string $categoryName - full name of the required category. 
     * 								if null, galleries root category will be used.
     * @return integer - the category id
     */
    public function getParentCategoryId($categoryName = null)
    {
        $categoryId = null;
        
        // determine the parent category name
        if (is_null($categoryName)) {
            // the default parent category
            $categoryName = Kms_Resource_Config::getRootGalleriesCategory();
        }
        
        // get the parent category id - we always use the full path here
        $category = $this->get($categoryName, true);
        
        if( isset($category->id)) {
            $categoryId = $category->id;
        }
        
        return $categoryId;
    }

    
    /**
     * calculates the category membership according to privacy, contributor membership
     * @param Kaltura_Client_Type_Category $category - the category to check
     * @return string
     */
    public function getMembership(Kaltura_Client_Type_Category $category)
    {
        $result = self::MEMBERSHIP_PRIVATE;
    
        if ($category->privacy == Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY){
            // private
            $result = self::MEMBERSHIP_PRIVATE;
        }
        else if ($category->contributionPolicy == Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION){
            // restricted
            $result = self::MEMBERSHIP_RESTRICTED;
        }
        else{
            // open
            $result = self::MEMBERSHIP_OPEN;
        }
        return $result;
    }
    
    /**
     * set the category attributes according to membership
     * @param Kaltura_Client_Type_Category &$category
     * @param int $membership
     */
    public function setMembership(Kaltura_Client_Type_Category &$category, $membership)
    {
        switch($membership)
        {
            case self::MEMBERSHIP_OPEN:
                $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;        // view
                $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::ALL;  // contrib
                $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;     // list
                break;
            case self::MEMBERSHIP_RESTRICTED:
                $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
                $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
                $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
                break;
            case self::MEMBERSHIP_PRIVATE:
                $category->privacy = Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY;
                $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
                $category->appearInList = Kaltura_Client_Enum_AppearInListType::CATEGORY_MEMBERS_ONLY;
                break;
        }
        return $category;
    }
    
    /**
     * get the user role associated with a category.
     * first search by category id, then by category name.
     * @param string $categoryName
     * @param string $userId
     * @param string $categoryId
     * @return integer the role
     */
    public function getUserRoleInCategory($categoryName, $userId, $categoryId = '')
    {
        Kms_Log::log('category: getting role for category ' . $categoryName  . ' user ' . $userId, Kms_Log::DEBUG);
    
        // we use a static member because this method can be called several times with the same parameters
        static $rolesByName;
        static $rolesById;
        
        $result = null;
        if (!empty($categoryId)) {
	        $result = $rolesById[$categoryId][$userId];
        }
        
        if (empty($result) && !empty($rolesByName[$categoryName]) ) {
	        $result = $rolesByName[$categoryName][$userId];
        }
        
        if (empty($result))
        {
            $role = self::CATEGORY_USER_NO_ROLE;
            // get the channel itself - need it for it's id
            $category = $this->get($categoryName, false, $categoryId);
            
            if (!empty($category)) {            
                // we have access to this channel - check what role
                $client = Kms_Resource_Client::getUserClient();
                try {
                    $categoryUser = $client->categoryUser->get($category->id, $userId);
                    $role = $categoryUser->permissionLevel;
                    
                    Kms_Log::log('category: role for category ' . $category->id  . ' user ' . $userId . ' role ' . $role , Kms_Log::DEBUG);
                }
                catch (Kaltura_Client_Exception $e){
                    Kms_Log::log('category: No user role for category ' . $categoryName .  ' user ' . $userId .', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::DEBUG);
                    // no role / error - do noting. just return null.
                }
                
	            $rolesById[$category->id][$userId] = $role;
	            $rolesByName[$category->name][$userId] = $role;
                $result = $role;
            }
        }
        
        return $result;
    }
    
    /**
     * get the total result count
     * @return number
     */
    public function getTotalCount()
    {
        return $this->totalcount;
    }
    
    /**
     * set the total result count
     * @param number $count
     */
    protected function setTotalCount($count)
    {
        $this->totalcount = $count;
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
}

