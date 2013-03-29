/* 
 *  All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 *  To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/**
 *call the keepalive interval function
 */
enableKeepAlive(5);


var kvpm = {
    entryAdded: function(entryId) {jsLog("Entry Added "+entryId); $("a#kvpmLink").css('display','block');},
    creationDone: function(entryId) {document.location.href = baseUrl + '/entry/process-new-presentation/id/' + entryId; $('#kvpm').css('visibility', 'hidden');}
}


