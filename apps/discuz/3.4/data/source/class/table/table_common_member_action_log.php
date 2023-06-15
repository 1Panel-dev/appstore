<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_action_log.php 28389 2012-02-28 10:27:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_action_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_action_log';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete_by_dateline($timestamp) {
		DB::delete($this->_table, 'dateline < '.dintval($timestamp));
	}

	public function count_day_hours($action, $uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE dateline>%d AND action=%d AND uid=%d', array($this->_table, TIMESTAMP - 86400, $action, $uid));
	}

	public function count_per_hour($uid, $type) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE dateline>%d AND `action`=%d AND uid=%d', array($this->_table, TIMESTAMP - 3600, getuseraction($type), $uid));
	}

	public function delete_by_uid($uids) {
		DB::delete($this->_table, 'uid IN ('.dimplode($uids).')');
	}
}

?>