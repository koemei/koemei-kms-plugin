<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * Description of Models
 *
 * @author leon
 */
class Kms_Resource_Models 
{
    private static $_entry;
    private static $_category;
    private static $_channel;
    private static $_user;
    private static $_playlist;
    
    public function init()
    {
        
        
    }
    
   /**
    * 
    * @param Application_Model_Entry $model
    */
    public static function setEntry(Application_Model_Entry $model)
    {
        self::$_entry = $model;
    }
    
    /**
     *
     * @return Application_Model_Entry  
     */
    public static function getEntry()
    {
        if(!is_a(self::$_entry, 'Application_Model_Entry'))
        {
            self::$_entry = new Application_Model_Entry();
        }
        
        return self::$_entry;
    }

//    /**
//     * @deprecated
//     *  We don't want to let anyone override categories model - it should be used as a singleton.
//     * @param Application_Model_Category $model
//	   *
//     */
//    public static function setCategory(Application_Model_Category $model)
//    {
//        self::$_category = $model;
//    } 
     
    /**
     * @return Application_Model_Category  
     */
    public static function getCategory()
    {
        if(!is_a(self::$_category, 'Application_Model_Category'))
        {
            self::$_category = new Application_Model_Category();
        }
        
        return self::$_category;
    }
    
//    /**
//     * @deprecated
//     *  We don't want to let anyone override the model - it should be used as a singleton.
//     * @param Application_Model_Channel $model
//     */
//    public static function setChannel(Application_Model_Channel $model)
//    {
//        self::$_channel = $model;
//    }
    
    /**
     * @return Application_Model_Channel  
     */
    public static function getChannel()
    {
        if(!is_a(self::$_channel, 'Application_Model_Channel'))
        {
            self::$_channel = new Application_Model_Channel();
        }
       
        return self::$_channel;
    }
    
    /**
     * 
     * @param Application_Model_User $model
     */
    public static function setUser(Application_Model_User $model)
    {
        self::$_user = $model;
    }
    
    /**
     *
     * @return Application_Model_User
     */
    public static function getUser()
    {
        if(!is_a(self::$_user, 'Application_Model_User'))
        {
            self::$_user = new Application_Model_User();
        }
       
        return self::$_user;
    }
    
   /**
    * 
    * @param Application_Model_Playlist $model
    */
    public static function setPlaylist(Application_Model_Playlist $model)
    {
        self::$_playlist = $model;
    }
    
    /**
     *
     * @return Application_Model_Playlist
     */
    public static function getPlaylist()
    {
        if(!is_a(self::$_playlist, 'Application_Model_Playlist'))
        {
            self::$_playlist = new Application_Model_Playlist();
        }
       
        return self::$_playlist;
    }
    
    
}

?>
