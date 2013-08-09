<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Screencapture_IndexController extends Kms_Module_Controller_Abstract
{

    public function init()
    {
        $this->_dispatcher = $this->getRequest()->getParam('dispatcher');
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if (!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }


        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('add-video', 'ajax')->initContext();
        $contextSwitch->addActionContext('add', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function addAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id)
        {
            $id = 1;
        }
        $this->view->uploadBoxId = $id;
        $this->view->form = new Application_Form_EditEntry();
        $this->view->form->setAction($this->view->baseUrl('entry/add-entry-form/id/' . $id));
        $this->view->form->setAttrib('ajax', '1');
        
        $nickname = Kms_Plugin_Access::getId();
        $this->view->form->getElement('nickname')->setValue($nickname);
        
        $this->view->form->enableSubmitButton('Save');
        $this->view->form->getElement('id')->setValue($id);
        $this->view->form->render();
    }

    public function addVideoAction()
    {
        $form = new Application_Form_EditEntry();
        $form->enableSubmitButton('Save');
        $request = $this->getRequest();
        $this->view->uploadBoxId = $request->getParam('id');
        if ($request->isPost())
        {
            if ($form->isValid($request->getPost()))
            {
                $this->view->formValid = true;
                $model = new Screencapture_Model_Screencapture();
            }
            else
            {
                $this->view->formValid = false;
                $this->view->form = $form;
            }
        }
    }

    /* for the mymedia sidebar link */
    public function addLinkAction()
    {
        
    }
    
    /* for the <li> in the header menu */
    public function addLiAction()
    {
        
    }

    public function processAction()
    {
        echo "starting...\n";
        $this->_helper->layout->disableLayout();
        $model = new Screencapture_Model_Screencapture();
        if ($this->getRequest()->getParam('key') == Screencapture_Model_Screencapture::SECRET)
        {
            $workPath = Kms_Resource_Config::getModuleConfig(Screencapture_Model_Screencapture::MODULE_NAME, 'savePath');
            $fp = @fopen($workPath . DIRECTORY_SEPARATOR . 'yt_process_lock', 'w+');
            
            if ($fp && flock($fp, LOCK_EX | LOCK_NB))
            { // do an exclusive lock
                fwrite($fp, "in use by pid ".getmypid()."\n");
                $model->getFilesFromQueue();
                flock($fp, LOCK_UN); // release the lock
            }
            else
            {
                echo "Couldn't get the lock!";
            }

            fclose($fp);
        }
        else
        {
            echo "denied\n";
        }
        exit;
    }

}

