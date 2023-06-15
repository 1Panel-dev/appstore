<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_highlight.php 33516 2013-06-27 08:58:10Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_highlight {

	var $version = '1.0';
	var $name = 'highlight_name';
	var $description = 'highlight_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();
	var $idtypearray = array('blogid', 'tid');

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'expiration' => array(
				'title' => 'highlight_expiration',
				'type' => 'text',
				'value' => '',
				'default' => 24,
			),
			'fids' => array(
				'title' => 'highlight_forum',
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
		$idtype = !empty($_GET['idtype']) ? $_GET['idtype'] : '';
		if(!in_array($idtype, $this->idtypearray)) {
			showmessage(lang('magic/highlight', 'highlight_info_notype'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
		}
		if(empty($_GET['id'])) {
			showmessage(lang('magic/highlight', 'highlight_info_nonexistence_'.$idtype));
		}

		if($idtype == 'tid') {
			$info = getpostinfo($_GET['id'], $idtype, array('fid', 'authorid', 'subject'));
			$this->_check($info['fid']);
			magicthreadmod($_GET['id']);
			C::t('forum_thread')->update($_GET['id'], array('highlight' => $_GET['highlight_color'], 'moderated' => 1));
			$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
			$expiration = TIMESTAMP + $this->parameters['expiration'] * 3600;
			updatemagicthreadlog($_GET['id'], $this->magic['magicid'], $expiration > 0 ? 'EHL' : 'HLT', $expiration);
			if($info['authorid'] != $_G['uid']) {
				notification_add($info['authorid'], 'magic', lang('magic/highlight', 'highlight_notification'), array('tid' => $_GET['id'], 'subject' => $info['subject'], 'magicname' => $this->magic['name']));
			}
		} elseif($idtype == 'blogid') {
			$info = getpostinfo($_GET['id'], $idtype, array('uid', 'subject'));
			C::t('home_blogfield')->update($_GET['id'], array('magiccolor' => $_GET['highlight_color']));
			if($info['uid'] != $_G['uid']) {
				notification_add($info['uid'], 'magic', lang('magic/highlight', 'highlight_notification_blogid'), array('blogid' => $_GET['id'], 'subject' => $info['subject'], 'magicname' => $this->magic['name']));
			}
		}

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, $idtype, $_GET['id']);

		showmessage(lang('magic/highlight', 'highlight_succeed_'.$idtype), dreferer(), array(), array('alert' => 'right', 'showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$id = !empty($_GET['id']) ? dhtmlspecialchars($_GET['id']) : '';
		$idtype = !empty($_GET['idtype']) ? $_GET['idtype'] : '';
		if(!in_array($idtype, $this->idtypearray)) {
			showmessage(lang('magic/highlight', 'highlight_info_notype'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
		}
		if($id) {
			$info = getpostinfo($_GET['id'], $idtype);
			if($idtype == 'tid') {
				$this->_check($info['fid']);
				$this->parameters['expiration'] = $this->parameters['expiration'] && $idtype == 'tid' ? intval($this->parameters['expiration']) : 24;
			}
		}
		magicshowtype('top');
		$lang = lang('magic/highlight');
		magicshowsetting(lang('magic/highlight', 'highlight_info_'.$idtype, array('expiration' => $this->parameters['expiration'])), 'id', $id, 'hidden');
echo <<<EOF
	<p class="mtm mbn">$lang[highlight_color]</p>
	<div class="hasd mbm cl">
		<input type="hidden" id="highlight_color" name="highlight_color" />
		<input type="hidden" id="highlight_idtype" name="idtype" value="$idtype"/>
		<input type="text" id="highlight_color_show" class="crl readonly="readonly"" />
		<a href="javascript:;" id="highlight_color_ctrl" class="dpbtn" onclick="showHighLightColor('highlight_color')">^</a>
	</div>
	<script type="text/javascript" reload="1">
		function showHighLightColor(hlid) {
			var showid = hlid + '_show';
			if(!$(showid + '_menu')) {
				var str = '';
				var coloroptions = {'0' : '#000', '1' : '#EE1B2E', '2' : '#EE5023', '3' : '#996600', '4' : '#3C9D40', '5' : '#2897C5', '6' : '#2B65B7', '7' : '#8F2A90', '8' : '#EC1282'};
				var menu = document.createElement('div');
				menu.id = showid + '_menu';
				menu.className = 'cmen';
				menu.style.display = 'none';
				for(var i in coloroptions) {
					str += '<a href="javascript:;" onclick="$(\'' + hlid + '\').value=' + i + ';$(\'' + showid + '\').style.backgroundColor=\'' + coloroptions[i] + '\';hideMenu(\'' + menu.id + '\')" style="background:' + coloroptions[i] + ';color:' + coloroptions[i] + ';">' + coloroptions[i] + '</a>';
				}
				menu.innerHTML = str;
				$('append_parent').appendChild(menu);
			}
			showMenu({'ctrlid':hlid + '_ctrl','evt':'click','showid':showid});
		}
	</script>
EOF;
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		$idtype = !empty($_GET['idtype']) ? $_GET['idtype'] : '';
		if(!empty($_GET['id'])) {
			$info = getpostinfo($_GET['id'], $idtype);
			if($idtype == 'tid') {
				$this->_check($info['fid']);
			}
		}
	}

	function _check($fid) {
		if(!checkmagicperm($this->parameters['forum'], $fid)) {
			showmessage(lang('magic/highlight', 'highlight_info_noperm'));
		}
	}

}

?>