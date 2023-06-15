<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_verify_info.php 31799 2012-10-11 02:36:34Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_verify_info extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_verify_info';
		$this->_pk    = 'vid';

		parent::__construct();
	}
	public function fetch_by_uid_verifytype($uid, $verifytype) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND verifytype=%d', array($this->_table, $uid, $verifytype));
	}
	public function fetch_all_search($uid, $vid, $flag = null, $username = '', $starttime = 0, $endtime = 0, $order = 'dateline', $start = 0, $limit = 0, $sort = 'DESC') {
		$condition = $this->search_condition($uid, $vid, $flag, $username, $starttime, $endtime);
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, $sort) : '';
		return DB::fetch_all("SELECT * FROM %t $condition[0] $ordersql ".DB::limit($start, $limit), $condition[1], $this->_pk);
	}
	public function group_by_verifytype_count() {
		return DB::fetch_all('SELECT verifytype, COUNT(*) AS num FROM %t WHERE flag=0 GROUP BY verifytype', array($this->_table));
	}

	public function delete_by_uid($uid, $verifytype = null) {
		if($uid) {
			$addsql = '';
			if($verifytype !== null) {
				$verifytype = dintval($verifytype, is_array($verifytype) ? true : false);
				$addsql = ' AND '.DB::field('verifytype', $verifytype);
			}
			return DB::query('DELETE FROM %t WHERE '.(is_array($uid) ? 'uid IN(%n)' : 'uid=%d').$addsql, array($this->_table, $uid));
		}
		return false;
	}

	public function count_by_search($uid, $vid, $flag = null, $username = '', $starttime = 0, $endtime = 0) {
		$condition = $this->search_condition($uid, $vid, $flag, $username, $starttime, $endtime);
		return DB::result_first('SELECT COUNT(*) FROM %t '.$condition[0], $condition[1]);
	}

	public function search_condition($uid, $vid, $flag, $username, $starttime, $endtime) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$parameter[] = $uid;
			$wherearr[] = 'uid=%d';
		}
		if($vid >= 0 && $vid < 8) {
			$parameter[] = $vid;
			$wherearr[] = 'verifytype=%d';
		}
		if($flag !== null) {
			$parameter[] = $flag;
			$wherearr[] = 'flag=%d';
		}
		if($starttime){
			$parameter[] = $starttime;
			$wherearr[] = 'dateline>=%d';
		}
		if($endtime){
			$parameter[] = $endtime;
			$wherearr[] = 'dateline<=%d';
		}
		if(!empty($username)) {
			$parameter[] = '%'.$username.'%';
			$wherearr[] = "username LIKE %s";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);

	}


}

?>