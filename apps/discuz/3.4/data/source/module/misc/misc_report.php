<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_report.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['uid'])) {
	showmessage('not_loggedin', null, array(), array('login' => 1));
}
$rtype = $_GET['rtype'];
$rid = intval($_GET['rid']);
$tid = intval($_GET['tid']);
$fid = intval($_GET['fid']);
$uid = intval($_GET['uid']);
$default_url = array(
	'user' => 'home.php?mod=space&uid=',
	'post' => 'forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid=',
	'thread' => 'forum.php?mod=viewthread&tid=',
	'group' => 'forum.php?mod=group&fid=',
	'album' => 'home.php?mod=space&do=album&uid='.$uid.'&id=',
	'blog' => 'home.php?mod=space&do=blog&uid='.$uid.'&id=',
	'pic' => 'home.php?mod=space&do=album&uid='.$uid.'&picid='
);
$url = '';
if($rid && !empty($default_url[$rtype])) {
	$url = $default_url[$rtype].intval($rid);
} else {
	$url = addslashes(dhtmlspecialchars(base64_decode($_GET['url'])));
	$url = preg_match("/^http[s]?:\/\/[^\[\"']+$/i", trim($url)) ? trim($url) : '';
}
if(empty($url) || empty($_G['inajax'])) {
	showmessage('report_parameters_invalid');
}
$urlkey = md5($url);
if(submitcheck('reportsubmit')) {
	$message = censor(cutstr(dhtmlspecialchars(trim($_GET['message'])), 200, ''));
	$message = $_G['username'].'&nbsp;:&nbsp;'.rtrim($message, "\\");
	if($reportid = C::t('common_report')->fetch_by_urlkey($urlkey)) {
		C::t('common_report')->update_num($reportid, $message);
	} else {
		$data = array('url' => $url, 'urlkey' => $urlkey, 'uid' => $_G['uid'], 'username' => $_G['username'], 'message' => $message, 'dateline' => TIMESTAMP);
		if($fid) {
			$data['fid'] = $fid;
		}
		C::t('common_report')->insert($data);
		$report_receive = unserialize($_G['setting']['report_receive']);
		$moderators = array();
		if($report_receive['adminuser']) {
			foreach($report_receive['adminuser'] as $touid) {
				notification_add($touid, 'report', 'new_report', array('from_id' => 1, 'from_idtype' => 'newreport'), 1);
			}
		}
		if($fid && $rtype == 'post') {
			foreach(C::t('forum_moderator')->fetch_all_by_fid($fid, false) as $row) {
				$moderators[] = $row['uid'];
			}
			if($report_receive['supmoderator']) {
				$moderators = array_unique(array_merge($moderators, $report_receive['supmoderator']));
			}
			foreach($moderators as $touid) {
				$touid != $_G['uid'] && !in_array($touid, $report_receive['adminuser']) && notification_add($touid, 'report', 'new_post_report', array('fid' => $fid, 'from_id' => 1, 'from_idtype' => 'newreport'), 1);
			}
		}
	}
	showmessage('report_succeed', '', array(), array('closetime' => true, 'showdialog' => 1, 'alert' => 'right'));
}
require_once libfile('function/misc');
include template('common/report');
?>