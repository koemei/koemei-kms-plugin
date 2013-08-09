<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Channelsubscription_IndexController extends Kms_Module_Controller_Abstract
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
		$contextSwitch->addActionContext('subscribe', 'ajax')->initContext();
		$contextSwitch->addActionContext('unsubscribe', 'ajax')->initContext();
		$this->_helper->contextSwitch()->initContext();
	}


	public function subscribeAction()
	{
		$categoryId  = $this->getRequest()->getParam('channelid');

		$auth     = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		$userId   = $identity->getId();
		$error    = '';

		// using no entitlement client because we need to update the category users when the session is not for the category manager
		$client         = Kms_Resource_Client::getAdminClientNoEntitlement();
		$categoryUser   = $this->getCategoryUser($client, $categoryId, $userId);

		$cacheParams = array('categoryId' => $categoryId, 'userId' => $userId);
		$cacheTags = array( 'categoryId_' . $categoryId, 'userId_' . $userId);
		Kms_Resource_Cache::apiClean('categoryUserForVis', $cacheParams, $cacheTags);

		$newPermissions = $this->addPermission($categoryUser ? $categoryUser->permissionNames : '', 'CATEGORY_SUBSCRIBE');
		if (is_null($categoryUser))
		{
			$categoryUser                  = new Kaltura_Client_Type_CategoryUser();
			$categoryUser->categoryId      = $categoryId;
			$categoryUser->userId          = $userId;
			$categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::NONE;
			$categoryUser->permissionNames = $newPermissions;
			$client->categoryUser->add($categoryUser);
		}
		else
		{
			$updateCategoryUser = new Kaltura_Client_Type_CategoryUser();
			$updateCategoryUser->permissionNames = $newPermissions;
			try
			{
				$client->categoryUser->update($categoryId, $userId, $updateCategoryUser, true);
			}
			catch(Kaltura_Client_Exception $ex)
			{
				if ($ex->getCode() == 'CANNOT_UPDATE_CATEGORY_USER_OWNER')
					$error = $this->_translate->translate('As the channel owner you cannot subscribe to this channel');
				else
					throw $ex;
			}
		}

		$this->view->error = $error;
	}

	public function unsubscribeAction()
	{
		$categoryId  = $this->getRequest()->getParam('channelid');

		$auth     = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		$userId   = $identity->getId();

		// using no entitlement client because we need to update the category users when the session is not for the category manager
		$client         = Kms_Resource_Client::getAdminClientNoEntitlement();
		$categoryUser   = $this->getCategoryUser($client, $categoryId, $userId);
		if (is_null($categoryUser))
			return;

		$newPermissions = $this->removePermission($categoryUser->permissionNames, 'CATEGORY_SUBSCRIBE');
		$updateCategoryUser = new Kaltura_Client_Type_CategoryUser();
		$updateCategoryUser->permissionNames = $newPermissions;
		$client->categoryUser->update($categoryId, $userId, $updateCategoryUser, true);
		
		$cacheParams = array('categoryId' => $categoryId, 'userId' => $userId);
		$cacheTags = array( 'categoryId_' . $categoryId, 'userId_' . $userId);
		Kms_Resource_Cache::apiClean('categoryUserForVis', $cacheParams, $cacheTags);
	}

	public function buttonAction()
	{
		$model = Kms_Resource_Models::getChannel();
		$channelId = $model->Category instanceof Kaltura_Client_Type_Category ? $model->Category->id : null;
		if (!$channelId)
			return;

		$this->view->allowSubscribeToChannel = Channelsubscription_Model_Channelsubscription::getSubscriptionAllowedInChannel($channelId);

		$auth = Zend_Auth::getInstance();
		$identity = $auth->getIdentity();
		$userId = $identity->getId();
		$client = Kms_Resource_Client::getAdminClient();
				
		$categoryUser = $this->getCachedCategoryUser($client, $channelId, $userId);
		
		if (is_null($categoryUser))
			$this->view->isUserSubscribed = false;
		else
			$this->view->isUserSubscribed = in_array('CATEGORY_SUBSCRIBE', explode(',', $categoryUser->permissionNames));
		$this->view->channelId = $channelId;
	}
	
	protected function getCachedCategoryUser(Kaltura_Client_Client $client, $categoryId, $userId)
	{
		$cacheParams = array('categoryId' => $categoryId, 'userId' => $userId);
		
		if(($categoryUser = Kms_Resource_Cache::apiGet('categoryUserForVis', $cacheParams)) === false)
		{
			$categoryUser = $this->getCategoryUser($client, $categoryId, $userId);
		}
		
		$categoryUser = $categoryUser===false ? null: $categoryUser;
		
		$cacheTags = array( 'categoryId_' . $categoryId, 'userId_' . $userId);
		Kms_Resource_Cache::apiSet('categoryUserForVis', $cacheParams, $categoryUser, $cacheTags);
		
		return $categoryUser;
	}

	/**
	 * @param Kaltura_Client_Client $client
	 * @param $categoryId
	 * @param $userId
	 * @return Kaltura_Client_Type_CategoryUser
	 * @throws Kaltura_Client_Exception
	 */
	protected function getCategoryUser(Kaltura_Client_Client $client, $categoryId, $userId)
	{
		/** @var $categoryUser Kaltura_Client_Type_CategoryUser */
		$categoryUser = null;
		try
		{
			$categoryUser = $client->categoryUser->get($categoryId, $userId);
		}
		catch (Kaltura_Client_Exception $ex)
		{
			if (!in_array($ex->getCode(), array('INVALID_USER_ID', 'CATEGORY_NOT_FOUND', 'INVALID_CATEGORY_USER_ID')))
				throw $ex;
		}
		return $categoryUser;
	}

	/**
	 * @param $currentPermissions
	 * @param $newPermission
	 * @return string
	 */
	protected function addPermission($currentPermissions, $newPermission)
	{
		if (!trim($currentPermissions))
			return $newPermission;

		$permissions   = explode(',', $currentPermissions);
		$alreadyExists = false;
		foreach ($permissions as $permission)
		{
			if (trim($permission) == $newPermission)
				$alreadyExists = true;
		}

		if ($alreadyExists)
			return $currentPermissions;

		$permissions[] = $newPermission;
		return implode(',', $permissions);
	}

	/**
	 * @param $currentPermissions
	 * @param $removePermission
	 * @return string
	 */
	protected function removePermission($currentPermissions, $removePermission)
	{
		if (!trim($currentPermissions))
			return '';

		$permissions   = explode(',', $currentPermissions);
		$existsAt = -1;
		foreach ($permissions as $i => $permission)
		{
			if (trim($permission) == $removePermission)
				$existsAt = $i;
		}

		if ($existsAt >= 0)
			unset($permissions[$existsAt]);

		return implode(',', $permissions);
	}
}



