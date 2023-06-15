<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleannotification.php 24556 2011-09-26 06:16:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

C::t('home_notification')->delete_clear(0, 2);
C::t('home_notification')->delete_clear(1, 30);

$deltime = $_G['timestamp'] - 7*3600*24;
C::t('home_pokearchive')->delete_by_dateline($deltime);

C::t('home_notification')->optimize();

?>