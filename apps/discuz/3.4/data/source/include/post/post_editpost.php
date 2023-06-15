<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_editpost.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$orig = get_post_by_tid_pid($_G['tid'], $pid);
$isfirstpost = $orig['first'] ? 1 : 0;

if($isfirstpost && (($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate']))) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if($_G['setting']['magicstatus']) {
	$magiclog = C::t('forum_threadmod')->fetch_by_tid_magicid($_G['tid'], 10);
	$magicid = $magiclog['magicid'];
	$_G['group']['allowanonymous'] = $_G['group']['allowanonymous'] || $magicid ? 1 : $_G['group']['allowanonymous'];
}

$isorigauthor = $_G['uid'] && $_G['uid'] == $orig['authorid'];
$isanonymous = ($_G['group']['allowanonymous'] || $orig['anonymous']) && getgpc('isanonymous') ? 1 : 0;
$audit = $orig['invisible'] == -2 || $thread['displayorder'] == -2 ? $_GET['audit'] : 0;

if(empty($orig)) {
	showmessage('post_nonexistence');
} elseif((!$_G['forum']['ismoderator'] || !$_G['group']['alloweditpost'] || (in_array($orig['adminid'], array(1, 2, 3)) && $_G['adminid'] > $orig['adminid'])) && !(($_G['forum']['alloweditpost'] || $orig['invisible'] == -3)&& $isorigauthor)) {
	showmessage('post_edit_nopermission', NULL);
} elseif($isorigauthor && !$_G['forum']['ismoderator'] && $orig['invisible'] != -3) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $special + 1);
	if(!$alloweditpost_status && $_G['group']['edittimelimit'] && TIMESTAMP - $orig['dateline'] > $_G['group']['edittimelimit'] * 60) {
		showmessage('post_edit_timelimit', NULL, array('edittimelimit' => $_G['group']['edittimelimit']));
	}
}

$thread['pricedisplay'] = $thread['price'] == -1 ? 0 : $thread['price'];

if($special == 5) {
	$debate = array_merge($thread, daddslashes(C::t('forum_debate')->fetch($_G['tid'])));
	$firststand = C::t('forum_debatepost')->get_firststand($_G['tid'], $_G['uid']);

	if(!$isfirstpost && $debate['endtime'] && $debate['endtime'] < TIMESTAMP && !$_G['forum']['ismoderator']) {
		showmessage('debate_end');
	}
	if($isfirstpost && $debate['umpirepoint'] && !$_G['forum']['ismoderator']) {
		showmessage('debate_umpire_comment_invalid');
	}
}

$rushreply = getstatus($thread['status'], 3);


if($isfirstpost && $isorigauthor && $_G['group']['allowreplycredit']) {
	if($replycredit_rule = C::t('forum_replycredit')->fetch($_G['tid'])) {
		if($thread['replycredit']) {
			$replycredit_rule['lasttimes'] = $thread['replycredit'] / $replycredit_rule['extcredits'];
		}
		$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
	}
}

if(!submitcheck('editsubmit')) {

	$thread['hiddenreplies'] = getstatus($thread['status'], 2);


	$postinfo = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid);
	if($postinfo['fid'] != $_G['fid'] || $postinfo['tid'] != $_G['tid']) {
		$postinfo = array();
	}

	$usesigcheck = $postinfo['usesig'] ? 'checked="checked"' : '';
	$urloffcheck = $postinfo['parseurloff'] ? 'checked="checked"' : '';
	$smileyoffcheck = $postinfo['smileyoff'] == 1 ? 'checked="checked"' : '';
	$codeoffcheck = $postinfo['bbcodeoff'] == 1 ? 'checked="checked"' : '';
	$tagoffcheck = $postinfo['htmlon'] & 2 ? 'checked="checked"' : '';
	$htmloncheck = $postinfo['htmlon'] & 1 ? 'checked="checked"' : '';
	if(!$isfirstpost) {
		$_G['group']['allowimgcontent'] = 0;
	}
	if($isfirstpost && $imgcontentcheck && $_G['group']['allowimgcontent']) {
		$editor['editormode'] = 0;
	}
	if($htmloncheck) {
		$editor['editormode'] = 0;
		$editor['allowswitcheditor'] = 0;
	}
	$showthreadsorts = ($thread['sortid'] || !empty($sortid)) && $isfirstpost;
	$sortid = empty($sortid) ? $thread['sortid'] : $sortid;

	$poll = $temppoll = array();
	if($isfirstpost) {
		if($postinfo['tags']) {
			$tagarray_all = $array_temp = $threadtag_array = array();
			$tagarray_all = explode("\t", $postinfo['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$array_temp = explode(',', $var);
						$threadtag_array[] = $array_temp['1'];
					}
				}
			}
			$postinfo['tag'] = implode(',', $threadtag_array);
		}
		$allownoticeauthor = getstatus($thread['status'], 6);

		if($rushreply) {
			$postinfo['rush'] = C::t('forum_threadrush')->fetch($_G['tid']);
			if($postinfo['rush']['creditlimit'] == -996) {
				$postinfo['rush']['creditlimit'] = '';
			}
			$postinfo['rush']['stopfloor'] = $postinfo['rush']['stopfloor'] ? $postinfo['rush']['stopfloor'] : '';
			$postinfo['rush']['starttimefrom'] = $postinfo['rush']['starttimefrom'] ? dgmdate($postinfo['rush']['starttimefrom'], 'Y-m-d H:i') : '';
			$postinfo['rush']['starttimeto'] = $postinfo['rush']['starttimeto'] ? dgmdate($postinfo['rush']['starttimeto'], 'Y-m-d H:i') : '';
		}

		if($special == 127) {
			$sppos = strpos($postinfo['message'], chr(0).chr(0).chr(0));
			$specialextra = substr($postinfo['message'], $sppos + 3);
			if($specialextra && array_key_exists($specialextra, $_G['setting']['threadplugins']) && in_array($specialextra, $_G['forum']['threadplugin']) && in_array($specialextra, $_G['group']['allowthreadplugin'])) {
				$postinfo['message'] = substr($postinfo['message'], 0, $sppos);
			} else {
				showmessage('post_edit_nopermission_threadplign');
				$special = 0;
				$specialextra = '';
			}
		}
		$thread['freecharge'] = $_G['setting']['maxchargespan'] && TIMESTAMP - $thread['dateline'] >= $_G['setting']['maxchargespan'] * 3600 ? 1 : 0;
		$freechargehours = !$thread['freecharge'] ? $_G['setting']['maxchargespan'] - intval((TIMESTAMP - $thread['dateline']) / 3600) : 0;
		if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $thread['authorid'] == $_G['uid'])) {
			$pollinfo = C::t('forum_poll')->fetch($_G['tid']);
			if($pollinfo['isimage']) {
				$pollimages = C::t('forum_polloption_image')->fetch_all_by_tid($_G['tid']);
				require_once libfile('function/home');
			}
			$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
			foreach($query as $temppoll) {
				$poll['multiple'] = $pollinfo['multiple'];
				$poll['visible'] = $pollinfo['visible'];
				$poll['maxchoices'] = $pollinfo['maxchoices'];
				$poll['expiration'] = $pollinfo['expiration'];
				$poll['overt'] = $pollinfo['overt'];
				$poll['isimage'] = $pollinfo['isimage'];
				$poll['polloptionid'][] = $temppoll['polloptionid'];
				$poll['displayorder'][] = $temppoll['displayorder'];
				$poll['polloption'][] = $temppoll['polloption'];
				$attach = array();
				if($pollinfo['isimage'] && $pollimages[$temppoll['polloptionid']]) {
					$attach = $pollimages[$temppoll['polloptionid']];
					$attach['small'] = pic_get($attach['attachment'], 'forum', $attach['thumb'], $attach['remote']);
					$attach['big'] = pic_get($attach['attachment'], 'forum', 0, $attach['remote']);
					$poll['imginfo'][$temppoll['polloptionid']] = $attach;
				}

			}
		} elseif($thread['special'] == 3) {
			$rewardprice = $thread['price'];
		} elseif($thread['special'] == 4) {
			$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
			$activity = C::t('forum_activity')->fetch($_G['tid']);
			$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'Y-m-d H:i');
			$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'Y-m-d H:i') : '';
			$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'Y-m-d H:i') : '';
			$activity['ufield'] = $activity['ufield'] ? dunserialize($activity['ufield']) : array();
			if($activity['ufield']['extfield']) {
				$activity['ufield']['extfield'] = implode("\n", $activity['ufield']['extfield']);
			}
		} elseif($thread['special'] == 5 ) {
			$debate['endtime'] = $debate['endtime'] ? dgmdate($debate['endtime'], 'Y-m-d H:i') : '';
		}
		if ($_G['group']['allowsetpublishdate']) {
			loadcache('cronpublish');
			$cron_publish_ids = dunserialize(getglobal('cache/cronpublish'));
			if (in_array($_G['tid'], $cron_publish_ids)) {
				$cronpublish = 1;
				$cronpublishdate = dgmdate($thread['dateline'], "dt");
			}
		}
	}

	if($thread['special'] == 2 && ($thread['authorid'] == $_G['uid'] && $_G['group']['allowposttrade'] || $_G['group']['allowedittrade'])) {
		$trade = C::t('forum_trade')->fetch_goods(0, $pid);
		if($trade) {
			$trade['expiration'] = $trade['expiration'] ? date('Y-m-d', $trade['expiration']) : '';
			$trade['costprice'] = $trade['costprice'] > 0 ? $trade['costprice'] : '';
			$trade['message'] = dhtmlspecialchars($trade['message']);
			$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
			$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
			$expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
			$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
			$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
			$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
		} else {
			$special = 0;
			$trade = array();
		}
	}

	if($isfirstpost && $specialextra) {
		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost')) {
			$threadplughtml = $threadpluginclass->editpost($_G['fid'], $_G['tid']);
		}
	}

	$postinfo['subject'] = str_replace('"', '&quot;', $postinfo['subject']);
	$postinfo['message'] = dhtmlspecialchars($postinfo['message']);
	$selectgroupid = 0;
	if($postinfo['first'] == 1) {
		preg_match("/(\[groupid=(\d+)\].*\[\/groupid\])/i", $postinfo['message'], $matchs);
		$postinfo['message'] = str_replace($matchs[1], '', $postinfo['message']);
		$selectgroupid = $matchs[2];

		if(helper_access::check_module('group')) {
			$mygroups = $groupids = array();
			$groupids = C::t('forum_groupuser')->fetch_all_fid_by_uids($_G['uid']);
			array_slice($groupids, 0, 20);
			$query = C::t('forum_forum')->fetch_all_info_by_fids($groupids);
			foreach($query as $group) {
				$mygroups[$group['fid']] = $group['name'];
			}
		}
	}
	$language = lang('forum/misc');
	$postinfo['message'] = preg_replace($postinfo['htmlon'] ? $language['post_edithtml_regexp'] : (!$_G['forum']['allowbbcode'] || $postinfo['bbcodeoff'] ? $language['post_editnobbcode_regexp'] : $language['post_edit_regexp']), '', $postinfo['message']);

	if($special == 5) {
		$standselected = array($firststand => 'selected="selected"');
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach($pid);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
		$attachfind = $attachreplace = array();
		if(!empty($attachs['used'])) {
			foreach($attachs['used'] as $attach) {
				if($attach['isimage']) {
					$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
					$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
				}
			}
		}
		if(!empty($imgattachs['used'])) {
			foreach($imgattachs['used'] as $attach) {
				$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
				$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
			}
		}
		$attachfind && $postinfo['message'] = preg_replace($attachfind, $attachreplace, $postinfo['message']);
	}
	if($special == 2 && $trade['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $tradeattach) {
			if($tradeattach['aid'] == $trade['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}
	if($special == 4 && $activity['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $activityattach) {
			if($activityattach['aid'] == $activity['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}

	if($sortid) {
		require_once libfile('post/threadsorts', 'include');
		foreach($_G['forum_optionlist'] as $option) {
			if($option['type'] == 'image') {
				foreach($imgattachs['used'] as $k => $sortattach) {
					if($sortattach['aid'] == $option['value']['aid']) {
						unset($imgattachs['used'][$k]);
						break;
					}
				}
			}
		}
	}

	$imgattachs['unused'] = !$sortid ? $imgattachs['unused'] : '';

	include template('forum/post');

} else {
	if($_GET['mygroupid']) {
		$mygroupid = explode('__', $_GET['mygroupid']);
		$mygid = intval($mygroupid[0]);
		if($mygid) {
			$mygname = $mygroupid[1];
			if(count($mygroupid) > 2) {
				unset($mygroupid[0]);
				$mygname = implode('__', $mygroupid);
			}
			$message .= '[groupid='.intval($mygid).']'.$mygname.'[/groupid]';
		}
	}
	$modpost = C::m('forum_post', $_G['tid'], $pid);

	$modpost->param('redirecturl', "forum.php?mod=viewthread&tid=$_G[tid]&page=$_GET[page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : '')."#pid$pid");

	if(empty($_GET['delete'])) {


		if($isfirstpost) {


			if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $isorigauthor) && !empty($_GET['polls'])) {

			} elseif($thread['special'] == 3 && $isorigauthor) {


			} elseif($thread['special'] == 4 && $_G['group']['allowpostactivity']) {


			} elseif($thread['special'] == 5 && $_G['group']['allowpostdebate']) {


			} elseif($specialextra) {

				@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit')) {
					$threadpluginclass->editpost_submit($_G['fid'], $_G['tid']);
				}

			}






		} else {


		}







		$feed = array();
		if($isfirstpost && $special == 127) {
			$message .= chr(0).chr(0).chr(0).$specialextra;
		}


		if($isfirstpost) {
			$modpost->attach_before_method('editpost', array('class' => 'extend_thread_sort', 'method' => 'before_editpost'));
			if($thread['special'] == 3) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_reward', 'method' => 'before_editpost'));
			}
			if($thread['special'] == 1) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_poll', 'method' => 'before_editpost'));
			}
			if($thread['special'] == 4 && $_G['group']['allowpostactivity']) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_activity', 'method' => 'before_editpost'));
			}
			if($thread['special'] == 5 && $_G['group']['allowpostdebate']) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_debate', 'method' => 'before_editpost'));
			}
			if($_G['group']['allowreplycredit']) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_replycredit', 'method' => 'before_editpost'));
			}
			if($rushreply) {
				$modpost->attach_before_method('editpost', array('class' => 'extend_thread_rushreply', 'method' => 'before_editpost'));
			}
			$modpost->attach_after_method('editpost', array('class' => 'extend_thread_follow', 'method' => 'after_editpost'));
		}

		if($_G['group']['allowat']) {
			$modpost->attach_before_method('editpost', array('class' => 'extend_thread_allowat', 'method' => 'before_editpost'));
			$modpost->attach_after_method('editpost', array('class' => 'extend_thread_allowat', 'method' => 'after_editpost'));
		}

		$modpost->attach_before_method('editpost', array('class' => 'extend_thread_image', 'method' => 'before_editpost'));

		if($special == '2' && $_G['group']['allowposttrade']) {
			$modpost->attach_before_method('editpost', array('class' => 'extend_thread_trade', 'method' => 'before_editpost'));
		}

		$modpost->attach_before_method('editpost', array('class' => 'extend_thread_filter', 'method' => 'before_editpost'));
		$modpost->attach_after_method('editpost', array('class' => 'extend_thread_filter', 'method' => 'after_editpost'));

		$param = array(
			'subject' => $subject,
			'message' => $message,
			'special' => $special,
			'sortid' => $sortid,
			'typeid' => $typeid,
			'isanonymous' => $isanonymous,

			'cronpublish' => $_GET['cronpublish'],
			'cronpublishdate' => $_GET['cronpublishdate'],
			'save' => $_GET['save'],

			'readperm' => $readperm,
			'price' => $_GET['price'],

			'ordertype' => $_GET['ordertype'],
			'hiddenreplies' => $_GET['hiddenreplies'],
			'allownoticeauthor' => $_GET['allownoticeauthor'],

			'audit' => $_GET['audit'],

			'tags' => $_GET['tags'],

			'bbcodeoff' => $_GET['bbcodeoff'],
			'smileyoff' => $_GET['smileyoff'],
			'parseurloff' => $_GET['parseurloff'],
			'usesig' => $_GET['usesig'],
			'htmlon' => $_GET['htmlon'],

			'extramessage' => $extramessage,
		);

		if($_G['group']['allowimgcontent']) {
			$param['imgcontent'] = $_GET['imgcontent'];
			$param['imgcontentwidth'] = $_G['setting']['imgcontentwidth'] ? intval($_G['setting']['imgcontentwidth']) : 100;
		}
		if($isfirstpost && $isorigauthor && $_G['group']['allowreplycredit']) {
			$param['replycredit_rule'] = $replycredit_rule;
		}

		$modpost->editpost($param);

	} else {










		if($thread['special'] == 3) {
			$modpost->attach_before_method('deletepost', array('class' => 'extend_thread_reward', 'method' => 'before_deletepost'));
		}
		if($rushreply) {
			$modpost->attach_before_method('deletepost', array('class' => 'extend_thread_rushreply', 'method' => 'before_deletepost'));
		}
		if($thread['replycredit'] && $isfirstpost) {
			$modpost->attach_before_method('deletepost', array('class' => 'extend_thread_replycredit', 'method' => 'before_deletepost'));
		}

		$modpost->attach_before_method('deletepost', array('class' => 'extend_thread_image', 'method' => 'before_deletepost'));

		if($thread['special'] == 2) {
			$modpost->attach_after_method('deletepost', array('class' => 'extend_thread_trade', 'method' => 'after_deletepost'));
		}
		if($isfirstpost) {
			$modpost->attach_after_method('deletepost', array('class' => 'extend_thread_sort', 'method' => 'after_deletepost'));
		}

		$modpost->attach_after_method('deletepost', array('class' => 'extend_thread_filter', 'method' => 'after_deletepost'));

		$param = array(
			'special' => $special,
			'isanonymous' => $isanonymous,
		);

		$modpost->deletepost($param);
	}

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit_end')) {
			$threadpluginclass->editpost_submit_end($_G['fid'], $_G['tid']);
		}

	}

	if($_G['forum']['threadcaches']) {
		deletethreadcaches($_G['tid']);
	}

	$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid);

	dsetcookie('clearUserdata', 'forum');

	if($_G['forum_auditstatuson']) {
		if($audit == 1) {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid, '2');
			showmessage('auditstatuson_succeed', $modpost->param('redirecturl'), $param);
		} else {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid);
			showmessage('audit_edit_succeed', '', $param, array('alert' => 'right'));
		}
	} else {
		if(!empty($_GET['delete']) && $isfirstpost) {
			showmessage('post_edit_delete_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
		} elseif(!empty($_GET['delete'])) {
			showmessage('post_edit_delete_succeed', "forum.php?mod=viewthread&tid=$_G[tid]&page=$_GET[page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : ''), $param);
		} else {
			if($isfirstpost && $modpost->param('modnewthreads')) {
				C::t('forum_post')->update($thread['posttableid'], $pid, array('status' => 4), false, false, null, -2, null, 0);
				updatemoderate('tid', $_G['tid']);
				showmessage('edit_newthread_mod_succeed', $modpost->param('redirecturl'), $param);
			} elseif(!$isfirstpost && $modpost->param('modnewreplies')) {
				C::t('forum_post')->update($thread['posttableid'], $pid, array('status' => 4), false, false, null, -2, null, 0);
				updatemoderate('pid', $pid);
				showmessage('edit_reply_mod_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
			} else {
				showmessage('post_edit_succeed', $modpost->param('redirecturl'), $param);
			}
		}
	}

}

?>