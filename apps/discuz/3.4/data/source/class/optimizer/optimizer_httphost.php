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

class optimizer_httphost {

	public function __construct() {

	}

	public function check() {
		global $_G;
		$status = false;
		if(!empty($_SERVER['HTTP_HOST'])) {
			$clientip = gethostbyname($_SERVER['HTTP_HOST']);
			$testurl = str_replace($_SERVER['HTTP_HOST'], $clientip, $_G['siteurl']);
			$content = dfsockopen($testurl);
			if(strpos($content, 'Discuz!') !== false) {
				$status = true;
			}
		}
		if($status) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_httphost_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_httphost_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		cpmsg('optimizer_httphost_optimizer', '', 'error');
	}
}

?>