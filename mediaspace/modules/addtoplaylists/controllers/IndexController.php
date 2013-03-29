<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Addtoplaylists_IndexController extends Kms_Module_Controller_Abstract
{
    private $_translate;
    
    public function init()
    {
        /* initialize translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('default');
        $this->view->messages = $this->_flashMessenger->getMessages();
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());

            $ajaxC->setAutoDisableLayout(false);
        }
        $dialogC = $contextSwitch->setContext('dialog', array());
        $dialogC->setAutoDisableLayout(false);

        $scriptC = $contextSwitch->setContext('script', array());
        $scriptC->setAutoDisableLayout(false);

        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('add-entry', 'ajax')->initContext();
        $contextSwitch->addActionContext('add-new-playlist', 'ajax')->initContext();
        $contextSwitch->addActionContext('bulk', 'dialog')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }
    

    public function indexAction()
    {
       // check if user has the publish permission (can edit playlist)
        $allowed = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('playlist', 'edit');
        
        $playlists = array();
        // also check if the entry belongs to our user
        $entryModel = Kms_Resource_Models::getEntry();
        // is entry under moderation?
        $this->view->pending = $entryModel->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;
        // is entry still converting?
        $this->view->converting = $entryModel->entry->status == Kaltura_Client_Enum_EntryStatus::READY ? false : true;
        // are all required fields filled out?
        $this->view->readyToPublish = $entryModel->readyToPublish;
        
        // can the user edit this entry? for displaying the link
        $this->view->canEdit = Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId);
        
        $this->view->allowed = $allowed && !$this->view->pending && !$this->view->converting && $this->view->readyToPublish;
        
        if($entryModel && $entryModel->entry && isset($entryModel->entry->mediaType))
        {
            $this->view->entryId = $entryModel->entry->id;
            if($this->view->allowed)
            {
                $playlistModel = Kms_Resource_Models::getPlaylist();
                $userId = Kms_Plugin_Access::getId();
                $playlists = $playlistModel->getUserPlaylists($userId);
                if(isset($playlists->objects) && count($playlists->objects))
                {
                    $playlists = $playlists->objects;
                }

                $this->view->allowCreate = Kms_Resource_Config::getModuleConfig('addtoplaylists', 'allowCreation');
                $entryPlaylists = array();

                foreach($playlists as $playlist)
                {
                    if(isset($playlist->playlistContent))
                    {
                        $playlistEntries = explode(',', $playlist->playlistContent);
                        if (in_array($this->view->entryId, $playlistEntries))
                        {
                            $entryPlaylists[$playlist->id] = array('id' => $playlist->id, 'name' => $playlist->name);
                        }
                    }
                }

                $this->view->entryPlaylists = $entryPlaylists;
                $this->view->playlists = $playlists;
            }
        }
        else
        {
            $this->view->allowed = false;
        }
        
        
    }
    
    
    public function addEntryAction()
    {
        // check if user has the publish permission (can edit playlist)
        $allowed = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('playlist', 'edit');
        $addStats = array();
        $playlistIdArray = explode(',', $this->getRequest()->getParam('playlist'));
        $entryIdArray = explode(',', $this->getRequest()->getParam('entry'));
        $this->view->bulk = $bulk = $this->getRequest()->getParam('bulk');
        
        if($bulk)
        {
            // set time limit to 2 minutes
            set_time_limit(120);
        }
        
        
        $this->view->success = false;
        foreach($entryIdArray as $entryId)
        {
            $this->view->entryId = $entryId;
            $entryModel = Kms_Resource_Models::getEntry();
            $entry = $entryModel->get($entryId);
            // is entry under moderation?
            $this->view->pending = $entryModel->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;
            // is entry still converting?
            $this->view->converting = $entryModel->entry->status == Kaltura_Client_Enum_EntryStatus::READY ? false : true;
            // are all required fields filled out?
            $this->view->readyToPublish = $entryModel->readyToPublish;

            $this->view->allowed = $allowed && !$this->view->pending && !$this->view->converting && $this->view->readyToPublish;

            if($entryModel && $entryModel->entry && isset($entryModel->entry->mediaType) && $this->view->allowed)
            {
                foreach($playlistIdArray as $playlistId)
                {
                    if($entryId && $playlistId)
                    {
                        $model = Kms_Resource_Models::getPlaylist();
                        try
                        {
                            if($model->entryExistsInPlaylist($playlistId, $entryId))
                            {
                                Kms_Log::log('playlist: entry '.$entryId.' exists in playlist ' . $playlistId, Kms_Log::DEBUG) ;                                                    

                                if(!$bulk)
                                {
                                    $playlist = $model->removeEntryFromPlaylist($playlistId, $entryId);
                                    $this->view->checked = false;
                                }
                            }
                            else
                            {
                                $playlist = $model->addEntriesToPlaylist($playlistId, array($entry));
                                $this->view->checked = true;
                                if (!empty($playlist)) {
                                    $addStats[$entry->id] = 1;
                                }
                            }
                            
                            if(isset($playlist) && $playlist)
                            {
                                $this->view->playlist = $playlist;
                                $this->view->success = true;
                            }
                        }
                        catch(Kaltura_Client_Exception $e)
                        {
                            Kms_Log::log('playlist: Failed to update playlist '.$playlistId.' : ' . $e->getCode().': '.$e->getMessage());
                        }
                    }
                }
            }
        }
        
        if($bulk)
        {
            $entryCount = count($addStats);
            $this->view->scriptOut = '$("body").addClass("cursorwait");document.location.reload()';
            if($entryCount)
            {
                $msg = $this->_translate->translate('Success') . 
                        ': ' . 
                        $entryCount . 
                        ' ' .
                        ( $entryCount > 1 ? $this->_translate->translate('media') : $this->_translate->translate('media') ) .
                        ' ' .
                        ( $entryCount > 1 ? $this->_translate->translate('were') : $this->_translate->translate('was') ) .
                        ' '.
                        $this->_translate->translate('added to playlist/s').
                        '.';
                $this->_flashMessenger->addMessage($msg);
            }
            else
            {
                $msg = $this->_translate->translate('Oops, something went wrong. No media was added.');
                $this->_flashMessenger->addMessage($msg);
            }
            
        }
        
    }

    public function addNewPlaylistAction()
    {
           // check if user has the publish permission (can edit playlist)
        $allowed = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('playlist', 'edit');
        $entryId = $this->getRequest()->getParam('entry');
        $name = $this->getRequest()->getParam('playlist');
        $playlistId = $this->getRequest()->getParam('playlist');
        $entryId = $this->getRequest()->getParam('entry');
        $this->view->success = false;
        $this->view->entryId = $entryId;
        $entryModel = Kms_Resource_Models::getEntry();
        $entryModel->get($entryId);
        // is entry under moderation?
        $this->view->pending = $entryModel->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;
        // is entry still converting?
        $this->view->converting = $entryModel->entry->status == Kaltura_Client_Enum_EntryStatus::READY ? false : true;
        // are all required fields filled out?
        $this->view->readyToPublish = $entryModel->readyToPublish;
        
        $this->view->allowed = $allowed && !$this->view->pending && !$this->view->converting && $this->view->readyToPublish;
        
        if($entryModel && $entryModel->entry && isset($entryModel->entry->mediaType) && $this->view->allowed)
        {
        
            $this->view->success = false;

            if($entryId && $name)
            {
                $model = Kms_Resource_Models::getPlaylist();
                try
                {
                    $playlist = $model->createPlaylist($name, $entryId);
                    $this->view->entryId = $entryId;
                    $this->view->playlist = $playlist;
                    $this->view->success = true;
                }
                catch(Kaltura_Client_Exception $e)
                {

                }
            }
        }
    }
    
    
    public function tabAction()
    {
        $this->view->allowed = false;
        $entryModel = Kms_Resource_Models::getEntry();
        if($entryModel && $entryModel->entry && isset($entryModel->entry->mediaType))
        {
            $this->view->allowed = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('playlist', 'edit');
        }
        
    }
    
    public function bulkButtonAction()
    {
        
        
    }

    
    public function bulkAction()
    {
       // check if user has the publish permission (can edit playlist)
        $userId = Kms_Plugin_Access::getId();
        $allowed = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('playlist', 'edit');
        $entryArray = explode(',', $this->getRequest()->getParam('id'));
        
        $this->view->entryIds = $entryArray;
        
        $this->view->allowed = $allowed;
        
        $playlistsModel = Kms_Resource_Models::getPlaylist();
        $entryModel = Kms_Resource_Models::getEntry();
        $playlists = $playlistsModel->getUserPlaylists($userId);
        // uncomment to test "no playlists" mode
//        $playlists = array();
        if (isset($playlists->objects) && count($playlists->objects))
        {
            $this->view->playlists = $playlists->objects;
        }
        
    }
}



