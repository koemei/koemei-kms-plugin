<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Contextual Role access for categories - galleries and channels.
 *
 * @author leon
 */
class Kms_Module_Contextual_Access_Channel extends Kms_Module_Contextual_Access
{
    private $requiredRole;
    
    /**
     * constructor
     *
     * @param Kaltura_Client_Enum_CategoryUserPermissionLevel $requiredRole
     * @param array $requestParams
     * @param bool $allowRedirect
     * @param array $accessDeniedPage 
     */
    public function __construct( $requiredRole, array $requestParams, $allowRedirect = true, $accessDeniedPage = null)
    {
        $this->requiredRole = $requiredRole;
        $this->requestParams = $requestParams;        
        $this->allowRedirect = $allowRedirect;  
        $this->accessDeniedPage = $accessDeniedPage;
    }
    
    /**
     * (non-PHPdoc)
     * @see Kms_Module_Contextual_Access::checkAllowed()
     */
    public function checkAllowed(array $contextId)
    {
    	$channelId = (!empty($contextId['channelid'])) ? $contextId['channelid'] : '';
        $role = $this->getChannelRole($contextId['channelname'], $channelId);
        if (!is_null($role) && $role <= $this->requiredRole) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * get the contextual role for the channel
     * @param unknown_type $channelName
     * @param unknown_type $channelId
     * @return integer the contextual role
     */
    private function getChannelRole($channelName, $channelId)
    {
        $role = NULL;
        $userId = Kms_Plugin_Access::getId();
        if (!is_null($channelName) && !is_null($userId)){
            // get the contextual role by the category and user
            $role = Kms_Resource_Models::getChannel()->getUserRoleInChannel($channelName, $userId, $channelId);
        }
        return $role;
    } 
    
    /**
     * get the access denied page
     * @see Kms_Module_Contextual_Access::getAccessDeniedPage()
     */
    public function getAccessDeniedPage()
    {
        if(empty($this->accessDeniedPage))
        {
            return array('controller' => 'channels', 'action' => 'denied', 'module' => 'default');
        }
        else if (is_array($this->accessDeniedPage))
        {
            return $this->accessDeniedPage;
        }
        else
        {
            return null;
        }
    }
    
}

