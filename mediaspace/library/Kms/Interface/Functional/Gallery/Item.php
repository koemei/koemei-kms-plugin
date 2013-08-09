<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for modules to modify the entry items in galleries.
 * Modules implementing this interface should implement Kms_Interface_Model_Entry_List - 
 * in order to get the gallery entries type data.
 * Implement this interface if you want your module to modify gallery entries - to change the type icons, 
 * for example, by data on the entry or time related.
 * Example imlpementation can be seen in webex module.
 *
 * @author talbone
 */
interface Kms_Interface_Functional_Gallery_Item extends Kms_Interface_Model_Entry_List
{
	/**
	 *  get the relevant css class for the given entry.
	 *  
	 *  @param Kaltura_Client_Type_BaseEntry $entry - the entry 
	 *  @return the relevant css class. 
	 */
    function getItemClass(Kaltura_Client_Type_BaseEntry $entry);

    /**
     *	get the relevant item description for the given entry
	 *  @param Kaltura_Client_Type_BaseEntry $entry - the entry 
	 *  @param String $description - the description so far 
	 *	@return the relevant description.
     */
    function getItemDescription(Kaltura_Client_Type_BaseEntry $entry, $description);
}