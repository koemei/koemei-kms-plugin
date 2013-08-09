/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 *call the keepalive interval function
 */
enableKeepAlive(5);

function reloadKDPCaptions() {
	window.kdp.sendNotification('entryReady');
}

function setConfirmUnload(on) {
    window.onbeforeunload = (on) ? unloadMessage : null;
}

function unloadMessage() {
    return translate("You're still uploading! Are you sure you want to leave this page?");
}

ksuHandlerPrototype = function(){
    this.id = null;
    this.ksuObj = null;
    this.hasError = false;
    
    // previous upload details
    this.fileName = null;

    this.ksuHeight = null;
    this.ksuWidth = null;
    
    this.readyHandler = function(){
    	
    	this.ksuHeight = $('#uploadbox' + this.id + " .uploadbutton").css('height');
        this.ksuWidth = $('#uploadbox' + this.id + " .uploadbutton").css('width');
        
    	// get the ksu
        this.ksuObj = document.getElementById('ksu'+this.id);
              
        // Jumping up and down effect in Chrome (webkit) fix
        $('#uploadbox' + this.id + " .uploadbutton .text").insertBefore($('#uploadbox' + this.id + " .uploadbutton .loader"));
        $('#uploadbox' + this.id).addClass('ready');

    	// file upload was done before (this is a validation error reload)
        console.log("readyHandler ", this.fileName);
        if (this.fileName){
        	this.hideButton();
        	this.showChangeFileLink();
            // update the file name
            $("#changeCaptionFile .fileName").empty();
            $("#changeCaptionFile .fileName").prepend(this.fileName);
        }
    };


    this.displayError = function(error) {
        // handle the errors here
        this.hasError = true;
    
        $('#uploadbox' + this.id + " .progressbar > span").html(translate('Oops') + '!');
        $("#uploadbox"+this.id+" .progressbar").progressbar('value', 100).addClass('error');
        $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").text('');
    }
    

    this.selectHandler = function() {
        // file was selected
    	console.log("selectHandler ");

        // hide the upload button
    	this.hideButton();
       
        // get the filename
        var files = this.ksuObj.getFiles();
        console.log(files);

        if(files && files[files.length -1]) {
            var fileName = files[files.length -1];
        }
        console.log(fileName);

        // check for errors
        var error = this.ksuObj.getError();

        // we allow for numFilesExceeds error to allow the user to change the uploaded file.
        // we will enforce one file to upload at the upload stage.
        if(!error || error == 'numFilesExceeds') {
            // update the selected file name
            $("#changeCaptionFile .fileName").empty();
            $("#changeCaptionFile .fileName").prepend(fileName);

            // show the change file link
            this.showChangeFileLink();

            setConfirmUnload(true);
        }
        else{
            console.log(error);
            this.displayError(error);
        }
    };
    
    this.singleUploadCompleteHandler = function(args){
    	var entry = args[0];
    	console.log("singleUploadCompleteHandler", entry);
    	// set the upload token and file extention
        this.updateFileDetails(entry);
    };
    
    
    this.allUploadsCompleteHandler = function() {
        console.log("allUploadsCompleteHandler");
        var error = this.ksuObj.getError();
        // check for errors
        if(error) {
            this.displayError(error);
            setConfirmUnload(false);
        }
        else {       
            // hide the progress bar
            $("#uploadbox"+this.id+" .entry_videofile").addClass('success');            
            $("#uploadbox"+this.id+" .progressbar > .ui-progressbar-value > span").html('');
            $("#uploadbox"+this.id+" .progressbar").addClass('complete');
            $('#uploadbox'+this.id+' .progressbar').hide();
            $("#uploadbox"+this.id+" .progressbar").removeAttr('title');

            // submit the form
            $('form#uploadCaption').submit(); 
            setConfirmUnload(false);            
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
    
    this.updateFileDetails = function(entry){
    	console.log('updateFileDetails' , entry);
    	// set the upload token
        $("#Upload-token").val(entry.token);
        // set the file type
        $("#Upload-type").val(entry.extension);
        // set the file name
        $("#Upload-name").val(entry.title);
    };
    
    this.hideButton = function(){
    	//console.log('hideButton');

        $('#uploadbox' + this.id + " .uploadbutton").css('left', '-200px').css('width', '1px').css('height', '1px');
        $(this.ksuObj).css('width', '1px').css('height', '1px');
    };
    
    this.showChangeFileLink = function(){
   	 	//console.log('showChangeFileLink');

    	$('#uploadbox' + this.id + " .uploadbutton .change").insertBefore($('#uploadbox' + this.id + " .uploadbutton .text"));
        $('#uploadbox' + this.id + " .uploadbutton").addClass('loaded');
        // show the ksu object again
        $('#uploadbox' + this.id + " .uploadbutton").css('left', '0').css('width', this.ksuWidth).css('height', this.ksuHeight);
        $(this.ksuObj).css('width', '100%').css('height', this.ksuHeight);        
    };

    this.uploadFile = function(){
        console.log("uploadFile");

        var files = this.ksuObj.getFiles();
        if (files && files.length > 0) {
            // test for too many files
            if (files.length > 1) {
                // remove all files but the last one
                this.ksuObj.removeFiles(0,files.length -2);
            }

            // show the progress bar
            $('#uploadbox' + this.id + " .progressbar").show().progressbar();
            $('#uploadbox' + this.id + ' .progressbar > .ui-progressbar-value').html('<span/>')
            $("#uploadbox" + this.id + " .progressbar > span").text('');

            // upload file
            this.ksuObj.upload();
        }
        else{
            // submit the form - to do validation
            $('form#uploadCaption').submit(); 
            setConfirmUnload(false);        
        }
    };
}