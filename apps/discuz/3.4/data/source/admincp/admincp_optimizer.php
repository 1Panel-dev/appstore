<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_optimizer.php 33867 2013-08-23 06:12:21Z jeffjzhang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$optimizer_option = array(
	'optimizer_thread',
	'optimizer_setting',
	'optimizer_post',
	'optimizer_member',
	'optimizer_dbbackup',
	'optimizer_dbbackup_clean',
	'optimizer_seo'
);

$security_option = array(
	'optimizer_inviteregister',
	'optimizer_emailregister',
	'optimizer_pwlength',
	'optimizer_regmaildomain',
	'optimizer_ipregctrl',
	'optimizer_newbiespan',
	'optimizer_editperdel',
	'optimizer_recyclebin',
	'optimizer_forumstatus',
	'optimizer_usergroup9',
	'optimizer_usergroup4',
	'optimizer_usergroup5',
	'optimizer_usergroup6',
	'optimizer_attachexpire',
	'optimizer_attachrefcheck',
	'optimizer_filecheck',
	'optimizer_plugin',
	'optimizer_loginpwcheck',
	'optimizer_loginoutofdate',
	'optimizer_dbbackup_visit',
	'optimizer_filesafe',
	'optimizer_remote',
);

$serversec_option = array(
	'optimizer_dos8p3',
	'optimizer_httphost'
);

if($_G['setting']['connect']['allow']) {
	$security_option[] = 'optimizer_postqqonly';
	$security_option[] = 'optimizer_aggid';
}

$check_record_time_key = 'check_record_time';
if(in_array($operation, array('security', 'serversec', 'performance'))) {
	$_GET['anchor'] = $operation;
	$operation = '';
}
if($_GET['anchor'] == 'security') {
	shownav('safe', 'menu_security');
	$optimizer_option = $security_option;
	$check_record_time_key = 'security_check_record_time';
	showsubmenu('menu_security');
} elseif($_GET['anchor'] == 'serversec') {
	shownav('safe', 'menu_serversec');
	$optimizer_option = $serversec_option;
	$check_record_time_key = 'serversec_check_record_time';
	showsubmenu('menu_serversec');
} elseif($_GET['anchor'] == 'performance') {
	shownav('founder', 'menu_optimizer');
	showsubmenu('menu_optimizer');
}

if($operation) {
	$type = $_GET['type'];
	if(!in_array($type, $optimizer_option)) {
		cpmsg('parameters_error', '', 'error');
	}

	include_once 'source/discuz_version.php';
	$optimizer = new optimizer($type);
}

$_GET['anchor'] = in_array($_GET['anchor'], array('security', 'serversec', 'performance')) ? $_GET['anchor'] : 'security';
$current = array($_GET['anchor'] => 1);
showmenu('nav_founder_optimizer', array(
	array('founder_optimizer_security', 'optimizer&anchor=security', $current['security']),
	array('founder_optimizer_serversec', 'optimizer&anchor=serversec', $current['serversec']),
	array('founder_optimizer_performance', 'optimizer&anchor=performance', $current['performance']),
));

if($operation == 'optimize_unit') {

	$optimizer->optimizer();

} elseif($operation == 'check_unit') {

	$checkstatus = $optimizer->check();

	C::t('common_optimizer')->update($type.'_checkrecord', ($checkstatus['status'] == 1 ? $checkstatus['status'] : 0));
	C::t('common_optimizer')->update($check_record_time_key, $_G['timestamp']);

	include template('common/header_ajax');
	echo '<script type="text/javascript">updatecheckstatus(\''.$type.'\', \''.$checkstatus['lang'].'\', \''.$checkstatus['status'].'\', \''.$checkstatus['type'].'\', \''.$checkstatus['extraurl'].'\');</script>';
	include template('common/footer_ajax');
	exit;

} elseif($operation == 'setting_optimizer') {

	if(submitcheck('setting_optimizer', 1)) {
		$setting_options = $_GET['options'];
		if($optimizer->option_optimizer($setting_options)) {
			cpmsg('founder_optimizer_setting_succeed', 'action=optimizer&operation=setting_optimizer&type=optimizer_setting', 'succeed');
		} else {
			cpmsg('founder_optimizer_setting_error', '', 'error');
		}
	} else {

		showformheader('optimizer&operation=setting_optimizer&type=optimizer_setting');
		showtableheader();

		$option = $optimizer->get_option();

		echo '<tr class="header">';
		echo '<th></th>';
		echo '<th class="td24">'.$lang['founder_optimizer_setting_option'].'</th>';
		echo '<th>'.$lang['founder_optimizer_setting_option_description'].'</th>';
		echo '<th class="td24">'.$lang['founder_optimizer_setting_description'].'</th>';
		echo '</tr>';
		foreach($option as $setting) {
			$color = ' style="'.($setting[4] ? 'color:red;' : 'color:green').'"';
			echo '<tr>';
			echo '<td><input type="checkbox" name="options[]" value="'.$setting[0].'" '.($setting[4] ? 'checked' : 'disabled').' /></td>';
			echo '<td'.$color.'>'.$setting[1].'</td>';
			echo '<td'.$color.'>'.$setting[2].'</td>';
			echo '<td'.$color.'>'.$setting[3].'</td>';
			echo '</tr>';
		}
		showsubmit('setting_optimizer');

		showtablefooter();
		showformfooter();
	}


} else {

	$checkrecordtime = C::t('common_optimizer')->fetch($check_record_time_key);

	if(!$_GET['checking']) {
		showtips('optimizer_'.$_GET['anchor'].'_tips');
	}

	showtableheader();

	echo '<div class="optblock cl">';
	echo $_GET['checking'] ? '<a href="javascript:;" id="checking" class="btn_big">'.$lang['founder_optimizer_checking'].'</a>' :
		'<a href="'.ADMINSCRIPT.'?action=optimizer&checking=1&anchor='.$_GET['anchor'].'" id="checking" class="btn_big">'.$lang['founder_optimizer_start_check'].'</a>';
	if($_GET['checking']) {
		echo '<div class="pbg" id="processid">';
		echo '<div class="pbr" style="width: 0;" id="percentprocess"></div>';
		echo '<div class="xs0" id="percent">0%</div>';
		echo '</div>';
	}
	echo '<div id="checkstatus">';
	if(!$checkrecordtime) {
		echo $lang['founder_optimizer_first_use'];
	} else {
		$num = 0;
		$checkrecordkey = array();
		foreach($optimizer_option as $option) {
			$checkrecordkey[] = $option.'_checkrecord';
		}
		foreach(C::t('common_optimizer')->fetch_all($checkrecordkey) as $checkrecordvalue) {
			if($checkrecordvalue['v'] == 1) {
				$num++;
			}
		}
		if(!$_GET['checking']) {
			echo $lang['founder_optimizer_lastcheck'].dgmdate($checkrecordtime).$lang['founder_optimizer_findnum'].$num.$lang['founder_optimizer_neednum'];
		}
	}
	echo '</div>';
	echo '</div>';
	if($_GET['checking']) {
		$inc_unit = ceil(100/count($optimizer_option));
		$adminscipt = ADMINSCRIPT;
		$C = '$C';
		print <<<END
			<script type="text/javascript">
				var checkpercent = 0;
				var checknum = 0;
				var optimize_num = 0;
				var security_num = 0;
				var tip_num = 0;
				var securitygrade = '';
				function updatecheckpercent() {
					checkpercent += {$inc_unit};
					checknum++;
					$('percent').innerHTML = parseInt(checkpercent) + '%';
					$('percentprocess').style.width = parseInt(checkpercent) * 2 + 'px';
				}
				function updatecheckstatus(id, msg, status, type, extraurl) {
					var optimize_table = $('optimizerable');
					var optimize_tablerows = optimize_table.rows.length;
					var security_table = $('securityoption');
					var security_tablerows = security_table.rows.length;
					var tip_table = $('tipoption');
					var tip_tablerows = tip_table.rows.length;

					if(id == 'optimizer_upgrade' || id == 'optimizer_patch') {
						securitygrade = '{$lang[founder_optimizer_low]}';
					}

					var optiontype = id;
					id = 'progress_' + id;
					$(id + '_tr').style.display = 'none';
					var color = 'green';
					if(status == 1) {
						color = 'red';
						optimize_num++;
						$('optimizerablenum').innerHTML = optimize_num;
						optimize_table.style.display = 'block';
						var newtr = optimize_table.insertRow(optimize_tablerows);
						newtr.className = 'ooclass';
					} else if(status == 2) {
						color = 'blue';
						tip_num++;
						$('tipoptionnum').innerHTML = tip_num;
						tip_table.style.display = 'block';
						var newtr = tip_table.insertRow(tip_tablerows);
						newtr.className = 'toclass';
						newtr.style.display = 'none';
					} else {
						color = 'green';
						security_num++;
						$('securityoptionnum').innerHTML = security_num;
						security_table.style.display = 'block';
						var newtr = security_table.insertRow(security_tablerows);
						newtr.className = 'soclass';
						newtr.style.display = 'none';
					}
					var statusstr = '';
					if(status != 0) {
						if(type == 'header') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor=$_GET[anchor]&type='+ optiontype + extraurl + '" target="_blank">{$lang[founder_optimizer_optimizer]}</a>';
						} else if(type == 'view') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor=$_GET[anchor]&type='+ optiontype + extraurl + '" target="_blank">{$lang[founder_optimizer_view]}</a>';
						} else if(type == 'scan') {
							statusstr = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&anchor=$_GET[anchor]&type='+ optiontype + extraurl + '" target="_blank">{$lang[founder_optimizer_scan]}</a>';
						}
					}
					newtr.insertCell(0).innerHTML = $(id + '_unit').innerHTML;
					newtr.insertCell(1).innerHTML = msg;
					newtr.insertCell(2).innerHTML = statusstr;

					if(parseInt(checkpercent) >= 100) {
						$('checking').innerHTML = '{$lang[founder_optimizer_recheck_js]}';
						$('checking').href = '{$adminscipt}?action=optimizer&checking=1&anchor={$_GET[anchor]}';
						$('processid').style.display = 'none';
						if('$_GET[anchor]' == 'security') {
							if(securitygrade == '') {
								if(optimize_num <= 1) {
									securitygrade = '{$lang[founder_optimizer_high]}';
								} else if(optimize_num >=2 && optimize_num <=4) {
									securitygrade = '{$lang[founder_optimizer_middle]}';
								} else {
									securitygrade = '{$lang[founder_optimizer_low]}';
								}
							}
							$('checkstatus').innerHTML = '{$lang[founder_optimizer_check_complete_js]}' + checknum + '{$lang[founder_optimizer_findnum]}' +  optimize_num + '{$lang[founder_optimizer_neednum]}' + ' {$lang[founder_optimizer_level]}: <span style="color:green;font-size:16px;font-weight:700;">' + securitygrade + '</span>';
						} else {
							$('checkstatus').innerHTML = '{$lang[founder_optimizer_check_complete_js]}' + checknum + '{$lang[founder_optimizer_findnum]}' +  optimize_num + '{$lang[founder_optimizer_neednum]}';
						}
					}
				}
				function showoptions(obj, option) {
					var o = $C(option);
					var isopen = 0;
					if(obj.innerHTML == '[-]') {
						isoepn = 0;
						obj.innerHTML = '[+]';
					} else {
						isopen = 1;
						obj.innerHTML = '[-]';
					}
					for(var i=0; i<o.length; i++) {
						if(isopen == 1) {
							o[i].style.display = '';
						} else {
							o[i].style.display = 'none';
						}
					}
				}
				function showlistmore(btnid, classname) {
					var btn = $(btnid);
					var o = $C(classname);
					btn.style.display = 'none';
					for(var i=0; i<o.length; i++) {
						o[i].style.display = 'block';
					}
				}
			</script>
END;
		echo '<table class="tb tb2 tb3" style="margin-top:0;">';
		foreach($optimizer_option as $option) {
			echo '<tr class="hover" id="progress_'.$option.'_tr">';
			echo '<td width="200"><div id="progress_'.$option.'_unit">'.$lang['optimizer_check_unit_'.$option].'</td>';
			echo '<td width="350"><div id="progress_'.$option.'">'.$lang['founder_optimizer_checking'].'...</div></td><script type="text/javascript">ajaxget(\''.ADMINSCRIPT.'?action=optimizer&operation=check_unit&type='.$option.'&anchor='.$_GET['anchor'].'\', \'progress_'.$option.'\', \'\', \'\', \'\', updatecheckpercent)</script>';
			echo '<td><div id="progress_'.$option.'_status"></div></td>';
			echo '</tr>';
		}
		echo '</table>';

		echo '<table id="optimizerable" class="tb tb2 tb3" style="margin-top:0;display:none;">';
		echo '<tr><td width="200" style="color:red;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'ooclass\')">[-]</a> '.$lang['founder_optimizer_needopti'].'(<span id="optimizerablenum"></span>)</td><td width="350"></td><td></td></tr>';
		echo '</table>';
		echo '<table id="securityoption" class="tb tb2 tb3" style="margin-top:0;display:none;">';
		echo '<tr><td width="200" style="color:green;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'soclass\')">[+]</a> '.$lang['founder_optimizer_safe'].'(<span id="securityoptionnum"></span>)</td><td width="350"></td><td></td></tr>';
		echo '</table>';
		echo '<table id="tipoption" class="tb tb2 tb3" style="margin-top:0;display:none;">';
		echo '<tr><td width="200" style="color:blue;font-weight:700;"><a href="javascript:;" onclick="showoptions(this, \'toclass\')">[+]</a> '.$lang['founder_optimizer_notice'].'(<span id="tipoptionnum"></span>)</td><td width="350"></td><td></td></tr>';
		echo '</table>';
	}

	showtablefooter();
}

?>