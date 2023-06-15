<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_admincp_group.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_admincp_group extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_admincp_group';
		$this->_pk    = 'cpgroupid';

		parent::__construct();
	}

	public function fetch_by_cpgroupname($name) {
		return $name ? DB::fetch_first('SELECT * FROM %t WHERE cpgroupname=%s', array($this->_table, $name)) : null;
	}
}

?>