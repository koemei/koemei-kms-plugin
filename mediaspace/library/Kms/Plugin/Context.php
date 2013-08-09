<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Front Controller plugin for managing view contexts (json,ajax,xml,html) etc..
 *
 * @author leon
 */
class Kms_Plugin_Context extends Zend_Controller_Plugin_Abstract
{
    private static $headerSet = false;
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Layout::getMvcInstance();
        if (null !== $layout && $layout->isEnabled()) 
        {
            $context = $request->getParam('format');
            if (null !== $context) 
            {
                $layout->setLayout($context);
            }
        }

	// set the custom header for the script's unique id
        if(!self::$headerSet)
        {
            $this->getResponse()->setHeader('kms-unique-id', UNIQUE_ID);	
            self::$headerSet = true;
        }
    }
}
