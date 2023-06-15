<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_thread.php 23660 2011-08-02 06:59:11Z maruitao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_thread {

	var $version = '1.0';
	var $name = 'thread_name';
	var $description = 'thread_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('forum', 'group');
	var $imagesizes = array('120x60', '120x240');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'thread_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'groups' => array(
				'title' => 'thread_groups',
				'type' => 'mselect',
				'value' => array(),
			),
			'position' => array(
				'title' => 'thread_position',
				'type' => 'mradio',
				'value' => array(
					array(2, 'thread_position_top'),
					array(3, 'thread_position_right'),
					array(1, 'thread_position_bottom'),
				),
				'default' => 1,
			),
			'pnumber' => array(
				'title' => 'thread_pnumber',
				'type' => 'mselect',
				'value' => array(
					array(0, 'thread_pnumber_all'),
				),
				'default' => array(0),
			),
		);
		loadcache(array('forums', 'grouptype'));
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(0, '&nbsp;');
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
		for($i = 1;$i <= $_G['ppp'];$i++) {
			$settings['pnumber']['value'][$i] = array($i, '> #'.$i);
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
		if(is_array($parameters['extra']['groups']) && in_array(0, $parameters['extra']['groups'])) {
			$parameters['extra']['groups'] = array();
		}
		if(is_array($parameters['extra']['pnumber']) && in_array(0, $parameters['extra']['pnumber'])) {
			$parameters['extra']['pnumber'] = array();
		}
	}

	function evalcode($adv) {
		return array(
			'check' => '
			if($params[2] != $parameter[\'position\']
			|| $parameter[\'pnumber\'] && !in_array($params[3] + 1, (array)$parameter[\'pnumber\'])
			|| $_G[\'basescript\'] == \'forum\' && $parameter[\'fids\'] && !in_array($_G[\'fid\'], $parameter[\'fids\'])
			|| $_G[\'basescript\'] == \'group\' && $parameter[\'groups\'] && !in_array($_G[\'grouptypeid\'], $parameter[\'groups\'])
			) {
				$checked = false;
			}',
			'create' => '
				$adid = $adids[array_rand($adids)];
				if($parameters[$adid][\'position\'] == 3) {
					$_G[\'thread\'][\'contentmr\'] = $parameters[$adid][\'width\'] ? $parameters[$adid][\'width\'].\'px\' : \'auto\';
					$extra = \'style="margin-left:10px;width:\'.$_G[\'thread\'][\'contentmr\'].\'"\';
				}
				$adcode = $codes[$adid];
			',
		);
	}

}

?>