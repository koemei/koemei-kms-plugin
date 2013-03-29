<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for extending an Channel Edit form.
 * Implement this interface if you want to add form elements (Zend_Form_Element) to the edit channel form.
 * Example implementation can be seen in channeltopics module.
 *
 * @author talbone
 */
interface Kms_Interface_Form_Channel_Edit
{
    /**
     * Pass the Edit Category form to a module as a parameter
     * @param Application_Form_EditChannel $form
     * @return none
     */
    public function editForm(Application_Form_EditChannel $form);
}
