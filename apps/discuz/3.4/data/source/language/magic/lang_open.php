<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_open.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'open_name' => '喧嚣卡',
	'open_desc' => '可以将主题开启，可以回复',
	'open_forum' => '允许使用本道具的版块',
	'open_info' => '开放指定的主题，请输入主题的 ID',
	'open_info_nonexistence' => '请指定要开放的主题',
	'open_succeed' => '您操作的主题已开放回复',
	'open_info_noperm' => '对不起，主题所在版块不允许使用本道具',
	'open_info_user_noperm' => '对不起，您不能对此人使用本道具',

	'open_notification' => '您的主题 {subject} 被 {actor} 使用了{magicname}，<a href="forum.php?mod=viewthread&tid={tid}">快去看看吧！</a>',
);

?>