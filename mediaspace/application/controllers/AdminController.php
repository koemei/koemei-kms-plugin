<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class AdminController extends Zend_Controller_Action
{

    private $_flashMessenger;
    private $_translate = null;

    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');

        $this->_helper->layout->setLayout('admin');
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
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

        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
//        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('user-add', 'dialog')->initContext();
        $contextSwitch->addActionContext('user-edit', 'dialog')->initContext();
        $contextSwitch->addActionContext('user-bulkupload', 'dialog')->initContext();
        $contextSwitch->addActionContext('user-delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('clear-cache', 'dialog')->initContext();
        $contextSwitch->addActionContext('enable-debug', 'dialog')->initContext();
        
        $contextSwitch->addActionContext('deploy', 'dialog')->initContext();
//        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('generate-password', 'ajax')->initContext();
        $contextSwitch->addActionContext('user-save', 'ajax')->initContext();
        $contextSwitch->addActionContext('user-delete', 'ajax')->initContext();
        $contextSwitch->addActionContext('config-field', 'ajax')->initContext();
        $contextSwitch->addActionContext('save-config', 'ajax')->initContext();
        $contextSwitch->addActionContext('kb-interface', 'ajax')->initContext();
        $contextSwitch->addActionContext('import-config', 'ajax')->initContext();
        $contextSwitch->addActionContext('log-viewer', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();

        // authenticate based on KS
        $ks = $this->_request->getParam('ks');

        if ($ks)
        {
            $adapter = new Kms_Auth_Admin(false, false, null, null, false, $ks);

            try
            {
                $auth = Zend_Auth::getInstance();
                $storage = new Zend_Auth_Storage_Session(Kms_Plugin_Access::STORAGE_ADMIN);
                $auth->setStorage($storage);
                $authResult = $auth->authenticate($adapter);


                $params = $this->getRequest()->getParams();

                if (isset($params['ref']))
                {
                    $urlparts = parse_url($params['ref']);
                }
                else
                {
                    $urlparts = parse_url($this->getRequest()->getRequestUri());
                }
                $path = $urlparts['path'];

                // TODO: sanitize redirect URL to avoid malicious redirects
                $this->_redirect($path, array('prependBase' => false));
                //$this->_redirect()
            }
            catch (Zend_Exception $e)
            {
                Zend_Debug::dump($e);
            }
        }
    }

    public function loginAction()
    {
        // action body
        $request = $this->getRequest();
        $form = new Application_Form_Login();
        $form->trackReferrer($request);

        // set custom layout for login action
        $this->_helper->layout->setLayout('login');

        $form->adminLogin();
        if ($request->isPost())
        {
            $form->isValid($request->getPost());
        }

        $form->render();
        $this->view->form = $form;
        $this->render('login');
    }

    public function authenticateAction()
    {
        $form = new Application_Form_Login();

        $request = $this->getRequest();
        $form->trackReferrer($request);
        $form->adminLogin();
        if ($request->isPost())
        {
            if ($form->isValid($request->getPost()))
            {
                $auth = Zend_Auth::getInstance();
                $storage = new Zend_Auth_Storage_Session(Kms_Plugin_Access::STORAGE_ADMIN);
                $auth->setStorage($storage);
                $login = $request->getParam('Login');
                $username = $login['username'];
                $password = $login['password'];

                $adapter = new Kms_Auth_Admin($username, $password, null, null, false);

                try
                {
                    $authResult = $auth->authenticate($adapter);
                }
                catch (Zend_Exception $e)
                {
                    
                }
                //$request-
                $ref = $form->getReferrer($this->view->baseUrl('/admin/login'));
                $this->getResponse()->setRedirect($ref);
            }
            else
            {
                $this->_forward('login', 'admin');
            }
        }
    }

    public function indexAction()
    {
        $this->_redirect('admin/config');
        // action body
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();

        $ref = $this->getRequest()->getParam('ref', $_SERVER['HTTP_REFERER']);
        if (!$ref)
        {
            $ref = $this->view->baseUrl('/admin/login');
        }

        // TODO: sanitize referer to avoid malicious redirects
        $this->_redirect($ref);
    }

    public function userAddAction()
    {
        // action body
        $form = new Application_Form_EditUser();
        $form->setAttribs(array(
            'id' => 'userForm',
            'ajax' => 1
        ));
        $form->setAction($this->view->baseUrl('/admin/user-save'));
        $form->setMethod("POST");

        // if authentication is not done via kaltura do not allow manage passwords
        if (Kms_Resource_Config::getConfiguration('auth', 'authNAdapter') != 'Kms_Auth_AuthN_Kaltura')
        {
            $form->removeElement('passwordLinks');
            $form->removeElement('password');
            $form->removeElement('password2');
        }

        $this->view->headerText = $this->_translate->translate('Add New User');

        $form->render();
        $this->view->form = $form;
        $this->render('userEdit');
    }

    public function userEditAction()
    {
        /* get the user details from Kaltura */
        $userId = $this->getRequest()->getParam('id');
        $userModel = Kms_Resource_Models::getUser();
        $user = $userModel->get($userId);
        $this->view->user = $user;

        $form = new Application_Form_EditUser();

        $form->setAttribs(array(
            'id' => 'userForm',
            'ajax' => 1,
        ));

        $form->setAction($this->view->baseUrl('/admin/user-save'));
        $form->setMethod("POST");
        $userRole = $userModel->getRole()? $userModel->getRole(): null;
        if($userRole === false)
        {
            $userRole = Kms_Auth_AuthZ_Kaltura::parseRole($user->partnerData);
        }

        $form->populate(array(
            'newuser' => '0',
            'username' => $userId,
            'firstname' => $user->firstName,
            'lastname' => $user->lastName,
            'email' => $user->email,
            'role' => $userRole,
            'extradata' => Kms_Auth_AuthN_Kaltura::parseExtraData($user->partnerData),
        ));

        // for the edit action, password is not required, and if left blank, then it will not be changed...
        $form->getElement('password')->setRequired(false);
        $form->getElement('password2')->setRequired(false);

        // if authentication is not done via kaltura do not allow manage passwords
        if (Kms_Resource_Config::getConfiguration('auth', 'authNAdapter') != 'Kms_Auth_AuthN_Kaltura')
        {
            $form->removeElement('passwordLinks');
            $form->removeElement('password');
            $form->removeElement('password2');
        }

        // disable editing of username
        $form->getElement('username')->setAttrib('readonly', 'readonly');

        $this->view->headerText = $this->_translate->translate('Edit User') . ' ' . $userId;

        $form->render();
        $this->view->form = $form;
        // action body
    }

    public function userDeleteAction()
    {
        $userId = $this->getRequest()->getParam('id');
        if (trim($userId))
        {
            $this->view->showDialog = true;
            $userIdArray = explode(',', $userId);
            $this->view->userIdArray = $userId;
            if (count($userIdArray) > 1)
            {
                $this->view->dialogText = $this->_translate->translate("Are you sure you want to delete") . " " . count($userIdArray) . " " . $this->_translate->translate('users') . "?";
                $successMessage = count($userIdArray) . ' ' . $this->_translate->translate('users were deleted successfully');
            }
            elseif (count($userIdArray) == 1)
            {
                $this->view->dialogText = $this->_translate->translate("Are you sure you want to delete the user") . " \"" . $userId . "\"?";
                $successMessage = $this->_translate->translate('The user') . ' ' . $userId . ' ' . $this->_translate->translate('was deleted successfully');
            }
            $this->view->userId = $userIdArray;

            $this->view->confirm = $this->getRequest()->getParam('confirm');
            if ($this->view->confirm == '1')
            {
                $userModel = Kms_Resource_Models::getUser();
                $res = $userModel->delete($userIdArray);

                if ($res)
                {
                    $this->_flashMessenger->addMessage($successMessage);
                }
            }
        }
        else
        {
            $this->view->showDialog = false;
        }
    }

    public function userSaveAction()
    {
        $form = new Application_Form_EditUser();
        $form->removeDecorator('form');

        $request = $this->getRequest();
        $this->view->formValid = false;
        $this->view->redirectToFirstPage = false;

        if ($request->isPost())
        {
            $data = $request->getPost();


            // only handle password setup if kaltura is used as authentication mechanism
            if (Kms_Resource_Config::getConfiguration('auth', 'authNAdapter') == 'Kms_Auth_AuthN_Kaltura')
            {
                // check if passwords can be left blank or not
                if ($data['user']['newuser'] != '1')
                {
                    $form->getElement('password')->setRequired(false);
                    $form->getElement('password2')->setRequired(false);
                }


                // check for passwords matching each other
                $form->getElement('password')->addValidator(new Zend_Validate_Identical($data['user']['password2']));
                if ($data['user']['password'] != $data['user']['password2'])
                {
                    $form->getElement('password')->addErrorMessage($this->_translate->translate('Passwords do not match'));
                }
            }
            else
            {
                $form->removeElement('passwordLinks');
                $form->removeElement('password');
                $form->removeElement('password2');
            }

            if ($form->isValid($data))
            {
                $this->view->formValid = true;
                $userModel = Kms_Resource_Models::getUser();

                if ($data['user']['newuser'] == '1')
                {
                    try
                    {
                        $userObject = $userModel->add($data['user']);
                        if ($userObject)
                        {
                            $this->_flashMessenger->addMessage($this->_translate->translate('The User') . ' "' . $data['user']['username'] . '" ' . $this->_translate->translate('was added successfully'));
                        }
                        $this->view->redirectToFirstPage = true;
                    }
                    catch (Kaltura_Client_Exception $e)
                    {
                        if ($e->getCode() == 'DUPLICATE_USER_BY_ID')
                        {
                            // duplicate user id, return error
                            $form->getElement('username')->addError($this->_translate->translate('This User ID is taken. Please choose another') . '.');
                            $this->view->formValid = false;
                        }
                        elseif($e->getCode() == 'INVALID_FIELD_VALUE')
                        {
                            $form->getElement('username')->addError($this->_translate->translate('User ID must be a string or an email address, no spaces') . '.');
                            $this->view->formValid = false;
                        }
                    }
                }
                else
                {
                    /* get the user details from Kaltura */
                    $userId = $data['user']['username'];
                    $userModel = Kms_Resource_Models::getUser();
                    $user = $userModel->get($userId);
                    $userObject = $userModel->update($user, $data['user']);
                    if ($userObject)
                    {
                        $this->_flashMessenger->addMessage($this->_translate->translate('The User') . ' "' . $userId . '" ' . $this->_translate->translate('was saved successfully'));
                    }
                }
            }
        }

        $form->render();
        $this->view->form = $form;
    }

    public function userListAction()
    {
        $form = new Application_Form_UserListFilters();
        $filter = null;
        $pageSize = 20;
        $page = $this->_request->getParam('page');
        if (!$page)
        {
            $page = 1;
        }

        $keyword = $this->_request->getParam('keyword');
        $role = $this->_request->getParam('role');
        $searchBy = $this->_request->getParam('searchBy');
        if($keyword || $role)
        {
            $filter = Application_Model_User::getStandardFilter();
            if(!is_null($keyword) && $searchBy == Application_Form_UserListFilters::KEYWORD_SEARCH_BY_EMAIL)
            {
                Application_Model_User::addFilterByEmail($filter, $keyword);
            }
            elseif(!is_null($keyword))
            {
                Application_Model_User::addFilterByName($filter, $keyword);
            }

            if(!is_null($role) && $role) Application_Model_User::addFilterByRole($filter, $role);

            $form->populate($this->_request->getParams());
        }
        else
        {
            $populateParams = array('searchBy' => Application_Form_UserListFilters::KEYWORD_SEARCH_BY_NAME);
            $form->populate($populateParams);
        }

        $this->view->pagehead = $this->_translate->translate('User Management');
        $this->view->headTitle($this->_translate->translate('User Management'));

        $this->view->managePasswords = false;
        // if authentication is not done via kaltura do not allow manage passwords
        if (Kms_Resource_Config::getConfiguration('auth', 'authNAdapter') == 'Kms_Auth_AuthN_Kaltura')
        {
            $this->view->managePasswords = true;
        }

        $this->view->users = array();

        // retreive the roles
        $roles = Kms_Resource_Config::getRoles()->toArray();

        // retrieve the users
        $userModel = Kms_Resource_Models::getUser();
        $users = $userModel->getUsers($pageSize, $page, $filter);
        $totalCount = $userModel->getTotalCount();

        $this->view->filterForm = $form->render();

        if ($totalCount && count($users))
        {
            $userIds = array();
            // iterate over the users 
            foreach ($users as $user)
            {
                $hasPassword = Kms_Auth_AuthN_Kaltura::parsePassword($user->partnerData);
                $extraData = Kms_Auth_AuthN_Kaltura::parseExtraData($user->partnerData);
                $userIds[] = $user->id;

                $this->view->users[$user->id] = array(
                    'id' => $user->id,
                    'password' => $hasPassword ? '*****' : '',
                    'role' => '',
                    'roleExists' => array_key_exists($role, $roles),
                    'email' => $user->email,
                    'extradata' => $extraData,
                    'userObject' => $user,
                );
            }

            $usersRoles = $userModel->getKalturaUsersRoles($userIds);
            foreach($usersRoles as $userId => $role)
            {
                $this->view->users[$userId]['role'] = ($role == Kms_Plugin_Access::EMPTY_ROLE)? '': $role;
                $this->view->users[$userId]['roleExists'] = in_array($role, $roles);
            }

            $this->view->totalCount = $totalCount;

            $adapter = new Zend_Paginator_Adapter_Null($this->view->totalCount);
            $paginator = new Zend_Paginator($adapter);
            $paginator->setCurrentPageNumber($page);
            $paginator->setItemCountPerPage($pageSize);
            $paginator->setPageRange(10);
            $paginator->setCacheEnabled(false);
            $this->view->paginator = $paginator;
        }
    }

    public function userBulkuploadAction()
    {
        $showForm = true;
        $request = $this->getRequest();
        $this->view->headerText = $this->_translate->translate('Submit Users CSV');
        $form = new Application_Form_UserBulkUpload();
        if($request->isPost())
        {
            if ($form->isValid($this->getRequest()->getPost()))
            {
                $destination = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';
                $uploadedFilePath = $this->handleSingleFileUpload('csv', false, 1000000, 'csvFile', $destination);
                if ($uploadedFilePath !== false)
                {
                    $client = Kms_Resource_Client::getAdminClient();
                    try
                    {
                        $buData = new Kaltura_Client_BulkUploadCsv_Type_BulkUploadCsvJobData();
                        $res = $client->user->addFromBulkUpload($uploadedFilePath, $buData);
                        $showForm = false;
                        $this->_flashMessenger->addMessage($this->_translate->translate('The CSV was submitted. You can track the progress of your import from Kaltura Management Console (KMC)'));
                        $this->_redirect('admin/user-list');
                    }
                    catch(Kaltura_Client_Exception $ex)
                    {
                        Kms_Log::log('admin: user bulk upload - faile due to Kaltura error: '.$ex->getMessage(), Kms_Log::ERR);
                        $this->_flashMessenger->addMessage($this->_translate->translate('! Failed to submit CSV !'));
                        $this->_redirect('/admin/user-list');
                    }
                }
            }
            else
            {
                Kms_Log::log('File Upload Error - '.Kms_Log::printData($form->getErrors()));
            }

        }

        if($showForm)
        {
            $form->setAction($this->view->baseUrl('/admin/user-bulkupload'));
            $this->view->form = $form->render();
        }
    }

    public function generatePasswordAction()
    {
        
    }

    public function configAction()
    {
        $configModel = new Application_Model_Config();
        $tabs = $configModel->getTabs();
        
        if(is_array($tabs) && isset($tabs['modules']))
        {
            ksort($tabs['modules']);
        }
        
        $this->view->globalTabs = $tabs['global'];
        $this->view->moduleTabs = $tabs['modules'];

        $currentTab = $this->_request->getParam('tab');
        if (!$currentTab)
        {
            $currentTab = $configModel->getFirstTabName();
        }

        $this->view->cannotWrite = false;
        if (!$configModel->checkWritable($currentTab))
        {
            $this->view->cannotWrite = '<strong>' . $this->_translate->translate('The config file') . ' ' . $configModel->getConfigFileName($currentTab) . ' ' . $this->_translate->translate('is not writable') . '. ' . $this->_translate->translate('Save is disabled') . '.</strong>';
        }

        $this->view->currentTab = $currentTab;

        $this->view->configs = $configModel->getTabConfigs($currentTab);
        $this->view->info = $configModel->getTabInfo($currentTab);
        
        $this->view->pagehead = $this->_translate->translate('Configuration Management');
        $this->view->headTitle($this->_translate->translate('Configuration Management'));

        $this->view->autoCompleteConfigs = $configModel->listAllConfigs();

        $this->view->importantItems = $configModel->listImportantItems();
    }

    public function saveConfigAction()
    {
        $tab = $this->_request->getParam('tab');
        $post = $this->_request->getPost();
        $model = new Application_Model_Config();
        $tabs = $model->getTabs();
        $this->view->globalTabs = $tabs['global'];
        $this->view->moduleTabs = $tabs['modules'];


        $this->view->headerText = $this->_translate->translate('Configuration saved');
        try
        {
            $backupFile = true;
            if(file_exists($model->getConfigFileName($tab)))
            {
                $newConfigFile = false;
                $backupFile = $model->backupConfigFile($tab);
            }
            else
            {
                $newConfigFile = true;
            }
        }
        catch (Zend_Exception $e)
        {
            $backupFile = false;
            $this->view->headerText = $this->_translate->translate('Failed to save configuration');
            $this->view->dialogText = $e->getMessage();
            $this->view->dialogText .= '-- ' . $this->_translate->translate('Please make sure that the config files backup folder exists, and that it is writable') . '.';
            $this->view->success = false;
        }

        if ($backupFile)
        {
            $this->view->tab = $tab;
            try
            {
                $result = $model->saveConfig($tab, $post);
                if (is_array($result))
                {
                    $message = $result['extraMessage'];
                    $result = $result['result'];
                }
                if ($result)
                {
                    Kms_Resource_Cache::apiWipe();
                    Kms_Resource_Cache::appWipe();
                    $this->view->dialogText = $this->_translate->translate('Your configuration for') . ' "' . ucfirst($tab) . '" ' . $this->_translate->translate('was saved') . '. ' . $this->_translate->translate('The cache was cleared') . '. ';
                    if(!$newConfigFile)
                    {
                        $this->view->dialogText .= $this->_translate->translate('We saved a backup file of your configuration under') . ' "' . $backupFile . '".';
                    }
                    if ($message)
                    {
                        $this->view->dialogText .= $message;
                    }
                    $this->view->success = true;
                }
                else
                {
                    $this->view->headerText = $this->_translate->translate('Saving Configuration') . ': ' . $this->_translate->translate('FAILED');
                    $this->view->success = false;
                    $this->view->dialogText = $this->_translate->translate('An error occurred while saving your configuration') . ($message ? ':<strong> ' . $message . '. </strong> ' : '.') . $this->_translate->translate('Please check the logs for details') . '.';
                    
                    //$this->_redirect( $this->_helper->url('config', 'admin', null, array('tab' => $tab)));
                }
            }
            catch (Zend_Exception $e)
            {
                $this->view->headerText = $this->_translate->translate('Saving Configuration') . ': ' . $this->_translate->translate('FAILED');
                $this->view->success = false;
                $this->view->dialogText = $e->getMessage();
            }
        }
    }

    public function configFieldAction()
    {
        $tab = $this->_request->getParam('tab');
        $field = $this->_request->getParam('field');
        $parent = $this->_request->getParam('parent');
        $belongsTo = $this->_request->getParam('belongsTo');
        $noLabel = $this->_request->getParam('noLabel');

        $model = new Application_Model_Config();

        $this->view->target = $this->_request->getParam('target');
        $this->view->currentTab = $tab;
        $field = $model->getOneField($tab, $field, $parent, $belongsTo, $noLabel);
        $this->view->field = $field;
        $this->view->fields = array($field);
    }

    public function clearCacheAction()
    {
        Kms_Resource_Cache::apiWipe();
        Kms_Resource_Cache::appWipe();
        $this->view->pagehead = $this->_translate->translate("Clear Cache");
    }

    public function unitTestsAction()
    {
        $this->view->pagehead = "Running Unit Tests on Kaltura MediaSpace";
        // set the hash for the unit testing
        $info = rand(10000, 999999);
        $salt = Kms_Resource_Config::getConfiguration('client', 'adminSecret');
        $sig = md5($salt . $info);
        $testhash = base64_encode($sig . '|' . $info);
        $this->view->testUrl = 'runtests.php?h=' . $testhash;
    }

    public function logViewerAction()
    {
        $this->view->pagehead = $this->_translate->translate('Log File Viewer');
        $uniqueId = $this->getRequest()->getParam('uniqueid');
        $logType = $this->getRequest()->getParam('type');
        $requestId = $this->getRequest()->getParam('requestid');
        switch ($logType)
        {
            case 'trace':
                $logPath = Kms_Resource_Config::getTraceLogPath();
                break;
            case 'api':
                $logPath = Kms_Resource_Config::getStatsLogPath();
                break;
            case 'apidebug':
                $logPath = Kms_Resource_Config::getClientLogPath();
                break;
            case 'apierrors':
                $logPath = Kms_Resource_Config::getClientErrorLogPath();
                break;
            case 'general':
            default:
                $logPath = Kms_Resource_Config::getLogPath();
                break;
        }

        if (!$uniqueId)
        {
            // get the last unique ids
            $cmd = 'tail -2000 ' . escapeshellarg($logPath) . ' | awk \'{ if($5) print $5 }\' | uniq';
            $lastUniqueIds = `$cmd`;
            if ($lastUniqueIds)
            {
                $lastUniqueIds = explode("\n", trim($lastUniqueIds));
                $uniqueId = preg_replace('#^\[(.*)\]$#', '$1', trim(array_pop($lastUniqueIds)));
            }
        }

        if ($uniqueId)
        {
            $level = $this->getRequest()->getParam('level');

            if (!$level)
            {
                $level = Kms_Log::DEBUG;
            }


            $filteredLines = array();
            // parse the log files
            // this only works on linux environment
            if($requestId)
            {
                $fp = popen('grep ' . escapeshellarg($uniqueId) . ' ' . escapeshellarg($logPath) .' | grep '.  escapeshellarg('\[request: '.$requestId.'\]'), 'r');
            }
            else
            {
                $fp = popen('grep ' . escapeshellarg($uniqueId) . ' ' . escapeshellarg($logPath), 'r');
            }
            while ($line = fgets($fp, 102400))
            {
                $lineParts = explode(' ', $line);
                //Zend_Debug::dump($line);
                $severityNumber = null;
                if (count($lineParts))
                {
                    // get the severity 
                    $severity = isset($lineParts[5]) ? $lineParts[5] : false;
                    if ($severity)
                    {
                        $matches = null;
                        preg_match('#\((\d)\)#', $severity, $matches);
                        if (count($matches) && isset($matches[1]))
                        {
                            $severityNumber = $matches[1];
                        }
                    }
                }
                // filter by severity
                if ($severityNumber && $severityNumber <= $level)
                {
                    $filteredLines[] = $line;
                }
            }

            if (count($filteredLines))
            {
                $this->view->lines = $filteredLines;
            }
            $this->view->level = $level;
            $this->view->logType = $logType;
        }

        $this->view->uniqueId = $uniqueId;
    }

    public function exportConfigAction()
    {
        $configValues = Kms_Resource_Config::getConfigObject();
        if (isset($configValues['config']) && count($configValues['config']))
        {
            $backupConf = new Zend_Config(array(), array('allowModifications' => true));
            $backupConf->merge($configValues['config']);

            // get the modules config
            if (isset($configValues['modules']) && count($configValues['modules']))
            {
                foreach ($configValues['modules'] as $module => $config)
                {
                    $sectionName = 'module_' . $module;
                    $backupConf->$sectionName = $config;
                }
            }
            //$backupConf->toArray();
            $writer = new Zend_Config_Writer_Ini();
            $writer->setConfig($backupConf);
            $this->getResponse()->setHeader('Content-Type', 'text/html');
            $this->getResponse()->setHeader("Content-Disposition", "attachment; filename=" . 'kms_config.' . date('Y-m-d--H-i-s', time()) . '.ini');
            $this->getResponse()->setBody($writer->render());
            $this->getResponse()->sendResponse();
        }
        exit;
    }

    public function importConfigAction()
    {
        $this->_helper->layout->setLayout('setup');
        $this->view->customHeaderText = $this->_translate->translate('Kaltura MediaSpace&trade; Configuration');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl('/css/setup.css'));
        $form = new Application_Form_ImportConfig();
        $res = false;
        $this->view->form = $form;
        $this->view->pagehead = $this->_translate->translate('Import Configuration');
        
        
        
        if ($this->getRequest()->isPost())
        {
            $keepPartnerSettings = $this->getRequest()->getParam('keepPartner');
            
            if ($form->isValid($this->getRequest()->getPost()))
            {
                $destination = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'backups';
                $uploadedFilePath = $this->handleSingleFileUpload('ini', true, 10000, 'configFile', $destination);
                if ($uploadedFilePath !== false)
                {
                    $res = $this->doImport($uploadedFilePath, $keepPartnerSettings);
                }
            }
        }
        
        if($res)
        {
            Kms_Resource_Cache::apiWipe();
            Kms_Resource_Cache::appWipe();

            $this->view->dialogText = $this->_translate->translate('Your configuration was imported, and the cache was cleared. Click OK to go back to the ').$this->_translate->translate('Configuration Management');
            $this->view->success = true;
            $this->view->headerText = $this->_translate->translate("Configuration saved");
            $this->render('import-success');
        }
        else
        {
            $this->view->dialogText = $this->_translate->translate('The configuration import process has failed.').' '.$this->_translate->translate('Perhaps you can try again with a different file?');
            $this->view->success = false;
            $this->view->headerText = $this->_translate->translate("Failed to save configuration");
        }
        
    }

    private function doImport($path, $keepPartnerSettings = true)
    {
        $ini = new Zend_Config_Ini($path, null, array('allowModifications' => true));
        // iterate over the ini to get the modules out
        $configModel = new Application_Model_Config();
        foreach($ini as $section => $config)
        {
            if(preg_match('/^module_.*$/', $section))
            {
                // this is a module configuration section
                $sectionName = preg_replace('/^module_(.*)$/', '$1', $section );
            }
            else
            {
                $sectionName = $section;
            }
            
            if($keepPartnerSettings !== '0' && $sectionName == 'client')
            {
                // skip the client section if we dont want to override partner settings (partnerid secrets and etc)
                continue;
            }
            try
            {
                $configModel->saveConfig($sectionName, $config->toArray());
            }
            catch(Zend_Controller_Exception $e)
            {
                Kms_Log::log('admin: failed to import configuration from uploaded ini file', Kms_Log::WARN);
                return false;
            }
        }
        return true;
    }
    
    
    private function handleSingleFileUpload($extension, $forceCase, $maxSize, $postFieldId, $destination)
    {
        try
        {
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->addValidator('Count', false, array('min' => 1, 'max' => 1))
                    ->addValidator('Size', false, array('max' => $maxSize))
                    ->addValidator('Extension', false, array('extension' => $extension, 'case' => $forceCase));

            $adapter->setDestination($destination);

            $files = $adapter->getFileInfo();
            if (count($files) > 1)
            {
                $message = $this->_translate->translate("maximum one file expected");
                Kms_Log::log("admin: file upload error  - " . $message, Kms_Log::WARN);
                throw new Exception($message);
            }
            $file = $files[$postFieldId];
            if ($adapter->isUploaded($file['name']) && $adapter->isValid($file['name']))
            {
                $a = $adapter->receive($file['name']);
                return $file['destination'] . DIRECTORY_SEPARATOR . $file['name'];
            }
            return false;
        }
        catch (Exception $ex)
        {
            Kms_Log::log('admin: failed to read uploaded file', Kms_Log::WARN);
            throw $ex;
        }
    }
    
    public function deployAction()
    {
        $type = $this->getRequest()->getParam('type');
        $partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $serviceUrl = Kms_Resource_Config::getConfiguration('client', 'serviceUrl');
        
        switch($type)
        {
            case 'customdata':
                Kms_Setup_Deployment::init($partnerId, $serviceUrl, Kms_Setup_Deployment::DEPLOYMENT_MODE_INSTALL);
                $configObj = Kms_Setup_Deployment::getConfigObject();
                if(isset($configObj->metadataProfiles))
                {
                    $this->view->createdProfiles = Kms_Setup_Deployment::deployMetadataProfiles($configObj->metadataProfiles);
                    if(count($this->view->createdProfiles))
                    {
                        Kms_Setup_Deployment::populateConfigWithDeployedIDs();
                    }
                }
                break;
            case 'uiconfs':
                $this->view->createdUiConfs = array();
                Kms_Setup_Deployment::init($partnerId, $serviceUrl, Kms_Setup_Deployment::DEPLOYMENT_MODE_INSTALL);
                $configObj = Kms_Setup_Deployment::getConfigObject();
                foreach ($configObj as $sectionName => $sectionValue)
                {
                    //If we are in the widgets section (like kmc, kcw, kse)
                    if ($sectionName != "general") {
                    	if (count($sectionValue->widgets))
                    	{
	                        $uiconfsArr = Kms_Setup_Deployment::deployWidgets($sectionValue);
	                        foreach($uiconfsArr as $uiConfObj)
	                        {
	                            $this->view->createdUiConfs[] = $uiConfObj;
	                        }
                    	}
	                    else if ($sectionName == 'conversionProfiles' && count($sectionValue->profiles)) {
	                    	// verify conversion profiles exist so we can use their ids as dependencies 
	                    	Kms_Setup_Deployment::deployConversionProfiles($sectionValue);
	                    }
                    }
                }
                if(count($this->view->createdUiConfs))
                {
                    Kms_Setup_Deployment::populateConfigWithDeployedIDs();
                }
            break;
            default:
                break;
        }
        
    }
    
    public function enableDebugAction()
    {
        Kms_Resource_Debug::setCookie();
    }

}