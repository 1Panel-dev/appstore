<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_attachment_n.php 27745 2012-02-14 01:43:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_attachment_n extends discuz_table
{
	public function __construct() {

		$this->_table = '';
		$this->_pk    = 'aid';

		parent::__construct();
	}

	private function _get_table($tableid) {
		if(!is_numeric($tableid)) {
			list($idtype, $id) = explode(':', $tableid);
			if($idtype == 'aid') {
				$aid = dintval($id);
				$tableid = DB::result_first("SELECT tableid FROM ".DB::table('forum_attachment')." WHERE aid='$aid'");
			} elseif($idtype == 'tid') {
				$tid = (string)$id;
				$tableid = dintval($tid[strlen($tid)-1]);
			} elseif($idtype == 'pid') {
				$pid = dintval($id);
				$tableid = DB::result_first("SELECT tableid FROM ".DB::table('forum_attachment')." WHERE pid='$pid' LIMIT 1");
				$tableid = $tableid >= 0 && $tableid < 10 ? intval($tableid) : 127;
			}
		}
		if($tableid >= 0 && $tableid < 10) {
			return 'forum_attachment_'.intval($tableid);
		} elseif($tableid == 127) {
			return 'forum_attachment_unused';
		} else {
			throw new DbException('Table forum_attachment_'.$this->_table.' has not exists');
		}
	}

	private function _check_id($idtype, $ids) {
		if($idtype == 'pid' && $this->_table == 'forum_attachment_unused') {
			return false;
		}
		if(in_array($idtype, array('aid', 'tid', 'pid', 'uid')) && !empty($ids)) {
			return true;
		} else {
			return false;
		}
	}

	public function delete($tableid, $val){
		return DB::delete($this->_get_table($tableid), DB::field($this->_pk, $val));
	}

	public function delete_by_id($tableid, $idtype, $id){
		return $this->_check_id($idtype, $id) ? DB::delete($this->_get_table($tableid), DB::field($idtype, $id)) : false;
	}

	public function update($tableid, $val, $data) {
		if(!$data) {
			return;
		}
		return DB::update($this->_get_table($tableid), $data, DB::field($this->_pk, $val));
	}

	public function insert($tableid, $data, $return_insert_id = false, $replace = false, $silent = false) {
		if(!$data) {
			return;
		}
		return DB::insert($this->_get_table($tableid), $data, $return_insert_id, $replace, $silent);
	}

	public function fetch($tableid, $aid, $isimage = false){
		$isimage = $isimage === false ? '' : ' AND '.DB::field('isimage', $isimage);
		return !empty($aid) ? DB::fetch_first('SELECT * FROM %t WHERE %i %i', array($this->_get_table($tableid), DB::field($this->_pk, $aid), $isimage)) : array();
	}

	public function fetch_max_image($tableid, $idtype, $id){
		return $this->_check_id($idtype, $id) ? DB::fetch_first('SELECT * FROM %t WHERE %i AND isimage IN (1, -1) ORDER BY width DESC LIMIT 1', array($this->_get_table($tableid), DB::field($idtype, $id))) : array();
	}

	public function count_by_id($tableid, $idtype, $id){
		return $this->_check_id($idtype, $id) ? DB::result_first('SELECT COUNT(*) FROM %t WHERE %i', array($this->_get_table($tableid), DB::field($idtype, $id))) : 0;
	}

	public function count_image_by_id($tableid, $idtype, $id){
		return $this->_check_id($idtype, $id) ? DB::result_first('SELECT COUNT(*) FROM %t WHERE %i AND isimage IN (1, -1)', array($this->_get_table($tableid), DB::field($idtype, $id))) : 0;
	}

	public function fetch_all($tableid, $aids, $remote = false, $isimage = false){
		$remote = $remote === false ? '' : ' AND '.DB::field('remote', $remote);
		$isimage = $isimage === false ? '' : ' AND '.DB::field('isimage', $isimage);
		return !empty($aids) ? DB::fetch_all('SELECT * FROM %t WHERE %i %i %i', array($this->_get_table($tableid), DB::field($this->_pk, $aids), $remote, $isimage)) : array();
	}

	public function fetch_all_by_id($tableid, $idtype, $ids, $orderby = '', $isimage = false, $isprice = false, $remote = false, $limit = false) {
		if($this->_check_id($idtype, $ids)) {
			$attachments = array();
			if($orderby) {
				$orderby = 'ORDER BY '.$orderby;
			}
			$isimage = $isimage === false ? '' : ' AND '.DB::field('isimage', $isimage);
			$isprice = $isprice === false ? '' : ' AND '.DB::field('price', 0, '>');
			$remote = $remote === false ? '' : ' AND '.DB::field('remote', $remote);
			$limit = $limit < 1 ? '' : DB::limit(0, $limit);
			$query = DB::query("SELECT * FROM %t WHERE %i %i %i %i %i %i", array($this->_get_table($tableid), DB::field($idtype, $ids), $isimage, $isprice, $remote, $orderby, $limit));
			while($value = DB::fetch($query)) {
				$attachments[$value['aid']] = $value;
			}
			return $attachments;
		} else {
			return array();
		}
	}

	public function reset_picid($tableid, $newids) {
		if($newids) {
			DB::query("UPDATE %t SET picid='0' WHERE picid IN (%n)", array($this->_get_table($tableid), (array)$newids), false, true);
		}
	}

	public function fetch_by_aid_uid($tableid, $aid, $uid) {
		$query = DB::query("SELECT * FROM %t WHERE aid=%d AND uid=%d", array($this->_get_table($tableid), $aid, $uid));
		return DB::fetch($query);
	}

	public function fetch_all_by_pid_width($tableid, $pids, $width) {
		return DB::fetch_all("SELECT * FROM %t WHERE %i AND isimage IN ('1', '-1') AND width>=%d", array($this->_get_table($tableid), DB::field('pid', $pids), $width));
	}

	public function get_total_filesize() {
		$attachsize = 0;
		for($i = 0;$i < 10;$i++) {
			$attachsize += DB::result_first("SELECT SUM(filesize) FROM ".DB::table('forum_attachment_'.$i));
		}
		return $attachsize;
	}

}

?>