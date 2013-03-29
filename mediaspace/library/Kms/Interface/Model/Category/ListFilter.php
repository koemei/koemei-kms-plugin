<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface to allow modules to modify category->list filter object before API call.
 * Implement this interface if you want to affect the filter sent to Kaltura to expand/contract the search results.
 *
 */
interface Kms_Interface_Model_Category_ListFilter
{
    /**
     * Function gets the current filter as was prepared for the API call and returns a modified filer.
     *
     * @param Kaltura_Client_Type_CategoryBaseFilter $filter
     * @return Kaltura_Client_Type_CategoryBaseFilter
     */
    public function modifyFilter(Kaltura_Client_Type_CategoryBaseFilter $filter);
}