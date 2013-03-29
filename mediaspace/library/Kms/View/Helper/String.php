<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/


/**
 * View Helper to help manipulate strings
 *
 * @author leon
 */
class Kms_View_Helper_String extends Zend_View_Helper_Abstract
{

    //put your code here
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function String()
    {
        return $this;
    }

    public static function formatDuration($value)
    {
        $hours = str_replace(" ", "", sprintf("%02d", $value / 3600));
        $mins = str_replace(" ", "", sprintf("%02d", ($value % 3600) / 60));
        $secs = sprintf("%02d", $value % 60);
        return $hours != "00" ? "$hours:$mins:$secs" : "$mins:$secs";
    }

    public static function shortenString($value, $maxLength)
    {
        return substr($value, 0, $maxLength);
    }

    public static function shortenDescription($desc, $maxLength = 430)
    {
    	$desc = strip_tags($desc);
        if(strlen($desc) > $maxLength)
        {
            $desc = substr($desc, 0, $maxLength);
            $desc = substr($desc, 0, strrpos($desc, ' '));
            $desc .= '...';
        }
        return $desc;
    }

    public static function escapeQuotes($value)
    {
        $value = iconv("UTF-8", "UTF-8//IGNORE", $value);
        $value = htmlentities($value, ENT_QUOTES, "UTF-8");
        /* 		$value = str_replace('"', "&quot;", $value);
          $value = str_replace("'", "&rsquo;", $value); */
        return $value;
    }

    public static function htmlEntities($value)
    {
        $value = iconv("UTF-8", "UTF-8//IGNORE", $value);
        $value = htmlentities($value, ENT_QUOTES, "UTF-8");
        return $value;
    }

    public static function escapeComma($value)
    {
        $value = str_replace("‚", "&sbquo;", $value);
        return $value;
    }

    /**
     * create an array where each element is the list of category names from the category root to a given category
     * @param array $categories category objects to format
     * @return array
     */
    public static function formatCategories($categories)
    {
        $catsArr = array();
        if(!empty($categories))
        {
	        $rootCat = Kms_Resource_Config::getRootGalleriesCategory();
	        $rootCatLen = strlen($rootCat);
            foreach ($categories as $cat)
            {
                if (substr($cat->fullName, 0, $rootCatLen) == $rootCat)
                {
                	$catsArr[] = self::getParts($cat, $rootCat);
                }
            }
        }
        return $catsArr;
    }
    
    public static function formatChannels($categories)
    {
    	$catsArr = array();
        if(!empty($categories))
        {
	        $rootCat = Kms_Resource_Config::getRootChannelsCategory();
	        $rootCatLen = strlen($rootCat);
            foreach ($categories as $cat)
            {
                if (substr($cat->fullName, 0, $rootCatLen) == $rootCat)
                {
                	$catsArr[] = self::getParts($cat, $rootCat);
                }
            }
        }
        return $catsArr;
    }
    
	
    private static function getParts(Kaltura_Client_Type_Category $cat, $rootcatName) 
    {
    	// remove path upto the root cat from category fullname
        $catName = preg_replace('#^'.preg_quote($rootcatName,'/').'>#', '', $cat->fullName);
		// names of required categoeris
        $catsNames = explode(">", $catName);
        // ids of all categories
        $catsIds = explode (">", $cat->fullIds);

        $n = count($catsIds) - count($catsNames);
        // remove ids upto the root category
        array_splice($catsIds, 0, $n);
                    
        $catParts = array();
                    
        $nameIterator = new ArrayIterator($catsNames);
        $idIterator = new ArrayIterator($catsIds);
                    
		for($nameIterator->rewind(), $idIterator->rewind(); $nameIterator->valid() && $idIterator->valid(); $nameIterator->next(), $idIterator->next())
		{
			$cid = $idIterator->current();
			$cname = $nameIterator->current();
            $catParts[$cid] = $cname; 
        }
        
        return $catParts;
    }
    
    
    public static function getAuthorNameFromTags($tagsStr)
    {
        $tags = explode(", ", $tagsStr);
        foreach ($tags as $tag)
        {
            if (strpos($tag, "displayname_") !== false)
            {
                $dispName = substr($tag, 12);
                return $dispName;
            }
        }
        return false;
    }

    public static function removeAuthorNameFromTags($tagsStr)
    {
        return trim(preg_replace('/(displayname_[^,]+,?)/', '', $tagsStr));   
    }
    
    public static function compareToLower($a, $b)
    {
        $same = false;
        if (strtolower($a) == strtolower($b))
            $same = true;
        return $same;
    }
    
    public static function formatCategoryName($categoryName)
    {
        return preg_replace('/^[0-9]*_(.*)/', '$1',$categoryName);
    }
    
    
    /**
     * replace special characters in category name so it can be used in links.
     * only replaces forward slashes, because they confuse zend urls.
     * @param string $categoryName
     * @return string
     */
    public static function removeSpecialChars($categoryName)
    {
        return str_replace('/', '_', $categoryName);
    }

}