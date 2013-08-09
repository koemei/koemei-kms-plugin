<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Embedplaylist_IndexController extends Kms_Module_Controller_Abstract
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
        $playlistModel = Kms_Resource_Models::getPlaylist();
        $playlistId = $playlistModel->getId();

        $this->view->playlistId = $playlistId;
        
        // parse the embed colors (skins)
        $embedColors = array();
        $isFirstColor = 1;
        $embedSkins = Kms_Resource_Config::getModuleConfig('embedplaylist','embedSkins');
        if ($embedSkins) 
        {
            foreach ($embedSkins as $key => $embedUiConfId) 
            {
                if ($isFirstColor) 
                {
                    $defaultEmbedPlayerId = $embedUiConfId;
                    $isFirstColor = 0;
                    
                }
                $embedColors[$key] = $embedUiConfId;
            }
            
            $this->view->embedColors = $embedColors;
        } 
        else 
        {
            $message = "Embed exception: You must have playlist embed color details in modules.ini";
            Kms_Log::log('embedplaylist: '.$message, Kms_Log::ERR);
            throw new Zend_Exception($message, 500);
        }
        
        // parse the embed sizes
        $playlistEmbedSizes = Kms_Resource_Config::getModuleConfig('embedplaylist','embedSizes');
        if ($playlistEmbedSizes) 
        {
            $this->view->embedSizes = array();
            foreach ($playlistEmbedSizes as $key => $fullSize) 
            {
                $parts = explode("x", $fullSize);
                $this->view->embedSizes[$key] = array('fullSize' => $fullSize, 'width' => $parts[0], 'height' => $parts[1]);
            }
        } 
        else 
        {
            $message = "Embed exception: You must have playlist embed sizes in the config file";
            Kms_Log::log('embedplaylist: '.$message, Kms_Log::ERR);
            throw new Zend_Exception($message, 500);
        }
        
        // check if this user is allowed to get an embed code
        $embedModel = new Embedplaylist_Model_Embedplaylist();
        $playlist = $playlistModel->get($playlistId);
        $this->view->allowEmbed = $embedModel->checkAllowed($playlist);        
    }

    
    public function embedAction()
    {
        // get the request
        $request = $this->getRequest();
        $embed = $request->getParam('embed');
        $playlistId = $request->getParam('playlistId');
        
        // get the parameter for the first time the embed button is pressed
        if (isset($playlistId))
        {
            $embed['playlistId'] = $playlistId;
        }
        
        // check if size is set
        if(!isset($embed['size']))
        {
            // get the default horizontal size
            $embed['size'] = 'horizontal';
        }
        
        if(!isset($embed['player']))
        {
            $embed['player'] = 'dark';
        }
        
        $embedSizes = Kms_Resource_Config::getModuleConfig('embedplaylist', 'embedSizes');
        if(isset($embedSizes->{$embed['size']}))
        {
            $size = $embedSizes->{$embed['size']};
        }
        else
        {
            $size = '640x400';
        }
        // parse width and height
        list($width, $height) = explode('x', $size);
        
        
        // try to get the entry if already in the model
        $playlistModel = Kms_Resource_Models::getPlaylist();
        
        if($playlistModel->getId())
        {
            $playlistId = $playlistModel->getId();
            $playlist = $playlistModel->playlist;          
        }
        elseif(isset($embed['playlistId']) && $embed['playlistId'])
        {
            $playlistId = $embed['playlistId'];
            // api request to get the model
            $playlist = $playlistModel->get($playlistId);
        }
        $this->view->playlistId = $playlistId;
        

        // assign view variables
        $this->view->player = $embed['player'];
        $this->view->size = $embed['size'];

        $uiconfsConfig = Kms_Resource_Config::getModuleConfig('embedplaylist', 'embedSkins');
        $configKey = $this->view->player.'_'.$this->view->size;
        
        if(isset($uiconfsConfig->{$configKey}))
        {
            $uiConfId = $uiconfsConfig->{$configKey};
        }
        
        // assign rtmp flash vars
        $flashVars = '';
        if(Kms_Resource_Config::getConfiguration('player', 'playback') == 'RTMP')
        {
            $flashVars .= 'streamerType=rtmp&amp;streamerUrl='.Kms_Resource_Config::getConfiguration('player', 'rtmpHost');
        }
        
        
        // check if this user is allowed to get an embed code
        $embedModel = new Embedplaylist_Model_Embedplaylist();
        $allowEmbed = $embedModel->checkAllowed($playlist);
        if (!$allowEmbed){
            // do not render the view
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
        
        // generate the widget with entitlement off
        if (empty($embed['widgetId']))
        {
            // generate the widget from the api            
            $this->view->widgetId = $embedModel->getWidgetId($embed['playlistId']);
        }
        else
        {
            // we already have the widget - just the uiconf changed
            $this->view->widgetId = $embed['widgetId'];
        }
        
        // parse the template and insert/replace variables for the embed code
        
        $duration = isset($playlist->duration) ? $playlist->duration : '';

        $this->view->embedParams = array(
            'UID' => uniqid(),
            'HEIGHT' => $height,
            'WIDTH' => $width,
            'MEDIA' => 'video',
            'HOST' => Kms_Resource_Config::getConfiguration('client', 'serviceUrl'),
            'PARTNER_ID' => Kms_Resource_Config::getConfiguration('client', 'partnerId'),
            'WIDGET_ID' => $this->view->widgetId,    
            'UICONF_ID' => $uiConfId,
            'PLAYLIST_ID' => $embed['playlistId'],
            'FLASHVARS' => $flashVars,
            'FLAVOR' => '',
            'THUMBNAILURL' => $playlist->thumbnailUrl,
            'DESCRIPTION' => $this->view->String()->shortenDescription($playlist->description),
            'PL_NAME' => $playlist->name,
            'NAME' => $playlist->name,
            'DURATION' => Kms_View_Helper_String::formatDuration($duration),
        );
        $this->view->uiConfId = $uiConfId;
    }

}



