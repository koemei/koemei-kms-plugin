<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for modifying the Gallery Search Forms - in channel search, gallery search, and my-media search.
 * The output of this interface is being wrapped in predetermined html. Be sure to return only data - no markup.
 * The filter bar itself is rendered using the view hook Kms_Resource_Viewhook::CORE_VIEW_HOOK_GALLERY_SEARCHES.
 * Implement this interface if you want to add to the search forms (link above, changed filter bar).
 *
 * @author talbone
 */

interface Kms_Interface_Functional_Search_ModifyGallery
{
    /**
     * returns the text to show in the link above the search box.
     */
    public function getLinkText();

    /**
     * returns the default text to show in the search box.
     */
    public function getDefaultText();

}