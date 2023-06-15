var supporttouch = "ontouchend" in document;
!supporttouch && (window.location.href = 'forum.php?mobile=1');

var platform = navigator.platform;
var ua = navigator.userAgent;
var ios = /iPhone|iPad|iPod/.test(platform) && ua.indexOf( "AppleWebKit" ) > -1;
var andriod = ua.indexOf( "Android" ) > -1;


(function($, window, document, undefined) {
	var dataPropertyName = "virtualMouseBindings",
		touchTargetPropertyName = "virtualTouchID",
		virtualEventNames = "vmouseover vmousedown vmousemove vmouseup vclick vmouseout vmousecancel".split(" "),
		touchEventProps = "clientX clientY pageX pageY screenX screenY".split( " " ),
		mouseHookProps = $.event.mouseHooks ? $.event.mouseHooks.props : [],
		mouseEventProps = $.event.props.concat( mouseHookProps ),
		activeDocHandlers = {},
		resetTimerID = 0,
		startX = 0,
		startY = 0,
		didScroll = false,
		clickBlockList = [],
		blockMouseTriggers = false,
		blockTouchTriggers = false,
		eventCaptureSupported = "addEventListener" in document,
		$document = $(document),
		nextTouchID = 1,
		lastTouchID = 0, threshold;
	$.vmouse = {
		moveDistanceThreshold: 10,
		clickDistanceThreshold: 10,
		resetTimerDuration: 1500
	};
	function getNativeEvent(event) {
		while( event && typeof event.originalEvent !== "undefined" ) {
			event = event.originalEvent;
		}
		return event;
	}
	function createVirtualEvent(event, eventType) {
		var t = event.type, oe, props, ne, prop, ct, touch, i, j, len;
		event = $.Event(event);
		event.type = eventType;
		oe = event.originalEvent;
		props = $.event.props;
		if(t.search(/^(mouse|click)/) > -1 ) {
			props = mouseEventProps;
		}
		if(oe) {
			for(i = props.length, prop; i;) {
				prop = props[ --i ];
				event[ prop ] = oe[ prop ];
			}
		}
		if(t.search(/mouse(down|up)|click/) > -1 && !event.which) {
			event.which = 1;
		}
		if(t.search(/^touch/) !== -1) {
			ne = getNativeEvent(oe);
			t = ne.touches;
			ct = ne.changedTouches;
			touch = (t && t.length) ? t[0] : (( ct && ct.length) ? ct[0] : undefined);
			if(touch) {
				for(j = 0, len = touchEventProps.length; j < len; j++) {
					prop = touchEventProps[j];
					event[prop] = touch[prop];
				}
			}
		}
		return event;
	}
	function getVirtualBindingFlags(element) {
		var flags = {},
			b, k;
		while(element) {
			b = $.data(element, dataPropertyName);
			for(k in b) {
				if(b[k]) {
					flags[k] = flags.hasVirtualBinding = true;
				}
			}
			element = element.parentNode;
		}
		return flags;
	}
	function getClosestElementWithVirtualBinding(element, eventType) {
		var b;
		while(element) {
			b = $.data( element, dataPropertyName );
			if(b && (!eventType || b[eventType])) {
				return element;
			}
			element = element.parentNode;
		}
		return null;
	}
	function enableTouchBindings() {
		blockTouchTriggers = false;
	}
	function disableTouchBindings() {
		blockTouchTriggers = true;
	}
	function enableMouseBindings() {
		lastTouchID = 0;
		clickBlockList.length = 0;
		blockMouseTriggers = false;
		disableTouchBindings();
	}
	function disableMouseBindings() {
		enableTouchBindings();
	}
	function startResetTimer() {
		clearResetTimer();
		resetTimerID = setTimeout(function() {
			resetTimerID = 0;
			enableMouseBindings();
		}, $.vmouse.resetTimerDuration);
	}
	function clearResetTimer() {
		if(resetTimerID ) {
			clearTimeout(resetTimerID);
			resetTimerID = 0;
		}
	}
	function triggerVirtualEvent(eventType, event, flags) {
		var ve;
		if((flags && flags[eventType]) ||
					(!flags && getClosestElementWithVirtualBinding(event.target, eventType))) {
			ve = createVirtualEvent(event, eventType);
			$(event.target).trigger(ve);
		}
		return ve;
	}
	function mouseEventCallback(event) {
		var touchID = $.data(event.target, touchTargetPropertyName);
		if(!blockMouseTriggers && (!lastTouchID || lastTouchID !== touchID)) {
			var ve = triggerVirtualEvent("v" + event.type, event);
			if(ve) {
				if(ve.isDefaultPrevented()) {
					event.preventDefault();
				}
				if(ve.isPropagationStopped()) {
					event.stopPropagation();
				}
				if(ve.isImmediatePropagationStopped()) {
					event.stopImmediatePropagation();
				}
			}
		}
	}
	function handleTouchStart(event) {
		var touches = getNativeEvent(event).touches,
			target, flags;
		if(touches && touches.length === 1) {
			target = event.target;
			flags = getVirtualBindingFlags(target);
			if(flags.hasVirtualBinding) {
				lastTouchID = nextTouchID++;
				$.data(target, touchTargetPropertyName, lastTouchID);
				clearResetTimer();
				disableMouseBindings();
				didScroll = false;
				var t = getNativeEvent(event).touches[0];
				startX = t.pageX;
				startY = t.pageY;
				triggerVirtualEvent("vmouseover", event, flags);
				triggerVirtualEvent("vmousedown", event, flags);
			}
		}
	}
	function handleScroll(event) {
		if(blockTouchTriggers) {
			return;
		}
		if(!didScroll) {
			triggerVirtualEvent("vmousecancel", event, getVirtualBindingFlags(event.target));
		}
		didScroll = true;
		startResetTimer();
	}
	function handleTouchMove(event) {
		if(blockTouchTriggers) {
			return;
		}
		var t = getNativeEvent(event).touches[0],
			didCancel = didScroll,
			moveThreshold = $.vmouse.moveDistanceThreshold,
			flags = getVirtualBindingFlags(event.target);
			didScroll = didScroll ||
				(Math.abs(t.pageX - startX) > moveThreshold ||
					Math.abs(t.pageY - startY) > moveThreshold);
		if(didScroll && !didCancel) {
			triggerVirtualEvent("vmousecancel", event, flags);
		}
		triggerVirtualEvent("vmousemove", event, flags);
		startResetTimer();
	}
	function handleTouchEnd(event) {
		if(blockTouchTriggers) {
			return;
		}
		disableTouchBindings();
		var flags = getVirtualBindingFlags(event.target), t;
		triggerVirtualEvent("vmouseup", event, flags);
		if(!didScroll) {
			var ve = triggerVirtualEvent("vclick", event, flags);
			if(ve && ve.isDefaultPrevented()) {
				t = getNativeEvent(event).changedTouches[0];
				clickBlockList.push({
					touchID: lastTouchID,
					x: t.clientX,
					y: t.clientY
				});
				blockMouseTriggers = true;
			}
		}
		triggerVirtualEvent("vmouseout", event, flags);
		didScroll = false;
		startResetTimer();
	}
	function hasVirtualBindings(ele) {
		var bindings = $.data( ele, dataPropertyName ), k;
		if(bindings) {
			for(k in bindings) {
				if(bindings[k]) {
					return true;
				}
			}
		}
		return false;
	}
	function dummyMouseHandler() {}

	function getSpecialEventObject(eventType) {
		var realType = eventType.substr(1);
		return {
			setup: function(data, namespace) {
				if(!hasVirtualBindings(this)) {
					$.data(this, dataPropertyName, {});
				}
				var bindings = $.data(this, dataPropertyName);
				bindings[eventType] = true;
				activeDocHandlers[eventType] = (activeDocHandlers[eventType] || 0) + 1;
				if(activeDocHandlers[eventType] === 1) {
					$document.bind(realType, mouseEventCallback);
				}
				$(this).bind(realType, dummyMouseHandler);
				if(eventCaptureSupported) {
					activeDocHandlers["touchstart"] = (activeDocHandlers["touchstart"] || 0) + 1;
					if(activeDocHandlers["touchstart"] === 1) {
						$document.bind("touchstart", handleTouchStart)
							.bind("touchend", handleTouchEnd)
							.bind("touchmove", handleTouchMove)
							.bind("scroll", handleScroll);
					}
				}
			},
			teardown: function(data, namespace) {
				--activeDocHandlers[eventType];
				if(!activeDocHandlers[eventType]) {
					$document.unbind(realType, mouseEventCallback);
				}
				if(eventCaptureSupported) {
					--activeDocHandlers["touchstart"];
					if(!activeDocHandlers["touchstart"]) {
						$document.unbind("touchstart", handleTouchStart)
							.unbind("touchmove", handleTouchMove)
							.unbind("touchend", handleTouchEnd)
							.unbind("scroll", handleScroll);
					}
				}
				var $this = $(this),
					bindings = $.data(this, dataPropertyName);
				if(bindings) {
					bindings[eventType] = false;
				}
				$this.unbind(realType, dummyMouseHandler);
				if(!hasVirtualBindings(this)) {
					$this.removeData(dataPropertyName);
				}
			}
		};
	}
	for(var i = 0; i < virtualEventNames.length; i++) {
		$.event.special[virtualEventNames[i]] = getSpecialEventObject(virtualEventNames[i]);
	}
	if(eventCaptureSupported) {
		document.addEventListener("click", function(e) {
			var cnt = clickBlockList.length,
				target = e.target,
				x, y, ele, i, o, touchID;
			if(cnt) {
				x = e.clientX;
				y = e.clientY;
				threshold = $.vmouse.clickDistanceThreshold;
				ele = target;
				while(ele) {
					for(i = 0; i < cnt; i++) {
						o = clickBlockList[i];
						touchID = 0;
						if((ele === target && Math.abs(o.x - x) < threshold && Math.abs(o.y - y) < threshold) ||
									$.data(ele, touchTargetPropertyName) === o.touchID) {
							e.preventDefault();
							e.stopPropagation();
							return;
						}
					}
					ele = ele.parentNode;
				}
			}
		}, true);
	}
})(jQuery, window, document);

(function($, window, undefined) {
	function triggercustomevent(obj, eventtype, event) {
		var origtype = event.type;
		event.type = eventtype;
		$.event.handle.call(obj, event);
		event.type = origtype;
	}

	$.event.special.tap = {
		setup : function() {
			var thisobj = this;
			var obj = $(thisobj);
			obj.on('vmousedown', function(e) {
				if(e.which && e.which !== 1) {
					return false;
				}
				var origtarget = e.target;
				var origevent = e.originalEvent;
				var timer;

				function cleartaptimer() {
					clearTimeout(timer);
				}
				function cleartaphandlers() {
					cleartaptimer();
					obj.off('vclick', clickhandler)
						.off('vmouseup', cleartaptimer);
					$(document).off('vmousecancel', cleartaphandlers);
				}

				function clickhandler(e) {
					cleartaphandlers();
					if(origtarget === e.target) {
						triggercustomevent(thisobj, 'tap', e);
					}
					return false;
				}

				obj.on('vmouseup', cleartaptimer)
					.on('vclick', clickhandler)
				$(document).on('touchcancel', cleartaphandlers);

				timer = setTimeout(function() {
					triggercustomevent(thisobj, 'taphold', $.Event('taphold', {target:origtarget}));
				}, 750);
				return false;
			});
		}
	};
	$.each(('tap').split(' '), function(index, name) {
		$.fn[name] = function(fn) {
			return this.on(name, fn);
		};
	});

})(jQuery, this);

var page = {
	converthtml : function() {
		var prevpage = $('div.pg .prev').prop('href');
		var nextpage = $('div.pg .nxt').prop('href');
		var lastpage = $('div.pg label span').text().replace(/[^\d]/g, '') || 0;
		var curpage = $('div.pg input').val() || 1;

		if(!lastpage) {
			prevpage = $('div.pg .pgb a').prop('href');
		}

		var prevpagehref = nextpagehref = '';
		if(prevpage == undefined) {
			prevpagehref = 'javascript:;" class="grey';
		} else {
			prevpagehref = prevpage;
		}
		if(nextpage == undefined) {
			nextpagehref = 'javascript:;" class="grey';
		} else {
			nextpagehref = nextpage;
		}

		var selector = '';
		if(lastpage) {
			selector += '<a id="select_a" style="margin:0 2px;padding:1px 0 0 0;border:0;display:inline-block;position:relative;width:100px;height:31px;line-height:27px;background:url('+STATICURL+'/image/mobile/images/pic_select.png) no-repeat;text-align:left;text-indent:20px;">';
			selector += '<select id="dumppage" style="position:absolute;left:0;top:0;height:27px;opacity:0;width:100px;">';
			for(var i=1; i<=lastpage; i++) {
				selector += '<option value="'+i+'" '+ (i == curpage ? 'selected' : '') +'>第'+i+'页</option>';
			}
			selector += '</select>';
			selector += '<span>第'+curpage+'页</span>';
		}

		$('div.pg').removeClass('pg').addClass('page').html('<a href="'+ prevpagehref +'">上一页</a>'+ selector +'<a href="'+ nextpagehref +'">下一页</a>');
		$('#dumppage').on('change', function() {
			var href = (prevpage || nextpage);
			window.location.href = href.replace(/page=\d+/, 'page=' + $(this).val());
		});
	},
};

var scrolltop = {
	obj : null,
	init : function(obj) {
		scrolltop.obj = obj;
		var fixed = this.isfixed();
		obj.css('opacity', '.618');
		if(fixed) {
			obj.css('bottom', '8px');
		} else {
			obj.css({'visibility':'visible', 'position':'absolute'});
		}
		$(window).on('resize', function() {
			if(fixed) {
				obj.css('bottom', '8px');
			} else {
				obj.css('top', ($(document).scrollTop() + $(window).height() - 40) + 'px');
			}
		});
		obj.on('tap', function() {
			$(document).scrollTop($(document).height());
		});
		$(document).on('scroll', function() {
			if(!fixed) {
				obj.css('top', ($(document).scrollTop() + $(window).height() - 40) + 'px');
			}
			if($(document).scrollTop() >= 400) {
				obj.removeClass('bottom')
				.off().on('tap', function() {
					window.scrollTo('0', '1');
				});
			} else {
				obj.addClass('bottom')
				.off().on('tap', function() {
					$(document).scrollTop($(document).height());
				});
			}
		});

	},
	isfixed : function() {
		var offset = scrolltop.obj.offset();
		var scrollTop = $(window).scrollTop();
		var screenHeight = document.documentElement.clientHeight;
		if(offset == undefined) {
			return false;
		}
		if(offset.top < scrollTop || (offset.top - scrollTop) > screenHeight) {
			return false;
		} else {
			return true;
		}
	}
};

var img = {
	init : function(is_err_t) {
		var errhandle = this.errorhandle;
		$('img').on('load', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			if(obj.width() < 5 && obj.height() < 10 && obj.css('display') != 'none') {
				return errhandle(obj, is_err_t);
			}
			obj.css('display', 'inline');
			obj.css('visibility', 'visible');
			if(obj.width() > window.innerWidth) {
				obj.css('width', window.innerWidth);
			}
			obj.parent().find('.loading').remove();
			obj.parent().find('.error_text').remove();
		})
		.on('error', function() {
			var obj = $(this);
			obj.attr('zsrc', obj.attr('src'));
			errhandle(obj, is_err_t);
		});
	},
	errorhandle : function(obj, is_err_t) {
		if(obj.attr('noerror') == 'true') {
			return;
		}
		obj.css('visibility', 'hidden');
		obj.css('display', 'none');
		var parentnode = obj.parent();
		parentnode.find('.loading').remove();
		parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
		var loadnums = parseInt(obj.attr('load')) || 0;
		if(loadnums < 3) {
			obj.attr('src', obj.attr('zsrc'));
			obj.attr('load', ++loadnums);
			return false;
		}
		if(is_err_t) {
			var parentnode = obj.parent();
			parentnode.find('.loading').remove();
			parentnode.append('<div class="error_text">点击重新加载</div>');
			parentnode.find('.error_text').one('click', function() {
				obj.attr('load', 0).find('.error_text').remove();
				parentnode.append('<div class="loading" style="background:url('+ IMGDIR +'/imageloading.gif) no-repeat center center;width:'+parentnode.width()+'px;height:'+parentnode.height()+'px"></div>');
				obj.attr('src', obj.attr('zsrc'));
			});
		}
		return false;
	}
};

var atap = {
	init : function() {
		$('.atap').on('tap', function() {
			var obj = $(this);
			obj.css({'background':'#6FACD5', 'color':'#FFFFFF', 'font-weight':'bold', 'text-decoration':'none', 'text-shadow':'0 1px 1px #3373A5'});
			return false;
		});
		$('.atap a').off('click');
	}
};


var POPMENU = new Object;
var popup = {
	init : function() {
		var $this = this;
		$('.popup').each(function(index, obj) {
			obj = $(obj);
			var pop = $(obj.attr('href'));
			if(pop && pop.attr('popup')) {
				pop.css({'display':'none'});
				obj.on('click', function(e) {
					$this.open(pop);
				});
			}
		});
		this.maskinit();
	},
	maskinit : function() {
		var $this = this;
		$('#mask').off().on('tap', function() {
			$this.close();
		});
	},

	open : function(pop, type, url) {
		this.close();
		this.maskinit();
		if(typeof pop == 'string') {
			$('#ntcmsg').remove();
			if(type == 'alert') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><input class="button2" type="button" value="确定" onclick="popup.close();"></dd></div>'
			} else if(type == 'confirm') {
				pop = '<div class="tip"><dt>'+ pop +'</dt><dd><input class="redirect button2" type="button" value="确定" href="'+ url +'"><a href="javascript:;" onclick="popup.close();">取消</a></dd></div>'
			}
			$('body').append('<div id="ntcmsg" style="display:none;">'+ pop +'</div>');
			pop = $('#ntcmsg');
		}
		if(POPMENU[pop.attr('id')]) {
			$('#' + pop.attr('id') + '_popmenu').html(pop.html()).css({'height':pop.height()+'px', 'width':pop.width()+'px'});
		} else {
			pop.parent().append('<div class="dialogbox" id="'+ pop.attr('id') +'_popmenu" style="border:1px solid #D7D7D7; padding: 20px; height:'+ pop.height() +'px;width:'+ pop.width() +'px;">'+ pop.html() +'</div>');
		}
		var popupobj = $('#' + pop.attr('id') + '_popmenu');
		var left = (window.innerWidth - popupobj.width()) / 2;
		var top = (document.documentElement.clientHeight - popupobj.height()) / 2;
		popupobj.css({'display':'block','position':'fixed','left':left,'top':top,'z-index':120,'opacity':1});
		$('#mask').css({'display':'block','width':'100%','height':'100%','position':'fixed','top':'0','left':'0','background':'black','opacity':'0.2','z-index':'100'});
		POPMENU[pop.attr('id')] = pop;
	},
	close : function() {
		$('#mask').css('display', 'none');
		$.each(POPMENU, function(index, obj) {
			$('#' + index + '_popmenu').css('display','none');
		});
	}
};

var dialog = {
	init : function() {
		$(document).on('click', '.dialog', function() {
			var obj = $(this);
			popup.open('请稍后 ...');
			$.ajax({
				type : 'GET',
				url : obj.attr('href') + '&inajax=1',
				dataType : 'xml'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
			})
			.error(function() {
				window.location.href = obj.attr('href');
				popup.close();
			});
			return false;
		});
	},

};

var formdialog = {
	init : function() {
		$(document).on('click', '.formdialog', function() {
			popup.open('请稍后 ...');
			var obj = $(this);
			var formobj = $(this.form);
			$.ajax({
				type:'POST',
				url:formobj.attr('action') + '&handlekey='+ formobj.attr('id') +'&inajax=1',
				data:formobj.serialize(),
				dataType:'xml'
			})
			.success(function(s) {
				popup.open(s.lastChild.firstChild.nodeValue);
				evalscript(s.lastChild.firstChild.nodeValue);
			})
			.error(function() {
				window.location.href = obj.attr('href');
				popup.close();
			});
			return false;
		});
	}
};

var redirect = {
	init : function() {
		$(document).on('click', '.redirect', function() {
			var obj = $(this);
			popup.close();
			window.location.href = obj.attr('href');
		});
	}
};

var DISMENU = new Object;
var display = {
	init : function() {
		var $this = this;
		$('.display').each(function(index, obj) {
			obj = $(obj);
			var dis = $(obj.attr('href'));
			if(dis && dis.attr('display')) {
				dis.css({'display':'none'});
				dis.css({'z-index':'102'});
				DISMENU[dis.attr('id')] = dis;
				obj.on('click', function(e) {
					if(in_array(e.target.tagName, ['A', 'IMG', 'INPUT'])) return;
					$this.maskinit();
					if(dis.attr('display') == 'true') {
						dis.css('display', 'block');
						dis.attr('display', 'false');
						$('#mask').css({'display':'block','width':'100%','height':'100%','position':'fixed','top':'0','left':'0','background':'transparent','z-index':'100'});
					}
					return false;
				});
			}
		});
	},
	maskinit : function() {
		var $this = this;
		$('#mask').off().on('touchstart', function() {
			$this.hide();
		});
	},
	hide : function() {
		$('#mask').css('display', 'none');
		$.each(DISMENU, function(index, obj) {
			obj.css('display', 'none');
			obj.attr('display', 'true');
		});
	}
};

var geo = {
	latitude : null,
	longitude : null,
	loc : null,
	errmsg : null,
	timeout : 5000,
	getcurrentposition : function() {
		if(!!navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(this.locationsuccess, this.locationerror, {
				enableHighAcuracy : true,
				timeout : this.timeout,
				maximumAge : 3000
			});
		}
	},
	locationerror : function(error) {
		geo.errmsg = 'error';
		switch(error.code) {
			case error.TIMEOUT:
				geo.errmsg = "获取位置超时，请重试";
				break;
			case error.POSITION_UNAVAILABLE:
				geo.errmsg = '无法检测到您的当前位置';
			    break;
		    case error.PERMISSION_DENIED:
		        geo.errmsg = '请允许能够正常访问您的当前位置';
		        break;
		    case error.UNKNOWN_ERROR:
		        geo.errmsg = '发生未知错误';
		        break;
		}
	},
	locationsuccess : function(position) {
		geo.latitude = position.coords.latitude;
		geo.longitude = position.coords.longitude;
		geo.errmsg = '';
		$.ajax({
			type:'POST',
			url:'http://maps.google.com/maps/api/geocode/json?latlng=' + geo.latitude + ',' + geo.longitude + '&language=zh-CN&sensor=true',
			dataType:'json'
		})
		.success(function(s) {
			if(s.status == 'OK') {
				geo.loc = s.results[0].formatted_address;
			}
		})
		.error(function() {
			geo.loc = null;
		});
	}
};

var pullrefresh = {
	init : function() {
		var pos = {};
		var status = false;
		var divobj = null;
		var contentobj = null;
		var reloadflag = false;
		$('body').on('touchstart', function(e) {
			e = mygetnativeevent(e);
			pos.startx = e.touches[0].pageX;
			pos.starty = e.touches[0].pageY;
		})
		.on('touchmove', function(e) {
			e = mygetnativeevent(e);
			pos.curposx = e.touches[0].pageX;
			pos.curposy = e.touches[0].pageY;
			if(pos.curposy - pos.starty < 0 && !status) {
				return;
			}
			if(!status && $(window).scrollTop() <= 0) {
				status = true;
				divobj = document.createElement('div');
				divobj = $(divobj);
				divobj.css({'position':'relative', 'margin-left':'-85px'});
				$('body').prepend(divobj);
				contentobj = document.createElement('div');
				contentobj = $(contentobj);
				contentobj.css({'position':'absolute', 'height':'30px', 'top': '-30px', 'left':'50%'});
				contentobj.html('<img src="'+ STATICURL + 'image/mobile/images/icon_arrow.gif" style="vertical-align:middle;margin-right:5px;-moz-transform:rotate(180deg);-webkit-transform:rotate(180deg);-o-transform:rotate(180deg);transform:rotate(180deg);"><span id="refreshtxt">下拉可以刷新</span>');
				contentobj.find('img').css({'-webkit-transition':'all 0.5s ease-in-out'});
				divobj.prepend(contentobj);
				pos.topx = pos.curposx;
				pos.topy = pos.curposy;
			}
			if(!status) {
				return;
			}
			if(status == true) {
				var pullheight = pos.curposy - pos.topy;
				if(pullheight >= 0 && pullheight < 150) {
					divobj.css({'height': pullheight/2 + 'px'});
					contentobj.css({'top': (-30 + pullheight/2) + 'px'});
					if(reloadflag) {
						contentobj.find('img').css({'-webkit-transform':'rotate(180deg)', '-moz-transform':'rotate(180deg)', '-o-transform':'rotate(180deg)', 'transform':'rotate(180deg)'});
						contentobj.find('#refreshtxt').html('下拉可以刷新');
					}
					reloadflag = false;
				} else if(pullheight >= 150) {
					divobj.css({'height':pullheight/2 + 'px'});
					contentobj.css({'top': (-30 + pullheight/2) + 'px'});
					if(!reloadflag) {
						contentobj.find('img').css({'-webkit-transform':'rotate(360deg)', '-moz-transform':'rotate(360deg)', '-o-transform':'rotate(360deg)', 'transform':'rotate(360deg)'});
						contentobj.find('#refreshtxt').html('松开可以刷新');
					}
					reloadflag = true;
				}
			}
			e.preventDefault();
		})
		.on('touchend', function(e) {
			if(status == true) {
				if(reloadflag) {
					contentobj.html('<img src="'+ STATICURL + 'image/mobile/images/icon_load.gif" style="vertical-align:middle;margin-right:5px;">正在加载...');
					contentobj.animate({'top': (-30 + 75) + 'px'}, 618, 'linear');
					divobj.animate({'height': '75px'}, 618, 'linear', function() {
						window.location.reload();
					});
					return;
				}
			}
			divobj.remove();
			divobj = null;
			status = false;
			pos = {};
		});
	}
};

function mygetnativeevent(event) {

	while(event && typeof event.originalEvent !== "undefined") {
		event = event.originalEvent;
	}
	return event;
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

var safescripts = {}, evalscripts = [];

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $('#' + id)[0]) {
		$('#' + id)[0].parentNode.removeChild($('#' + id)[0]);
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (!document.charset ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	if(seconds) {
		var expires = new Date();
		expires.setTime(expires.getTime() + seconds * 1000);
	}
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

$(document).ready(function() {

	if($('div.pg').length > 0) {
		page.converthtml();
	}
	if($('.scrolltop').length > 0) {
		scrolltop.init($('.scrolltop'));
	}
	if($('img').length > 0) {
		img.init(1);
	}
	if($('.popup').length > 0) {
		popup.init();
	}
	if($('.display').length > 0) {
		display.init();
	}
	if($('.atap').length > 0) {
		atap.init();
	}
	if($('.pullrefresh').length > 0) {
		pullrefresh.init();
	}
	dialog.init();
	formdialog.init();
	redirect.init();
});