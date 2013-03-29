<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class InstallController extends Zend_Controller_Action
{
    private $_flashMessenger = null;
    private $_translate = null;
    private $_steps = NULL;

    private $_uploadedFileValid = true;
    
    public function init()
    {
        /* init translator */
        set_time_limit(0);
        $this->_translate = Zend_Registry::get('Zend_Translate');        

        if(!defined("KMS_INSTALL_CONTROLLER_ALLOWED"))
        {
            $message = $this->_translate->translate("This instance does not require installation at this time");
            Kms_Log::log('install: '.$message, Kms_Log::WARN);
            throw new Exception($message);
        }
	
	$this->_steps = array(
	    'requirements' => $this->_translate->translate('Requirements check'),
	    'install' => array($this->_translate->translate('Installation'), $this->_translate->translate('Done')),
	    'migrate' => array($this->_translate->translate('Migration'), $this->_translate->translate('Done'))
	);
	
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_flashMessenger->setNamespace('default');
        $this->view->messages = $this->_flashMessenger->getMessages();

        /* Initialize action controller here */
        $this->_helper->layout->setLayout('setup');
        
        Kms_Resource_Config::setConfiguration('debug', 'logLevel', Kms_Log::DEBUG);
        Kms_Log::setDebugLevel();
        
        // tempprary enable the api debug log for the install process
        Kms_Resource_Config::setConfiguration('debug', 'kalturaDebug', 1);
    }

    public function indexAction()
    {
        $reqs = new Kms_Setup_Requirements();
        $this->view->list = $reqs->getList();
        $this->view->status = $reqs->getStatus();
	    $this->view->pagehead = $this->_steps['requirements'];
        $this->view->showNext = $this->view->status;
    }
    
    private function handleFileUpload($version)
    {
        if($version == Kms_Setup_Common::KNOWN_VERSION_2)
        {
            $requiredExtension = 'php';
        }
        elseif($version == Kms_Setup_Common::KNOWN_VERSION_30x || $version == Kms_Setup_Common::KNOWN_VERSION_40x)
        {
            $requiredExtension = 'ini';
        }
    	try
        {
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->addValidator('Count',false, array('min'=>1, 'max'=>1))
                ->addValidator('Size',false,array('max' => 10000))
                ->addValidator('Extension',false,array('extension' => $requiredExtension,'case' => true));
        
            $adapter->setDestination(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'backups');
            
            $files = $adapter->getFileInfo();
            if(count($files) > 1)
            {
                $message = $this->_translate->translate("maximum one file expected");
                Kms_Log::log("install: Config Migrate: file upload error  - ".$message, Kms_Log::WARN);
            	throw new Exception($message);
            }
            $file = $files['configFile'];
            if($adapter->isUploaded($file['name']) && $adapter->isValid($file['name']))
            {
                $a = $adapter->receive($file['name']);
                return $file['destination'] . DIRECTORY_SEPARATOR . $file['name'];
            }
            if($adapter->isUploaded($file['name']) && !$adapter->isValid($file['name']))
            {
                $this->_uploadedFileValid = false;
            }
            elseif(!$adapter->isUploaded($file['name']))
            {
                $this->_uploadedFileValid = false;
            }
            return false;
        }
        catch (Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function migrateAction()
    {
        // check writable configs first:
        try
        {
            $upgrade = Kms_Setup_Upgrade::checkWritePermissionsForMigration();
        }
        catch(Exception $ex)
        {
            $this->view->filepaths = Kms_Setup_Upgrade::getErrorPaths(); 
            $this->_helper->viewRenderer('migrate-permissions');
            return true;
        }
        
        $form = new Application_Form_Migrate();
        $request = $this->getRequest();
        $readable = false;

        if($request->isPost() && $form->isValid($request->getPost()))
        {
            $originVersion = $request->getParam('migrateFromVersion');
            $configPath = $request->getParam('configPath');
            $instanceId = $request->getParam('instanceId') ? $request->getParam('instanceId') : '';
            $forceInstanceCheckbox = $request->getParam('forceUseInstanceId')? $request->getParam('forceUseInstanceId'): false;
            $privacyContext = $request->getParam('privacyContext')? $request->getParam('privacyContext'): '';

            $uploadedFilePath = $this->handleFileUpload($originVersion);
            try
            {
                if($uploadedFilePath !== false)
                {                    
                    $this->doMigration($uploadedFilePath, $originVersion, $instanceId, $forceInstanceCheckbox, $privacyContext);
                }
                elseif(is_readable($configPath) && is_file($configPath))
                {                    
                    $this->doMigration($configPath, $originVersion, $instanceId, $forceInstanceCheckbox, $privacyContext);
                }
                elseif($configPath && !is_readable($configPath))
                {                    
                    $form->getElement('configPath')->addError($this->_translate->translate('Specified path is not readable'));
                }
                elseif ($configPath && !is_file($configPath))
                {                    
                    $form->getElement('configPath')->addError($this->_translate->translate('No File at Specified path'));
                }
                else
                {                    
                    // no uploaded file and not readable path (or empty path)
                    if(!$this->_uploadedFileValid)
                    {
                        $form->getElement('configFile')->addError($this->_translate->translate('Uploaded file has invalid extension or no file was uploaded.'));
                    }
                }
            }
            catch(Exception $e)
            {
                $this->tearDown();
                $this->view->migrationError = $e->getMessage();
            }
        }
        
        if(!$request->isPost() || !$readable)
        {
            $this->view->badPost = $request->isPost();
            $form->populate($request->getParams());
            $form->render();
            $this->view->form = $form;
	        $this->view->pagehead = $this->_steps['migrate'][0];
            $this->view->steps = $this->_steps['migrate'];
        }

    }

    private function tearDown()
    {
        $configFile = Kms_Resource_Config::getConfigFileName();
        if($configFile)
        {
            @unlink($configFile);
        }
    }
    
    private function doMigration($filePath, $version, $instanceId, $forceInstanceCheckbox, $privacyContext)
    {
        $upgrade = Kms_Setup_Common::factory($version, $filePath);
        $tmpClient = $upgrade->getTempClient();
        if(Kms_Setup_Common::validatePartnerInfo($tmpClient))
        {
            if(!Kms_Setup_InstanceIdMgr::validateInstanceAvailable($instanceId, $tmpClient) && !$forceInstanceCheckbox)
            {
                Kms_Log::log(Kms_Setup_Common::UPGRADE_EXCEPTION_DUPLICATE_INSTANCE_ID .' with  '.$instanceId, Kms_Log::CRIT);
                throw new Kms_Setup_Exception(Kms_Setup_Common::UPGRADE_EXCEPTION_DUPLICATE_INSTANCE_ID);
            }

            $additionalSettings = array(
                'application' => array(
                    'privacyContext' => $privacyContext,
                    'instanceId' => $instanceId,
                )
            );
            $upgrade->upgrade($additionalSettings);
            $deployParams = array('mode' => Kms_Setup_Deployment::DEPLOYMENT_MODE_MIGRATE, 'origVer' => $version);
            $this->_forward('deploy', 'install', null, $deployParams);

        }
        return;
    }
    
    public function installAction()
    {
        $form = new Application_Form_Install();
        $request = $this->getRequest();
        $displayForm = true;
        if($request->isPost())
        {
            if($form->isValid($request->getPost()))
            {
                $auth = Zend_Auth::getInstance();
                $storage = new Zend_Auth_Storage_Session( Kms_Plugin_Access::STORAGE_ADMIN );
                $auth->setStorage($storage);
                $login = $request->getParam('Login');
                $username = $login['username'];
                $password =  $login['password'];
                $serviceUrl =  $login['host'];
                $partnerId =  $login['partnerId'];
                $instanceId = $login['instanceId'];
                $forceInstanceCheckbox = isset($login['forceUseInstanceId'])? $login['forceUseInstanceId']: false;
                $privacyContext = isset($login['privacyContext'])? $login['privacyContext']: '';

                $adapter = new Kms_Auth_Admin($username, $password, $serviceUrl, $partnerId, true);
                
                try
                {
                    Kms_Resource_Client::setConfigValue('partnerId', $partnerId);
                    // TODO - do we want to authenticate through $auth?
                    // consider just $adapter->authenticate();             
                    $result = $adapter->authenticate();
                    $ks = $adapter->getLoginKs();
                    
                    $client = Kms_Resource_Client::getAdminClient();
                    $client->setKs($ks);
                    
                    $partnerInfo = $client->partner->getInfo();
                    if(!Kms_Setup_InstanceIdMgr::validateInstanceAvailable($instanceId, $client) && !$forceInstanceCheckbox)
                    {
                        $form->getElement('instanceId')->addError($this->_translate->translate('The instanceId provided is already in use. Check the "force use instanceId" or provide another ID'));
                        //$form->addForceInstanceCheckbox();
                    }
                    else
                    {
                        Kms_Setup_Install::initEssentialConfig($partnerInfo, $serviceUrl, $instanceId, $privacyContext);
                        $displayForm = false;
                    
                        $deployParams = array('mode' => Kms_Setup_Deployment::DEPLOYMENT_MODE_INSTALL);
                        $this->_forward('deploy', 'install', null, $deployParams);
                        return;
                    }
                }
                catch(Exception $ex)
                {
                    $this->tearDown();
                    $this->view->error = $ex->getMessage();
                }
            }
            else
            {
                //$this->view->error = $this->_translate->translate('form validation failed');
            }
        	
        }
        
        if($displayForm)
        {
            $form->render();
            $this->view->form = $form;  
	        $this->view->pagehead = $this->_steps['install'][0];
	        $this->view->steps = $this->_steps['install'];
        }
    }
    
    public function deployAction()
    {
        $request = $this->getRequest();
        $mode = $request->getParam('mode');
        $origVersion = $request->getParam('origVer', null);
        $partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $serviceUrl = Kms_Resource_Config::getConfiguration('client', 'serviceUrl');
    	// do deployment

        try
        {
            // init the class for deployment
            Kms_Setup_Deployment::init($partnerId, $serviceUrl, $mode, true, $origVersion);
            //deploy all the ui confs
            Kms_Setup_Deployment::deploy();
            Kms_Setup_Deployment::populateConfigWithDeployedIDs();
            Kms_Setup_Deployment::postDeployment();
        }
        catch(Exception $ex)
        {
            Kms_Log::log("Install: Deployment Failed. Error [{$ex->getMessage()}] ", Kms_Log::CRIT);
            $this->tearDown();
            $this->view->deploymentErrors = Kms_Setup_Deployment::getDeploymentErrors();
            $this->view->steps = $this->_steps['install'];
            $this->view->pagehead = $this->_translate->translate('Installation Error');
            $this->_helper->viewRenderer('deploy-error');
            return;
        }
        
        
        // set view details
        $this->view->deploymentErrors = Kms_Setup_Deployment::getDeploymentErrors();
	    $this->view->steps = $this->_steps['install'];
	    $this->view->pagehead = $this->_steps['install'][1];
        $this->_helper->viewRenderer('done');
    }
    
    
    public function doneAction()
    {
        $this->view->deploymentErrors = Kms_Setup_Deployment::getDeploymentErrors();
        $this->view->deploymentErrors = array("TESTING DEPLOYMENT Error Here lalala");
        $this->view->steps = $this->_steps['migrate'];
	    $this->view->pagehead = $this->_steps['migrate'][1];
    }
    
    public function checkRewriteAction()
    {
    	die(Kms_Setup_Requirements::MOD_REWRITE_EXPECTED_TEXT);
    }

}

