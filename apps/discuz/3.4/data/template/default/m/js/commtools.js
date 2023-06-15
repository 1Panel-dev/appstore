var TOOLS = {
	charset: 'utf-8',
	getcookie: function (name) {
		var cookie_start = document.cookie.indexOf(name);
		var cookie_end = document.cookie.indexOf(";", cookie_start);
		return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
	},
	isWX: function () {
		return /micromessenger/.test(navigator.userAgent.toLowerCase()) || typeof WeixinJSBridge !== 'undefined' ? true : false;
	},
	isMQ: function () {
		return mqq && mqq.device && mqq.device.isMobileQQ() ? true : false;
	},
	isQQBrowser: function () {
		return /mqqbrowser/.test(navigator.userAgent.toLowerCase());
	},
	setcookie: function (cookieName, cookieValue, seconds, path, domain, secure) {
		var expires = new Date();
		expires.setTime(expires.getTime() + seconds);
		document.cookie = escape(cookieName) + '=' + escape(cookieValue)
			+ (expires ? '; expires=' + expires.toGMTString() : '')
			+ (path ? '; path=' + path : '; path=/')
			+ (domain ? '; domain=' + domain : '')
			+ (secure ? '; secure' : '');
	},
	getQuery: function (key) {
		var search = window.location.search;
		if (search.indexOf('?') != -1) {
			var params = search.substr(1).split('&');
			var query = {};
			var q = [];
			var name = '';

			for (i = 0; i < params.length; i++) {
				q = params[i].split('=');
				name = decodeURIComponent(q[0]);

				if (name.substr(-2) == '[]') {
					if (!query[name]) {
						query[name] = [];
					}
					query[name].push(q[1]);
				} else {
					query[name] = q[1];
				}

			}
			if (key) {
				if (query[key]) {
					return query[key];
				}

				return null;
			} else {
				return query;
			}
		}
	},
	trim: function (str) {
		return str.replace(/(^\s*)|(\s*$)/g, '');
	},
	isObjectEmpty: function (obj) {
		for (i in obj) {
			return false;
		}
		return true;
	},
	strlen: function (str) {
		return (/msie/.test(navigator.userAgent.toLowerCase()) && str.indexOf('\n') !== -1) ? str.replace(/\r?\n/g, '_').length : str.length;
	},
	mb_strlen: function (str) {
		if (typeof str === 'undefined') {
			return 0;
		}
		var len = 0;
		for (var i = 0; i < str.length; i++) {
			len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (TOOLS.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1;
		}
		return len;
	},
	mb_cutstr: function (str, maxlen, dot) {
		var len = 0;
		var ret = '';
		var dot = !dot && dot !== '' ? '...' : dot;
		maxlen = maxlen - dot.length;
		for (var i = 0; i < str.length; i++) {
			len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (TOOLS.charset.toLowerCase() === 'utf-8' ? 3 : 2) : 1;
			if (len > maxlen) {
				ret += dot;
				break;
			}
			ret += str.substr(i, 1);
		}
		return ret;
	},
	strLenCalc: function (obj, showId, maxlen) {
		if (typeof obj === 'undefined') {
			return 0;
		}
		var v = obj.value, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = TOOLS.strlen(v);
		for (var i = 0; i < v.length; i++) {
			if (v.charCodeAt(i) < 0 || v.charCodeAt(i) > 127) {
				curlen -= 2;
			} else {
				curlen -= 1;
			}
		}
		jQuery('#' + showId).html(Math.floor(curlen / 2));
	},
	htmlEncode: function (text) {
		return text.replace(/&/g, '&amp').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},
	htmlDecode: function (text) {
		return text.replace(/&amp;/g, '&').replace(/&quot;/g, '/"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	},
	dialog: function (opts) {
		var opts = opts || {};
		var dId = opts.id || 'tips';
		var dialogId = 'fwin_dialog_' + dId;
		var maskId = 'fwin_mask_' + dId;

		if (!opts.content) {
			document.ontouchmove = function (e) {
				return true;
			};
			jQuery('#' + dialogId).remove();
			jQuery('#' + maskId).remove();
			return false;
		}

		var title = opts.title || '提示信息';
		var content = opts.content || '';

		var btnOk = opts.okValue || false;
		var btnCancel = opts.cancelValue || false;

		var isShowMask = opts.isMask || false;

		var existDialogCount = jQuery('div[id^="fwin_dialog_"]').length || 0;
		var maskZIndex = 10000 + existDialogCount * 10;
		var dialogZIndex = maskZIndex + 1;

		var maskStyle = 'position:absolute;top:-0px;left:-0px;width:' + jQuery(document).width() + 'px;height:' + jQuery(document).height() + 'px;background:#000;filter:alpha(opacity=60);opacity:0.5; z-index:' + maskZIndex + ';';

		var isHtml = opts.isHtml || false;

		var autoClose = opts.autoClose || false;

		var isConfirm = opts.isConfirm || false;

		var iconClass = '';
		switch (opts.icon) {
			case 'success':
				iconClass = 'icon_success';
				break;
			case 'none':
				iconClass = '';
				break;
			case 'error':
			default:
				iconClass = 'g-layer-tips';
				break;
		}

		var dialogHtmlArr = [];
		if (isShowMask) {
			var dialogMaskHtmlArr = [];
			dialogMaskHtmlArr.push('<div id=' + maskId + ' class="g-mask" style="' + maskStyle + '"></div>');
			var dialogMaskHtml = dialogMaskHtmlArr.join('');
			jQuery(dialogMaskHtml).appendTo('body');
			document.ontouchmove = function (e) {
				e.preventDefault();
			};
		}

		if (isHtml) {
			dialogHtmlArr.push('<div style="min-width:350px;position:fixed;z-index:' + dialogZIndex + ';" id="' + dialogId + '"><span class="close"></span>');
			dialogHtmlArr.push('<div class="popLayer pSpace" style="width:80%">');
			dialogHtmlArr.push('<p class="editArea">' + content + '</p></div></div>');
		} else {
			if (!opts.title && !btnOk && !btnCancel) {
				dialogHtmlArr.push('<div class="tips" style="position:fixed;z-index:' + dialogZIndex + ';" id="' + dialogId + '">');
				if (dId == 'loading') {
					dialogHtmlArr.push('<div class="loadInco tipL" style="vertical-align: -5px;"><span class="blockG" id="rotateG_01"></span><span class="blockG" id="rotateG_02"></span><span class="blockG" id="rotateG_03"></span><span class="blockG" id="rotateG_04"></span><span class="blockG" id="rotateG_05"></span><span class="blockG" id="rotateG_06"></span><span class="blockG" id="rotateG_07"></span><span class="blockG" id="rotateG_08"></span></div> ');
				}
				dialogHtmlArr.push(content + '</div>');
			} else if (isConfirm) {
				if (confirm(content)) {
					if (typeof opts.ok == 'function') {
						opts.ok();
					}

				} else {
					if (typeof opts.cancel == 'function') {
						opts.cancel();
					}
				}
				return true;
			} else {
				dialogHtmlArr.push('<div style="min-width:350px;position:fixed;z-index:' + dialogZIndex + ';" id="' + dialogId + '"><span class="close"></span>');
				dialogHtmlArr.push('<div class="popLayer pSpace" style="width:80%">');
				dialogHtmlArr.push('<p class="editTCon">' + content + '</p>');
				dialogHtmlArr.push('<div class="editArea">');
				dialogHtmlArr.push(btnOk ? '<a href="javascript:;" class="editBtn1 db" title="">' + btnOk + '</a>' : '');
				dialogHtmlArr.push(btnCancel ? '<a href="javascript:;" class="editBtn2 db" title="">' + btnCancel + '</a>' : '');
				dialogHtmlArr.push('</div></div>');
			}
		}
		var dialogHtml = dialogHtmlArr.join('');
		if (jQuery('#' + dialogId)[0]) {
			jQuery('#' + dialogId).remove();
			jQuery('#' + maskId).remove();
		}
		jQuery(dialogHtml).appendTo('body');

		var clientWidth = jQuery(window).width();
		var clientHeight = jQuery(window).height();
		dialogLeft = (clientWidth - jQuery('#' + dialogId).outerWidth()) / 2;
		dialogTop = (clientHeight - jQuery('#' + dialogId).height()) * 0.382;

		var dialogLeft = opts.left || dialogLeft;
		var dialogTop = opts.top || dialogTop;

		jQuery("#" + dialogId).css({"top": dialogTop + "px", "left": dialogLeft + "px"});

		jQuery('#' + dialogId + ' .close').click(function () {
			if (isShowMask) {
				document.ontouchmove = function (e) {
					return true;
				};
			}
			var closeCBResult = true;
			if (typeof opts.close == 'function') {
				closeCBResult = opts.close();
			}
			if (closeCBResult) {
				jQuery('#' + maskId).hide();
				jQuery('#' + maskId).remove();
				jQuery('#' + dialogId).hide();
				jQuery('#' + dialogId).remove();
			}
		});
		if (typeof opts.callback == 'function') {
			if (isShowMask) {
				document.ontouchmove = function (e) {
					e.preventDefault();
				};
			}
			opts.callback();
		}

		if (typeof opts.ok == 'function') {
			jQuery('#' + dialogId + ' .editBtn1').click(function () {
				opts.ok();
			});
		}

		if (typeof opts.cancel == 'function') {
			jQuery('#' + dialogId + ' .editBtn2').click(function () {
				opts.cancel();
			});
		}

		if (jQuery('#' + dialogId + ' .editBtn1')[0]) {
			jQuery('#' + dialogId + ' .editBtn1').click(function () {
				jQuery('#' + dialogId + ' .close').click();
			});
		}
		if (jQuery('#' + dialogId + ' .editBtn2')[0]) {
			jQuery('#' + dialogId + ' .editBtn2').click(function () {
				jQuery('#' + dialogId + ' .close').click();
			});
		}

		if (!opts.title && !btnOk && !btnCancel && autoClose) {
			autoClose = autoClose > 1 ? autoClose : 1000;
			setTimeout(function () {
				jQuery('#' + dialogId).fadeOut('slow', function () {
					jQuery('#' + maskId).hide();
					jQuery('#' + maskId).remove();
					jQuery('#' + dialogId).hide();
					jQuery('#' + dialogId).remove();
					if (typeof opts.close == 'function') {
						opts.close();
					}
				});
			}, autoClose);
		}
	},
	hideDialog: function (opts) {
		var opts = opts || {};
		var dId = opts.id || 'tips';
		var dialogId = 'fwin_dialog_' + dId;
		var maskId = 'fwin_mask_' + dId;

		jQuery('#' + maskId).hide();
		jQuery('#' + maskId).remove();
		jQuery('#' + dialogId).hide();
		jQuery('#' + dialogId).remove();
	},
	showLoading: function (display, waiting, autoClose) {
		var display = display || 'block';
		var autoClose = autoClose || false;
		waiting = waiting || '正在加载...';
		if (display == 'block') {
			TOOLS.dialog({id: 'loading', content: waiting, noMask: true, autoClose: autoClose});
		} else {
			TOOLS.dialog({id: 'loading'});
		}
	},
	hideLoading: function () {
		TOOLS.hideDialog({id: 'loading'});
	},
	showTips: function (content, autoClose, display) {
		var display = display || 'block';
		var autoClose = autoClose || false;
		if (display == 'block') {
			TOOLS.dialog({content: content, noMask: true, autoClose: autoClose});
		} else {
			TOOLS.dialog();
		}
	},
	hideTips: function () {
		TOOLS.hideDialog();
	},
	timerId: false,
	initTouch: function (opts) {
		var obj = opts.obj || document;
		var startX, startY, endX, endY, moveTouch;
		function touchStart(event) {
			var touch = event.touches[0];
			startY = touch.pageY;
			startX = touch.pageX;
			endX = touch.pageX;
			endY = touch.pageY;
			if (typeof opts.start == 'function') {
				opts.start(event);
			}
		}

		function touchMove(event) {
			window.clearInterval(TOOLS.timerId);
			touch = event.touches[0];
			endX = touch.pageX;
			endY = touch.pageY;
			if (document.body.scrollTop <= 0 && (startY - endY) <= 0 && jQuery.os.ios) {
				event.preventDefault();
			}
			if (typeof opts.move == 'function') {
				var offset = {x: startX - endX, y: startY - endY};
				opts.move(event, offset);
			}
			if (!jQuery.os.ios) {
				TOOLS.timerId = window.setTimeout(function () {
					touchEnd();
				}, 50);
			}
		}
		function touchEnd(event) {
			if (typeof opts.end == 'function') {
				var offset = {x: startX - endX, y: startY - endY};
				opts.end(event, offset);
			}
		}

		obj.addEventListener('touchstart', touchStart, false);
		obj.addEventListener('touchmove', touchMove, false);
		if (jQuery.os.ios) {
			obj.addEventListener('touchend', touchEnd, false);
		}
	},
	openNewPage: function (openUrl) {
		location.href = openUrl;
	},
	openLoginPage: function (openUrl, limit) {
		if(limit){
			setTimeout(function () {
				TOOLS.openNewPage('?a=login&referer=' + encodeURIComponent( openUrl ));
			}, limit);
		}else{
			TOOLS.openNewPage('?a=login&referer=' + encodeURIComponent( openUrl ));
		}
	},
	pageBack: function (url) {
		var url = url || '';
		var selfReferrer = document.referrer.indexOf(SITE_INFO.siteUrl) === 0;
		if (TOOLS.getQuery("_close") && window.parent) {
			window.parent.postMessage("close", "*");
		} else if ((selfReferrer || !url) && !TOOLS.getQuery("_backurl")) {
			history.go(-1);
		} else {
			TOOLS.openNewPage(url);
		}
	},
	transformBase: function (count, power, fixed) {
		if (count < 10000 || !power || power <= 0) {
			return count;
		} else if (power < 1) {
			power = 1;
		} else if (power > 5) {
			power = 4;
		}
		var unit = ["", "十", "百", "千", "万"];
		var v = count / Math.pow(10, power);
		if (fixed == undefined) {
			fixed = 0;
		}
		return v.toFixed(fixed) + unit[power];
	},
	getCacheData: function (key) {
		var newkey = key + "_" + SITE_ID;
		var data = localStorage.getItem(newkey);
		if (data) {
			var t = localStorage.getItem(newkey + "time");
			if (parseInt(t) >= (new Date()).getTime()) {

				switch (localStorage.getItem(newkey + "type")) {
					case "number":
						data = parseInt(data);
						break;
					case "boolean":
						data = Boolean(data);
						break;
				}
				return data;
			}
			TOOLS.removeCacheData(key);
		}
		return null;
	},
	setCacheData: function (key, data, time) {
		TOOLS.removeCacheData(key);
		var newkey = key + "_" + SITE_ID;
		var i = 2;
		while (i--) {
			try {
				localStorage.setItem(newkey, data.toString());
				localStorage.setItem(newkey + "type", typeof (data));
				localStorage.setItem(newkey + "time", ((new Date()).getTime() + time).toString());
				break;
			} catch (err) {
				if (err.name == 'QuotaExceededError') {
					localStorage.clear();
				}
			}
		}
	},
	getCacheJSon: function (key) {
		var jsonString = TOOLS.getCacheData(key);
		if (jsonString) {
			return $.parseJSON(jsonString);
		}
		return null;
	},
	setCacheJSon: function (key, json, time) {
		TOOLS.setCacheData(key, JSON.stringify(json), time);
	},
	removeCacheData: function (key) {
		var newkey = key + "_" + SITE_ID;
		localStorage.removeItem(newkey);
		localStorage.removeItem(newkey + "type");
		localStorage.removeItem(newkey + "time");
	},
	getParamFilter: function () {
		var filterParams = "";
		var url = window.location.href;
		var filter = ['a', 'c', 'f', 'source', 'fid', 'tid'];

		if (url.indexOf('?') == -1) {
			return "";
		}

		var getParams = url.split('?')[1].split('&');
		for (var i in getParams) {

			var j;
			var paramKey = getParams[i].split('=')[0];
			for (j = 0; j < filter.length && filter[j] != paramKey; j++)
				;
			if (j < filter.length) {
				continue;
			}
			filterParams += (filterParams == "" ? "" : "&") + getParams[i];
		}
		return filterParams;
	},
	getJsAuth: function () {
		var url = '';
		if (INFO_LOADED && !TOOLS.checkInfo.data.testcookie) {
			jsauth = TOOLS.getcookie('jsauth');
			if (jsauth) {
				url += "&_auth=" + encodeURIComponent(jsauth);
			}
		}
		return url;
	},
	dget: function (url, data, func, errorfunc, contentType, report, appendauth) {
		if (!INFO_LOADED) {
			setTimeout(function () {
				TOOLS.dget(url, data, func, errorfunc, contentType, report, appendauth);
			}, 100);
			return;
		}
		if (typeof appendauth === 'undefined') {
			var appendauth = true;
		}
		var isLogin = url.indexOf('&ac=wxlogin&') > -1;
		if (!isLogin) {
			if (report) {
				url += "&from=wx";
			} else {
				url += "&mapifrom=wx&charset=utf-8";
				url += "&" + TOOLS.getParamFilter();
			}
			if (appendauth) {
				url += TOOLS.getJsAuth();
			}
		}
		TOOLS.dajax('GET', url, data, func, errorfunc, contentType || 'text/plain');
	},
	dpost: function (url, data, func, errorfunc, contentType, appendauth) {
		if (!INFO_LOADED) {
			setTimeout(function () {
				TOOLS.dpost(url, data, func, errorfunc, contentType, appendauth);
			}, 100);
			return;
		}
		if (typeof appendauth === 'undefined') {
			var appendauth = true;
		}
		url += "&mapifrom=wx&charset=utf-8";
		url += "&" + TOOLS.getParamFilter();
		if (appendauth) {
			url += TOOLS.getJsAuth();
		}
		TOOLS.dajax('POST', url, data, func, errorfunc, contentType || 'application/x-www-form-urlencoded');
	},
	dajax: function (method, url, data, func, errorfunc, contentType) {
		jQuery.ajax({
			type: method,
			url: url,
			contentType: contentType,
			data: data,
			xhrFields: {
				withCredentials: true
			},
			headers: {
			},
			success: function (data) {

				var sys_error = data.error || '';
				if (sys_error !== '') {
					if (typeof errorfunc === 'function') {
						var siteName = typeof SITE_INFO.siteName !== 'undefined' ? SITE_INFO.siteName : '微社区';
						if (sys_error === 'mobile_is_closed') {
							TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />该社区未启用手机版');
						} else {
							TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />接口错误(ERR01)');
						}
					} else {
						return;
					}
				}

				var message = data.Message;
				if (message && message.messageval.indexOf('_succeed') < 0 && message.messageval.indexOf('_succed') < 0 && message.messageval.indexOf('_completion') < 0 && typeof errorfunc === 'function') {
					errorfunc(message, data);
					return;
				}

				if (typeof func === 'function') {
					TOOLS.parseStyle(data);
					TOOLS.parseFunc(data);
					func(data);
				}
			},
			error: function (data) {
				re = /^[^\{]*?(\{.*?\})[^\}]*?$/;
				var matches = re.exec(data.responseText);
				if (matches != null) {
					try {
						var data = jQuery.parseJSON(matches[1]);
					} catch (e) {
						var data = false;
					}
					if (data) {
						this.success(data);
						return;
					}
				}
				if (typeof errorfunc === 'function') {
					var siteName = typeof SITE_INFO.siteName !== 'undefined' ? SITE_INFO.siteName : '微社区';
					TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />接口错误(ERR02)');
					TOOLS.hideLoading();
				}
			}
		});
	},
	shareToQQ: function (siteName, summary, title, imageUrl, targetUrl, page_url, nobar, pagetitle, appCallback) {
		var url = "http://mq.wsq.qq.com/shareDirect?";
		url += "site=" + encodeURIComponent(siteName);
		url += "&title=" + encodeURIComponent(title);
		url += "&summary=" + encodeURIComponent(summary);
		url += "&targetUrl=" + encodeURIComponent(targetUrl);
		url += "&pageUrl=" + encodeURIComponent(page_url);
		url += "&imageUrl=" + encodeURIComponent(imageUrl);
		window.location.href = url;
	},
	showError: function (selector, description, clickEvent) {
		var errorDiv = document.createElement("div");
		errorDiv.className = "errorInfo";
		var errorI = document.createElement("i");
		errorI.className = "eInco db spr";
		var errorP = document.createElement("p");
		errorP.innerHTML = description;
		errorDiv.appendChild(errorI);
		errorDiv.appendChild(errorP);
		$(selector).html(errorDiv);


		if (typeof (clickEvent) == 'function') {
			$('.errorInfo').on('click', clickEvent);
		}
	},
	checkInfo: {
		data: null,
		getChecking: false,
		getCheckHandle: [],
		get: function () {
			TOOLS.checkInfo.getChecking = true;
			if (JC.VERSION == localStorage.getItem(JC.KEYPREFIX + "checkinfoversion")) {
				data = localStorage.getItem(JC.KEYPREFIX + 'checkinfo') || null;
				if (typeof (data) == "string") {
					data = jQuery.parseJSON(data);
				}
				TOOLS.checkInfo.data = data;
			}
		},
		load: function () {
			data = {
				'siteid': 0,
				'time': ((new Date()).getTime() + 600000).toString()
			};
			var t = +(new Date());
			TOOLS.dajax('GET', API_URL + 'module=check&version=4&_t=' + t, null, function (re) {
				if (typeof (re) == "string") {
					try {
						re = jQuery.parseJSON(re);
					} catch (e) {
						var siteName = typeof SITE_INFO.siteName !== 'undefined' ? SITE_INFO.siteName : '微社区';
						TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />接口错误(ERR03)');
						TOOLS.hideLoading();
						return;
					}
				}
				data.ucenterurl = re.ucenterurl ? re.ucenterurl : '';
				data.discuzversion = re.discuzversion ? re.discuzversion : '';
				data.pluginversion = re.pluginversion ? re.pluginversion : '';
				data.regname = re.regname ? re.regname : '';
				data.qqconnect = re.qqconnect ? re.qqconnect : '';
				data.wsqqqconnect = re.wsqqqconnect ? re.wsqqqconnect : '';
				data.wsqhideregister = re.wsqhideregister ? re.wsqhideregister : '';
				data.defaultfid = re.defaultfid ? re.defaultfid : '';
				data.disableforumlist = re.disableforumlist ? re.disableforumlist : '';
				data.totalposts = re.totalposts ? re.totalposts : '';
				data.totalmembers = re.totalmembers ? re.totalmembers : '';
				if (jQuery.os.ios) {
					data.testcookie = false;
				} else {
					if (typeof re.testcookie != 'undefined') {
						data.testcookie = !TOOLS.checkCookie() ? true : (re.testcookie ? true : false);
					} else {
						data.testcookie = true;
					}
				}
				if (!data.defaultfid && !data.totalmembers) {
					TOOLS.dajax('GET', API_URL + 'module=wsqindex&version=4&_t=' + t, null, function (re) {
						if (typeof (re) === "string") {
							try {
								re = jQuery.parseJSON(re);
							} catch (e) {
								var siteName = typeof SITE_INFO.siteName !== 'undefined' ? SITE_INFO.siteName : '微社区';
								TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />接口错误(ERR04)');
								TOOLS.hideLoading();
								return;
							}
						}
						data.defaultfid = re.Variables.forum.fid;
						data.totalposts = re.Variables.stats.totalposts;
						data.totalmembers = re.Variables.stats.totalmembers;
						TOOLS.checkInfo.data = data;
						localStorage.setItem(JC.KEYPREFIX + 'checkinfo', JSON.stringify(data));
						localStorage.setItem(JC.KEYPREFIX + "checkinfoversion", JC.VERSION);
						TOOLS.checkInfo.handle();
					});
				} else {
					TOOLS.checkInfo.data = data;
					localStorage.setItem(JC.KEYPREFIX + 'checkinfo', JSON.stringify(data));
					localStorage.setItem(JC.KEYPREFIX + "checkinfoversion", JC.VERSION);
					TOOLS.checkInfo.handle();
				}
			},
				null,
				'text/plain');
		},
		handle: function () {
			TOOLS.checkInfo.getChecking = false;
			INFO_LOADED = true;
			for (i in TOOLS.checkInfo.getCheckHandle) {
				func = TOOLS.checkInfo.getCheckHandle[i];
				if (func && typeof func === 'function') {
					func(TOOLS.checkInfo.data);
				}
			}
			TOOLS.checkInfo.getCheckHandle = [];
		}
	},
	getCheckInfo: function (func) {
		var func = func || null;
		if (func) {
			TOOLS.checkInfo.getCheckHandle.push(func);
		}
		if (TOOLS.checkInfo.getChecking) {
			return;
		}
		TOOLS.checkInfo.get();
		var t = +(new Date());
		if (TOOLS.checkInfo.data === null || parseInt(TOOLS.checkInfo.data.time) < t) {
			if (TOOLS.checkCookie()) {
				jQuery.ajax({type: 'GET', url: API_URL + 'module=checkcookie&version=4&_t=' + t, contentType: 'application/x-www-form-urlencoded', xhrFields: {withCredentials: true},
					success: function (data) {
						TOOLS.checkInfo.load();
					},
					error: function (data) {
						re = /^.*?(\[\]).*?$/;
						var matches = re.exec(data.responseText);
						if (matches != null) {
							TOOLS.checkInfo.load();
						} else {
							var siteName = typeof SITE_INFO.siteName !== 'undefined' ? SITE_INFO.siteName : '微社区';
							TOOLS.showError('.warp', '您请求的' + siteName + '无法访问<br /><br />接口错误(ERR05)');
							TOOLS.hideLoading();
						}
					}
				});
			} else {
				TOOLS.checkInfo.load();
			}
		} else {
			TOOLS.checkInfo.handle();
		}
	},
	checkCookie: function () {
		return true;
	},
	in_array: function (search, array) {
		for (var i in array) {
			if (array[i] == search) {
				return true;
			}
		}
		return false;
	},
	hook: function (data, hookname, shownum, replace) {
		if (!data.pluginVariables) {
			return null;
		}
		if (typeof data.pluginVariables[hookname] === 'undefined') {
			return null;
		}
		var shownum = shownum || 0;
		var replace = replace || 0;
		var ret = null;
		i = 0;
		for (plugin in data.pluginVariables[hookname]) {
			if (typeof data.pluginVariables[hookname][plugin] === 'string') {
				if (ret === null) {
					var ret = '';
				}
				var v = data.pluginVariables[hookname][plugin];
				if (v !== '') {
					if (!replace) {
						ret += v;
					} else {
						ret = v;
					}
					i++;
				}
			} else {
				if (ret === null) {
					var ret = [];
				}
				for (order in data.pluginVariables[hookname][plugin]) {
					var rtype = typeof ret[order];
					var vtype = typeof data.pluginVariables[hookname][plugin][order];
					var v = data.pluginVariables[hookname][plugin][order];
					if (v !== '') {
						if (vtype !== 'undefined') {
							if (!replace) {
								if (vtype === 'string') {
									if (rtype === 'undefined') {
										ret[order] = v;
									} else {
										ret[order] += v;
									}
								} else if (vtype === 'object') {
									ret.push(v);
								}
							} else {
								ret[order] = v;
							}
						}
						i++;
					}
				}
			}
			if (shownum > 0 && i >= shownum) {
				break;
			}
		}
		return ret;
	},
	stripCode: function (s, page, styleClass, disableWord) {
		if (!s) {
			return s;
		}
		var page = page || 0;
		var styleClass = styleClass || '';
		var regs = [/<script.*?>([\s\S]*?)<\/script>/ig, /<link.*?>/ig,
			/<title.*?>([\s\S]*?)<\/title>/ig, /<base.*?>/ig, /<meta.*?>/ig,
			/<head.*?>([\s\S]*?)<\/head>/ig, /<body.*?>([\s\S]*?)<\/body>/ig, /<object.*?>([\s\S]*?)<\/object>/ig,
			/<applet.*?>([\s\S]*?)<\/applet>/ig, /<embed.*?>([\s\S]*?)<\/embed>/ig, /<basefont.*?>/ig,
			/<canvas.*?>([\s\S]*?)<\/canvas>/ig, /<audio.*?>([\s\S]*?)<\/audio>/ig, /<frame.*?>([\s\S]*?)<\/frame>/ig,
			/<frameset.*?>([\s\S]*?)<\/frameset>/ig];
		if (!page) {
			regs.push(/<iframe.*?>.*?<\/iframe>/ig);
		}
		if (!styleClass) {
			regs.push(/<style.*?>([\s\S]*?)<\/style>/ig);
		} else {
			s = s.replace(/<style(.*?)>([\s\S]*?)<\/style>/ig, function ($0, $1, $2) {
				$2 = $2.replace(/(.+?)\{([\s\S]*?)\}\s*/ig, function ($0, $1, $2) {
					if ($1.indexOf(styleClass) !== 0 || disableWord && $1.search(disableWord) !== -1) {
						return '';
					} else {
						return $0;
					}
				});
				return '<style' + $1 + '>' + $2 + '</style>';
			});
		}
		for (var i = regs.length - 1; i >= 0; i--) {
			s = s.replace(regs[i], '');
		}
		s = s.replace(/%>/ig, '{%}')
			.replace(/<[a-zA-Z]+[^>]*?on[a-zA-Z]+\s*?=\s*?([\'"])[^>]+?\1[^>]*?>/ig, function ($0) {
				return $0.replace(/on[a-zA-Z]+\s*?=\s*?([\'"])[^>]+?\1/ig, '');
			})
			.replace(/\s*?([\'"])\s*?javascript\s*[^>]+?\1/ig, function ($0, $1) {
				return $1 + 'javascript:;' + $1;
			})
			.replace(/<[a-zA-Z]+[^>]*?click=\s*?([\'"])([\(\);\w]+)\1[^>]*?>/ig, function ($0, $1, $2) {
				return $0.replace(/click=\s*?([\'"])[^>]+?\1/ig, ' onclick="TOOLS.customFuncs.run(event, \'' + $2 + '\')" ');
			})
			.replace(/<wsqscript>([\s\S]+?)<\/wsqscript>/ig, function ($0, $1) {
				TOOLS.customFuncs.run(event, $1, false);
				return '';
			})
			.replace(/\{%\}/ig, '%>');
		return s;
	},
	getoption: function (data, option) {
		if (!data.Variables.options) {
			return null;
		} else if (data.Variables.options[option]) {
			return data.Variables.options[option];
		} else {
			return null;
		}
	},
	customFuncs: {
		data: {},
		handle: {
			'WSQ.ajaxget': function (param) {
				var url = API_URL + "module=plugin&version=4&" + param[0] + TOOLS.getJsAuth();
				TOOLS.dajax('GET', url, null, function (re) {
					if (param[1] && $('#' + param[1])) {
						$('#' + param[1]).html(TOOLS.stripCode(re.Variables.html));
					}
				}, function (error) {
					TOOLS.showTips(error.messagestr, 1000);
				});
			},
			'WSQ.ajaxpost': function (param) {
				var data = $(param[1]) ? $('#' + param[1]).serialize() : null;
				var url = API_URL + "module=plugin&version=4&" + param[0] + TOOLS.getJsAuth();
				TOOLS.dajax('POST', url, data, function (re) {
					if (param[2] && $('#' + param[2])) {
						$('#' + param[2]).html(TOOLS.stripCode(re.Variables.html));
					}
				}, function (error) {
					TOOLS.showTips(error.messagestr, 1000);
				});
			},
			'WSQ.show': function (param) {
				$('#' + param[0]).show();
			},
			'WSQ.hide': function (param) {
				$('#' + param[0]).hide();
			},
			'WSQ.tip': function (param) {
				TOOLS.showTips(param[0], param[1] || 1000);
			},
			'WSQ.dialog': function (param) {
				var opts = {
					'id': param['id'],
					'content': TOOLS.stripCode(param['content']),
					'okValue': '确定',
					'ok': param['ok'] ? function () {
						TOOLS.customFuncs.run(event, param['ok']);
					} : null,
					'cancelValue': '取消',
					'cancel': param['cancel'] ? function () {
						TOOLS.customFuncs.run(event, param['cancel']);
					} : null,
					'isMask': param['mask'] || true
				};
				TOOLS.dialog(opts);
			},
			'WSQ.location': function (param) {
				TOOLS.openNewPage(param[0]);
			}
		},
		run: function (event, funcnames, stopEvent) {
			var stopEvent = stopEvent || true;
			var funcnames = funcnames.split(';');
			for (i in funcnames) {
				var sp = funcnames[i].split('(');
				var name = TOOLS.trim(sp[0]);
				if (!TOOLS.customFuncs.data[name]) {
					continue;
				}
				var func = TOOLS.customFuncs.data[name][0];
				var param = TOOLS.customFuncs.data[name][1];
				var handle = TOOLS.customFuncs.handle[func];
				if (!func || !param || !handle) {
					continue;
				}
				handle(param);
			}
			if (stopEvent) {
				event.stopPropagation();
			}
		}
	},
	parseFunc: function (data) {
		try {
			var func = data.Variables.function;
			if (typeof func === 'undefined') {
				return;
			}
		} catch (e) {
			return;
		}
		for (funcname in func) {
			TOOLS.customFuncs.data[funcname] = func[funcname];
		}
	},
	parseStyle: function (data) {
		try {
			var style = data.Variables.style;
			if (typeof style === 'undefined') {
				return;
			}
		} catch (e) {
			return;
		}
		var style = style.replace(/(.+?)\{([\s\S]*?)\}\s*/ig, function ($0, $1, $2) {
			var allowclass = [
				'background', 'color', 'box-shadow', 'border-left', 'border-top', 'border-right',
				'border-bottom', 'border', 'border-radius', 'padding', 'margin', 'line-height',
				'text-shadow', 'font-weight'
			];
			var values = $2.split(';');
			var result = '';
			for (i in values) {
				var pos = values[i].indexOf(':');
				if (pos === -1) {
					continue;
				}
				var attr = values[i].substr(0, pos).toLowerCase();
				var value = values[i].substr(pos + 1);
				attr = attr.replace(/(^\s*)|(\s*$)/g, '');
				if (TOOLS.in_array(attr, allowclass)) {
					result += attr + ':' + value + ';';
				}
			}
			return $1 + '{' + result + '}';
		});
		obj = document.createElement('style');
		obj.type = 'text/css';
		obj.innerHTML = style;
		$('head').append(obj);
	},
	lazyLoad: function (rule) {
		var rule = rule || 'img.lazy';
		$(rule).lazyload({skip_invisible: false, threshold: 200, failurelimit: 100});
	},
	uploadCompatible: function (uploadbtn, msgObj) {
		var msg = '';
		if (jQuery.os.ios) {
			if (jQuery.os.version.toString() < '6.0') {
				msg = '手机系统不支持图片上传，请升级到iOS6以上';
			}
		}
		if (msg) {
			jq(msgObj).html(msg);
		}
	},
	attachUrl: function (url) {
		if ((url.indexOf('http:/') != -1) || (url.indexOf('https:/') != -1)) {
			return url;
		} else {
			return DOMAIN + url;
		}
	},
	parsePost: function () {
		$('a[ed2k]').each(function () {
			var obj = $(this);
			obj.attr('href', 'ed2k://' + unescape(obj.attr('ed2k')) + '/');
			obj.html(unescape(decodeURIComponent(obj.html())));
			obj.removeAttr('ed2k');
		});
	},
	showPublicEvent: function () {
		if (!SITE_INFO.settings || !SITE_INFO.settings.isbind) {
			return;
		}
		var wsqUrl = 'http://api.wsq.qq.com/publicEvent?sId=' + SITE_ID + '&resType=json&isAjax=1&_=' + Math.random();
		jQuery.ajax({type: 'get', url: wsqUrl, dataType: 'json', contentType: 'application/x-www-form-urlencoded', xhrFields: {withCredentials: false},
			success: function (re) {
				var status = parseInt(re.errCode);
				if (status === 0) {
					if (jQuery.isEmptyObject(re.data.event) && jQuery.isEmptyObject(re.data.ad)) {
						return false;
					}
					var pEvent = re.data.event, ad = re.data.ad;
					var showEvent = true;
					if (!jQuery.isEmptyObject(re.data.ad)) {
						if (re.data.hadJoin) {
							if (Math.random() * 100 > 50) {
								showEvent = false;
							}
						} else {
							showEvent = false;
						}
					}
					if (showEvent) {
						if (!re.data.hadJoin) {
							return false;
						}

						TC.load("common.htm");
						html = template.render('publicEventTpl', {});
						$('#headerbanner').append(html);
						$('#headerbanner').show();

						$('#pEventImg').attr('src', pEvent.peBanner);
						if (pEvent.showJoinNum) {
							$('#pEventNum').html(pEvent.peNum || 0);
						} else {
							$('#pEvent p').hide();
						}

						if (pEvent.peMethod == 2) {
							var url = pEvent.peCustomUrl;
							if (url.indexOf('?') == -1) {
								url += '?';
							} else {
								url += '&';
							}
							url += 'showSId=' + SITE_ID;
						} else {
							var url = 'http://m.wsq.qq.com/' + pEvent.peClickUrl + '?peId=' + pEvent.peId + '&sId=' + SITE_ID;
						}
					} else {
						$('#pEventImg').attr('src', ad.banner);
						$('#pEvent p').hide();
						var url = ad.url;
					}

					$('#pEvent').on('click', function () {
						TOOLS.openNewPage(url);
						return false;
					}).slideDown();
				}
			}
		});
	}
};

template.compile('error_tmpl', '<div class="errorInfo"> <i class="eInco db spr"></i> <P><%=title%></P> </div>');
jQuery.extend({
	os: {
		ios: false,
		android: false,
		version: false
	}
});

template.helper('stripCode', function (s, page, styleClass, disableWord) {
	return TOOLS.stripCode(s, page, styleClass, disableWord);
});