<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 33969 2013-09-10 08:32:14Z nemohou $
 */

if(!empty($_SERVER['QUERY_STRING'])) {
	$plugin = !empty($_GET['oem']) ? 'mobileoem' : 'mobile';
	$dir = '../../source/plugin/'.$plugin.'/';
	chdir($dir);
	if((isset($_GET['check']) && $_GET['check'] == 'check' || $_SERVER['QUERY_STRING'] == 'check') && is_file('check.php')) {
		require_once 'check.php';
	} elseif(is_file('mobile.php')) {
		require_once 'mobile.php';
	}
}

?>