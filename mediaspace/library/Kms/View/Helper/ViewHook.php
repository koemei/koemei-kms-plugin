<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * View helper to handle in-view hooks to load content from other modules
 */

/**
 * Description of ViewHook
 *
 * @author leon
 */
class Kms_View_Helper_ViewHook extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    /**
     *  viewHook view helper. renders modules view hook.
     *
     *  @param string $hook - the view hook to call.
     *  @param string $pre - markup to render before each view hook call. can contain the placeholders:  [module] [controller] [action].
     *  @param string $post - markup to render after each view hook call. can contain the placeholders:  [module] [controller] [action].
     *  @param string $interface - (optional) an interface that the module registered for the view hook needs to implement.
     *  @param string $method - (optional) a method of said interface, returning bool, being called to determine if the view hook will be rendered.
     *  @param array $params - (optinal) the params of said method. 
     */
    public function ViewHook($hook, $pre = null , $post = null, $interface = null, $method = null, array $params = array())
    {
        $modules = Kms_Resource_Config::getModulesForViewHook($hook);
        $front = Zend_Controller_Front::getInstance();
        $dispatcher = array(
            'controller' => $front->getRequest()->getControllerName(),
            'action' => $front->getRequest()->getActionName(),
            'module' => $front->getRequest()->getModuleName()
        );

        $out = '<!-- viewhook start '.$hook.' -->'.PHP_EOL;
        Kms_Log::log('viewhook: Parsing hook '.$hook, Kms_Log::DEBUG);
        uasort($modules, array('Kms_View_Helper_ViewHook', 'OrderSort'));
        
        // check the interface dependency
        $modules = $this->filterModulesByInterface($modules, $interface, $method, $params);
        
        if (!count($modules)) {
        	// return an empty string, so we can use it to indicate no mudule handled this viewhook
        	$out = '';
        	return $out;
        }

        $access = $front->getPlugin('Kms_Plugin_Access');
        
        foreach($modules as $module => $options)
        {
            Kms_Log::log('viewhook: found a registered module -  '.$module.' / '.$options['controller'] .' / ' . $options['action'], Kms_Log::DEBUG);
           
            // check if role has the permissions
            if($access->hasPermission($module.':'.$options['controller'], $options['action']))
            {
                Kms_Log::log('viewhook: for module '.$module.' - access ok. rendering', Kms_Log::DEBUG);
                    
                $requestParams = array_merge(array('dispatcher' => $dispatcher),$front->getRequest()->getParams());
                if (!empty($requestParams['format'])) {
                    $requestParams['format'] = '';        
                }
                
                // replace the pre markup placeholders
                if (!empty($pre)) {
                    $pre = str_replace('[module]', $module, $pre);
                    $pre = str_replace('[controller]', $options['controller'], $pre);
                    $pre = str_replace('[action]', $options['action'], $pre);

                    $out .= $pre;
                }

                $out .= $this->view->action($options['action'], $options['controller'], $module, $requestParams);                

                // replace the post markup placeholders
                if (!empty($post)) {
                    $post = str_replace('[module]', $module, $post);
                    $post = str_replace('[controller]', $options['controller'], $post);
                    $post = str_replace('[action]', $options['action'], $post);

                    $out .= $post;
                }
            }
            else
            {
                Kms_Log::log('viewhook: denied access to'.$module.' / '.$options['controller'] .' / ' . $options['action'], Kms_Log::DEBUG);
            }     
        }
        $out .= PHP_EOL.'<!-- viewhook end '.$hook.' -->';
        return $out;
    }
    
    /**
     *  filters the modules by interface dependency.
     *
     *  @param array $modules - the modules to filter.
     *  @param string $interface - (optional) an interface that the module registered for the view hook needs to implement.
     *  @param string $method - (optional) a method of said interface, returning bool, being called to determine if the view hook will be rendered.
     *  @param array $params - (optinal) the params of said method. 
     */
    private function filterModulesByInterface(array $modules, $interface = null, $method = null, array $params = array())
    {
    	$filteredModules = $modules;
        
        // check the interface dependency
        if(!empty($interface)){
            // we have an interface dependency 
            $filteredModules = array();

            foreach($modules as $module => $options)
            {
                Kms_Log::log('viewhook: checking interface ' . $interface . ' ' . $method .' for module -  '.$module, Kms_Log::DEBUG);

                $runViewHook = true;
                if (Kms_Resource_Config::moduleImplements($module, $interface)){    
                    // check for specific method dependency
                    if (!empty($method)) {
                        // we have an interface specific method dependency - call the method with the params
                        $model = Kms_Resource_Config::getModelObjectByName($module);                        
                        $runViewHook = call_user_func_array(array($model, $method), $params);
                    }

                    if ($runViewHook) {
                        Kms_Log::log('viewhook: interface ' . $interface . ' for module ' .$module.' - condition met.', Kms_Log::DEBUG);
                        $filteredModules[$module] = $options;
                    }
                    else{
                        Kms_Log::log('viewhook: interface method ' . $interface . ' ' .$method . ' for module ' .$module.' - condition not met.', Kms_Log::DEBUG);
                    }
                }
                else{
                        Kms_Log::log('viewhook: interface ' . $interface . ' for module ' .$module.' - condition not met.', Kms_Log::DEBUG);
                }
            }
        }

        return $filteredModules;
    }

    static function OrderSort($a, $b)
    {
        if(!isset($a['order']))
        {
            return 1;
        }
        
        if(!isset($b['order']))
        {
            return -1;
        }
        
        if ($a['order'] == $b['order']) 
        {
            return 0;
        }
        return ($a['order'] < $b['order']) ? -1 : 1;        
    }
}

