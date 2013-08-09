<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Application_Form_ImportConfig extends Zend_Form
{
    const FORM_NAME = 'Import Configuration';
    private $view;
    private $_translate = null;

    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        $this->setMethod(self::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('ajax', '1');

        $configFile = new Zend_Form_Element_File(
                        'configFile',
                        array(
                            'label' => $this->_translate->translate('Upload configuration file'),
                            'required' => true,
                           // 'onchange' => 'this.form.submit()',
                        )
        );

        $configFile->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'element'));
        $configFile->addDecorator('Label');

        $configSelect = new Zend_Form_Element_Select(
                        'keepPartner',
                        array(
                            'label' => $this->_translate->translate('Keep partner settings?'),
                            'required' => false,
                        )
        );
        $configSelect->setMultiOptions(array('1' => $this->_translate->translate('Yes'), '0' => $this->_translate->translate('No')));
        
        $migrate = new Zend_Form_Element_Submit(
                        'import',
                        array(
                            'required' => false,
                            'ignore' => true,
                            'label' => $this->_translate->translate('Import'),
                        )
        );

        $this->addElement($configFile);
        $this->addElement($configSelect);

        $this->addElement($migrate);

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
    }

    public function isValid($data)
    {
        $valid = parent::isValid($data);
        foreach ($this->getElements() as $elem)
        {
            if ($elem->hasErrors())
            {
                $elem->addDecorator('Errors', array('placement' => 'APPEND'));
                $elem->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'element error'));
                /* if($elem->getDecorator('Label'))
                  {
                  $elem->addDecorator('Label');
                  //                    $elem->addDecorator(array('errorDiv' => 'HtmlTag'), array('tag'=>'div', ));
                  } */
            }
        }
        if (!$valid)
        {
            $this->removeDecorator('FormErrors');
        }

        return $valid;
    }

}

