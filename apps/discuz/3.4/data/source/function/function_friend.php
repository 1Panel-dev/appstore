<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_friend.php 26635 2011-12-19 01:59:13Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function friend_list($uid, $limit, $start=0) {
	$list = array();
	$query = C::t('home_friend')->fetch_all_by_uid($uid, $start, $limit, true);
	foreach($query as $value) {
		$list[$value['fuid']] = $value;
	}
	return $list;
}

function friend_group_list() {
	global $_G;

	$space = array('uid' => $_G['uid']);
	space_merge($space, 'field_home');

	$groups = array();
	$spacegroup = empty($space['privacy']['groupname'])?array():$space['privacy']['groupname'];
	for($i = 0; $i < $_G['setting']['friendgroupnum']; $i++) {
		if($i == 0) {
			$groups[0] = lang('friend', 'friend_group_default');
		} else {
			if(!empty($spacegroup[$i])) {
				$groups[$i] = $spacegroup[$i];
			} else {
				if($i<8) {
					$groups[$i] = lang('friend', 'friend_group_'.$i);
				} else {
					$groups[$i] = lang('friend', 'friend_group_more', array('num'=>$i));
				}
			}
		}
	}
	return $groups;
}

function friend_check($touids, $isfull = 0) {
	global $_G;

	if(empty($_G['uid'])) return false;
	if(is_array($touids)) {
		$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $touids);

		foreach($query as $value) {
			$touid = $value['fuid'];
			$var = "home_friend_{$_G['uid']}_{$touid}";
			$fvar = "home_friend_{$touid}_{$_G['uid']}";
			$_G[$var] = $_G[$fvar] = true;
			if($isfull) {
				$fvarinfo = "home_friend_info_{$touid}_{$_G['uid']}";
				$_G[$fvarinfo] = $value;
			}
		}

		if(count($query) != count($touids)) {
			return false;
		} else {
			return true;
		}
	} else {
		$touid = $touids;
		$var = "home_friend_{$_G['uid']}_{$touid}";
		$fvar = "home_friend_{$touid}_{$_G['uid']}";
		if(!isset($_G[$var])) {
			$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $touid);
			$friend = $query[0];
			if($friend) {
				$_G[$var] = $_G[$fvar] = true;
				if($isfull) {
					$fvarinfo = "home_friend_info_{$touid}_{$_G['uid']}";
					$_G[$fvarinfo] = $friend;
				}
			} else {
				$_G[$var] = $_G[$fvar] = false;
			}
		}
		return $_G[$var];
	}

}

function friend_request_check($touid) {
	global $_G;

	$var = "home_friend_request_{$touid}";
	if(!isset($_G[$var])) {
		$result = C::t('home_friend_request')->fetch_by_uid_fuid($_G['uid'], $touid);
		$_G[$var] = $result?true:false;
	}
	return $_G[$var];
}

function friend_add($touid, $gid=0, $note='') {
	global $_G;

	if($touid == $_G['uid']) return -2;
	if(friend_check($touid)) return -2;

	include_once libfile('function/stat');
	$freind_request = C::t('home_friend_request')->fetch_by_uid_fuid($_G['uid'], $touid);
	if($freind_request) {
		$setarr = array(
			'uid' => $_G['uid'],
			'fuid' => $freind_request['fuid'],
			'fusername' => addslashes($freind_request['fusername']),
			'gid' => $gid,
			'dateline' => $_G['timestamp']
		);
		C::t('home_friend')->insert($setarr);

		friend_request_delete($touid);

		friend_cache($_G['uid']);

		$setarr = array(
			'uid' => $touid,
			'fuid' => $_G['uid'],
			'fusername' => $_G['username'],
			'gid' => $freind_request['gid'],
			'dateline' => $_G['timestamp']
		);
		C::t('home_friend')->insert($setarr);

		addfriendlog($_G['uid'], $touid);
		friend_cache($touid);
		updatestat('friend');
	} else {

		$to_freind_request = C::t('home_friend_request')->fetch_by_uid_fuid($touid, $_G['uid']);
		if($to_freind_request) {
			return -1;
		}

		$setarr = array(
			'uid' => $touid,
			'fuid' => $_G['uid'],
			'fusername' => $_G['username'],
			'gid' => $gid,
			'note' => $note,
			'dateline' => $_G['timestamp']
		);
		C::t('home_friend_request')->insert($setarr);

		updatestat('addfriend');
	}

	return 1;
}

function friend_make($touid, $tousername, $checkrequest=true) {
	global $_G;

	if($touid == $_G['uid']) return false;

	if($checkrequest) {
		$to_freind_request = C::t('home_friend_request')->fetch_by_uid_fuid($touid, $_G['uid']);
		if($to_freind_request) {
			C::t('home_friend_request')->delete_by_uid_fuid($touid, $_G['uid']);
		}

		$to_freind_request = C::t('home_friend_request')->fetch_by_uid_fuid($_G['uid'], $touid);
		if($to_freind_request) {
			C::t('home_friend_request')->delete_by_uid_fuid($_G['uid'], $touid);
		}
	}


	$insertarray = array(
		'uid' => $touid,
		'fuid' => $_G['uid'],
		'fusername' => $_G['username'],
		'dateline' => $_G['timestamp'],
	);
	C::t('home_friend')->insert($insertarray, false, true);

	$insertarray = array(
		'uid' => $_G['uid'],
		'fuid' => $touid,
		'fusername' => $tousername,
		'dateline' => $_G['timestamp'],
	);
	C::t('home_friend')->insert($insertarray, false, true);

	addfriendlog($_G['uid'], $touid);
	include_once libfile('function/stat');
	updatestat('friend');
	friend_cache($touid);
	friend_cache($_G['uid']);
}

function addfriendlog($uid, $touid, $action = 'add') {
	global $_G;

	if($uid && $touid) {
		$flog = array(
				'uid' => $uid > $touid ? $uid : $touid,
				'fuid' => $uid > $touid ? $touid : $uid,
				'dateline' => $_G['timestamp'],
				'action' => $action
		);
		DB::insert('home_friendlog', $flog, false, true);
		return true;
	}

	return false;

}

function friend_addnum($touid) {
	global $_G;

	if($_G['uid'] && $_G['uid'] != $touid) {
		C::t('home_friend')->update_num_by_uid_fuid(1, $_G['uid'], $touid);
	}
}

function friend_cache($touid) {
	global $_G;

	$tospace = array('uid' => $touid);
	space_merge($tospace, 'field_home');

	$filtergids = empty($tospace['privacy']['filter_gid'])?array():$tospace['privacy']['filter_gid'];

	$uids = array();
	$count = 0;
	$fcount = 0;
	$query = C::t('home_friend')->fetch_all_by_uid($touid, 0, 0, true);
	foreach($query as $value) {
		if($value['fuid'] == $touid) continue;
		if($fcount > 200) {
			$count = count($query);
			break;
		} elseif(empty($filtergids) || !in_array($value['gid'], $filtergids)) {
			$uids[] = $value['fuid'];
			$fcount++;
		}
		$count++;
	}
	C::t('common_member_field_home')->update($touid, array('feedfriend'=>implode(',', $uids)));
	C::t('common_member_count')->update($touid, array('friends'=>$count));

}


function friend_request_delete($touid) {
	global $_G;

	return C::t('home_friend_request')->delete_by_uid_fuid($_G['uid'], $touid);
}

function friend_delete($touid) {
	global $_G;

	if(!friend_check($touid)) return false;

	C::t('home_friend')->delete_by_uid_fuid_dual($_G['uid'], $touid);

	if(DB::affected_rows()) {
		addfriendlog($_G['uid'], $touid, 'delete');
		friend_cache($_G['uid']);
		friend_cache($touid);
	}
}

?>