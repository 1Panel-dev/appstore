<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_follow.php 34174 2013-10-28 07:18:04Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_follow extends extend_thread_base {

	public function after_newthread() {
		$tid = $this->tid;
		$pid = $this->pid;
		$uid = $this->member['uid'];
		if($this->param['displayorder'] >= 0 && helper_access::check_module('follow') && !$this->param['isanonymous']) {
			$values = array();
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feedcontent = array(
				'tid' => $tid,
				'content' => followcode($this->param['message'], $tid, $pid, 1000),
			);
			C::t('forum_threadpreview')->insert($feedcontent);
			C::t('forum_thread')->update_status_by_tid($tid, '512');
			$followfeed = array(
				'uid' => $uid,
				'username' => $this->member['username'],
				'tid' => $tid,
				'note' => '',
				'dateline' => TIMESTAMP
			);
			$values['feedid'] = C::t('home_follow_feed')->insert($followfeed, true);
			C::t('common_member_count')->increase($uid, array('feeds'=>1));

			$this->param['values'] = array_merge((array)$this->param['values'], $values);
		}

	}

	public function after_newreply() {
		$feedid = 0;
		if(helper_access::check_module('follow') && !$this->param['isanonymous']) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feedcontent = C::t('forum_threadpreview')->count_by_tid($this->thread['tid']);
			$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($this->thread['tid']);

			if(empty($feedcontent)) {
				$feedcontent = array(
					'tid' => $this->thread['tid'],
					'content' => followcode($firstpost['message'], $this->thread['tid'], $this->pid, 1000),
				);
				C::t('forum_threadpreview')->insert($feedcontent);
				C::t('forum_thread')->update_status_by_tid($this->thread['tid'], '512');
			} else {
				C::t('forum_threadpreview')->update_relay_by_tid($this->thread['tid'], 1);
			}
			$notemsg = cutstr(followcode($this->param['message'], $this->thread['tid'], $this->pid, 0, false), 140);
			$followfeed = array(
				'uid' => $this->member['uid'],
				'username' => $this->member['username'],
				'tid' => $this->thread['tid'],
				'note' => $notemsg,
				'dateline' => TIMESTAMP
			);
			$feedid = C::t('home_follow_feed')->insert($followfeed, true);
			C::t('common_member_count')->increase($this->member['uid'], array('feeds'=>1));
		}
		if($feedid) {
			$this->param['showmsgparam'] = array_merge((array)$this->param['showmsgparam'], array('feedid' => $feedid));
		}
	}

	public function after_editpost() {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feed = C::t('forum_threadpreview')->fetch($this->thread['tid']);
			if($feed) {
				C::t('forum_threadpreview')->update($this->thread['tid'], array('content' => followcode($this->param['message'], $this->thread['tid'], $this->post['pid'], 1000)));
			}
		}
	}
}

?>