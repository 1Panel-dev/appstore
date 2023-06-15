<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_magic.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_magic extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_magic';
		$this->_pk    = 'magicid';

		parent::__construct();
	}

	public function fetch_all_data($available = false) {
		$available = $available !== false ? ' WHERE available='.intval($available) : '';
		return DB::fetch_all('SELECT * FROM %t %i ORDER BY displayorder', array($this->_table, $available));
	}

	public function check_identifier($identifier, $magicid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE identifier=%s AND magicid!=%d", array($this->_table, $identifier, $magicid));
	}

	public function count_page($operation) {
		$salevolume = $operation == 'index' ? '' : 'AND salevolume>0';
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE available=1 %i', array($this->_table, $salevolume));
	}

	public function fetch_all_page($operation, $start, $limit) {
		if($operation == 'index') {
			$salevolume = '';
			$orderby = "displayorder, magicid";
		} else {
			$salevolume = 'AND salevolume>0';
			$orderby = "salevolume DESC, magicid";
		}
		return DB::fetch_all('SELECT * FROM %t WHERE available=1 %i ORDER BY %i LIMIT %d,%d', array($this->_table, $salevolume, $orderby, $start, $limit));
	}

	public function fetch_all_name_by_available($available = 1) {
		return DB::fetch_all('SELECT magicid, name FROM %t WHERE available=%d ORDER BY displayorder', array($this->_table, $available), $this->_pk);
	}

	public function fetch_by_identifier($identifier) {
		return DB::fetch_first('SELECT * FROM %t WHERE identifier=%s', array($this->_table, $identifier));
	}

	public function update_salevolume($magicid, $number) {
		DB::query("UPDATE %t SET num=num+('-%d'), salevolume=salevolume+'%d' WHERE magicid=%d", array($this->_table, $number, $number, $magicid));
	}

	public function fetch_member_magic($uid, $identifier) {
		return DB::fetch_first('SELECT mm.* FROM %t cm INNER JOIN %t mm ON mm.uid=%d AND mm.magicid=cm.magicid WHERE cm.identifier=%s', array($this->_table, 'common_member_magic', $uid, $identifier));
	}

}

?>