<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_thread.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class table_forum_thread extends discuz_table
{
	private $_posttableid = array();
	private $_urlparam = array();

	public function __construct() {

		$this->_table = 'forum_thread';
		$this->_pk    = 'tid';
		$this->_pre_cache_key = 'forum_thread_';
		parent::__construct();
	}

	public function fetch($tid, $tableid = 0) {
		$tid = intval($tid);
		$data = array();
		if($tid && ($data = $this->fetch_cache($tid)) === false) {
			$parameter = array($this->get_table_name($tableid), $tid);
			$data = DB::fetch_first("SELECT * FROM %t WHERE tid=%d", $parameter);
			if(!empty($data)) $this->store_cache($tid, $data, $this->_cache_ttl);
		}
		return $data;
	}

	public function fetch_by_tid_displayorder($tid, $displayorder = null, $glue = '>=',  $authorid = null, $tableid = 0) {
		$data = $this->fetch($tid, $tableid);
		if(!empty($data)) {
			if(($displayorder !== null && !($this->compare_number($data['displayorder'], $displayorder, $glue))) || ($authorid !== null && $data['authorid'] != $authorid)) {
				$data = array();
			}
		}
		return $data;
	}

	public function fetch_by_fid_displayorder($fid, $displayorder = 0, $glue = '>=', $order = 'lastpost', $sort = 'DESC') {
		$fid = intval($fid);
		if(!empty($fid)) {
			$parameter = array($this->get_table_name(), $fid,  $displayorder);
			$glue = helper_util::check_glue($glue);
			$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, $sort) : '';
			return DB::fetch_first("SELECT * FROM %t WHERE fid=%d AND displayorder{$glue}%d $ordersql ".DB::limit(0, 1), $parameter);
		}
		return array();
	}
	public function fetch_next_tid_by_fid_lastpost($fid, $lastpost, $glue = '>', $sort = 'DESC', $tableid = 0) {
		$glue = helper_util::check_glue($glue);
		return DB::result_first("SELECT tid FROM %t WHERE fid=%d AND displayorder>=0 AND closed=0 AND lastpost{$glue}%d  ORDER BY ".DB::order('lastpost', $sort).DB::limit(1), array($this->get_table_name($tableid), $fid, $lastpost));
	}
	public function fetch_by_tid_fid_displayorder($tid, $fid, $displayorder = null, $tableid = 0, $glue = '>=') {
		if($tid) {
			$data = $this->fetch($tid, $tableid);
			if(!empty($data)) {
				if(($data['fid'] != $fid) || ($displayorder !== null && !($this->compare_number($data['displayorder'], $displayorder, $glue)))) {
					$data = array();
				}
			}
			return $data;
		}
		return array();
	}
	public function fetch_thread_table_ids() {
		$threadtableids = array('0' => 0);
		$db = DB::object();
		$query = $db->query("SHOW TABLES LIKE '".str_replace('_', '\_', DB::table('forum_thread').'_%')."'");
		while($table = $db->fetch_array($query, $db->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM)) {
			$tablename = $table[0];
			$tableid = intval(substr($tablename, strrpos($tablename, '_') + 1));
			if(empty($tableid)) {
				continue;
			}
			$threadtableids[$tableid] = $tableid;
		}
		return $threadtableids;
	}

	public function fetch_all_by_digest_displayorder($digest, $digestglue = '=', $displayorder = 0, $glue = '>=', $start = 0, $limit = 0, $tableid = 0) {
		$parameter = array($this->get_table_name($tableid), $digest, $displayorder);
		$digestglue = helper_util::check_glue($digestglue);
		$glue = helper_util::check_glue($glue);
		return DB::fetch_all("SELECT * FROM %t WHERE digest{$digestglue}%d AND displayorder{$glue}%d".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_by_fid_typeid_displayorder($fid, $typeid = null, $displayorder = null, $glue = '=', $start = 0, $limit = 0) {

		$parameter = array($this->get_table_name(), $fid);
		$wherearr = array();
		$wherearr[] = is_array($fid) ? 'fid IN(%n)' : 'fid=%d';

		if($typeid) {
			$parameter[] = $typeid;
			$wherearr[] = "typeid=%d";
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$glue = helper_util::check_glue($glue);
			$wherearr[] = "displayorder{$glue}%d";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY lastpost DESC ".DB::limit($start, $limit), $parameter, $this->_pk);
	}
	public function fetch_all_by_fid_lastpost($fid, $lstart = 0, $lend = 0, $tableid = 0) {
		$parameter = array($this->get_table_name($tableid), $fid);
		$wherearr = array();
		$wherearr[] = is_array($fid) ? 'fid IN(%n)' : 'fid=%d';
		$wherearr[] = 'displayorder=0';
		if($lstart) {
			$wherearr[] = 'lastpost>%d';
			$parameter[] = $lstart;
		}
		if($lend) {
			$wherearr[] = 'lastpost<%d';
			$parameter[] = $lend;
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY lastpost DESC ".DB::limit(0, 100), $parameter, $this->_pk);
	}

	public function fetch_all_by_authorid_displayorder($authorid, $displayorder = null, $dglue = '=', $closed = null, $subject = '', $start = 0, $limit = 0, $replies = null, $fid = null, $rglue = '>=', $tableid = 0) {

		$parameter = array($this->get_table_name($tableid));
		$wherearr = array();
		if(!empty($authorid)) {
			$authorid = dintval($authorid, true);
			$parameter[] = $authorid;
			$wherearr[] = is_array($authorid) && $authorid ? 'authorid IN(%n)' : 'authorid=%d';
		}
		if($fid !== null) {
			$fid = dintval($fid, true);
			$parameter[] = $fid;
			$wherearr[] = is_array($fid) && $fid ? 'fid IN(%n)' : 'fid=%d';
		}
		if(getglobal('setting/followforumid')) {
			$parameter[] = getglobal('setting/followforumid');
			$wherearr[] = 'fid<>%d';
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$dglue = helper_util::check_glue($dglue);
			$wherearr[] = "displayorder{$dglue}%d";
		}
		if($closed !== null) {
			$parameter[] = $closed;
			$wherearr[] = "closed=%d";
		}
		if($replies !== null) {
			$parameter[] = $replies;
			$rglue = helper_util::check_glue($rglue);
			$wherearr[] = "replies{$rglue}%d";
		}
		if(!empty($subject)) {
			$parameter[] = '%'.$subject.'%';
			$wherearr[] = "subject LIKE %s";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY dateline DESC ".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_by_tid($tids, $start = 0, $limit = 0, $tableid = 0) {
		$data = array();
		if(($data = $this->fetch_cache($tids)) === false || count($tids) != count($data)) {
			if(is_array($data) && !empty($data)) {
				$tids = array_diff($tids, array_keys($data));
			}
			if($data === false) $data = array();
			if(!empty($tids)) {
				$parameter = array($this->get_table_name($tableid), $tids);
				$query = DB::query("SELECT * FROM %t WHERE tid IN(%n)".DB::limit($start, $limit), $parameter);
				while($value = DB::fetch($query)) {
					$data[$value['tid']] = $value;
					$this->store_cache($value['tid'], $value, $this->_cache_ttl);
				}
			}
		}
		return $data;
	}

	public function fetch_all_by_tid_displayorder($tids, $displayorder = null, $glue = '>=', $fids = array(), $closed = null) {
		$data = array();
		if(!empty($tids)) {
			$data = $this->fetch_all_by_tid((array)$tids);
			$fids = $fids && !is_array($fids) ? array($fids) : $fids;
			foreach($data as $tid => $value) {
				if($displayorder !== null && !(helper_util::compute($value['displayorder'], $displayorder, $glue))) {
					unset($data[$tid]);
				} elseif(!empty($fids) && !in_array($value['fid'], $fids)) {
					unset($data[$tid]);
				} elseif($closed !== null && $value['closed'] != $closed) {
					unset($data[$tid]);
				}
			}
		}
		return $data;
	}

	public function fetch_all_by_tid_fid_displayorder($tids, $fids = null, $displayorder = null, $order = 'dateline', $start = 0, $limit = 0, $glue = '>=', $sort = 'DESC', $tableid = 0) {
		$parameter = array($this->get_table_name($tableid));
		$wherearr = array();
		if(!empty($tids)) {
			$tids = dintval($tids, true);
			$parameter[] = $tids;
			$wherearr[] = is_array($tids) && $tids ? 'tid IN(%n)' : 'tid=%d';
		}
		if(!empty($fids)) {
			$fids = dintval($fids, true);
			$parameter[] = $fids;
			$wherearr[] = is_array($fids) && $fids ? 'fid IN(%n)' : 'fid=%d';
		}

		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$glue = helper_util::check_glue($glue);
			$wherearr[] = "displayorder{$glue}%d";
		}
		if($order) {
			$order = 'ORDER BY '.DB::order($order, $sort);
		}
		if(!empty($wherearr)) {
			$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
			return DB::fetch_all("SELECT * FROM %t $wheresql $order ".DB::limit($start, $limit), $parameter, $this->_pk);
		} else {
			return array();
		}
	}

	public function fetch_all_by_tid_or_fid($fid, $tids = array()) {
		$parameter = array($this->get_table_name(), $fid);
		$forumstickytids = '';
		if(!empty($tids)) {
			$tids = dintval($tids, true);
			$parameter[] = $tids;
			$forumstickytids = ' OR '.(is_array($tids) && $tids ? 'tid IN(%n)' : 'tid=%d');
		}
		return DB::fetch_all("SELECT * FROM %t WHERE fid=%d AND displayorder=1 $forumstickytids ORDER BY lastpost DESC", $parameter);
	}

	public function fetch_all_by_displayorder($displayorder = 0, $glue = '>=', $start = 0, $limit = 0, $tableid = 0) {
		$glue = helper_util::check_glue($glue);
		$displayorder = dintval($displayorder, true);
		return DB::fetch_all('SELECT * FROM %t WHERE %i '.DB::limit($start, $limit), array($this->get_table_name($tableid), DB::field('displayorder', $displayorder, $glue)));
	}

	public function fetch_all_by_authorid($authorid, $start = 0, $limit = 0, $tableid = 0) {
		$authorid = dintval($authorid, true);
		return DB::fetch_all("SELECT * FROM %t WHERE %i ORDER BY dateline DESC ".DB::limit($start, $limit), array($this->get_table_name($tableid), DB::field('authorid', $authorid)), $this->_pk);
	}

	public function fetch_all_by_dateline($starttime, $start = 0, $limit = 0, $order = 'dateline', $sort = 'DESC') {
		if($starttime) {
			$orderby = '';
			if(!empty($order)) {
				$orderby = "ORDER BY ".DB::order($order, $sort);
			}
			$parameter = array($this->get_table_name(), $starttime);
			return DB::fetch_all("SELECT * FROM %t WHERE dateline>=%d AND displayorder>'-1' $orderby ".DB::limit($start, $limit), $parameter, $this->_pk);
		}
		return array();
	}

	public function fetch_all_by_fid_displayorder($fids, $displayorder = null, $dateline = null, $recommends = null, $start = 0, $limit = 0, $order = 'dateline', $sort = 'DESC', $dglue = '>=') {
		$parameter = array($this->get_table_name());
		$wherearr = array();
		$fids = dintval($fids, true);
		if(!empty($fids)) {
			$parameter[] = $fids;
			$wherearr[] = is_array($fids) && $fids ? 'fid IN(%n)' : 'fid=%d';
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$dglue = helper_util::check_glue($dglue);
			$wherearr[] = "displayorder{$dglue}%d";
		}
		if($dateline !== null) {
			$parameter[] = $dateline;
			$wherearr[] = "dateline>=%d";
		}
		if($recommends !== null) {
			$parameter[] = $recommends;
			$wherearr[] = "recommends>%d";
		}
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, $sort) : '';
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql $ordersql ".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_new_thread_by_tid($tid = 0, $start = 0, $limit = 0, $tableid = 0, $glue = '>', $sort = 'ASC') {
		$glue = helper_util::check_glue($glue);
		return DB::fetch_all("SELECT * FROM %t WHERE tid{$glue}%d ORDER BY ".DB::order('tid', $sort).DB::limit($start, $limit), array($this->get_table_name($tableid), $tid), $this->_pk);
	}
	public function fetch_all_group_thread_by_fid_displayorder($fids, $displayorder = null, $dateline = null, $lastpost = null, $digest = null, $order = 'dateline', $start = 0, $limit = 0, $dglue = '>=') {
		$fids = dintval($fids, true);
		$parameter = array($this->get_table_name(), $fids);
		$wherearr = array();
		$wherearr[] = is_array($fids) && $fids ? 'fid IN(%n)' : 'fid=%d';
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$dglue = helper_util::check_glue($dglue);
			$wherearr[] = "displayorder{$dglue}%d";
		}
		if($dateline !== null) {
			$parameter[] = $dateline;
			$wherearr[] = "dateline>=%d";
		}
		if($lastpost !== null) {
			$parameter[] = $lastpost;
			$wherearr[] = "lastpost>=%d";
		}
		if($digest !== null) {
			$parameter[] = $digest;
			$wherearr[] = "$digest>%d";
		}
		$ordersql = !empty($order) ? 'ORDER BY'.DB::order($order, 'DESC') : '';
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql $ordersql ".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_by_fid_authorid_displayorder($fids, $authorid, $displayorder = null, $lastpost = 0, $start = 0, $limit = 0) {
		$parameter = array($this->get_table_name());
		$wherearr = array();
		if($authorid) {
			$authorid = dintval($authorid, true);
			$parameter[] = $authorid;
			$wherearr[] = is_array($authorid) ? 'authorid IN(%n)' : 'authorid=%d';
		}
		$fids = dintval($fids, true);
		$parameter[] = $fids;
		$wherearr[] = is_array($fids) ? 'fid IN(%n)' : 'fid=%d';
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$wherearr[] = "displayorder=%d";
		}
		if($lastpost) {
			$parameter[] = $lastpost;
			$wherearr[] = "lastpost>%d";
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY lastpost DESC ".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_by_tid_fid($tids, $fids = array(), $isgroup = -1, $author = '', $subject = '', $start = 0, $limit = 0) {
		$data = array();
		$condition = $this->make_query_condition($tids, $fids, $isgroup, $author, $subject);
		$query = DB::query("SELECT * FROM %t $condition[0]".DB::limit($start, $limit), $condition[1]);
		while($value = DB::fetch($query)) {
			$data[$value['tid']] = $value;
			$this->_posttableid[$value['posttableid']][] = $value['tid'];
		}
		return $data;
	}

	public function fetch_all_by_fid($fids, $start = 0, $limit = 0, $tableid = 0) {
		$fids = dintval($fids, true);
		if($fids) {
			return DB::fetch_all("SELECT * FROM %t WHERE fid IN(%n) ".DB::limit($start, $limit), array($this->get_table_name($tableid), (array)$fids));
		}
		return array();
	}

	public function fetch_all_by_replies($number, $start = 0, $limit = 0, $glue = '>', $tableid = 0) {
		$number = dintval($number);
		if($number) {
			$glue = helper_util::check_glue($glue);
			return DB::fetch_all("SELECT * FROM %t WHERE replies{$glue}%d ".DB::limit($start, $limit), array($this->get_table_name($tableid), $number));
		}
		return array();
	}

	public function fetch_all_rank_thread($dateline, $notfid, $order = 'dateline', $start = 0, $limit = 0) {
		$parameter = array($this->get_table_name());
		$data = $fids = $wherearr = array();
		if($dateline) {
			$parameter[] = $dateline;
			$wherearr[] = 'dateline>%d';
		}
		$wherearr[] = 'displayorder>=0';
		if($notfid) {
			$parameter[] = $notfid;
			$wherearr[] = 'fid NOT IN(%n)';
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, 'DESC') : '';
		$query = DB::query("SELECT tid, fid, author, authorid, subject, dateline, views, replies, favtimes, sharetimes, heats FROM %t $wheresql $ordersql ".DB::limit($start, $limit), $parameter);
		while($value = DB::fetch($query)) {
			$data[$value['tid']] = $value;
			$fids[$value['fid']][$value['tid']] = $value['tid'];
		}
		if(!empty($fids)) {
			foreach(C::t('forum_forum')->fetch_all_name_by_fid(array_keys($fids)) as $value) {
				foreach($fids[$value['fid']] as $tid) {
					$data[$tid]['forum'] = $value['name'];
				}
			}
		}
		return $data;
	}
	public function fetch_all_rank_poll($dateline, $notfid, $order = 'dateline', $start = 0, $limit = 0) {
		$parameter = array($this->get_table_name(), 'forum_poll');
		$wherearr = array('t.special=1');
		if($dateline) {
			$parameter[] = $dateline;
			$wherearr[] = 't.dateline>%d';
		}
		$wherearr[] = 't.displayorder>=0';
		if($notfid) {
			$parameter[] = $notfid;
			$wherearr[] = 't.fid NOT IN(%n)';
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, 'DESC') : '';
		return DB::fetch_all("SELECT t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.favtimes, t.sharetimes, t.heats,  p.pollpreview, p.voters FROM %t t LEFT JOIN %t p ON p.tid=t.tid $wheresql $ordersql ".DB::limit($start, $limit), $parameter, $this->_pk);
	}
	public function fetch_all_rank_activity($dateline, $notfid, $order = 'dateline', $start = 0, $limit = 0) {
		$parameter = array($this->get_table_name(), 'forum_activity');
		$wherearr = array('t.special=4', 't.isgroup=0', 't.closed=0');
		if($dateline) {
			$parameter[] = $dateline;
			$wherearr[] = 't.dateline>%d';
		}
		$wherearr[] = 't.displayorder>=0';
		if($notfid) {
			$parameter[] = $notfid;
			$wherearr[] = 't.fid NOT IN(%n)';
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		$ordersql = !empty($order) ? ' ORDER BY '.DB::order($order, 'DESC') : '';
		return DB::fetch_all("SELECT t.tid, t.subject, t.views, t.author, t.authorid, t.replies, t.heats, t.sharetimes, t.favtimes, act.aid, act.starttimefrom, act.starttimeto, act.place, act.class, act.applynumber, act.expiration FROM %t t LEFT JOIN %t act ON act.tid=t.tid $wheresql $ordersql ".DB::limit($start, $limit), $parameter, $this->_pk);
	}

	public function fetch_all_by_recyclebine($fid = 0, $isgroup = 0, $author = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '', $start = 0, $limit = 0) {
		$sql = $this->recyclebine_where($fid, $isgroup, $author, $username, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
		return DB::fetch_all('SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
				t.tid, t.fid, t.authorid, t.author, t.subject, t.views, t.replies, t.dateline, t.posttableid,
				tm.uid AS moduid, tm.username AS modusername, tm.dateline AS moddateline, tm.action AS modaction, tm.reason
				FROM '.DB::table('forum_thread').' t LEFT JOIN '.DB::table('forum_threadmod').' tm ON tm.tid=t.tid
				LEFT JOIN '.DB::table('forum_forum').' f ON f.fid=t.fid '.$sql[0].' ORDER BY t.dateline DESC '.DB::limit($start, $limit), $sql[1]);
	}

	public function fetch_all_recyclebin_by_dateline($dateline, $start = 0, $limit = 0) {
		return DB::fetch_all("SELECT tid FROM %t WHERE displayorder=-1 AND dateline<%d".DB::limit($start, $limit), array($this->_table, $dateline));
	}

	public function fetch_all_moderate($fid = 0, $displayorder = null, $isgroup = null, $dateline = null, $author = null, $subject = null) {
		$parameter = $this->make_query_condition(null, $fid, $isgroup, $author, $subject, $displayorder, $dateline);
		return DB::fetch_all('SELECT * FROM %t '.$parameter[0], $parameter[1], $this->_pk);
	}

	public function fetch_all_movedthread($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT t1.tid, t2.tid AS threadexists, f.status, t1.isgroup FROM %t t1
				LEFT JOIN %t t2 ON t2.tid=t1.closed AND t2.displayorder>=0 LEFT JOIN %t f ON f.fid=t1.fid
				WHERE t1.closed>1'.DB::limit($start, $limit), array($this->get_table_name(), $this->get_table_name(), 'forum_forum'));
	}

	public function fetch_all_by_fid_cover_lastpost($fid, $cover = null, $starttime = 0, $endtime = 0, $start = 0, $limit = 0) {
		$parameter = array($this->get_table_name(), $fid);
		$wherearr = array('fid=%d', 'displayorder>=0');
		if($cover !== null) {
			$wherearr[] = 'cover=%d';
			$parameter[] = $cover;
		}
		if($starttime) {
			$wherearr[] = 'lastpost>%d';
			$parameter[] = $starttime;
		}
		if($endtime) {
			$wherearr[] = 'lastpost<%d';
			$parameter[] = $endtime;
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all('SELECT * FROM %t '.$wheresql.DB::limit($start, $limit), $parameter, $this->_pk);
	}
	public function fetch_all_by_posttableid_displayorder($tableid = 0, $posttableid = 0, $displayorder = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE posttableid=%d AND displayorder>=%d ORDER BY lastpost'.DB::limit(1000), array($this->get_table_name($tableid), $posttableid, $displayorder), $this->_pk);
	}

	public function fetch_all_search($conditions, $tableid = 0, $start = 0, $limit = 0, $order = '', $sort = 'DESC', $forceindex='') {
		$ordersql = '';
		if(!empty($order)) {
			$ordersql =  " ORDER BY $order $sort ";
		}
		$data = array();
		$tlkey = !empty($conditions['inforum']) && !is_array($conditions['inforum']) ? $conditions['inforum'] : '';
		$firstpage = false;
		$defult = count($conditions) < 5 ? true : false;
		if(count($conditions) < 5) {
			foreach(array_keys($conditions) as $key) {
				if(!in_array($key, array('inforum', 'sticky', 'displayorder', 'intids'))) {
					$defult = false;
					break;
				}
			}
		}
		if(!defined('IN_MOBILE') && $defult && $conditions['sticky'] == 4 && $start == 0 && $limit && strtolower(preg_replace("/\s?/is", '', $order)) == 'displayorderdesc,lastpostdesc' && empty($sort)) {
			foreach($conditions['displayorder'] as $id) {
				if($id < 2) {
					$firstpage = true;
					if($id < 0) {
						$firstpage = false;
						break;
					}
				}
			}
			if(!$tableid && $firstpage && !empty($tlkey) && ($ttl = getglobal('setting/memory/forum_thread_forumdisplay')) !== null && ($data = $this->fetch_cache($tlkey, 'forumdisplay_')) !== false) {
				$delusers = $this->fetch_cache('deleteuids', '');
				if(!empty($delusers)) {
					foreach($data as $tid => $value) {
						if(isset($delusers[$value['authorid']])) {
							$data = array();
						}
					}
				}
				if($data) {
					return $data;
				}
			}
		}
		$data = DB::fetch_all("SELECT * FROM ".DB::table($this->get_table_name($tableid))." $forceindex".$this->search_condition($conditions)." $ordersql ".DB::limit($start, $limit));
		if(!defined('IN_MOBILE') && !$tableid && $firstpage && !empty($tlkey) && ($ttl = getglobal('setting/memory/forum_thread_forumdisplay')) !== null) {
			$this->store_cache($tlkey, $data, $ttl, 'forumdisplay_');
		}
		return $data;
	}

	public function fetch_all_by_special($special, $authorid = 0, $replies = 0, $displayorder = null, $subject = '', $join = 0, $start = 0, $limit = 0, $order = 'dateline', $sort = 'DESC') {
		$condition = $this->make_special_condition($special, $authorid, $replies, $displayorder, $subject, $join, 0);
		$ordersql = !empty($order) ? ' ORDER BY t.'.DB::order($order, $sort) : '';
		return DB::fetch_all("SELECT t.* FROM %t t $condition[jointable] ".$condition['where'].$ordersql.DB::limit($start, $limit), $condition['parameter'], $this->_pk);
	}
	public function fetch_all_heats() {
		$heatdateline = getglobal('timestamp') - 86400 * getglobal('setting/indexhot/days');
		$addtablesql = $addsql = '';
		if(!helper_access::check_module('group')) {
			$addtablesql = " LEFT JOIN ".DB::table('forum_forum')." f ON f.fid = t.fid ";
			$addsql = " AND f.status IN ('0', '1') ";
		}
		return DB::fetch_all("SELECT t.tid,t.posttableid,t.views,t.dateline,t.replies,t.author,t.authorid,t.subject,t.price
				FROM ".DB::table('forum_thread')." t $addtablesql
				WHERE t.dateline>'$heatdateline' AND t.heats>'0' AND t.displayorder>='0' $addsql ORDER BY t.heats DESC LIMIT ".(getglobal('setting/indexhot/limit') * 2));

	}

	private function make_query_condition($tids, $fids = array(), $isgroup = -1, $author = '', $subject = '', $displayorder = null, $dateline = null) {
		$parameter = array($this->get_table_name());
		$wherearr = array();
		if(!empty($tids)) {
			$tids = dintval($tids, true);
			$parameter[] = $tids;
			$wherearr[] = is_array($tids) ? 'tid IN(%n)' : 'tid=%d';
		}
		if(!empty($fids)) {
			$fids = dintval($fids, true);
			$parameter[] = $fids;
			$wherearr[] = is_array($fids) ? 'fid IN(%n)' : 'fid=%d';
		}
		if(in_array($isgroup, array(0, 1))) {
			$parameter[] = $isgroup;
			$wherearr[] = "isgroup=%d";
		}
		if(!empty($author)) {
			$parameter[] = $author;
			$wherearr[] = "author=%s";
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$wherearr[] = 'displayorder=%d';
		}
		if($dateline !== null) {
			$parameter[] = getglobal('timestamp') - $dateline;
			$wherearr[] = 'dateline>=%d';
		}
		if(!empty($subject)) {
			$parameter[] = '%'.$subject.'%';
			$wherearr[] = "subject LIKE %s";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}


	public function count_by_special($special, $authorid = 0, $replies = 0, $displayorder = null, $subject = '', $join = 0) {
		$condition = $this->make_special_condition($special, $authorid, $replies, $displayorder, $subject, $join, 0);
		return DB::result_first("SELECT COUNT(*) FROM %t t $condition[jointable] ".$condition['where'], $condition['parameter']);
	}
	private function make_special_condition($special, $authorid = 0, $replies = 0, $displayorder = null, $subject = '', $join = 0, $tableid = 0) {
		$wherearr = $condition = array();
		$parameter = array($this->get_table_name($tableid));
		if($authorid && !$join) {
			$authorid = dintval($authorid, true);
			$parameter[] = $authorid;
			$wherearr[] = is_array($authorid) && $authorid ? 't.authorid IN(%n)' : 't.authorid=%d';
		}
		$parameter[] = $special;
		$wherearr[] = 't.special=%d';
		if($replies) {
			$parameter[] = $replies;
			$wherearr[] = 't.replies>=%d';
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$wherearr[] = 't.displayorder>=%d';
		}
		if(!empty($subject)) {
			$parameter[] = '%'.$subject.'%';
			$wherearr[] = "subject LIKE %s";
		}
		if($join) {
			if($special == 1) {
				$parameter[] = $authorid;
				$wherearr[] = 'p.uid=%d';
				$wherearr[] = 'p.tid = t.tid';
				$condition['jointable'] = ', '.DB::table('forum_pollvoter').' p ';
			} elseif($special == 5) {
				$parameter[] = $authorid;
				$wherearr[] = 'p.authorid=%d';
				$wherearr[] = 'p.first=0';
				$wherearr[] = 'p.tid = t.tid';
				$posttable = getposttable();
				$condition['jointable'] = ', '.DB::table($posttable).' p ';
			}
		}
		$condition['parameter'] = $parameter;
		$condition['where'] = ' WHERE '.implode(' AND ', $wherearr);
		return $condition;
	}
	public function count_search($conditions, $tableid = 0, $prefix = false) {
		$prefix = $prefix ? '' : 't';
		return DB::result_first("SELECT COUNT(*) FROM %t $prefix %i", array($this->get_table_name($tableid), $this->search_condition($conditions, $prefix)));
	}

	public function search_condition($conditions, $prefix = false) {
		$this->_urlparam = $wherearr = array();
		if($prefix) {
			$prefix = 't.';
		}
		if($conditions['sourcetableid'] != '') {
			$this->_urlparam[] = "sourcetableid={$conditions['sourcetableid']}";
		}
		if($conditions['inforum'] != '' && $conditions['inforum'] != 'all') {
			$wherearr[] = $prefix.DB::field('fid', $conditions['inforum']);
			$this->_urlparam[] = "inforum={$conditions['inforum']}";
		}
		if($conditions['intids']) {
			$wherearr[] = $prefix.DB::field('tid', $conditions['intids']);
			$this->_urlparam[] = "intids={$conditions['intids']}";
		}
		if($conditions['tidmin'] != '') {
			$wherearr[] = $prefix.DB::field('tid', $conditions['tidmin'], '>=');
			$this->_urlparam[] = "tidmin={$conditions['tidmin']}";
		}

		if($conditions['tidmax'] != '') {
			$wherearr[] = $prefix.DB::field('tid', $conditions['tidmax'], '<=');
			$this->_urlparam[] = "tidmax={$conditions['tidmax']}";
		}
		if(isset($conditions['sticky'])) {
			if($conditions['sticky'] == 1) {
				$wherearr[] = $prefix.DB::field('displayorder', 0, '>');
				$this->_urlparam[] = "sticky=1";
			} elseif($conditions['sticky'] == 2) {
				$wherearr[] = $prefix.DB::field('displayorder', 0);
				$this->_urlparam[] = "sticky=2";
			} elseif($conditions['sticky'] == 3) {
				$wherearr[] = $prefix.DB::field('displayorder', -1);
				$this->_urlparam[] = "sticky=3";
			} elseif($conditions['sticky'] == 4) {
				$wherearr[] = $prefix.DB::field('displayorder', $conditions['displayorder']);
				$this->_urlparam[] = "sticky=4";
			} else {
				$wherearr[] = $prefix.DB::field('displayorder', 0, '>=');
				$this->_urlparam[] = "sticky=0";
			}
		}

		if($conditions['noreplydays']) {
			$conditions['noreplydays'] = intval($conditions['noreplydays']);
			$lastpost = getglobal('timestamp') - $conditions['noreplydays'] * 86400;
			$wherearr[] = $prefix.DB::field('lastpost', $lastpost, '<');
			$this->_urlparam[] = "noreplydays={$conditions['noreplydays']}";
		}
		if($conditions['lastpostless']) {
			$wherearr[] = $prefix.DB::field('lastpost', $conditions['lastpostless'], '<=');
			$this->_urlparam[] = "lastpostless={$conditions['lastpostless']}";
		}
		if($conditions['lastpostmore']) {
			$wherearr[] = $prefix.DB::field('lastpost', $conditions['lastpostmore'], '>=');
			$this->_urlparam[] = "lastpostmore={$conditions['lastpostmore']}";
		}

		if($conditions['intype'] != '' && $conditions['intype'] != 'all') {
			$wherearr[] = $prefix.DB::field('typeid', $conditions['intype']);
			$this->_urlparam[] = "intype={$conditions['intype']}";
		}
		if($conditions['insort'] != '' && $conditions['insort'] != 'all') {
			$wherearr[] = $prefix.DB::field('sortid', $conditions['insort']);
			$this->_urlparam[] = "insort={$conditions['insort']}";
		}
		if(isset($conditions['viewsless']) && $conditions['viewsless'] !== '') {
			$wherearr[] = $prefix.DB::field('views', $conditions['viewsless'], '<=');
			$this->_urlparam[] = "viewsless={$conditions['viewsless']}";
		}
		if(isset($conditions['viewsmore']) && $conditions['viewsmore'] !== '') {
			$wherearr[] = $prefix.DB::field('views', $conditions['viewsmore'], '>=');
			$this->_urlparam[] = "viewsmore={$conditions['viewsmore']}";
		}

		if(isset($conditions['repliesless']) && $conditions['repliesless'] !== '') {
			$wherearr[] = $prefix.DB::field('replies', $conditions['repliesless'], '<=');
			$this->_urlparam[] = "repliesless={$conditions['repliesless']}";
		}
		if(isset($conditions['repliesmore']) && $conditions['repliesmore'] !== '') {
			$wherearr[] = $prefix.DB::field('replies', $conditions['repliesmore'], '>=');
			$this->_urlparam[] = "repliesmore={$conditions['repliesmore']}";
		}
		if(isset($conditions['readpermmore']) && $conditions['readpermmore'] !== '') {
			$wherearr[] = $prefix.DB::field('readperm', $conditions['readpermmore'], '>');
			$this->_urlparam[] = "readpermmore={$conditions['readpermmore']}";
		}
		if(isset($conditions['pricesless']) && $conditions['pricesless'] !== '') {
			$wherearr[] = $prefix.DB::field('price', $conditions['pricesless'], '<');
			$this->_urlparam[] = "pricemore={$conditions['pricesless']}";
		}
		if(isset($conditions['pricemore']) && $conditions['pricemore'] !== '') {
			$wherearr[] = $prefix.DB::field('price', $conditions['pricemore'], '>');
			$this->_urlparam[] = "pricemore={$conditions['pricemore']}";
		}
		if($conditions['beforedays'] != '') {
			$dateline = getglobal('timestamp') - $conditions['beforedays']*86400;
			$wherearr[] = $prefix.DB::field('dateline', $dateline, '<');
			$this->_urlparam[] = "beforedays={$conditions['beforedays']}";
		}

		if($conditions['starttime'] != '') {
			$starttime = strtotime($conditions['starttime']);
			$wherearr[] = $prefix.DB::field('dateline', $starttime, '>');
			$this->_urlparam[] = "starttime={$conditions['starttime']}";
		}
		if($conditions['endtime'] != '') {
			$endtime = strtotime($conditions['endtime']);
			$wherearr[] = $prefix.DB::field('dateline', $endtime, '<=');
			$this->_urlparam[] = "endtime={$conditions['endtime']}";
		}
		$conditions['users'] = trim($conditions['users']);
		if(!empty($conditions['users'])) {
			$wherearr[] = $prefix.DB::field('author', explode(' ', trim($conditions['users'])));
			$this->_urlparam[] = "users={$conditions['users']}";
		}

		if($conditions['digest'] == 1) {
			$wherearr[] = $prefix.DB::field('digest', 0, '>');
			$this->_urlparam[] = "digest=1";
		} elseif($conditions['digest'] == 2) {
			$wherearr[] = $prefix.DB::field('digest', 0);
			$this->_urlparam[] = "digest=2";
		} elseif(is_array($conditions['digest'])) {
			$wherearr[] = $prefix.DB::field('digest', $conditions['digest']);
			$this->_urlparam[] = "digest=".implode(',', $conditions['digest']);
		}
		if($conditions['recommends']) {
			$wherearr[] = $prefix.DB::field('recommends', $conditions['recommends'], '>');
			$this->_urlparam[] = "recommends=".$conditions['recommends'];
		}
		if($conditions['authorid']) {
			$wherearr[] = $prefix.DB::field('authorid', $conditions['authorid']);
			$this->_urlparam[] = "authorid=".$conditions['authorid'];
		}
		if($conditions['attach'] == 1) {
			$wherearr[] = $prefix.DB::field('attachment', 0, '>');
			$this->_urlparam[] = "attach=1";
		} elseif($conditions['attach'] == 2) {
			$wherearr[] = $prefix.DB::field('attachment', 0);
			$this->_urlparam[] = "attach=2";
		}
		if($conditions['rate'] == 1) {
			$wherearr[] = $prefix.DB::field('rate', 0, '>');
			$this->_urlparam[] = "rate=1";
		} elseif($conditions['rate'] == 2) {
			$wherearr[] = $prefix.DB::field('rate', 0);
			$this->_urlparam[] = "rate=2";
		}
		if($conditions['highlight'] == 1) {
			$wherearr[] = $prefix.DB::field('highlight', 0, '>');
			$this->_urlparam[] = "highlight=1";
		} elseif($conditions['highlight'] == 2) {
			$wherearr[] = $prefix.DB::field('highlight', 0);
			$this->_urlparam[] = "highlight=2";
		}
		if($conditions['hidden'] == 1) {
			$wherearr[] = $prefix.DB::field('hidden', 0, '>');
			$this->_urlparam[] = "hidden=1";
		}
		if(!empty($conditions['special'])) {
			$this->_urlparam[] = "special={$conditions['special']}";
			if($conditions['specialthread'] == 1) {
				$wherearr[] = $prefix.DB::field('special', $conditions['special']);
				$this->_urlparam[] = "specialthread=1";
			} elseif($conditions['specialthread'] == 2) {
				$wherearr[] = $prefix.DB::field('special', $conditions['special'], 'notin');
				$this->_urlparam[] = "specialthread=2";
			}
		}
		if(isset($conditions['isgroup']) && in_array($conditions['isgroup'], array(0, 1))) {
			$wherearr[] = $prefix.DB::field('isgroup', $conditions['isgroup']);
		}

		if(trim($conditions['keywords'])) {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $conditions['keywords']));
			for($i = 0; $i < count($keywords); $i++) {
				$sqlkeywords .= " $or ".$prefix.DB::field('subject', '%'.$keywords[$i].'%', 'like');
				$or = 'OR';
			}
			$wherearr[] = "($sqlkeywords)";
			$this->_urlparam[] = "keywords={$conditions['keywords']}";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return $wheresql;
	}

	public function get_posttableid() {
		return $this->_posttableid;
	}
	public function get_url_param() {
		return $this->_urlparam;
	}

	public function update_displayorder_by_tid_displayorder($tids, $olddisplayorder, $newdisplayorder) {
		$tids = dintval((array)$tids, true);
		if($tids) {
			return DB::query('UPDATE %t SET displayorder=%d WHERE tid IN (%n) AND displayorder=%d', array($this->get_table_name(), $newdisplayorder, $tids, $olddisplayorder));
		}
		return 0;
	}

	public function update($tid, $data, $unbuffered = false, $low_priority = false, $tableid = 0, $realdata = false) {
		$tid = dintval($tid, true);
		if($data && is_array($data) && $tid) {
			if(!$realdata) {
				$num = DB::update($this->get_table_name($tableid), $data, DB::field('tid', $tid), $unbuffered, $low_priority);
				$this->update_batch_cache((array)$tid, $data);
			} else {
				$num = DB::query('UPDATE '.DB::table($this->get_table_name($tableid))." SET ".implode(',', $data)." WHERE ".DB::field('tid', $tid), 'UNBUFFERED');
				$this->clear_cache($tid);
			}
			return $num;
		}
		return !$unbuffered ? 0 : false;
	}

	public function update_by_fid($fid, $data, $tableid = 0) {
		$fid = dintval($fid, true);
		if($data && is_array($data) && $fid) {
			return DB::update($this->get_table_name($tableid), $data, DB::field('fid', $fid));
		}
		return array();
	}
	public function update_by_tid_displayorder($tid, $displayorder, $data, $fid = 0, $tableid = 0) {
		$condition = array();
		$tid = dintval($tid, true);
		$condition[] = DB::field('tid', $tid);
		if($fid) {
			$fid = dintval($fid, true);
			$condition[] = DB::field('fid', $fid);
		}
		$condition[] = DB::field('displayorder', $displayorder);
		if($data && is_array($data) && $tid) {
			return DB::update($this->get_table_name($tableid), $data, implode(' AND ', $condition));
		}
		return 0;
	}
	public function update_by_closed($tids, $data, $tableid = 0) {
		$tids = dintval($tids, true);
		if(!empty($data) && is_array($data)) {
			$num = DB::update($this->get_table_name($tableid), $data, DB::field('closed', $tids), true);
			if($num) {
				foreach((array)$tids as $tid) {
					$this->update_cache($tid, $data, $this->_cache_ttl);
				}
			}
			return $num;
		}
		return 0;
	}

	public function update_status_by_tid($tids, $value, $glue = '|') {
		$tids = dintval($tids, true);
		if($tids) {
			$this->clear_cache((array)$tids);
			$glue = helper_util::check_glue($glue);
			return DB::query("UPDATE %t SET status=status{$glue}%s WHERE tid IN(%n)", array($this->get_table_name(), $value, (array)$tids));
		}
		return 0;
	}

	public function update_sortid_by_sortid($sortid, $oldsortid) {
		$sortid = dintval($sortid);
		$oldsortid = dintval($oldsortid, true);
		if($oldsortid) {
			return DB::query("UPDATE %t SET sortid=%d WHERE sortid IN (%n)", array($this->get_table_name(), $sortid, $oldsortid));
		}
		return 0;
	}

	public function increase($tids, $fieldarr, $low_priority = false, $tableid = 0, $getsetarr = false) {
		$tids = dintval((array)$tids, true);
		$sql = array();
		$num = 0;
		$allowkey = array('views', 'replies', 'recommends', 'recommend_add', 'recommend_sub', 'favtimes', 'sharetimes', 'moderated', 'heats', 'lastposter', 'lastpost', 'attachment');
		foreach($fieldarr as $key => $value) {
			if(in_array($key, $allowkey)) {
				if(is_array($value)) {
					$sql[] = DB::field($key, $value[0]);
				} else {
					$value = dintval($value);
					$sql[] = "`$key`=`$key`+'$value'";
				}
			} else {
				unset($fieldarr[$key]);
			}
		}
		if($getsetarr) {
			return $sql;
		}
		if(!empty($sql)){
			$cmd = "UPDATE " . ($low_priority ? 'LOW_PRIORITY ' : '');
			$num = DB::query($cmd.DB::table($this->get_table_name($tableid))." SET ".implode(',', $sql)." WHERE tid IN (".dimplode($tids).")", 'UNBUFFERED');
			$this->increase_cache($tids, $fieldarr);
		}
		return $num;
	}

	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		if($data && is_array($data)) {
			$this->clear_cache($data['fid'], 'forumdisplay_');
			return DB::insert($this->_table, $data, $return_insert_id, $replace, $silent);
		}
		return 0;
	}

	public function insert_thread_copy_by_tid($tids, $origin = 0, $target = 0) {
		$tids = dintval($tids, true);
		if($tids) {
			$wheresql = is_array($tids) && $tids ? 'tid IN(%n)' : 'tid=%d';
			DB::query("INSERT INTO %t SELECT * FROM %t WHERE $wheresql", array($this->get_table_name($target), $this->get_table_name($origin), $tids));
		}
	}

	public function count_by_authorid($authorid, $tableid = 0) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE authorid=%d", array($this->get_table_name($tableid), $authorid));
	}

	public function count_by_fid($fid, $tableid = 0) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE fid=%d", array($this->get_table_name($tableid), $fid));
	}

	public function count_by_displayorder($displayorder) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE displayorder=%d", array($this->get_table_name(), $displayorder));
	}

	public function count_by_replies($number, $glue = '>') {
		$glue = helper_util::check_glue($glue);
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE replies{$glue}%d", array($this->get_table_name(), $number));
	}

	public function count_by_fid_typeid_displayorder($fid, $typeid = null, $displayorder = null, $glue = '=') {

		$parameter = array($this->get_table_name(), $fid);
		$wherearr = array();
		$fid = dintval($fid, true);
		$wherearr[] = is_array($fid) ? 'fid IN(%n)' : 'fid=%d';

		if($typeid) {
			$parameter[] = $typeid;
			$wherearr[] = "typeid=%d";
		}
		if($displayorder !== null) {
			$parameter[] = $displayorder;
			$glue = helper_util::check_glue($glue);
			$wherearr[] = "displayorder{$glue}%d";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first("SELECT COUNT(*) FROM %t $wheresql", $parameter);
	}
	public function count_posts_by_fid($fid, $forcetableid = null) {
		$data = array('threads' => 0, 'posts' => 0);
		loadcache('threadtableids');
		$threadtableids = array(0);
		$tableids = getglobal('cache/threadtableids');
		if(!empty($tableids)) {
			if($forcetableid === null || ($forcetableid > 0 && !in_array($forcetableid, $tableids))) {
				$threadtableids = array_merge($threadtableids, $tableids);
			} else {
				$threadtableids = array(intval($forcetableid));
			}
		}
		$threadtableids = array_unique($threadtableids);
		foreach($threadtableids as $tableid) {
			$value = DB::fetch_first('SELECT COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM %t WHERE fid=%d AND displayorder>=0', array($this->get_table_name($tableid), $fid));
			$data['threads'] += intval($value['threads']);
			$data['posts'] += intval($value['posts']);
		}
		return $data;
	}
	public function count_by_fid_displayorder_authorid($fid, $displayorder, $authorid, $tableid=0) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE fid=%d AND displayorder=%d AND authorid=%d", array($this->get_table_name($tableid), $fid, $displayorder, $authorid));
	}
	public function count_all_thread() {
		$count = 0;
		$settings = C::t('common_setting')->fetch_all('threadtableids', true);
		if(empty($settings['threadtableids']) || !is_array($settings['threadtableids'])) {
			$settings['threadtableids'] = array(0);
		}
		foreach($settings['threadtableids'] as $tableid) {
			$count += $this->count_by_tableid($tableid);
		}
		return $count;
	}
	public function count_by_posttableid_displayorder($tableid = 0, $posttableid = 0, $displayorder = 0) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE posttableid=%d AND displayorder=%d', array($this->get_table_name($tableid), $posttableid, $displayorder));
	}

	public function count_by_tableid($tableid) {
		return DB::result_first("SELECT COUNT(*) FROM %t", array($this->get_table_name($tableid)));
	}

	public function count_by_tid_fid($tids, $fids = array(), $isgroup = -1, $author = '', $subject = '') {
		$condition = $this->make_query_condition($tids, $fids, $isgroup, $author, $subject);
		return DB::result_first("SELECT COUNT(*) FROM %t $condition[0]", $condition[1]);
	}


	public function count_group_thread_by_fid($fid) {
		return DB::fetch_all('SELECT COUNT(*) AS num, authorid FROM %t WHERE fid=%d GROUP BY authorid', array($this->get_table_name(), $fid));
	}
	public function count_group_by_fid($tableid = 0) {
		return DB::fetch_all('SELECT fid, COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM %t GROUP BY fid', array($this->get_table_name($tableid)));
	}

	public function count_special_group_by_special() {
		return DB::fetch_all('SELECT special, count(*) AS spcount FROM %t GROUP BY special', array($this->get_table_name()));
	}

	public function count_by_recyclebine($fid = 0, $isgroup = 0, $author = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '') {
		$sql = $this->recyclebine_where($fid, $isgroup, $author, $username, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table('forum_thread').' t LEFT JOIN '.DB::table('forum_threadmod').' tm ON tm.tid=t.tid '.$sql[0], $sql[1]);
	}

	public function delete_by_tid($tids, $unbuffered = false, $tableid = 0, $limit = 0) {
		$tids = dintval($tids, true);
		if($tids) {
			$this->clear_cache($tids);
			C::t('forum_newthread')->delete_by_tids($tids);
			return DB::delete($this->get_table_name($tableid), DB::field('tid', $tids), $limit, $unbuffered);
		}
		return !$unbuffered ? 0 : false;
	}
	public function delete($tids, $unbuffered = false, $tableid = 0, $limit = 0) {
		return $this->delete_by_tid($tids, $unbuffered, $tableid, $limit);
	}
	public function delete_by_fid($fid, $unbuffered = false, $tableid = 0, $limit = 0) {
		$fid = dintval($fid, true);
		if($fid) {
			foreach((array)$fid as $delfid) {
				$this->clear_cache($delfid, 'forumdisplay_');
			}
			C::t('forum_newthread')->delete_by_tids($fid);
			return DB::delete($this->get_table_name($tableid), DB::field('fid', $fid), $limit, $unbuffered);
		}
		return 0;
	}
	public function get_table_name($tableid = 0){
		$tableid = intval($tableid);
		return $tableid ? "forum_thread_$tableid" : 'forum_thread';
	}
	public function fetch_all_for_guide($type, $limittid, $tids = array(), $heatslimit = 3, $dateline = 0, $start = 0, $limit = 600, $fids = 0) {
		switch ($type) {
			case 'hot' :
				$addsql = ' AND heats>='.intval($heatslimit);
				break;
			case 'digest' :
				$addsql = ' AND digest>0';
				break;
			default :
				$addsql = '';
		}
		if(getglobal('setting/followforumid')) {
			$addsql .= ' AND '.DB::field('fid', getglobal('setting/followforumid'), '<>');
		}
		$tidsql = '';
		if($tids) {
			$tids = dintval($tids, true);
			$tidsql = DB::field('tid', $tids);
		} else {
			$limittid = intval($limittid);
			$tidsql = 'tid>'.$limittid;
			$fids = dintval($fids, true);
			if($fids) {
				$tidsql .= is_array($fids) && $fids ? ' AND fid IN('.dimplode($fids).')' : ' AND fid='.$fids;
			}
			if($dateline) {
				$addsql .= ' AND dateline > '.intval($dateline);
			}
			if($type == 'newthread') {
				$orderby = 'tid';
			} elseif($type == 'reply') {
				$orderby = 'lastpost';
				$addsql .= ' AND replies > 0';
			} else {
				$orderby = 'lastpost';
			}
			$addsql .= ' AND displayorder>=0 ORDER BY '.$orderby.' DESC '.DB::limit($start, $limit);

		}
		return DB::fetch_all("SELECT * FROM ".DB::table('forum_thread')." WHERE ".$tidsql.$addsql);
	}
	public function fetch_max_tid() {
		return DB::result_first("SELECT MAX(tid) as maxtid FROM ".DB::table('forum_thread'));
	}
	public function move_thread_by_tid($tids, $source, $target) {
		$source = intval($source);
		$target = intval($target);
		if($source != $target) {
			DB::query('REPLACE INTO %t SELECT * FROM %t WHERE tid IN (%n)', array($this->get_table_name($target), $this->get_table_name($source), $tids));
			if(!$source) {
				C::t('forum_threadhidelog')->delete_by_tid($tids);
			}
			return DB::delete($this->get_table_name($source), DB::field('tid', $tids));
		} else {
			return false;
		}
	}

	function gettablestatus($tableid = 0) {
		$table_info = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', DB::table($this->get_table_name($tableid)))."'");
		$table_info['Data_length'] = $table_info['Data_length'] / 1024 / 1024;
		$nums = intval(log($table_info['Data_length']) / log(10));
		$digits = 0;
		if($nums <= 3) {
			$digits = 3 - $nums;
		}
		$table_info['Data_length'] = number_format($table_info['Data_length'], $digits).' MB';

		$table_info['Index_length'] = $table_info['Index_length'] / 1024 / 1024;
		$nums = intval(log($table_info['Index_length']) / log(10));
		$digits = 0;
		if($nums <= 3) {
			$digits = 3 - $nums;
		}
		$table_info['Index_length'] = number_format($table_info['Index_length'], $digits).' MB';
		return $table_info;
	}

	private function recyclebine_where($fid = 0, $isgroup = 0, $authors = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '') {
		$parameter = array();
		$wherearr = array('t.displayorder=-1', 'tm.action=\'DEL\'');
		if($fid) {
			$fid = dintval($fid, true);
			$parameter[] = $fid;
			$wherearr[] = is_array($fid) ? 't.fid IN(%n)' : 't.fid=%d';
		}
		if($isgroup) {
			$wherearr[] = 't.isgroup=1';
		}
		if(!empty($authors)) {
			$parameter[] = $authors;
			$wherearr[] = is_array($authors) && $authors ? 't.author IN(%n)' : 't.author=%s';
		}
		if(!empty($username)) {
			$parameter[] = $username;
			$wherearr[] = is_array($username) && $username ? 'tm.username IN(%n)' : 'tm.username=%s';
		}
		if($pstarttime) {
			$parameter[] = $pstarttime;
			$wherearr[] = 't.dateline>=%d';
		}
		if($pendtime) {
			$parameter[] = $pendtime;
			$wherearr[] = 't.dateline<%d';
		}
		if($mstarttime) {
			$parameter[] = $mstarttime;
			$wherearr[] = 'tm.dateline>=%d';
		}
		if($mendtime) {
			$parameter[] = $mendtime;
			$wherearr[] = 'tm.dateline<%d';
		}
		if($keywords) {
			$keysql = array();
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$parameter[] = '%'.$keyword.'%';
				$keysql[] = "t.subject LIKE %s";
			}
			if($keysql) {
				$wherearr[] = '('.implode(' OR ', $keysql).')';
			}
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}
	public function create_table($maxtableid) {
		if($maxtableid) {
			DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
			$db = &DB::object();
			$query = DB::query("SHOW CREATE TABLE %t", array($this->get_table_name()));
			$create = $db->fetch_row($query);
			$createsql = $create[1];
			$createsql = str_replace(DB::table($this->get_table_name()), DB::table($this->get_table_name($maxtableid)), $createsql);
			DB::query($createsql);

			return true;
		} else {
			return false;
		}
	}
	public function drop_table($tableid) {
		$tableid = intval($tableid);
		if($tableid) {
			DB::query("DROP TABLE %t", array($this->get_table_name($tableid)), true);
			return true;
		} else {
			return false;
		}
	}
	private function compare_number($firstnum, $secondnum, $glue = '>=') {
		switch($glue) {
			case '==':
			case '=':
				return $firstnum == $secondnum;
				break;
			case '>':
				return $firstnum > $secondnum;
				break;
			case '<':
				return $firstnum < $secondnum;
				break;
			case '<>':
				return $firstnum <> $secondnum;
				break;
			case '<=':
				return $firstnum <= $secondnum;
				break;
			case '>=':
				return $firstnum >= $secondnum;
				break;
		}
		return false;
	}
}

?>