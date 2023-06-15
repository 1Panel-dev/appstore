<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_card.php 29335 2012-04-05 02:08:34Z cnteacher $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if($operation != 'export') {
	cpheader();
}

$operation = $_GET['operation'] ? $_GET['operation'] : 'set' ;
$card_setting = $_G['setting']['card'];

if($operation == 'set') {
	$nav = 'config';
	$submenu['set'] = 1;
} elseif ($operation == 'manage') {
	$nav = 'admin';
	$submenu['manage'] = 1;
} elseif ($operation == 'type') {
	$nav = 'nav_card_type';
	$submenu['type'] = 1;
} elseif ($operation == 'make') {
	$nav = 'nav_card_make';
	$submenu['make'] = 1;
} elseif ($operation == 'log') {
	$nav = 'nav_card_log';
} else {
	$nav = '';
}
if($nav != '') {
	if(!submitcheck('cardsubmit', 1) || $operation == 'manage' || $operation == 'type') {
		shownav('extended', 'nav_card', $nav);
		showsubmenu('nav_card', array(
			array('config', 'card', $submenu['set']),
			array('admin', 'card&operation=manage', $submenu['manage']),
			array('nav_card_type', 'card&operation=type', $submenu['type']),
			array('nav_card_make', 'card&operation=make', $submenu['make']),
			array(array('menu' => 'nav_card_log', 'submenu' => array(
				array('nav_card_log_add', 'card&operation=log&do=add', $_GET['do'] == 'add'),
				array('nav_card_log_del', 'card&operation=log&do=del', $_GET['do'] == 'del'),
				array('nav_card_log_cron', 'card&operation=log&do=cron', $_GET['do'] == 'cron')
			)), in_array($_GET['do'], array('add', 'del', 'cron')))
		));
	}
}
if($operation == 'set') {
	if(!submitcheck('cardsubmit')) {
		showformheader('card&operation=set&');
		showtableheader();
		showsetting('card_config_open', 'card_config_open', ($card_setting['open'] ? $card_setting['open'] : 0), 'radio');
		showsubmit('cardsubmit');
		showtablefooter();
		showformfooter();
	} else {
		C::t('common_setting')->update('card', array('open' => $_POST['card_config_open']));
		updatecache('setting');
		cpmsg('card_config_succeed', 'action=card&operation=set', 'succeed');
	}
} elseif($operation == 'manage'){
	if(submitcheck('cardsubmit')) {
		if(is_array($_POST['delete'])) {
			$delnum = C::t('common_card')->delete($_POST['delete']);
			$card_info = serialize(array('num' => ($delnum ? $delnum : 0)));
			$cardlog = array(
				'uid' => $_G['uid'],
				'cardrule' => '',
				'info' => $card_info,
				'dateline' => $_G['timestamp'],
				'operation' => 3,
				'username' => $_G['member']['username']
			);
			C::t('common_card_log')->insert($cardlog);
		}
	}
	$sqladd = cardsql();
	foreach($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== false && $val) {
			if(in_array($key, array('srch_username'))){
				$val = rawurlencode($val);
			}
			$export_url[] = $key.'='.$val;
		}
	}

	$perpage = max(20, empty($_GET['perpage']) ? 20 : intval($_GET['perpage']));
	echo '<script type="text/javascript" src="static/js/calendar.js"></script>';

	showtips('card_manage_tips');
	$card_type_option = '';
	foreach(C::t('common_card_type')->range(0, 0, 'ASC') as $result) {
		$card_type[$result['id']] = $result;
		$card_type_option .= "<option value=\"{$result['id']}\" ".($_GET['srch_card_type'] == $result['id'] ? 'selected' : '').">{$result['typename']}</option>";
	}
	showformheader('card', '', 'cdform', 'get');
	showtableheader();
	showtablerow('', array('width="80"', 'width="100"', 'width=100', 'width="260"'),
		array(
			cplang('card_number'), '<input type="text" name="srch_id" class="txt" value="'.$_GET['srch_id'].'" />',
			cplang('card_log_price').cplang('between'), '<input type="text" name="srch_price_min" class="txt" value="'.($_GET['srch_price_min'] ? $_GET['srch_price_min'] : '').'" />- &nbsp;<input type="text" name="srch_price_max" class="txt" value="'.($_GET['srch_price_max'] ? $_GET['srch_price_max'] :'' ).'" />',
		)
	);

	echo "<input type='hidden' name='action' value='card'><input type='hidden' name='operation' value='manage'>";
	$extcredits_option = "<option value=''>".cplang('nolimit')."</option>";
	foreach($_G['setting']['extcredits'] AS $key => $val) {
		$extcredits_option .= "<option value='$key' ".($_GET['srch_extcredits'] == $key ? 'selected' : '').">{$val['title']}</option>";
	}
	foreach(array('1' => cplang('card_manage_status_1'), '2' => cplang('card_manage_status_2'), '9' => cplang('card_manage_status_9')) AS $key => $val) {
		$status_option .= "<option value='{$key}' ".($_GET['srch_card_status'] == $key ? "selected" : '').">{$val}</option>";
	}
	showtablerow('', array(),
		array(
			cplang('card_extcreditsval'), '<input type="text" name="srch_extcreditsval" class="txt" style="width:42px;" value="'.$_GET['srch_extcreditsval'].'" /><select name="srch_extcredits">'.$extcredits_option.'</select>',
			cplang('card_status'), "<select name='srch_card_status'><option value=''>".cplang('nolimit')."</option>".$status_option."</select>",
		)
	);
	showtablerow('', array(),
		array(
			cplang('card_log_used_user'), '<input type="text" name="srch_username" class="txt" value="'.$_GET['srch_username'].'" />',
			cplang('card_used_dateline'), '<input type="text" name="srch_useddateline_start" class="txt" value="'.$_GET['srch_useddateline_start'].'" onclick="showcalendar(event, this);" />- &nbsp;<input type="text" name="srch_useddateline_end" class="txt" value="'.$_GET['srch_useddateline_end'].'" onclick="showcalendar(event, this)" />',
		)
	);

	$perpage_selected[$perpage] = "selected=selected";
	showtablerow('', array(),
		array(
			cplang('card_type'), '<select name="srch_card_type"><option value="">'.cplang('nolimit').'</option><option value="0" '.($_GET['srch_card_type'] != '' && $_GET['srch_card_type'] == 0 ? 'selected' : '').'>'.cplang('card_type_default').'</option>'.$card_type_option.'</select>',
			cplang('card_search_perpage'), '<select name="perpage" class="ps" onchange="this.form.submit();" ><option value="20" '.$perpage_selected[20].'>'.cplang('perpage_20').'</option><option value="50" '.$perpage_selected[50].'>'.cplang('perpage_50').'</option><option value="100" '.$perpage_selected[100].'>'.cplang('perpage_100').'</option></select>',
		)
	);

	showtablerow('', array('width="40"', 'width="100"', 'width=50', 'width="260"'),
		array(
			'<input type="submit" name="srchbtn" class="btn" value="'.$lang['search'].'" />',''
		)
	);
	showtablefooter();
	showformfooter();

	showformheader('card&operation=manage&');
	showtableheader('card_manage_title');
	showsubtitle(array('', cplang('card_number'), cplang('card_log_price'), cplang('card_extcreditsval'), cplang('card_type'), cplang('card_status'), cplang('card_log_used_user'), cplang('card_used_dateline'), cplang('card_make_cleardateline')/*, cplang('card_maketype')*/, cplang('card_maketime'), cplang('card_log_maker')));


	$start_limit = ($page - 1) * $perpage;
	$export_url[] = 'start='.$start_limit;
	foreach ($_GET AS $key => $val) {
		if(strpos($key, 'srch_') !== FALSE) {
			$url_add .= '&'.$key.'='.$val;
		}
	}
	$url = ADMINSCRIPT.'?action=card&operation=manage&page='.$page.'&perpage='.$perpage.$url_add;
	$count = $sqladd ? C::t('common_card')->count_by_where($sqladd) : C::t('common_card')->count();
	if($count) {
		$multipage = multi($count, $perpage, $page, $url, 0, 3);
		foreach(C::t('common_card')->fetch_all_by_where($sqladd, $start_limit, $perpage) as $result) {
			$userlist[$result['makeruid']] = $result['makeruid'];
			$userlist[$result['uid']] = $result['uid'];
			$cardlist[] = $result;
		}
		if($userlist) {
			$members = C::t('common_member')->fetch_all($userlist);
			unset($userlist);
		}

		foreach($cardlist AS $key => $val) {
			showtablerow('', array('class="smallefont"', '', '', '', '', '', '', '', '', '', '', ''), array(
				'<input class="checkbox" type="checkbox" name="delete[]" value="'.$val[id].'">',
				$val['id'],
				$val['price'].cplang('card_make_price_unit'),
				$val['extcreditsval'].$_G['setting']['extcredits'][$val['extcreditskey']]['title'],
				$card_type[$val['typeid']]['typename'] ? $card_type[$val['typeid']]['typename'] : cplang('card_type_default'),
				cplang("card_manage_status_".$val['status']),
				$val['uid'] ? "<a href='home.php?mod=space&uid={$val[uid]}' target='_blank'>".$members[$val['uid']]['username'] : ' -- ',
				$val['useddateline'] ? dgmdate($val['useddateline']) : ' -- ',
				$val['cleardateline'] ? dgmdate($val['cleardateline'], 'Y-m-d') : cplang('card_make_cleardateline_none'),
				dgmdate($val['dateline'], 'u'),
				"<a href='home.php?mod=space&uid={$val['makeruid']}' target='_blank'>".$members[$val['makeruid']]['username']."</a>"
			));
		}
		echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
		showsubmit('cardsubmit', 'submit', 'del', '<a href="'.ADMINSCRIPT.'?action=card&operation=export&'.implode('&', $export_url).'" title="'.$lang['card_export_title'].'">'.$lang['card_export'].'</a>', $multipage, false);
	}

	showtablefooter();
	showformfooter();

} elseif($operation == 'type') {
	if(submitcheck('cardsubmit')) {
		if(is_array($_POST['delete'])) {
			C::t('common_card_type')->delete($_POST['delete']);
			C::t('common_card')->update_by_typeid($_POST['delete'], array('typeid'=>1));
		}
		if(is_array($_POST['newtype'])) {
			$_POST['newtype'] = dhtmlspecialchars(daddslashes($_POST['newtype']));
			foreach($_POST['newtype'] AS $key => $val) {
				if(trim($val)) {
					C::t('common_card_type')->insert(array('typename' => trim($val)));
				}
			}
		}
	}
	showtips('card_type_tips');
	showformheader('card&operation=type&');
	showtableheader();
	showtablerow('class="header"', array('', ''), array(
		cplang('delete'),
		cplang('card_type'),
	));

	showtablerow('', '', array(
		'<input class="checkbox" type="checkbox" value ="" disabled="disabled" >',
		cplang('card_type_default'),
	));
	foreach(C::t('common_card_type')->range(0, 0, 'ASC') as $result) {
		showtablerow('', '', array(
		'<input class="checkbox" type="checkbox" name ="delete[]" value ="'.$result['id'].'" >',
		$result['typename'],
		));
	}
	echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1,''], [1,'<input type="text" class="txt" size="30" name="newtype[]">']],
	];
	</script>
EOT;
	echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';
	showsubmit('cardsubmit', 'submit', 'select_all');
	showtablefooter();
	showformfooter();
} elseif($operation == 'make') {
	if(!submitcheck('cardsubmit', 1)) {
		if($card_log = C::t('common_card_log')->fetch_by_operation(1)) {
			$card_log['rule'] = dunserialize($card_log['cardrule']);
		}
		$card_type[] = array(0, cplang('card_type_default'));
		foreach(C::t('common_card_type')->range(0, 0, 'ASC') as $result) {
			$card_type[] = array($result['id'], $result['typename']);
		}

		echo '<script type="text/javascript" src="static/js/calendar.js"></script>';
		showformheader('card&operation=make&');
		showtips('card_make_tips');
		showtableheader();

		showsetting('card_make_rule', '', '', '<input type="text" name="rule" class="txt" value="'.($card_log['rule']['rule'] ? $card_log['rule']['rule'] : '').'" onkeyup="javascript:checkcardrule(this);"><br /><span id="cardrule_view" class="tips2" style="display:none;"></span>');
echo <<<EOT
	<script type="text/javascript" charset="gbk">
		function checkcardrule(obj) {
			var chrLength = obj.value.length;
			$('cardrule_view').style.display = "";
			$('cardrule_view').innerHTML = "{$lang['card_number']}<strong>"+chrLength+"</strong>{$lang['card_number_unit']}";
		}
	</script>
EOT;

		showsetting('card_type', array('typeid', $card_type), $card_log['rule']['typeid'], 'select');
		showsetting('card_make_num', 'num', ($card_log['rule']['num'] ? $card_log['rule']['num'] : 1), 'text');
		$extcredits_option = '';
		foreach($_G['setting']['extcredits'] AS $key => $val) {
			$extcredits_option .= "<option value='$key'".($card_log['rule']['extcreditskey'] == $key ? 'selected' : '').">{$val['title']}</option>";
		}
		showsetting('card_make_extcredits', '', '', '<select name="extcreditskey" style="width:80px;">'.$extcredits_option.'</select><input type="text" name="extcreditsval" value="'.($card_log['rule']['extcreditsval'] ? $card_log['rule']['extcreditsval'] : 1).'" class="txt" style="width:50px;">');
		showsetting('card_make_price', 'price', ($card_log['rule']['price'] ? $card_log['rule']['price'] : 0), 'text');

		showsetting('card_make_cleardateline', 'cleardateline', date("Y-m-d", $_G['timestamp']+31536000), 'calendar', '', 0, '');

		showsetting('card_make_description', 'description', $card_log['description'] , 'text');
		showsubmit('cardsubmit');
		showtablefooter();
		showformfooter();
	} else {
		$_GET['rule'] = rawurldecode(trim($_GET['rule']));
		$_GET['num'] = intval($_GET['num']);
		list($y, $m, $d) = explode("-", $_GET['cleardateline']);
		$_GET['step'] = $_GET['step'] ? $_GET['step'] : 1;
		$cleardateline = $_GET['cleardateline'] && $y && $m ? mktime(23, 59, 59, $m, $d, $y) : 0 ;
		if($cleardateline < TIMESTAMP) {
			cpmsg('card_make_cleardateline_early', '', 'error');
		}
		if(!$_GET['rule']) {
			cpmsg('card_make_rule_empty', '', 'error');
		}
		if($_GET['num'] < 1) {
			cpmsg('card_make_num_error', '', 'error');
		}
		include libfile("class/card");
		$card = new card();
		$checkrule = $card->checkrule($_GET['rule'], 1);

		if($checkrule === -2) {
			cpmsg('card_make_rule_error', '', 'error');
		}

		if($_GET['step'] == 1) {
			$card_rule = serialize(array('rule' => $_GET['rule'], 'price' => $_GET['price'], 'extcreditskey' => $_GET['extcreditskey'], 'extcreditsval' => $_GET['extcreditsval'], 'num' => $_GET['num'], 'cleardateline' => $cleardateline, 'typeid' => $_GET['typeid']));
			$cardlog = array(
				'uid' => $_G['uid'],
				'username' => $_G['member']['username'],
				'cardrule' => $card_rule,
				'dateline' => $_G['timestamp'],
				'description' => $_GET['description'],
				'operation' => 1,

			);
			$logid = C::t('common_card_log')->insert($cardlog, true);
		}
		$onepage_make = 500;
		$_GET['logid'] = $logid ? $logid : $_GET['logid'];
		if($_GET['num'] > $onepage_make) {
			$step_num = ceil($_GET['num']/$onepage_make);
			if($step_num > 1) {
				if($_GET['step'] == $step_num) {
					if($_GET['num'] % $onepage_make == 0) {
						$makenum = $onepage_make;
					} else {
						$makenum = $_GET['num'] % $onepage_make;
					}
				} else {
					$makenum = $onepage_make;
					$nextstep = $_GET['step'] + 1;
				}
			}
		} else {
			$makenum = $_GET['num'];
		}

		$cardval = array(
			'typeid' => $_GET['typeid'],
			'price' => $_GET['price'],
			'extcreditskey' => $_GET['extcreditskey'],
			'extcreditsval' => $_GET['extcreditsval'],
			'cleardateline' => $cleardateline
		);
		$card->make($_GET['rule'], $makenum, $cardval);
		$_GET['succeed_num'] += $card->succeed;
		$_GET['fail_num'] += $card->fail;
		if($nextstep) {
			$_GET['rule'] = rawurlencode($_GET['rule']);
			$nextlink = "action=card&operation=make&rule={$_GET['rule']}&num={$_GET['num']}&price={$_GET['price']}&extcreditskey={$_GET['extcreditskey']}&extcreditsval={$_GET['extcreditsval']}&cleardateline={$_GET['cleardateline']}&step={$nextstep}&succeed_num={$_GET['succeed_num']}&fail_num={$_GET['fail_num']}&typeid={$_GET['typeid']}&logid={$_GET['logid']}&cardsubmit=yes";
			cpmsg('card_make_step', $nextlink, 'loading', array('step' => $nextstep - 1, 'step_num' => $step_num, 'succeed_num' => $card->succeed, 'fail_num' => $card->fail));
		} else {
			$card_info = serialize(array('num' => $_GET['num'], 'succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']));
			C::t('common_card_log')->update($_GET['logid'], array('info'=>$card_info));
			if(ceil($_GET['num']*0.6) > $_GET['succeed_num']) {
				cpmsg('card_make_rate_succeed', 'action=card&operation=make', 'succeed', array('succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']));
			}
			cpmsg('card_make_succeed', 'action=card&operation=manage', 'succeed', array('succeed_num' => $_GET['succeed_num'], 'fail_num' => $_GET['fail_num']));
		}

	}
} elseif($operation == 'log'){
	showformheader('card&operation=log&');
	showtableheader();

	$perpage = max(20, empty($_GET['perpage']) ? 20 : intval($_GET['perpage']));
	$start_limit = ($page - 1) * $perpage;

	$do = in_array($_GET['do'], array('add', 'task', 'del', 'cron')) ? $_GET['do'] : 'add';
	$operation = 0;
	switch($do) {
		case 'add':
			$operation = 1;
			break;
		case 'task':
			$operation = 2;
			break;
		case 'del':
			$operation = 3;
			break;
		case 'cron':
			$operation = 9;
			break;
	}

	if($do == 'add' || $do == 'task') {
		$showtabletitle = array(
			cplang('time'),
			cplang('card_log_operation'),
			cplang('card_log_user'),
			cplang('card_log_rule'),
			cplang('card_log_add_info'),
			cplang('card_log_description')
		);
	} elseif($do == 'del') {
		$showtabletitle = array(
			cplang('time'),
			cplang('card_log_operation'),
			cplang('card_log_user'),
			cplang('card_log_del_info')
		);

	} elseif($do == 'cron') {
		$showtabletitle = array(
			cplang('time'),
			cplang('card_log_operation'),
			cplang('card_log_cron_info')
		);
	}

	showtablerow('class="header"', array('class="td21"','class="td23"','class="td23"','class="td21"','class="td23"'), $showtabletitle);

	$count = C::t('common_card_log')->count_by_operation($operation);
	if($count) {
		$url = ADMINSCRIPT."?action=card&operation=log&do=".$do."&page=".$page.'&perpage='.$perpage;
		$multipage = multi($count, $perpage, $page, $url, 0, 3);

		foreach(C::t('common_card_log')->fetch_all_by_operation($operation, $start_limit, $perpage) as $result) {
			$result['info_arr'] = dunserialize($result['info']);
			if($result['operation'] == 1 || $result['operation'] == 2) {
				$result['cardrule_arr'] = dunserialize($result['cardrule']);
				$showrule = array(
					$result['cardrule_arr']['rule'],
					cplang('card_log_price').' : '.$result['cardrule_arr']['price'].cplang('card_make_price_unit'),
					cplang('card_log_make_num').' : '.$result['cardrule_arr']['num'],
					cplang('card_extcreditsval').' : '.$result['cardrule_arr']['extcreditsval'].$_G['setting']['extcredits'][$result['cardrule_arr']['extcreditskey']]['title'],
					cplang('card_make_cleardateline').' : '.($result['cardrule_arr']['cleardateline'] ? dgmdate($result['cardrule_arr']['cleardateline'], 'Y-m-d H:i') : cplang('card_make_cleardateline_none')),
				);

				$showinfo = array(
					cplang('succeed_num').' : '.$result['info_arr']['succeed_num'],
					cplang('fail_num').' : '.$result['info_arr']['fail_num']
				);
				$showtablerow = array(
					dgmdate($result['dateline']),
					$result['operation'] == 1 ? cplang('card_log_operation_add') : cplang('card_log_operation_task'),
					$result['username'],
					implode("<br />", $showrule),
					implode("<br />", $showinfo),
					$result['description']
				);
			} elseif ($result['operation'] == 3 || $result['operation'] == 9) {
				$showinfo =array(
					cplang('card_log_num').$result['info_arr']['num'],
				);
				$showtablerow = $result['operation'] == 3 ? array(
					dgmdate($result['dateline']),
					cplang('card_log_operation_del'),
					$result['username'],
					implode("<br />", $showinfo),
				) : array(
					dgmdate($result['dateline']),
					cplang('card_log_operation_cron'),
					implode("<br />", $showinfo),
				);
			}
			showtablerow('', array('class="smallefont"'), $showtablerow);
		}
	} else {

	}
	showsubmit('', '', '', '', $multipage);
	showtablefooter();
	showformfooter();
} elseif ($operation == 'export'){

	$sqladd = cardsql();
	$_GET['start'] = intval($_GET['start']);
	$count = $sqladd ? C::t('common_card')->count_by_where($sqladd) : C::t('common_card')->count();
	if($count) {
		$cardtype = C::t('common_card_type')->range();
		$count = min(10000, $count);
		foreach(C::t('common_card')->fetch_all_by_where($sqladd, $_GET['start'], $count) as $result) {
			$userlist[$result['uid']] = $result['uid'];
			$userlist[$result['makeruid']] = $result['makeruid'];
			$result['extcreditsval'] = $result['extcreditsval'].$_G['setting']['extcredits'][$result['extcreditskey']]['title'];
			unset($result['extcreditskey']);
			unset($result['maketype']);
			$cardlist[] = $result;
		}
		if($userlist) {
			$members = C::t('common_member')->fetch_all($userlist);
			unset($userlist);
		}

		foreach($cardlist AS $key => $val) {
			foreach($val as $skey => $sval) {
				$sval = preg_replace('/\s+/', ' ', $sval);
				if($skey == 'id' && !$title['id']) { $title['id'] = cplang('card_number'); }
				if($skey == 'typeid') {
					if(!$title['typeid']) {
						$title['typeid'] = cplang("card_type");
					}
					$sval = $sval != 0 ? $cardtype[$sval]['typename'] : cplang('card_type_default');
				}
				if(in_array($skey, array('uid', 'makeruid'))) {
					if($skey == 'makeruid' && !$title['makeruid']) {
						$title['makeruid'] = cplang("card_log_maker");
					}
					if($skey == 'uid' && !$title['uid']) {
						$title['uid'] = cplang("card_log_used_user");
					}

					$sval = $members[$sval]['username'];
				}
				if($skey == 'price') {
					if(!$title['price']) {
						$title['price'] = cplang('card_log_price');
					}
					$sval = $sval.cplang("card_make_price_unit");
				}
				if($skey == 'extcreditsval') {
					if(!$title['extcreditsval']) {
						$title['extcreditsval'] = cplang('card_extcreditsval');
					}
				}
				if($skey == 'status') {
					if(!$title['status']) {
						$title['status'] = cplang('card_status');
					}
					$sval = cplang("card_manage_status_".$sval);
				}
				if(in_array($skey, array('dateline', 'cleardateline', 'useddateline'))) {
					if($skey == 'dateline' && !$title['dateline']) {
						$title['dateline'] = cplang('card_maketime');
					}
					if($skey == 'cleardateline' && !$title['cleardateline']) {
						$title['cleardateline'] = cplang('card_make_cleardateline');
					}
					if($skey == 'useddateline' && !$title['useddateline']) {
						$title['useddateline'] = cplang('card_used_dateline');
					}

					$sval = $sval ? date("Y-m-d", $sval) : '';
				}
				$detail .= strlen($sval) > 11 && is_numeric($sval) ? '['.$sval.'],' : $sval.',';
			}
			$detail = $detail."\n";
		}

	}
	$detail = implode(',', $title)."\n".$detail;
	$filename = 'card_'.date('Ymd', TIMESTAMP).'.csv';

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	if($_G['charset'] != 'gbk') {
		$detail = diconv($detail, $_G['charset'], 'GBK');
	}
	echo $detail;
	exit();
} else {
	cpmsg('action_noaccess', '', 'error');
}


function cardsql() {


	$_GET = daddslashes($_GET);

	$_GET['srch_id'] = trim($_GET['srch_id']);

	$_GET['srch_price_max'] = intval($_GET['srch_price_max']);
	$_GET['srch_price_min'] = intval($_GET['srch_price_min']);

	$_GET['srch_useddateline'] = trim($_GET['srch_useddateline']);
	$_GET['srch_username'] = trim($_GET['srch_username']);
	$_GET['srch_extcredits'] = trim($_GET['srch_extcredits']);
	$_GET['srch_extcreditsval'] = intval($_GET['srch_extcreditsval']) > 0 ? intval($_GET['srch_extcreditsval']) : '' ;
	$_GET['srch_username'] = trim($_GET['srch_username']);

	$_GET['srch_useddateline_start'] = trim($_GET['srch_useddateline_start']);
	$_GET['srch_useddateline_end'] = trim($_GET['srch_useddateline_end']);

	$sqladd = '';
	if($_GET['srch_id']) {
		$sqladd .= " AND id LIKE '%{$_GET['srch_id']}%' ";
	}
	if($_GET['srch_card_type'] != '') {
		$sqladd .= " AND typeid = '{$_GET['srch_card_type']}'";
	}
	if($_GET['srch_price_min'] && !$_GET['srch_price_max']) {
		$sqladd .= " AND price = '{$_GET['srch_price_min']}'";
	} elseif($_GET['srch_price_max'] && !$_GET['srch_price_min']) {
		$sqladd .= " AND price = '{$_GET['srch_price_max']}'";
	} elseif($_GET['srch_price_min'] && $_GET['srch_price_max']) {
		$sqladd .= " AND price between '{$_GET['srch_price_min']}' AND '{$_GET['srch_price_max']}'";
	}

	if($_GET['srch_extcredits']) {
		$sqladd .= " AND extcreditskey = '{$_GET['srch_extcredits']}'";
	}
	if($_GET['srch_extcreditsval']) {
		$sqladd .= " AND extcreditsval = '{$_GET['srch_extcreditsval']}'";
	}

	if($_GET['srch_username']) {
		$uid = ($uid = C::t('common_member')->fetch_uid_by_username($_GET['srch_username'])) ? $uid : C::t('common_member_archive')->fetch_uid_by_username($_GET['srch_username']);
		$sqladd .= " AND uid = '{$uid}'";
	}
	if($_GET['srch_card_status']) {
		$sqladd .= " AND status = '{$_GET['srch_card_status']}'";
	}
	if($_GET['srch_useddateline_start'] || $_GET['srch_useddateline_end']) {
		if($_GET['srch_useddateline_start']) {
			list($y, $m, $d) = explode("-", $_GET['srch_useddateline_start']);
			$sqladd .= " AND useddateline >= '".mktime('0', '0', '0', $m, $d, $y)."' ";
		}
		if($_GET['srch_useddateline_end']) {
			list($y, $m, $d) = explode("-", $_GET['srch_useddateline_end']);
			$sqladd .= " AND useddateline <= '".mktime('23', '59', '59', $m, $d, $y)."' AND useddateline <> 0 ";
		}
	}
	return $sqladd ? ' 1 '.$sqladd : '';
}
?>