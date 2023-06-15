<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_group.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_group extends discuz_block {
	var $setting = array();
	function block_group(){
		$this->setting = array(
			'gtids' => array(
				'title' => 'grouplist_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'fids' => array(
				'title' => 'grouplist_fids',
				'type' => 'text'
			),
			'titlelength' => array(
				'title' => 'grouplist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'grouplist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'orderby' => array(
				'title' => 'grouplist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('displayorder', 'grouplist_orderby_displayorder'),
					array('threads', 'grouplist_orderby_threads'),
					array('posts', 'grouplist_orderby_posts'),
					array('todayposts', 'grouplist_orderby_todayposts'),
					array('membernum', 'grouplist_orderby_membernum'),
					array('dateline', 'grouplist_orderby_dateline'),
					array('level', 'grouplist_orderby_level'),
					array('commoncredits', 'grouplist_orderby_commoncredits'),
					array('activity', 'grouplist_orderby_activity')
				),
				'default' => 'displayorder'
			)
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['gtids']) {
			loadcache('grouptype');
			$settings['gtids']['value'][] = array(0, lang('portalcp', 'block_all_type'));
			foreach($_G['cache']['grouptype']['first'] as $gid=>$group) {
				$settings['gtids']['value'][] = array($gid, $group['name']);
				if($group['secondlist']) {
					foreach($group['secondlist'] as $subgid) {
						$settings['gtids']['value'][] = array($subgid, '&nbsp;&nbsp;'.$_G['cache']['grouptype']['second'][$subgid]['name']);
					}
				}
			}
		}
		return $settings;
	}

	function name() {
		return lang('blockclass', 'blockclass_group_script_group');
	}

	function blockclass() {
		return array('group', lang('blockclass', 'blockclass_group_group'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_group_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_group_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_group_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('blockclass', 'blockclass_group_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'icon' => array('name' => lang('blockclass', 'blockclass_group_field_icon'), 'formtype' => 'text', 'datatype' => 'string'),
				'foundername' => array('name' => lang('blockclass', 'blockclass_group_field_foundername'), 'formtype' => 'text', 'datatype' => 'string'),
				'founderuid' => array('name' => lang('blockclass', 'blockclass_group_field_founderuid'), 'formtype' => 'text', 'datatype' => 'int'),
				'posts' => array('name' => lang('blockclass', 'blockclass_group_field_posts'), 'formtype' => 'text', 'datatype' => 'int'),
				'todayposts' => array('name' => lang('blockclass', 'blockclass_group_field_todayposts'), 'formtype' => 'text', 'datatype' => 'int'),
				'threads' => array('name' => lang('blockclass', 'blockclass_group_field_threads'), 'formtype' => 'date', 'datatype' => 'int'),
				'membernum' => array('name' => lang('blockclass', 'blockclass_group_field_membernum'), 'formtype' => 'text', 'datatype' => 'int'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_group_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'level' => array('name' => lang('blockclass', 'blockclass_group_field_level'), 'formtype' => 'text', 'datatype' => 'int'),
				'commoncredits' => array('name' => lang('blockclass', 'blockclass_group_field_commoncredits'), 'formtype' => 'text', 'datatype' => 'int'),
				'activity' => array('name' => lang('blockclass', 'blockclass_group_field_activity'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_forum' => array(
					'name' => lang('blockclass', 'blockclass_forum_forum'),
					'script' => 'forum',
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

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('grouptype');
		$typeids = array();
		if(!empty($parameter['gtids'])) {
			if($parameter['gtids'][0] == '0') {
				unset($parameter['gtids'][0]);
			}
			$typeids = $parameter['gtids'];
		}
		if(empty($typeids)) $typeids = array_keys($_G['cache']['grouptype']['second']);
		$fids		= !empty($parameter['fids']) ? explode(',',$parameter['fids']) : array();
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$orderby	= in_array($parameter['orderby'], array('displayorder','posts','todayposts','threads', 'membernum', 'dateline', 'level', 'activity', 'commoncredits')) ? $parameter['orderby'] : 'displayorder';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
		$sqlban = !empty($bannedids) ? ' AND f.fid NOT IN ('.dimplode($bannedids).')' : '';

		if($fids) {
			$wheresql = "f.fid IN (".dimplode($fids).") AND f.status='3' AND f.type='sub' $sqlban";
		} else {
			$wheresql = !empty($typeids) ? "f.fup IN (".dimplode($typeids).") AND f.status='3' AND f.type='sub' $sqlban" : "0";
		}
		$wheresql .= " AND f.level > '0'";

		if(in_array($orderby, array('posts', 'todayposts', 'threads', 'level', 'commoncredits'))) {
			$orderbysql = "f.$orderby DESC";
		} elseif(in_array($orderby, array('dateline', 'activity', 'membernum'))) {
			$orderbysql = "ff.$orderby DESC";
		} else {
			$orderbysql = "f.displayorder ASC";
		}
		$list = array();
		$query = DB::query('SELECT f.*, ff.* FROM '.DB::table('forum_forum').' f LEFT JOIN '.DB::table('forum_forumfield')." ff ON f.fid = ff.fid WHERE $wheresql ORDER BY $orderbysql LIMIT $items");
		while($data = DB::fetch($query)) {
			$list[] = array(
				'id' => $data['fid'],
				'idtype' => 'fid',
				'title' => cutstr($data['name'], $titlelength, ''),
				'url' => 'forum.php?mod=group&fid='.$data['fid'],
				'pic' => 'group/'.$data['banner'],
				'picflag' => '1',
				'summary' => cutstr($data['description'], $summarylength, ''),
				'fields' => array(
					'fulltitle' => $data['name'],
					'icon' => !empty($data['icon']) ? $_G['setting']['attachurl'].'group/'.$data['icon'] : STATICURL.'image/common/nophoto.gif',
					'founderuid' => $data['founderuid'],
					'foundername' => $data['foundername'],
					'threads' => $data['threads'],
					'posts' => $data['posts'],
					'todayposts' => $data['todayposts'],
					'dateline' => $data['dateline'],
					'level' => $data['level'],
					'membernum' => $data['membernum'],
					'activity' => $data['activity'],
					'commoncredits' => $data['commoncredits'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}


?>