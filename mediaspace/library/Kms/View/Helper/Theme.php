<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Kms_View_Helper_Theme
{  
  public function theme($url)
  {
    $themeFolder = Kms_Plugin_Theme::getThemeFolderName();
    if($themeFolder)
	{
	    $baseURL = $themeFolder .'/';
	        
	    if(file_exists(APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR. 'public' . DIRECTORY_SEPARATOR . $baseURL . $url))
	    {
			$url = $baseURL . $url;
	    }
	}	
    return $url;
  }
}