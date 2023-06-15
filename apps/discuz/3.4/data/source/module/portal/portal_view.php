<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_view.php 33660 2013-07-29 07:51:05Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
if(empty($aid)) {
	header("HTTP/1.1 404 Not Found");
	showmessage('view_no_article_id');
}
$article = C::t('portal_article_title')->fetch($aid);
require_once libfile('function/portalcp');
$categoryperm = getallowcategory($_G['uid']);

if(empty($article) || ($article['status'] > 0 && $article['uid'] != $_G['uid'] && !$_G['group']['allowmanagearticle'] && empty($categoryperm[$article['catid']]['allowmanage']) && $_G['adminid'] != 1 && $_GET['modarticlekey'] != modauthkey($article['aid']))) {
	header("HTTP/1.1 404 Not Found");
	showmessage('view_article_no_exist');
}

if(!empty($_G['setting']['antitheft']['allow']) && empty($_G['setting']['antitheft']['disable']['article']) && empty($_G['cache']['portalcategory'][$article['catid']]['noantitheft'])) {
	helper_antitheft::check($aid, 'aid');
}

if(!empty($_G['setting']['makehtml']['flag']) && $article['htmlmade'] && !isset($_G['makehtml']) && empty($_GET['diy']) && empty($article['url'])) {
	dheader('location:'. fetch_article_url($article));
}
$article_count = C::t('portal_article_count')->fetch($aid);
if($article_count) $article = array_merge($article_count, $article);

if($article_count) {
	C::t('portal_article_count')->increase($aid, array('viewnum'=>1));
	unset($article_count);
} else {
	C::t('portal_article_count')->insert(array(
		'aid'=>$aid,
		'catid'=>$article['catid'],
		'viewnum'=>1));
}

if($article['url']) {
	if(!isset($_G['makehtml'])) {
		dheader("location:{$article['url']}");
	}
	exit();
}


$cat = category_remake($article['catid']);

$article['pic'] = pic_get($article['pic'], '', $article['thumb'], $article['remote'], 1, 1);

$page = intval($_GET['page']);
if($page<1) $page = 1;

$content = $contents = array();
$multi = '';

$content = C::t('portal_article_content')->fetch_by_aid_page($aid, $page);

if($article['contents'] && $article['showinnernav']) {
	foreach(C::t('portal_article_content')->fetch_all($aid) as $value) {
		$contents[] = $value;
	}
	if(empty($contents)) {
		C::t('portal_article_content')->update($aid, array('showinnernav' => '0'));
	}
}

require_once libfile('function/blog');
$content['content'] = blog_bbcode($content['content']);

if(!empty($_G['setting']['makehtml']['flag']) && $article['htmlmade']) {
	$_caturl = $_G['cache']['portalcategory'][$cat['topid']]['domain'] ? $_G['cache']['portalcategory'][$cat['topid']]['caturl'] : '';
	$viewurl = $_caturl.$article['htmldir'].$article['htmlname'].'{page}.'.$_G['setting']['makehtml']['extendname'];
	unset($_caturl);
} else {
	$viewurl = "portal.php?mod=view&aid=$aid";
}

$multi = multi($article['contents'], 1, $page, $viewurl);
$org = array();
if($article['idtype'] == 'tid' || $content['idtype']=='pid') {
	$thread = $firstpost = array();
	require_once libfile('function/discuzcode');
	require_once libfile('function/forum');
	$thread = get_thread_by_tid($article[id]);
	if(!empty($thread)) {
		if($content['idtype']=='pid') {
			$firstpost = C::t('forum_post')->fetch($thread['posttableid'], $content['id']);
		} else {
			$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($article['id']);
		}
		if($firstpost && $firstpost['tid'] == $article['id']) {
			$firstpost['uid'] = $firstpost['authorid'];
			$firstpost['username'] = $firstpost['author'];
		}
	}
	if(!empty($firstpost) && !empty($thread) && $thread['displayorder'] != -1) {
		$_G['tid'] = $article['id'];
		$aids = array();
		$firstpost['message'] = $content['content'];
		if($thread['attachment']) {
			$_G['group']['allowgetimage'] = 1;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $firstpost['message'], $matchaids)) {
				$aids = $matchaids[1];
			}
		}

		if($aids) {
			parseforumattach($firstpost, $aids);
		}
		$content['content'] = $firstpost['message'];
		$content['pid'] = $firstpost['pid'];

		$org = $firstpost;
		$org_url = "forum.php?mod=viewthread&tid=$article[id]";
	} else {
		C::t('portal_article_title')->update($aid, array('id' => 0, 'idtype' => ''));
		C::t('portal_article_content')->update_by_aid($aid, array('id' => 0, 'idtype' => ''));
	}
} elseif($article['idtype']=='blogid') {
	$org = C::t('home_blog')->fetch($article['id']);
	if(empty($org)) {
		C::t('portal_article_title')->update($aid, array('id' => 0, 'idtype' => ''));
		dheader('location: '.  fetch_article_url($article));
		exit();
	}
}

$article['related'] = array();
if(($relateds = C::t('portal_article_related')->fetch_all_by_aid($aid))) {
	foreach(C::t('portal_article_title')->fetch_all(array_keys($relateds)) as $raid => $value) {
		$value['uri'] = fetch_article_url($value);
		$article['related'][$raid] = $value;
	}
}
$article['allowcomment'] = !empty($cat['allowcomment']) && !empty($article['allowcomment']) ? 1 : 0;
$_G['catid'] = $_GET['catid'] = $article['catid'];
$common_url = '';
$commentlist = array();
if($article['allowcomment']) {

	if($org && empty($article['owncomment'])) {

		if($article['idtype'] == 'blogid') {

			$common_url = "home.php?mod=space&uid=$org[uid]&do=blog&id=$article[id]";
			$form_url = "home.php?mod=spacecp&ac=comment";

			$article['commentnum'] = C::t('home_comment')->count_by_id_idtype($article['id'], 'blogid');
			if($article['commentnum']) {
				$query = C::t('home_comment')->fetch_all_by_id_idtype($article['id'], 'blogid', 0, 20, '', 'DESC');
				foreach($query as $value) {
					if($value['status'] == 0 || $_G['adminid'] == 1 || $value['uid'] == $_G['uid']) {
						$commentlist[] = $value;
					}
				}
			}

		} elseif($article['idtype'] == 'tid') {

			$common_url = "forum.php?mod=viewthread&tid=$article[id]";
			$form_url = "forum.php?mod=post&action=reply&tid=$article[id]&replysubmit=yes&infloat=yes&handlekey=fastpost";

			require_once libfile('function/discuzcode');
			$posttable = empty($thread['posttable']) ? getposttablebytid($article['id']) : $thread['posttable'];
			$_G['tid'] = $article['id'];
			$article['commentnum'] = getcount($posttable, array('tid'=>$article['id'], 'first'=>'0'));

			if($article['allowcomment'] && $article['commentnum']) {
				$attachpids = $attachtags = array();
				$_G['group']['allowgetattach'] = $_G['group']['allowgetimage'] = 1;
				foreach(C::t('forum_post')->fetch_all_by_tid('tid:'.$article['id'], $article['id'], true, 'ASC', 0, 20, null, 0) as $value) {
					$value['uid'] = $value['authorid'];
					$value['username'] = $value['author'];
					if($value['status'] != 1 && !$value['first']) {
						$value['message'] = discuzcode($value['message'], $value['smileyoff'], $value['bbcodeoff'], $value['htmlon']);
						$value['cid'] = $value['pid'];
						$commentlist[$value['pid']] = $value;
						if($value['attachment']) {
							$attachpids[] = $value['pid'];
							if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $value['message'], $matchaids)) {
								$attachtags[$value['pid']] = $matchaids[1];
							}
						}
					}
				}

				if($attachpids) {
					require_once libfile('function/attachment');
					parseattach($attachpids, $attachtags, $commentlist);
				}
			}
		}

	} else {

		$common_url = "portal.php?mod=comment&id=$aid&idtype=aid";
		$form_url = "portal.php?mod=portalcp&ac=comment";

		$query = C::t('portal_comment')->fetch_all_by_id_idtype($aid, 'aid', 'dateline', 'DESC', 0, 20);
		$pricount = 0;
		foreach($query as $value) {
			if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
				$value['allowop'] = 1;
				$commentlist[] = $value;
			} else {
				$pricount += 1;
			}
		}
	}
}

$hash = md5($article['uid']."\t".$article['dateline']);
$id = $article['aid'];
$idtype = 'aid';

loadcache('click');
$clicks = empty($_G['cache']['click']['aid'])?array():$_G['cache']['click']['aid'];
$maxclicknum = 0;
foreach ($clicks as $key => $value) {
	$value['clicknum'] = $article["click{$key}"];
	$value['classid'] = mt_rand(1, 4);
	if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
	$clicks[$key] = $value;
}

$clickuserlist = array();
foreach(C::t('home_clickuser')->fetch_all_by_id_idtype($id, $idtype, 0, 24) as $value) {
	$value['clickname'] = $clicks[$value['clickid']]['name'];
	$clickuserlist[] = $value;
}

$article['timestamp'] = $article['dateline'];
$article['dateline'] = dgmdate($article['dateline']);

foreach($cat['ups'] as $val) {
	$cats[] = $val['catname'];
}
$seodata = array('firstcat' => $cats[0], 'secondcat' => $cats[1], 'curcat' => $cat['catname'], 'subject' => $article['title'], 'user' => $article['username'], 'summary' => $article['summary'], 'page' => intval($_GET['page']));
list($navtitle, $metadescription, $metakeywords) = get_seosetting('article', $seodata);
if(empty($navtitle)) {
	$navtitle = helper_seo::get_title_page($article['title'], $_G['page']).' - '.$cat['catname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(empty($metakeywords)) {
	$metakeywords = $article['title'];
}
if(empty($metadescription)) {
	$metadescription = $article['summary'] ? $article['summary'] : $article['title'];
}

list($seccodecheck, $secqaacheck) = seccheck('publish');

$catid = $article['catid'];
if(!$_G['setting']['relatedlinkstatus']) {
	$_G['relatedlinks'] = get_related_link('article');
} else {
	$content['content'] = parse_related_link($content['content'], 'article');
}
if(isset($_G['makehtml'])) {
	helper_makehtml::portal_article($cat, $article, $page);
}
portal_get_per_next_article($article);
$tpldirectory = '';
$articleprimaltplname = $cat['articleprimaltplname'];
if(strpos($articleprimaltplname, ':') !== false) {
	list($tpldirectory, $articleprimaltplname) = explode(':', $articleprimaltplname);
}
include_once template("diy:portal/view:{$catid}", NULL, $tpldirectory, NULL, $articleprimaltplname);

function parseforumattach(&$post, $aids) {
	global $_G;
	if(($aids = array_unique($aids))) {
		require_once libfile('function/attachment');
		$finds = $replaces = array();
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'aid', $aids) as $attach) {

			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
			$attach['dateline'] = dgmdate($attach['dateline'], 'u');
			$extension = strtolower(fileext($attach['filename']));
			$attach['ext'] = $extension;
			$attach['imgalt'] = $attach['isimage'] ? strip_tags(str_replace('"', '\"', $attach['description'] ? $attach['description'] : $attach['filename'])) : '';
			$attach['attachicon'] = attachtype($extension."\t".$attach['filetype']);
			$attach['attachsize'] = sizecount($attach['filesize']);

			$attach['refcheck'] = (!$attach['remote'] && $_G['setting']['attachrefcheck']) || ($attach['remote'] && ($_G['setting']['ftp']['hideurl'] || ($attach['isimage'] && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp')));
			$aidencode = packaids($attach);
			$widthcode = attachwidth($attach['width']);
			$is_archive = $_G['forum_thread']['is_archived'] ? "&fid=".$_G['fid']."&archiveid=".$_G['forum_thread']['archiveid'] : '';
			if($attach['isimage']) {
				$attachthumb = getimgthumbname($attach['attachment']);
					if($_G['setting']['thumbstatus'] && $attach['thumb']) {
						$replaces[$attach['aid']] = "<a href=\"javascript:;\"><img id=\"_aimg_$attach[aid]\" aid=\"$attach[aid]\" onclick=\"zoom(this, this.getAttribute('zoomfile'), 0, 0, '{$_G[forum][showexif]}')\"
						zoomfile=\"".($attach['refcheck']? "forum.php?mod=attachment{$is_archive}&aid=$aidencode&noupdate=yes&nothumb=yes" : $attach['url'].$attach['attachment'])."\"
						src=\"".($attach['refcheck'] ? "forum.php?mod=attachment{$is_archive}&aid=$aidencode" : $attach['url'].$attachthumb)."\" alt=\"$attach[imgalt]\" title=\"$attach[imgalt]\" w=\"$attach[width]\" /></a>";
					} else {
						$replaces[$attach['aid']] = "<img id=\"_aimg_$attach[aid]\" aid=\"$attach[aid]\"
						zoomfile=\"".($attach['refcheck'] ? "forum.php?mod=attachment{$is_archive}&aid=$aidencode&noupdate=yes&nothumb=yes" : $attach['url'].$attach['attachment'])."\"
						src=\"".($attach['refcheck'] ? "forum.php?mod=attachment{$is_archive}&aid=$aidencode&noupdate=yes " : $attach['url'].$attach['attachment'])."\" $widthcode alt=\"$attach[imgalt]\" title=\"$attach[imgalt]\" w=\"$attach[width]\" />";
					}
			} else {
				$replaces[$attach['aid']] = "$attach[attachicon]<a href=\"forum.php?mod=attachment{$is_archive}&aid=$aidencode\" onmouseover=\"showMenu({'ctrlid':this.id,'pos':'12'})\" id=\"aid$attach[aid]\" target=\"_blank\">$attach[filename]</a>";
			}
			$finds[$attach['aid']] = '[attach]'.$attach['aid'].'[/attach]';
		}
		if($finds && $replaces) {
			$post['message'] = str_ireplace($finds, $replaces, $post['message']);
		}
	}
}

function portal_get_per_next_article(&$article) {
	$data = array();
	$aids = array();
	if($article['preaid']) {
		$aids[$article['preaid']] = $article['preaid'];
	}
	if($article['nextaid']) {
		$aids[$article['nextaid']] = $article['nextaid'];
	}
	if($aids) {
		$data = C::t('portal_article_title')->fetch_all($aids);
		foreach ($data as $aid => &$value) {
			$value['url'] = fetch_article_url($value);
		}
	}
	if($data[$article['preaid']]) {
		$article['prearticle'] = $data[$article['preaid']];
	}
	if($data[$article['nextaid']]) {
		$article['nextarticle'] = $data[$article['nextaid']];
	}
}
?>