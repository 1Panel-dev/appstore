<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_specialuser.php 28772 2012-03-12 09:21:59Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_specialuser extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_specialuser';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_status($status, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE status=%d ORDER BY displayorder'.DB::limit($start, $limit), array($this->_table, $status), 'uid');
	}

	public function count_by_status($status, $username = '') {
		$addsql = $username ? " AND username='".addslashes($username)."' " : '';
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE status=%d'.$addsql, array($this->_table, $status));
	}

	public function update_by_uid_status($uid, $status, $data) {
		if(!empty($data) && is_array($data) && ($uid = dintval($uid))) {
			return DB::update($this->_table, $data, array('uid' => $uid, 'status' => dintval($status)));
		}
		return 0;
	}

	public function delete_by_uid_status($uid, $status) {
		return ($uid = dintval($uid, true)) ? DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('status', dintval($status))) : false;
	}

	public function fetch_by_uid_status($uid, $status) {
		return ($uid = dintval($uid, true)) ? DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND status=%d', array($this->_table, $uid, $status)) : array();
	}
}

?>