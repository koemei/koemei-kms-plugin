<?php

/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * 
 * Plugin for Sap "All" Item in the Home tab
 * author: Ofer
 * 
 */

class Kms_Plugin_Sap extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $container = Kms_Resource_Nav::getContainer();
        foreach($container->getPages() as $page)
        {
            if($page->getLabel() == 'Topics')
            {
                $newPage = new Zend_Navigation_Page_Mvc(
                        array(
                            'label' => 'All Media',
//                            'order' => -999,
                            'controller' => 'gallery',
                            'action' => 'view',
                            'route' => 'categoryid',
                            'params' => array(
                                        'catid' => 'Topics',
                                        ),                            
                            )
                        );
                        
 //               $newPage->setOrder(-999);
                $pages = $page->getPages();
                foreach($pages as $pageToCheck)
                {
                    if ($pageToCheck->getLabel() == 'All Media') return;
                }
                
                foreach($pages as $pageToRemove)
                {
                    $page->removePage($pageToRemove);
                }
                $page->addPage($newPage);
                foreach($pages as $pageToAdd)
                {
                    $page->addPage($pageToAdd);
                }
            }
        }
    }
}

?>
