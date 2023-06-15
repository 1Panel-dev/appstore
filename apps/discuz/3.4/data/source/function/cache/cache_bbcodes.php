<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_bbcodes.php 24610 2011-09-28 03:02:49Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_bbcodes() {
	$data = array();

	$regexp = array	(
		1 => "/\[{bbtag}\](.*?)\[\/{bbtag}\]/is",
		2 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?)\](.*?)\[\/{bbtag}\]/is",
		3 => "/\[{bbtag}=(['\"]?)(.+?)(['\"]?),(['\"]?)(.+?)(['\"]?)\](.*?)\[\/{bbtag}\]/is"
	);

	foreach(C::t('forum_bbcode')->fetch_all_by_available_icon(0, false, '>') as $bbcode) {
		$bbcode['perm'] = explode("\t", $bbcode['perm']);
		if(in_array('', $bbcode['perm']) || !$bbcode['perm']) {
			continue;
		}
		$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
		$bbcode['replacement'] = preg_replace("/([\r\n])/", '', $bbcode['replacement']);
		switch($bbcode['params']) {
			case 2:
				$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{2}', '\\4', $bbcode['replacement']);
				break;
			case 3:
				$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
				$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);
				break;
			default:
				$bbcode['replacement'] = str_replace('{1}', '\\1', $bbcode['replacement']);
				break;
		}
		if(preg_match("/\{(RANDOM|MD5)\}/", $bbcode['replacement'])) {
			$replace = str_replace('{RANDOM}', '', str_replace('{MD5}', '', $bbcode['replacement']));
		} else {
			$replace = $bbcode['replacement'];
		}

		foreach($bbcode['perm'] as $groupid) {
			for($i = 0; $i < $bbcode['nest']; $i++) {
				$data[$groupid]['searcharray'][] = $search;
				$data[$groupid]['replacearray'][] = $replace;
			}
		}
	}

	savecache('bbcodes', $data);
}

?>