<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_tradehot.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_trade', 'class/block/forum');

class block_tradehot extends block_trade {
	function block_tradehot() {
		$this->setting = array(
			'fids'	=> array(
				'title' => 'tradelist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'orderby' => array(
				'title' => 'tradelist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('todayhots', 'tradelist_orderby_todayhots'),
					array('weekhots', 'tradelist_orderby_weekhots'),
					array('monthhots', 'tradelist_orderby_monthhots'),
				),
				'default' => 'weekhots'
			),
			'titlelength' => array(
				'title' => 'tradelist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'tradelist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'tradelist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_trade_script_tradehot');
	}
}

?>