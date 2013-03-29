<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class RedirectorController extends Zend_Controller_Action
{
    private $_redirector;
    
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');  
        $this->_redirector->setCode(301);
    }

    public function indexAction()
    {
        // action body
    }
    /*
    public function showAction()
    {
        if($this->_request->getParam('id'))
        {
            $this->_redirector->gotoRoute(
                array('id' => $this->_request->getParam('id')),
                'entryplay'
            );
        }
        elseif($this->_request->getParam('cat'))
        {
            $this->_redirector->gotoRoute(
                array('catid' => $this->_request->getParam('cat')),
                'categoryid'
            );
        }
    }*/
}

