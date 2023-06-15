<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api.php 33591 2013-07-12 06:39:49Z andyzheng $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

$modarray = array('js' => 'javascript/javascript', 'ad' => 'javascript/advertisement');

$mod = !empty($_GET['mod']) ? $_GET['mod'] : '';
if(empty($mod) || !in_array($mod, array('js', 'ad'))) {
	exit('Access Denied');
}

require_once './api/'.$modarray[$mod].'.php';

function loadcore() {
	global $_G;
	require_once './source/class/class_core.php';

	$discuz = C::app();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();
}

?>