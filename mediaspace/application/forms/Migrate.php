<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Application_Form_Migrate extends Zend_Form 
{
    const FORM_NAME = 'PrepareForMigration';
    private $view;
    private $_translate = null;

    const MSG_MANDATORY = 'msgMandatory';
    protected $_messageTemplates = array(
        self::MSG_MANDATORY => " is mandatory",
    );

    public function init() 
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        $this->setMethod(self::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');

        $versionToMigrate = new Zend_Form_Element_Select(
                'migrateFromVersion',
                array(
                    'label' => $this->_translate->translate('Select Source Version') . ':',
                    'required' => false,
                )
        );
            
        $versions = array(
            Kms_Setup_Common::KNOWN_VERSION_2 => 'Kaltura MediaSpace 2',
            Kms_Setup_Common::KNOWN_VERSION_30x => 'Kaltura MediaSpace 3',
            Kms_Setup_Common::KNOWN_VERSION_40x => 'Kaltura MediaSpace 4',            
        );
            
        $versionToMigrate->setMultiOptions($versions);

        $configPath = new Zend_Form_Element_Text(
                'configPath',
                array(
                    'label' => $this->_translate->translate('Config file path') . ':',
                    'required' => false,
                )
        );

        $configFile = new Zend_Form_Element_File(
                'configFile',
                array(
                    'label' => $this->_translate->translate('upload config file') . ':',
                    'required' => false,
                )
        );

        $configFile->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'element'));
        $configFile->addDecorator('Label');

        $instanceId = new Zend_Form_Element_Text(
            'instanceId',
            array(
                'label' => $this->_translate->translate('Instance ID').':',
                'validators' => array(
                    'Alnum',
                    array('StringLength', false, array(4,10)),
                ),
                'required' => true,
            )
        );
        $instanceId->setDescription($this->_translate->translate('Unique ID for this instance of MediaSpace installation.'));

        $privacyContext = new Zend_Form_Element_Text(
                'privacyContext',
                array(
                    'label' => $this->_translate->translate('Privacy Context').':',
                    'validators' => array(
                        'Alnum',
                        array('StringLength', false, array(4,10)),
                    ),
                    'required' => false, // setting empty context means no entitlement is enforced in KMS
                )
        );
        $privacyContext->setDescription($this->_translate->translate('String to be set as privacy context on KMS root category to enforce entitlement.'));

        $migrate = new Zend_Form_Element_Submit(
                'migrate',
                array(
                    'required' => false,
                    'ignore' => true,
                    'label' => $this->_translate->translate('Start Migration'),
                )
        );

        $this->addElement($versionToMigrate);
        $this->addElement($configPath);
        $this->addElement($configFile);
        $this->addElement($instanceId);
        $this->addElement($privacyContext);

        $this->addElement($migrate);
        $this->getElement('migrateFromVersion')->setOrder(1)->setAttrib('tabindex', 1);
        $this->getElement('configPath')->setOrder(2)->setAttrib('tabindex', 2);
        $this->getElement('configFile')->setOrder(3)->setAttrib('tabindex', 3);
        $this->getElement('instanceId')->setOrder(4)->setAttrib('tabindex', 4);
        $this->getElement('privacyContext')->setOrder(6)->setAttrib('tabindex', 6);
        $this->getElement('migrate')->setOrder(16)->setAttrib('tabindex', 16);

        $this->setElementDecorators(array(
            'ViewHelper',
            'Label',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'element')),
        ));
        
        $configFile->setDecorators(array(
            'File',
            'Label',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'element')),
        ));
        
        $migrate->removeDecorator('Label');

        $this->setDecorators(array(
            'FormElements',
            array('Description', array('placement' => 'prepend')),
            'Form',
            'Errors'
        ));
            
        $this->removeDecorator('Errors');
        $this->addForceInstanceCheckbox();
    }

    public function addForceInstanceCheckbox()
    {
        $element = new Zend_Form_Element_Checkbox('forceUseInstanceId',
            array(
                'label' => $this->_translate->translate('Force instance id').':',
            )
        );
        $element->setOrder(5)->setAttrib('tabindex', 5);
        $element->setDecorators(array(
        'Label',
            'ViewHelper',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'element')),
        ));
        $this->addElement($element);
    }

    public function isValid($values){
        if ($values['migrateFromVersion'] == Kms_Setup_Common::KNOWN_VERSION_40x) {
            // if this is migration from 4.0.x, we don't need privacy context and instance id
            unset($values['instanceId']);
            unset($values['privacyContext']);
            return parent::isValidPartial($values);
        }
        else{
            // full validation
            return parent::isValid($values);
        }
    }
}

