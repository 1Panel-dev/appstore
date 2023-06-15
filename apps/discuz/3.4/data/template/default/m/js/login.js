var login = {
	formhash: '',
	regname: '',
	loginUrl: '',
	postParams: '',
	extraPost: '',
	extraAuth: '',
	login: false,
	initPage: function () {
		$('#siteLogo')[0].src = SITE_INFO.siteLogo;
		$('#siteName').html(SITE_INFO.siteName);

		var url = API_URL + "version=4&module=login";
		TOOLS.dget(url, null, function (data) {
			login.formhash = data.Variables.formhash;
			login.login = data.Variables.member_uid;
			if(login.login > 0){
				TOOLS.showTips('登录成功', true);
				setTimeout(function(){
					if (TOOLS.getQuery('referer')) {
						TOOLS.openNewPage(unescape(TOOLS.getQuery('referer')));
					} else {
						TOOLS.openNewPage('?a=index');
					}
				},1000);
			}
		}, null, 'text/plain', false, false);

		TOOLS.getCheckInfo(function (re) {
			login.regname = re.regname;
			login.wsqqqconnect = re.wsqqqconnect;
			login.wsqhideregister = re.wsqhideregister;
			if (login.wsqqqconnect == '1') {
				$('.qqLoginBox').show();
			}
			if(TOOLS.isWX() && SITE_INFO.openApi.wx != undefined){
				$('.wxLoginBox').show();
			}
			if (TOOLS.isWX() && login.wsqhideregister == '1') {
				$('#toQuickLogin').hide();
			}
			login.bindEvent();
		});

		secure.checkDzVersion();

		if (TOOLS.getQuery('loginErr')) {
			var loginErr = TOOLS.getQuery('loginErr');
			if (loginErr == 1001) {
				TOOLS.showTips('此QQ账号尚未绑定，无法登录', true);
			}else if (loginErr == 2001) {
				TOOLS.showTips('此微信尚未绑定过账号<br />请用您已注册的账号登录完成绑定', true);
			}
		}

		if (TOOLS.getQuery('loginUrl')) {
			TOOLS.dajax('GET', unescape(TOOLS.getQuery('loginUrl')), null, function (r) {
				login.loginSuccess();
			}, null, 'text/plain');
		}
	},
	checkSecure: function (callbackFunc, force) {
		var force = force || 0;
		secure.checkSecure({
			"success": function () {
				if (secure.isNeedSecure) {
					var opts = {
						'seccode': secure.seccode,
						'secqaa': secure.secqaa,
						success: function (seccoderesult) {
							login.extraPost = "&sechash=" + secure.sechash
								+ "&seccodeverify=" + seccoderesult.seccodeverify
								+ "&secanswer=" + seccoderesult.secanswer;
							callbackFunc.post();
						}
					};
					secure.showSecure(opts);
					return false;
				} else {
					callbackFunc.post();
				}
			},
			"error": function (error) {
				btn.disabled = false;
			}
		}, 'login', force);
	},
	loginRequest: function () {
		var url = API_URL + "version=4&module=login";
		TOOLS.dget(url, null, function (data) {
			TOOLS.dpost(login.loginUrl, login.postParams + login.extraPost + login.extraAuth, login.callbackFunc.success, login.callbackFunc.error, 'application/x-www-form-urlencoded');
			TOOLS.showLoading(null, '正在登录');
		}, null, 'text/plain', false, false);
	},
	logoutRequest: function (call) {
		call();
	},
	loginSuccess: function () {
		TOOLS.hideLoading();
		TOOLS.showTips('登录成功', true);
		if ($('#referer').val() != '') {
			TOOLS.openNewPage($('#referer').val());
		} else {
			TOOLS.openNewPage('?a=index');
		}
	},
	callbackFunc: {
		post: function () {
			login.logoutRequest(login.loginRequest);
		},
		success: function (re) {
			login.loginSuccess();
		},
		error: function (error, data) {
			TOOLS.hideLoading();
			if (error.messageval == 'submit_seccode_invalid' || error.messageval == 'login_seccheck2') {
				secure.isChecked = false;
				if (data.Variables.auth) {
					login.extraAuth = '&auth=' + encodeURIComponent(data.Variables.auth);
				}
				login.checkSecure(login.callbackFunc, 1);
			} else {
				TOOLS.showTips(error.messagestr, true);
			}

		}
	},
	bindEvent: function () {
		$('#registerBtn').on('click', function () {
			TOOLS.openNewPage(DOMAIN + 'member.php?mod=' + login.regname + '&mobile=2');
		});
		$('.qqLogin').on('click', function () {
				var url = DOMAIN + 'connect.php?mod=login&op=init&referer=' + encodeURIComponent(location.search);
				TOOLS.openNewPage(url);
		});
		$('.wxLogin').on('click', function () {
			var url = DOMAIN + 'm/?wxlogin=yes'+ '&referer=' + encodeURIComponent(location.search);
			TOOLS.openNewPage(url);
	});
		$('#loginBtn').on('click', function () {
			login.loginUrl = API_URL + "version=4&module=login&loginsubmit=yes";
			login.postParams = $('#loginBox').serialize();
			login.extraPost = '';
			login.extraAuth = '';
			login.callbackFunc.post();
		});
		var subopts = {'id': 'questionbox'};
		$('#questionclick').on('click', function () {
			var opts = {
				'id': 'questionbox',
				'isHtml': true,
				'isMask': true,
				'content': template.render('questionselect'),
				'callback': function () {
					$('.popLayer').width("210px");
					$('.popLayer').find('p').hide();
					$('.popLayer').find('br').hide();

					$('.g-mask').on('click', function (e) {
						TOOLS.hideDialog(subopts);
						$('#questionbox').hide();
					});
					$('.questionbox a[class="select"]').on('click', function (e) {
						$('#questionid').val($(this).attr('v'));
						if ($(this).attr('v') > 0) {
							$('#questionclick').attr('value', $(this).html());
							$('#qainput').show();
						} else {
							$('#questionclick').attr('value', '');
							$('#qainput').hide();
						}
						TOOLS.hideDialog(subopts);
						$('#questionbox').hide();
					});
					$('.questionbox a[class="cancel"]').on('click', function (e) {
						TOOLS.hideDialog(subopts);
						$('#questionbox').hide();
					});
				}
			};
			TOOLS.dialog(opts);
		});
	}
};

$(function () {
	login.initPage();
});