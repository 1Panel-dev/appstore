<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_memberrecommend.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_memberrecommend extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_memberrecommend';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_by_recommenduid_tid($uid, $tid) {
		return DB::fetch_first('SELECT * FROM %t WHERE recommenduid=%d AND tid=%d', array($this->_table, $uid, $tid));
	}

	public function count_by_recommenduid_dateline($uid, $dateline) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE recommenduid=%d AND dateline>%d',array($this->_table, $uid, $dateline));
	}

}

?>