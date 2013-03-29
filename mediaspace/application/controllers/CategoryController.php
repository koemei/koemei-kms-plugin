<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class CategoryController extends Zend_Controller_Action
{

    public function init()
    {
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
        if(!$contextSwitch->getContext('script'))
        {
            $scriptC = $contextSwitch->addContext('script', array());
            $scriptC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('view', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function indexAction()
    {
        // action body
    }

    public function getAction()
    {
        
       
    }

    public function saveAction()
    {
        // action body
		// getting $_POST
        $categoryId = $this->getRequest()->getParam('id');
		$categoryName = $this->getRequest()->getParam('name');
        //$myObj = Zend_Form::getObjFromForm();
        
        $model = new Application_Model_Category;
        $model->id = $categoryId;
        $model->name = $categoryName;
//        $model->extra_fields = 
        $model->save();
    }

    public function listAction()
    {
        $name = $this->getRequest()->getParam('name');
        $model = new Application_Model_Category;
        $list = $model->getList($name);
        
    }

    


}









