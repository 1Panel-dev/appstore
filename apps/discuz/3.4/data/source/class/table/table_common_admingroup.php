<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_admingroup.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_admingroup extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_admingroup';
		$this->_pk    = 'admingid';

		parent::__construct();
	}

	public function fetch_all_merge_usergroup($gids = array()) {
		$admingroups = empty($gids) ? $this->range() : $this->fetch_all($gids);
		$data = array();
		foreach(C::t('common_usergroup')->fetch_all(array_keys($admingroups)) as $gid=>$value) {
			$data[$gid] = array_merge($admingroups[$gid], $value);
		}
		return $data;
	}

	public function fetch_all_order() {
		return DB::fetch_all("SELECT u.radminid, u.groupid, u.grouptitle FROM ".DB::table('common_admingroup')." a LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid ORDER BY u.radminid, a.admingid");
	}
}

?>