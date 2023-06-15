<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_plugin.php 31344 2012-08-15 04:01:32Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_plugin {

	public function __construct() {

	}

	public function check() {
		require_once libfile('function/admincp');
		require_once libfile('function/plugin');
		require_once libfile('function/cloudaddons');
		$pluginarray = C::t('common_plugin')->fetch_all_data();
		$addonids = array();
		foreach($pluginarray as $row) {
			if(ispluginkey($row['identifier'])) {
				$addonids[] = $row['identifier'].'.plugin';
			}
		}
		$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
		savecache('addoncheck_plugin', $checkresult);
		$newversion = 0;
		foreach($checkresult as $value) {
			list(, $newver) = explode(':', $value);
			if($newver) {
				$newversion++;
			}
		}

		if($newversion) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_plugin_new_plugin', array('newversion' => $newversion)));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_plugin_no_upgrade'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.getglobal('siteurl').$adminfile.'?action=plugins');
	}
}

?>