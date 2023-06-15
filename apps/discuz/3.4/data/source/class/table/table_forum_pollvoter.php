<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_pollvoter.php 27737 2012-02-13 09:46:21Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_pollvoter extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_pollvoter';
		$this->_pk    = '';

		parent::__construct();
	}
	public function delete_by_tid($tids) {
		if(!$tids) {
			return;
		}
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

}

?>