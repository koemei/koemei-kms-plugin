<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of MajorVersion
 *
 * @author gonen
 */
class Kms_View_Helper_MajorVersion extends Zend_View_Helper_Abstract
{
    public function MajorVersion()
    {
        $version = Kms_Resource_Config::getVersion();
        $parts = explode('.', $version);
        return $parts[0].'.'.$parts[1];
    }
}

?>
