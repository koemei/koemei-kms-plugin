<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Channel Topics Controller
 * 
 * @author talbone
 *
 */
class Channeltopics_IndexController extends Kms_Module_Controller_Abstract
{   
    const MODULE_NAME = 'channeltopics';
    
    
    /**
     * render a navigation sidebar 
     */
    public function sidenavAction()
    {           
        
        // get the available topics
        $customdataProfileId = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'profileId');
        $topicField = Kms_Resource_Config::getModuleConfig(self::MODULE_NAME, 'topicField');
        $model = new Channeltopics_Model_Channeltopics();
            
        $this->view->topics = $model->getAvailableTopics($customdataProfileId, $topicField); 
        $this->view->currentTopic = $this->getRequest()->getParam('topic');             
    }
    
    /**
     * render a title to the channel page
     */
    public function channeltitleAction()
    {
        $model = Kms_Resource_Models::getChannel();
        $this->view->noOfChannels = $model->getTotalCount();
        $this->view->currentTopic = $this->getRequest()->getParam('topic');
    }
    
    /**
     * return the current topic for the search keyword
     */
    public function searchkeywordAction()
    {
        $this->view->currentTopic = $this->getRequest()->getParam('topic');
    }
}