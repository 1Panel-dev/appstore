<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_editperdel.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_editperdel {

	public function __construct() {

	}

	public function check() {
		$editperdel = C::t('common_setting')->fetch('editperdel');
		if($editperdel) {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_editperdel_no_need'));
		} else {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_editperdel_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=permissions');
	}
}

?>