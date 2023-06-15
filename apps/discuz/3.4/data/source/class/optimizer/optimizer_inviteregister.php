<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_inviteregister.php 33957 2013-09-06 03:51:03Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_inviteregister {

	public function __construct() {

	}

	public function check() {
		$regstatus = C::t('common_setting')->fetch('regstatus');
		if($regstatus >= 2) {
			$inviteconfig = C::t('common_setting')->fetch('inviteconfig', true);
			if(!$inviteconfig['inviteareawhite']) {
				$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_inviteregister_tip'));
			} else {
				$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_iniviteregister_normal'));
			}
		} else {
			$return = array('status' => 2, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_iniviteregister_normal'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=access');
	}
}

?>