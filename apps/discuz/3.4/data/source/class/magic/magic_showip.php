<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_showip.php 29373 2012-04-09 07:55:30Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_showip {

	var $version = '1.0';
	var $name = 'showip_name';
	var $description = 'showip_desc';
	var $price = '10';
	var $weight = '10';
	var $useevent = 1;
	var $targetgroupperm = true;
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
	}

	function setsetting(&$magicnew, &$parameters) {
	}

	function usesubmit() {
		global $_G;
		if(empty($_GET['username'])) {
			showmessage(lang('magic/showip', 'showip_info_nonexistence'));
		}

		$member = getuserinfo($_GET['username']);
		$this->_check($member['groupid']);

		$memberstatus = C::t('common_member_status')->fetch($member['uid']);
		$ip = $memberstatus['lastip'];
		unset($memberstatus);
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'uid', $member['uid']);

		if($member['uid'] != $_G['uid']) {
			notification_add($member['uid'], 'magic', lang('magic/showip', 'showip_notification'), array('magicname' => $this->magic['name']), 1);
		}

		showmessage(lang('magic/showip', 'showip_ip_message'), '', array('username' => $_GET['username'], 'ip' => $ip), array('alert' => 'info', 'showdialog' => 1));
	}

	function show() {
		global $_G;
		$user = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		if($user) {
			$member = getuserinfo($user);
			$this->_check($member['groupid']);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/showip', 'showip_targetuser'), 'username', $user, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_GET['id'])) {
			$member = getuserinfo($_GET['id']);
			if($_G['group']['allowviewip']) {
				$_GET['username'] = $member['username'];
				$this->usesubmit();
			} else {
				$this->_check($member['groupid']);
			}
		}
	}

	function _check($groupid) {
		if(!checkmagicperm($this->parameters['targetgroups'], $groupid)) {
			showmessage(lang('magic/showip', 'showip_info_noperm'));
		}
	}

}

?>