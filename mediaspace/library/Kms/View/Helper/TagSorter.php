<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Description of Kms_View_Helper_TagSorter
 * legacy from older version of mediaspace
 * this class sorts an aray of tags from an entry array by relevance 
 * 
 * @author leon
 */
class Kms_View_Helper_TagSorter
{
    private $tagArr;

    /**
     * checks for the number of matches in the entry's tags versus the original entry's tags
     * 
     * @param Kaltura_Client_Type_BaseEntry $entry
     * @return int 
     */
    function numOfMatches($entry) 
    {
        return count(array_intersect($this->tagArr, explode(", ", $entry->tags)));
    }

    /**
     * helper function for usort()
     * @param int $a
     * @param int $b
     * @return int
     */
    function cmp($a, $b) 
    {
        return $this->numOfMatches($a) < $this->numOfMatches($b) ? 1 : -1;
    }

    /**
     *
     * @param array $tags tags for the original entry
     * @param array $arr array of entries
     * @param int $limit limit by number of tags
     * @return type array
     */
    public function tagSort($tags, $arr, $limit) 
    {
        $this->tagArr = $tags;
        usort($arr, array($this, 'cmp'));
        $arr = array_slice($arr, 0, $limit);
        return $arr;
    }
}


?>
