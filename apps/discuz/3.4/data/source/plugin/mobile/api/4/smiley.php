<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: smiley.php 34398 2014-04-14 07:11:22Z nemohou $
 */

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'misc.php';

class mobile_api {

	function common() {
		global $_G;
		loadcache(array('smilies', 'smileytypes'));
		$variable = array();
		foreach ($_G['cache']['smilies']['replacearray'] as $id => $img) {
			$variable['smilies'][$_G['cache']['smilies']['typearray'][$id]][] = array(
			    'code' => $_G['cache']['smilies']['searcharray'][$id],
			    'image' => $_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$id]]['directory'] . '/' . $img
			);
		}
		$variable['smilies'] = array_values($variable['smilies']);
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {

	}

}

?>