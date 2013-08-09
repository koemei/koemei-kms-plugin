<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * This Interface defines the save function for a Model class inside a custom module.
 * Implement this interface if you need to perform any action when a channel is saved.
 * Example imlpementation can be seen in channeltopics module.
 * 
 * @author talbone
 *
 */
interface Kms_Interface_Model_Channel_Save {

    /**
     * Function gets the channel model after the category has been saved in Kaltura over the API.
     * Function is not expected to return results. Any returned value will be ignored.
     * 
     * @param Application_Model_Channel $model
     * @param array $data
     */
    function save(Application_Model_Channel $model, array $data);

}