<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_article_title.php 31618 2012-09-14 09:32:26Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_article_title extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_article_title';
		$this->_pk    = 'aid';

		parent::__construct();
	}


	public function update_click($cid, $clickid, $incclick) {
		$clickid = intval($clickid);
		if($clickid < 1 || $clickid > 8 || empty($cid) || empty($incclick)) {
			return false;
		}
		return DB::query('UPDATE %t SET click'.$clickid.' = click'.$clickid.'+\'%d\' WHERE aid = %d', array($this->_table, $incclick, $cid));
	}
	public function fetch_count_for_cat($catid) {
		if(empty($catid)) {
			return 0;
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE catid=%d', array($this->_table, $catid));
	}
	public function fetch_count_for_idtype($id, $idtype) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE id=%d AND idtype=%s", array($this->_table, $id, $idtype));
	}
	public function fetch_all_for_cat($catid, $status = null, $orderaid = 0, $start = 0, $limit = 0) {
		if(empty($catid)) {
			return array();
		}
		$statussql = $status !== null ? ' AND '.DB::field('status', $status) : '';
		$orderaidsql = $orderaid ? ' ORDER BY aid DESC' : '';
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('catid', $catid).$statussql.$orderaidsql.DB::limit($start, $limit), array($this->_table));
	}
	public function update_for_cat($catid, $data) {
		if(empty($catid) || empty($data)) {
			return false;
		}
		return DB::update($this->_table, $data, DB::field('catid', $catid));
	}
	public function range($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' ORDER BY dateline DESC'.DB::limit($start, $limit));
	}
	public function fetch_all_by_sql($where, $order = '', $start = 0, $limit = 0, $count = 0, $alias = '') {
		$where = $where && !is_array($where) ? " WHERE $where" : '';
		if(is_array($order)) {
			$order = '';
		}
		if($count) {
			return DB::result_first('SELECT count(*) FROM '.DB::table($this->_table).'  %i %i %i '.DB::limit($start, $limit), array($alias, $where, $order));
		}
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' %i %i %i '.DB::limit($start, $limit), array($alias, $where, $order));
	}
	public function fetch_all_by_title($idtype, $subject) {
		$parameter = array($this->_table);
		$or = $wheresql = '';
		$subject = explode(',', str_replace(' ', '', $subject));
		if(empty($subject)) {
			return array();
		}
		for($i = 0; $i < count($subject); $i++) {
			if(preg_match("/\{(\d+)\}/", $subject[$i])) {
				$subject[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($subject[$i], '/'));
				$wheresql .= " $or title REGEXP %s";
				$parameter[] = $subject[$i];
			} else {
				$wheresql .= " $or title LIKE %s";
				$parameter[] = '%'.$subject[$i].'%';
			}
			$or = 'OR';
		}
		return DB::fetch_all("SELECT $idtype FROM %t WHERE $wheresql", $parameter);
	}
	public function fetch_all_for_search($aids, $orderby = '', $ascdesc = '', $start = 0, $limit = 0) {
		return DB::fetch_all("SELECT at.*,ac.viewnum, ac.commentnum FROM ".DB::table($this->_table)." at LEFT JOIN ".DB::table('portal_article_count')." ac ON at.aid=ac.aid WHERE at.".DB::field('aid', $aids).($orderby ? " ORDER BY ".DB::order($orderby, $ascdesc) : ' ').DB::limit($start, $limit));
	}


	public function repair_htmlmade($ids) {
		if(($ids = dintval($ids, true))) {
			return DB::update($this->_table, array('htmlmade' => 0), DB::field($this->_pk, $ids));
		}
		return false;
	}

	public function fetch_all_aid_by_dateline($dateline, $catids = array(), $startid = 0, $endid = 0) {
		$data = array();
		$where = array();
		if($startid) {
			$where[] = DB::field('aid', intval($startid), '>=');
		}
		if($endid) {
			$where[] = DB::field('aid', intval($endid), '<=');
		}
		if($catids) {
			$where[] = DB::field('catid', dintval($catids, true));
		}
		if($dateline) {
			$where[] = DB::field('dateline', intval($dateline), '>=');
		}
		if($where) {
			$data = DB::fetch_all('SELECT aid FROM '.DB::table($this->_table).' WHERE '. implode(' AND ', $where).' LIMIT 200000', NULL, $this->_pk);
		}
		return $data;
	}

	public function fetch_preaid_by_catid_aid($catid, $aid) {
		$ret = 0;
		if(($catid = intval($catid)) && ($aid = intval($aid))) {
			$ret = DB::result_first('SELECT aid FROM %t WHERE catid=%d AND aid<%d ORDER BY aid DESC LIMIT 1', array($this->_table, $catid, $aid));
		}
		return $ret;
	}

	public function fetch_nextaid_by_catid_aid($catid, $aid) {
		$ret = 0;
		if(($catid = intval($catid)) && ($aid = intval($aid))) {
			$ret = DB::result_first('SELECT aid FROM %t WHERE catid=%d AND aid>%d ORDER BY aid ASC LIMIT 1', array($this->_table, $catid, $aid));
		}
		return $ret;
	}
}

?>