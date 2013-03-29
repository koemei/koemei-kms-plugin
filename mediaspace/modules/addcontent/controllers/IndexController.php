<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Addcontent_IndexController extends Kms_Module_Controller_Abstract
{
    private $_flashMessenger;
    private $_translate = null;

    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');

        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');  
        $this->_flashMessenger->setNamespace('default');
        
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        if(!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->addActionContext('add', 'ajax')->initContext();
        $contextSwitch->addActionContext('index', 'dialog')->initContext();
        $contextSwitch->addActionContext('index', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }
    

    public function indexAction()
    {
        $categoryId = $this->getRequest()->getParam('categoryid');
        $channelId = $this->getRequest()->getParam('channelid');
		
        if($categoryId)
        {
            $this->view->categoryId = $categoryId;
        }
        elseif($channelId)
        {
            $this->view->categoryId = $channelId;
            $this->view->channelId = $channelId;
        }
            
        $this->view->allowed = true;
        
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        
        $params['userId'] = $identity->getId();
        $params['page'] = $this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1;
        
        // supported upload types
        //@todo set up the logic to determine these switches
        $this->view->mediaUpload = true;
        $this->view->webcamUpload = true;

        $this->view->presentationUpload = Kms_Resource_Config::getConfiguration('application', 'enablePresentations');
        
        $model = new Addcontent_Model_Addcontent();
        $this->view->entries = $model->getEntries($params);

        $this->view->published = array();
        if(count($this->view->entries))
        {
        	// check if entries are published in this category
            $entriesToCheck = array();
            foreach($this->view->entries as $entryModelObj)
            {
                $entriesToCheck[] = $entryModelObj->id;
            }

            $entriesPublished = Kms_Resource_Models::getEntry()->getPublishedInCategory($entriesToCheck, $this->view->categoryId);
            if(is_array($entriesPublished))
            {
                foreach($entriesPublished as $entry)
                {
                    if(isset($entry->entryId) && isset($entry->categoryId) && $entry->categoryId == $this->view->categoryId)
                    {
                        $this->view->published[$entry->entryId] = 1;
                    }
                }
            }

            // init paging
            $pagingAdapter = new Zend_Paginator_Adapter_Null( $model->totalCount );
            $paginator = new Zend_Paginator( $pagingAdapter );
            // set the page number
            $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
            $pageSize = Application_Model_Entry::MY_MEDIA_LIST_PAGE_SIZE;
            $paginator->setItemCountPerPage($pageSize);
            $paginator->setPageRange(Kms_Resource_Config::getConfiguration('gallery', 'pageCount'));
            
            $this->view->paginator = $paginator;
            $this->view->pagerType = Kms_Resource_Config::getConfiguration('gallery', 'pagerType');

            $this->view->noEnries = false;
        }
        else
        {
            $this->view->noEnries = true;
        }
    }
    
    public function buttonAction()
    {
    	
        if($this->getRequest()->getParam('categoryid'))
        {
            $model = Kms_Resource_Models::getCategory();
            $pageCategoryId = $this->getRequest()->getParam('categoryid');
            if($model->Category && $model->Category->id == $pageCategoryId)
            {
                $this->view->categoryId = $model->Category->id;
            }
            else
            {
                $model->get(null, false, $pageCategoryId);
                if($model->Category && $model->Category->id == $pageCategoryId)
                {
                    $this->view->categoryId = $model->Category->id;
                }
                else
                {
                    // got here because maybe privacy context is off
                    //throw new Zend_Controller_Action_Exception('addcontent: Failed to get the category from API ('.$pageCategoryId.').');
                    $this->view->categoryId = null;
                }
            }

            // check for galleries contextual role. channels contextual roles is already checked when we get here.
            if($this->view->categoryId)
            {
                // this gallery requires contextual role. 
                if($model->Category->membership != Application_Model_Category::MEMBERSHIP_OPEN)
                {
                    // get the role for this user on this gallery
                    $role = $model->getUserRoleInCategory($model->Category->name, Kms_Plugin_Access::getId(), $model->Category->id);

                    // contextual role disallows publishing
                    if ($role > Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR) {
                        $this->view->categoryId = null;
                    }
                }
            }
        }
        elseif($this->getRequest()->getParam('channelname'))
        {
            $model = Kms_Resource_Models::getChannel();
            $channel = $model->getCurrent($this->getRequest()->getParam('channelname'), $this->getRequest()->getParam('channelid'));
            $this->view->channelId = $channel->id;
        }
    }
    
    public function addAction()
    {
        $entryIds = $this->getRequest()->getParam('sel-entry');
        $categoryId = $this->getRequest()->getParam('categoryid');
        $channelId = $this->getRequest()->getParam('channelid');
        
        if(!$categoryId && $channelId)
        {
            $categoryId = $channelId;
        }
        
        $entryModel = Kms_Resource_Models::getEntry();
        
        $success = 0;
        if(count($entryIds))
        {
            foreach($entryIds as $entryId)
            {
                if($entryModel->addCategory($entryId, $categoryId))
                {
                    $success++;
                }
            }

            if($success == 1)
            {
                $this->_flashMessenger->addMessage($this->_translate->translate('The media was published successfully'));
            }
            elseif($success > 1)
            {
                $this->_flashMessenger->addMessage($success .' '. $this->_translate->translate(' media were published successfully'));
            }
            elseif ($success < count($entryIds)) 
            {
                $this->_flashMessenger->addMessage($this->_translate->translate('An error occured while trying to publisg media.'));
            }
        }
        else
        {
            // no entries selected
            die();
        }

    }
    


}



