<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ldap
 *
 * @author leon
 */
class Kms_Auth_AuthN_Ldap extends Kms_Auth_AuthN_Abstract implements Kms_Auth_Interface_AuthZ
{
    const ADAPTER_NAME = "LDAP AuthN";

    const LDAP_OPT_DIAGNOSTIC_MESSAGE = 0x0032;
    const ROLE_FAILURE_TEXT = 'no valid role found';
    const LOGIN_FAILURE_TEXT = 'failed to login';
    const USER_REPLACEMENT_TOKEN = '@@USERNAME@@';
    const GROUPS_REPLACEMENT_TOKEN = '@@GROUPS_REPLACEMENTS@@';
    const GROUP_NAME_REPLACEMENT_TOKEN = '@@GROUPNAME@@';

    const BIND_METHOD_SEARCH = "search";
    const BIND_METHOD_DIRECT = "direct";
    
    const GROUP_SEARCH_BY_GROUP = "byGroup";
    const GROUP_SEARCH_BY_USER = "byUser";
    
    private static $_conn;
    private static $_userDn;
    private static $_groupsToRoles = array();
    private static $_configServer;
    private static $_configGroups;
    private static $_configOptions;

    private static $_username;
    private static $_password;

    private static $_userEmail = null;
    private static $_userMemberOf = array();
    private static $_userPrimaryGroupIds = array();
    private static $_userFirstName = null;
    private static $_userLastName = null;
    
    private static $request;
    
    private static $_bindMethod = null;
    
    
    
    
    /**
     * set the username and password for authentication
     * @return void
     */
    public function __construct() 
    {
        $front = Zend_Controller_Front::getInstance();
        
        self::$request = $front->getRequest();
        $login = self::$request->getParam('Login');
        
        self::$_username = $login['username'];
        self::$_password = $login['password'];
        
        self::$_configServer = Kms_Resource_Config::getConfiguration('auth', 'ldapServer');
        self::$_configGroups = Kms_Resource_Config::getConfiguration('auth', 'ldapGroups');
        self::$_configOptions = Kms_Resource_Config::getConfiguration('auth', 'ldapOptions');
        
        self::$_bindMethod = self::$_configServer->bindMethod;
//        exit;
        // initialize groups-to-roles mapping from KMS config
        if (!count(self::$_groupsToRoles))
        {
            $groupMatchingOrder = isset(self::$_configOptions->groupsMatchingOrder) ? explode(',', self::$_configOptions->groupsMatchingOrder) : array();
            $ldapGroupMapping = self::$_configGroups;
            foreach ($groupMatchingOrder as $configRoleName)
            {
                self::$_groupsToRoles[$configRoleName] = array();
                $searchGroups = isset(self::$_configGroups->$configRoleName) ? self::$_configGroups->$configRoleName : array();
                
                foreach ($searchGroups as $groupName)
                {
                    self::$_groupsToRoles[$configRoleName][] = strtolower($groupName);
                }
            }
        }
        
    }

    public function loginFormEnabled()
    {
        return true;
    }
    
    public function getLoginRedirectUrl()
    {
        return null;
    }

    public function getLogoutRedirectUrl()
    {
        return null;
    }
    
    private static function connect()
    {
        // initialize connection to ldap server
        if (!self::$_conn)
        {
            
            // construct the LDAP stream server URI
            $stream = self::$_configServer->protocol . '://' . self::$_configServer->host . ':' . self::$_configServer->port;
            //$stream = self::$_configServer->host . ':' . self::$_configServer->port;
            // if LDAPS is set, ignore certificate to simplify setup
            if (self::$_configServer->protocol == 'ldaps' || self::$_configServer->port == 636)
            {
                if(!putenv('LDAPTLS_REQCERT=never'))
                {
                    $err = 'could not put environment variable to allow connection to ldap over ssl';
                    Kms_Log::log('login: '.$err, Kms_Log::WARN);
                    throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE_UNCATEGORIZED);
                }
            }
            // connect to LDAP server
            $ds = ldap_connect($stream);
            if(!$ds)
            {
                $err = "Could not connect to LDAP server.";
                Kms_Log::log('login: '.$err, Kms_Log::WARN);
                throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
            }
            else
            {
                self::$_conn = $ds;
            }

            // set LDAP server protocol, default is 3
            ldap_set_option(self::$_conn, LDAP_OPT_PROTOCOL_VERSION, self::$_configServer->protocolVersion);
            
            // set LDAP referrals chasing to OFF
            ldap_set_option(self::$_conn, LDAP_OPT_REFERRALS, 0);
            
            
            if(self::$_bindMethod == self::BIND_METHOD_SEARCH)
            {
            
              
                // check if need to bind to ldap server prior to searching user
                $ldapSearchUser = self::$_configServer->searchUser->username;
                $ldapSearchPass = self::$_configServer->searchUser->password;


                if (!empty($ldapSearchUser) && !empty($ldapSearchPass))
                {
                    $searchBind = @ldap_bind(self::$_conn, $ldapSearchUser, $ldapSearchPass);
                    if (!$searchBind)
                    {
                        $extended_error = null;
                        if (ldap_get_option(self::$_conn, self::LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error))
                        {
                            $err = "Error Binding to LDAP: $extended_error";
                            Kms_Log::log('login: '.$err, Kms_Log::WARN);
                            throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
                        } 
                        else
                        {
                            $err = "Error Binding to LDAP: No additional information is available";
                            Kms_Log::log('login: '.$err, Kms_Log::WARN);
                            throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
                        }
                    }
                }
            }
        }
        
    }
    
    
    
    /**
     * main function for authenticating a user.
     * this function is called from Kms_Auth_Adapter when a user fills in the login form and Kms_Auth_AuthN_Ldap is set for authentication
     * 
     * @return bool
     */
    public function authenticateUser()
    {
        // connect
        self::connect();
        // do authentication using LDAP, POST params should contain the values

        if(self::authenticateThroughLdap())
        {
            return true;
        }
        else
        {
            // could not bind user - authentication failed
            if(ldap_get_option(self::$_conn, self::LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error))
            {
                Kms_Log::log('login: '.self::LOGIN_FAILURE_TEXT . ': '.$extended_error, Kms_Log::DEBUG);
            }
            else
            {
                Kms_Log::log('login: '.self::LOGIN_FAILURE_TEXT, Kms_Log::DEBUG);
            }
            return false;
        }
    }

    /**
     * Method required by Kms_Auth_Interface_AuthZ
     * Method returns user role on successful authorization or false on failure
     *
     * @param string $userId
     * @return mixed
     */
    public function authorizeUser($userId)
    {
        if(!isset(self::$_conn))
        {
            self::connect();
        }
        if(isset(self::$_configOptions->groupSearch) && self::$_configOptions->groupSearch == self::GROUP_SEARCH_BY_USER)
        {
            Kms_Log::log(__METHOD__.':'.__LINE__ . ' going to authorize user from user object', Kms_Log::DEBUG);
            $userRole = self::getUserRoleFromUser($userId);
        }
        elseif(isset(self::$_configOptions->groupSearch) && self::$_configOptions->groupSearch == self::GROUP_SEARCH_BY_GROUP)
        {
            Kms_Log::log(__METHOD__.':'.__LINE__ . ' going to authorize user from groups objects', Kms_Log::DEBUG);
            $userRole = self::getUserRoleFromGroups($userId);
        }
        Kms_Log::log(__METHOD__.':'.__LINE__ . ' decided user role as '.$userRole, Kms_Log::DEBUG);
        if (self::validateUserRole($userRole))
        {
            return $userRole;
        } 
        else
        {
            // role is not relevant for this mediaSpace instance
            Kms_Log::log('login: '.self::ROLE_FAILURE_TEXT, Kms_Log::DEBUG);
            return false;
        }
    }

    /**
     * search for the user DN based on username and user search pattern
     * if user DN found - try to bind with provided password
     * 
     * @param string $username
     * @param string @password
     * 
     * @return bool
     */
    private static function authenticateThroughLdap()
    {
        $bind = false;
        $userDn = self::getUserDn(self::$_username);
        
        if($userDn)
        {
            Kms_Log::log(__METHOD__." binding user with DN: $userDn");
            $bind = @ldap_bind(self::$_conn, $userDn, self::$_password);
        }
        // verify binding of user
        if ($bind)
        {
            // if binded successfuly, keep userDn in static to use in other functions
            self::$_userDn = $userDn;
            // authentication successful
            return true;
        }

        // authentication failed - user not found / password incorrect
        return false;
    }

    /**
     * method to return user DN either by constructing or by searching over LDAP
     * 
     * @param string $userId
     * @return string
     */
    private static function getUserDn($userId)
    {
        $userDn = false;
        if(self::$_bindMethod == self::BIND_METHOD_DIRECT)
        {
            // skip the search for the user
            $userDn = str_replace(self::USER_REPLACEMENT_TOKEN, $userId, self::$_configServer->directBind->userDnFormat);
        }
        else
        {
            $filterPattern = self::$_configServer->searchUser->userSearchQueryPattern; //(&(objectClass=person)(uid=@@USERNAME@@))

            if (strpos($filterPattern, self::USER_REPLACEMENT_TOKEN) === false)
            {
                $err = 'LDAP setting [auth].ldap.userSearchQueryPattern must include the username replacement token ' . self::USER_REPLACEMENT_TOKEN;
                Kms_Log::log($err, Kms_Log::WARN);
                throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
            }
            $filter = str_replace(self::USER_REPLACEMENT_TOKEN, $userId, $filterPattern);
            $fields = array('dn');

            $user = self::getUserRecord($filter, $fields);

            // if only 1 user is found, try to bind with the found DN and password
            if (count($user))
            {
                Kms_Log::log(__METHOD__.':'.__LINE__. ' returned user record '.Kms_Log::printData($user), Kms_Log::DEBUG);
                $userDn = $user['dn'];
            }
        }

        return $userDn;
    }

    /**
     * method to set self::$_userMemberOf according to configuration and fetched user record
     *
     * @param array $userRecord
     */
    private static function setMemberOf($userRecord)
    {
        $memberOfArrayKey = strtolower(self::$_configOptions->byUser->memberOfAttribute);
        if(isset(self::$_configOptions->byUser->memberOfAttribute) && self::$_configOptions->byUser->memberOfAttribute != '' && in_array($memberOfArrayKey, $userRecord))
        {
            self::$_userMemberOf = $userRecord[$memberOfArrayKey];
        }
    }

    /**
     * method to set self::$_userPrimaryGroupIds according to configuration and fetched user record
     *
     * @param array $userRecord
     */
    private static function setPrimaryGroupIds($userRecord)
    {
        $primaryGroupArrayKey = strtolower(self::$_configOptions->byUser->primaryGroupIdAttribute);
        if(isset(self::$_configOptions->byUser->primaryGroupIdAttribute) && self::$_configOptions->byUser->primaryGroupIdAttribute != '' && in_array($primaryGroupArrayKey, $userRecord))
        {
            self::$_userPrimaryGroupIds = $userRecord[$primaryGroupArrayKey];
        }
    }

    /**
     * method to set self::$_userEmail according to configuration and fetched user record
     *
     * @param array $userRecord
     */
    private static function setUserEmail($userRecord)
    {
        if(!is_null(self::$_configServer->emailAttribute) && self::$_configServer->emailAttribute != '' && in_array(self::$_configServer->emailAttribute, $userRecord))
        {
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' got record to extract email from: '.print_r($userRecord, true), Kms_Log::DEBUG);
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' setting email to '.$userRecord[self::$_configServer->emailAttribute][0]);
            self::$_userEmail = $userRecord[self::$_configServer->emailAttribute][0];
        }
        else
        {
            Kms_Log::log('some info on email sync:');
            Kms_Log::log('  email attribute is: ['.self::$_configServer->emailAttribute.'] is_null result is ['.((int)is_null(self::$_configServer->emailAttribute)).']');
            Kms_Log::log('  in_array result is: ['.((int)in_array(self::$_configServer->emailAttribute, $userRecord)).']');
            Kms_Log::log(' got record to extract email from: '.print_r($userRecord, true), Kms_Log::DEBUG);
        }
    }

    /**
     * method to set self::$_userFirstName according to configuration and fetched user record
     *
     * @param array $userRecord
     */
    private static function setFirstName($userRecord)
    {
        $firstNameAttribute = strtolower(self::$_configServer->firstNameAttribute);

        if(isset(self::$_configServer->firstNameAttribute) && self::$_configServer->firstNameAttribute != '' && in_array($firstNameAttribute, $userRecord))
        {
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' got record to extract first name from: '.print_r($userRecord, true), Kms_Log::DEBUG);
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' setting first name to '.$userRecord[$firstNameAttribute][0]);
            self::$_userFirstName = $userRecord[$firstNameAttribute][0];
        }
    }

    /**
     * method to set self::$_userMemberOf according to configuration and fetched user record
     *
     * @param array $userRecord
     */
    private static function setLastName($userRecord)
    {
        $lastNameAttribute = strtolower(self::$_configServer->lastNameAttribute);
        if(isset(self::$_configServer->lastNameAttribute) && self::$_configServer->lastNameAttribute != '' && in_array($lastNameAttribute, $userRecord))
        {
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' got record to extract last name from: '.print_r($userRecord, true), Kms_Log::DEBUG);
            Kms_Log::log(__METHOD__ .':'. __LINE__ . ' setting last name to '.$userRecord[$lastNameAttribute][0]);
            self::$_userLastName = $userRecord[$lastNameAttribute][0];
        }
    }

    /**
     * method to return array of additional attributes to fetch from the user record to save queries
     *
     * @return array
     */
    private static function getAdditionalFields()
    {
        $fields = array();
        if(!is_null(self::$_configServer->emailAttribute) && self::$_configServer->emailAttribute != '')
        {
            $fields[] = self::$_configServer->emailAttribute;
        }

        if(!is_null(self::$_configServer->firstNameAttribute) && self::$_configServer->firstNameAttribute != '')
        {
            $fields[] = self::$_configServer->firstNameAttribute;
        }

        if(!is_null(self::$_configServer->lastNameAttribute) && self::$_configServer->lastNameAttribute != '')
        {
            $fields[] = self::$_configServer->lastNameAttribute;
        }

        if(isset(self::$_configOptions->groupSearch) && self::$_configOptions->groupSearch == self::GROUP_SEARCH_BY_USER)
        {
            $fields[] = self::$_configOptions->byUser->memberOfAttribute;
            if(isset(self::$_configOptions->byUser->primaryGroupIdAttribute))
            {
                $fields[] = self::$_configOptions->byUser->primaryGroupIdAttribute;
            }
        }

        Kms_Log::log('passing additional fields to '.print_r($fields, true));
        return $fields;
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @return string
     */
    public function getUserId()
    {
        return self::$_username;
    }

    /**
     * construct a LDAP query to search for all the matched groups, according to the role-matching order
     * 
     * @return string
     */
    private static function buildLdapGroupSearchQuery()
    {
        $fullGroupQuery = self::$_configOptions->byGroup->groupSearchQuery;
        if (isset($fullGroupQuery) && !empty($fullGroupQuery))
            return $fullGroupQuery;

        // full group query is not defined, using patterns instead
        $query = self::$_configOptions->byGroup->groupSearchQueryPattern;
        if (strpos($query, self::GROUPS_REPLACEMENT_TOKEN) === false)
        {
            
            $err = 'LDAP setting ldap_group_search_query_pattern must include the username replacement token ' . self::GROUPS_REPLACEMENT_TOKEN;
            Kms_Log::log($err, Kms_Log::WARN);
            throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
        }
        
        $groupReplacementPattern = self::$_configOptions->byGroup->groupSearchEachGroupPattern;
        if (strpos($groupReplacementPattern, self::GROUP_NAME_REPLACEMENT_TOKEN) === false)
        {
            $err = 'LDAP setting ldap_group_search_each_group_pattern must include the username replacement token ' . self::GROUP_NAME_REPLACEMENT_TOKEN;
            Kms_Log::log($err, Kms_Log::WARN);
            throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
        }
        $groupsReplacement = "";

        // self::$_groupsToRoles is initiated according to matching order in configuration
        foreach (self::$_groupsToRoles as $roleName => $groupsForRole)
        {
            foreach ($groupsForRole as $groupName)
            {
                $groupsReplacement .= str_replace(self::GROUP_NAME_REPLACEMENT_TOKEN, $groupName, $groupReplacementPattern);
            }
        }
        $query = str_replace(self::GROUPS_REPLACEMENT_TOKEN, $groupsReplacement, $query);
        return $query;
    }


    /**
     * method to fetch user record from LDAP
     *
     * @param string $filter
     * @param array $attributes
     * return array returns user record array on success or empty array on failure
     */
    private static function getUserRecord($filter, $attributes = array())
    {
        $allAttributes = array_merge($attributes, self::getAdditionalFields());
        Kms_Log::log(__METHOD__.':'.__LINE__. ' querying LDAP with query ['.$filter.'] and attributes ['.Kms_Log::printData($allAttributes).']', Kms_Log::DEBUG);
        $sr = ldap_search(self::$_conn, self::$_configServer->baseDn, $filter, $allAttributes);
        $users = ldap_get_entries(self::$_conn, $sr);
        if($users['count'] == 1)
        {
            self::setFirstName($users[0]);
            self::setLastName($users[0]);
            self::setUserEmail($users[0]);
            self::setMemberOf($users[0]);
            self::setPrimaryGroupIds($users[0]);
            return $users[0];
        }
        else
        {
            if($users['count'] > 1) Kms_Log::log('Ldap search for user found '.$users['count'].' results', Kms_Log::WARN);
            return array();
        }
    }

    /**
     * method will get role from user record in LDAP.
     *
     * @param string $userId
     * @return string
     */
    private static function getUserRoleFromUser($userId)
    {
        // if memberOf was not fetched already when searching for the user (probably "direct bind") - search it now
        if(!count(self::$_userMemberOf))
        {
            Kms_Log::log(__METHOD__.':'.__LINE__ . ' we dont have membership info from user object '.Kms_Log::printData(self::$_userMemberOf), Kms_Log::DEBUG);
            $attributes = array(self::$_configOptions->byUser->memberOfAttribute);
            $filterPattern = self::$_configOptions->byUser->userSearchQueryPattern; //(&(objectClass=person)(uid=@@USERNAME@@))

            if (strpos($filterPattern, self::USER_REPLACEMENT_TOKEN) === false)
            {
                $err = 'LDAP setting [auth].ldap.userSearchQueryPattern must include the username replacement token ' . self::USER_REPLACEMENT_TOKEN;
                Kms_Log::log($err, Kms_Log::WARN);
                throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE);
            }
            $filter = str_replace(self::USER_REPLACEMENT_TOKEN, $userId, $filterPattern);

            $user = self::getUserRecord($filter, $attributes);

            if(!count($user))
            {
                Kms_Log::log(__METHOD__.':'.__LINE__ . ' no group membership results', Kms_Log::DEBUG);
                self::$_userMemberOf = array();
                self::$_userPrimaryGroupIds = array();
            }
        }

        $userGroup = false;
        $userGroups = array();
        if(count(self::$_userMemberOf))
        {
            foreach(self::$_userMemberOf as $key => $group)
            {
                if(is_numeric($key))
                {
                    $userGroups[] = self::extractGroupNameFromDn($group);
                }
            }
            // match the highest ranking role to the groups
            foreach(self::$_groupsToRoles as $role => $kmsGroups)
            {
                foreach($userGroups as $userGroup)
                {
                    if(in_array($userGroup,$kmsGroups))
                    {
                        return Kms_Plugin_Access::getRole($role);
                    }
                }
            }
            
        }

        // if we got here we didn't find any role, try to match by primaryGroupId if we're configured for that
        if(isset(self::$_configOptions->byUser->primaryGroupIdAttribute) && count(self::$_userPrimaryGroupIds))
        {
            $groupIds = self::$_userPrimaryGroupIds;
            unset($groupIds['count']);
            foreach(self::$_configGroups->matchByPrimaryGroupId as $groupIdToRole)
            {
                $groupId = $groupIdToRole->primaryGroupId;
                $role = $groupIdToRole->roleForGroup;
                if(in_array($groupId, $groupIds))
                {
                    return Kms_Plugin_Access::getRole($role);
                }
            }
        }
    }
    
    
    /**
     * find to which group this user belong, out of the role-matching groups configured.
     * 
     * @return string
     * @return bool
     */
    private static function getUserRoleFromGroups($userId)
    {
        if(!self::$_userDn) // not authenticated through LDAP - no user DN yet
        {
            self::$_userDn = self::getUserDn($userId);
        }
        $membersAttribute = self::$_configOptions->byGroup->groupMembershipAttribute;
        
        $filter = self::buildLdapGroupSearchQuery();
        // getting group properties from ldap:
        $fields = array($membersAttribute);
        Kms_Log::log(__METHOD__.':'.__LINE__. ' querying LDAP with filter: ['.$filter.']', Kms_Log::DEBUG);
        $sr = ldap_search(self::$_conn, self::$_configServer->baseDn, $filter, $fields);
        
        $groupsDetails = ldap_get_entries(self::$_conn, $sr);
        Kms_Log::log('ldap: fetched groups '.print_r($groupsDetails, true), Kms_Log::DUMP);
        $userGroups = array();
        // loop groups, assume LDAP result order is the same as ldap_groups_matching_order
        foreach ($groupsDetails as $key => $group)
        {
            if (!is_numeric($key))
                continue;
            $groupDn = $group['dn'];
            $groupMembers = isset($group[$membersAttribute]) ? $group[$membersAttribute] : array();
            
            // loop on members in group
            foreach ($groupMembers as $gmKey => $memberDn)
            {
                if (!is_numeric($gmKey))
                    continue;
                // if the authenticated userDn is equal to the memberDn belongs to this group - stop loop
                if (strtolower($memberDn) == strtolower(self::$_userDn))
                {
                    $userGroups[] = self::extractGroupNameFromDn($groupDn);
                    break;
                }
            }
        }
        // if no userGroup found - do not return any role, user does not belong to any of the matched groups
        Kms_Log::log('ldap: filtered groups '.print_r($userGroups, true), Kms_Log::DUMP);
        if (!count($userGroups))
        {
            return false;
        }

        $groupName = '';
        // get the group name out of the group DN
        foreach(self::$_groupsToRoles as $role => $groupsArr)
        {
            foreach($groupsArr as $groupToRole)
            {
                if(in_array($groupToRole, $userGroups))
                {
                    $groupName = $groupToRole;
                    break;
                }
            }
            if($groupName)
                break;
        }
        // get the role matched with this group
        $role = self::getRoleFromGroupName($groupName);
        
        
        return Kms_Plugin_Access::getRole($role);
    }

    /**
     * take group name (i.e. cn) out of the group DN
     * 
     * @param string $groupDn
     * 
     * @return string
     */
    private static function extractGroupNameFromDn($groupDn)
    {
        $dnParts = explode(',', $groupDn);
        foreach ($dnParts as $part)
        {
            list($type, $value) = explode('=', $part);
            if (strtolower($type) == 'cn')
            {
                return strtolower($value);
            }
        }
        return '';
    }

    /**
     * given a group name, find the matching role according to role-matching configurations
     * 
     * @param string $group
     * 
     * @return string
     * @return bool
     */
    private static function getRoleFromGroupName($group)
    {
        foreach (self::$_groupsToRoles as $role => $groups)
        {
            foreach ($groups as $groupName)
            {
                if ($groupName == $group)
                    return $role;
            }
        }
        return false;
    }

    private static function validateUserRole($role)
    {
        if ($role !== false)
            return true;
        return false;
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getEmail($userId)
    {
        // this is set only when the attribute setting has value. otherwise this will return null
        return self::$_userEmail;
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getFirstName($userId)
    {
        if(!isset(self::$_userFirstName) && isset(self::$_configServer->firstNameAttribute) && !empty(self::$_configServer->firstNameAttribute))
        {
            // getting here means user object was not fetched
            // if we are set to direct bind and fetch from group - this makes sense
            if(self::$_bindMethod == self::BIND_METHOD_DIRECT && isset(self::$_configOptions->groupSearch) && self::$_configOptions->groupSearch == self::GROUP_SEARCH_BY_GROUP)
            {
                $filter = null;
                if(isset(self::$_configOptions->byUser->userSearchQueryPattern) && !empty(self::$_configOptions->byUser->userSearchQueryPattern))
                {
                    $filter = str_replace(self::USER_REPLACEMENT_TOKEN, $userId, self::$_configOptions->byUser->userSearchQueryPattern);
                }
                elseif(isset(self::$_configServer->searchUser->userSearchQueryPattern) && !empty(self::$_configServer->searchUser->userSearchQueryPattern))
                {
                    $filter = str_replace(self::USER_REPLACEMENT_TOKEN, $userId, self::$_configServer->searchUser->userSearchQueryPattern);
                }
                else
                {
                    // we have no way to determine the user search filter because such was not configured
                    $cn = self::extractCnFromDn(self::$_userDn);
                    if(isset(self::$_userDn) && $cn)
                    {
                        $filter = "(cn=$cn)";
                    }
                    else
                    {
                        Kms_Log::log('LDAP: We have no way to determine search filter to find user record. no details to sync');
                    }
                }
                if(!is_null($filter))
                {
                    $user = self::getUserRecord($filter);
                }
            }
        }
        return self::$_userFirstName;
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getLastName($userId)
    {
        return self::$_userLastName;
    }

    /**
     * extract cn from DN
     */
    private static function extractCnFromDn($dn)
    {
        $parts = explode(',', $dn);
        $cnParts = explode('=', $parts[0]);
        if(strtolower($cnParts[0]) == 'cn')
        {
            return $cnParts[1];
        }

        return null;
    }
}

?>
