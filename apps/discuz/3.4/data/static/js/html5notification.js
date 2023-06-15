/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: html5notification.js 31484 2012-09-03 03:49:21Z zhangjie $
*/

function Html5notification() {
	var h5n = new Object();

	h5n.issupport = function() {
		return 'Notification' in window;
	};

	h5n.shownotification = function(replaceid, url, imgurl, subject, message) {
		if (Notification.permission === 'granted') {
			sendit();
		} else if (Notification.permission !== 'denied') {
			Notification.requestPermission().then(function (perm) {
				if (perm === 'granted') {
					sendit();
				}
			});
		}
		function sendit() {
			var n = new Notification(subject, {
				tag: replaceid,
				icon: imgurl,
				body: message
			});
			n.onclick = function (e) {
				e.preventDefault();
				window.open(url, '_blank');
			};
		}
	};

	return h5n;
}