<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: commonblock_html.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class commonblock_html  extends discuz_block{

	function fields() {
		return array();
	}

	function blockclass() {
		return array('html', lang('blockclass', 'blockclass_html_html'));
	}

}