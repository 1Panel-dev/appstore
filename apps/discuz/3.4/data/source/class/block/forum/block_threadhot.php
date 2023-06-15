<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_threadhot.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_thread', 'class/block/forum');

class block_threadhot extends block_thread {
	function block_threadhot() {
		$this->setting = array(
			'fids'	=> array(
				'title' => 'threadlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'special' => array(
				'title' => 'threadlist_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_special_1'),
					array(2, 'threadlist_special_2'),
					array(3, 'threadlist_special_3'),
					array(4, 'threadlist_special_4'),
					array(5, 'threadlist_special_5'),
					array(0, 'threadlist_special_0'),
				),
				'default' => array('0')
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'rewardstatus' => array(
				'title' => 'threadlist_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'threadlist_special_reward_0'),
					array(1, 'threadlist_special_reward_1'),
					array(2, 'threadlist_special_reward_2')
				),
				'default' => 0,
			),
			'picrequired' => array(
				'title' => 'threadlist_picrequired',
				'type' => 'radio',
				'value' => '0'
			),
			'orderby' => array(
				'title' => 'threadlist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('lastpost', 'threadlist_orderby_lastpost'),
					array('dateline', 'threadlist_orderby_dateline'),
					array('replies', 'threadlist_orderby_replies'),
					array('views', 'threadlist_orderby_views'),
					array('heats', 'threadlist_orderby_heats'),
					array('recommends', 'threadlist_orderby_recommends'),
				),
				'default' => 'heats'
			),
			'postdateline' => array(
				'title' => 'threadlist_postdateline',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'threadlist_postdateline_nolimit'),
					array('3600', 'threadlist_postdateline_hour'),
					array('86400', 'threadlist_postdateline_day'),
					array('604800', 'threadlist_postdateline_week'),
					array('2592000', 'threadlist_postdateline_month'),
				),
				'default' => '0'
			),
			'lastpost' => array(
				'title' => 'threadlist_lastpost',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'threadlist_lastpost_nolimit'),
					array('3600', 'threadlist_lastpost_hour'),
					array('86400', 'threadlist_lastpost_day'),
					array('604800', 'threadlist_lastpost_week'),
					array('2592000', 'threadlist_lastpost_month'),
				),
				'default' => '0'
			),
			'titlelength' => array(
				'title' => 'threadlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'threadlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_thread_script_threadhot');
	}

}

?>