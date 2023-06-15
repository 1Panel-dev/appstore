<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_album.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_album extends discuz_block {
	var $setting = array();
	function block_album() {
		$this->setting = array(
			'aids'	=> array(
				'title' => 'albumlist_aids',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => 'albumlist_uids',
				'type' => 'text',
				'value' => ''
			),
			'catid' => array(
				'title' => 'albumlist_catid',
				'type' => 'mselect',
			),
			'orderby' => array(
				'title' => 'albumlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'albumlist_orderby_dateline'),
					array('updatetime', 'albumlist_orderby_updatetime'),
					array('picnum', 'albumlist_orderby_picnum'),
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => 'albumlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'albumlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_album_script_album');
	}

	function blockclass() {
		return array('album', lang('blockclass', 'blockclass_space_album'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_album_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_album_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_album_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'uid' => array('name' => lang('blockclass', 'blockclass_album_field_uid'), 'formtype' => 'text', 'datatype' => 'int'),
				'username' => array('name' => lang('blockclass', 'blockclass_album_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_album_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'updatetime' => array('name' => lang('blockclass', 'blockclass_album_field_updatetime'), 'formtype' => 'date', 'datatype' => 'date'),
				'picnum' => array('name' => lang('blockclass', 'blockclass_album_field_picnum'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['catid']) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_all_category'));
			loadcache('albumcategory');
			foreach($_G['cache']['albumcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['albumcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
							if($value2['children']) {
								foreach($value2['children'] as $catid3) {
									$value3 = $_G['cache']['albumcategory'][$catid3];
									$settings['catid']['value'][] = array($value3['catid'], '---- '.$value3['catname']);
								}
							}
						}
					}
				}
			}
		}
		return $settings;
	}


	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$aids		= !empty($parameter['aids']) ? explode(',', $parameter['aids']) : array();
		$catid		= !empty($parameter['catid']) ? $parameter['catid'] : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('dateline', 'picnum', 'updatetime')) ? $parameter['orderby'] : 'dateline';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = array();

		$query = C::t('home_album')->fetch_all_by_block($aids, $bannedids, $uids, $catid, $startrow, $items, $orderby);
		foreach($query as $data) {
			$list[] = array(
				'id' => $data['albumid'],
				'idtype' => 'albumid',
				'title' => cutstr($data['albumname'], $titlelength, ''),
				'url' => "home.php?mod=space&uid=$data[uid]&do=album&id=$data[albumid]",
				'pic' => 'album/'.$data['pic'],
				'picflag' => $data['picflag'],
				'summary' => '',
				'fields' => array(
					'fulltitle' => $data['albumname'],
					'uid'=>$data['uid'],
					'username'=>$data['username'],
					'dateline'=>$data['dateline'],
					'updatetime'=>$data['updatetime'],
					'picnum'=>$data['picnum'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>