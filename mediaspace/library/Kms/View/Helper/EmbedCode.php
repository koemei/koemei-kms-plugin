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
class Kms_View_Helper_EmbedCode extends Zend_View_Helper_Abstract
{
    public $view;
    private $template = '<object id="kaltura_player_{UID}" name="kaltura_player_{UID}" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="{HEIGHT}" width="{WIDTH}" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:{MEDIA}" resource="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}" data="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="{FLASHVARS}&amp;{FLAVOR}" /><param name="movie" value="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}{ENTRY_ID}" />{ALT} {SEO}</object>';
    private $kalturaLinks = '<a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a><a href="http://corp.kaltura.com/technology/video_player">video player</a>';
    private $mediaSeo = '<a rel="media:thumbnail" href="{THUMBNAILURL}/width/120/height/90/bgcolor/000000/type/2" ></a> <span property="dc:description" content="{DESCRIPTION}"></span> <span property="media:title" content="{NAME}"></span> <span property="media:width" content="{WIDTH}"></span> <span property="media:height" content="{HEIGHT}"> </span><span property="media:type" content="application/x-shockwave-flash"></span> <span property="media:duration" content="{DURATION}"></span>';

    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    /**
     *
     * @param array $params params to replace the macros {var}
     * @param string $template the template of the embed code
     * @param string $mediaSeo media seo template
     * @param string $kalturaLinks kaltura links
     * @return string 
     */
    public function EmbedCode($params, $template = null, $mediaSeo = null, $kalturaLinks = null)
    {
        $out = is_null($template) ? $this->template : $template;
        $out = preg_replace('/{SEO}/', is_null($mediaSeo) ? $this->mediaSeo : $mediaSeo, $out);
        $out = preg_replace('/{ALT}/', is_null($kalturaLinks) ? $this->kalturaLinks : $kalturaLinks, $out);
        
        foreach($params as $name => $value)
        {
            $out = preg_replace('/\{'.$name.'\}/', $value, $out);
        }
        
        return $out;
    }
}

?>
