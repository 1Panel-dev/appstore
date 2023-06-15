<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_activitycity.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_activity', 'class/block/forum');

class block_activitycity extends block_activity {
	function block_activitycity() {
		$this->setting = array(
			'fids'	=> array(
				'title' => 'activitylist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'place' => array(
				'title' => 'activitylist_place',
				'type' => 'text'
			),
			'class' => array(
				'title' => 'activitylist_class',
				'type' => 'select',
				'value' => array()
			),
			'orderby' => array(
				'title' => 'activitylist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('dateline', 'activitylist_orderby_dateline'),
					array('weekstart', 'activitylist_orderby_weekstart'),
					array('monthstart', 'activitylist_orderby_monthstart'),
					array('weekexp', 'activitylist_orderby_weekexp'),
					array('monthexp', 'activitylist_orderby_monthexp'),
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => 'activitylist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'activitylist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'activitylist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_activity_script_activitycity');
	}
}


?>