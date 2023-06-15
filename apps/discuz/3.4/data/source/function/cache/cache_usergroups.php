<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_usergroups.php 32967 2013-03-28 10:57:48Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_usergroups() {
	global $_G;

	$data_uf = C::t('common_usergroup_field')->fetch_all_fields(null, array('groupid', 'readaccess', 'allowgetattach', 'allowgetimage', 'allowmediacode', 'maxsigsize', 'allowbegincode'));

	foreach(C::t('common_usergroup')->range_orderby_creditshigher() as $key=>$value) {
		$group = array_merge(array('groupid' => $value['groupid'], 'type' => $value['type'], 'grouptitle' => $value['grouptitle'], 'creditshigher' => $value['creditshigher'], 'creditslower' => $value['creditslower'], 'stars' => $value['stars'], 'color' => $value['color'], 'icon' => $value['icon'], 'system' => $value['system']), $data_uf[$key]);
		if($group['type'] == 'special') {
			if($group['system'] != 'private') {
				list($dailyprice) = explode("\t", $group['system']);
				$group['pubtype'] = $dailyprice > 0 ? 'buy' : 'free';
			}
		}
		unset($group['system']);
		$groupid = $group['groupid'];
		$group['grouptitle'] = $group['color'] ? '<font color="'.$group['color'].'">'.$group['grouptitle'].'</font>' : $group['grouptitle'];
		if($_G['setting']['userstatusby'] == 1) {
			$group['userstatusby'] = 1;
		} elseif($_G['setting']['userstatusby'] == 2) {
			if($group['type'] != 'member') {
				$group['userstatusby'] = 1;
			} else {
				$group['userstatusby'] = 2;
			}
		}
		if($group['type'] != 'member') {
			unset($group['creditshigher'], $group['creditslower']);
		}
		unset($group['groupid']);
		$data[$groupid] = $group;
	}
	savecache('usergroups', $data);

	build_cache_usergroups_single();

	foreach(C::t('common_admingroup')->range() as $data) {
		savecache('admingroup_'.$data['admingid'], $data);
	}
}

function build_cache_usergroups_single() {
	$pluginvalue = pluginsettingvalue('groups');
	$allowthreadplugin = C::t('common_setting')->fetch('allowthreadplugin', true);

	$data_uf = C::t('common_usergroup_field')->range();
	$data_ag = C::t('common_admingroup')->range();
	foreach(C::t('common_usergroup')->range() as $gid => $data) {
		$data = array_merge($data, (array)$data_uf[$gid], (array)$data_ag[$gid]);
		$ratearray = array();
		if($data['raterange']) {
			foreach(explode("\n", $data['raterange']) as $rating) {
				$rating = explode("\t", $rating);
				$ratearray[$rating[0]] = array('isself' => $rating[1], 'min' => $rating[2], 'max' => $rating[3], 'mrpd' => $rating[4]);
			}
		}
		$data['raterange'] = $ratearray;
		$data['grouptitle'] = $data['color'] ? '<font color="'.$data['color'].'">'.$data['grouptitle'].'</font>' : $data['grouptitle'];
		$data['grouptype'] = $data['type'];
		$data['grouppublic'] = $data['system'] != 'private';
		$data['groupcreditshigher'] = $data['creditshigher'];
		$data['groupcreditslower'] = $data['creditslower'];
		$data['maxspacesize'] = intval($data['maxspacesize']) * 1024 * 1024;
		$data['allowthreadplugin'] = !empty($allowthreadplugin[$data['groupid']]) ? $allowthreadplugin[$data['groupid']] : array();
		$data['plugin'] = $pluginvalue[$data['groupid']];
		unset($data['type'], $data['system'], $data['creditshigher'], $data['creditslower'], $data['groupavatar'], $data['admingid']);
		savecache('usergroup_'.$data['groupid'], $data);
	}
}