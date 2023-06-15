<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_plugin.php 33362 2013-05-31 09:31:22Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginkey = 'spacecp'.($op ? '_'.$op : '');
$navtitle = $_G['setting']['plugins'][$pluginkey][$_GET['id']]['name'];
$_GET['id'] = $_GET['id'] ? preg_replace("/[^A-Za-z0-9_:]/", '', $_GET['id']) : '';
include pluginmodule($_GET['id'], $pluginkey);
if(!$op || $op == 'credit') {
	include template('home/spacecp_plugin');
} elseif($op == 'profile') {
	$defaultop = '';
	$profilegroup = C::t('common_setting')->fetch('profilegroup', true);
	$operation = 'plugin';
	include template('home/spacecp_profile');
}

?>