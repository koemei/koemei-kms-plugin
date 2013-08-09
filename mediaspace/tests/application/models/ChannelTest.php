<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Application_Model_ChannelTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private static $validTestCategory;
    private static $partialTestCategory;
    private static $emptyTestcategory;
    private static $rootCategory;
    private static $rootChannelId;
    private static $metadataProfileId;
    
    public function setUp()
    {
        parent::setUp();
        
        // for entitlement
        Kms_Resource_Config::setConfiguration('application', 'instanceId', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
        
        Kms_Resource_Client::reInitClients();
    }
    
    public static function setUpBeforeClass()
    {
        echo "Starting tests on models/Channel.php\n";
        Kms_Resource_Cache::disableCache();
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'instanceId', 'unitTest');
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'unitTest');
        
        // create root category
        echo "Creating categories for tests...\n";
    
        self::$validTestCategory = 'CategoryUnitTestValid' . rand(1, 1000);
        self::$partialTestCategory = 'CategoryUnitTestEmpty' . rand(1, 1000);
        self::$emptyTestcategory = 'CategoryUnitTestPartial' . rand(1, 1000);
    
        self::createValidCategoryTree();
        self::createEmptyCategoryTree();
        self::createPartialCategoryTree();
    
        self::createTestChannels();
        self::createTestProfile();
        self::createTestUrlList();
                
        // create an identity
        Kms_Test_Helper::setUpTestIdentity('testuser');
        
        // create test entry
        Kms_Test_Helper::createTestImageEntries(1);        
        
        echo "done\n";
    }
    
    public static function tearDownAfterClass()
    {
        Kms_Resource_Cache::disableCache();
       // Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        Kms_Test_Helper::tearDownTestEntries();
       // Kms_Test_Helper::tearDownTestIdentity('testuser');
    
        self::deleteTestUrlList();
        self::deleteTestProfile();
        self::deleteCategoryTrees();
        self::deleteTestChannels();
        
        
        echo "Finished tests on models/Channel.php\n-----------------------------------------\n";
    }
    
    /**
     * create a valid category tree
     */
    private static function createValidCategoryTree()
    {
        $rootCategory = Kms_Test_Helper::createTestRootCategory(self::$validTestCategory, Kms_Resource_Config::getCategoryContext());
    
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $category = new Kaltura_Client_Type_Category();
    
        $category->name = "private";
        $category->parentId = $rootCategory->id;
        $client->category->add($category);
    
        $category->name = "archive";
        $category->parentId = $rootCategory->id;
        $client->category->add($category);
    
        $category->name = "site";
        $category->parentId = $rootCategory->id;
        $siteCategory = $client->category->add($category);
    
        $category->name = "galleries";
        $category->parentId = $siteCategory->id;
        $client->category->add($category);
    
        $category->name = "channels";
        $category->parentId = $siteCategory->id;
        $siteCategory = $client->category->add($category);
    
        self::$rootCategory = $rootCategory;
        self::$rootChannelId = $siteCategory->id;
    }
    
    /**
     * create an empty category tree
     */
    private static function createEmptyCategoryTree()
    {
        $rootCategory = Kms_Test_Helper::createTestRootCategory(self::$emptyTestcategory, Kms_Resource_Config::getCategoryContext());
    }
    
    /**
     * create a partialy correct category tree - no archive, site->channels.
     */
    private static function createPartialCategoryTree()
    {
        $rootCategory = Kms_Test_Helper::createTestRootCategory(self::$partialTestCategory, Kms_Resource_Config::getCategoryContext());
    
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $category = new Kaltura_Client_Type_Category();
    
        $category->name = "private";
        $category->parentId = $rootCategory->id;
        $client->category->add($category);
    
        $category->name = "site";
        $category->parentId = $rootCategory->id;
        $siteCategory = $client->category->add($category);
    
        $category->name = "galleries";
        $category->parentId = $siteCategory->id;
        $client->category->add($category);
    }
    
    
    /**
     * create us some test channels
     */
    private static function createTestChannels()
    {
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $category = new Kaltura_Client_Type_Category();
        
        
        $user['username'] = 'categorytestuser';
        $user['email'] = 'test.mediaspace@kaltura.com';
        $user['role'] = Kms_Plugin_Access::VIEWER_ROLE;
        $user['password'] = 'zZ!1zzzzz423';
      
        $model = Kms_Resource_Models::getUser();
        $model->add($user);
        
        // restricted channel
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel one";
        $category->parentId = self::$rootChannelId;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        $category->moderation = true;
        $channel = $client->category->add($category);
        
        // add the user to the channel as a member
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'categorytestuser';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR;
        $result = $client->categoryUser->add($categoryUser);
        
        // add the user to the channel as a member
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'testuser';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR;
        $result = $client->categoryUser->add($categoryUser);
        
        // restricted channel
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel two";
        $category->parentId = self::$rootChannelId;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        $channel = $client->category->add($category);
        
        // add the user to the channel as a member
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'categorytestuser';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR;
        $result = $client->categoryUser->add($categoryUser);
        
        // restricted channel
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel three";
        $category->parentId = self::$rootChannelId;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        $channel = $client->category->add($category);
        
        // add the user to the channel as an admin
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'categorytestuser';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR;
        $result = $client->categoryUser->add($categoryUser);
        
        // private channel
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel four";
        $category->parentId = self::$rootChannelId;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MEMBERS_WITH_CONTRIBUTION_PERMISSION;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::CATEGORY_MEMBERS_ONLY;
        $channel = $client->category->add($category);
        
        // add the user to the channel as an admin
        $categoryUser = new Kaltura_Client_Type_CategoryUser();
        $categoryUser->categoryId = $channel->id;
        $categoryUser->userId = 'categorytestuser';
        $categoryUser->permissionLevel = Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER;
        $result = $client->categoryUser->add($categoryUser);
                
        // open channel
        $category = new Kaltura_Client_Type_Category();
        $category->name = "channel five";
        $category->parentId = self::$rootChannelId;
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::ALL;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        $channel = $client->category->add($category);
        
        // dont add the user - open channel
    }
    
    /**
     * create a metadata profile for test
     */
    private static function createTestProfile()
    {
        //global $unitTestSchema;
        $iniPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'deployment' . DIRECTORY_SEPARATOR . 'metadataProfiles' . DIRECTORY_SEPARATOR . 'channelThumbnails.xml';        
        $unitTestSchema = file_get_contents($iniPath);
                        
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
    
        $metadataProfile = new Kaltura_Client_Metadata_Type_MetadataProfile();
        $metadataProfile->name = "channel topic test metadata";
        $metadataProfile->metadataObjectType = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;
    
        $metadataProfile = $metadataPlugin->metadataProfile->add($metadataProfile,$unitTestSchema);
    
        self::$metadataProfileId = $metadataProfile->id;
    }
    
    /**
     * create test metadata object for a channel
     */
    private static function createTestUrlList()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        Kms_Resource_Config::setConfiguration('channels', 'channelThumbnailProfileId', self::$metadataProfileId);
        
        $profileId = self::$metadataProfileId;
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->get('channel one');
                
        // create the metadata object
        $urls = array('url one', 'url two');
        $customDataXML = new SimpleXMLElement('<metadata/>');
        foreach ($urls as $url){
            $customDataXML->addChild('ChannelThumbnails', $url);
        }
        
        $result = $metadataPlugin->metadata->add($profileId,Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, $channel->id, $customDataXML->asXML());
    }
    
    /**
     * delete the test metadata object
     */
    private static function deleteTestUrlList()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        
        $profileId = self::$metadataProfileId;
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->get('channel one');
        
        //$metadataPlugin->metadata->delete($channel->id);
    }
    
    /**
     * delete the test metadata profile
     */
    private static function deleteTestProfile()
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $metadataPlugin->metadataProfile->delete(self::$metadataProfileId);
    }
    
    /**
     * delete the various category trees created for the channel tests
     */
    private static function deleteCategoryTrees()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        Kms_Test_Helper::tearDownTestCategories();
    
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$emptyTestcategory);
        Kms_Test_Helper::tearDownTestCategories();
    
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$partialTestCategory);
        Kms_Test_Helper::tearDownTestCategories();
    }
    
    /**
     * delete the test channels
     */
    private static function deleteTestChannels()
    {
        $model = Kms_Resource_Models::getUser();
        $model->delete(array('categorytestuser'));
    }
    
    /*******************************************************/
    
    /**
     *  START THE TESTS HERE
     */
    
    
    /**
     * test the getChannel function
     */
    public function testGetChannel()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        $model = Kms_Resource_Models::getChannel();
        
        // test with one context       
        $channel = $model->get(self::$validTestCategory,true);
        $this->assertFalse(empty($channel),'No channel found. Check Entitlement settings.');
        
        // add a context to the category
        Kms_Resource_Config::setConfiguration('application', 'instanceId', 'testcontext3');
        $model->validateRootCategoryContext(self::$validTestCategory);
        
        // test with context no. one
        $channel = $model->get(self::$validTestCategory,true);
        $this->assertFalse(empty($channel),'No channel found. Check Entitlement settings.');
        
        // test with context no. two
        Kms_Resource_Client::reInitClients();
        Kms_Resource_Models::setCategory(new Application_Model_Category());
        $model = new Application_Model_Channel();
        
        $client = Kms_Resource_Client::getUserClient();
        $client->getConfig()->getLogger()->log('========================== testGetChannel >> ==========================');
        $channel = $model->get(self::$validTestCategory,true);
        $client->getConfig()->getLogger()->log('========================== testGetChannel << ==========================');
        
        $this->assertFalse(empty($channel),'No channel found. Check Entitlement settings.');
    }

   
    
    /**
     * test my channels
     */
    public function testGetMyChannelList()
    {
        // set the configuration
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        Kms_Resource_Config::setConfiguration('channels', 'pageSize', 1); 
        Kms_Test_Helper::setUpTestIdentity('categorytestuser');
        $model = Kms_Resource_Models::getChannel();
        
        
        // test the member list
        $channels = $model->getMyChannelsList(array());              
        $channel = array_pop($channels);
        
        $this->assertAttributeContains('channel one', 'name', $channel,'channel name incorrect');
        $this->assertEquals(3, $model->getTotalCount(), 'total count incorrect');
        
        $channels = $model->getMyChannelsList(array('page' => 2));        
        $channel = array_pop($channels);
        
        $this->assertAttributeContains('channel two', 'name', $channel,'channel name incorrect');
        $this->assertEquals(3, $model->getTotalCount(), 'total count incorrect');
        
       // test the admin list
        $channels = $model->getMyChannelsList(array('type' => 'manager'));
        $channel = array_pop($channels);
        
        $this->assertAttributeContains('channel four', 'name', $channel,'channel name incorrect');
        $this->assertEquals(1, $model->getTotalCount() == 1, 'total count incorrect');
    }
    
    /**
     * test getMyChannelCount
     */
    public function testGetMyChannelCount()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        Kms_Test_Helper::setUpTestIdentity('categorytestuser');
        
        $model = Kms_Resource_Models::getChannel();
        
        $myChannelCount = $model->getMyChannelsCount();
        
        $this->assertEquals(3,$myChannelCount['member'], 'member count incorrect ' . $myChannelCount['member']);
        $this->assertEquals(1,$myChannelCount['manager'], 'manager count incorrect ' . $myChannelCount['manager']);        
    }
    
    /**
     * test that we are getting the expected role
     */
    public function testGetUserRoleInChannel()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        
        $model = Kms_Resource_Models::getChannel();
        
        // correct user and category
        $role = $model->getUserRoleInChannel('channel one', 'categorytestuser');
        $this->assertNotNull($role, 'no role for user');
        $this->assertTrue($role == Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR,'roles dont match '. $role);
    
        // incorrect user and category
        $role = $model->getUserRoleInChannel('channel one', 'no user');
        $this->assertEquals(Application_Model_Category::CATEGORY_USER_NO_ROLE, $role,'role exist but should not');
                
        // incorrect user and private category - as the incorrect user
        $role = $model->getUserRoleInChannel('channel four', 'no user');
        $this->assertEquals(Application_Model_Category::CATEGORY_USER_NO_ROLE, $role,'role exist but should not');
    }
    
    /**
     * test that the validation is correct
     */
    public function testValidateRootCategory()
    {
        $model = Kms_Resource_Models::getChannel();
    
        // valid tree
        $created_categories = $model->validateRootCategoryStructure(self::$validTestCategory);
    
        $this->assertTrue(empty($created_categories),'there were missing categories ' . implode($created_categories,' '));
    
        // empty tree
        $created_categories = $model->validateRootCategoryStructure(self::$emptyTestcategory);
    
        $this->assertArrayHasKey(self::$emptyTestcategory .'>site', $created_categories,'site not created');
        $this->assertArrayHasKey(self::$emptyTestcategory .'>private', $created_categories,'private not created');
        $this->assertArrayHasKey(self::$emptyTestcategory .'>archive', $created_categories,'archive not created');
        $this->assertArrayHasKey(self::$emptyTestcategory .'>site>galleries', $created_categories,'galleries not created');
        $this->assertArrayHasKey(self::$emptyTestcategory .'>site>channels', $created_categories,'channels not created');
    
        // partial tree
        $created_categories = $model->validateRootCategoryStructure(self::$partialTestCategory);
    
        $this->assertArrayNotHasKey(self::$partialTestCategory .'>site', $created_categories,'site not created');
        $this->assertArrayNotHasKey(self::$partialTestCategory .'>private', $created_categories,'private not created');
        $this->assertArrayHasKey(self::$partialTestCategory .'>archive', $created_categories,'archive created');
        $this->assertArrayNotHasKey(self::$partialTestCategory .'>site>galleries', $created_categories,'galleries not created');
        $this->assertArrayHasKey(self::$partialTestCategory .'>site>channels', $created_categories,'channels created');
    }
    
    /**
     * test that the privacy context is set
     */
    public function testValidateRootCategorycontext()
    {        
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $model = Kms_Resource_Models::getChannel();
    
        // add context to a category
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'testcontext');
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
    
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        
        $model->validateRootCategoryContext(self::$validTestCategory);
                        
        $filter->fullNameEqual = self::$validTestCategory;
        $category = $client->category->listAction($filter);
                
        $category = $category->objects[0];
        $privacyContext = explode(',', $category->privacyContext);
        $privacyContext = array_flip($privacyContext);
    
        $this->assertArrayHasKey('testcontext', $privacyContext);
    
        // add another context to the same category
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'testcontext2');
    
        $model->validateRootCategoryContext(self::$validTestCategory);
    
        $filter->fullNameEqual = self::$validTestCategory;
        $category = $client->category->listAction($filter);
        $category = $category->objects[0];
        $privacyContext = explode(',', $category->privacyContext);
        $privacyContext = array_flip($privacyContext);
    
        $this->assertArrayHasKey('testcontext', $privacyContext);
        $this->assertArrayHasKey('testcontext2', $privacyContext);
    
        // add context to a new category, and remove it from the old
        Kms_Resource_Config::setConfiguration('application', 'privacyContext', 'testcontext');
    
        $model->validateRootCategoryContext(self::$emptyTestcategory);
    
        $filter->fullNameEqual = self::$emptyTestcategory;
        $category = $client->category->listAction($filter);
        $category = $category->objects[0];
        $privacyContext = explode(',', $category->privacyContext);
        $privacyContext = array_flip($privacyContext);
    
        $this->assertArrayHasKey('testcontext', $privacyContext);
    
        $filter->fullNameEqual = self::$validTestCategory;
        $category = $client->category->listAction($filter);
        $category = $category->objects[0];
        $privacyContext = explode(',', $category->privacyContext);
        $privacyContext = array_flip($privacyContext);
    
        $this->assertArrayNotHasKey('testcontext', $privacyContext);
        $this->assertArrayHasKey('testcontext2', $privacyContext);
        
    }
    
    /**
     * test that we are getting the correct url list
     */
    public function testGetChannelThumbnails()
    {
        $profileId = self::$metadataProfileId;
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $model = Kms_Resource_Models::getChannel();
        
        $client->getConfig()->getLogger()->log('========================== testGetChannelThumbnails >> ==========================');
        $channel = $model->get('channel one');
        $client->getConfig()->getLogger()->log('========================== testGetChannelThumbnails << ==========================');
        
        $this->assertFalse(empty($channel),'No channel found. Check Entitlement settings.');
        
        // get the metadata object
        $channels = $model->getChannelThumbnails(array($channel));    
        $channel = $channels[0];
        
        $this->assertFalse(empty($channel->thumbnails),'No thumbnails for channel');        
        
        $thumbnails = array_flip($channel->thumbnails);
        
        $this->assertArrayHasKey('url one', $thumbnails);
        $this->assertArrayHasKey('url two', $thumbnails);
        
        // channel with no thumbnails metadata
        $channel = $model->get('channel two');
        
        // get the metadata object
        $channels = $model->getChannelThumbnails(array($channel));
        $channel = $channels[0];
        
        $this->assertTrue(empty($channel->thumbnails),'No thumbnails for channel');        
    }
    
    /**
     * test that the thumbnail metadata was created 
     */
    public function testCreateChannelThumbnails()
    {
        Kms_Resource_Config::setConfiguration('channels', 'ChannelThumbnailProfileId' , self::$metadataProfileId);
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        $model = Kms_Resource_Models::getChannel();

        $channel = $model->get('channel one');
                
        $this->assertFalse(empty($channel),'No channel found. Check Entitlement settings.');
        
        $model->createChannelThumbnails($channel);
        
        $thumbnails = $model->getChannelThumbnails(array($channel));
        //$thumbnails = array_flip($thumbnails);
        
        // do not test - convertion take forever
        //Zend_Debug::dump($thumbnails);
        //$this->assertArrayHasKey('test thumbnail url', $thumbnails);
    }
   
    public function testGetChannelsForPublish()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$rootCategory->name);
        Kms_Test_Helper::setUpTestIdentity('categorytestuser');
        $model = Kms_Resource_Models::getChannel();
        
        $client = Kms_Resource_Client::getAdminClient();
        $client->getConfig()->getLogger()->log('========================== testGetChannelsForPublish >> ==========================');
        $channels = $model->getChannelsForPublish();
        $client->getConfig()->getLogger()->log('========================== testGetChannelsForPublish << ==========================');
        
        $this->assertEquals(5, count($channels), 'wrong number of channels to publish');
        
        // open channel
        $channel = $model->get('channel five');
        $this->assertArrayHasKey($channel->id, $channels);
                        
        // private channels
        $channel = $model->get('channel four');
        $this->assertArrayHasKey($channel->id, $channels);
                
        $channel = $model->get('channel two');
        $this->assertArrayHasKey($channel->id, $channels);
        
        $channel = $model->get('channel three');
        $this->assertArrayHasKey($channel->id, $channels);
                
        // restricted channel
        $channel = $model->get('channel one');
        $this->assertArrayHasKey($channel->id, $channels);
    }
    
    /**
     * test the accept entries for channel
     */
    public function testAcceptChannelEntries()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$rootCategory->name);
        Kms_Test_Helper::setUpTestIdentity('testuser');
        $client = Kms_Resource_Client::getAdminClient();
        $model = Kms_Resource_Models::getChannel();
        
        // the channel with the moderation
        $channel = $model->get('channel one');
        
        // assign the test entry to the channel
        $entries = array();
        foreach (Kms_Test_Helper::$globalTestEntries as $entryId => $entry)
        {
            $entries[] = $entryId;
            $categoryEntry = new Kaltura_Client_Type_CategoryEntry();
            $categoryEntry->entryId = $entryId;
            $categoryEntry->categoryId = $channel->id;
            $client->categoryEntry->add($categoryEntry);
        }
        
        // change to a moderator user
        Kms_Test_Helper::setUpTestIdentity('categorytestuser');
        Kms_Resource_Client::reInitClients();
        $client = Kms_Resource_Client::getAdminClient();
        
        // test that it was added as needing moderation
        $entryModel = new Application_Model_Entry();
        $pager = $entryModel::getStandardEntryPager(array());
        $filter = $entryModel::getStandardEntryFilter(array());
        
        $categoryEntryAdvancedFilter = new Kaltura_Client_Type_CategoryEntryAdvancedFilter();
        $categoryEntryAdvancedFilter->categoriesMatchOr = $channel->fullName; 
        $categoryEntryAdvancedFilter->categoryEntryStatusIn = Kaltura_Client_Enum_CategoryEntryStatus::PENDING;
        $filter->advancedSearch = $categoryEntryAdvancedFilter;
                
        $entries = $entryModel->listAction($filter, $pager);
        
        $this->assertSameSize(Kms_Test_Helper::$globalTestEntries, $entries,'pending entries number match');
        
        // see that the pending list was changed
        $channel = $model->get('channel one');
        $this->assertAttributeEquals(count(Kms_Test_Helper::$globalTestEntries), 'pendingEntriesCount', $channel,'wrong pendingEntriesCount for channel');
         
        $entryIds = array(); 
        foreach ($entries as $entry){
            $entryIds[] = $entry->id;
        }
        
        // approve the entries
        $model->approveChannelEntries($channel,$entryIds);
        
        // see that the pending list was changed
        $channel = $model->get('channel one');
        $this->assertAttributeEquals(0, 'pendingEntriesCount', $channel,'wrong pendingEntriesCount for channel');
                
        // test that the entry was accepted
        $filter = $entryModel::getStandardEntryFilter(array());
        
        $categoryEntryAdvancedFilter = new Kaltura_Client_Type_CategoryEntryAdvancedFilter();
        $categoryEntryAdvancedFilter->categoriesMatchOr = $channel->fullName;
        $categoryEntryAdvancedFilter->categoryEntryStatusIn = Kaltura_Client_Enum_CategoryEntryStatus::ACTIVE;
        $filter->advancedSearch = $categoryEntryAdvancedFilter;
        
        $entries = $entryModel->listAction($filter, $pager);
        
        $this->assertSameSize(Kms_Test_Helper::$globalTestEntries, $entries,'approved entries number match');

        $entries = array_reverse($entries);
        foreach (Kms_Test_Helper::$globalTestEntries as $entry){
            $testEntry = array_pop($entries);
            $this->assertEquals($entry->id , $testEntry->id,'entry not found');
        }
    }
    
    public function testRejectChannelEntries()
    {
        
    }
}