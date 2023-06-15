var specialThread = {
	haveEvent: true,
	activity: null,
	currentChoices: 0,
	formhash: '',
	uid: 0,
	tid: 0,
	fid: 0,
	pid: 0,
	init: function (json, data) {
		if (!json.Variables.special_activity) {
			return;
		}
		specialThread.formhash = json.Variables.formhash;
		specialThread.uid = json.Variables.member_uid;
		specialThread.tid = json.Variables.thread.tid;
		specialThread.fid = json.Variables.thread.fid;
		specialThread.pid = firstpId;

		specialThread.activity = json.Variables.special_activity;
		if (specialThread.activity.thumb && specialThread.activity.attachurl) {
			specialThread.activity.thumb = TOOLS.attachUrl(specialThread.activity.thumb);
			specialThread.activity.attachurl = TOOLS.attachUrl(specialThread.activity.attachurl);
		}

		data.special_activity = specialThread.activity;
		data.jsversion = JSGLOBAL.jsversion;
		return data;
	},
	bindEvent: function () {
		imageviewCommon('.dzShowImg');
		var ufield = specialThread.activity.ufield;
		var joinfield = specialThread.activity.joinfield;
		var userfield = specialThread.activity.userfield;
		var extfield = specialThread.activity.extfield;
		var basefield = specialThread.activity.basefield;
		var joinHtml = '';
		if (typeof joinfield === 'undefined') {
			return;
		}
		for (var i in ufield.userfield) {
			if (joinfield[ufield.userfield[i]].available) {
				if (joinfield[ufield.userfield[i]].choices) {
					var choices = joinfield[ufield.userfield[i]].choices.split("\n");
					joinfield[ufield.userfield[i]].choices = {};
					for (var j in choices) {
						joinfield[ufield.userfield[i]].choices[choices[j]] = choices[j];
					}
				} else if (joinfield[ufield.userfield[i]].formtype != 'text' && joinfield[ufield.userfield[i]].formtype != 'textarea') {
					if (ufield.userfield[i] == 'gender') {
						joinfield[ufield.userfield[i]].choices = {1: '男', 2: '女'};
					} else {
						joinfield[ufield.userfield[i]].formtype = 'text';
					}
				}
				joinfield[ufield.userfield[i]].require = true;
				joinfield[ufield.userfield[i]].value = userfield[ufield.userfield[i]];
				joinHtml += template.render('joinfield', joinfield[ufield.userfield[i]]);
			}
		}
		for (var i in ufield.extfield) {
			joinHtml += template.render('joinfield', {fieldid: ufield.extfield[i], title: ufield.extfield[i], formtype: "text", require: false, value: extfield ? extfield[ufield.extfield[i]] : ''});
		}
		joinHtml += template.render('joinfield', {fieldid: 'message', title: '留言', formtype: "textarea", require: false, value: basefield.message});
		specialThread.activity.joinHtml = joinHtml;
		joinHtml = template.render('joinform', specialThread.activity);
		$('#joinbox').html(joinHtml);
		cancelHtml = '';
		cancelHtml += template.render('joinfield', {fieldid: 'message', title: '留言', formtype: "textarea", require: false});
		specialThread.activity.cancelHtml = cancelHtml;
		cancelHtml = template.render('cancelform', specialThread.activity);
		$('#cancelbox').html(cancelHtml);

		$('dd.f16.checkbox input[type=checkbox]').click(function (event) {
			var name = $(this)[0].name;
			var rule = '.c_' + name;
			if ($(this)[0].checked) {
				$(this).hide();
				$(this).parent().children('span').show();
				$(this).parent().parent().addClass('on');
			} else {
				$(this).show();
				$(this).parent().children('span').hide();
				$(this).parent().parent().removeClass('on');
			}
		});
		$('dd.f16.radio input[type=radio]').click(function (event) {
			var name = $(this)[0].name;
			var rule = '.c_' + name;
			$(rule).each(function () {
				$(this).removeClass('on');
			});
			$(rule + ' input').each(function () {
				$(this).show();
			});
			$(rule + ' span').each(function () {
				$(this).hide();
			});
			$(this).hide();
			$(this).parent().children('span').show();
			$(this).parent().parent().addClass('on');
		});

		$('#joinBtn').click(function (event) {
			if (specialThread.uid == "0") {
				FUNCS.jumpToLoginPage('a=viewthread&tid=' + specialThread.tid);
				return;
			}
			$('#joinbox').show();
			$('#activitybtn').hide();
		});
		$('#cancelBtn').click(function (event) {
			$('#cancelbox').show();
			$('#activitybtn').hide();
		});
		$('#submitJoinBtn').click(function (event) {
			$('#actjoinformhash').val(specialThread.formhash);
			var postUrl = API_URL + 'module=forummisc&version=4&t=output&action=activityapplies&fid=' + specialThread.fid + '&tid=' + specialThread.tid + '&pid=' + specialThread.pid + '&activitysubmit=yes';
			TOOLS.dpost(postUrl, $('#actjoinform').serialize(),
				function (re) {
					TOOLS.showTips("报名成功", true);
					setTimeout(function () {
						location.reload();
					}, 300);
				},
				function (error) {
					TOOLS.hideLoading();
					TOOLS.showTips(error.messagestr, true);
					$('#submitJoinBtn').disabled = false;
				}
			);
			$('#submitJoinBtn').disabled = true;
		});
		$('#cancelJoinBtn').click(function (event) {
			$('#actcancelformhash').val(specialThread.formhash);
			var postUrl = API_URL + 'module=forummisc&version=4&t=output&action=activityapplies&fid=' + specialThread.fid + '&tid=' + specialThread.tid + '&pid=' + specialThread.pid + '&activitycancel=yes';
			TOOLS.dpost(postUrl, $('#actcancelform').serialize(),
				null,
				function (re) {
					TOOLS.showTips("已取消报名", true);
					setTimeout(function () {
						location.reload();
					}, 300);
				}
			);
			$('#calcenJoinBtn').disabled = true;
		});
		$('#closeJBtn').click(function (event) {
			$('#joinbox').hide();
			$('#activitybtn').show();
			window.scrollTo(0, document.body.scrollTop - $('#joinbox').offsetTop);
		});
		$('#closeCBtn').click(function (event) {
			$('#cancelbox').hide();
			$('#activitybtn').show();
		});
	}
};

template.helper('inArray', function (search, array) {
	return array && TOOLS.in_array(search, array.split(','));
});