<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Time Elapsed view helper. Just translated the time elapsed, no extra "ago" or anything else.
 * Only shows the top level of time elapsed.
 *
 * @author talbone
 *
 */
class Kms_View_Helper_TimeElapsed extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function TimeElapsed($secs, $maxLevel = 2)
    {
    	// use the global translator to be able to use the helper with no view
    	$translator = Zend_Registry::get('Zend_Translate');

    	if (empty($secs)) {
    		return '';
    	}

		$bit = array(
	        ' ' .$translator->translate('year')      => $secs / 31556926 % 12,
	        ' ' .$translator->translate('week')      => $secs / 604800 % 52,
	        ' ' .$translator->translate('day')       => $secs / 86400 % 7,
	        ' ' .$translator->translate('hour')      => $secs / 3600 % 24,
	        ' ' .$translator->translate('minute')    => $secs / 60 % 60,
	        ' ' .$translator->translate('second')    => $secs % 60	        
	    );
	    
	    $ret = array();
	    $i = 1;
	    foreach($bit as $k => $v){
	    	if ($i < $maxLevel) {
	    		if ($v != 0) $i++;
		    	if ($v < 1) $v = -$v;
		        if($v > 1)$ret[] = $v . $k . 's';
		        if($v == 1)$ret[] = $v . $k;
	    	}
	    }
	   
	    return join(' ', $ret);
    }
}