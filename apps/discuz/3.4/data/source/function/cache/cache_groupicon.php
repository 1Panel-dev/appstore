<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_groupicon.php 24596 2011-09-27 10:39:31Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_groupicon() {
	$data = array();
	foreach(C::t('forum_onlinelist')->fetch_all_order_by_displayorder() as $list) {
		if($list['url']) {
			$data[$list['groupid']] = STATICURL.'image/common/'.$list['url'];
		}
	}

	savecache('groupicon', $data);
}

?>