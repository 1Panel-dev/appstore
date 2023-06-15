<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_article.php 13141 2010-07-22 00:56:42Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_article {

	var $version = '1.0';
	var $name = 'article_name';
	var $description = 'article_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('portal');
	var $imagesizes = array('250x60', '250x250', '250x300', '658x40', '658x60');
	var $categoryvalue = array();

	function getsetting() {
		global $_G;
		$settings = array(
			'position' => array(
				'title' => 'article_position',
				'type' => 'mradio',
				'value' => array(
					array(1, 'article_position_float'),
					array(2, 'article_position_up'),
					array(3, 'article_position_down'),
				),
				'default' => 1,
			),
			'category' => array(
				'title' => 'article_category',
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