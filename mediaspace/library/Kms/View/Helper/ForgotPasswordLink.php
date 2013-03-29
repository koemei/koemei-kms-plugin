<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of ForgotPasswordLink
 *
 * @author leon
 */
class Kms_View_Helper_ForgotPasswordLink extends Zend_View_Helper_Abstract
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function ForgotPasswordLink()
    {
        $ret = '';
        $config = Kms_Resource_Config::getConfiguration('auth', 'forgotPassword');
        if(isset($config->link) && $config->link)
        {
            if(preg_match('/^mailto:/', $config->link))
            {
                $subj = $config->emailSubject;
                $body = $config->emailBody;
                $ret = '<a href="'.$config->link.'?subject='.$config->emailSubject.'&body='.$config->emailBody.'">'.$this->view->translate('Forgot Password').'?</a>';
            }
            else
            {
                $ret = '<a href="'.$config->link.'" target="_blank">'.$this->view->translate('Forgot Password').'?</a>';
            }
        }
        
        
        return $ret;
    }
}

