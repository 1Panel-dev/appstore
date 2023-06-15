<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_report.php 29666 2012-04-24 08:07:56Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}
if(!empty($_G['fid'])) {
	$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
	$report_reward = unserialize($_G['setting']['report_reward']);
	if(submitcheck('reportsubmit')) {
		if($_GET['reportids']) {
			foreach($_GET['reportids'] as $reportid) {
				if(C::t('common_report')->fetch_count(0, $reportid)) {
					$creditchange = '';
					$uid = $_GET['reportuids'][$reportid];
					if($uid != $_G['uid']) {
						$msg = !empty($_GET['msg'][$reportid]) ? '<br />'.dhtmlspecialchars($_GET['msg'][$reportid]) : '';
						if(!empty($_GET['creditsvalue'][$reportid])) {
							$credittag = $_GET['creditsvalue'][$reportid] > 0 ? '+' : '';
							if($report_reward['max'] < $_GET['creditsvalue'][$reportid] || $_GET['creditsvalue'][$reportid] < $report_reward['min']) {
								showmessage('quickclear_noperm', "$cpscript?mod=modcp&action=report&fid=$_G[fid]");
							}
							$creditchange = '<br />'.lang('forum/misc', 'report_msg_your').$_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_GET['creditsvalue'][$reportid];
							updatemembercount($uid, array($curcredits => intval($_GET['creditsvalue'][$reportid])), true, 'RPC', $reportid);
						}
						if($creditchange || $msg) {
							notification_add($uid, 'report', 'report_change_credits', array('creditchange' => $creditchange, 'msg' => $msg), 1);
						}
					}
					$opresult = !empty($_GET['creditsvalue'][$reportid])? $curcredits."\t".intval($_GET['creditsvalue'][$reportid]) : 'ignore';
					C::t('common_report')->update($reportid, array('opuid' => $_G['uid'], 'opname' => $_G['username'], 'optime' => TIMESTAMP, 'opresult' => $opresult));
				}
			}
			showmessage('modcp_report_success', "$cpscript?mod=modcp&action=report&fid=$_G[fid]&lpp=$lpp");
		}
	}
	$rewardlist = '';
	$offset = abs(ceil(($report_reward['max'] - $report_reward['min']) / 10));
	if($report_reward['max'] > $report_reward['min']) {
		for($vote = $report_reward['max']; $vote >= $report_reward['min']; $vote -= $offset) {
			if($vote != 0) {
				$rewardlist .= $vote ? '<option value="'.$vote.'">'.($vote > 0 ? '+'.$vote : $vote).'</option>' : '';
			} else {
				$rewardlist .= '<option value="0" selected>'.lang('forum/misc', 'report_noreward').'</option>';
			}
		}
	}
	$reportlist = array();
	$lpp = empty($_GET['lpp']) ? 20 : intval($_GET['lpp']);
	$lpp = min(200, max(5, $lpp));
	$page = max(1, intval($_G['page']));
	$start = ($page - 1) * $lpp;

	$reportcount = C::t('common_report')->fetch_count(0, 0, $_G['fid']);
	$query = C::t('common_report')->fetch_all($start, $lpp, 0, $_G['fid']);
	foreach($query as $row) {
		$row['dateline'] = dgmdate($row['dateline']);
		$reportlist[] = $row;
	}
	$multipage = multi($reportcount, $lpp, $page, "$cpscript?mod=modcp&action=report&fid=$_G[fid]&lpp=$lpp");
}
?>