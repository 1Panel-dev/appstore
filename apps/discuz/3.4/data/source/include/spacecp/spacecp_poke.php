<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_poke.php 34369 2014-04-01 02:00:04Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$_GET['fuid'] = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);

if($uid == $_G['uid']) {
	showmessage('not_to_their_own_greeted');
}

if($op == 'send' || $op == 'reply') {

	if(!checkperm('allowpoke')) {
		showmessage('no_privilege_poke');
	}

	cknewuser();

	$tospace = array();

	if($uid) {
		$tospace = getuserbyuid($uid);
	} elseif ($_POST['username']) {
		$tospace = C::t('common_member')->fetch_uid_by_username($_POST['username']);
	}

	if($tospace && isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	if(submitcheck('pokesubmit')) {
		if(empty($tospace)) {
			showmessage('space_does_not_exist');
		}

		$notetext = censor(htmlspecialchars(cutstr($_POST['note'], strtolower(CHARSET) == 'utf-8' ? 30 : 20, '')));
		$setarr = array(
			'pokeuid' => $uid+$_G['uid'],
			'uid' => $uid,
			'fromuid' => $_G['uid'],
			'note' => $notetext, //need to do
			'dateline' => $_G['timestamp'],
			'iconid' => intval($_POST['iconid'])
		);
		C::t('home_pokearchive')->insert($setarr);

		$setarr = array(
			'uid' => $uid,
			'fromuid' => $_G['uid'],
			'fromusername' => $_G['username'],
			'note' => $notetext,
			'dateline' => $_G['timestamp'],
			'iconid' => intval($_POST['iconid'])
		);

		C::t('home_poke')->insert($setarr, false, true);

		require_once libfile('function/friend');
		friend_addnum($tospace['uid']);

		if($op == 'reply') {
			C::t('home_poke')->delete_by_uid_fromuid($_G['uid'], $uid);
			C::t('common_member')->increase($_G['uid'], array('newprompt' => -1));
		}
		updatecreditbyaction('poke', 0, array(), $uid);

		if($setarr['iconid']) {
			require_once libfile('function/spacecp');
			$pokemsg = makepokeaction($setarr['iconid']);
		} else {
			$pokemsg = lang('home/template', 'say_hi');
		}
		if(!empty($setarr['note'])) {
			$pokemsg .= ', '.lang('home/template', 'say').':'.$setarr['note'];
		}

		$note = array(
				'fromurl' => 'home.php?mod=space&uid='.$_G['uid'],
				'fromusername' => $_G['username'],
				'fromuid' => $_G['uid'],
				'from_id' => $_G['uid'],
				'from_idtype' => 'pokequery',
				'pokemsg' => $pokemsg
			);
		notification_add($uid, 'poke', 'poke_request', $note);

		include_once libfile('function/stat');
		updatestat('poke');

		showmessage('poke_success', dreferer(), array('username' => $tospace['username'], 'uid' => $uid, 'from' => $_GET['from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

	}

} elseif($op == 'ignore') {
	if(submitcheck('ignoresubmit')) {
		$where = empty($uid)?'':"AND fromuid='$uid'";
		C::t('home_poke')->delete_by_uid_fromuid($_G['uid'], $uid);

		C::t('home_notification')->delete_by_uid_type_authorid($_G['uid'], 'poke', $uid);

		showmessage('has_been_hailed_overlooked', '', array('uid' => $uid, 'from' => $_GET['from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'alert' => 'right'));
	}

} elseif($op == 'view') {

	$_GET['uid'] = intval($_GET['uid']);

	$list = array();
	foreach(C::t('home_poke')->fetch_all_by_uid_fromuid($space['uid'], $_GET['uid']) as $value) {
		$pokeuid = $value['uid']+$value['fromuid'];

		$value['uid'] = $value['fromuid'];
		$value['username'] = $value['fromusername'];

		require_once libfile('function/friend');
		$value['isfriend'] = $value['uid']==$space['uid'] || friend_check($value['uid']) ? 1 : 0;

		foreach(C::t('home_pokearchive')->fetch_all_by_pokeuid($pokeuid) as $subvalue) {
			$list[$subvalue['pid']] = $subvalue;
		}

	}

} else {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$fuids = $list = array();
	$count = C::t('home_poke')->count_by_uid($space['uid']);
	if($count) {
		foreach(C::t('home_poke')->fetch_all_by_uid($space['uid'], $start, $perpage) as $value) {
			$value['uid'] = $value['fromuid'];
			$value['username'] = $value['fromusername'];

			$fuids[$value['uid']] = $value['uid'];
			$list[$value['uid']] = $value;
		}
		if($fuids) {
			require_once libfile('function/friend');
			friend_check($fuids);

			$value = array();
			foreach($fuids as $key => $fuid) {
				$value['isfriend'] = $fuid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$fuid] ? 1 : 0;
				$list[$fuid] = array_merge($list[$fuid], $value);
			}

		}
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=poke");

}

$actives = array($op=='send'?'send':'poke' =>' class="a"');

include_once template('home/spacecp_poke');

?>