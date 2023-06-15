<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_forums.php 36345 2017-01-12 01:55:04Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$operation = empty($operation) ? 'admin' : $operation;
$fid = intval(getgpc('fid'));

if($operation == 'admin') {

	if(!submitcheck('editsubmit')) {
		shownav('forum', 'forums_admin');
		showsubmenu('forums_admin');
		showtips('forums_admin_tips');

		require_once libfile('function/forumlist');
		$forums = str_replace("'", "\'", forumselect(false, 0, 0, 1));

?>
<script type="text/JavaScript">
var forumselect = '<?php echo $forums;?>';
var rowtypedata = [
	[[1, ''], [1,'<input type="text" class="txt" name="newcatorder[]" value="0" />', 'td25'], [5, '<div><input name="newcat[]" value="<?php cplang('forums_admin_add_category_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a></div>']],
	[[1, ''], [1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [5, '<div class="board"><input name="newforum[{1}][]" value="<?php cplang('forums_admin_add_forum_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a><select name="newinherited[{1}][]"><option value=""><?php cplang('forums_edit_newinherited', null, true);?></option>' + forumselect + '</select></div>']],
	[[1, ''], [1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [5, '<div class="childboard"><input name="newforum[{1}][]" value="<?php cplang('forums_admin_add_forum_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a>&nbsp;<label><input name="inherited[{1}][]" type="checkbox" class="checkbox" value="1">&nbsp;<?php cplang('forums_edit_inherited', null, true);?></label></div>']],
];
</script>
<?php
		showformheader('forums');
		echo '<div style="height:30px;line-height:30px;"><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a> <input type="text" id="srchforumipt" class="txt" /> <input type="submit" class="btn" value="'.cplang('search').'" onclick="return srchforum()" /></div>';
		showtableheader('');
		showsubtitle(array('', 'display_order', 'forums_admin_name', '', 'forums_moderators', '<a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));

		$forumcount = C::t('forum_forum')->fetch_forum_num();

		$query = C::t('forum_forum')->fetch_all_forum_for_sub_order();
		$groups = $forums = $subs = $fids = $showed = array();
		foreach($query as $forum) {
			if($forum['type'] == 'group') {
				$groups[$forum['fid']] = $forum;
			} elseif($forum['type'] == 'sub') {
				$subs[$forum['fup']][] = $forum;
			} else {
				$forums[$forum['fup']][] = $forum;
			}
			$fids[] = $forum['fid'];
		}

		foreach ($groups as $id => $gforum) {
			$toggle = $forumcount > 50 && count($forums[$id]) > 2;
			$showed[] = showforum($gforum, 'group', '', $toggle);
			if(!empty($forums[$id])) {
				foreach ($forums[$id] as $forum) {
					$showed[] = showforum($forum);
					$lastfid = 0;
					if(!empty($subs[$forum['fid']])) {
						foreach ($subs[$forum['fid']] as $sub) {
							$showed[] = showforum($sub, 'sub');
							$lastfid = $sub['fid'];
						}
					}
					showforum($forum, $lastfid, 'lastchildboard');
				}
			}
			showforum($gforum, '', 'lastboard');
		}

		if(count($fids) != count($showed)) {
			foreach($fids as $fid) {
				if(!in_array($fid, $showed)) {
					C::t('forum_forum')->update($fid, array('fup' => '0', 'type' => 'forum'));
				}
			}
		}

		showforum($gforum, '', 'last');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$usergroups = array();
		$query = C::t('common_usergroup')->range();
		foreach($query as $group) {
			$usergroups[$group['groupid']] = $group;
		}

		if(is_array($_GET['order'])) {
			foreach($_GET['order'] as $fid => $value) {
				C::t('forum_forum')->update($fid, array('name' => $_GET['name'][$fid], 'displayorder' => $_GET['order'][$fid]));
			}
		}

		if(is_array($_GET['newcat'])) {
			foreach($_GET['newcat'] as $key => $forumname) {
				if(empty($forumname)) {
					continue;
				}
				$fid = C::t('forum_forum')->insert(array('type' => 'group', 'name' => $forumname, 'status' => 1, 'displayorder' => $_GET['newcatorder'][$key]), 1);
				C::t('forum_forumfield')->insert(array('fid' => $fid));
			}
		}

		$table_forum_columns = array('fup', 'type', 'name', 'status', 'displayorder', 'styleid', 'allowsmilies',
			'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowpostspecial', 'alloweditrules',
			'alloweditpost', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'disablethumb',
			'autoclose', 'simple', 'allowside', 'allowfeed');
		$table_forumfield_columns = array('fid', 'attachextensions', 'threadtypes', 'viewperm', 'postperm', 'replyperm',
			'getattachperm', 'postattachperm', 'postimageperm');

		if(is_array($_GET['newforum'])) {

			foreach($_GET['newforum'] as $fup => $forums) {

				$fupforum = C::t('forum_forum')->get_forum_by_fid($fup);
				if(empty($fupforum)) continue;

				if($fupforum['fup']) {
					$groupforum = C::t('forum_forum')->get_forum_by_fid($fupforum['fup']);
				} else {
					$groupforum = $fupforum;
				}

				foreach($forums as $key => $forumname) {

					if(empty($forumname) || strlen($forumname) > 50) continue;

					$forum = $forumfields = array();
					$inheritedid = !empty($_GET['inherited'][$fup]) ? $fup : (!empty($_GET['newinherited'][$fup][$key]) ? $_GET['newinherited'][$fup][$key] : '');

					if(!empty($inheritedid)) {

						$forum = C::t('forum_forum')->get_forum_by_fid($inheritedid);
						$forumfield =  C::t('forum_forum')->get_forum_by_fid($inheritedid, null, 'forumfield');

						foreach($table_forum_columns as $field) {
							$forumfields[$field] = $forum[$field];
						}

						foreach($table_forumfield_columns as $field) {
							$forumfields[$field] = $forumfield[$field];
						}

					} else {
						$forumfields['allowsmilies'] = $forumfields['allowbbcode'] = $forumfields['allowimgcode'] = 1;
						$forumfields['allowpostspecial'] = 1;
						$forumfields['allowside'] = 0;
						$forumfields['allowfeed'] = 0;
						$forumfields['recyclebin'] = 1;
					}

					$forumfields['fup'] = $fup ? $fup : 0;
					$forumfields['type'] = $fupforum['type'] == 'forum' ? 'sub' : 'forum';
					$forumfields['styleid'] = $groupforum['styleid'];
					$forumfields['name'] = $forumname;
					$forumfields['status'] = 1;
					$forumfields['displayorder'] = $_GET['neworder'][$fup][$key];

					$data = array();
					foreach($table_forum_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					$forumfields['fid'] = $fid = C::t('forum_forum')->insert($data, 1);

					$data = array();
					$forumfields['threadtypes'] = copy_threadclasses($forumfields['threadtypes'], $fid);
					foreach($table_forumfield_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					C::t('forum_forumfield')->insert($data);

					foreach(C::t('forum_moderator')->fetch_all_by_fid($fup, false) as $mod) {
						if($mod['inherited'] || $fupforum['inheritedmod']) {
							C::t('forum_moderator')->insert(array('uid' => $mod['uid'], 'fid' => $fid, 'inherited' => 1), false, true);
						}
					}
				}
			}
		}


		updatecache('forums');

		cpmsg('forums_update_succeed', 'action=forums', 'succeed');
	}

} elseif($operation == 'moderators' && $fid) {

	if(!submitcheck('modsubmit')) {

		$forum = C::t('forum_forum')->fetch($fid);
		shownav('forum', 'forums_moderators_edit');
		showsubmenu(cplang('forums_moderators_edit').' - '.$forum['name']);
		showtips('forums_moderators_tips');
		showformheader("forums&operation=moderators&fid=$fid&");
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'username', 'usergroups', 'forums_moderators_inherited'));

		$modgroups = C::t('common_admingroup')->fetch_all_merge_usergroup(array_keys(C::t('common_usergroup')->fetch_all_by_radminid(0)));
		$groupselect = '<select name="newgroup">';
		foreach($modgroups as $modgroup) {
			if($modgroup['radminid'] == 3) {
				$groupselect .= '<option value="'.$modgroup['admingid'].'">'.$modgroup['grouptitle'].'</option>';
			}
			$modgroups[$modgroup['admingid']] = $modgroup['grouptitle'];
		}
		$groupselect .= '</select>';

		$moderators = C::t('forum_moderator')->fetch_all_by_fid($fid);
		$uids = array_keys($moderators);
		if($uids) {
			$users = C::t('common_member')->fetch_all($uids);
		}

		foreach($moderators as $mod) {
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$mod[uid].'"'.($mod['inherited'] ? ' disabled' : '').' />',
				'<input type="text" class="txt" name="displayordernew['.$mod[uid].']" value="'.$mod[displayorder].'" size="2" />',
				"<a href=\"".ADMINSCRIPT."?mod=forum&action=members&operation=group&uid=$mod[uid]\" target=\"_blank\">{$users[$mod['uid']]['username']}</a>",
				$modgroups[$users[$mod['uid']]['groupid']],
				cplang($mod['inherited'] ? 'yes' : 'no'),
			));
		}

		if($forum['type'] == 'group' || $forum['type'] == 'sub') {
			$checked = $forum['type'] == 'group' ? 'checked' : '';
			$disabled = 'disabled';
		} else {
			$checked = $forum['inheritedmod'] ? 'checked' : '';
			$disabled = '';
		}

		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newdisplayorder" value="0" size="2" />',
			'<input type="text" class="txt" name="newmoderator" value="" size="20" />',
			$groupselect,
			''
		));

		showsubmit('modsubmit', 'submit', 'del', '<input class="checkbox" type="checkbox" name="inheritedmodnew" value="1" '.$checked.' '.$disabled.' id="inheritedmodnew" /><label for="inheritedmodnew">'.cplang('forums_moderators_inherit').'</label>');
		showtablefooter();
		showformfooter();

	} else {
		$forum = C::t('forum_forum')->fetch($fid);
		$inheritedmodnew = $_GET['inheritedmodnew'];
		if($forum['type'] == 'group') {
			$inheritedmodnew = 1;
		} elseif($forum['type'] == 'sub') {
			$inheritedmodnew = 0;
		}

		if(!empty($_GET['delete']) || $_GET['newmoderator'] || (bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {

			$fidarray = $newmodarray = $origmodarray = array();

			if($forum['type'] == 'group') {
				$query = C::t('forum_forum')->fetch_all_fids(1, 'forum', $fid);
				foreach($query as $sub) {
					$fidarray[] = $sub['fid'];
				}
				$query = C::t('forum_forum')->fetch_all_fids(1, 'sub', $fidarray);
				foreach($query as $sub) {
					$fidarray[] = $sub['fid'];
				}
			} elseif($forum['type'] == 'forum') {
				$query = C::t('forum_forum')->fetch_all_fids(1, 'sub', $fid);
				foreach($query as $sub) {
					$fidarray[] = $sub['fid'];
				}
			}

			if(is_array($_GET['delete'])) {
				foreach($_GET['delete'] as $uid) {
					C::t('forum_moderator')->delete_by_uid_fid_inherited($uid, $fid, $fidarray);
				}

				$excludeuids = array();
				$deleteuids = '\''.implode('\',\'', $_GET['delete']).'\'';
				foreach(C::t('forum_moderator')->fetch_all_by_uid($_GET['delete']) as $mod) {
					$excludeuids[] = $mod['uid'];
				}

				$usergroups = array();
				$query = C::t('common_usergroup')->range();
				foreach($query as $group) {
					$usergroups[$group['groupid']] = $group;
				}

				$members = C::t('common_member')->fetch_all($_GET['delete'], false, 0);
				foreach($members as $uid => $member) {
					if(!in_array($uid, $excludeuids) && !in_array($member['adminid'], array(1,2))) {
						if($usergroups[$member['groupid']]['type'] == 'special' && $usergroups[$member['groupid']]['radminid'] != 3) {
							$adminidnew = -1;
							$groupidnew = $member['groupid'];
						} else {
							$adminidnew = 0;
							foreach($usergroups as $group) {
								if($group['type'] == 'member' && $member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) {
									$groupidnew = $group['groupid'];
									break;
								}
							}
						}
						C::t('common_member')->update($member['uid'], array('adminid'=>$adminidnew, 'groupid'=>$groupidnew));
					}
				}
			}

			if($_GET['newmoderator']) {
				$member = C::t('common_member')->fetch_by_username($_GET['newmoderator']);
				if(!$member) {
					cpmsg_error('members_edit_nonexistence');
				} else {
					$newmodarray[] = $member['uid'];
					$membersetarr = array();
					if(!in_array($member['adminid'],array(1,2,3,4,5,6,7,8,-1))) {
						$membersetarr['groupid'] = $_GET['newgroup'];
					}
					if(!in_array($member['adminid'],array(1,2))) {
						$membersetarr['adminid'] = '3';
					}
					if(!empty($membersetarr)) {
						C::t('common_member')->update($member['uid'], $membersetarr);
					}

					C::t('forum_moderator')->insert(array(
						'uid' => $member['uid'],
						'fid' => $fid,
						'displayorder' => $_GET['newdisplayorder'],
						'inherited' => '0',
					), false, true);
				}
			}

			if((bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {
				foreach(C::t('forum_moderator')->fetch_all_by_fid_inherited($fid) as $mod) {
					$origmodarray[] = $mod['uid'];
					if(!$forum['inheritedmod'] && $inheritedmodnew) {
						$newmodarray[] = $mod['uid'];
					}
				}
				if($forum['inheritedmod'] && !$inheritedmodnew) {
					C::t('forum_moderator')->delete_by_uid_fid($origmodarray, $fidarray);
				}
			}

			foreach($newmodarray as $uid) {
				C::t('forum_moderator')->insert(array(
					'uid' => $uid,
					'fid' => $fid,
					'displayorder' => $_GET['newdisplayorder'],
					'inherited' => '0',
				), false, true);

				if($inheritedmodnew) {
					foreach($fidarray as $ifid) {
						C::t('forum_moderator')->insert(array(
							'uid' => $uid,
							'fid' => $ifid,
							'inherited' => '1',
						), false, true);
					}
				}
			}

			if($forum['type'] == 'group') {
				$inheritedmodnew = 1;
			} elseif($forum['type'] == 'sub') {
				$inheritedmodnew = 0;
			}
			C::t('forum_forum')->update($fid, array('inheritedmod' => $inheritedmodnew));
		}

		if(is_array($_GET['displayordernew'])) {
			foreach($_GET['displayordernew'] as $uid => $order) {
				C::t('forum_moderator')->update_by_fid_uid($fid, $uid, array(
					'displayorder' => $order,
				));
			}
		}

		$fidarray[] = $fid;
		foreach($fidarray as $fid) {
			$moderators = $tab = '';
			$modorder = array();
			$modmemberarray = C::t('forum_moderator')->fetch_all_no_inherited_by_fid($fid);
			foreach($modmemberarray as $moduid => $modmember) {
				$modorder[] = $moduid;
			}
			$members = C::t('common_member')->fetch_all_username_by_uid($modorder);
			foreach($modorder as $mod) {
				if(!$members[$mod]) {
					continue;
				}
				$moderators .= $tab.addslashes($members[$mod]);
				$tab = "\t";
			}

			C::t('forum_forumfield')->update($fid, array('moderators' => $moderators));
		}
		cpmsg('forums_moderators_update_succeed', "mod=forum&action=forums&operation=moderators&fid=$fid", 'succeed');

	}

} elseif($operation == 'merge') {
	$source = $_GET['source'];
	$target = $_GET['target'];
	if(!submitcheck('mergesubmit') || $source == $target) {

		require_once libfile('function/forumlist');
		loadcache('forums');
		$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select')."</option><option value=\"\">&nbsp;</option>".str_replace('%', '%%', forumselect(FALSE, 0, 0, TRUE)).'</select>';
		shownav('forum', 'forums_merge');
		showsubmenu('forums_merge');
		showformheader('forums&operation=merge');
		showtableheader();
		showsetting('forums_merge_source', '', '', sprintf($forumselect, 'source'));
		showsetting('forums_merge_target', '', '', sprintf($forumselect, 'target'));
		showsubmit('mergesubmit');
		showtablefooter();
		showformfooter();

	} else {
		if(C::t('forum_forum')->check_forum_exists(array($source,$target)) != 2) {
			cpmsg_error('forums_nonexistence');
		}
		if(C::t('forum_forum')->fetch_forum_num('', $source)) {
			cpmsg_error('forums_merge_source_sub_notnull');
		}

		C::t('forum_thread')->update_by_fid($source, array('fid' => $target));
		loadcache('posttableids');
		$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
		foreach($posttableids as $id) {
			C::t('forum_post')->update_fid_by_fid($id, $source, $target);
		}

		$sourceforum = C::t('forum_forum')->fetch_info_by_fid($source);
		$targetforum = C::t('forum_forum')->fetch_info_by_fid($target);
		$sourcethreadtypes = (array)dunserialize($sourceforum['threadtypes']);
		$targethreadtypes = (array)dunserialize($targetforum['threadtypes']);
		$targethreadtypes['types'] = array_merge((array)$targethreadtypes['types'], (array)$sourcethreadtypes['types']);
		$targethreadtypes['icons'] = array_merge((array)$targethreadtypes['icons'], (array)$sourcethreadtypes['icons']);
		C::t('forum_forum')->update($target, array('threads' => $targetforum['threads'] + $sourceforum['threads'], 'posts' => $targetforum['posts'] + $sourceforum['posts']));
		C::t('forum_forumfield')->update($target, array('threadtypes' => serialize($targethreadtypes)));
		C::t('forum_threadclass')->update_by_fid($source, array('fid' => $target));
		C::t('forum_forum')->delete_by_fid($source);
		C::t('home_favorite')->delete_by_id_idtype($source, 'fid');
		C::t('forum_moderator')->delete_by_fid($source);
		C::t('common_member_forum_buylog')->delete_by_fid($target);

		$query = C::t('forum_access')->fetch_all_by_fid_uid($source);
		foreach($query as $access) {
			C::t('forum_access')->insert(array('uid' => $access['uid'], 'fid' => $target, 'allowview' => $access['allowview'], 'allowpost' => $access['allowpost'], 'allowreply' => $access['allowreply'], 'allowgetattach' => $access['allowgetattach']), false, true);
		}
		C::t('forum_access')->delete_by_fid($source);
		C::t('forum_thread')->clear_cache(array($source,$target), 'forumdisplay_');
		updatecache('forums');

		cpmsg('forums_merge_succeed', 'action=forums', 'succeed');
	}

} elseif($operation == 'edit') {

	require_once libfile('function/forumlist');
	require_once libfile('function/domain');
	$highlight = getgpc('highlight');
	$highlight = !empty($highlight) ? dhtmlspecialchars($highlight, ENT_QUOTES) : '';
	$anchor = getgpc('anchor');

	list($pluginsetting, $pluginvalue) = get_pluginsetting('forums');

	$multiset = 0;
	if(empty($_GET['multi'])) {
		$fids = $fid;
	} else {
		$multiset = 1;
		if(is_array($_GET['multi'])) {
			$fids = $_GET['multi'];
		} else {
			$_GET['multi'] = explode(',', $_GET['multi']);
			$fids = &$_GET['multi'];
		}
	}
	if(count($_GET['multi']) == 1) {
		$fids = $_GET['multi'][0];
		$multiset = 0;
	}
	if(empty($fids)) {
		cpmsg('forums_edit_nonexistence', 'action=forums&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : '').(!empty($anchor) ? "&anchor=$anchor" : ''), 'form', array(), '<select name="fid">'.forumselect(FALSE, 0, 0, TRUE).'</select>');
	}
	$mforum = array();
	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm', 'postimageperm');

	$query = C::t('forum_forum')->fetch_all_info_by_fids($fids);
	if(empty($query)) {
		cpmsg('forums_nonexistence', '', 'error');
	} else {
		foreach($query as $forum) {
			if(isset($pluginvalue[$forum['fid']])) {
				$forum['plugin'] = $pluginvalue[$forum['fid']];
			}
			$mforum[] = $forum;
		}
	}

	$dactionarray = array();
	$allowthreadtypes = !in_array('threadtypes', $dactionarray);


	$forumkeys = C::t('common_setting')->fetch('forumkeys', true);

	$rules = array();
	foreach(C::t('common_credit_rule')->fetch_all_by_action(array('reply', 'post', 'digest', 'postattach', 'getattach')) as $value) {
		$rules[$value['rid']] = $value;
	}
	$navs = array();
	foreach(C::t('common_nav')->fetch_all_by_navtype_type(0, 5) as $nav) {
		$navs[$nav['identifier']] = $nav['id'];
	}

	if(!submitcheck('detailsubmit')) {
		$anchor = in_array($_GET['anchor'], array('basic', 'extend', 'posts', 'attachtype', 'credits', 'threadtypes', 'threadsorts', 'perm', 'plugin')) ? $_GET['anchor'] : 'basic';
		shownav('forum', 'forums_edit');

		loadcache('forums');
		$forumselect = '';
		$sgid = 0;
		foreach($_G['cache']['forums'] as $forums) {
			$checked = $fid == $forums['fid'] || in_array($forums['fid'], $_GET['multi']);
			if($forums['type'] == 'group') {
				$sgid = $forums['fid'];
				$forumselect .= '</div><em class="cl">'.
					'<span class="right"><input name="checkall_'.$forums['fid'].'" onclick="checkAll(\'value\', this.form, '.$forums['fid'].', \'checkall_'.$forums['fid'].'\')" type="checkbox" class="vmiddle checkbox" /></span>'.
					'<span class="pointer" onclick="sdisplay(\'g_'.$forums['fid'].'\', this)"><img src="static/image/admincp/desc.gif" class="vmiddle" /></span> <span class="pointer" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].'\'">'.$forums['name'].'</span></em><div id="g_'.$forums['fid'].'" style="display:">';
			} elseif($forums['type'] == 'forum') {
				$forumselect .= '<input class="left checkbox ck" chkvalue="'.$sgid.'" name="multi[]" value="'.$forums['fid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/><a class="f'.($checked ? ' current"' : '').'" href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].($mforum[0]['type'] != 'group' ? '&anchor=\'+currentAnchor' : '\'').'+\'&scrolltop=\'+scrollTopBody()">'.$forums['name'].'</a>';
			} elseif($forums['type'] == 'sub') {
				$forumselect .= '<input class="left checkbox ck" chkvalue="'.$sgid.'" name="multi[]" value="'.$forums['fid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/><a class="s'.($checked ? ' current"' : '').'" href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].($mforum[0]['type'] != 'group' ? '&anchor=\'+currentAnchor' : '\'').'+\'&scrolltop=\'+scrollTopBody()">'.$forums['name'].'</a>';
			}
		}
		$forumselect = '<span id="fselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'fselect_menu\').style.top=(parseInt($(\'fselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'fselect_menu\').style.left=(parseInt($(\'fselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.cplang('forums_edit_switch').'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="fselect_menu" class="popupmenu_popup" style="display:none"><div class="fsel"><div>'.$forumselect.'</div></div><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('forums_multiedit').'" /></div></div>';

		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'forums', 'operation' => 'edit'));
		if(count($mforum) == 1 && $mforum[0]['type'] == 'group') {
			showsubmenu(cplang('forums_cat_detail').(count($mforum) == 1 ? ' - '.$mforum[0]['name'].'(gid:'.$mforum[0]['fid'].')' : ''), array(), $forumselect);
		} else {
			if($multiset && !in_array($anchor, array('basic', 'extend', 'posts', 'perm', 'plugin'))) {
				$anchor = 'basic';
			}
			showsubmenuanchors(cplang('forums_edit').(count($mforum) == 1 ? ' - '.$mforum[0]['name'].'(fid:'.$mforum[0]['fid'].')' : ''), array(
				array('forums_edit_basic', 'basic', $anchor == 'basic'),
				array('forums_edit_extend', 'extend', $anchor == 'extend'),
				array('forums_edit_posts', 'posts', $anchor == 'posts'),
				array('forums_edit_perm', 'perm', $anchor == 'perm'),
				!$multiset ? array('forums_edit_credits', 'credits', $anchor == 'credits') : array(),
				!$multiset ? array(array('menu' => 'usergroups_edit_other', 'submenu' => array(
					array('forums_edit_threadtypes', 'threadtypes', $anchor == 'threadtypes'),
					array('forums_edit_threadsorts', 'threadsorts', $anchor == 'threadsorts'),
					!$multiset ? array('forums_edit_attachtype', 'attachtype', $anchor == 'attachtype') : array(),
					!$pluginsetting ? array() : array('forums_edit_plugin', 'plugin', $anchor == 'plugin'),
				))) : array(),
				$multiset && $pluginsetting ? array('forums_edit_plugin', 'plugin', $anchor == 'plugin') : array(),
			), $forumselect);
		}
		showformfooter();

		$groups = array();
		$query = C::t('common_usergroup')->range_orderby_credit();
		foreach($query as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groups[$group['type']][] = $group;
		}

		$styleselect = "<select name=\"styleidnew\"><option value=\"0\">$lang[use_default]</option>";
		foreach(C::t('common_style')->fetch_all_data(false, false) as $style) {
			$styleselect .= "<option value=\"$style[styleid]\" ".
				($style['styleid'] == $mforum[0]['styleid'] ? 'selected="selected"' : NULL).
				">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		if(!$multiset) {
			$attachtypes = '';
			foreach(C::t('forum_attachtype')->fetch_all_by_fid($fid) as $type) {
				$type['maxsize'] = round($type['maxsize'] / 1024);
				$attachtypes .= showtablerow('', array('class="td25"', 'class="td24"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\" />",
					"<input type=\"text\" class=\"txt\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\" />",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\" />"
				), TRUE);
			}
		} else {
			showtips('setting_multi_tips');
		}
		showformheader("forums&operation=edit&fid=$fid&", 'enctype');
		showhiddenfields(array('type' => $mforum[0]['type']));

		if(count($mforum) == 1 && $mforum[0]['type'] == 'group') {
			$mforum[0]['extra'] = dunserialize($mforum[0]['extra']);
			showtableheader();
			showsetting('forums_edit_basic_cat_name', 'namenew', $mforum[0]['name'], 'text');
			showsetting('forums_edit_basic_cat_name_color', 'extranew[namecolor]', $mforum[0]['extra']['namecolor'], 'color');
			showsetting('forums_edit_basic_cat_style', '', '', $styleselect);
			showsetting('forums_edit_extend_forum_horizontal', 'forumcolumnsnew', $mforum[0]['forumcolumns'], 'text');
			showsetting('forums_edit_extend_cat_sub_horizontal', 'catforumcolumnsnew', $mforum[0]['catforumcolumns'], 'text');
			if(!empty($_G['setting']['domain']['root']['forum'])) {
				showsetting('forums_edit_extend_domain', '', '', $_G['scheme'].'://<input type="text" name="domainnew" class="txt" value="'.$mforum[0]['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['forum']);
			} else {
				showsetting('forums_edit_extend_domain', 'domainnew', '', 'text', 'disabled');
			}
			showsetting('forums_cat_display', 'statusnew', $mforum[0]['status'], 'radio');
			showsetting('forums_edit_basic_shownav', 'shownavnew', array_key_exists($mforum[0]['fid'], $navs) ? 1 : 0, 'radio');
			showtablefooter();
			showtips('setting_seo_forum_tips', 'seo_tips', true, 'setseotips');
			showtableheader();
			showsetting('forums_edit_basic_seotitle', 'seotitlenew', dhtmlspecialchars($mforum[0]['seotitle']), 'text');
			showsetting('forums_edit_basic_keyword', 'keywordsnew', dhtmlspecialchars($mforum[0]['keywords']), 'text');
			showsetting('forums_edit_basic_seodescription', 'seodescriptionnew', dhtmlspecialchars($mforum[0]['seodescription']), 'textarea');
			showsubmit('detailsubmit');
			showtablefooter();

		} else {

			require_once libfile('function/editor');

			if($multiset) {
				$_G['showsetting_multi'] = 0;
				$_G['showsetting_multicount'] = count($mforum);
				foreach($mforum as $forum) {
					$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$forum['name'].'(fid:'.$forum['fid'].')</a>';
				}
			}
			$mfids = array();
			foreach($mforum as $forum) {
				$fid = $forum['fid'];
				$mfids[] = $fid;
				if(!$multiset) {
					$fupselect = "<select name=\"fupnew\">\n";
					$query = C::t('forum_forum')->fetch_all_info_by_ignore_fid($fid);
					foreach($query as $fup) {
						$fups[] = $fup;
					}
					if(is_array($fups)) {
						foreach($fups as $forum1) {
							if($forum1['type'] == 'group') {
								$selected = $forum1['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
								$fupselect .= "<option value=\"$forum1[fid]\" $selected>$forum1[name]</option>\n";
								foreach($fups as $forum2) {
									if($forum2['type'] == 'forum' && $forum2['fup'] == $forum1['fid']) {
										$selected = $forum2['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
										$fupselect .= "<option value=\"$forum2[fid]\" $selected>&nbsp; &gt; $forum2[name]</option>\n";
									}
								}
							}
						}
						foreach($fups as $forum0) {
							if($forum0['type'] == 'forum' && $forum0['fup'] == 0) {
								$selected = $forum0['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
								$fupselect .= "<option value=\"$forum0[fid]\" $selected>$forum0[name]</option>\n";
							}
						}
					}
					$fupselect .= '</select>';

					if($forum['threadtypes']) {
						$forum['threadtypes'] = dunserialize($forum['threadtypes']);
						$forum['threadtypes']['status'] = 1;
					} else {
						$forum['threadtypes'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
					}

					if($forum['threadsorts']) {
						$forum['threadsorts'] = dunserialize($forum['threadsorts']);
						$forum['threadsorts']['status'] = 1;
					} else {
						$forum['threadsorts'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
					}

					$typeselect = $sortselect = '';

					$query = C::t('forum_threadtype')->fetch_all_for_order();
					$typeselect = getthreadclasses_html($fid);
					foreach($query as $type) {
						$typeselected = array();
						$enablechecked = '';

						$keysort = $type['special'] ? 'threadsorts' : 'threadtypes';
						if(isset($forum[$keysort]['types'][$type['typeid']])) {
							$enablechecked = ' checked="checked"';
						}

						$showtype = TRUE;

						loadcache('threadsort_option_'.$type['typeid']);
						if($type['special'] && !$_G['cache']['threadsort_option_'.$type['typeid']]) {
							$showtype = FALSE;
						}
						if($type['special']) {
							$typeselected[3] = $forum['threadsorts']['show'][$type['typeid']] ? ' checked="checked"' : '';
							$sortselect .= $showtype ? showtablerow('', array('class="td25"'), array(
								'<input type="checkbox" name="threadsortsnew[options][enable]['.$type['typeid'].']" value="1" class="checkbox"'.$enablechecked.' />',
								$type['name'],
								$type['description'],
								"<input class=\"checkbox\" type=\"checkbox\" name=\"threadsortsnew[options][show][{$type[typeid]}]\" value=\"3\" $typeselected[3] />",
								"<input class=\"radio\" type=\"radio\" name=\"threadsortsnew[defaultshow]\" value=\"$type[typeid]\" ".($forum['threadsorts']['defaultshow'] == $type['typeid'] ? 'checked' : '')." />"
							), TRUE) : '';
						}
					}
					$forum['creditspolicy'] = $forum['creditspolicy'] ? dunserialize($forum['creditspolicy']) : array();
				}

				if($forum['autoclose']) {
					$forum['autoclosetime'] = abs($forum['autoclose']);
					$forum['autoclose'] = $forum['autoclose'] / abs($forum['autoclose']);
				}

				if($forum['threadplugin']) {
					$forum['threadplugin'] = dunserialize($forum['threadplugin']);
				}

				$simplebin = sprintf('%08b', $forum['simple']);
				$forum['defaultorderfield'] = bindec(substr($simplebin, 0, 2));
				$forum['defaultorder'] = ($forum['simple'] & 32) ? 1 : 0;
				$forum['subforumsindex'] = bindec(substr($simplebin, 3, 2));
				$forum['subforumsindex'] = $forum['subforumsindex'] == 0 ? -1 : ($forum['subforumsindex'] == 2 ? 0 : 1);
				$forum['simple'] = $forum['simple'] & 1;
				$forum['modrecommend'] = $forum['modrecommend'] ? dunserialize($forum['modrecommend']) : '';
				$forum['formulaperm'] = dunserialize($forum['formulaperm']);
				$forum['medal'] = $forum['formulaperm']['medal'];
				$forum['formulapermmessage'] = $forum['formulaperm']['message'];
				$forum['formulapermusers'] = $forum['formulaperm']['users'];
				$forum['formulaperm'] = $forum['formulaperm'][0];
				$forum['extra'] = dunserialize($forum['extra']);
				$forum['threadsorts']['default'] = $forum['threadsorts']['defaultshow'] ? 1 : 0;

				$_G['multisetting'] = $multiset ? 1 : 0;
				showmultititle();
				showtagheader('div', 'basic', $anchor == 'basic');
				if(!$multiset) {
					showtips('forums_edit_tips');
				}
				showtableheader('forums_edit_basic', 'nobottom');
				showsetting('forums_edit_basic_name', 'namenew', $forum['name'], 'text');
				showsetting('forums_edit_base_name_color', 'extranew[namecolor]', $forum['extra']['namecolor'], 'color');
				if(!$multiset) {
					if($forum['icon']) {
						$valueparse = parse_url($forum['icon']);
						if(isset($valueparse['host'])) {
							$forumicon = $forum['icon'];
						} else {
							$forumicon = $_G['setting']['attachurl'].'common/'.$forum['icon'].'?'.random(6);
						}
						$forumiconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$forumicon.'" /><br />';
					}
					showsetting('forums_edit_basic_icon', 'iconnew', $forum['icon'], 'filetext', '', 0, $forumiconhtml);
					showsetting('forums_edit_basic_icon_width', 'extranew[iconwidth]', $forum['extra']['iconwidth'], 'text');
					if($forum['banner']) {
						$valueparse = parse_url($forum['banner']);
						if(isset($valueparse['host'])) {
							$forumbanner = $forum['banner'];
						} else {
							$forumbanner = $_G['setting']['attachurl'].'common/'.$forum['banner'].'?'.random(6);
						}
						$forumbannerhtml = '<label><input type="checkbox" class="checkbox" name="deletebanner" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$forumbanner.'" /><br />';
					}
					showsetting('forums_edit_basic_banner', 'bannernew', $forum['banner'], 'filetext', '', 0, $forumbannerhtml);
				}
				showsetting('forums_edit_basic_display', 'statusnew', $forum['status'], 'radio');
				showsetting('forums_edit_basic_shownav', 'shownavnew', array_key_exists($fid, $navs) ? 1 : 0, 'radio');
				if(!$multiset) {
					showsetting('forums_edit_basic_up', '', '', $fupselect);
				}
				showsetting('forums_edit_basic_redirect', 'redirectnew', $forum['redirect'], 'text');
				showsetting('forums_edit_basic_description', 'descriptionnew', htmlspecialchars_decode(html2bbcode($forum['description'])), 'textarea');
				showsetting('forums_edit_basic_rules', 'rulesnew', htmlspecialchars_decode(html2bbcode($forum['rules'])), 'textarea');
				showsetting('forums_edit_basic_keys', 'keysnew', $forumkeys[$fid], 'text');
				if(!empty($_G['setting']['domain']['root']['forum'])) {
					$iname = $multiset ? "multinew[{$_G[showsetting_multi]}][domainnew]" : 'domainnew';
					showsetting('forums_edit_extend_domain', '', '', $_G['scheme'].'://<input type="text" name="'.$iname.'" class="txt" value="'.$forum['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['forum']);
				} elseif(!$multiset) {
					showsetting('forums_edit_extend_domain', 'domainnew', '', 'text', 'disabled');
				}
				showtablefooter();
				if(!$multiset) {
					showtips('setting_seo_forum_tips', 'seo_tips', true, 'setseotips');
				}
				showtableheader();
				showsetting('forums_edit_basic_seotitle', 'seotitlenew', dhtmlspecialchars($forum['seotitle']), 'text');
				showsetting('forums_edit_basic_keyword', 'keywordsnew', dhtmlspecialchars($forum['keywords']), 'text');
				showsetting('forums_edit_basic_seodescription', 'seodescriptionnew', dhtmlspecialchars($forum['seodescription']), 'textarea');
				showtablefooter();
				showtagfooter('div');

				showtagheader('div', 'extend', $anchor == 'extend');
				if(!$multiset) {
					showtips('forums_edit_tips');
				}
				showtableheader('forums_edit_extend', 'nobottom');
				$multi_styleselect = $_GET['multi'] ? preg_replace('/\w+new/', 'multinew['.$_G['showsetting_multi'].'][\\0]', $styleselect) : $styleselect;
				$styleid = $forum['styleid'];
				$multi_styleselect = str_replace("selected=\"selected\"", "", $multi_styleselect);
				$multi_styleselect = str_replace("value=\"$styleid\"", "value=\"$styleid\" selected=\"selected\"", $multi_styleselect);
				showsetting('forums_edit_extend_style', '', '', $multi_styleselect);
				if($forum['type'] != 'sub') {
					showsetting('forums_edit_extend_sub_horizontal', 'forumcolumnsnew', $forum['forumcolumns'], 'text');
					showsetting('forums_edit_extend_subforumsindex', array('subforumsindexnew', array(
						array(-1, cplang('default')),
						array(1, cplang('yes')),
						array(0, cplang('no'))
					), 1), $forum['subforumsindex'], 'mradio');
					showsetting('forums_edit_extend_simple', 'simplenew', $forum['simple'], 'radio');
				} else {
					if($_GET['multi']) {
						showsetting('forums_edit_extend_sub_horizontal', '', '', cplang('forums_edit_sub_multi_tips'));
						showsetting('forums_edit_extend_subforumsindex', '', '', cplang('forums_edit_sub_multi_tips'));
						showsetting('forums_edit_extend_simple', '', '', cplang('forums_edit_sub_multi_tips'));
					}
				}
				showsetting('forums_edit_extend_widthauto', array('widthautonew', array(
					array(0, cplang('default')),
					array(-1, cplang('forums_edit_extend_widthauto_-1')),
					array(1, cplang('forums_edit_extend_widthauto_1')),
				), 1), $forum['widthauto'], 'mradio');
				showsetting('forums_edit_extend_picstyle', 'picstylenew', $forum['picstyle'], 'radio');
				showsetting('forums_edit_extend_allowside', 'allowsidenew', $forum['allowside'], 'radio');
				showsetting('forums_edit_extend_recommend_top', 'allowglobalsticknew', $forum['allowglobalstick'], 'radio');
				showsetting('forums_edit_extend_defaultorderfield', array('defaultorderfieldnew', array(
					array(0, cplang('forums_edit_extend_order_lastpost')),
					array(1, cplang('forums_edit_extend_order_starttime')),
					array(2, cplang('forums_edit_extend_order_replies')),
					array(3, cplang('forums_edit_extend_order_views'))
				)), $forum['defaultorderfield'], 'mradio');
				showsetting('forums_edit_extend_defaultorder', array('defaultordernew', array(
					array(0, cplang('forums_edit_extend_order_desc')),
					array(1, cplang('forums_edit_extend_order_asc'))
				)), $forum['defaultorder'], 'mradio');
				if($_G['setting']['allowreplybg']) {
					$replybghtml = '';
					if($forum['replybg']) {
						$replybgurl = parse_url($forum['replybg']);
						if(isset($replybgurl['host'])) {
							$replybgicon = $forum['replybg'];
						} else {
							$replybgicon = $_G['setting']['attachurl'].'common/'.$forum['replybg'].'?'.random(6);
						}
						$replybghtml = '<label><input type="checkbox" class="checkbox" name="delreplybg" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$replybgicon.'" width="200px" />';
					}
					showsetting('forums_edit_extend_reply_background', 'replybgnew', (!$replybgurl['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $forum['replybg']) : $forum['replybg']), 'filetext', '', 0, $replybghtml);
				}
				showsetting('forums_edit_extend_threadcache', 'threadcachesnew', $forum['threadcaches'], 'text');
				showsetting('forums_edit_extend_relatedgroup', 'relatedgroupnew', $forum['relatedgroup'], 'text');
				showsetting('forums_edit_extend_edit_rules', 'alloweditrulesnew', $forum['alloweditrules'], 'radio');
				showsetting('forums_edit_extend_disablecollect', 'disablecollectnew', $forum['disablecollect'], 'radio');
				showsetting('forums_edit_extend_recommend', 'modrecommendnew[open]', $forum['modrecommend']['open'], 'radio', '', 1);
				showsetting('forums_edit_extend_recommend_sort', array('modrecommendnew[sort]', array(
					array(1, cplang('forums_edit_extend_recommend_sort_auto')),
					array(0, cplang('forums_edit_extend_recommend_sort_manual')),
					array(2, cplang('forums_edit_extend_recommend_sort_mix')))), $forum['modrecommend']['sort'], 'mradio');
				showsetting('forums_edit_extend_recommend_orderby', array('modrecommendnew[orderby]', array(
					array(0, cplang('forums_edit_extend_recommend_orderby_dateline')),
					array(1, cplang('forums_edit_extend_recommend_orderby_lastpost')),
					array(2, cplang('forums_edit_extend_recommend_orderby_views')),
					array(3, cplang('forums_edit_extend_recommend_orderby_replies')),
					array(4, cplang('forums_edit_extend_recommend_orderby_digest')),
					array(5, cplang('forums_edit_extend_recommend_orderby_recommend')),
					array(6, cplang('forums_edit_extend_recommend_orderby_heats')),
					)), $forum['modrecommend']['orderby'], 'mradio');
				showsetting('forums_edit_extend_recommend_num', 'modrecommendnew[num]', $forum['modrecommend']['num'], 'text');
				showsetting('forums_edit_extend_recommend_imagenum', 'modrecommendnew[imagenum]', $forum['modrecommend']['imagenum'], 'text');
				showsetting('forums_edit_extend_recommend_imagesize', array('modrecommendnew[imagewidth]', 'modrecommendnew[imageheight]'), array(intval($forum['modrecommend']['imagewidth']), intval($forum['modrecommend']['imageheight'])), 'multiply');
				showsetting('forums_edit_extend_recommend_maxlength', 'modrecommendnew[maxlength]', $forum['modrecommend']['maxlength'], 'text');
				showsetting('forums_edit_extend_recommend_cachelife', 'modrecommendnew[cachelife]', $forum['modrecommend']['cachelife'], 'text');
				showsetting('forums_edit_extend_recommend_dateline', 'modrecommendnew[dateline]', $forum['modrecommend']['dateline'], 'text');
				showtablefooter();
				showtagfooter('div');

				showtagheader('div', 'posts', $anchor == 'posts');
				if(!$multiset) {
					showtips('forums_edit_tips');
				}
				showtableheader('forums_edit_posts', 'nobottom');
				showsetting('forums_edit_posts_modposts', array('modnewpostsnew', array(
					array(0, cplang('none')),
					array(1, cplang('forums_edit_posts_modposts_threads')),
					array(2, cplang('forums_edit_posts_modposts_posts'))
				)), $forum['modnewposts'], 'mradio');
				showsetting('forums_edit_posts_alloweditpost', 'alloweditpostnew', $forum['alloweditpost'], 'radio');
				showsetting('forums_edit_posts_recyclebin', 'recyclebinnew', $forum['recyclebin'], 'radio');
				showsetting('forums_edit_posts_html', 'allowhtmlnew', $forum['allowhtml'], 'radio');
				showsetting('forums_edit_posts_bbcode', 'allowbbcodenew', $forum['allowbbcode'], 'radio');
				showsetting('forums_edit_posts_imgcode', 'allowimgcodenew', $forum['allowimgcode'], 'radio');
				showsetting('forums_edit_posts_mediacode', 'allowmediacodenew', $forum['allowmediacode'], 'radio');
				showsetting('forums_edit_posts_smilies', 'allowsmiliesnew', $forum['allowsmilies'], 'radio');
				showsetting('forums_edit_posts_jammer', 'jammernew', $forum['jammer'], 'radio');
				showsetting('forums_edit_posts_anonymous', 'allowanonymousnew', $forum['allowanonymous'], 'radio');
				showsetting('forums_edit_posts_disablethumb', 'disablethumbnew', $forum['disablethumb'], 'radio');
				showsetting('forums_edit_posts_disablewatermark', 'disablewatermarknew', $forum['disablewatermark'], 'radio');

				showsetting('forums_edit_posts_allowpostspecial', array('allowpostspecialnew', array(
					cplang('thread_poll'),
					cplang('thread_trade'),
					cplang('thread_reward'),
					cplang('thread_activity'),
					cplang('thread_debate')
				)), $forum['allowpostspecial'], 'binmcheckbox');
				$threadpluginarray = array();
				if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
					$threadpluginarray[] = array($tpid, $data['name']);
				}
				if($threadpluginarray) {
					showsetting('forums_edit_posts_threadplugin', array('threadpluginnew', $threadpluginarray), $forum['threadplugin'], 'mcheckbox');
				}
				showsetting('forums_edit_posts_allowspecialonly', 'allowspecialonlynew', $forum['allowspecialonly'], 'radio');
				showsetting('forums_edit_posts_autoclose', array('autoclosenew', array(
					array(0, cplang('forums_edit_posts_autoclose_none'), array('autoclose_time' => 'none')),
					array(1, cplang('forums_edit_posts_autoclose_dateline'), array('autoclose_time' => '')),
					array(-1, cplang('forums_edit_posts_autoclose_lastpost'), array('autoclose_time' => ''))
				)), $forum['autoclose'], 'mradio');
				showtagheader('tbody', 'autoclose_time', $forum['autoclose'], 'sub');
				showsetting('forums_edit_posts_autoclose_time', 'autoclosetimenew', $forum['autoclosetime'], 'text');
				showtagfooter('tbody');
				showsetting('forums_edit_posts_attach_ext', 'attachextensionsnew', $forum['attachextensions'], 'text');
				showsetting('forums_edit_posts_allowfeed', 'allowfeednew', $forum['allowfeed'], 'radio');
				showsetting('forums_edit_posts_commentitem', 'commentitemnew', $forum['commentitem'], 'textarea');
				showsetting('forums_edit_posts_noantitheft', 'noantitheftnew', $forum['noantitheft'], 'radio');
				showsetting('forums_edit_posts_noforumhidewater', 'noforumhidewaternew', $forum['noforumhidewater'], 'radio');
				showsetting('forums_edit_posts_noforumrecommend', 'noforumrecommendnew', $forum['noforumrecommend'], 'radio');

				showtablefooter();
				showtagfooter('div');

				if(!$multiset) {
					showtagheader('div', 'attachtype', $anchor == 'attachtype');
					showtips('forums_edit_attachtype_tips');
					showtableheader();
					showtablerow('class="partition"', array('class="td25"', 'class="td24"'), array(cplang('del'), cplang('misc_attachtype_ext'), cplang('misc_attachtype_maxsize')));
					echo $attachtypes;
					echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['misc_attachtype_add'].'</a></div></tr>';
					showtablefooter();
					showtagfooter('div');

					showtagheader('div', 'credits', $anchor == 'credits');
					if(!$multiset) {
						showtips('forums_edit_tips');
					}
					showtableheader('forums_edit_credits_policy', 'fixpadding');
					echo '<tr class="header"><th>'.cplang('credits_id').'</th><th>'.cplang('setting_credits_policy_cycletype').'</th><th>'.cplang('setting_credits_policy_rewardnum').'</th><th class="td25">'.cplang('custom').'</th>';
					foreach($_G['setting']['extcredits'] as $i => $extcredit) {
						echo '<th>'.$extcredit['title'].'</th>';
					}
					echo '<th>&nbsp;</th></tr>';

					if(is_array($_G['setting']['extcredits'])) {
						foreach($rules as $rid => $rule) {
							$globalrule = $rule;
							$readonly = $checked = '';
							if(isset($forum['creditspolicy'][$rule['action']])) {
								$rule = $forum['creditspolicy'][$rule['action']];
								$checked = ' checked="checked"';
							} else {
								for($i = 1; $i <= 8; $i++) {
									$rule['extcredits'.$i] = '';
								}
								$readonly = ' readonly="readonly" style="display:none;"';
							}
							$usecustom = '<input type="checkbox" name="usecustom['.$rule['rid'].']" onclick="modifystate(this);" value="1" class="checkbox" '.$checked.' />';
							$tdarr = array($rule['rulename'], $rule['rid'] ? cplang('setting_credits_policy_cycletype_'.$rule['cycletype']) : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A', $usecustom);

							for($i = 1; $i <= 8; $i++) {
								if($_G['setting']['extcredits'][$i]) {
									array_push($tdarr, '<input type="text" name="creditnew['.$rule['rid'].']['.$i.']" class="txt smtxt" value="'.$rule['extcredits'.$i].'" '.$readonly.' /><span class="sml">('.($globalrule['extcredits'.$i]).')</span>');
								}
							}
							$opstr = '<a href="'.ADMINSCRIPT.'?action=credits&operation=edit&rid='.$rule['rid'].'&fid='.$fid.'" title="" class="act">'.cplang('edit').'</a>';
							array_push($tdarr, $opstr);
							showtablerow('', array_fill(4, count($_G['setting']['extcredits']) + 4, 'width="70"'), $tdarr);
						}

					}
					showtablerow('', 'class="lineheight" colspan="13"', cplang('forums_edit_credits_comment', array('fid' => $fid)));

					showtablefooter();
					print <<<EOF
					<script type="text/javascript">
						function modifystate(custom) {
							var trObj = custom.parentNode.parentNode;
							var inputsObj = trObj.getElementsByTagName('input');
							for(key in inputsObj) {
								var obj = inputsObj[key];
								if(typeof obj == 'object' && obj.type != 'checkbox') {
									obj.value = '';
									obj.readOnly = custom.checked ? false : true;
									obj.style.display = obj.readOnly ? 'none' : '';
								}
							}
						}
					</script>
EOF;
					showtagfooter('div');
				}

				if($allowthreadtypes && !$multiset) {
					$lang_forums_edit_threadtypes_use_cols = cplang('forums_edit_threadtypes_use_cols');
					$lang_forums_edit_threadtypes_use_choice = cplang('forums_edit_threadtypes_use_choice');
					echo <<<EOT
	<script type="text/JavaScript">
		var rowtypedata = [
			[
				[1,'', 'td25'],
				[1,'<input type="text" size="2" name="newdisplayorder[]" value="0" />'],
				[1,'<input type="text" name="newname[]" />'],
				[1,'<input type="text" name="newicon[]" />'],
				[1,'<input type="hidden" name="newenable[]" value="1"><input type="checkbox" class="checkbox" checked="checked" disabled />'],
				[1,'<input type="hidden" name="newmoderators[]" value="0"><input type="checkbox" class="checkbox" disabled />'],
				[1,'']
			],
			[
				[1,'', 'td25'],
				[1,'<input name="newextension[]" type="text" class="txt" size="10">', 'td24'],
				[1,'<input name="newmaxsize[]" type="text" class="txt" size="15">']
			]
		];
	</script>
EOT;
					showtagheader('div', 'threadtypes', $anchor == 'threadtypes');
					if(!$multiset) {
						showtips('forums_edit_tips');
					}
					showtableheader('forums_edit_threadtypes_config', 'nobottom');
					showsetting('forums_edit_threadtypes_status', array('threadtypesnew[status]', array(
						array(1, cplang('yes'), array('threadtypes_config' => '', 'threadtypes_manage' => '')),
						array(0, cplang('no'), array('threadtypes_config' => 'none', 'threadtypes_manage' => 'none'))
					), TRUE), $forum['threadtypes']['status'], 'mradio');
					showtagheader('tbody', 'threadtypes_config', $forum['threadtypes']['status']);
					showsetting('forums_edit_threadtypes_required', 'threadtypesnew[required]', $forum['threadtypes']['required'], 'radio');
					showsetting('forums_edit_threadtypes_listable', 'threadtypesnew[listable]', $forum['threadtypes']['listable'], 'radio');
					showsetting('forums_edit_threadtypes_prefix',
						array(
							'threadtypesnew[prefix]',
							array(
								array(0, cplang('forums_edit_threadtypes_noprefix')),
								array(1, cplang('forums_edit_threadtypes_textonly')),
								array(2, cplang('forums_edit_threadtypes_icononly')),
							),
						),
						$forum['threadtypes']['prefix'], 'mradio'
					);
					showtagfooter('tbody');
					showtablefooter();

					showtagheader('div', 'threadtypes_manage', $forum['threadtypes']['status']);
					showtableheader('forums_edit_threadtypes', 'noborder fixpadding');
					showsubtitle(array('delete', 'display_order', cplang('forums_edit_threadtypes_name').' '.cplang('tiny_bbcode_support'), 'forums_edit_threadtypes_icon', 'enable', 'forums_edit_threadtypes_moderators'));
					echo $typeselect;
					echo '<tr><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('threadtype_infotypes_add').'</a></div></td></tr>';
					showtablefooter();
					showtagfooter('div');
					showtagfooter('div');

					showtagheader('div', 'threadsorts', $anchor == 'threadsorts');
					if(!$multiset) {
						showtips('forums_edit_tips');
					}
					showtableheader('forums_edit_threadsorts', 'nobottom');
					showsetting('forums_edit_threadsorts_status', array('threadsortsnew[status]', array(
						array(1, cplang('yes'), array('threadsorts_config' => '', 'threadsorts_manage' => '')),
						array(0, cplang('no'), array('threadsorts_config' => 'none', 'threadsorts_manage' => 'none'))
					), TRUE), $forum['threadsorts']['status'], 'mradio');
					showtagheader('tbody', 'threadsorts_config', $forum['threadsorts']['status']);
					showsetting('forums_edit_threadtypes_required', 'threadsortsnew[required]', $forum['threadsorts']['required'], 'radio');
					showsetting('forums_edit_threadtypes_prefix', 'threadsortsnew[prefix]', $forum['threadsorts']['prefix'], 'radio');
					showsetting('forums_edit_threadsorts_default', 'threadsortsnew[default]', $forum['threadsorts']['default'], 'radio');
					showtagfooter('tbody');
					showtablefooter();

					showtagheader('div', 'threadsorts_manage', $forum['threadsorts']['status']);
					showtableheader('', 'noborder fixpadding');
					showsubtitle(array('enable', 'forums_edit_threadtypes_name', 'forums_edit_threadtypes_note', 'forums_edit_threadtypes_show', 'forums_edit_threadtypes_defaultshow'));
					echo $sortselect;
					showtablefooter();
					showtagfooter('div');
					showtagfooter('div');
				}

				showtagheader('div', 'perm', $anchor == 'perm');
				if(!$multiset) {
					showtips('forums_edit_tips');
				}
				showtableheader('', 'nobottom');
				showsetting('forums_edit_perm_price', 'pricenew', $forum['price'], 'text');
				showsetting('forums_edit_perm_passwd', 'passwordnew', $forum['password'], 'text');
				showsetting('forums_edit_perm_users', 'formulapermusersnew', $forum['formulapermusers'], 'textarea');
				$colums = array();
				loadcache('medals');
				foreach($_G['cache']['medals'] as $medalid => $medal) {
					$colums[] = array($medalid, $medal['name']);
				}
				showtagheader('tbody', '', $_G['setting']['medalstatus']);
				showsetting('forums_edit_perm_medal', array('medalnew', $colums), $forum['medal'], 'mcheckbox');
				showtagfooter('tbody');
				showtablefooter();

				if(!$multiset) {
					showtableheader('forums_edit_perm_forum', 'noborder fixpadding');
					showsubtitle(array(
						'',
						'<input class="checkbox" type="checkbox" name="chkall1" onclick="checkAll(\'prefix\', this.form, \'^viewperm\', \'chkall1\')" id="chkall1" /><label for="chkall1"><br />'.cplang('forums_edit_perm_view').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'^postperm\', \'chkall2\')" id="chkall2" /><label for="chkall2"><br />'.cplang('forums_edit_perm_post').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall3" onclick="checkAll(\'prefix\', this.form, \'^replyperm\', \'chkall3\')" id="chkall3" /><label for="chkall3"><br />'.cplang('forums_edit_perm_reply').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall4" onclick="checkAll(\'prefix\', this.form, \'^getattachperm\', \'chkall4\')" id="chkall4" /><label for="chkall4"><br />'.cplang('forums_edit_perm_getattach').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall5" onclick="checkAll(\'prefix\', this.form, \'^postattachperm\', \'chkall5\')" id="chkall5" /><label for="chkall5"><br />'.cplang('forums_edit_perm_postattach').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall6" onclick="checkAll(\'prefix\', this.form, \'^postimageperm\', \'chkall6\')" id="chkall6" /><label for="chkall6"><br />'.cplang('forums_edit_perm_postimage').'</label>'
					));

					$spviewgroup = array();
					foreach(array('member', 'special', 'specialadmin', 'system') as $type) {
						$tgroups = is_array($groups[$type]) ? $groups[$type] : array();
						showtablerow('', '', array('<b>'.cplang('usergroups_'.$type).'</b>'));
						foreach($tgroups as $group) {
							if($group['groupid'] != 1) {
								$spviewgroup[] = array($group['groupid'], $group['grouptitle']);
							}
							$colums = array('<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallv'.$group['groupid'].'" onclick="checkAll(\'value\', this.form, '.$group['groupid'].', \'chkallv'.$group['groupid'].'\')" id="chkallv_'.$group['groupid'].'" /><label for="chkallv_'.$group['groupid'].'"> '.$group['grouptitle'].'</label>');
							foreach($perms as $perm) {
								$checked = strstr($forum[$perm], "\t$group[groupid]\t") ? 'checked="checked"' : NULL;
								$colums[] = '<input class="checkbox" type="checkbox" name="'.$perm.'[]" value="'.$group['groupid'].'" chkvalue="'.$group['groupid'].'" '.$checked.'>';
							}
							showtablerow('', array('width="21%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="16%"', 'width="13%"', 'width="13%"'), $colums);
						}
					}
					$showverify = true;
					foreach($_G['setting']['verify'] as $vid => $verify) {
						if($verify['available']) {
							if($showverify) {
								showtablerow('', '', array('<b>'.$lang['forums_edit_perm_verify'].'</b>'));
								$showverify = false;
							}

							$colums = array('<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallverify'.$vid.'" onclick="checkAll(\'value\', this.form, \'verify'.$vid.'\', \'chkallverify'.$vid.'\')" id="chkallverify_'.$vid.'" /><label for="chkallverify_'.$vid.'"> '.$verify['title'].'</label>');
							foreach($perms as $perm) {
								$checked = strstr($forum[$perm], "\tv$vid\t") ? 'checked="checked"' : NULL;
								$colums[] = '<input class="checkbox" type="checkbox" name="'.$perm.'[]" value="v'.$vid.'" chkvalue="verify'.$vid.'" '.$checked.'>';
							}
							showtablerow('', array('width="21%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"'), $colums);
						}
					}
					showtablerow('', 'class="lineheight" colspan="6"', cplang('forums_edit_perm_forum_comment'));
					showtablefooter();

					showtableheader('forums_edit_perm_formula', 'fixpadding');
					$formulareplace .= '\'<u>'.cplang('setting_credits_formula_digestposts').'</u>\',\'<u>'.cplang('setting_credits_formula_posts').'</u>\'';

	?>
	<script type="text/JavaScript">
		function foruminsertunit(text, textend) {
			insertunit($('formulapermnew'), text, textend);
			formulaexp();
		}

		var formulafind = new Array('digestposts', 'posts');
		var formulareplace = new Array(<?php echo $formulareplace?>);
		function formulaexp() {
			var result = $('formulapermnew').value;
	<?php

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : cplang('setting_credits_formula_extcredits').$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.str_replace("'", "\'", $extcredittitle).'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="foruminsertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		$profilefields = '';
		foreach(C::t('common_member_profile_setting')->fetch_all_by_available_unchangeable(1, 1) as $profilefield) {
			echo 'result = result.replace(/'.$profilefield['fieldid'].'/g, \'<u>'.str_replace("'", "\'", $profilefield['title']).'</u>\');';
			$profilefields .= '<a href="###" onclick="foruminsertunit(\' '.$profilefield['fieldid'].' \')">&nbsp;'.$profilefield['title'].'&nbsp;</a>&nbsp;';
		}

		echo 'result = result.replace(/regdate/g, \'<u>'.cplang('forums_edit_perm_formula_regdate').'</u>\');';
		echo 'result = result.replace(/regday/g, \'<u>'.cplang('forums_edit_perm_formula_regday').'</u>\');';
		echo 'result = result.replace(/regip/g, \'<u>'.cplang('forums_edit_perm_formula_regip').'</u>\');';
		echo 'result = result.replace(/lastip/g, \'<u>'.cplang('forums_edit_perm_formula_lastip').'</u>\');';
		echo 'result = result.replace(/buyercredit/g, \'<u>'.cplang('forums_edit_perm_formula_buyercredit').'</u>\');';
		echo 'result = result.replace(/sellercredit/g, \'<u>'.cplang('forums_edit_perm_formula_sellercredit').'</u>\');';
		echo 'result = result.replace(/digestposts/g, \'<u>'.cplang('setting_credits_formula_digestposts').'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.cplang('setting_credits_formula_posts').'</u>\');';
		echo 'result = result.replace(/threads/g, \'<u>'.cplang('setting_credits_formula_threads').'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.cplang('setting_credits_formula_oltime').'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;<b>'.cplang('forums_edit_perm_formula_and').'</b>&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;<b>'.cplang('forums_edit_perm_formula_or').'</b>&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';
		echo 'result = result.replace(/==/g, \'=\');';

	?>
			$('formulapermexp').innerHTML = result;
		}
	</script>
	<tr><td colspan="2"><div class="extcredits">
	<?php echo $extcreditsbtn;?>
	<a href="###" onclick="foruminsertunit(' regdate ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regdate')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' regday ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regday')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' regip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regip')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' lastip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_lastip')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' buyercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_buyercredit')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' sellercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_sellercredit')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' digestposts ')"><?php echo cplang('forums_edit_perm_formula_digestposts')?></a>&nbsp;
	<a href="###" onclick="foruminsertunit(' posts ')"><?php echo cplang('forums_edit_perm_formula_posts')?></a>&nbsp;
	<a href="###" onclick="foruminsertunit(' threads ')"><?php echo cplang('forums_edit_perm_formula_threads')?></a>&nbsp;
	<a href="###" onclick="foruminsertunit(' oltime ')"><?php echo cplang('forums_edit_perm_formula_oltime')?></a>&nbsp;
	<a href="###" onclick="foruminsertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' == ')">&nbsp;=&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' != ')">&nbsp;!=&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' and ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_and')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="foruminsertunit(' or ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_or')?>&nbsp;</a>&nbsp;<br />
	<?php echo $profilefields;?>


	<div id="formulapermexp" class="margintop marginbot diffcolor2"><?php echo $formulapermexp?></div>
	</div>
	<textarea name="formulapermnew" id="formulapermnew" class="marginbot" style="width:80%" rows="3" onkeyup="formulaexp()" onkeydown="textareakey(this, event)"><?php echo dhtmlspecialchars($forum['formulaperm'])?></textarea>
	<script type="text/JavaScript">formulaexp()</script>
	<br /><span class="smalltxt"><?php cplang('forums_edit_perm_formula_comment', null, true);?></span>
	</td></tr>
	<?php

					showtablefooter();
					showtableheader('', 'noborder fixpadding');
					$forum['spviewperm'] = explode("\t", $forum['spviewperm']);
					showsetting('forums_edit_perm_spview', array('spviewpermnew', $spviewgroup), $forum['spviewperm'], 'mcheckbox');
					showsetting('forums_edit_perm_formulapermmessage', 'formulapermmessagenew', $forum['formulapermmessage'], 'textarea');
					showtablefooter();

				}
				if($pluginsetting) {
					showtagfooter('div');
					showtagheader('div', 'plugin', $anchor == 'plugin');
					showtableheader('', 'noborder fixpadding');
					foreach($pluginsetting as $setting) {
						showtitle($setting['name']);
						foreach($setting['setting'] as $varid => $var) {
							if($var['type'] != 'select') {
								showsetting($var['title'], 'pluginnew['.$varid.']', $forum['plugin'][$varid], $var['type'], '', 0, $var['description']);
							} else {
								showsetting($var['title'], array('pluginnew['.$varid.']', $var['select']), $forum['plugin'][$varid], $var['type'], '', 0, $var['description']);
							}
						}
					}
					showtablefooter();
				}

				showtagfooter('div');

				showtableheader('', 'notop');
				showsubmit('detailsubmit', 'submit');
				showtablefooter();
				$_G['showsetting_multi']++;
			}}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mfids)));
			showmulti();
		}

		showformfooter();

	} else {

		if(!$multiset) {
			$_GET['multinew'] = array(0 => array('single' => 1));
		}
		$pluginvars = array();
		require_once libfile('function/delete');
		foreach($_GET['multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_GET[''.$key] = $value;
			}
			$fid = $_GET['multi'][$k];
		}
		$forum = $mforum[$k];

		if(strlen($_GET['namenew']) > 50) {
			cpmsg('forums_name_toolong', '', 'error');
		}

		if(!$multiset) {
			if(!checkformulaperm($_GET['formulapermnew'])) {
				cpmsg('forums_formulaperm_error', '', 'error');
			}
			$_GET['formulapermnew'] = trim($_GET['formulapermnew']);
			$formulapermary[0] = $_GET['formulapermnew'];
			$formulapermary[1] = preg_replace(
				array("/(digestposts|posts|threads|oltime|extcredits[1-8])/", "/(regdate|regday|regip|lastip|buyercredit|sellercredit|field\d+)/"),
				array("getuserprofile('\\1')", "\$memberformula['\\1']"),
				$_GET['formulapermnew']);
			$formulapermary['message'] = $_GET['formulapermmessagenew'];
		} else {
			$formulapermary = dunserialize($forum['formulaperm']);
		}
		$formulapermary['medal'] = $_GET['medalnew'];
		$formulapermary['users'] = $_GET['formulapermusersnew'];
		$_GET['formulapermnew'] = serialize($formulapermary);

		$domain = '';
		if(!empty($_GET['domainnew']) && !empty($_G['setting']['domain']['root']['forum'])) {
			$domain = strtolower(trim($_GET['domainnew']));
		}
		require_once libfile('function/discuzcode');
		if($_GET['type'] == 'group') {
			if($_GET['namenew']) {
				$newstyleid = intval($_GET['styleidnew']);
				$forumcolumnsnew = $_GET['forumcolumnsnew'] > 1 ? intval($_GET['forumcolumnsnew']) : 0;
				$catforumcolumnsnew = $_GET['catforumcolumnsnew'] > 1 ? intval($_GET['catforumcolumnsnew']) : 0;
				$descriptionnew = preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode($_GET['descriptionnew'], 1, 0, 0, 0, 1, 1, 0, 0, 1));
				if(!empty($_G['setting']['domain']['root']['forum'])) {
					deletedomain($fid, 'subarea');
					if(!empty($domain)) {
						domaincheck($domain, $_G['setting']['domain']['root']['forum'], 1, 0);
						C::t('common_domain')->insert(array('domain' => $domain, 'domainroot' => $_G['setting']['domain']['root']['forum'], 'id' => $fid, 'idtype' => 'subarea'));
					}
				}
				C::t('forum_forum')->update($fid, array(
					'name' => $_GET['namenew'],
					'forumcolumns' => $forumcolumnsnew,
					'catforumcolumns' => $catforumcolumnsnew,
					'domain' => $domain,
					'status' => intval($_GET['statusnew']),
					'styleid' => $newstyleid,
				));

				$extranew = is_array($_GET['extranew']) ? $_GET['extranew'] : array();
				$extranew = serialize($extranew);
				C::t('forum_forumfield')->update($fid, array(
					'extra' => $extranew,
					'description' => $descriptionnew,
					'seotitle' => $_GET['seotitlenew'],
					'keywords' => $_GET['keywordsnew'],
					'seodescription' => $_GET['seodescriptionnew'],
				));
				loadcache('forums');
				$subfids = array();
				get_subfids($fid);

				if($newstyleid != $mforum[0]['styleid'] && !empty($subfids)) {
					C::t('forum_forum')->update($subfids, array('styleid' => $newstyleid));
				}

				if(array_key_exists($fid, $navs) && !$_GET['shownavnew']) {
					C::t('common_nav')->delete($navs[$fid]);
				} elseif(!array_key_exists($fid, $navs) && $_GET['shownavnew']) {
					$data = array(
						'url' => 'forum.php?mod=forumdisplay&fid='.$fid,
						'identifier' => $fid,
						'parentid' => 0,
						'name' => $_GET['namenew'],
						'displayorder' => 0,
						'subtype' => '',
						'type' => 5,
						'available' => 1,
						'navtype' => 0
					);
					C::t('common_nav')->insert($data);
				}

				updatecache(array('forums', 'setting'));

				cpmsg('forums_edit_succeed', 'action=forums', 'succeed');
			} else {
				cpmsg('forums_edit_name_invalid', '', 'error');
			}

		} else {
			$extensionarray = array();
			foreach(explode(',', $_GET['attachextensionsnew']) as $extension) {
				if($extension = trim($extension)) {
					$extensionarray[] = $extension;
				}
			}
			$_GET['attachextensionsnew'] = strtolower(implode(', ', $extensionarray));

			foreach($perms as $perm) {
				$_GET[''.$perm.'new'] = is_array($_GET[''.$perm]) && !empty($_GET[''.$perm]) ? "\t".implode("\t", $_GET[''.$perm])."\t" : '';
			}

			if(!$multiset) {
				if($_GET['delete']) {
					C::t('forum_attachtype')->delete_by_id_fid($_GET['delete'], $fid);
				}

				if(is_array($_GET['extension'])) {
					foreach($_GET['extension'] as $id => $val) {
						C::t('forum_attachtype')->update($id, array(
							'extension' => $_GET['extension'][$id],
							'maxsize' => $_GET['maxsize'][$id] * 1024,
						));
					}
				}

				if(is_array($_GET['newextension'])) {
					foreach($_GET['newextension'] as $key => $value) {
						if($newextension1 = trim($value)) {
							if(C::t('forum_attachtype')->count_by_extension_fid($newextension1, $fid)) {
								cpmsg('attachtypes_duplicate', '', 'error');
							}
							C::t('forum_attachtype')->insert(array(
								'extension' => $newextension1,
								'maxsize' => $_GET['newmaxsize'][$key] * 1024,
								'fid' => $fid
							));
						}
					}
				}
			}

			$fupadd = '';
			$forumdata = $forumfielddata = array();
			if($_GET['fupnew'] != $forum['fup'] && !$multiset) {
				if(C::t('forum_forum')->fetch_forum_num('', $fid)) {
					cpmsg('forums_edit_sub_notnull', '', 'error');
				}

				$fup = C::t('forum_forum')->fetch($_GET['fupnew']);

				$fupadd = ", type='".($fup['type'] == 'forum' ? 'sub' : 'forum')."', fup='$fup[fid]'";
				$forumdata['type'] = $fup['type'] == 'forum' ? 'sub' : 'forum';
				$forumdata['fup'] = $fup['fid'];
				C::t('forum_moderator')->delete_by_fid_inherited($fid, 1);
				if($fup['inheritedmod']) {
					$query = C::t('forum_moderator')->fetch_all_by_fid($_GET['fupnew'], FALSE);
				} else {
					$query = C::t('forum_moderator')->fetch_all_by_fid_inherited($_GET['fupnew'], 1);
				}
				foreach($query as $mod) {
					C::t('forum_moderator')->insert(array(
						'uid' => $mod['uid'],
						'fid' => $fid,
						'displayorder' => 0,
						'inherited' => 1
					), false, true);
				}

				$moderators = '';
				$modmemberarray = C::t('forum_moderator')->fetch_all_no_inherited_by_fid($fid);
				$members = C::t('common_member')->fetch_all_username_by_uid(array_keys($modmemberarray));
				$moderators = implode("\t", $members);

				C::t('forum_forumfield')->update($fid, array('moderators' => $moderators));
			}

			$allowpostspecialtrade = intval($_GET['allowpostspecialnew'][2]);
			$_GET['allowpostspecialnew'] = bindec(intval($_GET['allowpostspecialnew'][6]).intval($_GET['allowpostspecialnew'][5]).intval($_GET['allowpostspecialnew'][4]).intval($_GET['allowpostspecialnew'][3]).intval($_GET['allowpostspecialnew'][2]).intval($_GET['allowpostspecialnew'][1]));
			$allowspecialonlynew = $_GET['allowpostspecialnew'] || $_G['setting']['threadplugins'] && $_GET['threadpluginnew'] ? $_GET['allowspecialonlynew'] : 0;
			$forumcolumnsnew = $_GET['forumcolumnsnew'] > 1 ? intval($_GET['forumcolumnsnew']) : 0;
			$threadcachesnew = max(0, min(100, intval($_GET['threadcachesnew'])));
			$subforumsindexnew = $_GET['subforumsindexnew'] == -1 ? 0 : ($_GET['subforumsindexnew'] == 0 ? 2 : 1);
			$_GET['simplenew'] = isset($_GET['simplenew']) ? $_GET['simplenew'] : 0;
			$simplenew = bindec(sprintf('%02d', decbin($_GET['defaultorderfieldnew'])).$_GET['defaultordernew'].sprintf('%02d', decbin($subforumsindexnew)).'00'.$_GET['simplenew']);
			$allowglobalsticknew = $_GET['allowglobalsticknew'] ? 1 : 0;

			if(!empty($_G['setting']['domain']['root']['forum'])) {
				deletedomain($fid, 'forum');
				if(!empty($domain)) {
					domaincheck($domain, $_G['setting']['domain']['root']['forum'], 1, 0);
					C::t('common_domain')->insert(array('domain' => $domain, 'domainroot' => $_G['setting']['domain']['root']['forum'], 'id' => $fid, 'idtype' => 'forum'));
				}
			}
			$forumdata = array_merge($forumdata, array(
				'status' => $_GET['statusnew'],
				'name' => $_GET['namenew'],
				'styleid' => $_GET['styleidnew'],
				'alloweditpost' => $_GET['alloweditpostnew'],
				'allowpostspecial' => $_GET['allowpostspecialnew'],
				'allowspecialonly' => $allowspecialonlynew,
				'allowhtml' => $_GET['allowhtmlnew'],
				'allowbbcode' => $_GET['allowbbcodenew'],
				'allowimgcode' => $_GET['allowimgcodenew'],
				'allowmediacode' => $_GET['allowmediacodenew'],
				'allowsmilies' => $_GET['allowsmiliesnew'],
				'alloweditrules' => $_GET['alloweditrulesnew'],
				'allowside' => $_GET['allowsidenew'],
				'disablecollect' => $_GET['disablecollectnew'],
				'modnewposts' => $_GET['modnewpostsnew'],
				'recyclebin' => $_GET['recyclebinnew'],
				'jammer' => $_GET['jammernew'],
				'allowanonymous' => $_GET['allowanonymousnew'],
				'forumcolumns' => $forumcolumnsnew,
				'catforumcolumns' => $catforumcolumnsnew,
				'threadcaches' => $threadcachesnew,
				'simple' => $simplenew,
				'allowglobalstick' => $allowglobalsticknew,
				'disablethumb' => $_GET['disablethumbnew'],
				'disablewatermark' => $_GET['disablewatermarknew'],
				'autoclose' => intval($_GET['autoclosenew'] * $_GET['autoclosetimenew']),
				'allowfeed' => $_GET['allowfeednew'],
				'domain' => $domain,
			));
			C::t('forum_forum')->update($fid, $forumdata);

			if(!(C::t('forum_forumfield')->fetch($fid))) {
				C::t('forum_forumfield')->insert(array('fid' => $fid));
			}

			if(!$multiset) {
				$creditspolicynew = array();
				$creditspolicy = $forum['creditspolicy'] ? dunserialize($forum['creditspolicy']) : array();
				foreach($_GET['creditnew'] as $rid => $rule) {
					$creditspolicynew[$rules[$rid]['action']] = isset($creditspolicy[$rules[$rid]['action']]) ? $creditspolicy[$rules[$rid]['action']] : $rules[$rid];
					$usedefault = $_GET['usecustom'][$rid] ? false : true;

					if(!$usedefault) {
						foreach($rule as $i => $v) {
							$creditspolicynew[$rules[$rid]['action']]['extcredits'.$i] = is_numeric($v) ? intval($v) : 0;
						}
					}

					$cpfids = explode(',', $rules[$rid]['fids']);
					$cpfidsnew = array();
					foreach($cpfids as $cpfid) {
						if(!$cpfid) {
							continue;
						}
						if($cpfid != $fid) {
							$cpfidsnew[] = $cpfid;
						}
					}
					if(!$usedefault) {
						$cpfidsnew[] = $fid;
						$creditspolicynew[$rules[$rid]['action']]['fids'] = $rules[$rid]['fids'] = implode(',', $cpfidsnew);
					} else {
						$rules[$rid]['fids'] = implode(',', $cpfidsnew);
						unset($creditspolicynew[$rules[$rid]['action']]);
					}
					C::t('common_credit_rule')->update($rid, array('fids' => $rules[$rid]['fids']));
				}
				$forumfielddata = array();
				$forumfielddata['creditspolicy'] = serialize($creditspolicynew);

				$threadtypesnew = $_GET['threadtypesnew'];
				$threadtypesnew['types'] = $threadtypes['special'] = $threadtypes['show'] = array();
				$threadsortsnew['types'] = $threadsorts['special'] = $threadsorts['show'] = array();

				if($allowthreadtypes) {
					if(is_array($_GET['newname']) && $_GET['newname']) {
						$newname = array_unique($_GET['newname']);
						if($newname) {
							foreach($newname as $key => $val) {
								$newname[$key] = $val = strip_tags(trim(str_replace(array("'", "\""), array(), $val)), "<font><span><b><strong>");
								if($_GET['newenable'][$key] && $val) {
									$newtypearr = C::t('forum_threadclass')->fetch_by_fid_name($fid, $val);
									$newtypeid = $newtypearr['typeid'];
									if(!$newtypeid) {
										$threadtypes_newdisplayorder = intval($_GET['newdisplayorder'][$key]);
										$threadtypes_newicon = trim($_GET['newicon'][$key]);
										$newtypeid = C::t('forum_threadclass')->insert(array('fid' => $fid, 'name' => $val, 'displayorder' => $threadtypes_newdisplayorder, 'icon' => $threadtypes_newicon, 'moderators' => intval($_GET['newmoderators'][$key])), true);
									} else {
										$threadtypes_newicon = $newtypearr['icon'];
										$threadtypes_newdisplayorder = $newtypearr['displayorder'];
										$_GET['newmoderators'][$key] = $newtypearr['moderators'];
									}
									$threadtypesnew['options']['name'][$newtypeid] = $val;
									$threadtypesnew['options']['icon'][$newtypeid] = $threadtypes_newicon;
									$threadtypesnew['options']['displayorder'][$newtypeid] = $threadtypes_newdisplayorder;
									$threadtypesnew['options']['enable'][$newtypeid] = 1;
									$threadtypesnew['options']['moderators'][$newtypeid] = $_GET['newmoderators'][$key];
								}
							}
						}
						$threadtypesnew['status'] = 1;
					} else {
						$newname = array();
					}
					if($threadtypesnew['status']) {
						if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {
							if(!empty($threadtypesnew['options']['enable'])) {
								$typeids = array_keys($threadtypesnew['options']['enable']);
							} else {
								$typeids = array(0);
							}
							foreach(C::t('forum_threadclass')->fetch_all_by_typeid($typeids) as $type) {
								if($threadtypesnew['options']['name'][$type['typeid']] != $type['name'] ||
									$threadtypesnew['options']['displayorder'][$type['typeid']] != $type['displayorder'] ||
									$threadtypesnew['options']['icon'][$type['typeid']] != $type['icon'] ||
									$threadtypesnew['options']['moderators'][$type['typeid']] != $type['moderators']) {
									$threadtypesnew['options']['name'][$type['typeid']] = strip_tags(trim(str_replace(array("'", "\""), array(), $threadtypesnew['options']['name'][$type['typeid']])), "<font><span><b><strong>");
									C::t('forum_threadclass')->update_by_typeid($type['typeid'], array(
										'name' => $threadtypesnew['options']['name'][$type['typeid']],
										'displayorder' => $threadtypesnew['options']['displayorder'][$type['typeid']],
										'icon' => $threadtypesnew['options']['icon'][$type['typeid']],
										'moderators' => $threadtypesnew['options']['moderators'][$type['typeid']],
									));
								}
							}
							if(!empty($threadtypesnew['options']['delete'])) {
								C::t('forum_threadclass')->delete_by_typeid($threadtypesnew['options']['delete']);
							}
						}
					} else {
						$threadtypesnew = '';
					}
					if($threadtypesnew && $typeids) {
						foreach(C::t('forum_threadclass')->fetch_all_by_typeid($typeids) as $type) {
							if($threadtypesnew['options']['enable'][$type['typeid']]) {
								$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['options']['name'][$type['typeid']];
							}
							$threadtypesnew['icons'][$type['typeid']] = trim($threadtypesnew['options']['icon'][$type['typeid']]);
							$threadtypesnew['moderators'][$type['typeid']] = $threadtypesnew['options']['moderators'][$type['typeid']];
						}
						$threadtypesnew = $threadtypesnew['types'] ? serialize(array
							(
							'required' => (bool)$threadtypesnew['required'],
							'listable' => (bool)$threadtypesnew['listable'],
							'prefix' => $threadtypesnew['prefix'],
							'types' => $threadtypesnew['types'],
							'icons' => $threadtypesnew['icons'],
							'moderators' => $threadtypesnew['moderators'],
							)) : '';
					}
					$forumfielddata['threadtypes'] = is_array($threadtypesnew) ? serialize($threadtypesnew) : $threadtypesnew;

					$threadsortsnew = $_GET['threadsortsnew'];
					if($threadsortsnew['status']) {
						if(is_array($threadsortsnew['options']) && $threadsortsnew['options']) {
							if(!empty($threadsortsnew['options']['enable'])) {
								$sortids = array_keys($threadsortsnew['options']['enable']);
							} else {
								$sortids = array();
							}

							$query = C::t('forum_threadtype')->fetch_all_for_order($sortids);
							foreach($query as $sort) {
								if($threadsortsnew['options']['enable'][$sort['typeid']]) {
									$threadsortsnew['types'][$sort['typeid']] = $sort['name'];
								}
								$threadsortsnew['expiration'][$sort['typeid']] = $sort['expiration'];
								$threadsortsnew['description'][$sort['typeid']] = $sort['description'];
								$threadsortsnew['show'][$sort['typeid']] = $threadsortsnew['options']['show'][$sort['typeid']] ? 1 : 0;
							}
						}

						if($threadsortsnew['default'] && !$threadsortsnew['defaultshow']) {
							cpmsg('forums_edit_threadsort_nonexistence', '', 'error');
						}

						$threadsortsnew = $threadsortsnew['types'] ? serialize(array
							(
							'required' => (bool)$threadsortsnew['required'],
							'prefix' => (bool)$threadsortsnew['prefix'],
							'types' => $threadsortsnew['types'],
							'show' => $threadsortsnew['show'],
							'expiration' => $threadsortsnew['expiration'],
							'description' => $threadsortsnew['description'],
							'defaultshow' => $threadsortsnew['default'] ? $threadsortsnew['defaultshow'] : '',
							)) : '';
					} else {
						$threadsortsnew = '';
					}

					$forumfielddata['threadsorts'] = $threadsortsnew;

				}
			}

			$threadpluginnew = serialize($_GET['threadpluginnew']);
			$modrecommendnew = $_GET['modrecommendnew'];
			$modrecommendnew['num'] = $modrecommendnew['num'] ? intval($modrecommendnew['num']) : 10;
			$modrecommendnew['cachelife'] = intval($modrecommendnew['cachelife']);
			$modrecommendnew['maxlength'] = $modrecommendnew['maxlength'] ? intval($modrecommendnew['maxlength']) : 0;
			$modrecommendnew['dateline'] = $modrecommendnew['dateline'] ? intval($modrecommendnew['dateline']) : 0;
			$modrecommendnew['imagenum'] = $modrecommendnew['imagenum'] ? intval($modrecommendnew['imagenum']) : 0;
			$modrecommendnew['imagewidth'] = $modrecommendnew['imagewidth'] ? intval($modrecommendnew['imagewidth']) : 300;
			$modrecommendnew['imageheight'] = $modrecommendnew['imageheight'] ? intval($modrecommendnew['imageheight']): 250;
			$descriptionnew = preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode($_GET['descriptionnew'], 1, 0, 0, 0, 1, 1, 0, 0, 1));
			$rulesnew = preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode($_GET['rulesnew'], 1, 0, 0, 0, 1, 1, 0, 0, 1));
			$extranew = is_array($_GET['extranew']) ? $_GET['extranew'] : array();
			$forum['extra'] = dunserialize($forum['extra']);
			$forum['extra']['namecolor'] = $extranew['namecolor'];

			if(!$multiset) {
				if(($_GET['deletebanner'] || $_FILES['bannernew']) && $forum['banner']) {
					$valueparse = parse_url($forum['banner']);
					if(!isset($valueparse['host'])) {
						@unlink($_G['setting']['attachurl'].'common/'.$forum['banner']);
					}
					$forumfielddata['banner'] = '';
					if($_GET['bannernew'] == $forum['banner']) {
						$_GET['bannernew'] = '';
					}
				}
				if($_FILES['bannernew']) {
					$bannernew = upload_icon_banner($forum, $_FILES['bannernew'], 'banner');
				} else {
					$bannernew = $_GET['bannernew'];
				}
				if($bannernew) {
					$forumfielddata['banner'] = $bannernew;
				}

				if($_GET['deleteicon'] || $_FILES['iconnew']) {
					$valueparse = parse_url($forum['icon']);
					if(!isset($valueparse['host'])) {
						@unlink($_G['setting']['attachurl'].'common/'.$forum['icon']);
					}
					$forumfielddata['icon'] = '';
					$forum['extra']['iconwidth'] = '';
					if($_GET['iconnew'] == $forum['icon']) {
						$_GET['iconnew'] = '';
					}
				}
				if($_FILES['iconnew']) {
					$iconnew = upload_icon_banner($forum, $_FILES['iconnew'], 'icon');
				} else {
					$iconnew = $_GET['iconnew'];
				}
				if($iconnew) {
					$forumfielddata['icon'] = $iconnew;
					if(!$extranew['iconwidth']) {
						$valueparse = parse_url($forumfielddata['icon']);
						if(!isset($valueparse['host'])) {
							$iconnew = $_G['setting']['attachurl'].'common/'.$forumfielddata['icon'];
						}
						if($info = @getimagesize($iconnew)) {
							$extranew['iconwidth'] = $info[0];
						}
					}
					$forum['extra']['iconwidth'] = $extranew['iconwidth'];
				} else {
					$forum['extra']['iconwidth'] = '';
				}
			}

			$extranew = serialize($forum['extra']);

			$forumfielddata = array_merge($forumfielddata, array(
				'description' => $descriptionnew,
				'password' => $_GET['passwordnew'],
				'redirect' => $_GET['redirectnew'],
				'rules' => $rulesnew,
				'attachextensions' => $_GET['attachextensionsnew'],
				'modrecommend' => $modrecommendnew && is_array($modrecommendnew) ? serialize($modrecommendnew) : '',
				'seotitle' => $_GET['seotitlenew'],
				'keywords' => $_GET['keywordsnew'],
				'seodescription' => $_GET['seodescriptionnew'],
				'threadplugin' => $threadpluginnew,
				'extra' => $extranew,
				'commentitem' => $_GET['commentitemnew'],
				'formulaperm' => $_GET['formulapermnew'],
				'picstyle' => $_GET['picstylenew'],
				'widthauto' => $_GET['widthautonew'],
				'noantitheft' => intval($_GET['noantitheftnew']),
				'noforumhidewater' => intval($_GET['noforumhidewaternew']),
				'noforumrecommend' => intval($_GET['noforumrecommendnew']),
				'price' => intval($_GET['pricenew']),
			));
			if(!$multiset) {

				if($_GET['delreplybg']) {
					$valueparse = parse_url($forum['replybg']);
					if(!isset($valueparse['host']) && file_exists($_G['setting']['attachurl'].'common/'.$forum['replybg'])) {
						@unlink($_G['setting']['attachurl'].'common/'.$forum['replybg']);
					}
					$_GET['replybgnew'] = '';
				}
				if($_FILES['replybgnew']) {
					$data = array('fid' => "$fid");
					$replybgnew = upload_icon_banner($data, $_FILES['replybgnew'], 'replybg');
				} else {
					$replybgnew = $_GET['replybgnew'];
				}

				$forumfielddata = array_merge($forumfielddata, array(
					'viewperm' => $_GET['viewpermnew'],
					'postperm' => $_GET['postpermnew'],
					'replyperm' => $_GET['replypermnew'],
					'getattachperm' => $_GET['getattachpermnew'],
					'postattachperm' => $_GET['postattachpermnew'],
					'postimageperm' => $_GET['postimagepermnew'],
					'relatedgroup' => $_GET['relatedgroupnew'],
					'spviewperm' => implode("\t", $_GET['spviewpermnew']),
					'replybg' => $replybgnew
				));
			}
			if($forumfielddata) {
				C::t('forum_forumfield')->update($fid, $forumfielddata);
			}
			if($pluginsetting) {
				foreach($_GET['pluginnew'] as $pluginvarid => $value) {
					$pluginvars[$pluginvarid][$fid] = $value;
				}
			}

			if($modrecommendnew && !$modrecommendnew['sort']) {
				require_once libfile('function/forumlist');
				recommendupdate($fid, $modrecommendnew, '1');
			}

			if($forumkeys[$fid] != $_GET['keysnew'] && preg_match('/^\w*$/', $_GET['keysnew']) && !preg_match('/^\d+$/', $_GET['keysnew'])) {
				$forumkeys[$fid] = $_GET['keysnew'];
				C::t('common_setting')->update('forumkeys', $forumkeys);
			}

		}
		if(array_key_exists($fid, $navs) && !$_GET['shownavnew']) {
			C::t('common_nav')->delete($navs[$fid]);
		} elseif(!array_key_exists($fid, $navs) && $_GET['shownavnew']) {
			$data = array(
				'url' => 'forum.php?mod=forumdisplay&fid='.$fid,
				'identifier' => $fid,
				'parentid' => 0,
				'name' => $_GET['namenew'],
				'displayorder' => 0,
				'subtype' => '',
				'type' => 5,
				'available' => 1,
				'navtype' => 0
			);
			C::t('common_nav')->insert($data);
		}
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				unset($_GET[''.$key]);
			}
		}
		}

		if($pluginvars) {
			set_pluginsetting($pluginvars);
		}

		updatecache(array('forums', 'setting', 'creditrule', 'attachtype'));
		cpmsg('forums_edit_succeed', "mod=forum&action=forums&operation=edit&".($multiset ? 'multi='.implode(',', $_GET['multi']) : "fid=$fid").($_GET['anchor'] ? "&anchor={$_GET['anchor']}" : ''), 'succeed');

	}

} elseif($operation == 'delete' && FORMHASH == $_GET['formhash']) {
	$ajax = $_GET['ajax'];
	$confirmed = $_GET['confirmed'];
	$finished = $_GET['finished'];
	$total = intval($_GET['total']);
	$pp = intval($_GET['pp']);
	$currow = intval($_GET['currow']);

	if($_GET['ajax']) {
		require_once libfile('function/post');
		$tids = array();

		foreach(C::t('forum_thread')->fetch_all_by_fid($fid, $pp) as $thread) {
			$tids[] = $thread['tid'];
		}
		require_once libfile('function/delete');
		deletethread($tids);
		deletedomain($fid, 'forum');
		deletedomain($fid, 'subarea');
		if($currow + $pp > $total) {
			C::t('forum_forum')->delete_by_fid($fid);
			C::t('common_nav')->delete_by_type_identifier(5, $fid);
			C::t('home_favorite')->delete_by_id_idtype($fid, 'fid');
			C::t('forum_moderator')->delete_by_fid($fid);
			C::t('common_member_forum_buylog')->delete_by_fid($fid);
			C::t('forum_access')->delete_by_fid($fid);
			echo 'TRUE';
			exit;
		}

		echo 'GO';
		exit;

	} else {

		if($_GET['finished']) {
			updatecache('forums');
			cpmsg('forums_delete_succeed', 'action=forums', 'succeed');

		}

		if(C::t('forum_forum')->fetch_forum_num('', $fid)) {
			cpmsg('forums_delete_sub_notnull', '', 'error');
		}

		if(!$_GET['confirmed']) {

			cpmsg('forums_delete_confirm', "action=forums&operation=delete&fid=$fid&formhash=".FORMHASH, 'form');

		} else {

			$threads = C::t('forum_thread')->count_by_fid($fid);
			cpmsg('forums_delete_alarm', "action=forums&operation=delete&fid=$fid&confirmed=1&formhash=".FORMHASH, 'loadingform', '', '<div id="percent">0%</div>', FALSE);

			echo "
			<div id=\"statusid\" style=\"display:none\"></div>
			<script type=\"text/JavaScript\">
				var xml_http_building_link = '".cplang('xml_http_building_link')."';
				var xml_http_sending = '".cplang('xml_http_sending')."';
				var xml_http_loading = '".cplang('xml_http_loading')."';
				var xml_http_load_failed = '".cplang('xml_http_load_failed')."';
				var xml_http_data_in_processed = '".cplang('xml_http_data_in_processed')."';
				var adminfilename = '".ADMINSCRIPT."';
				function forumsdelete(url, total, pp, currow) {

					var x = new Ajax('HTML', 'statusid');
					x.get(url+'&ajax=1&pp='+pp+'&total='+total+'&currow='+currow, function(s) {
						if(s != 'GO') {
							location.href = adminfilename + '?action=forums&operation=delete&finished=1&formhash=".FORMHASH."';
						}

						currow += pp;
						var percent = ((currow / total) * 100).toFixed(0);
						percent = percent > 100 ? 100 : percent;
						document.getElementById('percent').innerHTML = percent+'%';
						document.getElementById('percent').style.backgroundPosition = '-'+percent+'%';

						if(currow < total) {
							forumsdelete(url, total, pp, currow);
						}
					});
				}
				forumsdelete(adminfilename + '?action=forums&operation=delete&fid=$fid&confirmed=1&formhash=".FORMHASH."', $threads, 2000, 0);
			</script>
			";
		}
	}

} elseif($operation == 'copy') {

	loadcache('forums');

	$source = intval($_GET['source']);
	$sourceforum = $_G['cache']['forums'][$source];

	if(empty($sourceforum) || $sourceforum['type'] == 'group') {
		cpmsg('forums_copy_source_invalid', '', 'error');
	}

	$delfields = array(
		'forums'	=> array('fid', 'fup', 'type', 'name', 'status', 'displayorder', 'threads', 'posts', 'todayposts', 'lastpost', 'modworks', 'icon', 'level', 'commoncredits', 'archive', 'recommend'),
		'forumfields'	=> array('description', 'password', 'redirect', 'moderators', 'rules', 'threadtypes', 'threadsorts', 'threadplugin', 'jointype', 'gviewperm', 'membernum', 'dateline', 'lastupdate', 'founderuid', 'foundername', 'banner', 'groupnum', 'activity'),
	);
	$fields = array(
		'forums' 	=> C::t('forum_forum')->fetch_table_struct('forum_forum'),
		'forumfields'	=> C::t('forum_forum')->fetch_table_struct('forum_forumfield'),
	);

	if(!submitcheck('copysubmit')) {

		require_once libfile('function/forumlist');

		$forumselect = '<select name="target[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		$optselect = '<select name="options[]" size="10" multiple="multiple">';
		$fieldarray = array_merge($fields['forums'], $fields['forumfields']);
		$listfields = array_diff($fieldarray, array_merge($delfields['forums'], $delfields['forumfields']));
		foreach($listfields as $field) {
			if(isset($lang['project_option_forum_'.$field])) {
				$optselect .= '<option value="'.$field.'">'.$lang['project_option_forum_'.$field].'</option>';
			}
		}
		$optselect .= '</select>';
		shownav('forum', 'forums_copy');
		showsubmenu('forums_copy');
		showtips('forums_copy_tips');
		showformheader('forums&operation=copy');
		showhiddenfields(array('source' => $source));
		showtableheader();
		showtitle('forums_copy');
		showsetting(cplang('forums_copy_source').':','','', $sourceforum['name']);
		showsetting('forums_copy_target', '', '', $forumselect);
		showsetting('forums_copy_options', '', '', $optselect);
		showsubmit('copysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$fids = array();
		if(is_array($_GET['target']) && count($_GET['target'])) {
			foreach($_GET['target'] as $fid) {
				if(($fid = intval($fid)) && $fid != $source ) {
					$fids[] = $fid;
				}
			}
		}
		if(empty($fids)) {
			cpmsg('forums_copy_target_invalid', '', 'error');
		}

		$forumoptions = array();
		if(is_array($_GET['options']) && !empty($_GET['options'])) {
			foreach($_GET['options'] as $option) {
				if($option = trim($option)) {
					if(in_array($option, $fields['forums'])) {
						$forumoptions['forum_forum'][] = $option;
					} elseif(in_array($option, $fields['forumfields'])) {
						$forumoptions['forum_forumfield'][] = $option;
					}
				}
			}
		}

		if(empty($forumoptions)) {
			cpmsg('forums_copy_options_invalid', '', 'error');
		}
		foreach(array('forum_forum', 'forum_forumfield') as $table) {
			if(is_array($forumoptions[$table]) && !empty($forumoptions[$table])) {
				$sourceforum = C::t($table)->fetch($source);
				foreach($sourceforum as $key=>$value) {
					if(!in_array($key, $forumoptions[$table])) {
						unset($sourceforum[$key]);
					}
				}
				if(!$sourceforum) {
					cpmsg('forums_copy_source_invalid', '', 'error');
				}
				C::t($table)->update($fids, $sourceforum);
			}
		}

		updatecache('forums');
		cpmsg('forums_copy_succeed', 'action=forums', 'succeed');

	}

}

function showforum(&$forum, $type = '', $last = '', $toggle = false) {

	global $_G;

	if($last == '') {

		$navs = array();
		foreach(C::t('common_nav')->fetch_all_by_navtype_type(0, 5) as $nav) {
			$navs[] = $nav['identifier'];
		}
		$return = '<tr class="hover">'.
			'<td class="td25"'.($type == 'group' ? ' onclick="toggle_group(\'group_'.$forum['fid'].'\', $(\'a_group_'.$forum['fid'].'\'))"' : '').'>'.($type == 'group' ? '<a href="javascript:;" id="a_group_'.$forum['fid'].'">'.($toggle ? '[+]' : '[-]').'</a>' : '').'</td>
			<td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
			$_G['fg'] = !empty($_G['fg']) ? intval($_G['fg']) : 0;
			$_G['fg']++;
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = '';
		if(!$forum['status']  || $forum['password'] || $forum['redirect'] || in_array($forum['fid'], $navs)) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
			$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
			$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
			$boardattr .= !in_array($forum['fid'], $navs) ? '' : ' '.cplang('misc_customnav_parent_top');
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />'.
			($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.cplang('forums_admin_add_sub').'</a>' : '').
			'</div>'.$boardattr.
			'</td><td align="right" class="td23 lightfont">('.($type == 'group' ? 'gid:' : 'fid:').$forum['fid'].')</td>'.
			'</td><td class="td23">'.showforum_moderators($forum).'</td>
			<td width="160"><input class="checkbox" value="'.$forum['fid'].'" type="checkbox"'.($type != 'group' ? ' chkvalue="g'.$_G['fg'].'" onclick="multiupdate(this, '.$forum['fid'].')"' : ' name="gc'.$_G['fg'].'" onclick="checkAll(\'value\', this.form, \'g'.$_G['fg'].'\', \'gc'.$_G['fg'].'\', 1)"').' />'.'
			<a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.cplang('forums_edit_comment').'" class="act">'.cplang('edit').'</a>'.
			($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].'" title="'.cplang('forums_copy_comment').'" class="act">'.cplang('forums_copy').'</a>' : '').
			'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'&formhash='.FORMHASH.'" title="'.cplang('forums_delete_comment').'" class="act">'.cplang('delete').'</a></td></tr>';
		if($type == 'group') $return .= '<tbody id="group_'.$forum['fid'].'"'.($toggle ? ' style="display:none;"' : '').'>';
	} else {
		if($last == 'lastboard') {
			$return = '</tbody><tr><td></td><td colspan="4"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('forums_admin_add_forum').'</a></div></td><td>&nbsp;</td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '</tbody><tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('forums_admin_add_category').'</a></div></td>'.
				'<td class="bold"><a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&multi=\' + getmultiids();return false;">'.cplang('multiedit').'</a></td>'.
				'</tr>';
		}
	}

	echo  $return = isset($return) ? $return : '';

	return $forum['fid'];
}

function showforum_moderators($forum) {
	global $_G;
	if($forum['moderators']) {
		$moderators = explode("\t", $forum['moderators']);
		$count = count($moderators);
		$max = $count > 2 ? 2 : $count;
		$mods = array();
		for($i = 0;$i < $max;$i++) {
			$mods[] = $forum['inheritedmod'] ? '<b>'.$moderators[$i].'</b>' : $moderators[$i];
		}
		$r = implode(', ', $mods);
		if($count > 2) {
			$r = '<span onmouseover="showMenu({\'ctrlid\':this.id})" id="mods_'.$forum['fid'].'">'.$r.'</span>';
			$mods = array();
			foreach($moderators as $moderator) {
				$mods[] = $forum['inheritedmod'] ? '<b>'.$moderator.'</b>' : $moderator;
			}
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
			$r .= '<div class="dropmenu1" id="mods_'.$forum['fid'].'_menu" style="display: none">'.implode('<br />', $mods).'</div>';
		} else {
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
		}


	} else {
		$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.cplang('forums_admin_no_moderator').'</a>';
	}
	return $r;
}

function getthreadclasses_html($fid) {
	$threadtypes = C::t('forum_forumfield')->fetch($fid);
	$threadtypes = dunserialize($threadtypes['threadtypes']);

	foreach(C::t('forum_threadclass')->fetch_all_by_fid($fid) as $type) {
		$enablechecked = $moderatorschecked = '';
		$typeselected = array();
		if(isset($threadtypes['types'][$type['typeid']])) {
			$enablechecked = ' checked="checked"';
		}
		if($type['moderators']) {
			$moderatorschecked = ' checked="checked"';
		}
		$typeselect .= showtablerow('', array('class="td25"'), array(
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][delete][]\" value=\"{$type['typeid']}\" />",
			"<input type=\"text\" size=\"2\" name=\"threadtypesnew[options][displayorder][{$type['typeid']}]\" value=\"{$type['displayorder']}\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][name][{$type['typeid']}]\" value=\"".(str_replace(array("'", "\""), array(), $type['name']))."\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][icon][{$type['typeid']}]\" value=\"{$type['icon']}\" />",
			'<input type="checkbox" name="threadtypesnew[options][enable]['.$type['typeid'].']" value="1" class="checkbox"'.$enablechecked.' />',
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][moderators][{$type['typeid']}]\" value=\"1\"{$moderatorschecked} />",
		), TRUE);
	}
	return $typeselect;
}

function get_subfids($fid) {
	global $subfids, $_G;
	$subfids[] = $fid;
	foreach($_G['cache']['forums'] as $key => $value) {
		if($value['fup'] == $fid) {
			get_subfids($value['fid']);
		}
	}
}

function copy_threadclasses($threadtypes, $fid) {
	global $_G;
	if($threadtypes) {
		$threadtypes = dunserialize($threadtypes);
		$i = 0;
		$data = array();
		foreach($threadtypes['types'] as $key => $val) {
			$data = array('fid' => $fid, 'name' => $val, 'displayorder' => $i++, 'icon' => $threadtypes['icons'][$key], 'moderators' => $threadtypes['moderators'][$key]);
			$newtypeid = C::t('forum_threadclass')->insert($data, true);
			$newtypes[$newtypeid] = $val;
			$newicons[$newtypeid] = $threadtypes['icons'][$key];
			$newmoderators[$newtypeid] = $threadtypes['moderators'][$key];
		}
		$threadtypes['types'] = $newtypes;
		$threadtypes['icons'] = $newicons;
		$threadtypes['moderators'] = $newmoderators;
		return serialize($threadtypes);
	}
	return '';
}
?>