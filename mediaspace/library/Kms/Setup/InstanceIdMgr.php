<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Class to manage instance IDs validation and update
 *
 * @author gonen
 */
class Kms_Setup_InstanceIdMgr
{
    const PROFILE_DEPLOYMENT_IDENTIFIER = 'PartnerInstancesProfile'; // identifier of the profile (in deployment.ini) that represents the partner metadataprofile that holds instanceIds

    /**
     * method to validate whether a given instance ID was already used
     *
     * @param string $instanceId
     * @param Kaltura_Client_Client $client
     * @return bool
     */
    public static function validateInstanceAvailable($instanceId, $client)
    {
        $iniPath = APPLICATION_PATH .DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'deployment' . DIRECTORY_SEPARATOR . Kms_Setup_Deployment::DEPLOYMENT_INSTALL_MODE_INI;
        $deploymentConfig = parse_ini_file($iniPath, true);
        $deploymentConfig = new Zend_Config_Ini($iniPath, 'metadataProfiles');
        $metadataProfiles = $deploymentConfig->profiles;
        
        $metadataProfile = self::getMetadataProfile($client);
        if($metadataProfile !== false)
        {
            $metadataObject = self::getInstancesMetadata($metadataProfile->id, $client);
            if($metadataObject !== false)
            {
                $used = self::isInstanceIdUsed($metadataObject->xml, $instanceId);
                return (!$used); // if used => not available. if not used => available. hence the NOT
            }
        }
        return true; // no metadata for profile - available
    }

    /**
     * method to get metadata profile object according to partber ID and the predefined identifier
     *
     * @param Kaltura_Client_Client $client
     * @return mixed returns Kaltura_Client_Metadata_Type_ if profile exists or FALSE if does not.
     */
    public static function getMetadataProfile($client)
    {
        $iniPath = APPLICATION_PATH .DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'deployment' . DIRECTORY_SEPARATOR . Kms_Setup_Deployment::DEPLOYMENT_INSTALL_MODE_INI;
        $deploymentConfig = parse_ini_file($iniPath, true);
        $deploymentConfig = new Zend_Config_Ini($iniPath, 'metadataProfiles');
        $metadataProfiles = $deploymentConfig->profiles;
        foreach($metadataProfiles as $profileConfig)
        {
            if(isset($profileConfig->identifier) && $profileConfig->identifier == self::PROFILE_DEPLOYMENT_IDENTIFIER)
            {
                $profile = Kms_Setup_Deployment::metadataProfileExists($profileConfig->systemName, $profileConfig->name, $profileConfig->objectType, $client);
                if($profile !== false)
                {
                    return $profile; // no profile, hence instance ID not used
                }
            }
        }
        return false;
    }

    /**
     * method to get metadata object that contains list of instances used
     *
     * @param int $profileId
     * @param Kaltura_Client_Client $client
     * @return mixed returns Kaltura_Client_Metadata_Type_Metadata if such exists or FALSE
     */
    public static function getInstancesMetadata($profileId, $client)
    {
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $metadataFilter = new Kaltura_Client_Metadata_Type_MetadataFilter();
        $metadataFilter->metadataProfileIdEqual = $profileId;
        $metadataFilter->metadataObjectTypeEqual = 4; // object type of partner
        $metadataFilter->objectIdEqual = $client->getConfig()->partnerId;
        $metadataObjects = $metadataPlugin->metadata->listAction($metadataFilter);
        if($metadataObjects->totalCount)
        {
            return $metadataObjects->objects[0];
        }
        else
        {
            return false;
        }
    }

    /**
     * method to parse metadata XML to determine if a given instance ID is used or not
     *
     * @param string $xml
     * @param string $instanceId
     * @return bool
     */
    public static function isInstanceIdUsed($xml, $instanceId)
    {
        if(trim($xml))
        {
            $instancesXmlObj = new SimpleXMLElement($xml);
            $instances = self::getInstancesArray($instancesXmlObj);
            if(in_array($instanceId, $instances))
            {
                return true; // instance ID is in use already
            }
        }
        return false;
    }

    /**
     * method to extract array of instance IDs from xml object using xpath
     *
     * @param SimpleXMLElement $xmlObj
     * @return array
     */
    public static function getInstancesArray(SimpleXMLElement $xmlObj)
    {
        $xpath = $xmlObj->xpath("/metadata/InstanceId");
        if(is_array($xpath) && count($xpath))
        {
            return $xpath;
        }
        return array();
    }

    /**
     * method to update metadata to include new instance ID
     *
     * @param Kaltura_Client_Metadata_Type_Metadata $metadataObject
     * @param string $instanceId
     * @param Kaltura_Client_Client $client
     */
    public static function addInstanceId($metadataObject, $instanceId, $client)
    {
        if(trim($metadataObject->xml))
        {
            $xmlObj = new SimpleXMLElement($metadataObject->xml);
            $instances = self::getInstancesArray($xmlObj);
        }
        else
        {
            $instances = array();
        }
        $instancesArray = array();
        foreach($instances as $iID)
        {
            $instancesArray[] = $iID;
        }
        $instancesArray[] = $instanceId;

        $xml = '<metadata>';
        foreach($instancesArray as $id)
        {
            $xml .= '<InstanceId>'.$id.'</InstanceId>';
        }
        $xml .= '</metadata>';

        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $metadataPlugin->metadata->update($metadataObject->id, $xml);
    }

    public static function addFirstInstanceId($metadataProfileId, $instanceId, $client)
    {
        $xml = '<metadata><InstanceId>'.$instanceId.'</InstanceId></metadata>';
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $objectType = 4; // PARTNER
        $metadataObj = $metadataPlugin->metadata->add($metadataProfileId, $objectType, $client->getConfig()->partnerId, $xml);
        Kms_Log::log("created initial metadata object for partner with following XML: $xml . got ID of ".$metadataObj->id);
    }
}