<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * Created on Dec 3, 2011
 *
 */
class Kms_Setup_Install
{
    /**
     *
     * @var Application_Model_Config
     */
    private static $_configModel = null;
    
    public static function buildConfigClientFromPartnerInfo(Kaltura_Client_Type_Partner $partnerInfo, $serviceUrl)
    {
        $configPost = array();
        $configPost['partnerId'] = $partnerInfo->id;
        $configPost['secret'] = $partnerInfo->secret;
        $configPost['adminSecret'] = $partnerInfo->adminSecret;
        $configPost['serviceUrl'] = $serviceUrl;
        self::getConfigModel()->saveConfig('client', $configPost);        
    }

    /**
     * build parts of the application config that must be determined programatically.
     * @param string $instanceId
     * @throws Exception
     */
    public static function buildApplicationConfig($instanceId, $privacyContext)
    {
        // check that we have a ks in the admin client - 
        // can happen if this function was called before Kms_Resource_Client::reInitClients();
        $client = Kms_Resource_Client::getAdminClient();
        $ks = $client->getKs();
        if (empty($ks)){
            throw new Exception('buildApplicationConfig() was called before Kms_Resource_Client::reInitClients() - will not work without ks.');
        }
        
        if (!self::getConfigModel()->isLikeInPartnerPermissions()){
            // like is disabled on server - change default config
            $configPost = array('enableLike' => 0);
            self::getConfigModel()->saveConfig('application', $configPost);
        }
        
        $configPost = array(
            'instanceId' => $instanceId,
            'privacyContext' => $privacyContext,
        );
        self::getConfigModel()->saveConfig('application', $configPost);
    }

    private static function getConfigModel()
    {
    	if(is_null(self::$_configModel))
        {
            self::$_configModel = new Application_Model_Config();
        }
        return self::$_configModel;
    }
    
    private static function buildConfigRoles()
    {
    	$configPost = array();
        self::getConfigModel()->saveConfig('roles', $configPost);
    }
    
    private static function buildConfigLogging()
    {
        $configPost = array();
        self::getConfigModel()->saveConfig('logging', $configPost);
    }
    
    public static function initEssentialConfig($partnerInfo, $serviceUrl, $instanceId, $privacyContext)
    {
        Kms_Setup_Upgrade::createEmptyConfigFile();
    	self::buildConfigClientFromPartnerInfo($partnerInfo, $serviceUrl);
        //self::buildApplicationConfig($instanceId);
        //self::buildConfigLogging();
        //self::buildConfigRoles();
        Kms_Resource_Config::reInitConfig(Kms_Setup_Common::getMainConfigFilePath());
        Kms_Resource_Client::reInitClients();
        
        self::buildApplicationConfig($instanceId, $privacyContext);
        Kms_Resource_Config::reInitConfig(Kms_Setup_Common::getMainConfigFilePath());
        
        Kms_Setup_Common::initCacheConfigFile();
    }
}
