<?php
/*
   [Discuz!] (C)2001-2099 Comsenz Inc.
   This is NOT a freeware, use is subject to license terms

   $Id: connect.php 26424 2011-12-13 03:02:20Z zhouxiaobo $
*/


if(isset($_GET['mod']) && $_GET['mod'] == 'register') {
	$_GET['mod'] = 'connect';
	$_GET['action'] = 'register';
	require_once 'member.php';
	exit;
}

define('APPTYPEID', 126);
define('CURSCRIPT', 'connect');
define('NOT_IN_MOBILE_API', 1);

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = C::app();

$mod = $discuz->var['mod'];
$discuz->init();

if(!in_array($mod, array('config', 'login', 'feed', 'check', 'user'))) {
	showmessage('undefined_action');
}

if(!$_G['setting']['connect']['allow']) {
	showmessage('qqconnect:qqconnect_closed');
}

define('CURMODULE', $mod);
runhooks();

require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Connect.php';
$connectService = new Cloud_Service_Connect();
require_once libfile('connect/'.$mod, 'plugin/qqconnect');
?>