<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_click.php 31313 2012-08-10 03:51:03Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$clickid = empty($_GET['clickid'])?0:intval($_GET['clickid']);
$idtype = empty($_GET['idtype'])?'':trim($_GET['idtype']);
$id = empty($_GET['id'])?0:intval($_GET['id']);

loadcache('click');
$clicks = empty($_G['cache']['click'][$idtype])?array():$_G['cache']['click'][$idtype];
$click = $clicks[$clickid];

if(empty($click)) {
	showmessage('click_error');
}

switch ($idtype) {
	case 'picid':
		$item = C::t('home_pic')->fetch($id);
		if($item) {
			$picfield = C::t('home_picfield')->fetch($id);
			$album = C::t('home_album')->fetch($item['albumid']);
			$item['hotuser'] = $picfield['hotuser'];
			$item['friend'] = $album['friend'];
			$item['username'] = $album['username'];
		}
		$tablename = 'home_pic';
		break;
	case 'aid':
		$item = C::t('portal_article_title')->fetch($id);
		$tablename = 'portal_article_title';
		break;
	default:
		$idtype = 'blogid';
		$item = array_merge(
			C::t('home_blog')->fetch($id),
			C::t('home_blogfield')->fetch($id)
		);
		$tablename = 'home_blog';
		break;
}
if(!$item) {
	showmessage('click_item_error');
}

$hash = md5($item['uid']."\t".$item['dateline']);
if($_GET['op'] == 'add') {
	if(!checkperm('allowclick') || $_GET['hash'] != $hash) {
		showmessage('no_privilege_click');
	}

	if($item['uid'] == $_G['uid']) {
		showmessage('click_no_self');
	}

	if(isblacklist($item['uid'])) {
		showmessage('is_blacklist');
	}

	if(C::t('home_clickuser')->count_by_uid_id_idtype($space[uid], $id, $idtype)) {
		showmessage('click_have');
	}

	$setarr = array(
		'uid' => $space['uid'],
		'username' => $_G['username'],
		'id' => $id,
		'idtype' => $idtype,
		'clickid' => $clickid,
		'dateline' => $_G['timestamp']
	);
	C::t('home_clickuser')->insert($setarr);

	C::t($tablename)->update_click($id, $clickid, 1);

	hot_update($idtype, $id, $item['hotuser']);

	$q_note = '';
	$q_note_values = array();

	$fs = array();
	switch ($idtype) {
		case 'blogid':
			$fs['title_template'] = 'feed_click_blog';
			$fs['title_data'] = array(
				'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[username]}</a>",
				'subject' => "<a href=\"home.php?mod=space&uid=$item[uid]&do=blog&id=$item[blogid]\">$item[subject]</a>",
				'click' => $click['name']
			);

			$q_note = 'click_blog';
			$q_note_values = array(
				'url'=>"home.php?mod=space&uid=$item[uid]&do=blog&id=$item[blogid]",
				'subject'=>$item['subject'],
				'from_id' => $item['blogid'],
				'from_idtype' => 'blogid'
			);
			break;
		case 'aid':
			require_once libfile('function/portal');
			$article_url = fetch_article_url($item);
			$fs['title_template'] = 'feed_click_article';
			$fs['title_data'] = array(
				'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[username]}</a>",
				'subject' => "<a href=\"$article_url\">$item[title]</a>",
				'click' => $click['name']
			);

			$q_note = 'click_article';
			$q_note_values = array(
				'url'=>$article_url,
				'subject'=>$item['title'],
				'from_id' => $item['aid'],
				'from_idtype' => 'aid'
			);
			break;
		case 'picid':
			$fs['title_template'] = 'feed_click_pic';
			$fs['title_data'] = array(
				'touser' => "<a href=\"home.php?mod=space&uid=$item[uid]\">{$item[username]}</a>",
				'click' => $click['name']
			);
			$fs['images'] = array(pic_get($item['filepath'], 'album', $item['thumb'], $item['remote']));
			$fs['image_links'] = array("home.php?mod=space&uid=$item[uid]&do=album&picid=$item[picid]");
			$fs['body_general'] = $item['title'];

			$q_note = 'click_pic';
			$q_note_values = array(
				'url'=>"home.php?mod=space&uid=$item[uid]&do=album&picid=$item[picid]",
				'from_id' => $item['picid'],
				'from_idtype' => 'picid'
			);
			break;
	}

	if(empty($item['friend']) && ckprivacy('click', 'feed')) {
		require_once libfile('function/feed');
		$fs['title_data']['hash_data'] = "{$idtype}{$id}";
		feed_add('click', $fs['title_template'], $fs['title_data'], '', array(), $fs['body_general'],$fs['images'], $fs['image_links']);
	}

	updatecreditbyaction('click', 0, array(), $idtype.$id);

	require_once libfile('function/stat');
	updatestat('click');

	notification_add($item['uid'], 'click', $q_note, $q_note_values);

	showmessage('click_success', '', array('idtype' => $idtype, 'id' => $id, 'clickid' => $clickid), array('msgtype' => 3, 'showmsg' => true, 'closetime' => true));

} elseif ($_GET['op'] == 'show') {

	$maxclicknum = 0;
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $item["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$perpage = 18;
	$page = intval($_GET['page']);
	$start = ($page-1)*$perpage;
	if($start < 0) $start = 0;

	$count = C::t('home_clickuser')->count_by_id_idtype($id, $idtype);
	$clickuserlist = array();
	$click_multi = '';

	if($count) {
		foreach(C::t('home_clickuser')->fetch_all_by_id_idtype($id, $idtype, $start, $perpage) as $value) {
			$value['clickname'] = $clicks[$value['clickid']]['name'];
			$clickuserlist[] = $value;
		}

		$click_multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=click&op=show&clickid=$clickid&idtype=$idtype&id=$id");
	}
}

include_once(template('home/spacecp_click'));

?>