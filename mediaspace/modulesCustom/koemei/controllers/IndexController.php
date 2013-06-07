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
		
		
		$start = 0;
		if (count($assets)>0){
            foreach ($assets as $key=>$asset) {
                if (count($asset)>0){
                    if ($asset[0]->partnerId==Kms_Resource_Config::getConfiguration('client', 'partnerId')) {
                        $start=1;
                    }
                }
            }
        }
		$this->view->start_koemei = $start;
		$this->view->entry_id = $entry->id;
	}
	public function editAction() {
		$CaptionModel = new Captions_Model_Captions();
		$entry = $CaptionModel->getEntry();
		$assets = $CaptionModel->getCaptionAssets($entry->id, array());
		$start = 0;
		foreach ($assets as $key=>$asset) { 
			if ($asset[0]->partnerId=="1366641") {
				$start=1;	
			}
		}
		$this->view->start_koemei = $start;
		$this->view->entry_id = $entry->id;
	}
	

	public function indexAction() {
		
		 echo "123"; exit;
	}
	
}

?>