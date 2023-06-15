<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_verify.php 33455 2013-06-19 03:52:01Z andyzheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = $operation ? $operation : '';

$anchor = in_array($_GET['anchor'], array('base', 'edit', 'verify', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'authstr', 'refusal', 'pass')) ? $_GET['anchor'] : 'base';
$current = array($anchor => 1);
$navmenu = array();

if($operation == 'verify') {
	loadcache('profilesetting');
	$vid = intval($_GET['do']);
	$anchor = in_array($_GET['anchor'], array('authstr', 'refusal', 'pass', 'add')) ? $_GET['anchor'] : 'authstr';
	$current = array($anchor => 1);
	if($anchor == 'add') {
		if(!submitcheck('addverifysubmit') || $vid < 0 || $vid > 6) {
			$navmenu[0] = array('members_verify_nav_authstr', 'verify&operation=verify&anchor=authstr&do='.$vid, 0);
			$navmenu[1] = array('members_verify_nav_refusal', 'verify&operation=verify&anchor=refusal&do='.$vid, 0);
			$navmenu[2] = array('members_verify_nav_pass', 'verify&operation=verify&anchor=pass&do='.$vid, 0);
			$navmenu[3] = array('members_verify_nav_add', 'verify&operation=verify&anchor=add&do='.$vid, 1);
			$vid ? shownav('user', 'nav_members_verify', $_G['setting']['verify'][$vid]['title']) : shownav('user', $_G['setting']['verify'][$vid]['title']);
			showsubmenu($lang['members_verify_add'].'-'.$_G['setting']['verify'][$vid]['title'], $navmenu);
			showformheader("verify&operation=verify&anchor=add&do=$vid", 'enctype');
			showtableheader();
			showsetting('members_verify_userlist', 'users', $member['users'], 'textarea');
			showsubmit('addverifysubmit');
			showtablefooter();
			showformfooter();
		} else {
			$userlist = explode("\r\n", $_GET['users']);
			$insert = array();
			$haveuser = false;
			$members = C::t('common_member')->fetch_all_by_username($userlist);
			$vuids = array();
			foreach($members as $value) {
				$vuids[$value['uid']] = $value['uid'];
			}
			$verifyusers = C::t('common_member_verify')->fetch_all($vuids);
			foreach($members as $member) {
				if(isset($verifyusers[$member['uid']])) {
					C::t('common_member_verify')->update($member['uid'], array("verify$vid" => 1));
				} else {
					C::t('common_member_verify')->insert(array('uid'=>$member['uid'], "verify$vid" => 1));
				}
				$haveuser = true;
			}
			if($haveuser) {
				cpmsg('members_verify_add_user_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor=pass', 'succeed');
			} else {
				cpmsg_error('members_verify_add_user_failure', 'action=verify&operation=add&vid='.$vid);
			}
		}

	} else {
		if($anchor != 'pass') {
			$_GET['verifytype'] = $vid;
		} else {
			$_GET['verify'.$vid] = 1;
			$_GET['orderby'] = 'uid';
		}
		require_once libfile('function/profile');
		if(!submitcheck('verifysubmit', true)) {

			$menutitle = $vid ? $_G['setting']['verify'][$vid]['title'] : $lang['members_verify_profile'];
			$navmenu[0] = array('members_verify_nav_authstr', 'verify&operation=verify&anchor=authstr&do='.$vid, $current['authstr']);
			$navmenu[1] = array('members_verify_nav_refusal', 'verify&operation=verify&anchor=refusal&do='.$vid, $current['refusal']);
			if($vid) {
				$navmenu[2] = array('members_verify_nav_pass', 'verify&operation=verify&anchor=pass&do='.$vid, $current['pass']);
				$navmenu[3] = array('members_verify_nav_add', 'verify&operation=verify&anchor=add&do='.$vid, $current['add']);
			}
			$vid ? shownav('user', 'nav_members_verify', $menutitle) : shownav('user', $menutitle);
			showsubmenu($lang['members_verify_verify'].($vid ? '-'.$menutitle : ''), $navmenu);


			$searchlang = array();
			$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
			'members_verify_dateline', 'members_verify_uid', 'members_verify_username', 'members_verify_fieldid');
			foreach ($keys as $key) {
				$searchlang[$key] = cplang($key);
			}

			$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';
			$datehtml = $orderbyhtml = '';
			if($anchor != 'pass') {
				$datehtml = "<tr><th>$searchlang[members_verify_dateline]</th><td colspan=\"3\">
					<input type=\"text\" name=\"dateline1\" value=\"$_GET[dateline1]\" size=\"10\" onclick=\"showcalendar(event, this)\"> ~
					<input type=\"text\" name=\"dateline2\" value=\"$_GET[dateline2]\" size=\"10\" onclick=\"showcalendar(event, this)\"> (YYYY-MM-DD)
					</td></tr>";
				$orderbyhtml = "<select name=\"orderby\"><option value=\"dateline\"$orderby[dateline]>$searchlang[members_verify_dateline]</option>	</select>";
			} else {
				$orderbyhtml = "<select name=\"orderby\"><option value=\"uid\"$orderby[dateline]>$searchlang[members_verify_uid]</option>	</select>";
			}


			$ordersc = isset($_GET['ordersc']) ? $_GET['ordersc'] : '';
			$perpages = isset($_GET['perpages']) ? $_GET['perpages'] : '';
			$adminscript = ADMINSCRIPT;
			$expertsearch = $vid ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=members&operation=search&more=1&vid='.$vid.'" target="_top">'.cplang('search_higher').'</a>' : '';
echo <<<EOF
			<form method="get" autocomplete="off" action="$adminscript">
				<div class="block style4">
					<table cellspacing="3" cellpadding="3">
					<tr>
						<th>$searchlang[members_verify_username]* </th><td><input type="text" name="username" value="$_GET[username]"></td>
						<th>$searchlang[members_verify_uid]</th><td><input type="text" name="uid" value="$_GET[uid]"> *$searchlang[likesupport]</td>

					</tr>
					$datehtml
					<tr>
						<th>$searchlang[resultsort]</th>
						<td colspan="3">
							$orderbyhtml
							<select name="ordersc">
							<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
							<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
							</select>
							<select name="perpage">
							<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
							<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
							<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
							<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
							</select>
							<input type="hidden" name="action" value="verify">
							<input type="hidden" name="operation" value="verify">
							<input type="hidden" name="do" value="$vid">
							<input type="hidden" name="anchor" value="$anchor">
							<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">$expertsearch
						</td>
					</tr>
					</table>
				</div>
			</form>
			<iframe id="frame_profile" name="frame_profile" style="display: none"></iframe>
			<script type="text/javascript" src="static/js/calendar.js"></script>
			<script type="text/javascript">
				function showreason(vid, flag) {
					var reasonobj = $('reason_'+vid);
					if(reasonobj) {
						reasonobj.style.display = flag ? '' : 'none';
					}
					if(!flag && $('verifyitem_' + vid) != null) {
						var checkboxs = $('verifyitem_' + vid).getElementsByTagName('input');
						for(var i in checkboxs) {
							if(checkboxs[i].type == 'checkbox') {
								checkboxs[i].checked = '';
							}
						}
					}
				}
				function mod_setbg(vid, value) {
					$('mod_' + vid + '_row').className = 'mod_' + value;
				}
				function mod_setbg_all(value) {
					checkAll('option', $('cpform'), value);
					var trs = $('cpform').getElementsByTagName('TR');
					for(var i in trs) {
						if(trs[i].id && trs[i].id.substr(0, 4) == 'mod_') {
							trs[i].className = 'mod_' + value;
							showreason(trs[i].getAttribute('verifyid'), value == 'refusal' ? 1 : 0);
						}
					}
				}
				function mod_cancel_all() {
					var inputs = $('cpform').getElementsByTagName('input');
					for(var i in inputs) {
						if(inputs[i].type == 'radio') {
							inputs[i].checked = '';
						}
					}
					var trs = $('cpform').getElementsByTagName('TR');
					for(var i in trs) {
						if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row$/)) {
							trs[i].className = "mod_cancel";
							showreason(trs[i].getAttribute('verifyid'), 0)
						}
					}
				}
				function singleverify(vid) {
					var formobj = $('cpform');
					var oldaction = formobj.action;
					formobj.action = oldaction+'&frame=no&singleverify='+vid;
					formobj.target = "frame_profile";
					formobj.submit();
					formobj.action = oldaction;
					formobj.target = "";
				}

			</script>
EOF;

			$mpurl = ADMINSCRIPT.'?action=verify&operation=verify&anchor='.$anchor.'&do='.$vid;

			if($anchor == 'refusal') {
				$_GET['flag'] = -1;
			} elseif ($anchor == 'authstr') {
				$_GET['flag'] = 0;
			}
			$intkeys = array('uid', 'verifytype', 'flag', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6');
			$strkeys = array();
			$randkeys = array();
			$likekeys = array('username');
			$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'v.');
			foreach($likekeys as $k) {
				$_GET[$k] = dhtmlspecialchars($_GET[$k]);
			}
			$mpurl .= '&'.implode('&', $results['urls']);
			$wherearr = $results['wherearr'];
			if($_GET['dateline1']){
				$wherearr[] = "v.dateline >= '".strtotime($_GET['dateline1'])."'";
				$mpurl .= '&dateline1='.$_GET['dateline1'];
			}
			if($_GET['dateline2']){
				$wherearr[] = "v.dateline <= '".strtotime($_GET['dateline2'])."'";
				$mpurl .= '&dateline2='.$_GET['dateline2'];
			}

			$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

			$orders = getorders(array('dateline', 'uid'), 'dateline', 'v.');
			$ordersql = $orders['sql'];
			if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
			$orderby = array($_GET['orderby']=>' selected');
			$ordersc = array($_GET['ordersc']=>' selected');

			$orders = in_array($_G['orderby'], array('dateline', 'uid')) ? $_G['orderby'] : 'dateline';
			$ordersc = in_array(strtolower($_GET['ordersc']), array('asc', 'desc')) ? $_GET['ordersc'] : 'desc';

			$perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);
			if(!in_array($perpage, array(10, 20,50,100))) $perpage = 10;
			$perpages = array($perpage=>' selected');
			$mpurl .= '&perpage='.$perpage;

			$page = empty($_GET['page'])?1:intval($_GET['page']);
			if($page<1) $page = 1;
			$start = ($page-1)*$perpage;

			$multipage = '';

			showformheader("verify&operation=verify&do=".$vid.'&anchor='.$anchor);
			echo "<script>disallowfloat = '{$_G[setting][disallowfloat]}';</script><input type=\"hidden\" name=\"verifysubmit\" value=\"trun\" />";
			showtableheader('members_verify_manage', 'fixpadding');

			if($anchor != 'pass') {
				$cssarr = array('width="90"', 'width="120"', 'width="120"', '');
				$titlearr = array($lang['members_verify_username'], $lang['members_verify_type'], $lang['members_verify_dateline'], $lang['members_verify_info']);
				showtablerow('class="header"', $cssarr, $titlearr);
				$count = C::t('common_member_verify_info')->count_by_search($_GET['uid'], $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']));
			} else {
				$cssarr = array('width="80"', 'width="90"', 'width="120"', '');
				$titlearr = array('', $lang['members_verify_username'], $lang['members_verify_type'], $lang['members_verify_info']);
				showtablerow('class="header"', $cssarr, $titlearr);
				$wheresql = (!empty($_GET['username']) ? str_replace('v.username', 'm.username', $wheresql) : $wheresql) . ' AND v.uid=m.uid ';
				$count = C::t('common_member_verify')->count_by_search($_GET['uid'], $vid, $_GET['username']);
			}
			if($count) {

				if($anchor != 'pass') {
					$verifyusers = C::t('common_member_verify_info')->fetch_all_search($_GET['uid'], $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']), $orders, $start, $perpage, $ordersc);
				} else {
					$verifyusers = C::t('common_member_verify')->fetch_all_search($_GET['uid'], $vid, $_GET['username'], 'v.uid', $start, $perpage, $ordersc);
					$verifyuids = array_keys($verifyusers);
					$profiles = C::t('common_member_profile')->fetch_all($verifyuids, false, 0);
				}

				foreach($verifyusers as $uid => $value) {
					if($anchor == 'pass') {
						$value = array_merge($value, $profiles[$uid]);
					}
					$value['username'] = '<a href="home.php?mod=space&uid='.$value['uid'].'&do=profile" target="_blank">'.avatar($value['uid'], "small").'<br/>'.$value['username'].'</a>';
					if($anchor != 'pass') {
						$fields = $anchor != 'pass' ? dunserialize($value['field']) : $_G['setting']['verify'][$vid]['field'];
						$verifytype = $value['verifytype'] ? $_G['setting']['verify'][$value['verifytype']]['title'] : $lang['members_verify_profile'];
						$fieldstr = '<table width="96%">';
						$i = 0;
						$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td width="26">'.$lang[members_verify_refusal].'</td>' : '').'<td width="100">'.$lang['members_verify_fieldid'].'</td><td>'.$lang['members_verify_newvalue'].'</td></tr><tbody id="verifyitem_'.$value[vid].'">';
						$i++;
						foreach($fields as $key => $field) {
							if(in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
								continue;
							}
							if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
								if($field) {
									$field = '<a href="'.(getglobal('setting/attachurl').'./profile/'.$field).'" target="_blank"><img src="'.(getglobal('setting/attachurl').'./profile/'.$field).'" class="verifyimg" /></a>';
								} else {
									$field = cplang('members_verify_pic_removed');
								}
							} elseif(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
								$field = profile_show($key, $fields);
							}
							$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td><input type="checkbox" name="refusal['.$value['vid'].']['.$key.']" value="'.$key.'" onclick="$(\'refusal'.$value['vid'].'\').click();" /></td>' : '').'<td>'.$_G['cache']['profilesetting'][$key]['title'].':</td><td>'.$field.'</td></tr>';
							$i++;
						}
						$opstr = "";

						if($anchor == 'authstr') {
							$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');showreason($value[vid], 0);\">$lang[validate]</label>&nbsp;<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"refusal\" id=\"refusal$value[vid]\" onclick=\"mod_setbg($value[vid], 'refusal');showreason($value[vid], 1);\">$lang[members_verify_refusal]</label>";
						} elseif ($anchor == 'refusal') {
							$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');\">$lang[validate]</label>";
						}

						$fieldstr .= "</tbody><tr><td colspan=\"5\">$opstr &nbsp;<span id=\"reason_$value[vid]\" style=\"display: none;\">$lang[moderate_reasonpm]&nbsp; <input type=\"text\" class=\"txt\" name=\"reason[$value[vid]]\" style=\"margin: 0px;\"></span>&nbsp;<input type=\"button\" value=\"$lang[moderate]\" name=\"singleverifysubmit\" class=\"btn\" onclick=\"singleverify($value[vid]);\"></td></tr></table>";

						$valuearr = array($value['username'], $verifytype, dgmdate($value['dateline'], 'dt'), $fieldstr);
						showtablerow("id=\"mod_$value[vid]_row\" verifyid=\"$value[vid]\"", $cssarr, $valuearr);
					} else {
						$fields = $_G['setting']['verify'][$vid]['field'];
						$verifytype = $vid ? $_G['setting']['verify'][$vid]['title'] : $lang['members_verify_profile'];

						$fieldstr = '<table width="96%">';
						$fieldstr .= '<tr><td width="100">'.$lang['members_verify_fieldid'].'</td><td>'.$lang['members_verify_newvalue'].'</td></tr>';
						foreach($fields as $key => $field) {
							if(!in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
								if(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
									$value[$field] = profile_show($key, $value);
								}
								if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
									if($value[$field]) {
										$value[$field] = '<a href="'.(getglobal('setting/attachurl').'./profile/'.$value[$field]).'" target="_blank"><img src="'.(getglobal('setting/attachurl').'./profile/'.$value[$field]).'" class="verifyimg" /></a>';
									} else {
										$value[$field] = cplang('members_verify_pic_removed');
									}
								}
								$fieldstr .= '<tr><td width="100">'.$_G['cache']['profilesetting'][$key]['title'].':</td><td>'.$value[$field].'</td></tr>';
							}
						}
						$fieldstr .= "</table>";
						$opstr = "<ul class=\"nofloat\"><li><label><input class=\"radio\" type=\"radio\" name=\"verify[$value[uid]]\" value=\"export\" onclick=\"mod_setbg($value[uid], 'validate');\">$lang[export]</label></li><li><label><input class=\"radio\" type=\"radio\" name=\"verify[$value[uid]]\" value=\"refusal\" onclick=\"mod_setbg($value[uid], 'refusal');\">$lang[members_verify_refusal]</label></li></ul>";
						$valuearr = array($opstr, $value['username'], $verifytype, $fieldstr);
						showtablerow("id=\"mod_$value[uid]_row\"", $cssarr, $valuearr);
					}
				}
				$multipage = multi($count, $perpage, $page, $mpurl);
				if($anchor != 'pass') {
					showsubmit('batchverifysubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a>'. ($anchor == 'authstr' ? ' &nbsp;<a href="#all" onclick="mod_setbg_all(\'refusal\')">'.cplang('moderate_refusal_all').'</a>' : '').' &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_cancel_all').'</a>', $multipage, false);
				} else {
					showsubmit('batchverifysubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'export\')">'.cplang('moderate_export_all').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'refusal\')">'.cplang('moderate_refusal_all').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_cancel_all').'</a> &nbsp;|&nbsp;<a href="'.ADMINSCRIPT.'?action=verify&operation=verify&do='.$vid.'&anchor=pass&verifysubmit=true">'.cplang('moderate_export_getall').'</a>', $multipage, false);
				}
			} else {
				showtablerow('', 'colspan="'.count($cssarr).'"', '<strong>'.cplang('moderate_nodata').'</strong>');
			}

			showtablefooter();
			showformfooter();

		} else {

			if($anchor == 'pass') {
				$verifyuids = array();
				$note_values = array(
						'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$_G['setting']['verify'][$vid]['title'].'</a>' : ''
					);
				foreach($_GET['verify'] as $uid => $type) {
					if($type == 'export') {
						$verifyuids['export'][] = $uid;
					} elseif($type == 'refusal') {
						$verifyuids['refusal'][] = $uid;
						notification_add($uid, 'verify', 'profile_verify_pass_refusal', $note_values, 1);
					}
				}
				if(is_array($verifyuids['refusal']) && !empty($verifyuids['refusal'])) {
					C::t('common_member_verify')->update($verifyuids['refusal'], array("verify$vid" => '0'));
				}
				if(is_array($verifyuids['export']) && !empty($verifyuids['export']) || empty($verifyuids['refusal'])) {
					$uids = array();
					if(is_array($verifyuids['export']) && !empty($verifyuids['export'])) {
						$uids = $verifyuids['export'];
					}
					$fields = $_G['setting']['verify'][$vid]['field'];
					$fields = array_reverse($fields);
					$fields['username'] = 'username';
					$fields = array_reverse($fields);
					$title = $verifylist = '';
					$showtitle = true;
					$verifyusers = C::t('common_member_verify')->fetch_all_by_vid($vid, 1, $uids);
					$verifyuids = array_keys($verifyusers);
					$members = C::t('common_member')->fetch_all($verifyuids, false, 0);
					$profiles = C::t('common_member_profile')->fetch_all($verifyuids, false, 0);
					foreach($verifyusers as $uid => $value) {
						$value = array_merge($value, $members[$uid], $profiles[$uid]);
						$str = $common = '';
						foreach($fields as $key => $field) {
							if(in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
								continue;
							}
							if($showtitle) {
								$title .= $common.($key == 'username' ? $lang['username'] : $_G['cache']['profilesetting'][$key]['title']);
							}
							if(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
								$value[$field] = profile_show($key, $value);
							}
							$str .= $common.$value[$field];
							$common = "\t";
						}
						$verifylist .= $str."\n";
						$showtitle = false;
					}
					$verifylist = $title."\n".$verifylist;
					$filename = date('Ymd', TIMESTAMP).'.xls';

					define('FOOTERDISABLED', true);
					ob_end_clean();
					header("Content-type:application/vnd.ms-excel");
					header('Content-Encoding: none');
					header('Content-Disposition: attachment; filename='.$filename);
					header('Pragma: no-cache');
					header('Expires: 0');
					if($_G['charset'] != 'gbk') {
						$verifylist = diconv($verifylist, $_G['charset'], 'GBK');
					}
					echo $verifylist;
					exit();
				} else {
					cpmsg('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor=pass', 'succeed');
				}
			} else {
				$vids = array();
				$single = intval($_GET['singleverify']);
				$verifyflag = empty($_GET['verify']) ? false : true;
				if($verifyflag) {
					if($single) {
						$_GET['verify'] = array($single => $_GET['verify'][$single]);
					}
					foreach($_GET['verify'] as $id => $type) {
						$vids[] = $id;
					}

					$verifysetting = $_G['setting']['verify'];
					$verify = $refusal = array();
					foreach(C::t('common_member_verify_info')->fetch_all($vids) as $value) {
						if(in_array($_GET['verify'][$value['vid']], array('refusal', 'validate'))) {
							$fields = dunserialize($value['field']);
							$verifysetting = $_G['setting']['verify'][$value['verifytype']];

							if($_GET['verify'][$value['vid']] == 'refusal') {
								$refusalfields = !empty($_GET['refusal'][$value['vid']]) ? $_GET['refusal'][$value['vid']] : $verifysetting['field'];
								$fieldtitle = $common = '';
								$deleteverifyimg = false;
								foreach($refusalfields as $key => $field) {
									$fieldtitle .= $common.$_G['cache']['profilesetting'][$field]['title'];
									$common = ',';
									if($_G['cache']['profilesetting'][$field]['formtype'] == 'file') {
										$deleteverifyimg = true;
										@unlink(getglobal('setting/attachdir').'./profile/'.$fields[$key]);
										$fields[$field] = '';
									}
								}
								if($deleteverifyimg) {
									C::t('common_member_verify_info')->update($value['vid'], array('field' => serialize($fields)));
								}
								if($value['verifytype']) {
									$verify["verify"]['-1'][] = $value['uid'];
								}
								$verify['flag'][] = $value['vid'];
								$note_values = array(
										'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : '',
										'profile' => $fieldtitle,
										'reason' => $_GET['reason'][$value['vid']],
									);
								$note_lang = 'profile_verify_error';
							} else {
								C::t('common_member_profile')->update(intval($value['uid']), $fields);
								$verify['delete'][] = $value['vid'];
								if($value['verifytype']) {
									$verify["verify"]['1'][] = $value['uid'];
								}
								$note_values = array(
										'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : ''
									);
								$note_lang = 'profile_verify_pass';
							}
							notification_add($value['uid'], 'verify', $note_lang, $note_values, 1);
						}
					}
					if($vid && !empty($verify["verify"])) {
						foreach($verify["verify"] as $flag => $uids) {
							$flag = intval($flag);
							C::t('common_member_verify')->update($uids, array("verify$vid" => $flag));
						}
					}

					if(!empty($verify['delete'])) {
						C::t('common_member_verify_info')->delete($verify['delete']);
					}

					if(!empty($verify['flag'])) {
						C::t('common_member_verify_info')->update($verify['flag'], array('flag' => '-1'));
					}
				}
				if($single && $_GET['frame'] == 'no') {
					echo "<script type=\"text/javascript\">var trObj = parent.$('mod_{$single}_row');trObj.parentNode.removeChild(trObj);</script>";
				} else {
					cpmsg('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor='.$_GET['anchor'], 'succeed');
				}
			}
		}
	}
} elseif($operation == 'edit') {

	shownav('user', 'nav_members_verify');
	$vid = $_GET['vid'] < 8 ? intval($_GET['vid']) : 0;
	$verifyarr = $_G['setting']['verify'][$vid];
	if(!submitcheck('verifysubmit')) {
		if($vid == 7) {
			showtips('members_verify_setting_tips');
		}
		showformheader("verify&operation=edit&vid=$vid", 'enctype');
		showtableheader();
		$readonly = $vid == 6 || $vid == 7 ? 'readonly' : '';
		showsetting('members_verify_title', "verify[title]", $verifyarr['title'], 'text', $readonly);
		showsetting('members_verify_enable', "verify[available]", $verifyarr['available'], 'radio');
		$verificonhtml = '';
		if($verifyarr['icon']) {
			$icon_url = parse_url($verifyarr['icon']);
			$prefix = !$icon_url['host'] && strpos($verifyarr['icon'], $_G['setting']['attachurl'].'common/') === false ? $_G['setting']['attachurl'].'common/' : '';
			$verificonhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon['.$vid.']" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$prefix.$verifyarr['icon'].'" />';
		}
		$unverifyiconhtml = '';
		if($verifyarr['unverifyicon']) {
			$unverifyiconurl = parse_url($verifyarr['unverifyicon']);

			$prefix = !$unverifyiconurl['host'] && strpos($verifyarr['unverifyicon'], $_G['setting']['attachurl'].'common/') === false ? $_G['setting']['attachurl'].'common/' : '';
			$unverifyiconhtml = '<label><input type="checkbox" class="checkbox" name="delunverifyicon['.$vid.']" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$prefix.$verifyarr['unverifyicon'].'" />';
		}
		showsetting('members_verify_showicon', "verify[showicon]", $verifyarr['showicon'], 'radio', '', 1);
		showsetting('members_unverify_icon', 'unverifyiconnew', (!$unverifyiconurl['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $verifyarr['unverifyicon']) : $verifyarr['unverifyicon']), 'filetext', '', 0, $unverifyiconhtml);
		showsetting('members_verify_icon', 'iconnew', (!$icon_url['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $verifyarr['icon']) : $verifyarr['icon']), 'filetext', '', 0, $verificonhtml);
		showtagfooter('tbody');

		if($vid == 6) {
			showsetting('members_verify_view_real_name', "verify[viewrealname]", $verifyarr['viewrealname'], 'radio');
		} elseif($vid == 7) {
			showsetting('members_verify_view_video_photo', "verify[viewvideophoto]", $verifyarr['viewvideophoto'], 'radio');
		}
		if($vid != 7) {
			$varname = array('verify[field]', array(), 'isfloat');
			foreach(C::t('common_member_profile_setting')->fetch_all_by_available(1) as $value) {
				if(!in_array($value['fieldid'], array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
					$varname[1][] = array($value['fieldid'], $value['title'], $value['fieldid']);
				}
			}

			showsetting('members_verify_setting_field', $varname, $verifyarr['field'], 'omcheckbox');
		}
		$groupselect = array();
		foreach(C::t('common_usergroup')->fetch_all_not(array(6, 7)) as $group) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $verifyarr['groupid']) ? 'selected' : '').">$group[grouptitle]</option>\n";
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
		showsetting('members_verify_group', '', '', '<select name="verify[groupid][]" multiple="multiple" size="10">'.$groupselect.'</select>');

		showsubmit('verifysubmit');
		showtablefooter();
		showformfooter();
	} else {
		foreach( $_G['setting']['verify'] AS $key => $value) {
			$_G['setting']['verify'][$key]['icon'] = str_replace($_G['setting']['attachurl'].'common/', '', $value['icon']);
			$_G['setting']['verify'][$key]['unverifyicon'] = str_replace($_G['setting']['attachurl'].'common/', '', $value['unverifyicon']);
		}
		$verifynew = getgpc('verify');
		if($vid == 6 || $vid == 7) {
			$verifynew['title'] = $_G['setting']['verify'][$vid]['title'];
		}
		if($verifynew['available'] == 1 && !trim($verifynew['title'])) {
			cpmsg('members_verify_update_title_error', '', 'error');
		}
		$verifynew['icon'] = getverifyicon('iconnew', $vid);
		$verifynew['unverifyicon'] = getverifyicon('unverifyiconnew', $vid, 'unverify_icon');

		if($_GET['deleteicon']) {
			$verifynew['icon'] = delverifyicon($verifyarr['icon']);
		}
		if($_GET['delunverifyicon']) {
			$verifynew['unverifyicon'] = delverifyicon($verifyarr['unverifyicon']);
		}
		if(!empty($verifynew['field']['residecity'])) {
			$verifynew['field']['resideprovince'] = 'resideprovince';
			$verifynew['field']['residedist'] = 'residedist';
			$verifynew['field']['residecommunity'] = 'residecommunity';
		}
		if(!empty($verifynew['field']['birthday'])) {
			$verifynew['field']['birthyear'] = 'birthyear';
			$verifynew['field']['birthmonth'] = 'birthmonth';
		}
		if(!empty($verifynew['field']['birthcity'])) {
			$verifynew['field']['birthprovince'] = 'birthprovince';
			$verifynew['field']['birthdist'] = 'birthdist';
			$verifynew['field']['birthcommunity'] = 'birthcommunity';
		}
		$verifynew['groupid'] = !empty($verifynew['groupid']) && is_array($verifynew['groupid']) ? $verifynew['groupid'] : array();
		$_G['setting']['verify'][$vid] = $verifynew;
		$_G['setting']['verify']['enabled'] = false;
		for($i = 1; $i < 8; $i++) {
			if($_G['setting']['verify'][$i]['available'] && !$_G['setting']['verify']['enabled']) {
				$_G['setting']['verify']['enabled'] = true;
			}
			if($_G['setting']['verify'][$i]['icon']) {
				$icon_url = parse_url($_G['setting']['verify'][$i]['icon']);
			}
			$_G['setting']['verify'][$i]['icon'] = !$icon_url['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $_G['setting']['verify'][$i]['icon']) : $_G['setting']['verify'][$i]['icon'] ;
		}
		C::t('common_setting')->update('verify', $_G['setting']['verify']);
		if(isset($verifynew['viewrealname']) && !$verifynew['viewrealname']) {
			C::t('common_member_profile_setting')->update('realname', array('showinthread' => 0));
			$custominfo = C::t('common_setting')->fetch('customauthorinfo', true);
			if(isset($custominfo[0]['field_realname'])) {
				unset($custominfo[0]['field_realname']);
				C::t('common_setting')->update('customauthorinfo', $custominfo);
				updatecache(array('custominfo'));
			}
		}
		updatecache(array('setting'));
		cpmsg('members_verify_update_succeed', 'action=verify', 'succeed');
	}


} else {

	shownav('user', 'nav_members_verify');
	showsubmenu('members_verify_setting');
	if(!submitcheck('verifysubmit')) {
		showformheader("verify");
		showtableheader('members_verify_setting', 'fixpadding');
		showsubtitle(array('members_verify_available', 'members_verify_id', 'members_verify_title', ''), 'header');
		for($i = 1; $i < 7; $i++) {
			$readonly = $i == 6 ? true : false;
			$url = parse_url($_G['setting']['verify'][$i]['icon']);
			if(!$url['host'] && $_G['setting']['verify'][$i]['icon'] && strpos($_G['setting']['verify'][$i]['icon'], $_G['setting']['attachurl'].'common/') === false) {
				$_G['setting']['verify'][$i]['icon'] = $_G['setting']['attachurl'].'common/'.$_G['setting']['verify'][$i]['icon'];
			}
			showtablerow('', array('class="td25"', '', '', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[verify][$i][available]\" value=\"1\" ".($_G['setting']['verify'][$i]['available'] ? 'checked' : '')." />",
				'verify'.$i,
				($readonly ? $_G['setting']['verify'][$i]['title']."<input type=\"hidden\" name=\"settingnew[verify][$i][title]\" value=\"{$_G['setting']['verify'][$i]['title']}\" readonly>&nbsp;":"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[verify][$i][title]\" value=\"{$_G['setting']['verify'][$i]['title']}\">").
					($_G['setting']['verify'][$i]['icon'] ? '<img src="'.$_G['setting']['verify'][$i]['icon'].'" />' : ''),
				"<a href=\"".ADMINSCRIPT."?action=verify&operation=edit&anchor=base&vid=$i\">".$lang['edit']."</a>"
			));
		}
		showsubmit('verifysubmit');
		showtablefooter();
		showformfooter();

	} else {
		$settingnew = getgpc('settingnew');
		$enabled = false;
		foreach($settingnew['verify'] as $key => $value) {
			if($value['available'] && !$value['title']) {
				cpmsg('members_verify_title_invalid', '', 'error');
			}
			if($value['available']) {
				$enabled = true;
			}
			$_G['setting']['verify'][$key]['available'] = intval($value['available']);
			$_G['setting']['verify'][$key]['title'] = $value['title'];
		}
		$_G['setting']['verify']['enabled'] = $enabled;
		C::t('common_setting')->update('verify', $_G['setting']['verify']);
		updatecache(array('setting'));
		updatemenu('user');
		cpmsg('members_verify_update_succeed', 'action=verify', 'succeed');
	}
}

function getverifyicon($iconkey = 'iconnew', $vid = 1, $extstr = 'verify_icon') {
	global $_G, $_FILES;

	if($_FILES[$iconkey]) {
		$data = array('extid' => "$vid");
		$iconnew = upload_icon_banner($data, $_FILES[$iconkey], $extstr);
	} else {
		$iconnew = $_GET[''.$iconkey];
	}
	return $iconnew;
}

function delverifyicon($icon) {
	global $_G;

	$valueparse = parse_url($icon);
	if(!isset($valueparse['host']) && preg_match('/^'.preg_quote($_G['setting']['attachurl'].'common/', '/').'/', $icon)) {
		@unlink($icon);
	}
	return '';
}
?>