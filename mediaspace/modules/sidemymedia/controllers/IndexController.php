<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Sidemymedia_IndexController extends Kms_Module_Controller_Abstract
{
    public function init()
    {
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('sidemymedia', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }
    
    public function indexAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        $myUserId = Kms_Plugin_Access::getId();
        
        $this->view->show = false;
        if( isset($entryModel->entry->userId) && Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId))
        {
            // we are watching an entry that belongs to current user
            $this->view->show = true;
            $model = new Sidemymedia_Model_Sidemymedia();
            $this->view->sidemymedia = $model->getSideMyMedia();
        }
    }
    

    public function tabAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        
        $this->view->show = false;
        
        if( isset($entryModel->entry->userId) && Kms_Plugin_Access::isCurrentUser($entryModel->entry->userId) )
        {
            // we are watching an entry that belongs to current user
            $this->view->show = true;
        }
    }


}



