<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: checkpost.php 32489 2013-01-29 03:57:16Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
		$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/sub_checkpost.php';
		if(file_exists($apifile)) {
			require_once $apifile;
		}
		mobile_core::result(mobile_core::variable(mobile_api_sub::getvariable()));
	}

	function output() {}

}

?>