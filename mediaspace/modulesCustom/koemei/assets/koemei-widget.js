/*! Koemei Widget - v0.1.0 - 2013-06-07
* https://www.github.com/koemei/widget
* Copyright (c) 2013 Koemei; Licensed MIT */
/*!
 * jQuery JavaScript Library v1.8.1
 * http://jquery.com/
 *
 * Includes Sizzle.js
 * http://sizzlejs.com/
 *
 * Copyright 2012 jQuery Foundation and other contributors
 * Released under the MIT license
 * http://jquery.org/license
 *
 * Date: Thu Aug 30 2012 17:17:22 GMT-0400 (Eastern Daylight Time)
 */
(function( window, undefined ) {
var
	// A central reference to the root jQuery(document)
	rootjQuery,

	// The deferred used on DOM ready
	readyList,

	// Use the correct document accordingly with window argument (sandbox)
	document = window.document,
	location = window.location,
	navigator = window.navigator,

	// Map over jQuery in case of overwrite
	_jQuery = window.jQuery,

	// Map over the $ in case of overwrite
	_$ = window.$,

	// Save a reference to some core methods
	core_push = Array.prototype.push,
	core_slice = Array.prototype.slice,
	core_indexOf = Array.prototype.indexOf,
	core_toString = Object.prototype.toString,
	core_hasOwn = Object.prototype.hasOwnProperty,
	core_trim = String.prototype.trim,

	// Define a local copy of jQuery
	jQuery = function( selector, context ) {
		// The jQuery object is actually just the init constructor 'enhanced'
		return new jQuery.fn.init( selector, context, rootjQuery );
	},

	// Used for matching numbers
	core_pnum = /[\-+]?(?:\d*\.|)\d+(?:[eE][\-+]?\d+|)/.source,

	// Used for detecting and trimming whitespace
	core_rnotwhite = /\S/,
	core_rspace = /\s+/,

	// Make sure we trim BOM and NBSP (here's looking at you, Safari 5.0 and IE)
	rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,

	// A simple way to check for HTML strings
	// Prioritize #id over <tag> to avoid XSS via location.hash (#9521)
	rquickExpr = /^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,

	// Match a standalone tag
	rsingleTag = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,

	// JSON RegExp
	rvalidchars = /^[\],:{}\s]*$/,
	rvalidbraces = /(?:^|:|,)(?:\s*\[)+/g,
	rvalidescape = /\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,
	rvalidtokens = /"[^"\\\r\n]*"|true|false|null|-?(?:\d\d*\.|)\d+(?:[eE][\-+]?\d+|)/g,

	// Matches dashed string for camelizing
	rmsPrefix = /^-ms-/,
	rdashAlpha = /-([\da-z])/gi,

	// Used by jQuery.camelCase as callback to replace()
	fcamelCase = function( all, letter ) {
		return ( letter + "" ).toUpperCase();
	},

	// The ready event handler and self cleanup method
	DOMContentLoaded = function() {
		if ( document.addEventListener ) {
			document.removeEventListener( "DOMContentLoaded", DOMContentLoaded, false );
			jQuery.ready();
		} else if ( document.readyState === "complete" ) {
			// we're here because readyState === "complete" in oldIE
			// which is good enough for us to call the dom ready!
			document.detachEvent( "onreadystatechange", DOMContentLoaded );
			jQuery.ready();
		}
	},

	// [[Class]] -> type pairs
	class2type = {};

jQuery.fn = jQuery.prototype = {
	constructor: jQuery,
	init: function( selector, context, rootjQuery ) {
		var match, elem, ret, doc;

		// Handle $(""), $(null), $(undefined), $(false)
		if ( !selector ) {
			return this;
		}

		// Handle $(DOMElement)
		if ( selector.nodeType ) {
			this.context = this[0] = selector;
			this.length = 1;
			return this;
		}

		// Handle HTML strings
		if ( typeof selector === "string" ) {
			if ( selector.charAt(0) === "<" && selector.charAt( selector.length - 1 ) === ">" && selector.length >= 3 ) {
				// Assume that strings that start and end with <> are HTML and skip the regex check
				match = [ null, selector, null ];

			} else {
				match = rquickExpr.exec( selector );
			}

			// Match html or make sure no context is specified for #id
			if ( match && (match[1] || !context) ) {

				// HANDLE: $(html) -> $(array)
				if ( match[1] ) {
					context = context instanceof jQuery ? context[0] : context;
					doc = ( context && context.nodeType ? context.ownerDocument || context : document );

					// scripts is true for back-compat
					selector = jQuery.parseHTML( match[1], doc, true );
					if ( rsingleTag.test( match[1] ) && jQuery.isPlainObject( context ) ) {
						this.attr.call( selector, context, true );
					}

					return jQuery.merge( this, selector );

				// HANDLE: $(#id)
				} else {
					elem = document.getElementById( match[2] );

					// Check parentNode to catch when Blackberry 4.6 returns
					// nodes that are no longer in the document #6963
					if ( elem && elem.parentNode ) {
						// Handle the case where IE and Opera return items
						// by name instead of ID
						if ( elem.id !== match[2] ) {
							return rootjQuery.find( selector );
						}

						// Otherwise, we inject the element directly into the jQuery object
						this.length = 1;
						this[0] = elem;
					}

					this.context = document;
					this.selector = selector;
					return this;
				}

			// HANDLE: $(expr, $(...))
			} else if ( !context || context.jquery ) {
				return ( context || rootjQuery ).find( selector );

			// HANDLE: $(expr, context)
			// (which is just equivalent to: $(context).find(expr)
			} else {
				return this.constructor( context ).find( selector );
			}

		// HANDLE: $(function)
		// Shortcut for document ready
		} else if ( jQuery.isFunction( selector ) ) {
			return rootjQuery.ready( selector );
		}

		if ( selector.selector !== undefined ) {
			this.selector = selector.selector;
			this.context = selector.context;
		}

		return jQuery.makeArray( selector, this );
	},

	// Start with an empty selector
	selector: "",

	// The current version of jQuery being used
	jquery: "1.8.1",

	// The default length of a jQuery object is 0
	length: 0,

	// The number of elements contained in the matched element set
	size: function() {
		return this.length;
	},

	toArray: function() {
		return core_slice.call( this );
	},

	// Get the Nth element in the matched element set OR
	// Get the whole matched element set as a clean array
	get: function( num ) {
		return num == null ?

			// Return a 'clean' array
			this.toArray() :

			// Return just the object
			( num < 0 ? this[ this.length + num ] : this[ num ] );
	},

	// Take an array of elements and push it onto the stack
	// (returning the new matched element set)
	pushStack: function( elems, name, selector ) {

		// Build a new jQuery matched element set
		var ret = jQuery.merge( this.constructor(), elems );

		// Add the old object onto the stack (as a reference)
		ret.prevObject = this;

		ret.context = this.context;

		if ( name === "find" ) {
			ret.selector = this.selector + ( this.selector ? " " : "" ) + selector;
		} else if ( name ) {
			ret.selector = this.selector + "." + name + "(" + selector + ")";
		}

		// Return the newly-formed element set
		return ret;
	},

	// Execute a callback for every element in the matched set.
	// (You can seed the arguments with an array of args, but this is
	// only used internally.)
	each: function( callback, args ) {
		return jQuery.each( this, callback, args );
	},

	ready: function( fn ) {
		// Add the callback
		jQuery.ready.promise().done( fn );

		return this;
	},

	eq: function( i ) {
		i = +i;
		return i === -1 ?
			this.slice( i ) :
			this.slice( i, i + 1 );
	},

	first: function() {
		return this.eq( 0 );
	},

	last: function() {
		return this.eq( -1 );
	},

	slice: function() {
		return this.pushStack( core_slice.apply( this, arguments ),
			"slice", core_slice.call(arguments).join(",") );
	},

	map: function( callback ) {
		return this.pushStack( jQuery.map(this, function( elem, i ) {
			return callback.call( elem, i, elem );
		}));
	},

	end: function() {
		return this.prevObject || this.constructor(null);
	},

	// For internal use only.
	// Behaves like an Array's method, not like a jQuery method.
	push: core_push,
	sort: [].sort,
	splice: [].splice
};

// Give the init function the jQuery prototype for later instantiation
jQuery.fn.init.prototype = jQuery.fn;

jQuery.extend = jQuery.fn.extend = function() {
	var options, name, src, copy, copyIsArray, clone,
		target = arguments[0] || {},
		i = 1,
		length = arguments.length,
		deep = false;

	// Handle a deep copy situation
	if ( typeof target === "boolean" ) {
		deep = target;
		target = arguments[1] || {};
		// skip the boolean and the target
		i = 2;
	}

	// Handle case when target is a string or something (possible in deep copy)
	if ( typeof target !== "object" && !jQuery.isFunction(target) ) {
		target = {};
	}

	// extend jQuery itself if only one argument is passed
	if ( length === i ) {
		target = this;
		--i;
	}

	for ( ; i < length; i++ ) {
		// Only deal with non-null/undefined values
		if ( (options = arguments[ i ]) != null ) {
			// Extend the base object
			for ( name in options ) {
				src = target[ name ];
				copy = options[ name ];

				// Prevent never-ending loop
				if ( target === copy ) {
					continue;
				}

				// Recurse if we're merging plain objects or arrays
				if ( deep && copy && ( jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)) ) ) {
					if ( copyIsArray ) {
						copyIsArray = false;
						clone = src && jQuery.isArray(src) ? src : [];

					} else {
						clone = src && jQuery.isPlainObject(src) ? src : {};
					}

					// Never move original objects, clone them
					target[ name ] = jQuery.extend( deep, clone, copy );

				// Don't bring in undefined values
				} else if ( copy !== undefined ) {
					target[ name ] = copy;
				}
			}
		}
	}

	// Return the modified object
	return target;
};

jQuery.extend({
	noConflict: function( deep ) {
		if ( window.$ === jQuery ) {
			window.$ = _$;
		}

		if ( deep && window.jQuery === jQuery ) {
			window.jQuery = _jQuery;
		}

		return jQuery;
	},

	// Is the DOM ready to be used? Set to true once it occurs.
	isReady: false,

	// A counter to track how many items to wait for before
	// the ready event fires. See #6781
	readyWait: 1,

	// Hold (or release) the ready event
	holdReady: function( hold ) {
		if ( hold ) {
			jQuery.readyWait++;
		} else {
			jQuery.ready( true );
		}
	},

	// Handle when the DOM is ready
	ready: function( wait ) {

		// Abort if there are pending holds or we're already ready
		if ( wait === true ? --jQuery.readyWait : jQuery.isReady ) {
			return;
		}

		// Make sure body exists, at least, in case IE gets a little overzealous (ticket #5443).
		if ( !document.body ) {
			return setTimeout( jQuery.ready, 1 );
		}

		// Remember that the DOM is ready
		jQuery.isReady = true;

		// If a normal DOM Ready event fired, decrement, and wait if need be
		if ( wait !== true && --jQuery.readyWait > 0 ) {
			return;
		}

		// If there are functions bound, to execute
		readyList.resolveWith( document, [ jQuery ] );

		// Trigger any bound ready events
		if ( jQuery.fn.trigger ) {
			jQuery( document ).trigger("ready").off("ready");
		}
	},

	// See test/unit/core.js for details concerning isFunction.
	// Since version 1.3, DOM methods and functions like alert
	// aren't supported. They return false on IE (#2968).
	isFunction: function( obj ) {
		return jQuery.type(obj) === "function";
	},

	isArray: Array.isArray || function( obj ) {
		return jQuery.type(obj) === "array";
	},

	isWindow: function( obj ) {
		return obj != null && obj == obj.window;
	},

	isNumeric: function( obj ) {
		return !isNaN( parseFloat(obj) ) && isFinite( obj );
	},

	type: function( obj ) {
		return obj == null ?
			String( obj ) :
			class2type[ core_toString.call(obj) ] || "object";
	},

	isPlainObject: function( obj ) {
		// Must be an Object.
		// Because of IE, we also have to check the presence of the constructor property.
		// Make sure that DOM nodes and window objects don't pass through, as well
		if ( !obj || jQuery.type(obj) !== "object" || obj.nodeType || jQuery.isWindow( obj ) ) {
			return false;
		}

		try {
			// Not own constructor property must be Object
			if ( obj.constructor &&
				!core_hasOwn.call(obj, "constructor") &&
				!core_hasOwn.call(obj.constructor.prototype, "isPrototypeOf") ) {
				return false;
			}
		} catch ( e ) {
			// IE8,9 Will throw exceptions on certain host objects #9897
			return false;
		}

		// Own properties are enumerated firstly, so to speed up,
		// if last one is own, then all properties are own.

		var key;
		for ( key in obj ) {}

		return key === undefined || core_hasOwn.call( obj, key );
	},

	isEmptyObject: function( obj ) {
		var name;
		for ( name in obj ) {
			return false;
		}
		return true;
	},

	error: function( msg ) {
		throw new Error( msg );
	},

	// data: string of html
	// context (optional): If specified, the fragment will be created in this context, defaults to document
	// scripts (optional): If true, will include scripts passed in the html string
	parseHTML: function( data, context, scripts ) {
		var parsed;
		if ( !data || typeof data !== "string" ) {
			return null;
		}
		if ( typeof context === "boolean" ) {
			scripts = context;
			context = 0;
		}
		context = context || document;

		// Single tag
		if ( (parsed = rsingleTag.exec( data )) ) {
			return [ context.createElement( parsed[1] ) ];
		}

		parsed = jQuery.buildFragment( [ data ], context, scripts ? null : [] );
		return jQuery.merge( [],
			(parsed.cacheable ? jQuery.clone( parsed.fragment ) : parsed.fragment).childNodes );
	},

	parseJSON: function( data ) {
		if ( !data || typeof data !== "string") {
			return null;
		}

		// Make sure leading/trailing whitespace is removed (IE can't handle it)
		data = jQuery.trim( data );

		// Attempt to parse using the native JSON parser first
		if ( window.JSON && window.JSON.parse ) {
			return window.JSON.parse( data );
		}

		// Make sure the incoming data is actual JSON
		// Logic borrowed from http://json.org/json2.js
		if ( rvalidchars.test( data.replace( rvalidescape, "@" )
			.replace( rvalidtokens, "]" )
			.replace( rvalidbraces, "")) ) {

			return ( new Function( "return " + data ) )();

		}
		jQuery.error( "Invalid JSON: " + data );
	},

	// Cross-browser xml parsing
	parseXML: function( data ) {
		var xml, tmp;
		if ( !data || typeof data !== "string" ) {
			return null;
		}
		try {
			if ( window.DOMParser ) { // Standard
				tmp = new DOMParser();
				xml = tmp.parseFromString( data , "text/xml" );
			} else { // IE
				xml = new ActiveXObject( "Microsoft.XMLDOM" );
				xml.async = "false";
				xml.loadXML( data );
			}
		} catch( e ) {
			xml = undefined;
		}
		if ( !xml || !xml.documentElement || xml.getElementsByTagName( "parsererror" ).length ) {
			jQuery.error( "Invalid XML: " + data );
		}
		return xml;
	},

	noop: function() {},

	// Evaluates a script in a global context
	// Workarounds based on findings by Jim Driscoll
	// http://weblogs.java.net/blog/driscoll/archive/2009/09/08/eval-javascript-global-context
	globalEval: function( data ) {
		if ( data && core_rnotwhite.test( data ) ) {
			// We use execScript on Internet Explorer
			// We use an anonymous function so that context is window
			// rather than jQuery in Firefox
			( window.execScript || function( data ) {
				window[ "eval" ].call( window, data );
			} )( data );
		}
	},

	// Convert dashed to camelCase; used by the css and data modules
	// Microsoft forgot to hump their vendor prefix (#9572)
	camelCase: function( string ) {
		return string.replace( rmsPrefix, "ms-" ).replace( rdashAlpha, fcamelCase );
	},

	nodeName: function( elem, name ) {
		return elem.nodeName && elem.nodeName.toUpperCase() === name.toUpperCase();
	},

	// args is for internal usage only
	each: function( obj, callback, args ) {
		var name,
			i = 0,
			length = obj.length,
			isObj = length === undefined || jQuery.isFunction( obj );

		if ( args ) {
			if ( isObj ) {
				for ( name in obj ) {
					if ( callback.apply( obj[ name ], args ) === false ) {
						break;
					}
				}
			} else {
				for ( ; i < length; ) {
					if ( callback.apply( obj[ i++ ], args ) === false ) {
						break;
					}
				}
			}

		// A special, fast, case for the most common use of each
		} else {
			if ( isObj ) {
				for ( name in obj ) {
					if ( callback.call( obj[ name ], name, obj[ name ] ) === false ) {
						break;
					}
				}
			} else {
				for ( ; i < length; ) {
					if ( callback.call( obj[ i ], i, obj[ i++ ] ) === false ) {
						break;
					}
				}
			}
		}

		return obj;
	},

	// Use native String.trim function wherever possible
	trim: core_trim && !core_trim.call("\uFEFF\xA0") ?
		function( text ) {
			return text == null ?
				"" :
				core_trim.call( text );
		} :

		// Otherwise use our own trimming functionality
		function( text ) {
			return text == null ?
				"" :
				text.toString().replace( rtrim, "" );
		},

	// results is for internal usage only
	makeArray: function( arr, results ) {
		var type,
			ret = results || [];

		if ( arr != null ) {
			// The window, strings (and functions) also have 'length'
			// Tweaked logic slightly to handle Blackberry 4.7 RegExp issues #6930
			type = jQuery.type( arr );

			if ( arr.length == null || type === "string" || type === "function" || type === "regexp" || jQuery.isWindow( arr ) ) {
				core_push.call( ret, arr );
			} else {
				jQuery.merge( ret, arr );
			}
		}

		return ret;
	},

	inArray: function( elem, arr, i ) {
		var len;

		if ( arr ) {
			if ( core_indexOf ) {
				return core_indexOf.call( arr, elem, i );
			}

			len = arr.length;
			i = i ? i < 0 ? Math.max( 0, len + i ) : i : 0;

			for ( ; i < len; i++ ) {
				// Skip accessing in sparse arrays
				if ( i in arr && arr[ i ] === elem ) {
					return i;
				}
			}
		}

		return -1;
	},

	merge: function( first, second ) {
		var l = second.length,
			i = first.length,
			j = 0;

		if ( typeof l === "number" ) {
			for ( ; j < l; j++ ) {
				first[ i++ ] = second[ j ];
			}

		} else {
			while ( second[j] !== undefined ) {
				first[ i++ ] = second[ j++ ];
			}
		}

		first.length = i;

		return first;
	},

	grep: function( elems, callback, inv ) {
		var retVal,
			ret = [],
			i = 0,
			length = elems.length;
		inv = !!inv;

		// Go through the array, only saving the items
		// that pass the validator function
		for ( ; i < length; i++ ) {
			retVal = !!callback( elems[ i ], i );
			if ( inv !== retVal ) {
				ret.push( elems[ i ] );
			}
		}

		return ret;
	},

	// arg is for internal usage only
	map: function( elems, callback, arg ) {
		var value, key,
			ret = [],
			i = 0,
			length = elems.length,
			// jquery objects are treated as arrays
			isArray = elems instanceof jQuery || length !== undefined && typeof length === "number" && ( ( length > 0 && elems[ 0 ] && elems[ length -1 ] ) || length === 0 || jQuery.isArray( elems ) ) ;

		// Go through the array, translating each of the items to their
		if ( isArray ) {
			for ( ; i < length; i++ ) {
				value = callback( elems[ i ], i, arg );

				if ( value != null ) {
					ret[ ret.length ] = value;
				}
			}

		// Go through every key on the object,
		} else {
			for ( key in elems ) {
				value = callback( elems[ key ], key, arg );

				if ( value != null ) {
					ret[ ret.length ] = value;
				}
			}
		}

		// Flatten any nested arrays
		return ret.concat.apply( [], ret );
	},

	// A global GUID counter for objects
	guid: 1,

	// Bind a function to a context, optionally partially applying any
	// arguments.
	proxy: function( fn, context ) {
		var tmp, args, proxy;

		if ( typeof context === "string" ) {
			tmp = fn[ context ];
			context = fn;
			fn = tmp;
		}

		// Quick check to determine if target is callable, in the spec
		// this throws a TypeError, but we will just return undefined.
		if ( !jQuery.isFunction( fn ) ) {
			return undefined;
		}

		// Simulated bind
		args = core_slice.call( arguments, 2 );
		proxy = function() {
			return fn.apply( context, args.concat( core_slice.call( arguments ) ) );
		};

		// Set the guid of unique handler to the same of original handler, so it can be removed
		proxy.guid = fn.guid = fn.guid || proxy.guid || jQuery.guid++;

		return proxy;
	},

	// Multifunctional method to get and set values of a collection
	// The value/s can optionally be executed if it's a function
	access: function( elems, fn, key, value, chainable, emptyGet, pass ) {
		var exec,
			bulk = key == null,
			i = 0,
			length = elems.length;

		// Sets many values
		if ( key && typeof key === "object" ) {
			for ( i in key ) {
				jQuery.access( elems, fn, i, key[i], 1, emptyGet, value );
			}
			chainable = 1;

		// Sets one value
		} else if ( value !== undefined ) {
			// Optionally, function values get executed if exec is true
			exec = pass === undefined && jQuery.isFunction( value );

			if ( bulk ) {
				// Bulk operations only iterate when executing function values
				if ( exec ) {
					exec = fn;
					fn = function( elem, key, value ) {
						return exec.call( jQuery( elem ), value );
					};

				// Otherwise they run against the entire set
				} else {
					fn.call( elems, value );
					fn = null;
				}
			}

			if ( fn ) {
				for (; i < length; i++ ) {
					fn( elems[i], key, exec ? value.call( elems[i], i, fn( elems[i], key ) ) : value, pass );
				}
			}

			chainable = 1;
		}

		return chainable ?
			elems :

			// Gets
			bulk ?
				fn.call( elems ) :
				length ? fn( elems[0], key ) : emptyGet;
	},

	now: function() {
		return ( new Date() ).getTime();
	}
});

jQuery.ready.promise = function( obj ) {
	if ( !readyList ) {

		readyList = jQuery.Deferred();

		// Catch cases where $(document).ready() is called after the browser event has already occurred.
		// we once tried to use readyState "interactive" here, but it caused issues like the one
		// discovered by ChrisS here: http://bugs.jquery.com/ticket/12282#comment:15
		if ( document.readyState === "complete" ) {
			// Handle it asynchronously to allow scripts the opportunity to delay ready
			setTimeout( jQuery.ready, 1 );

		// Standards-based browsers support DOMContentLoaded
		} else if ( document.addEventListener ) {
			// Use the handy event callback
			document.addEventListener( "DOMContentLoaded", DOMContentLoaded, false );

			// A fallback to window.onload, that will always work
			window.addEventListener( "load", jQuery.ready, false );

		// If IE event model is used
		} else {
			// Ensure firing before onload, maybe late but safe also for iframes
			document.attachEvent( "onreadystatechange", DOMContentLoaded );

			// A fallback to window.onload, that will always work
			window.attachEvent( "onload", jQuery.ready );

			// If IE and not a frame
			// continually check to see if the document is ready
			var top = false;

			try {
				top = window.frameElement == null && document.documentElement;
			} catch(e) {}

			if ( top && top.doScroll ) {
				(function doScrollCheck() {
					if ( !jQuery.isReady ) {

						try {
							// Use the trick by Diego Perini
							// http://javascript.nwbox.com/IEContentLoaded/
							top.doScroll("left");
						} catch(e) {
							return setTimeout( doScrollCheck, 50 );
						}

						// and execute any waiting functions
						jQuery.ready();
					}
				})();
			}
		}
	}
	return readyList.promise( obj );
};

// Populate the class2type map
jQuery.each("Boolean Number String Function Array Date RegExp Object".split(" "), function(i, name) {
	class2type[ "[object " + name + "]" ] = name.toLowerCase();
});

// All jQuery objects should point back to these
rootjQuery = jQuery(document);
// String to Object options format cache
var optionsCache = {};

// Convert String-formatted options into Object-formatted ones and store in cache
function createOptions( options ) {
	var object = optionsCache[ options ] = {};
	jQuery.each( options.split( core_rspace ), function( _, flag ) {
		object[ flag ] = true;
	});
	return object;
}

/*
 * Create a callback list using the following parameters:
 *
 *	options: an optional list of space-separated options that will change how
 *			the callback list behaves or a more traditional option object
 *
 * By default a callback list will act like an event callback list and can be
 * "fired" multiple times.
 *
 * Possible options:
 *
 *	once:			will ensure the callback list can only be fired once (like a Deferred)
 *
 *	memory:			will keep track of previous values and will call any callback added
 *					after the list has been fired right away with the latest "memorized"
 *					values (like a Deferred)
 *
 *	unique:			will ensure a callback can only be added once (no duplicate in the list)
 *
 *	stopOnFalse:	interrupt callings when a callback returns false
 *
 */
jQuery.Callbacks = function( options ) {

	// Convert options from String-formatted to Object-formatted if needed
	// (we check in cache first)
	options = typeof options === "string" ?
		( optionsCache[ options ] || createOptions( options ) ) :
		jQuery.extend( {}, options );

	var // Last fire value (for non-forgettable lists)
		memory,
		// Flag to know if list was already fired
		fired,
		// Flag to know if list is currently firing
		firing,
		// First callback to fire (used internally by add and fireWith)
		firingStart,
		// End of the loop when firing
		firingLength,
		// Index of currently firing callback (modified by remove if needed)
		firingIndex,
		// Actual callback list
		list = [],
		// Stack of fire calls for repeatable lists
		stack = !options.once && [],
		// Fire callbacks
		fire = function( data ) {
			memory = options.memory && data;
			fired = true;
			firingIndex = firingStart || 0;
			firingStart = 0;
			firingLength = list.length;
			firing = true;
			for ( ; list && firingIndex < firingLength; firingIndex++ ) {
				if ( list[ firingIndex ].apply( data[ 0 ], data[ 1 ] ) === false && options.stopOnFalse ) {
					memory = false; // To prevent further calls using add
					break;
				}
			}
			firing = false;
			if ( list ) {
				if ( stack ) {
					if ( stack.length ) {
						fire( stack.shift() );
					}
				} else if ( memory ) {
					list = [];
				} else {
					self.disable();
				}
			}
		},
		// Actual Callbacks object
		self = {
			// Add a callback or a collection of callbacks to the list
			add: function() {
				if ( list ) {
					// First, we save the current length
					var start = list.length;
					(function add( args ) {
						jQuery.each( args, function( _, arg ) {
							var type = jQuery.type( arg );
							if ( type === "function" && ( !options.unique || !self.has( arg ) ) ) {
								list.push( arg );
							} else if ( arg && arg.length && type !== "string" ) {
								// Inspect recursively
								add( arg );
							}
						});
					})( arguments );
					// Do we need to add the callbacks to the
					// current firing batch?
					if ( firing ) {
						firingLength = list.length;
					// With memory, if we're not firing then
					// we should call right away
					} else if ( memory ) {
						firingStart = start;
						fire( memory );
					}
				}
				return this;
			},
			// Remove a callback from the list
			remove: function() {
				if ( list ) {
					jQuery.each( arguments, function( _, arg ) {
						var index;
						while( ( index = jQuery.inArray( arg, list, index ) ) > -1 ) {
							list.splice( index, 1 );
							// Handle firing indexes
							if ( firing ) {
								if ( index <= firingLength ) {
									firingLength--;
								}
								if ( index <= firingIndex ) {
									firingIndex--;
								}
							}
						}
					});
				}
				return this;
			},
			// Control if a given callback is in the list
			has: function( fn ) {
				return jQuery.inArray( fn, list ) > -1;
			},
			// Remove all callbacks from the list
			empty: function() {
				list = [];
				return this;
			},
			// Have the list do nothing anymore
			disable: function() {
				list = stack = memory = undefined;
				return this;
			},
			// Is it disabled?
			disabled: function() {
				return !list;
			},
			// Lock the list in its current state
			lock: function() {
				stack = undefined;
				if ( !memory ) {
					self.disable();
				}
				return this;
			},
			// Is it locked?
			locked: function() {
				return !stack;
			},
			// Call all callbacks with the given context and arguments
			fireWith: function( context, args ) {
				args = args || [];
				args = [ context, args.slice ? args.slice() : args ];
				if ( list && ( !fired || stack ) ) {
					if ( firing ) {
						stack.push( args );
					} else {
						fire( args );
					}
				}
				return this;
			},
			// Call all the callbacks with the given arguments
			fire: function() {
				self.fireWith( this, arguments );
				return this;
			},
			// To know if the callbacks have already been called at least once
			fired: function() {
				return !!fired;
			}
		};

	return self;
};
jQuery.extend({

	Deferred: function( func ) {
		var tuples = [
				// action, add listener, listener list, final state
				[ "resolve", "done", jQuery.Callbacks("once memory"), "resolved" ],
				[ "reject", "fail", jQuery.Callbacks("once memory"), "rejected" ],
				[ "notify", "progress", jQuery.Callbacks("memory") ]
			],
			state = "pending",
			promise = {
				state: function() {
					return state;
				},
				always: function() {
					deferred.done( arguments ).fail( arguments );
					return this;
				},
				then: function( /* fnDone, fnFail, fnProgress */ ) {
					var fns = arguments;
					return jQuery.Deferred(function( newDefer ) {
						jQuery.each( tuples, function( i, tuple ) {
							var action = tuple[ 0 ],
								fn = fns[ i ];
							// deferred[ done | fail | progress ] for forwarding actions to newDefer
							deferred[ tuple[1] ]( jQuery.isFunction( fn ) ?
								function() {
									var returned = fn.apply( this, arguments );
									if ( returned && jQuery.isFunction( returned.promise ) ) {
										returned.promise()
											.done( newDefer.resolve )
											.fail( newDefer.reject )
											.progress( newDefer.notify );
									} else {
										newDefer[ action + "With" ]( this === deferred ? newDefer : this, [ returned ] );
									}
								} :
								newDefer[ action ]
							);
						});
						fns = null;
					}).promise();
				},
				// Get a promise for this deferred
				// If obj is provided, the promise aspect is added to the object
				promise: function( obj ) {
					return typeof obj === "object" ? jQuery.extend( obj, promise ) : promise;
				}
			},
			deferred = {};

		// Keep pipe for back-compat
		promise.pipe = promise.then;

		// Add list-specific methods
		jQuery.each( tuples, function( i, tuple ) {
			var list = tuple[ 2 ],
				stateString = tuple[ 3 ];

			// promise[ done | fail | progress ] = list.add
			promise[ tuple[1] ] = list.add;

			// Handle state
			if ( stateString ) {
				list.add(function() {
					// state = [ resolved | rejected ]
					state = stateString;

				// [ reject_list | resolve_list ].disable; progress_list.lock
				}, tuples[ i ^ 1 ][ 2 ].disable, tuples[ 2 ][ 2 ].lock );
			}

			// deferred[ resolve | reject | notify ] = list.fire
			deferred[ tuple[0] ] = list.fire;
			deferred[ tuple[0] + "With" ] = list.fireWith;
		});

		// Make the deferred a promise
		promise.promise( deferred );

		// Call given func if any
		if ( func ) {
			func.call( deferred, deferred );
		}

		// All done!
		return deferred;
	},

	// Deferred helper
	when: function( subordinate /* , ..., subordinateN */ ) {
		var i = 0,
			resolveValues = core_slice.call( arguments ),
			length = resolveValues.length,

			// the count of uncompleted subordinates
			remaining = length !== 1 || ( subordinate && jQuery.isFunction( subordinate.promise ) ) ? length : 0,

			// the master Deferred. If resolveValues consist of only a single Deferred, just use that.
			deferred = remaining === 1 ? subordinate : jQuery.Deferred(),

			// Update function for both resolve and progress values
			updateFunc = function( i, contexts, values ) {
				return function( value ) {
					contexts[ i ] = this;
					values[ i ] = arguments.length > 1 ? core_slice.call( arguments ) : value;
					if( values === progressValues ) {
						deferred.notifyWith( contexts, values );
					} else if ( !( --remaining ) ) {
						deferred.resolveWith( contexts, values );
					}
				};
			},

			progressValues, progressContexts, resolveContexts;

		// add listeners to Deferred subordinates; treat others as resolved
		if ( length > 1 ) {
			progressValues = new Array( length );
			progressContexts = new Array( length );
			resolveContexts = new Array( length );
			for ( ; i < length; i++ ) {
				if ( resolveValues[ i ] && jQuery.isFunction( resolveValues[ i ].promise ) ) {
					resolveValues[ i ].promise()
						.done( updateFunc( i, resolveContexts, resolveValues ) )
						.fail( deferred.reject )
						.progress( updateFunc( i, progressContexts, progressValues ) );
				} else {
					--remaining;
				}
			}
		}

		// if we're not waiting on anything, resolve the master
		if ( !remaining ) {
			deferred.resolveWith( resolveContexts, resolveValues );
		}

		return deferred.promise();
	}
});
jQuery.support = (function() {

	var support,
		all,
		a,
		select,
		opt,
		input,
		fragment,
		eventName,
		i,
		isSupported,
		clickFn,
		div = document.createElement("div");

	// Preliminary tests
	div.setAttribute( "className", "t" );
	div.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>";

	all = div.getElementsByTagName("*");
	a = div.getElementsByTagName("a")[ 0 ];
	a.style.cssText = "top:1px;float:left;opacity:.5";

	// Can't get basic test support
	if ( !all || !all.length || !a ) {
		return {};
	}

	// First batch of supports tests
	select = document.createElement("select");
	opt = select.appendChild( document.createElement("option") );
	input = div.getElementsByTagName("input")[ 0 ];

	support = {
		// IE strips leading whitespace when .innerHTML is used
		leadingWhitespace: ( div.firstChild.nodeType === 3 ),

		// Make sure that tbody elements aren't automatically inserted
		// IE will insert them into empty tables
		tbody: !div.getElementsByTagName("tbody").length,

		// Make sure that link elements get serialized correctly by innerHTML
		// This requires a wrapper element in IE
		htmlSerialize: !!div.getElementsByTagName("link").length,

		// Get the style information from getAttribute
		// (IE uses .cssText instead)
		style: /top/.test( a.getAttribute("style") ),

		// Make sure that URLs aren't manipulated
		// (IE normalizes it by default)
		hrefNormalized: ( a.getAttribute("href") === "/a" ),

		// Make sure that element opacity exists
		// (IE uses filter instead)
		// Use a regex to work around a WebKit issue. See #5145
		opacity: /^0.5/.test( a.style.opacity ),

		// Verify style float existence
		// (IE uses styleFloat instead of cssFloat)
		cssFloat: !!a.style.cssFloat,

		// Make sure that if no value is specified for a checkbox
		// that it defaults to "on".
		// (WebKit defaults to "" instead)
		checkOn: ( input.value === "on" ),

		// Make sure that a selected-by-default option has a working selected property.
		// (WebKit defaults to false instead of true, IE too, if it's in an optgroup)
		optSelected: opt.selected,

		// Test setAttribute on camelCase class. If it works, we need attrFixes when doing get/setAttribute (ie6/7)
		getSetAttribute: div.className !== "t",

		// Tests for enctype support on a form(#6743)
		enctype: !!document.createElement("form").enctype,

		// Makes sure cloning an html5 element does not cause problems
		// Where outerHTML is undefined, this still works
		html5Clone: document.createElement("nav").cloneNode( true ).outerHTML !== "<:nav></:nav>",

		// jQuery.support.boxModel DEPRECATED in 1.8 since we don't support Quirks Mode
		boxModel: ( document.compatMode === "CSS1Compat" ),

		// Will be defined later
		submitBubbles: true,
		changeBubbles: true,
		focusinBubbles: false,
		deleteExpando: true,
		noCloneEvent: true,
		inlineBlockNeedsLayout: false,
		shrinkWrapBlocks: false,
		reliableMarginRight: true,
		boxSizingReliable: true,
		pixelPosition: false
	};

	// Make sure checked status is properly cloned
	input.checked = true;
	support.noCloneChecked = input.cloneNode( true ).checked;

	// Make sure that the options inside disabled selects aren't marked as disabled
	// (WebKit marks them as disabled)
	select.disabled = true;
	support.optDisabled = !opt.disabled;

	// Test to see if it's possible to delete an expando from an element
	// Fails in Internet Explorer
	try {
		delete div.test;
	} catch( e ) {
		support.deleteExpando = false;
	}

	if ( !div.addEventListener && div.attachEvent && div.fireEvent ) {
		div.attachEvent( "onclick", clickFn = function() {
			// Cloning a node shouldn't copy over any
			// bound event handlers (IE does this)
			support.noCloneEvent = false;
		});
		div.cloneNode( true ).fireEvent("onclick");
		div.detachEvent( "onclick", clickFn );
	}

	// Check if a radio maintains its value
	// after being appended to the DOM
	input = document.createElement("input");
	input.value = "t";
	input.setAttribute( "type", "radio" );
	support.radioValue = input.value === "t";

	input.setAttribute( "checked", "checked" );

	// #11217 - WebKit loses check when the name is after the checked attribute
	input.setAttribute( "name", "t" );

	div.appendChild( input );
	fragment = document.createDocumentFragment();
	fragment.appendChild( div.lastChild );

	// WebKit doesn't clone checked state correctly in fragments
	support.checkClone = fragment.cloneNode( true ).cloneNode( true ).lastChild.checked;

	// Check if a disconnected checkbox will retain its checked
	// value of true after appended to the DOM (IE6/7)
	support.appendChecked = input.checked;

	fragment.removeChild( input );
	fragment.appendChild( div );

	// Technique from Juriy Zaytsev
	// http://perfectionkills.com/detecting-event-support-without-browser-sniffing/
	// We only care about the case where non-standard event systems
	// are used, namely in IE. Short-circuiting here helps us to
	// avoid an eval call (in setAttribute) which can cause CSP
	// to go haywire. See: https://developer.mozilla.org/en/Security/CSP
	if ( div.attachEvent ) {
		for ( i in {
			submit: true,
			change: true,
			focusin: true
		}) {
			eventName = "on" + i;
			isSupported = ( eventName in div );
			if ( !isSupported ) {
				div.setAttribute( eventName, "return;" );
				isSupported = ( typeof div[ eventName ] === "function" );
			}
			support[ i + "Bubbles" ] = isSupported;
		}
	}

	// Run tests that need a body at doc ready
	jQuery(function() {
		var container, div, tds, marginDiv,
			divReset = "padding:0;margin:0;border:0;display:block;overflow:hidden;",
			body = document.getElementsByTagName("body")[0];

		if ( !body ) {
			// Return for frameset docs that don't have a body
			return;
		}

		container = document.createElement("div");
		container.style.cssText = "visibility:hidden;border:0;width:0;height:0;position:static;top:0;margin-top:1px";
		body.insertBefore( container, body.firstChild );

		// Construct the test element
		div = document.createElement("div");
		container.appendChild( div );

		// Check if table cells still have offsetWidth/Height when they are set
		// to display:none and there are still other visible table cells in a
		// table row; if so, offsetWidth/Height are not reliable for use when
		// determining if an element has been hidden directly using
		// display:none (it is still safe to use offsets if a parent element is
		// hidden; don safety goggles and see bug #4512 for more information).
		// (only IE 8 fails this test)
		div.innerHTML = "<table><tr><td></td><td>t</td></tr></table>";
		tds = div.getElementsByTagName("td");
		tds[ 0 ].style.cssText = "padding:0;margin:0;border:0;display:none";
		isSupported = ( tds[ 0 ].offsetHeight === 0 );

		tds[ 0 ].style.display = "";
		tds[ 1 ].style.display = "none";

		// Check if empty table cells still have offsetWidth/Height
		// (IE <= 8 fail this test)
		support.reliableHiddenOffsets = isSupported && ( tds[ 0 ].offsetHeight === 0 );

		// Check box-sizing and margin behavior
		div.innerHTML = "";
		div.style.cssText = "box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;";
		support.boxSizing = ( div.offsetWidth === 4 );
		support.doesNotIncludeMarginInBodyOffset = ( body.offsetTop !== 1 );

		// NOTE: To any future maintainer, we've window.getComputedStyle
		// because jsdom on node.js will break without it.
		if ( window.getComputedStyle ) {
			support.pixelPosition = ( window.getComputedStyle( div, null ) || {} ).top !== "1%";
			support.boxSizingReliable = ( window.getComputedStyle( div, null ) || { width: "4px" } ).width === "4px";

			// Check if div with explicit width and no margin-right incorrectly
			// gets computed margin-right based on width of container. For more
			// info see bug #3333
			// Fails in WebKit before Feb 2011 nightlies
			// WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
			marginDiv = document.createElement("div");
			marginDiv.style.cssText = div.style.cssText = divReset;
			marginDiv.style.marginRight = marginDiv.style.width = "0";
			div.style.width = "1px";
			div.appendChild( marginDiv );
			support.reliableMarginRight =
				!parseFloat( ( window.getComputedStyle( marginDiv, null ) || {} ).marginRight );
		}

		if ( typeof div.style.zoom !== "undefined" ) {
			// Check if natively block-level elements act like inline-block
			// elements when setting their display to 'inline' and giving
			// them layout
			// (IE < 8 does this)
			div.innerHTML = "";
			div.style.cssText = divReset + "width:1px;padding:1px;display:inline;zoom:1";
			support.inlineBlockNeedsLayout = ( div.offsetWidth === 3 );

			// Check if elements with layout shrink-wrap their children
			// (IE 6 does this)
			div.style.display = "block";
			div.style.overflow = "visible";
			div.innerHTML = "<div></div>";
			div.firstChild.style.width = "5px";
			support.shrinkWrapBlocks = ( div.offsetWidth !== 3 );

			container.style.zoom = 1;
		}

		// Null elements to avoid leaks in IE
		body.removeChild( container );
		container = div = tds = marginDiv = null;
	});

	// Null elements to avoid leaks in IE
	fragment.removeChild( div );
	all = a = select = opt = input = fragment = div = null;

	return support;
})();
var rbrace = /(?:\{[\s\S]*\}|\[[\s\S]*\])$/,
	rmultiDash = /([A-Z])/g;

jQuery.extend({
	cache: {},

	deletedIds: [],

	// Please use with caution
	uuid: 0,

	// Unique for each copy of jQuery on the page
	// Non-digits removed to match rinlinejQuery
	expando: "jQuery" + ( jQuery.fn.jquery + Math.random() ).replace( /\D/g, "" ),

	// The following elements throw uncatchable exceptions if you
	// attempt to add expando properties to them.
	noData: {
		"embed": true,
		// Ban all objects except for Flash (which handle expandos)
		"object": "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",
		"applet": true
	},

	hasData: function( elem ) {
		elem = elem.nodeType ? jQuery.cache[ elem[jQuery.expando] ] : elem[ jQuery.expando ];
		return !!elem && !isEmptyDataObject( elem );
	},

	data: function( elem, name, data, pvt /* Internal Use Only */ ) {
		if ( !jQuery.acceptData( elem ) ) {
			return;
		}

		var thisCache, ret,
			internalKey = jQuery.expando,
			getByName = typeof name === "string",

			// We have to handle DOM nodes and JS objects differently because IE6-7
			// can't GC object references properly across the DOM-JS boundary
			isNode = elem.nodeType,

			// Only DOM nodes need the global jQuery cache; JS object data is
			// attached directly to the object so GC can occur automatically
			cache = isNode ? jQuery.cache : elem,

			// Only defining an ID for JS objects if its cache already exists allows
			// the code to shortcut on the same path as a DOM node with no cache
			id = isNode ? elem[ internalKey ] : elem[ internalKey ] && internalKey;

		// Avoid doing any more work than we need to when trying to get data on an
		// object that has no data at all
		if ( (!id || !cache[id] || (!pvt && !cache[id].data)) && getByName && data === undefined ) {
			return;
		}

		if ( !id ) {
			// Only DOM nodes need a new unique ID for each element since their data
			// ends up in the global cache
			if ( isNode ) {
				elem[ internalKey ] = id = jQuery.deletedIds.pop() || ++jQuery.uuid;
			} else {
				id = internalKey;
			}
		}

		if ( !cache[ id ] ) {
			cache[ id ] = {};

			// Avoids exposing jQuery metadata on plain JS objects when the object
			// is serialized using JSON.stringify
			if ( !isNode ) {
				cache[ id ].toJSON = jQuery.noop;
			}
		}

		// An object can be passed to jQuery.data instead of a key/value pair; this gets
		// shallow copied over onto the existing cache
		if ( typeof name === "object" || typeof name === "function" ) {
			if ( pvt ) {
				cache[ id ] = jQuery.extend( cache[ id ], name );
			} else {
				cache[ id ].data = jQuery.extend( cache[ id ].data, name );
			}
		}

		thisCache = cache[ id ];

		// jQuery data() is stored in a separate object inside the object's internal data
		// cache in order to avoid key collisions between internal data and user-defined
		// data.
		if ( !pvt ) {
			if ( !thisCache.data ) {
				thisCache.data = {};
			}

			thisCache = thisCache.data;
		}

		if ( data !== undefined ) {
			thisCache[ jQuery.camelCase( name ) ] = data;
		}

		// Check for both converted-to-camel and non-converted data property names
		// If a data property was specified
		if ( getByName ) {

			// First Try to find as-is property data
			ret = thisCache[ name ];

			// Test for null|undefined property data
			if ( ret == null ) {

				// Try to find the camelCased property
				ret = thisCache[ jQuery.camelCase( name ) ];
			}
		} else {
			ret = thisCache;
		}

		return ret;
	},

	removeData: function( elem, name, pvt /* Internal Use Only */ ) {
		if ( !jQuery.acceptData( elem ) ) {
			return;
		}

		var thisCache, i, l,

			isNode = elem.nodeType,

			// See jQuery.data for more information
			cache = isNode ? jQuery.cache : elem,
			id = isNode ? elem[ jQuery.expando ] : jQuery.expando;

		// If there is already no cache entry for this object, there is no
		// purpose in continuing
		if ( !cache[ id ] ) {
			return;
		}

		if ( name ) {

			thisCache = pvt ? cache[ id ] : cache[ id ].data;

			if ( thisCache ) {

				// Support array or space separated string names for data keys
				if ( !jQuery.isArray( name ) ) {

					// try the string as a key before any manipulation
					if ( name in thisCache ) {
						name = [ name ];
					} else {

						// split the camel cased version by spaces unless a key with the spaces exists
						name = jQuery.camelCase( name );
						if ( name in thisCache ) {
							name = [ name ];
						} else {
							name = name.split(" ");
						}
					}
				}

				for ( i = 0, l = name.length; i < l; i++ ) {
					delete thisCache[ name[i] ];
				}

				// If there is no data left in the cache, we want to continue
				// and let the cache object itself get destroyed
				if ( !( pvt ? isEmptyDataObject : jQuery.isEmptyObject )( thisCache ) ) {
					return;
				}
			}
		}

		// See jQuery.data for more information
		if ( !pvt ) {
			delete cache[ id ].data;

			// Don't destroy the parent cache unless the internal data object
			// had been the only thing left in it
			if ( !isEmptyDataObject( cache[ id ] ) ) {
				return;
			}
		}

		// Destroy the cache
		if ( isNode ) {
			jQuery.cleanData( [ elem ], true );

		// Use delete when supported for expandos or `cache` is not a window per isWindow (#10080)
		} else if ( jQuery.support.deleteExpando || cache != cache.window ) {
			delete cache[ id ];

		// When all else fails, null
		} else {
			cache[ id ] = null;
		}
	},

	// For internal use only.
	_data: function( elem, name, data ) {
		return jQuery.data( elem, name, data, true );
	},

	// A method for determining if a DOM node can handle the data expando
	acceptData: function( elem ) {
		var noData = elem.nodeName && jQuery.noData[ elem.nodeName.toLowerCase() ];

		// nodes accept data unless otherwise specified; rejection can be conditional
		return !noData || noData !== true && elem.getAttribute("classid") === noData;
	}
});

jQuery.fn.extend({
	data: function( key, value ) {
		var parts, part, attr, name, l,
			elem = this[0],
			i = 0,
			data = null;

		// Gets all values
		if ( key === undefined ) {
			if ( this.length ) {
				data = jQuery.data( elem );

				if ( elem.nodeType === 1 && !jQuery._data( elem, "parsedAttrs" ) ) {
					attr = elem.attributes;
					for ( l = attr.length; i < l; i++ ) {
						name = attr[i].name;

						if ( name.indexOf( "data-" ) === 0 ) {
							name = jQuery.camelCase( name.substring(5) );

							dataAttr( elem, name, data[ name ] );
						}
					}
					jQuery._data( elem, "parsedAttrs", true );
				}
			}

			return data;
		}

		// Sets multiple values
		if ( typeof key === "object" ) {
			return this.each(function() {
				jQuery.data( this, key );
			});
		}

		parts = key.split( ".", 2 );
		parts[1] = parts[1] ? "." + parts[1] : "";
		part = parts[1] + "!";

		return jQuery.access( this, function( value ) {

			if ( value === undefined ) {
				data = this.triggerHandler( "getData" + part, [ parts[0] ] );

				// Try to fetch any internally stored data first
				if ( data === undefined && elem ) {
					data = jQuery.data( elem, key );
					data = dataAttr( elem, key, data );
				}

				return data === undefined && parts[1] ?
					this.data( parts[0] ) :
					data;
			}

			parts[1] = value;
			this.each(function() {
				var self = jQuery( this );

				self.triggerHandler( "setData" + part, parts );
				jQuery.data( this, key, value );
				self.triggerHandler( "changeData" + part, parts );
			});
		}, null, value, arguments.length > 1, null, false );
	},

	removeData: function( key ) {
		return this.each(function() {
			jQuery.removeData( this, key );
		});
	}
});

function dataAttr( elem, key, data ) {
	// If nothing was found internally, try to fetch any
	// data from the HTML5 data-* attribute
	if ( data === undefined && elem.nodeType === 1 ) {

		var name = "data-" + key.replace( rmultiDash, "-$1" ).toLowerCase();

		data = elem.getAttribute( name );

		if ( typeof data === "string" ) {
			try {
				data = data === "true" ? true :
				data === "false" ? false :
				data === "null" ? null :
				// Only convert to a number if it doesn't change the string
				+data + "" === data ? +data :
				rbrace.test( data ) ? jQuery.parseJSON( data ) :
					data;
			} catch( e ) {}

			// Make sure we set the data so it isn't changed later
			jQuery.data( elem, key, data );

		} else {
			data = undefined;
		}
	}

	return data;
}

// checks a cache object for emptiness
function isEmptyDataObject( obj ) {
	var name;
	for ( name in obj ) {

		// if the public data object is empty, the private is still empty
		if ( name === "data" && jQuery.isEmptyObject( obj[name] ) ) {
			continue;
		}
		if ( name !== "toJSON" ) {
			return false;
		}
	}

	return true;
}
jQuery.extend({
	queue: function( elem, type, data ) {
		var queue;

		if ( elem ) {
			type = ( type || "fx" ) + "queue";
			queue = jQuery._data( elem, type );

			// Speed up dequeue by getting out quickly if this is just a lookup
			if ( data ) {
				if ( !queue || jQuery.isArray(data) ) {
					queue = jQuery._data( elem, type, jQuery.makeArray(data) );
				} else {
					queue.push( data );
				}
			}
			return queue || [];
		}
	},

	dequeue: function( elem, type ) {
		type = type || "fx";

		var queue = jQuery.queue( elem, type ),
			startLength = queue.length,
			fn = queue.shift(),
			hooks = jQuery._queueHooks( elem, type ),
			next = function() {
				jQuery.dequeue( elem, type );
			};

		// If the fx queue is dequeued, always remove the progress sentinel
		if ( fn === "inprogress" ) {
			fn = queue.shift();
			startLength--;
		}

		if ( fn ) {

			// Add a progress sentinel to prevent the fx queue from being
			// automatically dequeued
			if ( type === "fx" ) {
				queue.unshift( "inprogress" );
			}

			// clear up the last queue stop function
			delete hooks.stop;
			fn.call( elem, next, hooks );
		}

		if ( !startLength && hooks ) {
			hooks.empty.fire();
		}
	},

	// not intended for public consumption - generates a queueHooks object, or returns the current one
	_queueHooks: function( elem, type ) {
		var key = type + "queueHooks";
		return jQuery._data( elem, key ) || jQuery._data( elem, key, {
			empty: jQuery.Callbacks("once memory").add(function() {
				jQuery.removeData( elem, type + "queue", true );
				jQuery.removeData( elem, key, true );
			})
		});
	}
});

jQuery.fn.extend({
	queue: function( type, data ) {
		var setter = 2;

		if ( typeof type !== "string" ) {
			data = type;
			type = "fx";
			setter--;
		}

		if ( arguments.length < setter ) {
			return jQuery.queue( this[0], type );
		}

		return data === undefined ?
			this :
			this.each(function() {
				var queue = jQuery.queue( this, type, data );

				// ensure a hooks for this queue
				jQuery._queueHooks( this, type );

				if ( type === "fx" && queue[0] !== "inprogress" ) {
					jQuery.dequeue( this, type );
				}
			});
	},
	dequeue: function( type ) {
		return this.each(function() {
			jQuery.dequeue( this, type );
		});
	},
	// Based off of the plugin by Clint Helfers, with permission.
	// http://blindsignals.com/index.php/2009/07/jquery-delay/
	delay: function( time, type ) {
		time = jQuery.fx ? jQuery.fx.speeds[ time ] || time : time;
		type = type || "fx";

		return this.queue( type, function( next, hooks ) {
			var timeout = setTimeout( next, time );
			hooks.stop = function() {
				clearTimeout( timeout );
			};
		});
	},
	clearQueue: function( type ) {
		return this.queue( type || "fx", [] );
	},
	// Get a promise resolved when queues of a certain type
	// are emptied (fx is the type by default)
	promise: function( type, obj ) {
		var tmp,
			count = 1,
			defer = jQuery.Deferred(),
			elements = this,
			i = this.length,
			resolve = function() {
				if ( !( --count ) ) {
					defer.resolveWith( elements, [ elements ] );
				}
			};

		if ( typeof type !== "string" ) {
			obj = type;
			type = undefined;
		}
		type = type || "fx";

		while( i-- ) {
			tmp = jQuery._data( elements[ i ], type + "queueHooks" );
			if ( tmp && tmp.empty ) {
				count++;
				tmp.empty.add( resolve );
			}
		}
		resolve();
		return defer.promise( obj );
	}
});
var nodeHook, boolHook, fixSpecified,
	rclass = /[\t\r\n]/g,
	rreturn = /\r/g,
	rtype = /^(?:button|input)$/i,
	rfocusable = /^(?:button|input|object|select|textarea)$/i,
	rclickable = /^a(?:rea|)$/i,
	rboolean = /^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,
	getSetAttribute = jQuery.support.getSetAttribute;

jQuery.fn.extend({
	attr: function( name, value ) {
		return jQuery.access( this, jQuery.attr, name, value, arguments.length > 1 );
	},

	removeAttr: function( name ) {
		return this.each(function() {
			jQuery.removeAttr( this, name );
		});
	},

	prop: function( name, value ) {
		return jQuery.access( this, jQuery.prop, name, value, arguments.length > 1 );
	},

	removeProp: function( name ) {
		name = jQuery.propFix[ name ] || name;
		return this.each(function() {
			// try/catch handles cases where IE balks (such as removing a property on window)
			try {
				this[ name ] = undefined;
				delete this[ name ];
			} catch( e ) {}
		});
	},

	addClass: function( value ) {
		var classNames, i, l, elem,
			setClass, c, cl;

		if ( jQuery.isFunction( value ) ) {
			return this.each(function( j ) {
				jQuery( this ).addClass( value.call(this, j, this.className) );
			});
		}

		if ( value && typeof value === "string" ) {
			classNames = value.split( core_rspace );

			for ( i = 0, l = this.length; i < l; i++ ) {
				elem = this[ i ];

				if ( elem.nodeType === 1 ) {
					if ( !elem.className && classNames.length === 1 ) {
						elem.className = value;

					} else {
						setClass = " " + elem.className + " ";

						for ( c = 0, cl = classNames.length; c < cl; c++ ) {
							if ( !~setClass.indexOf( " " + classNames[ c ] + " " ) ) {
								setClass += classNames[ c ] + " ";
							}
						}
						elem.className = jQuery.trim( setClass );
					}
				}
			}
		}

		return this;
	},

	removeClass: function( value ) {
		var removes, className, elem, c, cl, i, l;

		if ( jQuery.isFunction( value ) ) {
			return this.each(function( j ) {
				jQuery( this ).removeClass( value.call(this, j, this.className) );
			});
		}
		if ( (value && typeof value === "string") || value === undefined ) {
			removes = ( value || "" ).split( core_rspace );

			for ( i = 0, l = this.length; i < l; i++ ) {
				elem = this[ i ];
				if ( elem.nodeType === 1 && elem.className ) {

					className = (" " + elem.className + " ").replace( rclass, " " );

					// loop over each item in the removal list
					for ( c = 0, cl = removes.length; c < cl; c++ ) {
						// Remove until there is nothing to remove,
						while ( className.indexOf(" " + removes[ c ] + " ") > -1 ) {
							className = className.replace( " " + removes[ c ] + " " , " " );
						}
					}
					elem.className = value ? jQuery.trim( className ) : "";
				}
			}
		}

		return this;
	},

	toggleClass: function( value, stateVal ) {
		var type = typeof value,
			isBool = typeof stateVal === "boolean";

		if ( jQuery.isFunction( value ) ) {
			return this.each(function( i ) {
				jQuery( this ).toggleClass( value.call(this, i, this.className, stateVal), stateVal );
			});
		}

		return this.each(function() {
			if ( type === "string" ) {
				// toggle individual class names
				var className,
					i = 0,
					self = jQuery( this ),
					state = stateVal,
					classNames = value.split( core_rspace );

				while ( (className = classNames[ i++ ]) ) {
					// check each className given, space separated list
					state = isBool ? state : !self.hasClass( className );
					self[ state ? "addClass" : "removeClass" ]( className );
				}

			} else if ( type === "undefined" || type === "boolean" ) {
				if ( this.className ) {
					// store className if set
					jQuery._data( this, "__className__", this.className );
				}

				// toggle whole className
				this.className = this.className || value === false ? "" : jQuery._data( this, "__className__" ) || "";
			}
		});
	},

	hasClass: function( selector ) {
		var className = " " + selector + " ",
			i = 0,
			l = this.length;
		for ( ; i < l; i++ ) {
			if ( this[i].nodeType === 1 && (" " + this[i].className + " ").replace(rclass, " ").indexOf( className ) > -1 ) {
				return true;
			}
		}

		return false;
	},

	val: function( value ) {
		var hooks, ret, isFunction,
			elem = this[0];

		if ( !arguments.length ) {
			if ( elem ) {
				hooks = jQuery.valHooks[ elem.type ] || jQuery.valHooks[ elem.nodeName.toLowerCase() ];

				if ( hooks && "get" in hooks && (ret = hooks.get( elem, "value" )) !== undefined ) {
					return ret;
				}

				ret = elem.value;

				return typeof ret === "string" ?
					// handle most common string cases
					ret.replace(rreturn, "") :
					// handle cases where value is null/undef or number
					ret == null ? "" : ret;
			}

			return;
		}

		isFunction = jQuery.isFunction( value );

		return this.each(function( i ) {
			var val,
				self = jQuery(this);

			if ( this.nodeType !== 1 ) {
				return;
			}

			if ( isFunction ) {
				val = value.call( this, i, self.val() );
			} else {
				val = value;
			}

			// Treat null/undefined as ""; convert numbers to string
			if ( val == null ) {
				val = "";
			} else if ( typeof val === "number" ) {
				val += "";
			} else if ( jQuery.isArray( val ) ) {
				val = jQuery.map(val, function ( value ) {
					return value == null ? "" : value + "";
				});
			}

			hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];

			// If set returns undefined, fall back to normal setting
			if ( !hooks || !("set" in hooks) || hooks.set( this, val, "value" ) === undefined ) {
				this.value = val;
			}
		});
	}
});

jQuery.extend({
	valHooks: {
		option: {
			get: function( elem ) {
				// attributes.value is undefined in Blackberry 4.7 but
				// uses .value. See #6932
				var val = elem.attributes.value;
				return !val || val.specified ? elem.value : elem.text;
			}
		},
		select: {
			get: function( elem ) {
				var value, i, max, option,
					index = elem.selectedIndex,
					values = [],
					options = elem.options,
					one = elem.type === "select-one";

				// Nothing was selected
				if ( index < 0 ) {
					return null;
				}

				// Loop through all the selected options
				i = one ? index : 0;
				max = one ? index + 1 : options.length;
				for ( ; i < max; i++ ) {
					option = options[ i ];

					// Don't return options that are disabled or in a disabled optgroup
					if ( option.selected && (jQuery.support.optDisabled ? !option.disabled : option.getAttribute("disabled") === null) &&
							(!option.parentNode.disabled || !jQuery.nodeName( option.parentNode, "optgroup" )) ) {

						// Get the specific value for the option
						value = jQuery( option ).val();

						// We don't need an array for one selects
						if ( one ) {
							return value;
						}

						// Multi-Selects return an array
						values.push( value );
					}
				}

				// Fixes Bug #2551 -- select.val() broken in IE after form.reset()
				if ( one && !values.length && options.length ) {
					return jQuery( options[ index ] ).val();
				}

				return values;
			},

			set: function( elem, value ) {
				var values = jQuery.makeArray( value );

				jQuery(elem).find("option").each(function() {
					this.selected = jQuery.inArray( jQuery(this).val(), values ) >= 0;
				});

				if ( !values.length ) {
					elem.selectedIndex = -1;
				}
				return values;
			}
		}
	},

	// Unused in 1.8, left in so attrFn-stabbers won't die; remove in 1.9
	attrFn: {},

	attr: function( elem, name, value, pass ) {
		var ret, hooks, notxml,
			nType = elem.nodeType;

		// don't get/set attributes on text, comment and attribute nodes
		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		if ( pass && jQuery.isFunction( jQuery.fn[ name ] ) ) {
			return jQuery( elem )[ name ]( value );
		}

		// Fallback to prop when attributes are not supported
		if ( typeof elem.getAttribute === "undefined" ) {
			return jQuery.prop( elem, name, value );
		}

		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );

		// All attributes are lowercase
		// Grab necessary hook if one is defined
		if ( notxml ) {
			name = name.toLowerCase();
			hooks = jQuery.attrHooks[ name ] || ( rboolean.test( name ) ? boolHook : nodeHook );
		}

		if ( value !== undefined ) {

			if ( value === null ) {
				jQuery.removeAttr( elem, name );
				return;

			} else if ( hooks && "set" in hooks && notxml && (ret = hooks.set( elem, value, name )) !== undefined ) {
				return ret;

			} else {
				elem.setAttribute( name, "" + value );
				return value;
			}

		} else if ( hooks && "get" in hooks && notxml && (ret = hooks.get( elem, name )) !== null ) {
			return ret;

		} else {

			ret = elem.getAttribute( name );

			// Non-existent attributes return null, we normalize to undefined
			return ret === null ?
				undefined :
				ret;
		}
	},

	removeAttr: function( elem, value ) {
		var propName, attrNames, name, isBool,
			i = 0;

		if ( value && elem.nodeType === 1 ) {

			attrNames = value.split( core_rspace );

			for ( ; i < attrNames.length; i++ ) {
				name = attrNames[ i ];

				if ( name ) {
					propName = jQuery.propFix[ name ] || name;
					isBool = rboolean.test( name );

					// See #9699 for explanation of this approach (setting first, then removal)
					// Do not do this for boolean attributes (see #10870)
					if ( !isBool ) {
						jQuery.attr( elem, name, "" );
					}
					elem.removeAttribute( getSetAttribute ? name : propName );

					// Set corresponding property to false for boolean attributes
					if ( isBool && propName in elem ) {
						elem[ propName ] = false;
					}
				}
			}
		}
	},

	attrHooks: {
		type: {
			set: function( elem, value ) {
				// We can't allow the type property to be changed (since it causes problems in IE)
				if ( rtype.test( elem.nodeName ) && elem.parentNode ) {
					jQuery.error( "type property can't be changed" );
				} else if ( !jQuery.support.radioValue && value === "radio" && jQuery.nodeName(elem, "input") ) {
					// Setting the type on a radio button after the value resets the value in IE6-9
					// Reset value to it's default in case type is set after value
					// This is for element creation
					var val = elem.value;
					elem.setAttribute( "type", value );
					if ( val ) {
						elem.value = val;
					}
					return value;
				}
			}
		},
		// Use the value property for back compat
		// Use the nodeHook for button elements in IE6/7 (#1954)
		value: {
			get: function( elem, name ) {
				if ( nodeHook && jQuery.nodeName( elem, "button" ) ) {
					return nodeHook.get( elem, name );
				}
				return name in elem ?
					elem.value :
					null;
			},
			set: function( elem, value, name ) {
				if ( nodeHook && jQuery.nodeName( elem, "button" ) ) {
					return nodeHook.set( elem, value, name );
				}
				// Does not return so that setAttribute is also used
				elem.value = value;
			}
		}
	},

	propFix: {
		tabindex: "tabIndex",
		readonly: "readOnly",
		"for": "htmlFor",
		"class": "className",
		maxlength: "maxLength",
		cellspacing: "cellSpacing",
		cellpadding: "cellPadding",
		rowspan: "rowSpan",
		colspan: "colSpan",
		usemap: "useMap",
		frameborder: "frameBorder",
		contenteditable: "contentEditable"
	},

	prop: function( elem, name, value ) {
		var ret, hooks, notxml,
			nType = elem.nodeType;

		// don't get/set properties on text, comment and attribute nodes
		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
			return;
		}

		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );

		if ( notxml ) {
			// Fix name and attach hooks
			name = jQuery.propFix[ name ] || name;
			hooks = jQuery.propHooks[ name ];
		}

		if ( value !== undefined ) {
			if ( hooks && "set" in hooks && (ret = hooks.set( elem, value, name )) !== undefined ) {
				return ret;

			} else {
				return ( elem[ name ] = value );
			}

		} else {
			if ( hooks && "get" in hooks && (ret = hooks.get( elem, name )) !== null ) {
				return ret;

			} else {
				return elem[ name ];
			}
		}
	},

	propHooks: {
		tabIndex: {
			get: function( elem ) {
				// elem.tabIndex doesn't always return the correct value when it hasn't been explicitly set
				// http://fluidproject.org/blog/2008/01/09/getting-setting-and-removing-tabindex-values-with-javascript/
				var attributeNode = elem.getAttributeNode("tabindex");

				return attributeNode && attributeNode.specified ?
					parseInt( attributeNode.value, 10 ) :
					rfocusable.test( elem.nodeName ) || rclickable.test( elem.nodeName ) && elem.href ?
						0 :
						undefined;
			}
		}
	}
});

// Hook for boolean attributes
boolHook = {
	get: function( elem, name ) {
		// Align boolean attributes with corresponding properties
		// Fall back to attribute presence where some booleans are not supported
		var attrNode,
			property = jQuery.prop( elem, name );
		return property === true || typeof property !== "boolean" && ( attrNode = elem.getAttributeNode(name) ) && attrNode.nodeValue !== false ?
			name.toLowerCase() :
			undefined;
	},
	set: function( elem, value, name ) {
		var propName;
		if ( value === false ) {
			// Remove boolean attributes when set to false
			jQuery.removeAttr( elem, name );
		} else {
			// value is true since we know at this point it's type boolean and not false
			// Set boolean attributes to the same name and set the DOM property
			propName = jQuery.propFix[ name ] || name;
			if ( propName in elem ) {
				// Only set the IDL specifically if it already exists on the element
				elem[ propName ] = true;
			}

			elem.setAttribute( name, name.toLowerCase() );
		}
		return name;
	}
};

// IE6/7 do not support getting/setting some attributes with get/setAttribute
if ( !getSetAttribute ) {

	fixSpecified = {
		name: true,
		id: true,
		coords: true
	};

	// Use this for any attribute in IE6/7
	// This fixes almost every IE6/7 issue
	nodeHook = jQuery.valHooks.button = {
		get: function( elem, name ) {
			var ret;
			ret = elem.getAttributeNode( name );
			return ret && ( fixSpecified[ name ] ? ret.value !== "" : ret.specified ) ?
				ret.value :
				undefined;
		},
		set: function( elem, value, name ) {
			// Set the existing or create a new attribute node
			var ret = elem.getAttributeNode( name );
			if ( !ret ) {
				ret = document.createAttribute( name );
				elem.setAttributeNode( ret );
			}
			return ( ret.value = value + "" );
		}
	};

	// Set width and height to auto instead of 0 on empty string( Bug #8150 )
	// This is for removals
	jQuery.each([ "width", "height" ], function( i, name ) {
		jQuery.attrHooks[ name ] = jQuery.extend( jQuery.attrHooks[ name ], {
			set: function( elem, value ) {
				if ( value === "" ) {
					elem.setAttribute( name, "auto" );
					return value;
				}
			}
		});
	});

	// Set contenteditable to false on removals(#10429)
	// Setting to empty string throws an error as an invalid value
	jQuery.attrHooks.contenteditable = {
		get: nodeHook.get,
		set: function( elem, value, name ) {
			if ( value === "" ) {
				value = "false";
			}
			nodeHook.set( elem, value, name );
		}
	};
}


// Some attributes require a special call on IE
if ( !jQuery.support.hrefNormalized ) {
	jQuery.each([ "href", "src", "width", "height" ], function( i, name ) {
		jQuery.attrHooks[ name ] = jQuery.extend( jQuery.attrHooks[ name ], {
			get: function( elem ) {
				var ret = elem.getAttribute( name, 2 );
				return ret === null ? undefined : ret;
			}
		});
	});
}

if ( !jQuery.support.style ) {
	jQuery.attrHooks.style = {
		get: function( elem ) {
			// Return undefined in the case of empty string
			// Normalize to lowercase since IE uppercases css property names
			return elem.style.cssText.toLowerCase() || undefined;
		},
		set: function( elem, value ) {
			return ( elem.style.cssText = "" + value );
		}
	};
}

// Safari mis-reports the default selected property of an option
// Accessing the parent's selectedIndex property fixes it
if ( !jQuery.support.optSelected ) {
	jQuery.propHooks.selected = jQuery.extend( jQuery.propHooks.selected, {
		get: function( elem ) {
			var parent = elem.parentNode;

			if ( parent ) {
				parent.selectedIndex;

				// Make sure that it also works with optgroups, see #5701
				if ( parent.parentNode ) {
					parent.parentNode.selectedIndex;
				}
			}
			return null;
		}
	});
}

// IE6/7 call enctype encoding
if ( !jQuery.support.enctype ) {
	jQuery.propFix.enctype = "encoding";
}

// Radios and checkboxes getter/setter
if ( !jQuery.support.checkOn ) {
	jQuery.each([ "radio", "checkbox" ], function() {
		jQuery.valHooks[ this ] = {
			get: function( elem ) {
				// Handle the case where in Webkit "" is returned instead of "on" if a value isn't specified
				return elem.getAttribute("value") === null ? "on" : elem.value;
			}
		};
	});
}
jQuery.each([ "radio", "checkbox" ], function() {
	jQuery.valHooks[ this ] = jQuery.extend( jQuery.valHooks[ this ], {
		set: function( elem, value ) {
			if ( jQuery.isArray( value ) ) {
				return ( elem.checked = jQuery.inArray( jQuery(elem).val(), value ) >= 0 );
			}
		}
	});
});
var rformElems = /^(?:textarea|input|select)$/i,
	rtypenamespace = /^([^\.]*|)(?:\.(.+)|)$/,
	rhoverHack = /(?:^|\s)hover(\.\S+|)\b/,
	rkeyEvent = /^key/,
	rmouseEvent = /^(?:mouse|contextmenu)|click/,
	rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
	hoverHack = function( events ) {
		return jQuery.event.special.hover ? events : events.replace( rhoverHack, "mouseenter$1 mouseleave$1" );
	};

/*
 * Helper functions for managing events -- not part of the public interface.
 * Props to Dean Edwards' addEvent library for many of the ideas.
 */
jQuery.event = {

	add: function( elem, types, handler, data, selector ) {

		var elemData, eventHandle, events,
			t, tns, type, namespaces, handleObj,
			handleObjIn, handlers, special;

		// Don't attach events to noData or text/comment nodes (allow plain objects tho)
		if ( elem.nodeType === 3 || elem.nodeType === 8 || !types || !handler || !(elemData = jQuery._data( elem )) ) {
			return;
		}

		// Caller can pass in an object of custom data in lieu of the handler
		if ( handler.handler ) {
			handleObjIn = handler;
			handler = handleObjIn.handler;
			selector = handleObjIn.selector;
		}

		// Make sure that the handler has a unique ID, used to find/remove it later
		if ( !handler.guid ) {
			handler.guid = jQuery.guid++;
		}

		// Init the element's event structure and main handler, if this is the first
		events = elemData.events;
		if ( !events ) {
			elemData.events = events = {};
		}
		eventHandle = elemData.handle;
		if ( !eventHandle ) {
			elemData.handle = eventHandle = function( e ) {
				// Discard the second event of a jQuery.event.trigger() and
				// when an event is called after a page has unloaded
				return typeof jQuery !== "undefined" && (!e || jQuery.event.triggered !== e.type) ?
					jQuery.event.dispatch.apply( eventHandle.elem, arguments ) :
					undefined;
			};
			// Add elem as a property of the handle fn to prevent a memory leak with IE non-native events
			eventHandle.elem = elem;
		}

		// Handle multiple events separated by a space
		// jQuery(...).bind("mouseover mouseout", fn);
		types = jQuery.trim( hoverHack(types) ).split( " " );
		for ( t = 0; t < types.length; t++ ) {

			tns = rtypenamespace.exec( types[t] ) || [];
			type = tns[1];
			namespaces = ( tns[2] || "" ).split( "." ).sort();

			// If event changes its type, use the special event handlers for the changed type
			special = jQuery.event.special[ type ] || {};

			// If selector defined, determine special event api type, otherwise given type
			type = ( selector ? special.delegateType : special.bindType ) || type;

			// Update special based on newly reset type
			special = jQuery.event.special[ type ] || {};

			// handleObj is passed to all event handlers
			handleObj = jQuery.extend({
				type: type,
				origType: tns[1],
				data: data,
				handler: handler,
				guid: handler.guid,
				selector: selector,
				namespace: namespaces.join(".")
			}, handleObjIn );

			// Init the event handler queue if we're the first
			handlers = events[ type ];
			if ( !handlers ) {
				handlers = events[ type ] = [];
				handlers.delegateCount = 0;

				// Only use addEventListener/attachEvent if the special events handler returns false
				if ( !special.setup || special.setup.call( elem, data, namespaces, eventHandle ) === false ) {
					// Bind the global event handler to the element
					if ( elem.addEventListener ) {
						elem.addEventListener( type, eventHandle, false );

					} else if ( elem.attachEvent ) {
						elem.attachEvent( "on" + type, eventHandle );
					}
				}
			}

			if ( special.add ) {
				special.add.call( elem, handleObj );

				if ( !handleObj.handler.guid ) {
					handleObj.handler.guid = handler.guid;
				}
			}

			// Add to the element's handler list, delegates in front
			if ( selector ) {
				handlers.splice( handlers.delegateCount++, 0, handleObj );
			} else {
				handlers.push( handleObj );
			}

			// Keep track of which events have ever been used, for event optimization
			jQuery.event.global[ type ] = true;
		}

		// Nullify elem to prevent memory leaks in IE
		elem = null;
	},

	global: {},

	// Detach an event or set of events from an element
	remove: function( elem, types, handler, selector, mappedTypes ) {

		var t, tns, type, origType, namespaces, origCount,
			j, events, special, eventType, handleObj,
			elemData = jQuery.hasData( elem ) && jQuery._data( elem );

		if ( !elemData || !(events = elemData.events) ) {
			return;
		}

		// Once for each type.namespace in types; type may be omitted
		types = jQuery.trim( hoverHack( types || "" ) ).split(" ");
		for ( t = 0; t < types.length; t++ ) {
			tns = rtypenamespace.exec( types[t] ) || [];
			type = origType = tns[1];
			namespaces = tns[2];

			// Unbind all events (on this namespace, if provided) for the element
			if ( !type ) {
				for ( type in events ) {
					jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
				}
				continue;
			}

			special = jQuery.event.special[ type ] || {};
			type = ( selector? special.delegateType : special.bindType ) || type;
			eventType = events[ type ] || [];
			origCount = eventType.length;
			namespaces = namespaces ? new RegExp("(^|\\.)" + namespaces.split(".").sort().join("\\.(?:.*\\.|)") + "(\\.|$)") : null;

			// Remove matching events
			for ( j = 0; j < eventType.length; j++ ) {
				handleObj = eventType[ j ];

				if ( ( mappedTypes || origType === handleObj.origType ) &&
					 ( !handler || handler.guid === handleObj.guid ) &&
					 ( !namespaces || namespaces.test( handleObj.namespace ) ) &&
					 ( !selector || selector === handleObj.selector || selector === "**" && handleObj.selector ) ) {
					eventType.splice( j--, 1 );

					if ( handleObj.selector ) {
						eventType.delegateCount--;
					}
					if ( special.remove ) {
						special.remove.call( elem, handleObj );
					}
				}
			}

			// Remove generic event handler if we removed something and no more handlers exist
			// (avoids potential for endless recursion during removal of special event handlers)
			if ( eventType.length === 0 && origCount !== eventType.length ) {
				if ( !special.teardown || special.teardown.call( elem, namespaces, elemData.handle ) === false ) {
					jQuery.removeEvent( elem, type, elemData.handle );
				}

				delete events[ type ];
			}
		}

		// Remove the expando if it's no longer used
		if ( jQuery.isEmptyObject( events ) ) {
			delete elemData.handle;

			// removeData also checks for emptiness and clears the expando if empty
			// so use it instead of delete
			jQuery.removeData( elem, "events", true );
		}
	},

	// Events that are safe to short-circuit if no handlers are attached.
	// Native DOM events should not be added, they may have inline handlers.
	customEvent: {
		"getData": true,
		"setData": true,
		"changeData": true
	},

	trigger: function( event, data, elem, onlyHandlers ) {
		// Don't do events on text and comment nodes
		if ( elem && (elem.nodeType === 3 || elem.nodeType === 8) ) {
			return;
		}

		// Event object or event type
		var cache, exclusive, i, cur, old, ontype, special, handle, eventPath, bubbleType,
			type = event.type || event,
			namespaces = [];

		// focus/blur morphs to focusin/out; ensure we're not firing them right now
		if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
			return;
		}

		if ( type.indexOf( "!" ) >= 0 ) {
			// Exclusive events trigger only for the exact event (no namespaces)
			type = type.slice(0, -1);
			exclusive = true;
		}

		if ( type.indexOf( "." ) >= 0 ) {
			// Namespaced trigger; create a regexp to match event type in handle()
			namespaces = type.split(".");
			type = namespaces.shift();
			namespaces.sort();
		}

		if ( (!elem || jQuery.event.customEvent[ type ]) && !jQuery.event.global[ type ] ) {
			// No jQuery handlers for this event type, and it can't have inline handlers
			return;
		}

		// Caller can pass in an Event, Object, or just an event type string
		event = typeof event === "object" ?
			// jQuery.Event object
			event[ jQuery.expando ] ? event :
			// Object literal
			new jQuery.Event( type, event ) :
			// Just the event type (string)
			new jQuery.Event( type );

		event.type = type;
		event.isTrigger = true;
		event.exclusive = exclusive;
		event.namespace = namespaces.join( "." );
		event.namespace_re = event.namespace? new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") : null;
		ontype = type.indexOf( ":" ) < 0 ? "on" + type : "";

		// Handle a global trigger
		if ( !elem ) {

			// TODO: Stop taunting the data cache; remove global events and always attach to document
			cache = jQuery.cache;
			for ( i in cache ) {
				if ( cache[ i ].events && cache[ i ].events[ type ] ) {
					jQuery.event.trigger( event, data, cache[ i ].handle.elem, true );
				}
			}
			return;
		}

		// Clean up the event in case it is being reused
		event.result = undefined;
		if ( !event.target ) {
			event.target = elem;
		}

		// Clone any incoming data and prepend the event, creating the handler arg list
		data = data != null ? jQuery.makeArray( data ) : [];
		data.unshift( event );

		// Allow special events to draw outside the lines
		special = jQuery.event.special[ type ] || {};
		if ( special.trigger && special.trigger.apply( elem, data ) === false ) {
			return;
		}

		// Determine event propagation path in advance, per W3C events spec (#9951)
		// Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
		eventPath = [[ elem, special.bindType || type ]];
		if ( !onlyHandlers && !special.noBubble && !jQuery.isWindow( elem ) ) {

			bubbleType = special.delegateType || type;
			cur = rfocusMorph.test( bubbleType + type ) ? elem : elem.parentNode;
			for ( old = elem; cur; cur = cur.parentNode ) {
				eventPath.push([ cur, bubbleType ]);
				old = cur;
			}

			// Only add window if we got to document (e.g., not plain obj or detached DOM)
			if ( old === (elem.ownerDocument || document) ) {
				eventPath.push([ old.defaultView || old.parentWindow || window, bubbleType ]);
			}
		}

		// Fire handlers on the event path
		for ( i = 0; i < eventPath.length && !event.isPropagationStopped(); i++ ) {

			cur = eventPath[i][0];
			event.type = eventPath[i][1];

			handle = ( jQuery._data( cur, "events" ) || {} )[ event.type ] && jQuery._data( cur, "handle" );
			if ( handle ) {
				handle.apply( cur, data );
			}
			// Note that this is a bare JS function and not a jQuery handler
			handle = ontype && cur[ ontype ];
			if ( handle && jQuery.acceptData( cur ) && handle.apply( cur, data ) === false ) {
				event.preventDefault();
			}
		}
		event.type = type;

		// If nobody prevented the default action, do it now
		if ( !onlyHandlers && !event.isDefaultPrevented() ) {

			if ( (!special._default || special._default.apply( elem.ownerDocument, data ) === false) &&
				!(type === "click" && jQuery.nodeName( elem, "a" )) && jQuery.acceptData( elem ) ) {

				// Call a native DOM method on the target with the same name name as the event.
				// Can't use an .isFunction() check here because IE6/7 fails that test.
				// Don't do default actions on window, that's where global variables be (#6170)
				// IE<9 dies on focus/blur to hidden element (#1486)
				if ( ontype && elem[ type ] && ((type !== "focus" && type !== "blur") || event.target.offsetWidth !== 0) && !jQuery.isWindow( elem ) ) {

					// Don't re-trigger an onFOO event when we call its FOO() method
					old = elem[ ontype ];

					if ( old ) {
						elem[ ontype ] = null;
					}

					// Prevent re-triggering of the same event, since we already bubbled it above
					jQuery.event.triggered = type;
					elem[ type ]();
					jQuery.event.triggered = undefined;

					if ( old ) {
						elem[ ontype ] = old;
					}
				}
			}
		}

		return event.result;
	},

	dispatch: function( event ) {

		// Make a writable jQuery.Event from the native event object
		event = jQuery.event.fix( event || window.event );

		var i, j, cur, ret, selMatch, matched, matches, handleObj, sel, related,
			handlers = ( (jQuery._data( this, "events" ) || {} )[ event.type ] || []),
			delegateCount = handlers.delegateCount,
			args = [].slice.call( arguments ),
			run_all = !event.exclusive && !event.namespace,
			special = jQuery.event.special[ event.type ] || {},
			handlerQueue = [];

		// Use the fix-ed jQuery.Event rather than the (read-only) native event
		args[0] = event;
		event.delegateTarget = this;

		// Call the preDispatch hook for the mapped type, and let it bail if desired
		if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
			return;
		}

		// Determine handlers that should run if there are delegated events
		// Avoid non-left-click bubbling in Firefox (#3861)
		if ( delegateCount && !(event.button && event.type === "click") ) {

			for ( cur = event.target; cur != this; cur = cur.parentNode || this ) {

				// Don't process clicks (ONLY) on disabled elements (#6911, #8165, #11382, #11764)
				if ( cur.disabled !== true || event.type !== "click" ) {
					selMatch = {};
					matches = [];
					for ( i = 0; i < delegateCount; i++ ) {
						handleObj = handlers[ i ];
						sel = handleObj.selector;

						if ( selMatch[ sel ] === undefined ) {
							selMatch[ sel ] = jQuery( sel, this ).index( cur ) >= 0;
						}
						if ( selMatch[ sel ] ) {
							matches.push( handleObj );
						}
					}
					if ( matches.length ) {
						handlerQueue.push({ elem: cur, matches: matches });
					}
				}
			}
		}

		// Add the remaining (directly-bound) handlers
		if ( handlers.length > delegateCount ) {
			handlerQueue.push({ elem: this, matches: handlers.slice( delegateCount ) });
		}

		// Run delegates first; they may want to stop propagation beneath us
		for ( i = 0; i < handlerQueue.length && !event.isPropagationStopped(); i++ ) {
			matched = handlerQueue[ i ];
			event.currentTarget = matched.elem;

			for ( j = 0; j < matched.matches.length && !event.isImmediatePropagationStopped(); j++ ) {
				handleObj = matched.matches[ j ];

				// Triggered event must either 1) be non-exclusive and have no namespace, or
				// 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
				if ( run_all || (!event.namespace && !handleObj.namespace) || event.namespace_re && event.namespace_re.test( handleObj.namespace ) ) {

					event.data = handleObj.data;
					event.handleObj = handleObj;

					ret = ( (jQuery.event.special[ handleObj.origType ] || {}).handle || handleObj.handler )
							.apply( matched.elem, args );

					if ( ret !== undefined ) {
						event.result = ret;
						if ( ret === false ) {
							event.preventDefault();
							event.stopPropagation();
						}
					}
				}
			}
		}

		// Call the postDispatch hook for the mapped type
		if ( special.postDispatch ) {
			special.postDispatch.call( this, event );
		}

		return event.result;
	},

	// Includes some event props shared by KeyEvent and MouseEvent
	// *** attrChange attrName relatedNode srcElement  are not normalized, non-W3C, deprecated, will be removed in 1.8 ***
	props: "attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),

	fixHooks: {},

	keyHooks: {
		props: "char charCode key keyCode".split(" "),
		filter: function( event, original ) {

			// Add which for key events
			if ( event.which == null ) {
				event.which = original.charCode != null ? original.charCode : original.keyCode;
			}

			return event;
		}
	},

	mouseHooks: {
		props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
		filter: function( event, original ) {
			var eventDoc, doc, body,
				button = original.button,
				fromElement = original.fromElement;

			// Calculate pageX/Y if missing and clientX/Y available
			if ( event.pageX == null && original.clientX != null ) {
				eventDoc = event.target.ownerDocument || document;
				doc = eventDoc.documentElement;
				body = eventDoc.body;

				event.pageX = original.clientX + ( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) - ( doc && doc.clientLeft || body && body.clientLeft || 0 );
				event.pageY = original.clientY + ( doc && doc.scrollTop  || body && body.scrollTop  || 0 ) - ( doc && doc.clientTop  || body && body.clientTop  || 0 );
			}

			// Add relatedTarget, if necessary
			if ( !event.relatedTarget && fromElement ) {
				event.relatedTarget = fromElement === event.target ? original.toElement : fromElement;
			}

			// Add which for click: 1 === left; 2 === middle; 3 === right
			// Note: button is not normalized, so don't use it
			if ( !event.which && button !== undefined ) {
				event.which = ( button & 1 ? 1 : ( button & 2 ? 3 : ( button & 4 ? 2 : 0 ) ) );
			}

			return event;
		}
	},

	fix: function( event ) {
		if ( event[ jQuery.expando ] ) {
			return event;
		}

		// Create a writable copy of the event object and normalize some properties
		var i, prop,
			originalEvent = event,
			fixHook = jQuery.event.fixHooks[ event.type ] || {},
			copy = fixHook.props ? this.props.concat( fixHook.props ) : this.props;

		event = jQuery.Event( originalEvent );

		for ( i = copy.length; i; ) {
			prop = copy[ --i ];
			event[ prop ] = originalEvent[ prop ];
		}

		// Fix target property, if necessary (#1925, IE 6/7/8 & Safari2)
		if ( !event.target ) {
			event.target = originalEvent.srcElement || document;
		}

		// Target should not be a text node (#504, Safari)
		if ( event.target.nodeType === 3 ) {
			event.target = event.target.parentNode;
		}

		// For mouse/key events, metaKey==false if it's undefined (#3368, #11328; IE6/7/8)
		event.metaKey = !!event.metaKey;

		return fixHook.filter? fixHook.filter( event, originalEvent ) : event;
	},

	special: {
		load: {
			// Prevent triggered image.load events from bubbling to window.load
			noBubble: true
		},

		focus: {
			delegateType: "focusin"
		},
		blur: {
			delegateType: "focusout"
		},

		beforeunload: {
			setup: function( data, namespaces, eventHandle ) {
				// We only want to do this special case on windows
				if ( jQuery.isWindow( this ) ) {
					this.onbeforeunload = eventHandle;
				}
			},

			teardown: function( namespaces, eventHandle ) {
				if ( this.onbeforeunload === eventHandle ) {
					this.onbeforeunload = null;
				}
			}
		}
	},

	simulate: function( type, elem, event, bubble ) {
		// Piggyback on a donor event to simulate a different one.
		// Fake originalEvent to avoid donor's stopPropagation, but if the
		// simulated event prevents default then we do the same on the donor.
		var e = jQuery.extend(
			new jQuery.Event(),
			event,
			{ type: type,
				isSimulated: true,
				originalEvent: {}
			}
		);
		if ( bubble ) {
			jQuery.event.trigger( e, null, elem );
		} else {
			jQuery.event.dispatch.call( elem, e );
		}
		if ( e.isDefaultPrevented() ) {
			event.preventDefault();
		}
	}
};

// Some plugins are using, but it's undocumented/deprecated and will be removed.
// The 1.7 special event interface should provide all the hooks needed now.
jQuery.event.handle = jQuery.event.dispatch;

jQuery.removeEvent = document.removeEventListener ?
	function( elem, type, handle ) {
		if ( elem.removeEventListener ) {
			elem.removeEventListener( type, handle, false );
		}
	} :
	function( elem, type, handle ) {
		var name = "on" + type;

		if ( elem.detachEvent ) {

			// #8545, #7054, preventing memory leaks for custom events in IE6-8 –
			// detachEvent needed property on element, by name of that event, to properly expose it to GC
			if ( typeof elem[ name ] === "undefined" ) {
				elem[ name ] = null;
			}

			elem.detachEvent( name, handle );
		}
	};

jQuery.Event = function( src, props ) {
	// Allow instantiation without the 'new' keyword
	if ( !(this instanceof jQuery.Event) ) {
		return new jQuery.Event( src, props );
	}

	// Event object
	if ( src && src.type ) {
		this.originalEvent = src;
		this.type = src.type;

		// Events bubbling up the document may have been marked as prevented
		// by a handler lower down the tree; reflect the correct value.
		this.isDefaultPrevented = ( src.defaultPrevented || src.returnValue === false ||
			src.getPreventDefault && src.getPreventDefault() ) ? returnTrue : returnFalse;

	// Event type
	} else {
		this.type = src;
	}

	// Put explicitly provided properties onto the event object
	if ( props ) {
		jQuery.extend( this, props );
	}

	// Create a timestamp if incoming event doesn't have one
	this.timeStamp = src && src.timeStamp || jQuery.now();

	// Mark it as fixed
	this[ jQuery.expando ] = true;
};

function returnFalse() {
	return false;
}
function returnTrue() {
	return true;
}

// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
// http://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
jQuery.Event.prototype = {
	preventDefault: function() {
		this.isDefaultPrevented = returnTrue;

		var e = this.originalEvent;
		if ( !e ) {
			return;
		}

		// if preventDefault exists run it on the original event
		if ( e.preventDefault ) {
			e.preventDefault();

		// otherwise set the returnValue property of the original event to false (IE)
		} else {
			e.returnValue = false;
		}
	},
	stopPropagation: function() {
		this.isPropagationStopped = returnTrue;

		var e = this.originalEvent;
		if ( !e ) {
			return;
		}
		// if stopPropagation exists run it on the original event
		if ( e.stopPropagation ) {
			e.stopPropagation();
		}
		// otherwise set the cancelBubble property of the original event to true (IE)
		e.cancelBubble = true;
	},
	stopImmediatePropagation: function() {
		this.isImmediatePropagationStopped = returnTrue;
		this.stopPropagation();
	},
	isDefaultPrevented: returnFalse,
	isPropagationStopped: returnFalse,
	isImmediatePropagationStopped: returnFalse
};

// Create mouseenter/leave events using mouseover/out and event-time checks
jQuery.each({
	mouseenter: "mouseover",
	mouseleave: "mouseout"
}, function( orig, fix ) {
	jQuery.event.special[ orig ] = {
		delegateType: fix,
		bindType: fix,

		handle: function( event ) {
			var ret,
				target = this,
				related = event.relatedTarget,
				handleObj = event.handleObj,
				selector = handleObj.selector;

			// For mousenter/leave call the handler if related is outside the target.
			// NB: No relatedTarget if the mouse left/entered the browser window
			if ( !related || (related !== target && !jQuery.contains( target, related )) ) {
				event.type = handleObj.origType;
				ret = handleObj.handler.apply( this, arguments );
				event.type = fix;
			}
			return ret;
		}
	};
});

// IE submit delegation
if ( !jQuery.support.submitBubbles ) {

	jQuery.event.special.submit = {
		setup: function() {
			// Only need this for delegated form submit events
			if ( jQuery.nodeName( this, "form" ) ) {
				return false;
			}

			// Lazy-add a submit handler when a descendant form may potentially be submitted
			jQuery.event.add( this, "click._submit keypress._submit", function( e ) {
				// Node name check avoids a VML-related crash in IE (#9807)
				var elem = e.target,
					form = jQuery.nodeName( elem, "input" ) || jQuery.nodeName( elem, "button" ) ? elem.form : undefined;
				if ( form && !jQuery._data( form, "_submit_attached" ) ) {
					jQuery.event.add( form, "submit._submit", function( event ) {
						event._submit_bubble = true;
					});
					jQuery._data( form, "_submit_attached", true );
				}
			});
			// return undefined since we don't need an event listener
		},

		postDispatch: function( event ) {
			// If form was submitted by the user, bubble the event up the tree
			if ( event._submit_bubble ) {
				delete event._submit_bubble;
				if ( this.parentNode && !event.isTrigger ) {
					jQuery.event.simulate( "submit", this.parentNode, event, true );
				}
			}
		},

		teardown: function() {
			// Only need this for delegated form submit events
			if ( jQuery.nodeName( this, "form" ) ) {
				return false;
			}

			// Remove delegated handlers; cleanData eventually reaps submit handlers attached above
			jQuery.event.remove( this, "._submit" );
		}
	};
}

// IE change delegation and checkbox/radio fix
if ( !jQuery.support.changeBubbles ) {

	jQuery.event.special.change = {

		setup: function() {

			if ( rformElems.test( this.nodeName ) ) {
				// IE doesn't fire change on a check/radio until blur; trigger it on click
				// after a propertychange. Eat the blur-change in special.change.handle.
				// This still fires onchange a second time for check/radio after blur.
				if ( this.type === "checkbox" || this.type === "radio" ) {
					jQuery.event.add( this, "propertychange._change", function( event ) {
						if ( event.originalEvent.propertyName === "checked" ) {
							this._just_changed = true;
						}
					});
					jQuery.event.add( this, "click._change", function( event ) {
						if ( this._just_changed && !event.isTrigger ) {
							this._just_changed = false;
						}
						// Allow triggered, simulated change events (#11500)
						jQuery.event.simulate( "change", this, event, true );
					});
				}
				return false;
			}
			// Delegated event; lazy-add a change handler on descendant inputs
			jQuery.event.add( this, "beforeactivate._change", function( e ) {
				var elem = e.target;

				if ( rformElems.test( elem.nodeName ) && !jQuery._data( elem, "_change_attached" ) ) {
					jQuery.event.add( elem, "change._change", function( event ) {
						if ( this.parentNode && !event.isSimulated && !event.isTrigger ) {
							jQuery.event.simulate( "change", this.parentNode, event, true );
						}
					});
					jQuery._data( elem, "_change_attached", true );
				}
			});
		},

		handle: function( event ) {
			var elem = event.target;

			// Swallow native change events from checkbox/radio, we already triggered them above
			if ( this !== elem || event.isSimulated || event.isTrigger || (elem.type !== "radio" && elem.type !== "checkbox") ) {
				return event.handleObj.handler.apply( this, arguments );
			}
		},

		teardown: function() {
			jQuery.event.remove( this, "._change" );

			return !rformElems.test( this.nodeName );
		}
	};
}

// Create "bubbling" focus and blur events
if ( !jQuery.support.focusinBubbles ) {
	jQuery.each({ focus: "focusin", blur: "focusout" }, function( orig, fix ) {

		// Attach a single capturing handler while someone wants focusin/focusout
		var attaches = 0,
			handler = function( event ) {
				jQuery.event.simulate( fix, event.target, jQuery.event.fix( event ), true );
			};

		jQuery.event.special[ fix ] = {
			setup: function() {
				if ( attaches++ === 0 ) {
					document.addEventListener( orig, handler, true );
				}
			},
			teardown: function() {
				if ( --attaches === 0 ) {
					document.removeEventListener( orig, handler, true );
				}
			}
		};
	});
}

jQuery.fn.extend({

	on: function( types, selector, data, fn, /*INTERNAL*/ one ) {
		var origFn, type;

		// Types can be a map of types/handlers
		if ( typeof types === "object" ) {
			// ( types-Object, selector, data )
			if ( typeof selector !== "string" ) { // && selector != null
				// ( types-Object, data )
				data = data || selector;
				selector = undefined;
			}
			for ( type in types ) {
				this.on( type, selector, data, types[ type ], one );
			}
			return this;
		}

		if ( data == null && fn == null ) {
			// ( types, fn )
			fn = selector;
			data = selector = undefined;
		} else if ( fn == null ) {
			if ( typeof selector === "string" ) {
				// ( types, selector, fn )
				fn = data;
				data = undefined;
			} else {
				// ( types, data, fn )
				fn = data;
				data = selector;
				selector = undefined;
			}
		}
		if ( fn === false ) {
			fn = returnFalse;
		} else if ( !fn ) {
			return this;
		}

		if ( one === 1 ) {
			origFn = fn;
			fn = function( event ) {
				// Can use an empty set, since event contains the info
				jQuery().off( event );
				return origFn.apply( this, arguments );
			};
			// Use same guid so caller can remove using origFn
			fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
		}
		return this.each( function() {
			jQuery.event.add( this, types, fn, data, selector );
		});
	},
	one: function( types, selector, data, fn ) {
		return this.on( types, selector, data, fn, 1 );
	},
	off: function( types, selector, fn ) {
		var handleObj, type;
		if ( types && types.preventDefault && types.handleObj ) {
			// ( event )  dispatched jQuery.Event
			handleObj = types.handleObj;
			jQuery( types.delegateTarget ).off(
				handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType,
				handleObj.selector,
				handleObj.handler
			);
			return this;
		}
		if ( typeof types === "object" ) {
			// ( types-object [, selector] )
			for ( type in types ) {
				this.off( type, selector, types[ type ] );
			}
			return this;
		}
		if ( selector === false || typeof selector === "function" ) {
			// ( types [, fn] )
			fn = selector;
			selector = undefined;
		}
		if ( fn === false ) {
			fn = returnFalse;
		}
		return this.each(function() {
			jQuery.event.remove( this, types, fn, selector );
		});
	},

	bind: function( types, data, fn ) {
		return this.on( types, null, data, fn );
	},
	unbind: function( types, fn ) {
		return this.off( types, null, fn );
	},

	live: function( types, data, fn ) {
		jQuery( this.context ).on( types, this.selector, data, fn );
		return this;
	},
	die: function( types, fn ) {
		jQuery( this.context ).off( types, this.selector || "**", fn );
		return this;
	},

	delegate: function( selector, types, data, fn ) {
		return this.on( types, selector, data, fn );
	},
	undelegate: function( selector, types, fn ) {
		// ( namespace ) or ( selector, types [, fn] )
		return arguments.length == 1? this.off( selector, "**" ) : this.off( types, selector || "**", fn );
	},

	trigger: function( type, data ) {
		return this.each(function() {
			jQuery.event.trigger( type, data, this );
		});
	},
	triggerHandler: function( type, data ) {
		if ( this[0] ) {
			return jQuery.event.trigger( type, data, this[0], true );
		}
	},

	toggle: function( fn ) {
		// Save reference to arguments for access in closure
		var args = arguments,
			guid = fn.guid || jQuery.guid++,
			i = 0,
			toggler = function( event ) {
				// Figure out which function to execute
				var lastToggle = ( jQuery._data( this, "lastToggle" + fn.guid ) || 0 ) % i;
				jQuery._data( this, "lastToggle" + fn.guid, lastToggle + 1 );

				// Make sure that clicks stop
				event.preventDefault();

				// and execute the function
				return args[ lastToggle ].apply( this, arguments ) || false;
			};

		// link all the functions, so any of them can unbind this click handler
		toggler.guid = guid;
		while ( i < args.length ) {
			args[ i++ ].guid = guid;
		}

		return this.click( toggler );
	},

	hover: function( fnOver, fnOut ) {
		return this.mouseenter( fnOver ).mouseleave( fnOut || fnOver );
	}
});

jQuery.each( ("blur focus focusin focusout load resize scroll unload click dblclick " +
	"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
	"change select submit keydown keypress keyup error contextmenu").split(" "), function( i, name ) {

	// Handle event binding
	jQuery.fn[ name ] = function( data, fn ) {
		if ( fn == null ) {
			fn = data;
			data = null;
		}

		return arguments.length > 0 ?
			this.on( name, null, data, fn ) :
			this.trigger( name );
	};

	if ( rkeyEvent.test( name ) ) {
		jQuery.event.fixHooks[ name ] = jQuery.event.keyHooks;
	}

	if ( rmouseEvent.test( name ) ) {
		jQuery.event.fixHooks[ name ] = jQuery.event.mouseHooks;
	}
});
/*!
 * Sizzle CSS Selector Engine
 *  Copyright 2012 jQuery Foundation and other contributors
 *  Released under the MIT license
 *  http://sizzlejs.com/
 */
(function( window, undefined ) {

var dirruns,
	cachedruns,
	assertGetIdNotName,
	Expr,
	getText,
	isXML,
	contains,
	compile,
	sortOrder,
	hasDuplicate,

	baseHasDuplicate = true,
	strundefined = "undefined",

	expando = ( "sizcache" + Math.random() ).replace( ".", "" ),

	document = window.document,
	docElem = document.documentElement,
	done = 0,
	slice = [].slice,
	push = [].push,

	// Augment a function for special use by Sizzle
	markFunction = function( fn, value ) {
		fn[ expando ] = value || true;
		return fn;
	},

	createCache = function() {
		var cache = {},
			keys = [];

		return markFunction(function( key, value ) {
			// Only keep the most recent entries
			if ( keys.push( key ) > Expr.cacheLength ) {
				delete cache[ keys.shift() ];
			}

			return (cache[ key ] = value);
		}, cache );
	},

	classCache = createCache(),
	tokenCache = createCache(),
	compilerCache = createCache(),

	// Regex

	// Whitespace characters http://www.w3.org/TR/css3-selectors/#whitespace
	whitespace = "[\\x20\\t\\r\\n\\f]",
	// http://www.w3.org/TR/css3-syntax/#characters
	characterEncoding = "(?:\\\\.|[-\\w]|[^\\x00-\\xa0])+",

	// Loosely modeled on CSS identifier characters
	// An unquoted value should be a CSS identifier (http://www.w3.org/TR/css3-selectors/#attribute-selectors)
	// Proper syntax: http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
	identifier = characterEncoding.replace( "w", "w#" ),

	// Acceptable operators http://www.w3.org/TR/selectors/#attribute-selectors
	operators = "([*^$|!~]?=)",
	attributes = "\\[" + whitespace + "*(" + characterEncoding + ")" + whitespace +
		"*(?:" + operators + whitespace + "*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|(" + identifier + ")|)|)" + whitespace + "*\\]",

	// Prefer arguments not in parens/brackets,
	//   then attribute selectors and non-pseudos (denoted by :),
	//   then anything else
	// These preferences are here to reduce the number of selectors
	//   needing tokenize in the PSEUDO preFilter
	pseudos = ":(" + characterEncoding + ")(?:\\((?:(['\"])((?:\\\\.|[^\\\\])*?)\\2|([^()[\\]]*|(?:(?:" + attributes + ")|[^:]|\\\\.)*|.*))\\)|)",

	// For matchExpr.POS and matchExpr.needsContext
	pos = ":(nth|eq|gt|lt|first|last|even|odd)(?:\\(((?:-\\d)?\\d*)\\)|)(?=[^-]|$)",

	// Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
	rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" ),

	rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" ),
	rcombinators = new RegExp( "^" + whitespace + "*([\\x20\\t\\r\\n\\f>+~])" + whitespace + "*" ),
	rpseudo = new RegExp( pseudos ),

	// Easily-parseable/retrievable ID or TAG or CLASS selectors
	rquickExpr = /^(?:#([\w\-]+)|(\w+)|\.([\w\-]+))$/,

	rnot = /^:not/,
	rsibling = /[\x20\t\r\n\f]*[+~]/,
	rendsWithNot = /:not\($/,

	rheader = /h\d/i,
	rinputs = /input|select|textarea|button/i,

	rbackslash = /\\(?!\\)/g,

	matchExpr = {
		"ID": new RegExp( "^#(" + characterEncoding + ")" ),
		"CLASS": new RegExp( "^\\.(" + characterEncoding + ")" ),
		"NAME": new RegExp( "^\\[name=['\"]?(" + characterEncoding + ")['\"]?\\]" ),
		"TAG": new RegExp( "^(" + characterEncoding.replace( "w", "w*" ) + ")" ),
		"ATTR": new RegExp( "^" + attributes ),
		"PSEUDO": new RegExp( "^" + pseudos ),
		"CHILD": new RegExp( "^:(only|nth|last|first)-child(?:\\(" + whitespace +
			"*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace +
			"*(\\d+)|))" + whitespace + "*\\)|)", "i" ),
		"POS": new RegExp( pos, "ig" ),
		// For use in libraries implementing .is()
		"needsContext": new RegExp( "^" + whitespace + "*[>+~]|" + pos, "i" )
	},

	// Support

	// Used for testing something on an element
	assert = function( fn ) {
		var div = document.createElement("div");

		try {
			return fn( div );
		} catch (e) {
			return false;
		} finally {
			// release memory in IE
			div = null;
		}
	},

	// Check if getElementsByTagName("*") returns only elements
	assertTagNameNoComments = assert(function( div ) {
		div.appendChild( document.createComment("") );
		return !div.getElementsByTagName("*").length;
	}),

	// Check if getAttribute returns normalized href attributes
	assertHrefNotNormalized = assert(function( div ) {
		div.innerHTML = "<a href='#'></a>";
		return div.firstChild && typeof div.firstChild.getAttribute !== strundefined &&
			div.firstChild.getAttribute("href") === "#";
	}),

	// Check if attributes should be retrieved by attribute nodes
	assertAttributes = assert(function( div ) {
		div.innerHTML = "<select></select>";
		var type = typeof div.lastChild.getAttribute("multiple");
		// IE8 returns a string for some attributes even when not present
		return type !== "boolean" && type !== "string";
	}),

	// Check if getElementsByClassName can be trusted
	assertUsableClassName = assert(function( div ) {
		// Opera can't find a second classname (in 9.6)
		div.innerHTML = "<div class='hidden e'></div><div class='hidden'></div>";
		if ( !div.getElementsByClassName || !div.getElementsByClassName("e").length ) {
			return false;
		}

		// Safari 3.2 caches class attributes and doesn't catch changes
		div.lastChild.className = "e";
		return div.getElementsByClassName("e").length === 2;
	}),

	// Check if getElementById returns elements by name
	// Check if getElementsByName privileges form controls or returns elements by ID
	assertUsableName = assert(function( div ) {
		// Inject content
		div.id = expando + 0;
		div.innerHTML = "<a name='" + expando + "'></a><div name='" + expando + "'></div>";
		docElem.insertBefore( div, docElem.firstChild );

		// Test
		var pass = document.getElementsByName &&
			// buggy browsers will return fewer than the correct 2
			document.getElementsByName( expando ).length === 2 +
			// buggy browsers will return more than the correct 0
			document.getElementsByName( expando + 0 ).length;
		assertGetIdNotName = !document.getElementById( expando );

		// Cleanup
		docElem.removeChild( div );

		return pass;
	});

// If slice is not available, provide a backup
try {
	slice.call( docElem.childNodes, 0 )[0].nodeType;
} catch ( e ) {
	slice = function( i ) {
		var elem, results = [];
		for ( ; (elem = this[i]); i++ ) {
			results.push( elem );
		}
		return results;
	};
}

function Sizzle( selector, context, results, seed ) {
	results = results || [];
	context = context || document;
	var match, elem, xml, m,
		nodeType = context.nodeType;

	if ( nodeType !== 1 && nodeType !== 9 ) {
		return [];
	}

	if ( !selector || typeof selector !== "string" ) {
		return results;
	}

	xml = isXML( context );

	if ( !xml && !seed ) {
		if ( (match = rquickExpr.exec( selector )) ) {
			// Speed-up: Sizzle("#ID")
			if ( (m = match[1]) ) {
				if ( nodeType === 9 ) {
					elem = context.getElementById( m );
					// Check parentNode to catch when Blackberry 4.6 returns
					// nodes that are no longer in the document #6963
					if ( elem && elem.parentNode ) {
						// Handle the case where IE, Opera, and Webkit return items
						// by name instead of ID
						if ( elem.id === m ) {
							results.push( elem );
							return results;
						}
					} else {
						return results;
					}
				} else {
					// Context is not a document
					if ( context.ownerDocument && (elem = context.ownerDocument.getElementById( m )) &&
						contains( context, elem ) && elem.id === m ) {
						results.push( elem );
						return results;
					}
				}

			// Speed-up: Sizzle("TAG")
			} else if ( match[2] ) {
				push.apply( results, slice.call(context.getElementsByTagName( selector ), 0) );
				return results;

			// Speed-up: Sizzle(".CLASS")
			} else if ( (m = match[3]) && assertUsableClassName && context.getElementsByClassName ) {
				push.apply( results, slice.call(context.getElementsByClassName( m ), 0) );
				return results;
			}
		}
	}

	// All others
	return select( selector, context, results, seed, xml );
}

Sizzle.matches = function( expr, elements ) {
	return Sizzle( expr, null, null, elements );
};

Sizzle.matchesSelector = function( elem, expr ) {
	return Sizzle( expr, null, null, [ elem ] ).length > 0;
};

// Returns a function to use in pseudos for input types
function createInputPseudo( type ) {
	return function( elem ) {
		var name = elem.nodeName.toLowerCase();
		return name === "input" && elem.type === type;
	};
}

// Returns a function to use in pseudos for buttons
function createButtonPseudo( type ) {
	return function( elem ) {
		var name = elem.nodeName.toLowerCase();
		return (name === "input" || name === "button") && elem.type === type;
	};
}

/**
 * Utility function for retrieving the text value of an array of DOM nodes
 * @param {Array|Element} elem
 */
getText = Sizzle.getText = function( elem ) {
	var node,
		ret = "",
		i = 0,
		nodeType = elem.nodeType;

	if ( nodeType ) {
		if ( nodeType === 1 || nodeType === 9 || nodeType === 11 ) {
			// Use textContent for elements
			// innerText usage removed for consistency of new lines (see #11153)
			if ( typeof elem.textContent === "string" ) {
				return elem.textContent;
			} else {
				// Traverse its children
				for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
					ret += getText( elem );
				}
			}
		} else if ( nodeType === 3 || nodeType === 4 ) {
			return elem.nodeValue;
		}
		// Do not include comment or processing instruction nodes
	} else {

		// If no nodeType, this is expected to be an array
		for ( ; (node = elem[i]); i++ ) {
			// Do not traverse comment nodes
			ret += getText( node );
		}
	}
	return ret;
};

isXML = Sizzle.isXML = function isXML( elem ) {
	// documentElement is verified for cases where it doesn't yet exist
	// (such as loading iframes in IE - #4833)
	var documentElement = elem && (elem.ownerDocument || elem).documentElement;
	return documentElement ? documentElement.nodeName !== "HTML" : false;
};

// Element contains another
contains = Sizzle.contains = docElem.contains ?
	function( a, b ) {
		var adown = a.nodeType === 9 ? a.documentElement : a,
			bup = b && b.parentNode;
		return a === bup || !!( bup && bup.nodeType === 1 && adown.contains && adown.contains(bup) );
	} :
	docElem.compareDocumentPosition ?
	function( a, b ) {
		return b && !!( a.compareDocumentPosition( b ) & 16 );
	} :
	function( a, b ) {
		while ( (b = b.parentNode) ) {
			if ( b === a ) {
				return true;
			}
		}
		return false;
	};

Sizzle.attr = function( elem, name ) {
	var attr,
		xml = isXML( elem );

	if ( !xml ) {
		name = name.toLowerCase();
	}
	if ( Expr.attrHandle[ name ] ) {
		return Expr.attrHandle[ name ]( elem );
	}
	if ( assertAttributes || xml ) {
		return elem.getAttribute( name );
	}
	attr = elem.getAttributeNode( name );
	return attr ?
		typeof elem[ name ] === "boolean" ?
			elem[ name ] ? name : null :
			attr.specified ? attr.value : null :
		null;
};

Expr = Sizzle.selectors = {

	// Can be adjusted by the user
	cacheLength: 50,

	createPseudo: markFunction,

	match: matchExpr,

	order: new RegExp( "ID|TAG" +
		(assertUsableName ? "|NAME" : "") +
		(assertUsableClassName ? "|CLASS" : "")
	),

	// IE6/7 return a modified href
	attrHandle: assertHrefNotNormalized ?
		{} :
		{
			"href": function( elem ) {
				return elem.getAttribute( "href", 2 );
			},
			"type": function( elem ) {
				return elem.getAttribute("type");
			}
		},

	find: {
		"ID": assertGetIdNotName ?
			function( id, context, xml ) {
				if ( typeof context.getElementById !== strundefined && !xml ) {
					var m = context.getElementById( id );
					// Check parentNode to catch when Blackberry 4.6 returns
					// nodes that are no longer in the document #6963
					return m && m.parentNode ? [m] : [];
				}
			} :
			function( id, context, xml ) {
				if ( typeof context.getElementById !== strundefined && !xml ) {
					var m = context.getElementById( id );

					return m ?
						m.id === id || typeof m.getAttributeNode !== strundefined && m.getAttributeNode("id").value === id ?
							[m] :
							undefined :
						[];
				}
			},

		"TAG": assertTagNameNoComments ?
			function( tag, context ) {
				if ( typeof context.getElementsByTagName !== strundefined ) {
					return context.getElementsByTagName( tag );
				}
			} :
			function( tag, context ) {
				var results = context.getElementsByTagName( tag );

				// Filter out possible comments
				if ( tag === "*" ) {
					var elem,
						tmp = [],
						i = 0;

					for ( ; (elem = results[i]); i++ ) {
						if ( elem.nodeType === 1 ) {
							tmp.push( elem );
						}
					}

					return tmp;
				}
				return results;
			},

		"NAME": function( tag, context ) {
			if ( typeof context.getElementsByName !== strundefined ) {
				return context.getElementsByName( name );
			}
		},

		"CLASS": function( className, context, xml ) {
			if ( typeof context.getElementsByClassName !== strundefined && !xml ) {
				return context.getElementsByClassName( className );
			}
		}
	},

	relative: {
		">": { dir: "parentNode", first: true },
		" ": { dir: "parentNode" },
		"+": { dir: "previousSibling", first: true },
		"~": { dir: "previousSibling" }
	},

	preFilter: {
		"ATTR": function( match ) {
			match[1] = match[1].replace( rbackslash, "" );

			// Move the given value to match[3] whether quoted or unquoted
			match[3] = ( match[4] || match[5] || "" ).replace( rbackslash, "" );

			if ( match[2] === "~=" ) {
				match[3] = " " + match[3] + " ";
			}

			return match.slice( 0, 4 );
		},

		"CHILD": function( match ) {
			/* matches from matchExpr.CHILD
				1 type (only|nth|...)
				2 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
				3 xn-component of xn+y argument ([+-]?\d*n|)
				4 sign of xn-component
				5 x of xn-component
				6 sign of y-component
				7 y of y-component
			*/
			match[1] = match[1].toLowerCase();

			if ( match[1] === "nth" ) {
				// nth-child requires argument
				if ( !match[2] ) {
					Sizzle.error( match[0] );
				}

				// numeric x and y parameters for Expr.filter.CHILD
				// remember that false/true cast respectively to 0/1
				match[3] = +( match[3] ? match[4] + (match[5] || 1) : 2 * ( match[2] === "even" || match[2] === "odd" ) );
				match[4] = +( ( match[6] + match[7] ) || match[2] === "odd" );

			// other types prohibit arguments
			} else if ( match[2] ) {
				Sizzle.error( match[0] );
			}

			return match;
		},

		"PSEUDO": function( match, context, xml ) {
			var unquoted, excess;
			if ( matchExpr["CHILD"].test( match[0] ) ) {
				return null;
			}

			if ( match[3] ) {
				match[2] = match[3];
			} else if ( (unquoted = match[4]) ) {
				// Only check arguments that contain a pseudo
				if ( rpseudo.test(unquoted) &&
					// Get excess from tokenize (recursively)
					(excess = tokenize( unquoted, context, xml, true )) &&
					// advance to the next closing parenthesis
					(excess = unquoted.indexOf( ")", unquoted.length - excess ) - unquoted.length) ) {

					// excess is a negative index
					unquoted = unquoted.slice( 0, excess );
					match[0] = match[0].slice( 0, excess );
				}
				match[2] = unquoted;
			}

			// Return only captures needed by the pseudo filter method (type and argument)
			return match.slice( 0, 3 );
		}
	},

	filter: {
		"ID": assertGetIdNotName ?
			function( id ) {
				id = id.replace( rbackslash, "" );
				return function( elem ) {
					return elem.getAttribute("id") === id;
				};
			} :
			function( id ) {
				id = id.replace( rbackslash, "" );
				return function( elem ) {
					var node = typeof elem.getAttributeNode !== strundefined && elem.getAttributeNode("id");
					return node && node.value === id;
				};
			},

		"TAG": function( nodeName ) {
			if ( nodeName === "*" ) {
				return function() { return true; };
			}
			nodeName = nodeName.replace( rbackslash, "" ).toLowerCase();

			return function( elem ) {
				return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
			};
		},

		"CLASS": function( className ) {
			var pattern = classCache[ expando ][ className ];
			if ( !pattern ) {
				pattern = classCache( className, new RegExp("(^|" + whitespace + ")" + className + "(" + whitespace + "|$)") );
			}
			return function( elem ) {
				return pattern.test( elem.className || (typeof elem.getAttribute !== strundefined && elem.getAttribute("class")) || "" );
			};
		},

		"ATTR": function( name, operator, check ) {
			if ( !operator ) {
				return function( elem ) {
					return Sizzle.attr( elem, name ) != null;
				};
			}

			return function( elem ) {
				var result = Sizzle.attr( elem, name ),
					value = result + "";

				if ( result == null ) {
					return operator === "!=";
				}

				switch ( operator ) {
					case "=":
						return value === check;
					case "!=":
						return value !== check;
					case "^=":
						return check && value.indexOf( check ) === 0;
					case "*=":
						return check && value.indexOf( check ) > -1;
					case "$=":
						return check && value.substr( value.length - check.length ) === check;
					case "~=":
						return ( " " + value + " " ).indexOf( check ) > -1;
					case "|=":
						return value === check || value.substr( 0, check.length + 1 ) === check + "-";
				}
			};
		},

		"CHILD": function( type, argument, first, last ) {

			if ( type === "nth" ) {
				var doneName = done++;

				return function( elem ) {
					var parent, diff,
						count = 0,
						node = elem;

					if ( first === 1 && last === 0 ) {
						return true;
					}

					parent = elem.parentNode;

					if ( parent && (parent[ expando ] !== doneName || !elem.sizset) ) {
						for ( node = parent.firstChild; node; node = node.nextSibling ) {
							if ( node.nodeType === 1 ) {
								node.sizset = ++count;
								if ( node === elem ) {
									break;
								}
							}
						}

						parent[ expando ] = doneName;
					}

					diff = elem.sizset - last;

					if ( first === 0 ) {
						return diff === 0;

					} else {
						return ( diff % first === 0 && diff / first >= 0 );
					}
				};
			}

			return function( elem ) {
				var node = elem;

				switch ( type ) {
					case "only":
					case "first":
						while ( (node = node.previousSibling) ) {
							if ( node.nodeType === 1 ) {
								return false;
							}
						}

						if ( type === "first" ) {
							return true;
						}

						node = elem;

						/* falls through */
					case "last":
						while ( (node = node.nextSibling) ) {
							if ( node.nodeType === 1 ) {
								return false;
							}
						}

						return true;
				}
			};
		},

		"PSEUDO": function( pseudo, argument, context, xml ) {
			// pseudo-class names are case-insensitive
			// http://www.w3.org/TR/selectors/#pseudo-classes
			// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
			var args,
				fn = Expr.pseudos[ pseudo ] || Expr.pseudos[ pseudo.toLowerCase() ];

			if ( !fn ) {
				Sizzle.error( "unsupported pseudo: " + pseudo );
			}

			// The user may use createPseudo to indicate that
			// arguments are needed to create the filter function
			// just as Sizzle does
			if ( !fn[ expando ] ) {
				if ( fn.length > 1 ) {
					args = [ pseudo, pseudo, "", argument ];
					return function( elem ) {
						return fn( elem, 0, args );
					};
				}
				return fn;
			}

			return fn( argument, context, xml );
		}
	},

	pseudos: {
		"not": markFunction(function( selector, context, xml ) {
			// Trim the selector passed to compile
			// to avoid treating leading and trailing
			// spaces as combinators
			var matcher = compile( selector.replace( rtrim, "$1" ), context, xml );
			return function( elem ) {
				return !matcher( elem );
			};
		}),

		"enabled": function( elem ) {
			return elem.disabled === false;
		},

		"disabled": function( elem ) {
			return elem.disabled === true;
		},

		"checked": function( elem ) {
			// In CSS3, :checked should return both checked and selected elements
			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			var nodeName = elem.nodeName.toLowerCase();
			return (nodeName === "input" && !!elem.checked) || (nodeName === "option" && !!elem.selected);
		},

		"selected": function( elem ) {
			// Accessing this property makes selected-by-default
			// options in Safari work properly
			if ( elem.parentNode ) {
				elem.parentNode.selectedIndex;
			}

			return elem.selected === true;
		},

		"parent": function( elem ) {
			return !Expr.pseudos["empty"]( elem );
		},

		"empty": function( elem ) {
			// http://www.w3.org/TR/selectors/#empty-pseudo
			// :empty is only affected by element nodes and content nodes(including text(3), cdata(4)),
			//   not comment, processing instructions, or others
			// Thanks to Diego Perini for the nodeName shortcut
			//   Greater than "@" means alpha characters (specifically not starting with "#" or "?")
			var nodeType;
			elem = elem.firstChild;
			while ( elem ) {
				if ( elem.nodeName > "@" || (nodeType = elem.nodeType) === 3 || nodeType === 4 ) {
					return false;
				}
				elem = elem.nextSibling;
			}
			return true;
		},

		"contains": markFunction(function( text ) {
			return function( elem ) {
				return ( elem.textContent || elem.innerText || getText( elem ) ).indexOf( text ) > -1;
			};
		}),

		"has": markFunction(function( selector ) {
			return function( elem ) {
				return Sizzle( selector, elem ).length > 0;
			};
		}),

		"header": function( elem ) {
			return rheader.test( elem.nodeName );
		},

		"text": function( elem ) {
			var type, attr;
			// IE6 and 7 will map elem.type to 'text' for new HTML5 types (search, etc)
			// use getAttribute instead to test this case
			return elem.nodeName.toLowerCase() === "input" &&
				(type = elem.type) === "text" &&
				( (attr = elem.getAttribute("type")) == null || attr.toLowerCase() === type );
		},

		// Input types
		"radio": createInputPseudo("radio"),
		"checkbox": createInputPseudo("checkbox"),
		"file": createInputPseudo("file"),
		"password": createInputPseudo("password"),
		"image": createInputPseudo("image"),

		"submit": createButtonPseudo("submit"),
		"reset": createButtonPseudo("reset"),

		"button": function( elem ) {
			var name = elem.nodeName.toLowerCase();
			return name === "input" && elem.type === "button" || name === "button";
		},

		"input": function( elem ) {
			return rinputs.test( elem.nodeName );
		},

		"focus": function( elem ) {
			var doc = elem.ownerDocument;
			return elem === doc.activeElement && (!doc.hasFocus || doc.hasFocus()) && !!(elem.type || elem.href);
		},

		"active": function( elem ) {
			return elem === elem.ownerDocument.activeElement;
		}
	},

	setFilters: {
		"first": function( elements, argument, not ) {
			return not ? elements.slice( 1 ) : [ elements[0] ];
		},

		"last": function( elements, argument, not ) {
			var elem = elements.pop();
			return not ? elements : [ elem ];
		},

		"even": function( elements, argument, not ) {
			var results = [],
				i = not ? 1 : 0,
				len = elements.length;
			for ( ; i < len; i = i + 2 ) {
				results.push( elements[i] );
			}
			return results;
		},

		"odd": function( elements, argument, not ) {
			var results = [],
				i = not ? 0 : 1,
				len = elements.length;
			for ( ; i < len; i = i + 2 ) {
				results.push( elements[i] );
			}
			return results;
		},

		"lt": function( elements, argument, not ) {
			return not ? elements.slice( +argument ) : elements.slice( 0, +argument );
		},

		"gt": function( elements, argument, not ) {
			return not ? elements.slice( 0, +argument + 1 ) : elements.slice( +argument + 1 );
		},

		"eq": function( elements, argument, not ) {
			var elem = elements.splice( +argument, 1 );
			return not ? elements : elem;
		}
	}
};

function siblingCheck( a, b, ret ) {
	if ( a === b ) {
		return ret;
	}

	var cur = a.nextSibling;

	while ( cur ) {
		if ( cur === b ) {
			return -1;
		}

		cur = cur.nextSibling;
	}

	return 1;
}

sortOrder = docElem.compareDocumentPosition ?
	function( a, b ) {
		if ( a === b ) {
			hasDuplicate = true;
			return 0;
		}

		return ( !a.compareDocumentPosition || !b.compareDocumentPosition ?
			a.compareDocumentPosition :
			a.compareDocumentPosition(b) & 4
		) ? -1 : 1;
	} :
	function( a, b ) {
		// The nodes are identical, we can exit early
		if ( a === b ) {
			hasDuplicate = true;
			return 0;

		// Fallback to using sourceIndex (in IE) if it's available on both nodes
		} else if ( a.sourceIndex && b.sourceIndex ) {
			return a.sourceIndex - b.sourceIndex;
		}

		var al, bl,
			ap = [],
			bp = [],
			aup = a.parentNode,
			bup = b.parentNode,
			cur = aup;

		// If the nodes are siblings (or identical) we can do a quick check
		if ( aup === bup ) {
			return siblingCheck( a, b );

		// If no parents were found then the nodes are disconnected
		} else if ( !aup ) {
			return -1;

		} else if ( !bup ) {
			return 1;
		}

		// Otherwise they're somewhere else in the tree so we need
		// to build up a full list of the parentNodes for comparison
		while ( cur ) {
			ap.unshift( cur );
			cur = cur.parentNode;
		}

		cur = bup;

		while ( cur ) {
			bp.unshift( cur );
			cur = cur.parentNode;
		}

		al = ap.length;
		bl = bp.length;

		// Start walking down the tree looking for a discrepancy
		for ( var i = 0; i < al && i < bl; i++ ) {
			if ( ap[i] !== bp[i] ) {
				return siblingCheck( ap[i], bp[i] );
			}
		}

		// We ended someplace up the tree so do a sibling check
		return i === al ?
			siblingCheck( a, bp[i], -1 ) :
			siblingCheck( ap[i], b, 1 );
	};

// Always assume the presence of duplicates if sort doesn't
// pass them to our comparison function (as in Google Chrome).
[0, 0].sort( sortOrder );
baseHasDuplicate = !hasDuplicate;

// Document sorting and removing duplicates
Sizzle.uniqueSort = function( results ) {
	var elem,
		i = 1;

	hasDuplicate = baseHasDuplicate;
	results.sort( sortOrder );

	if ( hasDuplicate ) {
		for ( ; (elem = results[i]); i++ ) {
			if ( elem === results[ i - 1 ] ) {
				results.splice( i--, 1 );
			}
		}
	}

	return results;
};

Sizzle.error = function( msg ) {
	throw new Error( "Syntax error, unrecognized expression: " + msg );
};

function tokenize( selector, context, xml, parseOnly ) {
	var matched, match, tokens, type,
		soFar, groups, group, i,
		preFilters, filters,
		checkContext = !xml && context !== document,
		// Token cache should maintain spaces
		key = ( checkContext ? "<s>" : "" ) + selector.replace( rtrim, "$1<s>" ),
		cached = tokenCache[ expando ][ key ];

	if ( cached ) {
		return parseOnly ? 0 : slice.call( cached, 0 );
	}

	soFar = selector;
	groups = [];
	i = 0;
	preFilters = Expr.preFilter;
	filters = Expr.filter;

	while ( soFar ) {

		// Comma and first run
		if ( !matched || (match = rcomma.exec( soFar )) ) {
			if ( match ) {
				soFar = soFar.slice( match[0].length );
				tokens.selector = group;
			}
			groups.push( tokens = [] );
			group = "";

			// Need to make sure we're within a narrower context if necessary
			// Adding a descendant combinator will generate what is needed
			if ( checkContext ) {
				soFar = " " + soFar;
			}
		}

		matched = false;

		// Combinators
		if ( (match = rcombinators.exec( soFar )) ) {
			group += match[0];
			soFar = soFar.slice( match[0].length );

			// Cast descendant combinators to space
			matched = tokens.push({
				part: match.pop().replace( rtrim, " " ),
				string: match[0],
				captures: match
			});
		}

		// Filters
		for ( type in filters ) {
			if ( (match = matchExpr[ type ].exec( soFar )) && (!preFilters[ type ] ||
				( match = preFilters[ type ](match, context, xml) )) ) {

				group += match[0];
				soFar = soFar.slice( match[0].length );
				matched = tokens.push({
					part: type,
					string: match.shift(),
					captures: match
				});
			}
		}

		if ( !matched ) {
			break;
		}
	}

	// Attach the full group as a selector
	if ( group ) {
		tokens.selector = group;
	}

	// Return the length of the invalid excess
	// if we're just parsing
	// Otherwise, throw an error or return tokens
	return parseOnly ?
		soFar.length :
		soFar ?
			Sizzle.error( selector ) :
			// Cache the tokens
			slice.call( tokenCache(key, groups), 0 );
}

function addCombinator( matcher, combinator, context, xml ) {
	var dir = combinator.dir,
		doneName = done++;

	if ( !matcher ) {
		// If there is no matcher to check, check against the context
		matcher = function( elem ) {
			return elem === context;
		};
	}
	return combinator.first ?
		function( elem ) {
			while ( (elem = elem[ dir ]) ) {
				if ( elem.nodeType === 1 ) {
					return matcher( elem ) && elem;
				}
			}
		} :
		xml ?
			function( elem ) {
				while ( (elem = elem[ dir ]) ) {
					if ( elem.nodeType === 1 ) {
						if ( matcher( elem ) ) {
							return elem;
						}
					}
				}
			} :
			function( elem ) {
				var cache,
					dirkey = doneName + "." + dirruns,
					cachedkey = dirkey + "." + cachedruns;
				while ( (elem = elem[ dir ]) ) {
					if ( elem.nodeType === 1 ) {
						if ( (cache = elem[ expando ]) === cachedkey ) {
							return elem.sizset;
						} else if ( typeof cache === "string" && cache.indexOf(dirkey) === 0 ) {
							if ( elem.sizset ) {
								return elem;
							}
						} else {
							elem[ expando ] = cachedkey;
							if ( matcher( elem ) ) {
								elem.sizset = true;
								return elem;
							}
							elem.sizset = false;
						}
					}
				}
			};
}

function addMatcher( higher, deeper ) {
	return higher ?
		function( elem ) {
			var result = deeper( elem );
			return result && higher( result === true ? elem : result );
		} :
		deeper;
}

// ["TAG", ">", "ID", " ", "CLASS"]
function matcherFromTokens( tokens, context, xml ) {
	var token, matcher,
		i = 0;

	for ( ; (token = tokens[i]); i++ ) {
		if ( Expr.relative[ token.part ] ) {
			matcher = addCombinator( matcher, Expr.relative[ token.part ], context, xml );
		} else {
			matcher = addMatcher( matcher, Expr.filter[ token.part ].apply(null, token.captures.concat( context, xml )) );
		}
	}

	return matcher;
}

function matcherFromGroupMatchers( matchers ) {
	return function( elem ) {
		var matcher,
			j = 0;
		for ( ; (matcher = matchers[j]); j++ ) {
			if ( matcher(elem) ) {
				return true;
			}
		}
		return false;
	};
}

compile = Sizzle.compile = function( selector, context, xml ) {
	var group, i, len,
		cached = compilerCache[ expando ][ selector ];

	// Return a cached group function if already generated (context dependent)
	if ( cached && cached.context === context ) {
		return cached;
	}

	// Generate a function of recursive functions that can be used to check each element
	group = tokenize( selector, context, xml );
	for ( i = 0, len = group.length; i < len; i++ ) {
		group[i] = matcherFromTokens(group[i], context, xml);
	}

	// Cache the compiled function
	cached = compilerCache( selector, matcherFromGroupMatchers(group) );
	cached.context = context;
	cached.runs = cached.dirruns = 0;
	return cached;
};

function multipleContexts( selector, contexts, results, seed ) {
	var i = 0,
		len = contexts.length;
	for ( ; i < len; i++ ) {
		Sizzle( selector, contexts[i], results, seed );
	}
}

function handlePOSGroup( selector, posfilter, argument, contexts, seed, not ) {
	var results,
		fn = Expr.setFilters[ posfilter.toLowerCase() ];

	if ( !fn ) {
		Sizzle.error( posfilter );
	}

	if ( selector || !(results = seed) ) {
		multipleContexts( selector || "*", contexts, (results = []), seed );
	}

	return results.length > 0 ? fn( results, argument, not ) : [];
}

function handlePOS( groups, context, results, seed ) {
	var group, part, j, groupLen, token, selector,
		anchor, elements, match, matched,
		lastIndex, currentContexts, not,
		i = 0,
		len = groups.length,
		rpos = matchExpr["POS"],
		// This is generated here in case matchExpr["POS"] is extended
		rposgroups = new RegExp( "^" + rpos.source + "(?!" + whitespace + ")", "i" ),
		// This is for making sure non-participating
		// matching groups are represented cross-browser (IE6-8)
		setUndefined = function() {
			var i = 1,
				len = arguments.length - 2;
			for ( ; i < len; i++ ) {
				if ( arguments[i] === undefined ) {
					match[i] = undefined;
				}
			}
		};

	for ( ; i < len; i++ ) {
		group = groups[i];
		part = "";
		elements = seed;
		for ( j = 0, groupLen = group.length; j < groupLen; j++ ) {
			token = group[j];
			selector = token.string;
			if ( token.part === "PSEUDO" ) {
				// Reset regex index to 0
				rpos.exec("");
				anchor = 0;
				while ( (match = rpos.exec( selector )) ) {
					matched = true;
					lastIndex = rpos.lastIndex = match.index + match[0].length;
					if ( lastIndex > anchor ) {
						part += selector.slice( anchor, match.index );
						anchor = lastIndex;
						currentContexts = [ context ];

						if ( rcombinators.test(part) ) {
							if ( elements ) {
								currentContexts = elements;
							}
							elements = seed;
						}

						if ( (not = rendsWithNot.test( part )) ) {
							part = part.slice( 0, -5 ).replace( rcombinators, "$&*" );
							anchor++;
						}

						if ( match.length > 1 ) {
							match[0].replace( rposgroups, setUndefined );
						}
						elements = handlePOSGroup( part, match[1], match[2], currentContexts, elements, not );
					}
					part = "";
				}

			}

			if ( !matched ) {
				part += selector;
			}
			matched = false;
		}

		if ( part ) {
			if ( rcombinators.test(part) ) {
				multipleContexts( part, elements || [ context ], results, seed );
			} else {
				Sizzle( part, context, results, seed ? seed.concat(elements) : elements );
			}
		} else {
			push.apply( results, elements );
		}
	}

	// Do not sort if this is a single filter
	return len === 1 ? results : Sizzle.uniqueSort( results );
}

function select( selector, context, results, seed, xml ) {
	// Remove excessive whitespace
	selector = selector.replace( rtrim, "$1" );
	var elements, matcher, cached, elem,
		i, tokens, token, lastToken, findContext, type,
		match = tokenize( selector, context, xml ),
		contextNodeType = context.nodeType;

	// POS handling
	if ( matchExpr["POS"].test(selector) ) {
		return handlePOS( match, context, results, seed );
	}

	if ( seed ) {
		elements = slice.call( seed, 0 );

	// To maintain document order, only narrow the
	// set if there is one group
	} else if ( match.length === 1 ) {

		// Take a shortcut and set the context if the root selector is an ID
		if ( (tokens = slice.call( match[0], 0 )).length > 2 &&
				(token = tokens[0]).part === "ID" &&
				contextNodeType === 9 && !xml &&
				Expr.relative[ tokens[1].part ] ) {

			context = Expr.find["ID"]( token.captures[0].replace( rbackslash, "" ), context, xml )[0];
			if ( !context ) {
				return results;
			}

			selector = selector.slice( tokens.shift().string.length );
		}

		findContext = ( (match = rsibling.exec( tokens[0].string )) && !match.index && context.parentNode ) || context;

		// Reduce the set if possible
		lastToken = "";
		for ( i = tokens.length - 1; i >= 0; i-- ) {
			token = tokens[i];
			type = token.part;
			lastToken = token.string + lastToken;
			if ( Expr.relative[ type ] ) {
				break;
			}
			if ( Expr.order.test(type) ) {
				elements = Expr.find[ type ]( token.captures[0].replace( rbackslash, "" ), findContext, xml );
				if ( elements == null ) {
					continue;
				} else {
					selector = selector.slice( 0, selector.length - lastToken.length ) +
						lastToken.replace( matchExpr[ type ], "" );

					if ( !selector ) {
						push.apply( results, slice.call(elements, 0) );
					}

					break;
				}
			}
		}
	}

	// Only loop over the given elements once
	if ( selector ) {
		matcher = compile( selector, context, xml );
		dirruns = matcher.dirruns++;
		if ( elements == null ) {
			elements = Expr.find["TAG"]( "*", (rsibling.test( selector ) && context.parentNode) || context );
		}

		for ( i = 0; (elem = elements[i]); i++ ) {
			cachedruns = matcher.runs++;
			if ( matcher(elem) ) {
				results.push( elem );
			}
		}
	}

	return results;
}

if ( document.querySelectorAll ) {
	(function() {
		var disconnectedMatch,
			oldSelect = select,
			rescape = /'|\\/g,
			rattributeQuotes = /\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,
			rbuggyQSA = [],
			// matchesSelector(:active) reports false when true (IE9/Opera 11.5)
			// A support test would require too much code (would include document ready)
			// just skip matchesSelector for :active
			rbuggyMatches = [":active"],
			matches = docElem.matchesSelector ||
				docElem.mozMatchesSelector ||
				docElem.webkitMatchesSelector ||
				docElem.oMatchesSelector ||
				docElem.msMatchesSelector;

		// Build QSA regex
		// Regex strategy adopted from Diego Perini
		assert(function( div ) {
			// Select is set to empty string on purpose
			// This is to test IE's treatment of not explictly
			// setting a boolean content attribute,
			// since its presence should be enough
			// http://bugs.jquery.com/ticket/12359
			div.innerHTML = "<select><option selected=''></option></select>";

			// IE8 - Some boolean attributes are not treated correctly
			if ( !div.querySelectorAll("[selected]").length ) {
				rbuggyQSA.push( "\\[" + whitespace + "*(?:checked|disabled|ismap|multiple|readonly|selected|value)" );
			}

			// Webkit/Opera - :checked should return selected option elements
			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
			// IE8 throws error here (do not put tests after this one)
			if ( !div.querySelectorAll(":checked").length ) {
				rbuggyQSA.push(":checked");
			}
		});

		assert(function( div ) {

			// Opera 10-12/IE9 - ^= $= *= and empty values
			// Should not select anything
			div.innerHTML = "<p test=''></p>";
			if ( div.querySelectorAll("[test^='']").length ) {
				rbuggyQSA.push( "[*^$]=" + whitespace + "*(?:\"\"|'')" );
			}

			// FF 3.5 - :enabled/:disabled and hidden elements (hidden elements are still enabled)
			// IE8 throws error here (do not put tests after this one)
			div.innerHTML = "<input type='hidden'/>";
			if ( !div.querySelectorAll(":enabled").length ) {
				rbuggyQSA.push(":enabled", ":disabled");
			}
		});

		rbuggyQSA = rbuggyQSA.length && new RegExp( rbuggyQSA.join("|") );

		select = function( selector, context, results, seed, xml ) {
			// Only use querySelectorAll when not filtering,
			// when this is not xml,
			// and when no QSA bugs apply
			if ( !seed && !xml && (!rbuggyQSA || !rbuggyQSA.test( selector )) ) {
				if ( context.nodeType === 9 ) {
					try {
						push.apply( results, slice.call(context.querySelectorAll( selector ), 0) );
						return results;
					} catch(qsaError) {}
				// qSA works strangely on Element-rooted queries
				// We can work around this by specifying an extra ID on the root
				// and working up from there (Thanks to Andrew Dupont for the technique)
				// IE 8 doesn't work on object elements
				} else if ( context.nodeType === 1 && context.nodeName.toLowerCase() !== "object" ) {
					var groups, i, len,
						old = context.getAttribute("id"),
						nid = old || expando,
						newContext = rsibling.test( selector ) && context.parentNode || context;

					if ( old ) {
						nid = nid.replace( rescape, "\\$&" );
					} else {
						context.setAttribute( "id", nid );
					}

					groups = tokenize(selector, context, xml);
					// Trailing space is unnecessary
					// There is always a context check
					nid = "[id='" + nid + "']";
					for ( i = 0, len = groups.length; i < len; i++ ) {
						groups[i] = nid + groups[i].selector;
					}
					try {
						push.apply( results, slice.call( newContext.querySelectorAll(
							groups.join(",")
						), 0 ) );
						return results;
					} catch(qsaError) {
					} finally {
						if ( !old ) {
							context.removeAttribute("id");
						}
					}
				}
			}

			return oldSelect( selector, context, results, seed, xml );
		};

		if ( matches ) {
			assert(function( div ) {
				// Check to see if it's possible to do matchesSelector
				// on a disconnected node (IE 9)
				disconnectedMatch = matches.call( div, "div" );

				// This should fail with an exception
				// Gecko does not error, returns false instead
				try {
					matches.call( div, "[test!='']:sizzle" );
					rbuggyMatches.push( matchExpr["PSEUDO"].source, matchExpr["POS"].source, "!=" );
				} catch ( e ) {}
			});

			// rbuggyMatches always contains :active, so no need for a length check
			rbuggyMatches = /* rbuggyMatches.length && */ new RegExp( rbuggyMatches.join("|") );

			Sizzle.matchesSelector = function( elem, expr ) {
				// Make sure that attribute selectors are quoted
				expr = expr.replace( rattributeQuotes, "='$1']" );

				// rbuggyMatches always contains :active, so no need for an existence check
				if ( !isXML( elem ) && !rbuggyMatches.test( expr ) && (!rbuggyQSA || !rbuggyQSA.test( expr )) ) {
					try {
						var ret = matches.call( elem, expr );

						// IE 9's matchesSelector returns false on disconnected nodes
						if ( ret || disconnectedMatch ||
								// As well, disconnected nodes are said to be in a document
								// fragment in IE 9
								elem.document && elem.document.nodeType !== 11 ) {
							return ret;
						}
					} catch(e) {}
				}

				return Sizzle( expr, null, null, [ elem ] ).length > 0;
			};
		}
	})();
}

// Deprecated
Expr.setFilters["nth"] = Expr.setFilters["eq"];

// Back-compat
Expr.filters = Expr.pseudos;

// Override sizzle attribute retrieval
Sizzle.attr = jQuery.attr;
jQuery.find = Sizzle;
jQuery.expr = Sizzle.selectors;
jQuery.expr[":"] = jQuery.expr.pseudos;
jQuery.unique = Sizzle.uniqueSort;
jQuery.text = Sizzle.getText;
jQuery.isXMLDoc = Sizzle.isXML;
jQuery.contains = Sizzle.contains;


})( window );
var runtil = /Until$/,
	rparentsprev = /^(?:parents|prev(?:Until|All))/,
	isSimple = /^.[^:#\[\.,]*$/,
	rneedsContext = jQuery.expr.match.needsContext,
	// methods guaranteed to produce a unique set when starting from a unique set
	guaranteedUnique = {
		children: true,
		contents: true,
		next: true,
		prev: true
	};

jQuery.fn.extend({
	find: function( selector ) {
		var i, l, length, n, r, ret,
			self = this;

		if ( typeof selector !== "string" ) {
			return jQuery( selector ).filter(function() {
				for ( i = 0, l = self.length; i < l; i++ ) {
					if ( jQuery.contains( self[ i ], this ) ) {
						return true;
					}
				}
			});
		}

		ret = this.pushStack( "", "find", selector );

		for ( i = 0, l = this.length; i < l; i++ ) {
			length = ret.length;
			jQuery.find( selector, this[i], ret );

			if ( i > 0 ) {
				// Make sure that the results are unique
				for ( n = length; n < ret.length; n++ ) {
					for ( r = 0; r < length; r++ ) {
						if ( ret[r] === ret[n] ) {
							ret.splice(n--, 1);
							break;
						}
					}
				}
			}
		}

		return ret;
	},

	has: function( target ) {
		var i,
			targets = jQuery( target, this ),
			len = targets.length;

		return this.filter(function() {
			for ( i = 0; i < len; i++ ) {
				if ( jQuery.contains( this, targets[i] ) ) {
					return true;
				}
			}
		});
	},

	not: function( selector ) {
		return this.pushStack( winnow(this, selector, false), "not", selector);
	},

	filter: function( selector ) {
		return this.pushStack( winnow(this, selector, true), "filter", selector );
	},

	is: function( selector ) {
		return !!selector && (
			typeof selector === "string" ?
				// If this is a positional/relative selector, check membership in the returned set
				// so $("p:first").is("p:last") won't return true for a doc with two "p".
				rneedsContext.test( selector ) ?
					jQuery( selector, this.context ).index( this[0] ) >= 0 :
					jQuery.filter( selector, this ).length > 0 :
				this.filter( selector ).length > 0 );
	},

	closest: function( selectors, context ) {
		var cur,
			i = 0,
			l = this.length,
			ret = [],
			pos = rneedsContext.test( selectors ) || typeof selectors !== "string" ?
				jQuery( selectors, context || this.context ) :
				0;

		for ( ; i < l; i++ ) {
			cur = this[i];

			while ( cur && cur.ownerDocument && cur !== context && cur.nodeType !== 11 ) {
				if ( pos ? pos.index(cur) > -1 : jQuery.find.matchesSelector(cur, selectors) ) {
					ret.push( cur );
					break;
				}
				cur = cur.parentNode;
			}
		}

		ret = ret.length > 1 ? jQuery.unique( ret ) : ret;

		return this.pushStack( ret, "closest", selectors );
	},

	// Determine the position of an element within
	// the matched set of elements
	index: function( elem ) {

		// No argument, return index in parent
		if ( !elem ) {
			return ( this[0] && this[0].parentNode ) ? this.prevAll().length : -1;
		}

		// index in selector
		if ( typeof elem === "string" ) {
			return jQuery.inArray( this[0], jQuery( elem ) );
		}

		// Locate the position of the desired element
		return jQuery.inArray(
			// If it receives a jQuery object, the first element is used
			elem.jquery ? elem[0] : elem, this );
	},

	add: function( selector, context ) {
		var set = typeof selector === "string" ?
				jQuery( selector, context ) :
				jQuery.makeArray( selector && selector.nodeType ? [ selector ] : selector ),
			all = jQuery.merge( this.get(), set );

		return this.pushStack( isDisconnected( set[0] ) || isDisconnected( all[0] ) ?
			all :
			jQuery.unique( all ) );
	},

	addBack: function( selector ) {
		return this.add( selector == null ?
			this.prevObject : this.prevObject.filter(selector)
		);
	}
});

jQuery.fn.andSelf = jQuery.fn.addBack;

// A painfully simple check to see if an element is disconnected
// from a document (should be improved, where feasible).
function isDisconnected( node ) {
	return !node || !node.parentNode || node.parentNode.nodeType === 11;
}

function sibling( cur, dir ) {
	do {
		cur = cur[ dir ];
	} while ( cur && cur.nodeType !== 1 );

	return cur;
}

jQuery.each({
	parent: function( elem ) {
		var parent = elem.parentNode;
		return parent && parent.nodeType !== 11 ? parent : null;
	},
	parents: function( elem ) {
		return jQuery.dir( elem, "parentNode" );
	},
	parentsUntil: function( elem, i, until ) {
		return jQuery.dir( elem, "parentNode", until );
	},
	next: function( elem ) {
		return sibling( elem, "nextSibling" );
	},
	prev: function( elem ) {
		return sibling( elem, "previousSibling" );
	},
	nextAll: function( elem ) {
		return jQuery.dir( elem, "nextSibling" );
	},
	prevAll: function( elem ) {
		return jQuery.dir( elem, "previousSibling" );
	},
	nextUntil: function( elem, i, until ) {
		return jQuery.dir( elem, "nextSibling", until );
	},
	prevUntil: function( elem, i, until ) {
		return jQuery.dir( elem, "previousSibling", until );
	},
	siblings: function( elem ) {
		return jQuery.sibling( ( elem.parentNode || {} ).firstChild, elem );
	},
	children: function( elem ) {
		return jQuery.sibling( elem.firstChild );
	},
	contents: function( elem ) {
		return jQuery.nodeName( elem, "iframe" ) ?
			elem.contentDocument || elem.contentWindow.document :
			jQuery.merge( [], elem.childNodes );
	}
}, function( name, fn ) {
	jQuery.fn[ name ] = function( until, selector ) {
		var ret = jQuery.map( this, fn, until );

		if ( !runtil.test( name ) ) {
			selector = until;
		}

		if ( selector && typeof selector === "string" ) {
			ret = jQuery.filter( selector, ret );
		}

		ret = this.length > 1 && !guaranteedUnique[ name ] ? jQuery.unique( ret ) : ret;

		if ( this.length > 1 && rparentsprev.test( name ) ) {
			ret = ret.reverse();
		}

		return this.pushStack( ret, name, core_slice.call( arguments ).join(",") );
	};
});

jQuery.extend({
	filter: function( expr, elems, not ) {
		if ( not ) {
			expr = ":not(" + expr + ")";
		}

		return elems.length === 1 ?
			jQuery.find.matchesSelector(elems[0], expr) ? [ elems[0] ] : [] :
			jQuery.find.matches(expr, elems);
	},

	dir: function( elem, dir, until ) {
		var matched = [],
			cur = elem[ dir ];

		while ( cur && cur.nodeType !== 9 && (until === undefined || cur.nodeType !== 1 || !jQuery( cur ).is( until )) ) {
			if ( cur.nodeType === 1 ) {
				matched.push( cur );
			}
			cur = cur[dir];
		}
		return matched;
	},

	sibling: function( n, elem ) {
		var r = [];

		for ( ; n; n = n.nextSibling ) {
			if ( n.nodeType === 1 && n !== elem ) {
				r.push( n );
			}
		}

		return r;
	}
});

// Implement the identical functionality for filter and not
function winnow( elements, qualifier, keep ) {

	// Can't pass null or undefined to indexOf in Firefox 4
	// Set to 0 to skip string check
	qualifier = qualifier || 0;

	if ( jQuery.isFunction( qualifier ) ) {
		return jQuery.grep(elements, function( elem, i ) {
			var retVal = !!qualifier.call( elem, i, elem );
			return retVal === keep;
		});

	} else if ( qualifier.nodeType ) {
		return jQuery.grep(elements, function( elem, i ) {
			return ( elem === qualifier ) === keep;
		});

	} else if ( typeof qualifier === "string" ) {
		var filtered = jQuery.grep(elements, function( elem ) {
			return elem.nodeType === 1;
		});

		if ( isSimple.test( qualifier ) ) {
			return jQuery.filter(qualifier, filtered, !keep);
		} else {
			qualifier = jQuery.filter( qualifier, filtered );
		}
	}

	return jQuery.grep(elements, function( elem, i ) {
		return ( jQuery.inArray( elem, qualifier ) >= 0 ) === keep;
	});
}
function createSafeFragment( document ) {
	var list = nodeNames.split( "|" ),
	safeFrag = document.createDocumentFragment();

	if ( safeFrag.createElement ) {
		while ( list.length ) {
			safeFrag.createElement(
				list.pop()
			);
		}
	}
	return safeFrag;
}

var nodeNames = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|" +
		"header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",
	rinlinejQuery = / jQuery\d+="(?:null|\d+)"/g,
	rleadingWhitespace = /^\s+/,
	rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
	rtagName = /<([\w:]+)/,
	rtbody = /<tbody/i,
	rhtml = /<|&#?\w+;/,
	rnoInnerhtml = /<(?:script|style|link)/i,
	rnocache = /<(?:script|object|embed|option|style)/i,
	rnoshimcache = new RegExp("<(?:" + nodeNames + ")[\\s/>]", "i"),
	rcheckableType = /^(?:checkbox|radio)$/,
	// checked="checked" or checked
	rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,
	rscriptType = /\/(java|ecma)script/i,
	rcleanScript = /^\s*<!(?:\[CDATA\[|\-\-)|[\]\-]{2}>\s*$/g,
	wrapMap = {
		option: [ 1, "<select multiple='multiple'>", "</select>" ],
		legend: [ 1, "<fieldset>", "</fieldset>" ],
		thead: [ 1, "<table>", "</table>" ],
		tr: [ 2, "<table><tbody>", "</tbody></table>" ],
		td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],
		col: [ 2, "<table><tbody></tbody><colgroup>", "</colgroup></table>" ],
		area: [ 1, "<map>", "</map>" ],
		_default: [ 0, "", "" ]
	},
	safeFragment = createSafeFragment( document ),
	fragmentDiv = safeFragment.appendChild( document.createElement("div") );

wrapMap.optgroup = wrapMap.option;
wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;

// IE6-8 can't serialize link, script, style, or any html5 (NoScope) tags,
// unless wrapped in a div with non-breaking characters in front of it.
if ( !jQuery.support.htmlSerialize ) {
	wrapMap._default = [ 1, "X<div>", "</div>" ];
}

jQuery.fn.extend({
	text: function( value ) {
		return jQuery.access( this, function( value ) {
			return value === undefined ?
				jQuery.text( this ) :
				this.empty().append( ( this[0] && this[0].ownerDocument || document ).createTextNode( value ) );
		}, null, value, arguments.length );
	},

	wrapAll: function( html ) {
		if ( jQuery.isFunction( html ) ) {
			return this.each(function(i) {
				jQuery(this).wrapAll( html.call(this, i) );
			});
		}

		if ( this[0] ) {
			// The elements to wrap the target around
			var wrap = jQuery( html, this[0].ownerDocument ).eq(0).clone(true);

			if ( this[0].parentNode ) {
				wrap.insertBefore( this[0] );
			}

			wrap.map(function() {
				var elem = this;

				while ( elem.firstChild && elem.firstChild.nodeType === 1 ) {
					elem = elem.firstChild;
				}

				return elem;
			}).append( this );
		}

		return this;
	},

	wrapInner: function( html ) {
		if ( jQuery.isFunction( html ) ) {
			return this.each(function(i) {
				jQuery(this).wrapInner( html.call(this, i) );
			});
		}

		return this.each(function() {
			var self = jQuery( this ),
				contents = self.contents();

			if ( contents.length ) {
				contents.wrapAll( html );

			} else {
				self.append( html );
			}
		});
	},

	wrap: function( html ) {
		var isFunction = jQuery.isFunction( html );

		return this.each(function(i) {
			jQuery( this ).wrapAll( isFunction ? html.call(this, i) : html );
		});
	},

	unwrap: function() {
		return this.parent().each(function() {
			if ( !jQuery.nodeName( this, "body" ) ) {
				jQuery( this ).replaceWith( this.childNodes );
			}
		}).end();
	},

	append: function() {
		return this.domManip(arguments, true, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 ) {
				this.appendChild( elem );
			}
		});
	},

	prepend: function() {
		return this.domManip(arguments, true, function( elem ) {
			if ( this.nodeType === 1 || this.nodeType === 11 ) {
				this.insertBefore( elem, this.firstChild );
			}
		});
	},

	before: function() {
		if ( !isDisconnected( this[0] ) ) {
			return this.domManip(arguments, false, function( elem ) {
				this.parentNode.insertBefore( elem, this );
			});
		}

		if ( arguments.length ) {
			var set = jQuery.clean( arguments );
			return this.pushStack( jQuery.merge( set, this ), "before", this.selector );
		}
	},

	after: function() {
		if ( !isDisconnected( this[0] ) ) {
			return this.domManip(arguments, false, function( elem ) {
				this.parentNode.insertBefore( elem, this.nextSibling );
			});
		}

		if ( arguments.length ) {
			var set = jQuery.clean( arguments );
			return this.pushStack( jQuery.merge( this, set ), "after", this.selector );
		}
	},

	// keepData is for internal use only--do not document
	remove: function( selector, keepData ) {
		var elem,
			i = 0;

		for ( ; (elem = this[i]) != null; i++ ) {
			if ( !selector || jQuery.filter( selector, [ elem ] ).length ) {
				if ( !keepData && elem.nodeType === 1 ) {
					jQuery.cleanData( elem.getElementsByTagName("*") );
					jQuery.cleanData( [ elem ] );
				}

				if ( elem.parentNode ) {
					elem.parentNode.removeChild( elem );
				}
			}
		}

		return this;
	},

	empty: function() {
		var elem,
			i = 0;

		for ( ; (elem = this[i]) != null; i++ ) {
			// Remove element nodes and prevent memory leaks
			if ( elem.nodeType === 1 ) {
				jQuery.cleanData( elem.getElementsByTagName("*") );
			}

			// Remove any remaining nodes
			while ( elem.firstChild ) {
				elem.removeChild( elem.firstChild );
			}
		}

		return this;
	},

	clone: function( dataAndEvents, deepDataAndEvents ) {
		dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
		deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;

		return this.map( function () {
			return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
		});
	},

	html: function( value ) {
		return jQuery.access( this, function( value ) {
			var elem = this[0] || {},
				i = 0,
				l = this.length;

			if ( value === undefined ) {
				return elem.nodeType === 1 ?
					elem.innerHTML.replace( rinlinejQuery, "" ) :
					undefined;
			}

			// See if we can take a shortcut and just use innerHTML
			if ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
				( jQuery.support.htmlSerialize || !rnoshimcache.test( value )  ) &&
				( jQuery.support.leadingWhitespace || !rleadingWhitespace.test( value ) ) &&
				!wrapMap[ ( rtagName.exec( value ) || ["", ""] )[1].toLowerCase() ] ) {

				value = value.replace( rxhtmlTag, "<$1></$2>" );

				try {
					for (; i < l; i++ ) {
						// Remove element nodes and prevent memory leaks
						elem = this[i] || {};
						if ( elem.nodeType === 1 ) {
							jQuery.cleanData( elem.getElementsByTagName( "*" ) );
							elem.innerHTML = value;
						}
					}

					elem = 0;

				// If using innerHTML throws an exception, use the fallback method
				} catch(e) {}
			}

			if ( elem ) {
				this.empty().append( value );
			}
		}, null, value, arguments.length );
	},

	replaceWith: function( value ) {
		if ( !isDisconnected( this[0] ) ) {
			// Make sure that the elements are removed from the DOM before they are inserted
			// this can help fix replacing a parent with child elements
			if ( jQuery.isFunction( value ) ) {
				return this.each(function(i) {
					var self = jQuery(this), old = self.html();
					self.replaceWith( value.call( this, i, old ) );
				});
			}

			if ( typeof value !== "string" ) {
				value = jQuery( value ).detach();
			}

			return this.each(function() {
				var next = this.nextSibling,
					parent = this.parentNode;

				jQuery( this ).remove();

				if ( next ) {
					jQuery(next).before( value );
				} else {
					jQuery(parent).append( value );
				}
			});
		}

		return this.length ?
			this.pushStack( jQuery(jQuery.isFunction(value) ? value() : value), "replaceWith", value ) :
			this;
	},

	detach: function( selector ) {
		return this.remove( selector, true );
	},

	domManip: function( args, table, callback ) {

		// Flatten any nested arrays
		args = [].concat.apply( [], args );

		var results, first, fragment, iNoClone,
			i = 0,
			value = args[0],
			scripts = [],
			l = this.length;

		// We can't cloneNode fragments that contain checked, in WebKit
		if ( !jQuery.support.checkClone && l > 1 && typeof value === "string" && rchecked.test( value ) ) {
			return this.each(function() {
				jQuery(this).domManip( args, table, callback );
			});
		}

		if ( jQuery.isFunction(value) ) {
			return this.each(function(i) {
				var self = jQuery(this);
				args[0] = value.call( this, i, table ? self.html() : undefined );
				self.domManip( args, table, callback );
			});
		}

		if ( this[0] ) {
			results = jQuery.buildFragment( args, this, scripts );
			fragment = results.fragment;
			first = fragment.firstChild;

			if ( fragment.childNodes.length === 1 ) {
				fragment = first;
			}

			if ( first ) {
				table = table && jQuery.nodeName( first, "tr" );

				// Use the original fragment for the last item instead of the first because it can end up
				// being emptied incorrectly in certain situations (#8070).
				// Fragments from the fragment cache must always be cloned and never used in place.
				for ( iNoClone = results.cacheable || l - 1; i < l; i++ ) {
					callback.call(
						table && jQuery.nodeName( this[i], "table" ) ?
							findOrAppend( this[i], "tbody" ) :
							this[i],
						i === iNoClone ?
							fragment :
							jQuery.clone( fragment, true, true )
					);
				}
			}

			// Fix #11809: Avoid leaking memory
			fragment = first = null;

			if ( scripts.length ) {
				jQuery.each( scripts, function( i, elem ) {
					if ( elem.src ) {
						if ( jQuery.ajax ) {
							jQuery.ajax({
								url: elem.src,
								type: "GET",
								dataType: "script",
								async: false,
								global: false,
								"throws": true
							});
						} else {
							jQuery.error("no ajax");
						}
					} else {
						jQuery.globalEval( ( elem.text || elem.textContent || elem.innerHTML || "" ).replace( rcleanScript, "" ) );
					}

					if ( elem.parentNode ) {
						elem.parentNode.removeChild( elem );
					}
				});
			}
		}

		return this;
	}
});

function findOrAppend( elem, tag ) {
	return elem.getElementsByTagName( tag )[0] || elem.appendChild( elem.ownerDocument.createElement( tag ) );
}

function cloneCopyEvent( src, dest ) {

	if ( dest.nodeType !== 1 || !jQuery.hasData( src ) ) {
		return;
	}

	var type, i, l,
		oldData = jQuery._data( src ),
		curData = jQuery._data( dest, oldData ),
		events = oldData.events;

	if ( events ) {
		delete curData.handle;
		curData.events = {};

		for ( type in events ) {
			for ( i = 0, l = events[ type ].length; i < l; i++ ) {
				jQuery.event.add( dest, type, events[ type ][ i ] );
			}
		}
	}

	// make the cloned public data object a copy from the original
	if ( curData.data ) {
		curData.data = jQuery.extend( {}, curData.data );
	}
}

function cloneFixAttributes( src, dest ) {
	var nodeName;

	// We do not need to do anything for non-Elements
	if ( dest.nodeType !== 1 ) {
		return;
	}

	// clearAttributes removes the attributes, which we don't want,
	// but also removes the attachEvent events, which we *do* want
	if ( dest.clearAttributes ) {
		dest.clearAttributes();
	}

	// mergeAttributes, in contrast, only merges back on the
	// original attributes, not the events
	if ( dest.mergeAttributes ) {
		dest.mergeAttributes( src );
	}

	nodeName = dest.nodeName.toLowerCase();

	if ( nodeName === "object" ) {
		// IE6-10 improperly clones children of object elements using classid.
		// IE10 throws NoModificationAllowedError if parent is null, #12132.
		if ( dest.parentNode ) {
			dest.outerHTML = src.outerHTML;
		}

		// This path appears unavoidable for IE9. When cloning an object
		// element in IE9, the outerHTML strategy above is not sufficient.
		// If the src has innerHTML and the destination does not,
		// copy the src.innerHTML into the dest.innerHTML. #10324
		if ( jQuery.support.html5Clone && (src.innerHTML && !jQuery.trim(dest.innerHTML)) ) {
			dest.innerHTML = src.innerHTML;
		}

	} else if ( nodeName === "input" && rcheckableType.test( src.type ) ) {
		// IE6-8 fails to persist the checked state of a cloned checkbox
		// or radio button. Worse, IE6-7 fail to give the cloned element
		// a checked appearance if the defaultChecked value isn't also set

		dest.defaultChecked = dest.checked = src.checked;

		// IE6-7 get confused and end up setting the value of a cloned
		// checkbox/radio button to an empty string instead of "on"
		if ( dest.value !== src.value ) {
			dest.value = src.value;
		}

	// IE6-8 fails to return the selected option to the default selected
	// state when cloning options
	} else if ( nodeName === "option" ) {
		dest.selected = src.defaultSelected;

	// IE6-8 fails to set the defaultValue to the correct value when
	// cloning other types of input fields
	} else if ( nodeName === "input" || nodeName === "textarea" ) {
		dest.defaultValue = src.defaultValue;

	// IE blanks contents when cloning scripts
	} else if ( nodeName === "script" && dest.text !== src.text ) {
		dest.text = src.text;
	}

	// Event data gets referenced instead of copied if the expando
	// gets copied too
	dest.removeAttribute( jQuery.expando );
}

jQuery.buildFragment = function( args, context, scripts ) {
	var fragment, cacheable, cachehit,
		first = args[ 0 ];

	// Set context from what may come in as undefined or a jQuery collection or a node
	// Updated to fix #12266 where accessing context[0] could throw an exception in IE9/10 &
	// also doubles as fix for #8950 where plain objects caused createDocumentFragment exception
	context = context || document;
	context = !context.nodeType && context[0] || context;
	context = context.ownerDocument || context;

	// Only cache "small" (1/2 KB) HTML strings that are associated with the main document
	// Cloning options loses the selected state, so don't cache them
	// IE 6 doesn't like it when you put <object> or <embed> elements in a fragment
	// Also, WebKit does not clone 'checked' attributes on cloneNode, so don't cache
	// Lastly, IE6,7,8 will not correctly reuse cached fragments that were created from unknown elems #10501
	if ( args.length === 1 && typeof first === "string" && first.length < 512 && context === document &&
		first.charAt(0) === "<" && !rnocache.test( first ) &&
		(jQuery.support.checkClone || !rchecked.test( first )) &&
		(jQuery.support.html5Clone || !rnoshimcache.test( first )) ) {

		// Mark cacheable and look for a hit
		cacheable = true;
		fragment = jQuery.fragments[ first ];
		cachehit = fragment !== undefined;
	}

	if ( !fragment ) {
		fragment = context.createDocumentFragment();
		jQuery.clean( args, context, fragment, scripts );

		// Update the cache, but only store false
		// unless this is a second parsing of the same content
		if ( cacheable ) {
			jQuery.fragments[ first ] = cachehit && fragment;
		}
	}

	return { fragment: fragment, cacheable: cacheable };
};

jQuery.fragments = {};

jQuery.each({
	appendTo: "append",
	prependTo: "prepend",
	insertBefore: "before",
	insertAfter: "after",
	replaceAll: "replaceWith"
}, function( name, original ) {
	jQuery.fn[ name ] = function( selector ) {
		var elems,
			i = 0,
			ret = [],
			insert = jQuery( selector ),
			l = insert.length,
			parent = this.length === 1 && this[0].parentNode;

		if ( (parent == null || parent && parent.nodeType === 11 && parent.childNodes.length === 1) && l === 1 ) {
			insert[ original ]( this[0] );
			return this;
		} else {
			for ( ; i < l; i++ ) {
				elems = ( i > 0 ? this.clone(true) : this ).get();
				jQuery( insert[i] )[ original ]( elems );
				ret = ret.concat( elems );
			}

			return this.pushStack( ret, name, insert.selector );
		}
	};
});

function getAll( elem ) {
	if ( typeof elem.getElementsByTagName !== "undefined" ) {
		return elem.getElementsByTagName( "*" );

	} else if ( typeof elem.querySelectorAll !== "undefined" ) {
		return elem.querySelectorAll( "*" );

	} else {
		return [];
	}
}

// Used in clean, fixes the defaultChecked property
function fixDefaultChecked( elem ) {
	if ( rcheckableType.test( elem.type ) ) {
		elem.defaultChecked = elem.checked;
	}
}

jQuery.extend({
	clone: function( elem, dataAndEvents, deepDataAndEvents ) {
		var srcElements,
			destElements,
			i,
			clone;

		if ( jQuery.support.html5Clone || jQuery.isXMLDoc(elem) || !rnoshimcache.test( "<" + elem.nodeName + ">" ) ) {
			clone = elem.cloneNode( true );

		// IE<=8 does not properly clone detached, unknown element nodes
		} else {
			fragmentDiv.innerHTML = elem.outerHTML;
			fragmentDiv.removeChild( clone = fragmentDiv.firstChild );
		}

		if ( (!jQuery.support.noCloneEvent || !jQuery.support.noCloneChecked) &&
				(elem.nodeType === 1 || elem.nodeType === 11) && !jQuery.isXMLDoc(elem) ) {
			// IE copies events bound via attachEvent when using cloneNode.
			// Calling detachEvent on the clone will also remove the events
			// from the original. In order to get around this, we use some
			// proprietary methods to clear the events. Thanks to MooTools
			// guys for this hotness.

			cloneFixAttributes( elem, clone );

			// Using Sizzle here is crazy slow, so we use getElementsByTagName instead
			srcElements = getAll( elem );
			destElements = getAll( clone );

			// Weird iteration because IE will replace the length property
			// with an element if you are cloning the body and one of the
			// elements on the page has a name or id of "length"
			for ( i = 0; srcElements[i]; ++i ) {
				// Ensure that the destination node is not null; Fixes #9587
				if ( destElements[i] ) {
					cloneFixAttributes( srcElements[i], destElements[i] );
				}
			}
		}

		// Copy the events from the original to the clone
		if ( dataAndEvents ) {
			cloneCopyEvent( elem, clone );

			if ( deepDataAndEvents ) {
				srcElements = getAll( elem );
				destElements = getAll( clone );

				for ( i = 0; srcElements[i]; ++i ) {
					cloneCopyEvent( srcElements[i], destElements[i] );
				}
			}
		}

		srcElements = destElements = null;

		// Return the cloned set
		return clone;
	},

	clean: function( elems, context, fragment, scripts ) {
		var i, j, elem, tag, wrap, depth, div, hasBody, tbody, len, handleScript, jsTags,
			safe = context === document && safeFragment,
			ret = [];

		// Ensure that context is a document
		if ( !context || typeof context.createDocumentFragment === "undefined" ) {
			context = document;
		}

		// Use the already-created safe fragment if context permits
		for ( i = 0; (elem = elems[i]) != null; i++ ) {
			if ( typeof elem === "number" ) {
				elem += "";
			}

			if ( !elem ) {
				continue;
			}

			// Convert html string into DOM nodes
			if ( typeof elem === "string" ) {
				if ( !rhtml.test( elem ) ) {
					elem = context.createTextNode( elem );
				} else {
					// Ensure a safe container in which to render the html
					safe = safe || createSafeFragment( context );
					div = context.createElement("div");
					safe.appendChild( div );

					// Fix "XHTML"-style tags in all browsers
					elem = elem.replace(rxhtmlTag, "<$1></$2>");

					// Go to html and back, then peel off extra wrappers
					tag = ( rtagName.exec( elem ) || ["", ""] )[1].toLowerCase();
					wrap = wrapMap[ tag ] || wrapMap._default;
					depth = wrap[0];
					div.innerHTML = wrap[1] + elem + wrap[2];

					// Move to the right depth
					while ( depth-- ) {
						div = div.lastChild;
					}

					// Remove IE's autoinserted <tbody> from table fragments
					if ( !jQuery.support.tbody ) {

						// String was a <table>, *may* have spurious <tbody>
						hasBody = rtbody.test(elem);
							tbody = tag === "table" && !hasBody ?
								div.firstChild && div.firstChild.childNodes :

								// String was a bare <thead> or <tfoot>
								wrap[1] === "<table>" && !hasBody ?
									div.childNodes :
									[];

						for ( j = tbody.length - 1; j >= 0 ; --j ) {
							if ( jQuery.nodeName( tbody[ j ], "tbody" ) && !tbody[ j ].childNodes.length ) {
								tbody[ j ].parentNode.removeChild( tbody[ j ] );
							}
						}
					}

					// IE completely kills leading whitespace when innerHTML is used
					if ( !jQuery.support.leadingWhitespace && rleadingWhitespace.test( elem ) ) {
						div.insertBefore( context.createTextNode( rleadingWhitespace.exec(elem)[0] ), div.firstChild );
					}

					elem = div.childNodes;

					// Take out of fragment container (we need a fresh div each time)
					div.parentNode.removeChild( div );
				}
			}

			if ( elem.nodeType ) {
				ret.push( elem );
			} else {
				jQuery.merge( ret, elem );
			}
		}

		// Fix #11356: Clear elements from safeFragment
		if ( div ) {
			elem = div = safe = null;
		}

		// Reset defaultChecked for any radios and checkboxes
		// about to be appended to the DOM in IE 6/7 (#8060)
		if ( !jQuery.support.appendChecked ) {
			for ( i = 0; (elem = ret[i]) != null; i++ ) {
				if ( jQuery.nodeName( elem, "input" ) ) {
					fixDefaultChecked( elem );
				} else if ( typeof elem.getElementsByTagName !== "undefined" ) {
					jQuery.grep( elem.getElementsByTagName("input"), fixDefaultChecked );
				}
			}
		}

		// Append elements to a provided document fragment
		if ( fragment ) {
			// Special handling of each script element
			handleScript = function( elem ) {
				// Check if we consider it executable
				if ( !elem.type || rscriptType.test( elem.type ) ) {
					// Detach the script and store it in the scripts array (if provided) or the fragment
					// Return truthy to indicate that it has been handled
					return scripts ?
						scripts.push( elem.parentNode ? elem.parentNode.removeChild( elem ) : elem ) :
						fragment.appendChild( elem );
				}
			};

			for ( i = 0; (elem = ret[i]) != null; i++ ) {
				// Check if we're done after handling an executable script
				if ( !( jQuery.nodeName( elem, "script" ) && handleScript( elem ) ) ) {
					// Append to fragment and handle embedded scripts
					fragment.appendChild( elem );
					if ( typeof elem.getElementsByTagName !== "undefined" ) {
						// handleScript alters the DOM, so use jQuery.merge to ensure snapshot iteration
						jsTags = jQuery.grep( jQuery.merge( [], elem.getElementsByTagName("script") ), handleScript );

						// Splice the scripts into ret after their former ancestor and advance our index beyond them
						ret.splice.apply( ret, [i + 1, 0].concat( jsTags ) );
						i += jsTags.length;
					}
				}
			}
		}

		return ret;
	},

	cleanData: function( elems, /* internal */ acceptData ) {
		var data, id, elem, type,
			i = 0,
			internalKey = jQuery.expando,
			cache = jQuery.cache,
			deleteExpando = jQuery.support.deleteExpando,
			special = jQuery.event.special;

		for ( ; (elem = elems[i]) != null; i++ ) {

			if ( acceptData || jQuery.acceptData( elem ) ) {

				id = elem[ internalKey ];
				data = id && cache[ id ];

				if ( data ) {
					if ( data.events ) {
						for ( type in data.events ) {
							if ( special[ type ] ) {
								jQuery.event.remove( elem, type );

							// This is a shortcut to avoid jQuery.event.remove's overhead
							} else {
								jQuery.removeEvent( elem, type, data.handle );
							}
						}
					}

					// Remove cache only if it was not already removed by jQuery.event.remove
					if ( cache[ id ] ) {

						delete cache[ id ];

						// IE does not allow us to delete expando properties from nodes,
						// nor does it have a removeAttribute function on Document nodes;
						// we must handle all of these cases
						if ( deleteExpando ) {
							delete elem[ internalKey ];

						} else if ( elem.removeAttribute ) {
							elem.removeAttribute( internalKey );

						} else {
							elem[ internalKey ] = null;
						}

						jQuery.deletedIds.push( id );
					}
				}
			}
		}
	}
});
// Limit scope pollution from any deprecated API
(function() {

var matched, browser;

// Use of jQuery.browser is frowned upon.
// More details: http://api.jquery.com/jQuery.browser
// jQuery.uaMatch maintained for back-compat
jQuery.uaMatch = function( ua ) {
	ua = ua.toLowerCase();

	var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
		/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
		/(msie) ([\w.]+)/.exec( ua ) ||
		ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
		[];

	return {
		browser: match[ 1 ] || "",
		version: match[ 2 ] || "0"
	};
};

matched = jQuery.uaMatch( navigator.userAgent );
browser = {};

if ( matched.browser ) {
	browser[ matched.browser ] = true;
	browser.version = matched.version;
}

// Chrome is Webkit, but Webkit is also Safari.
if ( browser.chrome ) {
	browser.webkit = true;
} else if ( browser.webkit ) {
	browser.safari = true;
}

jQuery.browser = browser;

jQuery.sub = function() {
	function jQuerySub( selector, context ) {
		return new jQuerySub.fn.init( selector, context );
	}
	jQuery.extend( true, jQuerySub, this );
	jQuerySub.superclass = this;
	jQuerySub.fn = jQuerySub.prototype = this();
	jQuerySub.fn.constructor = jQuerySub;
	jQuerySub.sub = this.sub;
	jQuerySub.fn.init = function init( selector, context ) {
		if ( context && context instanceof jQuery && !(context instanceof jQuerySub) ) {
			context = jQuerySub( context );
		}

		return jQuery.fn.init.call( this, selector, context, rootjQuerySub );
	};
	jQuerySub.fn.init.prototype = jQuerySub.fn;
	var rootjQuerySub = jQuerySub(document);
	return jQuerySub;
};

})();
var curCSS, iframe, iframeDoc,
	ralpha = /alpha\([^)]*\)/i,
	ropacity = /opacity=([^)]*)/,
	rposition = /^(top|right|bottom|left)$/,
	// swappable if display is none or starts with table except "table", "table-cell", or "table-caption"
	// see here for display values: https://developer.mozilla.org/en-US/docs/CSS/display
	rdisplayswap = /^(none|table(?!-c[ea]).+)/,
	rmargin = /^margin/,
	rnumsplit = new RegExp( "^(" + core_pnum + ")(.*)$", "i" ),
	rnumnonpx = new RegExp( "^(" + core_pnum + ")(?!px)[a-z%]+$", "i" ),
	rrelNum = new RegExp( "^([-+])=(" + core_pnum + ")", "i" ),
	elemdisplay = {},

	cssShow = { position: "absolute", visibility: "hidden", display: "block" },
	cssNormalTransform = {
		letterSpacing: 0,
		fontWeight: 400
	},

	cssExpand = [ "Top", "Right", "Bottom", "Left" ],
	cssPrefixes = [ "Webkit", "O", "Moz", "ms" ],

	eventsToggle = jQuery.fn.toggle;

// return a css property mapped to a potentially vendor prefixed property
function vendorPropName( style, name ) {

	// shortcut for names that are not vendor prefixed
	if ( name in style ) {
		return name;
	}

	// check for vendor prefixed names
	var capName = name.charAt(0).toUpperCase() + name.slice(1),
		origName = name,
		i = cssPrefixes.length;

	while ( i-- ) {
		name = cssPrefixes[ i ] + capName;
		if ( name in style ) {
			return name;
		}
	}

	return origName;
}

function isHidden( elem, el ) {
	elem = el || elem;
	return jQuery.css( elem, "display" ) === "none" || !jQuery.contains( elem.ownerDocument, elem );
}

function showHide( elements, show ) {
	var elem, display,
		values = [],
		index = 0,
		length = elements.length;

	for ( ; index < length; index++ ) {
		elem = elements[ index ];
		if ( !elem.style ) {
			continue;
		}
		values[ index ] = jQuery._data( elem, "olddisplay" );
		if ( show ) {
			// Reset the inline display of this element to learn if it is
			// being hidden by cascaded rules or not
			if ( !values[ index ] && elem.style.display === "none" ) {
				elem.style.display = "";
			}

			// Set elements which have been overridden with display: none
			// in a stylesheet to whatever the default browser style is
			// for such an element
			if ( elem.style.display === "" && isHidden( elem ) ) {
				values[ index ] = jQuery._data( elem, "olddisplay", css_defaultDisplay(elem.nodeName) );
			}
		} else {
			display = curCSS( elem, "display" );

			if ( !values[ index ] && display !== "none" ) {
				jQuery._data( elem, "olddisplay", display );
			}
		}
	}

	// Set the display of most of the elements in a second loop
	// to avoid the constant reflow
	for ( index = 0; index < length; index++ ) {
		elem = elements[ index ];
		if ( !elem.style ) {
			continue;
		}
		if ( !show || elem.style.display === "none" || elem.style.display === "" ) {
			elem.style.display = show ? values[ index ] || "" : "none";
		}
	}

	return elements;
}

jQuery.fn.extend({
	css: function( name, value ) {
		return jQuery.access( this, function( elem, name, value ) {
			return value !== undefined ?
				jQuery.style( elem, name, value ) :
				jQuery.css( elem, name );
		}, name, value, arguments.length > 1 );
	},
	show: function() {
		return showHide( this, true );
	},
	hide: function() {
		return showHide( this );
	},
	toggle: function( state, fn2 ) {
		var bool = typeof state === "boolean";

		if ( jQuery.isFunction( state ) && jQuery.isFunction( fn2 ) ) {
			return eventsToggle.apply( this, arguments );
		}

		return this.each(function() {
			if ( bool ? state : isHidden( this ) ) {
				jQuery( this ).show();
			} else {
				jQuery( this ).hide();
			}
		});
	}
});

jQuery.extend({
	// Add in style property hooks for overriding the default
	// behavior of getting and setting a style property
	cssHooks: {
		opacity: {
			get: function( elem, computed ) {
				if ( computed ) {
					// We should always get a number back from opacity
					var ret = curCSS( elem, "opacity" );
					return ret === "" ? "1" : ret;

				}
			}
		}
	},

	// Exclude the following css properties to add px
	cssNumber: {
		"fillOpacity": true,
		"fontWeight": true,
		"lineHeight": true,
		"opacity": true,
		"orphans": true,
		"widows": true,
		"zIndex": true,
		"zoom": true
	},

	// Add in properties whose names you wish to fix before
	// setting or getting the value
	cssProps: {
		// normalize float css property
		"float": jQuery.support.cssFloat ? "cssFloat" : "styleFloat"
	},

	// Get and set the style property on a DOM Node
	style: function( elem, name, value, extra ) {
		// Don't set styles on text and comment nodes
		if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
			return;
		}

		// Make sure that we're working with the right name
		var ret, type, hooks,
			origName = jQuery.camelCase( name ),
			style = elem.style;

		name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( style, origName ) );

		// gets hook for the prefixed version
		// followed by the unprefixed version
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// Check if we're setting a value
		if ( value !== undefined ) {
			type = typeof value;

			// convert relative number strings (+= or -=) to relative numbers. #7345
			if ( type === "string" && (ret = rrelNum.exec( value )) ) {
				value = ( ret[1] + 1 ) * ret[2] + parseFloat( jQuery.css( elem, name ) );
				// Fixes bug #9237
				type = "number";
			}

			// Make sure that NaN and null values aren't set. See: #7116
			if ( value == null || type === "number" && isNaN( value ) ) {
				return;
			}

			// If a number was passed in, add 'px' to the (except for certain CSS properties)
			if ( type === "number" && !jQuery.cssNumber[ origName ] ) {
				value += "px";
			}

			// If a hook was provided, use that value, otherwise just set the specified value
			if ( !hooks || !("set" in hooks) || (value = hooks.set( elem, value, extra )) !== undefined ) {
				// Wrapped to prevent IE from throwing errors when 'invalid' values are provided
				// Fixes bug #5509
				try {
					style[ name ] = value;
				} catch(e) {}
			}

		} else {
			// If a hook was provided get the non-computed value from there
			if ( hooks && "get" in hooks && (ret = hooks.get( elem, false, extra )) !== undefined ) {
				return ret;
			}

			// Otherwise just get the value from the style object
			return style[ name ];
		}
	},

	css: function( elem, name, numeric, extra ) {
		var val, num, hooks,
			origName = jQuery.camelCase( name );

		// Make sure that we're working with the right name
		name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( elem.style, origName ) );

		// gets hook for the prefixed version
		// followed by the unprefixed version
		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];

		// If a hook was provided get the computed value from there
		if ( hooks && "get" in hooks ) {
			val = hooks.get( elem, true, extra );
		}

		// Otherwise, if a way to get the computed value exists, use that
		if ( val === undefined ) {
			val = curCSS( elem, name );
		}

		//convert "normal" to computed value
		if ( val === "normal" && name in cssNormalTransform ) {
			val = cssNormalTransform[ name ];
		}

		// Return, converting to number if forced or a qualifier was provided and val looks numeric
		if ( numeric || extra !== undefined ) {
			num = parseFloat( val );
			return numeric || jQuery.isNumeric( num ) ? num || 0 : val;
		}
		return val;
	},

	// A method for quickly swapping in/out CSS properties to get correct calculations
	swap: function( elem, options, callback ) {
		var ret, name,
			old = {};

		// Remember the old values, and insert the new ones
		for ( name in options ) {
			old[ name ] = elem.style[ name ];
			elem.style[ name ] = options[ name ];
		}

		ret = callback.call( elem );

		// Revert the old values
		for ( name in options ) {
			elem.style[ name ] = old[ name ];
		}

		return ret;
	}
});

// NOTE: To any future maintainer, we've window.getComputedStyle
// because jsdom on node.js will break without it.
if ( window.getComputedStyle ) {
	curCSS = function( elem, name ) {
		var ret, width, minWidth, maxWidth,
			computed = window.getComputedStyle( elem, null ),
			style = elem.style;

		if ( computed ) {

			ret = computed[ name ];
			if ( ret === "" && !jQuery.contains( elem.ownerDocument, elem ) ) {
				ret = jQuery.style( elem, name );
			}

			// A tribute to the "awesome hack by Dean Edwards"
			// Chrome < 17 and Safari 5.0 uses "computed value" instead of "used value" for margin-right
			// Safari 5.1.7 (at least) returns percentage for a larger set of values, but width seems to be reliably pixels
			// this is against the CSSOM draft spec: http://dev.w3.org/csswg/cssom/#resolved-values
			if ( rnumnonpx.test( ret ) && rmargin.test( name ) ) {
				width = style.width;
				minWidth = style.minWidth;
				maxWidth = style.maxWidth;

				style.minWidth = style.maxWidth = style.width = ret;
				ret = computed.width;

				style.width = width;
				style.minWidth = minWidth;
				style.maxWidth = maxWidth;
			}
		}

		return ret;
	};
} else if ( document.documentElement.currentStyle ) {
	curCSS = function( elem, name ) {
		var left, rsLeft,
			ret = elem.currentStyle && elem.currentStyle[ name ],
			style = elem.style;

		// Avoid setting ret to empty string here
		// so we don't default to auto
		if ( ret == null && style && style[ name ] ) {
			ret = style[ name ];
		}

		// From the awesome hack by Dean Edwards
		// http://erik.eae.net/archives/2007/07/27/18.54.15/#comment-102291

		// If we're not dealing with a regular pixel number
		// but a number that has a weird ending, we need to convert it to pixels
		// but not position css attributes, as those are proportional to the parent element instead
		// and we can't measure the parent instead because it might trigger a "stacking dolls" problem
		if ( rnumnonpx.test( ret ) && !rposition.test( name ) ) {

			// Remember the original values
			left = style.left;
			rsLeft = elem.runtimeStyle && elem.runtimeStyle.left;

			// Put in the new values to get a computed value out
			if ( rsLeft ) {
				elem.runtimeStyle.left = elem.currentStyle.left;
			}
			style.left = name === "fontSize" ? "1em" : ret;
			ret = style.pixelLeft + "px";

			// Revert the changed values
			style.left = left;
			if ( rsLeft ) {
				elem.runtimeStyle.left = rsLeft;
			}
		}

		return ret === "" ? "auto" : ret;
	};
}

function setPositiveNumber( elem, value, subtract ) {
	var matches = rnumsplit.exec( value );
	return matches ?
			Math.max( 0, matches[ 1 ] - ( subtract || 0 ) ) + ( matches[ 2 ] || "px" ) :
			value;
}

function augmentWidthOrHeight( elem, name, extra, isBorderBox ) {
	var i = extra === ( isBorderBox ? "border" : "content" ) ?
		// If we already have the right measurement, avoid augmentation
		4 :
		// Otherwise initialize for horizontal or vertical properties
		name === "width" ? 1 : 0,

		val = 0;

	for ( ; i < 4; i += 2 ) {
		// both box models exclude margin, so add it if we want it
		if ( extra === "margin" ) {
			// we use jQuery.css instead of curCSS here
			// because of the reliableMarginRight CSS hook!
			val += jQuery.css( elem, extra + cssExpand[ i ], true );
		}

		// From this point on we use curCSS for maximum performance (relevant in animations)
		if ( isBorderBox ) {
			// border-box includes padding, so remove it if we want content
			if ( extra === "content" ) {
				val -= parseFloat( curCSS( elem, "padding" + cssExpand[ i ] ) ) || 0;
			}

			// at this point, extra isn't border nor margin, so remove border
			if ( extra !== "margin" ) {
				val -= parseFloat( curCSS( elem, "border" + cssExpand[ i ] + "Width" ) ) || 0;
			}
		} else {
			// at this point, extra isn't content, so add padding
			val += parseFloat( curCSS( elem, "padding" + cssExpand[ i ] ) ) || 0;

			// at this point, extra isn't content nor padding, so add border
			if ( extra !== "padding" ) {
				val += parseFloat( curCSS( elem, "border" + cssExpand[ i ] + "Width" ) ) || 0;
			}
		}
	}

	return val;
}

function getWidthOrHeight( elem, name, extra ) {

	// Start with offset property, which is equivalent to the border-box value
	var val = name === "width" ? elem.offsetWidth : elem.offsetHeight,
		valueIsBorderBox = true,
		isBorderBox = jQuery.support.boxSizing && jQuery.css( elem, "boxSizing" ) === "border-box";

	// some non-html elements return undefined for offsetWidth, so check for null/undefined
	// svg - https://bugzilla.mozilla.org/show_bug.cgi?id=649285
	// MathML - https://bugzilla.mozilla.org/show_bug.cgi?id=491668
	if ( val <= 0 || val == null ) {
		// Fall back to computed then uncomputed css if necessary
		val = curCSS( elem, name );
		if ( val < 0 || val == null ) {
			val = elem.style[ name ];
		}

		// Computed unit is not pixels. Stop here and return.
		if ( rnumnonpx.test(val) ) {
			return val;
		}

		// we need the check for style in case a browser which returns unreliable values
		// for getComputedStyle silently falls back to the reliable elem.style
		valueIsBorderBox = isBorderBox && ( jQuery.support.boxSizingReliable || val === elem.style[ name ] );

		// Normalize "", auto, and prepare for extra
		val = parseFloat( val ) || 0;
	}

	// use the active box-sizing model to add/subtract irrelevant styles
	return ( val +
		augmentWidthOrHeight(
			elem,
			name,
			extra || ( isBorderBox ? "border" : "content" ),
			valueIsBorderBox
		)
	) + "px";
}


// Try to determine the default display value of an element
function css_defaultDisplay( nodeName ) {
	if ( elemdisplay[ nodeName ] ) {
		return elemdisplay[ nodeName ];
	}

	var elem = jQuery( "<" + nodeName + ">" ).appendTo( document.body ),
		display = elem.css("display");
	elem.remove();

	// If the simple way fails,
	// get element's real default display by attaching it to a temp iframe
	if ( display === "none" || display === "" ) {
		// Use the already-created iframe if possible
		iframe = document.body.appendChild(
			iframe || jQuery.extend( document.createElement("iframe"), {
				frameBorder: 0,
				width: 0,
				height: 0
			})
		);

		// Create a cacheable copy of the iframe document on first call.
		// IE and Opera will allow us to reuse the iframeDoc without re-writing the fake HTML
		// document to it; WebKit & Firefox won't allow reusing the iframe document.
		if ( !iframeDoc || !iframe.createElement ) {
			iframeDoc = ( iframe.contentWindow || iframe.contentDocument ).document;
			iframeDoc.write("<!doctype html><html><body>");
			iframeDoc.close();
		}

		elem = iframeDoc.body.appendChild( iframeDoc.createElement(nodeName) );

		display = curCSS( elem, "display" );
		document.body.removeChild( iframe );
	}

	// Store the correct default display
	elemdisplay[ nodeName ] = display;

	return display;
}

jQuery.each([ "height", "width" ], function( i, name ) {
	jQuery.cssHooks[ name ] = {
		get: function( elem, computed, extra ) {
			if ( computed ) {
				// certain elements can have dimension info if we invisibly show them
				// however, it must have a current display style that would benefit from this
				if ( elem.offsetWidth === 0 && rdisplayswap.test( curCSS( elem, "display" ) ) ) {
					return jQuery.swap( elem, cssShow, function() {
						return getWidthOrHeight( elem, name, extra );
					});
				} else {
					return getWidthOrHeight( elem, name, extra );
				}
			}
		},

		set: function( elem, value, extra ) {
			return setPositiveNumber( elem, value, extra ?
				augmentWidthOrHeight(
					elem,
					name,
					extra,
					jQuery.support.boxSizing && jQuery.css( elem, "boxSizing" ) === "border-box"
				) : 0
			);
		}
	};
});

if ( !jQuery.support.opacity ) {
	jQuery.cssHooks.opacity = {
		get: function( elem, computed ) {
			// IE uses filters for opacity
			return ropacity.test( (computed && elem.currentStyle ? elem.currentStyle.filter : elem.style.filter) || "" ) ?
				( 0.01 * parseFloat( RegExp.$1 ) ) + "" :
				computed ? "1" : "";
		},

		set: function( elem, value ) {
			var style = elem.style,
				currentStyle = elem.currentStyle,
				opacity = jQuery.isNumeric( value ) ? "alpha(opacity=" + value * 100 + ")" : "",
				filter = currentStyle && currentStyle.filter || style.filter || "";

			// IE has trouble with opacity if it does not have layout
			// Force it by setting the zoom level
			style.zoom = 1;

			// if setting opacity to 1, and no other filters exist - attempt to remove filter attribute #6652
			if ( value >= 1 && jQuery.trim( filter.replace( ralpha, "" ) ) === "" &&
				style.removeAttribute ) {

				// Setting style.filter to null, "" & " " still leave "filter:" in the cssText
				// if "filter:" is present at all, clearType is disabled, we want to avoid this
				// style.removeAttribute is IE Only, but so apparently is this code path...
				style.removeAttribute( "filter" );

				// if there there is no filter style applied in a css rule, we are done
				if ( currentStyle && !currentStyle.filter ) {
					return;
				}
			}

			// otherwise, set new filter values
			style.filter = ralpha.test( filter ) ?
				filter.replace( ralpha, opacity ) :
				filter + " " + opacity;
		}
	};
}

// These hooks cannot be added until DOM ready because the support test
// for it is not run until after DOM ready
jQuery(function() {
	if ( !jQuery.support.reliableMarginRight ) {
		jQuery.cssHooks.marginRight = {
			get: function( elem, computed ) {
				// WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
				// Work around by temporarily setting element display to inline-block
				return jQuery.swap( elem, { "display": "inline-block" }, function() {
					if ( computed ) {
						return curCSS( elem, "marginRight" );
					}
				});
			}
		};
	}

	// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
	// getComputedStyle returns percent when specified for top/left/bottom/right
	// rather than make the css module depend on the offset module, we just check for it here
	if ( !jQuery.support.pixelPosition && jQuery.fn.position ) {
		jQuery.each( [ "top", "left" ], function( i, prop ) {
			jQuery.cssHooks[ prop ] = {
				get: function( elem, computed ) {
					if ( computed ) {
						var ret = curCSS( elem, prop );
						// if curCSS returns percentage, fallback to offset
						return rnumnonpx.test( ret ) ? jQuery( elem ).position()[ prop ] + "px" : ret;
					}
				}
			};
		});
	}

});

if ( jQuery.expr && jQuery.expr.filters ) {
	jQuery.expr.filters.hidden = function( elem ) {
		return ( elem.offsetWidth === 0 && elem.offsetHeight === 0 ) || (!jQuery.support.reliableHiddenOffsets && ((elem.style && elem.style.display) || curCSS( elem, "display" )) === "none");
	};

	jQuery.expr.filters.visible = function( elem ) {
		return !jQuery.expr.filters.hidden( elem );
	};
}

// These hooks are used by animate to expand properties
jQuery.each({
	margin: "",
	padding: "",
	border: "Width"
}, function( prefix, suffix ) {
	jQuery.cssHooks[ prefix + suffix ] = {
		expand: function( value ) {
			var i,

				// assumes a single number if not a string
				parts = typeof value === "string" ? value.split(" ") : [ value ],
				expanded = {};

			for ( i = 0; i < 4; i++ ) {
				expanded[ prefix + cssExpand[ i ] + suffix ] =
					parts[ i ] || parts[ i - 2 ] || parts[ 0 ];
			}

			return expanded;
		}
	};

	if ( !rmargin.test( prefix ) ) {
		jQuery.cssHooks[ prefix + suffix ].set = setPositiveNumber;
	}
});
var r20 = /%20/g,
	rbracket = /\[\]$/,
	rCRLF = /\r?\n/g,
	rinput = /^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,
	rselectTextarea = /^(?:select|textarea)/i;

jQuery.fn.extend({
	serialize: function() {
		return jQuery.param( this.serializeArray() );
	},
	serializeArray: function() {
		return this.map(function(){
			return this.elements ? jQuery.makeArray( this.elements ) : this;
		})
		.filter(function(){
			return this.name && !this.disabled &&
				( this.checked || rselectTextarea.test( this.nodeName ) ||
					rinput.test( this.type ) );
		})
		.map(function( i, elem ){
			var val = jQuery( this ).val();

			return val == null ?
				null :
				jQuery.isArray( val ) ?
					jQuery.map( val, function( val, i ){
						return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
					}) :
					{ name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
		}).get();
	}
});

//Serialize an array of form elements or a set of
//key/values into a query string
jQuery.param = function( a, traditional ) {
	var prefix,
		s = [],
		add = function( key, value ) {
			// If value is a function, invoke it and return its value
			value = jQuery.isFunction( value ) ? value() : ( value == null ? "" : value );
			s[ s.length ] = encodeURIComponent( key ) + "=" + encodeURIComponent( value );
		};

	// Set traditional to true for jQuery <= 1.3.2 behavior.
	if ( traditional === undefined ) {
		traditional = jQuery.ajaxSettings && jQuery.ajaxSettings.traditional;
	}

	// If an array was passed in, assume that it is an array of form elements.
	if ( jQuery.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {
		// Serialize the form elements
		jQuery.each( a, function() {
			add( this.name, this.value );
		});

	} else {
		// If traditional, encode the "old" way (the way 1.3.2 or older
		// did it), otherwise encode params recursively.
		for ( prefix in a ) {
			buildParams( prefix, a[ prefix ], traditional, add );
		}
	}

	// Return the resulting serialization
	return s.join( "&" ).replace( r20, "+" );
};

function buildParams( prefix, obj, traditional, add ) {
	var name;

	if ( jQuery.isArray( obj ) ) {
		// Serialize array item.
		jQuery.each( obj, function( i, v ) {
			if ( traditional || rbracket.test( prefix ) ) {
				// Treat each array item as a scalar.
				add( prefix, v );

			} else {
				// If array item is non-scalar (array or object), encode its
				// numeric index to resolve deserialization ambiguity issues.
				// Note that rack (as of 1.0.0) can't currently deserialize
				// nested arrays properly, and attempting to do so may cause
				// a server error. Possible fixes are to modify rack's
				// deserialization algorithm or to provide an option or flag
				// to force array serialization to be shallow.
				buildParams( prefix + "[" + ( typeof v === "object" ? i : "" ) + "]", v, traditional, add );
			}
		});

	} else if ( !traditional && jQuery.type( obj ) === "object" ) {
		// Serialize object item.
		for ( name in obj ) {
			buildParams( prefix + "[" + name + "]", obj[ name ], traditional, add );
		}

	} else {
		// Serialize scalar item.
		add( prefix, obj );
	}
}
var // Document location
	ajaxLocation,
	// Document location segments
	ajaxLocParts,

	rhash = /#.*$/,
	rheaders = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg, // IE leaves an \r character at EOL
	// #7653, #8125, #8152: local protocol detection
	rlocalProtocol = /^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,
	rnoContent = /^(?:GET|HEAD)$/,
	rprotocol = /^\/\//,
	rquery = /\?/,
	rscript = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
	rts = /([?&])_=[^&]*/,
	rurl = /^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,

	// Keep a copy of the old load method
	_load = jQuery.fn.load,

	/* Prefilters
	 * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
	 * 2) These are called:
	 *    - BEFORE asking for a transport
	 *    - AFTER param serialization (s.data is a string if s.processData is true)
	 * 3) key is the dataType
	 * 4) the catchall symbol "*" can be used
	 * 5) execution will start with transport dataType and THEN continue down to "*" if needed
	 */
	prefilters = {},

	/* Transports bindings
	 * 1) key is the dataType
	 * 2) the catchall symbol "*" can be used
	 * 3) selection will start with transport dataType and THEN go to "*" if needed
	 */
	transports = {},

	// Avoid comment-prolog char sequence (#10098); must appease lint and evade compression
	allTypes = ["*/"] + ["*"];

// #8138, IE may throw an exception when accessing
// a field from window.location if document.domain has been set
try {
	ajaxLocation = location.href;
} catch( e ) {
	// Use the href attribute of an A element
	// since IE will modify it given document.location
	ajaxLocation = document.createElement( "a" );
	ajaxLocation.href = "";
	ajaxLocation = ajaxLocation.href;
}

// Segment location into parts
ajaxLocParts = rurl.exec( ajaxLocation.toLowerCase() ) || [];

// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
function addToPrefiltersOrTransports( structure ) {

	// dataTypeExpression is optional and defaults to "*"
	return function( dataTypeExpression, func ) {

		if ( typeof dataTypeExpression !== "string" ) {
			func = dataTypeExpression;
			dataTypeExpression = "*";
		}

		var dataType, list, placeBefore,
			dataTypes = dataTypeExpression.toLowerCase().split( core_rspace ),
			i = 0,
			length = dataTypes.length;

		if ( jQuery.isFunction( func ) ) {
			// For each dataType in the dataTypeExpression
			for ( ; i < length; i++ ) {
				dataType = dataTypes[ i ];
				// We control if we're asked to add before
				// any existing element
				placeBefore = /^\+/.test( dataType );
				if ( placeBefore ) {
					dataType = dataType.substr( 1 ) || "*";
				}
				list = structure[ dataType ] = structure[ dataType ] || [];
				// then we add to the structure accordingly
				list[ placeBefore ? "unshift" : "push" ]( func );
			}
		}
	};
}

// Base inspection function for prefilters and transports
function inspectPrefiltersOrTransports( structure, options, originalOptions, jqXHR,
		dataType /* internal */, inspected /* internal */ ) {

	dataType = dataType || options.dataTypes[ 0 ];
	inspected = inspected || {};

	inspected[ dataType ] = true;

	var selection,
		list = structure[ dataType ],
		i = 0,
		length = list ? list.length : 0,
		executeOnly = ( structure === prefilters );

	for ( ; i < length && ( executeOnly || !selection ); i++ ) {
		selection = list[ i ]( options, originalOptions, jqXHR );
		// If we got redirected to another dataType
		// we try there if executing only and not done already
		if ( typeof selection === "string" ) {
			if ( !executeOnly || inspected[ selection ] ) {
				selection = undefined;
			} else {
				options.dataTypes.unshift( selection );
				selection = inspectPrefiltersOrTransports(
						structure, options, originalOptions, jqXHR, selection, inspected );
			}
		}
	}
	// If we're only executing or nothing was selected
	// we try the catchall dataType if not done already
	if ( ( executeOnly || !selection ) && !inspected[ "*" ] ) {
		selection = inspectPrefiltersOrTransports(
				structure, options, originalOptions, jqXHR, "*", inspected );
	}
	// unnecessary when only executing (prefilters)
	// but it'll be ignored by the caller in that case
	return selection;
}

// A special extend for ajax options
// that takes "flat" options (not to be deep extended)
// Fixes #9887
function ajaxExtend( target, src ) {
	var key, deep,
		flatOptions = jQuery.ajaxSettings.flatOptions || {};
	for ( key in src ) {
		if ( src[ key ] !== undefined ) {
			( flatOptions[ key ] ? target : ( deep || ( deep = {} ) ) )[ key ] = src[ key ];
		}
	}
	if ( deep ) {
		jQuery.extend( true, target, deep );
	}
}

jQuery.fn.load = function( url, params, callback ) {
	if ( typeof url !== "string" && _load ) {
		return _load.apply( this, arguments );
	}

	// Don't do a request if no elements are being requested
	if ( !this.length ) {
		return this;
	}

	var selector, type, response,
		self = this,
		off = url.indexOf(" ");

	if ( off >= 0 ) {
		selector = url.slice( off, url.length );
		url = url.slice( 0, off );
	}

	// If it's a function
	if ( jQuery.isFunction( params ) ) {

		// We assume that it's the callback
		callback = params;
		params = undefined;

	// Otherwise, build a param string
	} else if ( params && typeof params === "object" ) {
		type = "POST";
	}

	// Request the remote document
	jQuery.ajax({
		url: url,

		// if "type" variable is undefined, then "GET" method will be used
		type: type,
		dataType: "html",
		data: params,
		complete: function( jqXHR, status ) {
			if ( callback ) {
				self.each( callback, response || [ jqXHR.responseText, status, jqXHR ] );
			}
		}
	}).done(function( responseText ) {

		// Save response for use in complete callback
		response = arguments;

		// See if a selector was specified
		self.html( selector ?

			// Create a dummy div to hold the results
			jQuery("<div>")

				// inject the contents of the document in, removing the scripts
				// to avoid any 'Permission Denied' errors in IE
				.append( responseText.replace( rscript, "" ) )

				// Locate the specified elements
				.find( selector ) :

			// If not, just inject the full result
			responseText );

	});

	return this;
};

// Attach a bunch of functions for handling common AJAX events
jQuery.each( "ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split( " " ), function( i, o ){
	jQuery.fn[ o ] = function( f ){
		return this.on( o, f );
	};
});

jQuery.each( [ "get", "post" ], function( i, method ) {
	jQuery[ method ] = function( url, data, callback, type ) {
		// shift arguments if data argument was omitted
		if ( jQuery.isFunction( data ) ) {
			type = type || callback;
			callback = data;
			data = undefined;
		}

		return jQuery.ajax({
			type: method,
			url: url,
			data: data,
			success: callback,
			dataType: type
		});
	};
});

jQuery.extend({

	getScript: function( url, callback ) {
		return jQuery.get( url, undefined, callback, "script" );
	},

	getJSON: function( url, data, callback ) {
		return jQuery.get( url, data, callback, "json" );
	},

	// Creates a full fledged settings object into target
	// with both ajaxSettings and settings fields.
	// If target is omitted, writes into ajaxSettings.
	ajaxSetup: function( target, settings ) {
		if ( settings ) {
			// Building a settings object
			ajaxExtend( target, jQuery.ajaxSettings );
		} else {
			// Extending ajaxSettings
			settings = target;
			target = jQuery.ajaxSettings;
		}
		ajaxExtend( target, settings );
		return target;
	},

	ajaxSettings: {
		url: ajaxLocation,
		isLocal: rlocalProtocol.test( ajaxLocParts[ 1 ] ),
		global: true,
		type: "GET",
		contentType: "application/x-www-form-urlencoded; charset=UTF-8",
		processData: true,
		async: true,
		/*
		timeout: 0,
		data: null,
		dataType: null,
		username: null,
		password: null,
		cache: null,
		throws: false,
		traditional: false,
		headers: {},
		*/

		accepts: {
			xml: "application/xml, text/xml",
			html: "text/html",
			text: "text/plain",
			json: "application/json, text/javascript",
			"*": allTypes
		},

		contents: {
			xml: /xml/,
			html: /html/,
			json: /json/
		},

		responseFields: {
			xml: "responseXML",
			text: "responseText"
		},

		// List of data converters
		// 1) key format is "source_type destination_type" (a single space in-between)
		// 2) the catchall symbol "*" can be used for source_type
		converters: {

			// Convert anything to text
			"* text": window.String,

			// Text to html (true = no transformation)
			"text html": true,

			// Evaluate text as a json expression
			"text json": jQuery.parseJSON,

			// Parse text as xml
			"text xml": jQuery.parseXML
		},

		// For options that shouldn't be deep extended:
		// you can add your own custom options here if
		// and when you create one that shouldn't be
		// deep extended (see ajaxExtend)
		flatOptions: {
			context: true,
			url: true
		}
	},

	ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
	ajaxTransport: addToPrefiltersOrTransports( transports ),

	// Main method
	ajax: function( url, options ) {

		// If url is an object, simulate pre-1.5 signature
		if ( typeof url === "object" ) {
			options = url;
			url = undefined;
		}

		// Force options to be an object
		options = options || {};

		var // ifModified key
			ifModifiedKey,
			// Response headers
			responseHeadersString,
			responseHeaders,
			// transport
			transport,
			// timeout handle
			timeoutTimer,
			// Cross-domain detection vars
			parts,
			// To know if global events are to be dispatched
			fireGlobals,
			// Loop variable
			i,
			// Create the final options object
			s = jQuery.ajaxSetup( {}, options ),
			// Callbacks context
			callbackContext = s.context || s,
			// Context for global events
			// It's the callbackContext if one was provided in the options
			// and if it's a DOM node or a jQuery collection
			globalEventContext = callbackContext !== s &&
				( callbackContext.nodeType || callbackContext instanceof jQuery ) ?
						jQuery( callbackContext ) : jQuery.event,
			// Deferreds
			deferred = jQuery.Deferred(),
			completeDeferred = jQuery.Callbacks( "once memory" ),
			// Status-dependent callbacks
			statusCode = s.statusCode || {},
			// Headers (they are sent all at once)
			requestHeaders = {},
			requestHeadersNames = {},
			// The jqXHR state
			state = 0,
			// Default abort message
			strAbort = "canceled",
			// Fake xhr
			jqXHR = {

				readyState: 0,

				// Caches the header
				setRequestHeader: function( name, value ) {
					if ( !state ) {
						var lname = name.toLowerCase();
						name = requestHeadersNames[ lname ] = requestHeadersNames[ lname ] || name;
						requestHeaders[ name ] = value;
					}
					return this;
				},

				// Raw string
				getAllResponseHeaders: function() {
					return state === 2 ? responseHeadersString : null;
				},

				// Builds headers hashtable if needed
				getResponseHeader: function( key ) {
					var match;
					if ( state === 2 ) {
						if ( !responseHeaders ) {
							responseHeaders = {};
							while( ( match = rheaders.exec( responseHeadersString ) ) ) {
								responseHeaders[ match[1].toLowerCase() ] = match[ 2 ];
							}
						}
						match = responseHeaders[ key.toLowerCase() ];
					}
					return match === undefined ? null : match;
				},

				// Overrides response content-type header
				overrideMimeType: function( type ) {
					if ( !state ) {
						s.mimeType = type;
					}
					return this;
				},

				// Cancel the request
				abort: function( statusText ) {
					statusText = statusText || strAbort;
					if ( transport ) {
						transport.abort( statusText );
					}
					done( 0, statusText );
					return this;
				}
			};

		// Callback for when everything is done
		// It is defined here because jslint complains if it is declared
		// at the end of the function (which would be more logical and readable)
		function done( status, nativeStatusText, responses, headers ) {
			var isSuccess, success, error, response, modified,
				statusText = nativeStatusText;

			// Called once
			if ( state === 2 ) {
				return;
			}

			// State is "done" now
			state = 2;

			// Clear timeout if it exists
			if ( timeoutTimer ) {
				clearTimeout( timeoutTimer );
			}

			// Dereference transport for early garbage collection
			// (no matter how long the jqXHR object will be used)
			transport = undefined;

			// Cache response headers
			responseHeadersString = headers || "";

			// Set readyState
			jqXHR.readyState = status > 0 ? 4 : 0;

			// Get response data
			if ( responses ) {
				response = ajaxHandleResponses( s, jqXHR, responses );
			}

			// If successful, handle type chaining
			if ( status >= 200 && status < 300 || status === 304 ) {

				// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
				if ( s.ifModified ) {

					modified = jqXHR.getResponseHeader("Last-Modified");
					if ( modified ) {
						jQuery.lastModified[ ifModifiedKey ] = modified;
					}
					modified = jqXHR.getResponseHeader("Etag");
					if ( modified ) {
						jQuery.etag[ ifModifiedKey ] = modified;
					}
				}

				// If not modified
				if ( status === 304 ) {

					statusText = "notmodified";
					isSuccess = true;

				// If we have data
				} else {

					isSuccess = ajaxConvert( s, response );
					statusText = isSuccess.state;
					success = isSuccess.data;
					error = isSuccess.error;
					isSuccess = !error;
				}
			} else {
				// We extract error from statusText
				// then normalize statusText and status for non-aborts
				error = statusText;
				if ( !statusText || status ) {
					statusText = "error";
					if ( status < 0 ) {
						status = 0;
					}
				}
			}

			// Set data for the fake xhr object
			jqXHR.status = status;
			jqXHR.statusText = "" + ( nativeStatusText || statusText );

			// Success/Error
			if ( isSuccess ) {
				deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
			} else {
				deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
			}

			// Status-dependent callbacks
			jqXHR.statusCode( statusCode );
			statusCode = undefined;

			if ( fireGlobals ) {
				globalEventContext.trigger( "ajax" + ( isSuccess ? "Success" : "Error" ),
						[ jqXHR, s, isSuccess ? success : error ] );
			}

			// Complete
			completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );

			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );
				// Handle the global AJAX counter
				if ( !( --jQuery.active ) ) {
					jQuery.event.trigger( "ajaxStop" );
				}
			}
		}

		// Attach deferreds
		deferred.promise( jqXHR );
		jqXHR.success = jqXHR.done;
		jqXHR.error = jqXHR.fail;
		jqXHR.complete = completeDeferred.add;

		// Status-dependent callbacks
		jqXHR.statusCode = function( map ) {
			if ( map ) {
				var tmp;
				if ( state < 2 ) {
					for ( tmp in map ) {
						statusCode[ tmp ] = [ statusCode[tmp], map[tmp] ];
					}
				} else {
					tmp = map[ jqXHR.status ];
					jqXHR.always( tmp );
				}
			}
			return this;
		};

		// Remove hash character (#7531: and string promotion)
		// Add protocol if not provided (#5866: IE7 issue with protocol-less urls)
		// We also use the url parameter if available
		s.url = ( ( url || s.url ) + "" ).replace( rhash, "" ).replace( rprotocol, ajaxLocParts[ 1 ] + "//" );

		// Extract dataTypes list
		s.dataTypes = jQuery.trim( s.dataType || "*" ).toLowerCase().split( core_rspace );

		// Determine if a cross-domain request is in order
		if ( s.crossDomain == null ) {
			parts = rurl.exec( s.url.toLowerCase() );
			s.crossDomain = !!( parts &&
				( parts[ 1 ] != ajaxLocParts[ 1 ] || parts[ 2 ] != ajaxLocParts[ 2 ] ||
					( parts[ 3 ] || ( parts[ 1 ] === "http:" ? 80 : 443 ) ) !=
						( ajaxLocParts[ 3 ] || ( ajaxLocParts[ 1 ] === "http:" ? 80 : 443 ) ) )
			);
		}

		// Convert data if not already a string
		if ( s.data && s.processData && typeof s.data !== "string" ) {
			s.data = jQuery.param( s.data, s.traditional );
		}

		// Apply prefilters
		inspectPrefiltersOrTransports( prefilters, s, options, jqXHR );

		// If request was aborted inside a prefilter, stop there
		if ( state === 2 ) {
			return jqXHR;
		}

		// We can fire global events as of now if asked to
		fireGlobals = s.global;

		// Uppercase the type
		s.type = s.type.toUpperCase();

		// Determine if request has content
		s.hasContent = !rnoContent.test( s.type );

		// Watch for a new set of requests
		if ( fireGlobals && jQuery.active++ === 0 ) {
			jQuery.event.trigger( "ajaxStart" );
		}

		// More options handling for requests with no content
		if ( !s.hasContent ) {

			// If data is available, append data to url
			if ( s.data ) {
				s.url += ( rquery.test( s.url ) ? "&" : "?" ) + s.data;
				// #9682: remove data so that it's not used in an eventual retry
				delete s.data;
			}

			// Get ifModifiedKey before adding the anti-cache parameter
			ifModifiedKey = s.url;

			// Add anti-cache in url if needed
			if ( s.cache === false ) {

				var ts = jQuery.now(),
					// try replacing _= if it is there
					ret = s.url.replace( rts, "$1_=" + ts );

				// if nothing was replaced, add timestamp to the end
				s.url = ret + ( ( ret === s.url ) ? ( rquery.test( s.url ) ? "&" : "?" ) + "_=" + ts : "" );
			}
		}

		// Set the correct header, if data is being sent
		if ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
			jqXHR.setRequestHeader( "Content-Type", s.contentType );
		}

		// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
		if ( s.ifModified ) {
			ifModifiedKey = ifModifiedKey || s.url;
			if ( jQuery.lastModified[ ifModifiedKey ] ) {
				jqXHR.setRequestHeader( "If-Modified-Since", jQuery.lastModified[ ifModifiedKey ] );
			}
			if ( jQuery.etag[ ifModifiedKey ] ) {
				jqXHR.setRequestHeader( "If-None-Match", jQuery.etag[ ifModifiedKey ] );
			}
		}

		// Set the Accepts header for the server, depending on the dataType
		jqXHR.setRequestHeader(
			"Accept",
			s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[0] ] ?
				s.accepts[ s.dataTypes[0] ] + ( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
				s.accepts[ "*" ]
		);

		// Check for headers option
		for ( i in s.headers ) {
			jqXHR.setRequestHeader( i, s.headers[ i ] );
		}

		// Allow custom headers/mimetypes and early abort
		if ( s.beforeSend && ( s.beforeSend.call( callbackContext, jqXHR, s ) === false || state === 2 ) ) {
				// Abort if not done already and return
				return jqXHR.abort();

		}

		// aborting is no longer a cancellation
		strAbort = "abort";

		// Install callbacks on deferreds
		for ( i in { success: 1, error: 1, complete: 1 } ) {
			jqXHR[ i ]( s[ i ] );
		}

		// Get transport
		transport = inspectPrefiltersOrTransports( transports, s, options, jqXHR );

		// If no transport, we auto-abort
		if ( !transport ) {
			done( -1, "No Transport" );
		} else {
			jqXHR.readyState = 1;
			// Send global event
			if ( fireGlobals ) {
				globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
			}
			// Timeout
			if ( s.async && s.timeout > 0 ) {
				timeoutTimer = setTimeout( function(){
					jqXHR.abort( "timeout" );
				}, s.timeout );
			}

			try {
				state = 1;
				transport.send( requestHeaders, done );
			} catch (e) {
				// Propagate exception as error if not done
				if ( state < 2 ) {
					done( -1, e );
				// Simply rethrow otherwise
				} else {
					throw e;
				}
			}
		}

		return jqXHR;
	},

	// Counter for holding the number of active queries
	active: 0,

	// Last-Modified header cache for next request
	lastModified: {},
	etag: {}

});

/* Handles responses to an ajax request:
 * - sets all responseXXX fields accordingly
 * - finds the right dataType (mediates between content-type and expected dataType)
 * - returns the corresponding response
 */
function ajaxHandleResponses( s, jqXHR, responses ) {

	var ct, type, finalDataType, firstDataType,
		contents = s.contents,
		dataTypes = s.dataTypes,
		responseFields = s.responseFields;

	// Fill responseXXX fields
	for ( type in responseFields ) {
		if ( type in responses ) {
			jqXHR[ responseFields[type] ] = responses[ type ];
		}
	}

	// Remove auto dataType and get content-type in the process
	while( dataTypes[ 0 ] === "*" ) {
		dataTypes.shift();
		if ( ct === undefined ) {
			ct = s.mimeType || jqXHR.getResponseHeader( "content-type" );
		}
	}

	// Check if we're dealing with a known content-type
	if ( ct ) {
		for ( type in contents ) {
			if ( contents[ type ] && contents[ type ].test( ct ) ) {
				dataTypes.unshift( type );
				break;
			}
		}
	}

	// Check to see if we have a response for the expected dataType
	if ( dataTypes[ 0 ] in responses ) {
		finalDataType = dataTypes[ 0 ];
	} else {
		// Try convertible dataTypes
		for ( type in responses ) {
			if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[0] ] ) {
				finalDataType = type;
				break;
			}
			if ( !firstDataType ) {
				firstDataType = type;
			}
		}
		// Or just use first one
		finalDataType = finalDataType || firstDataType;
	}

	// If we found a dataType
	// We add the dataType to the list if needed
	// and return the corresponding response
	if ( finalDataType ) {
		if ( finalDataType !== dataTypes[ 0 ] ) {
			dataTypes.unshift( finalDataType );
		}
		return responses[ finalDataType ];
	}
}

// Chain conversions given the request and the original response
function ajaxConvert( s, response ) {

	var conv, conv2, current, tmp,
		// Work with a copy of dataTypes in case we need to modify it for conversion
		dataTypes = s.dataTypes.slice(),
		prev = dataTypes[ 0 ],
		converters = {},
		i = 0;

	// Apply the dataFilter if provided
	if ( s.dataFilter ) {
		response = s.dataFilter( response, s.dataType );
	}

	// Create converters map with lowercased keys
	if ( dataTypes[ 1 ] ) {
		for ( conv in s.converters ) {
			converters[ conv.toLowerCase() ] = s.converters[ conv ];
		}
	}

	// Convert to each sequential dataType, tolerating list modification
	for ( ; (current = dataTypes[++i]); ) {

		// There's only work to do if current dataType is non-auto
		if ( current !== "*" ) {

			// Convert response if prev dataType is non-auto and differs from current
			if ( prev !== "*" && prev !== current ) {

				// Seek a direct converter
				conv = converters[ prev + " " + current ] || converters[ "* " + current ];

				// If none found, seek a pair
				if ( !conv ) {
					for ( conv2 in converters ) {

						// If conv2 outputs current
						tmp = conv2.split(" ");
						if ( tmp[ 1 ] === current ) {

							// If prev can be converted to accepted input
							conv = converters[ prev + " " + tmp[ 0 ] ] ||
								converters[ "* " + tmp[ 0 ] ];
							if ( conv ) {
								// Condense equivalence converters
								if ( conv === true ) {
									conv = converters[ conv2 ];

								// Otherwise, insert the intermediate dataType
								} else if ( converters[ conv2 ] !== true ) {
									current = tmp[ 0 ];
									dataTypes.splice( i--, 0, current );
								}

								break;
							}
						}
					}
				}

				// Apply converter (if not an equivalence)
				if ( conv !== true ) {

					// Unless errors are allowed to bubble, catch and return them
					if ( conv && s["throws"] ) {
						response = conv( response );
					} else {
						try {
							response = conv( response );
						} catch ( e ) {
							return { state: "parsererror", error: conv ? e : "No conversion from " + prev + " to " + current };
						}
					}
				}
			}

			// Update prev for next iteration
			prev = current;
		}
	}

	return { state: "success", data: response };
}
var oldCallbacks = [],
	rquestion = /\?/,
	rjsonp = /(=)\?(?=&|$)|\?\?/,
	nonce = jQuery.now();

// Default jsonp settings
jQuery.ajaxSetup({
	jsonp: "callback",
	jsonpCallback: function() {
		var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce++ ) );
		this[ callback ] = true;
		return callback;
	}
});

// Detect, normalize options and install callbacks for jsonp requests
jQuery.ajaxPrefilter( "json jsonp", function( s, originalSettings, jqXHR ) {

	var callbackName, overwritten, responseContainer,
		data = s.data,
		url = s.url,
		hasCallback = s.jsonp !== false,
		replaceInUrl = hasCallback && rjsonp.test( url ),
		replaceInData = hasCallback && !replaceInUrl && typeof data === "string" &&
			!( s.contentType || "" ).indexOf("application/x-www-form-urlencoded") &&
			rjsonp.test( data );

	// Handle iff the expected data type is "jsonp" or we have a parameter to set
	if ( s.dataTypes[ 0 ] === "jsonp" || replaceInUrl || replaceInData ) {

		// Get callback name, remembering preexisting value associated with it
		callbackName = s.jsonpCallback = jQuery.isFunction( s.jsonpCallback ) ?
			s.jsonpCallback() :
			s.jsonpCallback;
		overwritten = window[ callbackName ];

		// Insert callback into url or form data
		if ( replaceInUrl ) {
			s.url = url.replace( rjsonp, "$1" + callbackName );
		} else if ( replaceInData ) {
			s.data = data.replace( rjsonp, "$1" + callbackName );
		} else if ( hasCallback ) {
			s.url += ( rquestion.test( url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
		}

		// Use data converter to retrieve json after script execution
		s.converters["script json"] = function() {
			if ( !responseContainer ) {
				jQuery.error( callbackName + " was not called" );
			}
			return responseContainer[ 0 ];
		};

		// force json dataType
		s.dataTypes[ 0 ] = "json";

		// Install callback
		window[ callbackName ] = function() {
			responseContainer = arguments;
		};

		// Clean-up function (fires after converters)
		jqXHR.always(function() {
			// Restore preexisting value
			window[ callbackName ] = overwritten;

			// Save back as free
			if ( s[ callbackName ] ) {
				// make sure that re-using the options doesn't screw things around
				s.jsonpCallback = originalSettings.jsonpCallback;

				// save the callback name for future use
				oldCallbacks.push( callbackName );
			}

			// Call if it was a function and we have a response
			if ( responseContainer && jQuery.isFunction( overwritten ) ) {
				overwritten( responseContainer[ 0 ] );
			}

			responseContainer = overwritten = undefined;
		});

		// Delegate to script
		return "script";
	}
});
// Install script dataType
jQuery.ajaxSetup({
	accepts: {
		script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
	},
	contents: {
		script: /javascript|ecmascript/
	},
	converters: {
		"text script": function( text ) {
			jQuery.globalEval( text );
			return text;
		}
	}
});

// Handle cache's special case and global
jQuery.ajaxPrefilter( "script", function( s ) {
	if ( s.cache === undefined ) {
		s.cache = false;
	}
	if ( s.crossDomain ) {
		s.type = "GET";
		s.global = false;
	}
});

// Bind script tag hack transport
jQuery.ajaxTransport( "script", function(s) {

	// This transport only deals with cross domain requests
	if ( s.crossDomain ) {

		var script,
			head = document.head || document.getElementsByTagName( "head" )[0] || document.documentElement;

		return {

			send: function( _, callback ) {

				script = document.createElement( "script" );

				script.async = "async";

				if ( s.scriptCharset ) {
					script.charset = s.scriptCharset;
				}

				script.src = s.url;

				// Attach handlers for all browsers
				script.onload = script.onreadystatechange = function( _, isAbort ) {

					if ( isAbort || !script.readyState || /loaded|complete/.test( script.readyState ) ) {

						// Handle memory leak in IE
						script.onload = script.onreadystatechange = null;

						// Remove the script
						if ( head && script.parentNode ) {
							head.removeChild( script );
						}

						// Dereference the script
						script = undefined;

						// Callback if not abort
						if ( !isAbort ) {
							callback( 200, "success" );
						}
					}
				};
				// Use insertBefore instead of appendChild  to circumvent an IE6 bug.
				// This arises when a base node is used (#2709 and #4378).
				head.insertBefore( script, head.firstChild );
			},

			abort: function() {
				if ( script ) {
					script.onload( 0, 1 );
				}
			}
		};
	}
});
var xhrCallbacks,
	// #5280: Internet Explorer will keep connections alive if we don't abort on unload
	xhrOnUnloadAbort = window.ActiveXObject ? function() {
		// Abort all pending requests
		for ( var key in xhrCallbacks ) {
			xhrCallbacks[ key ]( 0, 1 );
		}
	} : false,
	xhrId = 0;

// Functions to create xhrs
function createStandardXHR() {
	try {
		return new window.XMLHttpRequest();
	} catch( e ) {}
}

function createActiveXHR() {
	try {
		return new window.ActiveXObject( "Microsoft.XMLHTTP" );
	} catch( e ) {}
}

// Create the request object
// (This is still attached to ajaxSettings for backward compatibility)
jQuery.ajaxSettings.xhr = window.ActiveXObject ?
	/* Microsoft failed to properly
	 * implement the XMLHttpRequest in IE7 (can't request local files),
	 * so we use the ActiveXObject when it is available
	 * Additionally XMLHttpRequest can be disabled in IE7/IE8 so
	 * we need a fallback.
	 */
	function() {
		return !this.isLocal && createStandardXHR() || createActiveXHR();
	} :
	// For all other browsers, use the standard XMLHttpRequest object
	createStandardXHR;

// Determine support properties
(function( xhr ) {
	jQuery.extend( jQuery.support, {
		ajax: !!xhr,
		cors: !!xhr && ( "withCredentials" in xhr )
	});
})( jQuery.ajaxSettings.xhr() );

// Create transport if the browser can provide an xhr
if ( jQuery.support.ajax ) {

	jQuery.ajaxTransport(function( s ) {
		// Cross domain only allowed if supported through XMLHttpRequest
		if ( !s.crossDomain || jQuery.support.cors ) {

			var callback;

			return {
				send: function( headers, complete ) {

					// Get a new xhr
					var handle, i,
						xhr = s.xhr();

					// Open the socket
					// Passing null username, generates a login popup on Opera (#2865)
					if ( s.username ) {
						xhr.open( s.type, s.url, s.async, s.username, s.password );
					} else {
						xhr.open( s.type, s.url, s.async );
					}

					// Apply custom fields if provided
					if ( s.xhrFields ) {
						for ( i in s.xhrFields ) {
							xhr[ i ] = s.xhrFields[ i ];
						}
					}

					// Override mime type if needed
					if ( s.mimeType && xhr.overrideMimeType ) {
						xhr.overrideMimeType( s.mimeType );
					}

					// X-Requested-With header
					// For cross-domain requests, seeing as conditions for a preflight are
					// akin to a jigsaw puzzle, we simply never set it to be sure.
					// (it can always be set on a per-request basis or even using ajaxSetup)
					// For same-domain requests, won't change header if already provided.
					if ( !s.crossDomain && !headers["X-Requested-With"] ) {
						headers[ "X-Requested-With" ] = "XMLHttpRequest";
					}

					// Need an extra try/catch for cross domain requests in Firefox 3
					try {
						for ( i in headers ) {
							xhr.setRequestHeader( i, headers[ i ] );
						}
					} catch( _ ) {}

					// Do send the request
					// This may raise an exception which is actually
					// handled in jQuery.ajax (so no try/catch here)
					xhr.send( ( s.hasContent && s.data ) || null );

					// Listener
					callback = function( _, isAbort ) {

						var status,
							statusText,
							responseHeaders,
							responses,
							xml;

						// Firefox throws exceptions when accessing properties
						// of an xhr when a network error occurred
						// http://helpful.knobs-dials.com/index.php/Component_returned_failure_code:_0x80040111_(NS_ERROR_NOT_AVAILABLE)
						try {

							// Was never called and is aborted or complete
							if ( callback && ( isAbort || xhr.readyState === 4 ) ) {

								// Only called once
								callback = undefined;

								// Do not keep as active anymore
								if ( handle ) {
									xhr.onreadystatechange = jQuery.noop;
									if ( xhrOnUnloadAbort ) {
										delete xhrCallbacks[ handle ];
									}
								}

								// If it's an abort
								if ( isAbort ) {
									// Abort it manually if needed
									if ( xhr.readyState !== 4 ) {
										xhr.abort();
									}
								} else {
									status = xhr.status;
									responseHeaders = xhr.getAllResponseHeaders();
									responses = {};
									xml = xhr.responseXML;

									// Construct response list
									if ( xml && xml.documentElement /* #4958 */ ) {
										responses.xml = xml;
									}

									// When requesting binary data, IE6-9 will throw an exception
									// on any attempt to access responseText (#11426)
									try {
										responses.text = xhr.responseText;
									} catch( _ ) {
									}

									// Firefox throws an exception when accessing
									// statusText for faulty cross-domain requests
									try {
										statusText = xhr.statusText;
									} catch( e ) {
										// We normalize with Webkit giving an empty statusText
										statusText = "";
									}

									// Filter status for non standard behaviors

									// If the request is local and we have data: assume a success
									// (success with no data won't get notified, that's the best we
									// can do given current implementations)
									if ( !status && s.isLocal && !s.crossDomain ) {
										status = responses.text ? 200 : 404;
									// IE - #1450: sometimes returns 1223 when it should be 204
									} else if ( status === 1223 ) {
										status = 204;
									}
								}
							}
						} catch( firefoxAccessException ) {
							if ( !isAbort ) {
								complete( -1, firefoxAccessException );
							}
						}

						// Call complete if needed
						if ( responses ) {
							complete( status, statusText, responses, responseHeaders );
						}
					};

					if ( !s.async ) {
						// if we're in sync mode we fire the callback
						callback();
					} else if ( xhr.readyState === 4 ) {
						// (IE6 & IE7) if it's in cache and has been
						// retrieved directly we need to fire the callback
						setTimeout( callback, 0 );
					} else {
						handle = ++xhrId;
						if ( xhrOnUnloadAbort ) {
							// Create the active xhrs callbacks list if needed
							// and attach the unload handler
							if ( !xhrCallbacks ) {
								xhrCallbacks = {};
								jQuery( window ).unload( xhrOnUnloadAbort );
							}
							// Add to list of active xhrs callbacks
							xhrCallbacks[ handle ] = callback;
						}
						xhr.onreadystatechange = callback;
					}
				},

				abort: function() {
					if ( callback ) {
						callback(0,1);
					}
				}
			};
		}
	});
}
var fxNow, timerId,
	rfxtypes = /^(?:toggle|show|hide)$/,
	rfxnum = new RegExp( "^(?:([-+])=|)(" + core_pnum + ")([a-z%]*)$", "i" ),
	rrun = /queueHooks$/,
	animationPrefilters = [ defaultPrefilter ],
	tweeners = {
		"*": [function( prop, value ) {
			var end, unit, prevScale,
				tween = this.createTween( prop, value ),
				parts = rfxnum.exec( value ),
				target = tween.cur(),
				start = +target || 0,
				scale = 1;

			if ( parts ) {
				end = +parts[2];
				unit = parts[3] || ( jQuery.cssNumber[ prop ] ? "" : "px" );

				// We need to compute starting value
				if ( unit !== "px" && start ) {
					// Iteratively approximate from a nonzero starting point
					// Prefer the current property, because this process will be trivial if it uses the same units
					// Fallback to end or a simple constant
					start = jQuery.css( tween.elem, prop, true ) || end || 1;

					do {
						// If previous iteration zeroed out, double until we get *something*
						// Use a string for doubling factor so we don't accidentally see scale as unchanged below
						prevScale = scale = scale || ".5";

						// Adjust and apply
						start = start / scale;
						jQuery.style( tween.elem, prop, start + unit );

						// Update scale, tolerating zeroes from tween.cur()
						scale = tween.cur() / target;

					// Stop looping if we've hit the mark or scale is unchanged
					} while ( scale !== 1 && scale !== prevScale );
				}

				tween.unit = unit;
				tween.start = start;
				// If a +=/-= token was provided, we're doing a relative animation
				tween.end = parts[1] ? start + ( parts[1] + 1 ) * end : end;
			}
			return tween;
		}]
	};

// Animations created synchronously will run synchronously
function createFxNow() {
	setTimeout(function() {
		fxNow = undefined;
	}, 0 );
	return ( fxNow = jQuery.now() );
}

function createTweens( animation, props ) {
	jQuery.each( props, function( prop, value ) {
		var collection = ( tweeners[ prop ] || [] ).concat( tweeners[ "*" ] ),
			index = 0,
			length = collection.length;
		for ( ; index < length; index++ ) {
			if ( collection[ index ].call( animation, prop, value ) ) {

				// we're done with this property
				return;
			}
		}
	});
}

function Animation( elem, properties, options ) {
	var result,
		index = 0,
		tweenerIndex = 0,
		length = animationPrefilters.length,
		deferred = jQuery.Deferred().always( function() {
			// don't match elem in the :animated selector
			delete tick.elem;
		}),
		tick = function() {
			var currentTime = fxNow || createFxNow(),
				remaining = Math.max( 0, animation.startTime + animation.duration - currentTime ),
				percent = 1 - ( remaining / animation.duration || 0 ),
				index = 0,
				length = animation.tweens.length;

			for ( ; index < length ; index++ ) {
				animation.tweens[ index ].run( percent );
			}

			deferred.notifyWith( elem, [ animation, percent, remaining ]);

			if ( percent < 1 && length ) {
				return remaining;
			} else {
				deferred.resolveWith( elem, [ animation ] );
				return false;
			}
		},
		animation = deferred.promise({
			elem: elem,
			props: jQuery.extend( {}, properties ),
			opts: jQuery.extend( true, { specialEasing: {} }, options ),
			originalProperties: properties,
			originalOptions: options,
			startTime: fxNow || createFxNow(),
			duration: options.duration,
			tweens: [],
			createTween: function( prop, end, easing ) {
				var tween = jQuery.Tween( elem, animation.opts, prop, end,
						animation.opts.specialEasing[ prop ] || animation.opts.easing );
				animation.tweens.push( tween );
				return tween;
			},
			stop: function( gotoEnd ) {
				var index = 0,
					// if we are going to the end, we want to run all the tweens
					// otherwise we skip this part
					length = gotoEnd ? animation.tweens.length : 0;

				for ( ; index < length ; index++ ) {
					animation.tweens[ index ].run( 1 );
				}

				// resolve when we played the last frame
				// otherwise, reject
				if ( gotoEnd ) {
					deferred.resolveWith( elem, [ animation, gotoEnd ] );
				} else {
					deferred.rejectWith( elem, [ animation, gotoEnd ] );
				}
				return this;
			}
		}),
		props = animation.props;

	propFilter( props, animation.opts.specialEasing );

	for ( ; index < length ; index++ ) {
		result = animationPrefilters[ index ].call( animation, elem, props, animation.opts );
		if ( result ) {
			return result;
		}
	}

	createTweens( animation, props );

	if ( jQuery.isFunction( animation.opts.start ) ) {
		animation.opts.start.call( elem, animation );
	}

	jQuery.fx.timer(
		jQuery.extend( tick, {
			anim: animation,
			queue: animation.opts.queue,
			elem: elem
		})
	);

	// attach callbacks from options
	return animation.progress( animation.opts.progress )
		.done( animation.opts.done, animation.opts.complete )
		.fail( animation.opts.fail )
		.always( animation.opts.always );
}

function propFilter( props, specialEasing ) {
	var index, name, easing, value, hooks;

	// camelCase, specialEasing and expand cssHook pass
	for ( index in props ) {
		name = jQuery.camelCase( index );
		easing = specialEasing[ name ];
		value = props[ index ];
		if ( jQuery.isArray( value ) ) {
			easing = value[ 1 ];
			value = props[ index ] = value[ 0 ];
		}

		if ( index !== name ) {
			props[ name ] = value;
			delete props[ index ];
		}

		hooks = jQuery.cssHooks[ name ];
		if ( hooks && "expand" in hooks ) {
			value = hooks.expand( value );
			delete props[ name ];

			// not quite $.extend, this wont overwrite keys already present.
			// also - reusing 'index' from above because we have the correct "name"
			for ( index in value ) {
				if ( !( index in props ) ) {
					props[ index ] = value[ index ];
					specialEasing[ index ] = easing;
				}
			}
		} else {
			specialEasing[ name ] = easing;
		}
	}
}

jQuery.Animation = jQuery.extend( Animation, {

	tweener: function( props, callback ) {
		if ( jQuery.isFunction( props ) ) {
			callback = props;
			props = [ "*" ];
		} else {
			props = props.split(" ");
		}

		var prop,
			index = 0,
			length = props.length;

		for ( ; index < length ; index++ ) {
			prop = props[ index ];
			tweeners[ prop ] = tweeners[ prop ] || [];
			tweeners[ prop ].unshift( callback );
		}
	},

	prefilter: function( callback, prepend ) {
		if ( prepend ) {
			animationPrefilters.unshift( callback );
		} else {
			animationPrefilters.push( callback );
		}
	}
});

function defaultPrefilter( elem, props, opts ) {
	var index, prop, value, length, dataShow, tween, hooks, oldfire,
		anim = this,
		style = elem.style,
		orig = {},
		handled = [],
		hidden = elem.nodeType && isHidden( elem );

	// handle queue: false promises
	if ( !opts.queue ) {
		hooks = jQuery._queueHooks( elem, "fx" );
		if ( hooks.unqueued == null ) {
			hooks.unqueued = 0;
			oldfire = hooks.empty.fire;
			hooks.empty.fire = function() {
				if ( !hooks.unqueued ) {
					oldfire();
				}
			};
		}
		hooks.unqueued++;

		anim.always(function() {
			// doing this makes sure that the complete handler will be called
			// before this completes
			anim.always(function() {
				hooks.unqueued--;
				if ( !jQuery.queue( elem, "fx" ).length ) {
					hooks.empty.fire();
				}
			});
		});
	}

	// height/width overflow pass
	if ( elem.nodeType === 1 && ( "height" in props || "width" in props ) ) {
		// Make sure that nothing sneaks out
		// Record all 3 overflow attributes because IE does not
		// change the overflow attribute when overflowX and
		// overflowY are set to the same value
		opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];

		// Set display property to inline-block for height/width
		// animations on inline elements that are having width/height animated
		if ( jQuery.css( elem, "display" ) === "inline" &&
				jQuery.css( elem, "float" ) === "none" ) {

			// inline-level elements accept inline-block;
			// block-level elements need to be inline with layout
			if ( !jQuery.support.inlineBlockNeedsLayout || css_defaultDisplay( elem.nodeName ) === "inline" ) {
				style.display = "inline-block";

			} else {
				style.zoom = 1;
			}
		}
	}

	if ( opts.overflow ) {
		style.overflow = "hidden";
		if ( !jQuery.support.shrinkWrapBlocks ) {
			anim.done(function() {
				style.overflow = opts.overflow[ 0 ];
				style.overflowX = opts.overflow[ 1 ];
				style.overflowY = opts.overflow[ 2 ];
			});
		}
	}


	// show/hide pass
	for ( index in props ) {
		value = props[ index ];
		if ( rfxtypes.exec( value ) ) {
			delete props[ index ];
			if ( value === ( hidden ? "hide" : "show" ) ) {
				continue;
			}
			handled.push( index );
		}
	}

	length = handled.length;
	if ( length ) {
		dataShow = jQuery._data( elem, "fxshow" ) || jQuery._data( elem, "fxshow", {} );
		if ( hidden ) {
			jQuery( elem ).show();
		} else {
			anim.done(function() {
				jQuery( elem ).hide();
			});
		}
		anim.done(function() {
			var prop;
			jQuery.removeData( elem, "fxshow", true );
			for ( prop in orig ) {
				jQuery.style( elem, prop, orig[ prop ] );
			}
		});
		for ( index = 0 ; index < length ; index++ ) {
			prop = handled[ index ];
			tween = anim.createTween( prop, hidden ? dataShow[ prop ] : 0 );
			orig[ prop ] = dataShow[ prop ] || jQuery.style( elem, prop );

			if ( !( prop in dataShow ) ) {
				dataShow[ prop ] = tween.start;
				if ( hidden ) {
					tween.end = tween.start;
					tween.start = prop === "width" || prop === "height" ? 1 : 0;
				}
			}
		}
	}
}

function Tween( elem, options, prop, end, easing ) {
	return new Tween.prototype.init( elem, options, prop, end, easing );
}
jQuery.Tween = Tween;

Tween.prototype = {
	constructor: Tween,
	init: function( elem, options, prop, end, easing, unit ) {
		this.elem = elem;
		this.prop = prop;
		this.easing = easing || "swing";
		this.options = options;
		this.start = this.now = this.cur();
		this.end = end;
		this.unit = unit || ( jQuery.cssNumber[ prop ] ? "" : "px" );
	},
	cur: function() {
		var hooks = Tween.propHooks[ this.prop ];

		return hooks && hooks.get ?
			hooks.get( this ) :
			Tween.propHooks._default.get( this );
	},
	run: function( percent ) {
		var eased,
			hooks = Tween.propHooks[ this.prop ];

		if ( this.options.duration ) {
			this.pos = eased = jQuery.easing[ this.easing ](
				percent, this.options.duration * percent, 0, 1, this.options.duration
			);
		} else {
			this.pos = eased = percent;
		}
		this.now = ( this.end - this.start ) * eased + this.start;

		if ( this.options.step ) {
			this.options.step.call( this.elem, this.now, this );
		}

		if ( hooks && hooks.set ) {
			hooks.set( this );
		} else {
			Tween.propHooks._default.set( this );
		}
		return this;
	}
};

Tween.prototype.init.prototype = Tween.prototype;

Tween.propHooks = {
	_default: {
		get: function( tween ) {
			var result;

			if ( tween.elem[ tween.prop ] != null &&
				(!tween.elem.style || tween.elem.style[ tween.prop ] == null) ) {
				return tween.elem[ tween.prop ];
			}

			// passing any value as a 4th parameter to .css will automatically
			// attempt a parseFloat and fallback to a string if the parse fails
			// so, simple values such as "10px" are parsed to Float.
			// complex values such as "rotate(1rad)" are returned as is.
			result = jQuery.css( tween.elem, tween.prop, false, "" );
			// Empty strings, null, undefined and "auto" are converted to 0.
			return !result || result === "auto" ? 0 : result;
		},
		set: function( tween ) {
			// use step hook for back compat - use cssHook if its there - use .style if its
			// available and use plain properties where available
			if ( jQuery.fx.step[ tween.prop ] ) {
				jQuery.fx.step[ tween.prop ]( tween );
			} else if ( tween.elem.style && ( tween.elem.style[ jQuery.cssProps[ tween.prop ] ] != null || jQuery.cssHooks[ tween.prop ] ) ) {
				jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
			} else {
				tween.elem[ tween.prop ] = tween.now;
			}
		}
	}
};

// Remove in 2.0 - this supports IE8's panic based approach
// to setting things on disconnected nodes

Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
	set: function( tween ) {
		if ( tween.elem.nodeType && tween.elem.parentNode ) {
			tween.elem[ tween.prop ] = tween.now;
		}
	}
};

jQuery.each([ "toggle", "show", "hide" ], function( i, name ) {
	var cssFn = jQuery.fn[ name ];
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return speed == null || typeof speed === "boolean" ||
			// special check for .toggle( handler, handler, ... )
			( !i && jQuery.isFunction( speed ) && jQuery.isFunction( easing ) ) ?
			cssFn.apply( this, arguments ) :
			this.animate( genFx( name, true ), speed, easing, callback );
	};
});

jQuery.fn.extend({
	fadeTo: function( speed, to, easing, callback ) {

		// show any hidden elements after setting opacity to 0
		return this.filter( isHidden ).css( "opacity", 0 ).show()

			// animate to the value specified
			.end().animate({ opacity: to }, speed, easing, callback );
	},
	animate: function( prop, speed, easing, callback ) {
		var empty = jQuery.isEmptyObject( prop ),
			optall = jQuery.speed( speed, easing, callback ),
			doAnimation = function() {
				// Operate on a copy of prop so per-property easing won't be lost
				var anim = Animation( this, jQuery.extend( {}, prop ), optall );

				// Empty animations resolve immediately
				if ( empty ) {
					anim.stop( true );
				}
			};

		return empty || optall.queue === false ?
			this.each( doAnimation ) :
			this.queue( optall.queue, doAnimation );
	},
	stop: function( type, clearQueue, gotoEnd ) {
		var stopQueue = function( hooks ) {
			var stop = hooks.stop;
			delete hooks.stop;
			stop( gotoEnd );
		};

		if ( typeof type !== "string" ) {
			gotoEnd = clearQueue;
			clearQueue = type;
			type = undefined;
		}
		if ( clearQueue && type !== false ) {
			this.queue( type || "fx", [] );
		}

		return this.each(function() {
			var dequeue = true,
				index = type != null && type + "queueHooks",
				timers = jQuery.timers,
				data = jQuery._data( this );

			if ( index ) {
				if ( data[ index ] && data[ index ].stop ) {
					stopQueue( data[ index ] );
				}
			} else {
				for ( index in data ) {
					if ( data[ index ] && data[ index ].stop && rrun.test( index ) ) {
						stopQueue( data[ index ] );
					}
				}
			}

			for ( index = timers.length; index--; ) {
				if ( timers[ index ].elem === this && (type == null || timers[ index ].queue === type) ) {
					timers[ index ].anim.stop( gotoEnd );
					dequeue = false;
					timers.splice( index, 1 );
				}
			}

			// start the next in the queue if the last step wasn't forced
			// timers currently will call their complete callbacks, which will dequeue
			// but only if they were gotoEnd
			if ( dequeue || !gotoEnd ) {
				jQuery.dequeue( this, type );
			}
		});
	}
});

// Generate parameters to create a standard animation
function genFx( type, includeWidth ) {
	var which,
		attrs = { height: type },
		i = 0;

	// if we include width, step value is 1 to do all cssExpand values,
	// if we don't include width, step value is 2 to skip over Left and Right
	includeWidth = includeWidth? 1 : 0;
	for( ; i < 4 ; i += 2 - includeWidth ) {
		which = cssExpand[ i ];
		attrs[ "margin" + which ] = attrs[ "padding" + which ] = type;
	}

	if ( includeWidth ) {
		attrs.opacity = attrs.width = type;
	}

	return attrs;
}

// Generate shortcuts for custom animations
jQuery.each({
	slideDown: genFx("show"),
	slideUp: genFx("hide"),
	slideToggle: genFx("toggle"),
	fadeIn: { opacity: "show" },
	fadeOut: { opacity: "hide" },
	fadeToggle: { opacity: "toggle" }
}, function( name, props ) {
	jQuery.fn[ name ] = function( speed, easing, callback ) {
		return this.animate( props, speed, easing, callback );
	};
});

jQuery.speed = function( speed, easing, fn ) {
	var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
		complete: fn || !fn && easing ||
			jQuery.isFunction( speed ) && speed,
		duration: speed,
		easing: fn && easing || easing && !jQuery.isFunction( easing ) && easing
	};

	opt.duration = jQuery.fx.off ? 0 : typeof opt.duration === "number" ? opt.duration :
		opt.duration in jQuery.fx.speeds ? jQuery.fx.speeds[ opt.duration ] : jQuery.fx.speeds._default;

	// normalize opt.queue - true/undefined/null -> "fx"
	if ( opt.queue == null || opt.queue === true ) {
		opt.queue = "fx";
	}

	// Queueing
	opt.old = opt.complete;

	opt.complete = function() {
		if ( jQuery.isFunction( opt.old ) ) {
			opt.old.call( this );
		}

		if ( opt.queue ) {
			jQuery.dequeue( this, opt.queue );
		}
	};

	return opt;
};

jQuery.easing = {
	linear: function( p ) {
		return p;
	},
	swing: function( p ) {
		return 0.5 - Math.cos( p*Math.PI ) / 2;
	}
};

jQuery.timers = [];
jQuery.fx = Tween.prototype.init;
jQuery.fx.tick = function() {
	var timer,
		timers = jQuery.timers,
		i = 0;

	for ( ; i < timers.length; i++ ) {
		timer = timers[ i ];
		// Checks the timer has not already been removed
		if ( !timer() && timers[ i ] === timer ) {
			timers.splice( i--, 1 );
		}
	}

	if ( !timers.length ) {
		jQuery.fx.stop();
	}
};

jQuery.fx.timer = function( timer ) {
	if ( timer() && jQuery.timers.push( timer ) && !timerId ) {
		timerId = setInterval( jQuery.fx.tick, jQuery.fx.interval );
	}
};

jQuery.fx.interval = 13;

jQuery.fx.stop = function() {
	clearInterval( timerId );
	timerId = null;
};

jQuery.fx.speeds = {
	slow: 600,
	fast: 200,
	// Default speed
	_default: 400
};

// Back Compat <1.8 extension point
jQuery.fx.step = {};

if ( jQuery.expr && jQuery.expr.filters ) {
	jQuery.expr.filters.animated = function( elem ) {
		return jQuery.grep(jQuery.timers, function( fn ) {
			return elem === fn.elem;
		}).length;
	};
}
var rroot = /^(?:body|html)$/i;

jQuery.fn.offset = function( options ) {
	if ( arguments.length ) {
		return options === undefined ?
			this :
			this.each(function( i ) {
				jQuery.offset.setOffset( this, options, i );
			});
	}

	var box, docElem, body, win, clientTop, clientLeft, scrollTop, scrollLeft, top, left,
		elem = this[ 0 ],
		doc = elem && elem.ownerDocument;

	if ( !doc ) {
		return;
	}

	if ( (body = doc.body) === elem ) {
		return jQuery.offset.bodyOffset( elem );
	}

	docElem = doc.documentElement;

	// Make sure we're not dealing with a disconnected DOM node
	if ( !jQuery.contains( docElem, elem ) ) {
		return { top: 0, left: 0 };
	}

	box = elem.getBoundingClientRect();
	win = getWindow( doc );
	clientTop  = docElem.clientTop  || body.clientTop  || 0;
	clientLeft = docElem.clientLeft || body.clientLeft || 0;
	scrollTop  = win.pageYOffset || docElem.scrollTop;
	scrollLeft = win.pageXOffset || docElem.scrollLeft;
	top  = box.top  + scrollTop  - clientTop;
	left = box.left + scrollLeft - clientLeft;

	return { top: top, left: left };
};

jQuery.offset = {

	bodyOffset: function( body ) {
		var top = body.offsetTop,
			left = body.offsetLeft;

		if ( jQuery.support.doesNotIncludeMarginInBodyOffset ) {
			top  += parseFloat( jQuery.css(body, "marginTop") ) || 0;
			left += parseFloat( jQuery.css(body, "marginLeft") ) || 0;
		}

		return { top: top, left: left };
	},

	setOffset: function( elem, options, i ) {
		var position = jQuery.css( elem, "position" );

		// set position first, in-case top/left are set even on static elem
		if ( position === "static" ) {
			elem.style.position = "relative";
		}

		var curElem = jQuery( elem ),
			curOffset = curElem.offset(),
			curCSSTop = jQuery.css( elem, "top" ),
			curCSSLeft = jQuery.css( elem, "left" ),
			calculatePosition = ( position === "absolute" || position === "fixed" ) && jQuery.inArray("auto", [curCSSTop, curCSSLeft]) > -1,
			props = {}, curPosition = {}, curTop, curLeft;

		// need to be able to calculate position if either top or left is auto and position is either absolute or fixed
		if ( calculatePosition ) {
			curPosition = curElem.position();
			curTop = curPosition.top;
			curLeft = curPosition.left;
		} else {
			curTop = parseFloat( curCSSTop ) || 0;
			curLeft = parseFloat( curCSSLeft ) || 0;
		}

		if ( jQuery.isFunction( options ) ) {
			options = options.call( elem, i, curOffset );
		}

		if ( options.top != null ) {
			props.top = ( options.top - curOffset.top ) + curTop;
		}
		if ( options.left != null ) {
			props.left = ( options.left - curOffset.left ) + curLeft;
		}

		if ( "using" in options ) {
			options.using.call( elem, props );
		} else {
			curElem.css( props );
		}
	}
};


jQuery.fn.extend({

	position: function() {
		if ( !this[0] ) {
			return;
		}

		var elem = this[0],

		// Get *real* offsetParent
		offsetParent = this.offsetParent(),

		// Get correct offsets
		offset       = this.offset(),
		parentOffset = rroot.test(offsetParent[0].nodeName) ? { top: 0, left: 0 } : offsetParent.offset();

		// Subtract element margins
		// note: when an element has margin: auto the offsetLeft and marginLeft
		// are the same in Safari causing offset.left to incorrectly be 0
		offset.top  -= parseFloat( jQuery.css(elem, "marginTop") ) || 0;
		offset.left -= parseFloat( jQuery.css(elem, "marginLeft") ) || 0;

		// Add offsetParent borders
		parentOffset.top  += parseFloat( jQuery.css(offsetParent[0], "borderTopWidth") ) || 0;
		parentOffset.left += parseFloat( jQuery.css(offsetParent[0], "borderLeftWidth") ) || 0;

		// Subtract the two offsets
		return {
			top:  offset.top  - parentOffset.top,
			left: offset.left - parentOffset.left
		};
	},

	offsetParent: function() {
		return this.map(function() {
			var offsetParent = this.offsetParent || document.body;
			while ( offsetParent && (!rroot.test(offsetParent.nodeName) && jQuery.css(offsetParent, "position") === "static") ) {
				offsetParent = offsetParent.offsetParent;
			}
			return offsetParent || document.body;
		});
	}
});


// Create scrollLeft and scrollTop methods
jQuery.each( {scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function( method, prop ) {
	var top = /Y/.test( prop );

	jQuery.fn[ method ] = function( val ) {
		return jQuery.access( this, function( elem, method, val ) {
			var win = getWindow( elem );

			if ( val === undefined ) {
				return win ? (prop in win) ? win[ prop ] :
					win.document.documentElement[ method ] :
					elem[ method ];
			}

			if ( win ) {
				win.scrollTo(
					!top ? val : jQuery( win ).scrollLeft(),
					 top ? val : jQuery( win ).scrollTop()
				);

			} else {
				elem[ method ] = val;
			}
		}, method, val, arguments.length, null );
	};
});

function getWindow( elem ) {
	return jQuery.isWindow( elem ) ?
		elem :
		elem.nodeType === 9 ?
			elem.defaultView || elem.parentWindow :
			false;
}
// Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
jQuery.each( { Height: "height", Width: "width" }, function( name, type ) {
	jQuery.each( { padding: "inner" + name, content: type, "": "outer" + name }, function( defaultExtra, funcName ) {
		// margin is only for outerHeight, outerWidth
		jQuery.fn[ funcName ] = function( margin, value ) {
			var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
				extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );

			return jQuery.access( this, function( elem, type, value ) {
				var doc;

				if ( jQuery.isWindow( elem ) ) {
					// As of 5/8/2012 this will yield incorrect results for Mobile Safari, but there
					// isn't a whole lot we can do. See pull request at this URL for discussion:
					// https://github.com/jquery/jquery/pull/764
					return elem.document.documentElement[ "client" + name ];
				}

				// Get document width or height
				if ( elem.nodeType === 9 ) {
					doc = elem.documentElement;

					// Either scroll[Width/Height] or offset[Width/Height] or client[Width/Height], whichever is greatest
					// unfortunately, this causes bug #3838 in IE6/8 only, but there is currently no good, small way to fix it.
					return Math.max(
						elem.body[ "scroll" + name ], doc[ "scroll" + name ],
						elem.body[ "offset" + name ], doc[ "offset" + name ],
						doc[ "client" + name ]
					);
				}

				return value === undefined ?
					// Get width or height on the element, requesting but not forcing parseFloat
					jQuery.css( elem, type, value, extra ) :

					// Set width or height on the element
					jQuery.style( elem, type, value, extra );
			}, type, chainable ? margin : undefined, chainable, null );
		};
	});
});
// Expose jQuery to the global object
window.jQuery = window.$ = jQuery;

// Expose jQuery as an AMD module, but only for AMD loaders that
// understand the issues with loading multiple versions of jQuery
// in a page that all might call define(). The loader will indicate
// they have special allowances for multiple jQuery versions by
// specifying define.amd.jQuery = true. Register as a named module,
// since jQuery can be concatenated with other files that may use define,
// but not use a proper concatenation script that understands anonymous
// AMD modules. A named AMD is safest and most robust way to register.
// Lowercase jquery is used because AMD module names are derived from
// file names, and jQuery is normally delivered in a lowercase file name.
// Do this after creating the global so that if an AMD module wants to call
// noConflict to hide this version of jQuery, it will work.
if ( typeof define === "function" && define.amd && define.amd.jQuery ) {
	define( "jquery", [], function () { return jQuery; } );
}

})( window );

if(typeof jwplayer=="undefined"){var jwplayer=function(a){if(jwplayer.api){return jwplayer.api.selectPlayer(a)}};var $jw=jwplayer;jwplayer.version="5.10.2295 (Licensed version)";jwplayer.vid=document.createElement("video");jwplayer.audio=document.createElement("audio");jwplayer.source=document.createElement("source");(function(b){b.utils=function(){};b.utils.typeOf=function(d){var c=typeof d;if(c==="object"){if(d){if(d instanceof Array){c="array"}}else{c="null"}}return c};b.utils.extend=function(){var c=b.utils.extend["arguments"];if(c.length>1){for(var e=1;e<c.length;e++){for(var d in c[e]){c[0][d]=c[e][d]}}return c[0]}return null};b.utils.clone=function(f){var c;var d=b.utils.clone["arguments"];if(d.length==1){switch(b.utils.typeOf(d[0])){case"object":c={};for(var e in d[0]){c[e]=b.utils.clone(d[0][e])}break;case"array":c=[];for(var e in d[0]){c[e]=b.utils.clone(d[0][e])}break;default:return d[0];break}}return c};b.utils.extension=function(c){if(!c){return""}c=c.substring(c.lastIndexOf("/")+1,c.length);c=c.split("?")[0];if(c.lastIndexOf(".")>-1){return c.substr(c.lastIndexOf(".")+1,c.length).toLowerCase()}return};b.utils.html=function(c,d){c.innerHTML=d};b.utils.wrap=function(c,d){if(c.parentNode){c.parentNode.replaceChild(d,c)}d.appendChild(c)};b.utils.ajax=function(g,f,c){var e;if(window.XMLHttpRequest){e=new XMLHttpRequest()}else{e=new ActiveXObject("Microsoft.XMLHTTP")}e.onreadystatechange=function(){if(e.readyState===4){if(e.status===200){if(f){if(!b.utils.exists(e.responseXML)){try{if(window.DOMParser){var h=(new DOMParser()).parseFromString(e.responseText,"text/xml");if(h){e=b.utils.extend({},e,{responseXML:h})}}else{h=new ActiveXObject("Microsoft.XMLDOM");h.async="false";h.loadXML(e.responseText);e=b.utils.extend({},e,{responseXML:h})}}catch(j){if(c){c(g)}}}f(e)}}else{if(c){c(g)}}}};try{e.open("GET",g,true);e.send(null)}catch(d){if(c){c(g)}}return e};b.utils.load=function(d,e,c){d.onreadystatechange=function(){if(d.readyState===4){if(d.status===200){if(e){e()}}else{if(c){c()}}}}};b.utils.find=function(d,c){return d.getElementsByTagName(c)};b.utils.append=function(c,d){c.appendChild(d)};b.utils.isIE=function(){return((!+"\v1")||(typeof window.ActiveXObject!="undefined"))};b.utils.userAgentMatch=function(d){var c=navigator.userAgent.toLowerCase();return(c.match(d)!==null)};b.utils.isIOS=function(){return b.utils.userAgentMatch(/iP(hone|ad|od)/i)};b.utils.isIPad=function(){return b.utils.userAgentMatch(/iPad/i)};b.utils.isIPod=function(){return b.utils.userAgentMatch(/iP(hone|od)/i)};b.utils.isAndroid=function(){return b.utils.userAgentMatch(/android/i)};b.utils.isLegacyAndroid=function(){return b.utils.userAgentMatch(/android 2.[012]/i)};b.utils.isBlackberry=function(){return b.utils.userAgentMatch(/blackberry/i)};b.utils.isMobile=function(){return b.utils.userAgentMatch(/(iP(hone|ad|od))|android/i)};b.utils.getFirstPlaylistItemFromConfig=function(c){var d={};var e;if(c.playlist&&c.playlist.length){e=c.playlist[0]}else{e=c}d.file=e.file;d.levels=e.levels;d.streamer=e.streamer;d.playlistfile=e.playlistfile;d.provider=e.provider;if(!d.provider){if(d.file&&(d.file.toLowerCase().indexOf("youtube.com")>-1||d.file.toLowerCase().indexOf("youtu.be")>-1)){d.provider="youtube"}if(d.streamer&&d.streamer.toLowerCase().indexOf("rtmp://")==0){d.provider="rtmp"}if(e.type){d.provider=e.type.toLowerCase()}}if(d.provider=="audio"){d.provider="sound"}return d};b.utils.getOuterHTML=function(c){if(c.outerHTML){return c.outerHTML}else{try{return new XMLSerializer().serializeToString(c)}catch(d){return""}}};b.utils.setOuterHTML=function(f,e){if(f.outerHTML){f.outerHTML=e}else{var g=document.createElement("div");g.innerHTML=e;var c=document.createRange();c.selectNodeContents(g);var d=c.extractContents();f.parentNode.insertBefore(d,f);f.parentNode.removeChild(f)}};b.utils.hasFlash=function(){if(typeof navigator.plugins!="undefined"&&typeof navigator.plugins["Shockwave Flash"]!="undefined"){return true}if(typeof window.ActiveXObject!="undefined"){try{new ActiveXObject("ShockwaveFlash.ShockwaveFlash");return true}catch(c){}}return false};b.utils.getPluginName=function(c){if(c.lastIndexOf("/")>=0){c=c.substring(c.lastIndexOf("/")+1,c.length)}if(c.lastIndexOf("-")>=0){c=c.substring(0,c.lastIndexOf("-"))}if(c.lastIndexOf(".swf")>=0){c=c.substring(0,c.lastIndexOf(".swf"))}if(c.lastIndexOf(".js")>=0){c=c.substring(0,c.lastIndexOf(".js"))}return c};b.utils.getPluginVersion=function(c){if(c.lastIndexOf("-")>=0){if(c.lastIndexOf(".js")>=0){return c.substring(c.lastIndexOf("-")+1,c.lastIndexOf(".js"))}else{if(c.lastIndexOf(".swf")>=0){return c.substring(c.lastIndexOf("-")+1,c.lastIndexOf(".swf"))}else{return c.substring(c.lastIndexOf("-")+1)}}}return""};b.utils.getAbsolutePath=function(j,h){if(!b.utils.exists(h)){h=document.location.href}if(!b.utils.exists(j)){return undefined}if(a(j)){return j}var k=h.substring(0,h.indexOf("://")+3);var g=h.substring(k.length,h.indexOf("/",k.length+1));var d;if(j.indexOf("/")===0){d=j.split("/")}else{var e=h.split("?")[0];e=e.substring(k.length+g.length+1,e.lastIndexOf("/"));d=e.split("/").concat(j.split("/"))}var c=[];for(var f=0;f<d.length;f++){if(!d[f]||!b.utils.exists(d[f])||d[f]=="."){continue}else{if(d[f]==".."){c.pop()}else{c.push(d[f])}}}return k+g+"/"+c.join("/")};function a(d){if(!b.utils.exists(d)){return}var e=d.indexOf("://");var c=d.indexOf("?");return(e>0&&(c<0||(c>e)))}b.utils.pluginPathType={ABSOLUTE:"ABSOLUTE",RELATIVE:"RELATIVE",CDN:"CDN"};b.utils.getPluginPathType=function(d){if(typeof d!="string"){return}d=d.split("?")[0];var e=d.indexOf("://");if(e>0){return b.utils.pluginPathType.ABSOLUTE}var c=d.indexOf("/");var f=b.utils.extension(d);if(e<0&&c<0&&(!f||!isNaN(f))){return b.utils.pluginPathType.CDN}return b.utils.pluginPathType.RELATIVE};b.utils.mapEmpty=function(c){for(var d in c){return false}return true};b.utils.mapLength=function(d){var c=0;for(var e in d){c++}return c};b.utils.log=function(d,c){if(typeof console!="undefined"&&typeof console.log!="undefined"){if(c){console.log(d,c)}else{console.log(d)}}};b.utils.css=function(d,g,c){if(b.utils.exists(d)){for(var e in g){try{if(typeof g[e]==="undefined"){continue}else{if(typeof g[e]=="number"&&!(e=="zIndex"||e=="opacity")){if(isNaN(g[e])){continue}if(e.match(/color/i)){g[e]="#"+b.utils.strings.pad(g[e].toString(16),6)}else{g[e]=Math.ceil(g[e])+"px"}}}d.style[e]=g[e]}catch(f){}}}};b.utils.isYouTube=function(c){return(c.indexOf("youtube.com")>-1||c.indexOf("youtu.be")>-1)};b.utils.transform=function(e,d,c,g,h){if(!b.utils.exists(d)){d=1}if(!b.utils.exists(c)){c=1}if(!b.utils.exists(g)){g=0}if(!b.utils.exists(h)){h=0}if(d==1&&c==1&&g==0&&h==0){e.style.webkitTransform="";e.style.MozTransform="";e.style.OTransform=""}else{var f="scale("+d+","+c+") translate("+g+"px,"+h+"px)";e.style.webkitTransform=f;e.style.MozTransform=f;e.style.OTransform=f}};b.utils.stretch=function(k,q,p,g,n,h){if(typeof p=="undefined"||typeof g=="undefined"||typeof n=="undefined"||typeof h=="undefined"){return}var d=p/n;var f=g/h;var m=0;var l=0;var e=false;var c={};if(q.parentElement){q.parentElement.style.overflow="hidden"}b.utils.transform(q);switch(k.toUpperCase()){case b.utils.stretching.NONE:c.width=n;c.height=h;c.top=(g-c.height)/2;c.left=(p-c.width)/2;break;case b.utils.stretching.UNIFORM:if(d>f){c.width=n*f;c.height=h*f;if(c.width/p>0.95){e=true;d=Math.ceil(100*p/c.width)/100;f=1;c.width=p}}else{c.width=n*d;c.height=h*d;if(c.height/g>0.95){e=true;d=1;f=Math.ceil(100*g/c.height)/100;c.height=g}}c.top=(g-c.height)/2;c.left=(p-c.width)/2;break;case b.utils.stretching.FILL:if(d>f){c.width=n*d;c.height=h*d}else{c.width=n*f;c.height=h*f}c.top=(g-c.height)/2;c.left=(p-c.width)/2;break;case b.utils.stretching.EXACTFIT:c.width=n;c.height=h;var o=Math.round((n/2)*(1-1/d));var j=Math.round((h/2)*(1-1/f));e=true;c.top=c.left=0;break;default:break}if(e){b.utils.transform(q,d,f,o,j)}b.utils.css(q,c)};b.utils.stretching={NONE:"NONE",FILL:"FILL",UNIFORM:"UNIFORM",EXACTFIT:"EXACTFIT"};b.utils.deepReplaceKeyName=function(k,e,c){switch(b.utils.typeOf(k)){case"array":for(var g=0;g<k.length;g++){k[g]=b.utils.deepReplaceKeyName(k[g],e,c)}break;case"object":for(var f in k){var j,h;if(e instanceof Array&&c instanceof Array){if(e.length!=c.length){continue}else{j=e;h=c}}else{j=[e];h=[c]}var d=f;for(var g=0;g<j.length;g++){d=d.replace(new RegExp(e[g],"g"),c[g])}k[d]=b.utils.deepReplaceKeyName(k[f],e,c);if(f!=d){delete k[f]}}break}return k};b.utils.isInArray=function(e,d){if(!(e)||!(e instanceof Array)){return false}for(var c=0;c<e.length;c++){if(d===e[c]){return true}}return false};b.utils.exists=function(c){switch(typeof(c)){case"string":return(c.length>0);break;case"object":return(c!==null);case"undefined":return false}return true};b.utils.empty=function(c){if(typeof c.hasChildNodes=="function"){while(c.hasChildNodes()){c.removeChild(c.firstChild)}}};b.utils.parseDimension=function(c){if(typeof c=="string"){if(c===""){return 0}else{if(c.lastIndexOf("%")>-1){return c}else{return parseInt(c.replace("px",""),10)}}}return c};b.utils.getDimensions=function(c){if(c&&c.style){return{x:b.utils.parseDimension(c.style.left),y:b.utils.parseDimension(c.style.top),width:b.utils.parseDimension(c.style.width),height:b.utils.parseDimension(c.style.height)}}else{return{}}};b.utils.getElementWidth=function(c){if(!c){return null}else{if(c==document.body){return b.utils.parentNode(c).clientWidth}else{if(c.clientWidth>0){return c.clientWidth}else{if(c.style){return b.utils.parseDimension(c.style.width)}else{return null}}}}};b.utils.getElementHeight=function(c){if(!c){return null}else{if(c==document.body){return b.utils.parentNode(c).clientHeight}else{if(c.clientHeight>0){return c.clientHeight}else{if(c.style){return b.utils.parseDimension(c.style.height)}else{return null}}}}};b.utils.timeFormat=function(c){str="00:00";if(c>0){str=Math.floor(c/60)<10?"0"+Math.floor(c/60)+":":Math.floor(c/60)+":";str+=Math.floor(c%60)<10?"0"+Math.floor(c%60):Math.floor(c%60)}return str};b.utils.useNativeFullscreen=function(){return(navigator&&navigator.vendor&&navigator.vendor.indexOf("Apple")==0)};b.utils.parentNode=function(c){if(!c){return document.body}else{if(c.parentNode){return c.parentNode}else{if(c.parentElement){return c.parentElement}else{return c}}}};b.utils.getBoundingClientRect=function(c){if(typeof c.getBoundingClientRect=="function"){return c.getBoundingClientRect()}else{return{left:c.offsetLeft+document.body.scrollLeft,top:c.offsetTop+document.body.scrollTop,width:c.offsetWidth,height:c.offsetHeight}}};b.utils.translateEventResponse=function(e,c){var g=b.utils.extend({},c);if(e==b.api.events.JWPLAYER_FULLSCREEN&&!g.fullscreen){g.fullscreen=g.message=="true"?true:false;delete g.message}else{if(typeof g.data=="object"){g=b.utils.extend(g,g.data);delete g.data}else{if(typeof g.metadata=="object"){b.utils.deepReplaceKeyName(g.metadata,["__dot__","__spc__","__dsh__"],["."," ","-"])}}}var d=["position","duration","offset"];for(var f in d){if(g[d[f]]){g[d[f]]=Math.round(g[d[f]]*1000)/1000}}return g};b.utils.saveCookie=function(c,d){document.cookie="jwplayer."+c+"="+d+"; path=/"};b.utils.getCookies=function(){var f={};var e=document.cookie.split("; ");for(var d=0;d<e.length;d++){var c=e[d].split("=");if(c[0].indexOf("jwplayer.")==0){f[c[0].substring(9,c[0].length)]=c[1]}}return f};b.utils.readCookie=function(c){return b.utils.getCookies()[c]}})(jwplayer);(function(a){a.events=function(){};a.events.COMPLETE="COMPLETE";a.events.ERROR="ERROR"})(jwplayer);(function(jwplayer){jwplayer.events.eventdispatcher=function(debug){var _debug=debug;var _listeners;var _globallisteners;this.resetEventListeners=function(){_listeners={};_globallisteners=[]};this.resetEventListeners();this.addEventListener=function(type,listener,count){try{if(!jwplayer.utils.exists(_listeners[type])){_listeners[type]=[]}if(typeof(listener)=="string"){eval("listener = "+listener)}_listeners[type].push({listener:listener,count:count})}catch(err){jwplayer.utils.log("error",err)}return false};this.removeEventListener=function(type,listener){if(!_listeners[type]){return}try{for(var listenerIndex=0;listenerIndex<_listeners[type].length;listenerIndex++){if(_listeners[type][listenerIndex].listener.toString()==listener.toString()){_listeners[type].splice(listenerIndex,1);break}}}catch(err){jwplayer.utils.log("error",err)}return false};this.addGlobalListener=function(listener,count){try{if(typeof(listener)=="string"){eval("listener = "+listener)}_globallisteners.push({listener:listener,count:count})}catch(err){jwplayer.utils.log("error",err)}return false};this.removeGlobalListener=function(listener){if(!listener){return}try{for(var globalListenerIndex=0;globalListenerIndex<_globallisteners.length;globalListenerIndex++){if(_globallisteners[globalListenerIndex].listener.toString()==listener.toString()){_globallisteners.splice(globalListenerIndex,1);break}}}catch(err){jwplayer.utils.log("error",err)}return false};this.sendEvent=function(type,data){if(!jwplayer.utils.exists(data)){data={}}if(_debug){jwplayer.utils.log(type,data)}if(typeof _listeners[type]!="undefined"){for(var listenerIndex=0;listenerIndex<_listeners[type].length;listenerIndex++){try{_listeners[type][listenerIndex].listener(data)}catch(err){jwplayer.utils.log("There was an error while handling a listener: "+err.toString(),_listeners[type][listenerIndex].listener)}if(_listeners[type][listenerIndex]){if(_listeners[type][listenerIndex].count===1){delete _listeners[type][listenerIndex]}else{if(_listeners[type][listenerIndex].count>0){_listeners[type][listenerIndex].count=_listeners[type][listenerIndex].count-1}}}}}for(var globalListenerIndex=0;globalListenerIndex<_globallisteners.length;globalListenerIndex++){try{_globallisteners[globalListenerIndex].listener(data)}catch(err){jwplayer.utils.log("There was an error while handling a listener: "+err.toString(),_globallisteners[globalListenerIndex].listener)}if(_globallisteners[globalListenerIndex]){if(_globallisteners[globalListenerIndex].count===1){delete _globallisteners[globalListenerIndex]}else{if(_globallisteners[globalListenerIndex].count>0){_globallisteners[globalListenerIndex].count=_globallisteners[globalListenerIndex].count-1}}}}}}})(jwplayer);(function(a){var b={};a.utils.animations=function(){};a.utils.animations.transform=function(c,d){c.style.webkitTransform=d;c.style.MozTransform=d;c.style.OTransform=d;c.style.msTransform=d};a.utils.animations.transformOrigin=function(c,d){c.style.webkitTransformOrigin=d;c.style.MozTransformOrigin=d;c.style.OTransformOrigin=d;c.style.msTransformOrigin=d};a.utils.animations.rotate=function(c,d){a.utils.animations.transform(c,["rotate(",d,"deg)"].join(""))};a.utils.cancelAnimation=function(c){delete b[c.id]};a.utils.fadeTo=function(m,f,e,j,h,d){if(b[m.id]!=d&&a.utils.exists(d)){return}if(m.style.opacity==f){return}var c=new Date().getTime();if(d>c){setTimeout(function(){a.utils.fadeTo(m,f,e,j,0,d)},d-c)}if(m.style.display=="none"){m.style.display="block"}if(!a.utils.exists(j)){j=m.style.opacity===""?1:m.style.opacity}if(m.style.opacity==f&&m.style.opacity!==""&&a.utils.exists(d)){if(f===0){m.style.display="none"}return}if(!a.utils.exists(d)){d=c;b[m.id]=d}if(!a.utils.exists(h)){h=0}var k=(e>0)?((c-d)/(e*1000)):0;k=k>1?1:k;var l=f-j;var g=j+(k*l);if(g>1){g=1}else{if(g<0){g=0}}m.style.opacity=g;if(h>0){b[m.id]=d+h*1000;a.utils.fadeTo(m,f,e,j,0,b[m.id]);return}setTimeout(function(){a.utils.fadeTo(m,f,e,j,0,d)},10)}})(jwplayer);(function(a){a.utils.arrays=function(){};a.utils.arrays.indexOf=function(c,d){for(var b=0;b<c.length;b++){if(c[b]==d){return b}}return -1};a.utils.arrays.remove=function(c,d){var b=a.utils.arrays.indexOf(c,d);if(b>-1){c.splice(b,1)}}})(jwplayer);(function(a){a.utils.extensionmap={"3gp":{html5:"video/3gpp",flash:"video"},"3gpp":{html5:"video/3gpp"},"3g2":{html5:"video/3gpp2",flash:"video"},"3gpp2":{html5:"video/3gpp2"},flv:{flash:"video"},f4a:{html5:"audio/mp4"},f4b:{html5:"audio/mp4",flash:"video"},f4v:{html5:"video/mp4",flash:"video"},mov:{html5:"video/quicktime",flash:"video"},m4a:{html5:"audio/mp4",flash:"video"},m4b:{html5:"audio/mp4"},m4p:{html5:"audio/mp4"},m4v:{html5:"video/mp4",flash:"video"},mp4:{html5:"video/mp4",flash:"video"},rbs:{flash:"sound"},aac:{html5:"audio/aac",flash:"video"},mp3:{html5:"audio/mp3",flash:"sound"},ogg:{html5:"audio/ogg"},oga:{html5:"audio/ogg"},ogv:{html5:"video/ogg"},webm:{html5:"video/webm"},m3u8:{html5:"audio/x-mpegurl"},gif:{flash:"image"},jpeg:{flash:"image"},jpg:{flash:"image"},swf:{flash:"image"},png:{flash:"image"},wav:{html5:"audio/x-wav"}}})(jwplayer);(function(e){e.utils.mediaparser=function(){};var g={element:{width:"width",height:"height",id:"id","class":"className",name:"name"},media:{src:"file",preload:"preload",autoplay:"autostart",loop:"repeat",controls:"controls"},source:{src:"file",type:"type",media:"media","data-jw-width":"width","data-jw-bitrate":"bitrate"},video:{poster:"image"}};var f={};e.utils.mediaparser.parseMedia=function(j){return d(j)};function c(k,j){if(!e.utils.exists(j)){j=g[k]}else{e.utils.extend(j,g[k])}return j}function d(n,j){if(f[n.tagName.toLowerCase()]&&!e.utils.exists(j)){return f[n.tagName.toLowerCase()](n)}else{j=c("element",j);var o={};for(var k in j){if(k!="length"){var m=n.getAttribute(k);if(e.utils.exists(m)){o[j[k]]=m}}}var l=n.style["#background-color"];if(l&&!(l=="transparent"||l=="rgba(0, 0, 0, 0)")){o.screencolor=l}return o}}function h(n,k){k=c("media",k);var l=[];var j=e.utils.selectors("source",n);for(var m in j){if(!isNaN(m)){l.push(a(j[m]))}}var o=d(n,k);if(e.utils.exists(o.file)){l[0]={file:o.file}}o.levels=l;return o}function a(l,k){k=c("source",k);var j=d(l,k);j.width=j.width?j.width:0;j.bitrate=j.bitrate?j.bitrate:0;return j}function b(l,k){k=c("video",k);var j=h(l,k);return j}f.media=h;f.audio=h;f.source=a;f.video=b})(jwplayer);(function(a){a.utils.loaderstatus={NEW:"NEW",LOADING:"LOADING",ERROR:"ERROR",COMPLETE:"COMPLETE"};a.utils.scriptloader=function(c){var d=a.utils.loaderstatus.NEW;var b=new a.events.eventdispatcher();a.utils.extend(this,b);this.load=function(){if(d==a.utils.loaderstatus.NEW){d=a.utils.loaderstatus.LOADING;var e=document.createElement("script");e.onload=function(f){d=a.utils.loaderstatus.COMPLETE;b.sendEvent(a.events.COMPLETE)};e.onerror=function(f){d=a.utils.loaderstatus.ERROR;b.sendEvent(a.events.ERROR)};e.onreadystatechange=function(){if(e.readyState=="loaded"||e.readyState=="complete"){d=a.utils.loaderstatus.COMPLETE;b.sendEvent(a.events.COMPLETE)}};document.getElementsByTagName("head")[0].appendChild(e);e.src=c}};this.getStatus=function(){return d}}})(jwplayer);(function(a){a.utils.selectors=function(b,e){if(!a.utils.exists(e)){e=document}b=a.utils.strings.trim(b);var c=b.charAt(0);if(c=="#"){return e.getElementById(b.substr(1))}else{if(c=="."){if(e.getElementsByClassName){return e.getElementsByClassName(b.substr(1))}else{return a.utils.selectors.getElementsByTagAndClass("*",b.substr(1))}}else{if(b.indexOf(".")>0){var d=b.split(".");return a.utils.selectors.getElementsByTagAndClass(d[0],d[1])}else{return e.getElementsByTagName(b)}}}return null};a.utils.selectors.getElementsByTagAndClass=function(e,h,g){var j=[];if(!a.utils.exists(g)){g=document}var f=g.getElementsByTagName(e);for(var d=0;d<f.length;d++){if(a.utils.exists(f[d].className)){var c=f[d].className.split(" ");for(var b=0;b<c.length;b++){if(c[b]==h){j.push(f[d])}}}}return j}})(jwplayer);(function(a){a.utils.strings=function(){};a.utils.strings.trim=function(b){return b.replace(/^\s*/,"").replace(/\s*$/,"")};a.utils.strings.pad=function(c,d,b){if(!b){b="0"}while(c.length<d){c=b+c}return c};a.utils.strings.serialize=function(b){if(b==null){return null}else{if(b=="true"){return true}else{if(b=="false"){return false}else{if(isNaN(Number(b))||b.length>5||b.length==0){return b}else{return Number(b)}}}}};a.utils.strings.seconds=function(d){d=d.replace(",",".");var b=d.split(":");var c=0;if(d.substr(-1)=="s"){c=Number(d.substr(0,d.length-1))}else{if(d.substr(-1)=="m"){c=Number(d.substr(0,d.length-1))*60}else{if(d.substr(-1)=="h"){c=Number(d.substr(0,d.length-1))*3600}else{if(b.length>1){c=Number(b[b.length-1]);c+=Number(b[b.length-2])*60;if(b.length==3){c+=Number(b[b.length-3])*3600}}else{c=Number(d)}}}}return c};a.utils.strings.xmlAttribute=function(b,c){for(var d=0;d<b.attributes.length;d++){if(b.attributes[d].name&&b.attributes[d].name.toLowerCase()==c.toLowerCase()){return b.attributes[d].value.toString()}}return""};a.utils.strings.jsonToString=function(f){var h=h||{};if(h&&h.stringify){return h.stringify(f)}var c=typeof(f);if(c!="object"||f===null){if(c=="string"){f='"'+f.replace(/"/g,'\\"')+'"'}else{return String(f)}}else{var g=[],b=(f&&f.constructor==Array);for(var d in f){var e=f[d];switch(typeof(e)){case"string":e='"'+e.replace(/"/g,'\\"')+'"';break;case"object":if(a.utils.exists(e)){e=a.utils.strings.jsonToString(e)}break}if(b){if(typeof(e)!="function"){g.push(String(e))}}else{if(typeof(e)!="function"){g.push('"'+d+'":'+String(e))}}}if(b){return"["+String(g)+"]"}else{return"{"+String(g)+"}"}}}})(jwplayer);(function(c){var d=new RegExp(/^(#|0x)[0-9a-fA-F]{3,6}/);c.utils.typechecker=function(g,f){f=!c.utils.exists(f)?b(g):f;return e(g,f)};function b(f){var g=["true","false","t","f"];if(g.toString().indexOf(f.toLowerCase().replace(" ",""))>=0){return"boolean"}else{if(d.test(f)){return"color"}else{if(!isNaN(parseInt(f,10))&&parseInt(f,10).toString().length==f.length){return"integer"}else{if(!isNaN(parseFloat(f))&&parseFloat(f).toString().length==f.length){return"float"}}}}return"string"}function e(g,f){if(!c.utils.exists(f)){return g}switch(f){case"color":if(g.length>0){return a(g)}return null;case"integer":return parseInt(g,10);case"float":return parseFloat(g);case"boolean":if(g.toLowerCase()=="true"){return true}else{if(g=="1"){return true}}return false}return g}function a(f){switch(f.toLowerCase()){case"blue":return parseInt("0000FF",16);case"green":return parseInt("00FF00",16);case"red":return parseInt("FF0000",16);case"cyan":return parseInt("00FFFF",16);case"magenta":return parseInt("FF00FF",16);case"yellow":return parseInt("FFFF00",16);case"black":return parseInt("000000",16);case"white":return parseInt("FFFFFF",16);default:f=f.replace(/(#|0x)?([0-9A-F]{3,6})$/gi,"$2");if(f.length==3){f=f.charAt(0)+f.charAt(0)+f.charAt(1)+f.charAt(1)+f.charAt(2)+f.charAt(2)}return parseInt(f,16)}return parseInt("000000",16)}})(jwplayer);(function(a){a.utils.parsers=function(){};a.utils.parsers.localName=function(b){if(!b){return""}else{if(b.localName){return b.localName}else{if(b.baseName){return b.baseName}else{return""}}}};a.utils.parsers.textContent=function(b){if(!b){return""}else{if(b.textContent){return b.textContent}else{if(b.text){return b.text}else{return""}}}}})(jwplayer);(function(a){a.utils.parsers.jwparser=function(){};a.utils.parsers.jwparser.PREFIX="jwplayer";a.utils.parsers.jwparser.parseEntry=function(c,d){for(var b=0;b<c.childNodes.length;b++){if(c.childNodes[b].prefix==a.utils.parsers.jwparser.PREFIX){d[a.utils.parsers.localName(c.childNodes[b])]=a.utils.strings.serialize(a.utils.parsers.textContent(c.childNodes[b]));if(a.utils.parsers.localName(c.childNodes[b])=="file"&&d.levels){delete d.levels}}if(!d.file&&String(d.link).toLowerCase().indexOf("youtube")>-1){d.file=d.link}}return d};a.utils.parsers.jwparser.getProvider=function(c){if(c.type){return c.type}else{if(c.file.indexOf("youtube.com/w")>-1||c.file.indexOf("youtube.com/v")>-1||c.file.indexOf("youtu.be/")>-1){return"youtube"}else{if(c.streamer&&c.streamer.indexOf("rtmp")==0){return"rtmp"}else{if(c.streamer&&c.streamer.indexOf("http")==0){return"http"}else{var b=a.utils.strings.extension(c.file);if(extensions.hasOwnProperty(b)){return extensions[b]}}}}}return""}})(jwplayer);(function(a){a.utils.parsers.mediaparser=function(){};a.utils.parsers.mediaparser.PREFIX="media";a.utils.parsers.mediaparser.parseGroup=function(d,f){var e=false;for(var c=0;c<d.childNodes.length;c++){if(d.childNodes[c].prefix==a.utils.parsers.mediaparser.PREFIX){if(!a.utils.parsers.localName(d.childNodes[c])){continue}switch(a.utils.parsers.localName(d.childNodes[c]).toLowerCase()){case"content":if(!e){f.file=a.utils.strings.xmlAttribute(d.childNodes[c],"url")}if(a.utils.strings.xmlAttribute(d.childNodes[c],"duration")){f.duration=a.utils.strings.seconds(a.utils.strings.xmlAttribute(d.childNodes[c],"duration"))}if(a.utils.strings.xmlAttribute(d.childNodes[c],"start")){f.start=a.utils.strings.seconds(a.utils.strings.xmlAttribute(d.childNodes[c],"start"))}if(d.childNodes[c].childNodes&&d.childNodes[c].childNodes.length>0){f=a.utils.parsers.mediaparser.parseGroup(d.childNodes[c],f)}if(a.utils.strings.xmlAttribute(d.childNodes[c],"width")||a.utils.strings.xmlAttribute(d.childNodes[c],"bitrate")||a.utils.strings.xmlAttribute(d.childNodes[c],"url")){if(!f.levels){f.levels=[]}f.levels.push({width:a.utils.strings.xmlAttribute(d.childNodes[c],"width"),bitrate:a.utils.strings.xmlAttribute(d.childNodes[c],"bitrate"),file:a.utils.strings.xmlAttribute(d.childNodes[c],"url")})}break;case"title":f.title=a.utils.parsers.textContent(d.childNodes[c]);break;case"description":f.description=a.utils.parsers.textContent(d.childNodes[c]);break;case"keywords":f.tags=a.utils.parsers.textContent(d.childNodes[c]);break;case"thumbnail":f.image=a.utils.strings.xmlAttribute(d.childNodes[c],"url");break;case"credit":f.author=a.utils.parsers.textContent(d.childNodes[c]);break;case"player":var b=d.childNodes[c].url;if(b.indexOf("youtube.com")>=0||b.indexOf("youtu.be")>=0){e=true;f.file=a.utils.strings.xmlAttribute(d.childNodes[c],"url")}break;case"group":a.utils.parsers.mediaparser.parseGroup(d.childNodes[c],f);break}}}return f}})(jwplayer);(function(b){b.utils.parsers.rssparser=function(){};b.utils.parsers.rssparser.parse=function(f){var c=[];for(var e=0;e<f.childNodes.length;e++){if(b.utils.parsers.localName(f.childNodes[e]).toLowerCase()=="channel"){for(var d=0;d<f.childNodes[e].childNodes.length;d++){if(b.utils.parsers.localName(f.childNodes[e].childNodes[d]).toLowerCase()=="item"){c.push(a(f.childNodes[e].childNodes[d]))}}}}return c};function a(d){var e={};for(var c=0;c<d.childNodes.length;c++){if(!b.utils.parsers.localName(d.childNodes[c])){continue}switch(b.utils.parsers.localName(d.childNodes[c]).toLowerCase()){case"enclosure":e.file=b.utils.strings.xmlAttribute(d.childNodes[c],"url");break;case"title":e.title=b.utils.parsers.textContent(d.childNodes[c]);break;case"pubdate":e.date=b.utils.parsers.textContent(d.childNodes[c]);break;case"description":e.description=b.utils.parsers.textContent(d.childNodes[c]);break;case"link":e.link=b.utils.parsers.textContent(d.childNodes[c]);break;case"category":if(e.tags){e.tags+=b.utils.parsers.textContent(d.childNodes[c])}else{e.tags=b.utils.parsers.textContent(d.childNodes[c])}break}}e=b.utils.parsers.mediaparser.parseGroup(d,e);e=b.utils.parsers.jwparser.parseEntry(d,e);return new b.html5.playlistitem(e)}})(jwplayer);(function(a){var c={};var b={};a.plugins=function(){};a.plugins.loadPlugins=function(e,d){b[e]=new a.plugins.pluginloader(new a.plugins.model(c),d);return b[e]};a.plugins.registerPlugin=function(h,f,e){var d=a.utils.getPluginName(h);if(c[d]){c[d].registerPlugin(h,f,e)}else{a.utils.log("A plugin ("+h+") was registered with the player that was not loaded. Please check your configuration.");for(var g in b){b[g].pluginFailed()}}}})(jwplayer);(function(a){a.plugins.model=function(b){this.addPlugin=function(c){var d=a.utils.getPluginName(c);if(!b[d]){b[d]=new a.plugins.plugin(c)}return b[d]}}})(jwplayer);(function(a){a.plugins.pluginmodes={FLASH:"FLASH",JAVASCRIPT:"JAVASCRIPT",HYBRID:"HYBRID"};a.plugins.plugin=function(b){var d="http://lp.longtailvideo.com";var j=a.utils.loaderstatus.NEW;var k;var h;var l;var c=new a.events.eventdispatcher();a.utils.extend(this,c);function e(){switch(a.utils.getPluginPathType(b)){case a.utils.pluginPathType.ABSOLUTE:return b;case a.utils.pluginPathType.RELATIVE:return a.utils.getAbsolutePath(b,window.location.href);case a.utils.pluginPathType.CDN:var o=a.utils.getPluginName(b);var n=a.utils.getPluginVersion(b);var m=(window.location.href.indexOf("https://")==0)?d.replace("http://","https://secure"):d;return m+"/"+a.version.split(".")[0]+"/"+o+"/"+o+(n!==""?("-"+n):"")+".js"}}function g(m){l=setTimeout(function(){j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)},1000)}function f(m){j=a.utils.loaderstatus.ERROR;c.sendEvent(a.events.ERROR)}this.load=function(){if(j==a.utils.loaderstatus.NEW){if(b.lastIndexOf(".swf")>0){k=b;j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE);return}j=a.utils.loaderstatus.LOADING;var m=new a.utils.scriptloader(e());m.addEventListener(a.events.COMPLETE,g);m.addEventListener(a.events.ERROR,f);m.load()}};this.registerPlugin=function(o,n,m){if(l){clearTimeout(l);l=undefined}if(n&&m){k=m;h=n}else{if(typeof n=="string"){k=n}else{if(typeof n=="function"){h=n}else{if(!n&&!m){k=o}}}}j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)};this.getStatus=function(){return j};this.getPluginName=function(){return a.utils.getPluginName(b)};this.getFlashPath=function(){if(k){switch(a.utils.getPluginPathType(k)){case a.utils.pluginPathType.ABSOLUTE:return k;case a.utils.pluginPathType.RELATIVE:if(b.lastIndexOf(".swf")>0){return a.utils.getAbsolutePath(k,window.location.href)}return a.utils.getAbsolutePath(k,e());case a.utils.pluginPathType.CDN:if(k.indexOf("-")>-1){return k+"h"}return k+"-h"}}return null};this.getJS=function(){return h};this.getPluginmode=function(){if(typeof k!="undefined"&&typeof h!="undefined"){return a.plugins.pluginmodes.HYBRID}else{if(typeof k!="undefined"){return a.plugins.pluginmodes.FLASH}else{if(typeof h!="undefined"){return a.plugins.pluginmodes.JAVASCRIPT}}}};this.getNewInstance=function(n,m,o){return new h(n,m,o)};this.getURL=function(){return b}}})(jwplayer);(function(a){a.plugins.pluginloader=function(h,e){var g={};var k=a.utils.loaderstatus.NEW;var d=false;var b=false;var c=new a.events.eventdispatcher();a.utils.extend(this,c);function f(){if(!b){b=true;k=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)}}function j(){if(!b){var m=0;for(plugin in g){var l=g[plugin].getStatus();if(l==a.utils.loaderstatus.LOADING||l==a.utils.loaderstatus.NEW){m++}}if(m==0){f()}}}this.setupPlugins=function(n,l,s){var m={length:0,plugins:{}};var p={length:0,plugins:{}};for(var o in g){var q=g[o].getPluginName();if(g[o].getFlashPath()){m.plugins[g[o].getFlashPath()]=l.plugins[o];m.plugins[g[o].getFlashPath()].pluginmode=g[o].getPluginmode();m.length++}if(g[o].getJS()){var r=document.createElement("div");r.id=n.id+"_"+q;r.style.position="absolute";r.style.zIndex=p.length+10;p.plugins[q]=g[o].getNewInstance(n,l.plugins[o],r);p.length++;if(typeof p.plugins[q].resize!="undefined"){n.onReady(s(p.plugins[q],r,true));n.onResize(s(p.plugins[q],r))}}}n.plugins=p.plugins;return m};this.load=function(){k=a.utils.loaderstatus.LOADING;d=true;for(var l in e){if(a.utils.exists(l)){g[l]=h.addPlugin(l);g[l].addEventListener(a.events.COMPLETE,j);g[l].addEventListener(a.events.ERROR,j)}}for(l in g){g[l].load()}d=false;j()};this.pluginFailed=function(){f()};this.getStatus=function(){return k}}})(jwplayer);(function(b){var a=[];b.api=function(d){this.container=d;this.id=d.id;var m={};var t={};var p={};var c=[];var g=undefined;var k=false;var h=[];var r=undefined;var o=b.utils.getOuterHTML(d);var s={};var j={};this.getBuffer=function(){return this.callInternal("jwGetBuffer")};this.getContainer=function(){return this.container};function e(v,u){return function(A,w,x,y){if(v.renderingMode=="flash"||v.renderingMode=="html5"){var z;if(w){j[A]=w;z="jwplayer('"+v.id+"').callback('"+A+"')"}else{if(!w&&j[A]){delete j[A]}}g.jwDockSetButton(A,z,x,y)}return u}}this.getPlugin=function(u){var w=this;var v={};if(u=="dock"){return b.utils.extend(v,{setButton:e(w,v),show:function(){w.callInternal("jwDockShow");return v},hide:function(){w.callInternal("jwDockHide");return v},onShow:function(x){w.componentListener("dock",b.api.events.JWPLAYER_COMPONENT_SHOW,x);return v},onHide:function(x){w.componentListener("dock",b.api.events.JWPLAYER_COMPONENT_HIDE,x);return v}})}else{if(u=="controlbar"){return b.utils.extend(v,{show:function(){w.callInternal("jwControlbarShow");return v},hide:function(){w.callInternal("jwControlbarHide");return v},onShow:function(x){w.componentListener("controlbar",b.api.events.JWPLAYER_COMPONENT_SHOW,x);return v},onHide:function(x){w.componentListener("controlbar",b.api.events.JWPLAYER_COMPONENT_HIDE,x);return v}})}else{if(u=="display"){return b.utils.extend(v,{show:function(){w.callInternal("jwDisplayShow");return v},hide:function(){w.callInternal("jwDisplayHide");return v},onShow:function(x){w.componentListener("display",b.api.events.JWPLAYER_COMPONENT_SHOW,x);return v},onHide:function(x){w.componentListener("display",b.api.events.JWPLAYER_COMPONENT_HIDE,x);return v}})}else{return this.plugins[u]}}}};this.callback=function(u){if(j[u]){return j[u]()}};this.getDuration=function(){return this.callInternal("jwGetDuration")};this.getFullscreen=function(){return this.callInternal("jwGetFullscreen")};this.getHeight=function(){return this.callInternal("jwGetHeight")};this.getLockState=function(){return this.callInternal("jwGetLockState")};this.getMeta=function(){return this.getItemMeta()};this.getMute=function(){return this.callInternal("jwGetMute")};this.getPlaylist=function(){var v=this.callInternal("jwGetPlaylist");if(this.renderingMode=="flash"){b.utils.deepReplaceKeyName(v,["__dot__","__spc__","__dsh__"],["."," ","-"])}for(var u=0;u<v.length;u++){if(!b.utils.exists(v[u].index)){v[u].index=u}}return v};this.getPlaylistItem=function(u){if(!b.utils.exists(u)){u=this.getCurrentItem()}return this.getPlaylist()[u]};this.getPosition=function(){return this.callInternal("jwGetPosition")};this.getRenderingMode=function(){return this.renderingMode};this.getState=function(){return this.callInternal("jwGetState")};this.getVolume=function(){return this.callInternal("jwGetVolume")};this.getWidth=function(){return this.callInternal("jwGetWidth")};this.setFullscreen=function(u){if(!b.utils.exists(u)){this.callInternal("jwSetFullscreen",!this.callInternal("jwGetFullscreen"))}else{this.callInternal("jwSetFullscreen",u)}return this};this.setMute=function(u){if(!b.utils.exists(u)){this.callInternal("jwSetMute",!this.callInternal("jwGetMute"))}else{this.callInternal("jwSetMute",u)}return this};this.lock=function(){return this};this.unlock=function(){return this};this.load=function(u){this.callInternal("jwLoad",u);return this};this.playlistItem=function(u){this.callInternal("jwPlaylistItem",u);return this};this.playlistPrev=function(){this.callInternal("jwPlaylistPrev");return this};this.playlistNext=function(){this.callInternal("jwPlaylistNext");return this};this.resize=function(v,u){if(this.renderingMode=="html5"){g.jwResize(v,u)}else{this.container.width=v;this.container.height=u;var w=document.getElementById(this.id+"_wrapper");if(w){w.style.width=v+"px";w.style.height=u+"px"}}return this};this.play=function(u){if(typeof u=="undefined"){u=this.getState();if(u==b.api.events.state.PLAYING||u==b.api.events.state.BUFFERING){this.callInternal("jwPause")}else{this.callInternal("jwPlay")}}else{this.callInternal("jwPlay",u)}return this};this.pause=function(u){if(typeof u=="undefined"){u=this.getState();if(u==b.api.events.state.PLAYING||u==b.api.events.state.BUFFERING){this.callInternal("jwPause")}else{this.callInternal("jwPlay")}}else{this.callInternal("jwPause",u)}return this};this.stop=function(){this.callInternal("jwStop");return this};this.seek=function(u){this.callInternal("jwSeek",u);return this};this.setVolume=function(u){this.callInternal("jwSetVolume",u);return this};this.loadInstream=function(v,u){r=new b.api.instream(this,g,v,u);return r};this.onBufferChange=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BUFFER,u)};this.onBufferFull=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BUFFER_FULL,u)};this.onError=function(u){return this.eventListener(b.api.events.JWPLAYER_ERROR,u)};this.onFullscreen=function(u){return this.eventListener(b.api.events.JWPLAYER_FULLSCREEN,u)};this.onMeta=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_META,u)};this.onMute=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_MUTE,u)};this.onPlaylist=function(u){return this.eventListener(b.api.events.JWPLAYER_PLAYLIST_LOADED,u)};this.onPlaylistItem=function(u){return this.eventListener(b.api.events.JWPLAYER_PLAYLIST_ITEM,u)};this.onReady=function(u){return this.eventListener(b.api.events.API_READY,u)};this.onResize=function(u){return this.eventListener(b.api.events.JWPLAYER_RESIZE,u)};this.onComplete=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_COMPLETE,u)};this.onSeek=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_SEEK,u)};this.onTime=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_TIME,u)};this.onVolume=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_VOLUME,u)};this.onBeforePlay=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BEFOREPLAY,u)};this.onBeforeComplete=function(u){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BEFORECOMPLETE,u)};this.onBuffer=function(u){return this.stateListener(b.api.events.state.BUFFERING,u)};this.onPause=function(u){return this.stateListener(b.api.events.state.PAUSED,u)};this.onPlay=function(u){return this.stateListener(b.api.events.state.PLAYING,u)};this.onIdle=function(u){return this.stateListener(b.api.events.state.IDLE,u)};this.remove=function(){if(!k){throw"Cannot call remove() before player is ready";return}q(this)};function q(u){h=[];if(b.utils.getOuterHTML(u.container)!=o){b.api.destroyPlayer(u.id,o)}}this.setup=function(v){if(b.embed){var u=this.id;q(this);var w=b(u);w.config=v;return new b.embed(w)}return this};this.registerPlugin=function(w,v,u){b.plugins.registerPlugin(w,v,u)};this.setPlayer=function(u,v){g=u;this.renderingMode=v};this.stateListener=function(u,v){if(!t[u]){t[u]=[];this.eventListener(b.api.events.JWPLAYER_PLAYER_STATE,f(u))}t[u].push(v);return this};this.detachMedia=function(){if(this.renderingMode=="html5"){return this.callInternal("jwDetachMedia")}};this.attachMedia=function(){if(this.renderingMode=="html5"){return this.callInternal("jwAttachMedia")}};function f(u){return function(w){var v=w.newstate,y=w.oldstate;if(v==u){var x=t[v];if(x){for(var z=0;z<x.length;z++){if(typeof x[z]=="function"){x[z].call(this,{oldstate:y,newstate:v})}}}}}}this.componentListener=function(u,v,w){if(!p[u]){p[u]={}}if(!p[u][v]){p[u][v]=[];this.eventListener(v,l(u,v))}p[u][v].push(w);return this};function l(u,v){return function(x){if(u==x.component){var w=p[u][v];if(w){for(var y=0;y<w.length;y++){if(typeof w[y]=="function"){w[y].call(this,x)}}}}}}this.addInternalListener=function(u,v){try{u.jwAddEventListener(v,'function(dat) { jwplayer("'+this.id+'").dispatchEvent("'+v+'", dat); }')}catch(w){b.utils.log("Could not add internal listener")}};this.eventListener=function(u,v){if(!m[u]){m[u]=[];if(g&&k){this.addInternalListener(g,u)}}m[u].push(v);return this};this.dispatchEvent=function(w){if(m[w]){var v=_utils.translateEventResponse(w,arguments[1]);for(var u=0;u<m[w].length;u++){if(typeof m[w][u]=="function"){m[w][u].call(this,v)}}}};this.dispatchInstreamEvent=function(u){if(r){r.dispatchEvent(u,arguments)}};this.callInternal=function(){if(k){var w=arguments[0],u=[];for(var v=1;v<arguments.length;v++){u.push(arguments[v])}if(typeof g!="undefined"&&typeof g[w]=="function"){if(u.length==2){return(g[w])(u[0],u[1])}else{if(u.length==1){return(g[w])(u[0])}else{return(g[w])()}}}return null}else{h.push(arguments)}};this.playerReady=function(v){k=true;if(!g){this.setPlayer(document.getElementById(v.id))}this.container=document.getElementById(this.id);for(var u in m){this.addInternalListener(g,u)}this.eventListener(b.api.events.JWPLAYER_PLAYLIST_ITEM,function(w){s={}});this.eventListener(b.api.events.JWPLAYER_MEDIA_META,function(w){b.utils.extend(s,w.metadata)});this.dispatchEvent(b.api.events.API_READY);while(h.length>0){this.callInternal.apply(this,h.shift())}};this.getItemMeta=function(){return s};this.getCurrentItem=function(){return this.callInternal("jwGetPlaylistIndex")};function n(w,y,x){var u=[];if(!y){y=0}if(!x){x=w.length-1}for(var v=y;v<=x;v++){u.push(w[v])}return u}return this};b.api.selectPlayer=function(d){var c;if(!b.utils.exists(d)){d=0}if(d.nodeType){c=d}else{if(typeof d=="string"){c=document.getElementById(d)}}if(c){var e=b.api.playerById(c.id);if(e){return e}else{return b.api.addPlayer(new b.api(c))}}else{if(typeof d=="number"){return b.getPlayers()[d]}}return null};b.api.events={API_READY:"jwplayerAPIReady",JWPLAYER_READY:"jwplayerReady",JWPLAYER_FULLSCREEN:"jwplayerFullscreen",JWPLAYER_RESIZE:"jwplayerResize",JWPLAYER_ERROR:"jwplayerError",JWPLAYER_MEDIA_BEFOREPLAY:"jwplayerMediaBeforePlay",JWPLAYER_MEDIA_BEFORECOMPLETE:"jwplayerMediaBeforeComplete",JWPLAYER_COMPONENT_SHOW:"jwplayerComponentShow",JWPLAYER_COMPONENT_HIDE:"jwplayerComponentHide",JWPLAYER_MEDIA_BUFFER:"jwplayerMediaBuffer",JWPLAYER_MEDIA_BUFFER_FULL:"jwplayerMediaBufferFull",JWPLAYER_MEDIA_ERROR:"jwplayerMediaError",JWPLAYER_MEDIA_LOADED:"jwplayerMediaLoaded",JWPLAYER_MEDIA_COMPLETE:"jwplayerMediaComplete",JWPLAYER_MEDIA_SEEK:"jwplayerMediaSeek",JWPLAYER_MEDIA_TIME:"jwplayerMediaTime",JWPLAYER_MEDIA_VOLUME:"jwplayerMediaVolume",JWPLAYER_MEDIA_META:"jwplayerMediaMeta",JWPLAYER_MEDIA_MUTE:"jwplayerMediaMute",JWPLAYER_PLAYER_STATE:"jwplayerPlayerState",JWPLAYER_PLAYLIST_LOADED:"jwplayerPlaylistLoaded",JWPLAYER_PLAYLIST_ITEM:"jwplayerPlaylistItem",JWPLAYER_INSTREAM_CLICK:"jwplayerInstreamClicked",JWPLAYER_INSTREAM_DESTROYED:"jwplayerInstreamDestroyed"};b.api.events.state={BUFFERING:"BUFFERING",IDLE:"IDLE",PAUSED:"PAUSED",PLAYING:"PLAYING"};b.api.playerById=function(d){for(var c=0;c<a.length;c++){if(a[c].id==d){return a[c]}}return null};b.api.addPlayer=function(c){for(var d=0;d<a.length;d++){if(a[d]==c){return c}}a.push(c);return c};b.api.destroyPlayer=function(h,d){var g=-1;for(var l=0;l<a.length;l++){if(a[l].id==h){g=l;continue}}if(g>=0){try{a[g].callInternal("jwDestroy")}catch(k){}var c=document.getElementById(a[g].id);if(document.getElementById(a[g].id+"_wrapper")){c=document.getElementById(a[g].id+"_wrapper")}if(c){if(d){b.utils.setOuterHTML(c,d)}else{var j=document.createElement("div");var f=c.id;if(c.id.indexOf("_wrapper")==c.id.length-8){newID=c.id.substring(0,c.id.length-8)}j.setAttribute("id",f);c.parentNode.replaceChild(j,c)}}a.splice(g,1)}return null};b.getPlayers=function(){return a.slice(0)}})(jwplayer);var _userPlayerReady=(typeof playerReady=="function")?playerReady:undefined;playerReady=function(b){var a=jwplayer.api.playerById(b.id);if(a){a.playerReady(b)}else{jwplayer.api.selectPlayer(b.id).playerReady(b)}if(_userPlayerReady){_userPlayerReady.call(this,b)}};(function(a){a.api.instream=function(c,j,n,q){var h=c;var b=j;var g=n;var k=q;var e={};var p={};function f(){h.callInternal("jwLoadInstream",n,q)}function m(r,s){b.jwInstreamAddEventListener(s,'function(dat) { jwplayer("'+h.id+'").dispatchInstreamEvent("'+s+'", dat); }')}function d(r,s){if(!e[r]){e[r]=[];m(b,r)}e[r].push(s);return this}function o(r,s){if(!p[r]){p[r]=[];d(a.api.events.JWPLAYER_PLAYER_STATE,l(r))}p[r].push(s);return this}function l(r){return function(t){var s=t.newstate,v=t.oldstate;if(s==r){var u=p[s];if(u){for(var w=0;w<u.length;w++){if(typeof u[w]=="function"){u[w].call(this,{oldstate:v,newstate:s,type:t.type})}}}}}}this.dispatchEvent=function(u,t){if(e[u]){var s=_utils.translateEventResponse(u,t[1]);for(var r=0;r<e[u].length;r++){if(typeof e[u][r]=="function"){e[u][r].call(this,s)}}}};this.onError=function(r){return d(a.api.events.JWPLAYER_ERROR,r)};this.onFullscreen=function(r){return d(a.api.events.JWPLAYER_FULLSCREEN,r)};this.onMeta=function(r){return d(a.api.events.JWPLAYER_MEDIA_META,r)};this.onMute=function(r){return d(a.api.events.JWPLAYER_MEDIA_MUTE,r)};this.onComplete=function(r){return d(a.api.events.JWPLAYER_MEDIA_COMPLETE,r)};this.onSeek=function(r){return d(a.api.events.JWPLAYER_MEDIA_SEEK,r)};this.onTime=function(r){return d(a.api.events.JWPLAYER_MEDIA_TIME,r)};this.onVolume=function(r){return d(a.api.events.JWPLAYER_MEDIA_VOLUME,r)};this.onBuffer=function(r){return o(a.api.events.state.BUFFERING,r)};this.onPause=function(r){return o(a.api.events.state.PAUSED,r)};this.onPlay=function(r){return o(a.api.events.state.PLAYING,r)};this.onIdle=function(r){return o(a.api.events.state.IDLE,r)};this.onInstreamClick=function(r){return d(a.api.events.JWPLAYER_INSTREAM_CLICK,r)};this.onInstreamDestroyed=function(r){return d(a.api.events.JWPLAYER_INSTREAM_DESTROYED,r)};this.play=function(r){b.jwInstreamPlay(r)};this.pause=function(r){b.jwInstreamPause(r)};this.seek=function(r){b.jwInstreamSeek(r)};this.destroy=function(){b.jwInstreamDestroy()};this.getState=function(){return b.jwInstreamGetState()};this.getDuration=function(){return b.jwInstreamGetDuration()};this.getPosition=function(){return b.jwInstreamGetPosition()};f()}})(jwplayer);(function(a){var c=a.utils;a.embed=function(h){var k={width:400,height:300,components:{controlbar:{position:"over"}}};var g=c.mediaparser.parseMedia(h.container);var f=new a.embed.config(c.extend(k,g,h.config),this);var j=a.plugins.loadPlugins(h.id,f.plugins);function d(n,m){for(var l in m){if(typeof n[l]=="function"){(n[l]).call(n,m[l])}}}function e(){if(j.getStatus()==c.loaderstatus.COMPLETE){for(var n=0;n<f.modes.length;n++){if(f.modes[n].type&&a.embed[f.modes[n].type]){var p=f.modes[n].config;var t=f;if(p){t=c.extend(c.clone(f),p);var s=["file","levels","playlist"];for(var m=0;m<s.length;m++){var q=s[m];if(c.exists(p[q])){for(var l=0;l<s.length;l++){if(l!=m){var o=s[l];if(c.exists(t[o])&&!c.exists(p[o])){delete t[o]}}}}}}var r=new a.embed[f.modes[n].type](document.getElementById(h.id),f.modes[n],t,j,h);if(r.supportsConfig()){r.embed();d(h,f.events);return h}}}c.log("No suitable players found");new a.embed.logo(c.extend({hide:true},f.components.logo),"none",h.id)}}j.addEventListener(a.events.COMPLETE,e);j.addEventListener(a.events.ERROR,e);j.load();return h};function b(){if(!document.body){return setTimeout(b,15)}var d=c.selectors.getElementsByTagAndClass("video","jwplayer");for(var e=0;e<d.length;e++){var f=d[e];if(f.id==""){f.id="jwplayer_"+Math.round(Math.random()*100000)}a(f.id).setup({})}}b()})(jwplayer);(function(e){var k=e.utils;function h(m){var l=[{type:"flash",src:m?m:"/jwplayer/player.swf"},{type:"html5"},{type:"download"}];if(k.isAndroid()){l[0]=l.splice(1,1,l[0])[0]}return l}var a={players:"modes",autoplay:"autostart"};function b(o){var n=o.toLowerCase();var m=["left","right","top","bottom"];for(var l=0;l<m.length;l++){if(n==m[l]){return true}}return false}function c(m){var l=false;l=(m instanceof Array)||(typeof m=="object"&&!m.position&&!m.size);return l}function j(l){if(typeof l=="string"){if(parseInt(l).toString()==l||l.toLowerCase().indexOf("px")>-1){return parseInt(l)}}return l}var g=["playlist","dock","controlbar","logo","display"];function f(l){var o={};switch(k.typeOf(l.plugins)){case"object":for(var n in l.plugins){o[k.getPluginName(n)]=n}break;case"string":var p=l.plugins.split(",");for(var m=0;m<p.length;m++){o[k.getPluginName(p[m])]=p[m]}break}return o}function d(p,o,n,l){if(k.typeOf(p[o])!="object"){p[o]={}}var m=p[o][n];if(k.typeOf(m)!="object"){p[o][n]=m={}}if(l){if(o=="plugins"){var q=k.getPluginName(n);m[l]=p[q+"."+l];delete p[q+"."+l]}else{m[l]=p[n+"."+l];delete p[n+"."+l]}}}e.embed.deserialize=function(m){var n=f(m);for(var l in n){d(m,"plugins",n[l])}for(var q in m){if(q.indexOf(".")>-1){var p=q.split(".");var o=p[0];var q=p[1];if(k.isInArray(g,o)){d(m,"components",o,q)}else{if(n[o]){d(m,"plugins",n[o],q)}}}}return m};e.embed.config=function(l,v){var u=k.extend({},l);var s;if(c(u.playlist)){s=u.playlist;delete u.playlist}u=e.embed.deserialize(u);u.height=j(u.height);u.width=j(u.width);if(typeof u.plugins=="string"){var m=u.plugins.split(",");if(typeof u.plugins!="object"){u.plugins={}}for(var q=0;q<m.length;q++){var r=k.getPluginName(m[q]);if(typeof u[r]=="object"){u.plugins[m[q]]=u[r];delete u[r]}else{u.plugins[m[q]]={}}}}for(var t=0;t<g.length;t++){var p=g[t];if(k.exists(u[p])){if(typeof u[p]!="object"){if(!u.components[p]){u.components[p]={}}if(p=="logo"){u.components[p].file=u[p]}else{u.components[p].position=u[p]}delete u[p]}else{if(!u.components[p]){u.components[p]={}}k.extend(u.components[p],u[p]);delete u[p]}}if(typeof u[p+"size"]!="undefined"){if(!u.components[p]){u.components[p]={}}u.components[p].size=u[p+"size"];delete u[p+"size"]}}if(typeof u.icons!="undefined"){if(!u.components.display){u.components.display={}}u.components.display.icons=u.icons;delete u.icons}for(var o in a){if(u[o]){if(!u[a[o]]){u[a[o]]=u[o]}delete u[o]}}var n;if(u.flashplayer&&!u.modes){n=h(u.flashplayer);delete u.flashplayer}else{if(u.modes){if(typeof u.modes=="string"){n=h(u.modes)}else{if(u.modes instanceof Array){n=u.modes}else{if(typeof u.modes=="object"&&u.modes.type){n=[u.modes]}}}delete u.modes}else{n=h()}}u.modes=n;if(s){u.playlist=s}return u}})(jwplayer);(function(a){a.embed.download=function(c,g,b,d,f){this.embed=function(){var k=a.utils.extend({},b);var q={};var j=b.width?b.width:480;if(typeof j!="number"){j=parseInt(j,10)}var m=b.height?b.height:320;if(typeof m!="number"){m=parseInt(m,10)}var u,o,n;var s={};if(b.playlist&&b.playlist.length){s.file=b.playlist[0].file;o=b.playlist[0].image;s.levels=b.playlist[0].levels}else{s.file=b.file;o=b.image;s.levels=b.levels}if(s.file){u=s.file}else{if(s.levels&&s.levels.length){u=s.levels[0].file}}n=u?"pointer":"auto";var l={display:{style:{cursor:n,width:j,height:m,backgroundColor:"#000",position:"relative",textDecoration:"none",border:"none",display:"block"}},display_icon:{style:{cursor:n,position:"absolute",display:u?"block":"none",top:0,left:0,border:0,margin:0,padding:0,zIndex:3,width:50,height:50,backgroundImage:"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAALdJREFUeNrs18ENgjAYhmFouDOCcQJGcARHgE10BDcgTOIosAGwQOuPwaQeuFRi2p/3Sb6EC5L3QCxZBgAAAOCorLW1zMn65TrlkH4NcV7QNcUQt7Gn7KIhxA+qNIR81spOGkL8oFJDyLJRdosqKDDkK+iX5+d7huzwM40xptMQMkjIOeRGo+VkEVvIPfTGIpKASfYIfT9iCHkHrBEzf4gcUQ56aEzuGK/mw0rHpy4AAACAf3kJMACBxjAQNRckhwAAAABJRU5ErkJggg==)"}},display_iconBackground:{style:{cursor:n,position:"absolute",display:u?"block":"none",top:((m-50)/2),left:((j-50)/2),border:0,width:50,height:50,margin:0,padding:0,zIndex:2,backgroundImage:"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEpJREFUeNrszwENADAIA7DhX8ENoBMZ5KR10EryckCJiIiIiIiIiIiIiIiIiIiIiIh8GmkRERERERERERERERERERERERGRHSPAAPlXH1phYpYaAAAAAElFTkSuQmCC)"}},display_image:{style:{width:j,height:m,display:o?"block":"none",position:"absolute",cursor:n,left:0,top:0,margin:0,padding:0,textDecoration:"none",zIndex:1,border:"none"}}};var h=function(v,x,y){var w=document.createElement(v);if(y){w.id=y}else{w.id=c.id+"_jwplayer_"+x}a.utils.css(w,l[x].style);return w};q.display=h("a","display",c.id);if(u){q.display.setAttribute("href",a.utils.getAbsolutePath(u))}q.display_image=h("img","display_image");q.display_image.setAttribute("alt","Click to download...");if(o){q.display_image.setAttribute("src",a.utils.getAbsolutePath(o))}if(true){q.display_icon=h("div","display_icon");q.display_iconBackground=h("div","display_iconBackground");q.display.appendChild(q.display_image);q.display_iconBackground.appendChild(q.display_icon);q.display.appendChild(q.display_iconBackground)}_css=a.utils.css;_hide=function(v){_css(v,{display:"none"})};function r(v){_imageWidth=q.display_image.naturalWidth;_imageHeight=q.display_image.naturalHeight;t()}function t(){a.utils.stretch(a.utils.stretching.UNIFORM,q.display_image,j,m,_imageWidth,_imageHeight)}q.display_image.onerror=function(v){_hide(q.display_image)};q.display_image.onload=r;c.parentNode.replaceChild(q.display,c);var p=(b.plugins&&b.plugins.logo)?b.plugins.logo:{};q.display.appendChild(new a.embed.logo(b.components.logo,"download",c.id));f.container=document.getElementById(f.id);f.setPlayer(q.display,"download")};this.supportsConfig=function(){if(b){var j=a.utils.getFirstPlaylistItemFromConfig(b);if(typeof j.file=="undefined"&&typeof j.levels=="undefined"){return true}else{if(j.file){return e(j.file,j.provider,j.playlistfile)}else{if(j.levels&&j.levels.length){for(var h=0;h<j.levels.length;h++){if(j.levels[h].file&&e(j.levels[h].file,j.provider,j.playlistfile)){return true}}}}}}else{return true}};function e(j,l,h){if(h){return false}var k=["image","sound","youtube","http"];if(l&&(k.toString().indexOf(l)>-1)){return true}if(!l||(l&&l=="video")){var m=a.utils.extension(j);if(m&&a.utils.extensionmap[m]){return true}}return false}}})(jwplayer);(function(a){a.embed.flash=function(f,g,l,e,j){function m(o,n,p){var q=document.createElement("param");q.setAttribute("name",n);q.setAttribute("value",p);o.appendChild(q)}function k(o,p,n){return function(q){if(n){document.getElementById(j.id+"_wrapper").appendChild(p)}var s=document.getElementById(j.id).getPluginConfig("display");o.resize(s.width,s.height);var r={left:s.x,top:s.y};a.utils.css(p,r)}}function d(p){if(!p){return{}}var r={};for(var o in p){var n=p[o];for(var q in n){r[o+"."+q]=n[q]}}return r}function h(q,p){if(q[p]){var s=q[p];for(var o in s){var n=s[o];if(typeof n=="string"){if(!q[o]){q[o]=n}}else{for(var r in n){if(!q[o+"."+r]){q[o+"."+r]=n[r]}}}}delete q[p]}}function b(q){if(!q){return{}}var t={},s=[];for(var n in q){var p=a.utils.getPluginName(n);var o=q[n];s.push(n);for(var r in o){t[p+"."+r]=o[r]}}t.plugins=s.join(",");return t}function c(p){var n=p.netstreambasepath?"":"netstreambasepath="+encodeURIComponent(window.location.href.split("#")[0])+"&";for(var o in p){if(typeof(p[o])=="object"){n+=o+"="+encodeURIComponent("[[JSON]]"+a.utils.strings.jsonToString(p[o]))+"&"}else{n+=o+"="+encodeURIComponent(p[o])+"&"}}return n.substring(0,n.length-1)}this.embed=function(){l.id=j.id;var A;var r=a.utils.extend({},l);var o=r.width;var y=r.height;if(f.id+"_wrapper"==f.parentNode.id){A=document.getElementById(f.id+"_wrapper")}else{A=document.createElement("div");A.id=f.id+"_wrapper";a.utils.wrap(f,A);a.utils.css(A,{position:"relative",width:o,height:y})}var p=e.setupPlugins(j,r,k);if(p.length>0){a.utils.extend(r,b(p.plugins))}else{delete r.plugins}var s=["height","width","modes","events"];for(var v=0;v<s.length;v++){delete r[s[v]]}var q="opaque";if(r.wmode){q=r.wmode}h(r,"components");h(r,"providers");if(typeof r["dock.position"]!="undefined"){if(r["dock.position"].toString().toLowerCase()=="false"){r.dock=r["dock.position"];delete r["dock.position"]}}var x=a.utils.getCookies();for(var n in x){if(typeof(r[n])=="undefined"){r[n]=x[n]}}var z="#000000";var u;if(a.utils.isIE()){var w='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" bgcolor="'+z+'" width="100%" height="100%" id="'+f.id+'" name="'+f.id+'" tabindex=0"">';w+='<param name="movie" value="'+g.src+'">';w+='<param name="allowfullscreen" value="true">';w+='<param name="allowscriptaccess" value="always">';w+='<param name="seamlesstabbing" value="true">';w+='<param name="wmode" value="'+q+'">';w+='<param name="flashvars" value="'+c(r)+'">';w+="</object>";a.utils.setOuterHTML(f,w);u=document.getElementById(f.id)}else{var t=document.createElement("object");t.setAttribute("type","application/x-shockwave-flash");t.setAttribute("data",g.src);t.setAttribute("width","100%");t.setAttribute("height","100%");t.setAttribute("bgcolor","#000000");t.setAttribute("id",f.id);t.setAttribute("name",f.id);t.setAttribute("tabindex",0);m(t,"allowfullscreen","true");m(t,"allowscriptaccess","always");m(t,"seamlesstabbing","true");m(t,"wmode",q);m(t,"flashvars",c(r));f.parentNode.replaceChild(t,f);u=t}j.container=u;j.setPlayer(u,"flash")};this.supportsConfig=function(){if(a.utils.hasFlash()){if(l){var o=a.utils.getFirstPlaylistItemFromConfig(l);if(typeof o.file=="undefined"&&typeof o.levels=="undefined"){return true}else{if(o.file){return flashCanPlay(o.file,o.provider)}else{if(o.levels&&o.levels.length){for(var n=0;n<o.levels.length;n++){if(o.levels[n].file&&flashCanPlay(o.levels[n].file,o.provider)){return true}}}}}}else{return true}}return false};flashCanPlay=function(n,p){var o=["video","http","sound","image"];if(p&&(o.toString().indexOf(p)<0)){return true}var q=a.utils.extension(n);if(!q){return true}if(a.utils.exists(a.utils.extensionmap[q])&&!a.utils.exists(a.utils.extensionmap[q].flash)){return false}return true}}})(jwplayer);(function(a){a.embed.html5=function(c,g,b,d,f){function e(j,k,h){return function(l){var m=document.getElementById(c.id+"_displayarea");if(h){m.appendChild(k)}j.resize(m.clientWidth,m.clientHeight);k.left=m.style.left;k.top=m.style.top}}this.embed=function(){if(a.html5){d.setupPlugins(f,b,e);c.innerHTML="";var j=a.utils.extend({screencolor:"0x000000"},b);var h=["plugins","modes","events"];for(var k=0;k<h.length;k++){delete j[h[k]]}if(j.levels&&!j.sources){j.sources=b.levels}if(j.skin&&j.skin.toLowerCase().indexOf(".zip")>0){j.skin=j.skin.replace(/\.zip/i,".xml")}var l=new (a.html5(c)).setup(j);f.container=document.getElementById(f.id);f.setPlayer(l,"html5")}else{return null}};this.supportsConfig=function(){if(!!a.vid.canPlayType){if(b){var j=a.utils.getFirstPlaylistItemFromConfig(b);if(typeof j.file=="undefined"&&typeof j.levels=="undefined"){return true}else{if(j.file){return html5CanPlay(a.vid,j.file,j.provider,j.playlistfile)}else{if(j.levels&&j.levels.length){for(var h=0;h<j.levels.length;h++){if(j.levels[h].file&&html5CanPlay(a.vid,j.levels[h].file,j.provider,j.playlistfile)){return true}}}}}}else{return true}}return false};html5CanPlay=function(k,j,l,h){if(h){return false}if(l&&l=="youtube"){return true}if(l&&l!="video"&&l!="http"&&l!="sound"){return false}if(navigator.userAgent.match(/BlackBerry/i)!==null){return false}var m=a.utils.extension(j);if(!a.utils.exists(m)||!a.utils.exists(a.utils.extensionmap[m])){return true}if(!a.utils.exists(a.utils.extensionmap[m].html5)){return false}if(a.utils.isLegacyAndroid()&&m.match(/m4v|mp4/)){return true}return browserCanPlay(k,a.utils.extensionmap[m].html5)};browserCanPlay=function(j,h){if(!h){return true}if(j.canPlayType(h)){return true}else{if(h=="audio/mp3"&&navigator.userAgent.match(/safari/i)){return j.canPlayType("audio/mpeg")}else{return false}}}}})(jwplayer);(function(a){a.embed.logo=function(m,l,d){var j={prefix:"http://l.longtailvideo.com/"+l+"/",file:"",link:"",linktarget:"_top",margin:8,out:0.5,over:1,timeout:5,hide:false,position:"bottom-left"};_css=a.utils.css;var b;var h;k();function k(){o();c();f()}function o(){if(j.prefix){var q=a.version.split(/\W/).splice(0,2).join("/");if(j.prefix.indexOf(q)<0){j.prefix+=q+"/"}}h=a.utils.extend({},j,m)}function p(){var s={border:"none",textDecoration:"none",position:"absolute",cursor:"pointer",zIndex:10};s.display=h.hide?"none":"block";var r=h.position.toLowerCase().split("-");for(var q in r){s[r[q]]=h.margin}return s}function c(){b=document.createElement("img");b.id=d+"_jwplayer_logo";b.style.display="none";b.onload=function(q){_css(b,p());e()};if(!h.file){return}if(h.file.indexOf("http://")===0){b.src=h.file}else{b.src=h.prefix+h.file}}if(!h.file){return}function f(){if(h.link){b.onmouseover=g;b.onmouseout=e;b.onclick=n}else{this.mouseEnabled=false}}function n(q){if(typeof q!="undefined"){q.preventDefault();q.stopPropagation()}if(h.link){window.open(h.link,h.linktarget)}return}function e(q){if(h.link){b.style.opacity=h.out}return}function g(q){if(h.hide){b.style.opacity=h.over}return}return b}})(jwplayer);(function(a){a.html5=function(b){var c=b;this.setup=function(d){a.utils.extend(this,new a.html5.api(c,d));return this};return this}})(jwplayer);(function(a){var d=a.utils;var b=d.css;var c=d.isIOS();a.html5.view=function(n,H,h){var m=n;var y=H;var j=h;var R;var g;var t;var o;var F;var P;var O;var E=false;var x=false;var A,N;var f,S,u;function L(){R=document.createElement("div");R.id=y.id;R.className=y.className;_videowrapper=document.createElement("div");_videowrapper.id=R.id+"_video_wrapper";y.id=R.id+"_video";b(R,{position:"relative",height:j.height,width:j.width,padding:0,backgroundColor:U(),zIndex:0});function U(){if(m.skin.getComponentSettings("display")&&m.skin.getComponentSettings("display").backgroundcolor){return m.skin.getComponentSettings("display").backgroundcolor}return parseInt("000000",16)}b(y,{width:"100%",height:"100%",top:0,left:0,zIndex:1,margin:"auto",display:"block"});b(_videowrapper,{overflow:"hidden",position:"absolute",top:0,left:0,bottom:0,right:0});d.wrap(y,R);d.wrap(y,_videowrapper);o=document.createElement("div");o.id=R.id+"_displayarea";R.appendChild(o);_instreamArea=document.createElement("div");_instreamArea.id=R.id+"_instreamarea";b(_instreamArea,{overflow:"hidden",position:"absolute",top:0,left:0,bottom:0,right:0,zIndex:100,background:"000000",display:"none"});R.appendChild(_instreamArea)}function K(){for(var U=0;U<j.plugins.order.length;U++){var V=j.plugins.order[U];if(d.exists(j.plugins.object[V].getDisplayElement)){j.plugins.object[V].height=d.parseDimension(j.plugins.object[V].getDisplayElement().style.height);j.plugins.object[V].width=d.parseDimension(j.plugins.object[V].getDisplayElement().style.width);j.plugins.config[V].currentPosition=j.plugins.config[V].position}}v()}function s(U){x=j.fullscreen}function p(U){if(S){return}switch(U.newstate){case a.api.events.state.PLAYING:if(j.getMedia()&&j.getMedia().hasChrome()){o.style.display="none"}break;default:o.style.display="block";break}l()}function v(V){var X=j.getMedia()?j.getMedia().getDisplayElement():null;if(d.exists(X)){if(O!=X){if(O&&O.parentNode){O.parentNode.replaceChild(X,O)}O=X}for(var U=0;U<j.plugins.order.length;U++){var W=j.plugins.order[U];if(d.exists(j.plugins.object[W].getDisplayElement)){j.plugins.config[W].currentPosition=j.plugins.config[W].position}}}G(j.width,j.height)}this.setup=function(){if(j&&j.getMedia()){y=j.getMedia().getDisplayElement()}L();K();m.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,p);m.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_LOADED,v);m.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_BEFOREPLAY,s);m.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_META,function(V){l()});var U;if(d.exists(window.onresize)){U=window.onresize}window.onresize=function(V){if(d.exists(U)){try{U(V)}catch(X){}}if(m.jwGetFullscreen()){if(!B()){var W=d.getBoundingClientRect(document.body);j.width=Math.abs(W.left)+Math.abs(W.right);j.height=window.innerHeight;G(j.width,j.height)}}else{G(j.width,j.height)}}};function M(U){switch(U.keyCode){case 27:if(m.jwGetFullscreen()){m.jwSetFullscreen(false)}break;case 32:if(m.jwGetState()!=a.api.events.state.IDLE&&m.jwGetState()!=a.api.events.state.PAUSED){m.jwPause()}else{m.jwPlay()}break}}function G(U,ad){if(R.style.display=="none"){return}var X=[].concat(j.plugins.order);X.reverse();F=X.length+2;if(x&&B()){try{if(j.fullscreen&&!j.getMedia().getDisplayElement().webkitDisplayingFullscreen){j.fullscreen=false}}catch(aa){}}if(!j.fullscreen){g=U;t=ad;if(typeof U=="string"&&U.indexOf("%")>0){g=d.getElementWidth(d.parentNode(R))*parseInt(U.replace("%"),"")/100}else{g=U}if(typeof ad=="string"&&ad.indexOf("%")>0){t=d.getElementHeight(d.parentNode(R))*parseInt(ad.replace("%"),"")/100}else{t=ad}var Y={top:0,bottom:0,left:0,right:0,width:g,height:t,position:"absolute"};b(o,Y);var ae={};var ab;try{ab=j.plugins.object.display.getDisplayElement()}catch(aa){}if(ab){ae.width=d.parseDimension(ab.style.width);ae.height=d.parseDimension(ab.style.height)}var ac=d.extend({},Y,ae,{zIndex:_instreamArea.style.zIndex,display:_instreamArea.style.display});b(_instreamArea,ac);b(R,{height:t,width:g});var Z=w(I,X);if(Z.length>0){F+=Z.length;var W=Z.indexOf("playlist"),V=Z.indexOf("controlbar");if(W>=0&&V>=0){Z[W]=Z.splice(V,1,Z[W])[0]}w(q,Z,true)}A=d.getElementWidth(o);N=d.getElementHeight(o)}else{if(!B()&&!c){w(e,X,true)}}l()}var r;function w(ab,X,Y){r=0;var Z=[];for(var W=0;W<X.length;W++){var aa=X[W];if(d.exists(j.plugins.object[aa].getDisplayElement)){if(j.plugins.config[aa].currentPosition!=a.html5.view.positions.NONE){var U=ab(aa,F--);if(!U){Z.push(aa)}else{var V=U.width;var ac=U.height;if(Y){delete U.width;delete U.height}b(j.plugins.object[aa].getDisplayElement(),U);j.plugins.object[aa].resize(V,ac)}}else{b(j.plugins.object[aa].getDisplayElement(),{display:"none"})}}}return Z}function I(V,W){if(d.exists(j.plugins.object[V].getDisplayElement)){if(j.plugins.config[V].position&&T(j.plugins.config[V].position)){if(!d.exists(j.plugins.object[V].getDisplayElement().parentNode)){R.appendChild(j.plugins.object[V].getDisplayElement())}var U=z(V);U.zIndex=W;return U}}return false}function q(U,V){if(!d.exists(j.plugins.object[U].getDisplayElement().parentNode)){o.appendChild(j.plugins.object[U].getDisplayElement())}return{position:"absolute",width:(d.getElementWidth(o)-d.parseDimension(o.style.right)),height:(d.getElementHeight(o)-d.parseDimension(o.style.bottom)),zIndex:V}}function e(U,V){return{position:"fixed",width:j.width,height:j.height,zIndex:V}}var l=this.resizeMedia=function(){o.style.position="absolute";var W=j.getMedia()?j.getMedia().getDisplayElement():u;if(!W){return}if(W&&W.tagName.toLowerCase()=="video"){if(!W.videoWidth||!W.videoHeight){W.style.width=o.style.width;W.style.height=o.style.height;return}W.style.position="absolute";d.fadeTo(W,1,0.25);if(W.parentNode){W.parentNode.style.left=o.style.left;W.parentNode.style.top=o.style.top}if(j.fullscreen&&m.jwGetStretching()==a.utils.stretching.EXACTFIT&&!d.isMobile()){var U=document.createElement("div");d.stretch(a.utils.stretching.UNIFORM,U,d.getElementWidth(o),d.getElementHeight(o),A,N);d.stretch(a.utils.stretching.EXACTFIT,W,d.parseDimension(U.style.width),d.parseDimension(U.style.height),W.videoWidth?W.videoWidth:400,W.videoHeight?W.videoHeight:300);b(W,{left:U.style.left,top:U.style.top})}else{if(!c){d.stretch(m.jwGetStretching(),W,d.getElementWidth(o),d.getElementHeight(o),W.videoWidth?W.videoWidth:400,W.videoHeight?W.videoHeight:300)}}}else{var V=j.plugins.object.display.getDisplayElement();if(V){j.getMedia().resize(d.parseDimension(V.style.width),d.parseDimension(V.style.height))}else{j.getMedia().resize(d.parseDimension(o.style.width),d.parseDimension(o.style.height))}}};var z=this.getComponentPosition=function(V){var W={position:"absolute",margin:0,padding:0,top:null};var U=j.plugins.config[V].currentPosition.toLowerCase();switch(U.toUpperCase()){case a.html5.view.positions.TOP:W.top=d.parseDimension(o.style.top);W.left=d.parseDimension(o.style.left);W.width=d.getElementWidth(o)-d.parseDimension(o.style.left)-d.parseDimension(o.style.right);W.height=j.plugins.object[V].height;o.style[U]=d.parseDimension(o.style[U])+j.plugins.object[V].height+"px";o.style.height=d.getElementHeight(o)-W.height+"px";break;case a.html5.view.positions.RIGHT:W.top=d.parseDimension(o.style.top);W.right=d.parseDimension(o.style.right);W.width=j.plugins.object[V].width;W.height=d.getElementHeight(o)-d.parseDimension(o.style.top)-d.parseDimension(o.style.bottom);o.style.width=d.getElementWidth(o)-W.width+"px";break;case a.html5.view.positions.BOTTOM:W.left=d.parseDimension(o.style.left);W.width=d.getElementWidth(o)-d.parseDimension(o.style.left)-d.parseDimension(o.style.right);W.height=j.plugins.object[V].height;W.bottom=d.parseDimension(o.style.bottom+r);r+=W.height;o.style.height=d.getElementHeight(o)-W.height+"px";break;case a.html5.view.positions.LEFT:W.top=d.parseDimension(o.style.top);W.left=d.parseDimension(o.style.left);W.width=j.plugins.object[V].width;W.height=d.getElementHeight(o)-d.parseDimension(o.style.top)-d.parseDimension(o.style.bottom);o.style[U]=d.parseDimension(o.style[U])+j.plugins.object[V].width+"px";o.style.width=d.getElementWidth(o)-W.width+"px";break;default:break}return W};this.resize=G;var J,k,Q;var C=this.fullscreen=function(W){if(c){return}var Y;try{Y=j.getMedia().getDisplayElement()}catch(X){}if(W){k=j.width;Q=j.height}var aa={position:"fixed",width:"100%",height:"100%",top:0,left:0,zIndex:2147483000},Z={position:"relative",height:k,width:Q,zIndex:0};if(B()&&Y&&Y.webkitSupportsFullscreen){if(W&&!Y.webkitDisplayingFullscreen){try{b(Y,aa);d.transform(Y);J=o.style.display;o.style.display="none";Y.webkitEnterFullscreen()}catch(V){}}else{if(!W){b(Y,Z);l();if(Y.webkitDisplayingFullscreen){try{Y.webkitExitFullscreen()}catch(V){}}o.style.display=J}}E=false}else{if(W){document.onkeydown=M;clearInterval(P);var U=d.getBoundingClientRect(document.body);j.width=Math.abs(U.left)+Math.abs(U.right);j.height=window.innerHeight;b(R,aa);aa.zIndex=1;if(j.getMedia()&&j.getMedia().getDisplayElement()){b(j.getMedia().getDisplayElement(),aa)}aa.zIndex=2;b(o,aa);E=true}else{document.onkeydown="";j.width=g;j.height=t;b(R,Z);E=false}G(j.width,j.height)}};function T(U){return([a.html5.view.positions.TOP,a.html5.view.positions.RIGHT,a.html5.view.positions.BOTTOM,a.html5.view.positions.LEFT].toString().indexOf(U.toUpperCase())>-1)}function B(){if(m.jwGetState()!=a.api.events.state.IDLE&&!E&&(j.getMedia()&&j.getMedia().getDisplayElement()&&j.getMedia().getDisplayElement().webkitSupportsFullscreen)&&d.useNativeFullscreen()){return true}return false}this.setupInstream=function(U,V){d.css(_instreamArea,{display:"block",position:"absolute"});o.style.display="none";_instreamArea.appendChild(U);u=V;S=true};var D=this.destroyInstream=function(){_instreamArea.style.display="none";_instreamArea.innerHTML="";o.style.display="block";u=null;S=false;G(j.width,j.height)}};a.html5.view.positions={TOP:"TOP",RIGHT:"RIGHT",BOTTOM:"BOTTOM",LEFT:"LEFT",OVER:"OVER",NONE:"NONE"}})(jwplayer);(function(a){var b={backgroundcolor:"",margin:10,font:"Arial,sans-serif",fontsize:10,fontcolor:parseInt("000000",16),fontstyle:"normal",fontweight:"bold",buttoncolor:parseInt("ffffff",16),position:a.html5.view.positions.BOTTOM,idlehide:false,hideplaylistcontrols:false,forcenextprev:false,layout:{left:{position:"left",elements:[{name:"play",type:"button"},{name:"divider",type:"divider"},{name:"prev",type:"button"},{name:"divider",type:"divider"},{name:"next",type:"button"},{name:"divider",type:"divider"},{name:"elapsed",type:"text"}]},center:{position:"center",elements:[{name:"time",type:"slider"}]},right:{position:"right",elements:[{name:"duration",type:"text"},{name:"blank",type:"button"},{name:"divider",type:"divider"},{name:"mute",type:"button"},{name:"volume",type:"slider"},{name:"divider",type:"divider"},{name:"fullscreen",type:"button"}]}}};_utils=a.utils;_css=_utils.css;_hide=function(c){_css(c,{display:"none"})};_show=function(c){_css(c,{display:"block"})};a.html5.controlbar=function(m,Y){window.controlbar=this;var l=m;var D=_utils.extend({},b,l.skin.getComponentSettings("controlbar"),Y);if(D.position==a.html5.view.positions.NONE||typeof a.html5.view.positions[D.position]=="undefined"){return}if(_utils.mapLength(l.skin.getComponentLayout("controlbar"))>0){D.layout=l.skin.getComponentLayout("controlbar")}var ag;var R;var af;var E;var w="none";var h;var k;var ah;var g;var f;var z;var S={};var q=false;var c={};var Q=-1;var ac;var j=false;var p;var d;var V=false;var G=false;var H;var aa=new a.html5.eventdispatcher();_utils.extend(this,aa);function K(){if(!ac){ac=l.skin.getSkinElement("controlbar","background");if(!ac){ac={width:0,height:0,src:null}}}return ac}function O(){af=0;E=0;R=0;if(!q){var ap={height:K().height,backgroundColor:D.backgroundcolor};ag=document.createElement("div");ag.id=l.id+"_jwplayer_controlbar";_css(ag,ap)}var ao=(l.skin.getSkinElement("controlbar","capLeft"));var an=(l.skin.getSkinElement("controlbar","capRight"));if(ao){y("capLeft","left",false,ag)}ad("background",ag,{position:"absolute",height:K().height,left:(ao?ao.width:0),zIndex:0},"img");if(K().src){S.background.src=K().src}ad("elements",ag,{position:"relative",height:K().height,zIndex:1});if(an){y("capRight","right",false,ag)}}this.getDisplayElement=function(){return ag};this.resize=function(ap,an){T();_utils.cancelAnimation(ag);f=ap;z=an;if(G!=l.jwGetFullscreen()){G=l.jwGetFullscreen();if(!G){Z()}d=undefined}var ao=x();J({id:l.id,duration:ah,position:k});v({id:l.id,bufferPercent:g});return ao};this.show=function(){if(j){j=false;_show(ag);W()}};this.hide=function(){if(!j){j=true;_hide(ag);ae()}};function r(){var ao=["timeSlider","volumeSlider","timeSliderRail","volumeSliderRail"];for(var ap in ao){var an=ao[ap];if(typeof S[an]!="undefined"){c[an]=_utils.getBoundingClientRect(S[an])}}}var e;function Z(an){if(j){return}clearTimeout(p);if(D.position==a.html5.view.positions.OVER||l.jwGetFullscreen()){switch(l.jwGetState()){case a.api.events.state.PAUSED:case a.api.events.state.IDLE:if(ag&&ag.style.opacity<1&&(!D.idlehide||_utils.exists(an))){e=false;setTimeout(function(){if(!e){X()}},100)}if(D.idlehide){p=setTimeout(function(){A()},2000)}break;default:e=true;if(an){X()}p=setTimeout(function(){A()},2000);break}}else{X()}}function A(){if(!j){ae();if(ag.style.opacity==1){_utils.cancelAnimation(ag);_utils.fadeTo(ag,0,0.1,1,0)}}}function X(){if(!j){W();if(ag.style.opacity==0){_utils.cancelAnimation(ag);_utils.fadeTo(ag,1,0.1,0,0)}}}function I(an){return function(){if(V&&d!=an){d=an;aa.sendEvent(an,{component:"controlbar",boundingRect:P()})}}}var W=I(a.api.events.JWPLAYER_COMPONENT_SHOW);var ae=I(a.api.events.JWPLAYER_COMPONENT_HIDE);function P(){if(D.position==a.html5.view.positions.OVER||l.jwGetFullscreen()){return _utils.getDimensions(ag)}else{return{x:0,y:0,width:0,height:0}}}function ad(ar,aq,ap,an){var ao;if(!q){if(!an){an="div"}ao=document.createElement(an);S[ar]=ao;ao.id=ag.id+"_"+ar;aq.appendChild(ao)}else{ao=document.getElementById(ag.id+"_"+ar)}if(_utils.exists(ap)){_css(ao,ap)}return ao}function N(){if(l.jwGetHeight()<=40){D.layout=_utils.clone(D.layout);for(var an=0;an<D.layout.left.elements.length;an++){if(D.layout.left.elements[an].name=="fullscreen"){D.layout.left.elements.splice(an,1)}}for(an=0;an<D.layout.right.elements.length;an++){if(D.layout.right.elements[an].name=="fullscreen"){D.layout.right.elements.splice(an,1)}}o()}am(D.layout.left);am(D.layout.center);am(D.layout.right)}function am(aq,an){var ar=aq.position=="right"?"right":"left";var ap=_utils.extend([],aq.elements);if(_utils.exists(an)){ap.reverse()}var aq=ad(aq.position+"Group",S.elements,{"float":"left",styleFloat:"left",cssFloat:"left",height:"100%"});for(var ao=0;ao<ap.length;ao++){C(ap[ao],ar,aq)}}function L(){return R++}function C(ar,au,aw){var aq,ao,ap,an,ax;if(!aw){aw=S.elements}if(ar.type=="divider"){y("divider"+L(),au,true,aw,undefined,ar.width,ar.element);return}switch(ar.name){case"play":y("playButton",au,false,aw);y("pauseButton",au,true,aw);U("playButton","jwPlay");U("pauseButton","jwPause");break;case"prev":y("prevButton",au,true,aw);U("prevButton","jwPlaylistPrev");break;case"stop":y("stopButton",au,true,aw);U("stopButton","jwStop");break;case"next":y("nextButton",au,true,aw);U("nextButton","jwPlaylistNext");break;case"elapsed":y("elapsedText",au,true,aw,null,null,l.skin.getSkinElement("controlbar","elapsedBackground"));break;case"time":ao=!_utils.exists(l.skin.getSkinElement("controlbar","timeSliderCapLeft"))?0:l.skin.getSkinElement("controlbar","timeSliderCapLeft").width;ap=!_utils.exists(l.skin.getSkinElement("controlbar","timeSliderCapRight"))?0:l.skin.getSkinElement("controlbar","timeSliderCapRight").width;aq=au=="left"?ao:ap;ax={height:K().height,position:"relative","float":"left",styleFloat:"left",cssFloat:"left"};var at=ad("timeSlider",aw,ax);y("timeSliderCapLeft",au,true,at,"relative");y("timeSliderRail",au,false,at,"relative");y("timeSliderBuffer",au,false,at,"absolute");y("timeSliderProgress",au,false,at,"absolute");y("timeSliderThumb",au,false,at,"absolute");y("timeSliderCapRight",au,true,at,"relative");ab("time");break;case"fullscreen":y("fullscreenButton",au,false,aw);y("normalscreenButton",au,true,aw);U("fullscreenButton","jwSetFullscreen",true);U("normalscreenButton","jwSetFullscreen",false);break;case"volume":ao=!_utils.exists(l.skin.getSkinElement("controlbar","volumeSliderCapLeft"))?0:l.skin.getSkinElement("controlbar","volumeSliderCapLeft").width;ap=!_utils.exists(l.skin.getSkinElement("controlbar","volumeSliderCapRight"))?0:l.skin.getSkinElement("controlbar","volumeSliderCapRight").width;aq=au=="left"?ao:ap;an=l.skin.getSkinElement("controlbar","volumeSliderRail").width+ao+ap;ax={height:K().height,position:"relative",width:an,"float":"left",styleFloat:"left",cssFloat:"left"};var av=ad("volumeSlider",aw,ax);y("volumeSliderCapLeft",au,false,av,"relative");y("volumeSliderRail",au,false,av,"relative");y("volumeSliderProgress",au,false,av,"absolute");y("volumeSliderThumb",au,false,av,"absolute");y("volumeSliderCapRight",au,false,av,"relative");ab("volume");break;case"mute":y("muteButton",au,false,aw);y("unmuteButton",au,true,aw);U("muteButton","jwSetMute",true);U("unmuteButton","jwSetMute",false);break;case"duration":y("durationText",au,true,aw,null,null,l.skin.getSkinElement("controlbar","durationBackground"));break}}function y(aq,au,ao,ax,ar,an,ap){if(_utils.exists(l.skin.getSkinElement("controlbar",aq))||aq.indexOf("Text")>0||aq.indexOf("divider")===0){var at={height:"100%",position:ar?ar:"relative",display:"block","float":"left",styleFloat:"left",cssFloat:"left"};if((aq.indexOf("next")===0||aq.indexOf("prev")===0)&&(l.jwGetPlaylist().length<2||D.hideplaylistcontrols.toString()=="true")){if(D.forcenextprev.toString()!="true"){ao=false;at.display="none"}}var ay;if(aq.indexOf("Text")>0){aq.innerhtml="00:00";at.font=D.fontsize+"px/"+(K().height+1)+"px "+D.font;at.color=D.fontcolor;at.textAlign="center";at.fontWeight=D.fontweight;at.fontStyle=D.fontstyle;at.cursor="default";if(ap){at.background="url("+ap.src+") no-repeat center";at.backgroundSize="100% "+K().height+"px"}at.padding="0 5px"}else{if(aq.indexOf("divider")===0){if(an){if(!isNaN(parseInt(an))){ay=parseInt(an)}}else{if(ap){var av=l.skin.getSkinElement("controlbar",ap);if(av){at.background="url("+av.src+") repeat-x center left";ay=av.width}}else{at.background="url("+l.skin.getSkinElement("controlbar","divider").src+") repeat-x center left";ay=l.skin.getSkinElement("controlbar","divider").width}}}else{at.background="url("+l.skin.getSkinElement("controlbar",aq).src+") repeat-x center left";ay=l.skin.getSkinElement("controlbar",aq).width}}if(au=="left"){if(ao){af+=ay}}else{if(au=="right"){if(ao){E+=ay}}}if(_utils.typeOf(ax)=="undefined"){ax=S.elements}at.width=ay;if(q){_css(S[aq],at)}else{var aw=ad(aq,ax,at);if(_utils.exists(l.skin.getSkinElement("controlbar",aq+"Over"))){aw.onmouseover=function(az){aw.style.backgroundImage=["url(",l.skin.getSkinElement("controlbar",aq+"Over").src,")"].join("")};aw.onmouseout=function(az){aw.style.backgroundImage=["url(",l.skin.getSkinElement("controlbar",aq).src,")"].join("")}}if(aq.indexOf("divider")==0){aw.setAttribute("class","divider")}aw.innerHTML="&nbsp;"}}}function F(){l.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,B);l.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,t);l.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_BUFFER,v);l.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,s);l.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_TIME,J);l.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_MUTE,al);l.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_VOLUME,n);l.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_COMPLETE,M)}function B(){if(!D.hideplaylistcontrols){if(l.jwGetPlaylist().length>1||D.forcenextprev.toString()=="true"){_show(S.nextButton);_show(S.prevButton)}else{_hide(S.nextButton);_hide(S.prevButton)}x();ai()}}function t(an){ah=l.jwGetPlaylist()[an.index].duration;Q=-1;J({id:l.id,duration:ah,position:0});v({id:l.id,bufferProgress:0})}function ai(){J({id:l.id,duration:l.jwGetDuration(),position:0});v({id:l.id,bufferProgress:0});al({id:l.id,mute:l.jwGetMute()});s({id:l.id,newstate:a.api.events.state.IDLE});n({id:l.id,volume:l.jwGetVolume()})}function U(ap,aq,ao){if(q){return}if(_utils.exists(l.skin.getSkinElement("controlbar",ap))){var an=S[ap];if(_utils.exists(an)){_css(an,{cursor:"pointer"});if(aq=="fullscreen"){an.onmouseup=function(ar){ar.stopPropagation();l.jwSetFullscreen(!l.jwGetFullscreen())}}else{an.onmouseup=function(ar){ar.stopPropagation();if(_utils.exists(ao)){l[aq](ao)}else{l[aq]()}}}}}}function ab(an){if(q){return}var ao=S[an+"Slider"];_css(S.elements,{cursor:"pointer"});_css(ao,{cursor:"pointer"});ao.onmousedown=function(ap){w=an};ao.onmouseup=function(ap){ap.stopPropagation();ak(ap.pageX)};ao.onmousemove=function(ap){if(w=="time"){h=true;var aq=ap.pageX-c[an+"Slider"].left-window.pageXOffset;_css(S[w+"SliderThumb"],{left:aq})}}}function ak(ao){h=false;var an;if(w=="time"){an=ao-c.timeSliderRail.left+window.pageXOffset;var aq=an/c.timeSliderRail.width*ah;if(aq<0){aq=0}else{if(aq>ah){aq=ah-3}}if(l.jwGetState()==a.api.events.state.PAUSED||l.jwGetState()==a.api.events.state.IDLE){l.jwPlay()}l.jwSeek(aq)}else{if(w=="volume"){an=ao-c.volumeSliderRail.left-window.pageXOffset;var ap=Math.round(an/c.volumeSliderRail.width*100);if(ap<10){ap=0}else{if(ap>100){ap=100}}if(l.jwGetMute()){l.jwSetMute(false)}l.jwSetVolume(ap)}}w="none"}function v(ao){if(_utils.exists(ao.bufferPercent)){g=ao.bufferPercent}if(c.timeSliderRail){var aq=l.skin.getSkinElement("controlbar","timeSliderCapLeft");var ap=c.timeSliderRail.width;var an=isNaN(Math.round(ap*g/100))?0:Math.round(ap*g/100);_css(S.timeSliderBuffer,{width:an,left:aq?aq.width:0})}}function al(an){if(an.mute){_hide(S.muteButton);_show(S.unmuteButton);_hide(S.volumeSliderProgress)}else{_show(S.muteButton);_hide(S.unmuteButton);_show(S.volumeSliderProgress)}}function s(an){if(an.newstate==a.api.events.state.BUFFERING||an.newstate==a.api.events.state.PLAYING){_show(S.pauseButton);_hide(S.playButton)}else{_hide(S.pauseButton);_show(S.playButton)}Z();if(an.newstate==a.api.events.state.IDLE){_hide(S.timeSliderBuffer);_hide(S.timeSliderProgress);_hide(S.timeSliderThumb);J({id:l.id,duration:l.jwGetDuration(),position:0})}else{_show(S.timeSliderBuffer);if(an.newstate!=a.api.events.state.BUFFERING){_show(S.timeSliderProgress);_show(S.timeSliderThumb)}}}function M(an){v({bufferPercent:0});J(_utils.extend(an,{position:0,duration:ah}))}function J(at){if(_utils.exists(at.position)){k=at.position}var ao=false;if(_utils.exists(at.duration)&&at.duration!=ah){ah=at.duration;ao=true}var aq=(k===ah===0)?0:k/ah;var av=c.timeSliderRail;if(av){var ap=isNaN(Math.round(av.width*aq))?0:Math.round(av.width*aq);var au=l.skin.getSkinElement("controlbar","timeSliderCapLeft");var ar=ap+(au?au.width:0);if(S.timeSliderProgress){_css(S.timeSliderProgress,{width:ap,left:au?au.width:0});if(!h){if(S.timeSliderThumb){S.timeSliderThumb.style.left=ar+"px"}}}}if(S.durationText){S.durationText.innerHTML=_utils.timeFormat(ah)}if(S.elapsedText){var an=_utils.timeFormat(k);S.elapsedText.innerHTML=an;if(Q!=an.length){ao=true;Q=an.length}}if(ao){x()}}function o(){var an=S.elements.childNodes;var at,aq;for(var ap=0;ap<an.length;ap++){var ar=an[ap].childNodes;for(var ao in ar){if(isNaN(parseInt(ao,10))){continue}if(ar[ao].id.indexOf(ag.id+"_divider")===0&&aq&&aq.id.indexOf(ag.id+"_divider")===0&&ar[ao].style.backgroundImage==aq.style.backgroundImage){ar[ao].style.display="none"}else{if(ar[ao].id.indexOf(ag.id+"_divider")===0&&at&&at.style.display!="none"){ar[ao].style.display="block"}}if(ar[ao].style.display!="none"){aq=ar[ao]}at=ar[ao]}}}function aj(){if(l.jwGetFullscreen()){_show(S.normalscreenButton);_hide(S.fullscreenButton)}else{_hide(S.normalscreenButton);_show(S.fullscreenButton)}if(l.jwGetState()==a.api.events.state.BUFFERING||l.jwGetState()==a.api.events.state.PLAYING){_show(S.pauseButton);_hide(S.playButton)}else{_hide(S.pauseButton);_show(S.playButton)}if(l.jwGetMute()==true){_hide(S.muteButton);_show(S.unmuteButton);_hide(S.volumeSliderProgress)}else{_show(S.muteButton);_hide(S.unmuteButton);_show(S.volumeSliderProgress)}}function x(){o();aj();var ap={width:f};var ax={"float":"left",styleFloat:"left",cssFloat:"left"};if(D.position==a.html5.view.positions.OVER||l.jwGetFullscreen()){ap.left=D.margin;ap.width-=2*D.margin;ap.top=z-K().height-D.margin;ap.height=K().height}var ar=l.skin.getSkinElement("controlbar","capLeft");var av=l.skin.getSkinElement("controlbar","capRight");ax.width=ap.width-(ar?ar.width:0)-(av?av.width:0);var aq=_utils.getBoundingClientRect(S.leftGroup).width;var au=_utils.getBoundingClientRect(S.rightGroup).width;var at=ax.width-aq-au-1;var ao=at;var an=l.skin.getSkinElement("controlbar","timeSliderCapLeft");var aw=l.skin.getSkinElement("controlbar","timeSliderCapRight");if(_utils.exists(an)){ao-=an.width}if(_utils.exists(aw)){ao-=aw.width}S.timeSlider.style.width=at+"px";S.timeSliderRail.style.width=ao+"px";_css(ag,ap);_css(S.elements,ax);_css(S.background,ax);r();return ap}function n(at){if(_utils.exists(S.volumeSliderRail)){var ap=isNaN(at.volume/100)?1:at.volume/100;var aq=_utils.parseDimension(S.volumeSliderRail.style.width);var an=isNaN(Math.round(aq*ap))?0:Math.round(aq*ap);var au=_utils.parseDimension(S.volumeSliderRail.style.right);var ao=(!_utils.exists(l.skin.getSkinElement("controlbar","volumeSliderCapLeft")))?0:l.skin.getSkinElement("controlbar","volumeSliderCapLeft").width;_css(S.volumeSliderProgress,{width:an,left:ao});if(S.volumeSliderThumb){var ar=(an-Math.round(_utils.parseDimension(S.volumeSliderThumb.style.width)/2));ar=Math.min(Math.max(ar,0),aq-_utils.parseDimension(S.volumeSliderThumb.style.width));_css(S.volumeSliderThumb,{left:ar})}if(_utils.exists(S.volumeSliderCapLeft)){_css(S.volumeSliderCapLeft,{left:0})}}}function T(){try{var ao=(l.id.indexOf("_instream")>0?l.id.replace("_instream",""):l.id);H=document.getElementById(ao);H.addEventListener("mousemove",Z)}catch(an){_utils.log("Could not add mouse listeners to controlbar: "+an)}}function u(){O();N();r();q=true;F();D.idlehide=(D.idlehide.toString().toLowerCase()=="true");if(D.position==a.html5.view.positions.OVER&&D.idlehide){ag.style.opacity=0;V=true}else{ag.style.opacity=1;setTimeout((function(){V=true;W()}),1)}T();ai()}u();return this}})(jwplayer);(function(b){var a=["width","height","state","playlist","item","position","buffer","duration","volume","mute","fullscreen"];var c=b.utils;b.html5.controller=function(o,K,f,h){var n=o,m=f,j=h,y=K,M=true,G=-1,A=false,d=false,P,C=[],q=false;var D=(c.exists(m.config.debug)&&(m.config.debug.toString().toLowerCase()=="console")),N=new b.html5.eventdispatcher(y.id,D);c.extend(this,N);function L(T){if(q){N.sendEvent(T.type,T)}else{C.push(T)}}function s(T){if(!q){q=true;N.sendEvent(b.api.events.JWPLAYER_READY,T);if(b.utils.exists(window.playerReady)){playerReady(T)}if(b.utils.exists(window[f.config.playerReady])){window[f.config.playerReady](T)}while(C.length>0){var V=C.shift();N.sendEvent(V.type,V)}if(f.config.autostart&&!b.utils.isIOS()){O()}while(x.length>0){var U=x.shift();B(U.method,U.arguments)}}}m.addGlobalListener(L);m.addEventListener(b.api.events.JWPLAYER_MEDIA_BUFFER_FULL,function(){m.getMedia().play()});m.addEventListener(b.api.events.JWPLAYER_MEDIA_TIME,function(T){if(T.position>=m.playlist[m.item].start&&G>=0){m.playlist[m.item].start=G;G=-1}});m.addEventListener(b.api.events.JWPLAYER_MEDIA_COMPLETE,function(T){setTimeout(E,25)});m.addEventListener(b.api.events.JWPLAYER_PLAYLIST_LOADED,O);m.addEventListener(b.api.events.JWPLAYER_FULLSCREEN,p);function F(){try{P=F;if(!A){A=true;N.sendEvent(b.api.events.JWPLAYER_MEDIA_BEFOREPLAY);A=false;if(d){d=false;P=null;return}}v(m.item);if(m.playlist[m.item].levels[0].file.length>0){if(M||m.state==b.api.events.state.IDLE){m.getMedia().load(m.playlist[m.item]);M=false}else{if(m.state==b.api.events.state.PAUSED){m.getMedia().play()}}}return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T);P=null}return false}function e(){try{if(m.playlist[m.item].levels[0].file.length>0){switch(m.state){case b.api.events.state.PLAYING:case b.api.events.state.BUFFERING:if(m.getMedia()){m.getMedia().pause()}break;default:if(A){d=true}}}return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function z(T){try{if(m.playlist[m.item].levels[0].file.length>0){if(typeof T!="number"){T=parseFloat(T)}switch(m.state){case b.api.events.state.IDLE:if(G<0){G=m.playlist[m.item].start;m.playlist[m.item].start=T}if(!A){F()}break;case b.api.events.state.PLAYING:case b.api.events.state.PAUSED:case b.api.events.state.BUFFERING:m.seek(T);break}}return true}catch(U){N.sendEvent(b.api.events.JWPLAYER_ERROR,U)}return false}function w(T){P=null;if(!c.exists(T)){T=true}try{if((m.state!=b.api.events.state.IDLE||T)&&m.getMedia()){m.getMedia().stop(T)}if(A){d=true}return true}catch(U){N.sendEvent(b.api.events.JWPLAYER_ERROR,U)}return false}function k(){try{if(m.playlist[m.item].levels[0].file.length>0){if(m.config.shuffle){v(S())}else{if(m.item+1==m.playlist.length){v(0)}else{v(m.item+1)}}}if(m.state!=b.api.events.state.IDLE){var U=m.state;m.state=b.api.events.state.IDLE;N.sendEvent(b.api.events.JWPLAYER_PLAYER_STATE,{oldstate:U,newstate:b.api.events.state.IDLE})}F();return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function I(){try{if(m.playlist[m.item].levels[0].file.length>0){if(m.config.shuffle){v(S())}else{if(m.item===0){v(m.playlist.length-1)}else{v(m.item-1)}}}if(m.state!=b.api.events.state.IDLE){var U=m.state;m.state=b.api.events.state.IDLE;N.sendEvent(b.api.events.JWPLAYER_PLAYER_STATE,{oldstate:U,newstate:b.api.events.state.IDLE})}F();return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function S(){var T=null;if(m.playlist.length>1){while(!c.exists(T)){T=Math.floor(Math.random()*m.playlist.length);if(T==m.item){T=null}}}else{T=0}return T}function H(U){if(!m.playlist||!m.playlist[U]){return false}try{if(m.playlist[U].levels[0].file.length>0){var V=m.state;if(V!==b.api.events.state.IDLE){if(m.playlist[m.item]&&m.playlist[m.item].provider==m.playlist[U].provider){w(false)}else{w()}}v(U);F()}return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function v(T){if(!m.playlist[T]){return}m.setActiveMediaProvider(m.playlist[T]);if(m.item!=T){m.item=T;M=true;N.sendEvent(b.api.events.JWPLAYER_PLAYLIST_ITEM,{index:T})}}function g(U){try{v(m.item);var V=m.getMedia();switch(typeof(U)){case"number":V.volume(U);break;case"string":V.volume(parseInt(U,10));break}m.setVolume(U);return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function r(U){try{v(m.item);var V=m.getMedia();if(typeof U=="undefined"){V.mute(!m.mute);m.setMute(!m.mute)}else{if(U.toString().toLowerCase()=="true"){V.mute(true);m.setMute(true)}else{V.mute(false);m.setMute(false)}}return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function J(U,T){try{m.width=U;m.height=T;j.resize(U,T);N.sendEvent(b.api.events.JWPLAYER_RESIZE,{width:m.width,height:m.height});return true}catch(V){N.sendEvent(b.api.events.JWPLAYER_ERROR,V)}return false}function u(U,V){try{if(typeof U=="undefined"){U=!m.fullscreen}if(typeof V=="undefined"){V=true}if(U!=m.fullscreen){m.fullscreen=(U.toString().toLowerCase()=="true");j.fullscreen(m.fullscreen);if(V){N.sendEvent(b.api.events.JWPLAYER_FULLSCREEN,{fullscreen:m.fullscreen})}N.sendEvent(b.api.events.JWPLAYER_RESIZE,{width:m.width,height:m.height})}return true}catch(T){N.sendEvent(b.api.events.JWPLAYER_ERROR,T)}return false}function R(T){try{w();if(A){d=false}m.loadPlaylist(T);if(m.playlist[m.item].provider){v(m.item);if(m.config.autostart.toString().toLowerCase()=="true"&&!c.isIOS()&&!A){F()}return true}else{return false}}catch(U){N.sendEvent(b.api.events.JWPLAYER_ERROR,U)}return false}function O(T){if(!c.isIOS()){v(m.item);if(m.config.autostart.toString().toLowerCase()=="true"&&!c.isIOS()){F()}}}function p(T){u(T.fullscreen,false)}function t(){try{return m.getMedia().detachMedia()}catch(T){return null}}function l(){try{var T=m.getMedia().attachMedia();if(typeof P=="function"){P()}}catch(U){return null}}b.html5.controller.repeatoptions={LIST:"LIST",ALWAYS:"ALWAYS",SINGLE:"SINGLE",NONE:"NONE"};function E(){if(m.state!=b.api.events.state.IDLE){return}P=E;switch(m.config.repeat.toUpperCase()){case b.html5.controller.repeatoptions.SINGLE:F();break;case b.html5.controller.repeatoptions.ALWAYS:if(m.item==m.playlist.length-1&&!m.config.shuffle){H(0)}else{k()}break;case b.html5.controller.repeatoptions.LIST:if(m.item==m.playlist.length-1&&!m.config.shuffle){w();v(0)}else{k()}break;default:w();break}}var x=[];function Q(T){return function(){if(q){B(T,arguments)}else{x.push({method:T,arguments:arguments})}}}function B(V,U){var T=[];for(i=0;i<U.length;i++){T.push(U[i])}V.apply(this,T)}this.play=Q(F);this.pause=Q(e);this.seek=Q(z);this.stop=Q(w);this.next=Q(k);this.prev=Q(I);this.item=Q(H);this.setVolume=Q(g);this.setMute=Q(r);this.resize=Q(J);this.setFullscreen=Q(u);this.load=Q(R);this.playerReady=s;this.detachMedia=t;this.attachMedia=l;this.beforePlay=function(){return A};this.destroy=function(){if(m.getMedia()){m.getMedia().destroy()}}}})(jwplayer);(function(a){a.html5.defaultSkin=function(){this.text='<?xml version="1.0" ?><skin author="LongTail Video" name="Five" version="1.1"><components><component name="controlbar"><settings><setting name="margin" value="20"/><setting name="fontsize" value="11"/><setting name="fontcolor" value="0x000000"/></settings><layout><group position="left"><button name="play"/><divider name="divider"/><button name="prev"/><divider name="divider"/><button name="next"/><divider name="divider"/><text name="elapsed"/></group><group position="center"><slider name="time"/></group><group position="right"><text name="duration"/><divider name="divider"/><button name="blank"/><divider name="divider"/><button name="mute"/><slider name="volume"/><divider name="divider"/><button name="fullscreen"/></group></layout><elements><element name="background" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAIAAABvFaqvAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAElJREFUOI3t1LERACAMQlFgGvcfxNIhHMK4gsUvUviOmgtNsiAZkBSEKxKEnCYkkQrJn/YwbUNiSDDYRZaQRDaShv+oX9GBZEIuK+8hXVLs+/YAAAAASUVORK5CYII="/><element name="blankButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAYCAYAAAAyJzegAAAAFElEQVQYV2P8//8/AzpgHBUc7oIAGZdH0RjKN8EAAAAASUVORK5CYII="/><element name="capLeft" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAYAAAA7zJfaAAAAQElEQVQIWz3LsRGAMADDQJ0XB5bMINABZ9GENGrszxhjT2WLSqxEJG2JQrTMdV2q5LpOAvyRaVmsi7WdeZ/7+AAaOTq7BVrfOQAAAABJRU5ErkJggg=="/><element name="capRight" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAYAAAA7zJfaAAAAQElEQVQIWz3LsRGAMADDQJ0XB5bMINABZ9GENGrszxhjT2WLSqxEJG2JQrTMdV2q5LpOAvyRaVmsi7WdeZ/7+AAaOTq7BVrfOQAAAABJRU5ErkJggg=="/><element name="divider" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAIAAAC0rgCNAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADhJREFUCB0FwcENgEAAw7Aq+893g8APUILNOQcbFRktVGqUVFRkWNz3xTa2sUaLNUosKlRUvvf5AdbWOTtzmzyWAAAAAElFTkSuQmCC"/><element name="playButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAANUlEQVR42u2RsQkAAAjD/NTTPaW6dXLrINJA1kBpGPMAjDWmOgp1HFQXx+b1KOefO4oxY57R73YnVYCQUCQAAAAASUVORK5CYII="/><element name="pauseButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAIUlEQVQ4jWNgGAWjYOiD/0gYG3/U0FFDB4Oho2AUDAYAAEwiL9HrpdMVAAAAAElFTkSuQmCC"/><element name="prevButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAQklEQVQ4y2NgGAWjYOiD/1AMA/JAfB5NjCJD/YH4PRaLyDa0H4lNNUP/DxlD59PCUBCIp3ZEwYA+NZLUKBgFgwEAAN+HLX9sB8u8AAAAAElFTkSuQmCC"/><element name="nextButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAQElEQVQ4y2NgGAWjYOiD/0B8Hojl0cT+U2ooCL8HYn9qGwrD/bQw9P+QMXQ+tSMqnpoRBUpS+tRMUqNgFAwGAADxZy1/mHvFnAAAAABJRU5ErkJggg=="/><element name="timeSliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAOElEQVRIDe3BwQkAIRADwAhhw/nU/kWwUK+KPITMABFh19Y+F0acY8CJvX9wYpXgRElwolSIiMf9ZWEDhtwurFsAAAAASUVORK5CYII="/><element name="timeSliderBuffer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAN0lEQVRIDe3BwQkAMQwDMBcc55mRe9zi7RR+FCwBEWG39vcfGHFm4MTuhhMlwYlVBSdKhYh43AW/LQMKm1spzwAAAABJRU5ErkJggg=="/><element name="timeSliderProgress" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAIElEQVRIiWNgGAWjYBTQBfynMR61YCRYMApGwSigMQAAiVWPcbq6UkIAAAAASUVORK5CYII="/><element name="timeSliderThumb" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAAYCAYAAAA/OUfnAAAAO0lEQVQYlWP4//8/Awwz0JgDBP/BeN6Cxf/hnI2btiI4u/fsQ3AOHjqK4Jw4eQbBOX/hEoKDYjSd/AMA4cS4mfLsorgAAAAASUVORK5CYII="/><element name="muteButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAYCAYAAADKx8xXAAAAJklEQVQ4y2NgGAUjDcwH4v/kaPxPikZkxcNVI9mBQ5XoGAWDFwAAsKAXKQQmfbUAAAAASUVORK5CYII="/><element name="unmuteButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAYCAYAAADKx8xXAAAAMklEQVQ4y2NgGAWDHPyntub5xBr6Hwv/Pzk2/yfVG/8psRFE25Oq8T+tQnsIaB4FVAcAi2YVysVY52AAAAAASUVORK5CYII="/><element name="volumeSliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYAgMAAACdGdVrAAAACVBMVEUAAACmpqampqbBXAu8AAAAAnRSTlMAgJsrThgAAAArSURBVAhbY2AgErBAyA4I2QEhOyBkB4TsYOhAoaCCUCUwDTDtMMNgRuMHAFB5FoGH5T0UAAAAAElFTkSuQmCC"/><element name="volumeSliderProgress" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYAgMAAACdGdVrAAAACVBMVEUAAAAAAAAAAACDY+nAAAAAAnRSTlMAgJsrThgAAAArSURBVAhbY2AgErBAyA4I2QEhOyBkB4TsYOhAoaCCUCUwDTDtMMNgRuMHAFB5FoGH5T0UAAAAAElFTkSuQmCC"/><element name="volumeSliderCapRight" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAYCAYAAAAyJzegAAAAFElEQVQYV2P8//8/AzpgHBUc7oIAGZdH0RjKN8EAAAAASUVORK5CYII="/><element name="fullscreenButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAQklEQVRIiWNgGAWjYMiD/0iYFDmSLbDHImdPLQtgBpEiR7Zl2NijAA5oEkT/0Whi5UiyAJ8BVMsHNMtoo2AUDAIAAGdcIN3IDNXoAAAAAElFTkSuQmCC"/><element name="normalscreenButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAP0lEQVRIx2NgGAWjYMiD/1RSQ5QB/wmIUWzJfzx8qhj+n4DYCAY0DyJ7PBbYU8sHMEvwiZFtODXUjIJRMJgBACpWIN2ZxdPTAAAAAElFTkSuQmCC"/></elements></component><component name="display"><elements><element name="background" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyAQMAAAAk8RryAAAABlBMVEUAAAAAAAClZ7nPAAAAAnRSTlOZpuml+rYAAAASSURBVBhXY2AYJuA/GBwY6jQAyDyoK8QcL4QAAAAASUVORK5CYII="/><element name="playIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAiUlEQVR42u3XSw2AMBREURwgAQlIQAISKgUpSEFKJeCg5b0E0kWBTVcD9ySTsL0Jn9IBAAAA+K2UUrBlW/Rr5ZDoIeeuoFkxJD9ss03aIXXQqB9SttoG7ZA6qNcOKdttiwcJh9RB+iFl4SshkRBuLR72+9cvH0SOKI2HRo7x/Fi1/uoCAAAAwLsD8ki99IlO2dQAAAAASUVORK5CYII="/><element name="muteIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAVUlEQVR42u3WMQrAIAxAUW/g/SdvGmvpoOBeSHgPsjj5QTANAACARCJilIhYM0tEvJM+Ik3Id9E957kQIb+F3OdCPC0hPkQriqWx9hp/x/QGAABQyAPLB22VGrpLDgAAAABJRU5ErkJggg=="/><element name="errorIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAA/0lEQVR42u2U0QmEMBAF7cASLMESUoIlpARLSCkpwRJSgiWkhOvAXD4WsgRkyaG5DbyB+Yvg8KITAAAAAAAYk+u61mwk15EjPtlEfihmqIiZR1Qx80ghjgdUuiHXGHSVsoag0x6x8DUoyjD5KovmEJ9NTDMRPIT0mtdIUkjlonuNohO+Ha99DTmkuGgKCTcvebAzx82ZoCWC3/3aIMWSRucaxcjORSFY4xpFdjYJGp1rFGcyCYZ/RVh6AUnfcNZ2zih3/mGj1jVCdiNDwyrq1rA/xMdeEXvDVdnYc1vDc3uPkDObXrlaxbNHSOohQhr/WOeLEWfWTgAAAAAAADzNF9sHJ7PJ57MlAAAAAElFTkSuQmCC"/><element name="bufferIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAACBklEQVR42u3Zv0sCYRzH8USTzOsHHEWGkC1HgaDgkktGDjUYtDQ01RDSljQ1BLU02+rk1NTm2NLq4Nx/0L/h9fnCd3j4cnZe1/U8xiO8h3uurufF0/3COd/3/0UWYiEWYiEWYiGJQ+J8xuPxKhXjEMZANinjIZhkGuVRNioE4wVURo4JkHm0xKWmhRAc1bh1EyCUw5BcBIjHiApKa4CErko6DEJwuRo6IRKzyJD8FJAyI3Zp2zRImiBcRhlfo5RtlxCcE3CcDNpGrhYIT2IhAJKilO0VRmzJ32fAMTpBTS0QMfGwlcuKMRftE0DJ0wCJdcOsCkBdXP3Mh9CEFUBTPS9mDZJBG6io4aqVzMdCokCw9H3kT6j/C/9iDdSeUMNC7DkyyxAs/Rk6Qss8FPWRZgdVtUH4DjxEn1zxh+/zj1wHlf4MQhNGrwqA6sY40U8JonRJwEQh+AO3AvCG6gHv4U7IY4krxkroWoAOkoQMGfCBrgIm+YBGqPENpIJ66CJg3x66Y0gnSUidAEEnNr9jjLiWMn5DiWP0OC/oAsCgkq43xBdGDMQr7YASP/vEkHvdl1+JOCcEV5sC4hGEOzTlPuKgd0b0xD4JkRcOgnRRTjdErkYhAsQVq6IdUuPJtmk7BCL3t/h88cx91pKQkI/pkDx6pmYTIjEoxiHsN1YWYiEWYiEWknhflZ5IErA5nr8AAAAASUVORK5CYII="/></elements></component><component name="dock"><settings><setting name="fontcolor" value="0xffffff"/></settings><elements><element name="button" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyAQMAAAAk8RryAAAABlBMVEUAAAAAAAClZ7nPAAAAAnRSTlOZpuml+rYAAAASSURBVBhXY2AYJuA/GBwY6jQAyDyoK8QcL4QAAAAASUVORK5CYII="/></elements></component><component name="playlist"><settings><setting name="backgroundcolor" value="0xe8e8e8"/></settings><elements><element name="item" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAIAAAC1nk4lAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHBJREFUaN7t2MENwCAMBEEe9N8wSKYC/D8YV7CyJoRkVtVImxkZPQInMxoP0XiIxkM0HsGbjjSNBx544IEHHnjggUe/6UQeey0PIh7XTftGxKPj4eXCtLsHHh+ZxkO0Iw8PR55Ni8ZD9Hu/EAoP0dc5RRg9qeRjVF8AAAAASUVORK5CYII="/><element name="sliderCapTop" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAHCAYAAADnCQYGAAAAFUlEQVQokWP8//8/A7UB46ihI9hQAKt6FPPXhVGHAAAAAElFTkSuQmCC"/><element name="sliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAUCAYAAABiS3YzAAAAKElEQVQ4y2P4//8/Az68bNmy/+iYkB6GUUNHDR01dNTQUUNHDaXcUABUDOKhcxnsSwAAAABJRU5ErkJggg=="/><element name="sliderThumb" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAUCAYAAABiS3YzAAAAJUlEQVQ4T2P4//8/Ay4MBP9xYbz6Rg0dNXTU0FFDRw0dNZRyQwHH4NBa7GJsXAAAAABJRU5ErkJggg=="/><element name="sliderCapBottom" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAHCAYAAADnCQYGAAAAFUlEQVQokWP8//8/A7UB46ihI9hQAKt6FPPXhVGHAAAAAElFTkSuQmCC"/></elements></component></components></skin>';this.xml=null;if(window.DOMParser){parser=new DOMParser();this.xml=parser.parseFromString(this.text,"text/xml")}else{this.xml=new ActiveXObject("Microsoft.XMLDOM");this.xml.async="false";this.xml.loadXML(this.text)}return this}})(jwplayer);(function(a){_utils=a.utils;_css=_utils.css;_hide=function(b){_css(b,{display:"none"})};_show=function(b){_css(b,{display:"block"})};a.html5.display=function(k,K){var j={icons:true,showmute:false};var X=_utils.extend({},j,K);var h=k;var W={};var e;var w;var z;var T;var u;var M;var E;var N=!_utils.exists(h.skin.getComponentSettings("display").bufferrotation)?15:parseInt(h.skin.getComponentSettings("display").bufferrotation,10);var s=!_utils.exists(h.skin.getComponentSettings("display").bufferinterval)?100:parseInt(h.skin.getComponentSettings("display").bufferinterval,10);var D=-1;var v=a.api.events.state.IDLE;var O=true;var d;var C=false,V=true;var p="";var g=false;var o=false;var m;var y,R;var L=new a.html5.eventdispatcher();_utils.extend(this,L);var H={display:{style:{cursor:"pointer",top:0,left:0,overflow:"hidden"},click:n},display_icon:{style:{cursor:"pointer",position:"absolute",top:((h.skin.getSkinElement("display","background").height-h.skin.getSkinElement("display","playIcon").height)/2),left:((h.skin.getSkinElement("display","background").width-h.skin.getSkinElement("display","playIcon").width)/2),border:0,margin:0,padding:0,zIndex:3,display:"none"}},display_iconBackground:{style:{cursor:"pointer",position:"absolute",top:((w-h.skin.getSkinElement("display","background").height)/2),left:((e-h.skin.getSkinElement("display","background").width)/2),border:0,backgroundImage:(["url(",h.skin.getSkinElement("display","background").src,")"]).join(""),width:h.skin.getSkinElement("display","background").width,height:h.skin.getSkinElement("display","background").height,margin:0,padding:0,zIndex:2,display:"none"}},display_image:{style:{display:"none",width:e,height:w,position:"absolute",cursor:"pointer",left:0,top:0,margin:0,padding:0,textDecoration:"none",zIndex:1}},display_text:{style:{zIndex:4,position:"relative",opacity:0.8,backgroundColor:parseInt("000000",16),color:parseInt("ffffff",16),textAlign:"center",fontFamily:"Arial,sans-serif",padding:"0 5px",fontSize:14}}};h.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,q);h.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_MUTE,q);h.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,P);h.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,q);h.jwAddEventListener(a.api.events.JWPLAYER_ERROR,r);Q();function Q(){W.display=G("div","display");W.display_text=G("div","display_text");W.display.appendChild(W.display_text);W.display_image=G("img","display_image");W.display_image.onerror=function(Y){_hide(W.display_image)};W.display_image.onload=B;W.display_icon=G("div","display_icon");W.display_iconBackground=G("div","display_iconBackground");W.display.appendChild(W.display_image);W.display_iconBackground.appendChild(W.display_icon);W.display.appendChild(W.display_iconBackground);f();setTimeout((function(){o=true;if(X.icons.toString()=="true"){J()}}),1)}this.getDisplayElement=function(){return W.display};this.resize=function(Z,Y){if(h.jwGetFullscreen()&&_utils.isMobile()){return}_css(W.display,{width:Z,height:Y});_css(W.display_text,{width:(Z-10),top:((Y-_utils.getBoundingClientRect(W.display_text).height)/2)});_css(W.display_iconBackground,{top:((Y-h.skin.getSkinElement("display","background").height)/2),left:((Z-h.skin.getSkinElement("display","background").width)/2)});if(e!=Z||w!=Y){e=Z;w=Y;d=undefined;J()}if(!h.jwGetFullscreen()){y=Z;R=Y}c();q({})};this.show=function(){if(g){g=false;t(h.jwGetState())}};this.hide=function(){if(!g){F();g=true}};function B(Y){z=W.display_image.naturalWidth;T=W.display_image.naturalHeight;c();if(h.jwGetState()==a.api.events.state.IDLE||h.jwGetPlaylist()[h.jwGetPlaylistIndex()].provider=="sound"){_css(W.display_image,{display:"block",opacity:0});_utils.fadeTo(W.display_image,1,0.1)}C=false}function c(){if(h.jwGetFullscreen()&&h.jwGetStretching()==a.utils.stretching.EXACTFIT){var Y=document.createElement("div");_utils.stretch(a.utils.stretching.UNIFORM,Y,e,w,y,R);_utils.stretch(a.utils.stretching.EXACTFIT,W.display_image,_utils.parseDimension(Y.style.width),_utils.parseDimension(Y.style.height),z,T);_css(W.display_image,{left:Y.style.left,top:Y.style.top})}else{_utils.stretch(h.jwGetStretching(),W.display_image,e,w,z,T)}}function G(Y,aa){var Z=document.createElement(Y);Z.id=h.id+"_jwplayer_"+aa;_css(Z,H[aa].style);return Z}function f(){for(var Y in W){if(_utils.exists(H[Y].click)){W[Y].onclick=H[Y].click}}}function n(Y){if(typeof Y.preventDefault!="undefined"){Y.preventDefault()}else{Y.returnValue=false}if(typeof m=="function"){m(Y);return}else{if(h.jwGetState()!=a.api.events.state.PLAYING){h.jwPlay()}else{h.jwPause()}}}function U(Y){if(E){F();return}W.display_icon.style.backgroundImage=(["url(",h.skin.getSkinElement("display",Y).src,")"]).join("");_css(W.display_icon,{width:h.skin.getSkinElement("display",Y).width,height:h.skin.getSkinElement("display",Y).height,top:(h.skin.getSkinElement("display","background").height-h.skin.getSkinElement("display",Y).height)/2,left:(h.skin.getSkinElement("display","background").width-h.skin.getSkinElement("display",Y).width)/2});b();if(_utils.exists(h.skin.getSkinElement("display",Y+"Over"))){W.display_icon.onmouseover=function(Z){W.display_icon.style.backgroundImage=["url(",h.skin.getSkinElement("display",Y+"Over").src,")"].join("")};W.display_icon.onmouseout=function(Z){W.display_icon.style.backgroundImage=["url(",h.skin.getSkinElement("display",Y).src,")"].join("")}}else{W.display_icon.onmouseover=null;W.display_icon.onmouseout=null}}function F(){if(X.icons.toString()=="true"){_hide(W.display_icon);_hide(W.display_iconBackground);S()}}function b(){if(!g&&X.icons.toString()=="true"){_show(W.display_icon);_show(W.display_iconBackground);J()}}function r(Y){E=true;F();W.display_text.innerHTML=Y.message;_show(W.display_text);W.display_text.style.top=((w-_utils.getBoundingClientRect(W.display_text).height)/2)+"px"}function I(){V=false;W.display_image.style.display="none"}function P(){v=""}function q(Y){if((Y.type==a.api.events.JWPLAYER_PLAYER_STATE||Y.type==a.api.events.JWPLAYER_PLAYLIST_ITEM)&&E){E=false;_hide(W.display_text)}var Z=h.jwGetState();if(Z==v){return}v=Z;if(D>=0){clearTimeout(D)}if(O||h.jwGetState()==a.api.events.state.PLAYING||h.jwGetState()==a.api.events.state.PAUSED){t(h.jwGetState())}else{D=setTimeout(l(h.jwGetState()),500)}}function l(Y){return(function(){t(Y)})}function t(Y){if(_utils.exists(M)){clearInterval(M);M=null;_utils.animations.rotate(W.display_icon,0)}switch(Y){case a.api.events.state.BUFFERING:if(_utils.isIPod()){I();F()}else{if(h.jwGetPlaylist()[h.jwGetPlaylistIndex()].provider=="sound"){x()}u=0;M=setInterval(function(){u+=N;_utils.animations.rotate(W.display_icon,u%360)},s);U("bufferIcon");O=true}break;case a.api.events.state.PAUSED:if(!_utils.isIPod()){if(h.jwGetPlaylist()[h.jwGetPlaylistIndex()].provider!="sound"){_css(W.display_image,{background:"transparent no-repeat center center"})}U("playIcon");O=true}break;case a.api.events.state.IDLE:if(h.jwGetPlaylist()[h.jwGetPlaylistIndex()]&&h.jwGetPlaylist()[h.jwGetPlaylistIndex()].image){x()}else{I()}U("playIcon");O=true;break;default:if(h.jwGetPlaylist()[h.jwGetPlaylistIndex()]&&h.jwGetPlaylist()[h.jwGetPlaylistIndex()].provider=="sound"){if(_utils.isIPod()){I();O=false}else{x()}}else{I();O=false}if(h.jwGetMute()&&X.showmute){U("muteIcon")}else{F()}break}D=-1}function x(){if(h.jwGetPlaylist()[h.jwGetPlaylistIndex()]){var Y=h.jwGetPlaylist()[h.jwGetPlaylistIndex()].image;if(Y){if(Y!=p){p=Y;C=true;W.display_image.src=_utils.getAbsolutePath(Y)}else{if(!(C||V)){V=true;W.display_image.style.opacity=0;W.display_image.style.display="block";_utils.fadeTo(W.display_image,1,0.1)}}}}}function A(Y){return function(){if(!o){return}if(!g&&d!=Y){d=Y;L.sendEvent(Y,{component:"display",boundingRect:_utils.getDimensions(W.display_iconBackground)})}}}var J=A(a.api.events.JWPLAYER_COMPONENT_SHOW);var S=A(a.api.events.JWPLAYER_COMPONENT_HIDE);this.setAlternateClickHandler=function(Y){m=Y};this.revertAlternateClickHandler=function(){m=undefined};return this}})(jwplayer);(function(a){var c=a.utils;var b=c.css;a.html5.dock=function(w,D){function x(){return{align:a.html5.view.positions.RIGHT}}var n=c.extend({},x(),D);if(n.align=="FALSE"){return}var j={};var A=[];var k;var F;var f=false;var C=false;var g={x:0,y:0,width:0,height:0};var z;var o;var y;var m=new a.html5.eventdispatcher();c.extend(this,m);var r=document.createElement("div");r.id=w.id+"_jwplayer_dock";r.style.opacity=1;p();w.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,q);this.getDisplayElement=function(){return r};this.setButton=function(K,H,I,J){if(!H&&j[K]){c.arrays.remove(A,K);r.removeChild(j[K].div);delete j[K]}else{if(H){if(!j[K]){j[K]={}}j[K].handler=H;j[K].outGraphic=I;j[K].overGraphic=J;if(!j[K].div){A.push(K);j[K].div=document.createElement("div");j[K].div.style.position="absolute";r.appendChild(j[K].div);j[K].div.appendChild(document.createElement("div"));j[K].div.childNodes[0].style.position="relative";j[K].div.childNodes[0].style.width="100%";j[K].div.childNodes[0].style.height="100%";j[K].div.childNodes[0].style.zIndex=10;j[K].div.childNodes[0].style.cursor="pointer";j[K].div.appendChild(document.createElement("img"));j[K].div.childNodes[1].style.position="absolute";j[K].div.childNodes[1].style.left=0;j[K].div.childNodes[1].style.top=0;if(w.skin.getSkinElement("dock","button")){j[K].div.childNodes[1].src=w.skin.getSkinElement("dock","button").src}j[K].div.childNodes[1].style.zIndex=9;j[K].div.childNodes[1].style.cursor="pointer";j[K].div.onmouseover=function(){if(j[K].overGraphic){j[K].div.childNodes[0].style.background=h(j[K].overGraphic)}if(w.skin.getSkinElement("dock","buttonOver")){j[K].div.childNodes[1].src=w.skin.getSkinElement("dock","buttonOver").src}};j[K].div.onmouseout=function(){if(j[K].outGraphic){j[K].div.childNodes[0].style.background=h(j[K].outGraphic)}if(w.skin.getSkinElement("dock","button")){j[K].div.childNodes[1].src=w.skin.getSkinElement("dock","button").src}};if(w.skin.getSkinElement("dock","button")){j[K].div.childNodes[1].src=w.skin.getSkinElement("dock","button").src}}if(j[K].outGraphic){j[K].div.childNodes[0].style.background=h(j[K].outGraphic)}else{if(j[K].overGraphic){j[K].div.childNodes[0].style.background=h(j[K].overGraphic)}}if(H){j[K].div.onclick=function(L){L.preventDefault();a(w.id).callback(K);if(j[K].overGraphic){j[K].div.childNodes[0].style.background=h(j[K].overGraphic)}if(w.skin.getSkinElement("dock","button")){j[K].div.childNodes[1].src=w.skin.getSkinElement("dock","button").src}}}}}l(k,F)};function h(H){return"url("+H+") no-repeat center center"}function t(H){}function l(H,T){p();if(A.length>0){var I=10;var S=I;var P=-1;var Q=w.skin.getSkinElement("dock","button").height;var O=w.skin.getSkinElement("dock","button").width;var M=H-O-I;var R,L;if(n.align==a.html5.view.positions.LEFT){P=1;M=I}for(var J=0;J<A.length;J++){var U=Math.floor(S/T);if((S+Q+I)>((U+1)*T)){S=((U+1)*T)+I;U=Math.floor(S/T)}var K=j[A[J]].div;K.style.top=(S%T)+"px";K.style.left=(M+(w.skin.getSkinElement("dock","button").width+I)*U*P)+"px";var N={x:c.parseDimension(K.style.left),y:c.parseDimension(K.style.top),width:O,height:Q};if(!R||(N.x<=R.x&&N.y<=R.y)){R=N}if(!L||(N.x>=L.x&&N.y>=L.y)){L=N}K.style.width=O+"px";K.style.height=Q+"px";S+=w.skin.getSkinElement("dock","button").height+I}g={x:R.x,y:R.y,width:L.x-R.x+L.width,height:R.y-L.y+L.height}}if(C!=w.jwGetFullscreen()||k!=H||F!=T){k=H;F=T;C=w.jwGetFullscreen();z=undefined;setTimeout(s,1)}}function d(H){return function(){if(!f&&z!=H&&A.length>0){z=H;m.sendEvent(H,{component:"dock",boundingRect:g})}}}function q(H){if(c.isMobile()){if(H.newstate==a.api.events.state.IDLE){v()}else{e()}}else{B()}}function B(H){if(f){return}clearTimeout(y);if(D.position==a.html5.view.positions.OVER||w.jwGetFullscreen()){switch(w.jwGetState()){case a.api.events.state.PAUSED:case a.api.events.state.IDLE:if(r&&r.style.opacity<1&&(!D.idlehide||c.exists(H))){E()}if(D.idlehide){y=setTimeout(function(){u()},2000)}break;default:if(c.exists(H)){E()}y=setTimeout(function(){u()},2000);break}}else{E()}}var s=d(a.api.events.JWPLAYER_COMPONENT_SHOW);var G=d(a.api.events.JWPLAYER_COMPONENT_HIDE);this.resize=l;var v=function(){b(r,{display:"block"});if(f){f=false;s()}};var e=function(){b(r,{display:"none"});if(!f){G();f=true}};function u(){if(!f){G();if(r.style.opacity==1){c.cancelAnimation(r);c.fadeTo(r,0,0.1,1,0)}}}function E(){if(!f){s();if(r.style.opacity==0){c.cancelAnimation(r);c.fadeTo(r,1,0.1,0,0)}}}function p(){try{o=document.getElementById(w.id);o.addEventListener("mousemove",B)}catch(H){c.log("Could not add mouse listeners to dock: "+H)}}this.hide=e;this.show=v;return this}})(jwplayer);(function(a){a.html5.eventdispatcher=function(d,b){var c=new a.events.eventdispatcher(b);a.utils.extend(this,c);this.sendEvent=function(e,f){if(!a.utils.exists(f)){f={}}a.utils.extend(f,{id:d,version:a.version,type:e});c.sendEvent(e,f)}}})(jwplayer);(function(a){var b=a.utils;a.html5.instream=function(y,m,x,z){var t={controlbarseekable:"always",controlbarpausable:true,controlbarstoppable:true,playlistclickable:true};var v,A,C=y,E=m,j=x,w=z,r,H,o,G,e,f,g,l,q,h=false,k,d,n=this;this.load=function(M,K){c();h=true;A=b.extend(t,K);v=a.html5.playlistitem(M);F();d=document.createElement("div");d.id=n.id+"_instream_container";w.detachMedia();r=g.getDisplayElement();f=E.playlist[E.item];e=C.jwGetState();if(e==a.api.events.state.BUFFERING||e==a.api.events.state.PLAYING){r.pause()}H=r.src?r.src:r.currentSrc;o=r.innerHTML;G=r.currentTime;q=new a.html5.display(n,b.extend({},E.plugins.config.display));q.setAlternateClickHandler(function(N){if(_fakemodel.state==a.api.events.state.PAUSED){n.jwInstreamPlay()}else{D(a.api.events.JWPLAYER_INSTREAM_CLICK,N)}});d.appendChild(q.getDisplayElement());if(!b.isMobile()){l=new a.html5.controlbar(n,b.extend({},E.plugins.config.controlbar,{}));if(E.plugins.config.controlbar.position==a.html5.view.positions.OVER){d.appendChild(l.getDisplayElement())}else{var L=E.plugins.object.controlbar.getDisplayElement().parentNode;L.appendChild(l.getDisplayElement())}}j.setupInstream(d,r);p();g.load(v)};this.jwInstreamDestroy=function(K){if(!h){return}h=false;if(e!=a.api.events.state.IDLE){g.load(f,false);g.stop(false)}else{g.stop(true)}g.detachMedia();j.destroyInstream();if(l){try{l.getDisplayElement().parentNode.removeChild(l.getDisplayElement())}catch(L){}}D(a.api.events.JWPLAYER_INSTREAM_DESTROYED,{reason:(K?"complete":"destroyed")},true);w.attachMedia();if(e==a.api.events.state.BUFFERING||e==a.api.events.state.PLAYING){r.play();if(E.playlist[E.item]==f){E.getMedia().seek(G)}}return};this.jwInstreamAddEventListener=function(K,L){k.addEventListener(K,L)};this.jwInstreamRemoveEventListener=function(K,L){k.removeEventListener(K,L)};this.jwInstreamPlay=function(){if(!h){return}g.play(true)};this.jwInstreamPause=function(){if(!h){return}g.pause(true)};this.jwInstreamSeek=function(K){if(!h){return}g.seek(K)};this.jwInstreamGetState=function(){if(!h){return undefined}return _fakemodel.state};this.jwInstreamGetPosition=function(){if(!h){return undefined}return _fakemodel.position};this.jwInstreamGetDuration=function(){if(!h){return undefined}return _fakemodel.duration};this.playlistClickable=function(){return(!h||A.playlistclickable.toString().toLowerCase()=="true")};function s(){_fakemodel=new a.html5.model(this,E.getMedia()?E.getMedia().getDisplayElement():E.container,E);k=new a.html5.eventdispatcher();C.jwAddEventListener(a.api.events.JWPLAYER_RESIZE,p);C.jwAddEventListener(a.api.events.JWPLAYER_FULLSCREEN,p)}function c(){_fakemodel.setMute(E.mute);_fakemodel.setVolume(E.volume)}function F(){if(!g){g=new a.html5.mediavideo(_fakemodel,E.getMedia()?E.getMedia().getDisplayElement():E.container);g.addGlobalListener(I);g.addEventListener(a.api.events.JWPLAYER_MEDIA_META,J);g.addEventListener(a.api.events.JWPLAYER_MEDIA_COMPLETE,u);g.addEventListener(a.api.events.JWPLAYER_MEDIA_BUFFER_FULL,B)}g.attachMedia()}function I(K){if(h){D(K.type,K)}}function B(K){if(h){g.play()}}function u(K){if(h){setTimeout(function(){n.jwInstreamDestroy(true)},10)}}function J(K){if(K.metadata.width&&K.metadata.height){j.resizeMedia()}}function D(K,L,M){if(h||M){k.sendEvent(K,L)}}function p(){var K=E.plugins.object.display.getDisplayElement().style;if(l){var L=E.plugins.object.controlbar.getDisplayElement().style;l.resize(b.parseDimension(K.width),b.parseDimension(K.height));_css(l.getDisplayElement(),b.extend({},L,{zIndex:1001,opacity:1}))}if(q){q.resize(b.parseDimension(K.width),b.parseDimension(K.height));_css(q.getDisplayElement(),b.extend({},K,{zIndex:1000}))}if(j){j.resizeMedia()}}this.jwPlay=function(K){if(A.controlbarpausable.toString().toLowerCase()=="true"){this.jwInstreamPlay()}};this.jwPause=function(K){if(A.controlbarpausable.toString().toLowerCase()=="true"){this.jwInstreamPause()}};this.jwStop=function(){if(A.controlbarstoppable.toString().toLowerCase()=="true"){this.jwInstreamDestroy();C.jwStop()}};this.jwSeek=function(K){switch(A.controlbarseekable.toLowerCase()){case"always":this.jwInstreamSeek(K);break;case"backwards":if(_fakemodel.position>K){this.jwInstreamSeek(K)}break}};this.jwGetPosition=function(){};this.jwGetDuration=function(){};this.jwGetWidth=C.jwGetWidth;this.jwGetHeight=C.jwGetHeight;this.jwGetFullscreen=C.jwGetFullscreen;this.jwSetFullscreen=C.jwSetFullscreen;this.jwGetVolume=function(){return E.volume};this.jwSetVolume=function(K){g.volume(K);C.jwSetVolume(K)};this.jwGetMute=function(){return E.mute};this.jwSetMute=function(K){g.mute(K);C.jwSetMute(K)};this.jwGetState=function(){return _fakemodel.state};this.jwGetPlaylist=function(){return[v]};this.jwGetPlaylistIndex=function(){return 0};this.jwGetStretching=function(){return E.config.stretching};this.jwAddEventListener=function(L,K){k.addEventListener(L,K)};this.jwRemoveEventListener=function(L,K){k.removeEventListener(L,K)};this.skin=C.skin;this.id=C.id+"_instream";s();return this}})(jwplayer);(function(a){var b={prefix:"",file:"",link:"",linktarget:"_top",margin:8,out:0.5,over:1,timeout:5,hide:true,position:"bottom-left"};_css=a.utils.css;a.html5.logo=function(n,r){var q=n;var u;var d;var t;var h=false;g();function g(){o();q.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,j);c();l()}function o(){if(b.prefix){var v=n.version.split(/\W/).splice(0,2).join("/");if(b.prefix.indexOf(v)<0){b.prefix+=v+"/"}}if(r.position==a.html5.view.positions.OVER){r.position=b.position}try{if(window.location.href.indexOf("https")==0){b.prefix=b.prefix.replace("http://l.longtailvideo.com","https://securel.longtailvideo.com")}}catch(w){}d=a.utils.extend({},b,r)}function c(){t=document.createElement("img");t.id=q.id+"_jwplayer_logo";t.style.display="none";t.onload=function(v){_css(t,k());p()};if(!d.file){return}if(d.file.indexOf("/")>=0){t.src=d.file}else{t.src=d.prefix+d.file}}if(!d.file){return}this.resize=function(w,v){};this.getDisplayElement=function(){return t};function l(){if(d.link){t.onmouseover=f;t.onmouseout=p;t.onclick=s}else{this.mouseEnabled=false}}function s(v){if(typeof v!="undefined"){v.stopPropagation()}if(!h){return}q.jwPause();q.jwSetFullscreen(false);if(d.link){window.open(d.link,d.linktarget)}return}function p(v){if(d.link&&h){t.style.opacity=d.out}return}function f(v){if(h){t.style.opacity=d.over}return}function k(){var x={textDecoration:"none",position:"absolute",cursor:"pointer"};x.display=(d.hide.toString()=="true"&&!h)?"none":"block";var w=d.position.toLowerCase().split("-");for(var v in w){x[w[v]]=parseInt(d.margin)}return x}function m(){if(d.hide.toString()=="true"){t.style.display="block";t.style.opacity=0;a.utils.fadeTo(t,d.out,0.1,parseFloat(t.style.opacity));u=setTimeout(function(){e()},d.timeout*1000)}h=true}function e(){h=false;if(d.hide.toString()=="true"){a.utils.fadeTo(t,0,0.1,parseFloat(t.style.opacity))}}function j(v){if(v.newstate==a.api.events.state.BUFFERING){clearTimeout(u);m()}}return this}})(jwplayer);(function(b){var d={ended:b.api.events.state.IDLE,playing:b.api.events.state.PLAYING,pause:b.api.events.state.PAUSED,buffering:b.api.events.state.BUFFERING};var f=b.utils;var a=f.isMobile();var g,e;var c={};b.html5.mediavideo=function(k,I){var M={abort:A,canplay:r,canplaythrough:r,durationchange:w,emptied:A,ended:r,error:q,loadeddata:w,loadedmetadata:w,loadstart:r,pause:r,play:A,playing:r,progress:G,ratechange:A,seeked:r,seeking:r,stalled:r,suspend:r,timeupdate:Q,volumechange:n,waiting:r,canshowcurrentframe:A,dataunavailable:A,empty:A,load:j,loadedfirstframe:A,webkitfullscreenchange:m};var E={};var N=new b.html5.eventdispatcher();f.extend(this,N);var l=k,D=I,o,h,F,W,H,P,O=false,v=false,z=false,L,J,T;U();this.load=function(Y,Z){if(typeof Z=="undefined"){Z=true}if(!v){return}W=Y;z=(W.duration>0);l.duration=W.duration;f.empty(o);o.style.display="block";o.style.opacity=1;if(g&&e){o.style.width=g;o.style.height=e;g=_previousHieght=0}T=0;s(Y.levels);if(Y.levels&&Y.levels.length>0){if(Y.levels.length==1||f.isIOS()){o.src=Y.levels[0].file}else{if(o.src){o.removeAttribute("src")}for(var X=0;X<Y.levels.length;X++){var aa=o.ownerDocument.createElement("source");aa.src=Y.levels[X].file;o.appendChild(aa);T++}}}else{o.src=Y.file}o.volume=l.volume/100;o.muted=l.mute;if(a){S()}L=J=F=false;l.buffer=0;if(!f.exists(Y.start)){Y.start=0}P=(Y.start>0)?Y.start:-1;u(b.api.events.JWPLAYER_MEDIA_LOADED);if((!a&&Y.levels.length==1)||!O){o.load()}O=false;if(Z){y(b.api.events.state.BUFFERING);u(b.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:0});C()}if(o.videoWidth>0&&o.videoHeight>0){w()}};this.play=function(){if(!v){return}C();if(J){y(b.api.events.state.PLAYING)}else{o.load();y(b.api.events.state.BUFFERING)}o.play()};this.pause=function(){if(!v){return}o.pause();y(b.api.events.state.PAUSED)};this.seek=function(X){if(!v){return}if(!F&&o.readyState>0){if(!(l.duration<=0||isNaN(l.duration))&&!(l.position<=0||isNaN(l.position))){o.currentTime=X;o.play()}}else{P=X}};var B=this.stop=function(X){if(!v){return}if(!f.exists(X)){X=true}t();if(X){J=false;var Y=navigator.userAgent;if(o.webkitSupportsFullscreen){try{o.webkitExitFullscreen()}catch(Z){}}o.style.opacity=0;x();if(f.isIE()){o.src=""}else{o.removeAttribute("src")}f.empty(o);o.load();O=true}if(f.isIPod()){g=o.style.width;e=o.style.height;o.style.width=0;o.style.height=0}else{if(f.isIPad()){o.style.display="none";try{o.webkitExitFullscreen()}catch(aa){}}}y(b.api.events.state.IDLE)};this.fullscreen=function(X){if(X===true){this.resize("100%","100%")}else{this.resize(l.config.width,l.config.height)}};this.resize=function(Y,X){};this.volume=function(X){if(!a){o.volume=X/100;u(b.api.events.JWPLAYER_MEDIA_VOLUME,{volume:(X/100)})}};this.mute=function(X){if(!a){o.muted=X;u(b.api.events.JWPLAYER_MEDIA_MUTE,{mute:X})}};this.getDisplayElement=function(){return o};this.hasChrome=function(){return a&&(h==b.api.events.state.PLAYING)};this.detachMedia=function(){v=false;return this.getDisplayElement()};this.attachMedia=function(){v=true};this.destroy=function(){if(o&&o.parentNode){t();for(var X in M){o.removeEventListener(X,K(X,M[X]),true)}f.empty(o);D=o.parentNode;o.parentNode.removeChild(o);delete c[l.id];o=null}};function K(Y,X){if(E[Y]){return E[Y]}else{E[Y]=function(Z){if(f.exists(Z.target.parentNode)){X(Z)}};return E[Y]}}function U(){h=b.api.events.state.IDLE;v=true;o=p();o.setAttribute("x-webkit-airplay","allow");if(D.parentNode){o.id=D.id;D.parentNode.replaceChild(o,D)}}function p(){var X=c[l.id];if(!X){if(D.tagName.toLowerCase()=="video"){X=D}else{X=document.createElement("video")}c[l.id]=X;if(!X.id){X.id=D.id}}for(var Y in M){X.addEventListener(Y,K(Y,M[Y]),true)}return X}function y(X){if(X==b.api.events.state.PAUSED&&h==b.api.events.state.IDLE){return}if(a){switch(X){case b.api.events.state.PLAYING:S();break;case b.api.events.state.BUFFERING:case b.api.events.state.PAUSED:x();break}}if(h!=X){var Y=h;l.state=h=X;u(b.api.events.JWPLAYER_PLAYER_STATE,{oldstate:Y,newstate:X})}}function A(X){}function n(X){var Y=Math.round(o.volume*100);u(b.api.events.JWPLAYER_MEDIA_VOLUME,{volume:Y},true);u(b.api.events.JWPLAYER_MEDIA_MUTE,{mute:o.muted},true)}function G(Z){if(!v){return}var Y;if(f.exists(Z)&&Z.lengthComputable&&Z.total){Y=Z.loaded/Z.total*100}else{if(f.exists(o.buffered)&&(o.buffered.length>0)){var X=o.buffered.length-1;if(X>=0){Y=o.buffered.end(X)/o.duration*100}}}if(f.useNativeFullscreen()&&f.exists(o.webkitDisplayingFullscreen)){if(l.fullscreen!=o.webkitDisplayingFullscreen){u(b.api.events.JWPLAYER_FULLSCREEN,{fullscreen:o.webkitDisplayingFullscreen},true)}}if(J===false&&h==b.api.events.state.BUFFERING){u(b.api.events.JWPLAYER_MEDIA_BUFFER_FULL);J=true}if(!L){if(Y==100){L=true}if(f.exists(Y)&&(Y>l.buffer)){l.buffer=Math.round(Y);u(b.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:Math.round(Y)})}}}function Q(Y){if(!v){return}if(f.exists(Y)&&f.exists(Y.target)){if(z>0){if(!isNaN(Y.target.duration)&&(isNaN(l.duration)||l.duration<1)){if(Y.target.duration==Infinity){l.duration=0}else{l.duration=Math.round(Y.target.duration*10)/10}}}if(!F&&o.readyState>0){y(b.api.events.state.PLAYING)}if(h==b.api.events.state.PLAYING){if(o.readyState>0&&(P>-1||!F)){F=true;try{if(o.currentTime!=P&&P>-1){o.currentTime=P;P=-1}}catch(X){}o.volume=l.volume/100;o.muted=l.mute}l.position=l.duration>0?(Math.round(Y.target.currentTime*10)/10):0;u(b.api.events.JWPLAYER_MEDIA_TIME,{position:l.position,duration:l.duration});if(l.position>=l.duration&&(l.position>0||l.duration>0)){R();return}}}G(Y)}function j(X){}function r(X){if(!v){return}if(g&&e){o.style.width=g;o.style.height=e;g=_previousHieght=0}if(d[X.type]){if(X.type=="ended"){R()}else{y(d[X.type])}}}function w(Y){if(!v){return}var X=Math.round(o.duration*10)/10;var Z={height:o.videoHeight,width:o.videoWidth,duration:X};if(!z){if((l.duration<X||isNaN(l.duration))&&o.duration!=Infinity){l.duration=X}}u(b.api.events.JWPLAYER_MEDIA_META,{metadata:Z})}function q(Z){if(!v){return}if(h==b.api.events.state.IDLE){return}var Y="There was an error: ";if((Z.target.error&&Z.target.tagName.toLowerCase()=="video")||Z.target.parentNode.error&&Z.target.parentNode.tagName.toLowerCase()=="video"){var X=!f.exists(Z.target.error)?Z.target.parentNode.error:Z.target.error;switch(X.code){case X.MEDIA_ERR_ABORTED:f.log("User aborted the video playback.");return;case X.MEDIA_ERR_NETWORK:Y="A network error caused the video download to fail part-way: ";break;case X.MEDIA_ERR_DECODE:Y="The video playback was aborted due to a corruption problem or because the video used features your browser did not support: ";break;case X.MEDIA_ERR_SRC_NOT_SUPPORTED:Y="The video could not be loaded, either because the server or network failed or because the format is not supported: ";break;default:Y="An unknown error occurred: ";break}}else{if(Z.target.tagName.toLowerCase()=="source"){T--;if(T>0){return}if(f.userAgentMatch(/firefox/i)){f.log("The video could not be loaded, either because the server or network failed or because the format is not supported.");B(false);return}else{Y="The video could not be loaded, either because the server or network failed or because the format is not supported: "}}else{f.log("An unknown error occurred.  Continuing...");return}}B(false);Y+=V();_error=true;u(b.api.events.JWPLAYER_ERROR,{message:Y});return}function V(){var Z="";for(var Y in W.levels){var X=W.levels[Y];var aa=D.ownerDocument.createElement("source");Z+=b.utils.getAbsolutePath(X.file);if(Y<(W.levels.length-1)){Z+=", "}}return Z}function C(){if(!f.exists(H)){H=setInterval(function(){G()},100)}}function t(){clearInterval(H);H=null}function R(){if(h==b.api.events.state.PLAYING){B(false);u(b.api.events.JWPLAYER_MEDIA_BEFORECOMPLETE);u(b.api.events.JWPLAYER_MEDIA_COMPLETE)}}function m(X){if(f.exists(o.webkitDisplayingFullscreen)){if(l.fullscreen&&!o.webkitDisplayingFullscreen){u(b.api.events.JWPLAYER_FULLSCREEN,{fullscreen:false},true)}}}function s(Z){if(Z.length>0&&f.userAgentMatch(/Safari/i)&&!f.userAgentMatch(/Chrome/i)){var X=-1;for(var Y=0;Y<Z.length;Y++){switch(f.extension(Z[Y].file)){case"mp4":if(X<0){X=Y}break;case"webm":Z.splice(Y,1);break}}if(X>0){var aa=Z.splice(X,1)[0];Z.unshift(aa)}}}function S(){setTimeout(function(){o.setAttribute("controls","controls")},100)}function x(){setTimeout(function(){o.removeAttribute("controls")},250)}function u(X,Z,Y){if(v||Y){if(Z){N.sendEvent(X,Z)}else{N.sendEvent(X)}}}}})(jwplayer);(function(a){var c={ended:a.api.events.state.IDLE,playing:a.api.events.state.PLAYING,pause:a.api.events.state.PAUSED,buffering:a.api.events.state.BUFFERING};var b=a.utils.css;a.html5.mediayoutube=function(j,e){var f=new a.html5.eventdispatcher();a.utils.extend(this,f);var l=j;var h=document.getElementById(e.id);var g=a.api.events.state.IDLE;var n,m;function k(p){if(g!=p){var q=g;l.state=p;g=p;f.sendEvent(a.api.events.JWPLAYER_PLAYER_STATE,{oldstate:q,newstate:p})}}this.getDisplayElement=this.detachMedia=function(){return h};this.attachMedia=function(){};this.play=function(){if(g==a.api.events.state.IDLE){f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:100});f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER_FULL);k(a.api.events.state.PLAYING)}else{if(g==a.api.events.state.PAUSED){k(a.api.events.state.PLAYING)}}};this.pause=function(){k(a.api.events.state.PAUSED)};this.seek=function(p){};this.stop=function(p){if(!_utils.exists(p)){p=true}l.position=0;k(a.api.events.state.IDLE);if(p){b(h,{display:"none"})}};this.volume=function(p){l.setVolume(p);f.sendEvent(a.api.events.JWPLAYER_MEDIA_VOLUME,{volume:Math.round(p)})};this.mute=function(p){h.muted=p;f.sendEvent(a.api.events.JWPLAYER_MEDIA_MUTE,{mute:p})};this.resize=function(q,p){if(q*p>0&&n){n.width=m.width=q;n.height=m.height=p}};this.fullscreen=function(p){if(p===true){this.resize("100%","100%")}else{this.resize(l.config.width,l.config.height)}};this.load=function(p){o(p);b(n,{display:"block"});k(a.api.events.state.BUFFERING);f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:0});f.sendEvent(a.api.events.JWPLAYER_MEDIA_LOADED);this.play()};this.hasChrome=function(){return(g!=a.api.events.state.IDLE)};function o(v){var s=v.levels[0].file;s=["http://www.youtube.com/v/",d(s),"&amp;hl=en_US&amp;fs=1&autoplay=1"].join("");n=document.createElement("object");n.id=h.id;n.style.position="absolute";var u={movie:s,allowfullscreen:"true",allowscriptaccess:"always"};for(var p in u){var t=document.createElement("param");t.name=p;t.value=u[p];n.appendChild(t)}m=document.createElement("embed");n.appendChild(m);var q={src:s,type:"application/x-shockwave-flash",allowfullscreen:"true",allowscriptaccess:"always",width:n.width,height:n.height};for(var r in q){m.setAttribute(r,q[r])}n.appendChild(m);n.style.zIndex=2147483000;if(h!=n&&h.parentNode){h.parentNode.replaceChild(n,h)}h=n}function d(q){var p=q.split(/\?|\#\!/);var s="";for(var r=0;r<p.length;r++){if(p[r].substr(0,2)=="v="){s=p[r].substr(2)}}if(s==""){if(q.indexOf("/v/")>=0){s=q.substr(q.indexOf("/v/")+3)}else{if(q.indexOf("youtu.be")>=0){s=q.substr(q.indexOf("youtu.be/")+9)}else{s=q}}}if(s.indexOf("?")>-1){s=s.substr(0,s.indexOf("?"))}if(s.indexOf("&")>-1){s=s.substr(0,s.indexOf("&"))}return s}this.embed=m;return this}})(jwplayer);(function(jwplayer){var _configurableStateVariables=["width","height","start","duration","volume","mute","fullscreen","item","plugins","stretching"];var _utils=jwplayer.utils;jwplayer.html5.model=function(api,container,options){var _api=api;var _container=container;var _cookies=_utils.getCookies();var _model={id:_container.id,playlist:[],state:jwplayer.api.events.state.IDLE,position:0,buffer:0,container:_container,config:{width:480,height:320,item:-1,skin:undefined,file:undefined,image:undefined,start:0,duration:0,bufferlength:5,volume:_cookies.volume?_cookies.volume:90,mute:_cookies.mute&&_cookies.mute.toString().toLowerCase()=="true"?true:false,fullscreen:false,repeat:"",stretching:jwplayer.utils.stretching.UNIFORM,autostart:false,debug:undefined,screencolor:undefined}};var _media;var _eventDispatcher=new jwplayer.html5.eventdispatcher();var _components=["display","logo","controlbar","playlist","dock"];jwplayer.utils.extend(_model,_eventDispatcher);for(var option in options){if(typeof options[option]=="string"){var type=/color$/.test(option)?"color":null;options[option]=jwplayer.utils.typechecker(options[option],type)}var config=_model.config;var path=option.split(".");for(var edge in path){if(edge==path.length-1){config[path[edge]]=options[option]}else{if(!jwplayer.utils.exists(config[path[edge]])){config[path[edge]]={}}config=config[path[edge]]}}}for(var index in _configurableStateVariables){var configurableStateVariable=_configurableStateVariables[index];_model[configurableStateVariable]=_model.config[configurableStateVariable]}var pluginorder=_components.concat([]);if(jwplayer.utils.exists(_model.plugins)){if(typeof _model.plugins=="string"){var userplugins=_model.plugins.split(",");for(var userplugin in userplugins){if(typeof userplugins[userplugin]=="string"){pluginorder.push(userplugins[userplugin].replace(/^\s+|\s+$/g,""))}}}}if(jwplayer.utils.isMobile()){pluginorder=["display","logo","dock","playlist"];if(!jwplayer.utils.exists(_model.config.repeat)){_model.config.repeat="list"}}else{if(_model.config.chromeless){pluginorder=["logo","dock","playlist"];if(!jwplayer.utils.exists(_model.config.repeat)){_model.config.repeat="list"}}}_model.plugins={order:pluginorder,config:{},object:{}};if(typeof _model.config.components!="undefined"){for(var component in _model.config.components){_model.plugins.config[component]=_model.config.components[component]}}var playlistVisible=false;for(var pluginIndex in _model.plugins.order){var pluginName=_model.plugins.order[pluginIndex];var pluginConfig=!jwplayer.utils.exists(_model.plugins.config[pluginName])?{}:_model.plugins.config[pluginName];_model.plugins.config[pluginName]=!jwplayer.utils.exists(_model.plugins.config[pluginName])?pluginConfig:jwplayer.utils.extend(_model.plugins.config[pluginName],pluginConfig);if(!jwplayer.utils.exists(_model.plugins.config[pluginName].position)){if(pluginName=="playlist"){_model.plugins.config[pluginName].position=jwplayer.html5.view.positions.NONE}else{_model.plugins.config[pluginName].position=jwplayer.html5.view.positions.OVER}}else{if(pluginName=="playlist"){playlistVisible=true}_model.plugins.config[pluginName].position=_model.plugins.config[pluginName].position.toString().toUpperCase()}}if(_model.plugins.config.controlbar&&playlistVisible){_model.plugins.config.controlbar.hideplaylistcontrols=true}if(typeof _model.plugins.config.dock!="undefined"){if(typeof _model.plugins.config.dock!="object"){var position=_model.plugins.config.dock.toString().toUpperCase();_model.plugins.config.dock={position:position}}if(typeof _model.plugins.config.dock.position!="undefined"){_model.plugins.config.dock.align=_model.plugins.config.dock.position;_model.plugins.config.dock.position=jwplayer.html5.view.positions.OVER}if(typeof _model.plugins.config.dock.idlehide=="undefined"){try{_model.plugins.config.dock.idlehide=_model.plugins.config.controlbar.idlehide}catch(e){}}}function _loadExternal(playlistfile){var loader=new jwplayer.html5.playlistloader();loader.addEventListener(jwplayer.api.events.JWPLAYER_PLAYLIST_LOADED,function(evt){_model.playlist=new jwplayer.html5.playlist(evt);_loadComplete(true)});loader.addEventListener(jwplayer.api.events.JWPLAYER_ERROR,function(evt){_model.playlist=new jwplayer.html5.playlist({playlist:[]});_loadComplete(false)});loader.load(playlistfile)}function _loadComplete(){if(_model.config.shuffle){_model.item=_getShuffleItem()}else{if(_model.config.item>=_model.playlist.length){_model.config.item=_model.playlist.length-1}else{if(_model.config.item<0){_model.config.item=0}}_model.item=_model.config.item}_model.position=0;_model.duration=_model.playlist.length>0?_model.playlist[_model.item].duration:0;_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_PLAYLIST_LOADED,{playlist:_model.playlist});_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_PLAYLIST_ITEM,{index:_model.item})}_model.loadPlaylist=function(arg){var input;if(typeof arg=="string"){if(arg.indexOf("[")==0||arg.indexOf("{")=="0"){try{input=eval(arg)}catch(err){input=arg}}else{input=arg}}else{input=arg}var config;switch(jwplayer.utils.typeOf(input)){case"object":config=input;break;case"array":config={playlist:input};break;default:config={file:input};break}_model.playlist=new jwplayer.html5.playlist(config);_model.item=_model.config.item>=0?_model.config.item:0;if(!_model.playlist[0].provider&&_model.playlist[0].file){_loadExternal(_model.playlist[0].file)}else{_loadComplete()}};function _getShuffleItem(){var result=null;if(_model.playlist.length>1){while(!jwplayer.utils.exists(result)){result=Math.floor(Math.random()*_model.playlist.length);if(result==_model.item){result=null}}}else{result=0}return result}function forward(evt){switch(evt.type){case jwplayer.api.events.JWPLAYER_MEDIA_LOADED:_container=_media.getDisplayElement();break;case jwplayer.api.events.JWPLAYER_MEDIA_MUTE:this.mute=evt.mute;break;case jwplayer.api.events.JWPLAYER_MEDIA_VOLUME:this.volume=evt.volume;break}_eventDispatcher.sendEvent(evt.type,evt)}var _mediaProviders={};_model.setActiveMediaProvider=function(playlistItem){if(playlistItem.provider=="audio"){playlistItem.provider="sound"}var provider=playlistItem.provider;var current=_media?_media.getDisplayElement():null;if(provider=="sound"||provider=="http"||provider==""){provider="video"}if(!jwplayer.utils.exists(_mediaProviders[provider])){switch(provider){case"video":_media=new jwplayer.html5.mediavideo(_model,current?current:_container);break;case"youtube":_media=new jwplayer.html5.mediayoutube(_model,current?current:_container);break}if(!jwplayer.utils.exists(_media)){return false}_media.addGlobalListener(forward);_mediaProviders[provider]=_media}else{if(_media!=_mediaProviders[provider]){if(_media){_media.stop()}_media=_mediaProviders[provider]}}return true};_model.getMedia=function(){return _media};_model.seek=function(pos){_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_MEDIA_SEEK,{position:_model.position,offset:pos});return _media.seek(pos)};_model.setVolume=function(newVol){_utils.saveCookie("volume",newVol);_model.volume=newVol};_model.setMute=function(state){_utils.saveCookie("mute",state);_model.mute=state};_model.setupPlugins=function(){if(!jwplayer.utils.exists(_model.plugins)||!jwplayer.utils.exists(_model.plugins.order)||_model.plugins.order.length==0){jwplayer.utils.log("No plugins to set up");return _model}for(var i=0;i<_model.plugins.order.length;i++){try{var pluginName=_model.plugins.order[i];if(jwplayer.utils.exists(jwplayer.html5[pluginName])){if(pluginName=="playlist"){_model.plugins.object[pluginName]=new jwplayer.html5.playlistcomponent(_api,_model.plugins.config[pluginName])}else{_model.plugins.object[pluginName]=new jwplayer.html5[pluginName](_api,_model.plugins.config[pluginName])}}else{_model.plugins.order.splice(plugin,plugin+1)}if(typeof _model.plugins.object[pluginName].addGlobalListener=="function"){_model.plugins.object[pluginName].addGlobalListener(forward)}}catch(err){jwplayer.utils.log("Could not setup "+pluginName)}}};return _model}})(jwplayer);(function(a){a.html5.playlist=function(b){var d=[];if(b.playlist&&b.playlist instanceof Array&&b.playlist.length>0){for(var c in b.playlist){if(!isNaN(parseInt(c))){d.push(new a.html5.playlistitem(b.playlist[c]))}}}else{d.push(new a.html5.playlistitem(b))}return d}})(jwplayer);(function(a){var c={size:180,position:a.html5.view.positions.NONE,itemheight:60,thumbs:true,fontcolor:"#000000",overcolor:"",activecolor:"",backgroundcolor:"#f8f8f8",font:"_sans",fontsize:"",fontstyle:"",fontweight:""};var b={_sans:"Arial, Helvetica, sans-serif",_serif:"Times, Times New Roman, serif",_typewriter:"Courier New, Courier, monospace"};_utils=a.utils;_css=_utils.css;_hide=function(d){_css(d,{display:"none"})};_show=function(d){_css(d,{display:"block"})};a.html5.playlistcomponent=function(r,C){var x=r;var e=a.utils.extend({},c,x.skin.getComponentSettings("playlist"),C);if(e.position==a.html5.view.positions.NONE||typeof a.html5.view.positions[e.position]=="undefined"){return}var y;var l;var D;var d;var g;var f;var k=-1;var h={background:undefined,item:undefined,itemOver:undefined,itemImage:undefined,itemActive:undefined};this.getDisplayElement=function(){return y};this.resize=function(G,E){l=G;D=E;if(x.jwGetFullscreen()){_hide(y)}else{var F={display:"block",width:l,height:D};_css(y,F)}};this.show=function(){_show(y)};this.hide=function(){_hide(y)};function j(){y=document.createElement("div");y.id=x.id+"_jwplayer_playlistcomponent";y.style.overflow="hidden";switch(e.position){case a.html5.view.positions.RIGHT:case a.html5.view.positions.LEFT:y.style.width=e.size+"px";break;case a.html5.view.positions.TOP:case a.html5.view.positions.BOTTOM:y.style.height=e.size+"px";break}B();if(h.item){e.itemheight=h.item.height}y.style.backgroundColor="#C6C6C6";x.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,s);x.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,v);x.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,m)}function p(){var E=document.createElement("ul");_css(E,{width:y.style.width,minWidth:y.style.width,height:y.style.height,backgroundColor:e.backgroundcolor,backgroundImage:h.background?"url("+h.background.src+")":"",color:e.fontcolor,listStyle:"none",margin:0,padding:0,fontFamily:b[e.font]?b[e.font]:b._sans,fontSize:(e.fontsize?e.fontsize:11)+"px",fontStyle:e.fontstyle,fontWeight:e.fontweight,overflowY:"auto"});return E}function z(E){return function(){var F=f.getElementsByClassName("item")[E];var G=e.fontcolor;var H=h.item?"url("+h.item.src+")":"";if(E==x.jwGetPlaylistIndex()){if(e.activecolor!==""){G=e.activecolor}if(h.itemActive){H="url("+h.itemActive.src+")"}}_css(F,{color:e.overcolor!==""?e.overcolor:G,backgroundImage:h.itemOver?"url("+h.itemOver.src+")":H})}}function o(E){return function(){var F=f.getElementsByClassName("item")[E];var G=e.fontcolor;var H=h.item?"url("+h.item.src+")":"";if(E==x.jwGetPlaylistIndex()){if(e.activecolor!==""){G=e.activecolor}if(h.itemActive){H="url("+h.itemActive.src+")"}}_css(F,{color:G,backgroundImage:H})}}function q(J){var Q=d[J];var P=document.createElement("li");P.className="item";_css(P,{height:e.itemheight,display:"block",cursor:"pointer",backgroundImage:h.item?"url("+h.item.src+")":"",backgroundSize:"100% "+e.itemheight+"px"});P.onmouseover=z(J);P.onmouseout=o(J);var K=document.createElement("div");var G=new Image();var L=0;var M=0;var N=0;if(w()&&(Q.image||Q["playlist.image"]||h.itemImage)){G.className="image";if(h.itemImage){L=(e.itemheight-h.itemImage.height)/2;M=h.itemImage.width;N=h.itemImage.height}else{M=e.itemheight*4/3;N=e.itemheight}_css(K,{height:N,width:M,"float":"left",styleFloat:"left",cssFloat:"left",margin:"0 5px 0 0",background:"black",overflow:"hidden",margin:L+"px",position:"relative"});_css(G,{position:"relative"});K.appendChild(G);G.onload=function(){a.utils.stretch(a.utils.stretching.FILL,G,M,N,this.naturalWidth,this.naturalHeight)};if(Q["playlist.image"]){G.src=Q["playlist.image"]}else{if(Q.image){G.src=Q.image}else{if(h.itemImage){G.src=h.itemImage.src}}}P.appendChild(K)}var F=l-M-L*2;if(D<e.itemheight*d.length){F-=15}var E=document.createElement("div");_css(E,{position:"relative",height:"100%",overflow:"hidden"});var H=document.createElement("span");if(Q.duration>0){H.className="duration";_css(H,{fontSize:(e.fontsize?e.fontsize:11)+"px",fontWeight:(e.fontweight?e.fontweight:"bold"),width:"40px",height:e.fontsize?e.fontsize+10:20,lineHeight:24,"float":"right",styleFloat:"right",cssFloat:"right"});H.innerHTML=_utils.timeFormat(Q.duration);E.appendChild(H)}var O=document.createElement("span");O.className="title";_css(O,{padding:"5px 5px 0 "+(L?0:"5px"),height:e.fontsize?e.fontsize+10:20,lineHeight:e.fontsize?e.fontsize+10:20,overflow:"hidden","float":"left",styleFloat:"left",cssFloat:"left",width:((Q.duration>0)?F-50:F)-10+"px",fontSize:(e.fontsize?e.fontsize:13)+"px",fontWeight:(e.fontweight?e.fontweight:"bold")});O.innerHTML=Q?Q.title:"";E.appendChild(O);if(Q.description){var I=document.createElement("span");I.className="description";_css(I,{display:"block","float":"left",styleFloat:"left",cssFloat:"left",margin:0,paddingLeft:O.style.paddingLeft,paddingRight:O.style.paddingRight,lineHeight:(e.fontsize?e.fontsize+4:16)+"px",overflow:"hidden",position:"relative"});I.innerHTML=Q.description;E.appendChild(I)}P.appendChild(E);return P}function s(F){y.innerHTML="";d=t();if(!d){return}items=[];f=p();for(var G=0;G<d.length;G++){var E=q(G);E.onclick=A(G);f.appendChild(E);items.push(E)}k=x.jwGetPlaylistIndex();o(k)();y.appendChild(f);if(_utils.isIOS()&&window.iScroll){f.style.height=e.itemheight*d.length+"px";var H=new iScroll(y.id)}}function t(){var F=x.jwGetPlaylist();var G=[];for(var E=0;E<F.length;E++){if(!F[E]["ova.hidden"]){G.push(F[E])}}return G}function A(E){return function(){x.jwPlaylistItem(E);x.jwPlay(true)}}function n(){f.scrollTop=x.jwGetPlaylistIndex()*e.itemheight}function w(){return e.thumbs.toString().toLowerCase()=="true"}function v(E){if(k>=0){o(k)();k=E.index}o(E.index)();n()}function m(){if(e.position==a.html5.view.positions.OVER){switch(x.jwGetState()){case a.api.events.state.IDLE:_show(y);break;default:_hide(y);break}}}function B(){for(var E in h){h[E]=u(E)}}function u(E){return x.skin.getSkinElement("playlist",E)}j();return this}})(jwplayer);(function(b){b.html5.playlistitem=function(d){var e={author:"",date:"",description:"",image:"",link:"",mediaid:"",tags:"",title:"",provider:"",file:"",streamer:"",duration:-1,start:0,currentLevel:-1,levels:[]};var c=b.utils.extend({},e,d);if(c.type){c.provider=c.type;delete c.type}if(c.levels.length===0){c.levels[0]=new b.html5.playlistitemlevel(c)}if(!c.provider){c.provider=a(c.levels[0])}else{c.provider=c.provider.toLowerCase()}return c};function a(e){if(b.utils.isYouTube(e.file)){return"youtube"}else{var f=b.utils.extension(e.file);var c;if(f&&b.utils.extensionmap[f]){if(f=="m3u8"){return"video"}c=b.utils.extensionmap[f].html5}else{if(e.type){c=e.type}}if(c){var d=c.split("/")[0];if(d=="audio"){return"sound"}else{if(d=="video"){return d}}}}return""}})(jwplayer);(function(a){a.html5.playlistitemlevel=function(b){var d={file:"",streamer:"",bitrate:0,width:0};for(var c in d){if(a.utils.exists(b[c])){d[c]=b[c]}}return d}})(jwplayer);(function(a){a.html5.playlistloader=function(){var c=new a.html5.eventdispatcher();a.utils.extend(this,c);this.load=function(e){a.utils.ajax(e,d,b)};function d(g){var f=[];try{var f=a.utils.parsers.rssparser.parse(g.responseXML.firstChild);c.sendEvent(a.api.events.JWPLAYER_PLAYLIST_LOADED,{playlist:new a.html5.playlist({playlist:f})})}catch(h){b("Could not parse the playlist")}}function b(e){c.sendEvent(a.api.events.JWPLAYER_ERROR,{message:e?e:"Could not load playlist an unknown reason."})}}})(jwplayer);(function(a){a.html5.skin=function(){var b={};var c=false;this.load=function(d,e){new a.html5.skinloader(d,function(f){c=true;b=f;e()},function(){new a.html5.skinloader("",function(f){c=true;b=f;e()})})};this.getSkinElement=function(d,e){if(c){try{return b[d].elements[e]}catch(f){a.utils.log("No such skin component / element: ",[d,e])}}return null};this.getComponentSettings=function(d){if(c&&b&&b[d]){return b[d].settings}return null};this.getComponentLayout=function(d){if(c){return b[d].layout}return null}}})(jwplayer);(function(a){a.html5.skinloader=function(f,p,k){var o={};var c=p;var l=k;var e=true;var j;var n=f;var s=false;function m(){if(typeof n!="string"||n===""){d(a.html5.defaultSkin().xml)}else{a.utils.ajax(a.utils.getAbsolutePath(n),function(t){try{if(a.utils.exists(t.responseXML)){d(t.responseXML);return}}catch(u){h()}d(a.html5.defaultSkin().xml)},function(t){d(a.html5.defaultSkin().xml)})}}function d(y){var E=y.getElementsByTagName("component");if(E.length===0){return}for(var H=0;H<E.length;H++){var C=E[H].getAttribute("name");var B={settings:{},elements:{},layout:{}};o[C]=B;var G=E[H].getElementsByTagName("elements")[0].getElementsByTagName("element");for(var F=0;F<G.length;F++){b(G[F],C)}var z=E[H].getElementsByTagName("settings")[0];if(z&&z.childNodes.length>0){var K=z.getElementsByTagName("setting");for(var P=0;P<K.length;P++){var Q=K[P].getAttribute("name");var I=K[P].getAttribute("value");var x=/color$/.test(Q)?"color":null;o[C].settings[Q]=a.utils.typechecker(I,x)}}var L=E[H].getElementsByTagName("layout")[0];if(L&&L.childNodes.length>0){var M=L.getElementsByTagName("group");for(var w=0;w<M.length;w++){var A=M[w];o[C].layout[A.getAttribute("position")]={elements:[]};for(var O=0;O<A.attributes.length;O++){var D=A.attributes[O];o[C].layout[A.getAttribute("position")][D.name]=D.value}var N=A.getElementsByTagName("*");for(var v=0;v<N.length;v++){var t=N[v];o[C].layout[A.getAttribute("position")].elements.push({type:t.tagName});for(var u=0;u<t.attributes.length;u++){var J=t.attributes[u];o[C].layout[A.getAttribute("position")].elements[v][J.name]=J.value}if(!a.utils.exists(o[C].layout[A.getAttribute("position")].elements[v].name)){o[C].layout[A.getAttribute("position")].elements[v].name=t.tagName}}}}e=false;r()}}function r(){clearInterval(j);if(!s){j=setInterval(function(){q()},100)}}function b(y,x){var w=new Image();var t=y.getAttribute("name");var v=y.getAttribute("src");var A;if(v.indexOf("data:image/png;base64,")===0){A=v}else{var u=a.utils.getAbsolutePath(n);var z=u.substr(0,u.lastIndexOf("/"));A=[z,x,v].join("/")}o[x].elements[t]={height:0,width:0,src:"",ready:false,image:w};w.onload=function(B){g(w,t,x)};w.onerror=function(B){s=true;r();l()};w.src=A}function h(){for(var u in o){var w=o[u];for(var t in w.elements){var x=w.elements[t];var v=x.image;v.onload=null;v.onerror=null;delete x.image;delete w.elements[t]}delete o[u]}}function q(){for(var t in o){if(t!="properties"){for(var u in o[t].elements){if(!o[t].elements[u].ready){return}}}}if(e===false){clearInterval(j);c(o)}}function g(t,v,u){if(o[u]&&o[u].elements[v]){o[u].elements[v].height=t.height;o[u].elements[v].width=t.width;o[u].elements[v].src=t.src;o[u].elements[v].ready=true;r()}else{a.utils.log("Loaded an image for a missing element: "+u+"."+v)}}m()}})(jwplayer);(function(a){a.html5.api=function(c,p){var n={};var g=document.createElement("div");c.parentNode.replaceChild(g,c);g.id=c.id;n.version=a.version;n.id=g.id;var m=new a.html5.model(n,g,p);var k=new a.html5.view(n,g,m);var l=new a.html5.controller(n,g,m,k);n.skin=new a.html5.skin();n.jwPlay=function(q){if(typeof q=="undefined"){f()}else{if(q.toString().toLowerCase()=="true"){l.play()}else{l.pause()}}};n.jwPause=function(q){if(typeof q=="undefined"){f()}else{if(q.toString().toLowerCase()=="true"){l.pause()}else{l.play()}}};function f(){if(m.state==a.api.events.state.PLAYING||m.state==a.api.events.state.BUFFERING){l.pause()}else{l.play()}}n.jwStop=l.stop;n.jwSeek=l.seek;n.jwPlaylistItem=function(q){if(d){if(d.playlistClickable()){d.jwInstreamDestroy();return l.item(q)}}else{return l.item(q)}};n.jwPlaylistNext=l.next;n.jwPlaylistPrev=l.prev;n.jwResize=l.resize;n.jwLoad=l.load;n.jwDetachMedia=l.detachMedia;n.jwAttachMedia=l.attachMedia;function j(q){return function(){return m[q]}}function e(q,s,r){return function(){var t=m.plugins.object[q];if(t&&t[s]&&typeof t[s]=="function"){t[s].apply(t,r)}}}n.jwGetPlaylistIndex=j("item");n.jwGetPosition=j("position");n.jwGetDuration=j("duration");n.jwGetBuffer=j("buffer");n.jwGetWidth=j("width");n.jwGetHeight=j("height");n.jwGetFullscreen=j("fullscreen");n.jwSetFullscreen=l.setFullscreen;n.jwGetVolume=j("volume");n.jwSetVolume=l.setVolume;n.jwGetMute=j("mute");n.jwSetMute=l.setMute;n.jwGetStretching=function(){return m.stretching.toUpperCase()};n.jwGetState=j("state");n.jwGetVersion=function(){return n.version};n.jwGetPlaylist=function(){return m.playlist};n.jwAddEventListener=l.addEventListener;n.jwRemoveEventListener=l.removeEventListener;n.jwSendEvent=l.sendEvent;n.jwDockSetButton=function(t,q,r,s){if(m.plugins.object.dock&&m.plugins.object.dock.setButton){m.plugins.object.dock.setButton(t,q,r,s)}};n.jwControlbarShow=e("controlbar","show");n.jwControlbarHide=e("controlbar","hide");n.jwDockShow=e("dock","show");n.jwDockHide=e("dock","hide");n.jwDisplayShow=e("display","show");n.jwDisplayHide=e("display","hide");var d;n.jwLoadInstream=function(r,q){if(!d){d=new a.html5.instream(n,m,k,l)}setTimeout(function(){d.load(r,q)},10)};n.jwInstreamDestroy=function(){if(d){d.jwInstreamDestroy()}};n.jwInstreamAddEventListener=o("jwInstreamAddEventListener");n.jwInstreamRemoveEventListener=o("jwInstreamRemoveEventListener");n.jwInstreamGetState=o("jwInstreamGetState");n.jwInstreamGetDuration=o("jwInstreamGetDuration");n.jwInstreamGetPosition=o("jwInstreamGetPosition");n.jwInstreamPlay=o("jwInstreamPlay");n.jwInstreamPause=o("jwInstreamPause");n.jwInstreamSeek=o("jwInstreamSeek");function o(q){return function(){if(d&&typeof d[q]=="function"){return d[q].apply(this,arguments)}else{_utils.log("Could not call instream method - instream API not initialized")}}}n.jwDestroy=function(){l.destroy()};n.jwGetLevel=function(){};n.jwGetBandwidth=function(){};n.jwGetLockState=function(){};n.jwLock=function(){};n.jwUnlock=function(){};function b(){if(m.config.playlistfile){m.addEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,h);m.loadPlaylist(m.config.playlistfile)}else{if(typeof m.config.playlist=="string"){m.addEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,h);m.loadPlaylist(m.config.playlist)}else{m.loadPlaylist(m.config);setTimeout(h,25)}}}function h(q){m.removeEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,h);m.setupPlugins();k.setup();var q={id:n.id,version:n.version};l.playerReady(q)}if(m.config.chromeless&&!a.utils.isIOS()){b()}else{n.skin.load(m.config.skin,b)}return n}})(jwplayer)};
(function($, jQuery){
//     Underscore.js 1.4.1
//     http://underscorejs.org
//     (c) 2009-2012 Jeremy Ashkenas, DocumentCloud Inc.
//     Underscore may be freely distributed under the MIT license.
(function(){var e=this,t=e._,n={},r=Array.prototype,i=Object.prototype,s=Function.prototype,o=r.push,u=r.slice,a=r.concat,f=r.unshift,l=i.toString,c=i.hasOwnProperty,h=r.forEach,p=r.map,d=r.reduce,v=r.reduceRight,m=r.filter,g=r.every,y=r.some,b=r.indexOf,w=r.lastIndexOf,E=Array.isArray,S=Object.keys,x=s.bind,T=function(e){if(e instanceof T)return e;if(!(this instanceof T))return new T(e);this._wrapped=e};typeof exports!="undefined"?(typeof module!="undefined"&&module.exports&&(exports=module.exports=T),exports._=T):e._=T,T.VERSION="1.4.1";var N=T.each=T.forEach=function(e,t,r){if(h&&e.forEach===h)e.forEach(t,r);else if(e.length===+e.length){for(var i=0,s=e.length;i<s;i++)if(t.call(r,e[i],i,e)===n)return}else for(var o in e)if(T.has(e,o)&&t.call(r,e[o],o,e)===n)return};T.map=T.collect=function(e,t,n){var r=[];return p&&e.map===p?e.map(t,n):(N(e,function(e,i,s){r[r.length]=t.call(n,e,i,s)}),r)},T.reduce=T.foldl=T.inject=function(e,t,n,r){var i=arguments.length>2;if(d&&e.reduce===d)return r&&(t=T.bind(t,r)),i?e.reduce(t,n):e.reduce(t);N(e,function(e,s,o){i?n=t.call(r,n,e,s,o):(n=e,i=!0)});if(!i)throw new TypeError("Reduce of empty array with no initial value");return n},T.reduceRight=T.foldr=function(e,t,n,r){var i=arguments.length>2;if(v&&e.reduceRight===v)return r&&(t=T.bind(t,r)),arguments.length>2?e.reduceRight(t,n):e.reduceRight(t);var s=e.length;if(s!==+s){var o=T.keys(e);s=o.length}N(e,function(u,a,f){a=o?o[--s]:--s,i?n=t.call(r,n,e[a],a,f):(n=e[a],i=!0)});if(!i)throw new TypeError("Reduce of empty array with no initial value");return n},T.find=T.detect=function(e,t,n){var r;return C(e,function(e,i,s){if(t.call(n,e,i,s))return r=e,!0}),r},T.filter=T.select=function(e,t,n){var r=[];return m&&e.filter===m?e.filter(t,n):(N(e,function(e,i,s){t.call(n,e,i,s)&&(r[r.length]=e)}),r)},T.reject=function(e,t,n){var r=[];return N(e,function(e,i,s){t.call(n,e,i,s)||(r[r.length]=e)}),r},T.every=T.all=function(e,t,r){t||(t=T.identity);var i=!0;return g&&e.every===g?e.every(t,r):(N(e,function(e,s,o){if(!(i=i&&t.call(r,e,s,o)))return n}),!!i)};var C=T.some=T.any=function(e,t,r){t||(t=T.identity);var i=!1;return y&&e.some===y?e.some(t,r):(N(e,function(e,s,o){if(i||(i=t.call(r,e,s,o)))return n}),!!i)};T.contains=T.include=function(e,t){var n=!1;return b&&e.indexOf===b?e.indexOf(t)!=-1:(n=C(e,function(e){return e===t}),n)},T.invoke=function(e,t){var n=u.call(arguments,2);return T.map(e,function(e){return(T.isFunction(t)?t:e[t]).apply(e,n)})},T.pluck=function(e,t){return T.map(e,function(e){return e[t]})},T.where=function(e,t){return T.isEmpty(t)?[]:T.filter(e,function(e){for(var n in t)if(t[n]!==e[n])return!1;return!0})},T.max=function(e,t,n){if(!t&&T.isArray(e)&&e[0]===+e[0]&&e.length<65535)return Math.max.apply(Math,e);if(!t&&T.isEmpty(e))return-Infinity;var r={computed:-Infinity};return N(e,function(e,i,s){var o=t?t.call(n,e,i,s):e;o>=r.computed&&(r={value:e,computed:o})}),r.value},T.min=function(e,t,n){if(!t&&T.isArray(e)&&e[0]===+e[0]&&e.length<65535)return Math.min.apply(Math,e);if(!t&&T.isEmpty(e))return Infinity;var r={computed:Infinity};return N(e,function(e,i,s){var o=t?t.call(n,e,i,s):e;o<r.computed&&(r={value:e,computed:o})}),r.value},T.shuffle=function(e){var t,n=0,r=[];return N(e,function(e){t=T.random(n++),r[n-1]=r[t],r[t]=e}),r};var k=function(e){return T.isFunction(e)?e:function(t){return t[e]}};T.sortBy=function(e,t,n){var r=k(t);return T.pluck(T.map(e,function(e,t,i){return{value:e,index:t,criteria:r.call(n,e,t,i)}}).sort(function(e,t){var n=e.criteria,r=t.criteria;if(n!==r){if(n>r||n===void 0)return 1;if(n<r||r===void 0)return-1}return e.index<t.index?-1:1}),"value")};var L=function(e,t,n,r){var i={},s=k(t);return N(e,function(t,o){var u=s.call(n,t,o,e);r(i,u,t)}),i};T.groupBy=function(e,t,n){return L(e,t,n,function(e,t,n){(T.has(e,t)?e[t]:e[t]=[]).push(n)})},T.countBy=function(e,t,n){return L(e,t,n,function(e,t,n){T.has(e,t)||(e[t]=0),e[t]++})},T.sortedIndex=function(e,t,n,r){n=n==null?T.identity:k(n);var i=n.call(r,t),s=0,o=e.length;while(s<o){var u=s+o>>>1;n.call(r,e[u])<i?s=u+1:o=u}return s},T.toArray=function(e){return e?e.length===+e.length?u.call(e):T.values(e):[]},T.size=function(e){return e.length===+e.length?e.length:T.keys(e).length},T.first=T.head=T.take=function(e,t,n){return t!=null&&!n?u.call(e,0,t):e[0]},T.initial=function(e,t,n){return u.call(e,0,e.length-(t==null||n?1:t))},T.last=function(e,t,n){return t!=null&&!n?u.call(e,Math.max(e.length-t,0)):e[e.length-1]},T.rest=T.tail=T.drop=function(e,t,n){return u.call(e,t==null||n?1:t)},T.compact=function(e){return T.filter(e,function(e){return!!e})};var A=function(e,t,n){return N(e,function(e){T.isArray(e)?t?o.apply(n,e):A(e,t,n):n.push(e)}),n};T.flatten=function(e,t){return A(e,t,[])},T.without=function(e){return T.difference(e,u.call(arguments,1))},T.uniq=T.unique=function(e,t,n,r){var i=n?T.map(e,n,r):e,s=[],o=[];return N(i,function(n,r){if(t?!r||o[o.length-1]!==n:!T.contains(o,n))o.push(n),s.push(e[r])}),s},T.union=function(){return T.uniq(a.apply(r,arguments))},T.intersection=function(e){var t=u.call(arguments,1);return T.filter(T.uniq(e),function(e){return T.every(t,function(t){return T.indexOf(t,e)>=0})})},T.difference=function(e){var t=a.apply(r,u.call(arguments,1));return T.filter(e,function(e){return!T.contains(t,e)})},T.zip=function(){var e=u.call(arguments),t=T.max(T.pluck(e,"length")),n=new Array(t);for(var r=0;r<t;r++)n[r]=T.pluck(e,""+r);return n},T.object=function(e,t){var n={};for(var r=0,i=e.length;r<i;r++)t?n[e[r]]=t[r]:n[e[r][0]]=e[r][1];return n},T.indexOf=function(e,t,n){var r=0,i=e.length;if(n){if(typeof n!="number")return r=T.sortedIndex(e,t),e[r]===t?r:-1;r=n<0?Math.max(0,i+n):n}if(b&&e.indexOf===b)return e.indexOf(t,n);for(;r<i;r++)if(e[r]===t)return r;return-1},T.lastIndexOf=function(e,t,n){var r=n!=null;if(w&&e.lastIndexOf===w)return r?e.lastIndexOf(t,n):e.lastIndexOf(t);var i=r?n:e.length;while(i--)if(e[i]===t)return i;return-1},T.range=function(e,t,n){arguments.length<=1&&(t=e||0,e=0),n=arguments[2]||1;var r=Math.max(Math.ceil((t-e)/n),0),i=0,s=new Array(r);while(i<r)s[i++]=e,e+=n;return s};var O=function(){};T.bind=function(t,n){var r,i;if(t.bind===x&&x)return x.apply(t,u.call(arguments,1));if(!T.isFunction(t))throw new TypeError;return i=u.call(arguments,2),r=function(){if(this instanceof r){O.prototype=t.prototype;var e=new O,s=t.apply(e,i.concat(u.call(arguments)));return Object(s)===s?s:e}return t.apply(n,i.concat(u.call(arguments)))}},T.bindAll=function(e){var t=u.call(arguments,1);return t.length==0&&(t=T.functions(e)),N(t,function(t){e[t]=T.bind(e[t],e)}),e},T.memoize=function(e,t){var n={};return t||(t=T.identity),function(){var r=t.apply(this,arguments);return T.has(n,r)?n[r]:n[r]=e.apply(this,arguments)}},T.delay=function(e,t){var n=u.call(arguments,2);return setTimeout(function(){return e.apply(null,n)},t)},T.defer=function(e){return T.delay.apply(T,[e,1].concat(u.call(arguments,1)))},T.throttle=function(e,t){var n,r,i,s,o,u,a=T.debounce(function(){o=s=!1},t);return function(){n=this,r=arguments;var f=function(){i=null,o&&(u=e.apply(n,r)),a()};return i||(i=setTimeout(f,t)),s?o=!0:(s=!0,u=e.apply(n,r)),a(),u}},T.debounce=function(e,t,n){var r,i;return function(){var s=this,o=arguments,u=function(){r=null,n||(i=e.apply(s,o))},a=n&&!r;return clearTimeout(r),r=setTimeout(u,t),a&&(i=e.apply(s,o)),i}},T.once=function(e){var t=!1,n;return function(){return t?n:(t=!0,n=e.apply(this,arguments),e=null,n)}},T.wrap=function(e,t){return function(){var n=[e];return o.apply(n,arguments),t.apply(this,n)}},T.compose=function(){var e=arguments;return function(){var t=arguments;for(var n=e.length-1;n>=0;n--)t=[e[n].apply(this,t)];return t[0]}},T.after=function(e,t){return e<=0?t():function(){if(--e<1)return t.apply(this,arguments)}},T.keys=S||function(e){if(e!==Object(e))throw new TypeError("Invalid object");var t=[];for(var n in e)T.has(e,n)&&(t[t.length]=n);return t},T.values=function(e){var t=[];for(var n in e)T.has(e,n)&&t.push(e[n]);return t},T.pairs=function(e){var t=[];for(var n in e)T.has(e,n)&&t.push([n,e[n]]);return t},T.invert=function(e){var t={};for(var n in e)T.has(e,n)&&(t[e[n]]=n);return t},T.functions=T.methods=function(e){var t=[];for(var n in e)T.isFunction(e[n])&&t.push(n);return t.sort()},T.extend=function(e){return N(u.call(arguments,1),function(t){for(var n in t)e[n]=t[n]}),e},T.pick=function(e){var t={},n=a.apply(r,u.call(arguments,1));return N(n,function(n){n in e&&(t[n]=e[n])}),t},T.omit=function(e){var t={},n=a.apply(r,u.call(arguments,1));for(var i in e)T.contains(n,i)||(t[i]=e[i]);return t},T.defaults=function(e){return N(u.call(arguments,1),function(t){for(var n in t)e[n]==null&&(e[n]=t[n])}),e},T.clone=function(e){return T.isObject(e)?T.isArray(e)?e.slice():T.extend({},e):e},T.tap=function(e,t){return t(e),e};var M=function(e,t,n,r){if(e===t)return e!==0||1/e==1/t;if(e==null||t==null)return e===t;e instanceof T&&(e=e._wrapped),t instanceof T&&(t=t._wrapped);var i=l.call(e);if(i!=l.call(t))return!1;switch(i){case"[object String]":return e==String(t);case"[object Number]":return e!=+e?t!=+t:e==0?1/e==1/t:e==+t;case"[object Date]":case"[object Boolean]":return+e==+t;case"[object RegExp]":return e.source==t.source&&e.global==t.global&&e.multiline==t.multiline&&e.ignoreCase==t.ignoreCase}if(typeof e!="object"||typeof t!="object")return!1;var s=n.length;while(s--)if(n[s]==e)return r[s]==t;n.push(e),r.push(t);var o=0,u=!0;if(i=="[object Array]"){o=e.length,u=o==t.length;if(u)while(o--)if(!(u=M(e[o],t[o],n,r)))break}else{var a=e.constructor,f=t.constructor;if(a!==f&&!(T.isFunction(a)&&a instanceof a&&T.isFunction(f)&&f instanceof f))return!1;for(var c in e)if(T.has(e,c)){o++;if(!(u=T.has(t,c)&&M(e[c],t[c],n,r)))break}if(u){for(c in t)if(T.has(t,c)&&!(o--))break;u=!o}}return n.pop(),r.pop(),u};T.isEqual=function(e,t){return M(e,t,[],[])},T.isEmpty=function(e){if(e==null)return!0;if(T.isArray(e)||T.isString(e))return e.length===0;for(var t in e)if(T.has(e,t))return!1;return!0},T.isElement=function(e){return!!e&&e.nodeType===1},T.isArray=E||function(e){return l.call(e)=="[object Array]"},T.isObject=function(e){return e===Object(e)},N(["Arguments","Function","String","Number","Date","RegExp"],function(e){T["is"+e]=function(t){return l.call(t)=="[object "+e+"]"}}),T.isArguments(arguments)||(T.isArguments=function(e){return!!e&&!!T.has(e,"callee")}),typeof /./!="function"&&(T.isFunction=function(e){return typeof e=="function"}),T.isFinite=function(e){return T.isNumber(e)&&isFinite(e)},T.isNaN=function(e){return T.isNumber(e)&&e!=+e},T.isBoolean=function(e){return e===!0||e===!1||l.call(e)=="[object Boolean]"},T.isNull=function(e){return e===null},T.isUndefined=function(e){return e===void 0},T.has=function(e,t){return c.call(e,t)},T.noConflict=function(){return e._=t,this},T.identity=function(e){return e},T.times=function(e,t,n){for(var r=0;r<e;r++)t.call(n,r)},T.random=function(e,t){return t==null&&(t=e,e=0),e+(0|Math.random()*(t-e+1))};var _={escape:{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","/":"&#x2F;"}};_.unescape=T.invert(_.escape);var D={escape:new RegExp("["+T.keys(_.escape).join("")+"]","g"),unescape:new RegExp("("+T.keys(_.unescape).join("|")+")","g")};T.each(["escape","unescape"],function(e){T[e]=function(t){return t==null?"":(""+t).replace(D[e],function(t){return _[e][t]})}}),T.result=function(e,t){if(e==null)return null;var n=e[t];return T.isFunction(n)?n.call(e):n},T.mixin=function(e){N(T.functions(e),function(t){var n=T[t]=e[t];T.prototype[t]=function(){var e=[this._wrapped];return o.apply(e,arguments),F.call(this,n.apply(T,e))}})};var P=0;T.uniqueId=function(e){var t=P++;return e?e+t:t},T.templateSettings={evaluate:/<%([\s\S]+?)%>/g,interpolate:/<%=([\s\S]+?)%>/g,escape:/<%-([\s\S]+?)%>/g};var H=/(.)^/,B={"'":"'","\\":"\\","\r":"r","\n":"n","	":"t","\u2028":"u2028","\u2029":"u2029"},j=/\\|'|\r|\n|\t|\u2028|\u2029/g;T.template=function(e,t,n){n=T.defaults({},n,T.templateSettings);var r=new RegExp([(n.escape||H).source,(n.interpolate||H).source,(n.evaluate||H).source].join("|")+"|$","g"),i=0,s="__p+='";e.replace(r,function(t,n,r,o,u){s+=e.slice(i,u).replace(j,function(e){return"\\"+B[e]}),s+=n?"'+\n((__t=("+n+"))==null?'':_.escape(__t))+\n'":r?"'+\n((__t=("+r+"))==null?'':__t)+\n'":o?"';\n"+o+"\n__p+='":"",i=u+t.length}),s+="';\n",n.variable||(s="with(obj||{}){\n"+s+"}\n"),s="var __t,__p='',__j=Array.prototype.join,print=function(){__p+=__j.call(arguments,'');};\n"+s+"return __p;\n";try{var o=new Function(n.variable||"obj","_",s)}catch(u){throw u.source=s,u}if(t)return o(t,T);var a=function(e){return o.call(this,e,T)};return a.source="function("+(n.variable||"obj")+"){\n"+s+"}",a},T.chain=function(e){return T(e).chain()};var F=function(e){return this._chain?T(e).chain():e};T.mixin(T),N(["pop","push","reverse","shift","sort","splice","unshift"],function(e){var t=r[e];T.prototype[e]=function(){var n=this._wrapped;return t.apply(n,arguments),(e=="shift"||e=="splice")&&n.length===0&&delete n[0],F.call(this,n)}}),N(["concat","join","slice"],function(e){var t=r[e];T.prototype[e]=function(){return F.call(this,t.apply(this._wrapped,arguments))}}),T.extend(T.prototype,{chain:function(){return this._chain=!0,this},value:function(){return this._wrapped}})}).call(this);
// Backbone.js 0.9.2

// (c) 2010-2012 Jeremy Ashkenas, DocumentCloud Inc.
// Backbone may be freely distributed under the MIT license.
// For all details and documentation:
// http://backbonejs.org
(function(){var l=this,y=l.Backbone,z=Array.prototype.slice,A=Array.prototype.splice,g;g="undefined"!==typeof exports?exports:l.Backbone={};g.VERSION="0.9.2";var f=l._;!f&&"undefined"!==typeof require&&(f=require("underscore"));var i=l.jQuery||l.Zepto||l.ender;g.setDomLibrary=function(a){i=a};g.noConflict=function(){l.Backbone=y;return this};g.emulateHTTP=!1;g.emulateJSON=!1;var p=/\s+/,k=g.Events={on:function(a,b,c){var d,e,f,g,j;if(!b)return this;a=a.split(p);for(d=this._callbacks||(this._callbacks=
{});e=a.shift();)f=(j=d[e])?j.tail:{},f.next=g={},f.context=c,f.callback=b,d[e]={tail:g,next:j?j.next:f};return this},off:function(a,b,c){var d,e,h,g,j,q;if(e=this._callbacks){if(!a&&!b&&!c)return delete this._callbacks,this;for(a=a?a.split(p):f.keys(e);d=a.shift();)if(h=e[d],delete e[d],h&&(b||c))for(g=h.tail;(h=h.next)!==g;)if(j=h.callback,q=h.context,b&&j!==b||c&&q!==c)this.on(d,j,q);return this}},trigger:function(a){var b,c,d,e,f,g;if(!(d=this._callbacks))return this;f=d.all;a=a.split(p);for(g=
z.call(arguments,1);b=a.shift();){if(c=d[b])for(e=c.tail;(c=c.next)!==e;)c.callback.apply(c.context||this,g);if(c=f){e=c.tail;for(b=[b].concat(g);(c=c.next)!==e;)c.callback.apply(c.context||this,b)}}return this}};k.bind=k.on;k.unbind=k.off;var o=g.Model=function(a,b){var c;a||(a={});b&&b.parse&&(a=this.parse(a));if(c=n(this,"defaults"))a=f.extend({},c,a);b&&b.collection&&(this.collection=b.collection);this.attributes={};this._escapedAttributes={};this.cid=f.uniqueId("c");this.changed={};this._silent=
{};this._pending={};this.set(a,{silent:!0});this.changed={};this._silent={};this._pending={};this._previousAttributes=f.clone(this.attributes);this.initialize.apply(this,arguments)};f.extend(o.prototype,k,{changed:null,_silent:null,_pending:null,idAttribute:"id",initialize:function(){},toJSON:function(){return f.clone(this.attributes)},get:function(a){return this.attributes[a]},escape:function(a){var b;if(b=this._escapedAttributes[a])return b;b=this.get(a);return this._escapedAttributes[a]=f.escape(null==
b?"":""+b)},has:function(a){return null!=this.get(a)},set:function(a,b,c){var d,e;f.isObject(a)||null==a?(d=a,c=b):(d={},d[a]=b);c||(c={});if(!d)return this;d instanceof o&&(d=d.attributes);if(c.unset)for(e in d)d[e]=void 0;if(!this._validate(d,c))return!1;this.idAttribute in d&&(this.id=d[this.idAttribute]);var b=c.changes={},h=this.attributes,g=this._escapedAttributes,j=this._previousAttributes||{};for(e in d){a=d[e];if(!f.isEqual(h[e],a)||c.unset&&f.has(h,e))delete g[e],(c.silent?this._silent:
b)[e]=!0;c.unset?delete h[e]:h[e]=a;!f.isEqual(j[e],a)||f.has(h,e)!=f.has(j,e)?(this.changed[e]=a,c.silent||(this._pending[e]=!0)):(delete this.changed[e],delete this._pending[e])}c.silent||this.change(c);return this},unset:function(a,b){(b||(b={})).unset=!0;return this.set(a,null,b)},clear:function(a){(a||(a={})).unset=!0;return this.set(f.clone(this.attributes),a)},fetch:function(a){var a=a?f.clone(a):{},b=this,c=a.success;a.success=function(d,e,f){if(!b.set(b.parse(d,f),a))return!1;c&&c(b,d)};
a.error=g.wrapError(a.error,b,a);return(this.sync||g.sync).call(this,"read",this,a)},save:function(a,b,c){var d,e;f.isObject(a)||null==a?(d=a,c=b):(d={},d[a]=b);c=c?f.clone(c):{};if(c.wait){if(!this._validate(d,c))return!1;e=f.clone(this.attributes)}a=f.extend({},c,{silent:!0});if(d&&!this.set(d,c.wait?a:c))return!1;var h=this,i=c.success;c.success=function(a,b,e){b=h.parse(a,e);if(c.wait){delete c.wait;b=f.extend(d||{},b)}if(!h.set(b,c))return false;i?i(h,a):h.trigger("sync",h,a,c)};c.error=g.wrapError(c.error,
h,c);b=this.isNew()?"create":"update";b=(this.sync||g.sync).call(this,b,this,c);c.wait&&this.set(e,a);return b},destroy:function(a){var a=a?f.clone(a):{},b=this,c=a.success,d=function(){b.trigger("destroy",b,b.collection,a)};if(this.isNew())return d(),!1;a.success=function(e){a.wait&&d();c?c(b,e):b.trigger("sync",b,e,a)};a.error=g.wrapError(a.error,b,a);var e=(this.sync||g.sync).call(this,"delete",this,a);a.wait||d();return e},url:function(){var a=n(this,"urlRoot")||n(this.collection,"url")||t();
return this.isNew()?a:a+("/"==a.charAt(a.length-1)?"":"/")+encodeURIComponent(this.id)},parse:function(a){return a},clone:function(){return new this.constructor(this.attributes)},isNew:function(){return null==this.id},change:function(a){a||(a={});var b=this._changing;this._changing=!0;for(var c in this._silent)this._pending[c]=!0;var d=f.extend({},a.changes,this._silent);this._silent={};for(c in d)this.trigger("change:"+c,this,this.get(c),a);if(b)return this;for(;!f.isEmpty(this._pending);){this._pending=
{};this.trigger("change",this,a);for(c in this.changed)!this._pending[c]&&!this._silent[c]&&delete this.changed[c];this._previousAttributes=f.clone(this.attributes)}this._changing=!1;return this},hasChanged:function(a){return!arguments.length?!f.isEmpty(this.changed):f.has(this.changed,a)},changedAttributes:function(a){if(!a)return this.hasChanged()?f.clone(this.changed):!1;var b,c=!1,d=this._previousAttributes,e;for(e in a)if(!f.isEqual(d[e],b=a[e]))(c||(c={}))[e]=b;return c},previous:function(a){return!arguments.length||
!this._previousAttributes?null:this._previousAttributes[a]},previousAttributes:function(){return f.clone(this._previousAttributes)},isValid:function(){return!this.validate(this.attributes)},_validate:function(a,b){if(b.silent||!this.validate)return!0;var a=f.extend({},this.attributes,a),c=this.validate(a,b);if(!c)return!0;b&&b.error?b.error(this,c,b):this.trigger("error",this,c,b);return!1}});var r=g.Collection=function(a,b){b||(b={});b.model&&(this.model=b.model);b.comparator&&(this.comparator=b.comparator);
this._reset();this.initialize.apply(this,arguments);a&&this.reset(a,{silent:!0,parse:b.parse})};f.extend(r.prototype,k,{model:o,initialize:function(){},toJSON:function(a){return this.map(function(b){return b.toJSON(a)})},add:function(a,b){var c,d,e,g,i,j={},k={},l=[];b||(b={});a=f.isArray(a)?a.slice():[a];c=0;for(d=a.length;c<d;c++){if(!(e=a[c]=this._prepareModel(a[c],b)))throw Error("Can't add an invalid model to a collection");g=e.cid;i=e.id;j[g]||this._byCid[g]||null!=i&&(k[i]||this._byId[i])?
l.push(c):j[g]=k[i]=e}for(c=l.length;c--;)a.splice(l[c],1);c=0;for(d=a.length;c<d;c++)(e=a[c]).on("all",this._onModelEvent,this),this._byCid[e.cid]=e,null!=e.id&&(this._byId[e.id]=e);this.length+=d;A.apply(this.models,[null!=b.at?b.at:this.models.length,0].concat(a));this.comparator&&this.sort({silent:!0});if(b.silent)return this;c=0;for(d=this.models.length;c<d;c++)if(j[(e=this.models[c]).cid])b.index=c,e.trigger("add",e,this,b);return this},remove:function(a,b){var c,d,e,g;b||(b={});a=f.isArray(a)?
a.slice():[a];c=0;for(d=a.length;c<d;c++)if(g=this.getByCid(a[c])||this.get(a[c]))delete this._byId[g.id],delete this._byCid[g.cid],e=this.indexOf(g),this.models.splice(e,1),this.length--,b.silent||(b.index=e,g.trigger("remove",g,this,b)),this._removeReference(g);return this},push:function(a,b){a=this._prepareModel(a,b);this.add(a,b);return a},pop:function(a){var b=this.at(this.length-1);this.remove(b,a);return b},unshift:function(a,b){a=this._prepareModel(a,b);this.add(a,f.extend({at:0},b));return a},
shift:function(a){var b=this.at(0);this.remove(b,a);return b},get:function(a){return null==a?void 0:this._byId[null!=a.id?a.id:a]},getByCid:function(a){return a&&this._byCid[a.cid||a]},at:function(a){return this.models[a]},where:function(a){return f.isEmpty(a)?[]:this.filter(function(b){for(var c in a)if(a[c]!==b.get(c))return!1;return!0})},sort:function(a){a||(a={});if(!this.comparator)throw Error("Cannot sort a set without a comparator");var b=f.bind(this.comparator,this);1==this.comparator.length?
this.models=this.sortBy(b):this.models.sort(b);a.silent||this.trigger("reset",this,a);return this},pluck:function(a){return f.map(this.models,function(b){return b.get(a)})},reset:function(a,b){a||(a=[]);b||(b={});for(var c=0,d=this.models.length;c<d;c++)this._removeReference(this.models[c]);this._reset();this.add(a,f.extend({silent:!0},b));b.silent||this.trigger("reset",this,b);return this},fetch:function(a){a=a?f.clone(a):{};void 0===a.parse&&(a.parse=!0);var b=this,c=a.success;a.success=function(d,
e,f){b[a.add?"add":"reset"](b.parse(d,f),a);c&&c(b,d)};a.error=g.wrapError(a.error,b,a);return(this.sync||g.sync).call(this,"read",this,a)},create:function(a,b){var c=this,b=b?f.clone(b):{},a=this._prepareModel(a,b);if(!a)return!1;b.wait||c.add(a,b);var d=b.success;b.success=function(e,f){b.wait&&c.add(e,b);d?d(e,f):e.trigger("sync",a,f,b)};a.save(null,b);return a},parse:function(a){return a},chain:function(){return f(this.models).chain()},_reset:function(){this.length=0;this.models=[];this._byId=
{};this._byCid={}},_prepareModel:function(a,b){b||(b={});a instanceof o?a.collection||(a.collection=this):(b.collection=this,a=new this.model(a,b),a._validate(a.attributes,b)||(a=!1));return a},_removeReference:function(a){this==a.collection&&delete a.collection;a.off("all",this._onModelEvent,this)},_onModelEvent:function(a,b,c,d){("add"==a||"remove"==a)&&c!=this||("destroy"==a&&this.remove(b,d),b&&a==="change:"+b.idAttribute&&(delete this._byId[b.previous(b.idAttribute)],this._byId[b.id]=b),this.trigger.apply(this,
arguments))}});f.each("forEach,each,map,reduce,reduceRight,find,detect,filter,select,reject,every,all,some,any,include,contains,invoke,max,min,sortBy,sortedIndex,toArray,size,first,initial,rest,last,without,indexOf,shuffle,lastIndexOf,isEmpty,groupBy".split(","),function(a){r.prototype[a]=function(){return f[a].apply(f,[this.models].concat(f.toArray(arguments)))}});var u=g.Router=function(a){a||(a={});a.routes&&(this.routes=a.routes);this._bindRoutes();this.initialize.apply(this,arguments)},B=/:\w+/g,
C=/\*\w+/g,D=/[-[\]{}()+?.,\\^$|#\s]/g;f.extend(u.prototype,k,{initialize:function(){},route:function(a,b,c){g.history||(g.history=new m);f.isRegExp(a)||(a=this._routeToRegExp(a));c||(c=this[b]);g.history.route(a,f.bind(function(d){d=this._extractParameters(a,d);c&&c.apply(this,d);this.trigger.apply(this,["route:"+b].concat(d));g.history.trigger("route",this,b,d)},this));return this},navigate:function(a,b){g.history.navigate(a,b)},_bindRoutes:function(){if(this.routes){var a=[],b;for(b in this.routes)a.unshift([b,
this.routes[b]]);b=0;for(var c=a.length;b<c;b++)this.route(a[b][0],a[b][1],this[a[b][1]])}},_routeToRegExp:function(a){a=a.replace(D,"\\$&").replace(B,"([^/]+)").replace(C,"(.*?)");return RegExp("^"+a+"$")},_extractParameters:function(a,b){return a.exec(b).slice(1)}});var m=g.History=function(){this.handlers=[];f.bindAll(this,"checkUrl")},s=/^[#\/]/,E=/msie [\w.]+/;m.started=!1;f.extend(m.prototype,k,{interval:50,getHash:function(a){return(a=(a?a.location:window.location).href.match(/#(.*)$/))?a[1]:
""},getFragment:function(a,b){if(null==a)if(this._hasPushState||b){var a=window.location.pathname,c=window.location.search;c&&(a+=c)}else a=this.getHash();a.indexOf(this.options.root)||(a=a.substr(this.options.root.length));return a.replace(s,"")},start:function(a){if(m.started)throw Error("Backbone.history has already been started");m.started=!0;this.options=f.extend({},{root:"/"},this.options,a);this._wantsHashChange=!1!==this.options.hashChange;this._wantsPushState=!!this.options.pushState;this._hasPushState=
!(!this.options.pushState||!window.history||!window.history.pushState);var a=this.getFragment(),b=document.documentMode;if(b=E.exec(navigator.userAgent.toLowerCase())&&(!b||7>=b))this.iframe=i('<iframe src="javascript:0" tabindex="-1" />').hide().appendTo("body")[0].contentWindow,this.navigate(a);this._hasPushState?i(window).bind("popstate",this.checkUrl):this._wantsHashChange&&"onhashchange"in window&&!b?i(window).bind("hashchange",this.checkUrl):this._wantsHashChange&&(this._checkUrlInterval=setInterval(this.checkUrl,
this.interval));this.fragment=a;a=window.location;b=a.pathname==this.options.root;if(this._wantsHashChange&&this._wantsPushState&&!this._hasPushState&&!b)return this.fragment=this.getFragment(null,!0),window.location.replace(this.options.root+"#"+this.fragment),!0;this._wantsPushState&&this._hasPushState&&b&&a.hash&&(this.fragment=this.getHash().replace(s,""),window.history.replaceState({},document.title,a.protocol+"//"+a.host+this.options.root+this.fragment));if(!this.options.silent)return this.loadUrl()},
stop:function(){i(window).unbind("popstate",this.checkUrl).unbind("hashchange",this.checkUrl);clearInterval(this._checkUrlInterval);m.started=!1},route:function(a,b){this.handlers.unshift({route:a,callback:b})},checkUrl:function(){var a=this.getFragment();a==this.fragment&&this.iframe&&(a=this.getFragment(this.getHash(this.iframe)));if(a==this.fragment)return!1;this.iframe&&this.navigate(a);this.loadUrl()||this.loadUrl(this.getHash())},loadUrl:function(a){var b=this.fragment=this.getFragment(a);return f.any(this.handlers,
function(a){if(a.route.test(b))return a.callback(b),!0})},navigate:function(a,b){if(!m.started)return!1;if(!b||!0===b)b={trigger:b};var c=(a||"").replace(s,"");this.fragment!=c&&(this._hasPushState?(0!=c.indexOf(this.options.root)&&(c=this.options.root+c),this.fragment=c,window.history[b.replace?"replaceState":"pushState"]({},document.title,c)):this._wantsHashChange?(this.fragment=c,this._updateHash(window.location,c,b.replace),this.iframe&&c!=this.getFragment(this.getHash(this.iframe))&&(b.replace||
this.iframe.document.open().close(),this._updateHash(this.iframe.location,c,b.replace))):window.location.assign(this.options.root+a),b.trigger&&this.loadUrl(a))},_updateHash:function(a,b,c){c?a.replace(a.toString().replace(/(javascript:|#).*$/,"")+"#"+b):a.hash=b}});var v=g.View=function(a){this.cid=f.uniqueId("view");this._configure(a||{});this._ensureElement();this.initialize.apply(this,arguments);this.delegateEvents()},F=/^(\S+)\s*(.*)$/,w="model,collection,el,id,attributes,className,tagName".split(",");
f.extend(v.prototype,k,{tagName:"div",$:function(a){return this.$el.find(a)},initialize:function(){},render:function(){return this},remove:function(){this.$el.remove();return this},make:function(a,b,c){a=document.createElement(a);b&&i(a).attr(b);c&&i(a).html(c);return a},setElement:function(a,b){this.$el&&this.undelegateEvents();this.$el=a instanceof i?a:i(a);this.el=this.$el[0];!1!==b&&this.delegateEvents();return this},delegateEvents:function(a){if(a||(a=n(this,"events"))){this.undelegateEvents();
for(var b in a){var c=a[b];f.isFunction(c)||(c=this[a[b]]);if(!c)throw Error('Method "'+a[b]+'" does not exist');var d=b.match(F),e=d[1],d=d[2],c=f.bind(c,this),e=e+(".delegateEvents"+this.cid);""===d?this.$el.bind(e,c):this.$el.delegate(d,e,c)}}},undelegateEvents:function(){this.$el.unbind(".delegateEvents"+this.cid)},_configure:function(a){this.options&&(a=f.extend({},this.options,a));for(var b=0,c=w.length;b<c;b++){var d=w[b];a[d]&&(this[d]=a[d])}this.options=a},_ensureElement:function(){if(this.el)this.setElement(this.el,
!1);else{var a=n(this,"attributes")||{};this.id&&(a.id=this.id);this.className&&(a["class"]=this.className);this.setElement(this.make(this.tagName,a),!1)}}});o.extend=r.extend=u.extend=v.extend=function(a,b){var c=G(this,a,b);c.extend=this.extend;return c};var H={create:"POST",update:"PUT","delete":"DELETE",read:"GET"};g.sync=function(a,b,c){var d=H[a];c||(c={});var e={type:d,dataType:"json"};c.url||(e.url=n(b,"url")||t());if(!c.data&&b&&("create"==a||"update"==a))e.contentType="application/json",
e.data=JSON.stringify(b.toJSON());g.emulateJSON&&(e.contentType="application/x-www-form-urlencoded",e.data=e.data?{model:e.data}:{});if(g.emulateHTTP&&("PUT"===d||"DELETE"===d))g.emulateJSON&&(e.data._method=d),e.type="POST",e.beforeSend=function(a){a.setRequestHeader("X-HTTP-Method-Override",d)};"GET"!==e.type&&!g.emulateJSON&&(e.processData=!1);return i.ajax(f.extend(e,c))};g.wrapError=function(a,b,c){return function(d,e){e=d===b?e:d;a?a(b,e,c):b.trigger("error",b,e,c)}};var x=function(){},G=function(a,
b,c){var d;d=b&&b.hasOwnProperty("constructor")?b.constructor:function(){a.apply(this,arguments)};f.extend(d,a);x.prototype=a.prototype;d.prototype=new x;b&&f.extend(d.prototype,b);c&&f.extend(d,c);d.prototype.constructor=d;d.__super__=a.prototype;return d},n=function(a,b){return!a||!a[b]?null:f.isFunction(a[b])?a[b]():a[b]},t=function(){throw Error('A "url" property or function must be specified');}}).call(this);

/*! jQuery UI - v1.10.2 - 2013-04-01
* http://jqueryui.com
* Includes: jquery.ui.core.js, jquery.ui.widget.js, jquery.ui.mouse.js, jquery.ui.position.js, jquery.ui.slider.js
* Copyright 2013 jQuery Foundation and other contributors Licensed MIT */

(function( $, undefined ) {

var uuid = 0,
	runiqueId = /^ui-id-\d+$/;

// $.ui might exist from components with no dependencies, e.g., $.ui.position
$.ui = $.ui || {};

$.extend( $.ui, {
	version: "1.10.2",

	keyCode: {
		BACKSPACE: 8,
		COMMA: 188,
		DELETE: 46,
		DOWN: 40,
		END: 35,
		ENTER: 13,
		ESCAPE: 27,
		HOME: 36,
		LEFT: 37,
		NUMPAD_ADD: 107,
		NUMPAD_DECIMAL: 110,
		NUMPAD_DIVIDE: 111,
		NUMPAD_ENTER: 108,
		NUMPAD_MULTIPLY: 106,
		NUMPAD_SUBTRACT: 109,
		PAGE_DOWN: 34,
		PAGE_UP: 33,
		PERIOD: 190,
		RIGHT: 39,
		SPACE: 32,
		TAB: 9,
		UP: 38
	}
});

// plugins
$.fn.extend({
	focus: (function( orig ) {
		return function( delay, fn ) {
			return typeof delay === "number" ?
				this.each(function() {
					var elem = this;
					setTimeout(function() {
						$( elem ).focus();
						if ( fn ) {
							fn.call( elem );
						}
					}, delay );
				}) :
				orig.apply( this, arguments );
		};
	})( $.fn.focus ),

	scrollParent: function() {
		var scrollParent;
		if (($.ui.ie && (/(static|relative)/).test(this.css("position"))) || (/absolute/).test(this.css("position"))) {
			scrollParent = this.parents().filter(function() {
				return (/(relative|absolute|fixed)/).test($.css(this,"position")) && (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
			}).eq(0);
		} else {
			scrollParent = this.parents().filter(function() {
				return (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
			}).eq(0);
		}

		return (/fixed/).test(this.css("position")) || !scrollParent.length ? $(document) : scrollParent;
	},

	zIndex: function( zIndex ) {
		if ( zIndex !== undefined ) {
			return this.css( "zIndex", zIndex );
		}

		if ( this.length ) {
			var elem = $( this[ 0 ] ), position, value;
			while ( elem.length && elem[ 0 ] !== document ) {
				// Ignore z-index if position is set to a value where z-index is ignored by the browser
				// This makes behavior of this function consistent across browsers
				// WebKit always returns auto if the element is positioned
				position = elem.css( "position" );
				if ( position === "absolute" || position === "relative" || position === "fixed" ) {
					// IE returns 0 when zIndex is not specified
					// other browsers return a string
					// we ignore the case of nested elements with an explicit value of 0
					// <div style="z-index: -10;"><div style="z-index: 0;"></div></div>
					value = parseInt( elem.css( "zIndex" ), 10 );
					if ( !isNaN( value ) && value !== 0 ) {
						return value;
					}
				}
				elem = elem.parent();
			}
		}

		return 0;
	},

	uniqueId: function() {
		return this.each(function() {
			if ( !this.id ) {
				this.id = "ui-id-" + (++uuid);
			}
		});
	},

	removeUniqueId: function() {
		return this.each(function() {
			if ( runiqueId.test( this.id ) ) {
				$( this ).removeAttr( "id" );
			}
		});
	}
});

// selectors
function focusable( element, isTabIndexNotNaN ) {
	var map, mapName, img,
		nodeName = element.nodeName.toLowerCase();
	if ( "area" === nodeName ) {
		map = element.parentNode;
		mapName = map.name;
		if ( !element.href || !mapName || map.nodeName.toLowerCase() !== "map" ) {
			return false;
		}
		img = $( "img[usemap=#" + mapName + "]" )[0];
		return !!img && visible( img );
	}
	return ( /input|select|textarea|button|object/.test( nodeName ) ?
		!element.disabled :
		"a" === nodeName ?
			element.href || isTabIndexNotNaN :
			isTabIndexNotNaN) &&
		// the element and all of its ancestors must be visible
		visible( element );
}

function visible( element ) {
	return $.expr.filters.visible( element ) &&
		!$( element ).parents().addBack().filter(function() {
			return $.css( this, "visibility" ) === "hidden";
		}).length;
}

$.extend( $.expr[ ":" ], {
	data: $.expr.createPseudo ?
		$.expr.createPseudo(function( dataName ) {
			return function( elem ) {
				return !!$.data( elem, dataName );
			};
		}) :
		// support: jQuery <1.8
		function( elem, i, match ) {
			return !!$.data( elem, match[ 3 ] );
		},

	focusable: function( element ) {
		return focusable( element, !isNaN( $.attr( element, "tabindex" ) ) );
	},

	tabbable: function( element ) {
		var tabIndex = $.attr( element, "tabindex" ),
			isTabIndexNaN = isNaN( tabIndex );
		return ( isTabIndexNaN || tabIndex >= 0 ) && focusable( element, !isTabIndexNaN );
	}
});

// support: jQuery <1.8
if ( !$( "<a>" ).outerWidth( 1 ).jquery ) {
	$.each( [ "Width", "Height" ], function( i, name ) {
		var side = name === "Width" ? [ "Left", "Right" ] : [ "Top", "Bottom" ],
			type = name.toLowerCase(),
			orig = {
				innerWidth: $.fn.innerWidth,
				innerHeight: $.fn.innerHeight,
				outerWidth: $.fn.outerWidth,
				outerHeight: $.fn.outerHeight
			};

		function reduce( elem, size, border, margin ) {
			$.each( side, function() {
				size -= parseFloat( $.css( elem, "padding" + this ) ) || 0;
				if ( border ) {
					size -= parseFloat( $.css( elem, "border" + this + "Width" ) ) || 0;
				}
				if ( margin ) {
					size -= parseFloat( $.css( elem, "margin" + this ) ) || 0;
				}
			});
			return size;
		}

		$.fn[ "inner" + name ] = function( size ) {
			if ( size === undefined ) {
				return orig[ "inner" + name ].call( this );
			}

			return this.each(function() {
				$( this ).css( type, reduce( this, size ) + "px" );
			});
		};

		$.fn[ "outer" + name] = function( size, margin ) {
			if ( typeof size !== "number" ) {
				return orig[ "outer" + name ].call( this, size );
			}

			return this.each(function() {
				$( this).css( type, reduce( this, size, true, margin ) + "px" );
			});
		};
	});
}

// support: jQuery <1.8
if ( !$.fn.addBack ) {
	$.fn.addBack = function( selector ) {
		return this.add( selector == null ?
			this.prevObject : this.prevObject.filter( selector )
		);
	};
}

// support: jQuery 1.6.1, 1.6.2 (http://bugs.jquery.com/ticket/9413)
if ( $( "<a>" ).data( "a-b", "a" ).removeData( "a-b" ).data( "a-b" ) ) {
	$.fn.removeData = (function( removeData ) {
		return function( key ) {
			if ( arguments.length ) {
				return removeData.call( this, $.camelCase( key ) );
			} else {
				return removeData.call( this );
			}
		};
	})( $.fn.removeData );
}





// deprecated
$.ui.ie = !!/msie [\w.]+/.exec( navigator.userAgent.toLowerCase() );

$.support.selectstart = "onselectstart" in document.createElement( "div" );
$.fn.extend({
	disableSelection: function() {
		return this.bind( ( $.support.selectstart ? "selectstart" : "mousedown" ) +
			".ui-disableSelection", function( event ) {
				event.preventDefault();
			});
	},

	enableSelection: function() {
		return this.unbind( ".ui-disableSelection" );
	}
});

$.extend( $.ui, {
	// $.ui.plugin is deprecated.  Use the proxy pattern instead.
	plugin: {
		add: function( module, option, set ) {
			var i,
				proto = $.ui[ module ].prototype;
			for ( i in set ) {
				proto.plugins[ i ] = proto.plugins[ i ] || [];
				proto.plugins[ i ].push( [ option, set[ i ] ] );
			}
		},
		call: function( instance, name, args ) {
			var i,
				set = instance.plugins[ name ];
			if ( !set || !instance.element[ 0 ].parentNode || instance.element[ 0 ].parentNode.nodeType === 11 ) {
				return;
			}

			for ( i = 0; i < set.length; i++ ) {
				if ( instance.options[ set[ i ][ 0 ] ] ) {
					set[ i ][ 1 ].apply( instance.element, args );
				}
			}
		}
	},

	// only used by resizable
	hasScroll: function( el, a ) {

		//If overflow is hidden, the element might have extra content, but the user wants to hide it
		if ( $( el ).css( "overflow" ) === "hidden") {
			return false;
		}

		var scroll = ( a && a === "left" ) ? "scrollLeft" : "scrollTop",
			has = false;

		if ( el[ scroll ] > 0 ) {
			return true;
		}

		// TODO: determine which cases actually cause this to happen
		// if the element doesn't have the scroll set, see if it's possible to
		// set the scroll
		el[ scroll ] = 1;
		has = ( el[ scroll ] > 0 );
		el[ scroll ] = 0;
		return has;
	}
});

})( jQuery );
(function( $, undefined ) {

var uuid = 0,
	slice = Array.prototype.slice,
	_cleanData = $.cleanData;
$.cleanData = function( elems ) {
	for ( var i = 0, elem; (elem = elems[i]) != null; i++ ) {
		try {
			$( elem ).triggerHandler( "remove" );
		// http://bugs.jquery.com/ticket/8235
		} catch( e ) {}
	}
	_cleanData( elems );
};

$.widget = function( name, base, prototype ) {
	var fullName, existingConstructor, constructor, basePrototype,
		// proxiedPrototype allows the provided prototype to remain unmodified
		// so that it can be used as a mixin for multiple widgets (#8876)
		proxiedPrototype = {},
		namespace = name.split( "." )[ 0 ];

	name = name.split( "." )[ 1 ];
	fullName = namespace + "-" + name;

	if ( !prototype ) {
		prototype = base;
		base = $.Widget;
	}

	// create selector for plugin
	$.expr[ ":" ][ fullName.toLowerCase() ] = function( elem ) {
		return !!$.data( elem, fullName );
	};

	$[ namespace ] = $[ namespace ] || {};
	existingConstructor = $[ namespace ][ name ];
	constructor = $[ namespace ][ name ] = function( options, element ) {
		// allow instantiation without "new" keyword
		if ( !this._createWidget ) {
			return new constructor( options, element );
		}

		// allow instantiation without initializing for simple inheritance
		// must use "new" keyword (the code above always passes args)
		if ( arguments.length ) {
			this._createWidget( options, element );
		}
	};
	// extend with the existing constructor to carry over any static properties
	$.extend( constructor, existingConstructor, {
		version: prototype.version,
		// copy the object used to create the prototype in case we need to
		// redefine the widget later
		_proto: $.extend( {}, prototype ),
		// track widgets that inherit from this widget in case this widget is
		// redefined after a widget inherits from it
		_childConstructors: []
	});

	basePrototype = new base();
	// we need to make the options hash a property directly on the new instance
	// otherwise we'll modify the options hash on the prototype that we're
	// inheriting from
	basePrototype.options = $.widget.extend( {}, basePrototype.options );
	$.each( prototype, function( prop, value ) {
		if ( !$.isFunction( value ) ) {
			proxiedPrototype[ prop ] = value;
			return;
		}
		proxiedPrototype[ prop ] = (function() {
			var _super = function() {
					return base.prototype[ prop ].apply( this, arguments );
				},
				_superApply = function( args ) {
					return base.prototype[ prop ].apply( this, args );
				};
			return function() {
				var __super = this._super,
					__superApply = this._superApply,
					returnValue;

				this._super = _super;
				this._superApply = _superApply;

				returnValue = value.apply( this, arguments );

				this._super = __super;
				this._superApply = __superApply;

				return returnValue;
			};
		})();
	});
	constructor.prototype = $.widget.extend( basePrototype, {
		// TODO: remove support for widgetEventPrefix
		// always use the name + a colon as the prefix, e.g., draggable:start
		// don't prefix for widgets that aren't DOM-based
		widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix : name
	}, proxiedPrototype, {
		constructor: constructor,
		namespace: namespace,
		widgetName: name,
		widgetFullName: fullName
	});

	// If this widget is being redefined then we need to find all widgets that
	// are inheriting from it and redefine all of them so that they inherit from
	// the new version of this widget. We're essentially trying to replace one
	// level in the prototype chain.
	if ( existingConstructor ) {
		$.each( existingConstructor._childConstructors, function( i, child ) {
			var childPrototype = child.prototype;

			// redefine the child widget using the same prototype that was
			// originally used, but inherit from the new version of the base
			$.widget( childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto );
		});
		// remove the list of existing child constructors from the old constructor
		// so the old child constructors can be garbage collected
		delete existingConstructor._childConstructors;
	} else {
		base._childConstructors.push( constructor );
	}

	$.widget.bridge( name, constructor );
};

$.widget.extend = function( target ) {
	var input = slice.call( arguments, 1 ),
		inputIndex = 0,
		inputLength = input.length,
		key,
		value;
	for ( ; inputIndex < inputLength; inputIndex++ ) {
		for ( key in input[ inputIndex ] ) {
			value = input[ inputIndex ][ key ];
			if ( input[ inputIndex ].hasOwnProperty( key ) && value !== undefined ) {
				// Clone objects
				if ( $.isPlainObject( value ) ) {
					target[ key ] = $.isPlainObject( target[ key ] ) ?
						$.widget.extend( {}, target[ key ], value ) :
						// Don't extend strings, arrays, etc. with objects
						$.widget.extend( {}, value );
				// Copy everything else by reference
				} else {
					target[ key ] = value;
				}
			}
		}
	}
	return target;
};

$.widget.bridge = function( name, object ) {
	var fullName = object.prototype.widgetFullName || name;
	$.fn[ name ] = function( options ) {
		var isMethodCall = typeof options === "string",
			args = slice.call( arguments, 1 ),
			returnValue = this;

		// allow multiple hashes to be passed on init
		options = !isMethodCall && args.length ?
			$.widget.extend.apply( null, [ options ].concat(args) ) :
			options;

		if ( isMethodCall ) {
			this.each(function() {
				var methodValue,
					instance = $.data( this, fullName );
				if ( !instance ) {
					return $.error( "cannot call methods on " + name + " prior to initialization; " +
						"attempted to call method '" + options + "'" );
				}
				if ( !$.isFunction( instance[options] ) || options.charAt( 0 ) === "_" ) {
					return $.error( "no such method '" + options + "' for " + name + " widget instance" );
				}
				methodValue = instance[ options ].apply( instance, args );
				if ( methodValue !== instance && methodValue !== undefined ) {
					returnValue = methodValue && methodValue.jquery ?
						returnValue.pushStack( methodValue.get() ) :
						methodValue;
					return false;
				}
			});
		} else {
			this.each(function() {
				var instance = $.data( this, fullName );
				if ( instance ) {
					instance.option( options || {} )._init();
				} else {
					$.data( this, fullName, new object( options, this ) );
				}
			});
		}

		return returnValue;
	};
};

$.Widget = function( /* options, element */ ) {};
$.Widget._childConstructors = [];

$.Widget.prototype = {
	widgetName: "widget",
	widgetEventPrefix: "",
	defaultElement: "<div>",
	options: {
		disabled: false,

		// callbacks
		create: null
	},
	_createWidget: function( options, element ) {
		element = $( element || this.defaultElement || this )[ 0 ];
		this.element = $( element );
		this.uuid = uuid++;
		this.eventNamespace = "." + this.widgetName + this.uuid;
		this.options = $.widget.extend( {},
			this.options,
			this._getCreateOptions(),
			options );

		this.bindings = $();
		this.hoverable = $();
		this.focusable = $();

		if ( element !== this ) {
			$.data( element, this.widgetFullName, this );
			this._on( true, this.element, {
				remove: function( event ) {
					if ( event.target === element ) {
						this.destroy();
					}
				}
			});
			this.document = $( element.style ?
				// element within the document
				element.ownerDocument :
				// element is window or document
				element.document || element );
			this.window = $( this.document[0].defaultView || this.document[0].parentWindow );
		}

		this._create();
		this._trigger( "create", null, this._getCreateEventData() );
		this._init();
	},
	_getCreateOptions: $.noop,
	_getCreateEventData: $.noop,
	_create: $.noop,
	_init: $.noop,

	destroy: function() {
		this._destroy();
		// we can probably remove the unbind calls in 2.0
		// all event bindings should go through this._on()
		this.element
			.unbind( this.eventNamespace )
			// 1.9 BC for #7810
			// TODO remove dual storage
			.removeData( this.widgetName )
			.removeData( this.widgetFullName )
			// support: jquery <1.6.3
			// http://bugs.jquery.com/ticket/9413
			.removeData( $.camelCase( this.widgetFullName ) );
		this.widget()
			.unbind( this.eventNamespace )
			.removeAttr( "aria-disabled" )
			.removeClass(
				this.widgetFullName + "-disabled " +
				"ui-state-disabled" );

		// clean up events and states
		this.bindings.unbind( this.eventNamespace );
		this.hoverable.removeClass( "ui-state-hover" );
		this.focusable.removeClass( "ui-state-focus" );
	},
	_destroy: $.noop,

	widget: function() {
		return this.element;
	},

	option: function( key, value ) {
		var options = key,
			parts,
			curOption,
			i;

		if ( arguments.length === 0 ) {
			// don't return a reference to the internal hash
			return $.widget.extend( {}, this.options );
		}

		if ( typeof key === "string" ) {
			// handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
			options = {};
			parts = key.split( "." );
			key = parts.shift();
			if ( parts.length ) {
				curOption = options[ key ] = $.widget.extend( {}, this.options[ key ] );
				for ( i = 0; i < parts.length - 1; i++ ) {
					curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
					curOption = curOption[ parts[ i ] ];
				}
				key = parts.pop();
				if ( value === undefined ) {
					return curOption[ key ] === undefined ? null : curOption[ key ];
				}
				curOption[ key ] = value;
			} else {
				if ( value === undefined ) {
					return this.options[ key ] === undefined ? null : this.options[ key ];
				}
				options[ key ] = value;
			}
		}

		this._setOptions( options );

		return this;
	},
	_setOptions: function( options ) {
		var key;

		for ( key in options ) {
			this._setOption( key, options[ key ] );
		}

		return this;
	},
	_setOption: function( key, value ) {
		this.options[ key ] = value;

		if ( key === "disabled" ) {
			this.widget()
				.toggleClass( this.widgetFullName + "-disabled ui-state-disabled", !!value )
				.attr( "aria-disabled", value );
			this.hoverable.removeClass( "ui-state-hover" );
			this.focusable.removeClass( "ui-state-focus" );
		}

		return this;
	},

	enable: function() {
		return this._setOption( "disabled", false );
	},
	disable: function() {
		return this._setOption( "disabled", true );
	},

	_on: function( suppressDisabledCheck, element, handlers ) {
		var delegateElement,
			instance = this;

		// no suppressDisabledCheck flag, shuffle arguments
		if ( typeof suppressDisabledCheck !== "boolean" ) {
			handlers = element;
			element = suppressDisabledCheck;
			suppressDisabledCheck = false;
		}

		// no element argument, shuffle and use this.element
		if ( !handlers ) {
			handlers = element;
			element = this.element;
			delegateElement = this.widget();
		} else {
			// accept selectors, DOM elements
			element = delegateElement = $( element );
			this.bindings = this.bindings.add( element );
		}

		$.each( handlers, function( event, handler ) {
			function handlerProxy() {
				// allow widgets to customize the disabled handling
				// - disabled as an array instead of boolean
				// - disabled class as method for disabling individual parts
				if ( !suppressDisabledCheck &&
						( instance.options.disabled === true ||
							$( this ).hasClass( "ui-state-disabled" ) ) ) {
					return;
				}
				return ( typeof handler === "string" ? instance[ handler ] : handler )
					.apply( instance, arguments );
			}

			// copy the guid so direct unbinding works
			if ( typeof handler !== "string" ) {
				handlerProxy.guid = handler.guid =
					handler.guid || handlerProxy.guid || $.guid++;
			}

			var match = event.match( /^(\w+)\s*(.*)$/ ),
				eventName = match[1] + instance.eventNamespace,
				selector = match[2];
			if ( selector ) {
				delegateElement.delegate( selector, eventName, handlerProxy );
			} else {
				element.bind( eventName, handlerProxy );
			}
		});
	},

	_off: function( element, eventName ) {
		eventName = (eventName || "").split( " " ).join( this.eventNamespace + " " ) + this.eventNamespace;
		element.unbind( eventName ).undelegate( eventName );
	},

	_delay: function( handler, delay ) {
		function handlerProxy() {
			return ( typeof handler === "string" ? instance[ handler ] : handler )
				.apply( instance, arguments );
		}
		var instance = this;
		return setTimeout( handlerProxy, delay || 0 );
	},

	_hoverable: function( element ) {
		this.hoverable = this.hoverable.add( element );
		this._on( element, {
			mouseenter: function( event ) {
				$( event.currentTarget ).addClass( "ui-state-hover" );
			},
			mouseleave: function( event ) {
				$( event.currentTarget ).removeClass( "ui-state-hover" );
			}
		});
	},

	_focusable: function( element ) {
		this.focusable = this.focusable.add( element );
		this._on( element, {
			focusin: function( event ) {
				$( event.currentTarget ).addClass( "ui-state-focus" );
			},
			focusout: function( event ) {
				$( event.currentTarget ).removeClass( "ui-state-focus" );
			}
		});
	},

	_trigger: function( type, event, data ) {
		var prop, orig,
			callback = this.options[ type ];

		data = data || {};
		event = $.Event( event );
		event.type = ( type === this.widgetEventPrefix ?
			type :
			this.widgetEventPrefix + type ).toLowerCase();
		// the original event may come from any element
		// so we need to reset the target on the new event
		event.target = this.element[ 0 ];

		// copy original event properties over to the new event
		orig = event.originalEvent;
		if ( orig ) {
			for ( prop in orig ) {
				if ( !( prop in event ) ) {
					event[ prop ] = orig[ prop ];
				}
			}
		}

		this.element.trigger( event, data );
		return !( $.isFunction( callback ) &&
			callback.apply( this.element[0], [ event ].concat( data ) ) === false ||
			event.isDefaultPrevented() );
	}
};

$.each( { show: "fadeIn", hide: "fadeOut" }, function( method, defaultEffect ) {
	$.Widget.prototype[ "_" + method ] = function( element, options, callback ) {
		if ( typeof options === "string" ) {
			options = { effect: options };
		}
		var hasOptions,
			effectName = !options ?
				method :
				options === true || typeof options === "number" ?
					defaultEffect :
					options.effect || defaultEffect;
		options = options || {};
		if ( typeof options === "number" ) {
			options = { duration: options };
		}
		hasOptions = !$.isEmptyObject( options );
		options.complete = callback;
		if ( options.delay ) {
			element.delay( options.delay );
		}
		if ( hasOptions && $.effects && $.effects.effect[ effectName ] ) {
			element[ method ]( options );
		} else if ( effectName !== method && element[ effectName ] ) {
			element[ effectName ]( options.duration, options.easing, callback );
		} else {
			element.queue(function( next ) {
				$( this )[ method ]();
				if ( callback ) {
					callback.call( element[ 0 ] );
				}
				next();
			});
		}
	};
});

})( jQuery );
(function( $, undefined ) {

var mouseHandled = false;
$( document ).mouseup( function() {
	mouseHandled = false;
});

$.widget("ui.mouse", {
	version: "1.10.2",
	options: {
		cancel: "input,textarea,button,select,option",
		distance: 1,
		delay: 0
	},
	_mouseInit: function() {
		var that = this;

		this.element
			.bind("mousedown."+this.widgetName, function(event) {
				return that._mouseDown(event);
			})
			.bind("click."+this.widgetName, function(event) {
				if (true === $.data(event.target, that.widgetName + ".preventClickEvent")) {
					$.removeData(event.target, that.widgetName + ".preventClickEvent");
					event.stopImmediatePropagation();
					return false;
				}
			});

		this.started = false;
	},

	// TODO: make sure destroying one instance of mouse doesn't mess with
	// other instances of mouse
	_mouseDestroy: function() {
		this.element.unbind("."+this.widgetName);
		if ( this._mouseMoveDelegate ) {
			$(document)
				.unbind("mousemove."+this.widgetName, this._mouseMoveDelegate)
				.unbind("mouseup."+this.widgetName, this._mouseUpDelegate);
		}
	},

	_mouseDown: function(event) {
		// don't let more than one widget handle mouseStart
		if( mouseHandled ) { return; }

		// we may have missed mouseup (out of window)
		(this._mouseStarted && this._mouseUp(event));

		this._mouseDownEvent = event;

		var that = this,
			btnIsLeft = (event.which === 1),
			// event.target.nodeName works around a bug in IE 8 with
			// disabled inputs (#7620)
			elIsCancel = (typeof this.options.cancel === "string" && event.target.nodeName ? $(event.target).closest(this.options.cancel).length : false);
		if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
			return true;
		}

		this.mouseDelayMet = !this.options.delay;
		if (!this.mouseDelayMet) {
			this._mouseDelayTimer = setTimeout(function() {
				that.mouseDelayMet = true;
			}, this.options.delay);
		}

		if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
			this._mouseStarted = (this._mouseStart(event) !== false);
			if (!this._mouseStarted) {
				event.preventDefault();
				return true;
			}
		}

		// Click event may never have fired (Gecko & Opera)
		if (true === $.data(event.target, this.widgetName + ".preventClickEvent")) {
			$.removeData(event.target, this.widgetName + ".preventClickEvent");
		}

		// these delegates are required to keep context
		this._mouseMoveDelegate = function(event) {
			return that._mouseMove(event);
		};
		this._mouseUpDelegate = function(event) {
			return that._mouseUp(event);
		};
		$(document)
			.bind("mousemove."+this.widgetName, this._mouseMoveDelegate)
			.bind("mouseup."+this.widgetName, this._mouseUpDelegate);

		event.preventDefault();

		mouseHandled = true;
		return true;
	},

	_mouseMove: function(event) {
		// IE mouseup check - mouseup happened when mouse was out of window
		if ($.ui.ie && ( !document.documentMode || document.documentMode < 9 ) && !event.button) {
			return this._mouseUp(event);
		}

		if (this._mouseStarted) {
			this._mouseDrag(event);
			return event.preventDefault();
		}

		if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
			this._mouseStarted =
				(this._mouseStart(this._mouseDownEvent, event) !== false);
			(this._mouseStarted ? this._mouseDrag(event) : this._mouseUp(event));
		}

		return !this._mouseStarted;
	},

	_mouseUp: function(event) {
		$(document)
			.unbind("mousemove."+this.widgetName, this._mouseMoveDelegate)
			.unbind("mouseup."+this.widgetName, this._mouseUpDelegate);

		if (this._mouseStarted) {
			this._mouseStarted = false;

			if (event.target === this._mouseDownEvent.target) {
				$.data(event.target, this.widgetName + ".preventClickEvent", true);
			}

			this._mouseStop(event);
		}

		return false;
	},

	_mouseDistanceMet: function(event) {
		return (Math.max(
				Math.abs(this._mouseDownEvent.pageX - event.pageX),
				Math.abs(this._mouseDownEvent.pageY - event.pageY)
			) >= this.options.distance
		);
	},

	_mouseDelayMet: function(/* event */) {
		return this.mouseDelayMet;
	},

	// These are placeholder methods, to be overriden by extending plugin
	_mouseStart: function(/* event */) {},
	_mouseDrag: function(/* event */) {},
	_mouseStop: function(/* event */) {},
	_mouseCapture: function(/* event */) { return true; }
});

})(jQuery);
(function( $, undefined ) {

$.ui = $.ui || {};

var cachedScrollbarWidth,
	max = Math.max,
	abs = Math.abs,
	round = Math.round,
	rhorizontal = /left|center|right/,
	rvertical = /top|center|bottom/,
	roffset = /[\+\-]\d+(\.[\d]+)?%?/,
	rposition = /^\w+/,
	rpercent = /%$/,
	_position = $.fn.position;

function getOffsets( offsets, width, height ) {
	return [
		parseFloat( offsets[ 0 ] ) * ( rpercent.test( offsets[ 0 ] ) ? width / 100 : 1 ),
		parseFloat( offsets[ 1 ] ) * ( rpercent.test( offsets[ 1 ] ) ? height / 100 : 1 )
	];
}

function parseCss( element, property ) {
	return parseInt( $.css( element, property ), 10 ) || 0;
}

function getDimensions( elem ) {
	var raw = elem[0];
	if ( raw.nodeType === 9 ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: 0, left: 0 }
		};
	}
	if ( $.isWindow( raw ) ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: elem.scrollTop(), left: elem.scrollLeft() }
		};
	}
	if ( raw.preventDefault ) {
		return {
			width: 0,
			height: 0,
			offset: { top: raw.pageY, left: raw.pageX }
		};
	}
	return {
		width: elem.outerWidth(),
		height: elem.outerHeight(),
		offset: elem.offset()
	};
}

$.position = {
	scrollbarWidth: function() {
		if ( cachedScrollbarWidth !== undefined ) {
			return cachedScrollbarWidth;
		}
		var w1, w2,
			div = $( "<div style='display:block;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>" ),
			innerDiv = div.children()[0];

		$( "body" ).append( div );
		w1 = innerDiv.offsetWidth;
		div.css( "overflow", "scroll" );

		w2 = innerDiv.offsetWidth;

		if ( w1 === w2 ) {
			w2 = div[0].clientWidth;
		}

		div.remove();

		return (cachedScrollbarWidth = w1 - w2);
	},
	getScrollInfo: function( within ) {
		var overflowX = within.isWindow ? "" : within.element.css( "overflow-x" ),
			overflowY = within.isWindow ? "" : within.element.css( "overflow-y" ),
			hasOverflowX = overflowX === "scroll" ||
				( overflowX === "auto" && within.width < within.element[0].scrollWidth ),
			hasOverflowY = overflowY === "scroll" ||
				( overflowY === "auto" && within.height < within.element[0].scrollHeight );
		return {
			width: hasOverflowY ? $.position.scrollbarWidth() : 0,
			height: hasOverflowX ? $.position.scrollbarWidth() : 0
		};
	},
	getWithinInfo: function( element ) {
		var withinElement = $( element || window ),
			isWindow = $.isWindow( withinElement[0] );
		return {
			element: withinElement,
			isWindow: isWindow,
			offset: withinElement.offset() || { left: 0, top: 0 },
			scrollLeft: withinElement.scrollLeft(),
			scrollTop: withinElement.scrollTop(),
			width: isWindow ? withinElement.width() : withinElement.outerWidth(),
			height: isWindow ? withinElement.height() : withinElement.outerHeight()
		};
	}
};

$.fn.position = function( options ) {
	if ( !options || !options.of ) {
		return _position.apply( this, arguments );
	}

	// make a copy, we don't want to modify arguments
	options = $.extend( {}, options );

	var atOffset, targetWidth, targetHeight, targetOffset, basePosition, dimensions,
		target = $( options.of ),
		within = $.position.getWithinInfo( options.within ),
		scrollInfo = $.position.getScrollInfo( within ),
		collision = ( options.collision || "flip" ).split( " " ),
		offsets = {};

	dimensions = getDimensions( target );
	if ( target[0].preventDefault ) {
		// force left top to allow flipping
		options.at = "left top";
	}
	targetWidth = dimensions.width;
	targetHeight = dimensions.height;
	targetOffset = dimensions.offset;
	// clone to reuse original targetOffset later
	basePosition = $.extend( {}, targetOffset );

	// force my and at to have valid horizontal and vertical positions
	// if a value is missing or invalid, it will be converted to center
	$.each( [ "my", "at" ], function() {
		var pos = ( options[ this ] || "" ).split( " " ),
			horizontalOffset,
			verticalOffset;

		if ( pos.length === 1) {
			pos = rhorizontal.test( pos[ 0 ] ) ?
				pos.concat( [ "center" ] ) :
				rvertical.test( pos[ 0 ] ) ?
					[ "center" ].concat( pos ) :
					[ "center", "center" ];
		}
		pos[ 0 ] = rhorizontal.test( pos[ 0 ] ) ? pos[ 0 ] : "center";
		pos[ 1 ] = rvertical.test( pos[ 1 ] ) ? pos[ 1 ] : "center";

		// calculate offsets
		horizontalOffset = roffset.exec( pos[ 0 ] );
		verticalOffset = roffset.exec( pos[ 1 ] );
		offsets[ this ] = [
			horizontalOffset ? horizontalOffset[ 0 ] : 0,
			verticalOffset ? verticalOffset[ 0 ] : 0
		];

		// reduce to just the positions without the offsets
		options[ this ] = [
			rposition.exec( pos[ 0 ] )[ 0 ],
			rposition.exec( pos[ 1 ] )[ 0 ]
		];
	});

	// normalize collision option
	if ( collision.length === 1 ) {
		collision[ 1 ] = collision[ 0 ];
	}

	if ( options.at[ 0 ] === "right" ) {
		basePosition.left += targetWidth;
	} else if ( options.at[ 0 ] === "center" ) {
		basePosition.left += targetWidth / 2;
	}

	if ( options.at[ 1 ] === "bottom" ) {
		basePosition.top += targetHeight;
	} else if ( options.at[ 1 ] === "center" ) {
		basePosition.top += targetHeight / 2;
	}

	atOffset = getOffsets( offsets.at, targetWidth, targetHeight );
	basePosition.left += atOffset[ 0 ];
	basePosition.top += atOffset[ 1 ];

	return this.each(function() {
		var collisionPosition, using,
			elem = $( this ),
			elemWidth = elem.outerWidth(),
			elemHeight = elem.outerHeight(),
			marginLeft = parseCss( this, "marginLeft" ),
			marginTop = parseCss( this, "marginTop" ),
			collisionWidth = elemWidth + marginLeft + parseCss( this, "marginRight" ) + scrollInfo.width,
			collisionHeight = elemHeight + marginTop + parseCss( this, "marginBottom" ) + scrollInfo.height,
			position = $.extend( {}, basePosition ),
			myOffset = getOffsets( offsets.my, elem.outerWidth(), elem.outerHeight() );

		if ( options.my[ 0 ] === "right" ) {
			position.left -= elemWidth;
		} else if ( options.my[ 0 ] === "center" ) {
			position.left -= elemWidth / 2;
		}

		if ( options.my[ 1 ] === "bottom" ) {
			position.top -= elemHeight;
		} else if ( options.my[ 1 ] === "center" ) {
			position.top -= elemHeight / 2;
		}

		position.left += myOffset[ 0 ];
		position.top += myOffset[ 1 ];

		// if the browser doesn't support fractions, then round for consistent results
		if ( !$.support.offsetFractions ) {
			position.left = round( position.left );
			position.top = round( position.top );
		}

		collisionPosition = {
			marginLeft: marginLeft,
			marginTop: marginTop
		};

		$.each( [ "left", "top" ], function( i, dir ) {
			if ( $.ui.position[ collision[ i ] ] ) {
				$.ui.position[ collision[ i ] ][ dir ]( position, {
					targetWidth: targetWidth,
					targetHeight: targetHeight,
					elemWidth: elemWidth,
					elemHeight: elemHeight,
					collisionPosition: collisionPosition,
					collisionWidth: collisionWidth,
					collisionHeight: collisionHeight,
					offset: [ atOffset[ 0 ] + myOffset[ 0 ], atOffset [ 1 ] + myOffset[ 1 ] ],
					my: options.my,
					at: options.at,
					within: within,
					elem : elem
				});
			}
		});

		if ( options.using ) {
			// adds feedback as second argument to using callback, if present
			using = function( props ) {
				var left = targetOffset.left - position.left,
					right = left + targetWidth - elemWidth,
					top = targetOffset.top - position.top,
					bottom = top + targetHeight - elemHeight,
					feedback = {
						target: {
							element: target,
							left: targetOffset.left,
							top: targetOffset.top,
							width: targetWidth,
							height: targetHeight
						},
						element: {
							element: elem,
							left: position.left,
							top: position.top,
							width: elemWidth,
							height: elemHeight
						},
						horizontal: right < 0 ? "left" : left > 0 ? "right" : "center",
						vertical: bottom < 0 ? "top" : top > 0 ? "bottom" : "middle"
					};
				if ( targetWidth < elemWidth && abs( left + right ) < targetWidth ) {
					feedback.horizontal = "center";
				}
				if ( targetHeight < elemHeight && abs( top + bottom ) < targetHeight ) {
					feedback.vertical = "middle";
				}
				if ( max( abs( left ), abs( right ) ) > max( abs( top ), abs( bottom ) ) ) {
					feedback.important = "horizontal";
				} else {
					feedback.important = "vertical";
				}
				options.using.call( this, props, feedback );
			};
		}

		elem.offset( $.extend( position, { using: using } ) );
	});
};

$.ui.position = {
	fit: {
		left: function( position, data ) {
			var within = data.within,
				withinOffset = within.isWindow ? within.scrollLeft : within.offset.left,
				outerWidth = within.width,
				collisionPosLeft = position.left - data.collisionPosition.marginLeft,
				overLeft = withinOffset - collisionPosLeft,
				overRight = collisionPosLeft + data.collisionWidth - outerWidth - withinOffset,
				newOverRight;

			// element is wider than within
			if ( data.collisionWidth > outerWidth ) {
				// element is initially over the left side of within
				if ( overLeft > 0 && overRight <= 0 ) {
					newOverRight = position.left + overLeft + data.collisionWidth - outerWidth - withinOffset;
					position.left += overLeft - newOverRight;
				// element is initially over right side of within
				} else if ( overRight > 0 && overLeft <= 0 ) {
					position.left = withinOffset;
				// element is initially over both left and right sides of within
				} else {
					if ( overLeft > overRight ) {
						position.left = withinOffset + outerWidth - data.collisionWidth;
					} else {
						position.left = withinOffset;
					}
				}
			// too far left -> align with left edge
			} else if ( overLeft > 0 ) {
				position.left += overLeft;
			// too far right -> align with right edge
			} else if ( overRight > 0 ) {
				position.left -= overRight;
			// adjust based on position and margin
			} else {
				position.left = max( position.left - collisionPosLeft, position.left );
			}
		},
		top: function( position, data ) {
			var within = data.within,
				withinOffset = within.isWindow ? within.scrollTop : within.offset.top,
				outerHeight = data.within.height,
				collisionPosTop = position.top - data.collisionPosition.marginTop,
				overTop = withinOffset - collisionPosTop,
				overBottom = collisionPosTop + data.collisionHeight - outerHeight - withinOffset,
				newOverBottom;

			// element is taller than within
			if ( data.collisionHeight > outerHeight ) {
				// element is initially over the top of within
				if ( overTop > 0 && overBottom <= 0 ) {
					newOverBottom = position.top + overTop + data.collisionHeight - outerHeight - withinOffset;
					position.top += overTop - newOverBottom;
				// element is initially over bottom of within
				} else if ( overBottom > 0 && overTop <= 0 ) {
					position.top = withinOffset;
				// element is initially over both top and bottom of within
				} else {
					if ( overTop > overBottom ) {
						position.top = withinOffset + outerHeight - data.collisionHeight;
					} else {
						position.top = withinOffset;
					}
				}
			// too far up -> align with top
			} else if ( overTop > 0 ) {
				position.top += overTop;
			// too far down -> align with bottom edge
			} else if ( overBottom > 0 ) {
				position.top -= overBottom;
			// adjust based on position and margin
			} else {
				position.top = max( position.top - collisionPosTop, position.top );
			}
		}
	},
	flip: {
		left: function( position, data ) {
			var within = data.within,
				withinOffset = within.offset.left + within.scrollLeft,
				outerWidth = within.width,
				offsetLeft = within.isWindow ? within.scrollLeft : within.offset.left,
				collisionPosLeft = position.left - data.collisionPosition.marginLeft,
				overLeft = collisionPosLeft - offsetLeft,
				overRight = collisionPosLeft + data.collisionWidth - outerWidth - offsetLeft,
				myOffset = data.my[ 0 ] === "left" ?
					-data.elemWidth :
					data.my[ 0 ] === "right" ?
						data.elemWidth :
						0,
				atOffset = data.at[ 0 ] === "left" ?
					data.targetWidth :
					data.at[ 0 ] === "right" ?
						-data.targetWidth :
						0,
				offset = -2 * data.offset[ 0 ],
				newOverRight,
				newOverLeft;

			if ( overLeft < 0 ) {
				newOverRight = position.left + myOffset + atOffset + offset + data.collisionWidth - outerWidth - withinOffset;
				if ( newOverRight < 0 || newOverRight < abs( overLeft ) ) {
					position.left += myOffset + atOffset + offset;
				}
			}
			else if ( overRight > 0 ) {
				newOverLeft = position.left - data.collisionPosition.marginLeft + myOffset + atOffset + offset - offsetLeft;
				if ( newOverLeft > 0 || abs( newOverLeft ) < overRight ) {
					position.left += myOffset + atOffset + offset;
				}
			}
		},
		top: function( position, data ) {
			var within = data.within,
				withinOffset = within.offset.top + within.scrollTop,
				outerHeight = within.height,
				offsetTop = within.isWindow ? within.scrollTop : within.offset.top,
				collisionPosTop = position.top - data.collisionPosition.marginTop,
				overTop = collisionPosTop - offsetTop,
				overBottom = collisionPosTop + data.collisionHeight - outerHeight - offsetTop,
				top = data.my[ 1 ] === "top",
				myOffset = top ?
					-data.elemHeight :
					data.my[ 1 ] === "bottom" ?
						data.elemHeight :
						0,
				atOffset = data.at[ 1 ] === "top" ?
					data.targetHeight :
					data.at[ 1 ] === "bottom" ?
						-data.targetHeight :
						0,
				offset = -2 * data.offset[ 1 ],
				newOverTop,
				newOverBottom;
			if ( overTop < 0 ) {
				newOverBottom = position.top + myOffset + atOffset + offset + data.collisionHeight - outerHeight - withinOffset;
				if ( ( position.top + myOffset + atOffset + offset) > overTop && ( newOverBottom < 0 || newOverBottom < abs( overTop ) ) ) {
					position.top += myOffset + atOffset + offset;
				}
			}
			else if ( overBottom > 0 ) {
				newOverTop = position.top -  data.collisionPosition.marginTop + myOffset + atOffset + offset - offsetTop;
				if ( ( position.top + myOffset + atOffset + offset) > overBottom && ( newOverTop > 0 || abs( newOverTop ) < overBottom ) ) {
					position.top += myOffset + atOffset + offset;
				}
			}
		}
	},
	flipfit: {
		left: function() {
			$.ui.position.flip.left.apply( this, arguments );
			$.ui.position.fit.left.apply( this, arguments );
		},
		top: function() {
			$.ui.position.flip.top.apply( this, arguments );
			$.ui.position.fit.top.apply( this, arguments );
		}
	}
};

// fraction support test
(function () {
	var testElement, testElementParent, testElementStyle, offsetLeft, i,
		body = document.getElementsByTagName( "body" )[ 0 ],
		div = document.createElement( "div" );

	//Create a "fake body" for testing based on method used in jQuery.support
	testElement = document.createElement( body ? "div" : "body" );
	testElementStyle = {
		visibility: "hidden",
		width: 0,
		height: 0,
		border: 0,
		margin: 0,
		background: "none"
	};
	if ( body ) {
		$.extend( testElementStyle, {
			position: "absolute",
			left: "-1000px",
			top: "-1000px"
		});
	}
	for ( i in testElementStyle ) {
		testElement.style[ i ] = testElementStyle[ i ];
	}
	testElement.appendChild( div );
	testElementParent = body || document.documentElement;
	testElementParent.insertBefore( testElement, testElementParent.firstChild );

	div.style.cssText = "position: absolute; left: 10.7432222px;";

	offsetLeft = $( div ).offset().left;
	$.support.offsetFractions = offsetLeft > 10 && offsetLeft < 11;

	testElement.innerHTML = "";
	testElementParent.removeChild( testElement );
})();

}( jQuery ) );
(function( $, undefined ) {

// number of pages in a slider
// (how many times can you page up/down to go through the whole range)
var numPages = 5;

$.widget( "ui.slider", $.ui.mouse, {
	version: "1.10.2",
	widgetEventPrefix: "slide",

	options: {
		animate: false,
		distance: 0,
		max: 100,
		min: 0,
		orientation: "horizontal",
		range: false,
		step: 1,
		value: 0,
		values: null,

		// callbacks
		change: null,
		slide: null,
		start: null,
		stop: null
	},

	_create: function() {
		this._keySliding = false;
		this._mouseSliding = false;
		this._animateOff = true;
		this._handleIndex = null;
		this._detectOrientation();
		this._mouseInit();

		this.element
			.addClass( "ui-slider" +
				" ui-slider-" + this.orientation +
				" ui-widget" +
				" ui-widget-content" +
				" ui-corner-all");

		this._refresh();
		this._setOption( "disabled", this.options.disabled );

		this._animateOff = false;
	},

	_refresh: function() {
		this._createRange();
		this._createHandles();
		this._setupEvents();
		this._refreshValue();
	},

	_createHandles: function() {
		var i, handleCount,
			options = this.options,
			existingHandles = this.element.find( ".ui-slider-handle" ).addClass( "ui-state-default ui-corner-all" ),
			handle = "<a class='ui-slider-handle ui-state-default ui-corner-all' href='#'></a>",
			handles = [];

		handleCount = ( options.values && options.values.length ) || 1;

		if ( existingHandles.length > handleCount ) {
			existingHandles.slice( handleCount ).remove();
			existingHandles = existingHandles.slice( 0, handleCount );
		}

		for ( i = existingHandles.length; i < handleCount; i++ ) {
			handles.push( handle );
		}

		this.handles = existingHandles.add( $( handles.join( "" ) ).appendTo( this.element ) );

		this.handle = this.handles.eq( 0 );

		this.handles.each(function( i ) {
			$( this ).data( "ui-slider-handle-index", i );
		});
	},

	_createRange: function() {
		var options = this.options,
			classes = "";

		if ( options.range ) {
			if ( options.range === true ) {
				if ( !options.values ) {
					options.values = [ this._valueMin(), this._valueMin() ];
				} else if ( options.values.length && options.values.length !== 2 ) {
					options.values = [ options.values[0], options.values[0] ];
				} else if ( $.isArray( options.values ) ) {
					options.values = options.values.slice(0);
				}
			}

			if ( !this.range || !this.range.length ) {
				this.range = $( "<div></div>" )
					.appendTo( this.element );

				classes = "ui-slider-range" +
				// note: this isn't the most fittingly semantic framework class for this element,
				// but worked best visually with a variety of themes
				" ui-widget-header ui-corner-all";
			} else {
				this.range.removeClass( "ui-slider-range-min ui-slider-range-max" )
					// Handle range switching from true to min/max
					.css({
						"left": "",
						"bottom": ""
					});
			}

			this.range.addClass( classes +
				( ( options.range === "min" || options.range === "max" ) ? " ui-slider-range-" + options.range : "" ) );
		} else {
			this.range = $([]);
		}
	},

	_setupEvents: function() {
		var elements = this.handles.add( this.range ).filter( "a" );
		this._off( elements );
		this._on( elements, this._handleEvents );
		this._hoverable( elements );
		this._focusable( elements );
	},

	_destroy: function() {
		this.handles.remove();
		this.range.remove();

		this.element
			.removeClass( "ui-slider" +
				" ui-slider-horizontal" +
				" ui-slider-vertical" +
				" ui-widget" +
				" ui-widget-content" +
				" ui-corner-all" );

		this._mouseDestroy();
	},

	_mouseCapture: function( event ) {
		var position, normValue, distance, closestHandle, index, allowed, offset, mouseOverHandle,
			that = this,
			o = this.options;

		if ( o.disabled ) {
			return false;
		}

		this.elementSize = {
			width: this.element.outerWidth(),
			height: this.element.outerHeight()
		};
		this.elementOffset = this.element.offset();

		position = { x: event.pageX, y: event.pageY };
		normValue = this._normValueFromMouse( position );
		distance = this._valueMax() - this._valueMin() + 1;
		this.handles.each(function( i ) {
			var thisDistance = Math.abs( normValue - that.values(i) );
			if (( distance > thisDistance ) ||
				( distance === thisDistance &&
					(i === that._lastChangedValue || that.values(i) === o.min ))) {
				distance = thisDistance;
				closestHandle = $( this );
				index = i;
			}
		});

		allowed = this._start( event, index );
		if ( allowed === false ) {
			return false;
		}
		this._mouseSliding = true;

		this._handleIndex = index;

		closestHandle
			.addClass( "ui-state-active" )
			.focus();

		offset = closestHandle.offset();
		mouseOverHandle = !$( event.target ).parents().addBack().is( ".ui-slider-handle" );
		this._clickOffset = mouseOverHandle ? { left: 0, top: 0 } : {
			left: event.pageX - offset.left - ( closestHandle.width() / 2 ),
			top: event.pageY - offset.top -
				( closestHandle.height() / 2 ) -
				( parseInt( closestHandle.css("borderTopWidth"), 10 ) || 0 ) -
				( parseInt( closestHandle.css("borderBottomWidth"), 10 ) || 0) +
				( parseInt( closestHandle.css("marginTop"), 10 ) || 0)
		};

		if ( !this.handles.hasClass( "ui-state-hover" ) ) {
			this._slide( event, index, normValue );
		}
		this._animateOff = true;
		return true;
	},

	_mouseStart: function() {
		return true;
	},

	_mouseDrag: function( event ) {
		var position = { x: event.pageX, y: event.pageY },
			normValue = this._normValueFromMouse( position );

		this._slide( event, this._handleIndex, normValue );

		return false;
	},

	_mouseStop: function( event ) {
		this.handles.removeClass( "ui-state-active" );
		this._mouseSliding = false;

		this._stop( event, this._handleIndex );
		this._change( event, this._handleIndex );

		this._handleIndex = null;
		this._clickOffset = null;
		this._animateOff = false;

		return false;
	},

	_detectOrientation: function() {
		this.orientation = ( this.options.orientation === "vertical" ) ? "vertical" : "horizontal";
	},

	_normValueFromMouse: function( position ) {
		var pixelTotal,
			pixelMouse,
			percentMouse,
			valueTotal,
			valueMouse;

		if ( this.orientation === "horizontal" ) {
			pixelTotal = this.elementSize.width;
			pixelMouse = position.x - this.elementOffset.left - ( this._clickOffset ? this._clickOffset.left : 0 );
		} else {
			pixelTotal = this.elementSize.height;
			pixelMouse = position.y - this.elementOffset.top - ( this._clickOffset ? this._clickOffset.top : 0 );
		}

		percentMouse = ( pixelMouse / pixelTotal );
		if ( percentMouse > 1 ) {
			percentMouse = 1;
		}
		if ( percentMouse < 0 ) {
			percentMouse = 0;
		}
		if ( this.orientation === "vertical" ) {
			percentMouse = 1 - percentMouse;
		}

		valueTotal = this._valueMax() - this._valueMin();
		valueMouse = this._valueMin() + percentMouse * valueTotal;

		return this._trimAlignValue( valueMouse );
	},

	_start: function( event, index ) {
		var uiHash = {
			handle: this.handles[ index ],
			value: this.value()
		};
		if ( this.options.values && this.options.values.length ) {
			uiHash.value = this.values( index );
			uiHash.values = this.values();
		}
		return this._trigger( "start", event, uiHash );
	},

	_slide: function( event, index, newVal ) {
		var otherVal,
			newValues,
			allowed;

		if ( this.options.values && this.options.values.length ) {
			otherVal = this.values( index ? 0 : 1 );

			if ( ( this.options.values.length === 2 && this.options.range === true ) &&
					( ( index === 0 && newVal > otherVal) || ( index === 1 && newVal < otherVal ) )
				) {
				newVal = otherVal;
			}

			if ( newVal !== this.values( index ) ) {
				newValues = this.values();
				newValues[ index ] = newVal;
				// A slide can be canceled by returning false from the slide callback
				allowed = this._trigger( "slide", event, {
					handle: this.handles[ index ],
					value: newVal,
					values: newValues
				} );
				otherVal = this.values( index ? 0 : 1 );
				if ( allowed !== false ) {
					this.values( index, newVal, true );
				}
			}
		} else {
			if ( newVal !== this.value() ) {
				// A slide can be canceled by returning false from the slide callback
				allowed = this._trigger( "slide", event, {
					handle: this.handles[ index ],
					value: newVal
				} );
				if ( allowed !== false ) {
					this.value( newVal );
				}
			}
		}
	},

	_stop: function( event, index ) {
		var uiHash = {
			handle: this.handles[ index ],
			value: this.value()
		};
		if ( this.options.values && this.options.values.length ) {
			uiHash.value = this.values( index );
			uiHash.values = this.values();
		}

		this._trigger( "stop", event, uiHash );
	},

	_change: function( event, index ) {
		if ( !this._keySliding && !this._mouseSliding ) {
			var uiHash = {
				handle: this.handles[ index ],
				value: this.value()
			};
			if ( this.options.values && this.options.values.length ) {
				uiHash.value = this.values( index );
				uiHash.values = this.values();
			}

			//store the last changed value index for reference when handles overlap
			this._lastChangedValue = index;

			this._trigger( "change", event, uiHash );
		}
	},

	value: function( newValue ) {
		if ( arguments.length ) {
			this.options.value = this._trimAlignValue( newValue );
			this._refreshValue();
			this._change( null, 0 );
			return;
		}

		return this._value();
	},

	values: function( index, newValue ) {
		var vals,
			newValues,
			i;

		if ( arguments.length > 1 ) {
			this.options.values[ index ] = this._trimAlignValue( newValue );
			this._refreshValue();
			this._change( null, index );
			return;
		}

		if ( arguments.length ) {
			if ( $.isArray( arguments[ 0 ] ) ) {
				vals = this.options.values;
				newValues = arguments[ 0 ];
				for ( i = 0; i < vals.length; i += 1 ) {
					vals[ i ] = this._trimAlignValue( newValues[ i ] );
					this._change( null, i );
				}
				this._refreshValue();
			} else {
				if ( this.options.values && this.options.values.length ) {
					return this._values( index );
				} else {
					return this.value();
				}
			}
		} else {
			return this._values();
		}
	},

	_setOption: function( key, value ) {
		var i,
			valsLength = 0;

		if ( key === "range" && this.options.range === true ) {
			if ( value === "min" ) {
				this.options.value = this._values( 0 );
				this.options.values = null;
			} else if ( value === "max" ) {
				this.options.value = this._values( this.options.values.length-1 );
				this.options.values = null;
			}
		}

		if ( $.isArray( this.options.values ) ) {
			valsLength = this.options.values.length;
		}

		$.Widget.prototype._setOption.apply( this, arguments );

		switch ( key ) {
			case "orientation":
				this._detectOrientation();
				this.element
					.removeClass( "ui-slider-horizontal ui-slider-vertical" )
					.addClass( "ui-slider-" + this.orientation );
				this._refreshValue();
				break;
			case "value":
				this._animateOff = true;
				this._refreshValue();
				this._change( null, 0 );
				this._animateOff = false;
				break;
			case "values":
				this._animateOff = true;
				this._refreshValue();
				for ( i = 0; i < valsLength; i += 1 ) {
					this._change( null, i );
				}
				this._animateOff = false;
				break;
			case "min":
			case "max":
				this._animateOff = true;
				this._refreshValue();
				this._animateOff = false;
				break;
			case "range":
				this._animateOff = true;
				this._refresh();
				this._animateOff = false;
				break;
		}
	},

	//internal value getter
	// _value() returns value trimmed by min and max, aligned by step
	_value: function() {
		var val = this.options.value;
		val = this._trimAlignValue( val );

		return val;
	},

	//internal values getter
	// _values() returns array of values trimmed by min and max, aligned by step
	// _values( index ) returns single value trimmed by min and max, aligned by step
	_values: function( index ) {
		var val,
			vals,
			i;

		if ( arguments.length ) {
			val = this.options.values[ index ];
			val = this._trimAlignValue( val );

			return val;
		} else if ( this.options.values && this.options.values.length ) {
			// .slice() creates a copy of the array
			// this copy gets trimmed by min and max and then returned
			vals = this.options.values.slice();
			for ( i = 0; i < vals.length; i+= 1) {
				vals[ i ] = this._trimAlignValue( vals[ i ] );
			}

			return vals;
		} else {
			return [];
		}
	},

	// returns the step-aligned value that val is closest to, between (inclusive) min and max
	_trimAlignValue: function( val ) {
		if ( val <= this._valueMin() ) {
			return this._valueMin();
		}
		if ( val >= this._valueMax() ) {
			return this._valueMax();
		}
		var step = ( this.options.step > 0 ) ? this.options.step : 1,
			valModStep = (val - this._valueMin()) % step,
			alignValue = val - valModStep;

		if ( Math.abs(valModStep) * 2 >= step ) {
			alignValue += ( valModStep > 0 ) ? step : ( -step );
		}

		// Since JavaScript has problems with large floats, round
		// the final value to 5 digits after the decimal point (see #4124)
		return parseFloat( alignValue.toFixed(5) );
	},

	_valueMin: function() {
		return this.options.min;
	},

	_valueMax: function() {
		return this.options.max;
	},

	_refreshValue: function() {
		var lastValPercent, valPercent, value, valueMin, valueMax,
			oRange = this.options.range,
			o = this.options,
			that = this,
			animate = ( !this._animateOff ) ? o.animate : false,
			_set = {};

		if ( this.options.values && this.options.values.length ) {
			this.handles.each(function( i ) {
				valPercent = ( that.values(i) - that._valueMin() ) / ( that._valueMax() - that._valueMin() ) * 100;
				_set[ that.orientation === "horizontal" ? "left" : "bottom" ] = valPercent + "%";
				$( this ).stop( 1, 1 )[ animate ? "animate" : "css" ]( _set, o.animate );
				if ( that.options.range === true ) {
					if ( that.orientation === "horizontal" ) {
						if ( i === 0 ) {
							that.range.stop( 1, 1 )[ animate ? "animate" : "css" ]( { left: valPercent + "%" }, o.animate );
						}
						if ( i === 1 ) {
							that.range[ animate ? "animate" : "css" ]( { width: ( valPercent - lastValPercent ) + "%" }, { queue: false, duration: o.animate } );
						}
					} else {
						if ( i === 0 ) {
							that.range.stop( 1, 1 )[ animate ? "animate" : "css" ]( { bottom: ( valPercent ) + "%" }, o.animate );
						}
						if ( i === 1 ) {
							that.range[ animate ? "animate" : "css" ]( { height: ( valPercent - lastValPercent ) + "%" }, { queue: false, duration: o.animate } );
						}
					}
				}
				lastValPercent = valPercent;
			});
		} else {
			value = this.value();
			valueMin = this._valueMin();
			valueMax = this._valueMax();
			valPercent = ( valueMax !== valueMin ) ?
					( value - valueMin ) / ( valueMax - valueMin ) * 100 :
					0;
			_set[ this.orientation === "horizontal" ? "left" : "bottom" ] = valPercent + "%";
			this.handle.stop( 1, 1 )[ animate ? "animate" : "css" ]( _set, o.animate );

			if ( oRange === "min" && this.orientation === "horizontal" ) {
				this.range.stop( 1, 1 )[ animate ? "animate" : "css" ]( { width: valPercent + "%" }, o.animate );
			}
			if ( oRange === "max" && this.orientation === "horizontal" ) {
				this.range[ animate ? "animate" : "css" ]( { width: ( 100 - valPercent ) + "%" }, { queue: false, duration: o.animate } );
			}
			if ( oRange === "min" && this.orientation === "vertical" ) {
				this.range.stop( 1, 1 )[ animate ? "animate" : "css" ]( { height: valPercent + "%" }, o.animate );
			}
			if ( oRange === "max" && this.orientation === "vertical" ) {
				this.range[ animate ? "animate" : "css" ]( { height: ( 100 - valPercent ) + "%" }, { queue: false, duration: o.animate } );
			}
		}
	},

	_handleEvents: {
		keydown: function( event ) {
			/*jshint maxcomplexity:25*/
			var allowed, curVal, newVal, step,
				index = $( event.target ).data( "ui-slider-handle-index" );

			switch ( event.keyCode ) {
				case $.ui.keyCode.HOME:
				case $.ui.keyCode.END:
				case $.ui.keyCode.PAGE_UP:
				case $.ui.keyCode.PAGE_DOWN:
				case $.ui.keyCode.UP:
				case $.ui.keyCode.RIGHT:
				case $.ui.keyCode.DOWN:
				case $.ui.keyCode.LEFT:
					event.preventDefault();
					if ( !this._keySliding ) {
						this._keySliding = true;
						$( event.target ).addClass( "ui-state-active" );
						allowed = this._start( event, index );
						if ( allowed === false ) {
							return;
						}
					}
					break;
			}

			step = this.options.step;
			if ( this.options.values && this.options.values.length ) {
				curVal = newVal = this.values( index );
			} else {
				curVal = newVal = this.value();
			}

			switch ( event.keyCode ) {
				case $.ui.keyCode.HOME:
					newVal = this._valueMin();
					break;
				case $.ui.keyCode.END:
					newVal = this._valueMax();
					break;
				case $.ui.keyCode.PAGE_UP:
					newVal = this._trimAlignValue( curVal + ( (this._valueMax() - this._valueMin()) / numPages ) );
					break;
				case $.ui.keyCode.PAGE_DOWN:
					newVal = this._trimAlignValue( curVal - ( (this._valueMax() - this._valueMin()) / numPages ) );
					break;
				case $.ui.keyCode.UP:
				case $.ui.keyCode.RIGHT:
					if ( curVal === this._valueMax() ) {
						return;
					}
					newVal = this._trimAlignValue( curVal + step );
					break;
				case $.ui.keyCode.DOWN:
				case $.ui.keyCode.LEFT:
					if ( curVal === this._valueMin() ) {
						return;
					}
					newVal = this._trimAlignValue( curVal - step );
					break;
			}

			this._slide( event, index, newVal );
		},
		click: function( event ) {
			event.preventDefault();
		},
		keyup: function( event ) {
			var index = $( event.target ).data( "ui-slider-handle-index" );

			if ( this._keySliding ) {
				this._keySliding = false;
				this._stop( event, index );
				this._change( event, index );
				$( event.target ).removeClass( "ui-state-active" );
			}
		}
	}

});

}(jQuery));

/*!
 * zeroclipboard
 * The Zero Clipboard library provides an easy way to copy text to the clipboard using an invisible Adobe Flash movie, and a JavaScript interface.
 * Copyright 2012 Jon Rohan, James M. Greene, .
 * Released under the MIT license
 * http://jonrohan.github.com/ZeroClipboard/
 * v1.1.7
 */(function(){"use strict";var a=function(a,b){var c=a.style[b];a.currentStyle?c=a.currentStyle[b]:window.getComputedStyle&&(c=document.defaultView.getComputedStyle(a,null).getPropertyValue(b));if(c=="auto"&&b=="cursor"){var d=["a"];for(var e=0;e<d.length;e++)if(a.tagName.toLowerCase()==d[e])return"pointer"}return c},b=function(a){if(!l.prototype._singleton)return;a||(a=window.event);var b;this!==window?b=this:a.target?b=a.target:a.srcElement&&(b=a.srcElement),l.prototype._singleton.setCurrent(b)},c=function(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent&&a.attachEvent("on"+b,c)},d=function(a,b,c){a.removeEventListener?a.removeEventListener(b,c,!1):a.detachEvent&&a.detachEvent("on"+b,c)},e=function(a,b){if(a.addClass)return a.addClass(b),a;if(b&&typeof b=="string"){var c=(b||"").split(/\s+/);if(a.nodeType===1)if(!a.className)a.className=b;else{var d=" "+a.className+" ",e=a.className;for(var f=0,g=c.length;f<g;f++)d.indexOf(" "+c[f]+" ")<0&&(e+=" "+c[f]);a.className=e.replace(/^\s+|\s+$/g,"")}}return a},f=function(a,b){if(a.removeClass)return a.removeClass(b),a;if(b&&typeof b=="string"||b===undefined){var c=(b||"").split(/\s+/);if(a.nodeType===1&&a.className)if(b){var d=(" "+a.className+" ").replace(/[\n\t]/g," ");for(var e=0,f=c.length;e<f;e++)d=d.replace(" "+c[e]+" "," ");a.className=d.replace(/^\s+|\s+$/g,"")}else a.className=""}return a},g=function(b){var c={left:0,top:0,width:b.width||b.offsetWidth||0,height:b.height||b.offsetHeight||0,zIndex:9999},d=a(b,"zIndex");d&&d!="auto"&&(c.zIndex=parseInt(d,10));while(b){var e=parseInt(a(b,"borderLeftWidth"),10),f=parseInt(a(b,"borderTopWidth"),10);c.left+=isNaN(b.offsetLeft)?0:b.offsetLeft,c.left+=isNaN(e)?0:e,c.top+=isNaN(b.offsetTop)?0:b.offsetTop,c.top+=isNaN(f)?0:f,b=b.offsetParent}return c},h=function(a){return(a.indexOf("?")>=0?"&":"?")+"nocache="+(new Date).getTime()},i=function(a){var b=[];return a.trustedDomains&&(typeof a.trustedDomains=="string"?b.push("trustedDomain="+a.trustedDomains):b.push("trustedDomain="+a.trustedDomains.join(","))),b.join("&")},j=function(a,b){if(b.indexOf)return b.indexOf(a);for(var c=0,d=b.length;c<d;c++)if(b[c]===a)return c;return-1},k=function(a){if(typeof a=="string")throw new TypeError("ZeroClipboard doesn't accept query strings.");return a.length?a:[a]},l=function(a,b){a&&(l.prototype._singleton||this).glue(a);if(l.prototype._singleton)return l.prototype._singleton;l.prototype._singleton=this,this.options={};for(var c in o)this.options[c]=o[c];for(var d in b)this.options[d]=b[d];this.handlers={},l.detectFlashSupport()&&p()},m,n=[];l.prototype.setCurrent=function(b){m=b,this.reposition(),b.getAttribute("title")&&this.setTitle(b.getAttribute("title")),this.setHandCursor(a(b,"cursor")=="pointer")},l.prototype.setText=function(a){a&&a!==""&&(this.options.text=a,this.ready()&&this.flashBridge.setText(a))},l.prototype.setTitle=function(a){a&&a!==""&&this.htmlBridge.setAttribute("title",a)},l.prototype.setSize=function(a,b){this.ready()&&this.flashBridge.setSize(a,b)},l.prototype.setHandCursor=function(a){this.ready()&&this.flashBridge.setHandCursor(a)},l.version="1.1.7";var o={moviePath:"ZeroClipboard.swf",trustedDomains:null,text:null,hoverClass:"zeroclipboard-is-hover",activeClass:"zeroclipboard-is-active",allowScriptAccess:"always"};l.setDefaults=function(a){for(var b in a)o[b]=a[b]},l.destroy=function(){l.prototype._singleton.unglue(n);var a=l.prototype._singleton.htmlBridge;a.parentNode.removeChild(a),delete l.prototype._singleton},l.detectFlashSupport=function(){var a=!1;try{new ActiveXObject("ShockwaveFlash.ShockwaveFlash")&&(a=!0)}catch(b){navigator.mimeTypes["application/x-shockwave-flash"]&&(a=!0)}return a};var p=function(){var a=l.prototype._singleton,b=document.getElementById("global-zeroclipboard-html-bridge");if(!b){var c='      <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" id="global-zeroclipboard-flash-bridge" width="100%" height="100%">         <param name="movie" value="'+a.options.moviePath+h(a.options.moviePath)+'"/>         <param name="allowScriptAccess" value="'+a.options.allowScriptAccess+'"/>         <param name="scale" value="exactfit"/>         <param name="loop" value="false"/>         <param name="menu" value="false"/>         <param name="quality" value="best" />         <param name="bgcolor" value="#ffffff"/>         <param name="wmode" value="transparent"/>         <param name="flashvars" value="'+i(a.options)+'"/>         <embed src="'+a.options.moviePath+h(a.options.moviePath)+'"           loop="false" menu="false"           quality="best" bgcolor="#ffffff"           width="100%" height="100%"           name="global-zeroclipboard-flash-bridge"           allowScriptAccess="always"           allowFullScreen="false"           type="application/x-shockwave-flash"           wmode="transparent"           pluginspage="http://www.macromedia.com/go/getflashplayer"           flashvars="'+i(a.options)+'"           scale="exactfit">         </embed>       </object>';b=document.createElement("div"),b.id="global-zeroclipboard-html-bridge",b.setAttribute("class","global-zeroclipboard-container"),b.setAttribute("data-clipboard-ready",!1),b.style.position="absolute",b.style.left="-9999px",b.style.top="-9999px",b.style.width="15px",b.style.height="15px",b.style.zIndex="9999",b.innerHTML=c,document.body.appendChild(b)}a.htmlBridge=b,a.flashBridge=document["global-zeroclipboard-flash-bridge"]||b.children[0].lastElementChild};l.prototype.resetBridge=function(){this.htmlBridge.style.left="-9999px",this.htmlBridge.style.top="-9999px",this.htmlBridge.removeAttribute("title"),this.htmlBridge.removeAttribute("data-clipboard-text"),f(m,this.options.activeClass),m=null,this.options.text=null},l.prototype.ready=function(){var a=this.htmlBridge.getAttribute("data-clipboard-ready");return a==="true"||a===!0},l.prototype.reposition=function(){if(!m)return!1;var a=g(m);this.htmlBridge.style.top=a.top+"px",this.htmlBridge.style.left=a.left+"px",this.htmlBridge.style.width=a.width+"px",this.htmlBridge.style.height=a.height+"px",this.htmlBridge.style.zIndex=a.zIndex+1,this.setSize(a.width,a.height)},l.dispatch=function(a,b){l.prototype._singleton.receiveEvent(a,b)},l.prototype.on=function(a,b){var c=a.toString().split(/\s/g);for(var d=0;d<c.length;d++)a=c[d].toLowerCase().replace(/^on/,""),this.handlers[a]||(this.handlers[a]=b);this.handlers.noflash&&!l.detectFlashSupport()&&this.receiveEvent("onNoFlash",null)},l.prototype.addEventListener=l.prototype.on,l.prototype.off=function(a,b){var c=a.toString().split(/\s/g);for(var d=0;d<c.length;d++){a=c[d].toLowerCase().replace(/^on/,"");for(var e in this.handlers)e===a&&this.handlers[e]===b&&delete this.handlers[e]}},l.prototype.removeEventListener=l.prototype.off,l.prototype.receiveEvent=function(a,b){a=a.toString().toLowerCase().replace(/^on/,"");var c=m;switch(a){case"load":if(b&&parseFloat(b.flashVersion.replace(",",".").replace(/[^0-9\.]/gi,""))<10){this.receiveEvent("onWrongFlash",{flashVersion:b.flashVersion});return}this.htmlBridge.setAttribute("data-clipboard-ready",!0);break;case"mouseover":e(c,this.options.hoverClass);break;case"mouseout":f(c,this.options.hoverClass),this.resetBridge();break;case"mousedown":e(c,this.options.activeClass);break;case"mouseup":f(c,this.options.activeClass);break;case"datarequested":var d=c.getAttribute("data-clipboard-target"),g=d?document.getElementById(d):null;if(g){var h=g.value||g.textContent||g.innerText;h&&this.setText(h)}else{var i=c.getAttribute("data-clipboard-text");i&&this.setText(i)}break;case"complete":this.options.text=null}if(this.handlers[a]){var j=this.handlers[a];typeof j=="function"?j.call(c,this,b):typeof j=="string"&&window[j].call(c,this,b)}},l.prototype.glue=function(a){a=k(a);for(var d=0;d<a.length;d++)j(a[d],n)==-1&&(n.push(a[d]),c(a[d],"mouseover",b))},l.prototype.unglue=function(a){a=k(a);for(var c=0;c<a.length;c++){d(a[c],"mouseover",b);var e=j(a[c],n);e!=-1&&n.splice(e,1)}},typeof module!="undefined"?module.exports=l:typeof define=="function"&&define.amd?define(function(){return l}):window.ZeroClipboard=l})();
// moment.js
// version : 2.0.0
// author : Tim Wood
// license : MIT
// momentjs.com
(function(e){function O(e,t){return function(n){return j(e.call(this,n),t)}}function M(e){return function(t){return this.lang().ordinal(e.call(this,t))}}function _(){}function D(e){H(this,e)}function P(e){var t=this._data={},n=e.years||e.year||e.y||0,r=e.months||e.month||e.M||0,i=e.weeks||e.week||e.w||0,s=e.days||e.day||e.d||0,o=e.hours||e.hour||e.h||0,u=e.minutes||e.minute||e.m||0,a=e.seconds||e.second||e.s||0,f=e.milliseconds||e.millisecond||e.ms||0;this._milliseconds=f+a*1e3+u*6e4+o*36e5,this._days=s+i*7,this._months=r+n*12,t.milliseconds=f%1e3,a+=B(f/1e3),t.seconds=a%60,u+=B(a/60),t.minutes=u%60,o+=B(u/60),t.hours=o%24,s+=B(o/24),s+=i*7,t.days=s%30,r+=B(s/30),t.months=r%12,n+=B(r/12),t.years=n}function H(e,t){for(var n in t)t.hasOwnProperty(n)&&(e[n]=t[n]);return e}function B(e){return e<0?Math.ceil(e):Math.floor(e)}function j(e,t){var n=e+"";while(n.length<t)n="0"+n;return n}function F(e,t,n){var r=t._milliseconds,i=t._days,s=t._months,o;r&&e._d.setTime(+e+r*n),i&&e.date(e.date()+i*n),s&&(o=e.date(),e.date(1).month(e.month()+s*n).date(Math.min(o,e.daysInMonth())))}function I(e){return Object.prototype.toString.call(e)==="[object Array]"}function q(e,t){var n=Math.min(e.length,t.length),r=Math.abs(e.length-t.length),i=0,s;for(s=0;s<n;s++)~~e[s]!==~~t[s]&&i++;return i+r}function R(e,t){return t.abbr=e,s[e]||(s[e]=new _),s[e].set(t),s[e]}function U(e){return e?(!s[e]&&o&&require("./lang/"+e),s[e]):t.fn._lang}function z(e){return e.match(/\[.*\]/)?e.replace(/^\[|\]$/g,""):e.replace(/\\/g,"")}function W(e){var t=e.match(a),n,r;for(n=0,r=t.length;n<r;n++)A[t[n]]?t[n]=A[t[n]]:t[n]=z(t[n]);return function(i){var s="";for(n=0;n<r;n++)s+=typeof t[n].call=="function"?t[n].call(i,e):t[n];return s}}function X(e,t){function r(t){return e.lang().longDateFormat(t)||t}var n=5;while(n--&&f.test(t))t=t.replace(f,r);return C[t]||(C[t]=W(t)),C[t](e)}function V(e){switch(e){case"DDDD":return p;case"YYYY":return d;case"YYYYY":return v;case"S":case"SS":case"SSS":case"DDD":return h;case"MMM":case"MMMM":case"dd":case"ddd":case"dddd":case"a":case"A":return m;case"X":return b;case"Z":case"ZZ":return g;case"T":return y;case"MM":case"DD":case"YY":case"HH":case"hh":case"mm":case"ss":case"M":case"D":case"d":case"H":case"h":case"m":case"s":return c;default:return new RegExp(e.replace("\\",""))}}function $(e,t,n){var r,i,s=n._a;switch(e){case"M":case"MM":s[1]=t==null?0:~~t-1;break;case"MMM":case"MMMM":r=U(n._l).monthsParse(t),r!=null?s[1]=r:n._isValid=!1;break;case"D":case"DD":case"DDD":case"DDDD":t!=null&&(s[2]=~~t);break;case"YY":s[0]=~~t+(~~t>68?1900:2e3);break;case"YYYY":case"YYYYY":s[0]=~~t;break;case"a":case"A":n._isPm=(t+"").toLowerCase()==="pm";break;case"H":case"HH":case"h":case"hh":s[3]=~~t;break;case"m":case"mm":s[4]=~~t;break;case"s":case"ss":s[5]=~~t;break;case"S":case"SS":case"SSS":s[6]=~~(("0."+t)*1e3);break;case"X":n._d=new Date(parseFloat(t)*1e3);break;case"Z":case"ZZ":n._useUTC=!0,r=(t+"").match(x),r&&r[1]&&(n._tzh=~~r[1]),r&&r[2]&&(n._tzm=~~r[2]),r&&r[0]==="+"&&(n._tzh=-n._tzh,n._tzm=-n._tzm)}t==null&&(n._isValid=!1)}function J(e){var t,n,r=[];if(e._d)return;for(t=0;t<7;t++)e._a[t]=r[t]=e._a[t]==null?t===2?1:0:e._a[t];r[3]+=e._tzh||0,r[4]+=e._tzm||0,n=new Date(0),e._useUTC?(n.setUTCFullYear(r[0],r[1],r[2]),n.setUTCHours(r[3],r[4],r[5],r[6])):(n.setFullYear(r[0],r[1],r[2]),n.setHours(r[3],r[4],r[5],r[6])),e._d=n}function K(e){var t=e._f.match(a),n=e._i,r,i;e._a=[];for(r=0;r<t.length;r++)i=(V(t[r]).exec(n)||[])[0],i&&(n=n.slice(n.indexOf(i)+i.length)),A[t[r]]&&$(t[r],i,e);e._isPm&&e._a[3]<12&&(e._a[3]+=12),e._isPm===!1&&e._a[3]===12&&(e._a[3]=0),J(e)}function Q(e){var t,n,r,i=99,s,o,u;while(e._f.length){t=H({},e),t._f=e._f.pop(),K(t),n=new D(t);if(n.isValid()){r=n;break}u=q(t._a,n.toArray()),u<i&&(i=u,r=n)}H(e,r)}function G(e){var t,n=e._i;if(w.exec(n)){e._f="YYYY-MM-DDT";for(t=0;t<4;t++)if(S[t][1].exec(n)){e._f+=S[t][0];break}g.exec(n)&&(e._f+=" Z"),K(e)}else e._d=new Date(n)}function Y(t){var n=t._i,r=u.exec(n);n===e?t._d=new Date:r?t._d=new Date(+r[1]):typeof n=="string"?G(t):I(n)?(t._a=n.slice(0),J(t)):t._d=n instanceof Date?new Date(+n):new Date(n)}function Z(e,t,n,r,i){return i.relativeTime(t||1,!!n,e,r)}function et(e,t,n){var i=r(Math.abs(e)/1e3),s=r(i/60),o=r(s/60),u=r(o/24),a=r(u/365),f=i<45&&["s",i]||s===1&&["m"]||s<45&&["mm",s]||o===1&&["h"]||o<22&&["hh",o]||u===1&&["d"]||u<=25&&["dd",u]||u<=45&&["M"]||u<345&&["MM",r(u/30)]||a===1&&["y"]||["yy",a];return f[2]=t,f[3]=e>0,f[4]=n,Z.apply({},f)}function tt(e,n,r){var i=r-n,s=r-e.day();return s>i&&(s-=7),s<i-7&&(s+=7),Math.ceil(t(e).add("d",s).dayOfYear()/7)}function nt(e){var n=e._i,r=e._f;return n===null||n===""?null:(typeof n=="string"&&(e._i=n=U().preparse(n)),t.isMoment(n)?(e=H({},n),e._d=new Date(+n._d)):r?I(r)?Q(e):K(e):Y(e),new D(e))}function rt(e,n){t.fn[e]=t.fn[e+"s"]=function(e){var t=this._isUTC?"UTC":"";return e!=null?(this._d["set"+t+n](e),this):this._d["get"+t+n]()}}function it(e){t.duration.fn[e]=function(){return this._data[e]}}function st(e,n){t.duration.fn["as"+e]=function(){return+this/n}}var t,n="2.0.0",r=Math.round,i,s={},o=typeof module!="undefined"&&module.exports,u=/^\/?Date\((\-?\d+)/i,a=/(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|YYYYY|YYYY|YY|a|A|hh?|HH?|mm?|ss?|SS?S?|X|zz?|ZZ?|.)/g,f=/(\[[^\[]*\])|(\\)?(LT|LL?L?L?|l{1,4})/g,l=/([0-9a-zA-Z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)/gi,c=/\d\d?/,h=/\d{1,3}/,p=/\d{3}/,d=/\d{1,4}/,v=/[+\-]?\d{1,6}/,m=/[0-9]*[a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF]+\s*?[\u0600-\u06FF]+/i,g=/Z|[\+\-]\d\d:?\d\d/i,y=/T/i,b=/[\+\-]?\d+(\.\d{1,3})?/,w=/^\s*\d{4}-\d\d-\d\d((T| )(\d\d(:\d\d(:\d\d(\.\d\d?\d?)?)?)?)?([\+\-]\d\d:?\d\d)?)?/,E="YYYY-MM-DDTHH:mm:ssZ",S=[["HH:mm:ss.S",/(T| )\d\d:\d\d:\d\d\.\d{1,3}/],["HH:mm:ss",/(T| )\d\d:\d\d:\d\d/],["HH:mm",/(T| )\d\d:\d\d/],["HH",/(T| )\d\d/]],x=/([\+\-]|\d\d)/gi,T="Month|Date|Hours|Minutes|Seconds|Milliseconds".split("|"),N={Milliseconds:1,Seconds:1e3,Minutes:6e4,Hours:36e5,Days:864e5,Months:2592e6,Years:31536e6},C={},k="DDD w W M D d".split(" "),L="M D H h m s w W".split(" "),A={M:function(){return this.month()+1},MMM:function(e){return this.lang().monthsShort(this,e)},MMMM:function(e){return this.lang().months(this,e)},D:function(){return this.date()},DDD:function(){return this.dayOfYear()},d:function(){return this.day()},dd:function(e){return this.lang().weekdaysMin(this,e)},ddd:function(e){return this.lang().weekdaysShort(this,e)},dddd:function(e){return this.lang().weekdays(this,e)},w:function(){return this.week()},W:function(){return this.isoWeek()},YY:function(){return j(this.year()%100,2)},YYYY:function(){return j(this.year(),4)},YYYYY:function(){return j(this.year(),5)},a:function(){return this.lang().meridiem(this.hours(),this.minutes(),!0)},A:function(){return this.lang().meridiem(this.hours(),this.minutes(),!1)},H:function(){return this.hours()},h:function(){return this.hours()%12||12},m:function(){return this.minutes()},s:function(){return this.seconds()},S:function(){return~~(this.milliseconds()/100)},SS:function(){return j(~~(this.milliseconds()/10),2)},SSS:function(){return j(this.milliseconds(),3)},Z:function(){var e=-this.zone(),t="+";return e<0&&(e=-e,t="-"),t+j(~~(e/60),2)+":"+j(~~e%60,2)},ZZ:function(){var e=-this.zone(),t="+";return e<0&&(e=-e,t="-"),t+j(~~(10*e/6),4)},X:function(){return this.unix()}};while(k.length)i=k.pop(),A[i+"o"]=M(A[i]);while(L.length)i=L.pop(),A[i+i]=O(A[i],2);A.DDDD=O(A.DDD,3),_.prototype={set:function(e){var t,n;for(n in e)t=e[n],typeof t=="function"?this[n]=t:this["_"+n]=t},_months:"January_February_March_April_May_June_July_August_September_October_November_December".split("_"),months:function(e){return this._months[e.month()]},_monthsShort:"Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),monthsShort:function(e){return this._monthsShort[e.month()]},monthsParse:function(e){var n,r,i,s;this._monthsParse||(this._monthsParse=[]);for(n=0;n<12;n++){this._monthsParse[n]||(r=t([2e3,n]),i="^"+this.months(r,"")+"|^"+this.monthsShort(r,""),this._monthsParse[n]=new RegExp(i.replace(".",""),"i"));if(this._monthsParse[n].test(e))return n}},_weekdays:"Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),weekdays:function(e){return this._weekdays[e.day()]},_weekdaysShort:"Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),weekdaysShort:function(e){return this._weekdaysShort[e.day()]},_weekdaysMin:"Su_Mo_Tu_We_Th_Fr_Sa".split("_"),weekdaysMin:function(e){return this._weekdaysMin[e.day()]},_longDateFormat:{LT:"h:mm A",L:"MM/DD/YYYY",LL:"MMMM D YYYY",LLL:"MMMM D YYYY LT",LLLL:"dddd, MMMM D YYYY LT"},longDateFormat:function(e){var t=this._longDateFormat[e];return!t&&this._longDateFormat[e.toUpperCase()]&&(t=this._longDateFormat[e.toUpperCase()].replace(/MMMM|MM|DD|dddd/g,function(e){return e.slice(1)}),this._longDateFormat[e]=t),t},meridiem:function(e,t,n){return e>11?n?"pm":"PM":n?"am":"AM"},_calendar:{sameDay:"[Today at] LT",nextDay:"[Tomorrow at] LT",nextWeek:"dddd [at] LT",lastDay:"[Yesterday at] LT",lastWeek:"[last] dddd [at] LT",sameElse:"L"},calendar:function(e,t){var n=this._calendar[e];return typeof n=="function"?n.apply(t):n},_relativeTime:{future:"in %s",past:"%s ago",s:"a few seconds",m:"a minute",mm:"%d minutes",h:"an hour",hh:"%d hours",d:"a day",dd:"%d days",M:"a month",MM:"%d months",y:"a year",yy:"%d years"},relativeTime:function(e,t,n,r){var i=this._relativeTime[n];return typeof i=="function"?i(e,t,n,r):i.replace(/%d/i,e)},pastFuture:function(e,t){var n=this._relativeTime[e>0?"future":"past"];return typeof n=="function"?n(t):n.replace(/%s/i,t)},ordinal:function(e){return this._ordinal.replace("%d",e)},_ordinal:"%d",preparse:function(e){return e},postformat:function(e){return e},week:function(e){return tt(e,this._week.dow,this._week.doy)},_week:{dow:0,doy:6}},t=function(e,t,n){return nt({_i:e,_f:t,_l:n,_isUTC:!1})},t.utc=function(e,t,n){return nt({_useUTC:!0,_isUTC:!0,_l:n,_i:e,_f:t})},t.unix=function(e){return t(e*1e3)},t.duration=function(e,n){var r=t.isDuration(e),i=typeof e=="number",s=r?e._data:i?{}:e,o;return i&&(n?s[n]=e:s.milliseconds=e),o=new P(s),r&&e.hasOwnProperty("_lang")&&(o._lang=e._lang),o},t.version=n,t.defaultFormat=E,t.lang=function(e,n){var r;if(!e)return t.fn._lang._abbr;n?R(e,n):s[e]||U(e),t.duration.fn._lang=t.fn._lang=U(e)},t.langData=function(e){return e&&e._lang&&e._lang._abbr&&(e=e._lang._abbr),U(e)},t.isMoment=function(e){return e instanceof D},t.isDuration=function(e){return e instanceof P},t.fn=D.prototype={clone:function(){return t(this)},valueOf:function(){return+this._d},unix:function(){return Math.floor(+this._d/1e3)},toString:function(){return this.format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ")},toDate:function(){return this._d},toJSON:function(){return t.utc(this).format("YYYY-MM-DD[T]HH:mm:ss.SSS[Z]")},toArray:function(){var e=this;return[e.year(),e.month(),e.date(),e.hours(),e.minutes(),e.seconds(),e.milliseconds()]},isValid:function(){return this._isValid==null&&(this._a?this._isValid=!q(this._a,(this._isUTC?t.utc(this._a):t(this._a)).toArray()):this._isValid=!isNaN(this._d.getTime())),!!this._isValid},utc:function(){return this._isUTC=!0,this},local:function(){return this._isUTC=!1,this},format:function(e){var n=X(this,e||t.defaultFormat);return this.lang().postformat(n)},add:function(e,n){var r;return typeof e=="string"?r=t.duration(+n,e):r=t.duration(e,n),F(this,r,1),this},subtract:function(e,n){var r;return typeof e=="string"?r=t.duration(+n,e):r=t.duration(e,n),F(this,r,-1),this},diff:function(e,n,r){var i=this._isUTC?t(e).utc():t(e).local(),s=(this.zone()-i.zone())*6e4,o,u;return n&&(n=n.replace(/s$/,"")),n==="year"||n==="month"?(o=(this.daysInMonth()+i.daysInMonth())*432e5,u=(this.year()-i.year())*12+(this.month()-i.month()),u+=(this-t(this).startOf("month")-(i-t(i).startOf("month")))/o,n==="year"&&(u/=12)):(o=this-i-s,u=n==="second"?o/1e3:n==="minute"?o/6e4:n==="hour"?o/36e5:n==="day"?o/864e5:n==="week"?o/6048e5:o),r?u:B(u)},from:function(e,n){return t.duration(this.diff(e)).lang(this.lang()._abbr).humanize(!n)},fromNow:function(e){return this.from(t(),e)},calendar:function(){var e=this.diff(t().startOf("day"),"days",!0),n=e<-6?"sameElse":e<-1?"lastWeek":e<0?"lastDay":e<1?"sameDay":e<2?"nextDay":e<7?"nextWeek":"sameElse";return this.format(this.lang().calendar(n,this))},isLeapYear:function(){var e=this.year();return e%4===0&&e%100!==0||e%400===0},isDST:function(){return this.zone()<t([this.year()]).zone()||this.zone()<t([this.year(),5]).zone()},day:function(e){var t=this._isUTC?this._d.getUTCDay():this._d.getDay();return e==null?t:this.add({d:e-t})},startOf:function(e){e=e.replace(/s$/,"");switch(e){case"year":this.month(0);case"month":this.date(1);case"week":case"day":this.hours(0);case"hour":this.minutes(0);case"minute":this.seconds(0);case"second":this.milliseconds(0)}return e==="week"&&this.day(0),this},endOf:function(e){return this.startOf(e).add(e.replace(/s?$/,"s"),1).subtract("ms",1)},isAfter:function(e,n){return n=typeof n!="undefined"?n:"millisecond",+this.clone().startOf(n)>+t(e).startOf(n)},isBefore:function(e,n){return n=typeof n!="undefined"?n:"millisecond",+this.clone().startOf(n)<+t(e).startOf(n)},isSame:function(e,n){return n=typeof n!="undefined"?n:"millisecond",+this.clone().startOf(n)===+t(e).startOf(n)},zone:function(){return this._isUTC?0:this._d.getTimezoneOffset()},daysInMonth:function(){return t.utc([this.year(),this.month()+1,0]).date()},dayOfYear:function(e){var n=r((t(this).startOf("day")-t(this).startOf("year"))/864e5)+1;return e==null?n:this.add("d",e-n)},isoWeek:function(e){var t=tt(this,1,4);return e==null?t:this.add("d",(e-t)*7)},week:function(e){var t=this.lang().week(this);return e==null?t:this.add("d",(e-t)*7)},lang:function(t){return t===e?this._lang:(this._lang=U(t),this)}};for(i=0;i<T.length;i++)rt(T[i].toLowerCase().replace(/s$/,""),T[i]);rt("year","FullYear"),t.fn.days=t.fn.day,t.fn.weeks=t.fn.week,t.fn.isoWeeks=t.fn.isoWeek,t.duration.fn=P.prototype={weeks:function(){return B(this.days()/7)},valueOf:function(){return this._milliseconds+this._days*864e5+this._months*2592e6},humanize:function(e){var t=+this,n=et(t,!e,this.lang());return e&&(n=this.lang().pastFuture(t,n)),this.lang().postformat(n)},lang:t.fn.lang};for(i in N)N.hasOwnProperty(i)&&(st(i,N[i]),it(i.toLowerCase()));st("Weeks",6048e5),t.lang("en",{ordinal:function(e){var t=e%10,n=~~(e%100/10)===1?"th":t===1?"st":t===2?"nd":t===3?"rd":"th";return e+n}}),o&&(module.exports=t),typeof ender=="undefined"&&(this.moment=t),typeof define=="function"&&define.amd&&define("moment",[],function(){return t})}).call(this);
// Livestamp.js / v1.1.2 / (c) 2012 Matt Bradley / MIT License
(function(d,g){var h=1E3,i=!1,e=d([]),j=function(b,a){var c=b.data("livestampdata");"number"==typeof a&&(a*=1E3);b.removeAttr("data-livestamp").removeData("livestamp");a=g(a);g.isMoment(a)&&!isNaN(+a)&&(c=d.extend({},{original:b.contents()},c),c.moment=g(a),b.data("livestampdata",c).empty(),e.push(b[0]))},k=function(){i||(f.update(),setTimeout(k,h))},f={update:function(){d("[data-livestamp]").each(function(){var a=d(this);j(a,a.data("livestamp"))});var b=[];e.each(function(){var a=d(this),c=a.data("livestampdata");
  if(void 0===c)b.push(this);else if(g.isMoment(c.moment)){var e=a.html(),c=c.moment.fromNow();if(e!=c){var f=d.Event("change.livestamp");a.trigger(f,[e,c]);f.isDefaultPrevented()||a.html(c)}}});e=e.not(b)},pause:function(){i=!0},resume:function(){i=!1;k()},interval:function(b){if(void 0===b)return h;h=b}},l={add:function(b,a){"number"==typeof a&&(a*=1E3);a=g(a);g.isMoment(a)&&!isNaN(+a)&&(b.each(function(){j(d(this),a)}),f.update());return b},destroy:function(b){e=e.not(b);b.each(function(){var a=
  d(this),c=a.data("livestampdata");if(void 0===c)return b;a.html(c.original?c.original:"").removeData("livestampdata")});return b},isLivestamp:function(b){return void 0!==b.data("livestampdata")}};d.livestamp=f;d(function(){f.resume()});d.fn.livestamp=function(b,a){l[b]||(a=b,b="add");return l[b](this,a)}})(jQuery,moment);

(function(N,d,p,K,k,H){var b=this;var n=Math.floor(Math.random()*10000);var q=Function.prototype;var Q=/^((http.?:)\/\/([^:\/\s]+)(:\d+)*)/;var R=/[\-\w]+\/\.\.\//;var F=/([^:])\/\//g;var I="";var o={};var M=N.easyXDM;var U="easyXDM_";var E;var y=false;var i;var h;function C(X,Z){var Y=typeof X[Z];return Y=="function"||(!!(Y=="object"&&X[Z]))||Y=="unknown"}function u(X,Y){return !!(typeof(X[Y])=="object"&&X[Y])}function r(X){return Object.prototype.toString.call(X)==="[object Array]"}function c(){var Z="Shockwave Flash",ad="application/x-shockwave-flash";if(!t(navigator.plugins)&&typeof navigator.plugins[Z]=="object"){var ab=navigator.plugins[Z].description;if(ab&&!t(navigator.mimeTypes)&&navigator.mimeTypes[ad]&&navigator.mimeTypes[ad].enabledPlugin){i=ab.match(/\d+/g)}}if(!i){var Y;try{Y=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");i=Array.prototype.slice.call(Y.GetVariable("$version").match(/(\d+),(\d+),(\d+),(\d+)/),1);Y=null}catch(ac){}}if(!i){return false}var X=parseInt(i[0],10),aa=parseInt(i[1],10);h=X>9&&aa>0;return true}var v,x;if(C(N,"addEventListener")){v=function(Z,X,Y){Z.addEventListener(X,Y,false)};x=function(Z,X,Y){Z.removeEventListener(X,Y,false)}}else{if(C(N,"attachEvent")){v=function(X,Z,Y){X.attachEvent("on"+Z,Y)};x=function(X,Z,Y){X.detachEvent("on"+Z,Y)}}else{throw new Error("Browser not supported")}}var W=false,J=[],L;if("readyState" in d){L=d.readyState;W=L=="complete"||(~navigator.userAgent.indexOf("AppleWebKit/")&&(L=="loaded"||L=="interactive"))}else{W=!!d.body}function s(){if(W){return}W=true;for(var X=0;X<J.length;X++){J[X]()}J.length=0}if(!W){if(C(N,"addEventListener")){v(d,"DOMContentLoaded",s)}else{v(d,"readystatechange",function(){if(d.readyState=="complete"){s()}});if(d.documentElement.doScroll&&N===top){var g=function(){if(W){return}try{d.documentElement.doScroll("left")}catch(X){K(g,1);return}s()};g()}}v(N,"load",s)}function G(Y,X){if(W){Y.call(X);return}J.push(function(){Y.call(X)})}function m(){var Z=parent;if(I!==""){for(var X=0,Y=I.split(".");X<Y.length;X++){Z=Z[Y[X]]}}return Z.easyXDM}function e(X){N.easyXDM=M;I=X;if(I){U="easyXDM_"+I.replace(".","_")+"_"}return o}function z(X){return X.match(Q)[3]}function f(X){return X.match(Q)[4]||""}function j(Z){var X=Z.toLowerCase().match(Q);var aa=X[2],ab=X[3],Y=X[4]||"";if((aa=="http:"&&Y==":80")||(aa=="https:"&&Y==":443")){Y=""}return aa+"//"+ab+Y}function B(X){X=X.replace(F,"$1/");if(!X.match(/^(http||https):\/\//)){var Y=(X.substring(0,1)==="/")?"":p.pathname;if(Y.substring(Y.length-1)!=="/"){Y=Y.substring(0,Y.lastIndexOf("/")+1)}X=p.protocol+"//"+p.host+Y+X}while(R.test(X)){X=X.replace(R,"")}return X}function P(X,aa){var ac="",Z=X.indexOf("#");if(Z!==-1){ac=X.substring(Z);X=X.substring(0,Z)}var ab=[];for(var Y in aa){if(aa.hasOwnProperty(Y)){ab.push(Y+"="+H(aa[Y]))}}return X+(y?"#":(X.indexOf("?")==-1?"?":"&"))+ab.join("&")+ac}var S=(function(X){X=X.substring(1).split("&");var Z={},aa,Y=X.length;while(Y--){aa=X[Y].split("=");Z[aa[0]]=k(aa[1])}return Z}(/xdm_e=/.test(p.search)?p.search:p.hash));function t(X){return typeof X==="undefined"}var O=function(){var Y={};var Z={a:[1,2,3]},X='{"a":[1,2,3]}';if(typeof JSON!="undefined"&&typeof JSON.stringify==="function"&&JSON.stringify(Z).replace((/\s/g),"")===X){return JSON}if(Object.toJSON){if(Object.toJSON(Z).replace((/\s/g),"")===X){Y.stringify=Object.toJSON}}if(typeof String.prototype.evalJSON==="function"){Z=X.evalJSON();if(Z.a&&Z.a.length===3&&Z.a[2]===3){Y.parse=function(aa){return aa.evalJSON()}}}if(Y.stringify&&Y.parse){O=function(){return Y};return Y}return null};function T(X,Y,Z){var ab;for(var aa in Y){if(Y.hasOwnProperty(aa)){if(aa in X){ab=Y[aa];if(typeof ab==="object"){T(X[aa],ab,Z)}else{if(!Z){X[aa]=Y[aa]}}}else{X[aa]=Y[aa]}}}return X}function a(){var Y=d.body.appendChild(d.createElement("form")),X=Y.appendChild(d.createElement("input"));X.name=U+"TEST"+n;E=X!==Y.elements[X.name];d.body.removeChild(Y)}function A(Y){if(t(E)){a()}var ac;if(E){ac=d.createElement('<iframe name="'+Y.props.name+'"/>')}else{ac=d.createElement("IFRAME");ac.name=Y.props.name}ac.id=ac.name=Y.props.name;delete Y.props.name;if(typeof Y.container=="string"){Y.container=d.getElementById(Y.container)}if(!Y.container){T(ac.style,{position:"absolute",top:"-2000px",left:"0px"});Y.container=d.body}var ab=Y.props.src;Y.props.src="javascript:false";T(ac,Y.props);ac.border=ac.frameBorder=0;ac.allowTransparency=true;Y.container.appendChild(ac);if(Y.onLoad){v(ac,"load",Y.onLoad)}if(Y.usePost){var aa=Y.container.appendChild(d.createElement("form")),X;aa.target=ac.name;aa.action=ab;aa.method="POST";if(typeof(Y.usePost)==="object"){for(var Z in Y.usePost){if(Y.usePost.hasOwnProperty(Z)){if(E){X=d.createElement('<input name="'+Z+'"/>')}else{X=d.createElement("INPUT");X.name=Z}X.value=Y.usePost[Z];aa.appendChild(X)}}}aa.submit();aa.parentNode.removeChild(aa)}else{ac.src=ab}Y.props.src=ab;return ac}function V(aa,Z){if(typeof aa=="string"){aa=[aa]}var Y,X=aa.length;while(X--){Y=aa[X];Y=new RegExp(Y.substr(0,1)=="^"?Y:("^"+Y.replace(/(\*)/g,".$1").replace(/\?/g,".")+"$"));if(Y.test(Z)){return true}}return false}function l(Z){var ae=Z.protocol,Y;Z.isHost=Z.isHost||t(S.xdm_p);y=Z.hash||false;if(!Z.props){Z.props={}}if(!Z.isHost){Z.channel=S.xdm_c.replace(/["'<>\\]/g,"");Z.secret=S.xdm_s;Z.remote=S.xdm_e.replace(/["'<>\\]/g,"");ae=S.xdm_p;if(Z.acl&&!V(Z.acl,Z.remote)){throw new Error("Access denied for "+Z.remote)}}else{Z.remote=B(Z.remote);Z.channel=Z.channel||"default"+n++;Z.secret=Math.random().toString(16).substring(2);if(t(ae)){if(j(p.href)==j(Z.remote)){ae="4"}else{if(C(N,"postMessage")||C(d,"postMessage")){ae="1"}else{if(Z.swf&&C(N,"ActiveXObject")&&c()){ae="6"}else{if(navigator.product==="Gecko"&&"frameElement" in N&&navigator.userAgent.indexOf("WebKit")==-1){ae="5"}else{if(Z.remoteHelper){ae="2"}else{ae="0"}}}}}}}Z.protocol=ae;switch(ae){case"0":T(Z,{interval:100,delay:2000,useResize:true,useParent:false,usePolling:false},true);if(Z.isHost){if(!Z.local){var ac=p.protocol+"//"+p.host,X=d.body.getElementsByTagName("img"),ad;var aa=X.length;while(aa--){ad=X[aa];if(ad.src.substring(0,ac.length)===ac){Z.local=ad.src;break}}if(!Z.local){Z.local=N}}var ab={xdm_c:Z.channel,xdm_p:0};if(Z.local===N){Z.usePolling=true;Z.useParent=true;Z.local=p.protocol+"//"+p.host+p.pathname+p.search;ab.xdm_e=Z.local;ab.xdm_pa=1}else{ab.xdm_e=B(Z.local)}if(Z.container){Z.useResize=false;ab.xdm_po=1}Z.remote=P(Z.remote,ab)}else{T(Z,{channel:S.xdm_c,remote:S.xdm_e,useParent:!t(S.xdm_pa),usePolling:!t(S.xdm_po),useResize:Z.useParent?false:Z.useResize})}Y=[new o.stack.HashTransport(Z),new o.stack.ReliableBehavior({}),new o.stack.QueueBehavior({encode:true,maxLength:4000-Z.remote.length}),new o.stack.VerifyBehavior({initiate:Z.isHost})];break;case"1":Y=[new o.stack.PostMessageTransport(Z)];break;case"2":Z.remoteHelper=B(Z.remoteHelper);Y=[new o.stack.NameTransport(Z),new o.stack.QueueBehavior(),new o.stack.VerifyBehavior({initiate:Z.isHost})];break;case"3":Y=[new o.stack.NixTransport(Z)];break;case"4":Y=[new o.stack.SameOriginTransport(Z)];break;case"5":Y=[new o.stack.FrameElementTransport(Z)];break;case"6":if(!i){c()}Y=[new o.stack.FlashTransport(Z)];break}Y.push(new o.stack.QueueBehavior({lazy:Z.lazy,remove:true}));return Y}function D(aa){var ab,Z={incoming:function(ad,ac){this.up.incoming(ad,ac)},outgoing:function(ac,ad){this.down.outgoing(ac,ad)},callback:function(ac){this.up.callback(ac)},init:function(){this.down.init()},destroy:function(){this.down.destroy()}};for(var Y=0,X=aa.length;Y<X;Y++){ab=aa[Y];T(ab,Z,true);if(Y!==0){ab.down=aa[Y-1]}if(Y!==X-1){ab.up=aa[Y+1]}}return ab}function w(X){X.up.down=X.down;X.down.up=X.up;X.up=X.down=null}T(o,{version:"2.4.17.1",query:S,stack:{},apply:T,getJSONObject:O,whenReady:G,noConflict:e});o.DomHelper={on:v,un:x,requiresJSON:function(X){if(!u(N,"JSON")){d.write('<script type="text/javascript" src="'+X+'"><\/script>')}}};(function(){var X={};o.Fn={set:function(Y,Z){X[Y]=Z},get:function(Z,Y){var aa=X[Z];if(Y){delete X[Z]}return aa}}}());o.Socket=function(Y){var X=D(l(Y).concat([{incoming:function(ab,aa){Y.onMessage(ab,aa)},callback:function(aa){if(Y.onReady){Y.onReady(aa)}}}])),Z=j(Y.remote);this.origin=j(Y.remote);this.destroy=function(){X.destroy()};this.postMessage=function(aa){X.outgoing(aa,Z)};X.init()};o.Rpc=function(Z,Y){if(Y.local){for(var ab in Y.local){if(Y.local.hasOwnProperty(ab)){var aa=Y.local[ab];if(typeof aa==="function"){Y.local[ab]={method:aa}}}}}var X=D(l(Z).concat([new o.stack.RpcBehavior(this,Y),{callback:function(ac){if(Z.onReady){Z.onReady(ac)}}}]));this.origin=j(Z.remote);this.destroy=function(){X.destroy()};X.init()};o.stack.SameOriginTransport=function(Y){var Z,ab,aa,X;return(Z={outgoing:function(ad,ae,ac){aa(ad);if(ac){ac()}},destroy:function(){if(ab){ab.parentNode.removeChild(ab);ab=null}},onDOMReady:function(){X=j(Y.remote);if(Y.isHost){T(Y.props,{src:P(Y.remote,{xdm_e:p.protocol+"//"+p.host+p.pathname,xdm_c:Y.channel,xdm_p:4}),name:U+Y.channel+"_provider"});ab=A(Y);o.Fn.set(Y.channel,function(ac){aa=ac;K(function(){Z.up.callback(true)},0);return function(ad){Z.up.incoming(ad,X)}})}else{aa=m().Fn.get(Y.channel,true)(function(ac){Z.up.incoming(ac,X)});K(function(){Z.up.callback(true)},0)}},init:function(){G(Z.onDOMReady,Z)}})};o.stack.FlashTransport=function(aa){var ac,X,ab,ad,Y,ae;function af(ah,ag){K(function(){ac.up.incoming(ah,ad)},0)}function Z(ah){var ag=aa.swf+"?host="+aa.isHost;var aj="easyXDM_swf_"+Math.floor(Math.random()*10000);o.Fn.set("flash_loaded"+ah.replace(/[\-.]/g,"_"),function(){o.stack.FlashTransport[ah].swf=Y=ae.firstChild;var ak=o.stack.FlashTransport[ah].queue;for(var al=0;al<ak.length;al++){ak[al]()}ak.length=0});if(aa.swfContainer){ae=(typeof aa.swfContainer=="string")?d.getElementById(aa.swfContainer):aa.swfContainer}else{ae=d.createElement("div");T(ae.style,h&&aa.swfNoThrottle?{height:"20px",width:"20px",position:"fixed",right:0,top:0}:{height:"1px",width:"1px",position:"absolute",overflow:"hidden",right:0,top:0});d.body.appendChild(ae)}var ai="callback=flash_loaded"+ah.replace(/[\-.]/g,"_")+"&proto="+b.location.protocol+"&domain="+z(b.location.href)+"&port="+f(b.location.href)+"&ns="+I;ae.innerHTML="<object height='20' width='20' type='application/x-shockwave-flash' id='"+aj+"' data='"+ag+"'><param name='allowScriptAccess' value='always'></param><param name='wmode' value='transparent'><param name='movie' value='"+ag+"'></param><param name='flashvars' value='"+ai+"'></param><embed type='application/x-shockwave-flash' FlashVars='"+ai+"' allowScriptAccess='always' wmode='transparent' src='"+ag+"' height='1' width='1'></embed></object>"}return(ac={outgoing:function(ah,ai,ag){Y.postMessage(aa.channel,ah.toString());if(ag){ag()}},destroy:function(){try{Y.destroyChannel(aa.channel)}catch(ag){}Y=null;if(X){X.parentNode.removeChild(X);X=null}},onDOMReady:function(){ad=aa.remote;o.Fn.set("flash_"+aa.channel+"_init",function(){K(function(){ac.up.callback(true)})});o.Fn.set("flash_"+aa.channel+"_onMessage",af);aa.swf=B(aa.swf);var ah=z(aa.swf);var ag=function(){o.stack.FlashTransport[ah].init=true;Y=o.stack.FlashTransport[ah].swf;Y.createChannel(aa.channel,aa.secret,j(aa.remote),aa.isHost);if(aa.isHost){if(h&&aa.swfNoThrottle){T(aa.props,{position:"fixed",right:0,top:0,height:"20px",width:"20px"})}T(aa.props,{src:P(aa.remote,{xdm_e:j(p.href),xdm_c:aa.channel,xdm_p:6,xdm_s:aa.secret}),name:U+aa.channel+"_provider"});X=A(aa)}};if(o.stack.FlashTransport[ah]&&o.stack.FlashTransport[ah].init){ag()}else{if(!o.stack.FlashTransport[ah]){o.stack.FlashTransport[ah]={queue:[ag]};Z(ah)}else{o.stack.FlashTransport[ah].queue.push(ag)}}},init:function(){G(ac.onDOMReady,ac)}})};o.stack.PostMessageTransport=function(aa){var ac,ad,Y,Z;function X(ae){if(ae.origin){return j(ae.origin)}if(ae.uri){return j(ae.uri)}if(ae.domain){return p.protocol+"//"+ae.domain}throw"Unable to retrieve the origin of the event"}function ab(af){var ae=X(af);if(ae==Z&&af.data.substring(0,aa.channel.length+1)==aa.channel+" "){ac.up.incoming(af.data.substring(aa.channel.length+1),ae)}}return(ac={outgoing:function(af,ag,ae){Y.postMessage(aa.channel+" "+af,ag||Z);if(ae){ae()}},destroy:function(){x(N,"message",ab);if(ad){Y=null;ad.parentNode.removeChild(ad);ad=null}},onDOMReady:function(){Z=j(aa.remote);if(aa.isHost){var ae=function(af){if(af.data==aa.channel+"-ready"){Y=("postMessage" in ad.contentWindow)?ad.contentWindow:ad.contentWindow.document;x(N,"message",ae);v(N,"message",ab);K(function(){ac.up.callback(true)},0)}};v(N,"message",ae);T(aa.props,{src:P(aa.remote,{xdm_e:j(p.href),xdm_c:aa.channel,xdm_p:1}),name:U+aa.channel+"_provider"});ad=A(aa)}else{v(N,"message",ab);Y=("postMessage" in N.parent)?N.parent:N.parent.document;Y.postMessage(aa.channel+"-ready",Z);K(function(){ac.up.callback(true)},0)}},init:function(){G(ac.onDOMReady,ac)}})};o.stack.FrameElementTransport=function(Y){var Z,ab,aa,X;return(Z={outgoing:function(ad,ae,ac){aa.call(this,ad);if(ac){ac()}},destroy:function(){if(ab){ab.parentNode.removeChild(ab);ab=null}},onDOMReady:function(){X=j(Y.remote);if(Y.isHost){T(Y.props,{src:P(Y.remote,{xdm_e:j(p.href),xdm_c:Y.channel,xdm_p:5}),name:U+Y.channel+"_provider"});ab=A(Y);ab.fn=function(ac){delete ab.fn;aa=ac;K(function(){Z.up.callback(true)},0);return function(ad){Z.up.incoming(ad,X)}}}else{if(d.referrer&&j(d.referrer)!=S.xdm_e){N.top.location=S.xdm_e}aa=N.frameElement.fn(function(ac){Z.up.incoming(ac,X)});Z.up.callback(true)}},init:function(){G(Z.onDOMReady,Z)}})};o.stack.NameTransport=function(ab){var ac;var ae,ai,aa,ag,ah,Y,X;function af(al){var ak=ab.remoteHelper+(ae?"#_3":"#_2")+ab.channel;ai.contentWindow.sendMessage(al,ak)}function ad(){if(ae){if(++ag===2||!ae){ac.up.callback(true)}}else{af("ready");ac.up.callback(true)}}function aj(ak){ac.up.incoming(ak,Y)}function Z(){if(ah){K(function(){ah(true)},0)}}return(ac={outgoing:function(al,am,ak){ah=ak;af(al)},destroy:function(){ai.parentNode.removeChild(ai);ai=null;if(ae){aa.parentNode.removeChild(aa);aa=null}},onDOMReady:function(){ae=ab.isHost;ag=0;Y=j(ab.remote);ab.local=B(ab.local);if(ae){o.Fn.set(ab.channel,function(al){if(ae&&al==="ready"){o.Fn.set(ab.channel,aj);ad()}});X=P(ab.remote,{xdm_e:ab.local,xdm_c:ab.channel,xdm_p:2});T(ab.props,{src:X+"#"+ab.channel,name:U+ab.channel+"_provider"});aa=A(ab)}else{ab.remoteHelper=ab.remote;o.Fn.set(ab.channel,aj)}var ak=function(){var al=ai||this;x(al,"load",ak);o.Fn.set(ab.channel+"_load",Z);(function am(){if(typeof al.contentWindow.sendMessage=="function"){ad()}else{K(am,50)}}())};ai=A({props:{src:ab.local+"#_4"+ab.channel},onLoad:ak})},init:function(){G(ac.onDOMReady,ac)}})};o.stack.HashTransport=function(Z){var ac;var ah=this,af,aa,X,ad,am,ab,al;var ag,Y;function ak(ao){if(!al){return}var an=Z.remote+"#"+(am++)+"_"+ao;((af||!ag)?al.contentWindow:al).location=an}function ae(an){ad=an;ac.up.incoming(ad.substring(ad.indexOf("_")+1),Y)}function aj(){if(!ab){return}var an=ab.location.href,ap="",ao=an.indexOf("#");if(ao!=-1){ap=an.substring(ao)}if(ap&&ap!=ad){ae(ap)}}function ai(){aa=setInterval(aj,X)}return(ac={outgoing:function(an,ao){ak(an)},destroy:function(){N.clearInterval(aa);if(af||!ag){al.parentNode.removeChild(al)}al=null},onDOMReady:function(){af=Z.isHost;X=Z.interval;ad="#"+Z.channel;am=0;ag=Z.useParent;Y=j(Z.remote);if(af){T(Z.props,{src:Z.remote,name:U+Z.channel+"_provider"});if(ag){Z.onLoad=function(){ab=N;ai();ac.up.callback(true)}}else{var ap=0,an=Z.delay/50;(function ao(){if(++ap>an){throw new Error("Unable to reference listenerwindow")}try{ab=al.contentWindow.frames[U+Z.channel+"_consumer"]}catch(aq){}if(ab){ai();ac.up.callback(true)}else{K(ao,50)}}())}al=A(Z)}else{ab=N;ai();if(ag){al=parent;ac.up.callback(true)}else{T(Z,{props:{src:Z.remote+"#"+Z.channel+new Date(),name:U+Z.channel+"_consumer"},onLoad:function(){ac.up.callback(true)}});al=A(Z)}}},init:function(){G(ac.onDOMReady,ac)}})};o.stack.ReliableBehavior=function(Y){var aa,ac;var ab=0,X=0,Z="";return(aa={incoming:function(af,ad){var ae=af.indexOf("_"),ag=af.substring(0,ae).split(",");af=af.substring(ae+1);if(ag[0]==ab){Z="";if(ac){ac(true);ac=null}}if(af.length>0){aa.down.outgoing(ag[1]+","+ab+"_"+Z,ad);if(X!=ag[1]){X=ag[1];aa.up.incoming(af,ad)}}},outgoing:function(af,ad,ae){Z=af;ac=ae;aa.down.outgoing(X+","+(++ab)+"_"+af,ad)}})};o.stack.QueueBehavior=function(Z){var ac,ad=[],ag=true,aa="",af,X=0,Y=false,ab=false;function ae(){if(Z.remove&&ad.length===0){w(ac);return}if(ag||ad.length===0||af){return}ag=true;var ah=ad.shift();ac.down.outgoing(ah.data,ah.origin,function(ai){ag=false;if(ah.callback){K(function(){ah.callback(ai)},0)}ae()})}return(ac={init:function(){if(t(Z)){Z={}}if(Z.maxLength){X=Z.maxLength;ab=true}if(Z.lazy){Y=true}else{ac.down.init()}},callback:function(ai){ag=false;var ah=ac.up;ae();ah.callback(ai)},incoming:function(ak,ai){if(ab){var aj=ak.indexOf("_"),ah=parseInt(ak.substring(0,aj),10);aa+=ak.substring(aj+1);if(ah===0){if(Z.encode){aa=k(aa)}ac.up.incoming(aa,ai);aa=""}}else{ac.up.incoming(ak,ai)}},outgoing:function(al,ai,ak){if(Z.encode){al=H(al)}var ah=[],aj;if(ab){while(al.length!==0){aj=al.substring(0,X);al=al.substring(aj.length);ah.push(aj)}while((aj=ah.shift())){ad.push({data:ah.length+"_"+aj,origin:ai,callback:ah.length===0?ak:null})}}else{ad.push({data:al,origin:ai,callback:ak})}if(Y){ac.down.init()}else{ae()}},destroy:function(){af=true;ac.down.destroy()}})};o.stack.VerifyBehavior=function(ab){var ac,aa,Y,Z=false;function X(){aa=Math.random().toString(16).substring(2);ac.down.outgoing(aa)}return(ac={incoming:function(af,ad){var ae=af.indexOf("_");if(ae===-1){if(af===aa){ac.up.callback(true)}else{if(!Y){Y=af;if(!ab.initiate){X()}ac.down.outgoing(af)}}}else{if(af.substring(0,ae)===Y){ac.up.incoming(af.substring(ae+1),ad)}}},outgoing:function(af,ad,ae){ac.down.outgoing(aa+"_"+af,ad,ae)},callback:function(ad){if(ab.initiate){X()}}})};o.stack.RpcBehavior=function(ad,Y){var aa,af=Y.serializer||O();var ae=0,ac={};function X(ag){ag.jsonrpc="2.0";aa.down.outgoing(af.stringify(ag))}function ab(ag,ai){var ah=Array.prototype.slice;return function(){var aj=arguments.length,al,ak={method:ai};if(aj>0&&typeof arguments[aj-1]==="function"){if(aj>1&&typeof arguments[aj-2]==="function"){al={success:arguments[aj-2],error:arguments[aj-1]};ak.params=ah.call(arguments,0,aj-2)}else{al={success:arguments[aj-1]};ak.params=ah.call(arguments,0,aj-1)}ac[""+(++ae)]=al;ak.id=ae}else{ak.params=ah.call(arguments,0)}if(ag.namedParams&&ak.params.length===1){ak.params=ak.params[0]}X(ak)}}function Z(an,am,ai,al){if(!ai){if(am){X({id:am,error:{code:-32601,message:"Procedure not found."}})}return}var ak,ah;if(am){ak=function(ao){ak=q;X({id:am,result:ao})};ah=function(ao,ap){ah=q;var aq={id:am,error:{code:-32099,message:ao}};if(ap){aq.error.data=ap}X(aq)}}else{ak=ah=q}if(!r(al)){al=[al]}try{var ag=ai.method.apply(ai.scope,al.concat([ak,ah]));if(!t(ag)){ak(ag)}}catch(aj){ah(aj.message)}}return(aa={incoming:function(ah,ag){var ai=af.parse(ah);if(ai.method){if(Y.handle){Y.handle(ai,X)}else{Z(ai.method,ai.id,Y.local[ai.method],ai.params)}}else{var aj=ac[ai.id];if(ai.error){if(aj.error){aj.error(ai.error)}}else{if(aj.success){aj.success(ai.result)}}delete ac[ai.id]}},init:function(){if(Y.remote){for(var ag in Y.remote){if(Y.remote.hasOwnProperty(ag)){ad[ag]=ab(Y.remote[ag],ag)}}}aa.down.init()},destroy:function(){for(var ag in Y.remote){if(Y.remote.hasOwnProperty(ag)&&ad.hasOwnProperty(ag)){delete ad[ag]}}aa.down.destroy()}})};b.easyXDM=o})(window,document,location,window.setTimeout,decodeURIComponent,encodeURIComponent);
function timestamp_to_mmss(timestamp){
    var temp = Math.round(timestamp/6000*100)/100;
    var minutes = Math.floor(temp);
    var aft_virg = temp - minutes;
    var second = Math.round(aft_virg*60);

    if (minutes < 10) {
        minutes = '0' + minutes;
    }
    if(second< 10){
        second = '0'+second;
    }
    return minutes+':'+second;
}

/**
 * Convert seconds to mm:ss string format.
 *
 * @param {secs} secconds 
 * @return {s} formatted mm:ss string
 */

function secondstotime(secs)
{
    var t = new Date(1970,0,1);
    t.setSeconds(secs);
    var s = t.toTimeString().substr(0,8);
    if(secs > 86399)
    	s = Math.floor((t - Date.parse("1/1/70")) / 3600000) + s.substr(2);
    return s;
}



/**
 * Convert timestamp to seconds.
 *
 * @param {timestamp} timestamp to convert
 * @return {integer} rounded number of seconds corresponding to the input timestamp
 */
function timestamp_to_seconds(timestamp){
    var temp = Math.round(timestamp/6000*100)/100;
	temp = Math.round(temp*60);
    return temp;
}


/**
 * Generate basic auth from username+password
 * @param {String} user name
 * @param {String} password
 * @return {String} basic auth string
 */
function make_base_auth(user, password) {
    var tok = user + ':' + password;
    var hash = btoa(tok);
    return "Basic " + hash;
}

// TODO : add comments.
// TODO : How is this function different from timestamp_to_mmss?
function time_tommss(timestamp){
	timestamp=parseInt(timestamp);
        var mins = ~~(timestamp / 60);
	var secs = timestamp % 60;
	
	if (mins>59) { hours = Math.floor(mins/60);
		dif = mins-(hours*60);
		if (hours<10) { hours='0'+hours; }
		if (dif<10) { dif='0'+dif; }
		if (secs<10) { secs='0'+secs; }
		return hours+':'+dif+':'+secs;
	 } else {
		if (mins<10) { mins='0'+mins; }
		if (secs<10) { secs='0'+secs; }
		return mins+':'+secs;
	 }
}

/**
 * Get the URL parameters based on some global variables
 * @param {String} criteria for the query (optional)
 * @returns {String} encoded query for a GET request
 */
function urlencodeQuery(criteria) {
    var query = '';
    var sep = '?';

    if(criteria.start != null) {
        if (criteria.page) {
            criteria.start = criteria.count * criteria.page - criteria.count;
        }
        query += sep + 'start=' + criteria.start;
        sep = '&';
    }
    if(criteria.count != null) {
        query += sep + 'count=' + criteria.count;
        sep = '&';
    }
    if(criteria.order_way != null) {
        query += sep + 'order_way=' + criteria.order_way;
        sep = '&';
    }
    if(criteria.order_by != null) {
        query += sep + 'order_by=' + criteria.order_by;
        sep = '&';
    }
    if(criteria.status_filter != null) {
        query += sep + 'status_filter=' + criteria.status_filter;
        sep = '&';
    }
    if (criteria.deleted != null) {
        query += sep + 'deleted=' + criteria.deleted;
        sep = '&';
    }
    if(criteria.search_query != null) {
        query += sep + 'search_query=' + criteria.search_query;
        sep = '&';
    }
    if(criteria.source_filter != null) {
        query += sep + 'source_filter=' + criteria.source_filter;
        sep = '&';
    }
    if(criteria.include_external != null) {
        query += sep + 'include_external=' + criteria.include_external;
        sep = '&';
    }
    if(criteria.unfinished_only != null) {
        query += sep + 'unfinished_only=' + criteria.unfinished_only;
    }
    return query;
}

    /**
     * Highlight text in a html document
     * @param {string} element_id : id of the element to highlight
     */
function selectText(element_id) {
    "use strict";
    var doc = document;
    var text = doc.getElementById(element_id);
    var range;
    if (doc.body.createTextRange) {
        range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        var selection = window.getSelection();
        range = document.createRange();
        if (text) {
            range.selectNodeContents(text);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
}


// TODO : check if used. If yes, add comments, if no remove
String.prototype.replaceAll = function (find, replace) {
    var str = this;
    return str.replace(new RegExp(find, 'g'), replace);
};

// TODO : check if used. If yes, add comments, if no remove
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}
(function(a){a.topZIndex=function(b){return Math.max(0,Math.max.apply(null,a.map((b||"*")==="*"?a.makeArray(document.getElementsByTagName("*")):a(b),function(b){return parseFloat(a(b).css("z-index"))||null})))};a.fn.topZIndex=function(b){if(this.length===0)return this;b=a.extend({increment:1},b);var c=a.topZIndex(b.selector),d=b.increment;return this.each(function(){this.style.zIndex=c+=d})}})(jQuery);
// tipsy, facebook style tooltips for jquery
// version 1.0.0a
// (c) 2008-2010 jason frame [jason@onehackoranother.com]
// releated under the MIT license

(function($) {
    
    function fixTitle($ele) {
        if ($ele.attr('title') || typeof($ele.attr('original-title')) != 'string') {
            $ele.attr('original-title', $ele.attr('title') || '').removeAttr('title');
        }
    }
    
    function Tipsy(element, options) {
        this.$element = $(element);
        this.options = options;
        this.enabled = true;
        fixTitle(this.$element);
    }
    
    Tipsy.prototype = {
        show: function() {
            var title = this.getTitle();
            if (title && this.enabled) {
                var $tip = this.tip();
                
                $tip.find('.tipsy-inner')[this.options.html ? 'html' : 'text'](title);
                $tip[0].className = 'tipsy'; // reset classname in case of dynamic gravity
                $tip.remove().css({top: 0, left: 0, visibility: 'hidden', display: 'block'}).appendTo(document.body);
                
                var pos = $.extend({}, this.$element.offset(), {
                    width: this.$element[0].offsetWidth,
                    height: this.$element[0].offsetHeight
                });
                
                var actualWidth = $tip[0].offsetWidth, actualHeight = $tip[0].offsetHeight;
                var gravity = (typeof this.options.gravity == 'function')
                                ? this.options.gravity.call(this.$element[0])
                                : this.options.gravity;
                
                var tp;
                switch (gravity.charAt(0)) {
                    case 'n':
                        tp = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 's':
                        tp = {top: pos.top - actualHeight , left: pos.left + pos.width - this.options.offset / 2 - actualWidth / 2};
                        break;
                    case 'e':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth - this.options.offset};
                        break;
                    case 'w':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
                        break;
                }
                
                if (gravity.length == 2) {
                    if (gravity.charAt(1) == 'w') {
                        tp.left = pos.left + pos.width / 2 - 15;
                    } else {
                        tp.left = pos.left + pos.width / 2 - actualWidth + 15;
                    }
                }
                
                $tip.css(tp).addClass('tipsy-' + gravity);
                
                if (this.options.fade) {
                    $tip.stop().css({opacity: 0, display: 'block', visibility: 'visible'}).animate({opacity: this.options.opacity});
                } else {
                    $tip.css({visibility: 'visible', opacity: this.options.opacity});
                }
				$('.tipsy').topZIndex();

            }
        },
        
        hide: function() {
            if (this.options.fade) {
                this.tip().stop().fadeOut(function() { $(this).remove(); });
            } else {
                this.tip().remove();
            }
        },
        
        getTitle: function() {
            var title, $e = this.$element, o = this.options;
            fixTitle($e);
            var title, o = this.options;
            if (typeof o.title == 'string') {
                title = $e.attr(o.title == 'title' ? 'original-title' : o.title);
            } else if (typeof o.title == 'function') {
                title = o.title.call($e[0]);
            }
            title = ('' + title).replace(/(^\s*|\s*$)/, "");
            return title || o.fallback;
        },
        
        tip: function() {
            if (!this.$tip) {
                this.$tip = $('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"/></div>');
            }
            return this.$tip;
        },
        
        validate: function() {
            if (!this.$element[0].parentNode) {
                this.hide();
                this.$element = null;
                this.options = null;
            }
        },
        
        enable: function() { this.enabled = true; },
        disable: function() { this.enabled = false; },
        toggleEnabled: function() { this.enabled = !this.enabled; }
    };
    
    $.fn.tipsy = function(options) {
        
        if (options === true) {
            return this.data('tipsy');
        } else if (typeof options == 'string') {
            return this.data('tipsy')[options]();
        }
        
        options = $.extend({}, $.fn.tipsy.defaults, options);
        
        function get(ele) {
            var tipsy = $.data(ele, 'tipsy');
            if (!tipsy) {
                tipsy = new Tipsy(ele, $.fn.tipsy.elementOptions(ele, options));
                $.data(ele, 'tipsy', tipsy);
            }
            return tipsy;
        }
        
        function enter() {
            var tipsy = get(this);
            tipsy.hoverState = 'in';
            if (options.delayIn == 0) {
                tipsy.show();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'in') tipsy.show(); }, options.delayIn);
            }
        };
        
        function leave() {
            var tipsy = get(this);
            tipsy.hoverState = 'out';
            if (options.delayOut == 0) {
                tipsy.hide();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'out') tipsy.hide(); }, options.delayOut);
            }
        };
        
        if (!options.live) this.each(function() { get(this); });
        
        if (options.trigger != 'manual') {
            var binder   = options.live ? 'live' : 'bind',
                eventIn  = options.trigger == 'hover' ? 'mouseenter' : 'focus',
                eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
            this[binder](eventIn, enter)[binder](eventOut, leave);
        }
        
        return this;
        
    };
    
    $.fn.tipsy.defaults = {
        delayIn: 0,
        delayOut: 0,
        fade: false,
        fallback: '',
        gravity: 'n',
        html: false,
        live: false,
        offset: 0,
        opacity: 0.8,
        title: 'title',
        trigger: 'hover'
    };
    
    // Overwrite this method to provide options on a per-element basis.
    // For example, you could store the gravity in a 'tipsy-gravity' attribute:
    // return $.extend({}, options, {gravity: $(ele).attr('tipsy-gravity') || 'n' });
    // (remember - do not modify 'options' in place!)
    $.fn.tipsy.elementOptions = function(ele, options) {
        return $.metadata ? $.extend({}, options, $(ele).metadata()) : options;
    };
    
    $.fn.tipsy.autoNS = function() {
        return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? 's' : 'n';
    };
    
    $.fn.tipsy.autoWE = function() {
        return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? 'e' : 'w';
    };
    
})(jQuery);

/*! Copyright (c) 2010 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.4
 *
 * Requires: 1.2.2+
 */

(function($) {

    var types = ['DOMMouseScroll', 'mousewheel'];

    $.event.special.mousewheel = {
        setup: function() {
            if ( this.addEventListener ) {
                for ( var i=types.length; i; ) {
                    this.addEventListener( types[--i], handler, false );
                }
            } else {
                this.onmousewheel = handler;
            }
        },

        teardown: function() {
            if ( this.removeEventListener ) {
                for ( var i=types.length; i; ) {
                    this.removeEventListener( types[--i], handler, false );
                }
            } else {
                this.onmousewheel = null;
            }
        }
    };

    $.fn.extend({
        mousewheel: function(fn) {
            return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
        },

        unmousewheel: function(fn) {
            return this.unbind("mousewheel", fn);
        }
    });


    function handler(event) {
        var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
        event = $.event.fix(orgEvent);
        event.type = "mousewheel";

        // Old school scrollwheel delta
        if ( event.wheelDelta ) { delta = event.wheelDelta/120; }
        if ( event.detail     ) { delta = -event.detail/3; }
        if ( event.originalEvent.detail) { delta = -event.originalEvent.detail/3; }
        // New school multidimensional scroll (touchpads) deltas
        deltaY = delta;

        // Gecko
        if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
            deltaY = 0;
            deltaX = -1*delta;
        }
        // Webkit
        if ( orgEvent.wheelDelta !== undefined ) { delta = orgEvent.wheelDelta/120;}


        // Add event and delta to the front of the arguments
        args.unshift(event, delta, deltaX, deltaY);
        return $.event.handle.apply(this, args);
    }

})(jQuery);
//jQuery vertical scroller plugin v1.5
//Author: Simon Battersby, www.simonbattersby.com 
//Documentation: http://www.simonbattersby.com/blog/vertical-scrollbar-plugin-using-jquery-ui-slider/


(function( $ ){

    $.fn.sbscroller = function( options ) {

        var settings = {//defaults
            handleImage : false,
            handleTopImage : false,
            handleBottomImage : false,
            handleGripImage : false,
            mousewheel : false,
            autohide : false
        };

        return this.each(function() {
            if (options === 'refresh') {
                $.extend( settings, $(this).data() );//include any previously stored options for this scroll pane
                if (!$(this).hasClass('scroll-pane')){//traps the case where refresh has been called on slider that has not been set up
                    $(this).addClass('scroll-pane').data(options);//add a class and store the options as data against the element in case they are needed later
                    $(this).children().wrapAll('<div class="kw_scroll-content"/>');
                }
                setSlider($(this));
            }

            else {
                if ( options ) {
                    $.extend( settings, options );
                }
                $(this).addClass('scroll-pane').data(options);//add a class and store the options as data against the element in case they are needed later
                $(this).children().wrapAll('<div class="kw_scroll-content"/>');
                setSlider($(this));
            }

        });

        function setSlider($scrollpane){

            //change the main div to overflow-hidden as we can use the slider now
            $scrollpane.css('overflow','hidden');

            //if it's not already there, wrap an extra div around the scrollpane so we can use the mousewheel later
            if ($scrollpane.parent('.scroll-container').length==0) $scrollpane.wrap('<\div class="scroll-container"> /');


            //compare the height of the scroll content to the scroll pane to see if we need a scrollbar
            var difference = $scrollpane.find('.kw_scroll-content').height()-$scrollpane.height();//eg it's 200px longer
            $scrollpane.data('difference',difference);

            if(difference<=0 && $scrollpane.find('.kw_slider-wrap').length>0)//scrollbar exists but is no longer required
            {
                $scrollpane.find('.kw_slider-wrap').remove();//remove the scrollbar
                $scrollpane.find('.kw_scroll-content').css({top:0});//and reset the top position
            }

            if(difference>0)//if the scrollbar is needed, set it up...
            {
                var proportion = difference / $scrollpane.find('.kw_scroll-content').height();//eg 200px/500px

                var handleHeight = Math.round((1-proportion)*$scrollpane.height());//set the proportional height - round it to make sure everything adds up correctly later on
                handleHeight -= handleHeight%2;

                //if the slider has already been set up and this function is called again, we may need to set the position of the slider handle
                var contentposition = $scrollpane.find('.kw_scroll-content').position();
                var sliderInitial = 100*(1-Math.abs(contentposition.top)/difference);


                if($scrollpane.find('.kw_slider-wrap').length==0)//if the kw_slider-wrap doesn't exist, insert it and set the initial value
                {
                    $scrollpane.append('<\div class="kw_slider-wrap" ><\div class="slider-vertical"><\/div><\/div>');//append the necessary divs so they're only there if needed
                    sliderInitial = 100;
                }

                $scrollpane.find('.kw_slider-wrap').height(Math.round($scrollpane.height()));//set the height of the slider bar to that of the scroll pane


                //set up the slider
                $scrollpane.find('.slider-vertical').slider({
                    orientation: 'vertical',
                    min: 0,
                    max: 100,
                    value: sliderInitial,
                    slide: function(event, ui) {
                        var topValue = -((100-ui.value)*difference/100);
                        $scrollpane.find('.kw_scroll-content').css({top:topValue});//move the top up (negative value) by the percentage the slider has been moved times the difference in height
                    },
                    change: function(event, ui) {
                        var topValue = -((100-ui.value)*($scrollpane.find('.kw_scroll-content').height()-$scrollpane.height())/100);//recalculate the difference on change
                        $scrollpane.find('.kw_scroll-content').css({top:topValue});//move the top up (negative value) by the percentage the slider has been moved times the difference in height
                    }
                });

                //set the handle height and bottom margin so the middle of the handle is in line with the slider
                $scrollpane.find(".ui-slider-handle").css({height:handleHeight,'margin-bottom':-0.5*handleHeight});
                var origSliderHeight = $scrollpane.height();//read the original slider height
                var sliderHeight = origSliderHeight - handleHeight ;//the height through which the handle can move needs to be the original height minus the handle height
                var sliderMargin =  (origSliderHeight - sliderHeight)*0.5;//so the slider needs to have both top and bottom margins equal to half the difference
                $scrollpane.find(".ui-slider").css({height:sliderHeight,'margin-top':sliderMargin});//set the slider height and margins
                $scrollpane.find(".ui-slider-range").css({top:-sliderMargin});//position the slider-range div at the top of the slider container

                //create elements to hold the images for the scrollbar handle if needed
                if(settings.handleTopImage) $scrollpane.find(".ui-slider-handle").css({backgroundImage:'url('+settings.handleImage+')',backgroundRepeat:'repeat-y'});
                if(settings.handleTopImage) $scrollpane.find(".ui-slider-handle").append('<img class="scrollbar-top" src="'+settings.handleTopImage+'"/>');
                if(settings.handleBottomImage) $scrollpane.find(".ui-slider-handle").append('<img class="scrollbar-bottom" src="'+settings.handleBottomImage+'"/>');
                if(settings.handleGripImage) {
                    $scrollpane.find(".ui-slider-handle").append('<img class="scrollbar-grip" src="'+settings.handleGripImage+'"/>');
                    $scrollpane.find('.scrollbar-grip').load(function(){//wait till the image loads for Webkit
                        $scrollpane.find(".scrollbar-grip").css({marginTop:-1*Math.round(0.5*$scrollpane.find(".scrollbar-grip").height()+0.5)+'px'});
                    });
                }
            }//end if

            //code for clicks on the scrollbar outside the slider
            $(".ui-slider").click(function(event){//stop any clicks on the slider propagating through to the code below
                event.stopPropagation();
            });

            $(".kw_slider-wrap").click(function(event){//clicks on the wrap outside the slider range
                var offsetTop = $(this).offset().top;//read the offset of the scroll pane
                var clickValue = (event.pageY-offsetTop)*100/$(this).height();//find the click point, subtract the offset, and calculate percentage of the slider clicked
                $(this).find(".slider-vertical").slider("value", 100-clickValue);//set the new value of the slider
            });


            //additional code for mousewheel
            if($.fn.mousewheel){
                $scrollpane.parent().unmousewheel();//remove any previously attached mousewheel events
                $scrollpane.parent().mousewheel(function(event, delta){
                    var speed = Math.round(1000/$scrollpane.data('difference'));
                    if (speed <1) speed = 1;
                    if (speed >100) speed = 100;
                    var sliderVal = $(this).find(".slider-vertical").slider("value");//read current value of the slider
                    sliderVal += (delta*speed);//increment the current value
                    $(this).find(".slider-vertical").slider("value", sliderVal);//and set the new value of the slider
                    //$(this).find(".slider-vertical").slider("value",70);
                    event.preventDefault();//stop any default behaviour
                });
            }

            //autohide
            if(settings.autohide){
                if (!$scrollpane.find(".kw_slider-wrap").hasClass('kw_slider-wrap-active')) $scrollpane.find(".kw_slider-wrap").hide();//only hide if it's not already active - this could be the case if content is added or removed from within the scroll pane
                $scrollpane.hover(function(){
                        $scrollpane.find(".kw_slider-wrap").show().addClass('kw_slider-wrap-active');
                    },
                    function(){
                        $scrollpane.find(".kw_slider-wrap").hide().removeClass('kw_slider-wrap-active');
                    })
            }


        }

    };
})( jQuery );

jQuery.fn.customScrollV = function(_options){
    var _options = jQuery.extend({
        lineWidth: 16, /* this parameter sets the width of the scroll*/
        afterMouseWheel: function(){}
    }, _options);
	;
    return this.each(function(){
        var _box = jQuery(this);
		$(".koemei_widget").scrollTop(0);
        if(_box.is(':visible')){
            if(_box.children('.kw_scroll-content').length == 0){
                var line_w = _options.lineWidth;
                /*--- init part ---*/
                var scrollBar = jQuery('<div class="kw_vscroll-bar kw_scroll-bar"><div class="scroll-up"></div><div class="kw_scroll-line"><div class="kw_scroll-slider"><div class="kw_scroll-slider-c"></div></div></div><div class="kw_scroll-down"></div></div>');
                _box.wrapInner('<div class="kw_scroll-content"><div class="kw_scroll-hold"></div></div>').append(scrollBar);
                var scrollContent = _box.children('.kw_scroll-content');
                var scrollSlider = scrollBar.find('.kw_scroll-slider');
                var scrollSliderH = scrollSlider.parent();
                var scrollUp = scrollBar.find('.kw_scroll-up');
                var scrollDown = scrollBar.find('.kw_scroll-down');
				var readonly = $('#transcript_container').find('.kw_readonly_scroll');
				
                /*--- different variables ---*/
                var box_h = _box.height();
                var slider_h = 0;
                var slider_f = 0;
                var cont_h = $(scrollContent).height();
				if (readonly.length>0) {
					dif = parseInt(cont_h*0.012);
					cont_h = parseInt(cont_h)+dif;	
				}
				
				
                var _f = false;
                var _f1 = false;
                var _f2 = true;
                var _t1, _t2, _s1, _s2;
                /*--- set styles ---*/
                _box.css({
                    position: 'relative',
                    overflow: 'hidden',
                    height: box_h
                });
                scrollContent.css({
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    zIndex: 1,
                    height: 'auto'
                });
                scrollBar.css({
                    position: 'absolute',
                    bottom: 0,
                    right: 0,
                    zIndex:2,
                    width: line_w,
                    height: box_h,
                    overflow: 'hidden'
                });
                scrollUp.css({
                    width: line_w,
                    height: line_w,
                    overflow: 'hidden',
                    cursor: 'pointer'
                });
                scrollDown.css({
                    width: line_w,
                    height: line_w,
                    overflow: 'hidden',
                    cursor: 'pointer'
                });
                slider_h = scrollBar.height();
                if(scrollUp.is(':visible')) slider_h -= scrollUp.height();
                if(scrollDown.is(':visible')) slider_h -= scrollDown.height();
                scrollSliderH.css({
                    position: 'relative',
                    width: line_w,
                    height: slider_h,
                    overflow: 'hidden'
                });
                slider_h = 0;
                scrollSlider.css({
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    width: line_w,
                    height: slider_h,
                    overflow: 'hidden',
                    cursor: 'pointer'
                });
                box_h = _box.height();
                cont_h = scrollContent.height();
				if (readonly.length>0) {
					dif = parseInt(cont_h*0.012);
					cont_h = parseInt(cont_h)+dif;		
				}
                if(box_h < cont_h){
                    _f = true;
                    slider_h = Math.round(box_h/cont_h*scrollSliderH.height());
                    if(slider_h < 5) slider_h = 5;
                    scrollSlider.height(slider_h);
                    slider_h = scrollSlider.height();
                    slider_f = (cont_h - box_h)/(scrollSliderH.height() - slider_h);
                    _s1 = (scrollSliderH.height() - slider_h)/15;
                    _s2 = (scrollSliderH.height() - slider_h)/3;
                    scrollContent.children('.kw_scroll-hold').css('margin-right', scrollSliderH.width());
					$(".koemei_widget").scrollTop(0);
                }
                else{
                    _f = false;
                    scrollBar.hide();
                    scrollContent.css({width: _box.width(), top: 0, left:0});
                    scrollContent.children('.kw_scroll-hold').css('margin-right', 0);
					$(".koemei_widget").scrollTop(0);
                };
                var _top = 0;
                /*--- element's events ---*/
                scrollUp.mousedown(function(){
					
                    _top -= _s1;
                    scrollCont();
                    _t1 = setTimeout(function(){
                        _t2 = setInterval(function(){
                            _top -= 4/slider_f;
                            scrollCont();
                        }, 20);
                    }, 500);
                    return false;
                }).mouseup(function(){
                        if(_t1) clearTimeout(_t1);
                        if(_t2) clearInterval(_t2);
                    }).mouseleave(function(){
                        if(_t1) clearTimeout(_t1);
                        if(_t2) clearInterval(_t2);
                    });
                scrollDown.mousedown(function(){
                    _top += _s1;
                    scrollCont();
                    _t1 = setTimeout(function(){
                        _t2 = setInterval(function(){
                            _top += 4/slider_f;
                            scrollCont();
                        }, 20);
                    }, 500);
                    return false;
                }).mouseup(function(){
                        if(_t1) clearTimeout(_t1);
                        if(_t2) clearInterval(_t2);
                    }).mouseleave(function(){
                        if(_t1) clearTimeout(_t1);
                        if(_t2) clearInterval(_t2);
                    });
                scrollSliderH.click(function(e){
                    if(_f2){
                        _top = e.pageY - scrollSliderH.offset().top - scrollSlider.outerHeight()/2;
                        scrollCont();
                    }
                    else{
                        _f2 = true;
                    };
                });
				
                var t_y = 0;
                var tttt_f = (jQuery.browser.msie)?(true):(false);
                scrollSlider.mousedown(function(e){
					
                    t_y = e.pageY - jQuery(this).position().top;
					
                    _f1 = true;
                    return false;
                }).mouseup(function(){
                        _f1 = false;
                    });
                jQuery('body').mousemove(function(e){
                    if(_f1){
                        _f2 = false;
                        _top = e.pageY - t_y;
                        if(tttt_f) document.selection.empty();
                        scrollCont();
                    }
                }).mouseup(function(){
                        _f1 = false;
                    });
                document.body.onselectstart = function(){
                    if(_f1) return false;
                };
                if(!_box.hasClass('not-scroll')){
                    _box.bind('mousewheel', function(event, delta){
                        if(_f){
                            _top -=delta*_s1;
                            scrollCont();
                            if ((_top > 0) && (_top + slider_h < scrollSliderH.height())) {
                                _options.afterMouseWheel();
                                return false;
                            }
                        }
                    });
                };
				
                function scrollCont(){
					
                    if(_top < 0) _top = 0;
                    else if(_top+slider_h > scrollSliderH.height()) _top = scrollSliderH.height() - slider_h;
                    scrollSlider.css('top', _top);
                    scrollContent.css('top', -_top*slider_f);
                    scrollContent.trigger('scrollContentMove', {height: scrollContent.height()-box_h, top: Math.round(_top*slider_f)});
					$(".koemei_widget").scrollTop(0);
				};
                this.scrollResize = function(){
					if(_box.hasClass('basket-scroll')){
						scrollContent.css({position: 'relative'});
						_box.css({height: 'auto'});
                    box_h = _box.height();
                    cont_h = scrollContent.height();
						scrollContent.css({position: 'absolute'});
						_box.css({height: box_h});
						//box_h -= 20;
					}
					else{
                    	box_h = _box.height();
                   		cont_h = scrollContent.height();
					}
					
					
                    if(box_h < cont_h){
                        _f = true;
                        scrollBar.show();
                        scrollBar.height(box_h);
                        slider_h = scrollBar.height();
                        if(scrollUp.is(':visible')) slider_h -= scrollUp.height();
                        if(scrollDown.is(':visible')) slider_h -= scrollDown.height();
                        scrollSliderH.height(slider_h);
                        slider_h = Math.round(box_h/cont_h*scrollSliderH.height());
                        if(slider_h < 5) slider_h = 5;
                        scrollSlider.height(slider_h);
                        slider_h = scrollSlider.height();
                        slider_f = (cont_h - box_h)/(scrollSliderH.height() - slider_h);
						var new_top = parseInt(scrollContent.css('top'));
                        if(cont_h + scrollContent.position().top < box_h) scrollContent.css('top', -(cont_h - box_h));
                        _top = - new_top/slider_f;
                        scrollSlider.css('top', _top);
                        _s1 = (scrollSliderH.height() - slider_h)/15;
                        _s2 = (scrollSliderH.height() - slider_h)/3;
                        scrollContent.children('.kw_scroll-hold').css('margin-right', scrollSliderH.width());
						scrollContent.children('.kw_scroll-hold').css({width:scrollContent.outerWidth() - scrollSliderH.width()});
						setTimeout(function(){
							scrollContent.children('.kw_scroll-hold').css({width:'auto'});
						}, 1500);
						$(".koemei_widget").scrollTop(0);
						$("#kw_transcript_container").scrollTop(0);
                    }
                    else{
                        _f = false;
                        scrollBar.hide();
                        scrollContent.css({top: 0, left:0});
                        scrollContent.children('.kw_scroll-hold').css('margin-right', 0);
						scrollContent.children('.kw_scroll-hold').css({width:scrollContent.outerWidth()});
						setTimeout(function(){
							scrollContent.children('.kw_scroll-hold').css({width:'auto'});
						}, 1500);
						$(".koemei_widget").scrollTop(0);
                    };
                };
				

                /*setInterval(function(){
                    if(_box.is(':visible') && cont_h != scrollContent.height()) _box.get(0).scrollResize();
                }, 100);*/

            }
            else{
                this.scrollResize();
            };
        };
    })};
/*global jQuery: true */

/*
   --------------------------------
   Infinite Scroll
   --------------------------------
   + https://github.com/paulirish/infinite-scroll
   + version 2.0b2.120519
   + Copyright 2011/12 Paul Irish & Luke Shumard
   + Licensed under the MIT license

   + Documentation: http://infinite-scroll.com/
*/

(function (window, $, undefined) {
    "use strict";

    $.infinitescroll = function infscr(options, callback, element) {
        this.element = $(element);

        // Flag the object in the event of a failed creation
        if (!this._create(options, callback)) {
            this.failed = true;
        }
    };

    $.infinitescroll.defaults = {
        loading: {
            finished: undefined,
            finishedMsg: "<em>Congratulations, you've reached the end of the internet.</em>",
            img: "data:image/gif;base64,R0lGODlh3AATAPQeAPDy+MnQ6LW/4N3h8MzT6rjC4sTM5r/I5NHX7N7j8c7U6tvg8OLl8uXo9Ojr9b3G5MfP6Ovu9tPZ7PT1+vX2+tbb7vf4+8/W69jd7rC73vn5/O/x+K243ai02////wAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQECgD/ACwAAAAA3AATAAAF/6AnjmRpnmiqrmzrvnAsz3Rt33iu73zv/8CgcEj0BAScpHLJbDqf0Kh0Sq1ar9isdioItAKGw+MAKYMFhbF63CW438f0mg1R2O8EuXj/aOPtaHx7fn96goR4hmuId4qDdX95c4+RBIGCB4yAjpmQhZN0YGYGXitdZBIVGAsLoq4BBKQDswm1CQRkcG6ytrYKubq8vbfAcMK9v7q7EMO1ycrHvsW6zcTKsczNz8HZw9vG3cjTsMIYqQkCLBwHCgsMDQ4RDAYIqfYSFxDxEfz88/X38Onr16+Bp4ADCco7eC8hQYMAEe57yNCew4IVBU7EGNDiRn8Z831cGLHhSIgdFf9chIeBg7oA7gjaWUWTVQAGE3LqBDCTlc9WOHfm7PkTqNCh54rePDqB6M+lR536hCpUqs2gVZM+xbrTqtGoWqdy1emValeXKzggYBBB5y1acFNZmEvXAoN2cGfJrTv3bl69Ffj2xZt3L1+/fw3XRVw4sGDGcR0fJhxZsF3KtBTThZxZ8mLMgC3fRatCbYMNFCzwLEqLgE4NsDWs/tvqdezZf13Hvk2A9Szdu2X3pg18N+68xXn7rh1c+PLksI/Dhe6cuO3ow3NfV92bdArTqC2Ebd3A8vjf5QWfH6Bg7Nz17c2fj69+fnq+8N2Lty+fuP78/eV2X13neIcCeBRwxorbZrA1ANoCDGrgoG8RTshahQ9iSKEEzUmYIYfNWViUhheCGJyIP5E4oom7WWjgCeBFAJNv1DVV01MAdJhhjdkplWNzO/5oXI846njjVEIqR2OS2B1pE5PVscajkxhMycqLJghQSwT40PgfAl4GqNSXYdZXJn5gSkmmmmJu1aZYb14V51do+pTOCmA40AqVCIhG5IJ9PvYnhIFOxmdqhpaI6GeHCtpooisuutmg+Eg62KOMKuqoTaXgicQWoIYq6qiklmoqFV0UoeqqrLbq6quwxirrrLTWauutJ4QAACH5BAUKABwALAcABADOAAsAAAX/IPd0D2dyRCoUp/k8gpHOKtseR9yiSmGbuBykler9XLAhkbDavXTL5k2oqFqNOxzUZPU5YYZd1XsD72rZpBjbeh52mSNnMSC8lwblKZGwi+0QfIJ8CncnCoCDgoVnBHmKfByGJimPkIwtiAeBkH6ZHJaKmCeVnKKTHIihg5KNq4uoqmEtcRUtEREMBggtEr4QDrjCuRC8h7/BwxENeicSF8DKy82pyNLMOxzWygzFmdvD2L3P0dze4+Xh1Arkyepi7dfFvvTtLQkZBC0T/FX3CRgCMOBHsJ+EHYQY7OinAGECgQsB+Lu3AOK+CewcWjwxQeJBihtNGHSoQOE+iQ3//4XkwBBhRZMcUS6YSXOAwIL8PGqEaSJCiYt9SNoCmnJPAgUVLChdaoFBURN8MAzl2PQphwQLfDFd6lTowglHve6rKpbjhK7/pG5VinZP1qkiz1rl4+tr2LRwWU64cFEihwEtZgbgR1UiHaMVvxpOSwBA37kzGz9e8G+B5MIEKLutOGEsAH2ATQwYfTmuX8aETWdGPZmiZcccNSzeTCA1Sw0bdiitC7LBWgu8jQr8HRzqgpK6gX88QbrB14z/kF+ELpwB8eVQj/JkqdylAudji/+ts3039vEEfK8Vz2dlvxZKG0CmbkKDBvllRd6fCzDvBLKBDSCeffhRJEFebFk1k/Mv9jVIoIJZSeBggwUaNeB+Qk34IE0cXlihcfRxkOAJFFhwGmKlmWDiakZhUJtnLBpnWWcnKaAZcxI0piFGGLBm1mc90kajSCveeBVWKeYEoU2wqeaQi0PetoE+rr14EpVC7oAbAUHqhYExbn2XHHsVqbcVew9tx8+XJKk5AZsqqdlddGpqAKdbAYBn1pcczmSTdWvdmZ17c1b3FZ99vnTdCRFM8OEcAhLwm1NdXnWcBBSMRWmfkWZqVlsmLIiAp/o1gGV2vpS4lalGYsUOqXrddcKCmK61aZ8SjEpUpVFVoCpTj4r661Km7kBHjrDyc1RAIQAAIfkEBQoAGwAsBwAEAM4ACwAABf/gtmUCd4goQQgFKj6PYKi0yrrbc8i4ohQt12EHcal+MNSQiCP8gigdz7iCioaCIvUmZLp8QBzW0EN2vSlCuDtFKaq4RyHzQLEKZNdiQDhRDVooCwkbfm59EAmKi4SGIm+AjIsKjhsqB4mSjT2IOIOUnICeCaB/mZKFNTSRmqVpmJqklSqskq6PfYYCDwYHDC4REQwGCBLGxxIQDsHMwhAIX8bKzcENgSLGF9PU1j3Sy9zX2NrgzQziChLk1BHWxcjf7N046tvN82715czn9Pryz6Ilc4ACj4EBOCZM8KEnAYYADBRKnACAYUMFv1wotIhCEcaJCisqwJFgAUSQGyX/kCSVUUTIdKMwJlyo0oXHlhskwrTJciZHEXsgaqS4s6PJiCAr1uzYU8kBBSgnWFqpoMJMUjGtDmUwkmfVmVypakWhEKvXsS4nhLW5wNjVroJIoc05wSzTr0PtiigpYe4EC2vj4iWrFu5euWIMRBhacaVJhYQBEFjA9jHjyQ0xEABwGceGAZYjY0YBOrRLCxUp29QM+bRkx5s7ZyYgVbTqwwti2ybJ+vLtDYpycyZbYOlptxdx0kV+V7lC5iJAyyRrwYKxAdiz82ng0/jnAdMJFz0cPi104Ec1Vj9/M6F173vKL/feXv156dw11tlqeMMnv4V5Ap53GmjQQH97nFfg+IFiucfgRX5Z8KAgbUlQ4IULIlghhhdOSB6AgX0IVn8eReghen3NRIBsRgnH4l4LuEidZBjwRpt6NM5WGwoW0KSjCwX6yJSMab2GwwAPDXfaBCtWpluRTQqC5JM5oUZAjUNS+VeOLWpJEQ7VYQANW0INJSZVDFSnZphjSikfmzE5N4EEbQI1QJmnWXCmHulRp2edwDXF43txukenJwvI9xyg9Q26Z3MzGUcBYFEChZh6DVTq34AU8Iflh51Sd+CnKFYQ6mmZkhqfBKfSxZWqA9DZanWjxmhrWwi0qtCrt/43K6WqVjjpmhIqgEGvculaGKklKstAACEAACH5BAUKABwALAcABADOAAsAAAX/ICdyQmaMYyAUqPgIBiHPxNpy79kqRXH8wAPsRmDdXpAWgWdEIYm2llCHqjVHU+jjJkwqBTecwItShMXkEfNWSh8e1NGAcLgpDGlRgk7EJ/6Ae3VKfoF/fDuFhohVeDeCfXkcCQqDVQcQhn+VNDOYmpSWaoqBlUSfmowjEA+iEAEGDRGztAwGCDcXEA60tXEiCrq8vREMEBLIyRLCxMWSHMzExnbRvQ2Sy7vN0zvVtNfU2tLY3rPgLdnDvca4VQS/Cpk3ABwSLQkYAQwT/P309vcI7OvXr94jBQMJ/nskkGA/BQBRLNDncAIAiDcG6LsxAWOLiQzmeURBKWSLCQbv/1F0eDGinJUKR47YY1IEgQASKk7Yc7ACRwZm7mHweRJoz59BJUogisKCUaFMR0x4SlJBVBFTk8pZivTR0K73rN5wqlXEAq5Fy3IYgHbEzQ0nLy4QSoCjXLoom96VOJEeCosK5n4kkFfqXjl94wa+l1gvAcGICbewAOAxY8l/Ky/QhAGz4cUkGxu2HNozhwMGBnCUqUdBg9UuW9eUynqSwLHIBujePef1ZGQZXcM+OFuEBeBhi3OYgLyqcuaxbT9vLkf4SeqyWxSQpKGB2gQpm1KdWbu72rPRzR9Ne2Nu9Kzr/1Jqj0yD/fvqP4aXOt5sW/5qsXXVcv1Nsp8IBUAmgswGF3llGgeU1YVXXKTN1FlhWFXW3gIE+DVChApysACHHo7Q4A35lLichh+ROBmLKAzgYmYEYDAhCgxKGOOMn4WR4kkDaoBBOxJtdNKQxFmg5JIWIBnQc07GaORfUY4AEkdV6jHlCEISSZ5yTXpp1pbGZbkWmcuZmQCaE6iJ0FhjMaDjTMsgZaNEHFRAQVp3bqXnZED1qYcECOz5V6BhSWCoVJQIKuKQi2KFKEkEFAqoAo7uYSmO3jk61wUUMKmknJ4SGimBmAa0qVQBhAAAIfkEBQoAGwAsBwAEAM4ACwAABf/gJm5FmRlEqhJC+bywgK5pO4rHI0D3pii22+Mg6/0Ej96weCMAk7cDkXf7lZTTnrMl7eaYoy10JN0ZFdco0XAuvKI6qkgVFJXYNwjkIBcNBgR8TQoGfRsJCRuCYYQQiI+ICosiCoGOkIiKfSl8mJkHZ4U9kZMbKaI3pKGXmJKrngmug4WwkhA0lrCBWgYFCCMQFwoQDRHGxwwGCBLMzRLEx8iGzMMO0cYNeCMKzBDW19lnF9DXDIY/48Xg093f0Q3s1dcR8OLe8+Y91OTv5wrj7o7B+7VNQqABIoRVCMBggsOHE36kSoCBIcSH3EbFangxogJYFi8CkJhqQciLJEf/LDDJEeJIBT0GsOwYUYJGBS0fjpQAMidGmyVP6sx4Y6VQhzs9VUwkwqaCCh0tmKoFtSMDmBOf9phg4SrVrROuasRQAaxXpVUhdsU6IsECZlvX3kwLUWzRt0BHOLTbNlbZG3vZinArge5Dvn7wbqtQkSYAAgtKmnSsYKVKo2AfW048uaPmG386i4Q8EQMBAIAnfB7xBxBqvapJ9zX9WgRS2YMpnvYMGdPK3aMjt/3dUcNI4blpj7iwkMFWDXDvSmgAlijrt9RTR78+PS6z1uAJZIe93Q8g5zcsWCi/4Y+C8bah5zUv3vv89uft30QP23punGCx5954oBBwnwYaNCDY/wYrsYeggnM9B2Fpf8GG2CEUVWhbWAtGouEGDy7Y4IEJVrbSiXghqGKIo7z1IVcXIkKWWR361QOLWWnIhwERpLaaCCee5iMBGJQmJGyPFTnbkfHVZGRtIGrg5HALEJAZbu39BuUEUmq1JJQIPtZilY5hGeSWsSk52G9XqsmgljdIcABytq13HyIM6RcUA+r1qZ4EBF3WHWB29tBgAzRhEGhig8KmqKFv8SeCeo+mgsF7YFXa1qWSbkDpom/mqR1PmHCqJ3fwNRVXjC7S6CZhFVCQ2lWvZiirhQq42SACt25IK2hv8TprriUV1usGgeka7LFcNmCldMLi6qZMgFLgpw16Cipb7bC1knXsBiEAACH5BAUKABsALAcABADOAAsAAAX/4FZsJPkUmUGsLCEUTywXglFuSg7fW1xAvNWLF6sFFcPb42C8EZCj24EJdCp2yoegWsolS0Uu6fmamg8n8YYcLU2bXSiRaXMGvqV6/KAeJAh8VgZqCX+BexCFioWAYgqNi4qAR4ORhRuHY408jAeUhAmYYiuVlpiflqGZa5CWkzc5fKmbbhIpsAoQDRG8vQwQCBLCwxK6vb5qwhfGxxENahvCEA7NzskSy7vNzzzK09W/PNHF1NvX2dXcN8K55cfh69Luveol3vO8zwi4Yhj+AQwmCBw4IYclDAAJDlQggVOChAoLKkgFkSCAHDwWLKhIEOONARsDKryogFPIiAUb/95gJNIiw4wnI778GFPhzBKFOAq8qLJEhQpiNArjMcHCmlTCUDIouTKBhApELSxFWiGiVKY4E2CAekPgUphDu0742nRrVLJZnyrFSqKQ2ohoSYAMW6IoDpNJ4bLdILTnAj8KUF7UeENjAKuDyxIgOuGiOI0EBBMgLNew5AUrDTMGsFixwBIaNCQuAXJB57qNJ2OWm2Aj4skwCQCIyNkhhtMkdsIuodE0AN4LJDRgfLPtn5YDLdBlraAByuUbBgxQwICxMOnYpVOPej074OFdlfc0TqC62OIbcppHjV4o+LrieWhfT8JC/I/T6W8oCl29vQ0XjLdBaA3s1RcPBO7lFvpX8BVoG4O5jTXRQRDuJ6FDTzEWF1/BCZhgbyAKE9qICYLloQYOFtahVRsWYlZ4KQJHlwHS/IYaZ6sZd9tmu5HQm2xi1UaTbzxYwJk/wBF5g5EEYOBZeEfGZmNdFyFZmZIR4jikbLThlh5kUUVJGmRT7sekkziRWUIACABk3T4qCsedgO4xhgGcY7q5pHJ4klBBTQRJ0CeHcoYHHUh6wgfdn9uJdSdMiebGJ0zUPTcoS286FCkrZxnYoYYKWLkBowhQoBeaOlZAgVhLidrXqg2GiqpQpZ4apwSwRtjqrB3muoF9BboaXKmshlqWqsWiGt2wphJkQbAU5hoCACH5BAUKABsALAcABADOAAsAAAX/oGFw2WZuT5oZROsSQnGaKjRvilI893MItlNOJ5v5gDcFrHhKIWcEYu/xFEqNv6B1N62aclysF7fsZYe5aOx2yL5aAUGSaT1oTYMBwQ5VGCAJgYIJCnx1gIOBhXdwiIl7d0p2iYGQUAQBjoOFSQR/lIQHnZ+Ue6OagqYzSqSJi5eTpTxGcjcSChANEbu8DBAIEsHBChe5vL13G7fFuscRDcnKuM3H0La3EA7Oz8kKEsXazr7Cw9/Gztar5uHHvte47MjktznZ2w0G1+D3BgirAqJmJMAQgMGEgwgn5Ei0gKDBhBMALGRYEOJBb5QcWlQo4cbAihZz3GgIMqFEBSM1/4ZEOWPAgpIIJXYU+PIhRG8ja1qU6VHlzZknJNQ6UanCjQkWCIGSUGEjAwVLjc44+DTqUQtPPS5gejUrTa5TJ3g9sWCr1BNUWZI161StiQUDmLYdGfesibQ3XMq1OPYthrwuA2yU2LBs2cBHIypYQPPlYAKFD5cVvNPtW8eVGbdcQADATsiNO4cFAPkvHpedPzc8kUcPgNGgZ5RNDZG05reoE9s2vSEP79MEGiQGy1qP8LA4ZcdtsJE48ONoLTBtTV0B9LsTnPceoIDBDQvS7W7vfjVY3q3eZ4A339J4eaAmKqU/sV58HvJh2RcnIBsDUw0ABqhBA5aV5V9XUFGiHfVeAiWwoFgJJrIXRH1tEMiDFV4oHoAEGlaWhgIGSGBO2nFomYY3mKjVglidaNYJGJDkWW2xxTfbjCbVaOGNqoX2GloR8ZeTaECS9pthRGJH2g0b3Agbk6hNANtteHD2GJUucfajCQBy5OOTQ25ZgUPvaVVQmbKh9510/qQpwXx3SQdfk8tZJOd5b6JJFplT3ZnmmX3qd5l1eg5q00HrtUkUn0AKaiGjClSAgKLYZcgWXwocGRcCFGCKwSB6ceqphwmYRUFYT/1WKlOdUpipmxW0mlCqHjYkAaeoZlqrqZ4qd+upQKaapn/AmgAegZ8KUtYtFAQQAgAh+QQFCgAbACwHAAQAzgALAAAF/+C2PUcmiCiZGUTrEkKBis8jQEquKwU5HyXIbEPgyX7BYa5wTNmEMwWsSXsqFbEh8DYs9mrgGjdK6GkPY5GOeU6ryz7UFopSQEzygOGhJBjoIgMDBAcBM0V/CYqLCQqFOwobiYyKjn2TlI6GKC2YjJZknouaZAcQlJUHl6eooJwKooobqoewrJSEmyKdt59NhRKFMxLEEA4RyMkMEAjDEhfGycqAG8TQx9IRDRDE3d3R2ctD1RLg0ttKEnbY5wZD3+zJ6M7X2RHi9Oby7u/r9g38UFjTh2xZJBEBMDAboogAgwkQI07IMUORwocSJwCgWDFBAIwZOaJIsOBjRogKJP8wTODw5ESVHVtm3AhzpEeQElOuNDlTZ0ycEUWKWFASqEahGwYUPbnxoAgEdlYSqDBkgoUNClAlIHbSAoOsqCRQnQHxq1axVb06FWFxLIqyaze0Tft1JVqyE+pWXMD1pF6bYl3+HTqAWNW8cRUFzmih0ZAAB2oGKukSAAGGRHWJgLiR6AylBLpuHKKUMlMCngMpDSAa9QIUggZVVvDaJobLeC3XZpvgNgCmtPcuwP3WgmXSq4do0DC6o2/guzcseECtUoO0hmcsGKDgOt7ssBd07wqesAIGZC1YIBa7PQHvb1+SFo+++HrJSQfB33xfav3i5eX3Hnb4CTJgegEq8tH/YQEOcIJzbm2G2EoYRLgBXFpVmFYDcREV4HIcnmUhiGBRouEMJGJGzHIspqgdXxK0yCKHRNXoIX4uorCdTyjkyNtdPWrA4Up82EbAbzMRxxZRR54WXVLDIRmRcag5d2R6ugl3ZXzNhTecchpMhIGVAKAYpgJjjsSklBEd99maZoo535ZvdamjBEpusJyctg3h4X8XqodBMx0tiNeg/oGJaKGABpogS40KSqiaEgBqlQWLUtqoVQnytekEjzo0hHqhRorppOZt2p923M2AAV+oBtpAnnPNoB6HaU6mAAIU+IXmi3j2mtFXuUoHKwXpzVrsjcgGOauKEjQrwq157hitGq2NoWmjh7z6Wmxb0m5w66+2VRAuXN/yFUAIACH5BAUKABsALAcABADOAAsAAAX/4CZuRiaM45MZqBgIRbs9AqTcuFLE7VHLOh7KB5ERdjJaEaU4ClO/lgKWjKKcMiJQ8KgumcieVdQMD8cbBeuAkkC6LYLhOxoQ2PF5Ys9PKPBMen17f0CCg4VSh32JV4t8jSNqEIOEgJKPlkYBlJWRInKdiJdkmQlvKAsLBxdABA4RsbIMBggtEhcQsLKxDBC2TAS6vLENdJLDxMZAubu8vjIbzcQRtMzJz79S08oQEt/guNiyy7fcvMbh4OezdAvGrakLAQwyABsELQkY9BP+//ckyPDD4J9BfAMh1GsBoImMeQUN+lMgUJ9CiRMa5msxoB9Gh/o8GmxYMZXIgxtR/yQ46S/gQAURR0pDwYDfywoyLPip5AdnCwsMFPBU4BPFhKBDi444quCmDKZOfwZ9KEGpCKgcN1jdALSpPqIYsabS+nSqvqplvYqQYAeDPgwKwjaMtiDl0oaqUAyo+3TuWwUAMPpVCfee0cEjVBGQq2ABx7oTWmQk4FglZMGN9fGVDMCuiH2AOVOu/PmyxM630gwM0CCn6q8LjVJ8GXvpa5Uwn95OTC/nNxkda1/dLSK475IjCD6dHbK1ZOa4hXP9DXs5chJ00UpVm5xo2qRpoxptwF2E4/IbJpB/SDz9+q9b1aNfQH08+p4a8uvX8B53fLP+ycAfemjsRUBgp1H20K+BghHgVgt1GXZXZpZ5lt4ECjxYR4ScUWiShEtZqBiIInRGWnERNnjiBglw+JyGnxUmGowsyiiZg189lNtPGACjV2+S9UjbU0JWF6SPvEk3QZEqsZYTk3UAaRSUnznJI5LmESCdBVSyaOWUWLK4I5gDUYVeV1T9l+FZClCAUVA09uSmRHBCKAECFEhW51ht6rnmWBXkaR+NjuHpJ40D3DmnQXt2F+ihZxlqVKOfQRACACH5BAUKABwALAcABADOAAsAAAX/ICdyUCkUo/g8mUG8MCGkKgspeC6j6XEIEBpBUeCNfECaglBcOVfJFK7YQwZHQ6JRZBUqTrSuVEuD3nI45pYjFuWKvjjSkCoRaBUMWxkwBGgJCXspQ36Bh4EEB0oKhoiBgyNLjo8Ki4QElIiWfJqHnISNEI+Ql5J9o6SgkqKkgqYihamPkW6oNBgSfiMMDQkGCBLCwxIQDhHIyQwQCGMKxsnKVyPCF9DREQ3MxMPX0cu4wt7J2uHWx9jlKd3o39MiuefYEcvNkuLt5O8c1ePI2tyELXGQwoGDAQf+iEC2xByDCRAjTlAgIUWCBRgCPJQ4AQBFXAs0coT40WLIjRxL/47AcHLkxIomRXL0CHPERZkpa4q4iVKiyp0tR/7kwHMkTUBBJR5dOCEBAVcKKtCAyOHpowXCpk7goABqBZdcvWploACpBKkpIJI1q5OD2rIWE0R1uTZu1LFwbWL9OlKuWb4c6+o9i3dEgw0RCGDUG9KlRw56gDY2qmCByZBaASi+TACA0TucAaTteCcy0ZuOK3N2vJlx58+LRQyY3Xm0ZsgjZg+oPQLi7dUcNXi0LOJw1pgNtB7XG6CBy+U75SYfPTSQAgZTNUDnQHt67wnbZyvwLgKiMN3oCZB3C76tdewpLFgIP2C88rbi4Y+QT3+8S5USMICZXWj1pkEDeUU3lOYGB3alSoEiMIjgX4WlgNF2EibIwQIXauWXSRg2SAOHIU5IIIMoZkhhWiJaiFVbKo6AQEgQXrTAazO1JhkBrBG3Y2Y6EsUhaGn95hprSN0oWpFE7rhkeaQBchGOEWnwEmc0uKWZj0LeuNV3W4Y2lZHFlQCSRjTIl8uZ+kG5HU/3sRlnTG2ytyadytnD3HrmuRcSn+0h1dycexIK1KCjYaCnjCCVqOFFJTZ5GkUUjESWaUIKU2lgCmAKKQIUjHapXRKE+t2og1VgankNYnohqKJ2CmKplso6GKz7WYCgqxeuyoF8u9IQAgA7",
            msg: null,
            msgText: "<em>Loading the next set of posts...</em>",
            selector: null,
            speed: 'fast',
            start: undefined
        },
        state: {
            isDuringAjax: false,
            isInvalidPage: false,
            isDestroyed: false,
            isDone: false, // For when it goes all the way through the archive.
            isPaused: false,
            currPage: 1
        },
        debug: false,
        behavior: undefined,
        binder: $(window), // used to cache the selector
        nextSelector: "div.navigation a:first",
        navSelector: "div.navigation",
        contentSelector: null, // rename to pageFragment
        extraScrollPx: 150,
        itemSelector: "div.post",
        animate: false,
        pathParse: undefined,
        dataType: 'html',
        appendCallback: true,
        jsonField: 'data',
        bufferPx: 40,
        errorCallback: function () { },
        infid: 0, //Instance ID
        pixelsFromNavToBottom: undefined,
        path: undefined, // Either parts of a URL as an array (e.g. ["/page/", "/"] or a function that takes in the page number and returns a URL
        prefill: false // When the document is smaller than the window, load data until the document is larger or links are exhausted
    };

    $.infinitescroll.prototype = {

        /*  
            ----------------------------
            Private methods
            ----------------------------
            */

        // Bind or unbind from scroll
        _binding: function infscr_binding(binding) {

            var instance = this,
            opts = instance.options;

            opts.v = '2.0b2.111027';

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_binding_'+opts.behavior] !== undefined) {
                this['_binding_'+opts.behavior].call(this);
                return;
            }

            if (binding !== 'bind' && binding !== 'unbind') {
                this._debug('Binding value  ' + binding + ' not valid');
                return false;
            }

            if (binding === 'unbind') {
                (this.options.binder).unbind('smartscroll.infscr.' + instance.options.infid);
            } else {
                (this.options.binder)[binding]('smartscroll.infscr.' + instance.options.infid, function () {
                    instance.scroll();
                });
            }

            this._debug('Binding', binding);
        },

        // Fundamental aspects of the plugin are initialized
        _create: function infscr_create(options, callback) {

            // Add custom options to defaults
            var opts = $.extend(true, {}, $.infinitescroll.defaults, options);
            this.options = opts;
            var $window = $(window);
            var instance = this;

            // Validate selectors
            if (!instance._validate(options)) {
                return false;
            }

            // Validate page fragment path
            var path = $(opts.nextSelector).attr('href');
            if (!path) {
                this._debug('Navigation selector not found');
                return false;
            }

            // Set the path to be a relative URL from root.
            opts.path = opts.path || this._determinepath(path);

            // contentSelector is 'page fragment' option for .load() / .ajax() calls
            opts.contentSelector = opts.contentSelector || this.element;

            // loading.selector - if we want to place the load message in a specific selector, defaulted to the contentSelector
            opts.loading.selector = opts.loading.selector || opts.contentSelector;

            // Define loading.msg
            opts.loading.msg = opts.loading.msg || $('<div id="infscr-loading"><img alt="Loading..." src="' + opts.loading.img + '" /><div>' + opts.loading.msgText + '</div></div>');

            // Preload loading.img
            (new Image()).src = opts.loading.img;

            // distance from nav links to bottom
            // computed as: height of the document + top offset of container - top offset of nav link
            if(opts.pixelsFromNavToBottom === undefined) {
                opts.pixelsFromNavToBottom = $(document).height() - $(opts.navSelector).offset().top;
            }

            var self = this;

            // determine loading.start actions
            opts.loading.start = opts.loading.start || function() {
                $(opts.navSelector).hide();
                opts.loading.msg
                .appendTo(opts.loading.selector)
                .show(opts.loading.speed, $.proxy(function() {
                    this.beginAjax(opts);
                }, self));
            };

            // determine loading.finished actions
            opts.loading.finished = opts.loading.finished || function() {
                opts.loading.msg.fadeOut(opts.loading.speed);
            };

            // callback loading
            opts.callback = function(instance,data) {
                if (!!opts.behavior && instance['_callback_'+opts.behavior] !== undefined) {
                    instance['_callback_'+opts.behavior].call($(opts.contentSelector)[0], data);
                }

                if (callback) {
                    callback.call($(opts.contentSelector)[0], data, opts);
                }

                if (opts.prefill) {
                    $window.bind("resize.infinite-scroll", instance._prefill);
                }
            };

            if (options.debug) {
                // Tell IE9 to use its built-in console
                if (Function.prototype.bind && (typeof console === 'object' || typeof console === 'function') && typeof console.log === "object") {
                    ["log","info","warn","error","assert","dir","clear","profile","profileEnd"]
                        .forEach(function (method) {
                            console[method] = this.call(console[method], console);
                        }, Function.prototype.bind);
                }
            }

            this._setup();

            // Setups the prefill method for use
            if (opts.prefill) {
                this._prefill();
            }

            // Return true to indicate successful creation
            return true;
        },

        _prefill: function infscr_prefill() {
            var instance = this;
            var $document = $(document);
            var $window = $(window);

            function needsPrefill() {
                return ($document.height() <= $window.height());
            }

            this._prefill = function() {
                if (needsPrefill()) {
                    instance.scroll();
                }

                $window.bind("resize.infinite-scroll", function() {
                    if (needsPrefill()) {
                        $window.unbind("resize.infinite-scroll");
                        instance.scroll();
                    }
                });
            };

            // Call self after setting up the new function
            this._prefill();
        },

        // Console log wrapper
        _debug: function infscr_debug() {
            if (true !== this.options.debug) {
                return;
            }

            if (typeof console !== 'undefined' && typeof console.log === 'function') {
                // Modern browsers
                // Single argument, which is a string
                if ((Array.prototype.slice.call(arguments)).length === 1 && typeof Array.prototype.slice.call(arguments)[0] === 'string') {
                    console.log( (Array.prototype.slice.call(arguments)).toString() );
                } else {
                    console.log( Array.prototype.slice.call(arguments) );
                }
            } else if (!Function.prototype.bind && typeof console !== 'undefined' && typeof console.log === 'object') {
                // IE8
                Function.prototype.call.call(console.log, console, Array.prototype.slice.call(arguments));
            }
        },

        // find the number to increment in the path.
        _determinepath: function infscr_determinepath(path) {

            var opts = this.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_determinepath_'+opts.behavior] !== undefined) {
                return this['_determinepath_'+opts.behavior].call(this,path);
            }

            if (!!opts.pathParse) {

                this._debug('pathParse manual');
                return opts.pathParse(path, this.options.state.currPage+1);

            } else if (path.match(/^(.*?)\b2\b(.*?$)/)) {
                path = path.match(/^(.*?)\b2\b(.*?$)/).slice(1);

                // if there is any 2 in the url at all.    
            } else if (path.match(/^(.*?)2(.*?$)/)) {

                // page= is used in django:
                // http://www.infinite-scroll.com/changelog/comment-page-1/#comment-127
                if (path.match(/^(.*?page=)2(\/.*|$)/)) {
                    path = path.match(/^(.*?page=)2(\/.*|$)/).slice(1);
                    return path;
                }

                path = path.match(/^(.*?)2(.*?$)/).slice(1);

            } else {

                // page= is used in drupal too but second page is page=1 not page=2:
                // thx Jerod Fritz, vladikoff
                if (path.match(/^(.*?page=)1(\/.*|$)/)) {
                    path = path.match(/^(.*?page=)1(\/.*|$)/).slice(1);
                    return path;
                } else {
                    this._debug('Sorry, we couldn\'t parse your Next (Previous Posts) URL. Verify your the css selector points to the correct A tag. If you still get this error: yell, scream, and kindly ask for help at infinite-scroll.com.');
                    // Get rid of isInvalidPage to allow permalink to state
                    opts.state.isInvalidPage = true;  //prevent it from running on this page.
                }
            }
            this._debug('determinePath', path);
            return path;

        },

        // Custom error
        _error: function infscr_error(xhr) {

            var opts = this.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_error_'+opts.behavior] !== undefined) {
                this['_error_'+opts.behavior].call(this,xhr);
                return;
            }

            if (xhr !== 'destroy' && xhr !== 'end') {
                xhr = 'unknown';
            }

            this._debug('Error', xhr);

            if (xhr === 'end') {
                this._showdonemsg();
            }

            opts.state.isDone = true;
            opts.state.currPage = 1; // if you need to go back to this instance
            opts.state.isPaused = false;
            this._binding('unbind');

        },

        // Load Callback
        _loadcallback: function infscr_loadcallback(box, data) {

            var opts = this.options,
            callback = this.options.callback, // GLOBAL OBJECT FOR CALLBACK
            result = (opts.state.isDone) ? 'done' : (!opts.appendCallback) ? 'no-append' : 'append',
            frag;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_loadcallback_'+opts.behavior] !== undefined) {
                this['_loadcallback_'+opts.behavior].call(this,box,data);
                return;
            }

            switch (result) {
                case 'done':
                    this._showdonemsg();
                    return false;

                case 'no-append':
                    if (opts.dataType === 'html') {
                        data = '<div>' + data + '</div>';
                        data = $(data).find(opts.itemSelector);
                    }
                    break;

                case 'append':
                    var children = box.children();
                    // if it didn't return anything
                    if (children.length === 0) {
                        return this._error('end');
                    }

                    // use a documentFragment because it works when content is going into a table or UL
                    frag = document.createDocumentFragment();
                    while (box[0].firstChild) {
                        frag.appendChild(box[0].firstChild);
                    }

                    this._debug('contentSelector', $(opts.contentSelector)[0]);
                    $(opts.contentSelector)[0].appendChild(frag);
                    // previously, we would pass in the new DOM element as context for the callback
                    // however we're now using a documentfragment, which doesn't have parents or children,
                    // so the context is the contentContainer guy, and we pass in an array
                    // of the elements collected as the first argument.

                    data = children.get();
                    break;
            }

            // loadingEnd function
            opts.loading.finished.call($(opts.contentSelector)[0],opts);

            // smooth scroll to ease in the new content
            if (opts.animate) {
                var scrollTo = $(window).scrollTop() + $('#infscr-loading').height() + opts.extraScrollPx + 'px';
                $('html,body').animate({ scrollTop: scrollTo }, 800, function () { opts.state.isDuringAjax = false; });
            }

            if (!opts.animate) {
                // once the call is done, we can allow it again.
                opts.state.isDuringAjax = false;
            }

            callback(this,data);
        },

        _nearbottom: function infscr_nearbottom() {

            var opts = this.options,
            pixelsFromWindowBottomToBottom = 0 + $(document).height() - (opts.binder.scrollTop()) - $(window).height();

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_nearbottom_'+opts.behavior] !== undefined) {
                return this['_nearbottom_'+opts.behavior].call(this);
            }

            this._debug('math:', pixelsFromWindowBottomToBottom, opts.pixelsFromNavToBottom);

            // if distance remaining in the scroll (including buffer) is less than the orignal nav to bottom....
            return (pixelsFromWindowBottomToBottom - opts.bufferPx < opts.pixelsFromNavToBottom);

        },

        // Pause / temporarily disable plugin from firing
        _pausing: function infscr_pausing(pause) {

            var opts = this.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_pausing_'+opts.behavior] !== undefined) {
                this['_pausing_'+opts.behavior].call(this,pause);
                return;
            }

            // If pause is not 'pause' or 'resume', toggle it's value
            if (pause !== 'pause' && pause !== 'resume' && pause !== null) {
                this._debug('Invalid argument. Toggling pause value instead');
            }

            pause = (pause && (pause === 'pause' || pause === 'resume')) ? pause : 'toggle';

            switch (pause) {
                case 'pause':
                    opts.state.isPaused = true;
                break;

                case 'resume':
                    opts.state.isPaused = false;
                break;

                case 'toggle':
                    opts.state.isPaused = !opts.state.isPaused;
                break;
            }

            this._debug('Paused', opts.state.isPaused);
            return false;

        },

        // Behavior is determined
        // If the behavior option is undefined, it will set to default and bind to scroll
        _setup: function infscr_setup() {

            var opts = this.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_setup_'+opts.behavior] !== undefined) {
                this['_setup_'+opts.behavior].call(this);
                return;
            }

            this._binding('bind');

            return false;

        },

        // Show done message
        _showdonemsg: function infscr_showdonemsg() {

            var opts = this.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['_showdonemsg_'+opts.behavior] !== undefined) {
                this['_showdonemsg_'+opts.behavior].call(this);
                return;
            }

            opts.loading.msg
            .find('img')
            .hide()
            .parent()
            .find('div').html(opts.loading.finishedMsg).animate({ opacity: 1 }, 2000, function () {
                $(this).parent().fadeOut(opts.loading.speed);
            });

            // user provided callback when done    
            opts.errorCallback.call($(opts.contentSelector)[0],'done');
        },

        // grab each selector option and see if any fail
        _validate: function infscr_validate(opts) {
            for (var key in opts) {
                if (key.indexOf && key.indexOf('Selector') > -1 && $(opts[key]).length === 0) {
                    this._debug('Your ' + key + ' found no elements.');
                    return false;
                }
            }

            return true;
        },

        /*  
            ----------------------------
            Public methods
            ----------------------------
            */

        // Bind to scroll
        bind: function infscr_bind() {
            this._binding('bind');
        },

        // Destroy current instance of plugin
        destroy: function infscr_destroy() {

            this.options.state.isDestroyed = true;
            return this._error('destroy');

        },

        // Set pause value to false
        pause: function infscr_pause() {
            this._pausing('pause');
        },

        // Set pause value to false
        resume: function infscr_resume() {
            this._pausing('resume');
        },

        beginAjax: function infscr_ajax(opts) {
            var instance = this,
                path = opts.path,
                box, desturl, method, condition;

            // increment the URL bit. e.g. /page/3/
            opts.state.currPage++;

            // if we're dealing with a table we can't use DIVs
            box = $(opts.contentSelector).is('table') ? $('<tbody/>') : $('<div/>');

            desturl = (typeof path === 'function') ? path(opts.state.currPage) : path.join(opts.state.currPage);
            instance._debug('heading into ajax', desturl);

            // ------ changed code for Koemei ------ //

            var xhr = opts.xhr,
                xhrFields = opts.xhrFields;
            xhrFields.url = desturl;

            var field = opts.jsonField;
            instance._debug('Using JSON via $.ajax() method');

            xhr.request(xhrFields, function(response){
                var json = $.parseJSON(response.data);
                var rResultCount = json[field].length;
                if (rResultCount > 0) {
                    instance._loadcallback(box, json);
                } else {
                    instance._error('end');
                }
            }, function(response){
                instance._debug("JSON ajax request failed.");
                instance._error('end');
            });

            /*$.ajax({
                dataType: 'json',
                type: 'GET',
                url: desturl,
                success: function (data, textStatus, jqXHR) {
                    condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === "success" || textStatus === "notmodified");
                    // modified to allow end of deals detection
                    var rResultCount =  ($.parseJSON(jqXHR.responseText))[field].length;
                    if (opts.appendCallback) {
                        // if appendCallback is true, you must defined template in options.
                        // note that data passed into _loadcallback is already an html (after processed in opts.template(data)).
                        if (opts.template !== undefined) {
                            var theData = opts.template(data);
                            box.append(theData);
                            if (condition && (rResultCount > 0)) {
                                instance._loadcallback(box, theData);
                            } else {
                                instance._error('end');
                            }
                        } else {
                            instance._debug("template must be defined.");
                            instance._error('end');
                        }
                    } else {
                        // if appendCallback is false, we will pass in the JSON object. you should handle it yourself in your callback.
                        if (condition && (rResultCount > 0)) {
                            instance._loadcallback(box, data);
                        } else {
                            instance._error('end');
                        }
                    }
                },
                error: function() {
                    instance._debug("JSON ajax request failed.");
                    instance._error('end');
                }
            });*/
        },

        // Retrieve next set of content items
        retrieve: function infscr_retrieve(pageNum) {
            pageNum = pageNum || null;

            var instance = this,
            opts = instance.options;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['retrieve_'+opts.behavior] !== undefined) {
                this['retrieve_'+opts.behavior].call(this,pageNum);
                return;
            }

            // for manual triggers, if destroyed, get out of here
            if (opts.state.isDestroyed) {
                this._debug('Instance is destroyed');
                return false;
            }

            // we dont want to fire the ajax multiple times
            opts.state.isDuringAjax = true;

            opts.loading.start.call($(opts.contentSelector)[0],opts);
        },

        // Check to see next page is needed
        scroll: function infscr_scroll() {

            var opts = this.options,
            state = opts.state;

            // if behavior is defined and this function is extended, call that instead of default
            if (!!opts.behavior && this['scroll_'+opts.behavior] !== undefined) {
                this['scroll_'+opts.behavior].call(this);
                return;
            }

            if (state.isDuringAjax || state.isInvalidPage || state.isDone || state.isDestroyed || state.isPaused) {
                return;
            }

            if (!this._nearbottom()) {
                return;
            }

            this.retrieve();

        },

        // Toggle pause value
        toggle: function infscr_toggle() {
            this._pausing();
        },

        // Unbind from scroll
        unbind: function infscr_unbind() {
            this._binding('unbind');
        },

        // update options
        update: function infscr_options(key) {
            if ($.isPlainObject(key)) {
                this.options = $.extend(true,this.options,key);
            }
        }
    };


    /*  
        ----------------------------
        Infinite Scroll function
        ----------------------------

        Borrowed logic from the following...

        jQuery UI
        - https://github.com/jquery/jquery-ui/blob/master/ui/jquery.ui.widget.js

        jCarousel
        - https://github.com/jsor/jcarousel/blob/master/lib/jquery.jcarousel.js

        Masonry
        - https://github.com/desandro/masonry/blob/master/jquery.masonry.js     

*/

    $.fn.infinitescroll = function infscr_init(options, callback) {


        var thisCall = typeof options;

        switch (thisCall) {

            // method 
            case 'string':
                var args = Array.prototype.slice.call(arguments, 1);

                this.each(function () {
                    var instance = $.data(this, 'infinitescroll');

                    if (!instance) {
                        // not setup yet
                        // return $.error('Method ' + options + ' cannot be called until Infinite Scroll is setup');
                        return false;
                    }

                    if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                        // return $.error('No such method ' + options + ' for Infinite Scroll');
                        return false;
                    }

                    // no errors!
                    instance[options].apply(instance, args);
                });

            break;

            // creation 
            case 'object':

                this.each(function () {

                var instance = $.data(this, 'infinitescroll');

                if (instance) {

                    // update options of current instance
                    instance.update(options);

                } else {

                    // initialize new instance
                    instance = new $.infinitescroll(options, callback, this);

                    // don't attach if instantiation failed
                    if (!instance.failed) {
                        $.data(this, 'infinitescroll', instance);
                    }

                }

            });

            break;

        }

        return this;
    };



    /* 
     * smartscroll: debounced scroll event for jQuery *
     * https://github.com/lukeshumard/smartscroll
     * Based on smartresize by @louis_remi: https://github.com/lrbabe/jquery.smartresize.js *
     * Copyright 2011 Louis-Remi & Luke Shumard * Licensed under the MIT license. *
     */

    var event = $.event,
    scrollTimeout;

    event.special.smartscroll = {
        setup: function () {
            $(this).bind("scroll", event.special.smartscroll.handler);
        },
        teardown: function () {
            $(this).unbind("scroll", event.special.smartscroll.handler);
        },
        handler: function (event, execAsap) {
            // Save the context
            var context = this,
            args = arguments;

            // set correct event type
            event.type = "smartscroll";

            if (scrollTimeout) { clearTimeout(scrollTimeout); }
            scrollTimeout = setTimeout(function () {
                $.event.handle.apply(context, args);
            }, execAsap === "execAsap" ? 0 : 100);
        }
    };

    $.fn.smartscroll = function (fn) {
        return fn ? this.bind("smartscroll", fn) : this.trigger("smartscroll", ["execAsap"]);
    };


})(window, jQuery);

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(C($){8($.1r.1v){G}$.1r.6p=$.1r.1v=C(u,w){8(1k.S==0){17(I,\'6q 55 6r 1j "\'+1k.4n+\'".\');G 1k}8(1k.S>1){G 1k.1W(C(){$(1k).1v(u,w)})}E y=1k,$13=1k[0],56=K;8(y.1m(\'57\')){56=y.1Q(\'3o\',\'4o\');y.R(\'3o\',[\'4p\',I])}y.59=C(o,a,b){o=3T($13,o);o.D=6s($13,o.D);o.1M=6t($13,o.1M);o.M=6u($13,o.M);o.V=5a($13,o.V);o.Y=5a($13,o.Y);o.1a=6v($13,o.1a);o.1q=6w($13,o.1q);o.1h=6x($13,o.1h);8(a){31=$.1N(I,{},$.1r.1v.5b,o)}7=$.1N(I,{},$.1r.1v.5b,o);7.d=6y(7);z.2b=(7.2b==\'4q\'||7.2b==\'1n\')?\'Y\':\'V\';E c=y.14(),2w=5c($1s,7,\'N\');8(3p(7.25)){7.25=\'7Q\'+F.3U}7.4r=5d(7,2w);7.D=6z(7.D,7,c,b);7[7.d[\'N\']]=6A(7[7.d[\'N\']],7,c);7[7.d[\'1d\']]=6B(7[7.d[\'1d\']],7,c);8(7.2m){8(!3V(7[7.d[\'N\']])){7[7.d[\'N\']]=\'2J%\'}}8(3V(7[7.d[\'N\']])){z.6C=I;z.4s=7[7.d[\'N\']];7[7.d[\'N\']]=4t(2w,z.4s);8(!7.D.L){7.D.T.1c=I}}8(7.2m){7.1R=K;7.1i=[0,0,0,0];7.1A=K;7.D.T.1c=K}O{8(!7.D.L){7=6D(7,2w)}8(!7[7.d[\'N\']]){8(!7.D.T.1c&&Z(7.D[7.d[\'N\']])&&7.D.1t==\'*\'){7[7.d[\'N\']]=7.D.L*7.D[7.d[\'N\']];7.1A=K}O{7[7.d[\'N\']]=\'1c\'}}8(1G(7.1A)){7.1A=(Z(7[7.d[\'N\']]))?\'5e\':K}8(7.D.T.1c){7.D.L=32(c,7,0)}}8(7.D.1t!=\'*\'&&!7.D.T.1c){7.D.T.4u=7.D.L;7.D.L=3W(c,7,0)}7.D.L=2x(7.D.L,7,7.D.T.2c,$13);7.D.T.1Z=7.D.L;8(7.2m){8(!7.D.T.34){7.D.T.34=7.D.L}8(!7.D.T.1X){7.D.T.1X=7.D.L}7=5f(7,c,2w)}O{7.1i=6E(7.1i);8(7.1A==\'3q\'){7.1A=\'1n\'}O 8(7.1A==\'5g\'){7.1A=\'35\'}1B(7.1A){Q\'5e\':Q\'1n\':Q\'35\':8(7[7.d[\'N\']]!=\'1c\'){7=5h(7,c);7.1R=I}16;2y:7.1A=K;7.1R=(7.1i[0]==0&&7.1i[1]==0&&7.1i[2]==0&&7.1i[3]==0)?K:I;16}}8(!Z(7.1M.1C)){7.1M.1C=6F}8(1G(7.1M.D)){7.1M.D=(7.2m||7.D.T.1c||7.D.1t!=\'*\')?\'L\':7.D.L}7.M=$.1N(I,{},7.1M,7.M);7.V=$.1N(I,{},7.1M,7.V);7.Y=$.1N(I,{},7.1M,7.Y);7.1a=$.1N(I,{},7.1M,7.1a);7.M=6G($13,7.M);7.V=5i($13,7.V);7.Y=5i($13,7.Y);7.1a=6H($13,7.1a);7.1q=6I($13,7.1q);7.1h=6J($13,7.1h);8(7.2n){7.2n=5j(7.2n)}8(7.M.5k){7.M.4v=7.M.5k;2K(\'M.5k\',\'M.4v\')}8(7.M.5l){7.M.4w=7.M.5l;2K(\'M.5l\',\'M.4w\')}8(7.M.5m){7.M.4x=7.M.5m;2K(\'M.5m\',\'M.4x\')}8(7.M.5n){7.M.2L=7.M.5n;2K(\'M.5n\',\'M.2L\')}};y.6K=C(){y.1m(\'57\',I);E a=y.14(),3X=5o(y,[\'6L\',\'6M\',\'3r\',\'3q\',\'35\',\'5g\',\'1n\',\'3Y\',\'N\',\'1d\',\'6N\',\'1S\',\'5p\',\'6O\']),5q=\'7R\';1B(3X.3r){Q\'6P\':Q\'7S\':5q=3X.3r;16}$1s.X(3X).X({\'7T\':\'3s\',\'3r\':5q});y.1m(\'5r\',3X).X({\'6L\':\'1n\',\'6M\':\'3Z\',\'3r\':\'6P\',\'3q\':0,\'35\':\'M\',\'5g\':\'M\',\'1n\':0,\'6N\':0,\'1S\':0,\'5p\':0,\'6O\':0});4y(a,7);5s(a,7);8(7.2m){5t(7,a)}};y.6Q=C(){y.5u();y.12(H(\'5v\',F),C(e,a){e.1f();8(!z.2d){8(7.M.W){7.M.W.36(2z(\'4z\',F))}}z.2d=I;8(7.M.1H){7.M.1H=K;y.R(H(\'3a\',F),a)}G I});y.12(H(\'5w\',F),C(e){e.1f();8(z.20){41(U)}G I});y.12(H(\'3a\',F),C(e,a,b){e.1f();1u=3t(1u);8(a&&z.20){U.2d=I;E c=2o()-U.2M;U.1C-=c;8(U.3u){U.3u.1C-=c}8(U.3v){U.3v.1C-=c}41(U,K)}8(!z.26&&!z.20){8(b){1u.3w+=2o()-1u.2M}}8(!z.26){8(7.M.W){7.M.W.36(2z(\'6R\',F))}}z.26=I;8(7.M.4w){E d=7.M.2L-1u.3w,3b=2J-1I.2A(d*2J/7.M.2L);7.M.4w.1g($13,3b,d)}G I});y.12(H(\'1H\',F),C(e,b,c,d){e.1f();1u=3t(1u);E v=[b,c,d],t=[\'2N\',\'27\',\'3c\'],a=3d(v,t);b=a[0];c=a[1];d=a[2];8(b!=\'V\'&&b!=\'Y\'){b=z.2b}8(!Z(c)){c=0}8(!1l(d)){d=K}8(d){z.2d=K;7.M.1H=I}8(!7.M.1H){e.2e();G 17(F,\'3x 4z: 2p 3e.\')}8(z.26){8(7.M.W){7.M.W.2O(2z(\'4z\',F));7.M.W.2O(2z(\'6R\',F))}}z.26=K;1u.2M=2o();E f=7.M.2L+c;42=f-1u.3w;3b=2J-1I.2A(42*2J/f);8(7.M.1e){1u.1e=7U(C(){E a=2o()-1u.2M+1u.3w,3b=1I.2A(a*2J/f);7.M.1e.4A.1g(7.M.1e.2q[0],3b)},7.M.1e.5x)}1u.M=7V(C(){8(7.M.1e){7.M.1e.4A.1g(7.M.1e.2q[0],2J)}8(7.M.4x){7.M.4x.1g($13,3b,42)}8(z.20){y.R(H(\'1H\',F),b)}O{y.R(H(b,F),7.M)}},42);8(7.M.4v){7.M.4v.1g($13,3b,42)}G I});y.12(H(\'3f\',F),C(e){e.1f();8(U.2d){U.2d=K;z.26=K;z.20=I;U.2M=2o();2P(U)}O{y.R(H(\'1H\',F))}G I});y.12(H(\'V\',F)+\' \'+H(\'Y\',F),C(e,b,f,g,h){e.1f();8(z.2d||y.2f(\':3s\')){e.2e();G 17(F,\'3x 4z 7W 3s: 2p 3e.\')}E i=(Z(7.D.4B))?7.D.4B:7.D.L+1;8(i>J.P){e.2e();G 17(F,\'2p 6S D (\'+J.P+\' P, \'+i+\' 6T): 2p 3e.\')}E v=[b,f,g,h],t=[\'2g\',\'27/2N\',\'C\',\'3c\'],a=3d(v,t);b=a[0];f=a[1];g=a[2];h=a[3];E k=e.5y.18(F.3y.43.S);8(!1D(b)){b={}}8(1o(g)){b.3g=g}8(1l(h)){b.3z=h}b=$.1N(I,{},7[k],b);8(b.5z&&!b.5z.1g($13,k)){e.2e();G 17(F,\'7X "5z" 7Y K.\')}8(!Z(f)){8(7.D.1t!=\'*\'){f=\'L\'}O{E m=[f,b.D,7[k].D];1j(E a=0,l=m.S;a<l;a++){8(Z(m[a])||m[a]==\'6U\'||m[a]==\'L\'){f=m[a];16}}}1B(f){Q\'6U\':e.2e();G y.1Q(H(k+\'7Z\',F),[b,g]);16;Q\'L\':8(!7.D.T.1c&&7.D.1t==\'*\'){f=7.D.L}16}}8(U.2d){y.R(H(\'3f\',F));y.R(H(\'3z\',F),[k,[b,f,g]]);e.2e();G 17(F,\'3x 80 3e.\')}8(b.1C>0){8(z.20){8(b.3z){y.R(H(\'3z\',F),[k,[b,f,g]])}e.2e();G 17(F,\'3x 81 3e.\')}}1u.3w=0;y.R(H(\'6V\'+k,F),[b,f]);8(7.2n){E s=7.2n,c=[b,f];1j(E j=0,l=s.S;j<l;j++){E d=k;8(!s[j][2]){d=(d==\'V\')?\'Y\':\'V\'}8(!s[j][1]){c[0]=s[j][0].1Q(\'3o\',[\'4C\',d])}c[1]=f+s[j][3];s[j][0].R(\'3o\',[\'6V\'+d,c])}}G I});y.12(H(\'82\',F),C(e,b,c){e.1f();E d=y.14();8(!7.1T){8(J.11==0){8(7.3A){y.R(H(\'Y\',F),J.P-1)}G e.2e()}}1U(d,7);8(!Z(c)){8(7.D.T.1c){c=4D(d,7,J.P-1)}O 8(7.D.1t!=\'*\'){E f=(Z(b.D))?b.D:5A(y,7);c=6W(d,7,J.P-1,f)}O{c=7.D.L}c=4E(c,7,b.D,$13)}8(!7.1T){8(J.P-c<J.11){c=J.P-J.11}}7.D.T.1Z=7.D.L;8(7.D.T.1c){E g=2x(32(d,7,J.P-c),7,7.D.T.2c,$13);8(7.D.L+c<=g&&c<J.P){c++;g=2x(32(d,7,J.P-c),7,7.D.T.2c,$13)}7.D.L=g}O 8(7.D.1t!=\'*\'){E g=3W(d,7,J.P-c);7.D.L=2x(g,7,7.D.T.2c,$13)}1U(d,7,I);8(c==0){e.2e();G 17(F,\'0 D 44 1M: 2p 3e.\')}17(F,\'6X \'+c+\' D 5B.\');J.11+=c;2h(J.11>=J.P){J.11-=J.P}8(!7.1T){8(J.11==0&&b.4F){b.4F.1g($13,\'V\')}8(!7.3A){3B(7,J.11,F)}}y.14().18(J.P-c,J.P).83(y);8(J.P<7.D.L+c){y.14().18(0,(7.D.L+c)-J.P).4G(I).45(y)}E d=y.14(),3h=6Y(d,7,c),2i=6Z(d,7),1Y=d.1O(c-1),21=3h.3i(),2r=2i.3i();1U(d,7);E h=0,2B=0;8(7.1A){E p=4H(2i,7);h=p[0];2B=p[1]}E i=(h<0)?7.1i[7.d[3]]:0;E j=K,2Q=$();8(7.D.L<c){2Q=d.18(7.D.T.1Z,c);8(b.1V==\'70\'){E k=7.D[7.d[\'N\']];j=2Q;1Y=2r;5C(j);7.D[7.d[\'N\']]=\'1c\'}}E l=K,3C=2R(d.18(0,c),7,\'N\'),2j=4I(4J(2i,7,I),7,!7.1R),3D=0,28={},4K={},2s={},2S={},4L={},2T={},5D={},2U=5E(b,7,c,3C);1B(b.1V){Q\'1J\':Q\'1J-1w\':3D=2R(d.18(0,7.D.L),7,\'N\');16}8(j){7.D[7.d[\'N\']]=k}1U(d,7,I);8(2B>=0){1U(21,7,7.1i[7.d[1]])}8(h>=0){1U(1Y,7,7.1i[7.d[3]])}8(7.1A){7.1i[7.d[1]]=2B;7.1i[7.d[3]]=h}2T[7.d[\'1n\']]=-(3C-i);5D[7.d[\'1n\']]=-(3D-i);4K[7.d[\'1n\']]=2j[7.d[\'N\']];E m=C(){},1P=C(){},1E=C(){},3E=C(){},2C=C(){},5F=C(){},1F=C(){},3F=C(){},1x=C(){},1y=C(){},1K=C(){};1B(b.1V){Q\'3j\':Q\'1J\':Q\'1J-1w\':Q\'22\':Q\'22-1w\':l=y.4G(I).45($1s);16}1B(b.1V){Q\'3j\':Q\'22\':Q\'22-1w\':l.14().18(0,c).2t();l.14().18(7.D.T.1Z).2t();16;Q\'1J\':Q\'1J-1w\':l.14().18(7.D.L).2t();l.X(5D);16}y.X(2T);U=46(2U,b.2k);28[7.d[\'1n\']]=(7.1R)?7.1i[7.d[3]]:0;8(7[7.d[\'N\']]==\'1c\'||7[7.d[\'1d\']]==\'1c\'){m=C(){$1s.X(2j)};1P=C(){U.19.1b([$1s,2j])}}8(7.1R){8(2r.4M(1Y).S){2s[7.d[\'1S\']]=1Y.1m(\'29\');8(h<0){1Y.X(2s)}O{1F=C(){1Y.X(2s)};3F=C(){U.19.1b([1Y,2s])}}}1B(b.1V){Q\'1J\':Q\'1J-1w\':l.14().1O(c-1).X(2s);16}8(2r.4M(21).S){2S[7.d[\'1S\']]=21.1m(\'29\');1E=C(){21.X(2S)};3E=C(){U.19.1b([21,2S])}}8(2B>=0){4L[7.d[\'1S\']]=2r.1m(\'29\')+7.1i[7.d[1]];2C=C(){2r.X(4L)};5F=C(){U.19.1b([2r,4L])}}}1K=C(){y.X(28)};E n=7.D.L+c-J.P;1y=C(){8(n>0){y.14().18(J.P).2t();3h=$(y.14().18(J.P-(7.D.L-n)).3G().71(y.14().18(0,n).3G()))}5G(j);8(7.1R){E a=y.14().1O(7.D.L+c-1);a.X(7.d[\'1S\'],a.1m(\'29\'))}};E o=5H(3h,2Q,2i,c,\'V\',2U,2j);1x=C(){5I(y,l,b);z.20=K;2a.3g=47($13,b,\'3g\',o,2a);2D=5J(y,2D,F);8(!z.26){y.R(H(\'1H\',F))}};z.20=I;1u=3t(1u);2a.3H=47($13,b,\'3H\',o,2a);1B(b.1V){Q\'3Z\':y.X(28);m();1E();2C();1F();1K();1y();1x();16;Q\'1w\':U.19.1b([y,{\'1L\':0},C(){m();1E();2C();1F();1K();1y();U=46(2U,b.2k);U.19.1b([y,{\'1L\':1},1x]);2P(U)}]);16;Q\'3j\':y.X({\'1L\':0});U.19.1b([l,{\'1L\':0}]);U.19.1b([y,{\'1L\':1},1x]);1P();1E();2C();1F();1K();1y();16;Q\'1J\':U.19.1b([l,28,C(){1E();2C();1F();1K();1y();1x()}]);1P();16;Q\'1J-1w\':U.19.1b([y,{\'1L\':0}]);U.19.1b([l,28,C(){y.X({\'1L\':1});1E();2C();1F();1K();1y();1x()}]);1P();16;Q\'22\':U.19.1b([l,4K,1x]);1P();1E();2C();1F();1K();1y();16;Q\'22-1w\':y.X({\'1L\':0});U.19.1b([y,{\'1L\':1}]);U.19.1b([l,4K,1x]);1P();1E();2C();1F();1K();1y();16;2y:U.19.1b([y,28,C(){1y();1x()}]);1P();3E();5F();3F();16}2P(U);5K(7.25,y,F);y.R(H(\'3I\',F),[K,2j]);G I});y.12(H(\'84\',F),C(e,c,d){e.1f();E f=y.14();8(!7.1T){8(J.11==7.D.L){8(7.3A){y.R(H(\'V\',F),J.P-1)}G e.2e()}}1U(f,7);8(!Z(d)){8(7.D.1t!=\'*\'){E g=(Z(c.D))?c.D:5A(y,7);d=72(f,7,0,g)}O{d=7.D.L}d=4E(d,7,c.D,$13)}E h=(J.11==0)?J.P:J.11;8(!7.1T){8(7.D.T.1c){E i=32(f,7,d),g=4D(f,7,h-1)}O{E i=7.D.L,g=7.D.L}8(d+i>h){d=h-g}}7.D.T.1Z=7.D.L;8(7.D.T.1c){E i=2x(5L(f,7,d,h),7,7.D.T.2c,$13);2h(7.D.L-d>=i&&d<J.P){d++;i=2x(5L(f,7,d,h),7,7.D.T.2c,$13)}7.D.L=i}O 8(7.D.1t!=\'*\'){E i=3W(f,7,d);7.D.L=2x(i,7,7.D.T.2c,$13)}1U(f,7,I);8(d==0){e.2e();G 17(F,\'0 D 44 1M: 2p 3e.\')}17(F,\'6X \'+d+\' D 73.\');J.11-=d;2h(J.11<0){J.11+=J.P}8(!7.1T){8(J.11==7.D.L&&c.4F){c.4F.1g($13,\'Y\')}8(!7.3A){3B(7,J.11,F)}}8(J.P<7.D.L+d){y.14().18(0,(7.D.L+d)-J.P).4G(I).45(y)}E f=y.14(),3h=74(f,7),2i=75(f,7,d),1Y=f.1O(d-1),21=3h.3i(),2r=2i.3i();1U(f,7);E j=0,2B=0;8(7.1A){E p=4H(2i,7);j=p[0];2B=p[1]}E k=K,2Q=$();8(7.D.T.1Z<d){2Q=f.18(7.D.T.1Z,d);8(c.1V==\'70\'){E l=7.D[7.d[\'N\']];k=2Q;1Y=21;5C(k);7.D[7.d[\'N\']]=\'1c\'}}E m=K,3C=2R(f.18(0,d),7,\'N\'),2j=4I(4J(2i,7,I),7,!7.1R),3D=0,28={},4N={},2s={},2S={},2T={},2U=5E(c,7,d,3C);1B(c.1V){Q\'22\':Q\'22-1w\':3D=2R(f.18(0,7.D.T.1Z),7,\'N\');16}8(k){7.D[7.d[\'N\']]=l}8(7.1A){8(7.1i[7.d[1]]<0){7.1i[7.d[1]]=0}}1U(f,7,I);1U(21,7,7.1i[7.d[1]]);8(7.1A){7.1i[7.d[1]]=2B;7.1i[7.d[3]]=j}2T[7.d[\'1n\']]=(7.1R)?7.1i[7.d[3]]:0;E n=C(){},1P=C(){},1E=C(){},3E=C(){},1F=C(){},3F=C(){},1x=C(){},1y=C(){},1K=C(){};1B(c.1V){Q\'3j\':Q\'1J\':Q\'1J-1w\':Q\'22\':Q\'22-1w\':m=y.4G(I).45($1s);m.14().18(7.D.T.1Z).2t();16}1B(c.1V){Q\'3j\':Q\'1J\':Q\'1J-1w\':y.X(\'3Y\',1);m.X(\'3Y\',0);16}U=46(2U,c.2k);28[7.d[\'1n\']]=-3C;4N[7.d[\'1n\']]=-3D;8(j<0){28[7.d[\'1n\']]+=j}8(7[7.d[\'N\']]==\'1c\'||7[7.d[\'1d\']]==\'1c\'){n=C(){$1s.X(2j)};1P=C(){U.19.1b([$1s,2j])}}8(7.1R){E o=2r.1m(\'29\');8(2B>=0){o+=7.1i[7.d[1]]}2r.X(7.d[\'1S\'],o);8(1Y.4M(21).S){2S[7.d[\'1S\']]=21.1m(\'29\')}1E=C(){21.X(2S)};3E=C(){U.19.1b([21,2S])};E q=1Y.1m(\'29\');8(j>0){q+=7.1i[7.d[3]]}2s[7.d[\'1S\']]=q;1F=C(){1Y.X(2s)};3F=C(){U.19.1b([1Y,2s])}}1K=C(){y.X(2T)};E r=7.D.L+d-J.P;1y=C(){8(r>0){y.14().18(J.P).2t()}E a=y.14().18(0,d).45(y).3i();8(r>0){2i=3J(f,7)}5G(k);8(7.1R){8(J.P<7.D.L+d){E b=y.14().1O(7.D.L-1);b.X(7.d[\'1S\'],b.1m(\'29\')+7.1i[7.d[3]])}a.X(7.d[\'1S\'],a.1m(\'29\'))}};E s=5H(3h,2Q,2i,d,\'Y\',2U,2j);1x=C(){y.X(\'3Y\',y.1m(\'5r\').3Y);5I(y,m,c);z.20=K;2a.3g=47($13,c,\'3g\',s,2a);2D=5J(y,2D,F);8(!z.26){y.R(H(\'1H\',F))}};z.20=I;1u=3t(1u);2a.3H=47($13,c,\'3H\',s,2a);1B(c.1V){Q\'3Z\':y.X(28);n();1E();1F();1K();1y();1x();16;Q\'1w\':U.19.1b([y,{\'1L\':0},C(){n();1E();1F();1K();1y();U=46(2U,c.2k);U.19.1b([y,{\'1L\':1},1x]);2P(U)}]);16;Q\'3j\':y.X({\'1L\':0});U.19.1b([m,{\'1L\':0}]);U.19.1b([y,{\'1L\':1},1x]);1P();1E();1F();1K();1y();16;Q\'1J\':y.X(7.d[\'1n\'],$1s[7.d[\'N\']]());U.19.1b([y,2T,1x]);1P();1E();1F();1y();16;Q\'1J-1w\':y.X(7.d[\'1n\'],$1s[7.d[\'N\']]());U.19.1b([m,{\'1L\':0}]);U.19.1b([y,2T,1x]);1P();1E();1F();1y();16;Q\'22\':U.19.1b([m,4N,1x]);1P();1E();1F();1K();1y();16;Q\'22-1w\':y.X({\'1L\':0});U.19.1b([y,{\'1L\':1}]);U.19.1b([m,4N,1x]);1P();1E();1F();1K();1y();16;2y:U.19.1b([y,28,C(){1K();1y();1x()}]);1P();3E();3F();16}2P(U);5K(7.25,y,F);y.R(H(\'3I\',F),[K,2j]);G I});y.12(H(\'3k\',F),C(e,b,c,d,f,g,h){e.1f();E v=[b,c,d,f,g,h],t=[\'2N/27/2g\',\'27\',\'3c\',\'2g\',\'2N\',\'C\'],a=3d(v,t);f=a[3];g=a[4];h=a[5];b=3K(a[0],a[1],a[2],J,y);8(b==0){G K}8(!1D(f)){f=K}8(z.20){8(!1D(f)||f.1C>0){G K}}8(g!=\'V\'&&g!=\'Y\'){8(7.1T){g=(b<=J.P/2)?\'Y\':\'V\'}O{g=(J.11==0||J.11>b)?\'Y\':\'V\'}}8(g==\'V\'){b=J.P-b}y.R(H(g,F),[f,b,h]);G I});y.12(H(\'85\',F),C(e,a,b){e.1f();E c=y.1Q(H(\'48\',F));G y.1Q(H(\'5M\',F),[c-1,a,\'V\',b])});y.12(H(\'86\',F),C(e,a,b){e.1f();E c=y.1Q(H(\'48\',F));G y.1Q(H(\'5M\',F),[c+1,a,\'Y\',b])});y.12(H(\'5M\',F),C(e,a,b,c,d){e.1f();8(!Z(a)){a=y.1Q(H(\'48\',F))}E f=7.1a.D||7.D.L,1X=1I.2A(J.P/f)-1;8(a<0){a=1X}8(a>1X){a=0}G y.1Q(H(\'3k\',F),[a*f,0,I,b,c,d])});y.12(H(\'76\',F),C(e,s){e.1f();8(s){s=3K(s,0,I,J,y)}O{s=0}s+=J.11;8(s!=0){8(D.P>0){2h(s>J.P){s-=J.P}}y.87(y.14().18(s,J.P))}G I});y.12(H(\'2n\',F),C(e,s){e.1f();8(s){s=5j(s)}O 8(7.2n){s=7.2n}O{G 17(F,\'6q 88 44 2n.\')}E n=y.1Q(H(\'4o\',F)),x=I;1j(E j=0,l=s.S;j<l;j++){8(!s[j][0].1Q(H(\'3k\',F),[n,s[j][3],I])){x=K}}G x});y.12(H(\'3z\',F),C(e,a,b){e.1f();8(1o(a)){a.1g($13,2D)}O 8(2V(a)){2D=a}O 8(!1G(a)){2D.1b([a,b])}G 2D});y.12(H(\'89\',F),C(e,b,c,d,f){e.1f();E v=[b,c,d,f],t=[\'2N/2g\',\'2N/27/2g\',\'3c\',\'27\'],a=3d(v,t);b=a[0];c=a[1];d=a[2];f=a[3];8(1D(b)&&!2u(b)){b=$(b)}O 8(1p(b)){b=$(b)}8(!2u(b)||b.S==0){G 17(F,\'2p a 5N 2g.\')}8(1G(c)){c=\'4a\'}4y(b,7);5s(b,7);E g=c,4b=\'4b\';8(c==\'4a\'){8(d){8(J.11==0){c=J.P-1;4b=\'77\'}O{c=J.11;J.11+=b.S}8(c<0){c=0}}O{c=J.P-1;4b=\'77\'}}O{c=3K(c,f,d,J,y)}E h=y.14().1O(c);8(h.S){h[4b](b)}O{17(F,\'8a 8b-3r 4M 6r! 8c 8d 44 3L 4a.\');y.78(b)}8(g!=\'4a\'&&!d){8(c<J.11){J.11+=b.S}}J.P=y.14().S;8(J.11>=J.P){J.11-=J.P}y.R(H(\'4O\',F));y.R(H(\'5O\',F));G I});y.12(H(\'79\',F),C(e,c,d,f){e.1f();E v=[c,d,f],t=[\'2N/27/2g\',\'3c\',\'27\'],a=3d(v,t);c=a[0];d=a[1];f=a[2];E g=K;8(c 2W $&&c.S>1){h=$();c.1W(C(i,a){E b=y.R(H(\'79\',F),[$(1k),d,f]);8(b)h=h.8e(b)});G h}8(1G(c)||c==\'4a\'){h=y.14().3i()}O{c=3K(c,f,d,J,y);E h=y.14().1O(c);8(h.S){8(c<J.11)J.11-=h.S}}8(h&&h.S){h.8f();J.P=y.14().S;y.R(H(\'4O\',F))}G h});y.12(H(\'3H\',F)+\' \'+H(\'3g\',F),C(e,a){e.1f();E b=e.5y.18(F.3y.43.S);8(2V(a)){2a[b]=a}8(1o(a)){2a[b].1b(a)}G 2a[b]});y.12(H(\'4o\',F),C(e,a){e.1f();8(J.11==0){E b=0}O{E b=J.P-J.11}8(1o(a)){a.1g($13,b)}G b});y.12(H(\'48\',F),C(e,a){e.1f();E b=7.1a.D||7.D.L,1X=1I.2A(J.P/b-1),2l;8(J.11==0){2l=0}O 8(J.11<J.P%b){2l=0}O 8(J.11==b&&!7.1T){2l=1X}O{2l=1I.7a((J.P-J.11)/b)}8(2l<0){2l=0}8(2l>1X){2l=1X}8(1o(a)){a.1g($13,2l)}G 2l});y.12(H(\'8g\',F),C(e,a){e.1f();E b=3J(y.14(),7);8(1o(a)){a.1g($13,b)}G b});y.12(H(\'18\',F),C(e,f,l,b){e.1f();8(J.P==0){G K}E v=[f,l,b],t=[\'27\',\'27\',\'C\'],a=3d(v,t);f=(Z(a[0]))?a[0]:0;l=(Z(a[1]))?a[1]:J.P;b=a[2];f+=J.11;l+=J.11;8(D.P>0){2h(f>J.P){f-=J.P}2h(l>J.P){l-=J.P}2h(f<0){f+=J.P}2h(l<0){l+=J.P}}E c=y.14(),$i;8(l>f){$i=c.18(f,l)}O{$i=$(c.18(f,J.P).3G().71(c.18(0,l).3G()))}8(1o(b)){b.1g($13,$i)}G $i});y.12(H(\'26\',F)+\' \'+H(\'2d\',F)+\' \'+H(\'20\',F),C(e,a){e.1f();E b=e.5y.18(F.3y.43.S),5P=z[b];8(1o(a)){a.1g($13,5P)}G 5P});y.12(H(\'4C\',F),C(e,a,b,c){e.1f();E d=K;8(1o(a)){a.1g($13,7)}O 8(1D(a)){31=$.1N(I,{},31,a);8(b!==K)d=I;O 7=$.1N(I,{},7,a)}O 8(!1G(a)){8(1o(b)){E f=4P(\'7.\'+a);8(1G(f)){f=\'\'}b.1g($13,f)}O 8(!1G(b)){8(2X c!==\'3c\')c=I;4P(\'31.\'+a+\' = b\');8(c!==K)d=I;O 4P(\'7.\'+a+\' = b\')}O{G 4P(\'7.\'+a)}}8(d){1U(y.14(),7);y.59(31);y.5Q();E g=4Q(y,7);y.R(H(\'3I\',F),[I,g])}G 7});y.12(H(\'5O\',F),C(e,a,b){e.1f();8(1G(a)){a=$(\'8h\')}O 8(1p(a)){a=$(a)}8(!2u(a)||a.S==0){G 17(F,\'2p a 5N 2g.\')}8(!1p(b)){b=\'a.6p\'}a.8i(b).1W(C(){E h=1k.7b||\'\';8(h.S>0&&y.14().7c($(h))!=-1){$(1k).23(\'5R\').5R(C(e){e.2E();y.R(H(\'3k\',F),h)})}});G I});y.12(H(\'3I\',F),C(e,b,c){e.1f();8(!7.1a.1z){G}E d=7.1a.D||7.D.L,4R=1I.2A(J.P/d);8(b){8(7.1a.3M){7.1a.1z.14().2t();7.1a.1z.1W(C(){1j(E a=0;a<4R;a++){E i=y.14().1O(3K(a*d,0,I,J,y));$(1k).78(7.1a.3M.1g(i[0],a+1))}})}7.1a.1z.1W(C(){$(1k).14().23(7.1a.3N).1W(C(a){$(1k).12(7.1a.3N,C(e){e.2E();y.R(H(\'3k\',F),[a*d,-7.1a.4S,I,7.1a])})})})}E f=y.1Q(H(\'48\',F))+7.1a.4S;8(f>=4R){f=0}8(f<0){f=4R-1}7.1a.1z.1W(C(){$(1k).14().2O(2z(\'7d\',F)).1O(f).36(2z(\'7d\',F))});G I});y.12(H(\'4O\',F),C(e){E a=7.D.L,2F=y.14(),2w=5c($1s,7,\'N\');J.P=2F.S;7.4r=5d(7,2w);8(z.4s){7[7.d[\'N\']]=4t(2w,z.4s)}8(7.2m){7.D.N=7.D.3O.N;7.D.1d=7.D.3O.1d;7=5f(7,2F,2w);a=7.D.L;5t(7,2F)}O 8(7.D.T.1c){a=32(2F,7,0)}O 8(7.D.1t!=\'*\'){a=3W(2F,7,0)}8(!7.1T&&J.11!=0&&a>J.11){8(7.D.T.1c){E b=4D(2F,7,J.11)-J.11}O 8(7.D.1t!=\'*\'){E b=7e(2F,7,J.11)-J.11}O{E b=7.D.L-J.11}17(F,\'8j 8k-1T: 8l \'+b+\' D 5B.\');y.R(H(\'V\',F),b)}7.D.L=2x(a,7,7.D.T.2c,$13);7.D.T.1Z=7.D.L;7=5h(7,2F);E c=4Q(y,7);y.R(H(\'3I\',F),[I,c]);4T(7,J.P,F);3B(7,J.11,F);G c});y.12(H(\'4p\',F),C(e,a){e.1f();1u=3t(1u);y.1m(\'57\',K);y.R(H(\'5w\',F));8(a){y.R(H(\'76\',F))}1U(y.14(),7);8(7.2m){y.14().1W(C(){$(1k).X($(1k).1m(\'7f\'))})}y.X(y.1m(\'5r\'));y.5u();y.5S();$1s.8m(y);G I});y.12(H(\'17\',F),C(e){17(F,\'3x N: \'+7.N);17(F,\'3x 1d: \'+7.1d);17(F,\'7g 8n: \'+7.D.N);17(F,\'7g 8o: \'+7.D.1d);17(F,\'4c 4d D L: \'+7.D.L);8(7.M.1H){17(F,\'4c 4d D 5T 8p: \'+7.M.D)}8(7.V.W){17(F,\'4c 4d D 5T 5B: \'+7.V.D)}8(7.Y.W){17(F,\'4c 4d D 5T 73: \'+7.Y.D)}G F.17});y.12(\'3o\',C(e,n,o){e.1f();G y.1Q(H(n,F),o)})};y.5u=C(){y.23(H(\'\',F));y.23(H(\'\',F,K));y.23(\'3o\')};y.5Q=C(){y.5S();4T(7,J.P,F);3B(7,J.11,F);8(7.M.2G){E b=3P(7.M.2G);$1s.12(H(\'4U\',F,K),C(){y.R(H(\'3a\',F),b)}).12(H(\'4V\',F,K),C(){y.R(H(\'3f\',F))})}8(7.M.W){7.M.W.12(H(7.M.3N,F,K),C(e){e.2E();E a=K,b=2H;8(z.26){a=\'1H\'}O 8(7.M.4W){a=\'3a\';b=3P(7.M.4W)}8(a){y.R(H(a,F),b)}})}8(7.V.W){7.V.W.12(H(7.V.3N,F,K),C(e){e.2E();y.R(H(\'V\',F))});8(7.V.2G){E b=3P(7.V.2G);7.V.W.12(H(\'4U\',F,K),C(){y.R(H(\'3a\',F),b)}).12(H(\'4V\',F,K),C(){y.R(H(\'3f\',F))})}}8(7.Y.W){7.Y.W.12(H(7.Y.3N,F,K),C(e){e.2E();y.R(H(\'Y\',F))});8(7.Y.2G){E b=3P(7.Y.2G);7.Y.W.12(H(\'4U\',F,K),C(){y.R(H(\'3a\',F),b)}).12(H(\'4V\',F,K),C(){y.R(H(\'3f\',F))})}}8(7.1a.1z){8(7.1a.2G){E b=3P(7.1a.2G);7.1a.1z.12(H(\'4U\',F,K),C(){y.R(H(\'3a\',F),b)}).12(H(\'4V\',F,K),C(){y.R(H(\'3f\',F))})}}8(7.V.2Y||7.Y.2Y){$(4e).12(H(\'7h\',F,K,I,I),C(e){E k=e.7i;8(k==7.Y.2Y){e.2E();y.R(H(\'Y\',F))}8(k==7.V.2Y){e.2E();y.R(H(\'V\',F))}})}8(7.1a.4X){$(4e).12(H(\'7h\',F,K,I,I),C(e){E k=e.7i;8(k>=49&&k<58){k=(k-49)*7.D.L;8(k<=J.P){e.2E();y.R(H(\'3k\',F),[k,0,I,7.1a])}}})}8(7.V.4Y||7.Y.4Y){2K(\'3L 4f-7j\',\'3L 8q-7j\');8($.1r.4f){E c=(7.V.4Y)?C(){y.R(H(\'V\',F))}:2H,4g=(7.Y.4Y)?C(){y.R(H(\'Y\',F))}:2H;8(4g||4g){8(!z.4f){z.4f=I;E d={\'8r\':30,\'8s\':30,\'8t\':I};1B(7.2b){Q\'4q\':Q\'5U\':d.8u=c;d.8v=4g;16;2y:d.8w=4g;d.8x=c}$1s.4f(d)}}}}8($.1r.1q){E f=\'8y\'8z 3l;8((f&&7.1q.4h)||(!f&&7.1q.5V)){E g=$.1N(I,{},7.V,7.1q),7k=$.1N(I,{},7.Y,7.1q),5W=C(){y.R(H(\'V\',F),[g])},5X=C(){y.R(H(\'Y\',F),[7k])};1B(7.2b){Q\'4q\':Q\'5U\':7.1q.2I.8A=5X;7.1q.2I.8B=5W;16;2y:7.1q.2I.8C=5X;7.1q.2I.8D=5W}8(z.1q){y.1q(\'4p\')}$1s.1q(7.1q.2I);$1s.X(\'7l\',\'8E\');z.1q=I}}8($.1r.1h){8(7.V.1h){2K(\'7m V.1h 7n\',\'3L 1h 4C 2g\');7.V.1h=2H;7.1h={D:5Y(7.V.1h)}}8(7.Y.1h){2K(\'7m Y.1h 7n\',\'3L 1h 4C 2g\');7.Y.1h=2H;7.1h={D:5Y(7.Y.1h)}}8(7.1h){E h=$.1N(I,{},7.V,7.1h),7o=$.1N(I,{},7.Y,7.1h);8(z.1h){$1s.23(H(\'1h\',F,K))}$1s.12(H(\'1h\',F,K),C(e,a){e.2E();8(a>0){y.R(H(\'V\',F),[h])}O{y.R(H(\'Y\',F),[7o])}});z.1h=I}}8(7.M.1H){y.R(H(\'1H\',F),7.M.5Z)}8(z.6C){E i=$(3l),61=0,62=0;i.12(H(\'8F\',F,K,I,I),C(e){E a=i.N(),63=i.1d();8(a!=61||63!=62){y.R(H(\'5w\',F));8(7.M.64&&!z.26){y.R(H(\'1H\',F))}1U(y.14(),7);y.R(H(\'4O\',F));61=a;62=63}})}};y.5S=C(){E a=H(\'\',F),3Q=H(\'\',F,K);65=H(\'\',F,K,I,I);$(4e).23(65);$(3l).23(65);$1s.23(3Q);8(7.M.W){7.M.W.23(3Q)}8(7.V.W){7.V.W.23(3Q)}8(7.Y.W){7.Y.W.23(3Q)}8(7.1a.1z){7.1a.1z.23(3Q);8(7.1a.3M){7.1a.1z.14().2t()}}8(z.1q){y.1q(\'4p\');$1s.X(\'7l\',\'2y\');z.1q=K}8(z.1h){z.1h=K}4T(7,\'4i\',F);3B(7,\'2O\',F)};8(1l(w)){w={\'17\':w}}E z={\'2b\':\'Y\',\'26\':I,\'20\':K,\'2d\':K,\'1h\':K,\'1q\':K},J={\'P\':y.14().S,\'11\':0},1u={\'M\':2H,\'1e\':2H,\'2M\':2o(),\'3w\':0},U={\'2d\':K,\'1C\':0,\'2M\':0,\'2k\':\'\',\'19\':[]},2a={\'3H\':[],\'3g\':[]},2D=[],F=$.1N(I,{},$.1r.1v.7p,w),7={},31=$.1N(I,{},u),$1s=y.8G(\'<\'+F.66.55+\' 8H="\'+F.66.7q+\'" />\').68();F.4n=y.4n;F.3U=$.1r.1v.3U++;y.59(31,I,56);y.6K();y.6Q();y.5Q();8(2V(7.D.3m)){E A=7.D.3m}O{E A=[];8(7.D.3m!=0){A.1b(7.D.3m)}}8(7.25){A.8I(4j(7r(7.25),10))}8(A.S>0){1j(E a=0,l=A.S;a<l;a++){E s=A[a];8(s==0){69}8(s===I){s=3l.8J.7b;8(s.S<1){69}}O 8(s===\'7s\'){s=1I.4k(1I.7s()*J.P)}8(y.1Q(H(\'3k\',F),[s,0,I,{1V:\'3Z\'}])){16}}}E B=4Q(y,7),7t=3J(y.14(),7);8(7.7u){7.7u.1g($13,{\'N\':B.N,\'1d\':B.1d,\'D\':7t})}y.R(H(\'3I\',F),[I,B]);y.R(H(\'5O\',F));8(F.17){y.R(H(\'17\',F))}G y};$.1r.1v.3U=1;$.1r.1v.5b={\'2n\':K,\'3A\':I,\'1T\':I,\'2m\':K,\'2b\':\'1n\',\'D\':{\'3m\':0},\'1M\':{\'2k\':\'8K\',\'1C\':6F,\'2G\':K,\'3N\':\'5R\',\'3z\':K}};$.1r.1v.7p={\'17\':K,\'3y\':{\'43\':\'\',\'7v\':\'8L\'},\'66\':{\'55\':\'8M\',\'7q\':\'8N\'},\'6a\':{}};$.1r.1v.7w=C(a){G\'<a 8O="#"><7x>\'+a+\'</7x></a>\'};$.1r.1v.7y=C(a){$(1k).X(\'N\',a+\'%\')};$.1r.1v.25={3G:C(n){n+=\'=\';E b=4e.25.3R(\';\');1j(E a=0,l=b.S;a<l;a++){E c=b[a];2h(c.8P(0)==\' \'){c=c.18(1)}8(c.3S(n)==0){G c.18(n.S)}}G 0},6b:C(n,v,d){E e="";8(d){E a=6c 7z();a.8Q(a.2o()+(d*24*60*60*8R));e="; 8S="+a.8T()}4e.25=n+\'=\'+v+e+\'; 8U=/\'},2t:C(n){$.1r.1v.25.6b(n,"",-1)}};C 46(d,e){G{19:[],1C:d,8V:d,2k:e,2M:2o()}}C 2P(s){8(1D(s.3u)){2P(s.3u)}1j(E a=0,l=s.19.S;a<l;a++){E b=s.19[a];8(!b){69}8(b[3]){b[0].5v()}b[0].8W(b[1],{8X:b[2],1C:s.1C,2k:s.2k})}8(1D(s.3v)){2P(s.3v)}}C 41(s,c){8(!1l(c)){c=I}8(1D(s.3u)){41(s.3u,c)}1j(E a=0,l=s.19.S;a<l;a++){E b=s.19[a];b[0].5v(I);8(c){b[0].X(b[1]);8(1o(b[2])){b[2]()}}}8(1D(s.3v)){41(s.3v,c)}}C 5I(a,b,o){8(b){b.2t()}1B(o.1V){Q\'1w\':Q\'3j\':Q\'1J-1w\':Q\'22-1w\':a.X(\'1t\',\'\');16}}C 47(d,o,b,a,c){8(o[b]){o[b].1g(d,a)}8(c[b].S){1j(E i=0,l=c[b].S;i<l;i++){c[b][i].1g(d,a)}}G[]}C 5J(a,q,c){8(q.S){a.R(H(q[0][0],c),q[0][1]);q.8Y()}G q}C 5C(b){b.1W(C(){E a=$(1k);a.1m(\'7A\',a.2f(\':3s\')).4i()})}C 5G(b){8(b){b.1W(C(){E a=$(1k);8(!a.1m(\'7A\')){a.4l()}})}}C 3t(t){8(t.M){8Z(t.M)}8(t.1e){90(t.1e)}G t}C 5H(a,b,c,d,e,f,g){G{\'N\':g.N,\'1d\':g.1d,\'D\':{\'1Z\':a,\'91\':b,\'L\':c,\'6c\':c},\'1M\':{\'D\':d,\'2b\':e,\'1C\':f}}}C 5E(a,o,b,c){E d=a.1C;8(a.1V==\'3Z\'){G 0}8(d==\'M\'){d=o.1M.1C/o.1M.D*b}O 8(d<10){d=c/d}8(d<1){G 0}8(a.1V==\'1w\'){d=d/2}G 1I.7a(d)}C 4T(o,t,c){E a=(Z(o.D.4B))?o.D.4B:o.D.L+1;8(t==\'4l\'||t==\'4i\'){E f=t}O 8(a>t){17(c,\'2p 6S D (\'+t+\' P, \'+a+\' 6T): 92 93.\');E f=\'4i\'}O{E f=\'4l\'}E s=(f==\'4l\')?\'2O\':\'36\',h=2z(\'3s\',c);8(o.M.W){o.M.W[f]()[s](h)}8(o.V.W){o.V.W[f]()[s](h)}8(o.Y.W){o.Y.W[f]()[s](h)}8(o.1a.1z){o.1a.1z[f]()[s](h)}}C 3B(o,f,c){8(o.1T||o.3A)G;E a=(f==\'2O\'||f==\'36\')?f:K,4Z=2z(\'94\',c);8(o.M.W&&a){o.M.W[a](4Z)}8(o.V.W){E b=a||(f==0)?\'36\':\'2O\';o.V.W[b](4Z)}8(o.Y.W){E b=a||(f==o.D.L)?\'36\':\'2O\';o.Y.W[b](4Z)}}C 3T(a,b){8(1o(b)){b=b.1g(a)}O 8(1G(b)){b={}}G b}C 6s(a,b){b=3T(a,b);8(Z(b)){b={\'L\':b}}O 8(b==\'1c\'){b={\'L\':b,\'N\':b,\'1d\':b}}O 8(!1D(b)){b={}}G b}C 6t(a,b){b=3T(a,b);8(Z(b)){8(b<=50){b={\'D\':b}}O{b={\'1C\':b}}}O 8(1p(b)){b={\'2k\':b}}O 8(!1D(b)){b={}}G b}C 51(a,b){b=3T(a,b);8(1p(b)){E c=6d(b);8(c==-1){b=$(b)}O{b=c}}G b}C 6u(a,b){b=51(a,b);8(2u(b)){b={\'W\':b}}O 8(1l(b)){b={\'1H\':b}}O 8(Z(b)){b={\'2L\':b}}8(b.1e){8(1p(b.1e)||2u(b.1e)){b.1e={\'2q\':b.1e}}}G b}C 6G(a,b){8(1o(b.W)){b.W=b.W.1g(a)}8(1p(b.W)){b.W=$(b.W)}8(!1l(b.1H)){b.1H=I}8(!Z(b.5Z)){b.5Z=0}8(1G(b.4W)){b.4W=I}8(!1l(b.64)){b.64=I}8(!Z(b.2L)){b.2L=(b.1C<10)?95:b.1C*5}8(b.1e){8(1o(b.1e.2q)){b.1e.2q=b.1e.2q.1g(a)}8(1p(b.1e.2q)){b.1e.2q=$(b.1e.2q)}8(b.1e.2q){8(!1o(b.1e.4A)){b.1e.4A=$.1r.1v.7y}8(!Z(b.1e.5x)){b.1e.5x=50}}O{b.1e=K}}G b}C 5a(a,b){b=51(a,b);8(2u(b)){b={\'W\':b}}O 8(Z(b)){b={\'2Y\':b}}G b}C 5i(a,b){8(1o(b.W)){b.W=b.W.1g(a)}8(1p(b.W)){b.W=$(b.W)}8(1p(b.2Y)){b.2Y=6d(b.2Y)}G b}C 6v(a,b){b=51(a,b);8(2u(b)){b={\'1z\':b}}O 8(1l(b)){b={\'4X\':b}}G b}C 6H(a,b){8(1o(b.1z)){b.1z=b.1z.1g(a)}8(1p(b.1z)){b.1z=$(b.1z)}8(!Z(b.D)){b.D=K}8(!1l(b.4X)){b.4X=K}8(!1o(b.3M)&&!52(b.3M)){b.3M=$.1r.1v.7w}8(!Z(b.4S)){b.4S=0}G b}C 6w(a,b){8(1o(b)){b=b.1g(a)}8(1G(b)){b={\'4h\':K}}8(3p(b)){b={\'4h\':b}}O 8(Z(b)){b={\'D\':b}}G b}C 6I(a,b){8(!1l(b.4h)){b.4h=I}8(!1l(b.5V)){b.5V=K}8(!1D(b.2I)){b.2I={}}8(!1l(b.2I.7B)){b.2I.7B=K}G b}C 6x(a,b){8(1o(b)){b=b.1g(a)}8(3p(b)){b={}}O 8(Z(b)){b={\'D\':b}}O 8(1G(b)){b=K}G b}C 6J(a,b){G b}C 3K(a,b,c,d,e){8(1p(a)){a=$(a,e)}8(1D(a)){a=$(a,e)}8(2u(a)){a=e.14().7c(a);8(!1l(c)){c=K}}O{8(!1l(c)){c=I}}8(!Z(a)){a=0}8(!Z(b)){b=0}8(c){a+=d.11}a+=b;8(d.P>0){2h(a>=d.P){a-=d.P}2h(a<0){a+=d.P}}G a}C 4D(i,o,s){E t=0,x=0;1j(E a=s;a>=0;a--){E j=i.1O(a);t+=(j.2f(\':L\'))?j[o.d[\'2v\']](I):0;8(t>o.4r){G x}8(a==0){a=i.S}x++}}C 7e(i,o,s){G 6e(i,o.D.1t,o.D.T.4u,s)}C 6W(i,o,s,m){G 6e(i,o.D.1t,m,s)}C 6e(i,f,m,s){E t=0,x=0;1j(E a=s,l=i.S;a>=0;a--){x++;8(x==l){G x}E j=i.1O(a);8(j.2f(f)){t++;8(t==m){G x}}8(a==0){a=l}}}C 5A(a,o){G o.D.T.4u||a.14().18(0,o.D.L).1t(o.D.1t).S}C 32(i,o,s){E t=0,x=0;1j(E a=s,l=i.S-1;a<=l;a++){E j=i.1O(a);t+=(j.2f(\':L\'))?j[o.d[\'2v\']](I):0;8(t>o.4r){G x}x++;8(x==l+1){G x}8(a==l){a=-1}}}C 5L(i,o,s,l){E v=32(i,o,s);8(!o.1T){8(s+v>l){v=l-s}}G v}C 3W(i,o,s){G 6f(i,o.D.1t,o.D.T.4u,s,o.1T)}C 72(i,o,s,m){G 6f(i,o.D.1t,m+1,s,o.1T)-1}C 6f(i,f,m,s,c){E t=0,x=0;1j(E a=s,l=i.S-1;a<=l;a++){x++;8(x>=l){G x}E j=i.1O(a);8(j.2f(f)){t++;8(t==m){G x}}8(a==l){a=-1}}}C 3J(i,o){G i.18(0,o.D.L)}C 6Y(i,o,n){G i.18(n,o.D.T.1Z+n)}C 6Z(i,o){G i.18(0,o.D.L)}C 74(i,o){G i.18(0,o.D.T.1Z)}C 75(i,o,n){G i.18(n,o.D.L+n)}C 4y(i,o,d){8(o.1R){8(!1p(d)){d=\'29\'}i.1W(C(){E j=$(1k),m=4j(j.X(o.d[\'1S\']),10);8(!Z(m)){m=0}j.1m(d,m)})}}C 1U(i,o,m){8(o.1R){E x=(1l(m))?m:K;8(!Z(m)){m=0}4y(i,o,\'7C\');i.1W(C(){E j=$(1k);j.X(o.d[\'1S\'],((x)?j.1m(\'7C\'):m+j.1m(\'29\')))})}}C 5s(i,o){8(o.2m){i.1W(C(){E j=$(1k),s=5o(j,[\'N\',\'1d\']);j.1m(\'7f\',s)})}}C 5t(o,b){E c=o.D.L,7D=o.D[o.d[\'N\']],6g=o[o.d[\'1d\']],7E=3V(6g);b.1W(C(){E a=$(1k),6h=7D-7F(a,o,\'96\');a[o.d[\'N\']](6h);8(7E){a[o.d[\'1d\']](4t(6h,6g))}})}C 4Q(a,o){E b=a.68(),$i=a.14(),$v=3J($i,o),53=4I(4J($v,o,I),o,K);b.X(53);8(o.1R){E p=o.1i,r=p[o.d[1]];8(o.1A&&r<0){r=0}E c=$v.3i();c.X(o.d[\'1S\'],c.1m(\'29\')+r);a.X(o.d[\'3q\'],p[o.d[0]]);a.X(o.d[\'1n\'],p[o.d[3]])}a.X(o.d[\'N\'],53[o.d[\'N\']]+(2R($i,o,\'N\')*2));a.X(o.d[\'1d\'],6i($i,o,\'1d\'));G 53}C 4J(i,o,a){G[2R(i,o,\'N\',a),6i(i,o,\'1d\',a)]}C 6i(i,o,a,b){8(!1l(b)){b=K}8(Z(o[o.d[a]])&&b){G o[o.d[a]]}8(Z(o.D[o.d[a]])){G o.D[o.d[a]]}a=(a.6j().3S(\'N\')>-1)?\'2v\':\'3n\';G 4m(i,o,a)}C 4m(i,o,b){E s=0;1j(E a=0,l=i.S;a<l;a++){E j=i.1O(a);E m=(j.2f(\':L\'))?j[o.d[b]](I):0;8(s<m){s=m}}G s}C 2R(i,o,b,c){8(!1l(c)){c=K}8(Z(o[o.d[b]])&&c){G o[o.d[b]]}8(Z(o.D[o.d[b]])){G o.D[o.d[b]]*i.S}E d=(b.6j().3S(\'N\')>-1)?\'2v\':\'3n\',s=0;1j(E a=0,l=i.S;a<l;a++){E j=i.1O(a);s+=(j.2f(\':L\'))?j[o.d[d]](I):0}G s}C 5c(a,o,d){E b=a.2f(\':L\');8(b){a.4i()}E s=a.68()[o.d[d]]();8(b){a.4l()}G s}C 5d(o,a){G(Z(o[o.d[\'N\']]))?o[o.d[\'N\']]:a}C 6k(i,o,b){E s=K,v=K;1j(E a=0,l=i.S;a<l;a++){E j=i.1O(a);E c=(j.2f(\':L\'))?j[o.d[b]](I):0;8(s===K){s=c}O 8(s!=c){v=I}8(s==0){v=I}}G v}C 7F(i,o,d){G i[o.d[\'97\'+d]](I)-i[o.d[d.6j()]]()}C 4t(s,o){8(3V(o)){o=4j(o.18(0,-1),10);8(!Z(o)){G s}s*=o/2J}G s}C H(n,c,a,b,d){8(!1l(a)){a=I}8(!1l(b)){b=I}8(!1l(d)){d=K}8(a){n=c.3y.43+n}8(b){n=n+\'.\'+c.3y.7v}8(b&&d){n+=c.3U}G n}C 2z(n,c){G(1p(c.6a[n]))?c.6a[n]:n}C 4I(a,o,p){8(!1l(p)){p=I}E b=(o.1R&&p)?o.1i:[0,0,0,0];E c={};c[o.d[\'N\']]=a[0]+b[1]+b[3];c[o.d[\'1d\']]=a[1]+b[0]+b[2];G c}C 3d(c,d){E e=[];1j(E a=0,7G=c.S;a<7G;a++){1j(E b=0,7H=d.S;b<7H;b++){8(d[b].3S(2X c[a])>-1&&1G(e[b])){e[b]=c[a];16}}}G e}C 6E(p){8(1G(p)){G[0,0,0,0]}8(Z(p)){G[p,p,p,p]}8(1p(p)){p=p.3R(\'98\').7I(\'\').3R(\'99\').7I(\'\').3R(\' \')}8(!2V(p)){G[0,0,0,0]}1j(E i=0;i<4;i++){p[i]=4j(p[i],10)}1B(p.S){Q 0:G[0,0,0,0];Q 1:G[p[0],p[0],p[0],p[0]];Q 2:G[p[0],p[1],p[0],p[1]];Q 3:G[p[0],p[1],p[2],p[1]];2y:G[p[0],p[1],p[2],p[3]]}}C 4H(a,o){E x=(Z(o[o.d[\'N\']]))?1I.2A(o[o.d[\'N\']]-2R(a,o,\'N\')):0;1B(o.1A){Q\'1n\':G[0,x];Q\'35\':G[x,0];Q\'5e\':2y:G[1I.2A(x/2),1I.4k(x/2)]}}C 6y(o){E a=[[\'N\',\'7J\',\'2v\',\'1d\',\'7K\',\'3n\',\'1n\',\'3q\',\'1S\',0,1,2,3],[\'1d\',\'7K\',\'3n\',\'N\',\'7J\',\'2v\',\'3q\',\'1n\',\'5p\',3,2,1,0]];E b=a[0].S,7L=(o.2b==\'35\'||o.2b==\'1n\')?0:1;E c={};1j(E d=0;d<b;d++){c[a[0][d]]=a[7L][d]}G c}C 4E(x,o,a,b){E v=x;8(1o(a)){v=a.1g(b,v)}O 8(1p(a)){E p=a.3R(\'+\'),m=a.3R(\'-\');8(m.S>p.S){E c=I,6l=m[0],2Z=m[1]}O{E c=K,6l=p[0],2Z=p[1]}1B(6l){Q\'9a\':v=(x%2==1)?x-1:x;16;Q\'9b\':v=(x%2==0)?x-1:x;16;2y:v=x;16}2Z=4j(2Z,10);8(Z(2Z)){8(c){2Z=-2Z}v+=2Z}}8(!Z(v)||v<1){v=1}G v}C 2x(x,o,a,b){G 6m(4E(x,o,a,b),o.D.T)}C 6m(v,i){8(Z(i.34)&&v<i.34){v=i.34}8(Z(i.1X)&&v>i.1X){v=i.1X}8(v<1){v=1}G v}C 5j(s){8(!2V(s)){s=[[s]]}8(!2V(s[0])){s=[s]}1j(E j=0,l=s.S;j<l;j++){8(1p(s[j][0])){s[j][0]=$(s[j][0])}8(!1l(s[j][1])){s[j][1]=I}8(!1l(s[j][2])){s[j][2]=I}8(!Z(s[j][3])){s[j][3]=0}}G s}C 6d(k){8(k==\'35\'){G 39}8(k==\'1n\'){G 37}8(k==\'4q\'){G 38}8(k==\'5U\'){G 40}G-1}C 5K(n,a,c){8(n){E v=a.1Q(H(\'4o\',c));$.1r.1v.25.6b(n,v)}}C 7r(n){E c=$.1r.1v.25.3G(n);G(c==\'\')?0:c}C 5o(a,b){E c={},54;1j(E p=0,l=b.S;p<l;p++){54=b[p];c[54]=a.X(54)}G c}C 6z(a,b,c,d){8(!1D(a.T)){a.T={}}8(!1D(a.3O)){a.3O={}}8(a.3m==0&&Z(d)){a.3m=d}8(1D(a.L)){a.T.34=a.L.34;a.T.1X=a.L.1X;a.L=K}O 8(1p(a.L)){8(a.L==\'1c\'){a.T.1c=I}O{a.T.2c=a.L}a.L=K}O 8(1o(a.L)){a.T.2c=a.L;a.L=K}8(!1p(a.1t)){a.1t=(c.1t(\':3s\').S>0)?\':L\':\'*\'}8(!a[b.d[\'N\']]){8(b.2m){17(I,\'7M a \'+b.d[\'N\']+\' 1j 3L D!\');a[b.d[\'N\']]=4m(c,b,\'2v\')}O{a[b.d[\'N\']]=(6k(c,b,\'2v\'))?\'1c\':c[b.d[\'2v\']](I)}}8(!a[b.d[\'1d\']]){a[b.d[\'1d\']]=(6k(c,b,\'3n\'))?\'1c\':c[b.d[\'3n\']](I)}a.3O.N=a.N;a.3O.1d=a.1d;G a}C 6D(a,b){8(a.D[a.d[\'N\']]==\'1c\'){a.D.T.1c=I}8(!a.D.T.1c){8(Z(a[a.d[\'N\']])){a.D.L=1I.4k(a[a.d[\'N\']]/a.D[a.d[\'N\']])}O{a.D.L=1I.4k(b/a.D[a.d[\'N\']]);a[a.d[\'N\']]=a.D.L*a.D[a.d[\'N\']];8(!a.D.T.2c){a.1A=K}}8(a.D.L==\'9c\'||a.D.L<1){17(I,\'2p a 5N 27 4d L D: 7M 44 "1c".\');a.D.T.1c=I}}G a}C 6A(a,b,c){8(a==\'M\'){a=4m(c,b,\'2v\')}G a}C 6B(a,b,c){8(a==\'M\'){a=4m(c,b,\'3n\')}8(!a){a=b.D[b.d[\'1d\']]}G a}C 5h(o,a){E p=4H(3J(a,o),o);o.1i[o.d[1]]=p[1];o.1i[o.d[3]]=p[0];G o}C 5f(o,a,b){E c=6m(1I.2A(o[o.d[\'N\']]/o.D[o.d[\'N\']]),o.D.T);8(c>a.S){c=a.S}E d=1I.4k(o[o.d[\'N\']]/c);o.D.L=c;o.D[o.d[\'N\']]=d;o[o.d[\'N\']]=c*d;G o}C 3P(p){8(1p(p)){E i=(p.3S(\'9d\')>-1)?I:K,r=(p.3S(\'3f\')>-1)?I:K}O{E i=r=K}G[i,r]}C 5Y(a){G(Z(a))?a:2H}C 6n(a){G(a===2H)}C 1G(a){G(6n(a)||2X a==\'7N\'||a===\'\'||a===\'7N\')}C 2V(a){G(a 2W 9e)}C 2u(a){G(a 2W 7O)}C 1D(a){G((a 2W 9f||2X a==\'2g\')&&!6n(a)&&!2u(a)&&!2V(a))}C Z(a){G((a 2W 4c||2X a==\'27\')&&!9g(a))}C 1p(a){G((a 2W 9h||2X a==\'2N\')&&!1G(a)&&!3p(a)&&!52(a))}C 1o(a){G(a 2W 9i||2X a==\'C\')}C 1l(a){G(a 2W 9j||2X a==\'3c\'||3p(a)||52(a))}C 3p(a){G(a===I||a===\'I\')}C 52(a){G(a===K||a===\'K\')}C 3V(x){G(1p(x)&&x.18(-1)==\'%\')}C 2o(){G 6c 7z().2o()}C 2K(o,n){17(I,o+\' 2f 9k, 9l 1j 9m 9n 9o 9p. 9q \'+n+\' 9r.\')}C 17(d,m){8(1D(d)){E s=\' (\'+d.4n+\')\';d=d.17}O{E s=\'\'}8(!d){G K}8(1p(m)){m=\'1v\'+s+\': \'+m}O{m=[\'1v\'+s+\':\',m]}8(3l.6o&&3l.6o.7P){3l.6o.7P(m)}G K}$.1N($.2k,{\'9s\':C(t){E a=t*t;G t*(-a*t+4*a-6*t+4)},\'9t\':C(t){G t*(4*t*t-9*t+6)},\'9u\':C(t){E a=t*t;G t*(33*a*a-9v*a*t+9w*a-67*t+15)}})})(7O);',62,591,'|||||||opts|if||||||||||||||||||||||||||||||function|items|var|conf|return|cf_e|true|itms|false|visible|auto|width|else|total|case|trigger|length|visibleConf|scrl|prev|button|css|next|is_number||first|bind|tt0|children||break|debug|slice|anims|pagination|push|variable|height|progress|stopPropagation|call|mousewheel|padding|for|this|is_boolean|data|left|is_function|is_string|swipe|fn|wrp|filter|tmrs|carouFredSel|fade|_onafter|_moveitems|container|align|switch|duration|is_object|_s_paddingold|_s_paddingcur|is_undefined|play|Math|cover|_position|opacity|scroll|extend|eq|_a_wrapper|triggerHandler|usePadding|marginRight|circular|sz_resetMargin|fx|each|max|i_cur_l|old|isScrolling|i_old_l|uncover|unbind||cookie|isPaused|number|a_cfs|_cfs_origCssMargin|clbk|direction|adjust|isStopped|stopImmediatePropagation|is|object|while|i_new|w_siz|easing|nr|responsive|synchronise|getTime|Not|bar|i_new_l|a_cur|remove|is_jquery|outerWidth|avail_primary|cf_getItemsAdjust|default|cf_c|ceil|pR|_s_paddingnew|queu|preventDefault|a_itm|pauseOnHover|null|options|100|deprecated|timeoutDuration|startTime|string|removeClass|sc_startScroll|i_skp|ms_getTotalSize|a_old|a_lef|a_dur|is_array|instanceof|typeof|key|adj||opts_orig|gn_getVisibleItemsNext||min|right|addClass||||pause|perc|boolean|cf_sortParams|scrolling|resume|onAfter|i_old|last|crossfade|slideTo|window|start|outerHeight|_cfs_triggerEvent|is_true|top|position|hidden|sc_clearTimers|pre|post|timePassed|Carousel|events|queue|infinite|nv_enableNavi|i_siz|i_siz_vis|_a_paddingold|_a_paddingcur|get|onBefore|updatePageStatus|gi_getCurrentItems|gn_getItemIndex|the|anchorBuilder|event|sizesConf|bt_pauseOnHoverConfig|ns2|split|indexOf|go_getObject|serialNumber|is_percentage|gn_getVisibleItemsNextFilter|orgCSS|zIndex|none||sc_stopScroll|dur2|prefix|to|appendTo|sc_setScroll|sc_fireCallbacks|currentPage||end|before|Number|of|document|touchwipe|wN|onTouch|hide|parseInt|floor|show|ms_getTrueLargestSize|selector|currentPosition|destroy|up|maxDimension|primarySizePercentage|ms_getPercentage|org|onTimeoutStart|onTimeoutPause|onTimeoutEnd|sz_storeMargin|stopped|updater|minimum|configuration|gn_getVisibleItemsPrev|cf_getAdjust|onEnd|clone|cf_getAlignPadding|cf_mapWrapperSizes|ms_getSizes|a_wsz|a_new|not|a_cfs_vis|updateSizes|eval|sz_setSizes|pgs|deviation|nv_showNavi|mouseenter|mouseleave|pauseOnEvent|keys|wipe|di||go_getNaviObject|is_false|sz|prop|element|starting_position|_cfs_isCarousel||_cfs_init|go_getPrevNextObject|defaults|ms_getParentSize|ms_getMaxDimension|center|in_getResponsiveValues|bottom|in_getAlignPadding|go_complementPrevNextObject|cf_getSynchArr|onPauseStart|onPausePause|onPauseEnd|pauseDuration|in_mapCss|marginBottom|newPosition|_cfs_origCss|sz_storeSizes|sz_setResponsiveSizes|_cfs_unbind_events|stop|finish|interval|type|conditions|gn_getVisibleOrg|backward|sc_hideHiddenItems|a_lef_vis|sc_getDuration|_a_paddingnew|sc_showHiddenItems|sc_mapCallbackArguments|sc_afterScroll|sc_fireQueue|cf_setCookie|gn_getVisibleItemsNextTestCircular|slideToPage|valid|linkAnchors|value|_cfs_bind_buttons|click|_cfs_unbind_buttons|scrolled|down|onMouse|swP|swN|bt_mousesheelNumber|delay||_windowWidth|_windowHeight|nh|pauseOnResize|ns3|wrapper||parent|continue|classnames|set|new|cf_getKeyCode|gn_getItemsPrevFilter|gn_getItemsNextFilter|seco|nw|ms_getLargestSize|toLowerCase|ms_hasVariableSizes|sta|cf_getItemAdjustMinMax|is_null|console|caroufredsel|No|found|go_getItemsObject|go_getScrollObject|go_getAutoObject|go_getPaginationObject|go_getSwipeObject|go_getMousewheelObject|cf_getDimensions|in_complementItems|in_complementPrimarySize|in_complementSecondarySize|upDateOnWindowResize|in_complementVisibleItems|cf_getPadding|500|go_complementAutoObject|go_complementPaginationObject|go_complementSwipeObject|go_complementMousewheelObject|_cfs_build|textAlign|float|marginTop|marginLeft|absolute|_cfs_bind_events|paused|enough|needed|page|slide_|gn_getScrollItemsPrevFilter|Scrolling|gi_getOldItemsPrev|gi_getNewItemsPrev|directscroll|concat|gn_getScrollItemsNextFilter|forward|gi_getOldItemsNext|gi_getNewItemsNext|jumpToStart|after|append|removeItem|round|hash|index|selected|gn_getVisibleItemsPrevFilter|_cfs_origCssSizes|Item|keyup|keyCode|plugin|scN|cursor|The|option|mcN|configs|classname|cf_getCookie|random|itm|onCreate|namespace|pageAnchorBuilder|span|progressbarUpdater|Date|_cfs_isHidden|triggerOnTouchEnd|_cfs_tempCssMargin|newS|secp|ms_getPaddingBorderMargin|l1|l2|join|innerWidth|innerHeight|dx|Set|undefined|jQuery|log|caroufredsel_cookie_|relative|fixed|overflow|setInterval|setTimeout|or|Callback|returned|Page|resumed|currently|slide_prev|prependTo|slide_next|prevPage|nextPage|prepend|carousel|insertItem|Correct|insert|Appending|item|add|detach|currentVisible|body|find|Preventing|non|sliding|replaceWith|widths|heights|automatically|touchSwipe|min_move_x|min_move_y|preventDefaultEvents|wipeUp|wipeDown|wipeLeft|wipeRight|ontouchstart|in|swipeUp|swipeDown|swipeLeft|swipeRight|move|resize|wrap|class|unshift|location|swing|cfs|div|caroufredsel_wrapper|href|charAt|setTime|1000|expires|toGMTString|path|orgDuration|animate|complete|shift|clearTimeout|clearInterval|skipped|Hiding|navigation|disabled|2500|Width|outer|px|em|even|odd|Infinity|immediate|Array|Object|isNaN|String|Function|Boolean|DEPRECATED|support|it|will|be|removed|Use|instead|quadratic|cubic|elastic|106|126'.split('|'),0,{}))
;(function($,window,undefined){'use strict';var defaults={'version':'2.0.3','tipLocation':'bottom','nubPosition':'auto','scroll':true,'scrollSpeed':300,'timer':0,'autoStart':false,'startTimerOnClick':true,'startOffset':0,'nextButton':true,'tipAnimation':'fade','pauseAfter':[],'tipAnimationFadeSpeed':300,'cookieMonster':false,'cookieName':'joyride','cookieDomain':false,'tipContainer':'body','modal':false,'expose':false,'postExposeCallback':$.noop,'preRideCallback':$.noop,'postRideCallback':$.noop,'preStepCallback':$.noop,'postStepCallback':$.noop,'template':{'link':'<a href="#close" class="joyride-close-tip">X</a>','timer':'<div class="joyride-timer-indicator-wrap"><span class="joyride-timer-indicator"></span></div>','tip':'<div class="joyride-tip-guide"><span class="joyride-nub"></span></div>','wrapper':'<div class="joyride-content-wrapper" role="dialog"></div>','button':'<a href="#" class="joyride-next-tip"></a>','modal':'<div class="joyride-modal-bg"></div>','expose':'<div class="joyride-expose-wrapper"></div>','exposeCover':'<div class="joyride-expose-cover"></div>'}},Modernizr=Modernizr||false,settings={},methods={init:function(opts){return this.each(function(){if($.isEmptyObject(settings)){settings=$.extend(true,defaults,opts);settings.document=window.document;settings.$document=$(settings.document);settings.$window=$(window);settings.$content_el=$(this);settings.$body=$(settings.tipContainer);settings.body_offset=$(settings.tipContainer).position();settings.$tip_content=$('> li',settings.$content_el);settings.paused=false;settings.attempts=0;settings.tipLocationPatterns={top:['bottom'],bottom:[],left:['right','top','bottom'],right:['left','top','bottom']};methods.jquery_check();if(!$.isFunction($.cookie)){settings.cookieMonster=false;}
    if(!settings.cookieMonster||!$.cookie(settings.cookieName)){settings.$tip_content.each(function(index){methods.create({$li:$(this),index:index});});if(settings.autoStart)
    {if(!settings.startTimerOnClick&&settings.timer>0){methods.show('init');methods.startTimer();}else{methods.show('init');}}}
    settings.$document.on('click.joyride','.joyride-next-tip, .joyride-modal-bg',function(e){e.preventDefault();if(settings.$li.next().length<1){methods.end();}else if(settings.timer>0){clearTimeout(settings.automate);methods.hide();methods.show();methods.startTimer();}else{methods.hide();methods.show();}});settings.$document.on('click.joyride','.joyride-close-tip',function(e){e.preventDefault();methods.end();});settings.$window.bind('resize.joyride',function(e){if(settings.$li){if(settings.exposed&&settings.exposed.length>0){var $els=$(settings.exposed);$els.each(function(){var $this=$(this);methods.un_expose($this);methods.expose($this);});}
        if(methods.is_phone()){methods.pos_phone();}else{methods.pos_default();}}});}else{methods.restart();}});},resume:function(){methods.set_li();methods.show();},nextTip:function(){if(settings.$li.next().length<1){methods.end();}else if(settings.timer>0){clearTimeout(settings.automate);methods.hide();methods.show();methods.startTimer();}else{methods.hide();methods.show();}},tip_template:function(opts){var $blank,content,$wrapper;opts.tip_class=opts.tip_class||'';$blank=$(settings.template.tip).addClass(opts.tip_class);content=$.trim($(opts.li).html())+
    methods.button_text(opts.button_text)+
    settings.template.link+
    methods.timer_instance(opts.index);$wrapper=$(settings.template.wrapper);if(opts.li.attr('data-aria-labelledby')){$wrapper.attr('aria-labelledby',opts.li.attr('data-aria-labelledby'))}
    if(opts.li.attr('data-aria-describedby')){$wrapper.attr('aria-describedby',opts.li.attr('data-aria-describedby'))}
    $blank.append($wrapper);$blank.first().attr('data-index',opts.index);$('.joyride-content-wrapper',$blank).append(content);return $blank[0];},timer_instance:function(index){var txt;if((index===0&&settings.startTimerOnClick&&settings.timer>0)||settings.timer===0){txt='';}else{txt=methods.outerHTML($(settings.template.timer)[0]);}
    return txt;},button_text:function(txt){if(settings.nextButton){txt=$.trim(txt)||'Next';txt=methods.outerHTML($(settings.template.button).append(txt)[0]);}else{txt='';}
    return txt;},create:function(opts){var buttonText=opts.$li.attr('data-button')||opts.$li.attr('data-text'),tipClass=opts.$li.attr('class'),$tip_content=$(methods.tip_template({tip_class:tipClass,index:opts.index,button_text:buttonText,li:opts.$li}));$(settings.tipContainer).append($tip_content);},show:function(init){var opts={},ii,opts_arr=[],opts_len=0,p,$timer=null;if(settings.$li===undefined||($.inArray(settings.$li.index(),settings.pauseAfter)===-1)){if(settings.paused){settings.paused=false;}else{methods.set_li(init);}
    settings.attempts=0;if(settings.$li.length&&settings.$target.length>0){if(init){settings.preRideCallback(settings.$li.index(),settings.$next_tip);if(settings.modal){methods.show_modal();}}
        settings.preStepCallback(settings.$li.index(),settings.$next_tip);if(settings.modal&&settings.expose){methods.expose();}
        opts_arr=(settings.$li.data('options')||':').split(';');opts_len=opts_arr.length;for(ii=opts_len-1;ii>=0;ii--){p=opts_arr[ii].split(':');if(p.length===2){opts[$.trim(p[0])]=$.trim(p[1]);}}
        settings.tipSettings=$.extend({},settings,opts);settings.tipSettings.tipLocationPattern=settings.tipLocationPatterns[settings.tipSettings.tipLocation];if(!/body/i.test(settings.$target.selector)&&settings.scroll){methods.scroll_to();}
        if(methods.is_phone()){methods.pos_phone(true);}else{methods.pos_default(true);}
        $timer=$('.joyride-timer-indicator',settings.$next_tip);if(/pop/i.test(settings.tipAnimation)){$timer.outerWidth(0);if(settings.timer>0){settings.$next_tip.show();$timer.animate({width:$('.joyride-timer-indicator-wrap',settings.$next_tip).outerWidth()},settings.timer);}else{settings.$next_tip.show();}}else if(/fade/i.test(settings.tipAnimation)){$timer.outerWidth(0);if(settings.timer>0){settings.$next_tip.fadeIn(settings.tipAnimationFadeSpeed);settings.$next_tip.show();$timer.animate({width:$('.joyride-timer-indicator-wrap',settings.$next_tip).outerWidth()},settings.timer);}else{settings.$next_tip.fadeIn(settings.tipAnimationFadeSpeed);}}
        settings.$current_tip=settings.$next_tip;$('.joyride-next-tip',settings.$current_tip).focus();methods.tabbable(settings.$current_tip);}else if(settings.$li&&settings.$target.length<1){methods.show();}else{methods.end();}}else{settings.paused=true;}},is_phone:function(){if(Modernizr){return Modernizr.mq('only screen and (max-width: 767px)');}
    return(settings.$window.width()<767)?true:false;},hide:function(){if(settings.modal&&settings.expose){methods.un_expose();}
    if(!settings.modal){$('.joyride-modal-bg').hide();}
    settings.$current_tip.hide();settings.postStepCallback(settings.$li.index(),settings.$current_tip);},set_li:function(init){if(init){settings.$li=settings.$tip_content.eq(settings.startOffset);methods.set_next_tip();settings.$current_tip=settings.$next_tip;}else{settings.$li=settings.$li.next();methods.set_next_tip();}
    methods.set_target();},set_next_tip:function(){settings.$next_tip=$('.joyride-tip-guide[data-index='+settings.$li.index()+']');},set_target:function(){var cl=settings.$li.attr('data-class'),id=settings.$li.attr('data-id'),$sel=function(){if(id){return $(settings.document.getElementById(id));}else if(cl){return $('.'+cl).first();}else{return $('body');}};settings.$target=$sel();},scroll_to:function(){var window_half,tipOffset;window_half=settings.$window.height()/2;tipOffset=Math.ceil(settings.$target.offset().top-window_half+settings.$next_tip.outerHeight());$("html, body").stop().animate({scrollTop:tipOffset},settings.scrollSpeed);},paused:function(){if(($.inArray((settings.$li.index()+1),settings.pauseAfter)===-1)){return true;}
    return false;},destroy:function(){if(!$.isEmptyObject(settings)){settings.$document.off('.joyride');}
    $(window).off('.joyride');$('.joyride-close-tip, .joyride-next-tip, .joyride-modal-bg').off('.joyride');$('.joyride-tip-guide, .joyride-modal-bg').remove();clearTimeout(settings.automate);settings={};},restart:function(){if(!settings.autoStart)
{if(!settings.startTimerOnClick&&settings.timer>0){methods.show('init');methods.startTimer();}else{methods.show('init');}
    settings.autoStart=true;}
else
{methods.hide();settings.$li=undefined;methods.show('init');}},pos_default:function(init){var half_fold=Math.ceil(settings.$window.height()/2),tip_position=settings.$next_tip.offset(),$nub=$('.joyride-nub',settings.$next_tip),nub_width=Math.ceil($nub.outerWidth()/2),nub_height=Math.ceil($nub.outerHeight()/2),toggle=init||false;if(toggle){settings.$next_tip.css('visibility','hidden');settings.$next_tip.show();}
    if(!/body/i.test(settings.$target.selector)){if(methods.bottom()){settings.$next_tip.css({top:(settings.$target.offset().top+nub_height+settings.$target.outerHeight()),left:settings.$target.offset().left-$nub.outerWidth()-1});methods.nub_position($nub,settings.tipSettings.nubPosition,'top');}else if(methods.top()){settings.$next_tip.css({top:(settings.$target.offset().top-settings.$next_tip.outerHeight()-nub_height),left:settings.$target.offset().left-$nub.outerWidth()-1});methods.nub_position($nub,settings.tipSettings.nubPosition,'bottom');}else if(methods.right()){settings.$next_tip.css({top:settings.$target.offset().top-$nub.outerHeight()-1,left:(settings.$target.outerWidth()+settings.$target.offset().left+nub_width)});methods.nub_position($nub,settings.tipSettings.nubPosition,'left');}else if(methods.left()){settings.$next_tip.css({top:settings.$target.offset().top-$nub.outerHeight()-1,left:(settings.$target.offset().left-settings.$next_tip.outerWidth()-nub_width)});methods.nub_position($nub,settings.tipSettings.nubPosition,'right');}
        if(!methods.visible(methods.corners(settings.$next_tip))&&settings.attempts<settings.tipSettings.tipLocationPattern.length){$nub.removeClass('bottom').removeClass('top').removeClass('right').removeClass('left');settings.tipSettings.tipLocation=settings.tipSettings.tipLocationPattern[settings.attempts];settings.attempts++;methods.pos_default(true);}}else if(settings.$li.length){methods.pos_modal($nub);}
    if(toggle){settings.$next_tip.hide();settings.$next_tip.css('visibility','visible');}},pos_phone:function(init){var tip_height=settings.$next_tip.outerHeight(),tip_offset=settings.$next_tip.offset(),target_height=settings.$target.outerHeight(),$nub=$('.joyride-nub',settings.$next_tip),nub_height=Math.ceil($nub.outerHeight()/2),toggle=init||false;$nub.removeClass('bottom').removeClass('top').removeClass('right').removeClass('left');if(toggle){settings.$next_tip.css('visibility','hidden');settings.$next_tip.show();}
    if(!/body/i.test(settings.$target.selector)){if(methods.top()){settings.$next_tip.offset({top:settings.$target.offset().top-tip_height-nub_height});$nub.addClass('bottom');}else{settings.$next_tip.offset({top:settings.$target.offset().top+target_height+nub_height});$nub.addClass('top');}}else if(settings.$li.length){methods.pos_modal($nub);}
    if(toggle){settings.$next_tip.hide();settings.$next_tip.css('visibility','visible');}},pos_modal:function($nub){methods.center();$nub.hide();methods.show_modal();},show_modal:function(){if($('.joyride-modal-bg').length<1){$('body').append(settings.template.modal).show();}
    if(/pop/i.test(settings.tipAnimation)){$('.joyride-modal-bg').show();}else{$('.joyride-modal-bg').fadeIn(settings.tipAnimationFadeSpeed);}},expose:function(){var expose,exposeCover,el,origCSS,randId='expose-'+Math.floor(Math.random()*10000);if(arguments.length>0&&arguments[0]instanceof $){el=arguments[0];}else if(settings.$target&&!/body/i.test(settings.$target.selector)){el=settings.$target;}else{return false;}
    if(el.length<1){if(window.console){console.error('element not valid',el);}
        return false;}
    expose=$(settings.template.expose);settings.$body.append(expose);expose.css({top:el.offset().top,left:el.offset().left,width:el.outerWidth(true),height:el.outerHeight(true)});exposeCover=$(settings.template.exposeCover);origCSS={zIndex:el.css('z-index'),position:el.css('position')};el.css('z-index',expose.css('z-index')*1+1);if(origCSS.position=='static'){el.css('position','relative');}
    el.data('expose-css',origCSS);exposeCover.css({top:el.offset().top,left:el.offset().left,width:el.outerWidth(true),height:el.outerHeight(true)});settings.$body.append(exposeCover);expose.addClass(randId);exposeCover.addClass(randId);el.data('expose',randId);settings.postExposeCallback(settings.$li.index(),settings.$next_tip,el);methods.add_exposed(el);},un_expose:function(){var exposeId,el,expose,origCSS,clearAll=false;if(arguments.length>0&&arguments[0]instanceof $){el=arguments[0];}else if(settings.$target&&!/body/i.test(settings.$target.selector)){el=settings.$target;}else{return false;}
    if(el.length<1){if(window.console){console.error('element not valid',el);}
        return false;}
    exposeId=el.data('expose');expose=$('.'+exposeId);if(arguments.length>1){clearAll=arguments[1];}
    if(clearAll===true){$('.joyride-expose-wrapper,.joyride-expose-cover').remove();}else{expose.remove();}
    origCSS=el.data('expose-css');if(origCSS.zIndex=='auto'){el.css('z-index','');}else{el.css('z-index',origCSS.zIndex);}
    if(origCSS.position!=el.css('position')){if(origCSS.position=='static'){el.css('position','');}else{el.css('position',origCSS.position);}}
    el.removeData('expose');el.removeData('expose-z-index');methods.remove_exposed(el);},add_exposed:function(el){settings.exposed=settings.exposed||[];if(el instanceof $){settings.exposed.push(el[0]);}else if(typeof el=='string'){settings.exposed.push(el);}},remove_exposed:function(el){var search;if(el instanceof $){search=el[0]}else if(typeof el=='string'){search=el;}
    settings.exposed=settings.exposed||[];for(var i=0;i<settings.exposed.length;i++){if(settings.exposed[i]==search){settings.exposed.splice(i,1);return;}}},center:function(){var $w=settings.$window;settings.$next_tip.css({top:((($w.height()-settings.$next_tip.outerHeight())/2)+$w.scrollTop()),left:((($w.width()-settings.$next_tip.outerWidth())/2)+$w.scrollLeft())});return true;},bottom:function(){return/bottom/i.test(settings.tipSettings.tipLocation);},top:function(){return/top/i.test(settings.tipSettings.tipLocation);},right:function(){return/right/i.test(settings.tipSettings.tipLocation);},left:function(){return/left/i.test(settings.tipSettings.tipLocation);},corners:function(el){var w=settings.$window,window_half=w.height()/2,tipOffset=Math.ceil(settings.$target.offset().top-window_half+settings.$next_tip.outerHeight()),right=w.width()+w.scrollLeft(),offsetBottom=w.height()+tipOffset,bottom=w.height()+w.scrollTop(),top=w.scrollTop();if(tipOffset<top){if(tipOffset<0){top=0;}else{top=tipOffset;}}
    if(offsetBottom>bottom){bottom=offsetBottom;}
    return[el.offset().top<top,right<el.offset().left+el.outerWidth(),bottom<el.offset().top+el.outerHeight(),w.scrollLeft()>el.offset().left];},visible:function(hidden_corners){var i=hidden_corners.length;while(i--){if(hidden_corners[i])return false;}
    return true;},nub_position:function(nub,pos,def){if(pos==='auto'){nub.addClass(def);}else{nub.addClass(pos);}},startTimer:function(){if(settings.$li.length){settings.automate=setTimeout(function(){methods.hide();methods.show();methods.startTimer();},settings.timer);}else{clearTimeout(settings.automate);}},end:function(){if(settings.cookieMonster){$.cookie(settings.cookieName,'ridden',{expires:365,domain:settings.cookieDomain});}
    if(settings.timer>0){clearTimeout(settings.automate);}
    if(settings.modal&&settings.expose){methods.un_expose();}
    $('.joyride-modal-bg').hide();settings.$current_tip.hide();settings.postStepCallback(settings.$li.index(),settings.$current_tip);settings.postRideCallback(settings.$li.index(),settings.$current_tip);},jquery_check:function(){if(!$.isFunction($.fn.on)){$.fn.on=function(types,sel,fn){return this.delegate(sel,types,fn);};$.fn.off=function(types,sel,fn){return this.undelegate(sel,types,fn);};return false;}
    return true;},outerHTML:function(el){return el.outerHTML||new XMLSerializer().serializeToString(el);},version:function(){return settings.version;},tabbable:function(el){$(el).on('keydown',function(event){if(!event.isDefaultPrevented()&&event.keyCode&&event.keyCode===27){event.preventDefault();methods.end();return;}
    if(event.keyCode!==9){return;}
    var tabbables=$(el).find(":tabbable"),first=tabbables.filter(":first"),last=tabbables.filter(":last");if(event.target===last[0]&&!event.shiftKey){first.focus(1);event.preventDefault();}else if(event.target===first[0]&&event.shiftKey){last.focus(1);event.preventDefault();}});}};$.fn.joyride=function(method){if(methods[method]){return methods[method].apply(this,Array.prototype.slice.call(arguments,1));}else if(typeof method==='object'||!method){return methods.init.apply(this,arguments);}else{$.error('Method '+method+' does not exist on jQuery.joyride');}};}(jQuery,this));
var koemei;

koemei = koemei || {};

koemei.JWPlayerInterface = (function() {
  function JWPlayerInterface(options, media_item) {
    var jwplayer_setup;

    this.options = options;
    this.media_item = media_item;
    jwplayer_setup = {
      modes: [
        {
          type: "flash",
          src: static_cdn_path + "/javascript/libraries/jwplayer/player.swf"
        }
      ],
      file: media_item.streaming_url,
      skin: static_cdn_path + "/widget/skins/koemei.swf",
      width: options.width,
      height: options.video_height,
      controlbar: "over",
      stretching: "fill",
      plugins: static_cdn_path + "/widget/skins/plugin.swf"
    };
    if (jwplayer_setup.file.indexOf("http://www.youtube.com/watch?v=") !== 0) {
      jwplayer_setup.provider = "rtmp";
      jwplayer_setup.streamer = usermedia_cdn_path;
    }
    if (this.options.mode === 'minimal' && this.media_item.status === 5) {
      jwplayer_setup.tracks = [
        {
          file: "https://www.koemei.com/REST/transcripts/" + this.media_item.current_transcript_uuid + ".srt",
          kind: "captions",
          label: "English",
          "default": true
        }
      ];
      console.log(jwplayer_setup);
    }
    this.player = jwplayer("widget_player").setup(jwplayer_setup);
  }

  JWPlayerInterface.prototype.play = function() {
    return this.player.play();
  };

  JWPlayerInterface.prototype.pause = function(flag) {
    return this.player.pause(true);
  };

  JWPlayerInterface.prototype.seek = function(pos) {
    return this.player.seek(pos);
  };

  JWPlayerInterface.prototype.getState = function() {
    return this.player.getState();
  };

  JWPlayerInterface.prototype.getPosition = function(callback) {
    return this.player.getPosition(callback);
  };

  JWPlayerInterface.prototype.onTime = function(callback) {
    return this.player.onTime(callback);
  };

  JWPlayerInterface.prototype.onSeek = function(callback) {
    return this.player.onSeek(callback);
  };

  JWPlayerInterface.prototype.onPlay = function(callback) {
    return this.player.onPlay(callback);
  };

  JWPlayerInterface.prototype.onPause = function(callback) {
    return this.player.onPause(callback);
  };

  return JWPlayerInterface;

})();

var koemei;

koemei = koemei || {};

koemei.KalturaPlayerInterface = (function() {
  function KalturaPlayerInterface(options, media_item) {
    this.options = options;
    this.media_item = media_item;
    this.player = document.getElementById(this.options.player_id);
    this.status = "UNDEFINED";
  }

  KalturaPlayerInterface.prototype.play = function() {
    console.log("PLAY");
    if ($("#widget_play").hasClass("kw_t_pause")) {
      return this.player.sendNotification("doPause");
    } else {
      return this.player.sendNotification("doPlay");
    }
  };

  KalturaPlayerInterface.prototype.pause = function(flag) {
    console.log("PAUSE");
    return this.player.sendNotification("doPause");
  };

  KalturaPlayerInterface.prototype.seek = function(pos) {
    return this.player.sendNotification("doSeek", pos);
  };

  KalturaPlayerInterface.prototype.getState = function() {
    return this.status;
  };

  KalturaPlayerInterface.prototype.getPosition = function(callback) {
    return this.player.evaluate("{video.player.currentTime}");
  };

  KalturaPlayerInterface.prototype.onTime = function(callback) {
    return this.player.kBind("playerUpdatePlayhead.koemeiOnPage", callback);
  };

  KalturaPlayerInterface.prototype.onSeek = function(callback) {
    return this.player.kBind("playerSeekEnd.koemeiOnPage", callback);
  };

  KalturaPlayerInterface.prototype.onPlay = function(callback) {
    return this.player.kBind("playerPlayed.koemeiOnPage", callback);
  };

  KalturaPlayerInterface.prototype.onPause = function(callback) {
    return this.player.kBind("playerPaused.koemeiOnPage", callback);
  };

  KalturaPlayerInterface.prototype.onStateChange = function(callback) {
    return this.player.kBind("playerStateChange.koemeiOnPage", callback);
  };

  KalturaPlayerInterface.onStateChangeCallback = function(state) {
    console.log("state change from " + this.status + " to " + state);
    this.status = state;
    return console.log(this.status);
  };

  return KalturaPlayerInterface;

})();

var transcript_uuid = '';
var auth = false;
var static_cdn_path = 'http://damm4k358vhk1.cloudfront.net';
//var usermedia_cdn_path = 'rtmp://usermedia.preprod.koemei.com/cfx/st/';
var usermedia_cdn_path = 'rtmp://usermedia.koemei.com/cfx/st/';
var published = false;
var koemei_css_path = 'dist/koemei-widget.css';
var koemei_host = "https://www.koemei.com";
var ZeroClipboardPath = "/widget/ZeroClipboard.swf";
function Controls() {
    /**
     * Initialization of the controls container
     */
    this.init = function init(xhr, config, transcript) {
        this.transcript = transcript;

		config.readonly = typeof config.readonly !== 'undefined' ? config.readonly : false;
		if (config.background_save) {
        	var save_button = '<a id="kw_save-button" class="' + config.save_button_class + ' background_save" href="#">SAVE</a>';
		} else {
        	var save_button = '<a id="kw_save-button" class="' + config.save_button_class + '" href="#">SAVE</a>';
		}
		if (config.show_exit) {
			var publish_button = '<a id="kw_publish-button" class="' + config.publish_button_class + '" href="#">EXIT</a>';
			if (config.service === 'kaltura'){

                if (config.mode === 'edit_review')
				    publish_button = '';
                else
                    publish_button = '<a id="kw_publish-button" class="' + config.publish_button_class + '" href="#">FINISH</a>';
            }
			if (config.published) {
				publish_button = '<a id="kw_publish-button" class="' + config.publish_button_class + '" href="#">FINISH</a>';
			}
		} else {
			var publish_button = '';
		}
        // now it's recived from config
		var timezone_dif = new Date(config.last_save).getTimezoneOffset();
		var time = new Date(config.last_save).getTime()/1000;
		var ua = $.browser;
		if (ua.mozilla) {
			timezone_dif = timezone_dif*60;
			if (timezone_dif<0) { 
				time = time - timezone_dif;
			} else {
				time = time + timezone_dif;
			}
		}
        var save_info = "Last saved  <strong class='timeago' data-livestamp='"+time+"'></strong>";
		if (!config.readonly) {
            $(".kw_edit-controls").append(save_button).append('<span>' + save_info + '</span>').append(publish_button);
		}
        var modal = '';
        var go_save = 0;
        $("#kw_confirm_popup a").click(function (event) {
			var _this = this;
			transcript.save(xhr, config.auth,function() {console.log('saved');});
			//console.log(this);
			//console.log(self);
            var action = $(this).attr('data-action');
            if (action === "YES") {
               
                if (modal === "save") {
                   transcript.save(xhr, config.auth,function() {console.log('saved');});
                }
                if (modal === "publish") {
                    $("#kw_publish-button").click();
                }
            }
            $('#kw_confirm_popup').hide();
        });


		
        $("#kw_save-button").on('click',function(event) {
			if (!$(this).hasClass('background_save')) {
                modal = 'save';
                event.preventDefault();
                if (go_save === 0) {
                    $('#kw_confirm_popup').show();
                }
				if (go_save === 1) {
					go_save = 0;
				}	
			}
            var _this = $(this);
            if (window.submitForm || typeof window.submitForm === 'undefined') {
                _this.addClass('kw_loading_bg');
				_this.addClass('btn-disabled');
                try {
                    //window.submitForm = false;
                    //console.log(transcript)
                    transcript.save(xhr, config.auth,function() { console.log('saved');});
                    setTimeout(function () {
                        _this.removeClass('btn-disabled');
						_this.removeClass('kw_loading_bg');
                    }, 200);
                }
                catch (e) {
                    console.error("Error saving modifications : " + e.message + "(" + e.lineNumber + ")");
                }
            }
            return false;
        });

        // Seb:  disable Zeroclipboaerds because it is painful
		//ZeroClipboard.setDefaults( { moviePath: ZeroClipboardPath } );
		$('.kw_copy_segment').each(function() {
			contents = $('#transcript_container').text();
			$(this).attr('data-clipboard-text',contents);
            // Seb:  disable Zeroclipboaerds because it is painful
			var clip = new ZeroClipboard($(this));
			clip.setHandCursor(true);
			clip.glue(this);
			clip.setText(contents);
			clip.on( 'mouseover', function ( client, args ) {
			  $(this).css('display','block');
			});
			clip.on( 'mouseout', function ( client, args ) {
				$(this).css('display','');
			});
		});
		
		$("#kw_publish-button").click(function () {
			var text = $(this).text();
            // TODO : create 2 different popus : publish vs exit
			if (text==="EXIT" || text==="PUBLISH") {
				$('#kw_exit_popup').show();			
			}
			if (text==="UNPUBLISH") {
				transcript.media.unpublish(xhr);
				$('#kw_notify_popup div h2').html('The captions have been unpublished');
           		 $('#kw_notify_popup').show();
				 $(this).text('EXIT');
			}
		});
    };
    /*
     * Display control panel
     * */
    this.display = function display() {
    };
}
var koemei;

koemei = koemei || {};

koemei.Label = (function() {
  function Label(value, start, end, confidence, transcript) {
    this.value = value;
    this.start = start;
    this.end = end;
    this.confidence = confidence;
    this.transcript = transcript;
    this.dom_element = $('#l_' + this.start);
  }

  /*
  On click behaviour of  label element
  * @param {DOMelement} label element
  * @return {boolean} False
  */


  Label.on_click = function(label_element, segment) {
    var err, time;

    try {
      segment.transcript.player.pause(true);
      segment.transcript.segmentClick = false;
      time = parseInt(label_element.attr("id").slice(2), 10) + 1;
      segment.go(time);
      return false;
    } catch (_error) {
      err = _error;
      return console.log("Error binding function to label on click : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Convert js of label ot an html label
  * @param {json} json label representation
  * @return {html} html label representation
  */


  Label.to_html = function(label_json) {
    var err;

    try {
      return '<div id="l_' + label_json.start + '" class="label">' + label_json.value + '</div> ';
    } catch (_error) {
      err = _error;
      return console.log("Error converting label from json to html : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Set the label as fixed : confidence goes to 1 and is not underlined anymore
  */


  Label.prototype.set_fixed = function() {
    this.confidence = 1;
    return this.dom_element.removeClass('kw_low_confidence_label');
  };

  /*
  Return the class name with the corresponding background color according to confidence level
  * @param {double} label confidence
  * @return {String} css class
  TODO : this function should not be static
  */


  Label.get_confidence_color = function(confidence) {
    var label_color, tolerance;

    confidence = parseFloat(confidence);
    label_color = "";
    tolerance = 0.50;
    if (confidence < tolerance) {
      label_color = "kw_low_confidence_label";
    }
    return label_color;
  };

  return Label;

})();

var koemei;

koemei = koemei || {};

koemei.Segment = (function() {
  var srt;

  srt = "";

  function Segment(transcript, labels_json, index, speaker) {
    var label_json, _i, _len;

    this.transcript = transcript;
    this.index = index;
    this.speaker = speaker;
    this.labels = [];
    for (_i = 0, _len = labels_json.length; _i < _len; _i++) {
      label_json = labels_json[_i];
      this.labels.push(new koemei.Label(label_json.value, label_json.start, label_json.end, label_json.confidence, label_json.transcript));
    }
    this.start = this.labels[0].start;
    this.end = this.labels[this.labels.length - 1].end;
    this.dom_element = $('#s_' + this.start);
  }

  /*
  Get cleaned up html representation of segment
  * @param {DOMelement} html representation of segment, may contain dirty stuff
  * @return {DOMelement} cleaned up representation of the segment
  
  cleanup_html : (dirty_segment) ->
    try
      clean_segment = dirty_segment
  
      $(dirty_segment).html clean_segment
      @init_labels(dirty_segment)
  
    catch err
      console.log "Error cleaning segment html : " + err.message + "(" + err.lineNumber + ")"
  */


  /*
  Initialise labels
  * @param {DOMelement} html representation of segment
  */


  Segment.prototype.init_labels = function() {
    var err, label, label_element, _i, _len, _ref, _results,
      _this = this;

    try {
      _ref = this.labels;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        label = _ref[_i];
        label_element = $("#l_" + label.start);
        _results.push(label_element.unbind("click").click((function(label_element) {
          return function() {
            return koemei.Label.on_click(label_element, _this);
          };
        })(label_element)));
      }
      return _results;
    } catch (_error) {
      err = _error;
      return console.log("Error init segment labels : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Get an object representation of a segment from an html representation
  * @param {DOMelement} html representation of segment
  * @return {koemei.segment} segment object obtained from html input
  */


  Segment.get_from_html = function(segment_html) {
    var err, label, label_index, label_length, label_start, labels, new_segment, old_label, segment_duration, segment_text, _i, _len;

    try {
      segment_duration = parseInt(segment_html.attr("seg-end"), 10) - parseInt(segment_html.attr("seg-start"), 10);
      segment_text = segment_html.text().trim();
      new_segment = [];
      labels = segment_text.split(/\s+/);
      label_length = parseInt(segment_duration / labels.length, 10);
      label_start = parseInt(segment_html.attr("seg-start"), 10);
      label_index = 1;
      label = {};
      for (_i = 0, _len = labels.length; _i < _len; _i++) {
        old_label = labels[_i];
        if (old_label.trim() !== "") {
          label = {
            start: label_start,
            end: label_start + label_length,
            confidence: 1,
            id: label_index,
            value: old_label.trim()
          };
          label_start = label.end;
          label_index++;
          new_segment.push(label);
        }
      }
      if (new_segment.length === 0) {
        label = {
          start: parseInt(segment_html.attr("seg-start"), 10),
          end: label_start + label_length,
          confidence: 1,
          id: label_index,
          value: " "
        };
        new_segment.push(label);
      }
    } catch (_error) {
      err = _error;
      console.log("Error getting segment from html : " + err.message + "(" + err.lineNumber + ")");
    }
    return new_segment;
  };

  /*
  Convert js of segment ot an html segment
  * @param {json} json segment representation
  * @return {html} html segment representation
  */


  Segment.prototype.to_html = function(segment_json) {
    var err, label, segment_html, _i, _len;

    try {
      segment_html = '';
      for (_i = 0, _len = segment_json.length; _i < _len; _i++) {
        label = segment_json[_i];
        segment_html = segment_html + koemei.Label.to_html(label);
      }
      return segment_html;
    } catch (_error) {
      err = _error;
      return console.log("Error converting segment from json to html : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Create subtitles for sergment labels
  * @return {html} subtitles text
  */


  Segment.prototype.to_srt = function() {
    var changes, current_start, label, overwrite, _i, _len, _ref;

    current_start = this.transcript.current_segment.start;
    changes = this.transcript.changes;
    overwrite = null;
    $.each(changes, function(index, value) {
      if (value.start === current_start) {
        overwrite = value.labels;
        0;
      }
      return 0;
    });
    srt = "";
    if (!overwrite) {
      _ref = this.labels;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        label = _ref[_i];
        srt += label.value.trim() + " ";
      }
      srt = srt.trim();
    } else {
      $.each(overwrite, function(index, label) {
        return srt += label.value.trim() + " ";
      });
      srt = srt.trim();
    }
    return $('#kw_subtitles').text(srt);
  };

  /*
  On change listener
  @on_change : () ->
    $('body')
      .on 'focus', '[contenteditable]', ->
        $this = $(this)
        $this.data 'before', $this.html()
        return $this
      .on 'blur keyup paste', '[contenteditable]', ->
        $this = $(this)
        if $this.data('before') isnt $this.html()
          $this.data 'before', $this.html()
          segment.update_handler $this
        return $this
  */


  /*
  Go to the segment
  * @param {time} start of the segment clicked
  */


  Segment.prototype.go = function(start) {
    if (start !== parseInt(this.transcript.current_segment.start, 10)) {
      $("div.kw_segment-content > div.kw_current").removeClass("kw_current");
      this.transcript.player.segment_nav_on = false;
      return this.transcript.set_current_segment(start);
    }
  };

  /*
  On click event listener
  */


  Segment.prototype.on_click = function() {
    var _this;

    _this = this;
    $("#s_" + this.start).on('click', (function(event) {
      var flag;

      console.log("bind1");
      flag = true;
      $(this).find("div.label").each(function() {
        if ($(this).offset().left > event.pageX && ($(this).offset().top < event.pageY && $(this).offset().top + $(this).outerHeight() > event.pageY) && flag) {
          flag = false;
          return $(this).trigger("click");
        }
      });
      if (flag) {
        _this.go(parseInt($(this).find("div.label:eq(0)").attr("id").slice(2), 10) + 1);
      }
      return false;
    }));
    return $("#seg_div_" + this.start).on('click', (function(event) {
      console.log("bind2");
      _this.go(parseInt($(this).find("div.label:eq(0)").attr("id").slice(2), 10) + 1);
      return false;
    }));
  };

  /*
  Event handler for segment update
  * @param {html} html segment representation
  */


  Segment.prototype.update_handler = function(segment_html) {
    this.new_value = koemei.Segment.get_from_html(segment_html);
    this.transcript.register_changes();
    this.transcript.calc_quality();
    this.transcript.current_label.set_fixed();
    return this.to_srt();
  };

  /*
  Display the segment as html
  * @param {bool} readonly display
  * @param {bool} display_speaker
  */


  Segment.prototype.display = function(readonly, display_speaker) {
    var class_readonly, contentEditable, div, h, label, _i, _len, _ref;

    contentEditable = readonly ? 'false' : 'true';
    class_readonly = readonly ? 'kw_readonly_segment' : '';
    div = [];
    h = 0;
    div[h++] = '<div class="kw_tr-segment ' + class_readonly + '" id="seg_div_' + this.start + '">';
    if (!readonly) {
      div[h++] = '<div class="kw_time" id="st_' + this.start + '"><strong>' + timestamp_to_mmss(this.start) + '</strong>';
    }
    if (display_speaker) {
      div[h++] = '<span class="speaker">' + this.speaker + '</span>';
    }
    if (!readonly) {
      div[h++] = '</div>';
    }
    div[h++] = '<div class="kw_text-body kw_segment-content" contenteditable="' + contentEditable + '" ' + 'id="s_' + this.start + '" ' + 'seg-id="' + this.index + '" ' + 'seg-start="' + this.start + '" ' + 'seg-end="' + this.end + '">';
    _ref = this.labels;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      label = _ref[_i];
      div[h++] = '<div id="l_' + label.start + '" class="label ' + koemei.Label.get_confidence_color(label.confidence) + '">' + label.value + '</div> ';
    }
    div[h++] = '</div>';
    div[h++] = '</div>';
    return div.join('');
  };

  return Segment;

})();

var koemei;

koemei = koemei || {};

koemei.Media = (function() {
  /*
  Constructor for the koemei.Media object
  * @param {uuid} media uuid (can ben external or koemei uuid)
  * @param {json} information on the media
  */
  function Media(uuid, status, progress, access_level, kuuid, current_transcript_uuid, streaming_url, publish_status, created, title, description, authors, categories, duration, processing_time) {
    this.uuid = uuid;
    this.status = status;
    this.progress = progress;
    this.access_level = access_level;
    this.current_transcript_uuid = current_transcript_uuid;
    this.streaming_url = streaming_url;
    this.publish_status = publish_status;
    this.created = created;
    this.title = title;
    this.description = description;
    this.authors = authors;
    this.categories = categories;
    this.duration = duration;
    this.processing_time = processing_time;
    this.external_uuid = this.uuid !== kuuid ? this.uuid : null;
    this.uuid = kuuid;
  }

  /*
  Is the media transcribed
  */


  Media.prototype.is_transcribed = function() {
    if (this.status === 2 || this.status === 4 || this.status === 6 || (this.status === 1 && this.progress === !100)) {
      return false;
    } else {
      return true;
    }
  };

  /*
  Is the media private
  */


  Media.prototype.is_private = function() {
    if (this.access_level === 0) {
      return true;
    } else {
      return false;
    }
  };

  /*
  Publish the media current transcript
  * @param {xhr} crossdomain magic
  * @param {service} service to publish the captions to
  * @param {url} success callback url that will be called by koemei server once publish process finished
  * @param {url} error callback url that will be called by koemei server if process publish bugs
  * @param {basicauth} authorization for the user
  * @param {function} success_callback : success callback function
  * @param {function} error_callback : error callback function
  */


  Media.prototype.publish = function(xhr, service, auth, success_callback_url, error_callback_url, success_callback, error_callback) {
    var err, req;

    try {
      console.log(this);
      if (this.publish_status) {
        return this.unpublish(xhr, service, auth, success_callback_url, error_callback_url, success_callback, error_callback);
      } else {
        req = {
          url: "../REST/media/" + this.uuid + "/publish",
          method: "PUT",
          headers: {
            Authorization: auth,
            Accept: "application/json"
          },
          data: {
            service_name: service,
            success_callback_url: success_callback_url,
            error_callback_url: error_callback_url
          }
        };
        return xhr.request(req, (function(response) {
          return success_callback(jQuery.parseJSON(response.data));
        }), function(response) {
          return console.error("Error in koemei.Media.publish : " + response);
        });
      }
    } catch (_error) {
      err = _error;
      return console.error("Error in koemei.Media.publish : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Unublish the media current transcript
  * @param {xhr} crossdomain magic
  * @param {service} service to publish the captions to
  * @param {url} success callback url that will be called by koemei server once unpublish process finished
  * @param {url} error callback url that will be called by koemei server if process unpublish bugs
  * @param {basicauth} authorization for the user
  * @param {function} success_callback : success callback function
  * @param {function} error_callback : error callback function
  */


  Media.prototype.unpublish = function(xhr, service, auth, success_callback_url, error_callback_url, success_callback, error_callback) {
    var err, req;

    try {
      req = {
        url: "../REST/media/" + this.uuid + "/unpublish",
        method: "PUT",
        headers: {
          Authorization: auth,
          Accept: "application/json"
        },
        data: {
          service_name: service,
          success_callback_url: success_callback_url,
          error_callback_url: error_callback_url
        }
      };
      return xhr.request(req, (function(response) {
        return success_callback(jQuery.parseJSON(response.data));
      }), function(response) {
        return console.error("Error in koemei.Media.unpublish : " + response);
      });
    } catch (_error) {
      err = _error;
      return console.error("Error in koemei.Media.unpublish : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Get the specified media
  * @param {xhr} crossdomain magic
  * @param {String} uuid : media identifier
  * @param {String} auth : authentification
  * @param {function} success_callback : success callback function
  * @param {function} error_callback : error callback function
  */


  Media.get_one = function(xhr, uuid, auth, success_callback, error_callback) {
    var err, req;

    try {
      req = {
        url: "../REST/media/" + uuid,
        method: "GET",
        headers: {
          Authorization: auth,
          Accept: 'application/json'
        }
      };
      return xhr.request(req, (function(response) {
        var info, media_item;

        info = jQuery.parseJSON(response.data).media_item;
        media_item = new koemei.Media(uuid, info.status, info.progress, info.access_level, info.uuid, info.current_transcript_uuid, info.streaming_url, info.publish_status, info.created, info.title, info.description, info.authors, info.categories, info.size, 150);
        return success_callback(media_item);
      }), function(response) {
        return console.error("Error in koemei.Media.get_one : " + response);
      });
    } catch (_error) {
      err = _error;
      return console.error("Error in koemei.Media.get_one : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Get a list of media items
  * @param {xhr} crossdomain magic
  * @param {String} criteria is the list of criteria for the request
  * @param {String} user_uuid : user id
  * @param {String} auth : authentification
  * @param {function} success_callback : success callback function
  * @param {function} error_callback : error callback function
  TODO : this should return koemei.Media objects, not raw json
  */


  Media.get_all = function(xhr, criteria, auth, user_uuid, success_callback, error_callback) {
    var err, req, uid;

    try {
      uid = user_uuid ? "&user_uuid=" + user_uuid : "";
      req = {
        url: "../REST/media" + urlencodeQuery(criteria) + uid,
        method: "GET",
        headers: {
          Authorization: auth,
          Accept: "application/json"
        }
      };
      return xhr.request(req, (function(response) {
        return success_callback(jQuery.parseJSON(response.data));
      }), function(response) {
        return console.error("Error in koemei.Media.get_all : " + response);
      });
    } catch (_error) {
      err = _error;
      return console.error("Error in koemei.Media.get_all : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  return Media;

})();

var koemei;

koemei = koemei || {};

koemei.Transcript = (function() {
  /*
  Constructor for the koemei.Transcript object
  * @param {uuid} transcript uuid
  * @param {bool} draft
  */
  function Transcript(uuid, draft, media, player, segments_json, confidence, created, updated) {
    var index, segment_json, _i, _len;

    this.uuid = uuid;
    this.draft = draft;
    this.media = media;
    this.player = player;
    this.confidence = confidence;
    this.created = created;
    this.segments = [];
    index = 0;
    for (_i = 0, _len = segments_json.length; _i < _len; _i++) {
      segment_json = segments_json[_i];
      this.segments.push(new koemei.Segment(this, segment_json.labels, index, segment_json.speaker));
      index++;
    }
    this.current_label = this.segments[0].labels[0];
    this.current_segment = this.segments[0];
    this.changes = [];
    this.quality = Math.round(this.confidence * 100);
    this.updated = updated != null ? updated.split('.')[0] : this.created.split('.')[0];
    this.left_key = 0;
    this.right_key = 0;
  }

  /*
  Get the specified transcript
  * @param {String} transcript identifier
  * @param {String} stage (draft or current)
  * @param {koemei.Media} media object
  * @param {koemei.Player} player object
  * @param {String} auth : authentification
  * @param {function} success_callback : success callback function
  * @param {function} error_callback : error callback function
  */


  Transcript.get_one = function(xhr, transcript_uuid, stage, media, player, auth, success_callback, error_callback) {
    var err, request_url;

    if (stage) {
      request_url = "../REST/transcripts/" + transcript_uuid + "?stage=" + stage;
    } else {
      request_url = "../REST/transcripts/" + transcript_uuid;
    }
    try {
      return xhr.request({
        url: request_url,
        method: "GET",
        headers: {
          Authorization: auth,
          Accept: "application/json"
        }
      }, (function(response) {
        var resp, transcript;

        resp = jQuery.parseJSON(response.data);
        transcript = new koemei.Transcript(resp._id, stage, media, player, resp.segmentation, resp.confidence, resp.created.replace('"', ''), resp.updated != null ? resp.updated.replace('"', '') : null);
        return success_callback(transcript);
      }), function(response) {
        return console.error("Error in getTranscript : " + response);
      });
    } catch (_error) {
      err = _error;
      return console.error("Error in getTranscript : " + err.message + "(" + err.lineNumber + ")");
    }
  };

  /*
  Display the segment
  * @param {bool} readonly flag : display the segment as readonly
  * @param {bool} display flag : display speakers or not
  */


  Transcript.prototype.display = function(readonly, display_speaker) {
    var div, h, old_spk, segment, shown, transcript, _i, _j, _len, _len1, _ref, _ref1;

    $(".kw_transcription_toolbar .kw_progress").show();
    this.update_quality();
    h = 0;
    div = [];
    _ref = this.segments;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      segment = _ref[_i];
      if (display_speaker && readonly) {
        if (old_spk !== segment.speaker) {
          div[h++] = "<br><br>";
          old_spk = segment.speaker;
        }
      }
      div[h++] = segment.display(readonly, display_speaker);
    }
    $("#kw_transcript_container").append(div.join(""));
    $('#kw_transcript_container').customScrollV({
      lineWidth: 7
    });
    shown = false;
    setInterval(function() {
      if ($("#kw_transcript_container").is(":visible") && !shown) {
        $('#kw_transcript_container').customScrollV({
          lineWidth: 7
        });
        shown = true;
        return 0;
      }
    }, 500);
    _ref1 = this.segments;
    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
      segment = _ref1[_j];
      segment.init_labels();
      segment.on_click();
    }
    transcript = this;
    $('#kw_transcript_container').on('focus', '[contenteditable]', function() {
      var $this;

      $this = $(this);
      $this.data('before', $this.text());
      return $this;
    }).on('blur keyup paste', '[contenteditable]', function() {
      var $this;

      $this = $(this);
      if ($this.data('before') !== $this.text()) {
        $this.data('before', $this.text());
        $(this).parent().find('br').replaceWith(' ');
        transcript.current_segment.update_handler($this);
        return $this;
      }
    }).on('keyup paste', '[contenteditable]', function() {
      var $this;

      $this = $(this);
      return $(this).children('.kw_current').removeClass('kw_low_confidence_label');
    });
    return false;
  };

  /*
  Save the transcript
  */


  Transcript.prototype.save_version = function(xhr, auth, success_callback, error_callback) {
    var ex, req,
      _this = this;

    try {
      req = {
        url: "../REST/transcripts/" + this.uuid + "/save_version",
        method: "PUT",
        timeout: 500000,
        headers: {
          Authorization: auth,
          Accept: 'application/json'
        }
      };
      return xhr.request(req, (function(response) {}), function(response) {
        return console.error("Error saving transcript : " + response);
      });
    } catch (_error) {
      ex = _error;
      return console.error("Exception saving transcript version " + ex + "\n" + this.changes);
    }
  };

  /*
  Create a version for the transcript
  */


  Transcript.prototype.save = function(xhr, auth, success_callback, error_callback) {
    var change, ex, label, req, _i, _j, _len, _len1, _ref,
      _this = this;

    try {
      if (this.changes.length > 0) {
        _ref = this.changes;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          change = _ref[_i];
          for (_j = 0, _len1 = change.length; _j < _len1; _j++) {
            label = change[_j];
            label.value = change.value.replace(/\"/g, "\\\"");
          }
        }
        req = {
          url: "../REST/transcripts/" + this.uuid,
          method: "POST",
          data: {
            transcript_content: JSON.stringify(this.changes)
          },
          timeout: 500000,
          headers: {
            Authorization: auth,
            Accept: 'application/json'
          }
        };
        return xhr.request(req, (function(response) {
          var resp, timestamp;

          resp = jQuery.parseJSON(response.data);
          timestamp = new Date().getTime();
          timestamp = timestamp / 1000;
          $(".kw_edit-controls span strong").attr("data-livestamp", timestamp);
          _this.updated = timestamp;
          _this.changes = [];
          _this.save_version(xhr, auth, success_callback, error_callback);
          return success_callback(xhr, auth);
        }), function(response) {
          return console.error("Error saving transcript : " + response);
        });
      } else {

      }
    } catch (_error) {
      ex = _error;
      return console.error("Exception saving transcripr changes " + ex + "\n" + this.changes);
    }
  };

  /*
  Get the segment corresponding to the a given time
  * @param {number} time in milliseconds
  * @return {koemei.Segment} segment corresponding to time, or the first segment if none found
  */


  Transcript.prototype.get_segment = function(time) {
    var ex, found, i;

    try {
      found = false;
      i = 0;
      while (i < this.segments.length && !found) {
        if (parseInt(this.segments[i].start, 10) <= parseInt(time, 10) && parseInt(this.segments[i].end, 10) > parseInt(time, 10)) {
          found = this.segments[i];
        }
        i++;
      }
    } catch (_error) {
      ex = _error;
      console.error("Exception getting segment for time " + time(+" : " + ex));
    }
    if (!found) {
      console.log("No segment found for time " + time + ". Returning to first segment");
      found = this.current_segment;
    }
    return found;
  };

  /*
  Get the label corresponding to a given time
  * @param {number} time in milliseconds
  * @return {koemei.Label} label corresponding to time, or the first label in the current segment if none found
  */


  Transcript.prototype.get_label = function(time) {
    var ex, found, i;

    try {
      found = false;
      i = 0;
      while (i < this.current_segment.labels.length && !found) {
        if (parseInt(this.current_segment.labels[i].start, 10) <= parseInt(time, 10) && parseInt(this.current_segment.labels[i].end, 10) >= parseInt(time, 10)) {
          found = this.current_segment.labels[i];
        } else if (parseInt(this.current_segment.labels[i].start, 10) >= parseInt(time, 10)) {
          found = this.current_segment.labels[i - 1];
        }
        i++;
      }
    } catch (_error) {
      ex = _error;
      console.error("Exception getting label for time " + time(+" : " + ex));
    }
    if (!found) {
      console.log("No label found around time " + time);
      found = this.current_label;
    }
    return found;
  };

  /*
  Compute quality for the transcript
  */


  Transcript.prototype.calc_quality = function() {
    var label, nb_labels, segment, total_confidence, _i, _j, _len, _len1, _ref, _ref1;

    if (this.changes.length > 0) {
      nb_labels = 0;
      total_confidence = 0;
      _ref = this.segments;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        segment = _ref[_i];
        _ref1 = segment.labels;
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          label = _ref1[_j];
          nb_labels++;
          total_confidence += parseFloat(label.confidence);
        }
      }
      this.quality = Math.round(total_confidence / nb_labels * 100);
      return this.update_quality();
    }
  };

  /*
  Update quality display
  */


  Transcript.prototype.update_quality = function() {
    $(".kw_progress").attr("title", "Progress : " + this.quality + "%");
    $(".kw_transcription_toolbar span").css("width", this.quality + "%");
    $(".kw_transcription_toolbar strong").html(this.quality + "%");
    if (this.quality > 56) {
      return $(".kw_transcription_toolbar strong").addClass("white");
    }
  };

  /*
  Get the changes to the transcript
  */


  Transcript.prototype.register_changes = function() {
    var err, i, new_segment_changed, segment, _i, _len, _ref, _results;

    try {
      _ref = this.segments;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        segment = _ref[_i];
        if (segment.new_value) {
          new_segment_changed = true;
          i = 0;
          while (i < this.changes.length) {
            if (this.changes[i].start === segment.start) {
              new_segment_changed = false;
              this.changes[i] = {
                labels: segment.new_value,
                start: segment.start
              };
            }
            i++;
          }
          if (new_segment_changed) {
            this.changes.push({
              labels: segment.new_value,
              start: segment.start
            });
          }
          segment.new_value = null;
          _results.push(false);
        } else {
          _results.push(void 0);
        }
      }
      return _results;
    } catch (_error) {
      err = _error;
      return console.error("Exception getting changes " + err);
    }
  };

  /*
  Move scroll to position
  @param top {number} top of scroll.
  @param callback {function} callback function.
  */


  Transcript.prototype.move_scroll_to_position = function(top, callback) {
    var el, height, kw_transcript_container, readonly, scrollContent;

    kw_transcript_container = $("#kw_transcript_container");
    scrollContent = $("#kw_transcript_container .kw_scroll-content");
    el = $("#kw_transcript_container").find(".kw_current-segment");
    readonly = $("#kw_transcript_container").find(".kw_readonly_scroll");
    height = 0;
    if (readonly.length > 0) {
      height = 10;
    } else {
      if (el.length > 0) {
        el = $(el).parent();
        height = parseInt($(el).height());
      } else {
        el = $("#kw_transcript_container").find(".kw_tr-segment:first");
        height = parseInt($(el).height());
      }
    }
    if (top - 0 > scrollContent.outerHeight() - kw_transcript_container.outerHeight()) {
      top = scrollContent.outerHeight() - kw_transcript_container.outerHeight() + 0;
    }
    if (-top + 0 < 0) {
      return scrollContent.animate({
        top: -top + height
      }, 300, function() {
        kw_transcript_container.customScrollV();
        if (typeof callback === "function") {
          return callback.call();
        }
      });
    } else {
      return scrollContent.animate({
        top: 0
      }, 300, function() {
        kw_transcript_container.customScrollV();
        if (typeof callback === "function") {
          return callback.call();
        }
      });
    }
  };

  /*
  Handle current segment change
  @param time {number} time in millisecs of the playback position.
  */


  Transcript.prototype.set_current_segment = function(time) {
    var element_position, err;

    this.left_key = 0;
    this.right_key = 0;
    try {
      if (this.startFrom !== this.current_segment.start) {
        $(".kw_current-segment").removeClass("kw_current-segment");
        this.current_segment = this.get_segment(time);
        this.current_segment.to_srt();
        if (!this.segmentClick) {
          this.move_scroll_to_position($("#s_" + this.current_segment.start).parents("div.kw_tr-segment").position().top);
        }
        $("#s_" + this.current_segment.start).addClass("kw_current-segment");
        $("#s_" + this.current_segment.start).focus();
        element_position = parseInt($('.kw_current-segment').parent().position().top);
        this.startFrom = this.current_segment.start;
        if (element_position === 0) {
          $("#kw_transcript_container").css("background-position", "left -34px");
        } else {
          $("#kw_transcript_container").css("background-position", "left 0%");
        }
      } else {
        element_position = parseInt($('.kw_current-segment').parent().position().top);
        if (element_position === 0) {
          $("#kw_transcript_container").css("background-position", "left -34px");
        } else {
          $("#kw_transcript_container").css("background-position", "left 0%");
        }
      }
    } catch (_error) {
      err = _error;
      console.error("Error setting current segment : " + err.message + "(" + err.lineNumber + ")");
    }
    try {
      if (time <= this.current_segment.start || time >= this.current_segment.end) {
        $(".kw_current-segment").removeClass("kw_current-segment");
        this.current_segment = this.get_segment(time);
        $("#s_" + this.current_segment.start + ", #shm_" + this.current_segment.start).addClass("kw_current-segment");
        $("#s_" + this.current_segment.start).focus();
        if (!this.segmentClick) {
          this.move_scroll_to_position($("#s_" + this.current_segment.start).parents("div.kw_tr-segment").position().top);
        }
      }
    } catch (_error) {
      err = _error;
      console.error("Error setting current segment : " + err.message + "(" + err.lineNumber + ")");
    }
    this.set_current_label(time);
    return $(".kw_koemei_widget").scrollTop(0);
  };

  /*
  Set the current label
  * @param time {number} time in millisecs of the playback position.
  */


  Transcript.prototype.set_current_label = function(time) {
    var cur_segment, ind, new_label;

    new_label = this.get_label(time);
    if ($("#l_" + new_label.start).length > 0) {
      $("#l_" + this.current_label.start).removeClass("kw_current");
      this.current_label = new_label;
      selectText("l_" + this.current_label.start);
      $("#l_" + this.current_label.start).addClass("kw_current");
      if ($("#transcript_pannel_containter").hasClass("kw_readonly_pannel")) {
        cur_segment = $(".kw_current");
        cur_segment = cur_segment[0];
        $(".label").removeClass("kw_shown");
        ind = $(".label").index(cur_segment);
        return $(".label").slice(0, ind).addClass("kw_shown");
      }
    } else {
      console.error("koemei.Transcript.set_current_label : No label found for time " + time);
      return console.error("approx was label " + new_label.start);
    }
  };

  return Transcript;

})();

function Player(options, media_item) {
    /**
     * Player model object
     */
    this.segment_nav_on = true; //tells if the segment navigation is enabled or not

    "use strict";
    if (options.service === 'koemei') {
        this.player = new koemei.JWPlayerInterface(options, media_item);
    }
    else if (options.service === 'kaltura') {
        this.player = new koemei.KalturaPlayerInterface(options, media_item);
        this.player.onStateChange(koemei.KalturaPlayerInterface.onStateChangeCallback);
    }

    this.init = function (transcript) {
        this.transcript = transcript;

        this.resume_playback_interval = -100; //interval when resuming playback

        this.initShortcuts();
        this.initListeners();
    };

    /**
     * Interface to the player object
     */
    this.play = function play() {
        return this.player.play();
    };

    this.pause = function pause(flag) {
        return this.player.pause(flag);
    };

    this.seek = function seek(pos) {
        return this.player.seek(pos);
    };

    this.getState = function getState() {
        return this.player.getState();
    };

    this.getPosition = function getPosition(callback) {
        this.player.getPosition(callback);
    };

    this.onTime = function onTime(callback) {
        this.player.onTime(callback);
    };

    this.onSeek = function onSeek(callback) {
        this.player.onSeek(callback);
    };

    this.onPlay = function onPlay(callback) {
        this.player.onPlay(callback);
    };

    this.onPause = function onPause(callback) {
        this.player.onPause(callback);
    };

    this.onStateChange = function onStateChange(callback) {
        this.player.onStateChange(callback);
    };

    /**
     * Intitialise the shortcuts
     */
    this.initShortcuts = function initShortcuts() {
        var isShift = false;
        var flag = true; // disable Play/pause shortcut when press Next / previous segment shortcut
        var win = $('html').hasClass('win');
        var player = this.player;
        var transcript = this.transcript;
		var stuck_key = '';
		
		 $(document).keydown(function (e) {
			 if (stuck_key=='') {
				 console.log('set as stuck');
			 	stuck_key = e.keyCode ? e.keyCode : e.which;
			 }
		 });

        /* Play/pause shortcut */
        $(document).keyup(function (e) {
            var code = e.keyCode ? e.keyCode : e.which;
			if (code===stuck_key) {
				stuck_key='';	
			}
			
			
			$(document).keydown(function(event) {
				code = event.keyCode;
			});
			

            if (((code === 17 && win && stuck_key==='') || (code === 91 && !win && stuck_key==='') || (code === 224 && stuck_key==='')) && flag) { //Ctrl / command keycode
                e.preventDefault();
                player.play();
                //console.log(player.status);
            }
        });


        /* Next / previous segment shortcut */
        $(document).keyup(function (e) {
            var code = e.keyCode ? e.keyCode : e.which;
            if ((code === 38) || (code === 40)) {
                e.preventDefault();
                flag = false;
                var segments = $('#kw_transcript_container .kw_segment-content');
                var current_segment_idx = segments.index(segments.filter('.kw_current-segment'));
                if (current_segment_idx < 0) {
                    current_segment_idx = -1;
                }
                // previous segment
                if (code === 38) {
                    current_segment_idx--;
                    if (current_segment_idx>=0) {
                    if (current_segment_idx < segments.length) {
                        transcript.move_scroll_to_position(segments.eq(current_segment_idx).parents('div.kw_tr-segment').position().top, function () {
                            transcript.set_current_segment(segments.eq(current_segment_idx).attr('seg-start') / 1);
                            flag = true;
                        });
                        player.pause(true);
                    }
                    }
                }
                //next segment
                else if (code === 40) {
                    current_segment_idx++;
                    if (current_segment_idx < segments.length) {
                        transcript.move_scroll_to_position(segments.eq(current_segment_idx).parents('div.kw_tr-segment').position().top, function () {
                            transcript.set_current_segment(segments.eq(current_segment_idx).attr('seg-start') / 1);
                            flag = true;
                        });
                        player.pause(true);
                    }

                }

                return false;
            }
        });


        // next/previous word
        $(document).keyup(function (e) {
            var code = e.keyCode ? e.keyCode : e.which;
            if (code === 16) {
                isShift = false;
                flag = true;
            }
            if (code === 9) {
                e.preventDefault();
            }
        }).keydown(function (e) {
                var code = e.keyCode ? e.keyCode : e.which;

                if (code === 16) {
                    isShift = true;
                }
                if (code === 9) {
                    e.preventDefault();
                    var current_label = $('#kw_transcript_container').find('.kw_current');

                    // next label shortcut
                    if (!isShift) {
                        // if current_label is not set, go to the first label of the segment
                        if ($(current_label).length == 0) {
                            var segment = $('.kw_segment-content:first');
                            var label = $(segment).find('.label:first');
                            $(label).click();
                        }
                        else {
                            var next_label = $(current_label).next();
                            // if next label not found, go to next segment first label
                            if ($(next_label).length == 0) {
                                var next_segment = $('.kw_current-segment').parent('div').next();
                                if ($(next_segment).length != 0) {
                                    var label = $(next_segment).find('.label:first');
                                    transcript.move_scroll_to_position($(next_segment).position().top, function () {
                                        $(label).click();
                                    });
                                }
                            }
                            else {
                                $(next_label).click();
                            }
                        }
                    }
                    // previous label shortcut
                    else {
                        if ($(current_label).length != 0) {
                            var prev_label = $(current_label).prev();
                            if ($(prev_label).length == 0) {
                                var prev_segment = $('.kw_current-segment').parent('div').prev();
                                if ($(prev_segment).length != 0) {
                                    var label = $(prev_segment).find('.label:last');
                                    transcript.move_scroll_to_position($(prev_segment).position().top, function () {
                                        $(label).click();
                                    });
                                }
                            } else {
                                $(prev_label).click();
                            }
                        }
                    }

                }
            });
    };

    this.initListeners = function initListeners() {
        var transcript = this.transcript;
        var player = this.player;
        var position = parseInt(options.start_time);
        var autoplay = options.autoplay;
        var caption_status = options.captions;
        player.onTime(function (event) {
            // TODO : this is nasty, should be handled in the Kaltura/JWPlayer interfaces
            var time = parseInt(event.position * 100, 10);

            if (isNaN(event.position)) {
                time = parseInt(event * 100, 10);
            }
            //console.log("Player OnTime pos : "+player.getPosition()+" / "+time);
            if (parseInt(event.position * 100, 10) !== 0) {
                transcript.set_current_segment(time);
            }
        });

        player.onSeek(function () {
            if (!this.segment_nav_on) {
                this.segment_nav_on = true;
            }
        });

        if (autoplay || position > 0) {
            player.play();
        }

        player.onPlay(function () {
            var _this = this
            if (position > 0) {
                position = position - 1;
                player.seek(position);
                position = 0;
            }
            $(".kw_koemei_widget").scrollTop(0);
//			removed the seeker fade out			
//            setTimeout(function () {
//                $('.kw_seeker').fadeOut(500);
//            }, 2000);
            if (!$("#kw_subtitles").hasClass('hidden') && caption_status) {
                $("#koemei_widget_toolbar ul li.captions_item").addClass('kw_active');
                $("#kw_subtitles").show();
            }


            $('#widget_play').addClass('kw_t_pause');
            $('#widget_play').removeClass('kw_t_play');


            transcript.segmentClick = false;
            //console.log("Player OnPlay current_label : "+transcript.current_label.start);
            /*
             * When the user clicks on a word, the playback is set
             * at the beginning of the word and is paused. So when the user resumes the
             * playback, it starts resume_playback_interval/100 seconds before the beginning of the word that was clicked.
             * (or at start of segment if start of label is < 2s from start of segment)
             * after pausing and hitting play again
             */
            var pos_to_seek = 0;
            if (!_this.segment_nav_on) { //play or pause normal mode
                _this.segment_nav_on = true;
                var segment_start = $('#l_' + transcript.current_label.start).parent().attr('seg-start');
                if (segment_start > parseInt(transcript.current_label.start, 10) - 100) {
                    pos_to_seek = Math.ceil(segment_start / 100);
                }
                else {
                    pos_to_seek = Math.ceil((parseInt(transcript.current_label.start, 10) - 100) / 100);
                }

                player.seek(pos_to_seek);
            }
        });

        player.onPause(function () {
            var _this = this
            $('#widget_play').addClass('kw_t_play');
            $('#widget_play').removeClass('kw_t_pause');
//            $('.kw_seeker').fadeIn(100);
            _this.segment_nav_on = false;
        });
    };

}
KoemeiWidget = Backbone.View.extend({
    initialize:function (options) {
		$('body').css('position','static');
        //default config
        var defaults = {
			el:'',
            service:'koemei',
            width:640,
            video_height:360,
            widget_height:400,
            pannel:'transcripts',
            search_string:'',
            toolbar:true,
            left_space:46,
			captions:true,
            right_space:67,
            alternatives:false,
            modal:true,
            readonly:false,
            display_related_media:false,
            improve_popup:false,
			autosave:60,
			user_uuid:'25903e05-3c03-496d-bebc-3b3e1be4fdb9',
			mode:'edit',
			solid_overlay:false,
			black_overlay:false,
			media_uuid:'',
			type:'widget',
			search_default:'prompt',
			start_time:0,
			autoplay:false,
			background_save: true,
            player_id:false,
			override_access_levels:false,
			show_exit:true
        };
        this.options = $.extend({}, defaults, this.options);
		if ( ! this.options.el) {
				//TODO: only append if there isn't a div already
				$('body').append('<div id="koemei_widget_container"></div>');
				this.$el = $('#koemei_widget_container');
		}
		_.bindAll(this,'init_video', 'init_transcript','keypress','display_search_results','close');

        // crossdomain request initialization
        this.xhr = new easyXDM.Rpc({
            remote: koemei_host+"/cors/"
        }, {
            remote: {
                request: {} // request is exposed by /cors/
            }
        });

		$.each(this.options,function(key,value) {
			if (key!=='features') {
			if (!defaults.hasOwnProperty(key)) {
				console.log('"'+key+'" is not a valid widget config option, got "'+value+'" for it');	
			}
			}
		});



		if (this.options.type=="widget") {
			var mode = this.options.mode;
			switch(mode) {
				case 'edit':
					var config = {
						features:{ info:true, related:false, captions:true, transcriptions:true, arrow:false, feedback:false, dots:false, search:true },
						improve_popup:false,
						captions:true
					};
				break;
                case 'edit_review':
					var config = {
						features:{ info:true, related:false, captions:true, transcriptions:true, arrow:false, feedback:false, dots:false, search:true },
						improve_popup:false,
						captions:true
					};
				break;
				case 'embed':
					var config = {
						features:{ info:true, related:false, captions:false, transcriptions:true, arrow:false, feedback:false, dots:false, search:true },
						readonly:true,
						captions:false
					};
				break;
				
				case 'play':
					var config = {
						features:{ info:true, related:false, captions:true, transcriptions:true, arrow:false, feedback:false, dots:false, search:true },
						widget_height:37,
						captions:true
					};
				break;
				case 'minimal':
					var config = {
						features:{ info:true, related:false, captions:false, transcriptions:false, arrow:false, feedback:false, dots:false, search:false },
						widget_height:37,
						captions:false,
						autoplay:true
					};
				break;
			}
			this.options = $.extend({},this.options,config);
			
			
			$(window).keyup(this.keypress);
			var total_height = this.options.video_height + this.options.widget_height;
			var viewportHeight = $(window).height();
			if (viewportHeight<total_height) {
				var new_height = viewportHeight-30;
				precentage = new_height/total_height;
				this.options.video_height = parseInt(this.options.video_height*precentage);
				this.options.widget_height = parseInt(this.options.widget_height*precentage);
				this.options.width = parseInt(this.options.width*precentage);
			}
			this.$el.empty();
			this.$el.show();
			
			
			this.render();
		}
		
		if (this.options.type=="search_portal") {
			this.$el.empty();
			this.$el.show();
			this.render_sp(this.options.string);			
		} else if (this.options.type=="caption_portal") {
            this.$el.empty();
            this.$el.show();
            //this.renderCaptionPortal();
            //TODO: use the above line and remove 'if' from doRecent and doSearch
            //TODO: also move the progress bar from display_search_results
            this.render_sp(this.options.string);
        }

    },
	
    render:function (string) {
		
		
		
        var left_margin = this.options.width / 2;
        var total_height = this.options.video_height + this.options.widget_height;
        var top_margin = total_height / 2;
		var custom_class='';
		if (!this.options.modal) {
			custom_class='kw_no_modal';
		} 
		if (this.options.mode==="minimal") {
			custom_class=custom_class+' kw_overflow_hidden';
		}
		if (this.options.readonly) {
			custom_class=custom_class+' kw_transparent_widget';
		}
        var template_variables = { width:this.options.width,custom:custom_class, left_margin:left_margin, height:total_height, top_margin:top_margin, video_height:this.options.video_height, left_space:this.options.left_space, widget_height:this.options.widget_height };
        var template = _.template(template_widget, template_variables);
        this.$el.html(template);
        this.$el.topZIndex();
		$('.kw_widget_overlay').topZIndex();
		$('.kw_koemei_widget').topZIndex();
		
        koemei.Media.get_one(this.xhr, this.options.media_uuid, this.options.auth, this.init_video,this.error_handler);


        if (this.options.toolbar) {
            this.build_toolbar();
        }
		this.height = $(window).height()/2;
		if (this.options.modal) {
			this.$el.css({'position':'fixed','width':'100%','height':'100%','top':0,'left':0});
		}
		if (!this.options.modal) {
			$('.kw_widget_overlay').hide();
		} 
		if (this.options.solid_overlay) {
			$('.kw_widget_overlay').addClass('kw_solid_overlay');
		}
		if (this.options.black_overlay) {
			$('.kw_widget_overlay').addClass('kw_black_overlay');
		}
		this.fixIeBug();
        this.init_walkthrough();
    },
	

    // Render a caption portal
    //TODO
    renderCaptionPortal: function() {

    },
	
	
    events:{
        "click #koemei_widget_toolbar ul li a":"select_pannel",
        "click #widget_logo ":"display_about",
        "click #close_widget ":"close_handler",
        "submit #widget_search ":"widget_search",
        "click .hold_scroll p":"search_result_click",
        "click .kw_seeker span":"seeker_click",
        "click #related_holder li a":"reinit",
        "click #kw_subtitles":"toogle_captions",
        "click #segment_next":'next_segment',
        "click #segment_prev":'prev_segment',
        "click #next_word":'next_word',
        "click #prev_word":'prev_word',
        "click #widget_play":'widget_play',
        "click #submit_btn_cancel":'cancel_search',
        "click #show_all_related":'search_portal_init',
        'click #kw_notify_popup a':'hide_notify',
        'click #help_popup a,span':'hide_improve',
        'click #kw_help':'show_walkthrough',
    		'focus #widget_search input':'pause_it',
    		'click #kw_exit_popup a':'exit_widget',
    		//events Search Portal
        "submit #kw_search_form":"doSearch",
        //"click .kw_pagination a":"paginate",
        "click .kw_display-mode li a":"display_mode_change",
        "click .kw_result_grid article a":'trigger_video',
        "click .kw_sort_mode":'sort_mode',
        "click .kw_sort_mode ul li":'sort_mode_set',
        "click #kw_search_form .cancel":'remove',
		"click .kw_result_list .kw_caption p":'expand_list',
		"click .kw_list_video a,.kw_video_list_link":'trigger_list_video',
		"click .kw_search_error":'hide_error',
		"focus #kw_search_form input":'hide_error',
		'keypress #kw_search_form input':'trigger_change',
		'click .recent_trigger': 'doRecent'
    },
	fixIeBug: function(){
		if(!$.browser.msie || $.browser.version > 8){
			return;
		}
		var delegateEventSplitter = /^(\S+)\s*(.*)$/;
		var events = this.events;
		if($.isFunction(events)){
			events = events.apply(this);                
		}
		var handleEvents = ['change', 'submit', 'reset', 'focus', 'blur']
		for (var key in events) {
			var method = events[key];
			if (!_.isFunction(method)) method = this[events[key]];
			if (!method) throw new Error('Method "' + events[key] + '" does not exist');
			var match = key.match(delegateEventSplitter);
			var eventName = match[1], selector = match[2];
			if( _.indexOf(handleEvents, eventName) == -1){ continue; }
			method = _.bind(method, this);
			if (selector === '') {
			} else {                  
				var self = this;                     
				(function(eventName,method){
					//console.log('trying to set "on" handler for %s, key=%s', eventName, key);
					self.$(selector).on(eventName, function(e){                            
						//console.log('got it, eventName=%s', eventName);                  
						return method(e);
					})
				})(eventName,method);
			}
		}                
	},
	
	error_handler:function(data) {
		console.log(data);
		console.log('error');
		
	},
	
	
    reinit:function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        this.close();
        var media_uuid = $(element).attr('data-uuid');
        var string = $(element).attr('data-string');
        this.options.media_uuid = media_uuid;
        this.options.pannel = 'search';
        this.options.search_string = string;
        this.$el.empty();
        this.$el.show();
        this.render();
    },
	keypress: function(event) {
		var key = event.keyCode;
		if (key==27 && this.options.modal) {
			this.close();	
		}
	},
    search_portal_init:function (event) {
        event.preventDefault();
        var val = $('#widget_search input').val();
        this.player.pause(true);
		this.close();
		this.$el.attr('style','');
		new KoemeiWidget({
		  type: 'search_portal',
		  mode:'embed',
		  string:val
		});
    },
    hide_notify:function (event) {
        event.preventDefault();
        $('#kw_notify_popup').hide();
    },
	pause_it: function(event) {
		if (this.player) {
		var status = this.player.getState();
		if (status=="PLAYING") {
			this.player.pause();
			$('#widget_search input').focus();
		}
		}
	},
    hide_improve:function (event) {
        event.preventDefault();
		var element = event.currentTarget;
		var action = $(element).attr('data-action');
		
        $('#help_popup').hide();
		if (action=="edit") {
			this.options.readonly=false;	
			this.options.improve_popup=false;
			this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').empty();
			this.init_pannel('transcripts');
			$('#transcript_pannel_containter').removeClass('kw_readonly_pannel');
		}
    },
    next_segment:function (event) {
        event.preventDefault();
        var e = $.Event('keyup');
        e.which = 40;
        $(document).trigger(e);
    },
    prev_segment:function (event) {
        event.preventDefault();
        var e = $.Event('keyup');
        e.which = 38;
        $(document).trigger(e);
    },

    next_word:function (event) {
        event.preventDefault();
        var e = $.Event('keydown');
        e.which = 9;
        $(document).trigger(e);
    },
    prev_word:function (event) {
		event.preventDefault();
        var e = $.Event('keydown');
        e.which = 16;
        $(document).trigger(e);
        e = $.Event('keydown');
        e.which = 9;
        $(document).trigger(e);
        e = $.Event('keyup');
        e.which = 16;
        $(document).trigger(e);
    },
    widget_play:function (event) {
        event.preventDefault();
        var target = event.currentTarget;
        if ($(target).hasClass('kw_t_play')) {
            this.player.play();
        } else {
            this.player.pause();
			
        }
    },
	exit_widget:function(event) {
        event.preventDefault();
		var button = event.currentTarget;
		this.transcript.save(this.xhr, this.options.auth,function() {console.log('saved');});
		var action = $(button).attr('data-action');		
		if (action === "YES") {
			$('#kw_publish-button').addClass('btn-disabled');	
			this.transcript.media.publish(this.xhr, this.options.service, this.options.auth);
			$('#kw_publish-button').text('FINISH');
			setTimeout(function () {
				$('#kw_publish-button').removeClass('btn-disabled');
			}, 200);
			$('#kw_exit_popup').hide();
			this.close();
		} else {
			if (this.options.modal) {
				this.close();	
			}
		}
	},
	
    cancel_search:function (event) {
        event.preventDefault();
        $("#widget_search input").val('');
        $('#submit_btn_cancel').hide();
        $('#submit_btn_widget').show();
        $('.kw_seeker').empty();
		if (this.options.mode!='play') {
        	this.init_pannel('transcripts');
		} else { $('.kw_koemei_widget').addClass('close_wid'); this.expand_panel(); }
    },
    init_video:function (media_item) {
        this.media_item = media_item;
		
		
		// if media is not yet transcribed, display the widget in a minimal form : just the player and the toolbar with only the "i" button
        if (!this.media_item.is_transcribed() || this.media_item.current_transcript_uuid === undefined) {
			if (this.options.mode!=="minimal") {
				this.transcript = new Object;
				this.transcript.changes = 0;
				this.$el.empty();
				this.options.mode="minimal";
				this.options.widget_height=37;
				
				this.initialize();
			}
		}
		
		// if media is private
		if (this.media_item.is_private()) {
            if (!this.options.override_access_levels) {
                this.options.readonly = true;
            }
            this.build_toolbar();
        }

        this.player = new Player(this.options, this.media_item);

		if (this.options.mode!=="minimal") {
		        if (this.options.mode==="edit") {
		          var stage = "drafts";
		        } else { 
		           var stage ='';
		        }
		
            koemei.Transcript.get_one(this.xhr, this.media_item.current_transcript_uuid, stage, this.media_item, this.player, this.options.auth, this.init_transcript)
		}
        else {
            $('.kw_koemei_widget').removeClass('auto');
        }
    },

    toogle_captions:function (event) {
		event.preventDefault();
        $("#kw_subtitles").toggleClass('hidden');
        $("#koemei_widget_toolbar ul li.captions_item").removeClass('kw_active');
        $("#kw_subtitles").hide();

    },


    init_transcript:function (transcript) {
        this.transcript = transcript;			
		console.log(transcript);
        this.init_pannel(this.options.pannel);
    		if (this.options.autosave!=0) {
    			var interval = this.options.autosave*1000;
    			var self = this;
    			this.interval = setInterval(function() { 
    				self.transcript.save(self.xhr, self.options.auth,function() {console.log('saved');});
    			 },interval);
    		}
    		if (this.options.mode=="play") {
    			this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('li').not(this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('.captions_item')).removeClass('kw_active');
    		}
    },
	
    close:function () {
  		if(this.interval!==undefined) { 
  			clearInterval(this.interval);
  		}
			this.$el.empty();
			this.$el.hide();
			this.undelegateEvents();
    },
    
    close_handler:function(event) { 
      if (this.transcript.changes.length>0) {
        //if the transcript has changes show the publish popup - there the transcript is saved, no need to do it here
        $('#kw_exit_popup').show();
      } else { 
        // no modifications are made
        if (!this.media_item.publish_status) {
          // if the transcript is not published show the publish popup
          $('#kw_exit_popup').show();
        } else {
          //if it's published and no modifications are made, just close, no need to show the popup
          this.close();
        }       
      }   
    },
    //toolbar build
    build_toolbar:function () {
        var tool_bar = '<div id="koemei_widget_toolbar">';
        tool_bar += '<a href="#" class="kw_logo" id="widget_logo" data-target="about">KOEMEI</a>';
        tool_bar += '<ul>';
        if (this.options.features.info) {
            tool_bar += '<li><a href="#" data-target="info" class="tips_toolbar" title="Video info">Info</a></li>';
        }
        if (this.options.features.related) {
            tool_bar += '<li><a href="#" class="related tips_toolbar" title="Related videos" data-target="related">Related</a></li>';
        }
        if (this.options.features.captions) {
            tool_bar += '<li class="captions_item"><a href="#" class="captions tips_toolbar" title="Toogle captions" data-target="cc" >CC</a></li>';
        }
        if (this.options.features.transcriptions) {
            var readonly_class = (this.options.readonly) ? "transcript_readonly" : "";
            var edit_tooltip = (this.options.readonly) ? "View transcripts" : "Edit transcripts";
            tool_bar += '<li><a href="#" class="transcript_edit tips_toolbar ' + readonly_class + '" title="' + edit_tooltip + '" data-target="transcripts">' + edit_tooltip + '</a></li>';
        }
        if (this.options.features.arrow) {
            tool_bar += '<li><a href="#" class="arrow" data-target="arrow">184</a></li>';
        }
        if (this.options.features.feedback) {
            tool_bar += '<li class="last"><a href="#" class="feedback tips_toolbar" title="Feedback" data-target="feedback">Give feedback</a></li>';
        }
        if (this.options.features.dots) {
            tool_bar += '<li class="last"><a href="#" class="dots" data-target="dots">184</a></li>';
        }
        tool_bar += '</ul>';
        if (this.options.features.search) {
            tool_bar += '<form id="widget_search">';
            tool_bar += '<button type="submit" id="submit_btn_widget"></button>';
            tool_bar += '<button type="button" id="submit_btn_cancel" class="cancel" style="display:none"></button>';
            tool_bar += '<input type="text" placeholder="search in video..." value="' + this.options.search_string + '">';
            tool_bar += '</form>';
        }
        this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').html(tool_bar);
        $('.tips_toolbar').tipsy({live:true, gravity:'s', offset:40});

    },

    //click on toolbar buttons
    select_pannel:function (event) {
		event.preventDefault();
        var element = event.currentTarget;
        var target = $(element).attr('data-target');
		var par = $(element).parent();
		$('#widget_logo').removeClass('close');	
		if (target=='cc') { this.init_pannel(target); } else {
			if (!$(par).hasClass('kw_active')) { 
				this.init_pannel(target);
			} else {
				$('.kw_koemei_widget').addClass('close_wid');
				this.expand_panel();
			}
		}
    },

	expand_panel:function(pannel) {
		if (this.options.mode=="play" || this.options.mode=="minimal" ) {
		if ($('.kw_koemei_widget').hasClass('auto')) {
				$('.kw_koemei_widget').removeClass('auto');
			} else {
				if (!$('.kw_koemei_widget').hasClass('expanded')) {
					this.options.widget_height=400;
					var total_h = this.options.widget_height+this.options.video_height;
					var viewportHeight = $(window).height();
					if (total_h>viewportHeight) {
						total_h = viewportHeight-30;
						dif = total_h - this.options.video_height;
						this.options.widget_height = dif;
					}
					var mg_top = total_h/2;
					$('.kw_koemei_widget').animate({height:total_h,marginTop:'-'+mg_top});
					$('#kw_subtitles').animate({bottom:this.options.widget_height});
					$('.kw_koemei_widget').addClass('expanded');
					$('#transcript_pannel_containter').css('height',this.options.widget_height-37);
					if (this.options.readonly) { 
						$('#kw_transcript_container').css('height',this.options.widget_height-87);
					} else {
						$('#kw_transcript_container').css('height',this.options.widget_height-122);
					}
					$('#help_popup').animate({height:'100%'});
				} else {
					if ((this.options.mode=="play" && $('.kw_koemei_widget').hasClass('close_wid') && pannel!='search') || (this.options.mode=="minimal" && $('.kw_koemei_widget').hasClass('close_wid') && pannel!='search')) {
						$('.kw_koemei_widget').removeClass('expanded');
						var total_h = this.options.video_height+37;
						var mg_top = total_h/2;
						this.options.widget_height =total_h;
						$('.kw_koemei_widget').animate({height:total_h,marginTop:'-'+mg_top});
						$('#kw_subtitles').animate({bottom:37});
						this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('li').not(this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('.captions_item')).removeClass('kw_active');
						$('.kw_koemei_widget').removeClass('close_wid');
						$('#help_popup').animate({height:0});
					}

					
				}
			}
		}
	},


    //init the desired pannel
    init_pannel:function (pannel) {
		if (this.transcript && this.transcript.changes.length>0) {
			$('#kw_confirm_popup').show();
		} else {
			this.$el.children('.kw_koemei_widget').children('#pannel_containter').html();
			if (pannel == "info") {
				this.display_info();
				this.select_menu('info');
				this.$el.children('.kw_koemei_widget').children('#pannel_containter').show();
				this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').hide();
			}
			if (pannel == "feedback") {
				this.display_feedback();
				this.select_menu('feedback');
				this.$el.children('.kw_koemei_widget').children('#pannel_containter').show();
				this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').hide();
			}
			if (pannel == "transcripts") {
				this.$el.children('.kw_koemei_widget').children('#pannel_containter').hide();
				this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').show();
				this.select_menu('transcripts');
				this.display_transcripts();
				
			}
			if (pannel == "search") {
				this.display_transcripts();
				this.select_menu('transcripts');
				var _this = this;
				setTimeout(function() {
					_this.widget_search();	
				 },50);
	
			}
			if (pannel == "cc") {
				$('#kw_subtitles').toggle();
				$("#kw_subtitles").toggleClass('hidden');
				$("#koemei_widget_toolbar ul li.captions_item").toggleClass('kw_active');
			}
			
			if ((this.options.mode=="play"  && pannel!='cc') || (this.options.mode=="minimal" && pannel!='cc')) {
				this.expand_panel(pannel);
			}
		}
		
		
    },

    //select the menu item on init
    select_menu:function (pannel) {
        this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('li').not(this.$el.children('.kw_koemei_widget').children('#widget_toolbar_hold').children('#koemei_widget_toolbar').children('ul').children('.captions_item')).removeClass('kw_active');
        el = this.$el.children('.kw_koemei_widget').find('[data-target="' + pannel + '"]');
        $(el).parent('li').addClass('kw_active');
    },


    display_transcripts:function () {
		if (this.media_item) {
            if (this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').is(":empty")) {
                var height = this.options.widget_height - 90;
                if (this.options.toolbar) {
                    height = height - 39;
                }
                if (this.options.readonly) {
                    height = height + 42;
                    this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').addClass('kw_readonly_pannel');
                }
                var CtrlName = 'Ctrl';
                if (navigator.platform.indexOf("Mac") != -1) {
                    CtrlName = "⌘";
                    $('html').removeClass('win');
                }
                //TODO : use template
                var confirm_popup = '<div id="kw_confirm_popup" style="height:' + height + 'px; display:none;" class="kw_player_popup">';
                confirm_popup += '<div>';
                confirm_popup += '<h2>Is captioning completed?</h2>';
                confirm_popup += '<a href="#" data-action="YES"><strong>YES</strong>Save</a>';
                confirm_popup += '<a href="#" data-action="NO"><strong>NO</strong>Continue editing</a>';
                confirm_popup += '</div>';
                confirm_popup += '</div>';
                //TODO : use template
                var exit_popup = '<div id="kw_exit_popup" style="height:' + height + 'px; display:none;" class="kw_player_popup">';
                exit_popup += '<div>';
                exit_popup += '<h2>Finish caption?</h2>';
                if (this.options.service!=='koemei')
                    exit_popup += '<a href="#" data-action="YES"><strong>YES</strong>Exit</a>';
                else
                    exit_popup += '<a href="#" data-action="YES"><strong>YES</strong>Publish</a>';
                exit_popup += '<a href="#" data-action="NO"><strong>NO</strong>I will be back</a>';
                exit_popup += '</div>';
                exit_popup += '</div>';
                //TODO : use template
                var notify_popup = '<div id="kw_notify_popup" style="height:' + height + 'px; display:none;" class="kw_player_popup">';
                notify_popup += '<div>';
                notify_popup += '<h2></h2>';
                notify_popup += '<a href="#" data-action="YES"><strong>OK</strong>Thank you</a>';
                notify_popup += '</div>';
                notify_popup += '</div>';
                //TODO : use template
                if ((this.options.improve_popup && this.options.mode=='embed' && !this.media_item.publish_status) || (this.options.readonly && this.options.improve_popup && this.options.mode=='play' && !this.media_item.publish_status)) {
                    var help_popup = '<div id="help_popup" style="height:' + height + 'px; " class="kw_player_popup kw_help_popup">';
                    help_popup += '<div><span data-action="">Close</span>';
                    help_popup += '<h2>Help us improve this machine transcript</h2>';
                    help_popup += '<a href="#" data-action="edit">EDIT</a>';
                    help_popup += '<a href="#" data-action="" class="show">SHOW</a>';
                    help_popup += '</div>';
                } else {
                    var help_popup = '';
                }
                //TODO : use template
                var popup_wait = '<div id="popup-wait" class="dialog-window dialog-message-upload-progress" style="display:none">Please wait... We are saving your modifications, this may take some time if your media is long.<br /><img src="https://d18embnwuxbhpk.cloudfront.net/images/loading.gif" alt="Please wait" /></div>';
                var transcript_hold = '<div id="kw_transcripts" class="tab">';
                if (!this.options.readonly) {
                    transcript_hold += '<div class="kw_transcription_toolbar">';
                    transcript_hold += '<ul>';
                    transcript_hold += '<li><a href="#" class="kw_t_play tips" title="' + CtrlName + ': Play/Pause" id="widget_play">pause</a></li>';
                    transcript_hold += '<li><a href="#" class="kw_t_prev tips" title="Shift+Tab: Previous word" id="prev_word">First</a></li>';
                    transcript_hold += '<li><a href="#" class="kw_t_next tips" title="Tab: Next word" id="next_word">Last</a></li>';
                    transcript_hold += '<li><a href="#" class="kw_t_first tips" title="Up arrow: Previous segment" id="segment_prev">Prev</a></li>';
                    transcript_hold += '<li><a href="#" class="kw_t_last tips" title="Down arrow: Next segment" id="segment_next">Next</a></li>';
                    transcript_hold += '</ul>';
                    transcript_hold += '<div class="kw_progress"  title="Progress bar tooltip"><span style="width:0%">&nbsp;</span><strong>0%</strong></div>';
                    transcript_hold += '<a href="#" class="kw_setings tips" ="Settings">Settings</a>';
                    transcript_hold += '</div>';
                }
                transcript_hold += '<div id="kw_transcript_container" class="text-box"  style="height:' + height + 'px"></div>';
                transcript_hold += '<div class="kw_edit-controls"></div>' + confirm_popup + notify_popup + help_popup + popup_wait + exit_popup;
                transcript_hold += '</div>';
                this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').html(transcript_hold);

                this.player.init(this.transcript);

                this.transcript.display(this.options.readonly);

                this.controls = new Controls();

                var config = {
                    publish_button_class:"btn-2",
                    save_button_class:"btn-2",
                    text:"no transcript loaded",
                    transcript_uuid:this.transcript.uuid,
                    readonly:this.options.readonly,
                    last_save:this.transcript.updated,
                    published:this.media_item.publish_status,
                    background_save: this.options.background_save,
                    service: this.options.service,
					show_exit: this.options.show_exit,
                    auth: this.options.auth,
                    mode: this.options.mode
                };
                this.controls.init(this.xhr, config, this.transcript);
                $('.tips').tipsy({live:true, gravity:'s', offset:14});
                $('.kw_progress').tipsy({live:true, gravity:'s', offset:150, html:true});
                if (this.options.alternatives == false) {
                    $('#kw_transcripts').addClass('alternatives-off');
                }
            }
		}
    },

    //display the info pannel
    display_info:function () {
		console.log(this.media_item);
        var date = this.media_item.created.split(" ");
		var categs = this.media_item.categories;
		var authors = this.media_item.authors;
		var status = this.media_item.status;
		var progress = this.media_item.progress;
        var info_pane = '<div id="info" class="tab kw_widget_info_tab">';
       /// info_pane += '<div class="kw_head"><h2 title="' + this.media_item.title + '">' + this.media_item.title + '</h2><a id="kw_help" href="#" title="Help">help</a><p><strong>1.245</strong> views</p></div>';
        info_pane += '<div class="kw_head"><h2 title="' + this.media_item.title + '">' + this.media_item.title + '</h2><a id="kw_help" href="#" title="Help">help</a></div>';
		if (this.media_item.description !== undefined && this.media_item.description !== null) {
			info_pane += '<p class="kw_description">'+this.media_item.description+'</p>';
		}

        info_pane += '<ul>';
        info_pane += '<li><strong>Published on:</strong> ' + date[0] + '</li>';
		
		if (categs.length>0) {
			var dispay_categs='';
			$.each(categs,function(i,categ) {
				if (dispay_categs=='') {
				dispay_categs = categ; } else {
					dispay_categs = dispay_categs+','+categ; 
				}
			});
			info_pane += '<li class="cat"><strong>Category:</strong> '+dispay_categs+'</li>';
		}
		
		if (authors.length>0) {
			var dispay_authors='';
			$.each(authors,function(i,author) {
				if (dispay_authors=='') {
				dispay_authors = author; } else {
					dispay_authors = dispay_authors+','+author; 
				}
			});
			info_pane += '<li class="tags"><strong>Authors:</strong> '+dispay_authors+'</li>';
		}
		
		
        //info_pane += '<li class="cat"><strong>Category:</strong> <a href="#">Geography</a></li>';
        //info_pane += '<li class="tags"><strong>Tags:</strong> <a href="#">geography</a>, <a href="#">education</a>, <a href="#">stanford</a></li>';
        info_pane += '</ul>';
		if ((status === 1 &&  progress<100)  || status === 4 || status === 6) {
			info_pane += '<p  class="kw_description">The captions will be available in approximately '+secondstotime(this.media_item.processing_time)+'</p>';
			
		}
		
		
		
        //info_pane += '<div class="widget_graph" title="My tooltip"><div class="bar" style="height:34%; "> </div><div class="bar" style="height:20%"> </div><div class="bar" > </div></div>';
		
        info_pane += '</div>';
        this.$el.children('.kw_koemei_widget').children('#pannel_containter').html(info_pane);
		$('#kw_help').tipsy({live:true, gravity:'w',  html:true});

    },

    //display feedback pane
    display_feedback:function () {
        //feedback pane
        var height = this.options.widget_height - 206;
        if (this.options.toolbar) {
            height = height - 39;
        }
		
        var feedback_pane = '<div id="feedback" class="tab kw_feedback_pane">';
        feedback_pane += '<h2>Give feedback</h2>';
        feedback_pane += '<form>';
        feedback_pane += '<select><option>Category</option></select>';
        feedback_pane += '<textarea placeholder="Enter your message" style="height:' + height + 'px"></textarea>';
        feedback_pane += '<input type="email" placeholder="Your e-mail address">';
        feedback_pane += '<button type="submit">Send feedback</button>';
        feedback_pane += '</form>';
        feedback_pane += '</div>';
        this.$el.children('.kw_koemei_widget').children('#pannel_containter').html(feedback_pane);
    },

    //display the about pane
    display_about:function (event) {
		if (this.transcript.changes.length>0) {
			$('#kw_confirm_popup').show();
		} else {
		if (this.options.mode=="play" ||  this.options.mode=="minimal") {
			if ($('#widget_logo').hasClass('close')) {
				$('#widget_logo').removeClass('close');	
				$('.kw_koemei_widget').addClass('close_wid');
			} else {
				$('#widget_logo').addClass('close');				
			}
			this.expand_panel();
		}
		
		event.preventDefault();
		this.$el.children('.kw_koemei_widget').children('#pannel_containter').show();
		this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').hide();
        var about_pane = '<div id="about" class="tab kw_widget_info_tab">';
        about_pane += '<div class="kw_head"><h2>About KOEMEi</h2></div>';
        about_pane += '<div class="kw_widget_info_content"><p>Proin aliquet laoreet purus, hendrerit commodo est scelerisque sit amet. Praesent libero lectus, gravida quis molestie id, faucibus in lorem. Nullam dictum libero quis diam faucibus pellentesque. Pellentesque diam velit, sollicitudin quis consequat at, lobortis non mi.</p></div>';
        about_pane += '</div>';
        this.$el.children('.kw_koemei_widget').children('#pannel_containter').html(about_pane);
        this.select_menu('');
		}
    },

    //Search pane init
    widget_search:function (event) {
		if (event) {
			event.preventDefault();
		}
		
		if (this.options.mode=="play") {
			this.expand_panel('search');
		}
		
		this.$el.children('.kw_koemei_widget').children('#pannel_containter').show();
		this.$el.children('.kw_koemei_widget').children('#transcript_pannel_containter').hide();		
        var mg = 20;
        var height = this.options.widget_height - 201;
        if (this.options.toolbar) {
            height = height - 39;
        }
        if (!this.options.display_related_media) {
            height = height + 201;
            mg = 0;
        }
        var inner_height = height - 10;
        var search_pane = '<div id="search" class="tab">';
        search_pane += '<div class="kw_search_results" style="height:' + height + 'px"><div class="hold_scroll" id="search_results_holder"  style="height:' + inner_height + 'px; margin-bottom:' + mg + 'px"></div>';
        search_pane += '</div>';
        if (this.options.display_related_media) {
            //same carosel as related carousels
            search_pane += '<div class="kw_widget_carousel kw_loading kw_loading_bg" id="related_holder"></div>';
        }
        search_pane += '</div>';
		
        var parent_element = this.$el.children('.kw_koemei_widget').children('#pannel_containter');
        $(parent_element).html(search_pane);
        var search_results_holder = $(parent_element).children('#search').children('.kw_search_results').children('#search_results_holder');
        this.select_menu('');
        var seeker_width = this.options.width - this.options.left_space - this.options.right_space;
        $('.kw_seeker').width(seeker_width);
		if(this.player) {
			 var duration = this.media_item.duration;
			  $('.kw_seeker').empty();
		}
        var seccond_width = seeker_width / duration;
        $(search_results_holder).empty();
        var search_params = new Object();
        search_params.search_query = $('#widget_search input').val();
        search_params.count = 10;
        var words_arr = $('#widget_search input').val();
		words_arr = words_arr.trim();
		words_arr = words_arr.split(" ");
        var found_Segments = new Array();
		if(this.transcript) {
		
		console.log(this.transcript);
		
		
		
		$.each(this.transcript.segments, function (i, segment) {
			for (var g = 0; g < words_arr.length; g++) {
				$.each(segment.labels, function (k, label) {
					if (label.value.toUpperCase() === words_arr[g].toUpperCase()) {
					    var segment_id = segment.start;
                        var skyp = 0;
                        var key = '';
                        //check if segment is already flagged
                        $.each(found_Segments, function (index, seg) {
                            if (segment_id == seg.start) {
                                skyp = 1;
                                key = index;
                            }
                        });
                        if (skyp == 0) {
                            found_Segments.push(segment);
                        }
					}
				});
			}
		});
		
		
		
		// search in the edited segments...it's a quick fix i supose, have to talk how the changed semgments work :-??
		if (this.transcript.changes) {  
		  $.each(this.transcript.changes, function (i, segment) {
		    console.log(segment);
		  	for (var g = 0; g < words_arr.length; g++) {
		  		$.each(segment.labels, function (k, label) {
		  			if (label.value.toUpperCase() === words_arr[g].toUpperCase()) {
		  			    var segment_id = segment.start;
		                      var skyp = 0;
		                      var key = '';
		                      //check if segment is already flagged
		                      $.each(found_Segments, function (index, seg) {
		                          if (segment_id == seg.start) {
		                              skyp = 1;
		                              key = index;
		                          }
		                      });
		                      if (skyp == 0) {
		                          found_Segments.push(segment);
		                      }
		  			}
		  		});
		  	}
		  });
		
		
		
		} 
		
		
        var found = 0;
        $.each(found_Segments, function (index, segment) {
            found = found + 1;
            var time = timestamp_to_mmss(segment.start);
            var string = '';
            $.each(segment.labels, function (j, label) {
                    string += label.value + ' ';
            });
            string = string.replaceAll('"', '');
            string = string.replaceAll("'", '');
            var short_string = string.replace(/^(.{50}[^\s]*).*/, "$1");
			var seek_class='';
            $.each(words_arr, function (i, word) {
                string = string.replaceAll(word, '<em class="found_' + i + '">' + word + '</em>');
                short_string = short_string.replaceAll(word, '<em class="found_' + i + '">' + word + '</em>');
				n=string.indexOf(word);
				if (n>0) {
					seek_class='found_'+i;
				}
				
            });
            var content = '<p data-start="' + segment.start + '"><strong>' + time + '</strong>' + string + '</p> ';
            var marker_pos = parseInt(timestamp_to_seconds(segment.start) * seccond_width);
            var marker = "<span style='left:" + marker_pos + "px;' class='"+seek_class+"' data-start='" + segment.start + "' title='<div class=kw_marker_content>" + short_string + "</div>'>&nbsp;</span>";
            $(search_results_holder).append(content);
            $('.kw_seeker').append(marker);
        });
        if (found == 0) {
            $(search_results_holder).append('<h2>No results</h2>');
        } else {
            $(search_results_holder).append('<p>&nbsp;</p>');
        }
        $('#submit_btn_cancel').show();
        $('#submit_btn_widget').hide();
        $('.kw_hold_scroll').sbscroller();
        $('.kw_seeker span').tipsy({live:true, gravity:'s', offset:6, html:true});
        if(this.options.display_related_media) {
            $('#related_holder').addClass('kw_loading_bg');
            koemei.Media.get_all(this.xhr, search_params, this.options.auth,this.options.user_uuid, this.search_in_library);
        }
        }
        
    },

    //click on search result
    search_result_click:function (event) {
		if (this.player) {
        var element = event.currentTarget;
        if (this.player.getState() == "PAUSED" || this.player.getState() == "IDLE") {
            this.player.play();
        }
        var position = $(element).attr('data-start');
        var pos = timestamp_to_seconds(position);
        pos = pos - 1;
        if (pos<0) { pos = 0; }
        console.log(pos);
        this.player.seek(pos);
        if ($(element).hasClass('open')) {
            $(this).css('white-space', 'nowrap');
            $(this).removeClass('open');
        } else {
            $(element).css('white-space', 'normal');
            $(element).addClass('open');
        }
        $('.hold_scroll').sbscroller('refresh');
		}
    },

    //click on a seeker
    seeker_click:function (event) {
		if (this.player) {
        var element = event.currentTarget;
        if (this.player.getState() == "PAUSED" || this.player.getState() == "IDLE") {
            this.player.play();
        }
        var position = $(element).attr('data-start');
        var pos = timestamp_to_seconds(position);
        pos = pos - 1;
        if (pos<0) { pos = 1; }
        this.player.seek(pos);
		}
    },

    //display search results from the entire library
    search_in_library:function (response) {
        $('#related_holder').removeClass('kw_loading_bg');
        var remaining = parseInt(response.remaining);
        var media = response.media;
        var len = parseInt(media.length);

        var content = '<h2>Results from other videos';
        if (remaining > 0) {
            content += '<a href="#" id="show_all_related">show all</a>';
        }
        content += '</h2>';
        if (len > 0) {
            content += '<a href="#" class="kw_prev">&nbsp</a>';
            content += '<a href="#" class="kw_next">&nbsp</a>';
            content += '<ul id="search_carousel">';
            $.each(media, function (index, result) {
               // content += ' <li>\
			//	<article><a href="#" style="background-image:url(' + result.thumbnail_path + ')" data-uuid="' + result.uuid + '" data-string="' + $('#widget_search input').val() + '"><span><em>' + result.title + '</em></span></a>\
			//	  <div class="info"><strong class="time">' + time_tommss(result.size) + '</strong>\
		//			<div class="graph"><div class="bar" style="height:40%; ">&nbsp;</div><div class="bar">&nbsp;</div><div class="bar">&nbsp;</div><div class="bar" style="height:40%; ">&nbsp;</div></div>\
		//		  </div>\
		//		</article>\
		//	  </li>';
			  content += ' <li>\
			  <article><a href="#" style="background-image:url(' + result.thumbnail_path + ')" data-uuid="' + result.uuid + '" data-string="' + $('#widget_search input').val() + '"><span><em>' + result.title + '</em></span></a>\
			    <div class="info"><strong class="time">' + time_tommss(result.size) + '</strong>\
			  	\
			    </div>\
			  </article>\
			  </li>';
			  
            });
            content += '</ul>';
            $('#related_holder').html(content);
            $("#search_carousel").carouFredSel({
                responsive:true,
                auto:false,
                items:{visible:3},
                prev:{    button:"#search a.kw_prev" },
                next:{ button:"#search a.kw_next" }
            });
        } else {
            content += '<p class="kw_no_data">No extra data</p>';
            $('#related_holder').html(content);
            
        }
    },

	//search portal functions start here 

    //remove the search widget
    remove:function () {
			this.$el.empty();
			this.$el.show();
			this.render_sp();			
    },
	
    render_sp:function (string) {
        // Compile the template using underscore
        var template = _.template(template_search);
        this.$el.show();
        $(this.$el).topZIndex();
        this.$el.html(template);
		if (this.options.search_default=="prompt") {
        if (string != undefined) {
            $('#search_phrase').val(string);
            setTimeout(function () {
                $('#kw_search_form').submit();
            }, 500);
        }
		if (this.options.media_uuid!=='' && this.options.media_uuid!==undefined) {
			
			 setTimeout(function () {
               $('#kw_search_form').submit();
            }, 500);
            
		}
		
		}
		 if (this.options.search_default=="recent") {
			 this.doRecent();
		}
		this.fixIeBug();
    },
	
    //open/close the sort
    sort_mode:function (event) {
        $(event.currentTarget).toggleClass('open');
    },
	
    //set the sorting mode
    sort_mode_set:function (event) {
        var type = $(event.currentTarget).attr('data-type');
        $('.sort_mode').attr('sort_mode', type);
        $('#kw_search_form').submit();
    },
	
	trigger_change: function() {
		$('.kw_display-mode').attr('display-mode','kw_result_list');
	},
	
    //page click
    paginate:function (event) {
        event.preventDefault();
        var page = $(event.currentTarget).attr('data-page');
        $('#page').val(page);
       
		if (this.options.search_default=="recent") {
			 this.doRecent();
		} else {
			 $('#kw_search_form').submit();
		}
    },

    //click on grid item video
    trigger_video:function (event) {
        event.preventDefault();
        var par = $(event.currentTarget).parent();
        $('.kw_result_grid article').removeClass('active');
        $(par).addClass('active');
        var id = $(event.currentTarget).attr('data-id');
        $('#video_detail').empty();
		
		if ($('#video_start_id').length>0) {
			var start_time = $('#video_start_id').html();	
			$('#video_start_id').remove();
		}
		
		var invoke_options = new Object();
		invoke_options.media_uuid=id;
		invoke_options.el=$("#video_detail");
		invoke_options.width=726;
		invoke_options.modal=false;
		invoke_options.widget_height=340;
		invoke_options.video_height=366;
		invoke_options.mode=this.options.mode;		
		invoke_options.display_related_media=false;
		invoke_options.improve_popup = this.options.improve_popup;
		invoke_options.readonly = this.options.readonly;
		if ($('#search_phrase').val()!='') { 
			invoke_options.search_string=$('#search_phrase').val();
			invoke_options.pannel='search';
		}
		if (start_time) {
			invoke_options.start_time=start_time;	
		}
		new KoemeiWidget(invoke_options);	
		setTimeout(function() {
		$('.kw_search_header').topZIndex();	},2000);	
    },

    //click on grid item video
    trigger_external_video:function (uuid) {
        $('#video_detail').empty();
		var invoke_options = new Object();
		invoke_options.media_uuid=uuid;
		invoke_options.el=$("#video_detail");
		invoke_options.width=726;
		invoke_options.modal=false;
		invoke_options.mode="embed";
		invoke_options.widget_height=340;
		invoke_options.video_height=366;
		invoke_options.display_related_media=false;
		invoke_options.improve_popup = this.options.improve_popup;
		new KoemeiWidget(invoke_options);			
    },


	//click on list item video
	trigger_list_video:function(event) {
		event.preventDefault();
		var id = $(event.currentTarget).attr('data-id');
		$('body').append('<span id="video_click_id" style="display:none">'+id+'</span>'); 
		$('#btn_grid').click();
	},

	
	expand_list: function(event) {
		event.preventDefault();
		var id = $(event.currentTarget).attr('data-id');
		var start = $(event.currentTarget).attr('data-start');
		$('body').append('<span id="video_click_id" style="display:none">'+id+'</span>'); 
		$('body').append('<span id="video_start_id" style="display:none">'+start+'</span>'); 
		$('#btn_grid').click();
	},

    //show the search result for the captions
    show_this_caption:function (response) {
        this.subtitles_init(response);
        var words_arr = $('#search_phrase').val();
		words_arr = words_arr.trim();
		words_arr = words_arr.split(" ");
        var found_Segments = new Array();
        for (var g = 0; g < words_arr.length; g++) {
            $.each(response.segments, function (i, segment) {
                $.each(segment.label_seqs, function (j, labelseq) {
                    $.each(labelseq.labels, function (k, label) {
                        if (label.value.toUpperCase() === words_arr[g].toUpperCase()) {
                            segment_id = segment.id;
                            skyp = 0;
                            key = '';
                            //check if segment is already flagged
                            $.each(found_Segments, function (index, seg) {
                                if (segment_id == seg.id) {
                                    skyp = 1;
                                    key = index;
                                }
                            });
                            if (skyp == 0) {
                                found_Segments.push(segment);
                            }
                        }
                    });
                });
            });
        }
        var found = 0;
        $('#search_portal_transcription').empty();
        $.each(found_Segments, function (index, segment) {
            found = found + 1;
            var time = timestamp_to_mmss(segment.start);
            var string = '';
            $.each(segment.label_seqs, function (j, labelseq) {
                $.each(labelseq.labels, function (k, label) {
                    string += label.value + ' ';
                });
            });
            $.each(words_arr, function (i, word) {
                string = string.replaceAll(word, '<em class="found_' + i + '">' + word + '</em>');
            });
            var content = '<p data-start="' + segment.start + '"><strong>' + time + '</strong>' + string + '</p>';
            $('#search_portal_transcription').append(content);
        });
        //if (found==0) { $('#search_portal_transcription').append('<h2>No results</h2>'); }
        $('#search_portal_transcription').sbscroller();

    },

    //chagne display mode: list/grid
    display_mode_change:function (event) {
        event.preventDefault();
        var mode = $(event.currentTarget).attr('data-mode');
        $('.kw_display-mode li').removeClass('kw_active');
        var par = $(event.currentTarget).parent();
        $(par).addClass('kw_active');
        $('.kw_display-mode').attr('display-mode', mode);
		if (this.options.search_default=="recent") {
			 this.doRecent();
		} else {
        	$('#kw_search_form').submit();
		}
    },

    //display search results
    display_search_results:function (page_data, append) {
        var _this = this;
        var search_string = $('#search_phrase').val();
		var words_arr;
		words_arr = search_string.trim();
		words_arr = words_arr.split(" ");
		if (words_arr==undefined) { words_arr = Array(); }
        var current_nr = parseInt(page_data.media.length);
        var remaining = parseInt(page_data.remaining);
        var total = current_nr + remaining;
        page_data.PAGES = parseInt(total / 10);
        page_data.PAGE = 1;
        var display_mode = $('.kw_display-mode').attr('display-mode');
		
		if (current_nr>0) {
		
        if (display_mode == undefined) {
            display_mode = 'kw_result_list';
        }
		
		
	
		
		if(this.options.media_uuid!=='' && this.options.media_uuid!==undefined) {
			//$('#btn_grid').click();
			display_mode = 'kw_result_grid';
			$('.kw_display-mode li').removeClass('active');
			$('#btn_grid').parent().addClass('active');
			//delete this.options.media_uuid;
		}
        if (total > 0) {
            if ( ! append) {
                $('#num_results').html('(' + total + ' results)');
                $('#num_results').show();
                if(this.options.media_uuid!=='' && this.options.media_uuid!==undefined) {
                    $('#num_results').hide();
                }
            }
            if (remaining === 0) {
                this.destroy_infinitescroll($('#results'));
            }
            $('#kw_remaining').html(remaining + ' remaining items');

            //$('.kw_pagination').html(pagination_string);
            if ( ! append) {
                $('#results').empty();
                $('#results').removeClass();
                $('#results').addClass(display_mode);

                $('#loading').fadeOut(300, function () {
                    $('#results').fadeIn(300);
                });
            }
			
			
            //list
            if (display_mode == "kw_result_list") {
                $.each(page_data.media, function (index, video) {
					if (video.status!==6 || video.status!==4) {
					var caption = '';
                    $.each(words_arr, function (i, word) {
						if (video.matches) {
							$.each(video.matches, function(ind,video_match) {
								video_match = video_match.text.replaceAll(word, '<b class="found_' + i + '">' + word + '</b>');
								video.matches[ind].text=video_match;
							});
						}
                    });
					if (video.matches) {
						$.each(video.matches, function(ind,video_match) {
							caption+='<p data-target="scrol_zone_'+index+'" data-id="'+video.uuid+'" data-start="'+timestamp_to_seconds(video_match.start)+'" ><strong>'+timestamp_to_mmss(video_match.start)+'</strong>'+video_match.text+'</p>';
						});
						
					}
					if (video.description) {
						var string = video.description;
					} else {
						var string = '&nbsp;';	
					}
					
                    var created = video.created.split(".");
                    created = created[0];
                    created = created.substr(0, 16);

                    var object = '<article class="kw_grid_12">';
                    object += '<div class="kw_list_video kw_grid_3">';
                    object += '<a href="#" data-id="' + video.uuid + '" style="background-image:url(' + video.thumbnail_path + ')">';
                    if (_this.options.type == 'caption_portal') {
                        var progress = (video.current_transcript.confidence * 100).toFixed(2) + '%';
                        object += '<div class="kw_progress_bar" title="Progress: '+progress+'" style="top:10px;right:10px"><div class="kw_progress_fill" style="width:'+progress+'"></div></div>';
                    }
                    object += '</a>';
                    object += '</div>';
                    object += '<div class="kw_grid_7 kw_alpha">';
                    object += '<h2><a href="#" class="kw_video_list_link" data-id="' + video.uuid + '">' + video.title + '</a></h2>';
                    object += '<p class="kw_match">' + string + '</p>';
					if (caption) { 
                    	object += '<div class="kw_caption" ><div class="inner_scrol" id="scrol_zone_'+index+'">'+caption+'</div></div>'; 
					}
                    object += '</div>';
                    object += '<div class="kw_grid_3 kw_omega"><div class="kw_info">';
                    //object += '<span class="kw_views"><strong>' + Math.floor((Math.random() * 99) + 1) + '</strong> views</span>';
                    object += '<strong class="kw_time">' + time_tommss(video.size) + '</strong>';
                    //object += '<div class="kw_graph" title="My tooltip"><div class="bar" style="height:40%; ">&nbsp;</div><div class="bar">&nbsp;</div><div class="bar">&nbsp;</div><div class="bar" style="height:40%; ">&nbsp;</div></div>';
                    
					if (video.authors.length>0) { 
						var dispay_authors='';
						$.each(video.authors,function(i,author) {
							if (dispay_authors=='') {
								dispay_authors = author; } else {
								dispay_authors = dispay_authors+','+author; 
							}
						});
						object += '<a href="#" class="ico">'+dispay_authors+'</a>';
					}
					if (video.categories.length>0) { 
						var dispay_categs='';
						$.each(video.categories,function(i,categ) {
							if (dispay_categs=='') {
								dispay_categs = categ; } else {
								dispay_categs = dispay_categs+','+categ; 
							}
						});
					
                    	object += '<a href="#" class="ico cat">'+dispay_categs+'</a>';
					}
                    object += '<span class="kw_date">' + created + '</span>';
                    object += '</div></div>';
                    object += '</article>';
                    $('#results').append(object);
					if (caption) {
						$('#scrol_zone_'+index).sbscroller();
						setTimeout(function() {
						$('#scrol_zone_'+index).sbscroller('refresh');},500);
					}
					}});
            }
            //grid
            if (display_mode == "kw_result_grid") {
                var nr = current_nr;
                var object = '';
                var added = 0;

                if ( ! append) {
                    object += '<div class="video kw_grid_9" id="video_detail">';
                    object += '</div>';

                    object += '<div class="kw_grid_3">';
                }
				
                $.each(page_data.media, function (index, video) {
					if (video.status!==6 || video.status!==4) {
                    if (video.matches) {
					$.each(words_arr, function (i, word) {
                        video.matches[0].text = video.matches[0].text.replaceAll(word, '<b class="found_' + i + '">' + word + '</b>');
                    });
					}
                    object += '<article class="kw_grid_3 kw_alpha kw_omega kw_grid_video">';
                    object += '<a href="#" id="list_video_' + index + '" data-id="' + video.uuid + '" style="background-image:url(' + video.thumbnail_path + ')">';
                    object += '<span>';
                    object += '<strong>' + video.title + '</strong>';
					if (video.description) {
                    	object+='<em>'+video.description+'</em>';
					} else {
                    	object+='<em>&nbsp;</em>';
					}
                    object += '</span>';
                    object += '</a>';
                    object += '<div class="kw_info">';
                    if (_this.options.type == 'caption_portal') {
                        var progress = (video.current_transcript.confidence * 100).toFixed(2) + '%';
                        object += '<div class="kw_progress_bar" title="Progress: '+progress+'" style="top:-8px;right:0"><div class="kw_progress_fill" style="width:'+progress+'"></div></div>';
                    }
                    //object += '<span class="kw_views"><strong>' + Math.floor((Math.random() * 10) + 1) + '</strong> views</span>';
                    object += '<strong class="kw_time">' + time_tommss(video.size) + '</strong>';
                    //object += '<div class="kw_graph" title="My tooltip"><div class="bar" style="height:' + Math.floor((Math.random() * 80) + 1) + '%; " >&nbsp;</div><div class="bar">&nbsp;</div><div class="bar">&nbsp;</div><div class="bar" style="height:' + Math.floor((Math.random() * 80) + 1) + '%; ">&nbsp;</div></div>';
                    object += '</div>';
                    object += '</article>';
					}});

                if (append) {
                    $('#results > .kw_grid_3').append(object);
                } else {
				    object += '</div>';

                    $('#results').append(object);
                    if(this.options.media_uuid==='') {
                        if ($("#video_click_id").length > 0){
                            video_id = $('#video_click_id').html();
							var _this = this;
                            setTimeout(function () {
								//here click element trigger_external_video
								var element_to_click = $('.kw_grid_video a[data-id="'+video_id+'"]');
								if (element_to_click.length>0) {
                                $('.kw_grid_video a[data-id="'+video_id+'"]').click(); }
								else {
									_this.trigger_external_video(video_id);
								}
                                $('#video_click_id').remove();
                            },200);
                        } else {
                            setTimeout(function () {
                                $('#list_video_0').click();
                            }, 200);
                        }
                    } else {
                        //click on the first video
                        if(this.options.media_uuid==='') {
                            setTimeout(function () {
                                $('#list_video_0').click();
                            }, 200);
                        } else {
                            this.trigger_external_video(this.options.media_uuid);
                            delete this.options.media_uuid;
                        }
                    }
				
                }
            }
        }
		
		} else {
			$('.kw_search_results_holder').fadeOut(200,'',function() {
				$('.kw_search_enter').html("No results found for search query '<em>"+search_string+"</em>'").fadeIn(200);
			});
			
		}
    },
	hide_error:function() {
		$('.kw_search_error').fadeOut(100);
	},
	
	doRecent:function(event) {
		this.hide_error();
		$('#results').fadeOut(100, function () {
				$('#loading').fadeIn(100);
				$('#results').empty();
		});
		var title = 'Recent videos';	

        //TODO: remove this! use a proper render instead
        if (this.options.type == 'caption_portal') {
            title = 'Caption portal';
        }

		 $('#search_title').html(title);
		 $('#search_title').addClass('recent_trigger');
		 //$('.kw_pagination').hide();
            $('.kw_search_enter').fadeOut(100, function () {
                $('.kw_search_results_holder').fadeIn(100);
            });
		var search_params = new Object();
		var page = parseInt($('#page').val());
		search_params.count=12;
		search_params.order_way='ASC';
		search_params.order_by='upload_date';
		search_params.status_filter='all';
		search_params.deleted='0';
		search_params.start = search_params.count * page - search_params.count;

        //TODO: remove this! use a proper render instead
        if (this.options.type == 'caption_portal') {
            search_params.order_by = 'progress';
            search_params.unfinished_only = true;
        }

        var _this = this;
        this.init_infinitescroll($('#results'), function(page) {
            var uid = _this.options.user_uuid ? '&user_uuid=' + _this.options.user_uuid : '';
            search_params.start = search_params.count * page - search_params.count;
            return '../REST/media' + urlencodeQuery(search_params) + uid;
        }, function(json){
            _this.display_search_results(json, /*append*/true);
        });

        koemei.Media.get_all(this.xhr,search_params, this.options.auth,this.options.user_uuid, this.display_search_results);
        $('#page').val('1');
	},
	
	
    doSearch:function (event) {
		$('#search_title').removeClass('recent_trigger');
        event.preventDefault();
		this.hide_error();
		if (this.options.search_default="recent") {
			this.options.search_default='prompt';
			$('#page').val('1');
		}
        var string = $('#search_phrase').val();
        var len = string.length;
        if (len > 3 || (this.options.media_uuid!=='' && this.options.media_uuid!==undefined) ) {
			
			//$('#submit_btn').prop('type', 'button');
			$('#submit_btn').after('<button type="button" class="cancel" id="submit_btn2"></button>');
			$('#submit_btn').remove();
			$('#results').fadeOut(100, function () {
				$('#loading').fadeIn(100);
				$('#results').empty();
			});
			string = string.trim();
            var words = string.split(' ');
            var title = 'Search results for ';
            $.each(words, function (index, value) {
                title = title + '<strong>' + value + '</strong> ';
            });
			if(this.options.media_uuid!=='' && this.options.media_uuid!==undefined) {
				title='Detail';	
			}
            $('#search_title').html(title);
            //$('.kw_pagination').hide();
            $('.kw_search_enter').fadeOut(100, function () {
                $('.kw_search_results_holder').fadeIn(100);
            });
            //get params
            var sort_mode = $('.kw_sort_mode').attr('data-sort');
            var page = parseInt($('#page').val());
            //build params TODO - i have no ideea what the sort options are
            var search_params = new Object();
			
			// TO DO on details, i can't run search without a string, had to simulate
            search_params.search_query = string;
			if(this.options.media_uuid!=='' && this.options.media_uuid!==undefined) {
            	search_params.search_query = 'country';
			}
            search_params.count = 10;
            search_params.start = search_params.count * page - search_params.count;

            //TODO: remove this! use a proper render instead
            if (this.options.type == 'caption_portal') {
                search_params.order_by = 'progress';
                search_params.unfinished_only = true;
            }

            var _this = this;
            //TODO: this is code is the same as above, take to a separate function
            this.init_infinitescroll($('#results'), function(page) {
                var uid = _this.options.user_uuid ? '&user_uuid=' + _this.options.user_uuid : '';
                search_params.start = search_params.count * page - search_params.count;
                return '../REST/media' + urlencodeQuery(search_params) + uid;
            }, function(json){
                _this.display_search_results(json, /*append*/true);
            });

            koemei.Media.get_all(this.xhr, search_params, this.options.auth,this.options.user_uuid, this.display_search_results);
			$('#page').val('1');
        }
        else {
			$('.kw_search_error').fadeIn(100);
        }
    },

    init_infinitescroll:function ($el, path, callback) {
        this.destroy_infinitescroll($el);
        $el.infinitescroll({
            xhr: this.xhr,
            xhrFields: {
                method: "GET",
                headers: {
                    "Authorization": this.options.auth,
                    "Accept": 'application/json'
                }
            },
            loading: {
                msgText: "<em>Loading media...</em>",
                finishedMsg: "<em>No more media.</em>",
                selector: "#kw_infscr_loading"
            },
            navSelector: "#kw_next_page",
            nextSelector: "#kw_next_page",
            appendCallback: false,
            jsonField: "media",
            dataType: "json",
            path: path
        }, callback);
    },

    destroy_infinitescroll:function ($el) {
        $el.infinitescroll("destroy")
           .infinitescroll("unbind")
           .removeData("infinitescroll");
    },

    init_walkthrough:function () {
        this.$el.append(this.template_walkthrough);
    },

    show_walkthrough:function (event) {
        event.preventDefault();
        $('#kw_help_ol').joyride({
            preStepCallback: function(index, $next_tip){
                // there are some steps that need to do some stuff (eg: switch tabs)
                var $li = $('#kw_help_ol li:nth-child('+(index+1)+')');
                var kw_id = $li.data('id');
                var kw_class = $li.data('class');

                if (kw_class == 'transcript_edit') {
                    $('.'+kw_class).click();
                }
            }
        });
    },

    template_walkthrough: '\
        <ol id="kw_help_ol" style="display:none">\
            <li>\
                <h4>Welcome to Koemei widget</h4>\
                <p>Click \'Next\' to begin the tour.</p>\
            </li>\
            <li data-class="tips_toolbar">\
                <p>Info tab</p>\
            </li>\
            <li data-class="transcript_edit">\
                <p>Transcript tab</p>\
            </li>\
            <li data-id="widget_play">\
                <p>Play/Pause</p>\
                <p><em>Shortcut</em>: <strong>Ctrl</strong></p>\
            </li>\
            <li data-id="segment_prev">\
                <p>Previous segment</p>\
                <p><em>Shortcut</em>: <strong>Shift+Tab</strong></p>\
            </li>\
            <li data-id="prev_word">\
                <p>Previous word</p>\
                <p><em>Shortcut</em>: <strong>Shift+Left</strong></p>\
            </li>\
            <li data-id="next_word">\
                <p>Next word</p>\
                <p><em>Shortcut</em>: <strong>Shift+Right</strong></p>\
            </li>\
            <li data-id="segment_next">\
                <p>Next segment</p>\
                <p><em>Shortcut</em>: <strong>Tab</strong></p>\
            </li>\
            <li data-class="kw_setings">\
                <p>Transcript settings</p>\
            </li>\
            <li data-id="widget_search">\
                <p>Search inside the transcript</p>\
            </li>\
            <li data-class="kw_seeker">\
                <p>Search results will also appear in the video seek bar</p>\
            </li>\
            <li data-id="close_widget">\
                <p>Close the widget</p>\
            </li>\
        </ol>'

});

// JavaScript Document

var template_search = '\
        <div class="koemei_search_portal_wrapper">\
        <header class="kw_search_header">\
        <div class="kw_container_12">\
            <div class="kw_grid_12">\
                <form action="" method="post" id="kw_search_form">\
                    <button type="submit" id="submit_btn"></button>\
                    <input type="hidden" id="page" value="1">\
                    <input type="text" id="search_phrase" placeholder="search in video" style="border-right-width:0px">\
                    <a href="#" style="display:none">advanced search</a>\
                </form>\
                <div class="kw_search_error">Search string to small</div>\
            </div>\
        </div>\
	</header>\
    <div class="kw_search_enter kw_container_12" >\
        <div class="kw_grid_12">\
        Please enter your search\
        </div>\
    </div>\
    <div class="kw_search_results_holder kw_container_12" style="display:none">\
        <div class="kw_grid_12 kw_search_results_header">\
            <div class="kw_grid_8 kw_alpha">\
                <h2 id="search_title">Search results for <strong>search</strong> <strong>in</strong> <strong>video</strong></h2><span id="num_results" style="display:none">(148 results)</span>\
            </div>\
            <div class="kw_grid_4 kw_omega">\
            <ul class="kw_display-mode" data-mode="kw_result_grid">\
                <li>show:</li>\
                <li><a href="#" title="Grid" data-mode="kw_result_grid" id="btn_grid">Grid</a></li>\
                <li class="kw_active"><a href="#" title="List" class="list" id="btn_list"  data-mode="kw_result_list">List</a></li>\
            </ul>\
            <div class="kw_sort_mode" data-sort="default">\
                <a href="#">sort</a>\
                <ul>\
                    <li data-type="date">Date</li>\
                    <li data-type="name">Name</li>\
                    <li data-type="something">Something</li>\
                    <li data-type="else">Else</li>\
                </ul>\
            </div>\
            </div>\
        </div>\
        <div id="loading" class="kw_grid_12 kw_loading_bg">&nbsp;</div>\
        <div style="display:none" id="results" class="kw_result_grid">\
        </div>\
        <div class="kw_pagination kw_grid_12" style="display:none"></div>\
        <div id="kw_infscr_loading"></div>\
        <div id="kw_remaining"></div>\
        <a id="kw_next_page" href="#"></a>\
</div>\
</div>\
';

var template_widget = '\
<div class="kw_widget_overlay">&nbsp;</div>\
<div class="kw_koemei_widget <%= custom %> auto" style="width:<%= width %>px; margin-left:-<%= left_margin %>px; height:<%= height %>px; margin-top:-<%= top_margin %>px;">\
	<div id="kw_subtitles" style="bottom:<%= widget_height %>px; display:none;"></div>\
	<div id="widget_player" style="height:<%= video_height %>px;"></div>\
	<div id="widget_toolbar_hold"></div><div id="pannel_containter"></div>\
	<div id="transcript_pannel_containter"></div>\
	<div id="close_widget">&nbsp;</div>\
	<div class="kw_seeker" style="left:<%= left_space %>px;top:<%= video_height %>px; "></div>\
</div>';
// see http://stackoverflow.com/questions/574944/how-to-load-up-css-files-using-javascript
var doc = document; // shortcut
var cssId = 'myCss';  // you could encode the css path itself to generate id..
if (!doc.getElementById(cssId))
{
    var head  = doc.getElementsByTagName('head')[0];
    var link  = doc.createElement('link');
    link.id   = cssId;
    link.rel  = 'stylesheet';
    link.type = 'text/css';
    //link.href = static_cdn_path + '/widget/css/koemei-widget.css';
    //link.href =  'dist/koemei-widget.css';
    link.href = koemei_css_path;
    link.media = 'all';
    head.appendChild(link);
}
var cssId = 'fonts';  // you could encode the css path itself to generate id..
if (!doc.getElementById(cssId))
{
    var head  = doc.getElementsByTagName('head')[0];
    var link  = doc.createElement('link');
    link.id   = cssId;
    link.rel  = 'stylesheet';
    link.type = 'text/css';
	if (location.protocol==="http:") {
		link.href = 'http://fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic';
	} else {
		link.href = 'https://fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic'; 
	}
    link.media = 'all';
    head.appendChild(link);
}


})($, $);
// restore jquery previous objects
$.noConflict(true);
