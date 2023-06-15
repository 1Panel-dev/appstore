<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: dz_newthread.php 33590 2013-07-12 06:39:08Z andyzheng $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class dz_newthread extends extends_data {
	function __construct() {
		parent::__construct();
	}

	function common() {
		global $_G;

		loadcache('mobile_pnewthread');
		loadcache('forums');

		$maxnum = 50000;
		$maxtid = C::t('forum_thread')->fetch_max_tid();
		$limittid = max(0,($maxtid - $maxnum));

		$this->page = intval($_GET['page']) ? intval($_GET['page']) : 1;
		$start = ($this->page - 1)*$this->perpage;
		$num = $this->perpage;

		if($_G['cache']['mobile_pnewthread'] && (TIMESTAMP - $_G['cache']['mobile_pnewthread']['cachetime']) < 900) {
			$tids = array_slice($_G['cache']['mobile_pnewthread']['data'], $start ,$num);
			if(empty($tids)) {
				return;
			}
		} else {
			$tids = array();
		}

		$tsql = $addsql = '';
		$updatecache = false;
		$fids = array();
		if($_G['setting']['followforumid']) {
			$addsql .= ' AND '.DB::field('fid', $_G['setting']['followforumid'], '<>');
		}
		if($tids) {
			$tids = dintval($tids, true);
			$tidsql = DB::field('tid', $tids);
		} else {
			$tidsql = 'tid>'.intval($limittid);
			$addsql .= ' AND displayorder>=0 ORDER BY tid DESC LIMIT 600';
			$tids = array();
			foreach($_G['cache']['forums'] as $fid => $forum) {
				if($forum['type'] != 'group' && $forum['status'] > 0 && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
					$fids[] = $fid;
				}
			}
			if(empty($fids)) {
				return ;
			}
			$updatecache = true;
		}

		$list = $threadids = array();
		$n = 0;
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE ".$tidsql.$addsql);
		while($thread = DB::fetch($query)) {
			if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
				continue;
			}
			if($thread['displayorder'] < 0) {
				continue;
			}
			$threadids[] = $thread['tid'];
			if($tids || ($n >= $start && $n < ($start + $num))) {
				$list[$thread['tid']] = $thread;
			}
			$n ++;
		}
		$threadlist = array();
		if($tids) {
			foreach($tids as $key => $tid) {
				if($list[$tid]) {
					$threadlist[$key] = $list[$tid];
				}
			}
		} else {
			$threadlist = $list;
		}
		unset($list);

		if($updatecache) {
			$data = array('cachetime' => TIMESTAMP, 'data' => $threadids);
			$_G['cache']['mobile_pnewthread'] = $data;
			savecache('mobile_pnewthread', $_G['cache']['mobile_pnewthread']);
		}

		foreach($threadlist as $thread) {
			$this->field('author', '0', $thread['author']);
			$this->field('dateline', '0', $thread['dateline']);
			$this->field('replies', '1', $thread['replies']);
			$this->field('views', '2', $thread['views']);
			$this->id = $thread['tid'];
			$this->title = $thread['subject'];
			$this->image = '';
			$this->icon = '1';
			$this->poptype = '0';
			$this->popvalue = '';
			$this->clicktype = 'tid';
			$this->clickvalue = $thread['tid'];

			$this->insertrow();

		}
	}

}
?>