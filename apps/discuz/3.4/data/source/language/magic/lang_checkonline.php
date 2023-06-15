<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_checkonline.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'checkonline_name' => '雷达卡',
	'checkonline_desc' => '查看某个用户是否在线',
	'checkonline_targetuser' => '您要查看谁是否在线',
	'checkonline_info_nonexistence' => '请输入用户名',
	'checkonline_hidden_message' => '{username} 当前隐身，最后活动时间是 {time}',
	'checkonline_online_message' => '{username} 当前在线，最后活动时间是 {time}',
	'checkonline_offline_message' => '{username} 当前离线',
	'checkonline_info_noperm' => '对不起，您无权查看此人的 IP',

	'checkonline_notification' => '有人使用了{magicname}检查您是否在线',
);

?>