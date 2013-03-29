<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class UserController extends Zend_Controller_Action
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
        if (!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        if (!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
        if (!$contextSwitch->getContext('script'))
        {
            $scriptC = $contextSwitch->addContext('script', array());
            $scriptC->setAutoDisableLayout(false);
        }

        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('my-media', 'ajax')->initContext();
        $contextSwitch->addActionContext('authenticate', 'ajax')->initContext();
        $contextSwitch->addActionContext('login', 'dialog')->initContext();
        $contextSwitch->addActionContext('my-media-clear-cache', 'ajax')->initContext();
        $contextSwitch->addActionContext('my-playlists', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function indexAction()
    {
        // action body
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        // https login (in case not ajax)
        if( !isset($_SERVER['HTTPS'])
                && $request->getParam('format') != 'ajax' 
                && $request->getParam('format') != 'dialog' 
                && $request->getParam('format') != 'script' 
                && Kms_Resource_Config::getConfiguration('auth', 'httpsLogin'))
        {
            $url = 'https://'  
                . $_SERVER['HTTP_HOST'] 
                . $this->view->baseUrl('/user/login')
                . ($request->getParam('ref') ? '?ref=' . str_replace("%2F", "/", urlencode(preg_replace('#^/#', '', $request->getParam('ref')))): '');   

            $this->_helper->getHelper('Redirector')->gotoUrl($url);
        }
        
        // set custom layout for login action
        $this->_helper->layout->setLayout('login');
        
        // create the auth adapter
        $authNAdapter = Kms_Resource_Config::getConfiguration('auth', 'authNAdapter');
        if (class_exists($authNAdapter))
        {
            $adapter = new $authNAdapter();
        }
        else
        {
            $err = $this->_translate->translate('Error in authentication') . '. ' . $this->_translate->translate('Auth Adapter') . ' "' . $authNAdapter . '" ' . $this->_translate->translate('does not exist') . '!';
            Kms_Log::log('login: '.$err, Kms_Log::ERR);
            throw new Zend_Exception($err, 500);
        }

        // check if we have a redirect url
        $loginUrl = $adapter->getLoginRedirectUrl();

        if ($loginUrl)
        {
            if ($loginUrl instanceof Zend_Navigation_Page_Mvc)
            {
                $action = $loginUrl->getAction();
                $controller = $loginUrl->getController();
                $module = $loginUrl->getModule();
                $params = $loginUrl->getParams();

                $ref = '';
                if(isset($params['ref']))
                {
                    $ref = $params['ref'];
                    unset($params['ref']);
                }

                $this->_helper->redirector->setGotoSimple($action, $controller, $module, $params);
                $preUrl = $this->_helper->redirector->getRedirectUrl();
                // TODO: sanitize referer to avoid malicious redirects
                if($ref) $preUrl .= '?ref='.$ref;
                $this->_helper->redirector->gotoUrl($preUrl, array('prependBase' => false));


            }
            elseif (is_string($loginUrl) && Zend_Uri_Http::check($loginUrl))
            {
                $this->_redirect($loginUrl);
            }
            else
            {
                $err = $this->_translate->translate('The Redirect URL') . ' "' . $loginUrl . '" ' . $this->_translate->translate(' is invalid');
                Kms_Log::log('login: '.$err, Kms_Log::ERR);
                throw new Zend_Exception($err, 500);
            }
        }
        elseif ($adapter->loginFormEnabled())
        {
            // this adapter needs a login form
            $form = new Application_Form_Login();
            if(!$adapter->handlePasswordRecovery())
            {
                $form->removeForgotPassword();
            }
            $form->trackReferrer($request);
            if ($request->getParam('format') == 'ajax' || $request->getParam('format') == 'dialog')
            {
                $form->setAttrib('ajax', 1);
                $form->removeElement('login');
            }

            if ($request->getParam('error'))
            {
                $form->addErrorMessage($request->getParam('error'));
            }


            $form->setAction($this->view->baseUrl('/user/authenticate'));
            if ($request->isPost())
            {
                $valid = $form->isValid($request->getPost());
            }

            //$form->render();
            $this->view->form = $form;
            //die(print_r(Zend_Auth::getInstance()->getIdentity()));
            
            if ($request->getParam('format') == 'ajax' || $request->getParam('format') == 'dialog')
            {
                $this->view->render('user/login.ajax.phtml');
            }
        }
    }

    public function authenticateAction()
    {
        $authNAdapter = Kms_Resource_Config::getConfiguration('auth', 'authNAdapter');
        if (class_exists($authNAdapter))
        {
            $adapter = new $authNAdapter();
        }
        else
        {
            $err = $this->_translate->translate('Error in authentication') . '. ' . $this->_translate->translate('Auth Adapter') . ' "' . $authNAdapter . '" ' . $this->_translate->translate('does not exist') . '!';
            Kms_Log::log('login: '.$err, Kms_Log::ERR);
            throw new Zend_Exception($err, 500);
        }

        if(Kms_Resource_Config::getConfiguration('auth', 'demoMode'))
        {
            $authenticationAdapter = new Kms_Auth_Demo(Kms_Plugin_Access::UNMOD_ROLE);
        }
        else
        {
            $authenticationAdapter = new Kms_Auth_Adapter();
        }
        

        // set base value of ref
        $ref = $this->view->baseUrl('/');

        if ($adapter->loginFormEnabled())
        {
            // validate login form
            $form = new Application_Form_Login();

            $request = $this->getRequest();
            $format = $request->getParam('format');

            $form->trackReferrer($request);

            if ($request->isPost())
            {
                // form is invalid
                if (!$form->isValid($request->getPost()))
                {
                    $this->_forward('login');
                    return;
                }
                else
                {
                    $ref = $form->getReferrer($this->view->baseUrl('/'));
                    Kms_Log::trace($ref);
                }
            }
            else
            {
                // no login data was sent
                $this->_forward('login');
                return;
            }
        }
        else
        {
            $ref = $this->_request->getParam('ref') ? $this->_request->getParam('ref') : $this->view->baseUrl('/');
        }

        $auth = Zend_Auth::getInstance();
        try
        {
            $authResult = $auth->authenticate($authenticationAdapter);
            
            // get the new referer
            $authRef = $adapter->getReferer();
            if (!empty($authRef))
            {
                $ref = $authRef;
            }
            
            if (Kms_Resource_Config::getConfiguration('auth', 'httpsLogin'))
            {
            	if(preg_match('#^https://.*#', $ref))
                {
                    $url = $ref;
                }
                else if(preg_match('#^http://.*#', $ref))
                {
                    $url = $ref;
                }
                else
                {
                    $url = 'http://'  
                        . $_SERVER['HTTP_HOST']
                        . '/'   
                        . preg_replace('#^/#', '', $ref);
                }
                $this->_helper->getHelper('Redirector')->gotoUrl($url);
            }
            else
            {
                Kms_Log::log('authentication successfull - redirecting to referrer '.$ref, Kms_Log::DEBUG);
                $this->getResponse()->setRedirect($ref);
            }
        }
        catch (Zend_Exception $e)
        {
            Kms_Log::log("cought exception during login - forwarding to login (internal or external)", Kms_Log::DEBUG);
            if ($adapter->loginFormEnabled())
            {
                $this->_forward('login', 'user', 'default', array('error' => $this->_translate->translate('Invalid Username/Password provided') . '.', 'format' => $format));
                return;
            }
            else
            {
                $loginUrl = $adapter->getLoginRedirectUrl();
                if ($loginUrl instanceof Zend_Navigation_Page_Mvc)
                {
                    $action = $loginUrl->getAction();
                    $controller = $loginUrl->getController();
                    $module = $loginUrl->getModule();
                    $params = $loginUrl->getParams();

					$ref = '';
                    if(isset($params['ref']))
                    {
                        $ref = $params['ref'];
                        unset($params['ref']);
                    }
                    $this->_helper->redirector->setGotoSimple($action, $controller, $module, $params);
					$preUrl = $this->_helper->redirector->getRedirectUrl();
                                        // TODO: sanitize referer to avoid malicious redirects
					$this->_helper->redirector->gotoUrl($preUrl, array('prependBase' => false));
                }
                elseif (is_string($loginUrl) && Zend_Uri_Http::check($loginUrl))
                {
                    $this->_redirect($loginUrl);
                }
                else
                {
                    $err = $this->_translate->translate('The Redirect URL') . ' "' . $loginUrl . '" ' . $this->_translate->translate(' is invalid');
                    Kms_Log::log('login: '.$err, Kms_Log::ERR);
                    throw new Zend_Exception($err, 500);
                }
            }
        }

    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $preLogoutDetails = array(
            'id' => $auth->getIdentity()->getId(),
            'role' => $auth->getIdentity()->getRole(),
        );
        $auth->clearIdentity();
        $auth->setStorage(new Zend_Auth_Storage_Session(Kms_Plugin_Access::STORAGE_USER));
        
        // authenticate with anonymous
        $auth->authenticate(new Kms_Auth_Anonymous());
        $accessPlugin = $this->getFrontController()->getPlugin('Kms_Plugin_Access');
        $accessPlugin->getIdentity();
                
        // create the auth adapter to get the logout url (if exists)
        $authNAdapter = Kms_Resource_Config::getConfiguration('auth', 'authNAdapter');
        if (class_exists($authNAdapter))
        {
            $adapter = new $authNAdapter();
            $adapter->setPreLogoutDetails($preLogoutDetails);
        }
        else
        {
            $err = $this->_translate->translate('Error in authentication') . '. ' . $this->_translate->translate('Auth Adapter') . ' "' . $authNAdapter . '" ' . $this->_translate->translate('does not exist') . '!';
            Kms_Log::log('login: '.$err, Kms_Log::ERR);
            throw new Zend_Exception($err, 500);
        }
        
        // check if auth adapter has a defined logout url
        $logoutUrl = $adapter->getLogoutRedirectUrl();
        if ($logoutUrl)
        {
            // redirect to auth adapter logout url
            $this->_redirect($logoutUrl);
        }
        else
        {
            $this->_redirect('/');
            exit;
        }
    }

    public function unauthorizedAction()
    {
        $this->_helper->layout->setLayout('login');
        $this->getResponse()->setHttpResponseCode(401);
    }

    public function accessDeniedAction()
    {
        $partnerAccess = $this->getRequest()->getParam('partnerAccess');
        
        /* check if identity and role are set, then show an Access Denied page instead of a login page */
        if(Zend_Auth::getInstance()->hasIdentity())
        {
            $id = Zend_Auth::getInstance()->getIdentity();
            $role = $id->getRole();
            if($role != Kms_Plugin_Access::getRole(Kms_Plugin_Access::ANON_ROLE) && $role != Kms_Plugin_Access::getRole(Kms_Plugin_Access::EMPTY_ROLE))
            {
                if(!$partnerAccess && $role != Kms_Plugin_Access::getRole(Kms_Plugin_Access::PARTNER_ROLE))
                {
                    throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);

                }
            }
        }
        $this->getResponse()->setHttpResponseCode(405);
        $this->view->ref = preg_replace('#^/?(.*)/?$#', '$1', $this->getRequest()->getRequestUri());
        // check if the referrer is "logout" and if so, redirect to "/"
        
        if($this->getRequest()->getParam('logout') == true)
        {
            $this->_redirect('/');
        }
        
        
        $this->getRequest()->setParam('ref', $this->getRequest()->getRequestUri());
        $this->view->format = $this->getRequest()->getParam('format');
        if ($partnerAccess)
        {
            $this->_forward('login', 'admin');
            return;
        }
        else
        {
            // https login (in case not ajax)
            if($this->view->format != 'ajax' 
                    && $this->view->format != 'dialog' 
                    && $this->view->format != 'script' 
                    && Kms_Resource_Config::getConfiguration('auth', 'httpsLogin'))
            {
                $ref = str_replace("%2F", "/", urlencode($this->getRequest()->getRequestUri()));
                //$ref = preg_replace('#^/#', '', $ref);
                
                $url = 'https://'  
                    . $_SERVER['HTTP_HOST'] 
                    . $this->view->baseUrl('/user/login')
                    . '?ref=' . $ref ;
                
                $this->_helper->getHelper('Redirector')->gotoUrl($url);
            }
            else
            {
                $this->_forward('login', 'user');
                return;
            }
        }
    }

    // method for invalidating my media cache
    public function myMediaClearCacheAction()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        $entryModel->clearMyMediaCache();
        exit;
    }

    public function myMediaAction()
    {
        $request = $this->getRequest();
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
        	//if the module doesn't define default sorter - take default for my media
        	if (empty($params['sort']))
        	{
        		$params['sort'] = 'recent';
        	}
        }
        $post = $this->getRequest()->getPost();

        if (isset($post['keyword']) && $post['keyword'] === '')
        {
            $params['keyword'] = null;
        }
        $this->view->defaultKeyword = $this->_translate->translate('Search My Media');
        
        if($params['keyword'] == $this->view->defaultKeyword)
        {
            $params['keyword'] = null;
        }

        
        // pass the parameters to the view
        $this->view->wideLayout = TRUE;

        // view type (full / short)
        $this->view->itemType = $request->getParam('view') ? $request->getParam('view') : 'full';

        if ($params['type'] == 'presentation' && !Kms_Resource_Config::getConfiguration('application', 'enablePresentations'))
        {
            $params['type'] = '';
        }
        $this->view->params = $params;
        
        $this->view->presentationView = ($params['type'] == 'presentation');

	    $entryModel = Kms_Resource_Models::getEntry();
        if($this->view->itemType == 'full')
        {
            $pageSize = Application_Model_Entry::MY_MEDIA_PAGE_SIZE;
            
        }
        else
        {
            $pageSize = Application_Model_Entry::MY_MEDIA_LIST_PAGE_SIZE;
        }
        $entryModel->setPageSize($pageSize);
        
        $entries = $entryModel->getMyMedia($params);

        $this->view->entries = $entries;

        $totalEntries = $entryModel->getLastResultCount();

        // init paging
        $pagingAdapter = new Zend_Paginator_Adapter_Null($totalEntries);
        $paginator = new Zend_Paginator($pagingAdapter);
        // set the page number
        $paginator->setCurrentPageNumber($params['page'] ? $params['page'] : 1);
        // set the number of items per page
        $paginator->setItemCountPerPage($pageSize);
        // set the number of pages to show
        $paginator->setPageRange(Kms_Resource_Config::getConfiguration('gallery', 'pageCount'));

        $this->view->paginator = $paginator;
        $this->view->pagerType = Kms_Resource_Config::getConfiguration('gallery', 'pagerType');


        // supported upload types
        //@todo set up the logic to determine these switches
        $this->view->mediaUpload = true;
        $this->view->webcamUpload = true;

        $this->view->presentationUpload = Kms_Resource_Config::getConfiguration('application', 'enablePresentations');
    }

    public function myPlaylistsAction()
    {
        $request = $this->getRequest();
        $this->view->wideLayout = TRUE;

        // view type (full / short)
        $this->view->itemType = $request->getParam('view') ? $request->getParam('view') : 'full';


        $userId = Kms_Plugin_Access::getId();

        $playlistsModel = Kms_Resource_Models::getPlaylist();
        $entryModel = Kms_Resource_Models::getEntry();
        $playlists = $playlistsModel->getUserPlaylists($userId);
        // uncomment to test "no playlists" mode
//        $playlists = array(); 
        if (isset($playlists->objects) && count($playlists->objects))
        {
            $playlists = $playlists->objects;

            $playlistId = $request->getParam('id');
            if (!$playlistId)
            {
                // get the id of the first playlist in the list
                $playlistId = reset($playlists)->id;
            }

            $playlistsModel->setId($playlistId);

            $entries = $playlistsModel->getEntries($playlistId);
            if (count($entries))
            {
                foreach ($entries as $key => $entry)
                {
                    if (!($entry instanceof Kaltura_Client_Type_BaseEntry) || !($entry instanceof Kaltura_Client_Type_MediaEntry))
                    {
                        unset($entries[$key]);
                    }
                }
            }

            $this->view->params = array(
                'id' => $playlistId,
                'view' => $request->getParam('view'),
            );
            $this->view->playlistId = $playlistId;
            $this->view->playlists = $playlists;

            $this->view->entries = $entries;
        }
        else
        {
            $this->_forward('no-playlists');
            return;
        }
    }

    public function noPlaylistsAction()
    {
        
    }
    
    public function keepAliveAction()
    {
        // session keepalive action (empty action, nothing is done here)
        exit;
    }

}
