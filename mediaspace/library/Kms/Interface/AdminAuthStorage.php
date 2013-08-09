<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/
/**
 * A module that implements this interface can modify the auth storage type to admin storage for some of its controller actions
 * This can be used for protecting some actions with partners admin credentials
 *
 * @package Kms
 * @subpackage Interfaces
 * @author roman
 */
interface Kms_Interface_AdminAuthStorage
{
    /**
     * Returns true if controller / action should use admin storage for auth
     *
	 * @var $request Zend_Controller_Request_Abstract
     * @return bool
     */
    function shouldUseAdminAuthStorage(Zend_Controller_Request_Abstract $request);
}