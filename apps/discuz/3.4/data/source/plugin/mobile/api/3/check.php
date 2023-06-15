<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: check.php 34236 2013-11-21 01:13:12Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include 'data/sysdata/cache_mobile.php';

echo $mobilecheck;

?>