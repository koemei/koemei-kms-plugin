<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Sideplaylists_IndexController extends Kms_Module_Controller_Abstract
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
        $contextSwitch->addActionContext('sideplaylist', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }
    
    public function indexAction()
    {
        $model = new Sideplaylists_Model_Sideplaylists();
        
        $this->view->sideplaylists = $model->getSidePlaylists();
    }
    

    public function tabAction()
    {
        $model = new Sideplaylists_Model_Sideplaylists();
        $this->view->tabs = $model->getSidePlaylists();
    }


}



