/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 * written by: Yuri Friedberg
 */
(function($) {
    $.fn.ktooltip = function(options) {
	var defaults = {
	    'tipclass': 'ktooltip'
	}
	var item = this;
	item.settings = {}
	var init = function() {
	    item.settings = $.extend({}, defaults, options);
	}
	var content = null;
	var timeout = null;
	init();
	var triangle = $('<em>', {
	    'css':{
		'position': 'absolute',
		'bottom': '-16px',
		'width': 17,
		'height': 17,
		'display': 'block'
	    }
	})
	var tip = $('<div></div>', {
	    'class': item.settings.tipclass,
	    'css': {
		'position': 'absolute'
	    }
	});    
	$('body').append(tip);
	var build = function(obj){
	    content = $('<div></div>', {
		'html': obj.attr('title')
	    });
	    obj.attr('title', '');
	    timeout = setTimeout(function(){
		tip.html(content)
		tip.css({
		    'position': 'absolute',
		    'top': $(obj).offset().top - tip.outerHeight(true),
		    'left': $(obj).offset().left + $(obj).width() / 2 - tip.outerWidth(true) / 2
		});
		
		show();
		triangle.css('left', (tip.outerWidth(true) / 2) - 9);
		tip.append(triangle);
	    }, 500);
	}
	var show = function(){
	    tip.fadeIn('fast');
	}
	var destroy = function(obj){
	    obj.attr('title', content.text());
	    tip.fadeOut('fast');
	    window.clearTimeout(timeout);
	}
	
	if(item.attr('title') != ''){
	    item.hover(function(){
		build($(this));
	    }, function(){
		destroy($(this));
	    });
	}
    }
})(jQuery);

