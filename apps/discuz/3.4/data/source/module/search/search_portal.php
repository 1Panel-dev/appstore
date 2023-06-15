<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_portal.php 33522 2013-06-28 02:58:15Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

require_once libfile('function/home');
require_once libfile('function/portal');
if(!$_G['setting']['search']['portal']['status']) {
	showmessage('search_portal_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 1)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['portal']['searchctrl'] = intval($_G['setting']['search']['portal']['searchctrl']);

$srchmod = 1;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;


$srchtxt = $_GET['srchtxt'];

$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/portal');

} else {

	$orderby = in_array($_GET['orderby'], array('aid')) ? $_GET['orderby'] : 'aid';
	$ascdesc = isset($_GET['ascdesc']) && $_GET['ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		$page = max(1, intval($_GET['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = C::t('common_searchindex')->fetch_by_searchid_srchmod($searchid, $srchmod);
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = dhtmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$articlelist = array();
		$query = C::t('portal_article_title')->fetch_all_for_search(explode(',', $index['ids']), $orderby, $ascdesc, $start_limit, $_G['tpp']);
		foreach($query as $article) {
			$article['dateline'] = dgmdate($article['dateline']);
			$article['pic'] = $article['pic'] ? pic_get($article['pic'], '', $article['thumb'], $article['remote'], 1, 1) : '';
			$article['title'] = bat_highlight($article['title'], $keyword);
			$article['summary'] = bat_highlight($article['summary'], $keyword);
			$articlelist[] = $article;
		}

		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=portal&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=portal&'.$_SERVER['QUERY_STRING'];

		include template('search/portal');

	} else {

		!($_G['group']['exempt'] & 2) && checklowerlimit('search');

		$searchstring = 'portal|title|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['portal']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=portal', array('searchctrl' => $_G['setting']['search']['portal']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			if(!$srchtxt) {
				dheader('Location: search.php?mod=portal');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['portal']['maxspm']) {
				if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['portal']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=portal', array('maxspm' => $_G['setting']['search']['portal']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['portal']['maxsearchresults'] = $_G['setting']['search']['portal']['maxsearchresults'] ? intval($_G['setting']['search']['portal']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "title LIKE '%{text}%'", true);
			$query = C::t('portal_article_title')->fetch_all_by_sql(' 1 '.$srchtxtsql, 'ORDER BY aid DESC ', 0, $_G['setting']['search']['portal']['maxsearchresults']);
			foreach($query as $article) {
				$ids .= ','.$article['aid'];
				$num++;
			}

			$keywords = str_replace('%', '+', $srchtxt);
			$expiration = TIMESTAMP + $cachelife_text;

			$searchid = C::t('common_searchindex')->insert(array(
				'srchmod' => $srchmod,
				'keywords' => $keywords,
				'searchstring' => $searchstring,
				'useip' => $_G['clientip'],
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'expiration' => $expiration,
				'num' => $num,
				'ids' => $ids
			), true);

			!($_G['portal']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=portal&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>