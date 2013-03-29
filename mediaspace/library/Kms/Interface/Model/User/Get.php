<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for adding data on User module.
 * Implement this interface if you have data (from kaltura API or elsewhere) you want added on User object.
 *
 * @author leon
 */
interface Kms_Interface_Model_User_Get {

    /**
     * Function gets the user model after the user object from Kaltura was fetched.
     * Function can use $model->setModuleData($data, $moduleName) to store data of the module on the entry model.
     * Function is not expected to return results. Any returned value will be ignored.
     *
     * @param Application_Model_User $model
     * @return void
     */
    function get(Application_Model_User $model);
}

?>
