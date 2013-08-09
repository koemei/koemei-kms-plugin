<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for post upload action
 * Implement this interface if you want to manipulate entry data after upload
 * 
 *
 * @author leon
 */
interface Kms_Interface_Model_Entry_EntryAdded {

    /**
     * 
     *
     * @param Application_Model_Entry $model
     * @return void
     */
    function entryAdded(Application_Model_Entry $model);
    
}

?>
