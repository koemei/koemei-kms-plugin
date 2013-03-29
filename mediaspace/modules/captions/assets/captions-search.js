// for writing of all Klatura apps code in its own scope (not the global scope) and for generally better code orgnaization.
// in case KApps has already been defined, don't overwrite it (like if app is part of page with more apps).
window.KApps = window.KApps || {}

// for dev logging:
KApps.log = KApps.log || function(log) {
	if(KApps.debug || location.search.indexOf("kdebug") != -1) {
		window.console && console.log(log);
	} 
}

// for sharing variables between apps:
KApps.vars = KApps.vars || {}

// main app  object
KApps.CaptionsSearch = {
	vars				: {
		currentOutTime: 100000000,
		currentEntryId: null,
		firstLoad : false,
		currentInTime: null,
		playerFirstLoad : true,
        changedMedia : false,
        playedMedia : false,
        label: null,
        labelMap : null,
        captionsPlugin : null,
	},
	

	searchResultClick : function(tr_obj) {
		KApps.log('searchResultClick');

		// find TR parent element that holds data
		$tr = $(tr_obj);
		
		// remove active class from active result
		$("#kitems.captions")
			.find(".active")
			.removeClass("active");

		$("#kitems.captions")
			.find(".active_asset")
			.removeClass("active_asset");

		// make current result active
		$tr.addClass("active");
		$tr.addClass("active_asset");
		
		// collect data
		var entry_id = $tr.attr("data-entryid"); 
		var out_time = $tr.attr("data-end");
		var in_time = $tr.attr("data-start");
		var label = $tr.attr("data-label");
		var language = $tr.attr("data-lang");

		KApps.CaptionsSearch.searchResultActivate(entry_id,out_time,in_time,label,language);
	},

	searchResultActivateAgain : function() {
		KApps.log('searchResultActivateAgain');
		
		//these parameters were already loaded
		//kdp wasn't ready
		//when it ready just call this function again
		label = KApps.CaptionsSearch.vars.label;
		if (!label) return; //this function should be called only if the screen was opened from external captions search screen and kdp was not ready
		out_time = KApps.CaptionsSearch.vars.currentOutTime * 1000;
		in_time = KApps.CaptionsSearch.vars.currentInTime * 1000;
		entry_id = KApps.CaptionsSearch.vars.currentEntryId;
		KApps.CaptionsSearch.searchResultActivate(entry_id,out_time,in_time,label,label, true);
		
	},
	searchResultActivate : function(entry_id,out_time,in_time,label,language,internal) {
		KApps.log('searchResultActivate');
		
		// use the language if the label is missing
		if(label == ''){
			label = language;
		}
		KApps.CaptionsSearch.vars.label = label;
		KApps.CaptionsSearch.vars.currentOutTime = out_time/1000;
		KApps.CaptionsSearch.vars.currentInTime = in_time/1000;
		
		// if new entry - change media in the player
		if(entry_id != KApps.CaptionsSearch.vars.currentEntryId)
		{			
			KApps.CaptionsSearch.vars.changedMedia = true;
			KApps.CaptionsSearch.vars.currentEntryId = entry_id;
		}
		else
		{
			KApps.CaptionsSearch.vars.changedMedia = false;
		}
		
		// if KDP is not available or not ready - do nothing
		if(!window.kdp) 
		{
			KApps.log("player is not ready yet.");
			return false;
		}
		if (KApps.CaptionsSearch.vars.playerFirstLoad) 
		{
			KApps.CaptionsSearch.vars.playerFirstLoad = false;
			window.kdp.removeJsListener("kdpReady", "KApps.CaptionsSearch.searchResultActivate");
		}

		kdp.sendNotification('closedCaptionsSelected', {label: label});
		kdp.setKDPAttribute('ccOverComboBox', 'selectedIndex', KApps.CaptionsSearch.getCaptionLabelIndex(label));
		
		
		kdp.sendNotification("doSeek",in_time/1000);
			
		//first seek should be sent twice (player needs to load media)
		if (KApps.CaptionsSearch.vars.playedMedia == false)
		{
			KApps.CaptionsSearch.vars.playedMedia = true;
			//player doesn't send the seekEnd event - second seek should be done with sleep
			//fix it when the player fixes the bug
			setTimeout(function() {kdp.sendNotification("doSeek",in_time/1000);},2000);
		}
		// make sure the player will pause automatically on the next outTime
		window.kdp.addJsListener("playerUpdatePlayhead", "KApps.CaptionsSearch.autoPauseOnOutTime");
	},
		
	autoPauseOnOutTime : function(playHead) {
		KApps.log("KApps.CaptionsSearch.autoPauseOnOutTime");
		if(playHead >= KApps.CaptionsSearch.vars.currentOutTime)
		{
			window.kdp.sendNotification("doPause");
			// allow the user to click play after reaching the outTime of the caption
			KApps.CaptionsSearch.vars.currentOutTime = 100000000;
		}
	},
	
	

	/**
	 *	get the caption label index as used by the kdp. needed to change the label.
	 */
	getCaptionLabelIndex : function (label) {
		// check if we need to query the kdp about its caption files - 
		// the label map is empty, or this is a new entry
		if (KApps.CaptionsSearch.vars.changedMedia || KApps.CaptionsSearch.vars.labelMap == null) {
			KApps.CaptionsSearch.vars.labelMap = {};
			// load kdp caption labels. 
			// quering the labels doesn't work, so parse the asset files instead.
			var assets = kdp.evaluate('{' + KApps.CaptionsSearch.vars.captionsPlugin + '.availableCCFiles}');
			if (assets != null) {
				for (var i = 0; i < assets.length ; ++i) {
					var asset = assets[i];
					KApps.CaptionsSearch.vars.labelMap[asset.label] = i + 1;
				};
			};
		};
		// return the label index
		return KApps.CaptionsSearch.vars.labelMap[label];
	},

	/**
	 *	check if the entry played in the kdp was changed
	 */
	entryChanged : function() {
		KApps.log('entryChanged changed media ' + KApps.CaptionsSearch.vars.changedMedia);
		return KApps.CaptionsSearch.vars.changedMedia;
	},

	/**
	 *	determines the captions plugin used by the kdp
	 */
	buildPluginMap : function(pluginsMap){
		// get the captions plugin used by the kdp
		if (pluginsMap['closedCaptionsOverPlayer']) {
			// KMS default - closedCaptionsOverPlayer
			KApps.CaptionsSearch.vars.captionsPlugin = 'closedCaptionsOverPlayer';
		}
		else if (pluginsMap['closedCaptionsUndelPlayer']) {
			// second option - closedCaptionsUndelPlayer
			KApps.CaptionsSearch.vars.captionsPlugin = 'closedCaptionsUndelPlayer';			
		}
		else if (pluginsMap['closedCaptions']){
			// custom captions plugin - closedCaptions			
			KApps.CaptionsSearch.vars.captionsPlugin = 'closedCaptions';			
		}
	}, 

}

$(function() { // app
	// do we have a player on the page?
	if ("kWidget" in window) {
		// assign click event on caption search results
		$("#kitems.captions li").click(function() {
			KApps.log("caption results clicked");
			KApps.CaptionsSearch.searchResultClick(this);
		});
		// register the captions jsCallbackReady to the kdp ready event.
		kWidget.addReadyCallback( captionsJsCallbackReady );
	}
});

// called by kdp once it is ready to interact with javascript on the page:
function captionsJsCallbackReady(player_id) {
	KApps.log("captionsJsCallbackReady("+player_id+")");
	window.kdp = $("#" + player_id).get(0);
	KApps.log(kdp);

	window.kdp.addJsListener("playerUpdatePlayhead", "KApps.CaptionsSearch.autoPauseOnOutTime");
	window.kdp.addJsListener("pluginsReady", "KApps.CaptionsSearch.buildPluginMap");
	window.kdp.addJsListener('ccDataLoaded', "KApps.CaptionsSearch.searchResultActivateAgain");

}