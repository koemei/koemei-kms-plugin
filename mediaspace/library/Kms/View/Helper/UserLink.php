<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * View Helper to display login/logout link with username
 */

/**
 * Description of UserLink
 *
 * @author leon
 */
class Kms_View_Helper_UserLink extends Zend_View_Helper_Abstract
{
    //put your code here
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function UserLink()
    {
        if(Zend_Auth::getInstance()->hasIdentity())
        {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $role = $identity->getRole();
        //check if the user has a logged in role
            $id = $identity->getId();
        }
        $anonymousGreeting = Kms_Resource_Config::getConfiguration('auth', 'anonymousGreeting');
        
        $username = isset($id) && $id != $anonymousGreeting ? $id : $anonymousGreeting;
        
        $ret = '<li title="'.$username.'" class="userlink"><span>'.$username.'</span> ';
        
        if(isset($role) && $role != Kms_Plugin_Access::getRole(Kms_Plugin_Access::ANON_ROLE) && $role != Kms_Plugin_Access::getRole(Kms_Plugin_Access::EMPTY_ROLE))
        {
            $ret .= ' <span>(<a href="'.$this->view->baseUrl('/user/logout').'">'.$this->view->translate('logout').'</a>)</span>';
        }
        else
        {
            if(Kms_Resource_Config::getConfiguration('auth', 'httpsLogin'))
            {
                $loginUrl = 'https://' . $_SERVER['HTTP_HOST'] . $this->view->baseUrl('/user/login');
            }
            else
            {
                $loginUrl = $this->view->baseUrl('/user/login');
            }
            
            $ret .= ' <span>(<a href="'.$loginUrl.'">'.$this->view->translate('login').'</a>)</span>';
        }
        
        $ret .= '</li>';
        return $ret;
    }
}

