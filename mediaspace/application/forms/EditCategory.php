<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * form to change category settings
 * the members of this form corresponds to the members of Kaltura_Client_Type_Category - 
 * this in order for the populate to work.
 */
class Application_Form_EditCategory extends Zend_Form
{
    const FORM_NAME = 'Category';
    
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');

        // title
        $title = new Zend_Form_Element_Text('name', array(
                'label' => $translate->translate('Title'),
                'required' => 1,
                'belongsTo' => self::FORM_NAME
        ));
        $validator = new Zend_Validate_StringLength(array('max' => 60));
        $validator->setMessage($translate->translate('Value is longer than %max% characters').'.', Zend_Validate_StringLength::TOO_LONG);
        $title->setAttrib('maxlength', 60);
        $title->setAttrib('autofocus', '');
        $title->addValidator($validator);
        $this->addElement($title);

        // description
        $description = new Zend_Form_Element_Textarea('description', array(
                'label' => $translate->translate('Description'),
                'required' => 0,
                'rows' => 4,
                'belongsTo' => self::FORM_NAME
        ));
        $this->addElement($description);

        // tags
        $tags = new Zend_Form_Element_Text('tags', array(
                'label' => $translate->translate('Tags'),
                'required' => 0,
                'belongsTo' => self::FORM_NAME,
        ));
        $this->addElement($tags);
        
        // membership
        $membership = new Kms_Form_Element_Radio('membership', array(
                'required' => 1,
                'multiOptions' => array(Application_Model_Category::MEMBERSHIP_OPEN => '<div>' . $translate->translate('Open') . '<span>' . $translate->translate('Membership is open and non-members can view content and participate.') . '</span></div>',
                        Application_Model_Category::MEMBERSHIP_RESTRICTED => '<div>' . $translate->translate('Restricted') . '<span>' . $translate->translate('Non-members can view content, but users must be invited to participate.') . '</span></div>',
                        Application_Model_Category::MEMBERSHIP_PRIVATE => '<div>' . $translate->translate('Private') . '<span>' . $translate->translate('Membership is by invitation only and only members can view content and participate.') . '</span></div>'
                        ),
                'belongsTo' => self::FORM_NAME,
                'value' => Application_Model_Category::MEMBERSHIP_OPEN
        ));
        $membership->removeDecorator('label');
        $this->addElement($membership);
        
        // indication that this form is about a channel 
        $hidden = new Zend_Form_Element_Hidden('id',array('value' => 0, 'belongsTo' => self::FORM_NAME));
        $hidden->removeDecorator('Label');
        $hidden->removeDecorator('HtmlTag');
//        Zend_Debug::dump($hidden->getDecorators());
        $this->addElement($hidden);
        
        
        $spec = array(
                'belongsTo' => self::FORM_NAME,
                'name' => 'moderation',
                'description' => $translate->translate('Moderate content (Media will not appear in channel until approved by channel manager)'),
        );
        
        $element = new Zend_Form_Element_Checkbox($spec);
        
        $element->setOrder(9);
        $element->getDecorator('Description')->setTag('span');
        $element->removeDecorator('Label');
        $element->getDecorator('HtmlTag')->clearOptions()->setTag('div')->setOption('id', 'Category-moderation-element');
        // add new form element to the channel edit form
        $this->addElement($element);
        
        // allow modules to modify the form
        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_Category_Edit') as $name => $model)
        {
            $model->editForm($this);
        }        
        
        // submit
        $submit = new Zend_Form_Element_Submit(array('belongsTo' => self::FORM_NAME, 'name' => $translate->translate('Save')));
        $submit->setOrder(10);
        $this->addElement($submit);
        
        // add a trim filter to this form's elements
        $this->setElementFilters(array('StringTrim'));
        
    }
    
    public function populate(array $values)
    {
        parent::populate($values);
        
        // allow modules to populate the form
        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_Category_EditPopulate') as $name => $model)
        {
            $model->populate($this, $values);
        }
    }
}