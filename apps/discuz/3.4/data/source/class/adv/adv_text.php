<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_text.php 13462 2010-07-27 07:26:27Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_text {

	var $version = '2.0';
	var $name = 'text_name';
	var $description = 'text_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('forum', 'group', 'home', 'portal');
	var $imagesizes = array('120x60', '250x60', '100x100');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'text_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'groups' => array(
				'title' => 'text_groups',
				'type' => 'mselect',
				'value' => array(),
			),
			'category' => array(
				'title' => 'text_category',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache(array('forums', 'grouptype'));
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(0, '&nbsp;');
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(-1, 'text_index');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		foreach($_G['cache']['grouptype']['first'] as $gid => $group) {
			$settings['groups']['value'][] = array($gid, $group['name']);
			if($group['secondlist']) {
				foreach($group['secondlist'] as $sgid) {
					$settings['groups']['value'][] = array($sgid, str_repeat('&nbsp;', 4).$_G['cache']['grouptype']['second'][$sgid]['name']);
				}
			}
		}
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
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
		if(is_array($parameters['extra']['groups']) && in_array(0, $parameters['extra']['groups'])) {
			$parameters['extra']['groups'] = array();
		}
	}

	function evalcode() {
		return array(
			'check' => '
			if($_G[\'basescript\'] == \'forum\' && $parameter[\'fids\'] && !(in_array($_G[\'fid\'], $parameter[\'fids\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'fids\']))
			|| $_G[\'basescript\'] == \'group\' && $parameter[\'groups\'] && !(in_array($_G[\'grouptypeid\'], $parameter[\'groups\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'groups\']))
			|| $_G[\'basescript\'] == \'portal\' && $parameter[\'category\'] && !(in_array($_G[\'catid\'], $parameter[\'category\']))
			) {
				$checked = false;
			}',
			'create' => '
				$advcount = count($adids);
				if($advcount > 5) {
	        			$minfillpercent = 0;
	        			for($cols = 5; $cols >= 3; $cols--) {
	        				if(($remainder = $advcount % $cols) == 0) {
	        					$advcols = $cols;
	        					break;
	        				} elseif($remainder / $cols > $minfillpercent)  {
	        					$minfillpercent = $remainder / $cols;
	        					$advcols = $cols;
	        				}
	        			}
	        		} else {
	        			$advcols = $advcount;
	        		}
	        		$adcode = \'\';
	        		for($i = 0; $i < $advcols * ceil($advcount / $advcols); $i++) {
	        			$adcode .= (($i + 1) % $advcols == 1 || $advcols == 1 ? \'<tr>\' : \'\').
	        				\'<td width="\'.intval(100 / $advcols).\'%">\'.(isset($codes[$adids[$i]]) ? $codes[$adids[$i]] : \'&nbsp;\').\'</td>\'.
	        				(($i + 1) % $advcols == 0 ? "</tr>\n" : \'\');
	        		}
				$adcode = \'<table cellpadding="0" cellspacing="1">\'.$adcode.\'</table>\';
			',
		);
	}

}

?>