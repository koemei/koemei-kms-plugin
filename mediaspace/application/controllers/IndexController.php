<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize contexts here */
    }

    public function indexAction()
    {
        $nav = Kms_Resource_Nav::getContainer();
        if (count($nav))
        {
            $page = $nav->current();
            if ($page instanceof Zend_Navigation_Page_Mvc)
            {
                $page->setActive(true);
                $action = $page->getAction();
                $controller = $page->getController();
                $module = $page->getModule();
                $params = $page->getParams();
                $route = $page->getRoute();
                
                if($this->getRequest()->getParam('redirected'))
                {
                    if($route)
                    {
                        $this->_helper->redirector->gotoRoute($params, $route);
                    }
                    else
                    {
                        $this->_helper->redirector->gotoSimple($action, $controller, $module, $params);
                    }
                }
                else
                {
                    $this->_forward($action, $controller, $module, $params);
                    return;
                }
            }
            elseif ($page instanceof Zend_Navigation_Page_Uri)
            {
                $uri = $page->getUri();
                $req = new Zend_Controller_Request_Http();
                $req->setRequestUri($uri);
                $router = Zend_Controller_Front::getInstance()->getRouter();

                //$req->setParam('requestUri', $href);
                $route = $router->route($req);
                $module = $route->getModuleName();
                $controller = $route->getControllerName();
                $action = $route->getActionName();
                $params = $route->getParams();

                if (Zend_Controller_Front::getInstance()->getDispatcher()->isDispatchable($req))
                {
                    $page->setActive(true);
                    
                    // check if this is a category
                    if($action == 'view' && $controller == 'gallery' && isset($params['categoryname']))
                    {
                        // since we didn't build a sub navigation menu for this item (because probably it's a link item and not an mvc page)
                        // then we will do it now (and only for the case where the index/index action is called directly (homepage)
                        // so, if the homepage is a category page, then we must calculate the subcategories too
                        $categoryname = Kms_Resource_Config::getRootGalleriesCategory() . '>' .$params['categoryname'];
                        // build subnav for this item
                        $pages = Kms_Resource_Nav::buildCategoryTree(null, $categoryname, true);
                        // add the sub categories to the current page
                        $nav->current()->addPages($pages);
                        
                    }
                    
                    $this->_forward($action, $controller, $module, $params);
                    return;

                }
                else
                {
                    Kms_Log::log('index: ' . $uri . ' is not a valid mediaspace URL, skipping to next navigation item', Kms_Log::DEBUG);
                    $nav->next();
                    Kms_Resource_Nav::setContainer($nav);
                    //$router->removeRoute('default');
                    //$router->addDefaultRoutes();
                    $this->_forward('index', 'index', null, array('redirected' => true));
                    return;
                }
            }
        }
        else
        {
            $this->_forward('my-media', 'user');
            return;
        }
    }

    public function versionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->view->version = Kms_Resource_Config::getVersion();
    }
}