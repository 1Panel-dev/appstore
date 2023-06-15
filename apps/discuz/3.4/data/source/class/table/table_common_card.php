<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_card.php 27846 2012-02-15 09:04:33Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_card extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_card';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function update_by_typeid($typeid, $data) {
		if(($typeid = dintval($typeid, true)) && !empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, DB::field('typeid', $typeid));
		}
		return false;
	}

	public function count_by_where($where) {
		return ($where = (string)$where) ? DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_card').' WHERE '.$where) : 0;
	}

	public function fetch_all_by_where($where, $start = 0, $limit = 0) {
		$where = $where ? ' WHERE '.(string)$where : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY dateline DESC'.DB::limit($start, $limit));
	}

	public function update_to_overdue($timestamp) {
		return ($timestamp = dintval($timestamp)) ? DB::query('UPDATE '.DB::table('common_card')." SET status = 9 WHERE status = '1' AND cleardateline <= '$timestamp'") : false;
	}

	public function update_to_used($id) {
		global $_G;
		return DB::query('UPDATE '.DB::table('common_card')." SET status = '2', uid = '".$_G['uid']."', useddateline = '".$_G['timestamp']."' WHERE id = '".daddslashes($id)."' AND status = '1'");
	}

}

?>