<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * Auth adapter for Admin users (partner users)
 */

/**
 * Description of Kms_Auth_Admin
 *
 * @author leon
 */
class Kms_Auth_Admin implements Zend_Auth_Adapter_Interface {
    
    private $_email;
    private $_password;
    private $_kalturaHost;
    private $_partnerId;
    private $_firstLogin;
    private $_ks = null;
    
    private $_loginKs = null;
    
    /**
     * set the email and password for authentication
     * @return void
     */
    public function __construct($email, $password, $serviceUrl = null, $partnerId = null, $firstLogin = false, $ks = null) 
    {
        if($ks)
        {
            $this->_ks = $ks;
        }
        
        $this->_email = $email;
        $this->_password = $password;
        
        
        if($serviceUrl)
        {
            $this->_kalturaHost = $serviceUrl;
        }
        else
        {
            $this->_kalturaHost = Kms_Resource_Config::getConfiguration('client', 'serviceUrl');
        }
        $this->_partnerId = $partnerId;
        $this->_firstLogin = $firstLogin;
    }
    
    public function authenticate()
    {
        $client = Kms_Resource_Client::getAdminClient();
        if($this->_firstLogin)
        {
            $client->setKs(null);
        }
        $client->getConfig()->serviceUrl = $this->_kalturaHost;
        try
        {
            if($this->_ks)
            {
                $ks = $this->_ks;
            }
            else
            {
                $ks = $client->adminUser->login($this->_email, $this->_password, $this->_partnerId);
            }
        }
        catch (Kaltura_Client_Exception $e)
        {
            Kms_Log::log('login: '.$e, Kms_Log::WARN);
            throw new Zend_Auth_Exception('Failed Admin Login with Kaltura ('.$e->getCode().': '.$e->getMessage().')', Zend_Auth_Result::FAILURE);
        }
        
        // user logged in
        if($ks)
        {
            
            // update the KS for the client
            $client->setKs($ks);
            
            $partnerInfo = $client->partner->getInfo();

            if(!$this->_ks)
            {
                $userInfo = $client->user->getByLoginId($this->_email);
            }
            else
            {
                $userInfo = new Kaltura_Client_Type_User();
                $userInfo->fullName = $partnerInfo->name;
            }
            
            
            // compare partner id, and admin email
            if( $partnerInfo->id == Kms_Resource_Config::getConfiguration('client', 'partnerId') )
            {
                // login confirmed, create an identity
                $identity = new Application_Model_User();
                $identity->setId($userInfo->fullName);
                $identity->setRole( Kms_Plugin_Access::getRole(Kms_Plugin_Access::PARTNER_ROLE) );

                // for installation - to use KS to populate config
                $this->_loginKs = $ks;
                $res = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
                return $res;
            }
            else
            {
                Kms_Log::log('login: Admin login failed', Kms_Log::WARN);
                throw new Zend_Auth_Exception('Admin login failed', Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID);
            }
        }
        else
        {
                Kms_Log::log('login: Admin login failed', Kms_Log::WARN);
                throw new Zend_Auth_Exception('Admin login failed', Zend_Auth_Result::FAILURE);
        }
    }
    
    public function getLoginKs()
    {
    	return $this->_loginKs;
    }
    
}

?>
