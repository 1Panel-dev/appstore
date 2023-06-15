/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: html2dynamic.js 32720 2013-03-04 10:21:58Z zhangguosheng $
*/

function htmlGetUserStatus () {
	var x = new Ajax();
	var type = '', typeid = 0, arr = [];
	if(DYNAMICURL.indexOf('mod=topic') > -1) {
		type = 'topic';
		arr = DYNAMICURL.match(/topicid=(\d+)/);
		typeid = arr ? arr[1] : 0;
	} else if(DYNAMICURL.indexOf('mod=view') > -1) {
		type = 'article';
		arr = DYNAMICURL.match(/aid=(\d+)/);
		typeid = arr ? arr[1] : 0;
	}

	x.getJSON('misc.php?mod=userstatus&r='+(+(new Date())+'&type='+type+'&typeid='+typeid), function (s) {
		if(s) {
			for(var key in s) {
				switch(key) {
					case 'userstatus' :
						initUserstatus(s[key]);
						break;
					case 'qmenu' :
						initQmenu(s[key]);
						break;
					case 'diynav' :
						initDiynav(s[key]);
						break;
					case 'commentnum' :
					case 'viewnum' :
						initNum(key, s[key]);
						break;

				}
			}
		}
	});

	function initNum(name, val) {
		var obj = null;
		if(val > 0 && (obj = $('_'+name))) {
			obj.innerHTML = parseInt(val);
		}
	}

	function initUserstatus (code) {
		try{
			var lsform = $('lsform');
			if(lsform) {
				var i = 0, l = 0;
				var parent = lsform.parentNode;
				var dom = document.createElement('div');
				dom.innerHTML = code;
				var allNodes = dom.childNodes;
				parent.removeChild(lsform);
				for(i = 0,l = allNodes.length; i < l; i++) {
					parent.appendChild(allNodes[0]);
				}
				evalscript(code);
			}
		} catch (e) {
			debug('initUserstatus', e);
		}
	}

	function initQmenu(code) {
		try {
			var qmenu = $('qmenu_menu');
			if(qmenu) {
				var dom = document.createElement('div');
				dom.innerHTML = code;
				qmenu.parentNode.replaceChild(dom.childNodes[0], qmenu);
				evalscript(code);
			}
		} catch (e) {
			debug('initQmenu', e);
		}

	}

	function initDiynav(code) {
		try {
			var i = 0, l = 0;
			var dom = document.createElement('div');
			dom.innerHTML = code;
			var allNodes = dom.childNodes;
			var switchblind = $('switchblind');
			var insertdom = '';
			if(switchblind) {
				insertdom = switchblind.parentNode;
				for(i = 0,l = allNodes.length; i < l; i++) {
					insertdom.appendChild(allNodes[0]);
				}
			} else {
				var wp = $('wp');
				if(wp) {
					insertdom = wp.parentNode;
					for(i = 0,l = allNodes.length; i < l; i++) {
						insertdom.insertBefore(allNodes[0], wp);
					}
				}
			}
		} catch (e) {
			debug('initDiynav', e);
		}
	}

	function debug(name, e) {
		if(console) {
			console.log(name + ':' + e);
		}
	}
}

function htmlCheckUpdate() {
	var timestamp = (+ new Date())/1000;
	if(html_lostmodify && html_lostmodify < timestamp - 300) {
		$F('make_html', [SITEURL + DYNAMICURL + (DYNAMICURL.indexOf('?') < 0 ? '?' : '&') + '_makehtml'], 'makehtml');
	}
}