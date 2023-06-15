<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_ipregctrl.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_ipregctrl {

	public function __construct() {

	}

	public function check() {
		$ipregctrl = C::t('common_setting')->fetch('ipregctrl');
		if($ipregctrl) {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_ipregctrl_no_need'));
		} else {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_ipregctrl_tip'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=access');
	}
}

?>