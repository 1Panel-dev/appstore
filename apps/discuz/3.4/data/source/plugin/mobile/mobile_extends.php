<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile_extends.php 31700 2012-09-24 03:46:59Z zhangjie $
 */


if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['identifier'] = !empty($_GET['identifier']) ? $_GET['identifier'] : '' ;
$_GET['check'] = !empty($_GET['check']) ? $_GET['check'] : '' ;
require_once './source/class/class_core.php';

if(empty($_GET['identifier']) && !empty($_GET['check'])) {
	require_once 'extends/mobile_extends_check.php';
} else {
	require_once 'extends/mobile_extends_list.php';
}


C::app()->init();
define('HOOKTYPE', 'hookscript');
hookscript('common', 'global');
hookscript('global', 'global');

?>