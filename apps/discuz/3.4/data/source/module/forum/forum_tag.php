<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;
$op = in_array($_GET['op'], array('search', 'manage', 'set')) ? $_GET['op'] : '';
$taglist = array();
$thread = & $_G['thread'];

if($op == 'search') {
	$searchkey = stripsearchkey($_GET['searchkey']);
	$query = C::t('common_tag')->fetch_all_by_status(0, $searchkey, 50, 0);
	foreach($query as $value) {
		$taglist[] = $value;
	}
	$searchkey = dhtmlspecialchars($searchkey);

} elseif($op == 'manage') {
	if($_G['tid']) {
		$tagarray_all = $array_temp = $threadtag_array = array();
		$tags = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
		$tags = $tags['tags'];
		$tagarray_all = explode("\t", $tags);
		if($tagarray_all) {
			foreach($tagarray_all as $var) {
				if($var) {
					$array_temp = explode(',', $var);
					$threadtag_array[] = $array_temp['1'];
				}
			}
		}
		$tags = implode(',', $threadtag_array);

		$recent_use_tag = array();
		$i = 0;
		$query = C::t('common_tagitem')->select(0, 0, 'tid', 'itemid', 'DESC', 10);
		foreach($query as $result) {
			if($i > 4) {
				break;
			}
			if($recent_use_tag[$result['tagid']] == '') {
				$i++;
			}
			$recent_use_tag[$result['tagid']] = 1;
		}
		if($recent_use_tag) {
			$query = C::t('common_tag')->fetch_all(array_keys($recent_use_tag));
			foreach($query as $result) {
				$recent_use_tag[$result[tagid]] = $result['tagname'];
			}
		}
	}
} elseif($op == 'set' && $_GET['formhash'] == FORMHASH && $_G['group']['allowmanagetag']) {
	$class_tag = new tag();
	$tagstr = $class_tag->update_field($_GET['tags'], $_G['tid'], 'tid', $_G['thread']);
	C::t('forum_post')->update_by_tid('tid:'.$_G['tid'], $_G['tid'], array('tags' => $tagstr), false, false, 1);
}

include_once template("forum/tag");
?>