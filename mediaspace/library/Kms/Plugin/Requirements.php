<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/*
 * plugin for initializing the checking of system requirements
 * only initialized when not in production environment
 * @author Gonen
 */



class Kms_Plugin_Requirements extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        $requirements = new Kms_Setup_Requirements(true);

        if(!$requirements->getStatus())
        {
            $layout = Zend_Layout::getMvcInstance();
            $layout->getView()->requirements = $requirements->getFailed();
        }
    }
}
?>
