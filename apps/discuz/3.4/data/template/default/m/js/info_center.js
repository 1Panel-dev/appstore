var nextStart = 0;
var haveMore = false;

var renderPage = function (json) {
	var messageList = json.Variables.messageList;
	var postListHtml = '';
	for (var i in messageList) {
		postListHtml += template.render('tmpl_topic_item', messageList[i]);
	}
	$('div.topic').append(postListHtml);
	$('.topic_item').on('click', function (event) {
		TOOLS.openNewPage($(this).attr('url'));
		event.stopPropagation();
	});
	TOOLS.hideLoading();
};
var dataLoaded = function (np, checkWarp) {
	TOOLS.dget(API_URL + "module=wsqmsglist&version=4" + "&start=" + np, null,
		function (json) {
			renderPage(json);
			var no_data = !json.Variables.messageList || json.Variables.messageList.length == 0;
			if (checkWarp && no_data) {
				TOOLS.showError('.warp', "没有消息", null);
				return;
			}
			nextStart = json.Variables.nextStart;
			haveMore = nextStart >= 0;
			if (haveMore) {
				$('#historyInfo').show();
				$('#historyInfoAll').hide();
			} else {
				$('#historyInfoAll').show();
				$('#historyInfo').hide();
			}
		},
		function (error) {
			TOOLS.showError('.warp', "网络不稳定,单击页面重新加载~", function () {
				location.reload();
			});
			TOOLS.hideLoading();
			TOOLS.showTips(error.messagestr, true);
		}
	);
};
var bindEvent = function () {
	$('#historyInfo').on('click', function (event) {
		getNextPage();
		event.stopPropagation();
	});

};
var getNextPage = function () {
	dataLoaded(nextStart, false);
};
$(function () {
	TOOLS.showLoading();
	dataLoaded(nextStart, true);
	bindEvent();
});