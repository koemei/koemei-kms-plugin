<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Helper
 *
 * @author leon
 */
class Kms_Test_Helper
{

    public static $globalTestEntries = array();

    //put your code here
    static function getSourceOnlyConversionProfileId()
    {
        $client = Kms_Resource_Client::getAdminClient();
        $res = $client->conversionProfile->listAction();
        foreach ($res->objects as $conversionProfile)
        {
            $flavorParams = explode(',', $conversionProfile->flavorParamsIds);
            if (count($flavorParams) == 1 && $flavorParams[0] === '0')
            {
                return $conversionProfile->id;
            }
        }


        return false;
    }

    
    public static function createTestRootCategory($cat, $context = null)
    {
        $result = null;
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $category = new Kaltura_Client_Type_Category();
        $category->name = $cat;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
        
        if($context){
            $category->privacyContext = $context;
        }
        
        try
        {
            $result = $client->category->add($category);
        }
        catch(Kaltura_Client_Exception $e)
        {
            
        }
        return $result;
    }
    
    public static function createTestEntry($testEntry, $file)
    {
        // TODO - use entitlement without deprecated entry->categories and entry->categoriesIds
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        try
        {
            $testEntry = $client->baseEntry->add($testEntry, Kaltura_Client_Enum_EntryType::AUTOMATIC);
            // echo "added entry $testEntry->id\n";
            self::$globalTestEntries[$testEntry->id] = $testEntry;
            $token = new Kaltura_Client_Type_UploadToken();
            $token->fileName = $file;
            $token->fileSize = filesize($file);
            $res = $client->uploadToken->add($token);
            //echo "uploading media...";
            $uploadedToken = $client->uploadToken->upload($res->id, $file);
            if ($uploadedToken->status == Kaltura_Client_Enum_UploadTokenStatus::FULL_UPLOAD)
            {
                $resource = new Kaltura_Client_Type_UploadedFileTokenResource();
                $resource->token = $uploadedToken->id;
                $updatedEntry = $client->media->addContent($testEntry->id, $resource);
                $client->media->approve($testEntry->id);
                //echo "done\n";
            }
        }
        catch (Kaltura_Client_Exception $e)
        {
            echo "adding entry failed\n"; 
            print_r($e->getMessage());
            echo "\n";
        }
    }

    public static function setUpTestIdentity($id = 'testuser')
    {
        $adapter = new Kms_Auth_Demo();
        $adapter->setId($id);
        $adapter->setRole(Kms_Resource_Config::getConfiguration('roles', 'unmoderatedAdminRole'));
        Zend_Auth::getInstance()->authenticate($adapter);
    }

    public static function createTestImageEntries($numEntriesToCreate = 5)
    {
        echo "creating image entries for tests...\n";
        for ($i = 1; $i <= $numEntriesToCreate; $i++)
        {
            // create a test entry for each test case and get its id
            $testEntry = new Kaltura_Client_Type_MediaEntry();
            //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
            $testEntry->mediaType = Kaltura_Client_Enum_MediaType::IMAGE;
            $testEntry->name = "One Time Test Image - " . $i;
            $testEntry->description = "This is a One Time test Image - " . $i;
            $testEntry->tags = "logotest,some,tags,for,testing";
            $testEntry->categories = Kms_Resource_Config::getConfiguration('categories', 'rootCategory') . ">onetimetest";
            $testEntry->userId = Zend_Auth::getInstance()->getIdentity()->getId();

            $file = APPLICATION_PATH . '/../tests/materials/logo.png';
            self::createTestEntry($testEntry, $file);
        }
        echo "done\n";
    }

    public static function tearDownTestEntries()
    {
        echo "\ndeleting test entries\n";

        foreach (self::$globalTestEntries as $entryId => $entry)
        {
            try
            {
                // try to delete (if not deleted already)
                $client = Kms_Resource_Client::getAdminClient();
                $delEntry = $client->baseEntry->get($entryId);
                $client->baseEntry->delete($delEntry->id);
                // echo "deleted entry $delEntry->id\n";
            }
            catch (Kaltura_Client_Exception $e)
            {
                
            }
        }
    }

    public static function createTestVideoEntries($numEntriesToCreate = 5)
    {
        $conversionProfileId = Kms_Test_Helper::getSourceOnlyConversionProfileId();
        if (!$conversionProfileId)
            die('Must have a SourceOnly conversion Profile for these unit tests');
        echo "creating video entries for tests, this may take a while...";
        // create ten video entries
        for ($i = 1; $i <= $numEntriesToCreate; $i++)
        {
            // create a test entry for each test case and get its id
            $testEntry = new Kaltura_Client_Type_MediaEntry();
            //$testEntry->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
            $testEntry->mediaType = Kaltura_Client_Enum_MediaType::VIDEO;
            $testEntry->name = "One Time Test Video - " . $i;
            $testEntry->description = "This is a One Time test Video - " . $i;
            $testEntry->tags = "logotest,some,tags,for,testing";
            $testEntry->categories = Kms_Resource_Config::getConfiguration('categories', 'rootCategory') . ">onetimetest";
            $testEntry->userId = Zend_Auth::getInstance()->getIdentity()->getId();
            $testEntry->conversionProfileId = $conversionProfileId;

            $file = APPLICATION_PATH . '/../tests/materials/logoblack.flv';
            self::createTestEntry($testEntry, $file);
        }
        $client = Kms_Resource_Client::getAdminClient();
        echo "done\nChecking conversion status...";
        foreach (self::$globalTestEntries as $testEntryId => $entry)
        {

            try
            {
                $startTime = time();
                $entryReady = false;
                while (!$entryReady && time() <= $startTime + 60 * 10)
                {
                    $tmpEntry = $client->baseEntry->get($testEntryId);
                    if ($tmpEntry->status == Kaltura_Client_Enum_EntryStatus::READY)
                    {
                        // echo "finished converting\n";
                        $entryReady = true;
                    }
                    else
                    {
                        // echo "waiting 10 seconds\n";
                        usleep(1000000 * 10);
                    }
                }

                //sleep(10);
            }
            catch (Kaltura_Client_Exception $e)
            {
                
            }
        }
        echo "done\n";
    }

    public static function createTestCategories()
    {
        $client = Kms_Resource_Client::getAdminClient();
        $rootCategory = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');

        $categories = array(
            $rootCategory,
            $rootCategory . '>1',
            $rootCategory . '>2',
            $rootCategory . '>3',
            $rootCategory . '>1>1>1',
            $rootCategory . '>1>1>2',
            $rootCategory . '>1>1>3',
            $rootCategory . '>1>2>1',
            $rootCategory . '>1>2>2',
            $rootCategory . '>1>2>3',
            $rootCategory . '>1>3>1',
            $rootCategory . '>1>3>2',
            $rootCategory . '>1>3>3',
            $rootCategory . '>2>1>1',
            $rootCategory . '>2>1>2',
            $rootCategory . '>2>1>3',
            $rootCategory . '>2>2>1',
            $rootCategory . '>2>2>2',
            $rootCategory . '>2>2>3',
            $rootCategory . '>2>3>1',
            $rootCategory . '>2>3>2',
            $rootCategory . '>2>3>3',
            $rootCategory . '>3>1>1',
            $rootCategory . '>3>1>2',
            $rootCategory . '>3>1>3',
            $rootCategory . '>3>2>1',
            $rootCategory . '>3>2>2',
            $rootCategory . '>3>2>3',
            $rootCategory . '>3>3>1',
            $rootCategory . '>3>3>2',
            $rootCategory . '>3>3>3',
        );
        // create categories via new entry (faster)
        $entry = new Kaltura_Client_Type_BaseEntry();
        $entry->name = 'test category entry';
        $entry->categories = join(',', $categories);

        $newEntry = $client->baseEntry->add($entry);
        
        $client->baseEntry->delete($newEntry->id);
    }

    public static function tearDownTestIdentity($username)
    {
        try
        {
            $userModel = new Application_Model_User();
            $userModel->delete(array($username));
        }
        catch(Kaltura_Client_Exception $e)
        {
            
        }
    }
    
    public static function tearDownTestCategories()
    {
        $client = Kms_Resource_Client::getAdminClientNoEntitlement();
        $rootcat = Kms_Resource_Config::getConfiguration('categories', 'rootCategory');
        $filter = new Kaltura_Client_Type_CategoryFilter();
        $filter->fullNameEqual = $rootcat;
        $categories = $client->category->listAction($filter);
        if ($categories && isset($categories->objects) && count($categories->objects))
        {
            foreach ($categories->objects as $cat)
            {

                try
                {
                    $client->category->delete($cat->id);
                }
                catch (Kaltura_Client_Exception $e)
                {
                    print_r($e->getMessage());
                }
                // $cat = $categories->objects[0];
            }

        }

        //$client->category->delete($rootcat);
    }

    
    public static function cleanUp()
    {
        self::$globalTestEntries = array();
    }

}

