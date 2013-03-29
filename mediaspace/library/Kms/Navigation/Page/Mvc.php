<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Kms_Navigation_Page_Mvc extends Zend_Navigation_Page_Mvc
{
	
	private $optionalParam;
	
	public function __construct($options = null)
    {
    	parent::__construct($options);
    	if (is_array($options) && isset($options['optionalparam'])) {
    		$this->optionalParam = $options['optionalparam'];
    	}
    }
	
	
	/**
     * Returns whether page should be considered active or not
     *
     * This method will compare the page properties against the request object
     * that is found in the front controller.
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active or not
     */
    public function isActive($recursive = false)
    {
    	$result = parent::isActive($recursive);  
    	if (!$result && !empty($this->optionalParam)) {
    		$myParams = $this->_params;
    		if (isset($myParams[$this->optionalParam])) {
	    		$myModifiedParams = $myParams;
    			unset($myModifiedParams[$this->optionalParam]);
    			$this->setParams($myModifiedParams);
    			$result = parent::isActive($recursive);
    			$this->setParams($myParams);
    		}
    	}
   		return $result;
    }
}