<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Adapter
 *
 * @author gonen
 */
class Kms_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    /**
     *
     * @var Application_Model_Users
     */
    private $_userModel;
    private $_role;
    private $_userId;
    private $_email = null;
    private $_firstName = null;
    private $_lastName = null;

    private $_refreshRoleOnLogin;

    /**
     * method required by Zend_Auth_Adapter_Interface.
     * This method implements the main logic of authN/Z in KMS by calling the right authentication adapter to login the user
     * and then the authorization adapter to determine the user role.
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        Kms_Log::log(__METHOD__ . ':' . __LINE__ .' going to authenticate user');
        $kalturaUserDoesNotExist = false;
        $this->_refreshRoleOnLogin = Kms_Resource_Config::getConfiguration('auth', 'refreshRoleOnLogin');

        // create the correct authentication adapter according to config
        $authNAdapterClass = self::getAuthenticationClass();
        $authNAdapter = new $authNAdapterClass();

        $userAuthenticated = $authNAdapter->authenticateUser();
        if($userAuthenticated)
        {
            $this->_userId = $authNAdapter->getUserId();
            Kms_Log::log(__METHOD__ . ' user ['.$this->_userId.'] successfully authenticated through authNAdapter '.$authNAdapterClass, Kms_Log::DEBUG);
            $this->_userModel = Kms_Resource_Models::getUser();
            if(!$this->_userModel->user)
            {
                try
                {
                    // try to get user from kaltura according to user ID
                    $this->_userModel->get($this->_userId);
                }
                catch(Kaltura_Client_Exception $ex)
                {
                    // could not get user from Kaltura, we should create one.
                    Kms_Log::log(__METHOD__ . ':'.__LINE__ . ' could not get user from Kaltura - assuming user does not exist', Kms_Log::DEBUG);
                    $kalturaUserDoesNotExist = true;
                }
            }

            // decide which authorization adapter should be used
            $authZAdapterClass = $this->getAuthorizationClass(!$kalturaUserDoesNotExist);
            // instantiate the authorization adapter
            $authZAdapter = new $authZAdapterClass();
            Kms_Log::log(__METHOD__ .':'.__LINE__ .' authorization adapter selected is '.$authZAdapterClass, Kms_Log::DEBUG);

            // try to authorize the user and get a role from the instantiated authorization adapter
            $this->_role = $authZAdapter->authorizeUser($this->_userId);
            Kms_Log::log(__METHOD__ .':'.__LINE__ .' authorization returned with role '.$this->_role, Kms_Log::DEBUG);

            if($this->_role === FALSE) // user is not authorized
            {
                Kms_Log::log('login: Role not returned. User is not authorized', Kms_Log::WARN);
                throw new Zend_Auth_Exception('Role is not registered', Zend_Auth_Result::FAILURE_UNCATEGORIZED);
            }

            // we do not trust all authorization adapters to return a valid role (although we create all of them)
            // so we validate that the role returned is one relevant for this instance of KMS
            if(!Kms_Plugin_Access::roleExists($this->_role))
            {
                Kms_Log::log('login: Role '.$this->_role.' is not registered', Kms_Log::WARN);
                throw new Zend_Auth_Exception('Role '.$this->_role.' is not registered', Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS);
            }

            // if we got here we have a valid role and user ID
            // regenerated session ID to avoid session fixation
            Zend_Session::regenerateId();
            // create identity
            $identity = new Application_Model_User();
            $identity->setId($this->_userId);
            $identity->setRole($this->_role);
            $sessionExpiration = time() + Kms_Resource_Config::getConfiguration('auth', 'sessionLifetime');
            $identity->setExpires( $sessionExpiration );
            $authResult = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);

            $refreshDetailsOnLogin = Kms_Resource_Config::getConfiguration('auth', 'refreshDetailsOnLogin');
            if($authNAdapterClass != 'Kms_Auth_AuthN_Kaltura')
            {
                // sync details to kaltura if refresh is configured or if user does not exist
                if($refreshDetailsOnLogin || $kalturaUserDoesNotExist)
                {
                    $this->_firstName = $authNAdapter->getFirstName($this->_userId);
                    $this->_lastName = $authNAdapter->getLastName($this->_userId);
                    $this->_email = $authNAdapter->getEmail($this->_userId);
                    Kms_Log::log(__METHOD__.':'.__LINE__ ." user details to sync are: [firstname = {$this->_firstName}] [lastname = {$this->_lastName}] [email = {$this->_email}]", Kms_Log::DEBUG);
                }
            }

            if($kalturaUserDoesNotExist)
            {
                // kaltura user does not exist - let's create one now
                $this->createUserOnKaltura();
            }
            else
            {
                // kaltura user exists - let's update it now
                $this->updateUserOnKaltura();
            }

        }
        else
        {
            Kms_Log::log('login: could not authenticate user', Kms_Log::WARN);
            throw new Zend_Auth_Exception('Could not authenticate user', Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID);
        }

        return $authResult;
    }

    private function updateUserOnKaltura()
    {
        $newUser = array();
        if(!$this->isUserDetailEmpty($this->_email) && $this->_email != $this->_userModel->user->email)
        {
            $newUser['email'] = $this->_email;
        }
        if(!$this->isUserDetailEmpty($this->_firstName) && $this->_firstName != $this->_userModel->user->firstName)
        {
            $newUser['firstname'] = $this->_firstName;
        }
        if(!$this->isUserDetailEmpty($this->_lastName) && $this->_lastName != $this->_userModel->user->lastName)
        {
            $newUser['lastname'] = $this->_lastName;
        }

        Kms_Log::log('user role from model is ['.$this->_userModel->getRole().']. selected role for current login is '.$this->_role, Kms_Log::DEBUG);
        // if user does not have any role in Kaltura - lets set one
        if(!$this->_userModel->getRole())
        {
            $newUser['role'] = $this->_role;
        }
        elseif($this->_userModel->getRole() != $this->_role)
        {
            // user has valid role from metadata and is different from the role decided in authorization
            if($this->_refreshRoleOnLogin)
            {
                Kms_Log::log('setting role on user data array to refresh role', Kms_Log::DEBUG);
                // only set role to be updated if role needs to be updated on every login
                $newUser['role'] = $this->_role;
            }
        }

        // extradata and password are not updated during login, only by editing user.
        // if we need to update user role and/or email - let's finish up the details so nothing will get lost
        if(count($newUser))
        {
            //not sending password will use whatever is on the user already. if user originally created through external login - password is the same random number
            $newUser['password'] = '';
            // take extradata from partnerData
            $newUser['extradata'] = Kms_Auth_AuthN_Kaltura::parseExtraData($this->_userModel->user->partnerData);
            $updatedKalturaUser = $this->_userModel->update($this->_userModel->user, $newUser);
        }    
    }

    private function createUserOnKaltura()
    {
        /**
         * note that we are not passing password.
         * since the user was not authenticated through Kaltura and we create it on-the-fly there's no need to set password (which wight not even know, like in SSO or Header)
         */

        $newUser = array();
        $newUser['username'] = $this->_userId;

        //we can set role as this is first login with that user (does not exist in kaltura)
        $newUser['role'] = $this->_role;

        // if user Email provided by authN - use it, otherwise don't sync email
        $newUser['email'] = (!$this->isUserDetailEmpty($this->_email))? $this->_email: '';
        // if user firstName provided by authN - use it, otherwise don't sync first name
        $newUser['firstname'] = (!$this->isUserDetailEmpty($this->_firstName))? $this->_firstName : $this->_userId;
        // if user lastName provided by authN - use it, otherwise don't sync last name
        $newUser['lastname'] = (!$this->isUserDetailEmpty($this->_lastName))? $this->_lastName : '';

        try
        {
            $updatedKalturaUser = $this->_userModel->add($newUser);
        }
        catch(Kaltura_Client_Exception $ex)
        {
            Kms_Log::log('Auth: could not create user on the fly. User object: '.Kms_Log::printData($newUser).' ; Kaltura error - '.$ex->getMessage());
        }
    }

    private function isUserDetailEmpty($detail)
    {
        return ($detail == "" || is_null($detail));
    }

    private function getAuthorizationClass($KalturaUserExists)
    {
        $authZAdapterClass = '';
        $authZAdapter = Kms_Resource_Config::getConfiguration('auth', 'authZAdapter');
        // if authorization method is kaltura - use kaltura, none of the other parameters are relevant
        if($authZAdapter == 'Kms_Auth_AuthZ_Kaltura')
        {
            $authZAdapterClass = 'Kms_Auth_AuthZ_Kaltura';
        }
        else
        {
            if($this->_refreshRoleOnLogin || !$KalturaUserExists)
            {
                // user is not on Kaltura, or role on Kaltura is not relvant (refreshRoleOnLogin is true)
                // in such case we should use external method as configured
                $authZAdapterClass = $authZAdapter;
            }
            elseif(!$this->_refreshRoleOnLogin && $KalturaUserExists)
            {
                // user exists in kaltura and role should be taken from kaltura (refreshRoleOnLogin is false)
                // in such case - user Kaltura
                $authZAdapterClass = 'Kms_Auth_AuthZ_Kaltura';
            }
        }

        if (class_exists($authZAdapterClass) && in_array('Kms_Auth_Interface_AuthZ', class_implements($authZAdapterClass)))
        {
            return $authZAdapterClass;
        }
        else
        {
            $err = $this->_translate->translate('Error in authentication') . '. ' . $this->_translate->translate('Authorization Adapter') . ' "' . $authZAdapterClass . '" ' . $this->_translate->translate('does not exist') . '!';
            Kms_Log::log('login: '.$err .' or does not implement the required interface', Kms_Log::ERR);
            throw new Zend_Exception($err, 500);
        }
        
    }

    public static function getAuthenticationClass()
    {
        $authNAdapterClass = Kms_Resource_Config::getConfiguration('auth', 'authNAdapter');
        if (class_exists($authNAdapterClass) && in_array('Kms_Auth_Interface_AuthN', class_implements($authNAdapterClass)))
        {
            return $authNAdapterClass;
        }
        else
        {
            $err = $this->_translate->translate('Error in authentication') . '. ' . $this->_translate->translate('Authentication Adapter') . ' "' . $authNAdapterClass . '" ' . $this->_translate->translate('does not exist') . '!';
            Kms_Log::log('login: '.$err.' or does not implement the required interface', Kms_Log::ERR);
            throw new Zend_Exception($err, 500);
        }

    }

}

?>
