<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Form_EditEntry extends Zend_Form 
{
    const FORM_NAME = 'Entry';

    private $view;
    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');
        
        $this->setMethod(self::METHOD_POST);
        $this->setAttrib('id', 'edit_entry');
        $this->setAttrib('class', 'edit_entry');
        
        $nameElem = $this->createFormElement(
            'text', 
            'name', 
            array(
                'label' => $this->_translate->translate('Name'),
//                'validators' => array('alnum'),
                'required' => true,
                'isEmpty' => false
            )
        );
        
        $this->createFormElement(
            'text', 
            'nickname', 
            array(
                'label' => $this->_translate->translate('Created By'),
//                'validators' => array('alnum'),
                'required' => Kms_Resource_Config::getConfiguration('metadata', 'createdByRequired'),
                'isEmpty' => false
            )
        );

        $this->createFormElement(
            'textarea', 
            'description', 
            array(
                'label' => $this->_translate->translate('Description'),
//                'validators' => array('alnum'),
                'required' => Kms_Resource_Config::getConfiguration('metadata', 'descriptionRequired'),
                'isEmpty' => false
            )
        );
        
        $this->createFormElement(
            'text', 
            'tags', 
            array(
                'label' => $this->_translate->translate('Tags'),
//                'validators' => array('alnum'),
                'required' => Kms_Resource_Config::getConfiguration('metadata', 'tagsRequired'),
            )
        );

        $idElem = $this->createFormElement(
            'hidden', 
            'id', 
            array(
            )
        );
        

        $this->setIsArray(true);
        
        
        //@todo Change the form interaction with modules... call a function in the module and pass the form by reference
        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_Entry_Edit') as $name => $model)
        {
            $model->editForm($this);
        }
        
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));      
        
        
        // set elements decorators
        $elements = $this->getElements();
        foreach($elements as $elem)
        {
            $elem->addDecorator('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND'));
            
            // if label exists - update it (in some cases label is removed)
            if($elem->getDecorator('Label'))
            {
                $elem->addDecorator('Label', array( 'optionalSuffix' => ':', 'requiredSuffix' => ':<br/><span>(* '.$this->_translate->translate('Required').')&nbsp;</span>', 'escape' => false));
                if($elem->getType() != 'Zend_Form_Element_Hidden')
                {
                    $elem->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
                }
            }
            else
            {
                // add div wrapper to all but hidden fields
                if($elem->getType() != 'Zend_Form_Element_Hidden')
                {
                    $elem->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'nolabel'));
                }
                
            }
            $elem->removeDecorator('HtmlTag');
        }
        // move the elements to a separate display group, in order to enable scrolling just for the elements, and not for the submit button
        $this->moveElementsToDisplayGroup();
        $this->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'class' => 'elements-scroll')),
        ));
        
        // add a trim filter to this form's elements
        $this->setElementFilters(array('StringTrim'));
        
    }

    public function moveElementsToDisplayGroup()
    {
        $elements = array();
        foreach($this as $name => $element)
        {
            $elements[] = $name;
        }
        $this->addDisplayGroup($elements, 'elements');
    }
    
    
    public function enableSubmitButton($text, $asyncCancelButton = false, $buttonDisabled = false)
    {
        $entryUrl = $this->view->EntryLink($this->getElement('id')->getValue());
        $formFooter =new Kms_Form_Element_Note('formfooter');
        $formFooter->setValue('<button id="save_edit_entry" class="save_edit_entry" '.($buttonDisabled ? 'disabled="disabled"' : '').' type="submit" onclick="$(this).text(\''.$this->_translate->translate('Saving').'...\');$(\'#cancel_edit_entry\').hide();">'.$text.'</button>');
        $formFooter->setDecorators(
            array(
                'ViewHelper',
                'Fieldset',
            )
        );
        $this->addElement($formFooter);
        
    }
    
    private function createFormElement($type, $name, $params = array())
    {
        $params['belongsTo'] = self::FORM_NAME;
        $this->addElement($type, $name, $params);
        $elem = $this->getElement($name);
        $elem->setAttrib('class', $elem->getId());
    }
    
    public function isValid($data)
    {
        $valid = parent::isValid($data);
        foreach($this->getElements() as $elem)
        {
            if($elem->hasErrors())
            {
                $elem->addDecorator('Errors', array('placement' => 'APPEND'));
                if($elem->getDecorator('Label'))
                {
                    $elem->addDecorator('Label', array( 'optionalSuffix' => ':', 'requiredSuffix' => ':<br/><span>(* '.$this->_translate->translate('Required').')&nbsp;</span>', 'escape' => false));
                    $elem->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
                    $elem->addDecorator(array('errorDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'error'));
                }
                else
                {
                    $elem->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
                    $elem->addDecorator(array('errorDiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'nolabel error'));

                }
            }
        }
        return $valid;
        
        
    }
    
}

