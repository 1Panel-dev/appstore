<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_forumrecommend.php 27745 2012-02-14 01:43:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_forumrecommend extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_forumrecommend';
		$this->_pk    = 'tid';

		parent::__construct();
	}

	public function delete_by_fid($fids, $moderatorid = false) {
		if(!$fids) {
			return;
		}
		$moderatorid = $moderatorid !== false ? ' AND moderatorid='.intval($moderatorid) : '';
		DB::query("DELETE FROM %t WHERE %i %i", array($this->_table, DB::field('fid', $fids), $moderatorid));
	}

	public function delete_by_tid($tids) {
		if(!$fids) {
			return;
		}
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

	public function delete_old() {
		DB::query("DELETE FROM %t WHERE expiration>0 AND expiration<%d", array($this->_table, TIMESTAMP), false, true);
	}

	public function fetch_all_by_fid($fid, $position = false, $moderatorid = false, $start = 0, $limit = 0) {
		$position = $position ? ' AND '.DB::field('position', array(0, $position)) : '';
		$moderatorid = $moderatorid ? ' AND '.DB::field('moderatorid', array(0, $moderatorid)) : '';
		$limit = $start && $limit ? ' LIMIT '.intval($start).', '.intval($limit) : '';
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d %i %i ORDER BY displayorder %i', array($this->_table, $fid, $position, $moderatorid, $limit));
	}

	public function count_by_fid($fid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE fid=%d", array($this->_table, $fid));
	}

}

?>