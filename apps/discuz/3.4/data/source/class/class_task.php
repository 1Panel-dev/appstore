<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_task.php 34346 2014-03-19 03:10:01Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task {

	var $task;
	var $taskvars;
	var $message;
	var $multipage;
	var $listdata;

	function task() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new task();
		}
		return $object;
	}

	function tasklist($item) {
		global $_G;

		$multipage = '';
		$page = max(1, intval($_GET['page']));
		$start_limit = ($page - 1) * $_G['tpp'];
		$tasklist = $endtaskids = $magicids = $medalids = $groupids = array();


		$updated = FALSE;
		$num = 0;
		foreach(C::t('common_task')->fetch_all_by_status($_G['uid'], $item) as $task) {
			if($item == 'new' || $item == 'canapply') {
				list($task['allowapply'], $task['t']) = $this->checknextperiod($task);
				if($task['allowapply'] < 0) {
					continue;
				}
				$task['noperm'] = $task['applyperm'] && $task['applyperm'] != 'all' && !(($task['applyperm'] == 'member' && in_array($_G['adminid'], array(0, -1))) || ($task['applyperm'] == 'admin' && $_G['adminid'] > '0') || forumperm($task['applyperm']));
				$task['appliesfull'] = $task['tasklimits'] && $task['achievers'] >= $task['tasklimits'];
				if($item == 'canapply' && ($task['noperm'] || $task['appliesfull'])) {
					continue;
				}
			}
			$num++;
			if($task['reward'] == 'magic') {
				$magicids[] = $task['prize'];
			} elseif($task['reward'] == 'medal') {
				$medalids[] = $task['prize'];
			} elseif($task['reward'] == 'invite') {
				$invitenum = $task['prize'];
			} elseif($task['reward'] == 'group') {
				$groupids[] = $task['prize'];
			}
			if($task['available'] == '2' && ($task['starttime'] > TIMESTAMP || ($task['endtime'] && $task['endtime'] <= TIMESTAMP))) {
				$endtaskids[] = $task['taskid'];
			}
			$csc = explode("\t", $task['csc']);
			$task['csc'] = floatval($csc[0]);
			$task['lastupdate'] = intval($csc[1]);
			if(!$updated && $item == 'doing' && $task['csc'] < 100) {
				$updated = TRUE;
				$escript = explode(':', $task['scriptname']);
				if(count($escript) > 1) {
					include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
					$taskclassname = 'task_'.$escript[1];
				} else {
					require_once libfile('task/'.$task['scriptname'], 'class');
					$taskclassname = 'task_'.$task['scriptname'];
				}
				$taskclass = new $taskclassname;
				$task['applytime'] = $task['dateline'];
				if(method_exists($taskclass, 'csc')) {
					$result = $taskclass->csc($task);
				} else {
					showmessage('task_not_found', '', array('taskclassname' => $taskclassname));
				}
				if($result === TRUE) {
					$task['csc'] = '100';
					C::t('common_mytask')->update($_G['uid'], $task['taskid'], array('csc' => $task['csc']));
				} elseif($result === FALSE) {
					C::t('common_mytask')->update($_G['uid'], $task['taskid'], array('status' => -1));
				} else {
					$task['csc'] = floatval($result['csc']);
					C::t('common_mytask')->update($_G['uid'], $task['taskid'], array('csc' => $task['csc']."\t".$_G['timestamp']));
				}
			}
			if(in_array($item, array('done', 'failed')) && $task['period']) {
				list($task['allowapply'], $task['t']) = $this->checknextperiod($task);
				$task['allowapply'] = $task['allowapply'] > 0 ? 1 : 0;
			}
			$task['icon'] = $task['icon'] ? $task['icon'] : 'task.gif';
			if(!preg_match('/^https?:\/\//is', $task['icon'])) {
				$escript = explode(':', $task['scriptname']);
				if(count($escript) > 1 && file_exists(DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif')) {
					$task['icon'] = 'source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif';
				} else {
					$task['icon'] = 'static/image/task/'.$task['icon'];
				}
			}
			$task['dateline'] = $task['dateline'] ? dgmdate($task['dateline'], 'u') : '';
			$tasklist[] = $task;
		}

		if($magicids) {
			foreach(C::t('common_magic')->fetch_all($magicids) as $magic) {
				$this->listdata[$magic['magicid']] = $magic['name'];
			}
		}

		if($medalids) {
			foreach(C::t('forum_medal')->fetch_all($medalids) as $medal) {
				$this->listdata[$medal['medalid']] = $medal['name'];
			}
		}

		if($groupids) {
			foreach(C::t('common_usergroup')->fetch_all($groupids) as $group) {
				$this->listdata[$group['groupid']] = $group['grouptitle'];
			}
		}

		if($invitenum) {
			$this->listdata[$invitenum] = $_G['lang']['invite_code'];
		}

		if($endtaskids) {
		}

		return $tasklist;
	}
	function view($id) {
		global $_G;

		$this->task = C::t('common_task')->fetch_by_uid($_G['uid'], $id);
		if(!$this->task) {
			showmessage('task_nonexistence');
		}
		switch($this->task['reward']) {
			case 'magic':
				$this->task['rewardtext'] = C::t('common_magic')->fetch($this->task['prize']);
				$this->task['rewardtext'] = $this->task['rewardtext']['name'];
				break;
			case 'medal':
				$this->task['rewardtext'] = C::t('forum_medal')->fetch($this->task['prize']);
				$this->task['rewardtext'] = $this->task['rewardtext']['name'];
				break;
			case 'group':
				$group = C::t('common_usergroup')->fetch($this->task['prize']);
				$this->task['rewardtext'] = $group['grouptitle'];
				break;
		}
		$this->task['icon'] = $this->task['icon'] ? $this->task['icon'] : 'task.gif';
		if(!preg_match('/^https?:\/\//is', $this->task['icon'])) {
			$escript = explode(':', $this->task['scriptname']);
			if(count($escript) > 1 && file_exists(DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif')) {
				$this->task['icon'] = 'source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.gif';
			} else {
				$this->task['icon'] = 'static/image/task/'.$this->task['icon'];
			}
		}
		$this->task['endtime'] = $this->task['endtime'] ? dgmdate($this->task['endtime'], 'u') : '';
		$this->task['description'] = nl2br($this->task['description']);

		$this->taskvars = array();
		foreach(C::t('common_taskvar')->fetch_all_by_taskid($id) as $taskvar) {
			if(!$taskvar['variable'] || $taskvar['value']) {
				if(!$taskvar['variable']) {
					$taskvar['value'] = $taskvar['description'];
				}
				if($taskvar['sort'] == 'apply') {
					$this->taskvars['apply'][] = $taskvar;
				} elseif($taskvar['sort'] == 'complete') {
					$this->taskvars['complete'][$taskvar['variable']] = $taskvar;
				} elseif($taskvar['sort'] == 'setting') {
					$this->taskvars['setting'][$taskvar['variable']] = $taskvar;
				}
			}
		}

		$this->task['grouprequired'] = $comma = '';
		$this->task['applyperm'] = $this->task['applyperm'] == 'all' ? '' : $this->task['applyperm'];
		if(!in_array($this->task['applyperm'], array('', 'member', 'admin'))) {
			$query = C::t('common_usergroup')->fetch_all(explode(',', str_replace("\t", ',', $this->task['applyperm'])));
			foreach($query as $group) {
				$this->task['grouprequired'] .= $comma.$group[grouptitle];
				$comma = ', ';
			}
		}

		if($this->task['relatedtaskid']) {
			$task = C::t('common_task')->fetch($this->task['relatedtaskid']);
			$_G['taskrequired'] = $task['name'];
		}

		$escript = explode(':', $this->task['scriptname']);
		if(count($escript) > 1) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclassname = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$this->task['scriptname'], 'class');
			$taskclassname = 'task_'.$this->task['scriptname'];
		}
		$taskclass = new $taskclassname;
		if($this->task['status'] == '-1') {
			if($this->task['period']) {
				list($allowapply, $this->task['t']) = $this->checknextperiod($this->task);
			} else {
				$allowapply = -4;
			}
		} elseif($this->task['status'] == '0') {
			$allowapply = -1;
			$csc = explode("\t", $this->task['csc']);
			$this->task['csc'] = floatval($csc[0]);
			$this->task['lastupdate'] = intval($csc[1]);
			if($this->task['csc'] < 100) {
				if(method_exists($taskclass, 'csc')) {
					$result = $taskclass->csc($this->task);
				}
				if($result === TRUE) {
					$this->task['csc'] = '100';
					C::t('common_mytask')->update($_G['uid'], $id, array('csc' => $this->task['csc']));
				} elseif($result === FALSE) {
					C::t('common_mytask')->update($_G['uid'], $id, array('status' => -1));
					dheader("Location: home.php?mod=task&do=view&id=$id");
				} else {
					$this->task['csc'] = floatval($result['csc']);
					C::t('common_mytask')->update($_G['uid'], $id, array('csc' => $this->task['csc']."\t".$_G['timestamp']));
				}
			}
		} elseif($this->task['status'] == '1') {
			if($this->task['period']) {
				list($allowapply, $this->task['t']) = $this->checknextperiod($this->task);
			} else {
				$allowapply = -5;
			}
		} else {
			$allowapply = 1;
		}
		if(method_exists($taskclass, 'view')) {
			$this->task['viewmessage'] = $taskclass->view($this->task, $this->taskvars);
		} else {
			$this->task['viewmessage'] = '';
		}

		if($allowapply > 0) {
			if($this->task['applyperm'] && $this->task['applyperm'] != 'all' && !(($this->task['applyperm'] == 'member' && in_array($_G['adminid'], array(0, -1))) || ($this->task['applyperm'] == 'admin' && $_G['adminid'] > '0') || preg_match("/(^|\t)(".$_G['groupid'].")(\t|$)/", $this->task['applyperm']))) {
				$allowapply = -2;
			} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
				$allowapply = -3;
			}
		}

		$this->task['dateline'] = dgmdate($this->task['dateline'], 'u');
		return $allowapply;

	}

	function checknextperiod($task) {
		global $_G;

		$allowapply = false;
		$nextapplytime = '';
		if($task['periodtype'] == 0) {
			$allowapply = TIMESTAMP - $task['dateline'] >= $task['period'] * 3600 ? 2 : -6;
			$nextapplytime = tasktimeformat($task['period'] * 3600 - TIMESTAMP + $task['dateline']);
		} elseif($task['periodtype'] == 1) {
			$todaytimestamp = TIMESTAMP - (TIMESTAMP + $_G['setting']['timeoffset'] * 3600) % 86400;
			$allowapply = $task['dateline'] < $todaytimestamp - ($task['period'] - 1) * 86400 ? 2 : -6;
			$nextapplytime = ($task['dateline'] - ($task['dateline'] + $_G['setting']['timeoffset'] * 3600) % 86400) + $task['period'] * 86400;
			$nextapplytime = dgmdate($nextapplytime);
		} elseif($task['periodtype'] == 2 && $task['period'] > 0 && $task['period'] <= 7) {
			$task['period'] = $task['period'] != 7 ? $task['period'] : 0;
			$todayweek = dgmdate(TIMESTAMP, 'w');
			$weektimestamp = TIMESTAMP - ($todayweek - $task['period']) * 86400;
			$weekstart = $weektimestamp - ($weektimestamp + $_G['setting']['timeoffset'] * 3600) % 86400;
			$weekfirstday = $weekstart - $task['period'] * 86400;
			if($task['dateline'] && ($task['dateline'] > $weekstart || $task['dateline'] > $weekfirstday)) {
				$allowapply = -6;
				if($task['dateline'] > $weekfirstday) {
					$weekstart += 604800;
				}
				$nextapplytime = dgmdate($weekstart);
			} else {
				$allowapply = 2;
			}
		} elseif($task['periodtype'] == 3 && $task['period'] > 0) {
			list($year, $month) = explode('/', dgmdate(TIMESTAMP, 'Y/n'));
			$monthstart = mktime(0, 0, 0, $month, $task['period'], $year);
			$monthfirstday = mktime(0, 0, 0, $month, 1, $year);
			if($task['dateline'] && ($task['dateline'] > $monthstart || $task['dateline'] > $monthfirstday)) {
				$allowapply = -6;
				if($task['dateline'] > $monthfirstday) {
					$monthstart = mktime(0, 0, 0, $month + 1, $task['period'], $year);
				}
				$nextapplytime = dgmdate($monthstart);
			} else {
				$allowapply = 2;
			}
		}
		return array($allowapply, $nextapplytime);
	}

	function apply($id) {
		global $_G;

		$this->task = C::t('common_task')->fetch($id);
		if($this->task['available'] != 2) {
			showmessage('task_nonexistence');
		} elseif(($this->task['starttime'] && $this->task['starttime'] > TIMESTAMP) || ($this->task['endtime'] && $this->task['endtime'] <= TIMESTAMP)) {
			showmessage('task_offline');
		} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
			showmessage('task_full');
		}

		if($this->task['relatedtaskid'] && !C::t('common_mytask')->count($_G['uid'], $this->task['relatedtaskid'], 1)) {
			return -1;
		} elseif($this->task['applyperm'] && $this->task['applyperm'] != 'all' && !(($this->task['applyperm'] == 'member' && in_array($_G['adminid'], array(0, -1))) || ($this->task['applyperm'] == 'admin' && $_G['adminid'] > '0') || preg_match("/(^|\t)(".$_G['groupid'].")(\t|$)/", $this->task['applyperm']))) {
			return -2;
		} elseif(!$this->task['period'] && C::t('common_mytask')->count($_G['uid'], $id)) {
			return -3;
		} elseif($this->task['period']) {
			$mytask = C::t('common_mytask')->fetch($_G['uid'], $id);
			$task = C::t('common_task')->fetch($id);
			$mytask['period'] = $task['period'];
			$mytask['periodtype'] = $task['periodtype'];
			unset($task);
			list($allowapply) = $this->checknextperiod($mytask);
			if($allowapply < 0) {
				return -4;
			}
		}

		$escript = explode(':', $this->task['scriptname']);
		if(count($escript) > 1) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclassname = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$this->task['scriptname'], 'class');
			$taskclassname = 'task_'.$this->task['scriptname'];
		}
		$taskclass = new $taskclassname;
		if(method_exists($taskclass, 'condition')) {
			$taskclass->condition();
		}
		C::t('common_mytask')->insert(array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'taskid' => $this->task['taskid'],
			'csc' => '0\t'.$_G['timestamp'],
			'dateline' => $_G['timestamp']
		), false, true);
		C::t('common_task')->update_applicants($this->task['taskid'], 1);
		if(method_exists($taskclass, 'preprocess')) {
			$taskclass->preprocess($this->task);
		}
		return true;
	}

	function draw($id) {
		global $_G;

		if(!($this->task = C::t('common_task')->fetch_by_uid($_G['uid'], $id))) {
			showmessage('task_nonexistence');
		} elseif(!isset($this->task['status']) || $this->task['status'] != 0) {
			showmessage('task_not_underway');
		} elseif($this->task['tasklimits'] && $this->task['achievers'] >= $this->task['tasklimits']) {
			return -1;
		}

		$escript = explode(':', $this->task['scriptname']);
		if(count($escript) > 1) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclassname = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$this->task['scriptname'], 'class');
			$taskclassname = 'task_'.$this->task['scriptname'];
		}
		$taskclass = new $taskclassname;
		if(method_exists($taskclass, 'csc')) {
			$result = $taskclass->csc($this->task);
		} else {
			showmessage('task_not_found', '', array('taskclassname' => $taskclassname));
		}

		if($result === TRUE) {

			if($this->task['reward'] && C::t('common_mytask')->update_to_success($_G['uid'], $id, $_G['timestamp']) && C::t('common_task')->update_achievers($id, 1)) {
				$rewards = $this->reward();
				$notification = $this->task['reward'];
				if($this->task['reward'] == 'magic') {
					$rewardtext = C::t('common_magic')->fetch($this->task['prize']);
					$rewardtext = $rewardtext['name'];
				} elseif($this->task['reward'] == 'medal') {
					$rewardtext = C::t('forum_medal')->fetch($this->task['prize']);
					$rewardtext = $rewardtext['name'];
					if(!$this->task['bonus']) {
						$notification = 'medal_forever';
					}
				} elseif($this->task['reward'] == 'group') {
					$group = C::t('common_usergroup')->fetch($this->task['prize']);
					$rewardtext = $group['grouptitle'];
				} elseif($this->task['reward'] == 'invite') {
					$rewardtext = $this->task['prize'];
				}
				notification_add($_G[uid], 'task', 'task_reward_'.$notification, array(
					'taskid' => $this->task['taskid'],
					'name' => $this->task['name'],
					'creditbonus' => $_G['setting']['extcredits'][$this->task['prize']]['title'].' '.$this->task['bonus'].' '.$_G['setting']['extcredits'][$this->task['prize']]['unit'],
					'rewardtext' => $rewardtext,
					'bonus' => $this->task['bonus'],
					'prize' => $this->task['prize'],
				));
			}

			if(method_exists($taskclass, 'sufprocess')) {
				$taskclass->sufprocess($this->task);
			}

			if($_G['inajax']) {
				$this->message('100', $this->task['reward'] ? 'task_reward_'.$this->task['reward'] : 'task_completed', array(
						'creditbonus' => $_G['setting']['extcredits'][$this->task['prize']]['title'].' '.$this->task['bonus'].' '.$_G['setting']['extcredits'][$this->task['prize']]['unit'],
						'rewardtext' => $rewardtext,
						'bonus' => $this->task['bonus'],
						'prize' => $this->task['prize']
					)
				);
			} else {
				return true;
			}

		} elseif($result === FALSE) {

			C::t('common_mytask')->update($_G['uid'], $id, array('status' => -1));
			if($_G['inajax']) {
				$this->message('-1', 'task_failed');
			} else {
				return -2;
			}

		} else {

			$result['t'] = $this->timeformat($result['remaintime']);
			$this->messagevalues['values'] = array('csc' => $result['csc'], 't' => $result['t']);
			if($result['csc']) {
				C::t('common_mytask')->update($_G['uid'], $id, array('csc' => $result['csc']."\t".$_G['timestamp']));
				$this->messagevalues['msg'] = $result['t'] ? 'task_doing_rt' : 'task_doing';
			} else {
				$this->messagevalues['msg'] = $result['t'] ? 'task_waiting_rt' : 'task_waiting';
			}
			if($_G['inajax']) {
				$this->message($result['csc'], $this->messagevalues['msg'], $this->messagevalues['values']);
			} else {
				return -3;
			}

		}
	}

	function giveup($id) {
		global $_G;

		if($_GET['formhash'] != FORMHASH) {
			showmessage('undefined_action');
		} elseif(!($this->task = C::t('common_task')->fetch_by_uid($_G['uid'], $id))) {
			showmessage('task_nonexistence');
		} elseif($this->task['status'] != '0') {
			showmessage('task_not_underway');
		}

		C::t('common_mytask')->delete($_G['uid'], $id);
		C::t('common_task')->update_applicants($id, -1);
	}

	function parter($id) {
		$parterlist = array();
		foreach(C::t('common_mytask')->fetch_all_by_taskid($id, 8) as $parter) {
			$parter['avatar'] = avatar($parter['uid'], 'small');
			$csc = explode("\t", $parter['csc']);
			$parter['csc'] = floatval($csc[0]);
			$parterlist[] = $parter;
		}
		return $parterlist;
	}

	function delete($id) {
		global $_G;
		$mytask = C::t('common_mytask')->fetch($_G['uid'], $id);
		$this->task = C::t('common_task')->fetch($id);
		if($this->task['available'] != 2 || empty($mytask) || $mytask['status'] == 1) {
			showmessage('task_nonexistence');
		}

		$escript = explode(':', $this->task['scriptname']);
		if(count($escript) > 1) {
			include_once DISCUZ_ROOT.'./source/plugin/'.$escript[0].'/task/task_'.$escript[1].'.php';
			$taskclassname = 'task_'.$escript[1];
		} else {
			require_once libfile('task/'.$this->task['scriptname'], 'class');
			$taskclassname = 'task_'.$this->task['scriptname'];
		}
		$taskclass = new $taskclassname;
		if(method_exists($taskclass, 'delete')) {
			$taskclass->delete($this->task);
		}

		C::t('common_mytask')->delete($_G['uid'], $id);
		C::t('common_task')->update_applicants($id, -1);
		return true;
	}

	function update_available($update = 0) {
		global $_G;
		$updatetasknext = 0;
		loadcache('tasknext');
		$tasknext = getglobal('cache/tasknext');
		if(!is_array($tasknext)) {
			$tasknext = array();
		}
		if(!isset($tasknext['starttime']) || $tasknext['starttime'] > TIMESTAMP + 86400) {
			$tasknext['starttime'] = 0;
		}
		if(!isset($tasknext['endtime']) || $tasknext['endtime'] > TIMESTAMP + 86400) {
			$tasknext['endtime'] = 0;
		}
		if(TIMESTAMP >= $tasknext['starttime'] || TIMESTAMP >= $tasknext['endtime'] || $update) {
			$processname = 'update_task_available';
			if($update || !discuz_process::islocked($processname, 600)) {
				if(TIMESTAMP >= $tasknext['starttime'] || $update) {
					C::t('common_task')->update_available(2);
					$starttime = C::t('common_task')->fetch_next_starttime();
					$tasknext['starttime'] = $starttime ? min($starttime, TIMESTAMP + 86400) : TIMESTAMP + 86400;
					$updatetasknext = 1;
				}

				if(TIMESTAMP >= $tasknext['endtime'] || $update) {
					C::t('common_task')->update_available(1);
					$endtime = C::t('common_task')->fetch_next_endtime();
					$tasknext['endtime'] = $endtime ? min($endtime, TIMESTAMP + 86400) : TIMESTAMP + 86400;
					$updatetasknext = 1;
				}

				if($updatetasknext) {
					savecache('tasknext', $tasknext);
				}
				discuz_process::unlock($processname);
			}
		}
		return true;
	}

	function reward() {
		switch($this->task['reward']) {
			case 'credit': return $this->reward_credit($this->task['prize'], $this->task['bonus']); break;
			case 'magic': return $this->reward_magic($this->task['prize'], $this->task['bonus']); break;
			case 'medal': return $this->reward_medal($this->task['prize'], $this->task['bonus']); break;
			case 'invite': return $this->reward_invite($this->task['prize'], $this->task['bonus']); break;
			case 'group': return $this->reward_group($this->task['prize'], $this->task['bonus']); break;
		}
	}

	function reward_credit($extcreditid, $credits) {
		global $_G;

		$creditsarray[$extcreditid] = $credits;
		updatemembercount($_G['uid'], $creditsarray, 1, 'TRC', $this->task['taskid']);
	}

	function reward_magic($magicid, $num) {
		global $_G;

		if(C::t('common_member_magic')->count($_G['uid'], $magicid)) {
			C::t('common_member_magic')->increase($_G['uid'], $magicid, array('num' => $num), false, true);
		} else {
			C::t('common_member_magic')->insert(array(
				'uid' => $_G['uid'],
				'magicid' => $magicid,
				'num' => $num
			));
		}
	}

	function reward_medal($medalid, $day) {
		global $_G;

		$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
		$medals = $memberfieldforum['medals'];
		unset($memberfieldforum);
		if(empty($medals) || !in_array($medalid, explode("\t", $medals))) {
			$medalsnew = $medals ? $medals."\t".$medalid : $medalid;
			C::t('common_member_field_forum')->update($_G['uid'], array('medals' => $medalsnew), 'UNBUFFERED');
			C::t('common_member_medal')->insert(array('uid' => $_G['uid'], 'medalid' => $medalid), 0, 1);
			$data = array(
				'uid' => $_G['uid'],
				'medalid' => $medalid,
				'type' => 0,
				'dateline' => TIMESTAMP,
				'expiration' => $day ? TIMESTAMP + $day * 86400 : '',
				'status' => 1,
			);
			C::t('forum_medallog')->insert($data);
		}
	}

	function reward_invite($num, $day) {
		global $_G;
		$day = empty($day) ? 5 : $day;
		$expiration = $_G['timestamp'] + $day * 86400;
		$codes = array();
		for ($i=0; $i < $num; $i++) {
			$code = strtolower(random(6));
			$codes[] = "('$_G[uid]', '$code', '$_G[timestamp]', '$expiration', '$_G[clientip]')";
			$invitedata = array(
					'uid' => $_G['uid'],
					'code' => $code,
					'dateline' => $_G['timestamp'],
					'endtime' => $expiration,
					'inviteip' => $_G['clientip']
			);
			C::t('common_invite')->insert($invitedata);
		}

	}

	function reward_group($gid, $day = 0) {
		global $_G;

		$exists = FALSE;
		if($_G['member']['extgroupids']) {
			$_G['member']['extgroupids'] = explode("\t", $_G['member']['extgroupids']);
			if(in_array($gid, $_G['member']['extgroupids'])) {
				$exists = TRUE;
			} else {
				$_G['member']['extgroupids'][] = $gid;
			}
			$_G['member']['extgroupids'] = implode("\t", $_G['member']['extgroupids']);
		} else {
			$_G['member']['extgroupids'] = $gid;
		}

		C::t('common_member')->update($_G['uid'], array('extgroupids' => $_G['member']['extgroupids']), 'UNBUFFERED');

		if($day) {
			$memberfieldforum = C::t('common_member_field_forum')->fetch($_G['uid']);
			$groupterms = !empty($memberfieldforum['groupterms']) ? dunserialize($memberfieldforum['groupterms']) : array();
			unset($memberfieldforum);
			$groupterms['ext'][$gid] = $exists && $groupterms['ext'][$gid] ? max($groupterms['ext'][$gid], TIMESTAMP + $day * 86400) : TIMESTAMP + $day * 86400;
			C::t('common_member_field_forum')->update($_G['uid'], array('groupterms' => serialize($groupterms)), 'UNBUFFERED');

		}
	}

	function message($csc, $msg, $values = array()) {
		include template('common/header_ajax');
		$msg = lang('message', $msg, $values);
		echo "$csc|$msg";
		include template('common/footer_ajax');
		exit;
	}

	function timeformat($t) {
		global $_G;

		if($t) {
			$h = floor($t / 3600);
			$m = floor(($t - $h * 3600) / 60);
			$s = floor($t - $h * 3600 - $m * 60);
			return ($h ? "$h{$_G['setting']['dlang'][date][4]}" : '').($m ? "$m{$_G[setting][dlang][date][6]}" : '').($h || !$s ? '' : "$s{$_G[setting][dlang][date][7]}");
		}
		return '';
	}

}

function tasktimeformat($t) {
	global $_G;

	if($t) {
		$h = floor($t / 3600);
		$m = floor(($t - $h * 3600) / 60);
		$s = floor($t - $h * 3600 - $m * 60);
		return ($h ? "$h{$_G['lang']['core']['date']['hour']}" : '').($m ? "$m{$_G['lang']['core']['date']['min']}" : '').($h || !$s ? '' : "$s{$_G['lang']['core']['date']['sec']}");
	}
	return '';
}
?>