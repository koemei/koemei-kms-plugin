<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * a class to hold the caption asset change form
 * @author talbone
 *
 */
class Emailnotifications_Form_Update extends Zend_Form
{
    const FORM_NAME = 'Update';
    
    
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');
    
        //reset the form decorators (dl)
        $this->setDecorators(array('FormElements','Form'));
        
        $this->setAttrib('ajax', true);
        
        $this->setAttrib('method', 'post');
            

        // form id
        $this->setAttrib('id', 'updateForm');
        
        // language
        $textArea1 = new Zend_Form_Element_Textarea('subText', array(
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'decorators' => array( 'ViewHelper', 'Errors'),
        		'class' => "subText"
        ));
        
     
        $this->addElement($textArea1);
    
        // language
        $textArea2 = new Zend_Form_Element_Textarea('bodyText', array(
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'decorators' => array( 'ViewHelper', 'Errors'),
        		'class' => "bodyText"
        ));
        
     
        $this->addElement($textArea2);

        
        $submit = new Zend_Form_Element_Submit(array('belongsTo' => self::FORM_NAME, 'name' => 'submit'));
        $submit->setLabel('update');
        $submit->removeDecorator('label');
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'column submit'));
        $this->addElement($submit);
    	
        
    }
}