<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_devicetoken.php 31700 2012-09-24 03:46:59Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_devicetoken extends discuz_table {

	public function __construct() {
		$this->_table = 'common_devicetoken';
		$this->_pk = 'token';

		parent::__construct();
	}

	public function loginToken($deviceToken, $uid) {
		return DB::insert($this->_table, array(
			'uid' => $uid,
			'token' => $deviceToken,
		), false, true);
	}

	public function logoutToken($deviceToken, $uid) {
		return DB::query('DELETE FROM %t WHERE uid=%d AND token=%s', array($this->_table, $uid, $deviceToken));
	}

	public function clearToken($deviceToken) {
		return DB::query('DELETE FROM %t WHERE token=%s', array($this->_table, $deviceToken));
	}

}