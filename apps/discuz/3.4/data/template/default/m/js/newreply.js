jq = jQuery;
var newReply = {
	maxUpload: 8,
	uploadInfo: {},
	uploadQueue: [],
	previewQueue: [],
	xhr: {},
	isBusy: false,
	userName: '',
	fid: 0,
	tid: 0,
	touchEnd: function (e, offset) {
		document.ontouchmove = function (e) {
			return true;
		};
	},
	countUpload: function () {

		var num = 0;
		jq.each(newReply.uploadInfo, function (i, n) {
			if (n) {
				++num;
			}
		});

		return num;
	},
	getbuilder: function (s, filename, filedata, boundary) {
		var dashdash = '--',
			crlf = '\r\n',
			builder = '';

		for (var i in s.uploadformdata) {
			builder += dashdash;
			builder += boundary;
			builder += crlf;
			builder += 'Content-Disposition: form-data; name="' + i + '"';
			builder += crlf;
			builder += crlf;
			builder += s.uploadformdata[i];
			builder += crlf;
		}

		builder += dashdash;
		builder += boundary;
		builder += crlf;
		builder += 'Content-Disposition: form-data; name="' + s.uploadinputname + '"';
		builder += '; filename="' + filename + '"';
		builder += crlf;

		builder += 'Content-Type: application/octet-stream';
		builder += crlf;
		builder += crlf;

		builder += filedata;
		builder += crlf;

		builder += dashdash;
		builder += boundary;
		builder += dashdash;
		builder += crlf;
		return builder;
	},
	uploadPreview: function (id) {

		var reader = new FileReader();
		var uploadBase64;
		var conf = {}, file = newReply.uploadInfo[id].file;

		reader.onload = function (e) {
			var result = this.result;

			if (file.type == 'image/jpeg') {
				var jpg = new JpegMeta.JpegFile(result, file.name);
				if (jpg.tiff && jpg.tiff.Orientation) {
					conf = jq.extend(conf, {
						orien: jpg.tiff.Orientation.value
					});
				}
			}

			if (ImageCompresser.support()) {
				var img = new Image();
				img.onload = function () {
					try {
						uploadBase64 = ImageCompresser.getImageBase64(this, conf);
					} catch (e) {
						TOOLS.dialog({content: '压缩图片失败', autoClose: true});
						jq('#li' + id).remove();
						return false;
					}
					if (uploadBase64.indexOf('data:image') < 0) {
						TOOLS.dialog({content: '上传照片格式不支持', autoClose: true});
						jq('#li' + id).remove();
						return false;
					}

					newReply.uploadInfo[id].file = uploadBase64;//e.target.result;
					newReply.uploadInfo[id].filename = file.name;
					jq('#li' + id).find('img').attr('src', uploadBase64);
					newReply.uploadQueue.push(id);
				};
				img.src = ImageCompresser.getFileObjectURL(file);
			} else {
				uploadBase64 = result;
				if (uploadBase64.indexOf('data:image') < 0) {
					TOOLS.dialog({content: '上传照片格式不支持', autoClose: true});
					jq('#li' + id).remove();
					return false;
				}
				newReply.uploadInfo[id].file = uploadBase64;//e.target.result;
				newReply.uploadInfo[id].filename = file.name;
				jq('#li' + id).find('img').attr('src', uploadBase64);
				newReply.uploadQueue.push(id);
			}

		};
		reader.readAsBinaryString(newReply.uploadInfo[id].file);
	},
	_keys: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=', /*用于BASE64转码*/

	base64decode: function (input) {
		var output = '';
		var chr1, chr2, chr3 = '';
		var enc1, enc2, enc3, enc4 = '';
		var i = 0;
		if (input.length % 4 != 0) {
			return '';
		}
		var base64test = /[^A-Za-z0-9\+\/\=]/g;
		if (base64test.exec(input)) {
			return '';
		}
		do {
			enc1 = newReply._keys.indexOf(input.charAt(i++));
			enc2 = newReply._keys.indexOf(input.charAt(i++));
			enc3 = newReply._keys.indexOf(input.charAt(i++));
			enc4 = newReply._keys.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output += String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output += String.fromCharCode(chr3);
			}
			chr1 = chr2 = chr3 = '';
			enc1 = enc2 = enc3 = enc4 = '';
		} while (i < input.length);
		return output;
	},
	createUpload: function (id) {

		if (!newReply.uploadInfo[id]) {
			return false;
		}

		var uploadUrl = API_URL + 'siteid=' + SITE_ID + '&version=4&module=forumupload&fid=' + this.fid + '&type=image&simple=1';
		var progressHtml = '<div class="progress" id="progress' + id + '"><div class="proBar" style="width:0%;"></div></div>';
		jq('#li' + id).find('.maskLay').after(progressHtml);

		var formData = new FormData();
		formData.append('Filedata', newReply.uploadInfo[id].file);
		formData.append('uid', uid);
		formData.append('hash', uploadHash);

		var progress = function (e) {
			if (e.target.response) {
				var result = jq.parseJSON(e.target.response);
				if (result.errCode != 0) {
					TOOLS.dialog({content: '网络不稳定，请稍后重新操作', autoClose: true});
					removePic(id);
				}
			}

			var progress = jq('#progress' + id).find('.proBar');
			if (e.total == e.loaded) {
				var percent = 100;
			} else {
				var percent = 100 * (e.loaded / e.total);
			}
			if (percent > 100) {
				percent = 100;
			}
			progress.css('width', percent + '%');

			if (percent == 100) {
				jq('#li' + id).find('.maskLay').remove();
				jq('#li' + id).find('.progress').remove();
			}
		};

		var removePic = function (id) {
			donePic(id);
			jq('#li' + id).remove();
		};

		var donePic = function (id) {
			newReply.isBusy = false;
			newReply.uploadInfo[id].isDone = true;
			newReply.xhr[id] = null;
		};

		var complete = function (e) {
			donePic(id);
			var attach = e.target.response.split('|');
			if (attach.length > 3 && parseInt(attach[2]) > 0) {
				var input = '<input type="hidden" id="attachnew' + id + '" name="attachnew[' + attach[2] + '][description]" value="" class="attachhide">';
				jq('#replyForm').append(input);
				jq('#li' + id).find('.maskLay').remove();
				jq('#li' + id).find('.progress').remove();
			} else {
				if (parseInt(attach[1]) == 3) {
					TOOLS.dialog({content: '图片过大, 应小于 ' + attach[4] / 1024 + ' KB', autoClose: true});
				} else {
					TOOLS.dialog({content: '网络不稳定，请稍后重新操作', autoClose: true});
				}
				removePic(id);
			}
		};

		var failed = function () {
			newReply.isBusy = false;
			newReply.uploadInfo[id].isDone = true;
			TOOLS.dialog({content: '网络断开，请稍后重新操作', autoClose: true});
			removePic(id);
		};

		var abort = function () {
			newReply.isBusy = false;
			newReply.uploadInfo[id].isDone = true;
			TOOLS.dialog({content: '上传已取消', autoClose: true});
			removePic(id);
		};

		newReply.xhr[id] = new XMLHttpRequest();
		var boundary = '------multipartformboundary' + (new Date).getTime();
		var s = {
			uploadformdata: {uid: uid, hash: uploadHash},
			uploadinputname: 'Filedata'
		};
		var picdata = newReply.uploadInfo[id].file.replace(/data:.+;base64,/, '');
		if (typeof atob == 'function') {
			picdata = atob(picdata);
		} else {
			picdata = newReply.base64decode(picdata);
		}
		var builder = newReply.getbuilder(s, newReply.uploadInfo[id].filename, picdata, boundary);
		newReply.xhr[id].open("POST", uploadUrl, true);
		newReply.xhr[id].setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
		if (!XMLHttpRequest.prototype.sendAsBinary) {
			XMLHttpRequest.prototype.sendAsBinary = function (datastr) {
				function byteValue(x) {
					return x.charCodeAt(0) & 0xff;
				}
				var ords = Array.prototype.map.call(datastr, byteValue);
				var ui8a = new Uint8Array(ords);
				this.send(ui8a.buffer);
			};
		}
		newReply.xhr[id].sendAsBinary(builder);
		newReply.xhr[id].onerror = function (e) {
			failed();
		};
		newReply.xhr[id].onload = function (e) {
			complete(e);
		};
		newReply.xhr[id].abort = function (e) {
			abort();
		};
		newReply.xhr[id].onsendstream = function (e) {
			progress(e);
		};

	},
	initUpload: function () {
		if (uploadImage) {
			TOOLS.uploadCompatible('#addPic', '#uploadnotice');
			jq('#addPic').on('click', function () {
				if (!newReply.isBusy) {
					jq('#uploadFile').click();
				} else {
					TOOLS.dialog({content: '图片上传中，请稍后添加', autoClose: true});
					return false;
				}
			});
			jq('#message').focus();
		} else {
			jq('#addPic').on('click', function () {
				TOOLS.dialog({content: '当前版块不支持图片上传', autoClose: true});
			});
			jq('#uploadnotice').html('当前版块不支持图片上传');
		}

		jq('.popLayer').on('change', '#uploadFile', function (e) {

			e = e || window.event;
			var fileList = e.target.files;
			if (!fileList.length) {
				return false;
			}

			for (var i = 0; i < fileList.length; i++) {
				if (newReply.countUpload() >= newReply.maxUpload) {
					TOOLS.dialog({content: '你最多只能上传8张照片', autoClose: true});
					break;
				}

				var file = fileList[i];

				if (!newReply.checkPicSize(file)) {
					TOOLS.dialog({content: '图片体积过大', autoClose: true});
					continue;
				}
				if (!newReply.checkPicType(file)) {
					TOOLS.dialog({content: '上传照片格式不支持', autoClose: true});
					continue;
				}

				var id = Date.now() + i;
				newReply.uploadInfo[id] = {
					file: file,
					filename: '',
					isDone: false
				};

				var html = '<li id="li' + id + '"><div class="photoCut"><img src="' + DATA_DIR + '/images/defaultImg.jpg" class="attchImg" alt="photo"></div>' +
					'<div class="maskLay"></div>' +
					'<a href="javascript:;" class="cBtn cBtnOn pa db" title="" _id="' + id + '">关闭</a></li>';
				jq('#addPic').before(html);

				newReply.previewQueue.push(id);
			}

			if (newReply.countUpload() >= newReply.maxUpload) {
				jq('#addPic').hide();
			}

			jq(this).val('');
		});

		jq('.photoList').on('click', '.cBtn', function () {
			var id = jq(this).attr('_id');
			if (newReply.xhr[id]) {
				newReply.xhr[id].abort();
			}
			jq('#li' + id).remove();
			jq('#input' + id).remove();
			jq('#attachnew' + id).remove();
			newReply.uploadInfo[id] = null;

			if (newReply.countUpload() < newReply.maxUpload) {
				jq('#addPic').show();
			}
		});

		setInterval(function () {
			setTimeout(function () {
				if (newReply.previewQueue.length) {
					var jobId = newReply.previewQueue.shift();
					newReply.uploadPreview(jobId);
				}
			}, 1);
			setTimeout(function () {
				if (!newReply.isBusy && newReply.uploadQueue.length) {
					var jobId = newReply.uploadQueue.shift();
					newReply.isBusy = true;
					newReply.createUpload(jobId);
				}
			}, 10);
		}, 300);
	},
	init: function () {
		newReply.fid = TOOLS.getQuery('fid');

		if (!newReply.fid && fId) {
			newReply.fid = fId;
		}
		if (replyCommenttId) {
			newReply.tid = replyCommenttId;
		}
		if (!newReply.tid && tId) {
			newReply.tid = tId;
		}
		TOOLS.initTouch({obj: jq('.warp')[0], end: newReply.touchEnd});
		var opts = {
			dataType: 'json',
			success: function (re) {
				if (parseInt(re.Version) == 1 && parseInt(re.Variables.allowperm.allowreply) == 1) {
					formhash = re.Variables.formhash;
					jq('#formhash').val(formhash);

					uid = re.Variables.member_uid;
					uploadHash = re.Variables.allowperm.uploadhash;
					if (re.Variables.allowperm.allowupload) {
						jq.each(re.Variables.allowperm.allowupload, function (key, value) {
							if (key.indexOf('jpg') != -1 || key.indexOf('jpeg') != -1 || key.indexOf('gif') != -1 || key.indexOf('png') != -1) {
								if (parseInt(value) == -1 || parseInt(value) > 0) {
									uploadImage = true;
								}
							}
						});
					} else {
						uploadImage = false;
					}

					newReply.initForm();

					var opts = {
						dataType: 'json',
						success: function (res) {
							TOOLS.hideLoading();
							if (parseInt(res.Version) == 1) {
								newReply.userName = re.Variables.member_username;
							}
						}
					};
					TOOLS.dget(API_URL + 'siteid=' + SITE_ID + '&module=forumnav&version=1', null, opts.success, function (error) {
						TOOLS.hideLoading();
						TOOLS.showTips(error.messagestr, true);
					}
					);

					var sendreply_extraInfo = TOOLS.hook(re, 'sendreply_extraInfo');
					sendreply_extraInfo = TOOLS.stripCode(sendreply_extraInfo);
					$('#customHtml').append(sendreply_extraInfo);

				} else {
					TOOLS.dialog({
						isMask: true,
						content: '当前账户暂无发帖权限',
						isShowMask: true,
						cancelValue: '确定',
						cancel: function () {
							newReply.goBack();
						}
					});
				}
			}
		};
		var original = API_URL + 'siteid=' + SITE_ID + '&module=sendreply&fid=' + this.fid + '&tid=' + this.tid + '&submodule=checkpost&version=1';
		TOOLS.showLoading();
		TOOLS.dget(original, null, opts.success, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
			if(error.messageval=='replyperm_login_nopermission//1'){
				TOOLS.openLoginPage(location.href, 1000);
			}

			jq('#submitButton').bind('click', function () {
				TOOLS.showTips('无法发送,请取消并检查网络后重进', true);
				return false;
			});

			jq('.cancelBtn').bind('click', function () {
				newReply.goBack();
			});

		}
		);

		emotion.init();

		jQuery(".expreSelect").click(function () {
			emotion.show();
		});

		jQuery(".photoSelect").click(function () {
			emotion.hide();
		});

		jQuery("#message").focus(function () {
			jQuery('.photoList').hide();
			jQuery('.expreBox').hide();
		});

		jQuery('.backBtn').click(function () {
			TOOLS.openNewPage('?a=index&fid=' + newReply.fid);
		});

		secure.checkDzVersion();
		document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
			WeixinJSBridge.call('hideOptionMenu');
		});

	},
	sendPost: function (extraUrl, extraPost) {
		extraUrl = extraUrl || "";
		extraPost = extraPost || "";
		var sendopt = {
			success: function (re) {
				var message = re.Message.messageval;
				TOOLS.hideLoading();
				TOOLS.showTips("发帖成功", true);
				jq('#submitButton').disabled = false;
				clearInterval(timer);
				localStorage.removeItem(newReply.storageContentKey);
				localStorage.removeItem(newReply.storageSubjectKey);
				setTimeout(function () {
					newReply.goBack();
				}, 300);
			}
		};

		var postUrl = jq("#newthread").attr("action") + extraUrl;
		TOOLS.dpost(postUrl, jq('#newthread').serialize() + extraPost, sendopt.success,
			function (error) {
				TOOLS.hideLoading();
				TOOLS.showTips(error.messagestr, true);
				jq('#submitButton').disabled = false;
			}
		);
		jq('#submitButton').disabled = true;
		TOOLS.showLoading(null, '正在发帖中...', false);
	},
	initForm: function () {
		var storageContentKey = uid + "thread_content";
		var storageSubjectKey = uid + "thread_subject";

		timer = setInterval(function () {

			if (TOOLS.mb_strlen(jq('textarea[name="message"]').val()) > 140 * 2) {//140个汉字
				jq('textarea[name="message"]').val(jq('textarea[name="message"]').val().substring(0, 140 * 2));
			}

			TOOLS.strLenCalc(jq('textarea[name="message"]')[0], 'pText', 140 * 2);

			if (jq('textarea[name="message"]').val() || jq('#subject').val()) {
			}
		}, 500);



		jq('#submitButton').bind('click', function () {
			var btn = jq(this);
			if (!newReply.checkForm()) {
				return false;
			}
			if (newReply.countUpload()) {
				jq('#allowphoto').val(1);
			}
			var os = OS.toLowerCase();

			jQuery('input[name="mobiletype"]').val(5);

			secure.checkSecure({
				"success": function () {
					btn.disabled = false;
					if (secure.isNeedSecure) {
						var opts = {
							'seccode': secure.seccode,
							'secqaa': secure.secqaa,
							success: function (seccoderesult) {
								var extraPost = "&sechash=" + secure.sechash
									+ "&seccodeverify=" + seccoderesult.seccodeverify
									+ "&secanswer=" + seccoderesult.secanswer;
								newReply.sendPost('', extraPost);
							}
						};
						secure.showSecure(opts);
						return false;
					} else {
						newReply.sendPost('', '');
					}
				},
				"error": function (error) {
					btn.disabled = false;
				}
			});
			btn.disabled = true;
			return false;
		});

		jq('.cancelBtn').bind('click', function () {
			if (jq('.photoList .attchImg').length > 0) {
				var result = confirm('是否放弃当前内容?');
			} else {
				var result = true;
			}

			if (result) {
				clearInterval(timer);
			}
		});
		newReply.initUpload();
		newReply.initModal();
	},
	initModal: function () {
		jq('#submitButton').bind('touchstart', function () {
			jq(this).addClass('sendOn');
		}).bind('touchend', function () {
			jq(this).removeClass('sendOn');
		});
		jq('#cBtn').bind('touchstart', function () {
			jq(this).addClass('cancelOn');
		}).bind('touchend', function () {
			jq(this).removeClass('cancelOn');
		});
	},
	checkForm: function () {

		jq.each(newReply.uploadInfo, function (i, n) {
			if (n && !n.isDone) {
				TOOLS.dialog({content: '图片上传中，请等待', autoClose: true});
				return false;
			}
		});
		var length = TOOLS.mb_strlen(TOOLS.trim(jq('#subject').val()));
		if (length < 1) {
			TOOLS.dialog({content: '请输入标题', autoClose: true});
			return false;
		}

		length = TOOLS.mb_strlen(TOOLS.trim(jq('#message').val()));
		if (length < 15) {
			TOOLS.dialog({content: '内容过短', autoClose: true});
			return false;
		}

		if (threadtypes && parseInt(threadtypes.required) == 1 && jq('#typeid').val() == '0') {
			TOOLS.dialog({content: '请选择主题分类', autoClose: true});
			return false;
		}

		return true;
	},
	checkPicSize: function (file) {
		if (file.size > 10000000) {
			return false;
		}
		return true;
	},
	checkPicType: function (file) {
		if (file.type.indexOf('image/') == 0) {
			return true;
		} else {
			var index = file.name.lastIndexOf('.');
			var extName = file.name.substring(index + 1).toLowerCase();
			if (extName == 'jpg' || extName == 'jpeg' || extName == 'png' || extName == 'gif') {
				return true;
			}
			return false;
		}
	},
	goBack: function () {
		TOOLS.openNewPage("?a=index&f=" + F + "&fid=" + newReply.fid + "&siteid=" + SITE_ID);
	}

};

newReply.init();