<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsqapi.class.php 34924 2014-08-27 06:33:08Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class WSQAPI {

	function forumdisplay_variables(&$variables) {
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow'] || !$_G['wechat']['setting']['showactivity']['tids']) {
			return;
		}
		$tids = array();
		foreach ($variables['forum_threadlist'] as &$thread) {
			if(in_array($thread['tid'], $_G['wechat']['setting']['showactivity']['tids'])) {
				$thread['showactivity'] = 1;
				$tids[] = $thread['tid'];
			}
		}
		$activities = C::t('forum_activity')->fetch_all($tids);
		foreach($activities as $tid => $activity) {
			$variables['showactivity'][$tid]['starttimefrom'] = dgmdate($activities[$tid]['starttimefrom']);
			$variables['showactivity'][$tid]['expiration'] = dgmdate($activities[$tid]['expiration']);
			$variables['showactivity'][$tid]['applynumber'] = $activities[$tid]['applynumber'];
			$variables['showactivity'][$tid]['thumb'] = $activity['aid'] ? $_G['siteurl'].getforumimg($activity['aid'], 0, 400, 400) : '';
		}
	}

	function viewthread_variables(&$variables) {
		if(!showActivity::init()) {
			return;
		}
		global $_G;
		$variables['thread']['showactivity'] = 1;
		$variables['special_activity']['thumb'] = preg_match('/^https?:\//', $GLOBALS['activity']['thumb']) ? $GLOBALS['activity']['thumb'] : $_G['siteurl'].$GLOBALS['activity']['thumb'];
		unset($variables['special_activity']['attachurl']);

		if(empty($_GET['viewpid'])) {
			if(!$_GET['viewhot']) {
				$pids = array();
				foreach($variables['postlist'] as $post) {
					$pids[] = $post['pid'];
				}
				if($pids) {
					$posts = DB::fetch_all("SELECT pid, voters FROM %t WHERE pid IN (%n)", array('forum_debatepost', $pids), 'pid');
					$voters = array();
					foreach($variables['postlist'] as $key => $post) {
						$variables['postlist'][$key]['voters'] = intval($posts[$post['pid']]['voters']);
						if($_G['page'] == 1 && !$post['first'] && $_G['uid'] && $_G['uid'] == $post['authorid']) {
							unset($variables['postlist'][$key]);
						}
					}
				}
				$variables['postlist'] = array_values($variables['postlist']);
				$myarr = array();
				if($_G['uid'] && $_G['page'] == 1) {
					$pids = array();
					$posts = C::t('forum_post')->fetch_all_common_viewthread_by_tid($_G['tid'], 0, $_G['uid'], 1, 2, 0, 0, 0);
					foreach($posts as $pid => $post) {
						$myarr[$pid] = array(
						    'pid' => $pid,
						    'author' => $post['author'],
						    'authorid' => $post['authorid'],
						    'voters' => 0,
						);
						$pids[] = $post['pid'];
					}
					$posts = DB::fetch_all("SELECT pid, voters FROM %t WHERE pid IN (%n)", array('forum_debatepost', $pids), 'pid');
					foreach($posts as $pid => $post) {
						$myarr[$pid]['voters'] = intval($post['voters']);
					}
					if($myarr) {
						require_once libfile('function/attachment');
						parseattach(array_keys($myarr), array(), $myarr);
					}
				}
				$variables['special_activity']['my_postlist'] = array_values($myarr);
				$variables['special_activity']['view'] = 'new';
			} else {
				foreach($variables['postlist'] as $key => $post) {
					if(!$post['first']) {
						unset($variables['postlist'][$key]);
					}
				}
				$cachekey = 'showactivity_'.$_G['tid'];
				loadcache($cachekey);
				if(!$_G['cache'][$cachekey] || TIMESTAMP - $_G['cache'][$cachekey]['expiration'] > 600) {
					$posts = DB::fetch_all("SELECT pid, voters FROM %t d WHERE tid=%d AND voters>1 ORDER BY voters DESC LIMIT 500", array('forum_debatepost', $_G['tid']), 'pid');
					foreach($posts as $vpost) {
						$voters[$vpost['pid']] = $vpost['voters'];
					}
					$top = 1;
					$toparr = array();
					$posts = C::t('forum_post')->fetch_all_by_pid('tid:'.$_G['tid'], array_keys($voters), false, '', 0, 0, null, 0);
					foreach($voters as $pid => $voters) {
						if($posts[$pid]) {
							$toparr[$pid] = array(
							    'pid' => $pid,
							    'author' => $posts[$pid]['author'],
							    'authorid' => $posts[$pid]['authorid'],
							    'voters' => $voters,
							    'top' => $top++
							);
							if($top > 50) {
								break;
							}
						}
					}
					$variables['special_activity']['top_postlist'] = $toparr;
					savecache($cachekey, array('variable' => $toparr, 'expiration' => TIMESTAMP));
				} else {
					$variables['special_activity']['top_postlist'] = $_G['cache'][$cachekey]['variable'];
				}
				$hotpage = max(1, $_GET['page']);
				$start = max(0, ($hotpage - 1) * $_G['ppp']);

				$toplist = & $variables['special_activity']['top_postlist'];
				$toplist = array_slice($toplist, $start, $_G['ppp'], 1);
				require_once libfile('function/attachment');
				parseattach(array_keys($toplist), array(), $toplist);
				$toplist = array_values($toplist);
				$variables['special_activity']['view'] = 'hot';
			}
		} else {
			$comments = array();
			foreach($GLOBALS['comments'][$_GET['viewpid']] as $comment) {
				$comments[] = array(
					'author' => $comment['author'],
					'authorid' => $comment['authorid'],
					'avatar' => avatar($comment['authorid'], 'small', 1),
					'message' => $comment['comment'],
					'dateline' => strip_tags(dgmdate($comment['dateline'], 'u')),
				);
			}
			$variables['postlist'] = array_merge($variables['postlist'], $comments);
			$variables['thread']['replies'] = $GLOBALS['commentcount'][$_GET['viewpid']];
			$voters = C::t('forum_debatepost')->fetch($_GET['viewpid']);
			$variables['thread']['recommend_add'] = $voters['voters'];
		}
	}

}