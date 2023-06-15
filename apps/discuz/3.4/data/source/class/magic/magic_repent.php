<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_repent.php 29373 2012-04-09 07:55:30Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_repent {

	var $version = '1.0';
	var $name = 'repent_name';
	var $description = 'repent_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'repent_forum',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		$magic['fids'] = explode("\t", $magic['forum']);

		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		global $_G;
		$magicnew['forum'] = is_array($parameters['fids']) && !empty($parameters['fids']) ? implode("\t",$parameters['fids']) : '';
	}

	function usesubmit() {
		global $_G;
		if(empty($_GET['pid'])) {
			showmessage(lang('magic/repent', 'repent_info_nonexistence'));
		}
		$_G['tid'] = $_GET['ptid'];

		$post = getpostinfo($_GET['pid'], 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid', 'p.replycredit', 't.status as thread_status'));
		$this->_check($post);

		require_once libfile('function/post');
		require_once libfile('function/delete');
		if($post['first']) {
			if($have_replycredit = C::t('forum_replycredit')->fetch($post['tid'])) {
				$thread = C::t('forum_thread')->fetch($post['tid']);
				if($thread['replycredit']) {
					updatemembercount($post['authorid'], array($_G['setting']['creditstransextra'][10] => -$thread['replycredit']));
				}
				C::t('forum_replycredit')->delete($post['tid']);
				C::t('common_credit_log')->delete_by_operation_relatedid(array('RCT', 'RCA', 'RCB'), $post['tid']);
			}

			deletethread(array($post['tid']));
			updateforumcount($post['fid']);
		} else {
			if($post['replycredit'] > 0) {
				updatemembercount($post['authorid'], array($_G['setting']['creditstransextra'][10] => -$post['replycredit']));
				C::t('common_credit_log')->delete_by_uid_operation_relatedid($post['authorid'], 'RCA', $post['tid']);
			}
			deletepost(array($_GET['pid']));
			updatethreadcount($post['tid']);
		}

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['tid']);

		showmessage(lang('magic/repent', 'repent_succeed'), $post['first'] ? 'forum.php?mod=forumdisplay&fid='.$post['fid'] : dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$pid = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		list($pid, $_G['tid']) = explode(':', $pid);
		if($_G['tid']) {
			$post = getpostinfo($_GET['id'], 'pid', array('p.fid', 'p.authorid', 't.status as thread_status'));
			$this->_check($post);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/repent', 'repent_info'), 'pid', $pid, 'text');
		magicshowsetting('', 'ptid', $_G['tid'], 'hidden');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_GET['id'])) {
			list($_GET['id'], $_G['tid']) = explode(':', $_GET['id']);
			$post = getpostinfo($_GET['id'], 'pid', array('p.fid', 'p.authorid'));
			$this->_check($post);
		}
	}

	function _check($post) {
		global $_G;
		if(!checkmagicperm($this->parameters['forum'], $post['fid'])) {
			showmessage(lang('magic/repent', 'repent_info_noperm'));
		}
		if($post['authorid'] != $_G['uid']) {
			showmessage(lang('magic/repent', 'repent_info_user_noperm'));
		}
		if(getstatus($post['status'], 3)) {
			showmessage(lang('magic/repent', 'repent_do_not_rushreply'));
		}
	}

}

?>