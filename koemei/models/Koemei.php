<?php
/**
 * Koemei module.
 * Copyright ©2013 Koemei SA
 * @author Tra!an
 *
 */
 


class Koemei_Model_Koemei extends Kms_Module_BaseModel implements Kms_Interface_Deployable_PreDeployment {
	const MODULE_NAME = 'Koemei';
	
	private $canEnable;
    public $viewHooks = array
    (

            Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array(
                    'action' => 'entrytab',
                    'controller' => 'index',
                    'order' => 100
            ),
			Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array(
                    'action' => 'entry',
                    'controller' => 'index',
                    'order' => 40
            ),
			Kms_Resource_Viewhook::CORE_VIEW_HOOK_EDIT_ENTRY_TABS => array(
                    'action' => 'edit',
                    'controller' => 'index',
                    'order' => 40
            ),
			Kms_Resource_Viewhook::CORE_VIEW_HOOK_MODULES_HEADER => array( 
                    'action' => 'header',
                    'controller' => 'index', 
                    'order' => 20
            )
    );
	


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
                        'actions' => array('entrytab','entry','edit'),
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
	public static function settingsSaved() {
		$k_id= $_POST['koemeiUuid'];
		$content = Koemei_Model_Koemei::get_data('https://www.koemei.com/REST/users/'.$k_id);
		$xml = simplexml_load_string($content);
		
		
		//show message if UUID not found.
		if (!isset($xml->Id)) {
			echo "The Koemei UUID you've specified dose not exist.";	
			exit;
		}
		
		//alow public customisation
		if ($_POST['AlowCustomization']==1) {
			$content = Koemei_Model_Koemei::get_data('https://www.koemei.com/REST/users/'.$k_id.'/?default_access_level={allow_everyone}');
		}
		
	}
    public static function getModuleDependency()
    {
        return array('captions');
    }
	

}
?>