/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/* 
 * Global JS interactions
 */

// disable cache for Ajax calls
$.ajaxSetup({ cache: false });
// hack for IE7
if (!window.console){console={log:function(){}}};
var defaultTimeout = 30;
var historyEnabled = false; // disable by default
// initialize history plugin (disabled by default)
if (historyEnabled == true && typeof history.replaceState != 'undefined') {
    var stateObj = {
        link: document.location.href, 
        obj:null
    };
    //  add a stateObj to the history entry of the current page view
    history.replaceState(stateObj, document.title, document.location.href);
    // bind the popstate event (catch back and forward actions)
    $(window).bind("popstate", function() {
        // in case the history entry has a stateObj
        if('state' in window.history) {
            var state = window.history['state'];
            jsLog(state);
            if('link' in state) {
                var href = state['link'];
                href += '?format=ajax';
                $.getJSON(href, asyncCallback).error( transportError );
                $('a').removeClass('active');
                $elem.addClass('active');
                $('body').addClass('cursorwait');
                ajaxRequestInProgress = true;
                if('obj' in state && state['obj'] != null) {
                    state['obj'].addClass('active');
                }
            }
        }
    });
}


$('form').live( 'submit', function(event) {
    var $form = $(this);
    //    jsLog($form);
    if($form.attr('ajax'))
    {
        jsLog('submitting form via Ajax');
        // async request with post data
        var action = $form.attr('action') + '?format=ajax';
        
        $.ajax({
            url: action,
            dataType: "json",
            type: $form.attr('method') ? $form.attr('method') : 'GET',
            data: $form.serialize(),
            success: asyncCallback,
            error: transportError
        });
        
        $('a').removeClass('active');
        $form.addClass('active');
        $('body').addClass('cursorwait');

        event.stopPropagation();
        event.preventDefault();
        return false;
    }
});


$('html').click( function(event) {
    jsLog('html click');
        
    var $elem = $(event.target);
    // check if target is a button
    if( !$elem.is('button')) {
        // if not, check for closest link
        var $link = $(event.target).closest('a');
        if($link.is('a')) {
            $elem = $link;
        }
        else {
            // no link, check for closest button
            $elem = $(event.target).closest('button');
            if(!$elem.is('button')) {
                // target has no link or button
                return;
            }
        }
        $link = null;
    }
    
    // in case of ctrl click or shift click, we open in new tab/window
    if(event.ctrlKey || event.shiftKey) {
        return;
    }
    var href = $elem.attr('href');
    var rel = $elem.attr('rel');
    var setUrl = $elem.attr('seturl');
    var timeout = $elem.attr('data-timeout');
    
    if(timeout) {
        jsLog('Temporary increasing timeout to '+timeout+' seconds.');
        setAjaxTimeout(timeout);
    }
    
    // if the link has a conditional element, call it
    var callback = $elem.attr('data-cond');
    if (callback) {
        var res = (new Function("return " + callback))();        
        if (!res) {
            return false;
        };
    };

    if(rel) {
        // capture event
        jsLog('Element rel:' + rel);
        if(ajaxRequestInProgress) {
            jsLog('another request is in progress');
            return false;
        }
        
        switch(rel) {
            case 'async':
                // check if we modify browser history (html5 browsers)
                if (typeof history.pushState != 'undefined' && historyEnabled == true ) {
                    // set state obj
                    var stateObj = {
                        link: href, 
                        obj: $elem
                    };
                    // modify history
                    if(setUrl === "1") {
                        // if the link needs to set the href in the browser
                        history.pushState(stateObj, $elem.attr('title'), href);
                    }
                    else {
                        // if not, then we put the current location in the browser (leave as is)
                        history.pushState(stateObj, $elem.attr('title'), document.location.href);
                    }
                    
                }
                href += '?format=ajax' + parseLinkParams($elem);
                jsLog('Requesting '+href);
                $.getJSON(href, asyncCallback).error( transportError );
                break;
            case 'script':
                href += '?format=script'  + parseLinkParams($elem);
                jsLog('Requesting '+href);
                $.getScript(href, scriptCallback).error( transportError );
                break;
            case 'dialog':
                href += '?format=dialog'  + parseLinkParams($elem);
                jsLog('Requesting '+href);
                $.getScript(href, scriptCallback).error( transportError );
                break;
            case 'require-flash':
                if(!kSupportsFlash()) {
                    alert(translate("Sorry, this action requires Adobe Flash."));
                    return false;
                }
                return true;
            break;
            default:
                return;
        }
        ajaxRequestInProgress = true;
        
    }
    else {
        // continue with normal event
        return;
    }
    
    $('a').removeClass('active');
    $elem.addClass('active');
    $('body').addClass('cursorwait');
    
    event.stopPropagation();
    event.preventDefault();
});

parseLinkParams = function(obj) {
    var params = obj.attr('data-params');
    return params ? '&' + params : '';
}


transportError = function(data) {
    setAjaxTimeout(defaultTimeout);
    var title;
    ajaxRequestInProgress = false;
    $('body').removeClass('cursorwait');
    if(data) {
        jsLog(data);
        
        if(data.statusText == 'timeout') {
            title = 'Request timeout';
            $('#errorDialog').html('Request took too long to complete');
        }
        else if(data.status == 0 && data.statusText == 'error' && data.responseText == '') {
        // do nothing
        }
        else if(data.status == '405') {
            // try to remove the navigate away confirmation
            try{
                setConfirmUnload(false);
            }
            catch(e){}
            // redirect to login
            document.location.href = baseUrl + '/user/login';
        //            $.getScript(baseUrl + '/user/login?format=dialog');
        } 
        else {
            title = 'Error' + ( data.status != '200' ? ': ' + data.status : '');
            $('#errorDialog').html(data.responseText);
        }
        
        if(title) {
            $('#errorDialog').dialog({
                width: 'auto',
                autoOpen: true,
                draggable: false,
                modal: true,
                resizable: false,
                title: title,
                closeOnEscape: true,
                buttons: {
                    Close: function(){
                        $(this).dialog('close');
                    }
                }
            });
        }
    }
}


asyncCallback = function(data)
{
    setAjaxTimeout(defaultTimeout);
    ajaxRequestInProgress = false;
    jsLog('async callback');
    var action, content, target, script;
    $('body').removeClass('cursorwait');
    try{
        if(data && data.content)
        {
            jsLog("Length of content array: " + data.content.length);
            for(var i=0; i<data.content.length; i++) {
                action = data.content[i].action;
                content = data.content[i].content ? data.content[i].content : '';
                target = data.content[i].target;
                
                if(action && target) {
                    jsLog('action ' + action + ', target ' + target);
                    var debugColor = '#ffff99';
                    var debugParent = false;
                    switch(action) {
                        case 'replace':
                            $(target).html(content);
                            break;
                        case 'append':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).append(content);
                            break;
                        case 'appendFade':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).append( $(content).hide().fadeIn('fast') );
                            break;
                        case 'prepend':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).prepend(content);
                            break;
                        case 'prependFade':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).prepend( $(content).hide().fadeIn('fast') );
                            break;
                        case 'value':
                            $(target).val(content);
                            break;
                        case 'after':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).after(content);
                            break;
                        case 'before':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).before(content);
                            break;
                        case 'afterFade':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).after($(content).hide().fadeIn('fast'));
                            break;
                        case 'beforeFade':
                            debugColor = '#aaaaff';
                            debugParent = true;
                            $(target).before($(content).hide().fadeIn('fast'));
                            break;
                        case 'delete':
                            debugColor = '#ffaaaa';
                            debugParent = true;
                            $(target).remove();
                            break;
                        case 'deleteFade':
                            debugColor = '#ffaaaa';
                            debugParent = true;
                            $(target).fadeOut('fast', function() {
                                $(this).remove();
                            });
                            break;
                        case 'empty':
                            debugColor = '#ffaaaa';
                            $(target).empty();
                            break;
                        default:
                            break;
                    }
                    
                    // add highlight effect for debuging and troubleshooting
                    if(typeof jsHighlight != 'undefined' && jsHighlight == 1) {
                        if(debugParent == true) {
                            $(target).parent().effect("highlight", {color: debugColor}, 3000);
                        }
                        else if($(target).is(':visible')) {
                            $(target).effect("highlight", {color: debugColor}, 3000);
                            
                        }
                    }
                }
            }
        }
        if(data && data.script) {
            script = data.script;
            eval(script);
        }
    } catch(e) {
        jsLog(e)
        }
    
    
}


scriptCallback = function(data)
{
    ajaxRequestInProgress = false;
    jsLog('script callback');
    $('body').removeClass('cursorwait');
    setAjaxTimeout(defaultTimeout);
}




/**
 *
 * translation function
 */

function translate(string) {
    return (typeof(LOCALE) != 'undefined' && LOCALE[string]) ? LOCALE[string] : string;
}

/**
 * logger function
 */

jsLog = function(object) {
    if(debug == 1 && typeof(console) != 'undefined' && console && console.log) {
        console.log(object);
    }
}

/**
 * flash detection
 */

function kSupportsFlash(){
    var version = kGetFlashVersion().split(',').shift();
    if( version < 10 ){
        return false;
    } else {
        return true;
    }
}

function kGetFlashVersion(){
    // navigator browsers:
    if (navigator.plugins && navigator.plugins.length) {
        try {
            if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
                return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
            }
        } catch(e) {}
    }
    // IE
    try {
        try {
            if( typeof ActiveXObject != 'undefined' ){
                // avoid fp6 minor version lookup issues
                // see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
                var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
                try { 
                    axo.AllowScriptAccess = 'always'; 
                } catch(e) { 
                    return '6,0,0'; 
                }
            }
        } catch(e) {}
        return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
    } catch(e) {}
    return '0,0,0';
}

/**
 * set an interval of 5 minutes to ping the keep alive action (for extending the session)
 **/

// interval in minutes
var keepAliveInterval;
function enableKeepAlive(interval) {
    // default is 5 minutes
    var delay = 5 * 60 * 1000;
    if(interval) {
       delay = interval * 60 * 1000; 
    }
    
    keepAliveInterval = setInterval(function(){
        $.ajax({
            url: baseUrl + '/user/keep-alive',
            type: 'POST',
            error: transportError
        });
    }, delay);
    
}


function setAjaxTimeout(timeout) {
    jsLog('Setting Ajax timeout to ' + timeout + ' seconds');
    $.ajaxSetup({
        timeout: (timeout * 1000)
    });
}

setAjaxTimeout(30);
var ajaxRequestInProgress = false;
