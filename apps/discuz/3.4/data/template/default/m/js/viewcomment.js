var tId = 0;
var pId = 0;

var fId = 0;

var threadTitle = '';
var threadContent = '';

var page = 1;
var realPage = 1;
var ppp_defualt = 10;
var isEmpty = false;

var formhash = "";

var member_uid = 0;
var ismoderator = 0;
var ucenterurl = '';

var getRealPage = function (replies, ppp) {
	if (!ppp) {
		ppp = ppp_defualt;
	}
	return Math.ceil(replies / ppp);
};
var bindCommentEvent = function () {
	$('a.replyBtn').unbind("click").click(function () {
		if (member_uid != "0") {
			replyComment(formhash, tId, pId, '楼层', 'commentsubmit=yes&comment', 1, 0, 0);
		}
	});
};

var bindEvent = function () {
	$('a.replyByPid').click(function () {
		if (member_uid != "0") {
			replyComment(formhash, tId, 0, '', 'pid=' + pId + '&comment=yes&commentsubmit', 1, 7, pId);
		} else {
			FUNCS.replyCommentPage(tId, 'showactivity', pId);
		}
	});

	$('a.replyBtn').click(function () {
		replyComment(formhash, tId, pId, '楼层', 'commentsubmit=yes&comment', 1, 0, 0);
	});

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

	$('.perImg img').on('click', function () {
		TOOLS.openNewPage('?a=profile&uid=' + $(this).attr('uid'));
	});

	$(function () {
		TOOLS.lazyLoad();

		TOOLS.showPublicEvent();
	});
};


var dataLoaded = function (json, isInit) {
	var postList = json.Variables.postlist;
	var comments = json.Variables.comments[pId];
	formhash = json.Variables.formhash;
	member_uid = json.Variables.member_uid;
	ismoderator = json.Variables.ismoderator;
	if (isInit) {
		var postItem = postList.shift();
		if (parseInt(postItem['groupiconid']) > 0) {
			postItem['authorLv'] = postItem['groupiconid'];
		} else {
			postItem['avatarClass'] = postItem['groupiconid'];
		}

		json.Variables.thread.firstBody = 'tmpl_thread_normal';
		var topicHtml = template.render('tmpl_topic_item', {'ucenterurl': ucenterurl, 'thread': json.Variables.thread, 'post': postItem, 'siteDomain': DOMAIN, 'fromwx': TOOLS.isWX() || TOOLS.isMQ()});
		$('div.header.topH').append(topicHtml);
		var headerHtml = template.render('tmpl_header_item', {'thread': json.Variables.thread});
		$('#frombar').append(headerHtml);
		$('#frombar').show();
		TOOLS.hideLoading();
		$('title').html(SITE_INFO.siteName);

		TOOLS.initTouch({obj: $('.warp')[0], end: function (e, offset) {
				document.ontouchmove = function (e) {
					return true;
				};
				var loadingObj = $('#loadNext');
				var loadingPos = $('#loadNextPos');
				var loadingObjTop = loadingPos.offset().top - document.body.scrollTop - window.screen.availHeight;

				if (offset.y > 10 && loadingObjTop <= document.body.scrollHeight * 0.3) {
					if (isEmpty) {
						loadingObj.hide();
					} else if (loadingObj.css('display') != 'block') {
						loadingObj.show();
						viewCommentGetMore();
					}
					return false;
				}
			}});
		$('.floatLayer').show();
		$('.header').show();

		var hash = decodeURI(location.hash).split('|');
		if (hash && hash[0] == '#ptlogin') {
			location.hash = '';
			replyComment(formhash, hash[1], 0, '', 'pid=' + hash[2] + '&comment=yes&commentsubmit', 1, 7, hash[2]);
		}

		$('.topicLogo img').on('click', function () {
			TOOLS.openNewPage('?a=profile&uid=' + $(this).attr('uid'));
		});

		var returnurl = '?a=viewthread&tid=' + tId + (TOOLS.getQuery('_bvpage') ? '&_bvpage=' + TOOLS.getQuery('_bvpage') + '&_goto=post' + pId : '') + (TOOLS.getQuery('_bpage') ? '&_bpage=' + TOOLS.getQuery('_bpage') : '');
		$('.return').on('click', function () {
			TOOLS.openNewPage(returnurl);
		});

		$('.backBtn').click(function () {
			if (TOOLS.getQuery("source") || TOOLS.getQuery('_bvpage') || TOOLS.getQuery('_bpage')) {
				TOOLS.openNewPage(returnurl);
			} else {
				TOOLS.pageBack(returnurl);
			}
		});

		threadTitle = json.Variables.thread.subject ? json.Variables.thread.subject : '快来看看这个晒图';
		threadContent = '我参加了#' + SITE_INFO.siteName + '#举办的晒图活动，快来帮我点赞吧';

		if (postItem.authorid == member_uid) {
			$('.tipBor').html('希望更多人喜欢你的照片，点击右上角图标分享<span class="arrLeft"></span>');
		} else {
			$('.tipBor').html('如果喜欢我的照片，请点击右上角图标分享<span class="arrLeft"></span>');
		}

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

		if (TOOLS.isMQ()) {
			mqq.data.setShareInfo({
				share_url: window.location.href + "&source=mq&_wv=1",
				title: threadTitle,
				desc: TOOLS.mb_cutstr(threadContent, 200, '...'),
				image_url: imgUrl
			}, function (result) {});
		}
	}
	var postListHtml = '';
	for (var i in comments) {
		comments[i]['siteDomain'] = DOMAIN;
		comments[i]['message'] = comments[i]['comment'];
		if (parseInt(comments[i]['groupiconid']) > 0) {
			comments[i]['authorLv'] = comments[i]['groupiconid'];
		} else {
			comments[i]['avatarClass'] = comments[i]['groupiconid'];
		}
		postListHtml += template.render('tmpl_post_item', comments[i]);
	}

	$('div.container').append(postListHtml);
	bindEvent();

	TOOLS.parsePost();

	if (isInit && (TOOLS.getQuery('source') == 'pcscan' || TOOLS.getQuery('source') == 'newpic')) {
		$('#shareBtn').click();
	}
};

var viewThreadInit = function () {

	tId = TOOLS.getQuery('tid');
	pId = TOOLS.getQuery('pid');
	var index = TOOLS.getQuery('page');

	if (tId == undefined || tId <= 0) {
		TOOLS.showTips('不正确的主题ID', true, '');
		return;
	}
	if (OS.length == 0) {
		TOOLS.openNewPage(DOMAIN + "forum.php?mod=viewthread&tid=" + tId);
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
	if (pId) {
		url += '&viewpid=' + pId;
	}
	TOOLS.dget(url, null,
		function (json) {
			dataLoaded(json, true);
		},
		function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		}
	);
};

var viewCommentGetMore = function () {
	TOOLS.dget(API_URL + "module=viewcomment&tid=" + tId + "&pid=" + pId + "&page=" + (page + 1) + "&version=4", null,
		function (json) {
			$('#loadNext').hide();
			if (json.Variables.comments[pId].length > 0) {
				dataLoaded(json);
				page++;
			} else {
				isEmpty = true;
			}
		},
		function (error) {
			TOOLS.showTips(error.messagestr, true);
		}
	);
};

viewThreadInit();

var recommend = function (obj) {
	if (member_uid == "0") {
		FUNCS.jumpToLoginPage('a=viewthread&tid=' + tId);
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