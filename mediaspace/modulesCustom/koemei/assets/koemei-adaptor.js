// JavaScript Document
$(document).ready(function (e) {
    start_edit = 0;
    $('.edit_transcript').click(function (event) {
        event.preventDefault();
        koemeiWidget.close();
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#player').css('background', 'transparent');
        $('#new_player').html(clone);
        $('#pseudo_overlay').show();
        start_edit = 1;
    });

    $('#close_pseudo_widget').live('click', function (event) {
        event.preventDefault();
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#player').append(clone);
        $('#player').css('background', '#000');
        $('#pseudo_overlay').hide();
        start_edit = 0;
    });


    //edit page hook
    if (in_edit === 1) {
        var captions_list = $('.caption');
        if (captions_list.length > 0) {
            $.each(captions_list, function (index, element) {
                child = $(element).children('.caption-part').children('.label');
                label = $(child).html();
                if (label === 'Caption via Koemei') {
                    $(element).children('.caption-part').children('.change').html('<a href="#" class="improve_captions">Improve captions</a>');
                }
            });
        }
    }
    $('.improve_captions').live('click', function (event) {
        event.preventDefault();
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#edit_player').css('background', 'transparent');
        $('#new_player').html(clone);
        $('#pseudo_overlay').show();
        start_edit = 1;
        start_koemei = 1;
    });

    $('#close_pseudo_widget_edit').live('click', function (event) {
        event.preventDefault();
        koemeiWidget.close();
        var clone = $("#kplayer").clone(true);
        $("#kplayer").remove();
        $('#edit_player').append(clone);
        $('#edit_player').css('background', '#000');
        $('#pseudo_overlay').hide();
        start_edit = 0;
        start_koemei = 0;
    });


    $('#kw_publish-button').live('click', function (event) {
        $('#close_pseudo_widget').click();
        $('#close_pseudo_widget_edit').click();
    });


    KWidget.addReadyCallback(function (playerId) {
        if (start_koemei === 1) {
            new koemeiOnPage(playerId, entry_id, start_edit);
        }
    });

    koemeiOnPage = function (playerId, entryId, start_edit) {
        return this.init(playerId, entryId, start_edit);
    };

    koemeiOnPage.prototype = {
        init: function (playerId, entryId, start_edit) {
            this.playerId = playerId;

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

