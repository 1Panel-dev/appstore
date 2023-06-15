
var OPEN_JS_T = {
	FastClick: "",
	Template: "",
	QQapi: "",
	Jpegmeta: "",
	JpegEncoder: "",
	ImageCompress: ""
};

var JC = {
	HEAD: "head",
	BODY: "body",
	VERSION: "",
	KEYPREFIX: "",
	filePaths: [],
	fileVers: [],
	fileLoads: [],
	fileCodes: {},
	file: function (filePath, version, tag) {
		version = version || JC.VERSION;
		tag = tag || JC.HEAD;

		try {
			localStorage;
		} catch (err) {
			if (err.name == 'SecurityError') {
				jQuery(function () {
					jQuery('.warp').html('<div class="errorInfo"><i class="eInco db spr"></i><p>请开启 Cookie 访问设置</p></div>');
				});
			}
			return;
		}

		JC.filePaths.push([filePath, tag]);
		JC.fileVers[filePath] = version;
		if (version != localStorage.getItem(JC.KEYPREFIX + filePath + ".v")) {
			JC.fileLoads.push(filePath);
		} else {
			source = localStorage.getItem(JC.KEYPREFIX + filePath);
			if (!source) {
				JC.fileLoads.push(filePath);
			} else {
				JC.fileCodes[filePath] = source;
			}
		}
	},
	run: function (filePath, rPath) {
		var filePaths = filePath || JC.fileLoads.join(',');
		var rPath = rPath || 0;
		if (!filePaths) {
			JC.append();
			return;
		}

		jQuery.ajax({
			type: 'get',
			url: !rPath ? '?c=static&v=' + JC.VERSION + '&f=' + filePaths : [filePath],
			contentType: 'text/plain',
			async: false,
			cache: false,
			xhrFields: {withCredentials: true},
			success: function (data) {
				if (data.code == 0) {
					for (filePath in data.file) {
						source = data.file[filePath];
						JC.fileCodes[filePath] = source;
						var i = 2;
						while (i--) {
							try {
								localStorage.setItem(JC.KEYPREFIX + filePath, source);
								localStorage.setItem(JC.KEYPREFIX + filePath + ".v", JC.fileVers[filePath]);
								break;
							} catch (err) {
								if (err.name == 'QuotaExceededError') {
									localStorage.clear();
								}
							}
						}
					}
				}
				JC.append();
			}
		});
	},
	append: function () {
		for (var i = 0; i < JC.filePaths.length; i++) {
			var filePath = JC.filePaths[i][0];
			var tag = JC.filePaths[i][1];
			var script = document.createElement("script");
			script.language = "javascript";
			script.type = "text/javascript";
			script.defer = true;
			script.text = JC.fileCodes[filePath];

			var heads = document.getElementsByTagName(tag);
			if (heads.length) {
				heads[heads.length - 1].appendChild(script);
			} else {
				document.documentElement.appendChild(script);
			}
		}
		JC.filePaths = [];
		JC.fileVers = [];
		JC.fileLoads = [];
		JC.fileCodes = {};
	},
	load: function (filePath, version, tag, params) {
		JC.run(JS_DIR + filePath, 1);
	}
};

var TC = {
	BODY: "body",
	VERSION: "",
	KEYPREFIX: "",
	load: function (filePath, version, params) {

		filePath = TMPL_DIR + filePath;

		if (arguments.length == 2 && typeof (arguments[1]) == 'object') {
			params = arguments[1];
			version = null;
			tag = null;
		}
		;

		version = version || JC.VERSION;
		var source = TC.getCacheTSData(filePath, version);
		if (!source) {
			return;
		}

		if (params) {
			source = source.replace('[%params%]', JSON.stringify(params));
		}

		jQuery(source).appendTo('body');
	},
	getCacheTSData: function (filePath, version) {
		var source = null;
		if (version) {
			source = localStorage.getItem(TC.KEYPREFIX + filePath);
			if (source) {
				if (version == localStorage.getItem(TC.KEYPREFIX + filePath + ".v")) {
					return source;
				}
				localStorage.removeItem(TC.KEYPREFIX + filePath);
				localStorage.removeItem(TC.KEYPREFIX + filePath + ".v");
			}
		}
		jQuery.ajax({
			async: false,
			cache: false,
			dataType: "text",
			url: filePath,
			success: function (data) {
				source = data;
			}
		});
		if (source && version) {
			var i = 2;
			while (i--) {
				try {
					localStorage.setItem(TC.KEYPREFIX + filePath, source);
					localStorage.setItem(TC.KEYPREFIX + filePath + ".v", version);
					break;
				} catch (err) {
					if (err.name == 'QuotaExceededError') {
						localStorage.clear();
					}
				}
			}
		}
		return source;
	}
};