<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_show.php 29635 2012-04-23 09:00:27Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_show extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_show';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	public function count_by_credit($unitprice = false) {
		$args = array($this->_table);
		if($unitprice !== false) {
			$sql = 'AND unitprice>=%d';
			$args[] = $unitprice;
		}
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE credit>0 {$sql}", $args);
	}

	public function fetch_by_uid_credit($uid) {
		return DB::fetch_first('SELECT unitprice, credit FROM %t WHERE uid=%d AND credit>0', array($this->_table, $uid));
	}

	public function fetch_all_by_unitprice($start, $perpage, $selectall = false) {
		$selectfields = $selectall ? '*' : 'uid, username, unitprice, credit AS show_credit, note AS show_note';
		return DB::fetch_all("SELECT {$selectfields} FROM %t ORDER BY unitprice DESC, credit DESC %i", array($this->_table, DB::limit($start, $perpage)));
	}

	public function fetch_all_by_credit($start, $perpage) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY credit DESC %i', array($this->_table, DB::limit($start, $perpage)));
	}

	public function delete_by_credit($credit = 1) {
		return DB::query('DELETE FROM %t WHERE %i', array($this->_table, DB::field('credit', intval($credit), '<')));
	}
	public function delete_by_uid($uids) {
		if(!$uids) {
			return null;
		}
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

	public function update_credit_by_uid($uid, $inc_credit, $limit_credit = true, $unitprice = false, $note = false) {
		$args = array($this->_table, $inc_credit);

		if($limit_credit === true) {
			$sql = ' AND credit>0';
		}
		if($unitprice !== false) {
			$args[] = $unitprice;
			$set_sql = ', unitprice=%d';
		}

		if($note !== false) {
			$args[] = $note;
			$set_sql .= ', note=%s';
		}
		$args[] = $uid;
		return DB::query("UPDATE %t SET credit=credit+'%d' {$set_sql} WHERE uid=%d {$sql}", $args);
	}

}

?>