<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Embedplaylist_Model_Embedplaylist extends Kms_Module_BaseModel
{
    const MODULE_NAME = 'embedplaylist';

    private $Embedplaylist;

    /* view hooks */
    public $viewHooks = array
        (
        'MyPlaylistsSide' => array
            (
            'action' => 'index',
            'controller' => 'index',
            'order' => '1',
        )
    );
    /* end view hooks */

    public function getAccessRules()
    {
        $accessrules = array(
                array(
                        'controller' => 'embedplaylist:index',
                        'actions' => array('index', 'embed'),
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
     * @param Kaltura_Client_Type_Playlist $playlist
     * @return boolean
     */
    public function checkAllowed(Kaltura_Client_Type_Playlist $playlist)
    {
        $allowed = false;
        $embedAllowed = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'embedAllowed');
        
        if ($embedAllowed)
        {
            $embedAllowed = $embedAllowed->toArray();
            $identity = Zend_Auth::getInstance()->getIdentity();
    
            if ($identity)
            {
                $role = Kms_Plugin_Access::getRoleKey($identity->getRole());
    
                // user role fit - or the user is the entry owner
                if ( in_array($role,$embedAllowed) || ($playlist->userId == Kms_Plugin_Access::getId()))
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
    
        return $allowed;
    }
    
    /**
     * generate a widget in the api
     * @param string $playlistId
     * @throws Zend_Exception
     * @return string $widgetId
     */
    public function getWidgetId($playlistId)
    {
        $client = Kms_Resource_Client::getUserClientNoEntitlement();
    
        $widget = new Kaltura_Client_Type_Widget();
        $widget->entryId = $playlistId;
        $widget->sourceWidgetId = '_' . Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $widget->privacyContext = Kms_Resource_Config::getCategoryContext();
        try {
            $widget = $client->widget->add($widget);
            $widgetId = $widget->id;
        }
        catch (Kaltura_Client_Exception $e){
            $message = "Embed exception: Generation of widget failed";
            Kms_Log::log('embedplaylist: ' . $message . ' ' . $e->getMessage(), Kms_Log::ERR);
            throw new Zend_Exception($message, 500);
        }        
        
        return $widgetId;
    }
}

