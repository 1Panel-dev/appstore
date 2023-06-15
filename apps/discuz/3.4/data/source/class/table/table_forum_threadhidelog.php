<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadhidelog.php 33824 2013-08-19 08:26:11Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadhidelog extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_threadhidelog';
		$this->_pk    = '';

		parent::__construct();
	}

	public function insert($tid, $uid) {
		if(!DB::fetch_first('SELECT * FROM %t WHERE tid=%d AND uid=%d', array($this->_table, $tid, $uid))) {
			DB::insert($this->_table, array('tid' => $tid, 'uid' => $uid));
			DB::query("UPDATE %t SET hidden=hidden+1 WHERE tid=%d", array('forum_thread', $tid));
		}
	}

	public function resetshow($tid) {
		$this->delete_by_tid($tid);
		DB::update('forum_thread', array('hidden' => 0), DB::field('tid', $tid));
	}


	public function delete_by_uid($uid) {
		return $uid ? DB::delete($this->_table, DB::field('uid', $uid)) : false;
	}

	public function delete_by_tid($tid) {
		DB::query("UPDATE %t SET hidden=0 WHERE tid IN (%n)", array('forum_thread', $tid));
		return $tid ? DB::delete($this->_table, DB::field('tid', $tid)) : false;
	}

}

?>