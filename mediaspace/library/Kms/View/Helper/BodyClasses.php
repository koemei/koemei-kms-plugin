<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Time Elapsed view helper. Just translated the time elapsed, no extra "ago" or anything else.
 *
 * @author talbone
 *
 */
class Kms_View_Helper_BodyClasses extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function BodyClasses()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        $deviceClass = $this->view->Device()->getDevice();
        
        $classes = array(
            'module-'.$request->getModuleName(),
            'controller-'.$request->getControllerName(),
            'action-'.$request->getActionName(),
        );
        if($deviceClass)
        {
            $classes[] = $deviceClass;
        }
        
        $bodyClasses = implode(' ', $classes);

        return $bodyClasses;
    }
}