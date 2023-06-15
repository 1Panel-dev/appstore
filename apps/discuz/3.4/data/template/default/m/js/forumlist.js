var member_uid;

var dataLoaded = function (json) {
    member_uid = json.Variables.member_uid;
    var forumListHtml = '';
    forumListHtml += template.render('tmpl_forum_list_item', { 'json': json });
    $('.interestBox').html(forumListHtml);
    TOOLS.hideLoading();

    $(".iBtn").click(function () {
        $(this).parents(".interestList").children('.bd').slideToggle();
        $(this).toggleClass('iBtnOn');
		if ($(this).children('span').attr('class')=='incoA') {
			$(this).children('span').removeClass('incoA').addClass('upBtn');
		}
		else {
			$(this).children('span').removeClass('upBtn').addClass('incoA');
		}
        return false;
    });
};

var forumListInit2 = function () {

    TOOLS.showLoading();

    TOOLS.getCheckInfo(function(re) {
    });

    var jsonCache = TOOLS.getCacheJSon("forumlist");
    if(jsonCache) {
        try {
            dataLoaded(jsonCache);
        } catch(err) {
            TOOLS.removeCacheData("forumlist");
            jsonCache = null;
        }
    }
    TOOLS.dget(API_URL + "module=forumindex&version=4", null,
        function (json) {
            TOOLS.setCacheJSon("forumlist" ,json ,30 * 24 * 3600 * 1000);
            dataLoaded(json);
        },
        function (error) {
            TOOLS.hideLoading();
            TOOLS.showTips(error.messagestr, true);
        }
    );

};
forumListInit2();

function openForum(fid) {
    TOOLS.openNewPage("?a=index&fid=" + fid);
}