<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * View Helper to highlight matches in strings
 * @author talbone
 *
 */
class Kms_View_Helper_HighlightMatches extends Zend_View_Helper_Abstract
{   
    /**
     * function to find the searched keywords and wrap them with <strong> tag
     */
    public function highlightMatches($content, $searchText)
    {
        $keywords = explode(' ', $searchText);
        foreach ($keywords as $word)
        {
            // removing spaces from the word
            $exactWord = trim($word);
            //if the word was only space(s) - ignore it
            if(!$exactWord) continue;

            $pattern = "/\b$exactWord\b/iu";

            // '!'' at the pattern edges warrant different patterns, because (\b!) and (!\b) do not return a match.
            // '!' suffix -  exactWord![space]
            // '!' prefix - [non word char]!exactWord - not symmetrical, as is the server behaviour.
            $pattern = str_replace('!\b/', '!(\s|$)/', $pattern);
            $pattern = str_replace('/\b!', '/(\W|^)!', $pattern);

            $content = preg_replace($pattern, '<strong>$0</strong>', $content);
        }
        
        return $content;
    }
}