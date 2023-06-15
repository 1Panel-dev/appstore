<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_group.php 34651 2014-06-18 08:27:31Z hypowang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(empty($_G['setting']['groupstatus'])) {
	cpmsg('group_status_off', 'action=setting&operation=functions', 'error');
}

if($operation == 'setting') {
	$setting = &$_G['setting'];
	if(!($group_creditspolicy = dunserialize($setting['group_creditspolicy']))) {
		$group_creditspolicy = array();
	}
	if(!($group_admingroupids = dunserialize($setting['group_admingroupids']))) {
		$group_admingroupids = array();
	}
	$setting['group_recommend'] = $setting['group_recommend'] ? implode(',', array_keys(dunserialize($setting['group_recommend']))) : '';
	if(!($group_postpolicy = dunserialize($setting['group_postpolicy']))) {
		$group_postpolicy = array();
	}
	if($group_postpolicy['autoclose']) {
		$group_postpolicy['autoclosetime'] = abs($group_postpolicy['autoclose']);
		$group_postpolicy['autoclose'] = $group_postpolicy['autoclose'] / abs($group_postpolicy['autoclose']);
	}
	if(!submitcheck('updategroupsetting')) {
		shownav('group', 'nav_group_setting');
		showsubmenu('nav_group_setting');
		showformheader('group&operation=setting');
		showtableheader();
		showtitle('groups_setting_basic');
		showsetting('groups_setting_basic_mod', 'settingnew[groupmod]', $setting['groupmod'], 'radio');
		showsetting('groups_setting_basic_iconsize', 'settingnew[group_imgsizelimit]', $setting['group_imgsizelimit'], 'text');
		showsetting('groups_setting_basic_recommend', 'settingnew[group_recommend]', $setting['group_recommend'], 'text');
		showtitle('groups_setting_admingroup');
		$varname = array('newgroup_admingroupids', array(), 'isfloat');
		$query = C::t('common_usergroup')->fetch_all_by_radminid(array(1, 2), '=', 'groupid');
		foreach($query as $ugroup) {
			$varname[1][] = array($ugroup['groupid'], $ugroup['grouptitle'], '1');
		}
		showsetting('', $varname, $group_admingroupids, 'omcheckbox');
		showsetting('forums_edit_posts_allowfeed', 'settingnew[group_allowfeed]', $setting['group_allowfeed'], 'radio');

		showsubmit('updategroupsetting');
		showtablefooter();
		showformfooter();
	} else {

		require_once libfile('function/group');
		$settings = array();
		$settings['group_recommend'] = cacherecommend($_GET['settingnew']['group_recommend']);
		require_once libfile('function/discuzcode');
		$skey_array = array('group_imgsizelimit','group_allowfeed', 'groupmod');
		foreach($_GET['settingnew'] as $skey => $svalue) {
			if(in_array($skey, $skey_array)){
				$settings[$skey] = intval($svalue);
			}
		}

		$settings['group_admingroupids'] = $_GET['newgroup_admingroupids'];
		$descriptionnew = preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', $_GET['descriptionnew']);
		$keywordsnew = $_GET['keywordsnew'];
		$settings['group_description'] = $descriptionnew;
		$settings['group_keywords'] = $keywordsnew;
		C::t('common_setting')->update_batch($settings);

		updatecache('setting');
		cpmsg('groups_setting_succeed', 'action=group&operation=setting', 'succeed');
	}
} elseif($operation == 'type') {
		shownav('group', 'nav_group_type');
		showsubmenu('nav_group_type');
	if(!submitcheck('editsubmit')) {
?>
<script type="text/JavaScript">
var rowtypedata = [
	[[1,'<input type="text" class="txt" name="newcatorder[]" value="0" />', 'td25'], [3, '<input name="newcat[]" value="<?php echo $lang[groups_type_level_1];?>" size="20" type="text" class="txt" /> <?php echo cplang('groups_type_show_rows');?><input type="text" name="newforumcolumns[]" value="0" class="txt" style="width: 30px;" />']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="board"><input name="newforum[{1}][]" value="<?php echo $lang[groups_type_sub_new];?>" size="20" type="text" class="txt" /><?php echo cplang('groups_type_show_rows');?><input type="text" name="newforumcolumns[{1}][]" value="0" class="txt" style="width: 30px;" /></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="childboard"><input name="newforum[{1}][]" value="<?php echo $lang[groups_type_sub_new];?>" size="20" type="text" class="txt" /><?php echo cplang('groups_type_show_rows');?><input type="text" name="newforumcolumns[{1}][]" value="0" class="txt" style="width: 30px;" /></div>']],
];
</script>
<?php
		showformheader('group&operation=type');
		showtableheader('');
		showsubtitle(array('display_order', 'groups_type_name', 'groups_type_count', 'groups_type_operation'));

		$forums = $showedforums = array();
		$query = C::t('forum_forum')->fetch_all_group_type();
		$groups = $forums = $subs = $fids = $showed = array();
		foreach($query as $forum) {
			if($forum['type'] == 'group') {
				$groups[$forum['fid']] = $forum;
			} else {
				$forums[$forum['fup']][] = $forum;
			}
			$fids[] = $forum['fid'];
		}

		foreach ($groups as $id => $gforum) {
			$showed[] = showgroup($gforum, 'group');
			if(!empty($forums[$id])) {
				foreach ($forums[$id] as $forum) {
					$showed[] = showgroup($forum);
					$lastfid = 0;
					if(!empty($subs[$forum['fid']])) {
						foreach ($subs[$forum['fid']] as $sub) {
							$showed[] = showgroup($sub, 'sub');
							$lastfid = $sub['fid'];
						}
					}
					showgroup($forum, $lastfid, 'lastchildboard');
				}
			}
			showgroup($gforum, '', 'lastboard');
		}

		if(count($fids) != count($showed)) {
			foreach($fids as $fid) {
				if(!in_array($fid, $showed)) {
					C::t('forum_forum')->update($fid, array('fup' => '0', 'type' => 'forum'));
				}
			}
		}

		showgroup($gforum, '', 'last');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$order = $_GET['order'];
		$name = $_GET['name'];
		$newforum = $_GET['newforum'];
		$newcat = $_GET['newcat'];
		$neworder = $_GET['neworder'];
		$newforumcolumns = $_GET['newforumcolumns'];
		$forumcolumnsnew = $_GET['forumcolumnsnew'];
		if(is_array($order)) {
			foreach($order as $fid => $value) {
				if(empty($name[$fid])) {
					continue;
				}
				C::t('forum_forum')->update($fid, array('name'=>$name[$fid], 'displayorder'=>$order[$fid], 'forumcolumns'=>$forumcolumnsnew[$fid]));
			}
		}

		if(is_array($newcat)) {
			foreach($newcat as $key => $forumname) {
				if(empty($forumname)) {
					continue;
				}
				$fid = C::t('forum_forum')->insert(array('type' => 'group', 'name' => $forumname, 'status' => 3, 'displayorder' => $newcatorder[$key], 'forumcolumns' => $newforumcolumns[$key]), 1);
				C::t('forum_forumfield')->insert(array('fid' => $fid));
			}
		}

		$table_forum_columns = array('fup', 'type', 'name', 'status', 'displayorder', 'styleid', 'allowsmilies', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowpostspecial', 'alloweditrules', 'alloweditpost', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'autoclose', 'simple');
		$table_forumfield_columns = array('fid', 'attachextensions', 'threadtypes', 'creditspolicy', 'viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');
		$projectdata = array();

		if(is_array($newforum)) {
			foreach($newforum as $fup => $forums) {
				$forum = C::t('forum_forum')->fetch($fup);
				foreach($forums as $key => $forumname) {
					if(empty($forumname)) {
						continue;
					}
					$forumfields = array();

					$forumfields['allowsmilies'] = $forumfields['allowbbcode'] = $forumfields['allowimgcode'] = 1;
					$forumfields['allowpostspecial'] = 127;


					$forumfields['fup'] = $forum ? $fup : 0;
					$forumfields['type'] = 'forum';
					$forumfields['name'] = $forumname;
					$forumfields['status'] = 3;
					$forumfields['displayorder'] = $neworder[$fup][$key];
					$forumfields['forumcolumns'] = $newforumcolumns[$fup][$key];

					$data = array();
					foreach($table_forum_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					$forumfields['fid'] = $fid = C::t('forum_forum')->insert($data, 1);

					$data = array();
					foreach($table_forumfield_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}
					C::t('forum_forumfield')->insert($data);
				}
			}
		}
		updatecache('grouptype');
		cpmsg('group_update_succeed', 'action=group&operation=type', 'succeed');
	}
} elseif($operation == 'manage') {
	if(!$_GET['mtype']) {
		if(!submitcheck('submit', 1)) {

			shownav('group', 'nav_group_manage');
			showsubmenu('nav_group_manage');
			searchgroups($_GET['submit']);

		} else {
			list($page, $start_limit, $groupnum, $conditions, $urladd) = countgroups();
			$multipage = multi($groupnum, $_G['setting']['group_perpage'], $page, ADMINSCRIPT."?action=group&operation=manage&submit=yes".$urladd);
			$query  = C::t('forum_forum')->fetch_all_for_search($conditions, $start_limit, $_G['setting']['group_perpage']);
			foreach($query as $group) {
				$groups .= showtablerow('', array('class="td25"', '', ''), array(
					"<input type=\"checkbox\" name=\"fidarray[]\" value=\"$group[fid]\" class=\"checkbox\">",
					"<span class=\"lightfont right\">(fid:$group[fid])</span><a href=\"forum.php?mod=forumdisplay&fid=$group[fid]\" target=\"_blank\">$group[name]</a>",
					$group['posts'],
					$group['threads'],
					$group['membernum'],
					"<a href=\"home.php?mod=space&uid=$group[founderuid]\" target=\"_blank\">$group[foundername]</a>",
					"<a href=\"".ADMINSCRIPT."?action=group&operation=editgroup&fid=$group[fid]\" class=\"act\">".cplang('detail')."</a>"
				), TRUE);
			}

			shownav('group', 'nav_group_manage');
			showsubmenu('nav_group_manage');
			showformheader("group&operation=manage&mtype=managetype");
			showtableheader(cplang('groups_search_result', array('groupnum' => $groupnum)).' <a href="javascript:history.go(-1);" class="act lightlink normal">'.cplang('research').'</a>');
			showsubtitle(array('', 'groups_manage_name', 'groups_manage_postcount', 'groups_manage_threadcount', 'groups_manage_membercount', 'groups_manage_founder', ''));
			echo $groups;
			showtablerow('', array('class="td25"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'fidarray\')" /><label for="chkall">'.cplang('select_all').'</label>'));
			showtablefooter();
			showtableheader('operation', 'notop');
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" value="delete" >',
				cplang('founder_perm_group_deletegroup'), cplang('founder_perm_group_deletegroupcomments')));
			require_once libfile('function/group');
			$groupselect = get_groupselect(0, $group['fup'], 0);
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" value="changetype" >',
				cplang('group_changetype'),
				'<select name="newtypeid"><option value="">'.cplang('group_mergetype_selecttype').'</option>'.$groupselect.'</select>'));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" value="mergegroup" >',
				cplang('group_mergegroup'),
				'<input type="text" name="targetgroup" class="text" value="">&nbsp;&nbsp;'.cplang('groups_mergegroup_id')
			));
			showsubmit('submit', 'submit', '', '', $multipage);
			showtablefooter();
			showformfooter();

		}
	} elseif($_GET['mtype'] == 'managetype') {
		$fidarray = $_GET['fidarray'];
		$optype = $_GET['optype'];
		$newtypeid = intval($_GET['newtypeid']);
		$targetgroup = intval($_GET['targetgroup']);
		if(submitcheck('confirmed', 1)){
			$fidarray = explode(',', $fidarray);
			$recommend = $_G['setting']['group_recommend'] ? array_keys(dunserialize($_G['setting']['group_recommend'])) : array();
			$fidstr = $_G['setting']['group_recommend'] ? implode(',', $recommend) : '';
			$updaterecommend = false;
			foreach($fidarray as $fid) {
				if(in_array($fid, $recommend)) {
					$updaterecommend = true;
					break;
				}
			}
			if($optype == 'delete') {
				delete_groupimg($fidarray);
				require_once libfile('function/post');
				$tids = $nums = array();
				$pp = 100;
				$start = intval($_GET['start']);
				$query = C::t('forum_forum')->fetch_all_info_by_fids($fidarray);
				foreach($query as $fup) {
					$nums[$fup['fup']] ++;
				}
				foreach($nums as $fup => $num) {
					empty($start) && C::t('forum_forumfield')->update_groupnum($fup, -$num);
				}
				foreach(C::t('forum_thread')->fetch_all_by_fid($fidarray, $start, $pp) as $thread) {
					$tids[] = $thread['tid'];
				}
				require_once libfile('function/delete');
				if($tids) {
					deletepost($tids, 'tid');
					deletethread($tids);
					cpmsg('group_thread_removing', 'action=group&operation=manage&mtype=managetype&optype=delete&submit=yes&confirmed=yes&fidarray='.$_GET['fidarray'].'&start='.($start + $pp));
				}
				loadcache('posttable_info');
				if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
					foreach($_G['cache']['posttable_info'] as $key => $value) {
						C::t('forum_post')->delete_by_fid($key, $fidarray, true);
					}
				}
				loadcache('threadtableids');
				$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array('0');
				foreach($threadtableids as $tableid) {
					C::t('forum_thread')->delete_by_fid($fidarray, true, $tableid);
				}
				C::t('forum_forumrecommend')->delete_by_fid($fidarray);
				C::t('forum_forumrecommend')->delete_by_fid($fidarray);
				C::t('forum_forum')->delete_by_fid($fidarray);
				C::t('home_favorite')->delete_by_id_idtype($fidarray, 'gid');
				C::t('forum_groupuser')->delete_by_fid($fidarray);
				C::t('forum_groupcreditslog')->delete_by_fid($fidarray);
				C::t('forum_groupfield')->delete($fidarray);


				require_once libfile('function/delete');
				deletedomain($fidarray, 'group');
				if($updaterecommend) {
					cacherecommend($fidstr, false);
				}
				updatecache(array('setting', 'grouptype'));
				cpmsg('group_delete_succeed', 'action=group&operation=manage', 'succeed');
			} elseif($optype == 'changetype') {
				$fups = array();
				$query = C::t('forum_forum')->fetch_all_info_by_fids($fidarray);
				foreach($query as $fup) {
					$fups[$fup['fup']] ++;
				}
				C::t('forum_forum')->update($fidarray, array('fup' => $newtypeid));
				C::t('forum_forumfield')->update_groupnum($newtypeid, count($fidarray));
				foreach($fups as $fup => $num) {
					C::t('forum_forumfield')->update_groupnum($fup, -$num);
				}
				updatecache('grouptype');
				cpmsg('group_changetype_succeed', 'action=group&operation=manage', 'succeed');

			} elseif($optype == 'mergegroup') {
				$start = intval($_GET['start']) ? $_GET['start'] : 0;
				$threadtables = array('0');
				foreach(C::t('forum_forum_threadtable')->fetch_all_by_fid($targetgroup) as $data) {
					$threadtables[] = $data['threadtableid'];
				}

				if($fidarray[$start]) {
					$sourcefid = $fidarray[$start];
					if(empty($start)) {
						$nums = array();
						$query = C::t('forum_forum')->fetch_all_info_by_fids($fidarray);
						foreach($query as $fup) {
							$nums[$fup['fup']] ++;
						}
						foreach($nums as $fup => $num) {
							C::t('forum_forumfield')->update_groupnum($fup, -$num);
						}
					}
					foreach($threadtables as $tableid) {
						C::t('forum_thread')->update_by_fid($sourcefid, array('fid'=>$targetgroup), $tableid);
					}
					loadcache('posttableids');
					$posttableids = $_G['cache']['posttableids'] ? $_G['cache']['posttableids'] : array('0');
					foreach($posttableids as $id) {
						C::t('forum_post')->update_fid_by_fid($id, $sourcefid, $targetgroup);
					}

					$targetusers = $newgroupusers = array();
					$query = C::t('forum_groupuser')->fetch_all_by_fid($targetgroup, -1);
					foreach($query as $row) {
						$targetusers[$row['uid']] = $row['uid'];
					}
					$adduser = 0;
					$query = C::t('forum_groupuser')->fetch_all_by_fid($sourcefid, -1);
					foreach($query as $row) {
						if(empty($targetusers[$row['uid']])) {
							$newgroupusers[$row[uid]] = daddslashes($row['username']);
							$adduser ++;
						}
					}
					if($adduser) {
						foreach($newgroupusers as $newuid => $newusername) {
							C::t('forum_groupuser')->insert($targetgroup, $newuid, $newusername, 4, TIMESTAMP);
						}
						C::t('forum_forumfield')->update_membernum($targetgroup, $adduser);
					}
					C::t('forum_groupuser')->delete_by_fid($sourcefid);
					C::t('forum_groupcreditslog')->delete_by_fid($sourcefid);
					C::t('forum_groupfield')->delete($sourcefid);
					$start ++;
					cpmsg('group_merge_continue', 'action=group&operation=manage&mtype=managetype&optype='.$optype.'&submit=yes&confirmed=yes&targetgroup='.$targetgroup.'&fidarray='.$_GET['fidarray'].'&start='.$start, '', array('m' => $start, 'n' => count($fidarray)-$start));
				}
				$threads = $posts = 0;
				$archive = 0;
				foreach($threadtables as $tableid) {
					$data = C::t('forum_thread')->count_posts_by_fid($targetgroup, $tableid);
					$threads += $data['threads'];
					$posts += $data['posts'];
					if($data['threads'] > 0 && $tableid != 0) {
						$archive = 1;
					}
				}
				C::t('forum_forum')->update($targetgroup, array('archive' => $archive));
				C::t('forum_forum')->update_forum_counter($targetgroup, $threads, $posts);

				delete_groupimg($fidarray);
				C::t('forum_forum')->delete_by_fid($fidarray);
				C::t('home_favorite')->delete_by_id_idtype($fidarray, 'gid');
				require_once libfile('function/delete');
				deletedomain($fidarray, 'group');
				if($updaterecommend) {
					cacherecommend($fidstr, false);
				}
				updatecache(array('setting', 'grouptype'));
				cpmsg('group_mergegroup_succeed', 'action=group&operation=manage', 'succeed');
			}

		}
		if(empty($optype) || !in_array($optype, array('delete', 'changetype', 'mergegroup'))) {
			cpmsg('group_optype_no_choice', '', 'error');
		}
		if($optype == 'changetype' && empty($newtypeid)) {
			cpmsg('group_newtypeid_no_choice', '', 'error');
		}
		if($optype == 'mergegroup' && empty($targetgroup)) {
			cpmsg('group_targetgroup_no_choice', '', 'error');
		}
		if($fidarray) {
			$targetid = 0;
			$targetname = '';
			if($optype == 'changetype' && $newtypeid) {
				$targetid = $newtypeid;
			} elseif($optype == 'mergegroup' && $targetgroup) {
				if(in_array($targetgroup, $fidarray)) {
					cpmsg('group_targetgroup_repeat', '', 'error');
				}
				$targetid = $targetgroup;
			}
			if($targetid) {
				$targetname = C::t('forum_forum')->fetch($targetid);
				$targetname = $targetname['name'];
				if(empty($targetname)) {
					cpmsg('group_targetid_error');
				}
			}
			if(is_array($fidarray)) {
				$fidarray = implode(',', $fidarray);
			}
			cpmsg('group_'.$optype.'_confirm', 'action=group&operation=manage&mtype=managetype&optype='.$optype.'&submit=yes', 'form', array('targetname' => $targetname), '<input type="hidden" name="fidarray" value="'.$fidarray.'"><input type="hidden" name="newtypeid" value="'.$newtypeid.'"><input type="hidden" name="targetgroup" value="'.$targetgroup.'">');
		} else {
			cpmsg('group_group_no_choice', '', 'error');
		}
	}
} elseif($operation == 'deletetype') {
	$fid = $_GET['fid'];
	$ajax = $_GET['ajax'];
	$confirmed = $_GET['confirmed'];
	$finished = $_GET['finished'];
	$total = intval($_GET['total']);
	$pp = intval($_GET['pp']);
	$currow = intval($_GET['currow']);
	if($ajax && $_GET['formhash'] == formhash()) {
		ob_end_clean();
		require_once libfile('function/post');
		$tids = array();
		foreach(C::t('forum_thread')->fetch_all_by_fid($fid, $pp) as $thread) {
			$tids[] = $thread['tid'];
		}
		require_once libfile('function/delete');
		deletethread($tids);

		if($currow + $pp > $total) {
			C::t('forum_forum')->delete_by_fid($fid);
			C::t('home_favorite')->delete_by_id_idtype($fid, 'gid');
			C::t('forum_moderator')->delete_by_fid($fid);
			C::t('forum_access')->delete_by_fid($fid);

			echo 'TRUE';
			exit;
		}

		echo 'GO';
		exit;

	} else {
		if($finished) {
			updatecache('grouptype');
			cpmsg('grouptype_delete_succeed', 'action=group&operation=type', 'succeed');

		}

		if(C::t('forum_forum')->fetch_forum_num('group', $fid)) {
			cpmsg('grouptype_delete_sub_notnull', '', 'error');
		}

		if(!$confirmed) {

			cpmsg('grouptype_delete_confirm', "action=group&operation=deletetype&fid=$fid", 'form');

		} else {

			$threads = C::t('forum_thread')->count_by_fid($fid);
			$formhash = formhash();
			cpmsg('grouptype_delete_alarm', "action=group&operation=deletetype&fid=$fid&confirmed=1&formhash=$formhash", 'loadingform', array(), '<div id="percent">0%</div>', FALSE);
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
							location.href = adminfilename + '?action=group&operation=deletetype&finished=1';
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
				forumsdelete(adminfilename + '?action=group&operation=deletetype&fid=$fid&confirmed=1&formhash=$formhash', $threads, 2000, 0);
			</script>
			";
		}
	}
} elseif($operation == 'editgroup') {
	require_once libfile('function/group');
	$fid = intval($_GET['fid']);
	if(empty($fid)) {
		cpmsg('group_nonexist', 'action=group&operation=manage', 'error');
	}
	$group = C::t('forum_forum')->fetch_info_by_fid($fid);
	require_once libfile('function/editor');
	$group['description'] = html2bbcode($group['description']);

	if(!$group || $group['status'] != 3 || $group['type'] != 'sub') {
		cpmsg('group_nonexist', '', 'error');
	}

	require_once libfile('function/group');
	require_once libfile('function/discuzcode');
	$groupicon = get_groupimg($group['icon'], 'icon');
	$groupbanner = get_groupimg($group['banner']);
	$jointypeselect = array(array('-1', cplang('closed')), array('0', cplang('public')), array('1', cplang('invite')), array('2', cplang('moderate')));
	if(!submitcheck('editsubmit')) {
		$groupselect = get_groupselect(0, $group['fup'], 0);
		shownav('group', 'nav_group_manage');
		showsubmenu('nav_group_manage');
		showformheader("group&operation=editgroup&fid=$fid", 'enctype');
		showtableheader();
		showsetting('groups_editgroup_name', 'namenew', $group['name'], 'text');
		showsetting('groups_editgroup_category', '', '', '<select name="fupnew">'.$groupselect.'</select>');
		showsetting('groups_editgroup_jointype', array('jointypenew', $jointypeselect), $group['jointype'], 'select');
		showsetting('groups_editgroup_visible_all', 'gviewpermnew', $group['gviewperm'], 'radio');
		showsetting('groups_editgroup_description', 'descriptionnew', $group['description'], 'textarea');
		if($groupicon) {
			$groupicon = '<input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'<br /><img src="'.$groupicon.'?'.random(6).'" width="48" height="48" />';
		}
		if($groupbanner) {
			$groupbanner = '<input type="checkbox" class="checkbox" name="deletebanner" value="yes" /> '.$lang['delete'].'<br /><img src="'.$groupbanner.'?'.random(6).'" />';
		}
		showsetting('groups_editgroup_icon', 'iconnew', '', 'file', '', 0, $groupicon);
		showsetting('groups_editgroup_banner', 'bannernew', '', 'file', '', 0, $groupbanner);
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$_GET['jointypenew'] = intval($_GET['jointypenew']);
		$_GET['fupnew'] = intval($_GET['fupnew']);
		$_GET['gviewpermnew'] = intval($_GET['gviewpermnew']);
		require_once libfile('function/discuzcode');
		$_GET['descriptionnew'] = discuzcode(dhtmlspecialchars(censor(trim($_GET['descriptionnew']))), 0, 0, 0, 0, 1, 1, 0, 0, 1);
		$_GET['namenew'] = dhtmlspecialchars(censor(trim($_GET['namenew'])));
		$icondata = array();
		$iconnew = upload_icon_banner($group, $_FILES['iconnew'], 'icon');
		$bannernew = upload_icon_banner($group, $_FILES['bannernew'], 'banner');
		if($iconnew) {
			$icondata['icon'] = $iconnew;
		}
		if($bannernew) {
			$icondata['banner'] = $bannernew;
		};

		if($_GET['deleteicon']) {
			@unlink($_G['setting']['attachurl'].'group/'.$group['icon']);
			$icondata['icon'] = '';
		}
		if($_GET['deletebanner']) {
			@unlink($_G['setting']['attachurl'].'group/'.$group['banner']);
			$icondata['banner'] = '';
		}
		$groupdata = array_merge($icondata, array(
			'description' => $_GET['descriptionnew'],
			'gviewperm' => $_GET['gviewpermnew'],
			'jointype' => $_GET['jointypenew'],
		));
		C::t('forum_forumfield')->update($fid, $groupdata);
		$setarr = array();
		if($_GET['fupnew']) {
			$setarr['fup'] = $_GET['fupnew'];
		}
		if($_GET['namenew'] && $_GET['namenew'] != $group['name'] && C::t('forum_forum')->fetch_fid_by_name($_GET['namenew'])) {
			cpmsg('group_name_exist', 'action=group&operation=editgroup&fid='.$fid, 'error');
		}
		trim($_GET['namenew']) && $setarr['name'] = $_GET['namenew'];
		C::t('forum_forum')->update($fid, $setarr);

		if(!empty($_GET['fupnew']) && $_GET['fupnew'] != $group['fup']) {
			C::t('forum_forumfield')->update_groupnum($_GET['fupnew'], 1);
			C::t('forum_forumfield')->update_groupnum($group['fup'], -1);
			require_once libfile('function/cache');
			updatecache('grouptype');
		}

		cpmsg('group_edit_succeed', 'action=group&operation=editgroup&fid='.$fid, 'succeed');
	}

} elseif($operation == 'userperm') {
	if(!($group_userperm = dunserialize($_G['setting']['group_userperm']))) {
		$group_userperm = array();
	}
	if(!submitcheck('permsubmit')) {
		shownav('group', 'nav_group_userperm');
		$varname = array('newgroup_userperm', array(), 'isfloat');
		showsubmenu(cplang('nav_group_userperm').' - '.cplang('group_userperm_moderator'));
		showformheader("group&operation=userperm&id=$id");
		showtableheader();
		$varname[1] = array(
		 	array('allowstickthread', cplang('admingroup_edit_stick_thread'), '1'),
		 	array('allowbumpthread', cplang('admingroup_edit_bump_thread'), '1'),
		 	array('allowhighlightthread', cplang('admingroup_edit_highlight_thread'), '1'),
			array('allowlivethread', cplang('admingroup_edit_live_thread'), '1'),
		 	array('allowstampthread', cplang('admingroup_edit_stamp_thread'), '1'),
		 	array('allowrepairthread', cplang('admingroup_edit_repair_thread'), '1'),
		 	array('allowrefund', cplang('admingroup_edit_refund'), '1'),
		 	array('alloweditpoll', cplang('admingroup_edit_edit_poll'), '1'),
		 	array('allowremovereward', cplang('admingroup_edit_remove_reward'), '1'),
		 	array('alloweditactivity', cplang('admingroup_edit_edit_activity'), '1'),
		 	array('allowedittrade', cplang('admingroup_edit_edit_trade'), '1'),
		 );
		showtitle('admingroup_edit_threadperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		showsetting('admingroup_edit_digest_thread', array('newgroup_userperm[allowdigestthread]', array(
			array(0, cplang('admingroup_edit_digest_thread_none')),
			array(1, cplang('admingroup_edit_digest_thread_1')),
			array(2, cplang('admingroup_edit_digest_thread_2')),
			array(3, cplang('admingroup_edit_digest_thread_3')),
		)), $group_userperm['allowdigestthread'], 'mradio');

		$varname[1] = array(
		 	array('alloweditpost', cplang('admingroup_edit_edit_post'), '1'),
		 	array('allowwarnpost', cplang('admingroup_edit_warn_post'), '1'),
		 	array('allowbanpost', cplang('admingroup_edit_ban_post'), '1'),
		 	array('allowdelpost', cplang('admingroup_edit_del_post'), '1'),
		 );
		showtitle('admingroup_edit_postperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		$varname[1] = array(
		 	array('allowupbanner', cplang('group_userperm_upload_banner'), '1'),
		 );
		showtitle('admingroup_edit_modcpperm');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		$varname[1] = array(
		 	array('disablepostctrl', cplang('admingroup_edit_disable_postctrl'), '1'),
		 	array('allowviewip', cplang('admingroup_edit_view_ip'), '1')
		 );
		showtitle('group_userperm_others');
		showsetting('', $varname, $group_userperm, 'omcheckbox');

		showtablefooter();
		echo '</td></tr>';
		showtagfooter('tbody');
		showsubmit('permsubmit', 'submit');
		showtablefooter();
		showformfooter();
	} else {
		$default_perm = array('allowstickthread' => 0, 'allowbumpthread' => 0, 'allowhighlightthread' => 0, 'allowlivethread' => 0, 'allowstampthread' => 0, 'allowclosethread' => 0, 'allowmergethread' => 0, 'allowsplitthread' => 0, 'allowrepairthread' => 0, 'allowrefund' => 0, 'alloweditpoll' => 0, 'allowremovereward' => 0, 'alloweditactivity' => 0, 'allowedittrade' => 0, 'allowdigestthread' => 0, 'alloweditpost' => 0, 'allowwarnpost' => 0, 'allowbanpost' => 0, 'allowdelpost' => 0, 'allowupbanner' => 0, 'disablepostctrl' => 0, 'allowviewip' => 0);
		$_GET['newgroup_userperm'] = array_merge($default_perm, $_GET['newgroup_userperm']);
		if(serialize($_GET['newgroup_userperm']) != serialize($group_userperm)) {
			C::t('common_setting')->update('group_userperm', $_GET['newgroup_userperm']);
			updatecache('setting');
		}
		cpmsg('group_userperm_succeed', 'action=group&operation=userperm', 'succeed');
	}
} elseif($operation == 'level') {
	$levelid = !empty($_GET['levelid']) ? intval($_GET['levelid']) : 0;
	if(empty($levelid)) {
		$grouplevels = '';
		if(!submitcheck('grouplevelsubmit')) {
			$query = C::t('forum_grouplevel')->fetch_all_creditslower_order();
			foreach($query as $level) {
				$grouplevels .= showtablerow('', array('class="td25"', '', 'class="td28"', 'class=td28'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$level[levelid]]\" value=\"$level[levelid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"levelnew[$level[levelid]][leveltitle]\" value=\"$level[leveltitle]\">",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"levelnew[$level[levelid]][creditshigher]\" value=\"$level[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"levelnew[$level[levelid]][creditslower]\" value=\"$level[creditslower]\" disabled />",
					"<a href=\"".ADMINSCRIPT."?action=group&operation=level&levelid=$level[levelid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="levelnewadd[leveltitle][]">'],
		[1,'<input type="text" class="txt" size="6" name="levelnewadd[creditshigher][]">', 'td28'],
		[4,'']
	],
	[
		[1,'', 'td25'],
		[1,'<input type="text" class="txt" size="12" name="leveltitlenewadd[]">'],
		[1,'<input type="text" class="txt" size="2" name="creditshighernewadd[]">', 'td28'],
		[4, '']
	]
];
</script>
EOT;
			shownav('group', 'nav_group_level');
			showsubmenu('nav_group_level');
			showtips('group_level_tips');

			showformheader('group&operation=level');
			showtableheader('group_level', 'fixpadding', 'id="grouplevel"');
			showsubtitle(array('del', 'group_level_title', 'group_level_creditsrange', ''));
			echo $grouplevels;
			echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['group_level_add'].'</a></div></td></tr>';
			showsubmit('grouplevelsubmit', 'submit');
			showtablefooter();
			showformfooter();
		} else {
			$levelnewadd = $levelnewkeys = $orderarray = array();
			$maxlevelid = 0;
			if(!empty($_GET['levelnewadd'])) {
				$levelnewadd = array_flip_keys($_GET['levelnewadd']);
				foreach($levelnewadd as $k => $v) {
					if(!$v['leveltitle'] || !$v['creditshigher']) {
						unset($levelnewadd[$k]);
					}
				}
			}
			if(!empty($_GET['levelnew'])) {
				$levelnewkeys = array_keys($_GET['levelnew']);
				$maxlevelid = max($levelnewkeys);
			}

			foreach($levelnewadd as $k=>$v) {
				$_GET['levelnew'][$k+$maxlevelid+1] = $v;
			}
			if(is_array($_GET['levelnew'])) {
				foreach($_GET['levelnew'] as $id => $level) {
					if((is_array($_GET['delete']) && in_array($id, $_GET['delete'])) || ($id == 0 && (!$level['grouptitle'] || $level['creditshigher'] == ''))) {
						unset($_GET['levelnew'][$id]);
					} else {
						$orderarray[$level['creditshigher']] = $id;
					}
				}
			}
			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array
					(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
					);
			}
			foreach($_GET['levelnew'] as $id => $level) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('group_level_update_credits_duplicate', '', 'error');
				}
				$data = array(
					'leveltitle' => $level['leveltitle'],
					'creditshigher' => $creditshighernew,
					'creditslower' => $creditslowernew,
				);
				if(in_array($id, $levelnewkeys)) {
					C::t('forum_grouplevel')->update($id, $data);
				} elseif($level['leveltitle'] && $level['creditshigher'] != '') {
					$data = array(
						'leveltitle' => $level['leveltitle'],
						'type' => 'default',
						'creditshigher' => $creditshighernew,
						'creditslower' => $creditslowernew,
					);
					$data['type'] = 'default';
					$newlevelid = C::t('forum_grouplevel')->insert($data, 1);
				}
			}
			if($ids = dimplode($_GET['delete'])) {
				$levelcount = C::t('forum_grouplevel')->fetch_count();
				if(count($_GET['delete']) == $levelcount) {
					updatecache('grouplevels');
					cpmsg('group_level_succeed_except_all_levels', 'action=group&operation=level', 'succeed');

				}
				C::t('forum_grouplevel')->delete($ids);
			}
			updatecache('grouplevels');
			cpmsg('group_level_update_succeed', 'action=group&operation=level', 'succeed');
		}
	} else {
		$grouplevel = C::t('forum_grouplevel')->fetch($levelid);
		if(empty($grouplevel)) {
			cpmsg('group_level_noexist', 'action=group&operation=level', 'error');
		}
		if(!($group_creditspolicy = dunserialize($grouplevel['creditspolicy']))) {
			$group_creditspolicy = array();
		}
		if(!($group_postpolicy = dunserialize($grouplevel['postpolicy']))) {
			$group_postpolicy = array();
		}
		if(!($specialswitch = dunserialize($grouplevel['specialswitch']))) {
			$specialswitch = array();
		}
		if(!submitcheck('editgrouplevel')) {
			shownav('group', 'nav_group_level');
			showsubmenu('nav_group_level_editor');
			showtips('group_level_tips');

			showformheader('group&operation=level&levelid='.$levelid, 'enctype');
			showtableheader();
			showtitle('groups_setting_basic');
			showsetting('group_level_title', 'levelnew[leveltitle]', $grouplevel['leveltitle'], 'text');
			if($grouplevel['icon']) {
				$valueparse = parse_url($grouplevel['icon']);
				if(isset($valueparse['host'])) {
					$grouplevelicon = $grouplevel['icon'];
				} else {
					$grouplevelicon = $_G['setting']['attachurl'].'common/'.$grouplevel['icon'].'?'.random(6);
				}
				$groupleveliconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon[{$grouplevel[levelid]}]" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$grouplevelicon.'" />';
			}
			showsetting('group_level_icon', 'iconnew', $grouplevel['icon'], 'filetext', '', 0, $groupleveliconhtml);

			showtitle('group_level_credits');
			$varname = array('levelnew[creditspolicy]', array(), 'isfloat');
			$varname[1] = array(
			 	array('post', cplang('group_level_credits_post'), '1'),
			 	array('reply', cplang('group_level_credits_reply'), '1'),
			 	array('digest', cplang('group_level_credits_digest'), '1'),
			 	array('postattach', cplang('group_level_credits_upload'), '1'),
			 	array('getattach', cplang('group_level_credits_download'), '1'),
			 	array('tradefinished', cplang('group_level_credits_trade'), '1'),
			 	array('joinpoll', cplang('group_level_credits_poll'), '1'),
			 );
			showsetting('', $varname, $group_creditspolicy, 'omcheckbox');
			showtitle('group_level_posts');
			$varname = array('levelnew[postpolicy]', array(), 'isfloat');
			$varname[1] = array(
			 	array('alloweditpost', cplang('forums_edit_posts_alloweditpost'), '1'),
			 	array('recyclebin', cplang('forums_edit_posts_recyclebin'), '1'),
			 	array('allowsmilies', cplang('forums_edit_posts_smilies'), '1'),
			 	array('allowhtml', cplang('forums_edit_posts_html'), '1'),
			 	array('allowbbcode', cplang('forums_edit_posts_bbcode'), '1'),
			 	array('allowanonymous', cplang('forums_edit_posts_anonymous'), '1'),
			 	array('jammer', cplang('forums_edit_posts_jammer'), '1'),
			 	array('allowimgcode', cplang('forums_edit_posts_imgcode'), '1'),
			 	array('allowmediacode', cplang('forums_edit_posts_mediacode'), '1'),
			 );
			showsetting('', $varname, $group_postpolicy, 'omcheckbox');

			showsetting('forums_edit_posts_allowpostspecial', array('levelnew[postpolicy][allowpostspecial]', array(
				cplang('thread_poll'),
				cplang('thread_trade'),
				cplang('thread_reward'),
				cplang('thread_activity'),
				cplang('thread_debate')
			)), $group_postpolicy['allowpostspecial'], 'binmcheckbox');
			showsetting('forums_edit_posts_attach_ext', 'levelnew[postpolicy][attachextensions]', $group_postpolicy['attachextensions'], 'text');

			showtitle('group_level_special');
			showsetting('group_level_special_allowchangename', 'specialswitchnew[allowchangename]', $specialswitch['allowchangename'], 'radio');
			showsetting('group_level_special_allowchangetype', 'specialswitchnew[allowchangetype]', $specialswitch['allowchangetype'], 'radio');
			showsetting('group_level_special_allowclose', 'specialswitchnew[allowclosegroup]', $specialswitch['allowclosegroup'], 'radio');
			showsetting('group_level_special_allowthreadtype', 'specialswitchnew[allowthreadtype]', $specialswitch['allowthreadtype'], 'radio');
			showsetting('group_level_special_membermax', 'specialswitchnew[membermaximum]', $specialswitch['membermaximum'], 'text');

			showsubmit('editgrouplevel');
			showtablefooter();
			showformfooter();
		} else {
			$dataarr = array();
			$levelnew = $_GET['levelnew'];
			$dataarr['leveltitle'] = $levelnew['leveltitle'];
			$default_creditspolicy = array('post' => 0, 'reply' => 0, 'digest' => 0, 'postattach' => 0, 'getattach' => 0, 'tradefinished' => 0, 'joinpoll' => 0);
			$levelnew['creditspolicy'] = empty($levelnew['creditspolicy']) ? $default_creditspolicy : array_merge($default_creditspolicy, $levelnew['creditspolicy']);
			$dataarr['creditspolicy'] = serialize($levelnew['creditspolicy']);
			$default_postpolicy = array('alloweditpost' => 0, 'recyclebin' => 0, 'allowsmilies' => 0, 'allowhtml' => 0, 'allowbbcode' => 0, 'allowanonymous' => 0, 'jammer' => 0, 'allowimgcode' => 0, 'allowmediacode' => 0);
			$levelnew['postpolicy'] = array_merge($default_postpolicy, $levelnew['postpolicy']);

			$levelnew['postpolicy']['allowpostspecial'] = bindec(intval($levelnew['postpolicy']['allowpostspecial'][6]).intval($levelnew['postpolicy']['allowpostspecial'][5]).intval($levelnew['postpolicy']['allowpostspecial'][4]).intval($levelnew['postpolicy']['allowpostspecial'][3]).intval($levelnew['postpolicy']['allowpostspecial'][2]).intval($levelnew['postpolicy']['allowpostspecial'][1]));

			$dataarr['postpolicy'] = serialize($levelnew['postpolicy']);
			$_GET['specialswitchnew']['membermaximum'] = intval($_GET['specialswitchnew']['membermaximum']);
			$dataarr['specialswitch'] = serialize($_GET['specialswitchnew']);
			if($_GET['deleteicon']) {
				@unlink($_G['setting']['attachurl'].'common/'.$grouplevel['icon']);
				$dataarr['icon'] = '';
			} else {
				if($_FILES['iconnew']) {
					$data = array('extid' => "$levelid");
					$dataarr['icon'] = upload_icon_banner($data, $_FILES['iconnew'], 'grouplevel_icon');
				} else {
					$dataarr['icon'] = $_GET['iconnew'];
				}
			}
			C::t('forum_grouplevel')->update($levelid, $dataarr);
			updatecache('grouplevels');
			cpmsg('groups_setting_succeed', 'action=group&operation=level&levelid='.$levelid, 'succeed');
		}
	}
} elseif($operation == 'mergetype') {
	require_once libfile('function/group');
	loadcache('grouptype');
	$fid = $_GET['fid'];
	$sourcetype = C::t('forum_forum')->fetch_info_by_fid($fid);
	$firstgroup = $_G['cache']['grouptype']['first'];
	if($firstgroup[$fid]['secondlist']) {
		cpmsg('grouptype_delete_sub_notnull');
	}
	shownav('group', 'nav_group_type');
	showsubmenu(cplang('nav_group_type').' - '.cplang('group_mergetype').' - '.$sourcetype['name']);
	if(!submitcheck('mergesubmit', 1)) {
		$groupselect = get_groupselect(0, 0, 0);
		showformheader("group&operation=mergetype&fid=$fid", 'enctype');
		showtableheader();
		showsetting('group_mergetype_selecttype', '', '', '<select name="mergefid">'.$groupselect.'</select>');
		showsubmit('mergesubmit');
		showtablefooter();
		showformfooter();
	} else {
		$mergefid = $_GET['mergefid'];
		if(empty($_GET['confirm'])) {
			cpmsg('group_mergetype_confirm', 'action=group&operation=mergetype&fid='.$fid.'&mergesubmit=yes&confirm=1', 'form', array(), '<input type="hidden" name="mergefid" value="'.$mergefid.'">');
		}
		if($mergefid == $fid) {
			cpmsg('group_mergetype_target_error', 'action=group&operation=mergetype&fid='.$fid, 'error');
		}
		C::t('forum_forum')->update_fup_by_fup($fid, $mergefid);
		C::t('forum_forum')->delete_by_fid($fid);
		C::t('home_favorite')->delete_by_id_idtype($fid, 'gid');
		C::t('forum_forumfield')->update_groupnum($mergefid, $sourcetype['groupnum']);
		updatecache('grouptype');
		cpmsg('group_mergetype_succeed', 'action=group&operation=type');
	}
} elseif($operation == 'mod') {
	if(!empty($_GET['fidarray'])) {
		$groups = array();
		$query = C::t('forum_forum')->fetch_all_info_by_fids($_GET['fidarray']);
		foreach($query as $group) {
			$groups[$group[fid]] = $group;
			$fups[$group[fup]] ++;
		}
		if(submitcheck('validate')) {
			C::t('forum_forum')->validate_level_for_group($_GET['fidarray']);
			$updateforum = '';
			foreach($groups as $fid => $group) {
				notification_add($group['founderuid'], 'group', 'group_mod_check', array('fid' => $fid, 'groupname' => $group['name'], 'url' => $_G['siteurl'].'forum.php?mod=group&fid='.$fid), 1);
			}
		} elseif(submitcheck('delsubmit')) {
			C::t('forum_forum')->delete_by_fid($_GET['fidarray']);
			C::t('home_favorite')->delete_by_id_idtype($_GET['fidarray'], 'gid');
			C::t('forum_groupuser')->delete_by_fid($_GET['fidarray']);
			$updateforum = '-';
		}
		foreach($fups as $fid => $num) {
			$updateforum && C::t('forum_forumfield')->update_groupnum($fid, $updateforum.$num);
		}
		cpmsg('group_mod_succeed', 'action=group&operation=mod', 'succeed');
	}

	loadcache('grouptype');
	$perpage = 50;
	$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
	$startlimit = ($page - 1) * $perpage;
	$count = C::t('forum_forum')->validate_level_num();
	$multipage = multi($count, $perpage, $page, ADMINSCRIPT."?action=group&operation=mod&submit=yes");
	$query = C::t('forum_forum')->fetch_all_validate($startlimit, $startlimit+$perpage);
	foreach($query as $group) {
		$groups .= showtablerow('', array('class="td25"', '', ''), array(
			"<input type=\"checkbox\" name=\"fidarray[]\" value=\"$group[fid]\" class=\"checkbox\">",
			"<a href=\"forum.php?mod=forumdisplay&fid=$group[fid]\" target=\"_blank\">$group[name]</a>",
			empty($_G['cache']['grouptype']['first'][$group[fup]]) ? $_G['cache']['grouptype']['second'][$group[fup]]['name'] : $_G['cache']['grouptype']['first'][$group[fup]]['name'],
			"<a href=\"home.php?mod=space&uid=$group[founderuid]\" target=\"_blank\">$group[foundername]</a>",
			dgmdate($group['dateline'])
		), TRUE);
		$groups .=showtablerow('', array('','colspan="4"'), array('',cplang('group_mod_description').'&nbsp;:&nbsp;'.$group['description']), TRUE);
	}
	shownav('group', 'nav_group_mod');
	showsubmenu('nav_group_mod');
	showformheader("group&operation=mod");
	showtableheader('group_mod_wait');
	showsubtitle(array('', 'groups_manage_name', 'groups_editgroup_category', 'groups_manage_founder', 'groups_manage_createtime'));
	echo $groups;
	showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'fidarray\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" name="validate" value="'.cplang('validate').'" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" name="delsubmit" value="'.cplang('delete').'" onclick="return confirm(\''.cplang('group_mod_delconfirm').'\')" />', $multipage);
	showtablefooter();
	showformfooter();
}

function showgroup(&$forum, $type = '', $last = '') {
	global $_G;
	loadcache('grouptype');
	if($last == '') {
		$return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = $fcolumns = '';
		$fcolumns = ' '.cplang('groups_type_show_rows').'<input type="text" name="forumcolumnsnew['.$forum['fid'].']" value="'.$forum['forumcolumns'].'" class="txt" style="width: 30px;" />';

		if(!$forum['status']  || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
			$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
			$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
			$boardattr .= '</div>';
		}
		$selectgroups = '';
		if($type == 'group') {
			$secondlist = array();
			if(!empty($_G['cache']['grouptype']['first'][$forum[fid]]['secondlist'])){
				$secondlist = $_G['cache']['grouptype']['first'][$forum[fid]]['secondlist'];
			}
			$secondlist[] = $forum['fid'];
			foreach($secondlist as $sfid) {
				$selectgroups .= "&selectgroupid[]=$sfid";
			}
			$forum['groupnum'] = $_G['cache']['grouptype']['first'][$forum[fid]]['groupnum'];
		} else {
			$selectgroups = '&selectgroupid[]='.$forum['fid'];
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.dhtmlspecialchars($forum['name']).'" class="txt" />&nbsp;'.$fcolumns.'</div>'.$boardattr.
			'</td>
			<td>'.$forum['groupnum'].'</td>
			<td><a href="'.ADMINSCRIPT.'?action=group&operation=deletetype&fid='.$forum['fid'].'" title="'.cplang('groups_type_delete').'" class="act">'.cplang('delete').'</a>';
		$return .= '<a href="'.ADMINSCRIPT.'?action=group&operation=manage&submit=yes'.$selectgroups.'" class="act">'.cplang('groups_type_search').'</a><a href="'.ADMINSCRIPT.'?action=group&operation=mergetype&fid='.$forum['fid'].'" class="act">'.cplang('group_mergetype').'</a>';
		$return .= '</td></tr>';
	} else {
		if($last == 'lastboard') {
			$return = '<tr><td></td><td colspan="3"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('groups_type_sub_new').'</a></div></td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '<tr><td colspan="3"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('groups_type_level_1_add').'</a></div></td></tr>';
		}
	}
	echo $return;
	return $forum['fid'];
}

function searchgroups($submit) {
	global $_G;
	require_once libfile('function/group');
	empty($_GET['selectgroupid']) && $_GET['selectgroupid'] = array();
	$groupselect = get_groupselect(0, $_GET['selectgroupid'], 0);
	$monthselect = $dayselect = $birthmonth = $birthday = '';
	for($m=1; $m<=12; $m++) {
		$m = sprintf("%02d", $m);
		$monthselect .= "<option value=\"$m\" ".($birthmonth == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d=1; $d<=31; $d++) {
		$d = sprintf("%02d", $d);
		$dayselect .= "<option value=\"$d\" ".($birthday == $d ? 'selected' : '').">$d</option>\n";
	}

	showtagheader('div', 'searchgroups', !$submit);
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	showformheader("group&operation=manage");
	showtableheader();
	showsetting('groups_manage_name', 'srchname', $srchname, 'text');
	showsetting('groups_manage_id', 'srchfid', $srchfid, 'text');
	showsetting('groups_editgroup_category', '', '', '<select name="selectgroupid[]" multiple="multiple" size="10"><option value="all"'.(in_array('all', $_GET['selectgroupid']) ? ' selected' : '').'>'.cplang('unlimited').'</option>'.$groupselect.'</select>');
	showsetting('groups_manage_membercount', array('memberlower', 'memberhigher'), array($_GET['memberlower'], $_GET['memberhigher']), 'range');
	showsetting('groups_manage_threadcount', array('threadshigher', 'threadslower'), array($threadshigher, $threadslower), 'range');
	showsetting('groups_manage_replycount', array('postshigher', 'postslower'), array($postshigher, $postslower), 'range');
	showsetting('groups_manage_createtime', array('datelineafter', 'datelinebefore'), array($datelineafter, $datelinebefore), 'daterange');
	showsetting('groups_manage_updatetime', array('lastupdateafter', 'lastupdatebefore'), array($lastupdateafter, $lastupdatebefore), 'daterange');
	showsetting('groups_manage_founder', 'srchfounder', $srchfounder, 'text');
	showsetting('groups_manage_founder_uid', 'srchfounderid', $srchfounderid, 'text');

	showtagfooter('tbody');
	showsubmit('submit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
}

function countgroups() {
	global $_G;
	$_G['setting']['group_perpage'] = 100;
	$page = $_GET['page'] ? $_GET['page'] : 1;
	$start_limit = ($page - 1) * $_G['setting']['group_perpage'];
	$dateoffset = date('Z') - ($_G['setting']['timeoffset'] * 3600);
	$username = trim($username);

	$conditions = 'f.type=\'sub\' AND f.status=\'3\'';
	if($_GET['srchname'] != '') {
		$srchname = explode(',', addslashes($_GET['srchname']));
		foreach($srchname as $u) {
			$srchnameary[] = " f.name LIKE '%".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $u)."%'";
		}
		$conditions .= " AND (".implode(' OR ', $srchnameary).")";
	}
	$conditions .= intval($_GET['srchfid']) ? " AND f.fid='".intval($_GET['srchfid'])."'" : '';
	$conditions .= !empty($_GET['selectgroupid']) && !in_array('all', $_GET['selectgroupid']) != '' ? " AND f.fup IN ('".implode('\',\'', dintval($_GET['selectgroupid'], true))."')" : '';

	$conditions .= $_GET['postshigher'] != '' ? " AND f.posts>'".intval($_GET['postshigher'])."'" : '';
	$conditions .= $_GET['postslower'] != '' ? " AND f.posts<'".intval($_GET['postslower'])."'" : '';

	$conditions .= $_GET['threadshigher'] != '' ? " AND f.threads>'".intval($_GET['threadshigher'])."'" : '';
	$conditions .= $_GET['threadslower'] != '' ? " AND f.threads<'".intval($_GET['threadslower'])."'" : '';

	$conditions .= $_GET['memberhigher'] != '' ? " AND ff.membernum<'".intval($_GET['memberhigher'])."'" : '';
	$conditions .= $_GET['memberlower'] != '' ? " AND ff.membernum>'".intval($_GET['memberlower'])."'" : '';

	$conditions .= $_GET['datelinebefore'] != '' ? " AND ff.dateline<'".strtotime($_GET['datelinebefore'])."'" : '';
	$conditions .= $_GET['datelineafter'] != '' ? " AND ff.dateline>'".strtotime($_GET['datelineafter'])."'" : '';

	$conditions .= $_GET['lastupbefore'] != '' ? " AND ff.lastupdate<'".strtotime($_GET['lastupbefore'])."'" : '';
	$conditions .= $_GET['lastupafter'] != '' ? " AND ff.lastupdate>'".strtotime($_GET['lastupafter'])."'" : '';

	if($_GET['srchfounder'] != '') {
		$srchfounder = explode(',', addslashes($_GET['srchfounder']));
		foreach($srchfounder as $fu) {
			$srchfnameary[] = " ff.foundername LIKE '".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $fu)."'";
		}
		$conditions .= " AND (".implode(' OR ', $srchfnameary).")";
	}

	$conditions .= intval($_GET['srchfounderid']) ? " AND ff.founderuid='".intval($_GET['srchfounderid'])."'" : '';


	if(!$conditions && !$uidarray && $operation == 'clean') {
		cpmsg('groups_search_invalid', '', 'error');
	}

	$urladd = "&srchname=".rawurlencode($_GET['srchname'])."&srchfid=".intval($_GET['srchfid'])."&postshigher=".rawurlencode($_GET['postshigher'])."&postslower=".rawurlencode($_GET['postslower'])."&threadshigher=".rawurlencode($_GET['threadshigher'])."&threadslower=".rawurlencode($_GET['threadslower'])."&memberhigher=".rawurlencode($_GET['memberhigher'])."&memberlower=".rawurlencode($_GET['memberlower'])."&datelinebefore=".rawurlencode($_GET['datelinebefore'])."&datelineafter=".rawurlencode($_GET['datelineafter'])."&lastupbefore=".rawurlencode($_GET['lastupbefore'])."&lastupafter=".rawurlencode($_GET['lastupafter'])."&srchfounderid=".rawurlencode($_GET['srchfounderid']);

	$groupnum = C::t('forum_forum')->fetch_all_for_search($conditions, -1);
	return array($page, $start_limit, $groupnum, $conditions, $urladd);
}

function delete_groupimg($fidarray) {
	global $_G;
	if(!empty($fidarray)) {
		$query = C::t('forum_forumfield')->fetch_all($fidarray);
		$imgdir = $_G['setting']['attachdir'].'/group/';
		foreach($query as $group) {
			@unlink($imgdir.$group['icon']);
			@unlink($imgdir.$group['banner']);
		}
	}
}

function array_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = @array_keys($arr);
	list(, $first) = @each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
}
function cacherecommend($fidstr, $return = true) {
	require_once libfile('function/group');
	$group_recommend = array();
	$recommend_num = 8;
	$recommends = $fidstr ? explode(',', $fidstr) : array();
	if($recommends) {
		$query = C::t('forum_forum')->fetch_all_info_by_fids($recommends, 3);
		foreach($query as $val) {
			$row = array();
			if($val['type'] == 'sub') {
				$row = array('fid' => $val['fid'], 'name' => $val['name'], 'description' => $val['description'], 'icon' => $val['icon']);
				$row['icon'] = get_groupimg($row['icon'], 'icon');
				$temp[$row[fid]] = $row;
			}
		}
		foreach($recommends as $key) {
			if(!empty($temp[$key])) {
				$group_recommend[$key] = $temp[$key];
			}
		}
	}
	if(count($group_recommend) < $recommend_num) {
		$query = C::t('forum_forum')->fetch_all_default_recommend($recommend_num);
		foreach($query as $row) {
			$row['icon'] = get_groupimg($row['icon'], 'icon');
			if(count($group_recommend) == $recommend_num) {
				break;
			} elseif(empty($group_recommend[$row[fid]])) {
				$group_recommend[$row[fid]] = $row;
			}
		}
	}
	if($return) {
		return $group_recommend;
	} else {
		C::t('common_setting')->update_batch(array('group_recommend' => $group_recommend));
	}
}
?>