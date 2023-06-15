<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_announcement.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_announcement extends discuz_block {

	var $setting = array();

	function block_announcement(){
		$this->setting = array(
			'type' => array(
				'title' => 'announcement_type',
				'type' => 'mcheckbox',
				'value' => array(
					array('0', 'announcement_type_text'),
					array('1', 'announcement_type_link'),
				),
				'default' => array('0')
			),
			'titlelength' => array(
				'title' => 'announcement_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'announcement_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'announcement_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_announcement_script_announcement');
	}

	function blockclass() {
		return array('announcement', lang('blockclass', 'blockclass_html_announcement'));
	}

	function fields() {
		return array(
				'url' => array('name' => lang('blockclass', 'blockclass_announcement_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_announcement_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'summary' => array('name' => lang('blockclass', 'blockclass_announcement_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'starttime' => array('name' => lang('blockclass', 'blockclass_announcement_field_starttime'), 'formtype' => 'text', 'datatype' => 'date'),
				'endtime' => array('name' => lang('blockclass', 'blockclass_announcement_field_endtime'), 'formtype' => 'text', 'datatype' => 'date'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;


		$type           = !empty($parameter['type']) && is_array($parameter['type']) ? array_map('intval', $parameter['type']) : array('0');
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$startrow       = !empty($parameter['startrow']) ? intval($parameter['startrow']) : '0';
		$items          = !empty($parameter['items']) ? intval($parameter['items']) : 10;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$time = TIMESTAMP;

		$list = array();
		foreach(C::t('forum_announcement')->fetch_all_by_time($time, $type, $bannedids, $startrow, $items) as $data) {
			$list[] = array(
				'id' => $data['id'],
				'idtype' => 'announcementid',
				'title' => cutstr(str_replace('\\\'', '&#39;', strip_tags($data['subject'])), $titlelength, ''),
				'url' => $data['type']=='1' ? $data['message'] : 'forum.php?mod=announcement&id='.$data['id'],
				'pic' => '',
				'picflag' => '',
				'summary' => cutstr(str_replace('\\\'', '&#39;', $data['message']), $summarylength, ''),
				'fields' => array(
					'starttime' => $data['starttime'],
					'endtime' => $data['endtime'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}


?>