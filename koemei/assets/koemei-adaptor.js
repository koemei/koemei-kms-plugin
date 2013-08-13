/*
* Copyright ©2013 Koemei SA
* author : Tra!an
*/

// JavaScript Document
$(document).ready(function (e) {
	
	//start_edit = 0; - is it an edit widget?
    var kw_start_edit = 0;
    // TODO : Seb added this bcz dunno how to include the edit.phtml in the edit page...
    var kw_in_edit = 1;

	//edit transcript - entry page
    $('.edit_transcript').click(function (event) {
        event.preventDefault();
		//close the already read-only widget
        koemeiWidget.close();
		//clone the player & remove it
        var clone = $("#kplayer").clone(true);
		console.log(clone);
        $("#kplayer").remove();
        $('#player').css('background', 'transparent');
		//put the cloned player in the psedudo widget, and set start_edit = 1 so on player ready it will initialise an edit widget
        $('#new_player').html(clone);
		$('#kplayer').width(520);
		$('#kplayer').height(320);
        $('#pseudo_overlay').show();
        kw_start_edit = 1;
    });


	//close the widget, put the player back in the page
    $('body').on('click','#close_pseudo_widget', function (event) {
        event.preventDefault();
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#player').append(clone);
        $('#player').css('background', '#000');
		$('#kplayer').width('');
		$('#kplayer').height('');
        $('#pseudo_overlay').hide();
        kw_start_edit = 0;
    });


    //edit page: find rows in the captions tab that are from koemei servers.
    // add improve captions button
    if (kw_in_edit === 1) {
		$('#koemei-tab-tab').remove();


        var labels = $('*[data-type="label"]');
        var koemei_found=false;
        labels.each(function(){
            if ($(this).html()=== 'Caption via Koemei'){
                koemei_found=true;
                var sibs = $(this).parent().parent().siblings();
                sibs.each(function(){
                    var actions = $(this)
                    var downloadAction = actions.children(".downloadCaption");
                    downloadAction.each(function(){
                        var improveLink = '<a class="improve_captions" href="#" title="Improve caption"><i class="icon-edit"></i></a>';
                        actions.append(improveLink);
                    })
                });
            }
        });
       
    }
	
	//improve captions click, show edit widget
    $('body').on('click', '.improve_captions', function (event) {
        event.preventDefault();
		//clone the player & remove it
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#player').css('background', 'transparent');
		//put the cloned player in the psedudo widget, and set start_edit = 1 so on player ready it will initialise an edit widget
        $('#new_player').html(clone);
		$('#kplayer').width(520);
		$('#kplayer').height(320);
        $('#pseudo_overlay').show();
        kw_start_edit = 1;
        kw_start_koemei = 1;
    });

    $('body').on('click', '#close_pseudo_widget_edit', function (event) {
        event.preventDefault();
		//close the widget
        koemeiWidget.close();
		//clone the player & remove
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
		//put the player back
        $('#player').append(clone);
        $('#player').css('background', '#000');
        $('#pseudo_overlay').hide();
		$('#kplayer').width('');
		$('#kplayer').height('');
        kw_start_edit = 0;
        kw_start_koemei = 0;
    });

	//close widget on publish
    $('body').on('click', '#kw_publish-button', function (event) {
        $('#close_pseudo_widget').click();
        $('#close_pseudo_widget_edit').click();
    });


    KWidget.addReadyCallback(function (playerId) {
        if (kw_start_koemei === 1) {
            new koemeiOnPage(playerId, kw_entry_id, kw_start_edit);
        }
    });

    koemeiOnPage = function (playerId, entryId, kw_start_edit) {
        return this.init(playerId, entryId, kw_start_edit);
    };

    koemeiOnPage.prototype = {
        init: function (playerId, entryId, start_edit) {
            this.playerId = playerId;
			//in edit mode? show readonly widget
            if (start_edit === 0) {
                koemeiWidget = new KoemeiWidget({
                    media_uuid: entryId,
                    mode: 'embed',
                    player_id: playerId,
                    modal: false,
                    toolbar: false,
                    el: $('#koemei_player'),
                    video_height: 0,
                    width: 670,
                    service: 'kaltura',
                    readonly: true
                });
            }
			//edit widget
            if (start_edit === 1) {
                koemeiWidget = new KoemeiWidget({
                    media_uuid: entryId,
                    mode: 'edit',
                    player_id: playerId,
                    modal: false,
                    toolbar: false,
                    el: $('#new_widget'),
                    video_height: 0,
                    width: 520,
                    widget_height: 280,
                    service: 'kaltura'
                });
            }

        },
        addPlayerBindings: function () {
            try {
                // you can get flashvar or plugin config via the evaluate calls:
                //var myCustomFlashVar = this.kdp.evaluate('{configProxy.flashvars.myCustomFlashVar}');
                // add local listeners ( notice we postfix koemeiOnPage so its easy to remove just our listeners):
                this.kdp.kBind('doPlay.koemeiOnPage', this.onPlay);
                this.kdp.kBind('playerUpdatePlayhead.koemeiOnPage', this.onTime);
                // List of supported listeners across html5 and kdp is available here:
                // http://html5video.org/wiki/Kaltura_KDP_API_Compatibility
            }
            catch (ex) {
                console.error(ex);
            }
        },
        onPlay: function () {
            //console.log(KWidget);
            //console.log( 'video' + this + ' playing');

            // you can read the current time with:
            //KWidget.evaluate('{video.player.currentTime}');
        },
        onTime: function (data) {
            //console.log(data);
        }
    }


});

