<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_regmaildomain.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_regmaildomain {

	public function __construct() {

	}

	public function check() {
		$regmaildomain = C::t('common_setting')->fetch('regmaildomain');
		$maildomainlist = C::t('common_setting')->fetch('maildomainlist');
		if($regmaildomain == 2 && !$maildomainlist) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_regmaildomain_need'));
		} else {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_regmaildomain_tip'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=access');
	}
}

?>