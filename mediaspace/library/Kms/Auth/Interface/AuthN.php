<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface to define methods that authentication adapters should implement.
 * An authentication adapter class is responsible for validating user authenticity by the given user+pass/token or any other means of authentication.
 *
 * @author gonen
 */
interface Kms_Auth_Interface_AuthN
{
    
    /**
     * method authenticates the user and
     * returns a boolean result saying if the user is authenticated or not
     * it is recommended that the method will store staticlly the user’s details for later queries
     *
     * @return string userId or false on failure
     */
    public function authenticateUser();

    /**
     * method to return the userId after successful authentication
     *
     * @return bool
     */
    public function getUserId();

    /**
     * method returns the first name of the user (as part of user details),
     * potentially fromstatic variable
     *
     * @return string
     */
    public function getFirstName($userId);

    /**
     * method returns the last name of the user (as part of user details),
     * potentially fromstatic variable
     *
     * @return string
     */
    public function getLastName($userId);

    /**
     * method returns the email of the user (as part of user details),
     * potentially fromstatic variable
     *
     * @return string
     */
    public function getEmail($userId);

    /**
     * method determines if the internal KMS login form should be used.
     * 
     * @return bool 
     */
    function loginFormEnabled();

    /**
     * method returns URL/URI to redirect to (if internal login form is not is use)
     *
     * @return mixed return URL for redirect in the form of string or null
     */
    function getLoginRedirectUrl();

    /**
     * method returns URL/URI to redirect to (for methods that handle the login/logout externally)
     *
     * @return mixed return URL for redirect in the form of string or null
     */
    function getLogoutRedirectUrl();

    /**
     * method to store user details in object before logging the user out in case those are needed to post-logout action.
     * example - SSO authentication, when logging out we need to pass back a token to let the external authentication system know the user has logged out of KMS
     * 
     * @param array $details
     * @return void
     */
    function setPreLogoutDetails(array $details);

    /**
     * method to determine whether or not to extend the expiration timestamp in the session as long as the user is active.
     *
     * @return bool
     */
    function allowKeepAlive();

    /**
     * method to determine whether or not to authentication adapter allows handling password recovery
     *
     * @return bool
     */
    function handlePasswordRecovery();

    
    /**
     * method to allow the auth adapter to change the referer - and affect the post login url.
     * 
     * @return string
     */
    function getReferer();
}

?>
