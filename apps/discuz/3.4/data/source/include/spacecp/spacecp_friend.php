<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_friend.php 36272 2016-11-23 06:35:37Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/friend');

$op = empty($_GET['op'])?'':$_GET['op'];
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$_GET['from'] = preg_match('/^\w+$/', $_GET['from']) ? $_GET['from'] : '';

$space['key'] = space_key($space['uid']);

$actives = array($op=>' class="a"');

if($op == 'add') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege_addfriend');
	}

	if($uid == $_G['uid']) {
		showmessage('friend_self_error');
	}

	if(friend_check($uid)) {
		showmessage('you_have_friends');
	}

	$tospace = getuserbyuid($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	$groups = friend_group_list();

	space_merge($space, 'count');
	space_merge($space, 'field_home');

	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
		if($_G['setting']['magics']['friendnum']) {
			showmessage('enough_of_the_number_of_friends_with_magic');
		} else {
			showmessage('enough_of_the_number_of_friends');
		}
	}

	if(friend_request_check($uid)) {

		if(submitcheck('add2submit')) {

			$_POST['gid'] = intval($_POST['gid']);
			friend_add($uid, $_POST['gid']);

			if(ckprivacy('friend', 'feed')) {
				require_once libfile('function/feed');
				feed_add('friend', 'feed_friend_title', array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">$tospace[username]</a>"));
			}

			notification_add($uid, 'friend', 'friend_add');
			showmessage('friends_add', dreferer(), array('username' => $tospace['username'], 'uid'=>$uid, 'from' => $_GET['from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}

		$op = 'add2';
		$groupselect = empty($space['privacy']['groupname']) ? array(1 => ' checked') : array();
		$navtitle = lang('core', 'title_friend_add');
		include template('home/spacecp_friend');
		exit();

	} else {

		if(C::t('home_friend_request')->count_by_uid_fuid($uid, $_G['uid'])) {
			showmessage('waiting_for_the_other_test', '', array(), array('alert' => 'info'));
		}

		if(submitcheck('addsubmit')) {

			$_POST['gid'] = intval($_POST['gid']);
			$_POST['note'] = censor(htmlspecialchars(cutstr($_POST['note'], strtolower(CHARSET) == 'utf-8' ? 30 : 20, '')));
			friend_add($uid, $_POST['gid'], $_POST['note']);

			$note = array(
				'uid' => $_G['uid'],
				'url' => 'home.php?mod=spacecp&ac=friend&op=add&uid='.$_G['uid'].'&from=notice',
				'from_id' => $_G['uid'],
				'from_idtype' => 'friendrequest',
				'note' => !empty($_POST['note']) ? lang('spacecp', 'friend_request_note', array('note' => $_POST['note'])) : ''
			);

			notification_add($uid, 'friend', 'friend_request', $note);

			require_once libfile('function/mail');
			$values = array(
				'username' => $tospace['username'],
				'url' => $_G['setting']['securesiteurl'].'home.php?mod=spacecp&ac=friend&amp;op=request'
			);
			sendmail_touser($uid, lang('spacecp', 'friend_subject', $values), '', 'friend_add');
			showmessage('request_has_been_sent', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

		} else {
			include_once template('home/spacecp_friend');
			exit();
		}
	}

} elseif($op == 'ignore') {

	if($uid) {
		if(submitcheck('friendsubmit')) {

			if(friend_check($uid)) {
				friend_delete($uid);
			} else {
				friend_request_delete($uid);
			}
			showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request', array('uid'=>$uid, 'from' => $_GET['from']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 0));
		}
	} elseif($_GET['key'] == $space['key']) {
		$count = C::t('home_friend_request')->count_by_uid($_G['uid']);
		if($count) {
			C::t('home_friend_request')->delete_by_uid($_G['uid']);

			dsetcookie('promptstate_'.$_G['uid'], $space['newprompt'], 31536000);
		}
		showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request');
	}

} elseif($op == 'addconfirm') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege_addfriend');
	}
	if($_GET['key'] == $space['key']) {

		$maxfriendnum = checkperm('maxfriendnum');
		space_merge($space, 'field_home');
		space_merge($space, 'count');

		if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
			if($_G['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}

		if($value = C::t('home_friend_request')->fetch_by_uid($space['uid'])) {
			friend_add($value['fuid']);
			showmessage('friend_addconfirm_next', 'home.php?mod=spacecp&ac=friend&op=addconfirm&key='.$space['key'], array('username' => $value['fusername']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

	showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request&quickforward=1');

} elseif($op == 'find') {

	$maxnum = 36;

	$recommenduser = $myfuids = $fuids =array();

	$i = 0;
	$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 0, true);
	foreach($query as $value) {
		if($i < 100) {
			$fuids[$value['fuid']] = $value['fuid'];
		}
		$myfuids[$value['fuid']] = $value['fuid'];
		$i++;
	}
	$myfuids[$space['uid']] = $space['uid'];

	foreach(C::t('home_specialuser')->range() as $value) {
		$recommenduser[$value['uid']] = $value;
	}

	$i = 0;
	$nearlist = array();
	foreach(C::app()->session->fetch_all_by_ip($_G['clientip'], 200) as $value) {
		if($value['uid'] && empty($myfuids[$value['uid']])) {
			$nearlist[$value['uid']] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}

	$i = 0;
	$friendlist = array();
	if($fuids) {
		$query = C::t('home_friend')->fetch_all_by_uid($fuids, 0, 200);
		$fuids[$space['uid']] = $space['uid'];
		foreach($query as $value) {
			$value['fuid'] = $value['uid'];
			$value['fusername'] = $value['username'];
			if(empty($myfuids[$value['uid']])) {
				$friendlist[$value['uid']] = $value;
				$i++;
				if($i>=$maxnum) break;
			}
		}
	}

	$i = 0;
	$onlinelist = array();
	foreach(C::app()->session->fetch_member(1, 2, 200) as $value) {
		if(empty($myfuids[$value['uid']]) && !isset($onlinelist[$value['uid']])) {
			$onlinelist[$value['uid']] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}
	$navtitle = lang('core', 'title_people_might_know');

} elseif($op == 'changegroup') {

	if(submitcheck('changegroupsubmit')) {
		C::t('home_friend')->update_by_uid_fuid($_G['uid'], $uid, array('gid'=>intval($_POST['group'])));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer(), array('gid'=>intval($_POST['group'])), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

	$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $uid);
	if(!$friend = $query[0]) {
		showmessage('specified_user_is_not_your_friend');
	}
	$groupselect = array($friend['gid'] => ' checked');

	$groups = friend_group_list();


} elseif($op == 'editnote') {

	if(submitcheck('editnotesubmit')) {
		$note = getstr($_POST['note'], 20);
		C::t('home_friend')->update_by_uid_fuid($_G['uid'], $uid, array('note'=>$note));
		showmessage('do_success', dreferer(), array('uid'=>$uid, 'note'=>$note), array('showdialog'=>1, 'msgtype' => 2, 'closetime' => true));
	}

	$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $uid);
	if(!$friend = $query[0]) {
		showmessage('specified_user_is_not_your_friend');
	}


} elseif($op == 'changenum') {

	if(submitcheck('changenumsubmit')) {
		$num = abs(intval($_POST['num']));
		if($num > 9999) $num = 9999;
		C::t('home_friend')->update_by_uid_fuid($_G['uid'], $uid, array('num'=>$num));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer(), array('fuid'=>$uid, 'num'=>$num), array('showmsg' => true, 'timeout' => 3, 'return'=>1));
	}

	$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $uid);
	if(!$friend = $query[0]) {
		showmessage('specified_user_is_not_your_friend');
	}

} elseif($op == 'group') {

	if(submitcheck('groupsubmin')) {
		if(empty($_POST['fuids'])) {
			showmessage('please_correct_choice_groups_friend', dreferer());
		}
		$ids = $_POST['fuids'];
		$groupid = intval($_POST['group']);
		C::t('home_friend')->update_by_uid_fuid($_G['uid'], $ids, array('gid'=>$groupid));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer());
	}

	$perpage = 50;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();
	$multi = $wheresql = '';

	space_merge($space, 'count');

	if($space['friends']) {

		$groups = friend_group_list();

		$theurl = 'home.php?mod=spacecp&ac=friend&op=group';
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}

		$count = C::t('home_friend')->fetch_all_search($space['uid'], $group, '', true);
		if($count) {
			$query = C::t('home_friend')->fetch_all_search($space['uid'], $group, '', false, $start, $perpage, true);
			foreach($query as $value) {
				$value['uid'] = $value['fuid'];
				$value['username'] = $value['fusername'];
				$value['group'] = $groups[$value['gid']];
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}

	$actives = array('group'=>' class="a"');



} elseif($op == 'request') {

	if(submitcheck('requestsubmin')) {
		showmessage('do_success', dreferer());
	}

	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum) {
		$maxfriendnum = $maxfriendnum + $space['addfriend'];
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = $ols = array();

	$count = C::t('home_friend_request')->count_by_uid($space['uid']);
	if($count) {
		$fuids = array();
		foreach(C::t('home_friend_request')->fetch_all_by_uid($space['uid'], $start, $perpage) as $value) {
			$fuids[$value['fuid']] = $value['fuid'];
			$list[$value['fuid']] = $value;
		}
		if (!empty($fuids)) {
			foreach(C::app()->session->fetch_all_by_uid($fuids) as $value) {
				if(!$value['invisible']) {
					$ols[$value['uid']] = 1;
				}
			}
		}
	} else {

		dsetcookie('promptstate_'.$space['uid'], $space['newprompt'], 31536000);

	}

	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=friend&op=request");

	$navtitle = lang('core', 'title_friend_request');

} elseif($op == 'groupname') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupnamesubmit')) {
		$space['privacy']['groupname'][$group] = getstr($_POST['groupname'], 20);
		privacy_update();
		showmessage('do_success', dreferer(), array('gid'=>$group), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}
} elseif($op == 'groupignore') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupignoresubmit')) {
		if(isset($space['privacy']['filter_gid'][$group])) {
			unset($space['privacy']['filter_gid'][$group]);
			$ignore = false;
		} else {
			$space['privacy']['filter_gid'][$group] = $group;
			$ignore = true;
		}
		privacy_update();
		friend_cache($_G['uid']);

		showmessage('do_success', dreferer(), array('group' => $group, 'ignore' => $ignore), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

} elseif($op == 'blacklist') {

	if($_GET['subop'] == 'delete') {
		$_GET['uid'] = intval($_GET['uid']);
		C::t('home_blacklist')->delete_by_uid_buid($space['uid'], $_GET['uid']);
		$count = C::t('home_blacklist')->count_by_uid_buid($space['uid']);
		C::t('common_member_count')->update($_G['uid'], array('blacklist' => $count));
		showmessage('do_success', "home.php?mod=space&uid=$_G[uid]&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

	if(submitcheck('blacklistsubmit')) {
		$_POST['username'] = trim($_POST['username']);
		if(!($tospace = C::t('common_member')->fetch_by_username($_POST['username']))) {
			showmessage('space_does_not_exist');
		}
		if($tospace['uid'] == $space['uid']) {
			showmessage('unable_to_manage_self');
		}

		friend_delete($tospace['uid']);

		C::t('home_blacklist')->insert(array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_G['timestamp']), false, false, true);

		$count = C::t('home_blacklist')->count_by_uid_buid($space['uid']);
		C::t('common_member_count')->update($_G['uid'], array('blacklist' => $count));
		showmessage('do_success', "home.php?mod=space&uid=$_G[uid]&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

} elseif($op == 'rand') {

	$userlist = $randuids = array();
	space_merge($space, 'count');
	if($space['friends']<5) {
		$userlist = C::app()->session->fetch_member(1, 2, 100);
	} else {
		$query = C::t('home_friend')->fetch($_G['uid']);
		foreach($query as $value) {
			$userlist[$value['uid']] = $value['fuid'];
		}
	}
	unset($userlist[$space['uid']]);

	$randuids = sarray_rand($userlist, 1);
	showmessage('do_success', "home.php?mod=space&quickforward=1&uid=".array_pop($randuids));

} elseif ($op == 'getcfriend') {

	$fuid = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);

	$list = array();
	if($fuid) {
		$friend = $friendlist = array();
		$query = C::t('home_friend')->fetch_all_by_uid_common($space['uid'], $fuid);
		foreach($query as $value) {
			$friendlist[$value['uid']][] = $value['fuid'];
			$friend[$value['fuid']] = $value;
		}
		if($friendlist[$_G['uid']] && $friendlist[$fuid]) {
			$cfriend = array_intersect($friendlist[$_G['uid']], $friendlist[$fuid]);
			$i = 0;
			foreach($cfriend as $key => $uid) {
				if(isset($friend[$uid])) {
					$list[] = array('uid' => $friend[$uid]['fuid'], 'username' => $friend[$uid]['fusername']);
					$i++;
					if($i >= 15) break;
				}
			}
		}

	}
} elseif($op == 'getinviteuser') {
	require_once libfile('function/search');
	$perpage = 20;
	$username = empty($_GET['username'])?'':searchkey($_GET['username'], "f.fusername LIKE '{text}%'");
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	$gid = isset($_GET['gid']) ? intval($_GET['gid']) : -1;
	if($page<1) $page = 1;
	$start = ($page-1) * $perpage;
	$json = array();
	$wheresql = '';
	if($gid > -1) {
		$wheresql .= " AND f.gid='$gid'";
	}
	if(!empty($username)) {
		$wheresql .= $username;
	}

	$count = $count_at = $singlenum = 0;
	if($_GET['at'] == 1 && $gid < 0) {
		$count_at = C::t('home_follow')->count_by_uid_username($_G['uid'], $_GET['username']);
		if($count_at) {
			foreach(C::t('home_follow')->fetch_all_by_uid_username($_G['uid'], $_GET['username'], $start, $perpage) as $value) {
				$value['fusername'] = daddslashes($value['fusername']);
				$value['avatar'] = avatar($value['followuid'], 'small', true);
				$singlenum++;
				$json[$value['followuid']] = "$value[followuid]:{'uid':$value[followuid], 'username':'$value[fusername]', 'avatar':'$value[avatar]'}";
			}
			$perpage = $perpage - $singlenum;
			$start = max($start - $count_at, 0);
		}

	}
	if($perpage && $gid != -2) {
		$count = C::t('home_friend')->fetch_all_search($_G['uid'], $gid, $_GET['username'], true);
		if($count) {
			$homefriend = C::t('home_friend')->fetch_all_search($_G['uid'], $gid, $_GET['username'], false, $start, $perpage, true);

			$usrids = array();
			foreach($homefriend as $key=>$usrs) {
				$usrids[$key] = $usrs['fuid'];
			}

			$usernames = C::t('common_member')->fetch_all_username_by_uid($usrids);
			foreach($homefriend as $value) {
				$value['fusername'] = daddslashes($usernames[$value['fuid']]);
				$value['avatar'] = avatar($value['fuid'], 'small', true);
				$singlenum++;
				$json[$value['fuid']] = "$value[fuid]:{'uid':$value[fuid], 'username':'$value[fusername]', 'avatar':'$value[avatar]'}";
			}
		}
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}, 'maxfriendnum':'".($count+$count_at)."', 'singlenum':'$singlenum'}";

} elseif($op == 'search') {

	if(strlen($searchkey) < 2) {
		showmessage('username_less_two_chars');
	}

	$list = array();
	$list = C::t('common_member')->fetch_all_by_like_username($searchkey, 0, 100);
	$navtitle = lang('core', 'title_search_friend');
}

include template('home/spacecp_friend');

?>