<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_moderate.php 31513 2012-09-04 08:47:57Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_moderate extends discuz_table
{
	var $_tables = array();

	public function __construct() {

		$this->_table = '';
		$this->_pk    = '';
		$this->_tables = array(
			'tid' => 'forum_thread_moderate',
			'pid' => 'forum_post_moderate',
			'blogid' => 'home_blog_moderate',
			'picid' => 'home_pic_moderate',
			'doid' => 'home_doing_moderate',
			'sid' => 'home_share_moderate',
			'aid' => 'portal_article_moderate',
			'aid_cid' => 'portal_comment_moderate',
			'topicid_cid' => 'portal_comment_moderate',
			'uid_cid' => 'home_comment_moderate',
			'blogid_cid' => 'home_comment_moderate',
			'sid_cid' => 'home_comment_moderate',
			'picid_cid' => 'home_comment_moderate',
		);

		parent::__construct();
	}

	private function _get_table($idtype) {
		return $this->_tables[$idtype];
	}

	private function _is_comment_table($idtype) {
		return in_array($this->_get_table($idtype), array('portal_comment_moderate', 'home_comment_moderate'));
	}

	public function count_by_idtype($idtype, $status = 0, $dateline = 0) {
		return $this->query_data(1, $idtype, $status, $dateline);
	}

	public function fetch_all_by_idtype($idtype, $status = 0, $dateline = 0) {
		return $this->query_data(0, $idtype, $status, $dateline);
	}

	private function query_data($type, $idtype, $status = 0, $dateline = 0) {
		if(!isset($this->_tables[$idtype])) {
			return $type ? 0 : array();
		}
		$parameter = array($this->_get_table($idtype), $this->_is_comment_table($idtype) ? "idtype='$idtype' AND" : '', $status);
		$othersql = '';
		if($dateline) {
			$othersql = " AND dateline>=%d";
			$parameter[] = $dateline;
		}

		if($type) {
			return DB::result_first("SELECT COUNT(*) FROM %t WHERE %i `status`=%d $othersql ORDER BY dateline DESC", $parameter);
		} else {
			return DB::fetch_all("SELECT * FROM %t WHERE %i `status`=%d $othersql ORDER BY dateline DESC", $parameter, 'id');
		}
	}
	public function fetch_all_for_article($status, $catid = 0, $username = '', $dateline = 'all', $count = 0, $start = 0, $limit = 0) {
		$sqlwhere = '';
		$status = dintval($status);
		if(($catid = dintval($catid))) {
			$sqlwhere .= " AND a.catid='$catid'";
		}
		if(!empty($username)) {
			$sqlwhere .= " AND a.username='".addslashes($username)."'";
		}
		if($dateline != 'all') {
			$sqlwhere .= " AND a.dateline>'".(TIMESTAMP - dintval($dateline))."'";
		}
		if($count) {
			return DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('portal_article_moderate')." m
			LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=m.id
			WHERE m.status='$status' $sqlwhere");
		}
		return DB::fetch_all("SELECT a.aid, a.catid, a.uid, a.username, a.title, a.summary, a.dateline, cat.catname
			FROM ".DB::table('portal_article_moderate')." m
			LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=m.id
			LEFT JOIN ".DB::table('portal_category')." cat ON cat.catid=a.catid
			WHERE m.status='$status' $sqlwhere
			ORDER BY m.dateline DESC".DB::limit($start, $limit));
	}

	public function fetch_all_for_portalcomment($idtype, $tablename, $status, $catid = 0, $username = '', $dateline = 'all', $count = 0, $keyword = '', $start = 0, $limit = 0) {
		if(!isset($this->_tables[$idtype.'_cid'])) {
			return $count ? 0 : array();
		}
		if(!empty($catid)) {
			$sqlwhere .= " AND a.catid='$catid'";
		}
		if(!empty($username)) {
			$sqlwhere .= " AND a.username='$username'";
		}
		if($dateline != 'all') {
			$sqlwhere .= " AND a.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($keyword)) {
			$sqlwhere .= " AND c.message LIKE '%$keyword%'";
		}
		$sqlwhere .=  "AND c.idtype='$idtype'";
		if($count) {
			return DB::result_first("SELECT COUNT(*)
			FROM ".DB::table($this->_get_table($idtype.'_cid'))." m
			LEFT JOIN ".DB::table('portal_comment')." c ON c.cid=m.id
			LEFT JOIN ".DB::table($tablename)." a ON a.$idtype=c.id
			WHERE m.".DB::field('idtype', $idtype.'_cid')." AND m.status='$status' $sqlwhere");
		}
		return DB::fetch_all("SELECT c.cid, c.uid, c.username, c.id, c.postip, c.dateline, c.message, a.title
			FROM ".DB::table($this->_get_table($idtype.'_cid'))." m
			LEFT JOIN ".DB::table('portal_comment')." c ON c.cid=m.id
			LEFT JOIN ".DB::table($tablename)." a ON a.$idtype=c.id
			WHERE m.".DB::field('idtype', $idtype.'_cid')." AND m.status='$status' $sqlwhere
			ORDER BY m.dateline DESC".DB::limit($start, $limit));
	}

	public function count_group_idtype_by_status($status) {
		$return = array();
		foreach($this->_tables as $idtype => $table) {
			if($this->_is_comment_table($idtype)) {
				$return[] = array('idtype' => $idtype, 'count' => DB::result_first('SELECT COUNT(*) FROM %t WHERE idtype=%s AND status=%d', array($table, $idtype, $status)));
			} else {
				$return[] = array('idtype' => $idtype, 'count' => DB::result_first('SELECT COUNT(*) FROM %t WHERE status=%d', array($table, $status)));
			}
		}
		return $return;
	}

	public function delete($id, $idtype, $unbuffered = false) {
		if(!isset($this->_tables[$idtype])) {
			return false;
		}
		$table = $this->_get_table($idtype);
		$wheresql = array();
		$id && $wheresql[] = DB::field('id', $id);
		$this->_is_comment_table($idtype) && $wheresql[] = DB::field('idtype', $idtype);
		return DB::delete($table, implode(' AND ', $wheresql), 0, $unbuffered);
	}

	public function insert($idtype, $data, $return_insert_id = false, $replace = false, $silent = false) {
		if(!isset($this->_tables[$idtype]) || empty($data)) {
			return false;
		}
		$table = $this->_get_table($idtype);
		$this->_is_comment_table($idtype) && $data['idtype'] = $idtype;
		return DB::insert($table, $data, $return_insert_id, $replace, $silent);
	}

	public function update($id, $idtype, $data, $unbuffered = false, $low_priority = false) {
		if(!isset($this->_tables[$idtype]) || empty($data)) {
			return false;
		}
		$table = $this->_get_table($idtype);
		$wheresql = array();
		$id && $wheresql[] = DB::field('id', $id);
		$this->_is_comment_table($idtype) && $wheresql[] = DB::field('idtype', $idtype);
		return DB::update($table, $data, implode(' AND ', $wheresql), $unbuffered, $low_priority);
	}

	public function count_by_idtype_status_fid($idtype, $status, $fids) {
		if($idtype == 'tid') {
			$innertable = 'forum_thread';
		} elseif($idtype == 'pid') {
			$innertable = 'forum_post';
		} else {
			return 0;
		}
		return DB::result_first('SELECT COUNT(*) FROM %t m INNER JOIN %t t ON m.id=t.%i AND t.'.DB::field('fid', $fids).' WHERE m.status=%d',
				array($this->_get_table($idtype), $innertable, $idtype, $status));
	}

	public function count_by_search_for_post($posttable, $status = null, $first = null, $fids = null, $author = null, $dateline = null, $subject = null) {
		$wheresql = array();
		if($status !== null) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($first !== null) {
			$wheresql[] = 'p.'.DB::field('first', $first);
		}
		if($fids) {
			$wheresql[] = 'p.'.DB::field('fid', $fids);
		}
		if($author) {
			$wheresql[] = 'p.'.DB::field('author', $author);
		}
		if($dateline) {
			$wheresql[] = 'p.'.DB::field('dateline', $dateline, '>');
		}
		if($subject) {
			$wheresql[] = 'p.'.DB::field('message', '%'.$subject.'%', 'like');
		}

		return DB::result_first('SELECT COUNT(*) FROM %t m LEFT JOIN %t p ON p.pid=m.id WHERE %i',
				array($this->_get_table('pid'), $posttable, implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_post($posttable, $status = null, $first = null, $fids = null, $author = null, $dateline = null, $subject = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($first !== null) {
			$wheresql[] = 'p.'.DB::field('first', $first);
		}
		if($fids) {
			$wheresql[] = 'p.'.DB::field('fid', $fids);
		}
		if($author) {
			$wheresql[] = 'p.'.DB::field('author', $author);
		}
		if($dateline) {
			$wheresql[] = 'p.'.DB::field('dateline', $dateline, '>');
		}
		if($subject) {
			$wheresql[] = 'p.'.DB::field('message', '%'.$subject.'%', 'like');
		}
		return DB::fetch_all('SELECT p.pid, p.fid, p.tid,
			p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff, p.status
			FROM %t m
			LEFT JOIN %t p on p.pid=m.id
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('pid'), $posttable, implode(' AND ', $wheresql)));
	}

	public function count_by_seach_for_thread($status = null, $fids = null) {
		$wheresql = array();
		if($status !== null) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($fids) {
			$wheresql[] = 't.'.DB::field('fid', $fids);
		}
		return DB::result_first('SELECT COUNT(*) FROM %t m LEFT JOIN %t t ON t.tid=m.id WHERE %i',
			array($this->_get_table('tid'), 'forum_thread', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_thread($status = null, $fids = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($fids) {
			$wheresql[] = 't.'.DB::field('fid', $fids);
		}
		return DB::fetch_all('SELECT t.tid, t.fid, t.posttableid, t.author, t.sortid, t.authorid, t.subject as tsubject, t.dateline, t.attachment
			FROM %t m
			LEFT JOIN %t t ON t.tid=m.id
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('tid'), 'forum_thread', implode(' AND ', $wheresql)));
	}

	public function count_by_search_for_blog($status = null, $username = null, $dateline = null, $subject = null) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'b.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'b.'.DB::field('dateline', $dateline, '>');
		}
		if($subject) {
			$wheresql[] = 'b.'.DB::field('subject', '%'.$subject.'%', 'like');
		}
		return DB::result_first('SELECT COUNT(*)
			FROM %t m
			LEFT JOIN %t b ON b.blogid=m.id
			LEFT JOIN %t bf ON bf.blogid=b.blogid
			LEFT JOIN %t c ON b.classid=c.classid
			WHERE %i',
			array($this->_get_table('blogid'), 'home_blog', 'home_blogfield', 'home_class', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_blog($status = null, $username = null, $dateline = null, $subject = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'b.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'b.'.DB::field('dateline', $dateline, '>');
		}
		if($subject) {
			$wheresql[] = 'b.'.DB::field('subject', '%'.$subject.'%', 'like');
		}
		return DB::fetch_all('SELECT b.blogid, b.uid, b.username, b.classid, b.subject, b.dateline, bf.message, bf.postip, c.classname
			FROM %t m
			LEFT JOIN %t b ON b.blogid=m.id
			LEFT JOIN %t bf ON bf.blogid=b.blogid
			LEFT JOIN %t c ON b.classid=c.classid
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('blogid'), 'home_blog', 'home_blogfield', 'home_class', implode(' AND ', $wheresql)));
	}

	public function count_by_search_for_commnet($idtype = null, $status = null, $author = null, $dateline = null, $message = null) {
		$wheresql = array();
		if($idtype) {
			$table = $this->_get_table($idtype.'_cid');
			$wheresql[] = 'm.'.DB::field('idtype', $idtype.'_cid');
		} else {
			$table = 'home_comment_moderate';
		}
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($author) {
			$wheresql[] = 'c.'.DB::field('author', $author);
		}
		if($dateline) {
			$wheresql[] = 'c.'.DB::field('dateline', $dateline, '>');
		}
		if($message) {
			$message = str_replace(array('_', '%'), array('\_', '\%'), $message);
			$wheresql[] = 'c.'.DB::field('message', '%'.$message.'%', 'like');
		}
		return DB::result_first('SELECT COUNT(*) FROM %t m LEFT JOIN %t c ON c.cid=m.id WHERE %i',
			array($table, 'home_comment', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_comment($idtype = null, $status = null, $author = null, $dateline = null, $message = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($idtype) {
			$table = $this->_get_table($idtype.'_cid');
			$wheresql[] = 'm.'.DB::field('idtype', $idtype.'_cid');
		} else {
			$table = 'home_comment_moderate';
		}
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($author) {
			$wheresql[] = 'c.'.DB::field('author', $author);
		}
		if($dateline) {
			$wheresql[] = 'c.'.DB::field('dateline', $dateline, '>');
		}
		if($message) {
			$message = str_replace(array('_', '%'), array('\_', '\%'), $message);
			$wheresql[] = 'c.'.DB::field('message', '%'.$message.'%', 'like');
		}
		return DB::fetch_all('SELECT c.cid, c.uid, c.id, c.idtype, c.authorid, c.author, c.message, c.dateline, c.ip
			FROM %t m
			LEFT JOIN %t c ON c.cid=m.id
			WHERE %i
			ORDER BY c.dateline DESC '.DB::limit($start, $limit),
			array($table, 'home_comment', implode(' AND ', $wheresql)));
	}

	public function count_by_search_for_doing($status = null, $username = null, $dateline = null, $message = null) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'd.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'd.'.DB::field('dateline', $dateline, '>');
		}
		if($message) {
			$message = str_replace(array('_', '%'), array('\_', '\%'), $message);
			$wheresql[] = 'd.'.DB::field('message', '%'.$message.'%', 'like');
		}
		return DB::result_first('SELECT COUNT(*)
			FROM %t m
			LEFT JOIN %t d ON d.doid=m.id
			WHERE %i',
			array($this->_get_table('doid'), 'home_doing', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_doing($status = null, $username = null, $dateline = null, $message = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'd.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'd.'.DB::field('dateline', $dateline, '>');
		}
		if($message) {
			$message = str_replace(array('_', '%'), array('\_', '\%'), $message);
			$wheresql[] = 'd.'.DB::field('message', '%'.$message.'%', 'like');
		}
		return DB::fetch_all('SELECT d.doid, d.uid, d.username, d.dateline, d.message, d.ip
			FROM %t m
			LEFT JOIN %t d ON d.doid=m.id
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('doid'), 'home_doing', implode(' AND ', $wheresql)));
	}

	public function count_by_search_for_pic($status = null, $username = null, $dateline = null, $title = null) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'p.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'p.'.DB::field('dateline', $dateline, '>');
		}
		if($title) {
			$wheresql[] = 'p.'.DB::field('title', '%'.$title.'%', 'like');
		}
		return DB::result_first('SELECT COUNT(*)
			FROM %t m
			LEFT JOIN %t p ON p.picid=m.id
			WHERE %i',
			array($this->_get_table('picid'), 'home_pic', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_pic($status = null, $username = null, $dateline = null, $title = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 'p.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 'p.'.DB::field('dateline', $dateline, '>');
		}
		if($title) {
			$wheresql[] = 'p.'.DB::field('title', '%'.$title.'%', 'like');
		}
		return DB::fetch_all('SELECT p.picid, p.albumid, p.uid, p.username, p.title, p.dateline, p.filepath, p.thumb, p.remote, p.postip, a.albumname
			FROM %t m
			LEFT JOIN %t p ON p.picid=m.id
			LEFT JOIN %t a ON p.albumid=a.albumid
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('picid'), 'home_pic', 'home_album', implode(' AND ', $wheresql)));
	}

	public function delete_by_status_idtype($status, $idtype) {
		if(!isset($this->_tables[$idtype])) {
			return false;
		}
		$table = $this->_get_table($idtype);
		$idtype = $table == 'home_comment_moderate' ? DB::field('idtype', $idtype).' AND' : '';
		return DB::query('DELETE FROM %t WHERE %i status=%d', array($table, $idtype, $status));
	}

	public function count_by_search_for_share($status = null, $username = null, $dateline = null, $body_general = null) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 's.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 's.'.DB::field('dateline', $dateline, '>');
		}
		if($body_general) {
			$body_general = str_replace(array('%', '_'), array('\%', '\_'), $body_general);
			$wheresql[] = 's.'.DB::field('body_general', '%'.$body_general.'%', 'like');
		}
		return DB::result_first('SELECT COUNT(*)
			FROM %t m
			LEFT JOIN %t s ON s.sid=m.id
			WHERE %i',
			array($this->_get_table('sid'), 'home_share', implode(' AND ', $wheresql)));
	}

	public function fetch_all_by_search_for_share($status = null, $username = null, $dateline = null, $body_general = null, $start = 0, $limit = 0) {
		$wheresql = array();
		if($status !== null && !(is_array($status) && empty($status))) {
			$wheresql[] = 'm.'.DB::field('status', $status);
		}
		if($username) {
			$wheresql[] = 's.'.DB::field('username', $username);
		}
		if($dateline) {
			$wheresql[] = 's.'.DB::field('dateline', $dateline, '>');
		}
		if($body_general) {
			$wheresql[] = 's.'.DB::field('body_general', '%'.$body_general.'%', 'like');
		}
		return DB::fetch_all('SELECT s.sid, s.type, s.uid, s.username, s.dateline, s.body_general, s.itemid, s.fromuid
			FROM %t m
			LEFT JOIN %t s ON s.sid=m.id
			WHERE %i
			ORDER BY m.dateline DESC '.DB::limit($start, $limit),
			array($this->_get_table('sid'), 'home_share', implode(' AND ', $wheresql)));
	}

}

?>