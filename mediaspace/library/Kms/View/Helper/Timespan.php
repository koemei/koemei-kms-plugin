<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */


/*
 * View Helper to create a link to an entry
 */

/**
 * Description of TimeSpan
 *
 * @author Yuri
 */
class Kms_View_Helper_Timespan extends Zend_View_Helper_Abstract
{

    public $view;
    
    public function Timespan($start, $end = null)
    {
        if(is_null($end))
        {
            $end = time();
        }
        if ((!strtotime($start) || !strtotime($end)) && (!is_numeric($start) || !is_numeric($end)))
        {
            die('Wrong datatype for timespan() on Line: ' . __LINE__ . ' in File: ' . __FILE__);
        }
        if (!is_numeric($start))
            $start = strtotime($start);
        if (!is_numeric($end))
            $end = strtotime($end);
        $span = $end - $start;
        
        // YEAR
        $ts['year'] = intval(intval($span) / 31556926);
        // MONTH
        $ts['month'] = intval(intval($span) / 2629743);

        // WEEK
        $ts['week'] = intval(intval($span) / 604800);

        // DAY
        $ts['day'] = intval(intval($span) / 86400);


        // HOUR
        $ts['hour'] = intval(intval($span) / 3600);

        // MINUTE
        $ts['minute'] = intval(intval($span) / 60);

        // SECOND
        $ts['second'] = $span <= 0 ? 1 : $span;

        
        $minutes = $ts['minute'] > 1 ? $ts['minute'] . ' ' . $this->view->translate('minutes ago') : $this->view->translate('a moment ago');
        $hours = $ts['hour'] > 1 ? $ts['hour'] . ' ' . $this->view->translate('hours ago') : $this->view->translate('an hour ago');
        $days = $ts['day'] > 1 ? $ts['day'] . ' ' . $this->view->translate('days ago') : $this->view->translate('a day ago');
        $weeks = $ts['week'] > 1 ? $ts['week'] . ' ' . $this->view->translate('weeks ago') : $this->view->translate('a week ago');
        $months = $ts['month'] > 1 ? $ts['month'] . ' ' . $this->view->translate('months ago') : $this->view->translate('a month ago');
        $years = $ts['year'] > 1 ? $ts['year'] . ' ' . $this->view->translate('years ago') : $this->view->translate('a year ago');

        if (intval($ts['second']) > 0 && $ts['minute'] < 1)
        {
            $timespan = $minutes;
        }
        else if ($ts['minute'] > 0 && $ts['hour'] < 1)
        {
            $timespan = $minutes;
        }
        else if ($ts['hour'] > 0 && $ts['day'] < 1)
        {
            $timespan = $hours;
        }
        else if ($ts['day'] > 0 && $ts['week'] < 1)
        {
            $timespan = $days;
        }
        else if ($ts['week'] > 0 && $ts['month'] < 1)
        {
            $timespan = $weeks;
        }
        else if ($ts['month'] > 0 && $ts['year'] < 1)
        {
            $timespan = $months;
        }
        else if ($ts['year'] > 0)
        {
            $timespan = $years;
        }
        
        return $timespan;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
}