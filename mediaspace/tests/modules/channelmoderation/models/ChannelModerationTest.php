<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * test case for channelmoderation model
 * @author talbone
 *
 */
class Channelmoderation_Model_ChannelmoderationTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public static function setUpBeforeClass()
    {
        echo "Starting tests on modules/channelmoderation/models/Channelmoderation.php\n";

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
        
        $user['username'] = 'categorytestuser';
        $user['email'] = 'test.mediaspace@kaltura.com';
        $user['role'] = Kms_Plugin_Access::VIEWER_ROLE;
        $user['password'] = 'zZ!1zzzzz423';
        
        $model = Kms_Resource_Models::getUser();
        $model->add($user);
        
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
        $category->moderation = true;
        $channel = $client->category->add($category);
        
        // ////////////////////
        // members
        // ////////////////////
                
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
        
        // ////////////////////
        // entries
        // ////////////////////
        
        Kms_Test_Helper::createTestImageEntries(1);
        
    }
    
    public static function tearDownAfterClass()
    {
        // configuration
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        
        // entries
        Kms_Test_Helper::tearDownTestEntries();
        
        // categories
        Kms_Test_Helper::tearDownTestCategories();
        
        // users
        $model = Kms_Resource_Models::getUser();
        $model->delete(array('categorytestuser'));
        
        echo "done\n";
    }
    
    /**
     * test getPendingEntries() and getLastResultCount()
     */
    public function testGetPendingEntries()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        $client = Kms_Resource_Client::getAdminClient();
        
        // get the channel
        $model = new Application_Model_Channel();
        $channel = $model->get('unitTest>channel one',true);        
        
        // set the user - contributer
        Kms_Test_Helper::setUpTestIdentity('testuser');
        
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
        
        $channel = $model->get('unitTest>channel one',true);
        $this->assertAttributeEquals(count(Kms_Test_Helper::$globalTestEntries), 'pendingEntriesCount', $channel,'wrong pendingEntriesCount for channel');
        
        // set the user - moderator
        Kms_Test_Helper::setUpTestIdentity('categorytestuser');
        
        // get the pending entries
        $model = new Channelmoderation_Model_Channelmoderation();
        $pending = $model->getPendingEntries($channel);
        
        $this->assertSameSize(Kms_Test_Helper::$globalTestEntries, $pending,'pending entries number do not match');
        $this->assertEquals(count(Kms_Test_Helper::$globalTestEntries), $model->getLastResultCount(),'pending entries number do not match');
        
        // index the array by id - to search in it
        foreach ($pending as $entry)
        {
            $pendingEntries[$entry->id] = $entry;
        }
        
        foreach (Kms_Test_Helper::$globalTestEntries as $entryId => $entry)
        {
            $this->assertArrayHasKey($entryId, $pendingEntries,'pending entry ' . $entryId .' is not pending');
        }
    }
}
