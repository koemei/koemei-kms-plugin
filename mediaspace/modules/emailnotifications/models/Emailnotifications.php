<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Emailnotifications_Model_Emailnotifications extends Kms_Module_BaseModel implements Kms_Interface_AdminAuthStorage, Kms_Interface_Deployable_PreDeployment
{
	const MODULE_NAME = 'Emailnotifications';
	
	//This is for the demand to duplicate the enable/disable/clone metodologic for his depended 'brother' 'New_Item_Pending_Moderation_2'   
	public static $systemNameToBeChecked = 'New_Item_Pending_Moderation';
	public static $systemNameToBeCheckedBrother = 'New_Item_Pending_Moderation_2';

	/* view hooks */
	public $viewHooks = array
	(
		Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array( 
                    'action' => 'header',
                    'controller' => 'index', 
                    'order' => 20
            ),
	);
	/* end view hooks */

	/**
	 * @return array
	 */
	public function getAccessRules()
	{
		$accessRules = array(
			array(
				'controller' => 'emailnotifications:index',
				'actions' => array('index', 'enable', 'disable', 'header', 'update'),
				'role' => Kms_Plugin_Access::PARTNER_ROLE,
			),
//            array(
//                 'controller' => 'emailnotifications:index',
//                 'actions' => array('header','index'),
//                 'role' => Kms_Plugin_Access::PARTNER_ROLE,
//                )
            
        );
		return $accessRules;
	}
	
	//This is dependency in a level of installed modules only. No enabling/disabling connection
	public static function getModuleDependency()
	{
		return array('emailnotifications');
	}
	

	/**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canInstall()
     */
    public function canInstall()
    {
        return $this->externalTypesExists();
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::canEnable()
     */
    public function canEnable()
    {
        return $this->externalTypesExists();
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Deployable_PreDeployment::getPreDeploymentFailReason()
     */
    public function getPreDeploymentFailReason()
    {
        return $this->err;
    }

    /**
     * check that the server version has the external types defined
     * @return boolean
     */
    private function externalTypesExists()
    {
        $result = true;

        try {
            self::getEventNotificationTemplates();
        }
        catch (Kaltura_Client_Exception $e){
            if ($e->getCode() == 'SERVICE_FORBIDDEN') {
                Kms_Log::log('email_notification: ' . $e->getCode() . ': ' .$e->getMessage() . ' . Module will not be installed/enabled.' , Kms_Log::DEBUG);                
                $this->err = $e->getMessage();
                $result = false;
            }
            elseif ($e->getCode() == 'SERVICE_DOES_NOT_EXISTS') {
                Kms_Log::log('email_notification: ' . $e->getCode() . ': ' .$e->getMessage() . ' . Module will not be installed/enabled.' , Kms_Log::DEBUG);
                $this->err = $e->getMessage();                
                $result = false;
            }
        }

        return $result;
    }

	/**
	 * @return bool
	 */
	public static function isModuleEnabled()
	{
		return (bool)self::getModuleConfig('enabled');
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public static function getModuleConfig($key)
	{
		return Kms_Resource_Config::getModuleConfig(strtolower(Emailnotifications_Model_Emailnotifications::MODULE_NAME), $key);
	}

	/**
	 * @return array
	 */
	public static function getSupportedNotifications()
	{
		$supportedNotifications = array();
		$configArray = self::getModuleConfig('supportedNotifications');
		if (!$configArray)
			return $supportedNotifications;

		foreach($configArray as $config)
		{
			$supportedNotifications[] = $config->systemName;
		}
		return $supportedNotifications;
	}

	/**
	 * @return array
	 */
	public static function getEventNotificationTemplates()
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$filter = new Kaltura_Client_EventNotification_Type_EventNotificationTemplateFilter();
		$filter->typeEqual = Kaltura_Client_EventNotification_Enum_EventNotificationTemplateType::EMAIL;
		$pager = new Kaltura_Client_Type_FilterPager();
		$pager->pageSize = 500;
		$templatesResult = $eventNotificationPlugin->eventNotificationTemplate->listTemplates($filter, $pager);
		return $templatesResult->objects;
	}

	/**
	 * @return array
	 */
	public static function getEnabledEventNotificationTemplates()
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$filter = new Kaltura_Client_EventNotification_Type_EventNotificationTemplateFilter();
		$filter->statusEqual = Kaltura_Client_EventNotification_Enum_EventNotificationTemplateStatus::ACTIVE;
		$filter->typeEqual = Kaltura_Client_EventNotification_Enum_EventNotificationTemplateType::EMAIL;
		$pager = new Kaltura_Client_Type_FilterPager();
		$pager->pageSize = 500;
		$templatesResult = $eventNotificationPlugin->eventNotificationTemplate->listAction($filter, $pager);
		return $templatesResult->objects;
	}

	/**
	 * @param $systemName
	 * @return Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate
	 */
	public static function getEventNotificationBySystemName($systemName)
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$filter = new Kaltura_Client_EventNotification_Type_EventNotificationTemplateFilter();
		$filter->typeEqual = Kaltura_Client_EventNotification_Enum_EventNotificationTemplateType::EMAIL;
		$pager = new Kaltura_Client_Type_FilterPager();
		$pager->pageSize = 500;

		// first look in partners account
		$templatesResult = $eventNotificationPlugin->eventNotificationTemplate->listAction($filter, $pager);
		foreach($templatesResult->objects as $template)
		{
			if ($template->systemName == $systemName)
				return $template;
		}

		// if not found in partner, get from templates
		$templatesResult = $eventNotificationPlugin->eventNotificationTemplate->listTemplates($filter, $pager);
		foreach($templatesResult->objects as $template)
		{
			if ($template->systemName == $systemName)
				return $template;
		}

		return null;
	}

	public static function enableTemplate(Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate $template)
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$status = $eventNotificationPlugin->eventNotificationTemplate->updateStatus($template->id, Kaltura_Client_EventNotification_Enum_EventNotificationTemplateStatus::ACTIVE);

		if ($template->systemName == self::$systemNameToBeChecked)
		{
			$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName(self::$systemNameToBeCheckedBrother); 
			$status = $status & $eventNotificationPlugin->eventNotificationTemplate->updateStatus($template->id, Kaltura_Client_EventNotification_Enum_EventNotificationTemplateStatus::ACTIVE);
		}
		
		return $status;
	}

	public static function cloneTemplate(Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate $template)
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$updateTemplate = new Kaltura_Client_EventNotification_Type_EventNotificationTemplate();
		$newTemplate = new Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate();
		$newTemplate->subject = self::replaceTokens($template->subject);
		$newTemplate->body = self::replaceTokens($template->body);
		$newTemplate = $eventNotificationPlugin->eventNotificationTemplate->cloneAction($template->id, $newTemplate);
		$eventNotificationPlugin->eventNotificationTemplate->updateStatus($newTemplate->id, Kaltura_Client_EventNotification_Enum_EventNotificationTemplateStatus::ACTIVE);
		$newTemplate->status = Kaltura_Client_EventNotification_Enum_EventNotificationTemplateStatus::ACTIVE;
			
		if ($template->systemName == self::$systemNameToBeChecked)
		{
			$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName(self::$systemNameToBeCheckedBrother); 
			self::cloneTemplate($template);
		}
		return $newTemplate;
	}

	public static function disableNotification(Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate $template)
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$eventNotificationPlugin->eventNotificationTemplate->delete($template->id);
		
		if ($template->systemName == self::$systemNameToBeChecked)
		{
			$template = Emailnotifications_Model_Emailnotifications::getEventNotificationBySystemName(self::$systemNameToBeCheckedBrother); 
			$eventNotificationPlugin->eventNotificationTemplate->delete($template->id);
		}
	}
	
	public static function updateNotificationTemplate(Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate $template)
	{
		$adminClient = Kms_Resource_Client::getAdminClient();
		$eventNotificationPlugin = Kaltura_Client_EventNotification_Plugin::get($adminClient);
		$newTemplate = new Kaltura_Client_EmailNotification_Type_EmailNotificationTemplate();
		$newTemplate->subject = $template->subject;
		$newTemplate->body = $template->body;
		$eventNotificationPlugin->eventNotificationTemplate->update($template->id,$newTemplate);
	}

	public static function replaceTokens($str)
	{
		$serverUrlHelper = new Zend_View_Helper_ServerUrl();
		$baseUrlHelper = new Zend_View_Helper_BaseUrl();
		$tokens = array(
			'AppTitle' => Kms_Resource_Config::getConfiguration('application', 'title'),
			'ChannelSettingsPendingURLPrefix' => $serverUrlHelper->serverUrl($baseUrlHelper->baseUrl('/channels/edit/')),
			'ChannelSettingsPendingURLSuffix' => '/tab/pending',
			'AppEntryUrl' => $serverUrlHelper->serverUrl($baseUrlHelper->baseUrl('/media/')),
		);
		foreach($tokens as $token => $value)
			$str = str_replace('['.$token.']', $value, $str);

		return $str;
	}

	/**
	 * Returns true if controller / action should use admin storage for auth
	 *
	 * @var $request Zend_Controller_Request_Abstract
	 * @return bool
	 */
	function shouldUseAdminAuthStorage(Zend_Controller_Request_Abstract $request)
	{
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		return $controller == 'index' && in_array($action, array('index', 'enable', 'disable', 'update'));
	}
}