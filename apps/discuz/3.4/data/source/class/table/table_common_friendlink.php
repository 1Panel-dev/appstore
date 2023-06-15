<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_friendlink.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_friendlink extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_friendlink';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_displayorder($type = '')
	{
		$args = array($this->_table);
		if($type) {
			$sql = 'WHERE (`type` & %s > 0)';
			$args[] = $type;
		}
		return DB::fetch_all("SELECT * FROM %t $sql ORDER BY displayorder", $args, $this->_pk);
	}

}

?>