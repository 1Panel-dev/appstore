<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_sort.php 29557 2012-04-18 10:10:07Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_sort extends commonblock_html {

	var $setting = array();

	function block_sort(){
		$this->setting = array(
			'tids' => array(
				'title' => 'sortlist_tids',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'sortlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'sortids' => array(
				'title' => 'sortlist_sortids',
				'type' => 'mradio',
				'value' => array()
			),
			'digest' => array(
				'title' => 'sortlist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'sortlist_digest_1'),
					array(2, 'sortlist_digest_2'),
					array(3, 'sortlist_digest_3'),
					array(0, 'sortlist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'sortlist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'sortlist_stick_1'),
					array(2, 'sortlist_stick_2'),
					array(3, 'sortlist_stick_3'),
					array(0, 'sortlist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'sortlist_recommend',
				'type' => 'radio'
			),
			'orderby' => array(
				'title' => 'sortlist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('lastpost', 'sortlist_orderby_lastpost'),
					array('dateline', 'sortlist_orderby_dateline'),
					array('replies', 'sortlist_orderby_replies'),
					array('views', 'sortlist_orderby_views'),
					array('heats', 'sortlist_orderby_heats'),
					array('recommends', 'sortlist_orderby_recommends'),
				),
				'default' => 'lastpost'
			),
			'lastpost' => array(
				'title' => 'sortlist_lastpost',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'sortlist_lastpost_nolimit'),
					array('3600', 'sortlist_lastpost_hour'),
					array('86400', 'sortlist_lastpost_day'),
					array('604800', 'sortlist_lastpost_week'),
					array('2592000', 'sortlist_lastpost_month'),
				),
				'default' => '0'
			),
			'startrow' => array(
				'title' => 'sortlist_startrow',
				'type' => 'text',
				'default' => 0
			),
			'showitems' => array(
				'title' => 'sortlist_showitems',
				'type' => 'text',
				'default' => 10
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_html_script_sort');
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
			$defaultvalue = '';
			$query = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." ORDER BY typeid DESC");
			while($threadtype = DB::fetch($query)) {
				if($threadtype['special']) {
					if(empty($defaultvalue)) {
						$defaultvalue = $threadtype['typeid'];
					}
					$settings['sortids']['value'][] = array($threadtype['typeid'], $threadtype['name']);
				}
			}
			$settings['sortids']['default'] = $defaultvalue;
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$fids		= isset($parameter['fids']) && !in_array(0, (array)$parameter['fids']) ? $parameter['fids'] : array_keys($_G['cache']['forums']);
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['showitems']) ? intval($parameter['showitems']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('lastpost','dateline','replies','views','heats','recommends')) ? $parameter['orderby'] : 'lastpost') : 'lastpost';
		$lastpost	= isset($parameter['lastpost']) ? intval($parameter['lastpost']) : 0;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$sortid	= isset($parameter['sortids']) ? intval($parameter['sortids']) : '';

		if($fids) {
			$thefids = array();
			foreach($fids as $fid) {
				if($_G['cache']['forums'][$fid]['type']=='group') {
					$thefids[] = $fid;
				}
			}
			if($thefids) {
				foreach($_G['cache']['forums'] as $value) {
					if($value['fup'] && in_array($value['fup'], $thefids)) {
						$fids[] = intval($value['fid']);
					}
				}
			}
			$fids = array_unique($fids);
		}

		$datalist = $list = array();
		$threadtypeids = array();

		$sql = ($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($sortid ? ' AND t.sortid='.$sortid : '')
			.($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			." AND t.closed='0' AND t.isgroup='0'";
		if($lastpost) {
			$historytime = TIMESTAMP - $lastpost;
			$sql .= " AND t.dateline>='$historytime'";
		}
		if($orderby == 'heats') {
			$_G['setting']['indexhot']['days'] = !empty($_G['setting']['indexhot']['days']) ? intval($_G['setting']['indexhot']['days']) : 8;
			$heatdateline = TIMESTAMP - 86400 * $_G['setting']['indexhot']['days'];
			$sql .= " AND t.dateline>'$heatdateline' AND t.heats>'0'";
		}
		$sqlfrom = "FROM `".DB::table('forum_thread')."` t";
		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		if($recommend) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=t.tid";
		}

		require_once libfile('function/threadsort');
		$templatearray = $sortoptionarray = array();
		loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));
		sortthreadsortselectoption($sortid);
		$templatearray[$sortid] = $_G['cache']['threadsort_template_'.$sortid]['block'];
		$sortoptionarray[$sortid] = $_G['cache']['threadsort_option_'.$sortid];
		$isthreadtype = (strpos($templatearray[$sortid], '{typename}') !== false || strpos($templatearray[$sortid], '{typename_url}') !== false ) ? true : false;
		$threadtypes = array();
		if($isthreadtype && $fids) {
			foreach(C::t('forum_forumfield')->fetch_all($fids) as $fid => $forum) {
				$threadtypes[$fid] = dunserialize($forum['threadtypes']);
			}
		}

		$html = '';
		$threadlist = $verify = $verifyuids = array();
		$query = DB::query("SELECT t.*
			$sqlfrom WHERE 1 $sql
			AND t.readperm='0'
			AND t.displayorder>='0'
			ORDER BY t.$orderby DESC
			LIMIT $startrow,$items;"
			);

		while($thread = DB::fetch($query)) {

			if(isset($_G['setting']['verify']['enabled']) && $_G['setting']['verify']['enabled']) {
				$verifyuids[$thread['authorid']] = $thread['authorid'];
			}

			if($thread['highlight']) {
				$color = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
				$string = sprintf('%02d', $thread['highlight']);
				$stylestr = sprintf('%03b', $string[0]);

				$thread['highlight'] = ' style="';
				$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
				$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
				$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
				$thread['highlight'] .= $string[1] ? 'color: '.$color[$string[1]] : '';
				$thread['highlight'] .= '"';
			} else {
				$thread['highlight'] = '';
			}

			$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
			$fid = $thread['fid'];
			if($thread['typeid'] && $isthreadtype && $threadtypes[$fid] && !empty($threadtypes[$fid]['prefix']) && isset($threadtypes[$fid]['types'][$thread['typeid']])) {
				if($threadtypes[$fid]['prefix'] == 1) {
					$thread['typehtml'] = '<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.$threadtypes[$fid]['types'][$thread['typeid']].'</a>]</em>';
				} elseif($threadtypes[$fid]['icons'][$thread['typeid']] && $threadtypes[$fid]['prefix'] == 2) {
					$thread['typehtml'] = '<em><a title="'.$threadtypes[$fid]['types'][$thread['typeid']].'" href="forum.php?mod=forumdisplay&fid='.$fid.'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.'<img style="vertical-align: middle;padding-right:4px;" src="'.$threadtypes[$fid]['icons'][$thread['typeid']].'" alt="'.$threadtypes[$fid]['types'][$thread['typeid']].'" /></a></em>';
				}
				$thread['typename'] = $threadtypes[$fid]['types'][$thread['typeid']];
			} else {
				$thread['typename'] = $thread['typehtml'] = '';
			}

			$thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
			$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
			$threadlist[$thread['tid']] = $thread;
		}

		if(!empty($threadlist)) {
			if($verifyuids) {
				foreach(C::t('common_member_verify')->fetch_all($verifyuids) as $value) {
					foreach($_G['setting']['verify'] as $vid => $vsetting) {
						if($vsetting['available'] && $vsetting['showicon'] && $value['verify'.$vid] == 1) {
							$srcurl = '';
							if(!empty($vsetting['icon'])) {
								$srcurl = $vsetting['icon'];
							}
							$verify[$value['uid']] .= "<a href=\"home.php?mod=spacecp&ac=profile&op=verify&vid=$vid\" target=\"_blank\">".(!empty($srcurl) ? '<img src="'.$srcurl.'" class="vm" alt="'.$vsetting['title'].'" title="'.$vsetting['title'].'" />' : $vsetting['title']).'</a>';
						}
					}

				}
			}
			$html = implode('', showsortmodetemplate($sortid, $fids, $sortoptionarray, $templatearray, $threadlist, array_keys($threadlist), $verify));
		}

		return array('html' => $html, 'data' => null);
	}

}


?>