<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Kms_Auth_AuthN_Sso extends Kms_Auth_AuthN_Abstract
{
    const ADAPTER_NAME = "SSO Gateway AuthN";
    
    private $sessionKey = null;
    private $ssoConfig;
    private $request;
    private $_userId;

    private $_logoutDetails = array();
    
    const SESSION_KEY_PARAMETER = 'sessionKey';
    
    public function __construct()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->request = $front->getRequest();
        
        $this->sessionKey = $this->request->getParam(self::SESSION_KEY_PARAMETER);
        $this->ssoConfig = Kms_Resource_Config::getConfiguration('auth', 'sso');
    }
    
    function loginFormEnabled()
    {
        return false;
    }
    
    function getLoginRedirectUrl()
    {
        if($this->request->getParam('ref'))
        {
            $ref = $this->request->getParam('ref');
        }
        else
        {
            $ref = $this->request->getParam('ref', $this->request->getServer('HTTP_REFERER'));
        }

        $loginUrl = $this->ssoConfig->loginUrl;

        $loginUrlParts = Zend_Uri_Http::fromString($loginUrl);
        $loginUrlParts->addReplaceQueryParameters(array('ref' => $ref));
        Kms_Log::log('returning URL for external login page for redirect '.$loginUrlParts->__toString(), Kms_Log::DEBUG);
        return $loginUrlParts->__toString();

        
        
    }

    function setPreLogoutDetails(array $details)
    {
        foreach($details as $key => $value)
        {
            if($key == 'id' || $key == 'role')
            {
                $this->_logoutDetails[$key] = $value;
            }
        }
    }

    function getLogoutRedirectUrl()
    {
        $queryString = '';
        $logoutHash = '';
        
        if(count($this->_logoutDetails) && isset($this->_logoutDetails['id']) && isset($this->_logoutDetails['role']))
        {
            $secret = ($this->ssoConfig->secret == "default") ? Kms_Resource_Config::getConfiguration('client','adminSecret') : $this->ssoConfig->secret;
            $logoutHash = Kms_Auth_SessionKey::createSessionKey($this->_logoutDetails['id'], $this->_logoutDetails['role'], $secret);
            //$queryString = (substr($this->ssoConfig->logoutUrl, -1, 1) == '=')? $logoutHash: 
        }
        
        // if logout URL ends with = (like http://somedomain.com/?sessionKey=)
        // add the logout hash as is
        if(substr($this->ssoConfig->logoutUrl, -1, 1) == '=')
        {
            $queryString = $logoutHash;
        }
        elseif(strpos($this->ssoConfig->logoutUrl, '?') !== false)
        {
            // if logout URL not ends with =, and includes any query-string, append parameter at end
            $queryString = '&'.self::SESSION_KEY_PARAMETER.'='.$logoutHash;
        }
        elseif($logoutHash)
        {
            // if logout URL does not include any query string and we did generate logoutHash, add it
            $queryString = '?'.self::SESSION_KEY_PARAMETER.'='.$logoutHash;
        }

        return $this->ssoConfig->logoutUrl.$queryString;
        
    }
    
    
    function authenticateUser()
    {
        // if no session key
        if (!$this->sessionKey)
        {
            $err = 'Single Sign-On gateway did not provide a session key after logging-in';
            Kms_Log::log('login: '.$err, Kms_Log::WARN);
            return false;
        }

        $secret = ($this->ssoConfig->secret == "default") ? Kms_Resource_Config::getConfiguration('client','adminSecret') : $this->ssoConfig->secret;
        
        $objSessionKey = new Kms_Auth_SessionKey($this->sessionKey, $secret);
        if (!$objSessionKey->getIsValid())
        {
            $err = 'invalid or expired session key';
            Kms_Log::log('login: '.$err, Kms_Log::WARN);
            return false;
        }

        $this->_userId = $objSessionKey->userId;
        // session key is valid.
        return true;
    }
    
    // do not allow keep alive
    public function allowKeepAlive()
    {
        return false;
    }

    public function getUserId()
    {
        return $this->_userId;
    }
    
    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getEmail($userId)
    {
        // this is set only when the attribute setting has value. otherwise this will return null
        return null;
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getFirstName($userId)
    {
        return null; // not implemented in this phase.
    }

    /**
     * method required by Kms_Auth_Interface_AuthN
     *
     * @param string $userId
     * @return string will return null if emailAttribute setting is not configured
     */
    public function getLastName($userId)
    {
        return null; // not implemented in this phase.
    }
    
}

?>