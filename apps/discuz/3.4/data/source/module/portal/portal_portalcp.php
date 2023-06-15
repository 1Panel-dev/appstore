<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_portalcp.php 28492 2012-03-01 10:05:07Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['disabledwidthauto'] = 0;

$ac = in_array($_GET['ac'], array('comment', 'article', 'related', 'block', 'portalblock', 'blockdata', 'topic', 'diy', 'upload', 'category', 'plugin', 'logout'))?$_GET['ac']:'index';

$admincp2 = getstatus($_G['member']['allowadmincp'], 2);
$admincp3 = getstatus($_G['member']['allowadmincp'], 3);
$admincp4 = getstatus($_G['member']['allowadmincp'], 4);
$admincp5 = getstatus($_G['member']['allowadmincp'], 5);
$admincp6 = getstatus($_G['member']['allowadmincp'], 6);

if (!$_G['inajax'] && in_array($ac, array('index', 'portalblock', 'blockdata', 'category', 'plugin')) && ($_G['group']['allowdiy'] || $_G['group']['allowmanagearticle'] || $admincp2 || $admincp3 || $admincp4 || $admincp6)) {
	$modsession = new discuz_panel(PORTALCP_PANEL);
	if(getgpc('login_panel') && getgpc('cppwd') && submitcheck('submit')) {
		$modsession->dologin($_G[uid], getgpc('cppwd'), true);
	}

	if(!$modsession->islogin) {
		include template('portal/portalcp_login');
		dexit();
	}
}

if($ac == 'logout') {
	$modsession = new discuz_panel(PORTALCP_PANEL);
	$modsession->dologout();
	showmessage('modcp_logout_succeed', 'index.php');
}

$navtitle = lang('core', 'title_'.$ac.'_management').' - '.lang('core', 'title_portal_management');

require_once libfile('function/portalcp');
require_once libfile('portalcp/'.$ac, 'include');
?>