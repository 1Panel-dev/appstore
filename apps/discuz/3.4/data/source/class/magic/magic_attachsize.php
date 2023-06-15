<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_attachsize.php 26749 2011-12-22 07:38:37Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_attachsize {

	var $version = '1.0';
	var $name = 'attachsize_name';
	var $description = 'attachsize_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		$settings = array(
			'addsize' => array(
				'title' => 'attachsize_addsize',
				'type' => 'select',
				'value' => array(
					array('5', '5M'),
					array('10', '10M'),
					array('20', '20M'),
					array('50', '50M'),
					array('100', '100M'),
				),
				'default' => '10'
			),
		);
		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		$magicnew['addsize'] = in_array($parameters['addsize'], array(5,10,20,50,100)) ? intval($parameters['addsize']) : '10';
	}

	function usesubmit() {
		global $_G;

		$addsize = !empty($this->parameters['addsize']) ? intval($this->parameters['addsize']) : 10;
		C::t('common_member_field_home')->increase($_G['uid'], array('addsize' => $addsize));
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', 'uid', $_G['uid']);
		showmessage('magics_attachsize_message', '', array('num'=>intval($this->parameters['addsize'])), array('alert' => 'right', 'showdialog' => 1));
	}

	function show() {
		magicshowtips(lang('magic/attachsize', 'attachsize_info', array('num'=>intval($this->parameters['addsize']))));
	}

}

?>