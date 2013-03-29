<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Channelsettings_IndexController extends Kms_Module_Controller_Abstract
{
    /**
     * show a list of channel managers
     */
    public function managersAction()
    {
        $model = Kms_Resource_Models::getChannel();
        
        if($model->Category) {
            $this->view->channelName = $model->Category->name;
            $this->view->managers = $model->getChannelUsers($model->Category, Application_Model_Channel::CHANNEL_USER_TYPE_MANAGER,array('pagesize' => 3));
        }
    }
    
    /**
     * add 'channel settings' and 'add media' buttons - according to the contextual role.
     */
    public function buttonAction()
    {
        $model = Kms_Resource_Models::getChannel();
        
        if($model->Category) {
        	$this->view->channelId = $model->Category->id;
        	$this->view->channelName = $model->Category->name;
        	
            // get the role for this user on this channel
            $role = $model->getUserRoleInChannel($model->Category->name, Kms_Plugin_Access::getId(), $model->Category->id);
            
            // channel settings
            if ($role <= Kaltura_Client_Enum_CategoryUserPermissionLevel::MODERATOR){
                $this->view->showSettings = true;
            }
            
            // add media
            if ($model->Category->membership == Application_Model_Category::MEMBERSHIP_OPEN || 
                $role <= Kaltura_Client_Enum_CategoryUserPermissionLevel::CONTRIBUTOR){
                $this->view->showButtons = true;
            }
        }
    }
}