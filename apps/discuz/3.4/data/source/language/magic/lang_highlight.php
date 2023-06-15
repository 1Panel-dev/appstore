<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_highlight.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'highlight_name' => '变色卡',
	'highlight_desc' => '可以将帖子或日志的标题高亮，变更颜色',
	'highlight_expiration' => '高亮有效期',
	'highlight_expiration_comment' => '设置标题可以被高亮多长时间，默认 24 小时。作用于日志时无有效期。',
	'highlight_forum' => '允许使用本道具的版块',
	'highlight_info_tid' => '高亮主题的标题 {expiration} 小时',
	'highlight_info_blogid' => '可以将日志或帖子的标题高亮，变更颜色',
	'highlight_color' => '颜色',
	'highlight_info_nonexistence_tid' => '请指定要高亮的帖子',
	'highlight_info_nonexistence_blogid' => '请指定要高亮的日志',
	'highlight_succeed_tid' => '您操作的帖子已高亮',
	'highlight_succeed_blogid' => '您操作的日志已高亮',
	'highlight_info_noperm' => '对不起，主题所在版块不允许使用本道具',
	'highlight_info_notype' => '参数错误，没有指定操作类型。',

	'highlight_notification' => '您的主题 {subject} 被 {actor} 使用了{magicname}，<a href="forum.php?mod=viewthread&tid={tid}">快去看看吧！</a>',
	'highlight_notification_blogid' => '您的日志 {subject} 被 {actor} 使用了{magicname}，<a href="home.php?mod=space&do=blog&id={blogid}">快去看看吧！</a>',
);

?>