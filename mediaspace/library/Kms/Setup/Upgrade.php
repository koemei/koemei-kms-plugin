<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/*
 * Created on Nov 28, 2011
 *
 */

class Kms_Setup_Upgrade
{
    const MIGRATION_MAPPING_INI = 'config_migrate.ini';
    const TAKE_DEFAULT_FOR_PROPERTY = 'NEW';

    private static $_configMap;
    private static $_configModel;
    private static $_errorPaths;
    private $_oldConfig;
    private $_currentConfigPost;

    private $_tempClient;

    private $_additionalSettings = array();

    private static function init()
    {
        // read config map
        $migrationConfigPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . self::MIGRATION_MAPPING_INI;
        self::$_configMap = new Zend_Config_Ini($migrationConfigPath);

        // check if new configs are writable
        self::$_configModel = new Application_Model_Config();
    }

    function __construct($oldConfigPath)
    {
        if (!file_exists($oldConfigPath))
        {
            Kms_Log::log('install: could not read old config', Kms_Log::WARN);
            throw new Exception('could not read old config');
        }
        $this->_oldConfig = parse_ini_file($oldConfigPath);
        if($this->_oldConfig === false)
        {
            Kms_Log::log('install: could not parse KMS2 config file', Kms_Log::WARN);
            throw new Exception('could not parse KMS2 config file');
            // cannot touch empty config file
        }

        Kms_Resource_Client::setConfigValue(Kms_Resource_Client::CLIENT_PARTNER_ID, $this->_oldConfig['partnerId']);
        Kms_Resource_Client::setConfigValue(Kms_Resource_Client::CLIENT_ADMIN_SECRET, $this->_oldConfig['adminSecret']);
        $this->_tempClient = Kms_Resource_Client::getAdminClientNoEntitlement();
        $this->_tempClient->getConfig()->serviceUrl = $this->_oldConfig['serviceUrl'];

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

    public static function checkWritePermissionsForMigration()
    {
        self::init();
        if (!self::checkMainConfigPathWritable())
        {
            $missingPermission = true;
            // cannot perform migration
            self::$_errorPaths[] = Kms_Setup_Common::getMainConfigFilePath();
        }

        $missingPermission = false;
        foreach (self::$_configMap as $sectionName => $config)
        {
            if (strpos($sectionName, 'module-') === false)
                continue;

            if (!self::$_configModel->checkWritable(str_replace('module-', '', $sectionName)))
            {
                $missingPermission = true;
                self::$_errorPaths[] = self::$_configModel->getConfigFileName(str_replace('module-', '', $sectionName));
            }
        }
        if ($missingPermission)
        {
            Kms_Log::log('install: missing permissions', Kms_Log::WARN);
            throw new Exception("missing permissions");
        }
    }

    public static function getErrorPaths()
    {
        return self::$_errorPaths;
    }

    public static function createEmptyConfigFile()
    {
        $res = touch(Kms_Setup_Common::getMainConfigFilePath());
        return $res;
    }

    private static function checkMainConfigPathWritable()
    {
        $globalConfigPath = realpath(dirname(Kms_Setup_Common::getMainConfigFilePath()));
        return is_writable($globalConfigPath);
    }

    public function upgrade($additionalSetings = array())
    {
        $this->_additionalSettings = $additionalSetings;
        Kms_Log::log('setting additional settings to :'.print_r($additionalSetings, true), Kms_Log::DUMP);
        foreach (self::$_configMap as $key => $config)
        {
            $sectionName = str_replace('module-', '', $key);
            $this->handleSection($sectionName, $config);
            Kms_Resource_Config::reInitConfig(Kms_Setup_Common::getMainConfigFilePath());
            Kms_Resource_Client::reInitClients();
        }

        Kms_Setup_Common::initCacheConfigFile();
    }

    private function handleSection($sectionName, $config)
    {
        Kms_Log::log('migration: handling section '.$sectionName, Kms_Log::DEBUG);
        $this->_currentConfigPost = array();
        foreach ($config as $kms3Prop => $kms2Prop)
        {
            // check that new KMS3 property has default value in default.ini 
            //   - otherwise exception is thrown - this case should be caught during dev
            if ($kms2Prop == self::TAKE_DEFAULT_FOR_PROPERTY)
            {
                $this->newPropertyHasDefault($sectionName, $kms3Prop);
            }
            // special logic should be executed to port settings
            elseif (strpos($kms2Prop, '_run') == 0 && method_exists($this, $kms2Prop))
            {
                $this->$kms2Prop();
            }
            // no special logic needed - just verify if setting exists on KMS2 config
            elseif (isset($this->_oldConfig[$kms2Prop]))
            {
                $this->_currentConfigPost[$kms3Prop] = $this->_oldConfig[$kms2Prop];
            }
            else
            {
                /*                 * * by default don't add it to configuration. assume this is optional setting that application will not crash if does not exist ** */
                // is not set on old, but is not new setting - just make sure it has default value in defaults.ini
                //   - otherwise exception is thrown - this case should be caught during dev
                //default:
                //    $this->newPropertyHasDefault($sectionName, $kms3Prop);
            }
        }

        Kms_Log::log('Checking is section '. $sectionName .' is in additional settings '.print_r($this->_additionalSettings ,true), Kms_Log::DUMP);
        if(isset($this->_additionalSettings[$sectionName]))
        {
            Kms_Log::log('found section '. $sectionName .' in additional settings ', Kms_Log::DUMP);
            foreach($this->_additionalSettings[$sectionName] as $configKey => $configValue)
            {
                Kms_Log::log('adding config '. $configKey .' with value '.$configValue, Kms_Log::DUMP);
                $this->_currentConfigPost[$configKey] = $configValue;
            }
            Kms_Log::log('current config post is '. print_r($this->_currentConfigPost, true), Kms_Log::DUMP);
        }

        self::$_configModel->saveConfig($sectionName, $this->_currentConfigPost);
    }

    private function newPropertyHasDefault($section, $property)
    {
        $value = Kms_Resource_Config::getDefaultConfiguration($section, $property);
        if (is_null($value))
        {
            throw new Zend_Exception("missing default value in default.ini for property $property under section $section");
        }

        // default value exists in default.ini - doesn't need to be specifically set on config.ini
        return true;
    }

    private function _runHeaderMenu()
    {
        $countItems = 1;
        if (!isset($this->_oldConfig['headerMenu']) || !count($this->_oldConfig['headerMenu']))
        {
            $this->_currentConfigPost['enabled'] = '0';
        }
        else
        {
            foreach ($this->_oldConfig['headerMenu'] as $headerTopMenuItem)
            {
                $configProperty = "menu.$countItems";
                $oldMenuItem = $this->explodeToKeyValArray($headerTopMenuItem, ',', '=');
                $simpleTypes = array('my_media', 'my_pl', 'url');
                if (in_array($oldMenuItem['type'], $simpleTypes))
                {
                    $this->addKeyValueArray($configProperty, $oldMenuItem, array('url' => 'items'));
                }
                elseif ($oldMenuItem['type'] == 'menu')
                {
                    $this->_currentConfigPost[$configProperty . '.type'] = 'menu';
                    $this->_currentConfigPost[$configProperty . '.label'] = $oldMenuItem['label'];
                    $this->buildSubHeaderMenu($oldMenuItem['items'], $configProperty . '.items');
                }

                $countItems++;
            }
        }
    }

    private function buildSubHeaderMenu($itemsString, $configPropertyPrefix)
    {
        $counter = 1;
        $oldMenuItems = explode(';', $itemsString);

        foreach ($oldMenuItems as $menuItem)
        {
            $menuParts = explode('|', $menuItem);
            if (count($menuParts) == 2)
            {
                $label = $menuParts[0];
                $link = $menuParts[1];
            }
            else
            {
                $label = $menuParts[0];
                $link = $menuParts[0];
            }

            $this->_currentConfigPost[$configPropertyPrefix . ".$counter.label"] = $label;
            $this->_currentConfigPost[$configPropertyPrefix . ".$counter.link"] = $link;
            $counter++;
        }
    }

    private function explodeToKeyValArray($string, $pairSeparator, $keyValueSeparator)
    {
        $return = array();
        $pairs = explode($pairSeparator, $string);
        foreach ($pairs as $keyValue)
        {
            $parts = explode($keyValueSeparator, $keyValue);
            if (count($parts) == 2)
            {
                list($key, $value) = $parts;
            }
            else
            {
                $key = $value = $parts[0];
            }
            $return[$key] = $value;
        }
        return $return;
    }

    private function addKeyValueArray($propertyPrefix, $arrKeyValues, $arrTypeKeyToSkip = array(), $replaceKeys = array())
    {
        $type = (isset($arrKeyValues['type'])) ? $arrKeyValues['type'] : false;
        foreach ($arrKeyValues as $key => $value)
        {
            if ($type && array_key_exists($type, $arrTypeKeyToSkip) && $key == $arrTypeKeyToSkip[$type])
                continue;

            if (array_key_exists($key, $replaceKeys))
            {
                $configProperty = $propertyPrefix . '.' . $replaceKeys[$key];
            }
            else
            {
                $configProperty = $propertyPrefix . '.' . $key;
            }
            $this->_currentConfigPost[$configProperty] = (string) $value;
        }
    }

    private function _runPre()
    {
        $countItems = 1;
        if (!isset($this->_oldConfig['pre']))
            return;
        foreach ($this->_oldConfig['pre'] as $preItem)
        {
            $this->addKeyValueArray("pre.$countItems", $this->explodeToKeyValArray($preItem, ',', '='), array('my_media' => 'value'));
            $countItems++;
        }
    }

    private function _runPost()
    {
        $countItems = 1;
        if (!isset($this->_oldConfig['post']))
            return;
        foreach ($this->_oldConfig['post'] as $preItem)
        {
            $this->addKeyValueArray("post.$countItems", $this->explodeToKeyValArray($preItem, ',', '='), array('my_media' => 'value'));
            $countItems++;
        }
    }

    private function _runRestrictedCategories()
    {
        $counter = 1;
        if (!isset($this->_oldConfig['restrictedCategoriesRoles']))
            return;
        foreach ($this->_oldConfig['restrictedCategoriesRoles'] as $restrictedCategoriesRow)
        {
            $configProperty = 'restricted.' . $counter;
            $restrictedCategories = $this->explodeToKeyValArray($restrictedCategoriesRow, ',', '=');
            $oldCategory = null;
            foreach ($restrictedCategories as $category => $roles)
            {
                // this is a hack if users edited config and entered something like:
                //   restrictedCategoriesRoles[] = "Category1=PrivateUploads|PublicUploads,Category3=me|you"
                // according to explanation in KMS2 config-mngmnt
                if (!is_null($oldCategory) && $oldCategory != $category)
                {
                    $counter++;
                    $configProperty = 'restricted.' . $counter;
                }
                $this->_currentConfigPost[$configProperty . ".category"] = $category;
                $rolesArr = explode('|', $roles);
                foreach ($rolesArr as $roleKey => $role)
                {
                    $roleKeyValue = $roleKey + 1;
                    $this->_currentConfigPost[$configProperty . ".roles." . $roleKeyValue] = $role;
                }
                $oldCategory = $category;
            }
            $counter++;
        }
    }

    private function _runSortMediaBy()
    {
        if (!isset($this->_oldConfig['sortMediaBy']))
            return;
        if ($this->_oldConfig['sortMediaBy'] == 'most_viewed')
        {
            $this->_currentConfigPost['sortMediaBy'] = 'views';
        }
        elseif ($this->_oldConfig['sortMediaBy'] == 'alphabetical')
        {
            $this->_currentConfigPost['sortMediaBy'] = 'name';
        }
        elseif ($this->_oldConfig['sortMediaBy'] == 'recent')
        {
            $this->_currentConfigPost['sortMediaBy'] = 'recent';
        }
        else
        {
            $this->_currentConfigPost['sortMediaBy'] = $this->_oldConfig['sortMediaBy'];
        }
    }

    /*     * * customdata module configurations ** */

    private function _runEnableCustomdata()
    {
        $kms2MetadataProfile = (isset($this->_oldConfig['metadataProfileId'])) ? $this->_oldConfig['metadataProfileId'] : false;
        if ($kms2MetadataProfile !== false && is_numeric($kms2MetadataProfile))
        {
            $this->_currentConfigPost['enabled'] = "1";
        }
        else
        {
            $this->_currentConfigPost['enabled'] = "0";
        }
    }

    private function getDefaultValueForProperty($tab, $property)
    {
        $configAdmin = self::$_configModel->getTabConfigs($tab);
        if (isset($configAdmin[$property]) && isset($configAdmin[$property]['default']))
        {
            return $configAdmin[$property]['default'];
        }

        return "";
    }

    private function _runCustomdataDateFormat()
    {
        $this->_currentConfigPost['dateFormat'] = $this->getDefaultValueForProperty('customdata', 'dateFormat');
    }

    private function _runCustomdataJsDateFormat()
    {
        $this->_currentConfigPost['jsDateFormat'] = $this->getDefaultValueForProperty('customdata', 'jsDateFormat');
    }

    private function _runCustomdataRequiredFields()
    {
        if (!isset($this->_oldConfig['customdataRequiredFields']))
            return;
        $requiredFields = $this->_oldConfig['customdataRequiredFields'];
        foreach ($requiredFields as $key => $fieldSysName)
        {
            $this->_currentConfigPost['requiredFields.' . $key] = $fieldSysName;
        }
    }

    private function _runCustomdataPrivateFields()
    {
        if (!isset($this->_oldConfig['customdataPrivateFields']))
            return;
        $requiredFields = $this->_oldConfig['customdataPrivateFields'];
        foreach ($requiredFields as $key => $fieldSysName)
        {
            $this->_currentConfigPost['privateFields.' . $key] = $fieldSysName;
        }
    }

    /*     * * Embed module configurations ** */

    private function _runEnableEmbed()
    {
        // embed module is always enabled.
        // whether embed code is visible or not depends on embedAllowed settings
        $this->_currentConfigPost['enabled'] = "1";
    }

    private function _runEmbedAllowed()
    {
        if (!isset($this->_oldConfig['embedAllowed']))
            return;
        $allowedEmbed = $this->_oldConfig['embedAllowed'];
        foreach ($allowedEmbed as $key => $role)
        {
            $this->_currentConfigPost['embedAllowed.' . $key] = $role;
        }
    }

    private function _runEmbedSkins()
    {
        if (!isset($this->_oldConfig['embedSkins']))
            return;
        $allEmbedSkins = $this->_oldConfig['embedSkins'];
        $counter = 1;
        foreach ($allEmbedSkins as $embedSkin)
        {
            $this->addKeyValueArray('embedSkins.' . $counter, $this->explodeToKeyValArray($embedSkin, ',', '='));
            $counter++;
        }
    }

    private function _runEmbedSizes()
    {
        if (!isset($this->_oldConfig['embedSizes']))
            return;
        if(is_array($this->_oldConfig['embedSizes']))
        {
            rsort($this->_oldConfig['embedSizes']);
        }
        $mapKeyToProperty = array(
            0 => 'large',
            1 => 'medium',
            2 => 'small',
        );
        $allEmbedSizes = $this->_oldConfig['embedSizes'];
        foreach ($allEmbedSizes as $key => $embedSize)
        {
            if(isset($mapKeyToProperty[$key]))
            {
                $this->_currentConfigPost['embedSizes.' . $mapKeyToProperty[$key]] = $embedSize;
            }
        }
    }

    private function _runOEmbedOptions()
    {
        if ($this->_oldConfig['oEmbedOptions'] == 'Set_from_config')
        {
            $this->_currentConfigPost['enableCustomization'] = "0";
        }
        else
        {
            $this->_currentConfigPost['enableCustomization'] = "0";
        }
    }

    /*     * * EmbedPlaylist module configurations ** */

    private function _runEnableEmbedPlaylist()
    {
        // embed module is always enabled.
        // whether embed code is visible or not depends on embedAllowed settings
        $this->_currentConfigPost['enabled'] = "1";
    }

    private function _runEmbedPlaylistAllowed()
    {
        if (!isset($this->_oldConfig['embedAllowed']))
            return;
        $allowedEmbed = $this->_oldConfig['embedAllowed'];
        foreach ($allowedEmbed as $key => $role)
        {
            $this->_currentConfigPost['embedAllowed.' . $key] = $role;
        }
    }

    private function _runEmbedPlaylistSkins()
    {
        if (!isset($this->_oldConfig['embedSkins']))
            return;
        $allEmbedSkins = $this->_oldConfig['embedSkins'];
        $counter = 1;
        foreach ($allEmbedSkins as $embedSkin)
        {
            $this->addKeyValueArray('embedSkins.' . $counter, $this->explodeToKeyValArray($embedSkin, ',', '='));
            $counter++;
        }
    }

    private function _runEmbedPlaylistSizes()
    {
        if (!isset($this->_oldConfig['embedSizes']))
            return;
        $mapKeyToProperty = array(
            0 => 'large',
            1 => 'medium',
            2 => 'small',
        );
        $allEmbedSizes = $this->_oldConfig['embedSizes'];
        foreach ($allEmbedSizes as $key => $embedSize)
        {
            $this->_currentConfigPost['embedSizes.' . $mapKeyToProperty[$key]] = $embedSize;
        }
    }

    private function _runEnableRelated()
    {
        if (!isset($this->_oldConfig['relatedPlaylist']) || $this->_oldConfig['relatedPlaylist'] == 0)
        {
            $this->_currentConfigPost['enabled'] = "0";
        }
        else
        {
            $this->_currentConfigPost['enabled'] = "1";
        }
    }

    private function _runRelatedLimit()
    {
        if (!isset($this->_oldConfig['relatedPlaylist']) || $this->_oldConfig['relatedPlaylist'] == 0)
        {
            $this->_currentConfigPost['limit'] = $this->getDefaultValueForProperty('related', 'limit');
        }
        else
        {
            $this->_currentConfigPost['limit'] = $this->_oldConfig['relatedPlaylist'];
        }
    }

    private function _runRelatedOrderBy()
    {
        if (!isset($this->_oldConfig['sortMediaBy']))
            return;
        if ($this->_oldConfig['sortMediaBy'] == 'most_viewed')
        {
            $this->_currentConfigPost['orderBy'] = 'views';
        }
        elseif ($this->_oldConfig['sortMediaBy'] == 'alphabetical')
        {
            $this->_currentConfigPost['orderBy'] = 'name';
        }
        elseif ($this->_oldConfig['sortMediaBy'] == 'recent')
        {
            $this->_currentConfigPost['orderBy'] = 'recent';
        }
        else
        {
            $this->_currentConfigPost['orderBy'] = $this->_oldConfig['sortMediaBy'];
        }
    }

    private function _runEnableSidePlaylists()
    {
        if (isset($this->_oldConfig['sidePlaylists']))
        {
            $this->_currentConfigPost['enabled'] = "1";
        }
        else
        {
            $this->_currentConfigPost['enabled'] = "0";
        }
    }

    private function _runSidePlaylists()
    {
        if (!isset($this->_oldConfig['sidePlaylists']))
            return;
        $sidePlaylists = $this->_oldConfig['sidePlaylists'];
        $counter = 1;
        foreach ($sidePlaylists as $playlist)
        {
            $this->addKeyValueArray(
                    'items.' . $counter, $this->explodeToKeyValArray($playlist, ',', '='), array(), array('value' => 'id', 'name' => 'label')
            );

            $counter++;
        }
    }

    private function _runSidePlaylistsLimit()
    {
        // in KMS2 - value of relatedPlaylist affects side playlists as well
        if (!isset($this->_oldConfig['relatedPlaylist']) || $this->_oldConfig['relatedPlaylist'] == 0)
        {
            $this->_currentConfigPost['limit'] = $this->getDefaultValueForProperty('sideplaylists', 'limit');
        }
        else
        {
            $this->_currentConfigPost['limit'] = $this->_oldConfig['relatedPlaylist'];
        }
    }

    private function _runSso()
    {

        $this->_currentConfigPost['sso'] = array();

        if (isset($this->_oldConfig['SSOAuth_secret']))
        {
            $this->_currentConfigPost['sso']['secret'] = $this->_oldConfig['SSOAuth_secret'];
        }

        if (isset($this->_oldConfig['SSOAuth_logout_url']))
        {
            $this->_currentConfigPost['sso']['logoutUrl'] = $this->_oldConfig['SSOAuth_logout_url'];
        }

        if (isset($this->_oldConfig['loginUrl']) && $this->_oldConfig['loginUrl'] != 'login.php')
        {
            $this->_currentConfigPost['sso']['loginUrl'] = $this->_oldConfig['loginUrl'];
        }
    }

    private function _runLdap()
    {

        $this->_currentConfigPost['ldapServer'] = array();
        $this->_currentConfigPost['ldapGroups'] = array();
        $this->_currentConfigPost['ldapOptions'] = array();

        if (isset($this->_oldConfig['ldap_server']))
        {
            $this->_currentConfigPost['ldapServer']['host'] = $this->_oldConfig['ldap_server'];
        }

        if (isset($this->_oldConfig['ldap_port']))
        {
            $this->_currentConfigPost['ldapServer']['port'] = $this->_oldConfig['ldap_port'];
        }

        if (isset($this->_oldConfig['ldap_protocol']))
        {
            $this->_currentConfigPost['ldapServer']['protocol'] = $this->_oldConfig['ldap_protocol'];
        }

        if (isset($this->_oldConfig['ldap_protocol_version']))
        {
            $this->_currentConfigPost['ldapServer']['protocolVersion'] = $this->_oldConfig['ldap_protocol_version'];
        }

        if (isset($this->_oldConfig['ldap_base_dn']))
        {
            $this->_currentConfigPost['ldapServer']['baseDn'] = $this->_oldConfig['ldap_base_dn'];
        }

        $this->_currentConfigPost['ldapServer']['bindMethod'] = 'search';
        if (isset($this->_oldConfig['ldap_search_user_dn']))
        {
            $this->_currentConfigPost['ldapServer']['searchUser']['username'] = $this->_oldConfig['ldap_search_user_dn'];
        }

        if (isset($this->_oldConfig['ldap_search_user_password']))
        {
            $this->_currentConfigPost['ldapServer']['searchUser']['password'] = $this->_oldConfig['ldap_search_user_password'];
        }

        if (isset($this->_oldConfig['ldap_group_for_adminRole']))
        {
            $this->_currentConfigPost['ldapGroups']['adminRole'] = $this->_oldConfig['ldap_group_for_adminRole'];
        }

        if (isset($this->_oldConfig['ldap_group_for_viewerRole']))
        {
            $this->_currentConfigPost['ldapGroups']['viewerRole'] = $this->_oldConfig['ldap_group_for_viewerRole'];
        }

        if (isset($this->_oldConfig['ldap_group_for_privateOnlyRole']))
        {
            $this->_currentConfigPost['ldapGroups']['privateOnlyRole'] = $this->_oldConfig['ldap_group_for_privateOnlyRole'];
        }

        if (isset($this->_oldConfig['ldap_group_for_unmoderatedAdminRole']))
        {
            $this->_currentConfigPost['ldapGroups']['unmoderatedAdminRole'] = $this->_oldConfig['ldap_group_for_unmoderatedAdminRole'];
        }

        if (isset($this->_oldConfig['ldap_groups_matching_order']))
        {
            $this->_currentConfigPost['ldapOptions']['groupsMatchingOrder'] = $this->_oldConfig['ldap_groups_matching_order'];
        }

        if (isset($this->_oldConfig['ldap_user_search_query_pattern']))
        {
            //$this->_currentConfigPost['ldapOptions']['byGroup']['userSearchQueryPattern'] = $this->_oldConfig['ldap_user_search_query_pattern']; // this is not supported in KMS2 so no point in migrating it
            $this->_currentConfigPost['ldapServer']['searchUser']['userSearchQueryPattern'] = $this->_oldConfig['ldap_user_search_query_pattern'];
        }

        if (isset($this->_oldConfig['ldap_group_search_query_pattern']))
        {
            $this->_currentConfigPost['ldapOptions']['byGroup']['groupSearchQueryPattern'] = $this->_oldConfig['ldap_group_search_query_pattern'];
        }

        if (isset($this->_oldConfig['ldap_group_search_each_group_pattern']))
        {
            $this->_currentConfigPost['ldapOptions']['byGroup']['groupSearchEachGroupPattern'] = $this->_oldConfig['ldap_group_search_each_group_pattern'];
        }

        if (isset($this->_oldConfig['ldap_group_search_query']))
        {
            $this->_currentConfigPost['ldapOptions']['byGroup']['groupSearchQuery'] = $this->_oldConfig['ldap_group_search_query'];
        }

        if (isset($this->_oldConfig['ldap_group_membership_attribute']))
        {
            $this->_currentConfigPost['ldapOptions']['byGroup']['groupMembershipAttribute'] = $this->_oldConfig['ldap_group_membership_attribute'];
        }
    }

    private function runForgotPassword()
    {
        $this->_currentConfigPost['forgotPassword'] = array();

        if (isset($this->_oldConfig['forgotLoginLink']))
        {
            $this->_currentConfigPost['forgotPassword']['link'] = $this->_oldConfig['forgotLoginLink'];
        }
        if (isset($this->_oldConfig['forgotLoginEmailSubj']))
        {
            $this->_currentConfigPost['forgotPassword']['emailSubject'] = $this->_oldConfig['forgotLoginEmailSubj'];
        }
        if (isset($this->_oldConfig['forgotLoginEmailBody']))
        {
            $this->_currentConfigPost['forgotPassword']['emailBody'] = $this->_oldConfig['forgotLoginEmailBody'];
        }
        if (isset($this->_oldConfig['loginReminderEmailSubject']))
        {
            $this->_currentConfigPost['forgotPassword']['reminderSubject'] = $this->_oldConfig['loginReminderEmailSubject'];
        }
        if (isset($this->_oldConfig['loginReminderEmailBody']))
        {
            $this->_currentConfigPost['forgotPassword']['reminderBody'] = $this->_oldConfig['loginReminderEmailBody'];
        }
    }

    private function _runAuthClass()
    {
        switch ($this->_oldConfig['authClass'])
        {
            case 'KalturaAuth':
                $this->_currentConfigPost['authNAdapter'] = 'Kms_Auth_AuthN_Kaltura';
                $this->_currentConfigPost['authZAdapter'] = 'Kms_Auth_AuthZ_Kaltura';
                break;

            case 'LdapAuth':
                $this->_currentConfigPost['authNAdapter'] = 'Kms_Auth_AuthN_Ldap';
                $this->_currentConfigPost['authZAdapter'] = 'Kms_Auth_AuthZ_Ldap';
                break;

            case 'SSOAuth':
                $this->_currentConfigPost['authNAdapter'] = 'Kms_Auth_AuthN_Sso';
                $this->_currentConfigPost['authZAdapter'] = 'Kms_Auth_AuthZ_Sso';
                break;

            case 'ShibbolethAuth':
                $this->_currentConfigPost['authNAdapter'] = 'Kms_Auth_AuthN_Shibboleth';
                $this->_currentConfigPost['authZAdapter'] = 'Kms_Auth_AuthZ_Shibboleth';
                break;

            default:
                Kms_Log::log('authentication class unidentified. settings will most likely not work in this version of KMS.', Kms_Log::CRIT);
                $this->_currentConfigPost['authNAdapter'] = $this->_oldConfig['authClass'];
                $this->_currentConfigPost['authZAdapter'] = $this->_oldConfig['authClass'];
        }
    }

    private function _runRootCategory()
    {
        $rootCat = $this->_oldConfig['rootCategory'];

        // take first category from "something>site>galleries" and use as root
        $newRootCategory = Kms_Setup_Common::validateMigrationCategoryFullName($rootCat);
        if($newRootCategory === false)
        {
            throw new Kms_Setup_Exception('Cannot complete migration. rootCategory does not comply with KMS4 requirements.');
        }
        else
        {
            $this->_currentConfigPost['rootCategory'] = $newRootCategory;
        }
    }
}