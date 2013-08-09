<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Module to handle the closed captions feature.
 * The module handles:
 * 1. upload of captions to entry.
 * 2. searching of captions inside an entry.
 * 3. enhancing global KMS search to include captions search.
 * 
 * @author talbone
 *
 */
class Captions_Model_Captions extends Kms_Module_BaseModel implements Kms_Interface_Model_Entry_Get, Kms_Interface_Functional_Search_ModifyGallery, Kms_Interface_Functional_Search_ModifyGlobal
{
    const MODULE_NAME = 'Captions';
    const CAPTION_ASSETS_PAGE_SIZE = 20;
        
    private $_translate = null;
    
    function __construct()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
    }

    /* view hooks */
    public $viewHooks = array
    (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array( 
                    'action' => 'header',
                    'controller' => 'index', 
                    'order' => 20
            ),

            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array(
                    'action' => 'entrytab',
                    'controller' => 'index',
                    'order' => 40
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array(
                    'action' => 'entry',
                    'controller' => 'search',
                    'order' => 40
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_EDIT_ENTRY_TABLINKS => array(
                    'action' => 'edittab',
                    'controller' => 'index',
                    'order' => 60,
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_EDIT_ENTRY_TABS => array(
                    'action' => 'edit',
                    'controller' => 'index',
            ),
             Kms_Resource_Viewhook::CORE_VIEW_HOOK_GALLERY_SEARCHES => array(
                    'action' => 'filter-bar',
                    'controller' => 'search',
            ),
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
                        'controller' => 'captions:index',
                        'actions' => array('header'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
                array(
                        'controller' => 'captions:index',
                        'actions' => array('entrytab'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
                array(
                        'controller' => 'captions:index',
                        'actions' => array('edittab', 'edit', 'delete', 'setdefault', 'download', 'upload', 'change', 'mymediasearch'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'captions:search',
                        'actions' => array('entry', 'global', 'category','filter-bar'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
                array(
                        'controller' => 'captions:search',
                        'actions' => array('my-media'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
        ); 
        return $accessrules;
    }
    
   /**
    * (non-PHPdoc)
    * @see Kms_Interface_Model_Entry_Get::get()
    */
    public function get(Application_Model_Entry $model)
    {
        if (isset($model->entry) && isset($model->entry->id))
        {
            // test to see if we have the captions set for this entry            
            $hasCaptions = $this->checkCaptions($model->entry->id);
            $model->setModuleData(array($model->entry->id => $hasCaptions), self::MODULE_NAME);
        }
    }
    

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Search_ModifyGallery::getLinkText()
     */
    public function getLinkText()
    {
        return $this->_translate->translate('Captions');
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Search_ModifyGallery::getDefaultText()
     */
    public function getDefaultText()
    {
        return '';
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Search_ModifyGlobal::getGlobalLinkText()
     */
    public function getGlobalLinkText()
    {
        return $this->_translate->translate('Captions');
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Search_ModifyGlobal::getGlobalSearchAction()
     */
    public function getGlobalSearchAction()
    {              
        return '/captions/search/global/keyword/';
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Search_ModifyGlobal::getGlobalDefaultText()
     */
    public function getGlobalDefaultText()
    {
        return '';
    }

    /**
     * get the supported languages for caption asset files
     */
    public static function getAvailableLanguages()
    {
        static $languages;
        if (!isset($languages)){
            $reflection = new ReflectionClass('Kaltura_Client_Enum_Language');
            $languages = $reflection->getConstants();    
            $languages =  array_combine(array_values($languages), array_values($languages));
        }
        return $languages;
    }


    /**
    * get an entry assets languages
    * @param string $entryId
    * @return array $languages - the languages of the entry caption assets
    */
    public function getEntryLanguages($entryId)
    {
        $languages = array();

        // we want ALL the assets
        $params = array('pagesize' => 500);
        $assets = $this->getCaptionAssets($entryId, array());

        // compose the entry languages list
        if (!empty($assets->objects)) {
            foreach ($assets->objects as $asset) {
                $languages[$asset->language] = $asset->language;
            }
        }
        
        return $languages;
    }

    /**
     * get the current loaded entry, or load it if not loaded.
     * @param string $entryId
     * @throws Zend_Controller_Action_Exception
     * @return Kaltura_Client_Type_BaseEntry entry
     */
    public function getEntry($entryId = null)
    {
        $model = Kms_Resource_Models::getEntry();
        return $model->getCurrent($entryId);
    }
    
    /**
     * check if this entry has captions.
     * @param string $entryId
     * @return bool if an entry has captions
     */
    public function hasCaptions($entryId)
    {
        $model = Kms_Resource_Models::getEntry();
        $data = $model->getModuleData(self::MODULE_NAME) ? $model->getModuleData(self::MODULE_NAME) : array();
        return $data[$entryId];
    }
    
    /**
     * check if an entry has captions in the api/cache
     * @param string $entryId
     * @throws Kaltura_Client_Exception
     * @return boolean
     */
    private function checkCaptions($entryId)
    {                  
        $filter = new Kaltura_Client_Type_AssetFilter();
        $filter->entryIdEqual = $entryId;
         
        $pager = new Kaltura_Client_Type_FilterPager();
        $pager->pageIndex = 1;
        $pager->pageSize = 1;

        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter,$pager);

        if (!$results = Kms_Resource_Cache::apiGet('hasCaptions', $cacheParams))
        {
            $client = Kms_Resource_Client::getUserClient();
            $plugin = Kaltura_Client_Caption_Plugin::get($client);

            try
            {
                $results = $plugin->captionAsset->listAction($filter,$pager);
                $cacheTags = array('entry_' . $entryId);
                Kms_Resource_Cache::apiSet('hasCaptions', $cacheParams, $results, $cacheTags);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('captions: Failed to get captions ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }

        return !empty($results->objects);
    }
    
    /**
     * get the entry caption assets
     * @param unknown_type $entryId
     * @param unknown_type $params
     * @throws Kaltura_Client_Exception
     */
    public function getCaptionAssets($entryId, $params)
    {
        // the results array
        $results = array();
        
        // entry filter
        $filter = new Kaltura_Client_Type_AssetFilter();
        $filter->entryIdEqual = $entryId;
         
        // pager
        $pager = new Kaltura_Client_Type_FilterPager();
        $pager->pageIndex = 1;                
        if (!empty($params['page'])){
            $pager->pageIndex = $params['page'];
        }
        if (!empty($params['pagesize'])){
            $pager->pageSize = $params['pagesize'];
        }
        
        $cacheParams = Kms_Resource_Cache::buildCacheParams($filter,$pager);
        
        if (!$results = Kms_Resource_Cache::apiGet('captionAssets', $cacheParams))
        {
            $client = Kms_Resource_Client::getUserClient();
            $plugin = Kaltura_Client_Caption_Plugin::get($client);
        
            try
            {                
                $results = $plugin->captionAsset->listAction($filter,$pager);
                $cacheTags = array('entry_' . $entryId);
                Kms_Resource_Cache::apiSet('captionAssets', $cacheParams, $results, $cacheTags);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('captions: Failed to get captions ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
                
        return $results;
    }
    
    /**
     * delete a caption asset from an entry
     * @param string $entryId - to clean the cache
     * @param string $captionAssetId - to delete
     */
    public function deleteCaptionAsset($entryId, $captionAssetId)
    {
        $results = false;
        
        $client = Kms_Resource_Client::getAdminClient();
        $plugin = Kaltura_Client_Caption_Plugin::get($client);
        
        try
        {
            $plugin->captionAsset->delete($captionAssetId);
            $results = true;
        }
        catch (Kaltura_Client_Exception $e)
        {
            if ($e->getCode() == 'CAPTION_ASSET_ID_NOT_FOUND') {
               $results = true;
            }
            Kms_Log::log('captions: Failed to delete caption asset ' . $captionAssetId . ' for entry ' . $entryId . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
        }

        $cacheTags = array('entry_' . $entryId);
        Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);

        return $results;
    }
    
    /**
     * saves a caption asset
     * @param array $data
     */
    public function saveCaptionAsset(array $data)
    {
        $result = null;
    
        $client = Kms_Resource_Client::getAdminClient();
        $plugin = Kaltura_Client_Caption_Plugin::get($client);
            
        if (isset($data['captionAssetId']))
        {
            // update exsting caption asset
            $currentAsset = $this->getCaptionasset($data['captionAssetId']);
            
            if (!empty($currentAsset))
            {
                try 
                {
                    $captionAsset = new Kaltura_Client_Caption_Type_CaptionAsset();
                    $captionAsset->label = $data['label'];
                    $captionAsset->language = $data['language'];
                    
                    $result = $plugin->captionAsset->update($data['captionAssetId'], $captionAsset);
                    
                    $cacheTags = array('entry_' . $data['entryId']);
                    Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);
                }
                catch (Kaltura_Client_Exception $e)
                {
                    $cacheTags = array('entry_' . $data['entryId']);
                    Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);

                    Kms_Log::log('captions: Failed to update caption asset for entry ' . $data['entryId'] . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
        }
        else
        {
            // new caption asset
            try 
            {
                $captionAsset = new Kaltura_Client_Caption_Type_CaptionAsset();
                $captionAsset->label = $data['label'];
                $captionAsset->language = $data['language'];
                $captionAsset->fileExt = $data['type'];
                $captionAsset->format = strtolower($data['type']) == 'srt' ? Kaltura_Client_Caption_Enum_CaptionType::SRT : Kaltura_Client_Caption_Enum_CaptionType::DFXP;
                
                $captionAsset = $plugin->captionAsset->add($data['entryId'], $captionAsset);
                            
                if (!empty($captionAsset))
                {
                    $contentResource = new Kaltura_Client_Type_UploadedFileTokenResource();
                    $contentResource->token = $data['token'];
                    
                    $result = $plugin->captionAsset->setContent($captionAsset->id, $contentResource);                
                }
                
                $cacheTags = array('entry_' . $data['entryId']);
                Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);
            }
            catch (Kaltura_Client_Exception $e)
            {
                $cacheTags = array('entry_' . $data['entryId']);
                Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);

                Kms_Log::log('captions: Failed to save caption asset for entry ' . $data['entryId'] . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        return $result;
    }

    
    /**
     * set an asset as the default one
     * @param string $entryId - to clean the cache
     * @param string $captionAssetId - to set as default
     */
    public function setDefaultAsset($entryId, $captionAssetId)
    {
        $results = false;
        
        $client = Kms_Resource_Client::getAdminClient();
        $plugin = Kaltura_Client_Caption_Plugin::get($client);
        
        try
        {
            $plugin->captionAsset->setAsDefault($captionAssetId);    
            $results = true;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('captions: Failed to set default caption asset ' . $captionAssetId . ' for entry ' . $entryId . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
        }
        
        $cacheTags = array('entry_' . $entryId);
        Kms_Resource_Cache::apiClean('captionAssets', null, $cacheTags);

        return $results;
    }
    
    /**
     * get the dowload url of a caption asset.
     * @param string $captionAssetId
     */
    public function getDownloadLink($captionAssetId)
    {
        $results = '';
        
        $client = Kms_Resource_Client::getUserClient();
        $plugin = Kaltura_Client_Caption_Plugin::get($client);
        
        try
        {
            $results = $plugin->captionAsset->getUrl($captionAssetId);
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('captions: Failed to get dowload link for caption asset ' . $captionAssetId . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
        }
        
        return $results;
    }
    
    
    /**
     * get a single Caption Asset straight from the api - no cache.
     * @param unknown_type $captionAssetId
     * @return Ambigous <NULL, unknown, string, unknown, multitype:Ambigous <string, unknown> >
     */
    public function getCaptionasset($captionAssetId)
    {
        $results = NULL;
        
        $client = Kms_Resource_Client::getAdminClient();
        $plugin = Kaltura_Client_Caption_Plugin::get($client);
        
        try
        {
            $results = $plugin->captionAsset->get($captionAssetId);
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('captions: Failed to get caption asset ' . $captionAssetId . ' ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
        }
        
        return $results;
    }
    
    
    /**
     * search for captions in entry or entries
     * @param array $params
     * @throws Kaltura_Client_Exception
     * @return array $results
     */
    public function search(array $params = array())
    {
        // the search results array
        $results = array();
        
        // check for the presence of search keyword
        if (empty($params['keyword'])){
            return $results;
        }
        
        // captionAssetItem filter object - for filter by language, and keyword
        $captionAssetItemFilter = new Kaltura_Client_CaptionSearch_Type_CaptionAssetItemFilter();
        if (!empty($params['keyword'])){
            $captionAssetItemFilter->contentMultiLikeOr = addcslashes($params['keyword'],'!"\\');
        }
        if (!empty($params['lang'])) {
            $captionAssetItemFilter->languageEqual = $params['lang'];
        }

        // baseEntry filter - for filtering by entry, category and sorting
        $entryFilter = new Kaltura_Client_Type_BaseEntryFilter();
        if (!empty($params['entryId'])){
            $entryFilter->idEqual = $params['entryId'];
        }
        if (!empty($params['categoryId'])){
            // filter by category id
            $entryFilter->categoriesIdsMatchOr = $params['categoryId'];
        }
        else if (!empty($params['categoryName'])){
            // filter by category name
	        $entryFilter->categoriesMatchOr = $params['categoryName'];
        }
        
        // sort filter
        if (empty($params['sort'])){
            $params['sort'] = Kaltura_Client_Enum_MediaEntryOrderBy::CREATED_AT_DESC;
        }
        $model = Kms_Resource_Models::getEntry();
        $entryFilter = $model->applySortFilter($entryFilter,$params['sort']);


        $captionAssetItemPager = new Kaltura_Client_Type_FilterPager();
        $captionAssetItemPager->pageIndex = 1;
        if (!empty($params['page'])){
            $captionAssetItemPager->pageIndex = $params['page'];
        }
        if (!empty($params['pagesize'])){
            $captionAssetItemPager->pageSize = $params['pagesize'];
        }
                
        $cacheParams = $this->buildCacheParams($entryFilter, $captionAssetItemFilter, $captionAssetItemPager, $params);
        
        if(!$results = Kms_Resource_Cache::apiGet('captionSearch', $cacheParams))
        {

            if (!empty($params['userId'])){
                // we have user id - show only our results (my media search)
                $client = Kms_Resource_Client::getUserClient();
                $plugin = Kaltura_Client_CaptionSearch_Plugin::get($client);
            }
            else{
                // no user id - show all results (all other searches)
                $client = Kms_Resource_Client::getAdminClient();
                $plugin = Kaltura_Client_CaptionSearch_Plugin::get($client);
            }

            try {
                // get the search results
                $results = $plugin->captionAssetItem->search($entryFilter, $captionAssetItemFilter, $captionAssetItemPager);
                
                // group the results by entry
                $results = $this->groupAssetsByEntry($results);

                $cacheTags = $this->buildCacheTags($results, $cacheParams);
                Kms_Resource_Cache::apiSet('captionSearch', $cacheParams, $results, $cacheTags);
            }
            catch (Kaltura_Client_Exception $e){
                Kms_Log::log('captions: Failed to search captions ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                //throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode()); - this can be miscomposed query (escaping)
            }
        }
         
        return $results;
    }
    
    
    /**
     * build the cache params
     * @param Kaltura_Client_Type_BaseEntryFilter $entryFilter
     * @param Kaltura_Client_CaptionSearch_Type_CaptionAssetItemFilter $captionAssetItemFilter
     * @param Kaltura_Client_Type_FilterPager $pager
     * @param array $params
     */
    private function buildCacheParams(  Kaltura_Client_Type_BaseEntryFilter $entryFilter = null, 
                                        Kaltura_Client_CaptionSearch_Type_CaptionAssetItemFilter $captionAssetItemFilter = null,
                                        Kaltura_Client_Type_FilterPager $pager = null,
                                        Array $params = array())
    {
        // exit if cache is disabled
        if (!Kms_Resource_Cache::isEnabled()) {
            return array();
        }

        $cacheParams = Kms_Resource_Cache::buildCacheParams($entryFilter,$pager);
        $cacheParams += Kms_Resource_Cache::buildCacheParams($captionAssetItemFilter,$pager);
        
        // include user id for global searches (they can include different sets of entries)
        if (empty($params['entryId']) && empty($params['categoryId']))
        {
            $cacheParams['userId'] = Kms_Plugin_Access::getId();
        }

        return $cacheParams;
    }
    
    /**
     * build the cache tags. some tags are not relevant for all search results.
     * @param stdClass $results
     * @param array $cacheParams
     */
    private function buildCacheTags(stdClass $results, Array $cacheParams)
    {
        // exit if cache is disabled
        if (!Kms_Resource_Cache::isEnabled()) {
            return array();
        }

        $cacheTags = array();
        
        if (!empty($results->objects) && count($results->objects))
        {
            foreach ($results->objects as $id => $caption){
                $cacheTags[$id] = 'entry_' . $id;
            
                if (!empty($cacheParams['userId']))
                {
                    // global search - was cached per user.
                    $cacheTags[$cacheParams['userId']] = 'userId_' . $cacheParams['userId'];
                }
                    
                if (!empty($cacheParams['filter:categoriesMatchOr']))
                {
                    // category search.
                    $cacheTags[$cacheParams['filter:categoriesMatchOr']] = 'categoryId_' . $cacheParams['filter:categoriesMatchOr'];
                }
            }
        }
                
        return $cacheTags;
    }
    
    /**
     * groups the caption assets search results by entry.
     * @param Kaltura_Client_CaptionSearch_Type_CaptionAssetItemListResponse $assets - the assets to group
     * @return stdClass $results - the grouped results
     */
    private function groupAssetsByEntry(Kaltura_Client_CaptionSearch_Type_CaptionAssetItemListResponse $assets = null)
    {
        $results = new stdClass();

        if (isset($assets->objects)) 
        {
            foreach ($assets->objects as $assetItem) {
                $asset = $assetItem->asset;
                $entry = $assetItem->entry;

                // set the entry in the results set if does not exist
                if (empty($results->objects[$entry->id])) 
                {
                    $results->objects[$entry->id] = array('entry' => $entry,'assetItems' => array());
                }

                $result = &$results->objects[$entry->id];
                $result['assetItems'][] = $assetItem;
            }

            $results->totalCount = $assets->totalCount;
        }

        return $results;
    }
    
}