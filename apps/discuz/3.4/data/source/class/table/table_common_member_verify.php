<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_verify.php 28405 2012-02-29 03:47:50Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_verify extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_verify';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'common_member_verify_';

		parent::__construct();
	}

	public function fetch_all_by_vid($vid, $flag, $uids = array()) {
		$parameter = array($this->_table);
		if($vid > 0 && $vid < 8) {
			$wherearr = array();
			if($uids) {
				$wherearr[] = is_array($uids) ? 'uid IN(%n)' : 'uid=%d';
				$parameter[] = $uids;
			}
			$parameter[] = $flag;
			$wherearr[] = "verify{$vid}=%d";
			return DB::fetch_all("SELECT * FROM %t WHERE ".implode(' AND ', $wherearr), $parameter, $this->_pk);
		} else {
			return array();
		}
	}
	public function fetch_all_search($uid, $vid, $username = '', $order = 'dateline', $start = 0, $limit = 0, $sort = 'DESC') {
		$condition = $this->search_condition($uid, $vid, $username);
		$ordersql = !empty($order) ? ' ORDER BY '.$order.' '.$sort : '';
		return DB::fetch_all("SELECT * FROM %t v, %t m  $condition[0] $ordersql ".DB::limit($start, $limit), $condition[1], $this->_pk);

	}

	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

	public function count_by_search($uid, $vid, $username = '') {
		$condition = $this->search_condition($uid, $vid, $username);
		return DB::result_first('SELECT COUNT(*) FROM %t v, %t m '.$condition[0], $condition[1]);
	}

	public function search_condition($uid, $vid, $username) {
		$parameter = array($this->_table, 'common_member');
		$wherearr = array();
		if($uid) {
			$parameter[] = $uid;
			$wherearr[] = 'v.uid=%d';
		}
		if($vid > 0 && $vid < 8) {
			$parameter[] = $vid;
			$wherearr[] = 'v.verify%d=1';
		}
		if(!empty($username)) {
			$parameter[] = '%'.$username.'%';
			$wherearr[] = "m.username LIKE %s";
		}
		$wherearr[] = "v.uid=m.uid";
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);

	}

}

?>