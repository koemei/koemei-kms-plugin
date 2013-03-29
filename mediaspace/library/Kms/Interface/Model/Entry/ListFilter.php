<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface to allow modules to modify media->list filter object before API call.
 * Implement this interface if you want to affect the filter sent to Kaltura to expand/contract the search results.
 *
 */
interface Kms_Interface_Model_Entry_ListFilter
{
    const CONTEXT_TAG_SEARCH = 'tagSearch'; // gallery controller - when clicking on tag
    const CONTEXT_USER_SEARCH = 'userSearch'; // gallery controller - when clicking on user
    const CONTEXT_KEYWORD_SEARCH = 'keywordSearch'; // gallery controller - when searching in search-box
    const CONTEXT_CATEGORY_SEARCH = 'categorySearch'; // gallery & entry controller - when listing by category
    const CONTEXT_LIST_MYMEDIA = 'myMedia';

    /**
     * Function gets the current filter as was prepared for the API call and returns a modified filer.
     *
     * @param Kaltura_Client_Type_BaseEntryFilter $filter
     * @param string $context the context in which the search API call is done
     *
     * @return Kaltura_Client_Type_BaseEntryFilter
     */
    public function modifyFilter(Kaltura_Client_Type_BaseEntryFilter $filter, $context);	
}