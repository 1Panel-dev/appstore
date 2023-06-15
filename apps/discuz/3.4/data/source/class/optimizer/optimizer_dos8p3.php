<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_dos8p3 {

	public function __construct() {

	}

	public function check() {
		global $_G;
		$status = false;
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$testurl = $_G['siteurl'] . 'API/JAVASC~1/ADVERT~1.PHP';
			$content = dfsockopen($testurl);
			if(strpos($content, 'Access Denied') !== false) {
				$status = true;
			}
		}
		if($status) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_dos8p3_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_dos8p3_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		cpmsg('optimizer_dos8p3_optimizer', '', 'error');
	}
}

?>