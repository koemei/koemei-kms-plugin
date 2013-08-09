<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for manipulating the sorting options in an Entry List request and in Gallery page (Recent, Most viewed, etc).
 * Implement this interface if you want add or remove sorting options in a gallery and in My Media.
 * Please note, you need to implement both functions for this to function correctly
 * Example implementation can be seen in comments module.
 *
 * @author leon
 */
interface Kms_Interface_Model_Entry_FilterSort
{
    /**
     * Pass the sorters to the editSorters function
     *
     * @return array
     */
    public function editSorters(array $sorters);
    
    
    /**
     *   Edit the sort part of the base entry filter
     * 
     *   @param Kaltura_Client_Type_BaseEntryFilter
     *   @param array
     * 
     *   @return Kaltura_Client_Type_BaseEntryFilter
     */
    public function editSortFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $params);
}
