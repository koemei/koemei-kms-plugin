<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * This Interface defines the save function for a Model class inside a custom module.
 * Implement this interface if you need to perform any action when an entry is saved.
 *
 * @author leon
 */
interface Kms_Interface_Model_Entry_Save {

    /**
     * Function gets the entry model after the entry has been saved in Kaltura over the API.
     * Function is not expected to return results. Any returned value will be ignored.
     *
     * @param Application_Model_Entry $model
     * @return void
     */
    function save(Application_Model_Entry $model);
    
}

?>
