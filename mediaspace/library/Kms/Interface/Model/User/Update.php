<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * This Interface defines the update function for a Model class inside a custom module.
 * Implement this interface if you need to perform any action when an user is updated.
 *
 * @author leon
 */
interface Kms_Interface_Model_User_Update {

    /**
     * Function gets the user model after the user has been updated in Kaltura over the API.
     * Function is not expected to return results. Any returned value will be ignored.
     *
     * @param Application_Model_User $model
     * @return void
     */
    function update(Application_Model_User $model);
    
}

?>
