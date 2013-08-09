<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
* To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Comments_Form_AddComment extends Zend_Form
{
    const FORM_NAME = 'AddComment';

    private $view;
    public  $_defaultKeyword = null;
    private $_options = array();

    public function __construct($options = array())
    {
        $this->_options = $options;
        parent::__construct();
    }    
    
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        // form id
        $this->setAttrib('id', 'addComment');
        $this->setAttrib('ajax' , 1);
        $this->setAttrib('class' , 'addComment');
        
        $this->_defaultKeyword = isset($this->_options['defaultKeyword']) ? $this->_options['defaultKeyword'] : $translate->translate('Add a Comment');
        
        $entryId = new Zend_Form_Element_Hidden(array(
            'name' => 'entryId',
            
        ));
        $entryId->removeDecorator('Label');
        $this->addElement($entryId);
        
        $parentId = new Zend_Form_Element_Hidden(array(
            'name' => 'parentId',
            
        ));
        $entryId->removeDecorator('Label');
        $this->addElement($parentId);

        $replyTo = new Zend_Form_Element_Hidden(array(
            'name' => 'replyTo',
            
        ));
        $replyTo->removeDecorator('Label');
        $this->addElement($replyTo);

        
        $commentsbox = new Zend_Form_Element_Textarea(array(
            'name' => 'commentsbox',
            'id' => 'commentsbox',
            'onfocus' => 'if($(this).val() == defaultCommentsText || $(this).val() == defaultReplyText) {$(this).val(""); $(this).addClass("focus");}',
            'onblur' => 'if($(this).val() == "") { $(this).val("'.$this->_defaultKeyword.'");$(this).removeClass("focus");}',
        )) ;
        $commentsbox->removeDecorator('Label');
        $commentsbox->setValue($this->_defaultKeyword); 
        
        
        $this->addElement($commentsbox);
        
        
        $add = new Zend_Form_Element_Submit(array(
            'label' => $translate->translate('Add'),
            'name' => 'add',
            'onclick' => 'if($("#commentsbox", $(this).closest("form")).val() == "" || $("#commentsbox", $(this).closest("form")).val() == defaultCommentsText || $("#commentsbox", $(this).closest("form")).val() == defaultReplyText) { return false; }'
        ));
        $add->removeDecorator('Label');
        
        $this->addElement($add);
        
        
        
    }
    
}