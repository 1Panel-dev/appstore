<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_dbbackup.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_dbbackup {

	public function __construct() {

	}

	public function check() {
		global $_G;
		$dateline = C::t('common_cache')->fetch('db_export');
		$dateline = dunserialize($dateline['cachevalue']);
		$dateline = $dateline['dateline'];
		if(($_G['timestamp'] - $dateline) > 86400 * 90) {
			$return = array('status' => 1, 'type' => 'header', 'lang' => lang('optimizer', 'optimizer_dbbackup_advice'));
		} else {
			$return = array('status' => 0, 'type' => 'header', 'lang' => lang('optimizer', 'optimizer_dbbackup_lastback').dgmdate($dateline));
		}
		return $return;
	}

	public function optimizer() {
		global $_G;
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=db&operation=export');
	}
}

?>