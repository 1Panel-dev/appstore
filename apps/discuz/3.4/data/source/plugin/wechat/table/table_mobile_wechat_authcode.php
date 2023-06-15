<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_mobile_wechat_authcode.php 34479 2014-05-07 00:40:10Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_wechat_authcode extends discuz_table {

	public function __construct() {
		$this->_table = 'mobile_wechat_authcode';
		$this->_pk = 'sid';

		parent::__construct();
	}

	public function fetch_by_code($code) {
		return DB::fetch_first('SELECT * FROM %t WHERE code=%d', array($this->_table, $code));
	}

	public function delete_history() {
		$time = TIMESTAMP - 3600;
		return DB::delete($this->_table, "createtime<$time");
	}

}