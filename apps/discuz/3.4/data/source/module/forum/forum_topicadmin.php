<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_topicadmin.php 30872 2012-06-27 10:11:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

$_G['inajax'] = 1;
$_GET['topiclist'] = !empty($_GET['topiclist']) ? (is_array($_GET['topiclist']) ? array_unique($_GET['topiclist']) : $_GET['topiclist']) : array();

loadcache(array('modreasons', 'stamptypeid', 'threadtableids'));

require_once libfile('function/post');
require_once libfile('function/misc');

$modpostsnum = 0;
$resultarray = $thread = array();

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
$specialperm = $_GET['action'] == 'stickreply' && $_G['thread']['authorid'] == $_G['uid'];

if(!$specialperm && (!$_G['uid'] || !$_G['forum']['ismoderator'])) {
	showmessage('admin_nopermission', NULL);
}

$frommodcp = !empty($_GET['frommodcp']) ? intval($_GET['frommodcp']) : 0;


$navigation = $navtitle = '';

if(!empty($_G['tid'])) {
	$_GET['archiveid'] = intval($_GET['archiveid']);
	$archiveid = 0;
	if(!empty($_GET['archiveid']) && in_array($_GET['archiveid'], $threadtableids)) {
		$archiveid = $_GET['archiveid'];
	}
	$displayorder = !$_G['forum_auditstatuson'] ? 0 : null;
	$thread = C::t('forum_thread')->fetch_by_tid_fid_displayorder($_G['tid'], $_G['fid'], $displayorder, $archiveid);
	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	$navigation .= " &raquo; <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
	$navtitle .= ' - '.$thread['subject'].' - ';

	if($thread['special'] && in_array($_GET['action'], array('copy', 'split', 'merge'))) {
		showmessage('special_noaction');
	}
}
if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_GET['sendreasonpm'])) {
	$forumname = strip_tags($_G['forum']['name']);
	$sendreasonpm = 1;
} else {
	$sendreasonpm = 0;
}

$_GET['handlekey'] = 'mods';


if(preg_match('/^\w+$/', $_GET['action']) && file_exists($topicadminfile = libfile('topicadmin/'.$_GET['action'], 'include'))) {
	require_once $topicadminfile;
} else {
	showmessage('undefined_action', NULL);
}

if($resultarray) {

	if($resultarray['modtids']) {
		updatemodlog($resultarray['modtids'], $modaction, $resultarray['expiration']);
	}

	updatemodworks($modaction, $modpostsnum);
	if(is_array($resultarray['modlog'])) {
		if(isset($resultarray['modlog']['tid'])) {
			modlog($resultarray['modlog'], $modaction);
		} else {
			foreach($resultarray['modlog'] as $thread) {
				modlog($thread, $modaction);
			}
		}
	}

	if($resultarray['reasonpm']) {
		$modactioncode = lang('forum/modaction');
		$modaction = $modactioncode[$modaction];
		foreach($resultarray['reasonpm']['data'] as $var) {
			sendreasonpm($var, $resultarray['reasonpm']['item'], $resultarray['reasonvar'], $resultarray['reasonpm']['notictype']);
		}
	}

	showmessage((isset($resultarray['message']) ? $resultarray['message'] : 'admin_succeed'), $resultarray['redirect']);

}

?>