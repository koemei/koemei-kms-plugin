<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class EntryController extends Zend_Controller_Action
{

    private $_flashMessenger = null;
    private $_translate = null;

    public function init()
    {
        /* initialize translator */
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
        $contextSwitch->addActionContext('add', 'ajax')->initContext();
        $contextSwitch->addActionContext('play', 'ajax')->initContext();
        $contextSwitch->addActionContext('get', 'ajax')->initContext();
        $contextSwitch->addActionContext('edit', 'ajax')->initContext();
        $contextSwitch->addActionContext('save', 'ajax')->initContext();
        $contextSwitch->addActionContext('done', 'ajax')->initContext();
        $contextSwitch->addActionContext('add-entry-form', 'ajax')->initContext();
        $contextSwitch->addActionContext('play', 'script')->initContext();
        $contextSwitch->addActionContext('check-status', 'ajax')->initContext();
        $contextSwitch->addActionContext('like', 'ajax')->initContext();
        $contextSwitch->addActionContext('unlike', 'ajax')->initContext();
        $this->_helper->contextSwitch()->initContext();
    }

    public function indexAction()
    {
        // action body
    }


    /**
     * display the form for editing an entry
     *
     *
     *
     *
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (!is_null($id))
        {// populate the form with the entry
            $model = Kms_Resource_Models::getEntry();
            $model->id = $id;
            $entry = $model->get($id);
            if ($entry)
            {
                Kms_Resource_Models::setEntry($model);
            }
        }
        else
        {
            $entry = new Kaltura_Client_Type_BaseEntry();
        }
        if(Kms_Plugin_Access::isCurrentUser($entry->userId))
        {
        	$this->view->noPlayer = $this->getRequest()->getParam('noplayer') == '1';
            $this->view->editEntryOpen = true;
            $this->view->converting = (!$entry || $entry->status == Kaltura_Client_Enum_EntryStatus::READY) ? false : true;
            // if this is a data entry then presentationView is enabled;
            $this->view->presentationView = ($entry instanceof Kaltura_Client_Type_DataEntry);

            $form = new Application_Form_EditEntry();
            $isAsyncRequest = $this->getRequest()->getParam('format') == 'ajax';
            // check if the form should be submitted via ajax or not
            if ($isAsyncRequest)
            {
                $form->setAttrib('ajax', '1');
            }
            $form->setAction($this->view->baseUrl('/entry/edit/id/' . $id));


            if ($this->getRequest()->isPost())
            {
                $request = $this->getRequest();
                $data = $request->getPost();
                $entryPost = $request->getParam('Entry');
                if ($form->isValid($data))
                {
                    $data = $form->getValues();
                    // redirect to the save action
                    $this->view->formValid = true;
                    // check if tags and nickname were set, and add nickname to the tags
                    if (isset($entryPost['nickname']) && $entryPost['nickname'])
                    {
                        if (isset($entryPost['tags']) && $entryPost['tags'])
                        {
                            // prepend the nickname tag to the tags
                            $entryPost['tags'] = 'displayname_' . $entryPost['nickname'] . ', ' . $entryPost['tags'];
                        }
                        else
                        {
                            // no tags for the entry, just add the displayname
                            $entryPost['tags'] = 'displayname_' . $entryPost['nickname'];
                        }
                    }

                    try
                    {
                        $newEntry = Kms_Resource_Models::getEntry()->save($entryPost);
                    }
                    catch(Kaltura_Client_Exception $e)
                    {
                        $this->view->saveError = true;
                        $this->view->formValid = false;
                        $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
                    }

                    if(!$this->view->saveError)
                    {

                        $newEntryBoxId = $this->_request->getParam('new');
                        if ($newEntryBoxId)
                        {
                            // new entry was added
                            if ($this->_request->getParam('format') == 'ajax')
                            {
                                //$this->_redirect('/entry/done/boxId/'.$newEntryBoxId.'/id/'.$id.'?format=ajax');
                                $this->_request->setParam('boxId', $newEntryBoxId);

                                // moderation auto-approval
                                /* $identity = Zend_Auth::getInstance()->getIdentity();
                                $userRole = $identity->getRole();
                                */

                                $this->_forward('done');
                                return;
                            }
                        }
                        else
                        {
                            $flashMessenger = $this->_flashMessenger->addMessage($this->_translate->translate('The information was saved successfully'));

                            if ($this->_request->getParam('format') == 'ajax')
                            {
                                $this->_redirect('/edit/'.$id . '?format=ajax&nocache');
                                //$this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?format=ajax&nocache');
                            }
                            else
                            {
                                $this->_redirect('/edit/'.$id . '?nocache');

                                //$this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?nocache');
                            }
    /*                        if ($this->_request->getParam('format') == 'ajax')
                            {
                                $this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?format=ajax&nocache');
                            }
                            else
                            {
                                $this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?nocache');
                            }*/
                        }
                    }
                }
                else
                {
                    $this->view->formValid = false;
                    $form->getElement('id')->setValue($id);
                    $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
                }
            }
            elseif ($entry->id)
            {
                // populate the form with entry details
                $form->getElement('id')->setValue($entry->id);
                $form->getElement('name')->setValue($entry->name);
                $form->getElement('nickname')->setValue($this->view->getHelper('String')->getAuthorNameFromTags($entry->tags));
                $form->getElement('description')->setValue($entry->description);
                $form->getElement('tags')->setValue($this->view->getHelper('String')->removeAuthorNameFromTags($entry->tags));

                $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
            }
            else
            { // empty form for new entry
                $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
            }
            $form->render();


            $this->view->entry = $entry;
            $this->view->form = $form;
        }
        else
        {
            throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
            
        }
    }

    /**
     * action to only validate the entry form
     */
    public function addEntryFormAction()
    {
        $form = new Application_Form_EditEntry();
        //get the filename from the parameters
        $name = $this->getRequest()->getParam('name');
        // remove extension from name
        $name = preg_replace('/^(.*)\.+.{3,4}$/', '$1', $name);

        $this->view->uploadBoxId = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : '1';
        $nickname = Zend_Auth::getInstance()->getIdentity()->getId();

        $isAsyncRequest = $this->getRequest()->getParam('format') == 'ajax';
        // check if the form should be submitted via ajax or not
        if ($isAsyncRequest)
        {
            $form->setAttrib('ajax', '1');
        }
        $form->setAction($this->view->baseUrl('/entry/add-entry-form/id/' . $this->view->uploadBoxId));

        if ($this->getRequest()->isPost())
        {
            $request = $this->getRequest();
            $data = $request->getPost();


            if ($form->isValid($data))
            {
                // repopulate from sanitized form data
                $data = $form->getValues();
                // add listeners to form elements to re-enable the form
                $formscript = new Kms_Form_Element_Note('formscript');
                $formscript->clearDecorators();
                $formscript->setDecorators(array('ViewHelper'));
                $formscript->setValue('
                    <script>
                        $("#uploadbox' . $this->view->uploadBoxId . ' .edit_entry").find("input,select,textarea").bind("change keydown", function() { ksuHandlers[' . $this->view->uploadBoxId . '].reEnableForm(); });
                    </script>
                    ');
                $form->addElement($formscript);

                // redirect to the save action
                $this->view->formValid = true;
                $form->enableSubmitButton($this->_translate->translate('Saved'), $isAsyncRequest, true);
                $entryPost = $request->getParam('Entry');
                if ($entryPost['id'])
                {
                    $this->_request->setParam('new', $this->view->uploadBoxId);
                    $this->view->form = $form;
                    // check if tags and nickname were set, and add nickname to the tags
                    if (isset($entryPost['nickname']) && $entryPost['nickname'])
                    {
                        if (isset($entryPost['tags']) && $entryPost['tags'])
                        {
                            // prepend the nickname tag to the tags
                            $entryPost['tags'] = 'displayname_' . $entryPost['nickname'] . ', ' . $entryPost['tags'];
                        }
                        else
                        {
                            // no tags for the entry, just add the displayname
                            $entryPost['tags'] = 'displayname_' . $entryPost['nickname'];
                        }
                    }

                    try
                    {
                        $newEntry = Kms_Resource_Models::getEntry()->save($entryPost);
                    }
                    catch(Kaltura_Client_Exception $e)
                    {
                        $this->view->saveError = true;
                        $this->view->formValid = false;
                        $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
                    }

                    if(!$this->view->saveError)
                    {
                        $newEntryBoxId = $this->_request->getParam('new');
                        if ($newEntryBoxId)
                        {
                            // new entry was added
                            if ($this->_request->getParam('format') == 'ajax')
                            {
                                $this->_request->setParam('boxId', $newEntryBoxId);

                                $this->_forward('done');
                                return;
                            }
                        }
                        else
                        {
                            $flashMessenger = $this->_flashMessenger->addMessage($this->_translate->translate('The information was saved successfully'));

                            if ($this->_request->getParam('format') == 'ajax')
                            {
                                $this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?format=ajax&nocache');
                            }
                            else
                            {
                                $this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?nocache');
                            }
                        }                    
                    }
                }
            }
            else
            {
                $this->view->formValid = false;
                $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
            }
        }
        else
        {
            $form->getElement('name')->setValue($name);
            $form->getElement('nickname')->setValue($nickname);
            $form->enableSubmitButton($this->_translate->translate('Save'), $isAsyncRequest);
        }

        $form->render();
        $this->view->form = $form;
    }

    /**
     * parse the form and save the entry
     *
     *
     * @return void
     *
     *
     */
    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');
        $request = $this->getRequest();

        // check if we have the request and it's a POST
        if ($request->isPost())
        {
            // create a model instance
            $entryModel = new Application_Model_Entry();

            // get the Entry submitted form
            $entry = $request->getParam('Entry');


            // check if tags and nickname were set, and add nickname to the tags
            if (isset($entry['nickname']) && $entry['nickname'])
            {
                if (isset($entry['tags']) && $entry['tags'])
                {
                    // prepend the nickname tag to the tags
                    $entry['tags'] = 'displayname_' . $entry['nickname'] . ', ' . $entry['tags'];
                }
                else
                {
                    // no tags for the entry, just add the displayname
                    $entry['tags'] = 'displayname_' . $entry['nickname'];
                }
            }

            $newEntry = $entryModel->save($entry);

            $newEntryBoxId = $this->_request->getParam('new');
            if ($newEntryBoxId)
            {
                // new entry was added
                if ($this->_request->getParam('format') == 'ajax')
                {
                    //$this->_redirect('/entry/done/boxId/'.$newEntryBoxId.'/id/'.$id.'?format=ajax');
                    $this->_request->setParam('boxId', $newEntryBoxId);

                    // moderation auto-approval
                    /* $identity = Zend_Auth::getInstance()->getIdentity();
                      $userRole = $identity->getRole();
                     */

                    $this->_forward('done');
                    return;
                }
            }
            else
            {
                $flashMessenger = $this->_flashMessenger->addMessage($this->_translate->translate('The information was saved successfully'));

                if ($this->_request->getParam('format') == 'ajax')
                {
                    $this->_redirect($this->view->baseUrl('/edit/'.$id) . '?format=ajax&nocache');
                    //$this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?format=ajax&nocache');
                }
                else
                {
                    $this->_redirect($this->view->baseUrl('/edit/'.$id) . '?nocache');
                    
                    //$this->_redirect($this->view->EntryLink($id, $newEntry->name, true) . '?nocache');
                }
            }
        }
    }

    /**
     * invoked when entry is done uploading and saved
     */
    public function doneAction()
    {
        $entry = $this->getRequest()->getParam('Entry');
        $this->view->entryId = $entry['id'];
        $this->view->entryName = $entry['name'];
        $this->view->uploadBoxId = $this->getRequest()->getParam('boxId');

        // check if we must approve this entry for unmoderated role
    }

    public function postUploadAction()
    {
        $entryId = $this->getRequest()->getParam('entryid');
        $entryModel = Kms_Resource_Models::getEntry();
        $entry = $entryModel->get($entryId);
        // add private category for the entry
        $entryModel->updateCategories($entryId, array());
        // auto approve entry if applicable
        $entryModel->approve($entry);

        // run interface
        $entryModel->entryAdded();
        
        // clear the my media cache
        $entryModel->clearMyMediaCache();
        exit;
    }

    public function playAction()
    {
        // action body
        $id = $this->getRequest()->getParam('id');
        $entryModel = Kms_Resource_Models::getEntry();
        try
        {
            $entry = $entryModel->get($id);
        }
        catch (Kaltura_Client_Exception $ex)
        {
            Kms_Log::log('entry: Unable to get entry id ' . $id . '. ' . $ex->getCode() . ': ' . $ex->getMessage(), Kms_Log::NOTICE);
            // entry is missing (wrong id, or deleted)
            throw new Zend_Controller_Action_Exception($this->_translate->translate('Sorry, you cannot access this page. Either access has been denied or page was not found.'), Kms_Plugin_Access::EXCEPTION_CODE_ACCESS);
            //$this->_forward('no-entry');
            return;
        }
        
        $this->view->categories = $entryModel->getEntryCategories($id);

        $this->view->isLiked = $entryModel->isLiked($id);
        
        $entryUser = $entry->userId;
        $currentUser = null;
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity)
        {
            $currentUser = $identity->getId();
        }

        // if this is a data entry then presentationView is enabled;
        $this->view->presentationView = ($entry instanceof Kaltura_Client_Type_DataEntry);

        $this->view->entry = $entry;
        $this->view->entryUser = $entryUser;
        $this->view->isEntryOwner = $entryUser == $currentUser;
        $this->view->pending = $entry->moderationStatus == Kaltura_Client_Enum_EntryModerationStatus::PENDING_MODERATION ? true : false;
        $this->view->converting = (!$entry || $entry->status == Kaltura_Client_Enum_EntryStatus::READY) ? false : true;
        $this->view->showLike = Kms_Resource_Config::getConfiguration('application', 'enableLike');
        $this->view->categoryId = Kms_Resource_Context::getPlaybackContext($this->getRequest());
        
        //$this->view->wideLayout = true;
        $this->view->contentClass = 'entrypage';     
        
        // should the entry page be handled by a module
        $this->view->renderByModule = $entryModel->handleEntryByModule($entry);
    }

    public function deleteAction()
    {
        // get the user id
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity)
        {
            $currentUser = $identity->getId();
        }

        // get the entry from the entry id requested
        $id = $this->getRequest()->getParam('id');
        //get redirect url from the params
        $redir = $this->getRequest()->getParam('redir');
        if ($redir)
        {
            $this->view->redirectUrl = $redir;
        }
        else
        {
            // redirect after delete, defaults to my-media
            $this->view->redirectUrl = $this->view->baseUrl('my-media');
        }
        $this->view->id = $id;
        $idArray = explode(',', $id);
        
        //number of entries sent for deletion
		$numSentForDeletion = count($idArray);
		
		//get entries - should be in cache
        $entryModel = Kms_Resource_Models::getEntry();
        $entries = $entryModel->getEntriesByIds($idArray);
        
        //filter out not owned and external (not editable) entries
        $idArray = $this->filterAllowDeleteIds($entries);
        //if there were not allowed entries - give a message
        $this->view->notAllowedMessage = '';
        if (count($idArray) < $numSentForDeletion)
		{
        	$this->view->notAllowedMessage = $this->_translate->translate(' (You are not allowed to delete other ') . ($numSentForDeletion - count($idArray)) . $this->_translate->translate(' entries)'); 
		}
		
        if (count($idArray) == 0)
        {
        	//nothing to delete
        	$this->view->allowed = false;
        	$this->view->confirmed == ($this->getRequest()->getParam('confirm') == '1');
            return;
        }
        //there are entries to delete
        $this->view->allowed = true;
        if (count($idArray) > 1) // multiple entries to delete
        {
            $this->view->multi = count($idArray);
            
            if ($this->getRequest()->getParam('confirm') == '1')
            {
                $this->view->confirmed = true;
                $numDeleted = $entryModel->deleteMulti($idArray);
                if ($numDeleted)
                {
                	$this->_flashMessenger->addMessage($this->_translate->translate('Successfully deleted') . ' ' . $numDeleted . ' ' . $this->_translate->translate('items') . '!' . $this->view->notAllowedMessage);
                }
            }
            else
            {
                $this->view->confirmed = false;
            }
        }
        else
        {
        	// only one entry to delete
        	$id = array_pop($idArray);
            $this->view->multi = false;
            $entry = $entryModel->get($id);
            $this->view->entry = $entry;
            
            // check if confirmation was sent
            if ($this->getRequest()->getParam('confirm') == '1')
            {
            	$this->view->confirmed = true;
                if ($entryModel->delete($entry->id))
                {
                	$this->_flashMessenger->addMessage($this->_translate->translate('Successfully deleted') . ' "' . $entry->name . '"' . $this->view->notAllowedMessage);
				}
            }
            else
            {
            	$this->view->confirmed = false;
			}
        }
    }
    
    private function filterAllowDeleteIds($entries) 
    {
    	
    	$allowedIds = array();
    	$isEntryEditableHelper = new Kms_View_Helper_IsEntryEditable();
    	foreach ($entries as $entry)
    	{
    		//entries are allowed for deletion if the user owns them and the entry is editable
    		if (Kms_Plugin_Access::isCurrentUser($entry->userId) && !($isEntryEditableHelper->IsEntryEditable($entry) === false))
    		{
    			array_push($allowedIds, $entry->id);	
    		}
    	} 
    	return $allowedIds;
    }

    /**
     * action for upload of entries (ajaxable)
     */
    public function addAction()
    {
        $uploadType = $this->getRequest()->getParam('type');
        if ($uploadType == 'webcam')
        {
            // enable the edit entry form right away, in case of webcam upload
            $form = new Application_Form_EditEntry();
            // empty form for new entry
            $form->enableSubmitButton($this->_translate->translate('Save'), true);
            $form->setAttrib('ajax', '1');
            $nickname = Zend_Auth::getInstance()->getIdentity()->getId();
            $form->setAction($this->view->baseUrl('/entry/add-entry-form/id/1/'));
            // set the nickname in the form
            $form->getElement('nickname')->setValue($nickname);
            $this->view->form = $form;
            //get the filename from the parameters
            $this->view->uploadBoxId = '1';
        }
        $this->view->uploadType = $uploadType;


        $this->view->ks = Kms_Resource_Client::getUserClient()->getKs();
//        $this->view->ks = Kms_Resource_Client::getAdminClient()->getKs();
        $this->view->partnerId = Kms_Resource_Config::getConfiguration('client', 'partnerId');

        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity)
        {
            $this->view->userId = $identity->getId();
        }
        else
        {
            $this->view->userId = null;
        }
        $this->view->uploadBoxId = $this->getRequest()->getParam('boxId') ? $this->getRequest()->getParam('boxId') : '1';

        $this->view->uploadButtonText = $this->view->uploadBoxId == 1 ? $this->_translate->translate('Choose a file to upload') : $this->_translate->translate('Choose another file');
        $this->view->uploadHeading = $this->view->uploadBoxId == 1 ? $this->_translate->translate('Upload Media') : $this->_translate->translate('Upload another file');
    }

    public function processNewPresentationAction()
    {
        $entryId = $this->getRequest()->getParam('id');
        $nickname = Zend_Auth::getInstance()->getIdentity()->getId();
        $model = Kms_Resource_Models::getEntry();
        $model->id = $entryId;
        $model->addAdminTag('presentation');
        $model->setNickname($nickname);
        $model->updateCategories($entryId, array());

        $this->_flashMessenger->addMessage($this->_translate->translate('Your presentation has been successfully created. You can now sync keypoints or edit metadata.'));
        $this->_redirect('/edit/' . $entryId);
    }

    public function noEntryAction()
    {
        // action body
    }

    public function checkStatusAction()
    {
        $entryId = $this->getRequest()->getParam('id');
        $this->view->entryId = $entryId;
        $entryModel = Kms_Resource_Models::getEntry();
        $entry = $entryModel->get($entryId);
        $this->view->entry = $entry;
        $entryReady = $entry->status == Kaltura_Client_Enum_EntryStatus::READY;

        if ($entryReady)
        {
            $this->view->ready = true;
        }
        else
        {
            $this->view->ready = false;
        }
    }

    /**
     * ajax action for "like"
     */
    public function likeAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id)
        {
            $model = Kms_Resource_Models::getEntry();
            $this->view->success = $model->like($id);
            if ($this->view->success){
                $entry = $model->entry;
                $this->view->id = $entry->id;
                $this->view->likes = $entry->votes;
            }
        }
    }

    /**
     * ajax action for "unlike"
     */
    public function unlikeAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if ($id)
    	{
    		$model = Kms_Resource_Models::getEntry();
    		$this->view->success = $model->unlike($id);
    		if ($this->view->success){
    		    $entry = $model->entry;
    		    $this->view->id = $entry->id;
                $this->view->likes = $entry->votes;
    		}       	
    	}
    }

}

