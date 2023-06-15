<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_jack.php 34353 2014-03-19 04:57:02Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_jack {

	var $version = '1.0';
	var $name = 'jack_name';
	var $description = 'jack_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'expiration' => array(
				'title' => 'jack_expiration',
				'type' => 'text',
				'value' => '',
				'default' => 1,
			),
			'fids' => array(
				'title' => 'jack_forum',
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
			showmessage(lang('magic/jack', 'jack_info_nonexistence'));
		}

		$thread = getpostinfo($_GET['tid'], 'tid', array('fid', 'authorid', 'subject', 'lastpost'));
		$this->_check($thread['fid']);
		magicthreadmod($_GET['tid']);

		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 1;
		$magicnum = abs(intval($_GET['magicnum']));
		if(empty($magicnum) || $magicnum > $this->magic['num']) {
			showmessage(lang('magic/jack', 'jack_num_not_enough'));
		}
		$expiration = ($thread['lastpost'] > TIMESTAMP ? $thread['lastpost'] : TIMESTAMP) + $this->parameters['expiration'] * $magicnum * 3600;
		C::t('forum_thread')->update($_GET['tid'], array('lastpost' => $expiration));
		usemagic($this->magic['magicid'], $this->magic['num'], $magicnum);
		updatemagiclog($this->magic['magicid'], '2', $magicnum, '0', 0, 'tid', $_GET['tid']);

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/jack', 'jack_notification'), array('tid' => $_GET['tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}
		showmessage(lang('magic/jack', 'jack_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		if($tid) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 1;
		magicshowtype('top');
		magicshowtips(lang('magic/jack', 'jack_info', array('expiration' => $this->parameters['expiration'], 'magicnum' => $this->magic['num'])));
		magicshowsetting(lang('magic/jack', 'jack_num'), 'magicnum', '1', 'text');
		magicshowsetting('', 'tid', $tid, 'hidden');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_GET['id'])) {
			$thread = getpostinfo($_GET['id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 1;
		magicshowtips(lang('magic/jack', 'jack_info', array('expiration' => $this->parameters['expiration'])));
	}

	function _check($fid) {
		if(!checkmagicperm($this->parameters['forum'], $fid)) {
			showmessage(lang('magic/jack', 'jack_info_noperm'));
		}
	}

}

?>