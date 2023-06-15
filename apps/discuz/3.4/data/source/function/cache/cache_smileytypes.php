<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_smileytypes.php 24968 2011-10-19 09:51:28Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_smileytypes() {
	$data = array();
	foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley', 1) as $type) {
		$typeid = $type['typeid'];
		unset($type['typeid']);
		if(C::t('common_smiley')->count_by_type_code_typeid('smiley', $typeid)) {
			$data[$typeid] = $type;
		}
	}

	savecache('smileytypes', $data);
}

?>