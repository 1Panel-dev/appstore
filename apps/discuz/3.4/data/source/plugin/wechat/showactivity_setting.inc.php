<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: showactivity_setting.inc.php 35147 2014-12-11 06:21:50Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$setting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$setting = (array)unserialize($setting['mobilewechat']);
$ac = !empty($_GET['ac']) ? $_GET['ac'] : '';

require_once libfile('function/forumlist');
loadcache('forums');

require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

if(!$setting['wsq_sitetoken']) {
	cpmsg(lang('plugin/wechat', 'wsq_api_register_close'), '', 'error');
}

if(!$ac) {

	$ppp = 20;
	if(!is_array($setting['showactivity']['tids'])) {
		$setting['showactivity']['tids'] = array();
	}
	arsort($setting['showactivity']['tids']);
	$page = max(1, $_GET['page']);
	$tids = array_slice($setting['showactivity']['tids'], ($page - 1) * $ppp, $ppp);
	$multipage = multi(count($setting['showactivity']['tids']), $ppp, $page, ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting', 0, 3, TRUE, TRUE);

	$showthreads = C::t('forum_thread')->fetch_all($tids);
	$activities = C::t('forum_activity')->fetch_all(array_keys($showthreads));

	arsort($showthreads);
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting&ac=del', 'enctype');
	showtableheader();
	echo '<tr class="header"><th></th><th>'.lang('plugin/wechat', 'show_title').'</th><th>'.
		lang('plugin/wechat', 'show_starttime').' - '.lang('plugin/wechat', 'show_endtime').'</th><th>'.
		lang('plugin/wechat', 'show_expiration').'</th><th>'.
		lang('plugin/wechat', 'show_applynumber').'</th><th>'.
		lang('plugin/wechat', 'show_forum').'</th><th></th></tr>';
	foreach($showthreads as $tid => $thread) {
		$settingsnew[$tid] = $tid;
		echo '<tr class="hover"><th class="td25"><input class="checkbox" type="checkbox" name="delete['.$thread['tid'].']" value="'.$thread['tid'].'"></th><th><a href="forum.php?mod=viewthread&tid='.$thread['tid'].'" target="_blank">'.$thread['subject'].'</a></th><th>'.
			dgmdate($activities[$thread['tid']]['starttimefrom']).($activities[$thread['tid']]['starttimeto'] ? ' - '.dgmdate($activities[$thread['tid']]['starttimeto']) : '').'</th><th>'.
			dgmdate($activities[$thread['tid']]['expiration']).'</th><th>'.
			$activities[$thread['tid']]['applynumber'].'</th><th>'.
			$_G['cache']['forums'][$thread['fid']]['name'].'</th><th>'.
			'<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting&ac=export&tid='.$thread['tid'].'">'.lang('plugin/wechat', 'show_export').'</a></th></tr>';
	}
	$add = '<input type="button" class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting&ac=add\'" value="'.lang('plugin/wechat', 'show_addthread').'" />';
	if($showthreads) {
		showsubmit('submit', lang('plugin/wechat', 'show_delthread'), $add, '', $multipage);
	} else {
		showsubmit('', '', 'td', $add);
	}
	showtablefooter();
	showformfooter();

} elseif($ac == 'del') {

	if(submitcheck('submit')) {
		foreach($_GET['delete'] as $delete) {
			unset($setting['showactivity']['tids'][$delete]);
			C::t('forum_thread')->delete($delete);
		}
		$settings = array('mobilewechat' => serialize($setting));
		C::t('common_setting')->update_batch($settings);
		updatecache(array('plugin', 'setting'));
		cpmsg(lang('plugin/wechat', 'show_delthread_succeed'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting', 'succeed');
	}

} elseif($ac == 'add') {
	if(!submitcheck('submit')) {

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		$forumselect = "<select name=\"fid\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select')."</option><option value=\"\">&nbsp;</option>".str_replace('%', '%%', forumselect(FALSE, 0, 0, TRUE)).'</select>';

		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting&ac=add', 'enctype');
		showtableheader();
		showsetting(lang('plugin/wechat', 'show_title'), 'subject', '', 'text');
		showsetting(lang('plugin/wechat', 'show_starttime'), 'starttimefrom', '', 'calendar', '', 0, '', 1);
		showsetting(lang('plugin/wechat', 'show_expiration'), 'activityexpiration', '', 'calendar', '', 0, '', 1);
		showsetting(lang('plugin/wechat', 'show_endtime'), 'starttimeto', '', 'calendar', '', 0, '', 1);
		showsetting(lang('plugin/wechat', 'show_memo'), 'message', '', 'textarea');
		showsetting(lang('plugin/wechat', 'show_forum'), '', '', $forumselect);
		showsubmit('submit');
		showtablefooter();
		showformfooter();

	} else {

		$_GET['activityclass'] = lang('plugin/wechat', 'show_thread_class');
		$_GET['activityplace'] = lang('plugin/wechat', 'show_thread_place');
		if(!$_GET['subject'] || !$_GET['starttimefrom'] || !$_GET['activityexpiration'] || !$_GET['message'] || !$_GET['fid']) {
			cpmsg(lang('plugin/wechat', 'show_input_error'), '', 'error');
		}

		if(@strtotime($_GET['starttimefrom']) === -1 || @strtotime($_GET['starttimefrom']) === FALSE) {
			cpmsg(lang('message', 'activity_fromtime_error'), '', 'error');
		} elseif(trim($_GET['activityexpiration']) && (@strtotime($_GET['activityexpiration']) === -1 || @strtotime($_GET['activityexpiration']) === FALSE)) {
			cpmsg(lang('message', 'activity_totime_error'), '', 'error');
		}

		$activity = array();
		$activity['class'] = $_GET['activityclass'];
		$activity['starttimefrom'] = @strtotime($_GET['starttimefrom']);
		$activity['starttimeto'] = $_GET['starttimeto'] ? @strtotime($_GET['starttimeto']) : 0;
		$activity['place'] = $_GET['activityplace'];
		$activity['expiration'] = @strtotime($_GET['activityexpiration']);

		$newthread = array(
			'fid' => $_GET['fid'],
			'posttableid' => 0,
			'readperm' => 0,
			'price' => 0,
			'typeid' => 0,
			'sortid' => 0,
			'author' => $_G['username'],
			'authorid' => $_G['uid'],
			'subject' => $_GET['subject'],
			'dateline' => TIMESTAMP,
			'lastpost' => TIMESTAMP,
			'lastposter' => $_G['username'],
			'displayorder' => 1,
			'digest' => 0,
			'special' => 4,
			'attachment' => 0,
			'moderated' => 0,
			'status' => 0,
			'isgroup' => 0,
			'replycredit' => 0,
			'closed' => 0,
		);
		$tid = C::t('forum_thread')->insert($newthread, true);

		$pid = insertpost(array(
			'fid' => $_GET['fid'],
			'tid' => $tid,
			'first' => '1',
			'author' => $_G['username'],
			'authorid' => $_G['uid'],
			'subject' => $_GET['subject'],
			'dateline' => TIMESTAMP,
			'message' => $_GET['message'],
			'useip' => '',
			'invisible' => 0,
			'anonymous' => 0,
			'usesig' => 0,
			'htmlon' => 0,
			'bbcodeoff' => 0,
			'smileyoff' => 0,
			'parseurloff' => 0,
			'attachment' => '0',
			'tags' => '',
			'replycredit' => 0,
			'status' => 0
		));

		$data = array(
			'tid' => $tid,
			'uid' => $_G['uid'],
			'cost' => 0,
			'starttimefrom' => $activity['starttimefrom'],
			'starttimeto' => $activity['starttimeto'],
			'place' => $activity['place'],
			'class' => $activity['class'],
			'expiration' => $activity['expiration']
		);
		C::t('forum_activity')->insert($data);

		$setting['showactivity']['tids'][$tid] = $tid;
		$settings = array('mobilewechat' => serialize($setting));
		C::t('common_setting')->update_batch($settings);
		updatecache(array('plugin', 'setting'));
		require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
		wsq::report('pubshowactivity');

		cpmsg(lang('plugin/wechat', 'show_addthread_succeed'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=showactivity_setting', 'succeed');

	}
} elseif($ac == 'export') {
	if(!isset($setting['showactivity']['tids'][$_GET['tid']])) {
		cpmsg(lang('plugin/wechat', 'show_thread_not_found'));
	}
	$thread = get_thread_by_tid($_GET['tid']);
	if(!$thread) {
		cpmsg(lang('plugin/wechat', 'show_thread_not_found'));
	}
	$posttableid = $thread['posttableid'];
	$posts = DB::fetch_all("SELECT * FROM %t WHERE tid=%d", array('forum_debatepost', $_GET['tid']), 'pid');
	foreach(C::t('forum_post')->fetch_all($posttableid, array_keys($posts), false) as $post) {
		$array[$posts[$post['pid']]['voters'].'.'.$post['position']] = $post['author'].','.$posts[$post['pid']]['voters'].','.$post['position'];
	}
	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=showactivity_'.$_GET['tid'].'.csv');
	header('Pragma: no-cache');
	header('Expires: 0');
	krsort($array);
	$detail = lang('plugin/wechat', 'show_export_title')."\r\n".implode("\r\n", $array);
	if($_G['charset'] != 'gbk') {
		$detail = diconv($detail, $_G['charset'], 'GBK');
	}
	define('FOOTERDISABLED', true);
	echo $detail;
	exit();
}

?>