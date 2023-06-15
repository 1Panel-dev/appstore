<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_myinvite.php 28246 2012-02-26 10:03:35Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_myinvite extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_myinvite';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_touid($touid) {
		return DB::fetch_all('SELECT * FROM %t WHERE touid=%d ORDER BY dateline DESC', array($this->_table, $touid));
	}
	public function count_by_touid($touid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE touid=%d', array($this->_table, $touid));
	}
	public function count_by_hash_touid($hash, $touid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE hash=%s AND touid=%d', array($this->_table, $hash, $touid));
	}

	public function delete_by_appid($appid) {
		$appid = dintval($appid, true);
		if($appid) {
			return DB::delete($this->_table, DB::field('appid', $appid));
		}
		return 0;
	}

	public function delete_by_touid_or_fromuid($uids) {
		$uids = dintval($uids, true);
		if($uids) {
			return DB::delete($this->_table, DB::field('touid', $uids).' OR '.DB::field('fromuid', $uids));
		}
		return 0;
	}

	public function delete_by_hash_touid($hash, $touid) {
		$touid = dintval($touid, true);
		if(!empty($hash) && $touid) {
			return DB::delete($this->_table, DB::field('hash', $hash).' AND '.DB::field('touid', $touid));
		}
		return 0;
	}

	public function delete_by_appid_touid($appid, $touid) {
		$touid = dintval($touid, true);
		$appid = dintval($appid, true);
		if($touid && $appid) {
			return DB::delete($this->_table, DB::field('appid', $appid).' AND '.DB::field('touid', $touid));
		}
		return 0;
	}

}

?>