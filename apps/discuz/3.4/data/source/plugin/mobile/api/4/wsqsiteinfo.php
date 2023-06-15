<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsqsiteinfo.php 34422 2014-04-23 09:56:17Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'misc.php';

class mobile_api {

	function common() {
		global $_G;
		if (!in_array('wechat', $_G['setting']['plugins']['available'])) {
			mobile_core::result(mobile_core::variable(array()));
		}
		require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
		mobile_core::result(mobile_core::variable(wsq::siteinfo()));
	}

	function output() {

	}

}

?>