<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_announcement.php 27829 2012-02-15 07:34:43Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_announcement extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_announcement';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_date($timestamp, $type = 2) {
		return DB::fetch_all('SELECT * FROM %t WHERE type!=%d AND starttime<=%d AND (endtime=0 OR endtime>%d) ORDER BY displayorder, starttime DESC, id DESC', array($this->_table, $type, $timestamp, $timestamp), $this->_pk);
	}

	public function fetch_all_by_displayorder() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY displayorder, starttime DESC, id DESC', array($this->_table), $this->_pk);
	}

	public function fetch_by_displayorder($timestamp) {
		return DB::fetch_first('SELECT * FROM %t WHERE type!=2 AND `groups` = \'\' AND starttime<=%d AND (endtime>=%d OR endtime=0) ORDER BY displayorder, starttime DESC, id DESC LIMIT 1', array($this->_table, $timestamp, $timestamp));
	}

	public function fetch_all_by_time($time, $type, $bannedids, $startrow, $items) {
		$type = dintval($type, true);
		$sql = ' AND '.DB::field('type', $type);
		if($bannedids) {
			$bannedids = dintval($bannedids, true);
			$sql .= ' AND '.DB::field('id', $bannedids, 'notin');
		}
		return DB::fetch_all('SELECT * FROM %t WHERE starttime <= %d AND (endtime = \'\' || endtime >= %d) %i ORDER BY displayorder DESC LIMIT %d, %d', array($this->_table, $time, $time, $sql, $startrow, $items), $this->_pk);
	}

	public function fetch_by_id_username($id, $username, $adminid = 1) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND (%d=1 OR author=%s)', array($this->_table, $id, $adminid, $username));
	}

	public function delete_by_id_username($ids, $username, $adminid = 1) {
		if(($ids = dintval((array)$ids, true))) {
			DB::query('DELETE FROM %t WHERE id IN(%n) AND (%d=1 OR author=%s)', array($this->_table, $ids, $adminid, $username), false, true);
		}
	}

	public function update_displayorder_by_id_username($id, $displayorder, $username, $adminid = 1) {
		if(($id = dintval((array)$id, true))) {
			DB::query('UPDATE %t SET displayorder=%d WHERE id IN(%n) AND (%d=1 OR author=%s)', array($this->_table, $displayorder, $id, $adminid, $username), false, true);
		}
	}

	public function update_by_id_username($id, $data, $username, $adminid = 1) {
		if(($id = dintval($id, true)) && $data && is_array($data)) {
			$adminid = dintval($adminid);
			DB::update($this->_table, $data, DB::field($this->_pk, $id)." AND ('{$adminid}'=1 OR ".DB::field('author', $username).')', true);
		}
	}

	public function delete_all_by_endtime($timestamp) {
		DB::query("DELETE FROM %t WHERE endtime<%d AND endtime<>'0'", array($this->_table, $timestamp));
	}

}

?>