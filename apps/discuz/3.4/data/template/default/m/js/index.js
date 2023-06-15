var fId = 0;
var index = {
	fid: 0,
	isDefaultFid: 0,
	page: 1,
	newMessageCount: 0,
	dropDown: false,
	dropUp: false,
	firstLoad: true,
	realFirstLoad: 2,
	isLoadCache: false,
	lastPage: 1,
	isLastPage: false,
	hasPopReplyFromPtlogin: false,
	member_uid: 0, //member_uid
	member_username: '',
	member_avatar: '',
	pluginversion: '',
	ucenterurl: '',
	tpp: 10,
	wsqHtml: '',
	refreshPage: function () {
		var url = '?a=index' + (index.fid ? '&fid=' + index.fid : '');
		if (index.typeid) {
			url += '&filter=typeid&typeid=' + index.typeid;
			if (index.sortid) {
				url += '&sortid=' + index.sortid;
			}
		} else if (index.sortid) {
			url += '&filter=sortid&sortid=' + index.sortid;
			if (index.typeid) {
				url += '&typeid=' + index.typeid;
			}
		} else if (index.digest) {
			url += '&filter=digest&digest=1';
		} else if (index.heats) {
			url += '&filter=heat&orderby=heats';
		}
		TOOLS.openNewPage(url);
	},
	backToTop: function (offset) {
		if (document.body.scrollTop <= 0 || offset.y > 2) {
			$('#backToTopBtn').hide();
		} else if (offset.y < -10) {
			$('#backToTopBtn').show();
		}
	},
	touchMove: function (e, offset) {
		var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
		document.ontouchmove = function (e) {
			return true;
		};
		throttle.process(index.backToTop, 200, offset);
	},
	touchEnd: function (e, offset) {
		var level = /Android 4.0/.test(window.navigator.userAgent) ? -10 : -100;
		document.ontouchmove = function (e) {
			return true;
		};
		var loadingPos = $('#loadNextPos');
		var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;
		if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.15 && !index.isLastPage && !index.dropUp) {
			index.dropUp = true;
			$('#loadNext').show();
			var url = API_URL + 'version=4&module=forumdisplay&fid=' + index.fid;
			index.loadPage(url, index.dropUpCallBack);
			return false;
		}
		if (document.body.scrollTop <= 0 && offset.y < 0) {
			index.dropDown = true;
			$('#refreshWait').show();
			index.isLastPage = false;
			index.page = 1;
			$('#showAll').hide();
			var url = API_URL + 'version=4&module=forumdisplay&fid=' + index.fid;
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
		var topLists = $('#container')[0].children;
		if (topLists.length > 5) {
			var lastList = topLists[topLists.length - 1].children;
			var lastTopic = lastList[lastList.length - 1];
			var currentInnerTop = lastTopic.offsetTop - document.body.scrollTop;
			topLists[0].remove();
			window.scrollTo(0, lastTopic.offsetTop - currentInnerTop);
		}
	},
	renderPage: function (data) {
		index.member_uid = data.Variables.member_uid;
		index.member_username = data.Variables.member_username;
		index.member_avatar = data.Variables.member_avatar;
		data.Variables.ucenterurl = index.ucenterurl;
		index.realFirstLoad = (index.realFirstLoad - 1 <= 0) ? 0 : 1;

		if (SITE_INFO.closed) {
			TOOLS.dialog({content: "站点已关闭", noMask: true, autoClose: 1});
			return;
		}

		if (SITE_INFO.followurl && TOOLS.isWX()) {
			$('.follow').show();
			$('.follow').on('click', function () {
				TOOLS.openNewPage(SITE_INFO.followurl);
			});
		}

		if (data.Variables.forum.redirect) {
			TOOLS.openNewPage(data.Variables.forum.redirect);
			return;
		}

		if (data.Variables.forum.password == 1 && !data.Variables.forum.threadcount) {
			TOOLS.dialog({content: "当前版块为加密版块，无法通过微社区访问", noMask: true});
			return;
		}

		topTopicCount = 0;
		for (i = 0; i < data.Variables.forum_threadlist.length; i++) {
			if (data.Variables.forum_threadlist[i].displayorder == 1 || (data.Variables.forum_threadlist[i].displayorder == 3 && (data.Variables.forum_threadlist[i].fid == fId || data.Variables.forum_threadlist[i].showactivity))) {
				topTopicCount++;
			}
		}
		data.Variables.topTopicCount = topTopicCount;

		if (data.Variables.forum.threadcount) {
			index.lastPage = parseInt(Math.ceil(data.Variables.forum.threadcount / index.tpp));
		} else if (data.Variables.forum_threadlist.length > 0) {
			index.lastPage = index.page + 2;
		}

		if (index.isLoadCache || index.realFirstLoad) {
			if (!index.isLoadCache) {
				index.firstLoad = false;
			}
			data.Variables.filter = TOOLS.getQuery('filter');

			$('title').html((TOOLS.getQuery('fid') ? data.Variables.forum.name + ' - ' : '') + SITE_INFO.siteName);
			mqq.invoke('nav', 'refreshTitle');

			var tem = 0;
			if (TOOLS.getQuery('a') == 'toplist') {
				tem = topTopicCount % index.tpp;
				index.lastPage = parseInt(topTopicCount / index.tpp);
			} else {
				tem = data.Variables.forum.threadcount % index.tpp;
				if (data.Variables.forum.threadcount) {
					index.lastPage = parseInt(Math.ceil(data.Variables.forum.threadcount / index.tpp));
				}
				if (data.Variables.forum_threadlist.length < index.tpp) {
				}
			}
			if (tem > 0 && !(data.Variables.forum.threadcount)) {
				++index.lastPage;
			}

			$('.bottomBar').show();
			$('#siteHeader').remove();

			if (TOOLS.getQuery('a') != 'toplist') {
				if (data.Variables.forum_threadlist.length && 0 < data.Variables.topTopicCount && data.Variables.topTopicCount < 20) {
					var top_bar = [
						{
							'name': '置顶帖',
							'html': template.render('TopList', data),
							'more': '?a=toplist&fid=' + index.fid
						}
					];
				} else {
					var top_bar = [];
				}
				if (SITE_INFO.settings && SITE_INFO.settings.appList) {
					for (i in SITE_INFO.settings.appList) {
						var appData = {
							'name': SITE_INFO.settings.appList[i]['appName'],
							'html': '<a class="cuTopicImg" href="' + SITE_INFO.settings.appList[i]['clickUrl'] + '"><img src="' + SITE_INFO.settings.appList[i]['imgUrl'] + '"></a>',
							'noheader': 1
						};
						top_bar.push(appData);
					}
				}

				var hook_top_bar = TOOLS.hook(data, 'forumdisplay_topBar');
				if (hook_top_bar && typeof hook_top_bar === 'object') {
					for (i in hook_top_bar) {
						top_bar.push(hook_top_bar[i]);
					}
				}
				var topHtml = '';
				if (top_bar.length > 0) {
					for (i in top_bar) {
						top_bar[i]['thisi'] = parseInt(i);
						var next = parseInt(i) + 1;
						if (next >= top_bar.length) {
							top_bar[i]['nextname'] = top_bar[0]['name'];
							top_bar[i]['nexti'] = 0;
						} else {
							top_bar[i]['nextname'] = top_bar[next]['name'];
							top_bar[i]['nexti'] = next;
						}
						if (top_bar.length == 1) {
							top_bar[i]['only'] = 1;
						}
						topHtml += template.render('TopArea', top_bar[i]);
					}
				}
			}

			data.Variables.threadtypeid = data.Variables.threadtypes ? TOOLS.getQuery('typeid') - 0 : 0;
			data.Variables.threadsortid = data.Variables.threadsorts ? TOOLS.getQuery('sortid') - 0 : 0;

			if (topHtml && index.realFirstLoad) {
				$('#topcontainer').append(topHtml);
				$('.caSide a.incoPage').on('click', function () {
					$('#top_' + $(this).attr('thisi')).hide();
					$('#top_' + $(this).attr('nexti')).show();
				});
				$('.customNotice li').on('click', function () {
					TOOLS.openNewPage('?a=viewthread&tid=' + $(this).attr('tid'));
				});
			}
		}

		if (index.disableforumlist != 1) {
			$('#forumlist').on('click', function (event) {
				TOOLS.openNewPage('?a=forumlist');
			});
		} else {
			$('#forumlist').hide();
		}
		$('#post_thread').unbind('click');
		$('#post_thread').on('click', function (event) {
			FUNCS.newThreadPage(data.Variables.member_uid, index.fid);
			event.stopPropagation();
		});


		if (index.page >= index.lastPage) {
			index.isLastPage = true;
			$('#showAll').show();
		}

		if (index.wsqHtml) {
			$('#container').prepend(index.wsqHtml);
			$('#topicList0 .topicBox').on('click', function () {
				var tid = $(this).attr('tid');
				TOOLS.openNewPage('http://m.wsq.qq.com/' + SITE_ID + '/t/' + tid);
			});
			$('#topicList0 .joinBox').on('click', function () {
				var tid = $(this).attr('tid');
				TOOLS.openNewPage('http://m.wsq.qq.com/' + SITE_ID + '/t/new?parentId=' + tid);
				event.stopPropagation();
			});
			index.wsqHtml = '';
		}

		if (parseInt(data.Variables.forum.picstyle)) {
			data.domain = DOMAIN;
			TC.load("forumdisplay_picbox.htm");
			if (!$('.listShow').length) {
				var html = template.render('picBox', data);
				$('#container').append(html);
			}
			for (var i = 0; i < data.Variables.forum_threadlist.length; i++) {
				if (!data.Variables.forum_threadlist[i].coverpath) {
					continue;
				}
				data.Variables.forum_threadlist[i].coverpath = TOOLS.attachUrl(data.Variables.forum_threadlist[i].coverpath);
				var html = template.render('subPicBox', data.Variables.forum_threadlist[i]);
				if (jQuery('#picBoxL').height() > jQuery('#picBoxR').height()) {
					jQuery('#picBoxR').append(html);
				} else {
					jQuery('#picBoxL').append(html);
				}
			}
			$('.listShowCon').on('click', function () {
				var tid = $(this).attr('tid');
				TOOLS.openNewPage('?a=viewthread&tid=' + tid);
			});

			$('.praiseBtn').on('click', function (event) {
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
		} else {

			var html = '';
			var hook_thread_bottom = TOOLS.hook(data, 'forumdisplay_threadBottom', 1);
			var hook_author_info = TOOLS.hook(data, 'forumdisplay_authorInfo');
			var hook_thread_style_template = TOOLS.hook(data, 'forumdisplay_threadStyleTemplate');
			var hook_thread_style = TOOLS.hook(data, 'forumdisplay_threadStyle', 0, 1);
			for (i = 0; i < data.Variables.forum_threadlist.length; i++) {
				if (TOOLS.getQuery('a') != 'toplist' && data.Variables.forum_threadlist[i].displayorder != 0 && data.Variables.page == 1 && data.Variables.topTopicCount < 20 && !data.Variables.forum_threadlist[i].showactivity
					|| TOOLS.getQuery('a') == 'toplist' && data.Variables.forum_threadlist[i].displayorder == 0) {
					continue;
				}

				if (data.Variables.groupiconid) {
					var groupiconid = data.Variables.groupiconid[data.Variables.forum_threadlist[i].authorid];
					if (parseInt(groupiconid) > 0) {
						data.Variables.forum_threadlist[i].authorLv = groupiconid;
					} else {
						data.Variables.forum_threadlist[i].avatarHtml = '';
						if (groupiconid == 'admin') {
							data.Variables.forum_threadlist[i].avatarHtml = '<span class="statusBg1 brBig db c2 pa"><i class="iconStationmaster commF f11"></i></span>';
						} else if (groupiconid == 'user') {
							data.Variables.forum_threadlist[i].avatarHtml = '<span class="statusBg3 brBig db c2 pa"><i class="iconVUser commF f11"></i></span>';
						}
					}
				}
				data.i = i;
				tplId = 'topicBox';
				if (data.Variables.forum_threadlist[i].showactivity) {
					data.Variables.forum_threadlist[i].showactivity = data.Variables.showactivity[data.Variables.forum_threadlist[i].tid];
					tplId = 'topicBox_showactivity';
					if (!$('#topicBox_showactivity')[0]) {
						TC.load("forumdisplay_showactivity.htm");
					}
				} else {
					if (hook_thread_bottom !== null && typeof hook_thread_bottom === 'object') {
						data.Variables.forum_threadlist[i].hook_thread_bottom = hook_thread_bottom[data.Variables.forum_threadlist[i].tid];
					}
					if (hook_author_info !== null && typeof hook_author_info === 'object') {
						data.Variables.forum_threadlist[i].hook_author_info = hook_author_info[data.Variables.forum_threadlist[i].authorid];
					}
				}
				if (hook_thread_style !== null && typeof hook_thread_style === 'object' && hook_thread_style[data.Variables.forum_threadlist[i].tid]) {
					var tplstyleid = typeof hook_thread_style[data.Variables.forum_threadlist[i].tid]['id'] === 'string' ? hook_thread_style[data.Variables.forum_threadlist[i].tid]['id'] : '';
					var tpl = hook_thread_style_template !== null && typeof hook_thread_style_template === 'object' && hook_thread_style_template[tplstyleid] ? hook_thread_style_template[tplstyleid] : '';
					if (tplstyleid && tpl) {
						var tplvars = typeof hook_thread_style[data.Variables.forum_threadlist[i].tid]['var'] === 'object' ? hook_thread_style[data.Variables.forum_threadlist[i].tid]['var'] : {};
						for (key in tplvars) {
							var reg = new RegExp('\{' + key + '\}', 'g');
							tpl = tpl.replace(reg, tplvars[key]);
						}
						template.compile('tmptpl', TOOLS.stripCode(tpl, 0, '.threadList'));
						html += template.render('tmptpl', data);
						continue;
					}
				}
				html += template.render(tplId, data);
			}
			var pageId = 'topicList' + index.page;
			$('#container').append('<div id="' + pageId + '">' + html + '</div>');
		}

		pageId = '#' + pageId;
		$(pageId + ' .topicBox').on('click', function () {
			var tid = $(this).attr('tid');
			var page = $(this).attr('page') || '';
			TOOLS.openNewPage('?a=viewthread&tid=' + tid + (TOOLS.getQuery('a') != 'toplist' ? '&_bpage=' + page : ''));
		});

		$(pageId + ' .personImgDate .perImg img').on('click', function () {
			TOOLS.openNewPage('?a=profile&uid=' + $(this).attr('uid'));
			event.stopPropagation();
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
			replyComment(data.Variables.formhash, $(this).attr("tid"));
			event.stopPropagation();
		});
		$(pageId + ' .joinBox').on('click', function (event) {
			if (data.Variables.member_uid != "0") {
				replyComment(data.Variables.formhash, $(this).attr("tid"));
			} else {
				FUNCS.replyCommentPage($(this).attr("tid"), 'viewthread');
			}
			event.stopPropagation();
		});
		if (data.Variables.ismoderator != '0') {
			$(pageId + ' .perDate').on('click', function (event) {
				if (typeof topicAdmin == 'undefined') {
					JC.load("topicadmin.js");
				}
				var opts = {
					'tid': $(this).attr('tid'),
					'fid': $(this).attr('fid'),
					'formhash': data.Variables.formhash,
					'isfirst': 1,
					'id': 'tid=' + $(this).attr('tid')
				};
				topicAdmin.menuEvent(opts);
				event.stopPropagation();
			});
		} else {
			$(pageId + ' .perDate').hide();
		}
		TOOLS.getCheckInfo(function (re) {
			index.pluginversion = re.pluginversion;
			var num = 0;
			if (data.Variables.notice.newmypost != '0') {
				num += parseInt(data.Variables.notice.newmypost);
			}
			if (data.Variables.notice.newpm != '0') {
				num += parseInt(data.Variables.notice.newpm);
			}
			if (num > 0) {
				$('.moreC .numP').show();
				$('.moreC .numP').html(num);
			}
		});
		if (index.realFirstLoad) {
			initSideBar(data, 'forumdisplay');
		}

		$(".backBtn").click(function () {
			TOOLS.pageBack();
		});

		TOOLS.lazyLoad();
		if (TOOLS.getQuery('_goto') && $('#' + TOOLS.getQuery('_goto')).length) {
			window.scrollTo(0, $('#' + TOOLS.getQuery('_goto')).offset().top);
		}

		if (index.member_uid != "0") {
			var script = document.createElement("script");
			script.language = "javascript";
			script.type = "text/javascript";
			script.src = API_URL + 'version=4&module=checknewpm&rand=' + Math.random();
			var heads = document.getElementsByTagName('head');
			if (heads.length) {
				heads[heads.length - 1].appendChild(script);
			} else {
				document.documentElement.appendChild(script);
			}
		}
	},
	bindEvent: function () {
		$('#info_center').on('click', function (event) {
			showSideBar();
			event.stopPropagation();
		});

		$('.upBtn').on('click', function () {
			scroll(0, 0);
			$('#backToTopBtn').hide();
		});

	},
	loadPage: function (url, callBack) {
		var pageadd = false;
		var bpage = TOOLS.getQuery('_bpage') > 1 ? TOOLS.getQuery('_bpage') : 0;
		if (index.dropUp) {
			if (index.page == 1 && index.lastPage > 2) {
				index.page++;
			}
			url += '&page=' + (++index.page) + '&tpp=' + index.tpp;
			var pageadd = true;
		} else if (!bpage) {
			index.page = 1;
			url += '&tpp=20';
		}
		if (!pageadd && bpage && callBack != index.dropDownCallBack) {
			index.page = bpage;
			url += '&page=' + index.page;
		}
		var fromuid = TOOLS.getQuery('fromuid');
		if (fromuid) {
			url += '&fromuid=' + fromuid;
		}
		var isRefresh = callBack == index.dropDownCallBack || TOOLS.getcookie('refreshindex');
		var isLoadmore = callBack == index.dropUpCallBack;

		var jsonCache = null;
		var typeid = TOOLS.getQuery('typeid') - 0;
		var digest = TOOLS.getQuery('filter') == 'digest' ? 1 : 0;
		var heats = TOOLS.getQuery('filter') == 'heat' ? 1 : 0;
		if (!isRefresh && !isLoadmore && jsonCache && IS_PAGE_BACK) {
			TOOLS.setcookie('refreshindex', '', -1);
			return;
		}

		TOOLS.dget(url, null, function (data) {
			fId = index.fid = data.Variables.forum.fid;

			if (isRefresh && jsonCache) {
				jsonCache.Variables.forum_threadlist = data.Variables.forum_threadlist;
				data = jsonCache;
			}

			if (!isLoadmore) {
				var typeid = TOOLS.getQuery('typeid') - 0;
			}
			TOOLS.hideLoading();
			index.isLoadCache = false;
			callBack(data);

			var hash = decodeURI(location.hash).split('|');
			if (hash && hash[0] == '#ptlogin') {
				location.hash = '';
				replyComment(data.Variables.formhash, hash[1]);
			}

		}, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		});
	},
	renderIndexPage: function (data) {
		data.siteInfo = index.siteInfo;

		data.Variables.transtotalposts = TOOLS.transformBase(index.totalposts, 4, 1);
		data.Variables.transtotalmembers = TOOLS.transformBase(index.totalmembers, 4, 1);
		data.header = {'name': index.siteInfo.siteName, 'logo': index.siteInfo.siteLogo};
		data.hook_headerbar = '';
		var hook_headerbar = TOOLS.hook(data, 'forumdisplay_headerBar', 1);
		if (hook_headerbar && typeof hook_headerbar === 'string') {
			data.hook_headerbar = hook_headerbar;
		}
		$('#header .header').remove();
		headerHtml = template.render('headerTpl', data);
		$('#header').append(headerHtml);
		if (data.hook_headerbar) {
			$('.header').css('height', 'auto');
		}
		index.renderPage(data);
		index.mobilesign(data);
	},
	renderForumDisplayPage: function (data) {
		data.siteInfo = index.siteInfo;

		data.Variables.transtotalposts = TOOLS.transformBase(data.Variables.forum.posts, 4, 1);
		data.header = {'name': data.Variables.forum.name, 'logo': data.Variables.forum.icon || index.siteInfo.siteLogo};
		data.hook_headerbar = '';
		var hook_headerbar = TOOLS.hook(data, 'forumdisplay_headerBar', 1);
		if (hook_headerbar && typeof hook_headerbar === 'string') {
			data.hook_headerbar = hook_headerbar;
		}
		$('#header .header').remove();
		headerHtml = template.render('headerTpl', data);
		$('#header').append(headerHtml);
		if (data.hook_headerbar) {
			$('.header').css('height', 'auto');
			$('#headerbar').css('max-height', '60px');
		}
		index.renderPage(data);
		index.mobilesign(data);
	},
	loadDZPage: function (ext) {
		var url = API_URL + 'version=4&module=forumdisplay&fid=' + index.fid + ext;
		if (!index.isDefaultFid) {
			index.loadPage(url, index.renderForumDisplayPage);
		} else {
			index.loadPage(url, index.renderIndexPage);
		}
	},
	initPage: function () {
		bottomHtml = template.render('bottomBar');
		$('.bottomBar').append(bottomHtml);
		$('.bottomBar').hide();
		TOOLS.initTouch({obj: $('.warp')[0], end: index.touchEnd, move: index.touchMove});
		index.bindEvent();

		TOOLS.getCheckInfo(function (re) {
			index.ucenterurl = re.ucenterurl;
		});

		TOOLS.showLoading();
		var ext = TOOLS.getQuery('ext') ? '&ext=' + TOOLS.getQuery('ext') : '';
		TOOLS.getCheckInfo(function (re) {
			if (TOOLS.getQuery('fid')) {
				index.fid = TOOLS.getQuery('fid');
				index.isDefaultFid = index.fid == re.defaultfid ? 1 : 0;
			} else {
				TOOLS.openNewPage('?a=forumlist');
				return;
			}
			index.siteInfo = SITE_INFO;
			index.totalposts = re.totalposts;
			index.totalmembers = re.totalmembers;
			index.disableforumlist = re.disableforumlist;
			index.loadDZPage(ext);
		});
	},
	mobilesign: function (data) {
		var hook_mobilesign = TOOLS.hook(data, 'forumdisplay_mobilesign', 1);
		if (hook_mobilesign && typeof hook_mobilesign === 'object') {
			$(".mobilesign").html(TOOLS.stripCode(hook_mobilesign['text']));
			$(".mobilesign").click(function () {
				TOOLS.openNewPage(hook_mobilesign['link']);
			});
		} else {
			$(".mobilesign").hide();
		}
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