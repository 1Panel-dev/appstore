<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_favorite.php 33832 2013-08-20 03:32:32Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$space = getuserbyuid($_G['uid']);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

$perpage = 20;

$_G['disabledwidthauto'] = 0;

$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$idtypes = array('thread'=>'tid', 'forum'=>'fid', 'blog'=>'blogid', 'group'=>'gid', 'album'=>'albumid', 'space'=>'uid', 'article'=>'aid');
$_GET['type'] = isset($idtypes[$_GET['type']]) ? $_GET['type'] : 'all';
$actives[$_GET['type']] = ' class="a"';

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'favorite',
	'type' => $_GET['type'],
	'from' => $_GET['from']
);
$theurl = 'home.php?'.url_implode($gets);

$wherearr = $list = array();
$favid = empty($_GET['favid'])?0:intval($_GET['favid']);
$idtype = isset($idtypes[$_GET['type']]) ? $idtypes[$_GET['type']] : '';

$count = C::t('home_favorite')->count_by_uid_idtype($_G['uid'], $idtype, $favid);
if($count) {
	$icons = array(
		'tid'=>'<img src="static/image/feed/thread.gif" alt="thread" class="t" /> ',
		'fid'=>'<img src="static/image/feed/discuz.gif" alt="forum" class="t" /> ',
		'blogid'=>'<img src="static/image/feed/blog.gif" alt="blog" class="t" /> ',
		'gid'=>'<img src="static/image/feed/group.gif" alt="group" class="t" /> ',
		'uid'=>'<img src="static/image/feed/profile.gif" alt="space" class="t" /> ',
		'albumid'=>'<img src="static/image/feed/album.gif" alt="album" class="t" /> ',
		'aid'=>'<img src="static/image/feed/article.gif" alt="article" class="t" /> ',
	);
	$articles = array();
	foreach(C::t('home_favorite')->fetch_all_by_uid_idtype($_G['uid'], $idtype, $favid, $start,$perpage) as $value) {
		$value['icon'] = isset($icons[$value['idtype']]) ? $icons[$value['idtype']] : '';
		$value['url'] = makeurl($value['id'], $value['idtype'], $value['spaceuid']);
		$value['description'] = !empty($value['description']) ? nl2br($value['description']) : '';
		$list[$value['favid']] = $value;
		if($value['idtype'] == 'aid') {
			$articles[$value['favid']] = $value['id'];
		}
	}
	if(!empty($articles)) {
		include_once libfile('function/portal');
		$_urls = array();
		foreach(C::t('portal_article_title')->fetch_all($articles) as $aid => $article) {
			$_urls[$aid] = fetch_article_url($article);
		}
		foreach ($articles as $favid => $aid) {
			$list[$favid]['url'] = $_urls[$aid];
		}
	}
}

$multi = multi($count, $perpage, $page, $theurl);

dsetcookie('home_diymode', $diymode);

if(!$_GET['type']) {
	$_GET['type'] = 'all';
}
if($_GET['type'] == 'group') {
	$navtitle = lang('core', 'title_group_favorite', array('gorup' => $_G['setting']['navs'][3]['navname']));
} else {
	$navtitle = lang('core', 'title_'.$_GET['type'].'_favorite');
}

include_once template("diy:home/space_favorite");

function makeurl($id, $idtype, $spaceuid=0) {
	$url = '';
	switch($idtype) {
		case 'tid':
			$url = 'forum.php?mod=viewthread&tid='.$id;
			break;
		case 'fid':
			$url = 'forum.php?mod=forumdisplay&fid='.$id;
			break;
		case 'blogid':
			$url = 'home.php?mod=space&uid='.$spaceuid.'&do=blog&id='.$id;
			break;
		case 'gid':
			$url = 'forum.php?mod=group&fid='.$id;
			break;
		case 'uid':
			$url = 'home.php?mod=space&uid='.$id;
			break;
		case 'albumid':
			$url = 'home.php?mod=space&uid='.$spaceuid.'&do=album&id='.$id;
			break;
		case 'aid':
			$url = 'portal.php?mod=view&aid='.$id;
			break;
	}
	return $url;
}

?>