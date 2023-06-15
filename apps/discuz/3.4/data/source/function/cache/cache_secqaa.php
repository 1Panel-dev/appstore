<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_secqaa.php 24522 2011-09-23 02:12:46Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_secqaa() {
	$data = array();
	$secqaanum = C::t('common_secquestion')->count();

	$start_limit = $secqaanum <= 10 ? 0 : mt_rand(0, $secqaanum - 10);
	$i = 1;
	foreach(C::t('common_secquestion')->fetch_all($start_limit, 10) as $secqaa) {
		if(!$secqaa['type'])  {
			$secqaa['answer'] = md5($secqaa['answer']);
		}
		$data[$i] = $secqaa;
		$i++;
	}
	while(($secqaas = count($data)) < 9) {
		$data[$secqaas + 1] = $data[array_rand($data)];
	}
	savecache('secqaa', $data);
}

?>