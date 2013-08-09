<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

include_once 'testXSD.php';

/**
 * tests the Channeltopics Model
 *  
 * @author talbone
 *
 */
class Channeltopics_Model_ChanneltopicsTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private static $metadataProfileId;
    private static $topicfiled = 'Topics';
    private static $channelName = 'channelTopicsUnitTest';
    private static $channel;
    private static $rootCategory;
    private static $rootCategoryName;
    
    public static function setUpBeforeClass()
    {
        echo "Starting tests on modules/Channeltopics/models/Channeltopics.php\n";   
        
        // we are testing this
        Kms_Resource_Cache::enableCache();
        
        // create a metadata profile to testing
        self::createTestProfile();
        self::createTestTopics();
          
    }
    
    public static function tearDownAfterClass()
    {
        // delete the metadata profile for testing
     //   self::deleteTestTopics();
        self::deleteTestProfile();
        
        echo "Finished tests on modules/Channeltopics/models/Channeltopics.php\n-----------------------------------------\n";
    }
    
    
    private static function createTestProfile()
    {
        global $unitTestSchema;
        
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        
        $metadataProfile = new Kaltura_Client_Metadata_Type_MetadataProfile();
        $metadataProfile->name = "channel topic test metadata";
        $metadataProfile->metadataObjectType = Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY;
        
        $metadataProfile = $metadataPlugin->metadataProfile->add($metadataProfile,$unitTestSchema);
        
        self::$metadataProfileId = $metadataProfile->id;
    }
    
    private static function deleteTestProfile()
    {
        $client = Kms_Resource_Client::getAdminClient();
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $metadataPlugin->metadataProfile->delete(self::$metadataProfileId);
    }
    
    private static function createTestTopics()
    {        
        $client = Kms_Resource_Client::getAdminClient();
        
        // create a channel - category
        self::$channelName = self::$channelName;
        $category = new Kaltura_Client_Type_Category();           
        $category->name = 'ChannelTopicsTestRootCat' . rand(1,1000);
        self::$rootCategory = $client->category->add($category);
        self::$rootCategoryName = $category->name;
        
        $category->name = self::$channelName;
        $category->parentId = self::$rootCategory->id;
        self::$channel = $client->category->add($category);
        
        // create meta data for that channel
        $customDataXML = new SimpleXMLElement('<metadata/>');
        $customDataXML->addChild(self::$topicfiled, 'topic one');
        $customDataXML->addChild(self::$topicfiled, 'topic two');
        
        $metadataPlugin = Kaltura_Client_Metadata_Plugin::get($client);
        $metadataPlugin->metadata->add(self::$metadataProfileId, Kaltura_Client_Metadata_Enum_MetadataObjectType::CATEGORY, self::$channel->id, $customDataXML->asXML());
    }
    
    private static function deleteTestTopics()
    {
        $client = Kms_Resource_Client::getAdminClient();
                
        // delete the channel
        $client->category->delete(self::$rootCategory->id);
    }
        
    /**
     * test that we created the test metadata profile as expected
     */
    public function testAvailableTopicsFromMetadata()
    {
        $model = new Channeltopics_Model_Channeltopics();
            
        $fields = Kms_Helper_Metadata::getCustomdataFields(self::$metadataProfileId);
        $topics = $fields[self::$topicfiled];
                
        // these values are hardcoded in the testXSD.php file
        $this->assertContains('topic one', $topics);
        $this->assertContains('topic two', $topics);
        $this->assertContains('topic three', $topics);
        $this->assertContains('last topic', $topics);        
    }
    
    /**
     * @depends testAvailableTopicsFromMetadata
     */
    public function testGetavailabletopics()
    {
        $model = new Channeltopics_Model_Channeltopics();        
        
        $fields = Kms_Helper_Metadata::getCustomdataFields(self::$metadataProfileId);
        $expectedTopics = $fields[self::$topicfiled]['listValues'];
        
        // see that the topics match
        $topics = $model->getAvailableTopics(self::$metadataProfileId , self::$topicfiled);
                
        $this->assertFalse(array_count_values(array_diff($expectedTopics, $topics))==0,'topics dont match');
        
        // see that the values are in the cache as expected
        $topics = Kms_Resource_Cache::appGet('channelTopicsFields', array('profileId' => self::$metadataProfileId,'fieldId' => 'Topics'));
       
        $this->assertTrue(count(array_diff($expectedTopics, $topics[self::$topicfiled]['listValues']))==0,'topics dont match in cache');
    }
    
    /**
     * @depends testGetavailabletopics
     */
    public function testGetchannelTopics()
    {
        $model = new Channeltopics_Model_Channeltopics();
        
        $topics = $model->getChannelTopics(self::$metadataProfileId, self::$topicfiled, self::$channel->id);

        // parse the result
        $fields = $model->getCustomdataFields(self::$metadataProfileId, self::$topicfiled);
        $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
        $topics = $topics[self::$topicfiled];
        
        // test that the topics match
        $this->assertContains('topic one', $topics);
        $this->assertContains('topic two', $topics);
        
        // test that they are in the cache
        $cacheParams = array('profileId' => self::$metadataProfileId,'fieldId' => self::$topicfiled, 'channelId' => self::$channel->id);
        $topics = Kms_Resource_Cache::apiGet('channelTopics', $cacheParams);
        
        // parse the result
        $fields = $model->getCustomdataFields(self::$metadataProfileId, self::$topicfiled);
        $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
        $topics = $topics[self::$topicfiled];
        
        $this->assertContains('topic one', $topics);
        $this->assertContains('topic two', $topics);
    }
    
    /**
     * @depends testGetchannelTopics
     */
    public function testSetchannelTopics()
    {        
        // create a channel - category
        $category = new Kaltura_Client_Type_Category();
        $category->name = "testSetchannelTopics_category";
        $category->parentId = self::$rootCategory->id;
        $client = Kms_Resource_Client::getAdminClient();
        $channel = $client->category->add($category);
        
        $model = new Channeltopics_Model_Channeltopics();
        
        $topics = $model->getChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id);
                
        // parse the result
        $this->assertTrue(empty($topics->objects));
        
        // add new topics to channel
        $topics = array();
        $topics['topic one'] = 'topic one';
        $topics['topic two'] = 'topic two';
        $model->setChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id, $topics);
        $topics = $model->getChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id);
        
        // parse the result
        $fields = $model->getCustomdataFields(self::$metadataProfileId, self::$topicfiled);
        $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
        $topics = $topics[self::$topicfiled];
                 
        $this->assertContains('topic one', $topics);
        $this->assertContains('topic two', $topics);
        
        // add topics to an existing channel
        $topics['last topic'] = 'last topic';
        $model->setChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id, $topics);
        $topics = $model->getChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id);
        
        // parse the result
        $fields = $model->getCustomdataFields(self::$metadataProfileId, self::$topicfiled);
        $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
        $topics = $topics[self::$topicfiled];
         
        $this->assertContains('topic one', $topics);
        $this->assertContains('topic two', $topics);
        $this->assertContains('last topic', $topics);
        
        // get new channel
        $category->name = "testSetchannelTopics_category2";
        $category->parentId = self::$rootCategory->id;
        $channel = $client->category->add($category);
        
        // add topics to a new empty channel
        $topics = array();
        $topics['last topic'] = 'last topic';
        $model->setChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id, $topics);
        $topics = $model->getChannelTopics(self::$metadataProfileId, self::$topicfiled, $channel->id);
        
        // parse the result
        $fields = $model->getCustomdataFields(self::$metadataProfileId, self::$topicfiled);
        $topics = Kms_Helper_Metadata::getCustomdataValues($topics->objects[0],$fields);
        $topics = $topics[self::$topicfiled];
         
        $this->assertContains('last topic', $topics);
    }
}