<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_doing.php 30377 2012-05-24 09:52:22Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_doing extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_doing';
		$this->_pk    = 'doid';

		parent::__construct();
	}

	public function update_replynum_by_doid($inc_replynum, $doid) {
		return DB::query('UPDATE %t SET replynum=replynum+\'%d\' WHERE doid=%d', array($this->_table, $inc_replynum, $doid));
	}

	public function delete_by_uid($uid) {
		if(!$uid) {
			return null;
		}
		return DB::delete($this->_table, DB::field('uid', $uid));
	}

	public function fetch_all_by_uid_doid($uids, $bannedids = '', $paramorderby = '', $startrow = 0, $items = 0, $status = true, $allfileds = false) {
		$parameter = array($this->_table);
		$orderby = $paramorderby && in_array($paramorderby, array('dateline', 'replynum')) ? 'ORDER BY '.DB::order($paramorderby, 'DESC') : 'ORDER BY '.DB::order('dateline', 'DESC');

		$wheres = array();
		if($uids) {
			$parameter[] = $uids;
			$wheres[] = 'uid IN (%n)';
		}
		if($bannedids) {
			$parameter[] = $bannedids;
			$wheres[] = 'doid NOT IN (%n)';
		}
		if($status) {
			$wheres[] = ' status = 0';
		}

		$wheresql = !empty($wheres) && is_array($wheres) ? ' WHERE '.implode(' AND ', $wheres) : '';

		if(empty($wheresql)) {
			return null;
		}
		return DB::fetch_all('SELECT '.($allfileds ? '*' : 'doid').' FROM %t '.$wheresql.' '.$orderby.DB::limit($startrow, $items), $parameter);
	}

	public function fetch_all_by_status($status = 0, $start = 0, $limit = 1000) {
		return DB::fetch_all('SELECT * FROM %t WHERE `status` = %d ORDER BY ' . $this->_pk . ' ' . DB::limit($start, $limit), array($this->_table, $status));
	}

	public function fetch_all_search($start, $limit, $fetchtype, $uids, $useip, $keywords, $lengthlimit, $starttime, $endtime, $basickeywords = 0, $doid = '', $findex = '') {
		$parameter = array($this->_table);
		$wherearr = array();
		if($doid) {
			$parameter[] = (array)$doid;
			$wherearr[] = 'doid IN(%n)';
		}
		if(is_array($uids) && count($uids)) {
			$parameter[] = $uids;
			$wherearr[] = 'uid IN(%n)';
		}
		if($useip) {
			$parameter[] = str_replace('*', '%', $useip);
			$wherearr[] = 'ip LIKE %s';
		}
		if($keywords) {
			if(!$basickeywords) {
				$sqlkeywords = '';
				$or = '';
				$keywords = explode(',', str_replace(' ', '', $keywords));

				for($i = 0; $i < count($keywords); $i++) {
					$keywords[$i] = addslashes(stripsearchkey($keywords[$i]));
					if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
						$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
						$sqlkeywords .= " $or message REGEXP '".addslashes(stripsearchkey($keywords[$i]))."'";
					} else {
						$sqlkeywords .= " $or message LIKE '%".$keywords[$i]."%'";
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

		if($lengthlimit) {
			$parameter[] = intval($lengthlimit);
			$wherearr[] = 'LENGTH(message) < %d';
		}

		if($starttime) {
			$parameter[] = is_numeric($starttime) ? $starttime : strtotime($starttime);
			$wherearr[] = 'dateline>%d';
		}

		if($endtime) {
			$parameter[] = is_numeric($endtime) ? $endtime : strtotime($endtime);
			$wherearr[] = 'dateline<%d';
		}

		if($fetchtype == 3) {
			$selectfield = "count(*)";
		} elseif ($fetchtype == 2) {
			$selectfield = "doid";
		} else {
			$selectfield = "*";
			$parameter[] = DB::limit($start, $limit);
			$ordersql = ' ORDER BY dateline DESC %i';
		}

		if($findex) {
			$findex = 'USE INDEX(dateline)';
		}

		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		if($fetchtype == 3) {
			return DB::result_first("SELECT $selectfield FROM %t $wheresql", $parameter);
		} else {
			return DB::fetch_all("SELECT $selectfield FROM %t {$findex} $wheresql $ordersql", $parameter);
		}
	}

}

?>