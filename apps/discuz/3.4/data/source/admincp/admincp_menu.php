<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_menu.php 36284 2016-12-12 00:47:50Z nemohou $
 */

global $_G;
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$isfounder = isset($isfounder) ? $isfounder : isfounder();

$topmenu = $menu = array();

$topmenu = array (
	'index' => '',
	'global' => '',
	'style' => '',
	'topic' => '',
	'user' => '',
	'portal' => '',
	'forum' => '',
	'group' => '',
	'safe' => '',
	'extended' => '',
	'plugin' => $isfounder ? 'plugins' : '',
	'template' => '',
	'tools' => '',
);

$menu['index'] = array(
	array('menu_home', 'index'),
	array('menu_custommenu_manage', 'misc_custommenu'),
);

$custommenu = get_custommenu();
$menu['index'] = array_merge($menu['index'], $custommenu);

$menu['global'] = array(
	array('menu_setting_basic', 'setting_basic'),
	array('menu_setting_access', 'setting_access'),
	array('menu_setting_functions', 'setting_functions'),
	array('menu_setting_optimize', 'setting_cachethread'),
	array('menu_setting_seo', 'setting_seo'),
	array('menu_setting_domain', 'domain'),
	array('menu_setting_follow', 'setting_follow'),
	array('menu_setting_home', 'setting_home'),
	array('menu_setting_user', 'setting_permissions'),
	array('menu_setting_credits', 'setting_credits'),
	array('menu_setting_datetime', 'setting_datetime'),
	array('menu_setting_attachments', 'setting_attach'),
	array('menu_setting_imgwater', 'setting_imgwater'),
	array('menu_posting_attachtypes', 'misc_attachtype'),
	array('menu_setting_search', 'setting_search'),
	array('menu_setting_district', 'district'),
	array('menu_setting_ranklist', 'setting_ranklist'),
	array('menu_setting_mobile', 'setting_mobile'),
	array('menu_setting_antitheft', 'setting_antitheft'),
);

$menu['style'] = array(
	array('menu_setting_customnav', 'nav'),
	array('menu_setting_styles', 'setting_styles'),
	array('menu_posting_smilies', 'smilies'),
	array('menu_click', 'click'),
	array('menu_thread_stamp', 'misc_stamp'),
	array('menu_posting_editor', 'setting_editor'),
	array('menu_misc_onlinelist', 'misc_onlinelist'),
);

$menu['topic'] = array(
	array('menu_moderate_posts', 'moderate'),
	array('menu_remoderate', 'remoderate'),
	array('menu_posting_censors', 'misc_censor'),
	array('menu_maint_report', 'report'),
	array('menu_setting_tag', 'tag'),
	array('menu_setting_collection', 'collection'),
	array(cplang('nav_forum'), '', 1),
		array('menu_maint_threads', 'threads'),
		array('menu_maint_prune', 'prune'),
		array('menu_maint_attaches', 'attach'),
	array(cplang('nav_forum'), '', 2),
	array(cplang('nav_group'), '', 1),
		array('menu_maint_threads_group', 'threads_group'),
		array('menu_maint_prune_group', 'prune_group'),
		array('menu_maint_attaches_group', 'attach_group'),
	array(cplang('nav_group'), '', 2),
	array(cplang('thread'), '', 1),
    		array('menu_moderate_recyclebin', 'recyclebin'),
		array('menu_moderate_recyclebinpost', 'recyclebinpost'),
		array('menu_threads_forumstick', 'threads_forumstick'),
		array('menu_postcomment', 'postcomment'),
	array(cplang('thread'), '', 2),
	array(cplang('nav_home'), '', 1),
		array('menu_maint_doing', 'doing'),
		array('menu_maint_blog', 'blog'),
		array('menu_maint_blog_recycle_bin', 'blogrecyclebin'),
		array('menu_maint_feed', 'feed'),
		array('menu_maint_album', 'album'),
		array('menu_maint_pic', 'pic'),
		array('menu_maint_comment', 'comment'),
		array('menu_maint_share', 'share'),
	array(cplang('nav_home'), '', 2),
);

$menu['user'] = array(
	array('menu_members_edit', 'members_search'),
	array('menu_members_add', 'members_add'),
	array('menu_members_profile', 'members_profile'),
	array('menu_members_stat', 'members_stat'),
	array('menu_members_newsletter', 'members_newsletter'),
	array('menu_members_mobile', 'members_newsletter_mobile'),
	array('menu_usertag', 'usertag'),
	array('menu_members_edit_ban_user', 'members_ban'),
	array('menu_members_ipban', 'members_ipban'),
	array('menu_members_credits', 'members_reward'),
	array('menu_moderate_modmembers', 'moderate_members'),
	array('menu_admingroups', 'admingroup'),
	array('menu_usergroups', 'usergroups'),
	array('menu_follow', 'specialuser_follow'),
	array('menu_defaultuser', 'specialuser_defaultuser'),
	array('members_verify_profile', 'verify_verify'),
	array('menu_members_verify_setting', 'verify'),
);

if(is_array($_G['setting']['verify'])) {
	foreach($_G['setting']['verify'] as $vid => $verify) {
		if($vid != 7 && $verify['available']) {
			$menu['user'][] = array($verify['title'], "verify_verify_$vid");
		}
	}
}

$menu['portal'] = array(
	array('menu_portalcategory', 'portalcategory'),
	array('menu_article', 'article'),
	array('menu_topic', 'topic'),
	array('menu_html', 'makehtml'),
	array('menu_diytemplate', 'diytemplate'),
	array('menu_block', 'block'),
	array('menu_blockstyle', 'blockstyle'),
	array('menu_blockxml', 'blockxml'),
	array('menu_portalpermission', 'portalpermission'),
	array('menu_blogcategory', 'blogcategory'),
	array('menu_albumcategory', 'albumcategory'),
);

$menu['forum'] = array(
	array('menu_forums', 'forums'),
	array('menu_forums_merge', 'forums_merge'),
	array('menu_forums_infotypes', 'threadtypes'),
	array('menu_grid', 'grid'),
);

$menu['group'] = array(
	array('menu_group_setting', 'group_setting'),
	array('menu_group_type', 'group_type'),
	array('menu_group_manage', 'group_manage'),
	array('menu_group_userperm', 'group_userperm'),
	array('menu_group_level', 'group_level'),
	array('menu_group_mod', 'group_mod'),
);

$menu['safe'] = array(
	array('menu_safe_setting', 'setting_sec'),
	array('menu_safe_seccheck', 'setting_seccheck'),
	array('menu_security', 'optimizer_security'),
	array('menu_serversec', 'optimizer_serversec'),
	array('menu_safe_accountguard', 'setting_accountguard'),
);

$menu['extended'] = array(
	array('menu_misc_announce', 'announce'),
	array('menu_adv_custom', 'adv'),
	array('menu_tasks', 'tasks'),
	array('menu_magics', 'magics'),
	array('menu_medals', 'medals'),
	array('menu_misc_help', 'faq'),
	array('menu_ec', 'setting_ec'),
	array('menu_misc_link', 'misc_link'),
	array('memu_focus_topic', 'misc_focus'),
	array('menu_misc_relatedlink', 'misc_relatedlink'),
	array('menu_card', 'card')
);

if(file_exists($menudir = DISCUZ_ROOT.'./source/admincp/menu')) {
	$adminextend = $adminextendnew = array();
	if(file_exists($adminextendfile = DISCUZ_ROOT.'./data/sysdata/cache_adminextend.php')) {
		@include $adminextendfile;
	}
	$menudirhandle = dir($menudir);
	while($entry = $menudirhandle->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^menu\_([\w\.]+)$/", $entry, $entryr) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($menudir.'/'.$entry)) {
			@include_once $menudir.'/'.$entry;
			$adminextendnew[] = $entryr[1];
		}
	}
	if($adminextend != $adminextendnew) {
		@unlink($adminextendfile);
		if($adminextendnew) {
			require_once libfile('function/cache');
			writetocache('adminextend', getcachevars(array('adminextend' => $adminextendnew)));
		}
		unset($_G['lang']['admincp']);
	}
}

if($isfounder) {
	$menu['plugin'] = array(
		array('menu_plugins', 'plugins'),
	);
}

$menu['template'] = array(
	array('menu_styles', 'styles'),
);
if($isfounder && isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0) {
	$menu['template'][] = array('menu_templates_add', 'templates_add');
}

loadcache('adminmenu');
if(is_array($_G['cache']['adminmenu'])) {
	foreach($_G['cache']['adminmenu'] as $row) {
		if($row['name'] == 'plugins_system') {
			$row['name'] = cplang('plugins_system');
		}
		$menu['plugin'][] = array($row['name'], $row['action'], $row['sub']);
	}
}
if(!$menu['plugin']) {
	unset($topmenu['plugin']);
}

$menu['tools'] = array(
	array('menu_tools_updatecaches', 'tools_updatecache'),
	array('menu_tools_updatecounters', 'counter'),
	array('menu_logs', 'logs'),
	array('menu_misc_cron', 'misc_cron'),
	$isfounder ? array('menu_tools_fileperms', 'tools_fileperms') : null,
	$isfounder ? array('menu_tools_filecheck', 'checktools_filecheck') : null,
	$isfounder ? array('menu_tools_hookcheck', 'checktools_hookcheck') : null,
	$isfounder ? array('menu_tools_replacekey', 'checktools_replacekey') : null,
);

if($isfounder) {
	$topmenu['founder'] = '';

	$menu['founder'] = array(
		array('menu_founder_perm', 'founder_perm'),
		array('menu_setting_mail', 'setting_mail'),
		array('menu_setting_uc', 'setting_uc'),
		array('menu_db', 'db_export'),
		array('menu_membersplit', 'membersplit_check'),
		array('menu_postsplit', 'postsplit_manage'),
		array('menu_threadsplit', 'threadsplit_manage'),
		array('menu_optimizer', 'optimizer_performance'),
	);

	$menu['uc'] = array();
}

if($isfounder || $_G['adminid'] == 1) {
	$topmenu['cloudaddons'] = '';
	$menu['cloudaddons'] = array(
		array('menu_addons', 'cloudaddons&frame=no', '_blank'),
	);
}

if(!isfounder() && !isset($GLOBALS['admincp']->perms['all'])) {
	$menunew = $menu;
	foreach($menu as $topkey => $datas) {
		if($topkey == 'index') {
			continue;
		}
		$itemexists = 0;
		foreach($datas as $key => $data) {
			if(array_key_exists($data[1], $GLOBALS['admincp']->perms)) {
				$itemexists = 1;
			} else {
				unset($menunew[$topkey][$key]);
			}
		}
		if(!$itemexists) {
			unset($topmenu[$topkey]);
			unset($menunew[$topkey]);
		}
	}
	$menu = $menunew;
}

?>