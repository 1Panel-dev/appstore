/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: portal_diy.js 31093 2012-07-16 03:54:34Z zhangguosheng $
*/

var drag = new Drag();
drag.extend({
	'getBlocksTimer' : '',
	'blocks' : [],
	'blockDefaultClass' : [{'key':'选择样式','value':''},{'key':'无边框且无边距','value':'cl_block_bm'},{'key':'样式1','value':'xbs_1'},{'key':'样式2','value':'xbs xbs_2'},{'key':'样式3','value':'xbs xbs_3'},{'key':'样式4','value':'xbs xbs_4'},{'key':'样式5','value':'xbs xbs_5'},{'key':'样式6','value':'xbs xbs_6'},{'key':'样式7','value':'xbs xbs_7'}],
	'frameDefaultClass' : [{'key':'选择样式','value':''},{'key':'无边框且无边距','value':'cl_frame_bm'},{'key':'无边框框架','value':'xfs xfs_nbd'},{'key':'样式1','value':'xfs xfs_1'},{'key':'样式2','value':'xfs xfs_2'},{'key':'样式3','value':'xfs xfs_3'},{'key':'样式4','value':'xfs xfs_4'},{'key':'样式5','value':'xfs xfs_5'}],
	setDefalutMenu : function () {
		this.addMenu('default','标题','drag.openTitleEdit(event)');
		this.addMenu('default','样式','drag.openStyleEdit(event)');
		this.addMenu('default', '删除', 'drag.removeBlock(event)');
		this.addMenu('block', '属性', 'drag.openBlockEdit(event)');
		this.addMenu('block', '数据', 'drag.openBlockEdit(event,"data")');
		this.addMenu('block', '更新', 'drag.blockForceUpdate(event)');
		this.addMenu('frame', '导出', 'drag.frameExport(event)');
		this.addMenu('tab', '导出', 'drag.frameExport(event)');
	},
	setSampleMenu : function () {
		this.addMenu('block', '属性', 'drag.openBlockEdit(event)');
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
	getDiyClassName : function (id,index) {
		var obj = this.getObjByName(id);
		var ele = $(id);
		var eleClassName = ele.className.replace(/ {2,}/g,' ');
		var className = '',srcClassName = '';
		if (obj instanceof Block) {
			className = eleClassName.split(this.blockClass+' ');
			srcClassName = this.blockClass;
		} else if(obj instanceof Tab) {
			className = eleClassName.split(this.tabClass+' ');
			srcClassName = this.tabClass;
		} else if(obj instanceof Frame) {
			className = eleClassName.split(this.frameClass+' ');
			srcClassName = this.frameClass;
		}
		if (index != null && index<className.length) {
			className = className[index].replace(/^ | $/g,'');
		} else {
			className.push(srcClassName);
		}
		return className;
	},
	getOption : function (arr,value) {
		var html = '';
		for (var i in arr) {
			if (typeof arr[i] == 'function') continue;
			var selected = arr[i]['value'] == value ? ' selected="selected"' : '';
			html += '<option value="'+arr[i]['value']+'"'+selected+'>'+arr[i]['key']+'</option>';
		}
		return html;
	},
	getRule : function (selector,attr) {
		selector = spaceDiy.checkSelector(selector);
		var value = (!selector || !attr) ? '' : spaceDiy.styleSheet.getRule(selector, attr);
		return value;
	},
	openStyleEdit : function (e) {
		e = Util.event(e);
		var id = e.aim.id.replace('cmd_','');
		var obj = this.getObjByName(id);
		var objType = obj instanceof Block ? 1 : 0;
		var bgcolor = '',bgimage = '',bgrepeat = '',html = '',diyClassName = '',fontcolor = '',fontsize = '',linkcolor = '',linkfontsize = '';
		var bdtstyle = '',bdtwidth = '',bdtcolor = '',bdrstyle = '',bdrwidth = '',bdrcolor = '',bdbstyle = '',bdbwidth = '',bdbcolor = '',bdlstyle = '',bdlwidth = '',bdlcolor = '';
		var margint = '',marginr = '',marginb = '',marginl = '',cmargint = '',cmarginr = '',cmarginb = '',cmarginl ='';
		var selector = '#'+id;
		bgcolor = this.getRule(selector, 'backgroundColor');
		bgimage = this.getRule(selector, 'backgroundImage');
		bgrepeat = this.getRule(selector, 'backgroundRepeat');
		bgimage = bgimage && bgimage != 'none' ? Util.trimUrl(bgimage) : '';

		fontcolor = this.getRule(selector+' .'+this.contentClass, 'color');
		fontsize = this.getRule(selector+' .'+this.contentClass, 'fontSize').replace('px','');
		var linkSelector = spaceDiy.checkSelector(selector+ ' .'+this.contentClass+' a');
		linkcolor = this.getRule(linkSelector, 'color');
		linkfontsize = this.getRule(linkSelector, 'fontSize').replace('px','');
		fontcolor = Util.formatColor(fontcolor);
		linkcolor = Util.formatColor(linkcolor);

		bdtstyle = this.getRule(selector, 'borderTopStyle');
		bdrstyle = this.getRule(selector, 'borderRightStyle');
		bdbstyle = this.getRule(selector, 'borderBottomStyle');
		bdlstyle = this.getRule(selector, 'borderLeftStyle');

		bdtwidth = this.getRule(selector, 'borderTopWidth');
		bdrwidth = this.getRule(selector, 'borderRightWidth');
		bdbwidth = this.getRule(selector, 'borderBottomWidth');
		bdlwidth = this.getRule(selector, 'borderLeftWidth');

		bdtcolor = this.getRule(selector, 'borderTopColor');
		bdrcolor = this.getRule(selector, 'borderRightColor');
		bdbcolor = this.getRule(selector, 'borderBottomColor');
		bdlcolor = this.getRule(selector, 'borderLeftColor');

		bgcolor = Util.formatColor(bgcolor);
		bdtcolor = Util.formatColor(bdtcolor);
		bdrcolor = Util.formatColor(bdrcolor);
		bdbcolor = Util.formatColor(bdbcolor);
		bdlcolor = Util.formatColor(bdlcolor);

		margint = this.getRule(selector, 'marginTop').replace('px','');
		marginr = this.getRule(selector, 'marginRight').replace('px','');
		marginb = this.getRule(selector, 'marginBottom').replace('px','');
		marginl = this.getRule(selector, 'marginLeft').replace('px','');

		if (objType == 1) {
			selector = selector + ' .'+this.contentClass;
			cmargint = this.getRule(selector, 'marginTop').replace('px','');
			cmarginr = this.getRule(selector, 'marginRight').replace('px','');
			cmarginb = this.getRule(selector, 'marginBottom').replace('px','');
			cmarginl = this.getRule(selector, 'marginLeft').replace('px','');
		}

		diyClassName = this.getDiyClassName(id,0);

		var widtharr = [];
		for (var k=0;k<11;k++) {
			var key = k+'px';
			widtharr.push({'key':key,'value':key});
		}

		var bigarr = [];
		for (var k=0;k<31;k++) {
			key = k+'px';
			bigarr.push({'key':key,'value':key});
		}
		var repeatarr = [{'key':'平铺','value':'repeat'},{'key':'不平铺','value':'no-repeat'},{'key':'横向平铺','value':'repeat-x'},{'key':'纵向平铺','value':'repeat-y'}];
		var stylearr = [{'key':'无样式','value':'none'},{'key':'实线','value':'solid'},{'key':'点线','value':'dotted'},{'key':'虚线','value':'dashed'}];
		var table = '<table class="tfm">';
		table += '<tr><th>字体</th><td><input type="text" id="fontsize" class="px p_fre vm" value="'+fontsize+'" size="2" />px <input type="text" id="fontcolor" class="px p_fre vm" value="'+fontcolor+'" size="2" />';
		table += getColorPalette(id+'_fontPalette', 'fontcolor' ,fontcolor)+'</td></tr>';
		table += '<tr><th>链接</th><td><input type="text" id="linkfontsize" class="px p_fre vm" value="'+linkfontsize+'" size="2" />px <input type="text" id="linkcolor" class="px p_fre vm" value="'+linkcolor+'" size="2" />';
		table += getColorPalette(id+'_linkPalette', 'linkcolor' ,linkcolor)+'</td></tr>';

		var ulclass = 'borderul', opchecked = '';
		if (bdtwidth != '' || bdtcolor != '' ) {
			ulclass = 'borderula';
			opchecked = ' checked="checked"';
		}

		table += '<tr><th>边框</th><td><ul id="borderul" class="'+ulclass+'">';
		table += '<li><label>上</label><select class="ps vm" id="bdtwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdtwidth)+'</select>';
		table += ' <select class="ps vm" id="bdtstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdtstyle)+'</select>';
		table += ' 颜色 <input type="text" id="bdtcolor" class="px p_fre vm" value="'+bdtcolor+'" size="7" />';
		table += getColorPalette(id+'_bdtPalette', 'bdtcolor' ,bdtcolor)+'</li>';

		table += '<li class="bordera mtn"><label>右</label><select class="ps vm" id="bdrwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdrwidth)+'</select>';
		table += ' <select class="ps vm" id="bdrstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdrstyle)+'</select>';
		table += ' 颜色 <input type="text" id="bdrcolor" class="px p_fre vm" value="'+bdrcolor+'" size="7" />';
		table += getColorPalette(id+'_bdrPalette', 'bdrcolor' ,bdrcolor)+'</li>';

		table += '<li class="bordera mtn"><label>下</label><select class="ps vm" id="bdbwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdbwidth)+'</select>';
		table += ' <select class="ps vm" id="bdbstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdbstyle)+'</select>';
		table += ' 颜色 <input type="text" id="bdbcolor" class="px p_fre vm" value="'+bdbcolor+'" size="7" />';
		table += getColorPalette(id+'_bdbPalette', 'bdbcolor' ,bdbcolor)+'</li>';

		table += '<li class="bordera mtn"><label>左</label><select class="ps vm" id="bdlwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdlwidth)+'</select>';
		table += ' <select class="ps vm" id="bdlstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdlstyle)+'</select>';
		table += ' 颜色 <input type="text" id="bdlcolor" class="px p_fre vm" value="'+bdlcolor+'" size="7" />';
		table += getColorPalette(id+'_bdlPalette', 'bdlcolor' ,bdlcolor)+'</li>';
		table += '</ul><p class="ptm"><label><input id="borderop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'borderul\').className = $(\'borderul\').className == \'borderul\' ? \'borderula\' : \'borderul\'">分别设置</label></p></td></tr>';

		bigarr = [];
		for (k=-20;k<31;k++) {
			key = k+'px';
			bigarr.push({'key':key,'value':key});
		}

		ulclass = 'borderul', opchecked = '';
		if (margint != '') {
			ulclass = 'borderula';
			opchecked = ' checked="checked"';
		}

		table += '<tr><th>外边距</th><td><div id="margindiv" class="'+ulclass+'"><span><label>上</label> <input type="text" id="margint" class="px p_fre vm" value="'+margint+'" size="1"/>px </span>';
		table += '<span class="bordera"><label>右</label> <input type="text" id="marginr" class="px p_fre vm" value="'+marginr+'" size="1" />px </span>';
		table += '<span class="bordera"><label>下</label> <input type="text" id="marginb" class="px p_fre vm" value="'+marginb+'" size="1" />px </span>';
		table += '<span class="bordera"><label>左</label> <input type="text" id="marginl" class="px p_fre vm" value="'+marginl+'" size="1" />px</span>';
		table += '</div><p class="ptm"><label><input id="marginop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'margindiv\').className = $(\'margindiv\').className == \'borderul\' ? \'borderula\' : \'borderul\'">分别设置</label></p></td></tr>';

		if (objType == 1) {

			ulclass = 'borderul', opchecked = '';
			if (cmargint != '') {
				ulclass = 'borderula';
				opchecked = ' checked="checked"';
			}

			table += '<tr><th>内边距</th><td><div id="cmargindiv" class="'+ulclass+'"><span><label>上</label> <input class="px p_fre" id="cmargint" value="'+cmargint+'" size="1" />px </span>';
			table += '<span class="bordera"><label>右</label> <input class="px p_fre" id="cmarginr" value="'+cmarginr+'" size="1" />px </span>';
			table += '<span class="bordera"><label>下</label> <input class="px p_fre" id="cmarginb" value="'+cmarginb+'" size="1" />px </span>';
			table += '<span class="bordera"><label>左</label> <input class="px p_fre" id="cmarginl" value="'+cmarginl+'" size="1" />px </span>';
			table += '</div><p class="ptm"><label><input id="cmarginop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'cmargindiv\').className = $(\'cmargindiv\').className == \'borderul\' ? \'borderula\' : \'borderul\'"> 分别设置</label></p></td></tr>';
		}
		table += '<tr><th>背景颜色</th><td><input type="text" id="bgcolor" class="px p_fre vm" value="'+bgcolor+'" size="4" />';
		table += getColorPalette(id+'_bgcPalette', 'bgcolor' ,bgcolor)+'</td></tr>';
		table += '<tr><th>背景图片</th><td><input type="text" id="bgimage" class="px p_fre vm" value="'+bgimage+'" size="25" /> <select class="ps vm" id="bgrepeat" >'+this.getOption(repeatarr,bgrepeat)+'</select></td></tr>';
		var classarr = objType == 1 ? this.blockDefaultClass : this.frameDefaultClass;
		table += '<tr><th>指定class</th><td><input type="text" id="diyClassName" class="px p_fre" value="'+diyClassName+'" size="8" /> <select class="ps vm" id="bgrepeat" onchange="$(\'diyClassName\').value=this.value;" >'+this.getOption(classarr, diyClassName)+'</select></td></tr>';
		table += '</table>';

		var wname = objType ? '模块' : '框架';
		html = '<div class="c diywin" style="width:450px;position:relative;">'+table+'</div>';
		var h = '<h3 class="flb"><em>编辑'+wname+'样式</em><span><a href="javascript:;" class="flbc" onclick="drag.closeStyleEdit(\''+id+'\');return false;" title="关闭">\n\
			关闭</a></span></h3>';
		var f = '<p class="o pns"><button onclick="drag.saveStyle(\''+id+'\');drag.closeStyleEdit(\''+id+'\');" class="pn pnc" value="true">\n\
			<strong>确定</strong></button><button onclick="drag.closeStyleEdit(\''+id+'\')" class="pn" value="true"><strong>取消</strong></button></p>';
		this.removeMenu(e);
		showWindow('eleStyle',h + html + f, 'html', 0);
	},
	closeStyleEdit : function (id) {
		this.deleteFrame([id+'_bgcPalette',id+'_bdtPalette',id+'_bdrPalette',id+'_bdbPalette',id+'_bdlPalette',id+'_fontPalette',id+'_linkPalette']);
		hideWindow('eleStyle');
	},
	saveStyle : function (id) {
		var className = this.getDiyClassName(id);
		var diyClassName = $('diyClassName').value;
		$(id).className = diyClassName+' '+className[2]+' '+className[1];
		var obj = this.getObjByName(id);
		var objType = obj instanceof Block ? 1 : 0;

		if (objType == 1) this.saveBlockClassName(id,diyClassName);

		var selector = '#'+id;
		var random = Math.random();
		spaceDiy.setStyle(selector, 'background-color', $('bgcolor').value, random);
		var bgimage = $('bgimage').value && $('bgimage') != 'none' ? Util.url($('bgimage').value) : '';
		var bgrepeat = bgimage ? $('bgrepeat').value : '';
		if ($('bgcolor').value != '' && bgimage == '') bgimage = 'none';
		spaceDiy.setStyle(selector, 'background-image', bgimage, random);
		spaceDiy.setStyle(selector, 'background-repeat', bgrepeat, random);
		spaceDiy.setStyle(selector+' .'+this.contentClass, 'color', $('fontcolor').value, random);
		spaceDiy.setStyle(selector+' .'+this.contentClass, 'font-size', this.formatValue('fontsize'), random);
		spaceDiy.setStyle(spaceDiy.checkSelector(selector+' .'+this.contentClass+' a'), 'color', $('linkcolor').value, random);
		var linkfontsize = parseInt($('linkfontsize').value);
		linkfontsize = isNaN(linkfontsize) ? '' : linkfontsize+'px';
		spaceDiy.setStyle(spaceDiy.checkSelector(selector+' .'+this.contentClass+' a'), 'font-size', this.formatValue('linkfontsize'), random);

		if ($('borderop').checked) {
			var bdtwidth = $('bdtwidth').value,bdrwidth = $('bdrwidth').value,bdbwidth = $('bdbwidth').value,bdlwidth = $('bdlwidth').value;
			var bdtstyle = $('bdtstyle').value,bdrstyle = $('bdrstyle').value,bdbstyle = $('bdbstyle').value,bdlstyle = $('bdlstyle').value;
			var bdtcolor = $('bdtcolor').value,bdrcolor = $('bdrcolor').value,bdbcolor = $('bdbcolor').value,bdlcolor = $('bdlcolor').value;
		} else {
			bdlwidth = bdbwidth = bdrwidth = bdtwidth = $('bdtwidth').value;
			bdlstyle = bdbstyle = bdrstyle = bdtstyle = $('bdtstyle').value;
			bdlcolor = bdbcolor = bdrcolor = bdtcolor = $('bdtcolor').value;
		}
		spaceDiy.setStyle(selector, 'border', '', random);
		spaceDiy.setStyle(selector, 'border-top-width', bdtwidth, random);
		spaceDiy.setStyle(selector, 'border-right-width', bdrwidth, random);
		spaceDiy.setStyle(selector, 'border-bottom-width', bdbwidth, random);
		spaceDiy.setStyle(selector, 'border-left-width', bdlwidth, random);

		spaceDiy.setStyle(selector, 'border-top-style', bdtstyle, random);
		spaceDiy.setStyle(selector, 'border-right-style', bdrstyle, random);
		spaceDiy.setStyle(selector, 'border-bottom-style', bdbstyle, random);
		spaceDiy.setStyle(selector, 'border-left-style', bdlstyle, random);

		spaceDiy.setStyle(selector, 'border-top-color', bdtcolor, random);
		spaceDiy.setStyle(selector, 'border-right-color', bdrcolor, random);
		spaceDiy.setStyle(selector, 'border-bottom-color', bdbcolor, random);
		spaceDiy.setStyle(selector, 'border-left-color', bdlcolor, random);

		if ($('marginop').checked) {
			var margint = this.formatValue('margint'),marginr = this.formatValue('marginr'), marginb = this.formatValue('marginb'), marginl = this.formatValue('marginl');
		} else {
			marginl = marginb = marginr = margint = this.formatValue('margint');
		}
		spaceDiy.setStyle(selector, 'margin-top',margint, random);
		spaceDiy.setStyle(selector, 'margin-right', marginr, random);
		spaceDiy.setStyle(selector, 'margin-bottom', marginb, random);
		spaceDiy.setStyle(selector, 'margin-left', marginl, random);

		if (objType == 1) {
			if ($('cmarginop').checked) {
				var cmargint = this.formatValue('cmargint'),cmarginr = this.formatValue('cmarginr'), cmarginb = this.formatValue('cmarginb'), cmarginl = this.formatValue('cmarginl');
			} else {
				cmarginl = cmarginb = cmarginr = cmargint = this.formatValue('cmargint');
			}
			selector = selector + ' .'+this.contentClass;
			spaceDiy.setStyle(selector, 'margin-top', cmargint, random);
			spaceDiy.setStyle(selector, 'margin-right', cmarginr, random);
			spaceDiy.setStyle(selector, 'margin-bottom', cmarginb, random);
			spaceDiy.setStyle(selector, 'margin-left', cmarginl, random);
		}

		this.setClose();
	},
	formatValue : function(id) {
		var value = '';
		if ($(id)) {
			value = parseInt($(id).value);
			value = isNaN(value) ? '' : value+'px';
		}
		return value;
	},
	saveBlockClassName : function(id,className){
		if (!$('saveblockclassname')){
			var dom  = document.createElement('div');
			dom.innerHTML = '<form id="saveblockclassname" method="post" action=""><input type="hidden" name="classname" value="" />\n\
				<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="saveclassnamesubmit" value="true"/></form>';
			$('append_parent').appendChild(dom.childNodes[0]);
		}
		$('saveblockclassname').action = 'portal.php?mod=portalcp&ac=block&op=saveblockclassname&bid='+id.replace('portal_block_','');
		document.forms.saveblockclassname.classname.value = className;
		ajaxpost('saveblockclassname','ajaxwaitid');
	},
	closeTitleEdit : function (fid) {
		this.deleteFrame(fid+'bgPalette_0');
		for (var i = 0 ; i<=10; i++) {
			this.deleteFrame(fid+'Palette_'+i);
		}
		hideWindow('frameTitle');
	},
	openTitleEdit : function (e) {
		if (typeof e == 'object') {
			e = Util.event(e);
			var fid = e.aim.id.replace('cmd_','');
		} else {
			fid = e;
		}
		var obj = this.getObjByName(fid);
		var titlename = obj instanceof Block ? '模块' : '框架';
		var repeatarr = [{'key':'平铺','value':'repeat'},{'key':'不平铺','value':'no-repeat'},{'key':'横向平铺','value':'repeat-x'},{'key':'纵向平铺','value':'repeat-y'}];

		var len = obj.titles.length;
		var bgimage = obj.titles.style && obj.titles.style['background-image'] ? obj.titles.style['background-image'] : '';
		bgimage = bgimage != 'none' ? Util.trimUrl(bgimage) : '';
		var bgcolor = obj.titles.style && obj.titles.style['background-color'] ? obj.titles.style['background-color'] : '';
		bgcolor = Util.formatColor(bgcolor);
		var bgrepeat = obj.titles.style && obj.titles.style['background-repeat'] ? obj.titles.style['background-repeat'] : '';

		var common = '<table class="tfm">';
		common += '<tr><th>背景图片:</th><td><input type="text" id="titleBgImage" class="px p_fre" value="'+bgimage+'" /> <select class="ps vm" id="titleBgRepeat" >'+this.getOption(repeatarr,bgrepeat)+'</select></td></tr>';
		common += '<tr><th>背景颜色:</th><td><input type="text" id="titleBgColor" class="px p_fre" value="'+bgcolor+'" size="7" />';
		common += getColorPalette(fid+'bgPalette_0', 'titleBgColor' ,bgcolor)+'</td></tr>';
		if (obj instanceof Tab) {
			var switchArr = [{'key':'点击','value':'click'},{'key':'滑过','value':'mouseover'}];
			var switchType = obj.titles['switchType'] ? obj.titles['switchType'][0] : 'click';
			common += '<tr><th>切换类型:</th><td><select class="ps" id="switchType" >'+this.getOption(switchArr,switchType)+'</select></td></tr>';
		}
		common += '</table><hr class="l">';
		var li = '';
		li += '<div id="titleInput_0"><table class="tfm"><tr><th>'+titlename+'标题:</th><td><input type="text" id="titleText_0" class="px p_fre" value="`title`" /></td></tr>';
		li += '<tr><th>链接:</th><td><input type="text" id="titleLink_0" class="px p_fre" value="`link`" /></td></tr>';
		li += '<tr><th>图片:</th><td><input type="text" id="titleSrc_0" class="px p_fre" value="`src`" /></td></tr>';
		li += '<tr><th>位置:</th><td><select id="titleFloat_0" class="ps vm"><option value="" `left`>居左</option><option value="right" `right`>居右</option></select>';
		li += '&nbsp;&nbsp;偏移量: <input type="text" id="titleMargin_0" class="px p_fre vm" value="`margin`" size="2" />px</td></tr>';
		li += '<tr><th>字体:</th><td><select class="ps vm" id="titleSize_0" ><option value="">大小</option>`size`</select>';
		li += '&nbsp;&nbsp;颜色: <input type="text" id="titleColor_0" class="px p_fre vm" value="`color`" size="4" />';
		li += getColorPalette(fid+'Palette_0', 'titleColor_0' ,'`color`');
		li += '</td></tr><tr><td colspan="2"><hr class="l"></td></tr></table></div>';
		var html = '';
		if (obj.titles['first']) {
			html = this.getTitleHtml(obj, 'first', li);
		}
		for (var i = 0; i < len; i++ ) {
			html += this.getTitleHtml(obj, i, li);
		}
		if (!html) {
			var bigarr = [];
			for (var k=7;k<27;k++) {
				var key = k+'px';
				bigarr.push({'key':key,'value':key});
			}
			var ssize = this.getOption(bigarr,ssize);
			html = li.replace('`size`', ssize).replace(/`\w+`/g, '');
		}

		var c = len + 1;
			html = '<div class="c diywin" style="width:450px;height:400px; overflow:auto;"><table cellspacing="0" cellpadding="0" class="tfm pns"><tr><th></th><td><button type="button" id="addTitleInput" class="pn" onclick="drag.addTitleInput('+c+');"><em>添加新标题</em></button></td></tr></table><div id="titleEdit">'+html+common+'</div></div>';
		var h = '<h3 class="flb"><em>编辑'+titlename+'标题</em><span><a href="javascript:;" class="flbc" onclick="drag.closeTitleEdit(\''+fid+'\');return false;" title="关闭">\n\
			关闭</a></span></h3>';
		var f = '<p class="o pns"><button onclick="drag.saveTitleEdit(\''+fid+'\');drag.closeTitleEdit(\''+fid+'\');" class="pn pnc" value="true">\n\
			<strong>确定</strong></button><button onclick="drag.closeTitleEdit(\''+fid+'\')" class="pn" value="true"><strong>取消</strong></button></p>';
		this.removeMenu(e);
		showWindow('frameTitle',h + html + f, 'html', 0);
	},
	getTitleHtml : function (obj, i, li) {
		var shtml = '',stitle = '',slink = '',sfloat = '',ssize = '',scolor = '',margin = '',src = '';
		var c = i == 'first' ? '0' : i+1;
		stitle = obj.titles[i]['text'] ? obj.titles[i]['text'] : '';
		slink = obj.titles[i]['href'] ? obj.titles[i]['href'] : '';
		sfloat = obj.titles[i]['float'] ? obj.titles[i]['float'] : '';
		margin = obj.titles[i]['margin'] ? obj.titles[i]['margin'] : '';
		ssize = obj.titles[i]['font-size'] ? obj.titles[i]['font-size']+'px' : '';
		scolor = obj.titles[i]['color'] ? obj.titles[i]['color'] : '';
		src = obj.titles[i]['src'] ? obj.titles[i]['src'] : '';

		var bigarr = [];
		for (var k=7;k<27;k++) {
			var key = k+'px';
			bigarr.push({'key':key,'value':key});
		}
		ssize = this.getOption(bigarr,ssize);

		shtml = li.replace(/_0/g, '_' + c).replace('`title`', stitle).replace('`link`', slink).replace('`size`', ssize).replace('`src`',src);
		var left = sfloat == '' ? 'selected' : '';
		var right = sfloat == 'right' ? 'selected' : '';
		scolor = Util.formatColor(scolor);
		shtml = shtml.replace(/`color`/g, scolor).replace('`left`', left).replace('`right`', right).replace('`margin`', margin);
		return shtml;
	},
	addTitleInput : function (c) {
		if (c  > 10) return false;
		var pre = $('titleInput_'+(c-1));
		var dom = document.createElement('div');
		dom.className = 'tfm';
		var exp = new RegExp('_'+(c-1), 'g');
		dom.id = 'titleInput_'+c;
		dom.innerHTML = pre.innerHTML.replace(exp, '_'+c);
		Util.insertAfter(dom, pre);
		$('addTitleInput').onclick = function () {drag.addTitleInput(c+1)};
	},
	saveTitleEdit : function (fid) {
		var obj = this.getObjByName(fid);
		var ele  = $(fid);
		var children = ele.childNodes;
		var title = first = '';
		var hastitle = 0;
		var c = 0;
		for (var i in children) {
			if (typeof children[i] == 'object' && Util.hasClass(children[i], this.titleClass)) {
				title = children[i];
				break;
			}
		}
		if (title) {
			var arrDel = [];
			for (var i in title.childNodes) {
				if (typeof title.childNodes[i] == 'object' && Util.hasClass(title.childNodes[i], this.titleTextClass)) {
					first = title.childNodes[i];
					this._createTitleHtml(first, c);
					if (first.innerHTML != '') hastitle = 1;
				} else if (typeof title.childNodes[i] == 'object' && !Util.hasClass(title.childNodes[i], this.moveableObject)) {
					arrDel.push(title.childNodes[i]);
				}
			}
			for (var i = 0; i < arrDel.length; i++) {
				title.removeChild(arrDel[i]);
			}
		} else {
			var titleClassName = '';
			if(obj instanceof Tab) {
				titleClassName = 'tab-';
			} else if(obj instanceof Frame) {
				titleClassName = 'frame-';
			} else if(obj instanceof Block) {
				titleClassName = 'block';
			}
			title = document.createElement('div');
			title.className = titleClassName + 'title' + ' '+ this.titleClass;
			ele.insertBefore(title,ele.firstChild);
		}
		if (!first) {
			var first = document.createElement('span');
			first.className = this.titleTextClass;
			this._createTitleHtml(first, c);
			if (first.innerHTML != '') {
				title.insertBefore(first, title.firstChild);
				hastitle = 1;
			}
		}
		while ($('titleText_'+(++c))) {
			var dom = document.createElement('span');
			dom.className = 'subtitle';
			this._createTitleHtml(dom, c);
			if (dom.innerHTML != '') {
				if (dom.innerHTML) Util.insertAfter(dom, first);
				first = dom;
				hastitle = 1;
			}
		}

		var titleBgImage = $('titleBgImage').value;
		titleBgImage = titleBgImage && titleBgImage != 'none' ? Util.url(titleBgImage) : '';
		if ($('titleBgColor').value != '' && titleBgImage == '') titleBgImage = 'none';
		title.style['backgroundImage'] = titleBgImage;
		if (titleBgImage) {
			title.style['backgroundRepeat'] = $('titleBgRepeat').value;
		}
		title.style['backgroundColor'] = $('titleBgColor').value;
		if ($('switchType')) {
			title.switchType = [];
			title.switchType[0] = $('switchType').value ? $('switchType').value : 'click';
			title.setAttribute('switchtype',title.switchType[0]);
		}

		obj.titles = [];
		if (hastitle == 1) {
			this._initTitle(obj,title);
		} else {
			if (!(obj instanceof Tab)) title.parentNode.removeChild(title);
			title = '';
			this.initPosition();
		}
		if (obj instanceof Block) this.saveBlockTitle(fid,title);
		this.setClose();

	},
	_createTitleHtml : function (ele,tid) {
		var html = '',img = '';
		tid = '_' + tid ;
		var ttext = $('titleText'+tid).value;
		var tlink = $('titleLink'+tid).value;
		var tfloat = $('titleFloat'+tid).value;
		var tmargin_ = tfloat != '' ? tfloat : 'left';
		var tmargin = $('titleMargin'+tid).value;
		var tsize = $('titleSize'+tid).value;
		var tcolor = $('titleColor'+tid).value;
		var src = $('titleSrc'+tid).value;
		var divStyle = 'float:'+tfloat+';margin-'+tmargin_+':'+tmargin+'px;font-size:'+tsize;
		var aStyle = 'color:'+tcolor+' !important;';
		if (src) {
			img = '<img class="vm" src="'+src+'" alt="'+ttext+'" />';
		}
		if (ttext || img) {
			if (tlink) {
				Util.setStyle(ele, divStyle);
				html = '<a href='+tlink+' target="_blank" style="'+aStyle+'">'+img+ttext+'</a>';
			} else {
				Util.setStyle(ele, divStyle+';'+aStyle);
				html = img+ttext;
			}
		}
		ele.innerHTML = html;
		return true;
	},
	saveBlockTitle : function (id,title) {
		if (!$('saveblocktitle')){
			var dom  = document.createElement('div');
			dom.innerHTML = '<form id="saveblocktitle" method="post" action=""><input type="hidden" name="title" value="" />\n\
				<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="savetitlesubmit" value="true"/></form>';
			$('append_parent').appendChild(dom.childNodes[0]);
		}
		$('saveblocktitle').action = 'portal.php?mod=portalcp&ac=block&op=saveblocktitle&bid='+id.replace('portal_block_','');
		var html = !title ? '' : title.outerHTML;
		document.forms.saveblocktitle.title.value = html;
		ajaxpost('saveblocktitle','ajaxwaitid');
	},
	removeBlock : function (e, flag) {
		if ( typeof e !== 'string') {
			e = Util.event(e);
			var id = e.aim.id.replace('cmd_','');
		} else {
			var id = e;
		}
		if ($(id) == null) return false;
		var obj = this.getObjByName(id);
		if (!flag) {
			if (!confirm('您确实要删除吗,删除以后将不可恢复')) return false;
		}
		if (obj instanceof Block) {
			this.delBlock(id);
		} else if (obj instanceof Frame) {
			this.delFrame(obj);
		}
		$(id).parentNode.removeChild($(id));
		var content = $(id+'_content');
		if(content) {
			content.parentNode.removeChild(content);
		}
		this.setClose();
		this.initPosition();
		this.initChkBlock();
	},
	delBlock : function (bid) {
		spaceDiy.removeCssSelector('#'+bid);
		this.stopSlide(bid);
	},
	delFrame : function (frame) {
		spaceDiy.removeCssSelector('#'+frame.name);
		for (var i in frame['columns']) {
			if (frame['columns'][i] instanceof Column) {
				var children = frame['columns'][i]['children'];
				for (var j in children) {
					if (children[j] instanceof Frame) {
						this.delFrame(children[j]);
					} else if (children[j] instanceof Block) {
						this.delBlock(children[j]['name']);
					}
				}
			}
		}
		this.setClose();
	},
	initChkBlock : function (data) {
		if (typeof name == 'undefined' || data == null ) data = this.data;
		if ( data instanceof Frame) {
			this.initChkBlock(data['columns']);
		} else if (data instanceof Block) {
			var el = $('chk'+data.name);
			if (el != null) el.checked = true;
		} else if (typeof data == 'object') {
			for (var i in data) {
				this.initChkBlock(data[i]);
			}
		}
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
	},
	frameExport : function (e) {
		var flag = true;
		if (drag.isChange) {
			flag = confirm('您已经做过修改，请保存后再做导出，否则导出的数据将不包括您这次所做的修改。');
		}
		if (flag) {
			if ( typeof e == 'object') {
				e = Util.event(e);
				var frame = e.aim.id.replace('cmd_','');
			} else {
				frame = e == undefined ? '' : e;
			}
			if (!$('frameexport')){
				var dom  = document.createElement('div');
				dom.innerHTML = '<form id="frameexport" method="post" action="" target="_blank"><input type="hidden" name="frame" value="" />\n\
					<input type="hidden" name="tpl" value="'+document.diyform.template.value+'" />\n\
					<input type="hidden" name="tpldirectory" value="'+document.diyform.tpldirectory.value+'" />\n\
					<input type="hidden" name="diysign" value="'+document.diyform.diysign.value+'" />\n\
					<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="exportsubmit" value="true"/></form>';
				$('append_parent').appendChild(dom.childNodes[0]);
			}
			$('frameexport').action = 'portal.php?mod=portalcp&ac=diy&op=export';
			document.forms.frameexport.frame.value = frame;
			document.forms.frameexport.submit();
		}
		doane();
	},
	openFrameImport : function (type) {
		type = type || 0;
		showWindow('showimport','portal.php?mod=portalcp&ac=diy&op=import&tpl='+document.diyform.template.value+'&tpldirectory='+document.diyform.tpldirectory.value+'&diysign='+document.diyform.diysign.value+'&type='+type, 'get');
	},
	endBlockForceUpdateBatch : function () {
		if($('allupdate')) {
			$('allupdate').innerHTML = '已操作完成。';
			$('fwin_dialog_submit').style.display = '';
			$('fwin_dialog_cancel').style.display = 'none';
		}
		this.initPosition();
	},
	getBlocks : function () {
		if (this.blocks.length == 0) {
			this.endBlockForceUpdateBatch();
		}
		if (this.blocks.length > 0) {
			var cur = this.blocksLen - this.blocks.length;
			if($('allupdate')) {
				$('allupdate').innerHTML = '共<span style="color:blue">'+this.blocksLen+'</span>个模块,正在更新第<span style="color:red">'+cur+'</span>个,已完成<span style="color:red">'+(parseInt(cur / this.blocksLen * 100)) + '%</span>';
				var bid = 'portal_block_'+this.blocks.pop();
				this.blockForceUpdate(bid,true);
			}
		}
	},
	blockForceUpdateBatch : function (blocks) {
		if (blocks) {
			this.blocks = blocks;
		} else {
			this.initPosition();
			this.blocks = this.allBlocks;
		}
		this.blocksLen = this.blocks.length;
		showDialog('<div id="allupdate" style="width:350px;line-height:28px;">开始更新...</div>','confirm','更新模块数据', '', true, 'drag.endBlockForceUpdateBatch()');
		var wait = function() {
			if($('fwin_dialog_submit')) {
				$('fwin_dialog_submit').style.display = 'none';
				$('fwin_dialog_cancel').className = 'pn pnc';
				setTimeout(function(){drag.getBlocks()},500);
			} else {
				setTimeout(wait,100);
			}
		};
		wait();
		doane();
	},
	clearAll : function () {
		if (confirm('您确实要清空页面上所在DIY数据吗,清空以后将不可恢复')) {
			for (var i in this.data) {
				for (var j in this.data[i]) {
					if (typeof(this.data[i][j]) == 'object' && this.data[i][j].name.indexOf('_temp')<0) {
						this.delFrame(this.data[i][j]);
						$(this.data[i][j].name).parentNode.removeChild($(this.data[i][j].name));
					}
				}
			}
			this.initPosition();
			this.setClose();
		}
		doane();
	},
	createObj : function (e,objType,contentType) {
		if (objType == 'block' && !this.checkHasFrame()) {alert("提示：未找到框架，请先添加框架。");spaceDiy.getdiy('frame');return false;}
		e = Util.event(e);
		if(e.which != 1 ) {return false;}
		var html = '',offWidth = 0;
		if (objType == 'frame') {
			html =  this.getFrameHtml(contentType);
			offWidth = 600;
		} else if (objType == 'block') {
			html =  this.getBlockHtml(contentType);
			offWidth = 200;
			this.fn = function (e) {drag.getBlockData(contentType);};
		} else if (objType == 'tab') {
			html = this.getTabHtml(contentType);
			offWidth = 300;
		}
		var ele = document.createElement('div');
		ele.innerHTML = html;
		ele = ele.childNodes[0];
		document.body.appendChild(ele);
		this.dragObj = this.overObj = ele;
		if (!this.getTmpBoxElement()) return false;
		var scroll = Util.getScroll();
		this.dragObj.style.position = 'absolute';
		this.dragObj.style.left = e.clientX + scroll.l - 60 + "px";
		this.dragObj.style.top = e.clientY + scroll.t - 10 + "px";
		this.dragObj.style.width = offWidth + 'px';
		this.dragObj.style.cursor = 'move';
		this.dragObj.lastMouseX = e.clientX;
		this.dragObj.lastMouseY = e.clientY;
		Util.insertBefore(this.tmpBoxElement,this.overObj);
		Util.addClass(this.dragObj,this.moving);
		this.dragObj.style.zIndex = 500 ;
		this.scroll = Util.getScroll();
		this.newFlag = true;
		var _method = this;
		document.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
		window.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
		document.onmousemove = function (e){Drag.prototype.drag.call(_method, e);};
		document.onmouseup = function (e){Drag.prototype.dragEnd.call(_method, e);};
	},
	getFrameHtml : function (type) {
		var id = 'frame'+Util.getRandom(6);
		var className = [this.frameClass,this.moveableObject].join(' ');
		className = className + ' cl frame-' + type;
		var str = '<div id="'+id+'" class="'+className+'">';
		str += '<div id="'+id+'_title" class="'+this.titleClass+' '+this.frameTitleClass+'"><span class="'+this.titleTextClass+'">'+type+'框架</span></div>';
		var cols = type.split('-');
		var clsl='',clsc='',clsr='';
		clsl = ' frame-'+type+'-l';
		clsc = ' frame-'+type+'-c';
		clsr = ' frame-'+type+'-r';
		var len = cols.length;
		if (len == 1) {
			str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsc+'"></div>';
		} else if (len == 2) {
			str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsl+ '"></div>';
			str += '<div id="'+id+'_center" class="'+this.moveableColumn+clsr+ '"></div>';
		} else if (len == 3) {
			str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsl+'"></div>';
			str += '<div id="'+id+'_center" class="'+this.moveableColumn+clsc+'"></div>';
			str += '<div id="'+id+'_right" class="'+this.moveableColumn+clsr+'"></div>';
		}
		str += '</div>';
		return str;
	},
	getTabHtml : function () {
		var id = 'tab'+Util.getRandom(6);
		var className = [this.tabClass,this.moveableObject].join(' ');
		className = className + ' cl';
		var titleClassName = [this.tabTitleClass, this.titleClass, this.moveableColumn, 'cl'].join(' ');
		var str = '<div id="'+id+'" class="'+className+'">';
		str += '<div id="'+id+'_title" class="'+titleClassName+'"><span class="'+this.titleTextClass+'">tab标签</span></div>';
		str += '<div id="'+id+'_content" class="'+this.tabContentClass+'"></div>';
		str += '</div>';
		return str;
	},
	getBlockHtml : function () {
		var id = 'block'+Util.getRandom(6);
		var str = '<div id="'+id+'" class="block move-span"></div>';
		str += '</div>';
		return str;
	},
	setClose : function () {
		if(this.sampleMode) {
			return true;
		} else {
			if (!this.isChange) {
				window.onbeforeunload = function() {
					return '您的数据已经修改,退出将无法保存您的修改。';
				};
			}
			this.isChange = true;
			spaceDiy.enablePreviewButton();
		}
	},
	clearClose : function () {
		this.isChange = false;
		this.isClearClose = true;
		window.onbeforeunload = function () {};
	},
	goonDIY : function () {
		if ($('prefile').value == '1') {
			showDialog('<div style="line-height:28px;">按继续按钮将打开暂存数据并DIY，<br />按删除按钮将删除暂存数据。</div>','confirm','是否继续暂存数据的DIY？', function(){location.replace(location.href+'&preview=yes');}, true, 'spaceDiy.cancelDIY()', '', '继续', '删除');
		} else if (location.search.indexOf('preview=yes') > -1) {
			spaceDiy.enablePreviewButton();
		} else {
			spaceDiy.disablePreviewButton();
		}
		setInterval(function(){spaceDiy.save('savecache', 1);},180000);
	}
});

var spaceDiy = new DIY();
spaceDiy.extend({
	save : function (optype,rejs) {
		optype = typeof optype == 'undefined' ? '' : optype;
		if (optype == 'savecache' && !drag.isChange) {return false;}
		var tplpre = document.diyform.template.value.split(':');
		if (!optype) {
			if (['portal/portal_topic_content', 'portal/list', 'portal/view'].indexOf(tplpre[0]) == -1) {
				if (document.diyform.template.value.indexOf(':') > -1 && !document.selectsave) {
					var schecked = '',dchecked = '';
					if (document.diyform.savemod.value == '1') {
						dchecked = ' checked';
					} else {
						schecked = ' checked';
					}
					showDialog('<form name="selectsave" action="" method="get"><label><input type="radio" value="0" name="savemod"'+schecked+' />应用于此类全部页面</label>\n\
					<label><input type="radio" value="1" name="savemod"'+dchecked+' />只应用于本页面</label></form>','notice', '', spaceDiy.save);
					return false;
				}
				if (document.selectsave) {
					if (document.selectsave.savemod[0].checked) {
						document.diyform.savemod.value = document.selectsave.savemod[0].value;
					} else {
						document.diyform.savemod.value = document.selectsave.savemod[1].value;
					}
				}
			} else {
				document.diyform.savemod.value = 1;
			}
		} else if (optype == 'savecache') {
			if (!drag.isChange) return false;
			this.checkPreview_form();
			document.diyform.rejs.value = rejs ? 0 : 1;
		} else if (optype =='preview') {
			if (drag.isChange) {
				optype = 'savecache';
			} else {
				this.checkPreview_form();
				$('preview_form').submit();
				return false;
			}
		}
		document.diyform.action = document.diyform.action.replace(/[&|\?]inajax=1/, '');
		document.diyform.optype.value = optype;
		document.diyform.spacecss.value = spaceDiy.getSpacecssStr();
		document.diyform.style.value = spaceDiy.style;
		document.diyform.layoutdata.value = drag.getPositionStr();
		document.diyform.gobackurl.value = spaceDiy.cancelDiyUrl();
		drag.clearClose();
		if (optype == 'savecache') {
			document.diyform.handlekey.value = 'diyform';
			ajaxpost('diyform','ajaxwaitid','ajaxwaitid','onerror');
		} else {
			saveUserdata('diy_advance_mode', '');
			document.diyform.submit();
		}
	},
	checkPreview_form : function () {
		if (!$('preview_form')) {
			var dom = document.createElement('div');
			var search = '';
			var sarr = location.search.replace('?','').split('&');
			for (var i = 0;i<sarr.length;i++){
				var kv = sarr[i].split('=');
				if (kv.length>1 && kv[0] != 'diy') {
					search += '<input type="hidden" value="'+kv[1]+'" name="'+kv[0]+'" />';
				}
			}
			search +=  '<input type="hidden" value="yes" name="preview" />';
			dom.innerHTML = '<form action="'+location.href+'" target="_bloak" method="get" id="preview_form">'+search+'</form>';
			var form = dom.getElementsByTagName('form');
			$('append_parent').appendChild(form[0]);
		}
	},
	cancelDiyUrl : function () {
		return location.href.replace(/[\?|\&]diy\=yes/g,'').replace(/[\?|\&]preview=yes/,'');
	},
	cancel : function () {
		saveUserdata('diy_advance_mode', '');
		if (drag.isClearClose) {
			showDialog('<div style="line-height:28px;">是否保留暂存数据？<br />按确定按钮将保留暂存数据，按取消按钮将删除暂存数据。</div>','confirm','保留暂存数据', function(){location.href = spaceDiy.cancelDiyUrl();}, true, function(){window.onunload=function(){spaceDiy.cancelDIY()};location.href = spaceDiy.cancelDiyUrl();});
		} else {
			location.href = this.cancelDiyUrl();
		}

	},
	recover : function() {
		if (confirm('您确定要恢复到上一版本保存的结果吗？')) {
			drag.clearClose();
			document.diyform.recover.value = '1';
			document.diyform.gobackurl.value = location.href.replace(/(\?diy=yes)|(\&diy=yes)/,'').replace(/[\?|\&]preview=yes/,'');
			document.diyform.submit();
		}
		doane();
	},
	enablePreviewButton : function () {
		if ($('preview')){
			$('preview').className = '';
			if(drag.isChange) {
				$('diy_preview').onclick = function () {spaceDiy.save('savecache');return false;};
			} else {
				$('diy_preview').onclick = function () {spaceDiy.save('preview');return false;};
			}
			Util.show($('savecachemsg'))
		}
	},
	disablePreviewButton : function () {
		if ($('preview')) {
			$('preview').className = 'unusable';
			$('diy_preview').onclick = function () {return false;};
		}
	},
	cancelDIY : function () {
		this.disablePreviewButton();
		document.diyform.optype.value = 'canceldiy';
		var x = new Ajax();
		x.post($('diyform').action+'&inajax=1','optype=canceldiy&diysubmit=1&template='+document.diyform.template.value+'&savemod='+document.diyform.savemod.value+'&formhash='+document.diyform.formhash.value+'&tpldirectory='+document.diyform.tpldirectory.value+'&diysign='+document.diyform.diysign.value,function(s){});
	},
	switchBlockclass : function(blockclass) {
		var navs = $('contentblockclass_nav').getElementsByTagName('a');
		var contents = $('contentblockclass').getElementsByTagName('ul');
		for(var i=0; i<navs.length; i++) {
			if(navs[i].id=='bcnav_'+blockclass) {
				navs[i].className = 'a';
			} else {
				navs[i].className = '';
			}
		}
		for(var i=0; i<contents.length; i++) {
			if(contents[i].id=='contentblockclass_'+blockclass) {
				contents[i].style.display = '';
			} else {
				contents[i].style.display = 'none';
			}
		}
	},
	getdiy : function (type) {
		if (type) {
			var nav = $('controlnav').children;
			for (var i in nav) {
				if (nav[i].className == 'current') {
					nav[i].className = '';
					var contentid = 'content'+nav[i].id.replace('nav', '');
					if ($(contentid)) $(contentid).style.display = 'none';
				}
			}
			$('nav'+type).className = 'current';
			if (type == 'start' || type == 'frame') {
				$('content'+type).style.display = 'block';
				return true;
			}
			if(type == 'blockclass' && $('content'+type).innerHTML !='') {
				$('content'+type).style.display = 'block';
				return true;
			}
			var para = '&op='+type;
			if (arguments.length > 1) {
				for (var i = 1; i < arguments.length; i++) {
					para += '&' + arguments[i] + '=' + arguments[++i];
				}
			}
			var ajaxtarget = type == 'diy' ? 'diyimages' : '';
			var x = new Ajax();
			x.showId = ajaxtarget;
			x.get('portal.php?mod=portalcp&ac=diy'+para+'&inajax=1&ajaxtarget='+ajaxtarget,function(s, x) {
				if (s) {
					if (typeof cpb_frame == 'object' && !BROWSER.ie) {delete cpb_frame;}
					if (!$('content'+type)) {
						var dom = document.createElement('div');
						dom.id = 'content'+type;
						$('controlcontent').appendChild(dom);
					}
					$('content'+type).innerHTML = s;
					$('content'+type).style.display = 'block';
					if (type == 'diy') {
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
	}
});

spaceDiy.init(1);

function succeedhandle_diyform (url, message, values) {
	if (values['rejs'] == '1') {
		document.diyform.rejs.value = '';
		parent.$('preview_form').submit();
	}
	spaceDiy.enablePreviewButton();
	return false;
}