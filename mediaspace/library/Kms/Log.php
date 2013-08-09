<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/*
 * Basic logger file for Kms
 */

/**
 * Description of Log
 *
 * @author leon
 */
class Kms_Log
{

    private static $_log = null;
    private static $_traceLog = null;
    private static $_statsLog = null;
    private static $_logLevel = Zend_Log::ERR;
    private static $_statsEnabled = false;
    
    
    const ALERT = Zend_Log::ALERT;
    const CRIT = Zend_Log::CRIT;
    const ERR = Zend_Log::ERR;
    const WARN = Zend_Log::WARN;
    const NOTICE = Zend_Log::NOTICE;
    const INFO = Zend_Log::INFO;
    const DEBUG = Zend_Log::DEBUG;
    const DUMP = 8;

    public static function setDebugLevel()
    {
        $level = Kms_Resource_Config::getConfiguration('debug', 'logLevel');
        if($level)
        {
            self::$_logLevel = $level;
        }
        
        self::$_statsEnabled = Kms_Resource_Config::getConfiguration('debug', 'kalturaStats');
    }
    
    public static function setLogWriters()
    {
        $logFormat = Kms_Resource_Config::getLogFormat();
        
        $enableEmailErrors = Kms_Resource_Config::getConfiguration('debug', 'emailErrors');
        $emailAddresses = Kms_Resource_Config::getConfiguration('debug', 'emailAddress');
        if($enableEmailErrors && count($emailAddresses) )
        {
            $mail = new Zend_Mail();
            foreach($emailAddresses as $addr)
            {
                $mail->addTo($addr);
            }
            $mailFrom = Kms_Resource_Config::getConfiguration('application', 'title');
            $mail->setFrom('mediaspace@'.$_SERVER['SERVER_NAME'], $mailFrom);
            $emailWriter = new Zend_Log_Writer_Mail($mail);
            $emailWriter->setSubjectPrependText('KMS '.Kms_Resource_Config::getVersion().' debug information ['.UNIQUE_ID.']');
            $logFormat = self::parseEventMetadata($logFormat);
            $formatter = new Zend_Log_Formatter_Simple($logFormat . PHP_EOL);
            $emailWriter->setFormatter($formatter);
            $emailPriority = Kms_Resource_Config::getConfiguration('debug', 'emailThreshold');
            $emailFilter = new Zend_Log_Filter_Priority((int) $emailPriority, '<=');
            $emailWriter->addFilter($emailFilter);
            self::$_log->addWriter($emailWriter);
        }
    }
    
    public static function initLog()
    {
        $path = Kms_Resource_Config::getLogPath();
        $logFormat = Kms_Resource_Config::getLogFormat();
        
        if ($path && is_writable(dirname($path)))
        {
            $writer = new Zend_Log_Writer_Stream($path);
            $logFormat = self::parseEventMetadata($logFormat);
            $formatter = new Zend_Log_Formatter_Simple($logFormat . PHP_EOL);
            $writer->setFormatter($formatter);

            self::$_log = new Zend_Log($writer);
            self::$_log->addPriority('DUMP', self::DUMP);
            self::$_log->setTimestampFormat("d-M-Y H:i:s");
            
        }
    }

    public static function initTraceLog()
    {
        $path = Kms_Resource_Config::getTraceLogPath();
        $logFormat = Kms_Resource_Config::getLogFormat();

        if ($path && is_writable(dirname($path)))
        {
            $writer = new Zend_Log_Writer_Stream($path);
            $logFormat = self::parseEventMetadata($logFormat);
            $formatter = new Zend_Log_Formatter_Simple($logFormat . PHP_EOL);
            $writer->setFormatter($formatter);
            self::$_traceLog = new Zend_Log($writer);
            self::$_traceLog->setTimestampFormat("d-M-Y H:i:s");
        }
    }
    
    
    
    public static function initStatsLog()
    {
        $path = Kms_Resource_Config::getStatsLogPath();
        $logFormat = Kms_Resource_Config::getLogFormat();

        if ($path && is_writable(dirname($path)))
        {
            $writer = new Zend_Log_Writer_Stream($path);
            $logFormat = self::parseEventMetadata($logFormat);
            $formatter = new Zend_Log_Formatter_Simple($logFormat . PHP_EOL);
            $writer->setFormatter($formatter);
            self::$_statsLog = new Zend_Log($writer);
            $fbWriter = new Zend_Log_Writer_Firebug();
            $fbWriter->setFormatter($formatter);
            self::$_statsLog->addWriter($fbWriter);

            self::$_statsLog->setTimestampFormat("d-M-Y H:i:s");
        }
        
    }

    public static function parseEventMetadata($format)
    {
        $format = str_replace('%logIp%', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'no-ip', $format);
        $format = str_replace('%pid%', UNIQUE_ID, $format);
        $format = str_replace('%host%', php_uname('n'), $format);
        //$format = str_replace('%memoryUsage%', memory_get_usage(), $format);
        return $format;
    }

    public static function trace($obj, $method = Zend_Log::DEBUG, $extra = null)
    {
        if (is_null(self::$_traceLog))
        { // initialize the logger statically for the application
            self::initTraceLog();
        }

        if (self::$_traceLog)
        {
            $mem = memory_get_usage();
            if ($mem)
            {
                $mem = round($mem / 1024 / 1024, 2) . ' MB';
            }
            $real = memory_get_usage(true);
            if($real)
            {
                $real = round($real / 1024 / 1024, 2) . 'MB';
            }
            if (!is_string($obj))
            {
                $obj = print_r($obj, true);
            }

            $obj = '[memory: ' . $mem . ', real: '.$real.']' . $obj;

            self::$_traceLog->log($obj, $method, $extra);
        }
    }
    
    public static function dump($obj)
    {
        self::log($obj, self::DUMP);
    }

    public static function statsLog($obj, $level = null)
    {
        if (is_null(self::$_statsLog))
        { // initialize the logger statically for the application
            self::initStatsLog();
        }
        if (self::$_statsLog && self::$_statsEnabled)
        {
            if(is_null($level))
            {
                $level = self::DEBUG;
            }
            
            $mem = memory_get_usage();
            if ($mem)
            {
                $mem = round($mem / 1024 / 1024, 2) . ' MB';
            }
            $real = memory_get_usage(true);
            if($real)
            {
                $real = round($real / 1024 / 1024, 2) . 'MB';
            }
            
            if (!is_string($obj))
            {
                $obj = print_r($obj, true);
            }

            $obj = '[memory: ' . $mem . ', real: '.$real.'] ' . $obj;
            self::$_statsLog->log($obj, $level);
        }
    }
    
    public static function log($obj, $method = Zend_Log::INFO, $extra = null)
    {
        if (is_null(self::$_log))
        { // initialize the logger statically for the application
            self::initLog();
        }
        if (self::$_log)
        {
            
            if ($method <= self::$_logLevel)
            {
                $mem = memory_get_usage();
                if ($mem)
                {
                    $mem = round($mem / 1024 / 1024, 2) . ' MB';
                }
                $real = memory_get_usage(true);
                if($real)
                {
                    $real = round($real / 1024 / 1024, 2) . 'MB';
                }
                
                if (!is_string($obj))
                {
                    $obj = print_r($obj, true);
                }
                // cut extra line breaks from log message if method is not "dump"
                if($method < self::DUMP)
                {
                    $idx = strpos($obj, "\n");
                    if($idx)
                    {
                        $obj = substr($obj, 0, $idx);
                    }
                }

                $obj = '[memory: ' . $mem . ', real: ' . $real . '] ' . $obj;
//                Zend_Debug::dump($method);
//                exit;
                self::$_log->log($obj, $method, $extra);
            }
        }
    }

    public static function printData($data)
    {
        return json_encode($data);
    }

}
