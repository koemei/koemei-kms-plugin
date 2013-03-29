<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Form_Install extends Application_Form_Login
{

    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        parent::init();
        
        $hostElement = new Zend_Form_Element_Text(
            'host', 
            array(
                'label' => $this->_translate->translate('Kaltura Server URL').':', 
                'required' => true,
                'value' => 'http://www.kaltura.com', 
            )
        );

        $pidElement = new Zend_Form_Element_Text(
            'partnerId', 
            array(
                'label' => $this->_translate->translate('Partner ID').':', 
                'validators' => array(
                    'Alnum', 
                    array('StringLength', false, array(3, 20)),
                ),
                'required' => true,
            )
        );

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
                    array('StringLength', false, array(1,10)),
                ),
                'required' => false, // setting empty context means no entitlement is enforced in KMS
            )
        );
        $privacyContext->setDescription($this->_translate->translate('String to be set as privacy context on KMS root category to enforce entitlement.'));

        $this->addElement($hostElement);
        $this->addElement($pidElement);
        $this->addElement($instanceId);
        $this->addElement($privacyContext);
        $this->getElement('username')->setOrder(1)->setAttrib('tabindex', 1);
        $this->getElement('password')->setOrder(2)->setAttrib('tabindex', 2);
        $this->getElement('partnerId')->setOrder(3)->setAttrib('tabindex', 3);
        $this->getElement('instanceId')->setOrder(4)->setAttrib('tabindex', 4);
        $this->getElement('privacyContext')->setOrder(6)->setAttrib('tabindex', 6);
        $this->getElement('host')->setOrder(15)->setAttrib('tabindex', 15);
        $this->getElement('login')->setOrder(16)->setAttrib('tabindex', 16);
        $this->getElement('login')->setLabel($this->_translate->translate('Next'));
        $this->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Label',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag'=>'div', 'class' => 'element')),
        ));
        $this->removeDecorator('Errors');
        $this->getElement('login')->removeDecorator('Label');
        $this->addForceInstanceCheckbox();

    }
    
    public function adminLogin()
    {
    	parent::adminLogin();
        $this->setAction( $this->view->baseUrl('/install/install'));
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


}

