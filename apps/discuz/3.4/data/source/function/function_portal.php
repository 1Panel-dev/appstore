<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_portal.php 33047 2013-04-12 08:46:56Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function category_remake($catid) {
	global $_G;

	$cat = $_G['cache']['portalcategory'][$catid];
	if(empty($cat)) return array();

	foreach ($_G['cache']['portalcategory'] as $value) {
		if($value['catid'] == $cat['upid']) {
			$cat['ups'][$value['catid']] = $value;
			$upid = $value['catid'];
			while(!empty($upid)) {
				if(!empty($_G['cache']['portalcategory'][$upid]['upid'])) {
					$upid = $_G['cache']['portalcategory'][$upid]['upid'];
					$cat['ups'][$upid] = $_G['cache']['portalcategory'][$upid];
				} else {
					$upid = 0;
				}
			}
		} elseif($value['upid'] == $cat['catid']) {
			$cat['subs'][$value['catid']] = $value;
		} elseif($value['upid'] == $cat['upid']) {
			$cat['others'][$value['catid']] = $value;
		}
	}
	if(!empty($cat['ups'])) $cat['ups'] = array_reverse($cat['ups'], TRUE);
	return $cat;
}

function getportalcategoryurl($catid) {
	if(empty($catid)) return '';
	loadcache('portalcategory');
	$portalcategory = getglobal('cache/portalcategory');
	if($portalcategory[$catid]) {
		return $portalcategory[$catid]['caturl'];
	} else {
		return '';
	}
}

function fetch_article_url($article) {
	global $_G;
	if(!empty($_G['setting']['makehtml']['flag']) && $article && $article['htmlmade']) {
		if(empty($_G['cache']['portalcategory'])) {
			loadcache('portalcategory');
		}
		$caturl = '';
		if(!empty($_G['cache']['portalcategory'][$article['catid']])) {
			$topid = $_G['cache']['portalcategory'][$article['catid']]['topid'];
			$caturl = $_G['cache']['portalcategory'][$topid]['domain'] ? $_G['cache']['portalcategory'][$topid]['caturl'] : '';
		}
		return $caturl.$article['htmldir'].$article['htmlname'].'.'.$_G['setting']['makehtml']['extendname'];
	} else {
		return 'portal.php?mod=view&aid='.$article['aid'];
	}
}

function fetch_topic_url($topic) {
	global $_G;
	if(!empty($_G['setting']['makehtml']['flag']) && $topic && $topic['htmlmade']) {
		return $_G['setting']['makehtml']['topichtmldir'].'/'.$topic['name'].'.'.$_G['setting']['makehtml']['extendname'];
	} else {
		return 'portal.php?mod=topic&topicid='.$topic['topicid'];
	}
}
?>