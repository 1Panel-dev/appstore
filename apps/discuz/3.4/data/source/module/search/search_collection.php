<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_collection.php 31371 2012-08-21 01:51:24Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');

if(!$_G['setting']['search']['collection']['status']) {
	showmessage('search_collection_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 64)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['collection']['searchctrl'] = intval($_G['setting']['search']['collection']['searchctrl']);

$srchmod = 7;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;

$srchtxt = $_GET['srchtxt'];
$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/collection');

} else {

	$orderby = in_array($_GET['orderby'], array('follownum', 'threadnum', 'commentnum', 'dateline')) ? $_GET['orderby'] : 'dateline';
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

		require_once libfile('function/discuzcode');

		$collectionlist = array();
		$maxcollection = $nowcollection = 0;
		$query = C::t('forum_collection')->fetch_all(explode(',', $index['ids']), $orderby, $ascdesc, $start_limit, $_G['tpp']);
		foreach($query as $value) {
			$value['lastupdate'] = dgmdate($value['lastupdate']);
			$value['shortdesc'] = cutstr(strip_tags(discuzcode($value['desc'])), 50);
			$value['name'] = bat_highlight($value['name'], $keyword);
			$collectionlist[$value['ctid']] = $value;
		}

		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=collection&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=collection&'.$_SERVER['QUERY_STRING'];

		include template('search/collection');

	} else {

		$searchstring = 'collection|title|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['collection']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=collection', array('searchctrl' => $_G['setting']['search']['collection']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=collection');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['collection']['maxspm']) {
				if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['collection']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=collection', array('maxspm' => $_G['setting']['search']['collection']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['collection']['maxsearchresults'] = $_G['setting']['search']['collection']['maxsearchresults'] ? intval($_G['setting']['search']['collection']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "name LIKE '%{text}%' OR keyword LIKE '%{text}%'", true);
			$query = C::t('forum_collection')->fetch_ctid_by_searchkey($srchtxtsql, $_G['setting']['search']['collection']['maxsearchresults']);
			foreach($query as $collection) {
				$ids .= ','.$collection['ctid'];
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

		dheader("location: search.php?mod=collection&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>