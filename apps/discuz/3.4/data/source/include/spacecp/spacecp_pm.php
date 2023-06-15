<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_pm.php 35056 2014-11-03 08:01:19Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$plid = empty($_GET['plid'])?0:intval($_GET['plid']);
$opactives['pm'] = 'class="a"';

if($uid) {
	$touid = $uid;
} else {
	$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
}
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

loaducenter();

if($_GET['op'] == 'checknewpm') {

	header('Content-Type: text/javascript');

	if($_G['uid'] && !getstatus($_G['member']['newpm'], 1)) {
		$ucnewpm = intval(uc_pm_checknew($_G['uid']));
		$newpm = setstatus(1, $ucnewpm ? 1 : 0, $_G['member']['newpm']);
		if($_G['member']['newpm'] != $newpm) {
			C::t('common_member')->update($_G['uid'], array('newpm' => $newpm));
		}
	}
	dsetcookie('checkpm', 1, 30);
	exit();

} elseif($_GET['op'] == 'getpmuser') {
	$otherpm = $json = array();
	$result = uc_pm_list($_G['uid'], 1, 30, 'inbox', 'privatepm');
	foreach($result['data'] as $key => $value) {
		$value['lastauthor'] = daddslashes($value['lastauthor']);
		$value['avatar'] = avatar($value['lastauthorid'], 'small', true);
		if($value['isnew']) {
			$json[$value['lastauthorid']] = "$value[lastauthorid]:{'uid':$value[lastauthorid], 'username':'$value[lastauthor]', 'avatar':'$value[avatar]', 'plid':$value[plid], 'isnew':$value[isnew], 'daterange':$value[daterange]}";
		} else {
			$otherpm[$value['lastauthorid']] = "$value[lastauthorid]:{'uid':$value[lastauthorid], 'username':'$value[lastauthor]', 'avatar':'$value[avatar]', 'plid':$value[plid], 'isnew':$value[isnew], 'daterange':$value[daterange]}";
		}
	}
	if(!empty($otherpm)) {
		$json = array_merge($json, $otherpm);
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}}";

} elseif($_GET['op'] == 'showmsg') {

	$msgonly = empty($_GET['msgonly']) ? 0 : intval($_GET['msgonly']);
	$touid = empty($_GET['touid']) ? 0: intval($_GET['touid']);
	$daterange = empty($_GET['daterange']) ? 1 : intval($_GET['daterange']);
	$result = uc_pm_view($_G['uid'], 0, $touid, $daterange, 0, 0, 0, 0);
	$msglist = array();
	$msguser = $messageappend = '';
	$online = 0;
	foreach($result as $key => $value) {
		if($value['authorid'] != $_G['uid']) {
			$msguser = $value['author'];
		}
		$daykey = dgmdate($value['dateline'], 'Y-m-d');
		$msglist[$daykey][$key] = $value;
	}
	if($touid && empty($msguser)) {
		$member = getuserbyuid($touid);
		$msguser = $member['username'];
	}
	if(!$msgonly) {
		$online = C::app()->session->fetch_by_uid($touid) ? 1 : 0;
		if($_G['member']['newpm']) {
			$newpm = setstatus(1, 0, $_G['member']['newpm']);
			C::t('common_member')->update($_G['uid'], array('newpm' => $newpm));
			uc_pm_ignore($_G['uid']);
		}
	}
	if(!empty($_GET['tradeid'])) {
		$trade = C::t('forum_trade')->fetch_goods(0, $_GET['tradeid']);
		if($trade) {
			$messageappend = dhtmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=viewthread&tid='.$trade['tid'].'&do=tradeinfo&pid='.$trade['pid'].'][b]'.$trade['subject'].'[/b][/url]');
		}
	} elseif(!empty($_GET['commentid'])) {
		$comment = C::t('forum_postcomment')->fetch($_GET['commentid']);
		if($comment) {
			$comment['comment'] = str_replace(array('[b]', '[/b]', '[/color]'), array(''), preg_replace("/\[color=([#\w]+?)\]/i", '', strip_tags($comment['comment'])));
			$messageappend = dhtmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=redirect&goto=findpost&pid='.$comment['pid'].'&ptid='.$comment['tid'].'][b]'.lang('spacecp', 'pm_comment').'[/b][/url][quote]'.$comment['comment'].'[/quote]');
		}
	} elseif(!empty($_GET['tid']) && !empty($_GET['pid'])) {
		$thread = C::t('forum_thread')->fetch($_GET['tid']);
		if($thread) {
			$messageappend = dhtmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=redirect&goto=findpost&pid='.intval($_GET['pid']).'&ptid='.$thread['tid'].'][b]'.lang('spacecp', 'pm_thread_about', array('subject' => $thread['subject'])).'[/b][/url]');
		}
	}

} elseif($_GET['op'] == 'showchatmsg') {
	$perpage = 50;
	$perpage = mob_perpage($perpage);
	$page = empty($_GET['page']) ? ceil($count/$perpage) : intval($_GET['page']);
	$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count/$perpage)-$page+1, $perpage, 1, 1);

} elseif($_GET['op'] == 'delete') {

	if($_GET['formhash'] != formhash()) {
		showmessage('delete_pm_error_option');
	}

	$gpmid = is_array($_GET['deletepm_gpmid']) ? $_GET['deletepm_gpmid'] : 0;
	$deluid = is_array($_GET['deletepm_deluid']) ? $_GET['deletepm_deluid'] : 0;
	$delpmid = is_array($_GET['deletepm_pmid']) ? $_GET['deletepm_pmid'] : 0;
	$delplid = is_array($_GET['deletepm_delplid']) ? $_GET['deletepm_delplid'] : 0;
	$quitplid = is_array($_GET['deletepm_quitplid']) ? $_GET['deletepm_quitplid'] : 0;

	if(empty($gpmid) && empty($deluid) && empty($delpmid) && empty($delplid) && empty($quitplid)) {
		showmessage('delete_pm_error_option');
	}

	if(submitcheck('deletesubmit', 1)) {
		$flag = true;

		if(!empty($gpmid)) {
			$return = C::t('common_member_grouppm')->update($_G['uid'], $gpmid, array('status' => -1));
			$returnurl = 'home.php?mod=space&do=pm&filter=announcepm';
			if(!$return) {
				$flag = false;
			}
		}
		if(!empty($deluid)) {
			$return = uc_pm_deleteuser($_G['uid'], $deluid);
			$returnurl = 'home.php?mod=space&do=pm&filter=privatepm';
			if($return <= 0) {
				$flag = false;
			}
		}

		if(!empty($delpmid)) {
			$return = uc_pm_delete($_G['uid'], 'inbox', $delpmid[0]);
			$returnurl = 'home.php?mod=space&do=pm&subop=view&touid='.$touid;
			if($return <= 0) {
				$flag = false;
			}
		}

		if(!empty($delplid)) {
			$return = uc_pm_deletechat($_G['uid'], $delplid, 1);
			$returnurl = 'home.php?mod=space&do=pm&filter=privatepm';
			if(!$return) {
				$flag = false;
			}
		}

		if(!empty($quitplid)) {
			$return = uc_pm_deletechat($_G['uid'], $quitplid);
			$returnurl = 'home.php?mod=space&do=pm&filter=privatepm';
			if(!$return) {
				$flag = false;
			}
		}

		if($flag) {
			showmessage('delete_pm_success', $returnurl);
		} else {
			showmessage('this_message_could_note_be_option');
		}
	}

} elseif($_GET['op'] == 'send') {

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('message_can_not_send_2', '', array(), array('return' => true));
	}

	cknewuser();

	if(!checkperm('allowsendpm')) {
		showmessage('no_privilege_sendpm', '', array(), array('return' => true));
	}

	if($touid) {
		if(isblacklist($touid)) {
			showmessage('is_blacklist', '', array(), array('return' => true));
		}
	}

	if(submitcheck('pmsubmit')) {
		if(!empty($_POST['username'])) {
			$_POST['users'][] = $_POST['username'];
		}
		$users = empty($_POST['users']) ? array() : $_POST['users'];
		$type = intval($_POST['type']);
		$coef = 1;
		if(!empty($users)) {
			$coef = count($users);
		}

		!($_G['group']['exempt'] & 1) && checklowerlimit('sendpm', 0, $coef);

		$message = (!empty($_POST['messageappend']) ? $_POST['messageappend']."\n" : '').trim($_POST['message']);
		if(empty($message)) {
			showmessage('unable_to_send_air_news', '', array(), array('return' => true));
		}
		$message = censor($message);
		loadcache(array('smilies', 'smileytypes'));
		foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
			$_G['cache']['smilies']['replacearray'][$key] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'[/img]';
		}
		$message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message);
		$subject = '';
		if($type == 1) {
			$subject = dhtmlspecialchars(trim($_POST['subject']));
		}

		include_once libfile('function/friend');
		$return = 0;
		if($touid || $pmid) {
			if($touid) {
				if(($value = getuserbyuid($touid))) {
					$value['onlyacceptfriendpm'] = $value['onlyacceptfriendpm'] ? $value['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
					if($_G['group']['allowsendallpm'] || $value['onlyacceptfriendpm'] == 2 || ($value['onlyacceptfriendpm'] == 1 && friend_check($touid))) {
						$return = sendpm($touid, $subject, $message, '', 0, 0, $type);
					} else {
						showmessage('message_can_not_send_onlyfriend', '', array(), array('return' => true));
					}
				} else {
					showmessage('message_bad_touid', '', array(), array('return' => true));
				}
			} else {
				$topmuid = intval($_GET['topmuid']);
				$return = sendpm($topmuid, $subject, $message, '', $pmid, 0);
			}

		} elseif($users) {
			$newusers = $uidsarr = $membersarr = array();
			if($users) {
				$membersarr = C::t('common_member')->fetch_all_by_username($users);
				foreach($membersarr as $aUsername=>$aUser) {
					$uidsarr[] = $aUser['uid'];
				}
			}
			if(empty($membersarr)) {
				showmessage('message_bad_touser', '', array(), array('return' => true));
			}
			if(isset($membersarr[$_G['uid']])) {
				showmessage('message_can_not_send_to_self', '', array(), array('return' => true));
			}

			friend_check($uidsarr);

			foreach($membersarr as $key => $value) {

				$value['onlyacceptfriendpm'] = $value['onlyacceptfriendpm'] ? $value['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
				if($_G['group']['allowsendallpm'] || $value['onlyacceptfriendpm'] == 2 || ($value['onlyacceptfriendpm'] == 1 && $_G['home_friend_'.$value['uid'].'_'.$_G['uid']])) {
					$newusers[$value['uid']] = $value['username'];
					unset($users[array_search($value['username'], $users)]);
				}
			}

			if(empty($newusers)) {
				showmessage('message_can_not_send_onlyfriend', '', array(), array('return' => true));
			}

			foreach($newusers as $key=>$value) {
				if(isblacklist($key)) {
					showmessage('is_blacklist', '', array(), array('return' => true));
				}
			}
			$coef = count($newusers);
			$return = sendpm(implode(',', $newusers), $subject, $message, '', 0, 1, $type);
		} else {
			showmessage('message_can_not_send_9', '', array(), array('return' => true));
		}

		if($return > 0) {
			include_once libfile('function/stat');
			updatestat('sendpm', 0, $coef);

			C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP));
			!($_G['group']['exempt'] & 1) && updatecreditbyaction('sendpm', 0, array(), '', $coef);
			if(!empty($newusers)) {
				if($type == 1) {
					$returnurl = 'home.php?mod=space&do=pm&filter=privatepm';
				} else {
					$returnurl = 'home.php?mod=space&do=pm';
				}
				showmessage(count($users) ? 'message_send_result' : 'do_success', $returnurl, array('users' => implode(',', $users), 'succeed' => count($newusers)));
			} else {
				if(!defined('IN_MOBILE')) {
					showmessage('do_success', 'home.php?mod=space&do=pm&subop=view&touid='.$touid, array('pmid' => $return), $_G['inajax'] ? array('msgtype' => 3, 'showmsg' => false) : array());
				} else {
					showmessage('do_success', 'home.php?mod=space&do=pm&subop=view'.(intval($_POST['touid']) ? '&touid='.intval($_POST['touid']) : ( intval($_POST['plid']) ? '&plid='.intval($_POST['plid']).'&daterange=1&type=1' : '' )));
				}

			}
		} else {
			if(in_array($return, range(-16, -1))) {
				showmessage('message_can_not_send_'.abs($return));
			} else {
				showmessage('message_can_not_send', '', array(), array('return' => true));
			}
		}
	}

} elseif($_GET['op'] == 'ignore') {

	if(submitcheck('ignoresubmit')) {
		$single = intval($_GET['single']);
		if($single) {
			uc_pm_blackls_add($_G['uid'], $_POST['ignoreuser']);
			showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		} else {
			uc_pm_blackls_set($_G['uid'], $_POST['ignorelist']);
			showmessage('do_success', 'home.php?mod=space&do=pm&view=ignore', array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

} elseif($_GET['op'] == 'setting') {

	if(submitcheck('settingsubmit')) {
		if(!(intval($_GET['onlyacceptfriendpm']) && intval($_GET['onlyacceptfriendpm']) == $_GET['onlyacceptfriendpm'])) {
			showmessage('pm_onlyacceptfriend_error', 'home.php?mod=space&do=pm&subop=setting');
		}

		uc_pm_blackls_set($_G['uid'], $_POST['ignorelist']);
		$setarr['onlyacceptfriendpm'] = $_GET['onlyacceptfriendpm'];

		C::t('common_member')->update($_G['uid'], $setarr);

		showmessage('do_success_pm', 'home.php?mod=space&do=pm&subop=setting');
	}

} elseif($_GET['op'] == 'pm_report') {

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	if(!$pmid) {
		showmessage('pm_report_error_nopm');
	}
	if($pmid && submitcheck('pmreportsubmit', 1)) {
		$pms = uc_pm_view($_G['uid'], $pmid);
		$pm = $pms[0];
		if(empty($pm)) {
			showmessage('pm_report_error_nopm');
		}
		if($pm['authorid'] == $_G['uid'] || !$pm['authorid']) {
			showmessage('pm_report_error_nome');
		}
		$pmreportuser = explode(',', $_G['setting']['pmreportuser']);
		if(empty($pmreportuser)) {
			showmessage('pm_report_error_nopmreportuser');
		}

		$pmreportcontent = lang('spacecp', 'pm_report_content', array('reporterid' => $_G['uid'], 'reportername' => $_G['username'], 'uid' => $pm['authorid'], 'username' => $pm['author'], 'message' => $pm['message']));
		foreach($pmreportuser as $key => $value) {
			notification_add($value, 'pmreport', 'pmreportcontent', array('pmreportcontent' => $pmreportcontent), 0);
		}
		showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

} elseif($_GET['op'] == 'pm_ignore') {
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}
	$username = $_GET['username'];

	if(!$username || !uc_get_user(addslashes($username))) {
		showmessage('pm_ignore_error_nopm');
	}

	if(submitcheck('pmignoresubmit')) {
		uc_pm_blackls_add($_G['uid'], addslashes($username));
		showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

} elseif($_GET['op'] == 'kickmember') {

	$memberuid = intval($_GET['memberuid']);
	if(!$memberuid) {
		showmessage('pm_kickmember_error_nopm');
	}
	if(submitcheck('pmkickmembersubmit')) {
		uc_pm_kickchatpm($plid, $_G['uid'], $memberuid);
		showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'locationtime' => 3));
	}

} elseif($_GET['op'] == 'appendmember') {

	$memberusername = trim($_GET['memberusername']);
	$members = array();
	if($memberusername) {
		$members = C::t('common_member')->fetch_all_by_username(explode(',', $memberusername));
	}
	if(empty($members)) {
		showmessage('pm_appendkmember_error_nopm');
	}
	if(submitcheck('pmappendmembersubmit')) {
		include_once libfile('function/friend');
		$returns = array();
		foreach($members as $member) {
			$member['onlyacceptfriendpm'] = $member['onlyacceptfriendpm'] ? $member['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
			if($_G['group']['allowsendallpm'] || $member['onlyacceptfriendpm'] == 2 || ($member['onlyacceptfriendpm'] == 1 && friend_check($member['uid']))) {
				$return = uc_pm_appendchatpm($plid, $_G['uid'], $member['uid']);
				$returns[] = array('uid' => $member['uid'], 'username' => $member['username'], 'return' => $return);
			} else {
				$returns[] = array('uid' => $member['uid'], 'username' => $member['username'], 'return' => 0);
			}
		}
		$cannotappend = array();
		foreach($returns as $value) {
			if($value['return'] < 0) {
				$cannotappend[] = $value['username'].'('.lang('spacecp', 'message_can_not_send_'.abs($value['return'])).')';
			} elseif($value['return'] == 0) {
				$cannotappend[] = $value['username'].'('.lang('spacecp', 'message_can_not_send_onlyfriend').')';
			}
		}
		if(empty($cannotappend)) {
			showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'locationtime' => 3));
		} else {
			showmessage('message_can_not_append_reason', dreferer(), array('cannotappend' => implode('<br />', $cannotappend)), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'locationtime' => 5));
		}
	}

} elseif($_GET['op'] == 'setpmstatus') {

	$gpmids = trim($_GET['gpmids']);
	$plids = trim($_GET['plids']);
	if($gpmids) {
		$gpmidarr = explode(',', $gpmids);
		C::t('common_member_grouppm')->update_to_read_by_unread($_G['uid'], $gpmidarr);
	}
	if($plids) {
		$plidarr = explode(',', $plids);
		uc_pm_readstatus($_G['uid'], array(), $plidarr, 0);
	}
	showmessage('do_success', '', array(), array('msgtype' => 3));

} elseif($_GET['op'] == 'viewpmid') {

	$list = uc_pm_view($_G['uid'], $_GET['pmid']);
	$value = $list[0];
	include template('common/header_ajax');
	include template('home/space_pm_node');
	include template('common/footer_ajax');
	exit;

} elseif($_GET['op'] == 'export') {

	if(!$touid && !$plid) {
		showmessage('pm_export_touser_not_exists');
	}

	if($touid) {
		$list = uc_pm_view($_G['uid'], 0, $touid, 5, 0, 0, 0, 0);
	} else {
		$list = uc_pm_view($_G['uid'], 0, $plid, 5, 0, 0, 1, 1);
		$subject = $list[0]['subject'];
	}

	if(count($list) == 0) {
		showmessage('pm_emport_banned_export');
	}
	$filename = lang('space', 'export_pm').'.html';
	if($touid) {
		if($touser = uc_get_user($touid, 1)) {
			$tousername = $touser[1];
			$filename = $touser[1].'.html';
		}
	}
	$contents = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$contents .= '<html xmlns="http://www.w3.org/1999/xhtml">';
	$contents .= '<head><meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" /><title>'.lang('space', 'pm_export_header').'</title></head>';
	$contents .= '<body>';
	$contents .= lang('space', 'pm_export_header');
	$contents .= "\r\n\r\n================================================================\r\n";
	if($touser) {
		$contents .= lang('space', 'pm_export_touser', array('touser' => '<a href="'.$_G['siteurl'].'home.php?mod=space&uid='.$touser[0].'">'.$touser[1].'</a>'));
		$contents .= "\r\n================================================================\r\n";
	} elseif($subject) {
		$contents .= lang('space', 'pm_export_subject', array('subject' => $subject));
		$contents .= "\r\n================================================================\r\n";
	}
	$contents .= "\r\n";
	foreach($list as $key => $val) {
		$contents .= $val['author']."\t".dgmdate($val['dateline'])."\r\n";
		$contents .= str_replace(array('<br>', '<br />', '&nbsp;'), array("\r\n", "\r\n", ' '), $val['message'])."\r\n\r\n";
	}
	$contents .= '</body></html>';
	$contents = nl2br($contents);

	$filesize = strlen($contents);
	$filenameencode = strtolower(CHARSET) == 'utf-8' ? rawurlencode($filename) : rawurlencode(diconv($filename, CHARSET, 'UTF-8'));

	$rfc6266blacklist = strexists($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') || strexists($_SERVER['HTTP_USER_AGENT'], 'Quark') || strexists($_SERVER['HTTP_USER_AGENT'], 'SogouM') || strexists($_SERVER['HTTP_USER_AGENT'], 'baidu');

	dheader('Date: '.gmdate('D, d M Y H:i:s', $val['dateline']).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $val['dateline']).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename="'.$filenameencode.'"'.(($filename == $filenameencode || $rfc6266blacklist) ? '' : '; filename*=utf-8\'\''.$filenameencode));

	dheader('Content-Type: application/octet-stream');
	dheader('Content-Length: '.$filesize);

	echo $contents;
	die;

} else {

	cknewuser();

	if(!checkperm('allowsendpm')) {
		showmessage('no_privilege_sendpm');
	}
	$friends = array();
	if($space['friendnum']) {
		$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 100, true);
		foreach($query as $value) {
			$value['uid'] = $value['fuid'];
			$value['username'] = daddslashes($value['fusername']);
			$friends[] = $value;
		}
	}
	require_once libfile('function/friend');
	$friendgrouplist = friend_group_list();

	$type = intval($_GET['type']);
}

include_once template("home/spacecp_pm");

?>