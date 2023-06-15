<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_comment.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_comment extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_comment';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function fetch_all_by_uid($uids, $start = 0, $limit = 5) {
		if(!$uids) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE uid IN (%n) OR authorid IN (%n) OR (id IN (%n) AND idtype=%s)  %i', array($this->_table, $uids, $uids, $uids, 'uid', DB::limit($start, $limit)));
	}

	public function delete_by_uid_idtype($uid) {
		if(!$uid){
			return null;
		}
		DB::delete($this->_table, DB::field('uid', $uid).' OR '.DB::field('authorid', $uid).' OR ('.DB::field('id', $uid).' AND idtype=\'uid\')');
	}

	public function delete_by_uid($uids) {
		if(!$uids){
			return null;
		}
		DB::delete($this->_table, DB::field('uid', $uids).' OR ('.DB::field('id', $uids).' AND idtype=\'uid\')');
	}

	public function delete($cid = '', $id = '', $idtype = '') {
		$condition = array();

		if($cid) {
			$condition[] = DB::field('cid', $cid);
		}

		if($id) {
			$condition[] = DB::field('id', $id);
			$condition[] = DB::field('idtype', $idtype);
		}

		if(!count($condition)) {
			return null;
		}

		DB::delete($this->_table, implode(' AND ', $condition));
	}

	public function update($cids, $data, $authorid = '') {
		$condition = array();
		if($cids) {
			$condition[] = DB::field('cid', $cids);
		}
		if($authorid) {
			$condition[] = DB::field('authorid', $authorid);
		}

		if(empty($data) || !is_array($data) || !count($condition)) {
			return null;
		}

		return DB::update($this->_table, $data, implode(' AND ', $condition));
	}

	public function count_by_id_idtype($id, $idtype, $cid = '') {
		if($cid) {
			$cidsql = DB::field('cid', $cid). ' AND ';
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.$cidsql.' id=%d AND idtype=%s', array($this->_table, $id, $idtype));
	}

	public function fetch_all_by_id_idtype($id, $idtype, $start, $limit, $cid = '', $order = '') {
		if($cid) {
			$cidsql = DB::field('cid', $cid). ' AND ';
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.$cidsql.' id=%d AND idtype=%s ORDER BY '.DB::order('dateline', $order).' %i', array($this->_table, $id, $idtype, DB::limit($start, $limit)));
	}

	public function fetch_latest_by_authorid($uid, $cid) {
		if($cid) {
			$cidsql = DB::field('cid', $cid). ' AND ';
		}
		return DB::fetch_first('SELECT * FROM %t WHERE '.$cidsql.' authorid=%d ORDER BY dateline DESC LIMIT 0,1', array($this->_table, $uid));
	}

	public function fetch_by_id_idtype($id, $idtype, $cid = '') {
		if($cid) {
			$cidsql = DB::field('cid', $cid). ' AND ';
		}
		return DB::fetch_first('SELECT * FROM %t WHERE '.$cidsql.' id=%d AND idtype=%s', array($this->_table, $id, $idtype));
	}

	public function fetch($cid, $authorid = '') {
		if(!$cid) {
			return null;
		}
		$wherearr = array();
		$wherearr[] = DB::field('cid', $cid);
		if($authorid) {
			$wherearr[] = DB::field('authorid', $authorid);
		}

		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		return DB::fetch_first('SELECT * FROM '.DB::table($this->_table).' '.$wheresql);
	}

	public function fetch_all_by_status($status = 0, $start = 0, $limit = 1000) {
		return DB::fetch_all('SELECT * FROM %t WHERE `status` = %d ORDER BY ' . $this->_pk . ' ' . DB::limit($start, $limit), array($this->_table, $status));
	}

	public function fetch_all_search($fetchtype, $ids, $authorid, $uids, $useip, $keywords, $idtype, $starttime, $endtime, $start = 0, $limit = 0, $basickeywords = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($ids) {
			$parameter[] = $ids;
			$wherearr[] = 'id IN(%n)';
		}
		if(is_array($authorid) && count($authorid)) {
			$parameter[] = $authorid;
			$wherearr[] = 'authorid IN(%n)';
		}
		if($idtype) {
			$parameter[] = $idtype;
			$wherearr[] = 'idtype=%s';
		}
		if($starttime) {
			$parameter[] = is_numeric($starttime) ? $starttime : strtotime($starttime);
			$wherearr[] = 'dateline>%d';
		}
		if($endtime) {
			$parameter[] = is_numeric($endtime) ? $endtime : strtotime($endtime);
			$wherearr[] = 'dateline<%d';
		}
		if($uids) {
			$parameter[] = $uids;
			$wherearr[] = 'uid IN(%n)';
		}
		if($keywords) {
			if(!$basickeywords) {
				$sqlkeywords = '';
				$or = '';
				$keywords = explode(',', str_replace(' ', '', $keywords));

				for($i = 0; $i < count($keywords); $i++) {
					if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
						$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
						$sqlkeywords .= " $or message REGEXP '".addslashes(stripsearchkey($keywords[$i]))."'";
					} else {
						$sqlkeywords .= " $or message LIKE '%".addslashes(stripsearchkey($keywords[$i]))."%'";
					}
					$or = 'OR';
				}
				$parameter[] = $sqlkeywords;
				$wherearr[] = '%i';
			} else {
				$parameter[] = '%'.$basickeywords.'%';
				$wherearr[] = 'message LIKE %s';
			}
		}
		if($useip) {
			$parameter[] = str_replace('*', '%', $useip);
			$wherearr[] = 'ip LIKE  %s';
		}
		if($fetchtype == 3) {
			$selectfield = "count(*)";
		} elseif ($fetchtype == 2) {
			$selectfield = "cid";
		} else {
			$selectfield = "*";
			$parameter[] = DB::limit($start, $limit);
			$ordersql = ' ORDER BY dateline DESC %i';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		if(empty($wheresql)) {
			return null;
		}
		if($fetchtype == 3) {
			return DB::result_first("SELECT $selectfield FROM %t $wheresql", $parameter);
		} else {
			return DB::fetch_all("SELECT $selectfield FROM %t $wheresql $ordersql", $parameter);
		}
	}

}

?>