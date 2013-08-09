<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Category
 *
 * @author leon
 */
class Application_Model_CategoryTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    private $testPlaylist = null;
    private static $validTestCategory;
    
    public static function setUpBeforeClass()
    {
        echo "Starting tests on models/Category.php\n";
        
        self::$validTestCategory = 'CategoryUnitTestValid' . rand(1, 1000);
        $rootCategory = Kms_Test_Helper::createTestRootCategory(self::$validTestCategory);
        
        $client = Kms_Resource_Client::getAdminClient();
        $category = new Kaltura_Client_Type_Category();
        
        $category->name = "site";
        $category->parentId = $rootCategory->id;
        $siteCategory = $client->category->add($category);
        
        $category->name = "galleries";
        $category->parentId = $siteCategory->id;
        $client->category->add($category);
        
        $category->name = "channels";
        $category->parentId = $siteCategory->id;
        $client->category->add($category);
        
        // create some test categories
        // Kms_Test_Helper::createTestCategories();
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        Kms_Test_Helper::tearDownTestCategories();
    }

    public static function tearDownAfterClass()
    {
        // Kms_Test_Helper::tearDownTestCategories();        
        echo "Finished tests on models/Category.php\n-----------------------------------------\n";
    }


    
    /*******************************************************/
    
    /**
     *  START THE TESTS HERE
     */
    
    
    public function testCanListCategories()
    {
      
    }
    
    /**
     * test that we get the correct parent categories
     */
    public function testGetParentCategory()
    {
        Kms_Resource_Config::setConfiguration('categories', 'rootCategory', self::$validTestCategory);
        
        $model = Kms_Resource_Models::getCategory();
        
        // channels parent category
        $category = $model->get('site>channels');
        $categoryId = $model->getParentCategoryId('site>channels');
        
        $this->assertTrue($category->id == $categoryId,'parent category id match');
        
        // galleries parent category
        $category = $model->get('site>galleries');
        $categoryId = $model->getParentCategoryId();
        
        $this->assertTrue($category->id == $categoryId,'parent category id match');
    }
    
    /**
     * test the membership calculation
     */
    public function testGetMembership()
    {
        $model = Kms_Resource_Models::getCategory();
    
        $category = new Kaltura_Client_Type_Category();
    
        // private
        $category->privacy = Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MODERATOR;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::CATEGORY_MEMBERS_ONLY;
    
        $membership = $model->getMembership($category);
    
        $this->assertTrue($membership == Application_Model_Category::MEMBERSHIP_PRIVATE, 'not private membership ' . $membership);
    
        // restricted
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::MODERATOR;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
    
        $membership = $model->getMembership($category);
    
        $this->assertTrue($membership == Application_Model_Category::MEMBERSHIP_RESTRICTED, 'not restricted membership ' . $membership);
    
        // open
        $category->privacy = Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS;
        $category->contributionPolicy = Kaltura_Client_Enum_ContributionPolicyType::ALL;
        $category->appearInList = Kaltura_Client_Enum_AppearInListType::PARTNER_ONLY;
    
        $membership = $model->getMembership($category);
    
        $this->assertTrue($membership == Application_Model_Category::MEMBERSHIP_OPEN, 'not open membership ' . $membership);
    }
}

