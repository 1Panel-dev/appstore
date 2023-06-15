TC.load("reply.htm");

var replyCommenttId = 0;
function replyComment(formhash, tId, pId, author, extra, iscomment, ttype, tparentid)
{
	replyCommenttId = tId;
	var iscomment = iscomment || 0;
	var str = template.render(!iscomment ? 'tmpl_replyForm' : 'tmpl_commentForm', "");
	var noticeTrim = "";
	var storageKey = tId + "reply_content";
	var extra = extra || 'replysubmit';
	var ttype = ttype || 0;
	var tparentid = tparentid || 0;
	var pId = pId || 0;

	function checkSecure(force) {
		secure.checkSecure({
			"success": function () {
				if (secure.isNeedSecure) {
					var opts = {
						'seccode': secure.seccode,
						'secqaa': secure.secqaa,
						'success': function (seccoderesult) {
							var extraPost = "&sechash=" + secure.sechash
								+ "&seccodeverify=" + seccoderesult.seccodeverify
								+ "&secanswer=" + seccoderesult.secanswer;
							sendPost('', extraPost);
						}
					};
					secure.showSecure(opts);
				} else {
					sendPost('', '');
				}
				return false;
			},
			"error": function (error) {

			}

		}, 'post', force);

	}

	TOOLS.dialog({content: str,
		id: 'replyForm',
		isHtml: true,
		isMask: true,
		top: 1,
		callback: function () {
			jQuery('#message').focus();
			emotion.init();
			jQuery(".editArea").hide();
			jQuery(".pSpace").css("width", "100%");
			jQuery("#fwin_dialog_replyForm").css('min-width', '');
			jQuery("#fwin_dialog_replyForm").css('left', '');
			jQuery("#fwin_dialog_replyForm").css('top', '');
			jQuery("#fwin_dialog_replyForm").css('bottom', '1px');
			jQuery("#fwin_dialog_replyForm").css('width', '100%');

			jQuery('#message').val(localStorage.getItem(storageKey));

			if (pId == null || pId == 0) {
				jQuery('#message').attr('placeholder', '说两句吧~');
			} else {
				jQuery('#message').attr('placeholder', "回复:" + author.replace(/(^\s*)|(\s*$)/g, ''));
				TOOLS.dget(API_URL + "module=sendreply&tid=" + tId + "&repquote=" + pId + "&version=3", null,
					function (json) {
						noticeTrim = json.Variables.noticetrimstr;
						var sendreply_extraInfo = TOOLS.hook(json, 'sendreply_extraInfo');
						sendreply_extraInfo = TOOLS.stripCode(sendreply_extraInfo);
						$('#customHtml').append(sendreply_extraInfo);
					},
					function (error) {
					}
				);
			}

			timer = setInterval(function () {

				if (TOOLS.mb_strlen(jQuery('#message').val()) > 280) {
					jQuery('#message').val(
						jQuery('#message').val().substring(0, 280));
				}

				if (jQuery('#message').val()) {
					localStorage.removeItem(storageKey);
					localStorage.setItem(storageKey, jQuery('#message').val());
				}
			}, 500);


			if (!iscomment) {
				jQuery(".expreSelect").click(function () {
					emotion.show();
				});

				jQuery(".photoSelect").click(function () {
					emotion.hide();
				});
			}

			jQuery(".sendBtn").click(function () {

				var content = jQuery('#message').val();
				var contentLen = TOOLS.mb_strlen(TOOLS.trim(content));

				if (contentLen == 0) {
					TOOLS.dialog({content: '请输入回复内容', autoClose: true});
					return;
				}
				jQuery('input[name="mobiletype"]').val(5);

				jQuery('#message').val(localStorage.getItem(storageKey));

				checkSecure();

			});

			jQuery(".cancelNewBtn").click(function () {
				clearInterval(timer);
				var opts = {id: 'replyForm'};
				TOOLS.hideDialog(opts);
			});

		}
	});

	if (!iscomment) {
		JC.load("newreply.js");
		JC.load("jpegmeta.js");
		JC.load("jpeg.encoder.basic.js");
		JC.load("image_compress.js");
	}

	var sendPost = function (extraUrl, extraPost) {
		extraUrl = extraUrl || "";
		extraPost = extraPost || "";
		var callbackFunc = {
			success: function (re) {
				TOOLS.hideLoading();
				var message = re.Message ? re.Message.messageval : '';
				clearInterval(timer);
				localStorage.removeItem(storageKey);
				var postMessage = jQuery('#message').val();
				jQuery('#message').val('');
				TOOLS.showTips("回复成功", true);
				TOOLS.setcookie('refreshindex', '1', 86400);
				var showactivity = typeof isshow == 'undefined' ? 0 : isshow;

				var a = TOOLS.getQuery('a');
				if (a != 'viewthread' && typeof index !== 'undefined') {
					setTimeout("jQuery('.cancelNewBtn').click()", 1000);
					setTimeout("postAppend(" + re.Variables.tid + "," + index.member_uid + ",'" + index.member_username + "','" + index.member_avatar + "','" + postMessage + "')", 1000);
				} else if (a == 'showactivity' || showactivity) {
					TOOLS.openNewPage('?a=showactivity&tid=' + re.Variables.tid + '&viewpid=' + re.Variables.pid + (!iscomment ? '&source=newpic' : ''));
				} else if (a == 'viewcomment') {
					TOOLS.openNewPage('?a=viewcomment&tid=' + re.Variables.tid + '&pid=' + re.Variables.pid);
				} else {
					var url = API_URL + "module=viewthread&tid=" + re.Variables.tid + "&version=4&viewpid=" + re.Variables.pid;
					TOOLS.dget(url, null,
						function (json) {
							var groupiconid = parseInt(json['Variables']['postlist'][0]['groupiconid']);
							if (groupiconid > 0) {
								json['Variables']['postlist'][0]['authorLv'] = json['Variables']['postlist'][0]['groupiconid'];
							}
							var hook_author_info = TOOLS.hook(json, 'viewthread_authorInfo');
							var hook_post_bottom = TOOLS.hook(json, 'viewthread_postBottom', 1);
							if (hook_author_info !== null && typeof hook_author_info === 'object') {
								json['Variables']['postlist'][0]['hook_author_info'] = hook_author_info[json['Variables']['postlist'][0]['authorid']];
							}
							if (hook_post_bottom !== null && typeof hook_post_bottom === 'object') {
								json['Variables']['postlist'][0]['hook_post_bottom'] = hook_post_bottom[json['Variables']['postlist'][0]['pid']];
							}
							json['Variables']['postlist'][0]['number'] = json['Variables']['postlist'][0]['position'] ? json['Variables']['postlist'][0]['position'] : '#';
							json['Variables']['postlist'][0]['siteDomain'] = SITE_INFO['siteUrl'];
							json['Variables']['postlist'][0]['ucenterurl'] = ucenterurl;
							json['Variables']['postlist'][0]['commentCount'] = 0;
							json['Variables']['postlist'][0]['postComment'] = [];

							var newposttpl = template.render('tmpl_post_item', json['Variables']['postlist'][0]);
							if (extra.indexOf('commentsubmit') >= 0) {
								TOOLS.openNewPage('?a=viewthread&tid=' + re.Variables.tid);
							} else {
								$('div.container').append(newposttpl);
							}
							TOOLS.parsePost();
							$('#containerHeader').show();
							setTimeout("jQuery('.cancelNewBtn').click()", 1000);
							setTimeout("jQuery('html, body').animate({scrollTop:jQuery(document).height()});", 1000);
							TOOLS.lazyLoad();
						},
						function (error) {
							TOOLS.hideLoading();
							TOOLS.showTips(error.messagestr, true);
						}
					);

				}
			},
			error: function (error) {
				TOOLS.hideLoading();
				if (error.messageval == 'submit_seccode_invalid') {
					secure.isChecked = false;
					checkSecure(1);
				} else {
					TOOLS.showTips(error.messagestr, true);
				}
			}

		};

		$('#replyForm input[name=formhash]').val(formhash);
		var postParams = $('#replyForm').serialize() + extraPost;

		var reqUrl = API_URL + "version=4&module=sendreply&tid=" + tId + "&pid=" + pId + "&" + extra + "=yes" + extraUrl;

		if (pId != null) {
			postParams += "&reppid=" + pId;
			postParams += "&noticetrimstr=" + noticeTrim;
		}
		var attach = jQuery('.attachhide');
		if (attach.length > 0) {
			for (var i = 0; i < attach.length; i++) {
				postParams += "&" + attach[i].name + "=" + attach[i].value;
			}
		}

		TOOLS.dpost(reqUrl, postParams, callbackFunc.success, callbackFunc.error);
		TOOLS.showLoading(null, '正在发送');
	};
}

function postAppend(tid, uid, username, avatar, message) {
	jQuery('.topicBox[tid=' + tid + '] .topicList ul').append('<li><img src="' + avatar + '" onerror="javascript:this.src=\'../cdn/discuz/images/personImg.jpg\'" class="sImg db fl" width="25" height="25" alt="头像"><a href="javascript:;" class="sW fl"><span>' + username + '：</span>' + message + '</a></li>');
}