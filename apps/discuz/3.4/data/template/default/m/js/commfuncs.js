var FUNCS = {
	replyCommentPage: function (tId, pos, pId, author) {
		var pId = pId || '';
		var author = author || '';
		var extra = '';
		extra += '&viewpid=' + pId || '';
		extra += author ? '&author=' + encodeURIComponent(author) : '';
		TOOLS.openNewPage('?a=' + pos + '&tid=' + tId + '&login=yes&pos=yes' + extra);
	},
	newThreadPage: function (uid, fId) {
		TOOLS.openNewPage('?a=newthread' + '&fid=' + fId + '&login=yes');
	},
	jumpToLoginPage: function (url) {
		TOOLS.openNewPage('?' + url + '&login=yes');
	}
};

var initWXShare = function (opts) {
	if(SITE_INFO.openApi.wx != undefined){
		wx.config({
		    debug: false,
		    appId: SITE_INFO.openApi.wx.appid,
		    timestamp: SITE_INFO.openApi.wx.js_timestamp,
		    nonceStr: SITE_INFO.openApi.wx.js_noncestr,
		    signature: SITE_INFO.openApi.wx.js_signature,
		    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone']
		});
		wx.ready(function(){
			var url = window.location.href + '&siteid=' + SITE_ID;
			if (member_uid) {
				url += '&fromuid=' + member_uid;
			}
			url += '&source=';
			wx.onMenuShareTimeline({
			    title: opts.title,
			    link: url + 'pyq',
			    imgUrl: opts.img,
			    success: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    },
			    cancel: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    }
			});
			wx.onMenuShareAppMessage({
			    title: opts.title,
			    desc: opts.desc,
			    link: url + 'wxhy',
			    imgUrl: opts.img,
			    type: '',
			    dataUrl: '',
			    success: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    },
			    cancel: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    }
			});
			wx.onMenuShareQQ({
			    title: opts.title,
			    desc: opts.desc,
			    link: url + 'qq',
			    imgUrl: opts.img,
			    success: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    },
			    cancel: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    }
			});
			wx.onMenuShareWeibo({
				title: opts.title,
			    desc: opts.desc,
			    link: url + 'wb',
			    imgUrl: opts.img,
			    success: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    },
			    cancel: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    }
			});
			wx.onMenuShareQZone({
				title: opts.title,
			    desc: opts.desc,
			    link: url + 'qzone',
			    imgUrl: opts.img,
			    success: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    },
			    cancel: function () {
			    	$('.tipInfo').hide();
					$('.maskLayer').hide();
			    }
			});
		});
	}

};