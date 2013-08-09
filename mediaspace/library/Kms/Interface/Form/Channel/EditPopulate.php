<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for populating a Channel Edit form.
 * Implement this interface if you want to populate form elements that you have added with Kms_Interface_Form_Channel_Edit.
 *
 * @author talbone
 */
interface Kms_Interface_Form_Channel_EditPopulate
{
    /**
     * Pass the Edit Category form to a module as a parameter
     * @param Application_Form_EditChannel $form
     * @param array $values
     */
    public function populate(Application_Form_EditChannel $form, array $values);
}
