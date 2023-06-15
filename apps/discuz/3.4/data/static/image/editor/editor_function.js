function uploadEdit(obj) {
	mainForm = obj.form;
	forms = $('attachbody').getElementsByTagName("FORM");
	albumid = $('uploadalbum').value;
	edit_save();
	upload();
}

function edit_save() {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status == 'code') {
		$('uchome-ttHtmlEditor').value = p.document.getElementById('sourceEditor').value;
	} else if(status == 'text') {
		if(BROWSER.ie) {
			obj.document.body.innerText = p.document.getElementById('dvtext').value;
			$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
		} else {
			obj.document.body.textContent = p.document.getElementById('dvtext').value;
			var sOutText = obj.document.body.innerHTML;
			$('uchome-ttHtmlEditor').value = sOutText.replace(/\r\n|\n/g,"<br>");
		}
	} else {
		$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
	}
	backupContent($('uchome-ttHtmlEditor').value);
}

function downRemoteFile() {
	edit_save();
	var formObj = $("articleform");
	var oldAction = formObj.action;
	formObj.action = "portal.php?mod=portalcp&ac=upload&op=downremotefile";
	formObj.onSubmit = "";
	formObj.target = "uploadframe";
	formObj.submit();
	formObj.action = oldAction;
	formObj.target = "";
}
function backupContent(sHTML) {
	if(sHTML.length > 11) {
		var obj = $('uchome-ttHtmlEditor').form;
		if(!obj) return;
		var data = subject = message = '';
		for(var i = 0; i < obj.elements.length; i++) {
			var el = obj.elements[i];
			if(el.name != '' && (el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio')) && el.name.substr(0, 6) != 'attach') {
				var elvalue = el.value;
				if(el.name == 'subject' || el.name == 'title') {
					subject = trim(elvalue);
				} else if(el.name == 'message' || el.name == 'content') {
					message = trim(elvalue);
				}
				if((el.type == 'checkbox' || el.type == 'radio') && !el.checked) {
					continue;
				}
				if(trim(elvalue)) {
					data += el.name + String.fromCharCode(9) + el.tagName + String.fromCharCode(9) + el.type + String.fromCharCode(9) + elvalue + String.fromCharCode(9, 9);
				}
			}
		}

		if(!subject && !message) {
			return;
		}
		saveUserdata('home', data);
	}
}

function edit_insert(html) {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status != 'html') {
		alert('本操作只在多媒体编辑模式下才有效');
		return;
	}
	obj.focus();
	if(BROWSER.ie){
		var f = obj.document.selection.createRange();
		f.pasteHTML(html);
		f.collapse(false);
		f.select();
	} else {
		obj.document.execCommand('insertHTML', false, html);
	}
}

function insertImage(image, url, width, height) {
	url = typeof url == 'undefined' || url === null ? image : url;
	width = typeof width == 'undefined' || width === null ? 0 : parseInt(width);
	height = typeof height == 'undefined' || height === null ? 0 : parseInt(height);
	var html = '<p><a href="' + url + '" target="_blank"><img src="'+image+'"'+(width?' width="'+width+'"':'')+(height?' height="'+height+'"':'')+'></a></p>';
	edit_insert(html);
}

function insertFile(file, url) {
	url = typeof url == 'undefined' || url === null ? image : url;
	var html = '<p><a href="' + url + '" target="_blank" class="attach">' + file + '</a></p>';
	edit_insert(html);
}

function createImageBox(fn) {
	if(typeof fn == 'function' && !fn()) {
		return false;
	}
	var menu = $('icoImg_image_menu');
	if(menu) {
		if(menu.style.visibility == 'hidden') {
			menu.style.visibility = 'visible';
		} else {
			menu.style.width = '600px';
			showMenu({'ctrlid':'icoImg_image','mtype':'win','evt':'click','pos':'00','timeout':250,'duration':3,'drag':'icoImg_image_ctrl'});
		}
	}
}

function createAttachBox(fn) {
	if(typeof fn == 'function' && !fn()) {
		return false;
	}
	var menu = $('icoAttach_attach_menu');
	if(menu) {
		if(menu.style.visibility == 'hidden') {
			menu.style.visibility = 'visible';
		} else {
			menu.style.width = '600px';
			showMenu({'ctrlid':'icoAttach_attach','mtype':'win','evt':'click','pos':'00','timeout':250,'duration':3,'drag':'icoAttach_attach_ctrl'});
		}
	}
}

function switchButton(btn, type) {
	var btnpre = 'icoImg_btn_';
	if(!$(btnpre + btn) || !$('icoImg_' + btn)) {
		return;
	}
	var tabs = $('icoImg_' + type + '_ctrl').getElementsByTagName('LI');
	$(btnpre + btn).style.display = '';
	$('icoImg_' + btn).style.display = '';
	$(btnpre + btn).className = 'current';
	var btni = '';
	for(i = 0;i < tabs.length;i++) {
		if(tabs[i].id.indexOf(btnpre) !== -1) {
			btni = tabs[i].id.substr(btnpre.length);
		}
		if(btni != btn) {
			if(!$('icoImg_' + btni) || !$('icoImg_btn_' + btni)) {
				continue;
			}
			$('icoImg_' + btni).style.display = 'none';
			$('icoImg_btn_' + btni).className = '';
		}
	}
}


function changeEditFull(flag) {
	var ifrHtmlEditor = $('uchome-ifrHtmlEditor');
	var editor = ifrHtmlEditor.parentNode;
	if(flag) {
		document.body.scroll = 'no';
		document.body.style.overflow = 'hidden';
		window.resize = function(){changeEditFull(1)};
		editor.style.top = '0';
		editor.style.left = '0';
		editor.style.position = 'fixed';
		editor.style.width = '100%';
		editor.setAttribute('srcheight', editor.style.height);
		editor.style.height = '100%';
		editor.style.minWidth = '800px';
		editor.style.zIndex = '300';
		ifrHtmlEditor.style.height = '100%';
		ifrHtmlEditor.style.zoom = ifrHtmlEditor.style.zoom=="1"?"100%":"1";
	} else {
		document.body.scroll = 'yes';
		document.body.style.overflow = 'auto';
		window.resize = null;
		editor.style.position = '';
		editor.style.width = '';
		editor.style.height = editor.getAttribute('srcheight');
	}
	doane();
}

function showInnerNav(){
	var navtitle = $('innernavele');
	var pagetitle = $('pagetitle');
	if(navtitle && navtitle.style.display == 'none') {
		navtitle.style.display = '';
	}
	if(pagetitle && pagetitle.style.display == 'none') {
		pagetitle.style.display = '';
	}
}

function loadimgsize(imgurl, editor, p) {
	var editor = !editor ? 'icoImg' : editor;
	var s = new Object();
	var p = !p ? '_image' : p;
	$(editor + p + '_param_2').value = '';
	$(editor + p + '_param_3').value = '';
	s.img = new Image();
	s.img.src = imgurl;
	s.loadCheck = function () {
		if($(editor + p + '_param_1').value == imgurl) {
			if(s.img.complete) {
				$(editor + p + '_param_2').value = s.img.width ? s.img.width : '';
				$(editor + p + '_param_3').value = s.img.height ? s.img.height : '';
			} else {
				setTimeout(function () {s.loadCheck();}, 100);
			}
		}
	};
	s.loadCheck();
}