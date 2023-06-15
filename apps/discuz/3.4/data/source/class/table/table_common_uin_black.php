<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_uin_black.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_uin_black extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_uin_black';
		$this->_pk    = 'uin';

		parent::__construct();
	}

	public function fetch_by_uid($uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid));
	}
	public function fetch_all_by_uin($ids = null) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($ids !== null) {
			$parameter[] = $ids;
			$wherearr[] = is_array($ids) ? 'uin IN(%n)' : 'uin=%d';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ", $parameter, $this->_pk, true);
	}

}

?>