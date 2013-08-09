<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for adding data on category module in the channel context.
 * Implement this interface if you have data (from kaltura API or elsewhere) you want added on category object.
 * Example imlpementation can be seen in channeltopics module.
 * 
 * @author talbone
 *
 */
interface Kms_Interface_Model_Channel_Get {
    
    function get(Application_Model_Channel $model);
}

?>
