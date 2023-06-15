function drop() {
	ai.hideUrl();
	var bar_list_div = ai.i("container"),
		bar_list = ai.i("bar_list"),
		minus = ai.ovb.ios() && ai.ovb.safari() && !ai.ovb.ipad() ? -20 : 40,
		up_arrow = ai.i("up_arrow"),
		up_text = ai.i("up_text"),
		up_ing = false,
		down_ing = false;
	loadb = false;
	bar_list.style.height = ai.wh() - minus + "px";


	function updateHeader() {
		if (this.xy > 10 && up_ing == false) {
			up_ing = true;
			down_ing = false;
			up_arrow.style['webkitTransitionDuration'] = '300ms';
			up_arrow.style['webkitTransform'] = 'rotate(-180deg)';
			up_text.innerHTML = "松开即可刷新";
			this.up_range = 0;
		} else if (this.xy < 10 && down_ing == false) {
			down_ing = true;
			up_ing = false;
			up_arrow.style['webkitTransitionDuration'] = '300ms';
			up_arrow.style['webkitTransform'] = 'rotate(0deg)';
			up_text.innerHTML = "下拉刷新";
			this.up_range = 60;
		}
	}
	function loadingHeader() {
		if (this.up_range == 0) {
			var that = this;
			up_text.innerHTML = 'Loading...';
			up_arrow.style['webkitTransitionDuration'] = '0ms';
			up_arrow.className += ' loading';
			setTimeout(function () {
				now = new Date();
				up_arrow.style['webkitTransform'] = 'rotate(0deg)';
				up_arrow.className = 'up_down_arrow';
				that.up_range = 60;
				that.refresh();
			}, 1000);
		}
	}
	function updateFooter() {
		if (this.xy < -(this.coreWidth_cut_width + 20) && up_ing == false) {
			up_ing = true;
			down_ing = false;
			up_arrow.style['webkitTransitionDuration'] = '300ms';
			up_arrow.style['webkitTransform'] = 'rotate(0deg)';
			up_text.innerHTML = "松开加载更多";
			loadb = true;

		} else if (this.xy > -(this.coreWidth_cut_width + 20) && down_ing == false) {
			down_ing = true;
			up_ing = false;
			up_arrow.style['webkitTransitionDuration'] = '300ms';
			up_arrow.style['webkitTransform'] = 'rotate(-180deg)';
			up_text.innerHTML = "上拉加载更多";
			loadb = false;
		}
	}
	function loadingFooter() {
		if (loadb) {
			loadb = false;
			var that = this;
			up_text.innerHTML = 'Loading...';
			up_arrow.style['webkitTransitionDuration'] = '0ms';
			up_arrow.className += ' loading';
			setTimeout(function () {
				now = new Date();
				var newli = '<li>' + now.getHours() + ' : ' + now.getMinutes() + ' : ' + now.getSeconds() + '&nbsp;&nbsp;加载的新内容</li><li>' + now.getHours() + ' : ' + now.getMinutes() + ' : ' + now.getSeconds() + '&nbsp;&nbsp;加载的新内容</li><li>' + now.getHours() + ' : ' + now.getMinutes() + ' : ' + now.getSeconds() + '&nbsp;&nbsp;加载的新内容</li>';
				bar_list_ul.innerHTML += newli;
				up_arrow.style['webkitTransform'] = 'rotate(-180deg)';
				up_arrow.className = 'up_down_arrow';
				that.up_range = 0;
				that.refresh();

			}, 1000);
		}
	}
	slip('px', bar_list_div, {
		up_range: 60,
		moveFun: updateHeader,
		endFun: loadingHeader
	});

}