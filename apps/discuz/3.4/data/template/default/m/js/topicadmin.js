TC.load("topicadmin.htm");

var topicAdmin = {
	opts: {},
	menuEvent: function (opts) {
		topicAdmin.opts = opts;
		var obj = '.perPop[' + topicAdmin.opts.id + ']';
		if ($(obj).html() == '') {
			if (opts.pid) {
				perPopHtml = template.render('topicadmin_menu_viewthread', {});
			} else {
				perPopHtml = template.render('topicadmin_menu_forumdisplay', {});
			}
			$(obj).html(perPopHtml);
			$(obj).show();
			$(obj + ' .ban').on('click', function (event) {
				topicAdmin.showBanDialog();
				event.stopPropagation();
			});
			$(obj + ' .del').on('click', function (event, formhash) {
				topicAdmin.showDelDialog();
				event.stopPropagation();
			});
		} else {
			if ($(obj).css('display') == 'none') {
				$(obj).show();
			} else {
				$(obj).hide();
			}
		}
	},
	showBanDialog: function () {
		var obj = '.perPop[' + topicAdmin.opts.id + ']';
		var opts = {
			'id': 'topicadmin',
			'isHtml': true,
			'isMask': true,
			'content': template.render(!topicAdmin.opts.ban ? 'topicadmin_ban' : 'topicadmin_unban'),
			'callback': function () {
				var subopts = {'id': 'topicadmin'};
				$('.popLayer').width("210px");
				$('.popLayer').find('p').hide();
				$('.popLayer').find('br').hide();

				$('.g-mask').on('click', function (e) {
					TOOLS.hideDialog(subopts);
					$(obj).hide();
				});
				$('.manageLayer a[class="ban_confirm"]').on('click', function (event) {
					var url = DOMAIN + 'api/mobile/index.php?module=topicadmin&version=4&action=banpost&modsubmit=yes';
					var post = 'banned=' + (!topicAdmin.opts.ban ? 1 : 0) + '&formhash=' + topicAdmin.opts.formhash + '&fid=' + topicAdmin.opts.fid + '&tid=' + topicAdmin.opts.tid + '&topiclist[]=' + topicAdmin.opts.pid;
					TOOLS.dpost(url, post,
						function (re) {
							$(obj).hide();
							$('.topicadminMsg[tid=' + topicAdmin.opts.tid + '][pid=' + topicAdmin.opts.pid + ']').html(!topicAdmin.opts.ban ? '已屏蔽' : '');
							TOOLS.showTips(re.Message.messagestr, true);
							TOOLS.setcookie('refreshindex', '1', 86400);
						},
						function (error) {
							TOOLS.hideLoading();
							$(obj).hide();
							TOOLS.showTips(error.messagestr, true);
						}
					);
					TOOLS.hideDialog(subopts);
					event.stopPropagation();
				});
				$('.manageLayer a[class="ban_cancel"]').on('click', function (event) {
					TOOLS.hideDialog(subopts);
					$(obj).hide();
					event.stopPropagation();
				});
			}
		};
		TOOLS.dialog(opts);
	},
	showDelDialog: function () {
		var obj = '.perPop[' + topicAdmin.opts.id + ']';
		var opts = {
			'id': 'topicadmin',
			'isHtml': true,
			'isMask': true,
			'content': template.render('topicadmin_del'),
			'callback': function () {
				var subopts = {'id': 'topicadmin'};
				$('.popLayer').width("210px");
				$('.popLayer').find('p').hide();
				$('.popLayer').find('br').hide();

				$('.g-mask').on('click', function (e) {
					TOOLS.hideDialog(subopts);
					$(obj).hide();
				});
				$('.manageLayer a[class="del_confirm"]').on('click', function (event) {
					if (topicAdmin.opts.isfirst) {
						var url = DOMAIN + 'api/mobile/index.php?module=topicadmin&version=4&action=moderate&optgroup=3&modsubmit=yes';
						var post = 'formhash=' + topicAdmin.opts.formhash + '&fid=' + topicAdmin.opts.fid + '&moderate[]=' + topicAdmin.opts.tid + '&operations[]=delete';
					} else {
						var url = DOMAIN + 'api/mobile/index.php?module=topicadmin&version=4&action=delpost&modsubmit=yes';
						var post = 'formhash=' + topicAdmin.opts.formhash + '&fid=' + topicAdmin.opts.fid + '&tid=' + topicAdmin.opts.tid + '&topiclist[]=' + topicAdmin.opts.pid;
					}
					TOOLS.dpost(url, post,
						function (re) {
							$(obj).hide();
							if (topicAdmin.opts.isfirst) {
								$('.topicadminMsg[tid=' + topicAdmin.opts.tid + ']').html('已删除');
							} else {
								$('.topicadminMsg[tid=' + topicAdmin.opts.tid + '][pid=' + topicAdmin.opts.pid + ']').html('已删除');
							}
							TOOLS.showTips(re.Message.messagestr, true);
							TOOLS.setcookie('refreshindex', '1', 86400);
						},
						function (error) {
							TOOLS.hideLoading();
							$(obj).hide();
							TOOLS.showTips(error.messagestr, true);
						}
					);
					TOOLS.hideDialog(subopts);
					event.stopPropagation();
				});
				$('.manageLayer a[class="del_cancel"]').on('click', function (event) {
					TOOLS.hideDialog(subopts);
					$(obj).hide();
					event.stopPropagation();
				});
			}
		};
		TOOLS.dialog(opts);
	}
};