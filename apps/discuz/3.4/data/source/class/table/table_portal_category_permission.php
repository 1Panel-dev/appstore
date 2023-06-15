<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_category_permission.php 27846 2012-02-15 09:04:33Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_category_permission extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_category_permission';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch($catid, $uid){
		return ($catid = dintval($catid)) && ($uid = dintval($uid)) ? DB::fetch_first('SELECT * FROM %t WHERE catid=%d AND uid=%d', array($this->_table, $catid, $uid)) : array();
	}

	public function fetch_all_by_catid($catid, $uid = 0) {
		return ($catid = dintval($catid)) ? DB::fetch_all('SELECT * FROM %t WHERE catid=%d'.($uid ? ' AND '.DB::field('uid', $uid) : ''), array($this->_table, $catid), 'uid') :array();
	}

	public function fetch_all_by_uid($uids, $flag = true, $sort = 'ASC', $start = 0, $limit = 0) {
		$wherearr = array();
		$sort = $sort === 'ASC' ? 'ASC' : 'DESC';
		if(($uids = dintval($uids, true))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedcatid = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY uid '.$sort.', inheritedcatid'.DB::limit($start, $limit), NULL, 'catid');
	}

	public function count_by_uids($uids, $flag) {
		$wherearr = array();
		if(($uids = dintval($uids))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedcatid = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).$where);
	}

	public function fetch_permission_by_uid($uids) {
		return ($uids = dintval($uids, true)) ? DB::fetch_all('SELECT uid, sum(allowpublish) as allowpublish, sum(allowmanage) as allowmanage FROM '.DB::table($this->_table)." WHERE uid IN (".dimplode($uids).") GROUP BY uid", null, 'uid') : array();
	}

	public function delete_by_catid_uid_inheritedcatid($catid = false, $uids = false, $inheritedcatid = false) {
		$wherearr = array();
		if(($catid = dintval($catid, true))) {
			$wherearr[] = DB::field('catid', $catid);
		}
		if(($uids = dintval($uids, true))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if($inheritedcatid === true) {
			$wherearr[] = "inheritedcatid>'0'";
		} elseif($inheritedcatid !== false && ($inheritedcatid = dintval($inheritedcatid, true))) {
			$wherearr[] = DB::field('inheritedcatid', $inheritedcatid);
		}
		return $wherearr ? DB::delete($this->_table, implode(' AND ', $wherearr)) : false;
	}

	public function insert_batch($users, $catids, $upid = 0) {
		$perms = array();
		if(!empty($users) && ($catids = dintval((array)$catids, true))) {
			foreach($users as $user) {
				$inheritedcatid = !empty($user['inheritedcatid']) ? $user['inheritedcatid'] : ($upid ? $upid : 0);
				foreach ($catids as $catid) {
					if($catid) {
						$perms[] = "('$catid','$user[uid]','$user[allowpublish]','$user[allowmanage]','$inheritedcatid')";
						$inheritedcatid = empty($inheritedcatid) ? $catid : $inheritedcatid;
					}
				}
			}
			if($perms) {
				DB::query('REPLACE INTO '.DB::table($this->_table).' (catid,uid,allowpublish,allowmanage,inheritedcatid) VALUES '.implode(',', $perms));
			}
		}
	}
}

?>