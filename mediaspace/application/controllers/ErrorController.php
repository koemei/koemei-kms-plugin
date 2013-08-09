<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class ErrorController extends Zend_Controller_Action
{
    private $_translate = null;
    
    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');        
        /* Initialize contexts here */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        
        $ajaxC = $contextSwitch->setContext('ajax', array());
        $ajaxC->setAutoDisableLayout(false);

        $dialogC = $contextSwitch->setContext('dialog', array());
        $dialogC->setAutoDisableLayout(false);

        $scriptC = $contextSwitch->setContext('script', array());
        $scriptC->setAutoDisableLayout(false);
        

        $contextSwitch->addActionContext('error', 'ajax')->initContext();
        $contextSwitch->addActionContext('error', 'script')->initContext();
        $contextSwitch->addActionContext('error', 'dialog')->initContext();
        $this->_helper->contextSwitch()->initContext();
        
        
        // cancel the page cache
        if(!is_null(Kms_Resource_Cache::$pageCache))
        {
            Kms_Resource_Cache::$pageCache->cancel();
        }
        
    }
    
    public function errorAction()
    {
        $this->view->errorHappened = true;
        $errors = $this->_getParam('error_handler');
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = $this->_translate->translate("We are sorry, but something went wrong").'.'.$this->_translate->translate("Please try again or contact your support team for assistance").'.';
            return;
        }
	
        if(is_a($errors->exception, 'Kaltura_Client_Exception') || is_a($errors->exception, 'Kaltura_Client_ClientException') /*|| $errors->exception->getMessage() == 'String could not be parsed as XML'*/)
        {
            // API error
            $errors->type = 'API';
        }
        
        if($errors->exception && $errors->exception->getCode() == Kms_Plugin_Access::EXCEPTION_CODE_ACCESS)
        {
            $errors->type = Kms_Plugin_Access::EXCEPTION_CODE_ACCESS;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
		
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
		// 404 error -- controller not found
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
        	$this->_helper->layout()->getView()->headTitle($this->_translate->translate('Page not found'));
		$this->view->pagehead = $this->_translate->translate('Page not found');
                $this->view->message = $this->_translate->translate("We're sorry, the page you requested cannot be found. Please check the address line or follow the navigation menu.");
                $this->view->extendedMessage = isset($errors->exception) && $errors->exception->getMessage() ? $errors->exception->getMessage() : '';
                $this->render('404');
                break;
            case Kms_Plugin_Access::EXCEPTION_CODE_ACCESS:
                $this->getResponse()->setHttpResponseCode(200);
                $priority = Zend_Log::NOTICE;
        	$this->_helper->layout()->getView()->headTitle($this->_translate->translate('Access Denied'));
		$this->view->pagehead = $this->_translate->translate('Access Denied');
                //$this->view->message = $this->_translate->translate("We're sorry, the page you requested cannot be found. Please check the address line or follow the navigation menu.");
                $this->view->message = isset($errors->exception) && $errors->exception->getMessage() ? $errors->exception->getMessage() : '';
                $this->render('404');
                break;
            case 'API':
        	$this->_helper->layout()->getView()->headTitle($this->_translate->translate('API Error'));
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->pagehead = $this->_translate->translate('API error');
                $this->view->message = isset($errors->exception) && $errors->exception->getMessage() ? $errors->exception->getMessage() : '';
                
                break;
            default:
        	$this->_helper->layout()->getView()->headTitle($this->_translate->translate('Application Error'));
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->pagehead = $this->_translate->translate('Application error');
                break;
        }
        $this->getFrontController()->getRouter();
        // Log exception, if logger available
        Kms_Log::log('error: '.isset($errors->exception) && $errors->exception->getMessage() ? $errors->exception->getMessage() : 'Exception with no message', $priority, $errors->exception);
        Kms_Log::log('Request Parameters: '. Kms_Log::printData($errors->request->getParams()), $priority);
        //Kms_Log::log('Debug info: '. Kms_Log::printData($errors), Kms_Log::DEBUG);
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        $this->view->request   = $errors->request;
    }


    
}

