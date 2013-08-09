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
class Kms_View_Helper_HeadingOnly extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function headingOnly($label = '', $tag = "h1", $class = 'heading')
    {
        $out = '<div class="'.$class.'">';
	$out .= '<'.$tag.'>'.$label.'</'.$tag.'>';
	$out .= '</div>';
        return $out;
    }
    
}

?>
