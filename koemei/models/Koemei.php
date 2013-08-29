<?php
/**
 * Koemei module.
 * Copyright ©2013 Koemei SA
 * @author Tra!an
 *
 */
 


class Koemei_Model_Koemei extends Kms_Module_BaseModel implements Kms_Interface_Deployable_PreDeployment,
                                                                  Kms_Interface_Functional_Entry_TabType,
                                                                  Kms_Interface_Functional_Entry_Tabs,
																  Kms_Interface_Functional_Entry_Edit_Tabs,
																  Kms_Interface_Model_Dependency

{
	const MODULE_NAME = 'koemei';
	
	private $canEnable;
    public $viewHooks = array
    (
			Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array(
                    'action' => 'header',
                    'controller' => 'index', 
                    'order' => 20
            )
    );
	
    /**
     * Adding a new interface function for marking this tab as available for external entry
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_TabType::isHandlingTabType()
     */
    public function isHandlingTabType(Kaltura_Client_Type_BaseEntry $entry)
    {
		$CaptionModel = new Captions_Model_Captions();
		$entry = $CaptionModel->getEntry();
		if (isset($entry->id) && $entry->mediaType==1) {
			return true;
		} else {
			return false;	
		}
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_Tabs::getEntryTabs()
     */
    public function getEntryTabs(Kaltura_Client_Type_BaseEntry $entry, Zend_Controller_Request_Abstract $request)
    {
        $translator = Zend_Registry::get('Zend_Translate');

        $link = new Kms_Type_Link_Mvc(Koemei_Model_Koemei::MODULE_NAME,'index','index');
        //$tab = new Kms_Type_Tab_Async($link, $translator->translate('Transcript'), '#transcript-tab', array(), '', 0);
        $tab = new Kms_Type_Tab_Sync($link, $translator->translate('Transcript'), '#transcript-tab', array(), '', 0);

        return array($tab);
    }

    public function getEntryEditTabs(Kaltura_Client_Type_BaseEntry $entry)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $link = new Kms_Type_Link_Mvc('koemei','index','edit',array('entryid' => $entry->id));
        $tab = new Kms_Type_Tab_Sync($link, $translator->translate('Transcript'), '#koemei-tab');
        return array($tab);
    }


    public function getAccessRules()
    {
        $accessrules = array(
				array(
                        'controller' => 'koemei:index',
                        'actions' => array('header'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                ),
                array(
                        'controller' => 'koemei:index',
                        'actions' => array('index','edit'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                )
        ); 
        return $accessrules;
    }

	
	 public function canInstall()
    {
        return true;
    }
	
	
	//check if the module can be enabled. only if Koemei UUID is set
	public function canEnable()
    {
		$uuid = Kms_Resource_Config::getModuleConfig('koemei', 'koemeiUuid');
		if (!$uuid) {
			$this->canEnable = false;	
		} else {
			$this->canEnable = true;		
		}
		return $this->canEnable;	
    }
	
		
	 public function getPreDeploymentFailReason()
    {
        return "<br><br>Please specify your Koemei UUID before enabling the Module.<br><br>If you don't have an account please go to <a href=\"https://www.koemei.com/billing\" target='_blank'>Koemei website</a> and create your account<br>";
    }
	
	//function for CURL requests
	public static function get_data($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	//save custom settings on koemei servers/check if user id exists
	public static function settingsSaved($param) {
		$content = Koemei_Model_Koemei::get_data('https://www.koemei.com/REST/users/'.$param);
		$xml = simplexml_load_string($content);
		//show message if UUID not found.
		if (!isset($xml->Id)) {
			return "The Koemei UUID you've specified does not exist.";
			exit;
		}
		return 'Account linked';
		
		
	}
	
	//alow public customisation
	public static function enableImprove($param) {
		if ($param==1) {
			$content = Koemei_Model_Koemei::get_data('https://www.koemei.com/REST/users/'.$k_id.'/?default_access_level={allow_everyone}');
			return 'Open captioning activated';
		} else {
			return 'Open captioning disabled';	
		}
	}
	
	
    public static function getModuleDependency()
    {
        return array('captions');
    }
	

}
?>