<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_onlinelist.php 24596 2011-09-27 10:39:31Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_onlinelist() {
	$data = array();
	$data['legend'] = '';
	foreach(C::t('forum_onlinelist')->fetch_all_order_by_displayorder() as $list) {
		$data[$list['groupid']] = $list['url'];
		$data['legend'] .= !empty($list['url']) ? "<img src=\"".STATICURL."image/common/$list[url]\" /> $list[title] &nbsp; &nbsp; &nbsp; " : '';
		if($list['groupid'] == 7) {
			$data['guest'] = $list['title'];
		}
	}

	savecache('onlinelist', $data);
}

?>