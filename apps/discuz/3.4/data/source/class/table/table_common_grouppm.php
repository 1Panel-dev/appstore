<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_grouppm.php 27876 2012-02-16 04:28:02Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_grouppm extends discuz_table
{
	private $_uids = array();
	public function __construct() {

		$this->_table = 'common_grouppm';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_id_authorid($id = 0, $authorid = 0) {
		$wherearr = $data = array();
		$parameter = array($this->_table);
		if($id) {
			$id = is_array($id) ? array_map('intval', (array)$id) : dintval($id);
			$wherearr[] = is_array($id) ? 'id IN(%n)' : 'id=%d';
			$parameter[] = $id;
		}
		if($authorid) {
			$authorid = dintval($authorid);
			$wherearr[] = 'authorid=%d';
			$parameter[] = $authorid;
		}
		$wheresql = !empty($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		$query = DB::query("SELECT * FROM %t $wheresql ORDER BY id DESC", $parameter);
		while($row = DB::fetch($query)) {
			$data[$row['id']] = $row;
			$this->_uids[$row['authorid']] = $row['authorid'];
		}
		return $data;
	}

	public function count_by_id_authorid($id, $authorid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE id=%d AND authorid=%d', array($this->_table, $id, $authorid));
	}

	public function get_uids() {
		return $this->_uids;
	}

}

?>