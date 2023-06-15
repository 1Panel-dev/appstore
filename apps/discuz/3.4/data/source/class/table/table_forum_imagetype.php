<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_imagetype.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_imagetype extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_imagetype';
		$this->_pk    = 'typeid';

		parent::__construct();
	}

	public function fetch_all_by_type($type, $available = null) {
		$available = $available !== null ? ($available ? ' AND available=1' : ' AND available=0') : '';
		return DB::fetch_all("SELECT * FROM %t WHERE type=%s %i ORDER BY displayorder", array($this->_table, $type, $available));
	}

	public function fetch_all_available() {
		return DB::fetch_all("SELECT * FROM %t WHERE available=1", array($this->_table));
	}

	public function count_by_name($type, $name) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE type=%s AND name=%s", array($this->_table, $type, $name));
	}

}

?>