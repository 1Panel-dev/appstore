<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_advs.php 30323 2012-05-22 09:33:36Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_advs() {
	global $_G;
	$advlist = $data = array();
	$data['code'] = $data['parameters'] = $data['evalcode'] = array();
	foreach(C::t('common_advertisement')->fetch_all_old() as $adv) {
		foreach(explode("\t", $adv['targets']) as $target) {
			$data['code'][$target][$adv['type']][$adv['advid']] = $adv['code'];
		}
		$etype = explode(':', $adv['type']);
		if(count($etype) > 1) {
			$advtype_class = DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/adv/adv_'.$etype[1].'.php';
			if(!file_exists($advtype_class) || !in_array($etype[0], $_G['setting']['plugins']['available'])) {
				continue;
			}
			require_once $advtype_class;
			$advclass = 'adv_'.$etype[1];
		} else {
			$advtype_class = libfile('adv/'.$adv['type'], 'class');
			if(!file_exists($advtype_class)) {
				continue;
			}
			require_once $advtype_class;
			$advclass = 'adv_'.$adv['type'];
		}
		$advclass = new $advclass;
		$adv['parameters'] = unserialize($adv['parameters']);
		unset($adv['parameters']['style'], $adv['parameters']['html'], $adv['parameters']['displayorder']);
		$data['parameters'][$adv['type']][$adv['advid']] = $adv['parameters'];
		if($adv['parameters']['extra']) {
			$data['parameters'][$adv['type']][$adv['advid']] = array_merge($data['parameters'][$adv['type']][$adv['advid']], $adv['parameters']['extra']);
			unset($data['parameters'][$adv['type']][$adv['advid']]['extra']);
		}
		$advlist[] = $adv;
		$data['evalcode'][$adv['type']] = $advclass->evalcode($adv);
	}
	updateadvtype();

	savecache('advs', $data);
}

function updateadvtype() {
	global $_G;

	$advtype = array();
	foreach(C::t('common_advertisement')->fetch_all_old() as $row) {
		$advtype[$row['type']] = 1;
	}
	$_G['setting']['advtype'] = $advtype = array_keys($advtype);
	C::t('common_setting')->update('advtype', $advtype);
}

?>