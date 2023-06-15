<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_membercredit.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_membercredit extends block_member {
	function block_membercredit() {
		$this->setting = array(
			'orderby' => array(
				'title' => 'memberlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('credits', 'memberlist_orderby_credits'),
					array('extcredits', 'memberlist_orderby_extcredits'),
				),
				'default' => 'credits'
			),
			'extcredit' => array(
				'title' => 'memberlist_orderby_extcreditselect',
				'type' => 'select',
				'value' => array()
			),
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_member_script_membercredit');
	}
}

?>