<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_faq.php 30560 2012-06-04 03:03:56Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_faq extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_faq';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_fpid($fpid = '', $srchkw = '') {
		$sql = array();
		if($fpid !== '' && $fpid) {
			$sql[] = DB::field('fpid', $fpid);
		}
		if($srchkw) {
			$sql[] = DB::field('title', '%'.$srchkw.'%', 'like').' OR '.DB::field('message', '%'.$srchkw.'%', 'like');
		}
		$sql = implode(' AND ', $sql);
		if($sql) {
			$sql = 'WHERE '.$sql;
		}
		return DB::fetch_all("SELECT *  FROM %t  %i ORDER BY displayorder", array($this->_table, $sql));
	}

	public function check_identifier($identifier, $id) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE identifier=%s AND id!=%s", array($this->_table, $identifier, $id));
	}

}

?>