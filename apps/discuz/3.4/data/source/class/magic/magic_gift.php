<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_gift.php 26749 2011-12-22 07:38:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_gift {

	var $version = '1.0';
	var $name = 'gift_name';
	var $description = 'gift_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		$settings = array();
		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
	}

	function usesubmit() {
		global $_G;

		$info = array(
			'credits' => intval($_POST['credits']),
			'percredit' => intval($_POST['percredit']),
			'credittype' => $_GET['credittype'],
			'left' => intval($_POST['credits']),
			'magicid' => intval($this->magic['magicid']),
			'receiver' => array()
		);
		if($info['credits'] < 1) {
			showmessage(lang('magic/gift', 'gift_bad_credits_input'));
		}
		if($info['percredit'] < 1 || $info['percredit'] > $info['credits']) {
			showmessage(lang('magic/gift', 'gift_bad_percredit_input'));
		}
		$member = array();
		if(preg_match('/^extcredits[1-8]$/', $info['credittype'])) {
			$member = C::t('common_member_count')->fetch($_G['uid']);
			if($member[$info['credittype']] < $info['credits']) {
				showmessage(lang('magic/gift', 'gift_credits_out_of_own'));
			}
			$extcredits = str_replace('extcredits', '', $info['credittype']);
			updatemembercount($_G['uid'], array($extcredits => -$info['credits']), 1, 'BGC', $this->magic['magicid']);
		} else {
			showmessage(lang('magic/gift', 'gift_bad_credittype_input'));
		}

		C::t('common_member_field_home')->update($_G['uid'], array('magicgift' => serialize($info)));
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', 'uid', $_G['uid']);

		showmessage(lang('magic/gift', 'gift_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		magicshowtips(lang('magic/gift', 'gift_info', array('num'=>$num)));

		$extcredits = array();
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			$extcredits['extcredits'.$id] = $credit['title'];
		}

		$op = 'show';
		include template('home/magic_gift');
	}
}

?>