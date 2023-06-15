<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_attachexpire.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_attachexpire {

	public function __construct() {

	}

	public function check() {
		$attachexpire = C::t('common_setting')->fetch('attachexpire');
		$remoteftp = C::t('common_setting')->fetch('ftp', true);
		if(!$attachexpire || ($remoteftp['on'] == 1 && !$remoteftp['hideurl'])) {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_attachexpire_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_attachexpire_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=attach');
	}
}

?>