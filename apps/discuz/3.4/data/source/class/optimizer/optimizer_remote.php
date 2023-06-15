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

class optimizer_remote {

	public function __construct() {

	}

	public function check() {
		$nosecurity = getglobal('config/remote/on');
		if($nosecurity) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_remote_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_remote_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		cpmsg('optimizer_remote_optimizer', '', 'error');
	}
}