<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_membersplit.php 29851 2012-05-02 02:18:40Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();


if(!$operation) {
	$operation = 'check';
}
loadcache(array('membersplitdata', 'userstats'));
if(!empty($_G['cache']['membersplitstep'])) {
	cpmsg('membersplit_split_in_backstage', 'action=membersplit&operation=check', 'loadingform');
}

if($operation == 'check') {
	shownav('founder', 'nav_membersplit');
	showsubmenu('membersplit');
	showtips('membersplit_check_tips');
	showformheader('membersplit&operation=manage');
	showtableheader('membersplit_table_orig');
	$membercount = $_G['cache']['userstats']['totalmembers'];

	showsubtitle(array('','','membersplit_count', 'membersplit_lasttime_check'));


	if($membercount < 20000) {
		$color = 'green';
		$msg = $lang['membersplit_without_optimization'];
	} else {
		$color = empty($_G['cache']['membersplitdata']) || $_G['cache']['membersplitdata']['dateline'] < TIMESTAMP - 86400*10 ?
			'red' : 'green';
		$msg = empty($_G['cache']['membersplitdata']) ? $lang['membersplit_has_no_check'] : dgmdate($_G['cache']['membersplitdata']['dateline']);
	}
	showtablerow('', '', array('','', number_format($membercount), '<span style="color:'.$color.'">'.$msg.'</span>'));

	if($membercount >= 20000) {
		showsubmit('membersplit_check_submit', 'membersplit_check');
	}
	showtablefooter();
	showformfooter();

} else if($operation == 'manage') {
	shownav('founder', 'nav_membersplit');
	if(!submitcheck('membersplit_split_submit', 1)) {
		showsubmenu('membersplit');
		showtips('membersplit_tips');
		showformheader('membersplit&operation=manage');
		showtableheader('membersplit_table_orig');

		if($_G['cache']['membersplitdata'] && $_G['cache']['membersplitdata']['dateline'] > TIMESTAMP - 86400) {
			$zombiecount = $_G['cache']['membersplitdata']['zombiecount'];
		} else {
			$zombiecount = C::t('common_member')->count_zombie();
			savecache('membersplitdata', array('zombiecount' => $zombiecount, 'dateline' => TIMESTAMP));
		}
		$membercount = $_G['cache']['userstats']['totalmembers'];
		$percentage = round($zombiecount/$membercount, 4)*100;

		showsubtitle(array('','','membersplit_count', 'membersplit_combie_count', 'membersplit_splitnum'));
		$color = $percentage > 0 ? 'red' : 'green';
		if($percentage == 0) {
			$msg = $lang['membersplit_message0'];
		} else if($percentage < 10) {
			$msg = $lang['membersplit_message10'];
		} else {
			$msg = $lang['membersplit_message100'];
		}
		showtablerow('', '',
				array('','', number_format($membercount), '<span style="color:'.$color.'">'.number_format($zombiecount).'('.$percentage.'%) '.$msg.'</span>', '<input name="splitnum" value="200" type="text" class="txt"/>'));

		if($percentage > 0) {
			showsubmit('membersplit_split_submit', 'membersplit_archive');
		}
		showtablefooter();
		showformfooter();

	} else {
		$step = intval($_GET['step'])+1;
		$splitnum = max(10, intval($_GET['splitnum']));
		if(!$_GET['nocheck'] && $step == 1 && !C::t('common_member_archive')->check_table()) {
			cpmsg('membersplit_split_check_table', 'action=membersplit&operation=rebuildtable&splitnum='.$splitnum, 'loadingform', array());
			cpmsg('', 'action=membersplit&operation=manage', 'error');
		}
		if(!C::t('common_member')->split($splitnum)) {
			cpmsg('membersplit_split_succeed', 'action=membersplit&operation=manage', 'succeed');
		}
		cpmsg('membersplit_split_doing', 'action=membersplit&operation=manage&membersplit_split_submit=1&step='.$step.'&splitnum='.$splitnum, 'loadingform', array('num' => $step*$splitnum));
	}
} else if($operation == 'rebuildtable') {
	$step = intval($_GET['step']);
	$splitnum = max(10, intval($_GET['splitnum']));
	$ret = C::t('common_member_archive')->rebuild_table($step);
	if($ret === false) {
		cpmsg('membersplit_split_check_table_done', 'action=membersplit&operation=manage&membersplit_split_submit=1&nocheck=1&splitnum='.$splitnum, 'loadingform');
	} else if($ret === true) {
		cpmsg('membersplit_split_checking_table', 'action=membersplit&operation=rebuildtable&splitnum='.$splitnum.'&step='.($step+1), 'loadingform', array('step' => $step+1));
	} else {
		cpmsg('membersplit_split_check_table_fail', 'action=membersplit&operation=manage&splitnum='.$splitnum, 'error', array('tablename' => $ret));
	}
}

?>