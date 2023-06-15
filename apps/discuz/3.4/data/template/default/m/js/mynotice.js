var index = {
	page: 1,
	dropDown: false,
	dropUp: false,
	ucenterurl: '',
	titleName: '我的提醒',
	discuzversion: '',
	backToTop: function (offset) {
		if (document.body.scrollTop <= 0 || offset.y > 2) {
			$('#backToTopBtn').hide();
		} else if (offset.y < -10) {
			$('#backToTopBtn').show();
		}
	},
	touchMove: function (e, offset) {
		var level = /Android 4.[013]/.test(window.navigator.userAgent) ? -10 : -100;
		document.ontouchmove = function (e) {
			return true;
		};
		throttle.process(index.backToTop, 200, offset);
	},
	touchEnd: function (e, offset) {
		var level = /Android 4.[013]/.test(window.navigator.userAgent) ? -10 : -100;
		document.ontouchmove = function (e) {
			return true;
		};
		var loadingPos = $('#loadNextPos');
		var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
		if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.15 && !index.dropUp) {
			index.dropUp = true;
			$('#loadNext').show();
			var url = API_URL + 'version=4&module=mynotelist';
			index.loadPage(url, index.dropUpCallBack);
			return false;
		}
		if (document.body.scrollTop <= 0 && offset.y < 0) {
			index.dropDown = true;
			$('#refreshWait').show();
			index.page = 1;
			$('#showAll').hide();


			var url = API_URL + 'version=4&module=mynotelist';
			index.loadPage(url, index.dropDownCallBack);
			return false;
		}
	},
	dropDownCallBack: function (data) {
		index.dropDown = false;
		$('#refreshWait').hide();
		$('#container').html('');
		index.renderPage(data);
	},
	dropUpCallBack: function (data) {
		index.dropUp = false;
		$('#loadNext').hide();
		index.renderPage(data);
	},
	renderMyPage: function (data) {
		data.title = index.titleName;
		var headerHtml = '';
		headerHtml += template.render('headerTpl', data);

		$('#header .header').remove();
		$('#container div').remove();
		$('#header').append(headerHtml);
		index.renderPage(data);
	},
	renderPage: function (data) {
		$('.bottomBar').show();
		if (!data.Variables.list) {
			$('#showAll').show();
			return;
		}
		data.ucenterurl = index.ucenterurl;
		for (i = 0; i < data.Variables.list.length; i++) {
			data.Variables.list[i].note = data.Variables.list[i].note.replace(/href=\"(.+?)\"/g, function (word, href) {
				if (/tid=\d+/.test(href)) {
					var tid = 0, pid = 0;
					re = /p?tid=(\d+)/g;
					var matches = re.exec(href);
					if (matches != null && matches[1]) {
						tid = matches[1];
					}
					re = /pid=(\d+)/g;
					var matches = re.exec(href);
					if (matches != null && matches[1]) {
						pid = matches[1];
					}
					if (tid && pid) {
						return 'href="?a=viewcomment&tid=' + tid + '&pid=' + pid + '"';
					} else if (tid) {
						return 'href="?a=viewthread&tid=' + tid + '"';
					} else {
						return 'href="' + DOMAIN + href + '"';
					}
				} else {
					return 'href_="' + href + '"';
				}
			});
		}
		var html = template.render('noticeBox', data);
		var pageId = 'noticeList' + index.page;
		$('#container').append('<div id="' + pageId + '">' + html + '</div>');
		TOOLS.setcookie('refreshindex', '1', 86400);
	},
	bindEvent: function () {
		$('.upBtn').on('click', function () {
			scroll(0, 0);
			$('#backToTopBtn').hide();
		});

	},
	loadPage: function (url, callBack) {
		if (index.dropUp) {
			url += '&page=' + (++index.page);
		} else {
			index.page = 1;
		}

		var isRefresh = callBack == index.dropDownCallBack;

		var jsonCache = null;
		if (!isRefresh && jsonCache && IS_PAGE_BACK)
			return;

		TOOLS.dget(url, null, function (data) {
			if (isRefresh && jsonCache) {
				jsonCache.Variables.list = data.Variables.list;
				data = jsonCache;
			}
			TOOLS.hideLoading();
			callBack(data);
		}, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		});
	},
	renderNoticePage: function (data) {
		$('#container div').remove();
		index.renderMyPage(data);
	},
	initPage: function () {
		$('.bottomBar').hide();
		TOOLS.initTouch({obj: $('.warp')[0], end: index.touchEnd, move: index.touchMove});
		index.bindEvent();
		TOOLS.showLoading();
		TOOLS.getCheckInfo(function (re) {
			index.ucenterurl = re.ucenterurl;
			index.discuzversion = re.discuzversion;
		});
		var url = API_URL + 'version=4&module=mynotelist';
		index.loadPage(url, index.renderNoticePage);
		$(".backBtn").click(function () {
			TOOLS.pageBack('?a=index');
		});
	}
};

var throttle = {
	timeoutId: null,
	process: function (method, delayMils, obj) {
		clearTimeout(this.timeoutId);
		this.timeoutId = setTimeout(function () {
			method(obj);
		}, delayMils);
	}
};

$(function () {
	index.initPage();
});