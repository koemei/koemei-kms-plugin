<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for extending an Entry Edit form.
 * Implement this interface if you want to add form elements (Zend_Form_Element) to the edit entry form.
 * Example implementation can be seen in customdata module.
 *
 * @author leon
 */
interface Kms_Interface_Form_Entry_Edit 
{
    /**
     * Pass the Edit Entry form to a module as a parameter
     *
     * @return none
     */
    public function editForm(Application_Form_EditEntry $form);
}

?>
