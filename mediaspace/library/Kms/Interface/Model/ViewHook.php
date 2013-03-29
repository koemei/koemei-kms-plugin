<?php
/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 * Interface for modules to return the view-hooks the module wishes to contribute.
 * Implement that interface in your {ModuleName}_Model_{ModuleName} class to integrate into view-hooks mechanism of KMS
 *
 * @author leon
 */
interface Kms_Interface_Model_ViewHook
{
    /**
     * Return associative array that describes when your viewHook is implemented.
     * Example:
     * array( action' => 'tab', 'controller' => 'index', 'order' => '10',)
     * Those details are used to render the action specified.
     *
     * @param string $viewHook the name of the requested viewhook
     * @return array
     */
    function getViewHook($viewHook);

    /**
     * Return associative array of strings, where each key is the name of the viewHook your module exposes
     * and each corresponding value is the description of that viewhook (where it can be seen).
     * Example: array('myNewHook' => 'appears in my custom page, before content');
     *
     * @return array
     */
    function addViewHooks();

    /**
     * Return array of strings, where each string is a name of a viewhook your module implements.
     *
     * @return array
     */
    function getImplementedViewHooks();
    
    /**
     * @deprecated - not called anymore
     * Per view-hook check a module should specify if this view-hook is returned by it
     *
     * @param string $viewHook
     * @return bool
     
    function viewHookExists($viewHook);*/
}

?>
