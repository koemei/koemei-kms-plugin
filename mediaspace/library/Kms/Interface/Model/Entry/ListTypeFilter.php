<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface to allow modules to modify baseEntry->list filter object before API call.
 * The interface allows to modify filter types and to change the filter by the modified type. 
 * Implement this interface if you want to affect the filter sent to Kaltura to expand/contract the search results.
 * Example imlpementation can be seen in webcast module.
 */
interface Kms_Interface_Model_Entry_ListTypeFilter
{
    
    /**
	 * Function gets the current filter as was prepared for the API call and search type and returns a modified filter.
	 *
	 * @param Kaltura_Client_Type_BaseEntryFilter $filter
	 * @param string $type the search type
	 *
	 * @return Kaltura_Client_Type_BaseEntryFilter
	 */
	public function modifyFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $type);
	
	/**
	 * Function gets the current filter types and modifies them 
	 *
	 * @param array $types the current filter types 
	 *
	 * @return array modified array of types
	 */
	public function modifyFilterTypes(array $types);
	
	/**
	 * Function gets the current search type and returns a list of sorters enabled for this type
	 *
	 * @param string $type the current filter type
	 *
	 * @return array of enabled sorters
	 */
	public function createSorters();
	
}