KalturaCaptionsThumbRotator = {

	mode : 'vidslice', // use mode vidslice for automatic division of the duration to frames
	// mode : 'vidsec', // use mode vidsec for pointing the start second for first frame and end second of last frame
		
        slices : 16, // number of thumbs per video
        frameRate : 1000, // frameRate in milliseconds for changing the thumbs
        
        offset: 1000, // offset in milliseconds between frames - only applies to vidsec mode!
       
        timer : null,
        slice : 0,
        img  : new Image(),
        
        originalUrl : '',
       
        init : function (mode)
        {
        	if(mode == 'vidsec') this.mode = mode;
        	if(mode == 'vidslice') this.mode = mode;
        	return;
        },
        
        thumbBase : function (o) // extract the base thumb path by removing the slicing parameters
        {
                var path = o.src;
                var search_string = "/vid_slice";
                if(this.mode == 'vidsec') search_string = '/vid_sec';

                var pos = path.indexOf(search_string);
                if (pos != -1)
                        path = path.substring(0, pos);
                       
                return path;
        },
       

        change : function (o, i) // set the Nth thumb, request the next one and set a timer for showing it
        {
                slice = (i + 1) % this.slices;

                var path = this.thumbBase(o);
               
                o.src = path + "/vid_slice/" + i + "/vid_slices/" + this.slices;
                this.img.src = path + "/vid_slice/" + slice + "/vid_slices/" + this.slices;

                i = i % this.slices;
                i++;
               
                this.timer = setTimeout(function () { KalturaCaptionsThumbRotator.change(o, i) }, this.frameRate);
        },
        
        change_vid_sec: function (o, sec, start_sec, end_sec)
        {
        	var new_sec = sec + Math.floor(this.offset/1000);
        	
        	if(new_sec > end_sec) new_sec = start_sec;
        	
        	var path =  this.thumbBase(o);
        	
        	o.src = path + "/vid_sec/" + new_sec;
        	this.img.src = path + "/vid_sec/" + new_sec;
        	
        	this.timer = setTimeout(function () { KalturaCaptionsThumbRotator.change_vid_sec(o, new_sec, start_sec, end_sec) }, this.frameRate);
        },
       
        start : function (o, start_sec, end_sec) // reset the timer and show the first thumb. start_sec and end_sec are relevant to vidsec mode only
        {
        	this.originalUrl = o.src;
        	clearTimeout(this.timer);
        	var path = this.thumbBase(o);
        	if(this.mode == 'vidsec' && (end_sec > start_sec || end_sec > 0)) 
        	{
        		first_call_start_sec = start_sec - Math.floor(this.frameRate/1000);
        		this.change_vid_sec(o, first_call_start_sec, start_sec, end_sec);
        	}
        	else
        	{
	                this.change(o, 1);
        	}
        },

        end : function (o) // reset the timer and restore the base thumb
        {
                clearTimeout(this.timer);
                if(this.originalUrl) o.src = this.originalUrl;
                else o.src = this.thumbBase(o);
        }
};


