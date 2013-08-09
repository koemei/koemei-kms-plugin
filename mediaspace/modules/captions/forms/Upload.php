<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */


/**
 * class to hold the caption asset upload form
 * 
 * @author talbone
 *
 */
class Captions_Form_Upload extends Zend_Form
{
    const FORM_NAME = 'Upload';

    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        //reset the form decorators (dl)
        $this->setDecorators(array('FormElements','Form',array('FormErrors', array('placement' => 'PREPEND', 'label' => false)) ));

        //custom decorator
        $customDecorators = array(
                'ViewHelper',
                array('Label', array('separator' => ' ')),
                array('Description', array('seperator' => ' '))
        );

        // form id
        $this->setAttrib('id', 'uploadCaption');

        // caption asset file upload token
        $token = new Zend_Form_Element_Hidden('token', array(
                'belongsTo' => self::FORM_NAME,
                'decorators' => array('ViewHelper')
                ));
        $this->addElement($token);
        
        // caption asset file type
        $type = new Zend_Form_Element_Hidden('type', array(
                'belongsTo' => self::FORM_NAME,
                'decorators' => array('ViewHelper')
        ));
        $this->addElement($type);
        
        // caption asset file name
        $name = new Zend_Form_Element_Hidden('name', array(
                'belongsTo' => self::FORM_NAME,
                'decorators' => array('ViewHelper')
        ));
        $this->addElement($name);
        
        // language
        $language = new Zend_Form_Element_Select('language', array(
                'label' => $translate->translate('Language'),
                'required' => 1,
                'multiOptions' => array('0' => $translate->translate('Select Language')) + Captions_Model_Captions::getAvailableLanguages(),
                'belongsTo' => self::FORM_NAME,
                'decorators' => $customDecorators
        ));
                
        $language->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'lang'));
        $validator = new Zend_Validate_InArray(array('haystack' => array_keys(Captions_Model_Captions::getAvailableLanguages())));
        $validator->setMessage($translate->translate('Select a language'),Zend_Validate_InArray::NOT_IN_ARRAY);
        $language->addValidator($validator);
        $this->addElement($language);
        
        // label
        $label = new Zend_Form_Element_Text('label', array(
                'label' => $translate->translate('Label'),
                'description' => $translate->translate('(text that appears in caption selector)'),
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'decorators' => $customDecorators
        ));

        $validator = new Zend_Validate_NotEmpty();
        $validator->setMessage($translate->translate('Enter a label'),Zend_Validate_NotEmpty::IS_EMPTY);
        $label->addValidator($validator);

        $this->addElement($label);
    }

}