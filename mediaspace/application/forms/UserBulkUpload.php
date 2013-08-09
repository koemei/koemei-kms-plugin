<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Application_Form_UserBulkUpload extends Zend_Form {
    const FORM_NAME = 'UserBulkUpload';
    private $view;
    private $_translate = null;

    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        $this->setMethod(self::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');

        $csvFile = new Zend_Form_Element_File(
                        'csvFile',
                        array(
                            'label' => $this->_translate->translate('Select File') . ':',
                            'required' => true,
                        )
        );

        $csvFile->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'element'));
        $csvFile->addDecorator('Label');


        $submit = new Zend_Form_Element_Submit(
                        'upload',
                        array(
                            'required' => false,
                            'ignore' => true,
                            'label' => $this->_translate->translate('OK'),
                            'id' => 'userCsvUploadButton',
                        )
        );

        $this->addElement($csvFile);
        $this->addElement($submit);

        $this->setElementDecorators(array(
            'ViewHelper',
            'Label',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'element')),
        ));

        $csvFile->setDecorators(array(
            'File',
            'Label',
            array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'element')),
        ));

        $csvFile->removeDecorator('Label');
        $submit->removeDecorator('Label');

    }

    public function isValid($data)
    {
        $valid = parent::isValid($data);
        foreach ($this->getElements() as $key => $elem)
        {
            if ($elem->hasErrors())
            {
                $elem->addDecorator('Errors', array('placement' => 'APPEND'));
                $elem->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'element error'));
            }
        }
        if (!$valid)
        {
            $this->removeDecorator('FormErrors');
        }

        return $valid;
    }

}

