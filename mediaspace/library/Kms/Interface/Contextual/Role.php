<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * interface to allow modules determine contextual role by channel and user
 * @author talbone
 *
 */
interface Kms_Interface_Contextual_Role
{
    /**
     * this action returns the access rules
     * @param string $actionName - the action name
     * @return Kms_Module_Contextual_Access access role object
     */
    function getContextualAccessRuleForAction($actionName);
}