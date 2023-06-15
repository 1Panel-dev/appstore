<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_block_favorite.php 27846 2012-02-15 09:04:33Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_block_favorite extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_block_favorite';
		$this->_pk    = 'favid';

		parent::__construct();
	}

	public function delete_by_uid_bid($uid, $bid) {
		return ($uid = dintval($uid)) && ($bid = dintval($bid)) ? DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('bid', $bid)) : false;
	}

	public function delete_by_bid($bid) {
		return ($bid = dintval($bid)) ? DB::delete($this->_table, DB::field('bid', $bid)) : false;
	}

	public function count_by_uid_bid($uid, $bid){
		return ($uid = dintval($uid)) && ($bid = dintval($bid)) ? DB::result_first('SELECT count(*) FROM %t WHERE uid=%d AND bid=%d', array($this->_table, $uid, $bid)) : false;
	}

	public function fetch_all_by_uid($uid) {
		return ($uid = dintval($uid)) ? DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC', array($this->_table, $uid), 'bid') : array();
	}
}

?>