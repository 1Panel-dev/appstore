<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_advertisement.php 33658 2013-07-29 06:25:15Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_advertisement extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_advertisement';
		$this->_pk    = 'advid';

		parent::__construct();
	}

	public function fetch_all_type() {
		return DB::fetch_all("SELECT type, COUNT(type) AS count FROM %t GROUP BY type", array($this->_table));
	}

	public function fetch_all_by_type($type) {
		return DB::fetch_all("SELECT * FROM %t WHERE type=%s", array($this->_table, $type));
	}

	public function fetch_all_old() {
		return DB::fetch_all("SELECT * FROM %t WHERE available>0 AND starttime<=%d ORDER BY displayorder", array($this->_table, TIMESTAMP));
	}

	public function close_endtime() {
		$return = DB::result_first("SELECT COUNT(*) FROM %t WHERE endtime>0 AND endtime<='".TIMESTAMP."'", array($this->_table));
		DB::update($this->_table, array('available' => 0), "endtime>0 AND endtime<='".TIMESTAMP."'", 'UNBUFFERED');
		return $return;
	}

	public function fetch_all_endtime($endtime) {
		return DB::fetch_all("SELECT * FROM %t WHERE endtime=%s", array($this->_table, $endtime));
	}

	private function _search_conditions($title, $starttime, $endtime, $type, $target) {
		$conditions = '';
		$conditions .= $title ? " AND ".DB::field('title', '%'.$title.'%', 'like') : '';
		$conditions .= $starttime > 0 ? " AND starttime>='".(TIMESTAMP - intval($starttime))."'" : ($starttime == -1 ? " AND starttime='0'" : '');
		$conditions .= $endtime > 0 ? " AND endtime>0 AND endtime<'".(TIMESTAMP + intval($endtime))."'" : ($endtime == -1 ? " AND endtime='0'" : '');
		$conditions .= $type ? " AND ".DB::field('type', $type) : '';
		$conditions .= $target ? " AND ".DB::field('targets', '%'.$target.'%', 'like') : '';
		return $conditions;
	}

	public function fetch_all_search($title, $starttime, $endtime, $type, $target, $orderby, $start_limit, $advppp) {
		$conditions = $this->_search_conditions($title, $starttime, $endtime, $type, $target);
		$order_by = $orderby == 'starttime' ? 'starttime' : ($orderby == 'type' ? 'type' : ($orderby == 'displayorder' ? 'displayorder' : 'advid DESC'));
		$start_limit = intval($start_limit);
		$advppp = intval($advppp);

		return DB::fetch_all("SELECT * FROM ".DB::table('common_advertisement')." WHERE 1 $conditions ORDER BY available DESC, $order_by LIMIT $start_limit, $advppp");
	}

	public function count_search($title, $starttime, $endtime, $type, $target) {
		$conditions = $this->_search_conditions($title, $starttime, $endtime, $type, $target);
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_advertisement')." WHERE 1 $conditions");
	}

}

?>