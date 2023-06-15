TC.load("secure.htm");

var secure = {
	isChecked: false,
	isNeedSecure: false,
	sechash: "",
	seccode: "",
	seccodeverify: "",
	secqaa: "",
	discuzversion: "",
	checkDzVersion: function () {
		TOOLS.getCheckInfo(function (re) {
			secure.discuzversion = re.discuzversion;
		});
	},
	checkSecure: function (optReturn, type, force, appendauth) {

		if (secure.isChecked && !secure.isNeedSecure) {
			optReturn.success();
			return;
		}

		if (secure.discuzversion == "") {
			TOOLS.getCheckInfo(function (re) {
				secure.discuzversion = re.discuzversion;
				secure.getSecure(optReturn, type, force, appendauth);
			});
		} else {
			secure.getSecure(optReturn, type, force, appendauth);
		}

	},
	getSecure: function (optReturn, type, force, appendauth) {
		var checkUrl = "";
		var type = type || 'post';
		var force = force || 0;
		var appendauth = appendauth == null ? true : appendauth;

		checkUrl = API_URL + 'module=secure&type=' + type + '&version=4&secversion=4' + (force ? '&force=1' : '');

		TOOLS.dget(checkUrl, null, function (re) {
			secure.isChecked = true;
			if (!re.Variables) {
				secure.isNeedSecure = false;
				optReturn.success();
				return;
			}
			secure.sechash = re.Variables.sechash || "";
			secure.seccode = re.Variables.seccode || "";
			secure.secqaa = re.Variables.secqaa || "";
			secure.isNeedSecure = (secure.seccode != "" || secure.secqaa != "");
			optReturn.success();
		}
		, function (error) {
			optReturn.error(error);
		}, 'text/plain', false, appendauth);
	},
	showSecure: function (opt) {
		var secureHtml = template.render('secure_tpl', {"seccode": secure.seccode, "secqaa": secure.secqaa});
		if (secureHtml) {
			var secureOpts = {
				'id': "secure",
				'isHtml': true,
				'isMask': true,
				'content': secureHtml,
				'callback': function () {
					jQuery('.editTCon').hide();
					jQuery('#fwin_dialog_secure .popLayer').width("210px");
					jQuery('#fwin_dialog_secure .popLayer').find('p').hide();
					jQuery('#fwin_dialog_secure .popLayer').find('br').hide();

					jQuery('#fwin_mask_secure').on('click', function (e) {
						TOOLS.hideDialog({id: secureOpts.id});
					});

					jQuery('#refreshSecure').on('click', function (event) {
						var optreturn = {
							"success": function () {
								jQuery('#secureimg').attr('src', secure.seccode);
								jQuery('#secqaa').html(secure.secqaa);
							},
							"error": function () {
								TOOLS.showTips('刷新失败', true);
							}
						};
						secure.getSecure(optreturn);
						event.stopPropagation();
					});

					jQuery('#sendInSecure').on('click', function (event) {
						var result = {
							seccodeverify: encodeURIComponent(jQuery('#secure').val() || ""),
							secanswer: encodeURIComponent(jQuery('#secanswer').val() || "")
						};
						opt.success(result);
						TOOLS.hideDialog({id: secureOpts.id});
						event.stopPropagation();
					});

				}
			};

			TOOLS.dialog(secureOpts);
		}
	}
};