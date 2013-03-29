<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Emailnotifications_IndexController extends Kms_Module_Controller_Abstract
{
	private $_flashMessenger;
	private $_translate = null;

	public function init()
	{
		/* init translator */
		$this->_translate = Zend_Registry::get('Zend_Translate');

		/* initialize flashMessenger */
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
		$this->_flashMessenger->setNamespace('default');

		/* initialize context switching */
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		if (!$contextSwitch->getContext('ajax'))
		{
			$ajaxC = $contextSwitch->addContext('ajax', array());
			$ajaxC->setAutoDisableLayout(false);
		}
		$contextSwitch->setSuffix('ajax', 'ajax');
		$contextSwitch->addActionContext('enable', 'ajax')->initContext();
		$contextSwitch->addActionContext('disable', 'ajax')->initContext();
		$contextSwitch->addActionContext('update', 'ajax')->initContext();
		$this->_helper->contextSwitch()->initContext();
	}

	public function indexAction()
	{
		$templates = Emailnotifications_Model_Emailnotifications::getEventNotificationTemplates();
		$enabledTemplates = Emailnotifications_Model_Emailnotifications::getEnabledEventNotificationTemplates();
		$supportedNotifications = Emailnotifications_Model_Emailnotifications::getSupportedNotifications();
		$notifications = array();
		foreach($supportedNotifications as $supportedNotificationSystemName)
		{
			$enabledTemplate = $this->getNotificationBySystemName($enabledTemplates, $supportedNotificationSystemName);
			$template = $this->getNotificationBySystemName($templates, $supportedNotificationSystemName);
			if ($enabledTemplate)
				$notifications[$supportedNotificationSystemName] = $enabledTemplate;
			elseif ($template)
				$notifications[$supportedNotificationSystemName] = $template;
		}

		$this->view->notifications = $notifications;
		
		$this->view->systemNameToNotDuplicate = Emailnotifications_Model_Emailnotifications::$systemNameToBeCheckedBrother;
	}
	
	/**
     *  add css and js to the page header
     */
    public function headerAction()
    {
        // mock action to create view
    }

	public function enableAction()
	{
		$systemName = $this->getRequest()->getParam('systemname');
		$rowIndex = $this->getRequest()->getParam('rowindex');
		$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName($systemName);
		if (!$template)
		{
			$this->view->error = 'Template not found';
			return;
		}
		$partnerId = Kms_Resource_Client::getUserClient()->getConfig()->partnerId;
		// if template is on partners account, just enable it
		if ($template->partnerId == $partnerId)
		{
			$template = Emailnotifications_Model_Emailnotifications::enableTemplate($template);
		}
		// otherwise, clone it (means that it's a template on partner 0)
		else
		{
			$template = Emailnotifications_Model_Emailnotifications::cloneTemplate($template);
		}

		$this->view->template = $template;
		$this->view->rowIndex = $rowIndex;
		$this->_helper->viewRenderer->setRender('reload-row');
	}

	public function disableAction()
	{
		$systemName = $this->getRequest()->getParam('systemname');
		$rowIndex = $this->getRequest()->getParam('rowindex');
		$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName($systemName);
		if (!$template)
		{
			$this->view->error = 'Template not found';
			return;
		}
		Emailnotifications_Model_Emailnotifications::disableNotification($template);
		// get the template of partner 0
		$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName($systemName);
		$this->view->template = $template;
		$this->view->rowIndex = $rowIndex;
		$this->_helper->viewRenderer->setRender('reload-row');
	}

	protected function getNotificationBySystemName(array $notifications, $systemName)
	{
		foreach($notifications as $notification)
		{
			if ($notification->systemName == $systemName)
				return $notification;
		}
		return null;
	}
	
/**
     * change a single caption asset
     */
    public function updateAction()
    {
    	$request = $this->getRequest();
    	
        $systemName = $request->getParam('systemname');
		$rowIndex = $request->getParam('rowindex');
		$type = $request->getParams('type');
		
		$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName($systemName);
		if (!$template)
		{
			$this->view->error = 'Template not found';
			return;
		}
		
		$data = $this->getRequest()->getPost();  

		$template->subject = $data["Update"]["subText"];
		$template->body = $data["Update"]["bodyText"];
		
		try
		{
			Emailnotifications_Model_Emailnotifications::updateNotificationTemplate($template);
		}
    	catch(Kaltura_Client_Exception $ex)
		{
			// This will occure when the template is disabled
			if ($ex->getCode() == 'EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND')
				$error = $this->view->error = "You cannot update a template while the template is disabled.";
			else
				throw $ex;
		}
		
		
		$this->view->template = $template;
		$this->view->rowIndex = $rowIndex;
		
		$this->_helper->viewRenderer->setRender('reload-row');
    }
}



