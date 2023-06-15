<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_setting.php 36362 2017-02-04 02:02:03Z nemohou $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$setting = C::t('common_setting')->fetch_all(null);

if(!$isfounder) {
	unset($setting['ftp']);
}

$extbutton = '';
$operation = $operation ? $operation : 'basic';

if($operation == 'styles') {
	$floatwinkeys = array('login', 'sendpm', 'newthread', 'reply', 'viewratings', 'viewwarning', 'viewthreadmod', 'viewvote', 'tradeorder', 'activity', 'debate', 'nav', 'usergroups', 'task');
	$floatwinarray = array();
	foreach($floatwinkeys as $k) {
		$floatwinarray[] = array($k, $lang['setting_styles_global_allowfloatwin_'.$k]);
	}
}

if(!submitcheck('settingsubmit')) {

	if($operation == 'ec') {
		shownav('extended', 'nav_ec', 'nav_ec_config');
	} elseif(in_array($operation, array('memory', 'cachethread', 'serveropti'))) {
		shownav('global', 'setting_optimize');
	} elseif($operation == 'seo') {
		shownav('global', 'nav_seo');
	} elseif($operation == 'styles') {
		shownav('style', 'setting_styles');
	} elseif($operation == 'editor') {
		shownav('style', 'setting_editor');
	} elseif($operation == 'profile') {
		shownav('user', 'nav_members_profile_group');
	} elseif($operation == 'threadprofile') {
		shownav('style', 'setting_threadprofile');
	} elseif($operation == 'sec') {
		shownav('safe', 'setting_sec');
	} elseif($operation == 'seccheck') {
		shownav('safe', 'setting_seccheck');
	} elseif($operation == 'accountguard') {
		shownav('safe', 'setting_accountguard');
	} elseif(in_array($operation, array('mail', 'uc'))) {
		shownav('founder', 'setting_'.$operation);
	} else {
		shownav('global', 'setting_'.$operation);
	}

	if(in_array($operation, array('memory', 'cachethread', 'serveropti', 'memorydata'))) {
		$current = array($operation => 1);
		$memorydata = memory('check') ? array('setting_memorydata', 'setting&operation=memorydata', $current['memorydata']) : '';
		showsubmenu('setting_optimize', array(
			array('setting_cachethread', 'setting&operation=cachethread', $current['cachethread']),
			array('setting_serveropti', 'setting&operation=serveropti', $current['serveropti']),
			array('setting_memory', 'setting&operation=memory', $current['memory']),
			$memorydata
		));
	} elseif($operation == 'seo') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('rewrite', 'portal', 'forum', 'home', 'group')) ? $_GET['anchor'] : 'rewrite';
		showsubmenuanchors('nav_seo', array(
			array('nav_seo_rewrite', 'rewrite', $_GET['anchor'] == 'rewrite'),
			array('nav_seo_portal', 'portal', $_GET['anchor'] == 'portal'),
			array('nav_seo_forum', 'forum', $_GET['anchor'] == 'forum'),
			array('nav_seo_home', 'home', $_GET['anchor'] == 'home'),
			array('nav_seo_group', 'group', $_GET['anchor'] == 'group'),
			array('other', 'other', $_GET['anchor'] == 'other'),
		));
	} elseif($operation == 'ec') {
		showsubmenu('nav_ec', array(
			array('nav_ec_config', 'setting&operation=ec', 1),
			array('nav_ec_tenpay', 'ec&operation=tenpay', 0),
			array('nav_ec_alipay', 'ec&operation=alipay', 0),
			array('nav_ec_credit', 'ec&operation=credit', 0),
			array('nav_ec_orders', 'ec&operation=orders', 0),
			array('nav_ec_tradelog', 'tradelog&mod=forum', 0),
			array('nav_ec_inviteorders', 'ec&operation=inviteorders', 0)
		));
	} elseif($operation == 'access') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('register', 'access')) ? $_GET['anchor'] : 'register';
		showsubmenuanchors('setting_access', array(
			array('setting_access_register', 'register', $_GET['anchor'] == 'register'),
			array('setting_access_access', 'access', $_GET['anchor'] == 'access')
		));
	} elseif($operation == 'follow') {
		$_GET['anchor'] = 'base';
		showsubmenuanchors('setting_follow', array(
				array('setting_follow_base', 'base', true)
		));
	} elseif($operation == 'home') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'privacy')) ? $_GET['anchor'] : 'base';
		showsubmenuanchors('setting_home', array(
			array('setting_home_base', 'base', $_GET['anchor'] == 'base'),
			array('setting_home_privacy', 'privacy', $_GET['anchor'] == 'privacy')
		));
	} elseif($operation == 'profile') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'edit')) ? $_GET['anchor'] : 'base';
	} elseif($operation == 'mail') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('setting', 'check')) ? $_GET['anchor'] : 'setting';
		showsubmenuanchors('setting_mail', array(
			array('setting_mail_setting', 'mailsetting', $_GET['anchor'] == 'setting'),
			array('setting_mail_check', 'mailcheck', $_GET['anchor'] == 'check')
		));
	} elseif($operation == 'sec') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'reginput', 'postperiodtime')) ? $_GET['anchor'] : 'base';
		showsubmenuanchors('setting_sec', array(
			array('setting_sec_base', 'base', $_GET['anchor'] == 'base'),
			array('setting_sec_reginput', 'reginput', $_GET['anchor'] == 'reginput'),
			array('setting_sec_postperiodtime', 'postperiodtime', $_GET['anchor'] == 'postperiodtime'),
		));
	} elseif($operation == 'seccheck') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('seccode', 'secqaa')) ? $_GET['anchor'] : 'seccode';
		showsubmenuanchors('setting_seccheck', array(
			array('setting_sec_seccode', 'seccode', $_GET['anchor'] == 'seccode'),
			array('setting_sec_secqaa', 'secqaa', $_GET['anchor'] == 'secqaa'),
		));
	} elseif($operation == 'attach') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('basic', 'forumattach', 'remote', 'albumattach', 'portalarticle')) ? $_GET['anchor'] : 'basic';
		showsubmenuanchors('setting_attach', array(
			array('setting_attach_basic', 'basic', $_GET['anchor'] == 'basic'),
			$isfounder ? array('setting_attach_remote', 'remote', $_GET['anchor'] == 'remote') : '',
			array('setting_attach_forumattach', 'forumattach', $_GET['anchor'] == 'forumattach'),
			array('setting_attach_album', 'albumattach', $_GET['anchor'] == 'albumattach'),
			array('setting_attach_portal_article_attach', 'portalarticle', $_GET['anchor'] == 'portalarticle'),
		));
	} elseif($operation == 'styles') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('global', 'index', 'forumdisplay', 'viewthread', 'threadprofile', 'numbercard', 'refresh', 'sitemessage')) ? $_GET['anchor'] : 'global';
		$current = array($_GET['anchor'] => 1);
		showsubmenu('setting_styles', array(
			array('setting_styles_global', 'setting&operation=styles&anchor=global', $current['global']),
			array('setting_styles_index', 'setting&operation=styles&anchor=index', $current['index']),
			array('setting_styles_forumdisplay', 'setting&operation=styles&anchor=forumdisplay', $current['forumdisplay']),
			array('setting_styles_viewthread', 'setting&operation=styles&anchor=viewthread', $current['viewthread']),
			array('setting_styles_threadprofile', 'setting&operation=styles&anchor=threadprofile', $current['threadprofile']),
			array('members_profile_numbercard', 'setting&operation=styles&anchor=numbercard', $current['numbercard']),
			array('setting_styles_refresh', 'setting&operation=styles&anchor=refresh', $current['refresh']),
			array('setting_styles_sitemessage', 'setting&operation=styles&anchor=sitemessage', $current['sitemessage'])
		));
	} elseif($operation == 'functions') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('curscript', 'mod', 'heatthread', 'recommend', 'comment', 'activity', 'other', 'threadexp', 'guide')) ? $_GET['anchor'] : 'curscript';
		showsubmenu('setting_functions', array(
			array('setting_functions_curscript', 'setting&operation=functions&anchor=curscript', $_GET['anchor'] == 'curscript'),
			array('setting_functions_mod', 'setting&operation=functions&anchor=mod', $_GET['anchor'] == 'mod'),
			array('setting_functions_heatthread', 'setting&operation=functions&anchor=heatthread', $_GET['anchor'] == 'heatthread'),
			array('setting_functions_recommend', 'setting&operation=functions&anchor=recommend', $_GET['anchor'] == 'recommend'),
			array('setting_functions_comment', 'setting&operation=functions&anchor=comment', $_GET['anchor'] == 'comment'),
			array('setting_functions_guide', 'setting&operation=functions&anchor=guide', $_GET['anchor'] == 'guide'),
			array('setting_functions_activity', 'setting&operation=functions&anchor=activity', $_GET['anchor'] == 'activity'),
			array('setting_functions_threadexp', 'setting&operation=functions&anchor=threadexp', $_GET['anchor'] == 'threadexp'),
			array('setting_functions_other', 'setting&operation=functions&anchor=other', $_GET['anchor'] == 'other'),
		));
	} elseif($operation == 'credits') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'policytable')) ? $_GET['anchor'] : 'base';
		$current = array($_GET['anchor'] => 1);
		showsubmenu('setting_credits', array(
			array('setting_credits_base', 'setting&operation=credits&anchor=base', $current['base']),
			array('setting_credits_policy', 'credits&operation=list&anchor=policytable', $current['policytable']),
		));
	} elseif($operation == 'editor') {
		showsubmenu('setting_editor', array(
			array('setting_editor_global', 'setting&operation=editor', 1),
			array('setting_editor_code', 'misc&operation=bbcode', 0),
		));
	} elseif($operation == 'imgwater') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('portal', 'forum', 'album')) ? $_GET['anchor'] : 'portal';
		showsubmenuanchors('setting_imgwater', array(
			array('setting_imgwater_portal', 'portal', $_GET['anchor'] == 'portal'),
			array('setting_imgwater_forum', 'forum', $_GET['anchor'] == 'forum'),
			array('setting_imgwater_album', 'album', $_GET['anchor'] == 'album'),
		));
	} elseif($operation == 'mobile') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('status')) ? $_GET['anchor'] : 'status';
		showsubmenuanchors('setting_mobile', array(
			array('setting_mobile_status', 'status', $_GET['anchor'] == 'status')
		));
	} elseif($operation == 'antitheft') {
		$_GET['anchor'] = in_array($_GET['anchor'], array('iplist'), true) ? $_GET['anchor'] : '';
		showsubmenu('setting_antitheft', array(
			array('setting_antitheft', 'setting&operation=antitheft', $_GET['anchor'] == ''),
			array('setting_antitheft_iplist', 'setting&operation=antitheft&anchor=iplist', $_GET['anchor'] == 'iplist'),
		));
	} else {
		showsubmenu('setting_'.$operation);
	}
	showformheader('setting&edit=yes', 'enctype');
	showhiddenfields(array('operation' => $operation));

	if($operation == 'basic') {

		showtableheader('');
		showsetting('setting_basic_bbname', 'settingnew[bbname]', $setting['bbname'], 'text');
		showsetting('setting_basic_sitename', 'settingnew[sitename]', $setting['sitename'], 'text');
		showsetting('setting_basic_siteurl', 'settingnew[siteurl]', $setting['siteurl'], 'text');
		showsetting('setting_basic_adminemail', 'settingnew[adminemail]', $setting['adminemail'], 'text');
		showsetting('setting_basic_site_qq', 'settingnew[site_qq]', $setting['site_qq'], 'text',$disabled = '', $hidden = 0, $comment = '', $extra = 'id="settingnew[site_qq]"');
		showsetting('setting_basic_icp', 'settingnew[icp]', $setting['icp'], 'text');
		showsetting('setting_basic_boardlicensed', 'settingnew[boardlicensed]', $setting['boardlicensed'], 'radio');
		showsetting('setting_basic_stat', 'settingnew[statcode]', $setting['statcode'], 'textarea');
		showtablefooter();

		showtableheader('setting_basic_bbclosed');
		showsetting('setting_basic_bbclosed', 'settingnew[bbclosed]', $setting['bbclosed'], 'radio', 0, 1);
		showsetting('setting_basic_closedreason', 'settingnew[closedreason]', $setting['closedreason'], 'textarea');
		showsetting('setting_basic_bbclosed_activation', 'settingnew[closedallowactivation]', $setting['closedallowactivation'], 'radio');
		showtagfooter('tbody');

	} elseif($operation == 'follow') {
		require_once libfile('function/forumlist');
		showtableheader('', 'nobottom', 'id="base"'.($_GET['anchor'] != 'base' ? ' style="display: none"' : ''));
		showsetting('setting_follow_base_default_follow_retain_day', 'settingnew[followretainday]', $setting['followretainday'], 'text');
		showsetting('setting_follow_base_default_follow_add_notice', 'settingnew[followaddnotice]', $setting['followaddnotice'], 'radio');
		showsetting('setting_follow_base_default_view_profile', 'settingnew[allowquickviewprofile]', $setting['allowquickviewprofile'], 'radio');
		showtablefooter();

	} elseif($operation == 'home') {

		require_once libfile('function/forumlist');

		showtableheader('', 'nobottom', 'id="base"'.($_GET['anchor'] != 'base' ? ' style="display: none"' : ''));
		showsetting('setting_home_base_feedday', 'settingnew[feedday]', $setting['feedday'], 'text');
		showsetting('setting_home_base_feedmaxnum', 'settingnew[feedmaxnum]', $setting['feedmaxnum'], 'text');
		showsetting('setting_home_base_feedhotday', 'settingnew[feedhotday]', $setting['feedhotday'], 'text');
		showsetting('setting_home_base_feedhotmin', 'settingnew[feedhotmin]', $setting['feedhotmin'], 'text');
		showsetting('setting_home_base_feedtargetblank', 'settingnew[feedtargetblank]', $setting['feedtargetblank'], 'radio');
		showsetting('setting_home_base_showallfriendnum', 'settingnew[showallfriendnum]', $setting['showallfriendnum'], 'text');
		showsetting('setting_home_base_feedhotnum', 'settingnew[feedhotnum]', $setting['feedhotnum'], 'text');
		showsetting('setting_home_base_maxpage', 'settingnew[maxpage]', $setting['maxpage'], 'text');
		showsetting('setting_home_base_sendmailday', 'settingnew[sendmailday]', $setting['sendmailday'], 'text');
		showsetting('setting_home_base_recycle_bin', 'settingnew[blogrecyclebin]', $setting['blogrecyclebin'], 'radio');

		showtagfooter('tbody');
		loadcache('forums');
		showsetting('setting_home_base_groupnum', 'settingnew[friendgroupnum]', $setting['friendgroupnum'], 'text');
		$threadtype = array('1' => 'poll', '2' => 'trade', '3' => 'reward', '4' => 'activity', '5' => 'debate');
		$oldforums = $_G['cache']['forums'];
		foreach($threadtype as $special => $key) {
			if($special == 0) {
				$fields = C::t('forum_forumfield')->fetch_all_by_fid(array_keys($_G['cache']['forums']));
				foreach($fields as $fid => $field) {
					if(!empty($field['threadsorts'])) {
						unset($_G['cache']['forums'][$fid]);
					}
				}
			} else {
				$_G['cache']['forums'] = $oldforums;
			}
			$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select')."</option>".str_replace('%', '%%', forumselect(FALSE, 0, $setting[$key.'forumid'], TRUE, FALSE, $special)).'</select>';
			showsetting('setting_home_base_default_'.$key.'_forum', "settingnew[{$key}forumid]", $setting[$key.'forumid'], sprintf($forumselect, "settingnew[{$key}forumid]"));
		}

		showsetting('setting_home_base_default_doing', 'settingnew[defaultdoing]', $setting['defaultdoing'], 'textarea');
		showtablefooter();

		if(isset($setting['privacy'])) {
			$setting['privacy'] = dunserialize($setting['privacy']);
		}
		showtableheader('', 'nobottom', 'id="privacy"'.($_GET['anchor'] != 'privacy' ? ' style="display: none"' : ''));
		showtitle('setting_home_privacy_new_user');
		showsetting('setting_home_privacy_view_index', array('settingnew[privacy][view][index]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['index'], 'select');
		showsetting('setting_home_privacy_view_profile', array('settingnew[privacy][view][profile]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['profile'], 'select');
		showsetting('setting_home_privacy_view_friend', array('settingnew[privacy][view][friend]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['friend'], 'select');
		showsetting('setting_home_privacy_view_wall', array('settingnew[privacy][view][wall]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(2, $lang['setting_home_privacy_self']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['wall'], 'select');
		showsetting('setting_home_privacy_view_feed', array('settingnew[privacy][view][home]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['home'], 'select');
		showsetting('setting_home_privacy_view_doing', array('settingnew[privacy][view][doing]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['doing'], 'select');
		showsetting('setting_home_privacy_view_blog', array('settingnew[privacy][view][blog]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['blog'], 'select');
		showsetting('setting_home_privacy_view_album', array('settingnew[privacy][view][album]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['album'], 'select');
		showsetting('setting_home_privacy_view_share', array('settingnew[privacy][view][share]', array(
			array(0, $lang['setting_home_privacy_alluser']),
			array(1, $lang['setting_home_privacy_friend']),
			array(3, $lang['setting_home_privacy_register'])
		)), $setting['privacy']['view']['share'], 'select');

		showsetting('setting_home_privacy_default_feed', array('settingnew[privacy][feed]', array(
			array('doing', $lang['setting_home_privacy_default_feed_doing'], '1'),
			array('blog', $lang['setting_home_privacy_default_feed_blog'], '1'),
			array('upload', $lang['setting_home_privacy_default_feed_upload'], '1'),
			array('share', $lang['setting_home_privacy_default_feed_share'], '1'),
			array('poll', $lang['setting_home_privacy_default_feed_poll'], '1'),
			array('joinpoll', $lang['setting_home_privacy_default_feed_joinpoll'], '1'),
			array('friend', $lang['setting_home_privacy_default_feed_friend'], '1'),
			array('comment', $lang['setting_home_privacy_default_feed_comments'], '1'),
			array('show', $lang['setting_home_privacy_default_feed_show'], '1'),
			array('credit', $lang['setting_home_privacy_default_feed_credit'], '1'),
			array('spaceopen', $lang['setting_home_privacy_default_feed_spaceopen'], '1'),
			array('invite', $lang['setting_home_privacy_default_feed_invite'], '1'),
			array('task', $lang['setting_home_privacy_default_feed_task'], '1'),
			array('profile', $lang['setting_home_privacy_default_feed_profile'], '1'),
			array('click', $lang['setting_home_privacy_default_feed_click'], '1'),
			array('newthread', $lang['setting_home_privacy_default_feed_newthread'], '1'),
			array('newreply', $lang['setting_home_privacy_default_feed_newreply'], '1'),
			)), $setting['privacy']['feed'], 'omcheckbox');
		showtablefooter();
		showtableheader();

	} elseif($operation == 'profile') {

		$profilegroup = dunserialize($setting['profilegroup']);
		if($_GET['anchor'] == 'edit' && in_array($_GET['type'], array('base', 'contact', 'edu', 'work', 'info'))) {
			shownav('user', 'nav_members_profile_group');
			$groupinfo = $profilegroup[$_GET['type']];
			showsubmenu($lang['setting_profile_group_name'].'-'.$groupinfo['title'], array(
				array('members_profile_group', 'setting&operation=profile&anchor=base', 0),
				array($lang['edit'], 'setting&operation=profile&anchor=edit&type='.$_GET['type'], 1),
			));
			showtableheader();
			showsetting('setting_profile_group_name', "settingnew[profile][title]", $groupinfo['title'], 'text');
			showsetting('setting_profile_group_available', "settingnew[profile][available]", $groupinfo['available'], 'radio');
			showsetting('setting_profile_group_displayorder', "settingnew[profile][displayorder]", $groupinfo['displayorder'], 'text');

			$varname = array('settingnew[profile][field]', array(), 'isfloat');
			foreach(C::t('common_member_profile_setting')->fetch_all_by_available(1) as $value) {
				if(!in_array($value['fieldid'], array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'resideprovince', 'birthprovince', 'residedist', 'residecommunity'))) {
					$varname[1][] = array($value['fieldid'], $value['title'], $value['fieldid']);
				}
			}
			$varname[1][] = array('sightml', $lang['setting_profile_personal_signature'], 'sightml');
			$varname[1][] = array('customstatus', $lang['setting_profile_permission_basic_status'], 'customstatus');
			$varname[1][] = array('timeoffset', $lang['setting_profile_time_zone'], 'timeoffset');

			showsetting('setting_profile_field', $varname, $groupinfo['field'], 'omcheckbox');
			echo "<input type=\"hidden\" name=\"settingnew[profile][type]\" value=\"$_GET[type]\" />";
			showtablefooter();

		} else {
			$current = array($_GET['action'] => 1);
			$profilenav = array(
				array('members_profile_list', 'members&operation=profile', $current['members']),
				array('members_profile_group', 'setting&operation=profile', $current['setting']),
			);
			showsubmenu($lang['members_profile'], $profilenav);

			showtips('setting_profile_tips');
			showtableheader('setting_profile_group_setting', 'fixpadding');
			showsubtitle(array('setting_profile_group_available', 'setting_profile_group_displayorder', 'setting_profile_group_name', ''), 'header');
			foreach($profilegroup as $key => $group) {
				showtablerow('', array('class="td25"', '', '', 'class="td25"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[profilegroupnew][$key][available]\" value=\"1\" ".($profilegroup[$key]['available'] ? 'checked' : '')." />",
					"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[profilegroupnew][$key][displayorder]\" value=\"{$profilegroup[$key]['displayorder']}\">",
					"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[profilegroupnew][$key][title]\" value=\"{$profilegroup[$key]['title']}\">",
					"<a href=\"".ADMINSCRIPT."?action=setting&operation=profile&anchor=edit&type=$key\">".$lang['edit']."</a>"
				));
			}
			showtablefooter();
		}


	} elseif($operation == 'access') {

		$wmsgcheck = array($setting['welcomemsg'] =>'checked');
		$setting['inviteconfig'] = dunserialize($setting['inviteconfig']);
		$setting['extcredits'] = dunserialize($setting['extcredits']);
		$buycredits = $rewardcredits = '';
		for($i = 0; $i <= 8; $i++) {
			if($setting['extcredits'][$i]['available']) {
				$extcredit = 'extcredits'.$i.' ('.$setting['extcredits'][$i]['title'].')';
				$buycredits .= '<option value="'.$i.'" '.($i == intval($setting['inviteconfig']['invitecredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
				$rewardcredits .= '<option value="'.$i.'" '.($i == intval($setting['inviteconfig']['inviterewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			}
		}

		$groupselect = '';
		foreach(C::t('common_usergroup')->fetch_all_by_type('special') as $group) {
			$groupselect .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['inviteconfig']['invitegroupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		$taskarray = array(array('', cplang('select')));
		foreach(C::t('common_task')->fetch_all_by_available(2) as $task) {
			$taskarray[] = array($task['taskid'], $task['name']);
		}

		showtableheader('', 'nobottom', 'id="register"'.($_GET['anchor'] != 'register' ? ' style="display: none"' : ''));
		$regstatus = array();
		if($setting['regstatus'] == 1 || $setting['regstatus'] == 3) {
			$regstatus[] = 'open';
		}
		if($setting['regstatus'] == 2 || $setting['regstatus'] == 3) {
			$regstatus[] = 'invite';
		}
		if($setting['regconnect']) {
			$regstatus[] = 'connect';
		}
		showsetting('setting_access_register_status', array('settingnew[regstatus]', array(
			array('open', $lang['setting_access_register_open']),
			array('invite', $lang['setting_access_register_invite'], 'showinvite'),
			$_G['setting']['connect']['allow'] ? array('connect', $lang['setting_access_register_connect']) : array(),
		)), $regstatus, 'mcheckbox');

		showtagheader('tbody', 'showinvite', in_array('invite', $regstatus), 'sub');
		showsetting('setting_access_register_invite_buyprompt', 'settingnew[inviteconfig][invitecodeprompt]', $setting['inviteconfig']['invitecodeprompt'], 'textarea');
		showsetting('setting_access_register_invite_buy', 'settingnew[inviteconfig][buyinvitecode]', $setting['inviteconfig']['buyinvitecode'], 'radio');
		showsetting('setting_access_register_invite_buyprice', 'settingnew[inviteconfig][invitecodeprice]', $setting['inviteconfig']['invitecodeprice'], 'text');
		showsetting('setting_access_register_invite_credit', '', '', '<select name="settingnew[inviteconfig][inviterewardcredit]">'.$rewardcredits.'</select>');
		showsetting('setting_access_register_invite_addcredit', 'settingnew[inviteconfig][inviteaddcredit]', $setting['inviteconfig']['inviteaddcredit'], 'text');
		showsetting('setting_access_register_invite_invitedcredit', 'settingnew[inviteconfig][invitedaddcredit]', $setting['inviteconfig']['invitedaddcredit'], 'text');
		showsetting('setting_access_register_invite_group', '', '', '<select name="settingnew[inviteconfig][invitegroupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$groupselect.'</select>');
		showsetting('setting_access_register_invite_areawhite', 'settingnew[inviteconfig][inviteareawhite]', $setting['inviteconfig']['inviteareawhite'], 'textarea');
		showsetting('setting_access_register_invite_ipwhite', 'settingnew[inviteconfig][inviteipwhite]', $setting['inviteconfig']['inviteipwhite'], 'textarea');
		showtagfooter('tbody');

		showsetting('setting_access_register_regclosemessage', 'settingnew[regclosemessage]', $setting['regclosemessage'], 'textarea');
		showsetting('setting_access_register_name', 'settingnew[regname]', $setting['regname'], 'text');
		showsetting('setting_access_register_send_register_url', 'settingnew[sendregisterurl]', $setting['sendregisterurl'], 'radio');
		showsetting('setting_access_register_link_name', 'settingnew[reglinkname]', $setting['reglinkname'], 'text');
		showsetting('setting_access_register_censoruser', 'settingnew[censoruser]', $setting['censoruser'], 'textarea');
		showsetting('setting_access_register_pwlength', 'settingnew[pwlength]', $setting['pwlength'], 'text');
		$setting['strongpw'] = dunserialize($setting['strongpw']);
		showsetting('setting_access_register_strongpw', array('settingnew[strongpw]', array(
			array('1', $lang['setting_access_register_strongpw_1']),
			array('2', $lang['setting_access_register_strongpw_2']),
			array('3', $lang['setting_access_register_strongpw_3']),
			array('4', $lang['setting_access_register_strongpw_4']),
		)), $setting['strongpw'], 'mcheckbox2');
		showsetting('setting_access_register_verify', array('settingnew[regverify]', array(
			array(0, $lang['none'], array('regverifyext' => 'none')),
			array(1, $lang['setting_access_register_verify_email'], array('regverifyext' => '')),
			array(2, $lang['setting_access_register_verify_manual'], array('regverifyext' => ''))
		)), $setting['regverify'], 'mradio');
		showtagheader('tbody', 'regverifyext', $setting['regverify'], 'sub');
		showsetting('setting_access_register_verify_areawhite', 'settingnew[areaverifywhite]', $setting['areaverifywhite'], 'textarea');
		showsetting('setting_access_register_verify_ipwhite', 'settingnew[ipverifywhite]', $setting['ipverifywhite'], 'textarea');
		showtagfooter('tbody');
		showsetting('setting_access_register_maildomain', array('settingnew[regmaildomain]', array(
			array(0, $lang['none'], array('regmaildomainext' => 'none')),
			array(1, $lang['setting_access_register_maildomain_white'], array('regmaildomainext' => '')),
			array(2, $lang['setting_access_register_maildomain_black'], array('regmaildomainext' => ''))
		)), $setting['regmaildomain'], 'mradio');
		showtagheader('tbody', 'regmaildomainext', $setting['regmaildomain'], 'sub');
		showsetting('setting_access_register_maildomain_list', 'settingnew[maildomainlist]', $setting['maildomainlist'], 'textarea');
		showtagfooter('tbody');
		showsetting('setting_access_register_ctrl', 'settingnew[regctrl]', $setting['regctrl'], 'text');
		showsetting('setting_access_register_floodctrl', 'settingnew[regfloodctrl]', $setting['regfloodctrl'], 'text');
		showsetting('setting_access_register_ipctrl_time', 'settingnew[ipregctrltime]', $setting['ipregctrltime'], 'text');
		showsetting('setting_access_register_ipctrl', 'settingnew[ipregctrl]', $setting['ipregctrl'], 'textarea');
		$welcomemsg = array();
		if($setting['welcomemsg'] == 1) {
			$welcomemsg[] = '1';
		} elseif($setting['welcomemsg'] == 2) {
			$welcomemsg[] = '2';
		} elseif($setting['welcomemsg'] == 3) {
			$welcomemsg[] = '1';
			$welcomemsg[] = '2';
		} else {
			$welcomemsg[] = '0';
		}
		showsetting('setting_access_register_welcomemsg', array('settingnew[welcomemsg]', array(
			array(1, $lang['setting_access_register_welcomemsg_pm']),
			array(2, $lang['setting_access_register_welcomemsg_email'])
		)), $welcomemsg, 'mcheckbox');
		showsetting('setting_access_register_welcomemsgtitle', 'settingnew[welcomemsgtitle]', $setting['welcomemsgtitle'], 'text');
		showsetting('setting_access_register_welcomemsgtxt', 'settingnew[welcomemsgtxt]', $setting['welcomemsgtxt'], 'textarea');
		showsetting('setting_access_register_bbrules', 'settingnew[bbrules]', $setting['bbrules'], 'radio', '', 1);
		showsetting('setting_access_register_bbruleforce', 'settingnew[bbrulesforce]', $setting['bbrulesforce'], 'radio');
		showsetting('setting_access_register_bbrulestxt', 'settingnew[bbrulestxt]', $setting['bbrulestxt'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="access"'.($_GET['anchor'] != 'access' ? ' style="display: none"' : ''));
		showsetting('setting_access_access_newbiespan', 'settingnew[newbiespan]', $setting['newbiespan'], 'text');
		showsetting('setting_access_access_ipaccess', 'settingnew[ipaccess]', $setting['ipaccess'], 'textarea');
		showsetting('setting_access_access_adminipaccess', 'settingnew[adminipaccess]', $setting['adminipaccess'], 'textarea');
		showsetting('setting_access_access_domainwhitelist', 'settingnew[domainwhitelist]', '', '<textarea class="tarea" cols="50" id="settingnew[domainwhitelist]" name="settingnew[domainwhitelist]" onkeydown="textareakey(this, event)" onkeyup="textareasize(this, 0)" ondblclick="textareasize(this, 1)" rows="6">'.$setting['domainwhitelist'].'</textarea><br><input class="checkbox" type="checkbox" value="1" name="settingnew[domainwhitelist_affectimg]" '.($setting['domainwhitelist_affectimg'] ?  'checked' : '').'>'.cplang('setting_access_access_domainwhitelist_affectimg'));
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'styles') {

		$_G['setting']['showsettings'] = str_pad(decbin($setting['showsettings']), 3, 0, STR_PAD_LEFT);
		$setting['showsignatures'] = $_G['setting']['showsettings'][0];
		$setting['showavatars'] = $_G['setting']['showsettings'][1];
		$setting['showimages'] = $_G['setting']['showsettings'][2];
		$setting['postnocustom'] = implode("\n", (array)dunserialize($setting['postnocustom']));
		$setting['sitemessage'] = dunserialize($setting['sitemessage']);
		$setting['disallowfloat'] = $setting['disallowfloat'] ? dunserialize($setting['disallowfloat']) : array();
		$setting['allowfloatwin'] = array_diff($floatwinkeys, $setting['disallowfloat']);
		$setting['indexhot'] = dunserialize($setting['indexhot']);

		$setting['customauthorinfo'] = dunserialize($setting['customauthorinfo']);
		$setting['customauthorinfo'] = $setting['customauthorinfo'][0];
		list($setting['zoomstatus'], $setting['imagemaxwidth']) = explode("\t", $setting['zoomstatus']);
		$setting['imagemaxwidth'] = !empty($setting['imagemaxwidth']) ? $setting['imagemaxwidth'] : 600;
		$setting['guestviewthumb'] = dunserialize($setting['guestviewthumb']);
		$setting['guesttipsinthread'] = dunserialize($setting['guesttipsinthread']);

		showtips('setting_tips', 'global_tips', $_GET['anchor'] == 'global');
		showtableheader('setting_styles_global', 'nobottom', 'id="global"'.($_GET['anchor'] != 'global' ? ' style="display: none"' : ''));
		showsetting('setting_styles_global_home_style', array('settingnew[homestyle]', array(
				array(1, $lang['setting_styles_global_home_style_1']),
				array(0, $lang['setting_styles_global_home_style_0']),
		)), $setting['homestyle'], 'mradio');
		showsetting('setting_styles_global_homepage_style', array('settingnew[homepagestyle]', array(
				array(1, $lang['setting_styles_global_homepage_style_1']),
				array(0, $lang['setting_styles_global_homepage_style_0']),
		)), $setting['homepagestyle'], 'mradio');

		showsetting('setting_styles_global_navsubhover', array('settingnew[navsubhover]', array(
			array(0, $lang['setting_styles_global_navsubhover_0']),
			array(1, $lang['setting_styles_global_navsubhover_1']),
		)), $setting['navsubhover'], 'mradio');
		showsetting('setting_styles_index_allowwidthauto', array('settingnew[allowwidthauto]', array(
			array(1, $lang['setting_styles_index_allowwidthauto_1']),
			array(0, $lang['setting_styles_index_allowwidthauto_0']),
		), 1), $setting['allowwidthauto'], 'mradio');
		showtagheader('tbody', '', 1, 'sub');
		showsetting('setting_styles_index_switchwidthauto', 'settingnew[switchwidthauto]', $setting['switchwidthauto'], 'radio');
		showtagfooter('tbody');
		showsetting('setting_styles_global_allowfloatwin', array('settingnew[allowfloatwin]', $floatwinarray), $setting['allowfloatwin'], 'mcheckbox');
		showsetting('setting_styles_global_showfjump', 'settingnew[showfjump]', $setting['showfjump'], 'radio');
		showsetting('setting_styles_global_creditnotice', 'settingnew[creditnotice]', $setting['creditnotice'], 'radio');
		showsetting('setting_styles_global_showusercard', 'settingnew[showusercard]', $setting['showusercard'], 'radio');
		showsetting('setting_styles_global_anonymoustext', 'settingnew[anonymoustext]', $setting['anonymoustext'], 'text');
		showtablefooter();

		showtableheader('setting_styles_index', 'nobottom', 'id="index"'.($_GET['anchor'] != 'index' ? ' style="display: none"' : ''));
		showsetting('setting_styles_index_indexhot_status', 'settingnew[indexhot][status]', $setting['indexhot']['status'], 'radio', 0, 1);
		showsetting('setting_styles_index_indexhot_limit', 'settingnew[indexhot][limit]', $setting['indexhot']['limit'], 'text');
		showsetting('setting_styles_index_indexhot_days', 'settingnew[indexhot][days]', $setting['indexhot']['days'], 'text');
		showsetting('setting_styles_index_indexhot_expiration', 'settingnew[indexhot][expiration]', $setting['indexhot']['expiration'], 'text');
		showsetting('setting_styles_index_indexhot_messagecut', 'settingnew[indexhot][messagecut]', $setting['indexhot']['messagecut'], 'text');
		showtagfooter('tbody');
		showsetting('setting_styles_index_subforumsindex', 'settingnew[subforumsindex]', $setting['subforumsindex'], 'radio');
		showsetting('setting_styles_index_forumlinkstatus', 'settingnew[forumlinkstatus]', $setting['forumlinkstatus'], 'radio');
		showsetting('setting_styles_index_forumallowside', 'settingnew[forumallowside]', $setting['forumallowside'], 'radio');
		showsetting('setting_styles_index_whosonline', array('settingnew[whosonlinestatus]', array(
			array(0, $lang['setting_styles_index_display_none']),
			array(1, $lang['setting_styles_index_whosonline_index']),
			array(2, $lang['setting_styles_index_whosonline_forum']),
			array(3, $lang['setting_styles_index_whosonline_both'])
		)), $setting['whosonlinestatus'], 'select');
		showsetting('setting_styles_index_whosonline_contract', 'settingnew[whosonline_contract]', $setting['whosonline_contract'], 'radio');
		showsetting('setting_styles_index_online_more_members', 'settingnew[maxonlinelist]', $setting['maxonlinelist'], 'text');
		showsetting('setting_styles_index_hideprivate', 'settingnew[hideprivate]', $setting['hideprivate'], 'radio');
		showsetting('setting_styles_index_showfollowcollection', 'settingnew[showfollowcollection]', $setting['showfollowcollection'], 'text');
		showsetting('setting_styles_index_disfixednv', 'settingnew[disfixednv_forumindex]', !empty($setting['disfixednv_forumindex']), 'radio');
		showtablefooter();

		showtips('setting_tips', 'forumdisplay_tips', $_GET['anchor'] == 'forumdisplay');
		showtableheader('setting_styles_forumdisplay', 'nobottom', 'id="forumdisplay"'.($_GET['anchor'] != 'forumdisplay' ? ' style="display: none"' : ''));
		showsetting('setting_styles_forumdisplay_tpp', 'settingnew[topicperpage]', $setting['topicperpage'], 'text');
		showsetting('setting_styles_forumdisplay_threadmaxpages', 'settingnew[threadmaxpages]', $setting['threadmaxpages'], 'text');
		showsetting('setting_styles_forumdisplay_leftsidewidth', 'settingnew[leftsidewidth]', $setting['leftsidewidth'], 'text');
		showsetting('setting_styles_forumdisplay_leftsideopen', 'settingnew[leftsideopen]', $setting['leftsideopen'], 'radio');
		showsetting('setting_styles_forumdisplay_globalstick', 'settingnew[globalstick]', $setting['globalstick'], 'radio');
		showsetting('setting_styles_forumdisplay_targetblank', 'settingnew[targetblank]', $setting['targetblank'], 'radio');
		showsetting('setting_styles_forumdisplay_stick', 'settingnew[threadsticky]', $setting['threadsticky'], 'text');
		showsetting('setting_styles_forumdisplay_part', 'settingnew[forumseparator]', $setting['forumseparator'], 'radio');
		showsetting('setting_styles_forumdisplay_visitedforums', 'settingnew[visitedforums]', $setting['visitedforums'], 'text');
		showsetting('setting_styles_forumdisplay_fastpost', 'settingnew[fastpost]', $setting['fastpost'], 'radio', 0, 1);
		showsetting('setting_styles_forumdisplay_fastsmilies', 'settingnew[fastsmilies]', $setting['fastsmilies'], 'radio');
		showtagfooter('tbody');
		$setting['forumpicstyle'] = dunserialize($setting['forumpicstyle']);
		showsetting('setting_styles_forumdisplay_forumpicstyle_thumbwidth', 'settingnew[forumpicstyle][thumbwidth]', $setting['forumpicstyle']['thumbwidth'], 'text');
		showsetting('setting_styles_forumdisplay_forumpicstyle_thumbheight', 'settingnew[forumpicstyle][thumbheight]', $setting['forumpicstyle']['thumbheight'], 'text');
		showsetting('setting_styles_forumdisplay_forumpicstyle_thumbnum', 'settingnew[forumpicstyle][thumbnum]', $setting['forumpicstyle']['thumbnum'], 'text');

		$stamplist[] = array(0, '');
		foreach(C::t('common_smiley')->fetch_all_by_type('stamplist') as $smiley) {
			$stamplist[] = array($smiley['displayorder'], $smiley['code']);
		}
		showsetting('setting_styles_forumdisplay_newbie', array('settingnew[newbie]', $stamplist), $setting['newbie'], 'select');
		showsetting('setting_styles_forumdisplay_disfixednv_forumdisplay', 'settingnew[disfixednv_forumdisplay]', !empty($setting['disfixednv_forumdisplay']), 'radio');
		showsetting('setting_styles_forumdisplay_threadpreview', 'settingnew[forumdisplaythreadpreview]', !empty($setting['forumdisplaythreadpreview']), 'radio');
		showtablefooter();

		showtagheader('div', 'viewthread', $_GET['anchor'] == 'viewthread');
		showtableheader('nav_setting_viewthread', 'nobottom');
		showsetting('setting_styles_viewthread_ppp', 'settingnew[postperpage]', $setting['postperpage'], 'text');
		showsetting('setting_styles_viewthread_starthreshold', 'settingnew[starthreshold]', $setting['starthreshold'], 'text');
		showsetting('setting_styles_viewthread_maxsigrows', 'settingnew[maxsigrows]', $setting['maxsigrows'], 'text');
		showsetting('setting_styles_viewthread_sigviewcond', 'settingnew[sigviewcond]', $setting['sigviewcond'], 'text');
		showsetting('setting_styles_viewthread_sigimgclick_on', 'settingnew[sigimgclick]', $setting['sigimgclick'], 'radio');
		showsetting('setting_styles_viewthread_rate_on', 'settingnew[ratelogon]', $setting['ratelogon'], 'radio');
		showsetting('setting_styles_viewthread_rate_number', 'settingnew[ratelogrecord]', $setting['ratelogrecord'], 'text');
		showsetting('setting_styles_viewthread_collection_number', 'settingnew[collectionnum]', $setting['collectionnum'], 'text');
		showsetting('setting_styles_viewthread_relate_number', 'settingnew[relatenum]', $setting['relatenum'], 'text');
		showsetting('setting_styles_viewthread_relate_time', 'settingnew[relatetime]', $setting['relatetime'], 'text');
		showsetting('setting_styles_viewthread_show_signature', 'settingnew[showsignatures]', $setting['showsignatures'], 'radio');
		showsetting('setting_styles_viewthread_show_face', 'settingnew[showavatars]', $setting['showavatars'], 'radio');
		showsetting('setting_styles_viewthread_show_images', 'settingnew[showimages]', $setting['showimages'], 'radio');
		showsetting('setting_styles_viewthread_imagemaxwidth', 'settingnew[imagemaxwidth]', $setting['imagemaxwidth'], 'text');
		showsetting('setting_styles_viewthread_imagelistthumb', 'settingnew[imagelistthumb]', $setting['imagelistthumb'], 'text');
		showsetting('setting_styles_viewthread_zoomstatus', 'settingnew[zoomstatus]', $setting['zoomstatus'], 'radio', 0, 1);
		showsetting('setting_styles_viewthread_showexif', 'settingnew[showexif]', $setting['showexif'], 'radio', !function_exists('exif_read_data'));
		showtagfooter('tbody');
		showsetting('setting_styles_viewthread_vtonlinestatus', array('settingnew[vtonlinestatus]', array(
			array(0, $lang['setting_styles_viewthread_display_none']),
			array(1, $lang['setting_styles_viewthread_online_easy']),
			array(2, $lang['setting_styles_viewthread_online_exactitude'])
		)), $setting['vtonlinestatus'], 'select');
		showsetting('setting_styles_viewthread_userstatusby', 'settingnew[userstatusby]', $setting['userstatusby'], 'radio');
		showsetting('setting_styles_viewthread_postno', 'settingnew[postno]', $setting['postno'], 'text');
		showsetting('setting_styles_viewthread_postnocustom', 'settingnew[postnocustom]', $setting['postnocustom'], 'textarea');
		showsetting('setting_styles_viewthread_maxsmilies', 'settingnew[maxsmilies]', $setting['maxsmilies'], 'text');

		showsetting('setting_styles_viewthread_author_onleft', array('settingnew[authoronleft]', array(
			array(1, cplang('setting_styles_viewthread_author_onleft_yes')),
			array(0, cplang('setting_styles_viewthread_author_onleft_no')))), $setting['authoronleft'], 'mradio');

		showsetting('setting_styles_forumdisplay_disfixedavatar', 'settingnew[disfixedavatar]', !empty($setting['disfixedavatar']), 'radio');
		showsetting('setting_styles_forumdisplay_disfixednv_viewthread', 'settingnew[disfixednv_viewthread]', !empty($setting['disfixednv_viewthread']), 'radio');
		showsetting('setting_styles_forumdisplay_threadguestlite', 'settingnew[threadguestlite]', !empty($setting['threadguestlite']), 'radio');
		showsetting('setting_styles_viewthread_close_leftinfo', 'settingnew[close_leftinfo]', !empty($setting['close_leftinfo']), 'radio');
		showsetting('setting_styles_viewthread_close_leftinfo_userctrl', 'settingnew[close_leftinfo_userctrl]', !empty($setting['close_leftinfo_userctrl']), 'radio');
		showsetting('setting_styles_viewthread_guestviewthumb', 'settingnew[guestviewthumb][flag]', !empty($setting['guestviewthumb']['flag']), 'radio', 0, 1);
		showsetting('setting_styles_viewthread_guestviewthumb_width', 'settingnew[guestviewthumb][width]', $setting['guestviewthumb']['width'], 'text');
		showsetting('setting_styles_viewthread_guestviewthumb_height', 'settingnew[guestviewthumb][height]', $setting['guestviewthumb']['height'], 'text');
		showtagfooter('tbody');
		showsetting('setting_styles_viewthread_guesttipsinthread', 'settingnew[guesttipsinthread][flag]', !empty($setting['guesttipsinthread']['flag']), 'radio', 0, 1);
		showsetting('setting_styles_viewthread_guesttipsinthread_text', 'settingnew[guesttipsinthread][text]', $setting['guesttipsinthread']['text'], 'text');
		showtagfooter('tbody');
		showsetting('setting_styles_viewthread_imgcontent', 'settingnew[imgcontentwidth]', $setting['imgcontentwidth'], 'text');
		showsetting('setting_styles_viewthread_fast_reply', 'settingnew[allowfastreply]', $setting['allowfastreply'], 'radio');
		showsetting('setting_styles_viewthread_allow_replybg', 'settingnew[allowreplybg]', $setting['allowreplybg'], 'radio', 0, 1);
		$replybghtml = '';
		if($setting['globalreplybg']) {
			$replybghtml = '<label><input type="checkbox" class="checkbox" name="delglobalreplybg" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$_G['setting']['attachurl'].'common/'.$setting['globalreplybg'].'" width="200px" />';
		}
		if($setting['globalreplybg']) {
			$replybgurl = parse_url($setting['globalreplybg']);
		}
		showsetting('setting_styles_viewthread_global_reply_background', 'globalreplybg', (!$replybgurl['host'] ? str_replace($_G['setting']['attachurl'].'common/', '', $setting['globalreplybg']) : $setting['globalreplybg']), 'filetext', '', 0, $replybghtml);
		showtablefooter();
		showtagfooter('div');

		$setting['msgforward'] = !empty($setting['msgforward']) ? dunserialize($setting['msgforward']) : array();
		$setting['msgforward']['messages'] = !empty($setting['msgforward']['messages']) ? implode("\n", $setting['msgforward']['messages']) : '';
		showtablefooter();

		loadcache('usergroups');
		$threadprofiles = C::t('forum_threadprofile')->fetch_all();
		$threadprofile_group = C::t('forum_threadprofile_group')->fetch_all();
		showtagheader('div', 'threadprofile', $_GET['anchor'] == 'threadprofile');

		echo '<table><tr><td valign="top" width="350">';

		showtableheader('setting_styles_threadprofile_group', 'nobottom');
		showsubtitle(array('setting_styles_threadprofile_name', 'setting_styles_threadprofile_plan'));
		foreach($_G['cache']['usergroups'] as $gid => $usergroup) {
			$select = '<select name="threadprofile['.$gid.']"><option value="0">'.$lang['nav_global'].'</option>';
			foreach($threadprofiles as $id => $threadprofile) {
				$select .= '<option value="'.$id.'"'.($threadprofile_group[$gid]['tpid'] == $id ? ' selected' : '').'>'.$threadprofile['name'].'</option>';
			}
			$select .= '</select>';
			showtablerow('', array('', ''), array($usergroup['grouptitle'], $select));
		}
		if($_G['setting']['verify']['enabled']) {
			foreach($_G['setting']['verify'] as $gid => $verify) {
				if($verify['available']) {
					$select = '<select name="threadprofile[-'.$gid.']"><option value="0">'.$lang['nav_global'].'</option>';
					foreach($threadprofiles as $id => $threadprofile) {
						$select .= '<option value="'.$id.'"'.($threadprofile_group[-$gid]['tpid'] == $id ? ' selected' : '').'>'.$threadprofile['name'].'</option>';
					}
					$select .= '</select>';
					showtablerow('', array('', ''), array($verify['title'], $select));
				}
			}
		}
		showtablefooter();

		echo '</td><td width="10"></td><td valign="top" width="350">';

		showtableheader('setting_styles_threadprofile_project', 'nobottom');
		$setting['threadprofile'] = !empty($setting['threadprofile']) ? dunserialize($setting['threadprofile']) : array();
		showsubtitle(array('setting_styles_threadprofile_name', 'nav_global', ''));
		foreach($threadprofiles as $id => $threadprofile) {
			showtablerow('', array('style="width:200px"', 'style="width:50px"', ''), array(
				$threadprofile['name'],
				'<input name="default" type="radio" value="'.$id.'"'.($threadprofile['global'] ? ' checked' : '').' />',
				'<a href="'.ADMINSCRIPT.'?action=setting&operation=threadprofile&do=edit&id='.$id.'">'.cplang('edit').'</a>'.
				($id > 1 ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=setting&operation=threadprofile&do=delete&id='.$id.'">'.cplang('delete').'</a>' : ''),
			));
		}
		echo '<tr><td colspan="3"><a href="'.ADMINSCRIPT.'?action=setting&operation=threadprofile&do=add" class="addtr">'.$lang['setting_styles_threadprofile_addplan'].'</td></tr>';
		showtablefooter();

		echo '</td></tr></table>';

		showtagfooter('div');

		showtips('members_profile_numbercard_tips', 'numbercard_tips', $_GET['anchor'] == 'numbercard');
		showtableheader('members_profile_numbercard', 'nobottom', 'id="numbercard"'.($_GET['anchor'] != 'numbercard' ? ' style="display: none"' : ''));
		$settingsAttribute = array();
		$allowedAttribute = array('threads', 'posts', 'credits', 'digestposts', 'doings', 'blogs', 'albums', 'sharings', 'oltime', 'feeds', 'follower', 'following', 'friends');
		foreach($allowedAttribute as $attribute) {
			$settingsAttribute[] = array($attribute, $lang['setting_numbercard_type_'.$attribute]);
		}
		$extcredits = dunserialize($setting['extcredits']);
		foreach($extcredits as $creditid=>$extcredit) {
			if($extcredit['title']) {
				$settingsAttribute[] = array('extcredits'.$creditid, $extcredit['title']);
			}
		}

		$setting['numbercard'] = dunserialize($setting['numbercard']);

		for($i = 1; $i <= 3; $i++) {
			showsetting(cplang('setting_numbercard_row', array('i' => $i)), array('settingnew[numbercard][row]['.$i.']', $settingsAttribute), $setting['numbercard']['row'][$i], 'select');
		}
		showtablefooter();

		showtableheader('setting_styles_refresh', 'nobottom', 'id="refresh"'.($_GET['anchor'] != 'refresh' ? ' style="display: none"' : ''));
		showsetting('setting_styles_refresh_refreshtime', 'settingnew[msgforward][refreshtime]', $setting['msgforward']['refreshtime'], 'text');
		showsetting('setting_styles_refresh_quick', 'settingnew[msgforward][quick]', $setting['msgforward']['quick'], 'radio', '', 1);
		showsetting('setting_styles_refresh_messages', 'settingnew[msgforward][messages]', $setting['msgforward']['messages'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('setting_styles_sitemessage', 'nobottom', 'id="sitemessage"'.($_GET['anchor'] != 'sitemessage' ? ' style="display: none"' : ''));
		showsetting('setting_styles_sitemessage_time', 'settingnew[sitemessage][time]', $setting['sitemessage']['time'], 'text');
		showsetting('setting_styles_sitemessage_register', 'settingnew[sitemessage][register]', $setting['sitemessage']['register'], 'textarea');
		showsetting('setting_styles_sitemessage_login', 'settingnew[sitemessage][login]', $setting['sitemessage']['login'], 'textarea');
		showsetting('setting_styles_sitemessage_newthread', 'settingnew[sitemessage][newthread]', $setting['sitemessage']['newthread'], 'textarea');
		showsetting('setting_styles_sitemessage_reply', 'settingnew[sitemessage][reply]', $setting['sitemessage']['reply'], 'textarea');
		showtagfooter('tbody');
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'threadprofile') {

		$authorinfoitems = array();
		$authorinfoitems = array(
			'{numbercard}' => $lang['setting_styles_threadprofile_attrcard'],
			'{groupicon}<p>{*}</p>{/groupicon}' => $lang['setting_styles_threadprofile_groupicon'],
			'{authortitle}<p><em>{*}</em></p>{/authortitle}' => $lang['setting_styles_threadprofile_groupname'],
			'{customstatus}<p class=xg1>{*}</p>{/customstatus}' => $lang['members_edit_nickname'],
			'{star}<p>{*}</p>{/star}' => $lang['group_level_icon'],
			'{upgradeprogress}' => $lang['setting_styles_threadprofile_groupstep'],
		);
		if(!empty($_G['setting']['extcredits'])) {
			foreach($_G['setting']['extcredits'] as $key => $value) {
				$authorinfoitems['extcredits'.$key] = $value['title'];
			}
		}
		$authorinfoitems = array_merge($authorinfoitems, array(
			1 => '-',
			'uid' => 'UID',
			'friends' => $lang['setting_styles_viewthread_userinfo_friends'],
			'doings' => $lang['setting_styles_viewthread_userinfo_doings'],
			'blogs' => $lang['setting_styles_viewthread_userinfo_blogs'],
			'albums' => $lang['setting_styles_viewthread_userinfo_albums'],
			'posts' => $lang['setting_styles_viewthread_userinfo_posts'],
			'threads' => $lang['setting_styles_viewthread_userinfo_threads'],
			'sharings' => $lang['setting_styles_viewthread_userinfo_sharings'],
			'digest' => $lang['setting_styles_viewthread_userinfo_digest'],
			'credits' => $lang['setting_styles_viewthread_userinfo_credits'],
			'readperm' => $lang['setting_styles_viewthread_userinfo_readperm'],
			'regtime' => $lang['setting_styles_viewthread_userinfo_regtime'],
			'lastdate' => $lang['setting_styles_viewthread_userinfo_lastdate'],
			'oltime' => $lang['setting_styles_viewthread_userinfo_oltime'],
			'eccredit_seller' => $lang['setting_styles_threadprofile_eccredit_seller'],
			'eccredit_buyer' => $lang['setting_styles_threadprofile_eccredit_buyer'],
			'follower' => $lang['setting_styles_viewthread_userinfo_follower'],
			'following' => $lang['setting_styles_viewthread_userinfo_following']
		));
		foreach(C::t('common_member_profile_setting')->fetch_all_by_available(1) as $profilefields) {
			if($profilefields['fieldid'] == 'birthyear' || $profilefields['fieldid'] == 'birthmonth') {
				continue;
			} elseif($profilefields['fieldid'] == 'realname') {
				$setting['verify'] = dunserialize($setting['verify']);
				if($setting['verify'][6]['available'] && !$setting['verify'][6]['viewrealname']) {
					continue;
				}
			}
			$authorinfoitems['field_'.$profilefields['fieldid']] = $profilefields['title'];
		}

		if($_G['setting']['hookscript']['global']['profile']['funcs']['profile_node']) {
			$pluginidentifiers = array();
			foreach($_G['setting']['hookscript']['global']['profile']['funcs']['profile_node'] as $plugin) {
				$pluginidentifiers[] = $plugin[0];
			}
			$plugins = C::t('common_plugin')->fetch_all_identifier($pluginidentifiers);
			foreach($plugins as $id => $value) {
				$authorinfoitems['{plugin:'.$id.'}'] = $value['name'];
			}
		}

		if($_GET['do'] == 'add') {
			showtips('setting_threadprofile_tpl_tpls');
			showtableheader('');
			showhiddenfields(array('do' => 'add'));
			showsetting('setting_styles_threadprofile_name', 'namenew', '', 'text');
			showsetting_threadprfile($authorinfoitems);
			showtagfooter('tbody');
			showtablefooter();
		} elseif($_GET['do'] == 'edit') {
			$id = intval($_GET['id']);
			$threadprofile = C::t('forum_threadprofile')->fetch($id);
			if(!$threadprofile) {
				dheader('location: '.ADMINSCRIPT.'?action=setting&operation=styles&anchor=threadprofile');
			}
			showtips('setting_threadprofile_tpl_tpls');
			showtableheader('');
			showhiddenfields(array('do' => 'edit', 'id' => $id));
			$threadprofile['template'] = dunserialize($threadprofile['template']);
			showsetting('setting_styles_threadprofile_name', 'namenew', $threadprofile['name'], 'text');
			showsetting_threadprfile($authorinfoitems, $threadprofile['template']);
			showtagfooter('tbody');
			showtablefooter();
		} elseif($_GET['do'] == 'delete') {
			$id = intval($_GET['id']);
			C::t('forum_threadprofile')->delete($_GET['id']);
			C::t('forum_threadprofile_group')->delete_by_tpid($_GET['id']);
			updatecache('setting');
			cpmsg('setting_update_succeed', 'action=setting&operation=styles&anchor=threadprofile', 'succeed');
		}

	} elseif($operation == 'seo') {

		$setting['seotitle'] = dunserialize($setting['seotitle']);
		$setting['seodescription'] = dunserialize($setting['seodescription']);
		$setting['seokeywords'] = dunserialize($setting['seokeywords']);

		$rewritedata = rewritedata();
		$setting['rewritestatus'] = isset($setting['rewritestatus']) ? dunserialize($setting['rewritestatus']) : '';
		$setting['rewriterule'] = isset($setting['rewriterule']) ? dunserialize($setting['rewriterule']) : '';
		echo '<div id="rewrite"'.($_GET['anchor'] != 'rewrite' ? ' style="display: none"' : '').'>';
			showtips('setting_tips', 'tips_rewrite');
			showtableheader('', 'nobottom');
			showtitle('<em class="right">'.cplang('setting_seo_rewritestatus_viewrule').'</em>'.cplang('setting_seo_rewritestatus'));
			showtablerow('', array('class="vtop tips2" colspan="3"'), array(cplang('setting_seo_rewritestatus_comment')));
			showsubtitle(array('setting_seo_pages', 'setting_seo_vars', 'setting_seo_rule', 'available'));
			foreach($rewritedata['rulesearch'] as $k => $v) {
				$v = !$setting['rewriterule'][$k] ? $v : $setting['rewriterule'][$k];
				showtablerow('', array('class="td24"', 'class="td31"', 'class="longtxt"', 'class="td25"'), array(
					cplang('setting_seo_rewritestatus_'.$k),
					implode(', ', array_keys($rewritedata['rulevars'][$k])),
					'<input onclick="doane(event)" name="settingnew[rewriterule]['.$k.']" class="txt" value="'.dhtmlspecialchars($v).'"/>',
					'<input type="checkbox" name="settingnew[rewritestatus][]" class="checkbox" value="'.$k.'" '.(in_array($k, $setting['rewritestatus']) ? 'checked="checked"' : '').'/>'
				));
			}
			showtablefooter();
			showtableheader();
			showsetting('setting_seo_rewritecompatible', 'settingnew[rewritecompatible]', $setting['rewritecompatible'], 'radio');
			showsetting('setting_seo_rewriteguest', 'settingnew[rewriteguest]', $setting['rewriteguest'], 'radio');
			showtablefooter();
		echo '</div>';

		echo '<div id="other"'.($_GET['anchor'] != 'other' ? ' style="display: none"' : '').'>';
			showtableheader();
			showtitle('<em class="right">'.cplang('setting_seo_robots_output').'</em>'.cplang('setting_seo'));
			showtablerow('', array('class="vtop tips2" colspan="4" style="padding-left:20px;"'), array('<ul><li>'.cplang('setting_seo_seotitle_comment').'</li><li>'.cplang('setting_seo_seodescription_comment').'</li><li>'.cplang('setting_seo_seokeywords_comment').'</li></ul>'));

			if($_G['setting']['navs'][5]['navname']) {
				showtitle($_G['setting']['navs'][5]['navname']);
				showtablerow('', array('width="80"', ''), array(
						cplang('setting_seo_seotitle'),
						'<input type="text" name="settingnew[seotitle][userapp]" value="'.$setting['seotitle']['userapp'].'" class="txt" style="width:280px;" />',
					)
				);
				showtablerow('', array('width="80"', ''), array(
						cplang('setting_seo_seokeywords'),
						'<input type="text" name="settingnew[seokeywords][userapp]" value="'.$setting['seokeywords']['userapp'].'" class="txt" style="width:280px;" />'
					)
				);
				showtablerow('', array('width="80"', ''), array(
						cplang('setting_seo_seodescription'),
						'<input type="text" name="settingnew[seodescription][userapp]" value="'.$setting['seodescription']['userapp'].'" class="txt" style="width:280px;" />',
					)
				);
			}

			showtablefooter();
			showtableheader();
			showsetting('setting_seo_seohead', 'settingnew[seohead]', $setting['seohead'], 'textarea');
			showtablefooter();
		echo '</div>';
		$seotypes = array(
			'portal' => array('portal', 'articlelist', 'article'),
			'forum' => array('forum', 'threadlist', 'viewthread'),
			'home' => array('home', 'blog', 'album'),
			'group' => array('group', 'grouppage', 'viewthread_group')
		);
		$codetypes = array(
			'portal' => 'bbname',
			'articlelist' => 'bbname,curcat,firstcat,secondcat,page',
			'article' => 'bbname,curcat,firstcat,secondcat,subject,summary,user,page',
			'forum' => 'bbname',
			'threadlist' => 'bbname,forum,fup,fgroup,page',
			'viewthread' => 'bbname,forum,fup,fgroup,subject,summary,tags,page',
			'home' => 'bbname',
			'blog' => 'bbname,subject,summary,tags,user',
			'album' => 'bbname,album,depict,user',
			'group' => 'bbname,forum,first,second',
			'grouppage' => 'bbname,forum,first,second,gdes,page',
			'viewthread_group' => 'bbname,forum,first,second,gdes,subject,summary,tags,page',
		);
		foreach($codetypes as $key => $val) {
			$jscodetypes .= "codetypes['{$key}'] = '{$val}';\r\n";
			foreach(explode(',', $val) as $code) {
				$cname = $code == 'bbname' ? cplang('setting_seo_code_bbname') : cplang('setting_seo_code_'.$key.'_'.$code);
				$jscodenames .= "codenames['{$key}_{$code}'] = '{$cname}';\r\n";
			}
		}
		print <<<EOF
		<div id="codediv" style="display:none; top: 707px;background: url('./static/image/common/mdly.png') no-repeat scroll 0 0 transparent; height: 100px; line-height: 32px; margin-top: -16px; overflow: hidden; padding: 10px 25px; position: absolute; left: 500px; width: 250px;">
		<p>
EOF;
		echo cplang('setting_seo_insallowcode');
		print <<<EOF
		</p>
		<p id="seocodes">
		<a onclick="insertcode('subject');return false;" href="javascript:;">{subject}</a>
		<span class="pipe">|</span>
		<a onclick="insertcode('forum');return false;" href="javascript:;">{forum}</a>
		</p>
		</div>
		<script src="static/js/home.js" type="text/javascript"></script>
		<script language="javascript">
		var codediv = $('codediv');
		var codetypes = new Array(), codenames = new Array();
		$jscodetypes
		$jscodenames
		function getcodetext(obj, ctype) {
			var top_offset = obj.offsetTop;
			var codecontent = '';
			var targetid = obj.id;
			while((obj = obj.offsetParent).tagName != 'BODY') {
				top_offset += obj.offsetTop;
			}
			if(!codetypes[ctype]) {
				return true;
			}
			types = codetypes[ctype].split(',');
			for(var i = 0; i < types.length; i++) {
				if(codecontent != '') {
					codecontent += '&nbsp;&nbsp;';
				}
				codecontent += '<a onclick="insertContent(\''+targetid+'\', \'{'+types[i]+'}\');return false;" href="javascript:;" title="'+codenames[ctype+'_'+types[i]]+'">{'+types[i]+'}</a>';
			}
			$('seocodes').innerHTML = codecontent;
			codediv.style.top = top_offset + 'px';
			codediv.style.display = '';
			_attachEvent($('submenu'), 'mouseover', function(){codediv.style.display='none';});
		}
		</script>
EOF;
		$first = $seconds = $thirds = $afirst = $aseconds = $athirds = array();
		$query = C::t('forum_forum')->fetch_all_forum_for_sub_order();
		foreach($query as $forum) {
			$forum['description'] = $forum['seodescription'];
			$forum['id'] = $forum['fid'];
			if($forum['type'] == 'group') {
				$first[$forum['fid']] = $forum;
			} elseif($forum['type'] == 'sub') {
				$thirds[$forum['fup']][] = $forum;
			} else {
				$seconds[$forum['fup']][] = $forum;
			}
		}
		loadcache('portalcategory');
		$portalcategory = $_G['cache']['portalcategory'];
		if($portalcategory) {
			foreach($portalcategory as $category) {
				$category['id'] = $category['catid'];
				$category['name'] = $category['catname'];
				$category['keywords'] = $category['keyword'];
				if($category['level'] == 0) {
					$afirst[$category['catid']] = $category;
				} elseif($category['level'] == 1) {
					$aseconds[$category['upid']][] = $category;
				} else {
					$athirds[$category['upid']][] = $category;
				}
			}
		}
		foreach($seotypes as $type => $subtypes) {
			echo '<div id="'.$type.'"'.($_GET['anchor'] != $type ? ' style="display: none"' : '').'>';
			showtips(cplang('setting_seo_global_tips').cplang('setting_seo_'.$type.'_tips'), 'tips_'.$type);
			showtableheader();
			foreach($subtypes as $subtype) {
				showtitle(cplang('setting_seo_'.$subtype).($subtype == 'threadlist' || $subtype == 'articlelist' ? ' &nbsp; <a href="javascript:;" class="act" onclick="if($(\''.$subtype.'_detail\').style.display){$(\''.$subtype.'_detail\').style.display=\'\';this.innerHTML=\''.cplang('setting_seo_closedetail').'\';}else{$(\''.$subtype.'_detail\').style.display=\'none\';this.innerHTML=\''.cplang('setting_seo_opendetail').'\';};return false;">'.cplang('setting_seo_opendetail').'</a>' : ''));
				showtablerow('', array('width="12%"', ''), array(
						cplang('setting_seo_seotitle'),
						'<input type="text" id="t_'.$type.$subtype.'" onfocus="getcodetext(this, \''.$subtype.'\');" name="settingnew[seotitle]['.$subtype.']" value="'.dhtmlspecialchars($setting['seotitle'][$subtype]).'" class="txt" style="width:280px;" />',
					)
				);
				showtablerow('', array('width="12%"', ''), array(
						cplang('setting_seo_seokeywords'),
						'<input type="text" id="k_'.$type.$subtype.'" onfocus="getcodetext(this, \''.$subtype.'\');" name="settingnew[seokeywords]['.$subtype.']" value="'.dhtmlspecialchars($setting['seokeywords'][$subtype]).'" class="txt" style="width:280px;" />'
					)
				);
				showtablerow('', array('width="12%"', ''), array(
						cplang('setting_seo_seodescription'),
						'<input type="text" id="d_'.$type.$subtype.'" onfocus="getcodetext(this, \''.$subtype.'\');" name="settingnew[seodescription]['.$subtype.']" value="'.dhtmlspecialchars($setting['seodescription'][$subtype]).'" class="txt" style="width:280px;" />',
					)
				);
				if($subtype == 'threadlist') {
					showlist($first, $seconds, $thirds, $subtype);
				}
				if($subtype == 'articlelist') {
					showlist($afirst, $aseconds, $athirds, $subtype);
				}
			}
			showtablefooter();
			echo '</div>';
		}
		showtagfooter('tbody');
	} elseif($operation == 'cachethread') {

		include_once libfile('function/forumlist');
		$forumselect = '<select name="fids[]" multiple="multiple" size="10"><option value="all">'.$lang['all'].'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		showtableheader();
		showtitle('setting_cachethread');
		showsetting('setting_cachethread_indexlife', 'settingnew[cacheindexlife]', $setting['cacheindexlife'], 'text');
		showsetting('setting_cachethread_life', 'settingnew[cachethreadlife]', $setting['cachethreadlife'], 'text');
		showsetting('setting_cachethread_dir', 'settingnew[cachethreaddir]', $setting['cachethreaddir'], 'text');

		showtitle('setting_cachethread_coefficient_set');
		showsetting('setting_cachethread_coefficient', 'settingnew[threadcaches]', '', "<input type=\"text\" class=\"txt\" size=\"30\" name=\"settingnew[threadcaches]\" value=\"$setting[threadcaches]\">");
		showsetting('setting_cachethread_coefficient_forum', '', '', $forumselect);

	} elseif($operation == 'serveropti') {

		$checkgzipfunc = !function_exists('ob_gzhandler') ? 1 : 0;
		if($setting['jspath'] == 'static/js/') {
			$tjspath['default'] = 'checked="checked"';
			$setting['jspath'] = '';
		} elseif($setting['jspath'] == 'data/cache/') {
			$tjspath['cache'] =  'checked="checked"';
			$setting['jspath'] = '';
		} else {
			$tjspath['custom'] =  'checked="checked"';
		}

		if(!$setting['csspathv'] || $setting['csspathv'] == 'data/cache/') {
			$tcsspath['cache'] =  'checked="checked"';
			$setting['csspathv'] = '';
		} else {
			$tcsspath['custom'] =  'checked="checked"';
		}

		showtips('setting_tips');
		showtableheader();
		showtitle('setting_serveropti');
		showsetting('setting_serveropti_optimize_thread_view', 'settingnew[optimizeviews]', $setting['optimizeviews'], 'radio');
		showsetting('setting_serveropti_preventrefresh', 'settingnew[preventrefresh]', $setting['preventrefresh'], 'radio');
		showsetting('setting_serveropti_delayviewcount', 'settingnew[delayviewcount]', $setting['delayviewcount'], 'radio');
		showsetting('setting_serveropti_nocacheheaders', 'settingnew[nocacheheaders]', $setting['nocacheheaders'], 'radio');
		showsetting('setting_serveropti_maxonlines', 'settingnew[maxonlines]', $setting['maxonlines'], 'text');
		showsetting('setting_serveropti_onlinehold', 'settingnew[onlinehold]', $setting['onlinehold'], 'text');
		showsetting('setting_serveropti_jspath', '', '', '<ul class="nofloat" onmouseover="altStyle(this);">
			<li'.($tjspath['default'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="static/js/" '.$tjspath['default'].'> '.$lang['setting_serveropti_jspath_default'].'</li>
			<li'.($tjspath['cache'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="data/cache/" '.$tjspath['cache'].'> '.$lang['setting_serveropti_jspath_cache'].'</li>
			<li'.($tjspath['custom'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[jspath]" value="" '.$tjspath['custom'].'> '.$lang['setting_serveropti_jspath_custom'].' <input type="text" class="txt" style="width: 100px" name="settingnew[jspathcustom]" value="'.$setting['jspath'].'" size="6"></li></ul>'
		);
		showsetting('setting_serveropti_csspath', '', '', '<ul class="nofloat" onmouseover="altStyle(this);">
			<li'.($tcsspath['cache'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[csspathv]" value="data/cache/" '.$tcsspath['cache'].'> '.$lang['setting_serveropti_csspath_cache'].'</li>
			<li'.($tcsspath['custom'] ? ' class="checked"' : '').'><input class="radio" type="radio" name="settingnew[csspathv]" value="" '.$tcsspath['custom'].'> '.$lang['setting_serveropti_csspath_custom'].' <input type="text" class="txt" style="width: 100px" name="settingnew[csspathcustom]" value="'.$setting['csspathv'].'" size="6"></li></ul>'
		);
		showsetting('setting_serveropti_lazyload', 'settingnew[lazyload]', $setting['lazyload'], 'radio');
		showsetting('setting_serveropti_blockmaxaggregationitem', 'settingnew[blockmaxaggregationitem]', $setting['blockmaxaggregationitem'], 'text');
		$setting['blockcachetimerange'] = empty($setting['blockcachetimerange']) ? array('0', '23') : explode(',', $setting['blockcachetimerange']);
		$blockcachetimerange = range(0, 23);
		$point = $lang['setting_serveropti_blockcachetimerangepoint'];
		$html = '<select name="settingnew[blockcachetimerange][0]" class="ps" style="width:60px;" >';
		foreach($blockcachetimerange as $value) {
			$html .= '<option value="'.$value.'"'.($value == $setting['blockcachetimerange'][0] ? ' selected="selected"' : '').'>'.$value.$point.'</option>';
		}
		$html .= '</select>- &nbsp;<select name="settingnew[blockcachetimerange][1]" class="ps" style="width:60px;" >';
		foreach($blockcachetimerange as $value) {
			$html .= '<option value="'.$value.'"'.($value == $setting['blockcachetimerange'][1] ? ' selected="selected"' : '').'>'.$value.$point.'</option>';
		}
		$html .= '</select>';
		showsetting('setting_serveropti_blockcachetimerange', '', '', $html);
		showsetting('setting_serveropti_sessionclose', 'settingnew[sessionclose]', $setting['sessionclose'], 'radio', '', 1);
		showsetting('setting_serveropti_onlineguestsmultiple', 'settingnew[onlineguestsmultiple]', $setting['onlineguestsmultiple'] ? $setting['onlineguestsmultiple'] : 10, 'text');
		showtagheader('tbody', '', true);

	} elseif($operation == 'editor') {

		$_G['setting']['editoroptions'] = str_pad(decbin($setting['editoroptions']), 3, 0, STR_PAD_LEFT);
		$setting['defaulteditormode'] = $_G['setting']['editoroptions'][0];
		$setting['allowswitcheditor'] = $_G['setting']['editoroptions'][1];
		$setting['simplemode'] = $_G['setting']['editoroptions'][2];

		showtableheader();
		showsetting('setting_editor_mode_default', array('settingnew[defaulteditormode]', array(
			array(0, $lang['setting_editor_mode_discuzcode']),
			array(1, $lang['setting_editor_mode_wysiwyg']))), $setting['defaulteditormode'], 'mradio');
		showsetting('setting_editor_swtich_enable', 'settingnew[allowswitcheditor]', $setting['allowswitcheditor'], 'radio');
		showsetting('setting_editor_simplemode', array('settingnew[simplemode]', array(
			array(1, $lang['setting_editor_simplemode_1']),
			array(0, $lang['setting_editor_simplemode_0'])), 1),$setting['simplemode'], 'mradio');
		showsetting('setting_editor_smthumb', 'settingnew[smthumb]', $setting['smthumb'], 'text');
		showsetting('setting_editor_smcols', 'settingnew[smcols]', $setting['smcols'], 'text');
		showsetting('setting_editor_smrows', 'settingnew[smrows]', $setting['smrows'], 'text');
		showtablefooter();

	} elseif($operation == 'functions') {
		$allowfuntype = array('portal', 'group', 'follow', 'collection', 'guide', 'feed', 'blog', 'doing', 'album', 'share', 'wall', 'homepage', 'ranklist');
		$_GET['type'] = in_array($_GET['type'], $allowfuntype) ? trim($_GET['type']) : '';
		echo "<script>disallowfloat = '{$_G[setting][disallowfloat]}';</script>";

		showtableheader('setting_functions_curscript_list', 'nobottom', 'id="curscript"'.($_GET['anchor'] != 'curscript' ? ' style="display: none"' : ''));
		$modulehtml = array();
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/portal_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_portal'].'</td><td width="370">'.$lang['setting_functions_curscript_portal_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['portalstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['portalstatus'] ? 'close':'open').'&type=portal" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['portalstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/group_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_group'].'</td><td width="370">'.$lang['setting_functions_curscript_group_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['groupstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['groupstatus'] ? 'close':'open').'&type=group" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['groupstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/follow_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_follow'].'</td><td width="370">'.$lang['setting_functions_curscript_follow_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['followstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['followstatus'] ? 'close':'open').'&type=follow" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['followstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/collection_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_collection'].'</td><td width="370">'.$lang['setting_functions_curscript_collection_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['collectionstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['collectionstatus'] ? 'close':'open').'&type=collection" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['collectionstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/guide_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_guide'].'</td><td width="370">'.$lang['setting_functions_curscript_guide_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['guidestatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['guidestatus'] ? 'close':'open').'&type=guide" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['guidestatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/feed_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_feed'].'</td><td width="370">'.$lang['setting_functions_curscript_feed_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['feedstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['feedstatus'] ? 'close':'open').'&type=feed" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['feedstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/blog_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_blog'].'</td><td width="370">'.$lang['setting_functions_curscript_blog_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['blogstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['blogstatus'] ? 'close':'open').'&type=blog" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['blogstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/album_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_album'].'</td><td width="370">'.$lang['setting_functions_curscript_album_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['albumstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['albumstatus'] ? 'close':'open').'&type=album" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['albumstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/share_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_share'].'</td><td width="370">'.$lang['setting_functions_curscript_share_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['sharestatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['sharestatus'] ? 'close':'open').'&type=share" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['sharestatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/doing_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_doing'].'</td><td width="370">'.$lang['setting_functions_curscript_doing_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['doingstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['doingstatus'] ? 'close':'open').'&type=doing" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['doingstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/wall_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_message'].'</td><td width="370">'.$lang['setting_functions_curscript_message_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['wallstatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['wallstatus'] ? 'close':'open').'&type=wall" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['wallstatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		$modulehtml[] = '<td class="td25"><img src="'.STATICURL.'image/feed/ranklist_b.png"/></td><td class="td23">'.$lang['setting_functions_curscript_ranklist'].'</td><td width="370">'.$lang['setting_functions_curscript_ranklist_intro'].'</td><td class="td30"><img class="vm" src="'.$_G['style']['imgdir'].'/data_'.($setting['rankliststatus'] ? 'valid':'invalid').'.gif"></td><td><a href="forum.php?mod=ajax&action=setnav&do='.($setting['rankliststatus'] ? 'close':'open').'&type=ranklist" onclick="showWindow(\'setnav\', this.href, \'get\', 0);return false;">'.($setting['rankliststatus'] ? $lang['setting_functions_curscript_close']:$lang['setting_functions_curscript_open']).'</a></td>';
		echo '<tr>'.implode('</tr><tr>', $modulehtml).'</tr>';
		showtablefooter();

		showtips('setting_tips', 'mod_tips', $_GET['anchor'] == 'mod');
		showtableheader('', 'nobottom', 'id="mod"'.($_GET['anchor'] != 'mod' ? ' style="display: none"' : ''));
		showsetting('setting_functions_mod_updatestat', 'settingnew[updatestat]', $setting['updatestat'], 'radio');
		showsetting('setting_functions_mod_status', 'settingnew[modworkstatus]', $setting['modworkstatus'], 'radio');
		showsetting('setting_functions_archiver', 'settingnew[archiver]', $setting['archiver'], 'radio', 0, 1);
		showsetting('setting_functions_archiverredirect', 'settingnew[archiverredirect]', $setting['archiverredirect'], 'radio');
		showtagfooter('tbody');
		showsetting('setting_functions_mod_maxmodworksmonths', 'settingnew[maxmodworksmonths]', $setting['maxmodworksmonths'], 'text');
		showsetting('setting_functions_mod_losslessdel', 'settingnew[losslessdel]', $setting['losslessdel'], 'text');
		showsetting('setting_functions_mod_reasons', 'settingnew[modreasons]', $setting['modreasons'], 'textarea');
		showsetting('setting_functions_mod_reasons_public', 'settingnew[modreasons_public]', $setting['modreasons_public'], 'radio');
		showsetting('setting_functions_user_reasons', 'settingnew[userreasons]', $setting['userreasons'], 'textarea');
		showsetting('setting_functions_mod_bannedmessages', array('settingnew[bannedmessages]', array(
			$lang['setting_functions_mod_bannedmessages_thread'],
			$lang['setting_functions_mod_bannedmessages_avatar'],
			$lang['setting_functions_mod_bannedmessages_signature'])), $setting['bannedmessages'], 'binmcheckbox');
		showsetting('setting_functions_mod_warninglimit', 'settingnew[warninglimit]', $setting['warninglimit'], 'text');
		showsetting('setting_functions_mod_warningexpiration', 'settingnew[warningexpiration]', $setting['warningexpiration'], 'text');
		showsetting('setting_functions_mod_rewardexpiration', 'settingnew[rewardexpiration]', $setting['rewardexpiration'], 'text');
		showsetting('setting_functions_mod_moddetail', 'settingnew[moddetail]', $setting['moddetail'], 'radio');
		showtablefooter();

		$setting['heatthread'] = dunserialize($setting['heatthread']);
		$setting['recommendthread'] = dunserialize($setting['recommendthread']);
		$setting['allowpostcomment'] = dunserialize($setting['allowpostcomment']);
		$count = count(explode(',', $setting['heatthread']['iconlevels']));
		$heatthreadicons = '';
		for($i = 0;$i < $count;$i++) {
			$heatthreadicons .= '<img src="static/image/common/hot_'.($i + 1).'.gif" /> ';
		}
		$count = count(explode(',', $setting['recommendthread']['iconlevels']));
		$recommendicons = '';
		for($i = 0;$i < $count;$i++) {
			$recommendicons .= '<img src="static/image/common/recommend_'.($i + 1).'.gif" /> ';
		}

		$setting['commentitem'] = explode("\t", $setting['commentitem']);
		foreach($setting['commentitem'] as $k => $v) {
			$tmp = explode(chr(0).chr(0).chr(0), $v);
			if(count($tmp) > 1) {
				$setting['commentitem'][$tmp[0]] = $tmp[1];
			}
		}

		showtips('setting_functions_heatthread_tips', 'heatthread_tips', $_GET['anchor'] == 'heatthread');
		showtableheader('', 'nobottom', 'id="heatthread"'.($_GET['anchor'] != 'heatthread' ? ' style="display: none"' : ''));
		showsetting('setting_functions_heatthread_period', 'settingnew[heatthread][period]', $setting['heatthread']['period'], 'text');
		showsetting('setting_functions_heatthread_iconlevels', '', '', '<input name="settingnew[heatthread][iconlevels]" class="txt" type="text" value="'.$setting['heatthread']['iconlevels'].'" /><br />'.$heatthreadicons);
		showtablefooter();

		showtips('setting_functions_recommend_tips', 'recommend_tips', $_GET['anchor'] == 'recommend');
		showtableheader('', 'nobottom', 'id="recommend"'.($_GET['anchor'] != 'recommend' ? ' style="display: none"' : ''));
		showsetting('setting_functions_recommend_status', 'settingnew[recommendthread][status]', $setting['recommendthread']['status'], 'radio', 0, 1);
		showsetting('setting_functions_recommend_addtext', 'settingnew[recommendthread][addtext]', $setting['recommendthread']['addtext'], 'text');
		showsetting('setting_functions_recommend_subtracttext', 'settingnew[recommendthread][subtracttext]', $setting['recommendthread']['subtracttext'], 'text');
		showsetting('setting_functions_recommend_daycount', 'settingnew[recommendthread][daycount]', intval($setting['recommendthread']['daycount']), 'text');
		showsetting('setting_functions_recommend_ownthread', 'settingnew[recommendthread][ownthread]', $setting['recommendthread']['ownthread'], 'radio');
		showsetting('setting_functions_recommend_iconlevels', '', '', '<input name="settingnew[recommendthread][iconlevels]" class="txt" type="text" value="'.$setting['recommendthread']['iconlevels'].'" /><br />'.$recommendicons);
		showtablefooter();

		showtableheader('', 'nobottom', 'id="comment"'.($_GET['anchor'] != 'comment' ? ' style="display: none"' : ''));
		showsetting('setting_functions_comment_allow', array('settingnew[allowpostcomment]', array(
			array(1, $lang['setting_functions_comment_allow_1'], 'commentextra'),
			array(2, $lang['setting_functions_comment_allow_2']))), $setting['allowpostcomment'], 'mcheckbox');
		showsetting('setting_functions_comment_number', 'settingnew[commentnumber]', $setting['commentnumber'], 'text');
		showsetting('setting_functions_comment_postself', 'settingnew[commentpostself]', $setting['commentpostself'], 'radio');
		showtagheader('tbody', 'commentextra', in_array(1, $setting['allowpostcomment']));
		showsetting('setting_functions_comment_firstpost', 'settingnew[commentfirstpost]', $setting['commentfirstpost'], 'radio');
		showsetting('setting_functions_comment_commentitem_0', 'settingnew[commentitem][0]', $setting['commentitem'][0], 'textarea');
		showsetting('setting_functions_comment_commentitem_1', 'settingnew[commentitem][1]', $setting['commentitem'][1], 'textarea');
		showsetting('setting_functions_comment_commentitem_2', 'settingnew[commentitem][2]', $setting['commentitem'][2], 'textarea');
		showsetting('setting_functions_comment_commentitem_3', 'settingnew[commentitem][3]', $setting['commentitem'][3], 'textarea');
		showsetting('setting_functions_comment_commentitem_4', 'settingnew[commentitem][4]', $setting['commentitem'][4], 'textarea');
		showsetting('setting_functions_comment_commentitem_5', 'settingnew[commentitem][5]', $setting['commentitem'][5], 'textarea');
		showtagfooter('tbody');
		if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
			showsetting($data['name'].cplang('setting_functions_comment_commentitem_threadplugin'), 'settingnew[commentitem]['.$tpid.']', $setting['commentitem'][$tpid], 'textarea', '', 0, cplang('setting_functions_comment_commentitem_threadplugin_comment'));
		}
		showtablefooter();

		showtableheader('', 'nobottom', 'id="threadexp"'.($_GET['anchor'] != 'threadexp' ? ' style="display: none"' : ''));
		showsetting('setting_functions_threadexp_repliesrank', 'settingnew[repliesrank]', $setting['repliesrank'], 'radio');
		showsetting('setting_functions_threadexp_blacklist', 'settingnew[threadblacklist]', $setting['threadblacklist'], 'radio');
		showsetting('setting_functions_threadexp_hotreplies', 'settingnew[threadhotreplies]', $setting['threadhotreplies'], 'text');
		showsetting('setting_functions_threadexp_filter', 'settingnew[threadfilternum]', $setting['threadfilternum'], 'text');
		showsetting('setting_functions_threadexp_nofilteredpost', 'settingnew[nofilteredpost]', $setting['nofilteredpost'], 'radio');
		showsetting('setting_functions_threadexp_hidefilteredpost', 'settingnew[hidefilteredpost]', $setting['hidefilteredpost'], 'radio');
		showsetting('setting_functions_threadexp_filterednovote', 'settingnew[filterednovote]', $setting['filterednovote'], 'radio');
		showtablefooter();

		showtips('setting_tips', 'other_tips', $_GET['anchor'] == 'other');
		showtableheader('', 'nobottom', 'id="other"'.($_GET['anchor'] != 'other' ? ' style="display: none"' : ''));
		showsetting('setting_functions_other_pwdsafety', 'settingnew[pwdsafety]', $setting['pwdsafety'], 'radio');
		showsetting('setting_functions_other_uidlogin', 'settingnew[uidlogin]', $setting['uidlogin'], 'radio');
		showsetting('setting_functions_other_autoidselect', 'settingnew[autoidselect]', $setting['autoidselect'], 'radio');
		showsetting('setting_functions_other_submitlock', 'settingnew[submitlock]', $setting['submitlock'], 'radio');
		showsetting('setting_functions_other_rssstatus', 'settingnew[rssstatus]', $setting['rssstatus'], 'radio');
		showsetting('setting_functions_other_rssttl', 'settingnew[rssttl]', $setting['rssttl'], 'text');
		showsetting('setting_functions_other_oltimespan', 'settingnew[oltimespan]', $setting['oltimespan'], 'text');
		showsetting('setting_functions_other_debug', 'settingnew[debug]', $setting['debug'], 'radio');
		showsetting('setting_functions_other_onlyacceptfriendpm', 'settingnew[onlyacceptfriendpm]', $setting['onlyacceptfriendpm'], 'radio');
		showsetting('setting_functions_other_pmreportuser', 'settingnew[pmreportuser]', $setting['pmreportuser'], 'text');
		showsetting('setting_functions_other_at_anyone', 'settingnew[at_anyone]', $setting['at_anyone'], 'radio');
		showsetting('setting_functions_other_chatpmrefreshtime', 'settingnew[chatpmrefreshtime]', $setting['chatpmrefreshtime'], 'text');
		showsetting('setting_functions_other_collectionteamworkernum', 'settingnew[collectionteamworkernum]', $setting['collectionteamworkernum'], 'text');
		showsetting('setting_functions_other_shortcut', 'settingnew[shortcut]', $setting['shortcut'], 'text');
		showsetting('setting_functions_other_closeforumorderby', 'settingnew[closeforumorderby]', $setting['closeforumorderby'], 'radio');
		showsetting('setting_functions_other_disableipnotice', 'settingnew[disableipnotice]', $setting['disableipnotice'], 'radio');
		showsetting('setting_functions_other_darkroom', 'settingnew[darkroom]', $setting['darkroom'], 'radio');
		showsetting('setting_functions_other_global_sign', 'settingnew[globalsightml]', $setting['globalsightml'], 'textarea');
		showtablefooter();

		$setting['guide'] = unserialize($setting['guide']);
		showtableheader('', 'nobottom', 'id="guide"'.($_GET['anchor'] != 'guide' ? ' style="display: none"' : ''));
		showsetting('setting_functions_heatthread_guidelimit', 'settingnew[heatthread][guidelimit]', $setting['heatthread']['guidelimit'], 'text');
		$dtarray = array(
			array(604800, $lang['7_day']),
			array(1209600, $lang['14_day']),
			array(2592000, $lang['30_day']),
			array(7776000, $lang['90_day'])
		);
		showsetting('setting_functions_guide_hotdt', array('settingnew[guide][hotdt]', $dtarray), $setting['guide']['hotdt'], 'select');
		showsetting('setting_functions_guide_digestdt', array('settingnew[guide][digestdt]', $dtarray), $setting['guide']['digestdt'], 'select');
		showtablefooter();

		showtableheader('', 'nobottom', 'id="activity"'.($_GET['anchor'] != 'activity' ? ' style="display: none"' : ''));
		showsetting('setting_functions_activity_type', 'settingnew[activitytype]', $setting['activitytype'], 'textarea');
		$varname = array('settingnew[activityfield]', array(), 'isfloat');
		$ignorearray = array('birthyear', 'birthmonth', 'resideprovince', 'birthprovince', 'residedist', 'residecommunity', 'constellation', 'zodiac');
		foreach(C::t('common_member_profile_setting')->fetch_all_by_available(1) as $row) {
			if(in_array($row['fieldid'], $ignorearray)) continue;
			$varname[1][] = array($row['fieldid'], $row['title'], $row['title']);
		}
		$activityfield = dunserialize($_G['setting']['activityfield']);
		showsetting('setting_functions_activity_field', $varname, $activityfield, 'omcheckbox');
		showsetting('setting_functions_activity_extnum', 'settingnew[activityextnum]', $setting['activityextnum'], 'text');
		$_G['setting']['creditstrans'] = array();
		$setting['extcredits'] = dunserialize($setting['extcredits']);
		for($i = 0; $i <= 8; $i++) {
			$_G['setting']['creditstrans'] .= '<option value="'.$i.'" '.($i == $setting['activitycredit'] ? 'selected' : '').'>'.($i ? 'extcredits'.$i.($setting['extcredits'][$i]['title'] ? '('.$setting['extcredits'][$i]['title'].')' : '') : $lang['none']).'</option>';
		}
		showsetting('setting_functions_activity_credit', '', '' ,'<select name="settingnew[activitycredit]">'.$_G['setting']['creditstrans'].'</select>');
		showsetting('setting_functions_activity_pp', 'settingnew[activitypp]', $setting['activitypp'], 'text');
		showtablefooter();

		showtableheader('', 'notop');
		if($_GET['anchor'] != 'curscript') {
			showsubmit('settingsubmit');
		}
		showtablefooter();
		showformfooter();
		exit;

	} elseif($operation == 'permissions') {

		include_once libfile('function/forumlist');
		$setting['allowviewuserthread'] = dunserialize($setting['allowviewuserthread']);
		$checkallselect = $setting['allowviewuserthread']['fids'] ? '' : ' selected';
		$forumselect = '<select name="settingnew[allowviewuserthread][fids][]" multiple="multiple" size="10"><option value=""'.$checkallselect.'>'.cplang('setting_permissions_allowviewuserthread_forum_group').'</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		if($setting['allowviewuserthread']['fids']) {
			foreach($setting['allowviewuserthread']['fids'] as $v) {
				$forumselect = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $forumselect);
			}
		}

		showtableheader();
		showsetting('setting_permissions_allowviewuserthread', 'settingnew[allowviewuserthread][allow]', $setting['allowviewuserthread']['allow'], 'radio', 0, 1);
		showsetting('setting_permissions_allowviewuserthread_fids', '', '', $forumselect);
		showtagfooter('tbody');
		showsetting('setting_permissions_allowmoderatingthread', 'settingnew[allowmoderatingthread]', $setting['allowmoderatingthread'], 'radio');
		showsetting('setting_permissions_memliststatus', 'settingnew[memliststatus]', $setting['memliststatus'], 'radio');
		showsetting('setting_permissions_minpostsize', 'settingnew[minpostsize]', $setting['minpostsize'], 'text');
		showsetting('setting_permissions_minpostsize_mobile', 'settingnew[minpostsize_mobile]', $setting['minpostsize_mobile'], 'text');
		showsetting('setting_permissions_maxpostsize', 'settingnew[maxpostsize]', $setting['maxpostsize'], 'text');
		showsetting('setting_permissions_alloweditpost', array('settingnew[alloweditpost]', array(
			cplang('thread_general'),
			cplang('thread_poll'),
			cplang('thread_trade'),
			cplang('thread_reward'),
			cplang('thread_activity'),
			cplang('thread_debate')
		)), $setting['alloweditpost'], 'binmcheckbox');
		showsetting('setting_permissions_post_append', 'settingnew[postappend]', $setting['postappend'], 'radio');
		showsetting('setting_permissions_mailinterval', 'settingnew[mailinterval]', $setting['mailinterval'], 'text');
		showsetting('setting_permissions_maxpolloptions', 'settingnew[maxpolloptions]', $setting['maxpolloptions'], 'text');
		showsetting('setting_permissions_editby', 'settingnew[editedby]', $setting['editedby'], 'radio');
		showsetting('setting_permissions_nsprofiles', 'settingnew[nsprofiles]', $setting['nsprofiles'], 'radio');
		showsetting('setting_permissions_modasban', 'settingnew[modasban]', $setting['modasban'], 'radio');

		showtitle('nav_setting_rate');
		showsetting('setting_permissions_karmaratelimit', 'settingnew[karmaratelimit]', $setting['karmaratelimit'], 'text');
		showsetting('setting_permissions_modratelimit', 'settingnew[modratelimit]', $setting['modratelimit'], 'radio');
		showsetting('setting_permissions_dupkarmarate', 'settingnew[dupkarmarate]', $setting['dupkarmarate'], 'radio');
		showsetting('setting_permissions_editperdel', 'settingnew[editperdel]', $setting['editperdel'], 'radio');
		showsetting('setting_permissions_hideexpiration', 'settingnew[hideexpiration]', $setting['hideexpiration'], 'text');

	} elseif($operation == 'credits') {

		$rules = array();
		foreach(C::t('common_credit_rule')->fetch_all_rule() as $value) {
			$rules[$value['rid']] = $value;
		}

		echo '<div id="base"'.($_GET['anchor'] != 'base' ? ' style="display: none"' : '').'>';

		$setting['extcredits'] = dunserialize($setting['extcredits']);
		$setting['initcredits'] = explode(',', $setting['initcredits']);
		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : cplang('setting_credits_formula_extcredits').$i;
			$resultstr .= 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.str_replace("'", "\'", $extcredittitle).'</u>\');'."\r\n";
			$extcreditsbtn .= '<a href="###" onclick="creditinsertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}
		$formulareplace .= '\'<u>'.cplang('setting_credits_formula_digestposts').'</u>\',\'<u>'.cplang('setting_credits_formula_posts').'</u>\'';

		showtableheader('setting_credits_extended', 'fixpadding');
		$title = $creditsetting = array();
		for($i = 1; $i <= 8; $i++) {
			if($i == 1) {
				$title[] = '<font style="font:12px normal normal">'.cplang('setting_credits_available').'</font>';
				$creditsetting[0] = '<td class="td23">'.cplang('credits_title').'</td>';
				$creditsetting[2] = '<td class="td23">'.cplang('credits_img').'</td>';
				$creditsetting[3] = '<td class="td23">'.cplang('credits_unit').'</td>';
				$creditsetting[4] = '<td class="td23">'.cplang('setting_credits_init').'</td>';
				$creditsetting[5] = '<td class="td23">'.cplang('setting_credits_lower_limit').'</td>';
				$creditsetting[6] = '<td class="td23">'.cplang('setting_credits_ratio').'</td>';
				$creditsetting[7] = '<td class="td23">'.cplang('credits_inport').'</td>';
				$creditsetting[8] = '<td class="td23">'.cplang('credits_import').'</td>';
			}
			$title[] = "<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[extcredits][$i][available]\" value=\"1\" ".($setting['extcredits'][$i]['available'] ? 'checked' : '')." />extcredits$i";
			$creditsetting[0] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"settingnew[extcredits][$i][title]\" value=\"{$setting['extcredits'][$i]['title']}\"></td>";
			$creditsetting[2] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" style=\"margin-right:0\" name=\"settingnew[extcredits][$i][img]\" value=\"{$setting['extcredits'][$i]['img']}\">".($setting['extcredits'][$i]['img'] ? ' <img src="'.$setting['extcredits'][$i]['img'].'" class="vmiddle" />' : '')."</td>";
			$creditsetting[3] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"settingnew[extcredits][$i][unit]\" value=\"{$setting['extcredits'][$i]['unit']}\"></td>";
			$creditsetting[4] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"settingnew[initcredits][$i]\" value=\"".intval($setting['initcredits'][$i])."\"></td>";
			$creditsetting[5] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"settingnew[lowerlimit][$i]\" value=\"{$_G['setting']['creditspolicy']['lowerlimit'][$i]}\"></td>";
			$creditsetting[6] .= "<td class=\"td32\"><input type=\"text\" class=\"txt\" name=\"settingnew[extcredits][$i][ratio]\" value=\"".(float)$setting['extcredits'][$i]['ratio']."\" onkeyup=\"if(this.value != '0' && \$('allowexchangeout$i').checked == false && \$('allowexchangein$i').checked == false) {\$('allowexchangeout$i').checked = true;\$('allowexchangein$i').checked = true;} else if(this.value == '0') {\$('allowexchangeout$i').checked = false;\$('allowexchangein$i').checked = false;}\"></td>";
			$creditsetting[7] .= "<td class=\"td32\"><input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[extcredits][$i][allowexchangeout]\" value=\"1\" ".($setting['extcredits'][$i]['allowexchangeout'] ? 'checked' : '')." id=\"allowexchangeout$i\"></td>";
			$creditsetting[8] .= "<td class=\"td32\"><input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[extcredits][$i][allowexchangein]\" value=\"1\" ".($setting['extcredits'][$i]['allowexchangein'] ? 'checked' : '')." id=\"allowexchangein$i\"></td>";
		}
		showsubtitle($title, 'header sml');
		echo '<tr>'.implode('</tr><tr>', $creditsetting).'</tr>';
		showtablerow('', 'colspan="9" class="lineheight"', $lang['setting_credits_extended_comment']);

		showtableheader('setting_credits');
?>
<script type="text/JavaScript">
	function isUndefined(variable) {
		return typeof variable == 'undefined' ? true : false;
	}
	function creditinsertunit(text, textend) {
		insertunit($('creditsformula'), text, textend);
		formulaexp();
	}
	var formulafind = new Array('digestposts', 'posts');
	var formulareplace = new Array(<?php echo $formulareplace?>);
	function formulaexp() {
		var result = $('creditsformula').value;
		<?php
			echo $resultstr;
			echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['setting_credits_formula_digestposts'].'</u>\');';
			echo 'result = result.replace(/posts/g, \'<u>'.$lang['setting_credits_formula_posts'].'</u>\');';
			echo 'result = result.replace(/threads/g, \'<u>'.$lang['setting_credits_formula_threads'].'</u>\');';
			echo 'result = result.replace(/oltime/g, \'<u>'.$lang['setting_credits_formula_oltime'].'</u>\');';
			echo 'result = result.replace(/friends/g, \'<u>'.$lang['setting_credits_formula_friends'].'</u>\');';
			echo 'result = result.replace(/doings/g, \'<u>'.$lang['setting_credits_formula_doings'].'</u>\');';
			echo 'result = result.replace(/blogs/g, \'<u>'.$lang['setting_credits_formula_blogs'].'</u>\');';
			echo 'result = result.replace(/albums/g, \'<u>'.$lang['setting_credits_formula_albums'].'</u>\');';
			echo 'result = result.replace(/sharings/g, \'<u>'.$lang['setting_credits_formula_sharings'].'</u>\');';
		?>
		$('formulapermexp').innerHTML = result;
	}

</script>

<?php
		print <<<EOF
			<tr>
				<td class="td27" colspan="2">$lang[setting_credits_formula]:</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="extcredits">
						$extcreditsbtn
						<a href="###" onclick="creditinsertunit(' posts ')">$lang[setting_credits_formula_posts]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' threads ')">$lang[setting_credits_formula_threads]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' digestposts ')">$lang[setting_credits_formula_digestposts]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' oltime ')">$lang[setting_credits_formula_oltime]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' friends ')">$lang[setting_credits_formula_friends]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' doings ')">$lang[setting_credits_formula_doings]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' blogs ')">$lang[setting_credits_formula_blogs]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' albums ')">$lang[setting_credits_formula_albums]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' sharings ')">$lang[setting_credits_formula_sharings]</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
						<a href="###" onclick="creditinsertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
					</div>
					<div id="formulapermexp" class="margintop marginbot diffcolor2">$formulapermexp</div>
					<textarea name="settingnew[creditsformula]" id="creditsformula" class="marginbot" style="width:80%" rows="3" onkeyup="formulaexp()" onkeydown="textareakey(this, event)">$setting[creditsformula]</textarea>
					<script type="text/JavaScript">formulaexp()</script>
					<br /><span class="smalltxt">$lang[setting_credits_formula_comment]</span>
				</td>
			</tr>
EOF;

		$setting['creditstrans'] = explode(',', $setting['creditstrans']);
		$_G['setting']['creditstrans'] = array();
		for($si = 0; $si < 13; $si++) {
			$_G['setting']['creditstrans'][$si] = '';
			for($i = 0; $i <= 8; $i++) {
				$_G['setting']['creditstrans'][$si] .= '<option value="'.$i.'" '.($i == $setting['creditstrans'][$si] ? 'selected' : '').'>'.($i ? 'extcredits'.$i.($setting['extcredits'][$i]['title'] ? '('.$setting['extcredits'][$i]['title'].')' : '') : ($si > 0 ? ($si != 11 ? $lang['setting_credits_trans_used'] : $lang['setting_credits_trans_credits']) : $lang['none'])).'</option>';
			}
		}
		showsetting('setting_credits_trans', '', '', '<select onchange="if(this.value > 0) {$(\'creditstransextra\').style.display = \'\';} else {$(\'creditstransextra\').style.display = \'none\';}" name="settingnew[creditstrans][0]">'.$_G['setting']['creditstrans'][0].'</select>');
		showtagheader('tbody', 'creditstransextra', $setting['creditstrans'][0], 'sub');
		showsetting('setting_credits_trans9', '', '' ,'<select name="settingnew[creditstrans][9]">'.$_G['setting']['creditstrans'][9].'</select>');
		showsetting('setting_credits_trans1', '', '' ,'<select name="settingnew[creditstrans][1]">'.$_G['setting']['creditstrans'][1].'</select>');
		showsetting('setting_credits_trans2', '', '' ,'<select name="settingnew[creditstrans][2]">'.$_G['setting']['creditstrans'][2].'</select>');
		showsetting('setting_credits_trans3', '', '' ,'<select name="settingnew[creditstrans][3]">'.$_G['setting']['creditstrans'][3].'</select>');
		showhiddenfields(array('settingnew[creditstrans][4]' => 0));
		showsetting('setting_credits_trans5', '', '' ,'<select name="settingnew[creditstrans][5]"><option value="-1">'.$lang['setting_credits_trans5_none'].'</option>'.$_G['setting']['creditstrans'][5].'</select>');
		showsetting('setting_credits_trans6', '', '' ,'<select name="settingnew[creditstrans][6]">'.$_G['setting']['creditstrans'][6].'</select>');
		showsetting('setting_credits_trans7', '', '' ,'<select name="settingnew[creditstrans][7]">'.$_G['setting']['creditstrans'][7].'</select>');
		$setting['report_reward'] = dunserialize($setting['report_reward']);
		showsetting('setting_credits_trans10', '', '' ,'<select name="settingnew[creditstrans][10]">'.$_G['setting']['creditstrans'][10].'</select>');
		showsetting('setting_credits_trans8', '', '' ,'<select name="settingnew[creditstrans][8]">'.$_G['setting']['creditstrans'][8].'</select><br \><br \>'.cplang('report_reward_min').': <input type="text" size="3" name="settingnew[report_reward][min]" value="'.$setting['report_reward']['min'].'">&nbsp;&nbsp;'.cplang('report_reward_max').': <input type="text" size="3" name="settingnew[report_reward][max]" value="'.$setting['report_reward']['max'].'">&nbsp;&nbsp;<br \>'.cplang('report_reward_comment'));
		showsetting('setting_credits_trans11', '', '' ,'<select name="settingnew[creditstrans][11]">'.$_G['setting']['creditstrans'][11].'</select>');
		showsetting('setting_credits_trans12', '', '' ,'<select name="settingnew[creditstrans][12]">'.$_G['setting']['creditstrans'][12].'</select>');

		showtagfooter('tbody');
		showsetting('setting_credits_tax', 'settingnew[creditstax]', $setting['creditstax'], 'text');
		showsetting('setting_credits_mintransfer', 'settingnew[transfermincredits]', $setting['transfermincredits'], 'text');
		showsetting('setting_credits_minexchange', 'settingnew[exchangemincredits]', $setting['exchangemincredits'], 'text');
		showsetting('setting_credits_maxincperthread', 'settingnew[maxincperthread]', $setting['maxincperthread'], 'text');
		showsetting('setting_credits_maxchargespan', 'settingnew[maxchargespan]', $setting['maxchargespan'], 'text');
		showtablefooter();
		echo '</div>';
		showtableheader();

	} elseif($operation == 'mail' && $isfounder) {

		$setting['mail'] = dunserialize($setting['mail']);
		$passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password'][0].'********'.substr($setting['mail']['auth_password'], -2) : '';

		showtableheader('', '', 'id="mailsetting"'.($_GET['anchor'] != 'setting' ? ' style="display: none"' : ''));

		showsetting('setting_mail_setting_send', array('settingnew[mail][mailsend]', array(
			array(1, $lang['setting_mail_setting_send_1'], array('hidden1' => 'none', 'hidden2' => 'none')),
			array(2, $lang['setting_mail_setting_send_2'], array('hidden1' => 'none', 'hidden2' => '')),
			array(3, $lang['setting_mail_setting_send_3'], array('hidden1' => '', 'hidden2' => 'none'))
		)), $setting['mail']['mailsend'], 'mradio');
		$sendtype = $setting['mail']['mailsend'] == 2 ? 0 : 1;
		showtagheader('tbody', 'hidden1', $setting['mail']['mailsend'] == 3, 'sub');

		echo <<<EOF
		<tr><td colspan="2" style="border-top:0px dotted #DEEFFB;">
		<script type="text/JavaScript">
			var rowtypedata = [];
			function setrowtypedata(sendtype) {
				if(sendtype) {
					rowtypedata = [
						[
							[1,'', 'td25'],
							[1,'<input type="text" class="txt" name="newsmtp[server][]" style="width: 90%;">', 'td28'],
							[1,'<input type="text" class="txt" name="newsmtp[port][]" value="25">', 'td28'],
							[1,'<input type="checkbox" name="newsmtp[auth][]" value="1">', 'td25'],
							[1,'<input type="text" class="txt" name="newsmtp[from][]" style="width: 90%;">'],
							[1,'<input type="text" class="txt" name="newsmtp[auth_username][]" style="width: 90%;">'],
							[1,'<input type="text" class="txt" name="newsmtp[auth_password][]" style="width: 90%;">'],
						]
					];
				} else {
					rowtypedata = [
						[
							[1,'', 'td25'],
							[1,'<input type="text" class="txt" name="newsmtp[server][]" style="width: 90%;">', 'td28'],
							[1,'<input type="text" class="txt" name="newsmtp[port][]" value="25">', 'td28']
						]
					];
				}
			}

			setrowtypedata($sendtype);
		</script>

		<table style="margin-top: 0px;" class="tb tb2">
			<tr class="header">
				<th class="td25">$lang[delete]</th>
				<th class="td28">$lang[setting_mail_setting_server]</th>
				<th class="td28">$lang[setting_mail_setting_port]</th>
			</tr>
EOF;
		foreach($setting['mail']['smtp'] as $id => $smtp) {
			$checkauth = $smtp['auth'] ? 'checked' : '';
			$smtp['auth_password'] = $smtp['auth_password'] ? $smtp['auth_password'][0].'********'.substr($smtp['auth_password'], -2) : '';
			showtablerow('', array('class="td25"', 'class="td28"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[mail][smtp][delete][]\" value=\"$id\">",
				"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][smtp][$id][server]\" value=\"$smtp[server]\" style=\"width: 90%;\">",
				"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][smtp][$id][port]\" value=\"$smtp[port]\">"
			));
		}
		echo '<tr><td colspan="7"><div><a href="###" onclick="setrowtypedata(0);addrow(this, 0);" class="addtr">'.$lang['setting_mail_setting_edit_addnew'].'</a></div></td></tr>';

		showtablefooter();
		echo '</td></tr>';
		showtagfooter('tbody');
		showtagheader('tbody', 'hidden2', $setting['mail']['mailsend'] == 2, 'sub');

		echo <<<EOF
		<tr><td colspan="2" style="border-top:0px dotted #DEEFFB;">
		<table style="margin-top: 0px;" class="tb tb2">
			<tr class="header">
				<th class="td25">$lang[delete]</th>
				<th class="td28">$lang[setting_mail_setting_server]</th>
				<th class="td28">$lang[setting_mail_setting_port]</th>
				<th id="auth_0">$lang[setting_mail_setting_validate]</th>
				<th id="from_0">$lang[setting_mail_setting_from]</th>
				<th id="username_0">$lang[setting_mail_setting_username]</th>
				<th id="password_0">$lang[setting_mail_setting_password]</th>
			</tr>
EOF;
		foreach($setting['mail']['smtp'] as $id => $smtp) {
			$checkauth = $smtp['auth'] ? 'checked' : '';
			$smtp['auth_password'] = $smtp['auth_password'] ? $smtp['auth_password'][0].'********'.substr($smtp['auth_password'], -2) : '';

			showtablerow('', array('class="td25"', 'class="td28"', 'class="td28"', 'class="td25"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[mail][esmtp][delete][]\" value=\"$id\">",
			"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][esmtp][$id][server]\" value=\"$smtp[server]\" style=\"width: 90%;\">",
			"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][esmtp][$id][port]\" value=\"$smtp[port]\">",
			"<input type=\"checkbox\" name=\"settingnew[mail][esmtp][$id][auth]\" value=\"1\" $checkauth>",
			"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][esmtp][$id][from]\" value=\"$smtp[from]\" style=\"width: 90%;\">",
			"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][esmtp][$id][auth_username]\" value=\"$smtp[auth_username]\" style=\"width: 90%;\">",
			"<input type=\"text\" class=\"txt\" name=\"settingnew[mail][esmtp][$id][auth_password]\" value=\"$smtp[auth_password]\" style=\"width: 90%;\">",
			));
		}
		echo '<tr><td colspan="7"><div><a href="###" onclick="setrowtypedata(1);addrow(this, 0);" class="addtr">'.$lang['setting_mail_setting_edit_addnew'].'</a></div></td></tr>';

		showtablefooter();
		echo '</td></tr>';

		showtagfooter('tbody');
		showsetting('setting_mail_setting_delimiter', array('settingnew[mail][maildelimiter]', array(
			array(1, $lang['setting_mail_setting_delimiter_crlf']),
			array(0, $lang['setting_mail_setting_delimiter_lf']),
			array(2, $lang['setting_mail_setting_delimiter_cr']))),  $setting['mail']['maildelimiter'], 'mradio');
		showsetting('setting_mail_setting_includeuser', 'settingnew[mail][mailusername]', $setting['mail']['mailusername'], 'radio');
		showsetting('setting_mail_setting_silent', 'settingnew[mail][sendmail_silent]', $setting['mail']['sendmail_silent'], 'radio');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="mailcheck"'.($_GET['anchor'] != 'check' ? ' style="display: none"' : ''));
		showsetting('setting_mail_check_test_from', 'test_from', '', 'text');
		showsetting('setting_mail_check_test_to', 'test_to', '', 'textarea');
		showsubmit('', '', '<input type="submit" class="btn" name="mailcheck" value="'.cplang('setting_mail_check_submit').'" onclick="this.form.operation.value=\'mailcheck\';this.form.action=\''.ADMINSCRIPT.'?action=checktools&operation=mailcheck&frame=no\';this.form.target=\'mailcheckiframe\';">', '<iframe name="mailcheckiframe" style="display: none"></iframe>');
		showtablefooter();

		showformfooter();
		exit;

	} elseif($operation == 'accountguard') {

		loadcache('usergroups');
		$setting['accountguard'] = dunserialize($setting['accountguard']);
		$usergroups = C::t('common_usergroup_field')->fetch_all(array_keys($_G['cache']['usergroups']));
		showtableheader('', 'nobottom');
		$forcelogin = '<tr class="header"><td></td><td>'.cplang('usergroups_edit_basic_forcelogin_none').'</td>'.($_G['setting']['connect']['allow'] ? '<td>'.cplang('usergroups_edit_basic_forcelogin_qq').'</td>' : '').'<td>'.cplang('usergroups_edit_basic_forcelogin_mail').'</td></tr>';
		ksort($_G['cache']['usergroups']);
		foreach($_G['cache']['usergroups'] as $gid => $usergroup) {
			if(in_array($gid, array(7, 8))) {
				continue;
			}
			$forcelogin .= '<tr class="hover"><td>'.$usergroup['grouptitle'].'</td>'.
				'<td><label><input class="radio" type="radio" name="aggid['.$gid.']" '.(!$usergroups[$gid]['forcelogin'] ? 'checked ' : '').'value="0">'.'</label></td>'.
				($_G['setting']['connect']['allow'] ? '<td><label><input class="radio" type="radio" name="aggid['.$gid.']" '.($usergroups[$gid]['forcelogin'] == 1 ? 'checked ' : '').'value="1">'.'</label></td>' : '').
				'<td><label><input class="radio" type="radio" name="aggid['.$gid.']" '.($usergroups[$gid]['forcelogin'] == 2 ? 'checked ' : '').'value="2">'.'</label></td>'.
				'</tr>';
		}
		$forcelogin .= '<tr><td colspan="3" class="lineheight">'.cplang('setting_sec_accountguard_forcelogin_comment').'</td></table>';
		if($_G['setting']['connect']['allow']) {
			showsetting('setting_sec_accountguard_postqqonly', 'settingnew[accountguard][postqqonly]', $setting['accountguard']['postqqonly'], 'radio');
		}
		showsetting('setting_sec_accountguard_loginpwcheck', array('settingnew[accountguard][loginpwcheck]', array(
			array(0, $lang['setting_sec_accountguard_loginpwcheck_none']),
			array(1, $lang['setting_sec_accountguard_loginpwcheck_prompt']),
			array(2, $lang['setting_sec_accountguard_loginpwcheck_force']))),  $setting['accountguard']['loginpwcheck'], 'mradio');
		showsetting('setting_sec_accountguard_loginoutofdate', 'settingnew[accountguard][loginoutofdate]', $setting['accountguard']['loginoutofdate'], 'radio');
		showsetting('setting_sec_accountguard_loginoutofdatenum', 'settingnew[accountguard][loginoutofdatenum]', $setting['accountguard']['loginoutofdatenum'], 'text');
		showtablefooter();
		showtableheader('setting_sec_accountguard_forcelogin', 'nobottom');
		echo $forcelogin;
		showtablefooter();

	} elseif($operation == 'seccheck') {

		$seccodecheck = 1;
		$sechash = 'S'.$_G['sid'];
		$seccheckhtml = "<span id=\"seccode_c$sechash\"></span><script type=\"text/javascript\">updateseccode('c$sechash', '<br /><sec> <sec> <sec>', 'admin');</script>";

		$checksc = array();
		$setting['seccodedata'] = dunserialize($setting['seccodedata']);

		$seccodetypearray = array(
			array(0, cplang('setting_sec_seccode_type_image'), array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(1, cplang('setting_sec_seccode_type_chnfont'), array('seccodeimageext' => '', 'seccodeimagewh' => '')),
			array(2, cplang('setting_sec_seccode_type_flash'), array('seccodeimageext' => 'none', 'seccodeimagewh' => '')),
			array(3, cplang('setting_sec_seccode_type_wav'), array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none')),
			array(99, cplang('setting_sec_seccode_type_bitmap'), array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none')),
		);

		$seccodetypearray = array_merge($seccodetypearray, getseccodes($seccodesettings));

		showtips('setting_sec_code_tips', 'seccode_tips', $_GET['anchor'] == 'seccode');

		showtableheader('', '', 'id="seccode"'.($_GET['anchor'] != 'seccode' ? ' style="display: none"' : ''));
		showtitle('setting_sec_seccode_rule_setting');
		showsetting('setting_sec_seccode_rule_register', array('settingnew[seccodedata][rule][register][allow]', array(
			array(2, cplang('setting_sec_seccode_rule_register_auto'), array('secrule_register' => '')),
			array(1, cplang('setting_sec_seccode_rule_register_on'), array('secrule_register' => 'none')),
			array(0, cplang('setting_sec_seccode_rule_register_off'), array('secrule_register' => 'none')),
		)), $setting['seccodedata']['rule']['register']['allow'], 'mradio');
		showtagheader('tbody', 'secrule_register', $setting['seccodedata']['rule']['register']['allow'] == 2, 'sub');
		showsetting('setting_sec_seccode_rule_register_numlimit', 'settingnew[seccodedata][rule][register][numlimit]', $setting['seccodedata']['rule']['register']['numlimit'], 'text');
		showsetting('setting_sec_seccode_rule_register_timelimit', array('settingnew[seccodedata][rule][register][timelimit]', array(
			array(60, '1 '.cplang('setting_sec_seccode_rule_min')),
			array(180, '3'.cplang('setting_sec_seccode_rule_min')),
			array(300, '5'.cplang('setting_sec_seccode_rule_min')),
			array(900, '15'.cplang('setting_sec_seccode_rule_min')),
			array(1800, '30'.cplang('setting_sec_seccode_rule_min')),
			array(3600, '1'.cplang('setting_sec_seccode_rule_hour')),
		)), $setting['seccodedata']['rule']['register']['timelimit'], 'select', 'noborder');
		showtagfooter('tbody');

		showsetting('setting_sec_seccode_rule_login', array('settingnew[seccodedata][rule][login][allow]', array(
			array(2, cplang('setting_sec_seccode_rule_login_auto'), array('secrule_login' => '')),
			array(1, cplang('setting_sec_seccode_rule_login_on'), array('secrule_login' => 'none')),
			array(0, cplang('setting_sec_seccode_rule_login_off'), array('secrule_login' => 'none')),
		)), $setting['seccodedata']['rule']['login']['allow'], 'mradio');
		showtagheader('tbody', 'secrule_login', $setting['seccodedata']['rule']['login']['allow'] == 2, 'sub');
		showsetting('setting_sec_seccode_rule_login_nolocal', 'settingnew[seccodedata][rule][login][nolocal]', $setting['seccodedata']['rule']['login']['nolocal'], 'radio');
		showsetting('setting_sec_seccode_rule_login_pwsimple', 'settingnew[seccodedata][rule][login][pwsimple]', $setting['seccodedata']['rule']['login']['pwsimple'], 'radio');
		showsetting('setting_sec_seccode_rule_login_pwerror', 'settingnew[seccodedata][rule][login][pwerror]', $setting['seccodedata']['rule']['login']['pwerror'], 'radio');
		showsetting('setting_sec_seccode_rule_login_outofday', 'settingnew[seccodedata][rule][login][outofday]', $setting['seccodedata']['rule']['login']['outofday'], 'text');
		showsetting('setting_sec_seccode_rule_login_numiptry', 'settingnew[seccodedata][rule][login][numiptry]', $setting['seccodedata']['rule']['login']['numiptry'], 'text');
		showsetting('setting_sec_seccode_rule_login_timeiptry', array('settingnew[seccodedata][rule][login][timeiptry]', array(
			array(60, '1 '.cplang('setting_sec_seccode_rule_min')),
			array(180, '3'.cplang('setting_sec_seccode_rule_min')),
			array(300, '5'.cplang('setting_sec_seccode_rule_min')),
			array(900, '15'.cplang('setting_sec_seccode_rule_min')),
			array(1800, '30'.cplang('setting_sec_seccode_rule_min')),
			array(3600, '1'.cplang('setting_sec_seccode_rule_hour')),
		)), $setting['seccodedata']['rule']['login']['timeiptry'], 'select', 'noborder');
		showtagfooter('tbody');

		showsetting('setting_sec_seccode_rule_post', array('settingnew[seccodedata][rule][post][allow]', array(
			array(2, cplang('setting_sec_seccode_rule_post_auto'), array('secrule_post' => '', 'secrule_post_common' => '')),
			array(1, cplang('setting_sec_seccode_rule_post_on'), array('secrule_post' => 'none', 'secrule_post_common' => '')),
			array(0, cplang('setting_sec_seccode_rule_post_off'), array('secrule_post' => 'none', 'secrule_post_common' => 'none')),
		)), $setting['seccodedata']['rule']['post']['allow'], 'mradio');
		showtagheader('tbody', 'secrule_post_common', $setting['seccodedata']['rule']['post']['allow'], 'sub');
		showtagheader('tbody', 'secrule_post', $setting['seccodedata']['rule']['post']['allow'] == 2, 'sub');
		showsetting('setting_sec_seccode_rule_post_numlimit', 'settingnew[seccodedata][rule][post][numlimit]', $setting['seccodedata']['rule']['post']['numlimit'], 'text');
		showsetting('setting_sec_seccode_rule_post_timelimit', array('settingnew[seccodedata][rule][post][timelimit]', array(
			array(60, '1 '.cplang('setting_sec_seccode_rule_min')),
			array(180, '3'.cplang('setting_sec_seccode_rule_min')),
			array(300, '5'.cplang('setting_sec_seccode_rule_min')),
			array(900, '15'.cplang('setting_sec_seccode_rule_min')),
			array(1800, '30'.cplang('setting_sec_seccode_rule_min')),
			array(3600, '1'.cplang('setting_sec_seccode_rule_hour')),
		)), $setting['seccodedata']['rule']['post']['timelimit'], 'select', 'noborder');
		showsetting('setting_sec_seccode_rule_post_nplimit', 'settingnew[seccodedata][rule][post][nplimit]', $setting['seccodedata']['rule']['post']['nplimit'], 'text');
		showsetting('setting_sec_seccode_rule_post_vplimit', 'settingnew[seccodedata][rule][post][vplimit]', $setting['seccodedata']['rule']['post']['vplimit'], 'text');
		showtagfooter('tbody');

		showsetting('setting_sec_seccode_rule_password', 'settingnew[seccodedata][rule][password][allow]', $setting['seccodedata']['rule']['password']['allow'], 'radio');
		showsetting('setting_sec_seccode_rule_card', 'settingnew[seccodedata][rule][card][allow]', $setting['seccodedata']['rule']['card']['allow'], 'radio');
		showsetting('setting_sec_seccode_minposts', 'settingnew[seccodedata][minposts]', $setting['seccodedata']['minposts'], 'text');

		showtitle('setting_sec_seccode_type_setting');
		showsetting('setting_sec_seccode_type', array('settingnew[seccodedata][type]', $seccodetypearray), $setting['seccodedata']['type'], 'mradio', '', 0, cplang('setting_sec_seccode_type_comment').$seccheckhtml);
		showtagheader('tbody', 'seccodeimagewh', is_numeric($setting['seccodedata']['type']) && $setting['seccodedata']['type'] != 3 && $setting['seccodedata']['type'] != 99, 'sub');
		showsetting('setting_sec_seccode_width', 'settingnew[seccodedata][width]', $setting['seccodedata']['width'], 'text');
		showsetting('setting_sec_seccode_height', 'settingnew[seccodedata][height]', $setting['seccodedata']['height'], 'text');
		showtagfooter('tbody');
		showtagheader('tbody', 'seccodeimageext', is_numeric($setting['seccodedata']['type']) && $setting['seccodedata']['type'] != 2 && $setting['seccodedata']['type'] != 3 && $setting['seccodedata']['type'] != 99, 'sub');
		showsetting('setting_sec_seccode_scatter', 'settingnew[seccodedata][scatter]', $setting['seccodedata']['scatter'], 'text');
		showsetting('setting_sec_seccode_background', 'settingnew[seccodedata][background]', $setting['seccodedata']['background'], 'radio');
		showsetting('setting_sec_seccode_adulterate', 'settingnew[seccodedata][adulterate]', $setting['seccodedata']['adulterate'], 'radio');
		showsetting('setting_sec_seccode_ttf', 'settingnew[seccodedata][ttf]', $setting['seccodedata']['ttf'], 'radio', !function_exists('imagettftext'));
		showsetting('setting_sec_seccode_angle', 'settingnew[seccodedata][angle]', $setting['seccodedata']['angle'], 'radio');
		showsetting('setting_sec_seccode_warping', 'settingnew[seccodedata][warping]', $setting['seccodedata']['warping'], 'radio');
		showsetting('setting_sec_seccode_color', 'settingnew[seccodedata][color]', $setting['seccodedata']['color'], 'radio');
		showsetting('setting_sec_seccode_size', 'settingnew[seccodedata][size]', $setting['seccodedata']['size'], 'radio');
		showsetting('setting_sec_seccode_shadow', 'settingnew[seccodedata][shadow]', $setting['seccodedata']['shadow'], 'radio');
		showsetting('setting_sec_seccode_animator', 'settingnew[seccodedata][animator]', $setting['seccodedata']['animator'], 'radio', !function_exists('imagegif'));
		showtagfooter('tbody');

		showsubmit('settingsubmit');
		showtablefooter();

		$setting['secqaa'] = dunserialize($setting['secqaa']);
		$start_limit = ($page - 1) * 10;
		$secqaanums = C::t('common_secquestion')->count();
		$multipage = multi($secqaanums, 10, $page, ADMINSCRIPT.'?action=setting&operation=seccheck&anchor=secqaa');


		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[[1,''], [1,'<input name="newquestion[]" type="text" class="txt">','td26'], [1, '<input name="newanswer[]" type="text" class="txt">']],
	];
	</script>
EOT;
		showtips('setting_sec_qaa_tips', 'secqaa_tips', $_GET['anchor'] == 'secqaa');
		showtagheader('div', 'secqaa', $_GET['anchor'] == 'secqaa');
		showtableheader('setting_sec_secqaa', 'nobottom');
		showsetting('setting_sec_secqaa_status', array('settingnew[secqaa][status]', array(
			cplang('setting_sec_seccode_status_register'),
			cplang('setting_sec_seccode_status_post'),
			cplang('setting_sec_seccode_status_password')
		)), $setting['secqaa']['status'], 'binmcheckbox');
		showsetting('setting_sec_secqaa_minposts', 'settingnew[secqaa][minposts]', $setting['secqaa']['minposts'], 'text');
		showtablefooter();

		showtableheader('setting_sec_secqaa_qaa', 'noborder fixpadding');
		showsubtitle(array('', 'setting_sec_secqaa_question', 'setting_sec_secqaa_answer'));

		$qaaext = array();
		foreach(C::t('common_secquestion')->fetch_all($start_limit, 10) as $item) {
			if(!$item['type']) {
				showtablerow('', array('', 'class="td26"'), array(
					'<input class="checkbox" type="checkbox" name="delete[]" value="'.$item['id'].'">',
					'<input type="text" class="txt" name="question['.$item['id'].']" value="'.dhtmlspecialchars($item['question']).'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">',
					'<input type="text" class="txt" name="answer['.$item['id'].']" value="'.$item['answer'].'" class="txtnobd" onblur="this.className=\'txtnobd\'" onfocus="this.className=\'txt\'">'
				));
			} else {
				$qaaext[] = $item['question'];
			}
		}
		echo '<tr><td></td><td class="td26"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['setting_sec_secqaa_add'].'</a></div></td><td></td></tr>';

		echo getsecqaas($qaaext);

		showsubmit('settingsubmit', 'submit', 'del', '', $multipage);
		showtablefooter();
		showtagfooter('div');
		exit;

	} elseif($operation == 'sec') {

		$setting['reginput'] = dunserialize($setting['reginput']);

		showtableheader('', '', 'id="base"'.($_GET['anchor'] != 'base' ? ' style="display: none"' : ''));
		showsetting('setting_sec_floodctrl', 'settingnew[floodctrl]', $setting['floodctrl'], 'text');
		showsetting('setting_sec_base_need_email', 'settingnew[need_email]', $setting['need_email'], 'radio');
		showsetting('setting_sec_base_need_avatar', 'settingnew[need_avatar]', $setting['need_avatar'], 'radio');
		showsetting('setting_sec_base_need_friendnum', 'settingnew[need_friendnum]', $setting['need_friendnum'], 'text');
		showtablefooter();

		showtagheader('div', 'reginput', $_GET['anchor'] == 'reginput');
		showtableheader('setting_sec_reginput', 'nobottom');
		showsetting('setting_sec_reginput_username', 'settingnew[reginput][username]', $setting['reginput']['username'], 'text');
		showsetting('setting_sec_reginput_password', 'settingnew[reginput][password]', $setting['reginput']['password'], 'text');
		showsetting('setting_sec_reginput_password2', 'settingnew[reginput][password2]', $setting['reginput']['password2'], 'text');
		showsetting('setting_sec_reginput_email', 'settingnew[reginput][email]', $setting['reginput']['email'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'postperiodtime', $_GET['anchor'] == 'postperiodtime');
		showtableheader('setting_sec_postperiodtime', 'nobottom');
		showsetting('setting_datetime_postbanperiods', 'settingnew[postbanperiods]', $setting['postbanperiods'], 'textarea');
		showsetting('setting_datetime_postmodperiods', 'settingnew[postmodperiods]', $setting['postmodperiods'], 'textarea');
		showsetting('setting_datetime_postignorearea', 'settingnew[postignorearea]', $setting['postignorearea'], 'textarea');
		showsetting('setting_datetime_postignoreip', 'settingnew[postignoreip]', $setting['postignoreip'], 'textarea');
		showtablefooter();
		showtagfooter('div');

	} elseif($operation == 'datetime') {

		$checktimeformat = array($setting['timeformat'] == 'H:i' ? 24 : 12 => 'checked');

		$setting['userdateformat'] = dateformat($setting['userdateformat']);
		$setting['dateformat'] = dateformat($setting['dateformat']);

		showtableheader();
		showtitle('setting_datetime_format');
		showsetting('setting_datetime_dateformat', 'settingnew[dateformat]', $setting['dateformat'], 'text');
		showsetting('setting_datetime_timeformat', '', '', '<input class="radio" type="radio" name="settingnew[timeformat]" value="24" '.$checktimeformat[24].'> 24 '.$lang['hour'].' <input class="radio" type="radio" name="settingnew[timeformat]" value="12" '.$checktimeformat[12].'> 12 '.$lang['hour'].'');
		showsetting('setting_datetime_dateconvert', 'settingnew[dateconvert]', $setting['dateconvert'], 'radio');

		$timezone_lang = cplang('setting_datetime_timezone');
		$timezone_select = "<select name='global_timeoffset' onchange=\"if(this.value !== '')$('settingnew[timeoffset]').value=this.value;\">";
		foreach($timezone_lang AS $key => $val) {
			$timezone_select .= "<option value='$key' ".($setting['timeoffset'] == $key ? 'selected="selected"' : '').">".cutstr($val, 34, '..')."</option>";
		}
		$timezone_select .= "</select>";
		$timezone_select .= "<br><br><input id=\"settingnew[timeoffset]\" type=\"text\" class=\"txt\" value=\"".$setting['timeoffset']."\" name=\"settingnew[timeoffset]\">";
		showsetting('setting_datetime_timeoffset', '', '', $timezone_select);

		showtitle('setting_datetime_periods');
		showsetting('setting_datetime_visitbanperiods', 'settingnew[visitbanperiods]', $setting['visitbanperiods'], 'textarea');
		showsetting('setting_datetime_ban_downtime', 'settingnew[attachbanperiods]', $setting['attachbanperiods'], 'textarea');
		showsetting('setting_datetime_searchbanperiods', 'settingnew[searchbanperiods]', $setting['searchbanperiods'], 'textarea');

	} elseif($operation == 'attach') {

		showtableheader('', '', 'id="basic"'.($_GET['anchor'] != 'basic' ? ' style="display: none"' : ''));
		showsetting('setting_attach_basic_dir', 'settingnew[attachdir]', $setting['attachdir'], 'text');
		showsetting('setting_attach_basic_url', 'settingnew[attachurl]', $setting['attachurl'], 'text');
		showsetting('setting_attach_image_lib', array('settingnew[imagelib]', array(
			array(0, $lang['setting_attach_image_watermarktype_GD'], array('imagelibext' => 'none')),
			array(1, $lang['setting_attach_image_watermarktype_IM'], array('imagelibext' => ''))
		)), $setting['imagelib'], 'mradio');
		showsetting('setting_attach_image_gdlimit', 'settingnew[gdlimit]', $setting['gdlimit'], 'text');
		showsetting('setting_attach_image_thumbquality', 'settingnew[thumbquality]', $setting['thumbquality'], 'text');
		showsetting('setting_attach_image_disabledmobile', 'settingnew[thumbdisabledmobile]', !$setting['thumbdisabledmobile'], 'radio');
		showsetting('setting_attach_image_preview', '', '', cplang('setting_attach_image_thumb_preview_btn'));
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="forumattach"'.($_GET['anchor'] != 'forumattach' ? ' style="display: none"' : ''));
		showsetting('setting_attach_basic_imgpost', 'settingnew[attachimgpost]', $setting['attachimgpost'], 'radio');
		showsetting('setting_attach_basic_allowattachurl', 'settingnew[allowattachurl]', $setting['allowattachurl'], 'radio');
		showsetting('setting_attach_image_thumbstatus', array('settingnew[thumbstatus]', array(
			array('', $lang['setting_attach_image_thumbstatus_none'], array('thumbext' => 'none')),
			array('fixnone', $lang['setting_attach_image_thumbstatus_fixnone'], array('thumbext' => '')),
			array('fixwr', $lang['setting_attach_image_thumbstatus_fixwr'], array('thumbext' => '')),
		)), $setting['thumbstatus'], 'mradio');
		showtagheader('tbody', 'thumbext', $setting['thumbstatus'], 'sub');
		showsetting('setting_attach_image_thumbwidthheight', array('settingnew[thumbwidth]', 'settingnew[thumbheight]'), array(intval($setting['thumbwidth']), intval($setting['thumbheight'])), 'multiply');
		showtagfooter('tbody');
		showsetting('setting_attach_basic_thumbsource', 'settingnew[thumbsource]', $setting['thumbsource'], 'radio', 0, 1);
		showsetting('setting_attach_image_thumbsourcewidthheight', array('settingnew[sourcewidth]', 'settingnew[sourceheight]'), array(intval($setting['sourcewidth']), intval($setting['sourceheight'])), 'multiply');
		showtagfooter('tbody');
		showsetting('setting_attach_antileech_expire', 'settingnew[attachexpire]', $setting['attachexpire'], 'text');
		showsetting('setting_attach_antileech_refcheck', 'settingnew[attachrefcheck]', $setting['attachrefcheck'], 'radio');
		showtagfooter('tbody');

		showsubmit('settingsubmit');
		showtablefooter();

		if($isfounder) {

			$setting['ftp'] = dunserialize($setting['ftp']);
			$setting['ftp'] = is_array($setting['ftp']) ? $setting['ftp'] : array();
			$setting['ftp']['password'] = authcode($setting['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
			$setting['ftp']['password'] = $setting['ftp']['password'] ? $setting['ftp']['password'][0].'********'.$setting['ftp']['password'][strlen($setting['ftp']['password']) - 1] : '';

			require_once libfile('function/cache');

			showtableheader('', '', 'id="remote"'.($_GET['anchor'] != 'remote' ? ' style="display: none"' : ''));
			showsetting('setting_attach_remote_enabled', array('settingnew[ftp][on]', array(
				array(1, $lang['yes'], array('ftpext' => '', 'ftpcheckbutton' => '')),
				array(0, $lang['no'], array('ftpext' => 'none', 'ftpcheckbutton' => 'none'))
			), TRUE), $setting['ftp']['on'], 'mradio');
			showtagheader('tbody', 'ftpext', $setting['ftp']['on'], 'sub');
			showsetting('setting_attach_remote_enabled_ssl', 'settingnew[ftp][ssl]', $setting['ftp']['ssl'], 'radio');
			showsetting('setting_attach_remote_ftp_host', 'settingnew[ftp][host]', $setting['ftp']['host'], 'text');
			showsetting('setting_attach_remote_ftp_port', 'settingnew[ftp][port]', $setting['ftp']['port'], 'text');
			showsetting('setting_attach_remote_ftp_user', 'settingnew[ftp][username]', $setting['ftp']['username'], 'text');
			showsetting('setting_attach_remote_ftp_pass', 'settingnew[ftp][password]', $setting['ftp']['password'], 'text');
			showsetting('setting_attach_remote_ftp_pasv', 'settingnew[ftp][pasv]', $setting['ftp']['pasv'], 'radio');
			showsetting('setting_attach_remote_dir', 'settingnew[ftp][attachdir]', $setting['ftp']['attachdir'], 'text');
			showsetting('setting_attach_remote_url', 'settingnew[ftp][attachurl]', $setting['ftp']['attachurl'], 'text');
			showsetting('setting_attach_remote_timeout', 'settingnew[ftp][timeout]', $setting['ftp']['timeout'], 'text');
			showsetting('setting_attach_remote_preview', '', '', cplang('setting_attach_remote_preview_btn'));
			showtagfooter('tbody');
			showsetting('setting_attach_remote_allowedexts', 'settingnew[ftp][allowedexts]', $setting['ftp']['allowedexts'], 'textarea');
			showsetting('setting_attach_remote_disallowedexts', 'settingnew[ftp][disallowedexts]', $setting['ftp']['disallowedexts'], 'textarea');
			showsetting('setting_attach_remote_minsize', 'settingnew[ftp][minsize]', $setting['ftp']['minsize'], 'text');
			showsetting('setting_attach_antileech_remote_hide_dir', 'settingnew[ftp][hideurl]', $setting['ftp']['hideurl'], 'radio');

			showsubmit('settingsubmit');
			showtablefooter();
		}

		showtableheader('', '', 'id="albumattach"'.($_GET['anchor'] != 'albumattach' ? ' style="display: none"' : ''));
		showsetting('setting_attach_album_maxtimage', array('settingnew[maxthumbwidth]', 'settingnew[maxthumbheight]'), array(intval($setting['maxthumbwidth']), intval($setting['maxthumbheight'])), 'multiply');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', '', 'id="portalarticle"'.($_GET['anchor'] != 'portalarticle' ? ' style="display: none"' : ''));
		showsetting('setting_attach_portal_article_img_thumb_closed', 'settingnew[portalarticleimgthumbclosed]', !$setting['portalarticleimgthumbclosed'], 'radio');
		showsetting('setting_attach_portal_article_imgsize', array('settingnew[portalarticleimgthumbwidth]', 'settingnew[portalarticleimgthumbheight]'), array(intval($setting['portalarticleimgthumbwidth']), intval($setting['portalarticleimgthumbheight'])), 'multiply');
		showsubmit('settingsubmit');
		showtablefooter();

		showformfooter();
		exit;

	} elseif($operation == 'imgwater') {
		$setting['watermarktext'] = (array)dunserialize($setting['watermarktext']);
		$setting['watermarkstatus'] = (array)dunserialize($setting['watermarkstatus']);
		$setting['watermarktype'] = (array)dunserialize($setting['watermarktype']);
		$setting['watermarktrans'] = (array)dunserialize($setting['watermarktrans']);
		$setting['watermarkquality'] = (array)dunserialize($setting['watermarkquality']);
		$setting['watermarkminheight'] = (array)dunserialize($setting['watermarkminheight']);
		$setting['watermarkminwidth'] = (array)dunserialize($setting['watermarkminwidth']);
		$setting['watermarktext']['fontpath'] = str_replace(array('ch/', 'en/'), '', $setting['watermarktext']['fontpath']);

		$fontlist = array();
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/en');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist['portal'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['portal'] ? ' selected>' : '>').$entry.'</option>';
				$fontlist['forum'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['forum'] ? ' selected>' : '>').$entry.'</option>';
				$fontlist['album'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['album'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$dir = opendir(DISCUZ_ROOT.'./static/image/seccode/font/ch');
		while($entry = readdir($dir)) {
			if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
				$fontlist['portal'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['portal'] ? ' selected>' : '>').$entry.'</option>';
				$fontlist['forum'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['forum'] ? ' selected>' : '>').$entry.'</option>';
				$fontlist['album'] .= '<option value="'.$entry.'"'.($entry == $setting['watermarktext']['fontpath']['album'] ? ' selected>' : '>').$entry.'</option>';
			}
		}
		$fontlist['portal'] .= '</select>';
		$fontlist['forum'] .= '</select>';
		$fontlist['album'] .= '</select>';
		$checkwm['portal'] = array($setting['watermarkstatus']['portal'] => 'checked');
		$checkwm['forum'] = array($setting['watermarkstatus']['forum'] => 'checked');
		$checkwm['album'] = array($setting['watermarkstatus']['album'] => 'checked');
		showtableheader('setting_imgwater_image_watermarks_portal', '', 'id="portal"'.($_GET['anchor'] != 'portal' ? ' style="display: none"' : ''));
		$fontlist['portal'] = '<select name="settingnew[watermarktext][fontpath][portal]">' . $fontlist['portal'];
		showhiddenfields(array('imagelib' => $_G['setting']['imagelib']));
		showsetting('setting_imgwater_image_watermarkstatus', '', '', '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="0" '.$checkwm['portal'][0].'>'.$lang['setting_imgwater_image_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="1" '.$checkwm['portal'][1].'> #1</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="2" '.$checkwm['portal'][2].'> #2</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="3" '.$checkwm['portal'][3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="4" '.$checkwm['portal'][4].'> #4</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="5" '.$checkwm['portal'][5].'> #5</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="6" '.$checkwm['portal'][6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="7" '.$checkwm['portal'][7].'> #7</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="8" '.$checkwm['portal'][8].'> #8</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][portal]" value="9" '.$checkwm['portal'][9].'> #9</td></tr></table>');
		showsetting('setting_imgwater_image_watermarkminwidthheight', array('settingnew[watermarkminwidth][portal]', 'settingnew[watermarkminheight][portal]'), array(intval($setting['watermarkminwidth']['portal']), intval($setting['watermarkminheight']['portal'])), 'multiply');
		showsetting('setting_imgwater_image_watermarktype', array('settingnew[watermarktype][portal]', array(
			array('gif', $lang['setting_imgwater_image_watermarktype_gif'], array('watermarktypeext_portal' => 'none')),
			array('png', $lang['setting_imgwater_image_watermarktype_png'], array('watermarktypeext_portal' => 'none')),
			array('text', $lang['setting_imgwater_image_watermarktype_text'], array('watermarktypeext_portal' => ''))
		)), $setting['watermarktype']['portal'], 'mradio');
		showsetting('setting_imgwater_image_watermarktrans', 'settingnew[watermarktrans][portal]', $setting['watermarktrans']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarkquality', 'settingnew[watermarkquality][portal]', $setting['watermarkquality']['portal'], 'text');
		showtagheader('tbody', 'watermarktypeext_portal', $setting['watermarktype']['portal'] == 'text', 'sub');
		showsetting('setting_imgwater_image_watermarktext_text', 'settingnew[watermarktext][text][portal]', $setting['watermarktext']['text']['portal'], 'textarea');
		showsetting('setting_imgwater_image_watermarktext_fontpath', '', '', $fontlist['portal']);
		showsetting('setting_imgwater_image_watermarktext_size', 'settingnew[watermarktext][size][portal]', $setting['watermarktext']['size']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_angle', 'settingnew[watermarktext][angle][portal]', $setting['watermarktext']['angle']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_color', 'settingnew[watermarktext][color][portal]', $setting['watermarktext']['color']['portal'], 'color');
		showsetting('setting_imgwater_image_watermarktext_shadowx', 'settingnew[watermarktext][shadowx][portal]', $setting['watermarktext']['shadowx']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowy', 'settingnew[watermarktext][shadowy][portal]', $setting['watermarktext']['shadowy']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowcolor', 'settingnew[watermarktext][shadowcolor][portal]', $setting['watermarktext']['shadowcolor']['portal'], 'color');
		showsetting('setting_imgwater_image_watermarktext_imtranslatex', 'settingnew[watermarktext][translatex][portal]', $setting['watermarktext']['translatex']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imtranslatey', 'settingnew[watermarktext][translatey][portal]', $setting['watermarktext']['translatey']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewx', 'settingnew[watermarktext][skewx][portal]', $setting['watermarktext']['skewx']['portal'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewy', 'settingnew[watermarktext][skewy][portal]', $setting['watermarktext']['skewy']['portal'], 'text');
		showtagfooter('tbody');
		showsetting('setting_imgwater_preview', '', '', cplang('setting_imgwater_preview_portal'));
		showtablefooter();

		showtableheader('setting_imgwater_image_watermarks_forum', '', 'id="forum"'.($_GET['anchor'] != 'forum' ? ' style="display: none"' : ''));
		$fontlist['forum'] = '<select name="settingnew[watermarktext][fontpath][forum]">' . $fontlist['forum'];
		showsetting('setting_imgwater_image_watermarkstatus', '', '', '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="0" '.$checkwm['forum'][0].'>'.$lang['setting_imgwater_image_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="1" '.$checkwm['forum'][1].'> #1</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="2" '.$checkwm['forum'][2].'> #2</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="3" '.$checkwm['forum'][3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="4" '.$checkwm['forum'][4].'> #4</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="5" '.$checkwm['forum'][5].'> #5</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="6" '.$checkwm['forum'][6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="7" '.$checkwm['forum'][7].'> #7</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="8" '.$checkwm['forum'][8].'> #8</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][forum]" value="9" '.$checkwm['forum'][9].'> #9</td></tr></table>');
		showsetting('setting_imgwater_image_watermarkminwidthheight', array('settingnew[watermarkminwidth][forum]', 'settingnew[watermarkminheight][forum]'), array(intval($setting['watermarkminwidth']['forum']), intval($setting['watermarkminheight']['forum'])), 'multiply');
		showsetting('setting_imgwater_image_watermarktype', array('settingnew[watermarktype][forum]', array(
			array('gif', $lang['setting_imgwater_image_watermarktype_gif'], array('watermarktypeext_forum' => 'none')),
			array('png', $lang['setting_imgwater_image_watermarktype_png'], array('watermarktypeext_forum' => 'none')),
			array('text', $lang['setting_imgwater_image_watermarktype_text'], array('watermarktypeext_forum' => ''))
		)), $setting['watermarktype']['forum'], 'mradio');
		showsetting('setting_imgwater_image_watermarktrans', 'settingnew[watermarktrans][forum]', $setting['watermarktrans']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarkquality', 'settingnew[watermarkquality][forum]', $setting['watermarkquality']['forum'], 'text');
		showtagheader('tbody', 'watermarktypeext_forum', $setting['watermarktype']['forum'] == 'text', 'sub');
		showsetting('setting_imgwater_image_watermarktext_text', 'settingnew[watermarktext][text][forum]', $setting['watermarktext']['text']['forum'], 'textarea');
		showsetting('setting_imgwater_image_watermarktext_fontpath', '', '', $fontlist['forum']);
		showsetting('setting_imgwater_image_watermarktext_size', 'settingnew[watermarktext][size][forum]', $setting['watermarktext']['size']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_angle', 'settingnew[watermarktext][angle][forum]', $setting['watermarktext']['angle']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_color', 'settingnew[watermarktext][color][forum]', $setting['watermarktext']['color']['forum'], 'color');
		showsetting('setting_imgwater_image_watermarktext_shadowx', 'settingnew[watermarktext][shadowx][forum]', $setting['watermarktext']['shadowx']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowy', 'settingnew[watermarktext][shadowy][forum]', $setting['watermarktext']['shadowy']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowcolor', 'settingnew[watermarktext][shadowcolor][forum]', $setting['watermarktext']['shadowcolor']['forum'], 'color');
		showsetting('setting_imgwater_image_watermarktext_imtranslatex', 'settingnew[watermarktext][translatex][forum]', $setting['watermarktext']['translatex']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imtranslatey', 'settingnew[watermarktext][translatey][forum]', $setting['watermarktext']['translatey']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewx', 'settingnew[watermarktext][skewx][forum]', $setting['watermarktext']['skewx']['forum'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewy', 'settingnew[watermarktext][skewy][forum]', $setting['watermarktext']['skewy']['forum'], 'text');
		showtagfooter('tbody');
		showsetting('setting_imgwater_preview', '', '', cplang('setting_imgwater_preview_forum'));
		showtablefooter();

		showtableheader('setting_imgwater_image_watermarks_album', '', 'id="album"'.($_GET['anchor'] != 'album' ? ' style="display: none"' : ''));
		$fontlist['album'] = '<select name="settingnew[watermarktext][fontpath][album]">' . $fontlist['album'];
		showsetting('setting_imgwater_image_watermarkstatus', '', '', '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="0" '.$checkwm['album'][0].'>'.$lang['setting_imgwater_image_watermarkstatus_none'].'</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="1" '.$checkwm['album'][1].'> #1</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="2" '.$checkwm['album'][2].'> #2</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="3" '.$checkwm['album'][3].'> #3</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="4" '.$checkwm['album'][4].'> #4</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="5" '.$checkwm['album'][5].'> #5</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="6" '.$checkwm['album'][6].'> #6</td></tr><tr><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="7" '.$checkwm['album'][7].'> #7</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="8" '.$checkwm['album'][8].'> #8</td><td><input class="radio" type="radio" name="settingnew[watermarkstatus][album]" value="9" '.$checkwm['album'][9].'> #9</td></tr></table>');
		showsetting('setting_imgwater_image_watermarkminwidthheight', array('settingnew[watermarkminwidth][album]', 'settingnew[watermarkminheight][album]'), array(intval($setting['watermarkminwidth']['album']), intval($setting['watermarkminheight']['album'])), 'multiply');
		showsetting('setting_imgwater_image_watermarktype', array('settingnew[watermarktype][album]', array(
			array('gif', $lang['setting_imgwater_image_watermarktype_gif'], array('watermarktypeext_album' => 'none')),
			array('png', $lang['setting_imgwater_image_watermarktype_png'], array('watermarktypeext_album' => 'none')),
			array('text', $lang['setting_imgwater_image_watermarktype_text'], array('watermarktypeext_album' => ''))
		)), $setting['watermarktype']['album'], 'mradio');
		showsetting('setting_imgwater_image_watermarktrans', 'settingnew[watermarktrans][album]', $setting['watermarktrans']['album'], 'text');
		showsetting('setting_imgwater_image_watermarkquality', 'settingnew[watermarkquality][album]', $setting['watermarkquality']['album'], 'text');
		showtagheader('tbody', 'watermarktypeext_album', $setting['watermarktype']['album'] == 'text', 'sub');
		showsetting('setting_imgwater_image_watermarktext_text', 'settingnew[watermarktext][text][album]', $setting['watermarktext']['text']['album'], 'textarea');
		showsetting('setting_imgwater_image_watermarktext_fontpath', '', '', $fontlist['album']);
		showsetting('setting_imgwater_image_watermarktext_size', 'settingnew[watermarktext][size][album]', $setting['watermarktext']['size']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_angle', 'settingnew[watermarktext][angle][album]', $setting['watermarktext']['angle']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_color', 'settingnew[watermarktext][color][album]', $setting['watermarktext']['color']['album'], 'color');
		showsetting('setting_imgwater_image_watermarktext_shadowx', 'settingnew[watermarktext][shadowx][album]', $setting['watermarktext']['shadowx']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowy', 'settingnew[watermarktext][shadowy][album]', $setting['watermarktext']['shadowy']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_shadowcolor', 'settingnew[watermarktext][shadowcolor][album]', $setting['watermarktext']['shadowcolor']['album'], 'color');
		showsetting('setting_imgwater_image_watermarktext_imtranslatex', 'settingnew[watermarktext][translatex][album]', $setting['watermarktext']['translatex']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imtranslatey', 'settingnew[watermarktext][translatey][album]', $setting['watermarktext']['translatey']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewx', 'settingnew[watermarktext][skewx][album]', $setting['watermarktext']['skewx']['album'], 'text');
		showsetting('setting_imgwater_image_watermarktext_imskewy', 'settingnew[watermarktext][skewy][album]', $setting['watermarktext']['skewy']['album'], 'text');
		showtagfooter('tbody');
		showsetting('setting_imgwater_preview', '', '', cplang('setting_imgwater_preview_album'));
		showtablefooter();
		showtableheader();
	} elseif($operation == 'search') {

		$setting['search'] = dunserialize($setting['search']);
		showtableheader('setting_search_status', 'fixpadding');
		showsubtitle(array('setting_search_onoff', 'search_item_name', 'setting_serveropti_searchctrl', 'setting_serveropti_maxspm', 'setting_serveropti_maxsearchresults'));
		if(helper_access::check_module('portal')) {
			$search_portal = array(
				$setting['search']['portal']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][portal][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][portal][status]" value="1" />',
				cplang('setting_search_status_portal'),
				'<input type="text" class="txt" name="settingnew[search][portal][searchctrl]" value="'.$setting['search']['portal']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][portal][maxspm]" value="'.$setting['search']['portal']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][portal][maxsearchresults]" value="'.$setting['search']['portal']['maxsearchresults'].'" />',
			);
		}
		$search_forum = array(
			$setting['search']['forum']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][forum][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][forum][status]" value="1" />',
			cplang('setting_search_status_forum'),
			'<input type="text" class="txt" name="settingnew[search][forum][searchctrl]" value="'.$setting['search']['forum']['searchctrl'].'" />',
			'<input type="text" class="txt" name="settingnew[search][forum][maxspm]" value="'.$setting['search']['forum']['maxspm'].'" />',
			'<input type="text" class="txt" name="settingnew[search][forum][maxsearchresults]" value="'.$setting['search']['forum']['maxsearchresults'].'" />',
		);

		if(helper_access::check_module('blog')) {
			$search_blog = array(
				$setting['search']['blog']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][blog][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][blog][status]" value="1" />',
				cplang('setting_search_status_blog'),
				'<input type="text" class="txt" name="settingnew[search][blog][searchctrl]" value="'.$setting['search']['blog']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][blog][maxspm]" value="'.$setting['search']['blog']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][blog][maxsearchresults]" value="'.$setting['search']['blog']['maxsearchresults'].'" />',
			);
		}
		if(helper_access::check_module('album')) {
			$search_album = array(
				$setting['search']['album']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][album][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][album][status]" value="1" />',
				cplang('setting_search_status_album'),
				'<input type="text" class="txt" name="settingnew[search][album][searchctrl]" value="'.$setting['search']['album']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][album][maxspm]" value="'.$setting['search']['album']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][album][maxsearchresults]" value="'.$setting['search']['album']['maxsearchresults'].'" />',
			);
		}
		if(helper_access::check_module('group')) {
			$search_group = array(
				$setting['search']['group']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][group][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][group][status]" value="1" />',
				cplang('setting_search_status_group'),
				'<input type="text" class="txt" name="settingnew[search][group][searchctrl]" value="'.$setting['search']['group']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][group][maxspm]" value="'.$setting['search']['group']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][group][maxsearchresults]" value="'.$setting['search']['group']['maxsearchresults'].'" />',
			);
		}
		if(helper_access::check_module('collection')) {
			$search_collection = array(
				$setting['search']['collection']['status'] ? '<input type="checkbox" class="checkbox" name="settingnew[search][collection][status]" value="1" checked="checked" />' : '<input type="checkbox" class="checkbox" name="settingnew[search][collection][status]" value="1" />',
				cplang('setting_search_status_collection'),
				'<input type="text" class="txt" name="settingnew[search][collection][searchctrl]" value="'.$setting['search']['collection']['searchctrl'].'" />',
				'<input type="text" class="txt" name="settingnew[search][collection][maxspm]" value="'.$setting['search']['collection']['maxspm'].'" />',
				'<input type="text" class="txt" name="settingnew[search][collection][maxsearchresults]" value="'.$setting['search']['collection']['maxsearchresults'].'" />',
			);
		}
		showtablerow('', array('width="100"', 'width="120"', 'width="120"', 'width="120"'), $search_portal);
		showtablerow('', '', $search_forum);
		showtablerow('', '', $search_blog);
		showtablerow('', '', $search_album);
		showtablerow('', '', $search_group);
		showtablerow('', '', $search_collection);
		showtablefooter();

		showtableheader('setting_search_srchsetting');
		showsetting('setting_search_srchcensor', 'settingnew[srchcensor]', $setting['srchcensor'], 'radio');
		showtablefooter();

		showtableheader('setting_search_srchhotkeywords');
		showsetting('setting_search_srchhotkeywords', 'settingnew[srchhotkeywords]', $setting['srchhotkeywords'], 'textarea');

		showtablefooter();

		showtableheader('settings_sphinx', 'fixpadding');
		showsetting('settings_sphinx_sphinxon', 'settingnew[sphinxon]', $setting['sphinxon'], 'radio');
		showsetting('settings_sphinx_sphinxhost', 'settingnew[sphinxhost]', $setting['sphinxhost'], 'text');
		showsetting('settings_sphinx_sphinxport', 'settingnew[sphinxport]', $setting['sphinxport'], 'text');
		showsetting('settings_sphinx_sphinxsubindex', 'settingnew[sphinxsubindex]', $setting['sphinxsubindex'], 'text');
		showsetting('settings_sphinx_sphinxmsgindex', 'settingnew[sphinxmsgindex]', $setting['sphinxmsgindex'], 'text');
		showsetting('settings_sphinx_sphinxmaxquerytime', 'settingnew[sphinxmaxquerytime]', $setting['sphinxmaxquerytime'], 'text');
		showsetting('settings_sphinx_sphinxlimit', 'settingnew[sphinxlimit]', $setting['sphinxlimit'], 'text');
		$spx_ranks = array('SPH_RANK_PROXIMITY_BM25', 'SPH_RANK_BM25', 'SPH_RANK_NONE');
		$selectspxrank = '';
		$selectspxrank = '<select name="settingnew[sphinxrank]">';
		foreach($spx_ranks as $spx_rank) {
			$selectspxrank.= '<option value="'.$spx_rank.'"'.($spx_rank == $setting['sphinxrank'] ? 'selected="selected"' : '').'>'.$spx_rank.'</option>';
		}
		$selectspxrank .='</select>';
		showsetting('settings_sphinx_sphinxrank', '', '', $selectspxrank);
		showtablefooter();
		showtableheader();

	} elseif($operation == 'uc' && $isfounder) {

		$disable = !is_writeable(DISCUZ_ROOT . './config/config_ucenter.php');
		include DISCUZ_ROOT.'./config/config_ucenter.php';

		showtips('setting_uc_tips');
		showtableheader();
		showsetting('setting_uc_appid', 'settingnew[uc][appid]', UC_APPID, 'text', $disable);
		showsetting('setting_uc_key', 'settingnew[uc][key]', '********', 'text', $disable);
		showsetting('setting_uc_api', 'settingnew[uc][api]', UC_API, 'text', $disable);
		showsetting('setting_uc_ip', 'settingnew[uc][ip]', UC_IP, 'text', $disable);
		showsetting('setting_uc_connect', array('settingnew[uc][connect]', array(
			array('mysql', $lang['setting_uc_connect_mysql'], array('ucmysql' => '')),
			array('', $lang['setting_uc_connect_api'], array('ucmysql' => 'none')))), UC_CONNECT, 'mradio', $disable);
		if(strpos(UC_DBTABLEPRE, '.')) {
			$prestr = str_replace('`', '', UC_DBTABLEPRE);
			$uctablepre = substr($prestr, strrpos($prestr, '.')+1);
		}
		showtagheader('tbody', 'ucmysql', UC_CONNECT, 'sub');
		showsetting('setting_uc_dbhost', 'settingnew[uc][dbhost]', UC_DBHOST, 'text', $disable);
		showsetting('setting_uc_dbuser', 'settingnew[uc][dbuser]', UC_DBUSER, 'text', $disable);
		showsetting('setting_uc_dbpass', 'settingnew[uc][dbpass]', '********', 'text', $disable);
		showsetting('setting_uc_dbname', 'settingnew[uc][dbname]', UC_DBNAME, 'text', $disable);
		showsetting('setting_uc_dbtablepre', 'settingnew[uc][dbtablepre]', $uctablepre, 'text', $disable);
		showtagfooter('tbody');
		showsetting('setting_uc_activation', 'settingnew[ucactivation]', $setting['ucactivation'], 'radio', 0, 1);
		showsetting('setting_uc_fastactivation', 'settingnew[fastactivation]', $setting['fastactivation'], 'radio');
		showtagfooter('tbody');
		showsetting('setting_uc_avatarmethod', array('settingnew[avatarmethod]', array(
			array(0, $lang['setting_uc_avatarmethod_0']),
			array(1, $lang['setting_uc_avatarmethod_1']),
			)), $setting['avatarmethod'], 'mradio');

	} elseif($operation == 'ec') {

		showtableheader();
		showtitle('setting_ec_credittrade');
		showsetting('setting_ec_ratio', 'settingnew[ec_ratio]', $setting['ec_ratio'], 'text');
		showsetting('setting_ec_mincredits', 'settingnew[ec_mincredits]', $setting['ec_mincredits'], 'text');
		showsetting('setting_ec_maxcredits', 'settingnew[ec_maxcredits]', $setting['ec_maxcredits'], 'text');
		showsetting('setting_ec_maxcreditspermonth', 'settingnew[ec_maxcreditspermonth]', $setting['ec_maxcreditspermonth'], 'text');

	} elseif($operation == 'memory') {

		showtips('setting_memory_tips');
		showtableheader('setting_memory_status', 'fixpadding');
		showsubtitle(array('setting_memory_state_interface', 'setting_memory_state_extension', 'setting_memory_state_config', 'setting_memory_clear', ''));

		$do_clear_ok = $do == 'clear' ? cplang('setting_memory_do_clear') : '';
		$do_clear_link = '<a href="'.ADMINSCRIPT.'?action=setting&operation=memory&do=clear">'.cplang('setting_memory_clear').'</a>'.$do_clear_ok;

		$cache_extension = C::memory()->extension;
		$cache_config = C::memory()->config;
		$cache_type = C::memory()->type;

		$dir = DISCUZ_ROOT.'./source/class/memory';
		$qaadir = dir($dir);
		$cachelist = array();
		while($entry = $qaadir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^memory\_driver\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				$cache = str_replace(array('.php', 'memory_driver_'), '', $entry);
				$class_name = 'memory_driver_'.$cache;
				$memory = new $class_name();
				$available = is_array($cache_config[$cache]) ? !empty($cache_config[$cache]['server']) : !empty($cache_config[$cache]);
				$cachelist[] = array($memory->cacheName,
					$memory->env($config) ? cplang('setting_memory_php_enable') : cplang('setting_memory_php_disable'),
					$available ? cplang('open') : cplang('closed'),
					$cache_type == $memory->cacheName ? $do_clear_link : '--'
				);
			}
		}

		foreach($cachelist as $cache) {
			showtablerow('', array('width="100"', 'width="120"', 'width="120"'), $cache);
		}
		showtablefooter();

		if(!isset($setting['memory'])) {
			C::t('common_setting')->update('memory', '');
			$setting['memory'] = '';
		}

		if($do == 'clear') {
			C::memory()->clear();
		}

		$setting['memory'] = dunserialize($setting['memory']);
		showtableheader('setting_memory_function', 'fixpadding');
		showsubtitle(array('setting_memory_func', 'setting_memory_func_enable', 'setting_memory_func_ttl', ''));

		foreach (getmemorycachekeys() as $skey) {
			$ttl = isset($setting['memory'][$skey]) ? intval($setting['memory'][$skey]) : '';
			showtablerow('', array('width="120"', 'width="120"', 'width="120"', ''), array(
					cplang('setting_memory_func_'.$skey),
					'<input type="checkbox" class="checkbox" name="settingnew[memory]['.$skey.'][enable]" '.($ttl !== ''? 'checked' : '').' value="1">',
					'<input type="text" class="txt" name="settingnew[memory]['.$skey.'][ttl]" value="'.$ttl.'">',cplang('setting_memory_func_'.$skey.'_comment'),
					));
		}


	} elseif($operation == 'memorydata') {

		$cache_keys = getmemorycachekeys();
		if(submitcheck('memorydatasubmit')) {
			$flag = 0;
			foreach($cache_keys as $k) {
				if(($id = $_GET[$k.'_id'])) {
					if($k == 'common_member') {
						$uid = intval($id);
						C::t('common_member')->clear_cache($uid);
						C::t('common_member_status')->clear_cache($uid);
						C::t('common_member_count')->clear_cache($uid);
						C::t('common_member_profile')->clear_cache($uid);
						C::t('common_member_field_home')->clear_cache($uid);
						C::t('common_member_field_forum')->clear_cache($uid);
						C::t('common_member_verify')->clear_cache($uid);
					} elseif($k == 'forum_thread_forumdisplay') {
						memory('rm', $id, 'forumdisplay_');
					} elseif($k == 'forumindex') {
						memory('rm', 'forum_index_page_'.$id);
					} elseif($k == 'diyblock' || $k == 'diyblockoutput') {
						C::t('common_block')->clear_cache($id);
					} else {
						C::t($k)->clear_cache($id);
					}
					$flag = 1;
				}
			}
			if($flag) {
				cpmsg('setting_memory_rm_succeed', 'action=setting&operation=memorydata', 'succeed', '', FALSE);
			} else {
				cpmsg('setting_memory_rm_error', 'action=setting&operation=memorydata', 'error', '', FALSE);
			}
		}

		$setting['memory'] = dunserialize($setting['memory']);
		showtableheader('setting_memorydata', 'fixpadding');
		showsubtitle(array('setting_memory_func', 'setting_memorydata_rm_cache_key', '', ''));

		foreach ($cache_keys as $skey) {
			if(isset($setting['memory'][$skey])) {
				showtablerow('', array('width="120"', 'width="120"', '', ''), array(
						cplang('setting_memory_func_'.$skey),
						'<input type="text" class="txt" name="'.$skey.'_id" id="'.$skey.'_id" value="">',
						cplang('setting_memory_data_'.$skey.'_comment'),
						));
			}
		}
		showsubmit('memorydatasubmit');
		showtablefooter();
		showtagfooter('div');
		showformfooter();
		exit;

	}  elseif($operation == 'ranklist') {

		$setting['ranklist'] = dunserialize($setting['ranklist']);
		showtableheader('', 'nobottom', 'id="all"');
		showsetting('setting_ranklist_status', 'settingnew[ranklist][status]', $setting['ranklist']['status'], 'radio');
		showsetting('setting_ranklist_index_cache_time', 'settingnew[ranklist][cache_time]', $setting['ranklist']['cache_time'], 'text');
		showsetting('setting_ranklist_index_select', array('settingnew[ranklist][index_select]', array(array('all',cplang('dateline_all')), array('thismonth',cplang('thismonth')), array('thisweek',cplang('thisweek')), array('today',cplang('today')))), $setting['ranklist']['index_select'], 'select');
		showsetting('setting_ranklist_ignorefid', 'settingnew[ranklist][ignorefid]', $setting['ranklist']['ignorefid'], 'text');
		showsetting('setting_ranklist_member_show', 'settingnew[ranklist][membershow]', $setting['ranklist']['membershow'], 'radio', 0, 1);
		showsetting('setting_ranklist_member_show_announcement', 'settingnew[ranklist][membershowannouncement]', $setting['ranklist']['membershowannouncement'], 'textarea');
		showtablefooter();

		showtableheader('setting_ranklist_block_set', 'fixpadding', 'id="other"');
		showsubtitle(array('setting_credits_available', 'setting_ranklist_block_name', 'setting_ranklist_cache_time', 'setting_ranklist_show_num'), '');
		$ranklist = array('member','thread','blog','poll','activity','picture','forum','group');

		if(!is_array($setting['ranklist'])) {
			$setting['ranklist'] = array();
		}
		foreach($ranklist as $i) {
			showtablerow('', array('width="40"', 'class="td22"', 'class="td21"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"settingnew[ranklist][$i][available]\" value=\"1\" ".($setting['ranklist'][$i]['available'] ? 'checked' : '')." />",
				cplang('setting_ranklist_'.$i),
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[ranklist][$i][cache_time]\" value=\"{$setting['ranklist'][$i]['cache_time']}\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"settingnew[ranklist][$i][show_num]\" value=\"{$setting['ranklist'][$i]['show_num']}\">"
			));
		}
		showtablerow('', 'colspan="10" class="lineheight"', $lang['setting_ranklist_block_comment']);
		showtablefooter();

		showtableheader('', 'notop');
		showsubmit('settingsubmit');
		showtablefooter();

		showtableheader('', 'notop');
		$ranklistarray = array();
		$ranklistarray[] = array('index', cplang('setting_ranklist_index'));
		foreach($ranklist as $k) {
			$ranklistarray[] = array($k, cplang('setting_ranklist_'.$k));
		}
		showsetting('setting_ranklist_update_cache_choose', array('update_ranklist_cache', $ranklistarray), '', 'mcheckbox');
		showtablerow('', 'colspan="10" class="lineheight"', $lang['setting_ranklist_cache_comment']);
		showtablefooter();

		showtableheader('', 'notop');
		showhiddenfields(array('updateranklistcache' => 0));
		showsubmit('', '', '<input type="submit" class="btn" name="settingsubmit" value="'.cplang('setting_ranklist_update_cache').'" onclick="this.form.updateranklistcache.value=1">');
		showtablefooter();

		showformfooter();
		exit;

	} elseif ($operation == 'mobile'){
		$setting['mobile'] = dunserialize($setting['mobile']);
		showtips('setting_mobile_status_tips');
		showtableheader('setting_mobile_status', '', 'id="status"'.($_GET['anchor'] != 'status' ? ' style="display: none"' : ''));
		showsetting('setting_mobile_allowmobile', array('settingnew[mobile][allowmobile]', array(
				array(1, $lang['yes'], array('mobileext' => '')),
				array(0, $lang['no'], array('mobileext' => 'none'))
			), TRUE), $setting['mobile']['allowmobile'] ? $setting['mobile']['allowmobile'] : 0, 'mradio');
		showtagheader('tbody', 'mobileext', $setting['mobile']['allowmobile'], 'sub');
		showsetting('setting_mobile_mobileforward', 'settingnew[mobile][mobileforward]', $setting['mobile']['mobileforward'], 'radio');
        	showsetting('setting_mobile_otherindex', 'settingnew[mobile][otherindex]', $setting['mobile']['otherindex'], 'radio');
		showsetting('setting_mobile_register', 'settingnew[mobile][mobileregister]', $setting['mobile']['mobileregister'], 'radio');
		showsetting('setting_mobile_hotthread', 'settingnew[mobile][mobilehotthread]', $setting['mobile']['mobilehotthread'], 'radio');
		showsetting('setting_mobile_displayorder3', 'settingnew[mobile][mobiledisplayorder3]', $setting['mobile']['mobiledisplayorder3'], 'radio');
		showsetting('setting_mobile_simpletype', 'settingnew[mobile][mobilesimpletype]', $setting['mobile']['mobilesimpletype'], 'radio');
		showsetting('setting_mobile_topicperpage', 'settingnew[mobile][mobiletopicperpage]', $setting['mobile']['mobiletopicperpage'] ? $setting['mobile']['mobiletopicperpage'] : 10, 'text');
		showsetting('setting_mobile_postperpage', 'settingnew[mobile][mobilepostperpage]', $setting['mobile']['mobilepostperpage'] ? $setting['mobile']['mobilepostperpage'] : 5, 'text');
		showsetting('setting_mobile_cachetime', 'settingnew[mobile][mobilecachetime]', $setting['mobile']['mobilecachetime'] ? $setting['mobile']['mobilecachetime'] : 0, 'text');
		showsetting('setting_mobile_index_forumview', array('settingnew[mobile][mobileforumview]', array(
				array(1, $lang['pack']),
				array(0, $lang['unwind'])
			)), $setting['mobile']['mobileforumview'] ? $setting['mobile']['mobileforumview'] : 0, 'mradio');
		showsetting('setting_mobile_come_from', 'settingnew[mobile][mobilecomefrom]', $setting['mobile']['mobilecomefrom'], 'textarea');
		showsetting('setting_mobile_wml', 'settingnew[mobile][wml]', $setting['mobile']['wml'], 'radio');
		showsetting('setting_mobile_allowmnew', 'settingnew[mobile][allowmnew]', $setting['mobile']['allowmnew'], 'radio');
		showtagfooter('tbody');
		showsubmit('settingsubmit');
		showformfooter();
		showtablefooter();
		exit;

	} elseif ($operation == 'antitheft'){
		if($_GET['anchor'] == 'iplist') {

			if(submitcheck('antitheftsubmit', true)) {
				$_GET['ips'] = dintval($_GET['ips'], true);
				$url = 'action=setting&operation=antitheft&anchor=iplist&page='.$page;
				if(empty($_GET['ips'])) {
					cpmsg('setting_antitheft_choose_ip', $url, 'error');
				}
				$antitheftsetting = C::t('common_setting')->fetch('antitheftsetting', true);
				if($_GET['optype'] == 'white' || $_GET['optype'] == 'black') {
					$ips = explode("\n", $antitheftsetting[$_GET['optype']]);
					$ips = array_diff(array_map('long2ip', $_GET['ips']), $ips);
					if($ips) {
						$antitheftsetting[$_GET['optype']] = $antitheftsetting[$_GET['optype']]."\n".implode("\n", $ips);
						C::t('common_setting')->update('antitheftsetting', $antitheftsetting);
						updatecache('antitheft');
					}
					C::t('common_visit')->delete($_GET['ips']);
					cpmsg('setting_antitheft_add_'.$_GET['optype'], $url, 'succeed');
				} elseif($_GET['optype'] == 'delete') {
					C::t('common_visit')->delete($_GET['ips']);
					cpmsg('setting_antitheft_delete_view', $url, 'succeed');
				} else {
					cpmsg('setting_antitheft_choose_optype', $url, 'error');
				}

			} else {
				$perpage = 30;
				$start = ($page-1)*$perpage;
				$mpurl .= '&perpage='.$perpage;
				$mpurl = ADMINSCRIPT.'?action=setting&operation=antitheft&anchor='.$_GET['anchor'];

				showformheader('setting&operation=antitheft&anchor='.$_GET['anchor']);
				showtableheader('setting_antitheft_iplist');
				showsubtitle(array('', 'setting_antitheft_ip', 'setting_antitheft_view', 'setting_antitheft_op'));

				$multipage = '';
				$count = C::t('common_visit')->count();
				if($count) {
					require_once libfile('function/misc');
					foreach(C::t('common_visit')->range($start, $perpage) as $value) {
						$ip = long2ip($value['ip']);
						showtablerow('', array('class="td25"', 'class=""', 'class="td28"'), array(
								"<input type=\"checkbox\" class=\"checkbox\" name=\"ips[]\" value=\"$value[ip]\">",
								"$ip ".convertip($ip),
								$value['view'],
								"<a href=\"$mpurl&optype=white&ips[]=$value[ip]&antitheftsubmit=yes\">$lang[setting_antitheft_addwhitelist]</a> |
								 <a href=\"$mpurl&optype=black&ips[]=$value[ip]&antitheftsubmit=yes\">$lang[setting_antitheft_addblacklist]</a> |
								 <a href=\"$mpurl&optype=delete&ips[]=$value[ip]&antitheftsubmit=yes\">$lang[delete]</a>
								",
							));
					}
					$multipage = multi($count, $perpage, $page, $mpurl);
				}

				$batchradio = '<input type="radio" name="optype" value="white" id="op_white" class="radio" /><label for="op_white">'.cplang('setting_antitheft_addwhitelist').'</label>&nbsp;&nbsp;';
				$batchradio .= '<input type="radio" name="optype" value="black" id="op_black" class="radio" /><label for="op_black">'.cplang('setting_antitheft_addblacklist').'</label>&nbsp;&nbsp;';
				$batchradio .= '<input type="radio" name="optype" value="delete" id="op_remove" class="radio" /><label for="op_remove">'.cplang('delete').'</label>&nbsp;&nbsp;<input type="hidden" name="antitheftsubmit" value="yes" />';
				showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ips\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'
							.$batchradio.'<input type="submit" class="btn" name="antitheftbutton" value="'.cplang('submit').'" />', $multipage);
				showtablefooter();
				showformfooter();
			}
		} else {
			showtips('setting_antitheft_tips');
			$setting['antitheft'] = dunserialize($setting['antitheft']);
			$setting['antitheftsetting'] = dunserialize($setting['antitheftsetting']);
			showtableheader('setting_antitheft_status', 'fixpadding');

			showsetting('setting_antitheft_allow', array('settingnew[antitheft][allow]', array(
					array(1, $lang['yes'], array('antitheftext' => '')),
					array(0, $lang['no'], array('antitheftext' => 'none'))
				), TRUE), !empty($setting['antitheft']['allow']) ? $setting['antitheft']['allow'] : 0, 'mradio');
			showtagheader('tbody', 'antitheftext', !empty($setting['antitheft']['allow']), 'sub');
			showsetting('setting_antitheft_24_max', 'settingnew[antitheft][max]', $setting['antitheft']['max'], 'text');
			showsetting('setting_antitheft_white', 'settingnew[antitheftwhite]', $setting['antitheftsetting']['white'], 'textarea');
			showsetting('setting_antitheft_black', 'settingnew[antitheftblack]', $setting['antitheftsetting']['black'], 'textarea');
			showsetting('setting_antitheft_disable', array('settingnew[antitheft][disable]', array(
						array('thread', $lang['setting_antitheft_disable_thread'], '1'),
						array('article', $lang['setting_antitheft_disable_article'], '1'),
						array('blog', $lang['setting_antitheft_disable_blog'], '1'),
						)), $setting['antitheft']['disable'], 'omcheckbox');
			showtagfooter('tbody');
			showtablefooter();
			showsubmit('settingsubmit');
		}
		exit;

	} else {
		if($operation == 'mail' || $operation == 'uc') {
			cpmsg('founder_action');
		} else {
			cpmsg('undefined_action');
		}
	}

	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();

} else {

	$settingnew = $_GET['settingnew'];

	if($operation == 'credits') {
		$extcredits_exists = 0;
		foreach($settingnew['extcredits'] as $val) {
			if(isset($val['available']) && $val['available'] == 1) {
				$extcredits_exists = 1;
				break;
			}
		}
		if(!$extcredits_exists) {
			cpmsg('setting_extcredits_must_available');
		}
		if($settingnew['report_reward']) {
			$settingnew['report_reward']['min'] = intval($settingnew['report_reward']['min']);
			$settingnew['report_reward']['max'] = intval($settingnew['report_reward']['max']);
			if($settingnew['report_reward']['min'] > $settingnew['report_reward']['max']) {
				unset($settingnew['report_reward']);
			}
			if($settingnew['report_reward']['min'] == $settingnew['report_reward']['max']) {
				$settingnew['report_reward'] = array('min' => '', 'max' => '');
			}
			$settingnew['report_reward'] = serialize($settingnew['report_reward']);
		}
		$settingnew['creditspolicy'] = @dunserialize($setting['creditspolicy']);
		$settingnew['creditspolicy']['lowerlimit'] = array();
		foreach($settingnew['lowerlimit'] as $key => $value) {
			if($settingnew['extcredits'][$key]['available']) {
				$settingnew['creditspolicy']['lowerlimit'][$key] = (float)$value;
			}
		}
		unset($settingnew['lowerlimit']);
	}

	if($operation == 'uc' && is_writeable('./config/config_ucenter.php') && $isfounder) {
		require_once './config/config_ucenter.php';

		$ucdbpassnew = $settingnew['uc']['dbpass'] == '********' ? addslashes(UC_DBPW) : addslashes($settingnew['uc']['dbpass']);
		$settingnew['uc']['key'] = addslashes($settingnew['uc']['key'] == '********' ? addslashes(UC_KEY) : $settingnew['uc']['key']);

		if(!is_numeric($settingnew['uc']['appid'])) {
			cpmsg('uc_config_appid_error', '', 'error');
		}

		if(!empty($settingnew['uc']['ip']) && !filter_var($settingnew['uc']['ip'], FILTER_VALIDATE_IP)) {
			cpmsg('uc_config_ip_error', '', 'error');
		}

		if(function_exists("mysql_connect") && ini_get("mysql.allow_local_infile")=="1" && constant("UC_DBHOST") != $settingnew['uc']['dbhost']){
			cpmsg('uc_config_load_data_local_infile_error', '', 'error');
		}

		if($settingnew['uc']['connect']) {
			$uc_dblink = function_exists("mysql_connect") ? @mysql_connect($settingnew['uc']['dbhost'], $settingnew['uc']['dbuser'], $ucdbpassnew, 1) : new mysqli($settingnew['uc']['dbhost'], $settingnew['uc']['dbuser'], $ucdbpassnew);
			if(!$uc_dblink) {
				cpmsg('uc_database_connect_error', '', 'error');
			} else {
				if(function_exists("mysql_connect")) {
					mysql_close($uc_dblink);
				} else {
					$uc_dblink->close();
				}
			}
		}

		$settingnew['uc']['api'] = str_replace(array('\'', '"', '\\', "\0", "\n", "\r"), '', $settingnew['uc']['api']);

		$fp = fopen('./config/config_ucenter.php', 'r');
		$configfile = fread($fp, filesize('./config/config_ucenter.php'));
		$configfile = trim($configfile);
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		fclose($fp);

		$connect = '';
		$settingnew['uc'] = daddslashes($settingnew['uc']);
		if($settingnew['uc']['connect']) {
			$connect = 'mysql';
			$samelink = ($dbhost == $settingnew['uc']['dbhost'] && $dbuser == $settingnew['uc']['dbuser'] && $dbpw == $ucdbpassnew);
			$samecharset = !($dbcharset == 'gbk' && UC_DBCHARSET == 'latin1' || $dbcharset == 'latin1' && UC_DBCHARSET == 'gbk');
			$configfile = str_replace("define('UC_DBHOST', '".addslashes(UC_DBHOST)."')", "define('UC_DBHOST', '".$settingnew['uc']['dbhost']."')", $configfile);
			$configfile = str_replace("define('UC_DBUSER', '".addslashes(UC_DBUSER)."')", "define('UC_DBUSER', '".$settingnew['uc']['dbuser']."')", $configfile);
			$configfile = str_replace("define('UC_DBPW', '".addslashes(UC_DBPW)."')", "define('UC_DBPW', '".$ucdbpassnew."')", $configfile);
			if(!preg_match('/^[\w\d\_]+$/', $settingnew['uc']['dbtablepre']) || !preg_match('/^[\w\d\_]+$/', $settingnew['uc']['dbname'])) {
				cpmsg('uc_config_write_error', '', 'error');
			}
			$configfile = str_replace("define('UC_DBNAME', '".addslashes(UC_DBNAME)."')", "define('UC_DBNAME', '".$settingnew['uc']['dbname']."')", $configfile);
			$configfile = str_replace("define('UC_DBTABLEPRE', '".addslashes(UC_DBTABLEPRE)."')", "define('UC_DBTABLEPRE', '`".$settingnew['uc']['dbname'].'`.'.$settingnew['uc']['dbtablepre']."')", $configfile);
		}
		$configfile = str_replace("define('UC_CONNECT', '".addslashes(UC_CONNECT)."')", "define('UC_CONNECT', '".$connect."')", $configfile);
		$configfile = str_replace("define('UC_KEY', '".addslashes(UC_KEY)."')", "define('UC_KEY', '".$settingnew['uc']['key']."')", $configfile);
		$configfile = str_replace("define('UC_API', '".addslashes(UC_API)."')", "define('UC_API', '".$settingnew['uc']['api']."')", $configfile);
		$configfile = str_replace("define('UC_IP', '".addslashes(UC_IP)."')", "define('UC_IP', '".$settingnew['uc']['ip']."')", $configfile);
		$configfile = str_replace("define('UC_APPID', '".addslashes(UC_APPID)."')", "define('UC_APPID', '".$settingnew['uc']['appid']."')", $configfile);

		$fp = fopen('./config/config_ucenter.php', 'w');
		if(!($fp = @fopen('./config/config_ucenter.php', 'w'))) {
			cpmsg('uc_config_write_error', '', 'error');
		}
		@fwrite($fp, trim($configfile));
		@fclose($fp);
	}

	isset($settingnew['regname']) && empty($settingnew['regname']) && $settingnew['regname'] = 'register';
	isset($settingnew['reglinkname']) && empty($settingnew['reglinkname']) && $settingnew['reglinkname'] = cplang('reglinkname_default');
	$nohtmlarray = array('bbname', 'regname', 'reglinkname', 'icp', 'sitemessage', 'site_qq');
	foreach($nohtmlarray as $k) {
		if(isset($settingnew[$k])) {
			$settingnew[$k] = dhtmlspecialchars($settingnew[$k]);
		}
	}

	if(isset($settingnew['statcode'])) {
		$settingnew['statcode'] = preg_replace('/language\s*=[\s|\'|\"]*php/is', '_', $settingnew['statcode']);
		$settingnew['statcode'] = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $settingnew['statcode']);
	}

	if($operation == 'access') {
		$settingnew['pwlength'] = intval($settingnew['pwlength']);
		$settingnew['regstatus'] = (array)$settingnew['regstatus'];
		$settingnew['regconnect'] = in_array('connect', $settingnew['regstatus']) ? 1 : 0;
		if(in_array('open', $settingnew['regstatus']) && in_array('invite', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 3;
		} elseif(in_array('open', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 1;
		} elseif(in_array('invite', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 2;
		} else {
			$settingnew['regstatus'] = 0;
		}
		$settingnew['regconnect'] = $settingnew['regconnect'] ? 1 : 0;

		$settingnew['welcomemsg'] = (array)$settingnew['welcomemsg'];
		if(in_array('1', $settingnew['welcomemsg']) && in_array('2', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 3;
		} elseif(in_array('1', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 1;
		} elseif(in_array('2', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 2;
		} else {
			$settingnew['welcomemsg'] = 0;
		}

		if(empty($settingnew['strongpw'])) {
			$settingnew['strongpw'] = array();
		}
	}

	if(isset($settingnew['censoruser'])) {
		$settingnew['censoruser'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingnew['censoruser']));
	}

	foreach(array('inviteconfig' => 'inviteipwhite', 'ipverifywhite', 'ipregctrl', 'ipaccess', 'adminipaccess') as $ipkey => $ipfield) {
		if(!is_int($ipkey)) {
			if(isset($settingnew[$ipkey][$ipfield])) {
				$ipfilterpointer = &$settingnew[$ipkey][$ipfield];
			}
		} else {
			if(isset($settingnew[$ipfield])) {
				$ipfilterpointer = &$settingnew[$ipfield];
			}
		}
		if(isset($ipfilterpointer)) {
			$ipfilterpointer = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $ipfilterpointer));
		}
		unset($ipfilterpointer);
	}

	if(!empty($settingnew['ipaccess']) && !ipaccess($_G['clientip'], $settingnew['ipaccess'])) {
		cpmsg('setting_ipaccess_invalid', '', 'error');
	}

	if(isset($settingnew['commentitem'])) {
		foreach($settingnew['commentitem'] as $k => $v) {
			if(!is_int($k)) {
				$settingnew['commentitem'][$k] = $k.chr(0).chr(0).chr(0).$v;
			}
		}
		$settingnew['commentitem'] = implode("\t" , $settingnew['commentitem']);
	}

	if(isset($settingnew['adminipaccess'])) {
		if($settingnew['adminipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['adminipaccess']))) {
			if(!ipaccess($_G['clientip'], $settingnew['adminipaccess'])) {
				cpmsg('setting_adminipaccess_invalid', '', 'error');
			}
		}
	}

	if(isset($settingnew['welcomemsgtitle'])) {
		$settingnew['welcomemsgtitle'] = cutstr(trim(dhtmlspecialchars($settingnew['welcomemsgtitle'])), 75);
	}

	if(isset($settingnew['showsignatures']) && isset($settingnew['showavatars']) && isset($settingnew['showimages'])) {
		$settingnew['showsettings'] = bindec($settingnew['showsignatures'].$settingnew['showavatars'].$settingnew['showimages']);
	}

	if(!empty($settingnew['globalstick'])) {
		updatecache('globalstick');
	}

	if(isset($settingnew['targetblank'])) {
		$settingnew['targetblank'] = intval($settingnew['targetblank']);
	}

	if(isset($settingnew['inviteconfig'])) {
		if($settingnew['inviteconfig']['invitecodeprice']) {
			$settingnew['inviteconfig']['invitecodeprice'] = round(abs($settingnew['inviteconfig']['invitecodeprice']), 2);
		}
	}


	if(isset($settingnew['smthumb'])) {
		$settingnew['smthumb'] = intval($settingnew['smthumb']) >= 20 && intval($settingnew['smthumb']) <= 40 ? intval($settingnew['smthumb']) : 20;
	}

	if(isset($settingnew['indexhot'])) {
		$settingnew['indexhot']['limit'] = intval($settingnew['indexhot']['limit']) ? $settingnew['indexhot']['limit'] : 10;
		$settingnew['indexhot']['days'] = intval($settingnew['indexhot']['days']) ? $settingnew['indexhot']['days'] : 7;
		$settingnew['indexhot']['expiration'] = intval($settingnew['indexhot']['expiration']) ? $settingnew['indexhot']['expiration'] : 900;
		$settingnew['indexhot']['width'] = intval($settingnew['indexhot']['width']) ? $settingnew['indexhot']['width'] : 100;
		$settingnew['indexhot']['height'] = intval($settingnew['indexhot']['height']) ? $settingnew['indexhot']['height'] : 70;
		$settingnew['indexhot']['messagecut'] = intval($settingnew['indexhot']['messagecut']) ? $settingnew['indexhot']['messagecut'] : 200;
		$_G['setting']['indexhot'] = $settingnew['indexhot'];
		updatecache('heats');
	}

	if(isset($settingnew['anonymoustext'])) {
		if(empty($settingnew['anonymoustext'])) {
			$settingnew['anonymoustext'] = cplang('anonymous');
		} else {
			$settingnew['anonymoustext'] = dhtmlspecialchars($settingnew['anonymoustext']);
		}
	}

	if(isset($settingnew['defaulteditormode']) && isset($settingnew['allowswitcheditor'])) {
		$settingnew['editoroptions'] = bindec($settingnew['defaulteditormode'].$settingnew['allowswitcheditor'].$settingnew['simplemode']);
	}

	if(isset($settingnew['myrecorddays'])) {
		$settingnew['myrecorddays'] = intval($settingnew['myrecorddays']) > 0 ? intval($settingnew['myrecorddays']) : 30;
	}

	if(!empty($settingnew['thumbstatus']) && !function_exists('imagejpeg')) {
		$settingnew['thumbstatus'] = 0;
	}


	if(!empty($settingnew['memory'])) {
		$memory = array();
		foreach($settingnew['memory'] as $k => $v) {
			if(!empty($settingnew['memory'][$k]['enable'])) {
				$memory[$k] = intval($settingnew['memory'][$k]['ttl']);
			}
		}
		if(isset($memory['common_member'])) {
			$memory['common_member_count'] = $memory['common_member_status'] = $memory['common_member_profile'] = $memory['common_member_field_home'] = $memory['common_member_field_forum'] = $memory['common_member_verify'] = $memory['common_member'];
		} else {
			unset($memory['common_member_count'], $memory['common_member_status'], $memory['common_member_profile'], $memory['common_member_field_home'], $memory['common_member_field_forum'], $memory['common_member_verify']);
		}
		$settingnew['memory'] = $memory;
	}

	if(isset($settingnew['creditsformula']) && isset($settingnew['extcredits']) && isset($settingnew['initcredits']) && isset($settingnew['creditstrans']) && isset($settingnew['creditstax'])) {
		if(!checkformulacredits($settingnew['creditsformula'])) {
			cpmsg('setting_creditsformula_invalid', '', 'error');
		}

		$extcreditsarray = array();
		if(is_array($settingnew['extcredits'])) {
			foreach($settingnew['extcredits'] as $key => $value) {
				if($value['available'] && !$value['title']) {
					cpmsg('setting_credits_title_invalid', '', 'error');
				}
				$extcreditsarray[$key] = array
					(
					'img' => dhtmlspecialchars($value['img']),
					'title'	=> dhtmlspecialchars($value['title']),
					'unit' => dhtmlspecialchars($value['unit']),
					'ratio' => ($value['ratio'] > 0 ? (float)$value['ratio'] : 0),
					'available' => $value['available'],
					'showinthread' => $value['showinthread'],
					'allowexchangein' => $value['allowexchangein'],
					'allowexchangeout' => $value['allowexchangeout'],
					);
			}
		}

		for($si = 0; $si < 13; $si++) {
			$creditstransi = $si > 0 && !$settingnew['creditstrans'][$si] ? $settingnew['creditstrans'][0] : $settingnew['creditstrans'][$si];
			if($creditstransi && empty($settingnew['extcredits'][$creditstransi]['available']) && $settingnew['creditstrans'][$si] != -1) {
				cpmsg('setting_creditstrans_invalid', '', 'error');
			}
		}
		ksort($settingnew['creditstrans']);

		$settingnew['creditsformulaexp'] = $settingnew['creditsformula'];
		foreach(array('digestposts', 'posts', 'threads', 'oltime', 'friends', 'doings', 'blogs', 'albums', 'polls', 'sharings', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
			if($extcreditsarray[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
				$replacement = $extcreditsarray[$creditsid]['title'];
			} else {
				$replacement = $lang['setting_credits_formula_'.$var];
			}
			$settingnew['creditsformulaexp'] = str_replace($var, '<u>'.$replacement.'</u>', $settingnew['creditsformulaexp']);
		}
		$settingnew['creditsformulaexp'] = addslashes('<u>'.$lang['setting_credits_formula_credits'].'</u>='.$settingnew['creditsformulaexp']);

		$initformula = str_replace('posts', '0', $settingnew['creditsformula']);
		for($i = 1; $i <= 8; $i++) {
			$settingnew['initcredits'][$i] = intval($settingnew['initcredits'][$i]);
			$initformula = str_replace('extcredits'.$i, $settingnew['initcredits'][$i], $initformula);
		}
		eval("\$_G['setting']['initcredits'] = round($initformula);");

		$settingnew['extcredits'] = $extcreditsarray;
		$settingnew['initcredits'] = $_G['setting']['initcredits'].','.implode(',', $settingnew['initcredits']);
		if($settingnew['creditstax'] < 0 || $settingnew['creditstax'] >= 1) {
			$settingnew['creditstax'] = 0;
		}

		$settingnew['creditstrans'] = implode(',', $settingnew['creditstrans']);
	}

	if(isset($settingnew['maxonlines'])) {
		if($settingnew['maxonlines'] > 65535 || !is_numeric($settingnew['maxonlines'])) {
			cpmsg('setting_maxonlines_invalid', '', 'error');
		}

		C::app()->session->update_max_rows($settingnew['maxonlines']);
		if($settingnew['maxonlines'] < $setting['maxonlines']) {
			C::app()->session->clear();
		}
	}

	if(isset($settingnew['seccodedata'])) {
		$settingnew['seccodedata']['width'] = intval($settingnew['seccodedata']['width']);
		$settingnew['seccodedata']['height'] = intval($settingnew['seccodedata']['height']);
		if($settingnew['seccodedata']['type'] != 3) {
			$settingnew['seccodedata']['width'] = $settingnew['seccodedata']['width'] < 100 ? 100 : ($settingnew['seccodedata']['width'] > 200 ? 200 : $settingnew['seccodedata']['width']);
			$settingnew['seccodedata']['height'] = $settingnew['seccodedata']['height'] < 30 ? 30 : ($settingnew['seccodedata']['height'] > 80 ? 80 : $settingnew['seccodedata']['height']);
		} else {
			$settingnew['seccodedata']['width'] = 85;
			$settingnew['seccodedata']['height'] = 25;
		}
		$seccoderoot = '';
		if($settingnew['seccodedata']['type'] == 0 || $settingnew['seccodedata']['type'] == 2) {
			$seccoderoot = 'static/image/seccode/font/en/';
		} elseif($settingnew['seccodedata']['type'] == 1) {
			$seccoderoot = 'static/image/seccode/font/ch/';
		}
		if($seccoderoot) {
			$dirs = opendir($seccoderoot);
			$seccodettf = array();
			while($entry = readdir($dirs)) {
				if($entry != '.' && $entry != '..' && in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
					$seccodettf[] = $entry;
				}
			}
			if(!$seccodettf) {
				cpmsg('setting_seccode_ttf_lost', '', 'error', array('path' => $seccoderoot));
			}
		}
	}



	if($operation == 'seccheck') {
		if(!is_numeric($settingnew['seccodedata']['type']) && !preg_match('/^[\w\_]+:[\w\_]+$/', $settingnew['seccodedata']['type'])) {
			$settingnew['seccodedata']['type'] = 0;
		}
		$settingnew['seccodestatus'] = $settingnew['seccodedata']['rule']['register']['allow'] || $settingnew['seccodedata']['rule']['login']['allow'] || $settingnew['seccodedata']['rule']['post']['allow'] || $settingnew['seccodedata']['rule']['password']['allow'] || $settingnew['seccodedata']['rule']['card']['allow'] ? 1 : 0;
		if(is_array($_GET['delete'])) {
			C::t('common_secquestion')->delete($_GET['delete']);
		}

		if(is_array($_GET['question'])) {
			foreach($_GET['question'] as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($_GET['answer'][$key])), 50);
				if($q !== '' && $a !== '') {
					C::t('common_secquestion')->update($key, array('question'=>$q, 'answer'=>$a));
				}
			}
		}
		C::t('common_secquestion')->delete_by_type(1);
		if(is_array($_GET['secqaaext'])) {
			foreach($_GET['secqaaext'] as $ext) {
				if(preg_match('/^[\w\_:]+$/', $ext)) {
					DB::insert('common_secquestion', array('type' => '1', 'question' => $ext));
				}
			}
		}

		if(is_array($_GET['newquestion']) && is_array($_GET['newanswer'])) {
			foreach($_GET['newquestion'] as $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($_GET['newanswer'][$key])), 50);
				if($q !== '' && $a !== '') {
					DB::insert('common_secquestion', array('question' => $q, 'answer' => $a));
				}
			}
		}

		updatecache('secqaa');

		$settingnew['secqaa']['status'] = bindec(intval($settingnew['secqaa']['status'][3]).intval($settingnew['secqaa']['status'][2]).intval($settingnew['secqaa']['status'][1]));
		$settingnew['secqaa'] = serialize($settingnew['secqaa']);

	} elseif($operation == 'sec') {
		if(!preg_match('/^[A-z]\w+?$/', $settingnew['reginput']['username'])) {
			$settingnew['reginput']['username'] =  'username';
		}
		if(!preg_match('/^[A-z]\w+?$/', $settingnew['reginput']['password'])) {
			$settingnew['reginput']['password'] =  'password';
		}
		if(!preg_match('/^[A-z]\w+?$/', $settingnew['reginput']['password2'])) {
			$settingnew['reginput']['password2'] =  'password2';
		}
		if(!preg_match('/^[A-z]\w+?$/', $settingnew['reginput']['email'])) {
			$settingnew['reginput']['email'] =  'email';
		}
		foreach($settingnew['reginput'] as $key => $val) {
			foreach($settingnew['reginput'] as $k => $v) {
				if($key == $k) continue;
				if($val == $v) {
					cpmsg('forum_name_duplicate', '', 'error');
				}
			}
		}
	} elseif($operation == 'seo') {
		if(!$settingnew['rewritestatus']) {
			$settingnew['rewritestatus'] = array();
		}
		$settingnew['baidusitemap_life'] = max(1, min(24, intval($settingnew['baidusitemap_life'])));
		$rewritedata = rewritedata();
		foreach($settingnew['rewriterule'] as $k => $v) {
			if(!$v) {
				$settingnew['rewriterule'][$k] = $rewritedata['rulesearch'][$k];
			}
		}
		if(!empty($_GET['seothreadlist']) && is_array($_GET['seothreadlist'])) {
			foreach($_GET['seothreadlist'] as $seofid => $val) {
				$seofid = intval($seofid);
				C::t('forum_forumfield')->update($seofid, array('seotitle' => $val['seotitle'], 'keywords' => $val['keywords'], 'seodescription' => $val['description']));
			}
		}
		if(!empty($_GET['seoarticlelist']) && is_array($_GET['seoarticlelist'])) {
			foreach($_GET['seoarticlelist'] as $seocateid => $val) {
				$seocateid = intval($seocateid);
				C::t('portal_category')->update($seocateid, array('seotitle' => $val['seotitle'], 'keyword' => $val['keywords'], 'description' => $val['description']));
			}
			updatecache('portalcategory');
		}
	} elseif($operation == 'functions') {
		$settingnew['bannedmessages'] = bindec(intval($settingnew['bannedmessages'][3]).intval($settingnew['bannedmessages'][2]).intval($settingnew['bannedmessages'][1]));
		$settingnew['activityextnum'] = intval($settingnew['activityextnum']);
		$settingnew['activitypp'] = intval($settingnew['activitypp']) == 0 ? 8 : intval($settingnew['activitypp']);
		if(!$settingnew['allowpostcomment']) $settingnew['allowpostcomment'] = array();
		if(!$settingnew['activityfield']) $settingnew['activityfield'] = array();
		if(!$settingnew['darkroom']) {
			C::t('common_nav')->update_by_navtype_type_identifier(1, 0, 'darkroom', array('available' => 0));
		} else {
			C::t('common_nav')->update_by_navtype_type_identifier(1, 0, 'darkroom', array('available' => 1));
		}
	} elseif($operation == 'permissions') {
		$settingnew['alloweditpost'] = bindec(intval($settingnew['alloweditpost'][6]).intval($settingnew['alloweditpost'][5]).intval($settingnew['alloweditpost'][4]).intval($settingnew['alloweditpost'][3]).intval($settingnew['alloweditpost'][2]).intval($settingnew['alloweditpost'][1]));
	} elseif($operation == 'ec') {
		if($settingnew['ec_ratio']) {
			if($settingnew['ec_ratio'] < 0) {
				cpmsg('alipay_ratio_invalid', '', 'error');
			}
		} else {
			$settingnew['ec_mincredits'] = $settingnew['ec_maxcredits'] = 0;
		}
		foreach(array('ec_ratio', 'ec_mincredits', 'ec_maxcredits', 'ec_maxcreditspermonth', 'tradeimagewidth', 'tradeimageheight') as $key) {
			$settingnew[$key] = intval($settingnew[$key]);
		}
	} elseif($operation == 'threadprofile') {
		$_GET['templatenew'] = serialize($_GET['templatenew']);
		if(!$_GET['namenew']) {
			cpmsg('setting_threadprofile_name_empty', '', 'error');
		}
		if($_GET['do'] == 'add') {
			C::t('forum_threadprofile')->insert(array('name' => strip_tags($_GET['namenew']), 'template' => $_GET['templatenew']));
		} elseif($_GET['do'] == 'edit') {
			C::t('forum_threadprofile')->update($_GET['id'], array('name' => strip_tags($_GET['namenew']), 'template' => $_GET['templatenew']));
		}
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=setting&operation=styles&anchor=threadprofile', 'succeed');
	}

	if($_GET['anchor'] == 'threadprofile') {
		foreach($_GET['threadprofile'] as $gid => $tpid) {
			if(!$tpid) {
				C::t('forum_threadprofile_group')->delete($gid);
			} else {
				C::t('forum_threadprofile_group')->insert(array('gid' => $gid, 'tpid' => $tpid), false, true);
			}
		}
		C::t('forum_threadprofile')->reset_default($_GET['default']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=setting&operation=styles&anchor=threadprofile', 'succeed');
	}

	if((isset($settingnew['postbanperiods']) && isset($settingnew['postmodperiods']))||(isset($settingnew['visitbanperiods'])&&isset($settingnew['attachbanperiods'])&&isset($settingnew['searchbanperiods'])) ) {
		foreach(array('visitbanperiods', 'postbanperiods','attachbanperiods', 'postmodperiods', 'searchbanperiods') as $periods) {
			$periodarray = array();
			foreach(explode("\n", $settingnew[$periods]) as $period) {
				if(preg_match("/^\d{1,2}\:\d{2}\-\d{1,2}\:\d{2}$/", $period = trim($period))) {
					$periodarray[] = $period;
				}
			}
			isset($settingnew[$periods])&&$settingnew[$periods] = implode("\r\n", $periodarray);
		}
	}


	if(isset($settingnew['heatthread'])) {
		$settingnew['heatthread']['reply'] = intval($settingnew['heatthread']['reply']);
		$settingnew['heatthread']['recommend'] = intval($settingnew['heatthread']['recommend']);
		$settingnew['heatthread']['type'] = 2;
		$settingnew['heatthread']['period'] = intval($settingnew['heatthread']['period']);
		$settingnew['heatthread']['guidelimit'] = $settingnew['heatthread']['guidelimit'] < 3 ? 3 : intval($settingnew['heatthread']['guidelimit']);
	}
	if(isset($settingnew['guide'])) {
		$settingnew['guide']['hotdt'] = intval($settingnew['guide']['hotdt']);
		$settingnew['guide']['digestdt'] = intval($settingnew['guide']['digestdt']);
	}


	if(isset($settingnew['timeformat'])) {
		$settingnew['timeformat'] = $settingnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
	}

	if(isset($settingnew['dateformat'])) {
		$settingnew['dateformat'] = dateformat($settingnew['dateformat'], 'format');
	}

	if($settingnew['accountguard']) {
		$settingnew['accountguard'] = serialize($settingnew['accountguard']);
	}

	if(!empty($_G['gp_aggid'])) {
		foreach($_G['gp_aggid'] as $gid => $v) {
			C::t('common_usergroup_field')->update($gid, array('forcelogin' => $v));
		}
		updatecache('usergroups');
	}

	if($isfounder && isset($settingnew['ftp'])) {
		$setting['ftp'] = dunserialize($setting['ftp']);
		$setting['ftp']['password'] = authcode($setting['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
		if(!empty($settingnew['ftp']['password'])) {
			$pwlen = strlen($settingnew['ftp']['password']);
			if($pwlen < 3) {
				cpmsg('ftp_password_short', '', 'error');
			}
			if($settingnew['ftp']['password'][0] == $setting['ftp']['password'][0] && $settingnew['ftp']['password'][$pwlen - 1] == $setting['ftp']['password'][strlen($setting['ftp']['password']) - 1] && substr($settingnew['ftp']['password'], 1, $pwlen - 2) == '********') {
				$settingnew['ftp']['password'] = $setting['ftp']['password'];
			}
			$settingnew['ftp']['password'] = authcode($settingnew['ftp']['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
		}
	}

	if($isfounder && isset($settingnew['mail'])) {
		$setting['mail'] = dunserialize($setting['mail']);
		$oldsmtp = $settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp'] : $settingnew['mail']['esmtp'];
		$settingnew['mail']['smtp'] = array();
		$deletesmtp = $settingnew['mail']['mailsend'] != 1 ? ($settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp']['delete'] : $settingnew['mail']['esmtp']['delete']) : array();
		foreach($oldsmtp as $id => $value) {
			if((empty($deletesmtp) || !in_array($id, $deletesmtp)) && !empty($value['server']) && !empty($value['port'])) {
				$passwordmask = $setting['mail']['smtp'][$id]['auth_password'] ? $setting['mail']['smtp'][$id]['auth_password'][0].'********'.substr($setting['mail']['smtp'][$id]['auth_password'], -2) : '';
				$value['auth_password'] = $value['auth_password'] == $passwordmask ? $setting['mail']['smtp'][$id]['auth_password'] : $value['auth_password'];
				$settingnew['mail']['smtp'][] = $value;
			}
		}

		if(!empty($_GET['newsmtp'])) {
			foreach($_GET['newsmtp']['server'] as $id => $server) {
				if(!empty($server) && !empty($_GET['newsmtp']['port'][$id])) {
					$settingnew['mail']['smtp'][] = array(
							'server' => $server,
							'port' => $_GET['newsmtp']['port'][$id] ? intval($_GET['newsmtp']['port'][$id]) : 25,
							'auth' => $_GET['newsmtp']['auth'][$id] ? 1 : 0,
							'from' => $_GET['newsmtp']['from'][$id],
							'auth_username' => $_GET['newsmtp']['auth_username'][$id],
							'auth_password' => $_GET['newsmtp']['auth_password'][$id]
						);
				}

			}
		}
	}

	if(isset($settingnew['jsrefdomains'])) {
		$settingnew['jsrefdomains'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['jsrefdomains']));
	}

	if(isset($settingnew['jsdateformat'])) {
		$settingnew['jsdateformat'] = dateformat($settingnew['jsdateformat'], 'format');
	}

	if(isset($settingnew['cachethreaddir']) && isset($settingnew['threadcaches'])) {
		if($settingnew['cachethreaddir'] && !is_writable(DISCUZ_ROOT.'./'.$settingnew['cachethreaddir'])) {
			cpmsg('cachethread_dir_noexists', '', 'error', array('cachethreaddir' => $settingnew['cachethreaddir']));
		}
		if(!empty($_GET['fids'])) {
			C::t('forum_forum')->update_threadcaches($settingnew['threadcaches'], $_GET['fids']);
		}
	}

	if($operation == 'attach') {
		if($settingnew['allowattachurl'] && !in_array($_G['config']['download']['readmod'], array(1, 4))) {
			cpmsg('attach_readmod_error', '', 'error');
		}
		$settingnew['thumbwidth'] = intval($settingnew['thumbwidth']) > 0 ? intval($settingnew['thumbwidth']) : 200;
		$settingnew['thumbheight'] = intval($settingnew['thumbheight']) > 0 ? intval($settingnew['thumbheight']) : 300;
		$settingnew['maxthumbwidth'] = intval($settingnew['maxthumbwidth']);
		$settingnew['maxthumbheight'] = intval($settingnew['maxthumbheight']);
		if($settingnew['maxthumbwidth'] < 300 || $settingnew['maxthumbheight'] < 300) {
			$settingnew['maxthumbwidth'] = 0;
			$settingnew['maxthumbheight'] = 0;
		}
		$settingnew['portalarticleimgthumbclosed'] = intval($settingnew['portalarticleimgthumbclosed']) ? '0' : 1;
		$settingnew['portalarticleimgthumbwidth'] = intval($settingnew['portalarticleimgthumbwidth']);
		$settingnew['portalarticleimgthumbheight'] = intval($settingnew['portalarticleimgthumbheight']);
	}

	if($operation == 'imgwater') {
		if(isset($settingnew['watermarktext']['portal'])) {
			watermarkinit('portal');
		}
		if(isset($settingnew['watermarktext']['forum'])) {
			watermarkinit('forum');
		}
		if(isset($settingnew['watermarktext']['album'])) {
			watermarkinit('album');
		}
		foreach(array('portal', 'forum', 'album') as $imgwatertype) {
			if($settingnew['watermarkstatus'][$imgwatertype]) {
				$settingnew['watermarktrans'][$imgwatertype] = intval($settingnew['watermarktrans'][$imgwatertype]);
				$settingnew['watermarkquality'][$imgwatertype] = intval($settingnew['watermarkquality'][$imgwatertype]);
				if(!$settingnew['watermarktrans'][$imgwatertype]) {
					$settingnew['watermarktrans'][$imgwatertype] = 50;
				}
				if(!$settingnew['watermarkquality'][$imgwatertype]) {
					$settingnew['watermarkquality'][$imgwatertype] = 85;
				}
			}
			if($settingnew['watermarktype'][$imgwatertype] == 'text') {
				$settingnew['watermarktext']['angle'][$imgwatertype] = intval($settingnew['watermarktext']['angle'][$imgwatertype]);
				$settingnew['watermarktext']['shadowx'][$imgwatertype] = intval($settingnew['watermarktext']['shadowx'][$imgwatertype]);
				$settingnew['watermarktext']['shadowy'][$imgwatertype] = intval($settingnew['watermarktext']['shadowy'][$imgwatertype]);
				$settingnew['watermarktext']['translatex'][$imgwatertype] = intval($settingnew['watermarktext']['translatex'][$imgwatertype]);
				$settingnew['watermarktext']['translatey'][$imgwatertype] = intval($settingnew['watermarktext']['translatey'][$imgwatertype]);
				$settingnew['watermarktext']['skewx'][$imgwatertype] = intval($settingnew['watermarktext']['skewx'][$imgwatertype]);
				$settingnew['watermarktext']['skewy'][$imgwatertype] = intval($settingnew['watermarktext']['skewy'][$imgwatertype]);
			}
		}
	}

	if(isset($settingnew['msgforward'])) {
		if(!empty($settingnew['msgforward']['messages'])) {
			$tempmsg = explode("\n", $settingnew['msgforward']['messages']);
			$settingnew['msgforward']['messages'] = array();
			foreach($tempmsg as $msg) {
				if($msg = strip_tags(trim($msg))) {
					$settingnew['msgforward']['messages'][] = $msg;
				}
			}
		} else {
			$settingnew['msgforward']['messages'] = array();
		}

		$tmparray = array(
			'refreshtime' => intval($settingnew['msgforward']['refreshtime']),
			'quick' => $settingnew['msgforward']['quick'] ? 1 : 0,
			'messages' => $settingnew['msgforward']['messages']
		);
		$settingnew['msgforward'] = $tmparray;
	}

	if(isset($settingnew['onlinehold'])) {
		$settingnew['onlinehold'] = intval($settingnew['onlinehold']) > 0 ? intval($settingnew['onlinehold']) : 15;
	}

	if(isset($settingnew['postno'])) {
		$settingnew['postno'] = trim($settingnew['postno']);
	}
	if(isset($settingnew['postnocustom'])) {
		$settingnew['postnocustom'] = explode("\n", $settingnew['postnocustom']);
	}

	if($operation == 'styles') {
		C::t('common_member_profile_setting')->clear_showinthread();
		$showinthreadfields = array();
		if(array_key_exists('field_birthday', $settingnew['customauthorinfo'])) {
			$settingnew['customauthorinfo']['field_birthyear'] = $settingnew['customauthorinfo']['field_birthmonth'] = $settingnew['customauthorinfo']['field_birthday'];
		}
		foreach($settingnew['customauthorinfo'] as $field => $v) {
			if(substr($field, 0, 6) == 'field_' && ($v['menu'] || $v['left'])) {
				$showinthreadfields[] = substr($field, 6);
			}
		}
		$settingnew['disallowfloat'] = array_diff($floatwinkeys, isset($settingnew['allowfloatwin']) ? $settingnew['allowfloatwin'] : array());
		$settingnew['customauthorinfo'] = array($settingnew['customauthorinfo']);
		list(, $_G['setting']['imagemaxwidth']) = explode("\t", $setting['zoomstatus']);
		if(!$settingnew['zoomstatus']) {
			$settingnew['showexif'] = 0;
		}
		$settingnew['zoomstatus'] = $settingnew['zoomstatus']."\t".(!empty($settingnew['imagemaxwidth']) ? $settingnew['imagemaxwidth'] : 600);
		if($settingnew['forumpicstyle']) {
			$settingnew['forumpicstyle']['thumbwidth'] = intval($settingnew['forumpicstyle']['thumbwidth']);
			$settingnew['forumpicstyle']['thumbheight'] = intval($settingnew['forumpicstyle']['thumbheight']);
			$settingnew['forumpicstyle']['thumbnum'] = intval($settingnew['forumpicstyle']['thumbnum']);
		}

		$settingnew['guestviewthumb']['flag'] = intval($settingnew['guestviewthumb']['flag']) ? 1 : 0;
		$settingnew['guestviewthumb']['width'] = intval($settingnew['guestviewthumb']['width']);
		$settingnew['guestviewthumb']['height'] = intval($settingnew['guestviewthumb']['height']);
		$settingnew['guestviewthumb']['width'] = $settingnew['guestviewthumb']['width'] ? $settingnew['guestviewthumb']['width'] : 100;
		$settingnew['guestviewthumb']['height'] = $settingnew['guestviewthumb']['height'] ? $settingnew['guestviewthumb']['height'] : 100;

		$settingnew['guesttipsinthread']['flag'] = intval($settingnew['guesttipsinthread']['flag']) ? 1 : 0;
		$settingnew['guesttipsinthread']['text'] = $settingnew['guesttipsinthread']['text'];


		if($showinthreadfields) {
			C::t('common_member_profile_setting')->update($showinthreadfields, array('showinthread' => 1));
		}

		unset($settingnew['allowfloatwin']);
	}

	if($operation == 'search') {
		foreach($settingnew['search'] as $key => $val) {
			foreach($val as $k => $v) {
				$settingnew['search'][$key][$k] = max(0, intval($v));
			}
		}
	}

	if($operation == 'ranklist') {
		if($_GET['updateranklistcache']) {
			if($_GET['update_ranklist_cache']) {
				foreach($_GET['update_ranklist_cache'] as $var) {
					savecache('ranklist_'.$var, '');
				}
			}
			cpmsg('ranklistcache_update', 'action=setting&operation='.$operation.(!empty($_GET['anchor']) ? '&anchor='.$_GET['anchor'] : '').(!empty($from) ? '&from='.$from : ''), 'succeed');
		}
	}

	if($operation == 'mobile') {
		$settingnew['mobile_arr']['allowmobile'] = intval($settingnew['mobile']['allowmobile']);
		if(!$settingnew['mobile_arr']['allowmobile']) {
			C::t('common_nav')->update_by_navtype_type_identifier(1, 0, 'mobile', array('available' => 0));
		} else {
			C::t('common_nav')->update_by_navtype_type_identifier(1, 0, 'mobile', array('available' => 1));
		}
		$settingnew['mobile_arr']['allowmnew'] = intval($settingnew['mobile']['allowmnew']);
		$settingnew['mobile_arr']['mobileforward'] = intval($settingnew['mobile']['mobileforward']);
        	$settingnew['mobile_arr']['otherindex'] = intval($settingnew['mobile']['otherindex']);
		$settingnew['mobile_arr']['mobileregister'] = intval($settingnew['mobile']['mobileregister']);
		$settingnew['mobile_arr']['mobileseccode'] = intval($settingnew['mobile']['mobileseccode']);
		$settingnew['mobile_arr']['mobilehotthread'] = intval($settingnew['mobile']['mobilehotthread']);
		$settingnew['mobile_arr']['mobiledisplayorder3'] = intval($settingnew['mobile']['mobiledisplayorder3']);
		$settingnew['mobile_arr']['mobilesimpletype'] = intval($settingnew['mobile']['mobilesimpletype']);
		$settingnew['mobile_arr']['mobiletopicperpage'] = intval($settingnew['mobile']['mobiletopicperpage']) > 0 ? intval($settingnew['mobile']['mobiletopicperpage']) : 1 ;
		$settingnew['mobile_arr']['mobilepostperpage'] = intval($settingnew['mobile']['mobilepostperpage']) > 0 ? intval($settingnew['mobile']['mobilepostperpage']) : 1 ;
		$settingnew['mobile_arr']['mobilecachetime'] = intval($settingnew['mobile']['mobilecachetime']);
		$settingnew['mobile_arr']['mobileforumview'] = intval($settingnew['mobile']['mobileforumview']);
		$settingnew['mobile_arr']['mobilecomefrom'] = preg_replace(array("/\son(.*)=[\'\"](.*?)[\'\"]/i"), '', strip_tags($settingnew['mobile']['mobilecomefrom'], '<a><font><img><span><strong><b>'));
		$settingnew['mobile_arr']['mobilepreview'] = intval($settingnew['mobile']['mobilepreview']);
		$settingnew['mobile_arr']['wml'] = intval($settingnew['mobile']['wml']);
		$settingnew['mobile'] = $settingnew['mobile_arr'];
		unset($settingnew['mobile_arr']);
	}
	if($operation == 'antitheft') {
		$settingnew['antitheft']['allow'] = intval($settingnew['antitheft']['allow']);
		$settingnew['antitheft']['max'] = intval($settingnew['antitheft']['max']);
		$settingnew['antitheftsetting'] = array();
		if($settingnew['antitheftwhite']) {
			$arr = explode("\n", $settingnew['antitheftwhite']);
			$arr = array_unique(array_filter(array_map('trim', $arr)));
			$settingnew['antitheftsetting']['white'] = implode("\n", $arr);
			unset($arr, $settingnew['antitheftwhite']);
		}
		if($settingnew['antitheftblack']) {
			$arr = explode("\n", $settingnew['antitheftblack']);
			$arr = array_unique(array_filter(array_map('trim', $arr)));
			$settingnew['antitheftsetting']['black'] = implode("\n", $arr);
			unset($arr, $settingnew['antitheftblack']);
		}
	}
	if($operation == 'profile') {
		$temp = array();
		$profilegroup = dunserialize($setting['profilegroup']);
		$enabledgroup = true;
		if(!empty($settingnew['profilegroupnew'])) {
			foreach($settingnew['profilegroupnew'] as $key => $value) {
				if(!in_array($key, array('base', 'contact', 'edu', 'work', 'info'))) {
					unset($profilegroup[$key]);
					continue;
				}
				$temp[$key] = $value['displayorder'];
				$profilegroup[$key]['available'] = !empty($value['available']) ? 1 : 0;
				$profilegroup[$key]['displayorder'] = $value['displayorder'];
				$profilegroup[$key]['title'] = $value['title'];
				if($enabledgroup && $value['available']) {
					$enabledgroup = false;
				}
			}
			asort($temp);
		} else {
			if(!empty($settingnew['profile'])) {
				$prokey = $settingnew['profile']['type'];
				unset($settingnew['profile']['type']);
				$profilegroup[$prokey] = $settingnew['profile'];
			}
			foreach($profilegroup as $key => $value) {
				if(!in_array($key, array('base', 'contact', 'edu', 'work', 'info'))) {
					unset($profilegroup[$key]);
					continue;
				}
				$temp[$key] = $value['displayorder'];
				if($enabledgroup && $value['available']) {
					$enabledgroup = false;
				}
			}
			asort($temp);
		}
		foreach($temp as $key => $value) {
			if($enabledgroup) {
				$profilegroup[$key]['available'] = 1;
			}
			$settingnew['profilegroup'][$key] = $profilegroup[$key];
		}
	}

	if(isset($settingnew['smcols'])) {
		$settingnew['smcols'] = $settingnew['smcols'] >= 8 && $settingnew['smcols'] <= 12 ? $settingnew['smcols'] : 8;
	}

	if(isset($settingnew['thumbdisabledmobile'])) {
		$settingnew['thumbdisabledmobile'] = !$settingnew['thumbdisabledmobile'] ? 1 : 0;
	}

	if(isset($settingnew['jspath'])) {
		if(!$settingnew['jspath']) {
			$settingnew['jspath'] = $settingnew['jspathcustom'];
		}
	}

	if(isset($settingnew['csspathv'])) {
		if(!$settingnew['csspathv']) {
			$settingnew['csspathv'] = $settingnew['csspathcustom'];
		}
	}

	if(isset($settingnew['domainwhitelist'])) {
		$settingnew['domainwhitelist'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingnew['domainwhitelist']));
	}
	if(empty($settingnew['domainwhitelist'])) {
		$settingnew['domainwhitelist_affectimg'] = 0;
	}
	$settingnew['domainwhitelist_affectimg'] = intval($settingnew['domainwhitelist_affectimg']);

	if(isset($settingnew['shownewuser']) && !$settingnew['shownewuser']) {
		$settingnew['newspacenum'] = 0;
	}

	if(isset($settingnew['blockmaxaggregationitem'])) {
		$settingnew['blockmaxaggregationitem'] = intval($settingnew['blockmaxaggregationitem']);
	}

	if(isset($settingnew['blockcachetimerange'])) {
		$settingnew['blockcachetimerange'] = $settingnew['blockcachetimerange'][0] == 0 && $settingnew['blockcachetimerange'][1] == 23 ? '' : $settingnew['blockcachetimerange'][0].','.$settingnew['blockcachetimerange'][1];
	}

	if(isset($settingnew['bbclosed']) && $settingnew['bbclosed'] == 0) {
		if(isset($setting['memberspliting'])) {
			C::t('common_member')->switch_keys('enable');
		}
	}

	if(isset($settingnew['sessionclose'])) {
		$settingnew['sessionclose'] = $settingnew['sessionclose'] ? true : false;
	}

	if(isset($settingnew['onlineguestsmultiple'])) {
		$settingnew['onlineguestsmultiple'] = floatval($settingnew['onlineguestsmultiple']);
	}
	if($_GET['delglobalreplybg']) {
		$valueparse = parse_url($setting['globalreplybg']);
		if(!isset($valueparse['host']) && file_exists($_G['setting']['attachurl'].'common/'.$setting['globalreplybg'])) {
			@unlink($_G['setting']['attachurl'].'common/'.$setting['globalreplybg']);
		}
		$_GET['globalreplybg'] = '';
	}
	if($_FILES['globalreplybg']) {
		$data = array('extid' => 'globalreplybg');
		$settingnew['globalreplybg'] = upload_icon_banner($data, $_FILES['globalreplybg'], 'globalreplybg');
	} else {
		$settingnew['globalreplybg'] = $_GET['globalreplybg'];
	}


	$updatecache = FALSE;
	$settings = array();
	foreach($settingnew as $key => $val) {
		if(in_array($key, array('siteuniqueid', 'my_sitekey', 'my_siteid')))  {
			continue;
		}
		if($setting[$key] != $val) {
			$updatecache = TRUE;
			if(in_array($key, array('newbiespan', 'topicperpage', 'postperpage', 'hottopic', 'starthreshold', 'delayviewcount', 'attachexpire',
				'visitedforums', 'maxsigrows', 'timeoffset', 'statscachelife', 'pvfrequence', 'oltimespan', 'seccodestatus',
				'maxprice', 'rssttl', 'maxonlines', 'floodctrl', 'regctrl', 'regfloodctrl',
				'searchctrl', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6',
				'extcredits7', 'extcredits8', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan',
				'maxspm', 'maxsearchresults', 'maxsmilies', 'threadmaxpages', 'maxpostsize', 'minpostsize', 'sendmailday',
				'maxpolloptions', 'karmaratelimit', 'losslessdel', 'smcols', 'allowdomain', 'feedday', 'feedmaxnum', 'feedhotday', 'feedhotmin',
				'feedtargetblank', 'updatestat', 'namechange', 'namecheck', 'networkpage', 'maxreward', 'groupnum', 'starlevelnum', 'friendgroupnum',
				'pollforumid', 'tradeforumid', 'rewardforumid', 'activityforumid', 'debateforumid', 'maxpage',
				'starcredit', 'topcachetime', 'newspacevideophoto', 'newspacerealname', 'newspaceavatar', 'newspacenum', 'shownewuser',
				'feedhotnum', 'showallfriendnum', 'feedread',
				'need_friendnum', 'need_avatar', 'uniqueemail', 'need_email', 'allowquickviewprofile', 'preventrefresh',
				'jscachelife', 'maxmodworksmonths', 'maxonlinelist'))) {
				$val = (float)$val;
			}

			if($key == 'privacy') {
				foreach($val['view'] as $var => $value) {
					$val['view'][$var] = intval($value);
				}
				if(!isset($val['feed']) || !is_array($val['feed'])) {
					$val['feed'] = array();
				}
				foreach($val['feed'] as $var => $value) {
					$val['feed'][$var] = 1;
				}
			}

			$settings[$key] = $val;
		}
	}

	if($settings) {
		C::t('common_setting')->update_batch($settings);
	}
	if($updatecache) {

		updatecache('setting');
		if(isset($settingnew['forumlinkstatus']) && $settingnew['forumlinkstatus'] != $setting['forumlinkstatus']) {
			updatecache('forumlinks');
		}
		if(isset($settingnew['userstatusby']) && $settingnew['userstatusby'] != $setting['userstatusby']) {
			updatecache('usergroups');
		}
		if((isset($settingnew['smthumb']) && $settingnew['smthumb'] != $setting['smthumb']) || (isset($settingnew['smcols']) && $settingnew['smcols'] != $setting['smcols']) || (isset($settingnew['smrows']) && $settingnew['smrows'] != $setting['smrows'])) {
			updatecache('smilies_js');
		}
		if(isset($settingnew['customauthorinfo']) && $settingnew['customauthorinfo'] != $setting['customauthorinfo']) {
			updatecache('custominfo');
		}
		if($operation == 'credits') {
			if($settingnew['extcredits'] != $setting['extcredits']) {
				include_once libfile('function/block');
				blockclass_cache();
			}
			updatecache('custominfo');
		}
		if($operation == 'access') {
			updatecache('ipctrl');
		}
		if($operation == 'styles') {
			updatecache('styles');
		}
		if(isset($settingnew['domainwhitelist'])) {
			updatecache('domainwhitelist');
		}
		if(isset($settingnew['modreasons'])) {
			updatecache('modreasons');
		}
		if(isset($settingnew['groupstatus'])) {
			updatecache('heats');
		}
		if(isset($settingnew['antitheftsetting'])) {
			updatecache('antitheft');
		}
	}

	cpmsg('setting_update_succeed', 'action=setting&operation='.$operation.(!empty($_GET['anchor']) ? '&anchor='.$_GET['anchor'] : '').(!empty($from) ? '&from='.$from : ''), 'succeed');
}

function dateformat($string, $operation = 'formalise') {
	$string = dhtmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}
function watermarkinit($type) {
	global $settingnew;
	$settingnew['watermarktext']['size'][$type] = intval($settingnew['watermarktext']['size'][$type]);
	$settingnew['watermarktext']['angle'][$type] = intval($settingnew['watermarktext']['angle'][$type]);
	$settingnew['watermarktext']['shadowx'][$type] = intval($settingnew['watermarktext']['shadowx']);
	$settingnew['watermarktext']['shadowy'][$type] = intval($settingnew['watermarktext']['shadowy'][$type]);
	$settingnew['watermarktext']['fontpath'][$type] = str_replace(array('\\', '/'), '', $settingnew['watermarktext']['fontpath'][$type]);
	if($settingnew['watermarktype'][$type] == 'text' && $settingnew['watermarktext']['fontpath'][$type]) {
		$fontpath = $settingnew['watermarktext']['fontpath'][$type];
		$fontpathnew = 'ch/'.$fontpath;
		$settingnew['watermarktext']['fontpath'][$type] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
		if(!$settingnew['watermarktext']['fontpath'][$type]) {
			$fontpathnew = 'en/'.$fontpath;
			$settingnew['watermarktext']['fontpath'][$type] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
		}
		if(!$settingnew['watermarktext']['fontpath'][$type]) {
			cpmsg('watermarkpreview_fontpath_error', '', 'error');
		}
	}
}

function showlist($first, $seconds, $thirds, $subtype) {
	echo '<tbody id="'.$subtype.'_detail" style="display:none"><tr><td colspan="2"><table width="100%">';
	foreach ($first as $id => $gsecond) {
		showdetial($gsecond, $subtype, 'group', '', 1);
		if(!empty($seconds[$id])) {
			foreach ($seconds[$id] as $second) {
				showdetial($second, $subtype);
				if(!empty($thirds[$second['id']])) {
					foreach ($thirds[$second['id']] as $third) {
						showdetial($third, $subtype);
					}
				}
			}
		}
		showdetial($gsecond, $subtype, '', 'last');
	}
	echo '</table></td></tr></tbody>';
}

function showdetial(&$forum, $varname, $type = '', $last = '', $toggle = false) {
	global $_G;

	if($last == '') {
		$tab1 = '&nbsp;&nbsp;';
		$tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;';
		if($type == 'group') {
			echo '<tr class="hover"><td colspan="2"'.($type == 'group' ? ' onclick="toggle_group(\'group_'.$varname.$forum['id'].'\', $(\'a_group_'.$varname.$forum['id'].'\'))"' : '').'>'.($type == 'group' ? '<a href="javascript:;" id="a_group_'.$varname.$forum['id'].'">'.($toggle ? '[+]' : '[-]').'</a>' : '').'&nbsp;&nbsp;'.$forum['name'].'</td></tr><tbody id="group_'.$varname.$forum['id'].'"'.($toggle ? ' style="display:none;"' : '').'>';
		}
			echo '<tr class="header"><td colspan="2">'.$tab1.$forum['name'].'</td></tr>';
			showtablerow('', array('width="12%"', ''), array(
					$tab2.cplang('setting_seo_seotitle'),
					'<input type="text" id="t_'.$forum['id'].'_'.$varname.'" onfocus="getcodetext(this, \''.$varname.'\');" name="seo'.$varname.'['.$forum[id].'][seotitle]" value="'.dhtmlspecialchars($forum['seotitle']).'" class="txt" style="width:280px;" />',
				)
			);
			showtablerow('', array('width="12%"', ''), array(
					$tab2.cplang('setting_seo_seokeywords'),
					'<input type="text" id="k_'.$forum['id'].'_'.$varname.'" onfocus="getcodetext(this, \''.$varname.'\');" name="seo'.$varname.'['.$forum[id].'][keywords]" value="'.dhtmlspecialchars($forum['keywords']).'" class="txt" style="width:280px;" />',
				)
			);
			showtablerow('', array('width="12%"', ''), array(
					$tab2.cplang('setting_seo_seodescription'),
					'<input type="text" id="d_'.$forum['id'].'_'.$varname.'" onfocus="getcodetext(this, \''.$varname.'\');" name="seo'.$varname.'['.$forum[id].'][description]" value="'.dhtmlspecialchars($forum['description']).'" class="txt" style="width:280px;" />',
				)
			);
	} else {
		if($last == 'lastboard') {
			$return = '</tbody>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '</tbody>';
		}
	}
	echo  $return = isset($return) ? $return : '';
}

function getmemorycachekeys() {
	return array('common_member', 'forum_forum', 'forum_thread', 'forum_thread_forumdisplay','forum_postcache',
				'forum_collectionrelated', 'forum_collection', 'home_follow', 'forumindex', 'diyblock', 'diyblockoutput');
}

function getseccodes() {
	global $_G;
	$checkdirs = array_merge(array(''), $_G['setting']['plugins']['available']);
	$seccodetypearray = array();
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_ROOT.'./source/plugin/'.$key.'/seccode';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/seccode';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$codedir = dir($dir);
		while($entry = $codedir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^seccode\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$codeclass = substr($entry, 0, -4);
				if(class_exists($codeclass)) {
					$code = new $codeclass();
					$script = substr($codeclass, 8);
					$script = ($key ? $key.':' : '').$script;
					if(!is_numeric($script)) {
						$seccodetypearray[] = array($script, lang('seccode/'.$script, $code->name), array('seccodeimageext' => 'none', 'seccodeimagewh' => 'none'));
					}
				}
			}
		}
	}
	return $seccodetypearray;
}

function getsecqaas($qaaext) {
	global $_G;
	$checkdirs = array_merge(array(''), $_G['setting']['plugins']['available']);
	$advs = array();
	$secqaaext = '';
	foreach($checkdirs as $key) {
		if($key) {
			$dir = DISCUZ_ROOT.'./source/plugin/'.$key.'/secqaa';
		} else {
			$dir = DISCUZ_ROOT.'./source/class/secqaa';
		}
		if(!file_exists($dir)) {
			continue;
		}
		$qaadir = dir($dir);
		while($entry = $qaadir->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^secqaa\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
				@include_once $dir.'/'.$entry;
				$qaaclass = substr($entry, 0, -4);
				if(class_exists($qaaclass)) {
					$qaa = new $qaaclass();
					$script = substr($qaaclass, 7);
					$script = ($key ? $key.':' : '').$script;
					$secqaaext .= showtablerow('', array('', 'class="td26"'), array(
						'',
						'<label>'.($qaa->copyright ? '<div class="right">'.lang('secqaa/'.$script, $qaa->copyright).'</div>' : '').'<label><input class="checkbox" class="checkbox" type="checkbox" name="secqaaext[]" value="'.$script.'"'.(in_array($script, $qaaext) ? ' checked="checked"' : '').'> '.lang('secqaa/'.$script, $qaa->name).(@filemtime($dir.'/'.$entry) > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '').($qaa->description ? '<div class="lightfont" style="margin-left:30px">'.lang('secqaa/'.$script, $qaa->description).'</div>' : '').'</label>'
					), true);
				}
			}
		}
	}
	return $secqaaext;
}

function threadprofile_buttons($id, $authorinfoitems) {
	$buttons = '';$i = 0;
	foreach($authorinfoitems as $k => $name) {
		if(!is_numeric($k)) {
			if($i > 11) {
				$buttons .= '<br />';
				$i = 0;
			}
			if(substr($k, 0, 1) == '{') {
				$code = $k;
			} else {
				$code = '<dt>{baseinfo='.$k.',1}</dt><dd>{baseinfo='.$k.',0}</dd>\n';
			}
			$buttons .= '<a href="###" onclick="insertunit($(\''.$id.'\'), \''.$code.'\')">'.$name.'</a>';
			$i++;
		} else {
			$buttons .= $name ? '<a href="javascript:;" onclick="display(\''.$id.'more\')" class="light">'.cplang('more').'</a><div id="'.$id.'more" style="display:none">' : '<br />';
			$i = 0;
		}
	}
	$buttons .= '</div>';
	return $buttons;
}

function showsetting_threadprfile($authorinfoitems, $template = array()) {
	$template_left = dhtmlspecialchars($template['left']);
	$buttons = threadprofile_buttons('tleft', $authorinfoitems);
	echo '<tr><td class="td27" colspan="2">'.cplang('setting_styles_threadprofile_leftinfoprofile').':</td></tr>
		<tr><td colspan="2"><div class="threadprofilenode">'.$buttons.'</div><textarea name="templatenew[left]" id="tleft" class="marginbot" style="width:80%" rows="10" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.$template_left.'</textarea></td></tr>';
	$template_top = dhtmlspecialchars($template['top']);
	$buttons = threadprofile_buttons('ttop', $authorinfoitems);
	echo '<tr><td class="td27" colspan="2">'.cplang('setting_styles_threadprofile_avatarprofile').':</td></tr>
		<tr><td colspan="2"><div class="threadprofilenode">'.$buttons.'</div><textarea name="templatenew[top]" id="ttop" class="marginbot" style="width:80%" rows="10" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.$template_top.'</textarea></td></tr>';
}

?>