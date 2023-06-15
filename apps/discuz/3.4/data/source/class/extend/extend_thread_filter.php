<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_filter.php 33048 2013-04-12 08:50:27Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_filter extends extend_thread_base {
	private $curFilterCheck = -1;

	private function _check_post_length($message, $length) {
		if($this->param['special'] || $this->thread['special'] || getstatus($this->thread['status'], 3) || !$length) {
			return 0;
		}
		require_once libfile('function/discuzcode');
		$langthread = lang('forum/thread');
		$content = discuzcode($message);
		$content = strip_tags($content);
		$content = str_replace(array(',', '.', '?', '!', $langthread['t_question'], $langthread['t_exclamatory'], $langthread['t_period'], $langthread['t_comma'], '~', $langthread['t_suspension']), '', $content);
		$content = preg_replace('/\s+/', '', $content);
		$realLength = dstrlen($content);

		$checkQuote = (preg_match("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", $message) > 0) || (preg_match("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/i", $message) > 0) || (preg_match("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/i", $message) > 0) || (preg_match("/\[attach\](\d+)\[\/attach\]/i", $message) > 0);
		if($checkQuote || $realLength >= $length) {
			return ($realLength <= 0 ? 1 : $realLength) ;
		} else {
			return 0;
		}
	}

	public function before_newreply($parameters) {
		$this->curFilterCheck = $this->_check_post_length($parameters['noticetrimstr'].$parameters['message'], $this->setting['threadfilternum']);
		if($this->curFilterCheck <= 0) {
			$this->param['modstatus'][11] = 1;
		}
	}

	public function after_newreply() {
		if($this->curFilterCheck > 0) {
			$data = array(
				'tid' => $this->thread['tid'],
				'pid' => $this->pid,
				'postlength' => $this->curFilterCheck
			);
			C::t('forum_filter_post')->insert($data);
		}
	}

	public function before_editpost($parameters) {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if(!$isfirstpost) {
			$this->curFilterCheck = $this->_check_post_length($parameters['message'], $this->setting['threadfilternum']);
			if($this->curFilterCheck <= 0) {
				$this->param['modstatus'][11] = 1;
			} else {
				$this->param['modstatus'][11] = 0;
			}
		}
	}

	public function after_editpost() {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if(!$isfirstpost) {
			if($this->curFilterCheck > 0) {
				$data = array(
					'tid' => $this->thread['tid'],
					'pid' => $this->post['pid'],
					'postlength' => $this->curFilterCheck
				);
				C::t('forum_filter_post')->insert($data, false, true);
			} else {
				C::t('forum_filter_post')->delete_by_tid_pid($this->thread['tid'], $this->post['pid']);
			}
		}
	}

	public function after_deletepost() {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			C::t('forum_filter_post')->delete_by_tid($this->thread['tid']);
			C::t('forum_hotreply_number')->delete_by_tid($this->thread['tid']);
			C::t('forum_hotreply_member')->delete_by_tid($this->thread['tid']);
		} else {
			C::t('forum_filter_post')->delete_by_tid_pid($this->thread['tid'], $this->post['pid']);
			C::t('forum_hotreply_number')->delete_by_pid($this->post['pid']);
			C::t('forum_hotreply_member')->delete_by_pid($this->post['pid']);
		}
	}
}

?>