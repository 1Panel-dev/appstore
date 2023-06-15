<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_adminnote.php 31558 2012-09-10 03:22:31Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_adminnote extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_adminnote';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete($id, $admin = '') {
		if(empty($id)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('id', $id).' %i', array($this->_table, ($admin ? ' AND '.DB::field('admin', $admin) : '')));
	}

	public function fetch_all_by_access($access) {
		if(!is_numeric($access) && !is_array($access)) {
			return array();
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('access', $access).' ORDER BY dateline DESC', array($this->_table));
	}

	public function count_by_access($access) {
		if(!is_numeric($access) && !is_array($access)) {
			return 0;
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.DB::field('access', $access), array($this->_table));
	}

}

?>