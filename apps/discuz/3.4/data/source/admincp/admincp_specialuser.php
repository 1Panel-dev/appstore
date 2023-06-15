<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_specialuser.php 27515 2012-02-03 03:29:49Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

cpheader();

$operation = in_array($_GET['operation'], array('defaultuser', 'follow')) ? trim($_GET['operation']) : 'defaultuser';
$suboperation = in_array($_GET['suboperation'], array('adduser', 'specialuser')) ? trim($_GET['suboperation']) : '';
$status = ($operation == 'defaultuser') ? 1 : 0;
$op = ($status == 1) ? 'defaultuser' : 'follow';
$url = 'specialuser&operation='.$op.'&suboperation=specialuser';

if($suboperation !== 'adduser') {
	if($_GET['do'] == 'edit') {
		$_GET['id'] = intval($_GET['id']);
		if(!submitcheck('editsubmit')) {
			$info = C::t('home_specialuser')->fetch_by_uid_status($_GET['uid'], $status);
			shownav('user', 'nav_defaultuser');
			showsubmenu('edit');
			showformheader('specialuser&operation='.$op.'&do=edit&uid='.$info[uid], '', 'userforum');
			showtableheader();
			showsetting('reason', 'reason', $info['reason'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			if(!$_GET['reason']) {
				cpmsg('specialuser_'.$op.'_noreason_invalid', 'action=specialuser&operation='.$op, 'error');
			}
			$updatearr = array('reason' => $_GET['reason']);
			C::t('home_specialuser')->update_by_uid_status($_GET['uid'], $status, $updatearr);
			cpmsg('specialuser_defaultuser_edit_succeed', 'action=specialuser&operation='.$op, 'succeed');
		}

	} elseif(!submitcheck('usersubmit')) {
		shownav('user', 'nav_'.$op);
		showsubmenu('nav_'.$op, array(
		array('nav_'.$operation, 'specialuser&operation='.$operation, 1),
		array('nav_add_'.$op, 'specialuser&operation='.$op.'&suboperation=adduser', 0)));
		showtips('specialuser_'.$op.'_tips');
		showformheader($url, '', 'userforum');
		showtableheader();
		$status ? showsubtitle(array('', 'specialuser_order', 'uid', 'username', 'reason', 'operator', 'time', ''))
				 : showsubtitle(array('', 'specialuser_order', 'uid', 'username', 'reason', 'operator', 'time', ''));
		foreach(C::t('home_specialuser')->fetch_all_by_status($status, ($page - 1) * $_G['ppp'], $_G['ppp']) as $specialuser) {

			$specialuser['dateline'] = dgmdate($specialuser['dateline']);
			$arr = array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$specialuser[uid]\">",
				"<input type=\"text\" name=\"displayorder[$specialuser[uid]]\" value=\"$specialuser[displayorder]\" size=\"8\">",
				$specialuser['uid'],
				"<a href=\"home.php?mod=space&uid=$specialuser[uid]\" target=\"_blank\">$specialuser[username]</a>",
				$specialuser['reason'],
				"<a href=\"home.php?mod=space&uid=$specialuser[opuid]\" target=\"_blank\">$specialuser[opusername]</a>",
				$specialuser['dateline'],
				"<a href=\"".ADMINSCRIPT."?action=specialuser&operation=$op&do=edit&uid=$specialuser[uid]\" class=\"act\">".$lang['edit']."</a>"
				);
			showtablerow('', '', $arr);
		}
		$usercount = C::t('home_specialuser')->count_by_status($status);
		$multi = multi($usercount, $_G['ppp'], $page, ADMINSCRIPT."?action=specialuser&operation=$op");
		showsubmit('usersubmit', 'submit', 'del', '', $multi);
		showtablefooter();
		showformfooter();

	} else {

		$ids = array();
		if(is_array($_GET['delete'])) {
			foreach($_GET['delete'] as $id) {
				$ids[] = $id;
			}
			if($ids) {
				C::t('home_specialuser')->delete_by_uid_status($ids, $status);
				cpmsg('specialuser_'.$op.'_del_succeed', 'action='.$url, 'succeed');
			}
		}

		if(is_array($_GET['displayorder'])) {
			foreach($_GET['displayorder'] as $id => $val) {
				$updatearr = array('displayorder' => intval($_GET['displayorder'][$id]));
				C::t('home_specialuser')->update_by_uid_status($id, $status, $updatearr);
			}
		}
		cpmsg('specialuser_defaultuser_edit_succeed', 'action='.$url, 'succeed');
	}

} elseif($suboperation == 'adduser') {

		if(!submitcheck('addsubmit')) {

			shownav('user', 'nav_'.$op);
			showsubmenu('nav_'.$op, array(
			array('nav_'.$operation, 'specialuser&operation='.$operation, 0),
			array('nav_add_'.$op, 'specialuser&operation='.$op.'&suboperation=adduser', 1)));
			showtips('specialuser_defaultuser_add_tips');
			showformheader('specialuser&operation='.$op.'&suboperation=adduser', '', 'userforum');
			showtableheader();
			showsetting('username', 'username', '', 'text');
			showsetting('reason', 'reason', '', 'text');
			showsubmit('addsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$username = trim($_GET['username']);
			$reason = trim($_GET['reason']);

			if(!$username || !$reason) {
				cpmsg('specialuser_defaultuser_add_invaild', '', 'error');
			}

			if(C::t('home_specialuser')->count_by_status($status, $username)) {
				cpmsg('specialuser_defaultuser_added_invalid', '', 'error');
			}

			$member = C::t('common_member')->fetch_by_username($username);
			if(empty($member)) {
				cpmsg('specialuser_defaultuser_nouser_invalid', '', 'error');
			}

			$data = array(
				'status' => $status,
				'uid' => $member['uid'],
				'username' => $member['username'],
				'reason' => $reason,
				'dateline' => $_G['timestamp'],
				'opuid' => $_G['member']['uid'],
				'opusername' => $_G['member']['username']
			);

			if(C::t('home_specialuser')->insert($data)) {
				cpmsg('specialuser_'.$op.'_add_succeed', 'action='.$url, 'succeed');
			}
		}
}
?>