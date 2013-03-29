<?php

/*
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Description of ContextualAccess
 *
 * @author leon
 */
abstract class Kms_Module_Contextual_Access
{    
    protected $requestParams;
    protected $allowRedirect = false;
    protected $accessDeniedPage = null;

    /**
     * determine if access is allowed by the context.
     *
     * @param array $contextId - request parameters composing the context
     * @return bool
     */
    abstract public function checkAllowed(array $contextId);
    
    /**
     * get the request params
     * @return array - the request params
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * should the controller perform a redirect in case of access denied.
     * if true, see getAccessDeniedPage()
     * 
     * @return bool
     */
    public function getAllowRedirect()
    {
        return $this->allowRedirect;
    }
    
    /**
     * @return array in the format of array('module','controller','action')
     * see Kms_Module_Controller_Abstract
     */
    public function getAccessDeniedPage()
    {
        return $this->accessDeniedPage;
    }
}

