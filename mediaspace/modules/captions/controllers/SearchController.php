<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

include_once ("IndexController.php");

/**
 * Captions module search controller.
 *
 * @author talbone
 *
 */
class Captions_SearchController extends Captions_IndexController
{
	
	public function init()
    {    
    	$this->_translate = Zend_Registry::get('Zend_Translate');

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
    
        $contextSwitch->setSuffix('ajax', 'ajax');
    
        $contextSwitch->addActionContext('entry', 'ajax')->initContext();
        $contextSwitch->addActionContext('global', 'ajax')->initContext();
        $contextSwitch->addActionContext('category', 'ajax')->initContext();
        $contextSwitch->addActionContext('my-media', 'ajax')->initContext();

        $this->_helper->contextSwitch()->initContext();    
    }

    /**
     * the captions gallery searches filter bar
     */
    public function filterBarAction()
    {
        // get the parameters
        $params = $this->getRequest()->getParams();

        // set the defaults
        if (empty($params['sort'])) {
            $params['sort'] = 'recent';
        }

        // set the url params in favor of links and search action
        $urlParams = array(
                        'module' => $this->getRequest()->getModuleName(), 
                        'controller' => $this->getRequest()->getControllerName(),
                        );

        switch ($params['dispatcher']['action']) {
            case 'global':
            case 'view':            
                $urlParams['action'] = 'category';
                break;
            case 'my-media':
                $urlParams['action'] = 'my-media';
                break;
            default:
                $urlParams['action'] = 'entry';
                break;
        }

        // reset view hook additional params
        if (!empty($params['dispatcher'])) {
            $params['dispatcher'] = null;
        }

        $this->view->params = $params;
        $this->view->urlParams = $urlParams;
    }

    /**
     * entry search action - search a single entry for captions
     */
    public function entryAction()
    {        
        // get the parameters
        $params = $this->getRequest()->getParams();

        // check for captions search params and set them on the view
        $this->view->inTime = !empty($params['inTime']) ? $params['inTime'] : 0;
        $this->view->outTime = !empty($params['outTime']) ? $params['outTime'] : 0;
        $this->view->label = !empty($params['label']) ? $params['label'] : '';
        $this->view->language = !empty($params['lang']) ? $params['lang'] : '';

        // get the model
        $model = new Captions_Model_Captions();
                
        // get the entry            
        $entryId = $this->getEntry($model, $this->getRequest()->getParam('entryId'));
        $this->view->entryId = $entryId;

        // get the entry captions languages
        $this->view->languages = $model->getEntryLanguages($entryId);

        if ($this->getRequest()->isPost() || $this->getRequest()->getParam('format') == 'ajax')    
        {
            // set the page size
            $params['pagesize'] = Kms_Resource_Config::getModuleConfig('captions', 'pageSize');
            
            // search the entry            
            $this->view->captions = $model->search($params);
            $this->view->keyword = $this->getRequest()->getParam('keyword');   

            // set the pager
            $totalCount = isset($this->view->captions->totalCount) ?  $this->view->captions->totalCount : 0;             
            $this->view->urlParams = array('keyword' => $params['keyword'], 'lang' => $this->view->language); 
            $this->view->paginator = $this->getPaginator($params, $totalCount);
            $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
        }
    }
    
    /**
     * global search action
     */
   	public function globalAction()
   	{		
		// get the parameters
        $params = $this->getRequest()->getParams();

		// set the page size
        $params['pagesize'] = Kms_Resource_Config::getModuleConfig('captions', 'pageSize');

        // set the base kms categories
        $params['categoryName'] = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));

		// get the model
        $model = new Captions_Model_Captions();

 		// search for captions           
        $this->view->captions = $model->search($params);
        $this->view->keyword = $this->getRequest()->getParam('keyword');   
        
	    // set the first entry in the player
	    if (!empty($this->view->captions->objects)) {
        	$caption = reset($this->view->captions->objects);
        	$this->view->entry = $caption['entry'];

        	try
            {
            	$entryModel = Kms_Resource_Models::getEntry();
                $entryModel->setEntry($this->view->entry);
            }
            catch(Kaltura_Client_Exception $e)
            {
                Kms_Log::log('captions: attempted to fetch an entry (first entry on page) which does not exist, or user is not entitled to - '.$e->getCode().': '.$e->getMessage());
            }
		}

        // set the active link indication
        $this->view->activeLink = $this->getRequest()->getModuleName();

        // set the url params for the default filter bar
        $this->view->urlParams = array(
                                'module' => 'default',
                                'controller' => 'search',
                                'action' => $params['keyword'],
                                );

	    // set the pager
        $totalCount = isset($this->view->captions->totalCount) ?  $this->view->captions->totalCount : 0;         
        $this->view->pagerParams = array('action' => 'category'); // change the action to the category search action
        $this->view->paginator = $this->getPaginator($params, $totalCount);
        $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
   	}

   	/**
   	 * category(gallery/channel) search action
   	 */
   	public function categoryAction()
   	{
	    // get the parameters
        $params = $this->getRequest()->getParams();

		// set the page size
        $params['pagesize'] = Kms_Resource_Config::getModuleConfig('captions', 'pageSize');

        // set the category id
        if (isset($params['categoryid'])) {
            $params['categoryId'] = $params['categoryid'];
        }
        if (isset($params['channelid'])) {
            $params['categoryId'] = $params['channelid'];
        }

        // is this actually a global search in disguise?
        if (empty($params['categoryId'])){
            // set the base kms categories
            $params['categoryName'] = join(',', array(Kms_Resource_Config::getRootGalleriesCategory(), Kms_Resource_Config::getRootChannelsCategory()));
        } 
        
		// get the model
        $model = new Captions_Model_Captions();

 		// search for captions           
        $this->view->captions = $model->search($params);
        $this->view->keyword = $this->getRequest()->getParam('keyword');   
        $this->view->language = !empty($params['lang']) ? $params['lang'] : '';

        // set the pager
        $totalCount = isset($this->view->captions->totalCount) ?  $this->view->captions->totalCount : 0;         
        $this->view->urlParams = array('keyword' => $params['keyword'], 'lang' => $this->view->language);  
        $this->view->paginator = $this->getPaginator($params, $totalCount);
        $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
   	}

   	/**
   	 * my media search action
   	 */
   	public function myMediaAction()
   	{
        // get the parameters
        $params = $this->getRequest()->getParams();

        // set the page size
        $params['pagesize'] = Kms_Resource_Config::getModuleConfig('captions', 'pageSize');

        // set the user id
        $params['userId'] = Kms_Plugin_Access::getId();

        // get the model
        $model = new Captions_Model_Captions();

        // search for captions           
        $this->view->captions = $model->search($params);
        $this->view->keyword = $this->getRequest()->getParam('keyword');   

        $totalCount = isset($this->view->captions->totalCount) ?  $this->view->captions->totalCount : 0; 

        // set the pager
        $totalCount = isset($this->view->captions->totalCount) ?  $this->view->captions->totalCount : 0;         
        $this->view->urlParams = array('keyword' => $params['keyword']); 
        $this->view->paginator = $this->getPaginator($params, $totalCount);
        $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
   	}
}