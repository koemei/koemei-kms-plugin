<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of ClientLog
 * Logging for the Kaltura Client
 * @author leon
 */
class Kms_ClientLog implements Kaltura_Client_ILogger 
{

    private $_log = null;
    private $_errorLog = null;
    private $debugEnabled = false;
    public static $statUrls = array();
    private static $requestNumber = 0;
    
    
    public function __construct()
    {
        $path = Kms_Resource_Config::getClientLogPath();
        $errorPath = Kms_Resource_Config::getClientErrorLogPath();
        $logFormat = Kms_Resource_Config::getLogFormat();
        $logFormat = Kms_Log::parseEventMetadata($logFormat);
        $formatter = new Zend_Log_Formatter_Simple($logFormat . PHP_EOL);
        
        if($path && is_writable(dirname($path)))
        {
            $writer = new Zend_Log_Writer_Stream( $path );
            $writer->setFormatter($formatter);
            $this->_log = new Zend_Log($writer);
            $this->_log->setTimestampFormat("d-M-Y H:i:s");
        }
        if($errorPath && is_writable(dirname($errorPath)))
        {
            $writer = new Zend_Log_Writer_Stream( $errorPath );
            $writer->setFormatter($formatter);
            $this->_errorLog = new Zend_Log($writer);
            $this->_errorLog->setTimestampFormat("d-M-Y H:i:s");
            $fbWriter = new Zend_Log_Writer_Firebug();
            $fbWriter->setFormatter($formatter);
            $this->_errorLog->addWriter($fbWriter);
            
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
                $emailWriter->setSubjectPrependText('KMS '.Kms_Resource_Config::getVersion().' API Request ['.UNIQUE_ID.']');
                $emailWriter->setFormatter($formatter);
                $emailPriority = Kms_Resource_Config::getConfiguration('debug', 'emailThreshold');
                $emailFilter = new Zend_Log_Filter_Priority((int) $emailPriority);
                
                $emailWriter->addFilter($emailFilter);
                $this->_errorLog->addWriter($emailWriter);
            }
            
        }
        
        $this->debugEnabled = Kms_Resource_Config::getConfiguration('debug', 'kalturaDebug');
    }
    
    
    public function log($obj)
    {
        static $statMessage;
        static $curl;
        static $kalsig;
        static $totalTime = 0;
        
        if(preg_match('#^service url:.*#', $obj))
        {
            // restart the log message stacking
            $statMessage = '';
            $curl = '';
            $kalsig = '';
            self::$requestNumber = self::$requestNumber ? self::$requestNumber + 1 : 1;
        }
        elseif(preg_match('#^curl: .*$#', $obj))
        {
            preg_match('#^curl: (.*)$#', $obj, $matches);
            if(isset($matches[1]))
            {
                self::$statUrls[] = $curl = trim($matches[1]);
                if(preg_match('#&kalsig=(.*)?#', $curl, $kalsigMatches))
                {
                    if(isset($kalsigMatches[1]))
                    {
                        $kalsig = trim($kalsigMatches[1]);
                    }
                }
            }
        }
        elseif(preg_match('#^execution time for.*#', $obj))
        {
            if(preg_match('#.*index\.php\?service=(.*)&action=(.*)$#', $obj, $matches))
            {
                if(isset($matches[1]))
                {
                    $statMessage .= '[service: '.$matches[1].'] ';
                }
                if(isset($matches[2]))
                {
                    $statMessage .= '[action: '.$matches[2].'] ';
                }
            }
            elseif(strpos($obj, '?service=multirequest'))
            {
                $statMessage .= '[service: multirequest] ';
            }
            $statMessage .= '[request: '.self::$requestNumber.'] ';
            
            if($kalsig)
            {
                $statMessage .= '[kalsig: '.$kalsig.'] ';
            }
            
            
            preg_match('#.*: \[(.*)\]$#', $obj, $matches);
            if(isset($matches[1]) && is_numeric($matches[1]))
            {
                $totalTime += $matches[1];
                $statMessage .= '[time: '.round($matches[1], 4).'s / total: '.round($totalTime, 4).'s]';
            }

            Kms_Log::statsLog($statMessage, Kms_Log::INFO );
            // send the string
        }
        
        if($this->_log && $this->debugEnabled)
        {
            if(strstr($obj, 'result (object dump):') || strstr($obj, 'result (serialized):'))
            {
                // empty replies from API
                if(strlen(trim($obj)) == strlen('result (serialized):'))
                {
                    $this->_errorLog->log('[request: '.self::$requestNumber.'] [Empty result from API] [Request URI: '.$curl.']' , Kms_Log::ERR);
                }
                
                
                // try to catch error and log 
                $xmlIdx = strpos($obj, '<xml>');
                if($xmlIdx)
                {
                    $xmlStr = substr($obj, $xmlIdx, strlen($obj));
                    $xmlObj = new SimpleXMLElement($xmlStr);
                    $xpath = $xmlObj->xpath('//*/error');
                    if(count($xpath))
                    {
                        if(isset($xpath[0]) && isset($xpath[0]->code))
                        {
                            $code = $xpath[0]->code;
                            $message = isset($xpath[0]->message) ? $xpath[0]->message : '';
                            $errStr = '[' . $code . ( $message ? ' - ' . $message : '') . '] ['.$obj.']';
                            if($curl)
                            {
                                $errStr .= ' [Request URI: '.$curl.']';
                            }
                            $this->_errorLog->log($errStr , Kms_Log::ERR);
                            
                        }
                    }
                    //exit;
                    
                }
                
                // remove line breaks from within XML ( so we get the full result when grepping )
                if(strstr($obj, 'result (serialized):'))
                {
                    $obj = str_replace("\n", "", $obj);
                }
                
                $this->_log->log('[request: '.self::$requestNumber.'] '.$obj, Zend_Log::DEBUG);
            }
            else
            {
                $this->_log->log('[request: '.self::$requestNumber.'] '.$obj, Zend_Log::INFO);
            }
        }
    }
}
