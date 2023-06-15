<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: smiley.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'misc.php';

class mobile_api {

	function common() {
		global $_G;
		loadcache(array('smilies', 'smileytypes'));
		$variable = array();
		foreach($_G['cache']['smilies']['replacearray'] as $id => $img) {
			$variable['smilies'][] = array(
			    'code' => $_G['cache']['smilies']['searcharray'][$id],
			    'image' => $_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$id]]['directory'].'/'.$img
			);
		}
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {
	}

}

?>