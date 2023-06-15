<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_secqaa.php 33682 2013-08-01 06:37:41Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$idhash = isset($_GET['idhash']) && preg_match('/^\w+$/', $_GET['idhash']) ? $_GET['idhash'] : '';

if($_GET['action'] == 'update') {

	$refererhost = parse_url($_SERVER['HTTP_REFERER']);
	$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

	if($refererhost['host'] != $_SERVER['HTTP_HOST']) {
		exit('Access Denied');
	}

	$message = '';
	$showid = 'secqaa_'.$idhash;
	if($_G['setting']['secqaa']) {
		$question = make_secqaa();
	}

	$message = preg_replace("/\r|\n/", '', $question);
	$message = str_replace("'", "\'", $message);
	$seclang = lang('forum/misc');
	header("Content-Type: application/javascript");
echo <<<EOF
if($('$showid')) {
	var sectpl = seccheck_tpl['$idhash'] != '' ? seccheck_tpl['$idhash'].replace(/<hash>/g, 'code$idhash') : '';
	var sectplcode = sectpl != '' ? sectpl.split('<sec>') : Array('<br />',': ','<br />','');
	var string = '<input name="secqaahash" type="hidden" value="$idhash" />' + sectplcode[0] + '$seclang[secqaa]' + sectplcode[1] + '<input name="secanswer" id="secqaaverify_$idhash" type="text" autocomplete="off" style="{$imemode}width:100px" class="txt px vm" onblur="checksec(\'qaa\', \'$idhash\')" />' +
		' <a href="javascript:;" onclick="updatesecqaa(\'$idhash\');doane(event);" class="xi2">$seclang[seccode_update]</a>' +
		'<span id="checksecqaaverify_$idhash"><img src="' + STATICURL + 'image/common/none.gif" width="16" height="16" class="vm" /></span>' +
		sectplcode[2] + '$message' + sectplcode[3];
	evalscript(string);
	$('$showid').innerHTML = string;
}
EOF;

} elseif($_GET['action'] == 'check') {

	include template('common/header_ajax');
	echo check_secqaa($_GET['secverify'], $idhash) ? 'succeed' : 'invalid';
	include template('common/footer_ajax');

}

?>