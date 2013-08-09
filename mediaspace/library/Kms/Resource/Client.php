<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Manage Resource plugin for Kaltura Client
 * Used application wide
 * 
 * @author leon
 */
class Kms_Resource_Client extends Zend_Application_Resource_ResourceAbstract
{
    private static $_adminClient = NULL;
    private static $_userClient = NULL;
    private static $_adminClientNoEntitlement = NULL;
    private static $_userClientNoEntitlement = NULL;
    private static $_config = null;
    private static $_opts = null;

    const CLIENT_CONFIG = 'config';
    const CLIENT_CONFIG_FILE = 'file';
    const CLIENT_CONFIG_SECTION = 'section';
    const CLIENT_PARTNER_ID = 'partnerId';
    const CLIENT_SECRET = 'secret';
    const CLIENT_ADMIN_SECRET = 'adminSecret';

    const DEFAULT_KS_EXPIRY = 7200;
    
    /**
     *
     * @return Kaltura_Client_Client
     */
    public static function getAdminClient()
    {
        if(is_null(self::$_adminClient))
        {
            self::$_adminClient = self::initClient(Kaltura_Client_Enum_SessionType::ADMIN);
        }

        return self::$_adminClient;
    }

    /**
     * create an admin client without the category context privlige
     * @return Kaltura_Client_Client
     */
    public static function getAdminClientNoEntitlement()
    {
        if(is_null(self::$_adminClientNoEntitlement))
        {
            self::$_adminClientNoEntitlement = self::initClient(Kaltura_Client_Enum_SessionType::ADMIN, FALSE);
        }
    
        return self::$_adminClientNoEntitlement;
    }
    
    /**
     *
     * @return Kaltura_Client_Client
     */
    public static function getUserClient()
    {
        if (is_null(self::$_userClient))
        {
            self::$_userClient = self::initClient(Kaltura_Client_Enum_SessionType::USER);
        }

        return self::$_userClient;
    }

    /**
     * create a client client without the category context privlige
     * @return Kaltura_Client_Client
     */
    public static function getUserClientNoEntitlement()
    {
        if (is_null(self::$_userClientNoEntitlement))
        {
            self::$_userClientNoEntitlement = self::initClient(Kaltura_Client_Enum_SessionType::USER, FALSE);
        }
    
        return self::$_userClientNoEntitlement;
    }
    
    /**
     * init a kaltura client with a ks
     * @param unknown_type $clientType
     * @param unknown_type $enableEntitlement - create the ks with entitlement and context
     * @return Kaltura_Client_Client
     */
    public static function initClient($clientType = Kaltura_Client_Enum_SessionType::USER, $enableEntitlement = TRUE)
    {
        $partnerId = self::$_config->{self::CLIENT_PARTNER_ID};

        // configuration
        $clientConfig = new Kaltura_Client_Configuration($partnerId);
        $clientConfig->clientTag = "KMS ".Kms_Resource_Config::getVersion().', build '.BUILD_NUMBER;
        $clientConfig->serviceUrl = Kms_Resource_Config::getConfiguration('client', 'serviceUrl');
        $clientConfig->setLogger(new Kms_ClientLog());
        $clientConfig->curlTimeout = 20;
        $clientConfig->verifySSL = Kms_Resource_Config::getConfiguration('client', 'verifySSL');
        // create the ks and set it in the client
        $client = new Kaltura_Client_Client($clientConfig);
    
        $privileges = self::getDefaultPrivileges($enableEntitlement);
        $userId = self::getUserId();
        // entitlement and category context
        
        
        if (isset(self::$_config->{self::CLIENT_ADMIN_SECRET})){
            $adminSecret = self::$_config->{self::CLIENT_ADMIN_SECRET};
            $client->setKs($client->generateSessionV2($adminSecret, $userId, $clientType, $partnerId, self::DEFAULT_KS_EXPIRY, $privileges));
        }
            
        return $client;
    }
    
    public static function getUserId() 
    {
	    // user id
        $userId = Kms_Plugin_Access::getId();
        $role = Zend_Auth::getInstance()->hasIdentity() ? Zend_Auth::getInstance()->getIdentity()->getRole(): null;
        $roleConst = Kms_Plugin_Access::getRole($role);
        if(is_null($userId) || $roleConst == Kms_Plugin_Access::getRole(Kms_Plugin_Access::ANON_ROLE) || $roleConst == Kms_Plugin_Access::getRole(Kms_Plugin_Access::EMPTY_ROLE))
        {
            $userId = '';
        }
        return $userId;
    } 
    
    private static function getDefaultPrivileges($enableEntitlement)
    {
    	$privileges = '';
        if ($enableEntitlement)
        {
            $categoryContext = Kms_Resource_Config::getCategoryContext();
            $privileges = "privacycontext:{$categoryContext},enableentitlement";
        }
        else{
            $privileges = "disableentitlement";
        }
        return $privileges;
    }
    
    /**
     * clear existing clients. read configuration again.
     */
    public static function reInitClients()
    {
        // re-read the config
        self::$_config = Kms_Resource_Config::getSection(self::$_opts[self::CLIENT_CONFIG][self::CLIENT_CONFIG_SECTION]);
        
        // reset the clients
        self::$_adminClient = NULL;
        self::$_userClient = NULL;
        self::$_adminClientNoEntitlement = NULL;
        self::$_userClientNoEntitlement = NULL;
    }

    public function init()
    {
        self::$_opts = $this->getOptions();
        //self::$_config = new Zend_Config_Ini($options[self::CLIENT_CONFIG][self::CLIENT_CONFIG_FILE], $options[self::CLIENT_CONFIG][self::CLIENT_CONFIG_SECTION]);
        self::$_config = Kms_Resource_Config::getSection(self::$_opts[self::CLIENT_CONFIG][self::CLIENT_CONFIG_SECTION]);
    }
    
    public static function setConfigValue($configKey, $value)
    {
        self::$_config->{$configKey} = $value;
    }
    
    public static function generateSession($type, $expiry = 86400, $privileges = '', $enableEntitlement = true)
    {
    	$privileges  = $privileges . "," . self::getDefaultPrivileges($enableEntitlement);
    	return self::getUserClient()->generateSessionV2(self::$_config->{self::CLIENT_ADMIN_SECRET}, self::getUserId(), $type, self::$_config->{self::CLIENT_PARTNER_ID}, $expiry, $privileges);
    }
}
