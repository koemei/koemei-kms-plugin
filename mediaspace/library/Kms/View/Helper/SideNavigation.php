<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of EmbedCode
 *
 * @author leon
 */
class Kms_View_Helper_SideNavigation extends Zend_View_Helper_Abstract
{
    public $view;
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function SideNavigation()
    {
//        $nav = Zend_Registry::isRegistered('Zend_Navigation') ? Zend_Registry::get('Zend_Navigation') : null;
        $nav = $this->view->navigation()->getContainer();
        if(!$nav->getPages())
        {
            $nav = Kms_Resource_Nav::getContainer();
            $this->view->navigation()->setContainer($nav);
            
        }
        $branch = $this->view->navigation()->findActive($nav, 0, 0);
        $active = $this->view->navigation()->findActive($nav);
        $out = '';
        if (0 != count($branch)) 
        {
            $pages = $branch['page']->getPages();
            
            if(count($pages))
            {
		//'<div class="heading"><h1>'.$active['page']->getLabel().'</h1></div>'
                $out = $this->recursePages($pages);
            }

        }
        return $out;
    }
    
    public function recursePages($pages)
    {
        $ret = "<ul>";
        foreach ($pages as $page) 
        {
            $liClass = $page->isActive() ? 'class="active"' : '';
            $ret .= '<li '.$liClass.'>';
            $ret .= $this->view->navigation()->menu()->htmlify($page);
            $subPages = $page->getPages();
            
            if(count($subPages))
            {
                $ret .= $this->recursePages($subPages);
            }
            $ret .= '</li>';
        }
        $ret .= "</ul>";
        return $ret;
    }
    
    
}

?>
