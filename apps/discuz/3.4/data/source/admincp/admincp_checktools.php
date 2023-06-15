<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_checktools.php 36334 2017-01-03 01:32:35Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

if($operation == 'filecheck') {

	$homecheck = !empty($_GET['homecheck']);

	if(!$homecheck) {
		$step = max(1, intval($_GET['step']));
		shownav('tools', 'nav_filecheck');
		showsubmenusteps('nav_filecheck', array(
			array('nav_filecheck_confirm', $step == 1),
			array('nav_filecheck_verify', $step == 2),
			array('nav_filecheck_completed', $step == 3)
		));
	} else {
		define('FOOTERDISABLED', true);
		$step = 3;
	}

	if($step == 1) {
		cpmsg(cplang('filecheck_tips_step1'), 'action=checktools&operation=filecheck&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('filecheck_verifying'), "action=checktools&operation=filecheck&step=3", 'loading', '', FALSE);
	} elseif($step == 3) {

		if(!$discuzfiles = @file('./source/admincp/discuzfiles.md5')) {
			if(!$homecheck) {
				cpmsg('filecheck_nofound_md5file', '', 'error');
			} else {
				ajaxshowheader();
				ajaxshowfooter();
			}
		}

		$md5data = array();
		$cachelist = checkcachefiles('data/sysdata/');
		checkfiles('./', '', 0);
		checkfiles('config/', '', 1, 'config_global.php,config_ucenter.php');
		checkfiles('data/', '\.xml', 0);
		checkfiles('data/', '\.htm', 0);
		checkfiles('data/log/', '\.htm', 0);
		checkfiles('data/plugindata/', '\.htm', 0);
		checkfiles('data/download/', '\.htm', 0);
		checkfiles('data/addonmd5/', '\.htm', 0);
		checkfiles('data/avatar/', '\.htm', 0);
		checkfiles('data/cache/', '\.htm', 0);
		checkfiles('data/ipdata/', '\.htm|\.dat', 0);
		checkfiles('data/template/', '\.htm', 0);
		checkfiles('data/threadcache/', '\.htm', 0);
		checkfiles('template/', '');
		checkfiles('api/', '');
		checkfiles('m/', '\.php');
		checkfiles('source/', '', 1, 'discuzfiles.md5,plugin');
		checkfiles('static/', '');
		checkfiles('archiver/', '');
		checkfiles('uc_client/', '\.php|\.htm', 0);
		checkfiles('uc_client/data/', '\.htm');
		checkfiles('uc_client/control/', '\.php|\.htm');
		checkfiles('uc_client/model/', '\.php|\.htm');
		checkfiles('uc_client/lib/', '\.php|\.htm');
		checkfiles('uc_server/', '\.php|\.htm|\.txt|\.xml', 0);
		checkfiles('uc_server/data/', '\.htm');
		checkfiles('uc_server/api/', '\.php|\.htm');
		checkfiles('uc_server/control/', '\.php|\.htm|\.md5');
		checkfiles('uc_server/model/', '\.php|\.htm');
		checkfiles('uc_server/lib/', '\.php|\.htm');
		checkfiles('uc_server/plugin/', '\.php|\.htm|\.xml');
		checkfiles('uc_server/upgrade/', '\.php');
		checkfiles('uc_server/images/', '\..+?');
		checkfiles('uc_server/js/', '\.js|\.htm');
		checkfiles('uc_server/release/', '\.php');
		checkfiles('uc_server/view/', '\.php|\.htm');


		C::t('common_cache')->insert(array(
			'cachekey' => 'checktools_filecheck',
			'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
			'dateline' => $_G['timestamp'],
		), false, true);

		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $md5data[$file]) {
				$modifylist[$file] = $md5data[$file];
			}
			$md5datanew[$file] = $md5data[$file];
		}

		$weekbefore = TIMESTAMP - 604800;
		$addlist = @array_merge(@array_diff_assoc($md5data, $md5datanew), $cachelist[2]);
		$dellist = @array_diff_assoc($md5datanew, $md5data);
		$modifylist = @array_merge(@array_diff_assoc($modifylist, $dellist), $cachelist[1]);
		$showlist = @array_merge($md5data, $md5datanew, $cachelist[0]);
		$doubt = 0;
		$dirlist = $dirlog = array();
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			if(@array_key_exists($file, $modifylist)) {
				$fileststus = 'modify';
			} elseif(@array_key_exists($file, $dellist)) {
				$fileststus = 'del';
			} elseif(@array_key_exists($file, $addlist)) {
				$fileststus = 'add';
			} else {
				$filemtime = @filemtime($file);
				if($filemtime > $weekbefore) {
					$fileststus = 'doubt';
					$doubt++;
				} else {
					$fileststus = '';
				}
			}
			if(file_exists($file)) {
				$filemtime = @filemtime($file);
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array(number_format(filesize($file)).' Bytes', dgmdate($filemtime));
			} else {
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array('', '');
			}
		}

		$modifiedfiles = count($modifylist);
		$deletedfiles = count($dellist);
		$unknownfiles = count($addlist);
		$doubt = intval($doubt);

		C::t('common_cache')->insert(array(
			'cachekey' => 'checktools_filecheck_result',
			'cachevalue' => serialize(array($modifiedfiles, $deletedfiles, $unknownfiles, $doubt)),
			'dateline' => $_G['timestamp'],
		), false, true);

		if($homecheck) {
			ajaxshowheader();
			echo "<em class=\"edited\">$lang[filecheck_modify]: $modifiedfiles</em> &nbsp; ".
				"<em class=\"del\">$lang[filecheck_delete]: $deletedfiles</em> &nbsp; ".
				"<em class=\"unknown\">$lang[filecheck_unknown]: $unknownfiles</em> &nbsp; ".
				"<em class=\"unknown\">$lang[filecheck_doubt]: $doubt</em>  &nbsp; ".
				$lang['filecheck_last_homecheck'].': '.dgmdate(TIMESTAMP, 'u').' <a href="'.ADMINSCRIPT.'?action=checktools&operation=filecheck&step=3">['.$lang['filecheck_view_list'].']</a>';
			ajaxshowfooter();
		}

		$result = $resultjs = '';
		$dirnum = 0;
		foreach($dirlist as $status => $filelist) {
			$dirnum++;
			$class = $status == 'modify' ? 'edited' : ($status == 'del' ? 'del' : 'unknown');
			$result .= '<tbody id="status_'.$status.'" style="display:'.($status != 'modify' ? 'none' : '').'">';
			foreach($filelist as $dir => $files) {
				$result .= '<tr><td colspan="4"><div class="ofolder">'.$dir.'</div><div class="margintop marginbot">';
				foreach($files as $filename => $file) {
					$result .= '<tr><td><em class="files bold">'.$filename.'</em></td><td style="text-align: right">'.$file[0].'&nbsp;&nbsp;</td><td>'.$file[1].'</td><td><em class="'.$class.'">&nbsp;</em></td></tr>';
				}
			}
			$result .= '</tbody>';
			$resultjs .= '$(\'status_'.$status.'\').style.display=\'none\';';
		}

		$result .= '<script>function showresult(o) {'.$resultjs.'$(\'status_\' + o).style.display=\'\';}</script>';
		showtips('filecheck_tips');
		showtableheader('filecheck_completed');
		showtablerow('', 'colspan="4"', "<div class=\"margintop marginbot\">".
			"<em class=\"edited\">$lang[filecheck_modify]: $modifiedfiles</em> ".($modifiedfiles > 0 ? "<a href=\"###\" onclick=\"showresult('modify')\">[$lang[view]]</a> " : '').
			" &nbsp; <em class=\"del\">$lang[filecheck_delete]: $deletedfiles</em> ".($deletedfiles > 0 ? "<a href=\"###\" onclick=\"showresult('del')\">[$lang[view]]</a> " : '').
			" &nbsp; <em class=\"unknown\">$lang[filecheck_unknown]: $unknownfiles</em> ".($unknownfiles > 0 ? "<a href=\"###\" onclick=\"showresult('add')\">[$lang[view]]</a> " : '').
			($doubt > 0 ? "&nbsp;&nbsp;&nbsp;&nbsp;<em class=\"unknown\">$lang[filecheck_doubt]: $doubt</em> <a href=\"###\" onclick=\"showresult('doubt')\">[$lang[view]]</a> " : '').
			"</div>");
		showsubtitle(array('filename', '', 'lastmodified', ''));
		echo $result;
		showtablefooter();

	}

} elseif($operation == 'hookcheck') {

	$step = max(1, intval($_GET['step']));
	shownav('tools', 'nav_hookcheck');
	showsubmenusteps('nav_hookcheck', array(
		array('nav_hookcheck_confirm', $step == 1),
		array('nav_hookcheck_verify', $step == 2),
		array('nav_hookcheck_completed', $step == 3)
	));
	showtips('hookcheck_tips');
	if($step == 1) {
		$styleselect = "<br><br><select name=\"styleid\">";
		foreach(C::t('common_style')->fetch_all_data() as $style) {
			$styleselect .= "<option value=\"$style[styleid]\" ".
				($style['styleid'] == $_G['setting']['styleid'] ? 'selected="selected"' : NULL).
				">$style[name]</option>\n";
		}
		$styleselect .= '</select>';
		cpmsg(cplang('hookcheck_tips_step1', array('template' => $styleselect)), 'action=checktools&operation=hookcheck&step=2', 'form', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('hookcheck_verifying'), "action=checktools&operation=hookcheck&step=3&styleid=$_POST[styleid]", 'loading', '', FALSE);
	} elseif($step == 3) {
		if(!$discuzfiles = @file('./source/admincp/discuzhook.dat')) {
			cpmsg('filecheck_nofound_md5file', '', 'error');
		}

		$discuzhookdata = $hookdata = array();
		$discuzhookdata_hook = array();

		$styleid = intval($_GET['styleid']);
		if(!$styleid) {
			$styleid = $_G['setting']['styleid'];
		}
		$style = C::t('common_style')->fetch_by_styleid($styleid);
		checkhook(substr($style['directory'], 2).'/', '\.htm|\.php', 1);

		foreach($discuzfiles as $line) {
			list($file, $hook) = explode(' *', trim($line));
			if($hook) {
				$discuzhookdata[$file][$hook][] = $hook;
				$discuzhookdata_hook[$file][] = $hook;
			}
		}

		$diffhooklist = $difffilelist = array();
		$diffnum = 0;
		foreach($discuzhookdata as $file => $hook) {
			$dir = dirname($file);
                        $filen = str_replace('template/default/', substr($style['directory'], 2).'/', $file);
			if(isset($hookdata[$filen])) {
				foreach($hook as $k => $hookarr) {
					if(($diff = count($hookarr) - count($hookdata[$filen][$k])) > 0) {
						for($i = 0; $i < $diff; $i++) {
							$diffhooklist[$file][] = $k;
						}
					}
				}
				if(!empty($diffhooklist[$file])) {
					$difffilelist[$dir][] = $file;
					$diffnum++;
				}
			}
		}

		foreach($difffilelist as $dir => $files) {
                        $dir = str_replace('template/default/', substr($style['directory'], 2).'/', $dir);
			$result .= '<tbody><tr><td class="td30"><a href="javascript:;" onclick="toggle_group(\'dir_'.$dir.'\')" id="a_dir_'.$dir.'">[-]</a></td><td colspan="3"><div class="ofolder">'.$dir.'</div></td></tr></tbody>';
			$result .= '<tbody id="dir_'.$dir.'">';
			foreach($files as $file) {
				$result .= '<tr><td></td><td><em class="files bold">'.basename($file).'</em></td><td>';
				foreach($discuzhookdata_hook[$file] as $hook) {
					$result .= '<p>'.dhtmlspecialchars($hook).'</p>';
				}
				$result .= '</td><td>';
				foreach($diffhooklist[$file] as $hook) {
					$result .= '<p>'.dhtmlspecialchars($hook).'</p>';
				}
				$result .= '</td></tr>';
			}
			$result .= '</tbody>';
		}
		if($diffnum > 20) {
			$result .= '<script type="text/javascript">hide_all_hook(\'dir_\', \'tbody\');</script>';
		}
		if($diffnum) {
			showformheader('forums');
			showtableheader('hookcheck_completed');
			showtablerow('', 'colspan="4"', "<div class=\"margintop marginbot\">".
				'<a href="javascript:;" onclick="show_all_hook(\'dir_\', \'tbody\')">'.$lang[show_all].'</a> | <a href="javascript:;" onclick="hide_all_hook(\'dir_\', \'tbody\')">'.$lang[hide_all].'</a>'.
				" &nbsp; <em class=\"del\">$lang[hookcheck_delete]: $diffnum</em> ".
				"</div>");
			showsubtitle(array('', 'filename', 'hookcheck_discuzhook', 'hookcheck_delhook'));
			echo $result;
			showtablefooter();
			showformfooter();
		} else {
			cpmsg('hookcheck_nodelhook', '', 'succeed', '', FALSE);
		}
	}

} elseif($operation == 'replacekey') {

	$step = max(1, intval($_GET['step']));
	shownav('tools', 'nav_replacekey');
	showtips('replacekey_tips');
	showsubmenusteps('nav_replacekey', array(
		array('nav_replacekey_confirm', $step == 1),
		array('nav_replacekey_verify', $step == 2),
		array('nav_replacekey_completed', $step == 3)
	));
	if($step == 1) {
		cpmsg(cplang('replacekey_tips_step1'), 'action=checktools&operation=replacekey&step=2', 'form', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('replacekey_tips_step2'), "action=checktools&operation=replacekey&step=3", 'loading', '', FALSE);
	} elseif($step == 3) {
		if(!is_writeable('./config/config_global.php')) {
			cpmsg('replacekey_must_write_config', '', 'error');
		}

		$oldauthkey = $_G['config']['security']['authkey'];
		$newauthkey = generate_key(64);

		$configfile = trim(file_get_contents(DISCUZ_ROOT.'./config/config_global.php'));
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		$configfile = str_replace($oldauthkey, $newauthkey, $configfile);

		if(file_put_contents(DISCUZ_ROOT.'./config/config_global.php', trim($configfile), LOCK_EX) === false) {
			cpmsg('replacekey_must_write_config', '', 'error');
		}

		$ecdata = authcode($_G['setting']['ec_contract'], 'DECODE', $oldauthkey);
		$ecdata = authcode($ecdata, 'ENCODE', $newauthkey);
		C::t('common_setting')->update('ec_contract', $ecdata);

		$ftpdata = $_G['setting']['ftp'];
		$ftppasswd = authcode($ftpdata['password'], 'DECODE', md5($oldauthkey));
		$ftpdata['password'] = authcode($ftppasswd, 'ENCODE', md5($newauthkey));
		C::t('common_setting')->update('ftp', $ftpdata);

		updatecache('setting');

		cpmsg('replacekey_succeed', '', 'succeed', '', FALSE);
	}

} elseif($operation == 'ftpcheck') {

	$alertmsg = '';
	$testcontent = md5('Discuz!' + $_G['config']['security']['authkey']);
	$testfile = 'test/discuztest.txt';
	$attach_dir = $_G['setting']['attachdir'];
	@mkdir($attach_dir.'test', 0777);
	if($fp = @fopen($attach_dir.'/'.$testfile, 'w')) {
		fwrite($fp, $testcontent);
		fclose($fp);
	}

	if(!$alertmsg) {
		$settingnew = $_GET['settingnew'];
		$settings['ftp'] = C::t('common_setting')->fetch('ftp', true);
		$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
		$pwlen = strlen($settingnew['ftp']['password']);
		if($settingnew['ftp']['password'][0] == $settings['ftp']['password'][0] && $settingnew['ftp']['password'][$pwlen - 1] == $settings['ftp']['password'][strlen($settings['ftp']['password']) - 1] && substr($settingnew['ftp']['password'], 1, $pwlen - 2) == '********') {
			$settingnew['ftp']['password'] = $settings['ftp']['password'];
		}
		$settingnew['ftp']['password'] = authcode($settingnew['ftp']['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
		$settingnew['ftp']['attachurl'] .= substr($settingnew['ftp']['attachurl'], -1, 1) != '/' ? '/' : '';
		$_G['setting']['ftp'] = $settingnew['ftp'];

		ftpcmd('upload', $testfile);
		$ftp = ftpcmd('object');
		if(ftpcmd('error')) {
			$alertmsg = cplang('setting_attach_remote_'.ftpcmd('error'));
		}
		if(!$alertmsg) {
			$str = getremotefile($_G['setting']['ftp']['attachurl'].$testfile);
			if($str !== $testcontent) {
				$alertmsg = cplang('setting_attach_remote_geterr');
			}
		}
		if(!$alertmsg) {
			ftpcmd('delete', $testfile);
			ftpcmd('delete', 'test/index.htm');
			$ftp->ftp_rmdir('test');
			$str = getremotefile($_G['setting']['ftp']['attachurl'].$testfile);
			if($str === $testcontent) {
				$alertmsg = cplang('setting_attach_remote_delerr');
			}
			@unlink($attach_dir.'/'.$testfile);
			@rmdir($attach_dir.'test');
		}
	}
	if(!$alertmsg) {
		$alertmsg = cplang('setting_attach_remote_ok');
	}

	echo '<script language="javascript">alert(\''.str_replace('\'', '\\\'', $alertmsg).'\');parent.$(\'cpform\').action=\''.ADMINSCRIPT.'?action=setting&edit=yes\';parent.$(\'cpform\').target=\'_self\'</script>';

} elseif($operation == 'mailcheck') {
	$oldmail = dunserialize($_G['setting']['mail']);
	$settingnew = $_GET['settingnew'];
	$oldsmtp = $settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp'] : $settingnew['mail']['esmtp'];
	$deletesmtp = $settingnew['mail']['mailsend'] != 1 ? ($settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp']['delete'] : $settingnew['mail']['esmtp']['delete']) : array();
	$settingnew['mail']['smtp'] = array();
	foreach($oldsmtp as $id => $value) {
		if((empty($deletesmtp) || !in_array($id, $deletesmtp)) && !empty($value['server']) && !empty($value['port'])) {
			$passwordmask = $oldmail['smtp'][$id]['auth_password'] ? $oldmail['smtp'][$id]['auth_password'][0].'********'.substr($oldmail['smtp'][$id]['auth_password'], -2) : '';
			$value['auth_password'] = $value['auth_password'] == $passwordmask ? $oldmail['smtp'][$id]['auth_password'] : $value['auth_password'];
			$settingnew['mail']['smtp'][] = $value;
		}
	}

	if(!empty($_GET['newsmtp'])) {
		foreach($_GET['newsmtp']['server'] as $id => $smtp) {
			if(!empty($smtp) && !empty($_GET['newsmtp']['port'][$id])) {
				$settingnew['mail']['smtp'][] = array(
						'server' => $smtp,
						'port' => $_GET['newsmtp']['port'][$id] ? intval($_GET['newsmtp']['port'][$id]) : 25,
						'auth' => $_GET['newsmtp']['auth'][$id] ? 1 : 0,
						'from' => $_GET['newsmtp']['from'][$id],
						'auth_username' => $_GET['newsmtp']['auth_username'][$id],
						'auth_password' => $_GET['newsmtp']['auth_password'][$id]
					);
			}
		}
	}

	$_G['setting']['mail'] = serialize($settingnew['mail']);
	$test_to = $_GET['test_to'];
	$test_from = $_GET['test_from'];
	$date = date('Y-m-d H:i:s');
	$alertmsg = '';

	$title = $lang['setting_mailcheck_title_'.$settingnew['mail']['mailsend']];
	$message = $lang['setting_mailcheck_message_'.$settingnew['mail']['mailsend']].' '.$test_from.$lang['setting_mailcheck_date'].' '.$date;

	$_G['setting']['bbname'] = $lang['setting_mail_check_method_1'];
	include libfile('function/mail');
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
	$_G['setting']['bbname'] = $lang['setting_mail_check_method_2'];
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);

	if($succeed) {
		$alertmsg = $lang['setting_mail_check_success_1']."$title @ $date".$lang['setting_mail_check_success_2'];
	} else {
		$alertmsg = $lang['setting_mail_check_error'].$alertmsg;
	}

	echo '<script language="javascript">alert(\''.str_replace(array('\'', "\n", "\r"), array('\\\'', '\n', ''), $alertmsg).'\');parent.$(\'cpform\').action=\''.ADMINSCRIPT.'?action=setting&edit=yes\';parent.$(\'cpform\').target=\'_self\';parent.$(\'cpform\').operation.value=\'mail\';</script>';

} elseif($operation == 'imagepreview') {

	$settingnew = $_GET['settingnew'];
	if(!empty($_GET['previewthumb'])) {
		$_G['setting']['imagelib'] = $settingnew['imagelib'];
		$_G['setting']['thumbwidth'] = $settingnew['thumbwidth'];
		$_G['setting']['thumbheight'] = $settingnew['thumbheight'];
		$_G['setting']['thumbquality'] = $settingnew['thumbquality'];

		require_once libfile('class/image');
		@unlink(DISCUZ_ROOT.$_G['setting']['attachdir'].'./temp/watermark_temp1.jpg');
		@unlink(DISCUZ_ROOT.$_G['setting']['attachdir'].'./temp/watermark_temp2.jpg');
		$image = new image;
		$r = 0;
		if(!($r = $image->Thumb(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp1.jpg', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], 1))) {
			$r = $image->error();
		}
		$sizetarget1 = $image->imginfo['size'];
		$image->Thumb(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp2.jpg', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], 2);
		$sizetarget2 = $image->imginfo['size'];
		if($r > 0) {
			showsubmenu('imagepreview_thumb');
			$sizesource = filesize(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg');
			echo '<img src="data/attachment/temp/watermark_temp1.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget1).' Bytes ('.
				(sprintf("%2.1f", $sizetarget1 / $sizesource * 100)).'%)<br /><br />';
			echo '<img src="data/attachment/temp/watermark_temp2.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget2).' Bytes ('.
				(sprintf("%2.1f", $sizetarget2 / $sizesource * 100)).'%)';
		} else {
			cpmsg('imagepreview_errorcode_'.$r, '', 'error');
		}
	} else {
		$type = $_GET['type'];
		if(!$_G['setting']['watermarkstatus'][$type]) {
			cpmsg('watermarkpreview_error', '', 'error');
		}
		require_once libfile('class/image');
		@unlink(DISCUZ_ROOT.'./data/attachment/temp/watermark_temp3.jpg');
		$image = new image;
		if(!($r = $image->Watermark(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp3.jpg', $type))) {
			$r = $image->error();
		}
		if($r > 0) {
			showsubmenu('imagepreview_watermark');
			$sizesource = filesize('static/image/admincp/watermarkpreview.jpg');
			$sizetarget = $image->imginfo['size'];
			echo '<img src="data/attachment/temp/watermark_temp3.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget).' Bytes ('.
				(sprintf("%2.1f", $sizetarget / $sizesource * 100)).'%)';
		} else {
			cpmsg('imagepreview_errorcode_'.$r, '', 'error');
		}
	}

} elseif($operation == 'rewrite') {

	$rule = array();
	$rewritedata = rewritedata();
	$rule['{apache1}'] = $rule['{apache2}'] = $rule['{iis}'] = $rule['{iis7}'] = $rule['{nginx}'] = $rule['{lighttpd}'] = $rule['{caddy}'] = '';
	foreach($rewritedata['rulesearch'] as $k => $v) {
		if(!in_array($k, $_G['setting']['rewritestatus'])) {
			continue;
		}
		$v = !$_G['setting']['rewriterule'][$k] ? $v : $_G['setting']['rewriterule'][$k];
		$pvmaxv = count($rewritedata['rulevars'][$k]) + 2;
		$vkeys = array_keys($rewritedata['rulevars'][$k]);
		$rewritedata['rulereplace'][$k] = pvsort($vkeys, $v, $rewritedata['rulereplace'][$k]);
		$v = str_replace($vkeys, $rewritedata['rulevars'][$k], addcslashes($v, '?*+^$.[]()|'));
		$rulepath = $k != 'forum_archiver' ? '' : 'archiver/';
		$rule['{apache1}'] .= "\t".'RewriteCond %{QUERY_STRING} ^(.*)$'."\n\t".'RewriteRule ^(.*)/'.$rulepath.$v.'$ $1/'.$rulepath.pvadd($rewritedata['rulereplace'][$k])."&%1\n";
		$rule['{apache2}'] .= 'RewriteCond %{QUERY_STRING} ^(.*)$'."\n".'RewriteRule ^'.$rulepath.$v.'$ '.$rulepath.$rewritedata['rulereplace'][$k]."&%1\n";
		$rule['{iis}'] .= 'RewriteRule ^(.*)/'.$rulepath.$v.'(\?(.*))*$ $1/'.$rulepath.addcslashes(pvadd($rewritedata['rulereplace'][$k]).'&$'.($pvmaxv + 1), '.?')."\n";
		$rule['{iis7}'] .= "\t\t".'&lt;rule name="'.$k.'"&gt;'."\n\t\t\t".'&lt;match url="^(.*/)*'.$rulepath.str_replace('\.', '.', $v).'\?*(.*)$" /&gt;'."\n\t\t\t".'&lt;action type="Rewrite" url="{R:1}/'.str_replace(array('&', 'page\%3D'), array('&amp;amp;', 'page%3D'), $rulepath.addcslashes(pvadd($rewritedata['rulereplace'][$k], array('{R:', '}')).'&{R:'.$pvmaxv.'}', '?')).'" /&gt;'."\n\t\t".'&lt;/rule&gt;'."\n";
		$rule['{nginx}'] .= 'rewrite ^([^\.]*)/'.$rulepath.$v.'$ $1/'.$rulepath.stripslashes(pvadd($rewritedata['rulereplace'][$k]))." last;\n";
		$rule['{lighttpd}'] .= '"(.*)/'.$rulepath.$v.'\?*(.*)$" =&gt; "$1/'.$rulepath.pvadd($rewritedata['rulereplace'][$k]).'&$'.$pvmaxv.'",'."\n";
		$rule['{caddy}'] .= '@'.$k.' path_regexp '.$k.' ^(.*)/'.$rulepath.$v."$\n".'rewrite @'.$k.' {re.'.$k.'.1}/'.$rulepath.pvadd($rewritedata['rulereplace'][$k], array('{re.'.$k.'.', '}')).'&{query}'."\n";
	}
	$rule['{nginx}'] .= "if (!-e \$request_filename) {\n\treturn 404;\n}";
	$rule['{siteroot}'] = !empty($_G['siteroot']) ? $_G['siteroot'] : '/';
	echo str_replace(array_keys($rule), $rule, cplang('rewrite_message'));

} elseif($operation == 'robots') {

	if($do == 'output') {
		$robots = implode('', file(DISCUZ_ROOT.'./source/admincp/robots.txt'));
		$robots = str_replace('{path}', $_G['siteroot'], $robots);
		$robots = str_replace('{ver}', $_G['setting']['version'], $robots);
		ob_end_clean();
		dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		dheader('Cache-Control: no-cache, must-revalidate');
		dheader('Pragma: no-cache');
		dheader('Content-Encoding: none');
		dheader('Content-Length: '.strlen($robots));
		dheader('Content-Disposition: attachment; filename=robots.txt');
		dheader('Content-Type: text/plain');
		echo $robots;
		define('FOOTERDISABLED' , 1);
		exit();
	}
	cpmsg('robots_output', 'action=checktools&operation=robots&do=output&frame=no', 'download', array('siteurl' => dhtmlspecialchars($_G['scheme'].'://'.$_SERVER['HTTP_HOST'].'/')));

}

function pvsort($key, $v, $s) {
	$r = '/';
	$p = '';
	foreach($key as $k) {
		$r .= $p.preg_quote($k);
		$p = '|';
	}
	$r .= '/';
	preg_match_all($r, $v, $a);
	$a = $a[0];
	$a = array_flip($a);
	foreach($a as $key => $value) {
		$s = str_replace($key, '$'.($value + 1), $s);
	}
	return $s;
}

function pvadd($s, $t = array()) {
	$s = str_replace(array('$3', '$2', '$1'), array('~4', '~3', '~2'), $s);
	if(!$t) {
		return str_replace(array('~4', '~3', '~2'), array('$4', '$3', '$2'), $s);
	} else {
		return str_replace(array('~4', '~3', '~2'), array($t[0].'4'.$t[1], $t[0].'3'.$t[1], $t[0].'2'.$t[1]), $s);
	}

}

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (($ext && preg_match($exts, $entry) || !$ext) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				if(is_dir($file)) {
					$md5data[$file] = md5($file);
				} else {
					$md5data[$file] = md5_file($file);
				}
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $_G;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen($file, "rb");
			$cachedata = fread($fp, filesize($file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Identify: (\w+)\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$_G['config']['security']['authkey']) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = '';
			}
		}
	}

	return array($showlist, $modifylist, $addlist);
}

function checkmailerror($type, $error) {
	global $alertmsg;
	$alertmsg .= !$alertmsg ? $error : '';
}

function getremotefile($file) {
	global $_G;
	@set_time_limit(0);
	$file = $file.'?'.TIMESTAMP.rand(1000, 9999);
	$str = @implode('', @file($file));
	if(!$str) {
		$str = dfsockopen($file);
	}
	return $str;
}

function checkhook($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $hooks, $hookdata;
	$dir = opendir($currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (preg_match($exts, $entry) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkhook($file.'/', $ext, $sub, $skip);
			} else {
				$data = file_get_contents($file);
				$hooks = array();
				preg_replace_callback("/\{hook\/(\w+?)(\s+(.+?))?\}/i", 'checkhook_callback_findhook_13', $data);
				if($hooks) {
					foreach($hooks as $v) {
                                        	if(preg_match('/\.php$/', $file)) {
                                                	$file = substr($file, 0, -4).'.htm';
                                                }
						$hookdata[$file][$v][] = $v;
					}
				}
			}
		}
	}
}

function checkhook_callback_findhook_13($matches) {
	return findhook($matches[1], $matches[3]);
}

function findhook($hookid, $key) {
	global $hooks;
	if($key) {
		$key = ' '.$key;
	}
	$hooks[] = '<!--{hook/'.$hookid.$key.'}-->';
}

function generate_key($length = 32) {
	$random = random($length);
	$info = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT'].$_SERVER['HTTP_USER_AGENT'].time());
	$return = '';
	for($i=0; $i<$length; $i++) {
		$return .= $random[$i].$info[$i];
	}
	return $return;
}