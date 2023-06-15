<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_feed.php 28299 2012-02-27 08:48:36Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function feed_add($icon, $title_template='', $title_data=array(), $body_template='', $body_data=array(), $body_general='', $images=array(), $image_links=array(), $target_ids='', $friend='', $appid='', $returnid=0, $id=0, $idtype='', $uid=0, $username='') {
	global $_G;

	if(!helper_access::check_module('feed')) {
		return false;
	}
	$title_template = $title_template?lang('feed', $title_template):'';
	$body_template = $body_template?lang('feed', $body_template):'';
	$body_general = $body_general?lang('feed', $body_general):'';
	if(empty($uid) || empty($username)) {
		$uid = $username = '';
	}

	$feedarr = array(
		'appid' => $appid,
		'icon' => $icon,
		'uid' => $uid ? intval($uid) : $_G['uid'],
		'username' => $username ? $username : $_G['username'],
		'dateline' => $_G['timestamp'],
		'title_template' => $title_template,
		'body_template' => $body_template,
		'body_general' => $body_general,
		'image_1' => empty($images[0])?'':$images[0],
		'image_1_link' => empty($image_links[0])?'':$image_links[0],
		'image_2' => empty($images[1])?'':$images[1],
		'image_2_link' => empty($image_links[1])?'':$image_links[1],
		'image_3' => empty($images[2])?'':$images[2],
		'image_3_link' => empty($image_links[2])?'':$image_links[2],
		'image_4' => empty($images[3])?'':$images[3],
		'image_4_link' => empty($image_links[3])?'':$image_links[3],
		'target_ids' => $target_ids,
		'friend' => $friend,
		'id' => $id,
		'idtype' => $idtype
	);

	$feedarr['title_data'] = serialize($title_data);
	$feedarr['body_data'] = serialize($body_data);
	$feedarr['hash_data'] = empty($title_data['hash_data'])?'':$title_data['hash_data'];

	if(is_numeric($icon)) {
		$feed_table = 'home_feed_app';
		unset($feedarr['id'], $feedarr['idtype']);
	} else {
		if($feedarr['hash_data']) {
			$oldfeed = C::t('home_feed')->fetch_feedid_by_hashdata($feedarr['uid'], $feedarr['hash_data']);
			if($oldfeed) {
				return 0;
			}
		}
		$feed_table = 'home_feed';
	}

	return C::t($feed_table)->insert($feedarr, $returnid);
}

function mkfeed($feed, $actors=array()) {
	global $_G;
	require_once libfile('function/discuzcode');
	$feed['title_data'] = empty($feed['title_data'])?array():(is_array($feed['title_data'])?$feed['title_data']:@dunserialize($feed['title_data']));
	if(!is_array($feed['title_data'])) $feed['title_data'] = array();

	$feed['body_data'] = empty($feed['body_data'])?array():(is_array($feed['body_data'])?$feed['body_data']:@dunserialize($feed['body_data']));
	if(!is_array($feed['body_data'])) $feed['body_data'] = array();
	$searchs = $replaces = array();
	if($feed['title_data']) {
		foreach (array_keys($feed['title_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $feed['title_data'][$key];
		}
	}

	$searchs[] = '{actor}';
	$replaces[] = empty($actors)?"<a href=\"home.php?mod=space&uid=$feed[uid]\" target=\"_blank\">$feed[username]</a>":implode(lang('core', 'dot'), $actors);
	$feed['title_template'] = str_replace($searchs, $replaces, $feed['title_template']);
	$feed['title_template'] = feed_mktarget($feed['title_template']);

	$searchs = $replaces = array();
	$searchs[] = '{actor}';
	$replaces[] = empty($actors)?"<a href=\"home.php?mod=space&uid=$feed[uid]\" target=\"_blank\">$feed[username]</a>":implode(lang('core', 'dot'), $actors);
	if($feed['body_data'] && is_array($feed['body_data'])) {
		foreach (array_keys($feed['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $feed['body_data'][$key];
		}
	}

	if($feed['body_data']['flashvar']){
		$feed['body_data']['player'] = parseflv($feed['body_data']['data'], '500', '373');
	}
	if($feed['body_data']['musicvar']){
		$feed['body_data']['player'] = parseaudio($feed['body_data']['data']);
	}
	if($feed['body_data']['videovar']){
		$feed['body_data']['player'] = parsemedia('x,500,373', $feed['body_data']['data']);
	}

	$feed['magic_class'] = '';
	if(!empty($feed['body_data']['magic_thunder'])) {
		$feed['magic_class'] = 'magicthunder';
	}

	$feed['body_template'] = str_replace($searchs, $replaces, $feed['body_template']);
	$feed['body_template'] = feed_mktarget($feed['body_template']);

	$feed['body_general'] = feed_mktarget($feed['body_general']);

	$feed['icon_image'] = STATICURL."image/feed/{$feed['icon']}.gif";

	$feed['new'] = 0;
	if($_G['cookie']['home_readfeed'] && $feed['dateline']+300 > $_G['cookie']['home_readfeed']) {
		$feed['new'] = 1;
	}

	return $feed;
}

function feed_mktarget($html) {
	global $_G;

	if($html && $_G['setting']['feedtargetblank']) {
		$html = preg_replace("/target\=([\'\"]?)[\w]+([\'\"]?)/i", '', $html);
		$html = preg_replace("/<a(.+?)href=([\'\"]?)([^>\s]+)\\2([^>]*)>/i", '<a href="\\3" target="_blank" \\1 \\4>', $html);
	}
	return $html;
}


function feed_publish($id, $idtype, $add=0) {
	global $_G;
	$id = intval($id);
	if(empty($id)) {
		return;
	}
	if(!helper_access::check_module('feed')) {
		return false;
	}
	$setarr = array();
	switch ($idtype) {
		case 'blogid':
			$value = array_merge(
				C::t('home_blog')->fetch($id),
				C::t('home_blogfield')->fetch($id)
			);
			if($value) {
				if($value['friend'] != 3) {
					$setarr['icon'] = 'blog';
					$setarr['id'] = $value['blogid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					$status = $value['status'];

					$url = "home.php?mod=space&uid=$value[uid]&do=blog&id=$value[blogid]";
					if($value['friend'] == 4) {
						$setarr['title_template'] = 'feed_blog_password';
						$setarr['title_data'] = array('subject' => "<a href=\"$url\">$value[subject]</a>");
					} else {
						if($value['pic']) {
							$setarr['image_1'] = pic_cover_get($value['pic'], $value['picflag']);
							$setarr['image_1_link'] = $url;
						}
						$setarr['title_template'] = 'feed_blog_title';
						$setarr['body_template'] = 'feed_blog_body';
						$value['message'] = preg_replace("/&[a-z]+\;/i", '', $value['message']);
						$setarr['body_data'] = array(
							'subject' => "<a href=\"$url\">$value[subject]</a>",
							'summary' => getstr($value['message'], 150, 0, 0, 0, -1)
						);
					}
				}
			}
			break;
		case 'albumid':
			$key = 1;
			if($id > 0) {
				$query = C::t('home_pic')->fetch_all_by_sql('p.'.DB::field('albumid', $id), 'a.dateline DESC', 0, 4);
				foreach($query as $value) {
					if($value['friend'] <= 2) {
						if(empty($setarr['icon'])) {
							$setarr['icon'] = 'album';
							$setarr['id'] = $value['albumid'];
							$setarr['idtype'] = $idtype;
							$setarr['uid'] = $value['uid'];
							$setarr['username'] = $value['username'];
							$setarr['dateline'] = $value['dateline'];
							$setarr['target_ids'] = $value['target_ids'];
							$setarr['friend'] = $value['friend'];
							$status = $value['status'];
							$setarr['title_template'] = 'feed_album_title';
							$setarr['body_template'] = 'feed_album_body';
							$setarr['body_data'] = array(
								'album' => "<a href=\"home.php?mod=space&uid=$value[uid]&do=album&id=$value[albumid]\">$value[albumname]</a>",
								'picnum' => $value['picnum']
							);
						}
						$setarr['image_'.$key] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
						$setarr['image_'.$key.'_link'] = "home.php?mod=space&uid=$value[uid]&do=album&picid=$value[picid]";
						$key++;
					} else {
						break;
					}
				}
			}
			break;
		case 'picid':
			$plussql = $id>0 ? 'p.'.DB::field('picid', $id) : 'p.'.DB::field('uid', $_G[uid]).' ORDER BY dateline DESC LIMIT 1';
			$query = C::t('home_pic')->fetch_all_by_sql($plussql);
			if($value = $query[0]) {
				if(empty($value['friend'])) {
					$setarr['icon'] = 'album';
					$setarr['id'] = $value['picid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					$status = $value['status'];
					$url = "home.php?mod=space&uid=$value[uid]&do=album&picid=$value[picid]";
					$setarr['image_1'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
					$setarr['image_1_link'] = $url;
					$setarr['title_template'] = 'feed_pic_title';
					$setarr['body_template'] = 'feed_pic_body';
					$setarr['body_data'] = array('title' => $value['title']);
				}
			}
			break;
	}

	if($setarr['icon']) {
		$setarr['title_template'] = $setarr['title_template']?lang('feed', $setarr['title_template']):'';
		$setarr['body_template'] = $setarr['body_template']?lang('feed', $setarr['body_template']):'';
		$setarr['body_general'] = $setarr['body_general']?lang('feed', $setarr['body_general']):'';

		$setarr['title_data']['hash_data'] = "{$idtype}{$id}";
		$setarr['title_data'] = serialize($setarr['title_data']);
		$setarr['body_data'] = serialize($setarr['body_data']);

		$feedid = 0;
		if(!$add && $setarr['id']) {
			$feedid = C::t('home_feed')->fetch($id, $idtype);
			$feedid = $feedid['feedid'];
		}
		if($status == 0) {
			if($feedid) {
				C::t('home_feed')->update('', $setarr, '', '', $feedid);
			} else {
				C::t('home_feed')->insert($setarr);
			}
		}
	}

}
?>