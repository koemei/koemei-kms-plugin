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
class Kms_View_Helper_PlaylistEmbedCode extends Zend_View_Helper_Abstract
{
    public $view;
    private $template = '<object id="kaltura_player_{UID}" name="kaltura_player_{UID}" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="{HEIGHT}" width="{WIDTH}" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:{MEDIA}" resource="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}" data="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="{FLASHVARS}&{FLAVOR}" /><param name="movie" value="{HOST}/index.php/kwidget/wid/_{PARTNER_ID}/uiconf_id/{UICONF_ID}" />{ALT}</object>';
    private $kalturaLinks = '<a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a><a href="http://corp.kaltura.com/technology/video_player">video player</a>';
//    private $mediaSeo = '<a rel="media:thumbnail" href="{THUMBNAILURL}/width/120/height/90/bgcolor/000000/type/2" ></a> <span property="dc:description" content="{DESCRIPTION}"></span> <span property="media:title" content="{NAME}"></span><span property="media:type" content="application/x-shockwave-flash"></span> <span property="media:duration" content="{DURATION}"></span>';

    private $plflashvars = 'playlistAPI.autoContinue=true&playlistAPI.autoInsert=true&playlistAPI.kpl0Name={PL_NAME}&playlistAPI.kpl0Url={HOST}%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26partner_id%3D{PARTNER_ID}%26subp_id%3D{PARTNER_ID}00%26format%3D8%26ks%3D%7Bks%7D%26playlist_id%3D{PLAYLIST_ID}';
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;

    }
    
    public function PlaylistEmbedCode($params)
    {
        $out = $this->template;
//        $out = preg_replace('/{SEO}/', $this->mediaSeo, $out);
        $out = preg_replace('/{ALT}/', $this->kalturaLinks, $out);
        $out = preg_replace('/{FLASHVARS}/', $this->plflashvars, $out);
        foreach($params as $name => $value)
        {
            $out = preg_replace('/\{'.preg_quote($name, '/').'\}/', $value, $out);
        }
        
        return $out;
    }
}

?>
