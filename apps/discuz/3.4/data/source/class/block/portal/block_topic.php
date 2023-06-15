<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_topic.php 31470 2012-08-31 03:29:50Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_topic extends discuz_block {
	var $setting = array();
	function block_topic() {
		$this->setting = array(
			'topicids'	=> array(
				'title' => 'topiclist_topicids',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => 'topiclist_uids',
				'type' => 'text',
				'value' => ''
			),
			'picrequired' => array(
				'title' => 'topiclist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'orderby' => array(
				'title' => 'topiclist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'topiclist_orderby_dateline'),
					array('viewnum', 'topiclist_orderby_viewnum')
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => 'topiclist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'summary' => 'topiclist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'topiclist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_topic_script_topic');
	}

	function blockclass() {
		return array('topic', lang('blockclass', 'blockclass_portal_topic'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_topic_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_topic_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_topic_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('blockclass', 'blockclass_topic_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'uid' => array('name' => lang('blockclass', 'blockclass_topic_field_uid'), 'formtype' => 'text', 'datatype' => 'int'),
				'username' => array('name' => lang('blockclass', 'blockclass_topic_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_topic_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'viewnum' => array('name' => lang('blockclass', 'blockclass_topic_field_viewnum'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$topicids	= !empty($parameter['topicids']) ? explode(',',$parameter['topicids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = $parameter['titlelength'] ? intval($parameter['titlelength']) : 40;
		$summarylength = $parameter['summarylength'] ? intval($parameter['summarylength']) : 80;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('dateline', 'viewnum')) ? $parameter['orderby'] : 'dateline';
		$picrequired = !empty($parameter['picrequired']) ? 1 : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$datalist = $list = array();
		$wherearr = array();
		if($topicids) {
			$wherearr[] = 'topicid IN ('.dimplode($topicids).')';
		}
		if($bannedids) {
			$wherearr[] = 'topicid NOT IN ('.dimplode($bannedids).')';
		}
		if($uids) {
			$wherearr[] = 'uid IN ('.dimplode($uids).')';
		}
		if($picrequired) {
			$wherearr[] = "cover != ''";
		}
		$wherearr[] = "closed = '0'";
		require_once libfile('function/portal');
		foreach(C::t('portal_topic')->fetch_all_by_search_where($wherearr, "ORDER BY $orderby DESC", $startrow, $items) as $data) {
			if(empty($data['cover'])) {
				$data['cover'] = STATICURL.'image/common/nophoto.gif';
				$data['picflag'] = '0';
			}
			$list[] = array(
				'id' => $data['topicid'],
				'idtype' => 'topicid',
				'title' => cutstr($data['title'], $titlelength, ''),
				'url' => !empty($_G['setting']['makehtml']['flag']) && !empty($_G['setting']['makehtml']['topichtmldir']) && !$data['htmlmade'] ? fetch_topic_url($data) : 'portal.php?mod=topic&topic='.$data['name'],
				'pic' => $data['cover'] ? $data['cover'] : '',
				'picflag' => $data['picflag'] ? $data['picflag'] : '',
				'summary' => $data['summary'] ? cutstr($data['summary'], $summarylength, '') : '',
				'fields' => array(
					'fulltitle' => $data['title'],
					'uid'=>$data['uid'],
					'username'=>$data['username'],
					'dateline'=>$data['dateline'],
					'viewnum'=>$data['viewnum'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>