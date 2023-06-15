<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_anonymouspost.php 29373 2012-04-09 07:55:30Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_anonymouspost {

	var $version = '1.0';
	var $name = 'anonymouspost_name';
	var $description = 'anonymouspost_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'anonymouspost_forum',
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
		$id = intval($_GET['id']);
		if(empty($id)) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_nonexistence'));
		}
		$idtype = !empty($_GET['idtype']) ? dhtmlspecialchars($_GET['idtype']) : '';
		if(!in_array($idtype, array('pid', 'cid'))) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_use_error'));
		}
		if($idtype == 'pid') {
			$_G['tid'] = $_GET['ptid'];
			$post = getpostinfo($id, 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid', 'p.author', 'p.dateline', 'p.anonymous'));
			$this->_check($post);

			if($post['authorid'] != $_G['uid']) {
				showmessage('magics_operation_nopermission');
			}

			$thread = getpostinfo($post['tid'], 'tid', array('tid', 'subject', 'author', 'replies', 'lastposter'));
			if($post['first']) {
				$author = '';
				$lastposter = $thread['replies'] > 0 ? $thread['lastposter'] : '';
			} else {
				$author = $thread['author'];
				$lastposter = '';
			}
			C::t('forum_post')->update('tid:'.$post['tid'], $id, array('anonymous' => 1));
			$query = C::t('forum_forum')->fetch($post['fid']);
			$forum['lastpost'] = explode("\t", $query['lastpost']);
			if($post['dateline'] == $forum['lastpost'][2] && ($post['author'] == $forum['lastpost'][3] || ($forum['lastpost'][3] == '' && $post['anonymous']))) {
				$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$lastposter";
				C::t('forum_forum')->update($post['fid'], array('lastpost' => $lastpost));
			}
			C::t('forum_thread')->update($post['tid'], array('author' => $author, 'lastposter' => $lastposter));
		} elseif($idtype == 'cid') {
			$value = C::t('home_comment')->fetch($id, intval($_G['uid']));
			if(empty($value)) {
				showmessage(lang('magic/anonymouspost', 'anonymouspost_use_error'));
			} elseif($value['author'] == '') {
				showmessage(lang('magic/anonymouspost', 'anonymouspost_once_limit'));
			}
			C::t('home_comment')->update($id, array('author'=>''), $_G['uid']);
		}

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, $idtype, $id);

		showmessage(lang('magic/anonymouspost', 'anonymouspost_succeed'), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$id = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		$idtype = !empty($_GET['idtype']) ? dhtmlspecialchars($_GET['idtype']) : '';
		if($idtype == 'pid') {
			list($id, $_G['tid']) = explode(':', $id);
			if($id && $_G['tid']) {
				$post = getpostinfo($id, 'pid', array('p.fid', 'p.authorid'));
				$this->_check($post);
			}
		}
		magicshowtype('top');
		magicshowtips(lang('magic/anonymouspost', 'anonymouspost_desc'));
		magicshowtips(lang('magic/anonymouspost', 'anonymouspost_num', array('magicnum' => $this->magic['num'])));
		magicshowsetting('', 'id', $id, 'hidden');
		magicshowsetting('', 'idtype', $idtype, 'hidden');
		if($idtype == 'pid') {
			magicshowsetting('', 'ptid', $_G['tid'], 'hidden');
		}
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		$id = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		$idtype = !empty($_GET['idtype']) ? dhtmlspecialchars($_GET['idtype']) : '';
		if(!empty($id) && $idtype == 'pid') {
			list($id, $_G['tid']) = explode(':', $id);
			$post = getpostinfo(intval($id), 'pid', array('p.fid', 'p.authorid'));
			$this->_check($post);
		}
	}

	function _check($post) {
		global $_G;
		if(!checkmagicperm($this->parameters['forum'], $post['fid'])) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_noperm'));
		}
		if($post['authorid'] != $_G['uid']) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_user_noperm'));
		}
	}

}

?>