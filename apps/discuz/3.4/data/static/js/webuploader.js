
var getBasePath = function() {
	var els = document.getElementsByTagName('script'),
	src;
	for (var i = 0,
	len = els.length; i < len; i++) {
		src = els[i].src || '';
		if (/webuploader[\w\-\.]*\.js/.test(src)) {
			return src.substring(0, src.lastIndexOf('/') + 1);
		}
	}
	return '';
};

var SWFUpload;
var sdCloseTime = 2;

if (SWFUpload == undefined) {
	SWFUpload = function(settings) {
		this.initSWFUpload(settings);
	};
}

SWFUpload.CURSOR = {
	ARROW: -1,
	HAND: -2
};

SWFUpload.EXT_MIME_MAP = {
	'3gp': 'video/3gpp',
	'7z': 'application/x-7z-compressed',
	'aac': 'audio/aac',
	'abw': 'application/x-abiword',
	'arc': 'application/x-freearc',
	'avi': 'video/x-msvideo',
	'apk': 'application/vnd.android.package-archive',
	'azw': 'application/vnd.amazon.ebook',
	'bin': 'application/octet-stream',
	'bmp': 'image/bmp',
	'bz': 'application/x-bzip',
	'bz2': 'application/x-bzip2',
	'bzip2': 'application/x-bzip2',
	'chm': 'application/vnd.ms-htmlhelp',
	'csh': 'application/x-csh',
	'css': 'text/css',
	'csv': 'text/csv',
	'doc': 'application/msword',
	'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'eot': 'application/vnd.ms-fontobject',
	'epub': 'application/epub+zip',
	'flv': 'video/x-flv',
	'gif': 'image/gif',
	'gz': 'application/gzip',
	'htm': 'text/html',
	'html': 'text/html',
	'ico': 'image/vnd.microsoft.icon',
	'ics': 'text/calendar',
	'jar': 'application/java-archive',
	'jpeg': 'image/jpeg',
	'jpg': 'image/jpeg',
	'js': 'text/javascript',
	'json': 'application/json',
	'jsonld': 'application/ld+json',
	'm4a': 'audio/mp4',
	'mid': 'audio/midi',
	'midi': 'audio/midi',
	'mjs': 'text/javascript',
	'mov': 'video/quicktime',
	'mkv': 'video/x-matroska',
	'mp3': 'audio/mpeg',
	'mp4': 'video/mp4',
	'mp4a': 'audio/mp4',
	'mp4v': 'video/mp4',
	'mpeg': 'video/mpeg',
	'mpkg': 'application/vnd.apple.installer+xml',
	'odp': 'application/vnd.oasis.opendocument.presentation',
	'ods': 'application/vnd.oasis.opendocument.spreadsheet',
	'odt': 'application/vnd.oasis.opendocument.text',
	'oga': 'audio/ogg',
	'ogv': 'video/ogg',
	'ogx': 'application/ogg',
	'opus': 'audio/opus',
	'otf': 'font/otf',
	'pdf': 'application/pdf',
	'php': 'application/php',
	'png': 'image/png',
	'ppt': 'application/vnd.ms-powerpoint',
	'pptx': 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'rar': 'application/x-rar-compressed',
	'rtf': 'application/rtf',
	'sh': 'application/x-sh',
	'svg': 'image/svg+xml',
	'swf': 'application/x-shockwave-flash',
	'tar': 'application/x-tar',
	'tif': 'image/tiff',
	'tiff': 'image/tiff',
	'ts': 'video/mp2t',
	'ttf': 'font/ttf',
	'txt': 'text/plain',
	'vsd': 'application/vnd.visio',
	'wav': 'audio/wav',
	'wma': 'audio/x-ms-wma',
	'wmv': 'video/x-ms-asf',
	'weba': 'audio/webm',
	'webm': 'video/webm',
	'webp': 'image/webp',
	'woff': 'font/woff',
	'woff2': 'font/woff2',
	'xhtml': 'application/xhtml+xml',
	'xls': 'application/vnd.ms-excel',
	'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'xml': 'application/xml',
	'xul': 'application/vnd.mozilla.xul+xml',
	'zip': 'application/zip'
};

SWFUpload.prototype.initSWFUpload = function(userSettings) {
	try {
		this.customSettings = {};	// A container where developers can place their own settings associated with this instance.
		this.settings = {};
		this.eventQueue = [];
		this.initSettings(userSettings);
	} catch (ex) {
		throw ex;
	}
};

SWFUpload.prototype.initSettings = function (userSettings) {
	this.ensureDefault = function(settingName, defaultValue) {
		var setting = userSettings[settingName];
		if (setting != undefined) {
			this.settings[settingName] = setting;
		} else {
			this.settings[settingName] = defaultValue;
		}
	};

	this.ensureDefault("upload_url", "");
	this.ensureDefault("file_post_name", "Filedata");
	this.ensureDefault("post_params", {});

	this.ensureDefault("file_types", "*.*");
	this.ensureDefault("file_types_description", "All Files");
	this.ensureDefault("file_size_limit", 0);	// Default zero means "unlimited"
	this.ensureDefault("file_upload_limit", 0);
	this.ensureDefault("file_queue_limit", 0);

	this.ensureDefault("button_image_url", "");
	this.ensureDefault("button_width", 1);
	this.ensureDefault("button_height", 1);
	this.ensureDefault("button_placeholder_id", "");

	this.ensureDefault("debug", false);

	this.ensureDefault("swfupload_preload_handler", null);
	this.ensureDefault("swfupload_load_failed_handler", null);
	this.ensureDefault("swfupload_loaded_handler", null);
	this.ensureDefault("file_dialog_start_handler", null);
	this.ensureDefault("file_queued_handler", null);
	this.ensureDefault("file_queue_error_handler", null);
	this.ensureDefault("file_dialog_complete_handler", null);

	this.ensureDefault("upload_resize_start_handler", null);
	this.ensureDefault("upload_start_handler", null);
	this.ensureDefault("upload_progress_handler", null);
	this.ensureDefault("upload_error_handler", null);
	this.ensureDefault("upload_success_handler", null);
	this.ensureDefault("upload_complete_handler", null);

	this.ensureDefault("debug_handler", this.debugMessage);

	this.ensureDefault("custom_settings", {});
	this.customSettings = this.settings.custom_settings;

	if(this.settings.button_placeholder_id == 'imgSpanButtonPlaceholder' && $("imgSpanButtonPlaceholder")){
		if($("icoImg_image_menu")){
			$('icoImg_image_menu').style.display = '';
			$('icoImg_image_menu').style.position = 'absolute';
			$('icoImg_image_menu').style.top = '-9999px';
		}else if(typeof editorid != 'undefined' && $(editorid + '_image_menu')){
			$(editorid + '_image_menu').style.display = '';
		}
		$('imgSpanButtonPlaceholder').innerHTML = 'upload';
		$("imgSpanButtonPlaceholder").style.display = 'inline-block';
		$("imgSpanButtonPlaceholder").style.width = this.settings.button_width + 'px';
		$("imgSpanButtonPlaceholder").style.height = this.settings.button_height + 'px';
		$("imgSpanButtonPlaceholder").style.backgroundImage = 'url(' + this.settings.button_image_url + ')';
		$("imgSpanButtonPlaceholder").style.backgroundRepeat = 'no-repeat';
        $("imgSpanButtonPlaceholder").onmouseover = function () {
			$("imgSpanButtonPlaceholder").style.backgroundPosition = '0px -25px';
        };
        $("imgSpanButtonPlaceholder").onmouseout = function () {
			$("imgSpanButtonPlaceholder").style.backgroundPosition = '0px 0px';
        };
	}
	if(this.settings.button_placeholder_id == 'spanButtonPlaceholder' && $("spanButtonPlaceholder")){
		if($("icoAttach_attach_menu")){
			$('icoAttach_attach_menu').style.display = '';
			$('icoAttach_attach_menu').style.position = 'absolute';
			$('icoAttach_attach_menu').style.top = '-9999px';
		}else if(typeof editorid != 'undefined' && $(editorid + '_attach_menu')){
			$(editorid + '_attach_menu').style.display = '';
		}
		$('spanButtonPlaceholder').innerHTML = 'upload';
		$("spanButtonPlaceholder").style.display = 'inline-block';
		$("spanButtonPlaceholder").style.width = this.settings.button_width + 'px';
		$("spanButtonPlaceholder").style.height = this.settings.button_height + 'px';
		$("spanButtonPlaceholder").style.backgroundImage = 'url(' + this.settings.button_image_url + ')';
		$("spanButtonPlaceholder").style.backgroundRepeat = 'no-repeat';
        $("spanButtonPlaceholder").onmouseover = function () {
			$("spanButtonPlaceholder").style.backgroundPosition = '0px -25px';
        };
        $("spanButtonPlaceholder").onmouseout = function () {
			$("spanButtonPlaceholder").style.backgroundPosition = '0px 0px';
        };
	}
	if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll' && $(this.settings.button_placeholder_id)){
		$(this.settings.button_placeholder_id).innerHTML = 'upload';
		$(this.settings.button_placeholder_id).style.display = 'inline-block';
		$(this.settings.button_placeholder_id).style.width = this.settings.button_width + 'px';
		$(this.settings.button_placeholder_id).style.height = this.settings.button_height + 'px';
		$(this.settings.button_placeholder_id).style.backgroundImage = 'url(' + this.settings.button_image_url + ')';
		$(this.settings.button_placeholder_id).style.backgroundRepeat = 'no-repeat';
		$(this.settings.button_placeholder_id).style.backgroundPosition = 'center top';
	}

	var self = this;

	var exts = "",
		mimes = "";

	if (this.settings.file_types.indexOf('*.*') < 0) {
		exts = this.settings.file_types.replace(/\*\./g, '').replace(/;/g, ',');
		var extsArray = jQuery.grep(exts.split(','), function (s) {
			return s.length > 0
		});
		mimes = jQuery.grep(
			jQuery.merge(
				jQuery.map(extsArray, function (ext) {
					return "." + ext;
				}),
				jQuery.map(extsArray, function (ext) {
					return SWFUpload.EXT_MIME_MAP[ext];
				})
			),
			function (s) {
				return s.length > 0
			}
		);
		mimes = jQuery.grep(mimes, function (m, i) {
			return i === jQuery.inArray(m, mimes)
		}).join(",");
	}

	var uploader = WebUploader.create({
		swf: getBasePath() + 'Uploader.swf',
		server: this.settings.upload_url,
		pick: '#' + this.settings.button_placeholder_id,
		compress: false,
		threads: 1,
		accept: {
			title: this.settings.file_types_description,
			extensions: exts,
			mimeTypes: mimes
		},
		fileVal: this.settings.file_post_name,
		formData: this.settings.post_params,
		fileNumLimit: this.settings.file_upload_limit,
		fileSingleSizeLimit: this.settings.file_size_limit * 1024,
		duplicate: true
	});

	this.uploader = uploader;

	uploader.on('beforeFileQueued', function(file) {
		self.queueEvent("file_dialog_start_handler");
	});

	uploader.on('fileQueued', function(file) {
		self.queueEvent("file_queued_handler", file);
	});

	uploader.on('startUpload', function() {
		self.queueEvent("file_dialog_complete_handler");
	});

	uploader.on('uploadStart', function(file) {
		self.queueEvent("upload_start_handler", file);
	});

	uploader.on('uploadBeforeSend', function (block, data) {
		delete data.id;
		delete data.name;
		delete data.lastModifiedDate;
		if(self.settings.post_params.type){
			data.type = self.settings.post_params.type;
		}
	});

	uploader.on('uploadProgress', function(file, percentage) {
		self.queueEvent("upload_progress_handler", [file, percentage]);
	});

	uploader.on('uploadError', function(file, reason) {
		self.queueEvent("upload_error_handler", [file, reason]);
	});

	uploader.on('uploadSuccess', function(file, response) {
		self.queueEvent("upload_success_handler", [file, response]);
	});

	uploader.on('error', function(code) {
		self.queueEvent("file_queue_error_handler", code);
	});

};

SWFUpload.prototype.setUploadURL = function (url) {
	this.uploader.options.server = url.toString();
};

SWFUpload.prototype.addPostParam = function (name, value) {
	this.uploader.options.formData[name] = value;
};

SWFUpload.prototype.queueEvent = function (handlerName, argumentArray) {
	var self = this;

	if (argumentArray == undefined) {
		argumentArray = [];
	} else if (!(argumentArray instanceof Array)) {
		argumentArray = [argumentArray];
	}

	if (typeof this.settings[handlerName] === "function") {
		this.eventQueue.push(function () {
			this.settings[handlerName].apply(this, argumentArray);
		});

		setTimeout(function () {
			self.executeNextEvent();
		}, 0);

	} else if (this.settings[handlerName] !== null) {
		throw "Event handler " + handlerName + " is unknown or is not a function";
	}
};

SWFUpload.prototype.executeNextEvent = function () {

	var  f = this.eventQueue ? this.eventQueue.shift() : null;
	if (typeof(f) === "function") {
		f.apply(this);
	}
};

SWFUpload.prototype.debug = function (message) {
	this.queueEvent("debug_handler", message);
};

SWFUpload.prototype.debugMessage = function (message) {
	var exceptionMessage, exceptionValues, key;

	if (this.settings.debug) {
		exceptionValues = [];

		if (typeof message === "object" && typeof message.name === "string" && typeof message.message === "string") {
			for (key in message) {
				if (message.hasOwnProperty(key)) {
					exceptionValues.push(key + ": " + message[key]);
				}
			}
			exceptionMessage = exceptionValues.join("\n") || "";
			exceptionValues = exceptionMessage.split("\n");
			exceptionMessage = "EXCEPTION: " + exceptionValues.join("\nEXCEPTION: ");
			SWFUpload.Console.writeLine(exceptionMessage);
		} else {
			SWFUpload.Console.writeLine(message);
		}
	}
};

function preLoad() {

}

function loadFailed() {

}

function fileDialogStart() {
	if(this.customSettings.uploadSource == 'forum') {
		this.customSettings.alertType = 0;
		if(this.customSettings.uploadFrom == 'fastpost') {
			if(typeof forum_post_inited == 'undefined') {
				appendscript(JSPATH + 'forum_post.js?' + VERHASH);
			}
		}
	}
}

function fileQueued(file) {
	try {
		var createQueue = true;
		if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
			var inputObj = $(this.customSettings.progressTarget+'_aid');
			if(inputObj && parseInt(inputObj.value)) {
				this.addPostParam('aid', inputObj.value);
			}
		} else if(this.customSettings.uploadSource == 'portal') {
			var inputObj = $('catid');
			if (inputObj && parseInt(inputObj.value)) {
				this.addPostParam('catid', inputObj.value);
			}else if(typeof check_catid == 'function' && !check_catid()) {
				this.uploader.cancelFile(file);
				return false;
			}
		}
		this.addPostParam('filetype', file.type);
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.maxAttachNum != undefined) {
				if(this.customSettings.maxAttachNum > 0) {
					this.customSettings.maxAttachNum--;
				} else {
					this.customSettings.alertType = 6;
					createQueue = false;
				}
			}

			if(createQueue && this.customSettings.maxSizePerDay != undefined) {
				if(this.customSettings.maxSizePerDay - file.size > 0) {
					this.customSettings.maxSizePerDay = this.customSettings.maxSizePerDay - file.size
				} else {
					this.customSettings.alertType = 11;
					createQueue = false;
				}
			}
			if(createQueue && this.customSettings.filterType != undefined) {
				var fileSize = this.customSettings.filterType[file.source.ext.toLowerCase()];
				if(fileSize != undefined && fileSize && file.size > fileSize) {
					this.customSettings.alertType = 5;
					createQueue = false;
				}
			}

		}
		if(createQueue) {
			progress.setStatus("等待上传...");
			this.uploader.upload(file);
		} else {
			this.uploader.cancelFile(file);
			progress.setStatus(this.customSettings.alertType ? STATUSMSG[this.customSettings.alertType] : "Cancelled");
			progress.setCancelled();
		}
		progress.toggleCancel(true, this);


	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(errorCode) {
	try {
		var err = '';
		switch (errorCode) {
		case 'F_EXCEED_SIZE':
			err = '单个文件大小不得超过' + WebUploader.Base.formatSize(this.uploader.option('fileSingleSizeLimit')) + '！';
			break;
		case 'Q_EXCEED_NUM_LIMIT':
			err = '最多只能上传' + this.settings.fileNumLimit + '个！';
			break;
		case 'Q_EXCEED_SIZE_LIMIT':
			err = '上传文件总大小超出' + WebUploader.Base.formatSize(this.uploader.option('fileSizeLimit')) + '！';
			break;
		case 'Q_TYPE_DENIED':
			err = '无效文件类型，请上传正确的文件格式！';
			break;
		case 'F_DUPLICATE':
			err = '请不要重复上传相同文件！';
			break;
		default:
			err = '上传错误，请刷新重试！' + code;
			break;
		}
		showDialog(err, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete() {
	try {
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.uploadType == 'attach') {
				if(typeof switchAttachbutton == "function") {
					switchAttachbutton('attachlist');
				}
				try {
						$('attach_tblheader').style.display = '';
						$('attach_notice').style.display = '';
				} catch (ex) {}
			} else if(this.customSettings.uploadType == 'image') {
				if(typeof switchImagebutton == "function") {
					switchImagebutton('imgattachlist');
				}
				try {
					$('imgattach_notice').style.display = '';
				} catch (ex) {}
			}
			var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
			var listObj = $(objId);
			var tableObj = listObj.getElementsByTagName("table");
			if(!tableObj.length) {
				listObj.innerHTML = "";
			}
		} else if(this.customSettings.uploadType == 'blog') {
			if(typeof switchImagebutton == "function") {
				switchImagebutton('imgattachlist');
			}
		}
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
			var preObj = $(this.customSettings.progressTarget);
			preObj.style.display = 'none';
			preObj.innerHTML = '';
		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("上传中...");
		progress.toggleCancel(true, this);
		if(this.customSettings.uploadSource == 'forum') {
			var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
			var attachlistObj = $(objId).parentNode;
			attachlistObj.scrollTop = $(file.id).offsetTop - attachlistObj.clientHeight;
		}
	} catch (ex) {
	}
}

function uploadProgress(file, percentage) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("正在上传 <progress value='" + percentage + "' max='1' style='width: 200px;'></progress> " + Math.ceil(percentage * 100) + "%");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.uploadType == 'poll') {
				var data = serverData;
				if(parseInt(data.aid)) {
					var preObj = $(this.customSettings.progressTarget);
					preObj.innerHTML = "";
					preObj.style.display = '';
					var img = new Image();
					img.src = IMGDIR + '/attachimg_2.png';//data.smallimg;
					var imgObj = document.createElement("img");
					imgObj.src = img.src;
					imgObj.className = "cur1";
					imgObj.onmouseout = function(){hideMenu('poll_img_preview_'+data.aid+'_menu');};//"hideMenu('poll_img_preview_"+data.aid+"_menu');";
					imgObj.onmouseover = function(){showMenu({'menuid':'poll_img_preview_'+data.aid+'_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});};//"showMenu({'menuid':'poll_img_preview_"+data.aid+"_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});";
					preObj.appendChild(imgObj);
					var inputObj = document.createElement("input");
					inputObj.type = 'hidden';
					inputObj.name = 'pollimage[]';
					inputObj.id = this.customSettings.progressTarget+'_aid';
					inputObj.value= data.aid;
					preObj.appendChild(inputObj);
					var preImgObj = document.createElement("span");
					preImgObj.style.display = 'none';
					preImgObj.id = 'poll_img_preview_'+data.aid+'_menu';
					img = new Image();
					img.src = data.smallimg;
					imgObj = document.createElement("img");
					imgObj.src = img.src;
					preImgObj.appendChild(imgObj);
					preObj.appendChild(preImgObj);
				}
			} else {
				aid = parseInt(serverData);
				if(aid > 0) {
					if(this.customSettings.uploadType == 'attach') {
						ajaxget('forum.php?mod=ajax&action=attachlist&aids=' + aid + (!fid ? '' : '&fid=' + fid)+(typeof resulttype == 'undefined' ? '' : '&result=simple'), file.id);
					} else if(this.customSettings.uploadType == 'image') {
						var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_'+aid);
						ajaxget('forum.php?mod=ajax&action=imagelist&type=single&pid=' + pid + '&aids=' + aid + (!fid ? '' : '&fid=' + fid), tdObj.id);
						$(file.id).style.display = 'none';
					}
				} else {
					aid = aid < -1 ? Math.abs(aid) : aid;
					if(typeof STATUSMSG[aid] == "string") {
						progress.setStatus(STATUSMSG[aid]);
						showDialog(STATUSMSG[aid], 'notice', null, null, 0, null, null, null, null, sdCloseTime);
					} else {
						progress.setStatus("取消上传");
					}
					this.uploader.cancelFile(file);
					progress.setCancelled();
					progress.toggleCancel(true, this.uploader);
				}
			}
		} else if(this.customSettings.uploadType == 'album') {
			var data = serverData;
			if(parseInt(data.picid)) {
				var newTr = document.createElement("TR");
				var newTd = document.createElement("TD");
				var img = new Image();
				img.src = data.url;
				var imgObj = document.createElement("img");
				imgObj.src = img.src;
				newTd.className = 'c';
				newTd.appendChild(imgObj);
				newTr.appendChild(newTd);
				newTd = document.createElement("TD");
				newTd.innerHTML = '<strong>'+file.name+'</strong>';
				newTr.appendChild(newTd);
				newTd = document.createElement("TD");
				newTd.className = 'd';
				newTd.innerHTML = '图片描述<br/><textarea name="title['+data.picid+']" cols="40" rows="2" class="pt"></textarea>';
				newTr.appendChild(newTd);
				this.customSettings.imgBoxObj.appendChild(newTr);
			} else {
				showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
			}
			$(file.id).style.display = 'none';
		} else if(this.customSettings.uploadType == 'blog') {
			var data = serverData;
			if(parseInt(data.picid)) {
				var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_'+data.picid);
				var img = new Image();
				img.src = data.url;
				var imgObj = document.createElement("img");
				imgObj.src = img.src;
				imgObj.className = "cur1";
				imgObj.onclick = function() {insertImage(data.bigimg);};
				tdObj.appendChild(imgObj);
				var inputObj = document.createElement("input");
				inputObj.type = 'hidden';
				inputObj.name = 'picids['+data.picid+']';
				inputObj.value= data.picid;
				tdObj.appendChild(inputObj);
			} else {
				showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
			}
			$(file.id).style.display = 'none';
		} else if(this.customSettings.uploadSource == 'portal') {
			var data = serverData;
			if(data.aid) {
				if(this.customSettings.uploadType == 'attach') {
					ajaxget('portal.php?mod=attachment&op=getattach&type=attach&id=' + data.aid, file.id);
					if($('attach_tblheader')) {
						$('attach_tblheader').style.display = '';
					}
				} else {
					var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'attach_list_'+data.aid);
					ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id);
					$(file.id).style.display = 'none';
				}
			} else {
				showDialog('上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
				progress.setStatus("Cancelled");
				this.uploader.cancelFile(file);
				progress.setCancelled();
				progress.toggleCancel(true, this.uploader);
			}
		} else {
			progress.setComplete();
			progress.setStatus("上传完成.");
			progress.toggleCancel(false);
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadComplete(file) {
}

function uploadError(file, message) {
	var progress = new FileProgress(file, this.customSettings.progressTarget);
	progress.setStatus("Upload Failed.");
	progress.setCancelled();
}

function getInsertTdId(boxObj, tdId) {
	var tableObj = boxObj.getElementsByTagName("table");
	var tbodyObj, trObj, tdObj;
	if (!tableObj.length) {
		tableObj = document.createElement("table");
		tableObj.className = "imgl";
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
		boxObj.appendChild(tableObj);

	} else if (!tableObj[0].getElementsByTagName("tbody").length) {
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
	} else {
		tableObj = tableObj[0];
		tbodyObj = tableObj.getElementsByTagName("tbody")[0];
	}

	var createTr = true;
	var inserID = 0;
	if (tbodyObj.childNodes.length) {
		trObj = tbodyObj.childNodes[tbodyObj.childNodes.length - 1];
		var findObj = trObj.getElementsByTagName("TD");
		for (var j = 0; j < findObj.length; j++) {
			if (findObj[j].id == "") {
				inserID = j;
				tdObj = findObj[j];
				break;
			}
		}
		if (inserID) {
			createTr = false;
		}
	}
	if (createTr) {
		trObj = document.createElement("TR");
		for (var i = 0; i < 4; i++) {
			var newTd = document.createElement("TD");
			newTd.width = "25%";
			newTd.vAlign = "bottom";
			newTd.appendChild(document.createTextNode(" "));
			trObj.appendChild(newTd);
		}
		tdObj = trObj.childNodes[0];
		tbodyObj.appendChild(trObj);
	}
	tdObj.id = tdId;
	return tdObj;
}

function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;

	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = document.createElement("div");
		this.fileProgressWrapper.className = "progressWrapper";
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";

		var progressCancel = document.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "javascript:;";
		progressCancel.style.display = "none";
		progressCancel.appendChild(document.createTextNode(" "));

		var progressText = document.createElement("div");
		progressText.className = "progressName";
		progressText.appendChild(document.createTextNode(file.name));

		var progressBar = document.createElement("div");
		progressBar.className = "progressBarInProgress";

		var progressStatus = document.createElement("div");
		progressStatus.className = "progressBarStatus";
		progressStatus.innerHTML = "&nbsp;";

		this.fileProgressElement.appendChild(progressCancel);
		this.fileProgressElement.appendChild(progressText);
		this.fileProgressElement.appendChild(progressStatus);
		this.fileProgressElement.appendChild(progressBar);

		this.fileProgressWrapper.appendChild(this.fileProgressElement);

		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);

}

FileProgress.prototype.setTimer = function(timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};

FileProgress.prototype.getTimer = function(timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function() {
	try {
		this.fileProgressElement.className = "progressContainer";

		this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
		this.fileProgressElement.childNodes[2].className = "progressBarStatus";

		this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
		this.fileProgressElement.childNodes[3].style.width = "0%";

		this.appear();
	} catch(ex) {}
};

FileProgress.prototype.setProgress = function(percentage) {
	this.fileProgressElement.className = "progressContainer green";
	this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
	this.fileProgressElement.childNodes[3].style.width = percentage + "%";

	this.appear();
};

FileProgress.prototype.setComplete = function() {
	this.fileProgressElement.className = "progressContainer blue";
	this.fileProgressElement.childNodes[3].className = "progressBarComplete";
	this.fileProgressElement.childNodes[3].style.width = "";

};

FileProgress.prototype.setError = function() {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function() {
		oSelf.disappear();
	},
	5000));
};

FileProgress.prototype.setCancelled = function() {
	this.fileProgressElement.className = "progressContainer";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function() {
		oSelf.disappear();
	},
	2000));
};

FileProgress.prototype.setStatus = function(status) {
	this.fileProgressElement.childNodes[2].innerHTML = status;
};

FileProgress.prototype.toggleCancel = function(show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.display = show ? "": "none";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		var oSelf = this;
		this.fileProgressElement.childNodes[0].onclick = function() {
			swfUploadInstance.cancelFile(fileID);
			oSelf.setStatus("Cancelled");
			oSelf.setCancelled();
			return false;
		};
	}
};

FileProgress.prototype.appear = function() {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}

	if (this.fileProgressWrapper.filters) {
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch(e) {
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}

	this.fileProgressWrapper.style.height = "";

	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";

};

FileProgress.prototype.disappear = function() {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30; // 15 fps
	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch(e) {
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function() {
			oSelf.disappear();
		},
		rate));
	} else {
		this.fileProgressWrapper.style.display = "none";
		this.setTimer(null);
	}
};