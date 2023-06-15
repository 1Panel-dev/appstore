<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_magic.php 27757 2012-02-14 03:08:15Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function checkmagicperm($perms, $id) {
	$id = $id ? intval($id) : '';
	return strexists("\t".trim($perms)."\t", "\t".trim($id)."\t") || !$perms;
}

function getmagic($magicid, $magicnum, $weight, $totalweight, $uid, $maxmagicsweight, $force = 0) {
	if($weight + $totalweight > $maxmagicsweight && !$force) {
		showmessage('magics_weight_range_invalid', '', array('less' => $weight + $totalweight - $maxmagicsweight));
	} else {
		if(C::t('common_member_magic')->count($uid, $magicid)) {
			C::t('common_member_magic')->increase($uid, $magicid, array('num' => $magicnum), false, true);
		} else {
			C::t('common_member_magic')->insert(array(
				'uid' => $uid,
				'magicid' => $magicid,
				'num' => $magicnum
			));
		}
	}
}

function getmagicweight($uid, $magicarray) {
	$totalweight = 0;
	$query = C::t('common_member_magic')->fetch_all($uid);
	foreach($query as $magic) {
		$totalweight += $magicarray[$magic['magicid']]['weight'] * $magic['num'];
	}

	return $totalweight;
}

function getpostinfo($id, $type, $colsarray = '') {
	global $_G;
	$type = in_array($type, array('tid', 'pid', 'blogid')) && !empty($type) ? $type : 'tid';

	switch($type) {
		case 'tid':
			$info = C::t('forum_thread')->fetch_by_tid_displayorder($id, 0);
			break;
		case 'pid':
			$info = C::t('forum_post')->fetch($_G['tid'], $id);
			if($info && $info['invisible'] == 0) {
				$thread = C::t('forum_thread')->fetch($_G['tid']);
				$thread['thread_author'] = $thread['author'];
				$thread['thread_authorid'] = $thread['authorid'];
				$thread['thread_status'] = $thread['status'];
				$thread['thread_replycredit'] = $thread['replycredit'];
				$info = array_merge($thread, $info);
			} else {
				$info = array();
			}
			break;
		case 'blogid':
			$info = C::t('home_blog')->fetch($id);
			if(!($info && $info['status'] == '0')) {
				$info = array();
			}
			break;
	}

	if(empty($info)) {
		showmessage('magics_target_nonexistence');
	} else {
		return daddslashes($info, 1);
	}
}

function getuserinfo($username) {
	$member = C::t('common_member')->fetch_by_username($username);
	if(!$member) {
		showmessage('magics_target_member_nonexistence');
	} else {
		return daddslashes($member, 1);
	}
}

function givemagic($username, $magicid, $magicnum, $totalnum, $totalprice, $givemessage, $magicarray) {
	global $_G;

	$member = C::t('common_member')->fetch_by_username($username);
	if(!$member) {
		showmessage('magics_target_member_nonexistence');
	} elseif($member['uid'] == $_G['uid']) {
		showmessage('magics_give_myself');
	}
	$member = array_merge(C::t('common_usergroup_field')->fetch($member['groupid']), $member);
	$totalweight = getmagicweight($member['uid'], $magicarray);
	$magicweight = $magicarray[$magicid]['weight'] * $magicnum;
	if($magicarray[$magicid]['weight'] && $magicweight + $totalweight > $member['maxmagicsweight']) {
		$num = floor(($member['maxmagicsweight'] - $totalweight) / $magicarray[$magicid]['weight']);
		$num = max(0, $num);
		showmessage('magics_give_weight_range_invalid', '', array('num' => $num));
	}

	getmagic($magicid, $magicnum, $magicweight, $totalweight, $member['uid'], $member['maxmagicsweight']);

	notification_add($member['uid'], 'magic', 'magics_receive', array('magicname' => $magicarray[$magicid]['name'], 'msg' => $givemessage));
	updatemagiclog($magicid, '3', $magicnum, $magicarray[$magicid]['price'], $member['uid']);

	if(empty($totalprice)) {
		usemagic($magicid, $totalnum, $magicnum);
		showmessage('magics_give_succeed', 'home.php?mod=magic&action=mybox', array('toname' => $username, 'num' => $magicnum, 'magicname' => $magicarray[$magicid]['name']));
	}
}


function magicthreadmod($tid) {
	foreach(C::t('forum_threadmod')->fetch_all_by_tid_magicid($tid) as $threadmod) {
		if(!$threadmod['magicid'] && in_array($threadmod['action'], array('CLS', 'ECL', 'STK', 'EST', 'HLT', 'EHL'))) {
			showmessage('magics_mod_forbidden');
		}
	}
}


function magicshowsetting($setname, $varname, $value, $type = 'radio', $width = '20%') {
	$check = array();

	echo '<p class="mtm mbn">'.$setname.'</p>';
	if($type == 'radio') {
		$value ? $check['true'] = 'checked="checked"' : $check['false'] = 'checked="checked"';
		echo "<input type=\"radio\" name=\"$varname\" class=\"pr\" value=\"1\" $check[true] /> ".lang('core', 'yes')." &nbsp; &nbsp; \n".
			"<input type=\"radio\" name=\"$varname\" class=\"pr\" value=\"0\" $check[false] /> ".lang('core', 'no')."\n";
	} elseif($type == 'text') {
		echo "<input type=\"text\" name=\"$varname\" class=\"px p_fre\" value=\"".dhtmlspecialchars($value)."\" size=\"12\" autocomplete=\"off\" />\n";
	} elseif($type == 'hidden') {
		echo "<input type=\"hidden\" name=\"$varname\" value=\"".dhtmlspecialchars($value)."\" />\n";
	} else {
		echo $type;
	}

}

function magicshowtips($tips) {
	echo '<p>'.$tips.'</p>';
}

function magicshowtype($type = '') {
	if($type != 'bottom') {
		echo '<p>';
	} else {
		echo '</p>';
	}
}


function usemagic($magicid, $totalnum, $num = 1) {
	global $_G;

	if($totalnum == $num) {
		C::t('common_member_magic')->delete($_G['uid'], $magicid);
	} else {
		C::t('common_member_magic')->increase($_G['uid'], $magicid, array('num' => -$num));
	}
}

function updatemagicthreadlog($tid, $magicid, $action = 'MAG', $expiration = 0, $extra = 0) {
	global $_G;
	$_G['username'] = !$extra ? $_G['username'] : '';
	$data = array(
				'tid' => $tid,
				'uid' => $_G['uid'],
				'magicid' => $magicid,
				'username' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'expiration' => $expiration,
				'action' => $action,
				'status' => 1
			);
	C::t('forum_threadmod')->insert($data);
}

function updatemagiclog($magicid, $action, $amount, $price, $targetuid = 0, $idtype = '', $targetid = 0) {
	global $_G;
	list($price, $credit) = explode('|', $price);
	$data = array(
			'uid' => $_G['uid'],
			'magicid' => $magicid,
			'action' => $action,
			'dateline' => $_G['timestamp'],
			'amount' => $amount,
			'price' => $price,
			'credit' => $credit,
			'idtype' => $idtype,
			'targetid' => $targetid,
			'targetuid' => $targetuid
		);
	C::t('common_magiclog')->insert($data);
}





function magic_check_idtype($id, $idtype) {
	global $_G;

	include_once libfile('function/spacecp');
	$value = '';
	$tablename = gettablebyidtype($idtype);
	if($tablename) {
		$value = C::t($tablename)->fetch_by_id_idtype($id);
		if($value['uid'] != $_G['uid']) {
			$value = null;
		}
	}
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	}
	return $value;
}


function magic_peroid($magic, $uid) {
	global $_G;
	if($magic['useperoid']) {
		$dateline = 0;
		if($magic['useperoid'] == 1) {
			$dateline = TIMESTAMP - (TIMESTAMP + $_G['setting']['timeoffset'] * 3600) % 86400;
		} elseif($magic['useperoid'] == 4) {
			$dateline = TIMESTAMP - 86400;
		} elseif($magic['useperoid'] == 2) {
			$dateline = TIMESTAMP - 86400 * 7;
		} elseif($magic['useperoid'] == 3) {
			$dateline = TIMESTAMP - 86400 * 30;
		}
		$num = C::t('common_magiclog')->count_by_uid_magicid_action_dateline($uid, $magic['magicid'], 2, $dateline);
		return $magic['usenum'] - $num;
	} else {
		return true;
	}
}

?>