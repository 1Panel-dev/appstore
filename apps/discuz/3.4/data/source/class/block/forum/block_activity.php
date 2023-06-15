<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_activity.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_activity extends discuz_block {

	var $setting = array();

	function block_activity(){
		$this->setting = array(
			'tids' => array(
				'title' => 'activitylist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'activitylist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'activitylist_keyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'activitylist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'digest' => array(
				'title' => 'activitylist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'activitylist_digest_1'),
					array(2, 'activitylist_digest_2'),
					array(3, 'activitylist_digest_3'),
					array(0, 'activitylist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'activitylist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'activitylist_stick_1'),
					array(2, 'activitylist_stick_2'),
					array(3, 'activitylist_stick_3'),
					array(0, 'activitylist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'activitylist_recommend',
				'type' => 'radio'
			),
			'place' => array(
				'title' => 'activitylist_place',
				'type' => 'text'
			),
			'class' => array(
				'title' => 'activitylist_class',
				'type' => 'select',
				'value' => array()
			),
			'gender' => array(
				'title' => 'activitylist_gender',
				'type' => 'mradio',
				'value' => array(
					array('', 'activitylist_gender_0'),
					array('1', 'activitylist_gender_1'),
					array('2', 'activitylist_gender_2'),
				),
				'default' => ''
			),
			'orderby' => array(
				'title' => 'activitylist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('dateline', 'activitylist_orderby_dateline'),
					array('weekstart', 'activitylist_orderby_weekstart'),
					array('monthstart', 'activitylist_orderby_monthstart'),
					array('weekexp', 'activitylist_orderby_weekexp'),
					array('monthexp', 'activitylist_orderby_monthexp'),
					array('weekhot', 'activitylist_orderby_weekhot'),
					array('monthhot', 'activitylist_orderby_monthhot'),
					array('alltimehot', 'activitylist_orderby_alltimehot'),
				),
				'default' => 'dateline'
			),
			'highlight' => array(
				'title' => 'activitylist_highlight',
				'type' => 'radio',
				'default' => 0,
			),
			'titlelength' => array(
				'title' => 'activitylist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'activitylist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'activitylist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_activity_script_activity');
	}

	function blockclass() {
		return array('activity', lang('blockclass', 'blockclass_activity_activity'));
	}

	function fields() {
		return array(
					'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
					'url' => array('name' => lang('blockclass', 'blockclass_activity_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_activity_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_activity_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_activity_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'time' => array('name' => lang('blockclass', 'blockclass_activity_field_time'), 'formtype' => 'text', 'datatype' => 'text'),
					'expiration' => array('name' => lang('blockclass', 'blockclass_activity_field_expiration'), 'formtype' => 'text', 'datatype' => 'text'),
					'author' => array('name' => lang('blockclass', 'blockclass_activity_field_author'), 'formtype' => 'text', 'datatype' => 'text'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_activity_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'cost' => array('name' => lang('blockclass', 'blockclass_activity_field_cost'), 'formtype' => 'text', 'datatype' => 'int'),
					'place' => array('name' => lang('blockclass', 'blockclass_activity_field_place'), 'formtype' => 'text', 'datatype' => 'text'),
					'class' => array('name' => lang('blockclass', 'blockclass_activity_field_class'), 'formtype' => 'text', 'datatype' => 'text'),
					'gender' => array('name' => lang('blockclass', 'blockclass_activity_field_gender'), 'formtype' => 'text', 'datatype' => 'text'),
					'number' => array('name' => lang('blockclass', 'blockclass_activity_field_number'), 'formtype' => 'text', 'datatype' => 'int'),
					'applynumber' => array('name' => lang('blockclass', 'blockclass_activity_field_applynumber'), 'formtype' => 'text', 'datatype' => 'int'),
				);
	}

	function fieldsconvert() {
		return array(
				'group_activity' => array(
					'name' => lang('blockclass', 'blockclass_group_activity'),
					'script' => 'groupactivity',
					'searchkeys' => array(),
					'replacekeys' => array(),
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
		$activitytype = explode("\n", $_G['setting']['activitytype']);
		$settings['class']['value'][] = array('', 'activitylist_class_all');
		foreach($activitytype as $item) {
			$item = trim($item);
			$settings['class']['value'][] = array($item, $item);
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('dateline','weekstart','monthstart','weekexp','monthexp','weekhot','monthhot','alltimehot')) ? $parameter['orderby'] : 'dateline') : 'dateline';
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
		$place		= !empty($parameter['place']) ? $parameter['place'] : '';
		$class		= !empty($parameter['class']) ? trim($parameter['class']) : '';
		$gender		= !empty($parameter['gender']) ? intval($parameter['gender']) : '';
		$viewmod	= !empty($parameter['viewmod']) ? 1 : 0;
		$highlight = !empty($parameter['highlight']) ? 1 : 0;

		$fids = array();
		if(!empty($parameter['fids'])) {
			if($parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		require_once libfile('function/post');
		require_once libfile('function/search');

		$datalist = $list = array();
		$keyword = $keyword ? searchkey($keyword, "t.subject LIKE '%{text}%'") : '';
		$sql = ($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.$keyword
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($bannedids ? ' AND t.tid NOT IN ('.dimplode($bannedids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			." AND t.isgroup='0'";
		$where = '';
		if(in_array($orderby, array('weekstart','monthstart'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekstart':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthstart':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.starttimefrom>='.TIMESTAMP.' AND a.starttimefrom<='.$historytime;
			$orderby = 'a.starttimefrom ASC';
		} elseif(in_array($orderby, array('weekexp','monthexp'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekexp':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthexp':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.expiration>='.TIMESTAMP.' AND a.expiration<='.$historytime;
			$orderby = 'a.expiration ASC';
		} elseif(in_array($orderby, array('weekhot','monthhot'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekhot':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthhot':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.expiration>='.TIMESTAMP.' AND a.expiration<='.$historytime;
			$orderby = 'a.applynumber DESC';
		} elseif($orderby == 'alltimehot') {
			$where = ' WHERE (a.expiration>='.TIMESTAMP." OR a.expiration='0')";
			$orderby = 'a.applynumber DESC';
		} else {
			$orderby = 't.dateline DESC';
		}
		$where .= $uids ? ' AND t.authorid IN ('.dimplode($uids).')' : '';
		if($gender) {
			$where .= " AND a.gender='$gender'";
		}
		if($class) {
			$where .= " AND a.class='$class'";
		}
		$sqlfrom = " INNER JOIN `".DB::table('forum_thread')."` t ON t.tid=a.tid $sql AND t.displayorder>='0'";
		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		if($recommend) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=tr.tid";
		}
		$sqlfield = $highlight ? ', t.highlight' : '';
		$query = DB::query("SELECT a.*, t.tid, t.subject, t.authorid, t.author$sqlfield
			FROM ".DB::table('forum_activity')." a $sqlfrom $where
			ORDER BY $orderby
			LIMIT $startrow,$items;"
			);
		require_once libfile('block_thread', 'class/block/forum');
		$bt = new block_thread();
		$listtids = $threadtids = $threads = $aid2tid = $attachtables = array();
		while($data = DB::fetch($query)) {
			$data['time'] = dgmdate($data['starttimefrom']);
			if($data['starttimeto']) {
				$data['time'] .= ' - '.dgmdate($data['starttimeto']);
			}
			if($style['getsummary']) {
				$threadtids[$data['posttableid']][] = $data['tid'];
			}
			if($data['aid']) {
				$aid2tid[$data['aid']] = $data['tid'];
				$attachtable = getattachtableid($data['tid']);
				$attachtables[$attachtable][] = $data['aid'];
			}
			$listtids[] = $data['tid'];
			$list[$data['tid']] = array(
				'id' => $data['tid'],
				'idtype' => 'tid',
				'title' => cutstr(str_replace('\\\'', '&#39;', $data['subject']), $titlelength, ''),
				'url' => 'forum.php?mod=viewthread&tid='.$data['tid'].($viewmod ? '&from=portal' : ''),
				'pic' => ($data['aid'] ? '' : $_G['style']['imgdir'].'/nophoto.gif'),
				'picflag' => '0',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'time' => $data['time'],
					'expiration' => $data['expiration'] ? dgmdate($data['expiration']) : 'N/A',
					'author' => $data['author'] ? $data['author'] : $_G['setting']['anonymoustext'],
					'authorid' => $data['authorid'] ? $data['authorid'] : 0,
					'cost' => $data['cost'],
					'place' => $data['place'],
					'class' => $data['class'],
					'gender' => $data['gender'],
					'number' => $data['number'],
					'applynumber' => $data['applynumber'],
				)
			);
			if($highlight && $data['highlight']) {
				$list[$data['tid']]['fields']['showstyle'] = $bt->getthreadstyle($data['highlight']);
			}
		}

		if(!empty($listtids)) {
			$query = DB::query("SELECT tid,COUNT(*) as sum FROM ".DB::table('forum_activityapply')." WHERE tid IN(".dimplode($listtids).") GROUP BY tid");
			while($value = DB::fetch($query)) {
				$list[$value['tid']]['fields']['applynumber'] = $value['sum'];
			}

			$threads = $bt->getthread($threadtids, $summarylength, true);
			if($threads) {
				foreach($threads as $tid => $var) {
					$list[$tid]['summary'] = $var;
				}
			}

			foreach($attachtables as $tableid => $taids) {
				$query = DB::query('SELECT aid, attachment, remote FROM '.DB::table('forum_attachment_'.$tableid).' WHERE aid IN ('.dimplode($taids).')');
				while($avalue = DB::fetch($query)) {
					$list[$aid2tid[$avalue['aid']]]['pic'] = 'forum/'.$avalue['attachment'];
					$list[$aid2tid[$avalue['aid']]]['picflag'] = $avalue['remote'] ? '2' : '1';
				}
			}

			foreach($listtids as $key => $value) {
				$datalist[] = $list[$value];
			}

		}
		return array('html' => '', 'data' => $datalist);
	}
}


?>