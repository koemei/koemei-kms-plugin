<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class PlaylistController extends Zend_Controller_Action
{

    private $_translate = null;
    
    public function init()
    {
        /* init translator */
        $this->_translate = Zend_Registry::get('Zend_Translate');        
        
        /* initialize flashMessenger */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');  
        $this->_flashMessenger->setNamespace('default');
        $this->view->messages = $this->_flashMessenger->getMessages();
        
        /* Initialize contexts here */
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        
        $ajaxC = $contextSwitch->setContext('ajax', array());
        $ajaxC->setAutoDisableLayout(false);

        $dialogC = $contextSwitch->setContext('dialog', array());
        $dialogC->setAutoDisableLayout(false);

        $scriptC = $contextSwitch->setContext('script', array());
        $scriptC->setAutoDisableLayout(false);
        
        
        $contextSwitch->setSuffix('dialog', 'dialog');
        $contextSwitch->setSuffix('ajax', 'ajax');
        $contextSwitch->setSuffix('script', 'script');
        $contextSwitch->addActionContext('set-private', 'dialog')->initContext();
        $contextSwitch->addActionContext('delete', 'dialog')->initContext();
        $contextSwitch->addActionContext('update-entries', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();

    }

    public function indexAction()
    {
        // action body
    }

    
    public function updateEntriesAction()
    {
        $playlistId = $this->getRequest()->getParam("id");
        $entriesArray = explode(',', $this->getRequest()->getParam('entries'));
        
        if($playlistId && count($entriesArray))
        {
            $playlistModel = new Application_Model_Playlist();
            if($playlistModel->updatePlaylist($playlistId, $entriesArray))
            {
                $this->view->JsonFlashMessage($this->_translate->translate('Playlist successfully updated').'.');  
                $this->view->JsonScript('$("#mp_wrap #bulk_actions button.save").attr("disabled","disabled").html("'.$this->_translate->translate('Saved').'");');
            }
            else
            {
                $this->view->JsonFlashMessage($this->_translate->translate('The playlist could not be updated').'.');  
            }
        }
        $this->_forward('my-playlists', 'user');
        
        
    }
    
    public function deleteAction()
    {
        
        // get the entry from the entry id requested
        $id = $this->getRequest()->getParam('id');
        $this->view->id = $id;
        
        $playlistModel = new Application_Model_Playlist();
        
        $playlist = $playlistModel->get($id);
        $this->view->playlist = $playlist;
        // check if the user is allowed to delete
        if(Kms_Plugin_Access::isCurrentUser($playlist->userId))
        {
            $this->view->allowed = true;
            // check if confirmation was sent
            if($this->getRequest()->getParam('confirm') == '1')
            {
                $this->view->confirmed = true;
                if($playlistModel->delete($playlist->id))
                {
                    $this->_flashMessenger->addMessage($this->_translate->translate('Successfully deleted').' "'.$playlist->name.'"');  
                }
            }
            else
            {
                $this->view->confirmed = false;
            }
        }
        else
        {
            $this->view->allowed = false;
        }
    }

}

