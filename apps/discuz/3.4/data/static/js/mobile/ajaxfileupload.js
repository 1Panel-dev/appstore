jQuery.extend({
	createuploadiframe: function(id, url) {
		var iframeid = 'uploadiframe' + id;
		var iframe = '<iframe id="' + iframeid + '" name="' + iframeid + '"';
		if(window.ActiveXObject) {
			if(typeof url == 'boolean') {
				iframe += ' src="' + 'javascript:false' + '"';
			} else if(typeof url == 'string') {
				iframe += ' src="' + url + '"';
			}
		}
		iframe += ' />';
		jQuery(iframe).css({'position':'absolute', 'top':'-1200px', 'left':'-1200px'}).appendTo(document.body);
		return jQuery('#' + iframeid).get(0);
    },
	createuploadform: function(id, fileobjid, data) {
		var formid = 'uploadform' + id;
		var fileid = 'uploadfile' + id;
		var form = jQuery('<form method="post" name="' + formid + '" id="' + formid + '" enctype="multipart/form-data"></form>');
		if(data) {
			for(var i in data) {
				jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
			}
		}
		var oldobj = jQuery('#' + fileobjid);
		var newobj = jQuery(oldobj).clone();
		jQuery(oldobj).attr('id', fileid).before(newobj).appendTo(form);
		jQuery(form).css({'position':'absolute', 'top':'-1200px', 'left':'-1200px'}).appendTo(document.body);
		return form;
	},
	ajaxfileupload: function(s) {
		s = jQuery.extend({}, jQuery.ajaxSettings, s);
		var id = new Date().getTime();
		var form = jQuery.createuploadform(id, s.fileElementId, (typeof(s.data)=='undefined'?false:s.data));
		var io = jQuery.createuploadiframe(id, s.secureuri);
		var iframeid = 'uploadiframe' + id;
		var formid = 'uploadform' + id;

		if(s.global && ! jQuery.active++) {
			jQuery.event.trigger("ajaxStart");
		}
		var requestDone = false;
        var xml = {};
        if(s.global) {
			jQuery.event.trigger("ajaxSend", [xml, s]);
		}
		var uploadcallback = function(istimeout) {
			var io = document.getElementById(iframeid);
			try {
				if(io.contentWindow) {
					xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
					xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;
				} else if(io.contentDocument) {
					xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
					xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}
			} catch(e) {
				jQuery.handleerror(s, xml, null, e);
			}
			if(xml||istimeout == 'timeout') {
				requestdone = true;
				var status;
				try {
					status = istimeout != 'timeout' ? 'success' : 'error';
					if(status != 'error') {
						var data = jQuery.uploadhttpdata(xml, s.dataType);
						if(s.success) {
							s.success( data, status );
						}
						if(s.global) {
							jQuery.event.trigger("ajaxSuccess", [xml, s]);
						}
					} else {
                        jQuery.handleerror(s, xml, status);
					}
				} catch(e) {
					status = 'error';
					jQuery.handleerror(s, xml, status, e);
				}
				if(s.global) {
					jQuery.event.trigger("ajaxComplete", [xml, s]);
				}

				if(s.global && ! --jQuery.active) {
					jQuery.event.trigger("ajaxStop");
				}

				if (s.complete) {
					s.complete(xml, status);
				}

				jQuery(io).off();

				setTimeout(function() {
					try {
						jQuery(io).remove();
						jQuery(form).remove();
					} catch(e) {
						jQuery.handleerror(s, xml, null, e);
					}
				}, 100);

				xml = null;
			}
		}
		if(s.timeout > 0) {
			setTimeout(function() {
				if(!requestdone) {
					uploadcallback('timeout');
				}
			}, s.timeout);
		}
		try {
			var form = jQuery('#' + formid);
			jQuery(form).attr('action', s.url).attr('method', 'post').attr('target', iframeid);
			if(form.encoding) {
				jQuery(form).attr('encoding', 'multipart/form-data');
			} else {
				jQuery(form).attr('enctype', 'multipart/form-data');
			}
			jQuery(form).submit();
		} catch(e) {
			jQuery.handleerror(s, xml, null, e);
		}

		jQuery('#' + iframeid).load(uploadcallback);
		return {abort: function () {}};
    },
	uploadhttpdata: function(r, type) {
		var data = !type;
		data = type == 'xml' || data ? r.responseXML : r.responseText;
		if(type == 'script') {
			jQuery.globalEval(data);
		}
		if(type == "json") {
			eval("data = " + data);
		}
		if(type == "html") {
			jQuery("<div>").html(data);
		}
		return data;
	},
	handleerror: function(s, xhr, status, e) {
		if(s.error) {
			s.error.call(s.context || s, xhr, status, e);
		}
		if(s.global) {
			(s.context ? jQuery(s.context) : jQuery.event).trigger("ajaxError", [xhr, s, e]);
		}
	}
});