<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Advanced Channel Settings Module Controller
 *
 */
class Channelsettingsadvanced_IndexController extends Kms_Module_Controller_Abstract
{
    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('update', 'ajax')->initContext();
    
        $this->_helper->contextSwitch()->initContext();
        $this->view->tabName = 'advanced';

    }
    
    /**
     * the advanced setting tab content
     */
    public function indexAction()
    {   
        // get the channel
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->Category;
                      
        if(!empty($channel))
        {
            if($this->getRequest()->getParam('tab') == $this->view->tabName)
            {
                // get the thumbnails
                $thumbnails = $model->getChannelThumbnails(array($channel));
                if (!empty($thumbnails) && !empty($thumbnails[$channel->id])){
                    $channel->thumbnails = $thumbnails[$channel->id];
                }

                $this->view->channel = $channel;
            }
            else
            {
                $this->_helper->viewRenderer->setNoRender(TRUE);
            }
        }        
    }   
    
    /**
     * add an advanced settings tab link
     */
    public function tabAction()
    {
        // get the channel
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->Category;
        $this->view->tabActive = $this->getRequest()->getParam('tab') == $this->view->tabName;
                
        if(!empty($channel))
        {     
            $this->view->channelName = $channel->name;
            $this->view->channelId = $channel->id;
        }
        $this->view->tabActive = $this->getRequest()->getParam('tab') == $this->view->tabName;
    }
    
    /**
     * update channel thumbnails urls action
     */
    public function updateAction()
    {       
        // get the channel
        $model = Kms_Resource_Models::getChannel();
        $channel = $model->get(null, false, $this->getRequest()->getParam('channelid'));
        
        if(!empty($channel))
        {
            // create new channel thumbnails
            $model->createChannelThumbnails($channel);
            
            // get the thumbnails
            $thumbnails = $model->getChannelThumbnails(array($channel));
            if (!empty($thumbnails) && !empty($thumbnails[$channel->id])){
                $channel->thumbnails = $thumbnails[$channel->id];
            }
            
            $this->view->channel = $channel;
        }
    }
}