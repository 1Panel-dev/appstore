/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: handlers.js 31952 2012-10-25 09:20:40Z zhengqingpeng $
*/

var sdCloseTime = 2;
function preLoad() {
	if(!this.support.loading) {
		disableMultiUpload(this.customSettings);
		return false;
	}
}
function loadFailed() {
	disableMultiUpload(this.customSettings);
}
function disableMultiUpload(obj) {
	if(obj.uploadSource == 'forum' && obj.uploadFrom != 'fastpost') {
		try{
			obj.singleUpload.style.display = '';
			var dIdStr = obj.singleUpload.getAttribute("did");
			if(dIdStr != null) {
				if(typeof forum_post_inited == 'undefined') {
					appendscript(JSPATH + 'forum_post.js?' + VERHASH);
				}
				var idArr = dIdStr.split("|");
				$(idArr[0]).style.display = 'none';
				if(idArr[1] == 'local') {
					switchImagebutton('local');
				} else if(idArr[1] == 'upload') {
					switchAttachbutton('upload');
				}
			}
		} catch (e) {
		}
	}
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
			if(inputObj && parseInt(inputObj.value)) {
				this.addPostParam('catid', inputObj.value);
			}
		}
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
				var fileSize = this.customSettings.filterType[file.type.substr(1).toLowerCase()];
				if(fileSize != undefined && fileSize && file.size > fileSize) {
					this.customSettings.alertType = 5;
					createQueue = false;
				}
			}

		}
		if(createQueue) {
			progress.setStatus("等待上传...");
		} else {
			this.cancelUpload(file.id);
			progress.setCancelled();
		}
		progress.toggleCancel(true, this);


	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			message = parseInt(message);
			showDialog("您选择的文件个数超过限制。\n"+(message === 0 ? "您已达到上传文件的上限了。" : "您还可以选择 " + message + " 个文件"), 'notice', null, null, 0, null, null, null, null, sdCloseTime);
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
			case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
				progress.setStatus("文件太大.");
				this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
				progress.setStatus("不能上传零字节文件.");
				this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
				progress.setStatus("禁止上传该类型的文件.");
				this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
				alert("You have selected too many files.  " +  (message > 1 ? "You may only add " +  message + " more files" : "You cannot add any more files."));
				break;
			default:
				if (file !== null) {
					progress.setStatus("Unhandled Error");
				}
				this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.uploadType == 'attach') {
				if(typeof switchAttachbutton == "function") {
					switchAttachbutton('attachlist');
				}
				try {
					if(this.getStats().files_queued) {
						$('attach_tblheader').style.display = '';
						$('attach_notice').style.display = '';
					}
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
		this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		this.addPostParam('filetype', file.type);
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

	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {

	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("正在上传("+percent+"%)...");

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.uploadType == 'poll') {
				var data = eval('('+serverData+')');
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
					this.cancelUpload(file.id);
					progress.setCancelled();
					progress.toggleCancel(true, this);
					var stats = this.getStats();
					var obj = {'successful_uploads':--stats.successful_uploads, 'upload_cancelled':++stats.upload_cancelled};
					this.setStats(obj);
				}
			}
		} else if(this.customSettings.uploadType == 'album') {
			var data = eval('('+serverData+')');
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
			var data = eval('('+serverData+')');
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
			var data = eval('('+serverData+')');
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
				this.cancelUpload(file.id);
				progress.setCancelled();
				progress.toggleCancel(true, this);
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

function getInsertTdId(boxObj, tdId) {
	var tableObj = boxObj.getElementsByTagName("table");
	var tbodyObj, trObj, tdObj;
	if(!tableObj.length) {
		tableObj = document.createElement("table");
		tableObj.className = "imgl";
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
		boxObj.appendChild(tableObj);

	} else if(!tableObj[0].getElementsByTagName("tbody").length) {
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
	} else {
		tableObj = tableObj[0];
		tbodyObj = tableObj.getElementsByTagName("tbody")[0];
	}

	var createTr = true;
	var inserID = 0;
	if(tbodyObj.childNodes.length) {
		trObj = tbodyObj.childNodes[tbodyObj.childNodes.length -1];
		var findObj = trObj.getElementsByTagName("TD");
		for(var j=0; j < findObj.length; j++) {
			if(findObj[j].id == "") {
				inserID = j;
				tdObj = findObj[j];
				break;
			}
		}
		if(inserID) {
			createTr = false;
		}
	}
	if(createTr) {
		trObj = document.createElement("TR");
		for(var i=0; i < 4; i++) {
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
function uploadComplete(file) {
	try {
		if (this.getStats().files_queued === 0) {
		} else {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}

}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				progress.setStatus("Upload Error: " + message);
				this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
				progress.setStatus("Configuration Error");
				this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
				progress.setStatus("Upload Failed.");
				this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR:
				progress.setStatus("Server (IO) Error");
				this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
				progress.setStatus("Security Error");
				this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				progress.setStatus("Upload limit exceeded.");
				this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
				progress.setStatus("File not found.");
				this.debug("Error Code: The file was not found, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
				progress.setStatus("Failed Validation.  Upload skipped.");
				this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
				if (this.getStats().files_queued === 0) {
				}
				progress.setStatus(this.customSettings.alertType ? STATUSMSG[this.customSettings.alertType] : "Cancelled");
				progress.setCancelled();
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
				progress.setStatus("Stopped");
				break;
			default:
				progress.setStatus("Unhandled Error: " + error_code);
				this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}