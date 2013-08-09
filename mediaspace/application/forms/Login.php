<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

class Application_Form_Login extends Zend_Form
{
    const FORM_NAME = 'Login';
    private $view;
    private $_translate = null;
    
    public function init()
    {
        $this->_translate = Zend_Registry::get('Zend_Translate');
        
        $front = Zend_Controller_Front::getInstance();
        $this->view = $front->getParam('bootstrap')->getResource('view');
        $this->setAttrib('id', 'loginForm');
        $this->setMethod(self::METHOD_POST);
        
        $username = new Zend_Form_Element_Text(
            'username', 
            array(
                'label' => $this->_translate->translate('Username').':', 
                'filters' => array(
                  'StringTrim', 'StringToLower',  
                ),
                'required' => true,
                'decorators' => array(
                    'ViewHelper',
                    'Label',
//                    array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
                    array('HtmlTag', array('tag'=>'div', 'class' => 'element')),
                ),
                'tabindex' => 1,
            )
        );
        $username->setAttrib('autocorrect', 'off');
        $username->setAttrib('autocapitalize', 'off');
        
        $forgotPassword = $this->view->ForgotPasswordLink();
        // for ipad/iphone
        $password = new Zend_Form_Element_Password(
            'password', 
            array(
                'label' => $this->_translate->translate('Password').':', 
                'filters' => array(
                  'StringTrim',  
                ),
                'validators' => array(
                    array('StringLength', false, array(6, 20)),
                ),
                'required' => true,
                'decorators' => array(
                    'ViewHelper',
                    array('Label', array('requiredSuffix' => $forgotPassword, 'escape' => false)),
  //                  array('Errors', array('HtmlTag' => 'div', 'placement' => 'PREPEND')),
                    array('HtmlTag', array('tag'=>'div', 'class' => 'element')),
                ),
                'tabindex' => 2,
            )
                
        );
        
        if (Kms_Resource_Config::getConfiguration('auth', 'authNAdapter') != 'Kms_Auth_AuthN_Kaltura') {
        	// only restrict length if this is Kaltura AuthN
        	$password->removeValidator('StringLength');
        }
        
        $login = new Zend_Form_Element_Button(
            'login', 
            array(
                'required' => false,
                'ignore'   => true,
                'label'    => $this->_translate->translate('Login'),
                'type'  => 'submit',
                'decorators' => array(
                    'ViewHelper',
                    array('HtmlTag', array('tag'=>'div', 'class' => 'button')),
                ),
                'tabindex' => 3,

            )
        );
        
//        $login->setLabel('Login');
        
        $this->addElement( $username );
        $this->addElement( $password );
        
        $this->addElement( $login );

        $this->setElementsBelongTo(self::FORM_NAME);
        
        
        $this->setDecorators(array(
            'FormElements',
            array('Description', array('placement' => 'prepend')),
            array('FormErrors', array('placement' => 'prepend')),
            'Form',
        ));
        
    }
    
    public function removeForgotPassword()
    {
        $password = $this->getElement('password');
        $password->getDecorator('Label')->setOption('requiredSuffix', '');
        
    }
    
    public function isValid($data)
    {
        $valid = parent::isValid($data);
        foreach($this->getElements() as $elem)
        {
            if($elem->hasErrors())
            {
                $elem->addDecorator('Errors', array('placement' => 'APPEND'));
                $elem->addDecorator('HtmlTag', array('tag'=>'div', 'class' => 'element error'));
               /* if($elem->getDecorator('Label'))
                {
                    $elem->addDecorator('Label');
//                    $elem->addDecorator(array('errorDiv' => 'HtmlTag'), array('tag'=>'div', ));
                }*/
            }
        }
        if(!$valid)
        {
            $this->removeDecorator('FormErrors');
        }
        
        return $valid;
        
        
    }
    
    
    
    /**
     * Method for changing the form method and validator for the admin login form
     */
    public function adminLogin()
    {
        // add an email address validator instead of the alphanum validator for username(email)
        $username = $this->getElement('username');
        $username->clearValidators();
        $username->addValidator(new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS |
                    Zend_Validate_Hostname::ALLOW_LOCAL));
        $this->removeForgotPassword();
        // change action to partner-authenticate
        $this->setAction( $this->view->baseUrl('/admin/authenticate'));
    }
    
    
    /** insert referer info into the form
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Application_Form_Login 
     */
    public function trackReferrer(Zend_Controller_Request_Abstract $request)
    {
        $this->addElement('hidden', 'referrer');
        
//        if($request->getControllerName() == 'user' && $request->getActionName() == 'login')
        {
            $this->setDefault(
                'referrer', 
                $request->getParam(
                    'ref', 
                    $request->getServer('HTTP_REFERER')
                )
            );
        }
/*        else 
        {
            $this->setDefault(
                'referrer', 
                $request->getParam(
                    'ref', 
                    $request->getServer('HTTP_REFERER')
                )
            );
        }*/

        // use no decorator for the actual form element
        $this->referrer->setDecorators(array('ViewHelper')); 

        return $this;
        
    }
    /**
     *
     * @param type $default
     * @return type 
     */
    public function getReferrer($default = false)
    {
        if (!isset($this->referrer))
        {
            return $default;
        }
        else 
        {
            $val = $this->referrer->getValue();
            if ($val) 
            {
                return $val;
            }
            else
            {
                return $default;
            }
    
        }
    }

}

