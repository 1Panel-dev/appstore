<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit_log.php 31381 2012-08-21 07:56:35Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$perpage = 20;
$start = ($page-1)*$perpage;

$gets = array(
	'mod' => 'spacecp',
	'op' => $_GET['op'],
	'ac' => 'credit',
	'suboperation' => $_GET['suboperation'],
	'exttype' => $_GET['exttype'],
	'income' => $_GET['income'],
	'starttime' => $_GET['starttime'],
	'endtime' => $_GET['endtime'],
	'optype' => $_GET['optype']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$_GET['income'] = intval($_GET['income']);
$incomeactives = array($_GET['income'] => ' selected="selected"');
$optypes = lang('spacecp', 'logs_credit_update_INDEX');
$endunixstr = $beginunixstr = 0;
if($_GET['starttime']) {
	$beginunixstr = strtotime($_GET['starttime']);
	$_GET['starttime'] = dgmdate($beginunixstr, 'Y-m-d');
}
if($_GET['endtime']) {
	$endunixstr = strtotime($_GET['endtime'].' 23:59:59');
	$_GET['endtime'] = dgmdate($endunixstr, 'Y-m-d');
}
if($beginunixstr && $endunixstr && $endunixstr < $beginunixstr) {
	showmessage('start_time_is_greater_than_end_time');
}

if($_GET['suboperation'] == 'creditrulelog') {

	$count = C::t('common_credit_rule_log')->count_by_uid($_G['uid']);
	if($count) {
		$rulelogs = C::t('common_credit_rule_log')->fetch_all_by_uid($_G['uid'], $start, $perpage);
		$rules = C::t('common_credit_rule')->fetch_all_by_rid(C::t('common_credit_rule_log')->get_rids());
		foreach($rulelogs as $value) {
			$value['rulename'] = $rules[$value['rid']]['rulename'];
			$list[] = $value;
		}
	}

} else {

	loadcache('usergroups');
	$suboperation = 'creditslog';
	$optype = '';
	if($_GET['optype'] && in_array($_GET['optype'], $optypes)) {
		$optype = $_GET['optype'];
	}
	$exttype = intval($_GET['exttype']);

	$income = intval($_GET['income']);
	$count = C::t('common_credit_log')->count_by_search($_G['uid'], $optype, $beginunixstr, $endunixstr, $exttype, $income, $_G['setting']['extcredits']);
	if($count) {
		$aids = $pids = $tids = $taskids = $uids = $loglist = array();
		loadcache(array('magics'));
		foreach(C::t('common_credit_log')->fetch_all_by_search($_G['uid'], $optype, $beginunixstr, $endunixstr, $exttype, $income, $_G['setting']['extcredits'], $start,$perpage) as $log) {
			$credits = array();
			$havecredit = false;
			$maxid = $minid = 0;
			foreach($_G['setting']['extcredits'] as $id => $credit) {
				if($log['extcredits'.$id]) {
					$havecredit = true;
					if($log['operation'] == 'RPZ') {
						$credits[] = $credit['title'].lang('spacecp', 'credit_update_reward_clean');
					} else {
						$credits[] = $credit['title'].' <span class="'.($log['extcredits'.$id] > 0 ? 'xi1' : 'xg1').'">'.($log['extcredits'.$id] > 0 ? '+' : '').$log['extcredits'.$id].'</span>';
					}
					if($log['operation'] == 'CEC' && !empty($log['extcredits'.$id])) {
						if($log['extcredits'.$id] > 0) {
							$log['maxid'] = $id;
						} elseif($log['extcredits'.$id] < 0) {
							$log['minid'] = $id;
						}
					}

				}
			}
			if(!$havecredit) {
				continue;
			}
			$log['credit'] = implode('<br/>', $credits);
			if(in_array($log['operation'], array('RTC', 'RAC', 'STC', 'BTC', 'ACC', 'RCT', 'RCA', 'RCB'))) {
				$tids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('SAC', 'BAC'))) {
				$aids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('PRC', 'RSC'))) {
				$pids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('TFR', 'RCV'))) {
				$uids[$log['relatedid']] = $log['relatedid'];
			} elseif($log['operation'] == 'TRC') {
				$taskids[$log['relatedid']] = $log['relatedid'];
			}
			$loglist[] = $log;
		}
		$otherinfo = getotherinfo($aids, $pids, $tids, $taskids, $uids);
	}


}

if($count) {
	$multi = multi($count, $perpage, $page, $theurl);
}

$optypehtml = '<select id="optype" name="optype" class="ps" width="168">';
$optypehtml .= '<option value="">'.lang('spacecp', 'logs_select_operation').'</option>';
foreach($optypes as $type) {
	$optypehtml .= '<option value="'.$type.'"'.($type == $_GET['optype'] ? ' selected="selected"' : '').'>'.lang('spacecp', 'logs_credit_update_'.$type).'</option>';
}
$optypehtml .= '</select>';
include template('home/spacecp_credit_log');
?>