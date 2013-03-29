<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Webcast module Model
 *
 * @author talbone
 *
 */
 class Webcast_Model_Webcast extends Kms_Module_BaseModel implements Kms_Interface_Deployable_PreDeployment,
                                                                     Kms_Interface_Functional_Entry_Type, 
                                                                     Kms_Interface_Model_Entry_List,
                                                                     Kms_Interface_Functional_Gallery_Item
 {
    const MODULE_NAME = 'webcast';
    
    const METADATA_BROADCAST_TIME_XPATH = '/metadata/BroadcastTime';
    const METADATA_BROADCAST_DURATION_XPATH = '/metadata/BroadcastDuration';
    const METADATA_EVENT_OPEN_TIME_XPATH = '/metadata/EventOpenTime';
    const METADATA_SPEAKER_XPATH = '/metadata/Speaker';
    const METADATA_URL_XPATH = '/metadata/URL';
    const METADATA_DIRECT_ACCESS_URL_XPATH = '/metadata/DirectGuestAccess';


    const BUTTON_TYPE_REGISTER = 'register'; 
    const BUTTON_TYPE_JOIN = 'join'; 
    const BUTTON_TYPE_WATCH = 'watch'; 


    /* view hooks */
    public $viewHooks = array
    (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array( 'action' => 'header','controller' => 'entry', 'order' => 20),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_ENTRY_PAGE => array( 'action' => 'play','controller' => 'entry', 'order' => 20),
        Kms_Resource_ViewHook::CORE_VIEW_HOOK_MYMEDIABULK => array(
            'action' => 'bulk-button', 
            'controller' => 'entry',
            'order' => '50',
            
        )     
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
                        'controller' => 'webcast:entry',
                        'actions' => array('play','header'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
                array(
                        'controller' => 'webcast:sso',
                        'actions' => array('redirect'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'webcast:entry',
                        'actions' => array('bulk-button','refresh-entries','refresh'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
        );
        return $accessrules;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canInstall()
     */
    public function canInstall()
    {
        return $this->externalTypesExists();
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canEnable()
     */
    public function canEnable()
    {
        return $this->externalTypesExists();
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::getPreDeploymentFailReason()
     */
    public function getPreDeploymentFailReason()
    {
        return 'External entry types not available on api version';
    }

    /**
     * check that the server version has the external types defined
     * @return boolean
     */
    private function externalTypesExists()
    {
        $result = true;

        try {
            $client = Kms_Resource_Client::getUserClient();
            $externalMediaPlugin = Kaltura_Client_ExternalMedia_Plugin::get($client);
            $externalMediaPlugin->externalMedia->listAction();
        }
        catch (Kaltura_Client_Exception $e){
            // we actually expect exceptions(SERVICE_FORBIDDEN), just not this one:
            if ($e->getCode() == 'SERVICE_DOES_NOT_EXISTS') {
                Kms_Log::log('webcast: ' . $e->getCode() . ': ' .$e->getMessage() . ' . Module will not be installed/enabled.' , Kms_Log::DEBUG);                
                $result = false;
            }
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::isHandlingEntryType()
     */
    public function isHandlingEntryType(Kaltura_Client_Type_BaseEntry $entry)
    {
        $isHandlingType = false;

        $entryType = $entry->type;
        $mediaType = ($entryType == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA && isset($entry->externalSourceType)? $entry->externalSourceType : null);

        if (Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA == $entryType && 
            Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL == $mediaType)
        {
            $isHandlingType = true;
        }        

        return $isHandlingType;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::getMockEntryType()
     */
    public function getMockEntryType()
    {
        $entry = new Kaltura_Client_ExternalMedia_Type_ExternalMediaEntry();
        $entry->type = Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA;
        $entry->externalSourceType = Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL;
        $entry->mediaType = Kaltura_Client_Enum_MediaType::VIDEO;
        return $entry;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::getThumbnail()
     */
    public function getThumbnail($entryId, $height, $width) 
    {
        $entry = Kms_Resource_Models::getEntry()->getCurrent($entryId);
        $thumbnailUrl = rtrim($entry->thumbnailUrl . '/width/' . $width . '/height/' . $height . '/type/' . Kaltura_Client_Enum_ThumbCropType::CROP);
        return $thumbnailUrl;
    } 
	
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::isEditable()
     */
    public function isEditable() 
    {
        return false;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::readyToPublish()
     */
    public function readyToPublish(Kaltura_Client_Type_BaseEntry  $entry, $readyToPublish)
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::canPublishToPlaylist()
     */
    public function canPublishToPlaylist(Kaltura_Client_Type_BaseEntry  $entry)
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::modifyFilter()
     */
    public function modifyFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $type)
    {
    	//if only webcast entries should be filtered need to replace filter by external filter and add the source type
    	//otherwise only add the external media to filter types
    	if ($type == self::MODULE_NAME)
    	{
    		$new_filter = new Kaltura_Client_ExternalMedia_Type_ExternalMediaEntryFilter();
    		//copy all values of base filter to external filter
    		foreach (get_class_vars(get_class($filter)) as $name => $value)
    		{
    			$new_filter->$name = $value;
    		}
    		$new_filter->typeIn = '';
    		$new_filter->typeEqual = Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA;
    		$new_filter->externalSourceTypeEqual = Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL;
    		$filter = $new_filter;
    	}
    	elseif (empty($type) || $type == 'all') 
    	{
    		if (empty($filter->typeIn))
    		{
    			$filter->typeIn = '';
    		} 
    		$filter->typeIn = explode(',', $filter->typeIn);
    		array_push($filter->typeIn, Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA);
    		$filter->typeIn = implode(',', $filter->typeIn);
    	}
        return $filter;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::modifyFilterTypes()
     */
    public function modifyFilterTypes(array $types = array())
    {
    	$translator = Zend_Registry::get('Zend_Translate');
    	return $types + array(self::MODULE_NAME => $translator->translate("Webcasts"));
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::createSorters()
     */
    public function createSorters()
    {
    	$translator = Zend_Registry::get('Zend_Translate');
    	$sorters = array(
           	'name' => $translator->translate('Alphabetical'),
    	    'like' => $translator->translate('Likes'),
        );
    	if (!Kms_Resource_Config::getConfiguration ( 'application', 'enableLike' ))
		{
			unset($sorters['like']);
		}
    	return array(self::MODULE_NAME => $sorters);
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Type::getDefaultSorter()
     */
    public function getDefaultSorter($type)
    {
    	if ($type === self::MODULE_NAME)
    	{
    		return Kms_Resource_Config::getModuleConfig($type, 'sortMediaBy');
    	}
    	return null;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Model_Entry_List::listAction()
     */
    public function listAction(Application_Model_Entry $model)
    {
        // get the entries in the gallery
        $entries = $model->Entries;

        if (!empty($entries)) 
        {
            $entryIds = array();

            // get the webcast gallery entries
            foreach ($entries as $entryId => $entry) {
                $entry = $entry->entry;
                if ($this->isHandlingEntryType($entry)) 
                {
                    Kms_Log::log('webcast: handling gallery item for entry ' . $entryId, Kms_Log::DEBUG);
                    $entryIds[] = $entryId;
                }
            }

            // get the custom data of the entries 
            $entries = implode(',', $entryIds);
            $customData = $this->getEntriesCustomdata($entries);

            if (!empty($customData->objects)) 
            {
                $moduleData = array();

                // get entries metadata
                foreach ($customData->objects as $metadataObject) 
                {
                    if ($this->isCustomDataValid($metadataObject)) 
                    {
                        // construct the xml element
                        $xmlObj = new SimpleXMLElement($metadataObject->xml);

                        // keep it by the entry`s id
                        $moduleData['metadata'][$metadataObject->objectId] = $xmlObj;   

                        // put broadcast duration in entry duration
                        $entry = $model->Entries[$metadataObject->objectId]; 
                        $entry = $entry->entry;
                        $entry->duration = $this->getCustomDataBroadcastDuration($xmlObj);
                    }
                }

                // save the entries metadata on the entry model
                $entryModel = Kms_Resource_Models::getEntry();
                $entryModel->setModuleData($moduleData, self::MODULE_NAME);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Gallery_Item::getItemClass()
     */
    public function getItemClass(Kaltura_Client_Type_BaseEntry $entry)
    {
        $class = '';

        // check that this is our entry
        if ($entry->type == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA) {
            if ($this->isHandlingEntryType($entry)) {
                $class = 'webcast';

                // get the current gallery webcast entries custom data from the entry model
                $entryModel = Kms_Resource_Models::getEntry();
                $moduleData = $entryModel->getModuleData(self::MODULE_NAME);

                // get the entry`s join/register/watch status
                if (!empty($moduleData['metadata'][$entry->id])) {
                    $xmlObj = $moduleData['metadata'][$entry->id];
                    $type = $this->getButtonType($xmlObj);
                    $class .= ' ' . $type;

                    Kms_Log::log('webcast: icon type ' . $type . ' for entry ' . $entry->id, Kms_Log::DEBUG);
                }
            }     
        }
        return $class;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Gallery_Item::getItemDescription()
     */
    public function getItemDescription(Kaltura_Client_Type_BaseEntry $entry, $description)
    {
        $description = '';

        // check that this is our entry
        if ($this->isHandlingEntryType($entry)) {
            // get the current gallery webcast entries custom data from the entry model
            $entryModel = Kms_Resource_Models::getEntry();
            $moduleData = $entryModel->getModuleData(self::MODULE_NAME);

            // does this entry have metadata
            if (!empty($moduleData['metadata'][$entry->id])) {
                // get the entry`s metadata
                $xmlObj = $moduleData['metadata'][$entry->id];
                $openTime = $this->getCustomDataOpenTime($xmlObj);
                $broadcastTime = $this->getCustomDataBroadcastTime($xmlObj);
                $broadcastDuration = $this->getCustomDataBroadcastDuration($xmlObj);

                // defaults and missing fields
                if (empty($openTime)) {
                    $openTime = $entry->createdAt;
                }
                if (empty($broadcastTime)) {
                    $broadcastTime = $entry->createdAt;
                }

                // time calculations
                $now = time();
                $translator = Zend_Registry::get('Zend_Translate');
                $helper = new Kms_View_Helper_TimeElapsed();

                // get the broadcast type - 
                if ($now < $openTime) {
                    // future event
                    $description = $translator->translate('Broadcasting') .': '. date('d M y, G:i A T', $broadcastTime);
                }
                elseif ($openTime < $now && $now  < $broadcastTime) {
                    // open event
                    $description = $translator->translate('Broadcast starting in') .' '. $helper->timeElapsed($broadcastTime - $now);
                }
                elseif ($now - $broadcastTime < $broadcastDuration) {
                    // ongoing event
                    $description = $translator->translate('Broadcast started') .' '. $helper->timeElapsed($now - $broadcastTime) .' '. $translator->translate('ago');
                }
                elseif ($now - $broadcastTime > $broadcastDuration) {
                    // on demand event
                    $description = $translator->translate('Recorded') .' '. $helper->timeElapsed($now - $broadcastTime) .' '. $translator->translate('ago');                    
                }
            }
        }     

        return $description;
    }

    /**
     *  get the custom data for entries
     *  @param string $entryIds - comma seperated entry ids
     *  @return Kaltura_Client_Metadata_Type_MetadataListResponse - the entries custom data
     */
    private function getEntriesCustomdata($entryIds = '')
    {
        $customData = new Kaltura_Client_Metadata_Type_MetadataListResponse();

        Kms_Log::log('webcast: getting customdata for entries ' . $entryIds , Kms_Log::DEBUG);

        $profileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'intercallWebcastProfileId');
        
        if (!empty($entryIds) && !empty($profileId))
        {
            $cacheParams = array('profileId' => $profileId, 'entryId' => $entryIds);
        
            $customData = Kms_Resource_Cache::apiGet('webcast', $cacheParams);
            if ($customData === false)
            {
                // get the metadata from the api
                $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
                $filter->objectIdIn = $entryIds;
                $filter->metadataProfileIdEqual = $profileId;
                $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;

                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

                try {
                    $customData = $metadataPlugin->metadata->listAction($filter);

                    // test that we got the object from the correct profile
                    if (!empty($customData->objects) && count($customData->objects)){
                        if ($customData->objects[0]->metadataProfileId != $profileId){
                            Kms_Log::log('webcast: Got wrong customdata for entries ' . $entryIds . ' profileId ' . $profileId, Kms_Log::ERR);
                            $customData = new Kaltura_Client_Metadata_Type_MetadataListResponse();
                        }
                    }
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('webcast: Failed getting customdata for entries ' . $entryIds . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                    throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
				
                $cacheTags = $this->buildCacheTags($entryIds);
                // update the cache
                Kms_Resource_Cache::apiSet('webcast', $cacheParams, $customData, $cacheTags);
            }
        }
        else
        {
            if (empty($profileId)){
                Kms_Log::log('webcast: intercallWebcastProfileId not configured' , Kms_Log::ERR);
            }
            elseif (empty($entryId)){
                Kms_Log::log('webcast: no entries given' , Kms_Log::ERR);
            }
        }
                                
       return $customData;
    }

    /**
     * build the cache tags
     * @param string $entryIds
     * @return array of cache tags
     */
    private function buildCacheTags($entryIds) {
    	$tags = array();
    	$temp = explode(',', $entryIds);
    	foreach ($temp as $entryId) {
    		$tags[] = 'entry_' . $entryId;
    	}
    	return $tags;
    }
    
    /**
     *  get the custom data for an entry
     *  @param string $entryId - the entry id
     *  @return SimpleXMLElement - the entry custom data
     */
    public function getEntryCustomdata($entryId)
    {
        $xmlObj = null;
        $customData = $this->getEntriesCustomdata($entryId);

        if (!empty($customData->objects)) {
            $metadataObject = $customData->objects[0];
        
            if ($this->isCustomDataValid($metadataObject)) {             
                $xmlObj = new SimpleXMLElement($metadataObject->xml);
            }
        }

        return $xmlObj;
    }

    /**
     *  test that the custom data is valid
     *  @param Kaltura_Client_Metadata_Type_Metadata $metadataObject - the custom data
     *  @return boolean
     */
    private function isCustomDataValid(Kaltura_Client_Metadata_Type_Metadata $metadataObject)
    {
        if (empty($metadataObject->xml)) {
            return false;
        }
        return true;
    }

    /**
     *  get values from the custom data
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @param string $xpath - the xpath to the field values
     *  @return array - the field array of values
     */
    private function getCustomDataValues(SimpleXMLElement $xmlObj, $xpath)
    {
        $values = $xmlObj->xpath($xpath);
        return $values;
    }

    /**
     *  get a single value from the custom data
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @param string $xpath - the xpath to the field value
     *  @return string - the value cast as a string
     */
    private function getCustomDataValue(SimpleXMLElement $xmlObj, $xpath)
    {
        $value = $this->getCustomDataValues($xmlObj, $xpath);
        $value = (string)$value[0];
        return $value;
    }

    /**
     *  get the webcast broadcast time from the custom data.
     *  @param SimpleXMLElement $customData - the custom data
     *  @return long - timestamp of the time
     */
    public function getCustomDataBroadcastTime(SimpleXMLElement $xmlObj = null)
    {
        $time = null;

        if (!is_null($xmlObj)) {
            $time = $this->getCustomDataValue($xmlObj, self::METADATA_BROADCAST_TIME_XPATH);
        }

        return $time;
    }

    /**
     *  get the webcast broadcast duration from the custom data.
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return long - timestamp of the duration    
     */
    public function getCustomDataBroadcastDuration(SimpleXMLElement $xmlObj = null)
    {
        $time = null;

        if (!is_null($xmlObj)) {
            $time = $this->getCustomDataValue($xmlObj, self::METADATA_BROADCAST_DURATION_XPATH);
        }

        return $time;
    }

    /**
     *  get the webcast broadcast open time from the custom data.
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return long - timestamp of the time     
     */
    public function getCustomDataOpenTime(SimpleXMLElement $xmlObj = null)
    {
        $time = null;

        if (!is_null($xmlObj)) {
            $time = $this->getCustomDataValue($xmlObj, self::METADATA_EVENT_OPEN_TIME_XPATH);
        }

        return $time;
    }

    /**
     *  get the webcast speakers from the custom data.
     *  @param string $entryId - the entry id     
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return array -  the speakers     
     */
    public function getCustomDataSpeakers($entryId, SimpleXMLElement $xmlObj = null)
    {
        $speakers = array();

        if (!is_null($xmlObj)) {
            $speakers = $this->getCustomDataValues($xmlObj, self::METADATA_SPEAKER_XPATH);                     
        }

        // construct the speaker image urls
        $speakers = $this->getSpeakerImages($entryId, $speakers);

        return $speakers;
    }

    /**
     *  get the webcast speaker image urls from the api.
     *  @param string $entryId - the entry id     
     *  @param array $speakers - the speakers
     *  @return array -  the speakers, with the image urls     
     */
    private function getSpeakerImages($entryId, array $speakers)
    {
        Kms_Log::log('webcast: getting speaker images urls for entry ' . $entryId , Kms_Log::DEBUG);

        $cacheParams = array('entryId' => $entryId);
        
        if (!$results = Kms_Resource_Cache::apiGet('webcastThumbs', $cacheParams))
        {
            $thumbParams = new Kaltura_Client_Type_ThumbParams();
            $thumbParams->width = 100;
            $thumbParams->cropType = Kaltura_Client_Enum_ThumbCropType::RESIZE_WITH_FORCE;

            $client = Kms_Resource_Client::getUserClient();

            $client->startMultiRequest();
            foreach ($speakers as $speaker) {
                // TODO - add thumbParams
                $client->thumbAsset->getUrl($speaker->SpeakerImage);
            }
            try{
                $results = $client->doMultiRequest();

                if (empty($results)) {
                    $results = array();
                }

                // set the cache with short expiration - to avoid stale cdn entries, 
                // resulting in denied assets requests.
                $cacheTags = array('entry_' . $entryId);
                Kms_Resource_Cache::apiSet('webcastThumbs', $cacheParams, $results, $cacheTags, 300);
            }
            catch(Kaltura_Client_Exception $e){
                Kms_Log::log('webcast: Error generating thumbAsset urls: ' . $e->getCode() . ', ' . $e->getMessage(), Kms_Log::ERR);
            }
        }

        // save the urls in the speaker objects
        foreach ($results as $key => $url) {
           $speaker = $speakers[$key];
           $speaker->SpeakerImageUrl = $url;
        }

        return $speakers;
    }

    /**
     *  get the register button type
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return int -  the button type - self::BUTTON_TYPE_REGISTER | self::BUTTON_TYPE_JOIN | self::BUTTON_TYPE_WATCH
     */
    public function getButtonType(SimpleXMLElement $xmlObj = null)
    {
        $type = self::BUTTON_TYPE_REGISTER;

        if (!is_null($xmlObj)) {
            $openTime = $this->getCustomDataValue($xmlObj, self::METADATA_EVENT_OPEN_TIME_XPATH);
            $closeTime = $openTime + $this->getCustomDataValue($xmlObj, self::METADATA_BROADCAST_DURATION_XPATH);

            if (empty($openTime) || empty($closeTime)) {
                $type = self::BUTTON_TYPE_WATCH;
            }
            else{
                $now = time();
                if ($now > $openTime) {
                    $type = self::BUTTON_TYPE_JOIN;
                }
                if ($now > $closeTime) {
                    $type = self::BUTTON_TYPE_WATCH;
                }
            }
        }

        return $type;
    }

    /**
     *  get the webcast guest user access url
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return string - url direct (guest) access url
     */
    public function getGuestUrl(SimpleXMLElement $xmlObj = null)
    {
        $url = '';

        if (!is_null($xmlObj)) {
            $url = $this->getCustomDataValue($xmlObj, self::METADATA_DIRECT_ACCESS_URL_XPATH);
        }

        return $url;
    }

    /**
     *  get the webcast user access url
     *  @param SimpleXMLElement $xmlObj - the custom data
     *  @return string - url webcast access url
     */
    public function getUrl(SimpleXMLElement $xmlObj = null)
    {
        $url = '';

        if (!is_null($xmlObj)) {
            $url = $this->getCustomDataValue($xmlObj, self::METADATA_URL_XPATH);
        }

        return $url;
    }

    /**
     *  set the user email.
     *  @param string $email - the user email
     */
    public function setUserEmail($email = null)
    {
        $KalturaUser = new Kaltura_Client_Type_User();
        $KalturaUser->id = Kms_Plugin_Access::getId();
        $KalturaUser->email = trim($email);

        $model = Kms_Resource_Models::getUser();
        $model->updateKalturaUser($KalturaUser, $KalturaUser, false);
    }

    /**
     *  generate the kaltura info for webcast
     *  @param string $url - the redirect url to sign the kaltura info for
     *  @return string $kalturaInfo 
     */
    public function getKalturaInfo($url = '')
    {
        $kalturaInfo = '';
        $eid = '';
        $seid = '';

        // pase the url params
        $urlparts = parse_url($url);
        if(isset($urlparts['query'])) {
            parse_str(urldecode($urlparts['query']), $urlparts['query']);

            $eid = $urlparts['query']['eid'];
            $seid = $urlparts['query']['seid'];
        }

        // get the user
        $model = Kms_Resource_Models::getUser();
        $user = $model->get(Kms_Plugin_Access::getId());

        // construct the privliges string
        $privileges = '';
        if (!empty($user->email)) {
            $privileges = "email:{$user->email},";
        }
        $privileges .= "eid:$eid,seid:$seid";
        if (!empty($user->firstName)) {
            $privileges .= ",first_name:{$user->firstName}";
        }
        if (!empty($user->lastName)) {
            $privileges .= ",last_name:{$user->lastName}";
        }        

        // config data
        $adminSecret = Kms_Resource_Config::getConfiguration('client','adminSecret');
        $partnerId = Kms_Resource_Config::getConfiguration('client','partnerId');
        $expiry = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'expiryTime');

        // generate ks v2
        $client = Kms_Resource_Client::getUserClient();
        $kalturaInfo = $client->generateSessionV2($adminSecret,Kms_Plugin_Access::getId(),Kaltura_Client_Enum_SessionType::USER,$partnerId,$expiry,$privileges);

        return $kalturaInfo;
    }
}