<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * View Helper to generate a thumb asset url.
 *
 * @author talbone
 *
 */
class Kms_View_Helper_ThumbAssetUrl extends Zend_View_Helper_Abstract
{
    public $view;    

    /**
     *	generate a thumb asset url using the client->serve() method.
     *
     *  @param string thumbAssetId - the is of the thumb asset.
     *  @param unknown $version - the version
     *	@param Kaltura_Client_Type_ThumbParams $thumbParms - the thumb params.
     *	@return string the thumb asset url.
     */
    public function thumbAssetUrl($thumbAssetId, $version = null, Kaltura_Client_Type_ThumbParams $thumbParams = null)
    {
        static $client;
        if (!isset($client)) {
            // we create a new client to avoid including the ks in the url
            $clientConfig = new Kaltura_Client_Configuration();
            $clientConfig->clientTag = "KMS ".Kms_Resource_Config::getVersion().', build '.BUILD_NUMBER;            
            $clientConfig->serviceUrl = Kms_Resource_Config::getConfiguration('client', 'serviceUrl');
            $client = new Kaltura_Client_Client($clientConfig);
        }
        
    	return $client->thumbAsset->serve($thumbAssetId, $version, $thumbParams);
    }
}