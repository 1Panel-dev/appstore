<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_pwlength.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_pwlength {

	public function __construct() {

	}

	public function check() {
		$pwlength = C::t('common_setting')->fetch('pwlength');
		if($pwlength < 6) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_pwlength_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_pwlength_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=access');
	}
}

?>