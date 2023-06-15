<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_blog.php 32130 2012-11-14 09:20:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_blog extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_blog';
		$this->_pk    = 'blogid';

		parent::__construct();
	}

	public function fetch_by_id_idtype($id) {
		if(!$id) {
			return null;
		}
		return DB::fetch_first('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('blogid', $id)));
	}
	public function update_dateline_by_id_idtype_uid($id, $idtype, $dateline, $uid) {
		return DB::update($this->_table, array('dateline' => $dateline), DB::field($idtype, $id).' AND '.DB::field('uid', $uid));
	}
	public function range($start = 0, $limit = 0, $ordersc = 'DESC', $orderby = 'dateline', $friend = null, $status = null, $uid = null, $dateline = null) {
		$wheresql = '1';
		$wheresql .= ($friend !== null) ? ' AND '.DB::field('friend', $friend) : '';
		$wheresql .= $uid ? ' AND '.DB::field('uid', $uid) : '';
		$wheresql .= $status ? ' AND '.DB::field('status', $status) : '';
		$wheresql .= $dateline ? ' AND '.DB::field('dateline', $dateline, '>=') : '';
		if(in_array($orderby, array('hot', 'dateline'))) {
			$wheresql .= ' ORDER BY '.DB::order($orderby, $ordersc);
		}
		$wheresql .= ' '.DB::limit($start, $limit);

		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $wheresql), $this->_pk);
	}

	public function fetch_all($blogid, $orderby = '', $ordersc = '', $start = 0, $limit = 0) {
		if(!$blogid) {
			return null;
		}

		$wheresql = DB::field('blogid', $blogid);

		if($orderby = DB::order($orderby, $ordersc)) {
			$wheresql .= ' ORDER BY '.$orderby;
		}
		if($limit = DB::limit($start, $limit)) {
			$wheresql .= ' '.$limit;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $wheresql), $this->_pk);
	}

	public function increase($blogid, $uid, $setarr) {
		$sql = array();
		$allowkey = array('hot', 'viewnum', 'replynum', 'favtimes', 'sharetimes');
		foreach($setarr as $key => $value) {
			if(($value = intval($value)) && in_array($key, $allowkey)) {
				$sql[] = "`$key`=`$key`+'$value'";
			}
		}
		$wheresql = DB::field('blogid', $blogid);
		if($uid) {
			$wheresql .= ' AND '.DB::field('uid', $uid);
		}
		if(!empty($sql)){
			return DB::query('UPDATE %t SET %i WHERE %i', array($this->_table, implode(',', $sql), $wheresql));
		}
	}

	public function update_click($blogid, $clickid, $incclick) {
		$clickid = intval($clickid);
		if($clickid < 1 || $clickid > 8) {
			return null;
		}
		return DB::query('UPDATE %t SET click'.$clickid.' = click'.$clickid.'+\'%d\' WHERE blogid = %d', array($this->_table, $incclick, $blogid));
	}

	public function update_classid_by_classid($classid, $newclassid) {
		return DB::query('UPDATE %t SET classid = %d WHERE classid = %d', array($this->_table, $newclassid, $classid));
	}

	public function fetch_blogid_by_subject($keyword, $limit) {
		$field = "subject LIKE '%{text}%'";
		if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
			$andor = ' AND ';
			$keywordsrch = '1';
			$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
		} else {
			$andor = ' OR ';
			$keywordsrch = '0';
			$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
		}
		$keyword = str_replace('*', '%', addcslashes(daddslashes($keyword), '%_'));
		foreach(explode('+', $keyword) as $text) {
			$text = trim($text);
			if($text) {
				$keywordsrch .= $andor;
				$keywordsrch .= str_replace('{text}', $text, $field);
			}
		}
		$wheresql = " ($keywordsrch)";

		if($limit) {
			$wheresql .= ' ORDER BY blogid DESC '.DB::limit(0, $limit);
		}

		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $wheresql), $this->_pk);
	}

	public function fetch_blogid_by_uid($uid, $start = 0, $limit = 0) {
		if(!$uid) {
			return null;
		}
		return DB::fetch_all('SELECT blogid FROM %t WHERE uid IN (%n) %i', array($this->_table, $uid, DB::limit($start, $limit)), $this->_pk);
	}

	public function fetch_all_by_uid($uid, $orderby = 'dateline', $start = 0, $limit = 0) {
		if(!$uid) {
			return null;
		}
		if($orderby = DB::order($orderby, 'DESC')) {
			$order = 'ORDER BY '.$orderby;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE uid IN (%n) %i', array($this->_table, $uid, $order.' '.DB::limit($start, $limit)), $this->_pk);
	}

	public function fetch_all_by_hot($hot, $orderby = 'dateline', $start = 0, $limit = 0) {
		if(!$uid) {
			return null;
		}
		if($orderby = DB::order($orderby, 'DESC')) {
			$order = 'ORDER BY '.$orderby;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE hot>=%d %i', array($this->_table, $hot, $order.' '.DB::limit($start, $limit)), $this->_pk);
	}

	public function count_by_catid($catid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE catid = %d', array($this->_table, $catid));
	}

	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid = %d', array($this->_table, $uid));
	}

	public function delete_by_catid($catid) {
		if(!$catid) {
			return null;
		}
		return DB::delete($this->_table, DB::field('catid', $catid));
	}

	public function delete_by_uid($uids) {
		if(!$uids) {
			return null;
		}
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

	public function update_by_catid($catid, $data) {
		if(empty($data) || !is_array($data) || !$catid) {
			return null;
		}
		return DB::update($this->_table, $data, DB::field('catid', $catid));
	}

	public function count_uid_by_blogid($blogid) {
		if(!is_array($blogid) || !$blogid) {
			return null;
		}
		return DB::fetch_all('SELECT uid, COUNT(blogid) AS count FROM %t WHERE blogid IN (%n) GROUP BY uid', array($this->_table, $blogid));
	}

	public function count_all_by_search($blogid = null, $uids = null, $starttime = null, $endtime = null, $hot1 = null, $hot2 = null, $viewnum1 = null, $viewnum2 = null, $replynum1 = null, $replynum2 = null, $friend = null, $ip = null, $keywords = null, $lengthlimit = null, $classid = null, $catid = null, $subject = null, $countwithoutjoin = false, $status = null) {
		return $this->fetch_all_by_search(3, $blogid, $uids, $starttime, $endtime, $hot1, $hot2, $viewnum1, $viewnum2, $replynum1, $replynum2, $friend, $ip, $keywords, $lengthlimit, null, null, 0, 0, $classid, $catid, $subject, null, $countwithoutjoin, $status);
	}

	public function fetch_all_by_search($fetchtype = 1, $blogid = null, $uids = null, $starttime = null, $endtime = null, $hot1 = null, $hot2 = null, $viewnum1 = null, $viewnum2 = null, $replynum1 = null, $replynum2 = null, $friend = null, $ip = null, $keywords = null, $lengthlimit = null, $orderby = null, $ordersc = null, $start = 0, $limit = 0, $classid = null, $catid = null, $subject = null, $findex = null, $countwithoutjoin = false, $status = null) {
		$sql = '';
		$sql .= $blogid ? ' AND b.'.DB::field('blogid', $blogid) : '';
		$sql .= is_array($uids) && count($uids) > 0 ? ' AND b.'.DB::field('uid', $uids) : '';
		$sql .= $starttime ? ' AND b.'.DB::field('dateline', $starttime, '>') : '';
		$sql .= $endtime ? ' AND b.'.DB::field('dateline', $endtime, '<') : '';
		$sql .= $hot1 ? ' AND b.'.DB::field('hot', $hot1, '>=') : '';
		$sql .= $hot2 ? ' AND b.'.DB::field('hot', $hot2, '<=') : '';

		$sql .= $viewnum1 ? ' AND b.'.DB::field('viewnum', $viewnum1, '>=') : '';
		$sql .= $viewnum2 ? ' AND b.'.DB::field('viewnum', $viewnum1, '<=') : '';
		$sql .= $replynum1 ? ' AND b.'.DB::field('replynum', $replynum1, '>=') : '';
		$sql .= $replynum2 ? ' AND b.'.DB::field('replynum', $replynum2, '<=') : '';
		$sql .= $classid ? ' AND b.'.DB::field('classid', $classid) : '';
		$sql .= $friend ? ' AND b.'.DB::field('friend', $friend) : '';
		$sql .= !is_null($status) ? ' AND b.'.DB::field('status', $status) : '';

		$ip = str_replace('*', '', $ip);
		if($fetchtype == 1) {
			$sql .= $ip ? ' AND bf.'.DB::field('postip', "%$ip%", 'like') : '';
		}

		$orderby = $orderby ? $orderby : 'dateline';
		$ordersc = $ordersc ? $ordersc : 'DESC';

		if($fetchtype == 1 && $keywords != '' && !is_array($keywords)) {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $keywords));

			for($i = 0; $i < count($keywords); $i++) {
				$keywords[$i] = daddslashes($keywords[$i]);
				if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
					$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
					$sqlkeywords .= " $or b.subject REGEXP '".$keywords[$i]."' OR bf.message REGEXP '".addslashes(stripsearchkey($keywords[$i]))."'";
				} else {
					$sqlkeywords .= " $or b.subject LIKE '%".$keywords[$i]."%' OR bf.message LIKE '%".$keywords[$i]."%'";
				}
				$or = 'OR';
			}
			if($sqlkeywords) {
				$sql .= " AND ($sqlkeywords)";
			}
		}

		$sql .= $subject ? ' AND b.'.DB::field('subject', "%$subject%", 'like') : '';
		$sql .= $catid ? ' AND b.'.DB::field('catid', $catid) : '';
		if($fetchtype == 1) {
			$sql .= $lengthlimit ? ' AND LENGTH(bf.message) > '.intval($lengthlimit) : '';
		}


		if($fetchtype == 3) {
			$selectfield = 'count(*)';
		} elseif ($fetchtype == 2) {
			$selectfield = 'b.blogid';
		} else {
			$selectfield = 'bf.*,b.*';
		}

		if($findex) {
			$findex = 'USE INDEX(dateline)';
		} else {
			$findex = '';
		}

		if($fetchtype == 3) {
			return DB::result_first("SELECT $selectfield FROM %t b ".(($countwithoutjoin === false) ? 'LEFT JOIN %t bf USING(blogid)  ' : '').
				"WHERE 1 %i", ($countwithoutjoin === false) ? array($this->_table, 'home_blogfield', $sql) : array($this->_table, $sql));
		} else {
			if($order = DB::order($orderby, $ordersc)) {
				$order = 'ORDER BY b.'.$order;
			} else {
				$order = '';
			}
			return DB::fetch_all("SELECT $selectfield FROM %t b {$findex} LEFT JOIN %t bf USING(blogid) " .
				"WHERE 1 %i", array($this->_table, 'home_blogfield', $sql.' '.$order.' '.DB::limit($start, $limit)));
		}

	}

	public function fetch_all_by_block($blogids = null, $bannedids = null, $uids = null, $catid = null, $hours = null, $getpic = null, $getsummary = null, $picrequired = null, $orderby = 'dateline', $start = 0, $limit = 0) {
		$wheres = array();
		if($blogids) {
			$wheres[] = 'b.'.DB::field('blogid', $blogids);
		}
		if($bannedids) {
			$val = implode(',', DB::quote($bannedids));
			$wheres[] = 'b.blogid NOT IN ('.$val.')';
		}
		if($uids) {
			$wheres[] = 'b.'.DB::field('uid', $uids);
		}
		if($catid && !in_array('0', $catid)) {
			$wheres[] = 'b.'.DB::field('catid', $catid);
		}
		if($hours) {
			$timestamp = TIMESTAMP - 3600 * $hours;
			$wheres[] = 'b.'.DB::field('dateline', $timestamp, '>=') ;
		}
		$tablesql = $fieldsql = '';
		if($getpic  || $getsummary || $picrequired) {
			if($picrequired) {
				$wheres[] = "bf.pic != ''";
			}
			$tablesql = ' LEFT JOIN '.DB::table('home_blogfield')." bf ON b.blogid = bf.blogid";
			$fieldsql = ', bf.pic, b.picflag, bf.message';
		}
		$wheres[] = "b.friend = '0'";
		$wheres[] = "b.status='0'";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';

		return DB::fetch_all('SELECT b.* %i FROM %t b %i WHERE %i', array($fieldsql, $this->_table, $tablesql, $wheresql.' ORDER BY b.'.DB::order($orderby, 'DESC').' '.DB::limit($start, $limit)));
	}
}

?>