<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/*
 * Auth adapter for Zend_Auth, anonymous user
 */

/**
 * Description of Anonymous
 *
 * @author leon
 */
class Kms_Auth_Anonymous implements Zend_Auth_Adapter_Interface
{
    /**
     * Authenticate anonymous user;
     * @return Zend_Auth_Result 
     */
    public function authenticate()
    {
        $identity = new Application_Model_User();
        if(Kms_Resource_Config::getConfiguration('auth', 'allowAnonymous'))
        {
            $identity->setId(Kms_Resource_Config::getConfiguration('auth', 'anonymousGreeting'));
            $identity->setRole(Kms_Plugin_Access::getRole(Kms_Plugin_Access::ANON_ROLE));
        }
        else
        {
            $identity->setId(null);
            $identity->setRole(Kms_Plugin_Access::EMPTY_ROLE);
        }
        
        $res = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
        return $res;
    }
}