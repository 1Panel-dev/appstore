<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_modreasons.php 24152 2011-08-26 10:04:08Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_modreasons() {
	$settings = C::t('common_setting')->fetch_all(array('modreasons', 'userreasons'));
	foreach($settings as $key => $data) {
		$data = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data);
		$data = explode("\n", trim($data));
		savecache($key, $data);
	}
}

?>