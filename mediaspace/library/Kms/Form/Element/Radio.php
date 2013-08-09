<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * a radio button element with unescaped labels, so they can contain html.
 * @author talbone
 *
 */
class Kms_Form_Element_Radio extends Zend_Form_Element_Radio
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form_Element::getFullyQualifiedName()
     */
    public function getFullyQualifiedName()
    {
        $name = array();
        $name['name'] = parent::getFullyQualifiedName();
        $name['escape'] = false;
        return $name;
    }
}