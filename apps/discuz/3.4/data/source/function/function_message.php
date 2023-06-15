<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_message.php 32580 2013-02-22 03:40:28Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function dshowmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G, $show_message;
	$_G['messageparam'] = func_get_args();
	if(empty($_G['inhookscript']) && defined('CURMODULE')) {
		hookscript(CURMODULE, $_G['basescript'], 'messagefuncs', array('param' => $_G['messageparam']));
	}
	if($extraparam['break']) {
		return;
	}
	$_G['inshowmessage'] = true;

	$param = array(
		'header'	=> false,
		'timeout'	=> null,
		'refreshtime'	=> null,
		'closetime'	=> null,
		'locationtime'	=> null,
		'alert'		=> null,
		'return'	=> false,
		'redirectmsg'	=> 0,
		'msgtype'	=> 1,
		'showmsg'	=> true,
		'showdialog'	=> false,
		'login'		=> false,
		'handle'	=> false,
		'extrajs'	=> '',
		'striptags'	=> true,
	);

	$navtitle = lang('core', 'title_board_message');

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('common/showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @dunserialize($_G['setting']['msgforward']);
	$handlekey = $leftmsg = '';

	if(defined('IN_MOBILE')) {
		unset($extraparam['showdialog']);
		unset($extraparam['closetime']);
		unset($extraparam['extrajs']);

		if(!$url_forward && dreferer() && IN_MOBILE == 1) {
			$url_forward = $referer = dreferer();
		}
		if(!empty($url_forward) && strpos($url_forward, 'mobile') === false) {
			$url_forward_arr = explode("#", $url_forward);
			if(strpos($url_forward_arr[0], '?') !== false) {
				$url_forward_arr[0] = $url_forward_arr[0].'&mobile='.IN_MOBILE;
			} else {
				$url_forward_arr[0] = $url_forward_arr[0].'?mobile='.IN_MOBILE;
			}
			$url_forward = implode("#", $url_forward_arr);
		}
	}


	if(empty($_G['inajax']) && (!empty($_GET['quickforward']) || $_G['setting']['msgforward']['quick'] && empty($extraparam['clean_msgforward']) && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	$_GET['handlekey'] = !empty($_GET['handlekey']) && preg_match('/^\w+$/', $_GET['handlekey']) ? $_GET['handlekey'] : '';
	if(!empty($_G['inajax'])) {
		$handlekey = $_GET['handlekey'] = !empty($_GET['handlekey']) ? dhtmlspecialchars($_GET['handlekey']) : '';
		$param['handle'] = true;
		$param['msgtype'] = empty($_GET['ajaxmenu']) && (empty($_POST) || !empty($_GET['nopost'])) ? 2 : 3;
	}
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	if(array_key_exists('set', $extraparam)) {
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	$timedefault = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
	if($param['timeout'] !== null) {
		$refreshsecond = !empty($timedefault) ? $timedefault : 3;
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($_GET['ajaxdata'] === 'json') {
		$param['header'] = '';
	}

	if($param['header']) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}
	$url_forward_js = addslashes(str_replace('\\', '%27', $url_forward));
	if($param['location'] && !empty($_G['inajax'])) {
		include template('common/header_ajax');
		echo '<script type="text/javascript" reload="1">window.location.href=\''.$url_forward_js.'\';</script>';
		include template('common/footer_ajax');
		dexit();
	}

	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}

	if(isset($_GET['ajaxdata'])) {
		if($_GET['ajaxdata'] === 'json') {
			helper_output::json(array('message' => $show_message, 'data' => $values));
		} else if($_GET['ajaxdata'] === 'html') {
			helper_output::html($show_message);
		}
	}

	if($_G['connectguest']) {
		$param['login'] = false;
		$param['alert'] = 'info';
		if (defined('IN_MOBILE')) {
			if ($message == 'postperm_login_nopermission_mobile') {
				$show_message = lang('plugin/qqconnect', 'connect_register_mobile_bind_error');
			}
			$show_message = str_replace(lang('forum/misc', 'connectguest_message_mobile_search'), lang('forum/misc', 'connectguest_message_mobile_replace'), $show_message);
		} else {
			$show_message = str_replace(lang('forum/misc', 'connectguest_message_search'), lang('forum/misc', 'connectguest_message_replace'), $show_message);
		}
		if ($message == 'group_nopermission') {
			$show_message = lang('plugin/qqconnect', 'connectguest_message_complete_or_bind');
		}
	}
	if($param['msgtype'] == 2 && $param['login']) {
		dheader('location: member.php?mod=logging&action=login&handlekey='.$handlekey.'&infloat=yes&inajax=yes&guestmessage=yes');
	}

	$show_jsmessage = str_replace("'", "\\'", $param['striptags'] ? strip_tags($show_message) : $show_message);

	if((!$param['showmsg'] || $param['showid']) && !defined('IN_MOBILE') ) {
		$show_message = '';
	}

	$allowreturn = !$param['timeout'] && !$url_forward && !$param['login'] || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	$extra = '';
	if($param['showid']) {
		$extra .= 'if($(\''.$param['showid'].'\')) {$(\''.$param['showid'].'\').innerHTML = \''.$show_jsmessage.'\';}';
	}
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			$v = daddslashes($v);
			if(is_array($v)) {
				$subcomma = '';
				foreach ($v as $subk => $subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward_js.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}
	if($param['closetime'] !== null) {
		$param['closetime'] = $param['closetime'] === true ? $timedefault : $param['closetime'];
	}
	if($param['locationtime'] !== null) {
		$param['locationtime'] = $param['locationtime'] === true ? $timedefault : $param['locationtime'];
	}
	if($handlekey) {
		if($param['showdialog']) {
			$modes = array('alert_error' => 'alert', 'alert_right' => 'right', 'alert_info' => 'notice');
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \''.$modes[$alerttype].'\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward_js.'\'; }' : 'null').', 0, null, null, null, null, '.($param['closetime'] ? $param['closetime'] : 'null').', '.($param['locationtime'] ? $param['locationtime'] : 'null').');';
			$param['closetime'] = null;
			$st = '';
			if($param['showmsg']) {
				$show_message = '';
			}
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	} else {
		$st = $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward_js.'\';", '.($param['locationtime'] * 1000).');' : '';
	}
	if(!$extra && $param['timeout'] && !defined('IN_MOBILE')) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward_js.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.$st.'</script>' : '';
	$show_message .= $param['extrajs'] ? $param['extrajs'] : '';
	include template('common/showmessage');

	dexit();
}

?>