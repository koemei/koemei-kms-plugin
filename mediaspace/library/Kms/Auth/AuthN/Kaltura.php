<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Kaltura
 *
 * @author gonen
 */
class Kms_Auth_AuthN_Kaltura extends Kms_Auth_AuthN_Abstract
{
    const ADAPTER_NAME = "Kaltura AuthN";

    private $_username;
    private $_hashedPassword;
    private $_password;
    private $request;

    /**
     * set the username and password for authentication
     * @return void
     */
    public function __construct()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->request = $front->getRequest();
        $login = $this->request->getParam('Login');

        $this->_username = $login['username'];
        $this->_password = $login['password'];
        $this->_hashedPassword = sha1($login['password']);
    }

    public function authenticateUser()
    {
        try
        {
            $userModel = Kms_Resource_Models::getUser();
            $user = $userModel->get($this->_username);
        }
        catch (Kaltura_Client_Exception $e)
        {
            // user probably does not exist - fail authentication
            Kms_Log::log('login: '.$e, Kms_Log::WARN);
            return false;
        }

        if(!$user->isAdmin) // handle authentication for regular users
        {
            if(!isset($user->partnerData))
            {
                Kms_Log::log('login: No Partner Data for User', Kms_Log::WARN);
                return false;
            }

            $userPassword = self::parsePassword($user->partnerData);
            if(!$userPassword)
            {
                Kms_Log::log('login: Password for user is not set', Kms_Log::WARN);
                return false;
            }
            if($userPassword != $this->_hashedPassword)
            {
                Kms_Log::log('login: Invalid Password for user '.$this->_username, Kms_Log::WARN);
                return false;
            }

            return true;
        }
        else // authenticate admin user
        {
            $client = Kms_Resource_Client::getUserClient();
            $client->setKs('');
            try
            {
                $ks = $client->user->loginByLoginId($this->_username, $this->_password, Kms_Resource_Config::getConfiguration('client', 'partnerId'));
                // if no exception, login was successful, i.e. user is authenticated
                return true;
            }
            catch(Kaltura_Client_Exception $ex)
            {
                Kms_Log::log('login: failed to authenticate admin user '.$ex.' '.Kms_Log::printData($user), Kms_Log::WARN);
                return false;
            }
        }
    }

    public function getUserId()
    {
        return $this->_username;
    }

    public function getFirstName($userId)
    {
        // when authenticating user on kaltura no need to update user data on kaltura.
        return null;
    }

    public function getLastName($userId)
    {
        // when authenticating user on kaltura no need to update user data on kaltura.
        return null;
    }

    public function getEmail($userId)
    {
        // when authenticating user on kaltura no need to update user data on kaltura.
        return null;
    }

    public static function parseExtraData($partnerData)
    {
        $retArray = array();
        $extraDataArray = explode(',', $partnerData);
        foreach($extraDataArray as $extraDataPiece)
        {
            $extraDataParts = explode('=', $extraDataPiece);
            if(count($extraDataParts) == 1 || $extraDataParts[0] != 'pw' && $extraDataParts[0] != 'role')
            {
                $retArray[] = $extraDataPiece;
            }
        }
        if(count($retArray))
        {
            return join(',', $retArray);
        }
        else
        {
            return '';
        }

    }

    public static function parsePassword($partnerData)
    {
        if(preg_match('/pw=([^,]+)/', $partnerData, $matches) && $matches[1])
        {
            return $matches[1];
        }
        else
        {
            return null;
        }
    }

    function handlePasswordRecovery()
    {
        return true;
    }
}

?>
