<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wechat_clearlogin.php 34422 2014-04-23 09:56:17Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require './source/class/class_core.php';

$discuz = C::app();

$discuz->cachelist = $cachelist;
$discuz->init();

$variable = array('result' => 0);
if($_G['uid'] && FORMHASH == $_GET['hash']) {
	C::t('#wechat#common_member_wechat')->update($_G['uid'], array('status' => 1));
	$variable['result'] = 1;
}

mobile_core::result(mobile_core::variable($variable));