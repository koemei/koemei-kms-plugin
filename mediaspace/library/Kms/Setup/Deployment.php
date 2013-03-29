<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * 
 * Used to deploy the ui confs
 * @author Roni C - migrated to KMSv3 by Gonen
 *
 */
class Kms_Setup_Deployment
{
    const DEPLOYMENT_INSTALL_MODE_INI = 'deployment.ini';
    const DEPLOYMENT_MIGRATE_MODE_INI = 'deployment-migrate.ini';
    const DEPLOYMENT_MIGRATE_MODE_3_TO_4 = 'deployment-migrate-3.ini';
    const DEPLOYMENT_MODE_INSTALL = 'install';
    const DEPLOYMENT_MODE_MIGRATE = 'migrate';

    const KALTURA_SERVER_VERSION_DRAGONFLY = 'DF';
    const KALTURA_SERVER_VERSION_EAGLE = 'E';
    const KALTURA_SERVER_VERSION_CASSIOPEIA = 'C';
    const KALTURA_SERVER_VERSION_FALCON = 'F';
    const KALTURA_SERVER_VERSION_UNKNOWN = 'UNKNOWN';

    /**
     * 
     * The default tags for the ui confs
     * @var string
     */
    public static $defaultTags = '';

    /**
     * 
     * The base tag for the config file
     * @var string
     */
    public static $baseTag = "";

    /**
     * 
     * The ini path for the deployment
     * @var unknown_type
     */
    public static $iniPath;

    /**
     * 
     * The tag search array
     * @var array<>
     */
    public static $tags_search = array();

    /**
     * 
     * the partner id for the ui conf deployment
     * @var int
     */
    public static $partnerId;

    /**
     * the version of Kaltura server - can be one of the KALTURA_SERVER_VERSION constants in this class
     * this is set with value during init
     * @var string
     */
    private static $_kalturaServerVersion;

    /**
     * 
     * the creation mode for the ui conf deployment (currentlly defaulted to 3)
     * This means these uiconfs will not be editable through studio
     * @var int
     */
    public static $creationMode = 2;

    /**
     * the deployment configuration object instatiated out of the deployment[-migrate].ini file
     * @var Zend_Config
     */
    private static $_confObj;

    /**
     * an associative array lists all the created uiconfs/profiles.
     * the key is the identifier and the value is the ID
     *
     * @var array
     */
    private static $_createdUiConfs = array();

    /**
     * an associative array lists all the created uiconfs/profiles with the entire .
     * the key is the identifier and the value is the entire uiconf object
     * for debugging purposes
     *
     * @var array
     */
    private static $_createdUiConfsDebug = array();

    
    /**
     * a kaltura client object to perform API calls
     * @var Kaltura_Client_Client
     */
    private static $_client;

    /**
     * mapping of identifier to path in KMS3 config where the value should be saved
     * @var array
     */
    private static $_identifierToConfigMap = array(
        'MainPlayerUIConfID' => array('section' => 'player', 'key' => 'playerId'),
        'DarkEmbedPlayerUIConfID' => array('section' => 'embed', 'key' => 'embedSkins.1.uiConfId'),
        'LightEmbedPlayerUIConfID' => array('section' => 'embed', 'key' => 'embedSkins.2.uiConfId'),
        'HoverEmbedPlayerUIConfID' => array('section' => 'embed', 'key' => 'embedSkins.3.uiConfId'), // default 2 embed players in embed module of KMS3???
        'DarkHorizontalEmbedPlaylistUIConfID' => array('section' => 'embedplaylist', 'key' => 'embedSkins.dark_horizontal'),
        'LightHorizontalEmbedPlaylistUIConfID' => array('section' => 'embedplaylist', 'key' => 'embedSkins.light_horizontal'),
        'DarkVerticalEmbedPlaylistUIConfID' => array('section' => 'embedplaylist', 'key' => 'embedSkins.dark_vertical'),
        'LightVerticalEmbedPlaylistUIConfID' => array('section' => 'embedplaylist', 'key' => 'embedSkins.light_vertical'),
        'KpwPlayerUIConfID' => array('section' => 'player', 'key' => 'kpwId'),
        'KrecordUIConfID' => array('section' => 'widgets', 'key' => 'krecordId'),
        'KsuUploadUIConfID' => array('section' => 'widgets', 'key' => 'ksuId'),
        'KsrUiconfId' => array('section' => 'screencapture', 'key' => 'ksrId'),
        'KvpmDocumentUploadUIConfID' => array('section' => 'widgets', 'key' => 'kvpmDocUploadId'),
        'KsuCaptionUploadUIConfID' => array('section' => 'captions', 'key' => 'captionsKsuId'),
        'KvpmCreateUIConfID' => array('section' => 'widgets', 'key' => 'kvpmCreationId'),
        'UserRolesSchema' => array('section' => 'application',  'key' => 'userRoleProfile'),
        'ChannelThumbnailProfileId' => array('section' => 'channels',  'key' => 'channelThumbnailProfileId'),
        'ChannelCommentsProfileId' => array('section' => 'comments',  'key' => 'channelCommentsProfileId'),
        'EntryCommentsProfileId' => array('section' => 'comments',  'key' => 'entryCommentsProfileId'),
		'EntryCommentsCountProfileId' => array('section' => 'comments',  'key' => 'entryCommentsCountProfileId'),
        'IntercallWebcastProfileId' => array('section' => 'webcast',  'key' => 'intercallWebcastProfileId'),
        //'ChannelDetailsProfileId' => array('section' => 'channels',  'key' => 'channelDetailsProfileId'), // we are not creating this schema as it is not needed at the moment. kept for use in the near future
        'FacebookPlayerUIConfID' => array('section' => 'facebook', 'key' => 'fPlayerId'),
		'ChannelSubscriptionProfileId' => array('section' => 'channelsubscription', 'key' => 'channelSubscriptionProfileId'),
    );

    /**
     * replace tokens for replacing ini values
     * @var array
     */
    private static $_iniReplaceTokens = array(
        '{INSTANCE_ID}' => '_funcGetInstanceId',
    );

    /**
     * array to stack view error messages to the installation output
     */
    private static $_deploymentErrors = array();

    /**
     * 
     * deploys the ui conf from the ini file
     */
    public static function deploy()
    {
        //Main Algorithm
        //Here we need to run on the entire config file
        //1. For each section in the config
        //	1.1. Fill all data like swf name, swf url and identifier.
        //	1.2. Foreach widget in this section
        //		1.2.1. Create the uiConf from the xml
        //		1.2.2. Foreach dependencies it has
        //			1.2.2.1. Find the uiCoinf id for this dependency and insert it in the right place
        //			1.2.2.2. save the new ui conf
        //Iterate through all sections (statics, general, kmc, kcw...)
        foreach (self::$_confObj as $sectionName => $sectionValue)
        {
            //If we are in the widgets section (like kmc, kcw, kse)
            if ($sectionName != "general" && count($sectionValue->widgets))
            {
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " identified Kaltura server version " . self::$_kalturaServerVersion, Kms_Log::DEBUG);
                $widgets = self::deployWidgets($sectionValue);
            }
            elseif ($sectionName == 'conversionProfiles')
            {
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " identified Kaltura server version " . self::$_kalturaServerVersion, Kms_Log::DEBUG);
                self::deployConversionProfiles($sectionValue);
            }
            elseif($sectionName == 'metadataProfiles')
            {
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " identified Kaltura server version " . self::$_kalturaServerVersion, Kms_Log::DEBUG);
                self::deployMetadataProfiles($sectionValue);
            }
        }
    }

    /**
     * deploys the uiconfs
     */
    public static function deployWidgets($sectionValue)
    {
        //Set section values
        $baseSwfUrl = $sectionValue->swfPath;
        $swfName = $sectionValue->swfName;
        $objectType = $sectionValue->objectType;
        $createdUiconfs = array();
        //For each widget (in the section)
        foreach ($sectionValue->widgets as $widgetName => $widgetValue)
        {
            $createdUiconf = null;
            //Set widget values
            self::$tags_search[$widgetValue->usage] = $widgetValue->usage;
            $widgetIdentifier = $widgetValue->identifier;

            //Create the ui conf from the xml
            $uiConf = self::populateUiconfFromConfig($widgetValue, $baseSwfUrl, $swfName, $objectType);

            if ($uiConf) //If the ui conf was generated successfully 
            {
                //Then we need to insert the ui conf to the DB (so we can get his id)
                $createdUiconf = self::addUiConfThroughAPI($uiConf);
                $uiconf_id = $createdUiconf->id;
                if (isset(self::$_createdUiConfs[$widgetValue->identifier]))
                {
                    // development/ini error - alert developer
                    Kms_Log::log("deployment: identifier " . $widgetValue->identifier . ' was previously defined', Kms_Log::WARN);
                    throw new Exception("identifier " . $widgetValue->identifier . ' was previously defined');
                }

                // add the created uiconf to the array of all created uiconfs to (1. later populate them into config) and (2. search uiconfs for dependency)
                self::$_createdUiConfs[$widgetValue->identifier] = $createdUiconf->id;
                self::$_createdUiConfsDebug[$widgetValue->identifier] = $createdUiconf;

                // if player uiconf has features file - lets update it with the uiconf ID we just created so it will be editable in the KMC
                if (isset($widgetValue->features))
                {
                    self::updateFeaturesFile($createdUiconf, $uiconf_id, $widgetValue->features_identifier);
                }

                //If the widget has dependencies
                if (isset($widgetValue->dependencies))
                {
                    //Then update him with the dependencies
                    foreach ($widgetValue->dependencies as $dependencyName => $dependencyValue)
                    {
                        Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . '[' . __LINE__ . '] - checking isset dependency of value ' . Kms_Log::printData($dependencyValue), Kms_Log::INFO);
                        if (isset(self::$_createdUiConfs[$dependencyValue])) // If the ui conf id was set already then we can set the dependencies
                        {
                            Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - dependency exists', Kms_Log::INFO);
                            $dependUiConfValue = self::$_createdUiConfs[$dependencyValue];

                            $createdUiconf->confFile = str_replace("@@{$dependencyValue}@@", $dependUiConfValue, $createdUiconf->confFile); // set new value instead of the dependency
                        }
                        else
                        {
                            Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - dependency does not exist', Kms_Log::INFO);
                            Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - ' . Kms_Log::printData($widgetValue->forceDependencyByVersion), Kms_Log::INFO);
                            // allow skipping dependency pending version of Kaltura server which might not support some dependency
                            if (isset($widgetValue->forceDependencyByVersion))
                            {
                                foreach ($widgetValue->forceDependencyByVersion as $forceDependency)
                                {
                                    Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - checking if should force ', Kms_Log::INFO);
                                    Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - ' . Kms_Log::printData($forceDependency), Kms_Log::INFO);
                                    Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - ' . $dependencyValue . ' ... ' . self::$_kalturaServerVersion, Kms_Log::INFO);
                                    if ($forceDependency->dependency == $dependencyValue && $forceDependency->version == self::$_kalturaServerVersion)
                                    {
                                        Kms_Log::log('deployment: ' . __METHOD__ . '[' . __LINE__ . '] - no dependency and should force for that version', Kms_Log::INFO);
                                        throw new Exception("Missing dependency: {$dependencyName} = {$dependencyValue} for widget: {$widgetName}");
                                    }
                                    elseif ($forceDependency->dependency == $dependencyValue && $forceDependency->version != self::$_kalturaServerVersion)
                                    {
                                        self::$_deploymentErrors[] = "Dependency {$dependencyValue} not completed due to incompatible Kaltura server vesion [" . self::$_kalturaServerVersion . "].";
                                        Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " skipping optional dependency due to incompatible server version " . self::$_kalturaServerVersion, Kms_Log::INFO);
                                    }
                                }
                            }
                            else // force dependency
                            {

                                throw new Exception("Missing dependency: {$dependencyName} = {$dependencyValue} for widget: {$widgetName}. force override no set");
                            }
                        }
                    }
                    
                    self::updateUIConfFile($createdUiconf);
                }
            }
            else
            {
                Kms_Log::log("deployment: failed to create uiconf object ($widgetName) due to missing values. check your config.ini", Kms_Log::WARN);
                throw new Exception("failed to create uiconf object ($widgetName) due to missing values. check your config.ini");
            }
            
            if($createdUiconf)
            {
                $createdUiconfs[] = $createdUiconf;
            }
        }
        //Zend_Debug::dump($createdUiconf->id);
        return $createdUiconfs;
        
    }
    
    
    /**
     * method to handle deployment of conversion profiles
     * @param Zend_Config $sectionValue
     */
    public static function deployConversionProfiles($sectionValue)
    {
        // deploy profiles only for eagle due to dependency on systemName on profile objects
        if (self::isKalturaVersionSupported(self::KALTURA_SERVER_VERSION_EAGLE))
        {
            // do profiles (like document conversion) deployment
            foreach ($sectionValue->profiles as $profileName => $profileConfig)
            {
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " deploying profile {$profileConfig->systemName}", Kms_Log::DEBUG);
                if (isset($profileConfig->systemName))
                {
                    $profile = self::profileExists($profileConfig->systemName, $profileConfig->name);
                    if ($profile !== false)
                    {
                        self::$_createdUiConfs[$profileConfig->identifier] = $profile->id;
                        self::$_createdUiConfsDebug[$profileConfig->identifier] = $profile;
                        // get flavorParam ID of swf from exiting profile and add to self::$_createdUiConfs
                        $flavorsWithIdentifiers = self::getFlavorIdentifiersByConversionProfile($profile, $profileConfig->flavorParams);
                        foreach ($flavorsWithIdentifiers as $flavorIdentifier => $flavorId)
                        {
                            self::$_createdUiConfs[$flavorIdentifier] = $flavorId;
                            self::$_createdUiConfsDebug[$flavorIdentifier] = $flavorId;
                        }
                        continue;
                    }
                }
                if (isset($profileConfig->flavorParams) && count($profileConfig->flavorParams))
                {
                    $flavorParams = self::createFlavorParamsFromConfig($profileConfig->flavorParams);
                }
                $profileParams = $profileConfig->flavorParamsIds;
                if (is_array($flavorParams) && count($flavorParams))
                {
                    foreach ($flavorParams as $identifier => $paramId)
                    {
                        self::$_createdUiConfs[$identifier] = $paramId;
                        self::$_createdUiConfsDebug[$identifier] = $paramId;
                        $profileParams .= ',' . $paramId;
                    }
                }
                $profile = self::createConversionProfileFromConfig($profileConfig, $profileParams);
                self::$_createdUiConfs[$profileConfig->identifier] = $profile->id;
                self::$_createdUiConfsDebug[$profileConfig->identifier] = $profile;
            }
        }
    }

    /**
     * method to handle deployment of metadataProfiles section
     * @param Zend_Config_Ini $sectionValue
     */
    public static function deployMetadataProfiles($sectionValue)
    {
        $createdProfiles = array();
        // deploy profiles only for falcon due to dependency on systemName and specific objectType on profile objects
        if (self::isKalturaVersionSupported(self::KALTURA_SERVER_VERSION_FALCON))
        {
            // do metadata profiles deployment
            foreach ($sectionValue->profiles as $profileName => $profileConfig)
            {
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " deploying profile {$profileConfig->systemName}", Kms_Log::DEBUG);
                if (isset($profileConfig->systemName))
                {
                    // some profiles might have dynamic parts in there systemname/name which needs to be replaced:
                    $profileConfig = new Zend_Config($profileConfig->toArray(), true);
                    $profileConfig->systemName = self::replaceIniTokens($profileConfig->systemName);
                    $profileConfig->name = self::replaceIniTokens($profileConfig->name);

                    // go on with checking profile exists and create if needed.
                    $profile = self::metadataProfileExists($profileConfig->systemName, $profileConfig->name, $profileConfig->objectType);
                    if ($profile === false)
                    {
                        $profile = self::createMetadataProfileFromConfig($profileConfig);
                        $createdProfiles[] = $profile;
                    }
                    self::$_createdUiConfs[$profileConfig->identifier] = $profile->id;
                    self::$_createdUiConfsDebug[$profileConfig->identifier] = $profile;
                }
            }
        }
        return $createdProfiles;
    }

    /**
     * method to perform token replacement to support dynamic values from ini
     *
     * @param string $value
     * @return string
     */
    public static function replaceIniTokens($value)
    {
        foreach(self::$_iniReplaceTokens as $token => $replacement)
        {
            if(strpos($value, $token) !== false)
            {
                if(strpos($replacement, '_func') === 0 && is_callable('Kms_Setup_Deployment::'.$replacement))
                {
                    $replacementValue = call_user_func('Kms_Setup_Deployment::'.$replacement);
                    return str_replace($token, $replacementValue, $value);
                }
                else
                {
                    return str_replace($token, $replacement, $value);
                }
            }
        }
        // no match found - return original
        return $value;
    }

    public static function _funcGetInstanceId()
    {
        return Kms_Resource_Config::getInstanceId();
    }

    /**
     * return the array of deployment errors to be displayed in output
     * 
     * @return array
     */
    public static function getDeploymentErrors()
    {
        return self::$_deploymentErrors;
    }
    
    public static function getConfigObject()
    {
        return self::$_confObj;
    }
    

    private static function getFlavorIdentifiersByConversionProfile(Kaltura_Client_Type_ConversionProfile $profile, $flavorsConfig)
    {
        $returnArray = array();

        $flavorParamIds = explode(',', $profile->flavorParamsIds);
        self::$_client->startMultiRequest();
        $flavors = array();
        foreach ($flavorParamIds as $flavorParamId)
        {
            self::$_client->flavorParams->get($flavorParamId);
        }
        $flavors = self::$_client->doMultiRequest();
//        $flavors = self::$_client->flavorParams->getByConversionProfileId($profile->id);



        foreach ($flavors as $flavor)
        {
            $identifier = self::getFlavorIdentifierBySystemName($flavor->systemName, $flavorsConfig);
            if ($identifier)
            {
                $returnArray[$identifier] = $flavor->id;
            }
        }
        return $returnArray;
    }

    /**
     * function to determine whether Kalura server is DF or higher
     */
    public static function getKalturaServerVersion($host)
    {
        $schemaUrl = rtrim($host, '/') . '/api_v3/api_schema.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $schemaUrl);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, '');
        $schemaXml = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        try
        {
            // suppress xml errors
            @libxml_use_internal_errors(true);
            $schemaXmlObj = new SimpleXMLElement($schemaXml);
            if(!($schemaXmlObj instanceof SimpleXMLElement))
            {
                // no xml , so throw exception
                throw new Exception('API version XML is invalid');
            }
            // unsupress the xml errors
            @libxml_use_internal_errors(false);
        }
        catch (Exception $e)
        {
            // missing KalturaClient.xml - probably missconfigured dev server
            Kms_Log::log('deployment: ' . __CLASS__ . ":" . __METHOD__ . " - no schema.xml received. Returning KALTURA_SERVER_VERSION_UNKNOWN.", Kms_Log::WARN);
            Kms_Log::log('deployment: ' . __CLASS__ . ":" . __METHOD__ . " " . $e, Kms_Log::WARN);
            return self::KALTURA_SERVER_VERSION_UNKNOWN;
        }

        // search for class filter on KalturaCategoryUser - if exists, Falcon and up
        $xpath = $schemaXmlObj->xpath("/xml/classes/class[@name='KalturaCategoryUser']");
        if (is_array($xpath) && count($xpath))
        {
            return self::KALTURA_SERVER_VERSION_FALCON;
        }

        // search for systemName filter on ConversionProfile - if exists, Eagle and up
        $xpath = $schemaXmlObj->xpath("/xml/classes/class[@name='KalturaConversionProfileBaseFilter']/property[@name='systemNameEqual']");
        if (is_array($xpath) && count($xpath))
        {
            return self::KALTURA_SERVER_VERSION_EAGLE;
        }

        // not eagle. lets see if dragonfly by searching for permissions API
        $xpath = $schemaXmlObj->xpath("/xml/classes/class[@name='KalturaPermissionItem']");
        if (is_array($xpath) && count($xpath))
        {
            return self::KALTURA_SERVER_VERSION_DRAGONFLY;
        }

        // not DF, probably cassiopeia - lets make sure
        $xpath = $schemaXmlObj->xpath("/xml[@apiVersion='3.1']");
        if (is_array($xpath) && count($xpath))
        {
            return self::KALTURA_SERVER_VERSION_CASSIOPEIA;
        }

        return self::KALTURA_SERVER_VERSION_UNKNOWN;
    }

    private static function isKalturaVersionSupported($minVersion)
    {
        Kms_Log::log("deploy: comparing detected version ".self::$_kalturaServerVersion." to minimum $minVersion", Kms_Log::DEBUG);
        if(self::$_kalturaServerVersion == self::KALTURA_SERVER_VERSION_UNKNOWN)
        {
            // kaltura server could not be identified to specific version - assume supported.
            // comment for "strict" behavior
            return true;

            // kaltura server could not be identified to specific version - assume not supported.
            return false;
        }
        $firstChar = substr(self::$_kalturaServerVersion, 0, 1);
        if(ord($minVersion) <= ord($firstChar))
        {
            return true;
        }

        return false;
    }

    /**
     * 
     */
    private static function profileExists($systemName, $profileName)
    {
        $conversionProfileFilter = new Kaltura_Client_Type_ConversionProfileFilter();
        $conversionProfileFilter->systemNameEqual = $systemName;
        try
        {
            $profiles = self::$_client->conversionProfile->listAction($conversionProfileFilter);
            Kms_Log::log('deployment: ' . __CLASS__ . ":" . __METHOD__ . " profiles from server");
            Kms_Log::log('deployment: ' . Kms_Log::printData($profiles));
            if ($profiles->totalCount > 1 /* && !self::isEmptySystemNames($profiles) */)
            {
                // we can fail here on DF with partner that has more than 1 conversion profile in the account
                throw new Exception("found more than one conversion profile with the same system name");
            }
            if ($profiles->totalCount == 0)
            {
                return false;
            }

            // Kaltura DragonFly on-prem does not have system-name, empty value will be returned
            // we try to compare on name as well assuming we don't change the name of the profile.
            //
            // if both name and system name are different (DF only because systemname is empty) -
            // assume partner doesn't have the required profile - and return false so it will be created
            // the down-side - multiple conversion profiles may be created for the partner
            if ($profiles->objects[0]->systemName != $systemName && $profiles->objects[0]->name != $profileName)
            {
                return false;
            }

            // only one returned, and either systemName is as expected (eagle up) or name is the same (DF up)
            // return profile object
            return $profiles->objects[0];
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to search for conversion profiles on kaltura server " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to search for conversion profiles on kaltura server " . $ex->getMessage());
        }
    }

    /**
     *
     */
    public static function metadataProfileExists($systemName, $profileName, $profileObjectType, $client = null)
    {
        $profileFilter = new Kaltura_Client_Metadata_Type_MetadataProfileFilter();
        $profileFilter->systemNameEqual = $systemName;
        $profileFilter->metadataObjectTypeEqual = $profileObjectType;
        $profileFilter->orderBy = Kaltura_Client_Metadata_Enum_MetadataProfileOrderBy::CREATED_AT_ASC;
        try
        {
            if(!is_null($client) && get_class($client) == 'Kaltura_Client_Client')
            {
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
            }
            else
            {
                $metadataPlugin = Kaltura_Client_Metadata_Plugin::get(self::$_client);
            }
            $profiles = $metadataPlugin->metadataProfile->listAction($profileFilter);
            Kms_Log::log('deployment: ' . __CLASS__ . ":" . __METHOD__ . " profiles from server");
            Kms_Log::log('deployment: ' . Kms_Log::printData($profiles));
            if ($profiles->totalCount > 1 /* && !self::isEmptySystemNames($profiles) */)
            {
                return $profiles->objects[0]; // if fuond more than one, always work witht he oldest
            }
            if ($profiles->totalCount == 0)
            {
                return false;
            }

            // Kaltura DragonFly on-prem does not have system-name, empty value will be returned
            // we try to compare on name as well assuming we don't change the name of the profile.
            //
            // if both name and system name are different (DF only because systemname is empty) -
            // assume partner doesn't have the required profile - and return false so it will be created
            // the down-side - multiple conversion profiles may be created for the partner
            if ($profiles->objects[0]->systemName != $systemName && $profiles->objects[0]->name != $profileName)
            {
                return false;
            }

            // only one returned, and either systemName is as expected (eagle up) or name is the same (DF up)
            // return profile object
            return $profiles->objects[0];
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to search for metadata profiles on kaltura server " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to search for metadata profiles on kaltura server " . $ex->getMessage());
        }
    }

    /**
     * save metadata profile ids in the configuration according to $_identifierToConfigMap
     */
    public static function populateConfigWithDeployedIDs()
    {
        $configPosts = array();
        foreach (self::$_createdUiConfs as $identifier => $id)
        {
            if (array_key_exists($identifier, self::$_identifierToConfigMap))
            {
                if (!isset($configPosts[self::$_identifierToConfigMap[$identifier]['section']]))
                    $configPosts[self::$_identifierToConfigMap[$identifier]['section']] = array();

                if (strpos(self::$_identifierToConfigMap[$identifier]['key'], '.') !== false)
                {
                    $arr = self::convertConfigKeyToArray(self::$_identifierToConfigMap[$identifier]['key'], $id);
                    $configPosts[self::$_identifierToConfigMap[$identifier]['section']] = array_merge_recursive($configPosts[self::$_identifierToConfigMap[$identifier]['section']], $arr);
                    ;
                }
                else
                {
                    $keyName = self::$_identifierToConfigMap[$identifier]['key'];
                    $configValue = $id;
                    $configPosts[self::$_identifierToConfigMap[$identifier]['section']][$keyName] = $id;
                }
            }
        }
        foreach ($configPosts as $section => $config)
        {
            self::saveConfigSection($section, $config);
        }
    }

    public static function convertConfigKeyToArray($keyString, $value)
    {
        $parts = explode('.', $keyString);
        if (count($parts) > 1)
        {
            $substr = substr($keyString, strpos($keyString, '.') + 1);
            return array($parts[0] => self::convertConfigKeyToArray($substr, $value));
        }
        else
        {
            return array($parts[0] => $value);
        }
    }

    /**
     * 
     */
    private static function saveConfigSection($section, $configPost)
    {
        // first lets get the full config (if any)
        $configSection = Kms_Resource_Config::getSection($section);
        if (!$configSection)
        {
            $configSection = Kms_Resource_Config::getModuleSection($section);
        }
        if ($configSection)
        {
            $arrConfigSection = $configSection->toArray();
            $fromConfigSection = new Zend_Config($arrConfigSection, true);
            // populate configPost array with missing values so they wont get dropped on save
            $partialConfigObj = new Zend_Config($configPost, true);
            $fromConfigSection->merge($partialConfigObj);
            $configToWrite = $fromConfigSection->toArray();
        }
        else
        {
            // this may happen if some module files were written to disk and the installation failed and now they got corrupted
            // @todo - can we handle that scenario better?
            throw new Exception("The section $section does not exist. Check that the section names in admin.ini and default.ini match the ones in Deployment.php");
        }

        // save the full configPost
        $configModel = new Application_Model_Config();
        $configModel->saveConfig($section, $configToWrite);
    }

    /**
     * 
     */
    private static function createConversionProfileFromConfig($profileConfig, $flavorParamsIds)
    {
        $conversionProfile = new Kaltura_Client_Type_ConversionProfile();
        $conversionProfile->name = $profileConfig->name;
        $conversionProfile->systemName = $profileConfig->systemName;
        $conversionProfile->flavorParamsIds = $flavorParamsIds;
        $conversionProfile->isDefault = Kaltura_Client_Enum_NullableBoolean::FALSE_VALUE;
        $conversionProfile->status = Kaltura_Client_Enum_ConversionProfileStatus::ENABLED;
        $conversionProfile->description = @$profileConfig->description;

        try
        {
            $profile = self::$_client->conversionProfile->add($conversionProfile);
            Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " conversion profile created");
            Kms_Log::log(Kms_Log::printData($profile));
            return $profile;
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to create conversion profile " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to create conversion profile " . $ex->getMessage());
        }
    }

    /**
     * method to create metadata profile
     *
     * @param type $profileConfig
     */
    private static function createMetadataProfileFromConfig($profileConfig)
    {
        $xsdData = self::readConfFileFromPath($profileConfig->xsd_file);
        if($xsdData !== false)
        {
            $profile = new Kaltura_Client_Metadata_Type_MetadataProfile();
            $profile->name = $profileConfig->name;
            $profile->systemName = $profileConfig->systemName;
            $profile->metadataObjectType = $profileConfig->objectType;
            $profile->createMode = $profileConfig->createMode;

            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get(self::$_client);
            try
            {
                $profile = $metadataPlugin->metadataProfile->add($profile, $xsdData);
                Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " metadata profile created");
                Kms_Log::log(Kms_Log::printData($profile));
                return $profile;
            }
            catch(Exception $ex)
            {
                throw new Exception("failed to create metadata profile with error {$ex->getMessage()}");
            }
        }
        else
        {
            Kms_Log::log("deployment: could not read xsd file in {$profileConfig->xsd_file}");
            throw new Exception("failed to read xsd file in {$profileConfig->xsd_file}");
        }
    }

    /**
     * 
     */
    private static function createFlavorParamsFromConfig($flavorsConfig)
    {
        $createdFlavorsIDs = array();

        $systemNames = array();
        foreach ($flavorsConfig as $key => $flavor)
        {
            if (!isset($flavor->systemName))
            {
                Kms_Log::log("deployment: flavor must be configured with system name to avoid duplications in the Kaltura system", Kms_Log::WARN);
                throw new Exception("flavor must be configured with system name to avoid duplications in the Kaltura system");
            }
            $systemNames[] = $flavor->systemName;
        }
        $existingFlavors = self::flavorsExists($systemNames);

        self::$_client->startMultiRequest();
        foreach ($flavorsConfig as $key => $flavor)
        {
            if ($existingFlavors[$flavor->systemName] !== false)
            {
                $createdFlavorsIDs[$flavor->identifier] = $existingFlavors[$flavor->systemName];

                continue;
            }

            if(!isset($flavor->objectType) || !class_exists($flavor->objectType))
            {
                Kms_Log::log("deployment: flavor must be configured with objectType. systemName of bad flavor is: ".$flavor->systemName, Kms_Log::WARN);
                throw new Exception("Deployment ini has flavorparams row without objectType");
            }
            $flavorParam = new $flavor->objectType;
            $flavorParam->name = $flavor->name;
            $flavorParam->systemName = $flavor->systemName;
            $flavorParam->description = $flavor->description;
            $flavorParam->videoBitrate = $flavor->videoBitrate;
            // 03-25-2012, Gonen: fix a bug to support deployment of json operators when installing on php 5.2.x which:
            //    1. does not allow double-quotes to be escaped in the ini. php 5.3.x handles escaping correctly.
            //    2. operators must include double-quotes in the json data format
            $flavorParam->operators = str_replace("'", '"', $flavor->operators);
            $flavorParam->engineVersion = $flavor->engineVersion;
            $flavorParam->format = $flavor->format;

            // @TODO - add more fields. not required right now for KMS3 doc conversion

            self::$_client->flavorParams->add($flavorParam);
        }
        try
        {
            $createdFlavors = self::$_client->doMultiRequest();
            if (is_array($createdFlavors))
            {
                foreach ($createdFlavors as $createdFlavor)
                {
                    $identifier = self::getFlavorIdentifierBySystemName($createdFlavor->systemName, $flavorsConfig);
                    if ($identifier)
                    {
                        $createdFlavorsIDs[$identifier] = $createdFlavor->id;
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to create flavorParams " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to create flavorParams " . $ex->getMessage());
        }
        return $createdFlavorsIDs;
    }

    private static function getFlavorIdentifierBySystemName($systemName, $flavorsConfig)
    {
        // todo - implement
        foreach ($flavorsConfig as $flavor)
        {
            if (isset($flavor->systemName) && $flavor->systemName == $systemName)
            {
                return $flavor->identifier;
            }
        }
        return null;
    }

    /**
     * 
     */
    private static function flavorsExists($systemNames = array())
    {
        $returnArr = array();

        self::$_client->startMultiRequest();
        foreach ($systemNames as $systemName)
        {
            $returnArr[$systemName] = false;
            $flavorParamsFilter = new Kaltura_Client_Type_FlavorParamsFilter();
            $flavorParamsFilter->systemNameEqual = $systemName;
            $flavors = self::$_client->flavorParams->listAction($flavorParamsFilter);
        }

        try
        {
            $flavorsMulti = self::$_client->doMultiRequest();
            foreach ($flavorsMulti as $flavors)
            {
                if ($flavors->totalCount > 1)
                {
                    Kms_Log::log("deployment: found more than one flavor with the same system name", Kms_Log::WARN);
                    throw new Exception("found more than one flavor with the same system name");
                }
                if ($flavors->totalCount == 1)
                {
                    $returnArr[$flavors->objects[0]->systemName] = $flavors->objects[0]->id;
                }
            }
            return $returnArr;
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to search for flavorParams on kaltura server " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to search for flavorParams on kaltura server " . $ex->getMessage());
        }
    }

    /**
     * 
     * Deprectes old ui confs which have the same Tags.
     * it replaces their tag from autodeploy to deprecated
     * @param string $tag - the tag to depracate
     */
    public static function deprecateOldUiConfs($tag)
    {
        $uiconfFilter = new Kaltura_Client_Type_UiConfFilter();
        $uiconfFilter->tagsMultiLikeAnd = $tag;
        $uiconfPager = new Kaltura_Client_Type_FilterPager();
        $uiconfPager->pageIndex = 1;
        // assume no more than 50 uiconfs for KMS3
        $uiconfPager->pageSize = 50;
        try
        {
            $uiConfs = self::$_client->uiConf->listAction($uiconfFilter);
        }
        catch (Exception $ex)
        {
            // failed to get uiconfs for deprecation
            Kms_Log::log("deployment: failed to get old uiconfs for deprecation " . $ex->getMessage(), Kms_Log::WARN);
        }

        if (isset($uiConf) && isset($uiConfs->objects) && count($uiConfs->objects))
        {
            self::$_client->startMultiRequest();
            //For each uiconf:
            foreach ($uiConfs->objects as $oldUiConf)
            {
                $deprecatedTag = str_replace("autodeploy", "deprecated", $oldUiConf->tags);
                $newUiConf = new Kaltura_Client_Type_UiConf();
                $newUiConf->tags = $deprecatedTag;

                // echo "newTag is:         {$newTag} \nDeprecatedTag is : {$deprecatedTag} for partner ". self::$partnerId . "\n";

                self::$_client->uiConf->update($oldUiConf->id, $newUiConf);
            }

            try
            {
                $updatedUiConfs = self::$_client->doMultiRequest();
            }
            catch (Exception $ex)
            {
                Kms_Log::log("deployment: failed to deprecate old uiconfs " . $ex->getMessage(), Kms_Log::WARN);
                throw new Exception("failed to deprecate old uiconfs " . $ex->getMessage());
            }
        }
    }

    private static function addUiConfThroughAPI(Kaltura_Client_Type_UiConf $uiconf)
    {
        try
        {
            $createdUiConf = self::$_client->uiConf->add($uiconf);
            return $createdUiConf;
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to create uiconf " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to create uiconf " . $ex->getMessage());
        }
    }

    /**
     * 
     * Used to initialize the ui conf deployment like a bootstarp fiel
     * @param unknown_type $conf_file_path
     */
    public static function init($partnerId, $serviceUrl, $mode, $shouldDeprecateOld = false, $originalVersion = Kms_Setup_Common::KNOWN_VERSION_2)
    {
        if ($mode == self::DEPLOYMENT_MODE_INSTALL)
        {
            $iniFileName = self::DEPLOYMENT_INSTALL_MODE_INI;
        }
        else
        {
            if($originalVersion == Kms_Setup_Common::KNOWN_VERSION_2)
            {
                $iniFileName = self::DEPLOYMENT_MIGRATE_MODE_INI;
            }
            else
            {
                $iniFileName = self::DEPLOYMENT_MIGRATE_MODE_3_TO_4;
            }
        }
        Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . " deploying with ini {$iniFileName}");
        self::$iniPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'deployment' . DIRECTORY_SEPARATOR . $iniFileName;
        self::$_confObj = new Zend_Config_Ini(self::$iniPath);


        self::$partnerId = $partnerId;

        self::$_client = Kms_Resource_Client::getAdminClient();
        self::$_client->getConfig()->serviceUrl = $serviceUrl;
        // set timeout to 60 secs for deployment
        self::$_client->getConfig()->curlTimeout = 60;

        self::$_kalturaServerVersion = self::getKalturaServerVersion($serviceUrl);
        Kms_Log::log('deployment: ' . __CLASS__ . "::" . __METHOD__ . '[' . __LINE__ . "] - detected version " . self::$_kalturaServerVersion . " of Kaltura Server $serviceUrl");

        self::$baseTag = self::$_confObj->general->component->name; // gets the application name for the default tags 
        self::$defaultTags = "autodeploy, " . self::$baseTag . "_" . self::$_confObj->general->component->version; // create the uiConf default tags (for ui confs of the application)

        if ($shouldDeprecateOld)
        {
            self::deprecateOldUiConfs(self::$defaultTags);
        }
    }

    /**
     * 
     * Reads the config file from the given path
     * @param string $file_path
     */
    public static function readConfFileFromPath($file_path)
    {
        if (!file_exists($file_path))
        {
            if (!file_exists(dirname(self::$iniPath)))
            {
                return FALSE;
            }
            else
            {
                $file_path = dirname(self::$iniPath) . DIRECTORY_SEPARATOR . $file_path;
            }
        }

        $file_content = file_get_contents($file_path);
        return $file_content;
    }

    /**
     * 
     * Populate the uiconf from the config
     * @param Zend_Config_Ini $widget
     * @param string $baseSwfUrl
     * @param string $swfName
     * @param int $objType
     * @param bool $disableUrlHashing
     */
    public static function populateUiconfFromConfig($widget, $baseSwfUrl, $swfName, $objType)
    {
        $uiconf = new Kaltura_Client_Type_UiConf();

        $confFileContents = self::readConfFileFromPath($widget->conf_file);

        if (!$confFileContents)
        {
            Kms_Log::log("deployment: Unable to read xml file from: {$widget->conf_file}", Kms_Log::WARN);
            throw new Exception("Unable to read xml file from: {$widget->conf_file}");
        }

        $uiconf->confFile = $confFileContents;

        if (isset($widget->features))
        {
            $uiconf->confFileFeatures = self::readConfFileFromPath($widget->features);
        }

        if ($uiconf->confFileFeatures === FALSE)
        {
            // echo "missing features conf file for uiconf {$widget->name}".PHP_EOL; // conf file is a must, features is not.
        }

        //Set values to the ui conf 
        $uiconf->partnerId = self::$partnerId;
        $uiconf->creationMode = self::$creationMode;
        $uiconf->useCdn = true;
        $uiconf->objType = $objType;

        $uiconf->name = $widget->name;
        $uiconf->swfUrl = $baseSwfUrl . $widget->version . '/' . $swfName;
        if ($widget->html5_version)
			$uiconf->html5Url = "/html5/html5lib/".$widget->html5_version."/mwEmbedLoader.php";
        $uiconf->tags = self::$defaultTags . ', ' . self::$baseTag . '_' . $widget->usage;

        $uiconf->width = @$widget->width;
        $uiconf->height = @$widget->height;
        $uiconf->confVars = @$widget->conf_vars;

        return $uiconf;
    }

    /**
     * 
     * updates the uiconf in the API. replacement already done in deploy() to save round-trips in case of multiple dependencies
     * @param uiConf $uiconf
     * @param string $newValue
     * @param string $replacementString
     */
    public static function updateUIConfFile(Kaltura_Client_Type_UiConf $uiconf)
    {
        $newUiConf = new Kaltura_Client_Type_UiConf();
        $newUiConf->confFile = $uiconf->confFile;

        try
        {
            $updatedUiConf = self::$_client->uiConf->update($uiconf->id, $newUiConf);
            return $updatedUiConf->confFile;
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to update uiconf with features " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to update uiconf with features " . $ex->getMessage());
        }
    }

    /**
     * 
     * Updates the player id in the features file
     * @param uiConf $uiconf
     * @param string $uiconfId
     * @param string $replacementString
     */
    public static function updateFeaturesFile(Kaltura_Client_Type_UiConf $uiconf, $uiconfId, $replacementString)
    {
        $newUiConf = new Kaltura_Client_Type_UiConf();
        $newUiConf->confFile = $uiconf->confFile;
        $featuresFile = $uiconf->confFileFeatures;
        $newFeatures = str_replace($replacementString, $uiconfId, $featuresFile);
        $newUiConf->confFileFeatures = $newFeatures;

        try
        {
            $updatedUiConf = self::$_client->uiConf->update($uiconfId, $newUiConf);
        }
        catch (Exception $ex)
        {
            Kms_Log::log("deployment: failed to update uiconf with features " . $ex->getMessage(), Kms_Log::WARN);
            throw new Exception("failed to update uiconf with features " . $ex->getMessage());
        }
    }

    public static function postDeployment()
    {
        // set the selected instanceId as used in partner custom data
        self::updateUsedInstanceId();

        // turn off modules that can not be enabled
        $modulesPaths = Kms_Resource_Config::getModulePaths();
        $configModel = new Application_Model_Config();

        // turn off modules that fail canInstall()
        $modulesForInterface = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Deployable_PreDeployment');
        $modulesForInterface = array_change_key_case($modulesForInterface);

        foreach ($modulesForInterface as $moduleName => $model) {
            if (!$model->canInstall()) {
                Kms_Log::log("deployment: disabling module " . $moduleName . ' for failing canInstall()', Kms_Log::WARN);

                // canInstall() failed - disable the module
                $config['enabled'] = false;        
                $configModel->saveConfig($moduleName, $config);
            }
        }

        // turn off modules that handle the same entry type
        $modulesForInterface = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
        $modulesForInterface = array_change_key_case($modulesForInterface);

        foreach ($modulesForInterface as $moduleName => $model) {
            if (!$model->isHandlingEntryType($model->getMockEntryType())) 
            {
                Kms_Log::log("deployment: disabling module " . $moduleName . ' for not handling own type', Kms_Log::WARN);

                // disable the module
                $config['enabled'] = false;        
                $configModel->saveConfig($moduleName, $config);
            }
            else 
            {
                foreach ($modulesForInterface as $enabledModuleName => $enabledModel) 
                {
                    // prevent comparison with self
                    if ($moduleName != $enabledModuleName) {
                        // check that our prospective module does not handle other enabled modules types
                        if ($model->isHandlingEntryType($enabledModel->getMockEntryType())) 
                        {
                            Kms_Log::log("deployment: disabling module " . $moduleName . '. module ' . $enabledModuleName . ' handles same type.', Kms_Log::WARN);

                            // disable the module
                            $config['enabled'] = false;        
                            $configModel->saveConfig($moduleName, $config);
                        }

                        // check that our prospective module entry type is not handled by the enabled modules
                        if ($enabledModel->isHandlingEntryType($model->getMockEntryType())) 
                        {
                            Kms_Log::log("deployment: disabling module " . $moduleName . '. module ' . $enabledModuleName . ' handles same type.', Kms_Log::WARN);

                            // disable the module
                            $config['enabled'] = false;        
                            $configModel->saveConfig($moduleName, $config);
                        }
                    }
                }
            }
        }
    }

    /**
     * method to set the selected instanceId as used in partner custom data
     * 
     */
    private static function updateUsedInstanceId()
    {
        // get instance ID as set in installation
        $instanceId = Kms_Resource_Config::getInstanceId();
        // try to get profile ID from the list of created uiconfs (actually contains IDs of all objects created, metadata profiles included)
        $metadataProfileId = isset(self::$_createdUiConfs[Kms_Setup_InstanceIdMgr::PROFILE_DEPLOYMENT_IDENTIFIER])? self::$_createdUiConfs[Kms_Setup_InstanceIdMgr::PROFILE_DEPLOYMENT_IDENTIFIER]: false;
        if($metadataProfileId === false)
        {
            // if we dont have profile ID, try to fetch from API
            $profile = Kms_Setup_InstanceIdMgr::getMetadataProfile(self::$_client);
            if($profile === false)
            {
                throw new Exception("partner profile ID not created or not fetched. could not complete setup");
            }
            $metadataProfileId = $profile->id;
        }
        // get the metadata object that contains list of IDs.
        $metadataObject = Kms_Setup_InstanceIdMgr::getInstancesMetadata($metadataProfileId, self::$_client);
        if($metadataObject === false)
        {
            Kms_Setup_InstanceIdMgr::addFirstInstanceId($metadataProfileId, $instanceId, self::$_client);
        }
        else
        {
            if(!Kms_Setup_InstanceIdMgr::isInstanceIdUsed($metadataObject->xml, $instanceId))
            {
                // if instance ID is not used - add it
                Kms_Setup_InstanceIdMgr::addInstanceId($metadataObject, $instanceId, self::$_client);
            }
        }

    }

}
