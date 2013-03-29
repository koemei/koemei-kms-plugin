/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
*/

var currentKsuObj;
var currentKsu = 1;
var ksuHandlers = new Array();
var uploadInProgress = false;

/**
 *call the keepalive interval function
 */
enableKeepAlive(5);


function setConfirmUnload(on) {
    window.onbeforeunload = (on) ? unloadMessage : null;
}

function unloadMessage() {
    return translate("You're still uploading! Are you sure you want to leave this page?");
}

ksuHandlerPrototype = function(){
    this.id = null;
    this.fileSelected = false;
    this.ksuObj = null;
    this.formSaved = false;
    this.entryId = '';
    this.hasError = false;
    this.uploadCancelled = false;
    
    this.readyHandler = function(){
        
        this.ksuObj = document.getElementById('ksu'+this.id);
        // hide the loader and show the text
//        $(this.ksuObj).css('left', '-200px');
//        $('#uploadbox' + this.id + " .uploadbutton .loader").hide();
       // $('#uploadbox' + this.id + " .uploadbutton .text").css('visibility','visible');

       // Jumping up and down effect in Chrome (webkit) fix
	$('#uploadbox' + this.id + " .uploadbutton .text").insertBefore($('#uploadbox' + this.id + " .uploadbutton .loader"));
        $('#uploadbox' + this.id).addClass('ready');

        
        jsLog(this.ksuObj);
    };

    /*this.errorHandler = function(args) {
        jsLog('KSU ERROR:');
        jsLog(args);
    }*/


    this.displayError = function(error) {
        // handle the errors here
        this.hasError = true;
        if(this.id == currentKsu) {
            ++currentKsu;
            if(currentKsu < ksuHandlers.length) {
                ksuHandlers[currentKsu].startUpload();    
            }
        }
        $('#uploadbox' + this.id + " .progressbar > span").html(translate('Oops') + '!');
        $("#uploadbox"+this.id+" .progressbar").progressbar('value', 100).addClass('error');
        $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text('');
        //        $("#uploadbox"+this.id+" .tryagain").show();
        $("#uploadbox"+this.id+" .entry_videofile").addClass('failure');
        $("#uploadbox"+this.id+" .entry_details").slideToggle('fast');
        $("#uploadbox"+this.id+" .cancel_upload").hide();
    }
    

    this.selectHandler = function() {
        // file was selected
        // hide upload button
        var error = this.ksuObj.getError();
        $('#uploadbox' + this.id + " .uploadbutton").css('left', '-200px').css('width', '1px').css('height', '1px');
        $(this.ksuObj).css('width', '1px').css('height', '1px');
        // init progress bar
        $('#uploadbox' + this.id + " .progressbar").show().progressbar();
        // get the filename
        var files = this.ksuObj.getFiles();
        
        if(files && files[0]) {
            var fileName = files[0];
        }
        //show the selected filename
        $("#uploadbox"+this.id+" .entry_videofile").text(fileName);
        $("#uploadbox"+this.id+" .bottomline").fadeOut('fast');
        $('#uploadbox' + this.id + ' .progressbar > .ui-progressbar-value').append('<span/>');
        // check for errors
        if(error) {
            this.displayError(error);
        }
        else {
            if(files && files[0]) {
                this.fileSelected = true;
            }
            
            
            // start the upload if nobody else is uploading, and 
            if(!uploadInProgress && currentKsu == this.id) {
                this.startUpload();
                uploadInProgress = true;
                setConfirmUnload(true);
            }
            else {
                $("#uploadbox"+this.id+" .progressbar > span").text(translate('Upload pending') + '...');
            }

            // load the form
            var formUrl = baseUrl + "/entry/add-entry-form/name/" + encodeURIComponent(fileName) + "/id/" + this.id +"?format=ajax";
            $.getJSON(formUrl, asyncCallback).error( transportError );
        }

        // load the next uploadbox (anyway even if there is an error)
        var addUrl = baseUrl + "/entry/add/boxId/" + (this.id+1) +"?format=ajax";
        $.getJSON(addUrl, asyncCallback).error( transportError );
        $("#uploadbox"+this.id+" .entry_details").fadeIn('fast');
    };
    
    this.allUploadsCompleteHandler = function() {
        
        var error = this.ksuObj.getError();
        // check for errors
        if(error) {
            this.displayError(error);
            uploadInProgress = false;
            setConfirmUnload(false);
            ++currentKsu;
            if(currentKsu < ksuHandlers.length) {
                ksuHandlers[currentKsu].startUpload();    
            }
        }
        else {
            // update progressbar to reflect that we are saving the entries now
            $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text(translate('Please Wait') + '...');
            $("#uploadbox"+this.id+" .progressbar > .cancel_upload").hide();
            jsLog("allUploadsCompleteHandler");
            // set progress to 100
            this.progressHandler(new Array(1,1));

            this.ksuObj.addEntries();
            $("#uploadbox"+this.id+" .entry_videofile").addClass('success');
            
        }
    };
    
    this.progressHandler = function(args) {
        
        if(args[0] != 0 && args[1] != 0) {
            // calculate the percentage
            var progress = Math.round(args[0] / args[1] * 100) ;
            var text = progress + "% ";
            // by leon: removing byte count and moving to title attr
            var text2 = progress + "% of ";
            
            // display megabytes if file size more than 5mb
            // otherwise display kilobytes
            if(Math.floor(args[1] / 1024 / 1024) >= 5) {
                text2 += Math.round(args[1] / 1024 / 1024, 2) + 'Mb';
            }
            else {
                text2 += Math.round(args[1] / 1024, 2) + 'Kb';
            }
            // update the progressbar() value
            $("#uploadbox"+this.id+" .progressbar").progressbar('value', progress);
            
            // update the span of the progressbar text
            if($("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text() != text) {
                $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text(text);
                $("#uploadbox"+this.id+" .progressbar").attr("title", text2);
            }
        }
    };
    
    this.entriesAddedHandler = function(entries) {
        if(entries && entries[0]) {
            var entry = entries[0];
            // change the action of the form to "save", add the entryId, and submit the form
            $("#uploadbox"+this.id+" .Entry-id").val(entry.entryId);
            this.entryId = entry.entryId;
            this.entryName = entry.title;
            
            // change the progressbar text to reflect that the entry is uploaded, and show a link
            $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text('');
            $("#uploadbox"+this.id+" .progressbar").addClass('complete');
            $('#uploadbox'+this.id+' .progressbar > span').html(translate('Finished uploading') + '!&nbsp;<a href="' +baseUrl + '/media/' + encodeURIComponent(this.entryName).replace('%2F', '/') + '/' + this.entryId +'" target="_blank">' + translate('Go to media page') + '</a>');
            $("#uploadbox"+this.id+" .progressbar").removeAttr('title');
            
            // ping the server to invalidate my-media cache and auto-moderate the entry
            var invalidateUrl = baseUrl + '/entry/post-upload/entryid/'+this.entryId+'?format=ajax';
            $.getJSON(invalidateUrl);
            
            // if the form was saved by the user, then submit it now.
            if(this.formSaved) {
                // submit the form
                this.submitForm();
            }
                
            
            // continue uploading next file
            uploadInProgress = false;
            setConfirmUnload(false);
            ++currentKsu;
            if(currentKsu < ksuHandlers.length) {
                ksuHandlers[currentKsu].startUpload();    
            }
                
        }
    };

    this.submitForm = function(){
        $("#uploadbox"+this.id+" .edit_entry").submit();
        // remove the pending save text
        $("#uploadbox"+this.id+" .pendingsave").remove();
        $("#uploadbox"+this.id+" .save_edit_entry").html(translate('Saving') + '...');
    }

    this.updateFormAction = function(entryId){
        // update the form action to "save" with the new entry Id
        $("#uploadbox"+this.id+" .edit_entry").attr('action', baseUrl + '/entry/add-entry-form/id/'+this.id+'/entryid/' + entryId);        
    }
    
    this.startUpload = function() {
        // check if upload was cancelled or if an error happened, if yes, skip to next
        if(this.hasError || this.uploadCancelled) {
            ++currentKsu;
            if(currentKsu < ksuHandlers.length) {
                ksuHandlers[currentKsu].startUpload();    
            }            
        }
        else{
            // start upload only if file selected
            if(this.fileSelected) {
                $("#uploadbox"+this.id+" .progressbar > span").text('');
                this.ksuObj.upload();
            }
        }
    }
    
   this.isCurrent = function()
   {
       return this.id == currentKsu;
   }
    
    this.reEnableForm = function() {
        /* set formSaved to false */
        this.formSaved = false;
        /* change the action back */
        //  $("#uploadbox"+this.id+" #edit_entry").attr('action', baseUrl + '/entry/add-entry-form/id/'+this.id);
        /* change button name to Save */
        $("#uploadbox"+this.id+" .save_edit_entry").html(translate('Save'));
        /* re-enable the button */
        $("#uploadbox"+this.id+" .save_edit_entry").removeAttr('disabled');
        
    }
    
    
    this.cancelUpload = function() {
        // function to cancel the upload
        this.uploadCancelled = true;
        try{
            this.ksuObj.stopUploads();
        }
        catch(e){
            jsLog(e);
        }
        
        this.hasError = true;
        $('#uploadbox' + this.id + " .progressbar > span").html(translate('Upload Cancelled') + '!');
        $("#uploadbox"+this.id+" .progressbar").progressbar('value', 100).addClass('error');
        $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text('');
        $("#uploadbox"+this.id+" .cancel_upload").hide();
        
        $("#uploadbox"+this.id+" .entry_videofile").addClass('failure');
        $("#uploadbox"+this.id+" .entry_details").slideToggle('fast');
        
        if(this.isCurrent() ) {
            uploadInProgress = false;
            setConfirmUnload(false);
            // continue uploading next file
            ++currentKsu;
            if(currentKsu < ksuHandlers.length) {
                ksuHandlers[currentKsu].startUpload();    
            }
        }
    }

}