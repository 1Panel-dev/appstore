<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_mobile_setting.php 31700 2012-09-24 03:46:59Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_setting extends discuz_table {

	public function __construct() {
		$this->_table = 'mobile_setting';
		$this->_pk = 'skey';

		parent::__construct();
	}

	public function fetch($skey) {
		return DB::result_first('SELECT svalue FROM %t WHERE skey=%s', array($this->_table, $skey));
	}

	public function fetch_all($skeyarr) {
		if(!empty($skeyarr)) {
			return array();
		}
		$return = array();
		$query = DB::query('SELECT * FROM %t WHERE '.DB::field($this->_pk, $skeyarr), array($this->_table));
		while($svalue = DB::fetch($query)) {
			$return[$svalue['skey']] = $svalue['svalue'];
		}
		return $return;
	}

}