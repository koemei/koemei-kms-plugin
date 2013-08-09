<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * 
 * Plugin for controlling themes
 * author: gonen
 * 
 */

class Kms_Plugin_Theme extends Zend_Controller_Plugin_Abstract
{

    private static $_theme = false;
    private static $_themeFullPath = false;
    private static $_baseLayoutPath = false;
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        $themeFolderName = Kms_Resource_Config::getConfiguration('application', 'theme');

        if ($themeFolderName && $themeFolderName != 'default' && $front->getRequest()->getControllerName() != 'admin')
        {
            // check in default themes
            $themes_path = 'themes';
            $appThemeFolderPath = THEMES_PATH . DIRECTORY_SEPARATOR . $themeFolderName;
            $publicThemeFolderPath = APPLICATION_PATH . '..'. DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeFolderName;

            if (!is_dir($appThemeFolderPath) && !is_dir($publicThemeFolderPath))
            {
                // check in custom themes
                $themes_path = 'themesCustom';
                $appThemeFolderPath = CUSTOM_THEMES_PATH . DIRECTORY_SEPARATOR . $themeFolderName;
                $publicThemeFolderPath = APPLICATION_PATH . '..'. DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeFolderName;
                
                if (!is_dir($appThemeFolderPath) && !is_dir($publicThemeFolderPath))
                {
                    Kms_Log::log("init: Theme folder '$themeFolderName' does not exist", Kms_Log::ERR);
                    throw new Zend_Exception("Theme folder '$themeFolderName' does not exist");
                }
                
                // check if custom theme is enabled (saas only)
                if (!Kms_Resource_Config::isThemeAvailable($themeFolderName))
                {
                    Kms_Log::log("init: Theme '$themeFolderName' is disabled", Kms_Log::ERR);
                    //throw new Zend_Exception("Theme '$themeFolderName' is disabled"); show an error
                    return; // show the default theme    
                }
            }

            self::$_theme = $themes_path . DIRECTORY_SEPARATOR . $themeFolderName;
            self::$_themeFullPath = $appThemeFolderPath;

            $bootstrap = $front->getParam('bootstrap');
            $view = $bootstrap->bootstrap('View');
            $viewResource = $view->getResource('View');
            $viewResource->addBasePath($appThemeFolderPath . DIRECTORY_SEPARATOR .  'views');
            $viewResource->addBasePath($appThemeFolderPath . DIRECTORY_SEPARATOR .  'layouts');
            $viewResource->addHelperPath($appThemeFolderPath . DIRECTORY_SEPARATOR . 'views/helpers', 'Kms_Theme_Helper');
            self::$_baseLayoutPath = Zend_Layout::getMvcInstance()->getLayoutPath();
        }
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        
        if(self::getThemeFolderName())
        {
            // add theme script path
            $front = Zend_Controller_Front::getInstance();

            $themeLayoutPath = self::getThemeFullPath() . DIRECTORY_SEPARATOR .'layouts' .DIRECTORY_SEPARATOR .'scripts' . DIRECTORY_SEPARATOR;

            $bootstrap = $front->getParam('bootstrap');
            $layoutInstance = Zend_Layout::getMvcInstance();
            
            //$layoutResource = $layout->getResource('Layout');
            $layoutName = $layoutInstance->getLayout();
            
            if (file_exists($themeLayoutPath . DIRECTORY_SEPARATOR. $layoutName .'.phtml'))
            {
                $layoutInstance->setLayoutPath($themeLayoutPath);
            }
            else
            {
                $layoutInstance->setLayoutPath(self::$_baseLayoutPath);
            }
        }
    }
    
    public static function getThemeFolderName()
    {
        return self::$_theme;
    }
    
    public static function getThemeFullPath()
    {
        return self::$_themeFullPath;
    }
}