<?php
/**
 * Koemei module.
 * 
 * @author Tra!an
 *
 */
 


class Koemei_Model_Koemei extends Kms_Module_BaseModel {
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
	

}
?>