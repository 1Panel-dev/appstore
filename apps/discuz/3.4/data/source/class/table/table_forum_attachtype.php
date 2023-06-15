<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_attachtype.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_attachtype extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_attachtype';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_data() {
		return DB::fetch_all('SELECT * FROM %t', array($this->_table), $this->_pk);
	}

	public function fetch_all_by_fid($fid) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d', array($this->_table, $fid), $this->_pk);
	}

	public function delete_by_id_fid($id, $fid) {
		$id = dintval($id, is_array($id) ? true : false);
		$fid = dintval($fid, is_array($fid) ? true : false);
		if(is_array($id) && empty($id) || is_array($fid) && empty($fid)) {
			return 0;
		}
		return DB::delete($this->_table, DB::field('id', $id).' AND '.DB::field('fid', $fid));
	}

	public function count_by_extension_fid($extension, $fid = null) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($fid !== null) {
			$wherearr[] = 'fid=%d';
			$parameter[] = $fid;
		}
		$parameter[] = $extension;
		$wherearr[] = 'extension=%s';
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first('SELECT COUNT(*) FROM %t'.$wheresql, $parameter);
	}

}

?>