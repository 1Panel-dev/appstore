<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_forum.php 29623 2012-04-23 06:54:18Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_forum extends discuz_block {
	var $setting = array();
	function block_forum() {
		$this->setting = array(
			'fids'	=> array(
				'title' => 'forumlist_fids',
				'type' => 'text',
			),
			'fups'	=> array(
				'title' => 'forumlist_fups',
				'type' => 'mselect',
				'value' => array()
			),
			'titlelength' => array(
				'title' => 'forumlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'forumlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'orderby' => array(
				'title' => 'forumlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('displayorder', 'forumlist_orderby_displayorder'),
					array('threads', 'forumlist_orderby_threads'),
					array('todayposts', 'forumlist_orderby_todayposts'),
					array('posts', 'forumlist_orderby_posts')
				),
				'default' => 'displayorder'
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_forum_script_forum');
	}

	function blockclass() {
		return array('forum', lang('blockclass', 'blockclass_forum_forum'));
	}

	function fields() {
		return array(
					'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
					'url' => array('name' => lang('blockclass', 'blockclass_forum_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_forum_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'summary' => array('name' => lang('blockclass', 'blockclass_forum_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'icon' => array('name' => lang('blockclass', 'blockclass_forum_field_icon'), 'formtype' => 'text', 'datatype' => 'string'),
					'posts' => array('name'=>lang('blockclass', 'blockclass_forum_field_posts'), 'formtype' => 'text', 'datatype'=>'int'),
					'threads' => array('name'=>lang('blockclass', 'blockclass_forum_field_threads'), 'formtype' => 'text', 'datatype'=>'int'),
					'todayposts' => array('name'=>lang('blockclass', 'blockclass_forum_field_todayposts'), 'formtype' => 'text', 'datatype'=>'int'),
				);
	}

	function fieldsconvert() {
		return array(
				'group_group' => array(
					'name' => lang('blockclass', 'blockclass_group_group'),
					'script' => 'group',
					'searchkeys' => array(),
					'replacekeys' => array(),
				),
				'portal_category' => array(
					'name' => lang('blockclass', 'blockclass_portal_category'),
					'script' => 'portalcategory',
					'searchkeys' => array('threads'),
					'replacekeys' => array('articles'),
				),
			);
	}

	function getsetting() {
		global $_G;

		$settings = $this->setting;
		loadcache('forums');
		$settings['fups']['value'][] = array(0, lang('portalcp', 'block_all_forum'));
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fups']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$fids	= !empty($parameter['fids']) ? explode(',',$parameter['fids']) : array();
		$fups		= isset($parameter['fups']) && !in_array(0, (array)$parameter['fups']) ? $parameter['fups'] : '';
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('displayorder','threads','posts', 'todayposts')) ? $parameter['orderby'] : 'displayorder') : 'displayorder';
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
		$sqlban = !empty($bannedids) ? ' AND f.fid NOT IN ('.dimplode($bannedids).')' : '';

		if(empty($fups)) {
			loadcache('forums');
			if(empty($_G['cache']['forums'])) {
				$fups = array('0');
			} else {
				$fups = array_keys($_G['cache']['forums']);
			}
		}

		$wheres = array();
		if($fids) {
			$wheres[] = 'f.`fid` IN ('.dimplode($fids).')';
		}
		if($fups) {
			$wheres[] = 'f.`fup` IN ('.dimplode($fups).')';
		}
		$wheres[] = "f.`status`='1'";
		$wheres[] = "f.`type`!='group'";
		$wheresql = implode(' AND ', $wheres);

		$ffadd1 = ", ff.icon, ff.description";
		$ffadd2 = "LEFT JOIN `".DB::table('forum_forumfield')."` ff ON f.`fid`=ff.`fid`";
		$query = DB::query("SELECT f.* $ffadd1
			FROM `".DB::table('forum_forum')."` f $ffadd2
			WHERE $wheresql
			$sqlban
			ORDER BY ".($orderby == 'displayorder' ? "f.fup, f.`displayorder` ASC " : "f.`$orderby` DESC")
			." LIMIT $startrow, $items"
		);
		$datalist = $list = array();
		$attachurl = preg_match('/^(http|ftp|ftps|https):\/\//', $_G['setting']['attachurl']) ? $_G['setting']['attachurl'] : $_G['siteurl'].$_G['setting']['attachurl'];
		while($data = DB::fetch($query)) {
			if(!empty($data['icon'])) {
				$data['icon'] = preg_match('/^(http|ftp|ftps|https):\/\//', $data['icon']) ? $data['icon'] : $attachurl.'common/'.$data['icon'];
			} else {
				$data['icon'] = 'static/image/common/forum_new.gif';
			}
			$list[] = array(
				'id' => $data['fid'],
				'idtype' => 'fid',
				'title' => cutstr($data['name'], $titlelength, ''),
				'url' => 'forum.php?mod=forumdisplay&fid='.$data['fid'],
				'pic' => '',
				'summary' => cutstr($data['description'], $summarylength, ''),
				'fields' => array(
					'fulltitle' => $data['name'],
					'icon' => $data['icon'],
					'threads' => intval($data['threads']),
					'posts' => intval($data['posts']),
					'todayposts' => intval($data['todayposts'])
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>