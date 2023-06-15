var parseMedia = function (obj) {
	url = obj.attr('href');
	if (url.indexOf('player.youku.com') != -1) {
		match = url.match(/sid\/(\w+)\/v\.swf/);
		if (match != null && match.length > 1) {
			obj.replaceWith('<p><iframe height=200 width=300 src="https://player.youku.com/embed/' + match[1] + '" frameborder=0 allowfullscreen></iframe></p>');
		}
	} else if (url.indexOf('tudou.com') != -1) {
		match = url.match(/tudou\.com\/v\/(\w+)\//);
		if (match != null && match.length > 1) {
			obj.replaceWith('<iframe src="https://www.tudou.com/programs/view/html5embed.action?code=' + match[1] + '" width="300px" height="200px" frameborder="0" scrolling="no"></iframe>');
		}
	} else if (url.indexOf('video.qq.com') != -1 || url.indexOf('v.qq.com') != -1 || url.indexOf('static.video.qq.com')) {
		match = url.match(/vid=(\w+)/);
		if (match != null && match.length > 1) {
			obj.replaceWith('<iframe width="300" height="200" src="https://v.qq.com/iframe/player.html?vid=' + match[1] + '&amp;width=300&amp;height=200&amp;auto=0" frameborder="0" style="z-index: 1;" allowfullscreen="" qbiframeattached="true"></iframe>');
		}
	} else if (url.indexOf('v.youku.com') != -1) {
		match = url.match(/v_show\/id_(\w+)\.html/);
		if (match != null && match.length > 1) {
			obj.replaceWith('<p><iframe height=200 width=300 src="https://player.youku.com/embed/' + match[1] + '" frameborder=0 allowfullscreen></iframe></p>');
		}
	}
};