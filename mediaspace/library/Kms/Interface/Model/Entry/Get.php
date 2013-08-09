<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for adding data on entry module.
 * Implement this interface if you have data (from kaltura API or elsewhere) you want added on entry object.
 * Example imlpementation can be seen in customdata module.
 *
 * @author leon
 */
interface Kms_Interface_Model_Entry_Get {

    /**
     * Function gets the entry model after the entry object from Kaltura was fetched.
     * Function can use $model->setModuleData($data, $moduleName) to store data of the module on the entry model.
     * Example implementation can be seen in customdata module.
     * Function is not expected to return results. Any returned value will be ignored.
     *
     * @param Application_Model_Entry $model
     * @return void
     */
    function get(Application_Model_Entry $model);
    
}

?>
