<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
*  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Helper class to get metadata from the api, and encapsulate its parsing 
 * 
 * @author talbone
 *
 */
class Kms_Helper_Metadata
{
    
    private static $_customdataXsd = array();
    private static $_customdataProfileByType = array();
    
    
    /**
     * get the values from a metadata entry according to the fields
     * 
     * @param unknown_type $customData
     * @param unknown_type $customDataFields
     */
    public static function getCustomdataValues($customData, $customDataFields)
    {
        $customdataValues = null;
        if (isset($customData->xml) && $customData->xml)
        {
            $metadataXML = simplexml_load_string($customData->xml);
            $json = json_encode($metadataXML);
            $customdataValues = json_decode($json, TRUE);
    
            // change the index on multiple fields
            foreach ($customdataValues as $key => $value)
            {
                if (isset($customDataFields[$key]) && $customDataFields[$key]['isMulti'])
                {
                    if ($customDataFields[$key]['type'] != 'listType' && is_array($value))
                    {
                        $newVal = array();
                        foreach ($value as $val)
                        {
                            $newId = round(rand(1, 1000) * microtime(true));
                            $newVal[$newId] = $val;
                        }
                        $customdataValues[$key] = $newVal;
                    }
                }
            }
        }
        return $customdataValues;
    }
    
    /***
     *
    * @return string
    */
    public static function getCustomdataXsd($customdataProfileId)
    {
        //$customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        if(is_numeric($customdataProfileId))
        {
            if(!isset(self::$_customdataXsd[$customdataProfileId]))
            {
                $client = Kms_Resource_Client::getAdminClient();
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
                $cacheParams = array('profileId' => $customdataProfileId);
                $customdataProfile = Kms_Resource_Cache::apiGet('customdataprofile', $cacheParams);
                if(!$customdataProfile)
                {
                    try
                    {
                        $customdataProfile = $metadataPlugin->metadataProfile->get($customdataProfileId);
                        Kms_Resource_Cache::apiSet('customdataprofile', $cacheParams, $customdataProfile);
                    }
                    catch(Kaltura_Client_Exception $e)
                    {
                        if ($e->getCode() == 'INVALID_OBJECT_ID')
                        {
                            Kms_Log::log('metadata: customDataProfileId ' . $customdataProfileId . ' does not exist. Check your MediaSpace Config and/ or KMC > Settings > Custom Data.', Kms_Log::CRIT);
                            //                        throw $e;
                        }
                        else
                        {
                            Kms_Log::log('metadata: get Customdata Profile failed: ' . $e->getCode() . ': '.$e->getMessage(),   Kms_Log::CRIT);
                            //                        throw $e;
                        }
                        // return NULL
                        return null;
                    }
                }
                $xsd = $customdataProfile->xsd;
                self::$_customdataXsd[$customdataProfileId] = $xsd;        
                return $xsd;
            }
            else
            {
                return self::$_customdataXsd[$customdataProfileId];
            }
        }
        else
        {
            return null;
        }
    }
    
    /**
     * get the fields from a profile id
     */
    public static function getCustomdataFields($customdataProfileId)
    {
        // initialize the array that we will return
        $customDataFields = array();
        $xsdFromApi = self::getCustomdataXsd($customdataProfileId);
        if(!is_null($xsdFromApi))
        {
            // initialize the simplexml element from the XSD xml we get from Kaltura
            $xsdElement = new SimpleXMLElement($xsdFromApi);
    
            // initiate the "xsd:" namespace
            $xsd = $xsdElement->children('http://www.w3.org/2001/XMLSchema');
    
            // get a collection of elements from the XSD
            $fieldsCollection = $xsd->element->complexType->sequence->element;
    
            // iterate over the elements in the XSD
            foreach($fieldsCollection as $fieldElement)
            {
                // get the field name
                $title = $fieldElement->attributes()->name;
    
                // check if the field can receive multiple values
                $isMulti = ((string) $fieldElement->attributes()->maxOccurs == '1') ? false : true;
    
                // get the label (called "key" for some reason) (under annotation->appinfo->key)
                $label = $fieldElement->annotation
                && $fieldElement->annotation->appinfo
                && $fieldElement->annotation->appinfo->children() ? $fieldElement->annotation->appinfo->children()->key : null;
                // get the field type attritube
                $type = $fieldElement->attributes()->type;
    
                // if the field has no type attribute, check for a list type
                $listValues = array();
                if(is_null($type))
                {
                    if($fieldElement->simpleType
                    && $fieldElement->simpleType->restriction
                    && $fieldElement->simpleType->restriction->enumeration)
                    {
                        // list definition exists, parse the values from the xml
                        $listElement = $fieldElement->simpleType->restriction->enumeration;
                        foreach($listElement as $elem)
                        {
                            $listval = (string) $elem->attributes()->value;
                            $listValues[$listval] = $listval;
                        }
                        // set the type
                        $type = $fieldElement->simpleType->restriction->attributes()->base;
                    }
                    else
                    {
                        // leave type as null (field will be ignored)
                        $type = null;
                    }
                }
    
                //          $isMulti = $fieldElement->
    
    
                $customDataFields{ (string) $title } = array(
                        'label' => (string) $label,
                        'type' => (string) $type,
                        'isMulti' => $isMulti,
                        'listValues' => $listValues,
                );
    
    
            }
            return $customDataFields;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * get the list of customdata profiles objects for the partner
     */
    public static function getCustomdataProfilesObjects($objectType = NULL)
    {
    	$filter = new Kaltura_Client_Metadata_Type_MetadataProfileFilter();
    	if ($objectType)
    	{
    		$filter->metadataObjectTypeEqual = $objectType;
    	}

    	$cacheParams = Kms_Resource_Cache::buildCacheParams($filter);

    	$result = Kms_Resource_Cache::apiGet('customdata_profiles', $cacheParams);
    	if(!$result)
    	{
    		$client = Kms_Resource_Client::getAdminClient();
    		$metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
    		$result = $metadataPlugin->metadataProfile->listAction($filter);
    		Kms_Resource_Cache::apiSet('customdata_profiles', $cacheParams, $result);
    	}

    	return $result;
    }
    
    
    /**
     * get the list of customdata profiles names for the partner
     */
    public static function getCustomdataProfiles($objectType = NULL)
    {
        if(!isset(self::$_customdataProfileByType[$objectType]))
        {
            $result = self::getCustomdataProfilesObjects($objectType);
            $ret = array();
            if($result && isset($result->objects) && count($result->objects))
            {
                foreach($result->objects as $profile)
                {
                    $ret[$profile->id] = $profile->id.': '.$profile->name;
                }
            }
            self::$_customdataProfileByType[$objectType] = $ret;

            return $ret;
        }
        else
        {
            return self::$_customdataProfileByType[$objectType];
        }
    }
}