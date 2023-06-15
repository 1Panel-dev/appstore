<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_share.php 31894 2012-10-23 02:13:29Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function mkshare($share) {
	require_once libfile('function/discuzcode');
	$share['body_data'] = unserialize($share['body_data']);

	$searchs = $replaces = array();
	if($share['body_data']) {
		if(isset($share['body_data']['flashaddr'])) {
			$share['body_data']['flashaddr'] = addslashes($share['body_data']['flashaddr']);
		} elseif(isset($share['body_data']['musicvar'])) {
			$share['body_data']['musicvar'] = addslashes($share['body_data']['musicvar']);
		}
		foreach (array_keys($share['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $share['body_data'][$key];
		}
		if($share['body_data']['flashvar']){
			$share['body_data']['player'] = parseflv($share['body_data']['data'], '500', '373');
		}
		if($share['body_data']['musicvar']){
			$share['body_data']['player'] = parseaudio($share['body_data']['data']);
		}
		if($share['body_data']['videovar']){
			$share['body_data']['player'] = parsemedia('x,500,373',$share['body_data']['data']);
		}
	}
	$share['body_template'] = str_replace($searchs, $replaces, $share['body_template']);

	return $share;
}
?>