<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
* To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * module to handle Channel Moderation.
 *
 * @author talbone
 *
 */
class Channelmoderation_Model_Channelmoderation extends Kms_Module_BaseModel implements Kms_Interface_Contextual_Role
{
    const MODULE_NAME = 'Channelmoderation';
    /* view hooks */
    public $viewHooks = array
    (
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABLINKS => array(
                    'action' => 'tab',
                    'controller' => 'index',
                    'order' => 10
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELTABS => array(
                    'action' => 'index',
                    'controller' => 'index',
                    'order' => 10
            ),
            Kms_Resource_Viewhook::CORE_VIEW_HOOK_CHANNELLIST_LINKS => array(
                    'action' => 'link',
                    'controller' => 'index',
                    'order' => 10
            )
    );

    /* end view hooks */

    /**
     * (non-PHPdoc)
     * @see Kms_Module_BaseModel::getAccessRules()
     */
    public function getAccessRules()
    {
        $accessrules = array(
                array(
                        'controller' => 'channelmoderation:index',
                        'actions' => array('tab'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmoderation:index',
                        'actions' => array('index'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmoderation:index',
                        'actions' => array('approve'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmoderation:index',
                        'actions' => array('reject'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                ),
                array(
                        'controller' => 'channelmoderation:index',
                        'actions' => array('link'),
                        'role' => Kms_Plugin_Access::VIEWER_ROLE,
                )
        );
        return $accessrules;
    }

    /**
     * (non-PHPdoc)
     * @see Kms_Interface_Contextual_Role::getContextualAccessRuleForAction()
     */
    public function getContextualAccessRuleForAction($actionName)
    {
        $contextualRule = false;

        if ($actionName == 'index' || $actionName == 'tab' || $actionName == 'link'){
            $contextualRule =  new Kms_Module_Contextual_Access_Channel(
                    Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR,
                    array('channelname', 'channelid'),
                    false
            );
        }

        return $contextualRule;
    }
    
    /**
     * get the entries waiting moderation
     * @param Kaltura_Client_Type_Category $channel
     * @param array $params
     */
    public function getPendingEntries(Kaltura_Client_Type_Category $channel, array $params = array())
    {        
        $entryModel = Kms_Resource_Models::getEntry();
        
        if(isset($params['pageSize']) ){
            $entryModel->setPageSize($params['pageSize']);     
        }
        $pager = Application_Model_Entry::getStandardEntryPager($params);
        $pager->pageIndex = isset($params['page']) ? $params['page'] : 1;
        
        $filter = Application_Model_Entry::getStandardEntryFilter($params);
        $categoryEntryAdvancedFilter = new Kaltura_Client_Type_CategoryEntryAdvancedFilter();
        $categoryEntryAdvancedFilter->categoriesMatchOr = $channel->fullName; 
        $categoryEntryAdvancedFilter->categoryEntryStatusIn = Kaltura_Client_Enum_CategoryEntryStatus::PENDING;
        $filter->advancedSearch = $categoryEntryAdvancedFilter;
        
        $entries = $entryModel->listAction($filter, $pager);        
        
        return $entries;
    }
    
    /**
     * get the last result count for getPendingEntries()
     * @return number
     */
    public function getLastResultCount()
    {
        $entryModel = Kms_Resource_Models::getEntry();
        return $entryModel->getLastResultCount();
    }
    
    
}