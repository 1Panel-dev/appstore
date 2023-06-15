/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: portal_diy_data.js 24360 2011-09-14 08:38:06Z zhangguosheng $
*/

var drag = new Drag();
drag.extend({
	'getBlocksTimer' : '',
	'blocks' : [],
	'blockDefaultClass' : [],
	'frameDefaultClass' : [],
	setSampleMenu : function () {
		this.addMenu('block', '数据', 'drag.openBlockEdit(event,"data")');
		this.addMenu('block', '更新', 'drag.blockForceUpdate(event)');
	},
	openBlockEdit : function (e,op) {
		e = Util.event(e);
		op = (op=='data') ? 'data' : 'block';
		var bid = e.aim.id.replace('cmd_portal_block_','');
		this.removeMenu();
		showWindow('showblock', 'portal.php?mod=portalcp&ac=block&op='+op+'&bid='+bid+'&tpl='+document.diyform.template.value, 'get', -1);
	},
	getBlockData : function (blockname) {
		var bid = this.dragObj.id;
		var eleid = bid;
		if (bid.indexOf('portal_block_') != -1) {
			eleid = 0;
		}else {
			bid = 0;
		}
		showWindow('showblock', 'portal.php?mod=portalcp&ac=block&op=block&classname='+blockname+'&bid='+bid+'&eleid='+eleid+'&tpl='+document.diyform.template.value,'get',-1);
		drag.initPosition();
		this.fn = '';
		return true;
	},
	stopSlide : function (id) {
		if (typeof slideshow == 'undefined' || typeof slideshow.entities == 'undefined') return false;
		var slidebox = $C('slidebox',$(id));
		if(slidebox && slidebox.length > 0) {
			if(slidebox[0].id) {
				var timer = slideshow.entities[slidebox[0].id].timer;
				if(timer) clearTimeout(timer);
				slideshow.entities[slidebox[0].id] = '';
			}
		}
	},
	init : function (sampleMode) {
		this.initCommon();
		$('samplepanel').innerHTML = '可直接管理模块数据 [<a href="javascript:;" onclick="spaceDiy.cancel();return false;" class="xi2">退出</a>]';
		this.setSampleMode(sampleMode);
		this.initSample();
		return true;
	},
	setClose : function () {},
	blockForceUpdate : function (e,all) {
		if ( typeof e !== 'string') {
			e = Util.event(e);
			var id = e.aim.id.replace('cmd_','');
		} else {
			var id = e;
		}
		if ($(id) == null) return false;
		var bid = id.replace('portal_block_', '');
		var bcontent = $(id+'_content');
		if (!bcontent) {
			bcontent = document.createElement('div');
			bcontent.id = id+'_content';
			bcontent.className = this.contentClass;
		}
		this.stopSlide(id);

		var height = Util.getFinallyStyle(bcontent, 'height');
		bcontent.style.lineHeight = height == 'auto' ? '' : (height == '0px' ? '20px' : height);
		var boldcontent = bcontent.innerHTML;
		bcontent.innerHTML = '<center>正在加载内容...</center>';
		var x = new Ajax();
		x.get('portal.php?mod=portalcp&ac=block&op=getblock&forceupdate=1&inajax=1&bid='+bid+'&tpl='+document.diyform.template.value, function(s) {
			if(s.indexOf('errorhandle_') != -1) {
				bcontent.innerHTML = boldcontent;
				runslideshow();
				showDialog('抱歉，您没有权限添加或编辑模块', 'alert');
				doane();
			} else {
				var obj = document.createElement('div');
				obj.innerHTML = s;
				bcontent.parentNode.removeChild(bcontent);
				$(id).innerHTML = obj.childNodes[0].innerHTML;
				evalscript(s);
				if(s.indexOf('runslideshow()') != -1) {runslideshow();}
				drag.initPosition();
				if (all) {drag.getBlocks();}
			}
		});
	}
});

var spaceDiy = new DIY();

spaceDiy.init(1);

function succeedhandle_diyform (url, message, values) {
	if (values['rejs'] == '1') {
		document.diyform.rejs.value = '';
		parent.$('preview_form').submit();
	}
	spaceDiy.enablePreviewButton();
	return false;
}