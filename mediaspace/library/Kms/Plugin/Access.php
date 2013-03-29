<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * Resource plugin for Access Control List (ACL)
 */

/**
 * Description of Access
 *
 * @author leon
 */
class Kms_Plugin_Access extends Zend_Controller_Plugin_Abstract
{
    protected $_request;

    const EMPTY_ROLE = 'emptyRole';
    const ANON_ROLE = 'anonymousRole';
    const VIEWER_ROLE = 'viewerRole';
    const PRIVATE_ROLE = 'privateOnlyRole';
    const ADMIN_ROLE = 'adminRole';
    const UNMOD_ROLE = 'unmoderatedAdminRole';
    const PARTNER_ROLE = 'partnerRole';

    const STORAGE_ADMIN = 'auth_admin';
    const STORAGE_USER  = 'auth_user';
    
    const EXCEPTION_CODE_ACCESS = 405;
    
    private $_acl;
    private $_auth;
    private $_identity;
    private $_allowed;
    private $_resource;
    private $_errorPage;
    private $_registeredRoles;
    private $_roles;
    
    private static $_roleMapping = array();
    const RESOURCE_DELIMITER = ':';

    public function __construct()
    {
        $this->_acl = new Zend_Acl();
        $this->_auth = Zend_Auth::getInstance();
    }

    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        parent::routeStartup($request);
        // extend expiration of session garbage collection
        // note - another application can delete the session if the expiration is not set
        $sessionExpire = Kms_Resource_Config::getConfiguration('auth', 'sessionLifetime');

        // Gonen 2012-12-10 - removing cookie_lifetime option - we put the expiration in the cookie data anyway.
        // the removal is to apply to StateFarm's security assesment which does not like the use of persistent cookies for session management.
        $sessionParams = array(
            //'cookie_lifetime' => $sessionExpire,
            'gc_maxlifetime'  => $sessionExpire,
        	'cookie_httponly' => true
        );
        //$sessionParams['httponly'] = true;
        // Gonen 2012-12-10 - in case KMS is accessed over SSL, specify "secure" flag on the session cookie.
        // this is added following StateFarm's security assesment
        if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
        {
            // only set secure flag if we're not on SSL due to the "force login on https" configuration
            // in which case we need to omit the secure flag because the cookie is going to be in use for HTTP
            if(!Kms_Resource_Config::getConfiguration('auth', 'httpsLogin'))
            {
                $sessionParams['cookie_secure'] = true;
            }
        }
        Zend_Session::setOptions($sessionParams);
        //ini_set('session.gc_maxlifetime', $sessionExpire);
    }
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if($this->requireAdminStorage($request))
        {
            // set storage to Admin
            $this->_auth->setStorage(new Zend_Auth_Storage_Session(self::STORAGE_ADMIN));
        }
        else
        {
            // set storage to User
            $this->_auth->setStorage(new Zend_Auth_Storage_Session(self::STORAGE_USER));
        }
        
        // set the roles
        $this->setRoles();

        $this->getIdentity();
        
        $this->setResource();
        
        $this->setErrorPage();
        
        $this->setAccessRules();
        
        $this->setModulesRules();
        
        if ( !$this->checkAllowed() )
        {
            // check if the resource requires a PARTNER_ROLE (kmc admin, for user management or config management)
            $partnerAccess = $this->_acl->isAllowed($this->_roles->{self::PARTNER_ROLE}, $this->_resource, $this->_request->getActionName());
            
            $this->denyAccess($partnerAccess);
        
        }
    }
    
    public function requireAdminStorage($request)
    {
        $isDefaultKmsAdminControllers =
            $request->getControllerName() == 'admin' 
                || $request->getControllerName() == 'install' 
                || $request->getControllerName() == 'kb';

        if ($isDefaultKmsAdminControllers)
            return true;

        /** @var $request Zend_Controller_Request_Abstract */
        $moduleName = $request->getModuleName();
        if (!$moduleName || $moduleName == Zend_Controller_Front::getInstance()->getDispatcher()->getDefaultModule())
            return false;

        $moduleModel = Kms_Resource_Config::getModelObjectByName($moduleName);
        if (is_null($moduleModel))
            return false;

        if (!$moduleModel instanceof Kms_Interface_AdminAuthStorage)
            return false;

        /** @var $moduleModel Kms_Interface_AdminAuthStorage */
        return $moduleModel->shouldUseAdminAuthStorage($request);
    }
    
    
    public function getIdentity()
    {
        $this->_identity = $this->_auth->getIdentity();
        
        if(is_null($this->_identity))
        {
            // create an anonymous session (authenticate)
            $this->_auth->authenticate(new Kms_Auth_Anonymous());
            $this->_identity = $this->_auth->getIdentity();
        }
        else
        {
            // add a check, if we have emptyRole but allow anonymous is true
            if($this->_identity->getRole() == self::EMPTY_ROLE && Kms_Resource_Config::getConfiguration('auth', 'allowAnonymous'))
            {
                // create an anonymous session (authenticate)
                $this->_auth->authenticate(new Kms_Auth_Anonymous());
                $this->_identity = $this->_auth->getIdentity();
            }
            else
            {
                // check if session is still valid, if not, clear it... and set to empty role
                if($this->_identity->getExpires() != 0 && $this->_identity->getExpires() < time())
                {
                    Kms_Log::log('accessPlugin: Clearing session due to expiration for '.$this->_identity->getId().', expiring at '.date('r', $this->_identity->getExpires()), Kms_Log::DEBUG);
                    $this->_auth->clearIdentity();
                    Kms_Log::log('accessPlugin: Setting anonymous session', Kms_Log::DEBUG);
                    $this->_auth->authenticate(new Kms_Auth_Anonymous());
                    $this->_identity = $this->_auth->getIdentity();
                }
                else
                {
                    // init auth adapter
                    $authNAdapter = Kms_Resource_Config::getConfiguration('auth', 'authNAdapter');
                    $adapter = null;
                    if (class_exists($authNAdapter))
                    {
                        $adapter = new $authNAdapter();
                    }
                    else
                    {
                        $err = 'Error in authentication. Auth Adapter "' . $authNAdapter . ' does not exist!';
                        Kms_Log::log('accessPlugin: '.$err, Kms_Log::ERR);
                        //throw new Zend_Exception($err, 500);
                    }
                    if($adapter && $adapter->allowKeepAlive())
                    {
                        // re-set the expiration to now + expiration time
                        $sessionExpiration = time() + Kms_Resource_Config::getConfiguration('auth', 'sessionLifetime');
                        $this->_identity->setExpires( $sessionExpiration );
                        Kms_Log::log('accessPlugin: changing session expiration for user '.$this->_identity->getId().', expiring at '.date('r', $this->_identity->getExpires()), Kms_Log::DEBUG);
                    }
                    
                }
            }
        }

    }
    
    public function hasPermission($section, $permission)
    {
        $role = $this->_identity->getRole();
        if(!self::roleExists($role))
        {
            $role = self::EMPTY_ROLE;
        }

        return $this->_acl->isAllowed($role, $section, $permission);
    }
    
    
    private function checkAllowed()
    { 
        //return true;
        if(!$this->_dispatchable)
        {
            // request is not dispatchable (does not exist, or etc)
            // we allow the request to go to the 404 page
            $this->_allowed = true;
        }
        else
        {
            try
            {
                $resources = $this->_acl->getResources();
                //check if role exists, and if not, assign empt role
                $role = $this->_identity->getRole();
                if(!self::roleExists($role))
                {
                    $role = self::EMPTY_ROLE;
                }
                
                if(in_array($this->_resource, $resources))
                {
                    $this->_allowed = $this->_acl->isAllowed($role, $this->_resource, $this->_request->getActionName());
                }
                
                // if not allowed - check category contextual roles
/*                if (!$this->_allowed && Kms_Plugin_KmsContext::hasContext())
                {
                    $this->_allowed = $this->_acl->isAllowed(Kms_Plugin_KmsContext::getContextualRole(), $this->_resource, $this->_request->getActionName());
                }*/
            }
            catch (Zend_Acl_Exception $e)
            {
                $this->_allowed = false;
                Kms_Log::log('accessPlugin: checkAllowed() - '.$e->getMessage(), Kms_Log::WARN);
            }
        }
        if(!$this->_allowed)
        {
           Kms_Log::log('accessPlugin: Access denied to '.$this->_resource.', '. $this->_request->getActionName().' for role '.$this->_identity->getRole(), Kms_Log::DEBUG);
/*           if (Kms_Plugin_KmsContext::hasContext())
           {
               Kms_Log::log('accessPlugin: Access denied to '.$this->_resource.', '. $this->_request->getActionName().' for contextual role '.Kms_Plugin_KmsContext::getContextualRole(), Kms_Log::DEBUG);
           }*/
        }
        
        return $this->_allowed;
    }
    
    private function setResource()
    {
        //check if the resource is dispatchable first
        $front = Zend_Controller_Front::getInstance();
        $dispatcher = $front->getDispatcher();
        $this->_dispatchable = $dispatcher->isDispatchable($this->_request);
        if($this->_dispatchable)
        {
            // create a resource name in the format module:controller (or controller) for default module
            $this->_resource = '';

            if($this->_request->getModuleName() != 'default')
            {
                $this->_resource .= $this->_request->getModuleName() . self::RESOURCE_DELIMITER;
            }

            $this->_resource .= $this->_request->getControllerName();
        }
    }
    
    private function setErrorPage()
    {
        /**
        $this->_errorPage = new stdClass;
        $this->_errorPage->module       = 'default';
        $this->_errorPage->controller   = 'user';
        $this->_errorPage->action       = 'access-denied';
        */
        $this->_errorPage = Kms_Resource_Config::getConfiguration('auth', 'accessDenied');
    }
    
    public static function roleExists($roleName)
    {
        // add a check for anonymous role and allowanonymous
        if($roleName == self::ANON_ROLE && Kms_Resource_Config::getConfiguration('auth', 'allowAnonymous') == false)
        {
            return false;
        }
        else
        {
            return in_array($roleName, self::$_roleMapping);
        }
    }
    
    private function isRoleRegistered($role)
    {
        return isset($this->_roles->$role) && !in_array($this->_roles->$role, $this->_registeredRoles);
    }
    
    
    /**
     *  Register a single Role
     * @param string $role
     * @param string $inherits 
     */
    private function registerRole($role, $inherits = null)
    {
        $this->_registeredRoles = $this->_acl->getRoles();
        if($this->isRoleRegistered($role))
        {
            if($inherits && isset($this->_roles->$inherits))
            {
                Kms_Log::log('accessPlugin:  Registering role '.$this->_roles->$role.', inherits '.$this->_roles->$inherits, Kms_Log::DEBUG);
                
                $this->_acl->addRole( new Zend_Acl_Role( $this->_roles->$role) , $this->_roles->$inherits );
            }
            else
            {
                Kms_Log::log('accessPlugin:  Registering role '.$this->_roles->$role, Kms_Log::DEBUG);
                $this->_acl->addRole( new Zend_Acl_Role($this->_roles->$role));
            }
        }
    }
    
    /*
     * set the roles
     */
    private function setRoles()
    {
        $this->_roles = Kms_Resource_Config::getRoles();
        
        // add an empty role in case allowAnonymous = 0
        $this->_roles->{self::EMPTY_ROLE} = self::EMPTY_ROLE;
        self::$_roleMapping[self::EMPTY_ROLE] = self::EMPTY_ROLE;
        $this->_roles->{self::PARTNER_ROLE} = self::PARTNER_ROLE;
        self::$_roleMapping[self::PARTNER_ROLE] = self::PARTNER_ROLE;
        
        $this->initRole(self::ANON_ROLE);
        $this->initRole(self::VIEWER_ROLE);
        $this->initRole(self::PRIVATE_ROLE);
        $this->initRole(self::ADMIN_ROLE);
        $this->initRole(self::UNMOD_ROLE);
        $this->initRole(self::PARTNER_ROLE);

        $this->registerRole(self::EMPTY_ROLE );
        $this->registerRole(self::ANON_ROLE     , self::EMPTY_ROLE);
        $this->registerRole(self::VIEWER_ROLE   , self::ANON_ROLE);
        $this->registerRole(self::PRIVATE_ROLE  , self::VIEWER_ROLE);
        $this->registerRole(self::ADMIN_ROLE    , self::PRIVATE_ROLE);
        $this->registerRole(self::UNMOD_ROLE    , self::ADMIN_ROLE);
        
        // partner user (kms Admin)
        $this->registerRole(self::PARTNER_ROLE, self::EMPTY_ROLE);
    }
   
    private function initRole($role)
    {
        if(isset($this->_roles->{$role}) && $this->_roles->{$role})
        {
            self::$_roleMapping[$role] = $this->_roles->{$role};        
        }
        else
        {
            $this->_roles->{$role} = $role;
            self::$_roleMapping[$role] = $role;
        }
    }
    
    
    /*
     * gets role mapping (kms role name to user defined roles from config [roles] section)
     * @return 
     */
    public static function getRole($role)
    {
        return isset(self::$_roleMapping[$role]) ? self::$_roleMapping[$role] : $role;
    }
    
    /*
     * gets role mapping ( user defined role name from config [roles] section to kms role name)
     * @return 
     */
    public static function getRoleKey($role)
    {
        $k = array_flip(self::$_roleMapping);
        return isset($k[$role]) ? $k[$role] : null;
    }

    
    /**
     * function to return user id
     * @return string 
     */
    public static function getId()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && is_object($auth->getIdentity()))
        {
            $id = $auth->getIdentity()->getId();
            return $id;
        }
        else
        {
            return NULL;
        }
    }
    
    public static function getCurrentRole()
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity() && is_object($auth->getIdentity()))
        {
            $role = $auth->getIdentity()->getRole();
            return $role;
        }
        else
        {
            return NULL;
        }
        
    }
    
    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function denyAccess($partnerAccess = false)
    {
        $this->_request->setModuleName($this->_errorPage->module);
        $this->_request->setControllerName($this->_errorPage->controller);
        $this->_request->setActionName($this->_errorPage->action);
        $this->_request->setParam('partnerAccess', $partnerAccess);
        $this->_request->setParam('accessDenied', true);
//        $this->_request->setParam('format', $);
    }
    
    public function setModulesRules()
    {
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Access');
        foreach($models as $model)
        {
            $rules = $model->getAccessRules();
            foreach($rules as $rule)
            {
                $this->setAllowRule($rule['controller'], $rule['actions'], $this->_roles->{$rule['role']});
            }
        }
     //   $controller = $this->getActionController();
    }
    
    
    public function setAccessRules()
    {
        // allow EVERYONE access to the redirector controller (backwards compatibility)
        $this->setAllowRule('redirector',   array(),                                                $this->_roles->{self::EMPTY_ROLE});
        
        // allow EVERYONE access to login, logout, authenticate, and error
        $this->setAllowRule('user',         array('login', 'logout', 'authenticate', 'unauthorized'),               $this->_roles->{self::EMPTY_ROLE});
        $this->setAllowRule('admin',        array('login', 'logout', 'authenticate'),               $this->_roles->{self::EMPTY_ROLE});
        $this->setAllowRule('error',        array('error'),                                         $this->_roles->{self::EMPTY_ROLE});
        
        // allow EVERYONE access to the asset action (mimick server behaviour)
        $this->setAllowRule('index',        array('asset'),                                         $this->_roles->{self::EMPTY_ROLE});        
        // allow anonymous and above access to index/index
        $this->setAllowRule('index',        array('index', 'version'),                              $this->_roles->{self::ANON_ROLE});
        
        // allow anonymous and above access to basic entry actions
        $this->setAllowRule('entry',        array('index', 'get', 'play', 'no-entry',
                                                    'check-status'),              $this->_roles->{self::ANON_ROLE});
        $this->setAllowRule('entry',        array('like', 'unlike'),              $this->_roles->{self::VIEWER_ROLE});

        $this->setAllowRule('entry',        array('set-private', 'add-entry-form', 'done'),         $this->_roles->{self::PRIVATE_ROLE});
        $this->setAllowRule('entry',        array('edit', 'save', 'delete', 'add'),                 $this->_roles->{self::PRIVATE_ROLE});
        $this->setAllowRule('entry',        array('process-new-presentation', 'post-upload'),       $this->_roles->{self::PRIVATE_ROLE});

        // permission for editing playlists
        $this->setAllowRule('playlist',        array('edit', 'delete', 'update-entries', 'save'),   $this->_roles->{self::PRIVATE_ROLE});

        
        // permission for setting a category (publishing)
        $this->setAllowRule('entry',        array('setcategory'),                                   $this->_roles->{self::ADMIN_ROLE});
        
        // permission for approving the entry
        $this->setAllowRule('entry',        array('approve'),                                       $this->_roles->{self::UNMOD_ROLE});
        
        // gallery permissions
        $this->setAllowRule('gallery',     array('view'),                                           $this->_roles->{self::ANON_ROLE});
        $this->setAllowRule('gallery',     array('remove-entry'),                                   $this->_roles->{self::VIEWER_ROLE});

        // my media - my playlists permissions
        $this->setAllowRule('user',        array('my-media', 'my-playlists', 'keep-alive',
                                                'no-playlists', 'my-media-clear-cache'),            $this->_roles->{self::PRIVATE_ROLE});
        
        // partner user (kmc admin) access rules
        $this->setAllowRule('admin',       array('user-list', 'user-add', 'user-edit', 
                                            'user-save', 'user-delete', 'generate-password',
                                            'clear-cache', 'config',
                                            'unit-tests',  'save-config', 'config-field',
                                            'export-config', 'import-config', 'user-bulkupload',
                                            'enable-debug',  'deploy', 'index', 'log-viewer'),      $this->_roles->{self::PARTNER_ROLE});
        
        $this->setAllowRule('install',        array('index', 'migrate', 'install', 
                                                          'deploy', 'check-rewrite', 'done'),       $this->_roles->{self::EMPTY_ROLE});
        
                                                          
        $this->setAllowRule('kb',        array('index', 'general', 'interfaces', 'interface-info', 
                                                'developer-tools',
                                                       'internal', 'viewfiles', 'viewhooks'),       $this->_roles->{self::PARTNER_ROLE});
                                                          
        // channels permissions- general
        $this->setAllowRule('channels' , array('denied'),                      $this->_roles->{self::ANON_ROLE});
        $this->setAllowRule('channels' , array('index','view', 'mychannels'),  $this->_roles->{self::VIEWER_ROLE});

        // channels permissions - set in configuration
        $channelCreatorRole = Kms_Resource_Config::getConfiguration('channels', 'channelCreator');
        if(isset($this->_roles->{$channelCreatorRole}))
        {
            $this->setAllowRule('channels' , array('create'), $this->_roles->{$channelCreatorRole});
        }
        else
        {
            Kms_Log::log('No role will be able to create channels. If this is not intentional, check config.ini (channelCreator).', Kms_Log::WARN);
        }
        // channel permissions - these have additional contextual roles
        $this->setAllowRule('channels' , array('edit', 'save', 'delete'),       $this->_roles->{self::VIEWER_ROLE});
        
    }
    
    
    public function setAllowRule($resource , $action = null, $roles = false )
    {
        if(!is_array($action))
        {
            $action = array($action);
        }
        
        if(!$roles)
        {
            $roles = $this->_acl->getRoles();
        }
        if(!in_array($resource, $this->_acl->getResources()))
        {
            $this->_acl->addResource($resource);
        }
        
        $this->_acl->allow($roles, $resource, $action );
        
    }
    
    
    /**
     * method to check whether userId is the current logged in user
     * @param string $userId 
     * @return boolean
     */
    public static function isCurrentUser($userId)
    {
        if(Zend_Auth::getInstance()->hasIdentity())
        {
            $identity = Zend_Auth::getInstance()->getIdentity();
            return strtolower($userId) == strtolower($identity->getId());
        }
        else
        {
            return false;
        }
    }
    
    /**
     * // method to return true if user is logged in, false if role is EMPTY or ANONYMOUS
     * @return boolean
     */
    public static function isLoggedIn()
    {
        $roleName = self::getCurrentRole();
        $role = self::getRole($roleName);
        if($role && $role != self::getRole(self::ANON_ROLE) && $role != self::getRole(self::EMPTY_ROLE))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
}
