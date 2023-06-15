<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_headerbanner.php 22514 2011-05-10 11:11:11Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_headerbanner {

	var $version = '1.0';
	var $name = 'headerbanner_name';
	var $description = 'headerbanner_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('portal', 'home', 'member', 'forum', 'group', 'plugin', 'custom');
	var $imagesizes = array('468x40', '468x60', '658x60', '728x90', '760x90', '950x90');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'headerbanner_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'groups' => array(
				'title' => 'headerbanner_groups',
				'type' => 'mselect',
				'value' => array(),
			),
			'category' => array(
				'title' => 'headerbanner_category',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache(array('forums', 'grouptype'));
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(0, '&nbsp;');
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(-1, 'headerbanner_index');
		$settings['fids']['value'][] = array(-2, 'Archiver');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		foreach($_G['cache']['grouptype']['first'] as $gid => $group) {
			$settings['groups']['value'][] = array($gid, str_repeat('&nbsp;', 4).$group['name']);
			if($group['secondlist']) {
				foreach($group['secondlist'] as $sgid) {
					$settings['groups']['value'][] = array($sgid, str_repeat('&nbsp;', 8).$_G['cache']['grouptype']['second'][$sgid]['name']);
				}
			}
		}
		loadcache('portalcategory');
		$this->categoryvalue[] = array(-1, 'headerbanner_index');
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
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
		if(is_array($parameters['extra']['groups']) && in_array(0, $parameters['extra']['groups'])) {
			$parameters['extra']['groups'] = array();
		}
		if(is_array($parameters['extra']['category']) && in_array(0, $parameters['extra']['category'])) {
			$parameters['extra']['category'] = array();
		}
	}

	function evalcode($adv) {
		return array(
			'check' => '
			if($_G[\'basescript\'] == \'forum\' && $parameter[\'fids\'] && !(!defined(\'IN_ARCHIVER\') && (in_array($_G[\'fid\'], $parameter[\'fids\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'fids\'])) || defined(\'IN_ARCHIVER\') && in_array(-2, $parameter[\'fids\']))
			|| $_G[\'basescript\'] == \'group\' && $parameter[\'groups\'] && !(in_array($_G[\'grouptypeid\'], $parameter[\'groups\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'groups\']))
			|| $_G[\'basescript\'] == \'portal\' && $parameter[\'category\'] && !(!empty($_G[\'catid\']) && in_array($_G[\'catid\'], $parameter[\'category\']) || empty($_G[\'catid\']) && in_array(-1, $parameter[\'category\']))
			) {
				$checked = false;
			}',
			'create' => '
				$adid = $adids[array_rand($adids)];
				$extra = $parameters[$adid][\'height\'] ? \' style="line-height:\'.$parameters[$adid][\'height\'].\'px;height:\'.$parameters[$adid][\'height\'].\'px"\' : \'\';
				$adcode = $codes[$adid];
			',
		);
	}

}

?>