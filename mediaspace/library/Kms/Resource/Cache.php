<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of Cache
 *
 * @author leon
 */
class Kms_Resource_Cache extends Zend_Application_Resource_ResourceAbstract 
{
    public static $apiCache = null;
    public static $appCache = null;
    public static $trCache = null;
    public static $pageCache = null;
    
    public static $manager = null;
    private static $config = null;
    private static $cacheEnabled = false;
    private static $cacheDebug = false;

    private static $cacheIdPrefix = null;
    
    public function init()
    {
        self::$config = Kms_Resource_Config::getCacheConfig();
        if(isset(self::$config->global) && self::$config->global && isset(self::$config->global->cacheEnabled) && self::$config->global->cacheEnabled)
        {
            self::$cacheEnabled = true;
            
            if(isset(self::$config->global->cacheDebug))
            {
                self::$cacheDebug = self::$config->global->cacheDebug;
            }
            
            if(is_null(self::$apiCache))
            {
                self::$manager = new Zend_Cache_Manager();
                if(!is_null(self::$cacheIdPrefix))
                {
                    self::$config->api->frontend->options->cache_id_prefix = self::$cacheIdPrefix;
                }
                self::$manager->setCacheTemplate('api', self::$config->api);
                self::$apiCache = self::$manager->getCache('api');
                
            }
            
            if(is_null(self::$appCache))
            {

                self::$manager = new Zend_Cache_Manager();
                if(!is_null(self::$cacheIdPrefix))
                {
                    self::$config->app->frontend->options->cache_id_prefix = self::$cacheIdPrefix;
                }
                self::$manager->setCacheTemplate('app', self::$config->app);

                self::$appCache = self::$manager->getCache('app');
                
            }
            
            if(is_null(self::$trCache))
            {

                self::$manager = new Zend_Cache_Manager();
                if(!is_null(self::$cacheIdPrefix))
                {
                    self::$config->tr->frontend->options->cache_id_prefix = self::$cacheIdPrefix;
                }
                self::$manager->setCacheTemplate('tr', self::$config->tr);

                self::$trCache = self::$manager->getCache('tr');
                
            }
            
            if(is_null(self::$pageCache) && isset(self::$config->global) && isset(self::$config->global->enablePageCache) && self::$config->global->enablePageCache)
            {
                self::$manager = new Zend_Cache_Manager();
                if(!is_null(self::$cacheIdPrefix))
                {
                    self::$config->page->frontend->options->cache_id_prefix = self::$cacheIdPrefix;
                }
                self::$manager->setCacheTemplate('page', self::$config->page);
                //$f = new Zend_Cache_Frontend_Page();
                //$f->setOption('regexps', array('^/.*$'));
                self::$pageCache = self::$manager->getCache('page');
                
                $regexArray = array();
                $bootstrap = $this->getBootstrap();
                $request = new Zend_Controller_Request_Http();
                $baseUrl = $request->getBaseUrl();
                
                if(isset(self::$config->page->pages) && count(self::$config->page->pages))
                {
                    foreach(self::$config->page->pages as $pattern)
                    {
                        $pattern = preg_replace('#^(\^/)#', '^'.$baseUrl.'/', $pattern);
                        $regexArray[$pattern] = array('cache'=>true);
                    }
                    self::$pageCache->setOption('regexps', $regexArray);
                    if($request->getParam('nocache'))
                    {
                        self::$pageCache->cancel();
                    }
                    else
                    {
                        header('Kms-Cached: true');
                        self::$pageCache->start();
                    }
                    header('Kms-Cached: false');
                }
            }
        }
    }

    public static function setCacheIdPrefix($prefix)
    {
        $prefix = preg_replace('/\./', '_', $prefix);
        $prefix = preg_replace('/-/', '', $prefix);
        self::$cacheIdPrefix = $prefix;
    }

    public static function getPageCacheObject()
    {
        return self::$pageCache;
    }
    
    
    public static function disableCache()
    {
        self::$cacheEnabled = false;
    }
    
    public static function enableCache(){
    	self::$cacheEnabled = true;
    }
    
    public static function isEnabled()
    {
        return self::$cacheEnabled;
    }
    
    public static function getTranslateCache()
    {
        return self::$trCache;
    }
    
    public static function apiGet($object, $params)
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        $key = self::buildKey($params);
        $res = self::$apiCache->load($object.'_'.$key);
        if(!$res)
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log("cache: missed on ".$object.'_'.$key."- ".$object.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
            }
        }
        else
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log("cache: hit on ".$object.'_'.$key." - ".$object.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
            }
        }

        return $res;
    }

    
    public static function apiSet($object, $params, $data, $tags = array(), $expiry = null)
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        
        $hashTags = array();
        foreach($tags as $tag)
        {
            $hashTags[] = md5($tag);
        }
        $key = self::buildKey($params);
        if(self::$cacheDebug)
        {
            Kms_Log::log("cache: saving to cache ".$object.'_'.$key." - ".$object.', params: '.Kms_Log::printData($params).', tags '.join(' , ', $tags).', data: '.Kms_Log::printData($params), Kms_Log::INFO);
        }
        
        $lifetime = self::$config->api->frontend->options->lifetime;
        if($expiry)
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log('cache: setting cache lifetime for this action to: '.$expiry, Kms_Log::INFO);
            }
            self::$apiCache->setLifetime($expiry);
        }
        self::$apiCache->save($data, $object.'_'.$key, $hashTags);
        
        // return lifetime to original
        self::$apiCache->setLifetime($lifetime);
    }
    
    
    public static function apiClean($object, $params, $tags = array())
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        $hashTags = array();
        foreach($tags as $tag)
        {
            $hashTags[] = md5($tag);
        }
        
        $key = self::buildKey($params);
        if(self::$cacheDebug)
        {
            Kms_Log::log("cache: removing cache ".$object.'_'.$key.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
        }
        self::$apiCache->remove($object.'_'.$key);
        if(count($hashTags))
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log('cache: Cleaning cache for tags '.join(' , ', $tags), Kms_Log::INFO);
            }
            self::$apiCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $hashTags);
        }
    }

    public static function apiWipe()
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        
        self::$apiCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        return true;
    }
    
    
    public static function appGet($object, $params)
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        
        $key = self::buildKey($params);
        $res = self::$appCache->load($object.'_'.$key);
        if(self::$cacheDebug)
        {
            if(!$res)
            {
                Kms_Log::log("cache: missed on ".$object.'_'.$key."- ".$object.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
            }
            else
            {
                Kms_Log::log("cache: hit on ".$object.'_'.$key." - ".$object.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
            }
        }

        return $res;
    }

    
    public static function appSet($object, $params, $data, $tags = array(), $expiry = null)
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        
        $hashTags = array();
        foreach($tags as $tag)
        {
            $hashTags[] = md5($tag);
        }
        $key = self::buildKey($params);
        if(self::$cacheDebug)
        {
            Kms_Log::log("cache: saving to cache ".$object.'_'.$key." - ".$object.', params: '.Kms_Log::printData($params).', tags '.join(' , ', $tags), Kms_Log::INFO);
        }
        
        $lifetime = self::$config->app->frontend->options->lifetime;
        if($expiry)
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log('cache: setting cache lifetime for this action to: '.$expiry, Kms_Log::INFO);
            }
            self::$appCache->setLifetime($expiry);
        }
        self::$appCache->save($data, $object.'_'.$key, $hashTags);
        
        // return lifetime to original
        self::$appCache->setLifetime($lifetime);
    }
    
    
    public static function appClean($object, $params, $tags = array())
    {
        
        if(!self::$cacheEnabled)
        {
            return false;
        }
        $hashTags = array();
        foreach($tags as $tag)
        {
            $hashTags[] = md5($tag);
        }
        
        $key = self::buildKey($params);
        if(self::$cacheDebug)
        {
            Kms_Log::log("cache: removing cache ".$object.'_'.$key.', params: '.Kms_Log::printData($params), Kms_Log::INFO);
        }
        
        self::$appCache->remove($object.'_'.$key);
        if(count($hashTags))
        {
            if(self::$cacheDebug)
            {
                Kms_Log::log('cache: Cleaning cache for tags '.join(' , ', $tags), Kms_Log::INFO);
            }
            self::$appCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $hashTags);
        }
    }

    public static function appWipe()
    {
        if(!self::$cacheEnabled)
        {
            return false;
        }
        
        self::$appCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        return true;
    }
    

    private static function buildKey( $params = array())
    {
        if(!self::$cacheEnabled)
        {
            return '';
        }
        $paramArray = array();
        $paramString = '';
        if(count($params))
        {
            foreach($params as $key => $value)
            {
                $paramArray[$key] = $value;
            }
            $paramString = http_build_query($paramArray);
        }
        
        $hash = md5($paramString);
        return $hash;
    }
    
    
    public static function buildCacheParams($filter = null, $pager = null)
    {
        if(!self::$cacheEnabled)
        {
            return '';
        }
        
        $cacheParams = array();
        if(is_object($filter))
        {
            foreach($filter as $key => $value)
            {
                if(!is_null($value))
                {
                    $cacheParams['filter:'.$key] = $value;
                }
            }
        }
        if(is_object($pager))
        {
            foreach($pager as $key => $value)
            {
                if(!is_null($value))
                {
                    $cacheParams['pager:'.$key] = $value;
                }
            }
        }
        return $cacheParams;
        
    }
    
}

?>
