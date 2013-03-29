<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of Debug
 *
 * @author leon
 */
class Kms_Resource_Debug extends Zend_Application_Resource_ResourceAbstract
{

    public function init()
    {
        
    }

    //put your code here
    static public function isEnabled()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        // if environment is development and debug bar is enabled , or the cookie is enabled
        if (APPLICATION_ENV == 'development' && Kms_Resource_Config::getConfiguration('debug', 'enableDebugBar') || $request->getCookie(Kms_Resource_Config::DEBUG_COOKIE_NAME) == self::generateCookieKey())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static public function setCookie()
    {
        $key = self::generateCookieKey();
        setcookie(Kms_Resource_Config::DEBUG_COOKIE_NAME, $key, null, '/');
    }

    static public function generateCookieKey()
    {
        $partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');
        $adminSecret = Kms_Resource_Config::getConfiguration('client', 'adminSecret');
        $key = substr(base64_encode(md5($partnerId . ':' . $adminSecret)), 1, 20);
        return $key;
    }

    static public function xmlpp($xml, $html_output=false)
    {
        $xml_obj = new SimpleXMLElement($xml);
        $level = 4;
        $indent = 0; // current indentation level  
        $pretty = array();

        // get an array containing each XML element  
        $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

        // shift off opening XML tag if present  
        if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
        {
            $pretty[] = array_shift($xml);
        }

        foreach ($xml as $el)
        {
            if (preg_match('/^<([\w])+[^>\/]*>$/U', $el))
            {
                // opening tag, increase indent  
                $pretty[] = str_repeat(' ', $indent) . $el;
                $indent += $level;
            }
            else
            {
                if (preg_match('/^<\/.+>$/', $el))
                {
                    $indent -= $level;  // closing tag, decrease indent  
                }
                if ($indent < 0)
                {
                    $indent += $level;
                }
                $pretty[] = str_repeat(' ', $indent) . $el;
            }
        }
        $xml = implode("\n", $pretty);
        return ($html_output) ? htmlentities($xml) : $xml;
    }
}

