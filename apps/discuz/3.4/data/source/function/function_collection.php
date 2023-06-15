<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_collection.php 31438 2012-08-28 06:03:08Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getmycollection($uid) {
	$collections = C::t('forum_collection')->fetch_all_by_uid($uid);
	$collectionteamworker = C::t('forum_collectionteamworker')->fetch_all_by_uid($uid);
	return $collections + $collectionteamworker;
}

function getHotCollection($number = 500, $pK = true) {
	$collection = array();
	if($number > 0) {
		$collection = C::t('forum_collection')->range(0, $number, 10, $pK);
		if(!$collection || count($collection) < $number) {
			$collection += C::t('forum_collection')->range(0, $number, null, $pK);
		}
	}
	return $collection;
}

function checkcollectionperm($collection, $uid, $allowteamworker = false) {
	global $_G;
	if($_G['group']['allowmanagecollection'] == 1) {
		return true;
	}
	if($collection['uid'] == $uid) {
		return true;
	}
	if($allowteamworker) {
		$collectionteamworker = C::t('forum_collectionteamworker')->fetch_all_by_ctid($collection['ctid']);
		$collectionteamworker = array_keys($collectionteamworker);

		if(in_array($uid, $collectionteamworker)) {
			return true;
		}
	}
	return false;
}

function processCollectionData($collection, $tf = array(), $orderby = '') {
	if(count($collection) <= 0) {
		return array();
	}
	require_once libfile('function/discuzcode');

	foreach($collection as $ctid=>&$curvalue) {
		$curvalue['updated'] = ($curvalue['lastupdate'] > $tf[$ctid]['lastvisit']) ? 1 : 0;
		$curvalue['tflastvisit'] = $tf[$ctid]['lastvisit'];
		$curvalue['lastupdate'] = dgmdate($curvalue['lastupdate']);
		$curvalue['dateline'] = dgmdate($curvalue['dateline']);
		$curvalue['lastposttime'] = dgmdate($curvalue['lastposttime']);
		$curvalue['avgrate'] = number_format($curvalue['rate'], 1);
		$curvalue['star'] = imgdisplayrate($curvalue['rate']);
		$curvalue['lastposterhtml'] = rawurlencode($curvalue['lastposter']);
		$curvalue['shortdesc'] = cutstr(strip_tags(discuzcode($curvalue['desc'])), 50);

		$curvalue['arraykeyword'] = parse_keyword($curvalue['keyword'], false, false);
		if($curvalue['arraykeyword']) {
			foreach ($curvalue['arraykeyword'] as $kid=>$s_keyword) {
				$curvalue['urlkeyword'][$kid] = rawurlencode($s_keyword);
			}
		}

		if($orderby == 'commentnum') {
			$curvalue['displaynum'] = $curvalue['commentnum'];
		} elseif($orderby == 'follownum') {
			$curvalue['displaynum'] = $curvalue['follownum'];
		} else {
			$curvalue['displaynum'] = $curvalue['threadnum'];
		}
	}
	return $collection;
}

function collectionThread(&$threadlist, $foruminfo = false, $lastvisit = null, &$collectiontids = null) {
	global $todaytime;

	if($foruminfo) {
		foreach ($threadlist as $thread) {
			$fids[$thread['fid']] = $thread['fid'];
		}
		$foruminfo = C::t('forum_forum')->fetch_all($fids);
	}

	foreach($threadlist as $curtid=>&$curvalue) {
		if($lastvisit) {
			$curvalue['reason'] = &$collectiontids[$curtid]['reason'];
			$curvalue['updatedthread'] = $lastvisit !== null && $lastvisit < $curvalue['dateline'] ? 1 : 0;
		}
		if($foruminfo) {
			$curvalue['forumname'] = $foruminfo[$curvalue['fid']]['name'];
		}
		$curvalue['istoday'] = $curvalue['dateline'] > $todaytime ? 1 : 0;
		$curvalue['dbdateline'] = $curvalue['dateline'];
		$curvalue['htmlsubject'] = dhtmlspecialchars($curvalue['subject']);
		$curvalue['cutsubject'] = $curvalue['subject'];
		$curvalue['dateline'] = dgmdate($curvalue['dateline'], 'u', '9999', getglobal('setting/dateformat'));
		$curvalue['dblastpost'] = $curvalue['lastpost'];
		$curvalue['lastpost'] = dgmdate($curvalue['lastpost'], 'u');
		$curvalue['lastposterenc'] = rawurlencode($curvalue['lastposter']);
	}
	if($collectiontids) {
		foreach($collectiontids as $curkey=>&$curthread) {
			if(!$threadlist[$curthread['tid']]) {
				unset($collectiontids[$curkey]);
			} else {
				$curthread = $threadlist[$curthread['tid']] + $curthread;
			}
		}
	}
}

function imgdisplayrate($rate) {
	$roundscore = floor($rate);
	return $roundscore;
}

function parse_keyword($keywords, $string = false, $filter = true) {
	if($keywords == '') {
		return $string === true ? '' : array();
	}

	$return = array();

	if($filter === true) {
		$keywords = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($keywords));
	}

	if(strexists($keywords, ',')) {
		$tagarray = array_unique(explode(',', $keywords));
	} else {
		$langcore = lang('core');
		$keywords = str_replace($langcore['fullblankspace'], ' ', $keywords);
		$tagarray = array_unique(explode(' ', $keywords));
	}
	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
			$tagcount++;
			$return[] = $tagname;
			if($tagcount > 4) {
				unset($tagarray);
				break;
			}
		}
	}
	if($string === true) {
		$return = implode(',', $return);
	}
	return $return;
}

?>