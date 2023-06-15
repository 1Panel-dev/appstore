<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: checkcookie.php 35028 2014-10-21 09:55:55Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

dsetcookie('testcookie', $_GET['siteid'], 600);

mobile_core::result(array());