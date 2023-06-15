<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_image.php 32709 2013-03-04 03:28:55Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class extend_thread_image extends extend_thread_base {

	public function after_newthread() {
		$threadimageaid = 0;
		$threadimage = array();
		$tid = $this->tid;
		$pid = $this->pid;
		$fid = $this->forum['fid'];
		if($this->param['special'] == 4 && $_GET['activityaid']) {
			$threadimageaid = $_GET['activityaid'];
			convertunusedattach($_GET['activityaid'], $tid, $pid);
		}
		$this->mobile_upload();
		if(($this->group['allowpostattach'] || $this->group['allowpostimage']) && ($_GET['attachnew'] || $this->param['sortid'] || !empty($_GET['activityaid']))) {
			updateattach($this->param['displayorder'] == -4 || $this->param['modnewthreads'], $tid, $pid, $_GET['attachnew']);
			if(!$threadimageaid) {
				$threadimage = C::t('forum_attachment_n')->fetch_max_image('tid:'.$tid, 'tid', $tid);
				$threadimageaid = $threadimage['aid'];
			}
		}

		$values = array('fid' => $fid, 'tid' => $tid, 'pid' => $pid, 'coverimg' => '');
		$param = array();
		if($this->forum['picstyle']) {
			setthreadcover($pid, 0, $threadimageaid);
		}

		if($threadimageaid) {
			if(!$threadimage) {
				$threadimage = C::t('forum_attachment_n')->fetch('tid:'.$tid, $threadimageaid);
			}
			$threadimage = daddslashes($threadimage);
			C::t('forum_threadimage')->insert(array(
				'tid' => $tid,
				'attachment' => $threadimage['attachment'],
				'remote' => $threadimage['remote'],
			));
		}

		$this->param['values'] = array_merge((array)$this->param['values'], $values);
		$this->param['param'] = array_merge((array)$this->param['param'], $param);
	}
	private function mobile_upload() {
		global $_G;
		if($_GET['mobile'] == 'yes' && !empty($_FILES['Filedata'])) {
			$forumattachextensions = '';
			if($_G['forum']) {
				$forum = $_G['forum'];
				if($forum['status'] == 3 && $forum['level']) {
					$levelinfo = C::t('forum_grouplevel')->fetch($forum['level']);
					if($postpolicy = $levelinfo['postpolicy']) {
						$postpolicy = dunserialize($postpolicy);
						$forumattachextensions = $postpolicy['attachextensions'];
					}
				} else {
					$forumattachextensions = $forum['attachextensions'];
				}
				if($forumattachextensions) {
					$_G['group']['attachextensions'] = $forumattachextensions;
				}
			}
			$upload = new forum_upload(1);
			if($upload) {
				$_GET['attachnew'][$upload->getaid] = array('description' => '');
			}
		}
	}
	public function after_newreply() {
		$this->mobile_upload();
		($this->group['allowpostattach'] || $this->group['allowpostimage']) && ($_GET['attachnew'] || $this->param['special'] == 2 && $_GET['tradeaid']) && updateattach($this->thread['displayorder'] == -4 || $this->param['modnewreplies'], $this->thread['tid'], $this->pid, $_GET['attachnew']);
	}

	public function before_editpost($parameters) {
		global $_G;
		$isfirstpost = $this->post['first'] ? 1 : 0;
		$attachupdate = !empty($_GET['delattachop']) || ($this->group['allowpostattach'] || $this->group['allowpostimage']) && ($_GET['attachnew'] || $parameters['special'] == 2 && $_GET['tradeaid'] || $parameters['special'] == 4 && $_GET['activityaid'] || $isfirstpost && $parameters['sortid']);

		if($attachupdate) {
			updateattach($this->thread['displayorder'] == -4 || $_G['forum_auditstatuson'], $this->thread['tid'], $this->post['pid'], $_GET['attachnew'], $_GET['attachupdate'], $this->post['authorid']);
		}


		if($isfirstpost && $attachupdate) {
			if(!$this->param['threadimageaid']) {
				$this->param['threadimage'] = C::t('forum_attachment_n')->fetch_max_image('tid:'.$this->thread['tid'], 'pid', $this->post['pid']);
				$this->param['threadimageaid'] = $this->param['threadimage']['aid'];
			}

			if($this->forum['picstyle']) {
				if(empty($this->thread['cover'])) {
					setthreadcover($this->post['pid'], 0, $this->param['threadimageaid']);
				} else {
					setthreadcover($this->post['pid'], $this->thread['tid'], 0, 1);
				}
			}

			if($this->param['threadimageaid']) {
				if(!$this->param['threadimage']) {
					$this->param['threadimage'] = C::t('forum_attachment_n')->fetch_max_image('tid:'.$this->thread['tid'], 'tid', $this->thread['tid']);
				}
				C::t('forum_threadimage')->delete_by_tid($this->thread['tid']);
				C::t('forum_threadimage')->insert(array(
					'tid' => $this->thread['tid'],
					'attachment' => $this->param['threadimage']['attachment'],
					'remote' => $this->param['threadimage']['remote'],
				));
			}
		}
	}

	public function before_deletepost($parameters) {
		$thread_attachment = $post_attachment = 0;
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$this->thread['tid'], 'tid', $this->thread['tid']) as $attach) {
			if($attach['pid'] == $this->post['pid']) {
				if($this->thread['displayorder'] >= 0) {
					$post_attachment++;
				}
				dunlink($attach);
			} else {
				if($attach['isimage']) {
					$thread_attachment = 2;
				}
				if($thread_attachment == 0) {
					$thread_attachment = 1;
				}
			}
		}

		$this->param['updatefieldarr']['attachment'] = array($thread_attachment);

		if($post_attachment) {
			C::t('forum_attachment')->delete_by_id('pid', $this->post['pid']);
			DB::query("DELETE FROM ".DB::table(getattachtablebytid($this->thread['tid']))." WHERE pid='".$this->post['pid']."'", 'UNBUFFEREED');
			updatecreditbyaction('postattach', $this->post['authorid'], array(),  '', -$post_attachment);
		}
	}
}

?>