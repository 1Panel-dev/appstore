<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_founder.php 33912 2013-08-30 06:00:06Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
	exit('Access Denied');
}

if($operation == 'perm') {

	$do = !in_array(getgpc('do'), array('group', 'member', 'gperm', 'notifyusers')) ? 'member' : getgpc('do');
	shownav('founder', 'menu_founder_perm');

	if($do == 'group') {
		$id = intval(getgpc('id'));

		if(!$id) {
			foreach(C::t('common_admincp_group')->range() as $group) {
				$groups[$group['cpgroupid']] = $group['cpgroupname'];
			}
			if(!submitcheck('submit')) {
				showsubmenu('menu_founder_perm', array(
					array('nav_founder_perm_member', 'founder&operation=perm&do=member',  0),
					array('nav_founder_perm_group', 'founder&operation=perm&do=group', 1),
					array('nav_founder_perm_notifyusers', 'founder&operation=perm&do=notifyusers', 0),
				));
				showformheader('founder&operation=perm&do=group');
				showtableheader();
				showsubtitle(array('', 'founder_cpgroupname', ''));
				foreach($groups as $id => $group) {
					showtablerow('style="height:20px"', array('class="td25"', 'class="td24"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$id\">",
						"<input type=\"text\" class=\"txtnobd\" onblur=\"this.className='txtnobd'\" onfocus=\"this.className='txt'\" size=\"15\" name=\"name[$id]\" value=\"$group\">",
						'<a href="'.ADMINSCRIPT.'?action=founder&operation=perm&do=group&id='.$id.'">'.cplang('edit').'</a>'
						));
				}
				showtablerow('style="height:20px"', array(), array(cplang('add_new'), '<input class="txt" type="text" name="newcpgroupname" value="" />', ''));
				showsubmit('submit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} else {
				if(!empty($_GET['newcpgroupname'])) {
					if(C::t('common_admincp_group')->fetch_by_cpgroupname($_GET['newcpgroupname'])) {
						cpmsg('founder_perm_group_name_duplicate', '', 'error', array('name' => $_GET['newcpgroupname']));
					}
					C::t('common_admincp_group')->insert(array('cpgroupname' => strip_tags($_GET['newcpgroupname'])));
				}
				if(!empty($_GET['delete'])) {
					C::t('common_admincp_perm')->delete_by_cpgroupid_perm($_GET['delete']);
					C::t('common_admincp_member')->update_cpgroupid_by_cpgroupid($_GET['delete'], array('cpgroupid' => 0));
					C::t('common_admincp_group')->delete($_GET['delete']);
				}
				if(!empty($_GET['name'])) {
					foreach($_GET['name'] as $id => $name) {
						if($groups[$id] != $name) {
							$cpgroupid = ($cpgroup = C::t('common_admincp_group')->fetch_by_cpgroupname($name)) ? $cpgroup['cpgroupid'] : 0;
							if($cpgroupid && $_GET['name'][$cpgroupid] == $groups[$cpgroupid]) {
								cpmsg('founder_perm_group_name_duplicate', '', 'error', array('name' => $name));
							}
							C::t('common_admincp_group')->update($id, array('cpgroupname' => $name));
						}
					}
				}
				cpmsg('founder_perm_group_update_succeed', 'action=founder&operation=perm&do=group', 'succeed');
			}
		} else {
			if(!submitcheck('submit')) {

				showpermstyle();
				$perms = array();
				foreach(C::t('common_admincp_perm')->fetch_all_by_cpgroupid($id) as $perm) {
					$perms[] = $perm['perm'];
				}

				$cpgroupname = ($cpgroup = C::t('common_admincp_group')->fetch($id)) ? $cpgroup['cpgroupname'] : '';
				$data = getactionarray();
				$grouplist = '';
				foreach(C::t('common_admincp_group')->range() as $ggroup) {
					$grouplist .= '<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=founder&operation=perm&do=group&switch=yes&id='.$ggroup['cpgroupid'].'&scrolltop=\'+document.documentElement.scrollTop"'.($_GET['id'] == $ggroup['cpgroupid'] ? ' class="current"' : '').'>'.$ggroup['cpgroupname'].'</a>';
				}
				$grouplist = '<span id="cpgselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'cpgselect_menu\').style.top=(parseInt($(\'cpgselect_menu\').style.top)-document.documentElement.scrollTop)+\'px\'">'.cplang('founder_group_switch').'<em>&nbsp;&nbsp;</em></span>'.
					'<div id="cpgselect_menu" class="popupmenu_popup" style="display:none">'.$grouplist.'</div>';

				showsubmenu('menu_founder_groupperm', array(array()), $grouplist, array('group' => $cpgroupname));
				showformheader('founder&operation=perm&do=group&id='.$id);
				showtableheader();
				foreach($data['cats'] as $topkey) {
					if(!$data['actions'][$topkey]) {
						continue;
					}
					$checkedall = true;
					$row = '<tr><td class="vtop" id="perms_'.$topkey.'">';
					foreach($data['actions'][$topkey] as $k => $item) {
						if(!$item) {
							continue;
						}
						$checked = @in_array($item[1], $perms);
						if(!$checked) {
							$checkedall = false;
						}
						$row .= $item[1] ? '<div class="item'.($checked ? ' checked' : '').'"><a class="right" title="'.cplang('config').'" href="'.ADMINSCRIPT.'?frames=yes&action=founder&operation=perm&do=gperm&gset='.$topkey.'_'.$k.'" target="_blank">&nbsp;</a><label class="txt"><input name="permnew[]" value="'.$item[1].'" class="checkbox" type="checkbox" '.($checked ? 'checked="checked" ' : '').' onclick="checkclk(this)" />'.cplang($item[0]).'</label></div>' : '';
					}
					$row .= '</td></tr>';
					if($topkey != 'setting') {
						showtitle('<label><input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_'.$topkey.'\')" '.($checkedall ? 'checked="checked" ' : '').'/> '.cplang('header_'.$topkey).'</label>');
					} else {
						showtitle('founder_perm_setting');
					}
					echo $row;
				}
				showsubmit('submit');
				showtablefooter();
				showformfooter();
				if(!empty($_GET['switch'])) {
					echo '<script type="text/javascript">showMenu({\'ctrlid\':\'cpgselect\',\'pos\':\'34\'});</script>';
				}

			} else {
				C::t('common_admincp_perm')->delete_by_cpgroupid_perm($id);
				if($_GET['permnew']) {
					foreach($_GET['permnew'] as $perm) {
						C::t('common_admincp_perm')->insert(array('cpgroupid' => $id, 'perm' => $perm));
					}
				}

				cpmsg('founder_perm_groupperm_update_succeed', 'action=founder&operation=perm&do=group', 'succeed');
			}
		}

	} elseif($do == 'member') {

		$founders = $_G['config']['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes($_G['config']['admincp']['founder']))) : array();
		if($founders) {
			$founderexists = true;
			$fuid = $fuser = array();
			foreach($founders as $founder) {
				if(is_numeric($founder)) {
					$fuid[] = $founder;
				} else {
					$fuser[] = $founder;
				}
			}
			$founders = array();
			if($fuid) {
				$founders = $founders + C::t('common_member')->fetch_all($fuid, false, 0);
			}
			if($fuser) {
				$founders = $founders + C::t('common_member')->fetch_all_by_username($fuser);
			}
		} else {
			$founderexists = false;
			$founders = C::t('common_member')->fetch_all_by_adminid(1);
		}
		$id = empty($_GET['id']) ? 0 : $_GET['id'];

		if(!$id) {
			if(!submitcheck('submit')) {
				showsubmenu('menu_founder_perm', array(
					array('nav_founder_perm_member', 'founder&operation=perm&do=member',  1),
					array('nav_founder_perm_group', 'founder&operation=perm&do=group', 0),
					array('nav_founder_perm_notifyusers', 'founder&operation=perm&do=notifyusers', 0),
				));
				$groupselect = '<select name="newcpgroupid"><option value="0">'.cplang('founder_master').'</option>';
				$groups = array();
				foreach(C::t('common_admincp_group')->range() as $group) {
					$groupselect .= '<option value="'.$group['cpgroupid'].'">'.$group['cpgroupname'].'</option>';
					$groups[$group['cpgroupid']] = $group['cpgroupname'];
				}
				$groupselect .= '</select>';
				$members = $adminmembers = array();
				$adminmembers = C::t('common_admincp_member')->range();
				foreach ($adminmembers as $adminmember) {
					$adminmembers[$adminmember['uid']] = $adminmember;
				}
				foreach($founders as $uid => $founder) {
					$members[$uid] = array('uid' => $uid, 'username' => $founder['username'], 'cpgroupname' => cplang('founder_admin'));
				}
				if($adminmembers) {
					foreach(C::t('common_member')->fetch_all(array_keys($adminmembers), false, 0) as $member) {
						if(isset($members[$member['uid']])) {
							C::t('common_admincp_member')->delete($member['uid']);
							continue;
						}
						$member['cpgroupname'] = !empty($adminmembers[$member['uid']]['cpgroupid']) ? $groups[$adminmembers[$member['uid']]['cpgroupid']] : cplang('founder_master');
						if(!$founderexists && in_array($member['uid'], array_keys($founders))) {
							$member['cpgroupname'] = cplang('founder_admin');
						}
						$members[$member['uid']] = $member;
					}
				}
				if(!$founderexists) {
					showtips(cplang('home_security_nofounder').cplang('home_security_founder'));
				} else {
					showtips('home_security_founder');
				}
				showformheader('founder&operation=perm&do=member');
				showtableheader();
				showsubtitle(array('', 'founder_username', 'founder_usergname', ''));
				foreach($members as $id => $member) {
					$isfounder = array_key_exists($id, $founders);
					showtablerow('style="height:20px"', array('class="td25"', 'class="td24"', 'class="td24"'), array(
						!$isfounder || isset($adminmembers[$member['uid']]['cpgroupid']) ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$id]\">" : '',
						"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
						$member['cpgroupname'],
						!$isfounder && $adminmembers[$member['uid']]['cpgroupid'] ? '<a href="'.ADMINSCRIPT.'?action=founder&operation=perm&do=member&id='.$id.'">'.cplang('edit').'</a>' : ''
						));
				}
				showtablerow('style="height:20px"', array('class="td25"', 'class="td24"', 'class="td24"'), array(cplang('add_new'), '<input class="txt" type="text" name="newcpusername" value="" />', $groupselect, ''));
				showsubmit('submit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} else {
				if(!empty($_GET['newcpusername'])) {
					$newcpuid = C::t('common_member')->fetch_uid_by_username($_GET['newcpusername']);
					if(!$newcpuid) {
						cpmsg('founder_perm_member_noexists', '', 'error', array('name' => $_GET['newcpusername']));
					}
					if(C::t('common_admincp_member')->count_by_uid($newcpuid) || array_key_exists($newcpuid, $founders)) {
						cpmsg('founder_perm_member_duplicate', '', 'error', array('name' => $_GET['newcpusername']));
					}
					C::t('common_admincp_member')->insert(array('uid' => $newcpuid, 'cpgroupid' => $_GET['newcpgroupid']));
				}
				if(!empty($_GET['delete'])) {
					C::t('common_admincp_member')->delete($_GET['delete']);
				}
				updatecache('founder');
				cpmsg('founder_perm_member_update_succeed', 'action=founder&operation=perm&do=member', 'succeed');
			}
		} else {
			if(!submitcheck('submit')) {
				$member = C::t('common_admincp_member')->fetch($id);
				if(!$member) {
					cpmsg('founder_perm_member_noexists', '', 'error');
				}
				$user = getuserbyuid($id);
				$username = $user['username'];
				$cpgroupid = empty($_GET['cpgroupid']) ? $member['cpgroupid'] : $_GET['cpgroupid'];
				$member['customperm'] = empty($_GET['cpgroupid']) || $_GET['cpgroupid'] == $member['cpgroupid'] ? dunserialize($member['customperm']) : array();
				$perms = array();
				foreach(C::t('common_admincp_perm')->fetch_all_by_cpgroupid($cpgroupid) as $perm) {
					$perms[] = $perm['perm'];
				}
				$data = getactionarray();

				$groupselect = '<select name="cpgroupidnew" onchange="location.href=\''.ADMINSCRIPT.'?action=founder&operation=perm&do=member&id='.$id.'&cpgroupid=\' + this.value">';
				foreach(C::t('common_admincp_group')->range() as $group) {
					$groupselect .= '<option value="'.$group['cpgroupid'].'"'.($group['cpgroupid'] == $cpgroupid ? ' selected="selected"' : '').'>'.$group['cpgroupname'].'</option>';
				}
				$groupselect .= '</select>';

				showpermstyle();
				showsubmenu('menu_founder_memberperm', array(array()), '', array('username' => $username));

				showformheader('founder&operation=perm&do=member&id='.$id);
				showtableheader();
				showsetting('founder_usergname', '', '', $groupselect);
				showtablefooter();
				showtableheader();
				foreach($data['cats'] as $topkey) {
					if(!$data['actions'][$topkey]) {
						continue;
					}
					$checkedall = true;
					$row = '<tr><td class="vtop" id="perms_'.$topkey.'">';
					foreach($data['actions'][$topkey] as $item) {
						if(!$item) {
							continue;
						}
						$checked = @in_array($item[1], $perms);
						$customchecked = @in_array($item[1], $member['customperm']);
						$extra = $checked ? ($customchecked ? '' : 'checked="checked" ').' onclick="checkclk(this)"' : 'disabled="disabled" ';
						if(!$checked || $customchecked) {
							$checkedall = false;
						}
						$row .= '<div class="item'.($checked && !$customchecked ? ' checked' : '').'"><label class="txt"><input name="permnew[]" value="'.$item[1].'" class="checkbox" type="checkbox" '.$extra.'/>'.cplang($item[0]).'</label></div>';
					}
					$row .= '</td></tr>';
					if($topkey != 'setting') {
						showtitle('<input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_'.$topkey.'\')" '.($checkedall ? 'checked="checked" ' : '').'/> '.cplang('header_'.$topkey).'</label>');
					} else {
						showtitle('founder_perm_setting');
					}
					echo $row;
				}
				showsubmit('submit');
				showtablefooter();
				showformfooter();
			} else {
				$_permnew = !empty($_GET['permnew']) ? $_GET['permnew'] : array();
				$cpgroupidnew = $_GET['cpgroupidnew'];
				$dbperms = C::t('common_admincp_perm')->fetch_all_by_cpgroupid($cpgroupidnew);
				$perms = array();
				foreach($dbperms as $dbperm) {
					$perms[] = $dbperm['perm'];
				}
				$customperm = serialize(array_diff($perms, $_permnew));
				C::t('common_admincp_member')->update($id, array('cpgroupid' => $cpgroupidnew, 'customperm' => $customperm));
				cpmsg('founder_perm_member_update_succeed', 'action=founder&operation=perm&do=member', 'succeed');
			}
		}

	} elseif($do == 'gperm' && !empty($_GET['gset'])) {

		$gset = $_GET['gset'];
		list($topkey, $k) = explode('_', $gset);
		$data = getactionarray();
		$gset = $data['actions'][$topkey][$k];
		if(!$gset) {
			cpmsg('undefined_action', '', 'error');
		}
		if(!submitcheck('submit')) {
			$allperms = C::t('common_admincp_perm')->fetch_all_by_perm($gset[1]);
			$groups = C::t('common_admincp_group')->range();
			showsubmenu('menu_founder_permgrouplist', array(array()), '', array('perm' => cplang($gset[0])));

			showformheader('founder&operation=perm&do=gperm&gset='.$_GET['gset']);
			showtableheader();
			showsubtitle(array('', 'founder_usergname'));
			foreach($groups as $id => $group) {
				showtablerow('style="height:20px"', array('class="td25"', ''), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"permnew[]\" ".($allperms[$group['cpgroupid']]['perm'] ? 'checked="checked"' : '')." value=\"$id\">",
					$group['cpgroupname']
					));
			}
			showsubmit('submit');
			showtablefooter();
			showformfooter();
		} else {
			foreach(C::t('common_admincp_group')->range() as $group) {
				if(in_array($group['cpgroupid'], $_GET['permnew'])) {
					C::t('common_admincp_perm')->insert(array('cpgroupid' => $group['cpgroupid'], 'perm' => $gset[1]), false, true);
				} else {
					C::t('common_admincp_perm')->delete_by_cpgroupid_perm($group['cpgroupid'], $gset[1]);
				}
			}
			cpmsg('founder_perm_gperm_update_succeed', 'action=founder&operation=perm', 'succeed');
		}

	} elseif($do == 'notifyusers') {
		$notifyusers = dunserialize($_G['setting']['notifyusers']);
		$notifytypes = explode(',', $_G['setting']['adminnotifytypes']);
		if(!submitcheck('submit')) {
			showpermstyle();
			showsubmenu('menu_founder_perm', array(
				array('nav_founder_perm_member', 'founder&operation=perm&do=member',  0),
				array('nav_founder_perm_group', 'founder&operation=perm&do=group', 0),
				array('nav_founder_perm_notifyusers', 'founder&operation=perm&do=notifyusers', 1),
			));
			showtips('founder_notifyusers_tips');
			showformheader('founder&operation=perm&do=notifyusers');
			showtableheader();
			showsubtitle(array('', 'username', '', 'founder_notifyusers_types'));
			foreach($notifyusers as $uid => $user) {
				$types = '';
				foreach($notifytypes as $key => $typename) {
					$checked = $user['types'][$key] ? ' checked' : '';
					if(substr($typename, 0, 7) == 'verify_') {
						$i = substr($typename, -1, 1);
						if($_G['setting']['verify'][$i]['available']) {
							$tname = $_G['setting']['verify'][$i]['title'];
						} else {
							continue;
						}
					} else {
						$tname = cplang('founder_notidyusers_'.$typename);
					}
					$types .= "<div class=\"item$checked\"><label class=\"txt\"><input class=\"checkbox\" onclick=\"checkclk(this)\" type=\"checkbox\" name=\"notifytypes_{$uid}[{$typename}]\" value=\"1\"$checked>".$tname.'</label></div>';
				}
				showtablerow('style="height:20px"', array('class="td25"', 'class="td24"', 'class="td25"', 'class="vtop"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$uid\">",
					"<input type=\"hidden\" class=\"txtnobd\" name=\"name[$uid]\" value=\"$user[username]\">$user[username]",
					'<input name="chkall_'.$uid.'" id="chkall_'.$uid.'" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'notifytypes_'.$uid.'\', \'chkall_'.$uid.'\', 1)" />'.cplang('select_all'),
					$types
					));
			}
			showtablerow('style="height:20px"', array('', 'colspan="3"'), array(cplang('add_new'), '<input class="txt" type="text" name="newusername" value="" />'));
			showsubmit('submit', 'submit', 'del');
			showtablefooter();
			showformfooter();
		} else {
			$newnotifyusers = array();
			if($_GET['name']) {
				foreach($_GET['name'] as $uid => $username) {
					if($_GET['delete'] && in_array($uid, $_GET['delete'])) {
						continue;
					}
					$types = '';
					foreach($notifytypes as $typename) {
						$types .= intval($_GET['notifytypes_'.$uid][$typename]);
					}
					$newnotifyusers[$uid] = array('username' => $username, 'types' => $types);
				}
			}
			if($_GET['newusername']) {
				$newusername = addslashes($_GET['newusername']);
				$newuid = C::t('common_member')->fetch_uid_by_username($newusername);
				if($newuid) {
					$newnotifyusers[$newuid] = array('username' => $newusername, 'types' => '');
				}
			}
			C::t('common_setting')->update('notifyusers', $newnotifyusers);
			updatecache('setting');
			cpmsg('founder_perm_notifyusers_succeed', 'action=founder&operation=perm&do=notifyusers', 'succeed');
		}
	}
}

function getactionarray() {
	$isfounder = false;
	require './source/admincp/admincp_menu.php';
	require './source/admincp/admincp_perm.php';
	unset($menu['cloud'][0]);
	unset($topmenu['index'], $menu['index']);
	$actioncat = $actionarray = array();
	$actioncat[] = 'setting';
	$actioncat = array_merge($actioncat, array_keys($topmenu));
	$actionarray['setting'][] = array('founder_perm_allowpost', '_allowpost');
	foreach($menu as $tkey => $items) {
		foreach($items as $item) {
			$actionarray[$tkey][] = $item;
		}
	}
	return array('actions' => $actionarray, 'cats' => $actioncat);
}

function showpermstyle() {
	echo <<<EOF
	<style>
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
.vtop .right, .item .right{ padding: 0 10px; line-height: 22px; background: url('static/image/admincp/bg_repno.gif') no-repeat -286px -145px; font-weight: normal;margin-right:10px; }
.vtop a:hover.right, .item a:hover.right { text-decoration:none; }
</style>
<script type="text/JavaScript">
function permcheckall(obj, perms, t) {
	var t = !t ? 0 : t;
	var checkboxs = $(perms).getElementsByTagName('INPUT');
	for(var i = 0; i < checkboxs.length; i++) {
		var e = checkboxs[i];
		if(e.type == 'checkbox') {
			if(!t) {
				if(!e.disabled) {
					e.checked = obj.checked;
				}
			} else {
				if(obj != e) {
					e.style.visibility = obj.checked ? 'hidden' : 'visible';
				}
			}
			e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
		}
	}
}
function checkclk(obj) {
	var obj = obj.parentNode.parentNode;
	obj.className = obj.className == 'item' ? 'item checked' : 'item';
}
</script>
EOF;
}