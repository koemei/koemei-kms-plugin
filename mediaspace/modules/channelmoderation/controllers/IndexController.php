<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Channel Moderation Module Controller
 *
 */
class Channelmoderation_IndexController extends Kms_Module_Controller_Abstract
{
    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('index', 'ajax')->initContext();
        $contextSwitch->addActionContext('approve', 'ajax')->initContext();
        $contextSwitch->addActionContext('reject', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();

        $this->view->tabName = 'pending';
    }
    
    /**
     * add a pending media link to channels in channel list view
     */
    public function linkAction()
    {
        $this->view->channelName = $this->getRequest()->getParam('channelname');
        $this->view->channelId = $this->getRequest()->getParam('channelid');
        $this->view->pendingEntriesCount = $this->getRequest()->getParam('pending');
    }
    
    /**
     * add a channel moderation tab link
     */
    public function tabAction()
    {
        // get the channel
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->Category;
        
        $userId = Kms_Plugin_Access::getId();
        if(Kms_Resource_Models::getChannel()->getUserRoleInChannel($channel->name, $userId, $channel->id) == Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR)
        {
            $this->view->tabActive = true;
        }
        else
        {
            $this->view->tabActive = $this->getRequest()->getParam('tab') == $this->view->tabName;
        }            
    
        if(!empty($channel))
        {
            $this->view->channel = $channel;
        }
    }
    
    /**
     * the channel mderation tab content
     */
    public function indexAction()
    {   
        // get the channel model
        $model = Kms_Resource_Models::getChannel();
        
        // get the channel
        $channel = $model->getCurrent($this->getRequest()->getParam('channelname'), $this->getRequest()->getParam('channelid'));
        
        if(!empty($channel))
        {
            $userId = Kms_Plugin_Access::getId();
            if(Kms_Resource_Models::getChannel()->getUserRoleInChannel($channel->name, $userId, $channel->id) == Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR)
            {
                $this->view->tabActive = true;
            }
            else
            {
                $this->view->tabActive = $this->getRequest()->getParam('tab') == $this->view->tabName;
            }            
            
            $this->view->channelName = $channel->name;
            $this->view->channel = $channel;
                        
            // activate the tab
            $this->view->activateTab = false;
            $tabParam = $this->view->tabActive ? $this->view->tabName : $this->getRequest()->getParam('tab');
            if ($tabParam == $this->view->tabName){
            
                // get the params
                $params = array(
                        'page' => $this->getRequest()->getParam('page'),
                        'sort' => $this->getRequest()->getParam('sort'),
                        'keyword' => $this->getRequest()->getParam('keyword'),
                        'type' => $this->getRequest()->getParam('type'),
                        'view' => $this->getRequest()->getParam('view'),
                        'tag' => $this->getRequest()->getParam('tag'),
                        'tab' => $this->view->tabName,
                );                    

                if (!$params['sort']){
                    $params['sort'] = 'recent';
                }
                
                if(isset($params['view']) && $params['type'] == 'short'){
                    $params['pageSize'] = Application_Model_Entry::MY_MEDIA_LIST_PAGE_SIZE;
                }
                else{
                    $params['pageSize'] = Application_Model_Entry::MY_MEDIA_PAGE_SIZE;
                }

                $this->view->params = $params;

                // keep those seperate - we dont want them all over our links
                $this->view->urlParams = array(
                        'module' => $this->getRequest()->getModuleName() ,
                        'controller' => $this->getRequest()->getControllerName() ,
                        'action' => $this->getRequest()->getActionName() ,
                        'channelname' => Kms_View_Helper_String::removeSpecialChars($channel->name) , 
                		'channelid' => $channel->id ,
                        );

                $this->view->itemType = $this->getRequest()->getParam('view') ? $this->getRequest()->getParam('view') : 'full';

                // get the channel entries pending moderation
                $totalEntries = 0;
                $model = new Channelmoderation_Model_Channelmoderation();
                $this->view->entries = $model->getPendingEntries($channel, $params);
                $totalEntries = $model->getLastResultCount();

                // avoid empty results when not in first page
                if (empty($this->view->entries) && $params['page'] > 1){
                    $params['page']--;
                    $this->view->params = $params;
                    $this->view->entries = $model->getPendingEntries($channel, $params);
                }
                
                // init paging
                $pagingAdapter = new Zend_Paginator_Adapter_Null( $totalEntries );
                $paginator = new Zend_Paginator( $pagingAdapter );

                // set the page number
                $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
                // set the number of items per page
                $paginator->setItemCountPerPage( $params['pageSize']);
                // set the number of pages to show
                $paginator->setPageRange(Kms_Resource_Config::getConfiguration('gallery', 'pageCount'));

                $this->view->paginator = $paginator;
                $this->view->pagerType = Kms_Resource_Config::getConfiguration('gallery', 'pagerType');
            }
            else
            {
                $this->_helper->viewRenderer->setNoRender(TRUE);
            }
        }        
    }   
    
    /**
     * approve entries for publication in a channel
     */
    public function approveAction()
    {       
        // get the channel model
        $channelId = $this->getRequest()->getParam('channelid');
        $model = Kms_Resource_Models::getChannel();

        // get the channel
        if (isset($model->Category) && $model->Category->id == $channelId){
            $channel = $model->Category;
        }
        else{
            $channel = $model->get($this->getRequest()->getParam('channelname'), false, $channelId);
        }
        
        if(!empty($channel))
        {   
            $entries = $this->getRequest()->getParam('id');
            
            // accept the entries
            if (!empty($entries)){
                $entries = explode(',', $entries);
                $model->approveChannelEntries($channel,$entries);
                
                $this->view->message = $this->_translate->translate('Item/s have been approved and will appear in channel.');
            }
        }
        
        $this->_forward('index');
        return;
    }
    
    /**
     * reject entries for publication in a channel
     */
    public function rejectAction()
    {
        // get the channel model
        $channelId = $this->getRequest()->getParam('channelid');
        $model = Kms_Resource_Models::getChannel();

        // get the channel
        if (isset($model->Category) && $model->Category->id == $channelId){
            $channel = $model->Category;
        }
        else{
            $channel = $model->get($this->getRequest()->getParam('channelname'), false, $channelId);
        }
        
        if(!empty($channel))
        {   
            $entries = $this->getRequest()->getParam('id');

            // reject the entries
            if (!empty($entries)){
                $entries = explode(',', $entries);
                $rejected = $model->rejectChannelEntries($channel,$entries);
                
                if ($rejected){
                    $this->view->message = $this->_translate->translate('Item/s have been rejected and will not appear in channel.');
                }
                else{
                    $this->view->message = $this->_translate->translate('An error occured while rejecting the Item/s from the channel.');
                }
            }
        }
        
        $this->_forward('index');
        return;
    }
}