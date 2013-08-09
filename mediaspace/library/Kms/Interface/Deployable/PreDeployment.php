<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface to for modules to performs checks before deployment - if they can be installed or enabled.
 * this in addition to any checks that KMS might do by itself.
 * Implement this interface if your module should be installed/enabled only if certain conditions are met - 
 * for example, if certain features are enabled for the partner.
 *
 * @author talbone
 */
interface Kms_Interface_Deployable_PreDeployment
{
	/**
     * can the implementing module be installed
     * @return boolean
     */
	function canInstall();
	
    /**
     * can the implementing module be enabled
     * @return boolean
     */
	function canEnable();

    /**
     * the deployment failure reason.
     * @return string the failure reason.
     */
    function getPreDeploymentFailReason();
}
