<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Comments_IndexController extends Kms_Module_Controller_Abstract
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
        $contextSwitch->addActionContext('add', 'ajax')->initContext();
        $contextSwitch->addActionContext('index', 'ajax')->initContext();
        $contextSwitch->addActionContext('reply', 'ajax')->initContext();
        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('delete', 'ajax')->initContext();
        $contextSwitch->addActionContext('save-option', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();

        
        $this->view->defaultText = $this->_translate->translate('Add a Comment');       
        $this->view->defaultReplyText = $this->_translate->translate('Add a Reply');
        
        // if sort by createdby descending, then box should be on top
        $this->view->boxOnTop = Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'sort') == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC;        
        
        // for replies - box always on top
        $this->view->replyBoxOnTop = true;//Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'sortReplies') == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC;        
        $this->view->repliesNewestFirst =  Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'sortReplies') == Kaltura_Client_CuePoint_Enum_CuePointOrderBy::CREATED_AT_DESC;
        
        $this->view->commentsEnabled = true;
        $this->view->commentsClosed = false;
        $this->view->allowClose = Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'allowClose') === '1';
        // check if allowed
        
        $this->view->allowedToPost = false;
        $allowedRoles = Kms_Resource_Config::getModuleConfig(strtolower(Comments_Model_Comments::MODULE_NAME), 'commentsAllowed');
        
        if(count($allowedRoles))
        {
            // check if user's role allows to post
            foreach($allowedRoles as $role)
            {
                if(trim($role) && $role == Kms_Plugin_Access::getCurrentRole())
                {
                    $this->view->allowedToPost = true;
                    break;
                }
            }
        }
        
        $model = new Comments_Model_Comments();
        // get the entry object 
        $entryModel = Kms_Resource_Models::getEntry();
        $entryId = $entryModel->id;
        if(!$entryId)
        {
            $entryId = $this->getRequest()->getParam('entryid');
            if($entryId)
            {
                $entryModel->get($entryId, false);
                $entryId = $entryModel->id;
            }
        }
        
        if($entryId)
        {
            $this->view->commentsEnabled = $model->getCommentsEnabled($entryId) ? true : false;
            $this->view->commentsClosed = $this->view->allowClose && $model->getCommentsClosed($entryId) ? true : false;
        }
        
        
        $this->view->commentsEnabled = $this->view->commentsEnabled && $model->isCommentsEnabledByRequest($this->getRequest());
        $this->view->disableComments = !$this->view->commentsEnabled;
        $this->view->entryId = $entryId;
        
        
    }

    public function tabcontentAction()
    {
        if($this->view->commentsEnabled)
        {
            $this->view->form = new Comments_Form_AddComment();
            $entryModel = Kms_Resource_Models::getEntry();
            if(!$entryModel->id)
            {
                $entryId = $this->getRequest()->getParam('entryid');
                $entryModel->get($entryId);
                
            }
            else
            {
                $entryId = $entryModel->id;
            }
            
            $start = $this->getRequest()->getParam('start');
            $this->view->form->setAction($this->view->baseUrl('/comments/index/add/'));
            $this->view->form->entryId->setValue($entryId);
            $commentsModel = new Comments_Model_Comments();
            $this->view->commentsCount = $commentsModel->getCommentsCount(array('entryId' => $entryId));
            $this->view->entryId = $entryId;
        }        
    }
    
    
    public function indexAction()
    {
        if($this->view->commentsEnabled)
        {
            $this->view->form = new Comments_Form_AddComment();
            $entryModel = Kms_Resource_Models::getEntry();
            if(!$entryModel->id)
            {
                $entryId = $this->getRequest()->getParam('entryid');
                $entryModel->get($entryId);
                
            }
            else
            {
                $entryId = $entryModel->id;
            }
            
            $start = $this->getRequest()->getParam('start');
            $this->view->form->setAction($this->view->baseUrl('/comments/index/add/'));
            $this->view->form->entryId->setValue($entryId);
            $commentsModel = new Comments_Model_Comments();
            $this->view->commentsCount = $commentsModel->getCommentsCount(array('entryId' => $entryId));
            $this->view->entryId = $entryId;
            $moduleData = $commentsModel->getComments(array('entryId' => $entryId, 'timeFrom' => $start));
                
            $this->view->comments = isset($moduleData['comments']) ? $moduleData['comments'] : array();
            $this->view->replies = isset($moduleData['replies']) ? $moduleData['replies'] : array();
        }        
    }
    
    
    public function addAction()
    {
        if($this->view->commentsEnabled && !$this->view->commentsClosed && $this->view->allowedToPost)
        {
            $model = new Comments_Model_Comments();
            $this->view->comment = $model->add(array(
                'entryId' => $this->getRequest()->getParam('entryId'),
                'parentId' => $this->getRequest()->getParam('parentId'),
                'body' => trim(strip_tags($this->getRequest()->getParam('commentsbox')))
            ));
            $this->view->parentId = $this->getRequest()->getParam('parentId');
            if($this->view->comment)
            {
                $this->view->replyTo = $this->getRequest()->getParam('replyTo') ? $this->getRequest()->getParam('replyTo') : $this->view->comment->userId;
                $this->view->commentsCount = $model->getCommentsCount(array('entryId' => $this->getRequest()->getParam('entryId')));
            }
            else
            {
                exit;
            }
        }
    }

    
    public function tabAction()
    {
        
    }
    
    public function deleteAction()
    {
        $this->view->commentId = $this->getRequest()->getParam('commentId');
        $this->view->confirm = $this->getRequest()->getParam('confirm');
        
        if($this->view->commentId && $this->view->confirm == '1')
        {
            $model = new Comments_Model_Comments();
            $model->delete($this->view->commentId);
            $this->view->commentsCount = $model->getCommentsCount(array('entryId' => $this->getRequest()->getParam('entryId')));
        }
        
    }
    
    public function replyAction()
    {
        $this->view->entryId = $this->getRequest()->getParam('entryId');
        $this->view->commentId = $this->getRequest()->getParam('commentId');
        $this->view->replyTo = $this->getRequest()->getParam('replyTo');
        
        $this->view->form = new Comments_Form_AddComment( array('defaultKeyword' => $this->view->defaultReplyText));
        $this->view->form->setAttrib('id', 'addComment_'.$this->view->commentId);
        $this->view->form->setAttrib('class', 'addComment reply');
        $this->view->form->setAction($this->view->baseUrl('/comments/index/add/'));
        $this->view->form->entryId->setValue($this->view->entryId);
        $this->view->form->parentId->setValue($this->view->commentId);
        $this->view->form->replyTo->setValue($this->view->replyTo); 
        
        
    }
    
    public function optionsAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();     
        $this->view->entryId = $entryModel->id;
        $model = new Comments_Model_Comments();
        $this->view->commentsClosed = $model->getCommentsClosed($this->view->entryId) ;
        $this->view->disableComments = !$model->getCommentsEnabled($this->view->entryId) ;
        
    }
    
    public function saveOptionAction()
    {
        $entryId = $this->getRequest()->getParam('entryid');
        $option = $this->getRequest()->getParam('option');
        $value = $this->getRequest()->getParam('value');
        $entryModel = Kms_Resource_Models::getEntry();
        if($entryId && !$entryModel->id)
        {
            $entryModel->get($entryId, false);
        }
        if($entryId && $option && $value && Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId))
        {
            $model = new Comments_Model_Comments();
            $model->saveEntryOptions($entryId, array($option => $value));
            
            $this->view->commentsClosed = $model->getCommentsClosed($entryId) ;
            $this->view->disableComments = !$model->getCommentsEnabled($entryId) ;
            $this->view->entryId = $entryModel->id;
        }
        
    }
    
}



