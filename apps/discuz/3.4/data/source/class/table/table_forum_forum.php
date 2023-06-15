<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_forum.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_forum extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_forum';
		$this->_pk    = 'fid';
		$this->_pre_cache_key = 'forum_forum_';

		parent::__construct();
	}

	public function fetch_all_by_status($status, $orderby = 1) {
		$status = $status ? 1 : 0;
		$ordersql = $orderby ? 'ORDER BY t.type, t.displayorder' : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table)." t WHERE t.status='$status' $ordersql");
	}
	public function fetch_all_fids($allstatus = 0, $type = '', $fup = '', $start = 0, $limit = 0, $count = 0) {
		$typesql = empty($type) ? "type<>'group'" : DB::field('type', $type);
		$statussql = empty($allstatus) ? ' AND status<>3' : '';
		$fupsql = empty($fup) ? '' : ' AND '.DB::field('fup', $fup);
		$limitsql = empty($limit) ? '' : ' LIMIT '.$start.', '.$limit;
		if($count) {
			return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table)." WHERE $typesql $statussql $fupsql");
		}
		return DB::fetch_all("SELECT * FROM ".DB::table($this->_table)." WHERE $typesql $statussql $fupsql $limitsql");
	}
	public function fetch_info_by_fid($fid) {
		$cache_name = $fid . "_with_fields";
		if(($data = $this->fetch_cache($cache_name)) === false) {
			$data = DB::fetch_first("SELECT ff.*, f.* FROM %t f LEFT JOIN %t ff ON ff.fid=f.fid WHERE f.fid=%d", array($this->_table, 'forum_forumfield', $fid));
			$this->store_cache($cache_name, $data);
		}
		return $data;
	}
	public function fetch_all_name_by_fid($fids) {
		if(empty($fids)) {
			return array();
		}
		return DB::fetch_all('SELECT fid, name FROM '.DB::table($this->_table)." WHERE ".DB::field('fid', $fids), array(), 'fid');
	}
	public function fetch_all_info_by_fids($fids, $status = 0, $limit = 0, $fup = 0, $displayorder = 0, $onlyforum = 0, $noredirect = 0, $type = '', $start = 0) {
		$sql = $fids ? "f.".DB::field('fid', $fids) : '';
		$sql .= empty($fup) ? '' : ($sql ? ' AND ' : '').'f.'.DB::field('fup', $fup);
		if(!strcmp($status, 'available')) {
			$sql .= ($sql ? ' AND ' : '')." f.status>'0'";
		} elseif($status) {
			$sql .= $status ? ($sql ? ' AND ' : '')." f.".DB::field('status', $status) : '';
		}
		$sql .= $onlyforum ? ($sql ? ' AND ' : '').'f.type<>\'group\'' : '';
		$sql .= $type ? ($sql ? ' AND ' : '').'f.'.DB::field('type', $type) : '';
		$sql .= $noredirect ? ($sql ? ' AND ' : '').'ff.redirect=\'\'' : '';
		$ordersql = $displayorder ? ' ORDER BY f.displayorder' : '';
		$limitsql = $limit ? DB::limit($start, $limit) : '';
		if(!$sql) {
			return array();
		}
		return DB::fetch_all("SELECT ff.*, f.* FROM %t f LEFT JOIN %t ff USING (fid) WHERE $sql $ordersql $limitsql", array($this->_table, 'forum_forumfield'), 'fid');
	}
	public function fetch_all_default_recommend($num = 10) {
		return DB::fetch_all("SELECT f.fid, f.name, ff.description, ff.icon FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type='sub' ORDER BY f.commoncredits desc ".DB::limit($num));
	}
	public function fetch_all_group_type($alltypeorder = 0) {
		$ordersql = empty($alltypeorder) ? 'f.type, ' : "f.type<>'group', ";
		return DB::fetch_all("SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.forumcolumns, f.inheritedmod, ff.moderators, ff.password, ff.redirect, ff.groupnum FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type IN('group', 'forum') ORDER BY $ordersql f.displayorder");
	}
	public function fetch_all_recommend_by_fid($fid) {
		return DB::fetch_all("SELECT ff.*, f.* FROM %t f LEFT JOIN %t ff ON ff.fid=f.fid WHERE f.recommend=%d", array($this->_table, 'forum_forumfield', $fid));
	}
	public function fetch_all_info_by_ignore_fid($fid) {
		if(!intval($fid)) {
			return array();
		}
		return DB::fetch_all("SELECT fid, type, name, fup FROM ".DB::table($this->_table)." WHERE ".DB::field('fid', $fid, '<>')." AND type<>'sub' AND status<>'3' ORDER BY displayorder");
	}
	public function fetch_all_forum($status = 0) {
		$statusql = intval($status) ? 'f.'.DB::field('status', $status) : 'f.status<>\'3\'';
		return DB::fetch_all("SELECT ff.*, f.*, a.uid FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid LEFT JOIN ".DB::table('forum_access')." a ON a.fid=f.fid AND a.allowview>'0' WHERE $statusql ORDER BY f.type, f.displayorder");
	}
	public function fetch_all_subforum_by_fup($fups) {
		return DB::fetch_all("SELECT fid, fup, name, threads, posts, todayposts, domain FROM %t WHERE status='1' AND fup IN (%n) AND type='sub' ORDER BY displayorder", array($this->_table, $fups));
	}
	public function fetch_all_forum_ignore_access() {
		return DB::fetch_all("SELECT ff.*, f.* FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE status <3 ORDER BY f.fid");
	}
	public function fetch_all_forum_for_sub_order() {
		return DB::fetch_all("SELECT ff.*, f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.inheritedmod FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status<>'3' ORDER BY f.type<>'group', f.displayorder");
	}
	public function fetch_all_valid_forum() {
		return DB::fetch_all("SELECT * FROM ".DB::table($this->_table)." WHERE status='1' AND type IN ('forum', 'sub') ORDER BY type");
	}
	public function fetch_all_valid_fieldinfo() {
		return DB::fetch_all("SELECT ff.* FROM ".DB::table($this->_table)." f INNER JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='1'");
	}
	public function fetch_threadcacheon_num() {
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->_table)." WHERE status='1' AND threadcaches>0");
	}
	public function fetch_all_by_recyclebin($recyclebin = 0) {
		return DB::fetch_all('SELECT fid, name FROM %t WHERE status<3 AND type IN (\'forum\', \'sub\') AND recyclebin=%d', array($this->_table, $recyclebin));
	}
	public function update($val, $data, $unbuffered = false, $low_priority = false) {
		$this->clear_cache(array($val, $val."_with_fields"));
		return parent::update($val, $data, $unbuffered, $low_priority);
	}
	public function update_threadcaches($threadcache, $fids) {
		if(empty($fids)) {
			return false;
		}
		$sqladd = in_array('all', $fids) ? '' :  ' WHERE '.DB::field('fid', $fids);
		DB::query("UPDATE ".DB::table($this->_table)." SET threadcaches='".intval($threadcache)."'$sqladd");
	}
	public function update_styleid($ids) {
		DB::query("UPDATE ".DB::table($this->_table)." SET styleid='0' WHERE styleid IN(%n)",array($ids));
	}
	public function fetch_forum_num($type = '', $fup = '') {
		$fupsql = $fup ? DB::field('fup', $fup).' AND ' : '';
		$addwhere = $type == 'group' ? "`status`='3'" : "`status`<>3";
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->_table)." WHERE $fupsql $addwhere");
	}
	public function check_forum_exists($fids, $issub = 1) {
		if(empty($fids)) {
			return false;
		}
		$typesql = $issub ? " AND type<>'group'" : '';
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->_table)." WHERE %i".$typesql, array(DB::field('fid', $fids)));
	}
	public function fetch_sum_todaypost() {
		return DB::result_first("SELECT sum(todayposts) FROM ".DB::table($this->_table));
	}
	public function fetch_group_counter() {
		return DB::fetch_first("SELECT SUM(todayposts) AS todayposts, COUNT(fid) AS groupnum FROM ".DB::table($this->_table)." WHERE status='3' AND type='sub'");
	}
	public function fetch_all_sub_group_by_fup($fups, $limit = 20) {
		return DB::fetch_all("SELECT fid, fup, name FROM %t WHERE fup IN(%n) AND type='sub' AND level>'-1' ORDER BY commoncredits DESC LIMIT %d", array($this->_table, $fups, $limit), $this->_pk);
	}
	public function fetch_all_for_threadsorts() {
		return DB::fetch_all("SELECT f.fid, f.name, ff.threadsorts FROM ".DB::table($this->_table)." f , ".DB::table('forum_forumfield')." ff WHERE ff.threadsorts<>'' AND f.fid=ff.fid");
	}

	public function fetch_all_for_search($conditions, $start = 0, $limit = 20) {
		if(empty($conditions)) {
			return array();
		}
		if($start == -1) {
			return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid
			WHERE status='3' AND type='sub' AND %i", array($conditions));
		}
		return DB::fetch_all("SELECT f.fid, f.fup, f.type, f.name, f.posts, f.threads, ff.membernum, ff.lastupdate, ff.dateline, ff.foundername, ff.founderuid FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid
			WHERE status='3' AND type='sub' AND %i ".DB::limit($start, $limit), array($conditions));
	}
	public function clear_todayposts() {
		DB::query("UPDATE ".DB::table($this->_table)." SET todayposts='0'");
	}
	public function clear_forum_counter_for_group() {
		DB::query("UPDATE ".DB::table($this->_table)." SET threads='0', posts='0' WHERE type='group'");
	}
	public function update_forum_counter($fid, $threads = 0, $posts = 0, $todayposts = 0, $modwork = 0, $favtimes = 0) {
		if(!dintval($fid)) {
			return false;
		}
		$addsql = array();
		if($threads) {
			$addsql[] = "threads=threads+'".intval($threads)."'";
		}
		if($posts) {
			$addsql[] = "posts=posts+'".intval($posts)."'";
		}
		if($todayposts) {
			$addsql[] = "todayposts=todayposts+'".intval($todayposts)."'";
		}
		if($modwork) {
			$addsql[] = "modworks='1'";
		}
		if($favtimes) {
			$addsql[] = "favtimes=favtimes+'".intval($favtimes)."'";
		}
		if($addsql) {
			DB::query("UPDATE ".DB::table($this->_table)." SET ".implode(', ', $addsql)." WHERE ".DB::field('fid', $fid), 'UNBUFFERED');
		}
	}
	public function update_commoncredits($fid) {
		if(!intval($fid)) {
			return false;
		}
		DB::query("UPDATE ".DB::table($this->_table)." SET commoncredits=commoncredits+1 WHERE ".DB::field('fid', $fid));
	}

	public function update_oldrank_and_yesterdayposts() {
		DB::query("UPDATE ".DB::table($this->_table).' SET oldrank=`rank`,yesterdayposts=todayposts');
	}
	public function update_group_level($levelid, $fid) {
		if(!intval($levelid) || !intval($fid)) {
			return false;
		}
		DB::query("UPDATE ".DB::table($this->_table)." SET level=%d WHERE fid=%d", array($levelid, $fid));
	}
	public function fetch_all_fid_for_group($start, $limit, $issub = 0, $conditions = '') {
		if(!empty($conditions) && !is_string($conditions)) {
			return array();
		}
		$typesql = $issub ? 'type=\'sub\'' : 'type<>\'sub\'';
		return DB::fetch_all("SELECT fid FROM ".DB::table($this->_table)." WHERE status='3' AND $typesql %i ".DB::limit($start, $limit), array($conditions));
	}
	public function fetch_groupnum_by_fup($fup) {
		if(!intval($fup)) {
			return false;
		}
		return DB::result_first("SELECT COUNT(*) as num FROM ".DB::table($this->_table)." WHERE fup=%d AND type='sub' GROUP BY fup", array($fup));
	}
	public function fetch_all_group_for_ranking() {
		return DB::fetch_all("SELECT fid FROM ".DB::table($this->_table)." WHERE type='sub' AND status='3' ORDER BY commoncredits DESC LIMIT 0, 1000");
	}
	public function fetch_all_for_ranklist($status, $type, $orderfield, $start = 0, $limit = 0, $ignorefids = array()) {
		if(empty($orderfield)) {
			return array();
		}
		$typesql = $type ? ' AND f.'.DB::field('type', $type) : ' AND f.type<>\'group\'';
		$ignoresql = $ignorefids ? ' AND f.fid NOT IN('.dimplode($ignorefids).')' : '';
		if($orderfield == 'membernum') {
			$fields = ', ff.membernum';
			$jointable = ' LEFT JOIN '.DB::table('forum_forumfield').' ff ON ff.fid=f.fid';
			$orderfield = 'ff.'.$orderfield;
		}
		return DB::fetch_all("SELECT f.* $fields FROM %t f $jointable WHERE f.status=%d $typesql $ignoresql ORDER BY %i DESC ".DB::limit($start, $limit), array($this->_table, $status, $orderfield));
	}
	public function fetch_fid_by_name($name) {
		return DB::result_first("SELECT fid FROM %t WHERE name=%s", array($this->_table, $name));
	}
	public function insert_group($fup, $type, $name, $status, $level) {
		DB::query("INSERT INTO %t (fup, type, name, status, level) VALUES (%d, %s, %s, %d, %d)", array($this->_table, $fup, $type, $name, $status, $level));
		return DB::insert_id();
	}
	public function fetch_all_by_fid($fids) {
		return DB::fetch_all("SELECT * FROM %t WHERE fid IN(%n)", array($this->_table, (array)$fids), $this->_pk);
	}
	public function delete_by_fid($fids) {
		if(empty($fids)) {
			return false;
		}
		DB::query("DELETE FROM ".DB::table($this->_table)." WHERE %i", array(DB::field('fid', $fids)));
		DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE %i", array(DB::field('fid', $fids)));
	}
	public function update_fup_by_fup($sourcefup, $targetfup) {
		DB::query("UPDATE ".DB::table($this->_table)." SET fup=%d WHERE fup=%s", array($targetfup, $sourcefup));
	}
	public function validate_level_for_group($fids) {
		if(empty($fids)) {
			return false;
		}
		DB::query("UPDATE ".DB::table($this->_table)." SET level='0' WHERE %i", array(DB::field('fid', $fids)));
	}
	public function validate_level_num() {
		return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table)." WHERE status='3' AND level='-1'");
	}
	public function fetch_all_validate($start, $limit) {
		return DB::fetch_all("SELECT f.*, ff.dateline, ff.founderuid, ff.foundername, ff.description FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE status='3' AND level='-1' ORDER BY f.fid DESC LIMIT ".intval($start).', '.intval($limit));
	}
	public function update_archive($fids) {
		return DB::update('forum_forum', array('archive' => '0'), "fid NOT IN (".dimplode($fids).")");
	}
	public function fetch_all_for_grouplist($orderby = 'displayorder', $fieldarray = array(), $num = 1, $fids = array(), $sort = 0, $getcount = 0) {
		if($fieldarray && is_array($fieldarray)) {
			$fieldadd = '';
			foreach($fieldarray as $field) {
				$fieldadd .= $field.', ';
			}
		} else {
			$fieldadd = 'ff.*, ';
		}
		$start = 0;
		if(is_array($num)) {
			list($start, $snum) = $num;
		} else {
			$snum = $num;
		}
		$orderbyarray = array('displayorder' => 'f.displayorder DESC', 'dateline' => 'ff.dateline DESC', 'lastupdate' => 'ff.lastupdate DESC', 'membernum' => 'ff.membernum DESC', 'thread' => 'f.threads DESC', 'activity' => 'f.commoncredits DESC');
		$useindex = $orderby == 'displayorder' ? 'USE INDEX(fup_type)' : '';
		$orderby = !empty($orderby) && $orderbyarray[$orderby] ? "ORDER BY ".$orderbyarray[$orderby].', f.fid DESC' : 'ORDER BY f.fid DESC';
		$limitsql = $num ? "LIMIT $start, $snum " : '';
		$field = $sort ? 'fup' : 'fid';
		$fids = $fids && is_array($fids) ? 'f.'.$field.' IN ('.dimplode($fids).')' : '';
		if(empty($fids)) {
			 $levelsql = " AND f.level>'-1'";
		}

		$fieldsql = $fieldadd.' f.fid, f.name, f.threads, f.posts, f.todayposts, f.level as flevel ';
		if($getcount) {
			return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table)." f $useindex WHERE".($fids ? " $fids AND " : '')." f.type='sub' AND f.status=3 $levelsql");
		}
		return DB::fetch_all("SELECT $fieldsql FROM ".DB::table($this->_table)." f $useindex LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid WHERE".($fids ? " $fids AND " : '')." f.type='sub' AND f.status=3 $levelsql $orderby $limitsql");
	}

	function fetch_table_struct($tablename, $result = 'FIELD') {
		if(empty($tablename)) {
			return array();
		}
		$datas = array();
		$query = DB::query("DESCRIBE ".DB::table($tablename));
		while($data = DB::fetch($query)) {
			$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
		}
		return $datas;
	}

	function get_forum_by_fid($fid, $field = '', $table = 'forum') {
		static $forumlist = array('forum' => array(), 'forumfield' => array());
		$table = $table != 'forum' ? 'forumfield' : 'forum';
		$return = array();
		if(!array_key_exists($fid, $forumlist[$table])) {
			$forumlist[$table][$fid] = DB::fetch_first("SELECT * FROM ".DB::table('forum_'.$table)." WHERE fid=%d", array($fid));
			if(!is_array($forumlist[$table][$fid])) {
				$forumlist[$table][$fid] = array();
			}
		}

		if(!empty($field)) {
			$return = isset($forumlist[$table][$fid][$field]) ? $forumlist[$table][$fid][$field] : null;
		} else {
			$return = $forumlist[$table][$fid];
		}
		return $return;
	}
}

?>