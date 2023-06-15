<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_announce.php 33271 2013-05-13 08:16:21Z kamichen $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(empty($operation)) {

	if(!submitcheck('announcesubmit')) {

		shownav('extended', 'announce', 'admin');
		showsubmenu('announce', array(
			array('admin', 'announce', 1),
			array('add', 'announce&operation=add', 0)
		));
		showtips('announce_tips');
		showformheader('announce');
		showtableheader();
		showsubtitle(array('del', 'display_order', 'author', 'subject', 'message', 'announce_type', 'start_time', 'end_time', ''));

		$announce_type = array(0=>$lang['announce_words'], 1=>$lang['announce_url']);
		$annlist = C::t('forum_announcement')->fetch_all_by_displayorder();
		foreach ($annlist as $announce) {
			$disabled = $_G['adminid'] != 1 && $announce['author'] != $_G['member']['username'] ? 'disabled' : NULL;
			$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'Y-n-j H:i') : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'Y-n-j H:i') : $lang['unlimited'];
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[{$announce[id]}]\" value=\"$announce[displayorder]\" size=\"2\" $disabled>",
				"<a href=\"./home.php?mod=space&username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a>",
				$announce['subject'],
				cutstr(strip_tags($announce['message']), 20),
				$announce_type[$announce['type']],
				$announce['starttime'],
				$announce['endtime'],
				"<a href=\"".ADMINSCRIPT."?action=announce&operation=edit&announceid=$announce[id]\" $disabled>$lang[edit]</a>"
			));
		}
		showsubmit('announcesubmit', 'submit', 'select_all');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_GET['delete'])) {
			C::t('forum_announcement')->delete_by_id_username($_GET['delete'], $_G['username'], $_G['adminid']);
		}

		if(is_array($_GET['displayordernew'])) {
			foreach($_GET['displayordernew'] as $id => $displayorder) {
				C::t('forum_announcement')->update_displayorder_by_id_username($id, $displayorder, $_G['username'], $_G['adminid']);
			}
		}

		updatecache(array('announcements', 'announcements_forum'));
		cpmsg('announce_update_succeed', 'action=announce', 'succeed');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		$newstarttime = dgmdate(TIMESTAMP, 'Y-n-j H:i');
		$newendtime = dgmdate(TIMESTAMP + 86400* 7, 'Y-n-j H:i');

		shownav('extended', 'announce', 'add');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 1)
		));
		showformheader('announce&operation=add');
		showtableheader('announce_add');
		showsetting($lang[subject], 'newsubject', '', 'htmltext');
		showsetting($lang['start_time'], 'newstarttime', $newstarttime, 'calendar', '', 0, '', 1);
		showsetting($lang['end_time'], 'newendtime', $newendtime, 'calendar', '', 0, '', 1);
		showsetting('announce_type', array('newtype', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url']))), 0, 'mradio');
		showsetting('announce_message', 'newmessage', '', 'textarea');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newstarttime = $_GET['newstarttime'] ? strtotime($_GET['newstarttime']) : 0;
		$newendtime = $_GET['newendtime'] ? strtotime($_GET['newendtime']) : 0;
		if($newendtime && $newstarttime > $newendtime) {
			cpmsg('announce_time_invalid', '', 'error');
		}
		$newsubject = trim($_GET['newsubject']);
		$newmessage = trim($_GET['newmessage']);
		if(!$newstarttime) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!$newsubject || !$newmessage) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$newmessage = $_GET['newtype'] == 1 ? explode("\n", $_GET['newmessage']) : array(0 => $_GET['newmessage']);
			$data = array(
				'author' => $_G['username'],
				'subject' => strip_tags($newsubject, '<u><i><b><font>'),
				'type' => $_GET['newtype'],
				'starttime' => $newstarttime,
				'endtime' => $newendtime,
				'message' => $newmessage[0],
			);
			C::t('forum_announcement')->insert($data);
			updatecache(array('announcements', 'announcements_forum'));
			cpmsg('announce_succeed', 'action=announce', 'succeed');
		}

	}

} elseif($operation == 'edit' && $_GET['announceid']) {

	$announce = C::t('forum_announcement')->fetch_by_id_username($_GET['announceid'], $_G['username'], $_G['adminid']);
	if(!$announce) {
		cpmsg('announce_nonexistence', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'Y-n-j H:i') : "";
		$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'Y-n-j H:i') : "";
		$b = $i = $u = $colorselect = $colorcheck = '';
		if(preg_match('/<b>(.*?)<\/b>/i', $announce['subject'])) {
			$b = 'class="a"';
		}
		if(preg_match('/<i>(.*?)<\/i>/i', $announce['subject'])) {
			$i = 'class="a"';
		}
		if(preg_match('/<u>(.*?)<\/u>/i', $announce['subject'])) {
			$u = 'class="a"';
		}
		$colorselect = preg_replace('/<font color=(.*?)>(.*?)<\/font>/i', '$1', $announce['subject']);
		$colorselect = strip_tags($colorselect);
		$_G['forum_colorarray'] = array(1=>'#EE1B2E', 2=>'#EE5023', 3=>'#996600', 4=>'#3C9D40', 5=>'#2897C5', 6=>'#2B65B7', 7=>'#8F2A90', 8=>'#EC1282');
		if(in_array($colorselect, $_G['forum_colorarray'])) {
			$colorcheck = "style=\"background: $colorselect\"";
		}

		shownav('extended', 'announce');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 0)
		));
		showformheader("announce&operation=edit&announceid={$_GET['announceid']}");
		showtableheader();
		showtitle('announce_edit');
		showsetting($lang['subject'], 'newsubject', $announce[subject], 'htmltext');
		showsetting('start_time', 'starttimenew', $announce['starttime'], 'calendar', '', 0, '', 1);
		showsetting('end_time', 'endtimenew', $announce['endtime'], 'calendar', '', 0, '', 1);
		showsetting('announce_type', array('typenew', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url'])
		)), $announce['type'], 'mradio');
		showsetting('announce_message', 'messagenew', $announce['message'], 'textarea');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(strpos($_GET['starttimenew'], '-')) {
			$starttimenew = strtotime($_GET['starttimenew']);
		} else {
			$starttimenew = 0;
		}
		if(strpos($_GET['endtimenew'], '-')) {
			$endtimenew = strtotime($_GET['endtimenew']);
		} else {
			$endtimenew = 0;
		}
		$subjectnew = trim($_GET['newsubject']);
		$messagenew = trim($_GET['messagenew']);
		if(!$starttimenew || ($endtimenew && $endtimenew <= TIMESTAMP) || $endtimenew && $starttimenew > $endtimenew) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!$subjectnew || !$messagenew) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$messagenew = $_GET['typenew'] == 1 ? explode("\n", $messagenew) : array(0 => $messagenew);
			C::t('forum_announcement')->update_by_id_username($_GET['announceid'], array(
				'subject' => strip_tags($subjectnew, '<u><i><b><font>'),
				'type' => $_GET['typenew'],
				'starttime' => $starttimenew,
				'endtime' => $endtimenew,
				'message' => $messagenew[0],
			), $_G['username'], $_G['adminid']);

			updatecache(array('announcements', 'announcements_forum'));
			cpmsg('announce_succeed', 'action=announce', 'succeed');
		}
	}

}
echo '<script type="text/javascript" src="static/js/calendar.js"></script>';

?>