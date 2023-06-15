<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_process.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_process extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_process';
		$this->_pk    = 'processid';

		parent::__construct();
	}

	public function delete_process($name, $time) {
		$name = addslashes($name);
		return DB::delete('common_process', "processid='$name' OR expiry<".intval($time));
	}
}

?>