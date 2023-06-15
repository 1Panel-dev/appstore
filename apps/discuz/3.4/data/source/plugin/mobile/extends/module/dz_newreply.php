<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: dz_newreply.php 33590 2013-07-12 06:39:08Z andyzheng $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class dz_newreply extends extends_data {
	function __construct() {
		parent::__construct();
	}

	function common() {
		global $_G;
		$this->page = intval($_GET['page']) ? intval($_GET['page']) : 1;
		$start = ($this->page - 1)*$this->perpage;
		$num = $this->perpage;
		loadcache('forum_guide');
		$dateline = 0;
		$maxnum = 50000;
		$_G['setting']['guide'] = unserialize($_G['setting']['guide']);
		if($_G['setting']['guide']['newdt']) {
			$dateline = time() - intval($_G['setting']['guide']['newdt']);
		}
		$maxtid = C::t('forum_thread')->fetch_max_tid();
		$limittid = max(0,($maxtid - $maxnum));
		$tids = array_slice($_G['cache']['forum_guide']['new']['data'], $start ,$num);
		$query = C::t('forum_thread')->fetch_all_for_guide('new', $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline);

		$fids = array();
		loadcache('forums');
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && $forum['status'] > 0 && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				$fids[] = $fid;
			}
		}
		$list = array();
		$n = 0;
		foreach($query as $thread) {
			if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
				continue;
			}
			if($thread['displayorder'] < 0) {
				continue;
			}
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