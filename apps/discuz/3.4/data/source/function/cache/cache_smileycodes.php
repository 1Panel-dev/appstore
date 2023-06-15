<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_smileycodes.php 24968 2011-10-19 09:51:28Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_smileycodes() {
	$data = array();
	foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley', 1) as $type) {
		foreach(C::t('common_smiley')->fetch_all_by_type_code_typeid('smiley', $type['typeid']) as $smiley) {
			if($size = @getimagesize('./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
				$data[$smiley['id']] = $smiley['code'];
			}
		}
	}

	savecache('smileycodes', $data);
}

?>