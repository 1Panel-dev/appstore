<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ranklist.php 32807 2013-03-13 08:49:49Z zhengqingpeng $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = $_G['page'];
$type = $_GET['type'];

$_G['disabledwidthauto'] = 1;

if(!in_array($type, array('index', 'member', 'thread', 'blog', 'poll', 'picture', 'activity', 'forum', 'group'))) {
	$type = 'index';
}

$ranklist_setting = $_G['setting']['ranklist'];
if(!$ranklist_setting['status']) {
	showmessage('ranklist_status_off');
}

$navtitle = lang('core', 'title_ranklist_'.$type);

if($type != 'index') {
	if(!$ranklist_setting[$type]['available']) {
		showmessage('ranklist_this_status_off');
	}
}

include libfile('misc/ranklist_'.$type, 'include');
function getranklist_thread($num = 20, $view = 'replies', $orderby = 'all') {
	global $_G;
	$dateline = '';
	$timestamp = 0;
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
	}
	$data = array();
	$rank = 0;
	$notfid = $_G['setting']['ranklist']['ignorefid'] ? explode(',', $_G['setting']['ranklist']['ignorefid']) : array();
	foreach(C::t('forum_thread')->fetch_all_rank_thread($timestamp, $notfid, $view, $num) as $thread) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['dateline'] = dgmdate($thread['dateline']);
		$data[] = $thread;
	}
	return $data;
}

function getranklist_poll($num = 20, $view = 'heats', $orderby = 'all') {
	global $_G;
	$dateline = '';
	$timestamp = 0;
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
	}
	$data = array();
	require_once libfile('function/forum');
	$rank = 0;
	$notfid = $_G['setting']['ranklist']['ignorefid'] ? explode(',', $_G['setting']['ranklist']['ignorefid']) : array();
	foreach(C::t('forum_thread')->fetch_all_rank_poll($timestamp, $notfid, $view, $num) as $poll) {
		++$rank;
		$poll['rank'] = $rank;
		$poll['avatar'] = avatar($poll['authorid'], 'small');
		$poll['dateline'] = dgmdate($poll['dateline']);
		$poll['pollpreview'] = explode("\t", trim($poll['pollpreview']));
		$data[] = $poll;
	}
	return $data;
}

function getranklist_activity($num = 20, $view = 'heats', $orderby = 'all') {
	global $_G;
	$dateline = '';
	$timestamp = 0;
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
	}
	$data = array();
	$rank = 0;$attachtables = array();
	$notfid = $_G['setting']['ranklist']['ignorefid'] ? explode(',', $_G['setting']['ranklist']['ignorefid']) : array();
	foreach(C::t('forum_thread')->fetch_all_rank_activity($timestamp, $notfid, $view, $num) as $thread) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['starttimefrom'] = dgmdate($thread['starttimefrom']);
		if($thread['starttimeto']) {
			$thread['starttimeto'] = dgmdate($thread['starttimeto']);
		} else {
			$thread['starttimeto'] = '';
		}
		if($thread['expiration'] && TIMESTAMP > $thread['expiration']) {
			$thread['has_expiration'] = true;
		} else {
			$thread['has_expiration'] = false;
		}
		$data[$thread['tid']] = $thread;
		$attachtables[getattachtableid($thread['tid'])][] = $thread['aid'];
	}
	foreach($attachtables as $attachtable => $aids) {
		$attachs = C::t('forum_attachment_n')->fetch_all($attachtable, $aids);
		foreach($attachs as $attach) {
			$attach['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
			$data[$attach['tid']] = array_merge($data[$attach['tid']], $attach);
		}
	}
	return $data;
}

function getranklist_picture($num = 20, $view = 'hot', $orderby = 'all') {

	if($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = 'p.'.DB::field('dateline', $timestamp, '>=');
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = 'p.'.DB::field('dateline', $timestamp, '>=');
	} elseif($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = 'p.'.DB::field('dateline', $timestamp, '>=');
	}

	$data = array();
	$query = C::t('home_pic')->fetch_all_by_sql($dateline, 'p.'.DB::order($view, 'DESC'), 0, $num);

	require_once libfile('function/home');
	$rank = 0;
	foreach($query as $value) {
		++$rank;
		$picture = array('picid' => $value['picid'], 'uid' => $value['uid'], 'username' => $value['username'], 'title' => $value['title'], 'filepath' => $value['filepath'], 'thumb' => $value['thumb'], 'remote' => $value['remote'], 'hot' => $value['hot'], 'sharetimes' => $value['sharetimes'], 'click1' => $value['click1'], 'click2' => $value['click2'], 'click3' => $value['click3'], 'click4' => $value['click4'], 'click5' => $value['click5'], 'click6' => $value['click6'], 'click7' => $value['click7'], 'click8' => $value['click8'], 'albumid' => $value['albumid'], 'albumname' => $value['albumname'], 'friend' => $value['friend']);
		$picture['rank'] = $rank;
		$picture['url'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', $picture['thumb'], $picture['remote']) : STATICURL.'image/common/nopublish.gif';;
		$picture['origurl'] = pic_get($picture['filepath'], 'album', 0, $picture['remote']);
		$data[] = $picture;
	}
	return $data;
}

function getranklist_pictures_index($num = 20, $dateline = 0, $orderby = 'hot DESC') {
	$picturelist = array();
	$query = C::t('home_pic')->fetch_all_by_sql('p.hot>3', 'p.dateline DESC', 0, $num);
	require_once libfile('function/home');
	foreach($query as $value) {
		$picture = array('picid' => $value['picid'], 'uid' => $value['uid'], 'username' => $value['username'], 'title' => $value['title'], 'filepath' => $value['filepath'], 'thumb' => $value['thumb'], 'remote' => $value['remote'], 'albumid' => $value['albumid'], 'albumname' => $value['albumname'], 'friend' => $value['friend']);
		$picture['url'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', $picture['thumb'], $picture['remote']) : STATICURL.'image/common/nopublish.gif';;
		$picture['origurl'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', 0, $picture['remote']) : STATICURL.'image/common/nopublish.gif';
		$picturelist[] = $picture;
	}
	return $picturelist;
}

function getranklist_members($offset = 0, $limit = 20) {
	require_once libfile('function/forum');
	$members = array();
	$topusers = C::t('home_show')->fetch_all_by_unitprice($offset, $limit, true);
	foreach($topusers as $member) {
		$member['avatar'] = avatar($member['uid'], 'small');
		$member['note'] = htmlspecialchars(dhtmlspecialchars($member['note']));
		$members[] = $member;
	}
	return $members;
}

function getranklist_girls($offset = 0, $limit = 20, $orderby = 'ORDER BY s.unitprice DESC, s.credit DESC') {
	return C::t('common_member')->fetch_all_girls_for_ranklist($offset, $limit, $orderby);
}

function getranklist_blog($num = 20, $view = 'hot', $orderby = 'all') {
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
	}

	$data = array();
	$data_blog = C::t('home_blog')->range(0, $num, 'DESC', $view, 0, 0, null, $timestamp);
	$blogids = array_keys($data_blog);
	$data_blogfield = C::t('home_blogfield')->fetch_all($blogids);

	require_once libfile('function/forum');
	require_once libfile('function/post');
	$rank = 0;
	foreach($data_blog as $curblogid => $blog) {
		$blog = array_merge($blog, (array)$data_blogfield[$curblogid]);
		++$rank;
		$blog['rank'] = $rank;
		$blog['dateline'] = dgmdate($blog['dateline']);
		$blog['avatar'] = avatar($blog['uid'], 'small');
		$blog['message'] = preg_replace('/<([^>]*?)>/', '', $blog['message']);
		$blog['message'] = messagecutstr($blog['message'], 140);
		$data[] = $blog;
	}
	return $data;
}

function getranklist_forum($num = 20, $view = 'threads') {
	global $_G;

	$data = array();
	$timelimit = 0;
	if($view == 'posts') {
		$key = 'posts';
	} elseif($view == 'today') {
		$key = 'todayposts';
	} else {
		$key = 'threads';
	}
	$query = C::t('forum_forum')->fetch_all_for_ranklist(1, 0, $key, 0, $num, explode(',', $_G['setting']['ranklist']['ignorefid']));
	$i = 1;
	foreach($query as $row) {
		$result = array('fid' => $row['fid'], 'name' => $row['name']);
		$result['posts'] = $row[$key];
		$result['rank'] = $i;
		$data[] = $result;
		$i++;
	}

	return $data;

}

function getranklist_group($num = 20, $view = 'threads') {
	global $_G;

	$timestamp = TIMESTAMP;
	$data = array();
	$timelimit = 0;

	if($view == 'posts') {
		$key = 'posts';
	} elseif($view == 'today'){
		$key = 'todayposts';
	} elseif($view == 'credit'){
		$key = 'commoncredits';
	} elseif($view == 'member'){
		$key = 'membernum';
	} else {
		$key = 'threads';
	}
	$query = C::t('forum_forum')->fetch_all_for_ranklist(3, 'sub', $key, 0, $num, explode(',', $_G['setting']['ranklist']['ignorefid']));
	$i = 1;
	foreach($query as $row) {
		$result = array('fid' => $row['fid'], 'name' => $row['name']);
		$result[$key] = $result['posts'] = $row[$key];
		$result['rank'] = $i;
		$data[] = $result;
		$i++;
	}

	return $data;

}

function getranklist_member($num = 20, $view = 'credit', $orderby = 'all') {
	$data = array();
	$functionname = 'getranklist_member_'.$view;
	$data = $functionname($num, $orderby);
	return $data;
}

function getranklist_member_credit($num, $orderby) {
	return C::t('common_member')->fetch_all_order_by_credit_for_ranklist($num, $orderby);
}

function getranklist_member_friendnum($num) {
	return C::t('common_member')->fetch_all_order_by_friendnum_for_ranklist($num);

}

function getranklist_member_invite($num, $orderby) {
	global $_G;

	if($orderby == 'thisweek') {
		$dateline = TIMESTAMP - 604800;
	} elseif($orderby == 'thismonth') {
		$dateline = TIMESTAMP - 2592000;
	} elseif($orderby == 'today') {
		$dateline = TIMESTAMP - 86400;
	}

	$invite = $invitearray = $inviteuidarray = $invitefieldarray = array();
	foreach(C::t('common_invite')->fetch_all_invitenum_group_by_uid($dateline) as $result) {
		$invitearray[] = $result;
		$inviteuidarray[] = $result['uid'];
	}

	$invitememberfield = C::t('common_member')->fetch_all($inviteuidarray, false, 0);
	if($invitearray) {
		foreach($invitearray as $key => $var) {
			$invite[] = $var;
			$invite[$key]['username'] = $invitememberfield[$var['uid']]['username'];
			$invite[$key]['videophotostatus'] = $invitememberfield[$var['uid']]['videophotostatus'];
			$invite[$key]['groupid'] = $invitememberfield[$var['uid']]['groupid'];
		}
	}
	return $invite;

}

function getranklist_member_onlinetime($num, $orderby) {
	global $_G;

	if($orderby == 'thismonth') {
		$orderby = 'thismonth';
		$online = 'thismonth AS onlinetime';
	} elseif($orderby == 'all') {
		$orderby = 'total';
		$online = 'total AS onlinetime';
	}

	$onlinetime = $onlinetimearray = $onlinetimeuidarray = $onlinetimefieldarray = array();

	$onlinetimearray = C::t('common_onlinetime')->range_by_field(0, $num, $orderby, 'DESC');
	$onlinetimeuidarray = array_keys($onlinetimearray);
	$onlinetimefieldarray = C::t('common_member')->fetch_all($onlinetimeuidarray, false, 0);
	if($onlinetimearray) {
		foreach($onlinetimearray as $key => $var) {
			$var['onlinetime'] = $var[$orderby];
			$var['username'] = $onlinetimefieldarray[$var['uid']]['username'];
			$var['videophotostatus'] = $onlinetimefieldarray[$var['uid']]['videophotostatus'];
			$var['groupid'] = $onlinetimefieldarray[$var['uid']]['groupid'];
			$onlinetime[$key] = $var;
		}
	}
	return $onlinetime;

}

function getranklist_member_blog($num) {
	global $_G;

	$blogs = array();
	$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,c.blogs FROM ".DB::table('common_member').
			" m LEFT JOIN ".DB::table('common_member_count')." c ON m.uid=c.uid WHERE c.blogs>0 ORDER BY blogs DESC LIMIT 0, $num";

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$blogs[] = $result;
	}

	return $blogs;

}


function getranklist_member_gender($gender, $num = 20) {
	global $_G;

	$num = intval($num);
	$num = $num ? $num : 20;
	$users = array();
	$query = DB::query("SELECT c.uid, c.views FROM ".DB::table('common_member_count')." c
			LEFT JOIN ".DB::table('common_member_profile')." p ON c.uid=p.uid
			WHERE c.views>0 AND p.gender = '$gender' ORDER BY c.views DESC LIMIT 0, $num");
	while($user = DB::fetch($query)) {
		$users[$user['uid']] = $user;
	}
	$uids = array_keys($users);
	if($uids) {
		foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $value) {
			$users[$uid] = array_merge($users[$uid], $value);
		}
	}
	return $users;

}

function getranklist_member_beauty($num = 20) {
	return getranklist_member_gender(2, $num);
}

function getranklist_member_handsome($num = 20) {
	return getranklist_member_gender(1, $num);
}

function getranklist_member_post($num, $orderby) {
	global $_G;

	$timestamp = TIMESTAMP;
	$posts = array();
	$timelimit = 0;
	if($orderby == 'digestposts') {
		$sql = "SELECT m.username, m.uid, mc.digestposts AS posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE mc.digestposts>0
		ORDER BY mc.digestposts DESC LIMIT 0, $num";
	} elseif($orderby == 'thismonth') {
		$timelimit = $timestamp-86400*30;
	} elseif($orderby == 'today') {
		$timelimit = $timestamp-86400;
	} else {
		$sql = "SELECT m.username, m.uid, mc.posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE	mc.posts>0
		ORDER BY mc.posts DESC LIMIT 0, $num";
	}
	if($timelimit) {
		$posts = C::t('forum_post')->fetch_all_top_post_author(0, $timelimit, $num);

	} else {
		$query = DB::query($sql);
		while($result = DB::fetch($query)) {
			$posts[] = $result;
		}
	}

	return $posts;

}

function getranklistdata($type, $view = '', $orderby = 'all') {
	if (!function_exists('getranklist_'.$type)) {
	    return array();
	}
	global $_G;
	$cache_time = $_G['setting']['ranklist'][$type]['cache_time'];
	$cache_num =  $_G['setting']['ranklist'][$type]['show_num'];
	if($cache_time <= 0 ) {
		$cache_time = 5;
	}
	$cache_time = $cache_time * 3600;
	if($cache_num <= 0 ) {
		$cache_num = 20;
	}

	$ranklistvars = array();
	loadcache('ranklist_'.$type);
	if(!isset($_G['cache']['ranklist_'.$type]) || !is_array($_G['cache']['ranklist_'.$type])) {
		$_G['cache']['ranklist_'.$type] = array();
	}
	if(!isset($_G['cache']['ranklist_'.$type][$view]) || !is_array($_G['cache']['ranklist_'.$type][$view])) {
		$_G['cache']['ranklist_'.$type][$view] = array();
	}
	if(!isset($_G['cache']['ranklist_'.$type][$view][$orderby]) || !is_array($_G['cache']['ranklist_'.$type][$view][$orderby])) {
		$_G['cache']['ranklist_'.$type][$view][$orderby] = array();
	}
	$ranklistvars = & $_G['cache']['ranklist_'.$type][$view][$orderby];

	if(empty($ranklistvars['lastupdated']) || (TIMESTAMP - $ranklistvars['lastupdated'] > $cache_time)) {
		$functionname = 'getranklist_'.$type;

		if(!discuz_process::islocked('ranklist_update', 600)) {
			$ranklistvars = $functionname($cache_num, $view, $orderby);
			$ranklistvars['lastupdated'] = TIMESTAMP;
			$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
			$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
			$_G['cache']['ranklist_'.$type][$view][$orderby] = $ranklistvars;
			savecache('ranklist_'.$type, $_G['cache']['ranklist_'.$type]);
		}
		discuz_process::unlock('ranklist_update');
	}
	$_G['lastupdate'] = $ranklistvars['lastupdate'];
	$_G['nextupdate'] = $ranklistvars['nextupdate'];
	unset($ranklistvars['lastupdated'], $ranklistvars['lastupdate'], $ranklistvars['nextupdate']);
	return $ranklistvars;
}

function getignorefid($pre = '') {
	global $_G;
	$fidsql = '';
	if($_G['setting']['ranklist']['ignorefid']) {
		$fidsql = ' AND '.($pre ? $pre.'.' : '').'fid NOT IN('.dimplode(explode(',', $_G['setting']['ranklist']['ignorefid'])).')';
	}
	return $fidsql;
}
?>