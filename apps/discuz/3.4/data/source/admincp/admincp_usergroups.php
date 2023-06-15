<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_usergroups.php 35097 2014-11-17 09:43:10Z laoguozhang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('groupsubmit')) {

		$sgroups = $smembers = $specialgroup = array();
		$sgroupids = '0';
		$smembernum = $membergroup = $sysgroup = $membergroupoption = $specialgroupoption = '';

		foreach(C::t('common_usergroup')->range_orderby_creditshigher() as $group) {
			if($group['type'] == 'member') {

				$membergroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$membergroup .= showtablerow('', array('class="td25"', '', 'class="td23 lightfont"', 'class="td28"', 'class=td28'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\">",
					"(groupid:$group[groupid])",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditslower]\" value=\"$group[creditslower]\" disabled />",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\">",
					"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\" name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
						"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>".
						"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=merge&source=$group[groupid]\" title=\"$lang[usergroups_merge_comment]\" class=\"act\">$lang[usergroups_merge_link]</a>"
				), TRUE);
			} elseif($group['type'] == 'system') {
				$sysgroup .= showtablerow('', array('', 'class="td23 lightfont"', '', 'class="td28"'), array(
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
					"(groupid:$group[groupid])",
					$lang['usergroups_system_'.$group['groupid']],
					"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
					"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gsystem\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
						"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>"
				), TRUE);
			} elseif($group['type'] == 'special' && $group['radminid'] == '0') {

				$specialgroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$sgroups[] = $group;
				$sgroupids .= ','.$group['groupid'];
			}
		}

		foreach($sgroups as $group) {
			if(is_array($smembers[$group['groupid']])) {
				$num = count($smembers[$group['groupid']]);
				$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
				unset($smembers[$group['groupid']]);
			} else {
				$specifiedusers = '';
				$num = 0;
			}
			$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";

			$sg = showtablerow('', array('class="td25"', '', 'class="td23 lightfont"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
				"(groupid:$group[groupid])",
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" style=\"background: {$group['color']}\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gspecial\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
					"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>".
					"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=merge&source=$group[groupid]\" title=\"$lang[usergroups_merge_comment]\" class=\"act\">$lang[usergroups_merge_link]</a>".
					"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=viewsgroup&sgroupid=$group[groupid]\" onclick=\"ajaxget(this.href, 'sgroup_$group[groupid]', 'sgroup_$group[groupid]');doane(event);\" class=\"act\">$lang[view]</a> &nbsp;"
			), TRUE);
			$sg .= showtablerow('', array('colspan="5" id="sgroup_'.$group['groupid'].'" style="display: none"'), array(''), TRUE);

			if($group['system'] == 'private') {
				$st = 'private';
			} else {
				list($dailyprice) = explode("\t", $group['system']);
				$st = $dailyprice > 0 ? 'buy' : 'free';
			}
			$specialgroup[$st] .= $sg;

		}

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[2,'<input type="text" class="txt" size="12" name="groupnewadd[grouptitle][]"><select name="groupnewadd[projectid][]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$membergroupoption</select>'],
		[1,'<input type="text" class="txt" size="6" name="groupnewadd[creditshigher][]">', 'td28'],
		[1,'<input type="text" class="txt" size="2" name="groupnewadd[stars][]">', 'td28'],
		[2,'<input type="text" class="txt" size="6" name="groupnewadd[color][]">']
	],
	[
		[1,'', 'td25'],
		[2,'<input type="text" class="txt" size="12" name="grouptitlenewadd[]"><select name="groupnewaddproject[]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$specialgroupoption</select>'],
		[1,'<input type="text" class="txt" size="2" name="starsnewadd[]">', 'td28'],
		[2,'<input type="text" class="txt" size="6" name="colornewadd[]">']
	]
];
</script>
EOT;
		shownav('user', 'nav_usergroups');
		showsubmenuanchors('nav_usergroups', array(
			array('usergroups_member', 'membergroups', !$_GET['type'] || $_GET['type'] == 'member'),
			array('usergroups_special', 'specialgroups', $_GET['type'] == 'special'),
			array('usergroups_system', 'systemgroups', $_GET['type'] == 'system')
		));
		showtips('usergroups_tips');

		showformheader('usergroups&type=member');
		showtableheader('usergroups_member', 'fixpadding', 'id="membergroups"'.($_GET['type'] && $_GET['type'] != 'member' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', '', 'usergroups_creditsrange', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcmember" onclick="checkAll(\'value\', this.form, \'gmember\', \'gcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $membergroup;
		echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=special');
		showtableheader('usergroups_special', 'fixpadding', 'id="specialgroups"'.($_GET['type'] != 'special' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', '', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcspecial" onclick="checkAll(\'value\', this.form, \'gspecial\', \'gcspecial\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		if($specialgroup['private']) {
			echo $specialgroup['private'];
		}
		if($specialgroup['buy']) {
			showsubtitle(array('', 'usergroups_edit_system_buy'));
			echo $specialgroup['buy'];
		}
		if($specialgroup['free']) {
			showsubtitle(array('', 'usergroups_edit_system_free'));
			echo $specialgroup['free'];
		}
		echo '<tr><td>&nbsp;</td><td colspan="5"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=system');
		showtableheader('usergroups_system', 'fixpadding', 'id="systemgroups"'.($_GET['type'] != 'system' ? ' style="display: none"' : ''));
		showsubtitle(array('usergroups_title', '', 'usergroups_status', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcsystem" onclick="checkAll(\'value\', this.form, \'gsystem\', \'gcsystem\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $sysgroup;
		showsubmit('groupsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(empty($_GET['type']) || !in_array($_GET['type'], array('member', 'special', 'system'))) {
			cpmsg('usergroups_type_nonexistence');
		}

		$oldgroups = $extadd = array();
		foreach(C::t('common_usergroup')->fetch_all_by_type($_GET['type'], null, true) as $gp) {
			$oldgroups[$gp['groupid']] = $gp;
		}

		foreach($oldgroups as $id => $vals) {
			$data = array();
			foreach($vals as $k => $v) {
				$v = addslashes($v);
				if(!in_array($k, array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'icon'))) {
					$data[$k] = $v;
				}
			}
			$extadd['g'.$id] = $data;
		}

		if($_GET['type'] == 'member') {
			$groupnewadd = array_flip_keys($_GET['groupnewadd']);
			foreach($groupnewadd as $k => $v) {
				if(!$v['grouptitle']) {
					unset($groupnewadd[$k]);
				} elseif(!$v['creditshigher']) {
					cpmsg('usergroups_update_creditshigher_invalid', '', 'error');
				}
			}
			$groupnewkeys = array_keys($_GET['groupnew']);
			$maxgroupid = max($groupnewkeys);
			foreach($groupnewadd as $k=>$v) {
				$_GET['groupnew'][$k+$maxgroupid+1] = $v;
			}
			$orderarray = array();
			if(is_array($_GET['groupnew'])) {
				foreach($_GET['groupnew'] as $id => $group) {
					if((is_array($_GET['delete']) && in_array($id, $_GET['delete'])) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
						unset($_GET['groupnew'][$id]);
					} else {
						$orderarray[$group['creditshigher']] = $id;
					}
				}
			}

			if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
				cpmsg('usergroups_update_credits_invalid', '', 'error');
			}

			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
				);
			}

			foreach($_GET['groupnew'] as $id => $group) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('usergroups_update_credits_duplicate', '', 'error');
				}
				if(in_array($id, $groupnewkeys)) {
					C::t('common_usergroup')->update($id, array('grouptitle' => $group['grouptitle'], 'creditshigher' => $creditshighernew, 'creditslower' => $creditslowernew, 'stars' => $group['stars'], 'color' => $group['color']), 'member');
					C::t('forum_onlinelist')->update_by_groupid($id, array('title' => $group['grouptitle']));

				} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
					$data = array(
						'grouptitle' => $group['grouptitle'],
						'creditshigher' => $creditshighernew,
						'creditslower' => $creditslowernew,
						'stars' => $group['stars'],
						'color' => $group['color'],
					);
					if(!empty($group['projectid']) && !empty($extadd[$group['projectid']])) {
						$data = array_merge($data, $extadd[$group['projectid']]);
					}

					$newgid = C::t('common_usergroup')->insert($data, true);

					$datafield = array(
						'groupid' => $newgid,
						'allowsearch' => 2,
					);


					C::t('common_usergroup_field')->insert($datafield);

					C::t('forum_onlinelist')->insert(array(
						'groupid' => $newgid,
						'title' => $data['grouptitle'],
						'displayorder' => '0',
						'url' => '',
					));

					$sqladd = !empty($group['projectid']) && !empty($extadd[$group['projectid']]) ? $extadd[$group['projectid']] : '';
					if($sqladd) {
						$projectid = substr($group['projectid'], 1);
						$group_fields = C::t('common_usergroup_field')->fetch($projectid);
						unset($group_fields['groupid']);
						C::t('common_usergroup_field')->update($newgid, $group_fields);
						$query = C::t('forum_forumfield')->fetch_all_field_perm();
						foreach($query as $row) {
							$upforumperm = array();
							if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
								$upforumperm['viewperm'] = "$row[viewperm]$newgid\t";
							}
							if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
								$upforumperm['postperm'] = "$row[postperm]$newgid\t";
							}
							if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
								$upforumperm['replyperm'] = "$row[replyperm]$newgid\t";
							}
							if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
								$upforumperm['getattachperm'] = "$row[getattachperm]$newgid\t";
							}
							if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
								$upforumperm['postattachperm'] = "$row[postattachperm]$newgid\t";
							}
							if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
								$upforumperm['postimageperm'] = "$row[postimageperm]$newgid\t";
							}
							if($upforumperm) {
								C::t('forum_forumfield')->update($row['fid'], $upforumperm);
							}
						}
					}
				}
			}

			if($_GET['delete']) {
				C::t('common_usergroup')->delete($_GET['delete'], 'member');
				C::t('common_usergroup_field')->delete($_GET['delete']);
				C::t('forum_onlinelist')->delete_by_groupid($_GET['delete']);
				deletegroupcache($_GET['delete']);
			}

		} elseif($_GET['type'] == 'special') {
			if(is_array($_GET['grouptitlenewadd'])) {
				foreach($_GET['grouptitlenewadd'] as $k => $v) {
					if($v) {
						$data = array(
							'type' => 'special',
							'grouptitle' => $_GET['grouptitlenewadd'][$k],
							'color' => $_GET['colornewadd'][$k],
							'stars' => $_GET['starsnewadd'][$k],
						);
						if(!empty($_GET['groupnewaddproject'][$k]) && !empty($extadd[$_GET['groupnewaddproject'][$k]])) {
							$data = array_merge($data, $extadd[$_GET['groupnewaddproject'][$k]]);
						}
						$newgid = C::t('common_usergroup')->insert($data, true);

						$datafield = array(
							'groupid' => $newgid,
							'allowsearch' => 2,
						);

						C::t('common_usergroup_field')->insert($datafield);
						C::t('forum_onlinelist')->insert(array(
							'groupid' => $newgid,
							'title' => $data['grouptitle'],
							'url' => '',
						));
						$sqladd = !empty($_GET['groupnewaddproject'][$k]) && !empty($extadd[$_GET['groupnewaddproject'][$k]]) ? $extadd[$_GET['groupnewaddproject'][$k]] : '';
						if($sqladd) {
							$projectid = substr($_GET['groupnewaddproject'][$k], 1);
							$group_fields = C::t('common_usergroup_field')->fetch($projectid);
							unset($group_fields['groupid']);
							C::t('common_usergroup_field')->update($newgid, $group_fields);
							$query = C::t('forum_forumfield')->fetch_all_field_perm();
							foreach($query as $row) {
								$upforumperm = array();
								if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
									$upforumperm['viewperm'] = "$row[viewperm]$newgid\t";
								}
								if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
									$upforumperm['postperm'] = "$row[postperm]$newgid\t";
								}
								if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
									$upforumperm['replyperm'] = "$row[replyperm]$newgid\t";
								}
								if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
									$upforumperm['getattachperm'] = "$row[getattachperm]$newgid\t";
								}
								if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
									$upforumperm['postattachperm'] = "$row[postattachperm]$newgid\t";
								}
								if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
									$upforumperm['postimageperm'] = "$row[postimageperm]$newgid\t";
								}
								if($upforumperm) {
									C::t('forum_forumfield')->update($row['fid'], $upforumperm);
								}
							}
						}
					}
				}
			}

			if(is_array($_GET['group_title'])) {
				foreach($_GET['group_title'] as $id => $title) {
					if(!$_GET['delete'][$id]) {
						C::t('common_usergroup')->update($id, array('grouptitle' => $_GET['group_title'][$id], 'stars' => $_GET['group_stars'][$id], 'color' => $_GET['group_color'][$id]));
						C::t('forum_onlinelist')->update_by_groupid($id, array('title' => $_GET['group_title'][$id]));
					}
				}
			}

			if(($ids = $_GET['delete'])) {
				C::t('common_usergroup')->delete($ids, 'special');
				C::t('forum_onlinelist')->delete_by_groupid($ids);
				C::t('common_admingroup')->delete($ids);
				$newgroupid = C::t('common_usergroup')->fetch_new_groupid();
				C::t('common_member')->update_by_groupid($ids, array('groupid' => $newgroupid, 'adminid' => '0'));
				deletegroupcache($ids);
			}

		} elseif($_GET['type'] == 'system') {
			if(is_array($_GET['group_title'])) {
				foreach($_GET['group_title'] as $id => $title) {
					C::t('common_usergroup')->update($id, array('grouptitle' => $_GET['group_title'][$id], 'stars' => $_GET['group_stars'][$id], 'color' => $_GET['group_color'][$id]));
					C::t('forum_onlinelist')->update_by_groupid($id, array('title' => $_GET['group_title'][$id]));
				}
			}
		}

		updatecache(array('usergroups', 'onlinelist', 'groupreadaccess'));
		cpmsg('usergroups_update_succeed', 'action=usergroups&type='.$_GET['type'], 'succeed');
	}

} elseif($operation == 'viewsgroup') {

	$sgroupid = $_GET['sgroupid'];
	$num = C::t('common_member')->count_by_groupid($sgroupid);
	$sgroups = '';
	foreach(C::t('common_member')->fetch_all_by_groupid($sgroupid, 0, 80) as $uid => $member) {
		$sgroups .= '<li><a href="home.php?mod=space&uid='.$uid.'" target="_blank">'.$member['username'].'</a></li>';
	}
	ajaxshowheader();
	echo '<ul class="userlist"><li class="unum">'.$lang['usernum'].$num.($num > 80 ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=members&operation=search&submit=yes&groupid='.$sgroupid.'">'.$lang['more'].'&raquo;</a>' : '').'</li>'.$sgroups.'</ul>';
	ajaxshowfooter();

} elseif($operation == 'edit') {

	$return = isset($_GET['return']) && $_GET['return'] ? 'admin' : '';

	list($pluginsetting, $pluginvalue) = get_pluginsetting('groups');

	$multiset = 0;
	$gids = array();
	if(empty($_GET['multi'])) {
		if($_GET['id']) {
			$gids[0] = $_GET['id'];
		}
	} else {
		$multiset = 1;
		if(is_array($_GET['multi'])) {
			$gids = &$_GET['multi'];
		} else {
			$_GET['multi'] = explode(',', $_GET['multi']);
			$gids = &$_GET['multi'];
		}
	}
	if(count($_GET['multi']) == 1) {
		if ($_GET['multi'][0]) {
			$gids[0] = $_GET['multi'][0];
		}
		$multiset = 0;
	}


	if(!count($gids)) {
		$grouplist = "<select name=\"id\" style=\"width:150px\">\n";
		$conditions = !empty($_GET['anchor']) && $_GET['anchor'] == 'system' ? 'special' : '';
		foreach(C::t('common_usergroup')->fetch_all_by_type($conditions) as $group) {
			$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$grouplist .= '</select>';
		cpmsg('usergroups_edit_nonexistence', 'action=usergroups&operation=edit'.(!empty($_GET['highlight']) ? "&highlight={$_GET['highlight']}" : '').(!empty($_GET['highlight']) ? "&anchor={$_GET['anchor']}" : ''), 'form', array(), $grouplist);
	}

	$group_data = C::t('common_usergroup')->fetch_all($gids);
	$groupfield_data = C::t('common_usergroup_field')->fetch_all($gids);
	if(!$group_data) {
		cpmsg('usergroups_nonexistence', '', 'error');
	} else {
		foreach($group_data as $curgid => $group) {
			$group = array_merge($group, (array)$groupfield_data[$curgid]);
			if(isset($pluginvalue[$group['groupid']])) {
				$group['plugin'] = $pluginvalue[$group['groupid']];
			}
			$mgroup[] = $group;
		}
	}

	$allowthreadplugin = $_G['setting']['threadplugins'] ? C::t('common_setting')->fetch('allowthreadplugin', true) : array();
	if(!submitcheck('detailsubmit')) {

		$grouplist = $groupcount = array();
		foreach(C::t('common_usergroup')->range_orderby_credit() as $ggroup) {
			$checked = $_GET['id'] == $ggroup['groupid'] || in_array($ggroup['groupid'], $_GET['multi']);
			$ggroup['type'] = $ggroup['type'] == 'special' && $ggroup['radminid'] ? 'specialadmin' : $ggroup['type'];
			$groupcount[$ggroup['type']]++;
			$grouplist[$ggroup['type']] .= '<input class="left checkbox ck" chkvalue="'.$ggroup['type'].'" name="multi[]" value="'.$ggroup['groupid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/>'.
				'<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+scrollTopBody()"'.($checked ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
			if(!($groupcount[$ggroup['type']] % 3)) {
				$grouplist[$ggroup['type']] .= '<br style="clear:both" />';
			}
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'ugselect_menu\').style.left=(parseInt($(\'ugselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.
			'<em class="cl"><span class="right"><input name="checkall_member" onclick="checkAll(\'value\', this.form, \'member\', \'checkall_member\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_member'].'</em>'.$grouplist['member'].'<br />'.
			($grouplist['special'] ? '<em class="cl"><span class="right"><input name="checkall_special" onclick="checkAll(\'value\', this.form, \'special\', \'checkall_special\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_special'].'</em>'.$grouplist['special'].'<br />' : '').
			($grouplist['specialadmin'] ? '<em class="cl"><span class="right"><input name="checkall_specialadmin" onclick="checkAll(\'value\', this.form, \'specialadmin\', \'checkall_specialadmin\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_specialadmin'].'</em>'.$grouplist['specialadmin'].'<br />' : '').
			'<em class="cl"><span class="right"><input name="checkall_system" onclick="checkAll(\'value\', this.form, \'system\', \'checkall_system\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_system'].'</em>'.$grouplist['system'].
			'<br style="clear:both" /><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('usergroups_multiedit').'" /></div>'.
			'</div>';
		$anchor = in_array($_GET['anchor'], array('basic', 'system', 'special', 'post', 'attach', 'magic', 'invite', 'pm', 'credit', 'home', 'group', 'portal', 'plugin')) ? $_GET['anchor'] : 'basic';
		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'usergroups', 'operation' => 'edit'));
		showsubmenuanchors(cplang('usergroups_edit').(count($mgroup) == 1 ? ' - '.$mgroup[0]['grouptitle'].'(groupid:'.$mgroup[0]['groupid'].')' : ''), array(
			array('usergroups_edit_basic', 'basic', $anchor == 'basic'),
			count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1 ? array('usergroups_edit_system', 'system', $anchor == 'system') : array(),
			array(array('menu' => 'usergroups_edit_forum', 'submenu' => array(
				array('usergroups_edit_post', 'post', $anchor == 'post'),
				array('usergroups_edit_attach', 'attach', $anchor == 'attach'),
				array('usergroups_edit_special', 'special', $anchor == 'special')
			))),
			array('usergroups_edit_group', 'group', $anchor == 'group'),
			array('usergroups_edit_portal', 'portal', $anchor == 'portal'),
			array('usergroups_edit_home', 'home', $anchor == 'home'),
			array(array('menu' => 'usergroups_edit_other', 'submenu' => array(
				array('usergroups_edit_credit', 'credit', $anchor == 'credit'),
				array('usergroups_edit_magic', 'magic', $anchor == 'magic'),
				array('usergroups_edit_invite', 'invite', $anchor == 'invite'),
				!$pluginsetting ? array() : array('usergroups_edit_plugin', 'plugin', $anchor == 'plugin'),
			))),
		), $gselect);
		showformfooter();

		if(count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1) {
			showtips('usergroups_edit_system_tips', 'system_tips', $anchor == 'system');
		}
		if($multiset) {
			showtips('setting_multi_tips');
		}

		showtips('usergroups_edit_magic_tips', 'magic_tips', $anchor == 'magic');
		showtips('usergroups_edit_invite_tips', 'invite_tips', $anchor == 'invite');
		if($_GET['id'] == 7) {
			showtips('usergroups_edit_system_guest_portal_tips', 'portal_tips', $anchor == 'portal');
			showtips('usergroups_edit_system_guest_home_tips', 'home_tips', $anchor == 'home');
		}
		showformheader("usergroups&operation=edit&id={$_GET['id']}&return=$return", 'enctype');

		if($multiset) {
			$_G['showsetting_multi'] = 0;
			$_G['showsetting_multicount'] = count($mgroup);
			foreach($mgroup as $group) {
				$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&id='.$group['groupid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$group['grouptitle'].'(groupid:'.$group['groupid'].')</a>';
			}
		}
		$mgids = array();
		foreach($mgroup as $group) {
		$_GET['id'] = $gid = $group['groupid'];
		$mgids[] = $gid;

		if(!$multiset && $group['type'] == 'special' && $group['radminid'] < 1) {
			showtagheader('div', 'system', $anchor == 'system');
			showtableheader();
			if($group['system'] == 'private') {
				$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
			} else {
				$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
				list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
			}
			showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio', 0, 1);
			showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
			showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
			showtablefooter();
			showtagfooter('div');
		}

		showmultititle();
		showtagheader('div', 'basic', $anchor == 'basic');
		showtableheader();
		showtitle('usergroups_edit_basic');
		showsetting('usergroups_edit_basic_title', 'grouptitlenew', $group['grouptitle'], 'text');
		$group['exempt'] = strrev(sprintf('%0'.strlen($group['exempt']).'b', $group['exempt']));
		if(!$multiset) {
			if($group['icon']) {
				$valueparse = parse_url($group['icon']);
				if(isset($valueparse['host'])) {
					$groupicon = $group['icon'];
				} else {
					$groupicon = $_G['setting']['attachurl'].'common/'.$group['icon'].'?'.random(6);
				}
				$groupiconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon[$group[groupid]]" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$groupicon.'" />';
			}
			showsetting('usergroups_icon', 'iconnew', $group['icon'], 'filetext', '', 0, $groupiconhtml);
		}


		$group['allowvisit'] = $group['groupid'] == 1 ? 2 : $group['allowvisit'];

		showsetting('usergroups_edit_basic_visit', array('allowvisitnew', array(
			array(0, cplang('usergroups_edit_basic_visit_none')),
			array(1, cplang('usergroups_edit_basic_visit_normal')),
			array(2, cplang('usergroups_edit_basic_visit_super')),
		)), $group['allowvisit'], 'mradio');

		showsetting('usergroups_edit_basic_read_access', 'readaccessnew', $group['readaccess'], 'text');
		showsetting('usergroups_edit_basic_max_friend_number', 'maxfriendnumnew', $group['maxfriendnum'], 'text');
		showsetting('usergroups_edit_basic_domain_length', 'domainlengthnew', $group['domainlength'], 'text');
		showsetting('usergroups_edit_basic_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
		showsetting('usergroups_edit_basic_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
		showsetting('usergroups_edit_basic_allowsendpm', 'allowsendpmnew', $group['allowsendpm'], 'radio', 0, 1);
		showsetting('usergroups_edit_pm_sendpmmaxnum', 'allowsendpmmaxnumnew', $group['allowsendpmmaxnum'], 'text');
		showsetting('usergroups_edit_pm_sendallpm', 'allowsendallpmnew', $group['allowsendallpm'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_post_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
		showsetting('usergroups_edit_post_url', array('allowposturlnew', array(
			array(0, $lang['usergroups_edit_post_url_banned']),
			array(1, $lang['usergroups_edit_post_url_mod']),
			array(2, $lang['usergroups_edit_post_url_unhandle']),
			array(3, $lang['usergroups_edit_post_url_enable'])
		)), $group['allowposturl'], 'mradio');
		showsetting('usergroups_edit_basic_allow_statdata', 'allowstatdatanew', $group['allowstatdata'], 'radio');
		showsetting('usergroups_edit_basic_search_post', 'allowfulltextnew', $group['allowsearch'] & 32, 'radio');
		$group['allowsearch'] = $group['allowsearch'] > 128 ? $group['allowsearch'] - 128 : $group['allowsearch'];
		showsetting('usergroups_edit_basic_search', array('allowsearchnew', array(
			cplang('setting_search_status_portal'),
			cplang('setting_search_status_forum'),
			cplang('setting_search_status_blog'),
			cplang('setting_search_status_album'),
			cplang('setting_search_status_group'),
			false,
			cplang('setting_search_status_collection')
		)), $group['allowsearch'], 'binmcheckbox');
		showsetting('usergroups_edit_basic_reasonpm', array('reasonpmnew', array(
			array(0, $lang['usergroups_edit_basic_reasonpm_none']),
			array(1, $lang['usergroups_edit_basic_reasonpm_reason']),
			array(2, $lang['usergroups_edit_basic_reasonpm_pm']),
			array(3, $lang['usergroups_edit_basic_reasonpm_both'])
		)), $group['reasonpm'], 'mradio');
		showsetting('usergroups_edit_basic_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
		showsetting('usergroups_edit_basic_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
		showsetting('usergroups_edit_basic_hour_threads', 'maxthreadsperhournew', intval($group['maxthreadsperhour']), 'text');
		showsetting('usergroups_edit_basic_hour_posts', 'maxpostsperhournew', intval($group['maxpostsperhour']), 'text');
		showsetting('usergroups_edit_basic_seccode', 'seccodenew', $group['seccode'], 'radio', $group['groupid'] == 7);
		showsetting('usergroups_edit_basic_forcesecques', 'forcesecquesnew', $group['forcesecques'], 'radio');
		if(!in_array($gid, array(7, 8))) {
			showsetting('usergroups_edit_basic_forcelogin', array('forceloginnew', array(
				array(0, $lang['usergroups_edit_basic_forcelogin_none']),
				array(1, $lang['usergroups_edit_basic_forcelogin_qq']),
				array(2, $lang['usergroups_edit_basic_forcelogin_mail']),
			)), $group['forcelogin'], 'mradio');
		}
		showsetting('usergroups_edit_basic_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
		showsetting('usergroups_edit_basic_ignore_censor', 'ignorecensornew', $group['ignorecensor'], 'radio');
		showsetting('usergroups_edit_basic_allowcreatecollection', 'allowcreatecollectionnew', intval($group['allowcreatecollection']), 'text');
		showsetting('usergroups_edit_basic_allowfollowcollection', 'allowfollowcollectionnew', intval($group['allowfollowcollection']), 'text');
		showsetting('usergroups_edit_basic_close_ad', 'closeadnew', $group['closead'], 'radio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'special', $anchor == 'special');
		showtableheader();
		showtitle('usergroups_edit_special');
		showsetting('usergroups_edit_special_activity', 'allowpostactivitynew', $group['allowpostactivity'], 'radio');
		showsetting('usergroups_edit_special_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
		showsetting('usergroups_edit_special_vote', 'allowvotenew', $group['allowvote'], 'radio');
		showsetting('usergroups_edit_special_reward', 'allowpostrewardnew', $group['allowpostreward'], 'radio');
		showsetting('usergroups_edit_special_reward_min', 'minrewardpricenew', $group['minrewardprice'], "text");
		showsetting('usergroups_edit_special_reward_max', 'maxrewardpricenew', $group['maxrewardprice'], "text");
		showsetting('usergroups_edit_special_trade', 'allowposttradenew', $group['allowposttrade'], 'radio');
		showsetting('usergroups_edit_special_trade_min', 'mintradepricenew', $group['mintradeprice'], "text");
		showsetting('usergroups_edit_special_trade_max', 'maxtradepricenew', $group['maxtradeprice'], "text");
		showsetting('usergroups_edit_special_trade_stick', 'tradesticknew', $group['tradestick'], "text");
		showsetting('usergroups_edit_special_debate', 'allowpostdebatenew', $group['allowpostdebate'], "radio");
		showsetting('usergroups_edit_special_rushreply', 'allowpostrushreplynew', $group['allowpostrushreply'], "radio");
		$threadpluginselect = array();
		if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
			$threadpluginselect[] = array($tpid, $data['name']);
		}
		if($threadpluginselect) {
			showsetting('usergroups_edit_special_allowthreadplugin', array('allowthreadpluginnew', $threadpluginselect), $allowthreadplugin[$_GET['id']], 'mcheckbox');
		}
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'post', $anchor == 'post');
		showtableheader();
		showtitle('usergroups_edit_post');
		showsetting('usergroups_edit_post_new', 'allowpostnew', $group['allowpost'], 'radio');
		showsetting('usergroups_edit_post_reply', 'allowreplynew', $group['allowreply'], 'radio');
		showsetting('usergroups_edit_post_direct', array('allowdirectpostnew', array(
			array(0, $lang['usergroups_edit_post_direct_none']),
			array(1, $lang['usergroups_edit_post_direct_reply']),
			array(2, $lang['usergroups_edit_post_direct_thread']),
			array(3, $lang['usergroups_edit_post_direct_all'])
		)), $group['allowdirectpost'], 'mradio');
		showsetting('usergroups_edit_post_allow_down_remote_img', 'allowdownremoteimgnew', $group['allowdownremoteimg'], 'radio');
		showsetting('usergroups_edit_post_anonymous', 'allowanonymousnew', $group['allowanonymous'], 'radio');
		showsetting('usergroups_edit_post_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
		showsetting('usergroups_edit_post_maxprice', 'maxpricenew', $group['maxprice'], 'text');
		showsetting('usergroups_edit_post_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
		showsetting('usergroups_edit_post_mediacode', 'allowmediacodenew', $group['allowmediacode'], 'radio');
		showsetting('usergroups_edit_post_begincode', 'allowbegincodenew', $group['allowbegincode'], 'radio');
		showsetting('usergroups_edit_post_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
		showsetting('usergroups_edit_post_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
		showsetting('usergroups_edit_post_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');
		if($group['groupid'] != 7) {
			showsetting('usergroups_edit_post_recommend', 'allowrecommendnew', $group['allowrecommend'], 'text');
		}
		showsetting('usergroups_edit_post_edit_time_limit', 'edittimelimitnew', intval($group['edittimelimit']), 'text');
		showsetting('usergroups_edit_post_allowreplycredit', 'allowreplycreditnew', $group['allowreplycredit'], 'radio');
		showsetting('usergroups_edit_post_tag', 'allowposttagnew', $group['allowposttag'], 'radio');
		showsetting('usergroups_edit_post_allowcommentpost', array('allowcommentpostnew', array(
			$lang['usergroups_edit_post_allowcommentpost_firstpost'],
			$lang['usergroups_edit_post_allowcommentpost_reply'],
		)), $group['allowcommentpost'], 'binmcheckbox', !in_array(1, $_G['setting']['allowpostcomment']));
		showsetting('usergroups_edit_post_allowcommentreply', 'allowcommentreplynew', $group['allowcommentreply'], 'radio', !in_array(2, $_G['setting']['allowpostcomment']));
		showsetting('usergroups_edit_post_allowcommentitem', 'allowcommentitemnew', $group['allowcommentitem'], 'radio', !in_array(1, $_G['setting']['allowpostcomment']));
		showsetting('usergroups_edit_post_allowat', 'allowatnew', $group['allowat'], 'text');
		showsetting('usergroups_edit_post_allowsetpublishdate', 'allowsetpublishdatenew', $group['allowsetpublishdate'], 'radio');
		showsetting('usergroups_edit_post_allowcommentcollection', 'allowcommentcollectionnew', $group['allowcommentcollection'], 'radio');
		showsetting('usergroups_edit_post_allowimgcontent', 'allowimgcontentnew', $group['allowimgcontent'], 'radio');
		showtablefooter();
		showtagfooter('div');

		$group['maxattachsize'] = intval($group['maxattachsize'] / 1024);
		$group['maxsizeperday'] = intval($group['maxsizeperday'] / 1024);
		$group['maximagesize'] = intval($group['maximagesize'] / 1024);

		showtagheader('div', 'attach', $anchor == 'attach');
		showtableheader();
		showtitle('usergroups_edit_attach');
		showsetting('usergroups_edit_attach_get', 'allowgetattachnew', $group['allowgetattach'], 'radio');
		showsetting('usergroups_edit_attach_getimage', 'allowgetimagenew', $group['allowgetimage'], 'radio');
		showsetting('usergroups_edit_attach_post', 'allowpostattachnew', $group['allowpostattach'], 'radio');
		showsetting('usergroups_edit_attach_set_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
		showsetting('usergroups_edit_image_post', 'allowpostimagenew', $group['allowpostimage'], 'radio');
		showsetting('usergroups_edit_attach_max_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
		showsetting('usergroups_edit_attach_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
		showsetting('usergroups_edit_attach_max_number_per_day', 'maxattachnumnew', $group['maxattachnum'], 'text');
		showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'magic', $anchor == 'magic');
		showtableheader();
		showtitle('usergroups_edit_magic');
		showsetting('usergroups_edit_magic_permission', array('allowmagicsnew', array(
			array(0, $lang['usergroups_edit_magic_unallowed']),
			array(1, $lang['usergroups_edit_magic_allow']),
			array(2, $lang['usergroups_edit_magic_allow_and_pass'])
		)), $group['allowmagics'], 'mradio');
		showsetting('usergroups_edit_magic_discount', 'magicsdiscountnew', $group['magicsdiscount'], 'text');
		showsetting('usergroups_edit_magic_max', 'maxmagicsweightnew', $group['maxmagicsweight'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'invite', $anchor == 'invite');
		showtableheader();
		showtitle('usergroups_edit_invite');
		showsetting('usergroups_edit_invite_permission', 'allowinvitenew', $group['allowinvite'], 'radio');
		showsetting('usergroups_edit_invite_send_permission', 'allowmailinvitenew', $group['allowmailinvite'], 'radio');
		showsetting('usergroups_edit_invite_price', 'invitepricenew', $group['inviteprice'], 'text');
		showsetting('usergroups_edit_invite_buynum', 'maxinvitenumnew', $group['maxinvitenum'], 'text');
		showsetting('usergroups_edit_invite_maxinviteday', 'maxinvitedaynew', $group['maxinviteday'], 'text');
		showtablefooter();
		showtagfooter('div');

		$raterangearray = array();
		foreach(explode("\n", $group['raterange']) as $range) {
			$range = explode("\t", $range);
			$raterangearray[$range[0]] = array('isself' => $range[1], 'min' => $range[2], 'max' => $range[3], 'mrpd' => $range[4]);
		}

		if($multiset) {
			showtagheader('div', 'credit', $anchor == 'credit');
			showtableheader();
			showtitle('usergroups_edit_credit');
			showsetting('usergroups_edit_credit_exempt_sendpm', 'exemptnew[0]', $group['exempt'][0], 'radio');
			showsetting('usergroups_edit_credit_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			$exempttype = $group['radminid'] ? ($group['radminid'] == 3 ? 1 : 2) : 3;
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_getattch');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio', $exempttype == 1 ? 0 : 'readonly');
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_attachpay');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio', $exempttype == 1 ? 0 : 'readonly');
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_threadpay');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio', $exempttype == 1 ? 0 : 'readonly');

			showtitle('usergroups_edit_credit_allowrate', '', 0);
			for($i = 1; $i <= 8; $i++) {
				if(isset($_G['setting']['extcredits'][$i])) {
					showsetting($_G['setting']['extcredits'][$i]['title'], 'raterangenew['.$i.'][allowrate]', $raterangearray[$i], 'radio');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_isself'], 'raterangenew['.$i.'][isself]', $raterangearray[$i]['isself'], 'radio');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_min'], 'raterangenew['.$i.'][min]', $raterangearray[$i]['min'], 'text');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_max'], 'raterangenew['.$i.'][max]', $raterangearray[$i]['max'], 'text');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_mrpd'], 'raterangenew['.$i.'][mrpd]', $raterangearray[$i]['mrpd'], 'text');
				}
			}
			showtablefooter();
			showtagfooter('div');
		} else {
			showtagheader('div', 'credit', $anchor == 'credit');
			showtableheader();
			showtitle('usergroups_edit_credit');
			showsetting('usergroups_edit_credit_exempt_sendpm', 'exemptnew[0]', $group['exempt'][0], 'radio');
			showsetting('usergroups_edit_credit_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			if($group['radminid']) {
				if($group['radminid'] == 3) {
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio');
				} else {
					echo '<input name="exemptnew[2]" type="hidden" value="1" /><input name="exemptnew[3]" type="hidden" value="1" /><input name="exemptnew[4]" type="hidden" value="1" />'.
						'<input name="exemptnew[5]" type="hidden" value="1" /><input name="exemptnew[6]" type="hidden" value="1" /><input name="exemptnew[7]" type="hidden" value="1" />';
				}
			} else {
				showsetting('usergroups_edit_credit_exempt_getattch', 'exemptnew[2]', $group['exempt'][2], 'radio');
				showsetting('usergroups_edit_credit_exempt_attachpay', 'exemptnew[3]', $group['exempt'][3], 'radio');
				showsetting('usergroups_edit_credit_exempt_threadpay', 'exemptnew[4]', $group['exempt'][4], 'radio');
			}

			echo '<tr><td colspan="2">'.$lang['usergroups_edit_credit_exempt_comment'].'</td></tr>';

			showtablefooter();
			showtableheader('usergroups_edit_credit_allowrate', '');

			$titlecolumn[0] = $lang['name'];
			for($i = 1; $i <= 8; $i++) {
				if(isset($_G['setting']['extcredits'][$i])) {
					$titlecolumn[$i] = $_G['setting']['extcredits'][$i]['title'];
				}
			}
			showsubtitle($titlecolumn);
			$leftcolumn = array('enable', 'usergroups_edit_credit_rate_isself', 'usergroups_edit_credit_rate_min', 'usergroups_edit_credit_rate_max', 'usergroups_edit_credit_rate_mrpd');
			foreach($leftcolumn as $value) {
				echo '<tr><td>'.$lang[$value].'</td>';
				foreach($titlecolumn as $subkey => $subvalue) {
					if(!$subkey) continue;
					if($value == 'enable') {
						echo '<td><input type="checkbox" class="checkbox" name="raterangenew['.$subkey.'][allowrate]" value="1" '.(empty($raterangearray[$subkey]) ? '' : 'checked').'></td>';
					} elseif($value == 'usergroups_edit_credit_rate_isself') {
						echo '<td><input type="checkbox" class="checkbox" name="raterangenew['.$subkey.'][isself]" value="1" '.(empty($raterangearray[$subkey]['isself']) ? '' : 'checked').'></td>';
					} elseif($value == 'usergroups_edit_credit_rate_min') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][min]" size="3" value="'.$raterangearray[$subkey]['min'].'"></td>';
					} elseif($value == 'usergroups_edit_credit_rate_max') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][max]" size="3" value="'.$raterangearray[$subkey]['max'].'"></td>';
					} elseif($value == 'usergroups_edit_credit_rate_mrpd') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][mrpd]" size="3" value="'.$raterangearray[$subkey]['mrpd'].'"></td>';
					}
				}
				echo '</tr>';
			}
			echo '<tr><td class="lineheight" colspan="9">'.$lang['usergroups_edit_credit_rate_tips'].'</td></tr>';
			showtablefooter();
			showtagfooter('div');
		}

		showtagheader('div', 'home', $anchor == 'home');
		showtableheader();
		showtitle('usergroups_edit_home');
		showsetting('usergroups_edit_attach_max_space_size', 'maxspacesizenew', $group['maxspacesize'], 'text');
		showsetting('usergroups_edit_home_allow_blog', 'allowblognew', $group['allowblog'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_blog_mod', 'allowblogmodnew', $group['allowblogmod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_doing', 'allowdoingnew', $group['allowdoing'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_doing_mod', 'allowdoingmodnew', $group['allowdoingmod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_upload', 'allowuploadnew', $group['allowupload'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_upload_mod', 'allowuploadmodnew', $group['allowuploadmod'], 'radio');
		showsetting('usergroups_edit_home_image_max_size', 'maximagesizenew', $group['maximagesize'], 'text');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_share', 'allowsharenew', $group['allowshare'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_share_mod', 'allowsharemodnew', $group['allowsharemod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_poke', 'allowpokenew', $group['allowpoke'], 'radio');
		showsetting('usergroups_edit_home_allow_friend', 'allowfriendnew', $group['allowfriend'], 'radio');
		showsetting('usergroups_edit_home_allow_click', 'allowclicknew', $group['allowclick'], 'radio');
		showsetting('usergroups_edit_home_allow_comment', 'allowcommentnew', $group['allowcomment'], 'radio');
		showsetting('usergroups_edit_home_allow_myop', 'allowmyopnew', $group['allowmyop'], 'radio');
		showsetting('usergroups_edit_home_allow_video_photo_ignore', 'videophotoignorenew', $group['videophotoignore'], 'radio');
		showsetting('usergroups_edit_home_allow_view_video_photo', 'allowviewvideophotonew', $group['allowviewvideophoto'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_html', 'allowspacediyhtmlnew', $group['allowspacediyhtml'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_bbcode', 'allowspacediybbcodenew', $group['allowspacediybbcode'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_imgcode', 'allowspacediyimgcodenew', $group['allowspacediyimgcode'], 'radio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'group', $anchor == 'group');
		showtableheader();
		showtitle('usergroups_edit_group');
		showsetting('usergroups_edit_group_build', 'allowbuildgroupnew', $group['allowbuildgroup'], 'text');
		showsetting('usergroups_edit_group_buildcredits', 'buildgroupcreditsnew', $group['buildgroupcredits'], 'text');
		showsetting('usergroups_edit_post_direct_group', array('allowgroupdirectpostnew', array(
			array(0, $lang['usergroups_edit_post_direct_none']),
			array(1, $lang['usergroups_edit_post_direct_reply']),
			array(2, $lang['usergroups_edit_post_direct_thread']),
			array(3, $lang['usergroups_edit_post_direct_all'])
		)), $group['allowgroupdirectpost'], 'mradio');
		showsetting('usergroups_edit_post_url_group', array('allowgroupposturlnew', array(
			array(0, $lang['usergroups_edit_post_url_banned']),
			array(1, $lang['usergroups_edit_post_url_mod']),
			array(2, $lang['usergroups_edit_post_url_unhandle']),
			array(3, $lang['usergroups_edit_post_url_enable'])
		)), $group['allowgroupposturl'], 'mradio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'portal', $anchor == 'portal');
		showtableheader();
		showtitle('usergroups_edit_portal');
		showsetting('usergroups_edit_portal_allow_comment_article', 'allowcommentarticlenew', $group['allowcommentarticle'], 'text');
		showsetting('usergroups_edit_portal_allow_post_article', 'allowpostarticlenew', $group['allowpostarticle'], 'radio', '', 1);
		showsetting('usergroups_edit_portal_allow_down_local_img', 'allowdownlocalimgnew', $group['allowdownlocalimg'], 'radio');
		showsetting('usergroups_edit_portal_allow_post_article_moderate', 'allowpostarticlemodnew', $group['allowpostarticlemod'], 'radio');
		showtablefooter();
		showtagfooter('div');

		if($pluginsetting) {
			showtagheader('div', 'plugin', $anchor == 'plugin');
			showtableheader();
			foreach($pluginsetting as $setting) {
				showtitle($setting['name']);
				foreach($setting['setting'] as $varid => $var) {
					if($var['type'] != 'select') {
						showsetting($var['title'], 'pluginnew['.$varid.']', $group['plugin'][$varid], $var['type'], '', 0, $var['description']);
					} else {
						showsetting($var['title'], array('pluginnew['.$varid.']', $var['select']), $group['plugin'][$varid], $var['type'], '', 0, $var['description']);
					}
				}
			}
			showtablefooter();
			showtagfooter('div');
		}

		showtableheader();
		showsubmit('detailsubmit', 'submit');
		showtablefooter();
		$_G['showsetting_multi']++;
		}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mgids)));
			showmulti();
		}
		showformfooter();
	} else {

		if(!$multiset) {
			$_GET['multinew'] = array(0 => array('single' => 1));
		}
		$pluginvars = array();
		foreach($_GET['multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_GET[''.$key] = $value;
			}
			$_GET['id'] = $_GET['multi'][$k];
		}
		$group = $mgroup[$k];

		$systemnew = 'private';

		if($group['type'] == 'special' && $group['radminid'] > 0) {

			$radminidnew = $group['radminid'];

		} elseif($group['type'] == 'special') {

			$radminidnew = '0';
			if(!$multiset && $_GET['system_publicnew']) {
				if($_GET['system_dailypricenew'] > 0) {
					if(!$_G['setting']['creditstrans']) {
						cpmsg('usergroups_edit_creditstrans_disabled', '', 'error');
					} else {
						$system_minspannew = $_GET['system_minspannew'] <= 0 ? 1 : $_GET['system_minspannew'];
						$systemnew = intval($_GET['system_dailypricenew'])."\t".intval($system_minspannew);
					}
				} else {
					$systemnew = "0\t0";
				}
			}

		} else {
			$radminidnew = in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
		}

		if(is_array($_GET['raterangenew'])) {
			foreach($_GET['raterangenew'] as $key => $rate) {
				if($key >= 1 && $key <= 8 && $rate['allowrate']) {
					if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
						cpmsg('usergroups_edit_rate_invalid', '', 'error');
					} else {
						$_GET['raterangenew'][$key] = implode("\t", array($key, ($rate['isself'] ? $rate['isself'] : 0), $rate['min'], $rate['max'], $rate['mrpd']));
					}
				} else {
					unset($_GET['raterangenew'][$key]);
				}
			}
		}

		if(in_array($group['groupid'], array(1))) {
			$_GET['allowvisitnew'] = 2;
		}

		$raterangenew = $_GET['raterangenew'] ? implode("\n", $_GET['raterangenew']) : '';
		$maxpricenew = $_GET['maxpricenew'] < 0 ? 0 : intval($_GET['maxpricenew']);
		$maxpostsperhournew = $_GET['maxpostsperhournew'] > 255 ? 255 : intval($_GET['maxpostsperhournew']);
		$maxthreadsperhournew = $_GET['maxthreadsperhournew'] > 255 ? 255 : intval($_GET['maxthreadsperhournew']);

		$extensionarray = array();
		foreach(explode(',', $_GET['attachextensionsnew']) as $extension) {
			if($extension = trim($extension)) {
				$extensionarray[] = $extension;
			}
		}
		$attachextensionsnew = implode(', ', $extensionarray);

		if($_GET['maxtradepricenew'] == $_GET['mintradepricenew'] || $_GET['maxtradepricenew'] < 0 || $_GET['mintradepricenew'] <= 0 || ($_GET['maxtradepricenew'] && $_GET['maxtradepricenew'] < $_GET['mintradepricenew'])) {
			cpmsg('trade_fee_error', '', 'error');
		} elseif(($_GET['maxrewardpricenew'] != 0 && $_GET['minrewardpricenew'] >= $_GET['maxrewardpricenew']) || $_GET['minrewardpricenew'] < 1 || $_GET['minrewardpricenew'] < 0 || $_GET['maxrewardpricenew'] < 0) {
			cpmsg('reward_credits_error', '', 'error');
		}

		$exemptnewbin = '';
		for($i = 0;$i < 8;$i++) {
			$exemptnewbin = intval($_GET['exemptnew'][$i]).$exemptnewbin;
		}
		$exemptnew = bindec($exemptnewbin);

		$tradesticknew = $_GET['tradesticknew'] > 0 ? intval($_GET['tradesticknew']) : 0;
		$maxinvitedaynew = $_GET['maxinvitedaynew'] > 0 ? intval($_GET['maxinvitedaynew']) : 10;
		$maxattachsizenew = $_GET['maxattachsizenew'] > 0 ? intval($_GET['maxattachsizenew'] * 1024) : 0;
		$maximagesizenew = $_GET['maximagesizenew'] > 0 ? intval($_GET['maximagesizenew'] * 1024) : 0;
		$maxsizeperdaynew = $_GET['maxsizeperdaynew'] > 0 ? intval($_GET['maxsizeperdaynew'] * 1024) : 0;
		$maxattachnumnew = $_GET['maxattachnumnew'] > 0 ? intval($_GET['maxattachnumnew']) : 0;
		$allowrecommendnew = $_GET['allowrecommendnew'] > 0 ? intval($_GET['allowrecommendnew']) : 0;
		$dataarr = array(
			'grouptitle' => $_GET['grouptitlenew'],
			'radminid' => $radminidnew,
			'allowvisit' => $_GET['allowvisitnew'],
			'allowsendpm' => $_GET['allowsendpmnew'],
			'maxinvitenum' => $_GET['maxinvitenumnew'],
			'maxinviteday' => $maxinvitedaynew,
			'allowinvite' => $_GET['allowinvitenew'],
			'allowmailinvite' => $_GET['allowmailinvitenew'],
			'inviteprice' => $_GET['invitepricenew']
		);
		if(!$multiset) {
			$dataarr['system'] = $systemnew;
			if($_FILES['iconnew']) {
				$data = array('extid' => "$_GET[id]");
				$iconnew = upload_icon_banner($data, $_FILES['iconnew'], 'usergroup_icon');
			} else {
				$iconnew = $_GET['iconnew'];
			}
			if($iconnew) {
				$dataarr['icon'] = $iconnew;
			}
			if($_GET['deleteicon']) {
				$valueparse = parse_url($group['icon']);
				if(!isset($valueparse['host'])) {
					$group['icon'] = str_replace(array('..', '//'), array('','/'), $group['icon']);
					@unlink($_G['setting']['attachurl'].'common/'.$group['icon']);
				}
				$dataarr['icon'] = '';
			}
		}
		C::t('common_usergroup')->update($_GET['id'], $dataarr);

		if($pluginsetting) {
			foreach($_GET['pluginnew'] as $pluginvarid => $value) {
				$pluginvars[$pluginvarid][$_GET['id']] = $value;
			}
		}

		C::t('forum_onlinelist')->update_by_groupid($_GET['id'], array('title' => $_GET['grouptitlenew']));

		$dataarr = array(
			'readaccess' => $_GET['readaccessnew'],
			'allowpost' => $_GET['allowpostnew'],
			'allowreply' => $_GET['allowreplynew'],
			'allowpostpoll' => $_GET['allowpostpollnew'],
			'allowpostreward' => $_GET['allowpostrewardnew'],
			'allowposttrade' => $_GET['allowposttradenew'],
			'allowpostactivity' => $_GET['allowpostactivitynew'],
			'allowdirectpost' => $_GET['allowdirectpostnew'],
			'allowgetattach' => $_GET['allowgetattachnew'],
			'allowgetimage' => $_GET['allowgetimagenew'],
			'allowpostattach' => $_GET['allowpostattachnew'],
			'allowvote' => $_GET['allowvotenew'],
			'allowsearch' => bindec(intval($_GET['allowsearchnew'][7]).intval($_GET['allowfulltextnew']).intval($_GET['allowsearchnew'][5]).intval($_GET['allowsearchnew'][4]).intval($_GET['allowsearchnew'][3]).intval($_GET['allowsearchnew'][2]).intval($_GET['allowsearchnew'][1])),
			'allowcstatus' => $_GET['allowcstatusnew'],
			'allowinvisible' => $_GET['allowinvisiblenew'],
			'allowtransfer' => $_GET['allowtransfernew'],
			'allowsetreadperm' => $_GET['allowsetreadpermnew'],
			'allowsetattachperm' => $_GET['allowsetattachpermnew'],
			'allowpostimage' => $_GET['allowpostimagenew'],
			'allowposttag' => $_GET['allowposttagnew'],
			'allowhidecode' => $_GET['allowhidecodenew'],
			'allowmediacode' => $_GET['allowmediacodenew'],
			'allowbegincode' => $_GET['allowbegincodenew'],
			'allowhtml' => $_GET['allowhtmlnew'],
			'allowanonymous' => $_GET['allowanonymousnew'],
			'allowsigbbcode' => $_GET['allowsigbbcodenew'],
			'allowsigimgcode' => $_GET['allowsigimgcodenew'],
			'allowmagics' => $_GET['allowmagicsnew'],
			'disableperiodctrl' => $_GET['disableperiodctrlnew'],
			'reasonpm' => $_GET['reasonpmnew'],
			'maxprice' => $maxpricenew,
			'maxsigsize' => $_GET['maxsigsizenew'],
			'maxspacesize' => $_GET['maxspacesizenew'],
			'maxattachsize' => $maxattachsizenew,
			'maximagesize' => $maximagesizenew,
			'maxsizeperday' => $maxsizeperdaynew,
			'maxpostsperhour' => $maxpostsperhournew,
			'maxthreadsperhour' => $maxthreadsperhournew,
			'attachextensions' => $attachextensionsnew,
			'mintradeprice' => $_GET['mintradepricenew'],
			'maxtradeprice' => $_GET['maxtradepricenew'],
			'minrewardprice' => $_GET['minrewardpricenew'],
			'maxrewardprice' => $_GET['maxrewardpricenew'],
			'magicsdiscount' => $_GET['magicsdiscountnew'] >= 0 && $_GET['magicsdiscountnew'] < 10 ? $_GET['magicsdiscountnew'] : 0,
			'maxmagicsweight' => $_GET['maxmagicsweightnew'] >= 0 && $_GET['maxmagicsweightnew'] <= 60000 ? $_GET['maxmagicsweightnew'] : 1,
			'allowpostdebate' => $_GET['allowpostdebatenew'],
			'tradestick' => $tradesticknew,
			'maxattachnum' => $maxattachnumnew,
			'allowposturl' => $_GET['allowposturlnew'],
			'allowrecommend' => $allowrecommendnew,
			'allowpostrushreply' => $_GET['allowpostrushreplynew'],
			'maxfriendnum' => $_GET['maxfriendnumnew'],
			'seccode' => $_GET['seccodenew'],
			'forcesecques' => $_GET['forcesecquesnew'],
			'forcelogin' => $_GET['forceloginnew'],
			'domainlength' => $_GET['domainlengthnew'],
			'disablepostctrl' => $_GET['disablepostctrlnew'],
			'allowblog' => $_GET['allowblognew'],
			'allowdoing' => $_GET['allowdoingnew'],
			'allowupload' => $_GET['allowuploadnew'],
			'allowshare' => $_GET['allowsharenew'],
			'allowblogmod' => $_GET['allowblogmodnew'],
			'allowdoingmod' => $_GET['allowdoingmodnew'],
			'allowuploadmod' => $_GET['allowuploadmodnew'],
			'allowsharemod' => $_GET['allowsharemodnew'],
			'allowpoke' => $_GET['allowpokenew'],
			'allowfriend' => $_GET['allowfriendnew'],
			'allowclick' => $_GET['allowclicknew'],
			'allowcomment' => $_GET['allowcommentnew'],
			'allowcommentarticle' => intval($_GET['allowcommentarticlenew']),
			'allowmyop' => $_GET['allowmyopnew'],
			'allowcommentpost' => bindec(intval($_GET['allowcommentpostnew'][2]).intval($_GET['allowcommentpostnew'][1])),
			'videophotoignore' => $_GET['videophotoignorenew'],
			'allowviewvideophoto' => $_GET['allowviewvideophotonew'],
			'allowspacediyhtml' => $_GET['allowspacediyhtmlnew'],
			'allowspacediybbcode' => $_GET['allowspacediybbcodenew'],
			'allowspacediyimgcode' => $_GET['allowspacediyimgcodenew'],
			'allowstatdata' => $_GET['allowstatdatanew'],
			'allowpostarticle' => $_GET['allowpostarticlenew'],
			'allowpostarticlemod' => $_GET['allowpostarticlemodnew'],
			'allowbuildgroup' => $_GET['allowbuildgroupnew'],
			'buildgroupcredits' => $_GET['buildgroupcreditsnew'],
			'allowgroupdirectpost' => intval($_GET['allowgroupdirectpostnew']),
			'allowgroupposturl' => intval($_GET['allowgroupposturlnew']),
			'edittimelimit' => intval($_GET['edittimelimitnew']),
			'allowcommentreply' => intval($_GET['allowcommentreplynew']),
			'allowdownlocalimg' => intval($_GET['allowdownlocalimgnew']),
			'allowdownremoteimg' => intval($_GET['allowdownremoteimgnew']),
			'allowcommentitem' => intval($_GET['allowcommentitemnew']),
			'allowat' => intval($_GET['allowatnew']),
			'allowreplycredit' => intval($_GET['allowreplycreditnew']),
			'allowsetpublishdate' => intval($_GET['allowsetpublishdatenew']),
			'allowcommentcollection' => intval($_GET['allowcommentcollectionnew']),
			'allowimgcontent' => intval($_GET['allowimgcontentnew']),
			'allowcreatecollection' => intval($_GET['allowcreatecollectionnew']),
			'allowfollowcollection' => intval($_GET['allowfollowcollectionnew']),
			'exempt' => $exemptnew,
			'raterange' => $raterangenew,
			'ignorecensor' => intval($_GET['ignorecensornew']),
			'allowsendallpm' => intval($_GET['allowsendallpmnew']),
			'allowsendpmmaxnum' => intval($_GET['allowsendpmmaxnumnew']),
			'closead' => intval($_GET['closeadnew']),
		);
		C::t('common_usergroup_field')->update($_GET['id'], $dataarr);

		if($_G['setting']['threadplugins']) {
			$allowthreadplugin = C::t('common_setting')->fetch('allowthreadplugin', true);
			$allowthreadplugin[$_GET['id']] = $_GET['allowthreadpluginnew'];
			C::t('common_setting')->update('allowthreadplugin', $allowthreadplugin);
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

		updatecache(array('setting', 'usergroups', 'onlinelist', 'groupreadaccess'));

		cpmsg('usergroups_edit_succeed', 'action=usergroups&operation=edit&'.($multiset ? 'multi='.implode(',', $_GET['multi']) : 'id='.$_GET['id']).'&anchor='.$_GET['anchor'], 'succeed');
	}

} elseif($operation == 'copy') {

	loadcache('usergroups');

	$source = intval($_GET['source']);
	$sourceusergroup = $_G['cache']['usergroups'][$source];

	if(empty($sourceusergroup)) {
		cpmsg('usergroups_copy_source_invalid', '', 'error');
	}

	$delfields = array(
		'usergroups'	=> array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'icon', 'groupavatar'),
	);
	$fields = array(
		'usergroups'		=> C::t('common_usergroup')->fetch_table_struct(),
		'usergroupfields'	=> C::t('common_usergroup_field')->fetch_table_struct(),
	);

	if(!submitcheck('copysubmit')) {

		$groupselect = array();
		foreach(C::t('common_usergroup')->fetch_all_not(array(6, 7), true) as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

		$usergroupselect = '<select name="target[]" size="10" multiple="multiple">'.$groupselect.'</select>';
		$optselect = '<select name="options[]" size="10" multiple="multiple">';
		$fieldarray = array_merge($fields['usergroups'], $fields['usergroupfields']);
		$listfields = array_diff($fieldarray, $delfields['usergroups']);
		foreach($listfields as $field) {
			if(isset($lang['project_option_group_'.$field])) {
				$optselect .= '<option value="'.$field.'">'.$lang['project_option_group_'.$field].'</option>';
			}
		}
		$optselect .= '</select>';
		shownav('user', 'usergroups_copy');
		showsubmenu('usergroups_copy');
		showtips('usergroups_copy_tips');
		showformheader('usergroups&operation=copy');
		showhiddenfields(array('source' => $source));
		showtableheader();
		showtitle('usergroups_copy');
		showsetting(cplang('usergroups_copy_source').':','','', $sourceusergroup['grouptitle']);
		showsetting('usergroups_copy_target', '', '', $usergroupselect);
		showsetting('usergroups_copy_options', '', '', $optselect);
		showsubmit('copysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$gids = $comma = '';
		if(is_array($_GET['target']) && count($_GET['target'])) {
			foreach($_GET['target'] as $key => $gid) {
				$_GET['target'][$key] = intval($gid);
				if(empty($_GET['target'][$key]) || $_GET['target'][$key] == $source) {
					unset($_GET['target'][$key]);
				}
			}
		}
		if(empty($_GET['target'])) {
			cpmsg('usergroups_copy_target_invalid', '', 'error');
		}

		$groupoptions = array();
		if(is_array($_GET['options']) && !empty($_GET['options'])) {
			foreach($_GET['options'] as $option) {
				if($option = trim($option)) {
					if(in_array($option, $fields['usergroups'])) {
						$groupoptions['common_usergroup'][] = $option;
					} elseif(in_array($option, $fields['usergroupfields'])) {
						$groupoptions['common_usergroup_field'][] = $option;
					}
				}
			}
		}

		if(empty($groupoptions)) {
			cpmsg('usergroups_copy_options_invalid', '', 'error');
		}
		foreach(array('common_usergroup', 'common_usergroup_field') as $table) {
			if(is_array($groupoptions[$table]) && !empty($groupoptions[$table])) {
				$sourceusergroup = C::t($table)->fetch($source);
				if(!$sourceusergroup) {
					cpmsg('usergroups_copy_source_invalid', '', 'error');
				}
				foreach($sourceusergroup as $key=>$value) {
					if(!in_array($key, $groupoptions[$table])) {
						unset($sourceusergroup[$key]);
					}
				}
				C::t($table)->update($_GET['target'], $sourceusergroup);
			}
		}

		updatecache('usergroups');
		cpmsg('usergroups_copy_succeed', 'action=usergroups', 'succeed');

	}

} elseif($operation == 'merge') {

	loadcache('usergroups');

	$source = intval($_GET['source']);
	$sourceusergroup = $_G['cache']['usergroups'][$source];

	if(empty($sourceusergroup) || $sourceusergroup['type'] == 'system' || ($sourceusergroup['type'] == 'special' && $sourceusergroup['radminid'])) {
		cpmsg('usergroups_copy_source_invalid', '', 'error');
	}

	if(!submitcheck('copysubmit')) {

		$groupselect = array();
		foreach(C::t('common_usergroup')->fetch_all_not(array(6, 7), true) as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '');

		$usergroupselect = '<select name="target" size="10">'.$groupselect.'</select>';

		shownav('user', 'usergroups_merge');
		showsubmenu('usergroups_merge');
		showtips('usergroups_merge_tips');
		showformheader('usergroups&operation=merge');
		showhiddenfields(array('source' => $source));
		showtableheader();
		showtitle('usergroups_copy');
		showsetting(cplang('usergroups_copy_source').':','','', $sourceusergroup['grouptitle']);
		showsetting('usergroups_merge_target', '', '', $usergroupselect);
		showsetting('usergroups_merge_delete_source', 'delete_source', 0, 'radio');
		showsubmit('copysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$target = intval($_GET['target']);
		$targetusergroup = $_G['cache']['usergroups'][$target];

		if(empty($targetusergroup) || $targetusergroup['type'] == 'system' || ($targetusergroup['type'] == 'special' && $targetusergroup['radminid'])) {
			cpmsg('usergroups_copy_target_invalid', '', 'error');
		}

		C::t('common_member')->update_groupid_by_groupid($source, $target);
		if(helper_dbtool::isexisttable('common_member_archive')) {
			C::t('common_member_archive')->update_groupid_by_groupid($source, $target);
		}

		if($_GET['delete_source']) {
			C::t('common_usergroup')->delete($source, $sourceusergroup['type']);
			C::t('common_usergroup_field')->delete($source);
			C::t('forum_onlinelist')->delete_by_groupid($source);
		}

		updatecache('usergroups');
		cpmsg('usergroups_merge_succeed', 'action=usergroups', 'succeed');

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

function deletegroupcache($groupidarray) {
	if(!empty($groupidarray) && is_array($groupidarray)) {
		$cachenames = array();
		foreach ($groupidarray as $id) {
			if(($id = dintval($id))) {
				$cachenames['usergroup_'.$id] = 'usergroup_'.$id;
				$cachenames['admingroup_'.$id] = 'admingroup_'.$id;
			}
		}
		if(!empty($cachenames)) {
			C::t('common_syscache')->delete($cachenames);
		}
	}
}


?>