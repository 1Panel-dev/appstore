function loadingMore(loadingDiv, loadingFun) {
	$(window).scroll(function () {
		if (($(window).height() + $(window).scrollTop()) >= $("#container").height()) {
			if (isLoading)
				return;
			isLoading = true;
			$(loadingDiv).show();
			loadingFun();
		}
	});
}