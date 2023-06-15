<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_credit_rule.php 27900 2012-02-16 07:50:00Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_credit_rule extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_credit_rule';
		$this->_pk    = 'rid';

		parent::__construct();
	}

	public function fetch_all_by_rid($rid = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($rid) {
			$rid = dintval($rid, true);
			$parameter[] = $rid;
			$wherearr[] = is_array($rid) ? 'rid IN(%n)' : 'rid=%d';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY rid DESC", $parameter, $this->_pk);
	}

	public function fetch_all_rule() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY rid DESC', array($this->_table));
	}

	public function fetch_all_by_action($action) {
		if(!empty($action)) {
			return DB::fetch_all('SELECT * FROM %t WHERE action IN(%n)', array($this->_table, $action), $this->_pk);
		}
		return array();
	}

}

?>