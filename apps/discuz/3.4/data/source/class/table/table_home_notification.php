<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_notification.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_notification extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_notification';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete_clear($new, $days) {
		$days = TIMESTAMP - intval($days) * 86400;
		DB::query("DELETE FROM %t WHERE new=%d AND dateline<%d", array($this->_table, $new, $days));
	}
	public function delete_by_type($type, $uid = 0) {
		if(!$type) {
			return;
		}
		$uid = $uid ? ' AND '.DB::field('uid', $uid) : '';
		return DB::query("DELETE FROM %t WHERE type=%s %i", array($this->_table, $type, $uid));
	}

	public function optimize() {
		DB::query("OPTIMIZE TABLE %t", array($this->_table), true);
	}

	public function fetch_by_fromid_uid($id, $idtype, $uid) {
		return DB::fetch_first("SELECT * FROM %t WHERE from_id=%d AND from_idtype=%s AND uid=%d", array($this->_table, $id, $idtype, $uid));
	}

	public function delete_by_id_uid($id, $uid) {
		DB::query("DELETE FROM %t WHERE id=%d AND uid=%d", array($this->_table, $id, $uid));
	}

	public function delete_by_uid($uid) {
		DB::query("DELETE FROM %t WHERE uid IN (%n) OR authorid IN (%n)", array($this->_table, $uid, $uid));
	}

	public function delete_by_uid_type_authorid($uid, $type, $authorid) {
		return DB::query('DELETE FROM %t WHERE uid=%d AND type=%s AND authorid=%d', array($this->_table, $uid, $type, $authorid));
	}

	public function fetch_all_by_authorid_fromid($authorid, $fromid, $type) {
		return DB::fetch_all("SELECT * FROM %t WHERE authorid=%d AND from_id=%d AND type=%s", array($this->_table, $authorid, $fromid, $type));
	}

	public function ignore($uid, $type = '', $category = '', $new = true, $from_num = true) {
		$uid = intval($uid);
		$update = array();
		if($new) {
			$update['new'] = 0;
		}
		if($from_num) {
			$update['from_num'] = 0;
		}
		$where = array('uid' => $uid, 'new' => 1);
		if($type) {
			$where['type'] = $type;
		}
		if($category !== '') {
			switch ($category) {
						case 'mypost' : $category = 1; break;
						case 'interactive' : $category = 2; break;
						case 'system' : $category = 3; break;
						case 'manage' : $category = 4; break;
						default :  $category = 0;
					}
			$where['category'] = $category;
		}
		if($update) {
			DB::update($this->_table, $update, $where);
		}
	}

	public function count_by_uid($uid, $new, $type = '', $category = '') {
		$new = intval($new);
		$type = $type ? ' AND '.DB::field('type', $type) : '';
		if($category !== '') {
			switch ($category) {
						case 'mypost' : $category = 1; break;
						case 'interactive' : $category = 2; break;
						case 'system' : $category = 3; break;
						case 'manage' : $category = 4; break;
						default :  $category = 0;
					}
			$category  = ' AND '.DB::field('category', $category);
		}
		$new = $new != '-1' ? ' AND '.DB::field('new', $new) : '';
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid=%d %i %i %i", array($this->_table, $uid, $new, $category, $type));
	}

	public function fetch_all_by_uid($uid, $new, $type = 0, $start = 0, $perpage = 0, $category = '') {
		$new = intval($new);
		$type = $type ? ' AND '.DB::field('type', $type) : '';
		if($category !== '') {
			switch ($category) {
						case 'mypost' : $category = 1; break;
						case 'interactive' : $category = 2; break;
						case 'system' : $category = 3; break;
						case 'manage' : $category = 4; break;
						case 'follow' : $category = 5; break;
						case 'follower' : $category = 6; break;
						default :  $category = 0;
					}
			$category  = ' AND '.DB::field('category', $category);
		}
		$new = $new != '-1' ? ' AND '.DB::field('new', $new) : '';
		return DB::fetch_all("SELECT * FROM %t WHERE uid=%d %i %i %i ORDER BY dateline DESC %i", array($this->_table, $uid, $new, $category, $type, DB::limit($start, $perpage)));
	}
}

?>