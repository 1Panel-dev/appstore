<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_membernew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_membernew extends block_member {
	function block_membernew() {
		$this->setting = array(
			'gender' => array(
				'title' => 'memberlist_gender',
				'type' => 'mradio',
				'value' => array(
					array('1', 'memberlist_gender_male'),
					array('2', 'memberlist_gender_female'),
					array('', 'memberlist_gender_nolimit'),
				),
				'default' => ''
			),
			'birthcity' => array(
				'title' => 'memberlist_birthcity',
				'type' => 'district',
				'value' => array('xbirthprovince', 'xbirthcity', 'xbirthdist', 'xbirthcommunity'),
			),
			'residecity' => array(
				'title' => 'memberlist_residecity',
				'type' => 'district',
				'value' => array('xresideprovince', 'xresidecity', 'xresidedist', 'xresidecommunity')
			),
			'avatarstatus' => array(
				'title' => 'memberlist_avatarstatus',
				'type' => 'radio',
				'default' => ''
			),
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_member_script_membernew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'regdate';
		return parent::cookparameter($parameter);
	}
}

?>