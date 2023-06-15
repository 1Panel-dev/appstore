<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_sofa.php 31637 2012-09-17 08:12:26Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_sofa extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_sofa';
		$this->_pk    = 'tid';

		parent::__construct();
	}

	public function range($start = 0, $limit = 20) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY tid DESC %i', array($this->_table, DB::limit($start, $limit)), $this->_pk);
	}

	public function fetch_all_by_fid($fid, $start = 0, $limit = 20) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid IN(%n) ORDER BY tid DESC %i', array($this->_table, $fid, DB::limit($start, $limit)), $this->_pk);
	}

}

?>