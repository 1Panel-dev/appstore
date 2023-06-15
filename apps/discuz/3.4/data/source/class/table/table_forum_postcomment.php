<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_postcomment.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_postcomment extends discuz_table
{
	private $cic_for_fetch_postcomment_by_pid;

	public function __construct() {

		$this->_table = 'forum_postcomment';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function count_by_authorid($authorid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE authorid=%d', array($this->_table, $authorid));
	}

	public function count_by_pid($pid, $authorid = null, $score = null) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE pid=%d '.($authorid ? ' AND '.DB::field('authorid', $authorid) : null).($score ? ' AND '.DB::field('score', $score) : null), array($this->_table, $pid, $authorid, $score));
	}

	public function count_by_tid($tid, $authorid = null, $score = null) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE tid=%d '.($authorid ? ' AND '.DB::field('authorid', $authorid) : null).($score ? ' AND '.DB::field('score', $score) : null), array($this->_table, $tid, $authorid, $score));
	}

	public function count_by_search($tid = null, $pid = null, $authorid = null, $starttime = null, $endtime = null, $ip = null, $message = null) {
		$sql = '';
		$tid && $sql .= ' AND '.DB::field('tid', $tid);
		$pid && $sql .= ' AND '.DB::field('pid', $pid);
		$authorid && $sql .= ' AND '.DB::field('authorid', $authorid);
		$starttime && $sql .= ' AND '.DB::field('dateline', $starttime, '>=');
		$endtime && $sql .= ' AND '.DB::field('dateline', $endtime, '<');
		$ip && $sql .= ' AND '.DB::field('useip', str_replace('*', '%', $ip), 'like');
		if($message) {
			$sqlmessage = '';
			$or = '';
			$message = explode(',', str_replace(' ', '', $message));

			for($i = 0; $i < count($message); $i++) {
				if(preg_match("/\{(\d+)\}/", $message[$i])) {
					$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
					$sqlmessage .= " $or comment REGEXP '".$message[$i]."'";
				} else {
					$sqlmessage .= " $or ".DB::field('comment', '%'.$message[$i].'%', 'like');
				}
				$or = 'OR';
			}
			$sql .= " AND ($sqlmessage)";
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE authorid>-1 %i', array($this->_table, $sql));
	}

	public function fetch_all_by_search($tid = null, $pid = null, $authorid = null, $starttime = null, $endtime = null, $ip = null, $message = null, $start = null, $limit = null) {
		$sql = '';
		$tid && $sql .= ' AND '.DB::field('tid', $tid);
		$pid && $sql .= ' AND '.DB::field('pid', $pid);
		$authorid && $sql .= ' AND '.DB::field('authorid', $authorid);
		$starttime && $sql .= ' AND '.DB::field('dateline', $starttime, '>=');
		$endtime && $sql .= ' AND '.DB::field('dateline', $endtime, '<');
		$ip && $sql .= ' AND '.DB::field('useip', str_replace('*', '%', $ip), 'like');
		if($message) {
			$sqlmessage = '';
			$or = '';
			$message = explode(',', str_replace(' ', '', $message));

			for($i = 0; $i < count($message); $i++) {
				if(preg_match("/\{(\d+)\}/", $message[$i])) {
					$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
					$sqlmessage .= " $or comment REGEXP '".$message[$i]."'";
				} else {
					$sqlmessage .= " $or ".DB::field('comment', '%'.$message[$i].'%', 'like');
				}
				$or = 'OR';
			}
			$sql .= " AND ($sqlmessage)";
		}
		return DB::fetch_all('SELECT * FROM %t WHERE authorid>-1 %i ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $sql));
	}

	public function fetch_all_by_authorid($authorid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE authorid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $authorid));
	}

	public function fetch_all_by_pid($pids) {
		if(empty($pids)) {
			return array();
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('pid', $pids).' ORDER BY dateline DESC', array($this->_table));
	}

	public function fetch_all_by_pid_score($pid, $score) {
		return DB::fetch_all('SELECT * FROM %t WHERE pid=%d AND score=%d', array($this->_table, $pid, $score));
	}

	public function fetch_standpoint_by_pid($pid) {
		return DB::fetch_first('SELECT * FROM %t WHERE pid=%d AND authorid=-1', array($this->_table, $pid));
	}

	public function update_by_pid($pids, $data, $unbuffered = false, $low_priority = false, $authorid = null) {
		if(empty($data)) {
			return false;
		}
		$where = array();
		$where[] = DB::field('pid', $pids);
		$authorid !== null && $where[] = DB::field('authorid', $authorid);
		return DB::update($this->_table, $data, implode(' AND ', $where), $unbuffered, $low_priority);
	}

	public function delete_by_authorid($authorids, $unbuffered = false, $rpid = false) {
		if(empty($authorids)) {
			return false;
		}
		$where = array();
		$where[] = DB::field('authorid', $authorids);
		$rpid && $where[] = DB::field('rpid', 0, '>');
		return DB::delete($this->_table, implode(' AND ', $where), null, $unbuffered);
	}

	public function delete_by_tid($tids, $unbuffered = false, $authorids = null) {
		$where = array();
		$where[] = DB::field('tid', $tids);
		$authorids !== null && !(is_array($authorids) && empty($authorids)) && $where[] = DB::field('authorid', $authorids);
		return DB::delete($this->_table, implode(' AND ', $where), null, $unbuffered);
	}

	public function delete_by_pid($pids, $unbuffered = false, $authorid = null) {
		$where = array();
		$where[] = DB::field('pid', $pids);
		$authorid !== null && !(is_array($authorid) && empty($authorid)) && $where[] = DB::field('authorid', $authorid);
		return DB::delete($this->_table, implode(' AND ', $where), null, $unbuffered);
	}

	public function delete_by_rpid($rpids, $unbuffered = false) {
		if(empty($rpids)) {
			return false;
		}
		return DB::delete($this->_table, DB::field('rpid', $rpids), null, $unbuffered);
	}
	public function fetch_postcomment_by_pid($pids, $postcache, $commentcount, $totalcomment, $commentnumber) {
		$query = DB::query("SELECT * FROM ".DB::table('forum_postcomment')." WHERE pid IN (".dimplode($pids).') ORDER BY dateline DESC');
		$commentcount = $comments = array();
		while($comment = DB::fetch($query)) {
			if($comment['authorid'] > '-1') {
				$commentcount[$comment['pid']]++;
			}
			if(count($comments[$comment['pid']]) < $commentnumber && $comment['authorid'] > '-1') {
				$comment['avatar'] = avatar($comment['authorid'], 'small');
				$comment['comment'] = str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment['comment']));
				$comments[$comment['pid']][] = $comment;
			}
			if($comment['authorid'] == '-1') {
				$this->cic_for_fetch_postcomment_by_pid = 0;
				$totalcomment[$comment['pid']] = preg_replace_callback('/<i>([\.\d]+)<\/i>/', array($this, 'fetch_postcomment_by_pid_callback_1'), $comment['comment']);
			}
			$postcache[$comment['pid']]['comment']['count'] = $commentcount[$comment['pid']];
			$postcache[$comment['pid']]['comment']['data'] = $comments[$comment['pid']];
			$postcache[$comment['pid']]['comment']['totalcomment'] = $totalcomment[$comment['pid']];
		}
		return array($comments, $postcache, $commentcount, $totalcomment);
	}

	public function fetch_postcomment_by_pid_callback_1($matches) {
		return '<i class="cmstarv" style="background-position:20px -'.(intval($matches[1]) * 16).'px">'.sprintf('%1.1f', $matches[1]).'</i>'.($this->cic_for_fetch_postcomment_by_pid++ % 2 ? '<br />' : '');
	}
}

?>