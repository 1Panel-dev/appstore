<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_relatedthread.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_relatedthread extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_relatedthread';
		$this->_pk    = 'tid';

		parent::__construct();
	}
	public function delete_by_tid($tids) {
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

}

?>