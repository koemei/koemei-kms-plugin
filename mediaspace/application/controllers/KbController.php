<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Action controller for knowledge base
 *
 * @author leon
 */
class KbController extends Zend_Controller_Action
{
    private $_flashMessenger;
    private $_translate = null;
    
    
    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');        
        
        $this->_helper->layout->setLayout('admin');
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');        
        $this->view->messages = $this->_flashMessenger->getMessages();
        
        /* Initialize contexts here */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        if(!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
//        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('interface-info', 'ajax')->initContext();
        $contextSwitch->addActionContext('developer-tools', 'dialog')->initContext();
        
        
        $this->view->pagehead = $this->_translate->translate('Knowledge base');
        $this->view->currentTab = $this->getRequest()->getActionName();
        $this->view->tabs = Kms_Kb_Common::getTabs();
    }

    
    public function indexAction()
    {
        $this->_forward('general');
        return;
    }
    
    public function generalAction()
    {
        $this->view->currentTab = 'general';
    }
    
    public function interfacesAction()
    {
                $this->view->interfaces = Kms_Kb_Common::listInterfaces();
        
    }
    
    public function viewhooksAction()
    {
                $this->view->viewhooks = Kms_Kb_Common::listViewHooks();
        
    }
    
    
    public function viewfilesAction()
    {
                $this->view->viewFiles = Kms_Kb_Common::listViewFiles();
        
    }
    
    
    public function internalAction()
    {
        
    }
    
    
    
    public function interfaceInfoAction()
    {
        $interfaceName = $this->_request->getParam('interface');
        $this->view->target = $interfaceName;
        $this->view->interfaceInfo = Kms_Kb_Common::getInterfaceInfo($interfaceName);
        $this->view->headTitle($this->_translate->translate('KMS KB - Interface').' :: '.$interfaceName);

    }
    
    
    public function developerToolsAction()
    {
        
    }
    
}

