<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_banned.php 27876 2012-02-16 04:28:02Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_banned extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_banned';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_by_ip($ip1, $ip2, $ip3, $ip4) {
		return DB::fetch_first('SELECT * FROM %t WHERE (ip1=%d OR ip1=-1) AND (ip2=%d OR ip2=-1) AND (ip3=%d OR ip3=-1) AND (ip4=%d OR ip4=-1)', array($this->_table, $ip1, $ip2, $ip3, $ip4));
	}

	public function fetch_all_order_dateline() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY dateline', array($this->_table));
	}

	public function fetch_all() {
		return DB::fetch_all('SELECT * FROM %t', array($this->_table));
	}

	public function delete_by_id($ids, $adminid, $adminname) {
		$ids = array_map('intval', (array)$ids);
		if($ids) {
			return DB::query('DELETE FROM %t WHERE id IN(%n) AND (1=%d OR `admin`=%s)', array($this->_table, $ids, $adminid, $adminname));
		}
		return 0;
	}

	public function delete_by_expiration($expiration) {
		return DB::query('DELETE FROM %t WHERE expiration<%d', array($this->_table, $expiration));
	}

	public function update_expiration_by_id($id, $expiration, $isadmin, $admin) {
		return DB::query('UPDATE %t SET expiration=%d WHERE id=%d AND (1=%d OR `admin`=%s)', array($this->_table, $expiration, $id, $isadmin, $admin));
	}

}

?>