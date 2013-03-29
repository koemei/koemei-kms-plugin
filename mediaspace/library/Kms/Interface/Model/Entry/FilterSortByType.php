<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for manipulating the sorting options in an Entry List request and in Gallery page (Recent, Most viewed, etc).
 * Implement this interface if you want add or remove sorting options in a gallery and in My Media.
 * Please note, you need to implement 3 functions for this to function correctly
 * Example implementation can be seen in comments module.
 *
 * @author yulia.t
 */
interface Kms_Interface_Model_Entry_FilterSortByType extends Kms_Interface_Model_Entry_FilterSort
{
    /**
     * Pass the sorters to the editSortersByType function
     * @param array sorters
     * @param string type - the type of selection to perform sort on  
     *
     * @return array
     */
    public function editSortersByType(array $sorters, $type);
   
}
