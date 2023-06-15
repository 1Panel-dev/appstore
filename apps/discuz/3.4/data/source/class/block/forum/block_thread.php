<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_thread.php 32768 2013-03-07 09:40:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_thread extends discuz_block {
	var $setting = array();

	function block_thread(){
		$this->setting = array(
			'tids' => array(
				'title' => 'threadlist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'threadlist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'threadlist_keyword',
				'type' => 'text'
			),
			'tagkeyword' => array(
				'title' => 'threadlist_tagkeyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'threadlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'typeids' => array(
				'title' => 'threadlist_typeids',
				'type' => 'text'
			),
			'sortids' => array(
				'title' => 'threadlist_sortids',
				'type' => 'mselect',
				'value' => array()
			),
			'reply' => array(
				'title' => 'threadlist_reply',
				'type' => 'radio'
			),
			'digest' => array(
				'title' => 'threadlist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_digest_1'),
					array(2, 'threadlist_digest_2'),
					array(3, 'threadlist_digest_3'),
					array(0, 'threadlist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'threadlist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_stick_1'),
					array(2, 'threadlist_stick_2'),
					array(3, 'threadlist_stick_3'),
					array(0, 'threadlist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'threadlist_recommend',
				'type' => 'radio'
			),
			'special' => array(
				'title' => 'threadlist_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_special_1'),
					array(2, 'threadlist_special_2'),
					array(3, 'threadlist_special_3'),
					array(4, 'threadlist_special_4'),
					array(5, 'threadlist_special_5'),
					array(0, 'threadlist_special_0'),
				)
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'rewardstatus' => array(
				'title' => 'threadlist_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'threadlist_special_reward_0'),
					array(1, 'threadlist_special_reward_1'),
					array(2, 'threadlist_special_reward_2')
				),
				'default' => 0,
			),
			'picrequired' => array(
				'title' => 'threadlist_picrequired',
				'type' => 'radio',
				'value' => '0'
			),
			'orderby' => array(
				'title' => 'threadlist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('lastpost', 'threadlist_orderby_lastpost'),
					array('dateline', 'threadlist_orderby_dateline'),
					array('replies', 'threadlist_orderby_replies'),
					array('views', 'threadlist_orderby_views'),
					array('heats', 'threadlist_orderby_heats'),
					array('recommends', 'threadlist_orderby_recommends'),
				),
				'default' => 'lastpost'
			),
			'postdateline' => array(
				'title' => 'threadlist_postdateline',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'threadlist_postdateline_nolimit'),
					array('3600', 'threadlist_postdateline_hour'),
					array('86400', 'threadlist_postdateline_day'),
					array('604800', 'threadlist_postdateline_week'),
					array('2592000', 'threadlist_postdateline_month'),
				),
				'default' => '0'
			),
			'lastpost' => array(
				'title' => 'threadlist_lastpost',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'threadlist_lastpost_nolimit'),
					array('3600', 'threadlist_lastpost_hour'),
					array('86400', 'threadlist_lastpost_day'),
					array('604800', 'threadlist_lastpost_week'),
					array('2592000', 'threadlist_lastpost_month'),
				),
				'default' => '0'
			),
			'highlight' => array(
				'title' => 'threadlist_highlight',
				'type' => 'radio',
				'default' => 0,
			),
			'titlelength' => array(
				'title' => 'threadlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'threadlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'threadlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_thread_script_thread');
	}

	function blockclass() {
		return array('thread', lang('blockclass', 'blockclass_forum_thread'));
	}

	function fields() {
		return array(
					'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
					'url' => array('name' => lang('blockclass', 'blockclass_thread_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_thread_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_thread_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_thread_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'author' => array('name' => lang('blockclass', 'blockclass_thread_field_author'), 'formtype' => 'text', 'datatype' => 'string'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_thread_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'avatar' => array('name' => lang('blockclass', 'blockclass_thread_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
					'avatar_middle' => array('name' => lang('blockclass', 'blockclass_thread_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
					'avatar_big' => array('name' => lang('blockclass', 'blockclass_thread_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
					'forumurl' => array('name' => lang('blockclass', 'blockclass_thread_field_forumurl'), 'formtype' => 'text', 'datatype' => 'string'),
					'forumname' => array('name' => lang('blockclass', 'blockclass_thread_field_forumname'), 'formtype' => 'text', 'datatype' => 'string'),
					'typename' => array('name' => lang('blockclass', 'blockclass_thread_field_typename'), 'formtype' => 'text', 'datatype' => 'string'),
					'typeicon' => array('name' => lang('blockclass', 'blockclass_thread_field_typeicon'), 'formtype' => 'text', 'datatype' => 'string'),
					'typeurl' => array('name' => lang('blockclass', 'blockclass_thread_field_typeurl'), 'formtype' => 'text', 'datatype' => 'string'),
					'sortname' => array('name' => lang('blockclass', 'blockclass_thread_field_sortname'), 'formtype' => 'text', 'datatype' => 'string'),
					'sorturl' => array('name' => lang('blockclass', 'blockclass_thread_field_sorturl'), 'formtype' => 'text', 'datatype' => 'string'),
					'posts' => array('name' => lang('blockclass', 'blockclass_thread_field_posts'), 'formtype' => 'text', 'datatype' => 'int'),
					'todayposts' => array('name' => lang('blockclass', 'blockclass_thread_field_todayposts'), 'formtype' => 'text', 'datatype' => 'int'),
					'lastposter' => array('name' => lang('blockclass', 'blockclass_thread_field_lastposter'), 'formtype' => 'string', 'datatype' => 'string'),
					'lastpost' => array('name' => lang('blockclass', 'blockclass_thread_field_lastpost'), 'formtype' => 'date', 'datatype' => 'date'),
					'dateline' => array('name' => lang('blockclass', 'blockclass_thread_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
					'replies' => array('name' => lang('blockclass', 'blockclass_thread_field_replies'), 'formtype' => 'text', 'datatype' => 'int'),
					'views' => array('name' => lang('blockclass', 'blockclass_thread_field_views'), 'formtype' => 'text', 'datatype' => 'int'),
					'heats' => array('name' => lang('blockclass', 'blockclass_thread_field_heats'), 'formtype' => 'text', 'datatype' => 'int'),
					'recommends' => array('name' => lang('blockclass', 'blockclass_thread_field_recommends'), 'formtype' => 'text', 'datatype' => 'int'),
				);
	}

	function fieldsconvert() {
		return array(
				'portal_article' => array(
					'name' => lang('blockclass', 'blockclass_portal_article'),
					'script' => 'article',
					'searchkeys' => array('author', 'authorid', 'forumurl', 'forumname', 'posts', 'views', 'replies'),
					'replacekeys' => array('username', 'uid', 'caturl', 'catname', 'articles', 'viewnum', 'commentnum'),
				),
				'space_blog' => array(
					'name' => lang('blockclass', 'blockclass_space_blog'),
					'script' => 'blog',
					'searchkeys' => array('author', 'authorid', 'views', 'replies'),
					'replacekeys' => array('username', 'uid', 'viewnum', 'replynum'),
				),
				'group_thread' => array(
					'name' => lang('blockclass', 'blockclass_group_thread'),
					'script' => 'groupthread',
					'searchkeys' => array('forumname', 'forumurl'),
					'replacekeys' => array('groupname', 'groupurl'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['fids']) {
			loadcache('forums');
			$settings['fids']['value'][] = array(0, lang('portalcp', 'block_all_forum'));
			foreach($_G['cache']['forums'] as $fid => $forum) {
				$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
			}
		}
		if($settings['sortids']) {
			$settings['sortids']['value'][] = array(0, 'threadlist_sortids_all');
			$query = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." WHERE special>'0' ORDER BY typeid DESC");
			while($threadtype = DB::fetch($query)) {
				$settings['sortids']['value'][] = array($threadtype['typeid'], $threadtype['name']);
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$returndata = array('html' => '', 'data' => '');
		$parameter = $this->cookparameter($parameter);

		loadcache('forums', 'stamps');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('lastpost','dateline','replies','views','heats','recommends')) ? $parameter['orderby'] : 'lastpost') : 'lastpost';
		$lastposter	= !empty($parameter['lastposter']) ? $parameter['lastposter'] : '';
		$lastpost	= isset($parameter['lastpost']) ? intval($parameter['lastpost']) : 0;
		$postdateline	= isset($parameter['postdateline']) ? intval($parameter['postdateline']) : 0;
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$reply		= !empty($parameter['reply']);
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
		$tagkeyword	= !empty($parameter['tagkeyword']) ? $parameter['tagkeyword'] : '';
		$typeids	= !empty($parameter['typeids']) ? explode(',',$parameter['typeids']) : array();
		$sortids	= !empty($parameter['sortids']) && !in_array(0, (array)$parameter['sortids']) ? $parameter['sortids'] : array();
		$special	= !empty($parameter['special']) ? $parameter['special'] : array();
		$rewardstatus	= !empty($parameter['rewardstatus']) ? intval($parameter['rewardstatus']) : 0;
		$picrequired	= !empty($parameter['picrequired']) ? 1 : 0;
		$viewmod	= !empty($parameter['viewmod']) ? 1 : 0;
		$highlight = !empty($parameter['highlight']) ? 1 : 0;

		$fids = array();
		if(!empty($parameter['fids'])) {
			if(isset($parameter['fids'][0]) && $parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		require_once libfile('function/post');
		require_once libfile('function/search');
		require_once libfile('function/discuzcode');

		$datalist = $list = $listtids = $pictids = $pics = $threadtids = $threadtypeids = $tagids = array();
		$keyword = $keyword ? searchkey($keyword, "t.subject LIKE '%{text}%'") : '';
		if($tagkeyword) {
			if(!($tagids = DB::fetch_all('SELECT tagid FROM '.DB::table('common_tag').' WHERE 1'.searchkey($tagkeyword, "tagname LIKE '%{text}%'"), '', 'tagid'))) {
				return array('data' => '');
			}
		}

		$threadsorts = $threadtypes = array();
		$querytmp = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." WHERE special>'0'");
		while($value = DB::fetch($querytmp)) {
			$threadsorts[$value['typeid']] = $value;
		}
		$querytmp = DB::query("SELECT * FROM ".DB::table('forum_threadclass'));
		foreach(C::t('forum_threadclass')->range() as $value) {
			$threadtypes[$value['typeid']] = $value;
		}

		$sql = ($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($uids ? ' AND t.authorid IN ('.dimplode($uids).')' : '')
			.($typeids ? ' AND t.typeid IN ('.dimplode($typeids).')' : '')
			.($sortids ? ' AND t.sortid IN ('.dimplode($sortids).')' : '')
			.($special ? ' AND t.special IN ('.dimplode($special).')' : '')
			.((in_array(3, $special) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			.($bannedids ? ' AND t.tid NOT IN ('.dimplode($bannedids).')' : '')
			.$keyword
			." AND t.isgroup='0'";

		if($postdateline) {
			$time = TIMESTAMP - $postdateline;
			$sql .= " AND t.dateline >= '$time'";
		}
		if($lastpost) {
			$time = TIMESTAMP - $lastpost;
			$sql .= " AND t.lastpost >= '$time'";
		}
		if($orderby == 'heats') {
			$sql .= " AND t.heats>'0'";
		}
		$sqlfrom = $sqlfield = $joinmethodpic = '';

		if($picrequired) {
			$joinmethodpic = 'INNER';
		} else if($style['getpic']) {
			$joinmethodpic = 'LEFT';
		}
		if($joinmethodpic) {
			$sqlfrom .= " $joinmethodpic JOIN `".DB::table('forum_threadimage')."` ti ON t.tid=ti.tid";
			$sqlfield = ', ti.attachment as attachmenturl, ti.remote';
		}

		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		if($recommend) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=t.tid";
		}

		if($reply) {
			$sql .= " AND t.replies>'0'";
		}

		if($tagids) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('common_tagitem')."` tim ON tim.tagid IN (".dimplode(array_keys($tagids)).") AND tim.itemid=t.tid AND tim.idtype='tid' ";
		}

		$maxwhere = '';
		if(!$tids && !$fids && !$digest && !$stick && $_G['setting']['blockmaxaggregationitem']) {
			$maxwhere = ($maxid = $this->getmaxid() - $_G['setting']['blockmaxaggregationitem']) > 0 ? 't.tid > '.$maxid.' AND ' : '';
		}

		$query = DB::query("SELECT DISTINCT t.*$sqlfield
			FROM `".DB::table('forum_thread')."` t
			$sqlfrom WHERE {$maxwhere}t.readperm='0'
			$sql
			AND t.displayorder>='0'
			ORDER BY t.$orderby DESC
			LIMIT $startrow,$items;"
			);
		while($data = DB::fetch($query)) {
			$_G['block_thread'][$data['tid']] = $data;
			if($style['getsummary']) {
				$threadtids[$data['posttableid']][] = $data['tid'];
			}
			$listtids[$data['tid']] = $data['tid'];
			$list[$data['tid']] = array(
				'id' => $data['tid'],
				'idtype' => 'tid',
				'title' => cutstr(str_replace('\\\'', '&#39;', addslashes($data['subject'])), $titlelength, ''),
				'url' => 'forum.php?mod=viewthread&tid='.$data['tid'].($viewmod ? '&from=portal' : ''),
				'pic' => $data['attachmenturl'] ? 'forum/'.$data['attachmenturl'] : STATICURL.'image/common/nophoto.gif',
				'picflag' => $data['attachmenturl'] ? ($data['remote'] ? '2' : '1') : '0',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'threads' => $data['threads'],
					'author' => $data['author'] ? $data['author'] : $_G['setting']['anonymoustext'],
					'authorid' => $data['author'] ? $data['authorid'] : 0,
					'avatar' => avatar(($data['author'] ? $data['authorid'] : 0), 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar(($data['author'] ? $data['authorid'] : 0), 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar(($data['author'] ? $data['authorid'] : 0), 'big', true, false, false, $_G['setting']['ucenterurl']),
					'posts' => $data['posts'],
					'todayposts' => $data['todayposts'],
					'lastposter' => str_replace('\\\'', '&#39;', addslashes($data['lastposter'])),
					'lastpost' => $data['lastpost'],
					'dateline' => $data['dateline'],
					'replies' => $data['replies'],
					'forumurl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'],
					'forumname' => $_G['cache']['forums'][$data['fid']]['name'],
					'typename' => discuzcode($threadtypes[$data['typeid']]['name'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0),
					'typeicon' => $threadtypes[$data['typeid']]['icon'],
					'typeurl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'].'&filter=typeid&typeid='.$data['typeid'],
					'sortname' => discuzcode($threadsorts[$data['sortid']]['name'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0),
					'sorturl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'].'&filter=sortid&sortid='.$data['sortid'],
					'views' => $data['views'],
					'heats' => $data['heats'],
					'recommends' => $data['recommends'],
					'hourviews' => $data['views'],
					'todayviews' => $data['views'],
					'weekviews' => $data['views'],
					'monthviews' => $data['views']
				)
			);
			if($highlight && $data['highlight']) {
				$list[$data['tid']]['fields']['showstyle'] = $this->getthreadstyle($data['highlight']);
			}
		}

		if($listtids) {
			$threads = $this->getthread($threadtids, $summarylength);
			if($threads) {
				foreach($threads as $tid => $var) {
					$list[$tid]['summary'] = $var;
				}
			}

			foreach($listtids as $key => $value) {
				$datalist[] = $list[$value];
			}
		}

		$returndata['data'] = $datalist;
		return $returndata;
	}

	function getthread($tidarray, $messagelength = 80, $nospecial = false) {
		global $_G;
		if(!$tidarray) {
			return '';
		}
		$notexists = $messagearr = $returnarr = array();
		foreach($tidarray as $var) {
			foreach($var as $v) {
				if(empty($_G['block_thread'][$v])) {
					$notexists[] = $v;
				}
			}
		}
		if($notexists) {
			$query = DB::query("SELECT tid, fid, subject, posttableid, price, special FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($notexists).")");
			while($result = DB::fetch($query)) {
				$_G['block_thread'][$result['tid']] = $result;
			}
		}
		foreach($tidarray as $key => $var) {
			if($key == 0) {
				$posttable = 'forum_post';
			} else {
				$posttable = "forum_post_{$key}";
			}
			$query = DB::query("SELECT tid, message FROM ".DB::table($posttable)." WHERE tid IN  (".dimplode($var).") AND first=1");
			while($result = DB::fetch($query)) {
				$messagearr[$result['tid']] = $result['message'];
			}
		}
		require_once libfile('function/post');
		require_once libfile('function/discuzcode');
		if($messagearr) {
			foreach($messagearr as $tid => $var) {
				$thread = $_G['block_thread'][$tid];
				if($nospecial) {
					$thread['special'] = 0;
				}
				if($thread['special'] == 1) {
					$polloptions = array();
					$multiple = DB::result_first("SELECT multiple FROM ".DB::table('forum_poll')." WHERE tid='$tid'");
					$optiontype = $multiple ? 'checkbox' : 'radio';
					$query = DB::query("SELECT polloptionid, polloption FROM ".DB::table('forum_polloption')." WHERE tid='$tid' ORDER BY displayorder");
					while($polloption = DB::fetch($query)) {
						$polloption['polloption'] = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
							"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $polloption['polloption']);
						$polloptions[] = $polloption;
					}
				} elseif($thread['special'] == 2) {
					$trade = C::t('forum_trade')->fetch_first_goods($tid);
					$trade['aid'] = $trade['aid'] ? getforumimg($trade['aid']) : '';
					$trades[$tid][] = $trade;
				} elseif($thread['special'] == 3) {
					$extcredits = $_G['settings']['extcredits'];
					$creditstransextra = $_G['settings']['creditstransextra'];
					$rewardend = $thread['price'] < 0;
					$rewardprice = abs($thread['price']);
					$message = messagecutstr($var, $messagelength, '');
				} elseif($thread['special'] == 4) {
					$message = messagecutstr($var, $messagelength, '');
					$activity = DB::fetch_first("SELECT aid, number, applynumber FROM ".DB::table('forum_activity')." WHERE tid='$tid'");
					$activity['aid'] = $activity['aid'] ? getforumimg($activity['aid']) : '';
					$activity['aboutmember'] = $activity['number'] - $activity['applynumber'];
				} elseif($thread['special'] == 5) {
					$message = messagecutstr($var, $messagelength, '');
					$debate = C::t('forum_debate')->fetch($tid);
					$debate['affirmvoteswidth'] = $debate['affirmvotes']  ? intval(80 * (($debate['affirmvotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
					$debate['negavoteswidth'] = $debate['negavotes']  ? intval(80 * (($debate['negavotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
					$debate['affirmpoint'] = discuzcode($debate['affirmpoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
					$debate['negapoint'] = discuzcode($debate['negapoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
				} else {
					$message = messagecutstr($var, $messagelength, '');
				}
				include template('common/block_thread');
				$returnarr[$tid] = $return;
			}
		}

		return $returnarr;
	}

	function getpic($tid) {
		global $_G;
		if(!$tid) {
			return '';
		}
		$pic = DB::fetch_first("SELECT attachment, remote FROM ".DB::table(getattachtablebytid($tid))." WHERE tid='$tid' AND isimage IN (1, -1) ORDER BY dateline DESC LIMIT 0,1");
		return $pic;
	}

	function getpics($tids) {
		$data = array();
		$tids = !empty($tids) && is_array($tids) ? $tids : array($tids);
		$tids = array_map('intval', $tids);
		$tids = array_filter($tids);
		if(!empty($tids)) {
			$query = DB::query('SELECT * FROM '.DB::table('forum_threadimage').' WHERE tid IN ('.dimplode($tids).')');
			while($value = DB::fetch($query)) {
				$data[$value['tid']] = $value;
			}
		}
		return $data;
	}

	function getthreadstyle($highlight) {
		$rt = array();
		if($highlight) {
			$color = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
			$string = sprintf('%02d', $highlight);
			$stylestr = sprintf('%03b', $string[0]);
			$rt = array(
				'title_b' => $stylestr[0] ? '1' : '',
				'title_i' => $stylestr[1] ? '1' : '',
				'title_u' => $stylestr[2] ? '1' : '',
				'title_c' => $string[1] ? $color[$string[1]] : '',
				);
		}
		return $rt;
	}

	function getmaxid() {
		loadcache('databasemaxid');
		$data = getglobal('cache/databasemaxid');
		if(!isset($data['thread']) || TIMESTAMP - $data['thread']['dateline'] >= 86400) {
			$data['thread']['dateline'] = TIMESTAMP;
			$data['thread']['id'] = DB::result_first('SELECT MAX(tid) FROM '.DB::table('forum_thread'));
			savecache('databasemaxid', $data);
		}
		return $data['thread']['id'];
	}
}


?>