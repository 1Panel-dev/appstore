/*
 参数
 parent			放置瀑布流元素的容器，默认为 $("waterfall")
 container		放置瀑布流的父容器，默认为 $("threadlist")
 maxcolumn		最多多少列，默认为 0 不限制
 space			图片间距，默认为 10
 index			从第几张开始排列，默认为 0
 tag			瀑布流元素的 tagName，默认为 li
 columnsheight	存放列高度的数组

 返回值
 index			当前瀑布流已经排列了多少个图片
 totalwidth		当前瀑布流的总宽度
 totalheight	当前瀑布流的总高度
 columnsheight	存放瀑布流列高的数组
 */
function waterfall(v) {
	var v = typeof(v) == "undefined" ? {} : v;
	var column = 1;
	var totalwidth = typeof(v.totalwidth) == "undefined" ? 0 : v.totalwidth;
	var totalheight = typeof(v.totalheight) == "undefined" ? 0 : v.totalheight;
	var parent = typeof(v.parent) == "undefined" ? $("waterfall") : v.parent;
	var container = typeof(v.container) == "undefined" ? $("threadlist") : v.container;
	var maxcolumn = typeof(v.maxcolumn) == "undefined" ? 0 : v.maxcolumn;
	var space = typeof(v.space) == "undefined" ? 10 : v.space;
	var index = typeof(v.index) == "undefined" ? 0 : v.index;
	var tag = typeof(v.tag) == "undefined" ? "li" : v.tag;

	var columnsheight = typeof(v.columnsheight) == "undefined" ? [] : v.columnsheight;

	function waterfallMin() {
		var min = 0;
		var index = 0;
		if(columnsheight.length > 0) {
			min = Math.min.apply({}, columnsheight);
			for(var i = 0, j = columnsheight.length; i < j; i++) {
				if(columnsheight[i] == min) {
					index = i;
					break;
				}
			}
		}
		return {"value": min, "index": index};
	}
	function waterfallMax() {
		return Math.max.apply({}, columnsheight);
	}

	var mincolumn = {"value": 0, "index": 0};
	var totalelem = [];
	var singlewidth = 0;
	totalelem = parent.getElementsByTagName(tag);
	if(totalelem.length > 0) {
		column = Math.floor((container.offsetWidth - space) / (totalelem[0].offsetWidth + space));
		if(maxcolumn && column > maxcolumn) {
			column = maxcolumn;
		}
		if(!column) {
			column = 1;
		}
		if(columnsheight.length != column) {
			columnsheight = [];
			for(var i = 0; i < column; i++) {
				columnsheight[i] = 0;
			}
			index = 0;
		}
		singlewidth = totalelem[0].offsetWidth + space;
		totalwidth = singlewidth * column - space;
		for(var i = index, j = totalelem.length; i < j; i++) {
			mincolumn = waterfallMin();
			totalelem[i].style.position = "absolute";
			totalelem[i].style.left = singlewidth * mincolumn.index + "px";
			totalelem[i].style.top = mincolumn.value + "px";
			columnsheight[mincolumn.index] = columnsheight[mincolumn.index] + totalelem[i].offsetHeight + space;
			totalheight = Math.max(totalheight, waterfallMax());
			index++;
		}
		parent.style.height = totalheight + "px";
		parent.style.width = totalwidth + "px";
	}
	return {"index": index, "totalwidth": totalwidth, "totalheight": totalheight, "columnsheight" : columnsheight};
}