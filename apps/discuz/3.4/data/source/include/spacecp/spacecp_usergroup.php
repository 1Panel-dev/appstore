<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_usergroup.php 34024 2013-09-22 08:55:00Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['inajax'] && $_GET['showextgroups']) {
	require_once libfile('function/forumlist');
	loadcache('usergroups');
	$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
	$group = array();
	if($_G['uid'] && $_G['group']['grouptype'] == 'member' && $_G['group']['groupcreditslower'] != 999999999) {
		$group['upgradecredit'] = $_G['group']['groupcreditslower'] - $_G['member']['credits'];
		$group['upgradeprogress'] = 100 - ceil($group['upgradecredit'] / ($_G['group']['groupcreditslower'] - $_G['group']['groupcreditshigher']) * 100);
		$group['upgradeprogress'] = max($group['upgradeprogress'], 2);
	}
	include template('forum/viewthread_profile_node');
	include template('common/extgroups');
	exit;
}

$do = in_array($_GET['do'], array('buy', 'exit', 'switch', 'list', 'forum', 'expiry')) ? trim($_GET['do']) : 'usergroup';

$extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
space_merge($space, 'count');
$credits = $space['credits'];
$forumselect = '';
$activeus = array();
$activeus[$do] = ' class="a"';

if(in_array($do, array('buy', 'exit'))) {

	if($_G['groupid'] == 4 && $_G['member']['groupexpiry'] > 0 && $_G['member']['groupexpiry'] > TIMESTAMP) {
		showmessage('usergroup_switch_not_allow');
	}

	$groupid = intval($_GET['groupid']);

	$group = C::t('common_usergroup')->fetch($groupid);
	if($group['type'] != 'special' || $group['system'] == 'private' || $group['radminid'] != 0) {
		$group = null;
	}
	if(empty($group)) {
		showmessage('usergroup_not_found');
	}
	$join = $do == 'buy' ? 1 : 0;
	$group['dailyprice'] = $group['minspan'] = 0;

	if($group['system'] != 'private') {
		list($group['dailyprice'], $group['minspan']) = explode("\t", $group['system']);
		if($group['dailyprice'] > -1 && $group['minspan'] == 0) {
			 $group['minspan'] = 1;
		}
	}
	$creditstrans = $_G['setting']['creditstrans'];
	if(!isset($_G['setting']['creditstrans'])) {
		showmessage('credits_transaction_disabled');
	}

	if(!submitcheck('buysubmit')) {
		$usermoney = $space['extcredits'.$creditstrans];
		$usermaxdays = $group['dailyprice'] > 0 ? intval($usermoney / $group['dailyprice']) : 0;
		$group['minamount'] = $group['dailyprice'] * $group['minspan'];
	} else {
		$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
		$groupterms = dunserialize($memberfieldforum['groupterms']);
		unset($memberfieldforum);
		require_once libfile('function/forum');
		if($join) {
			$extgroupidsarray = array();
			foreach(array_unique(array_merge($extgroupids, array($groupid))) as $extgroupid) {
				if($extgroupid) {
					$extgroupidsarray[] = $extgroupid;
				}
			}
			$extgroupidsnew = implode("\t", $extgroupidsarray);
			if($group['dailyprice']) {
				if(($days = intval($_GET['days'])) < $group['minspan']) {
					showmessage('usergroups_span_invalid', '', array('minspan' => $group['minspan']));
				}

				if($space['extcredits'.$creditstrans] - ($amount = $days * $group['dailyprice']) < ($minbalance = 0)) {
					showmessage('credits_balance_insufficient', '', array('title'=> $_G['setting']['extcredits'][$creditstrans]['title'],'minbalance' => ($minbalance + $amount)));
				}

				$groupterms['ext'][$groupid] = ($groupterms['ext'][$groupid] > TIMESTAMP ? $groupterms['ext'][$groupid] : TIMESTAMP) + $days * 86400;

				$groupexpirynew = groupexpiry($groupterms);

				C::t('common_member')->update($_G['uid'], array('groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
				updatemembercount($_G['uid'], array($creditstrans => "-$amount"), true, 'UGP', $extgroupidsnew);

				C::t('common_member_field_forum')->update($_G['uid'], array('groupterms' => serialize($groupterms)));

			} else {
				C::t('common_member')->update($_G['uid'], array('extgroupids' => $extgroupidsnew));
			}

			showmessage('usergroups_join_succeed', "home.php?mod=spacecp&ac=usergroup".($_GET['gid'] ? "&gid=$_GET[gid]" : '&do=list'), array('group' => $group['grouptitle']), array('showdialog' => 3, 'showmsg' => true, 'locationtime' => true));

		} else {

			if($groupid != $_G['groupid']) {
				if(isset($groupterms['ext'][$groupid])) {
					unset($groupterms['ext'][$groupid]);
				}
				$groupexpirynew = groupexpiry($groupterms);
				C::t('common_member_field_forum')->update($_G['uid'], array('groupterms' => serialize($groupterms)));

			} else {
				$groupexpirynew = 'groupexpiry';
			}

			$extgroupidsarray = array();
			foreach($extgroupids as $extgroupid) {
				if($extgroupid && $extgroupid != $groupid) {
					$extgroupidsarray[] = $extgroupid;
				}
			}
			$extgroupidsnew = implode("\t", array_unique($extgroupidsarray));
			C::t('common_member')->update($_G['uid'], array('groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));

			showmessage('usergroups_exit_succeed', "home.php?mod=spacecp&ac=usergroup".($_GET['gid'] ? "&gid=$_GET[gid]" : '&do=list'), array('group' => $group['grouptitle']), array('showdialog' => 3, 'showmsg' => true, 'locationtime' => true));

		}

	}

} elseif($do == 'switch') {

	$groupid = intval($_GET['groupid']);
	if(!in_array($groupid, $extgroupids)) {
		showmessage('usergroup_not_found');
	}
	if($_G['groupid'] == 4 && $_G['member']['groupexpiry'] > 0 && $_G['member']['groupexpiry'] > TIMESTAMP) {
		showmessage('usergroup_switch_not_allow');
	}
	$group = C::t('common_usergroup')->fetch($groupid);
	if(!$group['allowvisit']) {
		showmessage("usergroup_switch_not_allowvisit");
	}
	if(submitcheck('groupsubmit')) {
		$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
		$groupterms = dunserialize($memberfieldforum['groupterms']);
		unset($memberfieldforum);
		$extgroupidsnew = $_G['groupid'];
		$groupexpirynew = $groupterms['ext'][$groupid];
		foreach($extgroupids as $extgroupid) {
			if($extgroupid && $extgroupid != $groupid) {
				$extgroupidsnew .= "\t".$extgroupid;
			}
		}
		if($_G['adminid'] > 0 && $group['radminid'] > 0) {
			$newadminid = $_G['adminid'] < $group['radminid'] ? $_G['adminid'] : $group['radminid'];
		} elseif($_G['adminid'] > 0) {
			$newadminid = $_G['adminid'];
		} else {
			$newadminid = $group['radminid'];
		}

		C::t('common_member')->update($_G['uid'], array('groupid' => $groupid, 'adminid' => $newadminid, 'groupexpiry' => $groupexpirynew, 'extgroupids' => $extgroupidsnew));
		showmessage('usergroups_switch_succeed', "home.php?mod=spacecp&ac=usergroup".($_GET['gid'] ? "&gid=$_GET[gid]" : '&do=list'), array('group' => $group['grouptitle']), array('showdialog' => 3, 'showmsg' => true, 'locationtime' => true));
	}

} elseif($do == 'forum') {

	if($_G['setting']['verify']['enabled']) {
		$myverify= array();
		getuserprofile('verify1');
		for($i = 1; $i <= 6; $i++) {
			if($_G['member']['verify'.$i] == 1) {
				$myverify[] = $i;
			}
		}
		$ar = array(1, 2, 3, 4, 5);
	}
	$language = lang('forum/misc');
	$permlang = $language;
	loadcache('forums');
	$fids = array_keys($_G['cache']['forums']);
	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm', 'postimageperm');
	$defaultperm = array(
		array('viewperm' => 1, 'postperm' => 0, 'replyperm' => 0, 'getattachperm' => 1, 'postattachperm' => 0, 'postimageperm' => 0),
		array('viewperm' => 1, 'postperm' => 1, 'replyperm' => 1, 'getattachperm' => 1, 'postattachperm' => 1, 'postimageperm' => 1),
	);
	if($_G['setting']['verify']['enabled']) {
		for($i = 1; $i <= 6; $i++) {
			if($_G['setting']['verify'][$i]['available']) {
				$verifyicon[$i] = !empty($_G['setting']['verify'][$i]['icon']) ? '<img src="'.$_G['setting']['verify'][$i]['icon'].'" alt="'.$_G['setting']['verify'][$i]['title'].'" class="vm" title="'.$_G['setting']['verify'][$i]['title'].'" />' : $_G['setting']['verify'][$i]['title'];
			}
		}
	}
	$forumperm = $verifyperm = $myverifyperm = array();
	$query = C::t('forum_forum')->fetch_all_info_by_fids($fids);
	foreach($query as $forum) {
		foreach($perms as $perm) {
			if($forum[$perm]) {
				if($_G['setting']['verify']['enabled']) {
					for($i = 1; $i <= 6; $i++) {
						$verifyperm[$forum['fid']][$perm] .= preg_match("/(^|\t)(v".$i.")(\t|$)/", $forum[$perm]) ? $verifyicon[$i] : '';
						$includePerm = preg_match("/(^|\t)(v".$i.")(\t|$)/", $forum[$perm]) ? $verifyicon[$i] : '';
						if(in_array($i, $myverify) && $includePerm) {
							$myverifyperm[$forum['fid']][$perm] = 1;
						}
					}
				}
				$forumperm[$forum['fid']][$perm] = preg_match("/(^|\t)(".$_G['groupid'].")(\t|$)/", $forum[$perm]) ? 1 : 0;
			} else {
				$forumperm[$forum['fid']][$perm] = $defaultperm[$_G['groupid'] != 7 ? 1 : 0][$perm];
			}
		}
	}

} elseif($do == 'list' || $do == 'expiry') {

	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$expgrouparray = $expirylist = $termsarray = array();

	if(!empty($groupterms['ext']) && is_array($groupterms['ext'])) {
		$termsarray = $groupterms['ext'];
	}
	if(!empty($groupterms['main']['time']) && (empty($termsarray[$_G['groupid']]) || $termsarray[$_G['groupid']] > $groupterm['main']['time'])) {
		$termsarray[$_G['groupid']] = $groupterms['main']['time'];
	}

	foreach($termsarray as $expgroupid => $expiry) {
		if($expiry <= TIMESTAMP) {
			$expgrouparray[] = $expgroupid;
		}
	}

	if(!empty($groupterms['ext'])) {
		foreach($groupterms['ext'] as $extgroupid => $time) {
			$expirylist[$extgroupid] = array('time' => dgmdate($time, 'd'), 'type' => 'ext', 'noswitch' => $time < TIMESTAMP);
		}
	}

	if(!empty($groupterms['main'])) {
		$expirylist[$_G['groupid']] = array('time' => dgmdate($groupterms['main']['time'], 'd'), 'type' => 'main');
	}

	$groupids = array();
	foreach($_G['cache']['usergroups'] as $groupid => $usergroup) {
		if(!empty($usergroup['pubtype'])) {
			$groupids[] = $groupid;
		}
	}
	$expiryids = array_keys($expirylist);
	if(!$expiryids && $_G['member']['groupexpiry']) {
		C::t('common_member')->update($_G['uid'], array('groupexpiry' => 0));
	}
	$groupids = array_merge($extgroupids, $expiryids, $groupids);
	$usermoney = $space['extcredits'.$_G['setting']['creditstrans']];
	if($groupids) {
		foreach(C::t('common_usergroup')->fetch_all($groupids) as $group) {
			$isexp = in_array($group['groupid'], $expgrouparray);
			if($_G['cache']['usergroups'][$group['groupid']]['pubtype'] == 'buy') {
				list($dailyprice) = explode("\t", $group['system']);
				$expirylist[$group['groupid']]['dailyprice'] = $dailyprice;
				$expirylist[$group['groupid']]['usermaxdays'] = $dailyprice > 0 ? round($usermoney / $dailyprice) : 0;
			} else {
				$expirylist[$group['groupid']]['usermaxdays'] = 0;
			}
			$expirylist[$group['groupid']]['maingroup'] = $group['type'] != 'special' || $group['system'] == 'private' || $group['radminid'] > 0;
			$expirylist[$group['groupid']]['grouptitle'] = $isexp ? '<s>'.$group['grouptitle'].'</s>' : $group['grouptitle'];
		}
	}

} else {

	$language = lang('forum/misc');
	require_once libfile('function/forumlist');
	$permlang = $language;
	unset($language);
	$maingroup = $_G['group'];
	$ptype = in_array($_GET['ptype'], array(0, 1, 2)) ? intval($_GET['ptype']) : 0;
	foreach($_G['cache']['usergroups'] as $gid => $value) {
		$cachekey[] = 'usergroup_'.$gid;
	}
	loadcache($cachekey);
	$_G['group'] = $maingroup;
	$sidegroup = $usergroups = $activegs = array();
	$nextupgradeid = $nextexist = 0;
	$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
	$groupterms = dunserialize($memberfieldforum['groupterms']);
	unset($memberfieldforum);
	$switchmaingroup = $_G['group']['grouppublic'] || isset($groupterms['ext']) ? 1 : 0;
	foreach($_G['cache']['usergroups'] as $gid => $group) {
		$group['grouptitle'] = strip_tags($group['grouptitle']);
		if($group['type'] == 'special') {
			$type = $_G['cache']['usergroup_'.$gid]['radminid'] ? 'admin' : 'user';
		} elseif($group['type'] == 'system') {
			$type = $_G['cache']['usergroup_'.$gid]['radminid'] ? 'admin' : 'user';
		} elseif($group['type'] == 'member') {
			$type = 'upgrade';
		}
		if($nextupgradeid && $group['type'] == 'member') {
			$_GET['gid'] = $gid;
			$nextupgradeid = 0;
		}
		$g = '<a href="home.php?mod=spacecp&ac=usergroup&gid='.$gid.'"'.(!empty($_GET['gid']) && $_GET['gid'] == $gid ? ' class="xi1"' : '').'>'.$group['grouptitle'].'</a>';
		if(in_array($gid, $extgroupids)) {
			$usergroups['my'] .= $g;
		}
		$usergroups[$type] .= $g;
		if(!empty($_GET['gid']) && $_GET['gid'] == $gid) {
			$switchtype = $type;
			if(!empty($_GET['gid'])) {
				$activegs[$switchtype] = ' a';
			}
			$currentgrouptitle = $group['grouptitle'];
			$sidegroup = $_G['cache']['usergroup_'.$gid];
			if($_G['cache']['usergroup_'.$gid]['radminid']) {
				$admingids[] = $gid;
			}
		} elseif(empty($_GET['gid']) && $_G['groupid'] == $gid && $group['type'] == 'member') {
			$nextupgradeid = 1;
		}
	}
	$usergroups['my'] = '<a href="home.php?mod=spacecp&ac=usergroup">'.$maingroup['grouptitle'].'</a>'.$usergroups['my'];
	if($activegs == array()) {
		$activegs['my'] = ' a';
	}

	$bperms = array('allowvisit','readaccess','allowinvisible','allowsearch','allowcstatus','disablepostctrl', 'allowsendpm', 'allowfriend', 'allowstatdata', 'allowmyop');
	if($_G['setting']['portalstatus']) {
		$bperms[] = 'allowpostarticle';
	}
	$pperms = array('allowpost','allowreply','allowpostpoll','allowvote','allowpostreward','allowpostactivity','allowpostdebate','allowposttrade','allowat', 'allowreplycredit', 'allowposttag', 'allowcreatecollection','maxsigsize','allowsigbbcode','allowsigimgcode','allowrecommend','raterange','allowcommentpost','allowmediacode');
	$aperms = array('allowgetattach', 'allowgetimage', 'allowpostattach', 'allowpostimage', 'allowsetattachperm', 'maxattachsize', 'maxsizeperday', 'maxattachnum', 'attachextensions');
	$sperms = array('allowpoke', 'allowclick', 'allowcomment', 'maxspacesize', 'maximagesize');
	if(helper_access::check_module('blog')) {$sperms[] = 'allowblog';}
	if(helper_access::check_module('album')) {$sperms[] = 'allowupload';}
	if(helper_access::check_module('share')) {$sperms[] = 'allowshare';}
	if(helper_access::check_module('doing')) {$sperms[] = 'allowdoing';}
	$allperms = array();
	$allkey = array_merge($bperms, $pperms, $aperms, $sperms);
	if($sidegroup) {
		foreach($allkey as $pkey) {
			if(in_array($pkey, array('maxattachsize', 'maxsizeperday', 'maxspacesize', 'maximagesize'))) {
				$sidegroup[$pkey] = $sidegroup[$pkey] ? sizecount($sidegroup[$pkey]) : 0;
			}
			$allperms[$pkey][$sidegroup['groupid']] = $sidegroup[$pkey];
		}
	}

	foreach($maingroup as $pkey => $v) {
		if(in_array($pkey, array('maxattachsize', 'maxsizeperday', 'maxspacesize', 'maximagesize'))) {
			$maingroup[$pkey] = $maingroup[$pkey] ? sizecount($maingroup[$pkey]) : 0;
		}
	}

	$publicgroup = array();
	$extgroupids[] = $_G['groupid'];
	foreach(C::t('common_usergroup')->fetch_all_switchable(array_unique($extgroupids)) as $group) {
		$group['allowsetmain'] = in_array($group['groupid'], $extgroupids);
		$publicgroup[$group['groupid']] = $group;
	}
	$group = $group[count($group)];
	$_GET['perms'] = 'member';
	if($sidegroup) {
		$group = $sidegroup;
	}
}

include_once template("home/spacecp_usergroup");

?>