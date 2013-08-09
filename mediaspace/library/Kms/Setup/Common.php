<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Kms_Setup_Common
{
    const KNOWN_VERSION_2 = '2';
    const KNOWN_VERSION_30x = '3.0.x';
    const KNOWN_VERSION_40x = '4.0.x';
    
    const UPGRADE_EXCEPTION_WRONG_PARTNER_CREDENTIALS = 'Upgrade Error: Wrong partner credentials';
    const UPGRADE_EXCEPTION_DUPLICATE_INSTANCE_ID = 'Upgrade Error: duplicate instance ID';
    
    public static function getMainConfigFilePath()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrapOptions = $front->getParam('bootstrap')->getOptions();
    	return $bootstrapOptions['resources']['config']['config'];
    }
    
    public static function initCacheConfigFile()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrapOptions = $front->getParam('bootstrap')->getOptions();
        $destination = $bootstrapOptions['resources']['config']['cache'];
        $source = str_replace('/cache.ini', '/cache.dist.ini', $destination);
        if(!file_exists($destination) && file_exists($source))
        {
            @copy($source, $destination);
        }
    }

    public static function factory($version, $configFilePath)
    {
        if($version == self::KNOWN_VERSION_2)
        {
            $upgradeObj = new Kms_Setup_Upgrade($configFilePath);
        }
        elseif($version == self::KNOWN_VERSION_30x)
        {
            $upgradeObj = new Kms_Setup_Upgrade30x($configFilePath);
        }
        elseif($version == self::KNOWN_VERSION_40x)
        {
            $upgradeObj = new Kms_Setup_Upgrade40x($configFilePath);
        }
        else
        {
            Kms_Log::log('install: Unknown origin version to migrate from. '.$version, Kms_Log::WARN);
            throw new Exception('Unknown origin version to migrate from');
        }
        return $upgradeObj;
    }
    
    /**
     * method to validate partner info (for upgrade where we need to do it from client object constructed directly from old config)
     * 
     * @param Kaltura_Client_Client $client
     * @return bool 
     * @throws Kms_Setup_Exception
     */
    public static function validatePartnerInfo(Kaltura_Client_Client $client)
    {
        try
        {
            $partnerInfo = $client->partner->getInfo();
        }
        catch(Kaltura_Client_Exception $ex)
        {
            Kms_Log::log(self::UPGRADE_EXCEPTION_WRONG_PARTNER_CREDENTIALS .' with exception '.$ex->getMessage(), Kms_Log::CRIT);
            throw new Kms_Setup_Exception(self::UPGRADE_EXCEPTION_WRONG_PARTNER_CREDENTIALS);
        }
        return true;
    }

    /**
     * method to validate whether a category->fullName from KMS2 or 3 can be used (or actually, was ported to ) KMS4
     * prior to upgrading to KMS4, existing KMS tree must be moved manually to comply with the "root>site>galleries" requirement
     * 
     * @param string $fullName
     * @return mixed returns FALSE for invalid root category or string for the relative root for KMS4
     */
    public static function validateMigrationCategoryFullName($fullName)
    {
        $matches = array();
        $pattern = '/(.*)\>site\>galleries$/';
        if(preg_match($pattern, $fullName, $matches) && !empty($matches[1]))
        {
            return $matches[1];
        }
        else
        {
            return false;
        }
    }
}