<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * This interface is used to declare dependency on another module
 * If your module depends on another module, you should implement this interface, and the getModuleDependency static method
 * If one of those modules is disabled or missing, then your module will be disabled as well
 *
 * @author leon
 */
interface Kms_Interface_Model_Dependency
{
    /**
     * The static method should return an array of module names
     * @return array An array of module names
     */
    public static function getModuleDependency();
}

