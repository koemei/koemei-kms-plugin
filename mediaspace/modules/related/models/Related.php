<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Related_Model_Related extends Kms_Module_BaseModel implements Kms_Interface_Model_Entry_Get
{
    
    private $Related = null;
    
    public $existsInCache = false;
    
    const MODULE_NAME = 'related';
    /* view hooks */
    public $viewHooks = array
        (
            'PlayerSideTabs' => array
            (
                'action' => 'index', 
                'controller' => 'index',
                'order' => 20,
            ),
            'PlayerSideTabLinks' => array
            (
                'action' => 'tab', 
                'controller' => 'index',
                'order' => 20,
            )
        );
    /* end view hooks */

    
   /**
    *
    * @param Application_Model_Entry $model
    * @return type Object
    */
    public function get(Application_Model_Entry $model)
    {
        $entryId = $model->id;
        
        $results = $this->existsInCache($model->entry);
        
        if($results)
        {
            $model->setModuleData($results->objects, 'Related');
            $this->Related = $results->objects;
        }
    }
    
    public function getRelated(Application_Model_Entry $model)
    {
        $results = $this->getRelatedEntries($model->entry);
        
        if(isset($results) && is_array($results))
        {
            $model->setModuleData($results, 'Related');
            $this->Related = $results;
        }
        return $this->Related;
        
    }
    
    /**
     *
     * @param Kaltura_Client_Type_BaseEntry $entry 
     * @return Kaltura_Client_Type_BaseEntryBaseFilter $filter
     */
    private function buildRelatedFilter(Kaltura_Client_Type_BaseEntry $entry)
    {
        $filter = Application_Model_Entry::getStandardEntryFilter();
        $filter->categoriesMatchOr = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));
        $filter->tagsMultiLikeOr = preg_replace('/^displayname_[^,]+/', '', addslashes($entry->tags));
        switch( Kms_Resource_Config::getModuleConfig('related', 'orderBy'))
        {
            case 'recent':
                $filter->orderBy = Kaltura_Client_Enum_MediaEntryOrderBy::CREATED_AT_DESC;
            break;
            case 'name':
                $filter->orderBy = Kaltura_Client_Enum_MediaEntryOrderBy::NAME_ASC;
            break;
            case 'views':
            default:
                if($entry instanceof Kaltura_Client_Type_MediaEntry)
                {// if media entry
                    $filter->orderBy = Kaltura_Client_Enum_MediaEntryOrderBy::VIEWS_DESC;
                }
                else
                {   // if video presentation
                    $filter->orderBy = Kaltura_Client_Enum_MediaEntryOrderBy::CREATED_AT_DESC;   
                }
            break;
        }

    	$filter->idNotIn = $entry->id;
        
        return $filter;
    }
    
    private function getRelatedEntries($entry)
    {
    	$result = null;
        $pager = new Kaltura_Client_Type_FilterPager();
        $filter = $this->buildRelatedFilter($entry);
    	
    	if($entry->tags && trim(preg_replace('/^displayname_[^,]+/', '', addslashes($entry->tags)))) {
	        $entryModel = Kms_Resource_Models::getEntry();
	        $result = $entryModel->listAction($filter, $pager);
        }
        
    	if(empty($result) || !is_array($result)) {
        	// keep empty object in cache to avoid future cache misses, as entry.list doesn't keep empty in cache.
        	$result = new stdClass();
	        $result->objects = array();
	        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter, $pager);
	        Kms_Resource_Cache::apiSet('entries', $cacheParams, $result, array('entry_'.$entry->id));
        }
        else {
        	// remove related media that was published to a restricted category only
            $result = self::filterRestrictedEntries($result);
            // sort the results by tag relevance and limit them to limit
            $limit = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'limit');
            $tagArr = explode(',', $entry->tags);
            $tagSorter = new Kms_View_Helper_TagSorter();
            // sort
            $result = $tagSorter->tagSort($tagArr, $result , $limit);
        }
        return $result;
    }
    
    public static function filterRestrictedEntries($results)
    {
        $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $restrictedCats = Kms_Resource_Config::getConfiguration('categories', 'restricted');
        if(count($restrictedCats))
        {
            foreach($results as $key => $relatedEntry)
            {
                $relatedEntryCategories = explode(',', $relatedEntry->categories);
                $catCount = count($relatedEntryCategories);
                if($catCount)
                {
                    foreach($relatedEntryCategories as $relatedEntryCategory)
                    {
                        foreach($restrictedCats as $restrictedCat)
                        {
                            if($restrictedCat->category == $relatedEntryCategory || preg_match('/^'.preg_quote($rootCategory.'>'.$restrictedCat->category).'>.*/', $relatedEntryCategory))
                            {
                                $catCount--;
                            }
                        }
                    }
                    if($catCount <= 0)
                    {
                        unset($results{$key});
                    }
                }
            }
        }
        return $results;

    }
    
    
    public function existsInCache($entry)
    {
        $filter = $this->buildRelatedFilter($entry);
	
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);
        return Kms_Resource_Cache::apiGet('related', $cacheParams);
    }
    
    public function getAccessRules()
    {
        $accessrules = array(
            array(
                    'controller' => 'related:index',
                    'actions' => array('index', 'related', 'tab'),
                    'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
            
        );
        
        return $accessrules;
    }
   
    
}

