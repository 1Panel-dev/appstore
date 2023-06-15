<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_stick.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'stick_name' => '置顶卡',
	'stick_desc' => '可以将主题置顶',
	'stick_expiration' => '置顶有效期',
	'stick_expiration_comment' => '设置主题可以被置顶多长时间，默认 24 小时',
	'stick_forum' => '允许使用本道具的版块',
	'stick_info' => '置顶指定的主题 {expiration} 小时，请输入主题的 ID',
	'stick_info_nonexistence' => '请指定要置顶的主题',
	'stick_succeed' => '您操作的主题已置顶',
	'stick_info_noperm' => '对不起，主题所在版块不允许使用本道具',

	'stick_notification' => '您的主题 {subject} 被 {actor} 使用了{magicname}，<a href="forum.php?mod=viewthread&tid={tid}">快去看看吧！</a>',
);

?>