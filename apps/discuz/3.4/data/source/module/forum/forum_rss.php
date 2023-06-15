<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_rss.php 33056 2013-04-15 06:44:56Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

error_reporting(0);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', '');

loadcache('forums');

if(!$_G['setting']['rssstatus']) {
	exit('RSS Disabled');
}

$ttl = $_G['setting']['rssttl'] ? $_G['setting']['rssttl']: 30;
$num = 20;

$_G['groupid'] = 7;
$_G['uid'] = 0;
$_G['username'] = $_G['member']['password'] = '';
$rssfid = empty($_GET['fid']) ? 0 : intval($_GET['fid']);
$forumname = '';

if(empty($rssfid)) {
	foreach($_G['cache']['forums'] as $fid => $forum) {
		if(rssforumperm($forum)) {
			$fidarray[] = $fid;
		}
	}
} else {
	$forum = isset($_G['cache']['forums'][$rssfid]) && $_G['cache']['forums'][$rssfid]['type'] != 'group' ? $_G['cache']['forums'][$rssfid] : array();
	if(!isset($_G['cache']['forums'][$rssfid])) {
		$forum = $_G['cache']['forums'][$rssfid] = array();
		$subforum = C::t('forum_forum')->fetch_info_by_fid($rssfid);
		if($subforum['type'] == 'sub') {
			$forum = $_G['cache']['forums'][$rssfid] = $subforum;
		}
	}
	if($forum && rssforumperm($forum)) {
		$fidarray = array($rssfid);
		$forumname = dhtmlspecialchars($_G['cache']['forums'][$rssfid]['name']);
	} else {
		exit('Specified forum not found');
	}
}

$frewriteflag = $trewriteflag = 0;
$havedomain = implode('', $_G['setting']['domain']['app']);
if(is_array($_G['setting']['rewritestatus']) && in_array('forum_forumdisplay', $_G['setting']['rewritestatus'])) {
	$frewriteflag = 1;
}
if(is_array($_G['setting']['rewritestatus']) && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
	$trewriteflag = 1;
}

$charset = $_G['config']['output']['charset'];
dheader("Content-type: application/xml");
echo 	"<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".
	"<rss version=\"2.0\">\n".
	"  <channel>\n".
	(count($fidarray) > 1 ?
		"    <title>{$_G[setting][bbname]}</title>\n".
		"    <link>{$_G[siteurl]}forum.php</link>\n".
		"    <description>Latest $num threads of all forums</description>\n"
		:
		"    <title>{$_G[setting][bbname]} - $forumname</title>\n".
		"    <link>{$_G[siteurl]}".($frewriteflag ? rewriteoutput('forum_forumdisplay', 1, '', $rssfid) : "forum.php?mod=forumdisplay&amp;fid=$rssfid")."</link>\n".
		"    <description>Latest $num threads of $forumname</description>\n"
	).
	"    <copyright>Copyright(C) {$_G[setting][bbname]}</copyright>\n".
	"    <generator>Discuz! Board by Comsenz Inc.</generator>\n".
	"    <lastBuildDate>".gmdate('r', TIMESTAMP)."</lastBuildDate>\n".
	"    <ttl>$ttl</ttl>\n".
	"    <image>\n".
	"      <url>{$_G[siteurl]}static/image/common/logo_88_31.gif</url>\n".
	"      <title>{$_G[setting][bbname]}</title>\n".
	"      <link>{$_G[siteurl]}</link>\n".
	"    </image>\n";

if($fidarray) {
	$alldata = C::t('forum_rsscache')->fetch_all_by_fid($fidarray, $num);
	if($alldata) {
		foreach($alldata as $thread) {
			if(TIMESTAMP - $thread['lastupdate'] > $ttl * 60) {
				updatersscache($num);
				break;
			} else {
				list($thread['description'], $attachremote, $attachfile, $attachsize) = explode("\t", $thread['description']);
				if($attachfile) {
					if($attachremote) {
						$filename = $_G['setting']['ftp']['attachurl'].'forum/'.$attachfile;
					} else {
						$filename = (!strstr($_G['setting']['attachurl'], '://') ? $_G['siteurl'] : '').$_G['setting']['attachurl'].'forum/'.$attachfile;
					}
				}
				echo 	"    <item>\n".
					"      <title>".$thread['subject']."</title>\n".
					"      <link>$_G[siteurl]".($trewriteflag ? rewriteoutput('forum_viewthread', 1, '', $thread['tid']) : "forum.php?mod=viewthread&amp;tid=$thread[tid]")."</link>\n".
					"      <description><![CDATA[".dhtmlspecialchars($thread['description'])."]]></description>\n".
					"      <category>".dhtmlspecialchars($thread['forum'])."</category>\n".
					"      <author>".dhtmlspecialchars($thread['author'])."</author>\n".
					($attachfile ? '<enclosure url="'.$filename.'" length="'.$attachsize.'" type="image/jpeg" />' : '').
					"      <pubDate>".gmdate('r', $thread['dateline'])."</pubDate>\n".
					"    </item>\n";
			}
		}
	} else {
		updatersscache($num);
	}
}

echo 	"  </channel>\n".
	"</rss>";

function updatersscache($num) {
	global $_G;
	$processname = 'forum_rss_cache';
	if(discuz_process::islocked($processname, 600)) {
		return false;
	}
	C::t('forum_rsscache')->truncate();
	require_once libfile('function/post');
	foreach($_G['cache']['forums'] as $fid => $forum) {
		if($forum['type'] != 'group') {
			$forum['name'] = addslashes($forum['name']);
			foreach(C::t('forum_thread')->fetch_all_by_fid_displayorder($fid, 0, null, null, 0, $num, 'tid') as $thread) {
				$thread['author'] = $thread['author'] != '' ? addslashes($thread['author']) : 'Anonymous';
				$thread['subject'] = addslashes($thread['subject']);
				$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($thread['tid']);
				$attachdata = '';
				$thread['message'] = $post['message'];
				$thread['status'] = $post['status'];
				$thread['description'] = $thread['readperm'] > 0 || $thread['price'] > 0 || $thread['status'] & 1 ? '' : addslashes(messagecutstr($thread['message'], 250 - strlen($attachdata)).$attachdata);
				C::t('forum_rsscache')->insert(array(
					'lastupdate'=>$_G['timestamp'],
					'fid'=>$fid,
					'tid'=>$thread['tid'],
					'dateline'=>$thread['dateline'],
					'forum'=>$forum['name'],
					'author'=>$thread['author'],
					'subject'=>$thread['subject'],
					'description'=>$thread['description']
				), false, true);
			}
		}
	}
	discuz_process::unlock($processname);
	return true;
}

?>