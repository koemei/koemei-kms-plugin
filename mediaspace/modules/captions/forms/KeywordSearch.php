<?php
/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Form for captions search. Extends the application keyword search.
 * Mainly changes the ids of the elements.
 *
 * @author talbone
 */
class Captions_Form_KeywordSearch extends Application_Form_KeywordSearch
{
    public function __construct($options)
    {
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();

        $text = $this->getElement('keyword');
        $text->setattrib('id', 'search_within_captions');
        
        $searchButton = $this->getElement('search');
        $searchButton->setAttribs(
            array(
                'onclick' => 'if($("#search_within_captions").val() == defaultKeyword ){return false;}',
                'id' => 'do_search_within_captions',
                'type' => 'submit'
            )
        );
                
        // resubmit the form with an empty value
        $cancelOnClick = "
                        if($('#search_within_captions').val() != '' && $('#search_within_captions').val() != defaultKeyword) 
                        { 
                            $(this).hide(); 
                            $('#search_within_captions').val('');
                            $('#search_within_captions').removeClass('keyword').blur();
                        }";
        
        $resetButton = $this->getElement('reset');
        $resetButton->setAttribs(
            array(
                'type' => 'button',
                'onclick' => $cancelOnClick,
                'id' => 'reset_search_within_captions',
                'class' => $this->_keyword ? 'reset' :'hidden reset',
            )
        );

        // this field will receive its value from the filter bar
        $language = new Zend_Form_Element_Hidden('lang');
        $language->setDecorators(array('ViewHelper'));
        $this->addElement($language);

        // this field will receive its value from the filter bar
        $sort = new Zend_Form_Element_Hidden('sort');
        $sort->setDecorators(array('ViewHelper'));
        $this->addElement($sort);
    }
}

