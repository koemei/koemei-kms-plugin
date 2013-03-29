<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Entry
 *
 * @author leon
 */
class Application_Model_EntryTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    private $testEntry = null;

    public static function setUpBeforeClass()
    {
        echo "Starting tests on models/Entry.php\n";
        Kms_Resource_Cache::disableCache();
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        
        // create root category
        echo "Creating root category for tests...";
        Kms_Test_Helper::createTestRootCategory('unitTest');
        echo "done\n";
        
        // create an identity
        Kms_Test_Helper::setUpTestIdentity('testuser');

        Kms_Test_Helper::createTestVideoEntries(5);
        Kms_Test_Helper::createTestImageEntries(5);
    }

    public function setUp()
    {
        parent::setUp();
        $this->testEntry = null;
        // create an identity
        $adapter = new Kms_Auth_Demo();
        $adapter->setId('testuser');
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);


        // create a test entry for each test case and get its id
        $testEntry = new Kaltura_Client_Type_BaseEntry();
        //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $testEntry->type = Kaltura_Client_Enum_EntryType::MEDIA_CLIP;
        $testEntry->name = "Test Entry" . rand(1, 1000);
        $testEntry->description = "This is a test entry";
        $testEntry->tags = "test,some,tags,for,testing";
        $testEntry->categories = Kms_Resource_Config::getConfiguration('categories', 'rootCategory') . ">test";
        $testEntry->userId = Zend_Auth::getInstance()->getIdentity()->getId();

        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            $this->testEntry = $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
        } catch (Kaltura_Client_Exception $e)
        {
            
        }
    }

    public function tearDown()
    {

//        parent::tearDown();
        try
        {

            // try to delete (if not deleted already)
            $client = Kms_Resource_Client::getAdminClient();
            $delEntry = $client->baseEntry->get($this->testEntry->id);
            $client->baseEntry->delete($delEntry->id);
        } catch (Kaltura_Client_Exception $e)
        {
            
        }
    }

    public static function tearDownAfterClass()
    {
        Kms_Test_Helper::tearDownTestEntries();
        Kms_Test_Helper::tearDownTestCategories();
        Kms_Test_Helper::tearDownTestIdentity('testuser');
        Kms_Test_Helper::tearDownTestIdentity('testwronguser');
        Kms_Test_Helper::tearDownTestIdentity('testuserTwo');
        Kms_Test_Helper::tearDownTestIdentity('testuserThree');
        
        Kms_Test_Helper::cleanUp();
        echo "Finished tests on models/Entry.php\n-----------------------------------------\n";
    }

    
    /*******************************************************/
    
    /**
     *  START THE TESTS HERE
     */    
    
    public function testCanAddEntry()
    {
        $model = Kms_Resource_Models::getEntry();

        $retrievedEntry = $model->get($this->testEntry->id);

        $this->assertEquals($retrievedEntry->id, $this->testEntry->id);

        $this->assertEquals($retrievedEntry->name, $this->testEntry->name);
    }

    /**
     * @depends testCanAddEntry
     */
    public function testCanGetEntry()
    {
        $client = Kms_Resource_Client::getUserClient();
        $model = Kms_Resource_Models::getEntry();
        $model->get($this->testEntry->id);
        $this->assertEquals($this->testEntry->id, $model->entry->id);
    }

    /**
     * @depends testCanAddEntry
     */
    public function testCannotSaveEntryIfNotMine()
    {
        $this->setExpectedException(
                'Kaltura_Client_Exception', 'Access denied, tried to update entry that does not belong to me');

        $testSaveData = array(
            'id' => $this->testEntry->id,
            'name' => 'Updated test Entry',
            'description' => 'new description',
        );

        //aiuthenticate as another user
        $adapter = new Kms_Auth_Demo();
        $adapter->setId('testwronguser');
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);

        $model = Kms_Resource_Models::getEntry();
        $model->get($this->testEntry->id);
        $newEntry = $model->save($testSaveData);
    }

    /**
     * @depends testCanAddEntry
     */
    public function testCanSaveEntry()
    {
        $testSaveData = array(
            'id' => $this->testEntry->id,
            'name' => 'Updated test Entry',
            'description' => 'new description',
        );

        $model = Kms_Resource_Models::getEntry();
        $model->get($this->testEntry->id);

        $newEntry = $model->save($testSaveData);

        $retrievedEntry = $model->get($this->testEntry->id);

        $this->assertEquals($newEntry->name, $testSaveData['name']);
        $this->assertEquals($newEntry->description, $testSaveData['description']);

        $this->assertEquals($retrievedEntry->name, $testSaveData['name']);
        $this->assertEquals($retrievedEntry->description, $testSaveData['description']);
    }

    /**
     * @depends testCanAddEntry
     */
    public function testCanDeleteEntry()
    {
        $this->setExpectedException(
                'Kaltura_Client_Exception');

        $model = Kms_Resource_Models::getEntry();
        $model->delete($this->testEntry->id);

        $retrievedEntry = $model->get($this->testEntry->id);
//        $this->assertNotEquals($retrievedEntry->id, $this->testEntry->id);
    }

    /**
     * @depends testCanAddEntry
     */
    public function testCannotDeleteEntryIfNotMine()
    {
        //aiuthenticate as another user
        $adapter = new Kms_Auth_Demo();
        $adapter->setId('testwronguser');
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);
        // re-initialize the client (create a KS)
        Kms_Resource_Client::initClient(Kaltura_Client_Enum_SessionType::USER);

        $model = Kms_Resource_Models::getEntry();

        $res = $model->delete($this->testEntry->id);
        $this->assertFalse($res);

        //aiuthenticate as the right user
        $adapter = new Kms_Auth_Demo();
        $adapter->setId('testuser');
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);
        // re-initialize the client (create a KS)
        Kms_Resource_Client::initClient(Kaltura_Client_Enum_SessionType::USER);
        $retrievedEntry = $model->get($this->testEntry->id);

        $this->assertEquals($retrievedEntry->id, $this->testEntry->id);
    }

    private function createMultipleTestEntries()
    {

        // prepare our entries
        $client = Kms_Resource_Client::getAdminClient();
        $client->startMultiRequest();
        for ($i = 0; $i < 5; $i++)
        {
            // create a test entry for each test case and get its id
            $testEntry = new Kaltura_Client_Type_BaseEntry();
            //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
            $testEntry->type = Kaltura_Client_Enum_EntryType::MEDIA_CLIP;
            $testEntry->name = "Test Entry " . $i;
            $testEntry->description = "This is a test entry";
            $testEntry->tags = "some,tags,for,testing";
            $testEntry->categories = "test";
            $testEntry->userId = Zend_Auth::getInstance()->getIdentity()->getId();
            $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
        }
        $res = $client->doMultiRequest();
        return $res;
    }

    /**
     * 
     *
     */
    public function testCanDeleteMultipleEntries()
    {
        $entries = $this->createMultipleTestEntries();
        $count = count($entries);
        $entryIds = array();
        foreach ($entries as $entry)
        {
            $entryIds[] = $entry->id;
        }

        $model = Kms_Resource_Models::getEntry();
        // delete the entries
        $res = $model->deleteMulti($entryIds);

        $idsAfter = array();
        // try to get the entries and see if they are there or not
        foreach ($entryIds as $entryId)
        {
            try
            {
                $entry = $model->get($entryId);
                $idsAfter[] = $entry->id;
            } catch (Exception $e)
            {
                
            }
        }

        $this->assertLessThan($count, count($idsAfter));
        $this->assertEquals(0, count($idsAfter));
    }

    public function testGetEntriesByCategory()
    {
        $model = Kms_Resource_Models::getEntry();
        // get the rootcat
        $rootCat = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');

        // get the entries under the onetimetest
        $res = $model->getEntriesByCategory('onetimetest', array());

        // start the assertions
        $this->assertGreaterThan(0, count($res), 'No entries exist under the requested category');
        $this->assertLessThanOrEqual(Kms_Resource_Config::getConfiguration('gallery', 'pageSize'), count($res), 'Number of results was greater than allowed paging size');
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $categories = $entry->categories;
            $this->assertRegExp('/(^' . $rootCat . '>|,' . $rootCat . '>)/', $categories, 'Entry ' . $entry->id . ' does not belong to category ' . $rootCat);
        }

        // tweak the parameters to only get video
        $params = array('type' => 'video');
        $res = $model->getEntriesByCategory('onetimetest', $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::VIDEO, 'Got an entry of invalid type, when requesting Videos');
        }

        // tweak the parameters to only get images
        $params = array('type' => 'image');
        $res = $model->getEntriesByCategory('onetimetest', $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::IMAGE, 'Got an entry of invalid type, when requesting Images');
        }

        // also filter by keyword
        $params = array('keyword' => 'One Time');
        $res = $model->getEntriesByCategory('onetimetest', $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $categories = $entry->categories;
            $this->assertRegExp('/(^' . $rootCat . '>|,' . $rootCat . '>)/', $categories, 'Entry ' . $entry->id . ' does not belong to category ' . $rootCat);
        }
    }

    public function testGetEntriesByKeyword()
    {
        $model = Kms_Resource_Models::getEntry();
        $keyword = "logotest";
        $res = $model->getEntriesByKeyword($keyword, array('keyword' => $keyword));
        $this->assertGreaterThan(0, count($res), 'No entries exist with this keyword');
        $this->assertLessThanOrEqual(Kms_Resource_Config::getConfiguration('gallery', 'pageSize'), count($res), 'Number of results was greater than allowed paging size');
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $tags = explode(',', $entry->tags);
            $this->assertTrue(in_array($keyword, $tags), 'Got an entry that doesn\'t have the "onetimetest" tag in it');
        }

        // tweak the parameters to only get video
        $params = array('type' => 'video', 'keyword' => $keyword);
        $res = $model->getEntriesByKeyword($keyword, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::VIDEO, 'Got an entry of invalid type, when requesting Videos');
        }

        // tweak the parameters to only get images
        $params = array('type' => 'image', 'keyword' => $keyword);
        $res = $model->getEntriesByKeyword($keyword, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::IMAGE, 'Got an entry of invalid type, when requesting Images');
        }
    }

    public function testGetEntriesByTag()
    {
        $model = Kms_Resource_Models::getEntry();
        $tag = "logotest";
        $params = array();
        $res = $model->getEntriesByTag($tag, $params);
        $this->assertGreaterThan(0, count($res), 'No entries exist with this tag');
        $this->assertLessThanOrEqual(Kms_Resource_Config::getConfiguration('gallery', 'pageSize'), count($res), 'Number of results was greater than allowed paging size');
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $tags = explode(',', $entry->tags);
            $this->assertTrue(in_array($tag, $tags), 'Got an entry that doesn\'t have the ' . $tag . ' tag in it');
        }

        // tweak the parameters to only get video
        $params = array('type' => 'video');
        $res = $model->getEntriesByTag($tag, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::VIDEO, 'Got an entry of invalid type, when requesting Videos');
        }

        // tweak the parameters to only get images
        $params = array('type' => 'image');
        $res = $model->getEntriesByTag($tag, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::IMAGE, 'Got an entry of invalid type, when requesting Images');
        }
    }

    public function testGetEntriesByUser()
    {
        $model = Kms_Resource_Models::getEntry();
        $user = "testuser";
        $params = array();
        $res = $model->getEntriesByUserId($user, $params);
        $this->assertGreaterThan(0, count($res), 'No entries exist for the user '.$user);
        $this->assertLessThanOrEqual(Kms_Resource_Config::getConfiguration('gallery', 'pageSize'), count($res), 'Number of results was greater than allowed paging size');
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($user, $entry->userId, 'Got an entry that does not belong to '.$user);
        }

        // tweak the parameters to only get video
        $params = array('type' => 'video');
        $res = $model->getEntriesByUserId($user, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::VIDEO, 'Got an entry of invalid type, when requesting Videos');
        }

        // tweak the parameters to only get images
        $params = array('type' => 'image');
        $res = $model->getEntriesByUserId($user, $params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::IMAGE, 'Got an entry of invalid type, when requesting Images');
        }
    }
    
    public function getMyMedia()
    {
        $model = Kms_Resource_Models::getEntry();
        $user = "testuser";
        $pageSize = 5;
        $model->setPageSize($pageSize);
        
        $params = array();
        $res = $model->getMyMedia( $params);
        $this->assertGreaterThan(0, count($res), 'No entries exist for my media (user: '.$user.')');
        $this->assertLessThanOrEqual(Kms_Resource_Config::getConfiguration('gallery', 'pageSize'), count($res), 'Number of results was greater than allowed paging size');
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($user, $entry->userId, 'Got an entry that does not belong to '.$user);
        }

        // tweak the parameters to only get video
        $params = array('type' => 'video');
        $res = $model->getMyMedia($params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::VIDEO, 'Got an entry of invalid type, when requesting Videos');
        }

        // tweak the parameters to only get images
        $params = array('type' => 'image');
        $res = $model->getMyMedia($params);

        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $this->assertEquals($entry->mediaType, Kaltura_Client_Enum_MediaType::IMAGE, 'Got an entry of invalid type, when requesting Images');
        }
    }
    
    public function testToggleCategory()
    {
        $model = Kms_Resource_Models::getEntry();
        $rootCat = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $client = Kms_Resource_Client::getUserClient();
        $entry = $model->get($this->testEntry->id);
        $cats = $model->getEntryCategories();
        foreach($cats as $cat)
        {
            $removeCat = $cat;
            continue;
        }
        
        
        // remove the category
        $model->toggleCategory( $this->testEntry->id, $removeCat);
        $entry = $model->get($this->testEntry->id);
        $cats = $model->getEntryCategories();
        
        $this->assertFalse(in_array($removeCat, $cats), 'Category could not be unpublished');
        
        // toggle back the category
        $model->toggleCategory( $this->testEntry->id, $removeCat);
        $entry = $model->get($this->testEntry->id);
        $cats = $model->getEntryCategories();
        
        $this->assertTrue(in_array($removeCat, $cats), 'Category published successfuly');
        
        
    }
    
    /**
     * tests like using Entry->liked
     */
    public function testLike() {
        $model = new Application_Model_Entry();

        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 0, 'votes incorrect 0 != ' . $entry->votes);

        $this->assertTrue($model->like($this->testEntry->id), 'Like failed');

        $this->assertTrue($model->isLiked($this->testEntry->id), 'Like not stored in $model->liked');

        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 1, 'votes incorrect 1 != ' . $entry->votes . ' entry ' . $this->testEntry->id);

        // test double like
        $this->assertFalse($model->like($this->testEntry->id), 'double Like not correct');
    }
    
    /**
     * tests like with the cached value
     */
    public function testLikeCached()
    {
    	Kms_Resource_Cache::enableCache();
    	$userId = Kms_Plugin_Access::getId();
    	$model = new Application_Model_Entry();
    	    	    	
    	$model->like($this->testEntry->id);
    	 
    	// use new model to get clean $model->liked
    	$model = new Application_Model_Entry();
    	     	 
    	$this->assertTrue(Kms_Resource_Cache::isEnabled(),'no caching!!!!!');
    	
    	// test that is is stored in the cache correctly
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
    	$this->assertTrue($isLiked[0],'Like not stored in cache');
    	
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => ''));
    	$this->assertFalse($isLiked,'Like stored in cache without userId');
    	 
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => '' , 'userId' => $userId));
    	$this->assertFalse($isLiked,'Like stored in cache without id');
    	     	 
    	$this->assertTrue($model->isLiked($this->testEntry->id),'Like not stored in cache');
    	 
    	Kms_Resource_Cache::disableCache();
    }
    
    /**
     * tests like with the api call
     */
    public function testLikeApi()
    {
    	// make sure no cache (if testLikeCached() failed)
    	Kms_Resource_Cache::disableCache();
    	$userId = Kms_Plugin_Access::getId();
    	$model = new Application_Model_Entry();
    	    	    	     	 
    	$model->like($this->testEntry->id);
    	 
    	// use new model to get clean $model->liked
    	$model = new Application_Model_Entry();
    	  	 
    	// test for no entry in cache
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
    	$this->assertFalse($isLiked,'Like stored in cache');
    	 
    	$this->assertTrue($model->isLiked($this->testEntry->id),'Like using api call');    	 
    }
    
    /**
     * test unlike using Entry->liked
     */
    public function testUnlike()
    {
    	$userId = Kms_Plugin_Access::getId();
    	$model = new Application_Model_Entry();
    	    	 
    	// unlike without like first
    	$this->assertFalse($model->unlike($this->testEntry->id),'unlike with no like');
    	$this->assertFalse($model->isLiked($this->testEntry->id),'no like data should exsist');    	 
    	
    	// unlike with like first
    	$this->assertTrue($model->like($this->testEntry->id),'Like failed');    	 
    	$this->assertTrue($model->unlike($this->testEntry->id),'Unlike failed');
    	    	    	   	
    	$this->assertFalse($model->isLiked($this->testEntry->id),'Like stored in data member');
    	
    	$entry = $model->get($this->testEntry->id);
    	$this->assertTrue($entry->votes == 0,'votes incorrect 0 != ' . $entry->votes);
    	
    	// test double unlike
    	$this->assertFalse($model->unlike($this->testEntry->id),'double Unlike not correct');
    }
        
    /**
     * tests unlike using cached value
     */
    public function testUnlikeCached()
    {
    	Kms_Resource_Cache::enableCache();
    	$userId = Kms_Plugin_Access::getId();
    	$model = new Application_Model_Entry();
    	    	
    	$model->like($this->testEntry->id);
    	$model->unlike($this->testEntry->id);
    	
    	// use new model to get clean $model->liked
    	$model = new Application_Model_Entry();
    	    	
    	$this->assertTrue(Kms_Resource_Cache::isEnabled(),'no caching!!!!!');
    	 
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
    	$this->assertFalse($isLiked[0],'Like not stored in cache');
    	 
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => ''));
    	$this->assertFalse($isLiked,'Like stored in cache without userId');
    	
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => '' , 'userId' => $userId));
    	$this->assertFalse($isLiked,'Like stored in cache without id');
    	
    	$this->assertFalse($model->isLiked($this->testEntry->id),'Like not stored in cache');
    	
    	Kms_Resource_Cache::disableCache();
    }
    
    /**
     * tests unlike using api call
     */
    public function testUnlikeApi()
    {
    	// make sure no cache (if testUnlikeCached() failed)
    	Kms_Resource_Cache::disableCache();
    	$userId = Kms_Plugin_Access::getId();
    	$model = new Application_Model_Entry();
    	    	 
    	$model->like($this->testEntry->id);
    	$model->unlike($this->testEntry->id);
    	
    	// use new model to get clean $model->liked
    	$model = new Application_Model_Entry();
    		
    	// test for no cache
    	$isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
    	$this->assertFalse($isLiked,'Like stored in cache');
    	 
    	$this->assertFalse($model->isLiked($this->testEntry->id),'Unlike using api call');
    }
    
    /**
     * test that the like/unlike status is correct
     */
    public function testIsLiked()
    {
        $userId = Kms_Plugin_Access::getId();
        $model = new Application_Model_Entry();
    	    
        // allow the use of $model->liked
         
        $model->like($this->testEntry->id);
        $this->assertTrue($model->isLiked($this->testEntry->id),'Like not correct from $model->liked');
         
        $model->unlike($this->testEntry->id);
        $this->assertFalse($model->isLiked($this->testEntry->id),'Unlike not correct from $model->liked');
         
        // cached value

        Kms_Resource_Cache::enableCache();
         
        $model->like($this->testEntry->id);
        $model = new Application_Model_Entry();
        $this->assertTrue($model->isLiked($this->testEntry->id),'Like not correct from cache');

        $model->unlike($this->testEntry->id);
        $model = new Application_Model_Entry();
        $this->assertFalse($model->isLiked($this->testEntry->id),'Unlike not correct from cache');
         
        // api call

        // clean cache
        Kms_Resource_Cache::apiClean('like', array('id' => $this->testEntry->id ,'userId' => $userId));
        $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
        $this->assertFalse($isLiked,'Like stored in cache');
         
        $model->like($this->testEntry->id);
        
        // clean cache
        Kms_Resource_Cache::apiClean('like', array('id' => $this->testEntry->id ,'userId' => $userId));
        $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
        $this->assertFalse($isLiked,'Like stored in cache');
        
        $model = new Application_Model_Entry();
        $this->assertTrue($model->isLiked($this->testEntry->id),'Like not correct from api');
        
        // test that the entry is in the cache now
        $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
        $this->assertTrue($isLiked[0],'Like not stored in cache after isLiked');
        $model = new Application_Model_Entry();
        $this->assertTrue($model->isLiked($this->testEntry->id),'Like using cached value');
         
        // test that the entry is in the entry->liked now
        $this->assertTrue($model->isLiked($this->testEntry->id),'Like using data member');
         
        // clean cache
        Kms_Resource_Cache::apiClean('like', array('id' => $this->testEntry->id ,'userId' => $userId));
        $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
        $this->assertFalse($isLiked,'Like stored in cache');
         
        $model->unlike($this->testEntry->id);
        $model = new Application_Model_Entry();
        $this->assertFalse($model->isLiked($this->testEntry->id),'Unlike not correct from api');
         
        // test that the entry is in the cache now
        $isLiked = Kms_Resource_Cache::apiGet('like', array('id' => $this->testEntry->id , 'userId' => $userId));
        $this->assertFalse($isLiked[0],'Like not stored in cache after isLiked');
        $model = new Application_Model_Entry();
        $this->assertFalse($model->isLiked($this->testEntry->id),'Unlike using cached value');

        // test that the entry is in the entry->liked now
        $this->assertFalse($model->isLiked($this->testEntry->id),'Unlike using data member');
         
        Kms_Resource_Cache::disableCache();
    }
    
    /**
     * test that the entry votes are increased/decreased accordingly
     * @dependss testLike
     * @dependss testUnlike
     */
    public function testEntryVotes()
    {
        Kms_Resource_Cache::enableCache();
        
        $userId = Kms_Plugin_Access::getId();
        $model = new Application_Model_Entry();
        
        $entry = $model->get($this->testEntry->id);        
        $this->assertTrue($entry->votes == 0,'votes incorrect 0 != ' . $entry->votes);
              
        // first like first user               

        Kms_Test_Helper::setUpTestIdentity('testuserTwo');
        Kms_Resource_Client::reInitClients();
        $client = Kms_Resource_Client::getUserClient();
        
        // create an entry so the user get created
        $testEntry = new Kaltura_Client_Type_BaseEntry();
        $testEntry->type = Kaltura_Client_Enum_EntryType::MEDIA_CLIP;
        $testEntry->name = "Test Entry" . rand(1, 1000);
        $testEntry->userId = Kms_Plugin_Access::getId();
        $testEntry = $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
        Kms_Test_Helper::$globalTestEntries[$testEntry->id] = $testEntry;
        
        $likePluginServiceI = new Kaltura_Client_Like_LikeService($client);      
                     
        $this->assertTrue($likePluginServiceI->like($this->testEntry->id),'second like failed');
       
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 1,'votes incorrect 1 != ' . $entry->votes);

        // second like with second user
        
        Kms_Test_Helper::setUpTestIdentity('testuserThree');
        Kms_Resource_Client::reInitClients();
        $client = Kms_Resource_Client::getUserClient();
        
        // create an entry so the user get created
        $testEntry = new Kaltura_Client_Type_BaseEntry();
        $testEntry->type = Kaltura_Client_Enum_EntryType::MEDIA_CLIP;
        $testEntry->name = "Test Entry" . rand(1, 1000);
        $testEntry->userId = Kms_Plugin_Access::getId();
        $testEntry = $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
        Kms_Test_Helper::$globalTestEntries[$testEntry->id] = $testEntry;
        
        $likePluginServiceII = new Kaltura_Client_Like_LikeService($client);           
        
        $this->assertTrue($likePluginServiceII->like($this->testEntry->id),'third like failed');
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 2,'votes incorrect 2 != ' . $entry->votes);

        // third like third user
                
        Kms_Test_Helper::setUpTestIdentity('testuser');
        Kms_Resource_Client::reInitClients();
        
        // add the entry to the cache
        Kms_Resource_Cache::apiSet('entry', array('id' => $entry->id), $entry);
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 2,'votes incorrect 2 != ' . $entry->votes);
        
        // use the model - to force cache clean after like
        $this->assertTrue($model->like($this->testEntry->id),'last like failed');
        $this->assertFalse(Kms_Resource_Cache::apiGet('entry', array('id' => $this->testEntry->id)));
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 3,'votes incorrect 3 != ' . $entry->votes);
        
        
        // now unlike - mind the services

        // add the entry to the cache
        Kms_Resource_Cache::apiSet('entry', array('id' => $entry->id), $entry);
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 3,'votes incorrect 3 != ' . $entry->votes);
                
        // use the model - to force cache clean after unlike
        $this->assertTrue($model->unlike($this->testEntry->id),'unlike failed');
        $this->assertFalse(Kms_Resource_Cache::apiGet('entry', array('id' => $this->testEntry->id)));
        
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 2,'votes incorrect 2 != ' . $entry->votes);
        
        $likePluginServiceI->unlike($this->testEntry->id);
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 1,'votes incorrect 1 != ' . $entry->votes);

        $likePluginServiceII->unlike($this->testEntry->id);
        $entry = $model->get($this->testEntry->id);
        $this->assertTrue($entry->votes == 0,'votes incorrect 0 != ' . $entry->votes);
                
        Kms_Resource_Cache::disableCache();
    }
    
    /**
     * tests the sorting by total rank - no of likes
     */
/*    public function testSortBytotalRank()
    {
        $model = new Application_Model_Entry();
        Kms_Test_Helper::createTestRootCategory('likeTest');
        
        // prepare our entries
        $client = Kms_Resource_Client::getAdminClient();
        
        for ($i=0 ; $i <5 ; ++$i){
            $testEntry = new Kaltura_Client_Type_BaseEntry();
            //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
            $testEntry->type = Kaltura_Client_Enum_EntryType::MEDIA_CLIP;
            $testEntry->name = "Test Entry" . rand(1, 1000);
            $testEntry->description = "This is a test entry";
            $testEntry->tags = "sortTag";
            $testEntry->categories = 'likeTest';
            $testEntry->userId = Zend_Auth::getInstance()->getIdentity()->getId();

            try
            {
                $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
            } 
            catch (Kaltura_Client_Exception $e)
            {
                echo "$e";
            }
        }
        
        // test that they are there
        $res = $model->getEntriesByTag('sortTag', array());
        $this->assertGreaterThan(0, count($res), 'No entries exist with this tag');
        
        // original order
        $res = $model->getEntriesByTag('sortTag', array('sort' => 'like'));
        $this->assertGreaterThan(0, count($res), 'No entries exist with this tag');
        
        $likeIndex = 0;
        foreach ($res as $entry)
        {            
            // add likes to the entries
            $entry = $model->get($entry->id);
            $likeIndex++;
            for ($i=0 ; $i<$likeIndex ; ++$i){
                Kms_Test_Helper::setUpTestIdentity('testuser' . $i);
                Kms_Resource_Client::reInitClients();
                $this->assertTrue($model->like($entry->id),"entry $entry->id like $i failed");
                $entry->votes++;
            }
            $expected[] = $entry;
        }
        // we are expecting the entries to be at a reverse order due to the sort
        $expected = array_reverse($expected);
        
        // now - test with likes
        $res = $model->getEntriesByTag('sortTag', array('sort' => 'like'));
        $this->assertGreaterThan(0, count($res), 'No entries exist with this tag');
        
        $i = 0;
        foreach ($res as $entry)
        {
            $entry = $model->get($entry->id);
            $expectedEntry = $expected[$i];
            $i++;
            
            $this->assertTrue($entry->id == $expectedEntry->id, "entry $i not as expected");
            $this->assertTrue($entry->votes == $expectedEntry->votes,"votes incorrect $entry->votes != $expectedEntry->votes");
        }
    }*/
}

