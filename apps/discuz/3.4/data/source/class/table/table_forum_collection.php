<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collection.php 31438 2012-08-28 06:03:08Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collection extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collection';
		$this->_pk    = 'ctid';
		$this->_pre_cache_key = 'forum_collection_';

		parent::__construct();
	}


	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid), $this->_pk);
	}

	public function fetch_all_by_uid($uid, $start = 0, $limit = 0, $exceptctid = null) {
		if($exceptctid) {
			$sql = ' AND ctid!='.intval($exceptctid);
		} else {
			$sql = '';
		}
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d %i'.DB::limit($start, $limit), array($this->_table, $uid, $sql), $this->_pk);
	}

	public function range($start = 0, $limit = 0, $reqthread = 0, $pK = true) {
		return DB::fetch_all('SELECT * FROM %t WHERE threadnum>=%d ORDER BY lastupdate DESC '.DB::limit($start, $limit), array($this->_table, $reqthread), $pK ? $this->_pk : '');
	}

	public function fetch_all($ctid = '', $orderby = '', $ordersc = '', $start = 0, $limit = 0, $title = '', $cachetid = '') {
		if($this->_allowmem && $cachetid) {
			$data = $this->fetch_cache($cachetid, $this->_pre_cache_key.'tid_');
			if($data) {
				return $data;
			}
		}
		$sql = '';
		if($ctid) {
			$sql .= 'WHERE '.DB::field('ctid', $ctid);
		}
		if($title && str_replace('%', '', $title)) {
			$sql .= ($sql ? ' AND ' : 'WHERE ').DB::field('name', '%'.$title.'%', 'like');
		}
		$sql .= ($orderby = DB::order($orderby, $ordersc)) ? ' ORDER BY '.$orderby : '';
		$sql .= ' '.DB::limit($start, $limit);
		if(!$sql) {
			return null;
		}
		$data = DB::fetch_all('SELECT * FROM %t %i', array($this->_table, $sql), $this->_pk);
		if($this->_allowmem && $cachetid) {
			$this->store_cache($cachetid, $data, $this->_cache_ttl, $this->_pre_cache_key.'tid_');
		}
		return $data;
	}

	public function count_by_title($title) {
		if(!$title || !str_replace('%', '', $title)) {
			return null;
		}
		$sql = DB::field('name', '%'.$title.'%', 'like');
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function count_all_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

	public function update_by_ctid($ctid, $incthreadnum = 0, $incfollownum = 0, $inccommentnum = 0, $lastupdate = 0, $incratenum = 0, $totalratenum = 0, $lastpost = '') {
		if(!$ctid) {
			return false;
		}
		$sql = array();
		$para = array($this->_table);
		if($incthreadnum) {
			$sql[] = 'threadnum=threadnum+\'%d\'';
			$para[] = $incthreadnum;
		}
		if($incfollownum) {
			$sql[] = 'follownum=follownum+\'%d\'';
			$para[] = $incfollownum;
		}
		if($inccommentnum) {
			$sql[] = 'commentnum=commentnum+\'%d\'';
			$para[] = $inccommentnum;
		}
		if($lastupdate != 0) {
			$sql[] = 'lastupdate=%d';
			$para[] = $lastupdate;
		}
		if($incratenum > 0) {
			if($totalratenum > 0) {
				$sql[] = 'rate=((rate*ratenum)+\'%d\')/(ratenum+1),ratenum=ratenum+1';
			} else {
				$sql[] = 'ratenum=ratenum+1,rate=%d';
			}
			$para[] = $incratenum;
		}
		if(count($lastpost) == 4) {
			$sql[] = 'lastpost=%d,lastsubject=%s,lastposttime=%d,lastposter=%s';
			$para = array_merge($para, array($lastpost['lastpost'], $lastpost['lastsubject'], $lastpost['lastposttime'], $lastpost['lastposter']));
		}
		if(!count($sql)) {
			return null;
		}

		$sqlupdate = implode(',', $sql);

		$result = DB::query('UPDATE %t SET '.$sqlupdate.' WHERE '.DB::field($this->_pk, $ctid), $para, false, true);
		return $result;
	}

	public function fetch_all_for_search($name, $ctid, $username, $uid, $start = 0, $limit = 20) {

		$where = '1';
		$where .= $name ? ' AND '.DB::field('name', '%'.stripsearchkey($name).'%', 'like') : '';
		$where .= $ctid ? ' AND '.DB::field('ctid', $ctid) : '';
		$where .= $username ? ' AND '.DB::field('username', '%'.stripsearchkey($username).'%', 'like') : '';
		$where .= $uid ? ' AND '.DB::field('uid', $uid) : '';

		if($start == -1) {
			return DB::result_first("SELECT count(*) FROM %t WHERE %i", array($this->_table, $where));
		}
		return DB::fetch_all("SELECT * FROM %t 	WHERE %i ORDER BY dateline DESC %i", array($this->_table, $where, DB::limit($start, $limit)));
	}

	public function update($val, $data, $unbuffered = false, $low_priority = false) {
		if(!empty($data) && is_array($data) && $val) {
			$this->checkpk();
			return DB::update($this->_table, $data, DB::field($this->_pk, $val), $unbuffered, $low_priority);
		}
		return !$unbuffered ? 0 : false;
	}

	public function fetch_ctid_by_searchkey($searchkey, $limit) {
		return DB::fetch_all('SELECT ctid FROM %t WHERE 1 %i ORDER BY ctid DESC %i', array($this->_table, $searchkey, DB::limit(0, $limit)));
	}

	public function delete($val, $unbuffered = false) {
		if(!$val) {
			return false;
		}
		$this->checkpk();
		$ret = DB::delete($this->_table, DB::field($this->_pk, $val), null, $unbuffered);
		return $ret;
	}

	public function fetch($id, $force_from_db = true){
		return parent::fetch($id, true);
	}

}

?>