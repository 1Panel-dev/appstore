<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_member.php 26595 2011-12-16 03:50:07Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_member {

	var $version = '1.0';
	var $name = 'member_name';
	var $description = 'member_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array(
		'act' => array(
			'title' => 'member_complete_var_act',
			'type' => 'mradio',
			'value' => array(
				array('favorite', 'member_complete_var_act_favorite'),
				array('magic', 'member_complete_var_act_magic'),
			),
			'default' => 'favorite',
			'sort' => 'complete',
		),
		'num' => array(
			'title' => 'member_complete_var_num',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		),
		'time' => array(
			'title' => 'member_complete_var_time',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		)
	);

	function preprocess($task) {
		global $_G;

		$act = C::t('common_taskvar')->get_value_by_taskid($task['taskid'], 'act');
		if($act == 'favorite') {
			$value = C::t('home_favorite')->count_by_uid_idtype($_G['uid'], 'tid');
			C::t('forum_spacecache')->insert(array(
				'uid' => $_G['uid'],
				'variable' => 'favorite'.$task['taskid'],
				'value' => $value,
				'expiration' => $_G['timestamp'],
			), false, true);
		}
	}

	function csc($task = array()) {
		global $_G;

		$taskvars = array('num' => 0);
		$num = 0;
		foreach(C::t('common_taskvar')->fetch_all_by_taskid($task['taskid']) as $taskvar) {
			if($taskvar['value']) {
				$taskvars[$taskvar['variable']] = $taskvar['value'];
			}
		}

		$taskvars['time'] = floatval($taskvars['time']);
		if($taskvars['act'] == 'favorite') {
			$favorite = C::t('forum_spacecache')->fetch($_G['uid'], 'favorite'.$task['taskid']);
			$favorite = $favorite['value'];
			$num = C::t('home_favorite')->count_by_uid_idtype($_G['uid'], 'tid') - $favorite;
		} elseif($taskvars['act'] == 'magic') {
			$maxtime = $taskvars['time'] ? $task['applytime']+3600*$taskvars['time'] : 0;
			$num = C::t('common_magiclog')->count_by_action_uid_dateline(2, $_G['uid'], $task['applytime'], $maxtime);
		}

		if($num && $num >= $taskvars['num']) {
			if($taskvars['act'] == 'favorite' || $taskvars['act'] == 'userapp') {
				C::t('forum_spacecache')->delete($_G['uid'], $taskvars['act'].$task['taskid']);
			}
			return TRUE;
		} elseif($taskvars['time'] && TIMESTAMP >= $task['applytime'] + 3600 * $taskvars['time'] && (!$num || $num < $taskvars['num'])) {
			return FALSE;
		} else {
			return array('csc' => $num > 0 && $taskvars['num'] ? sprintf("%01.2f", $num / $taskvars['num'] * 100) : 0, 'remaintime' => $taskvars['time'] ? $task['applytime'] + $taskvars['time'] * 3600 - TIMESTAMP : 0);
		}
	}

	function view($task, $taskvars) {
		$return = lang('task/member', 'task_complete_time_start');
		if($taskvars['complete']['time']) {
			$return .= lang('task/member', 'task_complete_time_limit', array('value' => $taskvars['complete']['time']['value']));
		}
		$taskvars['complete']['num']['value'] = intval($taskvars['complete']['num']['value']);
		if($taskvars['complete']['act']['value'] == 'favorite') {
			$return .= lang('task/member', 'task_complete_act_favorite', array('value' => $taskvars['complete']['num']['value']));
		} elseif($taskvars['complete']['act']['value'] == 'userapp') {
			$return .= lang('task/member', 'task_complete_act_userapp', array('value' => $taskvars['complete']['num']['value']));
		} else {
			$return .= lang('task/member', 'task_complete_act_magic', array('value' => $taskvars['complete']['num']['value']));
		}
		return $return;
	}

}


?>