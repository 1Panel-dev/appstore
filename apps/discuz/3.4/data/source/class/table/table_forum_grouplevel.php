<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_grouplevel.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_grouplevel extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_grouplevel';
		$this->_pk    = 'levelid';

		parent::__construct();
	}
	public function fetch_all_creditslower_order() {
		return DB::fetch_all("SELECT * FROM ".DB::table('forum_grouplevel')." WHERE 1 ORDER BY creditslower");
	}
	public function fetch_count() {
		return DB::result_first("SELECT count(*) FROM ".DB::table('forum_grouplevel'));
	}
	public function fetch_by_credits($credits = 0) {
		return DB::fetch_first("SELECT * FROM %t WHERE creditshigher<=%d AND %d<creditslower LIMIT 1", array($this->_table, $credits, $credits));
	}
}

?>