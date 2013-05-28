// JavaScript Document
$(document).ready(function(e) {
	KWidget.addReadyCallback( function( playerId ){
		if (start_koemei===1) { 
			new koemeiOnPage( playerId,entry_id);
		}
	});
	
	koemeiOnPage = function( playerId, entryId ){
		return this.init( playerId, entryId );
	};
	
	koemeiOnPage.prototype = {
		init:function( playerId, entryId ){
			this.playerId = playerId;
            var koemeiWidget = new KoemeiWidget({
              media_uuid: entryId,
              mode:'embed',
              player_id:playerId,
              modal:false,
              toolbar:false,
			  el: $('#koemei_player'),
              video_height:0,
			  width: 670,
				service:'kaltura',
				readonly:true
            });
		},
		addPlayerBindings:function(){
            try {
                // you can get flashvar or plugin config via the evaluate calls:
                var myCustomFlashVar = this.kdp.evaluate('{configProxy.flashvars.myCustomFlashVar}');
                // add local listeners ( notice we postfix koemeiOnPage so its easy to remove just our listeners):
                this.kdp.kBind('doPlay.koemeiOnPage', this.onPlay);
                this.kdp.kBind('playerUpdatePlayhead.koemeiOnPage', this.onTime);
                // List of supported listeners across html5 and kdp is available here:
                // http://html5video.org/wiki/Kaltura_KDP_API_Compatibility
            }
            catch (ex){
                console.error(ex);
            }
		},
		onPlay:function(){
            //console.log(KWidget);
			//console.log( 'video' + this + ' playing');

			// you can read the current time with:
			//KWidget.evaluate('{video.player.currentTime}');
		},
		onTime:function(data){
            console.log(data);
		}
	}
	
	
});

