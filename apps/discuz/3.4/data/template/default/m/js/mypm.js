var index = {
	page: !TOOLS.getQuery('touid') ? 1 : null,
	dropDown: false,
	dropUp: false,
	ucenterurl: '',
	formhash: '',
	titleName: '我的私信',
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
		if (TOOLS.getQuery('touid')) {
			if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.15 && !index.dropDown) {
				index.dropDownFunc();
				return false;
			}
			if (document.body.scrollTop <= 0 && offset.y < 0 && !index.dropUp) {
				index.dropUpFunc();
				return false;
			}
		} else {
			if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.15 && !index.dropUp) {
				index.dropUpFunc();
				return false;
			}
			if (document.body.scrollTop <= 0 && offset.y < 0 && !index.dropDown) {
				index.dropDownFunc();
				return false;
			}
		}
	},
	dropDownFunc: function () {
		index.dropDown = true;
		$('#refreshWait').show();
		index.page = null;
		$('#showAll').hide();
		var url = API_URL + 'version=4&module=mypm' + (TOOLS.getQuery('touid') ? '&subop=view&touid=' + TOOLS.getQuery('touid') : '');
		index.loadPage(url, index.dropDownCallBack);
	},
	dropUpFunc: function () {
		index.dropUp = true;
		$('#loadNext').show();
		if (!TOOLS.getQuery('touid')) {
			index.page++;
		} else {
			index.page--;
		}
		if (index.page > 0) {
			var url = API_URL + 'version=4&module=mypm' + (TOOLS.getQuery('touid') ? '&subop=view&touid=' + TOOLS.getQuery('touid') : '');
			index.loadPage(url, index.dropUpCallBack);
		} else {
			index.dropUpCallBack();
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
		if (!TOOLS.getQuery('touid')) {
			var maxPage = Math.ceil(parseInt(data.Variables.count) / parseInt(data.Variables.perpage));
			if (maxPage >= data.Variables.page) {
				data.ucenterurl = index.ucenterurl;
				var html = template.render('pmListBox', data);
				$('#container').append(html);
			} else {
				$('#showAll').show();
			}
		} else {
			if (!data.Variables.list) {
				$('#showAll').show();
				return;
			}
			if (index.page == null) {
				data.Variables.first = 1;
			}
			index.page = data.Variables.page;
			data.ucenterurl = index.ucenterurl;
			var html = template.render('pmContentBox', data);
			$('#container').html(html + $('#container').html());
		}
		index.bindEvent();
	},
	bindEvent: function () {
		$('.upBtn').on('click', function () {
			scroll(0, 0);
			$('#backToTopBtn').hide();
		});

		$('.messageList li').on('click', function () {
			var touid = $(this).attr('touid');
			TOOLS.openNewPage('?a=mypm&touid=' + touid);
		});

		$('.inputWrap a').on('click', function () {
			var postUrl = API_URL + 'siteid=' + SITE_ID + '&version=4&module=sendpm&pmsubmit=yes';
			$('.inputWrap').addClass('disabled');
			TOOLS.dpost(postUrl, 'formhash=' + index.formhash + '&message=' + $('.messageInput').val(),
				function (re) {
					index.sendCallback(re);
				},
				function (re) {
					index.sendCallback(re);
				});
		});
	},
	sendCallback: function (data) {
		$('.inputWrap').removeClass('disabled');
		if (data.messageval == 'do_success') {
			$('.messageInput').val('');
			setTimeout(function () {
				index.page = null;
				var url = API_URL + 'version=4&module=mypm' + (TOOLS.getQuery('touid') ? '&subop=view&touid=' + TOOLS.getQuery('touid') : '');
				index.loadPage(url, index.renderMyPage);
			}, 500);
		} else {
			TOOLS.showTips('无法发送, 请稍后重新发送', true);
		}
	},
	loadPage: function (url, callBack) {
		if (index.page != null) {
			url += '&page=' + (index.page);
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
			index.formhash = data.Variables.formhash;
			TOOLS.hideLoading();
			callBack(data);

		}, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
			if(error.messageval=='login_before_enter_home//1'){
				TOOLS.openLoginPage(location.href, 1000);
			}
		});
	},
	initPage: function () {
		TOOLS.initTouch({obj: $('.warp')[0], end: index.touchEnd, move: index.touchMove});
		TOOLS.showLoading();
		TOOLS.getCheckInfo(function (re) {
			index.ucenterurl = re.ucenterurl;
		});
		var url = API_URL + 'version=4&module=mypm' + (TOOLS.getQuery('touid') ? '&subop=view&touid=' + TOOLS.getQuery('touid') : '');
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