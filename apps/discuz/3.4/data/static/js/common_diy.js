/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: common_diy.js 31093 2012-07-16 03:54:34Z zhangguosheng $
*/

String.prototype.property2js = function(){
	var t = this.replace(/-([a-z])/g, function($0, $1) {return $1.toUpperCase();});
	return t;
};
function styleCss(n) {
	if(typeof n == "number") {
		var _s = document.styleSheets[n];
	} else {
		return false;
	}
	this.sheet = _s;
	this.rules = _s.cssRules ? _s.cssRules : _s.rules;
};

styleCss.prototype.indexOf = function(selector) {
	for(var i = 0; i < this.rules.length; i++) {
		if (typeof(this.rules[i].selectorText) == 'undefined') continue;
		if(this.rules[i].selectorText == selector) {
			return i;
		}
	}
	return -1;
};

styleCss.prototype.removeRule = function(n) {
	if(typeof n == "number") {
		if(n < this.rules.length) {
			this.sheet.removeRule ? this.sheet.removeRule(n) : this.sheet.deleteRule(n);
		}
	} else {
		var i = this.indexOf(n);
		if (i>0) this.sheet.removeRule ? this.sheet.removeRule(i) : this.sheet.deleteRule(i);
	}
};

styleCss.prototype.addRule = function(selector, styles, n, porperty) {
	var i = this.indexOf(selector);
	var s = '';
	var reg = '';
	if (i != -1) {
		reg = new RegExp('^'+porperty+'.*;','i');
		s = this.getRule(selector);
		if (s) {
			s = s.replace(selector,'').replace('{', '').replace('}', '').replace(/  /g, ' ').replace(/^ | $/g,'');
			s = (s != '' && s.substr(-1,1) != ';') ? s+ ';' : s;
			s = s.toLowerCase().replace(reg, '');
			if (s.length == 1) s = '';
		}
		this.removeRule(i);
	}
	s = s.indexOf('!important') > -1 || s.indexOf('! important') > -1 ? s : s.replace(/;/g,' !important;');
	s = s + styles;
	if (typeof n == 'undefined' || !isNaN(n)) {
		n = this.rules.length;
	}
	if (this.sheet.insertRule) {
		this.sheet.insertRule(selector+'{'+s+'}', n);
	} else {
		if (s) this.sheet.addRule(selector, s, n);
	}
};

styleCss.prototype.setRule = function(selector, attribute, value) {
	var i = this.indexOf(selector);
	if(-1 == i) return false;
	this.rules[i].style[attribute] = value;
	return true;
};

styleCss.prototype.getRule = function(selector, attribute) {
	var i = this.indexOf(selector);
	if(-1 == i) return '';
	var value = '';
	if (typeof attribute == 'undefined') {
		value = typeof this.rules[i].cssText != 'undefined' ? this.rules[i].cssText : this.rules[i].style['cssText'];
	} else {
		value = this.rules[i].style[attribute];
	}
	return typeof value != 'undefined' ? value : '';
};
styleCss.prototype.removeAllRule = function(noSearch) {
	var num = this.rules.length;
	var j = 0;
	for(var i = 0; i < num; i ++) {
		var selector = this.rules[this.rules.length - 1 - j].selectorText;
		if(noSearch == 1) {
			this.sheet.removeRule ? this.sheet.removeRule(this.rules.length - 1 - j) : this.sheet.deleteRule(this.rules.length - 1 - j);
		} else {
			j++;
		}
	}
};
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function (element, index) {
		var length = this.length;
		if (index == null) {
			index = 0;
		} else {
			index = (!isNaN(index) ? index : parseInt(index));
			if (index < 0) index = length + index;
			if (index < 0) index = 0;
		}
		for (var i = index; i < length; i++) {
			var current = this[i];
			if (!(typeof(current) === 'undefined') || i in this) {
				if (current === element) return i;
			}
		}
		return -1;
	};
}
if (!Array.prototype.filter){
	Array.prototype.filter = function(fun , thisp){
	var len = this.length;
	if (typeof fun != "function")
	throw new TypeError();
	var res = new Array();
	var thisp = arguments[1];
	for (var i = 0; i < len; i++){
		if (i in this){
			var val = this[i];
			if (fun.call(thisp, val, i, this)) res.push(val);
		}
	}
	return res;
	};
}
var Util = {
	event: function(event){
		Util.e = event || window.event;
		Util.e.aim = Util.e.target || Util.e.srcElement;
		if (!Util.e.preventDefault) {
			Util.e.preventDefault = function(){
				Util.e.returnValue = false;
			};
		}
		if (!Util.e.stopPropagation) {
			Util.e.stopPropagation = function(){
				Util.e.cancelBubble = true;
			};
		}
		if (typeof Util.e.layerX == "undefined") {
			Util.e.layerX = Util.e.offsetX;
		}
		if (typeof Util.e.layerY == "undefined") {
			Util.e.layerY = Util.e.offsetY;
		}
		if (typeof Util.e.which == "undefined") {
			Util.e.which = Util.e.button;
		}
		return Util.e;
	},
	url: function(s){
		var s2 = s.replace(/(\(|\)|\,|\s|\'|\"|\\)/g, '\\$1');
		if (/\\\\$/.test(s2)) {
			s2 += ' ';
		}
		return "url('" + s2 + "')";
	},
	trimUrl : function(s){
		var s2 = s.toLowerCase().replace(/url\(|\"|\'|\)/g,'');
		return s2;
	},
	swapDomNodes: function(a, b){
		var afterA = a.nextSibling;
		if (afterA == b) {
			swapDomNodes(b, a);
			return;
		}
		var aParent = a.parentNode;
		b.parentNode.replaceChild(a, b);
		aParent.insertBefore(b, afterA);
	},
	hasClass: function(el, name){
		return el && el.nodeType == 1 && typeof el.className === 'string'  && el.className.split(/\s+/).indexOf(name) != -1;
	},
	addClass: function(el, name){
		el.className += this.hasClass(el, name) ? '' : ' ' + name;
	},
	removeClass: function(el, name){
		var names = el.className.split(/\s+/);
		el.className = names.filter(function(n){
			return name != n;
		}).join(' ');
	},
	getTarget: function(e, attributeName, value){
		var target = e.target || e.srcElement;
		while (target != null) {
			if (attributeName == 'className') {
				if (this.hasClass(target, value)) {
					return target;
				}
			}else if (target[attributeName] == value) {
					return target;
			}
			target = target.parentNode;
		}
		return false;
	},
	getOffset:function (el, isLeft) {
		var  retValue  = 0 ;
		while  (el != null ) {
			retValue  +=  el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return  retValue;
	},
	insertBefore: function (newNode, targetNode) {
		var parentNode = targetNode.parentNode;
		var next = targetNode.nextSibling;
		if (targetNode.id && targetNode.id.indexOf('temp')>-1) {
			parentNode.insertBefore(newNode,targetNode);
		} else if (!next) {
			parentNode.appendChild(newNode);
		} else {
			parentNode.insertBefore(newNode,targetNode);
		}
	},
	insertAfter : function (newNode, targetNode) {
		var parentNode = targetNode.parentNode;
		var next = targetNode.nextSibling;
		if (next) {
			parentNode.insertBefore(newNode,next);
		} else {
			parentNode.appendChild(newNode);
		}
	},
	getScroll: function () {
		var t, l, w, h;
		if (document.documentElement && document.documentElement.scrollTop) {
			t = document.documentElement.scrollTop;
			l = document.documentElement.scrollLeft;
			w = document.documentElement.scrollWidth;
			h = document.documentElement.scrollHeight;
		} else if (document.body) {
			t = document.body.scrollTop;
			l = document.body.scrollLeft;
			w = document.body.scrollWidth;
			h = document.body.scrollHeight;
		}
		return {t: t, l: l, w: w, h: h};
	},
	hide:function (ele){
		if (typeof ele == 'string') {ele = $(ele);}
		if (ele){ele.style.display = 'none';ele.style.visibility = 'hidden';}
	},
	show:function (ele){
		if (typeof ele == 'string') {ele = $(ele);}
		if (ele) {
			this.removeClass(ele, 'hide');
			ele.style.display = '';
			ele.style.visibility = 'visible';
		}
	},
	cancelSelect : function () {
		window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
	},
	getSelectText : function () {
		var t = '';
		 if (window.getSelection) {
			t = window.getSelection();
		} else if (document.getSelection) {
			t = document.getSelection();
		} else if (document.selection) {
			t = document.selection.createRange().text;
		} else {
			t = '';
		}
		return t;
	},
	toggleEle : function (ele) {
		ele = (typeof ele !='object') ? $(ele) : ele;
		if (!ele) return false;
		var value = this.getFinallyStyle(ele,'display');
		if (value =='none') {
			this.show(ele);
			this.hide($('uploadmsg_button'));
		} else {
			this.hide(ele);
			this.show($('uploadmsg_button'));
		}
	},
	getFinallyStyle : function (ele,property) {
		ele = (typeof ele !='object') ? $(ele) : ele;
		var style = (typeof(ele['currentStyle']) == 'undefined') ? window.getComputedStyle(ele,null)[property] : ele['currentStyle'][property];
		if (typeof style == 'undefined' && property == 'backgroundPosition') {
			style = ele['currentStyle']['backgroundPositionX'] + ' ' +ele['currentStyle']['backgroundPositionY'];
		}
		return style;
	},
	recolored:function (){
		var b = document.body;
		b.style.zoom = b.style.zoom=="1"?"100%":"1";
	},
	getRandom : function (len,type) {
		len = len < 0 ? 0 : len;
		type = type && type<=3? type : 3;
		var str = '';
		for (var i = 0; i < len; i++) {
			var j = Math.ceil(Math.random()*type);
			if (j == 1) {
				str += Math.ceil(Math.random()*9);
			} else if (j == 2) {
				str += String.fromCharCode(Math.ceil(Math.random()*25+65));
			} else {
				str += String.fromCharCode(Math.ceil(Math.random()*25+97));
			}
		}
		return str;
	},
	fade : function(obj,timer,ftype,cur,fn) {
		if (this.stack == undefined) {this.stack = [];}
		obj = typeof obj == 'string' ? $(obj) : obj;
		if (!obj) return false;

		for (var i=0;i<this.stack.length;i++) {
			if (this.stack[i] == obj && (cur == 0 || cur == 100)) return false;
		}
		if (cur == 0 || cur == 100) {this.stack.push(obj);}

		ftype = ftype != 'in' && ftype != 'out' ? 'out' : ftype;
		timer = timer || 400;
		var step = 100/(timer/20);
		obj.style.filter = 'Alpha(opacity=' + cur + ')';
		obj.style.opacity = cur / 100;
		cur = ftype == 'in' ? cur + step : cur - step ;
		var fadeTimer = (function(){
			return setTimeout(function () {
				Util.fade(obj, timer, ftype, cur, fn);
			}, 20);
			})();
		this[ftype == 'in' ? 'show' : 'hide'](obj);
		if(ftype == 'in' && cur >= 100 || ftype == 'out' && cur <= 0) {
			clearTimeout(fadeTimer);
			for (i=0;i<this.stack.length;i++) {
				if (this.stack[i] == obj ) {
					this.stack.splice(i,1);break;
				}
			}

			fn = fn || function(){};
			fn(obj);
		}
		return obj;
	},
	fadeIn : function (obj,timer,fn) {
		return this.fade(obj, timer, 'in', 0, fn);
	},
	fadeOut : function (obj,timer,fn) {
		return this.fade(obj, timer, 'out', 100, fn);
	},
	getStyle : function (ele) {
		if (ele) {
			var s = ele.getAttribute('style') || '';
			return typeof s == 'object' ? s.cssText : s;
		}
		return false;
	},
	setStyle : function (ele,cssText) {
		if (ele) {
			var s = ele.getAttribute('style') || '';
			return typeof s == 'object' ? s.cssText = cssText : ele.setAttribute('style',cssText);
		}
		return false;
	},
	getText : function (ele) {
		var t = ele.innerText ? ele.innerText : ele.textContent;
		return !t ? '' : t;
	},
	rgb2hex : function (color) {
		if (!color) return '';
		var reg = new RegExp('(\\d+)[, ]+(\\d+)[, ]+(\\d+)','g');
		var rgb = reg.exec(color);
		if (rgb == null) rgb = [0,0,0,0];
		var red = rgb[1], green = rgb[2], blue = rgb[3];
		var decColor = 65536 * parseInt(red) + 256 * parseInt(green) + parseInt(blue);
		var hex = decColor.toString(16).toUpperCase();
		var pre = new Array(6 - hex.length + 1).join('0');
		hex = pre + hex;
		return hex;
	},
	formatColor : function (color) {
		return color == '' || color.indexOf('#')>-1 || color.toLowerCase() == 'transparent' ? color : '#'+Util.rgb2hex(color);
	}
};

(function(){
	Frame = function(name, className, top, left, moveable){
		this.name = name;
		this.top = top;
		this.left = left;
		this.moveable = moveable ? true : false;
		this.columns = [];
		this.className = className;
		this.titles = [];
		if (typeof Frame._init == 'undefined') {
			Frame.prototype.addColumn = function (column) {
				if (column instanceof Column) {
					this.columns[column.name] = column;
				}
			};
			Frame.prototype.addFrame = function(columnId, frame) {
				if (frame instanceof Frame || frame instanceof Tab){
					this.columns[columnId].children.push(frame);
				}
			};
			Frame.prototype.addBlock = function(columnId, block) {
				if (block instanceof Block){
					this.columns[columnId].children.push(block);
				}
			};
		}
		Frame._init = true;
	};

	Column = function (name, className) {
		this.name = name;
		this.className = className;
		this.children = [];
	};
	Tab = function (name, className, top, left, moveable) {
		Frame.apply(this, arguments);
	};
	Tab.prototype = new Frame();
	Block = function(name, className, top, left) {
		this.name = name;
		this.top = top;
		this.left = left;
		this.className = className;
		this.titles = [];
	};

	Drag = function () {
		this.data = [];
		this.scroll = {};
		this.menu = [];
		this.data = [];
		this.allBlocks = [];
		this.overObj = '';
		this.dragObj = '';
		this.dragObjFrame = '';
		this.overObjFrame = '';
		this.isDragging = false;
		this.layout = 2;
		this.frameClass = 'frame';
		this.blockClass = 'block';
		this.areaClass = 'area';
		this.moveableArea = [];
		this.moveableColumn = 'column';
		this.moveableObject = 'move-span';
		this.titleClass = 'title';
		this.hideClass = 'hide';
		this.titleTextClass = 'titletext';
		this.frameTitleClass = 'frame-title',
		this.tabClass = 'frame-tab';
		this.tabActivityClass = 'tabactivity';
		this.tabTitleClass = 'tab-title';
		this.tabContentClass = 'tb-c';
		this.moving = 'moving';
		this.contentClass = 'dxb_bc';
		this.tmpBoxElement = null ;
		this.dargRelative = {};
		this.scroll = {};
		this.menu = [];
		this.rein = [];
		this.newFlag = false;
		this.isChange = false;
		this.fn = '';
		this._replaceFlag = false;
		this.sampleMode = false;
		this.sampleBlocks = null;
		this.advancedStyleSheet = null;
	};
	Drag.prototype = {
		getTmpBoxElement : function () {
			if  (!this.tmpBoxElement) {
				this.tmpBoxElement = document.createElement("div");
				this.tmpBoxElement.id = 'tmpbox';
				this.tmpBoxElement.className = "tmpbox" ;
				this.tmpBoxElement.style.width = this.overObj.offsetWidth-4+"px";
				this.tmpBoxElement.style.height = this.overObj.offsetHeight-4+"px";
			} else if (this.overObj && this.overObj.offsetWidth > 0) {
				this.tmpBoxElement.style.width = this.overObj.offsetWidth-4+"px";
			}
			return this.tmpBoxElement;
		},
		getPositionStr : function (){
			this.initPosition();
			var start = '<?xml version="1.0" encoding="ISO-8859-1"?><root>';
			var end ="</root>";
			var str = "";
			for (var i in this.data) {
				if (typeof this.data[i] == 'function') continue;
				str += '<item id="' + i + '">';
				for (var j in this.data[i]) {
					if (!(this.data[i][j] instanceof Frame || this.data[i][j] instanceof Tab)) continue;
					str += this._getFrameXML(this.data[i][j]);
				}
				str += '</item>';
			}
			return start + str + end;
		},
		_getFrameXML : function (frame) {
			if (!(frame instanceof Frame || frame instanceof Tab) || frame.name.indexOf('temp') > 0) return '';
			var itemId = frame instanceof Tab ? 'tab' : 'frame';
			var Cstr = "";
			var name = frame.name;
			var frameAttr = this._getAttrXML(frame);
			var columns = frame['columns'];
			for (var j in columns) {
				if (columns[j] instanceof Column) {
					var Bstr = '';
					var colChildren = columns[j].children;
					for (var k in colChildren) {
						if (k == 'attr' || typeof colChildren[k] == 'function' || colChildren[k].name.indexOf('temp') > 0) continue;
						if (colChildren[k] instanceof Block) {
							Bstr += '<item id="block`' + colChildren[k]['name'] + '">';
							Bstr += this._getAttrXML(colChildren[k]);
							Bstr += '</item>';
						} else if (colChildren[k] instanceof Frame || colChildren[k] instanceof Tab) {
							Bstr += this._getFrameXML(colChildren[k]);
						}
					}
					var columnAttr = this._getAttrXML(columns[j]);
					Cstr += '<item id="column`' + j + '">' + columnAttr + Bstr + '</item>';
				}
			}
			return '<item id="' + itemId + '`' + name + '">' + frameAttr + Cstr + '</item>';
		},
		_getAttrXML : function (obj) {
			var attrXml = '<item id="attr">';
			var trimAttr = ['left', 'top'];
			var xml = '';
			if (obj instanceof Frame || obj instanceof Tab || obj instanceof Block || obj instanceof Column) {
				for (var i in obj) {
					if (i == 'titles') {
						xml += this._getTitlesXML(obj[i]);
					}
					if (!(typeof obj[i] == 'object' || typeof obj[i] == 'function')) {
						if (trimAttr.indexOf(i) >= 0) continue;
						xml += '<item id="' + i + '"><![CDATA[' + obj[i] + ']]></item>';
					}
				}
			}else {
				xml += '';
			}
			return attrXml + xml + '</item>';
		},
		_getTitlesXML : function (titles) {
			var xml = '<item id="titles">';
			for (var i in titles) {
				if (typeof titles[i] == 'function') continue;
				xml += '<item id="'+i+'">';
				for (var j in titles[i]) {
					if (typeof titles[i][j] == 'function') continue;
					xml += '<item id="'+j+'"><![CDATA[' + titles[i][j] + ']]></item>';
				}
				xml += '</item>';
			}
			xml += '</item>';
			return xml;
		},
		getCurrentOverObj : function (e) {
			var _clientX = parseInt(this.dragObj.style.left);
			var _clientY = parseInt(this.dragObj.style.top);
			var max = 10000000;
			for (var i in this.data) {
				for (var j in this.data[i]) {
					if (!(this.data[i][j] instanceof Frame || this.data[i][j] instanceof Tab)) continue;
					var min = this._getMinDistance(this.data[i][j], max);
					if (min.distance < max) {
						var id = min.id;
						max = min.distance;
					}
				}
			}
			return $(id);
		},
		_getMinDistance : function (ele, max) {
			if(ele.name==this.dragObj.id) return {"id":ele.name, "distance":max};
			var _clientX = parseInt(this.dragObj.style.left);
			var _clientY = parseInt(this.dragObj.style.top);
			var id;
			var isTabInTab = Util.hasClass(this.dragObj, this.tabClass) && Util.hasClass($(ele.name).parentNode.parentNode, this.tabClass);
			if (ele instanceof Frame || ele instanceof Tab) {
				if (ele.moveable && !isTabInTab) {
					var isTab = Util.hasClass(this.dragObj, this.tabClass);
					var isFrame = Util.hasClass(this.dragObj, this.frameClass);
					var isBlock = Util.hasClass(this.dragObj, this.blockClass) && Util.hasClass($(ele.name).parentNode, this.moveableColumn);
					if ( isTab || isFrame || isBlock) {
						var _Y = ele['top'] - _clientY;
						var _X = ele['left'] - _clientX;
						var distance = Math.sqrt(Math.pow(_X, 2) + Math.pow(_Y, 2));
						if (distance < max) {
							max = distance;
							id = ele.name;
						}
					}
				}
				for (var i in ele['columns']) {
					var column = ele['columns'][i];
					if (column instanceof Column) {
						for (var j in column['children']) {
							if ((column['children'][j] instanceof Tab || column['children'][j] instanceof Frame || column['children'][j] instanceof Block)) {
								var min = this._getMinDistance(column['children'][j], max);
								if (min.distance < max) {
									id = min.id;
									max = min.distance;
								}
							}
						}
					}
				}
				return {"id":id, "distance":max};
			} else {
				if (isTabInTab) return {'id': ele['name'], 'distance': max};
				var _Y = ele['top'] - _clientY;
				var _X = ele['left'] - _clientX;
				var distance = Math.sqrt(Math.pow(_X, 2) + Math.pow(_Y, 2));
				if (distance < max) {
					return {'id': ele['name'], 'distance': distance};
				} else {
					return {'id': ele['name'], 'distance': max};
				}
			}
		},
		getObjByName : function (name, data) {
			if (!name) return false;
			data = data || this.data;
			if ( data instanceof Frame) {
				if (data.name == name) {
					return data;
				} else {
					var d = this.getObjByName(name,data['columns']);
					if (name == d.name) return d;
				}
			} else if (data instanceof Block) {
				if (data.name == name) return data;
			} else if (typeof data == 'object') {
				for (var i in data) {
					var d = this.getObjByName(name, data[i]);
					if (name == d.name) return d;
				}
			}
			return false;
		},
		initPosition : function () {
			this.data = [],this.allBlocks = [];
			var blocks = $C(this.blockClass);
			for(var i = 0; i < blocks.length; i++) {
				if (blocks[i]['id'].indexOf('temp') < 0) {
					this.checkEdit(blocks[i]);
					this.allBlocks.push(blocks[i]['id'].replace('portal_block_',''));
				}
			}
			var areaLen = this.moveableArea.length;
			for (var j = 0; j < areaLen; j++ ) {
				var area = this.moveableArea[j];
				var areaData = [];
				if (typeof area == 'object') {
					this.checkTempDiv(area.id);
					var frames = area.childNodes;
					for (var i in frames) {
						if (typeof(frames[i]) != 'object') continue;
						if (Util.hasClass(frames[i], this.frameClass) || Util.hasClass(frames[i], this.blockClass)
							|| Util.hasClass(frames[i], this.tabClass) || Util.hasClass(frames[i], this.moveableObject)) {
							areaData.push(this.initFrame(frames[i]));
						}
					}
					this.data[area.id] = areaData;
				}
			}
			this._replaceFlag = true;
		},
		removeBlockPointer : function(e) {this.removeBlock(e);},
		toggleContent : function (e) {
			if ( typeof e !== 'string') {
				e = Util.event(e);
				var id = e.aim.id.replace('_edit_toggle','');
			} else {
				id = e;
			}
			var obj = this.getObjByName(id);
			var display = '';
			if (obj instanceof Block || obj instanceof Tab) {
				display = $(id+'_content').style.display;
				Util.toggleEle(id+'_content');
			} else {
				var col = obj.columns;
				for (var i in col) {
					display = $(i).style.display;
					Util.toggleEle($(i));
				}
			}

			if(display != '') {
				e.aim.src=STATICURL+'/image/common/fl_collapsed_no.gif';
			} else {
				e.aim.src=STATICURL+'/image/common/fl_collapsed_yes.gif';
			}
		},
		checkEdit : function (ele) {
			if (!ele || Util.hasClass(ele, 'temp') || ele.getAttribute('noedit')) return false;
			var id = ele.id;
			var _method = this;
			if (!$(id+'_edit')) {
				var _method = this;
				var dom = document.createElement('div');
				dom.className = 'edit hide';
				dom.id = id+'_edit';
				dom.innerHTML = '<span id="'+id+'_edit_menu">编辑</span>';
				ele.appendChild(dom);
				$(id+'_edit_menu').onclick = function (e){Drag.prototype.toggleMenu.call(_method, e, this);};
			}
			ele.onmouseover = function (e) {Drag.prototype.showEdit.call(_method,e);};
			ele.onmouseout = function (e) {Drag.prototype.hideEdit.call(_method,e);};
		},
		initFrame : function (frameEle) {
			if (typeof(frameEle) != 'object') return '';
			var frameId = frameEle.id;
			if(!this.sampleMode) {
				this.checkEdit(frameEle);
			}
			var moveable = Util.hasClass(frameEle, this.moveableObject);
			var frameObj = '';
			if (Util.hasClass(frameEle, this.tabClass)) {
				this._initTabActivity(frameEle);
				frameObj = new Tab(frameId, frameEle.className, Util.getOffset(frameEle,false), Util.getOffset(frameEle,true), moveable);
			} else if (Util.hasClass(frameEle, this.frameClass) || Util.hasClass(frameEle, this.moveableObject)) {
				if (Util.hasClass(frameEle, this.frameClass) && !this._replaceFlag)	this._replaceFrameColumn(frameEle);
				frameObj = new Frame(frameId, frameEle.className, Util.getOffset(frameEle,false), Util.getOffset(frameEle,true), moveable);
			}
			this._initColumn(frameObj, frameEle);

			return frameObj;
		},
		_initColumn : function (frameObj,frameEle) {
			var columns = frameEle.children;
			if (Util.hasClass(frameEle.parentNode.parentNode,this.tabClass)) {
				var col2 = $(frameEle.id+'_content').children;
				var len = columns.length;
				for (var i in col2) {
					if (typeof(col2[i]) == 'object') columns[len+i] = col2[i];
				}
			}
			for (var i in columns) {
				if (typeof(columns[i]) != 'object') continue;
				if (Util.hasClass(columns[i], this.titleClass)) {
					this._initTitle(frameObj, columns[i]);
				}
				this._initEleTitle(frameObj, frameEle);
				if (Util.hasClass(columns[i], this.moveableColumn)) {
					var columnId = columns[i].id;
					var column = new Column(columnId, columns[i].className);
					frameObj.addColumn(column);
					this.checkTempDiv(columnId);
					var elements = columns[i].children;
					var eleLen = elements.length;
					for (var j = 0; j < eleLen; j++) {
						var ele = elements[j];
						if (Util.hasClass(ele, this.frameClass) || Util.hasClass(ele, this.tabClass)) {
							var frameObj2 = this.initFrame(ele);
							frameObj.addFrame(columnId, frameObj2);
						} else if (Util.hasClass(ele, this.blockClass) || Util.hasClass(ele, this.moveableObject)) {
							var block = new Block(ele.id, ele.className, Util.getOffset(ele, false), Util.getOffset(ele, true));
							for (var k in ele.children) {
								if (Util.hasClass(ele.children[k], this.titleClass)) this._initTitle(block, ele.children[k]);
							}
							this._initEleTitle(block, ele);
							frameObj.addBlock(columnId, block);
						}
					}
				}
			}
		},
		_initTitle : function (obj, ele) {
			if (Util.hasClass(ele, this.titleClass)) {
				obj.titles['className'] = [ele.className];
				obj.titles['style'] = {};
				if (ele.style.backgroundImage) obj.titles['style']['background-image'] = ele.style.backgroundImage;
				if (ele.style.backgroundRepeat) obj.titles['style']['background-repeat'] = ele.style.backgroundRepeat;
				if (ele.style.backgroundColor) obj.titles['style']['background-color'] = ele.style.backgroundColor;
				if (obj instanceof Tab) {
					obj.titles['switchType'] = [];
					obj.titles['switchType'][0] = ele.getAttribute('switchtype') ? ele.getAttribute('switchtype') : 'click';
				}
				var ch = ele.children;
				for (var k in ch) {
					if (Util.hasClass(ch[k], this.titleTextClass)){
						this._getTitleData(obj, ch[k], 'first');
					} else if (typeof ch[k] == 'object' && !Util.hasClass(ch[k], this.moveableObject)) {
						this._getTitleData(obj, ch[k]);
					}
				}
			}
		},
		_getTitleData : function (obj, ele, i) {
			var shref = '',ssize = '',sfloat = '',scolor = '',smargin = '',stext = '', src = '';
			var collection = ele.getElementsByTagName('a');
			if (collection.length > 0) {
				 shref = collection[0]['href'];
				 scolor = collection[0].style['color'] + ' !important';
			}
			collection = ele.getElementsByTagName('img');
			if (collection.length > 0) {
				 src = collection[0]['src'];
			}

			stext = Util.getText(ele);

			if (stext || src) {
				scolor = scolor ? scolor : ele.style['color'];
				sfloat = ele.style['styleFloat'] ? ele.style['styleFloat'] : ele.style['cssFloat'] ;
				sfloat = sfloat == undefined ? '' : sfloat;
				var margin_ = sfloat == '' ? 'left' : sfloat;
				smargin = parseInt(ele.style[('margin-'+margin_).property2js()]);
				smargin = smargin ? smargin : '';
				ssize = parseInt(ele.style['fontSize']);
				ssize = ssize ? ssize : '';
				var data = {'text':stext, 'href':shref,'color':scolor, 'float':sfloat, 'margin':smargin, 'font-size':ssize, 'className':ele.className, 'src':src};
				if (i) {
					obj.titles[i] = data;
				} else {
					obj.titles.push(data);
				}
			}
		},
		_initEleTitle : function (obj,ele) {
			if (Util.hasClass(ele, this.moveableObject)) {
				if (obj.titles['first'] && obj.titles['first']['text']) {
					var title = obj.titles['first']['text'];
				} else {
					var title = obj.name;
				}
			}
		},
		showBlockName : function (ele) {
			var title = $C('block-name', ele, 'div');
			if(title.length) {
				Util.show(title[0]);
			}
		},
		hideBlockName : function (ele) {
			var title = $C('block-name', ele, 'div');
			if(title.length) {
				Util.hide(title[0]);
			}
		},
		showEdit : function (e) {
			e = Util.event(e);
			var targetObject = Util.getTarget(e,'className',this.moveableObject);
			if (targetObject) {
				Util.show(targetObject.id + '_edit');
				targetObject.style.backgroundColor="#fffacd";
				this.showBlockName(targetObject);
			} else {
				var targetFrame = Util.getTarget(e,'className',this.frameClass);
				if (typeof targetFrame == 'object') {
					Util.show(targetFrame.id + '_edit');
					targetFrame.style.backgroundColor="#fffacd";
				}
			}
		},
		hideEdit : function (e) {
			e = Util.event(e);
			var targetObject = Util.getTarget(e,'className',this.moveableObject);
			var targetFrame = Util.getTarget(e,'className',this.frameClass);
			if (typeof targetFrame == 'object') {
				Util.hide(targetFrame.id + '_edit');
				targetFrame.style.backgroundColor = '';
			}
			if (typeof targetObject == 'object') {
				Util.hide(targetObject.id + '_edit');
				targetObject.style.backgroundColor = '';
				this.hideBlockName(targetObject);
			}
		},
		toggleMenu : function (e, obj) {
			e = Util.event(e);
			e.stopPropagation();
			var objPara = {'top' : Util.getOffset( obj, false),'left' : Util.getOffset( obj, true),
							'width' : obj['offsetWidth'], 'height' : obj['offsetHeight']};

			var dom = $('edit_menu');
			if (dom) {
				if (objPara.top + objPara.height == Util.getOffset(dom, false) && objPara.left == Util.getOffset(dom, true)) {
					dom.parentNode.removeChild(dom);
				} else {
					dom.style.top = objPara.top + objPara.height + 'px';
					dom.style.left = objPara.left + 'px';
					dom.innerHTML = this._getMenuHtml(e, obj);
				}
			} else {
				var html = this._getMenuHtml(e, obj);
				if (html != '') {
					dom = document.createElement('div');
					dom.id = 'edit_menu';
					dom.className = 'edit-menu';
					dom.style.top = objPara.top + objPara.height + 'px';
					dom.style.left = objPara.left + 'px';
					dom.innerHTML = html;
					document.body.appendChild(dom);
					var _method = this;
					document.body.onclick = function(e){Drag.prototype.removeMenu.call(_method, e);};
				}
			}
		},
		_getMenuHtml : function (e,obj) {
			var id = obj.id.replace('_edit_menu','');
			var html = '<ul>';
			if (typeof this.menu[id] == 'object') html += this._getMenuHtmlLi(id, this.menu[id]);
			if (Util.hasClass($(id),this.tabClass) && typeof this.menu['tab'] == 'object') html += this._getMenuHtmlLi(id, this.menu['tab']);
			if (Util.hasClass($(id),this.frameClass) && typeof this.menu['frame'] == 'object') html += this._getMenuHtmlLi(id, this.menu['frame']);
			if (Util.hasClass($(id),this.blockClass) && typeof this.menu['block'] == 'object') html += this._getMenuHtmlLi(id, this.menu['block']);
			if (typeof this.menu['default'] == 'object' && this.getObjByName(id)) html += this._getMenuHtmlLi(id, this.menu['default']);
			html += '</ul>';
			return html == '<ul></ul>' ? '' : html;
		},
		_getMenuHtmlLi : function (id, cmds) {
			var li = '';
			var len = cmds.length;
			for (var i=0; i<len; i++) {
				li += '<li class="mitem" id="cmd_'+id+'" onclick='+"'"+cmds[i]['cmd']+"'"+'>'+cmds[i]['cmdName']+'</li>';
			}
			return li;
		},
		removeMenu : function (e) {
			var dom = $('edit_menu');
			if (dom) dom.parentNode.removeChild(dom);
			document.body.onclick = '';
		},
		addMenu : function (objId,cmdName,cmd) {
			if (typeof this.menu[objId] == 'undefined') this.menu[objId] = [];
			this.menu[objId].push({'cmdName':cmdName, 'cmd':cmd});
		},
		setDefalutMenu : function () {},
		setSampleMenu : function () {},

		getPositionKey : function (n) {
			this.initPosition();
			n = parseInt(n);
			var i = 0;
			for (var k in this.position) {
				if (i++ >= n) break;
			}
			return k;
		},

		checkTempDiv : function (_id) {
			if(_id) {
				var id = _id+'_temp';
				var dom = $(id);
				if (dom == null || typeof dom == 'undefined') {
					dom  = document.createElement("div");
					dom.className = this.moveableObject+' temp';
					dom.id = id;
					$(_id).appendChild(dom);
				}
			}
		},
		_setCssPosition : function (ele, value) {
			while (ele && ele.parentNode && ele.id != 'ct') {
				if (Util.hasClass(ele,this.frameClass) || Util.hasClass(ele,this.tabClass)) ele.style.position = value;
				ele = ele.parentNode;
			}
		},
		initDragObj : function (e) {
			e = Util.event(e);
			var target = Util.getTarget(e,'className',this.moveableObject);
			if (!target) {return false;}
			if (this.overObj != target && target.id !='tmpbox') {
				this.overObj = target;
				this.overObj.style.cursor = 'move';
			}
		},
		dragStart : function (e) {
			e = Util.event(e);
			if (e.aim['id'] && e.aim['id'].indexOf && e.aim['id'].indexOf('_edit') > 0) return false;
			if(e.which != 1 ) {return false;}
			this.overObj = this.dragObj = Util.getTarget(e,'className',this.moveableObject);
			if (!this.dragObj || Util.hasClass(this.dragObj,'temp')) {return false;}
			if (!this.getTmpBoxElement()) return false;
			this.getRelative();
			this._setCssPosition(this.dragObj.parentNode.parentNode, "static");
			var offLeft = Util.getOffset( this.dragObj, true );
			var offTop = Util.getOffset( this.dragObj, false );
			var offWidth = this.dragObj['offsetWidth'];
			this.dragObj.style.position = 'absolute';
			this.dragObj.style.left = offLeft + "px";
			this.dragObj.style.top = offTop - 3 + "px";
			this.dragObj.style.width = offWidth + 'px';
			this.dragObj.lastMouseX = e.clientX;
			this.dragObj.lastMouseY = e.clientY;
			Util.insertBefore(this.tmpBoxElement,this.overObj);
			Util.addClass(this.dragObj,this.moving);
			this.dragObj.style.zIndex = 500 ;
			this.scroll = Util.getScroll();
			var _method = this;
			document.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
			window.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
			document.onmousemove = function (e){Drag.prototype.drag.call(_method, e);};
		},
		getRelative : function () {
			this.dargRelative = {'up': this.dragObj.previousSibling, 'down': this.dragObj.nextSibling};
		},
		resetObj : function (e) {
			if (this.dragObj){
				e = Util.event(e);
				var p = Util.getScroll();
				var _t = p.t - this.scroll.t;
				var _l = p.l - this.scroll.l;
				var t = parseInt(this.dragObj.style.top);
				var l = parseInt(this.dragObj.style.left);
				t += _t;
				l += _l;
				this.dragObj.style.top =t+'px';
				this.dragObj.style.left =l+'px';
				this.scroll = Util.getScroll();
			}
		},
		drag : function (e) {
			e = Util.event(e);
			if(!this.isDragging) {
				this.dragObj.style.filter = "alpha(opacity=60)" ;
				this.dragObj.style.opacity = 0.6 ;
				this.isDragging = true ;
			}
			var _clientX = e.clientX;
			var _clientY = e.clientY;
			if (this.dragObj.lastMouseX == _clientX && this.dragObj.lastMouseY == _clientY) return false ;
			var _lastY = parseInt(this.dragObj.style.top);
			var _lastX = parseInt(this.dragObj.style.left);
			_lastX = isNaN(_lastX) ? 0 :_lastX;
			_lastY = isNaN(_lastY) ? 0 :_lastY;
			var newX, newY;
			newY = _lastY + _clientY - this.dragObj.lastMouseY;
			newX = _lastX + _clientX - this.dragObj.lastMouseX;

			this.dragObj.style.left = newX +"px ";
			this.dragObj.style.top = newY + "px ";
			this.dragObj.lastMouseX = _clientX;
			this.dragObj.lastMouseY = _clientY;
			var obj = this.getCurrentOverObj(e);
			if (obj && this.overObj != obj) {
				this.overObj = obj;
				this.getTmpBoxElement();
				Util.insertBefore(this.tmpBoxElement, this.overObj);
				this.dragObjFrame = this.dragObj.parentNode.parentNode;
				this.overObjFrame = this.overObj.parentNode.parentNode;
			}
			Util.cancelSelect();
		},
		_pushTabContent : function (tab, ele){
			if (Util.hasClass(ele, this.frameClass) || Util.hasClass(ele, this.tabClass)) {
				var dom = $(ele.id+'_content');
				if (!dom) {
					dom = document.createElement('div');
					dom.id = ele.id+'_content';
					dom.className = Util.hasClass(ele, this.frameClass) ? this.contentClass+' cl '+ele.className.substr(ele.className.lastIndexOf(' ')+1) : this.contentClass+' cl';
				}
				var frame = this.getObjByName(ele.id);
				if (frame) {
					for (var i in frame['columns']) {
						if (frame['columns'][i] instanceof Column) dom.appendChild($(i));
					}
				} else {
					var children = ele.childNodes;
					var arrDom = [];
					for (var i in children) {
						if (typeof children[i] != 'object') continue;
						if (Util.hasClass(children[i],this.moveableColumn) || Util.hasClass(children[i],this.tabContentClass)) {
							arrDom.push(children[i]);
						}
					}
					var len = arrDom.length;
					for (var i = 0; i < len; i++) {
						dom.appendChild(arrDom[i]);
					}
				}
				$(tab.id+'_content').appendChild(dom);
			} else if (Util.hasClass(ele, this.blockClass)) {
				if ($(ele.id+'_content')) $(tab.id+'_content').appendChild($(ele.id+'_content'));
			}
		},
		_popTabContent : function (tab, ele){
			if (Util.hasClass(ele, this.frameClass) || Util.hasClass(ele, this.tabClass)) {
				Util.removeClass(ele, this.tabActivityClass);
				var eleContent = $(ele.id+'_content');
				if (!eleContent) return false;
				var children = eleContent.childNodes;
				var arrEle = [];
				for (var i in children) {
					if (typeof children[i] == 'object') arrEle.push(children[i]);
				}
				var len = arrEle.length;
				for (var i = 0; i < len; i++) {
					ele.appendChild(arrEle[i]);
				}
				children = '';
				$(tab.id+'_content').removeChild(eleContent);
			} else if (Util.hasClass(ele, this.blockClass)) {
				if ($(ele.id+'_content')) Util.show($(ele.id+'_content'));
				if ($(ele.id+'_content')) ele.appendChild($(ele.id+'_content'));
			}
		},
		_initTabActivity : function (ele) {
			if (!Util.hasClass(ele,this.tabClass)) return false;
			var tabs = $(ele.id+'_title').childNodes;
			var arrTab = [];
			for (var i in tabs) {
				if (typeof tabs[i] != 'object') continue;
				var tabId = tabs[i].id;
				if (Util.hasClass(tabs[i],this.frameClass) || Util.hasClass(tabs[i],this.tabClass)) {
					if (!this._replaceFlag)	this._replaceFrameColumn(tabs[i]);
					if (!$(tabId + '_content')) {
						var arrColumn = [];
						for (var j in tabs[i].childNodes) {
							if (Util.hasClass(tabs[i].childNodes[j], this.moveableColumn)) arrColumn.push(tabs[i].childNodes[j]);
						}
						var frameContent = document.createElement('div');
						frameContent.id = tabId + '_content';
						frameContent.className = Util.hasClass(tabs[i], this.frameClass) ? this.contentClass+' cl '+tabs[i].className.substr(tabs[i].className.lastIndexOf(' ')+1) : this.contentClass+' cl';
						var colLen = arrColumn.length;
						for (var k = 0; k < colLen; k++) {
							frameContent.appendChild(arrColumn[k]);
						}
					}
					arrTab.push(tabs[i]);
				} else if (Util.hasClass(tabs[i],this.blockClass)) {
					var frameContent = $(tabId+'_content');
					if (frameContent) {
						frameContent = Util.hasClass(frameContent.parentNode,this.blockClass) ? frameContent : '';
					} else {
						frameContent = document.createElement('div');
						frameContent.id = tabId+'_content';
					}
					arrTab.push(tabs[i]);
				}
				if (frameContent) $(ele.id + '_content').appendChild(frameContent);
			}
			var len = arrTab.length;
			for (var i = 0; i < len; i++) {
				Util[i > 0 ? 'hide' : 'show']($(arrTab[i].id+'_content'));
			}
		},
		dragEnd : function (e) {
			e = Util.event(e);
			if(!this.dragObj) {return false;}
			document.onscroll = function(){};
			window.onscroll = function(){};
			document.onmousemove = function(e){};
			document.onmouseup = '';
			if (this.tmpBoxElement.parentNode) {
				if (this.tmpBoxElement.parentNode == document.body) {
					document.body.removeChild(this.tmpBoxElement);
					document.body.removeChild(this.dragObj);
					this.fn = '';
				} else {
					Util.removeClass(this.dragObj,this.moving);
					this.dragObj.style.display = 'none';
					this.dragObj.style.width = '' ;
					this.dragObj.style.top = '';
					this.dragObj.style.left = '';
					this.dragObj.style.zIndex = '';
					this.dragObj.style.position = 'relative';
					this.dragObj.style.backgroundColor = '';
					this.isDragging = false ;
					this.tmpBoxElement.parentNode.replaceChild(this.dragObj, this.tmpBoxElement);
					Util.fadeIn(this.dragObj);
					this.tmpBoxElement='';
					this._setCssPosition(this.dragObjFrame, 'relative');
					this.doEndDrag();
					this.initPosition();
					if (!(this.dargRelative.up == this.dragObj.previousSibling && this.dargRelative.down == this.dragObj.nextSibling)) {
						this.setClose();
					}
					this.dragObjFrame = this.overObjFrame = null;
				}
			}
			this.newFlag = false;
			if (typeof this.fn == 'function') {this.fn();}
		},
		doEndDrag : function () {
			if (!Util.hasClass(this.dragObjFrame, this.tabClass) && Util.hasClass(this.overObjFrame, this.tabClass)) {
				this._pushTabContent(this.overObjFrame, this.dragObj);
			}else if (Util.hasClass(this.dragObjFrame, this.tabClass) && !Util.hasClass(this.overObjFrame, this.tabClass)) {
				this._popTabContent(this.dragObjFrame, this.dragObj);
			}else if (Util.hasClass(this.dragObjFrame, this.tabClass) && Util.hasClass(this.overObjFrame, this.tabClass)) {
				if (this.dragObjFrame != this.overObjFrame) {
					this._popTabContent(this.dragObjFrame, this.dragObj);
					this._pushTabContent(this.overObjFrame, this.dragObj);
				}
			} else {
			}

		},
		_replaceFrameColumn : function (ele,flag) {
			var children = ele.childNodes;
			var fcn = ele.className.match(/(frame-[\w-]*)/);
			if (!fcn) return false;
			var frameClassName = fcn[1];
			for (var i in children) {
				if (Util.hasClass(children[i], this.moveableColumn)) {
					var className = children[i].className;
					className = className.replace(' col-l', ' '+frameClassName+'-l');
					className = className.replace(' col-r', ' '+frameClassName+'-r');
					className = className.replace(' col-c', ' '+frameClassName+'-c');
					className = className.replace(' mn', ' '+frameClassName+'-l');
					className = className.replace(' sd', ' '+frameClassName+'-r');
					children[i].className = className;
				}
			}
		},
		stopCmd : function () {
			this.rein.length > 0 ? this.rein.pop()() : '';
		},
		setClose : function () {
			if (!this.isChange) {
				window.onbeforeunload = function() {
					return '您的数据已经修改,退出将无法保存您的修改。';
				};
			}
			this.isChange = true;
		},
		clearClose : function () {
			this.isChange = false;
			window.onbeforeunload = function () {};
		},
		_getMoveableArea : function (ele) {
			ele = ele ? ele : document.body;
			this.moveableArea = $C(this.areaClass, ele, 'div');
		},
		initMoveableArea : function () {
			var _method = this;
			this._getMoveableArea();
			var len = this.moveableArea.length;
			for (var i = 0; i < len; i++) {
				var el = this.moveableArea[i];
				if (el == null || typeof el == 'undefined') return false;
				el.ondragstart = function (e) {return false;};
				el.onmouseover = function (e) {Drag.prototype.initDragObj.call(_method, e);};
				el.onmousedown = function (e) {Drag.prototype.dragStart.call(_method, e);};
				el.onmouseup = function (e) {Drag.prototype.dragEnd.call(_method, e);};
				el.onclick = function (e) {e = Util.event(e);e.preventDefault();};
			}
			if ($('contentframe')) $('contentframe').ondragstart = function (e) {return false;};
		},
		disableAdvancedStyleSheet : function () {
			if(this.advancedStyleSheet) {
				this.advancedStyleSheet.disabled = true;
			}
		},
		enableAdvancedStyleSheet : function () {
			if(this.advancedStyleSheet) {
				this.advancedStyleSheet.disabled = false;
			}
		},
		init : function (sampleMode) {
			this.initCommon();
			this.setSampleMode(sampleMode);
			if(!this.sampleMode) {
				this.initAdvanced();
			} else {
				this.initSample();
			}
			return true;
		},
		initAdvanced : function () {
			this.initMoveableArea();
			this.initPosition();
			this.setDefalutMenu();
			this.enableAdvancedStyleSheet();
			this.showControlPanel();
			this.initTips();
			if(this.goonDIY) this.goonDIY();
			this.openfn();
		},

		openfn : function () {
			var openfn = loadUserdata('openfn');
			if(openfn) {
				if(typeof openfn == 'function') {
					openfn();
				} else {
					eval(openfn);
				}
				saveUserdata('openfn', '');
			}
		},
		initCommon : function () {
			this.advancedStyleSheet = $('diy_common');
			this.menu = [];
		},
		initSample : function () {
			this._getMoveableArea();
			this.initPosition();
			this.sampleBlocks = $C(this.blockClass);
			this.initSampleBlocks();
			this.setSampleMenu();
			this.disableAdvancedStyleSheet();
			this.hideControlPanel();
		},
		initSampleBlocks : function () {
			if(this.sampleBlocks) {
				for(var i = 0; i < this.sampleBlocks.length; i++){
					this.checkEdit(this.sampleBlocks[i]);
				}
			}
		},
		setSampleMode : function (sampleMode) {
			if(loadUserdata('diy_advance_mode')) {
				this.sampleMode = '';
			} else {
				this.sampleMode = sampleMode;
				saveUserdata('diy_advance_mode', sampleMode ? '' : '1');
			}

		},

		hideControlPanel : function() {
			Util.show('samplepanel');
			Util.hide('controlpanel');
			Util.hide('diy-tg');
		},

		showControlPanel : function() {
			Util.hide('samplepanel');
			Util.show('controlpanel');
			Util.show('diy-tg');
		},
		checkHasFrame : function (obj) {
			obj = !obj ? this.data : obj;
			for (var i in obj) {
				if (obj[i] instanceof Frame && obj[i].className.indexOf('temp') < 0 ) {
					return true;
				} else if (typeof obj[i] == 'object') {
					if (this.checkHasFrame(obj[i])) return true;
				}
			}
			return false;
		},
		deleteFrame : function (name) {
			if (typeof name == 'string') {
				if (typeof window['c'+name+'_frame'] == 'object' && !BROWSER.ie) delete window['c'+name+'_frame'];
			} else {
				for(var i = 0,L = name.length;i < L;i++) {
					if (typeof window['c'+name[i]+'_frame'] == 'object' && !BROWSER.ie) delete window['c'+name[i]+'_frame'];
				}
 			}
		},
		saveViewTip : function (tipname) {
			if(tipname) {
				saveUserdata(tipname, '1');
				Util.hide(tipname);
			}
			doane();
		},
		initTips : function () {
			var tips = ['diy_backup_tip'];
			for(var i = 0; i < tips.length; i++) {
				if(tips[i] && !loadUserdata(tips[i])) {
					Util.show(tips[i]);
				}
			}
		},
		extend : function (obj) {
			for (var i in obj) {
				this[i] = obj[i];
			}
		}
	};

	DIY = function() {
		this.frames = [];
		this.isChange = false;
		this.spacecss = [];
		this.style = 't1';
		this.currentDiy = 'body';
		this.opSet = [];
		this.opPointer = 0;
		this.backFlag = false;
		this.styleSheet = {} ;
		this.scrollHeight = 0 ;
	};
	DIY.prototype = {

		init : function (mod) {
			drag.init(mod);
			this.style = document.diyform.style.value;
			if (this.style == '') {
				var reg = RegExp('topic\(.*)\/style\.css');
				var href = $('style_css') ? $('style_css').href : '';
				var arr = reg.exec(href);
				this.style = arr && arr.length > 1 ? arr[1] : '';
			}
			this.currentLayout = typeof document.diyform.currentlayout == 'undefined' ? '' : document.diyform.currentlayout.value;
			this.initStyleSheet();
			if (this.styleSheet.rules) this.initDiyStyle();
		},
		initStyleSheet : function () {
			var all = document.styleSheets;
			for (var i=0;i<all.length;i++) {
				var ownerNode = all[i].ownerNode || all[i].owningElement;
				if (ownerNode.id == 'diy_style') {
					this.styleSheet = new styleCss(i);
					return true;
				}
			}
		},
		initDiyStyle : function (css) {
			var allCssText = css || $('diy_style').innerHTML;
			allCssText = allCssText ? allCssText.replace(/\n|\r|\t|  /g,'') : '';
			var random = Math.random(), rules = '';
			var reg = new RegExp('(.*?) ?\{(.*?)\}','g');
			while((rules = reg.exec(allCssText))) {
				var selector = this.checkSelector(rules[1]);
				var cssText = rules[2];
				var cssarr = cssText.split(';');
				var l = cssarr.length;
				for (var k = 0; k < l; k++) {
					var attribute = trim(cssarr[k].substr(0, cssarr[k].indexOf(':')).toLowerCase());
					var value = cssarr[k].substr(cssarr[k].indexOf(':')+1).toLowerCase();
					if (!attribute || !value) continue;
					if (!this.spacecss[selector]) this.spacecss[selector] = [];
					this.spacecss[selector][attribute] = value;
					if (css) this.setStyle(selector, attribute, value, random);
				}
			}
		},
		checkSelector : function (selector) {
			var s  = selector.toLowerCase();
			if (s.toLowerCase().indexOf('body') > -1) {
				var body = BROWSER.ie ? 'BODY' : 'body';
				selector = selector.replace(/body/i,body);
			}
			if (s.indexOf(' a') > -1) {
				selector = BROWSER.ie ? selector.replace(/ [aA]/,' A') : selector.replace(/ [aA]/,' a');
			}
			return selector;
		},
		initPalette : function (id) {
			var bgcolor = '',selector = '',bgimg = '',bgrepeat = '', bgposition = '', bgattachment = '', bgfontColor = '', bglinkColor = '', i = 0;
			var repeat = ['repeat','no-repeat','repeat-x','repeat-y'];
			var attachment = ['scroll','fixed'];
			var position = ['left top','center top','right top','left center','center center','right center','left bottom','center bottom','right bottom'];
			var position_ = ['0% 0%','50% 0%','100% 0%','0% 50%','50% 50%','100% 50%','0% 100%','50% 100%','100% 100%'];

			selector = this.getSelector(id);
			bgcolor = Util.formatColor(this.styleSheet.getRule(selector,'backgroundColor'));
			bgimg = this.styleSheet.getRule(selector,'backgroundImage');
			bgrepeat = this.styleSheet.getRule(selector,'backgroundRepeat');
			bgposition = this.styleSheet.getRule(selector,'backgroundPosition');
			bgattachment = this.styleSheet.getRule(selector,'backgroundAttachment');
			bgfontColor = Util.formatColor(this.styleSheet.getRule(selector,'color'));
			bglinkColor = Util.formatColor(this.styleSheet.getRule(this.getSelector(selector+' a'),'color'));

			var selectedIndex = 0;
			for (i=0;i<repeat.length;i++) {
				if (bgrepeat == repeat[i]) selectedIndex= i;
			}

			$('repeat_mode').selectedIndex = selectedIndex;
			for (i=0;i<attachment.length;i++) {
				$('rabga'+i).checked = (bgattachment == attachment[i] ? true : false);
			}
			var flag = '';
			for (i=0;i<position.length;i++) {
				var className = bgposition == position[i] ? 'red' : '';
				$('bgimgposition'+i).className = className;
				flag = flag ? flag : className;
			}
			if (flag != 'red') {
				for (i=0;i<position_.length;i++) {
					className = bgposition == position_[i] ? 'red' : '';
					$('bgimgposition'+i).className = className;
				}
			}
			$('colorValue').value = bgcolor;
			if ($('cbpb')) $('cbpb').style.backgroundColor = bgcolor;
			$('textColorValue').value = bgfontColor;
			if ($('ctpb')) $('ctpb').style.backgroundColor = bgfontColor;
			$('linkColorValue').value = bglinkColor;
			if ($('clpb')) $('clpb').style.backgroundColor = bglinkColor;

			Util.show($('currentimgdiv'));
			Util.hide($('diyimages'));
			if ($('selectalbum')) $('selectalbum').disabled = 'disabled';
			bgimg = bgimg != '' && bgimg != 'none' ? bgimg.replace(/url\(['|"]{0,1}/,'').replace(/['|"]{0,1}\)/,'') : 'static/image/common/nophotosmall.gif';
			$('currentimg').src = bgimg;
		},
		changeBgImgDiv : function () {
			Util.hide($('currentimgdiv'));
			Util.show($('diyimages'));
			if ($('selectalbum')) $('selectalbum').disabled = '';
		},
		getSpacecssStr : function() {
			var css = '';
			var selectors = ['body', '#hd','#ct', 'BODY'];
			for (var i in this.spacecss) {
				var name = i.split(' ')[0];
				if(selectors.indexOf(name) == -1 && !drag.getObjByName(name.substr(1))) {
					for(var k in this.spacecss) {
						if (k.indexOf(i) > -1) {
							this.spacecss[k] = [];
						}
					}
					continue;
				}
				var rule = this.spacecss[i];
				if (typeof rule == "function") continue;
				var one = '';
				rule = this.formatCssRule(rule);
				for (var j in rule) {
					var content = this.spacecss[i][j];
					if (content && typeof content == "string" && content.length > 0) {
						content = this.trimCssImportant(content);
						content = content ? content + ' !important;' : ';';
						one += j + ":" + content;
					}
				}
				if (one == '') continue;
				css += i + " {" + one + "}";
			}
			return css;
		},
		formatCssRule : function (rule) {
			var arr = ['top', 'right', 'bottom', 'left'], i = 0;
			if (typeof rule['margin-top'] != 'undefined') {
				var margin = rule['margin-bottom'];
				if (margin && margin == rule['margin-top'] && margin == rule['margin-right'] && margin == rule['margin-left']) {
					rule['margin'] = margin;
					for(i=0;i<arr.length;i++) {
						delete rule['margin-'+arr[i]];
					}
				} else {
					delete rule['margin'];
				}
			}
			var border = '', borderb = '', borderr = '', borderl = '';
			if (typeof rule['border-top-color'] != 'undefined' || typeof rule['border-top-width'] != 'undefined' || typeof rule['border-top-style'] != 'undefined') {
				var format = function (css) {
					css = css.join(' ').replace(/!( ?)important/g,'').replace(/  /g,' ').replace(/^ | $/g,'');
					return css ? css + ' !important' : '';
				};
				border = format([rule['border-top-color'], rule['border-top-width'], rule['border-top-style']]);
				borderr = format([rule['border-right-color'], rule['border-right-width'], rule['border-right-style']]);
				borderb = format([rule['border-bottom-color'], rule['border-bottom-width'], rule['border-bottom-style']]);
				borderl = format([rule['border-left-color'], rule['border-left-width'], rule['border-left-style']]);
			} else if (typeof rule['border-top'] != 'undefined') {
				border = rule['border-top'];borderr = rule['border-right'];borderb = rule['border-bottom'];borderl = rule['border-left'];
			}
			if (border) {
				if (border == borderb && border == borderr && border == borderl) {
					rule['border'] = border;
					for(i=0;i<arr.length;i++) {
						delete rule['border-'+arr[i]];
					}
				} else {
					rule['border-top'] = border;rule['border-right'] = borderr;rule['border-bottom'] = borderb;rule['border-left'] = borderl;
					delete rule['border'];
				}
				for(i=0;i<arr.length;i++) {
					delete rule['border-'+arr[i]+'-color'];delete rule['border-'+arr[i]+'-width'];delete rule['border-'+arr[i]+'-style'];
				}
			}
			return rule;
		},
		changeLayout : function (newLayout) {
			if (this.currentLayout == newLayout) return false;

			var data = $('layout'+newLayout).getAttribute('data');
			var dataArr = data.split(' ');

			var currentLayoutLength = this.currentLayout.length;
			var newLayoutLength = newLayout.length;

			if (newLayoutLength == currentLayoutLength){
				$('frame1_left').style.width = dataArr[0]+'px';
				$('frame1_center').style.width = dataArr[1]+'px';
				if (typeof(dataArr[2]) != 'undefined') $('frame1_right').style.width = dataArr[2]+'px';
			} else if (newLayoutLength > currentLayoutLength) {
				var block = this.getRandomBlockName();
				var dom = document.createElement('div');
				dom.id = 'frame1_right';
				dom.className = drag.moveableColumn + ' z';
				dom.appendChild($(block));
				$('frame1').appendChild(dom);
				$('frame1_left').style.width = dataArr[0]+'px';
				$('frame1_center').style.width = dataArr[1]+'px';
				dom.style.width = dataArr[2]+'px';
			} else if (newLayoutLength < currentLayoutLength) {
				var _length = drag.data['diypage'][0]['columns']['frame1_right']['children'].length;
				var tobj = $('frame1_center_temp');
				for (var i = 0; i < _length; i++) {
					var name = drag.data['diypage'][0]['columns']['frame1_right']['children'][i].name;
					if (name.indexOf('temp') < 0) $('frame1_center').insertBefore($(name),tobj);
				}
				$('frame1').removeChild($('frame1_right'));
				$('frame1_left').style.width = dataArr[0]+'px';
				$('frame1_center').style.width = dataArr[1]+'px';
			}

			var className = $('layout'+this.currentLayout).className;
			$('layout'+this.currentLayout).className = '';
			$('layout'+newLayout).className = className;
			this.currentLayout = newLayout;
			drag.initPosition();
			drag.setClose();
		},
		getRandomBlockName : function () {
			var left = drag.data['diypage'][0]['columns']['frame1_left']['children'];
			if (left.length > 2) {
				var block = left[0];
			} else {
				var block = drag.data['diypage'][0]['columns']['frame1_center']['children'][0];
			}
			return block.name;
		},
		changeStyle : function (t) {
			if (t == '') return false;
			$('style_css').href=STATICURL+t+"/style.css";

			if (!this.backFlag) {
				var oldData = [this.style];
				var newData = [t];
				var random = Math.random();
				this.addOpRecord ('this.changeStyle', newData, oldData, random);
			}
			var arr = t.split("/");
			this.style = arr[arr.length-1];
			drag.setClose();
		},
		setCurrentDiy : function (type) {
			if (type) {
				$('diy_tag_'+this.currentDiy).className = '';
				this.currentDiy = type;
				$('diy_tag_'+this.currentDiy).className = 'activity';
				this.initPalette(this.currentDiy);
			}
		},
		hideBg : function () {
			var random = Math.random();
			this.setStyle(this.currentDiy, 'background-image', '', random);
			this.setStyle(this.currentDiy, 'background-color', '', random);
			if (this.currentDiy == 'hd') this.setStyle(this.currentDiy, 'height', '', random);
			this.initPalette(this.currentDiy);
		},
		toggleHeader : function () {
			var random = Math.random();
			if ($('header_hide_cb').checked) {
				this.setStyle('#hd', 'height', '260px', random);
				this.setStyle('#hd', 'background-image', 'none', random);
				this.setStyle('#hd', 'border-width', '0px', random);
			} else {
				this.setStyle('#hd', 'height', '', random);
				this.setStyle('#hd', 'background-image', '', random);
				this.setStyle('#hd', 'border-width', '', random);
			}
		},
		setBgImage : function (value) {
			var path = typeof value == "string" ? value : value.src;
			if (path.indexOf('.thumb') > 0) {
				path = path.substring(0,path.indexOf('.thumb'));
			}
			if (path == '' || path == 'undefined') return false;
			var random = Math.random();
			this.setStyle(this.currentDiy, 'background-image', Util.url(path), random);
			this.setStyle(this.currentDiy, 'background-repeat', 'repeat', random);
			if (this.currentDiy == 'hd') {
				var _method = this;
				var img = new Image();
				img.onload = function () {
					if (parseInt(img.height) < 140) {
						DIY.prototype.setStyle.call(_method, _method.currentDiy, 'background-repeat', 'repeat', random);
						DIY.prototype.setStyle.call(_method, _method.currentDiy, 'height', '', random);
					} else {
						DIY.prototype.setStyle.call(_method, _method.currentDiy, 'height', img.height+"px", random);
					}
				};
				img.src = path;
			}
		},
		setBgRepeat : function (value) {
			var repeat = ['repeat','no-repeat','repeat-x','repeat-y'];
			if (typeof repeat[value] == 'undefined') {return false;}
			this.setStyle(this.currentDiy, 'background-repeat', repeat[value]);
		},
		setBgAttachment : function (value) {
			var attachment = ['scroll','fixed'];
			if (typeof attachment[value] == 'undefined') {return false;}
			this.setStyle(this.currentDiy, 'background-attachment', attachment[value]);
		},
		setBgColor : function (color) {
			this.setStyle(this.currentDiy, 'background-color', color);
		},
		setTextColor : function (color) {
			this.setStyle(this.currentDiy, 'color', color);
		},
		setLinkColor : function (color) {
			this.setStyle(this.currentDiy + ' a', 'color', color);
		},
		setBgPosition : function (id) {
			var td = $(id);
			var i = id.substr(id.length-1);
			var position = ['left top','center top','right top','left center','center center','right center','left bottom','center bottom','right bottom'];
			td.className = 'red';
			for (var j=0;j<9;j++) {
				if (i != j) {
					$('bgimgposition'+j).className = '';
				}
			}
			this.setStyle(this.currentDiy, 'background-position', position[i]);
		},
		getSelector : function (currentDiv) {
			var arr = currentDiv.split(' ');
			currentDiv = arr[0];
			var link = '';
			if (arr.length > 1) {
				link = (arr[arr.length-1].toLowerCase() == 'a') ? link = ' '+arr.pop() : '';
				currentDiv = arr.join(' ');
			}
			var selector = '';
			switch(currentDiv) {
				case 'blocktitle' :
					selector = '#ct .move-span .blocktitle';
					break;
				case 'body' :
				case 'BODY' :
					selector = BROWSER.ie ? 'BODY' : 'body';
					break;
				default :
					selector = currentDiv.indexOf("#")>-1 ? currentDiv : "#"+currentDiv;
			}
			var rega = BROWSER.ie ? ' A' : ' a';
			selector = (selector+link).replace(/ a/i,rega);
			return selector;
		},
		setStyle : function (currentDiv, property, value, random, num){
			property = trim(property).toLowerCase();
			var propertyJs = property.property2js();
			if (typeof value == 'undefined') value = '';
			var selector = this.getSelector(currentDiv);

			if (!this.backFlag) {
				var rule = this.styleSheet.getRule(selector,propertyJs);
				rule = rule.replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
				var oldData = [currentDiv, property, rule];
				var newData = [currentDiv, property, value];
				if (typeof random == 'undefined') random = Math.random();
				this.addOpRecord ('this.setStyle', newData, oldData, random);
			}
			value = this.trimCssImportant(value);
			value = value ? value + ' !important' : '';
			var pvalue = value ? property+':'+value : '';
			this.styleSheet.addRule(selector,pvalue,num,property);
			Util.recolored();

			if (typeof this.spacecss[selector] == 'undefined') {
				this.spacecss[selector] = [];
			}
			this.spacecss[selector][property] = value;
			drag.setClose();
		},
		trimCssImportant : function (value) {
			if (value instanceof Array) value = value.join(' ');
			return value ? value.replace(/!( ?)important/g,'').replace(/  /g,' ').replace(/^ | $/g,'') : '';
		},
		removeCssSelector : function (selector) {
			for (var i in this.spacecss) {
				if (typeof this.spacecss[i] == "function") continue;
				if (i.indexOf(selector) > -1) {
					this.styleSheet.removeRule(i);
					this.spacecss[i] = [];
				}
			}
		},
		undo : function () {
			if (this.opSet.length == 0) return false;
			var oldData = '';
			if (this.opPointer <= 0) {return '';}
			var step = this.opSet[--this.opPointer];
			var random = step['random'];

			var cmd = step['cmd'].split(',');
			var cmdNum = cmd.length;

			if (cmdNum >1) {
				for (var i=0; i<cmdNum; i++) {
					oldData = typeof step['oldData'][i] == 'undefined' || step['oldData'] == '' ? '' : '"' + step['oldData'][i].join('","') + '"';
					this.backFlag = true;
					eval(cmd[i]+'('+oldData+')');
					this.backFlag = false;
				}
			} else {
				oldData = typeof step['oldData'] == 'undefined' || step['oldData'] == '' ? '' : '"' + step['oldData'].join('","') + '"';
				this.backFlag = true;
				eval(cmd+'('+oldData+')');
				this.backFlag = false;
			}
			$('button_redo').className = '';
			if (this.opPointer == 0) {
				$('button_undo').className = 'unusable';
				drag.isChange = false;
				drag.clearClose();
				return '';
			} else if (random == this.opSet[this.opPointer-1]['random']) {
				this.undo();
				return '';
			} else {
				return '';
			}
		},
		redo : function () {
			if (this.opSet.length == 0) return false;
			var newData = '',random = '';
			if (this.opPointer >= this.opSet.length) return '';
			var step = this.opSet[this.opPointer++];
			random = step['random'];

			var cmd = step['cmd'].split(',');
			var cmdNum = cmd.length;

			if (cmdNum >1) {
				for (var i=0; i<cmdNum; i++) {
					newData = typeof step['newData'][i] == 'undefined' || step['oldData'] == '' ? '' : '"' + step['newData'][i].join('","') + '"';
					this.backFlag = true;
					eval(cmd[i]+'('+newData+')');
					this.backFlag = false;
				}
			}else {
				newData = typeof step['newData'] == 'undefined' || step['oldData'] == '' ? '' : '"' + step['newData'].join('","') + '"';
				this.backFlag = true;
				eval(cmd+'('+newData+')');
				this.backFlag = false;
			}
			$('button_undo').className = '';
			if (this.opPointer == this.opSet.length) {
				$('button_redo').className = 'unusable';
				return '';
			} else if(random == this.opSet[this.opPointer]['random']){
				this.redo();
			}
		},
		addOpRecord : function (cmd, newData, oldData, random) {
			if (this.opPointer == 0) this.opSet = [];
			this.opSet[this.opPointer++] = {'cmd':cmd, 'newData':newData, 'oldData':oldData, 'random':random};

			$('button_undo').className = '';
			$('button_redo').className = 'unusable';
			Util.show('recover_button');
		},
		recoverStyle : function () {
			var random = Math.random();
			for (var selector in this.spacecss){
				var style = this.spacecss[selector];
				if (typeof style == "function") {continue;}
				for (var attribute in style) {
					if (typeof style[attribute] == "function") {continue;}
					this.setStyle(selector,attribute,'',random);
				}
			}
			Util.hide('recover_button');
			drag.setClose();
			this.initPalette(this.currentDiy);
		},
		uploadSubmit : function (){
			if (document.uploadpic.attach.value.length<3) {
				alert('请选择您要上传的图片');
				return false;
			}
			if (document.uploadpic.albumid != null) document.uploadpic.albumid.value = $('selectalbum').value;
			return true;
		},
		save : function () {
			return false;
		},
		cancel : function () {
			var flag = false;
			if (this.isChange) {
				flag = confirm(this.cancelConfirm ? this.cancelConfirm : '退出将不会保存您刚才的设置。是否确认退出？');
			}
			if (!this.isChange || flag) {
				location.href = location.href.replace(/[\?|\&]diy\=yes/g,'');
			}
		},
		extend : function (obj) {
			for (var i in obj) {
				this[i] = obj[i];
			}
		}
	};
})();