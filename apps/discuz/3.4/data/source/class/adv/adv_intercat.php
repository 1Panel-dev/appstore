<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_intercat.php 26440 2011-12-13 06:28:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_intercat {

	var $version = '1.1';
	var $name = 'intercat_name';
	var $description = 'intercat_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('forum');
	var $imagesizes = array('468x60', '658x60', '728x90', '760x90', '950x90');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'intercat_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'position' => array(
				'title' => 'intercat_position',
				'type' => 'mradio',
				'value' => array(),
				'default' => 0,
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		$settings['fids']['value'][] = array(-1, 'intercat_position_fav');
		$settings['position']['value'][] = array(0, 'intercat_position_random');
		$settings['position']['value'][] = array(-1, 'intercat_position_fav');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] == 'group') {
				$settings['fids']['value'][] = array($fid, $forum['name']);
				$settings['position']['value'][] = array($fid, $forum['name']);
			}
		}

		return $settings;
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
	}

	function evalcode() {
		return array(
			'check' => '
			if(!(!$parameter[\'position\'] || $parameter[\'position\'] && $params[2] == $parameter[\'position\'] || $parameter[\'fids\'] && in_array($_GET[\'gid\'], $parameter[\'fids\']))) {
				$checked = false;
			}',
			'create' => '$adcode = $codes[$adids[array_rand($adids)]];',
		);
	}

}

?>