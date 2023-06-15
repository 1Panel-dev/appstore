<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_bbcodes_display.php 24599 2011-09-27 11:06:53Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_bbcodes_display() {
	$data = array();
	$i = 0;
	foreach(C::t('forum_bbcode')->fetch_all_by_available_icon(2, true) as $bbcode) {
		$bbcode['perm'] = explode("\t", $bbcode['perm']);
		if(in_array('', $bbcode['perm']) || !$bbcode['perm']) {
			continue;
		}
		$i++;
		$tag = $bbcode['tag'];
		$bbcode['i'] = $i;
		$bbcode['explanation'] = dhtmlspecialchars(trim($bbcode['explanation']));
		$bbcode['prompt'] = addcslashes($bbcode['prompt'], '\\\'');
		unset($bbcode['tag']);
		foreach($bbcode['perm'] as $groupid) {
			$data[$groupid][$tag] = $bbcode;
		}
	}

	savecache('bbcodes_display', $data);
}

?>