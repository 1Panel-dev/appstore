/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: imgcropper.js 30998 2012-07-06 07:22:08Z zhangguosheng $
*/
(function(){

	ImgCropper = function() {
		this.options = {
				opacity:	50,
				color:		"",
				width:		0,
				height:		0,
				resize:		false,
				right:		"",
				left:		"",
				up:			"",
				down:		"",
				rightDown:	"",
				leftDown:	"",
				rightUp:	"",
				leftUp:		"",
				min:		false,
				minWidth:	50,
				minHeight:	50,
				scale:		false,
				ratio:		0,
				Preview:	"",
				viewWidth:	0,
				viewHeight:	0
			};
		this.setParameter.apply(this, arguments);
	};
	ImgCropper.prototype = {
		setParameter: function(container, handle, url, options) {
			this._container = $(container);
			this._layHandle = $(handle);
			this.url = url;

			this._layBase = this._container.appendChild(document.createElement("img"));
			this._layCropper = this._container.appendChild(document.createElement("img"));
			this._layCropper.onload = Util.bindApply(this, this.setPos);
			this._tempImg = document.createElement("img");
			this._tempImg.onload = Util.bindApply(this, this.setSize);

			this.options = Util.setOptions(this.options, options || {});

			this.opacity = Math.round(this.options.opacity);
			this.color = this.options.color;
			this.scale = !!this.options.scale;
			this.ratio = Math.max(this.options.ratio, 0);
			this.width = Math.round(this.options.width);
			this.height = Math.round(this.options.height);
			this.setLayHandle = true;

			var oPreview = $(this.options.Preview);
			if(oPreview){
				oPreview.style.position = "relative";
				oPreview.style.overflow = "hidden";
				this.viewWidth = Math.round(this.options.viewWidth);
				this.viewHeight = Math.round(this.options.viewHeight);
				this._view = oPreview.appendChild(document.createElement("img"));
				this._view.style.position = "absolute";
				this._view.onload = Util.bindApply(this, this.SetPreview);
			}
			this._drag = new dzDrag(handle, {limit:true, container:container, onDragMove: Util.bindApply(this, this.setPos)});
			this.resize = !!this.options.resize;
			if(this.resize){
				var op = this.options;
				var _resize = new ImgCropperResize(container, {max: false, scale:true, min:true, minWidth:options.minWidth, minHeight:options.minHeight, onResize: Util.bindApply(this, this.scaleImg)});
				op.rightDown && (_resize.set(op.rightDown, "right-down"));
				op.leftDown && (_resize.set(op.leftDown, "left-down"));
				op.rightUp && (_resize.set(op.rightUp, "right-up"));
				op.leftUp && (_resize.set(op.leftUp, "left-up"));
				op.right && (_resize.set(op.right, "right"));
				op.left && (_resize.set(op.left, "left"));
				op.down && (_resize.set(op.down, "down"));
				op.up && (_resize.set(op.up, "up"));
				this.min = !!this.options.min;
				this.minWidth = Math.round(this.options.minWidth);
				this.minHeight = Math.round(this.options.minHeight);
				this._resize = _resize;
			}
			this._container.style.position = "relative";
			this._container.style.overflow = "hidden";
			this._layHandle.style.zIndex = 200;
			this._layCropper.style.zIndex = 100;
			this._layBase.style.position = this._layCropper.style.position = "absolute";
			this._layBase.style.top = this._layBase.style.left = this._layCropper.style.top = this._layCropper.style.left = 0;
			this.initialize();
		},

		initialize: function() {
			this.color && (this._container.style.backgroundColor = this.color);
			this._tempImg.src = this._layBase.src = this._layCropper.src = this.url;
			if(BROWSER.ie){
				this._layBase.style.filter = "alpha(opacity:" + this.opacity + ")";
				this._layHandle.style.filter = "alpha(opacity:0)";
				this._layHandle.style.backgroundColor = "#FFF";
			} else {
				this._layBase.style.opacity = this.opacity / 100;
			}
			this._view && (this._view.src = this.url);
			if(this.resize){
				with(this._resize){
					Scale = this.scale; Ratio = this.ratio; Min = this.min; minWidth = this.minWidth; minHeight = this.minHeight;
				}
			}
		},
		setPos: function() {
			if(BROWSER.ie == 6.0){ with(this._layHandle.style){ zoom = .9; zoom = 1; }; };
			var p = this.getPos();
			this._layCropper.style.clip = "rect(" + p.Top + "px " + (p.Left + p.Width) + "px " + (p.Top + p.Height) + "px " + p.Left + "px)";
			this.SetPreview();
			parent.resetHeight(this._container, this.getPos(), this._layBase);
		},
		scaleImg:function() {
			this.height = this._resize._styleHeight;
			this.width = this._resize._styleWidth;
			this.initialize();
			this.setSize();
			this.setPos();
			var maxRight = (parseInt(this._layHandle.style.left) || 0) + (parseInt(this._layHandle.offsetWidth) || 0);
			var maxBottom = (parseInt(this._layHandle.style.top) || 0) + (parseInt(this._layHandle.offsetHeight) || 0);
			if(this._container != null) {
				if(maxRight > this._container.clientWidth) {
					var nowLeft = this._container.clientWidth - this._layHandle.offsetWidth;
					this._layHandle.style.left = (nowLeft < 0 ? 0 : nowLeft) + "px";
				}
				if(maxBottom > this._container.clientHeight) {
					var nowTop = this._container.clientHeight - this._layHandle.offsetHeight;
					this._layHandle.style.top = (nowTop < 0 ? 0 : nowTop) + "px";
				}
			}
			parent.resetHeight(this._container, this.getPos(), this._layBase);
		},
		SetPreview: function() {
			if(this._view){
				var p = this.getPos(), s = this.getSize(p.Width, p.Height, this.viewWidth, this.viewHeight), scale = s.Height / p.Height;
				var pHeight = this._layBase.height * scale, pWidth = this._layBase.width * scale, pTop = p.Top * scale, pLeft = p.Left * scale;
				with(this._view.style){
					width = pWidth + "px"; height = pHeight + "px"; top = - pTop + "px "; left = - pLeft + "px";
					clip = "rect(" + pTop + "px " + (pLeft + s.Width) + "px " + (pTop + s.Height) + "px " + pLeft + "px)";
				}
			}
		},
		setSize: function() {
			if(this.width > this._tempImg.width) {
				this.width = this._tempImg.width;
			}
			if(this.height > this._tempImg.height) {
				this.height = this._tempImg.height;
			}
			var s = this.getSize(this._tempImg.width, this._tempImg.height, this.width, this.height);
			if(this.options.min && (s.Width <= this.options.minWidth || s.Height <= this.options.minHeight)) {
				return false;
			}
			this._layBase.style.width = this._layCropper.style.width = s.Width + "px";
			this._layBase.style.height = this._layCropper.style.height = s.Height + "px";
			this._drag.maxRight = s.Width; this._drag.maxBottom = s.Height;
			if(this.resize) {
				this._container.style.width = this._layBase.style.width; this._container.style.height = this._layBase.style.height;
				if(this.setLayHandle) {
					this._layHandle.style.left = ((s.Width - this._layHandle.offsetWidth)/2)+"px";
					this._layHandle.style.top = ((s.Height - this._layHandle.offsetHeight)/2)+"px";
					this.setPos();
					this.setLayHandle = false;
				}
			}
		},
		getPos: function() {
			with(this._layHandle){
				return { Top: offsetTop, Left: offsetLeft, Width: offsetWidth, Height: offsetHeight };
			}
		},
		getSize: function(nowWidth, nowHeight, fixWidth, fixHeight) {
			var iWidth = nowWidth, iHeight = nowHeight, scale = iWidth / iHeight;
			if(fixHeight){ iWidth = (iHeight = fixHeight) * scale; }
			if(fixWidth && (!fixHeight || iWidth > fixWidth)){ iHeight = (iWidth = fixWidth) / scale; }
			return { Width: iWidth, Height: iHeight }
		}
	};

	ImgCropperResize = function() {
		this.options = {
			max:		false,
			container:	"",
			maxLeft:	0,
			maxRight:	9999,
			maxTop:		0,
			maxBottom:	9999,
			min:		false,
			minWidth:	50,
			minHeight:	50,
			scale:		false,
			ratio:		0,
			onResize:	function(){}
		};
		this.initialize.apply(this, arguments);
	};

	ImgCropperResize.prototype = {
		initialize: function(resizeObjId, options) {
			this.options = Util.setOptions(this.options, options || {});
			this._resizeObj = $(resizeObjId);

			this._styleWidth = this._styleHeight = this._styleLeft = this._styleTop = 0;
			this._sideRight = this._sideDown = this._sideLeft = this._sideUp = 0;
			this._fixLeft = this._fixTop = 0;
			this._scaleLeft = this._scaleTop = 0;

			this._maxSet = function(){};
			this._maxRightWidth = this._maxDownHeight = this._maxUpHeight = this._maxLeftWidth = 0;
			this._maxScaleWidth = this._maxScaleHeight = 0;

			this._fun = function(){};

			var _style = Util.currentStyle(this._resizeObj);
			this._borderX = (parseInt(_style.borderLeftWidth) || 0) + (parseInt(_style.borderRightWidth) || 0);
			this._borderY = (parseInt(_style.borderTopWidth) || 0) + (parseInt(_style.borderBottomWidth) || 0);
			this._resizeTranscript = Util.bindApply(this, this.resize);
			this._stopTranscript = Util.bindApply(this, this.stop);

			this.max = !!this.options.max;
			this._container = $(this.options.container) || null;
			this.maxLeft = Math.round(this.options.maxLeft);
			this.maxRight = Math.round(this.options.maxRight);
			this.maxTop = Math.round(this.options.maxTop);
			this.maxBottom = Math.round(this.options.maxBottom);
			this.min = !!this.options.min;
			this.minWidth = Math.round(this.options.minWidth);
			this.minHeight = Math.round(this.options.minHeight);
			this.scale = !!this.options.scale;
			this.ratio = Math.max(this.options.ratio, 0);

			this.onResize = this.options.onResize;

			this._resizeObj.style.position = "absolute";
			!this._container || Util.currentStyle(this._container).position == "relative" || (this._container.style.position = "relative");
		},
		set: function(resize, side) {
			var resize = $(resize), fun;
			if(!resize) return;
			switch(side.toLowerCase()) {
				case "up":
					fun = this.up;
					break;
				case "down":
					fun = this.down;
					break;
				case "left":
					fun = this.left;
					break;
				case "right":
					fun = this.right;
					break;
				case "left-up":
					fun = this.leftUp;
					break;
				case "right-up":
					fun = this.rightUp;
					break;
				case "left-down":
					fun = this.leftDown;
					break;
				case "right-down" :
				default:
					fun = this.rightDown;
					break;
			};
			Util.addEventHandler(resize, "mousedown", Util.bindApply(this, this.start, fun));
		},
		start: function(oEvent, fun, touch) {
			oEvent.stopPropagation ? oEvent.stopPropagation() : (oEvent.cancelBubble = true);
			this._fun = fun;

			this._styleWidth = this._resizeObj.clientWidth;
			this._styleHeight = this._resizeObj.clientHeight;
			this._styleLeft = this._resizeObj.offsetLeft;
			this._styleTop = this._resizeObj.offsetTop;
			this._sideLeft = oEvent.clientX - this._styleWidth;
			this._sideRight = oEvent.clientX + this._styleWidth;
			this._sideUp = oEvent.clientY - this._styleHeight;
			this._sideDown = oEvent.clientY + this._styleHeight;
			this._fixLeft = this._styleLeft + this._styleWidth;
			this._fixTop = this._styleTop + this._styleHeight;

			if(this.scale) {
				this.ratio = Math.max(this.ratio, 0) || this._styleWidth / this._styleHeight;
				this._scaleLeft = this._styleLeft + this._styleWidth / 2;
				this._scaleTop = this._styleTop + this._styleHeight / 2;
			};
			if(this.max) {
				var maxLeft = this.maxLeft, maxRight = this.maxRight, maxTop = this.maxTop, maxBottom = this.maxBottom;
				if(!!this._container){
					maxLeft = Math.max(maxLeft, 0);
					maxTop = Math.max(maxTop, 0);
					maxRight = Math.min(maxRight, this._container.clientWidth);
					maxBottom = Math.min(maxBottom, this._container.clientHeight);
				};
				maxRight = Math.max(maxRight, maxLeft + (this.min ? this.minWidth : 0) + this._borderX);
				maxBottom = Math.max(maxBottom, maxTop + (this.min ? this.minHeight : 0) + this._borderY);
				this._mxSet = function() {
					this._maxRightWidth = maxRight - this._styleLeft - this._borderX;
					this._maxDownHeight = maxBottom - this._styleTop - this._borderY;
					this._maxUpHeight = Math.max(this._fixTop - maxTop, this.min ? this.minHeight : 0);
					this._maxLeftWidth = Math.max(this._fixLeft - maxLeft, this.min ? this.minWidth : 0);
				};
				this._mxSet();
				if(this.scale) {
					this._maxScaleWidth = Math.min(this._scaleLeft - maxLeft, maxRight - this._scaleLeft - this._borderX) * 2;
					this._maxScaleHeight = Math.min(this._scaleTop - maxTop, maxBottom - this._scaleTop - this._borderY) * 2;
				}
			}
			Util.addEventHandler(document, "mousemove", this._resizeTranscript);
			Util.addEventHandler(document, "mouseup", this._stopTranscript);
			if(BROWSER.ie){
				Util.addEventHandler(this._resizeObj, "losecapture", this._stopTranscript);
				this._resizeObj.setCapture();
			}else{
				Util.addEventHandler(window, "blur", this._stopTranscript);
				oEvent.preventDefault();
			};
		},
		resize: function(e) {
			window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
			this._fun(e);
			if(this.options.min && (this._styleWidth <= this.options.minWidth || this._styleHeight <= this.options.minHeight)) {
				return false;
			}
			with(this._resizeObj.style) {
				width = this._styleWidth + "px"; height = this._styleHeight + "px";
				top = this._styleTop + "px"; left = this._styleLeft + "px";
			}
			this.onResize();
		},
		up: function(e) {
			this.repairY(this._sideDown - e.clientY, this._maxUpHeight);
			this.repairTop();
			this.turnDown(this.down);
		},
		down: function(e) {
			this.repairY(e.clientY - this._sideUp, this._maxDownHeight);
			this.turnUp(this.up);
		},
		right: function(e) {
			this.repairX(e.clientX - this._sideLeft, this._maxRightWidth);
			this.turnLeft(this.left);
		},
		left: function(e) {
			this.repairX(this._sideRight - e.clientX, this._maxLeftWidth);
			this.repairLeft();
			this.turnRight(this.right);
		},
		rightDown: function(e) {
			this.repairAngle(
				e.clientX - this._sideLeft, this._maxRightWidth,
				e.clientY - this._sideUp, this._maxDownHeight
			);
			this.turnLeft(this.leftDown) || this.scale || this.turnUp(this.rightUp);
		},
		rightUp: function(e) {
			this.repairAngle(
				e.clientX - this._sideLeft, this._maxRightWidth,
				this._sideDown - e.clientY, this._maxUpHeight
			);
			this.repairTop();
			this.turnLeft(this.leftUp) || this.scale || this.turnDown(this.rightDown);
		},
		leftDown: function(e) {
			this.repairAngle(
				this._sideRight - e.clientX, this._maxLeftWidth,
				e.clientY - this._sideUp, this._maxDownHeight
			);
			this.repairLeft();
			this.turnRight(this.rightDown) || this.scale || this.turnUp(this.leftUp);
		},
		leftUp: function(e) {
			this.repairAngle(
				this._sideRight - e.clientX, this._maxLeftWidth,
				this._sideDown - e.clientY, this._maxUpHeight
			);
			this.repairTop();
			this.repairLeft();
			this.turnRight(this.rightUp) || this.scale || this.turnDown(this.leftDown);
		},
		repairX: function(iWidth, maxWidth) {
			iWidth = this.repairWidth(iWidth, maxWidth);
			if(this.scale){
				var iHeight = this.repairScaleHeight(iWidth);
				if(this.max && iHeight > this._maxScaleHeight){
					iHeight = this._maxScaleHeight;
					iWidth = this.repairScaleWidth(iHeight);
				}else if(this.min && iHeight < this.minHeight){
					var tWidth = this.repairScaleWidth(this.minHeight);
					if(tWidth < maxWidth){ iHeight = this.minHeight; iWidth = tWidth; }
				}
				this._styleHeight = iHeight;
				this._styleTop = this._scaleTop - iHeight / 2;
			}
			this._styleWidth = iWidth;
		},
		repairY: function(iHeight, maxHeight) {
			iHeight = this.repairHeight(iHeight, maxHeight);
			if(this.scale){
				var iWidth = this.repairScaleWidth(iHeight);
				if(this.max && iWidth > this._maxScaleWidth){
					iWidth = this._maxScaleWidth;
					iHeight = this.repairScaleHeight(iWidth);
				}else if(this.min && iWidth < this.minWidth){
					var tHeight = this.repairScaleHeight(this.minWidth);
					if(tHeight < maxHeight){ iWidth = this.minWidth; iHeight = tHeight; }
				}
				this._styleWidth = iWidth;
				this._styleLeft = this._scaleLeft - iWidth / 2;
			}
			this._styleHeight = iHeight;
		},
		repairAngle: function(iWidth, maxWidth, iHeight, maxHeight) {
			iWidth = this.repairWidth(iWidth, maxWidth);
			if(this.scale) {
				iHeight = this.repairScaleHeight(iWidth);
				if(this.max && iHeight > maxHeight){
					iHeight = maxHeight;
					iWidth = this.repairScaleWidth(iHeight);
				}else if(this.min && iHeight < this.minHeight){
					var tWidth = this.repairScaleWidth(this.minHeight);
					if(tWidth < maxWidth){ iHeight = this.minHeight; iWidth = tWidth; }
				}
			} else {
				iHeight = this.repairHeight(iHeight, maxHeight);
			}
			this._styleWidth = iWidth;
			this._styleHeight = iHeight;
		},
		repairTop: function() {
			this._styleTop = this._fixTop - this._styleHeight;
		},
		repairLeft: function() {
			this._styleLeft = this._fixLeft - this._styleWidth;
		},
		repairHeight: function(iHeight, maxHeight) {
			iHeight = Math.min(this.max ? maxHeight : iHeight, iHeight);
			iHeight = Math.max(this.min ? this.minHeight : iHeight, iHeight, 0);
			return iHeight;
		},
		repairWidth: function(iWidth, maxWidth) {
			iWidth = Math.min(this.max ? maxWidth : iWidth, iWidth);
			iWidth = Math.max(this.min ? this.minWidth : iWidth, iWidth, 0);
			return iWidth;
		},
		repairScaleHeight: function(iWidth) {
			return Math.max(Math.round((iWidth + this._borderX) / this.ratio - this._borderY), 0);
		},
		repairScaleWidth: function(iHeight) {
			return Math.max(Math.round((iHeight + this._borderY) * this.ratio - this._borderX), 0);
		},
		turnRight: function(fun) {
			if(!(this.min || this._styleWidth)){
				this._fun = fun;
				this._sideLeft = this._sideRight;
				this.max && this._mxSet();
				return true;
			}
		},
		turnLeft: function(fun) {
			if(!(this.min || this._styleWidth)){
				this._fun = fun;
				this._sideRight = this._sideLeft;
				this._fixLeft = this._styleLeft;
				this.max && this._mxSet();
				return true;
			}
		},
		turnUp: function(fun) {
			if(!(this.min || this._styleHeight)){
				this._fun = fun;
				this._sideDown = this._sideUp;
				this._fixTop = this._styleTop;
				this.max && this._mxSet();
				return true;
			}
		},
		turnDown: function(fun) {
			if(!(this.min || this._styleHeight)){
				this._fun = fun;
				this._sideUp = this._sideDown;
				this.max && this._mxSet();
				return true;
			}
		},
		stop: function() {
			Util.removeEventHandler(document, "mousemove", this._resizeTranscript);
			Util.removeEventHandler(document, "mouseup", this._stopTranscript);
			if(BROWSER.ie){
				Util.removeEventHandler(this._resizeObj, "losecapture", this._stopTranscript);
				this._resizeObj.releaseCapture();
			}else{
				Util.removeEventHandler(window, "blur", this._stopTranscript);
			}
		}
	};
	dzDrag = function() {
		this.options = {
			handle:			'',
			limit:			false,
			maxLeft:		0,
			maxRight:		9999,
			maxTop:			0,
			maxBottom:		9999,
			container:		'',
			lockX:			false,
			lockY:			false,
			onDragStart:	function(){},
			onDragMove:		function(){},
			onDragEnd:		function(){}
		};
		this.initialize.apply(this, arguments);
	};
	dzDrag.prototype = {
		initialize: function(dragId, options) {
			this.options = Util.setOptions(this.options, options || {});
			this._dragObj = $(dragId);
			this._x = this._y = 0;
			this._marginLeft = this._marginTop = 0;
			this._handle = $(this.options.handle) || this._dragObj;
			this._container = $(this.options.container) || null;
			this._dragObj.style.position = "absolute";
			this._dragEndTranscript = Util.bindApply(this, this.dragEnd);
			this._dragMoveTranscript = Util.bindApply(this, this.dragMove);
			Util.addEventHandler(this._handle, "mousedown", Util.bindApply(this, this.dragStart));
		},
		dragStart: function(event) {

			this.setLimit();
			this._x = event.clientX - this._dragObj.offsetLeft;
			this._y = event.clientY - this._dragObj.offsetTop;

			var curStyle = Util.currentStyle(this._dragObj);
			this._marginLeft = parseInt(curStyle.marginLeft) || 0;
			this._marginTop = parseInt(curStyle.marginTop) || 0;
			Util.addEventHandler(document, "mousemove", this._dragMoveTranscript);
			Util.addEventHandler(document, "mouseup", this._dragEndTranscript);
			if(BROWSER.ie){
				Util.addEventHandler(this._handle, "losecapture", this._dragEndTranscript);
				this._handle.setCapture();
			}else{
				Util.addEventHandler(window, "blur", this._dragEndTranscript);
				event.preventDefault();
			};
			this.options.onDragStart();
		},
		dragMove: function(event) {
			window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
			var iLeft = event.clientX - this._x;
			var iTop = event.clientY - this._y;
			if(this.options.limit) {
				var maxLeft = this.options.maxLeft, maxRight = this.options.maxRight, maxTop = this.options.maxTop, maxBottom = this.options.maxBottom;
				if(this._container != null){
					maxLeft = Math.max(maxLeft, 0);
					maxTop = Math.max(maxTop, 0);
					maxRight = Math.min(maxRight, this._container.clientWidth);
					maxBottom = Math.min(maxBottom, this._container.clientHeight);
				};
				iLeft = Math.max(Math.min(iLeft, maxRight - this._dragObj.offsetWidth), maxLeft);
				iTop = Math.max(Math.min(iTop, maxBottom - this._dragObj.offsetHeight), maxTop);
			}
			if(!this.options.lockX) { this._dragObj.style.left = iLeft - this._marginLeft + "px"; }
			if(!this.options.lockY) { this._dragObj.style.top = iTop - this._marginTop + "px"; }
			this.options.onDragMove();
		},
		dragEnd: function(event) {
			Util.removeEventHandler(document, "mousemove", this._dragMoveTranscript);
			Util.removeEventHandler(document, "mouseup", this._dragEndTranscript);
			if(BROWSER.ie) {
				Util.removeEventHandler(this._handle, "losecapture", this._dragEndTranscript);
				this._handle.releaseCapture();
			} else {
				Util.removeEventHandler(window, "blur", this._dragEndTranscript);
			}
			this.options.onDragEnd();
		},
		setLimit: function() {
			if(this.options.limit) {
				this.options.maxRight = Math.max(this.options.maxRight, this.options.maxLeft + this._dragObj.offsetWidth);
				this.options.maxBottom = Math.max(this.options.maxBottom, this.options.maxTop + this._dragObj.offsetHeight);
				!this._container || Util.currentStyle(this._container).position == "relative" || Util.currentStyle(this._container).position == "absolute" || (this._container.style.position = "relative");
			}
		}
	};
	var Util = {
		setOptions: function(object, source) {
			for(var property in source) {
				object[property] = source[property];
			}
			return object;
		},
		addEventHandler: function(targetObj, eventType, funHandler) {
			if(targetObj.addEventListener) {
				targetObj.addEventListener(eventType, funHandler, false);
			} else if (targetObj.attachEvent) {
				targetObj.attachEvent("on" + eventType, funHandler);
			} else {
				targetObj["on" + eventType] = funHandler;
			}
		},
		removeEventHandler: function(targetObj, eventType, funHandler) {
			if(targetObj.removeEventListener) {
				targetObj.removeEventListener(eventType, funHandler, false);
			} else if (targetObj.detachEvent) {
				targetObj.detachEvent("on" + eventType, funHandler);
			} else {
				targetObj["on" + eventType] = null;
			}
		},
		bindApply: function(object, fun) {
			var args = Array.prototype.slice.call(arguments).slice(2);
			return function(event) {
				return fun.apply(object, [event || window.event].concat(args));
			};
		},
		currentStyle: function(element){
			return element.currentStyle || document.defaultView.getComputedStyle(element, null);
		}
	};
})();