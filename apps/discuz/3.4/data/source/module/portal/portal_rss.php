<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_rss.php 24992 2011-10-20 09:41:00Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['rssstatus']) {
	exit('RSS Disabled');
}

$ttl = $_G['setting']['rssttl'] ? $_G['setting']['rssttl']: 30;
$num = 20;

$_G['groupid'] = 7;
$_G['uid'] = 0;
$_G['username'] = $_G['member']['password'] = '';

$rsscatid = empty($_GET['catid']) ? 0 : intval($_GET['catid']);
$catname = '';

if(empty($rsscatid)) {
	foreach($_G['cache']['portalcategory'] as $catid => $category) {
		$catarray[] = $catid;
	}
} else {

	if(isset($_G['cache']['portalcategory'][$rsscatid])) {
		$catarray = array($rsscatid);
		$catname = dhtmlspecialchars($_G['cache']['portalcategory'][$rsscatid]['catname']);
	} else {
		exit('Specified article not found');
	}
}

$rewriteflag = 0;
$havedomain = implode('', $_G['setting']['domain']['app']);
if(is_array($_G['setting']['rewritestatus']) && in_array('portal_article', $_G['setting']['rewritestatus'])) {
	$rewriteflag = 1;
}

$charset = $_G['config']['output']['charset'];
dheader("Content-type: application/xml");
echo 	"<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".
	"<rss version=\"2.0\">\n".
	"  <channel>\n".
	(count($catarray) > 1 ?
		"    <title>{$_G[setting][bbname]}</title>\n".
		"    <link>{$_G[siteurl]}forum.php</link>\n".
		"    <description>Latest $num articles of all categories</description>\n"
		:
		"    <title>{$_G[setting][bbname]} - $catname</title>\n".
		"    <link>{$_G[siteurl]}portal.php?mod=list&amp;catid=$rsscatid</link>\n".
		"    <description>Latest $num articles of $catname</description>\n"
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

if($catarray) {
	$alldata = C::t('portal_rsscache')->fetch_all_by_catid($catarray, $num);
	if($alldata) {
		foreach($alldata as $article) {
			if(TIMESTAMP - $article['lastupdate'] > $ttl * 60) {
				updatersscache($num);
				break;
			} else {
				list($article['description'], $attachremote, $attachfile, $attachsize) = explode("\t", $article['description']);
				if($attachfile) {
					if($attachremote) {
						$filename = $_G['setting']['ftp']['attachurl'].'portal/'.$attachfile;
					} else {
						$filename = $_G['siteurl'].$_G['setting']['attachurl'].'portal/'.$attachfile;
					}
				}
				echo 	"    <item>\n".
					"      <title>".$article['subject']."</title>\n".
					"      <link>$_G[siteurl]".($rewriteflag ? rewriteoutput('portal_article', 1, '', $article[aid]) : "portal.php?mod=view&amp;aid=$article[aid]")."</link>\n".
					"      <description><![CDATA[".dhtmlspecialchars($article['description'])."]]></description>\n".
					"      <category>".dhtmlspecialchars($article['catname'])."</category>\n".
					"      <author>".dhtmlspecialchars($article['author'])."</author>\n".
					($attachfile ? '<enclosure url="'.$filename.'" length="'.$attachsize.'" type="image/jpeg" />'."\n" : '').
					"      <pubDate>".gmdate('r', $article['dateline'])."</pubDate>\n".
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
	$processname = 'portal_rss_cache';
	if(discuz_process::islocked($processname, 600)) {
		return false;
	}
	C::t('portal_rsscache')->truncate();
	require_once libfile('function/post');
	foreach($_G['cache']['portalcategory'] as $catid => $catarray) {
		$query = C::t('portal_article_title')->fetch_all_for_cat($catid, 0, 1, 0, $num);
		$catarray['catname'] = addslashes($catarray['catname']);
		foreach($query as $article) {
			$article['author'] = $article['author'] != '' ? addslashes($article['author']) : ($article['username'] ? addslashes($article['username']) : 'Anonymous');
			$article['title'] = addslashes($article['title']);
			$articleattach = C::t('portal_attachment')->fetch_by_aid_image($article['aid']);
			$attachdata = '';
			if(!empty($articleattach)) {
				$attachdata = "\t".$articleattach['remote']."\t".$articleattach['attachment']."\t".$articleattach['filesize'];
			}
			$article['description'] = addslashes(messagecutstr($article['summary'], 250 - strlen($attachdata)).$attachdata);
			C::t('portal_rsscache')->insert(array(
				'lastupdate'=>$_G['timestamp'],
				'catid'=>$catid,
				'aid'=>$article['aid'],
				'dateline'=>$article['dateline'],
				'catname'=>$catarray['catname'],
				'author'=>$article['author'],
				'subject'=>$article['title'],
				'description'=>$article['description']
			));
		}
	}
	discuz_process::unlock($processname);
	return true;
}
?>