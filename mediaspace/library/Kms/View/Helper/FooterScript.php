<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

 /**
 * Footer script view helper. Identical to the Zend HeadScript one, only
 * rendered in the page footer.
 *
 * @author talbone
 *
 */
class Kms_View_Helper_FooterScript extends Zend_View_Helper_HeadScript
{
	protected $_regKey = 'Zend_View_Helper_FooterScript';

	public function footerScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
	{
		return $this->headScript($mode, $spec, $placement, $attrs, $type);
	}
}