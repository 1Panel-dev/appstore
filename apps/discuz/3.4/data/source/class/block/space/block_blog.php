<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blog.php 29655 2012-04-24 05:51:56Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_blog extends discuz_block {
	var $setting = array();
	function block_blog() {
		$this->setting = array(
			'blogids'	=> array(
				'title' => 'bloglist_blogids',
				'type' => 'text'
			),
			'uids'	=> array(
				'title' => 'bloglist_uids',
				'type' => 'text',
			),
			'catid' => array(
				'title' => 'bloglist_catid',
				'type'=>'mselect',
			),
			'picrequired' => array(
				'title' => 'bloglist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'orderby' => array(
				'title' => 'bloglist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'bloglist_orderby_dateline'),
					array('viewnum', 'bloglist_orderby_viewnum'),
					array('replynum', 'bloglist_orderby_replynum'),
					array('hot', 'bloglist_orderby_hot')
				),
				'default' => 'dateline'
			),
			'hours' => array(
				'title' => 'bloglist_hours',
				'type' => 'mradio',
				'value' => array(
					array('', 'bloglist_hours_nolimit'),
					array('1', 'bloglist_hours_hour'),
					array('24', 'bloglist_hours_day'),
					array('168', 'bloglist_hours_week'),
					array('720', 'bloglist_hours_month'),
					array('8760', 'bloglist_hours_year'),
				),
				'default' => ''
			),
			'titlelength' => array(
				'title' => 'bloglist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'bloglist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'bloglist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_blog_script_blog');
	}

	function blockclass() {
		return array('blog', lang('blockclass', 'blockclass_space_blog'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_blog_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_blog_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'summary' => array('name' => lang('blockclass', 'blockclass_blog_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'pic' => array('name' => lang('blockclass', 'blockclass_blog_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_blog_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'uid' => array('name' => lang('blockclass', 'blockclass_blog_field_uid'), 'formtype' => 'text', 'datatype' => 'int'),
				'username' => array('name' => lang('blockclass', 'blockclass_blog_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar' => array('name' => lang('blockclass', 'blockclass_blog_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_middle' => array('name' => lang('blockclass', 'blockclass_blog_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('blockclass', 'blockclass_blog_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'replynum' => array('name' => lang('blockclass', 'blockclass_blog_field_replynum'), 'formtype' => 'text', 'datatype' => 'int'),
				'viewnum' => array('name' => lang('blockclass', 'blockclass_blog_field_viewnum'), 'formtype' => 'text', 'datatype' => 'int'),
				'click1' => array('name' => lang('blockclass', 'blockclass_blog_field_click1'), 'formtype' => 'text', 'datatype' => 'int'),
				'click2' => array('name' => lang('blockclass', 'blockclass_blog_field_click2'), 'formtype' => 'text', 'datatype' => 'int'),
				'click3' => array('name' => lang('blockclass', 'blockclass_blog_field_click3'), 'formtype' => 'text', 'datatype' => 'int'),
				'click4' => array('name' => lang('blockclass', 'blockclass_blog_field_click4'), 'formtype' => 'text', 'datatype' => 'int'),
				'click5' => array('name' => lang('blockclass', 'blockclass_blog_field_click5'), 'formtype' => 'text', 'datatype' => 'int'),
				'click6' => array('name' => lang('blockclass', 'blockclass_blog_field_click6'), 'formtype' => 'text', 'datatype' => 'int'),
				'click7' => array('name' => lang('blockclass', 'blockclass_blog_field_click7'), 'formtype' => 'text', 'datatype' => 'int'),
				'click8' => array('name' => lang('blockclass', 'blockclass_blog_field_click8'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_thread' => array(
					'name' => lang('blockclass', 'blockclass_forum_thread'),
					'script' => 'thread',
					'searchkeys' => array('username', 'uid', 'viewnum', 'replynum'),
					'replacekeys' => array('author', 'authorid', 'views', 'replies'),
				),
				'group_thread' => array(
					'name' => lang('blockclass', 'blockclass_group_thread'),
					'script' => 'groupthread',
					'searchkeys' => array('username', 'uid', 'viewnum', 'replynum'),
					'replacekeys' => array('author', 'authorid', 'views', 'replies'),
				),
				'portal_article' => array(
					'name' => lang('blockclass', 'blockclass_portal_article'),
					'script' => 'article',
					'searchkeys' => array('replynum'),
					'replacekeys' => array('commentnum'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if(!empty($settings['catid'])) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_all_category'));
			loadcache('blogcategory');
			foreach($_G['cache']['blogcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['blogcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
							if($value2['children']) {
								foreach($value2['children'] as $catid3) {
									$value3 = $_G['cache']['blogcategory'][$catid3];
									$settings['catid']['value'][] = array($value3['catid'], '---- '.$value3['catname']);
								}
							}
						}
					}
				}
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$blogids	= !empty($parameter['blogids']) ? explode(',',$parameter['blogids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$catid		= !empty($parameter['catid']) ? $parameter['catid'] : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$hours		= isset($parameter['hours']) ? intval($parameter['hours']) : '';
		$titlelength = $parameter['titlelength'] ? intval($parameter['titlelength']) : 40;
		$summarylength = $parameter['summarylength'] ? intval($parameter['summarylength']) : 80;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('dateline', 'viewnum', 'replynum', 'hot')) ? $parameter['orderby'] : 'dateline';
		$picrequired = !empty($parameter['picrequired']) ? 1 : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$datalist = $list = array();
		$wheres = array();
		if(!$blogids && !$catid && $_G['setting']['blockmaxaggregationitem']) {
			if(($maxid = $this->getmaxid() - $_G['setting']['blockmaxaggregationitem']) > 0) {
				$wheres[] = 'b.blogid > '.$maxid;
			}
		}
		if($blogids) {
			$wheres[] = 'b.blogid IN ('.dimplode($blogids).')';
		}
		if($bannedids) {
			$wheres[] = 'b.blogid NOT IN ('.dimplode($bannedids).')';
		}
		if($uids) {
			$wheres[] = 'b.uid IN ('.dimplode($uids).')';
		}
		if($catid && !in_array('0', $catid)) {
			$wheres[] = 'b.catid IN ('.dimplode($catid).')';
		}
		if($hours) {
			$timestamp = TIMESTAMP - 3600 * $hours;
			$wheres[] = "b.dateline >= '$timestamp'";
		}
		$tablesql = $fieldsql = '';
		if($style['getsummary'] || $picrequired || $style['getpic']) {
			if($picrequired) {
				$wheres[] = "bf.pic != ''";
			}
			$tablesql = ' LEFT JOIN '.DB::table('home_blogfield')." bf ON b.blogid = bf.blogid";
			$fieldsql = ', bf.pic, b.picflag, bf.message';
		}
		$wheres[] = "b.friend = '0'";
		$wheres[] = "b.status='0'";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$sql = "SELECT b.* $fieldsql FROM ".DB::table('home_blog')." b $tablesql WHERE $wheresql ORDER BY b.$orderby DESC";
		$query = DB::query($sql." LIMIT $startrow,$items;");
		while($data = DB::fetch($query)) {
			if(empty($data['pic'])) {
				$data['pic'] = STATICURL.'image/common/nophoto.gif';
				$data['picflag'] = '0';
			} else {
				$data['pic'] = preg_replace('/\.thumb\.jpg$/', '', $data['pic']);
				$data['pic'] = 'album/'.$data['pic'];
				$data['picflag'] = $data['remote'] == '1' ? '2' : '1';
			}
			$list[] = array(
				'id' => $data['blogid'],
				'idtype' => 'blogid',
				'title' => cutstr($data['subject'], $titlelength, ''),
				'url' => 'home.php?mod=space&uid='.$data[uid].'&do=blog&id='.$data['blogid'],
				'pic' => $data['pic'],
				'picflag' => $data['picflag'],
				'summary' => $data['message'] ? preg_replace("/&amp;[a-z]+\;/i", '', cutstr(strip_tags($data['message']), $summarylength, '')) : '',
				'fields' => array(
					'fulltitle' => $data['subject'],
					'dateline'=>$data['dateline'],
					'uid'=>$data['uid'],
					'username'=>$data['username'],
					'avatar' => avatar($data['uid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar($data['uid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar($data['uid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
					'replynum'=>$data['replynum'],
					'viewnum'=>$data['viewnum'],
					'click1'=>$data['click1'],
					'click2'=>$data['click2'],
					'click3'=>$data['click3'],
					'click4'=>$data['click4'],
					'click5'=>$data['click5'],
					'click6'=>$data['click6'],
					'click7'=>$data['click7'],
					'click8'=>$data['click8'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}

	function getmaxid() {
		loadcache('databasemaxid');
		$data = getglobal('cache/databasemaxid');
		if(!isset($data['blog']) || TIMESTAMP - $data['blog']['dateline'] >= 86400) {
			$data['blog']['dateline'] = TIMESTAMP;
			$data['blog']['id'] = DB::result_first('SELECT MAX(blogid) FROM '.DB::table('home_blog'));
			savecache('databasemaxid', $data);
		}
		return $data['blog']['id'];
	}


}

?>