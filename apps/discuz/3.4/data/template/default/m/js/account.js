var ucenterurl = '';
var discuzversion = '';
var formhash = '';

var dataLoaded = function (data, list) {
	var headerHtml = '';
	formhash = data.Variables.formhash;
	data.Variables.ucenterurl = ucenterurl;
	var uids = '';
	var ucount = 0;
	for (i = 0; i < list.res.length; i++) {
		if (list.res[i].siteuid > 0) {
			uids += (uids !== '' ? ',' : '') + list.res[i].siteuid;
			ucount++;
		}
	}
	if (ucount > 1 && uids) {
		TOOLS.dget(API_URL + "module=profiles&version=4&uids=" + uids, null,
			function (profiles) {
				listLoaded(profiles);
			},
			function (error) {
			}
		);
	}
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

	$('#addaccount').on('click', function () {
		openid = $(this).attr("openid");
		openidsign = $(this).attr("openidsign");
		TOOLS.openNewPage(window.location.search + '&login=yes&state=backlogin');
	});

	$(".backBtn").click(function () {
		referer = TOOLS.getcookie(COOKIE_PRE + 'account_referer');
		TOOLS.pageBack(referer ? referer : '?a=profile&_backurl=yes');
	});

	headerHtml += template.render('headerTpl', data);
	$('#header').html(headerHtml);
	TOOLS.hideLoading();
	$('#addprofile').show();
};

var listLoaded = function (data) {
	if (!data.Variables.profiles) {
		return;
	}
	profileHtml = template.render('accountTpl', data.Variables);
	$('#profiles').append(profileHtml);
};

var initPage = function () {
	TOOLS.showLoading();
	TOOLS.getCheckInfo(function (re) {
		ucenterurl = re.ucenterurl;
		discuzversion = re.discuzversion;
	});
	if (TOOLS.getQuery('referer')) {
		if (TOOLS.getQuery('referer') !== 'none') {
			TOOLS.setcookie(COOKIE_PRE + 'account_referer', TOOLS.getQuery('referer'));
		} else {
			TOOLS.setcookie(COOKIE_PRE + 'account_referer', '', -1);
		}
	}
};

initPage();