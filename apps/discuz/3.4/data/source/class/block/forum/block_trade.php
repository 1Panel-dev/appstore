<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_trade.php 32768 2013-03-07 09:40:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_trade extends discuz_block {
	var $setting = array();

	function block_trade(){
		$this->setting = array(
			'tids' => array(
				'title' => 'tradelist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'tradelist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'tradelist_keyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'tradelist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'digest' => array(
				'title' => 'tradelist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'tradelist_digest_1'),
					array(2, 'tradelist_digest_2'),
					array(3, 'tradelist_digest_3'),
					array(0, 'tradelist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'tradelist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'tradelist_stick_1'),
					array(2, 'tradelist_stick_2'),
					array(3, 'tradelist_stick_3'),
					array(0, 'tradelist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'tradelist_recommend',
				'type' => 'radio'
			),
			'orderby' => array(
				'title' => 'tradelist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('dateline', 'tradelist_orderby_dateline'),
					array('todayhots', 'tradelist_orderby_todayhots'),
					array('weekhots', 'tradelist_orderby_weekhots'),
					array('monthhots', 'tradelist_orderby_monthhots'),
				),
				'default' => 'dateline'
			),
			'highlight' => array(
				'title' => 'tradelist_highlight',
				'type' => 'radio',
				'default' => 0,
			),
			'titlelength' => array(
				'title' => 'tradelist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'tradelist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'tradelist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_trade_script_trade');
	}

	function blockclass() {
		return array('trade', lang('blockclass', 'blockclass_trade_trade'));
	}

	function fields() {
		return array(
					'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
					'url' => array('name' => lang('blockclass', 'blockclass_trade_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_trade_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_trade_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_trade_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'totalitems' => array('name' => lang('blockclass', 'blockclass_trade_field_totalitems'), 'formtype' => 'text', 'datatype' => 'int'),
					'author' => array('name' => lang('blockclass', 'blockclass_trade_field_author'), 'formtype' => 'text', 'datatype' => 'text'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_trade_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'price' => array('name' => lang('blockclass', 'blockclass_trade_field_price'), 'formtype' => 'text', 'datatype' => 'text'),
				);
	}

	function fieldsconvert() {
		return array(
				'group_trade' => array(
					'name' => lang('blockclass', 'blockclass_group_trade'),
					'script' => 'grouptrade',
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
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('dateline','todayhots','weekhots','monthhots')) ? $parameter['orderby'] : 'dateline') : 'dateline';
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
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

		$datalist = $list = $listpids = $threadpids = $aid2pid = $attachtables = array();
		$keyword = $keyword ? searchkey($keyword, "t.subject LIKE '%{text}%'") : '';
		$sql = ($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			." AND t.isgroup='0'";
		$where = '';
		if(in_array($orderby, array('todayhots','weekhots','monthhots'))) {
			$historytime = 0;
			switch($orderby) {
				case 'todayhots':
					$historytime = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP));
				break;
				case 'weekhots':
					$week = gmdate('w', TIMESTAMP) - 1;
					$week = $week != -1 ? $week : 6;
					$historytime = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP) - $week, date('Y', TIMESTAMP));
				break;
				case 'monthhots':
					$historytime = mktime(0, 0, 0, date('m', TIMESTAMP), 1, date('Y', TIMESTAMP));
				break;
			}
			$where = ' WHERE tr.dateline>='.$historytime;
			$orderby = 'totalitems';
		}
		$where .= ($uids ? ' AND tr.sellerid IN ('.dimplode($uids).')' : '').$keyword;
		$where .= ($bannedids ? ' AND tr.pid NOT IN ('.dimplode($bannedids).')' : '');
		$sqlfrom = " INNER JOIN `".DB::table('forum_thread')."` t ON t.tid=tr.tid $sql AND t.displayorder>='0'";
		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		if($recommend) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=tr.tid";
		}
		$sqlfield = $highlight ? ', t.highlight' : '';
		$query = DB::query("SELECT tr.pid, tr.tid, tr.aid, tr.price, tr.credit, tr.subject, tr.totalitems, tr.seller, tr.sellerid$sqlfield
			FROM ".DB::table('forum_trade')." tr $sqlfrom $where
			ORDER BY tr.$orderby DESC
			LIMIT $startrow,$items;"
			);
		require_once libfile('block_thread', 'class/block/forum');
		$bt = new block_thread();
		while($data = DB::fetch($query)) {
			if($style['getsummary']) {
				$threadpids[$data['posttableid']][] = $data['pid'];
			}
			if($data['aid']) {
				$aid2pid[$data['aid']] = $data['pid'];
				$attachtable = getattachtableid($data['tid']);
				$attachtables[$attachtable][] = $data['aid'];
			}
			$listpids[] = $data['pid'];
			$list[$data['pid']] = array(
				'id' => $data['pid'],
				'idtype' => 'pid',
				'title' => cutstr(str_replace('\\\'', '&#39;', addslashes($data['subject'])), $titlelength, ''),
				'url' => 'forum.php?mod=viewthread&do=tradeinfo&tid='.$data['tid'].'&pid='.$data['pid'].($viewmod ? '&from=portal' : ''),
				'pic' => ($data['aid'] ? '' : $_G['style']['imgdir'].'/nophoto.gif'),
				'picflag' => '0',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'totalitems' => $data['totalitems'],
					'author' => $data['seller'] ? $data['seller'] : $_G['setting']['anonymoustext'],
					'authorid' => $data['sellerid'] ? $data['sellerid'] : 0,
					'price' => ($data['price'] > 0 ? '&yen; '.$data['price'] : '').($data['credit'] > 0 ? ($data['price'] > 0 ? lang('block/tradelist', 'tradelist_price_add') : '').$data['credit'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title'] : ''),
				)
			);
			if($highlight && $data['highlight']) {
				$list[$data['tid']]['fields']['showstyle'] = $bt->getthreadstyle($data['highlight']);
			}
		}
		if(!empty($listpids)) {
			foreach($threadpids as $key => $var) {
				$posttable = $key == 0 ? 'forum_post' : 'forum_post_'.$key;
				$query = DB::query("SELECT pid, message FROM ".DB::table($posttable)." WHERE pid IN  (".dimplode($var).")");
				while($result = DB::fetch($query)) {
					$list[$result['pid']]['summary'] = messagecutstr($result['message'], $summarylength, '');
				}
			}

			foreach($attachtables as $tableid => $taids) {
				$query = DB::query('SELECT aid, attachment, remote FROM '.DB::table('forum_attachment_'.$tableid).' WHERE aid IN ('.dimplode($taids).')');
				while($avalue = DB::fetch($query)) {
					$list[$aid2pid[$avalue['aid']]]['pic'] = 'forum/'.$avalue['attachment'];
					$list[$aid2pid[$avalue['aid']]]['picflag'] = $avalue['remote'] ? '2' : '1';
				}
			}

			foreach($listpids as $key => $value) {
				$datalist[] = $list[$value];
			}
		}
		return array('html' => '', 'data' => $datalist);
	}
}


?>