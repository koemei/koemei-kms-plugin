<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * test case for channelmembers model
 * @author talbone
 *
 */
class Channelmembers_Model_ChannelmembersTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public static function setUpBeforeClass()
    {
        echo "Starting tests on modules/channelmembers/models/Channelmembers.php\n";

        // ////////////////////
        // configurations
        // ////////////////////
        
        Kms_Resource_Cache::disableCache();
        
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'instanceId', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
        
        // ////////////////////
        // users
        // ////////////////////
        
        Kms_Test_Helper::setUpTestIdentity('testuser');
        
        $user['username'] = 'categorytestuser0';
        $user['firstname'] = 'first0';
        $user['lastname'] = 'last0';
        $user['email'] = 'test0.mediaspace@kaltura.com';
        $user['role'] = Kms_Plugin_Access::VIEWER_ROLE;
        $user['password'] = 'zZ!1zzzzz423';
        
        $model = Kms_Resource_Models::getUser();
        $model->add($user);
        
        $user['username'] = 'categorytestuser1';
        $user['firstname'] = 'first1';
        $user['lastname'] = 'last1';        
        $user['email'] = 'test1.mediaspace@kaltura.com';
        $user['role'] = Kms_Plugin_Access::VIEWER_ROLE;
        $user['password'] = 'zZ!1zzzzz423';
        
        $model = Kms_Resource_Models::getUser();
        $model->add($user);
        
        $user['username'] = 'categorytestuser2';
        $user['firstname'] = 'first2';
        $user['lastname'] = 'last2';        
        $user['email'] = 'test2.mediaspace@kaltura.com';
        $user['role'] = Kms_Plugin_Access::VIEWER_ROLE;
        $user['password'] = 'zZ!1zzzzz423';
        
        $model = Kms_Resource_Models::getUser();
        $model->add($user);
        
        // create the admin user differently
        $KalturaUser = new Kaltura_Client_Type_User();
        $KalturaUser->id = 'categorytestuser3';
        $KalturaUser->email = 'test3.mediaspace@kaltura.com';
        $KalturaUser->firstName = 'first3';
        $KalturaUser->lastName = 'last3';
        $KalturaUser->isAdmin = true;    // can't set this in userModel->add(), hence the reason for using userModel->addKalturaUser()
        
        $model = Kms_Resource_Models::getUser();
        $model->addKalturaUser($KalturaUser, Kms_Plugin_Access::PARTNER_ROLE);        
        
        // ////////////////////
        // channels 
        // ////////////////////
        
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();

        $category = new Kaltura_Client_Type_Category();
        $category->name = "unitTest";
        $category->privacyContext = 'unitTest';
        $rootCategory = $client->category->add($category);
        
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel one";
        $category->parentId = $rootCategory->id;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::ALL;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        $category->moderation = false;
        $category->owner = 'testuser';
        $channel = $client->category->add($category);
    }
    
    public static function tearDownAfterClass()
    {
        // configuration
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        
        // categories
        Kms_Test_Helper::tearDownTestCategories();
        
        // users
        $model = Kms_Resource_Models::getUser();
        $model->delete(array('categorytestuser0','categorytestuser1','categorytestuser2','categorytestuser3'));
        
        echo "\ndone\n";
    }
    
    /**
     * test getChannelMember()
     */
    public function testGetChannelMember()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
        Kms_Test_Helper::setUpTestIdentity('testuser');
         
        $client = Kms_Resource_Client::getAdminClient();
        
        $model = new Application_Model_Channel();
        $channel = $model->get('unitTest>channel one',true);
        $model = new Channelmembers_Model_Channelmembers();
        
        // non existing user
        
        $result = $model->getChannelMember($channel->id,'nonExistingCategoryUser');
        
        $this->assertNull($result,'non existing channem member found.');
        
        // non existing channel
        
        $result = $model->getChannelMember(0,'categorytestuser0');
        
        $this->assertNull($result,'non existing channem member found.');
        
        // existing user
        
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'categorytestuser0';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER;
        $categoryUser->updateMethod = Kaltura_Client_Enum_UpdateMethodType::MANUAL;
        
        $client->categoryUser->add($categoryUser);
        
        $result = $model->getChannelMember($channel->id,'categorytestuser0');
        
        $this->assertNotNull($result,'existing channem member not found.');
        
        $this->assertAttributeEquals($categoryUser->categoryId, 'categoryId', $result);
        $this->assertAttributeEquals($categoryUser->userId, 'userId', $result);
        $this->assertAttributeEquals($categoryUser->permissionLevel, 'permissionLevel', $result);
        $this->assertAttributeEquals($categoryUser->updateMethod, 'updateMethod', $result);
    }
    
   /**
    * test saveChannelMember()
    * 
    *@depends testGetChannelMember
    */ 
   public function testSaveChannelMember()
   {
       Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
       Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
       Kms_Test_Helper::setUpTestIdentity('testuser');
               
       $model = new Application_Model_Channel();
       $channel = $model->get('unitTest>channel one',true);
       $model = new Channelmembers_Model_Channelmembers();
        
       // new member - existing user
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser1';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER;
       
       $model->saveChannelMember($data);
        
       $member = $model->getChannelMember($channel->id, 'categorytestuser1');
               
       $this->assertAttributeEquals($data['categoryId'], 'categoryId', $member);
       $this->assertAttributeEquals($data['userId'], 'userId', $member);
       $this->assertAttributeEquals($data['permission'], 'permissionLevel', $member);
        
       // update existing member
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser1';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR;
        
       $model->saveChannelMember($data);
       
       $member = $model->getChannelMember($channel->id, 'categorytestuser1');
        
       $this->assertAttributeEquals($data['categoryId'], 'categoryId', $member);
       $this->assertAttributeEquals($data['userId'], 'userId', $member);
       $this->assertAttributeEquals($data['permission'], 'permissionLevel', $member);
       
       // new member - non existing user
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'nouser';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR;
       
       $this->setExpectedException('Kaltura_Client_Exception');
       
       $model->saveChannelMember($data);
   } 
    
   /**
    * test delChannelMember()
    * 
    * @depends testGetChannelMember
    */
   public function testDelChannelMember()
   {
       Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
       Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
       Kms_Test_Helper::setUpTestIdentity('testuser');        
        
       $model = new Application_Model_Channel();
       $channel = $model->get('unitTest>channel one',true);
       $model = new Channelmembers_Model_Channelmembers();
       
       // test the category user from the previous test (we have a dependency on it, so it should be there)
       
       $member = $model->getChannelMember($channel->id, 'categorytestuser0');
       
       $this->assertAttributeEquals($channel->id, 'categoryId', $member);
       $this->assertAttributeEquals('categorytestuser0', 'userId', $member);
       $this->assertAttributeEquals(Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER, 'permissionLevel', $member);

       // delete the channel member
       
       $result = $model->delChannelMember($channel->id, 'channel one', 'categorytestuser0');
       
       $this->assertTrue($result,'channel member delete failed');
       
       $member = $model->getChannelMember($channel->id, 'categorytestuser0');
        
       $this->assertNull($member,'channel member was not deleted');
   }
   
   /**
    * test getChannelUsers()
    * 
    * @depends testSaveChannelMember
    */
   public function testGetChannelUsers()
   {
       Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
       Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
       Kms_Test_Helper::setUpTestIdentity('testuser');
       
       $model = new Application_Model_Channel();
       $channel = $model->get('unitTest>channel one',true);
       $model = new Channelmembers_Model_Channelmembers();
        
       // create the channel members
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser0';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER;
       $model->saveChannelMember($data);
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser1';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR;
       $model->saveChannelMember($data);
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser2';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR;
       $model->saveChannelMember($data);
       
       $data['categoryId'] = $channel->id;
       $data['userId'] = 'categorytestuser3';
       $data['permission'] = Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER;
       $model->saveChannelMember($data);
       
       // test that they are all there - these four + the creator
       
       $members = $model->getChannelUsers($channel);
       
       $this->assertEquals(5, count($members),'member count differ');
       
       $this->assertArrayHasKey('testuser', $members);
       $this->assertArrayHasKey('categorytestuser0', $members);
       $this->assertArrayHasKey('categorytestuser1', $members);
       $this->assertArrayHasKey('categorytestuser2', $members);
       $this->assertArrayHasKey('categorytestuser3', $members);
        
       foreach ($members as  $member){
           $this->assertAttributeEquals('channel one', 'channelId', $member,'channel name incorrect');
       }
       
       $this->assertEquals($model->getChannelPermission(Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER), $members['testuser']->permission);
       $this->assertEquals($model->getChannelPermission(Kaltura_Client_Enum_CategoryUserPermissionLevel::MEMBER), $members['categorytestuser0']->permission);
       $this->assertEquals($model->getChannelPermission(Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR), $members['categorytestuser1']->permission);
       $this->assertEquals($model->getChannelPermission(Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR), $members['categorytestuser2']->permission);
       $this->assertEquals($model->getChannelPermission(Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER), $members['categorytestuser3']->permission);
   }
   
   /**
    * test getUserSuggestions()
    */
   public function testGetUserSuggestions()
   {
       Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
       Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
       Kms_Test_Helper::setUpTestIdentity('testuser');
       
       $model = new Channelmembers_Model_Channelmembers();
        
       // search by first name
       
       $suggestions = $model->getUserSuggestions('first');
       
       $this->assertEquals(4, count($suggestions),'user suggestions count differ');
        
       $users = array();
       foreach ($suggestions as $suggestion){
           $users[$suggestion->id] = $suggestion;
       }
       
       $this->assertArrayHasKey('categorytestuser0', $users);
       $this->assertArrayHasKey('categorytestuser1', $users);
       $this->assertArrayHasKey('categorytestuser2', $users);
       $this->assertArrayHasKey('categorytestuser3', $users);
       
       // search by last name
       
       $suggestions = $model->getUserSuggestions('last');
        
       $this->assertEquals(4, count($suggestions),'user suggestions count differ');
       
       $users = array();
       foreach ($suggestions as $suggestion){
           $users[$suggestion->id] = $suggestion;
       }
        
       $this->assertArrayHasKey('categorytestuser0', $users);
       $this->assertArrayHasKey('categorytestuser1', $users);
       $this->assertArrayHasKey('categorytestuser2', $users);
       $this->assertArrayHasKey('categorytestuser3', $users);
   }
}
