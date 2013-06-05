<?php
/**
 * Koemei module.
 * 
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
                        'actions' => array('entrytab','entry'),
                        'role' => Kms_Plugin_Access::ANON_ROLE,
                )
               
        ); 
        return $accessrules;
    }
	
	public function GetOne($media_id) {
			
		
		
	}
	
	 public function canInstall()
    {
        return true;
    }
	
	public function canEnable()
    {
		$uuid = Kms_Resource_Config::getModuleConfig('koemei', 'Koemei_uuid');
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
	
	
	public static function settingsSaved() {
		$k_id= $_POST['Koemei_uuid'];
		$content = Koemei_Model_Koemei::get_data('https://www.koemei.com/REST/users/'.$k_id);
		$xml = simplexml_load_string($content);
		if (!isset($xml->Id)) {
			echo "The Koemei UUID you've specified dose not exist.";	
			exit;
		}
		
	}

}
?>