<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_searchindex.php 28041 2012-02-21 07:33:55Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_searchindex extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_searchindex';
		$this->_pk    = 'searchid';

		parent::__construct();
	}

	public function fetch_by_searchid_srchmod($searchid, $srchmod) {
		return DB::fetch_first('SELECT * FROM %t WHERE searchid=%d AND srchmod=%d', array($this->_table, $searchid, $srchmod));
	}

	public function count_by_dateline($timestamp, $srchmod = '') {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.($srchmod ? 'srchmod='.dintval($srchmod).' AND ' : '').'dateline>%d-60', array($this->_table, $timestamp));
	}

	public function fetch_all_search($searchctrl, $useip, $uid, $timestamp, $searchstring, $srchmod = '') {
		if(!$searchctrl || !$timestamp) {
			return null;
		}
		$timestamp = dintval($timestamp);
		$uid = dintval($uid);
		$srchmod = dintval($srchmod);
		$useip = daddslashes($useip);
		$searchctrl = dintval($searchctrl);
		$searchstring = daddslashes($searchstring);

		return DB::fetch_all("SELECT searchid, dateline,
			('".$searchctrl."'<>'0' AND ".(empty($uid) ? "useip='$useip'" : "uid='$uid'")." AND $timestamp-dateline<'".$searchctrl."') AS flood,
			(searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
			FROM ".DB::table($this->_table)."
			WHERE ".($srchmod ? "srchmod='$srchmod' AND " : '')."('".$searchctrl."'<>'0' AND ".(empty($uid) ? "useip='$useip'" : "uid='$uid'")." AND $timestamp-dateline<".$searchctrl.") OR (searchstring='$searchstring' AND expiration>'$timestamp')
			ORDER BY flood");
	}

}

?>