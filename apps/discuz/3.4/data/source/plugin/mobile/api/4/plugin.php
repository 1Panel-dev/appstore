<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: profile.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'plugin.php';

class mobile_api {

	function common() {
	}

	function output() {
		json_output();
	}

}

function json_output() {
	mobile_core::result(mobile_core::variable(array('html' => $GLOBALS['variable'])));
}

?>