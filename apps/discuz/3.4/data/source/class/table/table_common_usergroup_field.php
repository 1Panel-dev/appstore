<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_usergroup_field.php 28041 2012-02-21 07:33:55Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_usergroup_field extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_usergroup_field';
		$this->_pk    = 'groupid';

		parent::__construct();
	}

	public function fetch_readaccess_by_readaccess($readaccess) {
		return DB::fetch_all('SELECT groupid,readaccess FROM %t WHERE readaccess>%d ORDER BY readaccess', array($this->_table, $readaccess), $this->_pk);
	}

	public function fetch_all_fields($gid, $fields) {
		if(!is_array($fields) || !$fields) {
			return null;
		}
		foreach($fields as &$field) {
			$field = DB::quote_field($field);
		}
		$fieldssql = implode(',', $fields);
		return DB::fetch_all('SELECT %i FROM %t %i', array($fieldssql, $this->_table, ($gid ? 'WHERE '.DB::field('groupid', $gid) : '')), $this->_pk);
	}

	public function count_by_field($field, $val, $glue = '=') {
		$allowedfield = array('allowposttrade');
		if(!in_array($field, $allowedfield)) {
			return null;
		}
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, DB::field($field, $val, $glue)));
	}

	public function fetch_table_struct($result = 'FIELD') {
		$datas = array();
		$query = DB::query('DESCRIBE %t', array($this->_table));
		while($data = DB::fetch($query)) {
			$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
		}
		return $datas;
	}

	public function update_allowsearch() {
		return DB::query('UPDATE %t SET allowsearch = allowsearch | 2 WHERE groupid < 20 AND groupid NOT IN (5, 6)', array($this->_table));
	}

}

?>