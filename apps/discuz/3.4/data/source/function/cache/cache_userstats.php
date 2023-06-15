<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_userstats.php 26680 2011-12-20 01:05:48Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_userstats() {
	global $_G;
	$totalmembers = C::t('common_member')->count();
	$member = C::t('common_member')->range(0, 1, 'DESC');
	$member = current($member);
	$newsetuser = $member['username'];
	$data = array('totalmembers' => $totalmembers, 'newsetuser' => $newsetuser);
	if($_G['setting']['plugins']['func'][HOOKTYPE]['cacheuserstats']) {
		$_G['userstatdata'] = & $data;
		hookscript('cacheuserstats', 'global', 'funcs', array(), 'cacheuserstats');
	}
	savecache('userstats', $data);
}

?>