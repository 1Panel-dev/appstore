<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_comment.php 29122 2012-03-27 05:57:21Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_comment extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_comment';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function fetch_all_by_id_idtype($id, $idtype = '', $orderby = '', $ordersc = 'DESC', $start = 0, $limit = 0) {
		if(!$id) {
			return null;
		}
		$sql = array(DB::field('id', $id));
		if($idtype) {
			$sql[] = DB::field('idtype', $idtype);
		}
		$wheresql = implode(' AND ', $sql);
		if($orderby = DB::order($orderby, $ordersc)) {
			$wheresql .= ' ORDER BY '.$orderby;
		}
		if($limit) {
			$wheresql .= DB::limit($start, $limit);
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $wheresql));
	}

	public function count_by_id_idtype($id, $idtype) {
		if(!$id || !$idtype) {
			return null;
		}
		$sql = DB::field('id', $id).' AND '.DB::field('idtype', $idtype);
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function delete_by_id_idtype($id, $idtype) {
		if(!$id) {
			return null;
		}
		$para = DB::field('id', $id);
		if($idtype) {
			$para .= ' AND '.DB::field('idtype', $idtype);
		}
		return DB::delete($this->_table, $para);
	}

	public function count_all_by_search($aid, $authorid, $starttime, $endtime, $idtype, $message) {
		return $this->fetch_all_by_search($aid, $authorid, $starttime, $endtime, $idtype, $message, 0, 0, 2);
	}

	public function fetch_all_by_idtype_status($idtype = 'aid', $status = 0, $start = 0, $limit = 1000) {
		$idtype = in_array($idtype, array('aid', 'topicid')) ? $idtype : 'aid';
		return DB::fetch_all('SELECT * FROM %t WHERE `idtype` = %s AND `status` = %d ORDER BY ' . $this->_pk . ' ' . DB::limit($start, $limit), array($this->_table, $idtype, $status));
	}

	public function fetch_all_by_search($aid, $authorid, $starttime, $endtime, $idtype, $message, $start = 0, $limit = 0, $type = 1) {
		$idtype = in_array($idtype, array('aid', 'topicid')) ? $idtype : 'aid';
		$tablename = $idtype == 'aid' ? 'portal_article_title' : 'portal_topic';

		$sql = '';
		$sql .= $aid ? ' AND c.'.DB::field('id', $aid) : '';
		$sql .= $authorid ? ' AND c.'.DB::field('uid', $authorid) : '';
		$sql .= $starttime ? ' AND c.'.DB::field('dateline', $starttime, '>') : '';
		$sql .= $endtime ? ' AND c.'.DB::field('dateline', $endtime, '<') : '';

		if($message != '') {
			$sqlmessage = '';
			$or = '';
			$message = daddslashes($message);
			$message = explode(',', str_replace(' ', '', $message));

			for($i = 0; $i < count($message); $i++) {
				if(preg_match("/\{(\d+)\}/", $message[$i])) {
					$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
					$sqlmessage .= " $or c.message REGEXP '".$message[$i]."'";
				} else {
					$sqlmessage .= " $or c.message LIKE '%".$message[$i]."%'";
				}
				$or = 'OR';
			}
			if($sqlmessage) {
				$sql .= " AND ($sqlmessage)";
			}
		}
		if($type == 2) {
			return DB::result_first('SELECT count(*) FROM %t c WHERE 1 %i', array($this->_table, $sql));
		} else {
			return DB::fetch_all('SELECT c.*, a.title FROM %t c LEFT JOIN %t a ON a.`'.$idtype.'`=c.id WHERE 1 %i ORDER BY c.dateline DESC %i', array($this->_table, $tablename, $sql, DB::limit($start, $limit)));
		}
	}

}

?>