<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_medals.php 31634 2012-09-17 06:43:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('medalsubmit')) {
		shownav('extended', 'nav_medals', 'admin');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 1),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 0)
		));
		showtips('medals_tips');
		showformheader('medals');
		showtableheader('medals_list', 'fixpadding');
		showsubtitle(array('', 'display_order', 'available', 'name', 'description', 'medals_image', 'medals_type', ''));

?>
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newdisplayorder[]" size="3">', 'td28'],
			[1,'', 'td25'],
			[1,'<input type="text" class="txt" name="newname[]" size="10">'],
			[1,'<input type="text" class="txt" name="newdescription[]" size="30">'],
			[1,'<input type="text" class="txt" name="newimage[]" size="20">'],
			[1,'', 'td23'],
			[1,'', 'td25']
		]
	];
</script>
<?php
		$perpage = 50;
		$start = ($_G['page'] - 1) * $perpage;
		$count = C::t('forum_medal')->count_by_available(false);
		$multi = multi($count, $perpage, $page, ADMINSCRIPT."?action=medals");
		foreach(C::t('forum_medal')->fetch_all_data(false, $start, $perpage) as $medal) {
			$checkavailable = $medal['available'] ? 'checked' : '';
			switch($medal['type']) {
				case 0:
					$medal['type'] = cplang('medals_adminadd');
					break;
				case 1:
					$medal['type'] = $medal['price'] ? cplang('medals_buy') : cplang('medals_register');
					break;
				case 2:
					$medal['type'] = cplang('modals_moderate');
					break;
			}
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', '', '', '', 'class="td23"', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$medal[medalid]\">",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$medal[medalid]]\" value=\"$medal[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$medal[medalid]]\" value=\"1\" $checkavailable>",
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"name[$medal[medalid]]\" value=\"$medal[name]\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"description[$medal[medalid]]\" value=\"$medal[description]\">",
				"<input type=\"text\" class=\"txt\" size=\"20\" name=\"image[$medal[medalid]]\" value=\"$medal[image]\"><img style=\"vertical-align:middle\" src=\"static/image/common/$medal[image]\">",
				$medal[type],
				"<a href=\"".ADMINSCRIPT."?action=medals&operation=edit&medalid=$medal[medalid]\" class=\"act\">$lang[detail]</a>"
			));
		}

		echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['medals_addnew'].'</a></div></td></tr>';
		showsubmit('medalsubmit', 'submit', 'del', '', $multi);
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_GET['delete']) && $_GET['delete']) {
			$ids = array();
			foreach($_GET['delete'] as $id) {
				$ids[] = $id;
			}
			C::t('forum_medal')->delete($_GET['delete']);
		}

		if(is_array($_GET['name'])) {
			foreach($_GET['name'] as $id => $val) {
				$update = array(
					'available' => $_GET['available'][$id],
					'displayorder' => intval($_GET['displayorder'][$id])
				);
				if($_GET['name'][$id]) {
					$update['name'] = dhtmlspecialchars($_GET['name'][$id]);
				}
				if($_GET['description'][$id]) {
					$update['description'] = dhtmlspecialchars($_GET['description'][$id]);
				}
				if($_GET['image'][$id]) {
					$update['image'] = dhtmlspecialchars($_GET['image'][$id]);
				}
				C::t('forum_medal')->update($id, $update);

			}
		}

		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $key => $value) {
				if($value != '' && $_GET['newimage'][$key] != '') {
					$data = array('name' => dhtmlspecialchars($value),
						'available' => $_GET['newavailable'][$key],
						'image' => $_GET['newimage'][$key],
						'displayorder' => intval($_GET['newdisplayorder'][$key]),
						'description' => dhtmlspecialchars($_GET['newdescription'][$key]),
					);
					C::t('forum_medal')->insert($data);
				}
			}
		}

		updatecache('setting');
		updatecache('medals');
		cpmsg('medals_succeed', 'action=medals', 'succeed');

	}

} elseif($operation == 'mod') {

	if(submitcheck('delmedalsubmit')) {
		if (is_array($_GET['delete']) && !empty($_GET['delete'])) {
			$ids = array();
			foreach($_GET['delete'] as $id) {
				$ids[] = $id;
			}
			C::t('forum_medallog')->update($ids, array('type' => 3));
			cpmsg('medals_invalidate_succeed', 'action=medals&operation=mod', 'succeed');
		} else {
			cpmsg('medals_please_input', 'action=medals&operation=mod', 'error');
		}
	} elseif(submitcheck('modmedalsubmit')) {

		if(is_array($_GET['delete']) && !empty($_GET['delete'])) {
			$ids = $comma = '';
			foreach($_GET['delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}

			$query = DB::query("SELECT me.id, me.uid, me.medalid, me.dateline, me.expiration, mf.medals
					FROM ".DB::table('forum_medallog')." me
					LEFT JOIN ".DB::table('common_member_field_forum')." mf USING (uid)
					WHERE id IN ($ids)");

			loadcache('medals');
			while($modmedal = DB::fetch($query)) {
				$modmedal['medals'] = empty($medalsnew[$modmedal['uid']]) ? $modmedal['medals'] : $medalsnew[$modmedal['uid']];

				foreach($modmedal['medals'] = explode("\t", $modmedal['medals']) as $key => $modmedalid) {
					list($medalid, $medalexpiration) = explode("|", $modmedalid);
					if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
						$medalsnew[$modmedal['uid']][$key] = $modmedalid;
					}
				}
				$medalstatus = empty($modmedal['expiration']) ? 0 : 1;
				$modmedal['expiration'] = $modmedal['expiration'] ? (TIMESTAMP + $modmedal['expiration'] - $modmedal['dateline']) : '';
				$medalsnew[$modmedal['uid']][] = $modmedal['medalid'].(empty($modmedal['expiration']) ? '' : '|'.$modmedal['expiration']);
				C::t('forum_medallog')->update($modmedal['id'], array('type' => 1, 'status' => $medalstatus, 'expiration' => $modmedal['expiration'], 'dateline' => TIMESTAMP));
				C::t('common_member_medal')->insert(array('uid' => $modmedal['uid'], 'medalid' => $modmedal['medalid']), 0, 1);
			}

			foreach ($medalsnew as $key => $medalnew) {
				$medalnew = array_unique($medalnew);
				$medalnew = implode("\t", $medalnew);
				C::t('common_member_field_forum')->update($key, array('medals' => $medalnew));
			}
			cpmsg('medals_validate_succeed', 'action=medals&operation=mod', 'succeed');
		} else {
			cpmsg('medals_please_input', 'action=medals&operation=mod', 'error');
		}
	} else {

		$medals = '';
		$medallogs = $medalids = $uids = array();
		foreach(C::t('forum_medallog')->fetch_all_by_type(2) as $id => $medal) {
			$medal['dateline'] =  dgmdate($medal['dateline'], 'Y-m-d H:i');
			$medal['expiration'] =  empty($medal['expiration']) ? $lang['medals_forever'] : dgmdate($medal['expiration'], 'Y-m-d H:i');
			$medalids[$medal['medalid']] = $medal['medalid'];
			$uids[$medal['uid']] = $medal['uid'];
			$medallogs[$id] = $medal;
		}
		$medalnames = C::t('forum_medal')->fetch_all($medalids);
		$medalusers = C::t('common_member')->fetch_all($uids);
		foreach($medallogs as $id => $medal) {
			$medals .= showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$id\">",
				"<a href=\"home.php?mod=space&username=".rawurlencode($medalusers[$medal['uid']]['username'])."\" target=\"_blank\">{$medalusers[$medal[uid]][username]}</a>",
				$medalnames[$medal['medalid']]['name'],
				$medal['dateline'],
				$medal['expiration']
			), TRUE);
		}

		shownav('extended', 'nav_medals', 'nav_medals_mod');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 0),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 1)
		));
		showformheader('medals&operation=mod');
		showtableheader('medals_mod');
		showtablerow('', '', array(
			'',
			cplang('medals_user'),
			cplang('medals_name'),
			cplang('medals_date'),
			cplang('medals_expr'),
		));
		echo $medals;
		showsubmit('modmedalsubmit', 'medals_modpass', 'select_all', '<input type="submit" class="btn" value="'.cplang('medals_modnopass').'" name="delmedalsubmit"> ');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'edit') {

	$medalid = intval($_GET['medalid']);

	if(!submitcheck('medaleditsubmit')) {

		$medal = C::t('forum_medal')->fetch($medalid);

		$medal['permission'] = dunserialize($medal['permission']);
		$medal['usergroupallow'] = $medal['permission']['usergroupallow'];
		$medal['usergroups'] = (array)$medal['permission']['usergroups'];
		$medal['permission'] = $medal['permission'][0];

		$credits = array();
		$credits[] = array(0, $lang['default']);
		foreach($_G['setting']['extcredits'] as $i => $extcredit) {
			$credits[] = array($i, $extcredit['title']);
		}

		$groupselect = array();
		foreach(C::t('common_usergroup')->range_orderby_credit() as $group) {
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $medal['usergroups']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
		}
		$usergroups = '<select name="usergroupsnew[]" size="10" multiple="multiple">'.
			'<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';

		shownav('extended', 'nav_medals', 'admin');
		showsubmenu('nav_medals', array(
			array('admin', 'medals', 1),
			array('nav_medals_confer', 'members&operation=confermedal', 0),
			array('nav_medals_mod', 'medals&operation=mod', 0)
		));
		showformheader("medals&operation=edit&medalid=$medalid");
		showtableheader(cplang('medals_edit').' - '.$medal['name'], 'nobottom');
		showsetting('medals_name1', 'namenew', $medal['name'], 'text');
		showsetting('medals_img', '', '', '<input type="text" class="txt" size="30" name="imagenew" value="'.$medal['image'].'" ><img src="static/image/common/'.$medal['image'].'">');
		showsetting('medals_type1', array('typenew', array(
			array(0, $lang['medals_adminadd'], array('creditdiv' => 'none')),
			array(1, $lang['medals_apply_auto'], array('creditdiv' => '')),
			array(2, $lang['medals_apply_noauto'], array('creditdiv' => 'none'))
		)), $medal['type'], 'mradio');
		showtagheader('tbody', 'creditdiv', $medal['type'] == 1, 'sub');
		showsetting('medals_credit', array('creditnew', $credits), $medal['credit'], 'select');
		showsetting('medals_price', 'pricenew', $medal['price'], 'text');
		showtagfooter('tbody');
		showsetting('medals_usergroups_allow', 'usergroupallow', $medal['usergroupallow'], 'radio', 0, 1);
		showsetting('medals_usergroups', '', '', $usergroups);
		showtagfooter('tbody');
		showsetting('medals_expr1', 'expirationnew', $medal['expiration'], 'text');
		showsetting('medals_memo', 'descriptionnew', $medal['description'], 'text');
		showtablefooter();

		showtableheader('medals_perm', 'notop');

			$formulareplace .= '\'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\',\'<u>'.$lang['setting_credits_formula_posts'].'</u>\',\'<u>'.$lang['setting_credits_formula_oltime'].'</u>\',\'<u>'.$lang['setting_credits_formula_pageviews'].'</u>\'';

?>
<script type="text/JavaScript">
	function medalsinsertunit(text, textend) {
		insertunit($('formulapermnew'), text, textend);
		formulaexp();
	}

	var formulafind = new Array('digestposts', 'posts', 'threads');
	var formulareplace = new Array(<?php echo $formulareplace;?>);
	function formulaexp() {
		var result = $('formulapermnew').value;
<?php

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $lang['setting_credits_formula_extcredits'].$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.$extcredittitle.'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="medalsinsertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		echo 'result = result.replace(/regdate/g, \'<u>'.cplang('forums_edit_perm_formula_regdate').'</u>\');';
		echo 'result = result.replace(/regday/g, \'<u>'.cplang('forums_edit_perm_formula_regday').'</u>\');';
		echo 'result = result.replace(/regip/g, \'<u>'.cplang('forums_edit_perm_formula_regip').'</u>\');';
		echo 'result = result.replace(/lastip/g, \'<u>'.cplang('forums_edit_perm_formula_lastip').'</u>\');';
		echo 'result = result.replace(/buyercredit/g, \'<u>'.cplang('forums_edit_perm_formula_buyercredit').'</u>\');';
		echo 'result = result.replace(/sellercredit/g, \'<u>'.cplang('forums_edit_perm_formula_sellercredit').'</u>\');';
		echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.$lang['setting_credits_formula_posts'].'</u>\');';
		echo 'result = result.replace(/threads/g, \'<u>'.$lang['setting_credits_formula_threads'].'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.$lang['setting_credits_formula_oltime'].'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_and'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;'.$lang['setting_credits_formulaperm_or'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';

?>
		$('formulapermexp').innerHTML = result;
	}
</script>
<tr><td colspan="2"><div class="extcredits">
<?php echo $extcreditsbtn;?>
<a href="###" onclick="medalsinsertunit(' regdate ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regdate')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' regday ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regday')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' regip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regip')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' lastip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_lastip')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' buyercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_buyercredit')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' sellercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_sellercredit')?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' digestposts ')"><?php echo $lang['setting_credits_formula_digestposts'];?></a>&nbsp;
<a href="###" onclick="medalsinsertunit(' posts ')"><?php echo $lang['setting_credits_formula_posts'];?></a>&nbsp;
<a href="###" onclick="medalsinsertunit(' threads ')"><?php echo $lang['setting_credits_formula_threads'];?></a>&nbsp;
<a href="###" onclick="medalsinsertunit(' oltime ')"><?php echo $lang['setting_credits_formula_oltime'];?></a>&nbsp;
<a href="###" onclick="medalsinsertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' == ')">&nbsp;=&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' and ')">&nbsp;<?php echo $lang['setting_credits_formulaperm_and'];?>&nbsp;</a>&nbsp;
<a href="###" onclick="medalsinsertunit(' or ')">&nbsp;<?php echo $lang['setting_credits_formulaperm_or'];?>&nbsp;</a>&nbsp;<br />
</div><div id="formulapermexp" class="marginbot diffcolor2"><?php echo $formulapermexp;?></div>
<textarea name="formulapermnew" id="formulapermnew" style="width: 80%" rows="3" onkeyup="formulaexp()" onkeydown="textareakey(this, event)"><?php echo dhtmlspecialchars($medal['permission']);?></textarea>
<br /><span class="smalltxt"><?php echo $lang['medals_permformula'];?></span>
<br /><?php echo $lang['creditwizard_current_formula_notice'];?>
<script type="text/JavaScript">formulaexp()</script>
</td></tr>
<?php
			showsubmit('medaleditsubmit');
			showtablefooter();
			showformfooter();

	} else {
		if(!checkformulaperm($_GET['formulapermnew'])) {
			cpmsg('forums_formulaperm_error', '', 'error');
		}

		$formulapermary[0] = $_GET['formulapermnew'];
		$formulapermary[1] = preg_replace(
				array("/(digestposts|posts|threads|oltime|extcredits[1-8])/", "/(regdate|regday|regip|lastip|buyercredit|sellercredit|field\d+)/"),
				array("getuserprofile('\\1')", "\$memberformula['\\1']"),
				$_GET['formulapermnew']);
		$formulapermary['usergroupallow'] = $_GET['usergroupallow'];
		$formulapermary['usergroups'] = (array)$_GET['usergroupsnew'];
		$formulapermnew = serialize($formulapermary);

		$update = array(
			'type' => $_GET['typenew'],
			'description' => dhtmlspecialchars($_GET['descriptionnew']),
			'expiration' => intval($_GET['expirationnew']),
			'permission' => $formulapermnew,
			'image' => $_GET['imagenew'],
			'credit' => $_GET['creditnew'],
			'price' => $_GET['pricenew'],
		);
		if($_GET['namenew']) {
			$update['name'] = dhtmlspecialchars($_GET['namenew']);
		}
		C::t('forum_medal')->update($medalid, $update);

		updatecache('medals');
		cpmsg('medals_succeed', 'action=medals&do=editmedals', 'succeed');
	}

}

?>