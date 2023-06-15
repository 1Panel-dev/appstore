/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: space_diy.js 23838 2011-08-11 06:51:58Z monkey $
*/

var drag = new Drag();
drag.extend({

	setDefalutMenu : function () {
		this.addMenu('default', '删除', 'drag.removeBlock(event)');
		this.addMenu('block', '属性', 'drag.openBlockEdit(event)');
	},
	removeBlock : function (e) {
		if ( typeof e !== 'string') {
			e = Util.event(e);
			id = e.aim.id.replace('cmd_','');
		} else {
			id = e;
		}
		if (!confirm('您确实要删除吗,删除以后将不可恢复')) return false;
		$(id).parentNode.removeChild($(id));
		var el = $('chk'+id);
		if (el != null) el.className = '';
		this.initPosition();
		this.initChkBlock();
	},
	initChkBlock : function (data) {
		if (typeof name == 'undefined' || data == null ) data = this.data;
		if ( data instanceof Frame) {
			this.initChkBlock(data['columns']);
		} else if (data instanceof Block) {
			var el = $('chk'+data.name);
			if (el != null) el.className = 'activity';
		} else if (typeof data == 'object') {
			for (var i in data) {
				this.initChkBlock(data[i]);
			}
		}
	},
	toggleBlock : function (blockname) {
		var el = $('chk'+blockname);
		if (el != null) {
			if (el.className == '') {
				this.getBlockData(blockname);
				el.className = 'activity';
			} else {
				this.removeBlock(blockname);
				this.initPosition();
			}
			this.setClose();
		}
	},
	getBlockData : function (blockname) {
		var el = $(blockname);
		if (el != null) {
			Util.show(blockname);
		} else {
			var x = new Ajax();
			x.get('home.php?mod=spacecp&ac=index&op=getblock&blockname='+blockname+'&inajax=1',function(s) {
				if (s) {
					el  = document.createElement("div");
					el.className = drag.blockClass + ' ' + drag.moveableObject;
					el.id = blockname;
					s = s.replace(/\<script.*\<\/script\>/ig,'<font color="red"> [javascript脚本保存后显示] </font>');
					el.innerHTML = s;
					var id = drag.data['diypage'][0]['columns']['frame1_left']['children'][0]['name'];
					$('frame1_left').insertBefore(el,$(id));
					drag.initPosition();
				}
			});
		}
	},
	openBlockEdit : function (e) {
		e = Util.event(e);
		var blockname = e.aim.id.replace('cmd_','');
		this.removeMenu();
		showWindow('showblock', 'home.php?mod=spacecp&ac=index&op=edit&blockname='+blockname,'get',0);
	}
});

var spaceDiy = new DIY();
spaceDiy.extend({
	save:function () {
		drag.clearClose();
		document.diyform.spacecss.value = this.getSpacecssStr();
		document.diyform.style.value = this.style;
		document.diyform.layoutdata.value = drag.getPositionStr();
		document.diyform.currentlayout.value = this.currentLayout;
		document.diyform.submit();

	},
	getdiy : function (type) {
		var type_ = type == 'image' ? 'diy' : type;
		if (type_) {
			var nav = $('controlnav').children;
			for (var i in nav) {
				if (nav[i].className == 'current') {
					nav[i].className = '';
				}
			}
			$('nav'+type_).className = 'current';

			var para = '&op='+type;
			if (arguments.length > 1) {
				for (i = 1; i < arguments.length; i++) {
					para += '&' + arguments[i] + '=' + arguments[++i];
				}
			}
			var ajaxtarget = type == 'image' ? 'diyimages' : '';
			var x = new Ajax();
			x.showId = ajaxtarget;
			x.get('home.php?mod=spacecp&ac=index'+para+'&inajax=1&ajaxtarget='+ajaxtarget,function(s) {
				if (s) {
					drag.deleteFrame(['pb', 'bpb', 'tpb', 'lpb']);
					if (type == 'image') {
						$('diyimages').innerHTML = s;
					} else {
						$('controlcontent').innerHTML = s;
						x.showId = 'controlcontent';
					}
					if (type_ == 'block') {
						drag.initPosition();
						drag.initChkBlock();
					} else if (type_ == 'layout') {
						$('layout'+spaceDiy.currentLayout).className = 'activity';
					} else if (type_ == 'diy' && type != 'image') {
						spaceDiy.setCurrentDiy(spaceDiy.currentDiy);
						if (spaceDiy.styleSheet.rules.length > 0) {
							Util.show('recover_button');
						}
					}

					var evaled = false;
					if(s.indexOf('ajaxerror') != -1) {
						evalscript(s);
						evaled = true;
					}
					if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
						if(x.showId) {
							ajaxupdateevents($(x.showId));
						}
					}
					if(!evaled) evalscript(s);
				}
			});
		}
	},
	menuChange : function (tabs, menu) {
		var tabobj = $(tabs);
		var aobj = tabobj.getElementsByTagName("li");
		for(i=0; i<aobj.length; i++) {
			aobj[i].className = '';
			$(aobj[i].id+'_content').style.display = 'none';
		}
		$(menu).className = 'a';
		$(menu+'_content').style.display="block";
		doane(null);
	},
	delIframe : function (){
		drag.deleteFrame(['m_ctc', 'm_bc', 'm_fc']);
	},
	showEditSpaceInfo : function () {
		$('spaceinfoshow').style.display='none';
		if (!$('spaceinfoedit')) {
			var dom = document.createElement('h2');
			dom.id = 'spaceinfoedit';
			Util.insertBefore(dom, $('spaceinfoshow'));
		}
		ajaxget('home.php?mod=spacecp&ac=index&op=getspaceinfo','spaceinfoedit');
	},
	spaceInfoCancel : function () {
		if ($('spaceinfoedit')) $('spaceinfoedit').style.display = 'none';
		if ($('spaceinfoshow')) $('spaceinfoshow').style.display = 'inline';
	},
	spaceInfoSave : function () {
		ajaxpost('savespaceinfo','spaceinfoshow','','onerror');
	},
	init : function () {
		drag.init();
		this.style = document.diyform.style.value;
		this.currentLayout = typeof document.diyform.currentlayout == 'undefined' ? '' : document.diyform.currentlayout.value;
		this.initStyleSheet();
		if (this.styleSheet.rules) this.initDiyStyle();
		this.initSpaceInfo();
	},
	initSpaceInfo : function () {
		this.spaceInfoCancel();
		if ($('spaceinfoshow')) {
			if (!$('infoedit')) {
				var dom = document.createElement('em');
				dom.id = 'infoedit';
				dom.innerHTML = '编辑';
				$('spacename').appendChild(dom);
			}
			$('infoedit').onmousedown = function () {spaceDiy.showEditSpaceInfo();};
		}
		if ($('nv')) {
			if(!$('nv').getElementsByTagName('li').length) {
				$('nv').getElementsByTagName('ul')[0].className = 'mininv';
			}
			$('nv').onmouseover = function () {spaceDiy.showEditNvInfo();};
			$('nv').onmouseout = function () {spaceDiy.hideEditNvInfo();};
		}
	},
	showEditNvInfo : function () {
		var nv = $('editnvinfo');
		if(!nv) {
			var dom = document.createElement('div');
			dom.innerHTML = '<span id="editnvinfo" class="edit" style="background-color:#336699;" onclick="spaceDiy.opNvEditInfo();">设置</span>';
			$('nv').appendChild(dom.childNodes[0]);
		} else {
			nv.style.display = '';
		}
	},
	hideEditNvInfo : function () {
		var nv = $('editnvinfo');
		if(nv) {
			nv.style.display = 'none';
		}
	},
	opNvEditInfo : function () {
		showWindow('showpersonalnv', 'home.php?mod=spacecp&ac=index&op=editnv','get',0);
	},
	getPersonalNv : function (show) {
		var x = new Ajax();
		show = !show ? '' : '&show=1';
		x.get('home.php?mod=spacecp&ac=index&op=getpersonalnv&inajax=1'+show, function(s) {
			if($('nv')) {
				$('hd').removeChild($('nv'));
			}
			var dom = document.createElement('div');
			dom.innerHTML = !s ? '&nbsp;' : s;
			$('hd').appendChild(dom.childNodes[0]);
			spaceDiy.initSpaceInfo();
		});
	}
});

spaceDiy.init();