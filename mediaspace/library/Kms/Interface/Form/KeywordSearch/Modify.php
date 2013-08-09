<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for extending an Category Edit form.
 * Implement this interface if you want to add form elements (Zend_Form_Element) to the edit category form.
 *
 * @author talbone
 */
interface Kms_Interface_Form_KeywordSearch_Modify
{
    /**
     * Pass the Edit Category form to a module as a parameter
     *
     * @return none
     */
    public function editKeywordSearch(Application_Form_KeywordSearch $form);
}

