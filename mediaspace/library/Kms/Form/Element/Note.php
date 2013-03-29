<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of Note
 * Note element, adds any html to a Zend Form
 *
 * @author leon
 */
class Kms_Form_Element_Note extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNote';

    /**
     * Function for overriding validation
     * 
     * @param type $value
     * @param type $context
     * @return Boolean 
     */
    public function isValid($value, $context = null) 
    {
      return TRUE; 
    }

    
}

?>
