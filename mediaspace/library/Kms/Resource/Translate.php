<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of Translate
 *
 * @author leon
 */
class Kms_Resource_Translate extends Zend_Application_Resource_ResourceAbstract
{
    function init()
    {
        // get options from bootstrap;
        $bootstrapOptions = $this->getBootstrap()->getOptions();
        //get options for the resource plugin
        $options = $this->getOptions();        
        
        $locale = Kms_Resource_Config::getConfiguration('application', 'language'); //
        if(!$locale)
        {
            $locale = $bootstrapOptions['resources']['locale']['default'];
        }
            
        
        $cache = Kms_Resource_Cache::getTranslateCache();
        if($cache)
        {
            Zend_Translate::setCache($cache);
        }
        
        $contentPath = join(DIRECTORY_SEPARATOR, array(
            $options['kms'],
            $locale,
            'default.mo',
        ));
        
        $translate = new Zend_Translate(
            array(
                'adapter' => 'gettext',
                'content' =>  $contentPath,
                'locale'  => $locale
            )
        ); 
        Zend_Registry::set('Zend_Translate', $translate);
        
        $this->initValidatorTranslation();
    }
    
    
    function initValidatorTranslation()
    {
        // get options from bootstrap;
        $bootstrapOptions = $this->getBootstrap()->getOptions();
        //get options for the resource plugin
        $options = $this->getOptions();        
        $locale = Kms_Resource_Config::getConfiguration('application', 'language'); 
        if(!$locale)
        {
            $locale = $bootstrapOptions['resources']['locale']['default'];
        }
        
        $contentPath = join(DIRECTORY_SEPARATOR, array(
            $options['validator'],
        ));
        
        $translate = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' =>  $contentPath,
                'locale'  => $locale,
                'scan' => Zend_Translate::LOCALE_DIRECTORY
            )
        ); 
        Zend_Validate_Abstract::setDefaultTranslator($translate);
    }
}

?>
