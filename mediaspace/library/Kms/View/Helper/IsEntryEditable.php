<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * View Helper to determine if an entry is editable.
 *
 * @author talbone
 *
 */
class Kms_View_Helper_IsEntryEditable extends Zend_View_Helper_Abstract
{
    public $view;
    
    /**
     *	check if an entry is editable by running it throuh the Modules 
     *	Kms_Interface_Functional_Entry_Type::isEditable() method.
     *
     *	@param Kaltura_Client_Type_BaseEntry $entry - the entry to check.
     *	@return boolean if a module is found to handle this type, null otherwise.
     */
    public function IsEntryEditable(Kaltura_Client_Type_BaseEntry $entry)
    {
    	$isEntryEditable = null;

        $models = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Functional_Entry_Type');
        foreach ($models as $model) {
        	if ($model->isHandlingEntryType($entry)) {
        		$isEntryEditable = $model->isEditable();
        		break;
        	}
        }

    	return $isEntryEditable;
    }
}