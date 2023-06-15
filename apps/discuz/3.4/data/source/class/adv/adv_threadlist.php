<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_threadlist.php 29052 2012-03-23 09:07:40Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_threadlist {

	var $version = '1.0';
	var $name = 'threadlist_name';
	var $description = 'threadlist_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('forum', 'group');
	var $imagesizes = array('120x60', '468x40', '468x60');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'threadlist_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'groups' => array(
				'title' => 'threadlist_groups',
				'type' => 'mselect',
				'value' => array(),
			),
			'pos' => array(
				'title' => 'threadlist_pos',
				'type' => 'text',
				'value' => '',
			),
			'mode' => array(
				'title' => 'threadlist_mode',
				'type' => 'mradio',
				'value' => array(
				    array(0, 'threadlist_mode_0'),
				    array(1, 'threadlist_mode_1'),
				),
			),
			'tid' => array(
				'title' => 'threadlist_tid',
				'type' => 'text',
				'value' => '',
			),
			'threadurl' => array(
				'title' => 'threadlist_threadurl',
				'type' => 'text',
				'value' => '',
			),
		);
		loadcache(array('forums', 'grouptype'));
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		foreach($_G['cache']['grouptype']['first'] as $gid => $group) {
			$settings['groups']['value'][] = array($gid, $group['name']);
			if($group['secondlist']) {
				foreach($group['secondlist'] as $sgid) {
					$settings['groups']['value'][] = array($sgid, str_repeat('&nbsp;', 4).$_G['cache']['grouptype']['second'][$sgid]['name']);
				}
			}
		}

		return $settings;
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
		if(is_array($parameters['extra']['groups']) && in_array(0, $parameters['extra']['groups'])) {
			$parameters['extra']['groups'] = array();
		}
		$parameters['extra']['pos'] = $parameters['pos'];
		$parameters['extra']['tid'] = $parameters['tid'];
		$parameters['extra']['threadurl'] = $parameters['threadurl'];
	}

	function evalcode() {
		return array(
			'check' => '
			if($GLOBALS[\'page\'] != 1 || !empty($_GET[\'filter\'])
			|| $_G[\'basescript\'] == \'forum\' && $parameter[\'fids\'] && !in_array($_G[\'fid\'], $parameter[\'fids\'])
			|| $_G[\'basescript\'] == \'group\' && $parameter[\'groups\'] && !in_array($_G[\'grouptypeid\'], $parameter[\'groups\'])
			) {
				$checked = false;
			} else {
				if(empty($_G[\'adv_vtp_count\'])) {
					for($i = 1;$i <= $_G[\'forum_threadnum\'];$i++) {
						if(empty($parameter[\'pos\'])) {
							$_G[\'adv_vtp\'][0][$i][] = $adid;
						} elseif($parameter[\'pos\'] == $i) {
							$_G[\'adv_vtp\'][1][$i][] = $adid;
						}
					}
					$_G[\'adv_vtp_showed\'] = $_G[\'adv_vtp_thread\'] = array();
				}
				if($parameter[\'mode\'] && $parameter[\'tid\']) {
					$_G[\'adv_vtp_tids\'][] = $parameter[\'tid\'];
				}
			}',
			'create' => '
			$_G[\'adv_vtp_count\']++;
			if($_G[\'adv_vtp_count\'] == 1 && !empty($_G[\'adv_vtp_tids\'])) {
				foreach(C::t(\'forum_thread\')->fetch_all_by_tid($_G[\'adv_vtp_tids\']) as $row) {
					$_G[\'adv_vtp_thread\'][$row[\'tid\']] = $row;
				}
			}
			$vt = $_G[\'adv_vtp_thread\'];
			$adi = !empty($_G[\'adv_vtp\'][1][$_G[\'adv_vtp_count\']]) ? 1 : 0;
			$adary = array_diff($_G[\'adv_vtp\'][$adi][$_G[\'adv_vtp_count\']], $_G[\'adv_vtp_showed\']);
			$adid = empty($adary) ? 0 : $adary[array_rand($adary)];
			$_G[\'adv_vtp_showed\'][] = $adid;
			$vttid = $parameters[$adid][\'tid\'];
			$notag = true;
			$adcode = $adid ? (!$parameters[$adid][\'mode\'] ? \'<tbody><tr><td colspan=\'.($_G[\'forum\'][\'ismoderator\'] && !$_GET[\'archiveid\'] ? 6 : 5).\'>\'.$codes[$adid].\'</td></tr></tbody>\'
			: \'<tr><td class="icn"><a href="forum.php?mod=viewthread&tid=\'.$vt[$vttid][\'tid\'].\'" target="_blank"><img src="\'.$_G[\'style\'][\'imgdir\'].\'/folder_new.gif" /></a></td>\'.
				($_G[\'forum\'][\'ismoderator\'] && !$_GET[\'archiveid\'] ? \'<td class="o"></td>\' : \'\').
				\'<td class="new"><a href="\'.($parameters[$adid][\'threadurl\'] ? $parameters[$adid][\'threadurl\'] : \'forum.php?mod=viewthread&tid=\'.$vt[$vttid][\'tid\']).\'" class="xst">\'.$codes[$adid].\'</a></td>\'.
				\'<td class="by"><cite><a href="home.php?mod=space&uid=\'.$vt[$vttid][\'authorid\'].\'">\'.$vt[$vttid][\'author\'].\'</a></cite><em>\'.dgmdate($vt[$vttid][\'dateline\'], \'d\').\'</em></td>\'.
				\'<td class="num"><a href="forum.php?mod=viewthread&tid=\'.$vt[$vttid][\'tid\'].\'" class="xi2">\'.$vt[$vttid][\'replies\'].\'</a><em>\'.$vt[$vttid][\'views\'].\'</em></td>\'.
				\'<td class="by"><cite><a href="forum.php?mod=viewthread&tid=\'.$vt[$vttid][\'tid\'].\'">\'.$vt[$vttid][\'lastposter\'].\'</a></cite>\'.
				\'<em><a href="forum.php?mod=redirect&tid=\'.$vt[$vttid][\'tid\'].\'&goto=lastpost#lastpost">\'.dgmdate($vt[$vttid][\'lastpost\'], \'u\').\'</a></em></td>\'.
				\'</tr>\') : \'\';',
		);
	}

}

?>