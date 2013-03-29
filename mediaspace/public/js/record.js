/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

/**
 *call the keepalive interval function
 */
enableKeepAlive(5);


var ksuHandlers = new Array();
var krecordHandlerPrototype = function() {
    this.formSaved = false;

    this.submitForm = function(){
        $("#uploadbox1 .edit_entry").submit();
        $("#uploadbox1 .pendingsave").remove();
        $("#uploadbox1 .save_edit_entry").html(translate('Saving') + '...');
    };
    
    this.reEnableForm = function() {
        /* set formSaved to false */
        this.formSaved = false;
        /* change the action back */
        //  $("#uploadbox"+this.id+" #edit_entry").attr('action', baseUrl + '/entry/add-entry-form/id/'+this.id);
        /* change button name to Save */
        $("#uploadbox1 .save_edit_entry").html(translate('Save'));
        /* re-enable the button */
        $("#uploadbox1 .save_edit_entry").removeAttr('disabled');
        
    }
}

ksuHandlers[1] = new krecordHandlerPrototype();

var krecordHandler = ksuHandlers[1];

var formLoaded = false;
function recordStart() {
    jsLog('record start');
}

function beforeAddEntry(){
    $("#uploadbox1 .progressbar").slideDown('medium').progressbar();
    $("#uploadbox1 .progressbar").progressbar('value', 100);
    $("#uploadbox1 .progressbar > span").text(translate('Please Wait') + '...');

}

function addEntryComplete(entries){
    jsLog('entry added');
    if(entries && entries[0]) {
        var entry = entries[0];
        jsLog(entry);
        $("#uploadbox1 .progressbar").addClass('complete');
        $('#uploadbox1 .krecord_container').slideUp();
        // set the entry id in the form
        $("#uploadbox1 #Entry-id").val(entry.id);            

        // complete progress bar
        $('#uploadbox1 .progressbar > span').html(translate('Finished recording') + '! <a href="' +baseUrl + '/media/' + encodeURIComponent( entry.name ).replace('%2F', '/') + '/' + entry.id +'" target="_blank">' + translate('Go to media page') + '</a>');

        // ping the server to invalidate my-media cache and auto-moderate the entry
        var invalidateUrl = baseUrl + '/entry/post-upload/entryid/'+entry.id+'?format=ajax';
        $.getJSON(invalidateUrl);
        $("#krecord").remove();


        // if the form was saved by the user, then submit it now.
        if(krecordHandler.formSaved) {
            // submit the form
            ksuHandlers[1].submitForm();
        }

    }

}

function addEntryFailed(a) {
    alert("addEntryFailed"+a[0]);
}

function connected() {
    if(!formLoaded) {
        formLoaded = true;
        $("#uploadbox1 #entry_details").fadeIn('medium');
    }
}

function connecting(){
    jsLog('connecting');
}


function deviceDetected() {
    jsLog('detected');
}

function micDenied() {
    jsLog('denied');
    jsLog($('#krecord').getCameras());
}