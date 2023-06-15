<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_optimizer.php 31034 2012-07-11 04:03:30Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_optimizer extends discuz_table {

	public function __construct() {

		$this->_table = 'common_optimizer';
		$this->_pk    = 'k';

		parent::__construct();
	}

	public function fetch($skey, $auto_unserialize = false) {
		$data = DB::result_first('SELECT v FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $skey));
		return $auto_unserialize ? (array)unserialize($data) : $data;
	}

	public function update($skey, $svalue){
		return DB::insert($this->_table, array($this->_pk => $skey, 'v' => is_array($svalue) ? serialize($svalue) : $svalue), false, true);
	}

	public function fetch_all($skeys) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $skeys));
	}
}
?>