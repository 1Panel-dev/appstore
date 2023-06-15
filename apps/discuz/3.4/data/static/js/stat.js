function drawstatchart(url) {
	if (BROWSER.ie && BROWSER.ie < 9) {
		$('statchart').innerHTML = AC_FL_RunContent(
			'width', '100%', 'height', '300',
			'src', STATICURL + 'image/common/stat.swf?path=&settings_file=data/stat_setting.xml&data_file=' + encodeURIComponent(url),
			'quality', 'high', 'wmode', 'transparent'
		);
		return;
	}

	var x = new Ajax();
	x.recvType = 'HTML';
	$('statchart').style.width = '100%';
	$('statchart').style.height = '400px';
	x.get(url, function (s, x) {
		var myChart = echarts.init($('statchart'));
		option = {
			grid: { left: 60, right: 20 },
			xAxis: { type: 'category', data: [] },
			yAxis: { type: 'value' },
			tooltip: { trigger: 'axis', textStyle: { fontSize: 12 } },
			series: [],
			legend: { type: 'scroll', data: [], left: 60, bottom: 10 }
		};
		var rex = x.XMLHttpRequest.responseXML, reax = rex.getElementsByTagName('xaxis')[0].childNodes;
		if (!reax.length) {
			option['title'] = {
				text: 'There is no data for selected period', padding: [10, 50],
				textAlign: 'center', textVerticalAlign: 'center',
				left: '50%', top: '50%', backgroundColor: '#e8f0f7'
			};
		}
		for (var i = 0; i < reax.length; i++) {
			option.xAxis.data.push(reax[i].firstChild.nodeValue);
		}
		for (var i = 0, q = rex.getElementsByTagName('graphs')[0].childNodes; i < q.length; i++) {
			qttl = q[i].getAttribute('title');
			option.legend.data.push(qttl);
			qdata = {
				type: 'line',
				name: qttl,
				data: []
			};
			for (var j = 0; j < q[i].childNodes.length; j++) {
				qdata.data.push(parseInt(q[i].childNodes[j].firstChild.nodeValue));
			}
			option.series.push(qdata);

		}
		myChart.setOption(option);
	});
}