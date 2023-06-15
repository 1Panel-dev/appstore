<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_rsscache.php 27793 2012-02-14 10:02:48Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_rsscache extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_rsscache';
		$this->_pk    = 'aid';

		parent::__construct();
	}

	public function fetch_all_by_catid($catid, $limit = 20) {
		return $catid ? DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('catid', $catid).' ORDER BY dateline DESC LIMIT '.dintval($limit), null, 'aid') : array();
	}
}

?>