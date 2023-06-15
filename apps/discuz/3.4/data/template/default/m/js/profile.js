var ucenterurl = '';
var discuzversion = '';
var formhash = '';
var infoDatas = {
	'threads': ['主题数'],
	'posts': ['回帖数'],
	'credits': ['积分']
};
var dataLoaded = function (data) {
	var headerHtml = '';
	formhash = data.Variables.formhash;
	data.Variables.ucenterurl = ucenterurl;
	if (parseInt(data.Variables.space.groupiconid) > 0) {
		data.Variables.space.authorLv = data.Variables.space.groupiconid;
	} else {
		data.Variables.space.avatarHtml = '';
		if (data.Variables.space.groupiconid == 'admin') {
			data.Variables.space.avatarHtml = '<span class="statusBg1 brBig db c2 pa"><i class="iconStationmaster commF f11"></i></span>';
		} else if (data.Variables.space.groupiconid == 'user') {
			data.Variables.space.avatarHtml = '<span class="statusBg3 brBig db c2 pa"><i class="iconVUser commF f11"></i></span>';
		}
	}
	var hook_author_info = TOOLS.hook(data, 'profile_authorInfo');
	if (hook_author_info !== null && typeof hook_author_info === 'string') {
		data.Variables.hook_author_info = hook_author_info;
	}

	headerHtml += template.render('headerTpl', data);
	$('#header').html(headerHtml);
	TOOLS.hideLoading();

	if (data.Variables.notice.newmypost != '0') {
		$('#mynotice .db.numP.pf').show();
		$('#mynotice .db.numP.pf').html(data.Variables.notice.newmypost);
	}
	if (data.Variables.member_uid == data.Variables.space.uid) {
		$('#usernav').show();
		if (data.Variables.notice.newpm != '0') {
			$('#mypm .db.numP.pf').show();
			$('#mypm .db.numP.pf').html(data.Variables.notice.newpm);
		}
	}
	if (data.Variables.space.upgradecredit) {
		infoDatas['upgradecredit'] = ['离升级还需'];
		data.Variables.space.upgradecredit = '<span class="upgradecredit"><span>' + data.Variables.space.upgradecredit + '</span>' +
			'<em class="box"><em class="percent" style="width:' + (data.Variables.space.upgradeprogress / 2) + 'px"></em></em></span>';
	}
	for (i in data.Variables.extcredits) {
		infoDatas['extcredits' + i] = [data.Variables.extcredits[i]['title'], data.Variables.extcredits[i]['unit']];
	}

	var hook_extra_info = TOOLS.hook(data, 'profile_extraInfo');
	if (hook_extra_info !== null && typeof hook_extra_info === 'object') {
		customHtml = '';
		for (key in hook_extra_info) {
			customHtml += template.render('customTpl', hook_extra_info[key]);
		}
		if (customHtml !== '') {
			$('#customnav').html(customHtml);
			$('#customnav').show();
		}
	}

	groupHtml = '';
	groupHtml += template.render('groupTpl', {'name': '用户组', 'value': data.Variables.space.group.grouptitle});
	if (data.Variables.space.admingroup.grouptitle) {
		groupHtml += template.render('groupTpl', {'name': '管理组', 'value': data.Variables.space.admingroup.grouptitle});
	}
	groupHtml += template.render('groupTpl', {'name': '注册时间', 'value': data.Variables.space.regdate});
	$('#groupnav').html(groupHtml);

	infoHtml = '';
	for (key in infoDatas) {
		var value = data.Variables.space[key] || '';
		var unit = infoDatas[key][1] || '';
		infoHtml += template.render('infoTpl', {'name': infoDatas[key][0], 'value': value + ' ' + unit});
	}
	$('#infonav').html(infoHtml);
};

var bindEvent = function (data) {
	$("#forumlist").append('版块');
	if (data.Variables.member_uid == data.Variables.space.uid) {
		$('#switchMember').on('click', function () {
			TOOLS.dget(API_URL + "module=login&mlogout=yes&version=4&hash=" + data.Variables.formhash, null, function() {
				TOOLS.openNewPage('?a=index');
			}, null, 'text/plain', false, false);
		});
		$('#switchNav').show();
	} else {
		$('#profileNav').show();
	}

	$('#mynotice').on('click', function () {
		TOOLS.openNewPage('?a=mynotice');
	});
	$('#mypm').on('click', function () {
		TOOLS.openNewPage('?a=mypm');
	});
	$('#sendpm').on('click', function () {
		TOOLS.openNewPage('?a=mypm&touid=' + data.Variables.space.uid);
	});
	$('#mythread').on('click', function () {
		TOOLS.openNewPage('?a=mythread&ac=thread');
	});
	$('#mypost').on('click', function () {
		TOOLS.openNewPage('?a=mythread&ac=reply');
	});
	$(".backBtn").click(function () {
		TOOLS.pageBack('?a=index');
	});
	$("#forumlist").click(function () {
		TOOLS.openNewPage('?a=forumlist');
	});
};

var initPage = function () {
	TOOLS.showLoading();
	TOOLS.getCheckInfo(function (re) {
		ucenterurl = re.ucenterurl;
		discuzversion = re.discuzversion;
	});
	var user = '';
	if (TOOLS.getQuery('uid')) {
		user = '&uid=' + TOOLS.getQuery('uid');
	} else if (TOOLS.getQuery('username')) {
		user = '&username=' + encodeURIComponent(TOOLS.getQuery('username'));
	}
	TOOLS.dget(API_URL + "module=profile&version=4" + user, null,
		function (json) {
			dataLoaded(json);
			bindEvent(json);
		},
		function (error) {
			TOOLS.showError('.warp', error.messagestr, function () {
				location.reload();
			});
			TOOLS.hideLoading();
		}
	);
};

initPage();