<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_login.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if($this->core->var['inajax']) {
	ajaxshowheader();
	ajaxshowfooter();
}

if($this->cpaccess == -3) {
	html_login_header(false);
} else {
	html_login_header();
}

if($this->cpaccess == -5) {
	echo  '<p class="logintips">'.lang('admincp_login', 'login_cp_guest').'</p>';

}elseif($this->cpaccess == -2 || $this->cpaccess == -3) {
	echo  '<p class="logintips">'.lang('admincp_login', 'login_cp_noaccess').'</p>';

}elseif($this->cpaccess == -1) {
	$ltime = $this->sessionlife - (TIMESTAMP - $this->adminsession['dateline']);
	echo  '<p class="logintips">'.lang('admincp_login', 'login_cplock', array('ltime' => $ltime)).'</p>';

}elseif($this->cpaccess == -4) {
	$ltime = $this->sessionlife - (TIMESTAMP - $this->adminsession['dateline']);
	echo  '<p class="logintips">'.lang('admincp_login', 'login_user_lock').'</p>';

} else {
	html_login_form();
}

html_login_footer();

function html_login_header($form = true) {
	$charset = CHARSET;
	$title = lang('admincp_login', 'login_title');
	$tips = lang('admincp_login', 'login_tips');
	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=$charset" />
<title>$title</title>
<link rel="stylesheet" href="static/image/admincp/admincp.css?{$_G[style][verhash]}" type="text/css" media="all" />
<meta content="Comsenz Inc." name="Copyright" />
</head>
<body>
EOT;

	if($form) {
		echo <<<EOT
<script language="JavaScript">
	if(self.parent.frames.length != 0) {
		self.parent.location=document.location;
	}
</script>
<table class="logintb">
<tr>
	<td class="login">
		<h1>Discuz! Administrator's Control Panel</h1>
		<p>$tips</p>
	</td>

	<td>
EOT;
	}
}

function html_login_footer($halt = true) {
	$version = getglobal('setting/version');
	echo <<<EOT

	</td>
</tr>
</table>
<table class="logintb">
<tr>
	<td colspan="2" class="footer">
		<div class="copyright">
			<p>Powered by <a href="https://www.discuz.vip/" target="_blank">Discuz!</a> $version </p>
			<p>&copy; 2001-2023 <a href="https://code.dismall.com/" target="_blank">Discuz! Team</a>.</p>
		</div>
	</td>
</tr>
</table>
</body>
</html>

EOT;

	$halt && exit();
}

function html_login_form() {
	global $_G;
	$isguest = !getglobal('uid');
	$lang = lang('admincp_login');
	$loginuser = $isguest ? '<input name="admin_username" tabindex="1" type="text" class="txt" autocomplete="off" />' : getglobal('member/username');
	$sid = getglobal('sid');
	$formhash = getglobal('formhash');
	$_SERVER['QUERY_STRING'] = str_replace('&amp;', '&', dhtmlspecialchars($_SERVER['QUERY_STRING']));
	$extra = ADMINSCRIPT.'?'.(getgpc('action') && getgpc('frames') ? 'frames=yes&' : '').$_SERVER['QUERY_STRING'];
	$forcesecques = '<option value="0">'.($_G['config']['admincp']['forcesecques'] || $_G['group']['forcesecques'] ? $lang['forcesecques'] : $lang['security_question_0']).'</option>';
	echo <<<EOT
		<form method="post" autocomplete="off" name="login" id="loginform" action="$extra">
		<input type="hidden" name="sid" value="$sid">
		<input type="hidden" name="frames" value="yes">
		<input type="hidden" name="formhash" value="$formhash">
		<p class="logintitle">$lang[login_username]: </p>
		<p class="loginform">$loginuser</p>
		<p class="logintitle">$lang[login_password]:</p>
		<p class="loginform"><input name="admin_password" tabindex="1" type="password" class="txt" autocomplete="off" /></p>
		<p class="logintitle">$lang[security_question]:</p>
		<p class="loginform">
			<select id="questionid" name="admin_questionid" tabindex="2">
				$forcesecques
				<option value="1">$lang[security_question_1]</option>
				<option value="2">$lang[security_question_2]</option>
				<option value="3">$lang[security_question_3]</option>
				<option value="4">$lang[security_question_4]</option>
				<option value="5">$lang[security_question_5]</option>
				<option value="6">$lang[security_question_6]</option>
				<option value="7">$lang[security_question_7]</option>
			</select>
		</p>
		<p class="logintitle">$lang[security_answer]:</p>
		<p class="loginform"><input name="admin_answer" tabindex="3" type="text" class="txt" autocomplete="off" /></p>
		<p class="loginnofloat"><input name="submit" value="$lang[submit]"  tabindex="3" type="submit" class="btn" /></p>
		</form>
EOT;
		echo '<script type="text/JavaScript">document.getElementById(\'loginform\').admin_'.($isguest ? 'username' : 'password').'.focus();</script>';
}

?>