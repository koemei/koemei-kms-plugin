<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Facebook module tag controller.
 * 
 * @author atars
 *
 */
class Facebook_TagController extends Kms_Module_Controller_Abstract
{
    protected $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
    }
    

    /**
     * add facebook meta tags to the page header
     */
    public function headerAction()
    {
    	$this->view->renderTags = false;	// main "SHIBER"
    	$this->view->renderVideoTags = false;
    	$this->view->renderTextTags = false;
    	$dispatcher = $this->getRequest()->getParam('dispatcher');
    	if ($dispatcher['module'] == 'default' && $dispatcher['controller'] == 'entry' && $dispatcher['action'] == 'play') {
    		// this is entry page, do your thing
    		
    		$identity = Zend_Auth::getInstance()->getIdentity();
			$roleKey = Kms_Plugin_Access::getRoleKey($identity->getRole());
    		if ($roleKey != Kms_Plugin_Access::ANON_ROLE) {
    			// user is logged in, quit.
    			return;
    		}
    		
    		
    		if (Kms_Resource_Config::getConfiguration('auth', 'allowAnonymous') != '1') {
    			// instance doesn't allow guests, quit
    			return;
    		}
    		
    		$id = $this->getRequest()->getParam('id'); // entry id
    		$entryModel = Kms_Resource_Models::getEntry();
    		
    		// see if the entry is assigned to any open categories (entry.get will fail if the user is not entitled to the entry)
    		if (empty($entryModel->entry)) {
    			// no entry (entitlement failed)
    			return;
    		}
    		elseif ($entryModel->entry->id != $id) {
    			// not supposed to happen
    			return;
    		}
    		
    		$this->view->renderTags = true;
    		$this->view->uiConfId = Kms_Resource_Config::getModuleConfig('facebook', 'fPlayerId');
    		$this->view->site_name = Kms_Resource_Config::getConfiguration('application', 'title');
    		$this->view->service_url = preg_replace('#http[s]?://#', '', Kms_Resource_Config::getConfiguration('client', 'serviceUrl'));
    		
    		$this->view->uiconf = $entryModel->getUiconfById($this->view->uiConfId);
    		
    		$entry = $entryModel->getCurrent($id); // current entryentry
    		$this->view->entry = $entry;
    		switch ($entry->mediaType) {
    			case Kaltura_Client_Enum_MediaType::VIDEO:
    				if ($entry->type == Kaltura_Client_Enum_EntryType::MEDIA_CLIP) {
    					// "simple" video entries
    					$this->view->renderVideoTags = true;
    					$partner_id = Kms_Resource_Config::getConfiguration('client', 'partnerId');
    					$flavor_asset_id = $entryModel->getMobileFlavorId($id);
    					if (!empty($flavor_asset_id)) {
			    			$this->view->flavor_url ='/p/'. $partner_id .'/sp/' . $partner_id . '00/playManifest/entryId/' . $id . '/flavorId/' . $flavor_asset_id . '/format/url/protocol/http/a.mp4';
    					}
    				}
    				else {
    					// anything else: external etc
    					$this->view->renderTextTags = true;
    				}
    				break;		
    			case Kaltura_Client_Enum_MediaType::AUDIO:
    			case Kaltura_Client_Enum_MediaType::IMAGE:
    				$this->view->renderTextTags = true;
    				break;
    			default:
    				// documents, data.. (?)
    				$this->view->renderTextTags = true;
    				break;		
    		}
    	}
    }
    

}