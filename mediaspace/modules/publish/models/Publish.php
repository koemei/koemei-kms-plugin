<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


class Publish_Model_Publish extends Kms_Module_BaseModel implements Kms_Interface_Functional_Entry_TabType
{
    const MODULE_NAME = 'publish';
    public static $Entry;

    /* view hooks */
    public $viewHooks = array 
    (
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABLINKS => array
        (
            'action' => 'tab', 
            'controller' => 'index',
            'order' => '30',
        ),
        Kms_Resource_Viewhook::CORE_VIEW_HOOK_PLAYERTABS => array
        (
            'action' => 'index', 
            'controller' => 'index',
            'order' => '30',
        ),
        Kms_Resource_ViewHook::CORE_VIEW_HOOK_MYMEDIABULK => array(
            'action' => 'bulk-button', 
            'controller' => 'index',
            'order' => '10',
            
        )
    );
    /* end view hooks */
    
    public function getAccessRules()
    {
        $accessrules[] = array(
            'controller' => 'publish:index',
            'actions' => array('tab','index'),
            'role' => Kms_Plugin_Access::ANON_ROLE,
        );
        
        $accessrules[] = array(
            'controller' => 'publish:index',
            'actions' => array('publish', 'unpublish', 'make-private', 'bulk', 'bulk-button'),
            'role' => Kms_Plugin_Access::PRIVATE_ROLE,
        );
        
        return $accessrules;
    }

    /**
     * Adding a new interface function for marking this tab as available for external entry
     * (non-PHPdoc)
     * @see Kms_Interface_Functional_Entry_TabType::isHandlingTabType()
     */
    public function isHandlingTabType(Kaltura_Client_Type_BaseEntry $entry)
    {
        $isHandlingType = false;

        $entryType = $entry->type;
        $mediaType = ($entryType == Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA ? $entry->externalSourceType : null);

        if (Kaltura_Client_Enum_EntryType::EXTERNAL_MEDIA == $entryType &&
                Kaltura_Client_ExternalMedia_Enum_ExternalMediaSourceType::INTERCALL == $mediaType)
        {
            $isHandlingType = true;
        }
        return $isHandlingType;
    }
}

