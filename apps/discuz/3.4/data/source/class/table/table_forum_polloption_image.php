<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_polloption_image.php 31112 2012-07-17 09:16:21Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class table_forum_polloption_image extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_polloption_image';
		$this->_pk    = 'aid';
		parent::__construct();
	}
	public function fetch_all_by_tid($tids) {
		return DB::fetch_all('SELECT * FROM %t WHERE tid'.(is_array($tids) ? ' IN(%n)' : '=%d'), array($this->_table, $tids), 'poid');
	}
	public function count_by_aid_uid($aid, $uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE aid=%d AND uid=%d', array($this->_table, $aid, $uid));
	}
	public function delete_by_tid($tids) {
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

	public function clear() {
		require_once libfile('function/forum');
		$deltids = array();
		$query = DB::query("SELECT tid, attachment, thumb FROM %t WHERE tid=0 AND dateline<=%d", array($this->_table, TIMESTAMP - 86400));
		while($attach = DB::fetch($query)) {
			dunlink($attach);
			$deltids[] = $attach['tid'];
		}
		if($deltids) {
			$this->delete_by_tid($deltids);
		}
	}
}

?>