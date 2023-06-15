<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_category.php 30378 2012-05-24 09:52:46Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['portalstatus']) {
	dheader('location:portal.php?mod=portalcp&ac=portalblock');
}

require_once libfile('function/portalcp');

$catid = max(0,intval($_GET['catid']));
$perpagearr = array(20, 30,40, 50, 100);
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array('unrecommend', 'recommended', 'me')) ? $_GET['type'] : 'all';
$perpage = isset($_GET['perpage']) && in_array($_GET['perpage'], $perpagearr) ? $_GET['perpage'] : 20;
$typearr[$_GET['type']] = 'class="a"';
$theurl = "portal.php?mod=portalcp&ac=category&catid=$catid&perpage=$perpage&type=$_GET[type]&formhash=".FORMHASH."&searchkey=".urlencode($_GET['searchkey']);

$allowmanage = checkperm('allowmanagearticle');
$allowpost = checkperm('allowpostarticle');
$catids = $wherearr = array();
$category = $_G['cache']['portalcategory'];
$permission = getallowcategory($_G['uid']);
if($catid) {
	if (!$allowmanage && !$allowpost && empty($permission[$catid]['allowmanage']) && empty($permission[$catid]['allowpublish'])) {
		showmessage('portal_nopermission');
	}
	$cate = $category[$catid];
	if(empty($cate)) {
		showmessage('article_category_empty');
	}
	$catids = category_get_childids('portal', $catid);
	$catids[] = $catid;

} else {
	$catids = array_keys($permission);
	if (!$allowmanage && !$allowpost && empty($catids)) {
		showmessage('portal_nopermission');
	}
}

if($_GET['type'] == 'me' || (!$admincp2 && !$allowmanage)) {
	$wherearr[] = " uid='$_G[uid]'";
}
if($catids) {
	$wherearr[] = " catid IN (".dimplode($catids).")";
}
if($_GET['searchkey']) {
	$_GET['searchkey'] = addslashes(stripsearchkey($_GET['searchkey']));
	$wherearr[] = "title LIKE '%$_GET[searchkey]%'";
	$_GET['searchkey'] = dhtmlspecialchars($_GET['searchkey']);
}
if($_GET['type'] == 'recommended') {
	$wherearr[] = "bid != ''";
} elseif($_GET['type'] == 'unrecommend') {
	$wherearr[] = "bid = ''";
}
$wheresql = implode(' AND ', $wherearr);

$page = max(1,intval($_GET['page']));
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

$list = array();
$multi = '';
$article_tags = article_tagnames();
$count = C::t('portal_article_title')->fetch_all_by_sql($wheresql, '', 0, 0, 1);
if($count) {

	$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY dateline DESC', $start, $perpage);
	foreach($query as $value) {
		if($value['pic']) $value['pic'] = pic_get($value['pic'], 'portal', $value['thumb'], $value['remote']);
		$value['dateline'] = dgmdate($value['dateline']);
		$value['allowmanage'] = ($allowmanage || !empty($permission[$value['catid']]['allowmanage'])) ? true : false;
		$value['allowpublish'] = ($value['allowmanage'] || $allowpost || !empty($permission[$value['catid']]['allowpublish'])) ? true : false;
		$value['taghtml'] = '';
		$tags = article_parse_tags($value['tag']);
		foreach($tags as $k=>$v) {
			if($v) {
				$value['taghtml'] .= "[{$article_tags[$k]}] ";
			}
		}
		$style = array();
		if($value['highlight']) {
			$style = explode('|', $value['highlight']);
			$value['highlight'] = ' style="';
			$value['highlight'] .= $style[0] ? 'color: '.$style[0].';' : '';
			$value['highlight'] .= $style[1] ? 'font-weight: bold;' : '';
			$value['highlight'] .= $style[2] ? 'font-style: italic;' : '';
			$value['highlight'] .= $style[3] ? 'text-decoration: underline;' : '';
			$value['highlight'] .= '"';
		}
		$list[] = $value;
	}

	$multi = multi($count, $perpage, $page, $theurl);
	$categoryselect = category_showselect('portal', 'catid', false, $catid);
}

include_once template("portal/portalcp_category");


?>