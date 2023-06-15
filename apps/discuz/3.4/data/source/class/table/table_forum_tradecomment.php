<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_tradecomment.php 27737 2012-02-13 09:46:21Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_tradecomment extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_tradecomment';
		$this->_pk    = 'id';

		parent::__construct();
	}

	function fetch_all_by_rateeid($rateeid, $type, $dateline = 0) {
		$dateline = $dateline ? 'AND dateline>='.intval($dateline) : '';
		return DB::fetch_all("SELECT * FROM %t WHERE rateeid=%d AND type=%d %i", array($this->_table, $rateeid, $type, $dateline));
	}

	function fetch_all_list($from, $uid, $dateline, $score, $start) {
		$sql = $from == 'myself' ? "tc.raterid='".intval($uid)."'" : "tc.rateeid='".intval($uid)."'";
		$sql .= $from == 'buyer' ? ' AND tc.type=0' : ($from == 'seller' ? ' AND tc.type=1' : '');
		$dateline = $dateline !== false ? ' AND tc.dateline>='.intval($dateline) : '';
		$score = $score !== false ? ' AND tc.score='.intval($score) : '';

		return DB::fetch_all("SELECT tc.*, tl.subject, tl.price, tl.credit FROM %t tc LEFT JOIN %t tl USING(orderid) WHERE %i %i %i ORDER BY tc.dateline DESC LIMIT %d, 10",
			array($this->_table, 'forum_tradelog', $sql, $dateline, $score, $start));
	}

	function count_list($from, $uid, $dateline, $score) {
		$sql = $from == 'myself' ? "tc.raterid='".intval($uid)."'" : "tc.rateeid='".intval($uid)."'";
		$sql .= $from == 'buyer' ? ' AND tc.type=0' : ($from == 'seller' ? ' AND tc.type=1' : '');
		$dateline = $dateline !== false ? ' AND tc.dateline>='.intval($dateline) : '';
		$score = $score !== false ? ' AND tc.score='.intval($score) : '';

		return DB::result_first("SELECT COUNT(*) FROM %t tc WHERE %i %i %i", array($this->_table, $sql, $dateline, $score));
	}

	function get_month_score($uid, $type, $rateeid) {
		$monthfirstday = mktime(0, 0, 0, date('m', TIMESTAMP), 1, date('Y', TIMESTAMP));
		return DB::result_first("SELECT COUNT(score) FROM %t WHERE raterid=%d AND type=%d AND dateline>=%d AND rateeid=%d", array($this->_table, $uid, $type, $monthfirstday, $rateeid));
	}

}

?>