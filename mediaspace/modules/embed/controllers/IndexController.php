<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Embed_IndexController extends Kms_Module_Controller_Abstract
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
        $contextSwitch->addActionContext('embed', 'ajax')->initContext();
    }

    public function indexAction()
    {
        // get the entry
        $entry = Kms_Resource_Models::getEntry();
        if(isset($entry->entry) && isset($entry->entry->id))
        {
            $this->view->entryId = $entry->entry->id;
            $this->view->entryName = $entry->entry->name;
            $this->view->isPresentation = $entry->entry->type == Kaltura_Client_Enum_EntryType::DATA && in_array('presentation', explode(',', $entry->entry->adminTags));
            // parse the embed colors (skins)
            $embedColors = array();
            $isFirstColor = 1;
            $embedSkins = Kms_Resource_Config::getModuleConfig('embed','embedSkins');
            if ($embedSkins) 
            {
                foreach ($embedSkins as $details) 
                {
                    if ($isFirstColor) 
                    {
                        $defaultEmbedPlayerId = $details->uiConfId;
                        $isFirstColor = 0;
                        
                    }
                    $embedColors[$details->name] = array("playerId" => $details->uiConfId, "image" => $details->imgFile);
                }
                
                $this->view->embedColors = $embedColors;
            } 
            else 
            {
                $message = "Embed exception: You must have entry embed color details in modules.ini";
                Kms_Log::log('embed: '.$message, Kms_Log::ERR);
                throw new Zend_Exception($message, 500);
            }
            
            // parse the embed sizes
            $entryEmbedSizes = Kms_Resource_Config::getModuleConfig('embed','embedSizes');
            if ($entryEmbedSizes) 
            {
                $this->view->embedSizes = array();
                foreach ($entryEmbedSizes as $key => $fullSize) 
                {
                    $parts = explode("x", $fullSize);
                    $this->view->embedSizes[$key] = array('fullSize' => $fullSize, 'width' => $parts[0], 'height' => $parts[1]);
                }
            } 
            else 
            {
                $message = "Embed exception: You must have entry embed sizes in the config file";
                Kms_Log::log('embed: '.$message, Kms_Log::ERR);
                throw new Zend_Exception($message, 500);
            }
            
            // check if this user is allowed to get an embed code
            $embedModel = new Embed_Model_Embed();
            $this->view->allowEmbed = $embedModel->checkAllowed($entry->entry);
        }
        else {
            return null;
        }
    }

    public function tabAction()
    {
        $entry = Kms_Resource_Models::getEntry();
        // action body
    }

    
    public function embedAction()
    {        
        // get the request
        $request = $this->getRequest();
        $embed = $request->getParam('embed');
        $entryId = $request->getParam('entryId');
        
        // get the parameter for the first time the embed button is pressed
        if (isset($entryId))
        {
            $embed['entryId'] = $entryId;
        }
        
        // check if size is set
        if(!isset($embed['size']))
        {
            // get the default medium size
            $embedSizes = Kms_Resource_Config::getModuleConfig('embed', 'embedSizes');
            $embed['size'] = $embedSizes->medium;
        }
        
        $entryModel = Kms_Resource_Models::getEntry();
        
        // try to get the entry if already in the model
        if(isset($entryModel->entry->name) && $entryModel->entry->name)
        {
            $entry = $entryModel->entry;
        }
        elseif(isset($embed['entryId']) && $embed['entryId'])
        {
            // api request to get the model
            $entry = $entryModel->get($embed['entryId'], false);
        }
       
        
        // check if this user is allowed to get an embed code
        $embedModel = new Embed_Model_Embed();
        $allowEmbed = $embedModel->checkAllowed($entry);
        if (!$allowEmbed){
            // do not render the view
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
        
        // parse width and height
        $this->view->isPresentation = false;
        $isPresentation = $entry->type == Kaltura_Client_Enum_EntryType::DATA && in_array('presentation', explode(',', $entry->adminTags));
        if($isPresentation)
        {
            $this->view->isPresentation = true;
            $width = '900';
            $height = '469';
            $embed['size'] = $width.'x'.$height;
            $embed['player'] = Kms_Resource_Config::getConfiguration('player', 'kpwId');
            
            // retrieve the entryid for the thumbnail (the media entry)
            $data = $entry->dataContent;
            $dataXml = new SimpleXMLElement($data);
//            $mediaEntryId = $dataXml->video->entryId->__toString();
            $mediaEntryId = (string) $dataXml->video->entryId;
            // change the thumbnail URL to include the entry Id of the media (video) entry
            $entry->thumbnailUrl = preg_replace('/\/entry_id\/([^\/])+/', '/entry_id/'.$mediaEntryId, $entry->thumbnailUrl);
        }
        else
        {
            list($width, $height) = explode('x', $embed['size']);

            // get the default skin, if not set
            if(!isset($embed['player']))
            {
                $embedSkins = Kms_Resource_Config::getModuleConfig('embed','embedSkins')->toArray();
                $skin = array_shift($embedSkins);
                $embed['player'] = $skin['uiConfId'];

            }
        }
        // assign view variables
        $this->view->player = $embed['player'];
        $this->view->size = $embed['size'];
        
        $flashVars = '';
        
        // assign presentation flash vars
        if($isPresentation)
        {
            $flashVars .= 'videoPresentationEntryId='.$entry->id.'&amp;';
        }
        
        // assign rtmp flash vars
        if(Kms_Resource_Config::getConfiguration('player', 'playback') == 'RTMP')
        {
            $flashVars .= 'streamerType=rtmp&amp;streamerUrl='.Kms_Resource_Config::getConfiguration('player', 'rtmpHost');
        }
        
        
        // generate the widget with entitlement off
        
        if (empty($embed['widgetId']))
        {  
            // generate the widget from the api
            $this->view->widgetId = $embedModel->getWidgetId($embed['entryId'], $this->view->player);
        }
        else 
        {
            // we already have the widget - just the uiconf changed
            $this->view->widgetId = $embed['widgetId'];
        }
        
        // parse the template and insert/replace variables for the embed code
        
        $duration = isset($entry->duration) ? $entry->duration : '';
        
        $this->view->embedParams = array(
            'UID' => uniqid(),
            'HEIGHT' => $height,
            'WIDTH' => $width,
            'MEDIA' => 'video',
            'HOST' => Kms_Resource_Config::getConfiguration('client', 'serviceUrl'),
            'WIDGET_ID' => $this->view->widgetId,
            'UICONF_ID' => $embed['player'],
            'ENTRY_ID' => $embed['entryId'],
            'FLASHVARS' => $flashVars,
            'FLAVOR' => '',
            'THUMBNAILURL' => $isPresentation ? '' : $entry->thumbnailUrl,
            'DESCRIPTION' => $this->view->String()->shortenDescription($entry->description),
            'NAME' => $entry->name,
            'DURATION' => Kms_View_Helper_String::formatDuration($duration),
        );
        
    }

}



