<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_otherfriendlink.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_otherfriendlink extends discuz_block {

	var $setting = array();

	function block_otherfriendlink() {
		$this->setting = array(
			'type' => array(
				'title' => 'friendlink_type',
				'type' => 'mcheckbox',
				'value' => array(
					array('1', 'friendlink_type_group1'),
					array('2', 'friendlink_type_group2'),
					array('3', 'friendlink_type_group3'),
					array('4', 'friendlink_type_group4'),
				),
				'default' => array('1','2','3','4')
			),
			'titlelength' => array(
				'title' => 'friendlink_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'friendlink_summarylength',
				'type' => 'text',
				'default' => 80
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_other_script_friendlink');
	}

	function blockclass() {
		return array('otherfriendlink', lang('blockclass', 'blockclass_other_friendlink'));
	}

	function fields() {
		return array(
					'url' => array('name' => lang('blockclass', 'blockclass_other_friendlink_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_other_friendlink_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_other_friendlink_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_other_friendlink_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				);
	}

	function getsetting() {
		return $this->setting;
	}

	function getdata($style, $parameter) {

		$parameter = $this->cookparameter($parameter);
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$type = !empty($parameter['type']) && is_array($parameter['type']) ? $parameter['type'] : array();
		$b = '0000';
		for($i=1;$i<=4;$i++) {
			if(in_array($i, $type)) {
				$b[$i-1] = '1';
			}
		}
		$type = intval($b, '2');
		$list = array();
		$query = C::t('common_friendlink')->fetch_all_by_displayorder($type);
		foreach ($query as $data) {
			$list[] = array(
				'id' => $data['id'],
				'idtype' => 'flid',
				'title' => cutstr($data['name'], $titlelength),
				'url' => $data['url'],
				'pic' => $data['logo'] ? $data['logo'] : $_G['style']['imgdir'].'/nophoto.gif',
				'picflag' => '0',
				'summary' => $data['description'],
				'fields' => array(
					'fulltitle' => $data['name'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}



?>