<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_credit_rule_log_field.php 27777 2012-02-14 07:07:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_credit_rule_log_field extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_credit_rule_log_field';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete_clid($val) {
		DB::delete($this->_table, DB::field('clid', $val));
	}

	public function delete_by_uid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

	public function update($uid, $clid, $data) {
		if(!empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, array('uid'=>$uid, 'clid'=>$clid));
		}
		return 0;
	}

	public function fetch($uid, $clid) {
		$logarr = array();
		if($uid && $clid) {
			$logarr = DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND clid=%d', array($this->_table, $uid, $clid));
		}
		return !empty($logarr) ? $logarr : array();
	}
}

?>