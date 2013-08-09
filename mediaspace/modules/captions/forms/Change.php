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
class Captions_Form_Change extends Zend_Form
{
    const FORM_NAME = 'Change';
    
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');
    
        //reset the form decorators (dl)
        $this->setDecorators(array('FormElements','Form'));
    
        // form id
        $this->setAttrib('id', 'changeCaption');
        
        // language
        $language = new Zend_Form_Element_Select('language', array(
                'label' => $translate->translate('Language'),
                'required' => 1,
                'multiOptions' => Captions_Model_Captions::getAvailableLanguages(),
                'belongsTo' => self::FORM_NAME,
                'decorators' => array( 'ViewHelper', 'Errors')
        ));
    
        $language->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'language column first'));
        $validator = new Zend_Validate_InArray(array('haystack' => array_keys(Captions_Model_Captions::getAvailableLanguages())));
        $validator->setMessage($translate->translate('Select a language'),Zend_Validate_InArray::NOT_IN_ARRAY);
        $language->addValidator($validator);        
        $this->addElement($language);
    
        // label
        $label = new Zend_Form_Element_Text('label', array(
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'decorators' => array( 'ViewHelper', 'Errors')
        ));
        $label->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'column'));

        $validator = new Zend_Validate_NotEmpty();
        $validator->setMessage($translate->translate('Enter a label'),Zend_Validate_NotEmpty::IS_EMPTY);
        $label->addValidator($validator);
        $this->addElement($label);
    
        // submit
        $submit = new Zend_Form_Element_Submit(array('belongsTo' => self::FORM_NAME, 'name' => 'submit'));
        $submit->setLabel($translate->translate('Done'));
        $submit->removeDecorator('label');
        $submit->removeDecorator('DtDdWrapper');
        $submit->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'column submit'));
        $this->addElement($submit);
    }
}