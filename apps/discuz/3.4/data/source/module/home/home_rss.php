<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_rss.php 25756 2011-11-22 02:47:45Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pagenum = 20;

$siteurl = getsiteurl();
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$list = array();

if(!empty($uid)) {
	$space = getuserbyuid($uid, 1);
}
if(empty($space)) {
	$space['username'] = $_G['setting']['sitename'];
	$space['name'] = $_G['setting']['sitename'];
	$space['email'] = $_G['setting']['adminemail'];
	$space['space_url'] = $siteurl;
} else {
	$space['username'] = $space['username'].'@'.$_G['setting']['sitename'];
	$space['space_url'] = $siteurl."home.php?mod=space&amp;uid=$space[uid]";
}

$uidsql = empty($space['uid'])?'':" AND b.uid='$space[uid]'";

$data_blog = C::t('home_blog')->range(0, $pagenum, 'DESC', 'dateline', 0, null, $uid);
$blogids = array_keys($data_blog);
$data_blogfield = C::t('home_blogfield')->fetch_all($blogids);

$charset = $_G['config']['output']['charset'];
dheader("Content-type: application/xml");
echo 	"<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".
	"<rss version=\"2.0\">\n".
	"  <channel>\n".
	"    <title>{$space[username]}</title>\n".
	"    <link>{$space[space_url]}</link>\n".
	"    <description>{$_G[setting][bbname]}</description>\n".
	"    <copyright>Copyright(C) {$_G[setting][bbname]}</copyright>\n".
	"    <generator>Discuz! Board by Comsenz Inc.</generator>\n".
	"    <lastBuildDate>".gmdate('r', TIMESTAMP)."</lastBuildDate>\n".
	"    <image>\n".
	"      <url>{$_G[siteurl]}static/image/common/logo_88_31.gif</url>\n".
	"      <title>{$_G[setting][bbname]}</title>\n".
	"      <link>{$_G[siteurl]}</link>\n".
	"    </image>\n";

foreach($data_blog as $curblogid => $value) {
	$value = array_merge($value, (array)$data_blogfield[$curblogid]);
	$value['message'] = getstr($value['message'], 300, 0, 0, 0, -1);
	if($value['pic']) {
		$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
		$value['message'] .= "<br /><img src=\"$value[pic]\">";
	}
	echo 	"    <item>\n".
			"      <title>".$value['subject']."</title>\n".
			"      <link>$_G[siteurl]home.php?mod=space&amp;uid=$value[uid]&amp;do=blog&amp;id=$value[blogid]</link>\n".
			"      <description><![CDATA[".dhtmlspecialchars($value['message'])."]]></description>\n".
			"      <author>".dhtmlspecialchars($value['username'])."</author>\n".
			"      <pubDate>".gmdate('r', $value['dateline'])."</pubDate>\n".
			"    </item>\n";
}

echo 	"  </channel>\n".
	"</rss>";
?>