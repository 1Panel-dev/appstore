<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_otherstat.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_otherstat extends discuz_block {

	function block_otherstat() {}

	function name() {
		return lang('blockclass', 'blockclass_other_script_stat');
	}

	function blockclass() {
		return array('otherstat', lang('blockclass', 'blockclass_other_stat'));
	}

	function fields() {
		return array(
					'posts' => array('name' => lang('blockclass', 'blockclass_other_stat_posts'), 'formtype' => 'text', 'datatype' => 'int'),
					'posts_title' => array('name' => lang('blockclass', 'blockclass_other_stat_posts_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'groups' => array('name' => lang('blockclass', 'blockclass_other_stat_groups'), 'formtype' => 'text', 'datatype' => 'int'),
					'groups_title' => array('name' => lang('blockclass', 'blockclass_other_stat_groups_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'members' => array('name' => lang('blockclass', 'blockclass_other_stat_members'), 'formtype' => 'text', 'datatype' => 'int'),
					'members_title' => array('name' => lang('blockclass', 'blockclass_other_stat_members_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'groupnewposts' => array('name' => lang('blockclass', 'blockclass_other_stat_groupnewposts'), 'formtype' => 'text', 'datatype' => 'int'),
					'groupnewposts_title' => array('name' => lang('blockclass', 'blockclass_other_stat_groupnewposts_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'bbsnewposts' => array('name' => lang('blockclass', 'blockclass_other_stat_bbsnewposts'), 'formtype' => 'text', 'datatype' => 'int'),
					'bbsnewposts_title' => array('name' => lang('blockclass', 'blockclass_other_stat_bbsnewposts_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'bbslastposts' => array('name' => lang('blockclass', 'blockclass_other_stat_bbslastposts'), 'formtype' => 'text', 'datatype' => 'int'),
					'bbslastposts_title' => array('name' => lang('blockclass', 'blockclass_other_stat_bbslastposts_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'onlinemembers' => array('name' => lang('blockclass', 'blockclass_other_stat_onlinemembers'), 'formtype' => 'text', 'datatype' => 'int'),
					'onlinemembers_title' => array('name' => lang('blockclass', 'blockclass_other_stat_onlinemembers_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'maxmembers' => array('name' => lang('blockclass', 'blockclass_other_stat_maxmembers'), 'formtype' => 'text', 'datatype' => 'int'),
					'maxmembers_title' => array('name' => lang('blockclass', 'blockclass_other_stat_maxmembers_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'doings' => array('name' => lang('blockclass', 'blockclass_other_stat_doings'), 'formtype' => 'text', 'datatype' => 'int'),
					'doings_title' => array('name' => lang('blockclass', 'blockclass_other_stat_doings_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'blogs' => array('name' => lang('blockclass', 'blockclass_other_stat_blogs'), 'formtype' => 'text', 'datatype' => 'int'),
					'blogs_title' => array('name' => lang('blockclass', 'blockclass_other_stat_blogs_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'albums' => array('name' => lang('blockclass', 'blockclass_other_stat_albums'), 'formtype' => 'text', 'datatype' => 'int'),
					'albums_title' => array('name' => lang('blockclass', 'blockclass_other_stat_albums_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'pics' => array('name' => lang('blockclass', 'blockclass_other_stat_pics'), 'formtype' => 'text', 'datatype' => 'int'),
					'pics_title' => array('name' => lang('blockclass', 'blockclass_other_stat_pics_title'), 'formtype' => 'text', 'datatype' => 'string'),
					'shares' => array('name' => lang('blockclass', 'blockclass_other_stat_shares'), 'formtype' => 'text', 'datatype' => 'int'),
					'shares_title' => array('name' => lang('blockclass', 'blockclass_other_stat_shares_title'), 'formtype' => 'text', 'datatype' => 'string'),
				);
	}
	function getsetting() {
		global $_G;
		$settings = array(
			'option' => array(
				'title' => 'stat_option',
				'type' => 'mcheckbox',
				'value' => array(
					array('posts', 'stat_option_posts'),
					array('groups', 'stat_option_groups'),
					array('members', 'stat_option_members'),
					array('groupnewposts', 'stat_option_groupnewposts'),
					array('bbsnewposts', 'stat_option_bbsnewposts'),
					array('bbslastposts', 'stat_option_bbslastposts'),
					array('onlinemembers', 'stat_option_onlinemembers'),
					array('maxmembers', 'stat_option_maxmembers'),
					array('doings', 'stat_option_doings'),
					array('blogs', 'stat_option_blogs'),
					array('albums', 'stat_option_albums'),
					array('pics', 'stat_option_pics'),
					array('shares', 'stat_option_shares'),
				),
				'default' => array('posts', 'groups', 'members')
			),
			'tip' => array(
				'title' => 'stat_edit_showtitle',
				'type' => lang('block/stat', 'stat_edit_showtitle_detail'),
			),
			'posts_title' => array(
				'title' => 'stat_option_posts',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_posts')
			),
			'groups_title' => array(
				'title' => 'stat_option_groups',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_groups')
			),
			'members_title' => array(
				'title' => 'stat_option_members',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_members')
			),
			'groupnewposts_title' => array(
				'title' => 'stat_option_groupnewposts',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_groupnewposts')
			),
			'bbsnewposts_title' => array(
				'title' => 'stat_option_bbsnewposts',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_bbsnewposts')
			),
			'bbslastposts_title' => array(
				'title' => 'stat_option_bbslastposts',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_bbslastposts')
			),
			'onlinemembers_title' => array(
				'title' => 'stat_option_onlinemembers',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_onlinemembers')
			),
			'maxmembers_title' => array(
				'title' => 'stat_option_maxmembers',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_maxmembers')
			),
			'doings_title' => array(
				'title' => 'stat_option_doings',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_doings')
			),
			'blogs_title' => array(
				'title' => 'stat_option_blogs',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_blogs')
			),
			'albums_title' => array(
				'title' => 'stat_option_albums',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_albums')
			),
			'pics_title' => array(
				'title' => 'stat_option_pics',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_pics')
			),
			'shares_title' => array(
				'title' => 'stat_option_shares',
				'type' => 'text',
				'default' => lang('block/stat', 'stat_shares')
			),
		);
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;
		$parameter = $this->cookparameter($parameter);
		$fields = array(
			'posts' => 0,
			'posts_title' => !empty($parameter['posts_title']) ? $parameter['posts_title'] : lang('block/stat', 'stat_posts'),
			'groups' => 0,
			'groups_title' => !empty($parameter['groups_title']) ? $parameter['groups_title'] : lang('block/stat', 'stat_groups'),
			'members' => 0,
			'members_title' => !empty($parameter['members_title']) ? $parameter['members_title'] : lang('block/stat', 'stat_members'),
			'groupnewposts' => 0,
			'groupnewposts_title' => !empty($parameter['groupnewposts_title']) ? $parameter['groupnewposts_title'] : lang('block/stat', 'stat_groupnewposts'),
			'bbsnewposts' => 0,
			'bbsnewposts_title' => !empty($parameter['bbsnewposts_title']) ? $parameter['bbsnewposts_title'] : lang('block/stat', 'stat_bbsnewposts'),
			'bbslastposts' => 0,
			'bbslastposts_title' => !empty($parameter['bbslastposts_title']) ? $parameter['bbslastposts_title'] : lang('block/stat', 'stat_bbslastposts'),
			'onlinemembers' => 0,
			'onlinemembers_title' => !empty($parameter['onlinemembers_title']) ? $parameter['onlinemembers_title'] : lang('block/stat', 'stat_onlinemembers'),
			'maxmembers' => 0,
			'maxmembers_title' => !empty($parameter['maxmembers_title']) ? $parameter['maxmembers_title'] : lang('block/stat', 'stat_maxmembers'),
			'doings' => 0,
			'doings_title' => !empty($parameter['doings_title']) ? $parameter['doings_title'] : lang('block/stat', 'stat_doings'),
			'blogs' => 0,
			'blogs_title' => !empty($parameter['blogs_title']) ? $parameter['blogs_title'] : lang('block/stat', 'stat_blogs'),
			'albums' => 0,
			'albums_title' => !empty($parameter['albums_title']) ? $parameter['albums_title'] : lang('block/stat', 'stat_albums'),
			'pics' => 0,
			'pics_title' => !empty($parameter['pics_title']) ? $parameter['pics_title'] : lang('block/stat', 'stat_pics'),
			'shares' => 0,
			'shares_title' => !empty($parameter['shares_title']) ? $parameter['shares_title'] : lang('block/stat', 'stat_shares'),
		);
		if(in_array('posts', $parameter['option']) || in_array('bbsnewposts', $parameter['option'])) {
			$sql = "SELECT sum(f.posts) AS posts, sum(f.todayposts) AS todayposts FROM ".DB::table('forum_forum')." f WHERE f.status='1'";
			$forum = DB::fetch_first($sql);
		}
		if(in_array('groups', $parameter['option']) || in_array('groupnewposts', $parameter['option'])) {
			loadcache('groupindex');
		}
		if(in_array('posts', $parameter['option'])) {
			$fields['posts'] = intval($forum['posts']);
		}
		if(in_array('groups', $parameter['option'])) {
			$fields['groups'] = intval($_G['cache']['groupindex']['groupnum']);
		}
		if(in_array('members', $parameter['option'])) {
			loadcache('userstats');
			$fields['members'] = intval($_G['cache']['userstats']['totalmembers']);
		}
		if(in_array('groupnewposts', $parameter['option'])) {
			$fields['groupnewposts'] = intval($_G['cache']['groupindex']['todayposts']);
		}
		if(in_array('bbsnewposts', $parameter['option'])) {
			$fields['bbsnewposts'] = intval($forum['todayposts']);
		}
		if(in_array('bbslastposts', $parameter['option'])) {
			loadcache('historyposts');
			$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array();
			$fields['bbslastposts'] = intval($postdata[0]);
		}
		if(in_array('onlinemembers', $parameter['option'])) {
			$num = !empty($_G['cookie']['onlineusernum']) ? intval($_G['cookie']['onlineusernum']) : C::app()->session->count();
			$fields['onlinemembers'] = intval($num);
		}
		if(in_array('maxmembers', $parameter['option'])) {
			loadcache('onlinerecord');
			$onlineinfo = explode("\t", $_G['cache']['onlinerecord']);
			$fields['maxmembers'] = !empty($onlineinfo[0]) ? intval($onlineinfo[0]) : 0;
		}
		if(in_array('doings', $parameter['option'])) {
			$num = C::t('home_doing')->count();
			$fields['doings'] = intval($num);
		}
		if(in_array('blogs', $parameter['option'])) {
			$num = C::t('home_blog')->count();
			$fields['blogs'] = intval($num);
		}
		if(in_array('albums', $parameter['option'])) {
			$num = C::t('home_album')->count();
			$fields['albums'] = intval($num);
		}
		if(in_array('pics', $parameter['option'])) {
			$num = C::t('home_pic')->count();
			$fields['pics'] = intval($num);
		}
		if(in_array('shares', $parameter['option'])) {
			$num = C::t('home_share')->count();
			$fields['shares'] = intval($num);
		}
		$list = array();
		$list[1] = array(
			'id' => 1,
			'idtype' => 'statid',
			'fields' => $fields
		);
		return array('html' => '', 'data' => $list);
	}
}

?>