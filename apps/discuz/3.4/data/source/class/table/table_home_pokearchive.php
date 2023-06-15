<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_pokearchive.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_pokearchive extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_pokearchive';
		$this->_pk    = 'pid';

		parent::__construct();
	}

	public function delete_by_uid_or_fromuid($uids) {
		$uids = dintval($uids, is_array($uids) ? true : false);
		if($uids) {
			return DB::delete($this->_table, DB::field('uid', $uids).' OR '.DB::field('fromuid', $uids));
		}
		return 0;
	}
	public function fetch_all_by_pokeuid($pokeuid) {
		return DB::fetch_all('SELECT * FROM %t WHERE pokeuid=%d ORDER BY dateline', array($this->_table, $pokeuid));
	}
	public function delete_by_dateline($dateline) {
		DB::query('DELETE FROM %t WHERE dateline<%d', array($this->_table, $dateline));
	}

}

?>