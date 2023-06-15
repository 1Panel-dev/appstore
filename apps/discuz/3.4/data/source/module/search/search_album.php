<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_album.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');

if(!$_G['setting']['search']['album']['status']) {
	showmessage('search_album_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 8)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['album']['searchctrl'] = intval($_G['setting']['search']['album']['searchctrl']);

$srchmod = 4;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;

$srchtxt = $_GET['srchtxt'];
$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/album');

} else {

	$orderby = in_array($_GET['orderby'], array('dateline', 'replies', 'views')) ? $_GET['orderby'] : 'lastpost';
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

		$albumlist = array();
		$maxalbum = $nowalbum = 0;
		$query = C::t('home_album')->fetch_all(explode(',', $index['ids']), 'updatetime', $start_limit, $_G['tpp']);
		foreach($query as $value) {
			if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
				$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
			} elseif ($value['picnum']) {
				$value['pic'] = STATICURL.'image/common/nopublish.jpg';
			} else {
				$value['pic'] = '';
			}
			$value['albumname'] = bat_highlight($value['albumname'], $keyword);
			$albumlist[$value['albumid']] = $value;
		}

		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=album&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=album&'.$_SERVER['QUERY_STRING'];

		include template('search/album');

	} else {

		$searchstring = 'album|title|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['album']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=album', array('searchctrl' => $_G['setting']['search']['album']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=album');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['album']['maxspm']) {
				if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['album']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=album', array('maxspm' => $_G['setting']['search']['album']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['album']['maxsearchresults'] = $_G['setting']['search']['album']['maxsearchresults'] ? intval($_G['setting']['search']['album']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "albumname LIKE '%{text}%'", true);
			$query = C::t('home_album')->fetch_albumid_by_searchkey($srchtxtsql, $_G['setting']['search']['album']['maxsearchresults']);
			foreach($query as $album) {
				$ids .= ','.$album['albumid'];
				$num++;
			}
			unset($query);

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

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=album&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>