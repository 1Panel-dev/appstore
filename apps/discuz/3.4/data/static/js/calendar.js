/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: calendar.js 33082 2013-04-18 11:13:53Z zhengqingpeng $
*/

var controlid = null;
var currdate = null;
var startdate = null;
var enddate  = null;
var halfhour = false;
var yy = null;
var mm = null;
var hh = null;
var ii = null;
var currday = null;
var addtime = false;
var today = new Date();
var lastcheckedyear = false;
var lastcheckedmonth = false;
var calendarrecall = null;

function loadcalendar() {
	s = '';
	s += '<div id="calendar" style="display:none; position:absolute; z-index:100000;" onclick="doane(event)">';
	s += '<div style="width: 210px;"><table cellspacing="0" cellpadding="0" width="100%" style="text-align: center;">';
	s += '<tr align="center" id="calendar_week"><td onclick="refreshcalendar(yy, mm-1)" title="上一月" style="cursor: pointer;"><a href="javascript:;">&laquo;</a></td><td colspan="5" style="text-align: center"><a href="javascript:;" onclick="showdiv(\'year\');doane(event)" class="dropmenu" title="点击选择年份" id="year"></a>&nbsp; - &nbsp;<a id="month" class="dropmenu" title="点击选择月份" href="javascript:;" onclick="showdiv(\'month\');doane(event)"></a></td><td onclick="refreshcalendar(yy, mm+1)" title="下一月" style="cursor: pointer;"><a href="javascript:;">&raquo;</a></td></tr>';
	s += '<tr id="calendar_header"><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>';
	for(var i = 0; i < 6; i++) {
		s += '<tr>';
		for(var j = 1; j <= 7; j++)
			s += "<td id=d" + (i * 7 + j) + " height=\"19\">0</td>";
		s += "</tr>";
	}
	s += '<tr id="hourminute" class="pns"><td colspan="4" align="left"><input type="text" size="1" value="" id="hour" class="px vm" style="width: 16px;margin-right: 5px;" onKeyUp=\'this.value=this.value > 23 ? 23 : zerofill(this.value);controlid.value=controlid.value.replace(/\\d+(\:\\d+)/ig, this.value+"$1")\'>点';
	s += '<span id="fullhourselector"><input type="text" size="1" value="" id="minute" class="px vm" style="width: 16px;margin: 0px 5px;" onKeyUp=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:)\\d+/ig, "$1"+this.value)\'>分</span>';
	s += '<span id="halfhourselector"><select id="minutehalfhourly" onchange=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:)\\d+/ig, "$1"+this.value)\'><option value="00">00</option><option value="30">30</option></select>分</span>';
	s += '</td><td align="right" colspan="3"><button class="pn" onclick="confirmcalendar();"><em>确定</em></button></td></tr>';
	s += '</table></div></div>';
	s += '<div id="calendar_year" onclick="doane(event)" style="display: none;z-index:100001;"><div class="col">';
	for(var k = 2090; k >= 2001; k--) {
		s += k != 2090 && k % 10 == 0 ? '</div><div class="col">' : '';
		s += '<a href="javascript:;" onclick="refreshcalendar(' + k + ', mm);$(\'calendar_year\').style.display=\'none\'"><span' + (today.getFullYear() == k ? ' class="calendar_today"' : '') + ' id="calendar_year_' + k + '">' + k + '</span></a><br />';
	}
	s += '</div></div>';
	s += '<div id="calendar_month" onclick="doane(event)" style="display: none;z-index:100001;">';
	for(var k = 1; k <= 12; k++) {
		s += '<a href="javascript:;" onclick="refreshcalendar(yy, ' + (k - 1) + ');$(\'calendar_month\').style.display=\'none\'"><span' + (today.getMonth()+1 == k ? ' class="calendar_today"' : '') + ' id="calendar_month_' + k + '">' + k + ( k < 10 ? '&nbsp;' : '') + ' 月</span></a><br />';
	}
	s += '</div>';
	if(BROWSER.ie && BROWSER.ie < 7) {
		s += '<iframe id="calendariframe" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
		s += '<iframe id="calendariframe_year" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
		s += '<iframe id="calendariframe_month" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
	}

	var div = document.createElement('div');
	div.innerHTML = s;
	$('append_parent').appendChild(div);
	document.onclick = function(event) {
		closecalendar(event);
	};
	$('calendar').onclick = function(event) {
		doane(event);
		$('calendar_year').style.display = 'none';
		$('calendar_month').style.display = 'none';
		if(BROWSER.ie && BROWSER.ie < 7) {
			$('calendariframe_year').style.display = 'none';
			$('calendariframe_month').style.display = 'none';
		}
	};
}
function closecalendar(event) {
	$('calendar').style.display = 'none';
	$('calendar_year').style.display = 'none';
	$('calendar_month').style.display = 'none';
	if(BROWSER.ie && BROWSER.ie < 7) {
		$('calendariframe').style.display = 'none';
		$('calendariframe_year').style.display = 'none';
		$('calendariframe_month').style.display = 'none';
	}
}

function parsedate(s) {
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec(s);
	var m1 = (RegExp.$1 && RegExp.$1 > 1899 && RegExp.$1 < 2101) ? parseFloat(RegExp.$1) : today.getFullYear();
	var m2 = (RegExp.$2 && (RegExp.$2 > 0 && RegExp.$2 < 13)) ? parseFloat(RegExp.$2) : today.getMonth() + 1;
	var m3 = (RegExp.$3 && (RegExp.$3 > 0 && RegExp.$3 < 32)) ? parseFloat(RegExp.$3) : today.getDate();
	var m4 = (RegExp.$4 && (RegExp.$4 > -1 && RegExp.$4 < 24)) ? parseFloat(RegExp.$4) : 0;
	var m5 = (RegExp.$5 && (RegExp.$5 > -1 && RegExp.$5 < 60)) ? parseFloat(RegExp.$5) : 0;
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec("0000-00-00 00\:00");
	return new Date(m1, m2 - 1, m3, m4, m5);
}

function settime(d) {
	if(!addtime) {
		$('calendar').style.display = 'none';
		$('calendar_month').style.display = 'none';
		if(BROWSER.ie && BROWSER.ie < 7) {
			$('calendariframe').style.display = 'none';
		}
	}
	controlid.value = yy + "-" + zerofill(mm + 1) + "-" + zerofill(d) + (addtime ? ' ' + zerofill($('hour').value) + ':' + zerofill($((halfhour) ? 'minutehalfhourly' : 'minute').value) : '');
	if(typeof calendarrecall == 'function') {
		calendarrecall();
	} else {
		eval(calendarrecall);
	}
}

function confirmcalendar() {
	if(addtime && controlid.value === '') {
		controlid.value = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate() + ' ' + zerofill($('hour').value) + ':' + zerofill($((halfhour) ? 'minutehalfhourly' : 'minute').value);
	}
	closecalendar();
}

function initclosecalendar() {
	var e = getEvent();
	var aim = e.target || e.srcElement;
	while (aim.parentNode != document.body) {
		if (aim.parentNode.id == 'append_parent') {
			aim.onclick = function () {closecalendar(e);};
		}
		aim = aim.parentNode;
	}
}
function showcalendar(event, controlid1, addtime1, startdate1, enddate1, halfhour1, recall) {
	controlid = controlid1;
	addtime = addtime1;
	startdate = startdate1 ? parsedate(startdate1) : false;
	enddate = enddate1 ? parsedate(enddate1) : false;
	currday = controlid.value ? parsedate(controlid.value) : today;
	hh = currday.getHours();
	ii = currday.getMinutes();
	halfhour = halfhour1 ? true : false;
	calendarrecall = recall ? recall : null;
	var p = fetchOffset(controlid);
	$('calendar').style.display = 'block';
	$('calendar').style.left = p['left']+'px';
	$('calendar').style.top	= (p['top'] + 20)+'px';
	doane(event);
	refreshcalendar(currday.getFullYear(), currday.getMonth());
	if(lastcheckedyear != false) {
		$('calendar_year_' + lastcheckedyear).className = 'calendar_default';
		$('calendar_year_' + today.getFullYear()).className = 'calendar_today';
	}
	if(lastcheckedmonth != false) {
		$('calendar_month_' + lastcheckedmonth).className = 'calendar_default';
		$('calendar_month_' + (today.getMonth() + 1)).className = 'calendar_today';
	}
	$('calendar_year_' + currday.getFullYear()).className = 'calendar_checked';
	$('calendar_month_' + (currday.getMonth() + 1)).className = 'calendar_checked';
	$('hourminute').style.display = addtime ? '' : 'none';
	lastcheckedyear = currday.getFullYear();
	lastcheckedmonth = currday.getMonth() + 1;
	if(halfhour) {
		$('halfhourselector').style.display = '';
		$('fullhourselector').style.display = 'none';
	} else {
		$('halfhourselector').style.display = 'none';
		$('fullhourselector').style.display = '';
	}
	if(BROWSER.ie && BROWSER.ie < 7) {
		$('calendariframe').style.top = $('calendar').style.top;
		$('calendariframe').style.left = $('calendar').style.left;
		$('calendariframe').style.width = $('calendar').offsetWidth;
		$('calendariframe').style.height = $('calendar').offsetHeight;
		$('calendariframe').style.display = 'block';
	}
	initclosecalendar();
}

function refreshcalendar(y, m) {
	var x = new Date(y, m, 1);
	var mv = x.getDay();
	var d = x.getDate();
	var dd = null;
	yy = x.getFullYear();
	mm = x.getMonth();
	$("year").innerHTML = yy;
	$("month").innerHTML = mm + 1 > 9  ? (mm + 1) : '0' + (mm + 1);

	for(var i = 1; i <= mv; i++) {
		dd = $("d" + i);
		dd.innerHTML = "&nbsp;";
		dd.className = "";
	}

	while(x.getMonth() == mm) {
		dd = $("d" + (d + mv));
		dd.style.cursor = 'pointer';
		dd.onclick = function () {
			settime(this.childNodes[0].innerHTML);
			doane();
		};
		dd.innerHTML = '<a href="javascript:;">' + d + '</a>';
		if(x.getTime() < today.getTime() || (enddate && x.getTime() > enddate.getTime()) || (startdate && x.getTime() < startdate.getTime())) {
			dd.className = 'calendar_expire';
		} else {
			dd.className = 'calendar_default';
		}
		if(x.getFullYear() == today.getFullYear() && x.getMonth() == today.getMonth() && x.getDate() == today.getDate()) {
			dd.className = 'calendar_today';
			dd.firstChild.title = '今天';
		}
		if(x.getFullYear() == currday.getFullYear() && x.getMonth() == currday.getMonth() && x.getDate() == currday.getDate()) {
			dd.className = 'calendar_checked';
		}
		x.setDate(++d);
	}

	while(d + mv <= 42) {
		dd = $("d" + (d + mv));
		dd.innerHTML = "&nbsp;";
		d++;
	}

	if(addtime) {
		$('hour').value = zerofill(hh);
		$('minute').value = zerofill(ii);
	}
}

function showdiv(id) {
	var p = fetchOffset($(id));
	$('calendar_' + id).style.left = p['left']+'px';
	$('calendar_' + id).style.top = (p['top'] + 16)+'px';
	$('calendar_' + id).style.display = 'block';
	if(BROWSER.ie && BROWSER.ie < 7) {
		$('calendariframe_' + id).style.top = $('calendar_' + id).style.top;
		$('calendariframe_' + id).style.left = $('calendar_' + id).style.left;
		$('calendariframe_' + id).style.width = $('calendar_' + id).offsetWidth;
		$('calendariframe_' + id ).style.height = $('calendar_' + id).offsetHeight;
		$('calendariframe_' + id).style.display = 'block';
	}
}

function zerofill(s) {
	var s = parseFloat(s.toString().replace(/(^[\s0]+)|(\s+$)/g, ''));
	s = isNaN(s) ? 0 : s;
	return (s < 10 ? '0' : '') + s.toString();
}

if(!BROWSER.other) {
	loadcss('forum_calendar');
	loadcalendar();
}