<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_patch.php 33690 2013-08-02 09:07:22Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['action'] == 'pluginnotice') {
	require_once libfile('function/admincp');
	require_once libfile('function/plugin');
	require_once libfile('function/cloudaddons');
	$pluginarray = C::t('common_plugin')->fetch_all_data();
	$addonids = $vers = array();
	foreach($pluginarray as $row) {
		if(ispluginkey($row['identifier'])) {
			$addonids[] = $row['identifier'].'.plugin';
			$vers[$row['identifier'].'.plugin'] = $row['version'];
		}
	}
	$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
	savecache('addoncheck_plugin', $checkresult);
	$newversion = 0;
	foreach($checkresult as $addonid => $value) {
		list(, $newver, $sysver) = explode(':', $value);
		if($sysver && $sysver > $vers[$addonid] || $newver) {
			$newversion++;
		}
	}
	include template('common/header_ajax');
	if($newversion) {
		$lang = lang('forum/misc');
		echo '<div class="bm"><div class="bm_h cl"><a href="javascript:;" onclick="$(\'plugin_notice\').style.display=\'none\';setcookie(\'pluginnotice\', 1, 86400)" class="y" title="'.$lang['patch_close'].'">'.$lang['patch_close'].'</a>';
		echo '<h2 class="i">'.$lang['plugin_title'].'</h2></div><div class="bm_c">';
		echo '<div class="cl bbda pbm">'.lang('forum/misc', 'plugin_memo', array('number' => $newversion)).'</div>';
		echo '<div class="ptn cl"><a href="admin.php?action=plugins" class="xi2 y">'.$lang['plugin_link'].' &raquo;</a></div>';
		echo '</div></div>';
	}
	include template('common/footer_ajax');
	exit;
} elseif($_GET['action'] == 'ipnotice') {
	require_once libfile('function/misc');
	include template('common/header_ajax');
	if($_G['cookie']['lip'] && $_G['cookie']['lip'] != ',' && $_G['uid'] && $_G['setting']['disableipnotice'] != 1) {
		$status = C::t('common_member_status')->fetch($_G['uid']);
		$lip = explode(',', $_G['cookie']['lip']);
		$lastipConvert = convertip($lip[0]);
		$lastipDate = dgmdate($lip[1]);
		$nowipConvert = convertip($status['lastip']);

		$lastipConvert = process_ipnotice($lastipConvert);
		$nowipConvert = process_ipnotice($nowipConvert);

		if($lastipConvert != $nowipConvert && stripos($lastipConvert, $nowipConvert) === false && stripos($nowipConvert, $lastipConvert) === false) {
			$lang = lang('forum/misc');
			include template('common/ipnotice');
		}
	}
	include template('common/footer_ajax');
	exit;
}



?>