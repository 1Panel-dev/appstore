<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_medal.php 28887 2012-03-16 10:17:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('medals');

if(!$_G['uid'] && $_GET['action']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$_G['mnid'] = 'mn_common';
$medallist = $medallogs = array();
$tpp = 10;
$page = max(1, intval($_GET['page']));
$start_limit = ($page - 1) * $tpp;

if(empty($_GET['action'])) {
	include libfile('function/forum');
	$medalcredits = array();
	foreach(C::t('forum_medal')->fetch_all_data(1) as $medal) {
		$medal['permission'] = medalformulaperm(serialize(array('medal' => dunserialize($medal['permission']))), $medal['type']);
		if($medal['price']) {
			$medal['credit'] = $medal['credit'] ? $medal['credit'] : $_G['setting']['creditstransextra'][3];
			$medalcredits[$medal['credit']] = $medal['credit'];
		}
		$medallist[$medal['medalid']] = $medal;
	}

	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
	$membermedal = $memberfieldforum['medals'] ? explode("\t", $memberfieldforum['medals']) : array();
	$membermedal = array_map('intval', $membermedal);

	$lastmedals = $uids = array();
	foreach(C::t('forum_medallog')->fetch_all_lastmedal(10) as $id => $lastmedal) {
		$lastmedal['dateline'] = dgmdate($lastmedal['dateline'], 'u');
		$lastmedals[$id] = $lastmedal;
		$uids[] = $lastmedal['uid'];
	}
	$lastmedalusers = C::t('common_member')->fetch_all($uids);
	$mymedals = C::t('common_member_medal')->fetch_all_by_uid($_G['uid']);
	$mymedals = array_keys($mymedals);
	$applylogs = C::t('forum_medallog')->fetch_all_by_type(2);
	foreach ($applylogs as $id => $log) {
		$log['uid'] == $_G['uid'] && $mymedals[$log['medalid']] = $log['medalid'];
	}

} elseif($_GET['action'] == 'confirm') {

	include libfile('function/forum');
	$medal = C::t('forum_medal')->fetch($_GET['medalid']);
	$medal['permission'] = medalformulaperm(serialize(array('medal' => dunserialize($medal['permission']))), $medal['type']);
	if($medal['price']) {
		$medal['credit'] = $medal['credit'] ? $medal['credit'] : $_G['setting']['creditstransextra'][3];
		$medalcredits[$medal['credit']] = $medal['credit'];
	}
	include template('home/space_medal_float');

} elseif($_GET['action'] == 'apply' && submitcheck('medalsubmit')) {

	$medalid = intval($_GET['medalid']);
	$_G['forum_formulamessage'] = $_G['forum_usermsg'] = $medalnew = '';
	$medal = C::t('forum_medal')->fetch($medalid);
	if(!$medal['type']) {
		showmessage('medal_apply_invalid');
	}

	if(C::t('common_member_medal')->count_by_uid_medalid($_G['uid'], $medalid)) {
		showmessage('medal_apply_existence', 'home.php?mod=medal');
	}

	$applysucceed = FALSE;
	$medalpermission = $medal['permission'] ? dunserialize($medal['permission']) : '';
	if($medalpermission[0] || $medalpermission['usergroupallow']) {
		include libfile('function/forum');
		medalformulaperm(serialize(array('medal' => $medalpermission)), $medal['type']);

		if($_G['forum_formulamessage']) {
			showmessage('medal_permforum_nopermission', 'home.php?mod=medal', array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']));
		} else {
			$applysucceed = TRUE;
		}
	} else {
		$applysucceed = TRUE;
	}

	if($applysucceed) {
		$expiration = empty($medal['expiration'])? 0 : TIMESTAMP + $medal['expiration'] * 86400;
		if($medal['type'] == 1) {
			if($medal['price']) {
				$medal['credit'] = $medal['credit'] ? $medal['credit'] : $_G['setting']['creditstransextra'][3];
				if($medal['price'] > getuserprofile('extcredits'.$medal['credit'])) {
					showmessage('medal_not_get_credit', '', array('credit' => $_G['setting']['extcredits'][$medal[credit]][title]));
				}
				updatemembercount($_G['uid'], array($medal['credit'] => -$medal['price']), true, 'BME', $medal['medalid']);
			}

			$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
			$usermedal = $memberfieldforum;
			unset($memberfieldforum);
			$medal['medalid'] = $medal['medalid'].(empty($expiration) ? '' : '|'.$expiration);
			$medalnew = $usermedal['medals'] ? $usermedal['medals']."\t".$medal['medalid'] : $medal['medalid'];
			C::t('common_member_field_forum')->update($_G['uid'], array('medals' => $medalnew));
			C::t('common_member_medal')->insert(array('uid' => $_G['uid'], 'medalid' => $medal['medalid']), 0, 1);
			$medalmessage = 'medal_get_succeed';
		} else {
			if(C::t('forum_medallog')->count_by_verify_medalid($_G['uid'], $medal['medalid'])) {
				showmessage('medal_apply_existence', 'home.php?mod=medal');
			}
			$medalmessage = 'medal_apply_succeed';
			manage_addnotify('verifymedal');
		}

		C::t('forum_medallog')->insert(array(
		    'uid' => $_G['uid'],
		    'medalid' => $medalid,
		    'type' => $medal['type'],
		    'dateline' => TIMESTAMP,
		    'expiration' => $expiration,
		    'status' => ($expiration ? 1 : 0),
		));
		showmessage($medalmessage, 'home.php?mod=medal', array('medalname' => $medal['name']));
	}

} elseif($_GET['action'] == 'log') {

	include libfile('function/forum');
	foreach(C::t('forum_medal')->fetch_all_data(1) as $medal) {
		$medallist[$medal['medalid']] = $medal;
	}

	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
	$membermedal = $memberfieldforum['medals'] ? explode("\t", $memberfieldforum['medals']) : array();
	foreach($membermedal as $k => $medal) {
		if(!in_array($medal, array_keys($medallist))) {
			unset($membermedal[$k]);
		}
	}
	$medalcount = count($membermedal);

	if(!empty($membermedal)) {
		$mymedal = array();
		foreach($membermedal as $medalid) {
			if($medalpos = strpos($medalid, '|')) {
				$medalid = substr($medalid, 0, $medalpos);
			}
			$mymedal['name'] = $_G['cache']['medals'][$medalid]['name'];
			$mymedal['image'] = $medallist[$medalid]['image'];
			$mymedals[] = $mymedal;
		}
	}

	$medallognum = C::t('forum_medallog')->count_by_uid($_G['uid']);
	$multipage = multi($medallognum, $tpp, $page, "home.php?mod=medal&action=log");

	foreach(C::t('forum_medallog')->fetch_all_by_uid($_G['uid'], $start_limit, $tpp) as $medallog) {
		$medallog['name'] = $_G['cache']['medals'][$medallog['medalid']]['name'];
		$medallog['dateline'] = dgmdate($medallog['dateline']);
		$medallog['expiration'] = !empty($medallog['expiration']) ? dgmdate($medallog['expiration']) : '';
		$medallogs[] = $medallog;
	}

} else {
	showmessage('undefined_action');
}

$navtitle = lang('core', 'title_medals_list');

include template('home/space_medal');

?>