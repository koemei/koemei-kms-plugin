<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Form_EditUser extends Zend_Form
{

    private $view;
    
    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');

        $newuser = new Zend_Form_Element_Hidden('newuser', array(
            'required' => 1,
        ));
        $newuser->setValue('1');
        $this->addElement($newuser);
        
        $notes = new Kms_Form_Element_Note('notes', array(
            'value' => '
                <div id="notes"><a href="#" onclick="$(\'#editusernotes\').slideToggle(); return false">'.$this->_translate->translate('Click for Notes').'...</a>
					<ul id="editusernotes" style="display: none;">
						<li>'.$this->_translate->translate('User ID, password and email may only contain these characters: 0-9, full alphabet of most languages (utf-8) and ~ . ! @ # $ % _ - +.&nbsp; Extra data can contain anything but a single-quote (\\\'), double-quote (") or backslash (\\\).').'</li>
						<li>'.$this->_translate->translate('To store name-value pairs in "Extra data": separate names from values with the equals sign (=) and separate name-value sets using a comma (,), e.g. name=value,lorem=ipsum,one=two').'</li>
						<li>'.$this->_translate->translate('Passwords cannot be retrieved but only overwritten').'</li>
					</ul>
				</div>'
        ));
        $notes->removeDecorator('Label');
        $this->addElement($notes);
        
        $username = new Zend_Form_Element_Text('username', array(
            'label' => $this->_translate->translate('User ID'),
            'required' => 1
        ));
        $this->addElement($username);
        $usernameValidator = new Zend_Validate_StringLength(array('min' => 3));
        $username->addValidator($usernameValidator);

        $firstName = new Zend_Form_Element_Text('firstname', array(
            'label' => $this->_translate->translate('First Name'),
            'required' => 1
        ));
        $this->addElement($firstName);

        $lastName = new Zend_Form_Element_Text('lastname', array(
            'label' => $this->_translate->translate('Last Name'),
            'required' => 1
        ));
        $this->addElement($lastName);

        $pwValidator = new Zend_Validate_StringLength(array('min' => 6, 'max' => 30));
        $password = new Zend_Form_Element_Text('password', array(
            'label' => $this->_translate->translate('Password'),
            'required' => 1,
        ));
        $password->addValidator($pwValidator);
        $this->addElement($password);
        
        $password2 = new Zend_Form_Element_Text('password2', array(
            'label' => $this->_translate->translate('Confirm Password'),
            'required' => 1,
        ));
        $password2->addValidator($pwValidator);
        $this->addElement($password2);
        
        
        
        $selectRoles = Kms_Resource_Config::getApplicationRoles();
        
        $emailPasswordLink = '';
        $userDetails = isset($this->view->user) ? $this->view->user : null;
        $forgotPasswordConfig = Kms_Resource_Config::getConfiguration('auth', 'forgotPassword');
        if($forgotPasswordConfig)
        {
            $emailPasswordLink = 
                '|&nbsp; <a id="email_pw" href="'.

                'mailto:'.
                ($userDetails && isset($userDetails->email) ? $userDetails->email : '').
                '?subject='.(isset($forgotPasswordConfig->reminderSubject) ? $forgotPasswordConfig->reminderSubject : '') .
                '&body='.(isset($forgotPasswordConfig->reminderBody) ? $forgotPasswordConfig->reminderBoredy : '') .
                '">'.$this->_translate->translate('send by email').'</a>';
        }
        
        $passwordLinks = new Kms_Form_Element_Note('passwordLinks', array(
            'value' => '<div class="pwActions"><a href="'.$this->view->baseUrl('/admin/generate-password').'" rel="async">'.$this->_translate->translate('generate new password').'</a> / <a id="reset_pw" href="#" onclick="$(\\\'#user-password,#user-password2\\\').val(\\\'\\\'); return false;">undo</a>&nbsp;'.$emailPasswordLink.'</div>'
        ));
        $passwordLinks->removeDecorator('label');
        $this->addElement($passwordLinks);
        
        
        $role = new Zend_Form_Element_Select('role', array(
            'label' => $this->_translate->translate('Role'),
            'required' => 1
        ));
        
        $role->setMultiOptions($selectRoles);

        $this->addElement($role);
        
        $email = new Zend_Form_Element_Text('email', array(
            'label' => $this->_translate->translate('Email'),
        ));
        $email->addValidator( new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS |
                    Zend_Validate_Hostname::ALLOW_LOCAL));
        
        $this->addElement($email);
        
        $extradata = new Zend_Form_Element_Textarea('extradata', array(
            'label' => $this->_translate->translate('Extra Data'),
            'rows' => 3,
        ));
        
        $this->addElement($extradata);
        
        
        $this->setElementsBelongTo('user');
        
        foreach($this->getElements() as $element)
        {
            $element->addDecorators(array(
                array('Errors', array('placement' => 'PREPEND')),
                array('Label', array('optionalSuffix' => ' :', 'requiredPrefix' => '* ', 'requiredSuffix' => ' ('.$this->_translate->translate('Required').'):')),
            ));
            
        }
        
        $this->setDecorators(array(
            'FormElements',
//            'Fieldset',
            //array('FormErrors', array('placement' => 'PREPEND')),
            'Form',
        ));        

        
        
    }
}

