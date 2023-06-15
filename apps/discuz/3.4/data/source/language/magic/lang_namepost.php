<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_namepost.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'namepost_name' => '照妖镜',
	'namepost_desc' => '可以查看一次匿名用户的真实身份。',
	'namepost_forum' => '允许使用本道具的版块',
	'namepost_num' => '拥有个数: {magicnum}',
	'namepost_info' => '指定要显身的帖子，请输入帖子的 ID',
	'namepost_info_nonexistence' => '参数错误，不能在此使用本道具。',
	'namepost_succeed' => '匿名的用户是 <a title="{username}" href="space.php?uid={uid}" target="_blank"><b>{username}</b></a>',
	'namepost_info_noperm' => '对不起，主题所在版块不允许使用本道具',
	'namepost_info_user_noperm' => '对不起，您不能对此人使用本道具',
	'magic_namepost_succeed' => '匿名的用户是',
);

?>