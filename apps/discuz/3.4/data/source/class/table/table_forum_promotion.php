<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_promotion.php 27863 2012-02-16 02:53:12Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_promotion extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_promotion';
		$this->_pk    = 'ip';

		parent::__construct();
	}

	public function count_by_uid($uid) {
		$uid = dintval($uid, is_array($uid) ? true : false);
		if(!empty($uid)) {
			$parameter = array($this->_table, $uid);
			$where = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
			return DB::result_first("SELECT COUNT(*) FROM %t WHERE $where", $parameter);
		}
		return 0;
	}
	public function delete_by_uid($uid) {
		return $uid ? DB::delete($this->_table, DB::field('uid', $uid)) : false;
	}
	public function delete_all() {
		return DB::query("DELETE FROM %t", array($this->_table));
	}

}

?>