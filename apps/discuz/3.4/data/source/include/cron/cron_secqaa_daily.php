<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_secqaa_daily.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['setting']['secqaa']['status'] > 0) {
	require_once libfile('function/cache');
	updatecache('secqaa');
}

?>