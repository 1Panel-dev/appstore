<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT . './source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

define('PMODURL', 'action=plugins&operation&config&identifier=wechat&pmod=masssend_setting&ac=');
$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);

if (!submitcheck('addsubmit') && !submitcheck('sendsubmit') && !submitcheck('delsubmit')) {

	$ac = !empty($_GET['ac']) ? $_GET['ac'] : '';
	if (!$ac) {
		showtips(lang('plugin/wechat', 'mass_main_tips'));
		echo '<a href="' . ADMINSCRIPT . '?' . PMODURL . 'add" class="addtr">' . lang('plugin/wechat', 'mass_create') . '</a>';
		$ppp = 10;
		$page = max(1, intval($_GET['page']));
		$start = ($page - 1) * $ppp;
		$count = C::t('#wechat#mobile_wechat_masssend')->count();
		$msg = C::t('#wechat#mobile_wechat_masssend')->range($start, $ppp, 'DESC');
		$multi = multi($count, $ppp, $page, ADMINSCRIPT . '?' . PMODURL);
		showformheader('plugins&operation=config&identifier=wechat&pmod=masssend_setting&ac=send', 'enctype');
		showtableheader(lang('plugin/wechat', 'mass_list'), '');
		showsubtitle(array(lang('plugin/wechat', 'mass_text_oper'), lang('plugin/wechat', 'mass_text_send'), lang('plugin/wechat', 'mass_type'), lang('plugin/wechat', 'mass_created_at'), 'MSG_ID', lang('plugin/wechat', 'mass_finish_at'), lang('plugin/wechat', 'mass_status'), lang('plugin/wechat', 'mass_totalcount'), lang('plugin/wechat', 'mass_filtercount'), lang('plugin/wechat', 'mass_sendcount'), lang('plugin/wechat', 'mass_errorcount')));
		foreach ($msg as $m) {
			showtablerow('', array(), array(
			    '<a href="' . ADMINSCRIPT . '?' . PMODURL . 'del&id=' . $m['id'] . '">' . lang('plugin/wechat', 'mass_delete') . '</a> | <a href="' . ADMINSCRIPT . '?' . PMODURL . 'add&id=' . $m['id'] . '">' . lang('plugin/wechat', 'mass_edit') . '</a>',
			    "<input type=\"radio\" name=\"massid\" value=\"$m[id]\" class=\"radio\">",
			    $m['type'],
			    dgmdate($m['created_at']),
			    $m['msg_id'],
			    $m['res_finish_at'] ? dgmdate($m['res_finish_at']) : '',
			    $m['res_status'],
			    $m['res_totalcount'],
			    $m['res_filtercount'],
			    $m['res_sentcount'],
			    $m['res_errorcount'],
			));
		}
		showtablefooter();
		echo '<br style="clear:both"><div class="right pg">' . $multi . '</div>';
		showsubmit('sendsubmit', lang('plugin/wechat', 'mass_send'));
		showformfooter();
	} else if ($ac == 'add') {
		$groups = $wechat_client->getAllGroups();
		if (!$groups) {
			cpmsg_error('wechat:mass_get_group_failed');
		}

		if (intval($_GET['id']) > 0) {
			$mass = C::t('#wechat#mobile_wechat_masssend')->fetch(intval($_GET['id']));
		}
		$massmessage = $mass['resource_id'] ? "[resource=$mass[resource_id]]" : $mass['text'];
		showtips(lang('plugin/wechat', 'mass_add_tips'));
		WeChatSetting::showResource();
		showformheader('plugins&operation=config&identifier=wechat&pmod=masssend_setting&ac=add', 'enctype');
		showtableheader();
		showtablerow('', '', array(lang('plugin/wechat', 'mass_type')));
		showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
		    "<textarea class=\"tarea\" name=\"massmessage\" id=\"res_subscribe\" rows=\"5\" cols=\"40\">$massmessage</textarea>"
		    . "<br /><a href=\"javascript:;\" id=\"rsel\" onclick=\"showResource('res_subscribe')\">" . lang('plugin/wechat', 'mass_select_media') . "</a>"
		));
		$select = array('group_id', array());
		foreach ($groups as $g) {
			$select[1][] = array($g['id'], diconv($g['name'], 'UTF-8', CHARSET) . "[$g[count]]");
		}
		showsetting(lang('plugin/wechat', 'mass_group_id'), $select, '', 'select');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	} else if ($ac == 'del') {
		$massid = intval($_GET['id']);
		$msg = C::t('#wechat#mobile_wechat_masssend')->fetch($massid);
		if (!$msg) {
			cpmsg_error('wechat:mass_not_exist');
		}
		if (!$msg['res_finish_at'] && $msg['msg_id']) {
			cpmsg('wechat:mass_del_tips_1', PMODURL . '&massid=' . $_GET['id'] . '&delsubmit=yes', 'form');
		}
		cpmsg('wechat:mass_del_tips_2', PMODURL . '&massid=' . $_GET['id'] . '&delsubmit=yes', 'form');
	}
} elseif (submitcheck('addsubmit')) {
	$group_id = intval($_GET['group_id']);
	$massmessage = trim($_GET['massmessage']);
	if (empty($massmessage)) {
		cpmsg_error('wechat:mass_no_text');
	}
	if (preg_match("/^\[resource=(\d+)\]/", $massmessage, $r)) {
		$resource_id = $r[1];

		$res = C::t('#wechat#mobile_wechat_resource')->fetch($resource_id);
		if (!$res) {
			cpmsg('wechat:mass_no_found');
		}
		$news = array();
		if ($res['type'] == 0) {
			if ($res['data']['pic']) {
				$thumb_media_id = $wechat_client->upload('image', $_G['setting']['attachdir'] . 'common/' . $res['data']['local']);
				if (!$thumb_media_id) {
					cpmsg_error($wechat_client->error());
				}
				$res['data']['thumb_media_id'] = $thumb_media_id;
				$res['data']['author'] = '';
			} else {
				cpmsg('wechat:mass_no_pic');
			}
			array_push($news, $res['data']);
		} else if ($res['type'] == 1) {
			$news = array();
			foreach (array_keys($res['data']['mergeids']) as $resource_id) {
				$res = C::t('#wechat#mobile_wechat_resource')->fetch($resource_id);
				if (!$res) {
					cpmsg('wechat:mass_no_found');
				}
				if ($res['data']['pic']) {
					$thumb_media_id = $wechat_client->upload('image', $_G['setting']['attachdir'] . 'common/' . $res['data']['local']);
					if (!$thumb_media_id) {
						cpmsg_error($wechat_client->error());
					}
					$res['data']['thumb_media_id'] = $thumb_media_id;
					$res['data']['author'] = '';
					array_push($news, $res['data']);
				} else {
					cpmsg_error('wechat:mass_no_pic');
				}
			}
		}
		$newsRes = $wechat_client->uploadNews($news);
		if (!$newsRes) {
			cpmsg_error($wechat_client->error());
		}
		C::t('#wechat#mobile_wechat_masssend')->insert(array(
		    'type' => 'media',
		    'resource_id' => $res['id'],
		    'text' => '',
		    'group_id' => $group_id,
		    'media_id' => $newsRes['media_id'],
		    'created_at' => $newsRes['created_at']
		));
		cpmsg('wechat:mass_created_succ', PMODURL, 'succeed');
	} else {
		$data = array(
		    'resource_id' => 0,
		    'type' => 'text',
		    'text' => $massmessage,
		    'group_id' => $group_id,
		    'created_at' => TIMESTAMP
		);
		C::t('#wechat#mobile_wechat_masssend')->insert($data);
		cpmsg('wechat:mass_created_succ', PMODURL, 'succeed');
	}
} else if (submitcheck('sendsubmit')) {
	$massid = intval($_GET['massid']);
	if (!$massid) {
		cpmsg_error('wechat:mass_not_exist');
	}
	$msg = C::t('#wechat#mobile_wechat_masssend')->fetch($massid);
	if (!$msg) {
		cpmsg_error('wechat:mass_not_exist');
	}

	if ($msg['type'] == 'media' && ($msg['created_at'] + 86400 * 3) < TIMESTAMP) {
		cpmsg_error('wechat:mass_send_expire');
	}

	$res = $wechat_client->sendMassMsg($msg);

	if ($res) {
		C::t('#wechat#mobile_wechat_masssend')->update($massid, array('msg_id' => $res['msg_id'], 'sent_at' => TIMESTAMP));
		$updatedata = array('receiveEvent::masssendjobfinish' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'masssendFinish'));
		$responsehook = WeChatHook::updateResponse($updatedata);
		cpmsg('wechat:mass_sent_succ', PMODURL, 'succeed');
	} else {
		cpmsg_error($wechat_client->error());
	}
} else if (submitcheck('delsubmit')) {
	$massid = intval($_GET['massid']);
	$msg = C::t('#wechat#mobile_wechat_masssend')->fetch($massid);
	if (!$msg) {
		cpmsg_error('wechat:mass_not_exist');
	}
	C::t('#wechat#mobile_wechat_masssend')->delete($massid);
	cpmsg('wechat:mass_oper_succ', PMODURL, 'succeed');
}
?>