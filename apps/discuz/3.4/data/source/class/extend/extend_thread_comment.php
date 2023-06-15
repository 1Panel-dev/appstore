<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_comment.php 33709 2013-08-06 09:06:56Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_comment extends extend_thread_base {

	private $postcomment;

	public function before_newreply($parameters) {
		global $nauthorid;
		list(, $this->param['modnewreplies']) = threadmodstatus($this->param['subject']."\t".$this->param['message'].$this->param['extramessage']);
		if($this->thread['displayorder'] == -4) {
			$this->param['modnewreplies'] = 0;
		}
		$pinvisible = $parameters['modnewreplies'] ? -2 : ($this->thread['displayorder'] == -4 ? -3 : 0);
		$this->postcomment = in_array(2, $this->setting['allowpostcomment']) && $this->group['allowcommentreply'] && !$pinvisible && !empty($_GET['reppid']) && ($nauthorid != $this->member['uid'] || $this->setting['commentpostself']) ? messagecutstr($parameters['message'], 200, ' ') : '';
	}

	public function after_newreply() {
		if(!empty($_GET['noticeauthor']) && !$this->param['isanonymous'] && !$this->param['modnewreplies']) {
			if($this->postcomment) {
				$rpid = intval($_GET['reppid']);
				if($rpost = C::t('forum_post')->fetch('tid:'.$this->thread['tid'], $rpid)) {
					if(!$rpost['first']) {
						$cid = C::t('forum_postcomment')->insert(array(
							'tid' => $this->thread['tid'],
							'pid' => $rpid,
							'rpid' => $this->pid,
							'author' => $this->member['username'],
							'authorid' => $this->member['uid'],
							'dateline' => TIMESTAMP,
							'comment' => $this->postcomment,
							'score' => 0,
							'useip' => getglobal('clientip'),
							'port'=>getglobal('remoteport')
						), true);

						C::t('forum_post')->update('tid:'.$this->thread['tid'], $rpid, array('comment' => 1));
						C::t('forum_postcache')->delete($rpid);
					}
				}
				unset($this->postcomment);
			}
		}
	}

	public function after_deletepost() {
		C::t('forum_postcomment')->delete_by_rpid($this->post['pid']);
	}
}

?>