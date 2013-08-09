<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of KeywordSearch
 *
 * @author leon
 */
class Application_Form_KeywordSearch extends Zend_Form
{
    public $_keyword = null;
    public  $_defaultKeyword = null;
    protected $_translate = null;
    protected $view; 
    protected $_options = array();

    public function __construct($options)
    {
        $this->_options = $options;
        parent::__construct();
    }
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');
        $this->setMethod(self::METHOD_POST);
        
        $this->_defaultKeyword = isset($this->_options['defaultKeyword']) ? $this->_options['defaultKeyword'] : $this->_translate->translate('Search');
        $defaultKeyword = $this->_defaultKeyword;
        $this->_keyword = isset($this->_options['keyword']) ? $this->_options['keyword'] : '';

        foreach(Kms_Resource_Config::getModulesForInterface('Kms_Interface_Form_KeywordSearch_Modify') as $name => $model)
        {
            $model->editKeywordSearch($this);
        }
        
        $text = new Zend_Form_Element_Text(
             array(
                'name' => 'keyword', 
            )
        );
        $text->setValue($this->_keyword ? $this->_keyword : $this->_defaultKeyword); 
        $text->setAttribs(   
                array(
                    'onfocus' => $this->_keyword ? '' : 'if($(this).val() == "' .$defaultKeyword. '") {$(this).val(""); $(this).addClass("focus");}',
                    'onblur' => $this->_keyword ? '' : 'if($(this).val() == "") { $(this).val("' .$defaultKeyword .'");$(this).removeClass("focus");}',
                    'id' => 'search_within_text',
                    'class' => $this->_keyword ? 'focus keyword' : '',
                )
        );
        $this->addElement($text);
        
        $searchButton = new Zend_Form_Element_Button(array('name' => 'search'));
        $searchButton->setValue($this->_translate->translate('Search'));
        $searchButton->setAttribs(
            array(
                'onclick' => 'if($("#search_within_text").val() == defaultKeyword ){return false;}',
                'id' => 'do_search_within',
                'type' => 'submit'
            )
        );
        
        $this->addElement($searchButton);
        
        // resubmit the form with an empty value
        $cancelOnClick = "if($('#search_within_text').val() != '' && $('#search_within_text').val() != defaultKeyword) { $(this).hide(); $('#search_within_text').val('');$('#search_within').submit();$('#search_within_text').removeClass('keyword').blur();}";
        
        $resetButton = new Zend_Form_Element_Button(array('name' => 'reset'));
        $resetButton->setValue($this->_translate->translate('Reset'));
        $resetButton->setAttribs(
            array(
                'type' => 'button',
                'onclick' => $cancelOnClick,
                'id' => 'reset_search_within',
                'class' => 'hidden reset',
            )
        );
        $this->addElement($resetButton);
        
        $this->setElementDecorators(array(
            'viewHelper',
        ));      
        $text->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
        $this->setDecorators(array('FormElements','Form'));
        
    }
    
}

