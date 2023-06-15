<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_activity.php 30378 2012-05-24 09:52:46Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_activity extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_activity';
		$this->_pk    = 'tid';

		parent::__construct();
	}

	public function fetch_all_for_search($view, $order, $searchkey, $type, $frienduid, $spaceuid, $minhot, $count = 0, $start = 0, $limit = 0) {
		$today = strtotime(dgmdate(TIMESTAMP, 'Y-m-d'));
		$wheresql = '1';
		$threadsql = $ordersql = $apply_sql = '';
		if($view == 'all') {
			if($order == 'hot') {
				$threadsql .= " t.special='4' AND t.replies>='$minhot'";
				$apply_sql = "INNER JOIN ".DB::table('forum_thread')." t ON t.special='4' AND t.tid = a.tid AND t.replies>='$minhot' AND t.displayorder>'-1'";
			}
		} elseif($view == 'me') {
			$type = in_array($type, array('orig', 'apply')) ? $type : 'orig';
			if($type == 'apply') {
				$wheresql = "1";
				$apply_sql = "INNER JOIN ".DB::table('forum_activityapply')." apply ON apply.uid = '$spaceuid' AND apply.tid = a.tid";
			} else {
				$wheresql = "a.uid = '$spaceuid'";
			}
			$ordersql = 'DESC';
		} else {
			if($frienduid) {
				$wheresql = "a.".DB::field('uid', $frienduid);
			}
			$ordersql = 'DESC';
		}
		if($view != 'all') {
		} elseif(empty($order)) {
			$ordersql = 'DESC';
		}
		if($searchkey) {
			$threadsql .= " AND t.subject LIKE ".DB::quote('%'.addslashes($searchkey).'%');
		}
		if($count) {
			return DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_activity')." a $apply_sql WHERE $wheresql"),0);
		}
		if($view == 'all' && $order == 'hot') {
			$apply_sql = '';
		}
		$threadsql = empty($threadsql) ? '' : $threadsql.' AND ';
		return DB::fetch_all("SELECT a.*, t.* FROM ".DB::table('forum_activity')." a $apply_sql
			INNER JOIN ".DB::table('forum_thread')." t ON $threadsql t.tid=a.tid
			WHERE t.displayorder>'-1' AND $wheresql
			ORDER BY a.starttimefrom $ordersql ".DB::limit($start, $limit));
	}
	public function delete_by_tid($tids) {
		return DB::delete($this->_table, DB::field('tid', $tids));
	}
}

?>