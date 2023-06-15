<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_visit.php 33714 2013-08-07 01:42:26Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_visit {

	var $version = '1.0';
	var $name = 'visit_name';
	var $description = 'visit_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		$settings = array(
			'num' => array(
				'title' => 'visit_num',
				'type' => 'select',
				'value' => array(
					array('5', '5'),
					array('10', '10'),
					array('20', '20'),
				),
				'default' => '10'
			),
		);
		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		$magicnew['num'] = in_array($parameters['num'], array(5,10,20,50)) ? intval($parameters['num']) : '10';
	}

	function usesubmit() {
		global $_G;

		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		$friends = $uids = $fids = array();
		$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 500);
		foreach($query as $value) {
			$value['username'] = $value['fusername'];
			$value['uid'] = $value['fuid'];
			$uids[] = intval($value['fuid']);
			$friends[$value['fuid']] = $value;
		}
		$count = count($uids);
		if(!$count) {
			showmessage('magicuse_has_no_valid_friend');
		} elseif($count == 1) {
			$fids = array($uids[0]);
		} else {
			$keys = array_rand($uids, min($num, $count));
			$fids = array();
			foreach ($keys as $key) {
				$fids[] = $uids[$key];
			}
		}
		$users = array();
		foreach($fids as $uid) {
			$value = $friends[$uid];
			$value['avatar'] = str_replace("'", "\'", avatar($value['uid'], 'small'));
			$users[$uid] = $value;
		}

		$inserts = array();
		if($_POST['visitway'] == 'poke') {
			$note = '';
			$icon = intval($_POST['visitpoke']);
			foreach ($fids as $fid) {
				$insertdata = array(
						'uid' => $fid,
						'fromuid' => $_G['uid'],
						'fromusername' => $_G['username'],
						'note' => $note,
						'dateline' => $_G['timestamp'],
						'iconid' => $icon
					);
				C::t('home_poke')->insert($insertdata, false, true);
			}
			$repokeids = array();
			foreach(C::t('home_poke')->fetch_all_by_uid_fromuid($_G['uid'], $fids) as $value) {
				$repokeids[] = $value['uid'];
			}
			$ids = array_diff($fids, $repokeids);
			if($ids) {
				require_once libfile('function/spacecp');
				$pokemsg = makepokeaction($icon);
				$pokenote = array(
							'fromurl' => 'home.php?mod=space&uid='.$_G['uid'],
							'fromusername' => $_G['username'],
							'fromuid' => $_G['uid'],
							'from_id' => $_G['uid'],
							'from_idtype' => 'pokequery',
							'pokemsg' => $pokemsg
						);
				foreach($ids as $puid) {
					notification_add($puid, 'poke', 'poke_request', $pokenote);
				}
			}
		} elseif($_POST['visitway'] == 'comment') {
			$message = getstr($_POST['visitmsg'], 255);
			$ip = $_G['clientip'];
			$note_inserts = array();
			foreach ($fids as $fid) {
				$actor = "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>";
				$inserts[] = array(
					'uid' => $fid,
					'id' => $fid,
					'idtype'=> uid,
					'authorid' => $_G['uid'],
					'author' => $_G['username'],
					'ip' => $ip,
					'port' => $_G['remoteport'],
					'dateline' => $_G['timestamp'],
					'message' => $message
				);
				$note = lang('spacecp', 'magic_note_wall', array('actor' => $actor, 'url'=>"home.php?mod=space&uid=$fid&do=wall"));
				$note_inserts[] = array(
					'uid' => $fid,
					'type' => 'comment',
					'new' => 1,
					'authorid' => $_G['uid'],
					'author' => $_G['username'],
					'note' => $note,
					'dateline' => $_G['timestamp']
				);
			}
			foreach($inserts as $insert) {
				C::t('home_comment')->insert($insert);
			}
			foreach($note_inserts as $note_insert) {
				C::t('home_notification')->insert($note_insert);
			}
			C::t('common_member')->increase($fids, array('newprompt' => 1));
		} else {
			foreach ($fids as $fid) {
				C::t('home_visitor')->insert(array('uid'=>$fid, 'vuid'=>$_G['uid'], 'vusername'=>$_G['username'], 'dateline'=>$_G['timestamp']), false, true);
			}
		}
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', 'uid', $_G['uid']);

		$op = 'show';
		include template('home/magic_visit');
	}

	function show() {
		global $_G;
		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		magicshowtips(lang('magic/visit', 'visit_info', array('num'=>$num)));
		$op = 'use';
		include template('home/magic_visit');
	}

}

?>