<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_seccheck.php 33625 2013-07-19 06:03:49Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_secwhite extends discuz_table
{

	public function __construct() {

		$this->_table = 'common_member_secwhite';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	public function check($uid) {
		DB::delete($this->_table, "dateline<".(TIMESTAMP-86400));
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid=%d", array($this->_table, $uid));
	}

	public function add($uid) {
		DB::insert($this->_table, array('uid' => $uid, 'dateline' => TIMESTAMP), false, true);
	}

}

?>