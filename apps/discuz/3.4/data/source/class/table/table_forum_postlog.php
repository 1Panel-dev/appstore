<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_postlog.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_postlog extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_postlog';
		$this->_pk    = '';

		parent::__construct();
	}
	public function delete_by_pid($pid) {
		return DB::delete($this->_table, DB::field('pid', $pid));
	}
	public function fetch_all_order_by_dateline($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY dateline '.DB::limit($start, $limit), array($this->_table));
	}

}

?>