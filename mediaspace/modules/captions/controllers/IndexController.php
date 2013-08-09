<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Captions module controller.
 * 
 * @author talbone
 *
 */
class Captions_IndexController extends Kms_Module_Controller_Abstract
{
    protected $_translate = null;
        
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
    
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        if (!$contextSwitch->getContext('dialog'))
        {
            $dialogC = $contextSwitch->addContext('dialog', array());
            $dialogC->setAutoDisableLayout(false);
        }
    
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('dialog', 'dialog');
    
        $contextSwitch->addActionContext('edit', 'ajax')->initContext();
        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('delete', 'ajax')->initContext();
        $contextSwitch->addActionContext('setdefault', 'ajax')->initContext();
        $contextSwitch->addActionContext('upload', 'dialog')->initContext();
        $contextSwitch->addActionContext('upload', 'ajax')->initContext();
        $contextSwitch->addActionContext('change', 'ajax')->initContext();
        
        $this->_helper->contextSwitch()->initContext();    
    }
    
    /**
     *  add css and js to the page header
     */
    public function headerAction()
    {
        // mock action to create a view
    }

    /**
     * add an entry tab link - if the entry has captions.
     */
    public function entrytabAction()
    {
        // get the entry
        $model = new Captions_Model_Captions();
        $entry = $model->getEntry();

        // check if it has captions
        if (!$model->hasCaptions($entry->id))
        {
            // no captions - do not present the tab
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }
    
    /**
     * add an entry upload tab link
     */
    public function edittabAction()
    {
        // get the entry
        $model = new Captions_Model_Captions();
        $entry = $model->getEntry();
                
        // show captions tab only for video entries
        if ($entry->type != Kaltura_Client_Enum_EntryType::MEDIA_CLIP || $entry->mediaType != Kaltura_Client_Enum_MediaType::VIDEO)
        {
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }
    
    /**
     * add an entry captions edit page 
     */
    public function editAction()
    {
        // get the parameters
        $params = $this->getRequest()->getParams();
        
        // set the page size
        $params['pagesize'] = Captions_Model_Captions::CAPTION_ASSETS_PAGE_SIZE;
        
        // get the entry
        $model = new Captions_Model_Captions();
        $entryId = $this->getEntry($model);
    
        // get the caption assets
        $this->view->captions = $model->getCaptionAssets($entryId, $params);
                
        // url params - for the pager
        $this->view->urlParams = array(
                'module' => $this->getRequest()->getModuleName() ,
                'controller' => $this->getRequest()->getControllerName() ,
                'action' => $this->getRequest()->getActionName() ,
                'entryId' => $entryId,
        );
                
        // set the pager
        $this->view->paginator = $this->getPaginator($params, $this->view->captions->totalCount);
        $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
    }
    
    /**
     * delete an asset item
     */
    public function deleteAction()
    {
        $results = false;
        
        // if not ajax - show the dialog
        if ($this->getRequest()->getParam('format') == 'ajax')
        {
            // get the parameters
            $params = $this->getRequest()->getParams();
            $entryId = $this->getRequest()->getParam('entryId');
            $this->view->assetId = $this->getRequest()->getParam('assetId');
            
            // delete the caption asset
            $model = new Captions_Model_Captions();
            $results = $model->deleteCaptionAsset($entryId, $this->view->assetId);

            if ($results)
            {
                // set the page size
                $params['pagesize'] = Captions_Model_Captions::CAPTION_ASSETS_PAGE_SIZE;
                $pageindex = isset($params['page']) ? $params['page'] : 1;
                 
                // get the assets again
                $captions = $model->getCaptionAssets($entryId, $params);
                $totalCount = isset($captions->totalCount) ? $captions->totalCount : 0;                
               
                $this->view->captions = new Kaltura_Client_Caption_Type_CaptionAssetListResponse();
                $this->view->captions->totalCount = $totalCount;
                
                // just deleted the last asset in this page - go down one page
                if (empty($captions->objects) && $pageindex > 1)
                {
                    $this->getRequest()->setParam('page', --$pageindex);
                    $this->_forward('edit');
                    return;
                }
                
                // reload next member - if exists
                if (count($captions->objects) == $params['pagesize']){
                    $captions = array_values($captions->objects);
                    $caption = $captions[$params['pagesize'] -1];
                    $captions = array($caption->id => $caption);
                                         
                    $this->view->captions->objects = $captions;
                    $this->view->caption = $caption;
                }
                
                
                // url params - for the pager
                $this->view->urlParams = array(
                        'module' => $this->getRequest()->getModuleName() ,
                        'controller' => $this->getRequest()->getControllerName() ,
                        'action' => 'edit' ,
                        'entryId' => $entryId,
                );
                
                // set the pager
                $this->view->paginator = $this->getPaginator($params, $totalCount);
                $this->view->pagerType = Kms_Resource_Config::getModuleConfig('captions', 'pagerType');
            }
            else
            {
                $this->view->message = $this->_translate->translate('An error occurred while deleting this caption file.');
                // reload the edit page - we dont know if the entry was deleted or not
                $this->_forward('edit');
                return;
            }
        }
        
        $this->view->results = $results;
    }
    
    /**
     * set an asset as the default one
     */
    public function setdefaultAction()
    {
        // get the parameters
        $entryId = $this->getRequest()->getParam('entryId');
        $assetId = $this->getRequest()->getParam('assetId');
        
        // set the asset as default
        $model = new Captions_Model_Captions();
        $results = $model->setDefaultAsset($entryId, $assetId);
        
        if (!$results)
        {
            $this->view->message = $this->_translate->translate('There was an error setting this caption file as Default.');
        }
        
        // reload the edit page
        $this->_forward('edit');
        return;
    }
    
    /**
     * dowload a caption asset file 
     */
    public function downloadAction()
    {
        // get the parameters
        $entryId = $this->getRequest()->getParam('entryId');
        $assetId = $this->getRequest()->getParam('assetId');
        
        // get the dowload link
        $model = new Captions_Model_Captions();
        $url = $model->getDownloadLink($assetId);
                
        if (empty($url))
        {    
            throw new Zend_Controller_Action_Exception($this->_translate->translate('There was an error dowloading this caption file.'));
        }
        else
        {
            // redirect the user to the download link
            $this->_redirect($url);
        }
    }
    
    /**
     * upload a new captions file
     */
    public function uploadAction()
    {
        // prepare the form
        $request = $this->getRequest();
        $this->view->form = new Captions_Form_Upload();
        $this->view->form->setAttrib('ajax', true);
        $this->view->form->setAction($this->view->baseUrl($request->getModuleName() .'/' . $request->getControllerName() .'/'. $request->getActionName() . '/entryId/' . $request->getParam('entryId')));        
        
        // params for ksu
        $this->view->userId = Kms_Plugin_Access::getId();
        $this->view->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $this->view->ks = Kms_Resource_Client::getUserClient()->getKs();
        
        // form was submitted 
        if ($this->getRequest()->isPost())
        {
            // get the post data
            $data = $this->getRequest()->getPost();
            $upload = $data[Captions_Form_Upload::FORM_NAME];
            
            // set the upload file name - to indicate if an apload was done
            $this->view->name = isset($upload['name'])? $upload['name'] : null;
            
            // populate the form with the member data
            $this->view->form->populate($upload);
            
            // check the token and file type - to issue one validation error
            if (empty($upload['token']) || empty($upload['type']))
            {
                $this->view->form->addError($this->_translate->translate('Select a caption file'));
            }

            // validate the form
            if ($this->view->form->isValid($data))
            {
                $upload['entryId'] = $request->getParam('entryId');

                // save the upload
                try{
                    $model = new Captions_Model_Captions();
                    $model->saveCaptionAsset($upload);
                }
                catch(Kaltura_Client_Exception $e){
                    $this->view->message = $this->_translate->translate('An Error occured while uploading the caption file.');
                }

                // reload the current captions page
                $this->_forward('edit');
                return;
            }
        }
    }
    
    /**
     * change a single caption asset
     */
    public function changeAction()
    {
        // get the request
        $request = $this->getRequest();
        
        // get the parameters
        $entryId = $request->getParam('entryId');
        $captionAssetId = $request->getParam('assetId');
        
        $this->view->assetId = $captionAssetId;
        
        $this->view->form = new Captions_Form_Change();
        $this->view->form->setAttrib('ajax', true);
        $this->view->form->setAction($this->view->baseUrl($request->getModuleName() .'/' . $request->getControllerName() .'/'. $request->getActionName() . '/entryId/' . $entryId . '/assetId/' . $captionAssetId));
        
        $model = new Captions_Model_Captions();
        
        if ($request->isPost())
        {
             // get the post data
            $data = $this->getRequest()->getPost();            
            $asset = $data[Captions_Form_Change::FORM_NAME];
            $asset['captionAssetId'] = $captionAssetId;
            $asset['entryId'] = $entryId;
            
            
            // populate the form with the member data
            $this->view->form->populate($asset);
            
            // validate the form
            if ($this->view->form->isValid($data))
            {
               // save the caption asset
               $captionAsset = $model->saveCaptionAsset($asset);
               
               // if the caption asset is missing, refresh the page
               if (empty($captionAsset))
               {
                   $this->view->message = $this->_translate->translate('This caption file no longer exists.');
                   $this->_forward('edit');
                   return;
               }
               
               // set the caption asset
               $this->view->asset = $captionAsset;       
               $this->view->refreshPlayer = true; 
            }
        }
        else
        {
            // get the current caption asset values    
            $captionAsset = $model->getCaptionasset($captionAssetId);
            
            // if the caption asset is missing, refresh the page
            if (empty($captionAsset))
            {
                $this->view->message = $this->_translate->translate('This caption file no longer exists.');            
                $this->_forward('edit');
                return;
            }
            
            // populate the form with the caption asset
            $this->view->form->populate((array) $captionAsset);
        }
    }

    /**
     * get the entry for an action. 
     * used by actions that get the entry from the model(regular action), or from a request param(ajax action).
     * @param Captions_Model_Captions $model - the model to use
     * @return string the current entry id
     */
    protected function getEntry(Captions_Model_Captions $model)
    {        
        $entryId = $this->getRequest()->getParam('id');
        if (empty($entryId)) {
            $entryId = $this->getRequest()->getParam('entryId');
        }
        if (empty($entryId))
        {
            $entry = $model->getEntry();
            $entryId = $entry->id;
        }
        return $entryId;
    }
    
    /**
     * create the paginator
     * @param array $params
     * @param int $totalResults
     * @return Zend_Paginator $paginator
     */
    protected function getPaginator(array $params, $totalResults)
    {
        // init paging
        $pagingAdapter = new Zend_Paginator_Adapter_Null( $totalResults );
        $paginator = new Zend_Paginator( $pagingAdapter );
        // set the page number
        $paginator->setCurrentPageNumber(isset($params['page']) ? $params['page'] : 1);
        // set the number of items per page
        $paginator->setItemCountPerPage( $params['pagesize']);
        // set the number of pages to show
        $paginator->setPageRange(Kms_Resource_Config::getModuleConfig('captions', 'pageCount'));
        return $paginator;
    }
}