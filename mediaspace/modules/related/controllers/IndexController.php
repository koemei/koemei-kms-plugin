<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Related_IndexController extends Kms_Module_Controller_Abstract
{
    public function init()
    {
        $this->_dispatcher = $this->getRequest()->getParam('dispatcher');
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('related', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }
    
    /*
     * action for checking if we have related entries cached, and if we do, then show them..
     * if not, then issue script that will load the related via AJAX
     */
    public function indexAction()
    {
        // get the module name
        $moduleName = Related_Model_Related::MODULE_NAME;
        
        // get the controller name
        
        $entryModel = Kms_Resource_Models::getEntry();
        
        $model = new Related_Model_Related();
        
        // see if we have the entry in cache
        $entryId = $entryModel->id;
        if($entryId)
        {
            $this->view->entryId = $entryId;
            
            // load the entry from the api only if we have to
            if (!isset($entryModel->entry) || (isset($entryModel->entry) && $entryModel->entry->id != $entryId)){
                $entry = $entryModel->get($entryId);
            }
            if(isset(Kms_Resource_Models::getEntry()->modules['Related']))
            {
                $results = Kms_Resource_Models::getEntry()->modules['Related'];
                if(count($results))
                {
                    $this->view->related = $results;
                }
                $this->render('related');
    //            return;
            }
        }
    }
    
    public function relatedAction()
    {
        $entryId = $this->getRequest()->getParam('id');
        
        $entry = Kms_Resource_Models::getEntry()->get($entryId);
        $model = new Related_Model_Related();
        
        // try to get the Related entries from the entry model
        if(isset(Kms_Resource_Models::getEntry()->modules['Related']))
        {
            $moduleData = Kms_Resource_Models::getEntry()->modules['Related'];
        }
        else
        {
            // if entry model has no related values (for example in case of multiple entries fetch, we do not get the values for each entry)
            // in this case get values for current entry id (from entry model)
            $moduleData = $model->getRelated(Kms_Resource_Models::getEntry());
        }
        
        $this->view->related = $moduleData;
        
    }

    public function tabAction()
    {
    }


}



