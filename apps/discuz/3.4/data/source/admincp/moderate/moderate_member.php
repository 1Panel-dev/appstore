<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_member.php 33688 2013-08-02 03:00:15Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$do = empty($do) ? 'mod' : $do;

if($do == 'mod') {

	if(!submitcheck('modsubmit')) {
		$count = C::t('common_member_validate')->fetch_all_status_by_count();

		$sendemail = isset($_GET['sendemail']) ? $_GET['sendemail'] : 0;
		$checksendemail = $sendemail ? 'checked' : '';

		$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

		$validatenum = C::t('common_member_validate')->count_by_status(0);
		$members = '';
		if($validatenum) {
			$multipage = multi($validatenum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT.'?action=moderate&operation=members&sendemail='.$sendemail);
			$vuids = array();
			loadcache('fields_register');
			require_once libfile('function/profile');
			loadcache('usergroups');
			$allvalidate = C::t('common_member_validate')->fetch_all_invalidate($start_limit, $_G['setting']['memberperpage']);
			$uids = array_keys($allvalidate);
			$allmember = C::t('common_member')->fetch_all($uids, false, 0);
			$allmemberstatus = C::t('common_member_status')->fetch_all($uids, false, 0);
			$allmemberprofile = C::t('common_member_profile')->fetch_all($uids, false, 0);
			foreach($allvalidate as $uid => $member) {
				$member = array_merge((array)$member, (array)$allmember[$uid], (array)$allmemberstatus[$uid], (array)$allmemberprofile[$uid]);
				if($member['groupid'] != 8 && !in_array($member['freeze'], array(-1, 2))) {
					$vuids[$uid] = $uid;
					continue;
				}

				$fields = !empty($member['field']) ? dunserialize($member['field']) : array();
				$str = '';
				foreach($_G['cache']['fields_register'] as $field) {
					if(!$field['available'] || in_array($field['fieldid'], array('uid', 'constellation', 'zodiac', 'birthmonth', 'birthyear', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
						continue;
					}
					$member[$field['fieldid']] = !empty($member[$field['fieldid']]) ? $member[$field['fieldid']] : $fields[$field['fieldid']];
					if($member[$field['fieldid']]) {
						$fieldstr = profile_show($field['fieldid'], $member);
						$str .= $field['title'].':'.$fieldstr."<br/>";
					}
				}
				$str = !empty($str) ? '<br/>'.$str : '';
				$member['regdate'] = dgmdate($member['regdate']);
				$member['submitdate'] = dgmdate($member['submitdate']);
				$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
				$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
				$members .= "<tr class=\"hover\" id=\"mod_uid_{$member[uid]}\"><td class=\"rowform\" style=\"width:80px;\"><ul class=\"nofloat\"><li><input id=\"mod_uid_{$member[uid]}_1\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"invalidate\" onclick=\"set_bg('invalidate', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_1\">$lang[invalidate]</label></li><li><input id=\"mod_uid_{$member[uid]}_2\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"validate\" onclick=\"set_bg('validate', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_2\">$lang[validate]</label></li>\n".
					"<li>".($member['groupid'] == 8 ? "<input id=\"mod_uid_{$member[uid]}_3\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"delete\" onclick=\"set_bg('delete', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_3\">$lang[delete]</label>" : "<input disabled class=\"radio\" type=\"radio\" />$lang[delete]")."</li><li><input id=\"mod_uid_{$member[uid]}_4\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"ignore\" onclick=\"set_bg('ignore', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_4\">$lang[ignore]</label></li></ul></td><td><b><a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".$_G['cache']['usergroups'][$member['groupid']]['grouptitle'].
					"<br />$lang[members_edit_regdate]: $member[regdate]<br />$lang[members_edit_regip]: $member[regip] ".convertip($member['regip'])."<br />$lang[members_edit_lastip]: $member[lastip] ".convertip($member['lastip'])."<br />Email: $member[email]$str</td>\n".
					"<td align=\"center\"><textarea rows=\"4\" name=\"userremark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[message]</textarea></td>\n".
					"<td>$lang[moderate_members_submit_times]: $member[submittimes]<br />$lang[moderate_members_submit_time]: $member[submitdate]<br />$lang[moderate_members_admin]: $member[admin]<br />\n".
					"$lang[moderate_members_mod_time]: $member[moddate]</td><td><textarea rows=\"4\" id=\"remark[$member[uid]]\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
			}
			if(!empty($vuids)) {
				C::t('common_member_validate')->delete($vuids);
			}
		}
		shownav('user', 'nav_modmembers');
		showsubmenu('nav_moderate_users', array(
			array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 1),
			array('clean', 'moderate&operation=members&do=del', 0)
		));
		showtips('moderate_members_tips');
		$moderate_members_bad_reason = cplang('moderate_members_bad_reason');
		$moderate_members_succeed = cplang('moderate_members_succeed');
		echo <<<EOT
<script type="text/javascript">
function set_bg(operation, uid) {
	if(operation == 'invalidate') {
		$('mod_uid_' + uid).className = "mod_invalidate";
		$('remark[' + uid + ']').value = '$moderate_members_bad_reason';
	} else if(operation == 'validate') {
		$('mod_uid_' + uid).className = "mod_validate";
		$('remark[' + uid + ']').value = '$moderate_members_succeed';
	} else if(operation == 'ignore') {
		$('mod_uid_' + uid).className = "mod_ignore";
		$('remark[' + uid + ']').value = '';
	} else if(operation == 'delete') {
		$('mod_uid_' + uid).className = "mod_delete";
		$('remark[' + uid + ']').value = '';
	}
	$('chk_apply_all').disabled = true;
	$('chk_apply_all').checked = false;
}
function set_bg_all(operation) {
	var trs = $('cpform').getElementsByTagName('TR');
	for(var i in trs) {
		if(trs[i].id && trs[i].id.substr(0, 8) == 'mod_uid_') {
			uid = trs[i].id.substr(8);
			if(operation == 'invalidate') {
				trs[i].className = 'mod_invalidate';
				$('remark[' + uid + ']').value = '$moderate_members_bad_reason';
			} else if(operation == 'validate') {
				trs[i].className = 'mod_validate';
				$('remark[' + uid + ']').value = '$moderate_members_succeed';
			} else if(operation == 'ignore') {
				trs[i].className = 'mod_ignore';
				$('remark[' + uid + ']').value = '';
			} else if(operation == 'delete') {
				trs[i].className = 'mod_delete';
				$('remark[' + uid + ']').value = '';
			}else if(operation == 'cancel') {
				trs[i].className = '';
				$('remark[' + uid + ']').value = '';
			}
		}
	}
	if(operation != 'cancel') {
		$('chk_apply_all').disabled = false;
		$('chk_apply_all').value = operation;
	} else {
		$('chk_apply_all').disabled = true;
		$('chk_apply_all').checked = false;
	}


}
function cancelallcheck() {
	var form = $('cpform');
	var checkall = 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'radio') {
			e.checked = '';
		}
	}
}
</script>
EOT;
		showformheader('moderate&operation=members&do=mod');
		showtableheader('moderate_members', 'fixpadding');
		showsubtitle(array('operation', 'members_edit_info', 'moderate_members_message', 'moderate_members_info', 'moderate_members_remark'));
		echo $members;
		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'invalidate\');set_bg_all(\'invalidate\');">'.cplang('moderate_all_invalidate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'validate\');set_bg_all(\'validate\');">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'delete\');set_bg_all(\'delete\');">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'ignore\');set_bg_all(\'ignore\');">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="cancelallcheck();set_bg_all(\'cancel\');">'.cplang('moderate_all_cancel').'</a><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').' &nbsp;<input class="checkbox" type="checkbox" name="sendemail" id="sendemail" value="1" '.$checksendemail.' /><label for="sendemail"> '.cplang('moderate_members_email').'</label>', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

		$uids = array();
		$uidsql = '';
		if(!$_GET['apply_all']) {
			if(is_array($_GET['modtype'])) {
				foreach($_GET['modtype'] as $uid => $act) {
					$uid = intval($uid);
					$uids[$uid] = $uid;
					$moderation[$act][$uid] = $uid;
				}
				$uidsql = 'v.uid IN ('.dimplode($uids).') AND';
			}
		}

		$members = array();

		$allmembervalidate = $uids ? C::t('common_member_validate')->fetch_all($uids) : C::t('common_member_validate')->range();
		foreach(C::t('common_member')->fetch_all(array_keys($allmembervalidate), false, 0) as $uid => $member) {
			if($member['groupid'] == 8 || $member['freeze'] == 2 || $member['freeze'] == -1) {
				$members[$uid] = $member;
			}
		}
		$alluids = array_keys($members);
		if($_GET['apply_all']) {
			$moderation[$_GET['apply_all']] = array_merge($alluids, $moderation[$_GET['apply_all']]);
		}
		if(!empty($members)) {
			$numdeleted = $numinvalidated = $numvalidated = 0;

			if(!empty($moderation['delete']) && is_array($moderation['delete'])) {
				$deluids = array_intersect($moderation['delete'], $alluids);
				$numdeleted = count($deluids);

				C::t('common_member')->delete_no_validate($deluids);

				loaducenter();
				uc_user_delete($deluids);
			} else {
				$moderation['delete'] = array();
			}

			if(!empty($moderation['validate']) && is_array($moderation['validate'])) {

				$validateuids = array_intersect($moderation['validate'], $alluids);
				C::t('common_member')->update($validateuids, array('adminid' => 0, 'groupid' => $_G['setting']['newusergroupid'], 'freeze' => 0));
				$numvalidated = count($validateuids);
				C::t('common_member_validate')->delete($validateuids);
			} else {
				$moderation['validate'] = array();
			}

			if(!empty($moderation['invalidate']) && is_array($moderation['invalidate'])) {
				$invalidateuids = array_intersect($moderation['invalidate'], $alluids);
				$numinvalidated = count($invalidateuids);
				foreach($invalidateuids as $uid) {
					C::t('common_member_validate')->update($uid, array('moddate' => $_G['timestamp'], 'admin' => $_G['username'], 'status' => '1', 'remark' => dhtmlspecialchars($_GET['remark'][$uid])));
				}
			} else {
				$moderation['invalidate'] = array();
			}

			foreach(array('validate', 'invalidate') as $o) {
				foreach($moderation[$o] as $uid) {
					if($_GET['remark'][$uid]) {
						switch($o) {
							case 'validate':
								notification_add($uid, 'mod_member', 'member_moderate_validate', array('remark' => $_GET['remark'][$uid]));
								break;
							case 'invalidate':
								notification_add($uid, 'mod_member', 'member_moderate_invalidate', array('remark' => $_GET['remark'][$uid]));
								break;
						}
					} else {
						switch($o) {
							case 'validate':
								notification_add($uid, 'mod_member', 'member_moderate_validate_no_remark');
								break;
							case 'invalidate':
								notification_add($uid, 'mod_member', 'member_moderate_invalidate_no_remark');
								break;
						}
					}
				}
			}

			if($_GET['sendemail']) {
				if(!function_exists('sendmail')) {
					include libfile('function/mail');
				}
				foreach(array('delete', 'validate', 'invalidate') as $o) {
					foreach($moderation[$o] as $uid) {
						if(isset($members[$uid])) {
							$member = $members[$uid];
							$member['regdate'] = dgmdate($member['regdate']);
							$member['submitdate'] = dgmdate($allmembervalidate[$uid]['submitdate']);
							$member['moddate'] = dgmdate(TIMESTAMP);
							$member['operation'] = $o;
							$member['remark'] = $_GET['remark'][$uid] ? dhtmlspecialchars($_GET['remark'][$uid]) : $lang['none'];
							$moderate_member_message = lang('email', 'moderate_member_message', array(
								'username' => $member['username'],
								'bbname' => $_G['setting']['bbname'],
								'regdate' => $member['regdate'],
								'submitdate' => $member['submitdate'],
								'submittimes' => $allmembervalidate[$uid]['submittimes'],
								'message' => $allmembervalidate[$uid]['message'],
								'modresult' => lang('email', 'moderate_member_'.$member['operation']),
								'moddate' => $member['moddate'],
								'adminusername' => $_G['member']['username'],
								'remark' => $member['remark'],
								'siteurl' => $_G['siteurl'],
							));

							if(!sendmail("$member[username] <$member[email]>", lang('email', 'moderate_member_subject'), $moderate_member_message)) {
								runlog('sendmail', "$member[email] sendmail failed.");
							}
						}
					}
				}
			}
		}
		cpmsg('moderate_members_op_succeed', "action=moderate&operation=members&page=$page", 'succeed', array('numvalidated' => $numvalidated, 'numinvalidated' => $numinvalidated, 'numdeleted' => $numdeleted));

	}

} elseif($do == 'del') {

	if(!submitcheck('prunesubmit', 1)) {

		shownav('user', 'nav_modmembers');
		showsubmenu('nav_moderate_users', array(
			array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 0),
			array('clean', 'moderate&operation=members&do=del', 1)
		));
		showtips('moderate_members_tips');
		showformheader('moderate&operation=members&do=del');
		showtableheader('moderate_members_prune');
		showsetting('moderate_members_prune_submitmore', 'submitmore', '5', 'text');
		showsetting('moderate_members_prune_regbefore', 'regbefore', '30', 'text');
		showsetting('moderate_members_prune_modbefore', 'modbefore', '15', 'text');
		showsetting('moderate_members_prune_regip', 'regip', '', 'text');
		showsubmit('prunesubmit');
		showtablefooter();
		showformfooter();

	} else {

		$uids = C::t('common_member_validate')->fetch_all_validate_uid($_GET['submitmore'], $_GET['regbefore'], $_GET['modbefore'], $_GET['regip']);
		if((!$membernum = count($uids))) {
			cpmsg('members_search_noresults', '', 'error');
		} elseif(!$_GET['confirmed']) {
			cpmsg('members_delete_confirm', "action=moderate&operation=members&do=del&submitmore=".rawurlencode($_GET['submitmore'])."&regbefore=".rawurlencode($_GET['regbefore'])."&modbefore=".rawurlencode($_GET['modbefore'])."&regip=".rawurlencode($_GET['regip'])."&prunesubmit=yes", 'form', array('membernum' => $membernum));
		} else {
			$numdeleted = C::t('common_member')->delete_no_validate(array_keys($uids));

			cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));
		}

	}

}

?>