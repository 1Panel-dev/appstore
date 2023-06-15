<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tasks.php 34093 2013-10-09 05:41:18Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$id = intval($_GET['id']);
$membervars = array('act', 'num', 'time');
$postvars = array('act', 'forumid', 'num', 'time', 'threadid', 'authorid');
$modvars = array();
$custom_types = C::t('common_setting')->fetch('tasktypes', true);
$custom_scripts = array_keys($custom_types);

$submenus = array();
foreach($custom_types as $k => $v) {
	$submenus[] = array($v['name'], "tasks&operation=add&script=$k", $_GET['script'] == $k);
}

if(!($operation)) {

	if(!submitcheck('tasksubmit')) {

		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 1),
			$submenus ? array(array('menu' => 'add', 'submenu' => $submenus)) : array(),
			array('nav_task_type', 'tasks&operation=type', 0)
		));
		showformheader('tasks');
		showtableheader();
		showsetting('tasks_on', 'taskonnew', $_G['setting']['taskon'], 'radio');
		showtablefooter();
		showtableheader('tasks_list', 'fixpadding');
		showsubtitle(array('display_order', 'available', 'name', 'tasks_reward', 'time', 'tasks_status', ''));

		$starttasks = array();
		foreach(C::t('common_task')->fetch_all_data() as $task) {

			if($task['reward'] == 'credit') {
				$reward = cplang('credits').' '.$_G['setting']['extcredits'][$task['prize']]['title'].' '.$task['bonus'].' '.$_G['setting']['extcredits'][$task['prize']]['unit'];
			} elseif($task['reward'] == 'magic') {
				$magicname = C::t('common_magic')->fetch($task['prize']);
				$reward = cplang('tasks_reward_magic').' '.$magicname['name'].' '.$task['bonus'].' '.cplang('magic_unit');
			} elseif($task['reward'] == 'medal') {
				$medalname = C::t('forum_medal')->fetch($task['prize']);
				$reward = cplang('medals').' '.$medalname['name'].($task['bonus'] ? ' '.cplang('validity').$task['bonus'].' '.cplang('days') : '');
			} elseif($task['reward'] == 'invite') {
				$reward = cplang('tasks_reward_invite').' '.$task['prize'].($task['bonus'] ? ' '.cplang('validity').$task['bonus'].' '.cplang('days') : '');
			} elseif($task['reward'] == 'group') {
				$group =  C::t('common_usergroup')->fetch($task['prize']);
				$grouptitle = $group['grouptitle'];
				$reward = cplang('usergroup').' '.$grouptitle.($task['bonus'] ? ' '.cplang('validity').' '.$task['bonus'].' '.cplang('days') : '');
			} else {
				$reward = cplang('none');
			}
			if($task['available'] == '1' && (!$task['starttime'] || $task['starttime'] <= TIMESTAMP) && (!$task['endtime'] || $task['endtime'] > TIMESTAMP)) {
				$starttasks[] = $task['taskid'];
			}

			$checked = $task['available'] ? ' checked="checked"' : '';

			if($task['starttime'] && $task['endtime']) {
				$task['time'] = dgmdate($task['starttime'], 'y-m-d H:i').' ~ '.dgmdate($task['endtime'], 'y-m-d H:i');
			} elseif($task['starttime'] && !$task['endtime']) {
				$task['time'] = dgmdate($task['starttime'], 'y-m-d H:i').' '.cplang('tasks_online');
			} elseif(!$task['starttime'] && $task['endtime']) {
				$task['time'] = dgmdate($task['endtime'], 'y-m-d H:i').' '.cplang('tasks_offline');
			} else {
				$task['time'] = cplang('nolimit');
			}

			if($task['available'] == 2 && ($task['starttime'] > TIMESTAMP || ($task['endtime'] && $task['endtime'] <= TIMESTAMP))) {
				$task['available'] = 1;
				C::t('common_task')->update($task['taskid'], array('available' => 1));
			}
			if($task['available'] == 1 && (!$task['starttime'] || $task['starttime'] <= TIMESTAMP) && (!$task['endtime'] || $task['endtime'] > TIMESTAMP)) {
				$task['available'] = 2;
				C::t('common_task')->update($task['taskid'], array('available' => 2));
			}

			showtablerow('', array('class="td25"', 'class="td25"'), array(
				'<input type="text" class="txt" name="displayordernew['.$task['taskid'].']" value="'.$task['displayorder'].'" size="3" />',
				"<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$task[taskid]]\" value=\"1\"$checked><input type=\"hidden\" name=\"availableold[$task[taskid]]\" value=\"$task[available]\">",
				"<input type=\"text\" class=\"txt\" name=\"namenew[$task[taskid]]\" size=\"20\" value=\"$task[name]\"><input type=\"hidden\" name=\"nameold[$task[taskid]]\" value=\"$task[name]\">",
				$reward,
				$task['time'].'<input type="hidden" name="scriptnamenew['.$task['taskid'].']" value="'.$task['scriptname'].'">',
				($task['available'] == 1 ? ($task['endtime'] && $task['endtime'] <= TIMESTAMP ? cplang('tasks_status_3') : cplang('tasks_status_1')) : ($task['available'] == 2 ? cplang('tasks_status_2') : cplang('tasks_status_0'))),
				"<a href=\"".ADMINSCRIPT."?action=tasks&operation=edit&id=$task[taskid]\" class=\"act\">$lang[edit]</a>&nbsp;&nbsp;<a href=\"".ADMINSCRIPT."?action=tasks&operation=delete&id=$task[taskid]\" class=\"act\">$lang[delete]</a>"
			));

		}

		if($starttasks) {
			C::t('common_task')->update($starttasks, array('available' => 2));
			require_once libfile('class/task');
			$tasklib = & task::instance();
			$tasklib->update_available(1);
		}

		showsubmit('tasksubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$checksettingsok = TRUE;
		if(is_array($_GET['namenew'])) {
			foreach($_GET['namenew'] as $id => $name) {
				$_GET['availablenew'][$id] = $_GET['availablenew'][$id] && (!$starttimenew[$id] || $starttimenew[$id] <= TIMESTAMP) && (!$endtimenew[$id] || $endtimenew[$id] > TIMESTAMP) ? 2 : $_GET['availablenew'][$id];
				$update = array('name' => dhtmlspecialchars($_GET['namenew'][$id]), 'available' => $_GET['availablenew'][$id]);
				if(isset($_GET['displayordernew'][$id])) {
					$update['displayorder'] = $_GET['displayordernew'][$id];
				}
				C::t('common_task')->update($id, $update);
			}
		}

		if($_GET['taskonnew'] != $_G['setting']['taskon']) {
			C::t('common_setting')->update('taskon', $_GET['taskonnew']);
		}

		updatecache('setting');
		require_once libfile('class/task');
		$tasklib = & task::instance();
		$tasklib->update_available(1);

		if($checksettingsok) {
			cpmsg('tasks_succeed', 'action=tasks', 'succeed');
		} else {
			cpmsg('tasks_setting_invalid', '', 'error');
		}

	}

} elseif($operation == 'add' && $_GET['script']) {

	$task_name = $task_description = $task_icon = $task_period = $task_periodtype = $task_conditions = '';
	if(in_array($_GET['script'], $custom_scripts)) {
		$escript = explode(':', $_GET['script']);
		if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $_GET['script'])) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclass = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$_GET['script'], 'class');
			$taskclass = 'task_'.$_GET['script'];
		}
		$task = new $taskclass;
		$task_name = lang('task/'.$_GET['script'], $task->name);
		$task_description = lang('task/'.$_GET['script'], $task->description);
		$task_icon = $task->icon;
		$task_period = $task->period;
		$task_periodtype = $task->periodtype;
		$task_conditions = $task->conditions;
	} else {
		cpmsg('parameters_error', '', 'error');
	}

	if(!submitcheck('addsubmit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 0),
			array(array('menu' => 'add', 'submenu' => $submenus), 1),
			array('nav_task_type', 'tasks&operation=type', 0)
		));

		showformheader('tasks&operation=add&script='.$_GET['script']);
		showtableheader('tasks_add_basic', 'fixpadding');
		showsetting('tasks_add_name', 'name', $task_name, 'text');
		showsetting('tasks_add_desc', 'description', $task_description, 'textarea');
		if(count($escript) > 1 && file_exists(DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif')) {
			$defaulticon = 'source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif';
		} else {
			$defaulticon = 'static/image/task/task.gif';
		}
		showsetting('tasks_add_icon', 'iconnew', $task_icon, 'text', '', 0, cplang('tasks_add_icon_comment', array('defaulticon' => $defaulticon)));
		showsetting('tasks_add_starttime', 'starttime', '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_endtime', 'endtime', '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_periodtype', array('periodtype', array(
			array(0, cplang('tasks_add_periodtype_hour')),
			array(1, cplang('tasks_add_periodtype_day')),
			array(2, cplang('tasks_add_periodtype_week')),
			array(3, cplang('tasks_add_periodtype_month')),
		)), $task_periodtype, 'mradio');
		showsetting('tasks_add_period', 'period', $task_period, 'text');
		showsetting('tasks_add_reward', array('reward', array(
			array('', cplang('none'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')),
			array('credit', cplang('credits'), array('reward_credit' => '', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')),
			$_G['setting']['magicstatus'] ? array('magic', cplang('tasks_reward_magic'), array('reward_credit' => 'none', 'reward_magic' => '', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => 'none')) : '',
			$_G['setting']['medalstatus'] ? array('medal', cplang('medals'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => '', 'reward_group' => 'none', 'reward_invite' => 'none')) : '',
			$_G['setting']['regstatus'] > 1 ? array('invite', cplang('tasks_reward_invite'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => '')) : '',
			array('group', cplang('tasks_add_group'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => '', 'reward_invite' => 'none'))
		)), '', 'mradio');

		$extcreditarray = array(array(0, cplang('select')));
		foreach($_G['setting']['extcredits'] as $creditid => $extcredit) {
			$extcreditarray[] = array($creditid, $extcredit['title']);
		}

		showtagheader('tbody', 'reward_credit');
		showsetting('tasks_add_extcredit', array('prize_credit', $extcreditarray), 0, 'select');
		showsetting('tasks_add_credits', 'bonus_credit', '0', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_magic');
		showsetting('tasks_add_magicname', array('prize_magic', C::t('common_magic')->fetch_all_name_by_available()), 0, 'select');
		showsetting('tasks_add_magicnum', 'bonus_magic', '0', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_medal');
		showsetting('tasks_add_medalname', array('prize_medal', C::t('forum_medal')->fetch_all_name_by_available()), 0, 'select');
		showsetting('tasks_add_medalexp', 'bonus_medal', '', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_invite');
		showsetting('tasks_add_invitenum', 'prize_invite', '1', 'text');
		showsetting('tasks_add_inviteexp', 'bonus_invite', '10', 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_group');
		showsetting('tasks_add_group', array('prize_group', C::t('common_usergroup')->fetch_all_by_type('special', 0)), 0, 'select');

		showsetting('tasks_add_groupexp', 'bonus_group', '', 'text');
		showtagfooter('tbody');

		showtitle('tasks_add_appyperm');
		showsetting('tasks_add_groupperm', array('grouplimit', array(
			array('all', cplang('tasks_add_group_all'), array('specialgroup' => 'none')),
			array('member', cplang('tasks_add_group_member'), array('specialgroup' => 'none')),
			array('admin', cplang('tasks_add_group_admin'), array('specialgroup' => 'none')),
			array('special', cplang('tasks_add_group_special'), array('specialgroup' => ''))
		)), 'all', 'mradio');
		showtagheader('tbody', 'specialgroup');
		showsetting('tasks_add_usergroup', array('applyperm[]', C::t('common_usergroup')->fetch_all_by_type()), 0, 'mselect');

		showtagfooter('tbody');
		showsetting('tasks_add_maxnum', 'tasklimits', '', 'text');

		if(is_array($task_conditions)) {
			foreach($task_conditions as $taskvarkey => $taskvar) {
				if($taskvar['sort'] == 'apply' && $taskvar['title']) {
					if(!empty($taskvar['value']) && is_array($taskvar['value'])) {
						foreach($taskvar['value'] as $k => $v) {
							$taskvar['value'][$k][1] = lang('task/'.$_GET['script'], $taskvar['value'][$k][1]);
						}
					}
					$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
						($taskvar['type'] == 'mselect' ? array($taskvarkey.'[]', $taskvar['value']) : array($taskvarkey, $taskvar['value']))
						: $taskvarkey;
					$comment = lang('task/'.$_GET['script'], $taskvar['title'].'_comment');
					$comment = $comment != $taskvar['title'].'_comment' ? $comment : '';
					showsetting(lang('task/'.$_GET['script'], $taskvar['title']).':', $varname, $taskvar['value'], $taskvar['type'], '', 0, $comment);
				}
			}
		}

		showtitle('tasks_add_conditions');

		if(in_array($_GET['script'], $custom_scripts)) {

			$haveconditions = false;
			if(is_array($task_conditions)) {
				foreach($task_conditions as $taskvarkey => $taskvar) {
					if($taskvar['sort'] == 'complete' && $taskvar['title']) {
						if(!empty($taskvar['value']) && is_array($taskvar['value'])) {
							foreach($taskvar['value'] as $k => $v) {
								$taskvar['value'][$k][1] = lang('task/'.$_GET['script'], $taskvar['value'][$k][1]);
							}
						}
						$haveconditions = true;
						$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
							($taskvar['type'] == 'mselect' ? array($taskvarkey.'[]', $taskvar['value']) : array($taskvarkey, $taskvar['value']))
							: $taskvarkey;
						$comment = lang('task/'.$_GET['script'], $taskvar['title'].'_comment');
						$comment = $comment != $taskvar['title'].'_comment' ? $comment : '';
						showsetting(lang('task/'.$_GET['script'], $taskvar['title']).':', $varname, $taskvar['default'], $taskvar['type'], '', 0, $comment);
					}
				}
			}
			if(!$haveconditions) {
				showtablerow('', 'class="td27" colspan="2"', cplang('nolimit'));
			}
		}

		showsubmit('addsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$applyperm = $_GET['grouplimit'] == 'special' && is_array($_GET['applyperm']) ? implode("\t", $_GET['applyperm']) : $_GET['grouplimit'];
		$_GET['starttime'] = strtotime($_GET['starttime']);
		$_GET['endtime'] = strtotime($_GET['endtime']);
		$reward = $_GET['reward'];
		$prize = $_GET['prize_'.$reward];
		$bonus = $_GET['bonus_'.$reward];
		if(!$_GET['name'] || !$_GET['description']) {
			cpmsg('tasks_basic_invalid', '', 'error');
		} elseif(($_GET['endtime'] && $_GET['endtime'] <= TIMESTAMP) || ($_GET['starttime'] && $_GET['endtime'] && $_GET['endtime'] <= $_GET['starttime'])) {
			cpmsg('tasks_time_invalid', '', 'error');
		} elseif($reward && (!$prize || ($reward == 'credit' && !$bonus))) {
			cpmsg('tasks_reward_invalid', '', 'error');
		}
		$data = array(
			'relatedtaskid' => $_GET['relatedtaskid'],
			'available' => 0,
			'name' => $_GET['name'],
			'description' => $_GET['description'],
			'icon' => $_GET['iconnew'],
			'tasklimits' => $_GET['tasklimits'],
			'applyperm' => $applyperm,
			'scriptname' => $_GET['script'],
			'starttime' => $_GET['starttime'],
			'endtime' => $_GET['endtime'],
			'period' => $_GET['period'],
			'periodtype' => $_GET['periodtype'],
			'reward' => $reward,
			'prize' => $prize,
			'bonus' => $bonus,
		);
		$taskid = C::t('common_task')->insert($data, true);

		if(is_array($task_conditions)) {
			foreach($task_conditions as $taskvarkey => $taskvars) {
				if($taskvars['title']) {
					$comment = lang('task/'.$_GET['script'], $taskvars['title'].'_comment');
					$comment = $comment != $taskvars['title'].'_comment' ? $comment : '';
					$data = array(
						'taskid' => $taskid,
						'sort' => $taskvars['sort'],
						'name' => lang('task/'.$_GET['script'], $taskvars['title']),
						'description' => $comment,
						'variable' => $taskvarkey,
						'value' => is_array($_GET[''.$taskvarkey]) ? serialize($_GET[''.$taskvarkey]) : $_GET[''.$taskvarkey],
						'type' => $taskvars['type'],
					);
					C::t('common_taskvar')->insert($data);
				}
			}
		}
		require_once libfile('class/task');
		$tasklib = & task::instance();
		$tasklib->update_available(1);

		cpmsg('tasks_succeed', "action=tasks", 'succeed');

	}

} elseif($operation == 'edit' && $id) {

	$task = C::t('common_task')->fetch($id);

	if(!submitcheck('editsubmit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		shownav('extended', 'nav_tasks');
		showsubmenu('nav_tasks', array(
			array('admin', 'tasks', 0),
			array(array('menu' => 'add', 'submenu' => $submenus)),
			array('nav_task_type', 'tasks&operation=type', 0)
		));
		$escript = explode(':', $task['scriptname']);

		showformheader('tasks&operation=edit&id='.$id);
		showtableheader(cplang('tasks_edit').' - '.$task['name'], 'fixpadding');
		showsetting('tasks_add_name', 'name', $task['name'], 'text');
		showsetting('tasks_add_desc', 'description', $task['description'], 'textarea');
		if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $task['scriptname']) && file_exists(DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif')) {
			$defaulticon = 'source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif';
		} else {
			$defaulticon = 'static/image/task/task.gif';
		}
		showsetting('tasks_add_icon', 'iconnew', $task['icon'], 'text', '', 0, cplang('tasks_add_icon_comment', array('defaulticon' => $defaulticon)));
		showsetting('tasks_add_starttime', 'starttime', $task['starttime'] ? dgmdate($task['starttime'], 'Y-m-d H:i') : '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_endtime', 'endtime', $task['endtime'] ? dgmdate($task['endtime'], 'Y-m-d H:i') : '', 'calendar', '', 0, '', 1);
		showsetting('tasks_add_periodtype', array('periodtype', array(
			array(0, cplang('tasks_add_periodtype_hour')),
			array(1, cplang('tasks_add_periodtype_day')),
			array(2, cplang('tasks_add_periodtype_week')),
			array(3, cplang('tasks_add_periodtype_month')),
		)), $task['periodtype'], 'mradio');
		showsetting('tasks_add_period', 'period', $task['period'], 'text');
		showsetting('tasks_add_reward', array('reward', array(
			array('', cplang('none'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none')),
			array('credit', cplang('credits'), array('reward_credit' => '', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none')),
			$_G['setting']['magicstatus'] ? array('magic', cplang('tasks_reward_magic'), array('reward_credit' => 'none', 'reward_magic' => '', 'reward_medal' => 'none', 'reward_group' => 'none')) : '',
			$_G['setting']['medalstatus'] ? array('medal', cplang('medals'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => '', 'reward_group' => 'none')) : '',
			$_G['setting']['regstatus'] > 1 ? array('invite', cplang('tasks_reward_invite'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => 'none', 'reward_invite' => '')) : '',
			array('group', cplang('tasks_add_group'), array('reward_credit' => 'none', 'reward_magic' => 'none', 'reward_medal' => 'none', 'reward_group' => ''))
		)), $task['reward'], 'mradio');

		$extcreditarray = array(array(0, cplang('select')));
		foreach($_G['setting']['extcredits'] as $creditid => $extcredit) {
			$extcreditarray[] = array($creditid, $extcredit['title']);
		}

		showtagheader('tbody', 'reward_credit', $task['reward'] == 'credit');
		showsetting('tasks_add_extcredit', array('prize_credit', $extcreditarray), $task['prize'], 'select');
		showsetting('tasks_add_credits', 'bonus_credit', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_magic', $task['reward'] == 'magic');
		showsetting('tasks_add_magicname', array('prize_magic', C::t('common_magic')->fetch_all_name_by_available()), $task['prize'], 'select');
		showsetting('tasks_add_magicnum', 'bonus_magic', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_medal', $task['reward'] == 'medal');
		showsetting('tasks_add_medalname', array('prize_medal', C::t('forum_medal')->fetch_all_name_by_available()), $task['prize'], 'select');
		showsetting('tasks_add_medalexp', 'bonus_medal', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_invite', $task['reward'] == 'invite');
		showsetting('tasks_add_invitenum', 'prize_invite', $task['prize'], 'text');
		showsetting('tasks_add_inviteexp', 'bonus_invite', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', 'reward_group', $task['reward'] == 'group');
		showsetting('tasks_add_group', array('prize_group', C::t('common_usergroup')->fetch_all_by_type('special', 0)), $task['prize'], 'select');
		showsetting('tasks_add_groupexp', 'bonus_group', $task['bonus'], 'text');
		showtagfooter('tbody');

		showtitle('tasks_add_appyperm');
		if(!$task['applyperm']) {
			$task['applyperm'] = 'all';
		}
		$task['grouplimit'] = in_array($task['applyperm'], array('all', 'member', 'admin')) ? $task['applyperm'] : 'special';
		showsetting('tasks_add_groupperm', array('grouplimit', array(
			array('all', cplang('tasks_add_group_all'), array('specialgroup' => 'none')),
			array('member', cplang('tasks_add_group_member'), array('specialgroup' => 'none')),
			array('admin', cplang('tasks_add_group_admin'), array('specialgroup' => 'none')),
			array('special', cplang('tasks_add_group_special'), array('specialgroup' => ''))
		)), $task['grouplimit'], 'mradio');
		showtagheader('tbody', 'specialgroup', $task['grouplimit'] == 'special');
		showsetting('tasks_add_usergroup', array('applyperm[]', C::t('common_usergroup')->fetch_all_by_type()), explode("\t", $task['applyperm']), 'mselect');
		showtagfooter('tbody');
		$tasklist = array(0 => array('taskid'=>0, 'name'=>cplang('nolimit') ));
		foreach(C::t('common_task')->fetch_all_by_available(2) as $value) {
			if($value['taskid'] != $task['taskid']) {
				$tasklist[$value['taskid']] = array('taskid'=>$value['taskid'], 'name'=>$value['name']);
			}
		}
		showsetting('tasks_add_relatedtask', array('relatedtaskid', $tasklist), $task['relatedtaskid'], 'select');
		showsetting('tasks_add_maxnum', 'tasklimits', $task['tasklimits'], 'text');

		$taskvars = array();
		foreach(C::t('common_taskvar')->fetch_all_by_taskid($id) as $taskvar) {
			if($taskvar['sort'] == 'apply') {
				$taskvars['apply'][] = $taskvar;
			} elseif($taskvar['sort'] == 'complete') {
				$taskvars['complete'][$taskvar['variable']] = $taskvar;
			} elseif($taskvar['sort'] == 'setting' && $taskvar['name']) {
				$taskvars['setting'][$taskvar['variable']] = $taskvar;
			}
		}

		if($taskvars['apply']) {
			foreach($taskvars['apply'] as $taskvar) {
				showsetting($taskvar['name'], $taskvar['variable'], $taskvar['value'], $taskvar['type'], '', 0, $taskvar['description']);
			}
		}

		showtitle('tasks_add_conditions');

		if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $task['scriptname'])) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclass = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$task['scriptname'], 'class');
			$taskclass = 'task_'.$task['scriptname'];
		}
		$taskcv = new $taskclass;

		if($taskvars['complete']) {
			foreach($taskvars['complete'] as $taskvar) {
				$taskcvar = $taskcv->conditions[$taskvar['variable']];
				if(is_array($taskcvar['value'])) {
					foreach($taskcvar['value'] as $k => $v) {
						$taskcvar['value'][$k][1] = lang('task/'.$task['scriptname'], $taskcvar['value'][$k][1]);
					}
				}
				$varname = in_array($taskvar['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
					($taskvar['type'] == 'mselect' ? array($taskvar['variable'].'[]', $taskcvar['value']) : array($taskvar['variable'], $taskcvar['value']))
					: $taskvar['variable'];
				if(in_array($taskvar['type'], array('mcheckbox', 'mselect'))) {
					$taskvar['value'] = dunserialize($taskvar['value']);
				}
				showsetting($taskvar['name'], $varname, $taskvar['value'], $taskvar['type'], '', 0, $taskvar['description']);
			}
		} else {
			showtablerow('', 'class="td27" colspan="2"', cplang('nolimit'));
		}

		showsubmit('editsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$applyperm = $_GET['grouplimit'] == 'special' && is_array($_GET['applyperm']) ? implode("\t", $_GET['applyperm']) : $_GET['grouplimit'];
		$_GET['starttime'] = strtotime($_GET['starttime']);
		$_GET['endtime'] = strtotime($_GET['endtime']);
		$reward = $_GET['reward'];
		$prize = $_GET['prize_'.$reward];
		$bonus = $_GET['bonus_'.$reward];

		if(!$_GET['name'] || !$_GET['description']) {
			cpmsg('tasks_basic_invalid', '', 'error');
		} elseif(($_GET['starttime'] != $task['starttime'] || $_GET['endtime'] != $task['endtime']) && (($_GET['endtime'] && $_GET['endtime'] <= TIMESTAMP) || ($_GET['starttime'] && $_GET['endtime'] && $_GET['endtime'] <= $_GET['starttime']))) {
			cpmsg('tasks_time_invalid', '', 'error');
		} elseif($reward && (!$prize || ($reward == 'credit' && !$bonus))) {
			cpmsg('tasks_reward_invalid', '', 'error');
		}

		if($task['available'] == '2' && ($_GET['starttime'] > TIMESTAMP || ($_GET['endtime'] && $_GET['endtime'] <= TIMESTAMP))) {
			C::t('common_task')->update($id, array('available' => 1));
		}
		if($task['available'] == '1' && (!$_GET['starttime'] || $_GET['starttime'] <= TIMESTAMP) && (!$_GET['endtime'] || $_GET['endtime'] > TIMESTAMP)) {
			C::t('common_task')->update($id, array('available' => 2));
		}

		$itemarray = array();
		foreach(C::t('common_taskvar')->fetch_all_by_taskid($id, 'IS NOT NULL') as $taskvar) {
			$itemarray[] = $taskvar['variable'];
		}
		C::t('common_task')->update($id, array(
			'relatedtaskid' => $_GET['relatedtaskid'],
			'name' => $_GET['name'],
			'description' => $_GET['description'],
			'icon' => $_GET['iconnew'],
			'tasklimits' => $_GET['tasklimits'],
			'applyperm' => $applyperm,
			'starttime' => $_GET['starttime'],
			'endtime' => $_GET['endtime'],
			'period' => $_GET['period'],
			'periodtype' => $_GET['periodtype'],
			'reward' => $reward,
			'prize' => $prize,
			'bonus' => $bonus,
		));

		foreach($itemarray as $item) {
			$value = $_GET[''.$item];
			if(in_array($item, array('num', 'time', 'threadid'))) {
				$value = intval($value);
			}
			if($value !== null) {
				C::t('common_taskvar')->update_by_taskid($id, $item, array('value' => is_array($value) ? serialize($value) : $value));
			}
		}
		require_once libfile('class/task');
		$tasklib = & task::instance();
		$tasklib->update_available(1);

		cpmsg('tasks_succeed', "action=tasks", 'succeed');

	}

} elseif($operation == 'delete' && $id) {

	if(!$_GET['confirmed']) {
		cpmsg('tasks_del_confirm', "action=tasks&operation=delete&id=$id", 'form');
	}

	C::t('common_task')->delete($id);
	C::t('common_taskvar')->delete_by_taskid($id);
	C::t('common_mytask')->delete(0, $id);
	require_once libfile('class/task');
	$tasklib = & task::instance();
	$tasklib->update_available(1);

	cpmsg('tasks_del', 'action=tasks', 'succeed');

} elseif($operation == 'type') {

	shownav('extended', 'nav_tasks');
	showsubmenu('nav_tasks', array(
		array('admin', 'tasks', 0),
		$submenus ? array(array('menu' => 'add', 'submenu' => $submenus)) : array(),
		array('nav_task_type', 'tasks&operation=type', 1)
	));
	showtips('tasks_tips_add_type');

	$tasks = gettasks();

	showtableheader('', 'fixpadding');

	if($tasks) {
		showsubtitle(array('name', 'tasks_version', 'copyright', ''));
		foreach($tasks as $task) {
			showtablerow('', '', array(
				$task['name'].($task['filemtime'] > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : ''),
				$task['version'],
				$task['copyright'],
				in_array($task['class'], $custom_scripts) ? "<a href=\"".ADMINSCRIPT."?action=tasks&operation=upgrade&script=$task[class]\" class=\"act\">$lang[tasks_upgrade]</a> <a href=\"".ADMINSCRIPT."?action=tasks&operation=uninstall&script=$task[class]\" class=\"act\">$lang[tasks_uninstall]</a><br />" : "<a href=\"".ADMINSCRIPT."?action=tasks&operation=install&script=$task[class]\" class=\"act\">$lang[tasks_install]</a>"
			));
		}
	} else {
		showtablerow('', '', $lang['task_module_nonexistence']);
	}

	showtablefooter();

} elseif($operation == 'install' && $_GET['script']) {

	if(C::t('common_task')->count_by_scriptname($_GET['script'])) {
		cpmsg('tasks_install_duplicate', '', 'error');
	}

	$escript = explode(':', $_GET['script']);
	if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $_GET['script'])) {
		include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
		$taskclass = 'task_'.$escript[1];
	} else {
		require_once libfile('task/'.$_GET['script'], 'class');
		$taskclass = 'task_'.$_GET['script'];
	}
	$task = new $taskclass;
	if(method_exists($task, 'install')) {
		$task->install();
	}

	$custom_types[$_GET['script']] = array('name' => lang('task/'.$_GET['script'], $task->name), 'version' => $task->version);
	C::t('common_setting')->update('tasktypes', $custom_types);
	require_once libfile('class/task');
	$tasklib = & task::instance();
	$tasklib->update_available(1);

	cpmsg('tasks_installed', 'action=tasks&operation=type', 'succeed');

} elseif($operation == 'uninstall' && $_GET['script']) {

	if(!$_GET['confirmed']) {
		cpmsg('tasks_uninstall_confirm', "action=tasks&operation=uninstall&script={$_GET['script']}", 'form');
	}

	$ids = array();
	foreach(C::t('common_task')->fetch_all_by_scriptname($_GET['script']) as $task) {
		$ids[] = $task['taskid'];
	}
	if($ids) {
		C::t('common_task')->delete($ids);
		C::t('common_taskvar')->delete_by_taskid($ids);
		C::t('common_mytask')->delete(0, $ids);
	}

	$escript = explode(':', $_GET['script']);
	if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $_GET['script'])) {
		include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
		$taskclass = 'task_'.$escript[1];
	} else {
		require_once libfile('task/'.$_GET['script'], 'class');
		$taskclass = 'task_'.$_GET['script'];
	}
	$task = new $taskclass;
	if(method_exists($task, 'uninstall')) {
		$task->uninstall();
	}

	unset($custom_types[$_GET['script']]);
	C::t('common_setting')->update('tasktypes', $custom_types);
	require_once libfile('class/task');
	$tasklib = & task::instance();
	$tasklib->update_available(1);

	cpmsg('tasks_uninstalled', 'action=tasks&operation=type', 'succeed');

} elseif($operation == 'upgrade' && $_GET['script']) {

	$escript = explode(':', $_GET['script']);
	if(count($escript) > 1 && preg_match('/^[\w\_:]+$/', $_GET['script'])) {
		include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
		$taskclass = 'task_'.$escript[1];
	} else {
		require_once libfile('task/'.$_GET['script'], 'class');
		$taskclass = 'task_'.$_GET['script'];
	}
	$task = new $taskclass;

	if($custom_types[$_GET['script']]['version'] >= $task->version) {
		cpmsg('tasks_newest', '', 'error');
	}

	if(method_exists($task, 'upgrade')) {
		$task->upgrade();
	}
	$task->name = lang('task/'.$_GET['script'], $task->name);
	$task->description = lang('task/'.$_GET['script'], $task->description);

	C::t('common_task')->update_by_scriptname($_GET['script'], array('version' => $task->version));
	$custom_types[$_GET['script']] = array('name' => $task->name, 'version' => $task->version);
	C::t('common_setting')->update('tasktypes', $custom_types);
	require_once libfile('class/task');
	$tasklib = & task::instance();
	$tasklib->update_available(1);

	cpmsg('tasks_updated', 'action=tasks&operation=type', 'succeed');

}


function gettasks() {
	global $_G;
	$checkdirs = array_merge(array(''), $_G['setting']['plugins']['available']);
	$tasks = array();
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_ROOT.'./source/plugin/'.$key.'/task';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/task';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$taskdir = dir($dir);
		while($entry = $taskdir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^task\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$taskclass = substr($entry, 0, -4);
				if(class_exists($taskclass)) {
					$task = new $taskclass();
					$script = substr($taskclass, 5);
					$script = ($key ? $key.':' : '').$script;
					$tasks[$entry] = array(
						'class' => $script,
						'name' => lang('task/'.$script, $task->name),
						'version' => $task->version,
						'copyright' => lang('task/'.$script, $task->copyright),
						'filemtime' => @filemtime($dir.'/'.$entry)
					);
				}
			}
		}
	}
	uasort($tasks, 'filemtimesort');
	return $tasks;
}

?>