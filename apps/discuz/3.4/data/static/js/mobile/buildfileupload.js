jQuery.extend({
	buildfileupload: function(s) {
		try {
			var reader = new FileReader();
			var canvaszoom = false;
			if(1 || (s.maxfilesize && s.files[0].size > s.maxfilesize * 1024)) {
				canvaszoom = true;
			}

			var picupload = function(picdata) {

				if(!XMLHttpRequest.prototype.sendAsBinary){
					XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
						function byteValue(x) {
							return x.charCodeAt(0) & 0xff;
						}
						var ords = Array.prototype.map.call(datastr, byteValue);
						var ui8a = new Uint8Array(ords);
						this.send(ui8a.buffer);
					}
				}

				var xhr = new XMLHttpRequest(),
					file = s.files[0],
					index = 0,
					start_time = new Date().getTime(),
					boundary = '------multipartformboundary' + (new Date).getTime(),
					builder;

				builder = jQuery.getbuilder(s, file.name, picdata, boundary);

				if(s.uploadpercent) {
					xhr.upload.onprogress = function(e) {
						if(e.lengthComputable) {
							var percent = Math.ceil((e.loaded / e.total) * 100);
							$('#' + s.uploadpercent).html(percent + '%');
						}
					};
				}

				xhr.open("POST", s.uploadurl, true);
				xhr.setRequestHeader('content-type', 'multipart/form-data; boundary='
					+ boundary);

				xhr.sendAsBinary(builder);

				xhr.onerror = function() {
					s.error();
				};
				xhr.onabort = function() {
					s.error();
				};
				xhr.ontimeout = function() {
					s.error();
				};
				xhr.onload = function() {
					if(xhr.responseText) {
						s.success(xhr.responseText);
					}
				};
			};

			var getorientation = function(binfile) {
				function getbyteat(offset) {
					return binfile.charCodeAt(offset) & 0xFF;
				}
				function getbytesat(offset, length) {
					var bytes = [];
					for(var i=0; i<length; i++) {
						bytes[i] = binfile.charCodeAt((offset + i)) & 0xFF;
					}
					return bytes;
				}
				function getshortat(offset, bigendian) {
					var shortat = bigendian ?
						(getbyteat(offset) << 8) + getbyteat(offset + 1)
						: (getbyteat(offset + 1) << 8) + getbyteat(offset);
					if(shortat < 0) {
						shortat += 65536;
					}
					return shortat;
				}
				function getlongat(offset, bigendian) {
					var byte1 = getbyteat(offset);
					var byte2 = getbyteat(offset + 1);
					var byte3 = getbyteat(offset + 2);
					var byte4 = getbyteat(offset + 3);
					var longat = bigendian ?
						(((((byte1 << 8) + byte2) << 8) + byte3) << 8) + byte4
						: (((((byte4 << 8) + byte3) << 8) + byte2) << 8) + byte1;
					if(longat < 0) longat += 4294967296;
					return longat;
				}
				function getslongat(offset, bigendian) {
					var ulongat = getlongat(offset, bigendian);
					if(ulongat > 2147483647) {
						return ulongat - 4294967296;
					} else {
						return ulongat;
					}
				}
				function getstringat(offset, length) {
					var str = [];
					var bytes = getbytesat(offset, length);
					for(var i=0; i<length; i++) {
						str[i] = String.fromCharCode(bytes[i]);
					}
					return str.join('');
				}
				function readtagvalue(entryoffset, tiffstart, dirstart, bigend) {
					var type = getshortat(entryoffset + 2, bigend);
					var numvalues = getlongat(entryoffset + 4, bigend);
					var valueoffset = getlongat(entryoffset + 8, bigend) + tiffstart;
					var offset, vals;
					switch(type) {
						case 1:
						case 7:
							if(numvalues == 1) {
								return getbyteat(entryoffset + 8, bigend);
							} else {
								offset = numvalues > 4 ? valueoffset : (entryoffset + 8);
								vals = [];
								for(var n=0; n<numvalues; n++) {
									vals[n] = getbyteat(offset + n);
								}
								return vals;
							}
						case 2:
							offset = numvalues > 4 ? valueoffset : (entryoffset + 8);
							return getstringat(offset, numvalues - 1);
						case 3:
							if(numvalues == 1) {
								return getshortat(entryoffset + 8, bigend);
							} else {
								offset = numvalues > 2 ? valueoffset : (entryoffset + 8);
								vals = [];
								for(var n=0;n<numvalues; n++) {
									vals[n] = getshortat(offset + 2 * n, bigend);
								}
								return vals;
							}
						case 4:
							if(numvalues == 1) {
								return getlongat(entryoffset + 8, bigend);
							} else {
								vals = [];
								for(var n=0; n<numvalues; i++) {
									vals[n] = getlongat(valueoffset + 4 * n, bigend);
								}
								return vals;
							}
						case 5:
							if(numvalues == 1) {
								var numerator = getlongat(valueoffset, bigend);
								var denominator = getlongat(valueoffset + 4, bigend);
								var val = new Number(numerator / denominator);
								val.numerator = numerator;
								val.denominator = denominator;
								return val;
							} else {
								vals = [];
								for(var n=0; n<numvalues; n++) {
									var numerator = getlongat(valueoffset + 8*n, bigend);
									var denominator = getlongat(valueoffset+4 + 8*n, bigend);
									vals[n] = new Number(numerator / denominator);
									vals[n].numerator = numerator;
									vals[n].denominator = denominator;
								}
								return vals;
							}
						case 9:
							if(numvalues == 1) {
								return getslongat(entryoffset + 8, bigend);
							} else {
								vals = [];
								for(var n=0;n<numvalues; n++) {
									vals[n] = getslongat(valueoffset + 4 * n, bigend);
								}
								return vals;
							}
						case 10:
							if(numvalues == 1) {
								return getslongat(valueoffset, bigend) / getslongat(valueoffset+4, bigend);
							} else {
								vals = [];
								for(var n=0; n<numvalues; n++) {
									vals[n] = getslongat(valuesoffset + 8*n, bigend) / getslongat(valueoffset+4 + 8*n, bigend);
								}
								return vals;
							}
					}
				}
				function readtags(tiffstart, dirstart, strings, bigend) {
					var entries = getshortat(dirstart, bigend);
					var tags = {}, entryofffset, tag;
					for(var i=0; i<entries; i++) {
						entryoffset = dirstart + i *12 + 2;
						tag = strings[getshortat(entryoffset, bigend)];
						tags[tag] = readtagvalue(entryoffset, tiffstart, dirstart, bigend);
					}
					return tags;
				}
				function readexifdata(start) {
					if(getstringat(start, 4) != 'Exif') {
						return false;
					}
					var bigend;
					var tags, tag;
					var tiffoffset = start + 6;
					if(getshortat(tiffoffset) == 0x4949) {
						bigend = false;
					} else if(getshortat(tiffoffset) == 0x4D4D) {
						bigend = true;
					} else {
						return false;
					}
					if(getshortat(tiffoffset + 2, bigend) != 0x002A) {
						return false;
					}
					if(getlongat(tiffoffset + 4, bigend) != 0x00000008) {
						return false;
					}
					var tifftags = {
						0x0112 : "Orientation"
					};
					tags = readtags(tiffoffset, tiffoffset + 8, tifftags, bigend);
					return tags;
				}
				if(getbyteat(0) != 0xFF || getbyteat(1) != 0xD8) {
					return false;
				}
				var offset = 2;
				var length = binfile.length;
				var marker;
				while(offset < length) {
					if(getbyteat(offset) != 0xFF) {
						return false;
					}
					marker = getbyteat(offset + 1);
					if(marker == 22400 || marker == 225) {
						return readexifdata(offset + 4);
					} else {
						offset += 2 + getshortat(offset + 2, true);
					}
				}
			};



			var detectsubsampling = function(img, imgwidth, imgheight) {
				if(imgheight * imgwidth > 1024 * 1024) {
					var tmpcanvas = document.createElement('canvas');
					tmpcanvas.width = tmpcanvas.height = 1;
					var tmpctx = tmpcanvas.getContext('2d');
					tmpctx.drawImage(img, -imgwidth + 1, 0);
					return tmpctx.getImageData(0, 0, 1, 1).data[3] === 0;
				} else {
					return false;
				}
			};
			var detectverticalsquash = function(img, imgheight) {
				var tmpcanvas = document.createElement('canvas');
				tmpcanvas.width = 1;
				tmpcanvas.height = imgheight;
				var tmpctx = tmpcanvas.getContext('2d');
				tmpctx.drawImage(img, 0, 0);
				var data = tmpctx.getImageData(0, 0, 1, imgheight).data;
				var sy = 0;
				var ey = imgheight;
				var py = imgheight;
				while(py > sy) {
					var alpha = data[(py - 1) * 4 + 3];
					if(alpha === 0) {
						ey = py;
					} else {
						sy = py;
					}
					py = (ey + sy) >> 1;
				}
				var ratio = py / imgheight;
				return (ratio === 0) ? 1 : ratio;
			};
			var transformcoordinate = function(canvas, ctx, width, height, orientation) {
				switch(orientation) {
					case 5:
					case 6:
					case 7:
					case 8:
						canvas.width = height;
						canvas.height = width;
						break;
					default:
						canvas.width = width;
						canvas.height = height;
				}
				switch(orientation) {
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
				}
			};

			var maxheight = 500;
			var maxwidth = 500;
			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');

			var img = new Image();
			img.onload = function() {
				$this = $(this);
				var imgwidth = this.width ? this.width : $this.width();
				var imgheight = this.height ? this.height : $this.height();

				var canvaswidth = maxwidth;
				var canvasheight = maxheight;
				var newwidth = imgwidth;
				var newheight = imgheight;
				if(imgwidth/imgheight <= canvaswidth/canvasheight && imgheight >= canvasheight) {
					newheight = canvasheight;
					newwidth = Math.ceil(canvasheight/imgheight*imgwidth);
				} else if(imgwidth/imgheight > canvaswidth/canvasheight && imgwidth >= canvaswidth) {
					newwidth = canvaswidth;
					newheight = Math.ceil(canvaswidth/imgwidth*imgheight);
				}

				ctx.save();

				var imgfilebinary = this.src.replace(/data:.+;base64,/, '');
				if(typeof atob == 'function') {
					imgfilebinary = atob(imgfilebinary);
				} else {
					imgfilebinary = jQuery.base64decode(imgfilebinary);
				}
				var orientation = getorientation(imgfilebinary);
				orientation = orientation.Orientation;

				if(detectsubsampling(this, imgwidth, imgheight)) {
					imgheight = imgheight / 2;
					imgwidth = imgwidth / 2;
				}
				var vertsquashratio = detectverticalsquash(this, imgheight);
				transformcoordinate(canvas, ctx, newwidth, newheight, orientation);
				ctx.drawImage(this, 0, 0, imgwidth, imgheight, 0, 0, newwidth, newheight/vertsquashratio);
				ctx.restore();

				var newdataurl = canvas.toDataURL(s.files[0].type).replace(/data:.+;base64,/, '');

				if(typeof atob == 'function') {
					picupload(atob(newdataurl));
				} else {
					picupload(jQuery.base64decode(newdataurl));
				}
			};

			reader.index = 0;
			reader.onloadend = function(e) {
				if(canvaszoom) {
					img.src = e.target.result;
				} else {
					picupload(e.target.result);
				}
				return;
			};
			if(canvaszoom) {
				reader.readAsDataURL(s.files[0]);
			} else {
				reader.readAsBinaryString(s.files[0]);
			}
		} catch(err) {
			return s.error();
		}
		return;
    },
	getbuilder: function(s, filename, filedata, boundary) {
		var dashdash = '--',
			crlf = '\r\n',
			builder = '';

		for(var i in s.uploadformdata) {
			builder += dashdash;
			builder += boundary;
			builder += crlf;
			builder += 'Content-Disposition: form-data; name="' + i + '"';
			builder += crlf;
			builder += crlf;
			builder += s.uploadformdata[i];
			builder += crlf;
		}

		builder += dashdash;
		builder += boundary;
		builder += crlf;
		builder += 'Content-Disposition: form-data; name="' + s.uploadinputname + '"';
		builder += '; filename="' + filename + '"';
		builder += crlf;

		builder += 'Content-Type: application/octet-stream';
		builder += crlf;
		builder += crlf;

		builder += filedata;
		builder += crlf;

		builder += dashdash;
		builder += boundary;
		builder += dashdash;
		builder += crlf;
		return builder;
	}
});

jQuery.extend({
	base64encode: function(input) {
		var output = '';
		var chr1, chr2, chr3 = '';
		var enc1, enc2, enc3, enc4 = '';
		var i = 0;
		do {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)){
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)){
				enc4 = 64;
			}
			output = output+this._keys.charAt(enc1)+this._keys.charAt(enc2)+this._keys.charAt(enc3)+this._keys.charAt(enc4);
			chr1 = chr2 = chr3 = '';
			enc1 = enc2 = enc3 = enc4 = '';
		} while (i < input.length);
		return output;
	},
	base64decode: function(input) {
		var output = '';
		var chr1, chr2, chr3 = '';
		var enc1, enc2, enc3, enc4 = '';
		var i = 0;
		if (input.length%4!=0){
			return '';
		}
		var base64test = /[^A-Za-z0-9\+\/\=]/g;
		if (base64test.exec(input)){
			return '';
		}
		do {
			enc1 = this._keys.indexOf(input.charAt(i++));
			enc2 = this._keys.indexOf(input.charAt(i++));
			enc3 = this._keys.indexOf(input.charAt(i++));
			enc4 = this._keys.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64){
				output+=String.fromCharCode(chr2);
			}
			if (enc4 != 64){
				output+=String.fromCharCode(chr3);
			}
			chr1 = chr2 = chr3 = '';
			enc1 = enc2 = enc3 = enc4 = '';
		} while (i < input.length);
		return output;
	},
	_keys: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
});