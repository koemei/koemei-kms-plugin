<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
* To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Channels section Controller.
 * @author talbone
 *
 */
class ChannelsController extends Zend_Controller_Action
{   
    private $_translate = null;
    
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        /* initialize translator */
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
        $contextSwitch->addActionContext('mychannels', 'ajax')->initContext();
        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('delete', 'ajax')->initContext();
        
        $this->_helper->contextSwitch()->initContext();
    }
    
    /**
     * list all channels
     */
    public function indexAction()
    {
        $model = Kms_Resource_Models::getChannel();
        $this->view->galleryType = 'channels';
        $params = array(
				'type' => $this->getRequest()->getParam('type'),
                'page' => $this->getRequest()->getParam('page'),
                'sort' => $this->getRequest()->getParam('sort'),
                'keyword' => $this->getRequest()->getParam('keyword'),
        );
        
        if (!$params['sort']){
            $params['sort'] = 'date';
        }
        
        $this->view->params = $params;
        $this->view->channels = $model->getChannelThumbnails($model->getChannelList($params));
        
        $this->view->ChannelNumber = $model->getTotalCount();
        $this->view->admin = false;
        
        // init paging
        $this->view->paginator = $this->getPaginator($params, $model);
        $this->view->pagerType = Kms_Resource_Config::getConfiguration('channels', 'pagerType');
    }

    /**
     * show a channel.
     * This action basically just adds contextual role check before the gallery action.
     */
    public function viewAction()
    {        
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->get($this->getRequest()->getParam('channelname'), false, $this->getRequest()->getParam('channelid'));
        
        if (!empty($channel))
        {
            if ($channel->membership == Application_Model_Category::MEMBERSHIP_PRIVATE){
                $role = $model->getUserRoleInChannel($channel->name, Kms_Plugin_Access::getId(), $channel->id);
                
                if ($role == Application_Model_Category::CATEGORY_USER_NO_ROLE){
                    // the user does not have access to this private channel
                    Kms_Log::log('accessPlugin: Access denied to channel:view because of contextual access');                    
                    throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
                }
            }
            
            $this->_forward('view','gallery');
        }
        else {
            throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
        }
   }

    /**
     * edit an existing channel
     */
    public function editAction()
    {
        $model = Kms_Resource_Models::getChannel();

        // get the channel
        try{
            $currentChannel = $model->get($this->getRequest()->getParam('channelname'), false, $this->getRequest()->getParam('channelid'));
            if (empty($currentChannel)){
                throw new Zend_Controller_Action_Exception('', 404);
            }
            
            $channel = (array) $currentChannel;
        }
        catch (Kaltura_Client_Exception $e){
            // forward to the 404 page
            throw new Zend_Controller_Action_Exception('', 404);
        }
        $this->view->tabName = 'basic';
        $tab = $this->getRequest()->getParam('tab');
        $this->view->tabActive = (!$tab || $tab == $this->view->tabName);
        
        // get the form
        $this->view->form = new Application_Form_EditChannel();
        $this->view->form->setAttrib('id','edit_channel');
        // form values
        if ($this->getRequest()->isPost())
        {
            // form submit - get the post data
            $data = $this->getRequest()->getPost();
            $channel = $data[Application_Form_EditCategory::FORM_NAME];
            
            // validate the form
            if ($this->view->form->isValid($data))
            {
                // form is valid - forward to the save action
                $this->_forward('save');
                return;
            } 
        }

        // populate the form with the channel data
        $this->view->form->populate($channel);
        
        // set the name for the channel delete link
        $this->view->channel = $currentChannel;
        
        // check contextual role
        $role = $model->getUserRoleInChannel($currentChannel->name, Kms_Plugin_Access::getId(), $currentChannel->id);
        if ($role != Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER)
        {
            unset($this->view->form);
        }
        
        // set the menu item as active if exists
        $this->setActiveMenu();
    }
    
    /**
     * creates a new channel
     */
    public function createAction()
    {
        $model = Kms_Resource_Models::getChannel();
        
        // get the form
        if(!isset($this->view->form))
        {
            $this->view->form = new Application_Form_EditChannel();
            $this->view->form->setAttrib('id','edit_channel');
        }
        
        // default values
        if ($this->getRequest()->isPost())
        {
            // get the post data
            $data = $this->getRequest()->getPost();
            $channel = $data[Application_Form_EditCategory::FORM_NAME];
            // populate the form with the channel data
            $this->view->form->populate($channel);
            
            // repopulate from sanitized form values
            $data = $this->view->form->getValues();
            $channel = $data[Application_Form_EditCategory::FORM_NAME];
            // validate the form
            if ($this->view->form->isValid($data))
            {
                // form is valid - forward to the save action
                $channelName = $channel['name'];
                $exists = Kms_Resource_Models::getChannel()->getByName($channelName);
                if($exists)
                {
                    $this->view->form->getElement('name')->addError($this->_translate->translate('Channel'). ' '.$channelName. ' '.$this->_translate->translate('already exists').'.');
                }
                else
                {
                    $this->_forward('save');
                    return;
                }
            }
        }
        
        // set the menu item as active if exists
        $this->setActiveMenu();
    }

    /**
     * saves a channel
     */
    public function saveAction()
    {
        $model = Kms_Resource_Models::getChannel();
    
        // save the data
        //$this->view->form->populate($data[Application_Form_EditCategory::FORM_NAME]);
        $data = $this->view->form->getValues();
        
        $channel = $model->saveChannel($data[Application_Form_EditCategory::FORM_NAME]);
    
        // redirect to the view page
        $this->_redirect($this->view->baseUrl('/channel/' . urlencode(Kms_View_Helper_String::removeSpecialChars($channel->name)) . '/' . $channel->id),
        	array('prependBase' => false));
    }
    
    /**
     * deletes a channel
     */
    public function deleteAction()
    {
        $this->view->channelName = $this->getRequest()->getParam('channelname');
        $this->view->channelId = $this->getRequest()->getParam('channelid');
        $this->view->categoryId = $this->getRequest()->getParam('categoryid');
        
        // if confirmed - delete
        if ($this->getRequest()->getParam('confirm') == true){
            $model = Kms_Resource_Models::getChannel();
            
            // delete the channel
            $model->delete($this->getRequest()->getParam('channelname'), $this->getRequest()->getParam('channelid'));
        }
    }

    /**
     * shows a user's channels
     */
    public function mychannelsAction()
    {
        $model = Kms_Resource_Models::getChannel();
        $this->view->galleryType = 'channels';
	
        $params = array(
                'page' => $this->getRequest()->getParam('page'),
                'sort' => $this->getRequest()->getParam('sort'),
                'keyword' => $this->getRequest()->getParam('keyword'),
                'type' => $this->getRequest()->getParam('type')
        );
        
        if (!$params['sort']){
            $params['sort'] = 'date';
        }
        
        $myChannelCount = $model->getMyChannelsCount();
        $this->view->memberCount = $myChannelCount['member'];
        $this->view->managerCount = $myChannelCount['manager'];
        if(is_null($params['type'])){
            $params['type'] = 'manager';
            if ($this->view->managerCount == 0){
                $params['type'] = 'member';
            }
        }
        
        
        $this->view->params = $params;
        $this->view->channels = $model->getChannelThumbnails($model->getMyChannelsList($params));
        
        if ($params['type'] == 'manager'){
            $this->view->admin = true;
        }
        
        // init paging
        $this->view->paginator = $this->getPaginator($params, $model);
        $this->view->pagerType = Kms_Resource_Config::getConfiguration('channels', 'pagerType');
        // ajax calls use the same view as index
        if ($this->getRequest()->getParam('format', false) && $this->getRequest()->getParam('format') == 'ajax')
        {
            $this->renderScript('channels/index.ajax.phtml');
        }
    }
    
    /**
     * access denied by contextual role - does not require login
     */
    public function deniedAction()
    {
        
    }
    
    /**
     * create the paginator
     * @param array $params
     * @param Application_Model_Channel $model
     * @return Zend_Paginator $paginator
     */
    private function getPaginator(array $params, Application_Model_Channel $model)
    {        
        // init paging
        $pagingAdapter = new Zend_Paginator_Adapter_Null( $model->getTotalCount() );
        $paginator = new Zend_Paginator( $pagingAdapter );
        // set the page number
        $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
        // set the number of items per page
        $paginator->setItemCountPerPage(Kms_Resource_Config::getConfiguration('channels', 'pageSize'));
        // set the number of pages to show
        $paginator->setPageRange(Kms_Resource_Config::getConfiguration('channels', 'pageCount'));
        
        return $paginator;
    }
    
    /**
     * set the channels menu item as active if exists
     */
    private function setActiveMenu()
    {
        // create a breadcrumb container for the menu item
        $breadcrumbsContainer = Kms_Resource_Nav::getContainer()->findOneBy('route', 'channels');
        if(empty($breadcrumbsContainer)){
            $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
        }
        $breadcrumbsContainer->addPage(new Zend_Navigation_Page_Mvc( array(
                'label' => 'channels',
                'controller' => 'channels',
                'action' => 'index',
                'route' => 'channels',
                'active' => true,
                'showInMenu' => false,
                'params' => array(
                        'disableSideNav' => true,),) )
        );
    }
}