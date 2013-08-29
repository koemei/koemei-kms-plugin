<?php
/**
 * Koemei module controller.
 * Copyright ©2013 Koemei SA
 * @author Tra!an
 *
 */

class Koemei_IndexController extends Kms_Module_Controller_Abstract

{
	public function init()
    {
		$this->_translate = Zend_Registry::get('Zend_Translate');
	}
	
	public function headerAction() {
		return true;
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
		$CaptionModel = new Captions_Model_Captions();
		$entry = $CaptionModel->getEntry();

		//$start = transcript found, display widget
		//$edit = allow transcript edit
		$start = 0;
		$edit = 0;

		if (is_object($entry)) {
			$assets = $CaptionModel->getCaptionAssets($entry->id, array());

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
		$allow_edit = Kms_Resource_Config::getModuleConfig('koemei', 'koemeiOpenImprove');
		if ($allow_edit==1 && $roleKey!='anonymousRole') {
			$edit=1;
		}

		$this->view->start_koemei = $start;
		$this->view->entry_id = $entry->id;
		$this->view->allow_edit = $edit;
	}
}

?>