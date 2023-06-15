<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_magic.php 26763 2011-12-22 09:28:20Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$space['credit'] = $space['credits'];

$op = empty($_GET['op']) ? "view" : $_GET['op'];
$mid = empty($_GET['mid']) ? '' : trim($_GET['mid']);

if(!checkperm('allowmagics')) {
	showmessage('magic_groupid_not_allowed');
}

if($op == 'cancelflicker') {

	$mid = 'flicker';
	$_GET['idtype'] = 'cid';
	$_GET['id'] = intval($_GET['id']);
	$value = C::t('home_comment')->fetch($_GET['id'], $_G['uid']);
	if(!$value || !$value['magicflicker']) {
		showmessage('no_flicker_yet');
	}

	if(submitcheck('cancelsubmit')) {
		C::t('home_comment')->update('', array('magicflicker'=>0), $_G['uid']);
		showmessage('do_success', dreferer(), array(), array('showdialog' => 1, 'closetime' => true));
	}

} elseif($op == 'cancelcolor') {

	$mid = 'color';
	$_GET['id'] = intval($_GET['id']);
	$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
	$tablename = $mapping[$_GET['idtype']];
	if(empty($tablename)) {
		showmessage('no_color_yet');
	}
	$value = C::t($tablename)->fetch($_GET['id']);
	if(!$value || $value['uid'] != $_G['uid'] || !$value['magiccolor']) {
		showmessage('no_color_yet');
	}

	if(submitcheck('cancelsubmit')) {
		DB::update($tablename, array('magiccolor'=>0), array($_GET['idtype']=>$_GET[id]));
		$feed = C::t('home_feed')->fetch($_GET['id'], $_GET['idtype']);
		if($feed) {
			$feed['body_data'] = dunserialize($feed['body_data']);
			if($feed['body_data']['magic_color']) {
				unset($feed['body_data']['magic_color']);
			}
			$feed['body_data'] = serialize($feed['body_data']);
			C::t('home_feed')->update('', array('body_data'=>$feed['body_data']), '', '', $feed['feedid']);
		}
		showmessage('do_success', dreferer(), 0);
	}

} elseif($op == 'receivegift') {

	$uid = intval($_GET['uid']);
	$mid = 'gift';
	$memberfieldhome = C::t('common_member_field_home')->fetch($uid);
	$info = $memberfieldhome['magicgift'] ? dunserialize($memberfieldhome['magicgift']) : array();
	unset($memberfieldhome);
	if(!empty($info['left'])) {
		$info['receiver'] = is_array($info['receiver']) ? $info['receiver'] : array();
		if(in_array($_G['uid'], $info['receiver'])) {
			showmessage('haved_red_bag');
		}
		$percredit = min($info['left'], $info['percredit']);
		$info['receiver'][] = $_G['uid'];
		$info['left'] = $info['left'] - $percredit;
		C::t('common_member_field_home')->update($uid, array('magicgift' => ($info['left'] > 0 ? serialize($info) : '')));
		$credittype = '';
		if(preg_match('/^extcredits[1-8]$/', $info['credittype'])) {
			$extcredits = str_replace('extcredits', '', $info['credittype']);
			updatemembercount($_G['uid'], array($extcredits => $percredit), 1, 'AGC', $info['magicid']);
			$credittype = $_G['setting']['extcredits'][$extcredits]['title'];
		}
		showmessage('haved_red_bag_gain', dreferer(), array('percredit' => $percredit, 'credittype' => $credittype), array('showdialog' => 1, 'locationtime' => true));
	}
	showmessage('space_no_red_bag', dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));

} elseif($op == 'retiregift') {

	$mid = 'gift';
	$memberfieldhome = C::t('common_member_field_home')->fetch($_G['uid']);
	$info = $memberfieldhome['magicgift'] ? dunserialize($memberfieldhome['magicgift']) : array();
	unset($memberfieldhome);
	$leftcredit = intval($info['left']);
	if($leftcredit<=0) {
		C::t('common_member_field_home')->update($_G['uid'], array('magicgift' => ''));
		showmessage('red_bag_no_credits');
	}

	$extcredits = str_replace('extcredits', '', $info['credittype']);
	$credittype = $_G['setting']['extcredits'][$extcredits]['title'];

	if(submitcheck('cancelsubmit')) {
		C::t('common_member_field_home')->update($_G['uid'], array('magicgift' => ''));
		if(preg_match('/^extcredits[1-8]$/', $info['credittype'])) {
			updatemembercount($_G['uid'], array($extcredits => $leftcredit), 1, 'RGC', $info['magicid']);
		}
		showmessage('return_red_bag', dreferer(), array('leftcredit' => $leftcredit, 'credittype' => $credittype), array('showdialog' => 1, 'locationtime' => true));
	}
}

include_once template('home/spacecp_magic');

?>