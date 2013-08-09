<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of Abstract
 *
 * @author leon
 */
abstract class Kms_Auth_AuthN_Abstract implements Kms_Auth_Interface_AuthN
{
    
    
    function loginFormEnabled()
    {
        return true;
    }
    
    function getLoginRedirectUrl()
    {
        return null;
    }
    
    function getLogoutRedirectUrl()
    {
        return null;
    }

    function setPreLogoutDetails(array $details)
    {
        return null;
    }
    
    function allowKeepAlive()
    {
        return true;
    }

    function handlePasswordRecovery()
    {
        return false;
    }
    
    function getReferer()
    {
        return null;
    }
}
?>
