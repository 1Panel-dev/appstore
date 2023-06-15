var specialThread = {
	haveEvent: true,
	poll: null,
	currentChoices: 0,
	formhash: '',
	uid: 0,
	tid: 0,
	init: function (json, data) {
		if (!json.Variables.special_poll) {
			return;
		}
		specialThread.formhash = json.Variables.formhash;
		specialThread.uid = json.Variables.member_uid;
		specialThread.tid = json.Variables.thread.tid;
		specialThread.poll = json.Variables.special_poll;
		specialThread.poll.expirationsFormat = specialThread.poll.remaintime ? specialThread.formatDate(specialThread.poll.expirations) : '';
		specialThread.poll.isEnd = specialThread.poll.remaintime ? false : (specialThread.poll.expirations && specialThread.poll.expirations < Date.parse(new Date()) / 1000 ? true : false);
		specialThread.poll.optionBody = specialThread.poll.polloptions[1].imginfo && specialThread.poll.polloptions[1].imginfo.aid ? 'option_image' : 'option_text';
		if (specialThread.poll.polloptions[1].imginfo && specialThread.poll.polloptions[1].imginfo.aid) {
			for (option in specialThread.poll.polloptions) {
				if (specialThread.poll.polloptions[option].imginfo.small) {
					specialThread.poll.polloptions[option].imginfo.small = TOOLS.attachUrl(specialThread.poll.polloptions[option].imginfo.small);
					specialThread.poll.polloptions[option].imginfo.big = TOOLS.attachUrl(specialThread.poll.polloptions[option].imginfo.big);
				}
			}
		}
		if (!specialThread.poll.allowvote && specialThread.uid == "0") {
			specialThread.poll.allowvote = 1;
		}

		specialThread.poll.visiblepoll = parseInt(specialThread.poll.visiblepoll);

		data.special_poll = specialThread.poll;
		data.jsversion = JSGLOBAL.jsversion;
		return data;
	},
	bindEvent: function () {
		var rule = specialThread.poll.optionBody == 'option_text' ? '.voteList ' : '.picVoteList ';
		if (specialThread.poll.maxchoices == 1) {
			$(rule + '.iconRadio').css('display', 'none');
		} else {
			$(rule + '.iconCheckbox').css('display', 'none');
		}
		if (!specialThread.poll.allowvote) {
			$(rule + 'input').css('display', 'none');
		} else {
			$(rule + '.pollclick').click(function (event) {
				if (specialThread.uid == "0") {
					FUNCS.jumpToLoginPage('a=viewthread&tid=' + specialThread.tid);
					return;
				}
				var optionid = $(this).attr('optionid');
				if (specialThread.poll.maxchoices == 1) {
					$('#option_' + optionid).prop('checked', true);
					$(rule + '.iconRadio').css('display', 'none');
					$(rule + '#sel_' + optionid).css('display', 'inline-block');
					$(rule + 'input').css('display', '');
					$('#option_' + optionid).css('display', 'none');
					$(rule + 'li').removeClass('current');
					$(rule + '#line_' + optionid).addClass('current');
				} else {
					$('#option_' + optionid).prop('checked', !$('#option_' + optionid).prop('checked'));
					if ($('#option_' + optionid).prop('checked')) {
						if (specialThread.currentChoices < specialThread.poll.maxchoices) {
							specialThread.currentChoices++;
							$(rule + '#sel_' + optionid).css('display', 'inline-block');
							$(rule + '#line_' + optionid).addClass('current');
							$('#option_' + optionid).css('display', 'none');
							$('#option_' + optionid).attr("checked", true);
						} else {
							$('#option_' + optionid).attr("checked", false);
						}
					} else {
						specialThread.currentChoices--;
						$(rule + '#sel_' + optionid).css('display', 'none');
						$(rule + '#line_' + optionid).removeClass('current');
						$('#option_' + optionid).css('display', '');
					}
					if (specialThread.currentChoices == specialThread.poll.maxchoices) {
						$(rule).addClass('voted');
					} else {
						$(rule).removeClass('voted');
					}
				}
			});
			$('.btnVote').click(function (event) {
				if (specialThread.uid == "0") {
					FUNCS.jumpToLoginPage('a=viewthread&tid=' + specialThread.tid);
					return;
				}
				$('#voteformhash').val(specialThread.formhash);
				var postUrl = API_URL + "module=pollvote&version=4&pollsubmit=yes";
				TOOLS.dpost(postUrl, $('#voteform').serialize(),
					function (re) {
						TOOLS.showTips("投票成功", true);
						setTimeout(function () {
							location.reload();
						}, 300);
					},
					function (error) {
						TOOLS.hideLoading();
						TOOLS.showTips(error.messagestr, true);
						$('.btnVote').disabled = false;
					}
				);
				$('.btnVote').disabled = true;
			});
		}
	},
	formatDate: function (timestamp) {
		var now = new Date(parseInt(timestamp) * 1000);
		var year = now.getFullYear();
		var month = '0' + (now.getMonth() + 1);
		var date = '0' + (now.getDate());
		var hour = '0' + (now.getHours());
		var minute = '0' + (now.getMinutes());
		return year + "-" + month.substr(-2) + "-" + date.substr(-2) + " " + hour.substr(-2) + ":" + minute.substr(-2);
	}
};