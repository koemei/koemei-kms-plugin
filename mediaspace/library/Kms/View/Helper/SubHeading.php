<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of HeadingOnly
 *
 * @author yuri f
 */
class Kms_View_Helper_SubHeading extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    
    public function subHeading($subheading = '', $css_class = 'sub_heading'){
	$out = '<div class="' . $css_class . '">' . $subheading . '</div>';
	return $out;
    }
}
?>
