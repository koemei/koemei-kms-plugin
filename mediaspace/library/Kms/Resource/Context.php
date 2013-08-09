<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 *	Resource to handle application context
 *
 *	@author talbone
 */
class Kms_Resource_Context extends Zend_Application_Resource_ResourceAbstract
{
	/**
     * (non-PHPdoc)
     * @see Zend_Application_Resource_ResourceAbstract::init()
     */
	public function init()
    {
    }

	/**
	 *	get the playback context - basically the category id
	 *
	 *	@param Zend_Controller_Request_Http $request - the request
	 *	@return string/null - the playback context.
	 */
	public static function getPlaybackContext(Zend_Controller_Request_Http $request) 
	{
		$categoryId = null;

    	$referer = $request->getHeader('referer');
        if (empty($referer)) {
            // no referer
            return $categoryId;
        }

    	$urlparts = parse_url($referer);
        if (empty($urlparts['path'])) {
            // no path in referer
            return $categoryId;
        }

        // remove the baseurl from the path
        $params = $urlparts['path'];
        $baseurl = Zend_Controller_Front::getInstance()->getBaseUrl();
        if (!empty($baseurl)) {
            $params = str_replace($baseurl, '', $params);
        }
        $params = ltrim($params,'/');
        // now we have something like channel/cha_nnel/8437971 or category/Nameri/8033251

        if (empty($params)) 
        {
            // we got here through the hp. lets get the first nav item

            $nav = Kms_Resource_Nav::getContainer();
            $pages = $nav->getPages();

            // check if we have navigation items at all
            if (!empty($pages)) {
                $pages = array_values($pages);
                $page = $pages[0];

                // test if the nav item is an mvc item (internal kms)
                if ($page instanceof Zend_Navigation_Page_Mvc){
                    $params = $page->getParams();
                    if (!empty($params['categoryid'])) {
                        // first nav item is a category - use it for context
                        $categoryId = $params['categoryid'];
                    }
                }
            }
        }
        else
        {
            // referer was a nav item. but of what type?

            $params = explode('/', $params);
            $controller = $params[0];
            if ($controller == 'category' || $controller == 'channel') {
            	// referer was gallery or channel, get category id
            	if (count($params) >= 3) {
            		// got id
            		$categoryId = $params[2]; 
            	}
            	else {
            		if ($controller == 'category') {
            			$model = Kms_Resource_Models::getCategory();
            		}
            		else {
            			$model = Kms_Resource_Models::getChannel();
            		}
            		$category = $model->get($params[1]);
            		$categoryId = $category->id;
            	}
            }
        }
        return $categoryId;
	}
}