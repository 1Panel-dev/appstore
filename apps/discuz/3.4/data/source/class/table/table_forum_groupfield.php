<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_groupfield.php 27763 2012-02-14 03:42:56Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_groupfield extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_groupfield';
		$this->_pk    = 'fid';

		parent::__construct();
	}
	public function truncate() {
		DB::query("TRUNCATE ".DB::table('forum_groupfield'));
	}
	public function delete_by_type($types, $fid = 0) {
		if(empty($types)) {
			return false;
		}
		$addfid = $fid ? " AND fid='".intval($fid)."'" : '';
		DB::query("DELETE FROM ".DB::table('forum_groupfield')." WHERE ".DB::field('type', $types).$addfid);
	}
	public function fetch_all_group_cache($fid, $types = array(), $privacy = 0) {
		$typeadd = $types && is_array($types) ? "AND ".DB::field('type', $types) : '';
		return DB::fetch_all("SELECT fid, dateline, type, data FROM ".DB::table('forum_groupfield')." WHERE fid=%d AND privacy=%d $typeadd", array($fid, $privacy));
	}
}

?>