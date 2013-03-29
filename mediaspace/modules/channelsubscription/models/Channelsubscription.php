<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Channelsubscription_Model_Channelsubscription extends Kms_Module_BaseModel implements Kms_Interface_Form_Channel_Edit, Kms_Interface_Model_Channel_Save
{
	const MODULE_NAME = 'Channelsubscription';

	/* view hooks */
	public $viewHooks = array
	(
		Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNEL_BUTTONS => array(
			'controller' => 'index',
			'action' => 'button',
			'order' => 30
		)
	);
	/* end view hooks */

	/**
	 * @return array
	 */
	public function getAccessRules()
	{
		$accessRules = array(
			array(
				'controller' => 'channelsubscription:index',
				'actions' => array('button', 'subscribe', 'unsubscribe'),
				'role' => Kms_Plugin_Access::VIEWER_ROLE,
			),
		);
		return $accessRules;
	}

	/**
	 * get the list of customdata profiles for the partner for entries objects
	 */
	public static function configGetCustomdataProfiles()
	{
		return Kms_Helper_Metadata::getCustomdataProfiles(Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY);
	}

	/**
	 * Pass the Edit Category form to a module as a parameter
	 * @param Application_Form_EditChannel $form
	 */
	public function editForm(Application_Form_EditChannel $form)
	{
		$model = Kms_Resource_Models::getChannel();
		$channelId = $model->Category instanceof Kaltura_Client_Type_Category ? $model->Category->id : null;

		$translate = Zend_Registry::get('Zend_Translate');
		$spec = array(
			'belongsTo' => 'Category',
			'name' => 'enableChannelSubscription',
			'description' => $translate->translate('Enable subscription to channel'),
		);
		$element = new Zend_Form_Element_Checkbox($spec);

		if ($channelId)
			$allowed = self::getSubscriptionAllowedInChannel($channelId);
		else
			$allowed = false; // we are creating a new channel
		$element->setValue($allowed ? '1' : '0' );
		$element->getDecorator('Description')->setTag('span');
		$element->removeDecorator('Label');
		$element->getDecorator('HtmlTag')->clearOptions()->setTag('div')->setOption('id', 'Category-comments-element');
		$form->addElement($element);
	}

	/**
	 * Function gets the channel model after the category has been saved in Kaltura over the API.
	 * Function is not expected to return results. Any returned value will be ignored.
	 *
	 * @param Application_Model_Channel $model
	 * @param array $data
	 */
	function save(Application_Model_Channel $model, array $data)
	{
		if (!isset($data['enableChannelSubscription']))
			return;

		$enableChannelSubscription = $data['enableChannelSubscription'] == '1' ? 'true' : 'false';
		$channelId = $model->Category instanceof Kaltura_Client_Type_Category ? $model->Category->id : null;
		$profileId = self::getModuleConfig('channelSubscriptionProfileId');
		$metadataObject = self::getChannelSubscriptionCustomdata($channelId);
		if (!$metadataObject)
		{
			$xml = '<metadata><AllowSubscriptionsInChannel>'.$enableChannelSubscription.'</AllowSubscriptionsInChannel></metadata>';
			$client = Kms_Resource_Client::getAdminClient();
			$metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
			$metadataObject = $metadataPlugin->metadata->add($profileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, $channelId, $xml);
		}
		else
		{
			$xml = new SimpleXMLElement($metadataObject->xml);
			$xml->AllowSubscriptionsInChannel = $enableChannelSubscription;
			$client = Kms_Resource_Client::getAdminClient();
			$metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
			$metadataObject = $metadataPlugin->metadata->update($metadataObject->id, $xml->asXML());
		}

		Kms_Resource_Cache::apiClean('channel_subscription', array('channelId' => $channelId));
		Kms_Resource_Cache::apiSet('channel_subscription', array('channelId' => $channelId), $metadataObject);
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
		return Kms_Resource_Config::getModuleConfig(strtolower(Channelsubscription_Model_Channelsubscription::MODULE_NAME), $key);
	}

	public static function getSubscriptionAllowedInChannel($channelId)
	{
		$allow = false;
		$customData = self::getChannelSubscriptionCustomdata($channelId);
		if ($customData && $customData->xml)
		{
			$xml = new SimpleXMLElement($customData->xml);
			$allow = isset($xml->AllowSubscriptionsInChannel) && (string)$xml->AllowSubscriptionsInChannel === "false" ? false : true;
		}
		return $allow;
	}

	/**
	 * @param $channelId
	 * @return Kaltura_Client_Metadata_Type_Metadata
	 */
	public static function getChannelSubscriptionCustomdata($channelId)
	{
		$metadata = Kms_Resource_Cache::apiGet('channel_subscription', array('channelId' => $channelId));
		if (!$metadata)
		{
			$profileId = self::getModuleConfig('channelSubscriptionProfileId');
			if (!$profileId)
			{
				Kms_Log::log(__METHOD__.' - no profile was set for channel subscription custom data', Kms_Log::ERR);
				return null;
			}

			// get the metadata from the api
			$filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
			$filter->objectIdEqual = $channelId;
			$filter->metadataProfileIdEqual = $profileId;
			$filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;
			$client = Kms_Resource_Client::getAdminClient();
			$metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);

			$metadataResponse = $metadataPlugin->metadata->listAction($filter);
			if (!count($metadataResponse->objects))
			{
				Kms_Log::log(__METHOD__.' - metadata object not found for profile '.$profileId.' and category '.$channelId, Kms_Log::ERR);
				return null;
			}
			$metadata = $metadataResponse->objects[0];
			Kms_Resource_Cache::apiSet('channel_subscription', array('channelId' => $channelId), $metadata);
		}

		return $metadata;
	}
}