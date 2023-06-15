<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_magics.php 24589 2011-09-27 07:45:55Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_magics() {
	$data = array();
	foreach(C::t('common_magic')->fetch_all_data(1) as $magic) {
		$data[$magic['magicid']] = $magic;
	}

	savecache('magics', $data);
}

?>