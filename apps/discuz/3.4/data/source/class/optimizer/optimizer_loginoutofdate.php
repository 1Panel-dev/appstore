<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_loginoutofdate.php 33867 2013-08-23 06:12:21Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_loginoutofdate {

	public function __construct() {

	}

	public function check() {
		$accountguard = C::t('common_setting')->fetch('accountguard', true);
		if(!$accountguard['loginoutofdate']) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_loginoutofdate_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_loginoutofdate_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=accountguard');
	}
}

?>