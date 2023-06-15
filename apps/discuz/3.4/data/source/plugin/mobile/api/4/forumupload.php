<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumupload.php 35181 2015-01-08 01:51:31Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

error_reporting(0);
mobile_core::make_cors($_SERVER['REQUEST_METHOD'], REQUEST_METHOD_DOMAIN);
$_GET['mod'] = 'swfupload';
$_GET['action'] = 'swfupload';
$_GET['operation'] = 'upload';
include_once 'misc.php';

class mobile_api {

	function common() {

	}

	function output() {

	}

}

?>