<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_warn.php 30872 2012-06-27 10:11:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowwarnpost']) {
	showmessage('no_privilege_warnpost');
}

$topiclist = $_GET['topiclist'];
if(!($warnpids = dimplode($topiclist))) {
	showmessage('admin_warn_invalid');
} elseif(!$_G['group']['allowbanpost'] || !$_G['tid']) {
	showmessage('admin_nopermission', NULL);
}

$posts = $authors = array();
$authorwarnings = $warningauthor = $warnstatus = '';
$postlist = C::t('forum_post')->fetch_all('tid:'.$_G['tid'], $topiclist);
foreach($postlist as $post) {
	$uids[] = $post['authorid'];
}
$memberlist = C::t('common_member')->fetch_all($uids);
foreach($postlist as $post) {
	if($post['tid'] != $_G['tid']) {
		continue;
	}
	$post['adminid'] = $memberlist[$post['authorid']]['adminid'];
	if($_G['adminid'] == 1 && $post['adminid'] != 1 ||
		$_G['adminid'] == 2 && !in_array($post['adminid'], array(1, 2)) ||
		$_G['adminid'] == 3 && in_array($post['adminid'], array(0, -1))) {
		$warnstatus = ($post['status'] & 2) || $warnstatus;
		$authors[$post['authorid']] = 1;
		$posts[] = $post;
	}
}
unset($memberlist, $postlist, $uids);

if(!$posts) {
	showmessage('admin_warn_nopermission');
}
$authorcount = count(array_keys($authors));
$modpostsnum = count($posts);

if($modpostsnum == 1 || $authorcount == 1) {
	$authorwarnings = C::t('forum_warning')->count_by_authorid_dateline($posts[0][authorid]);
	$warningauthor = $posts[0]['author'];
}

if(!submitcheck('modsubmit')) {

	$warnpid = $checkunwarn = $checkwarn = '';
	foreach($topiclist as $id) {
		$warnpid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
	}

	$warnstatus ? $checkunwarn = 'checked="checked"' : $checkwarn = 'checked="checked"';

	include template('forum/topicadmin_action');

} else {

	$warned = intval($_GET['warned']);
	$modaction = $warned ? 'WRN' : 'UWN';

	$reason = checkreasonpm();

	include_once libfile('function/member');

	$pids = $comma = '';
	foreach($posts as $k => $post) {
		if($warned && !($post['status'] & 2)) {
			C::t('forum_post')->increase_status_by_pid('tid:'.$_G['tid'], $post['pid'], 2, '|', true);
			$reason = cutstr(dhtmlspecialchars($_GET['reason']), 40);
			C::t('forum_warning')->insert(array(
				'pid' => $post['pid'],
				'operatorid' => $_G['uid'],
				'operator' => $_G['username'],
				'authorid' => $post['authorid'],
				'author' => $post['author'],
				'dateline' => $_G['timestamp'],
				'reason' => $reason,
			));
			$authorwarnings = C::t('forum_warning')->count_by_authorid_dateline($post['authorid'], $_G['timestamp'] - $_G['setting']['warningexpiration'] * 86400);
			if($authorwarnings >= $_G['setting']['warninglimit']) {
				$member = getuserbyuid($post[authorid]);
				$memberfieldforum = C::t('common_member_field_forum')->fetch($post[authorid]);
				$groupterms = dunserialize($memberfieldforum['groupterms']);
				unset($memberfieldforum);
				if($member && $member['groupid'] != 4) {
					$banexpiry = TIMESTAMP + $_G['setting']['warningexpiration'] * 86400;
					$groupterms['main'] = array('time' => $banexpiry, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
					$groupterms['ext'][4] = $banexpiry;
					C::t('common_member')->update($post['authorid'], array('groupid' => 4, 'adminid' => -1, 'groupexpiry' => groupexpiry($groupterms)));
					C::t('common_member_field_forum')->update($post['authorid'], array('groupterms' => serialize($groupterms)));
				}
			}
			$pids .= $comma.$post['pid'];
			$comma = ',';

			crime('recordaction', $post['authorid'], 'crime_warnpost', lang('forum/misc', 'crime_postreason', array('reason' => $reason, 'tid' => $_G['tid'], 'pid' => $post['pid'])));

		} elseif(!$warned && ($post['status'] & 2)) {
			C::t('forum_post')->increase_status_by_pid('tid:'.$_G['tid'], $post['pid'], 2, '^', true);
			C::t('forum_warning')->delete_by_pid($post['pid']);
			$pids .= $comma.$post['pid'];
			$comma = ',';
		}
	}

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_warn_post', 'notictype' => 'post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason,
			'warningexpiration' => $_G['setting']['warningexpiration'], 'warninglimit' => $_G['setting']['warninglimit'], 'warningexpiration' => $_G['setting']['warningexpiration'],
			'authorwarnings' => $authorwarnings),
	'modtids'	=> 0,
	'modlog'	=> $thread
	);

}

?>