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
class Kms_View_Helper_TopBreadCrumbs extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
        
    public function TopBreadCrumbs($bc, $pagesInfo = ''){

	$out = '<div id="breadcrumbs">';
	$out .=	$bc;
	$out .= '<span id="result_counter" style="visibility: visible;">';
	$out .= $pagesInfo;
    	$out .= '</span>';
        $out .= '</div>';
	return $out;
    }
}

?>
