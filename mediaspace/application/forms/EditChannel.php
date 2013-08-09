<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * form to change channel settings
 * the members of this form corresponds to the members of Kaltura_Client_Type_Category - 
 * this in order for the populate to work.
 * This form is identical to Application_Form_EditCategory, but calls an aditional channel interface.
 */
class Application_Form_EditChannel extends Application_Form_EditCategory
{
    public function init()
    {
        parent::init();
        $translate = Zend_Registry::get('Zend_Translate');
        
        // allow modules to modify the form
        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_Channel_Edit') as $name => $model)
        {
            $model->editForm($this);
        }
    }
    
    public function populate(array $values)
    {
        parent::populate($values);
    
        // allow modules to populate the form
        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_Channel_EditPopulate') as $name => $model)
        {
            $model->populate($this, $values);
        }
    }
}