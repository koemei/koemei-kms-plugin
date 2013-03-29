<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for modules to handle a specific entry type.
 * There can be only one module handling each type.
 * Modules implementing this interface should register to the CORE_VIEW_HOOK_ENTRY_PAGE view hook.
 * Implement this interface if you want your module to handle a specific entry type.
 * Example imlpementation can be seen in webex module.
 *
 * @author talbone
 */
interface Kms_Interface_Functional_Entry_Type
{
	/**
	 *	is the implementing module handling the specified entry type.
	 * 	@param Kaltura_Client_Type_BaseEntry $entry - the entry.
	 *	@return boolean 
	 */
	public function isHandlingEntryType(Kaltura_Client_Type_BaseEntry $entry);

	/**
	 *	get a mock entry representing the entries handled by the implementing module.
	 * 	when this entry is being passed as the parameter to isHandlingEntryType($entry), it
	 *	must always return true.
	 *	@return Kaltura_Client_Type_BaseEntry - the entry 
	 */
	public function getMockEntryType();

	/**
	 *	get the relevant entry thumbnail for the given dimentions.
	 *	@param string $entryId - the entry id
	 *	@param int $height
	 * 	@param int $width
	 *	@return the entry thumbnail.
	 */
	public function getThumbnail($entryId, $height, $width);

	/**
	 *	are entries of this type editable in kms.
	 *	@return boolean
	 */
	public function isEditable();
	
	/**
	 *	check if the entry is ready to publish
	 *	@param Kaltura_Client_Type_BaseEntry $entry - the entry to be published
	 *	@param boolean $readyToPublish - the decision reached so far by the core KMS
	 *	@return boolean - is the entry ready to be published
	 */
    public function readyToPublish(Kaltura_Client_Type_BaseEntry  $entry, $readyToPublish);

    /**
     *	check if the entry can be published to a playlist
	 *	@param Kaltura_Client_Type_BaseEntry $entry - the entry to be published to the playlist
	 *	@return boolean - can the entry be published to a playlist
     */
    public function canPublishToPlaylist(Kaltura_Client_Type_BaseEntry  $entry);

	/**
	 * 	Function gets the current filter as was prepared for the API call and search type and returns a modified filter.
	 *
	 * 	@param Kaltura_Client_Type_BaseEntryFilter $filter
	 * 	@param string $type the search type
	 *
	 * 	@return Kaltura_Client_Type_BaseEntryFilter
	 */
	public function modifyFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $type);
	
	/**
	 * 	Function gets the current filter types and modifies them
	 *
	 * 	@param array $types the current filter types
	 *
	 * 	@return array modified array of types
	 */
	public function modifyFilterTypes(array $types);
	
	/**
	 * 	Function returns a list of sorters enabled for this type
	 *
	 * 	@return array of enabled sorters
	 */
	public function createSorters();
	
	/**
	 * 	Get default sorter as it is defined in module
	 * 	@param string type - the type to sort
	 *
	 * 	@return array
	 */
	public function getDefaultSorter($type);
}