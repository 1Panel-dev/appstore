<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_forum.php 32563 2013-02-21 03:38:50Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$forumupdate = $listupdate = false;

$op = !in_array($op , array('editforum', 'recommend')) ? 'editforum' : $op;

if(empty($_G['fid'])) {
	if(!empty($_G['cookie']['modcpfid'])) {
		$fid = $_G['cookie']['modcpfid'];
	} else {
		list($fid) = array_keys($modforums['list']);
	}
	dheader("Location: {$cpscript}?mod=modcp&action=$_GET[action]&op=$op&fid=$fid");
}

if($_G['fid'] && $_G['forum']['ismoderator']) {

	if($op == 'editforum') {

		require_once libfile('function/editor');

		$alloweditrules = $_G['adminid'] == 1 || $_G['forum']['alloweditrules'] ? true : false;

		if(!submitcheck('editsubmit')) {
			$_G['forum']['rules'] = html2bbcode($_G['forum']['rules']);
		} else {

			require_once libfile('function/discuzcode');
			$forumupdate = true;
			$rulesnew = $alloweditrules ? preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode($_GET['rulesnew'], 1, 0, 0, 0, 1, 1, 0, 0, 1)) : $_G['forum']['rules'];
			C::t('forum_forumfield')->update($_G['fid'], array('rules' => $rulesnew));

			$_G['forum']['description'] = html2bbcode($descnew);
			$_G['forum']['rules'] = html2bbcode($rulesnew);

		}

	} elseif($op == 'recommend') {

		$useradd = 0;

		if($_G['adminid'] == 3) {
			$useradd = $_G['uid'];
		}
		$ordernew = !empty($_GET['ordernew']) && is_array($_GET['ordernew']) ? $_GET['ordernew'] : array();

		if(submitcheck('editsubmit') && $_G['forum']['modrecommend']['sort'] != 1) {
			$threads = array();
			foreach($_GET['order'] as $id => $position) {
				$threads[$id]['order'] = $position;
			}
			foreach($_GET['subject'] as $id => $title) {
				$threads[$id]['subject'] = $title;
			}
			foreach($_GET['expirationrecommend'] as $id => $expiration) {
				$expiration = trim($expiration);
				if(!empty($expiration)) {
					if(!preg_match('/^\d{4}-\d{1,2}-\d{1,2} +\d{1,2}:\d{1,2}$/', $expiration)) {
						showmessage('recommend_expiration_invalid');
					}
					list($expiration_date, $expiration_time) = explode(' ', $expiration);
					list($expiration_year, $expiration_month, $expiration_day) = explode('-', $expiration_date);
					list($expiration_hour, $expiration_min) = explode(':', $expiration_time);
					$expiration_sec = 0;

					$expiration_timestamp = mktime($expiration_hour, $expiration_min, $expiration_sec, $expiration_month, $expiration_day, $expiration_year);
				} else {
					$expiration_timestamp = 0;
				}
				$threads[$id]['expiration'] = $expiration_timestamp;
			}
			if($_GET['delete']) {
				$listupdate = true;
				C::t('forum_forumrecommend')->delete($_GET['delete']);
			}
			if(!empty($_GET['delete']) && is_array($_GET['delete'])) {
				foreach($_GET['delete'] as $id) {
					$threads[$id]['delete'] = true;
					unset($threads[$id]);
				}
			}
			foreach($threads as $id => $item) {
				$item['displayorder'] = intval($item['order']);
				$item['subject'] = dhtmlspecialchars($item['subject']);
				C::t('forum_forumrecommend')->update($id, array(
					'subject' => $item['subject'],
					'displayorder' => $item['displayorder'],
					'expiration' => $item['expiration']
				));
			}
			$listupdate = true;
		}

		$page = max(1, intval($_G['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$threadcount = C::t('forum_forumrecommend')->count_by_fid($_G['fid']);
		$multipage = multi($threadcount, $_G['tpp'], $page, "$cpscript?action=$_GET[action]&fid=$_G[fid]&page=$page");

		$threadlist = $moderatormembers = array();
		$moderatorids = array();
		foreach(C::t('forum_forumrecommend')->fetch_all_by_fid($_G['fid'], false, $useradd, $start_limit, $_G['tpp']) as $thread) {
			if($thread['moderatorid']) {
				$moderatorids[$thread['moderatorid']] = $thread['moderatorid'];
			}
			$thread['authorlink'] = $thread['authorid'] ? "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>" : 'Guest';
			$thread['expiration'] = $thread['expiration'] ? dgmdate($thread['expiration']) : '';
			$threadlist[] = $thread;
		}
		if($moderatorids) {
			$moderatormembers = C::t('common_member')->fetch_all($moderatorids, false, 0);
		}

	}
}

?>