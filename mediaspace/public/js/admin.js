/*
 * All Code Confidential and Proprietary, Copyright ©2011 Kaltura, Inc.
 * To learn more: http://corp.kaltura.com/Products/Video-Applications/Kaltura-Mediaspace-Video-Portal
 */

/*** user admin ****/

// event listener for unchecking the "check-all" checkbox
$('.user-sel').live('change', function() {
    $('.all-sel').removeAttr('checked');
});


// helper event listener for user send password reminder
$('#email_pw').live('click', function() {
    var href = $(this).attr('href');
    var pw = $('#user-password').val();
    if(pw) {
	$(this).attr('href', href + escape(' ' + pw) );
    }
    else {
	return false;
    }
});

// function for updating the href of the "delete checked" button
function updateChecked(obj)
{
    var vals = [];
    // add checked values to array "vals"
    $('.user-sel:checked').each(function() {
	if($(this).val()) {
	    vals.push($(this).val());
	}
    });
    // update the href of delete-checked
    if(vals.length) {
	obj.attr('href', baseUrl + '/admin/user-delete/id/' + vals.join(','));
    }
}





/**** config admin ***/
var undoElement = new Array();
$('a.add').live( 'click' , function(event) {
    var link = event.target;
    var t = $(link).data('field');
    var html = $("div[data-id="+t+"]:last,fieldset[data-id="+t+"]:last").clone();
    var htmlContent = $(html).html();
    
    htmlContent.replace('/randomkeyxxx/', Math.random() * 1000);
    $(html).html(htmlContent);
    
    //console.log(html);
    
    $(html).insertBefore($(link).parent());
    $('input,select').change();
//console.log($('#'+id));
});  

$('a.delete').live( 'click' , function(event) {
    var link = event.target;
    var t = $(link).closest('fieldset');
    $('div.undo').remove();
    $(t).fadeOut(500, function() {
	$('<div class="undo"><a>undo</a></div>').insertAfter(this);
	//       console.log(this);
	undoElement = this;
	$(this).remove();
    });
   
});

$('div.undo > a').live( 'click' , function(event) {
    var link = event.target;
    var t = $(link).closest('div');
    var elem = undoElement;
    //   console.log(elem);
    $(elem).insertAfter(t).fadeIn(500);
    $('div.undo').remove();
});

$('form#config input,form#config select').live ('change' , function(event) {
    var input = event.target;
    updateDependencies(new Array(input));
    setConfirmUnload(1);
});

flashItem = function(item) {
    var $elem;
    var h = false;
    if(item) {
        h = item;
    }
    else if(document.location.hash) {
        h = document.location.hash.substring(1);
    }
    
    if(h) {
        $elem = $("label[for="+h+"],a[name="+h+"]").closest('.tabItem');
        $elem.stop().css("background-color", "#FFFF9C").animate({ backgroundColor: "#FFFFFF"}, 5000);
        
        // open the toggler if needed (viewfiles kb)
        $toggler = $elem.parent().prev('.toggler');
        if(!$elem.parent().is(':visible')) {
            $toggler.click();
        }
    }
}

updateDependencies = function(objCollection){
    var o,c,n,i,id;
    for(i=0;i<objCollection.length;i++) {
	o = objCollection[i]; // current field in list of all given fields (1 "on change" or all "on load")
	n = $(o).attr('data-name'); // the name of the current config field
        id = $(o).attr('data-id'); // the id of the current config field
	v = $(o).val(); // the current value of the current config field
       // jsLog("searching for elements depends on "+n+ " or "+id+" with value "+v);
	c = $(o).closest('.tabItem,.itemCollection').parent().find('.tabItem[data-depends-field="'+n+'"],.tabItem[data-depends-fields*="'+n+'"],.itemCollection[data-depends-field="'+n+'"],.itemCollection[data-depends-fields*="'+n+'"],.tabItem[data-depends-field="'+id+'"],.tabItem[data-depends-fields*="'+id+'"],.itemCollection[data-depends-field="'+id+'"],.itemCollection[data-depends-fields*="'+id+'"]');
            
        // c is list of all components which match one of the above selectors for fields that depend on n (name of the current config field)
	$.each(c, function(index, item) {
            //jsLog("  determine for item that depends on field "+$(item).attr('data-depends-field')+ ' with value ['+$(item).attr('data-depends-value')+ '] and notValue ['+$(item).attr('data-depends-not-value')+']');
            if($(item).attr('data-depends-multi-value') != undefined)
            {
               // jsLog("  handling multi-field "+$(item).attr('data-depends-fields')+ ' with values ['+$(item).attr('data-depends-value')+ '] ');
                if(shouldShowMultiFieldDependency(item, o))
                {
                    $(item).show();
                }
                else
                {
                    $(item).hide();
                }
            }
	    else if(/*$(item).attr('data-depends-value') != undefined &&*/ $(item).attr('data-depends-value') == v){
               // jsLog("  decide to show based on value "+$(item).attr('data-depends-value')+ ' which equals '+v);
		$(item).show();
	    }
            // allow "opposite dependency" - field is relevant only if value of dependent field is NOT X
            else if($(item).attr('data-depends-not-value') != undefined && $(item).attr('data-depends-not-value') != "" && $(item).attr('data-depends-not-value') != v)
            {
               // jsLog("  decide to show based on notValue "+$(item).attr('data-depends-not-value')+ ' which is not equal to '+v);
                $(item).show();
            }
	    else {
                //jsLog("  --  hiding item "+$(item).attr('data-depends-value'));
		$(item).hide();
	    }
	});
    }
    
       
}

// added method to handle multi-dependency decision if an item should be displayed or not based on all dependencies (OR relation)
shouldShowMultiFieldDependency = function(object, o) {
    var fields = $(object).attr('data-depends-fields');
    var fieldsArr = fields.split(',');
    //jsLog(fieldsArr);
    var values = $(object).attr('data-depends-multi-value');
    
    var splittedValues = values.split(',');
    //jsLog(fieldsArr);

    var returnValue = false;

    for(index in fieldsArr)
    {
        var dependsOnObj = o;//$('*[data-name="'+fieldsArr[index]+'"]', o);
        var dependsOnValue = $(dependsOnObj).val();
        //jsLog(dependsOnValue);
        for(valIndex in splittedValues)
        {
            if(splittedValues[valIndex] == dependsOnValue)
            {
                returnValue = true;
                break;
            }
        }
        if(returnValue == true)
        {
            break;
        }
    }
    return returnValue;
}

enableRemoveLinks = function() {
    // remove all buttons first
    $('button.remove_elem').remove();
    
    // now add a button to each field
    $.each($('.multi'), function(index, elem) {
	var $b = $('<button class="remove_elem">X</button>');
	$(elem).after($b);
	$b.click(function(){
	    $(elem).fadeOut(500, function() {
		$('div.undoElem').remove();
		$('<div class="undoElem"><a>undo</a></div>').insertAfter(this);
		//       console.log(this);
		undoElement = this;
		$(this).remove();
	    });
	    $(this).remove();
	});
    });
}

$('div.undoElem > a').live( 'click' , function(event) {
    var link = event.target;
    var t = $(link).closest('div');
    
    var elem = undoElement;
    $("button.remove_elem").remove();
    //   console.log(elem);
    $(elem).insertAfter(t).fadeIn(500);
    $('div.undoElem').remove();
    enableRemoveLinks();
});


$(document).ready( function() {
    updateDependencies($('form#config input,form#config select'));
    enableRemoveLinks();
    
    $.each($('.toggler'), function(){
	$(this).togglerHandler();
    });
    
    flashItem();
    //expand collapse
});

$.fn.togglerHandler = function(){
    if(!$(this).hasClass('no-icon')) {
        $(this).prepend('<span>');
    }
     
    var box = $(this).next('div');
    if(box.is(':visible')){
	$('span', $(this)).css('backgroundPosition', 'bottom left');   
    } else {
	$('span', $(this)).css('backgroundPosition', 'top left');
    }
    $(this).click(function(){
	if(box.is(':visible')){
	    $(this).next('div').slideUp('fast');
	    $('span', $(this)).css('backgroundPosition', 'top left');
	} else {
	    $(this).next('div').slideDown('fast'); 
	    $('span', $(this)).css('backgroundPosition', 'bottom left'); 
	}
    });
}

function setConfirmUnload(on) {
    window.onbeforeunload = (on) ? unloadMessage : null;
}

function unloadMessage() {
    return 'You have entered some changes to the MediaSpace configuration. If you navigate away from this page without first saving your data, the changes will be lost.';
}
