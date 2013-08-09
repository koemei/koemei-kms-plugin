<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Viewhook
 *
 * @author gonen
 */
class Kms_Resource_Viewhook extends Zend_Application_Resource_ResourceAbstract
{
    const CORE_VIEW_HOOK_AFTERLOGIN = 'AfterLogin';
    const CORE_VIEW_HOOK_BEFORELOGIN = 'BeforeLogin';
    const CORE_VIEW_HOOK_MYMEDIASIDEBARPOST = 'myMediaSidebarPost';
    const CORE_VIEW_HOOK_MYMEDIASIDEBARPRE = 'myMediaSidebarPre';
    const CORE_VIEW_HOOK_MYMEDIABULK = 'myMediaBulk';
    const CORE_VIEW_HOOK_MYPLAYLISTSSIDE = 'MyPlaylistsSide';
    const CORE_VIEW_HOOK_PLAYERSIDETABLINKS = 'PlayerSideTabLinks';
    const CORE_VIEW_HOOK_PLAYERSIDETABS = 'PlayerSideTabs';
    const CORE_VIEW_HOOK_PLAYERTABLINKS = 'PlayerTabLinks';
    const CORE_VIEW_HOOK_PLAYERTABS = 'PlayerTabs';
    const CORE_VIEW_HOOK_POSTENTRYDETAILS = 'postEntryDetails';
    const CORE_VIEW_HOOK_POSTGALLERY = 'postGallery';
    const CORE_VIEW_HOOK_POSTGALLERYITEMS = 'postGalleryItems';
    const CORE_VIEW_HOOK_POSTMYMEDIA = 'postMyMedia';
    const CORE_VIEW_HOOK_POSTMYMEDIAENTRIES = 'postMyMediaEntries';
    const CORE_VIEW_HOOK_POSTMYPLAYLISTSENTRIES = 'postMyPlaylistsEntries';
    const CORE_VIEW_HOOK_PREGALLERY = 'preGallery';
    const CORE_VIEW_HOOK_PREGALLERYITEMS = 'preGalleryItems';
    const CORE_VIEW_HOOK_PREMYMEDIA = 'preMyMedia';
    const CORE_VIEW_HOOK_PREMYMEDIAENTRIES = 'preMyMediaEntries';
    const CORE_VIEW_HOOK_PREMYPLAYLISTSENTRIES = 'preMyPlaylistsEntries';
    const CORE_VIEW_HOOK_PRESIDENAV = 'preSideNavigation';
    const CORE_VIEW_HOOK_POSTSIDENAV = 'postSideNavigation';
    const CORE_VIEW_HOOK_HEADERMENU = 'headerMenu';
    const CORE_VIEW_HOOK_POSTHEADER = 'postHeader';
    const CORE_VIEW_HOOK_PRE_HEADERUPLOAD = 'preHeaderUpload';
    const CORE_VIEW_HOOK_POST_HEADERUPLOAD = 'postHeaderUpload';
    const CORE_VIEW_HOOK_GALLERY_BUTTONS = 'galleryButtons';
    const CORE_VIEW_HOOK_CHANNEL_BUTTONS = 'channelButtons';
    const CORE_VIEW_HOOK_CHANNEL_SIDENAV = 'channelSideNavigation';
    const CORE_VIEW_HOOK_PRE_CHANNELS = 'preChannels';   
    const CORE_VIEW_HOOK_PRE_CHANNEL = 'preChannel';
    const CORE_VIEW_HOOK_CHANNELTABLINKS = 'channelTabLinks';
    const CORE_VIEW_HOOK_CHANNELTABS = 'channelTabs';
    const CORE_VIEW_HOOK_CHANNELLIST_LINKS = 'channelListLinks';
    const CORE_VIEW_HOOK_FOOTER = 'siteFooter';
    const CORE_VIEW_HOOK_EDIT_ENTRY_TABLINKS = 'editEntryTabLinks';
    const CORE_VIEW_HOOK_EDIT_ENTRY_TABS = 'editEntryTabs';
    const CORE_VIEW_HOOK_EDIT_ENTRY_OPTIONS = 'editEntryOptions';
    const CORE_VIEW_HOOK_GALLERY_SEARCHES = 'gallerySearches';
    const CORE_VIEW_HOOK_ENTRY_PAGE = 'entryPage';
    const CORE_VIEW_HOOK_MODULES_HEADER = 'modulesHeader';
    const CORE_VIEW_HOOK_ADMIN_FOOTER = 'adminFooter';

    private static $_coreViewHooks = array();
    private static $_moduleViewHooks = array();
    private static $_registeredModules = array();
    private static $_viewHooksDescriptionsMap = array();

    public function init()
    {
        self::initCoreViewHooks($this);
        self::initModuleViewHooks();
    }

    private static function initCoreViewHooks($me)
    {
        if(!count(self::$_coreViewHooks))
        {
            $reflection = new ReflectionClass($me);
            $constants = $reflection->getConstants();
            foreach($constants as $constName => $constValue)
            {
                self::$_coreViewHooks[$constValue] = $constValue;
            }
        }
        self::mapViewHookDescriptions();
    }

    private static function mapViewHookDescriptions()
    {
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_AFTERLOGIN] = 'Add html in login page after the form.';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_BEFORELOGIN] = 'Add html in login page before the form';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_MYMEDIASIDEBARPOST] = 'Add HTML content before the sidebar in my media page (on the left)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_MYMEDIASIDEBARPRE] = 'Add HTML content after the sidebar in my media page (on the left)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_MYPLAYLISTSSIDE] = 'Add HTML to the side bar of "My Playlists"';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PLAYERSIDETABLINKS] = 'Add options to the dropdown box on the side of the player';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PLAYERSIDETABS] = 'Add the tabs to the side of the player (selectable by the dropdown)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PLAYERTABLINKS] = 'Add the links for the tabs below the player (the tabs themselves are in playerTabs viewhook)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PLAYERTABS] = 'Add the content of the tabs below the player';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTENTRYDETAILS] = 'Add HTML content to the bottom of the player tab "Details" (below description, tags, and categories, like Customdata for example)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTGALLERY] = 'Add HTML content after the gallery (below the Paginator, and above the player)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTGALLERYITEMS] = 'Add HTML content after the gallery thumbs (but before the paginator) ';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTMYMEDIA] = 'Add HTML content after the My Media section';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTMYMEDIAENTRIES] = 'Add HTML content after the My Media items, but above the pagination';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTMYPLAYLISTSENTRIES] = 'Add HTML content after the thumbnails in "My Playlists"';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PREGALLERY] = 'Add HTML content before the gallery thumbnails, and above the Filter Bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PREGALLERYITEMS] = 'Add HTML content before the gallery thumbnails, but below the Filter Bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PREMYMEDIA] = 'Add HTML content before the My Media thumbnails, but above the filter bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PREMYMEDIAENTRIES] = 'Add HTML content before the My Media thumbnails, below the filter bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PREMYPLAYLISTSENTRIES] = 'Add HTML content before the My Playlists thumbnails, below the filter bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PRESIDENAV] = 'Add HTML content before the side navigation bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTSIDENAV] = 'Add HTML content after the side navigation bar';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_HEADERMENU] = 'Add menu items to the header menu';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POSTHEADER] = 'Add HTML after the site header (including top navigation)';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PRE_HEADERUPLOAD] = 'Add an "li" or other html before the first item in the header upload menu';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_POST_HEADERUPLOAD] = 'Add an "li" or other html after the last item in the header upload menu';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_CHANNEL_SIDENAV] = 'Add a side navigation bar to the channel pages';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PRE_CHANNELS] = 'Add HTML content before the channel items in the channels page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_PRE_CHANNEL] = 'Add HTML content before the channel in the single channel pages';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_CHANNELTABLINKS] = 'Add a tab link in the edit channel page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_CHANNELTABS] = 'Add the tab content in the edit channel page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_CHANNELLIST_LINKS] = 'Add links to the channels in the channel list page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_FOOTER] = 'Render additional site footer';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_EDIT_ENTRY_TABLINKS] = 'Add the links for the tabs to the edit entry page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_EDIT_ENTRY_TABS] = 'Add the content of the tabs to the edit entry page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_EDIT_ENTRY_OPTIONS] = 'Add options to the edit entry page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_GALLERY_SEARCHES] = 'Add extra searches to galleries';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_ENTRY_PAGE] = 'Render an entry page';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_MODULES_HEADER] = 'Allows module to render HTML in the page header.';
        self::$_viewHooksDescriptionsMap['core'][self::CORE_VIEW_HOOK_ADMIN_FOOTER] = 'Allows module to render HTML in the admin footer.';

    }

    public static function listViewHooks()
    {
        return self::$_viewHooksDescriptionsMap;
    }

    public static function viewHookExists($viewHook)
    {
        $viewExists = false;
        if(isset(self::$_coreViewHooks[$viewHook]))
        {
            $viewExists = true;
        }
        else
        {
            foreach(self::$_moduleViewHooks as $module => $viewHooks)
            {
                if(isset($viewHooks[$viewHook]))
                {
                    $viewExists = true;
                    break;
                }
            }
        }

        return $viewExists;
    }

    /**
     * function for modules to register their view hooks
     * 
     * @param string $module name of the module that registers the viewhook
     * @param string $viewHook name of the view hook
     */
    public static function addViewHook($module, $viewHook)
    {
        if(self::viewHookExists($viewHook))
        {
            throw new Zend_Application_Exception("viewhook $viewHook already added. cannot add same viewhook twice.");
        }
        else
        {
            self::$_moduleViewHooks[$module][$viewHook] = $viewHook;
        }
    }

    /**
     * function called by modules to declare which viewhooks they wish to register for
     *
     * @param string $module name of the module that registers for a viewHook
     * @param string $viewHook name of the viewhook the module wants to register to
     */
    public static function registerForViewHook($module, $viewHook)
    {
        if(!self::viewHookExists($viewHook))
        {
            throw new Zend_Application_Exception("cannot register to $viewHook - no such viewhook");
        }
        else
        {
            self::$_registeredModules[$module][$viewHook] = $viewHook;
        }
    }

    public static function getModulesRegisteredForViewHook($viewHook)
    {
        Kms_Log::log("viewhook: getting modules for hook $viewHook", Kms_Log::DEBUG);
        $modules = array();
        foreach(self::$_registeredModules as $module => $viewHooks)
        {
            if(isset($viewHooks[$viewHook]))
            {
                Kms_Log::log('viewhook: found module '.$module.' implementing '.$viewHook, Kms_Log::DEBUG);
                $modules[] = $module;
            }
        }
        return $modules;
    }

    private static function initModuleViewHooks()
    {
        $modules = Kms_Resource_Config::getModulesForInterface('Kms_Interface_Model_ViewHook');
        foreach ($modules as $module => $model)
        {
            $viewHooks = $model->addViewHooks();
            foreach($viewHooks as $viewHook => $description)
            {
                self::addViewHook($module, $viewHook);
                self::$_viewHooksDescriptionsMap['modules'][$module][$viewHook] = $description;
            }
            unset($model);
        }

        foreach ($modules as $module => $model)
        {
            $registerForHooks = $model->getImplementedViewHooks();
            foreach($registerForHooks as $viewHook)
            {
                self::registerForViewHook($module, $viewHook);
            }
            unset($model);
        }
    }


}

?>
