<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_report.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_report extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_report';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_count($operated = 0, $id = 0, $fid = 0) {
		$where = empty($operated) ? 'opuid=0' : 'opuid>0';
		$idsql = $id ? DB::field('id', $id).' AND ' : '';
		$fidsql = $fid ? ' AND '.DB::field('fid', $fid) : '';
		return DB::result_first('SELECT count(*) FROM '.DB::table('common_report').' WHERE '.$idsql.$where.$fidsql);
	}

	public function fetch_all($start = 0, $limit = 100, $operated = 0, $fid = 0) {
		$where = empty($operated) ? 'opuid=0' : 'opuid>0';
		$order = empty($operated) ? 'num' : 'optime';
		$fidsql = $fid ? ' AND '.DB::field('fid', $fid) : '';
		return DB::fetch_all("SELECT * FROM %t WHERE $where.$fidsql ORDER BY $order DESC, dateline DESC LIMIT %d, %d", array($this->_table, $start, $limit));
	}

	public function fetch_by_urlkey($urlkey) {
		return DB::result_first("SELECT id FROM %t WHERE urlkey=%s AND opuid='0'", array($this->_table, $urlkey));
	}

	public function update_num($id, $message) {
		DB::query("UPDATE %t SET message=CONCAT_WS('<br>', message, %s), num=num+1 WHERE id=%d", array($this->_table, $message, $id));
	}
}

?>