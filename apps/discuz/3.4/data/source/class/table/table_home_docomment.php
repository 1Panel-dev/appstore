<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_docomment.php 27819 2012-02-15 05:12:23Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_docomment extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_docomment';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete_by_doid_uid($doids = null, $uids = null) {
		$sql = array();
		$doids && $sql[] = DB::field('doid', $doids);
		$uids && $sql[] = DB::field('uid', $uids);
		if($sql) {
			return DB::query('DELETE FROM %t WHERE %i', array($this->_table, implode(' OR ', $sql)));
		} else {
			return false;
		}
	}

	public function fetch_all_by_doid($doids) {
		if(empty($doids)) {
			return array();
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('doid', $doids).' ORDER BY dateline', array($this->_table));
	}

}

?>