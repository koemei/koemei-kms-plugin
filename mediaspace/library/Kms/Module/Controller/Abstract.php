<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of Abstract
 *
 * @author leon
 */
abstract class Kms_Module_Controller_Abstract extends Zend_Controller_Action
{
    private $_dispatcher = array('controller' => NULL, 'module' => NULL, 'action' => NULL);

    
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        if (Kms_Plugin_Theme::getThemeFullPath())    
        {
            $this->view->addBasePath(Kms_Plugin_Theme::getThemeFullPath() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->_request->getModuleName() . DIRECTORY_SEPARATOR . 'views');
        }
        
        $this->modulename = $request->getModuleName();
        
        // check if this module exists and is enabled - to catch cached urls of disabled modules
        if (!Kms_Resource_Config::shouldLoadModule($this->modulename))
        {
            throw new Zend_Exception('404');
        }
        
        // check if we need to check for contextual access
        if($this->modulename != 'default' && Kms_Resource_Config::moduleImplements($this->modulename, 'Kms_Interface_Contextual_Role'))
        {
            $modelName = Kms_Resource_Config::getModelName($this->modulename);
            $model = new $modelName();

            $action = $request->getActionName();
            $contextualRule = $model->getContextualAccessRuleForAction($action);
            if($contextualRule)
            {
            	foreach ($contextualRule->getRequestParams() as $requestParam) {
	               	$context[$requestParam] = $request->getParam($requestParam);
            	}
                
                if(!$contextualRule->checkAllowed($context))
                {
                    if($contextualRule->getAllowRedirect())
                    {
                        $deniedPage = $contextualRule->getAccessDeniedPage();
                        $deniedAction = isset($deniedPage['action']) ? $deniedPage['action'] : false;
                        $deniedController = isset($deniedPage['controller']) ? $deniedPage['controller'] : $request->getControllerName();
                        $deniedModule = isset($deniedPage['module']) ? $deniedPage['module'] : $request->getModuleName();

                        if($deniedAction)
                        {
                            $this->_forward($deniedAction, $deniedController, $deniedModule);
                        }
                    }
                    else
                    {
                        $this->_helper->viewRenderer->setNoRender();
                        Kms_Log::log('accessPlugin: Access denied to '.$request->getModuleName().':'.$request->getControllerName().' action '.$request->getActionName().' because of contextual access');
                        return;
    //                    ->setHeader('Content-Type', 'text/json');
                    }
                }
            }
        }
        
    }

    /**
     * basic action for modules to deliver their own static assets (images) without requiring theme
     * this is not yet reported in getAccessRules() so module that wishes to use it must allow it first.
     * module will also have to implement the relevant view that should look like:
     *
     * if(file_exists($imgPath)) // where image path is determined by view
     *      echo file_get_contents($imgPath);
     */
    public function getAssetAction()
    {
        $this->view->img = $this->getRequest()->getParam('id');
        $layout = Zend_Layout::getMvcInstance();
        $layout->disableLayout();

        $type = pathinfo($this->view->img, PATHINFO_EXTENSION);
        header("content-type: image/".$type);

    }
    
}
?>
