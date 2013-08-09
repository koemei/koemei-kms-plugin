<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for modules to render a tab inside specific entry types.
 * Implement this interface if you want your module to render an entry tab for specific entry types.
 * Example imlpementation can be seen in the publish module.
 *
 * @author yuliat
 */
interface Kms_Interface_Functional_Entry_TabType
{
	/**
	 *	is the implementing module handling the specified entry type.
	 * 	@param Kaltura_Client_Type_BaseEntry $entry - the entry.
	 *	@return boolean 
	 */
	public function isHandlingTabType(Kaltura_Client_Type_BaseEntry $entry);
}