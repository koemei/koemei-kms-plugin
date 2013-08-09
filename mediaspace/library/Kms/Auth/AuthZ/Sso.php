<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Sso
 *
 * @author gonen
 */
class Kms_Auth_AuthZ_Sso implements Kms_Auth_Interface_AuthZ
{
    const ADAPTER_NAME = 'SSO Gateway AuthZ';
    const AUTHENTICATION_METHOD_REQUIRED = 'Kms_Auth_AuthN_Sso';
    const SESSION_KEY_PARAMETER = 'sessionKey';

    public function __construct()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->request = $front->getRequest();

        $this->sessionKey = $this->request->getParam(self::SESSION_KEY_PARAMETER);
        $this->ssoConfig = Kms_Resource_Config::getConfiguration('auth', 'sso');
    }

    /**
     * method required by Kms_Auth_Interface_AuthZ
     * 
     * @param string $userId
     * @return mixed user role if authorized or false to deny
     */
    public function authorizeUser($userId)
    {
        $authenticationClass = Kms_Auth_Adapter::getAuthenticationClass();
        if($authenticationClass != self::AUTHENTICATION_METHOD_REQUIRED)
        {
            $err = 'Kms_Auth_AuthZ_Sso should only be used with Kms_Auth_AuthN_Sso as authentication adapter, while '.$authenticationClass.' is configured';
            Kms_Log::log('auth configuration: '.$err, Kms_Log::CRIT);
            throw new Exception('Kms_Auth_AuthZ_Sso should only be used with Kms_Auth_AuthN_Sso as authentication adapter');
        }

        // if no session key
        if (!$this->sessionKey)
        {
            $err = 'Single Sign-On gateway did not provide a session key after logging-in';
            Kms_Log::log('login: '.$err, Kms_Log::WARN);
            throw new Zend_Auth_Exception($err, Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS);
        }

        $secret = ($this->ssoConfig->secret == "default") ? Kms_Resource_Config::getConfiguration('client','adminSecret') : $this->ssoConfig->secret;

        $objSessionKey = new Kms_Auth_SessionKey($this->sessionKey, $secret);
        if (!$objSessionKey->getIsValid())
        {
            $err = 'invalid or expired session key';
            Kms_Log::log('login: '.$err, Kms_Log::WARN);
            return false;
        }

        if (!self::isValidRole($objSessionKey->role))
        {
            $err = 'Specified role ['.$objSessionKey->role.'] doesn\'t exist in this KMS instance';
            Kms_Log::log('login: '.$err, Kms_Log::WARN);
            return false;
        }

        return $objSessionKey->role;
    }

    private static function isValidRole($checkRole)
    {
        $roles = Kms_Resource_Config::getSection('roles');

        foreach($roles as $role)
        {
            if(strtolower($checkRole) == strtolower($role))
            {
                return true;
            }
        }

        return false;
    }
}

?>
