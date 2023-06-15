<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_postcache.php 28498 2012-03-01 11:21:16Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_postcache extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_postcache';
		$this->_pk    = 'pid';
		$this->_pre_cache_key = 'forum_postcache_';

		parent::__construct();
	}

	public function delete_by_dateline($dateline) {
		return DB::delete($this->_table, DB::field('dateline', $dateline, '<'));
	}
}

?>