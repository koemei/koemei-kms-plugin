<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

// @todo - refactor this class to consolidate different actions and populate members when getting user from API
class Application_Model_User 
{
    const METADATA_ROLE_XPATH = '/metadata/role';
    const METADATA_SCHEMA_ROLE_FIELD_XPATH = "/*[local-name()='metadata']/*[local-name()='role']";

    private $id;
    private $role;
    private $expires = 0;
    
    private $_totalCount;

    public $user;
    
    /**
     *
     * @param string $id
     * @return Kaltura_Client_Type_User
     */
    public function get($id, $getModules = true)
    {
        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            $user = $client->user->get($id);

            $this->user = $user;
            $this->id = $id;
            $role = $this->getKalturaUserRole($id);
            $this->role = ($role != Kms_Plugin_Access::EMPTY_ROLE)? $role: null;


            Kms_Resource_Models::setUser($this);
            // execute the modules implementing "get" for user
            $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_User_Get');
        
        
            if($getModules)
            {
                foreach ($models as $model) 
                {
                    $model->get($this);
                }
            }

        }
        catch( Kaltura_Client_Exception $e )
        {
            Kms_Log::log('user: User Get - Error getting user '.$id.'. '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
        
        return $user;
    }
    
    
    public function setId($id)
    {
        if($id)
        {
            $this->id = $id;
        }
    }

    public function setRole($role)
    {
        if($role)
        {
            $this->role = $role;
        }
    }
    
    public function setExpires($expires)
    {
        if(is_numeric($expires))
        {
            $this->expires = $expires;
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getRole()
    {
        return $this->role;
    }
    
    public function getExpires()
    {
        return $this->expires;
    }
    
    /**
     * @param boolean $filterByInstance - should we filter users from other instances of KMS
     * @return Kaltura_Client_Type_UserFilter 
     */
    public static function getStandardFilter($filterByInstance = true)
    {
        $filter = new Kaltura_Client_Type_UserFilter();
        $filter->orderBy = Kaltura_Client_Enum_UserOrderBy::CREATED_AT_DESC;
        $filter->isAdminEqual = Kaltura_Client_Enum_NullableBoolean::FALSE_VALUE;

        if($filterByInstance){
            // filter only users with role for that instance
            $advancedSearch = new Kaltura_Client_Metadata_Type_MetadataSearchItem();
            $advancedSearch->metadataProfileId = self::getUserRolesProfileId();
            $advancedSearch->type = Kaltura_Client_Enum_SearchOperatorType::SEARCH_AND;
    
            $advancedSearchItem = new Kaltura_Client_Type_SearchCondition();
            $advancedSearchItem->field = self::METADATA_SCHEMA_ROLE_FIELD_XPATH;
            $advancedSearchItem->value = '*';
            $advancedSearch->items = array($advancedSearchItem);
      //      Zend_Debug::dump($advancedSearch);
        //    exit;
            $filter->advancedSearch = $advancedSearch;
        }
        
        return $filter;
    }

    /**
     * method gets a filter object and adds filtering by name
     * does not return since object  is passed by reference.
     */
    public static function addFilterByName(Kaltura_Client_Type_UserFilter $filter, $name)
    {
        $filter->firstNameOrLastNameStartsWith = $name;
    }

    /**
     * method gets a filter object and adds filtering by name
     * does not return since object  is passed by reference.
     */
    public static function addFilterByRole(Kaltura_Client_Type_UserFilter $filter, $role)
    {
        if(!isset($filter->advancedSearch))
        {
            $advancedSearch = new Kaltura_Client_Metadata_Type_MetadataSearchItem();
            $advancedSearch->metadataProfileId = self::getUserRolesProfileId();
            $advancedSearch->type = Kaltura_Client_Enum_SearchOperatorType::SEARCH_AND;

            $advancedSearchItem = new Kaltura_Client_Type_SearchCondition();
            $advancedSearchItem->field = self::METADATA_SCHEMA_ROLE_FIELD_XPATH;
            $advancedSearchItem->value = $role;
            $advancedSearch->items = array($advancedSearchItem);
            $filter->advancedSearch = $advancedSearch;
        }
        else
        {
            $filterAdded = false;
            foreach($filter->advancedSearch->items as $key => $item)
            {
                if($item->field == self::METADATA_SCHEMA_ROLE_FIELD_XPATH)
                {
                    $filter->advancedSearch->items[$key]->value = $role;
                    $filterAdded = true;
                }
            }
            if(!$filterAdded)
            {
                $advancedSearchItem = new Kaltura_Client_Type_SearchCondition();
                $advancedSearchItem->field = self::METADATA_SCHEMA_ROLE_FIELD_XPATH;
                $advancedSearchItem->value = $role;
                $filter->advancedSearch->items[] = $advancedSearchItem;
            }
        }
    }

    /**
     * method gets a filter object and adds filtering by name
     * does not return since object  is passed by reference.
     */
    public static function addFilterByEmail(Kaltura_Client_Type_UserFilter $filter, $email)
    {
        $filter->emailStartsWith = $email;
    }

    /**
     *
     * @param int $pageSize
     * @param int$page
     * @param Kaltura_Client_Type_UserFilter $filter
     * @return array array of Kaltura_Client_Type_User
     */
    public function getUsers($pageSize = 500, $page = 1, $filter = null)
    {
        $client = Kms_Resource_Client::getAdminClient();

        $pager = new Kaltura_Client_Type_FilterPager();

        if(is_null($filter) || !is_a($filter, 'Kaltura_Client_Type_UserFilter'))
        {
            $filter = self::getStandardFilter();
        }

        $pager->pageSize = $pageSize;
        $pager->pageIndex = $page;
        $users = $client->user->listAction($filter, $pager);
        if($users->objects && count($users->objects))
        {
            $this->_totalCount = $users->totalCount;
            $usersToHide = array('','0','1','batchUser',Kms_Resource_Config::getConfiguration('client', 'partnerId'));
            foreach($users->objects as $key => $user)
            {
                if(in_array($user->id, $usersToHide) || strpos($user->id, '__ADMIN__') === 0 )
                {
                    unset($users->objects[$key]);
                }
            }
            return $users->objects;
        }
        else
        {
            return null;
        }
    }
    
    public function add($user)
    {
        
        $KalturaUser = new Kaltura_Client_Type_User();
        $KalturaUser->id = $user['username'];
        $KalturaUser->email = $user['email'];
        
        // partner data consists of password, role, and extra data
        if(isset($user['password']) && trim($user['password']) != '')
        {
            $partnerData[] = 'pw='.sha1($user['password']);
        }
        /*$partnerData[] = 'role='.$user['role'];*/
        $partnerData[] = (isset($user['extradata']))? $user['extradata']: '';
        $KalturaUser->partnerData = join(',', $partnerData);
        $KalturaUser->firstName = (isset($user['firstname']))? $user['firstname'] : $user['username'];
        $KalturaUser->lastName = (isset($user['lastname']))? $user['lastname'] : $user['username'];
        //$KalturaUser->screenName = $KalturaUser->firstName .' '.$KalturaUser->lastName;
        
        $res = $this->addKalturaUser($KalturaUser, $user['role']);
        
        // call modules
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_User_Add');

        foreach ($models as $model)
        {
            $model->add($this);
        }
        return $res;
        
    }

    /**
     * method to call kaltura API to create user
     *
     * @param Kaltura_Client_Type_User $newuser
     * @return type
     */
    public function addKalturaUser(Kaltura_Client_Type_User $KalturaUser, $role)
    {
        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            try
            {
                $userExists = $this->get($KalturaUser->id);
            }
            catch(Exception $ex)
            {
                $userExists = false;
            }
            // if user does not exist in kaltura - add it
            if($userExists === false)
            {
                Kms_Log::log('user: User Add - User does not exist, creating '.Kms_Log::printData($KalturaUser));
                $res = $client->user->add($KalturaUser);
            }
            else
            {
                Kms_Log::log('user: User Add - User exists, updating '.Kms_Log::printData($userExists));
                $KalturaUser->id = null; // not updating KalturaUser->id since this is not an updatable field
                $res = $client->user->update($userExists->id, $KalturaUser);
            }
            
            if(isset($res->id))
            {
                $this->setKalturaUserRole($res->id, $role);
            }
            else
            {
                throw new Zend_Application_Exception('Could not create user');
            }

            
            
            $this->user = $res;
        }
        catch( Kaltura_Client_Exception $e )
        {
            Kms_Log::log('user: User Add - Error saving user '.$KalturaUser->id.'. '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
        return $res;
    }
    
    
    public function update($olduser, $newuser)
    {
        $KalturaUser = new Kaltura_Client_Type_User();
        if(isset($newuser['email']) && trim($newuser['email']) != '')
        {
            $KalturaUser->email = $newuser['email'];
        }
        
        
        // partner data consists of password, role, and extra data
        if(isset($newuser['password']) && trim($newuser['password']) != '')
        {
            $partnerData[] = 'pw='.sha1($newuser['password']);
        }
        else
        {
            $hashedPassword = Kms_Auth_AuthN_Kaltura::parsePassword($olduser->partnerData);
            // if password not provided we want to keep whatever is stored (if stored)
            if($hashedPassword)
            {
                $partnerData[] = 'pw='.$hashedPassword;
            }
        }
        /*$partnerData[] = 'role='.$newuser['role'];*/
        $partnerData[] = (isset($newuser['extradata']))? $newuser['extradata']: '';
        $KalturaUser->partnerData = join(',', $partnerData);
        $KalturaUser->firstName = (isset($newuser['firstname']))? $newuser['firstname'] : $olduser->firstName;
        $KalturaUser->lastName = (isset($newuser['lastname']))? $newuser['lastname'] : $olduser->lastName;
        //$KalturaUser->screenName = $KalturaUser->firstName .' '.$KalturaUser->lastName;
        
        if(!isset($newuser['role']) || empty($newuser['role']))
        {
            $newuser['role'] = false;
        }
        
        $res = $this->updateKalturaUser($olduser, $KalturaUser, $newuser['role']);
        $this->user = $res;

        // call modules 
        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_User_Update');

        foreach ($models as $model)
        {
            $model->update($this);
        }
        
        return $res;
    }

    public function updateKalturaUser(Kaltura_Client_Type_User $oldUser, Kaltura_Client_Type_User $newUser, $role)
    {
        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            $res = $client->user->update($oldUser->id, $newUser);

            $this->user = $res;
            // only set role if the given role is not FALSE.
            if($role !== false)
            {
                $this->setKalturaUserRole($oldUser->id, $role);
            }
        }
        catch( Kaltura_Client_Exception $e )
        {
            Kms_Log::log('user: User Add - Error saving user '.$oldUser->id.'. '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
        return $res;
    }

    public function setKalturaUserRole($userId, $role)
    {
        if(!$role)
        {
            throw new Zend_Application_Exception("cannot set user role to empty string");
        }
        $profileId = self::getUserRolesProfileId();

        $userMetadataResult = $this->getKalturaUsersMetadata(array($userId));
        if($userMetadataResult && count($userMetadataResult))
        {
            $userMetadata = $userMetadataResult[0];
        }
        else
        {
            $userMetadata = false;
        }
        
        try
        {
            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get(Kms_Resource_Client::getAdminClient());
            $xml = '<metadata><role>'.$role.'</role></metadata>';
            if($userMetadata === false)
            {
                $metadata = $metadataPlugin->metadata->add($profileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::USER , $userId, $xml);
            }
            elseif(isset($userMetadata->id))
            {
                $metadata = $metadataPlugin->metadata->update($userMetadata->id, $xml);
            }
        }
        catch(Kaltura_Client_Exception $ex)
        {
            Zend_Debug::dump($ex); die;
            Kms_Log::log('could not update user role with error '.$ex->getMessage());
            throw new Kaltura_Client_Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function getRoleFromMetadata($xml)
    {
        try
        {
            $xmlObj = new SimpleXMLElement($xml);
        }
        catch(Exception $ex)
        {
            // @todo - handle exception when XML is not valid
            return null;
        }
        $role = $xmlObj->xpath(self::METADATA_ROLE_XPATH);
        if(is_array($role) && count($role) == 1)
        {
            $roleValue = (string)$role[0];
        }
        
        return $roleValue;
    }

    public function getKalturaUserRole($userId)
    {
        $role = null;
        $metadata = $this->getKalturaUsersMetadata(array($userId));

        if(count($metadata))
        {
            $role = $this->getRoleFromMetadata($metadata[0]->xml);
        }
        if(!count($metadata) || !Kms_Plugin_Access::roleExists($role))
        {
            return Kms_Plugin_Access::EMPTY_ROLE;
        }
        else
        {
            return $role;
        }
    }

    public function getKalturaUsersRoles(array $userIds)
    {
    	$userIds = array_map('strtolower', $userIds);
        $usersRoles = array();
        $metadata = $this->getKalturaUsersMetadata($userIds);
        foreach($metadata as $metadataObj)
        {
            $role = $this->getRoleFromMetadata($metadataObj->xml);
            if($role/* && Kms_Plugin_Access::roleExists($role)*/)
            {
                $usersRoles[strtolower($metadataObj->objectId)] = $role;
            }
            else
            {
                $usersRoles[$metadataObj->objectId] = Kms_Plugin_Access::EMPTY_ROLE;
            }
        }
        if(count($usersRoles) != count($userIds))
        {
            foreach($userIds as $id)
            {
                if(!isset($usersRoles[$id]))
                {
                    $usersRoles[$id] = Kms_Plugin_Access::EMPTY_ROLE;
                }
            }
        }

        return $usersRoles;
    }


    public function getKalturaUsersMetadata(array $userIds)
    {
    	$userIds = array_map('strtolower', $userIds);
        $profileId = self::getUserRolesProfileId();
        
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get(Kms_Resource_Client::getAdminClient());
        $filter = new Kaltura_Client_Metadata_Type_MetadataFilter();
        $filter->objectIdIn = implode(',', $userIds);
        $filter->metadataObjectTypeEqual = Kaltura_Client_Metadata_Enum_MetadataObjectType::USER; // USER
        $filter->metadataProfileIdEqual = $profileId;
        try
        {
            $usersMetadata = $metadataPlugin->metadata->listAction($filter);
        }
        catch(Kaltura_Client_Exception $ex)
        {
            // @todo - handle error in API
            Zend_Debug::dump($ex); die;
        }

        if($usersMetadata && $usersMetadata->totalCount == 0)
        {
            return array(); // user has no role
        }
        elseif($usersMetadata)
        {
            // make sure that returned list of objects actually represent user IDs requested to overcome a bug in API (temp)
            foreach($usersMetadata->objects as $key => $obj)
            {
                if(!in_array(strtolower($obj->objectId), $userIds))
                {
                    unset($usersMetadata->objects[$key]);
                }
            }
        }
        return $usersMetadata->objects;
    }

    private static function  getUserRolesProfileId()
    {
        $profileId = Kms_Resource_Config::getConfiguration('application', 'userRoleProfile');
        if(!$profileId)
        {
            Kms_Log::log('config.ini is missing userRoleProfile value - something went wrong in installation');
            throw new Zend_Application_Exception('config.ini is missing userRoleProfile value');
        }
        return $profileId;
    }

    public function setKalturaUserPassword($userId, $email, $password, $isAdmin)
    {
        $client = $client = Kms_Resource_Client::getAdminClient();
        // @todo - handle KMC admin users - do we want to skip password update?
        if(!$isAdmin)
        {
            try
            {
                // first try to disable login - it is possible that this user doesn't have login data at all
                $client->user->disableLogin($userId, $email);
            }
            catch(Kaltura_Client_Exception $ex)
            {
            }
            catch(Exception $ex)
            {
                Zend_Debug::dump($ex); die;
            }

            try
            {
                // then set login data (whether was disabled or not doesn't really matter)
                $client->user->enableLogin($userId, $email, $password);
            }
            catch(Kaltura_Client_Exception $ex)
            {
                Kms_Log::log("could not update user password (disable/enable login) with error ".$ex->getMessage());
                throw new Kaltura_Client_Exception($ex->getMessage(), $ex->getCode());
            }
            catch(Exception $ex)
            {
                Zend_Debug::dump($ex); die;
            }
        }
    }

    public function delete($userIdArray)
    {
        $client = Kms_Resource_Client::getAdminClient();
        
        try
        {
            // get users' metadata for roles:
            $metadata = $this->getKalturaUsersMetadata($userIdArray);
            $client->startMultiRequest();
            $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
            foreach($metadata as $metadataObj)
            {
                //$res = $client->user->delete($userId);
                // delete onle metadata that match a user in the array - getKalturaUsersMetadata makes sure of that
                $metadataPlugin->metadata->delete($metadataObj->id);
            }
            $client->doMultiRequest();
        }
        catch( Kaltura_Client_Exception $e )
        {
            Kms_Log::log('user: User Delete - Error deleting user '.$userId.'. '.$e->getCode().': '.$e->getMessage(), Kms_Log::ERR);
            throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
        }
        
        return true;
    }

    public function getTotalCount()
    {
        return $this->_totalCount;
    }
}

