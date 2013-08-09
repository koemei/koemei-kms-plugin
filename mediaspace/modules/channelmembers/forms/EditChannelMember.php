<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

class Channelmembers_Form_EditChannelMember extends Channelmembers_Form_AddChannelMember
{    
    public function init()
    {        
        $translate = Zend_Registry::get('Zend_Translate');
        
        parent::init();
        $this->removeDecorator('FormErrors');
        // change the user name element to hidden        
//        $this->removeElement('userName');
        $userId = new Zend_Form_Element_Hidden('userId', array(
                'belongsTo' => parent::FORM_NAME
        ));
        $userId->removeDecorator('label');
        $userId->removeDecorator('DtDdWrapper');     
        $userId->removeDecorator('HtmlTag');
        $this->addElement($userId);     
        // remove the label from the permission element
        $permission = $this->getElement('permission');
        $permission->removeDecorator('label');
        $permission->removeDecorator('elementDiv');
        
        // submit
        $submit = new Zend_Form_Element_Submit(array('belongsTo' => self::FORM_NAME, 'name' => 'submit'));
        $submit->setLabel($translate->translate('Done'));       
        $submit->removeDecorator('label'); 
        $submit->removeDecorator('DtDdWrapper');
        $this->addElement($submit);        
    }
}