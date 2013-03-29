<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * View Helper to render the thumbrotator js code
 * @author talbone
 *
 */
class Kms_View_Helper_ThumbRotator extends Zend_View_Helper_Abstract
{ 
	/**
	 *	render the thumbrotator js code
	 *	@param string $selector - the DOM selector of the element to use in the js code.
	 *	@param Kaltura_Client_Type_BaseEntry $entry - [optional] the entry whose this thumb this is.
	 *	@return string - the js code.
	 */
	public function thumbRotator($selector, Kaltura_Client_Type_BaseEntry $entry = null)
	{
		$code = 'onmouseover="KalturaThumbRotator.start($(this).find(\'' . $selector . '\').get(0))" onmouseout="KalturaThumbRotator.end($(this).find(\''. $selector . '\').get(0))"';

		if (!empty($entry)) {
			// we have an entry - check that its of a type that has thumb rotator - 
			// video and video presentation
			if (!($entry->type == Kaltura_Client_Enum_EntryType::MEDIA_CLIP 
				&& $entry->mediaType == Kaltura_Client_Enum_MediaType::VIDEO) &&
				!($entry->type == Kaltura_Client_Enum_EntryType::DATA 
				&& strpos("presentation", $entry->adminTags) !== false)) 
			{
				$code = '';
			}	
		}
		
		return $code;
	}
}