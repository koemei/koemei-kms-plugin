<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Oembed_IndexController extends Kms_Module_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
        /* initialize context switching */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        if(!$contextSwitch->getContext('ajax'))
        {
            $ajaxC = $contextSwitch->addContext('ajax', array());
            $ajaxC->setAutoDisableLayout(false);
        }
        
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->addActionContext('oembed', 'ajax')->initContext();
        $contextSwitch->addActionContext('grab', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function indexAction()
    {
        $this->view->enableCustomization = Kms_Resource_Config::getModuleConfig('oembed', 'enableCustomization');
        
        
        // action body
    }
    
    public function oembedAction()
    {
        //@todo fix this
        $request = $this->getRequest();
        $embed = $request->getParam('embed');
        
        $entryId = is_array($embed) && isset($embed['entryId']) ? $embed['entryId'] : NULL;
        if($entryId)
        {
            $entryModel = Kms_Resource_Models::getEntry();
            $entryModel->get($entryId, false);
            
        }
        
        
        if($entryModel->entry instanceof Kaltura_Client_Type_MediaEntry)
        {
            // get the request
            $this->view->enableCustomization = Kms_Resource_Config::getModuleConfig('oembed', 'enableCustomization');


            // check if size is set
            if(isset($embed['size']) && $this->view->enableCustomization)
            {
                list($height, $width) = explode('x', $embed['size']);
            }
            else
            {
                // get the size from the config
                $height = Kms_Resource_Config::getModuleConfig('oembed', 'height');
                $width = Kms_Resource_Config::getModuleConfig('oembed', 'width');
            }

            if(isset($embed['player']) && $this->view->enableCustomization)
            {
                $playerId = $embed['player'];
            }
            else
            {
                $playerId = Kms_Resource_Config::getModuleConfig('oembed', 'playerId');
                if($playerId == 'default')
                {
                    $playerId = Kms_Resource_Config::getConfiguration('player', 'playerId');
                }
            }

            // parse width and height
            list($width, $height) = explode('x', $embed['size']);

            $entryId = $embed['entryId'];

            $this->view->oembedLink = $this->view->serverUrl() .$this->view->baseUrl('/id/'.$entryId);
            if($this->view->enableCustomization)
            {
                $this->view->size = $width.'x'.$height;
                $this->view->oembedLink .= '?width='.$width.'&height='.$height.'&playerId='.$playerId;
            }
        }
        else
        {
            return;
        }
        
    }
    
    public function grabAction()
    {
        //$this->_helper->contextSwitch()->initContext('ajax');
        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();
        
        $entryId = $request->getParam('entryId');
        if(!$entryId)
        {
            $entryUrl = $request->getParam('url');
            // get the entry id from the url
            preg_match('#/id/([^/|\?]+)#', $entryUrl, $matches);
            if(count($matches) && isset($matches[1]))
            {
                $entryId = $matches[1];
            }            
            
            // parse the entry url (comes encoded from the consumer)
            $entryRequest = new Zend_Controller_Request_Http($entryUrl);
            $width = $entryRequest->getParam('width') ? $entryRequest->getParam('width') : Kms_Resource_Config::getModuleConfig('oembed', 'width');
            $height = $entryRequest->getParam('height') ? $entryRequest->getParam('height') : Kms_Resource_Config::getModuleConfig('oembed', 'height');
            $playerId = $entryRequest->getParam('playerId');
            if(!$playerId)
            {
                if(Kms_Resource_Config::getModuleConfig('oembed', 'playerId') == 'default')
                {
                    $playerId = Kms_Resource_Config::getConfiguration('player', 'playerId');
                }
                else
                {
                    $playerId = Kms_Resource_Config::getModuleConfig('oembed', 'playerId');
                }
            }
            
            
        }
        
        if($entryId)
        {
        
            $entryModel = Kms_Resource_Models::getEntry();          
            $entry = $entryModel->get($entryId, false);
            $this->view->oembed = array(
                'entryId' => $entryId,
                'version' => '1.0',
                'type' => 'video',
                'provider_url' => $this->view->serverUrl().'/'.$this->view->baseUrl(),
                'provider_name' => Kms_Resource_Config::getConfiguration('application', 'title'),
                'title' => $entry->name,
               
                'width' => $width,
                'height' => $height,
//                'size' => $width.'x'.$height,
                'playerId' => $playerId,
                'thumbnail_height' => $height,
                'thumbnail_width' => $width,
                'thumbnail_url' =>  $entry->thumbnailUrl.
                                    '/width/' . $width .
                                    '/height/' . $height ,
            );
            
            $embedObj = array(
                'entryId' => $entryId,
                'player' => $playerId,
                'size' => $width.'x'.$height,
            );
            
            $authorName = Kms_View_Helper_String::getAuthorNameFromTags($entry->tags);
            if($authorName)
            {
                $this->view->oembed['author_name'] = $authorName;
            }
            header('Content-Type: application/json');
            $this->view->oembed['html'] = $this->view->action('embed', 'index', 'embed', array('format' => '', 'embed' => $embedObj));
        }
    }
}

