/**
 * @license 
 * jQuery Tools @VERSION / Expose - Dim the lights
 * 
 * NO COPYRIGHTS OR LICENSES. DO WHAT YOU LIKE.
 * 
 * http://flowplayer.org/tools/toolbox/expose.html
 *
 * Since: Mar 2010
 * Date: @DATE 
 */
(function($) { 	

	// static constructs
	$.tools = $.tools || {version: '@VERSION'};

	var tool;

	tool = $.tools.expose = {

		conf: {	
			maskId: 'exposeMask',
			loadSpeed: 'slow',
			closeSpeed: 'none',
			closeOnClick: true,
			closeOnEsc: true,

			// css settings
			zIndex: 9998,
			opacity: 0.8,
			startOpacity: 0,
			color: '#fff',

			// callbacks
			onLoad: null,
			onClose: null
		}
	};

	/* one of the greatest headaches in the tool. finally made it */
	function viewport() {

		// the horror case
		if ($.browser.msie) {

			// if there are no scrollbars then use window.height
			var d = $(document).height(), w = $(window).height();

			return [
				window.innerWidth || 							// ie7+
				document.documentElement.clientWidth || 	// ie6  
				document.body.clientWidth, 					// ie6 quirks mode
				d - w < 20 ? w : d
			];
		} 

		// other well behaving browsers
		return [$(document).width(), $(document).height()]; 
	} 

	function call(fn) {
		if (fn) { return fn.call($.mask); }
	}

	var mask, exposed, loaded, config, overlayIndex;


	$.mask = {

		load: function(conf, els) {

			// already loaded ?
			if (loaded) { return this; }

			// configuration
			if (typeof conf == 'string') {
				conf = {color: conf};	
			}

			// use latest config
			conf = conf || config;

			config = conf = $.extend($.extend({}, tool.conf), conf);

			/* [+] ALPHA_CUSTOMIZE */
			var maxZindex = Math.max.apply(null, $.map($('body > *'), function (e, n) {
				
				if($(e).hasClass("Tooltip")) return true;
				if ($(e).css('position') == 'absolute')
					return parseInt($(e).css('z-index')) || conf.zIndex;
				})
			);
			if (isNaN(parseInt(maxZindex, 10)))
				maxZindex = conf.zIndex;
			conf.zIndex = maxZindex;

			mask = $(".exposeMask");
			if (mask.length > 0)
				conf.maskId = conf.maskId + "_" + (new Date()).getTime();
			/* [-] ALPHA_CUSTOMIZE */

			// get the mask
			mask = $("#" + conf.maskId);

			// or create it
			if (!mask.length) {
				mask = $('<div/>').attr("id", conf.maskId);
				mask.addClass("exposeMask"); /* ALPHA_CUSTOMIZE */
				$("body").append(mask);
			}

			// set position and dimensions
			var size = viewport();

			mask.css({
				position:'absolute', 
				top: 0, 
				left: 0,
				width: size[0],
				height: size[1],
				display: 'none',
				opacity: conf.startOpacity,			 		
				zIndex: conf.zIndex
			});

			if (conf.color) {
				mask.css("backgroundColor", conf.color);	
			}

			// onBeforeLoad
			if (call(conf.onBeforeLoad) === false) {
				return this;
			}

			// esc button
			if (conf.closeOnEsc) {
				$(document).on("keydown.mask", function(e) {
					if (e.keyCode == 27) {
						$.mask.close(e);	
					}
				});
			}

			// mask click closes
			if (conf.closeOnClick) {
				mask.on("click.mask", function(e)  {
					$.mask.close(e);
				});		
			}

			// resize mask when window is resized
			$(window).on("resize.mask", function() {
				$.mask.fit();
			});

			// exposed elements
			if (els && els.length) {
				// make sure element is positioned absolutely or relatively
				var overlayIndex = els.eq(0).css("zIndex");

				$.each(els, function() {
					var el = $(this);
					if (!/relative|absolute|fixed/i.test(el.css("position"))) {
						el.css("position", "relative");		
					}					
				});

				// make elements sit on top of the mask
				exposed = els.css({ zIndex: Math.max(conf.zIndex + 1, overlayIndex == 'auto' ? 0 : overlayIndex)});
			}	

			// reveal mask
			mask.css({display: 'block'}).fadeTo(conf.loadSpeed, conf.opacity, function() {
				$.mask.fit(); 
				call(conf.onLoad);
				//loaded = "full"; /* ALPHA_CUSTOMIZE */
			});

			//loaded = true; /* ALPHA_CUSTOMIZE */
			return this;
		},

		close: function() {
			var _mask = "";
			/* [+] ALPHA_CUSTOMIZE */
			var mask_highest_zindex = -1, mask_idx = -1, i = 0;
			$(".exposeMask").each(function() {
				var idx = parseInt($(this).css("zIndex"), 10);
				if(idx > mask_highest_zindex && !$(this).hasClass("remove")) {
					mask_highest_zindex = idx;
					mask_idx = i;
				}
				i++;
			});

			if (mask_idx > -1)
			{
				//loaded = true;
				_mask = $(".exposeMask").eq(mask_idx);
				_mask.addClass("remove");
			}
			/* [-] ALPHA_CUSTOMIZE */

			if (_mask.length > 0) {

				// onBeforeClose
				if (call(config.onBeforeClose) === false) { return this; }

				_mask.fadeOut(config.closeSpeed, function()  {
					call(config.onClose);
					if (exposed) {
						exposed.css({zIndex: overlayIndex});
					}				
					loaded = false;
					_mask.remove(); /* ALPHA_CUSTOMIZE */
				});

				// unbind various event listeners
				$(document).off("keydown.mask");
				_mask.off("click.mask");
				$(window).off("resize.mask");  
			}

			return this; 
		},

		fit: function() {
			if (1) {
				var size = viewport();
				mask.css({width: size[0], height: size[1]});
			}
		},

		getMask: function() {
			return mask;	
		},

		isLoaded: function(fully) {
			return fully ? loaded == 'full' : loaded;	
		}, 

		getConf: function() {
			return config;	
		},

		getExposed: function() {
			return exposed;	
		}		
	};

	$.fn.mask = function(conf) {
		$.mask.load(conf);
		return this;
	};

	$.fn.expose = function(conf) {
		$.mask.load(conf, this);
		return this;
	};


})(jQuery);