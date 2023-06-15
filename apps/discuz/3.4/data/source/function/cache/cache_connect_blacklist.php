<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_connect_blacklist.php 24406 2011-09-18 06:53:04Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_connect_blacklist() {
	global $_G;
	$data = array();

	foreach(C::t('common_uin_black')->fetch_all_by_uin() as $blacklist) {
		$data[] = $blacklist['uin'];
	}

	savecache('connect_blacklist', $data);
}

?>