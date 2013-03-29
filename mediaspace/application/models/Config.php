<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Model_Config
{
    private $_adminConfig;
    private $_configTemplate = array();
    private $_modulesTemplate;
    private $_modulesInfo;
    
    const MODULE_TEMPLATE_FILE = 'admin.ini';
    const MODULE_CONFIG_FILE = 'modules.ini';
    
    public function __construct()
    {
        
        $this->loadTemplates();
        $this->loadModuleTemplates();
        
    }
    

    private function fieldIni2Array($name, $config, $parent = false)
    {
        $field = array();
        
        $field['name'] = $name;
        // set default type to text
        $field['type'] = isset($config->type) ? $config->type : 'text';
        
        // required is 0 by default
        $field['required'] = isset($config->required) ? $config->required : 0;
        
        // comment
        $field['comment'] = isset($config->comment) ? $config->comment : '';
        
        // allow multi - disabled by default
        $field['allowMulti'] = isset($config->allowMulti) ? $config->allowMulti : 0;
        
        // allow custom (for dropdowns)
        $field['allowCustom'] = isset($config->allowCustom) ? $config->allowCustom : 0;
        
        // check if default value exists
        //$field['default'] = isset($config->default) ? $config->default : '';
        
        // check if belongs to a group
        $field['depends'] = isset($config->depends) ? $config->depends : '';
        
        $field['autocomplete'] = isset($config->autocomplete) ? $config->autocomplete : false;
        
        $field['autoValues'] = isset($config->autoValues) ? $config->autoValues : false;
        
        $field['important'] = isset($config->important) ? $config->important : false;
        
        $field['postSaveAction'] = isset($config->postSaveAction) ? $config->postSaveAction : false;

        if($parent)
        {
            $field['parent'] = $parent;
//            $field['belongsTo'] = $parent;
        }
        
        // parse values (for select fields)
        if(isset($config->values) && count($config->values))
        {
            foreach($config->values as $key => $value)
            {
                $label = false;
                // check if labels exist for values
                if(isset($config->labels) && count($config->labels))
                {
                    // check if label for $key exists
                    if(isset($config->labels->$key))
                    {
                        $label = $config->labels->$key;
                    }
                }
                if($label)
                {
                    $field['values'][$value] = $label;
                }
                else
                {
                    $field['values'][$value] = $value;                      
                }
            }
        }
        
        
        
        switch($field['type'])
        {
            case 'text':
            case 'textarea':
            break;
            case 'select':
                break;
            case 'boolean':
                break;
            case 'int':
                break;
            case 'array': 
            case 'object':
                if(isset($config->fields) && count($config->fields))
                {
                    $field['fields'] = array();
                    foreach($config->fields as $subField)
                    {
                        $fieldParent = $parent ? $parent . '/' . $field['name'] : $field['name'];
                        $subFieldName = $subField->name;
                        $field['fields'][$subFieldName] = $this->fieldIni2Array($subFieldName, $subField, $fieldParent);
                    }
                }
                
                break;
            
            default:
                break;
        }
        
        return $field;
    }
    
    
    private function loadTemplates()
    {
        $this->_adminConfig = new Zend_Config_Ini(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'admin.ini');
        
        $newTab = array();
        
        // create tabs
        foreach($this->_adminConfig as $tab => $configs)
        {
            
            $newTab = array();
            
            // run over each field in the tab
            foreach($configs as $key => $config)
            {
                $newTab[$key] = $this->fieldIni2Array($key, $config);
            }
            
            
            $this->_configTemplate[$tab] = $newTab;
        }
        if(Kms_Resource_Config::isSaasEnabled())
        {
            $this->_configTemplate = Kms_Saas_Config::removeAdminConfigurations($this->_configTemplate);
        }
        
    }
    
    /**
     * load all the modules templates
     */
    private function loadModuleTemplates()
    {
        /* parse the module config files */
        
        $modulesPaths = Kms_Resource_Config::getModulePaths();
        
        // run through all module paths and parse the module config file
        foreach ($modulesPaths as $modulesPath){
            
            // run through all module dirs and parse the module config files
            // each module has it's own config file
            
            $dir = new DirectoryIterator($modulesPath);
            foreach($dir as $fileInfo)
            {
                if(!$fileInfo->isDot() && $fileInfo->isDir())
                {
                    $moduleName = $fileInfo->getFilename();
                    
                    // show only available modules (is not SaaS, all are available)
                    if (Kms_Resource_Config::isModuleAvailable($modulesPath, $moduleName))
                    {
                        $this->loadModuleTemplate($fileInfo);
                        $this->loadModuleInfo($fileInfo);
                    }
                }
            }            
        }
                
        if(Kms_Resource_Config::isSaasEnabled())
        {
            $this->_modulesTemplate = Kms_Saas_Config::removeAdminConfigurations($this->_modulesTemplate);
        }
    }
    
    /**
     * load a single module template 
     * @param DirectoryIterator $fileInfo
     */
    private function loadModuleTemplate(DirectoryIterator $fileInfo)
    {
        $moduleName = $fileInfo->getFilename();
        $configFileName = $fileInfo->getPath(). DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . self::MODULE_TEMPLATE_FILE;

        if(file_exists($configFileName))
        {
            $moduleConfiguration = new Zend_Config_Ini($configFileName);

            $this->_modulesTemplate[$moduleName] = array();

            $this->_modulesTemplate[$moduleName]['enabled'] = array(
                    'name' => 'enabled',
                    'default' => '1',
                    'type' => 'boolean',
                    'comment' => 'Enable the '.ucfirst($moduleName).' module.',
                    'allowMulti' => false
            );

            foreach($moduleConfiguration as $key => $configItem)
            {
                $this->_modulesTemplate[$moduleName][$key] = $this->fieldIni2Array($key, $configItem);
            }
        }
    }
    
    /**
     * load the module info file
     * @param DirectoryIterator $fileInfo
     */
    private function loadModuleInfo(DirectoryIterator $fileInfo)
    {
        $moduleName = $fileInfo->getFilename();
        $infoFileName = $fileInfo->getPath(). DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . Kms_Resource_Config::MODULE_INFO_FILE;
        
        if(file_exists($infoFileName))
        {
            $moduleInfo = new Zend_Config_Ini($infoFileName);
        
            $this->_modulesInfo[$moduleName] = array();
        
            foreach($moduleInfo as $key => $configItem)
            {
                $this->_modulesInfo[$moduleName][$key] = $configItem;                
            }
        }
    }
    
    public function getTabConfigs($tab)
    {
        if(!$tab)
        {
            $tab = array_shift(array_keys($this->_configTemplate));
        }
        
        if(isset($this->_configTemplate[$tab]))
        {
            return $this->_configTemplate[$tab];
        }
        elseif(isset($this->_modulesTemplate[$tab]))
        {
            return $this->_modulesTemplate[$tab];
        }
    }
    
    public function getTabInfo($tab = NULL)
    {
        $info = array();
    
        if (!empty($this->_modulesInfo[$tab]))
        {
            $info = $this->_modulesInfo[$tab];
        }
        return $info;
    }
    
    public function listAllConfigs()
    {
        $tabs = $this->getTabs();
        $ret = array();
        foreach($tabs['global'] as $tab)
        {
            $configs = $this->getTabConfigs($tab);
            foreach($configs as $key => $val)
            {
                $configKey = $tab.'/'.$key;
                $ret[] = $configKey;
            }
        }
        foreach($tabs['modules'] as $module => $enabled)
        {
            $configs = $this->getTabConfigs($module);
            foreach($configs as $key => $val)
            {
                $configKey = $module.'/'.$key;
                $ret[] = $configKey;
            }
        }
        return $ret;
    }
    
    
    public function getTabs()
    {
        $moduleTabs = array();
        foreach(array_keys($this->_modulesTemplate) as $module)
        {
            $enabled = Kms_Resource_Config::getModuleConfig($module, 'enabled');
            $moduleTabs[$module] = $enabled;
        }
        
        return array(
            'global' => array_keys($this->_configTemplate),
            'modules' => $moduleTabs,
        );
    }
    
    public function loadConfigs()
    {
//        $this->_configValues = Kms_Resource_Config::getConfigObject();
        
    }
    
    public function getOneField($tab, $field, $parent, $belongsTo, $noLabel)
    {
        $configs = $this->getTabConfigs($tab);
        if($parent)
        {
            $parentsArray = explode('/',$parent);
            
            $currentPath = $configs;
            foreach($parentsArray as $p)
            {
                
                if(isset($currentPath[$p]['fields']))
                {
                   $currentPath = $currentPath[$p]['fields'];
                }
                else
                {
                    break;
                }
            }
            $fieldConfig = $currentPath[$field];
            
        }
        else
        {
            $fieldConfig = isset($configs[$field]) ? $configs[$field] : false;
        }
        
        $fieldConfig['nolabel'] = $noLabel == 1;
        
        $fieldConfig['belongsTo'] = $belongsTo;
        $fieldConfig['nowrapper'] = true;
        $fieldConfig['ajaxfield'] = true;
        return $fieldConfig;
    }
    
    public function getConfigFileName($moduleName = null)
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrapOptions = $front->getParam('bootstrap')->getOptions();
        $globalConfigPath = $bootstrapOptions['resources']['config']['config'];
        $modulesConfigPath = $bootstrapOptions['resources']['config']['modules'];
        if($moduleName)
        {
            if( Kms_Resource_Config::getSection($moduleName) )
            {
                return $globalConfigPath;
            }
            else
            {
                return $modulesConfigPath;
            }
        }
        else
        {
            return $globalConfigPath;
        }
    }
    
    
    private function cleanConfigArray($array)
    {
        foreach($array as $key => $val)
        {
            if(is_array($val))
            {
                $val = $this->cleanConfigArray($val);
                if(count($val))
                {
                    $array[$key] = $val;
                }
            }
            else
            {
                if('' === $val)
                {
                    unset($array[$key]);
                }
            }
        }
        return $array;
    }

    /**
     *  check if the given modules are handling the same entry types.
     *  @param Kms_Module_BaseModel $model - the module being saved model
     *  @param string $module - the module being saved
     *  @param array $config - the config     
     *  @return string $result - the error message if exists.
     */
    private function checkModulesHandlingSameType(Kms_Module_BaseModel $model, $module ,array $config)
    {
        $result = '';

        // is our module implementing the Kms_Interface_Functional_Entry_Type AND is being (possibly)enabled
        if (in_array('Kms_Interface_Functional_Entry_Type', class_implements($model)) && $config['enabled'])
        {
            Kms_Log::log("admin: module $module implementing Kms_Interface_Functional_Entry_Type config is being saved/enabled.", Kms_Log::DEBUG);

            // get the enabled modules for the Kms_Interface_Functional_Entry_Type
            $modulesForInterface = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
            $modulesForInterface = array_change_key_case($modulesForInterface);

            // our module is NOT one of the enabled modules implementing Kms_Interface_Functional_Entry_Type
            if (!empty($modulesForInterface) && !in_array($module, array_keys($modulesForInterface)))
            {
                Kms_Log::log("admin: enabling module $module implementing Kms_Interface_Functional_Entry_Type. Enabled modules are implementing it. Checking entry types.");

                // check that our prospective module handles its own type
                if (!$model->isHandlingEntryType($model->getMockEntryType())) {
                    Kms_Log::log("admin: module is not handling own entry type", Kms_Log::WARN);
                    $result = 'module does not handle its own entry type';                                            
                }

                // check for modules handling the same entry types as our module
                $iterator = new ArrayIterator($modulesForInterface);
                for($iterator->rewind() ; $iterator->valid() && empty($result); $iterator->next())
                {
                    $enabledModel = $iterator->current();
                    $enabledModuleName = $iterator->key();

                    // check that our prospective module does not handle other enabled modules types
                    if ($model->isHandlingEntryType($enabledModel->getMockEntryType())) {
                        Kms_Log::log("admin: module $enabledModuleName is handling this entry type already", Kms_Log::WARN);
                        return "module $enabledModuleName is handling this entry type already"; 
                    }

                    // check that our prospective module entry type is not handled by the enabled modules
                    if ($enabledModel->isHandlingEntryType($model->getMockEntryType())) {
                        Kms_Log::log("admin: this entry type is already being handled by module $enabledModuleName ", Kms_Log::WARN);
                        return "this entry type is already being handled by module $enabledModuleName"; 
                    }
                }
            }
        }
        return $result;
    }

    /**
     * performs actions before saving the module configuration
     * @param string $module - the module being saved
     * @param array $config - the config
     */
    private function preModuleSaveConfig($module, array $config)
    {
        $result = '';

        // our module's model
        $model = Kms_Resource_Config::getModelObjectByName($module);

        // test that our module meet its pre deployment conditions

        // is our module implementing the Kms_Interface_Deployable_PreDeployment AND is being (possibly)enabled
        if (in_array('Kms_Interface_Deployable_PreDeployment', class_implements($model)) && $config['enabled'])
        {
            Kms_Log::log("admin: module $module implementing Kms_Interface_Deployable_PreDeployment config is being saved/enabled.", Kms_Log::DEBUG);

            // get the enabled modules for the Kms_Interface_Functional_Entry_Type
            $modulesForInterface = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Deployable_PreDeployment');
            $modulesForInterface = array_change_key_case($modulesForInterface);
            
            // our module is NOT one of the enabled modules implementing Kms_Interface_Deployable_PreDeployment
            if (empty($modulesForInterface) || !in_array($module, array_keys($modulesForInterface)))
            {
                // can our module be enabled?
                if (!$model->canEnable()) {
                    // return the failure reason
                    return $model->getPreDeploymentFailReason();
                }
            }
        }

        // test that there is no other module implementing the Kms_Interface_Functional_Entry_Type for the same type
        // as our module

        $result  = $this->checkModulesHandlingSameType($model, $module, $config);

        return $result;
    }
    
    /**
     *  is this a modules config section
     */
    private function isModuleConfig($section)
    {
        $config_section = Kms_Resource_Config::getModuleSection($section); 
        return !empty($config_section);
    }

    /**
     * saves the configuration of a section
     * @param unknown_type $section
     * @param unknown_type $config
     * @throws Zend_Exception
     * @return multitype:boolean string |boolean
     */
    public function saveConfig($section, $config)
    {
        // is this a module?
        if ($this->isModuleConfig($section))
        {
            $result = $this->preModuleSaveConfig($section, $config);
            
            // pre module save action failed - issue an error message
            if (!empty($result))
            {
                return array('result' => false, 'extraMessage' => $result);
            }
        }
        
        $newConfig = new Zend_Config($config);
        $config = $this->cleanConfigArray($config);
        if(Kms_Resource_Config::isSaasEnabled())
        {

            $result = Kms_Saas_Config::mergeAndSaveConfig($section, $newConfig);
            if($result)
            {
                $message = $this->doPostSaveActions($section, $config);
                return array('result' => true, 'extraMessage' => $message);
            }
            else
            {
                return false;
            }
        }
        else
        {
            $filename = $this->getConfigFileName($section);

            $newConfigSection = $section;
            try
            {
                $currentConfig = new Zend_Config_Ini($filename, null, array('allowModifications' => true));
            }
            catch(Zend_Exception $exception)
            {
                Kms_Log::log('admin: Cannot load configuration from file: '.$filename, Kms_Log::NOTICE);
                $currentConfig = new Zend_Config(array(), true);
            }

            if($currentConfig)
            {
                if($newConfigSection)
                {
                    $currentConfig->$newConfigSection = $newConfig;
                }
                else
                {
                    $currentConfig = $newConfig;
                }

                // create a zend config writer object
                $writer = new Zend_Config_Writer_Ini(
                    array(
                        'config' => $currentConfig,
                        'filename' => $filename,
                    )
                );

                try
                {
                    $writer->write();
                    $message = $this->doPostSaveActions($section, $config);

                    return array('result' => true, 'extraMessage' => $message);
                }
                catch(Zend_Exception $e)
                {
                    Kms_Log::log('admin: Could not write configuration to '.realpath($filename).'... '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
                    throw new Zend_Exception($e->getMessage(), $e->getCode());
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
    }

    public function doPostSaveActions($section, $config)
    {
        $message = '';
        if(isset($this->_configTemplate[$section]))
        {
            $items = $this->_configTemplate[$section];
        }
        elseif(isset($this->_modulesTemplate[$section]))
        {
            $items = $this->_modulesTemplate[$section];
        }
        else
        {
            $items = null;
        }
        
        if(!is_null($items))
        {
            foreach($items as $item)
            {
                if(isset($item['postSaveAction']) && $item['postSaveAction'] && isset($config{$item['name']}))
                {
                    $postSaveAction = $item['postSaveAction'];
                    if(method_exists($this, $postSaveAction))
                    {
                        $message = $this->$postSaveAction($config{$item['name']} );
                    }
                    elseif(preg_match('/::/', $postSaveAction))
                    {

                        list($className, $postSaveMethod) = explode('::', $postSaveAction);
                        if(method_exists($className, $postSaveMethod))
                        {
                            $className::$postSaveMethod($config{$item['name']});
                        }
                    }
                }

            }
        }
        return $message;
    }
    
    
    public function backupConfigFile($section)
    {
        $path = $this->getConfigFileName($section);
        
        $conf = new Zend_Config_Ini($path, null);
        
        $fileName = basename($path);
        
        $configPath = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configs');
        $backupPath = $configPath . DIRECTORY_SEPARATOR . 'backups';
        
        if(!is_dir($backupPath))
        {
            try
            {
                @mkdir($backupPath, 0777, 1);
                if(!is_dir($backupPath))
                {
                    Kms_Log::log('admin: Cannot create backup config folder: "'.$backupPath.'"', Kms_Log::WARN);
                    throw new Zend_Exception('Cannot create backup config folder: "'.$backupPath.'"');
                    return false;
                }
            }
            catch(Exception $e)
            {
                Kms_Log::log($e->getCode().':'.$e->getMessage(), Kms_Log::ERR);
                throw new Zend_Exception($e->getMessage());
                return false;
            }
        }
        $bkPath = $backupPath;
        $backupPath = realpath($backupPath);
        if($backupPath)
        {

            $backupFullPath = $backupPath . DIRECTORY_SEPARATOR . $fileName . '.' . date('Y-m-d--H-i-s', time());

            try
            {
                if(@copy($path, $backupFullPath))
                {
                    return $backupFullPath;
                }
                else
                {
                    Kms_Log::log('admin: Failed to create backup file "'.$backupFullPath.'". The configuration was not saved.', Kms_Log::WARN);
                    throw new Zend_Exception('Failed to create backup file "'.$backupFullPath.'". The configuration was not saved.');
                    return false;
                }
            }
            catch(Exception $e)
            {
                Kms_Log::log($e->getCode().':'.$e->getMessage(), Kms_Log::ERR);
                throw new Zend_Exception($e->getMessage());
                return false;
            }
                
        }
        else
        {
            Kms_Log::log('admin: Unable to create or access backup folder "'.$bkPath.'". The configuration was not saved.', Kms_Log::WARN);
            throw new Zend_Exception('Unable to create or access backup folder "'.$bkPath.'". The configuration was not saved.');
        }
        
    }
    public function checkWritable($section)
    {
        $path = $this->getConfigFileName($section);
        if((file_exists($path) && is_writable($path)) || is_writable(dirname($path)))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function getFirstTabName()
    {
        $tabs = array_keys($this->_configTemplate);
        $tab = array_shift($tabs);
        return $tab;
    }
    
    
    
    
    
    /** functions for autocompletion **/
    public static function _getRoles()
    {
        $roles = array();
        
        foreach(Kms_Resource_Config::getSection('roles') as $key => $role)
        {
            if($key != Kms_Plugin_Access::EMPTY_ROLE && $key != Kms_Plugin_Access::PARTNER_ROLE)
            {
                $roles[$role] = $role;
            }
        }
        
        return $roles;
    }
    
    public static function _getRoleKeys()
    {
        $roles = array();
        
        foreach(Kms_Resource_Config::getSection('roles') as $key => $role)
        {
            if($key != Kms_Plugin_Access::EMPTY_ROLE && $key != Kms_Plugin_Access::PARTNER_ROLE)
            {
                $roles[$key] = $key;
            }
        }
        
        return $roles;
    }
    
    public static function _getRolesForKeys()
    {
        $roles = array();
        
        foreach(Kms_Resource_Config::getSection('roles') as $key => $role)
        {
            if($key != Kms_Plugin_Access::EMPTY_ROLE && $key != Kms_Plugin_Access::PARTNER_ROLE)
            {
                $roles[$key] = $role;
            }
        }
        
        return $roles;
    }

    /**
     * get the potential roles for channel creator. Ommit the anonymous role.
     * @return array of potential roles
     */
    public static function _getCreatorRoles()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        // add entry for no role at all - kmc admin only
        $roles[] = $translator->translate('No Role - Sys Admin only');
        $roles += self::_getRolesForKeys();
        // no anon roles
        unset ($roles[Kms_Plugin_Access::ANON_ROLE]);
        return $roles;
    }
    
    public static function _getCategoriesWithIds()
    {
        $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $categoryModel = Kms_Resource_Models::getCategory();
        $cats = $categoryModel->getCategoriesForForm();
        if(is_array($cats)) {
            $ret = array();
            foreach($cats as $id => $cat) {
                $ret[self::_createCategoryIdNameKey($id, $cat)] = $cat;
            }
            return $ret;
        }
        else {
            return array();
        }
    }
    
    
    /**
     * extracts category id and category name and returns them in an array [$id, $name]
     * @param unknown_type $idname
     */
    public static function _extractCategoryIdNameKey($idname)
    {
    	return explode('~', $idname, 2);
    }
    
    /**
     * concatenates category id and category name into a single string
     * @param unknown_type $id
     * @param unknown_type $name
     */
    public static function _createCategoryIdNameKey($id, $name)
    {
    	// $id has to preceed $name, because "~" is not a valid char in category id (used in _extractCategoryIdNameKey).
    	return $id . '~' . $name;
    }
    
    public static function _getCategories()
    {
        $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $categoryModel = Kms_Resource_Models::getCategory();
        $cats = $categoryModel->getCategoriesForForm();
        if(is_array($cats))
        {
            $ret = array();
            foreach($cats as $cat)
            {
                $ret[$cat] = $cat;
            }
            return $ret;
            
        }
        else
        {
            return array();
        }
        
    }
    
    
    public static function _getAllCategories()
    {
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $cats = $client->category->listAction();
        $categories = array();
        if($cats->totalCount > 0)
        {
            foreach($cats->objects as $cat)
            {
                $categories[$cat->fullName] = $cat->fullName;
            }
        }
        return $categories;
    }
    
    public static function _getThemes()
    {
        // get default themes
        $themes = self::_getThemesByFolder(THEMES_PATH);
        // get custom themes and check if they are enabled
        $themes += self::_getThemesByFolder(CUSTOM_THEMES_PATH, TRUE);
        return $themes;
    }
    
    public static function _getThemesByFolder($themeFolder, $checkEnabled = FALSE)
    {
        $themes = array(''=> '');
        if(is_dir($themeFolder))
        {
            $dp = opendir($themeFolder);
            while($dir = readdir($dp))
            {
                $enabled = true;
                if ($checkEnabled)
                {
                    $enabled = Kms_Resource_Config::isThemeAvailable($dir);
                }
                
                if(!preg_match('/^\./', $dir) && $enabled)
                {
                    $themes[$dir] = $dir;
                }
            }
        }
        return $themes;
    }
    
    public static function _getAuthNAdapters()
    {
        $adapterPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'library' . DIRECTORY_SEPARATOR . 'Kms' . DIRECTORY_SEPARATOR . 'Auth'  . DIRECTORY_SEPARATOR . 'AuthN';
        $adapters = array();
        
        if(is_dir($adapterPath))
        {
            $dp = opendir($adapterPath);
            while($file = readdir($dp))
            {
                $filePath = $adapterPath. DIRECTORY_SEPARATOR . $file;
                $className = 'Kms_Auth_AuthN_'.preg_replace('/(.*)\.php$/', '$1', $file);
                if(!preg_match('/^\./', $file) && is_file($filePath) && is_subclass_of($className, 'Kms_Auth_AuthN_Abstract'))
                {
                    $adapterName = $className;
                    $constName = $className.'::ADAPTER_NAME';
                    if(constant($constName)) $adapterName = $className::ADAPTER_NAME;

                    $adapters[$className] = $adapterName;
                }
                
            }
        }
        return $adapters;
        
    }

    public static function _getAuthZAdapters()
    {
        $adapterPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR .'library' . DIRECTORY_SEPARATOR . 'Kms' . DIRECTORY_SEPARATOR . 'Auth'  . DIRECTORY_SEPARATOR . 'AuthZ';
        $adapters = array();

        if(is_dir($adapterPath))
        {
            $dp = opendir($adapterPath);
            while($file = readdir($dp))
            {
                $filePath = $adapterPath. DIRECTORY_SEPARATOR . $file;
                $className = 'Kms_Auth_AuthZ_'.preg_replace('/(.*)\.php$/', '$1', $file);
                if(!preg_match('/^\./', $file) && is_file($filePath) && in_array('Kms_Auth_Interface_AuthZ', class_implements($className)))
                {
                    $adapterName = $className;
                    $constName = $className.'::ADAPTER_NAME';
                    if(constant($constName)) $adapterName = $className::ADAPTER_NAME;

                    $adapters[$className] = $adapterName;
                }

            }
        }
        return $adapters;

    }
    
    public function listImportantItems()
    {
        $configs = $this->listAllConfigs();
        $tabs = $this->getTabs();
        $ret = array();
        foreach($tabs['global'] as $tab)
        {
            $configs = $this->getTabConfigs($tab);
            foreach($configs as $key => $val)
            {
                if(isset($val['important']) && $val['important'])
                {
                    if(Kms_Resource_Config::getConfiguration($tab, $key) == Kms_Resource_Config::getDefaultConfiguration($tab, $key))
                    {
                        $configKey = $tab.'/'.$key;
                        $ret[] = $configKey;
                    }
                }
            }
        }
        foreach($tabs['modules'] as $module => $enabled)
        {
            $configs = $this->getTabConfigs($module);
            foreach($configs as $key => $val)
            {
                if(isset($val['important']) && $val['important'])
                {
                    if(Kms_Resource_Config::getConfiguration($module, $key) == Kms_Resource_Config::getDefaultConfiguration($module, $key))
                    {
                        $configKey = $module.'/'.$key;
                        $ret[] = $configKey;      
                    }
                }
            }
        }
        return $ret;
    }
    
    
    public function _validateCategoryTree($rootCategory)
    {
        $model = Kms_Resource_Models::getChannel();
        $model->validateRootCategoryContext($rootCategory);
        $createdCategories = $model->validateRootCategoryStructure($rootCategory);
        return count($createdCategories) ? "The following categories were created for you: ".join(', ', $createdCategories) : '';
    }
    
    
    public function _navigationPostSave($params)
    {
        $playlists = array();
        
        // get the playlists from the navigation items (post and pre)
        foreach($params as $navItem)
        {
            if(isset($navItem['type']) && $navItem['type'] == 'playlist')
            {
                if(isset($navItem['value']))
                {
                    $playlists[] = $navItem['value'];
                }
            }
        }
       
        $res = array();
        if(count($playlists))
        {
            $playlists = array_unique($playlists);
            $res = Kms_Resource_Models::getPlaylist()->makePublic($playlists);
        }
        
        if(count($res))
        {
            return 'The following playlists were added to the "playlists" category: '.join($res, ', ');
        }
        else
        {
            return '';
        }
    }
    
    
    /**
     * don't allow the like feature if the publisher configuration is not supporting it.
     * @return array $values
     */
    public static function _canEnableLikeValues()
    {
        $values = array(false => 'No (No permission for Partner)');
        $entryModel = Kms_Resource_Models::getEntry();

        if (self::isLikeInPartnerPermissions()){
            $values = array(true => 'Yes', false => 'No');
        }
     
        return $values;
    }
    
    /**
     * get the list of customdata profiles for the partner for users objects
     */
    public static function _configGetCustomdataProfilesForUser()
    {
        return Kms_Helper_Metadata::getCustomdataProfiles(Kaltura_Client_Metadata_Enum_MetadataObjectType::USER);
    }
    
    
    /**
     * test to see if the partner has like permission
     */
    public static function isLikeInPartnerPermissions()
    {
        $result = false;
        $client = Kms_Resource_Client::getAdminClient();
    
        try {
            $permissions = $client->permission->getCurrentPermissions();
            if (strpos($permissions, 'FEATURE_LIKE') !== FALSE){
                $result = true;
            }
        }
        catch (Kaltura_Client_Exception $ex){
            Kms_Log::log('config: Unable to check partner permissions' .$ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
        }
    
        return $result;
    }
    
    public static function getConfigFilesInFolder()
    {
        
        $configs = array();
        $configFolder = Kms_Resource_Config::getConfigPath();
        if(is_dir($configFolder))
        {
            $dp = opendir($configFolder);
            while($dir = readdir($dp))
            {
                if(preg_match('/^config\..*ini/', $dir) )
                {
                    $configs[] = $dir;
                }
                
            }
        }
        return $configs;
    }
    
}

