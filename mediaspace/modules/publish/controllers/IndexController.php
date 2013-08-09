<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Publish_IndexController extends Kms_Module_Controller_Abstract
{
    private $publishPermission;
    private $entryModel;
    private $_flashMessenger;
    private $_translate;
    
    public function init()
    {
        /* initialize translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('default');
        $this->view->messages = $this->_flashMessenger->getMessages();
        
        /* Initialize contexts here */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $ajaxC = $contextSwitch->setContext('ajax', array());
        $ajaxC->setAutoDisableLayout(false);

        $dialogC = $contextSwitch->setContext('dialog', array());
        $dialogC->setAutoDisableLayout(false);

        $scriptC = $contextSwitch->setContext('script', array());
        $scriptC->setAutoDisableLayout(false);

        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('publish', 'dialog')->initContext();
        $contextSwitch->addActionContext('unpublish', 'dialog')->initContext();
        $contextSwitch->addActionContext('make-private', 'dialog')->initContext();
        $contextSwitch->addActionContext('publish', 'ajax')->initContext();
        $contextSwitch->addActionContext('unpublish', 'ajax')->initContext();
        $contextSwitch->addActionContext('unpublish', 'dialog')->initContext();
        $contextSwitch->addActionContext('remove', 'dialog')->initContext();
        $contextSwitch->addActionContext('bulk', 'dialog')->initContext();
        $this->_helper->contextSwitch()->initContext();
        
        $this->view->channelsForPublish = Kms_Resource_Models::getChannel()->getChannelsForPublish();
        
        // check if user has the publish permission (can assign categories)
        $this->view->canPublishToGalleries = $this->getFrontController()->getPlugin('Kms_Plugin_Access')->hasPermission('entry', 'setcategory');
        $this->publishPermission = $this->view->canPublishToGalleries || count($this->view->channelsForPublish);
        
        // also check if the entry belongs to our user
        $userId = Kms_Plugin_Access::getId();
        $this->entryModel = Kms_Resource_Models::getEntry();
        if($this->entryModel && $this->entryModel->entry && $this->entryModel->entry->userId && Kms_Plugin_Access::isCurrentUser($this->entryModel->entry->userId) && $this->publishPermission)
        {
            $this->view->allowed = true;
        }
        else
        {
            $this->view->allowed = false;
            return false;
        }
    }

    public function indexAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        $categoryModel = Kms_Resource_Models::getCategory();
        
        $this->view->entryCats = $entryModel->getEntryCategories($entryModel->entry->id);
       
        // is entry under moderation?
        $this->view->pending = $entryModel->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;
        
        // is entry still converting?
        $this->view->converting = $entryModel->entry->status == Kaltura_Client_Enum_EntryStatus::READY ? false : true;
        
        // are all required fields filled out?
        $this->view->readyToPublish = $entryModel->readyToPublish;
        
        /// check for permission and for moderation status
        $this->view->allowed = $this->publishPermission && !$this->view->pending && !$this->view->converting && $this->view->readyToPublish;
        $this->view->entry = $entryModel->entry;
        if($this->publishPermission)
        {
            // retreive the categories
            Kms_Resource_Nav::initNavigation();
            $this->view->cats = Kms_Resource_Nav::getCategoryTree();
        }
        
    }

    public function tabAction()
    {
        // action body
    }

    public function unpublishAction()
    {
        $this->_forward('publish', null, null, array('unpublish' => 'true'));
    }
    
    
    public function makePrivateAction()
    {
        // unpublish all categories
        $entryId = $this->getRequest()->getParam('entry');
        $entryModel = Kms_Resource_Models::getEntry();
        $entryModel->get($entryId, false);
        $this->view->entry = $entryModel->entry;

        $entryUser = $entryModel->entry->userId;
        $currentUser = null;
        
        $this->view->entry = $entryModel->entry;
        $this->view->entryUser = $entryUser;
        $this->view->isEntryOwner = Kms_Plugin_Access::isCurrentUser($entryUser);

        if($this->getRequest()->getParam('confirm') == '1')
        {
            $res = $entryModel->updateCategories($entryId, array());
            $this->view->entryCats = $entryModel->getEntryCategories($entryModel->entry->id);
            $this->view->scriptOut = '$("#dialog").dialog(\'close\');$("#publishcats").hide();$("#publishradio-0").attr("checked", "checked");$("#publish_tab > .baloons").text("");';
            $this->_forward('publish', 'index', 'publish', array('leon' => 1, 'entry' => $entryId, 'format' => 'ajax'));
        }
    }

    public function publishAction()
    {
        // are all required fields filled out?
        Kms_Resource_Nav::initNavigation();
        // update entry categories here
        $boxId = $this->getRequest()->getParam('boxId');
        $entryArray = explode(',', $this->getRequest()->getParam('entry'));
        $categoryArray = $this->getRequest()->getParam('category') ? explode(',', $this->getRequest()->getParam('category')) : array();
        $this->view->bulk = $bulk = $this->getRequest()->getParam('bulk');
        $this->view->categoriesLimitReached = false;
        if($bulk)
        {
            // set time limit to 2 minutes
            set_time_limit(120);
        }
        
        // only one category pushed, and boxId - meaning that we are in the publish tab
        if(count($categoryArray) <= 1)
        {
            $this->view->target = '#publish_tab';
//            $this->_forward('index', 'index', 'publish', array('format' => 'ajax'));
//            $this->view->checked = 1;
        }
        
        $entryModel = Kms_Resource_Models::getEntry();
        $categoryModel = Kms_Resource_Models::getCategory();
        
        $categoriesById = $categoryModel->getList();
        $this->view->couldNotPublish = array();
        $this->view->publishedCategories = array();
        $publishStats = array('entries' => array(), 'categories' => array());
        foreach($entryArray as $entryId)
        {
            $entry = $entryModel->get($entryId);
            if(!is_null($entry))
            {
                // is entry under moderation?
                $this->view->pending = $entryModel->entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;

                // is entry still converting?
                $this->view->converting = $entryModel->entry->status == Kaltura_Client_Enum_EntryStatus::READY ? false : true;

                // are all required fields filled out?
                $this->view->readyToPublish = $entryModel->readyToPublish;

                /// check for permission and for moderation status
                $this->view->allowed = Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId) && $this->publishPermission && !$this->view->pending && !$this->view->converting && $this->view->readyToPublish;

                if($this->view->allowed)
                {
                    $unpublish = $this->getRequest()->getParam('unpublish');
                    // check if publish or unpublish
                    if($unpublish && $unpublish == 'true')
                    {
                        Kms_Log::Log('publish: unpublishing '.$entryId.' from categories: '.Kms_Log::printData($categoryArray), Kms_Log::DEBUG);
                        foreach($categoryArray as $category)
                        {
                            // forwarded from unpublish action
                            try
                            {
                                $res = $entryModel->removeCategory($entryId, $category);
                            }
                            catch(Kaltura_Client_Exception $e)
                            {
                                if($e->getCode() == 'MAX_CATEGORIES_FOR_ENTRY_REACHED')
                                {
                                    $this->view->categoriesLimitReached = true;
                                }
                            }
                        }
                    }
                    else
                    {
                        if(count($categoryArray))
                        {
                            // add category
                            Kms_Log::Log('publish: publishing '.$entryId.' to categories: '.Kms_Log::printData($categoryArray), Kms_Log::DEBUG);
                            try
                            {
                                $res = $entryModel->addCategory($entryId, $categoryArray);
                                // update statistics count for display of bulk action results
                                if($res)
                                { 
                                    if(!isset($publishStats['entries'][$entryId]))
                                    {
                                        $publishStats['entries'][$entryId] = 1;
                                    }
                                }
                            }
                            catch(Kaltura_Client_Exception $e)
                            {
                                if($e->getCode() == 'MAX_CATEGORIES_FOR_ENTRY_REACHED')
                                {
                                    $this->view->categoriesLimitReached = true;
                                }
                            }
                        }
                    }

                    if(!$bulk)
                    {
                        $entryUser = $entryModel->entry->userId;
                        $currentUser = null;
                        $identity = Zend_Auth::getInstance()->getIdentity();
                        if($identity)
                        {
                            $currentUser = $identity->getId();
                        }


                        $this->view->entryCats = $entryModel->getEntryCategories($entryModel->entry->id);
                        $this->view->entry = $entryModel->entry;
                        $this->view->entryUser = $entryUser;
                        $this->view->isEntryOwner = Kms_Plugin_Access::isCurrentUser($entryUser);

                        if(!$this->view->scriptOut)
                        {
                            $this->view->scriptOut = '';
                        }
                    }
                }
            }
           // $entryNewCategories = 
        }
        
        
        if($bulk)
        {
            $entryCount = count($publishStats['entries']);
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
                        $this->_translate->translate('published').
                        '.';
                $this->_flashMessenger->addMessage($msg);
                
                $notPublishedEntries = count($entryArray) - $entryCount;
                if($notPublishedEntries > 0)
                {
                    $msg = $this->_translate->translate('Failure') . 
                    ': ' . $notPublishedEntries . 
                    ' ' .
                    ( $notPublishedEntries > 1 ? $this->_translate->translate('media') : $this->_translate->translate('media') ) .
                    ' ' .
                    ( $notPublishedEntries > 1 ? $this->_translate->translate('were') : $this->_translate->translate('was') ) .
                    ' '.
                    $this->_translate->translate('not published').
                    '.';
                    $this->_flashMessenger->addMessage($msg);
                }
                
            }
            else
            {
                $msg = $this->_translate->translate('Oops, something went wrong. No media was published.');
                $this->_flashMessenger->addMessage($msg);
            }
            
        }
    }

    public function bulkAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        $entryIds = $this->getRequest()->getParam('id');
        $entryIdsArray = explode(',', $entryIds);
        if(count($entryIdsArray))
        {
            /// check for permission and for moderation status
            $this->view->allowed = $this->publishPermission;
            $this->view->entryIds = $entryIdsArray;
            if($this->publishPermission)
            {
                // retreive the categories
                Kms_Resource_Nav::initNavigation();
                $this->view->cats = Kms_Resource_Nav::getCategoryTree();
                $this->view->channels = Kms_Resource_Models::getChannel()->getChannelsForPublish();
            }
        }
        else
        {
            exit;
        }
                
    }
    
    public function bulkButtonAction()
    {
        
    }
    

}







