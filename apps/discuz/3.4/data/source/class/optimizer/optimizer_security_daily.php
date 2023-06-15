<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_security_daily.php 33867 2013-08-23 06:12:21Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_security_daily {

	public function __construct() {

	}

	public function check() {
		$flag = false;
		$crons = DB::query("SELECT * FROM ".DB::table('common_cron'));
		foreach($crons as $cron) {
			if($cron['filename'] == 'cron_security_daily.php' && $cron['available'] == '1') {
				$flag = true;
				break;
			}
		}
		if(!$flag) {
			$return = array('status' => 2, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_security_daily_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_security_daily_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=misc&operation=cron');
	}
}

?>