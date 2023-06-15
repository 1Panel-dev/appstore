<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_plugin.php 33362 2013-05-31 09:31:22Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginkey = 'space_'.$_GET['op'];
$_GET['id'] = $_GET['id'] ? preg_replace("/[^A-Za-z0-9_:]/", '', $_GET['id']) : '';
$navtitle = $_G['setting']['plugins'][$pluginkey][$_GET['id']]['name'];

include pluginmodule($_GET['id'], $pluginkey);
include template('home/space_plugin');

?>