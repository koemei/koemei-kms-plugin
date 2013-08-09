<?php
/**
 * Koemei module controller.
 * 
 * @author Tra!an
 *
 */

class Koemei_IndexController extends Kms_Module_Controller_Abstract

{
	public function init()
    {
		$this->_translate = Zend_Registry::get('Zend_Translate');
		$this->view->koemeiEnabled = true;
		
	}
	
	public function entrytabAction()
    {
        // get the entry
        $CaptionModel = new Captions_Model_Captions();
        $entry = $CaptionModel->getEntry();
        // check if it has captions
        if (!$CaptionModel->hasCaptions($entry->id))
        {
            // no captions - do not present the tab
            $this->_helper->viewRenderer->setNoRender(TRUE);
        } 
    }
	public function headerAction() {
		return true;
	}
	public function entryAction() {
		$CaptionModel = new Captions_Model_Captions();
		$entry = $CaptionModel->getEntry();
		$assets = $CaptionModel->getCaptionAssets($entry->id, array());
		
		//$start = transcript found, display widget
		//$edit = alow transcript edit
		$start = 0;
		$edit = 0;
		
		if (is_object($entry)) {
		//check if there is a valid transcript
			if (count($assets->objects)>0){
					$start=1;
				}
		}
		
		//admin? then allow edit
		$identity = Zend_Auth::getInstance()->getIdentity();
		$roleKey = Kms_Plugin_Access::getRoleKey($identity->getRole());
		if ($roleKey=="adminRole" || $roleKey=="unmoderatedAdminRole") {
			$edit = 1;	
		}
		
		//do the owner accepts public editing? alow edit but not for anonymus users
		$alow_edit = Kms_Resource_Config::getModuleConfig('koemei', 'OpenImprove');
		if ($alow_edit==1 && $roleKey!='anonymousRole') {
			$edit=1;	
		}
		
		$this->view->start_koemei = $start;
		$this->view->entry_id = $entry->id;
		$this->view->alow_edit = $edit;
	}
	public function editAction() {
		$CaptionModel = new Captions_Model_Captions();
		$entry = $CaptionModel->getEntry();
		$start = 0;
		
		if (is_object($entry)) {
		$assets = $CaptionModel->getCaptionAssets($entry->id, array());
		//$start = transcript found, display widget
		
			//check if there is a valid transcript
			if (count($assets->objects)>0){
				$start=1;
			}
		}
		$this->view->start_koemei = $start;
		$this->view->entry_id = $entry->id;
	}
	

	public function indexAction() {
		 
	}
	
}

?>