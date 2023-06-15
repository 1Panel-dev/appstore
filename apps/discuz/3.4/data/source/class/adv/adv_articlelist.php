<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_articlelist.php 13141 2010-07-22 00:56:42Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_articlelist {

	var $version = '1.0';
	var $name = 'articlelist_name';
	var $description = 'articlelist_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('portal');
	var $imagesizes = array('658x40', '658x60');

	function getsetting() {
		$settings = array(
			'position' => array(
				'title' => 'articlelist_position',
				'type' => 'mradio',
				'value' => array(
					array(1, 'articlelist_position_up1'),
					array(2, 'articlelist_position_up2'),
					array(3, 'articlelist_position_down1'),
					array(4, 'articlelist_position_down2'),
				),
				'default' => 1,
			),
			'category' => array(
				'title' => 'articlelist_category',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('portalcategory');
		$this->getcategory(0);
		$settings['category']['value'] = $this->categoryvalue;
		return $settings;
	}

	function getcategory($upid) {
		global $_G;
		foreach($_G['cache']['portalcategory'] as $category) {
			if($category['upid'] == $upid) {
				$this->categoryvalue[] = array($category['catid'], str_repeat('&nbsp;', $category['level'] * 4).$category['catname']);
				$this->getcategory($category['catid']);
			}
		}
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['category']) && in_array(0, $parameters['extra']['category'])) {
			$parameters['extra']['category'] = array();
		}
	}

	function evalcode() {
		return array(
			'check' => '
			$checked = $params[2] == $parameter[\'position\'] && (!$parameter[\'category\'] || $parameter[\'category\'] && in_array($_G[\'catid\'], $parameter[\'category\']));
			',
			'create' => '$adcode = $codes[$adids[array_rand($adids)]];',
		);
	}

}

?>