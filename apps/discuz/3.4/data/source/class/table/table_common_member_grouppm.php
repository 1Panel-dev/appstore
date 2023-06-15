<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_grouppm.php 31733 2012-09-26 02:07:47Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_grouppm extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_grouppm';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete_by_gpmid($gpmid) {
		return ($gpmid = dintval($gpmid))? DB::delete('common_member_grouppm', 'gpmid='.$gpmid) : false;
	}

	public function count_by_gpmid($gpmid, $type) {
		return $gpmid ? DB::result_first('SELECT COUNT(*) FROM %t WHERE gpmid=%d AND dateline'.($type ? '>' : '=').'0', array($this->_table, $gpmid)) : 0;
	}

	public function fetch($uid, $gpmid) {
		return $uid && $gpmid ? DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND gpmid=%d', array($this->_table, $uid, $gpmid)) : '';
	}

	public function fetch_all_by_gpmid($gpmid, $type, $start = 0, $limit = 0) {
		return $gpmid ? DB::fetch_all('SELECT * FROM %t WHERE gpmid=%d AND dateline'.($type ? '>' : '=').'0'.DB::limit($start, $limit), array($this->_table, $gpmid), 'uid') : array();
	}

	public function fetch_all_by_uid($uid, $type) {
		return $uid ? DB::fetch_all('SELECT * FROM %t WHERE uid=%d AND `status`'.($type ? '>=' : '=').'0', array($this->_table, $uid), 'gpmid') : array();
	}

	public function update($uid, $gpmid, $data) {
		return ($uid = dintval($uid)) && ($gpmid = dintval($gpmid, true)) && $data && is_array($data) ? DB::update($this->_table, $data, DB::field('gpmid', $gpmid).' AND '.DB::field('uid', $uid)) : false;
	}

	public function update_to_read_by_unread($uid, $gpmid) {
		return ($uid = dintval($uid)) && ($gpmid = dintval($gpmid, true)) ? DB::update($this->_table, array('status' => 1), DB::field('gpmid', $gpmid).' AND '.DB::field('uid', $uid).' AND status=0') : false;
	}
}

?>