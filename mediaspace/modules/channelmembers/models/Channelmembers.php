<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * module to handle Channel Members.
 *
 * @author talbone
 *
 */
class Channelmembers_Model_Channelmembers extends Kms_Module_BaseModel implements Kms_Interface_Contextual_Role
{
    const MODULE_NAME = 'Channelmembers';
    private static $permissions;
    
    /* view hooks */
    public $viewHooks = array
    (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABLINKS => array(
                    'action' => 'tab',
                    'controller' => 'index',
                    'order' => 5
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABS => array(
                    'action' => 'index',
                    'controller' => 'index',
                    'order' => 10
            )
    );
    /* end view hooks */
    
    /**
     * (non-PHPdoc)
     * @see Kms_Module_BaseModel::getAccessRules()
     */
    public function getAccessRules()
    {
        $accessrules = array(
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('tab'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('index'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('add'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('edit'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('delete'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmembers:index',
                        'actions' => array('usersuggestions'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                )
        );
        return $accessrules;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Contextual_Role::getContextualAccessRuleForAction()
     */
    public function getContextualAccessRuleForAction($actionName)
    {    
        $contextualRule = false;
            
        if ($actionName == 'index' || $actionName == 'tab'){
            $contextualRule =  new Kms_Module_Contextual_Access_Channel(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER,
                    array('channelname', 'channelid'),
                    false
            );
        }
    
        return $contextualRule;
    }
    
    /**
     * get the channel permission conversion array.
     * use lazy valuation.
     */
    public static function getChannelPermissions()
    {
        if (empty(self::$permissions)){
            $translate = Zend_Registry::get('Zend_Translate');
            self::$permissions = array(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER  => $translate->translate('Member'),
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR => $translate->translate('Contributor'),
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR => $translate->translate('Moderator'),
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER => $translate->translate('Manager')
            );
        }
        return self::$permissions;
    }
    
    /**
     * convert a categoryUser permission leve to permission as string
     * @param int $level
     * @return string
     */
    public function getChannelPermission($level)
    {
        $permision = '';
        $permissions = self::getChannelPermissions();
        
        if (!empty($permissions[$level])){
            $permision = $permissions[$level];
        }
    
        return $permision;
    }
    
    
    /**
     * @param string $channelName
     * @param string $channelId
     * @return Kaltura_Client_Type_Category, NULL
     */
    public function getChannel($channelName = '', $channelId = null)
    {
        $model = Kms_Resource_Models::getChannel();
        return $model->getCurrent($channelName, $channelId);
    }
    
    /**
     * get suggestions from the api regarding users
     * @param unknown_type $term
     * @return Ambigous <multitype:string , multitype:NULL >
     */
    public function getUserSuggestions($term = '')
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $results = array($translate->translate('No Results'));
        $model = Kms_Resource_Models::getUser();
        $filter = Application_Model_User::getStandardFilter(false);
        Application_Model_User::addFilterByName($filter, $term);
        $filter->isAdminEqual = Kaltura_Client_Enum_NullableBoolean::NULL_VALUE; // for both admin and regular users
        
        return $model->getUsers(Application_Model_Entry::MY_MEDIA_LIST_PAGE_SIZE,1,$filter);     
    }
    
    
    /**
     * get the channel users
     * @param Kaltura_Client_Type_Category $channel
     * @param unknown_type $userType
     * @param array $params
     * @return multitype:
     */
    public function getChannelUsers(Kaltura_Client_Type_Category $channel, $userType = Application_Model_Channel::CHANNEL_USER_TYPE_ALL, array $params = array())
    {
        $fullMembers = array();
        
        // get the category user objects
        $model = Kms_Resource_Models::getChannel();
        $members = $model->getChannelUsers($channel,$userType, $params);

        // get the users for the full user name
        $model = Kms_Resource_Models::getUser();
        $filter = Application_Model_User::getStandardFilter(false);
        $filter->isAdminEqual = Kaltura_Client_Enum_NullableBoolean::NULL_VALUE; // for both admin and regular users
        
        foreach ($members as $member){
            // save the users by their id so we can find them later on
            $fullMembers[$member->userId] = $member;
            $filter->idIn .= ',' . $member->userId;
        }
        $filter->idIn = trim($filter->idIn,',');
                
        if (count($members)) {
            $users = $model->getUsers(count($members),1,$filter);
            if( !empty($users)){
                foreach ($users as $user){
                    if(isset($fullMembers[$user->id]))
                    {
                        $fullMembers[$user->id]->fullName = $user->fullName;
                        $fullMembers[$user->id]->channelId = $channel->name;
                        $fullMembers[$user->id]->permission = $this->getChannelPermission($fullMembers[$user->id]->permissionLevel);
                    }
                }
            }
        }
        
        return $fullMembers;
    }
    
    /**
     * get the total count of the last model action 
     */
    public function getTotalCount()
    {
        $model = Kms_Resource_Models::getChannel();
        return $model->getTotalCount();
    }
    
    /**
     * save a channel member using the api
     * @param unknown_type $data
     * @throws Kaltura_Client_Exception
     * @return Ambigous <unknown, NULL, unknown, string, multitype:Ambigous <string, unknown> >
     */
    public function saveChannelMember($data)
    {
        $client = Kms_Resource_Client::getUserClient();
        
        $currentMember = $this->getChannelMember($data['categoryId'],$data['userId']);
        
        if (empty($currentMember))
        {
            // new member
            try {
                // make sure we are creating a member for an existing user - we want to avoid creating new users
                // this is done here to catch the remote possibility of someone modifying the edit form to create a user
                $model = Kms_Resource_Models::getUser();
                $user = $model->get($data['userId'],false);
           
                // user exists - create a category user for it
                $categoryUser = new Kaltura_Client_Type_CategoryUser();
                $categoryUser->categoryId = $data['categoryId'];
                $categoryUser->userId = $data['userId'];
                $categoryUser->permissionLevel = $data['permission'];
                $categoryUser->updateMethod = Kaltura_Client_Enum_UpdateMethodType::MANUAL;
                
                Kms_Log::log('channelmembers: add categoryUser ' . $data['categoryId'] . ' ' . $data['userId'] , Kms_Log::DEBUG);
                $results = $client->categoryUser->add($categoryUser);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('channelmembers: Failed to add categoryUser ' .$e->getCode().': '.$e->getMessage(), Kms_Log::INFO);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        else
        {
            // existing member
            $categoryUser = new Kaltura_Client_Type_CategoryUser();
            $categoryUser->categoryId = $currentMember->categoryId;
            $categoryUser->userId = $currentMember->userId;
            $categoryUser->permissionLevel = $data['permission'];
            $categoryUser->updateMethod = Kaltura_Client_Enum_UpdateMethodType::MANUAL;
                        
            try
            {
                Kms_Log::log('channelmembers: update categoryUser ' . $data['categoryId'] . ' ' . $data['userId'] , Kms_Log::DEBUG);
                
                $results = $client->categoryUser->update($data['categoryId'], $data['userId'], $categoryUser, true);
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('channelmembers: Failed to update categoryUser ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        if (empty($results)){
            $results = new $categoryUser();
        }
        
        // clean the cache
        $cacheTags[] = 'userId_' . $data['userId'];
        $cacheTags[] = 'categoryUser_' . $data['userId'];
        $cacheTags[] = 'categoryId_' . $data['categoryId'];
        Kms_Resource_Cache::apiclean('categoryUser', array(), $cacheTags);
        
        return $results;
    }
    
    /**
     * get the channel member from the api
     * @param unknown_type $channelId channel id, NOT name
     * @param unknown_type $userId
     * @throws Kaltura_Client_Exception
     */
    public function getChannelMember($channelId = '', $userId = '')
    {        
        $client = Kms_Resource_Client::getUserClient();
        $member = null;
        
        try
        {
            $member = $client->categoryUser->get($channelId, $userId);
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('channelmembers: no categoryUser ' .$e->getCode().': '.$e->getMessage(), Kms_Log::INFO);
        }
                
        return $member;
    }
    
    
    /**
     * delete a channel member using the api
     * @param int $categoryId    - for the delete
     * @param string $userId     - for the delete
     * @throws Kaltura_Client_Exception
     */
    public function delChannelMember($categoryId ,$userId)
    {        
        $results = false;
        $client = Kms_Resource_Client::getUserClient();
        
        // check if the user exists
        $currentMember = $this->getChannelMember($categoryId,$userId);
        
        if (!empty($currentMember))
        {
            try
            {
                Kms_Log::log('channelmembers: delete categoryUser ' . $categoryId . ' ' . $userId , Kms_Log::DEBUG);
            
                $client->categoryUser->delete($categoryId, $userId);
                $results = true;
            }
            catch (Kaltura_Client_Exception $e)
            {
                Kms_Log::log('channelmembers: Failed to delete categoryUser ' .$e->getCode().': '.$e->getMessage(), Kms_Log::WARN);
                throw new Kaltura_Client_Exception($e->getMessage(), $e->getCode());
            }
        }
        
        // clean the cache
        $cacheTags[] = 'userId_' . $userId;
        $cacheTags[] = 'categoryUser_' . $userId;
        $cacheTags[] = 'categoryId_' . $categoryId;
        Kms_Resource_Cache::apiclean('categoryUser', array(), $cacheTags);
        
        return $results;
    }
}