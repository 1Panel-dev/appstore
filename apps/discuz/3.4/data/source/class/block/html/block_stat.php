<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_stat.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_stat extends commonblock_html {

	function block_stat() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_stat');
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
		$parameter = $this->cookparameter($parameter);
		global $_G;
		if(in_array('posts', $parameter['option']) || in_array('bbsnewposts', $parameter['option'])) {
			$sql = "SELECT sum(f.posts) AS posts, sum(f.todayposts) AS todayposts FROM ".DB::table('forum_forum')." f WHERE f.status='1'";
			$forum = DB::fetch_first($sql);
		}
		if(in_array('groups', $parameter['option']) || in_array('groupnewposts', $parameter['option'])) {
			loadcache('groupindex');
		}
		$index = count($parameter['option']) - 1;
		$html = '<div class="tns"><table cellspacing="0" cellpadding="4" border="0"><tbody><tr>';
		if(in_array('posts', $parameter['option'])) {
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($forum['posts']).'</p>'.(!empty($parameter['posts_title']) ? $parameter['posts_title'] : lang('block/stat', 'stat_posts')).'</th>';
		}
		if(in_array('groups', $parameter['option'])) {
			$class = ($index-- == 0) ? ' class="bbn"' : '';
		    $html .= "<th$class><p>".intval($_G['cache']['groupindex']['groupnum']).'</p>'.(!empty($parameter['groups_title']) ? $parameter['groups_title'] : lang('block/stat', 'stat_groups')).'</th>';
		}
		if(in_array('members', $parameter['option'])) {
			loadcache('userstats');
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($_G['cache']['userstats']['totalmembers']).'</p>'.(!empty($parameter['members_title']) ? $parameter['members_title'] : lang('block/stat', 'stat_members')).'</th>';
		}
		if(in_array('groupnewposts', $parameter['option'])) {
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($_G['cache']['groupindex']['todayposts']).'</p>'.(!empty($parameter['groupnewposts_title']) ? $parameter['groupnewposts_title'] : lang('block/stat', 'stat_groupnewposts')).'</th>';
		}
		if(in_array('bbsnewposts', $parameter['option'])) {
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($forum['todayposts']).'</p>'.(!empty($parameter['bbsnewposts_title']) ? $parameter['bbsnewposts_title'] : lang('block/stat', 'stat_bbsnewposts')).'</th>';
		}
		if(in_array('bbslastposts', $parameter['option'])) {
			loadcache('historyposts');
			$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($postdata[0]).'</p>'.(!empty($parameter['bbslastposts_title']) ? $parameter['bbslastposts_title'] : lang('block/stat', 'stat_bbslastposts')).'</th>';
		}
		if(in_array('onlinemembers', $parameter['option'])) {
			$num = !empty($_G['cookie']['onlineusernum']) ? intval($_G['cookie']['onlineusernum']) : C::app()->session->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['onlinemembers_title']) ? $parameter['onlinemembers_title'] : lang('block/stat', 'stat_onlinemembers')).'</th>';
		}
		if(in_array('maxmembers', $parameter['option'])) {
			loadcache('onlinerecord');
			$onlineinfo = explode("\t", $_G['cache']['onlinerecord']);
			$num = !empty($onlineinfo[0]) ? intval($onlineinfo[0]) : 0;
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['maxmembers_title']) ? $parameter['maxmembers_title'] : lang('block/stat', 'stat_maxmembers')).'</th>';
		}
		if(in_array('doings', $parameter['option'])) {
			$num = C::t('home_doing')->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['doings_title']) ? $parameter['doings_title'] : lang('block/stat', 'stat_doings')).'</th>';
		}
		if(in_array('blogs', $parameter['option'])) {
			$num = C::t('home_blog')->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['blogs_title']) ? $parameter['blogs_title'] : lang('block/stat', 'stat_blogs')).'</th>';
		}
		if(in_array('albums', $parameter['option'])) {
			$num = C::t('home_album')->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['albums_title']) ? $parameter['albums_title'] : lang('block/stat', 'stat_albums')).'</th>';
		}
		if(in_array('pics', $parameter['option'])) {
			$num = C::t('home_pic')->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['pics_title']) ? $parameter['pics_title'] : lang('block/stat', 'stat_pics')).'</th>';
		}
		if(in_array('shares', $parameter['option'])) {
			$num = C::t('home_share')->count();
			$class = ($index-- == 0) ? ' class="bbn"' : '';
			$html .= "<th$class><p>".intval($num).'</p>'.(!empty($parameter['shares_title']) ? $parameter['shares_title'] : lang('block/stat', 'stat_shares')).'</th>';
		}
		$html .= '</tr></tbody></table></div>';
		return array('html' => $html, 'data' => null);
	}
}

?>