<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_forum.php 33198 2013-05-06 09:23:45Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if(!$_G['setting']['search']['forum']['status']) {
	showmessage('search_forum_closed');
}

if(in_array($_G['adminid'], array(0, -1)) && !($_G['group']['allowsearch'] & 2)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['forum']['searchctrl'] = intval($_G['setting']['search']['forum']['searchctrl']);

require_once libfile('function/forumlist');
require_once libfile('function/forum');
require_once libfile('function/post');
loadcache(array('forums', 'posttable_info'));
$posttableselect = '';
if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
	$posttableselect = '<select name="seltableid" id="seltableid" class="ps" style="display:none">';
	foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
		$posttableselect .= '<option value="'.$posttableid.'"'.($_GET['posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
	}
	$posttableselect .= '</select>';
}

$srchmod = 2;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_GET['srchtype']) ? '' : trim($_GET['srchtype']);
$searchid = isset($_GET['searchid']) ? intval($_GET['searchid']) : 0;
$seltableid = intval($_GET['seltableid']);

if($srchtype != 'title' && $srchtype != 'fulltext') {
	$srchtype = '';
}

$srchtxt = trim($_GET['srchtxt']);
$srchuid = intval($_GET['srchuid']);
$srchuname = isset($_GET['srchuname']) ? trim(str_replace('|', '', $_GET['srchuname'])) : '';;
$srchfrom = intval($_GET['srchfrom']);
$before = intval($_GET['before']);
$srchfid = $_GET['srchfid'];
$srhfid = intval($_GET['srhfid']);

$keyword = isset($srchtxt) ? dhtmlspecialchars(trim($srchtxt)) : '';

$forumselect = forumselect();
if(!empty($srchfid) && !is_numeric($srchfid)) {
	$forumselect = str_replace('<option value="'.$srchfid.'">', '<option value="'.$srchfid.'" selected="selected">', $forumselect);
}

if(!submitcheck('searchsubmit', 1)) {

	if($_GET['adv']) {
		include template('search/forum_adv');
	} else {
		include template('search/forum');
	}

} else {
	$orderby = in_array($_GET['orderby'], array('dateline', 'replies', 'views')) ? $_GET['orderby'] : 'lastpost';
	$ascdesc = isset($_GET['ascdesc']) && $_GET['ascdesc'] == 'asc' ? 'asc' : 'desc';
	$orderbyselected = array($orderby => 'selected="selected"');
	$ascchecked = array($ascdesc => 'checked="checked""');

	if(!empty($searchid)) {

		require_once libfile('function/misc');

		$page = max(1, intval($_GET['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = C::t('common_searchindex')->fetch_by_searchid_srchmod($searchid, $srchmod);
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = dhtmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$searchstring = explode('|', $index['searchstring']);
		$index['searchtype'] = $searchstring[0];//preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);
		$searchstring[2] = base64_decode($searchstring[2]);
		$srchuname = $searchstring[4];
		$modfid = 0;
		if($keyword) {
			$modkeyword = str_replace(' ', ',', $keyword);
			$fids = explode(',', str_replace('\\\'', '', $searchstring[5]));
			foreach ($fids as $srchfid) {
				if(!empty($srchfid) ) {
					$forumselect = str_replace('<option value="'.$srchfid.'">', '<option value="'.$srchfid.'" selected="selected">', $forumselect);
				}
			}
			if(count($fids) == 1 && in_array($_G['adminid'], array(1,2,3))) {
				$modfid = $fids[0];
				if($_G['adminid'] == 3 && !C::t('forum_moderator')->fetch_uid_by_fid_uid($modfid, $_G['uid'])) {
					$modfid = 0;
				}
			}
		}
		$threadlist = $posttables = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid_fid_displayorder(explode(',',$index['ids']), null, 0, $orderby, $start_limit, $_G['tpp'], '>=', $ascdesc) as $thread) {
			$thread['subject'] = bat_highlight($thread['subject'], $keyword);
			$thread['realtid'] = $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
			$threadlist[$thread['tid']] = procthread($thread, 'dt');
			$posttables[$thread['posttableid']][] = $thread['tid'];
		}
		if($threadlist) {
			foreach($posttables as $tableid => $tids) {
				foreach(C::t('forum_post')->fetch_all_by_tid($tableid, $tids, true, '', 0, 0, 1) as $post) {
					if($post['status'] & 1) {
						$threadlist[$post['tid']]['message'] = lang('forum/template', 'message_single_banned');
					} else {
						$threadlist[$post['tid']]['message'] = bat_highlight(messagecutstr($post['message'], 200), $keyword);
					}
				}
			}

		}
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=forum&'.$_SERVER['QUERY_STRING'];

		$fulltextchecked = $searchstring[1] == 'fulltext' ? 'checked="checked"' : '';

		$specials = explode(',', $searchstring[9]);
		$srchfilter = $searchstring[8];
		$before = $searchstring[7];
		$srchfrom = $searchstring[6];
		foreach($specials as $key) {
			$specialchecked[$key] = 'checked="checked""';
		}
		$srchfilterchecked[$srchfilter] = 'checked="checked""';
		$beforechecked = array($before => 'checked="checked""');
		$srchfromselected = array($srchfrom => 'selected="selected"');
		$advextra = '&orderby='.$orderby.'&ascdesc='.$ascdesc.'&searchid='.$searchid.'&searchsubmit=yes';
		if($_GET['adv']) {
			include template('search/forum_adv');
		} else {
			include template('search/forum');
		}

	} else {


		if($_G['group']['allowsearch'] & 32 && $srchtype == 'fulltext') {
			periodscheck('searchbanperiods');
		} elseif($srchtype != 'title') {
			$srchtype = 'title';
		}

		$forumsarray = array();
		if(!empty($srchfid)) {
			foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
				if($forum = intval(trim($forum))) {
					$forumsarray[] = $forum;
				}
			}
		}

		$fids = $comma = '';
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				if(!$forumsarray || in_array($fid, $forumsarray)) {
					$fids .= "$comma'$fid'";
					$comma = ',';
				}
			}
		}

		if($_G['setting']['threadplugins'] && $specialplugin) {
			$specialpluginstr = implode("','", $specialplugin);
			$special[] = 127;
		} else {
			$specialpluginstr = '';
		}
		$special = $_GET['special'];
		$specials = $special ? implode(',', $special) : '';
		$srchfilter = in_array($_GET['srchfilter'], array('all', 'digest', 'top')) ? $_GET['srchfilter'] : 'all';

		$searchstring = 'forum|'.$srchtype.'|'.base64_encode($srchtxt).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter.'|'.$specials.'|'.$specialpluginstr.'|'.$seltableid;
		$searchindex = array('id' => 0, 'dateline' => '0');

		foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['forum']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=forum', array('searchctrl' => $_G['setting']['search']['forum']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname && !$srchfrom && !in_array($srchfilter, array('digest', 'top')) && !is_array($special)) {
				dheader('Location: search.php?mod=forum');
			} elseif(isset($srchfid) && !empty($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
				showmessage('search_forum_invalid', 'search.php?mod=forum');
			} elseif(!$fids) {
				showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['forum']['maxspm']) {
				if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['forum']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=forum', array('maxspm' => $_G['setting']['search']['forum']['maxspm']));
				}
			}

			if($srchtype == 'fulltext' && $_G['setting']['sphinxon']) {
				require_once libfile('class/sphinx');

				$s = new SphinxClient();
				$s->setServer($_G['setting']['sphinxhost'], intval($_G['setting']['sphinxport']));
				$s->setMaxQueryTime(intval($_G['setting']['sphinxmaxquerytime']));
				$s->SetRankingMode($_G['setting']['sphinxrank']);
				$s->setLimits(0, intval($_G['setting']['sphinxlimit']), intval($_G['setting']['sphinxlimit']));
				$s->setGroupBy('tid', SPH_GROUPBY_ATTR);

				if($srchfilter == 'digest') {
					$s->setFilterRange('digest', 1, 3, false);
				}
				if($srchfilter == 'top') {
					$s->setFilterRange('displayorder', 1, 2, false);
				} else {
					$s->setFilterRange('displayorder', 0, 2, false);
				}

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';
					if($before) {
						$spx_timemix = 0;
						$spx_timemax = TIMESTAMP - $srchfrom;
					} else {
						$spx_timemix = TIMESTAMP - $srchfrom;
						$spx_timemax = TIMESTAMP;
					}
				} else {
					$uids = array();
					if($srchuname) {
						$uids = array_keys(C::t('common_member')->fetch_all_by_like_username($srchuname, 0, 50));
						if(count($uids) == 0) {
							$uids = array(0);
						}
					} elseif($srchuid) {
						$uids = array($srchuid);
					}
					if(is_array($uids) && count($uids) > 0) {
						$s->setFilter('authorid', $uids, false);
					}

					if($srchtxt) {
						if(preg_match("/\".*\"/", $srchtxt)) {
							$spx_matchmode = "PHRASE";
							$s->setMatchMode(SPH_MATCH_PHRASE);
						} elseif(preg_match("(AND|\+|&|\s)", $srchtxt) && !preg_match("(OR|\|)", $srchtxt)) {
							$srchtxt = preg_replace("/( AND |&| )/is", "+", $srchtxt);
							$spx_matchmode = "ALL";
							$s->setMatchMode(SPH_MATCH_ALL);
						} else {
							$srchtxt = preg_replace("/( OR |\|)/is", "+", $srchtxt);
							$spx_matchmode = 'ANY';
							$s->setMatchMode(SPH_MATCH_ANY);
						}
						$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
						foreach(explode('+', $srchtxt) as $text) {
							$text = trim(daddslashes($text));
							if($text) {
								$sqltxtsrch .= $andor;
								$sqltxtsrch .= $srchtype == 'fulltext' ? "(p.message LIKE '%".str_replace('_', '\_', $text)."%' OR p.subject LIKE '%$text%')" : "t.subject LIKE '%$text%'";
							}
						}
						$sqlsrch .= " AND ($sqltxtsrch)";
					}

					if(!empty($srchfrom)) {
						if($before) {
							$spx_timemix = 0;
							$spx_timemax = TIMESTAMP - $srchfrom;
						} else {
							$spx_timemix = TIMESTAMP - $srchfrom;
							$spx_timemax = TIMESTAMP;
						}
						$s->setFilterRange('lastpost', $spx_timemix, $spx_timemax, false);
					}
					if(!empty($specials)) {
						$s->setFilter('special', explode(",", $special), false);
					}

					$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
					$expiration = TIMESTAMP + $cachelife_text;

				}
				if($srchtype == "fulltext") {
					$result = $s->query("'".$srchtxt."'", $_G['setting']['sphinxmsgindex']);
				} else {
					$result = $s->query($srchtxt, $_G['setting']['sphinxsubindex']);
				}
				$tids = array();
				if($result) {
					if(is_array($result['matches'])) {
						foreach($result['matches'] as $value) {
							if($value['attrs']['tid']) {
								$tids[$value['attrs']['tid']] = $value['attrs']['tid'];
							}
						}
					}
				}
				if(count($tids) == 0) {
					$ids = 0;
					$num = 0;
				} else {
					$ids = implode(",", $tids);
					$num = $result['total_found'];
				}
			} else {
				$digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
				$topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {

					$searchfrom = $before ? '<=' : '>=';
					$searchfrom .= TIMESTAMP - $srchfrom;
					$sqlsrch = "FROM ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND t.lastpost$searchfrom";
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';

				} else {
					$sqlsrch = $srchtype == 'fulltext' ?
					"FROM ".DB::table(getposttable($seltableid))." p, ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND p.tid=t.tid AND p.invisible='0'" :
					"FROM ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd";
					if($srchuname) {
						$srchuid = array_keys(C::t('common_member')->fetch_all_by_like_username($srchuname, 0, 50));
						if(!$srchuid) {
							$sqlsrch .= ' AND 0';
						}
					}/* elseif($srchuid) {
						$srchuid = "'$srchuid'";
					}*/

					if($srchtxt) {
						$srcharr = $srchtype == 'fulltext' ? searchkey($keyword, "(p.message LIKE '%{text}%' OR p.subject LIKE '%{text}%')", true) : searchkey($keyword,"t.subject LIKE '%{text}%'", true);
						$srchtxt = $srcharr[0];
						$sqlsrch .= $srcharr[1];
					}

					if($srchuid) {
						$sqlsrch .= ' AND '.($srchtype == 'fulltext' ? 'p' : 't').'.authorid IN ('.dimplode((array)$srchuid).')';
					}

					if(!empty($srchfrom)) {
						$searchfrom = ($before ? '<=' : '>=').(TIMESTAMP - $srchfrom);
						$sqlsrch .= " AND t.lastpost$searchfrom";
					}

					if(!empty($specials)) {
						$sqlsrch .=  " AND special IN (".dimplode($special).")";
					}

					$keywords = str_replace('%', '+', $srchtxt);
					$expiration = TIMESTAMP + $cachelife_text;

				}

				$num = $ids = 0;
				$_G['setting']['search']['forum']['maxsearchresults'] = $_G['setting']['search']['forum']['maxsearchresults'] ? intval($_G['setting']['search']['forum']['maxsearchresults']) : 500;
				$query = DB::query("SELECT ".($srchtype == 'fulltext' ? 'DISTINCT' : '')." t.tid, t.closed, t.author, t.authorid $sqlsrch ORDER BY tid DESC LIMIT ".$_G['setting']['search']['forum']['maxsearchresults']);
				while($thread = DB::fetch($query)) {
					$ids .= ','.$thread['tid'];
					$num++;
				}
				DB::free_result($query);
			}

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

		dheader("location: search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>