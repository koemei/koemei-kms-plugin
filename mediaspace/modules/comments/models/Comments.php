<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Comments_Model_Comments extends Kms_Module_BaseModel implements Kms_Interface_Form_Channel_Edit, Kms_Interface_Model_Channel_Save, Kms_Interface_Functional_Entry_TabType, Kms_Interface_Model_Entry_FilterSortByType //, Kms_Interface_Model_Entry_FilterSort//, Kms_Interface_Form_Entry_Edit2, Kms_Interface_Form_Category_Edit2
{

    const MODULE_NAME = 'Comments';
    
    /* view hooks */

    public $viewHooks = array
        (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array
            (
            'action' => 'tabcontent',
            'controller' => 'index',
            'order' => 50
        ),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array
            (
            'action' => 'tab',
            'controller' => 'index',
            'order' => 50
        ),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_EDIT_ENTRY_OPTIONS => array
            (
            'action' => 'options',
            'controller' => 'index',
            'order' => '50',
        ),
    );

    /* end view hooks */
    /**
     * Adding a new interface function for marking this tab as available for external entry
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_TabType::isHandlingTabType()
     */
    public function isHandlingTabType(Kaltura_Client_Type_BaseEntry $entry)
    {
    	$isHandlingType = false;

        $entryType = $entry->type;
        $mediaType = ($entryType == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA ? $entry->externalSourceType : null);

        if (Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA == $entryType && 
        	Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL == $mediaType)
        {
            $isHandlingType = true;
        }        
        return $isHandlingType;
    } 

    public function getAccessRules()
    {
        $accessrules = array(
            array(
                'controller' => 'comments:index',
                'actions' => array('add', 'delete', 'reply', 'options', 'save-option'),
                'role' => Kms_Plugin_Access::VIEWER_ROLE,
            ),
            array(
                'controller' => 'comments:index',
                'actions' => array('index', 'tabcontent', 'tab'),
                'role' => Kms_Plugin_Access::ANON_ROLE,
            ),
        );

        return $accessrules;
    }
	
	/**
     * get customdata profiles for the partner for entry objects
     */
    public static function configGetEntryCustomdataProfiles()
    {
        return Kms_Helper_Metadata::getCustomdataProfiles(Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY);
    }

   /* public function get(Application_Model_Entry $model)
    {
        $data = $this->getComments(array('entryId' => $model->id));
        $model->setModuleData($data, self::MODULE_NAME);
    }*/

    public function getComments($params)
    {
        $comments = array();
        $replies = array();

        if (isset($params['entryId']) && $params['entryId'])
        {
            $client = Kms_Resource_Client::getAdminClient();
            $cuePointPlugin = Kaltura_Client_CuePoint_Plugin::get($client);

            $filter = new Kaltura_Client_Annotation_Type_AnnotationFilter();
            $filter->entryIdEqual = $params['entryId'];
            $filter->parentIdEqual = 0;
            $sort = Kms_Resource_Config::getModuleConfig('comments', 'sort');
            $filter->orderBy = $sort;
            if (isset($params['timeFrom']))
            {

                if ($sort == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_ASC)
                {
                    $filter->createdAtGreaterThanOrEqual = $params['timeFrom'] + 1;
                }
                elseif ($sort == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC)
                {
                    $filter->createdAtLessThanOrEqual = $params['timeFrom'] - 1;
                }
            }

            $pager = new Kaltura_Client_Type_FilterPager();
            $pager->pageSize = Kms_Resource_Config::getModuleConfig('comments', 'pageSize');

            $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

            $comments = Kms_Resource_Cache::apiGet('comments', $cacheParams);
            $cacheTags = array('entry_' . $params['entryId'], 'entry_comment_' . $params['entryId']);

            if (!$comments)
            {
                try
                {
                    // get first level comments
                    $comments = $cuePointPlugin->cuePoint->listAction($filter, $pager);
                    Kms_Resource_Cache::apiSet('comments', $cacheParams, $comments, $cacheTags);                    
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('comments: get comments - ' . $e->getCode() . ': ' . $e->getMessage(), E_WARNING);
                }
            }

            if (isset($comments->objects) && count($comments->objects))
            {
                $client->startMultiRequest();
                // get replies
                $pager->pageSize = 500;
                $sortReplies = Kms_Resource_Config::getModuleConfig(strtolower(self::MODULE_NAME), 'sortReplies');
                $filter->orderBy = $sortReplies == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC ? Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC : Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_ASC;
                foreach ($comments->objects as $comment)
                {
                    $filter->parentIdEqual = $comment->id;


                    $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

                    $replies[$comment->id] = Kms_Resource_Cache::apiGet('comments_replies', $cacheParams);
                    if (!$replies[$comment->id])
                    {
                        $cuePointPlugin->cuePoint->listAction($filter, $pager);
                    }
                }

                try
                {
                    $repliesFromApi = $client->doMultiRequest();
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('comments: get replies - ' . $e->getCode() . ': ' . $e->getMessage(), E_WARNING);
                }

                if (isset($repliesFromApi) && count($repliesFromApi))
                {
                    foreach ($repliesFromApi as $row)
                    {
                        if (isset($row->objects) && count($row->objects))
                        {
                            $parentId = null;
                            //$cacheTags = array('entry_comments_' . $params['entryId']);
                            $cacheTags = array();
                            foreach ($row->objects as $reply)
                            {

                                $parentId = $reply->parentId;
                                $cacheTags[] = 'comments_replies_' . $reply->id;
                                if (!isset($replies{$parentId}) || !$replies{$parentId})
                                {
                                    $replies{$parentId} = array();
                                }
                                $replies{$parentId}[] = $reply;
                            }

                            if ($parentId)
                            {
                                $cacheTags[] = 'comment_' . $parentId;
                            }

                            // save each replies set for each comment separately , hence we need to re-encode the cacheparams and build cache tags each time
                            $whatToSave = isset($replies{$parentId}) && count($replies{$parentId}) ? $replies{$parentId} : array();
                            $filter->parentIdEqual = $parentId;
                            $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);
                            Kms_Resource_Cache::apiSet('comments_replies', $cacheParams, $whatToSave, $cacheTags);
                        }
                    }
                }
            }


            return array(
                'comments' => isset($comments->objects) ? $comments->objects : array(),
                'replies' => $replies
            );
        }
    }

    public function getCommentsCount($params)
    {
        $count = 0;

        if (isset($params['entryId']) && $params['entryId'])
        {
            $client = Kms_Resource_Client::getAdminClient();
            $cuePointPlugin = Kaltura_Client_CuePoint_Plugin::get($client);

            $filter = new Kaltura_Client_Annotation_Type_AnnotationFilter();
            $filter->entryIdEqual = $params['entryId'];
            $filter->parentIdEqual = 0;
            $sort = Kms_Resource_Config::getModuleConfig('comments', 'sort');
            $filter->orderBy = $sort;
            $pager = new Kaltura_Client_Type_FilterPager();
            $pager->pageSize = Kms_Resource_Config::getModuleConfig('comments', 'pageSize');

            $cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

            $count = Kms_Resource_Cache::apiGet('comments_count', $cacheParams);
            $cacheTags = array('entry_' . $params['entryId'], 'entry_comment_' . $params['entryId']);

            if ($count === false)
            {
                try
                {
                    // get first level comments
                    $count = $cuePointPlugin->cuePoint->count($filter);
                    if ($count || $count === '0')
                    {
                        Kms_Resource_Cache::apiSet('comments_count', $cacheParams, $count, $cacheTags);
                    }
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('comments: get comments count - ' . $e->getCode() . ': ' . $e->getMessage(), E_WARNING);
                }
            }
        }
        return $count;
    }

    public function add($params)
    {
        $entryId = isset($params['entryId']) && $params['entryId'] ? $params['entryId'] : NULL;
        $body = isset($params['body']) && trim($params['body']) ? trim($params['body']) : NULL;
        $userId = Kms_Plugin_Access::getId();
        if ($entryId && $body && $userId)
        {
            $client = Kms_Resource_Client::getAdminClient();
            $cuePointPlugin = Kaltura_Client_CuePoint_Plugin::get($client);
            $comment = new Kaltura_Client_Annotation_Type_Annotation();
            $comment->entryId = $entryId;
            $comment->text = $body;

            $cacheTags = array();
            if (isset($params['parentId']) && $params['parentId'])
            {
                $comment->parentId = $params['parentId'];
                $cacheTags[] = 'comment_' . $params['parentId'];
            }
            try
            {

                $cuePoint = $cuePointPlugin->cuePoint->add($comment);
                $cacheTags[] = 'entry_comment_' . $params['entryId'];
                Kms_Resource_Cache::apiClean('comments', array(), $cacheTags);
                $this->updateCount($params['entryId']);
                return $cuePoint;
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('comments: Add -  ' . $e->getCode() . ': ' . $e->getMessage(), E_WARNING);
            }
        }
    }

    public function delete($commentId)
    {
        try
        {
            $client = Kms_Resource_Client::getAdminClient();
            $cuePointPlugin = Kaltura_Client_CuePoint_Plugin::get($client);
            $cuePointObject = $cuePointPlugin->cuePoint->get($commentId);
            if ($cuePointObject && $cuePointObject->entryId && $cuePointObject->userId)
            {
                $entryModel = Kms_Resource_Models::getEntry();
                $entryModel->get($cuePointObject->entryId, false);
                if (Kms_Plugin_Access::isCurrentUser($cuePointObject->userId) || Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId))
                {
                    $cuePointPlugin->cuePoint->delete($commentId);
                    $cacheTags = array('comment_' . $commentId);
                    $cacheTags[] = 'entry_comment_' . $cuePointObject->entryId;
                    Kms_Resource_Cache::apiClean('comments', array(), $cacheTags);
                    Kms_Resource_Cache::apiClean('comments_replies', array(), $cacheTags);
                    $this->updateCount($cuePointObject->entryId);
                }
            }
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Delete -  ' . $e->getCode() . ': ' . $e->getMessage(), E_WARNING);
        }
    }

    /**
     * get the roles for configuration
     */
    public static function getRoles()
    {
        $roles = Application_Model_Config::_getRoleKeys();
        // remove anonymous
        unset($roles[Kms_Plugin_Access::ANON_ROLE]);
        return $roles;
    }

    public function getChannelCommentsCustomdata($channelId)
    {
        $metadata = Kms_Resource_Cache::apiGet('channel_comments', array('channelId' => $channelId));
        if (false === $metadata)
        {
            $metadata = array();
            $profileId = Kms_Resource_Config::getModuleConfig(strtolower(self::MODULE_NAME), 'channelCommentsProfileId');
            if (!empty($channelId) && $profileId)
            {
                // get the metadata from the api
                $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
                $filter->objectIdEqual = $channelId;
                $filter->metadataProfileIdEqual = $profileId;
                $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;

                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

                try
                {
                    $metadata = $metadataPlugin->metadata->listAction($filter);

                    // test that we got the object from the correct profile
                    if (!empty($metadata->objects) && count($metadata->objects))
                    {
                        if ($metadata->objects[0]->metadataProfileId != $profileId)
                        {
                            Kms_Log::log('comments: Got wrong customdata for channel Id ' . $channelId . ' profileId ' . $profileId, Kms_Log::ERR);
                            $metadata = array();
                        }
                        else
                        {
                            // save to cache
                            $metadata = $metadata->objects[0];
                            Kms_Resource_Cache::apiSet('channel_comments', array('channelId' => $channelId), $metadata);
                        }
                    }
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('comments: Failed getting customdata for channel Id ' . $channelId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                    //                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
            else
            {
                Kms_Log::log('comments: Empty profileId ' . $profileId, Kms_Log::ERR);
            }
        }

        return $metadata;
    }

    /**
     * get the channel/s metadata for comments from the api
     * @param unknown_type $channelId
     * @throws Kaltura_Client_Exception
     * @returns boolean
     */
    public function getCommentsAllowedInChannel($channelId)
    {
        // default is true
        $allow = true;

        $obj = $this->getChannelCommentsCustomdata($channelId);
        if ($obj && isset($obj->xml))
        {
            // process the customdata
            $xml = new SimpleXMLElement($obj->xml);
            $allow = isset($xml->AllowCommentsInChannel) && (string) $xml->AllowCommentsInChannel === "false" ? false : true;
        }
        return $allow;
    }

    public function getEntryCommentsCustomdata($entryId)
    {
        $metadata = Kms_Resource_Cache::apiGet('entry_comments_options', array('entryId' => $entryId));
        if (false === $metadata)
        {
            $metadata = array();
            $profileId = Kms_Resource_Config::getModuleConfig(strtolower(self::MODULE_NAME), 'entryCommentsProfileId');
            if (!empty($entryId) && $profileId)
            {
                // get the metadata from the api
                $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
                $filter->objectIdEqual = $entryId;
                $filter->metadataProfileIdEqual = $profileId;
                $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;

                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

                try
                {
                    $metadata = $metadataPlugin->metadata->listAction($filter);

                    // test that we got the object from the correct profile
                    if (!empty($metadata->objects) && count($metadata->objects))
                    {
                        if ($metadata->objects[0]->metadataProfileId != $profileId)
                        {
                            Kms_Log::log('comments: Got wrong customdata for entry Id ' . $entryId . ' profileId ' . $profileId, Kms_Log::ERR);
                            $metadata = array();
                        }
                        else
                        {
                            $metadata = $metadata->objects[0];
                        }
                    }
                    // save to cache
                    Kms_Resource_Cache::apiSet('entry_comments_options', array('entryId' => $entryId), $metadata);
                }
                catch (Kaltura_Client_Exception $e)
                {
                    Kms_Log::log('comments: Failed getting customdata for entry Id ' . $entryId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
                    //                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
                }
            }
            else
            {
                Kms_Log::log('comments: Empty profileId ' . $profileId, Kms_Log::ERR);
            }
        }

        return $metadata;
    }

    public function getCommentsEnabled($entryId)
    {
        // default is true
        $enabled = true;
        $obj = $this->getEntryCommentsCustomdata($entryId);
        if ($obj && isset($obj->xml))
        {
            // process the customdata
            $xml = new SimpleXMLElement($obj->xml);
            $enabled = isset($xml->DisableComments) && (string) $xml->DisableComments === "true" ? false : true;
        }
        return $enabled;
    }

    public function getCommentsClosed($entryId)
    {
        // default is true
        $closed = false;
        $obj = $this->getEntryCommentsCustomdata($entryId);
        if ($obj && isset($obj->xml))
        {
            // process the customdata
            $xml = new SimpleXMLElement($obj->xml);
            $closed = isset($xml->CloseComments) && (string) $xml->CloseComments === "true" ? true : false;
        }
        return $closed;
    }

    public function editForm(Application_Form_EditChannel $form)
    {
        if(Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'showInChannels') === '1')
        {
        
            $translate = Zend_Registry::get('Zend_Translate');

            $spec = array(
                'belongsTo' => 'Category',
                'name' => 'enableComments',
                'description' => $translate->translate('Enable comments in channels'),
            );
            $element = new Zend_Form_Element_Checkbox($spec);
            $model = Kms_Resource_Models::getChannel();
            $channelId = isset($model->Category) && isset($model->Category->id) && $model->Category->id ? $model->Category->id : null;

            if ($channelId)
            {
                $allowed = $this->getCommentsAllowedInChannel($channelId);

                $element->setValue($allowed ? '1' : '0' );
            }
            else
            {
                $element->setValue('1');
            }
            $element->getDecorator('Description')->setTag('span');
            $element->removeDecorator('Label');
            $element->getDecorator('HtmlTag')->clearOptions()->setTag('div')->setOption('id', 'Category-comments-element');
            // add new form element to the channel edit form
            $form->addElement($element);
        }
    }

    /**
     * Implemeting interface save channel
     * @param Application_Model_Channel $model
     * @param array $data the post data
     */
    public function save(Application_Model_Channel $model, array $data)
    {
        if (isset($data['enableComments']))
        {
            $channelId = isset($model->Category) && isset($model->Category->id) && $model->Category->id ? $model->Category->id : null;
            $profileId = Kms_Resource_Config::getModuleConfig(strtolower(self::MODULE_NAME), 'channelCommentsProfileId');
            $obj = $this->getChannelCommentsCustomdata($channelId);
            if (!is_a($obj, 'Kaltura_Client_Metadata_Type_Metadata'))
            {
                // create new object ( since we have no object now )
                $xml = '<metadata><AllowCommentsInChannel>' . ($data['enableComments'] == '1' ? 'true' : 'false') . '</AllowCommentsInChannel></metadata>';
                if ($this->addChannelCustomdata($profileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, $channelId, $xml))
                {
                    Kms_Resource_Cache::apiClean('channel_comments', array('channelId' => $channelId));
                }
            }
            else
            {
                // process the customdata
                $xml = new SimpleXMLElement($obj->xml);
                // modify value
                $xml->AllowCommentsInChannel = $data['enableComments'] == '1' ? 'true' : 'false';

                if ($this->updateChannelCustomdata($obj->id, $xml->asXML(), $channelId))
                {
                    Kms_Resource_Cache::apiClean('channel_comments', array('channelId' => $channelId));
                }
            }
        }
    }

    public function updateChannelCustomdata($id, $xml, $channelId)
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try
        {
            $res = $metadataPlugin->metadata->update($id, $xml);
            return $res;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Failed updating customdata for channel Id ' . $channelId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
//                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
    }

    public function addChannelCustomdata($profileId, $objectType, $objectId, $xml)
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try
        {
            $res = $metadataPlugin->metadata->add($profileId, $objectType, $objectId, $xml);
            return $res;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Failed adding customdata for channel Id ' . $objectId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
        }
    }

    /**
     */
    public function saveEntryOptions($entryId, $options)
    {
        if ($entryId && is_array($options))
        {
            $profileId = Kms_Resource_Config::getModuleConfig(strtolower(self::MODULE_NAME), 'entryCommentsProfileId');
            $obj = $this->getEntryCommentsCustomdata($entryId);
            if (!is_a($obj, 'Kaltura_Client_Metadata_Type_Metadata'))
            {
                // create new object ( since we have no object now )
                $xml = '<metadata><DisableComments>' . (isset($options['disableComments']) && $options['disableComments'] === 'true' ? 'true' : 'false') . '</DisableComments><CloseComments>' . (isset($options['closeComments']) && $options['closeComments'] === 'true' ? 'true' : 'false') . '</CloseComments></metadata>';
                if ($this->addEntryCustomdata($profileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY, $entryId, $xml))
                {
                    Kms_Resource_Cache::apiClean('entry_comments_options', array('entryId' => $entryId));
                }
            }
            else
            {
                // process the customdata
                $xml = new SimpleXMLElement($obj->xml);
                // modify value
                if (isset($options['disableComments']))
                {
                    $xml->DisableComments = $options['disableComments'] === 'true' ? 'true' : 'false';
                }
                if (isset($options['closeComments']))
                {
                    $xml->CloseComments = $options['closeComments'] === 'true' ? 'true' : 'false';
                }
                if ($this->updateEntryCustomdata($obj->id, $xml->asXML(), $entryId))
                {
                    Kms_Resource_Cache::apiClean('entry_comments_options', array('entryId' => $entryId));
                }
            }
        }
    }

    public function updateEntryCustomdata($id, $xml, $entryId)
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try
        {
            $res = $metadataPlugin->metadata->update($id, $xml);
            return $res;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Failed updating customdata for entry Id ' . $entryId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
//                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
    }

    public function addEntryCustomdata($profileId, $objectType, $objectId, $xml)
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try
        {
            $res = $metadataPlugin->metadata->add($profileId, $objectType, $objectId, $xml);
            return $res;
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Failed adding customdata for entry Id ' . $objectId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
        }
    }

    /**
     * get the list of customdata profiles for the partner for entries objects
     */
    public static function configGetCustomdataProfiles()
    {
        return Kms_Helper_Metadata::getCustomdataProfiles(Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY);
    }

    /**
     * update entry count customdata field
     * @param type $entryId 
     */
    public function updateCount($entryId)
    {
        $profileId = Kms_Resource_Config::getModuleConfig('comments', 'entryCommentsCountProfileId');
        if ($profileId)
        {
            $client = Kms_Resource_Client::getAdminClient();
            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
            $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
            $filter->objectIdEqual = $entryId;
            $filter->metadataProfileIdEqual = $profileId;
            $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;
            $metadata = $metadataPlugin->metadata->listAction($filter);
            if (isset($metadata->objects) && count($metadata->objects))
            {
                $obj = $metadata->objects[0];
                $obj->xml = '<metadata><CommentsCount>' . $this->getCommentsCount(array('entryId' => $entryId)) . '</CommentsCount></metadata>';
                $metadataPlugin->metadata->update($obj->id, $obj->xml);
            }
            else
            {
                $xmlData = '<metadata><CommentsCount>' . $this->getCommentsCount(array('entryId' => $entryId)) . '</CommentsCount></metadata>';
                $metadataPlugin->metadata->add($profileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY, $entryId, $xmlData);
            }
        }
    }

    /**
     * Pass the sorters to the editSorters function
     *
     * @return array
     */
    public function editSorters(array $sorters)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        // only add comments in case the filter is of a media type (video audio image)
        // data entries dont have comments
        $front = Zend_Controller_Front::getInstance();
        //$type = $front->getRequest()->getParam('type');
        
        //if($type && ($type =='video' || $type =='audio' || $type=='image'))
        $commentsEnabled = $this->isCommentsEnabledByRequest($front->getRequest());
        
        if ($commentsEnabled)
        {
            // add entry for no role at all - owner only
            $sorters['comments'] = $translator->translate('Comments');
        }
        return $sorters;
        
    }
    
   /**
    * 
    * @param array $sorters
    * @param string $type
    * @return array of sorters
    */
    public function editSortersByType(array $sorters, $type)
    {
    	$translator = Zend_Registry::get('Zend_Translate');
    	$front = Zend_Controller_Front::getInstance();
    	$commentsEnabled = $this->isCommentsEnabledByRequest($front->getRequest());
    	if($commentsEnabled && $type && ($type == Webcast_Model_Webcast::MODULE_NAME))
    	{
    		// add entry for no role at all - owner only
    		$sorters['comments'] = $translator->translate('Comments');
    	}
    	return $sorters;
    
    }

    /**
     *   Edit the sort part of the base entry filter
     * 
     *   @param Kaltura_Client_Type_BaseEntryFilter
     *   @param array
     * 
     *   @return Kaltura_Client_Type_BaseEntryFilter
     */
    public function editSortFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $params)
    {
        if ($params == 'comments')
        {
            $filter->orderBy = NULL;
            $filter->advancedSearch = new Kaltura_Client_Metadata_Type_MetadataSearchItem();
            $filter->advancedSearch->metadataProfileId = Kms_Resource_Config::getModuleConfig('comments', 'entryCommentsCountProfileId');
            $filter->advancedSearch->orderBy = "-/*[local-name()='metadata']/*[local-name()='CommentsCount']";
        }

        return $filter;
    } 
/*
    public function getCustomdataCount($entryId)
    {
        $profileId = Kms_Resource_Config::getModuleConfig('comments', 'entryCommentsCountProfileId');
        // get the metadata from the api
        $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
        $filter->objectIdEqual = $entryId;
        $filter->metadataProfileIdEqual = $profileId;
        $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::ENTRY;
        $ret = null;
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

        try
        {
            $metadata = $metadataPlugin->metadata->listAction($filter);
            // test that we got the object from the correct profile
            if (!empty($metadata->objects) && count($metadata->objects))
            {
                if ($metadata->objects[0]->metadataProfileId != $profileId)
                {
                    Kms_Log::log('comments: Got wrong customdata for entry Id ' . $entryId . ' profileId ' . $profileId, Kms_Log::ERR);
                }
                else
                {
                    if(isset($metadata->objects[0]->xml))
                    {
                        $xml = new SimpleXMLElement($metadata->objects[0]->xml);
                        $ret = (int) $xml->CommentsCount;
                    }
                }
            }
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('comments: Failed getting customdata for entry Id ' . $entryId . ', ' . $e->getCode() . ': ' . $e->getMessage(), Kms_Log::WARN);
            //                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
        
        return $ret;
    }*/
    
	public function isCommentsEnabledByRequest($request)
    {
    	$dispatcher = $request->getParam('dispatcher');
        $inGallery = false;
        $commentsEnabled = true;
        if($request->getParam('categoryname'))
        {
            $inGallery = true;
        }
        elseif(is_array($dispatcher) && isset($dispatcher['controller']) && isset($dispatcher['module']) && isset($dispatcher['action']))
        {
            $inGallery = $dispatcher['module'] == 'default' &&
                            $dispatcher['controller'] == 'gallery' &&
                            $dispatcher['action'] == 'view';
        }
       
        // check if channel 
        $channelId = $request->getParam('channelid');
        if($channelId)
        {
            // check global configuration
            if(Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'showInChannels') === '0')
            {
                $commentsEnabled = false;
            }
            else
            {
                $channelModel = Kms_Resource_Models::getChannel();

                // get the single channel
                $channel = $channelModel->get($channelId);
                if(!$channel)
                {
                    $channel = $channelModel->getById($channelId);
                }
                
                if($channel )
                {
                    $commentsEnabled = $this->getCommentsAllowedInChannel($channel->id);
                }
            }
        }
        elseif($inGallery && Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'showInGalleries') === '0')
        {        // check configuration showingalleries
            $commentsEnabled = false;
        }
        return $commentsEnabled;
    }

}

