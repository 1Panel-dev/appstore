<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_mobile_wsq_threadlist.php 34398 2014-04-14 07:11:22Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_wsq_threadlist extends discuz_table {

	public function __construct() {
		$this->_table = 'mobile_wsq_threadlist';
		$this->_pk = 'skey';
		$this->_pre_cache_key = 'wsq_threadlist_';
		$this->_cache_ttl = 0;

		parent::__construct();
	}

	public function insert($tid, $data, $return_insert_id = false, $replace = false, $silent = false) {
		if($this->_allowmem) {
			$this->store_cache($tid, $data);
		}
		return DB::insert($this->_table, $data, $return_insert_id, $replace, $silent);
	}
}