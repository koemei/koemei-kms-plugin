<?php
/**
 * Koemei module.
 * 
 * @author Tra!an
 *
 */
 


class Koemei_Model_Koemei extends Kms_Module_BaseModel implements Kms_Interface_Deployable_PreDeployment,Kms_Interface_Model_Dependency {
	const MODULE_NAME = 'Koemei';
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
        return false;
    }
	
	 public function getPreDeploymentFailReason()
    {
        return '<br><br>Deployment should be allowed. You shouldn\'t ever see this message<br>';
    }
	
	public static function getModuleDependency() {
		return array('captions');
	}


}
?>