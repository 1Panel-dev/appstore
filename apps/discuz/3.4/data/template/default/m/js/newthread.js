var jq = jQuery.noConflict();
var $ = jq;
var fid = TOOLS.getQuery('fid');
SITE_ID = TOOLS.getQuery('siteId') == null ? SITE_ID : TOOLS.getQuery('siteId');
uid = TOOLS.getQuery('uid');
fid = TOOLS.getQuery('fid');
formhash = TOOLS.getQuery('formhash');
forumname = TOOLS.getQuery('forumname');
uploadHash = '';
threadtypes = null;
threadtypeHtml = '';
uploadImage = false;

var newThread = {
	maxUpload: 8,
	uploadInfo: {},
	uploadQueue: [],
	previewQueue: [],
	xhr: {},
	isBusy: false,
	userName: '',
	fid: 0,
	touchEnd: function (e, offset) {
		document.ontouchmove = function (e) {
			return true;
		};
	},
	countUpload: function () {

		var num = 0;
		jq.each(newThread.uploadInfo, function (i, n) {
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
		var conf = {}, file = newThread.uploadInfo[id].file;

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

					newThread.uploadInfo[id].file = uploadBase64;//e.target.result;
					newThread.uploadInfo[id].filename = file.name;
					jq('#li' + id).find('img').attr('src', uploadBase64);
					newThread.uploadQueue.push(id);
				};
				img.src = ImageCompresser.getFileObjectURL(file);
			} else {
				uploadBase64 = result;
				if (uploadBase64.indexOf('data:image') < 0) {
					TOOLS.dialog({content: '上传照片格式不支持', autoClose: true});
					jq('#li' + id).remove();
					return false;
				}
				newThread.uploadInfo[id].file = uploadBase64;//e.target.result;
				newThread.uploadInfo[id].filename = file.name;
				jq('#li' + id).find('img').attr('src', uploadBase64);
				newThread.uploadQueue.push(id);
			}

		};
		reader.readAsBinaryString(newThread.uploadInfo[id].file);
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
			enc1 = newThread._keys.indexOf(input.charAt(i++));
			enc2 = newThread._keys.indexOf(input.charAt(i++));
			enc3 = newThread._keys.indexOf(input.charAt(i++));
			enc4 = newThread._keys.indexOf(input.charAt(i++));
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

		if (!newThread.uploadInfo[id]) {
			return false;
		}

		var uploadUrl = API_URL + 'siteid=' + SITE_ID + '&version=4&module=forumupload&fid=' + fid + '&type=image&simple=1';
		var progressHtml = '<div class="progress" id="progress' + id + '"><div class="proBar" style="width:0%;"></div></div>';
		jq('#li' + id).find('.maskLay').after(progressHtml);

		var formData = new FormData();
		formData.append('Filedata', newThread.uploadInfo[id].file);
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
			newThread.isBusy = false;
			newThread.uploadInfo[id].isDone = true;
			newThread.xhr[id] = null;
		};

		var complete = function (e) {
			donePic(id);
			var attach = e.target.response.split('|');
			if (attach.length > 3 && parseInt(attach[2]) > 0) {
				var input = '<input type="hidden" id="attachnew' + id + '" name="attachnew[' + attach[2] + '][description]" value="' + attach[2] + '">';
				jq('#newthread').append(input);
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
			newThread.isBusy = false;
			newThread.uploadInfo[id].isDone = true;
			TOOLS.dialog({content: '网络断开，请稍后重新操作', autoClose: true});
			removePic(id);
		};

		var abort = function () {
			newThread.isBusy = false;
			newThread.uploadInfo[id].isDone = true;
			TOOLS.dialog({content: '上传已取消', autoClose: true});
			removePic(id);
		};

		newThread.xhr[id] = new XMLHttpRequest();
		var boundary = '------multipartformboundary' + (new Date).getTime();
		var s = {
			uploadformdata: {uid: uid, hash: uploadHash},
			uploadinputname: 'Filedata'
		};
		var picdata = newThread.uploadInfo[id].file.replace(/data:.+;base64,/, '');
		if (typeof atob == 'function') {
			picdata = atob(picdata);
		} else {
			picdata = newThread.base64decode(picdata);
		}
		var builder = newThread.getbuilder(s, newThread.uploadInfo[id].filename, picdata, boundary);
		newThread.xhr[id].open("POST", uploadUrl, true);
		newThread.xhr[id].setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
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
		newThread.xhr[id].sendAsBinary(builder);
		newThread.xhr[id].onerror = function (e) {
			failed();
		};
		newThread.xhr[id].onload = function (e) {
			complete(e);
		};
		newThread.xhr[id].abort = function (e) {
			abort();
		};
		newThread.xhr[id].onsendstream = function (e) {
			progress(e);
		};

	},
	initUpload: function () {
		if (uploadImage) {
			TOOLS.uploadCompatible('#addPic', '#uploadnotice');
			jq('#addPic').on('click', function () {
				if (!newThread.isBusy) {
					jq('#uploadFile').click();
				} else {
					TOOLS.dialog({content: '图片上传中，请稍后添加', autoClose: true});
					return false;
				}
			});
		} else {
			jq('#addPic').on('click', function () {
				TOOLS.dialog({content: '当前版块不支持图片上传', autoClose: true});
			});
			jq('#uploadnotice').html('当前版块不支持图片上传');
		}

		jq('.warp').on('change', '#uploadFile', function (e) {

			e = e || window.event;
			var fileList = e.target.files;
			if (!fileList.length) {
				return false;
			}

			for (var i = 0; i < fileList.length; i++) {
				if (newThread.countUpload() >= newThread.maxUpload) {
					TOOLS.dialog({content: '你最多只能上传8张照片', autoClose: true});
					break;
				}

				var file = fileList[i];

				if (!newThread.checkPicSize(file)) {
					TOOLS.dialog({content: '图片体积过大', autoClose: true});
					continue;
				}
				if (!newThread.checkPicType(file)) {
					TOOLS.dialog({content: '上传照片格式不支持', autoClose: true});
					continue;
				}

				var id = Date.now() + i;
				newThread.uploadInfo[id] = {
					file: file,
					filename: '',
					isDone: false
				};

				var html = '<li id="li' + id + '"><div class="photoCut"><img src="' + DATA_DIR + '/images/defaultImg.jpg" class="attchImg" alt="photo"></div>' +
					'<div class="maskLay"></div>' +
					'<a href="javascript:;" class="cBtn cBtnOn pa db" title="" _id="' + id + '">关闭</a></li>';
				jq('#addPic').before(html);

				newThread.previewQueue.push(id);
			}

			if (newThread.countUpload() >= newThread.maxUpload) {
				jq('#addPic').hide();
			}

			jq(this).val('');
		});

		jq('.photoList').on('click', '.cBtn', function () {
			var id = jq(this).attr('_id');
			if (newThread.xhr[id]) {
				newThread.xhr[id].abort();
			}
			jq('#li' + id).remove();
			jq('#input' + id).remove();
			jq('#attachnew' + id).remove();
			newThread.uploadInfo[id] = null;

			if (newThread.countUpload() < newThread.maxUpload) {
				jq('#addPic').show();
			}
		});

		setInterval(function () {
			setTimeout(function () {
				if (newThread.previewQueue.length) {
					var jobId = newThread.previewQueue.shift();
					newThread.uploadPreview(jobId);
				}
			}, 1);
			setTimeout(function () {
				if (!newThread.isBusy && newThread.uploadQueue.length) {
					var jobId = newThread.uploadQueue.shift();
					newThread.isBusy = true;
					newThread.createUpload(jobId);
				}
			}, 10);
		}, 300);
	},
	init: function () {
		newThread.fid = TOOLS.getQuery('fid');
		jQuery('#subject').focus();

		TOOLS.getCheckInfo(function (re) {
		});

		TOOLS.initTouch({obj: jq('.warp')[0], end: newThread.touchEnd});
		var opts = {
			dataType: 'json',
			success: function (re) {
				if (parseInt(re.Version) == 1 && parseInt(re.Variables.allowperm.allowpost) == 1) {
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

					newThread.initForm();

					var opts = {
						dataType: 'json',
						success: function (res) {
							TOOLS.hideLoading();
							if (parseInt(res.Version) == 1) {
								for (var i in res.Variables.forums) {
									if (parseInt(res.Variables.forums[i].fid) == parseInt(fid)) {
										threadtypes = res.Variables.forums[i].threadtypes;
										forumname = res.Variables.forums[i].name;
										jq('#forumname').html('所在版块：' + (forumname ? forumname : ''));
										break;
									}
								}

								if (threadtypes && typeof threadtypes.required != "undefined") {
									jq.each(threadtypes.types, function (key, value) {
										value = TOOLS.stripCode(value);
										threadtypeHtml += '<a class="f12" key="' + key + '">' + value + '</a>&nbsp;';
									});

									jq("#span_typeid .customTag").html(threadtypeHtml);
									jq("#span_typeid .customTag a").on("click", function (event) {
										jq('#typeid').val(jq(this).attr("key"));
										jq("#span_typeid .customTag a").removeClass('on');
										jq(this).addClass('on');
									});
									jq("#span_typeid").show();
								}

								newThread.userName = re.Variables.member_username;

							}
						}
					};
					TOOLS.dget(API_URL + 'siteid=' + SITE_ID + '&module=forumnav&version=1', null, opts.success, function (error) {
						TOOLS.hideLoading();
						TOOLS.showTips(error.messagestr, true);
					}
					);

					var newthread_extraInfo = TOOLS.hook(re, 'newthread_extraInfo');
					newthread_extraInfo = TOOLS.stripCode(newthread_extraInfo);
					jq('#customHtml').append(newthread_extraInfo);

				} else {
					TOOLS.dialog({
						isMask: true,
						content: '当前账户暂无发帖权限',
						isShowMask: true,
						cancelValue: '确定',
						cancel: function () {
							newThread.goBack();
						}
					});
				}
			}
		};
		jq("#newthread").attr("action", API_URL + 'siteid=' + SITE_ID + '&version=4&module=newthread&fid=' + fid + '&topicsubmit=yes');
		var original = API_URL + 'siteid=' + SITE_ID + '&module=newthread&fid=' + fid + '&submodule=checkpost&version=1';
		TOOLS.showLoading();
		TOOLS.dget(original, null, opts.success, function (error) {
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
			if(error.messageval=='postperm_login_nopermission_mobile//1'){
				TOOLS.openLoginPage(location.href, 1000);
			}

			jq('#submitButton').bind('click', function () {
				TOOLS.showTips('无法发送,请取消并检查网络后重进', true);
				return false;
			});

			jq('.cancelBtn').bind('click', function () {
				newThread.goBack();
			});

		}
		);

		emotion.init();

		jQuery(".expreSelect").click(function () {
			emotion.show();
			jQuery(".photoList").hide();
		});
		jQuery(".photoSelect").click(function () {
			emotion.hide();
			jQuery(".photoList").show();
		});

		jQuery('.backBtn').click(function () {
			TOOLS.openNewPage('?a=index&fid=' + newThread.fid);
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
				localStorage.removeItem(newThread.storageContentKey);
				localStorage.removeItem(newThread.storageSubjectKey);
				setTimeout(function () {
					TOOLS.openNewPage('?a=viewthread&tid=' + re.Variables.tid);
				}, 300);
			}
		};

		var postUrl = jq("#newthread").attr("action") + extraUrl;
		TOOLS.dpost(postUrl, jq('#newthread').serialize() + extraPost, sendopt.success,
			function (error) {
				TOOLS.hideLoading();
				if (error.messageval == 'post_sort_isnull') {
					TOOLS.showTips('手机上暂不支持发表分类信息，请移步PC进行操作', true);
					return;
				}
				if (error.messageval == 'submit_seccode_invalid') {
					secure.isChecked = false;
					newThread.checkSecure(1);
				} else {
					TOOLS.showTips(error.messagestr, true);
				}
				jq('#submitButton').disabled = false;
			}
		);
		jq('#submitButton').disabled = true;
		TOOLS.showLoading(null, '正在发帖中...', false);
	},
	checkSecure: function (force) {
		secure.checkSecure({
			"success": function () {
				if (secure.isNeedSecure) {
					var opts = {
						'seccode': secure.seccode,
						'secqaa': secure.secqaa,
						success: function (seccoderesult) {
							var extraPost = "&sechash=" + secure.sechash
								+ "&seccodeverify=" + seccoderesult.seccodeverify
								+ "&secanswer=" + seccoderesult.secanswer;
							newThread.sendPost('', extraPost);
						}
					};
					secure.showSecure(opts);
					return false;
				} else {
					newThread.sendPost('', '');
				}
			},
			"error": function (error) {
			}
		}, 'post', force);
	},
	initForm: function () {
		newThread.storageContentKey = uid + "thread_content";
		newThread.storageSubjectKey = uid + "thread_subject";

		jq('#message').val(localStorage.getItem(newThread.storageContentKey));
		jq('#subject').val(localStorage.getItem(newThread.storageSubjectKey));
		timer = setInterval(function () {

			if (TOOLS.mb_strlen(jq('textarea[name="message"]').val()) > 500 * 2) {//500个汉字
				jq('textarea[name="message"]').val(jq('textarea[name="message"]').val().substring(0, 500 * 2));
			}

			TOOLS.strLenCalc(jq('textarea[name="message"]')[0], 'pText', 500 * 2);

			if (jq('textarea[name="message"]').val() || jq('#subject').val()) {
				localStorage.removeItem(newThread.storageContentKey);
				localStorage.setItem(newThread.storageContentKey, jq('#message').val());
				localStorage.removeItem(newThread.storageSubjectKey);
				localStorage.setItem(newThread.storageSubjectKey, jq('#subject').val());
			}
		}, 500);



		jq('#submitButton').bind('click', function () {
			if (!newThread.checkForm()) {
				return false;
			}
			if (newThread.countUpload()) {
				jq('#allowphoto').val(1);
			}
			var os = OS.toLowerCase();

			jQuery('input[name="mobiletype"]').val(5);

			newThread.checkSecure();
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
				newThread.goBack();
			}
		});
		newThread.initUpload();
		newThread.initModal();
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

		jq.each(newThread.uploadInfo, function (i, n) {
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
		TOOLS.openNewPage("?a=index&f=" + F + "&fid=" + newThread.fid + "&siteid=" + SITE_ID);
	}

};

newThread.init();
window.onload = function () {
	var comment = document.getElementById("submitButton");
	comment.ontouchstart = function () {
		this.className = "sendBtn sendOn c1 db";
	};
	comment.ontouchend = function () {
		this.className = "sendBtn c1 db";
	};
};