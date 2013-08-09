<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class GalleryController extends Zend_Controller_Action
{

    private $_flashMessenger;
    
    private $_translate = null;
    
    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');        
        
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');  
        $this->_flashMessenger->setNamespace('default');
        $this->view->messages = $this->_flashMessenger->getMessages();
        
        /* Initialize contexts here */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        if(!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
        if(!$contextSwitch->getContext('script'))
        {
            $scriptC = $contextSwitch->addContext('script', array());
            $scriptC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('view', 'ajax')->initContext();
        $contextSwitch->addActionContext('remove-entry', 'dialog')->initContext();
        $contextSwitch->addActionContext('remove-entry', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
        
    }

    public function indexAction()
    {
        // action body
    }

    
    
    /*
     * default category action for gallery view
     * shows the gallery view (or an ajax view with just thumbs and pager}
     */
    public function viewAction()
    {
        $request = $this->getRequest();
        
        $playlistId = $request->getParam('playlistid');
        $catName = $request->getParam('categoryname');
        $catId = $request->getParam('categoryid');
        $channelName = $request->getParam('channelname');
        $tagId = $request->getParam('tagid');
        $userId = $request->getParam('userid');
        $searchKeyword = $request->getParam('searchkeyword');
        
        $playEntry = $request->getParam('entry');
        if(!$playEntry)
        {
            $playEntry = 0;
        }
        
        $params = array(
            'page' => $request->getParam('page'),
            'sort' => $request->getParam('sort'),
            'type' => $request->getParam('type'),
            'keyword' => $request->getParam('keyword'),
            'tag' => $request->getParam('tag'),
        );
        
        if(empty($params['sort']))
        {
        	//modules may define what is a default sorter for specific type
        	//should be only one module per type
        	$models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
        	foreach ($models as $model)
        	{
        		$sorter = $model->getDefaultSorter(($params['type']));
        		//we should get only one model for type
        		if (!empty($sorter))
        		{
        			$params['sort'] = $sorter;
        			break;
        		}
        	}
        	//if the module doesn't define default sorter - take default for gallery
        	if (empty($params['sort']))
        	{
            	// load the default sort method from the configuration
            	$params['sort'] = Kms_Resource_Config::getConfiguration('gallery', 'sortMediaBy');
        	}
        }
        // assign default keyword to the view
        $this->view->defaultKeyword = $this->_translate->translate('Search this gallery');
        // filter out the default keyword
        if($params['keyword'] == $this->view->defaultKeyword)
        {
            $params['keyword'] = '';
        }
        
        
        // pass the parameters to the view
        $this->view->params = $params;
        
        // pass the gallery type to the view;
        $this->view->presentationView = $params['type'] == 'presentation';
        
        $totalEntries = 0;
        
        $entryModel = Kms_Resource_Models::getEntry();
        
        // filter by categories
        if($catName)
        {
            $this->view->galleryType = 'category';
            // assign category Name to category model , in order to have it available for the modules
            $categoryModel = Kms_Resource_Models::getCategory();
            // $categoryObj = $categoryModel->get($catName);
            $categoryObj = $categoryModel->get($catName, null, $catId);
            if(isset($categoryObj->id))
            {
                $this->view->categoryId = $categoryObj->id;            
            }
            else
            {
                $this->view->categoryId = null;
            }
            
            // check if the category exists in our navigation
            if (!empty($catId)) {
                // check by the category id first - if exists
                $navigationPage = Kms_Resource_Nav::getContainer()->findOneBy('catNum', $catId);
            }
            if(empty($navigationPage)){
                // check by category name for backward compatibilty
                $navigationPage = Kms_Resource_Nav::getContainer()->findOneBy('categoryname', $catName);
            }

            // in case the category does not exist in the navigation controller
            // it can be because the category truly does not exist
            // but also it can be because the category is restricted
            if(is_null($navigationPage))
            {
                
                // check if this category is restricted
                $restrictedCats = Kms_Resource_Config::getConfiguration('categories', 'restricted');
                if(count($restrictedCats))
                {
                    foreach($restrictedCats as $restrictedCat)
                    {
                        if($restrictedCat->category == $catName || preg_match('/^'.preg_quote($restrictedCat->category, '/').'>.*/', $catName))
                        {
                            $identity = Zend_Auth::getInstance()->getIdentity();
                            if($identity && $identity->getRole() != Kms_Plugin_Access::getRole( Kms_Plugin_Access::ANON_ROLE ))
                            {
                                // if user is logged in
                                // redirect to error controller
                                $msg = $this->_translate->translate('Category').' "'.$catName.'" '.$this->_translate->translate('is restricted');
                                $this->_response->setHttpResponseCode(403);
                                $this->_request->setControllerName('Error');
                                $this->_request->setActionName('error');
                                $this->view->message = $msg;
                                return;
                            }
                            else
                            {
                                // redirect anonymous users to login page
                                $errorPage = Kms_Resource_Config::getConfiguration('auth', 'accessDenied');
                                $this->_request->setModuleName($errorPage->module);
                                $this->_request->setControllerName($errorPage->controller);
                                $this->_request->setActionName($errorPage->action);
                                $this->_request->setParam('partnerAccess', false);
                                $this->_request->setParam('accessDenied', true);
                                return;
                            }

                        }
                    }
                }
                // we are here because category is not restricted, but it doesn't exist or entitlement forbids us from getting it
                // throw an access denied error here

                $msg = $this->_translate->translate('Category').' "'.$catName.'" '.$this->_translate->translate('not found or denied');
                Kms_Log::log('gallery: '.$msg, Kms_Log::INFO);
                throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
            }
            
            // check if category has children (to show side navigation)
            if(!count($navigationPage->getPages()))
            {
                $this->view->wideLayout = true;
                // set page size to the wide page size
                $entryModel->setPageSize( Kms_Resource_Config::getConfiguration('gallery', 'pageSizeWide') );
            }
            
            // handle the access to the page (entitlement)
            $accessDenied = false;
            if(isset($categoryObj->privacy))
            {
                if( $categoryObj->privacy == Kaltura_Client_Enum_PrivacyType::AUTHENTICATED_USERS && !Kms_Plugin_Access::isLoggedIn()) // && role is anonymous at least || $categoryObj->privacy == )
                {
                    $accessDenied = true;
                }
                elseif ($categoryObj->privacy == Kaltura_Client_Enum_PrivacyType::MEMBERS_ONLY)
                {
                    $role = $categoryModel->getUserRoleInCategory($categoryObj->fullName, Kms_Plugin_Access::getId(), $categoryObj->id);     
                    if($role == Application_Model_Category::CATEGORY_USER_NO_ROLE)
                    {
                        $accessDenied = true;
                    }
                }
            }
            
            if($accessDenied) // do not show the gallery page, instead show an error
            {
                $msg = $this->_translate->translate('Category').' "'.$catName.'" '.$this->_translate->translate('access denied due to entitlement');
                Kms_Log::log('gallery: '.$msg, Kms_Log::INFO);
                throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
            }
            else
            {
                $entries = $entryModel->getEntriesByCategory($categoryObj->id, $params);
            }
        }
        elseif($channelName)
        {
	    // Setting the Gallery type
	    $this->view->galleryType = 'channel';
	    
            /* channel gallery */
            $model = Kms_Resource_Models::getChannel();
            
            try {
                // get the single channel
                $channel = $model->get($channelName, false, $request->getParam('channelid'));
                $this->view->channel = $channel;
                $this->view->categoryId = $channel->id;
                
                // channel is missing - wrong name in the url, or BE special chars Bug.
                if (empty($this->view->channel)){
                    throw new Zend_Controller_Action_Exception('', 404);
                }
            }
            catch (Kaltura_Client_Exception $e){
                // forward to the 404 page
                throw new Zend_Controller_Action_Exception('', 404);
            }

            // assign default keyword to the view
            $this->view->defaultKeyword = $this->_translate->translate('Search this channel');
            // filter out the default keyword
            if($params['keyword'] == $this->view->defaultKeyword)
            {
                $params['keyword'] = '';
            }
            
            
            $entries = $entryModel->getEntriesByChannel($channel->id, $params);
            
            // set the menu item as active - if exists
            $breadcrumbsContainer = Kms_Resource_Nav::getContainer()->findOneBy('route', 'channels');
            if(empty($breadcrumbsContainer)){
                $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
            }
            
            if(!empty($breadcrumbsContainer)){
                $breadcrumbsContainer->addPage(
                    new Zend_Navigation_Page_Mvc(
                        array(
                                'label' => $channel->name,
                                'controller' => 'gallery',
                                'action' => 'view',
                                'route' => 'channelview',
                                'active' => true,
                                'showInMenu' => false,
                                'params' => array(
                                        'disableSideNav' => true,
                                        'channelid' => $channelName,
                                ),
                        )
                    )
                );
            }
        }
        elseif($playlistId)
        {
            $this->view->galleryType = 'playlist';
            
            $this->view->wideLayout = true;
            // set page size to the wide page size
            $playlistModel = new Application_Model_Playlist();
            $playlistInfo = $playlistModel->get($playlistId);
            // throw a 404 error in case category does not exist
            if(!$playlistInfo)
            {
                $msg = $this->_translate->translate('Playlist').' "'.$playlistId.'" '.$this->_translate->translate('not found');
                Kms_Log::log('gallery: '.$msg, Kms_Log::WARN);
                throw new Zend_Controller_Action_Exception($msg, 404);
            }
            
            $entries = $playlistModel->getEntries($playlistId, Kms_Resource_Config::getConfiguration('gallery', 'pageSizeWide'), $request->getParam('page'));
            $totalEntries = $playlistModel->totalCount;
            /* setup breadcrumbs */
            if(Kms_Resource_Nav::getContainer()->findBy('playlistid', $playlistId))
            {
                $playlistNavLabel = Kms_Resource_Nav::getContainer()->findBy('playlistid', $playlistId)->getLabel();
            }
            else
            {
                $playlistNavLabel = isset($playlistInfo->name) && $playlistInfo->name ? $playlistInfo->name : $playlistId;
            }
            
            if($playlistNavLabel != $this->_translate->translate('Home'))
            {
                $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
                $breadcrumbsContainer->addPage(
                    new Zend_Navigation_Page_Mvc(
                        array(
                            'label' => $this->_translate->translate('Playlist').': '.$playlistNavLabel,
                            'controller' => 'gallery',
                            'action' => 'view',
                            'route' => 'playlist',
                            'active' => true,
                            'showInMenu' => false,
                            'params' => array(
                                'playlistid' => $playlistId,
                            ),
                        )
                    )
                );
            }
            /* end breadcrumbs */
            
        }
        elseif($tagId)
        {
            if($this->getRequest()->getParam('displayname'))
            {
                $tagId = 'displayname_'.$tagId;
            }
            $this->view->galleryType = 'tag';
            $this->view->wideLayout = true;
            // set page size to the wide page size
            $entryModel->setPageSize( Kms_Resource_Config::getConfiguration('gallery', 'pageSizeWide') );
            // set page size to the wide page size
            $entries = $entryModel->getEntriesByTag($tagId, $params);
            
            $breadCrumbsLabel = $this->_translate->translate('Tag').': '.$tagId;
            
            // handle display of createdby with the tag name (trick)
            if(preg_match('/^displayname_(.*)$/', $tagId, $tagMatch))
            {
                if(isset($tagMatch[1]))
                {
                    $breadCrumbsLabel = $this->_translate->translate('Created by').': '.$tagMatch[1];
                }
            }
            
            /* setup breadcrumbs */
            $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
            $breadcrumbsContainer->addPage(
                new Zend_Navigation_Page_Mvc(
                    array(
                        'label' => $breadCrumbsLabel,
                        'controller' => 'gallery',
                        'action' => 'view',
                        'route' => 'tags',
                        'active' => true,
                        'showInMenu' => false,
                        'params' => array(
                        'tagid' => $tagId,
                        ),
                    )
                )
            );
            
            /* end breadcrumbs */
            
            
        }
        elseif($userId) /* created by */
        {
            $this->view->galleryType = 'createdby';
            $this->view->wideLayout = true;
            // set page size to the wide page size
            $entryModel->setPageSize( Kms_Resource_Config::getConfiguration('gallery', 'pageSizeWide') );
            $entries = $entryModel->getEntriesByUserId($userId, $params);
            /* setup breadcrumbs */
            $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
            $page = new Zend_Navigation_Page_Mvc(
                        array(
                            'label' => $this->_translate->translate('Created by').': '.$userId,
                            'uri' => NULL,
                            'controller' => 'gallery',
                            'action' => 'view',
                            'route' => 'createdby',
                            'active' => true,
                            'showInMenu' => false,
                            'params' => array(
                            'userid' => $userId,
                            )
                        )
                    );

//            Zend_Debug::dump($page);
//            exit;
            $breadcrumbsContainer->addPage($page);
            
            /* end breadcrumbs */
        }
        elseif($searchKeyword)
        {
            $this->view->galleryType = 'search';
            $this->view->wideLayout = true;
            $this->view->searchKeyword = $searchKeyword;
            // set page size to the wide page size
            $entries = $entryModel->getEntriesByKeyword($searchKeyword, $params);
            /* setup breadcrumbs */
            $breadcrumbsContainer = Kms_Resource_Nav::getContainer();
            $breadcrumbsContainer->addPage(
                new Zend_Navigation_Page_Mvc(
                    array(
                        'label' => $this->_translate->translate('Search').': '.$searchKeyword,
                        'controller' => 'gallery',
                        'action' => 'view',
                        'route' => 'search',
                        'active' => true,
                        'showInMenu' => false,
                        'params' => array(
                            'searchkeyword' => $searchKeyword,
                        ),
                    )
                )
            );
            
            /* end breadcrumbs */
        }
        else
        {
            Kms_Log::log('gallery: '.$this->_translate->translate('No gallery type specified'), Kms_Log::ERR);
            throw new Zend_Controller_Action_Exception($this->_translate->translate('No gallery type specified'), 500);
        }
        
        if(isset($breadcrumbsContainer))
        {
            // update view helper breadcrumbs with custom container
            $this->view->navigation()->breadcrumbs($breadcrumbsContainer);
        }
        
        // get the total number of entries
        $totalEntries = $totalEntries ? $totalEntries: $entryModel->getLastResultCount();
        
        $this->view->totalEntries = $totalEntries;
        $this->view->entryAsyncMode = true;
        $this->view->entries = $entries;
        
        // init paging
        $pagingAdapter = new Zend_Paginator_Adapter_Null( $totalEntries );
        $paginator = new Zend_Paginator( $pagingAdapter );
        // set the page number
        $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
        // set the number of items per page
        if($this->view->wideLayout)
        {
            $paginator->setItemCountPerPage( Kms_Resource_Config::getConfiguration('gallery', 'pageSizeWide'));
        }
        else
        {
            $paginator->setItemCountPerPage( Kms_Resource_Config::getConfiguration('gallery', 'pageSize'));
        }        // set the number of pages to show
        $paginator->setPageRange(Kms_Resource_Config::getConfiguration('gallery', 'pageCount'));
        $this->view->paginator = $paginator;
        $this->view->pagerType = Kms_Resource_Config::getConfiguration('gallery', 'pagerType');
    }
    
    
    public function removeEntryAction()
    {
        // set allowed to false in the beginning
        $this->view->allowed = false;
        $entryId = $this->getRequest()->getParam('entry');
        $categoryId = $this->getRequest()->getParam('category');
        
        // is this a channel? Am I a manager of this channel?
        $channel = Kms_Resource_Models::getChannel()->getById($categoryId);
        if($channel)
        {
            $this->view->channel = $channel;
        }
        $isChannelManager = !is_null($channel) && Kms_Resource_Models::getChannel()->getUserRoleInChannel($channel->name, Kms_Plugin_Access::getId(), $channel->id) == Kaltura_Client_Enum_CategoryUserPermissionLevel::MANAGER;
        
        $this->view->confirmed = 0;
        if($entryId && $categoryId)
        {
            $model = Kms_Resource_Models::getEntry();
            $entry = $model->get($entryId);
            // check if I am the owner of this entry, or a channel manager
            if(Kms_Plugin_Access::isCurrentUser($entry->userId) || $isChannelManager)
            {
                $this->view->allowed = true;
            
                $this->view->entryId = $entryId;
                $this->view->categoryId = $categoryId;
                
                // check for confirmation
                $confirm = $this->getRequest()->getParam('confirm');
                if($confirm == '1')
                {
                    $this->view->confirmed = 1;
                    // remove the category via Entry model
                    $res = $model->removeCategory($entryId, $categoryId);
                    if($res)
                    {
                        // add a notification
                        $flashMessenger = $this->_flashMessenger->addMessage($this->_translate->translate('Successfully removed media') . '.');
                    }
                }
            }
        }
    }


}



