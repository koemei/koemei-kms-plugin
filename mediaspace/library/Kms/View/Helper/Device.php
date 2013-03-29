<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/*
 * View helper to return a device id (ios, android, win, etc)
 * currently only returns ios
 */

/**
 * Description of Device
 *
 * @author leon
 */
class Kms_View_Helper_Device extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function getDevice()
    {
        static $d;
        if(!$d)
        {
            $d = '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            if(preg_match('/iPhone/', $userAgent))
            {
                $d = 'ios';
            }
            if(preg_match('/iPad/', $userAgent))
            {
                $d =  'ios';
            }
            if(preg_match('/iOS/', $userAgent))
            {
                $d =  'ios';
            }
            if(preg_match('/Android/', $userAgent))
            {
                $d =  'android';
            }
            if(preg_match('/(?i)msie ([\d]+)/', $userAgent, $matches))
            {
				if ($matches[1] <= 7) {
					$d = 'ie7';
				}
            }
        }
        return $d;
        
    }
    
    public function Device($devices = array())
    {
        if (0 == func_num_args()) {
            return $this;
        }
        if(is_array($devices))
        {
            $d = $this->getDevice();
            return in_array($d, $devices);
        }
    }
}