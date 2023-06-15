<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_connect_tthreadlog.php 27640 2012-02-08 09:48:47Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_connect_feedlog extends discuz_table {

	public function __construct() {
		$this->_table = 'connect_feedlog';
		$this->_pk = 'flid';

		parent::__construct();
	}

	public function fetch_by_tid($tid) {

		return DB::fetch_first('SELECT * FROM %t WHERE tid=%d', array($this->_table, $tid));
	}

	public function update_by_tid($tid, $data) {
		$tid = dintval($tid);
		return DB::update($this->_table, $data, DB::field('tid', $tid));
	}
}