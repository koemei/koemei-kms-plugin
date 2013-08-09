<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Demo
 *
 * @author leon
 */
class Kms_Auth_Demo implements Zend_Auth_Adapter_Interface
{
    private $_id = null;
    private $_role = null;
    
    public function __construct($role = null)
    {
        $this->_role = $role;
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        if($request)
        {
            $login = $request->getParam('Login');
            if(is_array($login) && isset($login['username']))
            {
                $this->setId( $login['username'] );
            }
        }
    }
    
    public function setId($id)
    {
        $this->_id = $id;
    }
    
    public function loginFormEnabled()
    {
        return true;
    }
    
    public function setRole($role)
    {
        $this->_role = $role;
    }
    
    
    public function authenticate()
    {
        $user = new Application_Model_User();
        $user->setRole($this->_role);
        $user->setId($this->_id);
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user);
    }
}

