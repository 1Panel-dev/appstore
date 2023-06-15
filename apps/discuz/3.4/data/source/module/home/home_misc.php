<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_misc.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ac = empty($_GET['ac']) ? '' : $_GET['ac'];
$acs = isset($_G['group']['allowvisit']) && $_G['group']['allowvisit'] ? array('swfupload', 'inputpwd', 'ajax', 'sendmail', 'emailcheck') : array('swfupload', 'sendmail', 'emailcheck');

if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'home.php?mod=space');
}

$theurl = 'home.php?mod=misc&ac='.$ac;
require_once libfile('misc/'.$ac, 'include');

?>