<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_filecheck.php 31344 2012-08-15 04:01:32Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_filecheck {

	public function __construct() {

	}

	public function check() {
		global $_G;
		$dateline = C::t('common_cache')->fetch('checktools_filecheck');
		$dateline = dunserialize($dateline['cachevalue']);
		$dateline = $dateline['dateline'];
		if(($_G['timestamp'] - $dateline) > 86400 * 90) {
			$return = array('status' => 1, 'type' => 'header', 'lang' => lang('optimizer', 'optimizer_filecheck_advice'));
		} else {
			$return = array('status' => 0, 'type' => 'none', 'lang' => lang('optimizer', 'optimizer_filecheck_lastcheck').dgmdate($dateline));
		}
		return $return;
	}

	public function optimizer() {
		global $_G;
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=checktools&operation=filecheck');
	}
}

?>