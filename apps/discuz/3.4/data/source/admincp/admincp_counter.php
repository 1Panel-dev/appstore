<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_counter.php 33048 2013-04-12 08:50:27Z zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$pertask = isset($_GET['pertask']) ? intval($_GET['pertask']) : 100;
$current = isset($_GET['current']) && $_GET['current'] > 0 ? intval($_GET['current']) : 0;
$next = $current + $pertask;

if(submitcheck('forumsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&forumsubmit=yes";
	$processed = 0;

	$queryf = C::t('forum_forum')->fetch_all_fids(1, '', '', $current, $pertask);
	foreach($queryf as $forum) {
		$processed = 1;
		$threads = $posts = 0;
		$threadtables = array('0');
		$archive = 0;
		foreach(C::t('forum_forum_threadtable')->fetch_all_by_fid($forum['fid']) as $data) {
			if($data['threadtableid']) {
				$threadtables[] = $data['threadtableid'];
			}
		}
		$threadtables = array_unique($threadtables);
		foreach($threadtables as $tableid) {
			$data = C::t('forum_thread')->count_posts_by_fid($forum['fid'], $tableid);
			$threads += $data['threads'];
			$posts += $data['posts'];
			if($data['threads'] == 0 && $tableid != 0) {
				C::t('forum_forum_threadtable')->delete($forum['fid'], $tableid);
			}
			if($data['threads'] > 0 && $tableid != 0) {
				$archive = 1;
			}
		}
		C::t('forum_forum')->update($forum['fid'], array('archive' => $archive));

		$thread = C::t('forum_thread')->fetch_by_fid_displayorder($forum['fid']);
		$lastpost = "$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]";

		C::t('forum_forum')->update($forum['fid'], array('threads' => $threads, 'posts' => $posts, 'lastpost' => $lastpost));
	}

	if($processed) {
		cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		C::t('forum_forum')->clear_forum_counter_for_group();
		cpmsg('counter_forum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('digestsubmit', 1)) {

	if(!$current) {
		C::t('common_member_count')->clear_digestposts();
		$current = 0;
	}
	$nextlink = "action=counter&current=$next&pertask=$pertask&digestsubmit=yes";
	$processed = 0;
	$membersarray = $postsarray = array();

	foreach(C::t('forum_thread')->fetch_all_by_digest_displayorder(0, '<>', 0, '>=', $current, $pertask) as $thread) {
		$processed = 1;
		$membersarray[$thread['authorid']]++;
	}
	$threadtableids = C::t('common_setting')->fetch('threadtableids', true);
	foreach($threadtableids as $tableid) {
		if(!$tableid) {
			continue;
		}
		foreach(C::t('forum_thread')->fetch_all_by_digest_displayorder(0, '<>', 0, '>=', $current, $pertask, $tableid) as $thread) {
			$processed = 1;
			$membersarray[$thread['authorid']] ++;
		}
	}

	foreach($membersarray as $uid => $posts) {
		$postsarray[$posts][] = $uid;
	}
	unset($membersarray);

	foreach($postsarray as $posts => $uids) {
		C::t('common_member_count')->increase($uids, array('digestposts' => $posts));
	}

	if($processed) {
		cpmsg("$lang[counter_digest]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_digest_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('membersubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$threadtableids = C::t('common_setting')->fetch('threadtableids', true);
	$queryt = C::t('common_member')->range($current, $pertask);
	foreach($queryt as $mem) {
		$processed = 1;
		$postcount = 0;
		loadcache('posttable_info');
		if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
			foreach($_G['cache']['posttable_info'] as $key => $value) {
				$postcount += C::t('forum_post')->count_by_authorid($key, $mem['uid']);
			}
		} else {
			$postcount += C::t('forum_post')->count_by_authorid(0, $mem['uid']);
		}
		$postcount += C::t('forum_postcomment')->count_by_authorid($mem['uid']);
		$threadcount = C::t('forum_thread')->count_by_authorid($mem['uid']);
		foreach($threadtableids as $tableid) {
			if(!$tableid) {
				continue;
			}
			$threadcount += C::t('forum_thread')->count_by_authorid($mem['uid'], $tableid);
		}
		C::t('common_member_count')->update($mem['uid'], array('posts' => $postcount, 'threads' => $threadcount));
	}

	if($processed) {
		cpmsg("$lang[counter_member]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_member_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('threadsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&threadsubmit=yes";
	$processed = 0;

	foreach(C::t('forum_thread')->fetch_all_by_displayorder(0, '>=', $current, $pertask) as $threads) {
		$processed = 1;
		$replynum = C::t('forum_post')->count_visiblepost_by_tid($threads['tid']);
		$replynum--;
		$lastpost = C::t('forum_post')->fetch_visiblepost_by_tid('tid:'.$threads['tid'], $threads['tid'], 0, 1);
		if($threads['replies'] != $replynum || $threads['lastpost'] != $lastpost['dateline'] || $threads['lastposter'] != $lastpost['author']) {
			if(empty($threads['author'])) {
				$lastpost['author'] = '';
			}
			$updatedata = array(
				'replies' => $replynum,
				'lastpost' => $lastpost['dateline'],
				'lastposter' => $lastpost['author']
			);
			C::t('forum_thread')->update($threads['tid'], $updatedata, true, true);
		}
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_thread_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('movedthreadsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&movedthreadsubmit=yes";
	$processed = 0;

	$tids = array();
	$updateclosed = array();

	foreach(C::t('forum_thread')->fetch_all_movedthread($current, $pertask) as $thread) {
		$processed = 1;
		if($thread['isgroup'] && $thread['status'] == 3) {
			$updateclosed[] = $thread['tid'];
		} elseif($thread['threadexists']) {
			$tids[] = $thread['tid'];
		}
	}

	if($tids) {
		C::t('forum_thread')->delete_by_tid($tids, true);
	}
	if($updateclosed) {
		C::t('forum_thread')->update($updateclosed, array('closed' => ''));
	}

	if($processed) {
		cpmsg(cplang('counter_moved_thread').': '.cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_moved_thread_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('specialarrange', 1)) {
	$cursort = empty($_GET['cursort']) ? 0 : intval($_GET['cursort']);
	$changesort = isset($_GET['changesort']) && empty($_GET['changesort']) ? 0 : 1;
	$processed = 0;

	$fieldtypes = array('number' => 'bigint(20)', 'text' => 'mediumtext', 'radio' => 'smallint(6)', 'checkbox' => 'mediumtext', 'textarea' => 'mediumtext', 'select' => 'smallint(6)', 'calendar' => 'mediumtext', 'email' => 'mediumtext', 'url' => 'mediumtext', 'image' => 'mediumtext');

	$optionvalues = array();

	$optionvalues = $sortids = array();
	foreach(C::t('forum_typevar')->fetch_all_by_search_optiontype(1, array('checkbox', 'radio', 'select', 'number')) as $row) {
		$optionvalues[$row['sortid']][$row['identifier']] = $row['type'];
		$optionids[$row['sortid']][$row['optionid']] = $row['identifier'];
		$searchs[$row['sortid']][$row['optionid']] = $row['search'];
		$sortids[] = $row['sortid'];
	}
	$sortids = array_unique($sortids);
	sort($sortids);
	if($sortids[$cursort] && $optionvalues[$sortids[$cursort]]) {
		$processed = 1;
		$sortid = $sortids[$cursort];
		$options = $optionvalues[$sortid];
		$search = $searchs[$sortid];
		$dbcharset = $_G['config']['db'][1]['dbcharset'];
		$dbcharset = empty($dbcharset) ? str_replace('-', '', CHARSET) : $dbcharset;
		$fields = "tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',KEY (fid)";
		C::t('forum_optionvalue')->create($sortid, $fields, $dbcharset);
		if($changesort) {
			C::t('forum_optionvalue')->truncate($sortid);
		}
		$opids = array_keys($optionids[$sortid]);

		$tables = C::t('forum_optionvalue')->showcolumns($sortid);
		foreach($optionids[$sortid] as $optionid => $identifier) {
			if(!$tables[$identifier] && (in_array($options[$identifier], array('checkbox', 'radio', 'select', 'number')) || $search[$optionid])) {
				$fieldname = $identifier;
				if(in_array($options[$identifier], array('radio'))) {
					$fieldtype = 'smallint(6) UNSIGNED NOT NULL DEFAULT \'0\'';
				} elseif(in_array($options[$identifier], array('number', 'range'))) {
					$fieldtype = 'int(10) UNSIGNED NOT NULL DEFAULT \'0\'';
				} elseif($options[$identifier] == 'select') {
					$fieldtype = 'varchar(50) NOT NULL';
				} else {
					$fieldtype = 'mediumtext NOT NULL';
				}
				C::t('forum_optionvalue')->alter($sortid, "ADD $fieldname $fieldtype");

				if(in_array($options[$identifier], array('radio', 'select', 'number'))) {
					C::t('forum_optionvalue')->alter($sortid, "ADD INDEX ($fieldname)");
				}
			}
		}

		$inserts = array();
		$typeoptionvararr = C::t('forum_typeoptionvar')->fetch_all_by_search($sortid, null, null, $opids);
		if($typeoptionvararr) {
			$tids = array();
			foreach($typeoptionvararr as $value) {
				$tids[$value['tid']] = $value['tid'];
			}
			$tids = C::t('forum_thread')->fetch_all($tids);
			foreach($typeoptionvararr as $row) {
				$row['fid'] = $tids[$row['tid']]['fid'];
				$opname = $optionids[$sortid][$row['optionid']];
				if(empty($inserts[$row[tid]])) {
					$inserts[$row['tid']]['tid'] = $row['tid'];
					$inserts[$row['tid']]['fid'] = $row['fid'];
				}
				$inserts[$row['tid']][$opname] = addslashes($row['value']);
			}
			unset($tids, $typeoptionvararr);
		}
		if($inserts) {
			foreach($inserts as $tid => $fieldval) {
				$rfields = array();
				$ikey = $ival = '';
				foreach($fieldval as $ikey => $ival) {
					$rfields[] = "`$ikey`='$ival'";
				}
				C::t('forum_optionvalue')->insert($sortid, "SET ".implode(',', $rfields), true);
			}
		}
		$cursort ++;
		$changesort = 1;
	}

	$nextlink = "action=counter&changesort=$changesort&cursort=$cursort&specialarrange=yes";
	if($processed) {
		cpmsg('counter_special_arrange', $nextlink, 'loading', array('cursort' => $cursort, 'sortids' => count($sortids)));
	} else {
		cpmsg('counter_special_arrange_succeed', 'action=counter', 'succeed');
	}


	$nextlink = "action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$queryt = C::t('common_member')->range($current, $pertask);
	foreach($queryt as $mem) {
		$processed = 1;
		$postcount = 0;
		loadcache('posttable_info');
		if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
			foreach($_G['cache']['posttable_info'] as $key => $value) {
				$postcount += C::t('forum_post')->count_by_authorid($key, $mem['uid']);
			}
		} else {
			$postcount += C::t('forum_post')->count_by_authorid(0, $mem['uid']);
		}
		$postcount += C::t('forum_postcomment')->count_by_authorid($mem['uid']);
		$threadcount = C::t('forum_thread')->count_by_authorid($mem['uid']);
		C::t('common_member_count')->update($mem['uid'], array('posts' => $postcount, 'threads' => $threadcount));
	}

	if($processed) {
		cpmsg("$lang[counter_member]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_member_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('groupmembernum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupmembernum=yes";
	$processed = 0;

	$query = C::t('forum_forum')->fetch_all_fid_for_group($current, $pertask, 1);
	foreach($query as $group) {
		$processed = 1;
		$membernum = C::t('forum_groupuser')->fetch_count_by_fid($group['fid']);
		C::t('forum_forumfield')->update($group['fid'], array('membernum' => $membernum));
	}

	if($processed) {
		cpmsg("$lang[counter_groupmember_num]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_groupmember_num_succeed', 'action=counter', 'succeed');
	}
} elseif(submitcheck('groupmemberpost', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupmemberpost=yes";
	$processed = 0;

	$queryf = C::t('forum_forum')->fetch_all_fid_for_group($current, $pertask, 1);
	foreach($queryf as $group) {
		$processed = 1;

		$mreplies_array = array();
		loadcache('posttableids');
		$posttables = empty($_G['cache']['posttableids']) ? array(0) : $_G['cache']['posttableids'];
		foreach($posttables as $posttableid) {
			$mreplieslist = C::t('forum_post')->count_group_authorid_by_fid($posttableid, $group['fid']);
			if($mreplieslist) {
				foreach($mreplieslist as $mreplies) {
					$mreplies_array[$mreplies['authorid']] = $mreplies_array[$mreplies['authorid']] + $mreplies['num'];
				}
			}
		}
		unset($mreplieslist);
		foreach($mreplies_array as $authorid => $num) {
			C::t('forum_groupuser')->update_for_user($authorid, $group['fid'], null, $num);

		}
		foreach(C::t('forum_thread')->count_group_thread_by_fid($group['fid']) as $mthreads) {
			C::t('forum_groupuser')->update_for_user($mthreads['authorid'], $group['fid'], $mthreads['num']);
		}
	}

	if($processed) {
		cpmsg("$lang[counter_groupmember_post]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_groupmember_post_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('groupnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupnum=yes";
	$processed = 0;

	$queryf = C::t('forum_forum')->fetch_all_fid_for_group($current, $pertask);
	foreach($queryf as $group) {
		$processed = 1;
		$groupnum = C::t('forum_forum')->fetch_groupnum_by_fup($group['fid']);
		C::t('forum_forumfield')->update($group['fid'], array('groupnum' => intval($groupnum)));
	}

	if($processed) {
		cpmsg("$lang[counter_groupnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		updatecache('grouptype');
		cpmsg('counter_groupnum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('blogreplynum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&blogreplynum=yes";
	if(blog_replynum_stat($current, $pertask)) {
		cpmsg("$lang[counter_blog_replynum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_blog_replynum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('friendnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&friendnum=yes";
	if(space_friendnum_stat($current, $pertask)) {
		cpmsg("$lang[counter_friendnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_friendnum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('albumpicnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&albumpicnum=yes";
	if(album_picnum_stat($current, $pertask)) {
		cpmsg("$lang[counter_album_picnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_album_picnum_succeed', 'action=counter', 'succeed');
	}
} elseif(submitcheck('setthreadcover', 1)) {
	$fid = intval($_GET['fid']);
	$allthread = intval($_GET['allthread']);
	if(empty($fid)) {
		cpmsg('counter_thread_cover_fiderror', 'action=counter', 'error');
	}
	$nextlink = "action=counter&current=$next&pertask=$pertask&setthreadcover=yes&fid=$fid&allthread=$allthread";
	$starttime = strtotime($_GET['starttime']);
	$endtime = strtotime($_GET['endtime']);
	$timesql = '';
	if($starttime) {
		$timesql .= " AND lastpost > $starttime";
		$nextlink .= '&starttime='.$_GET['starttime'];
	}
	if($endtime) {
		$timesql .= " AND lastpost < $endtime";
		$nextlink .= '&endtime='.$_GET['endtime'];
	}
	$processed = 0;
	$foruminfo = C::t('forum_forum')->fetch_info_by_fid($fid);
	if(empty($foruminfo['picstyle'])) {
		cpmsg('counter_thread_cover_fidnopicstyle', 'action=counter', 'error');
	}
	if($_G['setting']['forumpicstyle']) {
		$_G['setting']['forumpicstyle'] = dunserialize($_G['setting']['forumpicstyle']);
		empty($_G['setting']['forumpicstyle']['thumbwidth']) && $_G['setting']['forumpicstyle']['thumbwidth'] = 203;
		empty($_G['setting']['forumpicstyle']['thumbheight']) && $_G['setting']['forumpicstyle']['thumbheight'] = 0;
	} else {
		$_G['setting']['forumpicstyle'] = array('thumbwidth' => 203, 'thumbheight' => 0);
	}
	require_once libfile('function/post');
	$coversql = empty($allthread) ? 'AND cover=\'0\'' : '';
	$cover = empty($allthread) ? 0 : null;
	$_G['forum']['ismoderator'] = 1;
	foreach(C::t('forum_thread')->fetch_all_by_fid_cover_lastpost($fid, $cover, $starttime, $endtime, $current, $pertask) as $thread) {
		$processed = 1;
		$pid = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid'], 0);
		$pid = $pid['pid'];
		setthreadcover($pid);
	}

	if($processed) {
		cpmsg("$lang[counter_thread_cover]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_thread_cover_succeed', 'action=counter', 'succeed');
	}
} else {

	shownav('tools', 'nav_updatecounters');
	showsubmenu('nav_updatecounters');
	showtips('counter_tips');
	showformheader('counter');
	showtableheader();
	showsubtitle(array('', 'counter_amount'));
	showhiddenfields(array('pertask' => ''));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_forum]:",
		'<input name="pertask1" type="text" class="txt" value="15" /><input type="submit" class="btn" name="forumsubmit" onclick="this.form.pertask.value=this.form.pertask1.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_digest]:",
		'<input name="pertask2" type="text" class="txt" value="1000" /><input type="submit" class="btn" name="digestsubmit" onclick="this.form.pertask.value=this.form.pertask2.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_member]:",
		'<input name="pertask3" type="text" class="txt" value="1000" /><input type="submit" class="btn" name="membersubmit" onclick="this.form.pertask.value=this.form.pertask3.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_thread]:",
		'<input name="pertask4" type="text" class="txt" value="500" /><input type="submit" class="btn" name="threadsubmit" onclick="this.form.pertask.value=this.form.pertask4.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_special]:",
		'<input name="pertask7" type="text" class="txt" value="1" disabled/><input type="submit" class="btn" name="specialarrange" onclick="this.form.pertask.value=this.form.pertask7.value" value="'.$lang['submit'].'" />'
	));

	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupnum]:",
		'<input name="pertask8" type="text" class="txt" value="10" /><input type="submit" class="btn" name="groupnum" onclick="this.form.pertask.value=this.form.pertask8.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupmember_num]:",
		'<input name="pertask9" type="text" class="txt" value="100" /><input type="submit" class="btn" name="groupmembernum" onclick="this.form.pertask.value=this.form.pertask9.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupmember_post]:",
		'<input name="pertask10" type="text" class="txt" value="100" /><input type="submit" class="btn" name="groupmemberpost" onclick="this.form.pertask.value=this.form.pertask10.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_blog_replynum]:",
		'<input name="pertask11" type="text" class="txt" value="100" /><input type="submit" class="btn" name="blogreplynum" onclick="this.form.pertask.value=this.form.pertask11.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_friendnum]:",
		'<input name="pertask12" type="text" class="txt" value="100" /><input type="submit" class="btn" name="friendnum" onclick="this.form.pertask.value=this.form.pertask12.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_album_picnum]:",
		'<input name="pertask13" type="text" class="txt" value="100" /><input type="submit" class="btn" name="albumpicnum" onclick="this.form.pertask.value=this.form.pertask13.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_thread_cover]:",
		'<script type="text/javascript" src="static/js/calendar.js"></script><input name="pertask14" type="text" class="txt" value="100" /> '.$lang['counter_forumid'].': <input type="text" class="txt" name="fid" value="" size="10">&nbsp;<input type="checkbox" value="1" name="allthread">'.$lang['counter_have_cover'].'<br><input type="text" onclick="showcalendar(event, this)" value="" name="starttime" class="txt"> -- <input type="text" onclick="showcalendar(event, this)" value="" name="endtime" class="txt">('.$lang['counter_thread_cover_settime'].')  &nbsp;&nbsp;<input type="submit" class="btn" name="setthreadcover" onclick="this.form.pertask.value=this.form.pertask14.value" value="'.$lang['submit'].'" />'
	));
	showtablefooter();
	showformfooter();

}

function runuchcount($start, $perpage) {

}

?>