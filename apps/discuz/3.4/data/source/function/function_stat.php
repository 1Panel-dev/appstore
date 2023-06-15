<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_stat.php 23484 2011-07-19 10:12:36Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatestat($type, $primary=0, $num=1) {
	$uid = getglobal('uid');
	$updatestat = getglobal('setting/updatestat');
	if(empty($uid) || empty($updatestat)) return false;
	C::t('common_stat')->updatestat($uid, $type, $primary, $num);
}

?>