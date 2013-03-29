<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * Resource to control navigation flows
 */

/**
 * Description of Nav
 *
 * @author leon
 */
class Kms_Resource_Nav extends Zend_Application_Resource_ResourceAbstract
{
    
    private static $_nav;
    private static $_categoryTree = NULL;
    private static $_categories;
    private static $_pre = null;
    private static $_post = null;
    private static $_showCategories = true;
    
    public function init()
    {
    }
    
    public static function initNavigation()
    {
        if(!self::$_nav)
        {
            self::$_showCategories = Kms_Resource_Config::getConfiguration('navigation', 'includeCategories');

            self::$_nav = new Zend_Navigation();
            // init categories
            $categoryModel = new Application_Model_Category();
            self::$_categories = $categoryModel->getList();
            //parse the post and the pre... 
            self::$_pre = self::parsePreAndPost(Kms_Resource_Config::getConfiguration('navigation', 'pre'));
            self::$_post = self::parsePreAndPost(Kms_Resource_Config::getConfiguration('navigation', 'post'));
            self::$_categoryTree = self::buildCategoryTree(self::$_categories);
            self::$_nav->addPages(self::$_pre);
            self::$_nav->addPages( self::$_categoryTree );
            self::$_nav->addPages(self::$_post);
            Zend_Registry::set('Zend_Navigation', self::$_nav);
        }
    }
    
    public static function getCategoryTree()
    {
        return self::$_categoryTree;
    }
    
    
    // build recursively a category tree
    public static function buildCategoryTree($categories, $parent =  null, $force = false)
    {
        $visible = (self::$_showCategories || !is_null($parent));
        if(self::$_categoryTree && !$force)
        {
            // tree is already prepared, just return it
            return self::$_categoryTree;
        }
        if(!$categories && self::$_categories)
        {
            $categories = self::$_categories;
        }
        //exit;
        $pages = array();
        $rootCategory = Kms_Resource_Config::getRootGalleriesCategory();
       
        if(is_null($parent))
        {
            // set first parent to root category.
            $parent = $rootCategory;

        }
        
        if(is_array($categories) && count($categories))
        {
            foreach($categories as $category)
            {
                // get the category full name (remove root category from beginning)
                $categoryName = preg_replace('/^'.preg_quote($rootCategory, '/').'>/', '', $category->fullName);
                $categoryName = Kms_View_Helper_String::removeSpecialChars($categoryName);
                // match the category only if it's a direct child of the parent
                if(preg_match('/^'.preg_quote($parent, '/').'>[^>]+$/', $category->fullName))
                {
                    // enter a recursion
                    $children = self::buildCategoryTree($categories, $category->fullName,$force);
                    $pages[] = Zend_Navigation_Page::factory (
                        array(
                        	'type' => 'Kms_Navigation_Page_Mvc',
                            'action' => 'view',
                            'controller' => 'gallery',
                            'route' => 'categoryid',
                            'label' => Kms_View_Helper_String::formatCategoryName($category->name),
                            'params' => array('categoryname' => $categoryName, 'categoryid' => $category->id),
                        	'optionalparam' => 'categoryid',
                            'categoryname' => $categoryName,
                            'catNum' => $category->id,
                            'pages' => $children,
                            'showInMenu' => $visible,
                        )
                    );
                    
                }
            }
        }
        
        return $pages;
    }
    
    private static function parsePreAndPost($array)
    {
        $pages = array();
        $front = Zend_Controller_Front::getInstance();
        if(!count($array)) return $pages;
        $isAnonymous = Zend_Auth::getInstance()->hasIdentity() && Zend_Auth::getInstance()->getIdentity()->getRole() == Kms_Plugin_Access::getRole(Kms_Plugin_Access::ANON_ROLE);
        foreach($array as $navItem)
        {
            switch($navItem->type)
            {
                case 'playlist':
                    if($navItem->value && $navItem->name)
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'action' => 'view',
                                'controller' => 'gallery',
                                'route' => 'playlist',
                                'playlistid' => $navItem->value,
                                'label' => $navItem->name,
                                'params' => array('playlistid' => $navItem->value),
                            )
                        );
                    }
                break;
                case 'category':
                    if($navItem->category && $navItem->name)
                    {
                    	$arr = Application_Model_Config::_extractCategoryIdNameKey($navItem->category); 
                    	$categoryName = Kms_View_Helper_String::removeSpecialChars($arr[1]);
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                            	'type' => 'Kms_Navigation_Page_Mvc',
                                'action' => 'view',
                                'controller' => 'gallery',
                                'route' => 'categoryid',
                                'label' => $navItem->name,
                                'params' => array('categoryname' => $categoryName, 'categoryid' => $arr[0]),
                            	'optionalparam' => 'categoryid',
                                'categoryname' => $navItem->category,
                            )
                        );
                    }
                break;
                case 'my_media':
                    if($front->getPlugin('Kms_Plugin_Access')->hasPermission('user', 'my-media') || $isAnonymous)
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'action' => 'my-media',
                                'controller' => 'user',
                                'route' => 'mymedia',
                                'label' => trim($navItem->name) ? $navItem->name : 'My Media',
                            )
                        );
                    }
                break;
                case 'entry':
                    if($navItem->entryId && $navItem->name)
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'action' => 'play',
                                'controller' => 'entry',
                                'route' => 'entry',
                                'params' => array('id' => $navItem->entryId),
                                'id' => $navItem->entryId,
                                'label' => trim($navItem->name),
                            )
                        );
                    }
                break;
                case 'channels':
                    //if($front->getPlugin('Kms_Plugin_Access')->hasPermission('channels', 'index'))
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'action' => 'index',
                                'controller' => 'channels',
                                'route' => 'channels',
                                'label' => trim($navItem->name) ? $navItem->name : 'Channels',
                            )
                        );
                    }
                break;
                case 'my-channels':
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                        array(
                                'action' => 'mychannels',
                                'controller' => 'channels',
                                'route' => 'channelsmy',
                                'label' => trim($navItem->name) ? $navItem->name : 'My Channels',
                        )
                        );
                    }
                    break;
                case 'my_playlists':
                    if($front->getPlugin('Kms_Plugin_Access')->hasPermission('user', 'my-playlists') || $isAnonymous)
                    {
                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'action' => 'my-playlists',
                                'controller' => 'user',
                                'route' => 'myplaylists',
                                'label' => trim($navItem->name) ? $navItem->name : 'My Playlists',
                            )
                        );
                    }
                        
                break;
                case 'link':
                    
                    if($navItem->value && $navItem->name)
                    {
                        $httpOrHttps = substr($navItem->value, 0, 7);
                        if($httpOrHttps == 'http://' || $httpOrHttps == 'https:/')
                        {
                            $url = $navItem->value;
                            $target = '_blank';
                        }
                        else
                        {
                            // get the view object
                            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
                            if (null === $viewRenderer->view) 
                            {
                                $viewRenderer->initView();
                            }
                            $view = $viewRenderer->view;
                            $url = $view->baseUrl($navItem->value);
                            $target = '_self';
                        }

                        $pages[] = Zend_Navigation_Page::factory (
                            array(
                                'label' => $navItem->name,
                                'uri' => $url,
                                'target' => $target,
                            )
                        );
                    }
                break;
            
            }
            
            
        }
        
        return $pages;
    }
    
    /**
     *
     * @return Zend_Navigation
     */
    public static function getContainer()
    {
        if(!self::$_nav)
        {
            self::initNavigation();
        }
        return self::$_nav;
    }
    
    
    public static function setContainer($nav)
    {
        self::$_nav = $nav;
    }
}

