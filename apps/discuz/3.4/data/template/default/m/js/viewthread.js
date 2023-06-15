var tId = 0;
var firstpId = 0;
var fId = 0;

var threadTitle = '';
var threadContent = '';

var page = 1;
var realPage = 1;
var ppp_defualt = 5;
var isEmpty = false;

var formhash = "";

var member_uid = 0;
var ismoderator = 0;
var isshow = 0;
var showinit = 0;
var ucenterurl = '';
var authorid = 0;

var bvloaded = false;
var pheadered = false;

var getRealPage = function (replies, ppp) {
	if (!ppp) {
		ppp = ppp_defualt;
	}
	return Math.ceil(replies / ppp);
};

var filterReload = function (tid, authorid, ordertype) {
	var url = '?a=viewthread&tid=' + tid;
	if (authorid) {
		url += '&authorid=' + authorid;
	}
	if (ordertype) {
		url += '&ordertype=' + ordertype;
	}
	TOOLS.openNewPage(url);
};

var bindCommentEvent = function () {
	$('a.replyByPid').unbind("click").click(function () {
		var pId = $(this).attr("pid");
		var first = $(this).attr("first");
		var author = '楼主';
		if (!first) {
			dom = $("#aut_" + $(this).attr("pid"));
			author = dom[0].innerHTML;
		}
		if (member_uid != "0") {
			replyComment(formhash, $(this).attr("tid"), pId, author, 'commentsubmit=yes&comment', 1, 0, 0);
		} else {
			FUNCS.replyCommentPage(tId, 'viewthread', pId, author);
		}
	});
};

var bindEvent = function () {
	$('a.replyByPid').click(function () {
		var pId = $(this).attr("pid");
		var first = $(this).attr("first");
		var dom = $("#msg_" + $(this).attr("pid"));
		var author = "";

		if (!first) {
			if (dom.children("div.reply_wrap").length > 0) {
				message = dom[0].innerHTML.split("</div>")[dom[0].innerHTML.split("</div>").length - 1];
			} else {
				message = dom[0].innerHTML;
			}

			dom = $("#aut_" + $(this).attr("pid"));
			author = dom[0].innerHTML;
		}
		replyComment(formhash, $(this).attr("tid"), pId, author, '', 0, isshow ? 7 : 0, 0);
	});

	if (!$("a.replyBtn").attr('bind')) {
		$("a.replyBtn").click(function () {
			replyComment(formhash, tId, 0, '', '', 0, isshow ? 7 : 0, 0);
		});
		$("a.replyBtn").attr('bind', 1);
	}
	$('#recommendBtn').on('click', function () {
		recommend(this);
	});
	$('#shareBtn').on('click', function () {
		$('.tipInfo').show();
		$('#frombar').addClass('pt');
		$('.maskLayer').show();
		$(".maskLayer").click(function () {
			$('.tipInfo').hide();
			$('#frombar').removeClass('pt');
			$('.maskLayer').hide();
		});
	});
	$('.hotLabel a[ordertype]').on('click', function () {
		filterReload(tId, 0, $(this).attr('ordertype'));
	});
	$('.hotLabel a[authorid]').on('click', function () {
		filterReload(tId, authorid == TOOLS.getQuery('authorid') ? 0 : authorid);
	});

	if (ismoderator != '0') {
		$('.perDate').on('click', function (event) {
			if (typeof topicAdmin == 'undefined') {
				JC.load("topicadmin.js");
			}
			var opts = {
				'tid': $(this).attr('tid'),
				'pid': $(this).attr('pid'),
				'fid': fId,
				'formhash': formhash,
				'isfirst': $(this).attr('isfirst') ? 1 : 0,
				'id': $(this).attr('isfirst') ? 'tid=' + $(this).attr('tid') : 'pid=' + $(this).attr('pid'),
				'ban': $(this).attr('ban') ? 1 : 0
			};
			topicAdmin.menuEvent(opts);
			event.stopPropagation();
		});
	} else {
		$('.perDate').hide();
	}

	$('.viewcommentBtn').on('click', function () {
		TOOLS.openNewPage('?a=viewcomment&tid=' + $(this).attr('tid') + '&pid=' + $(this).attr('pid'));
	});
	$('.perImg img').on('click', function () {
		TOOLS.openNewPage('?a=profile&uid=' + $(this).attr('uid'));
	});

	$('#info_center').on('click', function (event) {
		showSideBar();
		event.stopPropagation();
	});

	if (isshow) {
		$('#info_center').hide();
	}
};

var dataLoaded = function (json, isInit) {
	var postList = json.Variables.postlist;
	fId = json.Variables.thread.fid;
	formhash = json.Variables.formhash;
	member_uid = json.Variables.member_uid;
	ismoderator = json.Variables.ismoderator;
	isshow = parseInt(json.Variables.thread.showactivity);
	special = parseInt(json.Variables.thread.special);
	if (special == 4 && isshow) {
		special = 0;
	}
	var price = parseInt(json.Variables.thread.price);
	var allowComment = false;

	if (SITE_INFO.blocktids && TOOLS.in_array(json.Variables.thread.tid, SITE_INFO.blocktids)) {
		TOOLS.hideLoading();
		TOOLS.dialog({content: "帖子已关闭", noMask: true, autoClose: 1});
		return;
	}
	if (isInit) {
		var postItem = postList.shift();
		firstpId = postItem['pid'];
		if (parseInt(postItem['groupiconid']) > 0) {
			postItem['authorLv'] = postItem['groupiconid'];
		} else {
			postItem['avatarHtml'] = '';
			if (postItem['groupiconid'] == 'admin') {
				postItem['avatarHtml'] = '<span class="statusBg1 brBig db c2 pa"><i class="iconStationmaster commF f11"></i></span>';
			} else if (postItem['groupiconid'] == 'user') {
				postItem['avatarHtml'] = '<span class="statusBg3 brBig db c2 pa"><i class="iconVUser commF f11"></i></span>';
			}
		}
		authorid = postItem.authorid;
		var headerHtml = template.render('tmpl_header_item', {'name': SITE_INFO.siteName, 'logo': SITE_INFO.siteLogo});
		$('#frombar').append(headerHtml);
		$('#frombar').show();
		$('title').html((json.Variables.thread.subject ? json.Variables.thread.subject + ' - ' : '') + SITE_INFO.siteName);

		if (SITE_INFO.followurl && TOOLS.isWX()) {
			$('.follow').show();
			$('.follow').on('click', function () {
				TOOLS.openNewPage(SITE_INFO.followurl);
				event.stopPropagation();
			});
		}
		var hook_author_info = TOOLS.hook(json, 'viewthread_authorInfo');
		if (hook_author_info !== null && typeof hook_author_info === 'object') {
			postItem.hook_author_info = hook_author_info[postItem.authorid];
		}
		var hook_post_bottom = TOOLS.hook(json, 'viewthread_postBottom', 1);
		if (hook_post_bottom !== null && typeof hook_post_bottom === 'object') {
			postItem.hook_post_bottom = hook_post_bottom[postItem.pid];
		}
		var hook_thread_top = TOOLS.hook(json, 'viewthread_threadTop', 1);
		if (hook_thread_top !== null && typeof hook_thread_top === 'string') {
			postItem.hook_thread_top = hook_thread_top;
		}
		var hook_thread_bottom = TOOLS.hook(json, 'viewthread_threadBottom', 1);
		if (hook_thread_bottom !== null && typeof hook_thread_bottom === 'string') {
			postItem.hook_thread_bottom = hook_thread_bottom;
		}

		var comments = [];
		var commentCount = 0;
		if (allowComment && typeof (json.Variables.comments[postItem.pid]) != 'undefined') {
			comments = json.Variables.comments[postItem.pid];
			commentCount = json.Variables.commentcount[postItem.pid];
		}

		postItem.message = imageListInit(postItem);
		postItem.message = attachListInit(postItem);
		tmpl_data = {
			'uid': json.Variables.member_uid,
			'thread': json.Variables.thread,
			'post': postItem,
			'comments': comments,
			'commentCount': commentCount,
			'allowComment': allowComment,
			'fromwx': TOOLS.isWX() || TOOLS.isMQ(),
			threadsortshow: json.Variables.threadsortshow || null,
			'SITE_INFO': SITE_INFO,
			'ucenterurl': ucenterurl
		};
		json.Variables.thread.firstBody = 'tmpl_thread_normal';
		var specialEvent = false;
		if (special > 0) {
			var specials = {1: 'poll', 3: 'reward', 4: 'activity'};
			if (specials[special]) {
				if (typeof specialThread == 'undefined') {
					TC.load("viewthread_" + specials[special] + ".htm");
					JC.load("viewthread_" + specials[special] + ".js");
				}
				tmpl_data = specialThread.init(json, tmpl_data);
				specialEvent = specialThread.haveEvent;
				json.Variables.thread.firstBody = 'tmpl_thread_' + specials[special];
			}
		}
		var topicHtml = template.render('tmpl_topic_item', tmpl_data);
		$('div.header.topH').append(topicHtml);
		imageviewCommon('#msg_' + postItem.pid);
		if (specialEvent) {
			specialThread.bindEvent();
		}

		var hook_top_bar = TOOLS.hook(json, 'viewthread_topBar', 1);
		if (hook_top_bar !== null && typeof hook_top_bar === 'string') {
			var topHtml = template.render('TopArea', {'html': hook_top_bar});
			$('#topcontainer').append(topHtml);
			$('#topcontainer').show();
		}

		TOOLS.hideLoading();
		initSideBar(json, 'viewthread');
		TOOLS.initTouch({obj: $('.warp')[0], end: function (e, offset) {
				document.ontouchmove = function (e) {
					return true;
				};
				var loadingObj = $('#loadNext');
				var loadingPos = $('#loadNextPos');
				var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;

				isEmpty = page >= realPage;

				if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.3) {
					if (isEmpty) {
						loadingObj.hide();
					} else if (loadingObj.css('display') != 'block') {
						loadingObj.show();
						viewThreadGetMore();
						if (!isshow) {
							var postLists = $('.container')[0].children;
							if (postLists.length > 20) {
								var lastList = postLists[postLists.length - 1].children;
								var lastTopic = postLists[postLists.length - 1];
								var currentInnerTop = lastTopic.offsetTop - document.body.scrollTop;
								for (i = 0; i < 5; i++) {
									postLists[i].remove();
								}
								window.scrollTo(0, lastTopic.offsetTop - currentInnerTop);
							}
						} else {
							var postLists = $('#picBoxL')[0].children;
							if (postLists.length > 20) {
								var lastList = postLists[postLists.length - 1].children;
								var lastTopic = postLists[postLists.length - 1];
								var currentInnerTop = lastTopic.offsetTop - document.body.scrollTop;
								for (i = 0; i < 5; i++) {
									postLists[i].remove();
								}
								var postLists = $('#picBoxR')[0].children;
								for (i = 0; i < 5; i++) {
									postLists[i].remove();
								}
								window.scrollTo(0, lastTopic.offsetTop - currentInnerTop);
							}
						}
					}
					return false;
				}

				if (document.body.scrollTop <= 0 && offset.y < 0) {
					$('#refreshWait').show();
					TOOLS.openNewPage('?a=viewthread&tid=' + tId +
						(TOOLS.getQuery('_bpage') ? '&_bpage=' + TOOLS.getQuery('_bpage') : '') +
						(TOOLS.getQuery('ordertype') ? '&ordertype=' + TOOLS.getQuery('ordertype') : '') +
						(TOOLS.getQuery('authorid') ? '&authorid=' + TOOLS.getQuery('authorid') : '')
						);
					return false;
				}
			}});
		$('.floatLayer').show();
		$('.header.topH').show();

		var hash = decodeURI(location.hash).split('|');
		if (hash && hash[0] == '#ptlogin') {

			location.hash = '';
			if (hash.length > 2) {
				replyComment(formhash, hash[1], hash[2], hash[3], '', 0, isshow ? 7 : 0, 0);
			} else {
				replyComment(formhash, hash[1], 0, '', '', 0, isshow ? 7 : 0, 0);
			}
		}

		$('.topH .topicLogo img').on('click', function () {
			TOOLS.openNewPage('?a=profile&uid=' + $(this).attr('uid'));
		});

		var returnurl = '?a=index&fid=' + fId + (TOOLS.getQuery('_bpage') ? '&_bpage=' + TOOLS.getQuery('_bpage') + '&_goto=thread' + tId : '');
		$('.backBtn').click(function () {
			if (JSGLOBAL.source || isshow || TOOLS.getQuery('_bpage')) {
				TOOLS.openNewPage(returnurl);
			} else {
				TOOLS.pageBack(returnurl);
			}
		});

		$('.return').click(function () {
			TOOLS.openNewPage(returnurl);
		});

		threadTitle = json.Variables.thread.subject ? json.Variables.thread.subject : '快来看看这个话题';
		threadContent = postItem.message;

		var imgUrl = SITE_INFO.siteLogo;
		if (postItem.imagelist && postItem.imagelist.length > 0) {
			if (postItem.attachments[postItem.imagelist[0]].url) {
				imgUrl = TOOLS.attachUrl(postItem.attachments[postItem.imagelist[0]].url) + postItem.attachments[postItem.imagelist[0]].attachment;
			} else {
				imgUrl = TOOLS.attachUrl(postItem.attachments[postItem.imagelist[0]].attachment);
			}
		} else {
			var v = 0;
			$('.reading img').each(function () {
				var img = $(this);
				if (!img.attr('smilieid') && !v) {
					imgUrl = img.attr('data-original');
					v = 1;
				}
			});
		}
		var des = json.Variables.thread.subject;
		var t = json.Variables.thread.subject;

		if (typeof WeixinJSBridge != 'undefined') {
			initWXShare({
				'img': imgUrl,
				'desc': des,
				'title': t
			});
		} else {
			$(document).bind('WeixinJSBridgeReady', function () {
				initWXShare({
					'img': imgUrl,
					'desc': des,
					'title': t
				});
			});
		}
	}

	if (isshow) {
		if (json.Variables.special_activity.view) {
			var viewhot = json.Variables.special_activity.view == 'hot' ? 1 : 0;
		} else {
			var viewhot = 1;
		}
		if (viewhot) {
			if (json.Variables.special_activity.top_postlist && json.Variables.special_activity.top_postlist.length > 0) {
				postList = json.Variables.special_activity.top_postlist;
			} else {
				viewhot = 0;
			}
		} else {
			if (json.Variables.special_activity.my_postlist && json.Variables.special_activity.my_postlist.length > 0) {
				postList = json.Variables.special_activity.my_postlist.concat(postList);
			}
		}
		if (!postList.length) {
			realPage = 0;
		}
		json.viewhot = viewhot;
		TC.load("viewthread_picbox.htm");
		if (!$('.listShow').length) {
			var html = template.render('picBox', json);
			$('div.container').append(html);
		}
		var postListHtml = '';
		for (var i in postList) {
			if (!postList[i]['imagelist']) {
				continue;
			}
			postList[i]['imagenumber'] = postList[i]['imagelist'].length;
			var aid = postList[i]['imagelist'].shift();
			if (!aid) {
				continue;
			}
			if (postList[i]['attachments'][aid].url) {
				postList[i]['coverpath'] = TOOLS.attachUrl(postList[i]['attachments'][aid].url) + postList[i]['attachments'][aid].attachment;
			} else {
				postList[i]['coverpath'] = TOOLS.attachUrl(postList[i]['attachments'][aid].attachment);
			}
			postList[i].page = page;

			var html = template.render('subPicBox', postList[i]);
			if (jQuery('#picBoxL').height() > jQuery('#picBoxR').height()) {
				jQuery('#picBoxR').append(html);
			} else {
				jQuery('#picBoxL').append(html);
			}
		}
		$('.listShowCon').on('click', function () {
			var pid = $(this).attr('pid');
			var bvpage = $(this).attr('page');
			TOOLS.openNewPage('?a=showactivity&tid=' + tId + '&viewpid=' + pid + '&_bvpage=' + bvpage + (TOOLS.getQuery('_bpage') ? '&_bpage=' + TOOLS.getQuery('_bpage') : ''));
		});
		$('.praiseBtn').on('click', function (event) {
			if (member_uid == "0") {
				FUNCS.jumpToLoginPage('a=viewthread&tid=' + tId + (TOOLS.getQuery('_bpage') ? '&_bpage=' + TOOLS.getQuery('_bpage') : ''));
				event.stopPropagation();
				return;
			}
			var btn = $(this);
			var praisestatus = btn.find('i').attr('class');
			if (praisestatus == 'noPraise') {
				var pid = btn.attr('pid');
				var hash = json.Variables.formhash;
				var praiseUrl = API_URL + "version=4&module=showactivity&do=recommend&tid=" + tId + "&pid=" + pid + "&hash=" + hash;
				TOOLS.dget(praiseUrl, null, function (json) {
					if (json.Variables.result == 1) {
						btn.find('i').removeClass('noPraise').addClass('praise');
						var digSpan = btn.find('span');
						var digNum = parseInt(digSpan.html());
						if (isNaN(digNum)) {
							digNum = 0;
						}
						digSpan.html(digNum + 1);
					} else {
						TOOLS.showTips('您已经赞过', 1);
					}
				},
					function (error) {
					});
			}
			event.stopPropagation();
		});
		if (!showinit) {
			var topicHtml = template.render('tmpl_picbox_item', {'thread': json.Variables.thread, 'post': postItem, 'expiration': json.Variables.special_activity.expiration, 'viewhot': viewhot});
			$('div.header.topH').html(topicHtml);
			$('.container.conM').removeClass('conM');
			$('#authorInfo').hide();
			$('#showDetail').on('click', function () {
				if ($('#showDetail').attr('class') == 'detailShow') {
					$('.detailShow').addClass('more');
					$('.incoA.db').addClass('iBtnOn1');
				} else {
					$('.detailShow').removeClass('more');
					$('.incoA.db').removeClass('iBtnOn1');
				}
			});
			$('.replyBtn').html('<i class="incoR"></i>参与');
			$('.replyBtn').addClass('joinBtn');
			if (json.Variables.special_activity.closed && json.Variables.special_activity.closed != '0') {
				$('a.replyByPid').hide();
				$('.replyBtn').hide();
			}
			if (viewhot) {
				$('#showTabNew').on('click', function (event) {
					TOOLS.openNewPage('?a=viewthread&tid=' + tId + '&viewnew=yes');
				});
			} else {
				$('#showTabTop').on('click', function (event) {
					TOOLS.openNewPage('?a=viewthread&tid=' + tId + '&viewhot=yes');
				});
			}
			showinit = 1;
		}
	} else {
		var postListHtml = '';
		headerdata = {
			authorid: TOOLS.getQuery('authorid') && authorid == TOOLS.getQuery('authorid'),
			ordertype: typeof (json.Variables.thread.ordertype) != 'undefined' ? json.Variables.thread.ordertype : (typeof (TOOLS.getQuery('ordertype')) != 'undefined' ? TOOLS.getQuery('ordertype') : 0)
		};
		if (!pheadered) {
			postHeaderHtml = template.render('tmpl_post_header', headerdata);
			$('#containerHeader').append(postHeaderHtml);
			pheadered = true;
		}
		var hook_author_info = TOOLS.hook(json, 'viewthread_authorInfo');
		var hook_post_bottom = TOOLS.hook(json, 'viewthread_postBottom', 1);
		for (var i in postList) {
			if (!postList[i]['dbdateline'] && !postList[i]['message']) {
				continue;
			}
			if (parseInt(postList[i]['groupiconid']) > 0) {
				postList[i]['authorLv'] = postList[i]['groupiconid'];
			}
			if (hook_author_info !== null && typeof hook_author_info === 'object') {
				postList[i].hook_author_info = hook_author_info[postList[i].authorid];
			}
			if (hook_post_bottom !== null && typeof hook_post_bottom === 'object') {
				postList[i].hook_post_bottom = hook_post_bottom[postList[i].pid];
			}

			var comments = [];
			var commentCount = 0;
			if (allowComment && typeof (json.Variables.comments[postList[i].pid]) != 'undefined') {
				comments = json.Variables.comments[postList[i].pid];
				commentCount = json.Variables.commentcount[postList[i].pid];
			}
			postList[i].comments = comments;
			postList[i].allowComment = allowComment;
			postList[i].commentCount = commentCount;
			postList[i].ucenterurl = ucenterurl;
			postList[i].message = imageListInit(postList[i]);
			postList[i].message = attachListInit(postList[i]);
			postList[i].firstAuthor = authorid == postList[i].authorid;
			postList[i].isReward = special == 3 && price > 0 && member_uid != postList[i].authorid;
			postListHtml += template.render('tmpl_post_item', postList[i]);
			$('#containerHeader').show();
		}
	}
	$('div.container').append(postListHtml);
	for (var i in postList) {
		imageviewCommon('#msg_' + postList[i].pid);
	}

	if (special == 3 && price > 0) {
		$('.reward_answer').on('click', function () {
			var tid = $(this).attr('tid');
			var pid = $(this).attr('pid');
			var url = DOMAIN + 'api/mobile/index.php?module=bestanswer&version=4&tid=' + tid + '&pid=' + pid;
			var post = 'formhash=' + formhash;
			TOOLS.dpost(url, post,
				function (succ) {
					TOOLS.showTips(succ.messagestr, true);
					setTimeout(function () {
						location.reload();
					}, 300);
				},
				function (error) {
					TOOLS.showTips(error.messagestr, true);
				}
			);
		});
	}
	bindEvent();
	if (allowComment) {
		bindCommentEvent();
	}
	TOOLS.parsePost();

	if (isInit && JSGLOBAL.source == 'pcscan') {
		$('#shareBtn').click();
	}

	$(function () {
		$('.media').each(function () {
			if (typeof parseMedia == 'undefined') {
				JC.load("media.js");
			}
			parseMedia($(this));
		});
		TOOLS.lazyLoad();
		if (TOOLS.getQuery('_bvpage') && TOOLS.getQuery('_bvpage') > 1 && !bvloaded) {
			var ext = TOOLS.getQuery('ext') ? '&ext=' + TOOLS.getQuery('ext') : '';
			var share = TOOLS.getQuery('share') ? '&share=' + TOOLS.getQuery('share') : '';
			var url = API_URL + "module=viewthread&tid=" + tId + "&version=4" + ext + share;
			var fromuid = TOOLS.getQuery('fromuid');
			if (fromuid) {
				url += '&fromuid=' + fromuid;
			}
			url += '&page=' + TOOLS.getQuery('_bvpage');
			page = TOOLS.getQuery('_bvpage');
			TOOLS.dget(url, null,
				function (json) {
					dataLoaded(json, true);
					if (TOOLS.getQuery('_goto') && $('#' + TOOLS.getQuery('_goto')).length) {
						window.scrollTo(0, $('#' + TOOLS.getQuery('_goto')).offset().top);
					}
				},
				function (error) {
					TOOLS.hideLoading();
					TOOLS.showTips(error.messagestr, true);
				}
			);
			bvloaded = true;
		}

		TOOLS.showPublicEvent();

		if (member_uid != "0") {
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
	});

};

var viewThreadInit = function () {

	tId = TOOLS.getQuery('tid');
	fId = TOOLS.getQuery('fid');
	var index = TOOLS.getQuery('page');

	if (tId == undefined || tId <= 0) {
		TOOLS.showTips('不正确的主题ID', true, '');
		return;
	}

	if (index != undefined && index > 1) {
		page = index;
	}

	TOOLS.getCheckInfo(function (re) {
		ucenterurl = re.ucenterurl;
	});

	bottomHtml = template.render('bottomBar');
	$('.bottomBar').append(bottomHtml);

	TOOLS.showLoading();
	var ext = TOOLS.getQuery('ext') ? '&ext=' + TOOLS.getQuery('ext') : '';
	var share = TOOLS.getQuery('share') ? '&share=' + TOOLS.getQuery('share') : '';
	var url = API_URL + "module=viewthread&tid=" + tId + "&version=4" + ext + share;
	var fromuid = TOOLS.getQuery('fromuid');
	if (fromuid) {
		url += '&fromuid=' + fromuid;
	}
	if (TOOLS.getQuery('_bvpage') && TOOLS.getQuery('_bvpage') > 1) {
		url += '&ppp=1';
	} else {
		url += '&ppp=' + ppp_defualt;
	}
	TOOLS.dget(url, null,
		function (json) {
			realPage = getRealPage(parseInt(json.Variables.thread.replies) + 1, ppp_defualt);
			dataLoaded(json, true);
		},
		function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		}
	);
};

var viewThreadGetMore = function () {
	if (page < realPage) {
		TOOLS.dget(API_URL + "module=viewthread&tid=" + tId + "&page=" + (page + 1) + "&ppp=" + ppp_defualt + "&version=4", null,
			function (json) {
				$('#loadNext').hide();
				page++;
				dataLoaded(json);
			},
			function (error) {
				TOOLS.showTips(error.messagestr, true);
			}
		);
	} else {
		isEmpty = true;
	}
};

var recommend = function (obj) {
	if (member_uid == "0") {
		TOOLS.showTips('您尚未登录，没有权限点赞', true);
		TOOLS.openLoginPage(location.href, 1000);
		return;
	}
	var btn = $(obj);
	var status = btn.find('i').attr('class');
	if (status == 'praise') {
		TOOLS.showTips('您已经赞过该帖', true);
		return;
	}

	var praiseUrl = API_URL + 'version=4&module=recommend&tid=' + tId + '&hash=' + formhash;
	TOOLS.dget(praiseUrl, null,
		function (data) {
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
};

var attachListInit = function(post) {
    var ret = post.message, skipaid = [];
    ret = ret.replace(/\[attach\](\d+)\[\/attach\]/ig, function($0, $1) {
        skipaid.push($1);
        return '<b>' + ' <a href="' + (post.attachments[$1]['payed'] ? SITE_INFO.siteUrl + 'forum.php?mod=attachment&aid=' + post.attachments[$1]['aidencode'] : '###') + '">' + post.attachments[$1]['filename'] + ' (' + post.attachments[$1]['attachsize'] + ')</a></b> ';
    });
    for(var i in post.attachlist) {
        if(!TOOLS.in_array(post.attachlist[i], skipaid)) {
            ret += '<br /><b><i class="circle">●</i> ' + '<a href="' + (post.attachments[post.attachlist[i]]['payed'] ? SITE_INFO.siteUrl + 'forum.php?mod=attachment&aid=' + post.attachments[post.attachlist[i]]['aidencode'] : '###') + '">' + post.attachments[post.attachlist[i]]['filename'] + ' (' + post.attachments[post.attachlist[i]]['attachsize'] + ')</a></b>';
        }
    }
    return ret;
};


template.helper('threadsortshowImage', function (s, attach) {
	s = s.replace(/^.+?href="(.+?)".+?$/g, function ($0, $1) {
		var url = SITE_INFO.siteUrl + $1;
		return '<img class="lazy" data-original="' + url + '" attach="' + attach + '" />';
	});
	return s;
});

viewThreadInit();