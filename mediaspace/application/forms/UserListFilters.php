<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of UserListFilters
 *
 * @author gonen
 */
class Application_Form_UserListFilters extends Zend_Form
{
    const KEYWORD_SEARCH_BY_EMAIL = 'email';
    const KEYWORD_SEARCH_BY_NAME = 'name';
    
    private $view;

    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        //$this->setMethod('GET');
        $keywordElement = new Zend_Form_Element_Text('keyword');

        $submitElement = new Zend_Form_Element_Submit('searchUsers');

        $byElement = new Zend_Form_Element_Radio('searchBy', array(
            'class' => 'radio-input',
            'separator' => '',
        ));
        $byElement->setMultiOptions(array(
            self::KEYWORD_SEARCH_BY_NAME => 'By Name',
            self::KEYWORD_SEARCH_BY_EMAIL => 'By Email',
        ));

        $roleElement = new Zend_Form_Element_Select('role', array(
            'label' => $this->_translate->translate('Show'). ':',
        ));

        $selectRoles = Kms_Resource_Config::getApplicationRoles();
        $roleOptions = array_merge(array(''=>'All Roles'), $selectRoles);
        $roleElement->addMultiOptions($roleOptions);


        $this->addElement($keywordElement);
        $this->addElement($submitElement);
        $this->addElement($roleElement);
        $this->addElement($byElement);
        foreach($this->getElements() as $key => $elem)
        {
            $this->getElement($key)->setDecorators(array(
                'viewHelper',
                array(
                    'htmlTag',
                    array(
                        'tag' => 'div',
                        'id' => $key,
                    ),
                ),
            ));
        }
        $this->getElement('role')->addDecorator('Label');
        
    }
}
