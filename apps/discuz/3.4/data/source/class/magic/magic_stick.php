<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_stick.php 29373 2012-04-09 07:55:30Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_stick {

	var $version = '1.0';
	var $name = 'stick_name';
	var $description = 'stick_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'expiration' => array(
				'title' => 'stick_expiration',
				'type' => 'text',
				'value' => '',
				'default' => 24,
			),
			'fids' => array(
				'title' => 'stick_forum',
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
		$magicnew['expiration'] = intval($parameters['expiration']);
	}

	function usesubmit() {
		global $_G;
		if(empty($_GET['tid'])) {
			showmessage(lang('magic/stick', 'stick_info_nonexistence'));
		}

		$thread = getpostinfo($_GET['tid'], 'tid', array('fid', 'authorid', 'subject'));
		$this->_check($thread['fid']);
		magicthreadmod($_GET['tid']);

		C::t('forum_thread')->update($_GET['tid'], array('displayorder' => 1, 'moderated' => 1));
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		$expiration = TIMESTAMP + $this->parameters['expiration'] * 3600;

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_GET['tid']);
		updatemagicthreadlog($_GET['tid'], $this->magic['magicid'], $expiration > 0 ? 'EST' : 'STK', $expiration);

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/stick', 'stick_notification'), array('tid' => $_GET['tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/stick', 'stick_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		if($tid) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		magicshowtype('top');
		magicshowsetting(lang('magic/stick', 'stick_info', array('expiration' => $this->parameters['expiration'])), 'tid', $tid, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_GET['id'])) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
	}

	function _check($fid) {
		if(!checkmagicperm($this->parameters['forum'], $fid)) {
			showmessage(lang('magic/stick', 'stick_info_noperm'));
		}
	}

}

?>