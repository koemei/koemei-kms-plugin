<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Channel Members Module Controller
 *
 */
class Channelmembers_IndexController extends Kms_Module_Controller_Abstract
{
    private static $permissions;
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
        if (!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('dialog', 'dialog');
                
        $contextSwitch->addActionContext('index', 'ajax')->initContext();
        $contextSwitch->addActionContext('add', 'ajax')->initContext();
        $contextSwitch->addActionContext('add', 'dialog')->initContext();
        $contextSwitch->addActionContext('edit', 'ajax')->initContext();
        $contextSwitch->addActionContext('delete', 'ajax')->initContext();
        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('usersuggestions', 'ajax')->initContext();
        
        $this->_helper->contextSwitch()->initContext(); 
        
        $this->view->tabName = 'members';
    }
    
    /**
     * add a channel members tab link
     */
    public function tabAction()
    {
        // get the channel
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->Category;

        if(!empty($channel))
        {
            $this->view->channel = $channel;
        }
        $this->view->tabActive = $this->getRequest()->getParam('tab') == $this->view->tabName;
        
    }

    /**
     * the channel members tab content
     */
    public function indexAction()
    {                
        // get the channel
        $model = new Channelmembers_Model_Channelmembers();
        $channel = $model->getChannel($this->getRequest()->getParam('channelname'), $this->getRequest()->getParam('channelid'));

        if(!empty($channel))
        {        
            $params = $this->getParams();         
                                    
            if ($this->getRequest()->getParam('tab') == $this->view->tabName){
                $params['tab'] = $this->view->tabName;
            
                // these params will be used by the pagers and remove links
                $this->view->urlParams = array(
                        'module' => $this->getRequest()->getModuleName() ,
                        'controller' => $this->getRequest()->getControllerName() ,
                        'action' => $this->getRequest()->getActionName() ,
                        'channelname' => $channel->name,
                        'channelid' => $channel->id,
                        'tab' => $this->view->tabName,
                );

                $this->view->members = $model->getChannelUsers($channel,Application_Model_Channel::CHANNEL_USER_TYPE_ALL, $params);                

                $totalMembers = 0;
                if (!empty($this->view->members)){
                    $totalMembers = $model->getTotalCount();
                }
                else if ($params['page'] > 1){
                    $params['page']--;
                    $this->view->members = $model->getChannelUsers($channel,Application_Model_Channel::CHANNEL_USER_TYPE_ALL, $params);
                    $totalMembers = $model->getTotalCount();
                }
                
                $this->view->count = $totalMembers;
                $this->view->channel = $channel;
                $this->view->channelName = $channel->name;
                $this->view->channelId = $channel->id;
                $this->view->owner = $channel->owner;
                                
                $this->view->paginator = $this->getPaginator($params, $totalMembers);
                $this->view->pagerType = Kms_Resource_Config::getConfiguration('channels', 'pagerType');
            }
            else
            {
                $this->_helper->viewRenderer->setNoRender(TRUE);
            }
        }
    }
    
    /**
     * add a new channel member
     */
    public function addAction()
    {
        $request = $this->getRequest();
        
        $this->view->defaultText = $this->_translate->translate('Enter user id or name');
        
        // prepare the form
        $this->view->form = new Channelmembers_Form_AddChannelMember();
        $this->view->form->setAction($this->view->baseUrl($request->getModuleName() .'/' 
            . $request->getControllerName() .'/' . $request->getActionName() 
            . '/channelname/' . urlencode(Kms_View_Helper_String::removeSpecialChars($request->getParam('channelname'))) 
            . '/channelid/' . $request->getParam('channelid')));
        $this->view->form->setAttrib('ajax', true);
        
        $userId = $this->view->form->getElement('userId');
        $userId->setJQueryParam('source' , $this->view->baseUrl('channelmembers/index/usersuggestions'));
        
        $permission = $this->view->form->getElement('permission');
        $permission->setMultiOptions(Channelmembers_Model_Channelmembers::getChannelPermissions());
           
        // default values
        if ($this->getRequest()->isPost())
        {
            // get the post data
            $data = $this->getRequest()->getPost();
            
            // remove the default text from the value
            if (empty($data[Channelmembers_Form_AddChannelMember::FORM_NAME]['userId']) || 
                        $data[Channelmembers_Form_AddChannelMember::FORM_NAME]['userId'] == $this->view->defaultText){
                $data[Channelmembers_Form_AddChannelMember::FORM_NAME]['userId'] = '';
            }
            $member = $data[Channelmembers_Form_AddChannelMember::FORM_NAME];
        
            // populate the form with the member data
            $this->view->form->populate($member);
            
            // validate the form
            if ($this->view->form->isValid($data))
            {          
                // check that we are not adding existing user
                $model = new Channelmembers_Model_Channelmembers();                
                $channel = $model->getChannel($request->getParam('channelname'), $request->getParam('channelid'));
                $currentMember = $model->getChannelMember($channel->id,$member['userId']);
                
                if (!empty($currentMember))
                {   
                   Kms_Log::log('channelmembers: user is already a member of channel', Kms_Log::WARN);
                    
                    // we are trying to edit an existing member - show error
                   $this->view->form->addError($this->_translate->translate('This user is already a member of this channel'));                    
                }
                else {
                    // form is valid - save the member                 
                    try {    
                        $member['categoryId'] = $channel->id;
                        $member['channelId'] = $channel->name;
                        
                        $member = $model->saveChannelMember($member);
                        
                        $this->view->message = $member->userId . ' ' . $this->_translate->translate('was added to your channel.');
                        
                        // reload the current members
                        $this->getRequest()->setParam('tab', $this->view->tabName);
                        $this->_forward('index');
                        return;
                    }
                    catch (Kaltura_Client_Exception $e){
                        // issue an error message
                        if ($e->getCode() == 'INVALID_USER_ID'){
                            $this->view->form->addError($this->_translate->translate('User')  . ' ' . $this->view->escape($member['userId']) . ' ' . $this->_translate->translate('does not exist.'));
                        }
                        else{
                            $this->view->message = $this->_translate->translate('There was an error creating the channel member.');
                        }    
                    }
                }
            }
            else
            {
                // invalid data
                if(!$member['userId'])
                {
                    $member['userId'] = $this->view->defaultText;
                }
                $this->view->form->populate($member);
            }
        }
    }
    
    /**
     * edit an existing channel member object
     */
    public function editAction()
    {        
        $this->view->userId = $this->getRequest()->getParam('uid');
        $this->view->channelName = $this->getRequest()->getParam('channelname');
        $this->view->channelId = $this->getRequest()->getParam('channelid');
        
        // get the channel member data
        $member = array( 'permission' => $this->getRequest()->getParam('perm'),
                         'userId' => $this->getRequest()->getParam('uid'));
        
        // prepare the form
        $request = $this->getRequest();
        $this->view->form = new Channelmembers_Form_EditChannelMember();
        $this->view->form->setAction($this->view->baseUrl($request->getModuleName() .'/' . $request->getControllerName() .'/'. $request->getActionName() . '/channelname/' . urlencode($this->view->channelName). '/channelid/' . urlencode($this->view->channelId)));
        $this->view->form->setAttrib('ajax', true);
        $permission = $this->view->form->getElement('permission');
        $permission->setMultiOptions(Channelmembers_Model_Channelmembers::getChannelPermissions());
                
        // form values
        if ($this->getRequest()->isPost())
        {
            // form submit - get the post data
            $data = $this->getRequest()->getPost();            
            $member = $data[Channelmembers_Form_EditChannelMember::FORM_NAME];
        
            // validate the form
            if ($this->view->form->isValid($data))
            {
               // form is valid - save the member
                try {
                    $model = new Channelmembers_Model_Channelmembers();
                    
                    $channel = $model->getChannel($this->getRequest()->getParam('channelname'));
                    
                    $member['categoryId'] = $channel->id;
                    $member['channelId'] = $channel->name;
                    
                    $savedMember = $model->saveChannelMember($member);
                    
                    $savedMember->permission = $model->getChannelPermission($savedMember->permissionLevel);
                    $savedMember->channelId = $channel->name;
                                        
                    $this->view->message = $this->_translate->translate('Permission changed. Updating permissions might take a few minutes.');
                    $this->view->member = $savedMember;
                    $this->view->userId = $savedMember->userId;
                }
                catch (Kaltura_Client_Exception $e){
                    // issue an error message      
                    $this->view->message = $this->_translate->translate('There was an error updating the channel member permission.');
                    
                    $this->getRequest()->setParam('tab', $this->view->tabName);
                    $this->_forward('index');
                    return;                
                }
            }
        }        
        
        // populate the form with the member data
        $this->view->form->populate($member);
    }
    
    
    /**
     * delete a channel member
     */
    public function deleteAction()
    {   
        $results = false;
        $this->view->userId = $this->getRequest()->getParam('uid');
        $this->view->channelName = $this->getRequest()->getParam('channelname');
        $this->view->categoryId = $this->getRequest()->getParam('categoryid');
        
        // if not ajax - show the dialog
        if ($this->getRequest()->getParam('format') == 'ajax'){
            
            // test that we are not deleting ourselves
            if ($this->view->userId == Kms_Plugin_Access::getId()){
                // error - trying to delete oneself
                Kms_Log::log('channelmembers: user ' . $this->view->userId . ' is trying to delete itself from channel '. $this->view->channelName, Kms_Log::WARN);
            }
            else{        
                // delete the channel member
                $model = new Channelmembers_Model_Channelmembers();
                $results = $model->delChannelMember($this->view->categoryId ,$this->view->userId);
                
                // set the message
                $this->view->message = $this->view->userId . ' ' .$this->_translate->translate('is no longer a channel member.');
                
                if ($results)
                {
                    // get the members again
                    $channel = $model->getChannel($this->view->channelName);                    
                    $params = $this->getParams();
                    $members = $model->getChannelUsers($channel,Application_Model_Channel::CHANNEL_USER_TYPE_ALL, $params);
                    $totalMembers = $model->getTotalCount();
                    
                    // just deleted the last member in this page - reload page
                    if (empty($members)){
                        $this->getRequest()->setParam('tab', $this->view->tabName);
                        $this->_forward('index');
                        return;
                    }
                    
                    $this->view->count = $totalMembers;
                    $this->view->owner = $channel->owner;
                    
                    // reload next member - if exists
                    if (count($members) == $params['pagesize']){
                        $members = array_values($members);
                        $member = $members[$params['pagesize'] -1];
                        $members = array($member->userId => $member);
         
                        $this->view->members = $members;
                        $this->view->member = $member;
                    }
                    
                    // url params - for the pager
                    $this->view->urlParams = array(
                        'module' => $this->getRequest()->getModuleName() ,
                        'controller' => $this->getRequest()->getControllerName() ,
                        'action' => 'index' ,
                    );

                    $this->view->paginator = $this->getPaginator($params, $totalMembers);
                    $this->view->pagerType = Kms_Resource_Config::getConfiguration('channels', 'pagerType');
                }
            }
        }
        
        $this->view->results = $results;
    }
    
    /**
     * channel user autocomplete suggestions action
     */
    public function usersuggestionsAction()
    {
        $model = new Channelmembers_Model_Channelmembers();
        
        $users = $model->getUserSuggestions($this->getRequest()->getParam('term'));
        
        if (!empty($users)){
            $results = array();
            foreach ($users as $user){
                $suggestions[$user->id]['label'] = (!empty($user->fullName))? $user->id . ' - ' . $user->fullName : $user->id;
                $suggestions[$user->id]['value'] = $suggestions[$user->id]['label'];
                $suggestions[$user->id]['id'] = $user->id;
            }
        }
        $suggestions = array_values($suggestions);
        
        $this->view->suggestions = $suggestions;
        $this->_helper->layout->disableLayout();
    }
    
    /**
     * create the paginator
     * @param array $params
     * @param int $totalMembers
     * @return Zend_Paginator $paginator
     */
    private function getPaginator(array $params, $totalMembers)
    {
        // init paging
        $pagingAdapter = new Zend_Paginator_Adapter_Null( $totalMembers );
        $paginator = new Zend_Paginator( $pagingAdapter );
        // set the page number
        $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
        // set the number of items per page
        $paginator->setItemCountPerPage( $params['pagesize']);
        // set the number of pages to show
        $paginator->setPageRange(Kms_Resource_Config::getConfiguration('channels', 'pageCount'));
        return $paginator;
    }
    
    /**
     * get the request params with defaults set
     */
    private function getParams()
    {
        $params = array('page' => $this->getRequest()->getParam('page'));
        
        if (empty($params['page'])){
            $params['page'] = 1;
        }
        $params['pagesize'] = Application_Model_Entry::MY_MEDIA_PAGE_SIZE;
        
        return $params;
    }
}