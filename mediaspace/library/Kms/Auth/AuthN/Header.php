<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Authentication adapter that looks for custom http header which contains the user ID when user is authenticated.
 *
 * @author gonen
 */
class Kms_Auth_AuthN_Header  extends Kms_Auth_AuthN_Abstract
{
    const ADAPTER_NAME = 'Header AuthN';
    
    private $_userId;
    private $_httpHeader;
    private $_logoutRedirect;

    function __construct()
    {
        $config = Kms_Resource_Config::getConfiguration('auth', 'headerAuth');
        $this->_httpHeader = $config->headerName;
        $this->_logoutRedirect = $config->logoutUrl;

        if(!isset($this->_httpHeader) || $this->_httpHeader == "")
        {
            Kms_Log::log('header name setting must be configured when working with header authentication', Kms_Log::CRIT);
        }
    }

    public function authenticateUser()
    {
        if(isset($this->_httpHeader) && isset($_SERVER['HTTP_'.$this->_httpHeader]) && $_SERVER['HTTP_'.$this->_httpHeader] != '')
        {
            $this->_userId = $_SERVER['HTTP_'.$this->_httpHeader];
            Kms_Log::log(__METHOD__.':'.__LINE__ .' authenticated as '.$this->_userId);
            return true;
        }
        else
        {
            return false;

        }
    }

    public function getFirstName($userId) {
        return null; // header authentication currently not syncing user name
    }

    public function getLastName($userId) {
        return null; // header authentication currently not syncing user last name
    }

    public function getEmail($userId) {
        return null; // header authentication currently not syncing user email
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function loginFormEnabled() {
        return false;
    }

    public function getLoginRedirectUrl()
    {
        $front = Zend_Controller_Front::getInstance();
        
        if($front->getRequest()->getParam('ref'))
        {
            $ref = $front->getRequest()->getParam('ref');
        }
        else
        {
            $ref = $front->getRequest()->getParam('ref', $front->getRequest()->getServer('HTTP_REFERER'));
        }
        
        $inAuth = $front->getRequest()->getParam('inAuth');
        if($inAuth)
        {
            $page = Zend_Navigation_Page_Mvc::factory(array('controller' => 'user', 'action' => 'unauthorized', ));
        }
        else
        {
            $page = Zend_Navigation_Page_Mvc::factory(array('controller' => 'user', 'action' => 'authenticate', 'params' => array('inAuth' => 1, 'ref' => $ref) ));
        }
        
        return $page;
    }

    public function getLogoutRedirectUrl()
    {
        if(isset($this->_logoutRedirect) && $this->_logoutRedirect)
        {
            return $this->_logoutRedirect;
        }

        return null;
    }


}

?>
