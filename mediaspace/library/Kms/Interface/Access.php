<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/
/**
 * Interface for access rules for modules.
 * Every module that implements a controller with actions must declare its access rules, otherwise the actions will not be usable.
 *
 * @package Kms
 * @subpackage Interfaces
 * @author leon
 */
interface Kms_Interface_Access
{
    /**
     * function that returns access rules in the form of something something
     *
     * @return array
     */
    function getAccessRules();
}


?>
