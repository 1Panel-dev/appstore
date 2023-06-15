var VDZ_COOKIE_PRE = "mobile_local_";

var SITE_ID = "";

var DOMAIN = "";

var API_URL = "";

var USER_AGENT = navigator.userAgent;

var OS = "";

var VERSION = "";

var NEW_MSG_COUNT = 0;

var ISLIKED = 0;

var SID = '';

var SITE_USERNAME = '';

var INFO_LOADED = false;


var baseInit = function () {

	var browser = {},
		webkit = USER_AGENT.match(/WebKit\/([\d.]+)/),
		android = USER_AGENT.match(/(Android)\s+([\d.]+)/),
		ipad = USER_AGENT.match(/(iPad).*OS\s([\d_]+)/),
		ipod = USER_AGENT.match(/(iPod).*OS\s([\d_]+)/),
		iphone = !ipod && !ipad && USER_AGENT.match(/(iPhone\sOS)\s([\d_]+)/),
		webos = USER_AGENT.match(/(webOS|hpwOS)[\s\/]([\d.]+)/),
		touchpad = webos && USER_AGENT.match(/TouchPad/),
		kindle = USER_AGENT.match(/Kindle\/([\d.]+)/),
		silk = USER_AGENT.match(/Silk\/([\d._]+)/),
		blackberry = USER_AGENT.match(/(BlackBerry).*Version\/([\d.]+)/),
		mqqbrowser = USER_AGENT.match(/MQQBrowser\/([\d.]+)/),
		chrome = USER_AGENT.match(/CriOS\/([\d.]+)/),
		opera = USER_AGENT.match(/Opera\/([\d.]+)/),
		safari = USER_AGENT.match(/Safari\/([\d.]+)/);

	if (android) {
		jQuery.os.android = true;
		jQuery.os.version = android[2];
		OS = "android";
		VERSION = android[2];
	}
	if (iphone) {
		jQuery.os.ios = jQuery.os.iphone = true;
		jQuery.os.version = iphone[2].replace(/_/g, '.');
		OS = "iphone_os";
		VERSION = iphone[2].replace(/_/g, '.');
	}
	if (ipad) {
		jQuery.os.ios = jQuery.os.ipad = true;
		jQuery.os.version = ipad[2].replace(/_/g, '.');
		OS = "ipad_os";
		VERSION = ipad[2].replace(/_/g, '.');
	}
	if (ipod) {
		jQuery.os.ios = jQuery.os.ipod = true;
		jQuery.os.version = ipod[2].replace(/_/g, '.');
		OS = "ipod_os";
		VERSION = ipod[2].replace(/_/g, '.');
	}



	SITE_ID = TOOLS.getQuery('siteid');

	if (SITE_INFO) {
		DOMAIN = SITE_INFO.siteUrl;
		API_URL = DOMAIN + "api/mobile/index.php?";//api url 可根据domain去拼接
	}

	if (TOOLS.getQuery('sid')) {
		SID = TOOLS.getQuery('sid');
	} else {
		SID = TOOLS.getcookie('mq_sid');
	}

	SITE_USERNAME = TOOLS.getcookie(COOKIE_PRE + 'siteusername');

	if (TOOLS.getQuery('a') != 'apitest') {
		TOOLS.getCheckInfo();
	}

};

var initIndexWXShare = function (opts) {

	var forumId = TOOLS.getQuery('fid');

};

document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.call('hideToolbar');
	WeixinJSBridge.call('showOptionMenu');
});

baseInit();

var IS_PAGE_BACK = false;