<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_secquestion.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_secquestion extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_secquestion';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t'.DB::limit($start, $limit), array($this->_table));
	}

	public function delete_by_type($type) {
		DB::query('DELETE FROM %t WHERE type=%d', array($this->_table, $type));
	}

}

?>