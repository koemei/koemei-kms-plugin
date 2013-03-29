<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Upgrade40x
 *
 * @author talb
 */
class Kms_Setup_Upgrade40x
{
    private static $_configModel;
    private $_oldConfig;

    private $_tempClient;

    function __construct($oldConfigPath)
    {

        if (!file_exists($oldConfigPath))
        {
            Kms_Log::log('install: could not read old config', Kms_Log::WARN);
            throw new Exception('could not read old config');
        }
        $this->_oldConfig = new Zend_Config_Ini($oldConfigPath, null, array('allowModifications' => true));
        if($this->_oldConfig === false)
        {
            Kms_Log::log('install: could not parse KMS4 config file', Kms_Log::WARN);
            throw new Exception('could not parse KMS3 config file');
            // cannot touch empty config file
        }

        Kms_Resource_Client::setConfigValue(Kms_Resource_Client::CLIENT_PARTNER_ID, $this->_oldConfig->client->partnerId);
        Kms_Resource_Client::setConfigValue(Kms_Resource_Client::CLIENT_ADMIN_SECRET, $this->_oldConfig->client->adminSecret);
        
        Kms_Log::log('upgrade: setting client config vale with partner ID '. $this->_oldConfig->client->partnerId.' and secret '. $this->_oldConfig->client->adminSecret);
        $this->_tempClient = Kms_Resource_Client::getAdminClientNoEntitlement();
        $this->_tempClient->getConfig()->serviceUrl = $this->_oldConfig->client->serviceUrl;
        
        self::init();
        if (!$this->createEmptyConfigFile())
        {
            Kms_Log::log('install: could not create config file', Kms_Log::WARN);
            throw new Exception('could not create config file');
            // cannot touch empty config file
        }
    }

    public function getTempClient()
    {
        return $this->_tempClient;
    }

    public static function createEmptyConfigFile()
    {
        $res = touch(Kms_Setup_Common::getMainConfigFilePath());
        return $res;
    }

    private static function init()
    {
        // check if new configs are writable
        self::$_configModel = new Application_Model_Config();
    }

    public function upgrade($additionalSetings = array())
    {
        // iterate over the ini to get the modules out
        $configModel = new Application_Model_Config();

        // in case application section is not in KMS4 config, add it manually
        if(!isset($this->_oldConfig->application))
        {
            $applicationConfigArr = array();
            $appConfig = new Zend_Config($applicationConfigArr, true);
            $configModel->saveConfig('application', $appConfig->toArray());
        }
        
        foreach($this->_oldConfig as $section => $config)
        {
            if(preg_match('/^module_.*$/', $section))
            {
                // this is a module configuration section
                $sectionName = preg_replace('/^module_(.*)$/', '$1', $section );
            }
            else
            {
                $sectionName = $section;
            }

            if ($sectionName == 'auth')
            {
                $config = $this->handleAuthSection($config);
            }

            try
            {
                $configModel->saveConfig($sectionName, $config->toArray());
            }
            catch(Zend_Controller_Exception $e)
            {
                Kms_Log::log('install: failed to import configuration from uploaded ini file', Kms_Log::WARN);
                return false;
            }
        }
        Kms_Resource_Config::reInitConfig(Kms_Setup_Common::getMainConfigFilePath());
        Kms_Resource_Client::reInitClients();

        Kms_Setup_Common::initCacheConfigFile();

        return true;
    }

    private function handleAuthSection($config)
    {
        $oldAuthAdapter = $config->authAdapter;
        switch($oldAuthAdapter)
        {
            case 'Kms_Auth_Kaltura':
                $config->authNAdapter = 'Kms_Auth_AuthN_Kaltura';
                $config->authZAdapter = 'Kms_Auth_AuthZ_Kaltura';
                break;

            case 'Kms_Auth_Ldap':
                $config->authNAdapter = 'Kms_Auth_AuthN_Ldap';
                $config->authZAdapter = 'Kms_Auth_AuthZ_Ldap';
                break;

            case 'Kms_Auth_Sso':
                $config->authNAdapter = 'Kms_Auth_AuthN_Sso';
                $config->authZAdapter = 'Kms_Auth_AuthZ_Sso';
                break;

            case 'Kms_Auth_Shibboleth':
                $config->authNAdapter = 'Kms_Auth_AuthN_Shibboleth';
                $config->authZAdapter = 'Kms_Auth_AuthZ_Shibboleth';
                break;

            default:
                Kms_Log::log('authentication class unidentified. settings will most likely not work in this version of KMS.', Kms_Log::CRIT);
                $config->authNAdapter = $oldAuthAdapter;
                $config->authZAdapter = $oldAuthAdapter;
        }
        unset($config->authAdapter);

        return $config;
    }
}

?>