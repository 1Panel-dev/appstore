<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_emailregister.php 33906 2013-08-29 09:40:37Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_emailregister {

	public function __construct() {

	}

	public function check() {
		$sendregister = C::t('common_setting')->fetch('sendregisterurl');
		if($sendregister) {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_emailregister_normal'), 'extraurl' => '&checkemail=1');
		} else {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_emailregister_tip'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		if($_GET['checkemail']) {
			$url = '?action=setting&operation=mail';
		} else {
			$url = '?action=setting&operation=access';
		}
		dheader('Location: '.$_G['siteurl'].$adminfile.$url);
	}
}

?>