<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_modcp.php 28867 2012-03-16 02:27:08Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('IN_MODCP', true);

$cpscript = basename($_G['PHP_SELF']);
if(!empty($_G['forum']) && $_G['forum']['status'] == 3) {
	showmessage('group_admin_enter_panel', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
}

$modsession = new discuz_panel(MODCP_PANEL);
if(getgpc('login_panel') && getgpc('cppwd') && submitcheck('submit')) {
	$modsession->dologin($_G[uid], getgpc('cppwd'), true);
}

if(!$modsession->islogin) {
	$_GET['action'] = 'login';
}

if($_GET['action'] == 'logout') {
	$modsession->dologout();
	showmessage('modcp_logout_succeed', 'forum.php');
}

$modforums = $modsession->get('modforums');
$_GET['action'] = empty($_GET['action']) && $_G['fid'] ? 'thread' : $_GET['action'];
$op = getgpc('op');
if($modforums === null) {
	$modforums = array('fids' => '', 'list' => array(), 'recyclebins' => array());
	$comma = '';
	if($_G['adminid'] == 3) {
		foreach(C::t('forum_moderator')->fetch_all_by_uid_forum($_G['uid']) as $tforum) {
			$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
			$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
			$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
		}
	} else {
		$query = C::t('forum_forum')->fetch_all_info_by_fids(0, 1, 0, 0, 0, 1, 1);
		if(!empty($_G['member']['accessmasks'])) {
			$fids = array_keys($query);
			$accesslist = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
			foreach($query as $key => $val) {
				$query[$key]['allowview'] = $accesslist[$key];
			}
		}
		foreach($query as $tforum) {
			$tforum['allowview'] = !isset($tforum['allowview']) ? '' : $tforum['allowview'];
			if($tforum['allowview'] == 1 || ($tforum['allowview'] == 0 && ((!$tforum['viewperm'] && $_G['group']['readaccess']) || ($tforum['viewperm'] && forumperm($tforum['viewperm']))))) {
				$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
				$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
				$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
			}
		}
	}

	$modsession->set('modforums', $modforums, true);
}

$threadclasslist = array();
if($_G['fid'] && in_array($_G['fid'], explode(',', $modforums['fids']))) {
	foreach(C::t('forum_threadclass')->fetch_all_by_fid($_G['fid']) as $tc) {
		$threadclasslist[] = $tc;
	}
}

if($_G['fid'] && $_G['forum']['ismoderator']) {
	dsetcookie('modcpfid', $_G['fid']);
	$forcefid = "&amp;fid=$_G[fid]";
} elseif(!empty($modforums) && count($modforums['list']) == 1) {
	$forcefid = "&amp;fid=$modforums[fids]";
} else {
	$forcefid = '';
}

$script = $modtpl = '';
switch ($_GET['action']) {

	case 'announcement':
		$_G['group']['allowpostannounce'] && $script = 'announcement';
		break;

	case 'member':
		$op == 'edit' && $_G['group']['allowedituser'] && $script = 'member';
		$op == 'ban' && ($_G['group']['allowbanuser'] || $_G['group']['allowbanvisituser']) && $script = 'member';
		$op == 'ipban' && $_G['group']['allowbanip'] && $script = 'member';
		break;

	case 'moderate':
		($op == 'threads' || $op == 'replies') && $_G['group']['allowmodpost'] && $script = 'moderate';
		$op == 'members' && $_G['group']['allowmoduser'] && $script = 'moderate';
		break;

	case 'forum':
		$op == 'editforum' && $_G['group']['alloweditforum'] && $script = 'forum';
		$op == 'recommend' && $_G['group']['allowrecommendthread'] && $script = 'forum';
		break;

	case 'forumaccess':
		$_G['group']['allowedituser'] && $script = 'forumaccess';
		break;

	case 'log':
		$_G['group']['allowviewlog'] && $script = 'log';
		break;

	case 'login':
		$script = $modsession->islogin ? 'home' : 'login';
		break;

	case 'thread':
		$script = 'thread';
		break;

	case 'recyclebin':
		$script = 'recyclebin';
		break;

	case 'recyclebinpost':
		$script = 'recyclebinpost';
		break;

	case 'plugin':
		$script = 'plugin';
		break;

	case 'report':
		$script = 'report';
		break;

	default:
		$_GET['action'] = $script = 'home';
		$modtpl = 'modcp_home';
}

$script = empty($script) ? 'noperm' : $script;
$modtpl = empty($modtpl) ? (!empty($script) ? 'modcp_'.$script : '') : $modtpl;
$modtpl = 'forum/' . $modtpl;
$op = isset($op) ? trim($op) : '';

if($script != 'log') {
	require_once libfile('function/misc');
	$extra = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('cppwd', 'formhash', 'submit', 'addsubmit'));
	$modcplog = array(TIMESTAMP, $_G['username'], $_G['adminid'], $_G['clientip'], $_GET['action'], $op, $_G['fid'], $extra);
	writelog('modcp', implode("\t", clearlogstring($modcplog)));
}

require DISCUZ_ROOT.'./source/include/modcp/modcp_'.$script.'.php';

$reportnum = $modpostnum = $modthreadnum = $modforumnum = 0;
$modforumnum = count($modforums['list']);
$modnum = '';
if($modforumnum) {
	if(!empty($_G['setting']['moddetail'])) {
		if($_G['group']['allowmodpost']) {
			$modnum = C::t('common_moderate')->count_by_idtype_status_fid('tid', 0, explode(',', $modforums['fids']));
			$modnum += C::t('common_moderate')->count_by_idtype_status_fid('pid', 0, explode(',', $modforums['fids']));
		}
		if($_G['group']['allowmoduser']) {
			$modnum += C::t('common_member_validate')->count_by_status(0);
		}
	}
}

switch($_G['adminid']) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}
$notenum = C::t('common_adminnote')->count_by_access(explode(',', $access));

include template('forum/modcp');

function getposttableselect() {
	global $_G;

	loadcache('posttable_info');
	if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
		$posttableselect = '<select name="posttableid" id="posttableid" class="ps">';
		foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
			$posttableselect .= '<option value="'.$posttableid.'"'.($_GET['posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
		}
		$posttableselect .= '</select>';
	} else {
		$posttableselect = '';
	}
	return $posttableselect;
}

?>