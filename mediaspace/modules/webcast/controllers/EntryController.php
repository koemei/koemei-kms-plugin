<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Webcast module entry controller.
 * 
 * @author talbone
 *
 */
class Webcast_EntryController extends Kms_Module_Controller_Abstract
{
    protected $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
    }
    

    /**
     *  add css and js to the page header
     */
    public function headerAction()
    {
        // mock action to create view
    }

    /**
     *  show the webcast entry page
     */
    public function playAction()
    {
        $buttonText = array(Webcast_Model_Webcast::BUTTON_TYPE_REGISTER => $this->view->translate('Register Now'),
                            Webcast_Model_Webcast::BUTTON_TYPE_JOIN => $this->view->translate('Join'),
                            Webcast_Model_Webcast::BUTTON_TYPE_WATCH => $this->view->translate('Watch'),);

        $id = $this->getRequest()->getParam('id');
        $entryModel = Kms_Resource_Models::getEntry();

        $entry = $entryModel->getCurrent($id);

        $this->view->categories = $entryModel->getEntryCategories($id);
        $this->view->isLiked = $entryModel->isLiked($id);
        
        $entryUser = $entry->userId;
        $currentUser = null;
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity)
        {
            $currentUser = $identity->getId();
        }
        $this->view->entryUser = $entryUser;
        $this->view->entry = $entry;
        $this->view->isEntryOwner = $entryUser == $currentUser;
        $this->view->showLike = Kms_Resource_Config::getConfiguration('application', 'enableLike');

        // get the entry metadata
        $model = new Webcast_Model_Webcast();
        $customdata = $model->getEntryCustomdata($id);

        $this->view->buttonText = $buttonText[$model->getButtonType($customdata)];
        $this->view->buttonGuestUrl = $model->getGuestUrl($customdata);
        $this->view->broadcastTime = $model->getCustomDataBroadcastTime($customdata);
        $this->view->broadcastDuration = $model->getCustomDataBroadcastDuration($customdata);
        $this->view->eventOpenTime = $model->getCustomDataOpenTime($customdata);
        $this->view->eventOpenTimeDelta = $this->view->broadcastTime - $this->view->eventOpenTime;
        $this->view->speakers = $model->getCustomDataSpeakers($id, $customdata);
    }
    
	public function bulkButtonAction()
    {
        $this->view->urlParams = $this->getRequest()->getParams();
        
        $this->view->urlParams['module'] = 'webcast';
        $this->view->urlParams['controller'] = 'entry';
		$this->view->urlParams['action'] = 'refresh-entries';
		
		// remove page from url
		if (isset($this->view->urlParams['page'])) {
			unset($this->view->urlParams['page']);
		}
		
		// remove dispatcher from url
		if (isset($this->view->urlParams['dispatcher'])) {
			unset($this->view->urlParams['dispatcher']);
		}
		
		// remove defaultKeyword from url
		if (isset($this->view->urlParams['defaultKeyword'])) {
			unset($this->view->urlParams['defaultKeyword']);
		}
    }
    
    
	public function refreshEntriesAction()
    {
		$entryModel = Kms_Resource_Models::getEntry();
        $entryModel->clearMyMediaCache();
        
        $params = $this->getRequest()->getParams();
        // remove action variables
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);

        // build url from any filters available
        $url = '/my-media';
        foreach ($params as $key=>$value) {
        	$url .= '/' . $key . '/' . $value;
        }
        $this->_redirect($url);
    }
    
    
	public function refreshAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$entryModel = Kms_Resource_Models::getEntry();
    	$entry = $entryModel->getCurrent($id);
    	$cacheTags = array('entry_' . $id);
        Kms_Resource_Cache::apiClean('entry', array('id' => $id), $cacheTags);
    	$this->_redirect($this->view->EntryLink($id, $entry->name, true));
    }
}