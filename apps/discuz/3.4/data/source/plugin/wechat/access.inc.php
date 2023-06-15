<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: access.inc.php 34492 2014-05-09 02:18:22Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_GET['mobile'] = 2;
$_GET['op'] = 'access';

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.inc.php';