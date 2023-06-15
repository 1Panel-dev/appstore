var index = {
	page: 1,
	dropDown: false,
	dropUp: false,
	ucenterurl: '',
	titleNames: {'thread': '我的话题', 'reply': '我的回复'},
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
			var url = API_URL + 'version=4&module=mythread&type=' + TOOLS.getQuery('ac');
			index.loadPage(url, index.dropUpCallBack);
			return false;
		}
		if (document.body.scrollTop <= 0 && offset.y < 0) {
			index.dropDown = true;
			$('#refreshWait').show();
			index.page = 1;
			$('#showAll').hide();

			var url = API_URL + 'version=4&module=mythread&type=' + TOOLS.getQuery('ac');
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
	renderPage: function (data) {
		$('.bottomBar').show();
		if (!data.Variables.data) {
			$('#showAll').show();
			return;
		}
		data.ucenterurl = index.ucenterurl;
		var html = template.render('topicBox', data);
		var pageId = 'topicList' + index.page;
		$('#container').append('<div id="' + pageId + '">' + html + '</div>');
		pageId = '#' + pageId;
		$(pageId + ' .topicBox').on('click', function () {
			var tid = $(this).attr('tid');
			TOOLS.openNewPage('?a=viewthread&tid=' + tid);
		});

		$(pageId + ' .praiseBtn').on('click', function (event) {

			var btn = $(this);
			var praisestatus = btn.find('i').attr('class');
			if (praisestatus == 'noPraise') {
				var tid = btn.attr('tid');
				var hash = data.Variables.formhash;
				var praiseUrl = API_URL + "version=4&module=recommend&tid=" + tid + "&hash=" + hash;
				TOOLS.dget(praiseUrl, null, function (json) {
				},
				function (error) {
				});
				btn.find('i').removeClass('noPraise').addClass('praise');
				var digSpan = btn.find('span');
				var digNum = parseInt(digSpan.html());
				if (isNaN(digNum)) {
					digNum = 0;
				}
				digSpan.html(digNum + 1);

			}
			event.stopPropagation();
		});

		$(pageId + ' .incoRBtn').on('click', function (event) {
			if (data.Variables.member_uid != "0") {
				replyComment(data.Variables.formhash, $(this).attr("tid"));
			} else {
				TOOLS.openNewPage('?a=reply&tid=' + $(this).attr("tid") + '&pos=index');
			}
			event.stopPropagation();
		});
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
				jsonCache.Variables.data = data.Variables.data;
				data = jsonCache;
			}
			TOOLS.hideLoading();
			callBack(data);
		}, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		});
	},
	renderMyPage: function (data) {
		data.title = index.titleNames[TOOLS.getQuery('ac')];
		var headerHtml = '';
		headerHtml += template.render('headerTpl', data);

		$('#header .header').remove();
		$('#container div').remove();
		$('#header').append(headerHtml);
		index.renderPage(data);
	},
	initPage: function () {
		$('.bottomBar').hide();
		TOOLS.initTouch({obj: $('.warp')[0], end: index.touchEnd, move: index.touchMove});
		index.bindEvent();
		TOOLS.showLoading();
		TOOLS.getCheckInfo(function (re) {
			index.ucenterurl = re.ucenterurl;
		});
		var url = API_URL + 'version=4&module=mythread&type=' + TOOLS.getQuery('ac');
		index.loadPage(url, index.renderMyPage);
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