<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Playlist
 *
 * @author leon
 */
class Application_Model_PlaylistTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    private $testPlaylist = null;

    public static function setUpBeforeClass()
    {
        echo "Starting tests on models/Playlist.php\n";
        Kms_Resource_Cache::disableCache();
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', 'unitTest');
        
        // create root category
        echo "Creating root category for tests...";
        Kms_Test_Helper::createTestRootCategory('unitTest');
        echo "done\n";

        // create an identity
        Kms_Test_Helper::setUpTestIdentity('testuser');

       // Kms_Test_Helper::createTestVideoEntries(5);
        // NOTE NEED MINIMUM 10 entries FOR TESTING HERE
        Kms_Test_Helper::createTestImageEntries(10);
    }

    public function setUp()
    {
        parent::setUp();
        $this->testPlaylist = null;
        // create an identity
        $adapter = new Kms_Auth_Demo();
        $adapter->setId('testuser');
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);


        // create a test entry for each test case and get its id
        $testPlaylist = new Kaltura_Client_Type_Playlist();

        //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $testPlaylist->name = "Test Playlist" . rand(1, 1000);
        $testPlaylist->description = "This is a test playlist";
        $testPlaylist->userId = Zend_Auth::getInstance()->getIdentity()->getId();
        $testPlaylist->playlistType = Kaltura_Client_Enum_PlaylistType::STATIC_LIST;
        $entryIds = array();
        
        // put the first five entries inside the playlist
        foreach (array_slice(Kms_Test_Helper::$globalTestEntries, 0, 5) as $testEntry)
        {
            $entryIds[] = $testEntry->id;
        }

        $testPlaylist->playlistContent = join(',', $entryIds);

        $client = Kms_Resource_Client::getAdminClient();
        try
        {
            $this->testPlaylist = $client->playlist->add($testPlaylist);
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('test Playlist Model: '.$e->getMessage().' - '.$e->getMessage());
        }
    }

    public function tearDown()
    {

        // get rid of all user's playlists
        $filter = new Kaltura_Client_Type_PlaylistFilter();
        $filter->userIdEqual = $this->testPlaylist->userId;
        
        $client = Kms_Resource_Client::getAdminClient();
        $userPlaylistsFromApi = $client->playlist->listAction($filter);
        try
        {
            $client->startMultiRequest();
            foreach($userPlaylistsFromApi->objects as $userPl)
            {
                echo "delete ".$userPl->id."\n";
                $client->playlist->delete($userPl->id);
            }
            $res = $client->doMultiRequest();
        }
        catch(Kaltura_Client_Exception $e)
        {
            print_r($e);
        }
    }

    public static function tearDownAfterClass()
    {
        Kms_Test_Helper::tearDownTestEntries();
        Kms_Test_Helper::tearDownTestCategories();
        Kms_Test_Helper::cleanUp();
        Kms_Test_Helper::tearDownTestIdentity('testuser');
        echo "Finished tests on models/Playlist.php\n-----------------------------------------\n";
    }

    /*******************************************************/
    
    /**
     *  START THE TESTS HERE
     */
    
    
    public function testCanAddPlaylist()
    {
        $model = Kms_Resource_Models::getPlaylist();

        $retrievedPlaylist = $model->get($this->testPlaylist->id);

        $this->assertEquals($retrievedPlaylist->id, $this->testPlaylist->id);

        $this->assertEquals($retrievedPlaylist->name, $this->testPlaylist->name);        
    }
    
    public function testCanDeletePlaylist()
    {
        $this->setExpectedException(
                'Kaltura_Client_Exception');

        $model = Kms_Resource_Models::getPlaylist();
        $model->delete($this->testPlaylist->id);

        $retrievedPlaylist = $model->get($this->testPlaylist->id);
    }
    
    public function testCanGetPlaylist()
    {
        $model = new Application_Model_Playlist();
        $model->get($this->testPlaylist->id);
        $this->assertEquals($this->testPlaylist->id, $model->playlist->id);
    }
    
    public function testCanGetEntries()
    {
        $model = new Application_Model_Playlist();
        $res = $model->getEntries($this->testPlaylist->id);
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries = count(explode(',', $playlist->playlistContent));
        
        $this->assertGreaterThan(0, count($res));
        $this->assertEquals($numEntries, count($res));
        
    }
    
    public function testCanGetEntriesWithLimit()
    {
        $model = new Application_Model_Playlist();
        $lim = 3;
        $res = $model->getEntries($this->testPlaylist->id, $lim);
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries = count(explode(',', $playlist->playlistContent));
        
        $this->assertGreaterThan(0, count($res));
        $this->assertLessThanOrEqual($numEntries, count($res));
        $this->assertLessThanOrEqual($lim, count($res));
        
    }
    
    public function testCanAddOneEntryToPlaylist()
    {
        $model = new Application_Model_Playlist();
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries1 = count(explode(',', $playlist->playlistContent));
        
        // get an entry
        $numEntriesToAdd = 1;
        
        $entryIds = array();
        foreach(array_slice(Kms_Test_Helper::$globalTestEntries, 5, $numEntriesToAdd) as $entry)
        {
            $entryIds[] = $entry->id;
        }
        
        $model->addEntriesToPlaylist($this->testPlaylist->id, $entryIds);
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries2 = count(explode(',', $playlist->playlistContent));
        
        $playlistEntries = $model->getEntries($this->testPlaylist->id);
        $playlistEntriesIds = array();
        foreach($playlistEntries as $key=>$entry)
        {
            $playlistEntriesIds[] = $entry->id;
        }
        $this->assertEquals($numEntries1 + $numEntriesToAdd, $numEntries2 );
        
        foreach($entryIds as $entryId)
        {
            $this->assertTrue(in_array($entryId, $playlistEntriesIds));
        }
        
    }

    public function testCanAddManyEntriesToPlaylist()
    {
        $model = new Application_Model_Playlist();
        $model = new Application_Model_Playlist();
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries1 = count(explode(',', $playlist->playlistContent));
        
        // get an entry
        $numEntriesToAdd = 3;
        
        $entryIds = array();
        foreach(array_slice(Kms_Test_Helper::$globalTestEntries, 5, $numEntriesToAdd) as $entry)
        {
            $entryIds[] = $entry->id;
        }
        
        $model->addEntriesToPlaylist($this->testPlaylist->id, $entryIds);
        $playlist = $model->get($this->testPlaylist->id);
        $numEntries2 = count(explode(',', $playlist->playlistContent));
        
        $playlistEntries = $model->getEntries($this->testPlaylist->id);
        $playlistEntriesIds = array();
        foreach($playlistEntries as $key=>$entry)
        {
            $playlistEntriesIds[] = $entry->id;
        }
        $this->assertEquals($numEntries1 + $numEntriesToAdd, $numEntries2 );
        
        foreach($entryIds as $entryId)
        {
            $this->assertTrue(in_array($entryId, $playlistEntriesIds));
        }
        
    }
    
    
    public function testEntryExistsInPlaylist()
    {
        $model = new Application_Model_Playlist();
        $playlistEntries = $model->getEntries($this->testPlaylist->id);
        $entry = array_pop($playlistEntries);
        $entryId = $entry->id;
        $this->assertTrue($model->entryExistsInPlaylist($this->testPlaylist->id, $entryId), 'Entry Id that exists in the playlist is not being reported in EntryExistsInPlaylist');
    }
    
    public function testGetUserPlaylists()
    {
        // create a few playlists first
        $entry = array_pop(Kms_Test_Helper::$globalTestEntries);
        $entryId = $entry->id;
        $model = new Application_Model_Playlist();
        $userPlaylists = array($this->testPlaylist);
        
        for($i=0;$i<5;$i++)
        {
            $userPlaylists[] = $model->createPlaylist("test playlist $i", $entryId);
        }
        
        foreach($userPlaylists as $userPl)
        {
            $userPlaylistIds[] = $userPl->id;
        }
        
        $playlists = $model->getUserPlaylists($this->testPlaylist->userId);
        
        $this->assertEquals(count($userPlaylistIds), count($playlists->objects));
        foreach($playlists->objects as $pl)
        {
            $this->assertEquals($this->testPlaylist->userId, $pl->userId);
            $this->assertTrue(in_array($pl->id, $userPlaylistIds));
        }
    }
    
    
    public function testGetUserPlaylistsWithLimit()
    {
        // create a few playlists first
        $entry = array_pop(Kms_Test_Helper::$globalTestEntries);
        $entryId = $entry->id;
        $model = new Application_Model_Playlist();
        $userPlaylists = array($this->testPlaylist);
        
        for($i=0;$i<10;$i++)
        {
            $userPlaylists[] = $model->createPlaylist("test playlist $i", $entryId);
        }
        
        $userPlaylistIds = array();
        foreach($userPlaylists as $userPl)
        {
            $userPlaylistIds[] = $userPl->id;
        }
        $lim = 3;
        $playlists = $model->getUserPlaylists($this->testPlaylist->userId, $lim);
        $this->assertLessThanOrEqual($lim, count($playlists->objects));
        $this->assertLessThanOrEqual(count($userPlaylistIds), count($playlists->objects));
        
        foreach($playlists->objects as $pl)
        {
            $this->assertEquals($this->testPlaylist->userId, $pl->userId);
            $this->assertTrue(in_array($pl->id, $userPlaylistIds));
        }
        
    }
}

