/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: makehtml.js 33047 2013-04-12 08:46:56Z zhangguosheng $
*/

function make_html(url, obj) {
	var x = Ajax();
	if(url && url.indexOf('?') < 0) {
		url = url + '?';
	}
	x.getJSON(url+'&_makehtml&r='+(+ new Date()), function(ret){
		var title = obj ? obj.getAttribute('mktitle') || '' : '';
		if(ret && (ret=ret['data']) && ret['status'] == 'html_ok') {
			if(obj) {
				obj.style.color = 'blue';
				obj.innerHTML = '<a href="'+ret['path']+'" target="_blank">'+title+'生成成功</a>';
			}
			if(ret['nexturl']) {
				if(obj) {
					obj.style.color = 'green';
					obj.innerHTML = '生成'+title+(Math.round((ret['current']/ret['count'])*100))+'%';
				}
				make_html(ret['nexturl'], obj);
			}
		} else {
			if(obj) {
				obj.style.color = 'red';
				obj.innerHTML = title+'生成失败';
			}
		}
	});

}

function make_html_batch(url, ids, callback, dom, single) {
	this.url = url;
	this.ids = ids;
	this.count = this.ids.length;
	this.callback = callback;
	this.dom = dom;
	this.single = single && 1;
	this.makedcount = 0;
	this.jumptime = 2000;

	if(this.single) {
		this.make(this.ids, this.dom);
	} else if(this.ids) {
		id = this.ids.pop();
		var child = document.createElement('div');
		child.style.color = 'green';
		var cent = ((1/this.count)*100).toFixed(2);
		progress_bar(cent);
		child.innerHTML = '开始生成'+this.dom.getAttribute('mktitle');
		this.dom.innerHTML = '';
		this.dom.appendChild(child);
		this.make(id, child);
		this.child = child;
		var child2 = document.createElement('div');
		child2.innerHTML = '<a href="javascript:void(0);" id="mk_goon">如果您的浏览器没有反应，请点击继续...</a>';
		this.dom.appendChild(child2);
		var obj = this;
		$('mk_goon').onclick = function (e) {make_html_batch.prototype.make_goon.call(obj, e)};
	}

}


make_html_batch.prototype = {

	make_goon : function (){
		var id = this.ids.pop();
		if(id) {
			this.make(this.ids.pop(), this.child);
		} else if(this.callback) {
			var obj = this;
			setTimeout(function(){obj.dom.style.display = 'none';(obj.callback)();}, 1000);
		}
	},
	make : function (id, child) {
		var obj = this;
		var x = Ajax();
		x.getJSON(this.url+id+'&_makehtml&r='+(+ new Date()), function(ret){
			if(ret && (data=ret['data']) && data['status'] == 'html_ok') {
				obj.makedcount++;
				if(data['nexturl']) {
					make_html(data['nexturl']);
				}
			} else if(ret && ret['message']) {
				var makehtml_error = $('makehtml_error');
				if(!makehtml_error) {
					obj.jumptime = 500000;
					makehtml_error = document.createElement('div');
					makehtml_error.style.color = 'red';
					makehtml_error.style.height = '200px';
					makehtml_error.style.overflow = 'scroll';
					makehtml_error.id = 'makehtml_error';
					makehtml_error.innerHTML = '错误信息';
					obj.dom.appendChild(makehtml_error);
				}
				makehtml_error.innerHTML += '<br>[id:' + id + ']' + ret['message'];
				makehtml_error.scrollTop = makehtml_error.scrollHeight;
			}

			if(obj.single) {
				child.style.color = 'blue';
				child.innerHTML = '<div class="mk_msg">'+'<a href="'+data['path']+'" target="_blank">'+obj.dom.getAttribute('mktitle')+'</a>生成完成'+'</div>';
				if(obj.callback) {
					setTimeout(function(){(obj.callback)();}, 2000);
				}
			} else if((id = obj.ids.pop()) || obj.ids.length == 0){
				var current = obj.count - obj.ids.length;
				var cent = ((current/obj.count)*100).toFixed(2);
				progress_bar(cent);
				var str = '本次共需要生成'+obj.count+'个'+obj.dom.getAttribute('mktitle')+'文件，成功生成'+obj.makedcount+'个，';
				if(cent != 100) {
					child.innerHTML = str+'正在生成第'+current+'个，已经完成'+cent+'%';
				} else {
					child.style.color = 'blue';
					child.innerHTML = str+obj.dom.getAttribute('mktitle')+'生成完成';
				}
				if(id) {
					obj.make(id, child);
				} else if(obj.callback) {
					setTimeout(function(){progress_bar_reset(); obj.dom.innerHTML = ''; obj.dom.style.display = 'none'; (obj.callback)();}, obj.jumptime);
				}
			}
		});
		delete x;
	}
};

function progress_bar(cent) {
	var dom = $('progress_bar');
	if(dom) {
		if(dom.style.display != 'block') {
			dom.style.display = 'block';
		}
		var allwidth = 400;
		var setwidth = allwidth * (cent / 100);
		dom.style.borderLeftWidth = setwidth + 'px';
		dom.style.width = (allwidth - setwidth) + 'px';
	}
}

function progress_bar_reset() {
	var dom = $('progress_bar');
	if(dom) {
		dom.style.display = 'none';
		dom.style.borderLeftWidth = '1px';
		dom.style.width = '400px';
	}
}