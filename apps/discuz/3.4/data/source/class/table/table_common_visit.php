<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_visit.php 30814 2012-06-21 06:37:56Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_visit extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_visit';
		$this->_pk    = 'ip';

		parent::__construct();
	}

	public function inc($ip, $viewadd = 1) {
		return DB::query('UPDATE %t SET view=view+(%d) WHERE `ip`=%s', array($this->_table, $viewadd, $ip));

	}

	public function range($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' ORDER BY view DESC'.DB::limit($start, $limit), $this->_pk);
	}
}

?>