var cloudj = jQuery.noConflict();

if (typeof disallowfloat == 'undefined' || disallowfloat === null) {
	var disallowfloat = '';
}

var currentNormalEditDisplay = 0;

cloudj(document).ready(function() {
	ajaxGetSearchResultThreads();

	cloudj('#previewForm').submit(function() {
		return previewFormSubmit();
	});

});

function previewFormSubmit() {
	saveAllThread();

	if (!selectedTopicId) {
		alert('请推送头条信息');
		return false;
	}
	if (selectedNormalIds.length < 1) {
		alert('请至少推送一条信息到列表区域');
		return false;
	}

	var i = 1;
	for (var k = 1; k <= 5; k++) {
		var input_displayorder = cloudj('#normal_thread_' + k).find('.preview_displayorder');
		if (input_displayorder.size()) {
			input_displayorder.val(i);
			i++;
		}
	}
	return true;
}

function initSelect() {
	var initTopicObj = cloudj('#search_result .qqqun_op .qqqun_op_topon');
	initTopicObj.addClass('qqqun_op_top');
	initTopicObj.removeClass('qqqun_op_topon');
	var initNormalObj = cloudj('#search_result .qqqun_op .qqqun_op_liston');
	initNormalObj.addClass('qqqun_op_list');
	initNormalObj.removeClass('qqqun_op_liston');
	selectedTopicId = parseInt(selectedTopicId);
	if (selectedTopicId) {
		cloudj('#thread_addtop_' + selectedTopicId).addClass('qqqun_op_topon');
		cloudj('#thread_addtop_' + selectedTopicId).removeClass('qqqun_op_top');
	}
	cloudj.each(selectedNormalIds, function(k, v) {
		v = parseInt(v);
		if (v) {
			cloudj('#thread_addlist_' + v).addClass('qqqun_op_liston');
			cloudj('#thread_addlist_' + v).removeClass('qqqun_op_list');
		}
	});
}

function ajaxChangeSearch() {
	cloudj('#srchtid').val('');
	ajaxGetSearchResultThreads();
}

function ajaxGetSearchResultThreads() {
	cloudj('#search_result').html('<tr><td colspan="3">加载中...</td></tr>');
	qqgroupajaxpost('search_form', 'search_result', 'search_result', null, null, function() {initSelect(); return false});
	return false;
}

function ajaxGetPageResultThreads(page, mpurl) {
	cloudj('#search_result').html('<tr><td colspan="3">加载中...</td></tr>');
	if (typeof page == 'undefined' || page === null) {
		page = 1;
	}
	if (typeof mpurl == 'undefined' || !mpurl) {
		return false;
	}
	ajaxget(mpurl + '&page=' + page, 'search_result', null, null, null, function() {initSelect();} );
}

function addMiniportalTop(tid) {
	tid = parseInt(tid);
	if (cloudj.inArray(tid, selectedNormalIds) > -1) {
		removeNormalThreadByTid(tid);
	}
	addMiniportalTopId(tid);
	initSelect();
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread&tid=' + tid, 'topicDiv', null, null, null, function() { clickTopicEditor(); });
}

function addMiniportalTopId(tid) {
	selectedTopicId = tid;
}

function showPreviewEditor(topic, hideall) {
	if (hideall) {
		cloudj('.qqqun_list .qqqun_editor').hide();
		cloudj('.qqqun_list .qqqun_xl li').removeClass('current');
		cloudj('.qqqun_list').removeClass('qqqun_list_editor');
		cloudj('.qqqun_top .qqqun_editor').hide();
		cloudj('.qqqun_top').removeClass('qqqun_top_editor');
	} else {
		if (topic) {
			cloudj('.qqqun_list .qqqun_editor').hide();
			cloudj('.qqqun_list .qqqun_xl li').removeClass('current');
			cloudj('.qqqun_list').removeClass('qqqun_list_editor');
			cloudj('.qqqun_top .qqqun_editor').show();
			cloudj('.qqqun_top').addClass('qqqun_top_editor');
		} else {
			cloudj('.qqqun_list .qqqun_editor').show();
			cloudj('.qqqun_list').addClass('qqqun_list_editor');
			cloudj('.qqqun_list .qqqun_xl li').removeClass('current');
			cloudj('.qqqun_top .qqqun_editor').hide();
			cloudj('.qqqun_top').removeClass('qqqun_top_editor');
		}
	}

}

function clickTopicEditor(topicFocus) {
	if (typeof topicFocus == 'undefined') {
		var topicFocus = 'title';
	}
	showPreviewEditor(true, false);
	if (topicFocus == 'title') {
		cloudj('#topic-editor-input-title').addClass('pt_focus');
		cloudj('#topic-editor-input-title').focus();
	} else if (topicFocus == 'content') {
		cloudj('#topic-editor-textarea-content').addClass('pt_focus');
		cloudj('#topic-editor-textarea-content').focus();
	}
	currentNormalEditDisplay = 0;
}

function blurTopic(obj) {
	var thisobj = cloudj(obj);
	thisobj.removeClass('pt_focus');
}

function clickNormalEditor(obj) {
	var thisobj = cloudj(obj);
	showPreviewEditor(false, false);
	thisobj.addClass('pt_focus');
	thisobj.focus();
	currentNormalEditDisplay = parseInt(thisobj.parent().attr('displayorder'));
}

function blurNormalTextarea(obj) {
	var thisobj = cloudj(obj);
	liObj = thisobj.parent();
	var displayorder = parseInt(liObj.attr('displayorder'));
	if (displayorder == currentNormalEditDisplay) {
		liObj.addClass('current');
	}
	cloudj('.qqqun_list .qqqun_xl textarea').removeClass('pt_focus');
}

function addMiniportalList(tid) {
	tid = parseInt(tid);
	if (cloudj.inArray(tid, selectedNormalIds) > -1) {
		return false;
	}
	if (selectedNormalIds.length >= 5) {
		alert('推送帖子已达到5条，请在右侧取消一些再重试。');
		return false;
	}
	if (tid == selectedTopicId) {
		selectedTopicId = 0;
		ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread&tid=0', 'topicDiv');
	}
	addMiniportalListId(tid);
	initSelect();
	var insertPos = 'normal_thread_' + selectedNormalIds.length;
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getNormalThread&tid=' + tid, insertPos, null, null, null, function() { clickNormalEditor(cloudj('#' + insertPos).find('textarea')); });
}

function addMiniportalListId(tid) {
	selectedNormalIds.push(tid);
}

function editNormalThread() {
	var threadLi = cloudj('#normal_thread_' + currentNormalEditDisplay);
	clickNormalEditor(threadLi.find('textarea'));
}

function saveAllThread() {

	showPreviewEditor(false, true);

	currentNormalEditDisplay = 0;
}

function moveNormalThread(up) {
	var displayorder = currentNormalEditDisplay;
	var threadLi = cloudj('#normal_thread_' + displayorder);
	if (!threadLi.attr('id') || !displayorder) {
		return false;
	}
	var newDisplayorder = 0;
	if (up) {
		newDisplayorder = displayorder - 1;
	} else {
		newDisplayorder = displayorder + 1;
	}
	if (newDisplayorder < 1 || newDisplayorder > 5) {
		return false;
	}
	var newLiId = 'normal_thread_' + newDisplayorder;
	var newThreadLi = cloudj('#' + newLiId);
	if (!newThreadLi.find('textarea').size()) {
		return false;
	}
	var tmpHtml = newThreadLi.html();
	newThreadLi.html(threadLi.html());
	threadLi.html(tmpHtml);
	newThreadLi.addClass('current');
	threadLi.removeClass('current');
	currentNormalEditDisplay = newDisplayorder;
}

function removeTopicThread(tid) {
	tid = parseInt(tid);
	selectedTopicId = 0;
	initSelect();
	ajaxget(adminscript + '?action=cloud&operation=qqgroup&anchor=block&op=getTopicThread', 'topicDiv', null, null, null, function() { showPreviewEditor(false, true)});
}

function removeNormalThread() {
	var displayorder = currentNormalEditDisplay;
	var removeTid = parseInt(cloudj('#normal_thread_' + displayorder).find('.normal_thread_tid').val());
	return removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, true);
}

function removeNormalThreadByTid(tid) {
	tid = parseInt(tid);
	var threadInput = cloudj('.qqqun_list .qqqun_xl .normal_thread_tid[value="' + tid + '"]');
	if (threadInput.size()) {
		var displayorder = threadInput.parent().attr('displayorder');
		var removeTid = tid;
		return removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, false);
	}
}

function removeNormalThreadByDisplayorderAndTid(displayorder, removeTid, inNormalEditor) {
	displayorder = parseInt(displayorder);
	removeTid = parseInt(removeTid);
	var threadLi = cloudj('#normal_thread_' + displayorder);
	if (!threadLi.attr('id') || !displayorder) {
		return false;
	}
	threadLi.removeClass('current');
	var index = cloudj.inArray(removeTid, selectedNormalIds);
	if (index != -1) {
		selectedNormalIds.splice(index, 1);
	}
	initSelect();
	if (typeof inNormalEditor == 'udefined') {
		var inNormalEditor = false;
	}
	threadLi.slideUp(100, function() { removeNormalThreadRecall(displayorder, inNormalEditor)});

}

function removeNormalThreadRecall(displayorder, inNormalEditor) {
	for (var i = displayorder; i <= 5; i++) {
		var currentDisplayorder = i;
		var nextDisplayorder = i + 1;
		var currentLiId = 'normal_thread_' + currentDisplayorder;
		var currentThreadLi = cloudj('#' + currentLiId);
		var nextLiId = 'normal_thread_' + nextDisplayorder;
		var nextThreadLi = cloudj('#' + nextLiId);
		if (nextThreadLi.find('textarea').size()) {
			currentThreadLi.html(nextThreadLi.html());
			currentThreadLi.show();
		} else {
			currentThreadLi.html('');
			currentThreadLi.hide();
			break;
		}
	}
	var threadLi = cloudj('#normal_thread_' + displayorder);
	var prevDisplayorder = displayorder - 1;
	if (threadLi.find('textarea').size()) {
		if (inNormalEditor) {
			threadLi.addClass('current');
		}
		currentNormalEditDisplay = displayorder;
	} else if (prevDisplayorder) {
		var prevThreadLi = cloudj('#normal_thread_' + prevDisplayorder);
		if (inNormalEditor) {
			prevThreadLi.addClass('current');
		}
		currentNormalEditDisplay = prevDisplayorder;
	} else {
		var firstThreadLi =  cloudj('#normal_thread_1');
		if (inNormalEditor) {
			saveAllThread();
		}
		firstThreadLi.html('<div class="tips">点击左侧 <img src="static/image/admincp/cloud/qun_op_list.png" align="absmiddle" /> 将信息推送到列表</div>');
		firstThreadLi.show();
	}
}

function ajaxUploadQQGroupImage() {
	cloudj('#uploadImageResult').parent().show();
	cloudj('#uploadImageResult').text('图片上传中，请稍后...');
	qqgroupajaxpost('uploadImage', 'uploadImageResult', 'uploadImageResult', null, null, 'uploadRecall()');
}

function uploadRecall() {
	if(cloudj('#uploadImageResult').find('#upload_msg_success').size()) {
		cloudj('#uploadImageResult').parent().show();
		var debug_rand = Math.random();
		var imagePath = cloudj('#uploadImageResult #upload_msg_imgpath').text();
		var imageUrl = cloudj('#uploadImageResult #upload_msg_imgurl').text();
		cloudj('#topic_image_value').val(imagePath);
		cloudj('#topic_editor_thumb').attr('src', imageUrl + '?' + debug_rand);
		cloudj('#topic_preview_thumb').attr('src', imageUrl + '?' + debug_rand);
		setTimeout(function() {hideWindow('uploadImgWin');}, 2000);
	}
}

function qqgroupajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $(ajaxframeid);
	var formtarget = $(formid).target;

	var handleResult = function() {
		var s = '';
		var evaled = false;

		showloading('none');

		ajaxResponse = cloudj('#' + ajaxframeid).contents().text();
		var regex = /<\!\[CDATA\[(.*)\]\]>/;
		var regexed = regex.exec(ajaxResponse);
		if (regexed && regexed[1]) {
			s = regexed[1];
		} else {
			s = ajaxResponse;
		}
		if (!s) {
			s = '内部错误，无法显示此内容';
		}

		if(s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			if(showidclass != 'onerror') {
				cloudj(showid).addClass(showidclass);
			} else {
				showError(s);
				ajaxerror = true;
			}
		}
		if(submitbtn) {
			cloudj(submitbtn).attr('disabled', false);
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml($(showid), s);
		}
		ajaxerror = null;
		cloudj('#' + formid).attr('target', formtarget);
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		cloudj(ajaxframe.parentNode).remove();
	};
	if(!ajaxframe) {
		var div = cloudj('<div>');
		div.css('display', 'none');
		div.html('<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1">');
		cloudj('#append_parent').append(div);
		ajaxframe = $(ajaxframeid);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	showloading();
	cloudj('#' + formid).attr('target', ajaxframeid);
	var action = cloudj('#' + formid).attr('action');
	action = hostconvert(action);
	cloudj('#' + formid).attr('action', action.replace(/\&inajax\=1/g, '')+'&inajax=1');
	$(formid).submit();
	if(submitbtn) {
		cloudj(submitbtn).attr('disabled', true);
	}
	doane();
	return false;
}