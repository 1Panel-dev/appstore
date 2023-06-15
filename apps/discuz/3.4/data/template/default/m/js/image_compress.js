var ImageCompresser = {
	/**
	 * 检测ios平台是否有做过抽样处理
	 * @param {Element} img
	 */
	isIosSubSample: function (img) {
		var w = img.naturalWidth,
			h = img.naturalHeight,
			hasSubSample = false;
		if (w * h > 1024 * 1024) {
			var canvas = document.createElement('canvas');
			ctx = canvas.getContext('2d'),
				canvas.width = canvas.height = 1;
			ctx.drawImage(img, 1 - w, 0);
			hasSubSample = ctx.getImageData(0, 0, 1, 1).data[3] === 0;
			canvas = ctx = null;
		}
		return hasSubSample;
	},
	getIosImageRatio: function (img, w, h) {
		var canvas = document.createElement('canvas'),
			ctx = canvas.getContext('2d'),
			data,
			sy = 0,
			ey = h,
			py = h;
		canvas.width = 1;
		canvas.height = h;
		ctx.drawImage(img, 1 - Math.ceil(Math.random() * w), 0);
		data = ctx.getImageData(0, 0, 1, h).data;
		while (py > sy) {
			var alpha = data[(py - 1) * 4 + 3];//Notice:如果原图自带透明度，这里可能会失效
			if (alpha === 0) {
				ey = py;
			} else {
				sy = py;
			}
			py = (ey + sy) >> 1;
		}
		return py / h;
	},
	getImageBase64: function (img, opts) {
		opts = jQuery.extend({
			maxW: 800,
			maxH: 800,
			quality: 0.8,
			orien: 1
		}, opts);
		var maxW = opts.maxW,
			maxH = opts.maxW,
			quality = opts.quality,
			_w = img.naturalWidth,
			_h = img.naturalHeight,
			w, h;
		if (jq.os.ios && ImageCompresser.isIosSubSample(img)) {
			_w = _w / 2;
			_h = _h / 2;
		}
		if (_w > maxW && _w / _h >= maxW / maxH) {
			w = maxW;
			h = _h * maxW / _w;
		} else if (_h > maxH && _h / _w >= maxH / maxW) {
			w = _w * maxH / _h;
			h = maxH;
		} else {
			w = _w;
			h = _h;
		}
		var canvas = document.createElement('canvas'),
			ctx = canvas.getContext('2d'),
			base64Str;
		this.doAutoRotate(canvas, w, h, opts.orien);
		if (jq.os.ios) {
			var tmpCanvas = document.createElement('canvas'),
				tmpCtx = tmpCanvas.getContext('2d'),
				d = 1024,
				vertSquashRatio = ImageCompresser.getIosImageRatio(img, _w, _h), //ios平台大尺寸图片压缩比
				sx, sy, sw, sh, dx, dy, dw, dh;
			tmpCanvas.width = tmpCanvas.height = d;
			sy = 0;
			while (sy < _h) {
				sh = sy + d > _h ? _h - sy : d,
					sx = 0;
				while (sx < _w) {
					sw = sx + d > _w ? _w - sx : d;
					tmpCtx.clearRect(0, 0, d, d);
					tmpCtx.drawImage(img, -sx, -sy);
					dx = Math.floor(sx * w / _w);
					dw = Math.ceil(sw * w / _w);
					dy = Math.floor(sy * h / _h / vertSquashRatio);
					dh = Math.ceil(sh * h / _h / vertSquashRatio);
					ctx.drawImage(tmpCanvas, 0, 0, sw, sh, dx, dy, dw, dh);
					sx += d;
				}
				sy += d;
			}
			tmpCanvas = tmpCtx = null;
		} else {
			ctx.drawImage(img, 0, 0, _w, _h, 0, 0, w, h);
		}
		if (jq.os.android) {
			var imgData = ctx.getImageData(0, 0, canvas.width, canvas.height),
				encoder = new JPEGEncoder(quality * 100);
			base64Str = encoder.encode(imgData);
			encoder = null;
		} else {
			base64Str = canvas.toDataURL('image/jpeg', quality);
		}

		console.log(base64Str);
		canvas = ctx = null;
		return base64Str;
	},
	doAutoRotate: function (canvas, width, height, orientation) {
		var ctx = canvas.getContext('2d');
		if (orientation >= 5 && orientation <= 8) {
			canvas.width = height;
			canvas.height = width;
		} else {
			canvas.width = width;
			canvas.height = height;
		}
		switch (orientation) {
			case 2:
				ctx.translate(width, 0);
				ctx.scale(-1, 1);
				break;
			case 3:
				ctx.translate(width, height);
				ctx.rotate(Math.PI);
				break;
			case 4:
				ctx.translate(0, height);
				ctx.scale(1, -1);
				break;
			case 5:
				ctx.rotate(0.5 * Math.PI);
				ctx.scale(1, -1);
				break;
			case 6:
				ctx.rotate(0.5 * Math.PI);
				ctx.translate(0, -height);
				break;
			case 7:
				ctx.rotate(0.5 * Math.PI);
				ctx.translate(width, -height);
				ctx.scale(-1, 1);
				break;
			case 8:
				ctx.rotate(-0.5 * Math.PI);
				ctx.translate(-width, 0);
				break;
			default:
				break;
		}
	},
	getFileObjectURL: function (file) {
		var URL = window.URL || window.webkitURL || false;
		if (URL) {
			return URL.createObjectURL(file);
		}
	},
	support: function () {
		return typeof (window.File) && typeof (window.FileList) && typeof (window.FileReader) && typeof (window.Blob);
	}
};