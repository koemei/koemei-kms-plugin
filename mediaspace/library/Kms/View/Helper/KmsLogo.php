<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/*
 * View helper to handle the header logo logic
 */

/**
 * Description of KmsLogo
 *
 * @author leon
 */
class Kms_View_Helper_KmsLogo extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function KmsLogo()
    {
        $ret = '';
        $logoImage = Kms_Resource_Config::getConfiguration('header', 'logoImage');
        $logoImageUrl = Kms_Resource_Config::getConfiguration('header', 'logoImageUrl');
        $linkUrl = Kms_Resource_Config::getConfiguration('header', 'logoLink');
        $logoAltText = Kms_Resource_Config::getConfiguration('header', 'logoAltText');
        
        $isExternalLink = (strrpos($linkUrl, "http") === false) ? false : true;
        if($linkUrl == "home")
        {
            $linkUrl = $this->view->baseUrl('/');
        }
        
        $logoSet = false;
        if($logoImageUrl)
        {
            $ret .= '<img src="'.$logoImageUrl.'" alt="'.($logoAltText ? $logoAltText : '').'" />';
			
            $logoSet = true;
        }
        
        if($logoImage && !$logoSet)
        {
            $logoUrlPath = 'img/'.$logoImage;
            $relUrl = $this->view->theme($logoUrlPath);
            //$ret .= '<img src="'.$this->view->baseUrl('build'.BUILD_NUMBER.'/'.$relUrl).'" alt="'.($logoAltText ? $logoAltText : '').'" />';
			$ret .= '<div class="logoImage"></div>';
        }
        elseif($logoAltText && !$logoSet)
        {
            $ret .= '<span class="no-logo-alt">'.$logoAltText.'</span>';
        }
        
        if($linkUrl != 'false')
        {
            $ret = '<a href="'.$linkUrl.'" '.($isExternalLink ? 'target="_blank"' : '').' >'.$ret.'</a>';
        }
        return $ret;
        
    }
}