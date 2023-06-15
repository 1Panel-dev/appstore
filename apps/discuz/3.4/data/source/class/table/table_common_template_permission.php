<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_template_permission.php 27830 2012-02-15 07:39:23Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_template_permission extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_template_permission';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_targettplname($targettplname) {
		return DB::fetch_all('SELECT * FROM %t WHERE targettplname=%s ORDER BY inheritedtplname', array($this->_table, $targettplname), 'uid');
	}

	public function fetch_all_by_uid($uids, $flag = true, $sort = 'ASC', $start = 0, $limit = 0) {
		$wherearr = array();
		$sort = $sort === 'ASC' ? 'ASC' : 'DESC';
		if(($uids = dintval($uids, true))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedtplname = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY uid '.$sort.', inheritedtplname'.DB::limit($start, $limit), NULL, 'targettplname');
	}

	public function count_by_uids($uids, $flag) {
		$wherearr = array();
		if(($uids = dintval($uids, true))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedtplname = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).$where);
	}

	public function delete_by_targettplname_uid_inheritedtplname($targettplname = false, $uids = false, $inheritedtplname = false) {
		$wherearr = array();
		if($targettplname) {
			$wherearr[] = DB::field('targettplname', $targettplname);
		}
		if(($uids = dintval($uids, true))) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if($inheritedtplname === true) {
			$wherearr[] = "inheritedtplname!=''";
		} elseif($inheritedtplname !== false && is_string($inheritedtplname)) {
			$wherearr[] = DB::field('inheritedtplname', $inheritedtplname);
		}
		return $wherearr ? DB::delete($this->_table, implode(' AND ', $wherearr)) : false;
	}


	public function insert_batch($users, $templates, $uptplname = '') {
		$blockperms = array();
		if(!empty($users) && !empty($templates)){
			if(!is_array($templates)) {
				$templates = array($templates);
			}
			foreach($users as $user) {
				$inheritedtplname = $uptplname ? $uptplname : '';
				foreach ($templates as $tpl) {
					if($tpl) {
						$blockperms[] = "('$tpl','$user[uid]','$user[allowmanage]','$user[allowrecommend]','$user[needverify]','$inheritedtplname')";
						$inheritedtplname = empty($inheritedtplname) ? $tpl : $inheritedtplname;
					}
				}
			}
			if($blockperms) {
				DB::query('REPLACE INTO '.DB::table($this->_table).' (targettplname,uid,allowmanage,allowrecommend,needverify,inheritedtplname) VALUES '.implode(',', $blockperms));
			}
		}
	}
}

?>