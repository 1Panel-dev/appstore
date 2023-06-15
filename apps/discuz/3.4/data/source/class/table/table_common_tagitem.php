<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_tagitem.php 27769 2012-02-14 06:29:36Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_tagitem extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_tagitem';
		$this->_pk    = '';

		parent::__construct();
	}

	public function replace($tagid, $itemid, $idtype) {
		return DB::query('REPLACE INTO %t (tagid, itemid, idtype) VALUES (%d, %d, %s)', array($this->_table, $tagid, $itemid, $idtype));
	}

	public function select($tagid = 0, $itemid = 0, $idtype = '', $orderfield = '', $ordertype = 'DESC', $limit = 0, $count = 0, $itemidglue = '=', $returnnum = 0) {
		$data = self::make_where($tagid, $itemid, $idtype, $itemidglue);
		$ordersql = $limitsql = '';
		if($orderfield) {
			$ordersql = ' ORDER BY '.DB::order($orderfield, $ordertype);
		}
		if($limit || $count) {
			$limitsql = DB::limit($limit, $count);
		}
		if($data) {
			if($returnnum) {
				return DB::result_first('SELECT count(*) FROM %t WHERE '.$data['where'], $data['data']);
			}
			return DB::fetch_all('SELECT * FROM %t WHERE '.$data['where'].$ordersql.$limitsql, $data['data']);
		} else {
			return false;
		}
	}

	public function delete($tagid = 0, $itemid = 0, $idtype = '') {
		$data = self::make_where($tagid, $itemid, $idtype);
		if($data) {
			return DB::query('DELETE FROM %t WHERE '.$data['where'], $data['data']);
		} else {
			return false;
		}
	}

	private function make_where($tagid = 0, $itemid = 0, $idtype = '', $itemidglue = '=') {
		$wheresql = ' 1';
		$data = array();
		$data['data'][] = $this->_table;
		if($tagid) {
			$wheresql .= !is_array($tagid) ? " AND tagid=%d" : " AND tagid IN (%n)";
			$data['data'][] = $tagid;
		}
		if($itemid) {
			$wheresql .= !is_array($itemid) ? " AND ".DB::field('itemid', $itemid, $itemidglue) : " AND ".DB::field('itemid', $itemid);
		}
		if($idtype) {
			$wheresql .= " AND idtype=%s";
			$data['data'][] = $idtype;
		}
		if($wheresql == ' 1') {
			return false;
		}
		$data['where'] = $wheresql;
		return $data;
	}

	public function unique($tagid, $itemid, $idtype) {
		DB::query('DELETE FROM %t WHERE tagid<>%d AND itemid=%d AND idtype=%s', array($this->_table, $tagid, $itemid, $idtype));
	}

	public function merge_by_tagids($newid, $tagidarray) {
		if(!is_array($tagidarray)) {
			$tagidarray = array($tagidarray);
		}
		DB::query('UPDATE %t SET tagid=%d WHERE tagid IN (%n)', array($this->_table, $newid, $tagidarray));
	}

	public function count_by_tagid($tagid) {
		return DB::result_first("SELECT count(*) FROM ".DB::table('common_tagitem')." WHERE tagid='".intval($tagid)."'");
	}
}

?>