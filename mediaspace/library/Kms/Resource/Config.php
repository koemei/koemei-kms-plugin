<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Plugin
 *
 * @author leon
 */
class Kms_Resource_Config extends Zend_Application_Resource_ResourceAbstract
{

    private static $modules;
    private static $config;
    private static $defaultConfig;
    private static $defaultModulesConfig;
    private static $existingModules;
    private static $logging;
    private static $roles;
    private static $cache;
    private static $version;
    private static $configFileName;

    private static $hostname = null;
    private static $saasEnabled = false;
    private static $configPath;
    
    const MODULES_PREFIX = 'Module';
    const MODELS_DIR = 'models';

    const DEFAULT_APP_CONFIG = 'default';
    const APP_CONFIG = 'config';
    const DEFAULT_CONFIG_FILENAME = 'default.ini';
    const MODULE_TEMPLATE_FILE = 'admin.ini';
    const MODULE_INFO_FILE = 'module.info';
    
    const LOG_CONFIG = 'logs';

    const MODULES_CONFIG = 'modules';
    
    const ROLES_CONFIG = 'config';
    const ROLES_CONFIG_SECTION = 'roles';

    const CACHE_CONFIG = 'cache';
    const CACHE_CONFIG_SECTION = 'cache';
    
    const GALLERY_ROOT = 'site>galleries';
    const CHANNEL_ROOT = 'site>channels';
    
    const DEBUG_COOKIE_NAME = 'kmsdebug';
    const DEBUG_CONFIG_NAME = 'kmsdebugconfig';
    
    // initialize the plugin
    public function init()
    {
        
        $this->initLogging();
        $this->initVersion();
        $options = $this->getOptions();
        if(isset($options['saas']) && isset($options['saas']['enabled']) && $options['saas']['enabled'] == 1)
        {
            $this->initSaaS();
        }
        else
        {
            $this->initCache();
            $this->initConfig();
            Kms_Log::setDebugLevel();
            Kms_Log::setLogWriters();
            $this->initModules();
        }
        
        $this->initRoles();
    }

    private function initSaaS()
    {
        if(class_exists('Kms_Saas_Config'))
        {
            Kms_Saas_Config::init();
            if(isset($_SERVER['SERVER_NAME']))
            {
                self::$hostname = $_SERVER['SERVER_NAME'];
            }
            self::$saasEnabled = true;
            Kms_Resource_Cache::setCacheIdPrefix(self::$hostname);
            $this->initCache();
            $this->initDefaultConfig();
            if (!(self::$config instanceof Zend_Config) || !(self::$modules instanceof Zend_Config))
            {
                $configArray = Kms_Saas_Config::initKmsConfig();
                self::$config = $configArray['config'];
                self::$modules = $configArray['modules'];
            }
            Kms_Log::setDebugLevel();
            $this->initDefaultModules();
            $this->initModulesAutoloader();
        }
        else
        {
            throw new Exception('Kms_Saas_Config class is missing - cannot work in SaaS mode');
        }
    }

    public static function getHostname()
    {
        return self::$hostname;
    }

    public static function isSaasEnabled()
    {
        return self::$saasEnabled;
    }

    static public function getModulesForViewHook($viewHook)
    {
        $hooks = array();

        $modules = Kms_Resource_ViewHook::getModulesRegisteredForViewHook($viewHook);
        foreach ($modules as $moduleName)
        {
            $lcaseModuleName = strtolower($moduleName);
            $moduleEnabled = self::getModuleConfig(strtolower($moduleName), 'enabled');
            if($moduleEnabled)
            {
                $model = self::getModelObjectByName($moduleName);
                if (is_null($model))
                    continue;

                $hooks[strtolower($moduleName)] = $model->getViewHook($viewHook);
            }
        }
        return $hooks;
        /*
          $models = self::getModulesForInterface('Kms_Interface_Model_ViewHook');

          foreach ($models as $module => $model)
          {
          $module = strtolower($module);
          if ($model->viewHookExists($viewHook))
          {
          $hooks[$module] = $model->getViewHook($viewHook);
          }
          }
          return $hooks; */
    }

    public static function getModelObjectByName($moduleName)
    {
        $modelName = self::getModelName($moduleName);
        if (class_exists($modelName))
        {
            return new $modelName();
        }
        return null;
    }
    
    static public function moduleImplements($module, $interface)
    {
        $className = self::getModelName($module);
        
        if (in_array($interface, class_implements($className)))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static public function getModulesForInterface($interface)
    {
        $models = array();
        $modules = self::$existingModules;
        foreach ($modules as $module)
        {
            $moduleEnabled = self::getModuleConfig(strtolower($module), 'enabled');
            if($moduleEnabled)
            {
                $model = self::getModelObjectByName($module);
                if (is_null($model))
                    continue;

                // class_implements returns an array of interfaces implemented by the class (in parameter)
                if (in_array($interface, class_implements($model)))
                {
                    $models[ucfirst($module)] = $model;
                }
            }
        }
        return $models;
    }

    static public function getConfigFileName()
    {
        return self::$configFileName;
    }
    
    
    static public function getConfigPath()
    {
        return self::$configPath;
    }
    
    
    static public function getModelName($moduleName)
    {
        $modelName = ucfirst($moduleName) . '_Model_' . ucfirst($moduleName);
        return $modelName;
    }

    static public function getLogFormat()
    {
        if (isset(self::$logging['format']))
        {
            return self::$logging['format'];
        }
        else
        {
            return null;
        }
    }
    static public function getStatsLogPath()
    {
        if (isset(self::$logging['clientStats']))
        {
            return self::$logging['clientStats'];
        }
        else
        {
            return null;
        }
        
    }
    
    
    static public function getLogPath()
    {
        if (isset(self::$logging['application']))
        {
            return self::$logging['application'];
        }
        else
        {
            return null;
        }
    }

    static public function getTraceLogPath()
    {
        if (isset(self::$logging['trace']))
        {
            return self::$logging['trace'];
        }
        else
        {
            return null;
        }
    }

    
    static public function getClientErrorLogPath()
    {
        if (isset(self::$logging['clientErrors']))
        {
            return self::$logging['clientErrors'];
        }
        else
        {
            return null;
        }
    }
    
    static public function getClientLogPath()
    {
        if (isset(self::$logging['clientDebug']))
        {
            return self::$logging['clientDebug'];
        }
        else
        {
            return null;
        }
    }

    public function initVersion()
    {
        //get options for the resource plugin
        $options = $this->getOptions();
        self::$version = $options['version'];
        Kms_Log::log('init: version ' . self::$version, Kms_Log::DEBUG);
    }


    public function initDefaultModules()
    {
        if (APPLICATION_ENV == 'production')
        { // only check from cache on production env
            self::$defaultModulesConfig = Kms_Resource_Cache::appGet('config', array('defaultModules'));
            self::$existingModules = Kms_Resource_Cache::appGet('config', array('existingModules'));
        }
        else
        {
            self::$defaultModulesConfig = false;
            self::$existingModules = false;
        }
        
        if (!(self::$defaultModulesConfig instanceof Zend_Config) || !(self::$existingModules))
        {
            // now get all the default files together and merge them
            self::$existingModules = array();            
            
            $defaultModulesConfigObject = new Zend_Config(array(), true);
            
            $modulesPaths = self::getModulePaths($this->getBootstrap()->getOptions());
            foreach ($modulesPaths as $modulesPath){
                $defaultModulesConfigObject = $this->initDefaultModulesByPath($defaultModulesConfigObject, $modulesPath);
            }
            
            self::$defaultModulesConfig = $defaultModulesConfigObject;
            
            // save in cache
            if (APPLICATION_ENV == 'production')
            {
                Kms_Resource_Cache::appSet('config', array('defaultModules'), self::$defaultModulesConfig);
                Kms_Resource_Cache::appSet('config', array('existingModules'), self::$existingModules);
            }
        }
    }

    /**
     * init the default modules by their path
     * @param Zend_Config $defaultModulesConfigObject
     * @param string $modulesPath
     * @return Zend_Config $defaultModulesConfigObject
     */
    private function initDefaultModulesByPath(Zend_Config $defaultModulesConfigObject, $modulesPath)
    {
        $dir = new DirectoryIterator($modulesPath);
        $defaultConfigFilename = self::DEFAULT_CONFIG_FILENAME;
        
        foreach ($dir as $fileInfo)
        {
            if (!$fileInfo->isDot() && $fileInfo->isDir())
            {
                $moduleName = $fileInfo->getFilename();
      
                // load only available modules (if this is saas)
                if (self::isModuleAvailable($modulesPath, $moduleName, $this->getBootstrap()->getOptions()))
                {
                    self::$existingModules[$moduleName] = $moduleName;
                    $defaultConfigFullPath = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFilename() . DIRECTORY_SEPARATOR . $defaultConfigFilename;
                    if (file_exists($defaultConfigFullPath))
                    {
                        $defaultConfiguration = new Zend_Config_Ini($defaultConfigFullPath, null, array('allowModifications' => 'true'));
                        //exit;
                        $defaultModulesConfigObject->$moduleName = $defaultConfiguration;
                    }
                }
            }
        }
        
        return $defaultModulesConfigObject;
    }
    
    private function validateModulePath($moduleName)
    {
        $bootstrapOptions = $this->getBootstrap()->getOptions();
        if ($this->isModulePathsSet($bootstrapOptions))
        {
            $modulesPaths = $this->getModulePaths($bootstrapOptions);
            foreach ($modulesPaths as $modulesPath){
                if(is_dir($modulesPath) && is_dir($modulesPath . DIRECTORY_SEPARATOR . $moduleName))
                {
                    return $modulesPath . DIRECTORY_SEPARATOR . $moduleName;
                }
            }
            return false;
        }
        else
        {
            return false;
        }
    }
    
    
    public function initModulesAutoloader()
    {
        // front controller modules dir
        $this->addFrontControllerModuleDirectory();
        
        // modules autoloader
        $bootstrap = $this->getBootstrap();
        $view = $bootstrap->getResource('view');

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
                
        foreach (self::$existingModules as $module)
        {
            // iterate over the configured modules from the ini file
            $moduleDir = $this->validateModulePath($module);
            if ($moduleDir !== false)
            {
                // add autoloader namespace for this module
                $moduleLoader = new Zend_Application_Module_Autoloader(array(
                            'namespace' => ucfirst($module) . '_',
                            'basePath' => $moduleDir
                        ));

                // check if module is enabled
                $moduleEnabled = self::getModuleConfig(strtolower($module), 'enabled');
                if($moduleEnabled)
                {
                    $view->addHelperPath($moduleDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'helpers', ucfirst($module) . '_View_Helper_');

                    $modelClassName = ucfirst($module) . '_Model_' . ucfirst($module);
                    if(class_exists($modelClassName) && in_array( 'Kms_Interface_Model_Dependency', class_implements($modelClassName)))
                    {
                        eval('$moduleDependency = '.$modelClassName.'::getModuleDependency();');
                        Kms_Log::dump('module dependency: '.print_r($moduleDependency, true));
                        Kms_Log::dump('module dependency: '.print_r($modelClassName, true));
                        if(is_array($moduleDependency) && count($moduleDependency) && isset(self::$modules) && (self::$modules instanceof Zend_Config))
                        {
                            foreach($moduleDependency as $dependsOn)
                            {
                                Kms_Log::dump('module dep: '.$dependsOn);
                                Kms_Log::dump('module dep: isset - '.print_r(self::$modules->$dependsOn ,true));
                                if(!isset(self::$modules->$dependsOn) || !self::$modules->$dependsOn->enabled)
                                {
                                    Kms_Log::dump('unsetting module '.$module);
                                    unset(self::$modules->$module);
                                    $key = array_search($module, self::$existingModules);
                                    if($key !== false) unset(self::$existingModules[$key]);
                                    break;
                                }
                            }
                        }
                    }

                }
            }
        }
    }

    public function initModules()
    {
        Kms_Log::log('init: modules', Kms_Log::DEBUG);
        // get options from bootstrap;
        $bootstrapOptions = $this->getBootstrap()->getOptions();

        // check if we have the modules configuration in the cache
        if (APPLICATION_ENV == 'production')
        { // only check from cache on production env
            self::$modules = Kms_Resource_Cache::appGet('config', array('modules'));
        }
        else
        {
            self::$modules = false;
        }
        
        if (!(self::$modules instanceof Zend_Config))
        {
            // run through all module dirs and parse the default module files
            // each module has it's own defaults file, but all modules have one configuration file
            // it is kind of tricky to merge the defaults with the configuration already set in modules.ini

            $modulesConfigFilename = $bootstrapOptions['resources']['config'][self::MODULES_CONFIG];
            // try to load modules.ini first
            try
            {
                $modulesConfig = new Zend_Config_Ini($modulesConfigFilename, null, array('allowModifications' => true));
            }
            catch(Zend_Config_Exception $ex)
            {
                Kms_Log::log('config: unable to open modules.ini, '.$ex->getCode().': '.$ex->getMessage(), Kms_Log::DEBUG);
                $modulesConfig = new Zend_Config(array(), true);
            }
            
            // verify that the folder for each module actually exists
            self::$modules = new Zend_Config(array(), true);
            foreach($modulesConfig as $moduleName => $moduleSection)
            {
                if($this->validateModulePath($moduleName))
                {
                    self::$modules->$moduleName = $moduleSection;
                }
            }
            
            $this->saveModulesConfigToCache();

        }
        $this->initDefaultModules();
        $this->initModulesAutoloader();
    }

    private function initDefaultConfig()
    {
        //get options for the resource plugin
        $options = $this->getOptions();
        // check if we have the default config stored in cache
        if (APPLICATION_ENV == 'production')
        { // only check from cache on production env
            self::$defaultConfig = Kms_Resource_Cache::appGet('config', array('defautConfig'));
        }
        else
        {
            self::$defaultConfig = false;
        }
        
        if (!(self::$defaultConfig instanceof Zend_Config))
        {
            $defaultConfigFile = $options[self::DEFAULT_APP_CONFIG];
            if (file_exists($defaultConfigFile))
            {
                self::$defaultConfig = new Zend_Config_Ini($defaultConfigFile, null, array('allowModifications' => true));
                if (APPLICATION_ENV == 'production')
                {
                    Kms_Resource_Cache::appSet('config', array('defaultConfig'), self::$defaultConfig);
                }
            }

        }
    }

    // init global configuration
    public function initConfig()
    {
        //get options for the resource plugin
        $options = $this->getOptions();

        $this->initDefaultConfig();

        self::$configPath = $options['path'];
        self::$configFileName = $options[self::APP_CONFIG];
        // check if we have the config stored in cache
        if (APPLICATION_ENV == 'production')
        { // only check from cache on production env
            self::$config = Kms_Resource_Cache::appGet('config', array('config'));
        }
        else
        {
            self::$config = false;
        }
        
        if (!(self::$config instanceof Zend_Config))
        {
            //get options for the resource plugin
            $configFile = $options[self::APP_CONFIG];
            $configObj = NULL;
            // load default config

            // is there a config file?
            if (file_exists($configFile))
            {
                $configObj = new Zend_Config_Ini($configFile, null, array('allowModifications' => true));
                // merge default into config
                self::$config = $configObj;

                // store config in cache
                $this->saveConfigToCache();
            }
            else
            {
                $front = Zend_Controller_Front::getInstance();
                $front->registerPlugin(new Kms_Plugin_Install(), 0);
                self::$config = self::$defaultConfig;
            }
        }
    }

    private function saveConfigToCache()
    {
        // store config in cache
        if (APPLICATION_ENV == 'production')
        {
            Kms_Resource_Cache::appSet('config', array('config'), self::$config);
        }
    }

    private function saveModulesConfigToCache()
    {
        // store modules config in cache
        if (APPLICATION_ENV == 'production')
        {
            Kms_Resource_Cache::appSet('config', array('modules'), self::$modules);
        }
    }

    // init global configuration
    public function initCache()
    {
        //get options for the resource plugin
        $options = $this->getOptions();
        $configFile = $options[self::CACHE_CONFIG];

        // is there a config file?
        if (file_exists($configFile))
        {
            self::$cache = new Zend_Config_Ini($configFile, null, array('allowModifications' => true));
        }
        $this->getBootstrap()->bootstrap('cache');
    }

    // init the logging configuration
    public function initLogging()
    {
        //get options for the resource plugin
        $options = $this->getOptions();
        self::$logging = $options[self::LOG_CONFIG];
    }

    public function initRoles()
    {
        // is there a config?
        self::$roles = self::getSection('roles');
        //if (isset(self::$config->roles))
        //{
         //   self::$roles = self::$config->roles;
        //}
    }

    /**
     *
     * @return type Zend_Config
     */
    public static function getRoles()
    {
        // if roles not parsed yet
        return self::$roles;
    }

    /**
     * method returns array of application roles (i.e. 4 roles that can eb applied on users)
     * @return array
     */
    public static function getApplicationRoles()
    {
        $applicationRoles = array();
        $roles = self::getRoles()->toArray();
        $ignoreRoles = array('anonymousRole', 'emptyRole', 'partnerRole');
        foreach($roles as $key => $role)
        {
            if(!in_array($key, $ignoreRoles))
            {
                $applicationRoles[$role] = $role;
            }
        }
        return $applicationRoles;
    }

    public static function getConfiguration($section, $key)
    {
        if (isset(self::$config->$section) && isset(self::$config->$section->$key))
        {
            Kms_Log::log('config: Accessing configuration ' . $section . '[' . $key . ']', Kms_Log::DEBUG);
            return self::$config->$section->$key;
        }
        elseif(isset(self::$defaultConfig->$section) && isset(self::$defaultConfig->$section->$key))
        {
            Kms_Log::log('config: Accessing default configuration ' . $section . '[' . $key . ']', Kms_Log::DEBUG);
            return self::$defaultConfig->$section->$key;
        }
    }

    public static function getSection($section)
    {
        if (isset(self::$config->$section))
        {
            Kms_Log::log('config: Accessing configuration section ' . $section, Kms_Log::DEBUG);
            return self::$config->$section;
        }
        elseif(isset(self::$defaultConfig->$section))
        {
            Kms_Log::log('config: Accessing default configuration section ' . $section, Kms_Log::DEBUG);
            return self::$defaultConfig->$section;
        }
        else
        {
            Kms_Log::log('config: Accessing section that does not exist ('.$section.')', Kms_Log::NOTICE);
        }
    }

    public static function getModuleSection($module)
    {
        if (isset(self::$modules->$module))
        {
            Kms_Log::log('config: Accessing configuration section for module ' . $module, Kms_Log::DEBUG);
            return self::$modules->$module;
        }
        elseif (isset(self::$defaultModulesConfig->$module))
        {
            Kms_Log::log('config: Accessing default configuration section for module ' . $module, Kms_Log::DEBUG);
            return self::$defaultModulesConfig->$module;
        }
    }

    public static function getModuleConfig($module, $key, $default = null)
    {
        
        if (isset(self::$modules->$module) && isset(self::$modules->$module->$key))
        {
            Kms_Log::log('config: Accessing configuration for module ' . $module.' ['.$key.']', Kms_Log::DEBUG);
            return self::$modules->$module->$key;
        }
        elseif (isset(self::$defaultModulesConfig->$module) && isset(self::$defaultModulesConfig->$module->$key))
        {
            Kms_Log::log('config: Accessing default configuration for module ' . $module.' ['.$key.']', Kms_Log::DEBUG);
            return self::$defaultModulesConfig->$module->$key;
        }
    }

    public static function getCacheConfig()
    {
        if (isset(self::$cache))
        {
            return self::$cache;
        }
    }

    public static function setCacheConfig($section, $key, $value)
    {
        if (isset(self::$cache))
        {
            if (isset(self::$cache->$section))
            {
                //echo "setting cache to $value";
                self::$cache->$section->$key = $value;
            }
        }
    }

    public static function getConfigObject()
    {
        return array('config' => self::$config, 'modules' => self::$modules);
    }

    public static function getConfigSections()
    {
        $ret = array();
        foreach(self::$config as $section => $config)
        {
            $ret[$section] = $section;
        }
        
        foreach(self::$defaultConfig as $section => $config)
        {
            if(!isset($ret[$section]))
            {
                $ret[$section] = $section;
            }
        }
        return $ret;
    }
    
    public static function getDefaultConfiguration($section, $key)
    {
        if (isset(self::$defaultConfig->$section) && isset(self::$defaultConfig->$section->$key))
        {
            Kms_Log::log('config: Accessing default configuration ' . $section . '[' . $key . ']', Kms_Log::DEBUG);
            return self::$defaultConfig->$section->$key;
        }
    }

    /**
     * This changes the configuration runtime instance only! no config file is changed.
     * @param string $section
     * @param string $key
     * @param mixed $value
     */
    public static function setConfiguration($section, $key, $value)
    {
        if (isset(self::$config) && isset(self::$config->$section))
        {
            self::$config->$section->$key = $value;
        }
        elseif(isset(self::$defaultConfig) && isset(self::$defaultConfig->$section))
        {
            self::$defaultConfig->$section->$key = $value;
        }
    }

    public static function reInitConfig($configPath)
    {
        clearstatcache();
        // is there a config file?
        if (file_exists($configPath))
        {
            $configObj = new Zend_Config_Ini($configPath, null, array('allowModifications' => 'true'));
            // merge default into config
/*            if (self::$defaultConfig)
            {
                self::$defaultConfig->merge($configObj);
                self::$config = self::$defaultConfig;
            }
            else
            {*/
                self::$config = $configObj;
            //}
        }
    }

    public static function reInitSaasConfig($hostname)
    {
        $_SERVER['SERVER_NAME'] = $hostname;
        self::$hostname = $hostname;
        $obj = Kms_Saas_Config::getRecord();

        $config = (isset($obj->config))? unserialize($obj->config): null;
        if(!is_null($config) && ($config instanceof Zend_Config))
        {
            self::$config = $config;
        }
    }

    public static function getVersion()
    {
        return self::$version;
    }

    public static function getModules()
    {        
        return self::$modules;
    }
    
    public static function getAllModules()
    {    
        return self::$existingModules;
    }

    /**
     * get the KMS privact context
     */
    public static function getCategoryContext()
    {
        return self::getConfiguration('application', 'privacyContext');
    }

    /**
     * get the KMS instance ID
     * @return string
     */
    public static function getInstanceId()
    {
       return self::getConfiguration('application', 'instanceId');
    }
    
    /**
     * get the root mediaspace category
     */
    public static function getRootCategory()
    {
        return self::getConfiguration('categories', 'rootCategory');
    }
    
    /**
     * get the root category for galleries
     */
    public static function getRootGalleriesCategory()
    {
        $rootCategory = self::getRootCategory();
        return $rootCategory.'>'.self::GALLERY_ROOT;
    }

    /**
     * get the root category for channels
     */
    public static function getRootChannelsCategory()
    {
        $rootCategory = self::getRootCategory();
        return $rootCategory.'>'.self::CHANNEL_ROOT;
    }
    
    /**
     * get the modules paths - default + custom
     * @param array $bootstrapOptions
     */
    public static function getModulePaths($bootstrapOptions = null)
    {
        if (empty($bootstrapOptions)){
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            $bootstrapOptions = $bootstrap->getOptions();
        }
                
        // the general modules path
        $modulesPath = $bootstrapOptions['resources']['frontController']['moduleDirectory'];
                
        // the contrib modules path
        $contribModulesPath = $bootstrapOptions['resources']['frontController']['contribModuleDirectory'];
        
        $paths = array($modulesPath,$contribModulesPath);
        
        return $paths;
    }
    
    /**
     * check if the modules path config is set.
     * @param array $bootstrapOptions
     */
    private static function isModulePathsSet($bootstrapOptions)
    {
        return  isset($bootstrapOptions['resources']['frontController']['moduleDirectory']) &&
                isset($bootstrapOptions['resources']['frontController']['contribModuleDirectory']);
    }
    

    /**
     * add the extra modules directories to the front controller
     */
    private function addFrontControllerModuleDirectory()
    {
        $bootstrapOptions = $this->getBootstrap()->getOptions();
    
        $modulesPaths = $this->getModulePaths($bootstrapOptions);
        foreach ($modulesPaths as $path){
            if ($path != $bootstrapOptions['resources']['frontController']['moduleDirectory']){
                // this is not the default front controller module dir - add it.
                Zend_Controller_Front::getInstance()->addModuleDirectory($path);
            }
        }
    }
    
    /**
     * is this module exists and enabled
     * @param unknown_type $moduleName
     */
    public static function shouldLoadModule($modulename)
    {
        return array_key_exists($modulename, self::$existingModules) && self::getModuleConfig($modulename, 'enabled');
    }
    
    /**
     * check if a module is available to kms.
     * modules can be unavailable only under dir modulesCustom, when running as SaaS.
     * @param string $modulesPath
     * @param string $moduleName
     */
    public static function isModuleAvailable($modulesPath, $moduleName, $bootstrapOptions = NULL)
    {
        $enabled = true;
        
        if (empty($bootstrapOptions)){
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            $bootstrapOptions = $bootstrap->getOptions();
        }
        
        // test only if this is saas(kms, not admin), and the custom modules dir
        if (self::isSaasEnabled() && 
            $modulesPath == $bootstrapOptions['resources']['frontController']['contribModuleDirectory'] &&
            !empty(self::$hostname))
        {
            $enabled = Kms_Saas_Config::isModuleAvailable($moduleName, self::getHostname());            
        }
        return $enabled;
    }
    
    
    /**
     * check if a theme is available to kms.
     * @param string $themeName
     */
    public static function isThemeAvailable($themeName)
    {
        $enabled = true;
        
        // test only if this is saas(kms, not admin), and the custom themes dir
        if (self::isSaasEnabled() && !empty(self::$hostname))
        {
            $enabled = Kms_Saas_Config::isThemeAvailable($themeName, self::getHostname());
        }
        
        return $enabled;
    }
}

?>
