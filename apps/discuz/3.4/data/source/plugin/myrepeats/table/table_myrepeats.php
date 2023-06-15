<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_myrepeats.php 31512 2012-09-04 07:11:08Z monkey $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_myrepeats extends discuz_table
{
	public function __construct() {

		$this->_table = 'myrepeats';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_uid($uid) {
		return DB::fetch_all("SELECT * FROM %t WHERE uid=%d", array($this->_table, $uid));
	}

	public function fetch_all_by_username($username) {
		return DB::fetch_all("SELECT * FROM %t WHERE username=%s", array($this->_table, $username));
	}

	public function fetch_all_by_uid_username($uid, $username) {
		return DB::fetch_all("SELECT * FROM %t WHERE uid=%d AND username=%s", array($this->_table, $uid, $username));
	}

	public function count_by_uid_username($uid, $username) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid=%d AND username=%s", array($this->_table, $uid, $username));
	}

	public function delete_by_uid_usernames($uid, $usernames) {
		DB::query("DELETE FROM %t WHERE uid=%d AND username IN (%n)", array($this->_table, $uid, $usernames));
	}

	public function update_comment_by_uid_username($uid, $username, $value) {
		DB::query("UPDATE %t SET comment=%s WHERE uid=%d AND username=%s", array($this->_table, $value, $uid, $username));
	}

	public function update_locked_by_uid_username($uid, $username, $value) {
		DB::query("UPDATE %t SET locked=%d WHERE uid=%d AND username=%s", array($this->_table, $value, $uid, $username));
	}

	public function update_logindata_by_uid_username($uid, $username, $value) {
		DB::query("UPDATE %t SET logindata=%s WHERE uid=%d AND username=%s", array($this->_table, $value, $uid, $username));
	}

	public function update_lastswitch_by_uid_username($uid, $username, $value) {
		DB::query("UPDATE %t SET lastswitch=%d WHERE uid=%d AND username=%s", array($this->_table, $value, $uid, $username));
	}

	public function count_by_search($condition) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE 1 %i", array($this->_table, $condition));
	}

	public function fetch_all_by_search($condition, $start, $ppp) {
		return DB::fetch_all("SELECT * FROM %t WHERE 1 %i ORDER BY uid LIMIT %d, %d", array($this->_table, $condition, $start, $ppp));
	}

}

?>