<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface to define methods that authorization adapters should implement.
 * An authorization adapter is responsible for determining the KMS role a user should get when authenticating into KMS.
 *
 * @author gonen
 */
interface Kms_Auth_Interface_AuthZ
{

    /**
     * Method gets userId and is responsible for determiniring the role that the user should get
     *
     * @return mixed return a string representing the user role if authorized or FALSE if not authorized
     */
    public function authorizeUser($userId);

}

?>
