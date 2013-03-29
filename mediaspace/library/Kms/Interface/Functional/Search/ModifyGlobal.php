<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 * Interface for modifying the Global Search Form.
 * The output of this interface is being wrapped in predetermined html. Be sure to return only data - no markup.
 * Implement this interface if you want to add to the search forms (link above, changed action).
 *
 * @author talbone
 */

interface Kms_Interface_Functional_Search_ModifyGlobal
{
    /**
     * returns the text to show in the link above the search box.
     */
    public function getGlobalLinkText();

    /**
     * returns the action associated with the link for the specified source.
     */
    public function getGlobalSearchAction();

    /**
     * returns the default text to show in the search box.
     */
    public function getGlobalDefaultText();

}