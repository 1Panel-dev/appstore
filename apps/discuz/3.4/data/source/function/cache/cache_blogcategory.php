<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_blogcategory.php 24543 2011-09-23 08:30:17Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_blogcategory() {
	$data = array();
	$query = C::t('home_blog_category')->fetch_all_by_displayorder();

	foreach ($query as $value) {
		$value['catname'] = dhtmlspecialchars($value['catname']);
		$data[$value['catid']] = $value;
	}
	foreach($data as $key => $value) {
		$upid = $value['upid'];
		$data[$key]['level'] = 0;
		if($upid && isset($data[$upid])) {
			$data[$upid]['children'][] = $key;
			while($upid && isset($data[$upid])) {
				$data[$key]['level'] += 1;
				$upid = $data[$upid]['upid'];
			}
		}
	}

	savecache('blogcategory', $data);
}

?>