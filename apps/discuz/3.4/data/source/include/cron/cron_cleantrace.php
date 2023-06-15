<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleantrace.php 24958 2011-10-19 02:54:32Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$maxday = 90;
$deltime = $_G['timestamp'] - $maxday*3600*24;

C::t('home_clickuser')->delete_by_dateline($deltime);

C::t('home_visitor')->delete_by_dateline($deltime);

?>