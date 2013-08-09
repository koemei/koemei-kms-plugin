<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/



class Embed_Model_Embed extends Kms_Module_BaseModel implements Kms_Interface_Functional_Entry_TabType
{
    const MODULE_NAME = 'embed';
    
    private $Embed;

    /* view hooks */
    public $viewHooks = array 
    (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array
        (
            'action' => 'tab', 
            'controller' => 'index',
            'order' => '10',
        ),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array
        (
            'action' => 'index', 
            'controller' => 'index',
            'order' => '10',
        )
    );
    /* end view hooks */
    
    /**
     * Adding a new interface function for marking this tab as available for external entry
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_TabType::isHandlingTabType()
     */
    public function isHandlingTabType(Kaltura_Client_Type_BaseEntry $entry)
    {
    	$isHandlingType = false;

        $entryType = $entry->type;
        $mediaType = ($entryType == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA ? $entry->externalSourceType : null);

    	if (Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA == $entryType &&
    			Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL == $mediaType)
    	{
    		$isHandlingType = true;
    	}
    	return $isHandlingType;
    }

    public function addViewHooks()
    {
        return array('preEmbedTab' => 'allow adding HTML before actual embed code box and embed options');
    }
    
    public function getAccessRules()
    {
        $accessrules = array(
                array(
                        'controller' => 'embed:index',
                        'actions' => array('tab', 'index', 'embed'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
        );
        
        // additional access rules checks is done in the actions via checkAllowed() - 
        // to allow for the entry owner to embed regardless of role
        
        return $accessrules;
    }

    /**
     * get the roles for configuration
     */
    public static function getRoles()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        // add entry for no role at all - owner only
        $roles[] = $translator->translate('Owner Only');
        $roles += Application_Model_Config::_getRolesForKeys();
        return $roles;
    }
    
    /**
     * check if the user is allowed to use an embed code
     * @param Kaltura_Client_Type_BaseEntry $entry
     * @return boolean
     */
    public function checkAllowed(Kaltura_Client_Type_BaseEntry $entry)
    {
        $allowed = false;

        //embed is not allowed for external media
        if ($entry->type == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA)
        {
        	$allowed = false;
        }
        else
        {
        	$embedAllowed = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'embedAllowed');
	        
    	    if ($embedAllowed)
        	{
            	$embedAllowed = $embedAllowed->toArray();   
	            $identity = Zend_Auth::getInstance()->getIdentity();            
    	        
        	    if ($identity)
            	{
                	$role = Kms_Plugin_Access::getRoleKey($identity->getRole());
                
	                //  is the entry owner or the role is allowed
    	            if ( $entry->userId == Kms_Plugin_Access::getId() || in_array($role, $embedAllowed))
        	        {
            	        $allowed = true;
                	}
	            }
    	    }
        	else
	        {
    	        // config is empty - allow for all
        	    $allowed = true;
        	}
        }
                
        return $allowed;
    }
    
    
    /**
     * generate a widget in the api
     * @param string $entryId
     * @throws Zend_Exception
     * @return string $widgetId
     */
    public function getWidgetId($entryId, $uiconfID)
    {
        $client = Kms_Resource_Client::getUserClientNoEntitlement();
        
        $widget = new Kaltura_Client_Type_Widget();
        $widget->entryId = $entryId;
        $widget->sourceWidgetId = '_' . Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $widget->uiConfId = $uiconfID;
        $widget->enforceEntitlement = false;
        try {
            $widget = $client->widget->add($widget);
            $widgetId = $widget->id;
        }
        catch (Kaltura_Client_Exception $e){
            $message = "Embed exception: Generation of widget failed";
            Kms_Log::log('embed: ' . $message . ' ' . $e->getMessage(), Kms_Log::ERR);
            throw new Zend_Exception($message, 500);
        }
        
        return $widgetId;
    }
    
}

