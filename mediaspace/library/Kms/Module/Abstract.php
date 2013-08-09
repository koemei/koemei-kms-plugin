<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of Abstract
 *
 * @author leon
 */
abstract class Kms_Module_Abstract {
    
    public $moduleName;
    const MODEL_DIR = 'models';

    public function __construct($moduleName)
    {
        if($moduleName)
        {
            $this->moduleName = $moduleName;
            $moduleDir = Zend_Controller_Front::getInstance()
                                                            ->getModuleDirectory($moduleName);
            
            
            $modelFilename = $moduleDir . '/'. self::MODEL_DIR . '/' . ucfirst($moduleName) . '.php';
            
            // include the MODEL file if it exists
            Zend_Loader::isReadable($modelFilename) && Zend_Loader::loadFile($modelFilename);
        }
        
        $this->init();
        
    }
    
    abstract function init();
}
?>
