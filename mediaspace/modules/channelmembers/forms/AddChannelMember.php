<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
* To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Channelmembers_Form_AddChannelMember extends Zend_Form
{
    const FORM_NAME = 'AddChannelMember';

    private $view;
    
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        //reset the form decorators (dl)
        $this->setDecorators(array('FormElements','Form',array('FormErrors', array('placement' => 'PREPEND', 'label' => false)) ));

        //custom decorator
        $customDecorator = array(
                'ViewHelper',
                array(
                    'Label',
                    array('separator' => ' ')
                ),
        );

        // form id
        $this->setAttrib('id', 'addChannelMember');

        // user id - actual data field
        //$element = new Zend_Form_Element_Hidden('userId',array('value' => '', 'belongsTo' => self::FORM_NAME, 'id' => 'userId', 'label' => 'User Name', 'required' => 1));
        //$element->setDecorators($customDecorator);
        //$element->removeDecorator('Label');
        //$this->addElement($element);

        // user name - autocomplete field
        
        $userId = new ZendX_JQuery_Form_Element_AutoComplete('userId',
        array
        (
                'label' => $translate->translate('Enter user name'),
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'jQueryParams' => array(
                        'minLength' => 3,
                        'select' => new Zend_Json_Expr('
                        function(event,ui){
                            if(ui.item.id){
                                $("#AddChannelMember-userId").val(ui.item.id);
                                event.preventDefault();
                            }
                         }
                        '),
                ),

                'value' => $this->view->defaultText,
                'onfocus' => 'if($(this).val() == "' . $this->view->defaultText . '") {$(this).removeClass("default-text");}',
                'onclick' => 'if($(this).val() != "' . $this->view->defaultText . '") {$(this).select();} else {$(this).val("");}',
                'onkeydown' => 'if($(this).val() == "' . $this->view->defaultText . '") {$(this).removeClass("default-text");$(this).val("");}',
                'onblur' => 'if($(this).val() == "") { $(this).addClass("default-text");$(this).val("' . $this->view->defaultText . '");}',
                'class' => 'default-text',
        )
        );
        $userId->addDecorator('Label');
        $userId->removeDecorator('HtmlTag');
        $userId->removeDecorator('Errors');
        $userId->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
        $this->addElement($userId);


        // permission
        $permission = new Zend_Form_Element_Select('permission', array(
                'label' => $translate->translate('Set permission'),
                'required' => 1,
                'belongsTo' => self::FORM_NAME,
                'decorators' => $customDecorator
        ));
        $permission->addDecorator(array('elementDiv' => 'HtmlTag'), array('tag'=>'div'));
        $permission->addValidator(new Zend_Validate_InArray(array('haystack' => array_keys(Channelmembers_Model_Channelmembers::getChannelPermissions()))));
        $this->addElement($permission);
    }
    
}