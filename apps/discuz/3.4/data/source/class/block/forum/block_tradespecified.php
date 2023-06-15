<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_tradespecified.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_trade', 'class/block/forum');

class block_tradespecified extends block_trade {
	function block_tradespecified() {
		$this->setting = array(
			'tids' => array(
				'title' => 'tradelist_tids',
				'type' => 'text'
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'uids' => array(
				'title' => 'tradelist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'tradelist_keyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'tradelist_fids',
				'type' => 'mselect',
				'value' => array()
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
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_trade_script_tradespecified');
	}
}

?>