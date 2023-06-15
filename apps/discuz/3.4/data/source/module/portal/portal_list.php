<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_list.php 33245 2013-05-09 02:02:59Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['catid'] = $catid = max(0,intval($_GET['catid']));
if(empty($catid)) {
	showmessage('list_choose_category', dreferer());
}
$portalcategory = &$_G['cache']['portalcategory'];
$cat = $portalcategory[$catid];

if(empty($cat)) {
	showmessage('list_category_noexist', dreferer());
}
require_once libfile('function/portalcp');
$categoryperm = getallowcategory($_G['uid']);
if($cat['closed'] && !$_G['group']['allowdiy'] && !$categoryperm[$catid]['allowmanage']) {
	showmessage('list_category_is_closed', dreferer());
}

if(!isset($_G['makehtml'])) {
	if(!empty($cat['url']))	dheader('location:'.$cat['url']);
	if(defined('SUB_DIR') && $_G['siteurl']. substr(SUB_DIR, 1) != $cat['caturl'] || !defined('SUB_DIR') && $_G['siteurl'] != substr($cat['caturl'], 0, strrpos($cat['caturl'], '/')+1)) {
		dheader('location:'.$cat['caturl'], true, '301');
	}
}

$cat = category_remake($catid);
$navid = 'mn_P'.$cat['topid'];
foreach ($_G['setting']['navs'] as $navsvalue) {
	if($navsvalue['navid'] == $navid && $navsvalue['available'] && $navsvalue['level'] == 0) {
		$_G['mnid'] = $navid;
		break;
	}
}
$page = max(1, intval($_GET['page']));
foreach($cat['ups'] as $val) {
	$cats[] = $val['catname'];
}

$bodycss = array($cat['topid'] => 'pg_list_'.$cat['topid']);
if($cat['upid']) {
	$bodycss[$cat['upid']] = 'pg_list_'.$cat['upid'];
}
$bodycss[$cat['catid']] = 'pg_list_'.$cat['catid'];
$cat['bodycss'] = implode(' ', $bodycss);

$catseoset = array(
	'seotitle' => $cat['seotitle'],
	'seokeywords' => $cat['keyword'],
	'seodescription' => $cat['description']
);
$seodata = array('firstcat' => $cats[0], 'secondcat' => $cats[1], 'curcat' => $cat['catname'], 'page' => intval($_GET['page']));
list($navtitle, $metadescription, $metakeywords) = get_seosetting('articlelist', $seodata, $catseoset);
if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($cat['catname'], $_G['page']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = $cat['catname'];
}
if(!$metadescription) {
	$metadescription = $cat['catname'];
}

if(isset($_G['makehtml'])){
	helper_makehtml::portal_list($cat);
}

$file = 'portal/list:'.$catid;
$tpldirectory = '';
$primaltplname = $cat['primaltplname'];
if(strpos($primaltplname, ':') !== false) {
	list($tpldirectory, $primaltplname) = explode(':', $primaltplname);
}
include template('diy:'.$file, NULL, $tpldirectory, NULL, $primaltplname);


function category_get_wheresql($cat) {
	$wheresql = '';
	if(is_array($cat)) {
		$catid = $cat['catid'];
		if(!empty($cat['subs'])) {
			include_once libfile('function/portalcp');
			$subcatids = category_get_childids('portal', $catid);
			$subcatids[] = $catid;

			$wheresql = "at.catid IN (".dimplode($subcatids).")";
		} else {
			$wheresql = "at.catid='$catid'";
		}
	}
	$wheresql .= " AND at.status='0'";
	return $wheresql;
}

function category_get_list($cat, $wheresql, $page = 1, $perpage = 0) {
	global $_G;
	$cat['perpage'] = empty($cat['perpage']) ? 15 : $cat['perpage'];
	$cat['maxpages'] = empty($cat['maxpages']) ? 1000 : $cat['maxpages'];
	$perpage = intval($perpage);
	$page = intval($page);
	$perpage = empty($perpage) ? $cat['perpage'] : $perpage;
	$page = empty($page) ? 1 : min($page, $cat['maxpages']);
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;
	$list = array();
	$pricount = 0;
	$multi = '';
	$count = C::t('portal_article_title')->fetch_all_by_sql($wheresql, '', 0, 0, 1, 'at');
	if($count) {
		$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY at.dateline DESC', $start, $perpage, 0, 'at');
		foreach($query as $value) {
			$value['catname'] = $value['catid'] == $cat['catid'] ? $cat['catname'] : $_G['cache']['portalcategory'][$value['catid']]['catname'];
			$value['onerror'] = '';
			if($value['pic']) {
				$value['pic'] = pic_get($value['pic'], '', $value['thumb'], $value['remote'], 1, 1);
			}
			$value['dateline'] = dgmdate($value['dateline']);
			if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
				$list[] = $value;
			} else {
				$pricount++;
			}
		}
		if(strpos($cat['caturl'], 'portal.php') === false) {
			$cat['caturl'] .= 'index.php';
		}
		$multi = multi($count, $perpage, $page, $cat['caturl'], $cat['maxpages']);
	}
	return $return = array('list'=>$list,'count'=>$count,'multi'=>$multi,'pricount'=>$pricount);
}

function category_get_list_more($cat, $wheresql, $hassub = true,$hasnew = true,$hashot = true) {
	global $_G;
	$data = array();
	$catid = $cat['catid'];

	$cachearr = array();
	if($hashot) $cachearr[] = 'portalhotarticle';
	if($hasnew) $cachearr[] = 'portalnewarticle';

	if($hassub) {
		foreach($cat['children'] as $childid) {
			$cachearr[] = 'subcate'.$childid;
		}
	}

	$allowmemory = memory('check');
	foreach ($cachearr as $key) {
		$cachekey = $key.$catid;
		$data[$key] = $allowmemory ? memory('get', $cachekey) : false;
		if($data[$key] === false) {
			$list = array();
			$sql = '';
			if($key == 'portalhotarticle') {
				$dateline = TIMESTAMP - 3600 * 24 * 90;
				$query = C::t('portal_article_count')->fetch_all_hotarticle($wheresql, $dateline);
			} elseif($key == 'portalnewarticle') {
				$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql, 'ORDER BY at.dateline DESC', 0, 10, 0, 'at');
			} elseif(substr($key, 0, 7) == 'subcate') {
				$cacheid = intval(str_replace('subcate', '', $key));
				if(!empty($_G['cache']['portalcategory'][$cacheid])) {
					$where = '';
					if(!empty($_G['cache']['portalcategory'][$cacheid]['children']) && dimplode($_G['cache']['portalcategory'][$cacheid]['children'])) {
						$_G['cache']['portalcategory'][$cacheid]['children'][] = $cacheid;
						$where = 'at.catid IN ('.dimplode($_G['cache']['portalcategory'][$cacheid]['children']).')';
					} else {
						$where = 'at.catid='.$cacheid;
					}
					$where .= " AND at.status='0'";
					$query = C::t('portal_article_title')->fetch_all_by_sql($where, 'ORDER BY at.dateline DESC', 0, 10, 0, 'at');
				}
			}

			if($query) {

				foreach($query as $value) {
					$value['catname'] = $value['catid'] == $cat['catid'] ? $cat['catname'] : $_G['cache']['portalcategory'][$value['catid']]['catname'];
					if($value['pic']) $value['pic'] = pic_get($value['pic'], '', $value['thumb'], $value['remote'], 1, 1);
					$value['timestamp'] = $value['dateline'];
					$value['dateline'] = dgmdate($value['dateline']);
					$list[] = $value;
				}
			}

			$data[$key] = $list;
			if($allowmemory) {
				memory('set', $cachekey, $list, empty($list) ? 60 : 600);
			}
		}
	}
	return $data;
}

function article_title_style($value = array()) {

	$style = array();
	$highlight = '';
	if($value['highlight']) {
		$style = explode('|', $value['highlight']);
		$highlight = ' style="';
		$highlight .= $style[0] ? 'color: '.$style[0].';' : '';
		$highlight .= $style[1] ? 'font-weight: bold;' : '';
		$highlight .= $style[2] ? 'font-style: italic;' : '';
		$highlight .= $style[3] ? 'text-decoration: underline;' : '';
		$highlight .= '"';
	}
	return $highlight;

}
?>