<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Profiler
 *
 * @author leon
 */
class Kms_Plugin_Profiler extends Zend_Controller_Plugin_Abstract
{
    
    // check for duplicate API requests
    public function dispatchLoopShutdown()
    {
        if(Kms_Resource_Config::getConfiguration('debug', 'kalturaDebug'))
        {
            $count = array_count_values(Kms_ClientLog::$statUrls);
            foreach($count as $u => $c)
            {
                if($c > 1)
                {
                    Kms_Log::statsLog('[profiler: executed '.$c.' times] [url: '.$u.']', Kms_Log::WARN);
                }
            }
        }
        
    }
}

