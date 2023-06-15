<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_index.php 36306 2016-12-16 08:12:49Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sensitivedirs = array('./', './uc_server/', './ucenter/');

foreach ($sensitivedirs as $sdir) {
	if(@file_exists(DISCUZ_ROOT.$sdir.'install/index.php') && !DISCUZ_DEBUG) {
		@unlink(DISCUZ_ROOT.$sdir.'install/index.php');
		if(@file_exists(DISCUZ_ROOT.$sdir.'install/index.php')) {
			dexit('Please delete '.$sdir.'install/index.php via FTP!');
		}
	}
}

@include_once DISCUZ_ROOT.'./source/discuz_version.php';
require_once libfile('function/attachment');
require_once libfile('function/discuzcode');
$isfounder = isfounder();

$siteuniqueid = C::t('common_setting')->fetch('siteuniqueid');
if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = 'DX'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
	C::t('common_setting')->update('siteuniqueid', $siteuniqueid);
	require_once libfile('function/cache');
	updatecache('setting');
}

if(!empty($_GET['closesitereleasetips'])) {
	C::t('common_setting')->update('sitereleasetips', 0);
	$sitereleasetips = 0;
	require_once libfile('function/cache');
	updatecache('setting');
} else {
	$sitereleasetips = C::t('common_setting')->fetch('sitereleasetips');
}

$siterelease = C::t('common_setting')->fetch('siterelease');
$releasehash = substr(hash('sha512', $_G['config']['security']['authkey'].DISCUZ_VERSION.DISCUZ_RELEASE.$siteuniqueid), 0, 32);
if(empty($siterelease) || strcmp($siterelease, $releasehash) !== 0) {
	C::t('common_setting')->update('siteversion', DISCUZ_VERSION);
	C::t('common_setting')->update('siterelease', $releasehash);
	C::t('common_setting')->update('sitereleasetips', 1);
	$sitereleasetips = 1;
	require_once libfile('function/cache');
	updatecache('setting');
}

if(submitcheck('notesubmit', 1)) {
	if(!empty($_GET['noteid']) && is_numeric($_GET['noteid'])) {
		C::t('common_adminnote')->delete($_GET['noteid'], ($isfounder ? '' : $_G['username']));
	}
	if(!empty($_GET['newmessage'])) {
		$newaccess = 0;
		$_GET['newexpiration'] = TIMESTAMP + (intval($_GET['newexpiration']) > 0 ? intval($_GET['newexpiration']) : 30) * 86400;
		$_GET['newmessage'] = nl2br(dhtmlspecialchars($_GET['newmessage']));
		$data = array(
			'admin' => $_G['username'],
			'access' => 0,
			'adminid' => $_G['adminid'],
			'dateline' => $_G['timestamp'],
			'expiration' => $_GET['newexpiration'],
			'message' => $_GET['newmessage'],
		);
		C::t('common_adminnote')->insert($data);
	}
}

$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
$serversoft = $_SERVER['SERVER_SOFTWARE'];
$dbversion = helper_dbtool::dbversion();

if(@ini_get('file_uploads')) {
	require_once libfile('function/upload');
	$fileupload = getmaxupload();
} else {
	$fileupload = '<font color="red">'.$lang['no'].'</font>';
}


$dbsize = helper_dbtool::dbsize();
$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

if(isset($_GET['attachsize'])) {
	$attachsize = C::t('forum_attachment_n')->get_total_filesize();
	$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];
} else {
	$attachsize = '<a href="'.ADMINSCRIPT.'?action=index&attachsize">[ '.$lang['detail'].' ]</a>';
}

$membersmod = C::t('common_member_validate')->count_by_status(0);
$threadsdel = C::t('forum_thread')->count_by_displayorder(-1);
$groupmod = C::t('forum_forum')->validate_level_num();

$modcount = array();
foreach(C::t('common_moderate')->count_group_idtype_by_status(0) as $value) {
	$modcount[$value['idtype']] = $value['count'];
}

$medalsmod = C::t('forum_medallog')->count_by_type(2);
$threadsmod = $modcount['tid'];
$postsmod = $modcount['pid'];
$blogsmod = $modcount['blogid'];
$doingsmod = $modcount['doid'];
$picturesmod = $modcount['picid'];
$sharesmod = $modcount['sid'];
$commentsmod = $modcount['uid_cid'] + $modcount['blogid_cid'] + $modcount['sid_cid'] + $modcount['picid_cid'];
$articlesmod = $modcount['aid'];
$articlecommentsmod = $modcount['aid_cid'];
$topiccommentsmod = $modcount['topicid_cid'];
$verify = '';
foreach(C::t('common_member_verify_info')->group_by_verifytype_count() as $value) {
	if($value['num']) {
		if($value['verifytype']) {
			$verifyinfo = !empty($_G['setting']['verify'][$value['verifytype']]) ? $_G['setting']['verify'][$value['verifytype']] : array();
			if($verifyinfo['available']) {
				$verify .= '<a href="'.ADMINSCRIPT.'?action=verify&operation=verify&do='.$value['verifytype'].'">'.cplang('home_mod_verify_prefix').$verifyinfo['title'].'</a>(<em class="lightnum">'.$value['num'].'</em>)';
			}
		} else {
			$verify .= '<a href="'.ADMINSCRIPT.'?action=verify&operation=verify&do=0">'.cplang('home_mod_verify_prefix').cplang('members_verify_profile').'</a>(<em class="lightnum">'.$value['num'].'</em>)';
		}
	}
}

cpheader();
shownav();

require_once libfile('function/cloudaddons');
$newversion = (CHARSET == 'utf-8') ? dunserialize($_G['setting']['cloudaddons_newversion']) : json_decode($_G['setting']['cloudaddons_newversion'],true);
if(empty($newversion['newversion']) || !is_array($newversion['newversion']) || abs($_G['timestamp'] - $newversion['updatetime']) > 86400 || (isset($_GET['checknewversion']) && $_G['formhash'] == $_GET['formhash'])) {
	$newversion = json_decode(cloudaddons_open('&mod=app&ac=upgrade'), true);
	if(!empty($newversion['newversion'])){
		$newversion['updatetime'] = $_G['timestamp'];
		C::t('common_setting')->update('cloudaddons_newversion', ((CHARSET == 'utf-8') ? $newversion : json_encode($newversion)));
		updatecache('setting');
	}else{
		$newversion = array();
	}
}

showsubmenu('home_welcome', array(), '', array('bbname' => $_G['setting']['bbname']));

$save_master = C::t('common_setting')->fetch_all(array('mastermobile', 'masterqq', 'masteremail'));
$save_mastermobile = $save_master['mastermobile'];
$save_mastermobile = !empty($save_mastermobile) ? authcode($save_mastermobile, 'DECODE', $_G['config']['security']['authkey']) : '';
$save_masterqq = $save_master['masterqq'] ? $save_master['masterqq'] : '';
$save_masteremail = $save_master['masteremail'] ? $save_master['masteremail'] : '';

$securityadvise = '';
if($isfounder) {
	$securityadvise = '';
	$securityadvise .= !$_G['config']['admincp']['founder'] ? $lang['home_security_nofounder'] : '';
	$securityadvise .= !$_G['config']['admincp']['checkip'] ? $lang['home_security_checkip'] : '';
	$securityadvise .= $_G['config']['admincp']['runquery'] ? $lang['home_security_runquery'] : '';
	if(!empty($_GET['securyservice'])) {
		$_GET['new_mastermobile'] = trim($_GET['new_mastermobile']);
		$_GET['new_masterqq'] = trim($_GET['new_masterqq']);
		$_GET['new_masteremail'] = trim($_GET['new_masteremail']);
		if(empty($_GET['new_mastermobile'])) {
			$save_mastermobile = $_GET['new_mastermobile'];
		} elseif(strlen($_GET['new_mastermobile']) == 11 && is_numeric($_GET['new_mastermobile']) && in_array(substr($_GET['new_mastermobile'], 0, 2), array('13', '15', '18'))) {
			$save_mastermobile = $_GET['new_mastermobile'];
			$_GET['new_mastermobile'] = authcode($_GET['new_mastermobile'], 'ENCODE', $_G['config']['security']['authkey']);
		} else {
			$_GET['new_mastermobile'] = $save_master['mastermobile'];
		}
		if(empty($_GET['new_masterqq']) || is_numeric($_GET['new_masterqq'])) {
			$save_masterqq = $_GET['new_masterqq'];
		} else {
			$_GET['new_masterqq'] = $save_masterqq;
		}
		if(empty($_GET['new_masteremail']) || (strlen($_GET['new_masteremail']) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $_GET['new_masteremail']))) {
			$save_masteremail = $_GET['new_masteremail'];
		} else {
			$_GET['new_masteremail'] = $save_masteremail;
		}

		C::t('common_setting')->update_batch(array('mastermobile' => $_GET['new_mastermobile'], 'masterqq' => $_GET['new_masterqq'], 'masteremail' => $_GET['new_masteremail']));
	}

	$view_mastermobile = !empty($save_mastermobile) ? substr($save_mastermobile, 0 , 3).'*****'.substr($save_mastermobile, -3) : '';
}

if($securityadvise) {
	showtableheader('home_security_tips', '', '', 0);
	showtablerow('', 'class="tipsblock"', '<ul>'.$securityadvise.'</ul>');
	showtablefooter();
}

$onlines = '';
$admincp_session = C::t('common_admincp_session')->fetch_all_by_panel(1);
$members = C::t('common_member')->fetch_all(array_keys($admincp_session), false, 0);
foreach($admincp_session as $uid => $online) {
	$onlines .= '<a href="home.php?mod=space&uid='.$online['uid'].'" title="'.dgmdate($online['dateline']).'" target="_blank">'.$members[$uid]['username'].'</a>&nbsp;&nbsp;&nbsp;';
}


showtableheader('', 'nobottom fixpadding');
if($membersmod || $threadsmod || $postsmod || $medalsmod || $blogsmod || $picturesmod || $doingsmod || $sharesmod || $commentsmod || $articlesmod || $articlecommentsmod || $topiccommentsmod || $threadsdel || !empty($verify)) {
	showtablerow('', '', '<h3 class="left margintop">'.cplang('home_mods').': </h3><p class="left difflink">'.
		($membersmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=members">'.cplang('home_mod_members').'</a>(<em class="lightnum">'.$membersmod.'</em>)' : '').
		($threadsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=threads&dateline=all">'.cplang('home_mod_threads').'</a>(<em class="lightnum">'.$threadsmod.'</em>)' : '').
		($postsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=replies&dateline=all">'.cplang('home_mod_posts').'</a>(<em class="lightnum">'.$postsmod.'</em>)' : '').
		($medalsmod ? '<a href="'.ADMINSCRIPT.'?action=medals&operation=mod">'.cplang('home_mod_medals').'</a>(<em class="lightnum">'.$medalsmod.'</em>)' : '').
		($groupmod ? '<a href="'.ADMINSCRIPT.'?action=group&operation=mod">'.cplang('group_mod_wait').'</a>(<em class="lightnum">'.$groupmod.'</em>)' : '').
		($blogsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=blogs&dateline=all">'.cplang('home_mod_blogs').'</a>(<em class="lightnum">'.$blogsmod.'</em>)' : '').
		($picturesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=pictures&dateline=all">'.cplang('home_mod_pictures').'</a>(<em class="lightnum">'.$picturesmod.'</em>)' : '').
		($doingsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=doings&dateline=all">'.cplang('home_mod_doings').'</a>(<em class="lightnum">'.$doingsmod.'</em>)' : '').
		($sharesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=shares&dateline=all">'.cplang('home_mod_shares').'</a>(<em class="lightnum">'.$sharesmod.'</em>)' : '').
		($commentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=comments&dateline=all">'.cplang('home_mod_comments').'</a>(<em class="lightnum">'.$commentsmod.'</em>)' : '').
		($articlesmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=articles&dateline=all">'.cplang('home_mod_articles').'</a>(<em class="lightnum">'.$articlesmod.'</em>)' : '').
		($articlecommentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=articlecomments&dateline=all">'.cplang('home_mod_articlecomments').'</a>(<em class="lightnum">'.$articlecommentsmod.'</em>)' : '').
		($topiccommentsmod ? '<a href="'.ADMINSCRIPT.'?action=moderate&operation=topiccomments&dateline=all">'.cplang('home_mod_topiccomments').'</a>(<em class="lightnum">'.$topiccommentsmod.'</em>)' : '').
		($threadsdel ? '<a href="'.ADMINSCRIPT.'?action=recyclebin">'.cplang('home_del_threads').'</a>(<em class="lightnum">'.$threadsdel.'</em>)' : '').
		$verify.
		'</p><div class="clear"></div>'
	);
}
showtablefooter();

if(isfounder()) {
	$filecheck = C::t('common_cache')->fetch('checktools_filecheck_result');
	if($filecheck) {
		list($modifiedfiles, $deletedfiles, $unknownfiles, $doubt) = unserialize($filecheck['cachevalue']);
		$filecheckresult = "<em class=\"edited\">$lang[filecheck_modify]: $modifiedfiles</em> &nbsp; ".
			"<em class=\"del\">$lang[filecheck_delete]: $deletedfiles</em> &nbsp; ".
			"<em class=\"unknown\">$lang[filecheck_unknown]: $unknownfiles</em> &nbsp; ".
			"<em class=\"unknown\">$lang[filecheck_doubt]: $doubt</em> &nbsp; ".
			$lang['filecheck_last_homecheck'].': '.dgmdate($filecheck['dateline'], 'u').' <a href="'.ADMINSCRIPT.'?action=checktools&operation=filecheck&step=3">['.$lang['filecheck_view_list'].']</a>';
	} else {
		$filecheckresult = '';
	}

	showtableheader($lang['nav_filecheck'].' <a href="javascript:;" onclick="ajaxget(\''.ADMINSCRIPT.'?action=checktools&operation=filecheck&homecheck=yes\', \'filecheck_div\')">['.$lang['filecheck_check_now'].']</a>', 'nobottom fixpadding');
	echo '<tr><td><div id="filecheck_div">'.$filecheckresult.'</div></td></tr>';
	showtablefooter();
	if(TIMESTAMP - $filecheck['dateline'] > 86400 * 7) {
		echo '<script>ajaxget(\''.ADMINSCRIPT.'?action=checktools&operation=filecheck&homecheck=yes\', \'filecheck_div\');</script>';
	}
}

showtableheader('detect_environment', 'fixpadding');
$env_ok = true;
$now_ver_gd = function_exists('gd_info')? gd_info() : false;
$now_ver = array('PHP' => constant('PHP_VERSION'), 'MySQL' => helper_dbtool::dbversion(), 'gethostbyname' => function_exists('gethostbyname'), 'file_get_contents' => function_exists('file_get_contents'), 'xml_parser_create' => function_exists('xml_parser_create'),
'FileSock Function' => (function_exists('fsockopen') || function_exists('pfsockopen') || function_exists('stream_socket_client') || function_exists('curl_init')), 'GD' => ($now_ver_gd ? preg_replace('/[^0-9.]+/', '', $now_ver_gd['GD Version']) : false));
$req_ver = array('PHP' => '5.3', 'MySQL' => '5.0', 'filter_var' => true, 'gethostbyname' => true, 'file_get_contents' => true, 'xml_parser_create' => true, 'FileSock Function' => true, 'GD' => '1.0');
$sug_ver = array('PHP' => '7.1', 'MySQL' => '5.7', 'filter_var' => true, 'gethostbyname' => true, 'file_get_contents' => true, 'xml_parser_create' => true, 'FileSock Function' => true, 'GD' => '2.0');
foreach ($now_ver as $key => $value) {
	if($req_ver[$key] === true) {
		if (!$value) {
			showtablerow('', array('', 'class="td21" style="text-align:right;"'),
				'<em class="unfixed">'.lang("admincp", "req_not_found", array('req' => $key)).'</em>'
			);
			$env_ok = false;
		}
	} else if (version_compare($value, $req_ver[$key], '<')) {
		showtablerow('', array('', 'class="td21" style="text-align:right;"'),
			'<em class="unfixed">'.lang("admincp", "req_ver_too_low", array('req' => $key, 'now_ver' => $value, 'sug_ver' => $sug_ver[$key], 'req_ver' => $req_ver[$key])).'</em>'
		);
		$env_ok = false;
	}
}
if ($env_ok) {
	showtablerow('', array('', 'class="td21" style="text-align:right;"'),
		'<em class="fixed">'.lang("admincp", "req_ok", array('version' => constant("DISCUZ_VERSION").' R'.constant("DISCUZ_RELEASE").' '.strtoupper(constant("CHARSET")))).'</em>'
	);
}
showtablefooter();

if($sitereleasetips) {
	showtableheader('version_tips', 'fixpadding');
	showtablerow('', array('', 'class="td21" style="text-align:right;"'),
		'<em class="unknown">'.lang("admincp", "version_tips_msg", array('ADMINSCRIPT' => ADMINSCRIPT, 'version' => constant("DISCUZ_VERSION").' R'.constant("DISCUZ_RELEASE"))).'</em>'
	);
	showtablefooter();
}

showtableheader('home_onlines', 'nobottom fixpadding');
echo '<tr><td>'.$onlines.'</td></tr>';
showtablefooter();

showformheader('index');
showtableheader('home_notes', 'fixpadding"', '', '3');

foreach(C::t('common_adminnote')->fetch_all_by_access(0) as $note) {
	if($note['expiration'] < TIMESTAMP) {
		C::t('common_adminnote')->delete($note['id']);
	} else {
		$note['adminenc'] = rawurlencode($note['admin']);
		$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
		$note['dateline'] = dgmdate($note['dateline'], 'dt');
		showtablerow('', array('', '', ''), array(
			$isfounder || $_G['member']['username'] == $note['admin'] ? '<a href="'.ADMINSCRIPT.'?action=index&notesubmit=yes&noteid='.$note['id'].'"><img src="static/image/admincp/close.gif" width="7" height="8" title="'.cplang('delete').'" /></a>' : '',
			"<span class=\"bold\"><a href=\"home.php?mod=space&username=$note[adminenc]\" target=\"_blank\">$note[admin]</a></span> $note[dateline] (".cplang('validity').": $note[expiration] ".cplang('days').")<br />$note[message]",
		));
	}
}

showtablerow('', array(), array(
	cplang('home_notes_add'),
	'<input type="text" class="txt" name="newmessage" value="" style="width:300px;" />'.cplang('validity').': <input type="text" class="txt" name="newexpiration" value="30" style="width:30px;" />'.cplang('days').'&nbsp;<input name="notesubmit" value="'.cplang('submit').'" type="submit" class="btn" />'
));
showtablefooter();
showformfooter();

showtableheader('contributors', 'fixpadding');
showtablerow('', array('', 'class="td21" style="text-align:right;"'),
	'<a href="https://gitee.com/Discuz/DiscuzX/contributors?ref=master" class="lightlink2 smallfont" target="_blank">' . cplang('contributors_see') . '</a>'
);
showtablefooter();


loaducenter();

if(empty($newversion['newversion']['qqqun'])){
  $newversion['newversion']['qqqun'] = '73'.'210'.'36'.'90';
}

showtableheader('home_sys_info', 'fixpadding left" style="width : 48%;');
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_discuz_version'),
	'Discuz! '.DISCUZ_VERSION.' R'.DISCUZ_RELEASE.' '.strtoupper(CHARSET).((strlen(DISCUZ_RELEASE) == 8 && DISCUZ_RELEASE != '20180101') ? '' : cplang('home_git_version'))
));

$newversion['newversion'] = !empty($newversion['newversion']) ? $newversion['newversion'] : array();

$downlist = array();
foreach ($newversion['newversion']['downlist'] as $key => $value){
   $downlist[] = '<a href="'.diconv($value['url'], 'utf-8', CHARSET).'" target="_blank">'.discuzcode(strip_tags(diconv($value['title'], 'utf-8', CHARSET)), 1, 0).'</a>';
}

showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_check_newversion'),
    ($newversion['newversion']['release'] ? ($newversion['newversion']['release'] != DISCUZ_RELEASE ? '<b style="color:red;">' : '').'Discuz! '.$newversion['newversion']['version'].' R'.$newversion['newversion']['release'].' '.strtoupper(CHARSET).' '.($newversion['newversion']['release'] != DISCUZ_RELEASE ? '</b>' : '') : '<a href="https://www.dismall.com/thread-73-1-1.html" target="_blank">'.cplang('detect_environment_error').'</a>').
	  ' <a href="'.ADMINSCRIPT.'?action=index&checknewversion&formhash='.$_G['formhash'].'">[ '.cplang('refresh').' ]</a>&nbsp;&nbsp;<br><br>'.
    (!empty($downlist) ? implode('&#x3001;', $downlist).($newversion['newversion']['qqqun'] ? '<span class="bold">&nbsp;&nbsp;|&nbsp;&nbsp;'.cplang('qq_group').$newversion['newversion']['qqqun'].'</span>' : '') : '<span class="bold"><a href="https://gitee.com/3dming/DiscuzL/attach_files" target="_blank">'.cplang('download_latest').'</a> | '.cplang('qq_group').'73'.'21'.'03'.'690</span>')
));

showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_ucclient_version'),
	'UCenter '.UC_CLIENT_VERSION.' Release '.UC_CLIENT_RELEASE
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_environment'),
	$serverinfo
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_serversoftware'),
	$serversoft
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database'),
	$dbversion
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_upload_perm'),
	$fileupload
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database_size'),
	$dbsize
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_attach_size'),
	$attachsize
));
showtablefooter();

showtableheader('discuz_news', 'fixpadding left" style="width : 48%; margin-left: 2%; clear: none;', '', '3');
if(!empty($newversion['news'])){
    $newversion['news'] = dhtmlspecialchars($newversion['news']);
    foreach ($newversion['news'] as $v){
        showtablerow('', array('', 'class="td21" style="text-align:right;"'), array(
            '<a href="'.$v['url'].'" target="_blank">'.discuzcode(strip_tags(diconv($v['title'], 'utf-8', CHARSET)), 1, 0).'</a>',
            '['.discuzcode(strip_tags($v['date']), 1, 0).']',
        ));
    }
} else {
    showtablerow('', array('', 'class="td21" style="text-align:right;"'), array(
        '<a href="https://www.dismall.com/" target="_blank">'.cplang('log_in_to_update').'</a>',
        '',
    ));
    showtablerow('', array('', 'class="td21" style="text-align:right;"'), array(
        '<a href="https://gitee.com/3dming/DiscuzL/attach_files" target="_blank">'.cplang('download_latest').'</a>',
        '',
    ));
}
showtablefooter();

echo '<div class="clear"></div>';

showtableheader('home_dev', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"'), array(
	cplang('home_dev_copyright'),
	'<span class="bold">'.cplang('org_name').'</span>'
));
showtablerow('', array('class="vtop td24 lineheight"'), array(
	'&#x627F;&#x63A5;&#x8FD0;&#x8425;',
	'<span class="bold">&#x5408;&#x80A5;&#x8D30;&#x9053;&#x7F51;&#x7EDC;&#x79D1;&#x6280;&#x6709;&#x9650;&#x516C;&#x53F8;</span>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
	cplang('home_dev_manager'),
	'<a href="https://discuz.dismall.com/home.php?mod=space&uid=1" class="lightlink2 smallfont" target="_blank">'.cplang('dev_manager').'</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
	cplang('home_dev_team'),
	'
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=174393" class="lightlink2 smallfont" target="_blank">Guode \'sup\' Li</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=859" class="lightlink2 smallfont" target="_blank">Hypo \'Cnteacher\' Wang</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=263098" class="lightlink2 smallfont" target="_blank">Liming \'huangliming\' Huang</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=706770" class="lightlink2 smallfont" target="_blank">Jun \'Yujunhao\' Du</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=80629" class="lightlink2 smallfont" target="_blank">Ning \'Monkey\' Hou</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=246213" class="lightlink2 smallfont" target="_blank">Lanbo Liu</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=322293" class="lightlink2 smallfont" target="_blank">Qingpeng \'andy888\' Zheng</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=401635" class="lightlink2 smallfont" target="_blank">Guosheng \'bilicen\' Zhang</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=2829" class="lightlink2 smallfont" target="_blank">Mengshu \'msxcms\' Chen</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=492114" class="lightlink2 smallfont" target="_blank">Liang \'Metthew\' Xu</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=1087718" class="lightlink2 smallfont" target="_blank">Yushuai \'Max\' Cong</a>
	 <a href="https://discuz.dismall.com/home.php?mod=space&uid=875919" class="lightlink2 smallfont" target="_blank">Jie \'tom115701\' Zhang</a>
	 '
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight team"'), array(
	cplang('home_dev_skins'),
	'<a href="https://discuz.dismall.com/home.php?mod=space&uid=294092" class="lightlink2 smallfont" target="_blank">Fangming \'Lushnis\' Li</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=674006" class="lightlink2 smallfont" target="_blank">Jizhou \'Iavav\' Yuan</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=717854" class="lightlink2 smallfont" target="_blank">Ruitao \'Pony.M\' Ma</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight team"'), array(
	cplang('home_dev_thanks'),
	'<a href="https://gitee.com/Discuz/DiscuzX/contributors?ref=master" class="lightlink2 smallfont" target="_blank">Discuz! X Git Contributors</a>
	<a href="http://www.discuzfans.com" class="lightlink2 smallfont" target="_blank">Discuz! Fans</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=122246" class="lightlink2 smallfont" target="_blank">Heyond</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=632268" class="lightlink2 smallfont" target="_blank">JinboWang</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=15104" class="lightlink2 smallfont" target="_blank">Redstone</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=10407" class="lightlink2 smallfont" target="_blank">Qiang Liu</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=210272" class="lightlink2 smallfont" target="_blank">XiaoDunFang</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=86282" class="lightlink2 smallfont" target="_blank">Jianxieshui</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=9600" class="lightlink2 smallfont" target="_blank">Theoldmemory</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=2629" class="lightlink2 smallfont" target="_blank">Rain5017</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=26926" class="lightlink2 smallfont" target="_blank">Snow Wolf</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=17149" class="lightlink2 smallfont" target="_blank">Hehechuan</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=9132" class="lightlink2 smallfont" target="_blank">Pk0909</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=248" class="lightlink2 smallfont" target="_blank">feixin</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=675" class="lightlink2 smallfont" target="_blank">Laobing Jiuba</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=13877" class="lightlink2 smallfont" target="_blank">Artery</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=233" class="lightlink2 smallfont" target="_blank">Huli Hutu</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=122" class="lightlink2 smallfont" target="_blank">Lao Gui</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=159" class="lightlink2 smallfont" target="_blank">Tyc</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=177" class="lightlink2 smallfont" target="_blank">Stoneage</a>
	<a href="https://discuz.dismall.com/home.php?mod=space&uid=7155" class="lightlink2 smallfont" target="_blank">Gregry</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	cplang('home_dev_links'),
	'<a href="https://code.dismall.com/" class="lightlink2" target="_blank">'.cplang('discuz_git').'</a>,
	<a href="https://www.discuz.vip/" class="lightlink2" target="_blank">'.cplang('discussion_area').'</a>,
	<a href="https://www.dismall.com/" class="lightlink2" target="_blank">'.cplang('app_discussion').'</a>,
	<a href="'.ADMINSCRIPT.'?action=cloudaddons" class="lightlink2" target="_blank">'.cplang('app_center').'</a>
'));
showtablefooter();

echo '</div>';

?>