function submitForm() {

	if (dialogHtml == '') {
		dialogHtml = $('siteInfo').innerHTML;
		$('siteInfo').innerHTML = '';
	}

	showWindow('open_cloud', dialogHtml, 'html');

	$('fwin_open_cloud').style.top = '80px';
	$('cloud_api_ip').value = cloudApiIp;

	return false;
}

function dealHandle(msg) {

	getMsg = true;

	if (msg['status'] == 'error') {
		$('loadinginner').innerHTML = '<font color="red">' + msg['content'] + '</font>';
		return;
	}

	$('loading').style.display = 'none';
	$('mainArea').style.display = '';

	if(cloudStatus == 'upgrade') {
		$('title').innerHTML = msg['cloudIntroduction']['upgrade_title'];
		$('msg').innerHTML = msg['cloudIntroduction']['upgrade_content'];
	} else {
		$('title').innerHTML = msg['cloudIntroduction']['open_title'];
		$('msg').innerHTML = msg['cloudIntroduction']['open_content'];
	}

	if (msg['navSteps']) {
		$('nav_steps').innerHTML = msg['navSteps'];
	}

	if (msg['protocalUrl']) {
		$('protocal_url').href = msg['protocalUrl'];
	}

	if (msg['cloudApiIp']) {
		cloudApiIp = msg['cloudApiIp'];
	}

	if (msg['manyouUpdateTips']) {
		$('manyou_update_tips').innerHTML = msg['manyouUpdateTips'];
	}
}

function expiration() {

	if(!getMsg) {
		$('loadinginner').innerHTML = '<font color="red">' + expirationText + '</font>';
		clearTimeout(expirationTimeout);
	}
}