<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Addcontent_Model_Addcontent extends Kms_Module_BaseModel
{
    public $pageSize = Application_Model_Entry::MY_MEDIA_LIST_PAGE_SIZE;
    public $totalCount = 0;
    
    const MODULE_NAME = 'Addcontent';
    /* view hooks */
    public $viewHooks = array
        (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_GALLERY_BUTTONS => array
            (
                'action' => 'button', 
                'controller' => 'index',
                'order' => 20
            ),
        );
    /* end view hooks */

    
    public function getAccessRules()
    {
        // set Kms_Plugin_Access::PRIVATE_ROLE in case of channel, and Kms_Plugin_Access::ADMIN_ROLE in case it is a gallery 
        $role = Kms_Plugin_Access::ADMIN_ROLE;
        $front = Zend_Controller_Front::getInstance();
        $channelName = $front->getRequest()->getParam('channelname');
        $channelId = $front->getRequest()->getParam('channelid');
        
        if($channelName || $channelId) {
            $role = Kms_Plugin_Access::PRIVATE_ROLE;
        }
        
        $accessrules = array(
            array(
                    'controller' => 'addcontent:index',
                    'actions' => array('button'),
                    'role' => $role,
            ),
            array(
                    'controller' => 'addcontent:index',
                    'actions' => array('index', 'add'),
                    'role' => $role,
            ),
        );
        
        return $accessrules;
    }
    
    public function getEntries($params = array())
    {
        $entryModel = Kms_Resource_Models::getEntry();
        
        // init the standard entry filter
        $filter = Application_Model_Entry::getStandardEntryFilter($params);

        // init the standard entry pager
        $pager = Application_Model_Entry::getStandardEntryPager(array('pageSize' => $this->pageSize));

        // set the page number
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;

        // filter by $userId
        $filter->userIdEqual = isset($params['userId']) ? $params['userId'] : null;

        // get the entries
        $entries = $entryModel->listAction($filter, $pager);
        $this->totalCount = $entryModel->getLastResultCount();
        
        return $entryModel->Entries;   
    }
    
    
}

