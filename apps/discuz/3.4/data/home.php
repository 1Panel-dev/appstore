<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home.php 32932 2013-03-25 06:53:01Z zhangguosheng $
 */

define('APPTYPEID', 1);
define('CURSCRIPT', 'home');

if(!empty($_GET['mod']) && ($_GET['mod'] == 'misc' || $_GET['mod'] == 'invite')) {
	define('ALLOWGUEST', 1);
}

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = C::app();

$cachelist = array('magic','usergroups', 'diytemplatenamehome');
$discuz->cachelist = $cachelist;
$discuz->init();

$space = array();

$mod = getgpc('mod');
if(!in_array($mod, array('space', 'spacecp', 'misc', 'magic', 'editor', 'invite', 'task', 'medal', 'rss', 'follow'))) {
	$mod = 'space';
	$_GET['do'] = 'home';
}

if($mod == 'space' && ((empty($_GET['do']) || $_GET['do'] == 'index') && ($_G['inajax']))) {
	$_GET['do'] = 'profile';
}
$curmod = !empty($_G['setting']['followstatus']) && (empty($_GET['diy']) && empty($_GET['do']) && $mod == 'space' || $_GET['do'] == 'follow') ? 'follow' : $mod;
define('CURMODULE', $curmod);
runhooks($_GET['do'] == 'profile' && $_G['inajax'] ? 'card' : $_GET['do']);

require_once libfile('home/'.$mod, 'module');


?>