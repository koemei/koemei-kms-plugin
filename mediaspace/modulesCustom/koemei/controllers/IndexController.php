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
		$this->view->assets = $assets;
	}
	
	

	public function indexAction() {
		
		 echo "123"; exit;
	}
	 public function index() {
		 echo "123"; exit;
	 }
}

?>