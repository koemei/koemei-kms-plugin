<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
* To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * ChannelTopics Model
 * @author talbone
 *
 */
class Channeltopics_Model_Channeltopics extends Kms_Module_BaseModel implements Kms_Interface_Model_Channel_Get, 
                                                                                Kms_Interface_Model_Channel_Save, 
                                                                                Kms_Interface_Model_Channel_Delete,
                                                                                Kms_Interface_Form_Channel_Edit,
                                                                                Kms_Interface_Model_Channel_ListFilter,
                                                                                Kms_Interface_Form_KeywordSearch_Modify
{
    const MODULE_NAME = 'channeltopics';

    /* view hooks */
    public $viewHooks = array
    (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNEL_SIDENAV => array( 'action' => 'sidenav','controller' => 'index',),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PRE_CHANNELS => array ( 'action' => 'channeltitle','controller' => 'index',),
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
                        'controller' => 'channeltopics:index',
                        'actions' => array('sidenav','channeltitle'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
        );
        return $accessrules;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Model_Channel_Get::get()
     */
    public function get(Application_Model_Channel $model)
    {
        if ($model->Category)
        {
            $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
            $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
            $channelId = $model->Category->id;

            Kms_Log::log('channeltopics: getting topics for channel ' . $model->Category->name .' Id ' . $channelId , Kms_Log::DEBUG);

            // check the category metadata cache/api
            $cacheParams = array('profileId' => $customdataProfileId,'fieldId' => $topicField, 'channelId' => $channelId);
            $topics = Kms_Resource_Cache::apiGet('channelTopics', $cacheParams);
            if (!$topics)
            {
                $topics = $this->getChannelTopics($customdataProfileId, $topicField, $channelId);
                
                // update the cache
                Kms_Resource_Cache::apiSet('channelTopics', $cacheParams, $topics);
            }
                
            if (!empty($topics) && !empty($topics->objects) && $channelId)
            {
                // parse the values by the fields
                $fields = $this->getCustomdataFields($customdataProfileId, $topicField);
                $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
                
                // force the same structure for single and multi select values
                if (isset($topics[$topicField]) && is_array($topics[$topicField])){
                    $topics = $topics[$topicField];
                }
             
                // add it to the category
                $model->Category->topics = $topics;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Model_Channel_Save::save()
     */
    public function save(Application_Model_Channel $model, array $data)
    {
        // save the metadata for this category
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
        $channelId = $model->Category->id;
        $topics =  $data['topics'];
        
        // force the same structure for single and multi select values
        if (!is_array($topics)){
            $topics = array('topics' => $topics);
        }
        
        Kms_Log::log('channeltopics: saving topics for channel ' . $channelId, Kms_Log::DEBUG);
        
        $this->setChannelTopics($customdataProfileId, $topicField, $channelId, $topics);
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Model_Channel_Delete::delete()
     * @param Application_Model_Channel $model
     */
    public function delete(Application_Model_Channel $model)
    {
        if ($model->Category)
        {
            $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
            $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
            $channelId = $model->Category->id;
            
            // clean the cache for this channel. The metadata entry itself will be deleted by the server.
            $cacheParams = array('profileId' => $customdataProfileId,'fieldId' => $topicField, 'channelId' => $channelId);
            Kms_Resource_Cache::apiClean('channelTopics', $cacheParams);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Form_Channel_Edit::editForm()
     * @param Application_Form_EditChannel $form
     */
    public function editForm(Application_Form_EditChannel $form)
    {         
        Kms_Log::log('channeltopics: adding topics for channel form. ' , Kms_Log::DEBUG);

        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
        $fields = $this->getCustomdataFields($customdataProfileId, $topicField);
        $topics = $this->getAvailableTopics($customdataProfileId,$topicField);

        // add the topics form element only if there are topics to choose from
        if (!empty($topics)){
            // add an empty option
            $translator = Zend_Registry::get('Zend_Translate');
            $topics = array(null => $translator->translate('no value')) + $topics;
            
            $spec = array(
                    'belongsTo' => Application_Form_EditCategory::FORM_NAME,
                    'name' => 'topics',
                    'label' => $fields[$topicField]['label'],
                    'multiOptions' => $topics,
            );
    
            if ($fields[$topicField]['isMulti'])
            {
                $element = new Zend_Form_Element_Multiselect($spec);
                $element->getDecorator('HtmlTag')->setOption('class', 'multi');
            }
            else
            {
                $element = new Zend_Form_Element_Select($spec);
            }
    
            // add new form element to the channel edit form
            $form->addElement($element);
        }
    }
    
    /**
     * 
     * @see Kms_Interface_Model_Channel_ListFilter::modifyFilter()  
     * @param Kaltura_Client_Type_CategoryBaseFilter $filter
     * @return Kaltura_Client_Type_CategoryBaseFilter
     */
    public function modifyFilter(Kaltura_Client_Type_CategoryBaseFilter $filter)
    {
        $topic = Zend_Controller_Front::getInstance()->getRequest()->getParam('topic');
        if ($topic)
        {   
            Kms_Log::log('channeltopics: filtering channels by topic "' . $topic . '"', Kms_Log::DEBUG);
            
            $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
            $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
                        
            $filterAdvancedSearchItems = new Kaltura_Client_Type_SearchCondition();
            $filterAdvancedSearchItems->field = "/*[local-name()='metadata']/*[local-name()='$topicField']";
            $filterAdvancedSearchItems->value = $topic; 
            
            $filterAdvancedSearch = new Kaltura_Client_Metadata_Type_MetadataSearchItem();
            $filterAdvancedSearch->type = Kaltura_Client_Enum_SearchOperatorType::SEARCH_OR;
            $filterAdvancedSearch->metadataProfileId = $customdataProfileId;
            $filterAdvancedSearch->items = array($filterAdvancedSearchItems);
            
            $filter->advancedSearch = $filterAdvancedSearch;
        }
        return $filter;
    }

    /**
     * get the specific channel topics
     *
     * @param unknown_type $customdataProfileId
     * @param unknown_type $topicField
     * @param unknown_type $channelId
     */
    public function getChannelTopics($customdataProfileId, $topicField, $channelId)
    {
        // get the metadata from the api
        $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
        $filter->objectIdEqual = $channelId;
        $filter->metadataProfileIdEqual = $customdataProfileId;
        $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;

        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try {
            $topics = $metadataPlugin->metadata->listAction($filter);
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('channeltopics: Failed getting customdata for category Id ' . $channelId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }

        return $topics;
    }

    /**
     * set the specific channel topics
     *
     * @param unknown_type $topicField
     * @param unknown_type $topics
     */
    public function setChannelTopics($customdataProfileId, $topicField, $channelId, $topics)
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        // create new topics metadata
        $customDataXML = new SimpleXMLElement('<metadata/>');
        foreach ($topics as $topic)
        {
            // ignore empty values
            if ($topic){
                $customDataXML->addChild($topicField, $topic);
            }
        }

        // check exsisting metadata to decide update/create/delete
        $existingTopics = $this->getchannelTopics($customdataProfileId, $topicField, $channelId);

        // check if only empty value was selected
        $emptyValue = false;
        if (count($topics) == 1 && array_values($topics))
        {
            $topics = array_values($topics);
            if (empty($topics[0])){
                $emptyValue = true;
            }
        }
        
        if (!$existingTopics || empty($existingTopics->objects))
        {            
            // no channel topics metadata - add new metadata
            if ($emptyValue)
            {
                // no topic selected - do nothing much
                $newCustomdata->objects[] = null;
            }
            else
            {
                try {
                    $newCustomdata->objects[] = $metadataPlugin->metadata->add($customdataProfileId,Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, $channelId, $customDataXML->asXML());
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('channeltopics: Failed adding customdata for category Id ' . $channelId . ', profileId ' . $customdataProfileId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::ERR);
                }
            }
        }
        else
        {            
            // existing channel topics metadata - update metadata
            if ($emptyValue)
            {                
                // empty value selected - delete existing metadata
                try {
                    $customdataId = $existingTopics->objects[0]->id;
                    $metadataPlugin->metadata->delete($customdataId);
                    $newCustomdata->objects[] = null;
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('channeltopics: Failed deleting customdata for category Id ' . $channelId . ', profileId ' . $customdataProfileId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::ERR);
                }
            }    
            else 
            {
                try {
                    $customdataId = $existingTopics->objects[0]->id;
                    $newCustomdata->objects[] = $metadataPlugin->metadata->update($customdataId, $customDataXML->asXML());
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('channeltopics: Failed updating customdata for category Id ' . $channelId . ', profileId ' . $customdataProfileId . ', ' . $e->getCode() . ': ' . $e->getMessage() . '; xml: ' . $customDataXML->asXML(), Kms_Log::ERR);
                }
            }
        }
         
        // set the new channel topics in the cache
        $cacheParams = array('profileId' => $customdataProfileId,'fieldId' => $topicField, 'channelId' => $channelId);
        Kms_Resource_Cache::apiSet('channelTopics', $cacheParams, $newCustomdata);
    }

    /**
     * get the possible values for channel topics
     *
     * @param string $customdataProfileId
     * @param string $topicField
     * @param array $topicField
     */
    public function getAvailableTopics($customdataProfileId, $topicField)
    {
        $topics = array();
        $fields = $this->getCustomdataFields($customdataProfileId, $topicField);
        if ($fields)
        {
            if(isset($fields[$topicField]['listValues'])){
                $topics = $fields[$topicField]['listValues'];
            }
        }
        return $topics;
    }

    /**
     * get the formatted fields from the cache or the metadata
     *
     * @param unknown_type $customdataProfileId
     * @param unknown_type $topicField
     * @return string
     */
    public function getCustomdataFields($customdataProfileId, $topicField)
    {
        $cacheParams = array('profileId' => $customdataProfileId,'fieldId' => $topicField);
        $fields = Kms_Resource_Cache::appGet('channelTopicsFields', $cacheParams);

        if (!$fields)
        {
            $fields = Kms_Helper_Metadata::getCustomdataFields($customdataProfileId);
            Kms_Resource_Cache::appSet('channelTopicsFields', $cacheParams, $fields);
        }
        return $fields;
    }

    /**
     * get the list of customdata profiles for the partner
     */
    public static function configGetCustomdataProfiles()
    {
        return Kms_Helper_Metadata::getCustomdataProfiles(Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY);
    }

    /**
     * get the list of customdata fields for the selected metadata profile
     */
    public static function configGetCustomdataFields()
    {
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $fields = Kms_Helper_Metadata::getCustomdataFields($customdataProfileId);
        $ret = array();
        if(count($fields))
        {
            foreach($fields as $name => $field)
            {
                $ret[$name] = $name . ' (' . $field['type'] . ($field['isMulti'] ? ' - multi' : '') . ')';
            }
        }
        return $ret;
    } 
    
    public function editKeywordSearch(Application_Form_KeywordSearch $form)
    {
        $front = Zend_Controller_Front::getInstance();
        $topic = $front->getRequest()->getParam('topic');
        if(strlen($topic))
        {
            $form->_defaultKeyword = Zend_Registry::get('Zend_Translate')->translate('Search').' '.$topic;
        }
    }
}