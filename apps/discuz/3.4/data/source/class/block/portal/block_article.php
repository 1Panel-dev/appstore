<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_article.php 31313 2012-08-10 03:51:03Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_article extends discuz_block {
	var $setting = array();
	function block_article() {
		global $_G;
		$this->setting = array(
			'aids'	=> array(
				'title' => 'articlelist_aids',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => 'articlelist_uids',
				'type' => 'text',
				'value' => ''
			),
			'keyword' => array(
				'title' => 'articlelist_keyword',
				'type' => 'text'
			),
			'catid' => array(
				'title' => 'articlelist_catid',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'tag' => array(
				'title' => 'articlelist_tag',
				'type' => 'mcheckbox',
				'value' => array(
				),
			),
			'picrequired' => array(
				'title' => 'articlelist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'starttime' => array(
				'title' => 'articlelist_starttime',
				'type' => 'calendar',
				'default' => ''
			),
			'endtime' => array(
				'title' => 'articlelist_endtime',
				'type' => 'calendar',
				'default' => ''
			),
			'orderby' => array(
				'title' => 'articlelist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'articlelist_orderby_dateline'),
					array('viewnum', 'articlelist_orderby_viewnum'),
					array('commentnum', 'articlelist_orderby_commentnum'),
				),
				'default' => 'dateline'
			),
			'publishdateline' => array(
				'title' => 'articlelist_publishdateline',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'articlelist_publishdateline_nolimit'),
					array('3600', 'articlelist_publishdateline_hour'),
					array('86400', 'articlelist_publishdateline_day'),
					array('604800', 'articlelist_publishdateline_week'),
					array('2592000', 'articlelist_publishdateline_month'),
				),
				'default' => '0'
			),
			'titlelength' => array(
				'title' => 'articlelist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'articlelist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'articlelist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
		loadcache('click');
		$clicks = !empty($_G['cache']['click']['aid']) ? $_G['cache']['click']['aid'] : array();
		if(!empty($clicks)){
			foreach($clicks as $key => $value) {
				if($value['available']) {
					$this->setting['orderby']['value'][] = array('click'.$key, lang('block/articlelist', 'articlelist_orderby_click', array('clickname'=>$value['name'])));
				}
			}
		}
	}

	function name() {
		return lang('blockclass', 'blockclass_article_script_article');
	}

	function blockclass() {
		return array('article', lang('blockclass', 'blockclass_portal_article'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'uid' => array('name' => lang('blockclass', 'blockclass_article_field_uid'), 'formtype' => 'text', 'datatype' => 'int'),
				'username' => array('name' => lang('blockclass', 'blockclass_article_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar' => array('name' => lang('blockclass', 'blockclass_article_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_middle' => array('name' => lang('blockclass', 'blockclass_article_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('blockclass', 'blockclass_article_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'url' => array('name' => lang('blockclass', 'blockclass_article_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_article_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'pic' => array('name' => lang('blockclass', 'blockclass_article_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
				'summary' => array('name' => lang('blockclass', 'blockclass_article_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_article_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'caturl' => array('name' => lang('blockclass', 'blockclass_article_field_caturl'), 'formtype' => 'text', 'datatype' => 'string'),
				'catname' => array('name' => lang('blockclass', 'blockclass_article_field_catname'), 'formtype' => 'text', 'datatype' => 'string'),
				'articles' => array('name' => lang('blockclass', 'blockclass_article_field_articles'), 'formtype' => 'text', 'datatype' => 'int'),
				'viewnum' => array('name' => lang('blockclass', 'blockclass_article_field_viewnum'), 'formtype' => 'text', 'datatype' => 'int'),
				'commentnum' => array('name' => lang('blockclass', 'blockclass_article_field_commentnum'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_thread' => array(
					'name' => lang('blockclass', 'blockclass_forum_thread'),
					'script' => 'thread',
					'searchkeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
					'replacekeys' => array('author', 'authorid', 'forumurl', 'forumname', 'posts', 'views', 'replies'),
				),
				'group_thread' => array(
					'name' => lang('blockclass', 'blockclass_group_thread'),
					'script' => 'groupthread',
					'searchkeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
					'replacekeys' => array('author', 'authorid', 'groupurl', 'groupname', 'posts', 'views', 'replies'),
				),
				'space_blog' => array(
					'name' => lang('blockclass', 'blockclass_space_blog'),
					'script' => 'blog',
					'searchkeys' => array('commentnum'),
					'replacekeys' => array('replynum'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['catid']) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_all_category'));
			loadcache('portalcategory');
			foreach($_G['cache']['portalcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['portalcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
							if($value2['children']) {
								foreach($value2['children'] as $catid3) {
									$value3 = $_G['cache']['portalcategory'][$catid3];
									$settings['catid']['value'][] = array($value3['catid'], '---- '.$value3['catname']);
								}
							}
						}
					}
				}
			}
		}
		if($settings['tag']) {
			$tagnames = article_tagnames();
			foreach($tagnames as $k=>$v) {
				$settings['tag']['value'][] = array($k, $v);
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;
		require_once libfile('function/portal');

		$parameter = $this->cookparameter($parameter);
		$aids		= !empty($parameter['aids']) ? explode(',', $parameter['aids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
		$tag		= !empty($parameter['tag']) ? $parameter['tag'] : array();
		$starttime	= !empty($parameter['starttime']) ? strtotime($parameter['starttime']) : 0;
		$endtime	= !empty($parameter['endtime']) ? strtotime($parameter['endtime']) : 0;
		$publishdateline	= isset($parameter['publishdateline']) ? intval($parameter['publishdateline']) : 0;
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength = isset($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$clickarr = array('click1', 'click2', 'click3', 'click4', 'click5', 'click6', 'click7', 'click8');
		$orderby	= in_array($parameter['orderby'], array_merge(array('dateline', 'viewnum', 'commentnum'), $clickarr)) ? $parameter['orderby'] : 'dateline';
		$catid = array();
		if(!empty($parameter['catid'])) {
			if($parameter['catid'][0] == '0') {
				unset($parameter['catid'][0]);
			}
			$catid = $parameter['catid'];
		}

		$picrequired = !empty($parameter['picrequired']) ? 1 : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		loadcache('portalcategory');

		$list = array();
		$wheres = array();

		if($aids) {
			$wheres[] = 'at.aid IN ('.dimplode($aids).')';
		}
		if($uids) {
			$wheres[] = 'at.uid IN ('.dimplode($uids).')';
		}
		if($catid) {
			include_once libfile('function/portalcp');
			$childids = array();
			foreach($catid as $id) {
				if($_G['cache']['portalcategory'][$id]['disallowpublish']) {
					$childids = array_merge($childids, category_get_childids('portal', $id));
				}
			}
			$catid = array_merge($catid, $childids);
			$catid = array_unique($catid);
			$wheres[] = 'at.catid IN ('.dimplode($catid).')';
		}
		if(!$aids && !$catid && $_G['setting']['blockmaxaggregationitem']) {
			if(($maxid = $this->getmaxid() - $_G['setting']['blockmaxaggregationitem'] ) > 0) {
				$wheres[] = 'at.aid > '.$maxid;
			}
		}
		if(empty($aids) && ($picrequired)) {
			$wheres[] = "at.pic != ''";
		}
		if($publishdateline) {
			$time = TIMESTAMP - $publishdateline;
			$wheres[] = "at.dateline >= '$time'";
		}
		if($starttime) {
			$wheres[] = "at.dateline >= '$starttime'";
		}
		if($endtime) {
			$wheres[] = "at.dateline <= '$endtime'";
		}
		if($bannedids) {
			$wheres[] = 'at.aid NOT IN ('.dimplode($bannedids).')';
		}
		$wheres[] = "at.status='0'";
		if(is_array($tag)) {
			$article_tags = array();
			foreach($tag as $k) {
				$article_tags[$k] = 1;
			}
			include_once libfile('function/portalcp');
			$v=article_make_tag($article_tags);
			if($v > 0) {
				$wheres[] = "(at.tag & $v) = $v";
			}
		}
		if($keyword) {
			require_once libfile('function/search');
			$keyword = searchkey($keyword, "at.title LIKE '%{text}%'");
		}

		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		if(in_array($orderby, $clickarr)) {
			$orderby = "at.$orderby DESC,at.dateline DESC";
		} else {
			$orderby = ($orderby == 'dateline') ? 'at.dateline DESC ' : "ac.$orderby DESC";
		}
		$query = DB::query("SELECT at.*, ac.viewnum, ac.commentnum FROM ".DB::table('portal_article_title')." at LEFT JOIN ".DB::table('portal_article_count')." ac ON at.aid=ac.aid WHERE $wheresql$keyword ORDER BY $orderby LIMIT $startrow, $items");
		while($data = DB::fetch($query)) {
			if(empty($data['pic'])) {
				$data['pic'] = STATICURL.'image/common/nophoto.gif';
				$data['picflag'] = '0';
			} else {
				$data['pic'] = $data['pic'];
				$data['picflag'] = $data['remote'] == '1' ? '2' : '1';
			}
			$list[] = array(
				'id' => $data['aid'],
				'idtype' => 'aid',
				'title' => cutstr($data['title'], $titlelength, ''),
				'url' => fetch_article_url($data),
				'pic' => $data['pic'],
				'picflag' => $data['picflag'],
				'summary' => cutstr(strip_tags($data['summary']), $summarylength, ''),
				'fields' => array(
					'uid'=>$data['uid'],
					'username'=>$data['username'],
					'avatar' => avatar($data['uid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar($data['uid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar($data['uid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
					'fulltitle' => $data['title'],
					'dateline'=>$data['dateline'],
					'caturl'=> $_G['cache']['portalcategory'][$data['catid']]['caturl'],
					'catname' => $_G['cache']['portalcategory'][$data['catid']]['catname'],
					'articles' => $_G['cache']['portalcategory'][$data['catid']]['articles'],
					'viewnum' => intval($data['viewnum']),
					'commentnum' => intval($data['commentnum'])
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}

	function getmaxid() {
		loadcache('databasemaxid');
		$data = getglobal('cache/databasemaxid');
		if(!isset($data['article']) || TIMESTAMP - $data['article']['dateline'] >= 86400) {
			$data['article']['dateline'] = TIMESTAMP;
			$data['article']['id'] = DB::result_first('SELECT MAX(aid) FROM '.DB::table('portal_article_title'));
			savecache('databasemaxid', $data);
		}
		return $data['article']['id'];
	}

}

?>