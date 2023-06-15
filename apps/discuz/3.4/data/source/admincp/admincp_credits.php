<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_credits.php 32527 2013-02-05 09:56:25Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation ? $operation : 'list';

if($operation == 'list') {
	$rules = array();
	foreach(C::t('common_credit_rule')->fetch_all_rule() as $value) {
		$rules[$value['rid']] = $value;
	}
	if(!submitcheck('rulesubmit')) {

		$anchor = in_array($_GET['anchor'], array('base', 'policytable', 'edit')) ? $_GET['anchor'] : 'base';
		$current = array($anchor => 1);
		showsubmenu('setting_credits', array(
			array('setting_credits_base', 'setting&operation=credits&anchor=base', $current['base']),
			array('setting_credits_policy', 'credits&operation=list&anchor=policytable', $current['policytable']),
		));

		showformheader("credits&operation=list");
		showtableheader('setting_credits_policy', 'nobottom', 'id="policytable"'.($anchor != 'policytable' ? ' style="display: none"' : ''));
		echo '<tr class="header"><th class="td28 nowrap">'.$lang['setting_credits_policy_name'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_cycletype'].'</th><th class="td28 nowrap">'.$lang['setting_credits_policy_rewardnum'].'</th>';
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				echo "<th class=\"td25\" id=\"policy$i\" ".($_G['setting']['extcredits'][$i] ? '' : 'disabled')." valign=\"top\">".$_G['setting']['extcredits'][$i]['title']."</th>";
			}
		}
		echo '<th class="td25">&nbsp;</th></tr>';

		foreach($rules as $rid => $rule) {
			$tdarr = array($rule['rulename'], $rule['rid'] ? $lang['setting_credits_policy_cycletype_'.$rule['cycletype']] : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A');
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					array_push($tdarr, '<input name="credit['.$rule['rid'].']['.$i.']" class="txt" value="'.$rule['extcredits'.$i].'" />');
				}
			}
			$opstr = '<a href="'.ADMINSCRIPT.'?action=credits&operation=edit&rid='.$rule['rid'].'" title="" class="act">'.$lang['edit'].'</a>';
			array_push($tdarr, $opstr);
			showtablerow('', array_fill(0, count($_G['setting']['extcredits']) + 4, 'class="td25"'), $tdarr);
		}
		showtablerow('', 'class="lineheight" colspan="9"', $lang['setting_credits_policy_comment']);
		showtablefooter();
		showtableheader('', 'nobottom', '');
		showsetting('setting_credits_policy_mobile', 'settingnew[creditspolicymobile]', $_G['setting']['creditspolicymobile'], 'text');
		showsubmit('rulesubmit');
		showtablefooter();
		showformfooter();
	} else {
		foreach($_GET['credit'] as $rid => $credits) {
			$rule = array();
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					$rule['extcredits'.$i] = $credits[$i];
				}
			}
			C::t('common_credit_rule')->update($rid, $rule);
		}
		$settings = array(
			'creditspolicymobile' => $_GET['settingnew']['creditspolicymobile'],
		);
		C::t('common_setting')->update_batch($settings);
		updatecache(array('setting', 'creditrule'));
		cpmsg('credits_update_succeed', 'action=credits&operation=list&anchor=policytable', 'succeed');
	}
} elseif($operation == 'edit') {

	$rid = intval($_GET['rid']);
	$fid = intval($_GET['fid']);
	if($rid) {
		$globalrule = $ruleinfo = C::t('common_credit_rule')->fetch($rid);
		if($fid) {
			$query = C::t('forum_forum')->fetch_info_by_fid($fid);
			$forumname = $query['name'];
			$policy = $query['creditspolicy'] ? dunserialize($query['creditspolicy']) : array();
			if(isset($policy[$ruleinfo['action']])) {
				$ruleinfo = $policy[$ruleinfo['action']];
			}
		}
	}
	if(!submitcheck('rulesubmit')) {
		if(!$rid) {
			$ruleinfo['rulename'] = $lang['credits_edit_lowerlimit'];
		}
		if(!$fid) {
			shownav('global', 'credits_edit');
			showsubmenu("$lang[credits_edit] - $ruleinfo[rulename]");
		} else {
			if(!in_array($fid, explode(',', $globalrule['fids']))) {
				for($i = 1; $i <= 8; $i++) {
					$ruleinfo['extcredits'.$i] = '';
				}
			}
			shownav('forum', 'forums_edit');
			showsubmenu("$forumname - $lang[credits_edit] - $ruleinfo[rulename]");
			showtips('forums_edit_tips');
		}
		showformheader("credits&operation=edit&rid=$rid&".($fid ? "fid=$fid" : ''));
		$extra = '';
		if($fid) {
			$actives = $checkarr = array();
			$usecustom = in_array($fid, explode(',', $globalrule['fids'])) ? 1 : 0;
			$actives[$usecustom] = ' class="checked"';
			$checkarr[$usecustom] = ' checked';
			showtableheader('', 'nobottom');
				$str = <<<EOF
	<ul onmouseover="altStyle(this);">
		<li$actives[1]><input type="radio" onclick="$('edit').style.display = '';" $checkarr[1] value="1" name="rule[usecustom]" class="radio">&nbsp;$lang[yes]</li>
		<li$actives[0]><input type="radio" onclick="$('edit').style.display = 'none';" $checkarr[0] value="0" name="rule[usecustom]" class="radio">&nbsp;$lang[no]</li>
	</ul>
EOF;
			showsetting('setting_credits_use_custom_credit', 'usecustom', $usecustom, $str);
			showtablefooter();
			$extra = !$usecustom ? ' style="display:none;" ' : '';
		}
		showtips('setting_credits_policy_comment');
		showtableheader('credits_edit', 'nobottom', 'id="edit"'.$extra);
		if($rid) {
			showsetting('setting_credits_policy_cycletype', array('rule[cycletype]', array(
				array(0, $lang['setting_credits_policy_cycletype_0'], array('cycletimetd' => 'none', 'rewardnumtd' => 'none')),
				array(1, $lang['setting_credits_policy_cycletype_1'], array('cycletimetd' => 'none', 'rewardnumtd' => '')),
				array(2, $lang['setting_credits_policy_cycletype_2'], array('cycletimetd' => '', 'rewardnumtd' => '')),
				array(3, $lang['setting_credits_policy_cycletype_3'], array('cycletimetd' => '', 'rewardnumtd' => '')),
				array(4, $lang['setting_credits_policy_cycletype_4'], array('cycletimetd' => 'none', 'rewardnumtd' => '')),
			)), $ruleinfo['cycletype'], 'mradio');
			showtagheader('tbody', 'cycletimetd', in_array($ruleinfo['cycletype'], array(2, 3)), 'sub');
			showsetting('credits_edit_cycletime', 'rule[cycletime]', $ruleinfo['cycletime'], 'text');
			showtagfooter('tbody');
			showtagheader('tbody', 'rewardnumtd',  in_array($ruleinfo['cycletype'], array(1, 2, 3, 4)), 'sub');
			showsetting('credits_edit_rewardnum', 'rule[rewardnum]', $ruleinfo['rewardnum'], 'text');
			showtagfooter('tbody');
		}
		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				if($rid) {
					showsetting("extcredits{$i}(".$_G['setting']['extcredits'][$i]['title'].')', "rule[extcredits{$i}]", $ruleinfo['extcredits'.$i], 'text', '', 0, $fid ? '('.$lang['credits_edit_globalrule'].':'.$globalrule['extcredits'.$i].')' : '');
				} else {
					showsetting("extcredits{$i}(".$_G['setting']['extcredits'][$i]['title'].')', "rule[extcredits{$i}]", $_G['setting']['creditspolicy']['lowerlimit'][$i], 'text');
				}
			}
		}
		showtablefooter();
		showtableheader('', 'nobottom');
		showsubmit('rulesubmit');
		showtablefooter();
		showformfooter();
	} else {
		$rid = $_GET['rid'];
		$rule = $_GET['rule'];
		if($rid) {
			if(!$rule['cycletype']) {
				$rule['cycletime'] = 0;
				$rule['rewardnum'] = 1;
			}
			$havecredit = $rule['usecustom'] ? true : false;
			for($i = 1; $i <= 8; $i++) {
				if(!$_G['setting']['extcredits'][$i]) {
					$rule['extcredits'.$i] = 0;
				}
			}
			foreach($rule as $key => $val) {
				$rule[$key] = intval($val);
			}
			if($fid) {
				$fids = $globalrule['fids'] ? explode(',', $globalrule['fids']) : array();
				if($havecredit) {
					$rule['rid'] = $rid;
					$rule['fid'] = $fid;
					$rule['rulename'] = $ruleinfo['rulename'];
					$rule['action'] = $ruleinfo['action'];
					$policy[$ruleinfo['action']] = $rule;
					if(!in_array($fid, $fids)) {
						$fids[] = $fid;
					}
				} else {
					if($rule['cycletype'] != 0 && ($rule['cycletype'] == 4 && !$rule['rewardnum'])) {
						require_once DISCUZ_ROOT.'./source/class/class_credit.php';
						credit::deletelogbyfid($rid, $fid);
					}
					unset($policy[$ruleinfo['action']]);
					if(in_array($fid, $fids)) {
						unset($fids[array_search($fid, $fids)]);
					}
				}
				C::t('forum_forumfield')->update($fid, array('creditspolicy' => serialize($policy)));
				C::t('common_credit_rule')->update($rid, array('fids' => implode(',', $fids)));
				updatecache('creditrule');
				cpmsg('credits_update_succeed', 'action=forums&operation=edit&anchor=credits&fid='.$fid, 'succeed');
			} else {
				C::t('common_credit_rule')->update($rid, $rule);
			}
			updatecache('creditrule');
		} else {
			$lowerlimit['creditspolicy']['lowerlimit'] = array();
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i]) {
					$lowerlimit['creditspolicy']['lowerlimit'][$i] = (float)$rule['extcredits'.$i];
				}
			}
			C::t('common_setting')->update('creditspolicy', $lowerlimit['creditspolicy']);
			updatecache(array('setting', 'creditrule'));
		}
		cpmsg('credits_update_succeed', 'action=credits&operation=list&anchor=policytable', 'succeed');
	}
}
?>